<?php
class DashboardModel
{
    private $conn;

    public function __construct($db)
    {
        $this->conn = $db;
    }

    // Mengambil total pendapatan bulan ini
    public function getPendapatanBulanIni($bulan)
    {
        try {
            // [PERBAIKAN]: Mencoba menghitung transaksi dengan filter status = 'selesai'
            $query = "SELECT SUM(total_bayar) as total FROM transaksi 
                      WHERE DATE_FORMAT(tanggal, '%Y-%m') = :bulan 
                      AND status = 'selesai'";

            $stmt = $this->conn->prepare($query);
            $stmt->execute([':bulan' => $bulan]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            return $row['total'] ? $row['total'] : 0;
        } catch (\Throwable $e) {
            // FALLBACK: Gunakan \Throwable agar semua jenis error tertangkap.
            // Kembali ke query lama tanpa status.
            $query = "SELECT SUM(total_bayar) as total FROM transaksi 
                      WHERE DATE_FORMAT(tanggal, '%Y-%m') = :bulan";

            $stmt = $this->conn->prepare($query);
            $stmt->execute([':bulan' => $bulan]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            return $row['total'] ? $row['total'] : 0;
        }
    }

    // --- FITUR BARU: Ambil Pengeluaran Bulan Ini ---
    public function getPengeluaranBulanIni($bulan)
    {
        try {
            $query = "SELECT SUM(nominal) as total FROM arus_kas 
                      WHERE DATE_FORMAT(tanggal, '%Y-%m') = :bulan 
                      AND jenis = 'Pengeluaran'";

            $stmt = $this->conn->prepare($query);
            $stmt->execute([':bulan' => $bulan]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            return $row['total'] ? $row['total'] : 0;
        } catch (\Throwable $e) {
            return 0; // Jika tabel belum dibuat, return 0 agar tidak error
        }
    }

    // Mengambil barang dengan stok menipis (dibawah batas tertentu)
    public function getStokMenipis($limit)
    {
        try {
            $query = "SELECT nama_barang, stok FROM barang WHERE stok <= :limit ORDER BY stok ASC";
            $stmt = $this->conn->prepare($query);
            $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
            $stmt->execute();

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\Throwable $e) {
            return []; // Jika error, kembalikan array kosong
        }
    }

    // Mengambil antrian cucian karpet yang belum diambil
    public function getKarpetAktif()
    {
        try {
            // Mencoba query lengkap
            $query = "SELECT * FROM cucian_karpet WHERE status != 'diambil' ORDER BY tanggal_selesai ASC";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\Throwable $e) {
            try {
                // FALLBACK: Jika tanggal_selesai atau status tidak ada, ambil seadanya saja
                $query = "SELECT * FROM cucian_karpet";
                $stmt = $this->conn->prepare($query);
                $stmt->execute();

                return $stmt->fetchAll(PDO::FETCH_ASSOC);
            } catch (\Throwable $e2) {
                // Jika masih error juga (misal tabel belum ada), cegah crash total
                return [];
            }
        }
    }
}
