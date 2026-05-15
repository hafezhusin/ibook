

<?php $__env->startSection('title', 'Papan Pemuka'); ?>

<?php $__env->startSection('content'); ?>
<?php
    $namaBulan = ['', 'Januari', 'Februari', 'Mac', 'April', 'Mei', 'Jun', 'Julai', 'Ogos', 'September', 'Oktober', 'November', 'Disember'];
?>

<div class="mb-6">
    <h1 class="text-2xl font-bold text-gray-800">Selamat Datang, <?php echo e(auth()->user()->name); ?></h1>
    <?php if(auth()->user()->bolehLuluskan() && $menungguKelulusan > 0): ?>
    <p class="text-gray-500 mt-1">Anda mempunyai <span class="font-semibold text-amber-600"><?php echo e($menungguKelulusan); ?> permohonan</span> menunggu kelulusan.</p>
    <?php else: ?>
    <p class="text-gray-500 mt-1"><?php echo e(\Carbon\Carbon::now()->isoFormat('dddd, D MMMM YYYY')); ?></p>
    <?php endif; ?>
</div>


<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-5 mb-7">
    <div class="stat-card">
        <div class="flex items-start justify-between">
            <div class="w-11 h-11 rounded-xl flex items-center justify-center" style="background:#fef3c7">
                <i class="fa-solid fa-calendar-check text-amber-500 text-lg"></i>
            </div>
            <span class="text-green-500 text-sm font-semibold">↑ Aktif</span>
        </div>
        <div class="mt-4">
            <div class="text-3xl font-bold text-gray-800"><?php echo e($jumlahTempahan); ?></div>
            <div class="text-sm text-gray-500 mt-1">Jumlah Tempahan Bulan Ini</div>
            <div class="text-xs text-gray-400"><?php echo e($namaBulan[$bulanIni]); ?> <?php echo e($tahunIni); ?></div>
        </div>
    </div>

    <div class="stat-card">
        <div class="flex items-start justify-between">
            <div class="w-11 h-11 rounded-xl flex items-center justify-center" style="background:#fef3c7">
                <i class="fa-solid fa-clock text-amber-500 text-lg"></i>
            </div>
            <?php if($menungguKelulusan > 0): ?>
            <span class="text-amber-500 text-sm font-semibold"><?php echo e($menungguKelulusan); ?> baru</span>
            <?php endif; ?>
        </div>
        <div class="mt-4">
            <div class="text-3xl font-bold text-gray-800"><?php echo e($menungguKelulusan); ?></div>
            <div class="text-sm text-gray-500 mt-1">Menunggu Kelulusan</div>
            <div class="text-xs text-gray-400">Perlu tindakan</div>
        </div>
    </div>

    <div class="stat-card">
        <div class="flex items-start justify-between">
            <div class="w-11 h-11 rounded-xl flex items-center justify-center" style="background:#fef3c7">
                <i class="fa-solid fa-users text-amber-500 text-lg"></i>
            </div>
        </div>
        <div class="mt-4">
            <div class="text-3xl font-bold text-gray-800"><?php echo e($mesyuaratHariIni); ?></div>
            <div class="text-sm text-gray-500 mt-1">Mesyuarat Hari Ini</div>
            <div class="text-xs text-gray-400"><?php echo e(\Carbon\Carbon::today()->isoFormat('D MMMM YYYY')); ?></div>
        </div>
    </div>

    <div class="stat-card">
        <div class="flex items-start justify-between">
            <div class="w-11 h-11 rounded-xl flex items-center justify-center" style="background:#fef3c7">
                <i class="fa-solid fa-chart-bar text-amber-500 text-lg"></i>
            </div>
        </div>
        <div class="mt-4">
            <div class="text-3xl font-bold text-gray-800"><?php echo e($kadarPenggunaan); ?>%</div>
            <div class="text-sm text-gray-500 mt-1">Kadar Penggunaan Bilik</div>
            <div class="text-xs text-gray-400">Purata semua bilik</div>
        </div>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    
    <div class="lg:col-span-2 bg-white rounded-xl shadow-sm p-6">
        <div class="flex items-center justify-between mb-5">
            <div>
                <h2 class="font-bold text-gray-800 text-lg">Mesyuarat Akan Datang</h2>
                <p class="text-gray-400 text-sm">7 hari hadapan</p>
            </div>
            <a href="<?php echo e(route('kalendar')); ?>" class="text-amber-500 text-sm font-semibold hover:underline">Lihat Kalendar</a>
        </div>

        <?php $__empty_1 = true; $__currentLoopData = $mesyuaratAkanDatang; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $m): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
        <div class="flex gap-4 py-3 border-b border-gray-100 last:border-0">
            <div class="text-center min-w-[48px]">
                <div class="text-xs text-gray-400"><?php echo e($m->tarikh->isoFormat('ddd')); ?></div>
                <div class="text-2xl font-bold text-gray-700"><?php echo e($m->tarikh->format('d')); ?></div>
            </div>
            <div class="flex-1">
                <div class="font-semibold text-gray-800"><?php echo e($m->nama_mesyuarat); ?></div>
                <div class="text-sm text-gray-500"><?php echo e($m->masa_label); ?> &middot; <?php echo e($m->bilik->nama ?? '-'); ?></div>
            </div>
            <div class="flex items-center gap-3">
                <span class="text-sm text-gray-500"><?php echo e($m->bilangan_peserta); ?> peserta</span>
                <?php if($m->status === 'diluluskan'): ?>
                    <span class="badge-lulus">Diluluskan</span>
                <?php elseif($m->status === 'menunggu'): ?>
                    <span class="badge-menunggu">Menunggu</span>
                <?php else: ?>
                    <span class="badge-tolak">Ditolak</span>
                <?php endif; ?>
            </div>
        </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
        <div class="text-center py-10 text-gray-400">
            <i class="fa-solid fa-calendar-xmark text-3xl mb-3"></i>
            <p>Tiada mesyuarat akan datang</p>
        </div>
        <?php endif; ?>
    </div>

    
    <div class="space-y-6">
        
        <?php if(auth()->user()->bolehLuluskan()): ?>
        <div class="bg-white rounded-xl shadow-sm p-6">
            <div class="flex items-center justify-between mb-4">
                <h2 class="font-bold text-gray-800">Menunggu Kelulusan</h2>
                <a href="<?php echo e(route('kelulusan')); ?>" class="text-amber-500 text-sm font-semibold hover:underline">Semua</a>
            </div>
            <?php $__empty_1 = true; $__currentLoopData = $menungguList; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $m): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <div class="py-2 border-b border-gray-100 last:border-0">
                <div class="font-semibold text-sm text-gray-800"><?php echo e($m->nama_mesyuarat); ?></div>
                <div class="text-xs text-gray-500"><?php echo e($m->tarikh->format('d/m/Y')); ?> &middot; <?php echo e($m->masa_label); ?></div>
                <a href="<?php echo e(route('kelulusan')); ?>" class="text-xs text-amber-500 hover:underline">
                    <i class="fa-solid fa-eye mr-1"></i>Semak
                </a>
            </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <p class="text-sm text-gray-400 text-center py-4">Tiada permohonan menunggu</p>
            <?php endif; ?>
        </div>
        <?php endif; ?>

        
        <div class="bg-white rounded-xl shadow-sm p-6">
            <h2 class="font-bold text-gray-800 mb-4">Penggunaan Bilik</h2>
            <?php $__currentLoopData = $penggunaanBilik; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $b): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <div class="mb-3">
                <div class="flex justify-between text-sm mb-1">
                    <span class="text-gray-700"><?php echo e($b['nama']); ?></span>
                    <span class="font-semibold text-gray-800"><?php echo e($b['peratusan']); ?>%</span>
                </div>
                <div class="progress-bar">
                    <div class="progress-fill" style="width:<?php echo e($b['peratusan']); ?>%"></div>
                </div>
            </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\ibook\resources\views/dashboard/index.blade.php ENDPATH**/ ?>