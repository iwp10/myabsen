import test from 'node:test';
import assert from 'node:assert/strict';
import { hitungRingkasan, setSemuaHadir, ubahStatusSiswa } from '../absensi-form.js';

test('1. hitungRingkasan menghitung jumlah H, I, S, A dan total siswa dengan benar', () => {
    const students = {
        1: { status: 'hadir', keterangan: '' },
        2: { status: 'hadir', keterangan: '' },
        3: { status: 'izin', keterangan: 'Acara keluarga' },
        4: { status: 'sakit', keterangan: 'Demam tinggi' },
        5: { status: 'alpa', keterangan: '' },
    };

    const summary = hitungRingkasan(students);
    assert.deepEqual(summary, {
        hadir: 2,
        izin: 1,
        sakit: 1,
        alpa: 1,
        total: 5
    });
});

test('2. hitungRingkasan menangani data kosong atau tidak valid dengan aman', () => {
    assert.deepEqual(hitungRingkasan({}), { hadir: 0, izin: 0, sakit: 0, alpa: 0, total: 0 });
    assert.deepEqual(hitungRingkasan(null), { hadir: 0, izin: 0, sakit: 0, alpa: 0, total: 0 });
    assert.deepEqual(hitungRingkasan(undefined), { hadir: 0, izin: 0, sakit: 0, alpa: 0, total: 0 });
});

test('3. setSemuaHadir mengubah seluruh status menjadi hadir dan mempertahankan keterangan', () => {
    const students = {
        10: { status: 'alpa', keterangan: 'Tidak ada kabar' },
        11: { status: 'sakit', keterangan: 'Flu' },
        12: { status: 'hadir', keterangan: '' },
    };

    const hasil = setSemuaHadir(students);

    assert.equal(hasil[10].status, 'hadir');
    assert.equal(hasil[10].keterangan, 'Tidak ada kabar');
    assert.equal(hasil[11].status, 'hadir');
    assert.equal(hasil[11].keterangan, 'Flu');
    assert.equal(hasil[12].status, 'hadir');

    // Ringkasan setelah semua hadir
    const summary = hitungRingkasan(hasil);
    assert.equal(summary.hadir, 3);
    assert.equal(summary.izin, 0);
    assert.equal(summary.sakit, 0);
    assert.equal(summary.alpa, 0);
    assert.equal(summary.total, 3);
});

test('4. ubahStatusSiswa mengubah status siswa tertentu tanpa mempengaruhi siswa lain', () => {
    const students = {
        1: { status: 'hadir', keterangan: '' },
        2: { status: 'hadir', keterangan: '' }
    };

    const hasil = ubahStatusSiswa(students, 2, 'izin');
    assert.equal(hasil[1].status, 'hadir');
    assert.equal(hasil[2].status, 'izin');
});
