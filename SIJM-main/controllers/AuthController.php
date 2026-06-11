<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require_once 'config/Database.php';
require_once 'models/UserModel.php';

class AuthController
{
    private $userModel;

    public function __construct()
    {
        $database = new Database();
        $db = $database->getConnection();
        $this->userModel = new UserModel($db);
    }

    /**
     * Proses Verifikasi Login
     */
    public function login($username, $password)
    {
        $user = $this->userModel->getUser($username, $password);

        if ($user) {
            // Set session jika berhasil login
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];
            return true;
        }
        return false;
    }

    /**
     * Proteksi Halaman (Middleware Auth)
     * Mengarahkan ke parameter login router jika belum ada session
     */
    public function checkAuth()
    {
        if (!isset($_SESSION['user_id'])) {
            // Mengarahkan ke index.php dengan parameter halaman login
            header("Location: index.php?page=login");
            exit;
        }
    }

    /**
     * Proses Keluar Aplikasi (Clear Session)
     */
    public function logout()
    {
        // Hapus semua data session
        $_SESSION = array();
        
        // Hancurkan session fisik
        session_destroy();
        
        // Alihkan kembali ke halaman login utama melalui router
        header("Location: index.php?page=login");
        exit;
    }
}