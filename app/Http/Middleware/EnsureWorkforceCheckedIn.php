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
            $user = Auth::user();

            // 1. Administrator / Superadmin yang murni bertindak sebagai pengawas (oversight) dikecualikan
            $isPureAdminOversight = $user->hasRole(['administrator', 'superadmin'])
                && !in_array(strtoupper((string) ($user->job_function ?? '')), ['TECHNICIAN', 'NOC', 'GUDANG', 'KASIR', 'CS'])
                && !str_contains(strtolower((string) ($user->job_title ?? '')), 'gudang')
                && !str_contains(strtolower((string) ($user->job_title ?? '')), 'teknisi');

            if ($isPureAdminOversight) {
                return $next($request);
            }

            // Customer dan Reseller bukan staf/karyawan operasional internal
            if ($user->hasRole(['customer', 'reseller']) && !$user->hasRole(['admin_gudang', 'technician', 'noc', 'kasir', 'customer_service'])) {
                return $next($request);
            }

            // 2. Evaluasi peran staf operasional yang wajib presensi masuk:
            // Termasuk: Admin Gudang, Teknisi, NOC, Kasir / Billing, dan Customer Service (CS)
            $isWorkforce = $user->hasRole(['admin_gudang', 'technician', 'noc', 'kasir', 'customer_service'])
                || in_array(strtoupper((string) ($user->job_function ?? '')), ['TECHNICIAN', 'NOC', 'GUDANG', 'KASIR', 'CS', 'SALES'])
                || str_contains(strtolower((string) ($user->job_title ?? '')), 'gudang')
                || str_contains(strtolower((string) ($user->job_title ?? '')), 'logistik')
                || str_contains(strtolower((string) ($user->job_title ?? '')), 'teknisi')
                || str_contains(strtolower((string) ($user->job_title ?? '')), 'kasir')
                || str_contains(strtolower((string) ($user->job_title ?? '')), 'billing')
                || str_contains(strtolower((string) ($user->job_title ?? '')), 'customer service')
                || str_contains(strtolower((string) ($user->job_title ?? '')), 'cs');

            if (!$isWorkforce) {
                return $next($request);
            }

            // 3. Cek apakah sudah absen masuk hari ini
            $hasCheckedIn = Attendance::where('technician_id', $user->id)
                ->whereDate('date', Carbon::today())
                ->whereNotNull('checked_in_at')
                ->exists();

            if (!$hasCheckedIn) {
                $warningMsg = "Halo {$user->name}, Anda belum melakukan Absen Hadir hari ini. Silakan klik tombol Absen Masuk untuk memulai shift dan melayani transaksi.";

                // Beri sinyal session flash agar pop-up modal dan banner langsung terbuka di halaman
                session()->flash('must_check_in_popup', true);
                session()->flash('workforce_attendance_warning', $warningMsg);

                // Jika aksi adalah mutasi data (POST / PUT / PATCH / DELETE), blokir agar tidak dapat mengeksekusi transaksi sebelum absen
                if (in_array($request->method(), ['POST', 'PUT', 'PATCH', 'DELETE'])) {
                    // Pengecualian: Jangan blokir aksi presensi Livewire atau endpoint API absensi
                    if ($request->is('livewire/*') || $request->is('api/mobile/attendance/*') || $request->routeIs('technician.attendance')) {
                        return $next($request);
                    }

                    if ($request->expectsJson() || $request->is('api/*')) {
                        return response()->json([
                            'success' => false,
                            'message' => $warningMsg,
                            'requires_checkin' => true,
                        ], 403);
                    }

                    return back()->with('errorMessage', $warningMsg);
                }

                // Redirect khusus untuk teknisi portal jika mencoba langsung membuka halaman tugas
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
}
