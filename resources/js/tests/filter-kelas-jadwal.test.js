import { describe, it } from 'node:test';
import assert from 'node:assert/strict';
import { saringKelas, validasiPilihanKelas, kelolaFilterKelas } from '../filter-kelas-jadwal.js';

describe('Filter Kelas Jadwal (Logika Murni)', () => {
    const mockKelas = [
        { id: 1, nama: '10 RPL 1', tahun_ajaran: '2026/2027', semester: 'Ganjil' },
        { id: 2, nama: '10 RPL 2', tahun_ajaran: '2026/2027', semester: 'Ganjil' },
        { id: 3, nama: '10 RPL 1', tahun_ajaran: '2026/2027', semester: 'Genap' },
        { id: 4, nama: '10 TKJ 1', tahun_ajaran: '2025/2026', semester: 'Genap' },
    ];

    it('1. saringKelas mengembalikan hanya kelas dengan tahun_ajaran dan semester yang cocok', () => {
        const hasilGanjil = saringKelas(mockKelas, '2026/2027', 'Ganjil');
        assert.equal(hasilGanjil.length, 2);
        assert.equal(hasilGanjil[0].id, 1);
        assert.equal(hasilGanjil[1].id, 2);

        const hasilGenap = saringKelas(mockKelas, '2026/2027', 'Genap');
        assert.equal(hasilGenap.length, 1);
        assert.equal(hasilGenap[0].id, 3);
    });

    it('2. saringKelas case-insensitive terhadap semester dan menangani whitespace', () => {
        const hasil = saringKelas(mockKelas, ' 2026/2027 ', ' ganjil ');
        assert.equal(hasil.length, 2);
    });

    it('3. saringKelas mengembalikan array kosong jika periode tidak memiliki kelas', () => {
        const hasil = saringKelas(mockKelas, '2024/2025', 'Ganjil');
        assert.equal(hasil.length, 0);
    });

    it('4. saringKelas menangani input kosong atau tidak valid dengan aman', () => {
        assert.deepEqual(saringKelas(null, '2026/2027', 'Ganjil'), []);
        assert.deepEqual(saringKelas([], '', ''), []);
        assert.deepEqual(saringKelas(mockKelas, '', 'Ganjil'), []);
        assert.deepEqual(saringKelas(mockKelas, '2026/2027', ''), []);
    });

    it('5. validasiPilihanKelas mengembalikan true jika id kelas cocok ada dalam daftar', () => {
        const cocok = saringKelas(mockKelas, '2026/2027', 'Ganjil');
        assert.equal(validasiPilihanKelas(cocok, 1), true);
        assert.equal(validasiPilihanKelas(cocok, '2'), true);
        assert.equal(validasiPilihanKelas(cocok, 3), false);
        assert.equal(validasiPilihanKelas(cocok, ''), false);
    });

    it('6. kelolaFilterKelas: inisialisasi awal dan komputasi kelasCocok', () => {
        const state = kelolaFilterKelas({
            daftarKelas: mockKelas,
            tahunAjaranAwal: '2026/2027',
            semesterAwal: 'Ganjil',
            kelasIdAwal: '1',
        });

        assert.equal(state.adaKelas, true);
        assert.equal(state.kelasCocok.length, 2);
        assert.equal(state.kelasId, '1');
        assert.equal(state.pesanPilihUlang, false);
    });

    it('7. kelolaFilterKelas: mengganti periode mengosongkan kelasId dan mengaktifkan pesanPilihUlang jika tidak cocok', () => {
        const state = kelolaFilterKelas({
            daftarKelas: mockKelas,
            tahunAjaranAwal: '2026/2027',
            semesterAwal: 'Ganjil',
            kelasIdAwal: '1', // 10 RPL 1 Ganjil
        });

        // Ubah semester ke Genap (id: 1 tidak ada di Genap, yang ada id: 3)
        state.semester = 'Genap';
        state.onPeriodeChange();

        assert.equal(state.kelasId, '');
        assert.equal(state.pesanPilihUlang, true);
        assert.equal(state.adaKelas, true);
        assert.equal(state.kelasCocok.length, 1);
        assert.equal(state.kelasCocok[0].id, 3);
    });

    it('8. kelolaFilterKelas: mengganti periode mempertahankan kelasId jika kelasId masih ada di periode baru', () => {
        // Anggap ada 2 kelas di periode yang sama
        const state = kelolaFilterKelas({
            daftarKelas: mockKelas,
            tahunAjaranAwal: '2026/2027',
            semesterAwal: 'Ganjil',
            kelasIdAwal: '1',
        });

        // Trigger onPeriodeChange tanpa mengubah periode
        state.onPeriodeChange();

        assert.equal(state.kelasId, '1');
        assert.equal(state.pesanPilihUlang, false);
    });

    it('9. kelolaFilterKelas: memilih kelas baru mereset pesanPilihUlang', () => {
        const state = kelolaFilterKelas({
            daftarKelas: mockKelas,
            tahunAjaranAwal: '2026/2027',
            semesterAwal: 'Ganjil',
            kelasIdAwal: '1',
        });

        state.semester = 'Genap';
        state.onPeriodeChange();
        assert.equal(state.pesanPilihUlang, true);

        state.onKelasChange('3');
        assert.equal(state.kelasId, '3');
        assert.equal(state.pesanPilihUlang, false);
    });

    it('10. kelolaFilterKelas: periode tanpa kelas menyajikan adaKelas = false', () => {
        const state = kelolaFilterKelas({
            daftarKelas: mockKelas,
            tahunAjaranAwal: '2023/2024',
            semesterAwal: 'Ganjil',
            kelasIdAwal: '',
        });

        assert.equal(state.adaKelas, false);
        assert.equal(state.kelasCocok.length, 0);
    });
});

