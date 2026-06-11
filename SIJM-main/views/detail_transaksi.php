<?php
// views/detail_transaksi.php

require_once 'controllers/AuthController.php';
require_once 'controllers/TransaksiController.php';
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Proteksi Halaman
$auth = new AuthController();
$auth->checkAuth();

$id_transaksi = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$trxCtrl = new TransaksiController();
$data = $trxCtrl->detail($id_transaksi);

// Jika transaksi tidak ditemukan, kembalikan ke daftar riwayat lewat router utama index.php
if (!$data || !$data['transaksi']) {
    header("Location: index.php?page=riwayat_transaksi");
    exit;
}

$transaksi = $data['transaksi'];
$items = $data['detail'];

// Standarisasi string status dari database
$status_aktif = isset($transaksi['status']) ? strtolower($transaksi['status']) : 'menunggu';

// Mengambil data level hak akses (role) user langsung dari session login
$role_user = isset($_SESSION['role']) ? strtolower($_SESSION['role']) : 'kasir';
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Transaksi #<?= htmlspecialchars($transaksi['kode_transaksi']); ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        /* Gaya khusus saat cetak nota ke kertas/PDF */
        @media print {
            body { background-color: #fff; }
            #sidebar, .md\:hidden, .btn-action-panel, .status-changer-panel { display: none !important; }
            .main-content-panel { padding: 0 !important; width: 100% !important; }
            .shadow-lg { box-shadow: none !important; border: none !important; }
            .card-container { max-w: 100% !important; width: 100% !important; border: none !important; padding: 0 !important; }
        }
    </style>
</head>

<body class="bg-gray-100 flex flex-col md:flex-row min-h-screen">

    <?php 
    if (file_exists('views/includes/sidebar.php')) {
        include 'views/includes/sidebar.php';
    } elseif (file_exists('includes/sidebar.php')) {
        include 'includes/sidebar.php';
    } 
    ?>

    <div class="w-full bg-gray-100 flex-1 p-8 flex justify-center items-start main-content-panel">
        <div class="w-full max-w-2xl bg-white p-8 rounded-xl shadow-lg border border-gray-200 card-container">
            
            <div class="text-center border-b-2 border-dashed border-gray-300 pb-6 mb-6">
                <h1 class="text-2xl font-black text-gray-800 tracking-wide uppercase">Ikhsan Jaya Motor</h1>
                <p class="text-sm text-gray-500">Nota Rincian Transaksi Konsumen</p>
            </div>

            <div class="status-changer-panel bg-blue-50 border border-blue-200 p-4 rounded-xl mb-6 flex flex-wrap items-center justify-between gap-4">
                <div>
                    <span class="text-xs font-bold text-gray-400 uppercase block mb-1">Status Pengerjaan Saat Ini:</span>
                    <?php if ($status_aktif === 'selesai'): ?>
                        <span class="inline-block bg-green-100 text-green-800 text-xs font-bold px-3 py-1 rounded-full uppercase border border-green-200">✅ Selesai / Lunas</span>
                    <?php elseif ($status_aktif === 'sedang dijemur'): ?>
                        <span class="inline-block bg-orange-100 text-orange-800 text-xs font-bold px-3 py-1 rounded-full uppercase border border-orange-200">🧺 Sedang Dijemur</span>
                    <?php elseif ($status_aktif === 'lagi dicuci'): ?>
                        <span class="inline-block bg-yellow-100 text-yellow-800 text-xs font-bold px-3 py-1 rounded-full uppercase border border-yellow-200">🧼 Lagi Dicuci</span>
                    <?php else: ?>
                        <span class="inline-block bg-blue-100 text-blue-800 text-xs font-bold px-3 py-1 rounded-full uppercase border border-blue-200">📥 Antrean / Menunggu</span>
                    <?php endif; ?>
                </div>
                
                <?php if ($role_user === 'admin' || $role_user === 'kasir'): ?>
                    <form action="index.php?page=update_status" method="POST" class="flex items-center gap-2">
                        <input type="hidden" name="id_transaksi" value="<?= $id_transaksi; ?>">
                        <select name="status_baru" class="border border-gray-300 rounded py-1.5 px-3 bg-white text-xs font-medium text-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="menunggu" <?= $status_aktif === 'menunggu' ? 'selected' : ''; ?>>Antrean / Menunggu</option>
                            <option value="lagi dicuci" <?= $status_aktif === 'lagi dicuci' ? 'selected' : ''; ?>>Lagi Dicuci</option>
                            <option value="sedang dijemur" <?= $status_aktif === 'sedang dijemur' ? 'selected' : ''; ?>>Sedang Dijemur</option>
                            <option value="selesai" <?= $status_aktif === 'selesai' ? 'selected' : ''; ?>>Selesai / Lunas</option>
                        </select>
                        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-1.5 px-4 rounded text-xs shadow transition">
                            Perbarui
                        </button>
                    </form>
                <?php endif; ?>
            </div>

            <div class="grid grid-cols-2 gap-4 text-sm text-gray-600 mb-6 bg-gray-50 p-4 rounded-lg">
                <div>
                    <p class="mb-1"><strong>No. Nota:</strong> <span class="font-mono text-gray-800 font-bold"><?= htmlspecialchars($transaksi['kode_transaksi']); ?></span></p>
                    <p><strong>Tanggal:</strong> <?= date('d-m-Y H:i', strtotime($transaksi['tanggal'])); ?></p>
                </div>
                <div class="text-right">
                    <p class="mb-1"><strong>Pelanggan:</strong> <span class="text-gray-800 font-semibold"><?= htmlspecialchars($transaksi['nama_pelanggan']); ?></span></p>
                    <p class="mb-1"><strong>Plat Nomor:</strong> <span class="uppercase font-mono text-gray-800 font-semibold"><?= !empty($transaksi['plat_nomor']) ? htmlspecialchars($transaksi['plat_nomor']) : '-'; ?></span></p>
                    <p><strong>Kasir:</strong> <?= htmlspecialchars($transaksi['nama_kasir'] ?? 'Sistem'); ?></p>
                </div>
            </div>

            <h2 class="text-lg font-bold text-gray-700 mb-3">Daftar Item / Jasa Layanan</h2>
            <div class="border rounded-lg overflow-hidden mb-6">
                <table class="w-full text-sm">
                    <thead class="bg-gray-100 text-gray-600 font-bold">
                        <tr class="border-b">
                            <th class="py-3 px-4 text-left">Nama Item</th>
                            <th class="py-3 px-4 text-center">Qty</th>
                            <th class="py-3 px-4 text-right">Harga</th>
                            <th class="py-3 px-4 text-right">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody class="text-gray-600 divide-y">
                        <?php foreach ($items as $item): ?>
                            <tr>
                                <td class="py-3 px-4 font-medium text-gray-800">
                                    <?= htmlspecialchars($item['nama_item']); ?>
                                    <?php if ($item['jenis_item'] == 'barang'): ?>
                                        <span class="text-[10px] bg-blue-100 text-blue-700 px-1.5 py-0.5 rounded ml-2 uppercase font-semibold">Barang</span>
                                    <?php elseif ($item['jenis_item'] == 'layanan'): ?>
                                        <span class="text-[10px] bg-indigo-100 text-indigo-700 px-1.5 py-0.5 rounded ml-2 uppercase font-semibold">Jasa</span>
                                    <?php else: ?>
                                        <span class="text-[10px] bg-orange-100 text-orange-700 px-1.5 py-0.5 rounded ml-2 uppercase font-semibold">Manual</span>
                                    <?php endif; ?>
                                </td>
                                <td class="py-3 px-4 text-center font-bold text-gray-700"><?= $item['jumlah']; ?></td>
                                <td class="py-3 px-4 text-right">Rp <?= number_format($item['harga_satuan'], 0, ',', '.'); ?></td>
                                <td class="py-3 px-4 text-right font-bold text-gray-800">Rp <?= number_format($item['subtotal'], 0, ',', '.'); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <thead class="bg-gray-50 font-bold text-base text-gray-800">
                        <tr>
                            <td colspan="3" class="py-4 px-4 text-right">Total Keseluruhan:</td>
                            <td class="py-4 px-4 text-right text-xl text-blue-700">Rp <?= number_format($transaksi['total_bayar'], 0, ',', '.'); ?></td>
                        </tr>
                    </thead>
                </table>
            </div>

            <div class="mt-8 flex flex-col items-center justify-center border-t border-dashed border-gray-300 pt-6 text-center">
                <p class="text-xs font-bold text-gray-600 mb-3">Pindai QR Code untuk Memeriksa Status Nota</p>
                
                <?php
                $base_url = "http://localhost/SIJM-main/index.php";
                $url_tracking = $base_url . "?page=cek_status&nota=" . trim($transaksi['kode_transaksi']);
                $api_qr_code = "https://api.qrserver.com/v1/create-qr-code/?size=140x140&data=" . urlencode($url_tracking);
                ?>
                
                <div class="p-2 bg-white border border-gray-200 rounded-xl shadow-sm">
                    <img src="<?= $api_qr_code; ?>" alt="QR Code Tracking" class="w-[140px] h-[140px]">
                </div>
                <p class="text-[10px] font-mono font-bold text-gray-400 mt-2"><?= htmlspecialchars($transaksi['kode_transaksi']); ?></p>
            </div>

            <div class="flex justify-between items-center mt-6 pt-4 border-t btn-action-panel">
                <a href="index.php?page=riwayat_transaksi" class="text-sm font-semibold text-blue-600 hover:text-blue-800 flex items-center transition">
                    ← Kembali ke Daftar Riwayat
                </a>
                <button onclick="window.print()" class="bg-gray-800 hover:bg-gray-900 text-white text-sm font-bold py-2 px-5 rounded-lg shadow transition">
                    Cetak Nota / Print
                </button>
            </div>

        </div>
    </div>

</body>
</html>