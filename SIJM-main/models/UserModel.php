<?php
class UserModel
{
    private $conn;

    public function __construct($db)
    {
        $this->conn = $db;
    }

    // Mengambil data user berdasarkan username dan password
    public function getUser($username, $password)
    {
        $query = "SELECT id, username, role FROM users WHERE username = :username AND password = :password LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':username', $username);

        // Catatan: Di sistem nyata gunakan password_hash() & password_verify(). 
        // Disini kita pakai plain text sesuai dummy data awal.
        $stmt->bindParam(':password', $password);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
