<?php
// models/TransaksiModel.php

class TransaksiModel
{
    private $conn;

    public function __construct($db)
    {
        $this->conn = $db;
    }

    /**
     * Ambil semua barang yang stoknya masih ada (>0)
     */
    public function getAllBarang()
    {
        $query = "SELECT * FROM barang WHERE stok > 0";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Ambil semua layanan jasa / tindakan bengkel
     */
    public function getAllLayanan()
    {
        $query = "SELECT * FROM layanan";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // ==================== PENANGANAN DATA RIWAYAT TRANSAKSI ====================

    /**
     * Ambil semua daftar riwayat transaksi (Gabung dengan nama kasir pembawa transaksi)
     */
    public function getAllRiwayat()
    {
        $query = "SELECT t.*, u.username AS nama_kasir 
                  FROM transaksi t
                  LEFT JOIN users u ON t.kasir_id = u.id 
                  ORDER BY t.tanggal DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Ambil satu data induk transaksi spesifik berdasarkan ID utama
     */
    public function getTransaksiById($id_transaksi)
    {
        $query = "SELECT t.*, u.username AS nama_kasir 
                  FROM transaksi t
                  LEFT JOIN users u ON t.kasir_id = u.id 
                  WHERE t.id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([':id' => $id_transaksi]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Ambil seluruh item detail (barang/jasa) dari sebuah transaksi tertentu
     */
    public function getDetailTransaksi($id_transaksi)
    {
        $query = "SELECT * FROM detail_transaksi WHERE id_transaksi = :id_trx";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([':id_trx' => $id_transaksi]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Menghapus data transaksi beserta detailnya secara aman (Safe Delete)
     * Menggunakan Database Transaction untuk menghindari Error Foreign Key Constraint Violation.
     */
    public function hapusTransaksi($id_transaksi)
    {
        try {
            // Mulai mode aman transaction
            $this->conn->beginTransaction();

            // 1. Hapus data anak terlebih dahulu di tabel detail_transaksi
            $queryDetail = "DELETE FROM detail_transaksi WHERE id_transaksi = :id";
            $stmtDetail = $this->conn->prepare($queryDetail);
            $stmtDetail->execute([':id' => $id_transaksi]);

            // 2. Hapus data induk di tabel transaksi
            $queryUtama = "DELETE FROM transaksi WHERE id = :id";
            $stmtUtama = $this->conn->prepare($queryUtama);
            $stmtUtama->execute([':id' => $id_transaksi]);

            // Jika kedua proses di atas berhasil tanpa error, terapkan perubahan ke database
            $this->conn->commit();
            return true;
        } catch (Exception $e) {
            // Jika salah satu proses gagal, batalkan semua perubahan agar data tidak korup
            $this->conn->rollBack();
            die("Gagal menghapus data riwayat transaksi: " . $e->getMessage());
            return false;
        }
    }

    // ===========================================================================

    /**
     * Proses simpan transaksi baru ke banyak tabel sekaligus (Database Transaction)
     */
    public function buatTransaksi($dataTrx, $items, $kasir_id)
    {
        try {
            $this->conn->beginTransaction(); // Mulai mode aman (jika gagal satu, batal semua)

            // 1. Simpan ke tabel transaksi (Header)
            $kode_trx = 'TRX-' . date('YmdHis');
            $queryTrx = "INSERT INTO transaksi (kode_transaksi, nama_pelanggan, plat_nomor, total_bayar, kasir_id) 
                         VALUES (:kode, :nama, :plat, :total, :kasir)";
            $stmtTrx = $this->conn->prepare($queryTrx);
            $stmtTrx->execute([
                ':kode' => $kode_trx,
                ':nama' => $dataTrx['nama_pelanggan'],
                ':plat' => $dataTrx['plat_nomor'],
                ':total' => $dataTrx['total_bayar'],
                ':kasir' => $kasir_id
            ]);

            $id_transaksi = $this->conn->lastInsertId(); // Ambil ID transaksi yang baru dibuat

            // 2. Loop item yang dibeli (Simpan ke detail_transaksi)
            foreach ($items as $item) {
                // Jika jenisnya manual, id_item kita set 0. Jika bukan, pakai id_item dari database
                $id_item = ($item['jenis'] == 'manual') ? 0 : $item['id_item'];
                $nama_item = isset($item['nama_item']) ? $item['nama_item'] : '';

                $queryDetail = "INSERT INTO detail_transaksi (id_transaksi, jenis_item, id_item, nama_item, jumlah, harga_satuan, subtotal) 
                                VALUES (:id_trx, :jenis, :id_item, :nama_item, :qty, :harga, :subtotal)";
                $stmtDetail = $this->conn->prepare($queryDetail);
                $stmtDetail->execute([
                    ':id_trx'    => $id_transaksi,
                    ':jenis'     => $item['jenis'],
                    ':id_item'   => $id_item,
                    ':nama_item' => $nama_item,
                    ':qty'       => $item['qty'],
                    ':harga'     => $item['harga'],
                    ':subtotal'  => $item['subtotal']
                ]);

                // 3. Jika itu Barang, kurangi stok dan catat ke riwayat_stok
                if ($item['jenis'] == 'barang') {
                    // Kurangi stok barang
                    $queryUpdate = "UPDATE barang SET stok = stok - :qty WHERE id = :id";
                    $stmtUpdate = $this->conn->prepare($queryUpdate);
                    $stmtUpdate->execute([':qty' => $item['qty'], ':id' => $item['id_item']]);

                    // Catat riwayat log masuk/keluar stok
                    $queryRiwayat = "INSERT INTO riwayat_stok (id_barang, jenis, jumlah, keterangan) 
                                     VALUES (:id_barang, 'keluar', :qty, :ket)";
                    $stmtRiwayat = $this->conn->prepare($queryRiwayat);
                    $stmtRiwayat->execute([
                        ':id_barang' => $item['id_item'],
                        ':qty'       => $item['qty'],
                        ':ket'       => "Terjual di kasir (Struk: $kode_trx)"
                    ]);
                }
            }

            $this->conn->commit(); // Simpan permanen
            return true;
        } catch (Exception $e) {
            $this->conn->rollBack(); // Batalkan jika ada yang error
            die("Gagal menyimpan transaksi: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Memperbarui data transaksi beserta item detailnya (Database Transaction)
     */
    public function updateTransaksi($id_transaksi, $dataTrx, $items)
    {
        try {
            $this->conn->beginTransaction(); // Mulai mode aman transaction

            // 1. Update data induk pada tabel transaksi (Nama, Plat, & Total Baru)
            $queryTrx = "UPDATE transaksi SET 
                            nama_pelanggan = :nama, 
                            plat_nomor = :plat, 
                            total_bayar = :total 
                         WHERE id = :id";
            $stmtTrx = $this->conn->prepare($queryTrx);
            $stmtTrx->execute([
                ':nama'  => $dataTrx['nama_pelanggan'],
                ':plat'  => $dataTrx['plat_nomor'],
                ':total' => $dataTrx['total_bayar'],
                ':id'    => $id_transaksi
            ]);

            // 2. Ambil item lama terlebih dahulu untuk mengembalikan stok barang sebelum dihapus
            $queryGetOld = "SELECT id_item, jumlah FROM detail_transaksi WHERE id_transaksi = :id_trx AND jenis_item = 'barang'";
            $stmtGetOld = $this->conn->prepare($queryGetOld);
            $stmtGetOld->execute([':id_trx' => $id_transaksi]);
            $oldItems = $stmtGetOld->fetchAll(PDO::FETCH_ASSOC);

            // Kembalikan stok lama agar kalkulasi stok barang kembali akurat
            foreach ($oldItems as $oldItem) {
                $queryRestore = "UPDATE barang SET stok = stok + :old_qty WHERE id = :id_item";
                $stmtRestore = $this->conn->prepare($queryRestore);
                $stmtRestore->execute([':old_qty' => $oldItem['jumlah'], ':id_item' => $oldItem['id_item']]);
            }

            // 3. Hapus seluruh detail rincian transaksi yang lama
            $queryDeleteDetail = "DELETE FROM detail_transaksi WHERE id_transaksi = :id_trx";
            $stmtDelete = $this->conn->prepare($queryDeleteDetail);
            $stmtDelete->execute([':id_trx' => $id_transaksi]);

            // 4. Masukkan item detail yang baru dikonfigurasi (Looping Insert)
            foreach ($items as $item) {
                $id_item = ($item['jenis'] == 'manual') ? 0 : $item['id_item'];
                $nama_item = isset($item['nama_item']) ? $item['nama_item'] : '';

                $queryDetail = "INSERT INTO detail_transaksi (id_transaksi, jenis_item, id_item, nama_item, jumlah, harga_satuan, subtotal) 
                                VALUES (:id_trx, :jenis, :id_item, :nama_item, :qty, :harga, :subtotal)";
                $stmtDetail = $this->conn->prepare($queryDetail);
                $stmtDetail->execute([
                    ':id_trx'    => $id_transaksi,
                    ':jenis'     => $item['jenis'],
                    ':id_item'   => $id_item,
                    ':nama_item' => $nama_item,
                    ':qty'       => $item['qty'],
                    ':harga'     => $item['harga'],
                    ':subtotal'  => $item['subtotal']
                ]);

                // 5. Jika item baru adalah barang, kurangi kembali stoknya berdasarkan qty yang baru
                if ($item['jenis'] == 'barang') {
                    $queryUpdateStok = "UPDATE barang SET stok = stok - :new_qty WHERE id = :id_item";
                    $stmtUpdateStok = $this->conn->prepare($queryUpdateStok);
                    $stmtUpdateStok->execute([':new_qty' => $item['qty'], ':id_item' => $id_item]);
                }
            }

            $this->conn->commit(); // Simpan seluruh rangkaian perubahan ke database
            return true;
        } catch (Exception $e) {
            $this->conn->rollBack(); // Batalkan semua jika di tengah jalan ada satu proses saja yang gagal
            die("Gagal memperbarui transaksi: " . $e->getMessage());
            return false;
        }
    }

    // ==================== PEMBARUAN: AKUNTANSI & REKAP STATUS ====================
    
    /**
     * PEMBARUAN: Memperbarui status alur pengerjaan transaksi secara realtime oleh Admin
     */
    public function updateStatusTransaksi($id_transaksi, $status_baru)
    {
        try {
            $query = "UPDATE transaksi SET status = :status WHERE id = :id";
            $stmt = $this->conn->prepare($query);
            return $stmt->execute([
                ':status' => $status_baru,
                ':id'     => $id_transaksi
            ]);
        } catch (Exception $e) {
            die("Gagal memperbarui status transaksi: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Mengambil data laporan keuangan secara fleksibel (Hari, Minggu, Bulan, Tahun)
     */
    public function getLaporanFleksibel($tipe, $tanggal, $bulan, $tahun)
    {
        // Pangkalan dasar query untuk seluruh penjualan masuk aktif (termasuk status pengerjaan bertahap)
        $queryBase = "SELECT t.*, u.username AS nama_kasir 
                      FROM transaksi t
                      LEFT JOIN users u ON t.kasir_id = u.id WHERE 1=1";
        $params = [];

        // Sesuaikan WHERE klausa berdasarkan tipe filter penanggalan
        if ($tipe === 'hari') {
            $queryBase .= " AND DATE(t.tanggal) = :tanggal";
            $params[':tanggal'] = $tanggal;
        } elseif ($tipe === 'minggu') {
            // Filter berdasarkan rentang 7 hari ke belakang dari tanggal yang dipilih
            $queryBase .= " AND DATE(t.tanggal) BETWEEN DATE_SUB(:tanggal, INTERVAL 6 DAY) AND :tanggal";
            $params[':tanggal'] = $tanggal;
        } elseif ($tipe === 'tahun') {
            $queryBase .= " AND YEAR(t.tanggal) = :tahun";
            $params[':tahun'] = $tahun;
        } else { 
            // Default: rekap bulanan
            $queryBase .= " AND MONTH(t.tanggal) = :bulan AND YEAR(t.tanggal) = :tahun";
            $params[':bulan'] = str_pad($bulan, 2, '0', STR_PAD_LEFT);
            $params[':tahun'] = $tahun;
        }

        $queryBase .= " ORDER BY t.tanggal ASC";
        
        // Eksekusi query rincian penjualan masuk
        $stmtList = $this->conn->prepare($queryBase);
        $stmtList->execute($params);
        $list_transaksi = $stmtList->fetchAll(PDO::FETCH_ASSOC);

        // Kalkulasi Total Pemasukan Kotor (Omzet) dari baris transaksi yang ditemukan
        $pemasukan = 0;
        foreach ($list_transaksi as $row) {
            $pemasukan += $row['total_bayar'];
        }

        // Kalkulasi Pengeluaran Operasional (jika tabel 'pengeluaran' ada)
        $pengeluaran = 0;
        if ($this->tableExists('pengeluaran')) {
            $queryPengeluaran = "SELECT SUM(nominal) as total FROM pengeluaran WHERE 1=1";
            $paramsPengeluaran = [];

            if ($tipe === 'hari') {
                $queryPengeluaran .= " AND DATE(tanggal) = :tanggal";
                $paramsPengeluaran[':tanggal'] = $tanggal;
            } elseif ($tipe === 'minggu') {
                $queryPengeluaran .= " AND DATE(tanggal) BETWEEN DATE_SUB(:tanggal, INTERVAL 6 DAY) AND :tanggal";
                $paramsPengeluaran[':tanggal'] = $tanggal;
            } elseif ($tipe === 'tahun') {
                $queryPengeluaran .= " AND YEAR(tanggal) = :tahun";
                $paramsPengeluaran[':tahun'] = $tahun;
            } else {
                $queryPengeluaran .= " AND MONTH(tanggal) = :bulan AND YEAR(tanggal) = :tahun";
                $paramsPengeluaran[':bulan'] = str_pad($bulan, 2, '0', STR_PAD_LEFT);
                $paramsPengeluaran[':tahun'] = $tahun;
            }

            $stmtExpense = $this->conn->prepare($queryPengeluaran);
            $stmtExpense->execute($paramsPengeluaran);
            $pengeluaran = $stmtExpense->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;
        }

        return [
            'pemasukan'   => $pemasukan,
            'pengeluaran' => $pengeluaran,
            'laba_bersih' => $pemasukan - $pengeluaran,
            'detail'      => $list_transaksi
        ];
    }

    /**
     * Helper untuk memeriksa keberadaan tabel di database
     */
    private function tableExists($table) 
    {
        try {
            $result = $this->conn->query("SELECT 1 FROM {$table} LIMIT 1");
            return $result !== false;
        } catch (Exception $e) {
            return false;
        }
    }
}