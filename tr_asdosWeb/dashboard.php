<?php

require_once __DIR__ . '/init.php';

// Memastikan user sudah login (jika belum, redirect ke login)
require_login();

// Ambil data user yang sedang login
$user = current_user();

// Cek apakah user adalah admin
$is_admin = is_admin();

// Set judul halaman sesuai role (admin atau user biasa)
$page_title = $is_admin ? 'Dashboard Admin' : 'Dashboard Pemilik';

$service_options = [
    'mandi' => 'Mandi',
    'grooming' => 'Grooming',
    'penitipan' => 'Penitipan',
];

$status_options = [
    'dititip' => 'Dititip',
    'grooming' => 'Grooming',
    'selesai' => 'Selesai',
];

// Inisialisasi array untuk menyimpan error validasi form
$form_errors = [];

// Inisialisasi array untuk menyimpan nilai form (untuk menampilkan kembali jika error)
$form_values = [
    'pet_name' => '',
    'pet_type' => '',
    'service_type' => 'penitipan',
    'start_date' => date('Y-m-d'),
    'notes' => '',
];

// Variabel untuk menyimpan data hewan yang sedang diedit (jika ada)
$editing_pet = null;

// Cek apakah user ingin edit hewan (ada parameter ?edit=id di URL)
if (isset($_GET['edit'])) {
    // Ambil ID hewan dari URL dan convert ke integer (untuk keamanan)
    $pet_id = (int) $_GET['edit'];

    // Query database untuk mengambil data hewan berdasarkan ID
    $stmt = $pdo->prepare('SELECT * FROM pets WHERE id = ? LIMIT 1');
    $stmt->execute([$pet_id]);
    $found = $stmt->fetch(); // Ambil 1 baris data

    // Cek apakah hewan ditemukan dan user berhak mengeditnya
    // Admin bisa edit semua, user biasa hanya bisa edit hewan miliknya sendiri
    if ($found && ($is_admin || $found['user_id'] === $user['id'])) {
        $editing_pet = $found; // Simpan data hewan yang akan diedit

        // Isi form dengan data hewan yang akan diedit
        $form_values = array_merge($form_values, [
            'pet_name' => $found['pet_name'],
            'pet_type' => $found['pet_type'],
            'service_type' => $found['service_type'],
            'start_date' => $found['start_date'],
            'notes' => $found['notes'],
        ]);
    } else {
        // Jika hewan tidak ditemukan atau user tidak berhak, tampilkan error
        set_flash('error', 'Data hewan tidak ditemukan atau tidak boleh diubah.');
        header('Location: dashboard.php');
        exit;
    }
}

// Cek apakah form dikirim dengan method POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Ambil aksi yang dilakukan (save_pet, delete_pet, update_status)
    $action = $_POST['action'] ?? '';

    // ========== AKSI: Simpan/Tambah Hewan ==========
    if ($action === 'save_pet') {
        // Ambil ID hewan (jika edit, ada ID. Jika tambah baru, ID = 0)
        $pet_id = (int) ($_POST['pet_id'] ?? 0);

        // Ambil dan bersihkan data dari form
        $form_values['pet_name'] = trim($_POST['pet_name'] ?? '');
        $form_values['pet_type'] = trim($_POST['pet_type'] ?? '');
        $form_values['service_type'] = $_POST['service_type'] ?? 'penitipan';
        $form_values['start_date'] = $_POST['start_date'] ?? date('Y-m-d');
        $form_values['notes'] = trim($_POST['notes'] ?? '');

        // Validasi: nama hewan wajib diisi
        if ($form_values['pet_name'] === '') {
            $form_errors[] = 'Nama hewan wajib diisi.';
        }

        // Validasi: jenis hewan wajib diisi
        if ($form_values['pet_type'] === '') {
            $form_errors[] = 'Jenis hewan wajib diisi.';
        }

        // Validasi: service_type harus valid (ada di array $service_options)
        if (!isset($service_options[$form_values['service_type']])) {
            $form_errors[] = 'Jenis layanan tidak valid.';
        }

        // Validasi: format tanggal harus YYYY-MM-DD
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $form_values['start_date'])) {
            $form_errors[] = 'Tanggal mulai tidak valid.';
        }

        // Proses upload foto (jika ada)
        $new_photo = null;
        // Cek apakah ada file yang diupload dan tidak ada error
        if (!$form_errors && isset($_FILES['photo']) && ($_FILES['photo']['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE) {
            try {
                // Panggil fungsi handle_upload untuk memproses file
                $new_photo = handle_upload($_FILES['photo']);
            } catch (RuntimeException $e) {
                // Jika upload gagal, tambahkan error
                $form_errors[] = $e->getMessage();
            }
        }

        // Jika tidak ada error, simpan ke database
        if (!$form_errors) {
            // Jika ada pet_id, berarti ini adalah edit (update)
            if ($pet_id) {
                // Cek apakah hewan ada di database dan user berhak mengeditnya
                $stmt = $pdo->prepare('SELECT * FROM pets WHERE id = ? LIMIT 1');
                $stmt->execute([$pet_id]);
                $pet = $stmt->fetch();

                // Validasi: hewan harus ada dan user berhak mengedit
                if (!$pet || (!$is_admin && $pet['user_id'] !== $user['id'])) {
                    set_flash('error', 'Tidak boleh mengubah data ini.');
                    header('Location: dashboard.php');
                    exit;
                }

                // Build query UPDATE (jika ada foto baru, tambahkan kolom photo)
                $update_sql = 'UPDATE pets SET pet_name=?, pet_type=?, service_type=?, start_date=?, notes=?' . ($new_photo ? ', photo=?' : '') . ' WHERE id=?';

                // Siapkan parameter untuk query
                $params = [
                    $form_values['pet_name'],
                    $form_values['pet_type'],
                    $form_values['service_type'],
                    $form_values['start_date'],
                    $form_values['notes'],
                ];

                // Jika ada foto baru, tambahkan ke parameter
                if ($new_photo) {
                    $params[] = $new_photo;
                }

                // Tambahkan pet_id di akhir (untuk WHERE clause)
                $params[] = $pet_id;

                // Execute query update
                $update = $pdo->prepare($update_sql);
                $update->execute($params);

                // Hapus foto lama jika ada foto baru (untuk menghemat storage)
                if ($new_photo && $pet['photo'] && file_exists(base_path($pet['photo']))) {
                    @unlink(base_path($pet['photo'])); // @ = suppress error jika file tidak ada
                }

                set_flash('success', 'Data hewan berhasil diperbarui.');
                header('Location: dashboard.php');
                exit;
            } else {
                // Jika tidak ada pet_id, berarti ini adalah tambah baru (INSERT)
                $insert = $pdo->prepare('INSERT INTO pets (user_id, pet_name, pet_type, service_type, status, start_date, notes, photo) VALUES (?, ?, ?, ?, ?, ?, ?, ?)');
                $insert->execute([
                    $user['id'],
                    $form_values['pet_name'],
                    $form_values['pet_type'],
                    $form_values['service_type'],
                    'dititip',
                    $form_values['start_date'],
                    $form_values['notes'],
                    $new_photo,
                ]);

                set_flash('success', 'Hewan berhasil ditambahkan.');
                header('Location: dashboard.php');
                exit;
            }
        }
    }

    // ========== AKSI: Hapus Hewan ==========
    if ($action === 'delete_pet') {
        // Ambil ID hewan yang akan dihapus
        $pet_id = (int) ($_POST['pet_id'] ?? 0);

        // Ambil data hewan dari database
        $stmt = $pdo->prepare('SELECT * FROM pets WHERE id = ? LIMIT 1');
        $stmt->execute([$pet_id]);
        $pet = $stmt->fetch();

        // Validasi: hewan harus ada dan user berhak menghapusnya
        if (!$pet || (!$is_admin && $pet['user_id'] !== $user['id'])) {
            set_flash('error', 'Data tidak ditemukan.');
            header('Location: dashboard.php');
            exit;
        }

        // Hapus data hewan dari database
        $delete = $pdo->prepare('DELETE FROM pets WHERE id = ?');
        $delete->execute([$pet_id]);

        // Hapus file foto dari server (jika ada)
        if ($pet['photo'] && file_exists(base_path($pet['photo']))) {
            @unlink(base_path($pet['photo']));
        }

        set_flash('success', 'Data hewan dihapus.');
        header('Location: dashboard.php');
        exit;
    }

    // ========== AKSI: Update Status (Hanya Admin) ==========
    if ($action === 'update_status') {
        // Cek apakah user adalah admin
        if (!$is_admin) {
            set_flash('error', 'Akses khusus admin.');
            header('Location: dashboard.php');
            exit;
        }

        // Ambil ID hewan dan status baru
        $pet_id = (int) ($_POST['pet_id'] ?? 0);
        $status = $_POST['status'] ?? 'dititip';

        // Validasi: status harus valid
        if (!isset($status_options[$status])) {
            set_flash('error', 'Status tidak dikenal.');
            header('Location: dashboard.php');
            exit;
        }

        // Update status hewan di database
        $stmt = $pdo->prepare('UPDATE pets SET status = ? WHERE id = ?');
        $stmt->execute([$status, $pet_id]);

        set_flash('success', 'Status layanan diperbarui.');
        header('Location: dashboard.php');
        exit;
    }
}

// ========== AMBIL DATA UNTUK DITAMPILKAN ==========

// Ambil parameter search dan filter dari URL (GET)
$search = trim($_GET['search'] ?? '');
$filter_status = $_GET['status'] ?? '';
$filter_service = $_GET['service'] ?? '';

// Query untuk mengambil data pets dengan filter
if ($is_admin) {
    // Admin melihat semua hewan dari semua user
    $query = 'SELECT pets.*, users.full_name FROM pets JOIN users ON users.id = pets.user_id WHERE 1=1';
    // WHERE 1=1 = trik untuk memudahkan penambahan kondisi dengan AND
    $params = [];

    // Jika ada search, tambahkan kondisi LIKE untuk mencari nama hewan
    if ($search) {
        $query .= ' AND pets.pet_name LIKE ?';
        $params[] = "%$search%"; // % = wildcard (cocok dengan apapun di depan/belakang)
    }

    // Jika ada filter status, tambahkan kondisi
    if ($filter_status && isset($status_options[$filter_status])) {
        $query .= ' AND pets.status = ?';
        $params[] = $filter_status;
    }

    // Jika ada filter service, tambahkan kondisi
    if ($filter_service && isset($service_options[$filter_service])) {
        $query .= ' AND pets.service_type = ?';
        $params[] = $filter_service;
    }

    // Urutkan berdasarkan tanggal dibuat (terbaru di atas)
    $query .= ' ORDER BY pets.created_at DESC';

    // Execute query
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $pets = $stmt->fetchAll();
} else {
    // User biasa hanya melihat hewan miliknya sendiri
    $query = 'SELECT * FROM pets WHERE user_id = ?';
    $params = [$user['id']]; // Filter berdasarkan ID user yang login

    // Tambahkan kondisi search jika ada
    if ($search) {
        $query .= ' AND pet_name LIKE ?';
        $params[] = "%$search%";
    }

    // Tambahkan filter status jika ada
    if ($filter_status && isset($status_options[$filter_status])) {
        $query .= ' AND status = ?';
        $params[] = $filter_status;
    }

    // Tambahkan filter service jika ada
    if ($filter_service && isset($service_options[$filter_service])) {
        $query .= ' AND service_type = ?';
        $params[] = $filter_service;
    }

    // Urutkan berdasarkan tanggal dibuat
    $query .= ' ORDER BY created_at DESC';

    // Execute query
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $pets = $stmt->fetchAll();
}

// Hitung jumlah hewan per status (untuk ditampilkan di ringkasan)
$status_counts = [
    'dititip' => 0,
    'grooming' => 0,
    'selesai' => 0,
];
// Loop melalui semua hewan dan hitung per status
foreach ($pets as $pet) {
    $status_counts[$pet['status']]++;
}

include __DIR__ . '/partials/header.php';
?>

<!-- ========== FORM TAMBAH/EDIT HEWAN (Hanya untuk User, bukan Admin) ========== -->
<?php if (!$is_admin): ?>
    <section class="card">
        <h2><?= $editing_pet ? 'Perbarui Hewan' : 'Registrasi Hewan Baru'; ?></h2>

        <?php if ($form_errors): ?>
            <div class="flash error" style="margin-bottom:1rem;">
                <div><?= implode('<br>', array_map('clean', $form_errors)); ?></div>
            </div>
        <?php endif; ?>

        <form method="post" enctype="multipart/form-data">

            <input type="hidden" name="action" value="save_pet">

            <?php if ($editing_pet): ?>
                <input type="hidden" name="pet_id" value="<?= (int) $editing_pet['id']; ?>">
            <?php endif; ?>

            <div class="grid-2">
                <div>
                    <label for="pet_name">Nama Hewan</label>
                    <input type="text" id="pet_name" name="pet_name" value="<?= clean($form_values['pet_name']); ?>" required>
                </div>
                <div>
                    <label for="pet_type">Jenis Hewan</label>
                    <input type="text" id="pet_type" name="pet_type" value="<?= clean($form_values['pet_type']); ?>" required>
                </div>
            </div>

            <div class="grid-2">
                <div>
                    <label for="service_type">Layanan Harian</label>
                    <select id="service_type" name="service_type">
                        <?php foreach ($service_options as $value => $label): ?>
                            <option value="<?= $value; ?>" <?= $form_values['service_type'] === $value ? 'selected' : ''; ?>>
                                <?= $label; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label for="start_date">Tanggal Mulai</label>
                    <input type="date" id="start_date" name="start_date" value="<?= clean($form_values['start_date']); ?>">
                </div>
            </div>

            <div>
                <label for="notes">Catatan</label>
                <textarea id="notes" name="notes"><?= clean($form_values['notes']); ?></textarea>
            </div>

            <div>
                <label for="photo">Foto Hewan (opsional)</label>
                <input type="file" id="photo" name="photo" accept="image/png, image/jpeg">

                <?php if ($editing_pet && $editing_pet['photo']): ?>
                    <p>Foto saat ini:</p>
                    <img src="<?= clean($editing_pet['photo']); ?>" alt="Foto hewan" style="max-width:120px;border-radius:8px;">
                <?php endif; ?>
            </div>

            <button type="submit" class="btn btn-primary">
                <?= $editing_pet ? 'Simpan Perubahan' : 'Tambah Hewan'; ?>
            </button>

            <?php if ($editing_pet): ?>
                <a class="btn btn-ghost btn-small" href="dashboard.php">Batalkan</a>
            <?php endif; ?>
        </form>
    </section>
<?php endif; ?>

<!-- ========== RINGKASAN STATUS (Card Statistik) ========== -->
<section class="card">
    <h2><?= $is_admin ? 'Ringkasan Layanan' : 'Status Hewan Kamu'; ?></h2>
    <div class="grid-2">
        <?php foreach ($status_options as $key => $label): ?>
            <div class="pet-card">
                <img src="https://images.unsplash.com/photo-1517849845537-4d257902454a?auto=format&fit=crop&w=200&q=60" alt="Hewan">
                <div>
                    <h3><?= $label; ?></h3>
                    <p><?= $status_counts[$key]; ?> hewan dalam status ini.</p>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</section>

<!-- ========== TABEL DAFTAR HEWAN ========== -->
<section class="card">
    <h2><?= $is_admin ? 'Daftar Semua Hewan' : 'Hewan Saya'; ?></h2>

    <div class="search-filter-bar">
        <form method="get" class="search-filter-form">
            <div class="search-input-wrapper">
                <input type="text" name="search" placeholder="Cari nama hewan..." value="<?= clean($search); ?>" class="search-input">
            </div>

            <div class="filter-group">
                <select name="status" class="filter-select">
                    <option value="">Semua Status</option>
                    <?php foreach ($status_options as $value => $label): ?>
                        <option value="<?= $value; ?>" <?= $filter_status === $value ? 'selected' : ''; ?>>
                            <?= $label; ?>
                        </option>
                    <?php endforeach; ?>
                </select>

                <select name="service" class="filter-select">
                    <option value="">Semua Layanan</option>
                    <?php foreach ($service_options as $value => $label): ?>
                        <option value="<?= $value; ?>" <?= $filter_service === $value ? 'selected' : ''; ?>>
                            <?= $label; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="filter-actions">
                <button type="submit" class="btn btn-primary">Cari</button>
                <?php if ($search || $filter_status || $filter_service): ?>
                    <a href="dashboard.php" class="btn btn-ghost">Reset</a>
                <?php endif; ?>
            </div>
        </form>
    </div>

    <!-- Tampilkan pesan jika tidak ada data -->
    <?php if (!$pets): ?>
        <p>Belum ada data. <?= $is_admin ? 'Pengguna belum mendaftarkan hewan.' : 'Mulai tambah hewan di formulir atas.'; ?></p>
    <?php else: ?>
        <!-- Tabel data hewan -->
        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>Nama</th>
                        <th>Jenis</th>
                        <?php if ($is_admin): ?><th>Pemilik</th><?php endif; ?>
                        <th>Layanan</th>
                        <th>Status</th>
                        <th>Tanggal</th>
                        <th>Catatan</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($pets as $pet): ?>
                        <tr>
                            <td><?= clean($pet['pet_name']); ?></td>
                            <td><?= clean($pet['pet_type']); ?></td>

                            <?php if ($is_admin): ?>
                                <td><?= clean($pet['full_name']); ?></td>
                            <?php endif; ?>

                            <td><?= clean($service_options[$pet['service_type']]); ?></td>

                            <td>
                                <span class="status-pill status-<?= clean($pet['status']); ?>">
                                    <?= clean($status_options[$pet['status']]); ?>
                                </span>
                            </td>

                            <td><?= clean($pet['start_date']); ?></td>
                            <td><?= clean($pet['notes']); ?></td>

                            <td>
                                <div class="table-actions">
                                    <?php if ($is_admin): ?>
                                        <form method="post">
                                            <input type="hidden" name="action" value="update_status">
                                            <input type="hidden" name="pet_id" value="<?= (int) $pet['id']; ?>">
                                            <select name="status" onchange="this.form.submit()">
                                                <?php foreach ($status_options as $value => $label): ?>
                                                    <option value="<?= $value; ?>" <?= $pet['status'] === $value ? 'selected' : ''; ?>>
                                                        <?= $label; ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </form>
                                        <form method="post" onsubmit="return confirm('Yakin menghapus data ini?');">
                                            <input type="hidden" name="action" value="delete_pet">
                                            <input type="hidden" name="pet_id" value="<?= (int) $pet['id']; ?>">
                                            <button type="submit" class="btn btn-danger btn-small">Hapus</button>
                                        </form>
                                    <?php else: ?>
                                        <a class="btn btn-warning btn-small" href="dashboard.php?edit=<?= (int) $pet['id']; ?>">
                                            Edit
                                        </a>

                                        <form method="post" onsubmit="return confirm('Yakin menghapus data ini?');">
                                            <input type="hidden" name="action" value="delete_pet">
                                            <input type="hidden" name="pet_id" value="<?= (int) $pet['id']; ?>">
                                            <button type="submit" class="btn btn-danger btn-small">Hapus</button>
                                        </form>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</section>

<?php include __DIR__ . '/partials/footer.php'; ?>