

<?php $__env->startSection('title', 'Bilik Mesyuarat'); ?>

<?php $__env->startSection('content'); ?>
<div class="flex items-center justify-between mb-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-800">Bilik Mesyuarat</h1>
        <p class="text-gray-500 text-sm mt-1"><?php echo e($bilik->count()); ?> bilik berdaftar</p>
    </div>
    <a href="<?php echo e(route('bilik.create')); ?>" class="btn-primary">
        <i class="fa-solid fa-plus"></i> Tambah Bilik
    </a>
</div>

<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
    <?php $__empty_1 = true; $__currentLoopData = $bilik; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $b): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
        <div class="h-40 bg-gradient-to-br from-slate-100 to-slate-200 flex items-center justify-center">
            <i class="fa-solid fa-door-open text-slate-400 text-5xl"></i>
        </div>
        <div class="p-5">
            <div class="flex items-start justify-between mb-3">
                <h3 class="font-bold text-gray-800"><?php echo e($b->nama); ?></h3>
                <span class="text-xs font-semibold px-2 py-1 rounded-full <?php echo e($b->isAktif() ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500'); ?>">
                    <?php echo e($b->isAktif() ? 'Aktif' : 'Tidak Aktif'); ?>

                </span>
            </div>

            <div class="flex items-center gap-2 text-sm text-gray-500 mb-2">
                <i class="fa-solid fa-users text-amber-400"></i>
                <?php echo e($b->kapasiti); ?> orang
                <?php if($b->lokasi): ?>
                &middot; <i class="fa-solid fa-location-dot text-amber-400"></i> <?php echo e($b->lokasi); ?>

                <?php endif; ?>
            </div>

            <?php if($b->kemudahan && count($b->kemudahan) > 0): ?>
            <div class="flex flex-wrap gap-1 mb-3">
                <?php $__currentLoopData = $b->kemudahan; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $k): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <span class="text-xs bg-gray-100 text-gray-600 px-2 py-1 rounded">
                    <i class="fa-solid fa-check text-amber-400 mr-1"></i><?php echo e($k); ?>

                </span>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
            <?php endif; ?>

            <div class="mb-4">
                <div class="flex justify-between text-sm mb-1">
                    <span class="text-gray-500">Penggunaan bulan ini</span>
                    <span class="font-semibold text-gray-700"><?php echo e($b->penggunaan_bulan_ini); ?>%</span>
                </div>
                <div class="progress-bar">
                    <div class="progress-fill" style="width:<?php echo e($b->penggunaan_bulan_ini); ?>%"></div>
                </div>
            </div>

            <div class="flex gap-2 pt-3 border-t border-gray-100">
                <a href="<?php echo e(route('bilik.edit', $b)); ?>" class="text-amber-500 text-sm font-semibold hover:underline flex items-center gap-1">
                    <i class="fa-solid fa-pen"></i> Edit
                </a>
                <form method="POST" action="<?php echo e(route('bilik.destroy', $b)); ?>" class="ml-auto"
                    onsubmit="return confirm('Padam bilik ini?')">
                    <?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?>
                    <button type="submit" class="text-red-400 text-sm hover:text-red-600">
                        <i class="fa-solid fa-trash"></i> Padam
                    </button>
                </form>
            </div>
        </div>
    </div>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
    <div class="col-span-3 text-center py-16 text-gray-400 bg-white rounded-xl">
        <i class="fa-solid fa-door-open text-5xl mb-4"></i>
        <p>Tiada bilik mesyuarat berdaftar</p>
        <a href="<?php echo e(route('bilik.create')); ?>" class="btn-primary mt-4 inline-flex">Tambah Bilik</a>
    </div>
    <?php endif; ?>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\ibook\resources\views/bilik/index.blade.php ENDPATH**/ ?>