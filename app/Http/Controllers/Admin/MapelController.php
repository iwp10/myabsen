<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreMapelRequest;
use App\Http\Requests\UpdateMapelRequest;
use App\Models\Mapel;

class MapelController extends Controller
{
    public function index()
    {
        $mapels = Mapel::orderBy('nama')->paginate(10);
        return view('admin.mapel.index', compact('mapels'));
    }

    public function create()
    {
        return view('admin.mapel.create');
    }

    public function store(StoreMapelRequest $request)
    {
        Mapel::create($request->validated());

        return redirect()->route('admin.mapel.index')
            ->with('success', 'Mata Pelajaran berhasil ditambahkan.');
    }

    public function edit(Mapel $mapel)
    {
        return view('admin.mapel.edit', compact('mapel'));
    }

    public function update(UpdateMapelRequest $request, Mapel $mapel)
    {
        $mapel->update($request->validated());

        return redirect()->route('admin.mapel.index')
            ->with('success', 'Mata Pelajaran berhasil diperbarui.');
    }

    public function destroy(Mapel $mapel)
    {
        $mapel->delete();

        return redirect()->route('admin.mapel.index')
            ->with('success', 'Mata Pelajaran berhasil dihapus.');
    }
}
