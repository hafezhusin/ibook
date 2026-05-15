

<?php $__env->startSection('title', 'Laporan'); ?>

<?php $__env->startPush('styles'); ?>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>
<div class="flex items-center justify-between mb-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-800">Laporan</h1>
        <p class="text-gray-500 text-sm mt-1">Ringkasan penggunaan bilik dan tempahan</p>
    </div>
    <form method="GET" class="flex items-center gap-3">
        <select name="tahun" class="form-input w-auto text-sm" onchange="this.form.submit()">
            <?php $__currentLoopData = $senaraiTahun; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $t): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <option value="<?php echo e($t); ?>" <?php echo e($tahun == $t ? 'selected' : ''); ?>><?php echo e($t); ?></option>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </select>
    </form>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
    
    <div class="bg-white rounded-xl shadow-sm p-6">
        <h2 class="font-bold text-gray-800 mb-5">Tempahan Mengikut Bulan</h2>
        <canvas id="chartBulan" height="200"></canvas>
    </div>

    
    <div class="bg-white rounded-xl shadow-sm p-6">
        <h2 class="font-bold text-gray-800 mb-5">Tempahan Mengikut Kategori</h2>
        <canvas id="chartKategori" height="200"></canvas>
    </div>
</div>


<div class="bg-white rounded-xl shadow-sm overflow-hidden">
    <div class="p-6 border-b border-gray-100">
        <h2 class="font-bold text-gray-800">Ringkasan Penggunaan Bilik (<?php echo e($tahun); ?>)</h2>
    </div>
    <table class="table w-full">
        <thead class="table-header">
            <tr>
                <th>Bilik</th>
                <th>Kapasiti</th>
                <th>Jumlah Tempahan</th>
                <th>Jam Digunakan</th>
                <th>% Penggunaan</th>
            </tr>
        </thead>
        <tbody>
            <?php $__empty_1 = true; $__currentLoopData = $bilik; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $b): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <tr>
                <td class="font-semibold"><?php echo e($b['nama']); ?></td>
                <td><?php echo e($b['kapasiti']); ?> orang</td>
                <td><?php echo e($b['jumlah_tempahan']); ?></td>
                <td><?php echo e($b['jam_digunakan']); ?> jam</td>
                <td>
                    <div class="flex items-center gap-3">
                        <div class="progress-bar flex-1">
                            <div class="progress-fill" style="width:<?php echo e(min($b['peratusan'], 100)); ?>%"></div>
                        </div>
                        <span class="text-sm font-semibold w-10 text-right"><?php echo e($b['peratusan']); ?>%</span>
                    </div>
                </td>
            </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <tr><td colspan="5" class="text-center py-8 text-gray-400">Tiada data</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
const bulanLabel = ['Jan','Feb','Mac','Apr','Mei','Jun','Jul','Ogos','Sep','Okt','Nov','Dis'];
const dataBulan = <?php echo json_encode($dataBulan, 15, 512) ?>;

new Chart(document.getElementById('chartBulan'), {
    type: 'bar',
    data: {
        labels: bulanLabel,
        datasets: [{
            label: 'Tempahan Diluluskan',
            data: dataBulan,
            backgroundColor: 'rgba(245,158,11,0.8)',
            borderColor: '#f59e0b',
            borderWidth: 1,
            borderRadius: 4
        }]
    },
    options: {
        responsive: true,
        plugins: { legend: { display: false } },
        scales: { y: { beginAtZero: true, ticks: { precision: 0 } } }
    }
});

const kategoriLabel = <?php echo json_encode($mengikutKategori->pluck('kategori'), 15, 512) ?>;
const kategoriData = <?php echo json_encode($mengikutKategori->pluck('jumlah'), 15, 512) ?>;
const colors = ['#f59e0b','#3b82f6','#10b981','#8b5cf6','#ef4444','#06b6d4'];

new Chart(document.getElementById('chartKategori'), {
    type: 'doughnut',
    data: {
        labels: kategoriLabel,
        datasets: [{
            data: kategoriData,
            backgroundColor: colors,
            borderWidth: 2,
            borderColor: '#fff'
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: { position: 'bottom' }
        }
    }
});
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\ibook\resources\views/laporan/index.blade.php ENDPATH**/ ?>