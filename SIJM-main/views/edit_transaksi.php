<?php
// views/edit_transaksi.php
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Transaksi #<?= htmlspecialchars($transaksi['kode_transaksi']) ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-100 flex flex-col md:flex-row min-h-screen">

    <?php 
    if (file_exists('views/includes/sidebar.php')) include 'views/includes/sidebar.php';
    elseif (file_exists('includes/sidebar.php')) include 'includes/sidebar.php';
    ?>

    <div class="w-full bg-gray-100 flex-1 p-8">
        <h1 class="text-3xl font-bold text-gray-800 mb-2">Edit Data Transaksi</h1>
        <p class="text-sm text-gray-500 mb-6">Mengubah Nota: <span class="font-mono font-bold text-gray-700"><?= htmlspecialchars($transaksi['kode_transaksi']) ?></span></p>

        <div class="flex flex-wrap -mx-3">
            <div class="w-full lg:w-1/3 px-3 mb-6 space-y-6">

                <div class="bg-white p-6 rounded-lg shadow-md border-t-4 border-yellow-500">
                    <h2 class="text-lg font-bold mb-4 border-b pb-2 text-gray-700">Data Pelanggan</h2>
                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-bold mb-2">Nama Pelanggan</label>
                        <input type="text" id="input_nama" value="<?= htmlspecialchars($transaksi['nama_pelanggan']) ?>" class="shadow-sm border border-gray-300 rounded w-full py-2 px-3 text-gray-700 focus:outline-none focus:ring-2 focus:ring-yellow-500">
                    </div>
                    <div class="mb-2">
                        <label class="block text-gray-700 text-sm font-bold mb-2">Plat Nomor (Opsional)</label>
                        <input type="text" id="input_plat" value="<?= htmlspecialchars($transaksi['plat_nomor']) ?>" class="shadow-sm border border-gray-300 rounded w-full py-2 px-3 text-gray-700 focus:outline-none focus:ring-2 focus:ring-yellow-500 uppercase">
                    </div>
                </div>

                <div class="bg-white p-6 rounded-lg shadow-md border-t-4 border-indigo-500">
                    <h2 class="text-lg font-bold mb-4 border-b pb-2 text-gray-700">Pilih dari Database</h2>
                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-bold mb-2">Pilih Jasa / Barang</label>
                        <select id="pilih_item" class="shadow-sm border border-gray-300 rounded w-full py-2 px-3 text-gray-700 bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            <option value="">-- Pilih Item --</option>
                            <optgroup label="Layanan Jasa">
                                <?php foreach ($formData['layanan'] as $jasa): ?>
                                    <option value="<?= $jasa['id'] ?>" data-jenis="layanan" data-harga="<?= $jasa['harga'] ?>" data-nama="<?= htmlspecialchars($jasa['nama_layanan']) ?>">
                                        <?= htmlspecialchars($jasa['nama_layanan']) ?> - Rp <?= number_format($jasa['harga'], 0, ',', '.') ?>
                                    </option>
                                <?php endforeach; ?>
                            </optgroup>
                            <optgroup label="Barang & Sparepart">
                                <?php foreach ($formData['barang'] as $brg): ?>
                                    <option value="<?= $brg['id'] ?>" data-jenis="barang" data-harga="<?= $brg['harga_jual'] ?>" data-nama="<?= htmlspecialchars($brg['nama_barang']) ?>" data-stok="<?= $brg['stok'] ?>">
                                        <?= htmlspecialchars($brg['nama_barang']) ?> (Sisa: <?= $brg['stok'] ?>) - Rp <?= number_format($brg['harga_jual'], 0, ',', '.') ?>
                                    </option>
                                <?php endforeach; ?>
                            </optgroup>
                        </select>
                    </div>
                    <div class="flex space-x-2">
                        <div class="w-1/3">
                            <label class="block text-gray-700 text-sm font-bold mb-2">Qty</label>
                            <input type="number" id="input_qty" value="1" min="1" class="shadow-sm border border-gray-300 rounded w-full py-2 px-3 text-gray-700 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        </div>
                        <div class="w-2/3 flex items-end">
                            <button type="button" onclick="tambahItemSistem()" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded w-full shadow-md transition duration-200">
                                + Tambah Item
                            </button>
                        </div>
                    </div>
                </div>

                <div class="bg-white p-6 rounded-lg shadow-md border-t-4 border-orange-500">
                    <h2 class="text-lg font-bold mb-4 border-b pb-2 text-gray-700">Input Jasa Manual</h2>
                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-bold mb-2">Nama Jasa / Tindakan</label>
                        <input type="text" id="manual_nama" class="shadow-sm border border-gray-300 rounded w-full py-2 px-3 text-gray-700 focus:outline-none focus:ring-2 focus:ring-orange-500" placeholder="Cth: Las Knalpot">
                    </div>
                    <div class="flex space-x-2 mb-4">
                        <div class="w-2/3">
                            <label class="block text-gray-700 text-sm font-bold mb-2">Harga (Rp)</label>
                            <input type="number" id="manual_harga" min="0" class="shadow-sm border border-gray-300 rounded w-full py-2 px-3 text-gray-700 focus:outline-none focus:ring-2 focus:ring-orange-500" placeholder="0">
                        </div>
                        <div class="w-1/3">
                            <label class="block text-gray-700 text-sm font-bold mb-2">Qty</label>
                            <input type="number" id="manual_qty" value="1" min="1" class="shadow-sm border border-gray-300 rounded w-full py-2 px-3 text-gray-700 focus:outline-none focus:ring-2 focus:ring-orange-500">
                        </div>
                    </div>
                    <button type="button" onclick="tambahItemManual()" class="bg-orange-500 hover:bg-orange-600 text-white font-bold py-2 px-4 rounded w-full shadow-md transition duration-200">
                        + Tambah Manual
                    </button>
                </div>

            </div>

            <div class="w-full lg:w-2/3 px-3 mb-6">
                <form action="" method="POST" id="formTransaksi" class="bg-white p-6 rounded-lg shadow-md h-full flex flex-col justify-between">
                    <input type="hidden" name="nama_pelanggan" id="form_nama_pelanggan" value="<?= htmlspecialchars($transaksi['nama_pelanggan']) ?>">
                    <input type="hidden" name="plat_nomor" id="form_plat_nomor" value="<?= htmlspecialchars($transaksi['plat_nomor']) ?>">
                    <input type="hidden" name="total_bayar" id="form_total_bayar" value="<?= $transaksi['total_bayar'] ?>">

                    <div>
                        <h2 class="text-xl font-bold mb-4 border-b pb-2 text-gray-800">Daftar Belanja (Edit Mode)</h2>
                        <div class="overflow-x-auto border rounded-lg border-gray-200">
                            <table class="min-w-full bg-white">
                                <thead class="bg-gray-100 text-gray-600 uppercase text-sm leading-normal">
                                    <tr>
                                        <th class="py-3 px-4 text-left">Nama Item</th>
                                        <th class="py-3 px-4 text-right">Harga</th>
                                        <th class="py-3 px-4 text-center">Qty</th>
                                        <th class="py-3 px-4 text-right">Subtotal</th>
                                        <th class="py-3 px-4 text-center">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody id="keranjang_body" class="text-gray-600 text-sm font-light">
                                    <tr id="row_kosong">
                                        <td colspan="5" class="py-8 text-center text-gray-400 italic">Keranjang masih kosong</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="mt-8 bg-gray-50 p-6 rounded-lg border border-gray-200 shadow-inner">
                        <div class="flex justify-between items-center mb-6">
                            <span class="text-xl font-bold text-gray-700">Total Tagihan Baru:</span>
                            <span class="text-4xl font-extrabold text-yellow-600" id="display_total">Rp 0</span>
                        </div>
                        <div class="flex gap-4">
                            <a href="index.php?page=riwayat_transaksi" class="w-1/3 bg-gray-500 hover:bg-gray-600 text-white text-center font-bold py-4 rounded-xl shadow transition duration-200">
                                Batal
                            </a>
                            <button type="submit" name="update_transaksi" id="btn_simpan" class="w-2/3 bg-yellow-500 hover:bg-yellow-600 text-white text-lg font-bold py-4 rounded-xl shadow transition duration-200">
                                Simpan Perubahan
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        let totalSeluruhnya = 0;
        let indexItem = 0;

        // Jalankan saat dokumen selesai diload untuk memasukkan item lama transaksi
        window.addEventListener('DOMContentLoaded', (event) => {
            <?php foreach($items_lama as $it): ?>
                tambahkanBarisTabel(
                    "<?= $it['id_item'] ?>", 
                    "<?= $it['jenis_item'] ?>", 
                    "<?= htmlspecialchars($it['nama_item']) ?>", 
                    <?= $it['harga_satuan'] ?>, 
                    <?= $it['jumlah'] ?>
                );
            <?php endforeach; ?>
        });

        document.getElementById('input_nama').addEventListener('input', function() {
            document.getElementById('form_nama_pelanggan').value = this.value;
        });
        document.getElementById('input_plat').addEventListener('input', function() {
            document.getElementById('form_plat_nomor').value = this.value;
        });

        function cekKeranjangKosong() {
            const tbody = document.getElementById('keranjang_body');
            const rowKosong = document.getElementById('row_kosong');
            if (tbody.children.length > 0 && totalSeluruhnya > 0) {
                if (rowKosong) rowKosong.style.display = 'none';
            } else {
                if (rowKosong) rowKosong.style.display = 'table-row';
            }
        }

        function tambahItemSistem() {
            const select = document.getElementById('pilih_item');
            const qtyInput = document.getElementById('input_qty');
            if (select.value === "") { alert("Pilih item terlebih dahulu!"); return; }

            const option = select.options[select.selectedIndex];
            const id = select.value;
            const jenis = option.getAttribute('data-jenis'); 
            const nama = option.getAttribute('data-nama');
            const harga = parseFloat(option.getAttribute('data-harga'));
            const qty = parseInt(qtyInput.value);

            tambahkanBarisTabel(id, jenis, nama, harga, qty);
            select.value = ""; qtyInput.value = 1;
        }

        function tambahItemManual() {
            const namaInput = document.getElementById('manual_nama');
            const hargaInput = document.getElementById('manual_harga');
            const qtyInput = document.getElementById('manual_qty');

            const nama = namaInput.value.trim();
            const harga = parseFloat(hargaInput.value);
            const qty = parseInt(qtyInput.value);

            if (nama === "" || isNaN(harga) || harga < 0) { alert("Input manual tidak valid!"); return; }

            tambahkanBarisTabel(0, 'manual', nama, harga, qty);
            namaInput.value = ""; hargaInput.value = ""; qtyInput.value = 1;
        }

        function tambahkanBarisTabel(id, jenis, nama, harga, qty) {
            const subtotal = harga * qty;
            totalSeluruhnya += subtotal;

            const tbody = document.getElementById('keranjang_body');

            let badgeHtml = '';
            if (jenis === 'barang') badgeHtml = '<span class="text-[10px] bg-blue-100 text-blue-800 px-2 py-0.5 rounded ml-2 uppercase font-semibold">Barang</span>';
            else if (jenis === 'layanan') badgeHtml = '<span class="text-[10px] bg-indigo-100 text-indigo-800 px-2 py-0.5 rounded ml-2 uppercase font-semibold">Jasa</span>';
            else if (jenis === 'manual') badgeHtml = '<span class="text-[10px] bg-orange-100 text-orange-800 px-2 py-0.5 rounded ml-2 uppercase font-semibold">Manual</span>';

            const tr = document.createElement('tr');
            tr.className = "border-b border-gray-200 hover:bg-gray-50 transition";
            tr.id = `row_${indexItem}`;
            tr.innerHTML = `
                <td class="py-3 px-4 text-left font-medium text-gray-800">
                    ${nama} ${badgeHtml}
                    <input type="hidden" name="item_id[]" value="${id}">
                    <input type="hidden" name="item_jenis[]" value="${jenis}">
                    <input type="hidden" name="item_nama[]" value="${nama}"> 
                    <input type="hidden" name="item_harga[]" value="${harga}">
                    <input type="hidden" name="item_qty[]" value="${qty}">
                    <input type="hidden" name="item_subtotal[]" value="${subtotal}">
                </td>
                <td class="py-3 px-4 text-right">Rp ${harga.toLocaleString('id-ID')}</td>
                <td class="py-3 px-4 text-center font-bold">
                    <span class="bg-gray-100 px-3 py-1 rounded border">${qty}</span>
                </td>
                <td class="py-3 px-4 text-right font-bold text-blue-600">Rp ${subtotal.toLocaleString('id-ID')}</td>
                <td class="py-3 px-4 text-center">
                    <button type="button" onclick="hapusItem(${indexItem}, ${subtotal})" class="bg-red-50 text-red-500 hover:bg-red-500 hover:text-white px-3 py-1.5 rounded-md text-sm transition font-semibold border border-red-200">
                        Hapus
                    </button>
                </td>
            `;
            tbody.appendChild(tr);
            updateTotal();
            indexItem++;
            cekKeranjangKosong();
        }

        function hapusItem(idRow, subtotal) {
            document.getElementById(`row_${idRow}`).remove();
            totalSeluruhnya -= subtotal;
            updateTotal();
            cekKeranjangKosong();
        }

        function updateTotal() {
            document.getElementById('display_total').innerText = "Rp " + totalSeluruhnya.toLocaleString('id-ID');
            document.getElementById('form_total_bayar').value = totalSeluruhnya;

            const btnSimpan = document.getElementById('btn_simpan');
            if (totalSeluruhnya > 0) {
                btnSimpan.removeAttribute('disabled');
                btnSimpan.classList.remove('opacity-50', 'cursor-not-allowed');
            } else {
                btnSimpan.setAttribute('disabled', 'true');
                btnSimpan.classList.add('opacity-50', 'cursor-not-allowed');
            }
        }

        document.getElementById('formTransaksi').addEventListener('submit', function(e) {
            const namaForm = document.getElementById('form_nama_pelanggan').value;
            if (namaForm.trim() === '') {
                e.preventDefault();
                alert('Nama pelanggan tidak boleh kosong!');
            }
        });
    </script>
</body>
</html>