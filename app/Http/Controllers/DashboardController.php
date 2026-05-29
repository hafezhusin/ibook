<?php

/**
 * iBook --- Sistem Pengurusan Bilik Mesyuarat
 * Copyright (c) 2026 Bahagian Pengurusan Teknologi Maklumat (BPTM)
 * Hak cipta terpelihara. Dilarang meniru, menyalin, mengubah suai, atau
 * mengedar perisian ini tanpa kebenaran bertulis daripada pemilik hak cipta.
 *
 * Pembangun : Mohd Hafez bin Husin (Unit Aplikasi Gunasama)
 *
 * Unauthorized copying, modification, distribution, or use of this software,
 * via any medium, is strictly prohibited. Proprietary and confidential.
 */

namespace App\Http\Controllers;

use App\Models\Bahagian;
use App\Services\DashboardService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function __construct(private DashboardService $service) {}

    public function index(Request $request)
    {
        $user = Auth::user();

        // Filter bahagian — hanya untuk pentadbir sistem
        $bahagianFilter = null;
        if ($user->isPentadbir() && $request->filled('bahagian_id')) {
            $bahagianFilter = (int) $request->bahagian_id;
        }

        $bahagianList = $user->isPentadbir()
            ? Bahagian::where('aktif', true)->orderBy('kod')->get()
            : collect();

        $data = $this->service->getData($user, $bahagianFilter);
        $data['bahagianList']   = $bahagianList;
        $data['bahagianFilter'] = $bahagianFilter;

        return view('dashboard.index', $data);
    }
}
