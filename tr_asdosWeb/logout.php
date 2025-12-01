<?php
require_once __DIR__ . '/init.php';

session_destroy();
session_start();
set_flash('success', 'Berhasil logout.');
header('Location: index.php');
exit;
