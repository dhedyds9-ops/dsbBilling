<?php

use App\Models\Finance\CashAccount;
use App\Models\Finance\MemberIncome;
use App\Models\Master\Member;
use Livewire\Component;

new class extends Component
{
    public function render()
    {
        $totalIncome = MemberIncome::where('status', 'posted')->sum('amount');
        $totalCash = CashAccount::sum('balance');
        $totalMembers = Member::count();
        
        return view('components.⚡dashboard', [
            'totalIncome' => $totalIncome,
            'totalCash' => $totalCash,
            'totalMembers' => $totalMembers,
        ]);
    }
};
?>

<div>
    <div class="row g-4 mb-4">
        <div class="col-md-4">
            <div class="card card-stat">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="card-icon bg-primary me-3">
                            <i class="bi bi-cash"></i>
                        </div>
                        <div>
                            <p class="text-muted mb-1">Total Pendapatan</p>
                            <h3 class="mb-0">Rp {{ number_format($totalIncome, 0, ',', '.') }}</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card card-stat">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="card-icon bg-success me-3">
                            <i class="bi bi-wallet2"></i>
                        </div>
                        <div>
                            <p class="text-muted mb-1">Total Kas</p>
                            <h3 class="mb-0">Rp {{ number_format($totalCash, 0, ',', '.') }}</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card card-stat">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="card-icon bg-warning me-3">
                            <i class="bi bi-people"></i>
                        </div>
                        <div>
                            <p class="text-muted mb-1">Total Anggota</p>
                            <h3 class="mb-0">{{ $totalMembers }}</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Grafik Pendapatan Bulanan</h5>
                </div>
                <div class="card-body">
                    <canvas id="incomeChart" height="300"></canvas>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Aktivitas Terbaru</h5>
                </div>
                <div class="card-body">
                    <div class="list-group list-group-flush">
                        <div class="list-group-item">
                            <div class="d-flex align-items-center">
                                <i class="bi bi-info-circle text-primary me-3"></i>
                                <div>
                                    <p class="mb-0 fw-semibold">Sistem berhasil diinisialisasi</p>
                                    <small class="text-muted">Hari ini</small>
                                </div>
                            </div>
                        </div>
                        <div class="list-group-item">
                            <div class="d-flex align-items-center">
                                <i class="bi bi-check-circle text-success me-3"></i>
                                <div>
                                    <p class="mb-0 fw-semibold">Data master telah dimuat</p>
                                    <small class="text-muted">Hari ini</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const ctx = document.getElementById('incomeChart');
                new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Ags', 'Sep', 'Okt', 'Nov', 'Des'],
                        datasets: [{
                            label: 'Pendapatan',
                            data: [12000000, 19000000, 3000000, 5000000, 2000000, 3000000, 4000000, 5000000, 6000000, 7000000, 8000000, 9000000],
                            backgroundColor: 'rgba(102, 126, 234, 0.8)',
                            borderColor: 'rgba(102, 126, 234, 1)',
                            borderWidth: 1
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        scales: {
                            y: {
                                beginAtZero: true
                            }
                        }
                    }
                });
            });
        </script>
    @endpush
</div>
