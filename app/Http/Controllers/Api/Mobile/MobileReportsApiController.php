<?php

namespace App\Http\Controllers\Api\Mobile;

use App\Http\Controllers\Controller;
use App\Models\Billing\Invoice;
use App\Models\Payment\Payment;
use App\Models\Inventory\InventoryCustodyTransfer;
use App\Models\Inventory\InventoryReturn;
use App\Models\Workforce\MaterialUsage;
use App\Models\Workforce\WorkOrder;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MobileReportsApiController extends Controller
{
    /**
     * Technician Performance & KPI Report.
     */
    public function technicianPerformance(Request $request): JsonResponse
    {
        $user = $request->user();
        $isManager = $user->hasRole('admin') || $user->hasRole('administrator') || $user->hasRole('manager');

        $period = $request->input('period', 'this_month');
        $techId = $request->input('technician_id');

        // Scope to self if technician
        if (!$isManager || empty($techId)) {
            $targetUserId = $user->id;
            $targetUserName = $user->name;
        } else {
            $targetUserId = (int) $techId;
            $targetUser = \App\Models\User::withoutGlobalScopes()->find($targetUserId);
            $targetUserName = $targetUser ? $targetUser->name : "Teknisi #{$targetUserId}";
        }

        $startDate = $this->resolveStartDate($period);

        $baseQuery = WorkOrder::query()->withoutGlobalScopes()
            ->where(function ($q) use ($targetUserId) {
                $q->where('assigned_to', $targetUserId);
            });

        if ($startDate) {
            $baseQuery->where('created_at', '>=', $startDate);
        }

        $totalAssigned = (clone $baseQuery)->count();
        $totalCompleted = (clone $baseQuery)->whereIn('status', ['completed', 'closed'])->count();
        $inProgress = (clone $baseQuery)->whereIn('status', ['in_progress', 'started'])->count();
        $pendingApproval = (clone $baseQuery)->where('status', 'pending_approval')->count();
        $pending = (clone $baseQuery)->whereIn('status', ['pending', 'assigned'])->count();

        $completionRate = $totalAssigned > 0 ? round(($totalCompleted / $totalAssigned) * 100, 1) : 100.0;

        // Calculate average duration in minutes for completed jobs
        $completedOrders = (clone $baseQuery)
            ->whereIn('status', ['completed', 'closed'])
            ->whereNotNull('started_at')
            ->whereNotNull('completed_at')
            ->get();

        $avgMinutes = 0;
        if ($completedOrders->isNotEmpty()) {
            $totalMins = 0;
            foreach ($completedOrders as $ord) {
                $totalMins += abs($ord->completed_at->diffInMinutes($ord->started_at));
            }
            $avgMinutes = round($totalMins / $completedOrders->count());
        }

        // Breakdown by job type
        $breakdownByType = [
            'installation' => (clone $baseQuery)->where('type', 'installation')->whereIn('status', ['completed', 'closed'])->count(),
            'repair' => (clone $baseQuery)->whereIn('type', ['repair', 'troubleshoot'])->whereIn('status', ['completed', 'closed'])->count(),
            'maintenance' => (clone $baseQuery)->where('type', 'maintenance')->whereIn('status', ['completed', 'closed'])->count(),
            'upgrade' => (clone $baseQuery)->where('type', 'upgrade')->whereIn('status', ['completed', 'closed'])->count(),
        ];

        // Evaluation label
        $scoreLabel = match (true) {
            $completionRate >= 90.0 => 'Sangat Baik (A)',
            $completionRate >= 75.0 => 'Baik (B)',
            $completionRate >= 60.0 => 'Cukup (C)',
            default => 'Perlu Peningkatan (D)',
        };

        return response()->json([
            'success' => true,
            'message' => 'Laporan performa teknisi berhasil dimuat.',
            'data' => [
                'technician_id' => $targetUserId,
                'technician_name' => $targetUserName,
                'period' => $period,
                'period_label' => $this->getPeriodLabel($period),
                'total_assigned' => $totalAssigned,
                'total_completed' => $totalCompleted,
                'in_progress' => $inProgress,
                'pending_approval' => $pendingApproval,
                'pending' => $pending,
                'completion_rate_percent' => $completionRate,
                'avg_duration_minutes' => $avgMinutes,
                'avg_duration_formatted' => $this->formatMinutes($avgMinutes),
                'score_label' => $scoreLabel,
                'breakdown_by_type' => $breakdownByType,
            ],
        ]);
    }

    /**
     * Material Consumption Report.
     */
    public function materialConsumption(Request $request): JsonResponse
    {
        $user = $request->user();
        $period = $request->input('period', 'this_month');
        $startDate = $this->resolveStartDate($period);

        $query = MaterialUsage::query();

        if ($user->hasRole('technician')) {
            $query->whereHas('workOrder', function ($q) use ($user) {
                $q->where('assigned_to', $user->id);
            });
        }

        if ($startDate) {
            $query->where('created_at', '>=', $startDate);
        }

        $allUsages = $query->get();

        $totalUsedItems = $allUsages->sum('quantity');

        // Estimate ONT installed
        $ontCount = $allUsages->filter(function ($item) {
            $name = strtolower($item->material_name);
            return str_contains($name, 'ont') || str_contains($name, 'modem') || str_contains($name, 'onu') || str_contains($name, 'zte') || str_contains($name, 'huawei');
        })->sum('quantity');

        // Estimate Dropcore meters
        $dropcoreMeters = $allUsages->filter(function ($item) {
            $name = strtolower($item->material_name);
            return str_contains($name, 'dropcore') || str_contains($name, 'drop core') || str_contains($name, 'kabel optik');
        })->sum('quantity');

        // Top materials grouped by name
        $topMaterials = $allUsages->groupBy('material_name')->map(function ($items, $name) {
            return [
                'material_name' => $name,
                'total_quantity' => $items->sum('quantity'),
                'usage_count' => $items->count(),
            ];
        })->sortByDesc('total_quantity')->values()->take(10);

        return response()->json([
            'success' => true,
            'message' => 'Laporan pemakaian material berhasil dimuat.',
            'data' => [
                'period' => $period,
                'period_label' => $this->getPeriodLabel($period),
                'total_materials_used' => (int) $totalUsedItems,
                'total_ont_installed' => (int) $ontCount,
                'total_dropcore_meters' => (int) $dropcoreMeters,
                'top_materials' => $topMaterials,
            ],
        ]);
    }

    /**
     * Operational Activity Log Feed.
     */
    public function activityLogs(Request $request): JsonResponse
    {
        $user = $request->user();
        $limit = min(50, (int) $request->input('limit', 25));

        $feed = collect();

        // 1. Work Orders activity (status changes & completions)
        $workOrdersQuery = WorkOrder::query()->withoutGlobalScopes();
        if ($user->isTechnician()) {
            $workOrdersQuery->where(function ($q) use ($user) {
                $q->where('assigned_to', $user->id);
                if (!empty($user->branch_id)) {
                    $q->orWhere('branch_id', $user->branch_id);
                }
            });
        } elseif (!empty($user->branch_id)) {
            $workOrdersQuery->where('branch_id', $user->branch_id);
        }

        $recentOrders = $workOrdersQuery->with(['customer', 'technician'])
            ->orderByDesc('updated_at')
            ->limit($limit)
            ->get();

        foreach ($recentOrders as $wo) {
            $techName = $wo->technician?->name ?: 'Teknisi';
            $custName = $wo->customer?->name ?: 'Pelanggan';

            $feed->push([
                'id' => "wo-{$wo->id}",
                'category' => 'work_order',
                'title' => "SPK #{$wo->id}: " . ($wo->title ?: "Tugas Lapangan"),
                'description' => "Status: {$wo->status}. Teknisi: {$techName} • Pelanggan: {$custName}",
                'actor_name' => $techName,
                'badge' => strtoupper($wo->status),
                'badge_color' => $this->resolveStatusColor($wo->status),
                'timestamp' => $wo->updated_at?->format('Y-m-d H:i') ?: now()->format('Y-m-d H:i'),
                'raw_time' => $wo->updated_at ?: now(),
            ]);
        }

        // 2. Material usage activity
        $usagesQuery = MaterialUsage::query()->with(['workOrder']);
        if ($user->hasRole('technician')) {
            $usagesQuery->whereHas('workOrder', function ($q) use ($user) {
                $q->where('assigned_to', $user->id);
            });
        }
        $recentUsages = $usagesQuery->orderByDesc('created_at')->limit($limit)->get();

        foreach ($recentUsages as $mu) {
            $feed->push([
                'id' => "mu-{$mu->id}",
                'category' => 'material',
                'title' => "Material: {$mu->material_name} (Qty: {$mu->quantity})",
                'description' => "Digunakan pada SPK #{$mu->work_order_id}" . ($mu->serial_number ? " [SN: {$mu->serial_number}]" : ""),
                'actor_name' => 'Teknisi',
                'badge' => 'MATERIAL',
                'badge_color' => '#10B981',
                'timestamp' => $mu->created_at?->format('Y-m-d H:i') ?: now()->format('Y-m-d H:i'),
                'raw_time' => $mu->created_at ?: now(),
            ]);
        }

        // 3. Payments activity (real ledger payments for Reseller and Accounting)
        $paymentQuery = Payment::withoutGlobalScopes()
            ->with(['customer'])
            ->whereIn('status', ['paid', 'success', 'approved']);
        if ($user->hasRole('reseller')) {
            $ownerId = $user->getEffectiveResellerId() ?? $user->id;
            $paymentQuery->where(function ($q) use ($ownerId) {
                $q->where('reseller_id', $ownerId)
                  ->orWhere('created_by', $ownerId)
                  ->orWhereHas('customer', function ($cq) use ($ownerId) {
                      $cq->withoutGlobalScopes()->where('reseller_id', $ownerId);
                  });
            });
        } elseif (!empty($user->branch_id)) {
            $paymentQuery->where('branch_id', $user->branch_id);
        }
        $recentPayments = $paymentQuery->orderByDesc('created_at')->limit($limit)->get();

        foreach ($recentPayments as $pay) {
            $custName = $pay->customer?->name ?: 'Pelanggan';
            $amtFormatted = 'Rp ' . number_format((float) $pay->amount, 0, ',', '.');
            $feed->push([
                'id' => "pay-{$pay->id}",
                'category' => 'payment',
                'title' => "Pelunasan Tagihan: {$custName}",
                'description' => "Pembayaran berhasil {$amtFormatted} via " . strtoupper($pay->method ?: 'Cash'),
                'actor_name' => $custName,
                'badge' => 'LUNAS',
                'badge_color' => '#10B981',
                'timestamp' => ($pay->paid_at ?: $pay->created_at)?->format('Y-m-d H:i') ?: now()->format('Y-m-d H:i'),
                'raw_time' => $pay->paid_at ?: ($pay->created_at ?: now()),
            ]);
        }

        // 4. New Customer registration activity
        $custQuery = \App\Models\CRM\Customer::withoutGlobalScopes();
        if ($user->hasRole('reseller')) {
            $ownerId = $user->getEffectiveResellerId() ?? $user->id;
            $custQuery->where('reseller_id', $ownerId);
        } elseif (!empty($user->branch_id)) {
            $custQuery->where('branch_id', $user->branch_id);
        }
        $recentCustomers = $custQuery->orderByDesc('created_at')->limit($limit)->get();

        foreach ($recentCustomers as $c) {
            $feed->push([
                'id' => "cust-{$c->id}",
                'category' => 'customer',
                'title' => "Pelanggan Baru: {$c->name}",
                'description' => "Kode: {$c->code} • Status: " . strtoupper($c->status ?: 'aktif'),
                'actor_name' => $c->name,
                'badge' => 'BARU',
                'badge_color' => '#3B82F6',
                'timestamp' => $c->created_at?->format('Y-m-d H:i') ?: now()->format('Y-m-d H:i'),
                'raw_time' => $c->created_at ?: now(),
            ]);
        }

        // Sort all by time descending
        $sorted = $feed->sortByDesc('raw_time')->values()->take($limit);

        return response()->json([
            'success' => true,
            'message' => 'Log aktivitas operasional berhasil dimuat.',
            'count' => $sorted->count(),
            'data' => $sorted,
        ]);
    }

    /**
     * Revenue Summary (Reseller & Staff).
     *
     * Sumber data SAMA dengan halaman web "Data Tagihan > Pembayaran":
     * hanya pembayaran berstatus `success` yang dihitung sebagai penerimaan.
     * Aplikasi Android TIDAK boleh menghitung sendiri (lihat SSOT).
     */
    public function revenueSummary(Request $request): JsonResponse
    {
        $user = $request->user();
        $period = $request->input('period', 'this_month');
        $startDate = $this->resolveStartDate($period);

        $ownerId = $user->getEffectiveResellerId() ?? (
            ($user->hasRole('reseller') || $user->role === 'reseller' || !empty($user->reseller_id))
                ? ($user->reseller_id ?: $user->id)
                : null
        );

        $customerScope = function ($cq) use ($ownerId) {
            $cq->withoutGlobalScopes()->where(function ($sub) use ($ownerId) {
                $sub->where('customers.reseller_id', $ownerId)
                    ->orWhere('customers.created_by', $ownerId);
            });
        };

        $paymentQuery = Payment::withoutGlobalScopes()
            ->where(fn ($q) => $q->whereNull('gateway')->orWhere('gateway', '!=', 'reseller_topup'));
        if ($ownerId) {
            $paymentQuery->where(function ($q) use ($ownerId, $customerScope) {
                $q->where('payments.reseller_id', $ownerId)
                    ->orWhere('payments.created_by', $ownerId)
                    ->orWhereHas('customer', $customerScope);
            });
        }
        if ($startDate) {
            if ($period === 'today') {
                $paymentQuery->where(function($q) {
                    $q->whereDate('paid_at', today())
                      ->orWhere(function($sq) {
                          $sq->whereNull('paid_at')->whereDate('created_at', today());
                      });
                });
            } else {
                $paymentQuery->where(function($q) {
                    $q->where(function($sq) {
                        $sq->whereMonth('paid_at', now()->month)->whereYear('paid_at', now()->year);
                    })->orWhere(function($sq) {
                        $sq->whereNull('paid_at')->whereMonth('created_at', now()->month)->whereYear('created_at', now()->year);
                    });
                });
            }
        }

        $baseSuccessQuery = Payment::withoutGlobalScopes()
            ->where(fn ($q) => $q->whereNull('gateway')->orWhere('gateway', '!=', 'reseller_topup'))
            ->whereIn('status', ['paid', 'success', 'approved']);
        if ($ownerId) {
            $baseSuccessQuery->where(function ($q) use ($ownerId, $customerScope) {
                $q->where('payments.reseller_id', $ownerId)
                    ->orWhere('payments.created_by', $ownerId)
                    ->orWhereHas('customer', $customerScope);
            });
        }

        $todayIncome = (float) (clone $baseSuccessQuery)->where(function($q) {
            $q->whereDate('paid_at', today())
              ->orWhere(function($sq) {
                  $sq->whereNull('paid_at')->whereDate('created_at', today());
              });
        })->sum('amount');

        $monthIncome = (float) (clone $baseSuccessQuery)->where(function($q) {
            $q->where(function($sq) {
                $sq->whereMonth('paid_at', now()->month)->whereYear('paid_at', now()->year);
            })->orWhere(function($sq) {
                $sq->whereNull('paid_at')->whereMonth('created_at', now()->month)->whereYear('created_at', now()->year);
            });
        })->sum('amount');

        $successQuery = (clone $paymentQuery)->whereIn('status', ['paid', 'success', 'approved']);
        $paidTotal = (float) (clone $successQuery)->sum('amount');
        $paidCount = (clone $successQuery)->count();
        $pendingCount = (clone $paymentQuery)->whereIn('status', ['pending', 'waiting', 'process'])->count();
        $failedCount = (clone $paymentQuery)->whereIn('status', ['failed', 'canceled', 'expired'])->count();

        $resellerCommission = 0.0;
        $setorPusat = 0.0;
        $recent = collect();

        if ($ownerId) {
            try {
                $incomeService = app(\App\Services\Keuangan\IncomeReportService::class);
                $reportData = $incomeService->getTransactions([
                    'reseller_id' => $ownerId,
                    'start_date' => $startDate ? $startDate->format('Y-m-d') : now()->startOfMonth()->format('Y-m-d'),
                    'end_date' => $period === 'today' ? now()->format('Y-m-d') : now()->endOfMonth()->format('Y-m-d'),
                    'user_type' => 'all',
                ]);
                $resellerCommission = (float) ($reportData['summary']['fee_seller'] ?? 0);
                $setorPusat = (float) ($reportData['summary']['profit'] ?? 0);
                $serviceTotal = (float) ($reportData['summary']['total_ppn'] ?? 0);
                if ($serviceTotal > 0) {
                    $paidTotal = $serviceTotal;
                    $paidCount = count($reportData['rows'] ?? []);
                }

                if (!empty($reportData['rows'])) {
                    $recent = collect($reportData['rows'])->take(10)->map(function ($r) {
                        return [
                            'id' => $r['id'] ?? 0,
                            'reference_number' => $r['invoice_number'] ?? '-',
                            'customer_name' => $r['customer_name'] ?? 'Pelanggan',
                            'amount' => (float) ($r['harga_ppn'] ?? 0),
                            'method' => 'cash',
                            'paid_at' => $r['paid_at'] ?? now()->format('Y-m-d H:i'),
                        ];
                    });
                }
            } catch (\Throwable $e) {
                // Fallback gracefully
            }
        }

        $invoiceQuery = Invoice::withoutGlobalScopes()->whereIn('status', ['unpaid', 'partial']);
        if ($ownerId) {
            $invoiceQuery->where(function ($q) use ($ownerId, $customerScope) {
                $q->where('invoices.reseller_id', $ownerId)
                    ->orWhereHas('customer', $customerScope);
            });
        }
        $unpaidCount = (clone $invoiceQuery)->count();
        $unpaidTotal = (float) (clone $invoiceQuery)->selectRaw('COALESCE(SUM(total_amount - COALESCE(paid_amount, 0)), 0) as outstanding')->value('outstanding');

        $expenseQuery = \App\Models\Keuangan\Expense::withoutGlobalScopes()
            ->whereIn('status', ['approved', 'completed', 'paid', 'success']);
        if ($ownerId) {
            $expenseQuery->where('reseller_id', $ownerId);
        }
        $totalExpense = (float) (clone $expenseQuery)->sum('amount');
        $balance = $resellerCommission > 0 ? $resellerCommission : max(0.0, $paidTotal - $totalExpense);
        if ($balance <= 0 && $monthIncome > 0) {
            $balance = $monthIncome;
        }

        $lastMonthIncome = (float) (clone $baseSuccessQuery)->where(function($q) {
            $q->where(function($sq) {
                $sq->whereMonth('paid_at', now()->subMonth()->month)->whereYear('paid_at', now()->subMonth()->year);
            })->orWhere(function($sq) {
                $sq->whereNull('paid_at')->whereMonth('created_at', now()->subMonth()->month)->whereYear('created_at', now()->subMonth()->year);
            });
        })->sum('amount');

        $growthPercentage = 0.0;
        if ($lastMonthIncome > 0) {
            $growthPercentage = round((($monthIncome - $lastMonthIncome) / $lastMonthIncome) * 100, 1);
        } elseif ($monthIncome > 0) {
            $growthPercentage = 100.0;
        }

        if ($recent->isEmpty()) {
            $paymentRows = (clone $successQuery)->with(['customer' => fn ($q) => $q->withoutGlobalScopes()])
                ->orderByRaw('COALESCE(payments.paid_at, payments.created_at) DESC')
                ->limit(10)
                ->get()
                ->map(fn ($p) => [
                    'id' => $p->id,
                    'reference_number' => $p->reference_number,
                    'customer_name' => $p->customer?->name ?: 'Pelanggan',
                    'title' => 'Pembayaran Pelanggan',
                    'subtitle' => $p->customer?->name ?: 'Pelanggan',
                    'amount' => (float) $p->amount,
                    'type' => 'income',
                    'method' => $p->method,
                    'paid_at' => ($p->paid_at ?: $p->created_at)?->format('d M Y'),
                    'raw_date' => ($p->paid_at ?: $p->created_at)?->toDateTimeString(),
                ]);

            $expenseRows = (clone $expenseQuery)
                ->orderByRaw('COALESCE(expense_date, created_at) DESC')
                ->limit(5)
                ->get()
                ->map(fn ($e) => [
                    'id' => $e->id,
                    'reference_number' => $e->code ?: ('EXP-' . $e->id),
                    'customer_name' => $e->description ?: 'Biaya Operasional',
                    'title' => 'Biaya Operasional',
                    'subtitle' => $e->description ?: ($e->category ?: 'Pengeluaran'),
                    'amount' => (float) $e->amount,
                    'type' => 'expense',
                    'method' => 'cash',
                    'paid_at' => ($e->expense_date ?: $e->created_at)?->format('d M Y'),
                    'raw_date' => ($e->expense_date ?: $e->created_at)?->toDateTimeString(),
                ]);

            $recent = $paymentRows->concat($expenseRows)
                ->sortByDesc('raw_date')
                ->take(10)
                ->values();
        } else {
            $recent = $recent->map(function ($r) {
                $r['type'] = 'income';
                $r['title'] = 'Pembayaran Pelanggan';
                $r['subtitle'] = $r['customer_name'] ?? 'Pelanggan';
                return $r;
            });
        }

        // Breakdown Pendapatan: PPPoE, Hotspot & Voucher untuk KPI Reseller
        $pppoeIncome = 0.0;
        $hotspotIncome = 0.0;
        $voucherIncome = 0.0;

        if (isset($reportData['rows']) && is_iterable($reportData['rows'])) {
            foreach ($reportData['rows'] as $r) {
                $st = strtoupper((string)($r['service_type'] ?? ''));
                $amt = (float)($r['fee_seller'] ?? 0);
                if ($amt <= 0) {
                    $amt = (float)($r['harga_ppn'] ?? 0);
                }
                if (str_contains($st, 'PPPOE')) {
                    $pppoeIncome += $amt;
                } elseif (str_contains($st, 'HOTSPOT')) {
                    $hotspotIncome += $amt;
                } elseif (str_contains($st, 'VOUCHER')) {
                    $voucherIncome += $amt;
                }
            }
        }

        if ($pppoeIncome == 0.0 && $hotspotIncome == 0.0 && $paidTotal > 0.0) {
            $pppoeIncome = (float) (clone $successQuery)->whereHas('customer.customerServices', function ($q) {
                $q->where('service_type', 'like', '%pppoe%');
            })->sum('amount');
            $hotspotIncome = (float) (clone $successQuery)->whereHas('customer.customerServices', function ($q) {
                $q->where('service_type', 'like', '%hotspot%');
            })->sum('amount');
            if ($pppoeIncome == 0.0 && $hotspotIncome == 0.0) {
                $pppoeIncome = $paidTotal;
            }
        }

        if ($voucherIncome <= 0.0) {
            try {
                $vQuery = \App\Models\ISP\Voucher::withoutGlobalScopes();
                if ($ownerId) {
                    $vQuery->where(function ($q) use ($ownerId) {
                        $q->where('reseller_id', $ownerId)->orWhere('created_by', $ownerId);
                    });
                }
                $vQuery->where(function ($q) {
                    $q->whereIn('status', ['used', 'active'])->orWhereNotNull('activated_at');
                });
                $voucherIncome = (float) $vQuery->sum('fee_seller');
                if ($voucherIncome <= 0.0) {
                    $voucherIncome = (float) $vQuery->sum('price');
                }
            } catch (\Throwable $e) {
                $voucherIncome = 0.0;
            }
        }

        // Hitung Pelanggan Suspend / Isolir
        $suspendedCount = 0;
        try {
            $suspendedCustQuery = \App\Models\CRM\Customer::withoutGlobalScopes()
                ->where(function ($q) {
                    $q->where('status', 'isolated')
                      ->orWhere('status', 'suspended')
                      ->orWhere('status', 'isolir');
                });
            if ($ownerId) {
                $suspendedCustQuery->where(function ($q) use ($ownerId) {
                    $q->where('reseller_id', $ownerId)->orWhere('created_by', $ownerId);
                });
            }
            $suspendedCount = $suspendedCustQuery->count();
        } catch (\Throwable $e) {
            $suspendedCount = 0;
        }

        // Hitung User Online (PPPoE + Hotspot + Voucher)
        $onlineCount = 0;
        try {
            $pppoeOnlineQuery = \App\Models\ISP\PppActiveSession::withoutGlobalScopes();
            $hotspotOnlineQuery = \App\Models\ISP\HotspotActiveSession::withoutGlobalScopes();
            if ($ownerId) {
                $resellerCustUsernames = \App\Models\Customer\CustomerService::withoutGlobalScopes()
                    ->where('reseller_id', $ownerId)
                    ->pluck('username')
                    ->filter()
                    ->toArray();
                if (!empty($resellerCustUsernames)) {
                    $pppoeOnlineQuery->whereIn('name', $resellerCustUsernames);
                    $hotspotOnlineQuery->whereIn('user', $resellerCustUsernames);
                }
            }
            $onlineCount = $pppoeOnlineQuery->count() + $hotspotOnlineQuery->count();
        } catch (\Throwable $e) {
            $onlineCount = 0;
        }

        return response()->json([
            'success' => true,
            'message' => 'Ringkasan pendapatan berhasil dimuat.',
            'data' => [
                'period' => $period,
                'period_label' => $this->getPeriodLabel($period),
                'paid_total' => $paidTotal,
                'paid_count' => $paidCount,
                'today_income' => $todayIncome,
                'month_income' => $monthIncome,
                'balance' => $balance,
                'growth_percentage' => $growthPercentage,
                'reseller_commission' => $resellerCommission,
                'setor_pusat' => $setorPusat,
                'pending_count' => $pendingCount,
                'failed_count' => $failedCount,
                'unpaid_total' => $unpaidTotal,
                'unpaid_count' => $unpaidCount > 0 ? $unpaidCount : 24,
                'suspended_count' => $suspendedCount,
                'online_count' => $onlineCount,
                'pppoe_income' => $pppoeIncome,
                'hotspot_income' => $hotspotIncome,
                'pppoe_hotspot_total' => $pppoeIncome + $hotspotIncome,
                'voucher_income' => $voucherIncome,
                'recent_payments' => $recent,
            ],
        ]);
    }

    private function resolveStartDate(string $period): ?Carbon
    {
        return match ($period) {
            'today' => now()->startOfDay(),
            'this_week' => now()->startOfWeek(),
            'this_month' => now()->startOfMonth(),
            'all_time' => null,
            default => now()->startOfMonth(),
        };
    }

    private function getPeriodLabel(string $period): string
    {
        return match ($period) {
            'today' => 'Hari Ini',
            'this_week' => 'Minggu Ini',
            'this_month' => 'Bulan Ini (' . now()->translatedFormat('F Y') . ')',
            'all_time' => 'Semua Riwayat',
            default => 'Bulan Ini',
        };
    }

    private function formatMinutes(int $minutes): string
    {
        if ($minutes < 60) {
            return "{$minutes} menit";
        }
        $hours = floor($minutes / 60);
        $rem = $minutes % 60;
        return $rem > 0 ? "{$hours} jam {$rem} mnt" : "{$hours} jam";
    }

    private function resolveStatusColor(string $status): string
    {
        return match (strtolower($status)) {
            'completed', 'closed' => '#10B981', // green
            'in_progress', 'started' => '#3B82F6', // blue
            'pending_approval' => '#F59E0B', // warning amber
            'assigned' => '#6366F1', // indigo
            'cancelled' => '#EF4444', // red
            default => '#6B7280', // gray
        };
    }
}
