<?php
require 'config.php';
$page_title = "Artists";
$q = trim($_GET['q'] ?? '');

$artists = run($conn, "SELECT u.id, u.full_name, u.bio, COUNT(a.id) AS total_art
                       FROM users u JOIN artworks a ON a.user_id=u.id
                       WHERE a.status='approved' AND u.full_name LIKE ?
                       GROUP BY u.id ORDER BY u.full_name", "s", ["%$q%"]);
include 'header.php';