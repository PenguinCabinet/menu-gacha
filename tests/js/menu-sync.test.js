import assert from 'node:assert/strict';
import test from 'node:test';
import * as Y from 'yjs';
import { Server } from '@hocuspocus/server';
import { HocuspocusProvider } from '@hocuspocus/provider';
import { menuSnapshot, reconcileMenu } from '../../server/menu-sync-server.js';

const menu = {
    name: 'ランチ', isPublished: true,
    items: [{ id: 1, name: 'カレー', price: 800, flagIds: [3] }],
    flags: [{ id: 3, name: '学割' }],
};

test('保存したYjs文書を別端末に復元できる', () => {
    const original = new Y.Doc();
    reconcileMenu(original, menu);
    const name = original.getMap('items').get('1').get('name');
    name.delete(0, name.length);
    name.insert(0, 'チキンカレー');

    const restored = new Y.Doc();
    Y.applyUpdate(restored, Y.encodeStateAsUpdate(original));

    assert.deepEqual(menuSnapshot(restored), {
        ...menu, items: [{ id: 1, name: 'チキンカレー', price: 800, flagIds: [3] }],
    });
});

test('同時編集した別々の文字列変更がYjsで統合される', () => {
    const first = new Y.Doc();
    reconcileMenu(first, menu);
    const second = new Y.Doc();
    Y.applyUpdate(second, Y.encodeStateAsUpdate(first));

    first.getMap('menu').get('name').insert(0, '新');
    second.getMap('menu').get('name').insert(3, '限定');
    Y.applyUpdate(first, Y.encodeStateAsUpdate(second));
    Y.applyUpdate(second, Y.encodeStateAsUpdate(first));

    assert.equal(menuSnapshot(first).name, '新ランチ限定');
    assert.equal(menuSnapshot(second).name, '新ランチ限定');
});

test('別端末で同時に追加したフラグ選択が両方残る', () => {
    const first = new Y.Doc();
    reconcileMenu(first, { ...menu, items: [{ ...menu.items[0], flagIds: [] }] });
    const second = new Y.Doc();
    Y.applyUpdate(second, Y.encodeStateAsUpdate(first));

    first.getMap('items').get('1').get('flagIds').set('3', true);
    second.getMap('items').get('1').get('flagIds').set('4', true);
    Y.applyUpdate(first, Y.encodeStateAsUpdate(second));
    Y.applyUpdate(second, Y.encodeStateAsUpdate(first));

    assert.deepEqual(menuSnapshot(first).items[0].flagIds.sort(), [3, 4]);
    assert.deepEqual(menuSnapshot(second).items[0].flagIds.sort(), [3, 4]);
});

test('再接続時にDBの追加・削除と文書の項目を照合する', () => {
    const original = new Y.Doc();
    reconcileMenu(original, menu);
    reconcileMenu(original, {
        ...menu,
        items: [{ id: 2, name: 'うどん', price: 500, flagIds: [] }],
        flags: [],
    });

    assert.deepEqual(menuSnapshot(original), {
        name: 'ランチ', isPublished: true,
        items: [{ id: 2, name: 'うどん', price: 500, flagIds: [] }],
        flags: [],
    });
});

test('WebSocket経由で別端末へ編集が届く', { timeout: 10000 }, async () => {
    const server = new Server({ address: '127.0.0.1', port: 0, quiet: true, onLoadDocument: () => {
        const document = new Y.Doc();
        reconcileMenu(document, menu);
        return document;
    } });
    await server.listen();

    const first = new Y.Doc();
    const second = new Y.Doc();
    const synced = (provider) => new Promise((resolve) => provider.on('synced', ({ state }) => {
        if (state) {
            resolve();
        }
    }));
    const firstProvider = new HocuspocusProvider({ url: server.webSocketURL, name: '1', document: first });
    const secondProvider = new HocuspocusProvider({ url: server.webSocketURL, name: '1', document: second });

    try {
        await Promise.all([synced(firstProvider), synced(secondProvider)]);
        const received = new Promise((resolve) => second.on('update', () => {
            if (menuSnapshot(second).name === '新ランチ') {
                resolve();
            }
        }));
        first.getMap('menu').get('name').insert(0, '新');
        await received;

        assert.equal(menuSnapshot(second).name, '新ランチ');
    } finally {
        await Promise.all([firstProvider.destroy(), secondProvider.destroy()]);
        await server.destroy();
    }
});
