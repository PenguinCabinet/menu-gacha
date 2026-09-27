import assert from 'node:assert/strict';
import test from 'node:test';
import { MAX_GACHA_SELECTIONS, filter_gacha_items_by_flags, pull_gacha_by_menu } from '../../public/js/menu-gacha.js';

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

test('選出数が1万件を超える場合は例外で停止する', () => {
    assert.throws(
        () => pull_gacha_by_menu([{ name: '日替わり', price: 1 }], Number.MAX_SAFE_INTEGER, () => 0),
        RangeError,
    );
});

test('すべてのフラグがオンなら全項目が抽選対象になる', () => {
    const flaggedItems = [
        { name: '学割定食', price: 500, flagIds: [1] },
        { name: '限定定食', price: 550, flagIds: [2] },
        { name: '通常定食', price: 600, flagIds: [] },
    ];

    assert.deepEqual(filter_gacha_items_by_flags(flaggedItems, [1, 2]), flaggedItems);
});

test('オフのフラグを持つ項目は除外し、オンのフラグとフラグなしの項目は残す', () => {
    const flaggedItems = [
        { name: '学割ランチ', price: 500, flagIds: [1, 2] },
        { name: '学割のみ', price: 600, flagIds: [1] },
        { name: '限定のみ', price: 700, flagIds: [2] },
        { name: 'フラグなし', price: 800, flagIds: [] },
    ];
    const eligibleItems = filter_gacha_items_by_flags(flaggedItems, [1]);

    assert.deepEqual(eligibleItems.map((item) => item.name), ['学割のみ', 'フラグなし']);
    assert.deepEqual(pull_gacha_by_menu(eligibleItems, 600, () => 0).items.map((item) => item.name), ['学割のみ']);
    assert.deepEqual(filter_gacha_items_by_flags(flaggedItems, [2]).map((item) => item.name), ['限定のみ', 'フラグなし']);
});

test('すべてオフの場合はフラグなしの項目だけを残す', () => {
    const flaggedItems = [
        { name: '学割定食', price: 500, flagIds: [1] },
        { name: '通常定食', price: 600, flagIds: [] },
    ];

    assert.deepEqual(filter_gacha_items_by_flags(flaggedItems, []).map((item) => item.name), ['通常定食']);
    assert.deepEqual(filter_gacha_items_by_flags([flaggedItems[0]], []), []);
});
