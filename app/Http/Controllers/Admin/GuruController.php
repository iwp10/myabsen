<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreGuruRequest;
use App\Http\Requests\UpdateGuruRequest;
use App\Models\Guru;
use App\Models\SesiAbsensi;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

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
        DB::transaction(function () use ($request) {
            $user = User::create([
                'name' => $request->name,
                'username' => $request->nip,
                'password' => Hash::make('password'),
                'role' => 'guru',
            ]);

            Guru::create([
                'user_id' => $user->id,
                'nip' => $request->nip,
            ]);
        });

        return redirect()->route('admin.guru.index')
            ->with('success', 'Guru berhasil ditambahkan.');
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
        $hasHistory = SesiAbsensi::where('diabsen_oleh', $guru->user_id)
            ->orWhere('diubah_oleh', $guru->user_id)
            ->orWhereHas('jadwal', function ($q) use ($guru) {
                $q->where('guru_id', $guru->id);
            })
            ->exists();

        if ($hasHistory) {
            $guru->delete();

            return redirect()->route('admin.guru.index')
                ->with('success', 'Guru di-soft-delete karena memiliki riwayat absensi.');
        }

        // Hard delete user, this will cascade and hard delete the guru as well
        $guru->user()->delete();

        return redirect()->route('admin.guru.index')
            ->with('success', 'Guru beserta akun berhasil dihapus.');
    }

    public function resetPassword(Guru $guru)
    {
        $guru->user->update([
            'password' => Hash::make('password'),
        ]);

        return redirect()->route('admin.guru.index')
            ->with('success', 'Password guru berhasil direset ke "password".');
    }
}
