import assert from 'node:assert/strict';
import test from 'node:test';
import { pull_gacha_by_menu } from '../../public/js/menu-gacha.js';

const items = [
    { name: 'A定食', price: 250 },
    { name: 'B定食', price: 400 },
    { name: 'C定食', price: 600 },
];

test('抽選結果の合計金額は指定予算を超えない', () => {
    const budget = 850;
    const result = pull_gacha_by_menu(items, budget, () => 0);

    assert.ok(result.totalPrice <= budget);
    assert.equal(result.totalPrice, result.items.reduce((total, item) => total + item.price, 0));
});

test('抽選結果には予算内で追加できる項目が残らない', () => {
    const budget = 850;
    const result = pull_gacha_by_menu(items, budget, () => 0);

    assert.ok(result.items.length > 0);
    assert.ok(items.every((item) => result.totalPrice + item.price > budget));
});
