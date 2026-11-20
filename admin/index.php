<?php
require '../config.php';
require_admin();
$page_title = "Dashboard";


if (isset($_POST['action'])) {
    $id = (int)$_POST['id'];
    if ($_POST['action'] == 'approve') {
        run($conn, "UPDATE artworks SET status='approved', reject_reason=NULL WHERE id=?", "i", [$id]);
        set_flash('success', 'Artwork approved.');
    } elseif ($_POST['action'] == 'reject') {
        $reason = trim($_POST['reason'] ?? '');
        if ($reason == "") { $reason = null; }
        run($conn, "UPDATE artworks SET status='rejected', is_featured=0, reject_reason=? WHERE id=?", "si", [$reason, $id]);
        set_flash('success', 'Artwork rejected.');
    }
    header("Location: index.php");
    exit;
}

function count_rows($conn, $sql) {
    return mysqli_fetch_assoc(run($conn, $sql))['total'];
}
$students = count_rows($conn, "SELECT COUNT(*) AS total FROM users WHERE role='student'");
$total_art = count_rows($conn, "SELECT COUNT(*) AS total FROM artworks");
$pending = count_rows($conn, "SELECT COUNT(*) AS total FROM artworks WHERE status='pending'");
$events = count_rows($conn, "SELECT COUNT(*) AS total FROM events");

$latest_pending = run($conn, "SELECT a.id, a.title, a.image, a.created_at, u.full_name FROM artworks a JOIN users u ON a.user_id=u.id WHERE a.status='pending' ORDER BY a.created_at DESC LIMIT 5");
include 'header.php';
?>
<h2 class="mb-4">Admin Dashboard</h2>
<div class="row text-center mb-4">
  <div class="col-md-3 mb-2"><div class="card bg-primary text-white"><div class="card-body"><h2><?php echo $students; ?></h2>Students</div></div></div>
  <div class="col-md-3 mb-2"><div class="card bg-success text-white"><div class="card-body"><h2><?php echo $total_art; ?></h2>Artworks</div></div></div>
  <div class="col-md-3 mb-2"><div class="card bg-warning"><div class="card-body"><h2><?php echo $pending; ?></h2>Pending Approval</div></div></div>
  <div class="col-md-3 mb-2"><div class="card bg-info"><div class="card-body"><h2><?php echo $events; ?></h2>Events</div></div></div>
</div>

<h4>Latest Pending Submissions</h4>
<table class="table table-bordered bg-white">
  <thead class="table-light"><tr><th>Image</th><th>Title</th><th>Student</th><th>Date</th><th>Actions</th></tr></thead>
  <tbody>
  <?php if (mysqli_num_rows($latest_pending) == 0) { echo '<tr><td colspan="5" class="text-muted text-center">Nothing waiting for approval.</td></tr>'; } ?>
  <?php while ($p = mysqli_fetch_assoc($latest_pending)) { ?>
    <tr>
      <td><img src="../uploads/<?php echo e($p['image']); ?>" width="70" height="55" style="object-fit:cover;"></td>
      <td><a href="../artwork.php?id=<?php echo $p['id']; ?>" target="_blank"><?php echo e($p['title']); ?></a></td>
      <td><?php echo e($p['full_name']); ?></td>
      <td><?php echo e($p['created_at']); ?></td>
      <td>
        <form method="post" class="d-flex flex-wrap gap-1">
  <?php echo csrf_field(); ?>
          <input type="hidden" name="id" value="<?php echo $p['id']; ?>">
          <input type="text" name="reason" class="form-control form-control-sm" style="width:140px;" placeholder="Reject reason">
          <button name="action" value="approve" class="btn btn-sm btn-success">Approve</button>
          <button name="action" value="reject" class="btn btn-sm btn-warning">Reject</button>
        </form>
      </td>
    </tr>
  <?php } ?>
  </tbody>
</table>
<a href="artworks.php?status=pending" class="btn btn-warning">Review Pending Artworks</a>
<?php include 'footer.php'; ?>
