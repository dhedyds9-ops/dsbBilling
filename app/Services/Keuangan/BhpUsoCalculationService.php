<?php

namespace App\Services\Keuangan;

use App\Models\Finance\BhpUsoConfig;
use App\Models\Payment\Payment;
use Carbon\Carbon;

class BhpUsoCalculationService
{
    protected SettlementCalculator $settlementCalculator;

    public function __construct(SettlementCalculator $settlementCalculator)
    {
        $this->settlementCalculator = $settlementCalculator;
    }

    /**
     * Get active configuration for a given date.
     */
    public function getConfigForDate(string|\DateTimeInterface $date): BhpUsoConfig
    {
        $config = BhpUsoConfig::getActiveForDate($date);

        if (!$config) {
            // Fallback to default
            $config = new BhpUsoConfig([
                'period_name' => 'Default (Retail)',
                'start_date' => '1970-01-01',
                'end_date' => '2099-12-31',
                'calculation_basis' => 'RETAIL_REVENUE',
                'bhp_rate' => 0.0050, // 0.5%
                'uso_rate' => 0.0125, // 1.25%
                'is_active' => true
            ]);
        }

        return $config;
    }

    /**
     * Calculate BHP/USO for a period or date range.
     */
    public function calculateForPeriod(string $startDate, string $endDate): array
    {
        $config = $this->getConfigForDate($startDate);
        
        $payments = Payment::where('status', 'success')
            ->whereBetween('paid_at', [
                Carbon::parse($startDate)->startOfDay(),
                Carbon::parse($endDate)->endOfDay()
            ])
            ->with('invoices.items')
            ->get();

        $totalRetail = 0.0;
        $totalResellerMargin = 0.0;

        foreach ($payments as $p) {
            $amt = (float) $p->amount;
            if ($p->relationLoaded('invoices') && $p->invoices->isNotEmpty()) {
                $invTotal = (float) $p->invoices->sum('total_amount');
                $paymentRatio = $invTotal > 0 ? ($amt / $invTotal) : 1;

                foreach ($p->invoices as $inv) {
                    foreach ($inv->items as $item) {
                        $qty = max(1, (int) $item->quantity);
                        $unitPrice = (float) $item->unit_price;
                        $ownerPrice = (float) $item->owner_settlement_price;
                        $branchPrice = (float) $item->branch_settlement_price;
                        $resellerPrice = (float) $item->reseller_settlement_price;

                        $totalRetail += $unitPrice * $qty * $paymentRatio;

                        $allocation = $this->settlementCalculator->calculateItem(
                            $unitPrice,
                            $ownerPrice,
                            $branchPrice,
                            $resellerPrice,
                            $qty,
                            $paymentRatio
                        );
                        $totalResellerMargin += $allocation['reseller_margin'];
                    }
                }
            } else {
                $totalRetail += $amt;
            }
        }

        $basis = $config->calculation_basis;
        $dasarPengenaan = ($basis === 'RESELLER_MARGIN') ? $totalResellerMargin : $totalRetail;

        $bhpRate = (float) $config->bhp_rate;
        $usoRate = (float) $config->uso_rate;

        $bhpNominal = $dasarPengenaan * $bhpRate;
        $usoNominal = $dasarPengenaan * $usoRate;

        // Construct aligned rows contract for the view
        $detailRows = [
            [
                'row_type' => 'detail',
                'type' => 'BHP',
                'code' => 'BHP-TEL',
                'kode' => 'BHP-TEL',
                'name' => 'BHP Telekomunikasi',
                'dasar_pengenaan' => $dasarPengenaan,
                'revenue_base' => $dasarPengenaan,
                'base_pct' => $bhpRate * 100,
                'rate_pct' => $bhpRate * 100,
                'base_min' => 0.0,
                'minimal' => 0.0,
                'nominal' => $bhpNominal
            ],
            [
                'row_type' => 'subtotal-bhp',
                'type' => 'SUBTOTAL-BHP',
                'code' => 'TOTAL-BHP',
                'kode' => 'TOTAL-BHP',
                'name' => 'TOTAL BIAYA HAK PENGGUNAAN (BHP)',
                'dasar_pengenaan' => $dasarPengenaan,
                'revenue_base' => $dasarPengenaan,
                'base_pct' => $bhpRate * 100,
                'rate_pct' => $bhpRate * 100,
                'base_min' => 0.0,
                'minimal' => 0.0,
                'nominal' => $bhpNominal
            ],
            [
                'row_type' => 'detail',
                'type' => 'USO',
                'code' => 'USO-KOM',
                'kode' => 'USO-KOM',
                'name' => 'Kewajiban Pelayanan Universal (USO)',
                'dasar_pengenaan' => $dasarPengenaan,
                'revenue_base' => $dasarPengenaan,
                'base_pct' => $usoRate * 100,
                'rate_pct' => $usoRate * 100,
                'base_min' => 0.0,
                'minimal' => 0.0,
                'nominal' => $usoNominal
            ],
            [
                'row_type' => 'subtotal-uso',
                'type' => 'SUBTOTAL-USO',
                'code' => 'TOTAL-USO',
                'kode' => 'TOTAL-USO',
                'name' => 'TOTAL BIAYA USO',
                'dasar_pengenaan' => $dasarPengenaan,
                'revenue_base' => $dasarPengenaan,
                'base_pct' => $usoRate * 100,
                'rate_pct' => $usoRate * 100,
                'base_min' => 0.0,
                'minimal' => 0.0,
                'nominal' => $usoNominal
            ],
            [
                'row_type' => 'grand',
                'type' => 'GRAND',
                'code' => 'GRAND',
                'kode' => 'GRAND',
                'name' => 'GRAND TOTAL BHP + USO',
                'dasar_pengenaan' => $dasarPengenaan,
                'revenue_base' => $dasarPengenaan,
                'base_pct' => ($bhpRate + $usoRate) * 100,
                'rate_pct' => ($bhpRate + $usoRate) * 100,
                'base_min' => 0.0,
                'minimal' => 0.0,
                'nominal' => $bhpNominal + $usoNominal
            ]
        ];

        return [
            'config' => $config,
            'total_retail' => $totalRetail,
            'total_reseller_margin' => $totalResellerMargin,
            'dasar_pengenaan' => $dasarPengenaan,
            'bhp_total' => $bhpNominal,
            'uso_total' => $usoNominal,
            'grand_total' => $bhpNominal + $usoNominal,
            'detail_rows' => $detailRows
        ];
    }
}
