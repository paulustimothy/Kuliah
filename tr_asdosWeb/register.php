<?php

require_once __DIR__ . '/init.php';

$page_title = 'Daftar Akun Baru';

// Inisialisasi data form (untuk menampilkan kembali jika error)
$form_data = ['full_name' => '', 'email' => ''];
$errors = []; // Array untuk menyimpan error

// Cek apakah form dikirim dengan method POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Ambil data dari form dan bersihkan
    $form_data['full_name'] = trim($_POST['full_name'] ?? '');
    $form_data['email'] = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? ''; // Password konfirmasi

    // Validasi: nama lengkap tidak boleh kosong
    if ($form_data['full_name'] === '') {
        $errors[] = 'Nama lengkap wajib diisi.';
    }

    // Validasi: email harus valid format
    if (!filter_var($form_data['email'], FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Email tidak valid.';
    }

    // Validasi: password minimal 6 karakter
    if (strlen($password) < 6) {
        $errors[] = 'Password minimal 6 karakter.';
    }

    // Validasi: password dan konfirmasi password harus sama
    if ($password !== $confirm) {
        $errors[] = 'Konfirmasi password tidak sama.';
    }

    // Jika tidak ada error, lanjutkan proses registrasi
    if (!$errors) {
        try {
            // Cek apakah email sudah terdaftar
            $stmt = $pdo->prepare('SELECT id FROM users WHERE email = ? LIMIT 1');
            $stmt->execute([$form_data['email']]);

            // Jika email sudah ada di database
            if ($stmt->fetch()) {
                $errors[] = 'Email sudah terdaftar, silakan login.';
            } else {
                // Hash password menggunakan password_hash (fungsi PHP untuk enkripsi password)
                // PASSWORD_DEFAULT = menggunakan algoritma terbaru dan teraman
                $hash = password_hash($password, PASSWORD_DEFAULT);

                // Insert data user baru ke database
                $insert = $pdo->prepare('INSERT INTO users (full_name, email, password, role) VALUES (?, ?, ?, ?)');
                // Execute dengan parameter: nama, email, password hash, role default 'user'
                $insert->execute([$form_data['full_name'], $form_data['email'], $hash, 'user']);

                // Set pesan sukses
                set_flash('success', 'Registrasi berhasil! Silakan login.');
                // Redirect ke halaman login
                header('Location: login.php');
                exit;
            }
        } catch (PDOException $e) {
            // Jika terjadi error database
            $errors[] = 'Terjadi kesalahan, coba lagi beberapa saat.';
        }
    }
}

include __DIR__ . '/partials/header.php';
?>

<section class="card">
    <h2>Daftar Akun Pemilik Hewan</h2>
    <!-- Tampilkan error jika ada -->
    <?php if ($errors): ?>
        <div class="flash error" style="margin-bottom:1rem;">
            <div><?= implode('<br>', array_map('clean', $errors)); ?></div>
        </div>
    <?php endif; ?>

    <!-- Form registrasi -->
    <form method="post" novalidate>
        <!-- novalidate = nonaktifkan validasi HTML5, kita pakai validasi PHP -->
        <div class="grid-2">
            <div>
                <label for="full_name">Nama lengkap</label>
                <input type="text" id="full_name" name="full_name" value="<?= clean($form_data['full_name']); ?>" required>
            </div>
            <div>
                <label for="email">Email</label>
                <input type="email" id="email" name="email" value="<?= clean($form_data['email']); ?>" required>
            </div>
        </div>
        <div class="grid-2">
            <div>
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required>
            </div>
            <div>
                <label for="confirm_password">Konfirmasi Password</label>
                <input type="password" id="confirm_password" name="confirm_password" required>
            </div>
        </div>
        <button type="submit" class="btn btn-primary">Buat Akun</button>
        <p>Sudah punya akun? <a href="login.php">Masuk di sini</a>.</p>
    </form>
</section>

<?php include __DIR__ . '/partials/footer.php'; ?>