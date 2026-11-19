<?php
require 'config.php';
require_login();
$page_title = "My Submissions";
$uid = $_SESSION['user_id'];


if (isset($_POST['delete'])) {
    $id = (int)$_POST['id'];
    $row = mysqli_fetch_assoc(run($conn, "SELECT image FROM artworks WHERE id=? AND user_id=?", "ii", [$id, $uid]));
    if ($row) {
        if (file_exists("uploads/" . $row['image'])) { unlink("uploads/" . $row['image']); }
        run($conn, "DELETE FROM artworks WHERE id=? AND user_id=?", "ii", [$id, $uid]);
        set_flash('success', 'Artwork deleted.');
    }
    header("Location: my_submissions.php");
    exit;
}

$mine = run($conn, $art_select . " WHERE a.user_id=? ORDER BY a.created_at DESC", "i", [$uid]);
include 'header.php';
?>
<h2 class="mb-3">My Submission History</h2>
<a href="upload.php" class="btn btn-success mb-3">+ Upload New</a>
<div class="table-responsive">
<table class="table table-bordered bg-white align-middle">
  <thead class="table-light"><tr><th>Image</th><th>Title</th><th>Category</th><th>Status</th><th>Date</th><th>Actions</th></tr></thead>
  <tbody>
  <?php if (mysqli_num_rows($mine) == 0) { echo '<tr><td colspan="6" class="text-center text-muted">You have not submitted anything yet.</td></tr>'; } ?>
  <?php while ($a = mysqli_fetch_assoc($mine)) {
      $badge = ['pending' => 'warning text-dark', 'approved' => 'success', 'rejected' => 'danger'][$a['status']];
  ?>
    <tr>
      <td><img src="uploads/<?php echo e($a['image']); ?>" width="70" height="55" style="object-fit:cover;"></td>
      <td><a href="artwork.php?id=<?php echo $a['id']; ?>"><?php echo e($a['title']); ?></a></td>
      <td><?php echo e($a['category_name']); ?></td>
      <td>
        <span class="badge bg-<?php echo $badge; ?>"><?php echo e($a['status']); ?></span>
        <?php if ($a['status'] == 'rejected' && $a['reject_reason']) { ?>
          <br><small class="text-danger">Reason: <?php echo e($a['reject_reason']); ?></small>
        <?php } ?>
      </td>
      <td><?php echo e(substr($a['created_at'], 0, 10)); ?></td>
      <td>
        <a href="edit_artwork.php?id=<?php echo $a['id']; ?>" class="btn btn-sm btn-primary">Edit</a>
        <form method="post" class="d-inline" onsubmit="return confirm('Delete this artwork?');">
  <?php echo csrf_field(); ?>
          <input type="hidden" name="id" value="<?php echo $a['id']; ?>">
          <button type="submit" name="delete" class="btn btn-sm btn-danger">Delete</button>
        </form>
      </td>
    </tr>
  <?php } ?>
  </tbody>
</table>
</div>
<?php include 'footer.php'; ?>
