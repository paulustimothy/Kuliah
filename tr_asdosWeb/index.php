<?php
require_once __DIR__ . '/init.php';
$page_title = 'PetSpace - Penitipan Hewan';
include __DIR__ . '/partials/header.php';
?>

<section class="hero">
    <div class="hero-content">
        <p class="status-pill status-dititip">Penitipan Harian Aman</p>
        <h1>Titip hewan kesayanganmu tanpa khawatir.</h1>
        <p>Pilih layanan mandi, grooming, atau penitipan harian. Pantau progres perawatan mulai dari status dititip sampai selesai secara real-time.</p>
        <div class="hero-actions">
            <a href="<?= $user ? 'dashboard.php' : 'register.php'; ?>" class="btn btn-primary">
                <?= $user ? 'Lihat Dashboard' : 'Daftar Sekarang'; ?>
            </a>
            <a href="<?= $user ? 'dashboard.php' : 'login.php'; ?>" class="btn btn-outline">Masuk</a>
        </div>
    </div>
    <div class="card">
        <h2>Alur Layanan</h2>
        <ol>
            <li>Registrasi akun pemilik hewan.</li>
            <li>Tambah data hewan + pilih layanan harian.</li>
            <li>Petugas mengubah status: dititip → grooming → selesai.</li>
        </ol>
        <img src="https://images.unsplash.com/photo-1548199973-03cce0bbc87b?auto=format&fit=crop&w=800&q=80"
            alt="Hewan grooming" style="width:100%;border-radius:12px;margin-top:1rem;">
    </div>
</section>

<!-- Section Layanan -->
<section class="services-section">
    <div class="container">
        <div class="section-header">
            <h2>Layanan Kami</h2>
            <p>Berbagai layanan terbaik untuk hewan kesayangan Anda</p>
        </div>
        <div class="services-grid">
            <div class="service-card">
                <div class="service-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z" />
                    </svg>
                </div>
                <h3>🛁 Mandi</h3>
                <p>Layanan mandi lengkap dengan shampo khusus dan perawatan bulu hewan peliharaan Anda.</p>
            </div>
            <div class="service-card">
                <div class="service-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z" />
                    </svg>
                </div>
                <h3>✂️ Grooming</h3>
                <p>Grooming profesional termasuk potong kuku, bersihkan telinga, dan styling rambut.</p>
            </div>
            <div class="service-card">
                <div class="service-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z" />
                    </svg>
                </div>
                <h3>🏠 Penitipan</h3>
                <p>Penitipan harian dengan fasilitas nyaman dan pengawasan 24 jam untuk hewan kesayangan Anda.</p>
            </div>
        </div>
    </div>
</section>

<section class="card">
    <h2>Kenapa memilih PetSpace?</h2>
    <div class="grid-2">
        <div class="feature-item">
            <div class="feature-icon">📅</div>
            <h3>Layanan Harian</h3>
            <p>Mandi, grooming, atau penitipan harian untuk memastikan hewan nyaman.</p>
        </div>
        <div class="feature-item">
            <div class="feature-icon">📊</div>
            <h3>Monitoring Status</h3>
            <p>Status progres tercatat jelas: dititip, grooming, selesai.</p>
        </div>
        <div class="feature-item">
            <div class="feature-icon">👥</div>
            <h3>Admin & User Role</h3>
            <p>Admin memantau semua hewan, user memantau hewan miliknya.</p>
        </div>
        <div class="feature-item">
            <div class="feature-icon">🔒</div>
            <h3>Responsif & Aman</h3>
            <p>Antarmuka ramah mobile, password tersimpan menggunakan hash.</p>
        </div>
    </div>
</section>

<?php include __DIR__ . '/partials/footer.php'; ?>