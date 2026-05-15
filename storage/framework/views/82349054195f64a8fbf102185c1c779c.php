<!DOCTYPE html>
<html lang="ms">
<head>
<meta charset="UTF-8">
<style>
    body { font-family: Arial, sans-serif; font-size: 12px; color: #333; }
    h1 { color: #1a1a2e; font-size: 18px; }
    table { width: 100%; border-collapse: collapse; margin-top: 15px; }
    th { background: #1a1a2e; color: #fff; padding: 8px 10px; text-align: left; font-size: 11px; }
    td { padding: 7px 10px; border-bottom: 1px solid #eee; font-size: 11px; }
    .badge-menunggu { color: #92400e; font-weight: bold; }
    .badge-lulus { color: #065f46; font-weight: bold; }
    .badge-tolak { color: #991b1b; font-weight: bold; }
    .header { border-bottom: 2px solid #f59e0b; padding-bottom: 10px; margin-bottom: 15px; }
</style>
</head>
<body>
<div class="header">
    <h1>iBook 2.0 - Senarai Tempahan Bilik Mesyuarat</h1>
    <p>Tarikh Cetak: <?php echo e(now()->format('d/m/Y H:i')); ?></p>
</div>

<table>
    <thead>
        <tr>
            <th>#</th>
            <th>Nama Mesyuarat</th>
            <th>Tarikh</th>
            <th>Masa</th>
            <th>Bilik</th>
            <th>Pemohon</th>
            <th>Status</th>
        </tr>
    </thead>
    <tbody>
        <?php $__empty_1 = true; $__currentLoopData = $tempahan; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i => $t): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
        <tr>
            <td><?php echo e($i + 1); ?></td>
            <td><?php echo e($t->nama_mesyuarat); ?></td>
            <td><?php echo e($t->tarikh->format('d/m/Y')); ?></td>
            <td><?php echo e($t->masa_label); ?></td>
            <td><?php echo e($t->bilik->nama ?? '-'); ?></td>
            <td><?php echo e($t->pengguna->name ?? '-'); ?></td>
            <td class="badge-<?php echo e($t->status === 'diluluskan' ? 'lulus' : ($t->status === 'menunggu' ? 'menunggu' : 'tolak')); ?>">
                <?php echo e(ucfirst($t->status)); ?>

            </td>
        </tr>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
        <tr><td colspan="7" style="text-align:center;">Tiada rekod</td></tr>
        <?php endif; ?>
    </tbody>
</table>

<p style="margin-top:20px; text-align:center; color:#999; font-size:10px;">
    iBook 2.0 &copy; <?php echo e(date('Y')); ?>

</p>
</body>
</html>
<?php /**PATH C:\laragon\www\ibook\resources\views/tempahan/pdf.blade.php ENDPATH**/ ?>