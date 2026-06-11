<?php
// views/laporan_keuangan.php

$nama_bulan = [
    1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April', 
    5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus', 
    9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
];

// Menentukan teks deskripsi periode pada judul dokumen laporan sesuai dengan filter aktif
$teks_periode = '';
if ($tipe_filter === 'hari') {
    $teks_periode = "Per Hari (" . date('d-m-Y', strtotime($tgl_pilihan)) . ")";
} elseif ($tipe_filter === 'minggu') {
    $tgl_awal = date('d-m-Y', strtotime($tgl_pilihan . ' - 6 days'));
    $teks_periode = "Per Minggu (" . $tgl_awal . " s/d " . date('d-m-Y', strtotime($tgl_pilihan)) . ")";
} elseif ($tipe_filter === 'tahun') {
    $teks_periode = "Per Tahun " . $tahun_pilihan;
} else {
    $teks_periode = "Per Bulan " . $nama_bulan[$bulan_pilihan] . " " . $tahun_pilihan;
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Keuangan <?= $teks_periode ?> - Ikhsan Jaya Motor</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        /* Gaya khusus saat cetak laporan ke kertas/PDF */
        @media print {
            body { background-color: #fff; }
            #sidebar, .md\:hidden, #filter-panel, .btn-print-action { display: none !important; }
            .main-content-panel { padding: 0 !important; width: 100% !important; }
            .shadow-md { box-shadow: none !important; border: none !important; }
        }
    </style>
</head>

<body class="bg-gray-100 flex flex-col md:flex-row min-h-screen">

    <?php 
    if (file_exists('views/includes/sidebar.php')) include 'views/includes/sidebar.php';
    elseif (file_exists('includes/sidebar.php')) include 'includes/sidebar.php';
    ?>

    <div class="w-full bg-gray-100 flex-1 p-8 main-content-panel">
        
        <div class="flex justify-between items-center mb-6">
            <div>
                <h1 class="text-3xl font-bold text-gray-800">Laporan Keuangan</h1>
                <p class="text-sm text-gray-500">Periode Akuntansi: <span class="font-bold text-blue-600 uppercase"><?= $teks_periode ?></span></p>
            </div>
            <button onclick="window.print()" class="btn-print-action bg-gray-800 hover:bg-gray-900 text-white font-bold py-2.5 px-5 rounded-lg shadow transition duration-150 flex items-center gap-2">
                <span>🖨️</span> Cetak Laporan
            </button>
        </div>

        <div id="filter-panel" class="bg-white p-6 rounded-lg shadow-md mb-8 border-l-4 border-blue-500">
            <form action="index.php" method="GET" class="space-y-4">
                <input type="hidden" name="page" value="laporan">
                
                <div>
                    <label class="block text-gray-700 text-xs font-bold mb-2 uppercase tracking-wide">Metode Rekapitulasi</label>
                    <div class="flex flex-wrap gap-5">
                        <label class="inline-flex items-center cursor-pointer">
                            <input type="radio" name="tipe" value="hari" <?= $tipe_filter === 'hari' ? 'checked' : '' ?> onclick="switchFilterView('hari')" class="text-blue-600 focus:ring-blue-500 h-4 w-4">
                            <span class="ml-2 text-sm text-gray-700 font-medium">Per Hari</span>
                        </label>
                        <label class="inline-flex items-center cursor-pointer">
                            <input type="radio" name="tipe" value="minggu" <?= $tipe_filter === 'minggu' ? 'checked' : '' ?> onclick="switchFilterView('minggu')" class="text-blue-600 focus:ring-blue-500 h-4 w-4">
                            <span class="ml-2 text-sm text-gray-700 font-medium">7 Hari (Mingguan)</span>
                        </label>
                        <label class="inline-flex items-center cursor-pointer">
                            <input type="radio" name="tipe" value="bulan" <?= $tipe_filter === 'bulan' ? 'checked' : '' ?> onclick="switchFilterView('bulan')" class="text-blue-600 focus:ring-blue-500 h-4 w-4">
                            <span class="ml-2 text-sm text-gray-700 font-medium">Per Bulan</span>
                        </label>
                        <label class="inline-flex items-center cursor-pointer">
                            <input type="radio" name="tipe" value="tahun" <?= $tipe_filter === 'tahun' ? 'checked' : '' ?> onclick="switchFilterView('tahun')" class="text-blue-600 focus:ring-blue-500 h-4 w-4">
                            <span class="ml-2 text-sm text-gray-700 font-medium">Per Tahun</span>
                        </label>
                    </div>
                </div>

                <div class="flex flex-wrap items-end gap-4 border-t pt-4">
                    
                    <div id="box_tanggal" class="w-full sm:w-48 hidden">
                        <label class="block text-gray-600 text-xs font-bold mb-1 uppercase">Pilih Tanggal</label>
                        <input type="date" name="tanggal" value="<?= $tgl_pilihan ?>" class="w-full border border-gray-300 rounded py-2 px-3 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 text-gray-700 bg-white">
                    </div>

                    <div id="box_bulan" class="w-full sm:w-48 hidden">
                        <label class="block text-gray-600 text-xs font-bold mb-1 uppercase">Pilih Bulan</label>
                        <select name="bulan" class="w-full border border-gray-300 rounded py-2 px-3 bg-white text-sm text-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <?php foreach ($nama_bulan as $num => $name): ?>
                                <option value="<?= $num ?>" <?= $num == $bulan_pilihan ? 'selected' : '' ?>><?= $name ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div id="box_tahun" class="w-full sm:w-36 hidden">
                        <label class="block text-gray-600 text-xs font-bold mb-1 uppercase">Pilih Tahun</label>
                        <select name="tahun" class="w-full border border-gray-300 rounded py-2 px-3 bg-white text-sm text-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <?php 
                            $tahun_sekarang = (int)date('Y');
                            for ($i = $tahun_sekarang - 3; $i <= $tahun_sekarang + 2; $i++): 
                            ?>
                                <option value="<?= $i ?>" <?= $i == $tahun_pilihan ? 'selected' : '' ?>><?= $i ?></option>
                            <?php endfor; ?>
                        </select>
                    </div>

                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-6 rounded text-sm shadow transition duration-150 h-[38px]">
                        Tampilkan Laporan
                    </button>
                </div>
            </form>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <div class="bg-white rounded-lg shadow-md p-6 border-t-4 border-blue-500">
                <h3 class="text-gray-400 text-xs font-bold uppercase tracking-wider mb-1">Total Pemasukan (Omzet)</h3>
                <p class="text-2xl font-black text-blue-600">Rp <?= number_format($data_laporan['pemasukan'], 0, ',', '.') ?></p>
            </div>
            <div class="bg-white rounded-lg shadow-md p-6 border-t-4 border-red-500">
                <h3 class="text-gray-400 text-xs font-bold uppercase tracking-wider mb-1">Total Pengeluaran Operasional</h3>
                <p class="text-2xl font-black text-red-600">Rp <?= number_format($data_laporan['pengeluaran'], 0, ',', '.') ?></p>
            </div>
            <div class="bg-white rounded-lg shadow-md p-6 border-t-4 border-green-500">
                <h3 class="text-gray-400 text-xs font-bold uppercase tracking-wider mb-1">Laba Bersih</h3>
                <p class="text-2xl font-black text-green-600">Rp <?= number_format($data_laporan['laba_bersih'], 0, ',', '.') ?></p>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-md overflow-hidden border border-gray-200">
            <div class="p-5 border-b border-gray-100 bg-gray-50">
                <h2 class="text-lg font-bold text-gray-700">Rincian Buku Kas Masuk Penjualan</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full bg-white">
                    <thead class="bg-gray-800 text-white uppercase text-xs font-bold tracking-wider">
                        <tr>
                            <th class="py-3 px-5 text-center w-12">No</th>
                            <th class="py-3 px-5 text-left">Tanggal</th>
                            <th class="py-3 px-5 text-left">Nomor Nota</th>
                            <th class="py-3 px-5 text-left">Nama Pelanggan</th>
                            <th class="py-3 px-5 text-left">Plat Nomor</th>
                            <th class="py-3 px-5 text-left">Penanggung Jawab (Kasir)</th>
                            <th class="py-3 px-5 text-right">Total Transaksi</th>
                        </tr>
                    </thead>
                    <tbody class="text-gray-600 text-sm font-light divide-y divide-gray-100">
                        <?php if (empty($data_laporan['detail'])): ?>
                            <tr>
                                <td colspan="7" class="py-10 text-center text-gray-400 italic bg-gray-50">Tidak ada riwayat transaksi komersial pada periode ini.</td>
                            </tr>
                        <?php else: ?>
                            <?php $no = 1; foreach ($data_laporan['detail'] as $row): ?>
                                <tr class="hover:bg-gray-50 transition duration-100">
                                    <td class="py-3.5 px-5 text-center font-medium"><?= $no++ ?></td>
                                    <td class="py-3.5 px-5 text-left"><?= date('d-m-Y H:i', strtotime($row['tanggal'])) ?></td>
                                    <td class="py-3.5 px-5 text-left font-mono font-bold text-gray-700"><?= htmlspecialchars($row['kode_transaksi']) ?></td>
                                    <td class="py-3.5 px-5 text-left font-semibold"><?= htmlspecialchars($row['nama_pelanggan']) ?></td>
                                    <td class="py-3.5 px-5 text-left font-mono uppercase text-xs"><?= !empty($row['plat_nomor']) ? htmlspecialchars($row['plat_nomor']) : '-' ?></td>
                                    <td class="py-3.5 px-5 text-left capitalize"><?= htmlspecialchars($row['nama_kasir'] ?? 'Sistem') ?></td>
                                    <td class="py-3.5 px-5 text-right font-bold text-blue-600">Rp <?= number_format($row['total_bayar'], 0, ',', '.') ?></td>
                                </tr>
                            <?php endforeach; ?>
                            <tr class="bg-gray-800 text-white font-bold text-sm">
                                <td colspan="6" class="py-4 px-5 text-right uppercase tracking-wider">Total Akumulasi Buku Kas:</td>
                                <td class="py-4 px-5 text-right text-base text-yellow-400">Rp <?= number_format($data_laporan['pemasukan'], 0, ',', '.') ?></td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

    </div>

    <script>
        function switchFilterView(tipe) {
            const boxTanggal = document.getElementById('box_tanggal');
            const boxBulan = document.getElementById('box_bulan');
            const boxTahun = document.getElementById('box_tahun');

            // Sembunyikan semua kontainer input terlebih dahulu
            boxTanggal.classList.add('hidden');
            boxBulan.classList.add('hidden');
            boxTahun.classList.add('hidden');

            // Tampilkan elemen input yang relevan berdasarkan tipe filter akuntansi terpilih
            if (tipe === 'hari' || tipe === 'minggu') {
                boxTanggal.classList.remove('hidden');
            } else if (tipe === 'bulan') {
                boxBulan.classList.remove('hidden');
                boxTahun.classList.remove('hidden');
            } else if (tipe === 'tahun') {
                boxTahun.classList.remove('hidden');
            }
        }

        // Sinkronisasi form pertama kali saat halaman dimuat ke browser
        window.addEventListener('DOMContentLoaded', () => {
            switchFilterView('<?= $tipe_filter ?>');
        });
    </script>
</body>
</html>