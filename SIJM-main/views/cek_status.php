<?php
// views/cek_status.php
// Berkas interface pelacakan status nota realtime yang diakses oleh pelanggan publik/umum
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Status Transaksi #<?= htmlspecialchars($kode_nota) ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-100 min-h-screen flex items-center justify-center p-4">

    <div class="max-w-md w-full bg-white rounded-2xl shadow-xl overflow-hidden border border-gray-200">
        
        <div class="bg-blue-800 p-6 text-white text-center">
            <h1 class="text-xl font-bold tracking-wide">IKHSAN JAYA MOTOR</h1>
            <p class="text-xs text-blue-200 mt-1">Sistem Pelacakan Nota & Status Riwayat</p>
        </div>

        <?php if (!$transaksi): ?>
            <div class="p-8 text-center space-y-3">
                <span class="text-5xl block">🔍❌</span>
                <h2 class="text-lg font-bold text-gray-700">Nota Tidak Ditemukan</h2>
                <p class="text-sm text-gray-400">Pastikan tautan nomor nota <span class="font-mono text-red-500 font-bold"><?= htmlspecialchars($kode_nota) ?></span> yang Anda pindai sudah sesuai.</p>
                <div class="pt-4">
                    <a href="index.php" class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-2 rounded-lg text-xs font-bold shadow transition">
                        Kembali ke Utama
                    </a>
                </div>
            </div>
        <?php else: ?>
            <div class="p-6 space-y-6">
                
                <div class="text-center bg-gray-50 p-4 rounded-xl border border-gray-100">
                    <span class="text-xs font-bold text-gray-400 uppercase block mb-1">Status Pengerjaan / Cucian</span>
                    <?php 
                    // Menormalisasi string status pengerjaan dari database agar serasi dengan percabangan komponen
                    $status_db = isset($transaksi['status']) ? strtolower($transaksi['status']) : 'menunggu'; 
                    
                    if ($status_db === 'selesai'): ?>
                        <span class="inline-block bg-green-100 text-green-800 text-sm font-extrabold px-4 py-1.5 rounded-full uppercase border border-green-200">✅ Selesai / Lunas</span>
                    <?php elseif ($status_db === 'sedang dijemur'): ?>
                        <span class="inline-block bg-orange-100 text-orange-800 text-sm font-extrabold px-4 py-1.5 rounded-full uppercase border border-orange-200">🧺 Sedang Dijemur</span>
                    <?php elseif ($status_db === 'lagi dicuci'): ?>
                        <span class="inline-block bg-yellow-100 text-yellow-800 text-sm font-extrabold px-4 py-1.5 rounded-full uppercase border border-yellow-200">🧼 Lagi Dicuci</span>
                    <?php else: ?>
                        <span class="inline-block bg-blue-100 text-blue-800 text-sm font-extrabold px-4 py-1.5 rounded-full uppercase border border-blue-200">📥 Antrean / Menunggu</span>
                    <?php endif; ?>
                </div>

                <div class="grid grid-cols-2 gap-4 text-xs border-b pb-4">
                    <div>
                        <p class="text-gray-400 font-medium">Nomor Struk</p>
                        <p class="font-mono font-bold text-gray-800 text-sm"><?= htmlspecialchars($transaksi['kode_transaksi']) ?></p>
                    </div>
                    <div>
                        <p class="text-gray-400 font-medium">Tanggal Waktu</p>
                        <p class="font-bold text-gray-800"><?= date('d-m-Y H:i', strtotime($transaksi['tanggal'])) ?></p>
                    </div>
                    <div>
                        <p class="text-gray-400 font-medium">Nama Pelanggan</p>
                        <p class="font-bold text-gray-800 text-sm capitalize"><?= htmlspecialchars($transaksi['nama_pelanggan']) ?></p>
                    </div>
                    <div>
                        <p class="text-gray-400 font-medium">Nomor Plat</p>
                        <p class="font-mono font-bold text-gray-800 text-sm uppercase"><?= !empty($transaksi['plat_nomor']) ? htmlspecialchars($transaksi['plat_nomor']) : '-' ?></p>
                    </div>
                </div>

                <div>
                    <h3 class="text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">Item Rincian Servis & Jasa</h3>
                    <div class="space-y-2 max-h-48 overflow-y-auto pr-1">
                        <?php if (empty($items)): ?>
                            <p class="text-xs text-gray-400 italic text-center py-2 bg-gray-50 rounded">Tidak ada detail item pada transaksi ini.</p>
                        <?php else: ?>
                            <?php foreach ($items as $item): ?>
                                <div class="flex justify-between items-center text-xs bg-gray-50 p-2.5 rounded border border-gray-100">
                                    <div>
                                        <p class="font-semibold text-gray-800"><?= htmlspecialchars($item['nama_item']) ?></p>
                                        <p class="text-gray-400 text-[10px]"><?= $item['jumlah'] ?> x Rp <?= number_format($item['harga_satuan'], 0, ',', '.') ?></p>
                                    </div>
                                    <span class="font-bold text-gray-700">Rp <?= number_format($item['subtotal'], 0, ',', '.') ?></span>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="pt-4 border-t border-dashed flex justify-between items-center">
                    <span class="text-sm font-bold text-gray-600">Total Biaya Akhir:</span>
                    <span class="text-2xl font-black text-blue-700">Rp <?= number_format($transaksi['total_bayar'], 0, ',', '.') ?></span>
                </div>

                <p class="text-center text-[10px] text-gray-400 pt-4">Terima kasih telah mempercayakan kendaraan Anda di Ikhsan Jaya Motor.</p>

            </div>
        <?php endif; ?>

    </div>

</body>
</html>