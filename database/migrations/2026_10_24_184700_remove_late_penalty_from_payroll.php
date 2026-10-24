<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Set setting payroll.late_penalty ke 0
        try {
            DB::table('settings')->updateOrInsert(
                ['key' => 'payroll.late_penalty'],
                ['value' => '0', 'updated_at' => now()]
            );
        } catch (\Throwable $e) {
            // Ignore if settings table structure differs
        }

        // 2. Bersihkan potongan terlambat pada payroll berstatus 'draft'
        try {
            $draftPayrolls = DB::table('payrolls')->where('status', 'draft')->get();
            foreach ($draftPayrolls as $p) {
                // Jika potongan sama dengan late_days * 25000 atau ada catatan terlambat dan potongan 75000
                $deduction = (float)$p->deductions;
                $lateDays = (int)($p->late_days ?? 0);
                $absentDays = (int)($p->absent_days ?? 0);

                if ($deduction > 0) {
                    $lateCut = 0;
                    if ($lateDays > 0 && ($lateDays * 25000 == $deduction)) {
                        $lateCut = $lateDays * 25000;
                    } elseif (!empty($p->notes) && str_contains($p->notes, 'Terlambat') && !str_contains($p->notes, 'Alpa') && $deduction == 75000) {
                        $lateCut = 75000;
                    }

                    if ($lateCut > 0) {
                        $newDeduct = max(0, $deduction - $lateCut);
                        $newNet = max(0, (float)$p->base_salary + (float)$p->allowances - $newDeduct);
                        DB::table('payrolls')->where('id', $p->id)->update([
                            'deductions' => $newDeduct,
                            'net_salary' => $newNet,
                            'updated_at' => now(),
                        ]);
                    }
                }
            }
        } catch (\Throwable $e) {
            // Ignore error
        }
    }

    public function down(): void
    {
        //
    }
};
