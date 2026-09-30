import { Server } from '@hocuspocus/server';
import * as Y from 'yjs';

const baseUrl = process.env.MENU_SYNC_APP_URL ?? 'http://127.0.0.1:8080';
const serverKey = process.env.APP_KEY;

function menuId(documentName) {
    if (!/^[1-9]\d*$/.test(documentName)) {
        throw new Error('Invalid menu ID');
    }

    return documentName;
}

async function internalRequest(documentName, options = {}) {
    const response = await fetch(`${baseUrl}/internal/menu/${menuId(documentName)}/sync`, {
        ...options,
        headers: { 'X-Menu-Sync-Key': serverKey, ...options.headers },
    });
    if (!response.ok) {
        const error = new Error(`Menu sync request failed (${response.status})`);
        error.status = response.status;
        throw error;
    }

    return response.json();
}

export function reconcileMenu(document, menu) {
    const details = document.getMap('menu');
    const items = document.getMap('items');
    const flags = document.getMap('flags');

    document.transact(() => {
        reconcileText(details, 'name', menu.name);
        details.set('isPublished', menu.isPublished);

        for (const [records, data] of [[items, menu.items], [flags, menu.flags]]) {
            const existing = new Set(data.map((record) => String(record.id)));
            for (const key of records.keys()) {
                if (!existing.has(key)) {
                    records.delete(key);
                }
            }
            for (const record of data) {
                const id = String(record.id);
                if (!records.has(id)) {
                    records.set(id, new Y.Map());
                }
                const current = records.get(id);
                reconcileText(current, 'name', record.name);
                if (records === items) {
                    current.set('price', record.price);
                    if (!current.has('flagIds')) {
                        current.set('flagIds', new Y.Map());
                    }
                    const selected = current.get('flagIds');
                    for (const flagId of selected.keys()) {
                        if (!record.flagIds.includes(Number(flagId))) {
                            selected.delete(flagId);
                        }
                    }
                    for (const flagId of record.flagIds) {
                        selected.set(String(flagId), true);
                    }
                }
            }
        }
    });
}

function reconcileText(record, key, value) {
    if (!record.has(key)) {
        record.set(key, new Y.Text(value));
        return;
    }
    const text = record.get(key);
    if (text.toString() !== value) {
        text.delete(0, text.length);
        text.insert(0, value);
    }
}

export function menuSnapshot(document) {
    const details = document.getMap('menu');
    return {
        name: details.get('name').toString(),
        isPublished: details.get('isPublished'),
        items: Array.from(document.getMap('items'), ([id, data]) => ({
            id: Number(id), name: data.get('name').toString(), price: data.get('price'),
            flagIds: Array.from(data.get('flagIds').keys(), Number),
        })),
        flags: Array.from(document.getMap('flags'), ([id, data]) => ({ id: Number(id), name: data.get('name').toString() })),
    };
}

if (process.env.MENU_SYNC_LISTEN === 'true') {
    if (!serverKey) {
        throw new Error('APP_KEY is required to run menu sync');
    }

    const server = new Server({
        address: '127.0.0.1',
        port: 1234,
        debounce: 1000,
        maxDebounce: 5000,
        websocketOptions: { maxPayload: 2 * 1024 * 1024 },
        async onAuthenticate({ documentName, requestHeaders }) {
            const expectedOrigin = new URL(process.env.APP_URL ?? baseUrl).origin;
            if (requestHeaders.get('origin') !== expectedOrigin) {
                throw new Error('Invalid origin');
            }
            const response = await fetch(`${baseUrl}/menu/${menuId(documentName)}/sync/authorize`, {
                headers: { Cookie: requestHeaders.get('cookie') ?? '' },
                redirect: 'manual',
            });
            if (response.status !== 200 || !response.headers.get('content-type')?.includes('application/json')) {
                throw new Error('Not authorized to edit this menu');
            }
        },
        async onLoadDocument({ documentName }) {
            const { state, menu } = await internalRequest(documentName);
            const document = new Y.Doc();
            if (state) {
                Y.applyUpdate(document, Buffer.from(state, 'base64'));
            }
            reconcileMenu(document, menu);

            return document;
        },
        async onStoreDocument({ documentName, document }) {
            const state = Buffer.from(Y.encodeStateAsUpdate(document)).toString('base64');
            const menu = menuSnapshot(document);
            try {
                await internalRequest(documentName, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ state, menu }),
                });
            } catch (error) {
                if (error.status === 422) {
                    document.broadcastStateless('invalid');
                } else {
                    document.broadcastStateless('error');
                }
                throw error;
            }
            if (Buffer.from(state, 'base64').equals(Buffer.from(Y.encodeStateAsUpdate(document)))) {
                document.broadcastStateless('saved');
            }
        },
    });

    server.listen();
}
