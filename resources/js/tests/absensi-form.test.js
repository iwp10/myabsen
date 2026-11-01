import test from 'node:test';
import assert from 'node:assert/strict';
import {
    hitungRingkasan,
    setSemuaStatus,
    setSemuaHadir,
    setSemuaAlpa,
    ubahStatusSiswa
} from '../absensi-form.js';

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

test('4. setSemuaStatus("alpa") dan setSemuaAlpa mengubah seluruh siswa menjadi alpa dan ringkasan benar', () => {
    const students = {
        1: { status: 'hadir', keterangan: '' },
        2: { status: 'izin', keterangan: 'Acara dinas keluarga' },
        3: { status: 'sakit', keterangan: 'Radang' },
    };

    const hasil = setSemuaStatus(students, 'alpa');

    assert.equal(hasil[1].status, 'alpa');
    assert.equal(hasil[2].status, 'alpa');
    assert.equal(hasil[2].keterangan, 'Acara dinas keluarga'); // Keterangan tidak hilang
    assert.equal(hasil[3].status, 'alpa');
    assert.equal(hasil[3].keterangan, 'Radang');

    const summary = hitungRingkasan(hasil);
    assert.equal(summary.alpa, 3);
    assert.equal(summary.hadir, 0);
    assert.equal(summary.izin, 0);
    assert.equal(summary.sakit, 0);
    assert.equal(summary.total, 3);

    // Pengujian melalui pembungkus setSemuaAlpa
    const hasilWrapper = setSemuaAlpa(students);
    assert.deepEqual(hasilWrapper, hasil);
});

test('5. setSemuaStatus("hadir") berperilaku identik dengan setSemuaHadir dan mempertahankan keterangan', () => {
    const students = {
        1: { status: 'alpa', keterangan: 'Tanpa surat' },
        2: { status: 'sakit', keterangan: 'Pusing' },
    };

    const viaStatus = setSemuaStatus(students, 'hadir');
    const viaHadir = setSemuaHadir(students);

    assert.deepEqual(viaStatus, viaHadir);
    assert.equal(viaStatus[1].status, 'hadir');
    assert.equal(viaStatus[1].keterangan, 'Tanpa surat');
    assert.equal(viaStatus[2].status, 'hadir');
    assert.equal(viaStatus[2].keterangan, 'Pusing');

    const summary = hitungRingkasan(viaStatus);
    assert.equal(summary.hadir, 2);
    assert.equal(summary.alpa, 0);
});

test('6. setSemuaStatus dan setSemuaAlpa menangani data kosong atau tidak valid dengan aman', () => {
    assert.deepEqual(setSemuaStatus({}, 'alpa'), {});
    assert.deepEqual(setSemuaStatus(null, 'alpa'), {});
    assert.deepEqual(setSemuaStatus(undefined, 'alpa'), {});
    assert.deepEqual(setSemuaAlpa({}), {});
    assert.deepEqual(setSemuaAlpa(null), {});
    assert.deepEqual(setSemuaAlpa(undefined), {});
});

test('7. cakupan setSemuaStatus mencakup seluruh siswa dalam kelas (Langkah 0a), tidak terpengaruh filter pencarian', () => {
    // Simulasi daftar 4 siswa di kelas
    const students = {
        101: { status: 'hadir', keterangan: '' },
        102: { status: 'hadir', keterangan: '' },
        103: { status: 'izin', keterangan: 'Izin lomba' },
        104: { status: 'hadir', keterangan: '' },
    };

    // Simulasi pencarian hanya menampilkan siswa 103 (3 siswa lain disembunyikan di UI)
    const searchQuery = '103';
    const idTerlihat = Object.keys(students).filter(id => id.includes(searchQuery));
    assert.equal(idTerlihat.length, 1);

    // Tindakan massal tetap memengaruhi seluruh siswa dalam daftar kelas sesuai Langkah 0a
    const hasilAlpa = setSemuaStatus(students, 'alpa');
    assert.equal(Object.keys(hasilAlpa).length, 4);
    assert.equal(hasilAlpa[101].status, 'alpa');
    assert.equal(hasilAlpa[102].status, 'alpa');
    assert.equal(hasilAlpa[103].status, 'alpa');
    assert.equal(hasilAlpa[103].keterangan, 'Izin lomba'); // Keterangan tetap utuh
    assert.equal(hasilAlpa[104].status, 'alpa');

    const summaryAlpa = hitungRingkasan(hasilAlpa);
    assert.equal(summaryAlpa.alpa, 4);
    assert.equal(summaryAlpa.hadir, 0);

    // Begitu pula saat dikembalikan ke Hadir
    const hasilHadir = setSemuaStatus(hasilAlpa, 'hadir');
    assert.equal(hasilHadir[101].status, 'hadir');
    assert.equal(hasilHadir[102].status, 'hadir');
    assert.equal(hasilHadir[103].status, 'hadir');
    assert.equal(hasilHadir[103].keterangan, 'Izin lomba');
    assert.equal(hasilHadir[104].status, 'hadir');

    const summaryHadir = hitungRingkasan(hasilHadir);
    assert.equal(summaryHadir.hadir, 4);
    assert.equal(summaryHadir.alpa, 0);
});

test('8. ubahStatusSiswa mengubah status siswa tertentu tanpa mempengaruhi siswa lain', () => {
    const students = {
        1: { status: 'hadir', keterangan: '' },
        2: { status: 'hadir', keterangan: '' }
    };

    const hasil = ubahStatusSiswa(students, 2, 'izin');
    assert.equal(hasil[1].status, 'hadir');
    assert.equal(hasil[2].status, 'izin');
});
