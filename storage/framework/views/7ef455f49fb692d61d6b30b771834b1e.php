

<?php $__env->startSection('title', 'Senarai Tempahan'); ?>

<?php $__env->startSection('content'); ?>
<div class="flex items-center justify-between mb-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-800">Senarai Tempahan</h1>
        <p class="text-gray-500 text-sm mt-1"><?php echo e($tempahan->total()); ?> rekod</p>
    </div>
    <div class="flex gap-3">
        <a href="<?php echo e(route('tempahan.pdf', request()->query())); ?>" class="btn-secondary text-sm">
            <i class="fa-solid fa-file-pdf text-red-500"></i> Eksport PDF
        </a>
        <a href="<?php echo e(route('tempahan.excel', request()->query())); ?>" class="btn-secondary text-sm">
            <i class="fa-solid fa-file-excel text-green-600"></i> Eksport Excel
        </a>
        <a href="<?php echo e(route('tempahan.create')); ?>" class="btn-primary">
            <i class="fa-solid fa-plus"></i> Tempahan Baru
        </a>
    </div>
</div>


<div class="bg-white rounded-xl shadow-sm p-4 mb-5">
    <form method="GET" class="flex gap-3 flex-wrap">
        <select name="status" class="form-input w-auto text-sm">
            <option value="">Semua Status</option>
            <option value="menunggu" <?php echo e(request('status') === 'menunggu' ? 'selected' : ''); ?>>Menunggu Kelulusan</option>
            <option value="diluluskan" <?php echo e(request('status') === 'diluluskan' ? 'selected' : ''); ?>>Diluluskan</option>
            <option value="ditolak" <?php echo e(request('status') === 'ditolak' ? 'selected' : ''); ?>>Ditolak</option>
        </select>
        <select name="bilik_id" class="form-input w-auto text-sm">
            <option value="">Semua Bilik</option>
            <?php $__currentLoopData = $bilik; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $b): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <option value="<?php echo e($b->id); ?>" <?php echo e(request('bilik_id') == $b->id ? 'selected' : ''); ?>><?php echo e($b->nama); ?></option>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </select>
        <input type="text" name="carian" value="<?php echo e(request('carian')); ?>" placeholder="Cari nama mesyuarat..."
            class="form-input flex-1 min-w-[200px] text-sm">
        <button type="submit" class="btn-primary text-sm">
            <i class="fa-solid fa-search"></i> Cari
        </button>
        <?php if(request()->hasAny(['status','bilik_id','carian'])): ?>
        <a href="<?php echo e(route('tempahan.index')); ?>" class="btn-secondary text-sm">Reset</a>
        <?php endif; ?>
    </form>
</div>


<div class="bg-white rounded-xl shadow-sm overflow-hidden">
    <table class="table w-full">
        <thead class="table-header">
            <tr>
                <th>Mesyuarat</th>
                <th>Tarikh</th>
                <th>Masa</th>
                <th>Bilik</th>
                <th>Pemohon</th>
                <th>Status</th>
                <th>Tindakan</th>
            </tr>
        </thead>
        <tbody>
            <?php $__empty_1 = true; $__currentLoopData = $tempahan; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $t): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <tr>
                <td class="font-semibold"><?php echo e($t->nama_mesyuarat); ?></td>
                <td><?php echo e($t->tarikh->format('d/m/Y')); ?></td>
                <td><?php echo e($t->masa_label); ?></td>
                <td><?php echo e($t->bilik->nama ?? '-'); ?></td>
                <td><?php echo e($t->pengguna->name ?? '-'); ?></td>
                <td>
                    <?php if($t->status === 'diluluskan'): ?>
                        <span class="badge-lulus">Diluluskan</span>
                    <?php elseif($t->status === 'menunggu'): ?>
                        <span class="badge-menunggu">Menunggu Kelulusan</span>
                    <?php else: ?>
                        <span class="badge-tolak">Ditolak</span>
                    <?php endif; ?>
                </td>
                <td>
                    <a href="<?php echo e(route('tempahan.show', $t)); ?>" class="text-amber-500 hover:text-amber-700 text-sm font-semibold">
                        <i class="fa-solid fa-eye mr-1"></i>Lihat
                    </a>
                </td>
            </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <tr>
                <td colspan="7" class="text-center py-12 text-gray-400">
                    <i class="fa-solid fa-inbox text-3xl mb-3 block"></i>
                    Tiada rekod tempahan ditemui
                </td>
            </tr>
            <?php endif; ?>
        </tbody>
    </table>

    <?php if($tempahan->hasPages()): ?>
    <div class="px-6 py-4 border-t border-gray-100">
        <?php echo e($tempahan->withQueryString()->links()); ?>

    </div>
    <?php endif; ?>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\ibook\resources\views/tempahan/index.blade.php ENDPATH**/ ?>