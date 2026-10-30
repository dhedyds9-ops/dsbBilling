<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class UserExport implements FromCollection, WithHeadings, WithMapping, WithStyles, ShouldAutoSize
{
    protected $users;

    public function __construct($users)
    {
        if ($users instanceof \Illuminate\Database\Eloquent\Builder) {
            $this->users = $users->get();
        } else {
            $this->users = $users;
        }
    }

    public function collection()
    {
        return collect($this->users);
    }

    public function headings(): array
    {
        return [
            'ID User',
            'Nama Lengkap',
            'Email',
            'Username',
            'No. WhatsApp / HP',
            'Status Akun',
            'Status Kepegawaian',
            'NIK Pegawai',
            'Job Function',
            'Jabatan (Job Title)',
            'Role Akses',
            'Tanggal Terdaftar',
        ];
    }

    public function map($user): array
    {
        $phone = $user->whatsapp ?: ($user->employee?->phone ?: ($user->phone ?? '-'));
        $empStatus = $user->employee ? 'Pegawai' : 'Non-Pegawai';
        $empNik = $user->employee?->nik ?? '-';
        $roleNames = $user->roles->pluck('display_name')->filter()->implode(', ')
            ?: ($user->roles->pluck('name')->implode(', ') ?: '-');

        return [
            $user->id,
            $user->name,
            $user->email,
            $user->username ?? '-',
            $phone,
            $user->is_active ? 'Aktif' : 'Nonaktif',
            $empStatus,
            $empNik,
            $user->job_function ?? '-',
            $user->job_title ?? '-',
            $roleNames,
            $user->created_at ? $user->created_at->format('Y-m-d H:i:s') : '-',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => '4F46E5']], // Indigo 600
            ],
        ];
    }
}
