<?php
require_once 'config/Database.php';
require_once 'models/DashboardModel.php';

class DashboardController
{
    private $dashboardModel;

    public function __construct()
    {
        $database = new Database();
        $db = $database->getConnection();
        $this->dashboardModel = new DashboardModel($db);
    }

    // Mengambil dan merangkum semua data untuk Dashboard
    public function getDashboardData()
    {
        $bulan_ini = date('Y-m');

        $pendapatan = $this->dashboardModel->getPendapatanBulanIni($bulan_ini);
        $pengeluaran = $this->dashboardModel->getPengeluaranBulanIni($bulan_ini);

        return [
            'pendapatan' => $pendapatan,
            'pengeluaran' => $pengeluaran,
            'laba_bersih' => $pendapatan - $pengeluaran,
            'stok_menipis' => $this->dashboardModel->getStokMenipis(5),
            'karpet_aktif' => $this->dashboardModel->getKarpetAktif()
        ];
    }
}
