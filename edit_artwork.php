<?php
require 'config.php';
require_login();
$page_title = "Edit Artwork";
$uid = $_SESSION['user_id'];
$id = (int)($_GET['id'] ?? 0);


$art = mysqli_fetch_assoc(run($conn, "SELECT * FROM artworks WHERE id=? AND user_id=?", "ii", [$id, $uid]));
if (!$art) {
    set_flash('danger', 'Artwork not found.');
    header("Location: my_submissions.php");
    exit;
}

if (isset($_POST['update'])) {
    $title = trim($_POST['title']);
    $desc = trim($_POST['description']);
    $cat = (int)$_POST['category_id'];
    $image = $art['image'];
    $ok = true;

    if ($title == "" || $desc == "" || $cat == 0) {
        set_flash('danger', 'Please fill in all fields.');
        $ok = false;
    }

    
    if ($ok && $_FILES['image']['error'] == 0) {
        $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp']) || getimagesize($_FILES['image']['tmp_name']) === false) {
            set_flash('danger', 'Only image files are allowed.');
            $ok = false;
        } elseif ($_FILES['image']['size'] > 2 * 1024 * 1024) {
            set_flash('danger', 'Image must be smaller than 2MB.');
            $ok = false;
        } else {
            $image = time() . "_" . rand(1000, 9999) . "." . $ext;
            move_uploaded_file($_FILES['image']['tmp_name'], "uploads/" . $image);
            if (file_exists("uploads/" . $art['image'])) { unlink("uploads/" . $art['image']); }
        }
    }

    if ($ok) {
        
        run($conn, "UPDATE artworks SET title=?, description=?, category_id=?, image=?, status='pending', reject_reason=NULL, is_featured=0 WHERE id=? AND user_id=?",
            "ssisii", [$title, $desc, $cat, $image, $id, $uid]);
        set_flash('success', 'Artwork updated. It is waiting for admin approval again.');
        header("Location: my_submissions.php");
        exit;
    }
}
$categories = run($conn, "SELECT * FROM categories ORDER BY name");
include 'header.php';
?>
<div class="row justify-content-center">
  <div class="col-md-7">
    <div class="card"><div class="card-body">
      <h3 class="mb-3">Edit Artwork</h3>
      <form method="post" enctype="multipart/form-data">
  <?php echo csrf_field(); ?>
        <div class="mb-3"><label class="form-label">Title</label>
          <input type="text" name="title" class="form-control" value="<?php echo e($art['title']); ?>" required></div>
        <div class="mb-3"><label class="form-label">Category</label>
          <select name="category_id" class="form-select" required>
            <?php while ($c = mysqli_fetch_assoc($categories)) { ?>
              <option value="<?php echo $c['id']; ?>" <?php if ($c['id'] == $art['category_id']) echo 'selected'; ?>><?php echo e($c['name']); ?></option>
            <?php } ?>
          </select></div>
        <div class="mb-3"><label class="form-label">Description</label>
          <textarea name="description" class="form-control" rows="4" required><?php echo e($art['description']); ?></textarea></div>
        <div class="mb-3"><label class="form-label">Current Image</label><br>
          <img src="uploads/<?php echo e($art['image']); ?>" width="150" class="rounded mb-2"><br>
          <label class="form-label">Change Image (optional)</label>
          <input type="file" name="image" class="form-control" accept="image/*"></div>
        <button type="submit" name="update" class="btn btn-success">Save Changes</button>
        <a href="my_submissions.php" class="btn btn-secondary">Cancel</a>
      </form>
    </div></div>
  </div>
</div>
<?php include 'footer.php'; ?>
