# 📌 SIJM (Sistem Informasi Manajemen Bengkel Ikhsan Jaya Motor)

Aplikasi berbasis web menggunakan **PHP Native** untuk membantu pengelolaan data jasa/manajemen secara sederhana.

---

## 🚀 Fitur Utama

* Manajemen data (CRUD)
* Sistem autentikasi (login/logout)
* Dashboard sederhana
* Integrasi database MySQL (local)

---

## 🛠️ Teknologi yang Digunakan

* PHP Native
* MySQL
* HTML, CSS, JavaScript
* Bootstrap (optional)

---

## ⚙️ Cara Instalasi (Local)

### 1. Clone / Download Project

```bash
git clone https://github.com/dilanurlaila/SIJM.git
```

Atau download ZIP lalu extract ke folder:

```
htdocs (XAMPP) / www (Laragon)
```

---

### 2. Jalankan Web Server

Gunakan salah satu:

* XAMPP
* Laragon

Aktifkan:

* Apache
* MySQL

---

### 3. Setup Database

1. Buka **phpMyAdmin**

   ```
   http://localhost/phpmyadmin
   ```

2. Buat database baru, misalnya:

   ```
   sijm_db
   ```

3. Import file database:

   * Masuk ke database `db_ikhsanjaya`
   * Klik tab **Import**
   * Pilih file `.sql` yang ada di project

---

### 4. Konfigurasi Koneksi Database

Buka file konfigurasi (biasanya):

```
config/koneksi.php
```

Sesuaikan:

```php
$host = "localhost";
$user = "root";
$pass = "";
$db   = "sijm_db";
```

---

### 5. Jalankan Aplikasi

Buka browser:

```
http://localhost/nama-folder-project
```

---

## 🔑 Default Login (Jika Ada)

```
Username: Admin
Password: Admin123
```

---

## ⚠️ Catatan

* Database masih menggunakan **local environment**
* Pastikan Apache & MySQL sudah berjalan
* Jika error koneksi, cek kembali konfigurasi database

---

## 👨‍💻 Developer

* Nama: Dila Nurlaila 
* Project untuk pembelajaran / tugas / pengembangan

---

## 📄 Lisensi

Project ini digunakan untuk keperluan pembelajaran.
