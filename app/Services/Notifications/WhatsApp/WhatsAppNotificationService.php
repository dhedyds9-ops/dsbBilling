<?php

declare(strict_types=1);

namespace App\Services\Notifications\WhatsApp;

use App\Jobs\Notifications\SendWaMessageJob;
use App\Models\Billing\Invoice;
use App\Models\CRM\Customer;
use App\Models\ISP\PPPoEUser;
use App\Services\Adapters\Payment\PaymentOrchestrationService;
use App\Services\CustomerPortal\CustomerDashboardService;
use App\Services\ISP\ISPProvisioningService;
use App\Services\Notifications\WhatsApp\Contracts\WaGatewayDriverInterface;
use App\Services\Notifications\WhatsApp\ValueObjects\WaIncomingMessage;
use App\Services\Notifications\WhatsApp\ValueObjects\WaOutgoingMessage;
use App\Services\Support\TicketService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Src\Domain\Billing\Events\InvoiceCreatedEvent;

/**
 * SSOT: WhatsApp Notification Service — Orchestration SSOT.
 *
 * Tanggung jawab 2:
 * 1. OUTGOING: Dispatch notification billing (invoice created, reminders, payment sukses, suspended, reactivated, broadcast)
 *    → Lewat Job SendWaMessageJob (Async Queue + Rate Limit + Cooldown)
 *
 * 2. INCOMING BOT: Command Router (tagihan / status / bayar / gangguan)
 *    → Resolve customer dari phone number → execute command → kirim reply (sendMessage synchronous ke user yang chat saja)
 *
 * Antispam Rate Limit berlaku di layer Service + Job.
 */
final class WhatsAppNotificationService
{
    public function __construct(
        private readonly WaGatewayRegistry       $registry,
        private readonly WaTemplateEngine        $template,
        private readonly WaAntiSpamService       $antispam,
        private readonly ?CustomerDashboardService $customerDash = null,
        private readonly ?ISPProvisioningService $isp = null,
        private readonly ?TicketService          $ticket = null,
        private readonly ?PaymentOrchestrationService $paymentOrch = null,
    ) {}

    // ================================================================
    //  SECTION 1: OUTGOING NOTIFICATION DISPATCH (ASYNC)
    // ================================================================

    /**
     * @param array<string,mixed> $extra
     */
    public function notifyInvoiceCreated(Invoice $invoice, array $extra = []): void
    {
        $customer = $this->customerOf($invoice);
        if (!$customer) return;
        $phone = $this->phoneOf($customer);
        if (!$phone) return;
        $bankText = $this->bankAccountsText();
        $link = $this->paymentLinkOf($invoice, $customer);
        $package = $this->packageNameOf($invoice);
        $txt = $this->template->invoiceCreated([
            'customer_name' => $customer->name ?? $customer->full_name ?? 'Pelanggan',
            'invoice_number' => $invoice->invoice_number ?? ('INV-' . $invoice->id),
            'period' => ($invoice->period_month ? ($invoice->period_month . ' ' . ($invoice->period_year ?? '')) : now()->format('F Y')),
            'total_amount' => (float)$invoice->total_amount,
            'due_date' => is_object($invoice->due_date) ? $invoice->due_date->format('d M Y') : (string)$invoice->due_date,
            'package_name' => $package,
            'bank_accounts_text' => $bankText,
            'payment_link' => $link,
            ...$extra,
        ]);
        $this->dispatchSend($phone, $txt, category: 'billing', priorityHigh: false);
    }

    /**
     * @param array<string,mixed> $extra
     */
    public function notifyCustomerCreated(Customer $customer, array $credentials, array $extra = []): void
    {
        $phone = $this->phoneOf($customer);
        if (!$phone) return;
        
        $txt = $this->template->customerCreated([
            'customer_name' => $customer->name ?? $customer->full_name ?? 'Pelanggan',
            'username' => $credentials['username'] ?? '-',
            'password' => $credentials['password'] ?? '-',
            'portal_password' => $credentials['portal_password'] ?? '-',
            ...$extra,
        ]);
        
        $this->dispatchSend($phone, $txt, category: 'info', priorityHigh: true);
    }

    /**
     * @param array<string,mixed> $extra
     */
    public function notifyInvoiceReminder(Invoice $invoice, string $stage = 'h-1', array $extra = []): void
    {
        $customer = $this->customerOf($invoice);
        if (!$customer) return;
        $phone = $this->phoneOf($customer);
        if (!$phone) return;
        $dueDate = is_object($invoice->due_date) ? $invoice->due_date : now()->parse((string)$invoice->due_date);
        $days = now()->startOfDay()->diffInDays($dueDate, false);
        $txt = $this->template->invoiceReminder([
            'customer_name' => $customer->name ?? $customer->full_name ?? 'Pelanggan',
            'invoice_number' => $invoice->invoice_number ?? ('INV-' . $invoice->id),
            'total_amount' => (float)$invoice->total_amount,
            'due_date' => is_object($invoice->due_date) ? $invoice->due_date->format('d M Y') : (string)$invoice->due_date,
            'days_remaining' => $days,
            ...$extra,
        ], $stage);
        $this->dispatchSend($phone, $txt, category: 'reminder', priorityHigh: $stage === 'h+1');
    }

    /**
     * @param array<string,mixed> $extra
     */
    public function notifyPaymentSuccess(Invoice $invoice, float $paidAmount, string $receiptNo, array $extra = []): void
    {
        $customer = $this->customerOf($invoice);
        if (!$customer) return;
        $phone = $this->phoneOf($customer);
        if (!$phone) return;
        $txt = $this->template->paymentSuccess([
            'customer_name' => $customer->name ?? $customer->full_name ?? 'Pelanggan',
            'invoice_number' => $invoice->invoice_number ?? ('INV-' . $invoice->id),
            'paid_amount' => $paidAmount,
            'paid_at' => now()->format('d M Y H:i'),
            'payment_receipt' => $receiptNo,
            ...$extra,
        ]);
        $this->dispatchSend($phone, $txt, category: 'info', priorityHigh: true);
    }

    /**
     * @param array<string,mixed> $extra
     */
    public function notifyServiceSuspended(Invoice $invoice, array $extra = []): void
    {
        $customer = $this->customerOf($invoice);
        if (!$customer) return;
        $phone = $this->phoneOf($customer);
        if (!$phone) return;
        $outstanding = max(0, (float)$invoice->total_amount - (float)$invoice->paid_amount);
        $txt = $this->template->serviceSuspended([
            'customer_name' => $customer->name ?? $customer->full_name ?? 'Pelanggan',
            'invoice_number' => $invoice->invoice_number ?? ('INV-' . $invoice->id),
            'outstanding_amount' => $outstanding,
            ...$extra,
        ]);
        $this->dispatchSend($phone, $txt, category: 'info', priorityHigh: true);
    }

    /**
     * @param array<string,mixed> $extra
     */
    public function notifyServiceReactivated(Customer $customer, array $extra = []): void
    {
        $phone = $this->phoneOf($customer);
        if (!$phone) return;
        $package = '';
        $bw = '';
        try {
            if ($this->customerDash) {
                $s = $this->customerDash->getActiveServices($customer->id);
                if (is_array($s) && count($s) > 0) {
                    $package = (string)($s[0]['package_name'] ?? '');
                    $bw = trim((string)($s[0]['bandwidth_text'] ?? ''));
                }
            }
        } catch (\Throwable) {
        }
        $txt = $this->template->serviceReactivated([
            'customer_name' => $customer->name ?? $customer->full_name ?? 'Pelanggan',
            'package_name' => $package,
            'bandwidth_text' => $bw,
            ...$extra,
        ]);
        $this->dispatchSend($phone, $txt, category: 'info', priorityHigh: true);
    }

    /**
     * Broadcast info gangguan / maintenance ke list customer (dibagi chunk 500 / batch).
     *
     * @param iterable<Customer> $customers
     * @param array<string,mixed> $extra
     */
    public function broadcastNetworkAnnouncement(iterable $customers, string $type = 'maintenance', array $extra = []): void
    {
        $counter = 0;
        foreach ($customers as $c) {
            $phone = $this->phoneOf($c);
            if (!$phone) continue;
            $name = $c->name ?? $c->full_name ?? 'Pelanggan';
            $txt = $this->template->networkAnnouncement(['customer_name' => $name, ...$extra], $type);
            $this->dispatchSend($phone, $txt, category: 'marketing', priorityHigh: false);
            $counter++;
        }
        Log::info('[WA-BROADCAST] Enqueued messages', ['count' => $counter, 'type' => $type]);
    }

    // ================================================================
    //  SECTION 2: INCOMING BOT COMMAND ROUTER
    // ================================================================

    /**
     * Handle single incoming message (untuk semua driver).
     * Return: WaOutgoingMessage (balasan) atau null kalau no-reply.
     */
    public function handleIncoming(WaIncomingMessage $msg): ?WaOutgoingMessage
    {
        // 1. Rate limit per nomor: 20 pesan / 3 menit (anti spam command flood)
        $key = 'wa:bot:flood:' . $msg->fromPhone;
        if (!RateLimiter::attempt($key, 20, fn() => true, 180)) {
            return new WaOutgoingMessage(
                toPhone: $msg->fromPhone,
                type: 'text',
                text: "⚠️ Anda mengirim pesan terlalu cepat. Silakan coba lagi dalam 3 menit.\n\n— dsBilling Bot",
                category: 'info',
                priorityHigh: true,
            );
        }

        // 2. Resolve Customer by phone number
        $customer = $this->findCustomerByPhone($msg->fromPhone);
        if (!$customer) {
            $name = $msg->senderName;
            return new WaOutgoingMessage(
                toPhone: $msg->fromPhone,
                type: 'text',
                text: $this->template->botGreeting($name) . "\n\nℹ️ Nomor WA Anda *belum terdaftar* di database pelanggan kami.\n\nUntuk menggunakan perintah bot, silakan hubungkan nomor WA Anda ke akun pelanggan di portal atau hubungi CS.",
                category: 'info',
            );
        }

        $cmd = $this->normalizeCommand($msg->commandWord());
        $args = $msg->commandArgs();

        $replyText = match ($cmd) {
            'tagihan', 'bill', 'invoice', 'list' => $this->cmdTagihan($customer),
            'status', 'cekinet', 'ping'   => $this->cmdStatus($customer),
            'bayar', 'pay', 'payment'       => $this->cmdBayar($customer),
            'gangguan', 'tiket', 'trouble', 'lapor' => $this->cmdGangguan($customer, $msg, $args),
            'bantuan', 'help', 'menu', '?'  => $this->template->botHelp(),
            'halo', 'hallo', 'hi', 'test', 'p' => $this->template->botGreeting($customer->name ?? ($customer->full_name ?? null)),
            default => $this->template->botHelp() . "\n\n💡 Perintah *\"{$msg->commandWord()}\"* tidak dikenali.\nPilih menu di atas.",
        };

        return new WaOutgoingMessage(
            toPhone: $msg->fromPhone,
            type: 'text',
            text: $replyText,
            category: 'support',
            priorityHigh: true,
        );
    }

    public function sendImmediate(WaOutgoingMessage $msg): bool
    {
        if (!$msg->isPhoneValid()) return false;
        // Anti cooldown diabaikan jika priorityHigh=true (support / otp).
        if (!$msg->priorityHigh) {
            if ($this->antispam->isCoolingDown($msg->toPhone, $msg->category)) return false;
            if (!$this->antispam->acquireGlobalQuota(1)) return false;
        }
        $driver = $this->registry->primary();
        if (!$driver) return false;
        $res = $driver->sendMessage($msg);
        if ($res->success && !$msg->priorityHigh) {
            $this->antispam->markCoolingDown($msg->toPhone, $msg->category);
        }
        return $res->success;
    }

    // ================================================================
    //  SECTION 3: COMMAND IMPLEMENTATIONS
    // ================================================================

    private function cmdTagihan(object $customer): string
    {
        $customerId = (int)($customer->id ?? 0);
        $invoices = Invoice::query()
            ->where('customer_id', $customerId)
            ->whereRaw('(total_amount - paid_amount) > ?', [0])
            ->whereIn('status', ['unpaid', 'partial', 'sent'])
            ->orderBy('due_date', 'asc')
            ->limit(5)
            ->get(['id', 'invoice_number', 'period_month', 'period_year', 'total_amount', 'paid_amount', 'due_date'])
            ->map(fn($i) => [
                'invoice_number' => (string)($i->invoice_number ?? ('INV-' . $i->id)),
                'due_date' => is_object($i->due_date) ? $i->due_date->format('d M Y') : (string)$i->due_date,
                'total_amount' => (float)$i->total_amount,
                'paid_amount' => (float)$i->paid_amount,
            ])->toArray();
        return $this->template->botResponseTagihan([
            'customer_name' => $customer->name ?? ($customer->full_name ?? 'Pelanggan'),
            'invoices' => $invoices,
        ]);
    }

    private function cmdStatus(object $customer): string
    {
        $customerId = (int)($customer->id ?? 0);
        $status = 'unknown';
        $package = '-';
        $bw = '-';
        $ip = '-';
        $lastSeen = '-';
        $pppoeUser = '-';
        try {
            if ($this->isp) {
                $pu = PPPoEUser::query()->whereHas('customerService', function ($q) use ($customerId) {
                    $q->where('customer_id', $customerId);
                })->orderByDesc('updated_at')->first();
                if ($pu) {
                    $pppoeUser = (string)($pu->username ?? '-');
                    $cs = $pu->customerService;
                    $package = (string)($cs?->serviceProfile?->name ?? ($cs?->name ?? '-'));
                    $dl = (int)($cs?->serviceProfile?->rate_download_kbps ?? 0);
                    $ul = (int)($cs?->serviceProfile?->rate_upload_kbps ?? 0);
                    if ($dl > 0 || $ul > 0) {
                        $bw = "{$dl}/{$ul} kbps";
                    }
                    $status = match (strtolower((string)$pu->status)) {
                        'active' => 'online',
                        'suspended' => 'suspended',
                        default => 'unknown',
                    };
                }
            }
            $sess = \App\Models\ISP\OnlineSession::query()
                ->whereHas('pppoeUser.customerService', fn($q) => $q->where('customer_id', $customerId))
                ->orderByDesc('last_seen_at')->first();
            if ($sess) {
                $ip = (string)($sess->framed_ip_address ?? '-');
                $lastSeen = is_object($sess->last_seen_at) ? $sess->last_seen_at->diffForHumans() : (string)$sess->last_seen_at;
                if (strtolower((string)($sess->radius_state ?? 'online')) === 'online') $status = 'online';
            }
        } catch (\Throwable) {
        }
        return $this->template->botResponseStatus([
            'customer_name' => $customer->name ?? ($customer->full_name ?? 'Pelanggan'),
            'service_status' => $status,
            'package_name' => $package,
            'bandwidth' => $bw,
            'ip_address' => $ip,
            'last_online_at' => $lastSeen,
            'pppoe_username' => $pppoeUser,
        ]);
    }

    private function cmdBayar(object $customer): string
    {
        $customerId = (int)($customer->id ?? 0);
        $total = Invoice::query()
            ->where('customer_id', $customerId)
            ->whereRaw('(total_amount - paid_amount) > 0')
            ->sum(DB::raw('total_amount - paid_amount'));
        $link = $this->customerPortalLinkOf($customer);
        $bankText = $this->bankAccountsText();
        return $this->template->botResponseBayar([
            'customer_name' => $customer->name ?? ($customer->full_name ?? 'Pelanggan'),
            'total_outstanding' => (float)$total,
            'payment_link' => $link,
            'bank_accounts_text' => $bankText,
        ]);
    }

    private function cmdGangguan(object $customer, WaIncomingMessage $msg, string $args): string
    {
        $customerId = (int)($customer->id ?? 0);
        $title = 'Laporan Gangguan via WhatsApp';
        $description = "Nomor WA: {$msg->fromPhone}\nPesan user: " . ($args ?: $msg->rawText);
        if ($args === '') $title = 'Gangguan Umum - Perlu konfirmasi';
        $category = 'network';
        $priority = 'high';

        $ticketNo = null;
        try {
            if ($this->ticket) {
                $data = [
                    'title' => $title,
                    'description' => $description,
                    'category' => $category,
                    'priority' => $priority,
                    'status' => 'open',
                    'customer_id' => $customerId,
                ];
                if (method_exists($this->ticket, 'createSimple')) {
                    $t = $this->ticket->createSimple($customerId, $title, $description, $category, $priority);
                } else {
                    $t = \App\Models\Support\Ticket::create([
                        'uuid' => (string)\Illuminate\Support\Str::uuid(),
                        'customer_id' => $customerId,
                        'title' => $title,
                        'description' => $description,
                        'category' => $category,
                        'priority' => $priority,
                        'status' => 'open',
                    ]);
                }
                $ticketNo = (string)($t->ticket_number ?? ('TKT-' . str_pad((string)($t->id ?? ''), 6, '0', STR_PAD_LEFT)));
            }
        } catch (\Throwable $e) {
            Log::warning('[WA-BOT] create gangguan ticket gagal', ['err' => $e->getMessage(), 'customer_id' => $customerId]);
        }
        return $this->template->botResponseGangguan([
            'customer_name' => $customer->name ?? ($customer->full_name ?? 'Pelanggan'),
            'ticket_number' => $ticketNo ?? 'AUTO-PENDING',
            'ticket_title' => $title,
            'category' => 'Gangguan Layanan',
            'eta_response_text' => '2 jam (kerja)',
        ]);
    }

    // ================================================================
    //  HELPERS
    // ================================================================

    private function dispatchSend(string $phone, string $text, string $category = 'general', bool $priorityHigh = false): void
    {
        $msg = new WaOutgoingMessage(
            toPhone: $phone,
            type: 'text',
            text: $text,
            category: $category,
            priorityHigh: $priorityHigh,
        );
        if (!$msg->isPhoneValid()) return;
        
        $history = \App\Models\Integration\WaMessageHistory::create([
            'recipient_number' => $phone,
            'message' => $text,
            'category' => $category,
            'status' => 'pending'
        ]);

        SendWaMessageJob::dispatch($msg, $history->id)->onQueue('notifications-wa');
    }

    /**
     * @return Customer|null
     */
    private function customerOf(Invoice $invoice): ?object
    {
        try {
            $c = Customer::query()->find((int)$invoice->customer_id);
            return $c;
        } catch (\Throwable) {
            // Kalau Customer class berbeda, fallback ke CRM Customer
            return \App\Models\CRM\Customer::query()->find((int)$invoice->customer_id) ?: null;
        }
    }

    private function phoneOf(object $customer): ?string
    {
        $p = (string)($customer->whatsapp ?? $customer->phone ?? $customer->mobile ?? $customer->msisdn ?? '');
        $norm = WaOutgoingMessage::normalizePhone($p);
        return (bool)preg_match('/^628[0-9]{8,14}$/', $norm) ? $norm : null;
    }

    private function findCustomerByPhone(string $phoneNorm): ?object
    {
        // phoneNorm = 628xxxxx, cari kolom yang umum
        $candidates = [
            [Customer::class, ['whatsapp', 'phone', 'mobile', 'msisdn']],
            [\App\Models\CRM\Customer::class, ['whatsapp', 'phone', 'mobile', 'msisdn']],
        ];
        $withZero = '0' . substr($phoneNorm, 2);
        $withPlus = '+' . $phoneNorm;
        foreach ($candidates as [$class, $cols]) {
            if (!class_exists($class)) continue;
            $q = $class::query();
            $q->where(function ($sq) use ($cols, $phoneNorm, $withZero, $withPlus) {
                foreach ($cols as $col) {
                    $sq->orWhere($col, $phoneNorm)
                        ->orWhere($col, $withZero)
                        ->orWhere($col, $withPlus);
                }
            });
            $found = $q->first();
            if ($found) return $found;
        }
        return null;
    }

    private function normalizeCommand(string $w): string
    {
        $w = preg_replace('/[^a-z0-9?]/', '', mb_strtolower(trim($w)));
        return $w;
    }

    private function bankAccountsText(): string
    {
        try {
            $svc = app(\App\Services\Pengaturan\PaymentGatewaySettingsService::class);
            $mt = $svc->get('manual_transfer');
            $accs = $mt['bank_accounts'] ?? [];
            if (count($accs) === 0) return "  • Hubungi CS untuk rekening";
            $lines = [];
            foreach (array_filter($accs, fn($a) => (bool)($a['active'] ?? true)) as $a) {
                $lines[] = "  • " . ($a['bank_name'] ?? '') . " *" . ($a['account_number'] ?? '') . "* a.n " . ($a['account_holder'] ?? '');
            }
            return implode("\n", $lines);
        } catch (\Throwable) {
            return "  • Hubungi CS untuk rekening";
        }
    }

    private function paymentLinkOf(Invoice $invoice, object $customer): string
    {
        $base = config('app.url', url('/'));
        $tok = substr(md5(config('app.key') . 'wa-pay' . $invoice->id . $customer->id), 0, 12);
        return rtrim($base, '/') . '/bayar/' . $invoice->id . '?t=' . $tok;
    }

    private function customerPortalLinkOf(object $customer): string
    {
        $base = config('app.url', url('/'));
        $tok = substr(md5(config('app.key') . 'wa-cust' . ($customer->id ?? 0)), 0, 10);
        return rtrim($base, '/') . '/portal/tagihan?t=' . $tok . '&c=' . ($customer->id ?? 0);
    }

    private function packageNameOf(Invoice $invoice): string
    {
        try {
            $subs = \App\Models\Billing\Subscription::query()
                ->where('customer_id', (int)$invoice->customer_id)
                ->first();
            if (!$subs || empty($subs->customer_service_id)) return '';
            $cs = \App\Models\Customer\CustomerService::query()->find((int)$subs->customer_service_id);
            return (string)($cs?->serviceProfile?->name ?? ($cs?->name ?? ''));
        } catch (\Throwable) {
            return '';
        }
    }
}
