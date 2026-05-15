

<?php $__env->startSection('title', 'Tempahan Baru'); ?>

<?php $__env->startSection('content'); ?>
<div class="mb-6">
    <h1 class="text-2xl font-bold text-gray-800">Tempahan Baru</h1>
    <p class="text-gray-500 mt-1">Isi maklumat mesyuarat untuk membuat tempahan bilik.</p>
</div>

<div class="bg-white rounded-xl shadow-sm p-8 max-w-3xl">
    <?php if($errors->any()): ?>
    <div class="alert-error">
        <ul class="list-disc list-inside text-sm">
            <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <li><?php echo e($error); ?></li>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </ul>
    </div>
    <?php endif; ?>

    <form method="POST" action="<?php echo e(route('tempahan.store')); ?>">
        <?php echo csrf_field(); ?>

        <div class="mb-5">
            <label class="form-label">Nama Mesyuarat <span class="text-red-500">*</span></label>
            <input type="text" name="nama_mesyuarat" value="<?php echo e(old('nama_mesyuarat')); ?>"
                class="form-input <?php $__errorArgs = ['nama_mesyuarat'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> border-red-400 <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                placeholder="cth: Mesyuarat Pengurusan Bil. 4/2026">
            <?php $__errorArgs = ['nama_mesyuarat'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="text-red-500 text-xs mt-1"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-5 mb-5">
            <div>
                <label class="form-label">Tarikh <span class="text-red-500">*</span></label>
                <input type="date" name="tarikh" value="<?php echo e(old('tarikh')); ?>"
                    min="<?php echo e(date('Y-m-d')); ?>"
                    class="form-input <?php $__errorArgs = ['tarikh'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> border-red-400 <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>">
                <?php $__errorArgs = ['tarikh'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="text-red-500 text-xs mt-1"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
            </div>
            <div>
                <label class="form-label">Bilik Mesyuarat <span class="text-red-500">*</span></label>
                <select name="bilik_id" class="form-input <?php $__errorArgs = ['bilik_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> border-red-400 <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>">
                    <option value="">Pilih bilik</option>
                    <?php $__currentLoopData = $bilik; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $b): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($b->id); ?>" <?php echo e(old('bilik_id') == $b->id ? 'selected' : ''); ?>>
                        <?php echo e($b->nama); ?> (<?php echo e($b->kapasiti); ?> orang)
                    </option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
                <?php $__errorArgs = ['bilik_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="text-red-500 text-xs mt-1"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
            </div>
        </div>

        <div class="mb-5">
            <label class="form-label">Masa Mesyuarat <span class="text-red-500">*</span></label>
            <div class="space-y-3">
                <?php $__currentLoopData = $sesi; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $s): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <label class="flex items-center gap-3 p-3 border-2 rounded-lg cursor-pointer transition-all
                    <?php echo e(old('sesi') === $key ? 'border-amber-400 bg-amber-50' : 'border-gray-200 hover:border-amber-300'); ?>"
                    onclick="selectSesi(this, '<?php echo e($key); ?>')">
                    <input type="radio" name="sesi" value="<?php echo e($key); ?>" class="text-amber-500"
                        <?php echo e(old('sesi') === $key ? 'checked' : ''); ?>>
                    <div>
                        <div class="font-semibold text-gray-800"><?php echo e(Str::upper(Str::replace('_', ' ', $key === 'pagi' ? 'SESI PAGI 1' : 'SESI PETANG 2'))); ?></div>
                        <div class="text-sm text-gray-500"><?php echo e($s['label']); ?></div>
                    </div>
                </label>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
            <?php $__errorArgs = ['sesi'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="text-red-500 text-xs mt-1"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-5 mb-5">
            <div>
                <label class="form-label">Bilangan Peserta <span class="text-red-500">*</span></label>
                <input type="number" name="bilangan_peserta" value="<?php echo e(old('bilangan_peserta')); ?>" min="1"
                    class="form-input <?php $__errorArgs = ['bilangan_peserta'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> border-red-400 <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                    placeholder="cth: 20">
                <?php $__errorArgs = ['bilangan_peserta'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="text-red-500 text-xs mt-1"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
            </div>
            <div>
                <label class="form-label">Kategori Mesyuarat <span class="text-red-500">*</span></label>
                <select name="kategori" class="form-input <?php $__errorArgs = ['kategori'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> border-red-400 <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>">
                    <option value="">Pilih kategori</option>
                    <?php $__currentLoopData = $kategori; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $k => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($k); ?>" <?php echo e(old('kategori') === $k ? 'selected' : ''); ?>><?php echo e($label); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
                <?php $__errorArgs = ['kategori'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="text-red-500 text-xs mt-1"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
            </div>
        </div>

        <div class="mb-5">
            <label class="form-label">Nama Pengerusi <span class="text-red-500">*</span></label>
            <input type="text" name="nama_pengerusi" value="<?php echo e(old('nama_pengerusi')); ?>"
                class="form-input <?php $__errorArgs = ['nama_pengerusi'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> border-red-400 <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                placeholder="cth: YBrs. Encik Ahmad">
            <?php $__errorArgs = ['nama_pengerusi'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="text-red-500 text-xs mt-1"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
        </div>

        <div class="mb-7">
            <label class="form-label">Tujuan / Agenda</label>
            <textarea name="tujuan" rows="4"
                class="form-input <?php $__errorArgs = ['tujuan'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> border-red-400 <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                placeholder="Nyatakan tujuan atau agenda mesyuarat"><?php echo e(old('tujuan')); ?></textarea>
            <?php $__errorArgs = ['tujuan'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="text-red-500 text-xs mt-1"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
        </div>

        <div class="flex gap-3">
            <button type="submit" class="btn-primary">
                <i class="fa-solid fa-paper-plane"></i> Hantar Permohonan
            </button>
            <a href="<?php echo e(route('tempahan.index')); ?>" class="btn-secondary">
                <i class="fa-solid fa-xmark"></i> Batal
            </a>
        </div>
    </form>
</div>

<?php $__env->startPush('scripts'); ?>
<script>
function selectSesi(label, key) {
    document.querySelectorAll('[onclick^="selectSesi"]').forEach(el => {
        el.classList.remove('border-amber-400', 'bg-amber-50');
        el.classList.add('border-gray-200');
    });
    label.classList.remove('border-gray-200');
    label.classList.add('border-amber-400', 'bg-amber-50');
}
</script>
<?php $__env->stopPush(); ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\ibook\resources\views/tempahan/create.blade.php ENDPATH**/ ?>