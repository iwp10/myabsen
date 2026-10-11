/**
 * Modul murni pilihanMassal (tanpa akses DOM).
 * Mengelola pilihan baris/data massal untuk Alpine.js dan form actions.
 *
 * @param {Array<string|number>} idTersedia - Daftar ID yang sedang terlihat di tabel/daftar
 * @param {Array<string|number>} terpilihAwal - Daftar ID terpilih awal (opsional)
 * @param {Object} extra - Properti tambahan yang digabungkan ke objek state (opsional)
 */
export function pilihanMassal(idTersedia = [], terpilihAwal = [], extra = {}) {
    const normTersedia = (Array.isArray(idTersedia) ? idTersedia : []).map(id => String(id));
    const setTersedia = new Set(normTersedia);
    const normTerpilih = (Array.isArray(terpilihAwal) ? terpilihAwal : [])
        .map(id => String(id))
        .filter(id => setTersedia.has(id));

    const state = {
        terpilih: normTerpilih,
        idTersedia: normTersedia,

        get jumlah() {
            return this.terpilih.length;
        },

        get semuaTerpilih() {
            return this.idTersedia.length > 0 && this.idTersedia.every(id => this.terpilih.includes(id));
        },

        get sebagian() {
            const ada = this.idTersedia.some(id => this.terpilih.includes(id));
            return ada && !this.semuaTerpilih;
        },

        toggleSemua(centangBaru) {
            const harusCentang = Boolean(centangBaru);
            if (harusCentang) {
                const set = new Set(this.terpilih);
                for (const id of this.idTersedia) {
                    set.add(id);
                }
                this.terpilih = Array.from(set);
            } else {
                const setTersediaCurrent = new Set(this.idTersedia);
                this.terpilih = this.terpilih.filter(id => !setTersediaCurrent.has(id));
            }
        },

        toggleSatu(id) {
            const normId = String(id);
            if (this.terpilih.includes(normId)) {
                this.terpilih = this.terpilih.filter(item => item !== normId);
            } else {
                this.terpilih.push(normId);
            }
        },

        setTersedia(idBaru = []) {
            this.idTersedia = (Array.isArray(idBaru) ? idBaru : []).map(id => String(id));
            const setTersediaCurrent = new Set(this.idTersedia);
            this.terpilih = this.terpilih.filter(id => setTersediaCurrent.has(id));
        },

        reset() {
            this.terpilih = [];
        },
    };

    if (extra && typeof extra === 'object') {
        Object.defineProperties(state, Object.getOwnPropertyDescriptors(extra));
    }

    return state;
}

export default pilihanMassal;
