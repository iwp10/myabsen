<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ImportSiswaRequest;
use App\Http\Requests\Admin\MassalSiswaRequest;
use App\Http\Requests\Admin\SiswaFilterRequest;
use App\Http\Requests\StoreSiswaRequest;
use App\Http\Requests\UpdateSiswaRequest;
use App\Imports\SiswaImport;
use App\Models\Kelas;
use App\Models\Siswa;
use App\Models\User;
use App\Services\PasswordAwalService;
use App\Services\PeriodeService;
use App\Services\SiswaService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\Validators\ValidationException;
use RuntimeException;

class SiswaController extends Controller
{
    public function __construct(
        protected SiswaService $siswaService
    ) {}

    public function index(SiswaFilterRequest $request)
    {
        $search = $request->query('search');
        $filterKelasId = $request->query('kelas_id');
        $urut = $request->query('urut', 'nama_asc');

        $query = Siswa::with(['user', 'kelas.jurusan'])
            ->join('users', 'siswa.user_id', '=', 'users.id')
            ->select('siswa.*')
            ->withExists('detailAbsensi')
            ->when($filterKelasId, function ($q, $kelasId) {
                $q->where('siswa.kelas_id', $kelasId);
            })
            ->when($search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('siswa.nis', 'like', "%{$search}%")
                        ->orWhere('users.name', 'like', "%{$search}%");
                });
            });

        if ($urut === 'nama_desc' || $urut === 'nama-za') {
            $query->orderBy(DB::raw('LOWER(users.name)'), 'desc')->orderBy('siswa.id', 'asc');
        } elseif ($urut === 'nis') {
            $query->orderBy('siswa.nis', 'asc')->orderBy('siswa.id', 'asc');
        } else {
            $query->orderBy(DB::raw('LOWER(users.name)'), 'asc')->orderBy('siswa.id', 'asc');
        }

        $siswas = $query->paginate(10)->withQueryString();

        $periodeService = app(PeriodeService::class);
        $kelasListGrouped = $periodeService->getDaftarKelasGroupedByPeriode();
        $kelas = Kelas::orderBy('tingkat')->orderBy('nama')->get();

        return view('admin.siswa.index', compact('siswas', 'kelas', 'kelasListGrouped', 'filterKelasId', 'urut', 'search'));
    }

    public function create()
    {
        $kelas = Kelas::with('jurusan')->orderBy('tingkat')->orderBy('nama')->get();

        return view('admin.siswa.create', compact('kelas'));
    }

    public function store(StoreSiswaRequest $request)
    {
        try {
            $passwordAwal = PasswordAwalService::get();

            DB::transaction(function () use ($request, $passwordAwal) {
                $user = User::create([
                    'name' => $request->name,
                    'username' => $request->nis,
                    'password' => Hash::make($passwordAwal),
                    'role' => 'siswa',
                    'must_change_password' => true,
                ]);

                Siswa::create([
                    'user_id' => $user->id,
                    'nis' => $request->nis,
                    'kelas_id' => $request->kelas_id,
                ]);
            });

            return redirect()->route('admin.siswa.index')
                ->with('success', 'Siswa berhasil ditambahkan.');
        } catch (RuntimeException $e) {
            return back()->withInput()->with('error', 'Password awal belum diatur dengan aman. Hubungi pengelola sistem.');
        }
    }

    public function edit(Siswa $siswa)
    {
        $siswa->load('user');
        $kelas = Kelas::with('jurusan')->orderBy('tingkat')->orderBy('nama')->get();

        return view('admin.siswa.edit', compact('siswa', 'kelas'));
    }

    public function update(UpdateSiswaRequest $request, Siswa $siswa)
    {
        DB::transaction(function () use ($request, $siswa) {
            $siswa->user->update([
                'name' => $request->name,
                'username' => $request->nis,
            ]);

            $siswa->update([
                'nis' => $request->nis,
                'kelas_id' => $request->kelas_id,
            ]);
        });

        return redirect()->route('admin.siswa.index')
            ->with('success', 'Siswa berhasil diperbarui.');
    }

    public function destroy(Siswa $siswa)
    {
        $result = $this->siswaService->hapus($siswa);

        if ($result['status'] === 'berhasil') {
            return redirect()->route('admin.siswa.index')
                ->with('success', $result['pesan']);
        }

        return redirect()->route('admin.siswa.index')
            ->with('error', $result['pesan']);
    }

    public function resetPassword(Siswa $siswa)
    {
        try {
            $result = $this->siswaService->resetPassword($siswa);

            return redirect()->route('admin.siswa.index')
                ->with('success', $result['pesan']);
        } catch (RuntimeException $e) {
            return back()->with('error', 'Password awal belum diatur dengan aman. Hubungi pengelola sistem.');
        }
    }

    public function massalHapus(MassalSiswaRequest $request)
    {
        $ids = $request->validated('ids');
        $siswas = Siswa::with('user')->whereIn('id', $ids)->get()->keyBy('id');

        $countNonaktif = 0;
        $countPermanen = 0;
        $ditolak = [];

        foreach ($ids as $id) {
            $siswa = $siswas->get($id);
            if (! $siswa) {
                continue;
            }

            $namaSiswa = $siswa->user?->name ?? $siswa->nis;
            $result = $this->siswaService->hapus($siswa);

            if ($result['status'] === 'berhasil') {
                if ($result['jenis'] === 'nonaktif') {
                    $countNonaktif++;
                } else {
                    $countPermanen++;
                }
            } else {
                $ditolak[] = [
                    'nama' => $namaSiswa,
                    'alasan' => $result['pesan'],
                ];
            }
        }

        $countBerhasil = $countNonaktif + $countPermanen;
        $countDitolak = count($ditolak);

        Log::info('Aksi massal admin: hapus siswa', [
            'admin_id' => auth()->id(),
            'aksi' => 'massal_hapus_siswa',
            'ids' => $ids,
            'berhasil' => $countBerhasil,
            'ditolak' => $countDitolak,
        ]);

        if ($countDitolak === 0) {
            return redirect()->route('admin.siswa.index', $request->query())
                ->with('success', "{$countNonaktif} dinonaktifkan, {$countPermanen} dihapus permanen");
        }

        $flashKey = $countBerhasil > 0 ? 'warning' : 'error';
        $firstDitolak = array_slice($ditolak, 0, 10);
        $sisaDitolak = $countDitolak - count($firstDitolak);

        $pesanDitolak = "{$countBerhasil} berhasil, {$countDitolak} ditolak.";
        $detailList = collect($firstDitolak)->map(fn ($d) => '<li><strong>'.e($d['nama']).'</strong>: '.e($d['alasan']).'</li>')->implode('');
        if ($sisaDitolak > 0) {
            $detailList .= "<li>...dan {$sisaDitolak} lainnya.</li>";
        }
        $pesanLengkap = "<div>{$pesanDitolak}<ul class=\"mt-1 list-disc pl-5 space-y-0.5 text-xs\">{$detailList}</ul></div>";

        return redirect()->route('admin.siswa.index', $request->query())
            ->with($flashKey, $pesanLengkap);
    }

    public function massalResetPassword(MassalSiswaRequest $request)
    {
        try {
            $passwordAwal = PasswordAwalService::get();
        } catch (RuntimeException $e) {
            return redirect()->route('admin.siswa.index', $request->query())
                ->with('error', 'Password awal belum diatur dengan aman. Hubungi pengelola sistem.');
        }

        $ids = $request->validated('ids');
        $siswas = Siswa::with('user')->whereIn('id', $ids)->get()->keyBy('id');

        $berhasil = [];
        $ditolak = [];

        foreach ($ids as $id) {
            $siswa = $siswas->get($id);
            if (! $siswa) {
                continue;
            }

            $namaSiswa = $siswa->user?->name ?? $siswa->nis;

            try {
                DB::transaction(function () use ($siswa, $passwordAwal) {
                    $this->siswaService->resetPassword($siswa, $passwordAwal);
                });
                $berhasil[] = $namaSiswa;
            } catch (\Throwable $e) {
                $ditolak[] = [
                    'nama' => $namaSiswa,
                    'alasan' => 'Gagal mereset password.',
                ];
            }
        }

        $countBerhasil = count($berhasil);
        $countDitolak = count($ditolak);

        Log::info('Aksi massal admin: reset password siswa', [
            'admin_id' => auth()->id(),
            'aksi' => 'massal_reset_password_siswa',
            'ids' => $ids,
            'berhasil' => $countBerhasil,
            'ditolak' => $countDitolak,
        ]);

        if ($countDitolak === 0) {
            return redirect()->route('admin.siswa.index', $request->query())
                ->with('success', "{$countBerhasil} siswa berhasil direset");
        }

        $flashKey = $countBerhasil > 0 ? 'warning' : 'error';
        $firstDitolak = array_slice($ditolak, 0, 10);
        $sisaDitolak = $countDitolak - count($firstDitolak);

        $pesanDitolak = "{$countBerhasil} berhasil, {$countDitolak} ditolak.";
        $detailList = collect($firstDitolak)->map(fn ($d) => '<li><strong>'.e($d['nama']).'</strong>: '.e($d['alasan']).'</li>')->implode('');
        if ($sisaDitolak > 0) {
            $detailList .= "<li>...dan {$sisaDitolak} lainnya.</li>";
        }
        $pesanLengkap = "<div>{$pesanDitolak}<ul class=\"mt-1 list-disc pl-5 space-y-0.5 text-xs\">{$detailList}</ul></div>";

        return redirect()->route('admin.siswa.index', $request->query())
            ->with($flashKey, $pesanLengkap);
    }

    public function import(ImportSiswaRequest $request)
    {
        try {
            $passwordAwal = PasswordAwalService::get();

            DB::transaction(function () use ($request, $passwordAwal) {
                Excel::import(new SiswaImport($request->kelas_id, $passwordAwal), $request->file('file'));
            });

            return redirect()->route('admin.siswa.index')->with('success', 'Data siswa berhasil diimpor.');
        } catch (ValidationException $e) {
            $failures = $e->failures();
            $errors = [];
            foreach ($failures as $failure) {
                $errors[] = 'Baris '.$failure->row().': '.implode(', ', $failure->errors());
            }

            return back()->with('error', 'Gagal impor:<br>'.implode('<br>', $errors));
        } catch (RuntimeException $e) {
            return back()->with('error', 'Password awal belum diatur dengan aman. Hubungi pengelola sistem.');
        } catch (\Exception $e) {
            return back()->with('error', 'Terjadi kesalahan saat mengimpor data.');
        }
    }
}
