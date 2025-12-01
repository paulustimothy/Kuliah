<?php

// Memuat file init.php yang berisi koneksi database dan fungsi helper
require_once __DIR__ . '/init.php';

$page_title = 'Masuk Aplikasi';

// Inisialisasi variabel untuk menyimpan data form (untuk menampilkan kembali jika error)
$form_data = ['email' => ''];

// Array untuk menyimpan pesan error
$errors = [];

// Cek apakah form dikirim dengan method POST (user klik tombol submit)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Ambil dan bersihkan data dari form
    // trim() = hapus spasi di awal dan akhir
    $form_data['email'] = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    // Validasi email: cek apakah format email valid
    if (!filter_var($form_data['email'], FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Masukkan email yang valid.';
    }

    // Validasi password: cek apakah password tidak kosong
    if ($password === '') {
        $errors[] = 'Password wajib diisi.';
    }

    // Jika tidak ada error, lanjutkan proses login
    if (!$errors) {
        try {
            // Query database untuk mencari user berdasarkan email
            // LIMIT 1 = hanya ambil 1 data (email harus unique)
            $stmt = $pdo->prepare('SELECT id, full_name, email, password, role FROM users WHERE email = ? LIMIT 1');
            $stmt->execute([$form_data['email']]); // Execute query dengan parameter email
            $user = $stmt->fetch(); // Ambil hasil query (1 baris data)

            // Cek apakah user ditemukan dan password cocok
            // password_verify() = membandingkan password plaintext dengan hash
            if ($user && password_verify($password, $user['password'])) {
                // Hapus password dari array user (jangan simpan password di session)
                unset($user['password']);
                // Simpan data user ke session (menandakan user sudah login)
                $_SESSION['user'] = $user;
                // Set pesan sukses
                set_flash('success', 'Selamat datang kembali, ' . $user['full_name'] . '!');
                // Redirect ke halaman dashboard
                header('Location: dashboard.php');
                exit;
            }

            // Jika email/password salah, tambahkan error
            $errors[] = 'Email atau password salah.';
        } catch (PDOException $e) {
            // Jika terjadi error database, tampilkan pesan error umum
            $errors[] = 'Terjadi gangguan sistem, coba lagi.';
        }
    }
}

// Memuat file header (berisi HTML head, navbar, dll)
include __DIR__ . '/partials/header.php';
?>

<!-- Form login -->
<section class="card">
    <h2>Masuk</h2>
    <!-- Tampilkan error jika ada -->
    <?php if ($errors): ?>
        <div class="flash error" style="margin-bottom:1rem;">
            <div><?= implode('<br>', array_map('clean', $errors)); ?></div>
            <!-- implode = gabungkan array menjadi string dengan separator <br> -->
            <!-- array_map('clean', $errors) = bersihkan setiap error dari XSS -->
        </div>
    <?php endif; ?>

    <!-- Form login dengan method POST -->
    <form method="post">
        <div>
            <label for="email">Email</label>
            <input type="email" id="email" name="email" value="<?= clean($form_data['email']); ?>" required>
        </div>
        <div>
            <label for="password">Password</label>
            <input type="password" id="password" name="password" required>
        </div>
        <button type="submit" class="btn btn-primary">Masuk</button>
        <p>Belum punya akun? <a href="register.php">Daftar di sini</a>.</p>
    </form>
</section>

<!-- Memuat file footer -->
<?php include __DIR__ . '/partials/footer.php'; ?>