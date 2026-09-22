<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreJurusanRequest;
use App\Http\Requests\UpdateJurusanRequest;
use App\Models\Jurusan;

class JurusanController extends Controller
{
    public function index()
    {
        $jurusans = Jurusan::orderBy('nama')->paginate(10);
        return view('admin.jurusan.index', compact('jurusans'));
    }

    public function create()
    {
        return view('admin.jurusan.create');
    }

    public function store(StoreJurusanRequest $request)
    {
        Jurusan::create($request->validated());

        return redirect()->route('admin.jurusan.index')
            ->with('success', 'Jurusan berhasil ditambahkan.');
    }

    public function edit(Jurusan $jurusan)
    {
        return view('admin.jurusan.edit', compact('jurusan'));
    }

    public function update(UpdateJurusanRequest $request, Jurusan $jurusan)
    {
        $jurusan->update($request->validated());

        return redirect()->route('admin.jurusan.index')
            ->with('success', 'Jurusan berhasil diperbarui.');
    }

    public function destroy(Jurusan $jurusan)
    {
        // Check if there are classes associated with this jurusan
        if ($jurusan->kelas()->exists()) {
            return redirect()->route('admin.jurusan.index')
                ->with('error', 'Jurusan tidak bisa dihapus karena masih memiliki kelas.');
        }

        $jurusan->delete();

        return redirect()->route('admin.jurusan.index')
            ->with('success', 'Jurusan berhasil dihapus.');
    }
}
