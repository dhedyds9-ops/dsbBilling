<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan Penjualan</title>
    <style>
        body { font-family: sans-serif; font-size: 12px; color: #333; line-height: 1.5; }
        .header { text-align: center; margin-bottom: 20px; border-bottom: 2px solid #333; padding-bottom: 10px; }
        .title { font-size: 18px; font-weight: bold; margin: 0; text-transform: uppercase; }
        .subtitle { font-size: 14px; margin: 5px 0 0 0; color: #555; }
        .info { margin-bottom: 20px; }
        .info table { width: 100%; border: none; }
        .info td { padding: 3px 0; border: none; }
        table.data { width: 100%; border-collapse: collapse; margin-top: 15px; }
        table.data th, table.data td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        table.data th { background-color: #f8f9fa; font-weight: bold; }
        .text-right { text-align: right !important; }
        .text-center { text-align: center !important; }
        .footer { margin-top: 30px; font-size: 10px; color: #777; text-align: center; border-top: 1px solid #eee; padding-top: 10px; }
        .total-row th { background-color: #f1f5f9; font-size: 14px; }
    </style>
</head>
<body>

    <div class="header">
        <p class="title">Laporan Penjualan Lunas</p>
        <p class="subtitle"><?php echo e($reseller->name); ?></p>
    </div>

    <div class="info">
        <table>
            <tr>
                <td width="15%"><strong>Periode</strong></td>
                <td width="35%">: <?php echo e(date('F', mktime(0, 0, 0, $month, 10))); ?> <?php echo e($year); ?></td>
                <td width="15%"><strong>Dicetak Pada</strong></td>
                <td width="35%">: <?php echo e($datePrinted); ?></td>
            </tr>
            <tr>
                <td><strong>Total Transaksi</strong></td>
                <td>: <?php echo e($sales->count()); ?></td>
                <td><strong>Dicetak Oleh</strong></td>
                <td>: <?php echo e($reseller->name); ?></td>
            </tr>
        </table>
    </div>

    <table class="data">
        <thead>
            <tr>
                <th width="5%" class="text-center">No</th>
                <th width="20%">Tgl Lunas</th>
                <th width="25%">No Tagihan</th>
                <th width="30%">Pelanggan</th>
                <th width="20%" class="text-right">Nominal (Rp)</th>
            </tr>
        </thead>
        <tbody>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_0 = true; $__currentLoopData = $sales; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $sale): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_0 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
            <tr>
                <td class="text-center"><?php echo e($index + 1); ?></td>
                <td><?php echo e($sale->updated_at->format('d M Y H:i')); ?></td>
                <td><?php echo e($sale->invoice_number); ?></td>
                <td><?php echo e($sale->customer->name ?? '-'); ?></td>
                <td class="text-right"><?php echo e(number_format($sale->total_amount, 0, ',', '.')); ?></td>
            </tr>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_0): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
            <tr>
                <td colspan="5" class="text-center">Tidak ada transaksi lunas pada periode ini.</td>
            </tr>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </tbody>
        <tfoot>
            <tr class="total-row">
                <th colspan="4" class="text-right">TOTAL PENDAPATAN</th>
                <th class="text-right"><?php echo e(number_format($totalRevenue, 0, ',', '.')); ?></th>
            </tr>
        </tfoot>
    </table>

    <div class="footer">
        Dicetak secara otomatis oleh dsBilling System &copy; <?php echo e(date('Y')); ?>

    </div>

</body>
</html>
<?php /**PATH D:\dsBilling\resources\views\reports\reseller\sales-pdf.blade.php ENDPATH**/ ?>