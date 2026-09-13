<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan Komisi - <?php echo e($monthName); ?></title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            color: #333;
            margin: 0;
            padding: 0;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #2563eb;
            padding-bottom: 10px;
        }
        .header h1 {
            margin: 0 0 5px 0;
            font-size: 18px;
            color: #1e3a8a;
        }
        .header p {
            margin: 0;
            color: #64748b;
        }
        .summary-box {
            width: 100%;
            margin-bottom: 20px;
            border-collapse: collapse;
        }
        .summary-box td {
            border: 1px solid #e2e8f0;
            padding: 10px;
            text-align: center;
            width: 25%;
            background-color: #f8fafc;
        }
        .summary-box .label {
            font-size: 10px;
            color: #64748b;
            text-transform: uppercase;
            font-weight: bold;
        }
        .summary-box .value {
            font-size: 16px;
            font-weight: bold;
            color: #0f172a;
            margin-top: 5px;
        }
        .summary-box .value.profit {
            color: #059669;
        }
        table.data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        table.data-table th, table.data-table td {
            border: 1px solid #cbd5e1;
            padding: 8px;
            text-align: left;
        }
        table.data-table th {
            background-color: #f1f5f9;
            font-weight: bold;
            color: #334155;
        }
        .text-right {
            text-align: right !important;
        }
        .text-center {
            text-align: center !important;
        }
        .footer {
            margin-top: 30px;
            font-size: 10px;
            color: #94a3b8;
            text-align: center;
            border-top: 1px solid #e2e8f0;
            padding-top: 10px;
        }
    </style>
</head>
<body>

    <div class="header">
        <h1>LAPORAN KOMISI & MARGIN RESELLER</h1>
        <p>Reseller: <?php echo e($reseller->name); ?> | Periode: <?php echo e($monthName); ?></p>
    </div>

    <table class="summary-box">
        <tr>
            <td>
                <div class="label">Total Penjualan</div>
                <div class="value">Rp <?php echo e(number_format($grossRevenue, 0, ',', '.')); ?></div>
            </td>
            <td>
                <div class="label">Harga Modal (Pusat)</div>
                <div class="value">Rp <?php echo e(number_format($totalCost, 0, ',', '.')); ?></div>
            </td>
            <td>
                <div class="label">Estimasi Laba Bersih</div>
                <div class="value profit">Rp <?php echo e(number_format($netMargin, 0, ',', '.')); ?></div>
            </td>
            <td>
                <div class="label">Rasio Margin</div>
                <div class="value"><?php echo e(number_format($profitMarginPercent, 1, ',', '.')); ?>%</div>
            </td>
        </tr>
    </table>

    <h3 style="font-size: 14px; margin-bottom: 10px; color: #334155;">Rincian Transaksi Komisi</h3>
    <table class="data-table">
        <thead>
            <tr>
                <th width="15%">Tgl Lunas</th>
                <th width="35%">Pelanggan / No Tagihan</th>
                <th width="20%">Profil Paket</th>
                <th width="15%" class="text-right">Harga Jual</th>
                <th width="15%" class="text-right">Estimasi Margin</th>
            </tr>
        </thead>
        <tbody>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_0 = true; $__currentLoopData = $details; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_0 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                <?php
                    $subtotal = $row->items->sum('subtotal');
                    $cost = $row->items->sum('reseller_settlement_price');
                    if ($cost <= 0) {
                        $margin = $subtotal * 0.15;
                    } else {
                        $margin = $subtotal - $cost;
                    }
                    $sp = $row->customer ? $row->customer->customerServices()->with('serviceProfile')->first() : null;
                    $paket = ($sp && $sp->serviceProfile) ? $sp->serviceProfile->name : '-';
                ?>
                <tr>
                    <td><?php echo e($row->updated_at->format('d/m/Y H:i')); ?></td>
                    <td>
                        <strong><?php echo e($row->customer->name ?? '-'); ?></strong><br>
                        <span style="font-size: 10px; color: #64748b;"><?php echo e($row->invoice_number); ?></span>
                    </td>
                    <td><?php echo e($paket); ?></td>
                    <td class="text-right">Rp <?php echo e(number_format($subtotal, 0, ',', '.')); ?></td>
                    <td class="text-right" style="color: #059669; font-weight: bold;">Rp <?php echo e(number_format($margin, 0, ',', '.')); ?></td>
                </tr>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_0): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                <tr>
                    <td colspan="5" class="text-center">Tidak ada data transaksi komisi pada bulan ini.</td>
                </tr>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </tbody>
    </table>

    <div class="footer">
        Dicetak pada: <?php echo e(now()->format('d/m/Y H:i')); ?> oleh <?php echo e($reseller->name); ?> melalui sistem dsBilling.
    </div>

</body>
</html><?php /**PATH D:\dsBilling\resources\views\reports\reseller\commission-pdf.blade.php ENDPATH**/ ?>