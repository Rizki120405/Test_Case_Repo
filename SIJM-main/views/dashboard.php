<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SIM Ikhsan Jaya Motor - Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-100 flex flex-col md:flex-row min-h-screen">

    <?php if (file_exists('views/includes/sidebar.php')) {
        include 'views/includes/sidebar.php';
    } elseif (file_exists('includes/sidebar.php')) {
        include 'includes/sidebar.php';
    } ?>

    <div class="w-full bg-gray-100 flex-1 p-8">
        <h1 class="text-3xl font-bold text-gray-800 mb-6">Dashboard Kasir</h1>

        <?php if (!empty($pesan_notifikasi)): ?>
            <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6" role="alert">
                <p class="font-bold">Perhatian</p>
                <p><?= htmlspecialchars($pesan_notifikasi) ?></p>
            </div>
        <?php endif; ?>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <div class="bg-white rounded-lg shadow-md p-6 border-l-4 border-blue-500">
                <h3 class="text-gray-500 text-sm font-semibold mb-2">Pemasukan Kotor</h3>
                <p class="text-2xl font-bold text-gray-800">Rp <?= number_format($pendapatan_bulan_ini, 0, ',', '.') ?></p>
            </div>
            <div class="bg-white rounded-lg shadow-md p-6 border-l-4 border-red-500">
                <h3 class="text-gray-500 text-sm font-semibold mb-2">Pengeluaran Operasional</h3>
                <p class="text-2xl font-bold text-red-600">Rp <?= number_format($pengeluaran_bulan_ini, 0, ',', '.') ?></p>
            </div>
            <div class="bg-white rounded-lg shadow-md p-6 border-l-4 border-green-500">
                <h3 class="text-gray-500 text-sm font-semibold mb-2">Laba Bersih Bulan Ini</h3>
                <p class="text-2xl font-bold text-green-600">Rp <?= number_format($laba_bersih, 0, ',', '.') ?></p>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            
            <div class="bg-white rounded-lg shadow-md p-6">
                <h2 class="text-xl font-bold text-gray-800 mb-4 text-red-600 flex items-center">
                    ⚠️ Item Perlu Restock
                </h2>
                <div class="overflow-x-auto">
                    <table class="min-w-full bg-white">
                        <thead>
                            <tr class="w-full bg-gray-200 text-gray-600 uppercase text-xs leading-normal">
                                <th class="py-3 px-4 text-left">Nama Barang</th>
                                <th class="py-3 px-4 text-center">Sisa Stok</th>
                                <th class="py-3 px-4 text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="text-gray-600 text-sm font-light">
                            <?php if (isset($stok_menipis) && count($stok_menipis) > 0): ?>
                                <?php foreach ($stok_menipis as $stok): ?>
                                    <tr class="border-b border-gray-200 hover:bg-gray-50 transition">
                                        <td class="py-3 px-4 text-left font-medium text-gray-800">
                                            <?= htmlspecialchars($stok['nama_barang'] ?? 'Tanpa Nama') ?>
                                        </td>
                                        <td class="py-3 px-4 text-center text-red-500 font-bold">
                                            <?= htmlspecialchars($stok['stok'] ?? 0) ?>
                                        </td>
                                        <td class="py-3 px-4 text-center">
                                            <?php
                                            $id_brg = $stok['id_barang'] ?? $stok['id'] ?? 0;
                                            $nama_brg = htmlspecialchars($stok['nama_barang'] ?? 'Barang', ENT_QUOTES);
                                            ?>
                                            <button onclick="bukaModalStok('<?= $id_brg ?>', '<?= $nama_brg ?>')" class="bg-blue-500 hover:bg-blue-600 text-white px-3 py-1 rounded text-xs transition duration-150 shadow">
                                                Tambah Stok
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="3" class="py-4 text-center text-gray-400 italic">Stok semua barang aman.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-md p-6">
                <h2 class="text-xl font-bold text-gray-800 mb-4 flex items-center justify-between">
                    <span>📦 Transaksi Terakhir</span>
                    <a href="index.php?page=riwayat_transaksi" class="text-xs text-blue-600 hover:underline font-normal">Lihat Semua →</a>
                </h2>
                <div class="overflow-x-auto">
                    <table class="min-w-full bg-white">
                        <thead>
                            <tr class="w-full bg-gray-200 text-gray-600 uppercase text-xs leading-normal">
                                <th class="py-3 px-4 text-left">Nota</th>
                                <th class="py-3 px-4 text-left">Pelanggan</th>
                                <th class="py-3 px-4 text-right">Total</th>
                                <th class="py-3 px-4 text-center">Detail</th>
                            </tr>
                        </thead>
                        <tbody class="text-gray-600 text-sm font-light">
                            <?php if (empty($riwayat_singkat)): ?>
                                <tr>
                                    <td colspan="4" class="py-4 text-center text-gray-400 italic">Belum ada riwayat transaksi.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($riwayat_singkat as $row): ?>
                                    <tr class="border-b border-gray-200 hover:bg-gray-50 transition">
                                        <td class="py-3 px-4 text-left font-mono font-bold text-gray-700">
                                            <?= htmlspecialchars($row['kode_transaksi']) ?>
                                        </td>
                                        <td class="py-3 px-4 text-left">
                                            <div class="font-medium text-gray-800"><?= htmlspecialchars($row['nama_pelanggan']) ?></div>
                                            <div class="text-[10px] text-gray-400 uppercase"><?= !empty($row['plat_nomor']) ? htmlspecialchars($row['plat_nomor']) : '-' ?></div>
                                        </td>
                                        <td class="py-3 px-4 text-right font-bold text-blue-600">
                                            Rp <?= number_format($row['total_bayar'], 0, ',', '.') ?>
                                        </td>
                                        <td class="py-3 px-4 text-center">
                                            <a href="index.php?page=detail_transaksi&id=<?= $row['id'] ?>" class="text-indigo-600 hover:text-indigo-900 font-bold text-xs">
                                                Buka
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>

    <div class="hidden opacity-50 fixed inset-0 z-40 bg-black transition-opacity duration-300" id="modal-stok-backdrop"></div>

    <div class="hidden overflow-x-hidden overflow-y-auto fixed inset-0 z-50 outline-none focus:outline-none justify-center items-center transition-all duration-300" id="modal-stok">
        <div class="relative w-full my-6 mx-auto max-w-md p-4">
            <div class="border-0 rounded-lg shadow-lg relative flex flex-col w-full bg-white outline-none focus:outline-none">
                <div class="flex items-start justify-between p-5 border-b border-solid border-gray-300 rounded-t">
                    <h3 class="text-xl font-semibold text-gray-800">Restock Barang</h3>
                    <button class="p-1 ml-auto border-0 text-gray-400 hover:text-gray-600 float-right text-3xl leading-none font-semibold outline-none focus:outline-none" onclick="tutupModalStok()">
                        <span class="text-gray-500 h-6 w-6 text-2xl block">×</span>
                    </button>
                </div>

                <form action="" method="POST">
                    <div class="relative p-6 flex-auto">
                        <input type="hidden" name="id_barang" id="stok_id_barang">
                        <p class="mb-4 text-sm text-gray-600">Menambah stok untuk: <strong id="stok_nama_barang" class="text-blue-600"></strong></p>

                        <div class="mb-4">
                            <label class="block text-gray-700 text-sm font-bold mb-2">Jumlah Ditambahkan</label>
                            <input type="number" name="jumlah_tambah" min="1" value="1" required class="shadow border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                        <div class="mb-4">
                            <label class="block text-gray-700 text-sm font-bold mb-2">Keterangan / Catatan Kulakan</label>
                            <input type="text" name="keterangan" required class="shadow border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Cth: Belanja Suku Cadang PT. ABC">
                        </div>
                    </div>
                    <div class="flex items-center justify-end p-6 border-t border-solid border-gray-300 rounded-b">
                        <button class="text-red-500 hover:bg-gray-100 font-bold uppercase px-6 py-2 rounded text-sm transition mr-1 mb-1" type="button" onclick="tutupModalStok()">Batal</button>
                        <button class="bg-green-600 text-white hover:bg-green-700 font-bold uppercase text-sm px-6 py-3 rounded shadow transition mr-1 mb-1" type="submit" name="simpan_stok">Update Stok</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        function bukaModalStok(id, nama) {
            document.getElementById('stok_id_barang').value = id;
            document.getElementById('stok_nama_barang').innerText = nama;

            document.getElementById('modal-stok').classList.remove('hidden');
            document.getElementById('modal-stok').classList.add('flex');
            document.getElementById('modal-stok-backdrop').classList.remove('hidden');
        }

        function tutupModalStok() {
            document.getElementById('modal-stok').classList.add('hidden');
            document.getElementById('modal-stok').classList.remove('flex');
            document.getElementById('modal-stok-backdrop').classList.add('hidden');

            document.getElementById('stok_id_barang').value = '';
            document.getElementById('stok_nama_barang').innerText = '';
        }
    </script>
</body>

</html>