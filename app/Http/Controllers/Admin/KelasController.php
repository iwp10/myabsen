<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreKelasRequest;
use App\Http\Requests\UpdateKelasRequest;
use App\Models\Kelas;
use App\Models\Jurusan;

class KelasController extends Controller
{
    public function index()
    {
        $kelas = Kelas::with('jurusan')->orderBy('tingkat')->orderBy('nama')->paginate(10);
        return view('admin.kelas.index', compact('kelas'));
    }

    public function create()
    {
        $jurusans = Jurusan::orderBy('nama')->get();
        return view('admin.kelas.create', compact('jurusans'));
    }

    public function store(StoreKelasRequest $request)
    {
        Kelas::create($request->validated());

        return redirect()->route('admin.kelas.index')
            ->with('success', 'Kelas berhasil ditambahkan.');
    }

    public function edit(Kelas $kelas)
    {
        $jurusans = Jurusan::orderBy('nama')->get();
        return view('admin.kelas.edit', compact('kelas', 'jurusans'));
    }

    public function update(UpdateKelasRequest $request, Kelas $kelas)
    {
        $kelas->update($request->validated());

        return redirect()->route('admin.kelas.index')
            ->with('success', 'Kelas berhasil diperbarui.');
    }

    public function destroy(Kelas $kelas)
    {
        $kelas->delete();

        return redirect()->route('admin.kelas.index')
            ->with('success', 'Kelas berhasil dihapus.');
    }
}
