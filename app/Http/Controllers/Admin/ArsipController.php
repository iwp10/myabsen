<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ArsipRequest;
use App\Http\Requests\Admin\MassalArsipRequest;
use App\Services\ArsipService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class ArsipController extends Controller
{
    public function __construct(
        protected ArsipService $arsipService
    ) {}

    public function index(ArsipRequest $request): View
    {
        $jenis = $request->query('jenis', 'guru');
        $search = $request->query('search');

        $counts = $this->arsipService->getCounts();
        $items = $this->arsipService->getDaftar($jenis, $search);

        return view('admin.arsip.index', compact('jenis', 'search', 'counts', 'items'));
    }

    public function pulihkan(string $jenis, int $id): RedirectResponse
    {
        $result = $this->arsipService->pulihkan($jenis, (int) $id);

        if (! $result['success']) {
            return redirect()
                ->route('admin.arsip.index', ['jenis' => $jenis])
                ->with('error', $result['message']);
        }

        return redirect()
            ->route('admin.arsip.index', ['jenis' => $jenis])
            ->with('success', $result['message']);
    }

    public function massalPulihkan(MassalArsipRequest $request, string $jenis): RedirectResponse
    {
        if (! $this->arsipService->isValidJenis($jenis)) {
            abort(404);
        }

        $ids = $request->validated('ids');
        $berhasil = 0;
        $ditolak = [];

        foreach ($ids as $id) {
            $result = $this->arsipService->pulihkan($jenis, (int) $id);

            if ($result['success']) {
                $berhasil++;
            } else {
                $ditolak[] = $result['message'];
            }
        }

        $countBerhasil = $berhasil;
        $countDitolak = count($ditolak);

        Log::info('Aksi massal admin: pulihkan data terhapus', [
            'admin_id' => auth()->id(),
            'aksi' => 'massal_pulihkan_arsip',
            'jenis' => $jenis,
            'ids' => $ids,
            'berhasil' => $countBerhasil,
            'ditolak' => $countDitolak,
        ]);

        $params = array_merge(['jenis' => $jenis], $request->query());
        $label = ucfirst($jenis);

        if ($countDitolak === 0) {
            return redirect()
                ->route('admin.arsip.index', $params)
                ->with('success', "{$countBerhasil} data {$label} berhasil dipulihkan.");
        }

        $flashKey = $countBerhasil > 0 ? 'warning' : 'error';
        $firstDitolak = array_slice($ditolak, 0, 10);
        $sisaDitolak = $countDitolak - count($firstDitolak);

        $pesanDitolak = "{$countBerhasil} berhasil, {$countDitolak} ditolak.";
        $detailList = collect($firstDitolak)->map(fn ($msg) => '<li>'.e($msg).'</li>')->implode('');
        if ($sisaDitolak > 0) {
            $detailList .= "<li>...dan {$sisaDitolak} lainnya.</li>";
        }
        $pesanLengkap = "<div>{$pesanDitolak}<ul class=\"mt-1 list-disc pl-5 space-y-0.5 text-xs\">{$detailList}</ul></div>";

        return redirect()
            ->route('admin.arsip.index', $params)
            ->with($flashKey, $pesanLengkap);
    }
}
