<?php

namespace App\Http\Controllers\Api\Mobile;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\Payroll;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MobilePayrollApiController extends Controller
{
    /**
     * Get monthly payroll history for authenticated technician/staff.
     *
     * GET /api/mobile/payroll
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $employeeId = $user->employee->id ?? Employee::where('user_id', $user->id)->value('id');

        if (!$employeeId) {
            return response()->json([
                'success' => true,
                'message' => 'Data karyawan belum terhubung dengan akun Anda.',
                'data' => [],
            ]);
        }

        $payrolls = Payroll::where('employee_id', $employeeId)
            ->orderBy('period_year', 'desc')
            ->orderBy('period_month', 'desc')
            ->limit(24)
            ->get()
            ->map(function (Payroll $p) {
                return [
                    'id' => $p->id,
                    'period' => sprintf('%04d-%02d', $p->period_year, $p->period_month),
                    'period_label' => Carbon::createFromDate($p->period_year, $p->period_month, 1)->translatedFormat('F Y'),
                    'base_salary' => (float) $p->base_salary,
                    'allowances' => (float) $p->allowances,
                    'deductions' => (float) $p->deductions,
                    'net_salary' => (float) $p->net_salary,
                    'net_salary_formatted' => 'Rp ' . number_format((float) $p->net_salary, 0, ',', '.'),
                    'status' => $p->status,
                    'payment_date' => $p->payment_date ? $p->payment_date->toDateString() : null,
                    'work_days' => $p->work_days,
                    'present_days' => $p->present_days,
                ];
            });

        return response()->json([
            'success' => true,
            'message' => 'Daftar riwayat slip gaji berhasil dimuat.',
            'count' => $payrolls->count(),
            'data' => $payrolls,
        ]);
    }

    /**
     * Get detailed slip gaji by ID.
     *
     * GET /api/mobile/payroll/{id}
     */
    public function show(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        $employeeId = $user->employee->id ?? Employee::where('user_id', $user->id)->value('id');

        $payroll = Payroll::findOrFail($id);

        // Security check: only own payroll unless Admin/Manager
        $isManager = $user->hasRole(['administrator', 'superadmin', 'manager', 'admin'])
            || in_array(strtoupper($user->job_function ?? ''), ['ADMINISTRATOR', 'MANAGER']);

        if (!$isManager && $payroll->employee_id !== $employeeId) {
            return response()->json([
                'success' => false,
                'message' => 'Akses ditolak: Anda tidak memiliki wewenang melihat slip gaji ini.',
            ], 403);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $payroll->id,
                'employee_id' => $payroll->employee_id,
                'period' => sprintf('%04d-%02d', $payroll->period_year, $payroll->period_month),
                'period_label' => \Carbon\Carbon::createFromDate($payroll->period_year, $payroll->period_month, 1)->translatedFormat('F Y'),
                'base_salary' => (float) $payroll->base_salary,
                'allowances' => (float) $payroll->allowances,
                'deductions' => (float) $payroll->deductions,
                'net_salary' => (float) $payroll->net_salary,
                'net_salary_formatted' => 'Rp ' . number_format((float) $payroll->net_salary, 0, ',', '.'),
                'status' => $payroll->status,
                'payment_date' => $payroll->payment_date ? $payroll->payment_date->toDateString() : null,
                'attendance_summary' => [
                    'work_days' => $payroll->work_days,
                    'present_days' => $payroll->present_days,
                    'late_days' => $payroll->late_days,
                    'sick_days' => $payroll->sick_days,
                    'permission_days' => $payroll->permission_days,
                    'absent_days' => $payroll->absent_days,
                ],
                'notes' => $payroll->notes,
            ],
        ]);
    }
}
