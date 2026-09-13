<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Struk Pembayaran - <?php echo e($invoice->invoice_number); ?></title>
    <style>
        @page {
            margin: 0;
            size: 80mm auto; /* 80mm roll paper */
        }
        body {
            font-family: 'Courier New', Courier, monospace; /* Typical receipt font */
            width: 72mm; /* Leave a little margin */
            margin: 0 auto;
            padding: 4mm 0;
            font-size: 12px;
            color: #000;
            background: #fff;
            line-height: 1.4;
        }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .text-left { text-align: left; }
        .font-bold { font-weight: bold; }
        .mb-1 { margin-bottom: 4px; }
        .mb-2 { margin-bottom: 8px; }
        .mt-2 { margin-top: 8px; }
        .pb-2 { padding-bottom: 8px; }
        .pt-2 { padding-top: 8px; }
        .border-t { border-top: 1px dashed #000; }
        .border-b { border-bottom: 1px dashed #000; }
        table { width: 100%; border-collapse: collapse; }
        td { vertical-align: top; padding: 2px 0; }
        
        .header h1 {
            font-size: 16px;
            margin: 0 0 4px 0;
        }
        .header p {
            margin: 0;
            font-size: 11px;
        }
        
        .divider {
            border-top: 1px dashed #000;
            margin: 8px 0;
        }
        
        @media print {
            .no-print { display: none !important; }
        }
    </style>
</head>
<body onload="window.print()">
    <!-- Print Button (Hidden in print) -->
    <div class="no-print" style="text-align: center; margin-bottom: 20px;">
        <button onclick="window.print()" style="padding: 10px 20px; font-size: 14px; cursor: pointer;">Print Struk</button>
        <button onclick="window.close()" style="padding: 10px 20px; font-size: 14px; cursor: pointer;">Tutup</button>
    </div>

    <div class="header text-center">
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(!empty($company['logo_url'])): ?>
            <img src="<?php echo e($company['logo_url']); ?>" alt="Logo" style="max-height: 40px; margin-bottom: 4px; filter: grayscale(100%);">
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        <h1><?php echo e($company['name'] ?? 'BillingHub'); ?></h1>
        <p><?php echo e($company['address'] ?? ''); ?></p>
        <p>Telp: <?php echo e($company['phone'] ?? '-'); ?></p>
    </div>

    <div class="divider"></div>

    <table>
        <tr>
            <td>No. Inv</td>
            <td>: <?php echo e($invoice->invoice_number); ?></td>
        </tr>
        <tr>
            <td>Tanggal</td>
            <td>: <?php echo e($invoice->issue_date ? $invoice->issue_date->format('d/m/Y') : date('d/m/Y')); ?></td>
        </tr>
        <tr>
            <td>Pelanggan</td>
            <td>: <?php echo e($invoice->customer->name ?? '-'); ?></td>
        </tr>
        <tr>
            <td>Status</td>
            <td>: <?php echo e(strtoupper($invoice->status)); ?></td>
        </tr>
    </table>

    <div class="divider"></div>

    <table>
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $invoice->items ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
        <tr>
            <td colspan="2" class="pb-2">
                <?php echo e(is_array($item) ? ($item['description'] ?? 'Item') : ($item->description ?? 'Item')); ?><br>
                <div style="display: flex; justify-content: space-between; margin-top: 2px;">
                    <span><?php echo e(is_array($item) ? ($item['quantity'] ?? 1) : ($item->quantity ?? 1)); ?> x Rp <?php echo e(number_format(is_array($item) ? ($item['unit_price'] ?? 0) : ($item->unit_price ?? 0), 0, ',', '.')); ?></span>
                    <span>Rp <?php echo e(number_format(is_array($item) ? ($item['subtotal'] ?? 0) : ($item->subtotal ?? $item->total ?? 0), 0, ',', '.')); ?></span>
                </div>
            </td>
        </tr>
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
    </table>

    <div class="divider"></div>

    <table>
        <tr>
            <td>Subtotal</td>
            <td class="text-right">Rp <?php echo e(number_format($invoice->subtotal, 0, ',', '.')); ?></td>
        </tr>
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($invoice->tax_amount > 0): ?>
        <tr>
            <td>PPN</td>
            <td class="text-right">Rp <?php echo e(number_format($invoice->tax_amount, 0, ',', '.')); ?></td>
        </tr>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        <tr>
            <td class="font-bold pt-2 border-t">TOTAL</td>
            <td class="text-right font-bold pt-2 border-t">Rp <?php echo e(number_format($invoice->total_amount, 0, ',', '.')); ?></td>
        </tr>
        <tr>
            <td>Sudah Dibayar</td>
            <td class="text-right">Rp <?php echo e(number_format($invoice->paid_amount, 0, ',', '.')); ?></td>
        </tr>
    </table>

    <div class="divider"></div>
    
    <div class="text-center" style="font-size: 11px;">
        <p class="mb-1">Terima kasih atas pembayaran Anda.</p>
        <p>Layanan Anda aktif dan dapat digunakan.</p>
        <p class="mt-2 text-center" style="font-size: 10px;">-- Dicetak dari Portal Pelanggan --</p>
    </div>
</body>
</html>
<?php /**PATH D:\dsBilling\resources\views\customer-portal\billing\invoice-80mm.blade.php ENDPATH**/ ?>