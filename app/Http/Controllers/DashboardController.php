<?php

namespace App\Http\Controllers;

use App\Services\DashboardService;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function __construct(private DashboardService $service) {}

    public function index()
    {
        $user = Auth::user();
        $data = $this->service->getData($user);

        return view('dashboard.index', $data);
    }
}
