<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ImportSiswaRequest;
use App\Http\Requests\Admin\SiswaFilterRequest;
use App\Http\Requests\StoreSiswaRequest;
use App\Http\Requests\UpdateSiswaRequest;
use App\Imports\SiswaImport;
use App\Models\DetailAbsensi;
use App\Models\Kelas;
use App\Models\Siswa;
use App\Models\User;
use App\Services\PasswordAwalService;
use App\Services\PeriodeService;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\Validators\ValidationException;
use RuntimeException;

class SiswaController extends Controller
{
    public function index(SiswaFilterRequest $request)
    {
        $search = $request->query('search');
        $filterKelasId = $request->query('kelas_id');
        $urut = $request->query('urut', 'nama_asc');

        $query = Siswa::with(['user', 'kelas.jurusan'])
            ->join('users', 'siswa.user_id', '=', 'users.id')
            ->select('siswa.*')
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
        $hasHistory = DetailAbsensi::where('siswa_id', $siswa->id)->exists();

        if ($hasHistory) {
            $siswa->delete();

            return redirect()->route('admin.siswa.index')
                ->with('success', 'Siswa di-soft-delete karena memiliki riwayat absensi.');
        }

        try {
            DB::transaction(function () use ($siswa) {
                $user = $siswa->user;
                $siswa->forceDelete();
                $user?->delete();
            });

            return redirect()->route('admin.siswa.index')
                ->with('success', 'Siswa beserta akun berhasil dihapus.');
        } catch (QueryException $e) {
            return redirect()->route('admin.siswa.index')
                ->with('error', 'Akun tidak dapat dihapus karena memiliki riwayat absensi.');
        }
    }

    public function resetPassword(Siswa $siswa)
    {
        try {
            $passwordAwal = PasswordAwalService::get();

            $siswa->user->update([
                'password' => Hash::make($passwordAwal),
                'must_change_password' => true,
            ]);

            return redirect()->route('admin.siswa.index')
                ->with('success', 'Password siswa berhasil direset ke password awal.');
        } catch (RuntimeException $e) {
            return back()->with('error', 'Password awal belum diatur dengan aman. Hubungi pengelola sistem.');
        }
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
