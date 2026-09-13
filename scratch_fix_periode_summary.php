<?php
$file = 'app/Services/Billing/PeriodeTagihanService.php';
$content = file_get_contents($file);

$oldCode = <<<PHP
                DB::raw("COALESCE(SUM(CASE WHEN status IN ('unpaid','partial','pending') THEN (total_amount - paid_amount) ELSE 0 END), 0) as belum_bayar"),
                DB::raw("COALESCE(SUM(CASE WHEN status = 'paid' THEN total_amount ELSE 0 END), 0) as lunas"),
                DB::raw("COALESCE(SUM(CASE WHEN status = 'overdue' THEN (total_amount - paid_amount) ELSE 0 END), 0) as overdue"),
            ])
            ->from(DB::raw("({\$base->toSql()}) as inv_base"))
            ->mergeBindings(\$base->getQuery())
            ->first();

        return [
            'total_tagihan' => (float)(\$agg->total_tagihan ?? 0),
            'belum_bayar' => (float)(\$agg->belum_bayar ?? 0),
            'lunas' => (float)(\$agg->lunas ?? 0),
            'overdue' => (float)(\$agg->overdue ?? 0),
        ];
PHP;

$newCode = <<<PHP
                DB::raw("COALESCE(SUM(CASE WHEN status IN ('unpaid','partial','pending') THEN (total_amount - paid_amount) ELSE 0 END), 0) as belum_bayar"),
                DB::raw("COALESCE(SUM(paid_amount), 0) as sudah_bayar"),
                DB::raw("COALESCE(SUM(CASE WHEN status = 'overdue' THEN (total_amount - paid_amount) ELSE 0 END), 0) as overdue"),
            ])
            ->from(DB::raw("({\$base->toSql()}) as inv_base"))
            ->mergeBindings(\$base->getQuery())
            ->first();

        return [
            'total_tagihan' => (float)(\$agg->total_tagihan ?? 0),
            'belum_bayar' => (float)(\$agg->belum_bayar ?? 0),
            'sudah_bayar' => (float)(\$agg->sudah_bayar ?? 0),
            'lunas' => (float)(\$agg->sudah_bayar ?? 0),
            'overdue' => (float)(\$agg->overdue ?? 0),
        ];
PHP;

$content = str_replace($oldCode, $newCode, $content);
file_put_contents($file, $content);
echo "Fixed summary in PeriodeTagihanService\n";
