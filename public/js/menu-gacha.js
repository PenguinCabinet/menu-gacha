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

        editForm.addEventListener('submit', async (event) => {
            event.preventDefault();
            saveButton.disabled = true;
            status.className = 'position-fixed end-0 m-3 m-md-4 shadow-sm';
            status.replaceChildren();

            try {
                const response = await fetch(editForm.action, {
                    method: 'POST',
                    body: new FormData(editForm),
                    headers: { Accept: 'application/json' },
                });
                const result = await response.json();

                if (!response.ok) {
                    status.classList.add('alert', 'alert-danger');
                    const errors = Object.values(result.errors ?? {}).flat();
                    status.textContent = errors.length ? errors.join(' ') : '保存できませんでした。もう一度お試しください。';
                    return;
                }

                const pageResponse = await fetch(window.location.pathname, { headers: { Accept: 'text/html' } });
                if (!pageResponse.ok) {
                    throw new Error('Could not refresh menu data.');
                }

                const updatedPage = new DOMParser().parseFromString(await pageResponse.text(), 'text/html');
                document.querySelector('.card-header h1').textContent = updatedPage.querySelector('.card-header h1').textContent;
                document.title = updatedPage.title;
                document.getElementById('preview-panel').innerHTML = updatedPage.getElementById('preview-panel').innerHTML;

                const previousFlags = document.getElementById('gacha-flags');
                const selectedFlags = new Set(Array.from(previousFlags?.querySelectorAll('input:checked') ?? [], (input) => input.value));
                const updatedFlags = updatedPage.getElementById('gacha-flags');
                if (updatedFlags) {
                    for (const input of updatedFlags.querySelectorAll('input')) {
                        input.checked = selectedFlags.has(input.value);
                    }
                    if (previousFlags) {
                        previousFlags.replaceWith(updatedFlags);
                    } else {
                        document.getElementById('gacha-result').before(updatedFlags);
                    }
                } else {
                    previousFlags?.remove();
                }

                for (const label of document.querySelectorAll('#edit-panel label[for^="item-"]')) {
                    const updatedLabel = updatedPage.getElementById('edit-panel').querySelector(`label[for="${label.htmlFor}"]`);
                    if (updatedLabel) {
                        label.textContent = updatedLabel.textContent;
                    }
                }

                gachaItems = JSON.parse(updatedPage.getElementById('gacha-items').textContent);
                status.classList.add('alert', 'alert-success');
                status.textContent = result.message;
            } catch (error) {
                status.classList.add('alert', 'alert-danger');
                status.textContent = '通信に失敗しました。保存結果を確認してください。';
            } finally {
                saveButton.disabled = false;
            }
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
