<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreSiswaRequest;
use App\Http\Requests\UpdateSiswaRequest;
use App\Models\Siswa;
use App\Models\User;
use App\Models\Kelas;
use App\Models\DetailAbsensi;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;
use App\Imports\SiswaImport;
use Maatwebsite\Excel\Facades\Excel;

class SiswaController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->query('search');
        $siswas = Siswa::with(['user', 'kelas.jurusan'])
            ->when($search, function ($query) use ($search) {
                $query->whereHas('user', function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%");
                })->orWhere('nis', 'like', "%{$search}%");
            })
            ->paginate(10)
            ->withQueryString();

        $kelas = \App\Models\Kelas::orderBy('tingkat')->orderBy('nama')->get();

        return view('admin.siswa.index', compact('siswas', 'kelas'));
    }

    public function create()
    {
        $kelas = Kelas::with('jurusan')->orderBy('tingkat')->orderBy('nama')->get();
        return view('admin.siswa.create', compact('kelas'));
    }

    public function store(StoreSiswaRequest $request)
    {
        DB::transaction(function () use ($request) {
            $user = User::create([
                'name' => $request->name,
                'username' => $request->nis,
                'password' => Hash::make('password'),
                'role' => 'siswa',
            ]);

            Siswa::create([
                'user_id' => $user->id,
                'nis' => $request->nis,
                'kelas_id' => $request->kelas_id,
            ]);
        });

        return redirect()->route('admin.siswa.index')
            ->with('success', 'Siswa berhasil ditambahkan.');
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

        // Hard delete user, this will cascade and hard delete the siswa as well
        $siswa->user()->delete();

        return redirect()->route('admin.siswa.index')
            ->with('success', 'Siswa beserta akun berhasil dihapus.');
    }

    public function resetPassword(Siswa $siswa)
    {
        $siswa->user->update([
            'password' => Hash::make('password')
        ]);

        return redirect()->route('admin.siswa.index')
            ->with('success', 'Password siswa berhasil direset ke "password".');
    }

    public function import(Request $request)
    {
        $request->validate([
            'kelas_id' => 'required|exists:kelas,id',
            'file' => 'required|mimes:xlsx,csv,xls',
        ]);

        try {
            Excel::import(new SiswaImport($request->kelas_id), $request->file('file'));
            return redirect()->route('admin.siswa.index')->with('success', 'Data siswa berhasil diimpor.');
        } catch (\Maatwebsite\Excel\Validators\ValidationException $e) {
            $failures = $e->failures();
            $errors = [];
            foreach ($failures as $failure) {
                $errors[] = 'Baris ' . $failure->row() . ': ' . implode(', ', $failure->errors());
            }
            return back()->with('error', 'Gagal impor:<br>' . implode('<br>', $errors));
        } catch (\Exception $e) {
            return back()->with('error', 'Terjadi kesalahan saat mengimpor data.');
        }
    }
}
