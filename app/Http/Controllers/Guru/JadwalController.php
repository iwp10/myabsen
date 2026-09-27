<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Models\Guru;
use App\Models\Jadwal;
use Illuminate\Http\Request;

class JadwalController extends Controller
{
    /**
     * Menampilkan daftar seluruh jadwal mengajar guru dalam seminggu.
     */
    public function index(Request $request)
    {
        $guru = Guru::where('user_id', $request->user()->id)->first();

        if (! $guru) {
            $jadwals = collect();
        } else {
            $jadwals = Jadwal::with(['kelas.jurusan', 'mapel'])
                ->where('guru_id', $guru->id)
                ->orderByRaw("CASE hari 
                    WHEN 'senin' THEN 1 
                    WHEN 'selasa' THEN 2 
                    WHEN 'rabu' THEN 3 
                    WHEN 'kamis' THEN 4 
                    WHEN 'jumat' THEN 5 
                    WHEN 'sabtu' THEN 6 
                    ELSE 7 END")
                ->orderBy('jam_mulai', 'asc')
                ->get();
        }

        return view('guru.jadwal', compact('jadwals', 'guru'));
    }
}
