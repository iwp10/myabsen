<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ArsipRequest;
use App\Services\ArsipService;
use Illuminate\Http\RedirectResponse;
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
}
