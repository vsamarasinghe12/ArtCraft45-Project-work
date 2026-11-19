<?php
require 'config.php';
$page_title = "Home";
include 'header.php';

$featured = run($conn, $art_select . " WHERE a.status='approved' AND a.is_featured=1 ORDER BY a.created_at DESC LIMIT 6");
$latest = run($conn, $art_select . " WHERE a.status='approved' ORDER BY a.created_at DESC LIMIT 6");

$events = run($conn, "SELECT * FROM events WHERE event_date >= CURDATE() ORDER BY event_date LIMIT 3");
?>
</div>
<div class="hero text-center">
  <h1>NSBM ArtCraft</h1>
  <p class="lead">Showcase your artwork and handcrafts to the whole NSBM community.</p>
  <a href="gallery.php" class="btn btn-light">Browse Gallery</a>
  <?php if (!isset($_SESSION['user_id'])) { ?>
    <a href="register.php" class="btn btn-outline-light">Join Now</a>
  <?php } ?>
</div>