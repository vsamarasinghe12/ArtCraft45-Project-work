<?php
require '../config.php';
require_admin();
$page_title = "Categories";


if (isset($_POST['add'])) {
    $name = trim($_POST['name']);
    $desc = trim($_POST['description']);
    if ($name == "") {
        set_flash('danger', 'Category name is required.');
    } elseif (mysqli_num_rows(run($conn, "SELECT id FROM categories WHERE name=?", "s", [$name])) > 0) {
        set_flash('danger', 'That category already exists.');
    } else {
        run($conn, "INSERT INTO categories (name, description) VALUES (?,?)", "ss", [$name, $desc]);
        set_flash('success', 'Category added.');
    }
    header("Location: categories.php");
    exit;
}


if (isset($_POST['update'])) {
    $name = trim($_POST['name']);
    if ($name == "") {
        set_flash('danger', 'Category name is required.');
    } else {
        run($conn, "UPDATE categories SET name=?, description=? WHERE id=?", "ssi", [$name, trim($_POST['description']), (int)$_POST['id']]);
        set_flash('success', 'Category updated.');
    }
    header("Location: categories.php");
    exit;
}


if (isset($_POST['delete'])) {
    $id = (int)$_POST['id'];
    $used = mysqli_fetch_assoc(run($conn, "SELECT COUNT(*) AS total FROM artworks WHERE category_id=?", "i", [$id]))['total'];
    if ($used > 0) {
        set_flash('danger', 'Cannot delete: this category has artworks.');
    } else {
        run($conn, "DELETE FROM categories WHERE id=?", "i", [$id]);
        set_flash('success', 'Category deleted.');
    }
    header("Location: categories.php");
    exit;
}

$edit = null;
if (isset($_GET['edit'])) {
    $edit = mysqli_fetch_assoc(run($conn, "SELECT * FROM categories WHERE id=?", "i", [(int)$_GET['edit']]));
}
$categories = run($conn, "SELECT c.*, (SELECT COUNT(*) FROM artworks WHERE category_id=c.id) AS total FROM categories c ORDER BY c.name");
include 'header.php';
?>
<h2 class="mb-3">Manage Categories</h2>
<div class="row">
  <div class="col-md-4 mb-3">
    <div class="card"><div class="card-body">
      <h5><?php echo $edit ? 'Edit Category' : 'Add Category'; ?></h5>
      <form method="post">
  <?php echo csrf_field(); ?>
        <?php if ($edit) { ?><input type="hidden" name="id" value="<?php echo $edit['id']; ?>"><?php } ?>
        <div class="mb-3"><label class="form-label">Name</label>
          <input type="text" name="name" class="form-control" value="<?php echo e($edit['name'] ?? ''); ?>" required></div>
        <div class="mb-3"><label class="form-label">Description</label>
          <input type="text" name="description" class="form-control" value="<?php echo e($edit['description'] ?? ''); ?>"></div>
        <?php if ($edit) { ?>
          <button type="submit" name="update" class="btn btn-primary">Update</button>
          <a href="categories.php" class="btn btn-secondary">Cancel</a>
        <?php } else { ?>
          <button type="submit" name="add" class="btn btn-success">Add</button>
        <?php } ?>
      </form>
    </div></div>
  </div>
  <div class="col-md-8">
    <table class="table table-bordered bg-white">
      <thead class="table-light"><tr><th>Name</th><th>Description</th><th>Artworks</th><th>Actions</th></tr></thead>
      <tbody>
      <?php while ($c = mysqli_fetch_assoc($categories)) { ?>
        <tr>
          <td><?php echo e($c['name']); ?></td>
          <td><?php echo e($c['description']); ?></td>
          <td><?php echo $c['total']; ?></td>
          <td>
            <a href="categories.php?edit=<?php echo $c['id']; ?>" class="btn btn-sm btn-primary">Edit</a>
            <form method="post" class="d-inline" onsubmit="return confirm('Delete this category?');">
  <?php echo csrf_field(); ?>
              <input type="hidden" name="id" value="<?php echo $c['id']; ?>">
              <button type="submit" name="delete" class="btn btn-sm btn-danger">Delete</button>
            </form>
          </td>
        </tr>
      <?php } ?>
      </tbody>
    </table>
  </div>
</div>
<?php include 'footer.php'; ?>
