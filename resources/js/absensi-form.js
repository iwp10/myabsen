/**
 * Modul Javascript Murni untuk Logika Formulir Absensi Siswa.
 * Digunakan oleh Alpine.js di resources/views/guru/absensi.blade.php.
 */

/**
 * Menghitung jumlah status H/I/S/A dari objek students.
 *
 * @param {Record<string|number, { status: string, keterangan?: string }>} students
 * @returns {{ hadir: number, izin: number, sakit: number, alpa: number, total: number }}
 */
export function hitungRingkasan(students) {
    const counts = { hadir: 0, izin: 0, sakit: 0, alpa: 0, total: 0 };
    if (!students || typeof students !== 'object') {
        return counts;
    }

    for (const id in students) {
        if (Object.prototype.hasOwnProperty.call(students, id)) {
            const st = students[id]?.status;
            if (st === 'hadir' || st === 'izin' || st === 'sakit' || st === 'alpa') {
                counts[st]++;
            }
            counts.total++;
        }
    }
    return counts;
}

/**
 * Mengeset seluruh status siswa menjadi 'hadir' di sisi klien,
 * dengan tetap mempertahankan keterangan yang sudah pernah diketik.
 *
 * @param {Record<string|number, { status: string, keterangan?: string }>} students
 * @returns {Record<string|number, { status: string, keterangan?: string }>}
 */
export function setSemuaHadir(students) {
    if (!students || typeof students !== 'object') {
        return {};
    }

    const updated = {};
    for (const id in students) {
        if (Object.prototype.hasOwnProperty.call(students, id)) {
            updated[id] = {
                status: 'hadir',
                keterangan: students[id]?.keterangan || ''
            };
        }
    }
    return updated;
}

/**
 * Mengubah status satu siswa.
 *
 * @param {Record<string|number, { status: string, keterangan?: string }>} students
 * @param {string|number} id
 * @param {'hadir'|'izin'|'sakit'|'alpa'} statusBaru
 * @returns {Record<string|number, { status: string, keterangan?: string }>}
 */
export function ubahStatusSiswa(students, id, statusBaru) {
    if (!students || !students[id]) {
        return students;
    }
    return {
        ...students,
        [id]: {
            ...students[id],
            status: statusBaru
        }
    };
}
