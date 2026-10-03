<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Models\Guru;
use App\Services\AbsensiService;
use Illuminate\Http\Request;

class JadwalController extends Controller
{
    public function __construct(protected AbsensiService $absensiService) {}

    /**
     * Menampilkan daftar seluruh jadwal mengajar guru dalam seminggu.
     */
    public function index(Request $request)
    {
        $guru = Guru::where('user_id', $request->user()->id)->first();
        $jadwals = $this->absensiService->getJadwalMingguanGuru($request->user()->id);

        return view('guru.jadwal', compact('jadwals', 'guru'));
    }
}
