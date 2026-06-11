<?php
require_once 'config/Database.php';
require_once 'models/BarangModel.php';

class BarangController
{
    private $model;

    public function __construct()
    {
        $db = (new Database())->getConnection();
        $this->model = new BarangModel($db);
    }

    public function getDaftarBarang($keyword = '')
    {
        return $this->model->getAllBarang($keyword);
    }

    // --- FUNGSI BARU UNTUK KOMPRESI GAMBAR ---
    // Fungsi ini dibuat private karena hanya digunakan di dalam controller ini saja
    private function compressAndResizeImage($sourcePath, $destinationPath, $maxWidth = 800, $quality = 75)
    {
        $imgInfo = getimagesize($sourcePath);
        if (!$imgInfo) return false; // File bukan gambar yang valid

        $mime = $imgInfo['mime'];
        $width = $imgInfo[0];
        $height = $imgInfo[1];

        // Buat resource gambar berdasarkan tipe mime
        switch ($mime) {
            case 'image/jpeg':
            case 'image/jpg':
                $image = imagecreatefromjpeg($sourcePath);
                break;
            case 'image/png':
                $image = imagecreatefrompng($sourcePath);
                break;
            default:
                return false; // Format tidak didukung untuk dikompres (misal: GIF/WebP)
        }

        // Kalkulasi resolusi baru agar tidak melebihi maxWidth (contoh: 800px)
        if ($width > $maxWidth) {
            $ratio = $maxWidth / $width;
            $newWidth = $maxWidth;
            $newHeight = $height * $ratio;
        } else {
            // Jika gambar sudah lebih kecil dari 800px, biarkan ukurannya
            $newWidth = $width;
            $newHeight = $height;
        }

        // Buat kanvas kosong untuk gambar baru
        $imageResized = imagecreatetruecolor($newWidth, $newHeight);

        // Khusus PNG: Pertahankan transparansi (Background bolong)
        if ($mime == 'image/png') {
            imagealphablending($imageResized, false);
            imagesavealpha($imageResized, true);
            $transparent = imagecolorallocatealpha($imageResized, 255, 255, 255, 127);
            imagefilledrectangle($imageResized, 0, 0, $newWidth, $newHeight, $transparent);
        }

        // Proses memindahkan dan mengubah ukuran gambar
        imagecopyresampled($imageResized, $image, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);

        // Simpan gambar yang sudah dikompres ke folder tujuan
        $success = false;
        switch ($mime) {
            case 'image/jpeg':
            case 'image/jpg':
                $success = imagejpeg($imageResized, $destinationPath, $quality); // Quality: 0 - 100
                break;
            case 'image/png':
                // Quality PNG skalanya 0 - 9 (terbalik dari JPEG)
                $pngQuality = round((100 - $quality) / 100 * 9);
                $success = imagepng($imageResized, $destinationPath, $pngQuality);
                break;
        }

        // Bersihkan memori server
        imagedestroy($image);
        imagedestroy($imageResized);

        return $success;
    }
    // --- AKHIR FUNGSI KOMPRESI ---

    public function prosesTambahBarang($post, $files)
    {
        $nama_file = null;

        // Proses Upload dan Kompresi Gambar
        if (isset($files['gambar']) && $files['gambar']['error'] == 0) {
            $ext = strtolower(pathinfo($files['gambar']['name'], PATHINFO_EXTENSION));
            $nama_file = time() . '_' . uniqid() . '.' . $ext;
            $tujuan = 'uploads/' . $nama_file;
            $sumber = $files['gambar']['tmp_name'];

            // PANGGIL FUNGSI KOMPRESI DI SINI (Lebar max: 800px, Kualitas: 75%)
            $kompresi_berhasil = $this->compressAndResizeImage($sumber, $tujuan, 800, 75);

            // Fallback: Jika gambar formatnya bukan JPG/PNG (misal gagal dikompres)
            // Lakukan upload normal (move_uploaded_file)
            if (!$kompresi_berhasil) {
                move_uploaded_file($sumber, $tujuan);
            }
        }

        $data = [
            'kode_barang' => trim($post['kode_barang']),
            'nama_barang' => trim($post['nama_barang']),
            'kategori'    => trim($post['kategori']),
            'gambar'      => $nama_file,
            'deskripsi'   => trim($post['deskripsi']),
            'stok'        => (int)$post['stok'],
            'harga_modal' => (float)$post['harga_modal'],
            'harga_jual'  => (float)$post['harga_jual']
        ];
        return $this->model->tambahBarang($data);
    }

    public function hapusBarang($id)
    {
        return $this->model->hapusBarang($id);
    }

    public function prosesTambahStok($post)
    {
        $id_barang = (int)$post['id_barang'];
        $jumlah_tambah = (int)$post['jumlah_tambah'];
        $keterangan = trim($post['keterangan']); // Misal: "Kulakan dari agen ABC"

        return $this->model->tambahStok($id_barang, $jumlah_tambah, $keterangan);
    }

    public function getBarangById($id)
    {
        return $this->model->getBarangById($id);
    }

    public function prosesEditBarang($post, $files)
    {
        $id = $post['id'];

        $data = [
            'kode_barang' => trim($post['kode_barang']),
            'nama_barang' => trim($post['nama_barang']),
            'kategori'    => trim($post['kategori']),
            'deskripsi'   => trim($post['deskripsi']),
            'stok'        => (int)$post['stok'],
            'harga_modal' => (float)$post['harga_modal'],
            'harga_jual'  => (float)$post['harga_jual']
        ];

        // Cek apakah user mengupload gambar baru
        if (isset($files['gambar']) && $files['gambar']['error'] == 0) {
            $ext = pathinfo($files['gambar']['name'], PATHINFO_EXTENSION);
            $nama_file = time() . '_' . uniqid() . '.' . $ext;
            $tujuan = 'uploads/' . $nama_file;
            $sumber = $files['gambar']['tmp_name'];

            // Kompres gambar
            $kompresi_berhasil = $this->compressAndResizeImage($sumber, $tujuan, 800, 75);
            if (!$kompresi_berhasil) {
                move_uploaded_file($sumber, $tujuan);
            }

            $data['gambar'] = $nama_file;

            // Hapus gambar lama agar folder uploads tidak penuh
            $barangLama = $this->getBarangById($id);
            if (!empty($barangLama['gambar']) && file_exists('uploads/' . $barangLama['gambar'])) {
                unlink('uploads/' . $barangLama['gambar']);
            }
        }

        return $this->model->updateBarang($id, $data);
    }
}
