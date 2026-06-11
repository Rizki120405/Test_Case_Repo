<?php
// Menangkap parameter 'page' dari URL router utama (index.php?page=...)
// Jika tidak ada parameter 'page', maka default halaman dianggap 'dashboard'
$current_page = isset($_GET['page']) ? $_GET['page'] : 'dashboard';

error_reporting(E_ALL);
ini_set('display_errors', 1);

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
?>

<div class="md:hidden bg-blue-800 text-white flex justify-between items-center p-4 shadow-md w-full sticky top-0 z-50">
    <div class="font-bold text-lg tracking-wider">Ikhsan Jaya Motor</div>
    <button onclick="toggleSidebar()" class="focus:outline-none">
        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
        </svg>
    </button>
</div>

<div id="sidebar-backdrop" onclick="toggleSidebar()" class="fixed inset-0 bg-black opacity-50 z-40 hidden transition-opacity md:hidden"></div>

<div id="sidebar" class="bg-blue-800 shadow-xl w-64 min-h-screen flex flex-col justify-between fixed inset-y-0 left-0 transform -translate-x-full md:relative md:translate-x-0 transition-transform duration-300 ease-in-out z-50">
    <div>
        <div class="flex items-center justify-between p-6 md:block border-b border-blue-700 md:border-none">
            <h1 class="text-white text-2xl font-bold hidden md:block">Ikhsan Jaya Motor</h1>
            <h1 class="text-white text-xl font-bold md:hidden">Menu Navigasi</h1>
            <button onclick="toggleSidebar()" class="md:hidden text-blue-200 hover:text-white focus:outline-none">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>

        <div class="px-6 py-4 border-b border-blue-700">
            <p class="text-white text-sm">Halo, <span class="font-bold capitalize"><?= htmlspecialchars($_SESSION['username'] ?? 'User') ?></span></p>
            <span class="bg-blue-600 text-xs text-white px-2 py-1 rounded mt-1 inline-block uppercase"><?= htmlspecialchars($_SESSION['role'] ?? 'Role') ?></span>
        </div>

        <nav class="text-white text-base font-semibold pt-3 space-y-1 overflow-y-auto">
            
            <a href="index.php?page=dashboard" class="flex items-center text-white <?= $current_page == 'dashboard' ? 'bg-blue-900 border-l-4 border-white' : 'opacity-75 hover:opacity-100 hover:bg-blue-700' ?> py-4 pl-6 nav-item transition-all duration-200">
                <span class="mr-3">📊</span> Dashboard
            </a>
            
            <a href="index.php?page=transaksi_baru" class="flex items-center text-white <?= $current_page == 'transaksi_baru' ? 'bg-blue-900 border-l-4 border-white' : 'opacity-75 hover:opacity-100 hover:bg-blue-700' ?> py-4 pl-6 nav-item transition-all duration-200">
                <span class="mr-3">🛒</span> Transaksi Baru
            </a>

            <a href="index.php?page=riwayat_transaksi" class="flex items-center text-white <?= ($current_page == 'riwayat_transaksi' || $current_page == 'detail_transaksi' || $current_page == 'edit_transaksi') ? 'bg-blue-900 border-l-4 border-white' : 'opacity-75 hover:opacity-100 hover:bg-blue-700' ?> py-4 pl-6 nav-item transition-all duration-200">
                <span class="mr-3">📜</span> Riwayat Transaksi
            </a>
            
            <a href="index.php?page=kelola_barang" class="flex items-center text-white <?= $current_page == 'kelola_barang' ? 'bg-blue-900 border-l-4 border-white' : 'opacity-75 hover:opacity-100 hover:bg-blue-700' ?> py-4 pl-6 nav-item transition-all duration-200">
                <span class="mr-3">📦</span> Kelola Barang & Stok
            </a>

            <a href="index.php?page=laporan" class="flex items-center text-white <?= $current_page == 'laporan' ? 'bg-blue-900 border-l-4 border-white' : 'opacity-75 hover:opacity-100 hover:bg-blue-700' ?> py-4 pl-6 nav-item transition-all duration-200">
                <span class="mr-3">📈</span> Laporan Keuangan
            </a>
            
        </nav>
    </div>

    <div class="p-4 border-t border-blue-700">
        <a href="index.php?page=logout" onclick="return confirm('Yakin ingin keluar?');" class="flex items-center justify-center bg-red-600 hover:bg-red-700 text-white py-2 rounded shadow transition duration-200 font-bold text-sm">
            <span class="mr-2">🚪</span> Keluar
        </a>
    </div>
</div>

<script>
    function toggleSidebar() {
        const sidebar = document.getElementById('sidebar');
        const backdrop = document.getElementById('sidebar-backdrop');

        // Geser sidebar masuk/keluar layar
        sidebar.classList.toggle('-translate-x-full');
        // Muncul/hilangkan lapisan backdrop gelap
        backdrop.classList.toggle('hidden');
    }
</script>