<?php
require '../config.php';
require_admin();
$page_title = "Manage Artworks";

if (isset($_POST['action'])) {
    $id = (int)$_POST['id'];
    $action = $_POST['action'];

    if ($action == 'approve') {
        run($conn, "UPDATE artworks SET status='approved', reject_reason=NULL WHERE id=?", "i", [$id]);
        set_flash('success', 'Artwork approved.');
    } elseif ($action == 'reject') {
        $reason = trim($_POST['reason'] ?? '');
        if ($reason == "") { $reason = null; }
        run($conn, "UPDATE artworks SET status='rejected', is_featured=0, reject_reason=? WHERE id=?", "si", [$reason, $id]);
        set_flash('success', 'Artwork rejected.');
    } elseif ($action == 'feature') {
        run($conn, "UPDATE artworks SET is_featured=1 WHERE id=? AND status='approved'", "i", [$id]);
        set_flash('success', 'Artwork will be shown on the homepage.');
    } elseif ($action == 'unfeature') {
        run($conn, "UPDATE artworks SET is_featured=0 WHERE id=?", "i", [$id]);
        set_flash('success', 'Artwork removed from featured.');
    } elseif ($action == 'delete') {
        $row = mysqli_fetch_assoc(run($conn, "SELECT image FROM artworks WHERE id=?", "i", [$id]));
        if ($row && file_exists("../uploads/" . $row['image'])) { unlink("../uploads/" . $row['image']); }
        run($conn, "DELETE FROM artworks WHERE id=?", "i", [$id]);
        set_flash('success', 'Artwork deleted.');
    }
    header("Location: artworks.php?status=" . urlencode($_POST['back'] ?? ''));
    exit;
}

$status = $_GET['status'] ?? '';
$sql = "SELECT a.*, u.full_name, c.name AS category_name FROM artworks a
        JOIN users u ON a.user_id=u.id JOIN categories c ON a.category_id=c.id";
if (in_array($status, ['pending', 'approved', 'rejected'])) {
    $list = run($conn, $sql . " WHERE a.status=? ORDER BY a.created_at DESC", "s", [$status]);
} else {
    $list = run($conn, $sql . " ORDER BY a.created_at DESC");
}
include 'header.php';
?>
<h2 class="mb-3">Manage Artworks</h2>
<div class="mb-3">
  <a href="artworks.php" class="btn btn-outline-dark btn-sm">All</a>
  <a href="artworks.php?status=pending" class="btn btn-outline-warning btn-sm">Pending</a>
  <a href="artworks.php?status=approved" class="btn btn-outline-success btn-sm">Approved</a>
  <a href="artworks.php?status=rejected" class="btn btn-outline-danger btn-sm">Rejected</a>
</div>
<div class="table-responsive">
<table class="table table-bordered bg-white align-middle">
  <thead class="table-light"><tr><th>Image</th><th>Title</th><th>Student</th><th>Category</th><th>Status</th><th>Featured</th><th>Actions</th></tr></thead>
  <tbody>
  <?php if (mysqli_num_rows($list) == 0) { echo '<tr><td colspan="7" class="text-center text-muted">No artworks found.</td></tr>'; } ?>
  <?php while ($a = mysqli_fetch_assoc($list)) { ?>
    <tr>
      <td><img src="../uploads/<?php echo e($a['image']); ?>" width="70" height="55" style="object-fit:cover;"></td>
      <td><a href="../artwork.php?id=<?php echo $a['id']; ?>" target="_blank"><?php echo e($a['title']); ?></a></td>
      <td><?php echo e($a['full_name']); ?></td>
      <td><?php echo e($a['category_name']); ?></td>
      <td>
        <?php echo e($a['status']); ?>
        <?php if ($a['status'] == 'rejected' && $a['reject_reason']) { ?><br><small class="text-muted"><?php echo e($a['reject_reason']); ?></small><?php } ?>
      </td>
      <td><?php echo $a['is_featured'] ? 'Yes' : 'No'; ?></td>
      <td>
        <form method="post" class="d-flex flex-wrap gap-1">
  <?php echo csrf_field(); ?>
          <input type="hidden" name="id" value="<?php echo $a['id']; ?>">
          <input type="hidden" name="back" value="<?php echo e($status); ?>">
          <?php if ($a['status'] != 'approved') { ?>
            <button name="action" value="approve" class="btn btn-sm btn-success">Approve</button>
          <?php } ?>
          <?php if ($a['status'] != 'rejected') { ?>
            <input type="text" name="reason" class="form-control form-control-sm" style="width:140px;" placeholder="Reject reason">
            <button name="action" value="reject" class="btn btn-sm btn-warning">Reject</button>
          <?php } ?>
          <?php if ($a['status'] == 'approved') { ?>
            <?php if ($a['is_featured']) { ?>
              <button name="action" value="unfeature" class="btn btn-sm btn-secondary">Unfeature</button>
            <?php } else { ?>
              <button name="action" value="feature" class="btn btn-sm btn-info">Feature</button>
            <?php } ?>
          <?php } ?>
          <button name="action" value="delete" class="btn btn-sm btn-danger" onclick="return confirm('Delete this artwork permanently?');">Delete</button>
        </form>
      </td>
    </tr>
  <?php } ?>
  </tbody>
</table>
</div>
<?php include 'footer.php'; ?>
