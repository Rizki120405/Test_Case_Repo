<?php
// index.php (Berada di root folder utama proyek SIJM)

// 1. Load semua Controller yang dibutuhkan oleh aplikasi
require_once 'controllers/AuthController.php';
require_once 'controllers/DashboardController.php';
require_once 'controllers/BarangController.php'; 
require_once 'controllers/TransaksiController.php';

// 2. Aktifkan Error Reporting untuk mempermudah proses debugging (bisa dimatikan saat production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// 3. Proteksi Halaman - Cek status login user secara global
$auth = new AuthController();

// Tangkap parameter halaman dari URL (Contoh: index.php?page=riwayat_transaksi)
// Jika parameter 'page' tidak ada di URL, maka otomatis diarahkan ke halaman 'dashboard'
$page = isset($_GET['page']) ? $_GET['page'] : 'dashboard';

// Berikan pengecualian proteksi halaman agar halaman LOGIN, LOGOUT, dan CEK STATUS publik bisa diakses tanpa login
if ($page !== 'login' && $page !== 'logout' && $page !== 'cek_status') {
    $auth->checkAuth();
}

// 4. Inisialisasi semua objek Controller
$dashboardCtrl = new DashboardController();
$barangCtrl    = new BarangController(); 
$transaksiCtrl = new TransaksiController(); // Variabel objek utama yang digunakan

// Variabel global untuk menampung notifikasi jika terjadi error/sukses pada form
$pesan_notifikasi = '';

// 6. Sistem Routing Menggunakan Switch Case
switch ($page) {
    
    case 'login':
        // Jika user sudah masuk sesi login tapi mencoba accessing halaman login kembali
        if (isset($_SESSION['user_id'])) {
            header("Location: index.php?page=dashboard");
            exit;
        }
        include 'views/login.php';
        break;

    case 'logout':
        $auth->logout();
        break;

    case 'dashboard':
        // --- LOGIKA HALAMAN DASHBOARD ---
        
        // Cek jika ada submit form tambah stok dari modal dashboard
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['simpan_stok'])) {
            if ($barangCtrl->prosesTambahStok($_POST)) {
                echo "<script>alert('Stok berhasil ditambahkan!'); window.location.href='index.php?page=dashboard';</script>";
                exit;
            } else {
                $pesan_notifikasi = "Gagal menambahkan stok. Terjadi kesalahan database.";
            }
        }

        // Ambil dan siapkan data finansial serta stok dari Controller
        $data = $dashboardCtrl->getDashboardData();
        $riwayat_transaksi = $transaksiCtrl->riwayat();
        
        // Ambil 5 transaksi terakhir untuk tabel ringkasan di dashboard
        $riwayat_singkat = array_slice($riwayat_transaksi, 0, 5);

        // Map data ke variabel yang akan dicetak pada file view
        $pendapatan_bulan_ini  = $data['pendapatan'] ?? 0;
        $pengeluaran_bulan_ini = $data['pengeluaran'] ?? 0;
        $laba_bersih           = $data['laba_bersih'] ?? 0;
        $stok_menipis          = $data['stok_menipis'] ?? [];

        // Panggil tampilan dashboard bersih dari folder views/
        include 'views/dashboard.php';
        break;

    case 'transaksi_baru':
        // --- LOGIKA HALAMAN KASIR (POINT OF SALES) ---
        
        // Ambil data barang dan layanan untuk dropdown kasir
        $formData = $transaksiCtrl->getFormData();
        $pesan_sukses = '';

        // Cek jika kasir menekan tombol "Simpan & Cetak Struk"
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['simpan_transaksi'])) {
            
            // PERBAIKAN: Kunci waktu snapshot tunggal agar kode nota di index dan controller sinkron sempurna
            $waktu_sekarang = date('YmdHis');
            $kode_nota_generated = 'TRX-' . $waktu_sekarang;
            
            if ($transaksiCtrl->prosesTransaksi($_POST, $_SESSION['user_id'], $kode_nota_generated)) {
                // Ambil ID Transaksi yang baru saja disimpan berdasarkan kode nota paten unik
                $db = (new Database())->getConnection();
                $stmt = $db->prepare("SELECT id FROM transaksi WHERE kode_transaksi = :kode");
                $stmt->execute([':kode' => $kode_nota_generated]);
                $res = $stmt->fetch(PDO::FETCH_ASSOC);
                
                $id_terakhir = $res['id'] ?? 0;

                echo "<script>
                    alert('Transaksi berhasil disimpan!');
                    window.location.href = 'index.php?page=detail_transaksi&id=" . $id_terakhir . "';
                </script>";
                exit;
            } else {
                $pesan_notifikasi = "Gagal memproses transaksi.";
            }
        }

        // Panggil tampilan form transaksi baru
        include 'views/transaksi_baru.php';
        break;

    case 'riwayat_transaksi':
        // --- LOGIKA HALAMAN DAFTAR RIWAYAT TRANSAKSI ---
        
        // Ambil semua data riwayat dari database via controller
        $riwayat = $transaksiCtrl->riwayat();
        
        // Panggil tampilan tabel riwayat transaksi
        include 'views/riwayat_transaksi.php';
        break;

    case 'detail_transaksi':
        // --- LOGIKA HALAMAN NOTA / STRUK DETAIL TRANSAKSI ---
        
        // Tangkap ID Transaksi dari parameter URL (index.php?page=detail_transaksi&id=...)
        $id_transaksi = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        $data_detail  = $transaksiCtrl->detail($id_transaksi);

        // Jika ID transaksi salah atau tidak ditemukan, kembalikan ke daftar riwayat
        if (!$data_detail || !$data_detail['transaksi']) {
            header("Location: index.php?page=riwayat_transaksi");
            exit;
        }

        // Pecah data untuk dikirimkan ke file view
        $transaksi = $data_detail['transaksi'];
        $items     = $data_detail['detail'];

        // Panggil tampilan rincian nota struk belanjaan
        include 'views/detail_transaksi.php';
        break;

    case 'hapus_transaksi':
        // --- LOGIKA AKSI HAPUS TRANSAKSI ---
        
        // Tangkap ID Transaksi dari parameter URL (index.php?page=hapus_transaksi&id=...)
        $id_hapus = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        
        if ($id_hapus > 0) {
            if ($transaksiCtrl->prosesHapus($id_hapus)) {
                echo "<script>alert('Transaksi berhasil dihapus!'); window.location.href='index.php?page=riwayat_transaksi';</script>";
                exit;
            } else {
                echo "<script>alert('Gagal menghapus transaksi.'); window.location.href='index.php?page=riwayat_transaksi';</script>";
                exit;
            }
        } else {
            header("Location: index.php?page=riwayat_transaksi");
            exit;
        }
        break;

    case 'edit_transaksi':
        // --- LOGIKA HALAMAN EDIT TRANSAKSI ---
        
        // Tangkap ID Transaksi dari URL (index.php?page=edit_transaksi&id=...)
        $id_transaksi = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        
        // Ambil rincian data transaksi lama beserta item detailnya
        $data_detail = $transaksiCtrl->detail($id_transaksi);
        
        // Jika data transaksi tidak valid atau tidak ditemukan di database
        if (!$data_detail || !$data_detail['transaksi']) {
            header("Location: index.php?page=riwayat_transaksi");
            exit;
        }

        $transaksi  = $data_detail['transaksi'];
        $items_lama = $data_detail['detail'];

        // Ambil list data barang dan layanan aktif untuk mengisi elemen select option
        $formData = $transaksiCtrl->getFormData();

        // Cek jika kasir menekan tombol "Simpan Perubahan" dari form edit
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_transaksi'])) {
            if ($transaksiCtrl->prosesEdit($id_transaksi, $_POST)) {
                echo "<script>
                    alert('Transaksi berhasil diperbarui!');
                    window.location.href = 'index.php?page=detail_transaksi&id=" . $id_transaksi . "';
                </script>";
                exit;
            } else {
                $pesan_notifikasi = "Gagal memperbarui data transaksi.";
            }
        }

        // Panggil file interface form editor dari folder views/
        include 'views/edit_transaksi.php';
        break;

    case 'laporan':
        // --- LOGIKA HALAMAN LAPORAN KEUANGAN DINAMIS ---
        // Mendukung penyaringan multi-mode (Hari, Minggu/7 Hari terakhir, Bulan, Tahun)
        $tipe_filter   = isset($_GET['tipe']) ? $_GET['tipe'] : 'bulan';
        $tgl_pilihan   = isset($_GET['tanggal']) ? $_GET['tanggal'] : date('Y-m-d');
        $bulan_pilihan = isset($_GET['bulan']) ? (int)$_GET['bulan'] : (int)date('m');
        $tahun_pilihan = isset($_GET['tahun']) ? (int)$_GET['tahun'] : (int)date('Y');

        // Ambil hasil kalkulasi akuntansi dan detail transaksi dari controller dengan parameter dinamis baru
        $data_laporan = $transaksiCtrl->laporan($tipe_filter, $tgl_pilihan, $bulan_pilihan, $tahun_pilihan);

        // Panggil tampilan visual laporan keuangan dari folder views/
        include 'views/laporan_keuangan.php';
        break;

    case 'update_status':
        // --- PEMBARUAN: LOGIKA AKSI UBAH STATUS ALUR PENGERJAAN BY ADMIN ---
        $id_transaksi = isset($_POST['id_transaksi']) ? (int)$_POST['id_transaksi'] : 0;
        $status_baru  = isset($_POST['status_baru']) ? $_POST['status_baru'] : '';

        if ($id_transaksi > 0 && !empty($status_baru)) {
            if ($transaksiCtrl->prosesUpdateStatus($id_transaksi, $status_baru)) {
                echo "<script>
                    alert('Status pengerjaan transaksi berhasil diperbarui!');
                    window.location.href = 'index.php?page=detail_transaksi&id=" . $id_transaksi . "';
                </script>";
                exit;
            } else {
                echo "<script>alert('Gagal memperbarui status transaksi.'); window.location.href='index.php?page=riwayat_transaksi';</script>";
                exit;
            }
        } else {
            header("Location: index.php?page=riwayat_transaksi");
            exit;
        }
        break;

    case 'cek_status':
        // --- PEMBARUAN LOGIKA: DIRECT FETCH DATA REALTIME BERDASARKAN KODE NOTA NOTA ---
        $kode_nota = isset($_GET['nota']) ? trim($_GET['nota']) : '';
        
        $transaksi = null;
        $items = [];

        if (!empty($kode_nota)) {
            $db = (new Database())->getConnection();
            
            // 1. Ambil data INDUK transaksi secara murni langsung menggunakan kata kunci KODE NOTA (Toleran Whitespace)
            $stmtTrx = $db->prepare("SELECT t.*, u.username AS nama_kasir 
                                     FROM transaksi t
                                     LEFT JOIN users u ON t.kasir_id = u.id 
                                     WHERE t.kode_transaksi LIKE :kode LIMIT 1");
            $stmtTrx->execute([':kode' => '%' . $kode_nota . '%']);
            $transaksi = $stmtTrx->fetch(PDO::FETCH_ASSOC);

            // 2. Jika data induk berhasil didapatkan, lakukan query pencarian rincian detail item
            if ($transaksi) {
                $id_transaksi = $transaksi['id'];
                
                $stmtItems = $db->prepare("SELECT * FROM detail_transaksi WHERE id_transaksi = :id_trx");
                $stmtItems->execute([':id_trx' => $id_transaksi]);
                $items = $stmtItems->fetchAll(PDO::FETCH_ASSOC);
            }
        }

        // Panggil file tampilan pelacakan status pelanggan publik
        include 'views/cek_status.php';
        break;

    case 'kelola_barang':
        // --- LOGIKA HALAMAN KELOLA STOK / BARANG ---
        include 'views/kelola_barang.php';
        break;

    default:
        // --- TAMPILAN JIKA HALAMAN TIDAK ADA (ERROR 404) ---
        echo "<div style='text-align:center; margin-top:100px; font-family:Arial,sans-serif;'>
                <h1 style='font-size:48px; color:#dc3545; margin-bottom:10px;'>404</h1>
                <p style='font-size:18px; color:#6c757d;'>Halaman yang Anda cari tidak ditemukan.</p>
                <a href='index.php' style='color:#007bff; text-decoration:none; font-weight:bold;'>← Kembali ke Dashboard</a>
              </div>";
        break;
}