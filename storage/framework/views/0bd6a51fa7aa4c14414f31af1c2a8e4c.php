

<?php $__env->startSection('title', 'Pengguna'); ?>

<?php $__env->startSection('content'); ?>
<div class="flex items-center justify-between mb-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-800">Pengguna</h1>
        <p class="text-gray-500 text-sm mt-1">Pengurusan pengguna dan peranan</p>
    </div>
    <button onclick="document.getElementById('modal-tambah').classList.remove('hidden')" class="btn-primary">
        <i class="fa-solid fa-plus"></i> Tambah Pengguna
    </button>
</div>


<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5 mb-8">
    <?php $__currentLoopData = $pengguna; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $p): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
    <div class="bg-white rounded-xl shadow-sm p-6">
        <div class="flex items-start gap-4">
            <div class="w-12 h-12 rounded-full flex items-center justify-center text-white font-bold text-lg"
                style="background:#f59e0b">
                <?php echo e(strtoupper(substr($p->name, 0, 1))); ?>

            </div>
            <div class="flex-1 min-w-0">
                <div class="font-bold text-gray-800 truncate"><?php echo e($p->name); ?></div>
                <div class="text-sm text-gray-500"><?php echo e($p->jabatan ?? 'Tiada jabatan'); ?></div>
                <div class="text-xs text-gray-400 truncate"><?php echo e($p->email); ?></div>
            </div>
        </div>
        <div class="mt-4 flex items-center justify-between">
            <span class="text-xs font-semibold px-2 py-1 rounded-full
                <?php echo e($p->peranan === 'pentadbir_sistem' ? 'bg-red-100 text-red-700' :
                   ($p->peranan === 'urus_setia' ? 'bg-amber-100 text-amber-700' : 'bg-green-100 text-green-700')); ?>">
                <?php echo e($p->label_peranan); ?>

            </span>
            <div class="flex gap-2">
                <button onclick="openEdit(<?php echo e($p->id); ?>, '<?php echo e(addslashes($p->name)); ?>', '<?php echo e($p->jabatan); ?>', '<?php echo e($p->peranan); ?>', <?php echo e($p->aktif ? 'true' : 'false'); ?>)"
                    class="text-amber-500 text-xs hover:underline">
                    <i class="fa-solid fa-pen"></i> Edit
                </button>
                <button onclick="openReset(<?php echo e($p->id); ?>, '<?php echo e(addslashes($p->name)); ?>')"
                    class="text-gray-400 text-xs hover:text-gray-600">
                    <i class="fa-solid fa-key"></i> Tukar
                </button>
            </div>
        </div>
        <?php if(!$p->aktif): ?>
        <div class="mt-2 text-xs text-red-500 flex items-center gap-1">
            <i class="fa-solid fa-ban"></i> Akaun dinyahaktifkan
        </div>
        <?php endif; ?>
    </div>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</div>


<div class="bg-white rounded-xl shadow-sm p-6">
    <h2 class="font-bold text-gray-800 mb-4">Keterangan Peranan</h2>
    <div class="space-y-3">
        <div class="flex items-start gap-3">
            <span class="text-xs font-semibold px-2 py-1 rounded-full bg-red-100 text-red-700 flex-shrink-0">Pentadbir Sistem</span>
            <span class="text-sm text-gray-600">Akses penuh kepada semua fungsi termasuk pengurusan pengguna, bilik, dan tetapan sistem.</span>
        </div>
        <div class="flex items-start gap-3">
            <span class="text-xs font-semibold px-2 py-1 rounded-full bg-amber-100 text-amber-700 flex-shrink-0">Urus Setia</span>
            <span class="text-sm text-gray-600">Boleh meluluskan atau menolak permohonan tempahan, dan menguruskan mesyuarat.</span>
        </div>
        <div class="flex items-start gap-3">
            <span class="text-xs font-semibold px-2 py-1 rounded-full bg-green-100 text-green-700 flex-shrink-0">Staf</span>
            <span class="text-sm text-gray-600">Boleh membuat tempahan bilik mesyuarat sahaja.</span>
        </div>
    </div>
</div>


<div id="modal-tambah" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/50">
    <div class="bg-white rounded-xl shadow-xl p-6 w-full max-w-lg mx-4">
        <h3 class="font-bold text-gray-800 text-lg mb-5">Tambah Pengguna Baru</h3>
        <form method="POST" action="<?php echo e(route('pengguna.store')); ?>">
            <?php echo csrf_field(); ?>
            <div class="space-y-4">
                <div>
                    <label class="form-label">Nama Penuh <span class="text-red-500">*</span></label>
                    <input type="text" name="name" class="form-input" placeholder="Nama penuh pengguna">
                </div>
                <div>
                    <label class="form-label">Emel <span class="text-red-500">*</span></label>
                    <input type="email" name="email" class="form-input" placeholder="emel@jabatan.gov.my">
                </div>
                <div>
                    <label class="form-label">Jabatan</label>
                    <input type="text" name="jabatan" class="form-input" placeholder="cth: Bahagian ICT">
                </div>
                <div>
                    <label class="form-label">Peranan <span class="text-red-500">*</span></label>
                    <select name="peranan" class="form-input">
                        <option value="staf">Staf</option>
                        <option value="urus_setia">Urus Setia</option>
                        <option value="pentadbir_sistem">Pentadbir Sistem</option>
                    </select>
                </div>
                <div>
                    <label class="form-label">Kata Laluan <span class="text-red-500">*</span></label>
                    <input type="password" name="password" class="form-input" placeholder="Sekurang-kurangnya 8 aksara">
                </div>
                <div>
                    <label class="form-label">Sahkan Kata Laluan <span class="text-red-500">*</span></label>
                    <input type="password" name="password_confirmation" class="form-input">
                </div>
            </div>
            <div class="flex gap-3 mt-6">
                <button type="submit" class="btn-primary flex-1 justify-center py-2.5">
                    <i class="fa-solid fa-user-plus"></i> Tambah
                </button>
                <button type="button" onclick="document.getElementById('modal-tambah').classList.add('hidden')"
                    class="btn-secondary flex-1 justify-center py-2.5">Batal</button>
            </div>
        </form>
    </div>
</div>


<div id="modal-edit" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/50">
    <div class="bg-white rounded-xl shadow-xl p-6 w-full max-w-md mx-4">
        <h3 class="font-bold text-gray-800 text-lg mb-5">Edit Pengguna</h3>
        <form id="form-edit" method="POST">
            <?php echo csrf_field(); ?> <?php echo method_field('PUT'); ?>
            <div class="space-y-4">
                <div>
                    <label class="form-label">Nama Penuh</label>
                    <input type="text" id="edit-name" name="name" class="form-input">
                </div>
                <div>
                    <label class="form-label">Jabatan</label>
                    <input type="text" id="edit-jabatan" name="jabatan" class="form-input">
                </div>
                <div>
                    <label class="form-label">Peranan</label>
                    <select id="edit-peranan" name="peranan" class="form-input">
                        <option value="staf">Staf</option>
                        <option value="urus_setia">Urus Setia</option>
                        <option value="pentadbir_sistem">Pentadbir Sistem</option>
                    </select>
                </div>
                <div>
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" id="edit-aktif" name="aktif" value="1" style="accent-color:#f59e0b">
                        <span class="text-sm font-semibold text-gray-700">Akaun Aktif</span>
                    </label>
                </div>
            </div>
            <div class="flex gap-3 mt-6">
                <button type="submit" class="btn-primary flex-1 justify-center py-2.5">Kemaskini</button>
                <button type="button" onclick="document.getElementById('modal-edit').classList.add('hidden')"
                    class="btn-secondary flex-1 justify-center py-2.5">Batal</button>
            </div>
        </form>
    </div>
</div>


<div id="modal-reset" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/50">
    <div class="bg-white rounded-xl shadow-xl p-6 w-full max-w-md mx-4">
        <h3 class="font-bold text-gray-800 text-lg mb-1">Tukar Kata Laluan</h3>
        <p id="reset-nama" class="text-gray-500 text-sm mb-5"></p>
        <form id="form-reset" method="POST">
            <?php echo csrf_field(); ?>
            <div class="space-y-4">
                <div>
                    <label class="form-label">Kata Laluan Baru</label>
                    <input type="password" name="password" class="form-input">
                </div>
                <div>
                    <label class="form-label">Sahkan Kata Laluan</label>
                    <input type="password" name="password_confirmation" class="form-input">
                </div>
            </div>
            <div class="flex gap-3 mt-6">
                <button type="submit" class="btn-primary flex-1 justify-center py-2.5">Tukar</button>
                <button type="button" onclick="document.getElementById('modal-reset').classList.add('hidden')"
                    class="btn-secondary flex-1 justify-center py-2.5">Batal</button>
            </div>
        </form>
    </div>
</div>

<?php $__env->startPush('scripts'); ?>
<script>
function openEdit(id, name, jabatan, peranan, aktif) {
    document.getElementById('form-edit').action = '/pengguna/' + id;
    document.getElementById('edit-name').value = name;
    document.getElementById('edit-jabatan').value = jabatan || '';
    document.getElementById('edit-peranan').value = peranan;
    document.getElementById('edit-aktif').checked = aktif;
    document.getElementById('modal-edit').classList.remove('hidden');
}
function openReset(id, name) {
    document.getElementById('reset-nama').textContent = name;
    document.getElementById('form-reset').action = '/pengguna/' + id + '/reset-password';
    document.getElementById('modal-reset').classList.remove('hidden');
}
</script>
<?php $__env->stopPush(); ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\ibook\resources\views/pengguna/index.blade.php ENDPATH**/ ?>