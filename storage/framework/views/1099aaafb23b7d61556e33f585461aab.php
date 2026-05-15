

<?php $__env->startSection('title', 'Tetapan'); ?>

<?php $__env->startSection('content'); ?>
<div class="mb-6">
    <h1 class="text-2xl font-bold text-gray-800">Tetapan</h1>
    <p class="text-gray-500 text-sm mt-1">Konfigurasi sistem</p>
</div>

<div class="max-w-2xl">
    <form method="POST" action="<?php echo e(route('tetapan.update')); ?>">
        <?php echo csrf_field(); ?>

        
        <div class="bg-white rounded-xl shadow-sm p-6 mb-5">
            <h2 class="font-bold text-gray-800 mb-5 pb-3 border-b border-gray-100">Maklumat Organisasi</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <div class="md:col-span-2">
                    <label class="form-label">Nama Jabatan <span class="text-red-500">*</span></label>
                    <input type="text" name="nama_jabatan" value="<?php echo e(old('nama_jabatan', $tetapan['nama_jabatan'] ?? '')); ?>"
                        class="form-input" placeholder="cth: Bahagian Pengurusan Teknologi Maklumat">
                    <?php $__errorArgs = ['nama_jabatan'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="text-red-500 text-xs mt-1"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                </div>
                <div>
                    <label class="form-label">Singkatan <span class="text-red-500">*</span></label>
                    <input type="text" name="singkatan" value="<?php echo e(old('singkatan', $tetapan['singkatan'] ?? '')); ?>"
                        class="form-input" placeholder="cth: BPTM">
                    <?php $__errorArgs = ['singkatan'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="text-red-500 text-xs mt-1"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                </div>
            </div>
        </div>

        
        <div class="bg-white rounded-xl shadow-sm p-6 mb-5">
            <h2 class="font-bold text-gray-800 mb-5 pb-3 border-b border-gray-100">Waktu Operasi</h2>
            <div class="grid grid-cols-2 gap-5">
                <div>
                    <label class="form-label">Masa Mula <span class="text-red-500">*</span></label>
                    <input type="time" name="masa_mula" value="<?php echo e(old('masa_mula', $tetapan['masa_mula'] ?? '08:00')); ?>"
                        class="form-input">
                    <?php $__errorArgs = ['masa_mula'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="text-red-500 text-xs mt-1"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                </div>
                <div>
                    <label class="form-label">Masa Tamat <span class="text-red-500">*</span></label>
                    <input type="time" name="masa_tamat" value="<?php echo e(old('masa_tamat', $tetapan['masa_tamat'] ?? '17:00')); ?>"
                        class="form-input">
                    <?php $__errorArgs = ['masa_tamat'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="text-red-500 text-xs mt-1"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                </div>
            </div>
        </div>

        
        <div class="bg-white rounded-xl shadow-sm p-6 mb-6">
            <h2 class="font-bold text-gray-800 mb-5 pb-3 border-b border-gray-100">Notifikasi</h2>
            <div class="space-y-4">
                <label class="flex items-center gap-3 cursor-pointer">
                    <input type="checkbox" name="notif_tempahan_baru" value="1"
                        class="w-4 h-4 rounded" style="accent-color:#f59e0b"
                        <?php echo e(($tetapan['notif_tempahan_baru'] ?? '1') === '1' ? 'checked' : ''); ?>>
                    <div>
                        <div class="font-semibold text-sm text-gray-700">E-mel notifikasi untuk tempahan baru</div>
                        <div class="text-xs text-gray-400">Hantar emel kepada urus setia apabila ada tempahan baru</div>
                    </div>
                </label>
                <label class="flex items-center gap-3 cursor-pointer">
                    <input type="checkbox" name="notif_kelulusan" value="1"
                        class="w-4 h-4 rounded" style="accent-color:#f59e0b"
                        <?php echo e(($tetapan['notif_kelulusan'] ?? '1') === '1' ? 'checked' : ''); ?>>
                    <div>
                        <div class="font-semibold text-sm text-gray-700">E-mel notifikasi untuk kelulusan/penolakan</div>
                        <div class="text-xs text-gray-400">Hantar emel kepada pemohon selepas keputusan</div>
                    </div>
                </label>
                <label class="flex items-center gap-3 cursor-pointer">
                    <input type="checkbox" name="peringatan_mesyuarat" value="1"
                        class="w-4 h-4 rounded" style="accent-color:#f59e0b"
                        <?php echo e(($tetapan['peringatan_mesyuarat'] ?? '1') === '1' ? 'checked' : ''); ?>>
                    <div>
                        <div class="font-semibold text-sm text-gray-700">Peringatan mesyuarat (1 jam sebelum)</div>
                        <div class="text-xs text-gray-400">Hantar peringatan emel 1 jam sebelum mesyuarat bermula</div>
                    </div>
                </label>
            </div>
        </div>

        <button type="submit" class="btn-primary">
            <i class="fa-solid fa-floppy-disk"></i> Simpan Tetapan
        </button>
    </form>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\ibook\resources\views/tetapan/index.blade.php ENDPATH**/ ?>