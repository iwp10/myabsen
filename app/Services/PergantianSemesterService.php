<?php

namespace App\Services;

use App\Models\Jadwal;
use App\Models\Kelas;
use App\Models\Siswa;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;
use Throwable;

class PergantianSemesterService
{
    public const MAX_KELAS = 100;

    public const MAX_SISWA = 500;

    public const MAX_JADWAL = 1000;

    protected int $maxKelas = self::MAX_KELAS;

    protected int $maxSiswa = self::MAX_SISWA;

    protected int $maxJadwal = self::MAX_JADWAL;

    public function __construct(
        protected PeriodeService $periodeService
    ) {}

    /**
     * Mengatur batas keselamatan operasional (berguna untuk pengujian batas).
     */
    public function setBatasMaksimal(?int $kelas = null, ?int $siswa = null, ?int $jadwal = null): self
    {
        if ($kelas !== null) {
            $this->maxKelas = $kelas;
        }
        if ($siswa !== null) {
            $this->maxSiswa = $siswa;
        }
        if ($jadwal !== null) {
            $this->maxJadwal = $jadwal;
        }

        return $this;
    }

    /**
     * Mendapatkan daftar tahun ajaran yang memiliki kelas semester Ganjil aktif.
     *
     * @return array<int, string>
     */
    public function getDaftarTahunAjaranGanjil(): array
    {
        return Kelas::whereNull('deleted_at')
            ->where('semester', 'Ganjil')
            ->whereNotNull('tahun_ajaran')
            ->distinct()
            ->orderBy('tahun_ajaran', 'desc')
            ->pluck('tahun_ajaran')
            ->all();
    }

    /**
     * Memeriksa apakah dua rentang jam saling beririsan (jam_mulai < other.jam_selesai AND jam_selesai > other.jam_mulai).
     */
    public static function isJamBeririsan(string $mulaiA, string $selesaiA, string $mulaiB, string $selesaiB): bool
    {
        $mA = substr(trim($mulaiA), 0, 5);
        $sA = substr(trim($selesaiA), 0, 5);
        $mB = substr(trim($mulaiB), 0, 5);
        $sB = substr(trim($selesaiB), 0, 5);

        return ($mA < $sB) && ($sA > $mB);
    }

    /**
     * Memeriksa apakah ada irisan jam untuk guru atau kelas pada hari dan periode yang sama (aturan bentrok AB-06).
     *
     * @return array{bentrok_guru: bool, bentrok_kelas: bool}
     */
    public function cekBentrokJadwal(
        int $kelasId,
        int $guruId,
        string $hari,
        string $jamMulai,
        string $jamSelesai,
        string $tahunAjaran,
        string $semester,
        ?int $ignoreJadwalId = null
    ): array {
        $queryGuru = Jadwal::where('guru_id', $guruId)
            ->where('hari', strtolower($hari))
            ->where('tahun_ajaran', $tahunAjaran)
            ->where('semester', $semester)
            ->when($ignoreJadwalId, fn ($q) => $q->where('id', '!=', $ignoreJadwalId))
            ->where(function ($q) use ($jamMulai, $jamSelesai) {
                $q->where('jam_mulai', '<', $jamSelesai)
                    ->where('jam_selesai', '>', $jamMulai);
            });

        $queryKelas = Jadwal::where('kelas_id', $kelasId)
            ->where('hari', strtolower($hari))
            ->where('tahun_ajaran', $tahunAjaran)
            ->where('semester', $semester)
            ->when($ignoreJadwalId, fn ($q) => $q->where('id', '!=', $ignoreJadwalId))
            ->where(function ($q) use ($jamMulai, $jamSelesai) {
                $q->where('jam_mulai', '<', $jamSelesai)
                    ->where('jam_selesai', '>', $jamMulai);
            });

        return [
            'bentrok_guru' => $queryGuru->exists(),
            'bentrok_kelas' => $queryKelas->exists(),
        ];
    }

    /**
     * Menyusun rencana pergantian semester otomatis secara murni (read-only dari database).
     *
     * @param  array{salin_kelas?: bool, pindah_siswa?: bool, salin_jadwal?: bool}  $opsi
     * @return array<string, mixed>
     */
    public function susunRencana(string $tahunAjaranAsal, array $opsi = []): array
    {
        $salinKelas = array_key_exists('salin_kelas', $opsi) ? (bool) $opsi['salin_kelas'] : true;
        $pindahSiswa = array_key_exists('pindah_siswa', $opsi) ? (bool) $opsi['pindah_siswa'] : true;
        $salinJadwal = array_key_exists('salin_jadwal', $opsi) ? (bool) $opsi['salin_jadwal'] : true;

        $semesterAsal = 'Ganjil';
        $semesterTujuan = 'Genap';
        $tahunAjaranTujuan = $tahunAjaranAsal;

        // Ambil kelas aktif asal
        $kelasList = Kelas::with('jurusan')
            ->where('tahun_ajaran', $tahunAjaranAsal)
            ->where('semester', $semesterAsal)
            ->orderBy('tingkat', 'asc')
            ->orderBy('nama', 'asc')
            ->get();

        // Ambil seluruh kelas periode tujuan (aktif dan terhapus)
        $kelasTujuanAktif = Kelas::with('jurusan')
            ->where('tahun_ajaran', $tahunAjaranTujuan)
            ->where('semester', $semesterTujuan)
            ->get();

        $kelasTujuanTerhapus = Kelas::onlyTrashed()
            ->where('tahun_ajaran', $tahunAjaranTujuan)
            ->where('semester', $semesterTujuan)
            ->get();

        // Ambil seluruh jadwal periode tujuan yang sudah ada
        $jadwalTujuanExisting = Jadwal::where('tahun_ajaran', $tahunAjaranTujuan)
            ->where('semester', $semesterTujuan)
            ->get();

        // Ambil seluruh jadwal asal milik kelas-kelas asal
        $kelasIds = $kelasList->pluck('id')->all();
        $jadwalAsalList = Jadwal::with([
            'guru' => fn ($q) => $q->withTrashed()->with('user'),
            'mapel' => fn ($q) => $q->withTrashed(),
        ])
            ->whereIn('kelas_id', $kelasIds)
            ->where('tahun_ajaran', $tahunAjaranAsal)
            ->where('semester', $semesterAsal)
            ->orderBy('hari', 'asc')
            ->orderBy('jam_mulai', 'asc')
            ->get();

        // Ambil seluruh siswa aktif di kelas asal
        $siswaAsalList = Siswa::with('user')
            ->whereIn('kelas_id', $kelasIds)
            ->whereNull('deleted_at')
            ->orderBy('nis', 'asc')
            ->get();

        // Cek batas keselamatan
        $totalKelas = $kelasList->count();
        $totalSiswa = $siswaAsalList->count();
        $totalJadwal = $jadwalAsalList->count();

        $melebihiBatas = false;
        $pesanBatas = null;

        if ($totalKelas > $this->maxKelas) {
            $melebihiBatas = true;
            $pesanBatas = "Jumlah kelas asal ({$totalKelas}) melebihi batas maksimal ({$this->maxKelas} kelas per eksekusi).";
        } elseif ($totalSiswa > $this->maxSiswa) {
            $melebihiBatas = true;
            $pesanBatas = "Jumlah siswa aktif ({$totalSiswa}) melebihi batas maksimal ({$this->maxSiswa} siswa per eksekusi).";
        } elseif ($totalJadwal > $this->maxJadwal) {
            $melebihiBatas = true;
            $pesanBatas = "Jumlah jadwal pelajaran ({$totalJadwal}) melebihi batas maksimal ({$this->maxJadwal} jadwal per eksekusi).";
        }

        $rencanaKelas = [];
        $jadwalAkanDisalinBatch = []; // Untuk melacak bentrok antar jadwal yang baru disalin pada sesi ini

        $ringkasan = [
            'total_kelas' => $totalKelas,
            'kelas_disalin' => 0,
            'kelas_sudah_ada' => 0,
            'kelas_dilewati' => 0,
            'total_siswa' => $totalSiswa,
            'siswa_dipindah' => 0,
            'siswa_dilewati' => 0,
            'total_jadwal' => $totalJadwal,
            'jadwal_disalin' => 0,
            'jadwal_dilewati' => 0,
        ];

        foreach ($kelasList as $ka) {
            // Cek kelas tujuan terhapus dengan nama + tingkat + jurusan sama
            $ktTerhapus = $kelasTujuanTerhapus->first(function ($k) use ($ka) {
                return $k->nama === $ka->nama && $k->tingkat === $ka->tingkat && (int) $k->jurusan_id === (int) $ka->jurusan_id;
            });

            // Cek kelas tujuan aktif
            $ktAktif = $kelasTujuanAktif->first(function ($k) use ($ka) {
                return $k->nama === $ka->nama && $k->tingkat === $ka->tingkat && (int) $k->jurusan_id === (int) $ka->jurusan_id;
            });

            $statusKelas = 'akan_disalin';
            $alasanKelas = null;
            $kelasTujuanTersedia = false;
            $kelasTujuanId = null;

            if ($ktTerhapus) {
                $statusKelas = 'dilewati';
                $alasanKelas = 'Terdapat kelas terhapus dengan data sama pada semester Genap. Pulihkan kelas tersebut melalui menu Data Terhapus sebelum melanjutkan.';
                $ringkasan['kelas_dilewati']++;
            } elseif ($ktAktif) {
                $statusKelas = 'sudah_ada';
                $alasanKelas = 'Kelas tujuan sudah ada di semester Genap (akan digunakan).';
                $kelasTujuanTersedia = true;
                $kelasTujuanId = $ktAktif->id;
                $ringkasan['kelas_sudah_ada']++;
            } else {
                if ($salinKelas) {
                    $statusKelas = 'akan_disalin';
                    $kelasTujuanTersedia = true; // Akan dibuat saat eksekusi
                    $ringkasan['kelas_disalin']++;
                } else {
                    $statusKelas = 'dilewati';
                    $alasanKelas = 'Opsi salin kelas tidak diaktifkan dan kelas tujuan belum ada.';
                    $ringkasan['kelas_dilewati']++;
                }
            }

            // Evaluasi siswa kelas ini
            $siswaKelasIni = $siswaAsalList->where('kelas_id', $ka->id);
            $jumlahSiswaKelas = $siswaKelasIni->count();
            $statusSiswa = 'dilewati';
            $alasanSiswa = null;

            if (! $kelasTujuanTersedia) {
                $statusSiswa = 'dilewati';
                $alasanSiswa = 'Kelas tujuan belum tersedia.';
                $ringkasan['siswa_dilewati'] += $jumlahSiswaKelas;
            } else {
                if ($pindahSiswa) {
                    $statusSiswa = 'akan_dipindah';
                    $ringkasan['siswa_dipindah'] += $jumlahSiswaKelas;
                } else {
                    $statusSiswa = 'dilewati';
                    $alasanSiswa = 'Opsi pindah siswa tidak diaktifkan.';
                    $ringkasan['siswa_dilewati'] += $jumlahSiswaKelas;
                }
            }

            // Evaluasi jadwal kelas ini
            $jadwalKelasIni = $jadwalAsalList->where('kelas_id', $ka->id);
            $rencanaJadwal = [];

            foreach ($jadwalKelasIni as $jadwal) {
                $statusJadwal = 'akan_disalin';
                $alasanJadwal = null;

                if (! $kelasTujuanTersedia) {
                    $statusJadwal = 'dilewati';
                    $alasanJadwal = 'Kelas tujuan belum tersedia.';
                } elseif (! $salinJadwal) {
                    $statusJadwal = 'dilewati';
                    $alasanJadwal = 'Opsi salin jadwal tidak diaktifkan.';
                } elseif (! $jadwal->guru || $jadwal->guru->trashed()) {
                    $statusJadwal = 'dilewati';
                    $alasanJadwal = 'Guru pengajar sudah dinonaktifkan / terhapus.';
                } elseif (! $jadwal->mapel || $jadwal->mapel->trashed()) {
                    $statusJadwal = 'dilewati';
                    $alasanJadwal = 'Mata pelajaran sudah dinonaktifkan / terhapus.';
                } else {
                    $hari = strtolower($jadwal->hari);
                    $mulai = $jadwal->jam_mulai;
                    $selesai = $jadwal->jam_selesai;

                    // 1. Cek jadwal identik di periode tujuan (jika kelas tujuan sudah ada di DB)
                    if ($kelasTujuanId) {
                        $sudahIdentik = $jadwalTujuanExisting->first(function ($j) use ($kelasTujuanId, $jadwal, $hari) {
                            return (int) $j->kelas_id === (int) $kelasTujuanId
                                && (int) $j->mapel_id === (int) $jadwal->mapel_id
                                && (int) $j->guru_id === (int) $jadwal->guru_id
                                && strtolower($j->hari) === $hari
                                && substr($j->jam_mulai, 0, 5) === substr($jadwal->jam_mulai, 0, 5)
                                && substr($j->jam_selesai, 0, 5) === substr($jadwal->jam_selesai, 0, 5);
                        });

                        if ($sudahIdentik) {
                            $statusJadwal = 'dilewati';
                            $alasanJadwal = 'Jadwal identik sudah ada di semester Genap.';
                        }
                    }

                    // 2. Cek bentrok jam guru di jadwal tujuan existing
                    if ($statusJadwal === 'akan_disalin') {
                        $bentrokGuruExisting = $jadwalTujuanExisting->first(function ($j) use ($jadwal, $hari, $mulai, $selesai) {
                            return (int) $j->guru_id === (int) $jadwal->guru_id
                                && strtolower($j->hari) === $hari
                                && self::isJamBeririsan($mulai, $selesai, $j->jam_mulai, $j->jam_selesai);
                        });

                        if ($bentrokGuruExisting) {
                            $statusJadwal = 'dilewati';
                            $alasanJadwal = 'Bentrok jam mengajar guru dengan jadwal lain di semester Genap.';
                        }
                    }

                    // 3. Cek bentrok jam kelas di jadwal tujuan existing (jika kelas tujuan sudah ada)
                    if ($statusJadwal === 'akan_disalin' && $kelasTujuanId) {
                        $bentrokKelasExisting = $jadwalTujuanExisting->first(function ($j) use ($kelasTujuanId, $hari, $mulai, $selesai) {
                            return (int) $j->kelas_id === (int) $kelasTujuanId
                                && strtolower($j->hari) === $hari
                                && self::isJamBeririsan($mulai, $selesai, $j->jam_mulai, $j->jam_selesai);
                        });

                        if ($bentrokKelasExisting) {
                            $statusJadwal = 'dilewati';
                            $alasanJadwal = 'Bentrok jam pelajaran kelas dengan jadwal lain di semester Genap.';
                        }
                    }

                    // 4. Cek bentrok dengan jadwal yang baru disalin pada batch ini
                    if ($statusJadwal === 'akan_disalin') {
                        foreach ($jadwalAkanDisalinBatch as $jBatch) {
                            if (strtolower($jBatch['hari']) === $hari && self::isJamBeririsan($mulai, $selesai, $jBatch['jam_mulai'], $jBatch['jam_selesai'])) {
                                if ((int) $jBatch['guru_id'] === (int) $jadwal->guru_id) {
                                    $statusJadwal = 'dilewati';
                                    $alasanJadwal = 'Bentrok jam mengajar guru dengan jadwal lain yang disalin bersamaan.';
                                    break;
                                }

                                // Bentrok kelas pada batch yang sama (dicocokkan via nama kelas asal yang sama)
                                if ($jBatch['kelas_asal_id'] === $ka->id) {
                                    $statusJadwal = 'dilewati';
                                    $alasanJadwal = 'Bentrok jam pelajaran kelas dengan jadwal lain yang disalin bersamaan.';
                                    break;
                                }
                            }
                        }
                    }
                }

                if ($statusJadwal === 'akan_disalin') {
                    $ringkasan['jadwal_disalin']++;
                    $jadwalAkanDisalinBatch[] = [
                        'kelas_asal_id' => $ka->id,
                        'kelas_tujuan_id' => $kelasTujuanId,
                        'guru_id' => $jadwal->guru_id,
                        'mapel_id' => $jadwal->mapel_id,
                        'hari' => strtolower($jadwal->hari),
                        'jam_mulai' => $jadwal->jam_mulai,
                        'jam_selesai' => $jadwal->jam_selesai,
                    ];
                } else {
                    $ringkasan['jadwal_dilewati']++;
                }

                $rencanaJadwal[] = [
                    'id' => $jadwal->id,
                    'mapel_nama' => $jadwal->mapel?->nama ?? '-',
                    'guru_nama' => $jadwal->guru?->user?->name ?? '-',
                    'hari' => ucfirst($jadwal->hari),
                    'jam' => substr($jadwal->jam_mulai, 0, 5).' - '.substr($jadwal->jam_selesai, 0, 5),
                    'status' => $statusJadwal,
                    'alasan' => $alasanJadwal,
                ];
            }

            $rencanaKelas[] = [
                'kelas_asal' => $ka,
                'kelas_tujuan_id' => $kelasTujuanId,
                'kelas_tujuan_nama' => $ka->nama.' · Genap '.$tahunAjaranTujuan,
                'status_kelas' => $statusKelas,
                'alasan_kelas' => $alasanKelas,
                'jumlah_siswa' => $jumlahSiswaKelas,
                'status_siswa' => $statusSiswa,
                'alasan_siswa' => $alasanSiswa,
                'daftar_jadwal' => $rencanaJadwal,
            ];
        }

        return [
            'tahun_ajaran' => $tahunAjaranAsal,
            'semester_asal' => $semesterAsal,
            'semester_tujuan' => $semesterTujuan,
            'opsi' => [
                'salin_kelas' => $salinKelas,
                'pindah_siswa' => $pindahSiswa,
                'salin_jadwal' => $salinJadwal,
            ],
            'melebihi_batas' => $melebihiBatas,
            'pesan_batas' => $pesanBatas,
            'ringkasan' => $ringkasan,
            'rencana_kelas' => $rencanaKelas,
        ];
    }

    /**
     * Menghasilkan pratinjau aman tanpa efek samping (read-only).
     *
     * @param  array{salin_kelas?: bool, pindah_siswa?: bool, salin_jadwal?: bool}  $opsi
     * @return array<string, mixed>
     */
    public function pratinjau(string $tahunAjaranAsal, array $opsi = []): array
    {
        return $this->susunRencana($tahunAjaranAsal, $opsi);
    }

    /**
     * Menjalankan eksekusi pergantian semester otomatis.
     * Mengkalkulasi ulang rencana dari database dan memproses per kelas dalam transaksi atomik terpisah.
     *
     * @param  array{salin_kelas?: bool, pindah_siswa?: bool, salin_jadwal?: bool}  $opsi
     * @return array<string, mixed>
     */
    public function jalankan(string $tahunAjaranAsal, array $opsi, int $adminId): array
    {
        $rencana = $this->susunRencana($tahunAjaranAsal, $opsi);

        if ($rencana['melebihi_batas']) {
            throw new InvalidArgumentException($rencana['pesan_batas']);
        }

        $salinKelas = $rencana['opsi']['salin_kelas'];
        $pindahSiswa = $rencana['opsi']['pindah_siswa'];
        $salinJadwal = $rencana['opsi']['salin_jadwal'];

        $hasilPerKelas = [];
        $totalKelasBerhasil = 0;
        $totalKelasGagal = 0;
        $totalSiswaBerhasil = 0;
        $totalJadwalBerhasil = 0;
        $totalJadwalDilewati = 0;

        // Kumpulkan jadwal yang baru saja disalin dalam memori untuk deteksi bentrok antar-kelas
        $jadwalBaruDisalinBatch = [];

        foreach ($rencana['rencana_kelas'] as $item) {
            $kelasAsal = $item['kelas_asal'];
            $kelasId = $kelasAsal->id;

            try {
                $hasilKelas = DB::transaction(function () use (
                    $kelasAsal,
                    $tahunAjaranAsal,
                    $salinKelas,
                    $pindahSiswa,
                    $salinJadwal,
                    $item,
                    &$jadwalBaruDisalinBatch
                ) {
                    $kelasTujuanId = null;
                    $statusKelasAksi = 'dilewati';
                    $alasanKelasAksi = null;

                    // 1. Cek kelas terhapus di tujuan
                    $ktTerhapus = Kelas::onlyTrashed()
                        ->where('nama', $kelasAsal->nama)
                        ->where('tingkat', $kelasAsal->tingkat)
                        ->where('jurusan_id', $kelasAsal->jurusan_id)
                        ->where('tahun_ajaran', $tahunAjaranAsal)
                        ->where('semester', 'Genap')
                        ->first();

                    if ($ktTerhapus) {
                        return [
                            'berhasil' => false,
                            'status_kelas' => 'dilewati',
                            'alasan' => 'Terdapat kelas terhapus dengan data sama pada semester Genap. Pulihkan kelas tersebut melalui menu Data Terhapus.',
                            'siswa_dipindah' => 0,
                            'jadwal_disalin' => 0,
                            'jadwal_dilewati' => count($item['daftar_jadwal']),
                            'rincian_jadwal' => [],
                        ];
                    }

                    // 2. Ambil atau buat kelas tujuan
                    $kt = Kelas::where('nama', $kelasAsal->nama)
                        ->where('tingkat', $kelasAsal->tingkat)
                        ->where('jurusan_id', $kelasAsal->jurusan_id)
                        ->where('tahun_ajaran', $tahunAjaranAsal)
                        ->where('semester', 'Genap')
                        ->first();

                    if ($kt) {
                        $kelasTujuanId = $kt->id;
                        $statusKelasAksi = 'sudah_ada';
                    } elseif ($salinKelas) {
                        $baru = Kelas::create([
                            'nama' => $kelasAsal->nama,
                            'tingkat' => $kelasAsal->tingkat,
                            'jurusan_id' => $kelasAsal->jurusan_id,
                            'tahun_ajaran' => $tahunAjaranAsal,
                            'semester' => 'Genap',
                        ]);
                        $kelasTujuanId = $baru->id;
                        $statusKelasAksi = 'disalin';
                    } else {
                        return [
                            'berhasil' => false,
                            'status_kelas' => 'dilewati',
                            'alasan' => 'Kelas tujuan belum ada dan opsi salin kelas dinonaktifkan.',
                            'siswa_dipindah' => 0,
                            'jadwal_disalin' => 0,
                            'jadwal_dilewati' => count($item['daftar_jadwal']),
                            'rincian_jadwal' => [],
                        ];
                    }

                    // 3. Pindahkan Siswa Aktif
                    $siswaDipindahCount = 0;
                    if ($pindahSiswa && $kelasTujuanId) {
                        $siswaDipindahCount = Siswa::where('kelas_id', $kelasAsal->id)
                            ->whereNull('deleted_at')
                            ->update(['kelas_id' => $kelasTujuanId]);
                    }

                    // 4. Salin Jadwal
                    $jadwalDisalinCount = 0;
                    $jadwalDilewatiCount = 0;
                    $rincianJadwalAksi = [];

                    if ($salinJadwal && $kelasTujuanId) {
                        $jadwalList = Jadwal::with(['guru' => fn ($q) => $q->withTrashed(), 'mapel' => fn ($q) => $q->withTrashed()])
                            ->where('kelas_id', $kelasAsal->id)
                            ->where('tahun_ajaran', $tahunAjaranAsal)
                            ->where('semester', 'Ganjil')
                            ->get();

                        foreach ($jadwalList as $j) {
                            $dapatDisalin = true;
                            $alasanJadwal = null;

                            if (! $j->guru || $j->guru->trashed()) {
                                $dapatDisalin = false;
                                $alasanJadwal = 'Guru pengajar sudah dinonaktifkan / terhapus.';
                            } elseif (! $j->mapel || $j->mapel->trashed()) {
                                $dapatDisalin = false;
                                $alasanJadwal = 'Mata pelajaran sudah dinonaktifkan / terhapus.';
                            } else {
                                $hari = strtolower($j->hari);
                                $mulai = $j->jam_mulai;
                                $selesai = $j->jam_selesai;

                                // Jadwal identik
                                $identik = Jadwal::where('kelas_id', $kelasTujuanId)
                                    ->where('mapel_id', $j->mapel_id)
                                    ->where('guru_id', $j->guru_id)
                                    ->where('hari', $hari)
                                    ->where('tahun_ajaran', $tahunAjaranAsal)
                                    ->where('semester', 'Genap')
                                    ->where('jam_mulai', $mulai)
                                    ->where('jam_selesai', $selesai)
                                    ->exists();

                                if ($identik) {
                                    $dapatDisalin = false;
                                    $alasanJadwal = 'Jadwal identik sudah ada di semester Genap.';
                                } else {
                                    // Bentrok di database
                                    $bentrokDb = $this->cekBentrokJadwal(
                                        $kelasTujuanId,
                                        $j->guru_id,
                                        $hari,
                                        $mulai,
                                        $selesai,
                                        $tahunAjaranAsal,
                                        'Genap'
                                    );

                                    if ($bentrokDb['bentrok_guru']) {
                                        $dapatDisalin = false;
                                        $alasanJadwal = 'Bentrok jam mengajar guru dengan jadwal lain di semester Genap.';
                                    } elseif ($bentrokDb['bentrok_kelas']) {
                                        $dapatDisalin = false;
                                        $alasanJadwal = 'Bentrok jam pelajaran kelas dengan jadwal lain di semester Genap.';
                                    } else {
                                        // Bentrok dengan jadwal yang baru disalin pada proses berjalan ini
                                        foreach ($jadwalBaruDisalinBatch as $jBaru) {
                                            if ($jBaru['hari'] === $hari && self::isJamBeririsan($mulai, $selesai, $jBaru['jam_mulai'], $jBaru['jam_selesai'])) {
                                                if ((int) $jBaru['guru_id'] === (int) $j->guru_id) {
                                                    $dapatDisalin = false;
                                                    $alasanJadwal = 'Bentrok jam mengajar guru dengan jadwal lain yang disalin bersamaan.';
                                                    break;
                                                }
                                                if ((int) $jBaru['kelas_id'] === (int) $kelasTujuanId) {
                                                    $dapatDisalin = false;
                                                    $alasanJadwal = 'Bentrok jam pelajaran kelas dengan jadwal lain yang disalin bersamaan.';
                                                    break;
                                                }
                                            }
                                        }
                                    }
                                }
                            }

                            if ($dapatDisalin) {
                                $jadwalBaru = Jadwal::create([
                                    'kelas_id' => $kelasTujuanId,
                                    'mapel_id' => $j->mapel_id,
                                    'guru_id' => $j->guru_id,
                                    'hari' => strtolower($j->hari),
                                    'jam_mulai' => $j->jam_mulai,
                                    'jam_selesai' => $j->jam_selesai,
                                    'tahun_ajaran' => $tahunAjaranAsal,
                                    'semester' => 'Genap',
                                ]);

                                $jadwalBaruDisalinBatch[] = [
                                    'id' => $jadwalBaru->id,
                                    'kelas_id' => $kelasTujuanId,
                                    'guru_id' => $j->guru_id,
                                    'hari' => strtolower($j->hari),
                                    'jam_mulai' => $j->jam_mulai,
                                    'jam_selesai' => $j->jam_selesai,
                                ];

                                $jadwalDisalinCount++;
                                $rincianJadwalAksi[] = [
                                    'mapel' => $j->mapel?->nama ?? '-',
                                    'status' => 'disalin',
                                    'alasan' => null,
                                ];
                            } else {
                                $jadwalDilewatiCount++;
                                $rincianJadwalAksi[] = [
                                    'mapel' => $j->mapel?->nama ?? '-',
                                    'status' => 'dilewati',
                                    'alasan' => $alasanJadwal,
                                ];
                            }
                        }
                    }

                    return [
                        'berhasil' => true,
                        'status_kelas' => $statusKelasAksi,
                        'alasan' => $alasanKelasAksi,
                        'siswa_dipindah' => $siswaDipindahCount,
                        'jadwal_disalin' => $jadwalDisalinCount,
                        'jadwal_dilewati' => $jadwalDilewatiCount,
                        'rincian_jadwal' => $rincianJadwalAksi,
                    ];
                });

                if ($hasilKelas['berhasil']) {
                    $totalKelasBerhasil++;
                    $totalSiswaBerhasil += $hasilKelas['siswa_dipindah'];
                    $totalJadwalBerhasil += $hasilKelas['jadwal_disalin'];
                    $totalJadwalDilewati += $hasilKelas['jadwal_dilewati'];
                } else {
                    $totalKelasGagal++;
                }

                $hasilPerKelas[] = [
                    'nama_kelas' => $kelasAsal->nama,
                    'tingkat' => $kelasAsal->tingkat,
                    'berhasil' => $hasilKelas['berhasil'],
                    'status_kelas' => $hasilKelas['status_kelas'],
                    'alasan' => $hasilKelas['alasan'],
                    'siswa_dipindah' => $hasilKelas['siswa_dipindah'],
                    'jadwal_disalin' => $hasilKelas['jadwal_disalin'],
                    'jadwal_dilewati' => $hasilKelas['jadwal_dilewati'],
                    'rincian_jadwal' => $hasilKelas['rincian_jadwal'],
                ];
            } catch (Throwable $e) {
                // Kegagalan kelas ini diisolasi sehingga tidak membatalkan kelas lainnya
                $totalKelasGagal++;
                $hasilPerKelas[] = [
                    'nama_kelas' => $kelasAsal->nama,
                    'tingkat' => $kelasAsal->tingkat,
                    'berhasil' => false,
                    'status_kelas' => 'gagal',
                    'alasan' => 'Terjadi kesalahan sistem: '.$e->getMessage(),
                    'siswa_dipindah' => 0,
                    'jadwal_disalin' => 0,
                    'jadwal_dilewati' => count($item['daftar_jadwal']),
                    'rincian_jadwal' => [],
                ];
            }
        }

        // Catat Log::info tanpa data sensitif
        Log::info('Pergantian semester otomatis dijalankan', [
            'admin_id' => $adminId,
            'tahun_ajaran' => $tahunAjaranAsal,
            'semester_asal' => 'Ganjil',
            'semester_tujuan' => 'Genap',
            'opsi' => $opsi,
            'kelas_berhasil' => $totalKelasBerhasil,
            'kelas_gagal' => $totalKelasGagal,
            'siswa_dipindah' => $totalSiswaBerhasil,
            'jadwal_disalin' => $totalJadwalBerhasil,
            'jadwal_dilewati' => $totalJadwalDilewati,
        ]);

        return [
            'tahun_ajaran' => $tahunAjaranAsal,
            'semester_tujuan' => 'Genap',
            'kelas_berhasil' => $totalKelasBerhasil,
            'kelas_gagal' => $totalKelasGagal,
            'siswa_dipindah' => $totalSiswaBerhasil,
            'jadwal_disalin' => $totalJadwalBerhasil,
            'jadwal_dilewati' => $totalJadwalDilewati,
            'hasil_per_kelas' => $hasilPerKelas,
        ];
    }

    /**
     * Mengaktifkan periode akademik tujuan (Genap pada tahun ajaran yang bersangkutan).
     *
     * @return array{tahun_ajaran: string, semester: string, jumlah_jadwal: int, tanpa_jadwal: bool}
     */
    public function aktifkanPeriodeTujuan(string $tahunAjaran, int $adminId): array
    {
        $jumlahJadwalTujuan = Jadwal::where('tahun_ajaran', $tahunAjaran)
            ->where('semester', 'Genap')
            ->count();

        $this->periodeService->setPeriodeAktif($tahunAjaran, 'Genap');

        Log::info('Periode aktif diubah melalui pergantian semester otomatis', [
            'admin_id' => $adminId,
            'tahun_ajaran' => $tahunAjaran,
            'semester' => 'Genap',
            'jumlah_jadwal' => $jumlahJadwalTujuan,
        ]);

        return [
            'tahun_ajaran' => $tahunAjaran,
            'semester' => 'Genap',
            'jumlah_jadwal' => $jumlahJadwalTujuan,
            'tanpa_jadwal' => ($jumlahJadwalTujuan === 0),
        ];
    }
}
