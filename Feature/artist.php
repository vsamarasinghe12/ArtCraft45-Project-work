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
if (isset($_POST['toggle_follow'])) {
    require_login();
    if ($uid != $id) {
        $has = run($conn, "SELECT id FROM follows WHERE follower_id=? AND artist_id=?", "ii", [$uid, $id]);
        if (mysqli_num_rows($has) > 0) {
            run($conn, "DELETE FROM follows WHERE follower_id=? AND artist_id=?", "ii", [$uid, $id]);
        } else {
            run($conn, "INSERT INTO follows (follower_id, artist_id) VALUES (?,?)", "ii", [$uid, $id]);
        }
    }
    header("Location: artist.php?id=$id");
    exit;
    }

$followers = mysqli_fetch_assoc(run($conn, "SELECT COUNT(*) AS total FROM follows WHERE artist_id=?", "i", [$id]))['total'];
$following = mysqli_num_rows(run($conn, "SELECT id FROM follows WHERE follower_id=? AND artist_id=?", "ii", [$uid, $id])) > 0;
$artworks = run($conn, $art_select . " WHERE a.user_id=? AND a.status='approved' ORDER BY a.created_at DESC", "i", [$id]);

$page_title = $artist['full_name'];
include 'header.php';