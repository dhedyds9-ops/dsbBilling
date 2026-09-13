file_php = 'D:/dsBilling/app/Livewire/Admin/Payroll/Generate.php'
with open(file_php, 'r', encoding='utf-8') as f:
    content = f.read()

import re

search = """                $exists = Payroll::where('employee_id', $employee->id)
                    ->where('period_month', $monthInt)
                    ->where('period_year', $yearInt)
                    ->exists();

                if (!$exists) {
                    Payroll::create([
                        'employee_id' => $employee->id,
                        'period_month' => $monthInt,
                        'period_year' => $yearInt,
                        'period_start' => $this->period_start ?: null,
                        'period_end' => $this->period_end ?: null,
                        'base_salary' => $employee->base_salary,
                        'allowances' => 0,
                        'deductions' => 0,
                        'net_salary' => $employee->base_salary,
                        'status' => 'draft',
                        'notes' => $this->notes
                    ]);
                    $generatedCount++;
                }"""

replace = """                // Gunakan withTrashed() agar record yang sudah di soft-delete bisa terdeteksi dan di-restore
                $payroll = Payroll::withTrashed()
                    ->where('employee_id', $employee->id)
                    ->where('period_month', $monthInt)
                    ->where('period_year', $yearInt)
                    ->first();

                if ($payroll) {
                    // Jika ada dan sudah dihapus (soft delete), kita kembalikan (restore) dan perbarui datanya
                    if ($payroll->trashed()) {
                        $payroll->restore();
                        $payroll->update([
                            'period_start' => $this->period_start ?: null,
                            'period_end' => $this->period_end ?: null,
                            'base_salary' => $employee->base_salary,
                            'allowances' => 0,
                            'deductions' => 0,
                            'net_salary' => $employee->base_salary,
                            'status' => 'draft',
                            'notes' => $this->notes
                        ]);
                        $generatedCount++;
                    }
                    // Jika belum dihapus, biarkan saja (skip) agar tidak menimpa data yang sedang aktif
                } else {
                    Payroll::create([
                        'employee_id' => $employee->id,
                        'period_month' => $monthInt,
                        'period_year' => $yearInt,
                        'period_start' => $this->period_start ?: null,
                        'period_end' => $this->period_end ?: null,
                        'base_salary' => $employee->base_salary,
                        'allowances' => 0,
                        'deductions' => 0,
                        'net_salary' => $employee->base_salary,
                        'status' => 'draft',
                        'notes' => $this->notes
                    ]);
                    $generatedCount++;
                }"""

if search in content:
    content = content.replace(search, replace)
    with open(file_php, 'w', encoding='utf-8') as f:
        f.write(content)
    print("Generate logic updated to handle soft deletes")
else:
    print("Search block not found.")
