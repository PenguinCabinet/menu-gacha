import * as Y from 'yjs';
import { HocuspocusProvider } from '@hocuspocus/provider';

const form = document.getElementById('menu-gacha-edit-form');

if (form) {
    const ydoc = new Y.Doc();
    const menu = ydoc.getMap('menu');
    const items = ydoc.getMap('items');
    const flags = ydoc.getMap('flags');
    const status = document.getElementById('edit-sync-status');
    const saveButton = document.querySelector('[form="menu-gacha-edit-form"][type="submit"]');
    const menuId = form.action.match(/\/menu\/(\d+)/)?.[1];
    let ready = false;
    let refreshing = false;
    const pendingMutations = [];

    const provider = new HocuspocusProvider({
        url: `${location.protocol === 'https:' ? 'wss:' : 'ws:'}//${location.host}/sync/ws`,
        name: menuId,
        document: ydoc,
        onStatus({ status: connectionStatus }) {
            if (connectionStatus !== 'connected') {
                status.textContent = 'オフライン：再接続後に同期します';
                saveButton.classList.remove('d-none');
            }
        },
        onSynced({ state }) {
            if (state) {
                ready = true;
                for (const [submittedForm, result] of pendingMutations.splice(0)) {
                    window.menuGachaSync.afterFormSuccess(submittedForm, result);
                }
                render();
                status.textContent = 'リアルタイム同期中';
                saveButton.classList.add('d-none');
            }
        },
        onAuthenticationFailed() {
            status.textContent = '同期の認証に失敗しました。再ログインしてください。';
            saveButton.classList.remove('d-none');
        },
        async onStateless({ payload }) {
            if (payload === 'error') {
                status.textContent = '自動保存に失敗しました。再試行中です。';
                return;
            }
            if (payload === 'invalid') {
                status.textContent = '入力内容を確認してください。まだ保存されていません。';
                return;
            }
            if (payload === 'saved') {
                status.textContent = '自動保存済み';
                try {
                    await window.menuGachaRefresh?.();
                    render();
                } catch (error) {
                    status.textContent = '表示の更新に失敗しました。再読み込みしてください。';
                }
            }
        },
    });

    function updateText(text, value) {
        const previous = text.toString();
        if (previous === value) {
            return;
        }
        let prefix = 0;
        while (prefix < previous.length && prefix < value.length && previous[prefix] === value[prefix]) {
            prefix++;
        }
        let suffix = 0;
        while (suffix < previous.length - prefix && suffix < value.length - prefix
            && previous[previous.length - suffix - 1] === value[value.length - suffix - 1]) {
            suffix++;
        }
        text.delete(prefix, previous.length - prefix - suffix);
        text.insert(prefix, value.slice(prefix, value.length - suffix));
    }

    function updateSelections(selected, flagIds) {
        const desired = new Set(flagIds.map(String));
        for (const id of selected.keys()) {
            if (!desired.has(id)) {
                selected.delete(id);
            }
        }
        for (const id of desired) {
            if (!selected.has(id)) {
                selected.set(id, true);
            }
        }
    }

    function readItem(id) {
        const row = document.getElementById(`edit-item-${id}`);
        const record = new Y.Map();
        record.set('name', new Y.Text(document.getElementById(`item-name-${id}`).value));
        const price = document.getElementById(`item-price-${id}`).value;
        record.set('price', price === '' ? null : Number(price));
        const selected = new Y.Map();
        for (const input of row.querySelectorAll('fieldset input:checked')) {
            selected.set(input.value, true);
        }
        record.set('flagIds', selected);
        return record;
    }

    function readFlag(id) {
        const record = new Y.Map();
        record.set('name', new Y.Text(document.getElementById(`flag-name-${id}`).value));
        return record;
    }

    window.menuGachaSync = {
        afterFormSuccess(submittedForm, result) {
            if (!ready) {
                pendingMutations.push([submittedForm, result]);
                return;
            }

            ydoc.transact(() => {
                if (submittedForm.id === 'add-item-form') {
                    if (!items.has(String(result.itemId))) {
                        items.set(String(result.itemId), readItem(result.itemId));
                    }
                } else if (submittedForm.classList.contains('delete-item-form')) {
                    items.delete(submittedForm.closest('[id^="edit-item-"]')?.id.replace('edit-item-', '') ?? submittedForm.action.match(/\/items\/(\d+)/)?.[1]);
                } else if (submittedForm.id === 'add-flag-form') {
                    if (!flags.has(String(result.flagId))) {
                        flags.set(String(result.flagId), readFlag(result.flagId));
                    }
                } else if (submittedForm.classList.contains('delete-flag-form')) {
                    const deletedId = submittedForm.action.match(/\/flags\/(\d+)/)?.[1];
                    flags.delete(deletedId);
                    for (const record of items.values()) {
                        record.get('flagIds').delete(deletedId);
                    }
                } else {
                    updateFromInputs();
                }
            });
        },
    };

    function updateFromInputs() {
        updateText(menu.get('name'), document.getElementById('name').value);
        menu.set('isPublished', document.getElementById('is-published').value === '1');
        for (const [id, record] of items) {
            if (!document.getElementById(`edit-item-${id}`)) {
                continue;
            }
            updateText(record.get('name'), document.getElementById(`item-name-${id}`).value);
            const price = document.getElementById(`item-price-${id}`).value;
            record.set('price', price === '' ? null : Number(price));
            const selectedFlags = Array.from(document.querySelectorAll(`#edit-item-${id} fieldset input:checked`), (input) => Number(input.value));
            updateSelections(record.get('flagIds'), selectedFlags);
        }
        for (const [id, record] of flags) {
            if (document.getElementById(`flag-name-${id}`)) {
                updateText(record.get('name'), document.getElementById(`flag-name-${id}`).value);
            }
        }
    }

    function setValue(input, value) {
        if (!input || input.value === String(value)) {
            return;
        }
        const active = document.activeElement === input;
        const start = active && input.type === 'text' ? input.selectionStart : null;
        input.value = String(value);
        if (start !== null) {
            input.setSelectionRange(Math.min(start, input.value.length), Math.min(start, input.value.length));
        }
    }

    async function refreshStructure() {
        if (refreshing) {
            return;
        }
        refreshing = true;
        try {
            await window.menuGachaRefresh?.();
            render();
        } finally {
            refreshing = false;
        }
    }

    function render() {
        if (!ready) {
            return;
        }
        setValue(document.getElementById('name'), menu.get('name').toString());
        setValue(document.getElementById('is-published'), menu.get('isPublished') ? '1' : '0');

        const currentItemIds = Array.from(document.querySelectorAll('#edit-items > [id^="edit-item-"]'), (row) => row.id.slice(10));
        const currentFlagIds = Array.from(document.querySelectorAll('#edit-flags > [id^="edit-flag-"]'), (row) => row.id.slice(10));
        if (currentItemIds.length !== items.size || currentItemIds.some((id) => !items.has(id))
            || currentFlagIds.length !== flags.size || currentFlagIds.some((id) => !flags.has(id))) {
            refreshStructure().catch(() => { status.textContent = '画面の更新に失敗しました'; });
            return;
        }

        for (const [id, record] of items) {
            setValue(document.getElementById(`item-name-${id}`), record.get('name').toString());
            setValue(document.getElementById(`item-price-${id}`), record.get('price') ?? '');
            const selected = record.get('flagIds');
            for (const input of document.querySelectorAll(`#edit-item-${id} fieldset input`)) {
                input.checked = selected.has(input.value);
            }
        }
        for (const [id, record] of flags) {
            setValue(document.getElementById(`flag-name-${id}`), record.get('name').toString());
        }
    }

    ydoc.on('update', () => {
        if (ready) {
            status.textContent = provider.isSynced ? '自動保存中…' : 'オフライン：再接続後に同期します';
            render();
        }
    });

    document.getElementById('edit-panel').addEventListener('input', (event) => {
        if (!ready || !event.target.matches('[form="menu-gacha-edit-form"]')) {
            return;
        }
        ydoc.transact(updateFromInputs);
    });
    document.getElementById('edit-panel').addEventListener('change', (event) => {
        if (!ready || !event.target.matches('[form="menu-gacha-edit-form"]')) {
            return;
        }
        ydoc.transact(updateFromInputs);
    });
}
