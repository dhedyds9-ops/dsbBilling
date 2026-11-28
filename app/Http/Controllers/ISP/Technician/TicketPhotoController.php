<?php

namespace App\Http\Controllers\Isp\Technician;

use App\Http\Controllers\Controller;
use App\Models\Support\Ticket;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class TicketPhotoController extends Controller
{
    /**
     * Tampilkan atau unduh foto bukti tiket/pekerjaan dengan otorisasi berbasis cabang dan penugasan.
     */
    public function show(Request $request, $ticketId, string $filename): StreamedResponse
    {
        $user = Auth::user();
        if (!$user) {
            abort(401, 'Silakan login terlebih dahulu untuk mengakses bukti pekerjaan.');
        }

        // Ambil data tiket tanpa global scopes untuk memastikan otorisasi manual akurat
        $ticket = Ticket::withoutGlobalScopes()->with('customer')->findOrFail($ticketId);

        // 1. Evaluasi Hak Akses (Otorisasi)
        $isGlobalAdmin = $user->hasRole(['superadmin', 'administrator'])
            || in_array(strtolower((string) ($user->role ?? '')), ['superadmin', 'administrator'], true)
            || (method_exists($user, 'isSuperAdmin') && $user->isSuperAdmin())
            || $user->can('view-all-tickets');

        if (!$isGlobalAdmin) {
            $ticketBranchId = $ticket->branch_id ?? $ticket->customer?->branch_id;
            $userBranchId = $user->branch_id;

            $isManager = $user->hasRole('manager')
                || strtolower((string) ($user->role ?? '')) === 'manager'
                || $user->can('manage-support');

            if ($isManager) {
                // Manager hanya diizinkan melihat tiket di cabangnya (atau semua jika tidak terikat cabang)
                if ($userBranchId && $ticketBranchId && (int) $userBranchId !== (int) $ticketBranchId) {
                    abort(403, 'Anda tidak memiliki hak akses untuk melihat bukti tiket di luar cabang Anda.');
                }
            } else {
                // Teknisi / Staff Lapangan:
                // Harus ditugaskan pada tiket ATAU berada dalam cabang yang sama
                $isAssigned = ((int) ($ticket->assigned_to ?? 0) === (int) $user->id)
                    || ((int) ($ticket->technician_id ?? 0) === (int) $user->id);

                $isSameBranch = ($userBranchId && $ticketBranchId && (int) $userBranchId === (int) $ticketBranchId);

                if (!$isAssigned && !$isSameBranch) {
                    abort(403, 'Anda tidak memiliki hak akses untuk melihat bukti pekerjaan tiket ini.');
                }
            }
        }

        // 2. Sanitasi nama berkas untuk mencegah Directory Traversal
        $safeFilename = basename($filename);
        $relativePath = 'tickets/' . $ticket->id . '/' . $safeFilename;

        // 3. Pencarian Berkas (Prioritas: Private Storage 'local', Fallback: Legacy 'public', Multi-root & Glob)
        $filePath = null;

        if (Storage::disk('local')->exists($relativePath)) {
            $filePath = Storage::disk('local')->path($relativePath);
        } elseif (Storage::disk('public')->exists($relativePath)) {
            $filePath = Storage::disk('public')->path($relativePath);
        }

        if (!$filePath || !file_exists($filePath)) {
            $candidatePaths = [
                storage_path('app/private/public/' . $relativePath),
                storage_path('app/private/evidence/tickets/' . $ticket->id . '/' . $safeFilename),
                storage_path('app/public/evidence/tickets/' . $ticket->id . '/' . $safeFilename),
                storage_path('app/evidence/tickets/' . $ticket->id . '/' . $safeFilename),
                '/var/www/mstore/storage/app/private/' . $relativePath,
                '/var/www/mstore/storage/app/public/' . $relativePath,
                '/var/www/winets/storage/app/private/' . $relativePath,
                '/var/www/winets/storage/app/public/' . $relativePath,
            ];
            foreach ($candidatePaths as $cp) {
                if (@file_exists($cp) && @is_file($cp)) {
                    $filePath = $cp;
                    break;
                }
            }
        }

        if (!$filePath || !file_exists($filePath)) {
            // Glob search across bases
            $bases = [
                storage_path('app/public'),
                storage_path('app/private'),
                '/var/www/mstore/storage/app',
                '/var/www/winets/storage/app',
            ];
            foreach ($bases as $b) {
                if (!@is_dir($b)) continue;
                $matches = @glob($b . '/*/*/' . $safeFilename);
                if (empty($matches)) $matches = @glob($b . '/*/*/*/' . $safeFilename);
                if (!empty($matches)) {
                    $filePath = $matches[0];
                    break;
                }
            }
        }

        if (!$filePath || !file_exists($filePath)) {
            // Return clean SVG placeholder so <img> tags never render broken boxes
            $fnSafe = htmlspecialchars(substr($safeFilename, 0, 32));
            $svg = '<svg xmlns="http://www.w3.org/2000/svg" width="400" height="250" viewBox="0 0 400 250">
                <rect width="100%" height="100%" fill="#0f172a" rx="12"/>
                <circle cx="200" cy="95" r="34" fill="#1e293b" stroke="#334155" stroke-width="2"/>
                <path d="M185 103 L215 103 M190 87 L200 77 L210 77 L220 87 L225 87 C228 87 230 89 230 92 L230 108 C230 111 228 113 225 113 L175 113 C172 113 170 111 170 108 L170 92 C170 89 172 87 175 87 Z" fill="none" stroke="#64748b" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                <circle cx="200" cy="99" r="6" fill="none" stroke="#64748b" stroke-width="2"/>
                <text x="200" y="155" fill="#94a3b8" font-family="system-ui, sans-serif" font-size="13" font-weight="bold" text-anchor="middle">Foto Bukti Tiket</text>
                <text x="200" y="175" fill="#64748b" font-family="system-ui, sans-serif" font-size="11" text-anchor="middle">Menunggu Sinkronisasi / ' . $fnSafe . '</text>
            </svg>';
            return response()->stream(
                function () use ($svg) {
                    echo $svg;
                },
                200,
                [
                    'Content-Type' => 'image/svg+xml',
                    'Cache-Control' => 'no-cache',
                ]
            );
        }

        // 4. Sajikan Berkas dengan StreamedResponse (Aman dari pembatasan Windows ACL & Hemat Memori)
        $mimeType = @mime_content_type($filePath) ?: 'image/jpeg';
        $fileSize = @filesize($filePath) ?: null;

        $headers = [
            'Content-Type' => $mimeType,
            'Cache-Control' => 'private, no-cache, no-store, must-revalidate',
            'X-Content-Type-Options' => 'nosniff',
        ];

        if ($fileSize) {
            $headers['Content-Length'] = $fileSize;
        }

        return response()->stream(
            function () use ($filePath) {
                $stream = fopen($filePath, 'rb');
                if ($stream) {
                    fpassthru($stream);
                    fclose($stream);
                }
            },
            200,
            $headers
        );
    }
}
