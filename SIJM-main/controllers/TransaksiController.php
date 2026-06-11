<?php
require_once 'config/Database.php';
require_once 'models/TransaksiModel.php';

class TransaksiController
{
    private $model;

    public function __construct()
    {
        // Menggunakan method bawaan koneksi database Anda
        $db = (new Database())->getConnection();
        $this->model = new TransaksiModel($db);
    }

    /**
     * Ambil data untuk ditampilkan di Dropdown form kasir
     */
    public function getFormData()
    {
        return [
            'barang' => $this->model->getAllBarang(),
            'layanan' => $this->model->getAllLayanan()
        ];
    }

    /**
     * PERBARUAN: Proses data transaksi baru dengan mengunci kode nota dari router utama index.php
     */
    public function prosesTransaksi($post_data, $kasir_id, $kode_nota_generated = null)
    {
        $dataTrx = [
            'nama_pelanggan' => $post_data['nama_pelanggan'],
            'plat_nomor' => $post_data['plat_nomor'],
            'total_bayar' => $post_data['total_bayar']
        ];

        // Jika kode nota tidak dikirim dari index.php, buat default backup di sini
        if ($kode_nota_generated === null) {
            $kode_nota_generated = 'TRX-' . date('YmdHis');
        }

        $items = [];
        // Menggabungkan array dari input form dinamis ke dalam satu array rapi
        if (isset($post_data['item_id'])) {
            for ($i = 0; $i < count($post_data['item_id']); $i++) {
                $items[] = [
                    'id_item'   => $post_data['item_id'][$i],
                    'jenis'     => $post_data['item_jenis'][$i],
                    // PERBARUAN: Tangkap nama item (sangat penting untuk item tipe 'manual')
                    'nama_item' => isset($post_data['item_nama'][$i]) ? $post_data['item_nama'][$i] : '',
                    'harga'     => $post_data['item_harga'][$i],
                    'qty'       => $post_data['item_qty'][$i],
                    'subtotal'  => $post_data['item_subtotal'][$i]
                ];
            }
        }

        if (count($items) > 0) {
            // Meneruskan data transaksi, detail item, dan kode nota paten ke Model
            return $this->model->buatTransaksi($dataTrx, $items, $kasir_id, $kode_nota_generated);
        }
        return false;
    }

    // ==================== TAMBAHAN UNTUK RIWAYAT & AKUNTANSI ====================

    /**
     * Mengambil semua data transaksi utama untuk halaman riwayat_transaksi.php
     */
    public function riwayat()
    {
        return $this->model->getAllRiwayat();
    }

    /**
     * Mengambil data lengkap (Header + Detail Item) untuk halaman detail_transaksi.php
     */
    public function detail($id_transaksi)
    {
        if (empty($id_transaksi)) {
            return null;
        }

        // Ambil data induk transaksi (Kode, Nama Pelanggan, Plat, Kasir, dll)
        $transaksi = $this->model->getTransaksiById($id_transaksi);
        
        // Ambil baris list item belanjaan (Nama item, Qty, Subtotal)
        $detail = $this->model->getDetailTransaksi($id_transaksi);

        return [
            'transaksi' => $transaksi,
            'detail'    => $detail
        ];
    }

    /**
     * Memproses penghapusan transaksi dari sistem router terpusat
     */
    public function prosesHapus($id_transaksi)
    {
        if (empty($id_transaksi)) {
            return false;
        }
        // Meneruskan perintah penghapusan ke TransaksiModel
        return $this->model->hapusTransaksi($id_transaksi);
    }

    /**
     * Memproses pembaruan data transaksi dari form edit
     */
    public function prosesEdit($id_transaksi, $post_data)
    {
        if (empty($id_transaksi)) {
            return false;
        }

        $dataTrx = [
            'nama_pelanggan' => $post_data['nama_pelanggan'],
            'plat_nomor'     => $post_data['plat_nomor'],
            'total_bayar'    => $post_data['total_bayar']
        ];

        $items = [];
        // Memformat ulang input form dinamis dari halaman edit menjadi array berstruktur
        if (isset($post_data['item_id'])) {
            for ($i = 0; $i < count($post_data['item_id']); $i++) {
                $items[] = [
                    'id_item'   => $post_data['item_id'][$i],
                    'jenis'     => $post_data['item_jenis'][$i],
                    'nama_item' => isset($post_data['item_nama'][$i]) ? $post_data['item_nama'][$i] : '',
                    'harga'     => $post_data['item_harga'][$i],
                    'qty'       => $post_data['item_qty'][$i],
                    'subtotal'  => $post_data['item_subtotal'][$i]
                ];
            }
        }

        if (count($items) > 0) {
            // Meneruskan ID, data transaksi utama, beserta array item baru ke TransaksiModel
            return $this->model->updateTransaksi($id_transaksi, $dataTrx, $items);
        }
        return false;
    }

    /**
     * PEMBARUAN: Memproses permintaan perubahan status transaksi dari Admin ATAU Kasir
     */
    public function prosesUpdateStatus($id_transaksi, $status_baru)
    {
        // 1. Validasi Input
        if (empty($id_transaksi) || empty($status_baru)) {
            return false;
        }
        
        // 2. Validasi Hak Akses: Kasir dan Admin diizinkan mengubah status pengerjaan
        if (!isset($_SESSION['role']) || !in_array(strtolower($_SESSION['role']), ['admin', 'kasir'])) {
            return false;
        }

        // 3. Memastikan status yang dikirimkan sesuai dengan opsi alur kerja cucian yang diperbolehkan
        $opsi_valid = ['menunggu', 'lagi dicuci', 'sedang dijemur', 'selesai'];
        if (!in_array(strtolower($status_baru), $opsi_valid)) {
            return false;
        }

        // 4. Eksekusi ke Model
        return $this->model->updateStatusTransaksi($id_transaksi, strtolower($status_baru));
    }

    /**
     * PEMBARUAN: Memproses data laporan keuangan fleksibel (Hari, Minggu, Bulan, Tahun)
     */
    public function laporan($tipe, $tanggal, $bulan, $tahun)
    {
        $type = empty($tipe) ? 'bulan' : $tipe;
        $tgl  = empty($tanggal) ? date('Y-m-d') : $tanggal;
        $bln  = empty($bulan) ? date('m') : str_pad($bulan, 2, '0', STR_PAD_LEFT);
        $thn  = empty($tahun) ? date('Y') : $tahun;

        return $this->model->getLaporanFleksibel($type, $tgl, $bln, $thn);
    }
}