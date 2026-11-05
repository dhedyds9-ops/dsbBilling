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

        // 3. Pencarian Berkas (Prioritas: Private Storage 'local', Fallback: Legacy 'public')
        $filePath = null;

        if (Storage::disk('local')->exists($relativePath)) {
            $filePath = Storage::disk('local')->path($relativePath);
        } elseif (Storage::disk('public')->exists($relativePath)) {
            $filePath = Storage::disk('public')->path($relativePath);
        }

        if (!$filePath || !file_exists($filePath)) {
            abort(404, 'File bukti foto pekerjaan tidak ditemukan.');
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
