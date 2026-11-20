<?php
require 'config.php';
$id = (int)($_GET['id'] ?? 0);
$uid = $_SESSION['user_id'] ?? 0;

$artist = mysqli_fetch_assoc(run($conn, "SELECT id, full_name, bio FROM users WHERE id=? AND role='student'", "i", [$id]));
if (!$artist) {
    set_flash('warning', 'Artist not found.');
    header("Location: artists.php");
    exit;
}