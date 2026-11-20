<?php
require 'config.php';
$page_title = "Artists";
$q = trim($_GET['q'] ?? '');

$artists = run($conn, "SELECT u.id, u.full_name, u.bio, COUNT(a.id) AS total_art
                       FROM users u JOIN artworks a ON a.user_id=u.id
                       WHERE a.status='approved' AND u.full_name LIKE ?
                       GROUP BY u.id ORDER BY u.full_name", "s", ["%$q%"]);
include 'header.php';
?>
<h2 class="mb-3">Artists</h2>
<form method="get" class="row g-2 mb-4">
  <div class="col-md-6"><input type="text" name="q" class="form-control" placeholder="Search artist name" value="<?php echo e($q); ?>"></div>
  <div class="col-md-2"><button class="btn btn-success w-100">Search</button></div>
</form>
<div class="row">
  <?php if (mysqli_num_rows($artists) == 0) { echo '<p class="text-muted">No artists found.</p>'; } ?>
  <?php while ($u = mysqli_fetch_assoc($artists)) { ?>
    <div class="col-md-4 mb-3">
      <div class="card h-100"><div class="card-body">
        <h5><?php echo e($u['full_name']); ?></h5>
        <p class="text-muted small"><?php echo e(substr($u['bio'] ?? '', 0, 80)); ?></p>
        <p class="mb-2"><?php echo $u['total_art']; ?> artwork(s)</p>
        <a href="artist.php?id=<?php echo $u['id']; ?>" class="btn btn-sm btn-primary">View Profile</a>
      </div></div>
    </div>
  <?php } ?>
</div>
<?php include 'footer.php'; ?>