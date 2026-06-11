<?php
// views/kelola_barang.php

require_once 'controllers/AuthController.php';
require_once 'controllers/BarangController.php';

// Proteksi Halaman
$auth = new AuthController();
$auth->checkAuth();

$barangCtrl = new BarangController();
$pesan = '';
$tipe_pesan = '';

// Mengambil data level hak akses (role) user langsung dari session login
$role_user = isset($_SESSION['role']) ? strtolower($_SESSION['role']) : 'kasir';

// Proses form jika ada submit (Hanya diproses jika user adalah Admin)
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if ($role_user === 'admin') {
        if (isset($_POST['simpan_barang'])) {
            try {
                // Langsung lempar $_POST dan $_FILES ke Controller
                if ($barangCtrl->prosesTambahBarang($_POST, $_FILES)) {
                    $pesan = "Barang baru berhasil ditambahkan!";
                    $tipe_pesan = "green";
                } else {
                    $pesan = "Gagal menambah barang! Pastikan data terisi dengan benar.";
                    $tipe_pesan = "red";
                }
            } catch (\PDOException $e) {
                if ($e->getCode() == 23000) {
                    $pesan = "Gagal! Kode Barang '" . htmlspecialchars($_POST['kode_barang']) . "' sudah digunakan. Silakan gunakan kode lain.";
                } else {
                    $pesan = "Terjadi kesalahan database: " . $e->getMessage();
                }
                $tipe_pesan = "red";
            } catch (\Exception $e) {
                $pesan = "Terjadi kesalahan sistem: " . $e->getMessage();
                $tipe_pesan = "red";
            }
        } elseif (isset($_POST['simpan_stok'])) {
            try {
                if ($barangCtrl->prosesTambahStok($_POST)) {
                    $pesan = "Stok berhasil ditambahkan!";
                    $tipe_pesan = "green";
                } else {
                    $pesan = "Gagal menambah stok!";
                    $tipe_pesan = "red";
                }
            } catch (\Exception $e) {
                $pesan = "Gagal! Terjadi kesalahan: " . $e->getMessage();
                $tipe_pesan = "red";
            }
        } elseif (isset($_POST['hapus_barang'])) {
            try {
                if ($barangCtrl->hapusBarang($_POST['id_hapus'])) {
                    $pesan = "Barang berhasil dihapus!";
                    $tipe_pesan = "green";
                } else {
                    $pesan = "Gagal menghapus barang!";
                    $tipe_pesan = "red";
                }
            } catch (\PDOException $e) {
                if ($e->getCode() == 23000) {
                    $pesan = "Gagal! Barang ini tidak bisa dihapus karena memiliki riwayat pada transaksi / servis.";
                } else {
                    $pesan = "Terjadi kesalahan database saat menghapus.";
                }
                $tipe_pesan = "red";
            }
        } elseif (isset($_POST['update_barang'])) {
            try {
                if ($barangCtrl->prosesEditBarang($_POST, $_FILES)) {
                    $pesan = "Data barang berhasil diperbarui!";
                    $tipe_pesan = "green";
                } else {
                    $pesan = "Gagal memperbarui data barang.";
                    $tipe_pesan = "red";
                }
            } catch (\PDOException $e) {
                if ($e->getCode() == 23000) {
                    $pesan = "Gagal! Kode Barang '" . htmlspecialchars($_POST['kode_barang']) . "' sudah digunakan.";
                } else {
                    $pesan = "Terjadi kesalahan database: " . $e->getMessage();
                }
                $tipe_pesan = "red";
            }
        }
    } else {
        // Jika kasir mencoba bypass request POST manipulasi data secara ilegal
        $pesan = "Akses ditolak! Kasir hanya diperbolehkan melihat detail data barang.";
        $tipe_pesan = "red";
    }
}

$search_keyword = isset($_GET['search']) ? $_GET['search'] : '';
$daftar_barang = $barangCtrl->getDaftarBarang($search_keyword);

$kategori_list = [];
foreach ($daftar_barang as $brg) {
    if (!empty($brg['kategori']) && !in_array($brg['kategori'], $kategori_list)) {
        $kategori_list[] = $brg['kategori'];
    }
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Barang & Stok - Ikhsan Jaya Motor</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/html5-qrcode" type="text/javascript"></script>
    <script>
        function toggleModal(modalID) {
            document.getElementById(modalID).classList.toggle("hidden");
            document.getElementById(modalID + "-backdrop").classList.toggle("hidden");
            document.getElementById(modalID).classList.toggle("flex");
            document.getElementById(modalID + "-backdrop").classList.toggle("flex");
        }

        function bukaModalStok(id, nama) {
            document.getElementById('stok_id_barang').value = id;
            document.getElementById('stok_nama_barang').innerText = nama;
            toggleModal('modal-stok');
        }

        function bukaModalDetail(data) {
            document.getElementById('detail_kode').innerText = data.kode_barang;
            document.getElementById('detail_nama').innerText = data.nama_barang;
            document.getElementById('detail_kategori').innerText = data.kategori || '-';
            document.getElementById('detail_stok').innerText = data.stok;

            let hargaJual = new Intl.NumberFormat('id-ID', {
                style: 'currency',
                currency: 'IDR',
                maximumFractionDigits: 0
            }).format(data.harga_jual);
            document.getElementById('detail_harga').innerText = hargaJual;

            document.getElementById('detail_deskripsi').innerText = data.deskripsi || 'Tidak ada catatan kecocokan motor / deskripsi.';

            const imgElement = document.getElementById('detail_gambar');
            const noImgElement = document.getElementById('detail_no_gambar');

            if (data.gambar && data.gambar.trim() !== '') {
                const pathArray = window.location.pathname.split('/');
                pathArray.pop();
                const baseUrl = window.location.origin + pathArray.join('/');
                const imageUrl = baseUrl + '/uploads/' + data.gambar.trim();

                imgElement.src = imageUrl;

                imgElement.onerror = function() {
                    imgElement.classList.add('hidden');
                    noImgElement.classList.remove('hidden');
                    noImgElement.innerHTML = '<span class="text-4xl text-red-400">⚠️</span><span class="text-xs text-center mt-2 text-red-500 font-bold">Gambar gagal dimuat<br>URL: ' + imageUrl + '</span>';
                };

                imgElement.onload = function() {
                    imgElement.classList.remove('hidden');
                    noImgElement.classList.add('hidden');
                };
            } else {
                imgElement.src = '';
                imgElement.classList.add('hidden');
                noImgElement.classList.remove('hidden');
                noImgElement.innerHTML = '<span class="text-4xl">📷</span><span class="text-sm mt-2">Tidak ada gambar</span>';
            }

            toggleModal('modal-detail');
        }

        function bukaModalHapus(id, nama) {
            document.getElementById('hapus_id_barang').value = id;
            document.getElementById('hapus_nama_barang').innerText = nama;
            toggleModal('modal-hapus');
        }

        let html5QrcodeScanner = null;

        function bukaScanner() {
            document.getElementById('scanner-container').classList.remove('hidden');
            if (!html5QrcodeScanner) {
                html5QrcodeScanner = new Html5QrcodeScanner("reader", {
                    fps: 10,
                    qrbox: 250
                });
                html5QrcodeScanner.render(onScanSuccess, onScanFailure);
            }
        }

        function tutupScanner() {
            document.getElementById('scanner-container').classList.add('hidden');
            if (html5QrcodeScanner) {
                html5QrcodeScanner.clear();
                html5QrcodeScanner = null;
            }
        }

        function onScanSuccess(decodedText) {
            document.getElementById('input_kode_barang').value = decodedText;
            alert("Barcode berhasil discan: " + decodedText);
            tutupScanner();
        }

        function onScanFailure() {}
    </script>
</head>

<body class="bg-gray-100 flex flex-col md:flex-row min-h-screen">
    <?php include 'includes/sidebar.php'; ?>

    <div class="w-full bg-gray-100 flex-1 p-8">
        <div class="flex flex-col md:flex-row justify-between items-center mb-6">
            <h1 class="text-3xl font-bold text-gray-800 mb-4 md:mb-0">Data Barang & Sparepart</h1>

            <div class="flex flex-col md:flex-row space-y-2 md:space-y-0 md:space-x-4 w-full md:w-auto">
                <form action="" method="GET" class="flex">
                    <input type="text" name="search" value="<?= htmlspecialchars($search_keyword) ?>" placeholder="Cari barang / kode / kategori..." class="shadow appearance-none border rounded-l py-2 px-4 text-gray-700 leading-tight focus:outline-none focus:ring-blue-500 w-full md:w-64">
                    <button type="submit" class="bg-gray-200 hover:bg-gray-300 text-gray-800 font-bold py-2 px-4 rounded-r border border-l-0">Cari</button>
                </form>
                
                <?php if ($role_user === 'admin'): ?>
                    <button onclick="toggleModal('modal-tambah')" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded shadow whitespace-nowrap">+ Tambah Barang Baru</button>
                <?php endif; ?>
            </div>
        </div>

        <?php if ($pesan): ?>
            <div class="bg-<?= $tipe_pesan ?>-100 border border-<?= $tipe_pesan ?>-400 text-<?= $tipe_pesan ?>-700 px-4 py-3 rounded relative mb-4 flex items-center shadow-sm">
                <?php if ($tipe_pesan == 'red'): ?> <span class="text-xl mr-2">⚠️</span> <?php elseif ($tipe_pesan == 'green'): ?> <span class="text-xl mr-2">✅</span> <?php endif; ?>
                <span class="block sm:inline font-medium"><?= htmlspecialchars($pesan) ?></span>
            </div>
        <?php endif; ?>

        <div class="bg-white rounded-lg shadow-md overflow-x-auto">
            <table class="min-w-full bg-white">
                <thead class="bg-gray-800 text-white">
                    <tr>
                        <th class="py-3 px-6 text-left text-sm font-semibold uppercase tracking-wider">Kode</th>
                        <th class="py-3 px-6 text-left text-sm font-semibold uppercase tracking-wider">Kategori</th>
                        <th class="py-3 px-6 text-left text-sm font-semibold uppercase tracking-wider">Nama Barang</th>
                        <th class="py-3 px-6 text-center text-sm font-semibold uppercase tracking-wider">Stok</th>
                        
                        <?php if ($role_user === 'admin'): ?>
                            <th class="py-3 px-6 text-right text-sm font-semibold uppercase tracking-wider">Harga Modal</th>
                        <?php endif; ?>
                        
                        <th class="py-3 px-6 text-right text-sm font-semibold uppercase tracking-wider">Harga Jual</th>
                        <th class="py-3 px-6 text-center text-sm font-semibold uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="text-gray-700 text-sm">
                    <?php if (count($daftar_barang) > 0): ?>
                        <?php foreach ($daftar_barang as $brg): ?>
                            <?php $json_data = htmlspecialchars(json_encode($brg), ENT_QUOTES, 'UTF-8'); ?>
                            <tr class="border-b border-gray-200 hover:bg-gray-50">
                                <td class="py-3 px-6 text-left whitespace-nowrap"><span class="bg-gray-200 text-gray-800 py-1 px-2 rounded text-xs font-bold"><?= htmlspecialchars($brg['kode_barang']) ?></span></td>
                                <td class="py-3 px-6 text-left whitespace-nowrap"><span class="bg-blue-100 text-blue-800 py-1 px-2 rounded-full text-xs"><?= htmlspecialchars($brg['kategori'] ?? 'Uncategorized') ?></span></td>
                                <td class="py-3 px-6 text-left font-medium">
                                    <?= htmlspecialchars($brg['nama_barang']) ?>
                                    <?php if (!empty($brg['gambar'])): ?> <span class="ml-2 text-xs text-blue-500" title="Ada Gambar">📷</span> <?php endif; ?>
                                </td>
                                <td class="py-3 px-6 text-center">
                                    <span class="<?= $brg['stok'] <= 5 ? 'text-red-600 font-bold' : 'text-green-600 font-bold' ?>"><?= $brg['stok'] ?></span>
                                </td>
                                
                                <?php if ($role_user === 'admin'): ?>
                                    <td class="py-3 px-6 text-right text-gray-500">Rp <?= number_format($brg['harga_modal'], 0, ',', '.') ?></td>
                                <?php endif; ?>
                                
                                <td class="py-3 px-6 text-right font-bold text-blue-600">Rp <?= number_format($brg['harga_jual'], 0, ',', '.') ?></td>
                                <td class="py-3 px-6 text-center">
                                    <div class="flex item-center justify-center space-x-2">
                                        
                                        <button onclick="bukaModalDetail(<?= $json_data ?>)" class="bg-blue-500 hover:bg-blue-600 text-white py-1 px-3 rounded text-xs transition duration-200" title="Detail Barang">👁️ Detail</button>
                                        
                                        <?php if ($role_user === 'admin'): ?>
                                            <button onclick="bukaModalStok(<?= $brg['id'] ?>, '<?= htmlspecialchars($brg['nama_barang'], ENT_QUOTES) ?>')" class="bg-green-500 hover:bg-green-600 text-white py-1 px-2 rounded text-xs transition duration-200" title="Restock">+ Stok</button>
                                            <button
                                                onclick="bukaModalEdit(this)"
                                                data-id="<?= $brg['id'] ?>"
                                                data-kode="<?= htmlspecialchars($brg['kode_barang']) ?>"
                                                data-nama="<?= htmlspecialchars($brg['nama_barang']) ?>"
                                                data-kategori="<?= htmlspecialchars($brg['kategori']) ?>"
                                                data-stok="<?= $brg['stok'] ?>"
                                                data-modal="<?= $brg['harga_modal'] ?>"
                                                data-jual="<?= $brg['harga_jual'] ?>"
                                                data-deskripsi="<?= htmlspecialchars($brg['deskripsi']) ?>"
                                                class="bg-yellow-500 text-white px-2 py-1 rounded hover:bg-yellow-600 text-xs transition duration-200">
                                                Edit
                                            </button>
                                            <button onclick="bukaModalHapus(<?= $brg['id'] ?>, '<?= htmlspecialchars($brg['nama_barang'], ENT_QUOTES) ?>')" class="bg-red-500 hover:bg-red-600 text-white py-1 px-2 rounded text-xs transition duration-200" title="Hapus Barang">🗑️</button>
                                        <?php endif; ?>
                                        
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="<?= ($role_user === 'admin') ? '7' : '6' ?>" class="py-4 text-center text-gray-500">Barang tidak ditemukan.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <?php if ($role_user === 'admin'): ?>
        <div class="hidden overflow-x-hidden overflow-y-auto fixed inset-0 z-50 outline-none focus:outline-none justify-center items-center" id="modal-tambah">
            <div class="relative w-full my-6 mx-auto max-w-2xl">
                <div class="border-0 rounded-lg shadow-lg relative flex flex-col w-full bg-white outline-none focus:outline-none">
                    <div class="flex items-start justify-between p-5 border-b border-solid border-gray-300 rounded-t">
                        <h3 class="text-xl font-semibold text-gray-800">Tambah Barang Baru</h3>
                        <button class="p-1 ml-auto border-0 text-gray-400 hover:text-gray-600 float-right text-3xl leading-none font-semibold outline-none focus:outline-none" onclick="toggleModal('modal-tambah')">
                            <span class="text-gray-500 h-6 w-6 text-2xl block outline-none focus:outline-none">×</span>
                        </button>
                    </div>
                    <form action="" method="POST" enctype="multipart/form-data">
                        <div class="relative p-6 flex-auto max-h-[70vh] overflow-y-auto">
                            <div id="scanner-container" class="hidden mb-4 p-4 border-2 border-dashed border-blue-300 bg-blue-50 rounded text-center">
                                <p class="text-sm font-bold text-gray-700 mb-2">Arahkan barcode ke kamera</p>
                                <div id="reader" class="mx-auto" style="width: 100%; max-width: 400px;"></div>
                                <button type="button" onclick="tutupScanner()" class="mt-2 text-red-500 text-sm font-bold underline">Tutup Kamera</button>
                            </div>

                            <div class="mb-4">
                                <label class="block text-gray-700 text-sm font-bold mb-2">Kode Barang (Barcode)</label>
                                <div class="flex">
                                    <input type="text" name="kode_barang" id="input_kode_barang" required class="shadow appearance-none border rounded-l w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-blue-500" placeholder="Ketik atau Scan">
                                    <button type="button" onclick="bukaScanner()" class="bg-gray-800 hover:bg-gray-900 text-white font-bold py-2 px-4 rounded-r flex items-center">📷 Scan</button>
                                </div>
                            </div>

                            <div class="mb-4 grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-gray-700 text-sm font-bold mb-2">Nama Barang</label>
                                    <input type="text" name="nama_barang" required class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-blue-500" placeholder="Cth: Kampas Rem Vario">
                                </div>
                                <div>
                                    <label class="block text-gray-700 text-sm font-bold mb-2">Kategori</label>
                                    <input type="text" name="kategori" list="kategori-list" required class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-blue-500" placeholder="Cth: Sparepart">
                                    <datalist id="kategori-list">
                                        <?php foreach ($kategori_list as $kat): ?> <option value="<?= htmlspecialchars($kat) ?>"></option> <?php endforeach; ?>
                                    </datalist>
                                </div>
                            </div>

                            <div class="mb-4 border border-gray-300 p-4 rounded-lg bg-gray-50">
                                <label class="block text-gray-700 text-sm font-bold mb-3 border-b pb-2">Gambar Barang (Opsional)</label>
                                <div class="flex flex-col sm:flex-row gap-4 mb-3">
                                    <div class="flex-1">
                                        <label class="block text-xs text-gray-600 mb-1 font-semibold">1. Dari File / Galeri</label>
                                        <input type="file" name="gambar" id="input_file" accept="image/*" class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100" onchange="previewFile()">
                                    </div>
                                    <div class="flex items-center justify-center font-bold text-gray-400 text-sm">ATAU</div>
                                    <div class="flex-1">
                                        <label class="block text-xs text-gray-600 mb-1 font-semibold">2. Dari Kamera Langsung</label>
                                        <button type="button" onclick="bukaKameraUtama()" class="w-full bg-indigo-500 hover:bg-indigo-600 text-white font-bold py-2 px-4 rounded transition duration-200 flex justify-center items-center gap-2">📷 Buka Kamera</button>
                                    </div>
                                </div>
                                <div id="area_preview" class="hidden mt-3 text-center bg-white p-3 border rounded shadow-inner">
                                    <p class="text-xs text-green-600 font-bold mb-2" id="teks_sumber_foto">Preview:</p>
                                    <img id="preview_img" src="" alt="Preview" class="max-h-40 mx-auto rounded border-2 border-gray-200">
                                    <button type="button" onclick="hapusFoto()" class="mt-2 text-red-500 text-xs font-bold hover:text-red-700 underline">Batalkan / Hapus Foto</button>
                                </div>
                            </div>

                            <div class="mb-4">
                                <label class="block text-gray-700 text-sm font-bold mb-2">Kecocokan Motor & Persamaan Part (Opsional)</label>
                                <textarea name="deskripsi" rows="3" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-blue-500" placeholder="Cth: Cocok untuk motor Vario 125..."></textarea>
                            </div>

                            <div class="mb-4 grid grid-cols-1 md:grid-cols-3 gap-4">
                                <div>
                                    <label class="block text-gray-700 text-sm font-bold mb-2">Stok Awal</label>
                                    <input type="number" name="stok" value="0" min="0" required class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-blue-500">
                                </div>
                                <div>
                                    <label class="block text-gray-700 text-sm font-bold mb-2">Harga Modal</label>
                                    <input type="number" name="harga_modal" min="0" required class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-blue-500" placeholder="Rp">
                                </div>
                                <div>
                                    <label class="block text-gray-700 text-sm font-bold mb-2">Harga Jual</label>
                                    <input type="number" name="harga_jual" min="0" required class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 stroke-zinc-400 lead-tight focus:outline-none focus:ring-blue-500" placeholder="Rp">
                                </div>
                            </div>
                        </div>
                        <div class="flex items-center justify-end p-6 border-t border-solid border-gray-300 rounded-b">
                            <button class="text-red-500 bg-transparent font-bold uppercase px-6 py-2 text-sm outline-none focus:outline-none mr-1 mb-1 ease-linear transition-all duration-150" type="button" onclick="toggleModal('modal-tambah')">Batal</button>
                            <button class="bg-blue-600 text-white hover:bg-blue-700 font-bold uppercase text-sm px-6 py-3 rounded shadow outline-none focus:outline-none mr-1 mb-1 ease-linear transition-all duration-150" type="submit" name="simpan_barang">Simpan Barang</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <div class="hidden opacity-50 fixed inset-0 z-40 bg-black" id="modal-tambah-backdrop"></div>

        <div class="hidden overflow-x-hidden overflow-y-auto fixed inset-0 z-50 outline-none focus:outline-none justify-center items-center" id="modal-stok">
            <div class="relative w-full my-6 mx-auto max-w-md">
                <div class="border-0 rounded-lg shadow-lg relative flex flex-col w-full bg-white outline-none focus:outline-none">
                    <div class="flex items-start justify-between p-5 border-b border-solid border-gray-300 rounded-t">
                        <h3 class="text-xl font-semibold text-gray-800">Restock Barang</h3>
                        <button class="p-1 ml-auto border-0 text-gray-400 hover:text-gray-600 float-right text-3xl leading-none font-semibold outline-none focus:outline-none" onclick="toggleModal('modal-stok')">
                            <span class="text-gray-500 h-6 w-6 text-2xl block outline-none focus:outline-none">×</span>
                        </button>
                    </div>
                    <form action="" method="POST">
                        <div class="relative p-6 flex-auto">
                            <input type="hidden" name="id_barang" id="stok_id_barang">
                            <p class="mb-4 text-gray-600">Menambah stok untuk: <strong id="stok_nama_barang" class="text-blue-600"></strong></p>
                            <div class="mb-4">
                                <label class="block text-gray-700 text-sm font-bold mb-2">Jumlah Ditambahkan</label>
                                <input type="number" name="jumlah_tambah" min="1" value="1" required class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-blue-500">
                            </div>
                            <div class="mb-4">
                                <label class="block text-gray-700 text-sm font-bold mb-2">Keterangan / Catatan Kulakan</label>
                                <input type="text" name="keterangan" required class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-blue-500" placeholder="Cth: Beli dari Agen Maju Jaya (Nota 123)">
                            </div>
                        </div>
                        <div class="flex items-center justify-end p-6 border-t border-solid border-gray-300 rounded-b">
                            <button class="text-red-500 bg-transparent font-bold uppercase px-6 py-2 text-sm outline-none focus:outline-none mr-1 mb-1 ease-linear transition-all duration-150" type="button" onclick="toggleModal('modal-stok')">Batal</button>
                            <button class="bg-green-600 text-white hover:bg-green-700 font-bold uppercase text-sm px-6 py-3 rounded shadow outline-none focus:outline-none mr-1 mb-1 ease-linear transition-all duration-150" type="submit" name="simpan_stok">Update Stok</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <div class="hidden opacity-50 fixed inset-0 z-40 bg-black" id="modal-stok-backdrop"></div>

        <div class="hidden overflow-x-hidden overflow-y-auto fixed inset-0 z-50 outline-none focus:outline-none justify-center items-center" id="modal-hapus">
            <div class="relative w-full my-6 mx-auto max-w-sm">
                <div class="border-0 rounded-lg shadow-lg relative flex flex-col w-full bg-white outline-none focus:outline-none">
                    <form action="" method="POST">
                        <div class="relative p-6 flex-auto text-center">
                            <span class="text-5xl text-red-500 block mb-4">⚠️</span>
                            <h3 class="text-xl font-bold text-gray-800 mb-2">Yakin hapus barang ini?</h3>
                            <p class="text-gray-600 text-sm">Anda akan menghapus <strong id="hapus_nama_barang"></strong> secara permanen.</p>
                            <input type="hidden" name="id_hapus" id="hapus_id_barang">
                        </div>
                        <div class="flex items-center justify-center p-4 border-t border-solid border-gray-300 rounded-b bg-gray-50 space-x-2">
                            <button class="bg-gray-300 text-gray-800 hover:bg-gray-400 font-bold uppercase text-sm px-6 py-2 rounded shadow outline-none focus:outline-none ease-linear transition-all duration-150" type="button" onclick="toggleModal('modal-hapus')">Batal</button>
                            <button class="bg-red-600 text-white hover:bg-red-700 font-bold uppercase text-sm px-6 py-2 rounded shadow outline-none focus:outline-none ease-linear transition-all duration-150" type="submit" name="hapus_barang">Ya, Hapus</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <div class="hidden opacity-50 fixed inset-0 z-40 bg-black" id="modal-hapus-backdrop"></div>

        <div id="modal_kamera" class="fixed inset-0 bg-black bg-opacity-90 z-[60] hidden flex flex-col justify-center items-center">
            <div class="bg-white p-4 rounded-lg shadow-xl max-w-lg w-full mx-4">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-xl font-bold">Ambil Foto Barang</h3>
                    <button type="button" onclick="tutupKameraUtama()" class="text-red-500 hover:text-red-700 text-3xl font-bold">&times;</button>
                </div>
                <div class="bg-black rounded-lg overflow-hidden mb-4 relative aspect-video flex justify-center items-center w-full">
                    <video id="video_kamera" autoplay playsinline class="w-full h-full object-cover"></video>
                </div>
                <canvas id="canvas_kamera" class="hidden"></canvas>
                <div class="flex justify-center gap-4">
                    <button type="button" onclick="tutupKameraUtama()" class="bg-gray-500 hover:bg-gray-600 text-white font-bold py-2 px-6 rounded shadow">Batal</button>
                    <button type="button" onclick="jepretFotoBarang()" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-6 rounded shadow flex items-center gap-2">📷 Jepret Gambar</button>
                </div>
            </div>
        </div>

        <div id="modalEditBarang" class="fixed inset-0 z-50 hidden bg-black bg-opacity-50 flex items-center justify-center p-4 overflow-y-auto">
            <div class="bg-white rounded-lg p-6 w-full max-w-md relative">
                <h2 class="text-xl font-bold mb-4">Edit Barang</h2>
                <form action="" method="POST">
                    <input type="hidden" name="id" id="edit_id_barang">
                    <div class="mb-3">
                        <label class="block text-sm font-medium mb-1">Kode Barang</label>
                        <input type="text" name="kode_barang" id="edit_kode_barang" class="w-full border p-2 rounded" required>
                    </div>
                    <div class="mb-3">
                        <label class="block text-sm font-medium mb-1">Nama Barang</label>
                        <input type="text" name="nama_barang" id="edit_nama_barang" class="w-full border p-2 rounded" required>
                    </div>
                    <div class="mb-3">
                        <label class="block text-sm font-medium mb-1">Kategori</label>
                        <input type="text" name="kategori" id="edit_kategori" class="w-full border p-2 rounded">
                    </div>
                    <div class="grid grid-cols-2 gap-4 mb-3">
                        <div>
                            <label class="block text-sm font-medium mb-1">Stok</label>
                            <input type="number" name="stok" id="edit_stok" class="w-full border p-2 rounded" required>
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1">Harga Modal</label>
                            <input type="number" name="harga_modal" id="edit_harga_modal" class="w-full border p-2 rounded" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="block text-sm font-medium mb-1">Harga Jual</label>
                        <input type="number" name="harga_jual" id="edit_harga_jual" class="w-full border p-2 rounded" required>
                    </div>
                    <div class="mb-4">
                        <label class="block text-sm font-medium mb-1">Deskripsi</label>
                        <textarea name="deskripsi" id="edit_deskripsi" class="w-full border p-2 rounded"></textarea>
                    </div>
                    <div class="flex justify-end gap-2">
                        <button type="button" onclick="tutupModalEdit()" class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600">Batal</button>
                        <button type="submit" name="update_barang" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">Simpan Perubahan</button>
                    </div>
                </form>
            </div>
        </div>
    <?php endif; ?>

    <div class="hidden overflow-x-hidden overflow-y-auto fixed inset-0 z-50 outline-none focus:outline-none justify-center items-center" id="modal-detail">
        <div class="relative w-full my-6 mx-auto max-w-2xl">
            <div class="border-0 rounded-lg shadow-lg relative flex flex-col w-full bg-white outline-none focus:outline-none">
                <div class="flex items-start justify-between p-5 border-b border-solid border-gray-300 rounded-t bg-gray-50">
                    <h3 class="text-xl font-semibold text-gray-800">Detail Informasi Barang</h3>
                    <button class="p-1 ml-auto border-0 text-gray-400 hover:text-gray-600 float-right text-3xl leading-none font-semibold outline-none focus:outline-none" onclick="toggleModal('modal-detail')">
                        <span class="text-gray-500 h-6 w-6 text-2xl block outline-none focus:outline-none">×</span>
                    </button>
                </div>
                <div class="relative p-6 flex-auto">
                    <div class="flex flex-col md:flex-row gap-6">
                        <div class="w-full md:w-1/3 flex flex-col items-center justify-center border rounded p-2 bg-gray-100 min-h-[150px]">
                            <img id="detail_gambar" src="" alt="Gambar Barang" class="hidden max-w-full h-auto rounded shadow-sm object-cover max-h-48">
                            <div id="detail_no_gambar" class="text-gray-400 text-center flex flex-col items-center w-full">
                                <span class="text-4xl">📷</span>
                                <span class="text-sm mt-2">Tidak ada gambar</span>
                            </div>
                        </div>
                        <div class="w-full md:w-2/3">
                            <h4 id="detail_nama" class="text-2xl font-bold text-blue-700 mb-2">Nama Barang</h4>
                            <div class="grid grid-cols-2 gap-4 mb-4">
                                <div>
                                    <p class="text-xs text-gray-500 uppercase font-semibold">Kode/Barcode</p>
                                    <p id="detail_kode" class="font-bold text-gray-800">-</p>
                                </div>
                                <div>
                                    <p class="text-xs text-gray-500 uppercase font-semibold">Kategori</p>
                                    <p id="detail_kategori" class="font-bold text-gray-800">-</p>
                                </div>
                                <div>
                                    <p class="text-xs text-gray-500 uppercase font-semibold">Sisa Stok</p>
                                    <p id="detail_stok" class="font-bold text-gray-800">-</p>
                                </div>
                                <div>
                                    <p class="text-xs text-gray-500 uppercase font-semibold">Harga Jual</p>
                                    <p id="detail_harga" class="font-bold text-green-600 text-lg">-</p>
                                </div>
                            </div>
                            <div class="border-t pt-4 mt-2">
                                <p class="text-xs text-gray-500 uppercase font-semibold mb-1">Kecocokan Motor & Substitusi (Persamaan)</p>
                                <p id="detail_deskripsi" class="text-sm text-gray-700 bg-yellow-50 p-3 rounded border border-yellow-200">-</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="flex items-center justify-end p-4 border-t border-solid border-gray-300 rounded-b">
                    <button class="bg-gray-200 text-gray-800 hover:bg-gray-300 font-bold uppercase text-sm px-6 py-2 rounded shadow outline-none focus:outline-none ease-linear transition-all duration-150" type="button" onclick="toggleModal('modal-detail')">Tutup</button>
                </div>
            </div>
        </div>
    </div>
    <div class="hidden opacity-50 fixed inset-0 z-40 bg-black" id="modal-detail-backdrop"></div>

    <script>
        const videoBarang = document.getElementById('video_kamera');
        const canvasBarang = document.getElementById('canvas_kamera');
        const modalKamera = document.getElementById('modal_kamera');
        const inputFile = document.getElementById('input_file');
        const areaPreview = document.getElementById('area_preview');
        const previewImg = document.getElementById('preview_img');
        const teksSumber = document.getElementById('teks_sumber_foto');

        let streamBarang = null;

        async function bukaKameraUtama() {
            try {
                streamBarang = await navigator.mediaDevices.getUserMedia({
                    video: { facingMode: 'environment' }
                });
                videoBarang.srcObject = streamBarang;
                if(modalKamera) modalKamera.classList.remove('hidden');
            } catch (err) {
                alert("Gagal mengakses kamera. Pastikan browser memiliki izin akses kamera.\n\nError: " + err.message);
            }
        }

        function tutupKameraUtama() {
            if (streamBarang) {
                streamBarang.getTracks().forEach(track => track.stop());
                videoBarang.srcObject = null;
            }
            if(modalKamera) modalKamera.classList.add('hidden');
        }

        function jepretFotoBarang() {
            canvasBarang.width = videoBarang.videoWidth;
            canvasBarang.height = videoBarang.videoHeight;
            canvasBarang.getContext('2d').drawImage(videoBarang, 0, 0, canvasBarang.width, canvasBarang.height);

            canvasBarang.toBlob(function(blob) {
                const fileName = "cam_" + new Date().getTime() + ".jpg";
                const file = new File([blob], fileName, { type: "image/jpeg" });

                const dataTransfer = new DataTransfer();
                dataTransfer.items.add(file);
                if(inputFile) inputFile.files = dataTransfer.files;

                const previewUrl = URL.createObjectURL(blob);
                if(previewImg) previewImg.src = previewUrl;
                if(teksSumber) teksSumber.innerText = "Preview (Dari Kamera):";
                if(areaPreview) areaPreview.classList.remove('hidden');

                tutupKameraUtama();
            }, 'image/jpeg', 0.8);
        }

        function previewFile() {
            const file = inputFile.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    if(previewImg) previewImg.src = e.target.result;
                    if(teksSumber) teksSumber.innerText = "Preview (Dari File Galeri):";
                    if(areaPreview) areaPreview.classList.remove('hidden');
                }
                reader.readAsDataURL(file);
            }
        }

        function hapusFoto() {
            if(inputFile) inputFile.value = '';
            if(previewImg) previewImg.src = '';
            if(areaPreview) areaPreview.classList.add('hidden');
        }

        function bukaModalEdit(btn) {
            document.getElementById('edit_id_barang').value = btn.getAttribute('data-id');
            document.getElementById('edit_kode_barang').value = btn.getAttribute('data-kode');
            document.getElementById('edit_nama_barang').value = btn.getAttribute('data-nama');
            document.getElementById('edit_kategori').value = btn.getAttribute('data-kategori');
            document.getElementById('edit_stok').value = btn.getAttribute('data-stok');
            document.getElementById('edit_harga_modal').value = btn.getAttribute('data-modal');
            document.getElementById('edit_harga_jual').value = btn.getAttribute('data-jual');
            document.getElementById('edit_deskripsi').value = btn.getAttribute('data-deskripsi');

            document.getElementById('modalEditBarang').classList.remove('hidden');
        }

        function tutupModalEdit() {
            document.getElementById('modalEditBarang').classList.add('hidden');
        }
    </script>
</body>
</html>