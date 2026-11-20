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
<div class="container">

  <h3 class="mb-3">Featured Artworks</h3>
  <div class="row">
    <?php if (mysqli_num_rows($featured) == 0) { echo '<p class="text-muted">No featured artworks yet.</p>'; } ?>
    <?php while ($a = mysqli_fetch_assoc($featured)) { art_card($a); } ?>
  </div>

  <h3 class="mb-3 mt-4">Latest Artworks</h3>
  <div class="row">
    <?php if (mysqli_num_rows($latest) == 0) { echo '<p class="text-muted">No artworks yet.</p>'; } ?>
    <?php while ($a = mysqli_fetch_assoc($latest)) { art_card($a); } ?>
  </div>
 <h3 class="mb-3 mt-4">Upcoming Events</h3>
  <div class="row">
    <?php if (mysqli_num_rows($events) == 0) { echo '<p class="text-muted">No upcoming events.</p>'; } ?>
    <?php while ($ev = mysqli_fetch_assoc($events)) { ?>
      <div class="col-md-4 mb-3">
        <div class="card h-100"><div class="card-body">
          <h5><?php echo e($ev['title']); ?></h5>
          <p class="mb-1"><?php echo e($ev['event_date']); ?> | <?php echo e($ev['location']); ?></p>
          <a href="events.php" class="btn btn-sm btn-outline-success">Details</a>
        </div></div>
      </div>
    <?php } ?>
  </div>

<?php include 'footer.php'; ?>