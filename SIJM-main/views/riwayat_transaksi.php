<?php
// views/riwayat_transaksi.php

require_once 'controllers/AuthController.php';
require_once 'controllers/TransaksiController.php';

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Proteksi Halaman (Pastikan user sudah login)
$auth = new AuthController();
$auth->checkAuth();

// Inisialisasi Controller & Ambil Data Riwayat
$trxCtrl = new TransaksiController();
$riwayat = $trxCtrl->riwayat();

// Mengambil data level hak akses (role) user langsung dari session login
$role_user = isset($_SESSION['role']) ? strtolower($_SESSION['role']) : 'kasir';
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Riwayat Transaksi - Ikhsan Jaya Motor</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-100 flex flex-col md:flex-row min-h-screen">

    <?php 
    if (file_exists('views/includes/sidebar.php')) {
        include 'views/includes/sidebar.php';
    } elseif (file_exists('includes/sidebar.php')) {
        include 'includes/sidebar.php';
    } 
    ?>

    <div class="w-full bg-gray-100 flex-1 p-8">
        <h1 class="text-3xl font-bold text-gray-800 mb-6">Riwayat Transaksi Penjualan</h1>

        <div class="bg-white p-6 rounded-lg shadow-md border-t-4 border-blue-500">
            <div class="overflow-x-auto">
                <table class="min-w-full bg-white">
                    <thead class="bg-gray-800 text-white uppercase text-sm leading-normal">
                        <tr>
                            <th class="py-3 px-6 text-center">No</th>
                            <th class="py-3 px-6 text-left">Kode Transaksi</th>
                            <th class="py-3 px-6 text-center">Tanggal</th>
                            <th class="py-3 px-6 text-left">Pelanggan / Plat</th>
                            <th class="py-3 px-6 text-right">Total Bayar</th>
                            <th class="py-3 px-6 text-center">Status</th>
                            <th class="py-3 px-6 text-left">Kasir</th>
                            <th class="py-3 px-6 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="text-gray-600 text-sm font-light">
                        <?php if (empty($riwayat)): ?>
                            <tr>
                                <td colspan="8" class="py-8 text-center text-gray-400 italic bg-gray-50">Belum ada riwayat transaksi yang tercatat.</td>
                            </tr>
                        <?php else: ?>
                            <?php $no = 1; foreach ($riwayat as $row): ?>
                                <tr class="border-b border-gray-200 hover:bg-gray-50 transition">
                                    <td class="py-4 px-6 text-center font-medium"><?= $no++; ?></td>
                                    <td class="py-4 px-6 text-left font-bold text-gray-800"><?= htmlspecialchars($row['kode_transaksi']); ?></td>
                                    <td class="py-4 px-6 text-center"><?= date('d-m-Y H:i', strtotime($row['tanggal'])); ?></td>
                                    <td class="py-4 px-6 text-left">
                                        <div class="font-semibold"><?= htmlspecialchars($row['nama_pelanggan']); ?></div>
                                        <div class="text-xs text-gray-400 font-mono uppercase"><?= !empty($row['plat_nomor']) ? htmlspecialchars($row['plat_nomor']) : '-'; ?></div>
                                    </td>
                                    <td class="py-4 px-6 text-right font-bold text-blue-600">Rp <?= number_format($row['total_bayar'], 0, ',', '.'); ?></td>
                                    
                                    <td class="py-4 px-6 text-center">
                                        <?php 
                                        $status_row = isset($row['status']) ? strtolower($row['status']) : 'menunggu';
                                        
                                        if ($status_row === 'selesai'): ?>
                                            <span class="bg-green-100 text-green-800 text-xs px-2.5 py-1 rounded-full font-bold uppercase border border-green-200">Selesai</span>
                                        <?php elseif ($status_row === 'sedang dijemur'): ?>
                                            <span class="bg-orange-100 text-orange-800 text-xs px-2.5 py-1 rounded-full font-bold uppercase border border-orange-200">Sedang Dijemur</span>
                                        <?php elseif ($status_row === 'lagi dicuci'): ?>
                                            <span class="bg-yellow-100 text-yellow-800 text-xs px-2.5 py-1 rounded-full font-bold uppercase border border-yellow-200">Lagi Dicuci</span>
                                        <?php elseif ($status_row === 'menunggu'): ?>
                                            <span class="bg-blue-100 text-blue-800 text-xs px-2.5 py-1 rounded-full font-bold uppercase border border-blue-200">Menunggu</span>
                                        <?php else: ?>
                                            <span class="bg-red-100 text-red-800 text-xs px-2.5 py-1 rounded-full font-bold uppercase border border-red-200"><?= htmlspecialchars($status_row); ?></span>
                                        <?php endif; ?>
                                    </td>
                                    
                                    <td class="py-4 px-6 text-left font-medium"><?= htmlspecialchars($row['nama_kasir'] ?? 'Sistem'); ?></td>
                                    <td class="py-4 px-6 text-center">
                                        <div class="flex justify-center items-center gap-2">
                                            
                                            <a href="index.php?page=detail_transaksi&id=<?= $row['id']; ?>" 
                                               class="bg-blue-500 hover:bg-blue-600 text-white font-semibold rounded-md shadow-md transition duration-150 flex flex-col items-center justify-center w-14 h-12 gap-0.5" 
                                               title="Lihat Detail">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                                </svg>
                                                <span class="text-[10px] tracking-wide leading-none">Detail</span>
                                            </a>

                                            <?php if ($role_user === 'admin'): ?>
                                                <a href="index.php?page=edit_transaksi&id=<?= $row['id']; ?>" 
                                                   class="bg-yellow-500 hover:bg-yellow-600 text-white font-semibold rounded-md shadow-md transition duration-150 flex flex-col items-center justify-center w-14 h-12 gap-0.5" 
                                                   title="Edit Transaksi">
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                                    </svg>
                                                    <span class="text-[10px] tracking-wide leading-none">Edit</span>
                                                </a>

                                                <a href="index.php?page=hapus_transaksi&id=<?= $row['id']; ?>" 
                                                   onclick="return confirm('Apakah Anda yakin ingin menghapus data transaksi ini secara permanen?');"
                                                   class="bg-red-500 hover:bg-red-600 text-white font-semibold rounded-md shadow-md transition duration-150 flex flex-col items-center justify-center w-14 h-12 gap-0.5" 
                                                   title="Hapus Transaksi">
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                                    </svg>
                                                    <span class="text-[10px] tracking-wide leading-none">Hapus</span>
                                                </a>
                                            <?php endif; ?>

                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</body>
</html>