<?php

namespace App\Livewire\Keuangan\IncomePeriode;

use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Exports\IncomeExport;
use Carbon\Carbon;

use App\Livewire\BaseEnterpriseList;
use App\Services\Keuangan\IncomeReportService;
use App\Services\Auth\UserQueryService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\Computed;

class Index extends BaseEnterpriseList
{
    public string $activeModule = 'keuangan';
    public string $activePage = 'income-periode';

    protected IncomeReportService $service;

    public function boot(IncomeReportService $service): void
    {
        $this->service = $service;
    }

    public $showFilterModal = false;

    public function mount(): void
    {
        parent::mount();
        $this->activeModule = 'keuangan';
        $this->activePage = 'income-periode';
        
        $this->filters = [
            'start_date' => now()->startOfMonth()->toDateString(),
            'end_date' => now()->endOfMonth()->toDateString(),
            'user_type' => 'all', // all, customer, voucher
            'service_type' => 'all',
            'profile_paket' => 'all',
            'reseller_id' => 'all',
            'method' => '',
        ];
        $this->perPage = 50;
    }

    public function openFilterModal()
    {
        $this->showFilterModal = true;
    }

    public function applyFilter()
    {
        $this->showFilterModal = false;
        $this->resetPage();
    }

    public function getRowsQuery()
    {
        return collect();
    }

    public function getRows()
    {
        return collect($this->periodData['rows'] ?? []);
    }

    #[Computed]
    public function getPeriodDataProperty(): array
    {
        return $this->service->getTransactions($this->filters);
    }

    #[Computed]
    public function getResellersProperty()
    {
        return app(UserQueryService::class)->getResellers();
    }

        public function updatedFiltersUserType($value): void
    {
        $this->filters['service_type'] = 'all';
        $this->filters['profile_paket'] = 'all';
    }

    public function updatedFiltersServiceType($value): void
    {
        $this->filters['profile_paket'] = 'all';
    }

    #[Computed]
    public function getServicesProperty()
    {
        $userType = $this->filters['user_type'] ?? 'all';
        if ($userType === 'customer') {
            return [
                (object)['id' => 'pppoe', 'name' => 'PPPoE'],
                (object)['id' => 'hotspot', 'name' => 'Hotspot']
            ];
        } elseif ($userType === 'voucher') {
            return [
                (object)['id' => 'voucher', 'name' => 'Voucher'],
                (object)['id' => 'evoucher', 'name' => 'E-Voucher']
            ];
        }
        return [
            (object)['id' => 'pppoe', 'name' => 'PPPoE'],
            (object)['id' => 'hotspot', 'name' => 'Hotspot'],
            (object)['id' => 'voucher', 'name' => 'Voucher'],
            (object)['id' => 'evoucher', 'name' => 'E-Voucher']
        ];
    }

    #[Computed]
    public function getProfilesProperty()
    {
        $st = $this->filters['service_type'] ?? 'all';
        $q = \App\Models\ISP\ServiceProfile::query();
        if ($st !== 'all') {
            $q->where('service_type', $st);
        } else {
            $ut = $this->filters['user_type'] ?? 'all';
            if ($ut === 'customer') {
                $q->whereIn('service_type', ['pppoe', 'hotspot']);
            } elseif ($ut === 'voucher') {
                $q->whereIn('service_type', ['voucher', 'evoucher']);
            }
        }
        return $q->get();
    }

    #[Computed]
    public function getFilterConfigProperty(): array
    {
        $resellerOptions = [];
        foreach ($this->resellers as $r) {
            $resellerOptions[$r->id] = $r->name;
        }

        $serviceOptions = [];
        foreach ($this->services as $s) {
            $serviceOptions[$s->id] = $s->name;
        }

        $profileOptions = [];
        foreach ($this->profiles as $p) {
            $profileOptions[$p->id] = $p->name;
        }

        return [
            [
                'key' => 'reseller_id',
                'label' => 'Semua Reseller',
                'type' => 'select',
                'options' => $resellerOptions
            ],
            [
                'key' => 'user_type',
                'label' => 'Semua Tipe Pengguna',
                'type' => 'select',
                'options' => [
                    'customer' => 'Customer/Member',
                    'voucher' => 'Voucher'
                ]
            ],
            [
                'key' => 'service_type',
                'label' => 'Semua Layanan',
                'type' => 'select',
                'options' => $serviceOptions
            ],
            [
                'key' => 'profile_paket',
                'label' => 'Semua Profil Paket',
                'type' => 'select',
                'options' => $profileOptions
            ]
        ];
    }

    public function getBulkActionsProperty(): array
    {
        return [];
    }

    public function handleBulkAction(string $action, array $ids): int
    {
        return 0;
    }

            public function exportCsv(): \Symfony\Component\HttpFoundation\BinaryFileResponse|\Illuminate\Http\RedirectResponse
    {
        return redirect()->back()->with('error', 'Silakan gunakan Export Excel atau PDF.');
    }

    public function exportExcel()
    {
        $data = $this->periodData;
        $rows = $data['rows'] ?? [];
        $filename = 'income-' . now()->format('YmdHis') . '.xlsx';
        return Excel::download(new IncomeExport($rows), $filename);
    }

    public function exportPdf()
    {
        $data = $this->periodData;
        $rows = $data['rows'] ?? [];
        
        $isHarian = isset($this->filters['date']);
        
        $title = $isHarian ? 'Pemasukan Harian' : 'Pemasukan Periode';
        $subtitle = $isHarian 
            ? 'Tanggal: ' . Carbon::parse($this->filters['date'] ?? now())->translatedFormat('l, d F Y')
            : 'Periode: ' . Carbon::parse($this->filters['start_date'] ?? now())->translatedFormat('d F Y') . ' s/d ' . Carbon::parse($this->filters['end_date'] ?? now())->translatedFormat('d F Y');

        $pdf = Pdf::loadView('exports.income-pdf', [
            'rows' => $rows,
            'title' => $title,
            'subtitle' => $subtitle,
        ])->setPaper('a4', 'landscape');
        
        return response()->streamDownload(function () use ($pdf) {
            echo $pdf->output();
        }, 'income-' . now()->format('YmdHis') . '.pdf');
    }

    public function render()
    {
        $data = $this->periodData;
        $items = collect($data['rows']);
        $page = $this->page ?? 1;
        
        $rows = new \Illuminate\Pagination\LengthAwarePaginator(
            $items->forPage($page, $this->perPage),
            $items->count(),
            $this->perPage,
            $page,
            ['path' => \Illuminate\Support\Facades\Request::url(), 'query' => \Illuminate\Support\Facades\Request::query()]
        );
        
        return view('livewire.keuangan.income-periode.index', [
            'rows' => $rows,
            'summary' => $data['summary'] ?? [],
        ]);
    }
}
