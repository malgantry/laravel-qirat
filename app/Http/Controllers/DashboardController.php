<?php

namespace App\Http\Controllers;

use App\Services\DashboardService;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __invoke(Request $request, DashboardService $dashboardService)
    {
        if (auth()->check() && auth()->user()->is_admin && ! $request->wantsJson()) {
            return redirect()->route('admin.dashboard');
        }

        // Guests see a lightweight landing page before authentication.
        if (! auth()->check() && ! $request->wantsJson()) {
            return view('landing');
        }

        $data = $dashboardService->getData(false); // Speed: skip AI insights on initial load

        if ($request->wantsJson()) {
            return response()->json($data['dashboardData']);
        }

        return view('dashboard', $data);
    }
}

