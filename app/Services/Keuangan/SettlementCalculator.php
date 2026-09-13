<?php

namespace App\Services\Keuangan;

class SettlementCalculator
{
    /**
     * Calculate settlement allocation for a single invoice item.
     *
     * @param float $unitPrice Selling price to customer
     * @param float $ownerPrice Cost price settled to Owner (HPP)
     * @param float $branchPrice Price settled to Branch
     * @param float $resellerPrice Price settled to Reseller
     * @param int $qty Quantity
     * @param float $paymentRatio Payment ratio (amount paid / invoice total)
     * @return array
     */
    public function calculateItem(
        float $unitPrice,
        float $ownerPrice,
        float $branchPrice,
        float $resellerPrice,
        int $qty = 1,
        float $paymentRatio = 1.0
    ): array {
        $multiplier = $qty * $paymentRatio;

        if ($ownerPrice > 0 || $branchPrice > 0 || $resellerPrice > 0) {
            $feeReseller = max(0.0, $unitPrice - $resellerPrice) * $multiplier;
            $feeBranch = max(0.0, $resellerPrice - $branchPrice) * $multiplier;
            $hppInternet = $ownerPrice * $multiplier;
        } else {
            $feeReseller = 0.0;
            $feeBranch = 0.0;
            $hppInternet = ($unitPrice * 0.35) * $multiplier;
        }

        return [
            'reseller_margin' => $feeReseller,
            'branch_margin' => $feeBranch,
            'owner_settlement' => $hppInternet,
            'total_allocation' => $feeReseller + $feeBranch + $hppInternet
        ];
    }
}
