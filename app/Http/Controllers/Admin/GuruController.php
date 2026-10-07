<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreGuruRequest;
use App\Http\Requests\UpdateGuruRequest;
use App\Models\Guru;
use App\Models\User;
use App\Services\PasswordAwalService;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use RuntimeException;

class GuruController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->search;
        $gurus = Guru::with('user')
            ->when($search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('nip', 'like', "%{$search}%")
                        ->orWhereHas('user', function ($sub) use ($search) {
                            $sub->where('name', 'like', "%{$search}%");
                        });
                });
            })
            ->paginate(10)
            ->appends(['search' => $search]);

        return view('admin.guru.index', compact('gurus'));
    }

    public function create()
    {
        return view('admin.guru.create');
    }

    public function store(StoreGuruRequest $request)
    {
        try {
            $passwordAwal = PasswordAwalService::get();

            DB::transaction(function () use ($request, $passwordAwal) {
                $user = User::create([
                    'name' => $request->name,
                    'username' => $request->nip,
                    'password' => Hash::make($passwordAwal),
                    'role' => 'guru',
                    'must_change_password' => true,
                ]);

                Guru::create([
                    'user_id' => $user->id,
                    'nip' => $request->nip,
                ]);
            });

            return redirect()->route('admin.guru.index')
                ->with('success', 'Guru berhasil ditambahkan.');
        } catch (RuntimeException $e) {
            return back()->withInput()->with('error', 'Password awal belum diatur dengan aman. Hubungi pengelola sistem.');
        }
    }

    public function edit(Guru $guru)
    {
        $guru->load('user');

        return view('admin.guru.edit', compact('guru'));
    }

    public function update(UpdateGuruRequest $request, Guru $guru)
    {
        DB::transaction(function () use ($request, $guru) {
            $guru->user->update([
                'name' => $request->name,
                'username' => $request->nip,
            ]);

            $guru->update([
                'nip' => $request->nip,
            ]);
        });

        return redirect()->route('admin.guru.index')
            ->with('success', 'Guru berhasil diperbarui.');
    }

    public function destroy(Guru $guru)
    {
        $hasHistory = $guru->jadwal()->whereHas('sesiAbsensi')->exists();

        if ($hasHistory) {
            $guru->delete();

            return redirect()->route('admin.guru.index')
                ->with('success', 'Guru di-soft-delete karena memiliki riwayat absensi.');
        }

        try {
            DB::transaction(function () use ($guru) {
                $user = $guru->user;
                $guru->forceDelete();
                $user?->delete();
            });

            return redirect()->route('admin.guru.index')
                ->with('success', 'Guru beserta akun berhasil dihapus.');
        } catch (QueryException $e) {
            return redirect()->route('admin.guru.index')
                ->with('error', 'Akun tidak dapat dihapus karena memiliki riwayat absensi.');
        }
    }

    public function resetPassword(Guru $guru)
    {
        try {
            $passwordAwal = PasswordAwalService::get();

            $guru->user->update([
                'password' => Hash::make($passwordAwal),
                'must_change_password' => true,
            ]);

            return redirect()->route('admin.guru.index')
                ->with('success', 'Password guru berhasil direset ke password awal.');
        } catch (RuntimeException $e) {
            return back()->with('error', 'Password awal belum diatur dengan aman. Hubungi pengelola sistem.');
        }
    }
}
