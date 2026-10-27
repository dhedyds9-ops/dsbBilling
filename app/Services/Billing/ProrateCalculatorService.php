<?php

namespace App\Services\Billing;

use Carbon\Carbon;

class ProrateCalculatorService
{
    /**
     * Hitung tagihan proporsional harian (Prorate) dari tanggal aktivasi s/d akhir siklus tagihan.
     *
     * @param float $monthlyPrice Harga bulanan paket
     * @param Carbon|string $activationDate Tanggal aktivasi / mulai langganan
     * @param Carbon|string|null $cycleEndDate Tanggal akhir siklus (default: akhir bulan aktivasi)
     * @param bool $roundToHundred Pembulatan ke ratusan terdekat (misal: 112.903 -> 113.000)
     * @return array
     */
    public function calculateDaily(
        float $monthlyPrice,
        Carbon|string $activationDate,
        Carbon|string|null $cycleEndDate = null,
        bool $roundToHundred = true
    ): array {
        $start = $activationDate instanceof Carbon ? $activationDate->copy() : Carbon::parse($activationDate);
        $daysInMonth = $start->daysInMonth;

        if ($cycleEndDate) {
            $end = $cycleEndDate instanceof Carbon ? $cycleEndDate->copy() : Carbon::parse($cycleEndDate);
        } else {
            $end = $start->copy()->endOfMonth();
        }

        // Jumlah hari aktif (inklusif: hari aktivasi dihitung 1 hari penuh)
        $activeDays = max(1, $start->diffInDays($end) + 1);
        $dailyRate = $monthlyPrice / $daysInMonth;
        $rawAmount = $dailyRate * $activeDays;

        $proratedAmount = $roundToHundred ? ceil($rawAmount / 100) * 100 : round($rawAmount, 2);

        return [
            'monthly_price'        => $monthlyPrice,
            'days_in_month'        => $daysInMonth,
            'active_days'          => $activeDays,
            'daily_rate'           => round($dailyRate, 2),
            'raw_amount'           => round($rawAmount, 2),
            'prorated_amount'      => (float) $proratedAmount,
            'period_start'         => $start->toDateString(),
            'period_end'           => $end->toDateString(),
            'period_label'         => sprintf('%s s/d %s (%d hari)', $start->format('d M Y'), $end->format('d M Y'), $activeDays),
            'description'          => sprintf('Tagihan Prorate (%d/%d hari): %s s/d %s', $activeDays, $daysInMonth, $start->format('d/m/Y'), $end->format('d/m/Y')),
        ];
    }

    /**
     * Hitung tagihan pertama gabungan (Prorate sisa bulan ini + 1 bulan penuh berikutnya).
     * Sangat berguna jika pelanggan pasang di akhir bulan (misal tgl 20 ke atas) agar tidak
     * menerbitkan dua invoice berturut-turut dalam selang beberapa hari.
     *
     * @param float $monthlyPrice
     * @param Carbon|string $activationDate
     * @param int $lateMonthThreshold Tanggal ambang batas akhir bulan (default: tgl 20)
     * @return array
     */
    public function calculateCombinedFirstBill(
        float $monthlyPrice,
        Carbon|string $activationDate,
        int $lateMonthThreshold = 20
    ): array {
        $start = $activationDate instanceof Carbon ? $activationDate->copy() : Carbon::parse($activationDate);
        $prorate = $this->calculateDaily($monthlyPrice, $start);

        $isLateMonth = $start->day >= $lateMonthThreshold;

        if (!$isLateMonth) {
            return [
                'is_combined'          => false,
                'prorate'              => $prorate,
                'next_month_full'      => 0,
                'total_first_bill'     => $prorate['prorated_amount'],
                'next_billing_date'    => $start->copy()->addMonth()->startOfMonth()->toDateString(),
                'description'          => $prorate['description'],
            ];
        }

        $totalCombined = $prorate['prorated_amount'] + $monthlyPrice;
        $nextBillingDate = $start->copy()->addMonths(2)->startOfMonth()->toDateString();

        return [
            'is_combined'          => true,
            'prorate'              => $prorate,
            'next_month_full'      => $monthlyPrice,
            'total_first_bill'     => (float) $totalCombined,
            'next_billing_date'    => $nextBillingDate,
            'description'          => sprintf(
                'Tagihan Pertama Gabungan: Prorate (%s) + 1 Bulan Penuh (%s)',
                $prorate['period_label'],
                $start->copy()->addMonth()->format('F Y')
            ),
        ];
    }
}
