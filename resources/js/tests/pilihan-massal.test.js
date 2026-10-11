import test from 'node:test';
import assert from 'node:assert/strict';
import { pilihanMassal } from '../pilihan-massal.js';

test('1. Awal: tidak ada terpilih, semuaTerpilih false, sebagian false', () => {
    const pm = pilihanMassal([1, 2, 3]);

    assert.equal(pm.jumlah, 0);
    assert.deepEqual(pm.terpilih, []);
    assert.equal(pm.semuaTerpilih, false);
    assert.equal(pm.sebagian, false);
});

test('2. toggleSemua(true) memilih semua dan semuaTerpilih true', () => {
    const pm = pilihanMassal([10, 20, 30]);

    pm.toggleSemua(true);
    assert.equal(pm.jumlah, 3);
    assert.deepEqual(pm.terpilih, ['10', '20', '30']);
    assert.equal(pm.semuaTerpilih, true);
    assert.equal(pm.sebagian, false);
});

test('3. toggleSemua(false) mengosongkan dan semuaTerpilih false (REGRESI inversi)', () => {
    const pm = pilihanMassal(['a', 'b']);
    pm.toggleSemua(true);
    assert.equal(pm.semuaTerpilih, true);

    // Saat diklik false, harus mengosongkan dan bukan membalik status sembarangan
    pm.toggleSemua(false);
    assert.equal(pm.jumlah, 0);
    assert.deepEqual(pm.terpilih, []);
    assert.equal(pm.semuaTerpilih, false);
    assert.equal(pm.sebagian, false);

    // Memanggil toggleSemua(false) lagi saat sudah kosong tetap kosong (idempotent)
    pm.toggleSemua(false);
    assert.equal(pm.semuaTerpilih, false);
    assert.equal(pm.sebagian, false);
});

test('4. Memilih satu per satu sampai semua membuat semuaTerpilih true', () => {
    const pm = pilihanMassal(['1', '2']);

    pm.toggleSatu('1');
    assert.equal(pm.jumlah, 1);
    assert.equal(pm.sebagian, true);
    assert.equal(pm.semuaTerpilih, false);

    pm.toggleSatu('2');
    assert.equal(pm.jumlah, 2);
    assert.equal(pm.sebagian, false);
    assert.equal(pm.semuaTerpilih, true);
});

test('5. Melepas satu membuat sebagian true dan semuaTerpilih false', () => {
    const pm = pilihanMassal(['x', 'y', 'z']);
    pm.toggleSemua(true);
    assert.equal(pm.semuaTerpilih, true);
    assert.equal(pm.sebagian, false);

    pm.toggleSatu('y');
    assert.equal(pm.jumlah, 2);
    assert.equal(pm.sebagian, true);
    assert.equal(pm.semuaTerpilih, false);
    assert.ok(pm.terpilih.includes('x'));
    assert.ok(!pm.terpilih.includes('y'));
    assert.ok(pm.terpilih.includes('z'));
});

test('6. idTersedia kosong tidak pernah semuaTerpilih dan tidak pernah sebagian', () => {
    const pm = pilihanMassal([]);

    assert.equal(pm.semuaTerpilih, false);
    assert.equal(pm.sebagian, false);

    pm.toggleSemua(true);
    assert.equal(pm.semuaTerpilih, false);
    assert.equal(pm.sebagian, false);
});

test('7. id angka dan string diperlakukan sama secara konsisten', () => {
    const pm = pilihanMassal([101, 102]);

    // toggle dengan string saat idTersedia angka
    pm.toggleSatu('101');
    assert.ok(pm.terpilih.includes('101'));
    assert.equal(pm.sebagian, true);

    // toggle dengan angka untuk melepas
    pm.toggleSatu(101);
    assert.equal(pm.jumlah, 0);
    assert.equal(pm.sebagian, false);

    // toggle dengan angka
    pm.toggleSatu(102);
    assert.ok(pm.terpilih.includes('102'));
    assert.equal(pm.sebagian, true);
});

test('8. setTersedia membuang pilihan yang tidak lagi terlihat (filter pencarian)', () => {
    const pm = pilihanMassal([1, 2, 3, 4]);
    pm.toggleSemua(true);
    assert.equal(pm.jumlah, 4);

    // Filter mencari hanya 2 dan 3
    pm.setTersedia([2, 3]);
    assert.equal(pm.jumlah, 2);
    assert.deepEqual(pm.terpilih, ['2', '3']);
    assert.equal(pm.semuaTerpilih, true);
    assert.equal(pm.sebagian, false);

    // Filter tidak menemukan apa-apa
    pm.setTersedia([]);
    assert.equal(pm.jumlah, 0);
    assert.deepEqual(pm.terpilih, []);
    assert.equal(pm.semuaTerpilih, false);
    assert.equal(pm.sebagian, false);
});

test('9. reset mengosongkan semua terpilih', () => {
    const pm = pilihanMassal([5, 6]);
    pm.toggleSemua(true);
    assert.equal(pm.jumlah, 2);

    pm.reset();
    assert.equal(pm.jumlah, 0);
    assert.deepEqual(pm.terpilih, []);
    assert.equal(pm.semuaTerpilih, false);
    assert.equal(pm.sebagian, false);
});

test('10. Dukungan terpilihAwal dan properti ekstra', () => {
    const pm = pilihanMassal([1, 2], [1, 2], {
        search: '',
        confirmModal: false,
    });

    assert.equal(pm.jumlah, 2);
    assert.equal(pm.semuaTerpilih, true);
    assert.equal(pm.confirmModal, false);
    assert.equal(pm.search, '');
});
