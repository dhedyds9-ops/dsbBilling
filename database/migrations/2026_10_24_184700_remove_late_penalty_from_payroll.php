<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Set penalti absensi ke 0 (potongan hanya jika ada kasbon)
        try {
            DB::table('settings')->updateOrInsert(
                ['key' => 'payroll.late_penalty'],
                ['value' => '0', 'updated_at' => now()]
            );
            DB::table('settings')->updateOrInsert(
                ['key' => 'payroll.absent_penalty'],
                ['value' => '0', 'updated_at' => now()]
            );
        } catch (\Throwable $e) {
            // Ignore if settings table differs
        }

        // 2. Bersihkan potongan denda absensi pada payroll berstatus 'draft'
        try {
            $draftPayrolls = DB::table('payrolls')->where('status', 'draft')->get();
            foreach ($draftPayrolls as $p) {
                $deduction = (float)$p->deductions;
                $notes = strtolower((string)$p->notes);

                // Jika potongan berasal dari denda absensi (bukan kasbon)
                $isPenalty = (str_contains($notes, 'terlambat') || str_contains($notes, 'alpa')) && !str_contains($notes, 'kasbon');
                
                if ($deduction > 0 && ($isPenalty || $deduction == 75000 || $deduction == 100000)) {
                    $newNet = max(0, (float)$p->base_salary + (float)$p->allowances);
                    DB::table('payrolls')->where('id', $p->id)->update([
                        'deductions' => 0,
                        'net_salary' => $newNet,
                        'notes'      => null,
                        'updated_at' => now(),
                    ]);
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
