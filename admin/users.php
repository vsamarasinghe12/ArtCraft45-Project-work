<?php
require '../config.php';
require_admin();
$page_title = "Manage Users";


if (isset($_POST['delete'])) {
    $id = (int)$_POST['id'];
    $images = run($conn, "SELECT image FROM artworks WHERE user_id=?", "i", [$id]);
    while ($img = mysqli_fetch_assoc($images)) {
        if (file_exists("../uploads/" . $img['image'])) { unlink("../uploads/" . $img['image']); }
    }
    run($conn, "DELETE FROM users WHERE id=? AND role='student'", "i", [$id]);
    set_flash('success', 'Student deleted.');
    header("Location: users.php");
    exit;
}

$q = trim($_GET['q'] ?? '');
$users = run($conn, "SELECT u.*, (SELECT COUNT(*) FROM artworks WHERE user_id=u.id) AS total_art
                     FROM users u WHERE u.role='student' AND (u.full_name LIKE ? OR u.email LIKE ?) ORDER BY u.created_at DESC",
             "ss", ["%$q%", "%$q%"]);
include 'header.php';
?>
<h2 class="mb-3">Registered Students</h2>
<form method="get" class="row g-2 mb-3">
  <div class="col-md-5"><input type="text" name="q" class="form-control" placeholder="Search name or email" value="<?php echo e($q); ?>"></div>
  <div class="col-md-2"><button class="btn btn-dark w-100">Search</button></div>
</form>
<table class="table table-bordered bg-white">
  <thead class="table-light"><tr><th>Name</th><th>Email</th><th>Artworks</th><th>Joined</th><th>Action</th></tr></thead>
  <tbody>
  <?php if (mysqli_num_rows($users) == 0) { echo '<tr><td colspan="5" class="text-center text-muted">No students found.</td></tr>'; } ?>
  <?php while ($u = mysqli_fetch_assoc($users)) { ?>
    <tr>
      <td><?php echo e($u['full_name']); ?></td>
      <td><?php echo e($u['email']); ?></td>
      <td><?php echo $u['total_art']; ?></td>
      <td><?php echo e(substr($u['created_at'], 0, 10)); ?></td>
      <td>
        <form method="post" onsubmit="return confirm('Delete this student and all their artworks?');">
  <?php echo csrf_field(); ?>
          <input type="hidden" name="id" value="<?php echo $u['id']; ?>">
          <button type="submit" name="delete" class="btn btn-sm btn-danger">Delete</button>
        </form>
      </td>
    </tr>
  <?php } ?>
  </tbody>
</table>
<?php include 'footer.php'; ?>
