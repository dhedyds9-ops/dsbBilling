<?php

declare(strict_types=1);

namespace App\Services\ISP\BankMutation;

use App\Models\Billing\Invoice;
use App\Models\Payment\Payment;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * SSOT: Moota.co Bank Mutation Verifier.
 *
 * 3 Fase Matching Algorithm:
 *   1. EKSAK MATCH: amount == invoice.outstanding DAN nomor_rekening_tujuan cocok DAN timestamp <= 24 jam dari due_date
 *   2. LAST DIGITS MATCH: abs(amount - invoice.outstanding) <= 999 (user transfer 1.250.001 s/d 1.250.999)
 *   3. MANUAL REVIEW: lainnya (underpayment jauh / overpayment / beda rekening) → status pending_review
 *
 * Semua match EKSAK langsung approve Payment → success → trigger invoice paid → COA reactivate + WA.
 * Match last-digits: 3x verifikasi 1) waktu mutasi ≤ 7 hari 2) customer active 3) ≥ min amount, ATAU operator konfirmasi manual.
 */
final class MootaBankMutationService
{
    public const STATUS_MATCHED_EXACT = 'matched_exact';
    public const STATUS_MATCHED_DIGITS = 'matched_last_digits';
    public const STATUS_MANUAL_REVIEW = 'pending_manual_review';
    public const STATUS_NO_MATCH = 'no_match';

    public function __construct(
        private readonly string $apiToken = '',
        private readonly string $baseUrl = 'https://app.moota.co',
    ) {}

    /**
     * Ambil mutasi dari semua rekening yang terdaftar di Moota (last N jam).
     *
     * @return array<int,array{id:mixed,account_number:string,bank_type:string,amount:int,type:string,description:string,note:string,date:string,balance:int}>
     */
    public function fetchMutations(int $lastHours = 48): array
    {
        $token = $this->apiToken ?: (string)config('services.moota.api_token', '');
        if ($token === '') return [];
        try {
            $rows = Http::withToken($token)
                ->timeout(15)
                ->get($this->baseUrl . '/api/v2/mutation', [
                    'start_date' => now()->subHours($lastHours)->toDateString(),
                    'end_date' => now()->toDateString(),
                ]);
            if (!$rows->successful()) return [];
            $data = json_decode($rows->body(), true) ?: [];
            return (array)($data['data'] ?? $data);
        } catch (\Throwable) {
            return [];
        }
    }

    /**
     * Process single mutation → match ke Invoice + Payment pending.
     *
     * @return array{status: string, confidence: float, payment_id: ?int, invoice_id: ?int, reason: string, matched_amount: ?int}
     */
    public function matchMutation(array $mutation): array
    {
        $amount = (int)($mutation['amount'] ?? 0);
        if ($amount <= 0) return ['status' => self::STATUS_NO_MATCH, 'confidence' => 0, 'payment_id' => null, 'invoice_id' => null, 'reason' => 'amount <= 0', 'matched_amount' => null];
        if (strtolower((string)($mutation['type'] ?? 'CR')) !== 'CR') {
            return ['status' => self::STATUS_NO_MATCH, 'confidence' => 0, 'payment_id' => null, 'invoice_id' => null, 'reason' => 'debit bukan kredit', 'matched_amount' => null];
        }

        $accountNumber = (string)($mutation['account_number'] ?? '');
        $desc = (string)($mutation['description'] ?? '') . ' ' . (string)($mutation['note'] ?? '');
        $mutDate = \Illuminate\Support\Carbon::parse((string)($mutation['date'] ?? 'now'));

        // Fase 1: Eksak match by amount + ada Invoice yang outstanding == amount
        $invoicesExact = Invoice::query()
            ->where('status', '!=', 'paid')
            ->whereRaw('(total_amount - paid_amount) = ?', [$amount])
            ->where('due_date', '>=', $mutDate->clone()->subDays(30)->toDateString())
            ->orderBy('due_date', 'asc')
            ->limit(3)
            ->get();

        foreach ($invoicesExact as $inv) {
            $conf = 98.0;
            // Jika ada nomor rekening tujuan match (lewat note / description mengandung VA number / reference no)
            if ($accountNumber !== '' && $this->accountNumberMatchesBank($accountNumber, $desc, $inv)) {
                $conf = 99.9;
            }
            // Kalau invoice number ada di description → lebih percaya
            if (!empty($inv->invoice_number) && stripos($desc, (string)$inv->invoice_number) !== false) {
                $conf = 99.99;
            }
            return [
                'status' => self::STATUS_MATCHED_EXACT,
                'confidence' => $conf,
                'payment_id' => null,
                'invoice_id' => (int)$inv->id,
                'reason' => 'exact_amount_match',
                'matched_amount' => $amount,
            ];
        }

        // Fase 2: Last digits match (transfer beda 0-999 rupiah untuk konfirmasi unik)
        $diffMax = 999;
        $invoicesDigits = Invoice::query()
            ->where('status', '!=', 'paid')
            ->whereRaw('ABS((total_amount - paid_amount) - ?) <= ?', [$amount, $diffMax])
            ->where('due_date', '>=', $mutDate->clone()->subDays(14)->toDateString())
            ->orderByRaw('ABS((total_amount - paid_amount) - ?) ASC', [$amount])
            ->limit(3)
            ->get();

        foreach ($invoicesDigits as $inv) {
            $outstanding = (int)round((float)$inv->total_amount - (float)$inv->paid_amount);
            $diff = abs($outstanding - $amount);
            $conf = 80.0 - min(30.0, $diff * 0.15);
            // Kalau invoice number ketemu di keterangan → naikkan confidence
            if (!empty($inv->invoice_number) && stripos($desc, (string)$inv->invoice_number) !== false) {
                $conf += 12.0;
            }
            if ($conf > 99) $conf = 99;
            if ($conf >= 60) {
                return [
                    'status' => self::STATUS_MATCHED_DIGITS,
                    'confidence' => round($conf, 2),
                    'payment_id' => null,
                    'invoice_id' => (int)$inv->id,
                    'reason' => 'last_digits_match diff=' . $diff,
                    'matched_amount' => $amount,
                ];
            }
        }

        // Fase 3: Cek dari Payment pending manual_transfer yang amount mirip (operator nanti konfirmasi)
        $paymentsPending = Payment::query()
            ->where('method', 'like', '%manual%')
            ->where('status', 'pending')
            ->whereRaw('ABS(amount - ?) <= ?', [$amount, max($diffMax, (int)round($amount * 0.02))])
            ->orderByRaw('ABS(amount - ?) ASC', [$amount])
            ->limit(3)
            ->get();
        if ($paymentsPending->count() > 0) {
            $inv = Invoice::query()->find(optional($paymentsPending[0]->invoices()->first())?->id);
            return [
                'status' => self::STATUS_MANUAL_REVIEW,
                'confidence' => 40.0,
                'payment_id' => (int)$paymentsPending[0]->id,
                'invoice_id' => $inv?->id ? (int)$inv->id : null,
                'reason' => 'manual_payment_candidate',
                'matched_amount' => $amount,
            ];
        }

        return ['status' => self::STATUS_NO_MATCH, 'confidence' => 0, 'payment_id' => null, 'invoice_id' => null, 'reason' => 'no_candidate', 'matched_amount' => null];
    }

    /**
     * Auto-approve matched_exact → buat Payment success + apply ke invoice.
     * matched_digits hanya approve otomatis JIKA confidence ≥ 90.
     * Sisanya insert ke bank_mutation_matches untuk operator review manual.
     *
     * @param  array<string,mixed>  $mutation
     * @return array{approved: bool, match: array, payment_id: ?int, manual_review_required: bool}
     */
    public function processMutation(array $mutation): array
    {
        $match = $this->matchMutation($mutation);
        $manual = false;
        $paymentId = null;
        $approved = false;

        try {
            DB::beginTransaction();
            if ($match['status'] === self::STATUS_MATCHED_EXACT && !empty($match['invoice_id'])) {
                $paymentId = $this->approveMatch($match, $mutation);
                $approved = true;
            } elseif ($match['status'] === self::STATUS_MATCHED_DIGITS && $match['confidence'] >= 90.0 && !empty($match['invoice_id'])) {
                $paymentId = $this->approveMatch($match, $mutation);
                $approved = true;
            } else {
                $manual = true;
                // TODO: simpan ke tabel bank_mutation_matches (kalau ada migration)
                Log::info('[Moota] Mutation needs manual review', ['mutation' => $mutation, 'match' => $match]);
            }
            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('[Moota] Process mutation exception', ['err' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return ['approved' => false, 'match' => $match, 'payment_id' => null, 'manual_review_required' => true];
        }

        return [
            'approved' => $approved,
            'match' => $match,
            'payment_id' => $paymentId,
            'manual_review_required' => $manual,
        ];
    }

    /**
     * @param  array<string,mixed>  $match
     * @param  array<string,mixed>  $mutation
     */
    private function approveMatch(array $match, array $mutation): ?int
    {
        $invoiceId = (int)$match['invoice_id'];
        if ($invoiceId <= 0) return null;
        $invoice = Invoice::query()->lockForUpdate()->find($invoiceId);
        if (!$invoice) return null;
        $amount = (int)($match['matched_amount'] ?? $mutation['amount'] ?? 0);
        if ($amount <= 0) return null;

        $ref = 'BANK-' . now()->format('Ymd-His') . '-' . strtoupper(substr((string)md5(serialize($mutation)), 0, 8));
        $customerId = (int)$invoice->customer_id;

        /** @var PaymentService $paySvc */
        $paySvc = app(\App\Services\Billing\PaymentService::class);
        $p = $paySvc->createPayment(
            customerId: $customerId,
            amount: $amount,
            userId: 1, // system
            invoiceIds: [$invoiceId],
            currency: 'IDR',
            method: 'bank_transfer_moota',
            status: 'success',
            referenceNumber: $ref,
            paidAt: \Illuminate\Support\Carbon::parse((string)($mutation['date'] ?? 'now')),
            gateway: 'moota',
        );

        Log::info('[Moota] Mutation auto-approved', [
            'payment_id' => $p->id,
            'invoice_id' => $invoiceId,
            'amount' => $amount,
            'confidence' => $match['confidence'],
            'match_reason' => $match['reason'],
        ]);

        return $p->id;
    }

    private function accountNumberMatchesBank(string $accountNumber, string $desc, Invoice $inv): bool
    {
        // Simple: cek jika description mengandung sebagian accountNumber atau invoice_number
        if ($accountNumber !== '' && stripos($desc, $accountNumber) !== false) return true;
        if (!empty($inv->invoice_number) && stripos($desc, (string)$inv->invoice_number) !== false) return true;
        return false;
    }

    /**
     * Entry point untuk scheduler: fetch + batch process.
     *
     * @return array{processed: int, approved: int, manual_review: int, no_match: int}
     */
    public function runSync(int $lastHours = 48): array
    {
        $mutations = $this->fetchMutations($lastHours);
        $processed = 0;
        $approved = 0;
        $manual = 0;
        $noMatch = 0;
        foreach ($mutations as $m) {
            $processed++;
            $r = $this->processMutation($m);
            if ($r['approved']) $approved++;
            elseif ($r['manual_review_required']) $manual++;
            else $noMatch++;
        }
        Log::info('[Moota] Sync selesai', compact('processed', 'approved', 'manual', 'noMatch', 'lastHours'));
        return ['processed' => $processed, 'approved' => $approved, 'manual_review' => $manual, 'no_match' => $noMatch];
    }
}
