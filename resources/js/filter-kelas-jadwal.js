/**
 * Modul murni penyaringan daftar kelas berdasarkan tahun ajaran dan semester pada form jadwal.
 */

/**
 * Menyaring daftar kelas yang memiliki tahun ajaran dan semester yang sama persis.
 *
 * @param {Array<Object>} daftarKelas
 * @param {string} tahunAjaran
 * @param {string} semester
 * @returns {Array<Object>}
 */
export function saringKelas(daftarKelas = [], tahunAjaran = '', semester = '') {
    if (!Array.isArray(daftarKelas)) {
        return [];
    }

    const ta = String(tahunAjaran || '').trim();
    const sem = String(semester || '').trim().toLowerCase();

    if (!ta || !sem) {
        return [];
    }

    return daftarKelas.filter(k => {
        if (!k) return false;
        const kTa = String(k.tahun_ajaran || '').trim();
        const kSem = String(k.semester || '').trim().toLowerCase();
        return kTa === ta && kSem === sem;
    });
}

/**
 * Memeriksa apakah kelasId yang dipilih masih ada dalam daftar kelas yang cocok.
 *
 * @param {Array<Object>} daftarKelasCocok
 * @param {string|number} kelasId
 * @returns {boolean}
 */
export function validasiPilihanKelas(daftarKelasCocok = [], kelasId = '') {
    if (!kelasId || !Array.isArray(daftarKelasCocok)) {
        return false;
    }
    const idStr = String(kelasId);
    return daftarKelasCocok.some(k => String(k.id) === idStr);
}

/**
 * State manager untuk Alpine.js pada form jadwal (create & edit).
 *
 * @param {Object} config
 * @returns {Object}
 */
export function kelolaFilterKelas({
    daftarKelas = [],
    tahunAjaranAwal = '',
    semesterAwal = '',
    kelasIdAwal = '',
} = {}) {
    return {
        daftarKelas: Array.isArray(daftarKelas) ? daftarKelas : [],
        tahunAjaran: String(tahunAjaranAwal || ''),
        semester: String(semesterAwal || ''),
        kelasId: String(kelasIdAwal || ''),
        pesanPilihUlang: false,

        get kelasCocok() {
            return saringKelas(this.daftarKelas, this.tahunAjaran, this.semester);
        },

        get adaKelas() {
            return this.kelasCocok.length > 0;
        },

        onPeriodeChange() {
            const cocok = this.kelasCocok;
            if (this.kelasId && !validasiPilihanKelas(cocok, this.kelasId)) {
                this.kelasId = '';
                this.pesanPilihUlang = true;
            } else {
                this.pesanPilihUlang = false;
            }
        },

        onKelasChange(val) {
            this.kelasId = String(val || '');
            this.pesanPilihUlang = false;
        },
    };
}

