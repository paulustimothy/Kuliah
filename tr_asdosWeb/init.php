<?php

// Cek apakah session sudah dimulai, jika belum maka mulai session
// Session digunakan untuk menyimpan data user yang sedang login
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Memuat file konfigurasi database (config.php)
$config = require __DIR__ . '/config.php';

// Mencoba membuat koneksi ke database MySQL
try {
    $pdo = new PDO(
        "mysql:host={$config['host']};dbname={$config['name']};charset={$config['charset']}",
        $config['user'], 
        $config['pass'],
        [
            // Set mode error: jika ada error, langsung throw exception (tampilkan error)
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            // Set mode fetch default: hasil query berupa array associative (nama kolom sebagai key)
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]
    );
} catch (PDOException $e) {
    // Jika koneksi gagal, tampilkan pesan error
    http_response_code(500);
    echo '<!DOCTYPE html><html lang="id"><head><meta charset="UTF-8"><title>Gagal koneksi</title>';
    echo '<style>body{font-family:Arial;padding:2rem;background:#fdf2f2;color:#b91c1c;}</style></head><body>';
    echo '<h1>Koneksi Database Gagal</h1>';
    echo '<p>Pastikan MySQL berjalan dan pengaturan pada <code>config.php</code> sudah benar.</p>';
    echo '<small>Detail teknis: ' . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8') . '</small>';
    echo '</body></html>';
    exit; // Hentikan eksekusi script
}

// Fungsi untuk mendapatkan path absolut dari file/folder
// Contoh: base_path('uploads/photo.jpg') akan return '/path/to/project/uploads/photo.jpg'
function base_path(string $path = ''): string
{
    // __DIR__ = direktori tempat file ini berada
    // ltrim($path, '/') = hapus slash di depan path jika ada
    return __DIR__ . ($path ? '/' . ltrim($path, '/') : '');
}

// Fungsi untuk membersihkan string dari karakter berbahaya (XSS prevention)
// htmlspecialchars mengubah karakter seperti <, >, & menjadi entity HTML yang aman
function clean(?string $value): string
{
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

// Fungsi untuk menyimpan pesan flash (pesan sementara yang muncul sekali)
function set_flash(string $type, string $message): void
{
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

// Fungsi untuk mengambil dan menghapus flash message
// Setelah diambil, flash message dihapus agar tidak muncul lagi
function get_flash(): ?array
{
    if (!isset($_SESSION['flash'])) {
        return null;
    }

    $flash = $_SESSION['flash'];

    unset($_SESSION['flash']);
    return $flash;
}

// Fungsi untuk mendapatkan data user yang sedang login
function current_user(): ?array
{
    // ?? null = jika $_SESSION['user'] tidak ada, return null
    return $_SESSION['user'] ?? null;
}

// Fungsi untuk mengecek apakah user yang login adalah admin
function is_admin(): bool
{
    // Ambil role dari current_user, jika tidak ada return null
    // (bool) = convert ke boolean (true/false)
    return (bool) (current_user()['role'] ?? null) && current_user()['role'] === 'admin';
}

// Fungsi untuk memastikan user sudah login
function require_login(): void
{
    if (!current_user()) {
        set_flash('error', 'Silakan login terlebih dahulu.');
        header('Location: login.php');
    }
}

// Fungsi untuk memastikan user adalah admin
function require_admin(): void
{
    if (!is_admin()) {
        set_flash('error', 'Akses khusus admin.');
        header('Location: dashboard.php');
        exit;
    }
}

// Fungsi untuk menangani upload file foto
// Menerima array $_FILES dan mengembalikan path file yang diupload
function handle_upload(array $file): ?string
{
    global $config; // Akses variabel $config dari luar fungsi

    // Cek apakah ada file yang diupload
    if (($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
        return null; 
    }

    // Cek apakah upload berhasil (error code 0 = sukses)
    if ($file['error'] !== UPLOAD_ERR_OK) {
        // Throw exception jika upload gagal
        throw new RuntimeException('Gagal mengunggah gambar, coba lagi.');
    }

    // Cek ukuran file (max 2MB)
    if ($file['size'] > $config['max_upload_size']) {
        throw new RuntimeException('Ukuran gambar maksimal 2 MB.');
    }

    // Cek tipe file menggunakan mime_content_type
    $mime = mime_content_type($file['tmp_name']);
    // Cek apakah tipe file diizinkan (hanya JPG dan PNG)
    if (!in_array($mime, $config['allowed_mimes'], true)) {
        throw new RuntimeException('Format gambar harus JPG atau PNG.');
    }

    // Tentukan ekstensi file berdasarkan mime type
    $extension = $mime === 'image/png' ? 'png' : 'jpg';
    // Generate nama file unik menggunakan uniqid (menghasilkan ID unik)
    $filename = uniqid('pet_', true) . '.' . $extension;
    // Path lengkap tempat file akan disimpan
    $destination = base_path('uploads/' . $filename);

    // Pindahkan file dari temporary location ke folder uploads
    if (!move_uploaded_file($file['tmp_name'], $destination)) {
        throw new RuntimeException('Tidak dapat menyimpan gambar.');
    }

    // Return path relatif file (untuk disimpan di database)
    return 'uploads/' . $filename;
}
