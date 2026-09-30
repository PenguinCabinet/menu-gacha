export const MAX_GACHA_SELECTIONS = 10_000;

export function filter_gacha_items_by_flags(items, enabledFlagIds) {
    return items.filter((item) => (item.flagIds ?? []).every((flagId) => enabledFlagIds.includes(flagId)));
}

export function pull_gacha_by_menu(items, budget, random = Math.random) {
    const selectedItems = [];
    const selectedFreeItemIndexes = new Set();
    let totalPrice = 0;

    while (true) {
        const affordableItems = items
            .map((item, index) => ({ item, index, price: Number(item.price) }))
            .filter(({ index, price }) => (
                totalPrice + price <= budget && (price > 0 || !selectedFreeItemIndexes.has(index))
            ));

        if (affordableItems.length === 0) {
            break;
        }

        if (selectedItems.length >= MAX_GACHA_SELECTIONS) {
            throw new RangeError(`Gacha selection exceeded ${MAX_GACHA_SELECTIONS} items.`);
        }

        const selection = affordableItems[Math.floor(random() * affordableItems.length)];
        selectedItems.push(selection.item);
        totalPrice += selection.price;

        if (selection.price === 0) {
            selectedFreeItemIndexes.add(selection.index);
        }
    }

    return { items: selectedItems, totalPrice };
}

if (typeof document !== 'undefined') {
    let gachaItems = JSON.parse(document.getElementById('gacha-items').textContent);
    const gachaBudgetInput = document.getElementById('gacha-budget');
    const gachaResult = document.getElementById('gacha-result');
    const gachaTotal = document.getElementById('gacha-total');

    const editForm = document.getElementById('menu-gacha-edit-form');
    if (editForm) {
        const status = document.getElementById('edit-save-status');
        const saveButton = document.querySelector('[form="menu-gacha-edit-form"][type="submit"]');
        const itemList = document.getElementById('edit-items');
        const flagList = document.getElementById('edit-flags');
        const addItemForm = document.getElementById('add-item-form');
        const addFlagForm = document.getElementById('add-flag-form');

        function syncRows(currentList, updatedList) {
            for (const row of currentList.querySelectorAll(':scope > [id]')) {
                if (!updatedList.querySelector(`[id="${row.id}"]`)) {
                    row.remove();
                }
            }
            currentList.querySelector(':scope > p')?.remove();
            for (const row of updatedList.children) {
                if (row.id && !document.getElementById(row.id)) {
                    currentList.append(row.cloneNode(true));
                }
            }
            if (!currentList.children.length) {
                currentList.append(updatedList.querySelector('p').cloneNode(true));
            }
        }

        function syncFlagFieldset(currentContainer, updatedContainer) {
            const currentFieldset = currentContainer.querySelector('fieldset');
            const updatedFieldset = updatedContainer.querySelector('fieldset');

            if (!updatedFieldset) {
                currentFieldset?.remove();
                return;
            }

            const selectedFlags = new Set(Array.from(currentFieldset?.querySelectorAll('input:checked') ?? [], (input) => input.value));
            for (const input of updatedFieldset.querySelectorAll('input')) {
                input.checked = selectedFlags.has(input.value);
            }

            if (currentFieldset) {
                currentFieldset.replaceWith(updatedFieldset);
            } else {
                currentContainer.append(updatedFieldset);
            }
        }

        function applyUpdatedPage(updatedPage) {
            syncRows(itemList, updatedPage.getElementById('edit-items'));
            syncRows(flagList, updatedPage.getElementById('edit-flags'));
            document.querySelector('.card-header h1').textContent = updatedPage.querySelector('.card-header h1').textContent;
            document.title = updatedPage.title;
            document.getElementById('preview-panel').innerHTML = updatedPage.getElementById('preview-panel').innerHTML;

            const previousFlags = document.getElementById('gacha-flags');
            const selectedFlags = new Set(Array.from(previousFlags?.querySelectorAll('input:checked') ?? [], (input) => input.value));
            const previousFlagIds = new Set(Array.from(previousFlags?.querySelectorAll('input') ?? [], (input) => input.value));
            const updatedFlags = updatedPage.getElementById('gacha-flags');
            if (updatedFlags) {
                for (const input of updatedFlags.querySelectorAll('input')) {
                    input.checked = !previousFlagIds.has(input.value) || selectedFlags.has(input.value);
                }
                if (previousFlags) {
                    previousFlags.replaceWith(updatedFlags);
                } else {
                    document.getElementById('gacha-result').before(updatedFlags);
                }
            } else {
                previousFlags?.remove();
            }

            for (const row of itemList.querySelectorAll(':scope > [id^="edit-item-"]')) {
                syncFlagFieldset(row, updatedPage.getElementById(row.id));
            }
            syncFlagFieldset(addItemForm.querySelector('.row'), updatedPage.querySelector('#add-item-form .row'));
            gachaItems = JSON.parse(updatedPage.getElementById('gacha-items').textContent);
        }

        async function fetchUpdatedPage() {
            const response = await fetch(window.location.pathname, { headers: { Accept: 'text/html' } });
            if (!response.ok) {
                throw new Error('Could not refresh menu data.');
            }
            applyUpdatedPage(new DOMParser().parseFromString(await response.text(), 'text/html'));
        }

        window.menuGachaRefresh = fetchUpdatedPage;

        async function submitMenuForm(form, button) {
            button.disabled = true;
            status.className = 'position-fixed end-0 m-3 m-md-4 shadow-sm';
            status.replaceChildren();

            try {
                const response = await fetch(form.action, {
                    method: 'POST',
                    body: new FormData(form),
                    headers: { Accept: 'application/json' },
                });
                const result = await response.json();

                if (!response.ok) {
                    status.classList.add('alert', 'alert-danger');
                    const errors = Object.values(result.errors ?? {}).flat();
                    status.textContent = errors.length ? errors.join(' ') : '保存できませんでした。もう一度お試しください。';
                    return;
                }

                await fetchUpdatedPage();
                if (form === addItemForm || form === addFlagForm) {
                    form.reset();
                }
                window.menuGachaSync?.afterFormSuccess(form, result);
                status.classList.add('alert', 'alert-success');
                status.textContent = result.message;
            } catch (error) {
                status.classList.add('alert', 'alert-danger');
                status.textContent = '通信に失敗しました。保存結果を確認してください。';
            } finally {
                button.disabled = false;
            }
        }

        editForm.addEventListener('submit', (event) => {
            event.preventDefault();
            submitMenuForm(editForm, saveButton);
        });

        addItemForm.addEventListener('submit', (event) => {
            event.preventDefault();
            submitMenuForm(addItemForm, addItemForm.querySelector('[type="submit"]'));
        });

        itemList.addEventListener('submit', (event) => {
            const form = event.target.closest('.delete-item-form');
            if (!form || event.defaultPrevented) {
                return;
            }

            event.preventDefault();
            submitMenuForm(form, form.querySelector('[type="submit"]'));
        });

        addFlagForm.addEventListener('submit', (event) => {
            event.preventDefault();
            submitMenuForm(addFlagForm, addFlagForm.querySelector('[type="submit"]'));
        });

        flagList.addEventListener('submit', (event) => {
            const form = event.target.closest('.delete-flag-form');
            if (!form || event.defaultPrevented) {
                return;
            }

            event.preventDefault();
            submitMenuForm(form, form.querySelector('[type="submit"]'));
        });
    }

    document.getElementById('run-gacha').addEventListener('click', () => {
        const budget = Number(gachaBudgetInput.value);

        gachaResult.replaceChildren();
        gachaTotal.textContent = '';

        if (!Number.isSafeInteger(budget) || budget < 0) {
            gachaResult.textContent = '0円以上の予算を入力してください。';
            return;
        }

        if (gachaItems.length === 0) {
            gachaResult.textContent = '食事がまだ登録されていません。';
            return;
        }

        const enabledFlagIds = Array.from(document.querySelectorAll('#gacha-flags input[type="checkbox"]'))
            .filter((checkbox) => checkbox.checked)
            .map((checkbox) => Number(checkbox.value));
        const eligibleItems = filter_gacha_items_by_flags(gachaItems, enabledFlagIds);

        if (eligibleItems.length === 0) {
            gachaResult.textContent = '現在のフラグ設定で選べる食事がありません。';
            return;
        }

        let selectedItems;
        let totalPrice;

        try {
            ({ items: selectedItems, totalPrice } = pull_gacha_by_menu(eligibleItems, budget));
        } catch (error) {
            if (!(error instanceof RangeError)) {
                throw error;
            }

            gachaResult.textContent = `数が上限の${MAX_GACHA_SELECTIONS.toLocaleString()}件を超えるため、ガチャを中止しました。(フリーズを防止)`;
            return;
        }

        if (selectedItems.length === 0) {
            gachaResult.textContent = '予算内で選べる食事がありません。';
            return;
        }

        const list = document.createElement('div');
        list.className = 'd-flex flex-column gap-2';

        for (const item of selectedItems) {
            const row = document.createElement('div');
            row.className = 'd-flex justify-content-between border-bottom py-2';

            const name = document.createElement('span');
            name.textContent = item.name;

            const price = document.createElement('span');
            price.textContent = `${Number(item.price).toLocaleString()}円`;

            row.append(name, price);
            list.append(row);
        }

        gachaResult.append(list);
        gachaTotal.textContent = `合計金額：${totalPrice.toLocaleString()}円`;
    });
}
