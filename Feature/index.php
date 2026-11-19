<?php
require 'config.php';
$page_title = "Home";
include 'header.php';

$featured = run($conn, $art_select . " WHERE a.status='approved' AND a.is_featured=1 ORDER BY a.created_at DESC LIMIT 6");
$latest = run($conn, $art_select . " WHERE a.status='approved' ORDER BY a.created_at DESC LIMIT 6");

$events = run($conn, "SELECT * FROM events WHERE event_date >= CURDATE() ORDER BY event_date LIMIT 3");
?>