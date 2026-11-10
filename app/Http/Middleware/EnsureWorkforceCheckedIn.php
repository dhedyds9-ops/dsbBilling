<?php

namespace App\Http\Middleware;

use App\Services\Workforce\AttendanceAuthorizationService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EnsureWorkforceCheckedIn
{
    /**
     * Handle an incoming request.
     * Enforce attendance-based operational access control (Implementation Directive).
     */
    public function handle(Request $request, Closure $next)
    {
        $user = Auth::user() ?? $request->user();

        if ($user) {
            // 1. Pengecualian Endpoint: Jangan pernah blokir endpoint autentikasi, status absensi, check-in, payroll pribadi, dan bootstrap
            if ($this->isExemptEndpoint($request)) {
                return $next($request);
            }

            // 2. Evaluasi apakah role/job user dikecualikan dari kewajiban presensi staf (misal pure oversight admin)
            if (AttendanceAuthorizationService::isExemptFromAttendance($user)) {
                return $next($request);
            }

            // 3. Evaluasi presensi aktif (checked_in_at ada, checked_out_at null, shift valid, status aktif)
            $hasActiveAttendance = AttendanceAuthorizationService::hasActiveAttendance($user);

            if (!$hasActiveAttendance) {
                $warningMsg = "Pengguna harus melakukan absensi masuk sebelum menjalankan aktivitas operasional.";

                // Response JSON untuk API Mobile & Client REST
                if ($request->expectsJson() || $request->is('api/*')) {
                    return response()->json([
                        'success' => false,
                        'code' => 'ATTENDANCE_REQUIRED',
                        'message' => $warningMsg,
                        'attendance_required' => true,
                    ], 403);
                }

                // Beri sinyal session flash untuk Web Portal
                session()->flash('must_check_in_popup', true);
                session()->flash('workforce_attendance_warning', "Halo {$user->name}, Anda belum melakukan Absen Hadir aktif. Silakan lakukan Absen Masuk untuk membuka akses operasional.");

                // Blokir aksi mutasi data pada Web
                if (in_array($request->method(), ['POST', 'PUT', 'PATCH', 'DELETE'])) {
                    if ($request->is('livewire/*') || $request->routeIs('technician.attendance')) {
                        return $next($request);
                    }
                    return back()->with('errorMessage', $warningMsg);
                }

                // Redirect pada Web Portal Teknisi jika mencoba membuka halaman operasional langsung
                if ($request->is('technician-portal/*') || $request->is('technician-portal')) {
                    if (!$request->routeIs('technician.attendance') && !$request->routeIs('technician.dashboard')) {
                        return redirect()->route('technician.attendance')
                            ->with('errorMessage', $warningMsg);
                    }
                }
            }
        }

        return $next($request);
    }

    /**
     * Determine if the requested URL is an exempt endpoint that does not require active attendance.
     */
    protected function isExemptEndpoint(Request $request): bool
    {
        // Allow mobile auth, attendance management, payroll history, and sync bootstrap
        if ($request->is('api/mobile/attendance*')
            || $request->is('api/mobile/me')
            || $request->is('api/mobile/logout')
            || $request->is('api/mobile/payroll*')
            || $request->is('api/mobile/notifications*')
            || $request->is('api/mobile/sync/bootstrap')
            || ($request->is('api/mobile/customers*') && $request->isMethod('GET'))
            || $request->is('api/mobile/app/*')
            || $request->is('api/mobile/version/*')
            || $request->routeIs('technician.attendance')
            || $request->is('technician-portal/attendance*')
            || $request->is('technician-portal/dashboard')
        ) {
            return true;
        }

        return false;
    }
}
