<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Workforce\Attendance;
use Carbon\Carbon;

class EnsureWorkforceCheckedIn
{
    public function handle(Request $request, Closure $next)
    {
        if (Auth::check()) {
            $hasCheckedIn = Attendance::where('technician_id', Auth::id())
                ->whereDate('date', Carbon::today())
                ->whereNotNull('checked_in_at')
                ->exists();

            if (!$hasCheckedIn) {
                // Determine redirect route based on current portal
                if ($request->is('noc/*') || $request->is('noc')) {
                    if (!$request->routeIs('noc.overview')) {
                        return redirect()->route('noc.overview')
                            ->with('errorMessage', 'Silakan lakukan Check-In di pojok kanan atas layar terlebih dahulu.');
                    }
                }
                
                if ($request->is('technician-portal/*') || $request->is('technician-portal')) {
                    if (!$request->routeIs('technician.attendance') && !$request->routeIs('technician.dashboard')) {
                        return redirect()->route('technician.attendance')
                            ->with('errorMessage', 'Silakan lakukan Check-In terlebih dahulu untuk melihat dan memproses tugas hari ini.');
                    }
                }
            }
        }

        return $next($request);
    }
}
