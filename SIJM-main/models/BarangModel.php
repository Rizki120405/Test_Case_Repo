<?php
class BarangModel
{
    private $conn;
    function compressAndResizeImage($sourcePath, $destinationPath, $maxWidth = 800, $quality = 75)
    {
        $imgInfo = getimagesize($sourcePath);
        if (!$imgInfo) return false;
        $mime = $imgInfo['mime'];
        $width = $imgInfo[0];
        $height = $imgInfo[1];

        switch ($mime) {
            case 'image/jpeg':
                $image = imagecreatefromjpeg($sourcePath);
                break;
            case 'image/png':
                $image = imagecreatefrompng($sourcePath);
                break;
            case 'image/gif':
                $image = imagecreatefromgif($sourcePath);
                break;
            default:
                return false;
        }

        if ($width > $maxWidth) {
            $ratio = $maxWidth / $width;
            $newWidth = $maxWidth;
            $newHeight = $height * $ratio;
        } else {
            $newWidth = $width;
            $newHeight = $height;
        }

        $imageResized = imagecreatetruecolor($newWidth, $newHeight);

        if ($mime == 'image/png' || $mime == 'image/gif') {
            imagecolortransparent($imageResized, imagecolorallocatealpha($imageResized, 0, 0, 0, 127));
            imagealphablending($imageResized, false);
            imagesavealpha($imageResized, true);
        }

        imagecopyresampled($imageResized, $image, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);

        switch ($mime) {
            case 'image/jpeg':
                imagejpeg($imageResized, $destinationPath, $quality);
                break;
            case 'image/png':
                $pngQuality = round((100 - $quality) / 10);
                imagepng($imageResized, $destinationPath, $pngQuality);
                break;
            case 'image/gif':
                imagegif($imageResized, $destinationPath);
                break;
        }

        imagedestroy($image);
        imagedestroy($imageResized);
        return true;
    }


    // Constructor fleksibel
    public function __construct($db = null)
    {
        if ($db !== null) {
            $this->conn = $db;
        } else {
            require_once 'config/database.php';
            $database = new Database();
            $this->conn = $database->getConnection();
        }
    }

    // Mengambil semua data barang
    // public function getAllBarang()
    // {
    //     $query = "SELECT * FROM barang ORDER BY id DESC";
    //     $stmt = $this->conn->prepare($query);
    //     $stmt->execute();
    //     return $stmt->fetchAll(PDO::FETCH_ASSOC);
    // }
    public function getAllBarang($keyword = '')
    {
        try {
            // 1. Cek apakah ada kata kunci pencarian yang dikirim
            if (!empty($keyword)) {
                // Jika ADA pencarian: Filter data menggunakan WHERE dan LIKE
                $query = "SELECT * FROM barang 
                          WHERE nama_barang LIKE :keyword 
                          OR kode_barang LIKE :keyword 
                          OR kategori LIKE :keyword 
                          ORDER BY id DESC";

                $stmt = $this->conn->prepare($query);

                // Tambahkan tanda % di awal dan akhir agar bisa mencari penggalan kata
                $stmt->bindValue(':keyword', "%$keyword%");
            } else {
                // Jika TIDAK ADA pencarian (keyword kosong): Tampilkan semua data
                $query = "SELECT * FROM barang ORDER BY id DESC";
                $stmt = $this->conn->prepare($query);
            }

            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            // Tampilkan error jika terjadi masalah pada query database
            die("Error pada getAllBarang: " . $e->getMessage());
        }
    }

    // Mengambil data barang berdasarkan ID
    public function getBarangById($id)
    {
        try {
            $query = "SELECT * FROM barang WHERE id = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindValue(':id', $id);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            die("Error getBarangById: " . $e->getMessage());
        }
    }

    // Menambahkan barang dengan query dinamis
    public function tambahBarang($data)
    {
        if (isset($data['submit'])) unset($data['submit']);

        $columns = implode(", ", array_keys($data));
        $placeholders = ":" . implode(", :", array_keys($data));

        $query = "INSERT INTO barang ($columns) VALUES ($placeholders)";
        $stmt = $this->conn->prepare($query);

        return $stmt->execute($data);
    }

    // Memperbarui data barang dengan query dinamis
    public function updateBarang($id, $data)
    {
        try {
            $query = "UPDATE barang SET 
                        kode_barang = :kode_barang, 
                        nama_barang = :nama_barang, 
                        kategori = :kategori, 
                        deskripsi = :deskripsi, 
                        stok = :stok, 
                        harga_modal = :harga_modal, 
                        harga_jual = :harga_jual";

            // Jika ada gambar baru yang diupload, update juga kolom gambar
            if (isset($data['gambar'])) {
                $query .= ", gambar = :gambar";
            }

            $query .= " WHERE id = :id";
            $stmt = $this->conn->prepare($query);

            $stmt->bindValue(':kode_barang', $data['kode_barang']);
            $stmt->bindValue(':nama_barang', $data['nama_barang']);
            $stmt->bindValue(':kategori', $data['kategori']);
            $stmt->bindValue(':deskripsi', $data['deskripsi']);
            $stmt->bindValue(':stok', $data['stok']);
            $stmt->bindValue(':harga_modal', $data['harga_modal']);
            $stmt->bindValue(':harga_jual', $data['harga_jual']);
            $stmt->bindValue(':id', $id);

            if (isset($data['gambar'])) {
                $stmt->bindValue(':gambar', $data['gambar']);
            }

            return $stmt->execute();
        } catch (PDOException $e) {
            die("Error updateBarang: " . $e->getMessage());
        }
    }

    // Menghapus barang
    public function hapusBarang($id)
    {
        try {
            $this->conn->beginTransaction();

            $queryRiwayat = "DELETE FROM riwayat_stok WHERE id_barang = :id";
            $stmtRiwayat = $this->conn->prepare($queryRiwayat);
            $stmtRiwayat->execute([':id' => $id]);

            $queryBarang = "DELETE FROM barang WHERE id = :id";
            $stmtBarang = $this->conn->prepare($queryBarang);
            $stmtBarang->execute([':id' => $id]);

            $this->conn->commit();
            return true;
        } catch (PDOException $e) {
            $this->conn->rollBack();
            return false;
        }
    }

    // [FITUR BARU] Menambah stok dan mencatat riwayatnya
    public function tambahStok($id_barang, $jumlah_tambah, $keterangan)
    {
        try {
            $this->conn->beginTransaction();

            // 1. Tambahkan jumlah stok di tabel barang
            $queryUpdate = "UPDATE barang SET stok = stok + :jumlah_tambah WHERE id = :id_barang";
            $stmtUpdate = $this->conn->prepare($queryUpdate);
            $stmtUpdate->execute([
                ':jumlah_tambah' => $jumlah_tambah,
                ':id_barang' => $id_barang
            ]);

            // 2. Catat penambahan ini ke tabel riwayat_stok
            $queryRiwayat = "INSERT INTO riwayat_stok (id_barang, jumlah, keterangan) VALUES (:id_barang, :jumlah, :keterangan)";
            $stmtRiwayat = $this->conn->prepare($queryRiwayat);
            $stmtRiwayat->execute([
                ':id_barang' => $id_barang,
                ':jumlah' => $jumlah_tambah,
                ':keterangan' => $keterangan
            ]);

            $this->conn->commit();
            return true;
        } catch (PDOException $e) {
            $this->conn->rollBack();

            // =========================================================
            // DEBUGGING: TAMPILKAN ERROR ASLI DARI DATABASE
            // =========================================================
            die("<h1>ERROR DATABASE:</h1> <p>" . $e->getMessage() . "</p> <p>Silakan cek pesan error bahasa Inggris di atas. Biasanya karena tabel 'riwayat_stok' belum dibuat, atau nama kolomnya ada yang salah di database.</p>");

            return false;
        }
    }
}
