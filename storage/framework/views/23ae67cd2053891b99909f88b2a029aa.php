<!DOCTYPE html>
<html>
<head>
    <title>Daftar Paket Internet</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
        }
        h1 {
            text-align: center;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <h1>Daftar Paket Internet</h1>
    <p>Dicetak pada: <?php echo e(now()->format('d/m/Y H:i')); ?></p>
    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Kode Paket</th>
                <th>Nama Paket</th>
                <th>Jenis Layanan</th>
                <th>Download/Upload</th>
                <th>Harga</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $profiles; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $profile): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                <tr>
                    <td><?php echo e($index + 1); ?></td>
                    <td><?php echo e($profile->code); ?></td>
                    <td><?php echo e($profile->name); ?></td>
                    <td><?php echo e(strtoupper($profile->service_type)); ?></td>
                    <td><?php echo e($profile->download_speed); ?> / <?php echo e($profile->upload_speed); ?> Mbps</td>
                    <td><?php echo e($profile->base_price ? 'Rp ' . number_format($profile->base_price, 0, ',', '.') : '-'); ?></td>
                    <td><?php echo e($profile->status === 'active' ? 'Aktif' : 'Nonaktif'); ?></td>
                </tr>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
        </tbody>
    </table>
</body>
</html><?php /**PATH C:\Users\Lenovo\OneDrive\dsBilling\resources\views\pdf\service-profiles.blade.php ENDPATH**/ ?>