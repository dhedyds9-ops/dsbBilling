<!DOCTYPE html>
<html>
<head>
    <title>Service Profiles</title>
    <style>
        body { font-family: Arial, sans-serif; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
    </style>
</head>
<body>
    <h1>Service Profiles</h1>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Kode</th>
                <th>Nama</th>
                <th>Tipe</th>
                <th>Download</th>
                <th>Upload</th>
                <th>Harga</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $profiles; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $profile): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
            <tr>
                <td><?php echo e($profile->id); ?></td>
                <td><?php echo e($profile->code); ?></td>
                <td><?php echo e($profile->name); ?></td>
                <td><?php echo e($profile->serviceProfileType?->name); ?></td>
                <td><?php echo e($profile->download_speed ?: '-'); ?> Mbps</td>
                <td><?php echo e($profile->upload_speed ?: '-'); ?> Mbps</td>
                <td><?php echo e($profile->price ? 'Rp ' . number_format($profile->price, 0, ',', '.') : '-'); ?></td>
                <td><?php echo e($profile->status === 'active' ? 'Aktif' : 'Nonaktif'); ?></td>
            </tr>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
        </tbody>
    </table>
</body>
</html><?php /**PATH D:\dsBilling\resources\views\isp\service-profiles\exports\pdf.blade.php ENDPATH**/ ?>