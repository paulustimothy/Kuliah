<?php
$user = current_user();
$flash = get_flash();
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= clean($page_title ?? 'Pet Care'); ?></title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700&display=swap">
    <link rel="stylesheet" href="assets/css/styles.css">
</head>

<body>
    <header class="site-header">
        <div class="container header-inner">
            <a href="index.php" class="brand">PetSpace</a>
            <nav class="header-nav" id="mainNav">
                <a href="index.php">Beranda</a>
                <?php if ($user): ?>
                    <a href="dashboard.php">Dashboard</a>
                    <a href="logout.php" class="btn-link">Logout</a>
                <?php else: ?>
                    <a href="login.php">Login</a>
                    <a href="register.php" class="btn-link">Daftar</a>
                <?php endif; ?>
            </nav>
            <button class="nav-toggle" id="navToggle" aria-label="Buka menu">
                <span></span>
                <span></span>
                <span></span>
            </button>
        </div>
    </header>

    <?php if ($flash): ?>
        <div class="flash <?= clean($flash['type']); ?>">
            <div class="container"><?= clean($flash['message']); ?></div>
        </div>
    <?php endif; ?>

    <main class="container">