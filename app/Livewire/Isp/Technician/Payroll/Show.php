<?php
namespace App\Livewire\Isp\Technician\Payroll;

use App\Models\Payroll;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Show extends Component
{
    public Payroll $payroll;

    public function mount($id)
    {
        $this->payroll = Payroll::with('employee.user')->findOrFail($id);
        
        $user = Auth::user();
        if (!$user) {
            abort(403, 'Akses ditolak.');
        }

        // 1. SuperAdmin / Administrator memiliki hak pengawasan global
        $isGlobalAdmin = $user->hasRole(['superadmin', 'administrator', 'admin'])
            || in_array(strtolower((string) ($user->role ?? '')), ['superadmin', 'administrator', 'admin'], true)
            || (method_exists($user, 'isSuperAdmin') && $user->isSuperAdmin())
            || $user->can('access-all-payrolls');

        if ($isGlobalAdmin) {
            return;
        }

        // 2. Manager dibatasi secara ketat hanya pada cabang (Branch) penugasannya (P0-H2)
        $isManager = $user->hasRole('manager')
            || strtolower((string) ($user->role ?? '')) === 'manager'
            || $user->can('manage-workforce');

        if ($isManager) {
            $managerBranchId = $user->branch_id;
            $payrollEmployee = $this->payroll->employee;
            $employeeBranchId = $payrollEmployee?->user?->branch_id;

            // Jika manajer memiliki penugasan cabang, wajib mencocokkan cabang pegawai
            if ($managerBranchId) {
                if (!$employeeBranchId || (int) $employeeBranchId !== (int) $managerBranchId) {
                    abort(403, 'Anda tidak memiliki hak akses untuk melihat slip gaji karyawan di luar cabang Anda.');
                }
            }
            return;
        }

        // 3. Teknisi & Karyawan Lapangan: Hanya boleh melihat slip gaji milik sendiri
        $employee = $user->employee;

        // Teknisi wajib memiliki relasi employee yang valid
        if (!$employee) {
            abort(403, 'Akun Anda belum terhubung dengan data pegawai (Employee). Silakan hubungi HRD atau Administrator.');
        }

        // Payroll hanya boleh diakses oleh pemiliknya
        if ((int) $employee->id !== (int) $this->payroll->employee_id) {
            abort(403, 'Anda tidak memiliki hak akses untuk melihat slip gaji ini.');
        }
    }

    public function render()
    {
        return view('livewire.isp.technician.payroll.show')
            ->layout('layouts.technician-app');
    }
}
