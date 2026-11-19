<?php
require 'config.php';
require_login();
if ($_SESSION['role'] != 'student') {
    set_flash('warning', 'Only students can upload artworks.');
    header("Location: index.php");
    exit;
}
$page_title = "Upload Artwork";

$categories = run($conn, "SELECT * FROM categories ORDER BY name");

if (isset($_POST['upload'])) {
    $title = trim($_POST['title']);
    $desc = trim($_POST['description']);
    $cat = (int)$_POST['category_id'];
    $file = $_FILES['image'];

    $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

    if ($title == "" || $desc == "" || $cat == 0) {
        set_flash('danger', 'Please fill in all fields.');
    } elseif ($file['error'] != 0) {
        set_flash('danger', 'Please choose an image to upload.');
    } elseif (!in_array($ext, $allowed) || getimagesize($file['tmp_name']) === false) {
        set_flash('danger', 'Only image files (jpg, png, gif, webp) are allowed.');
    } elseif ($file['size'] > 2 * 1024 * 1024) {
        set_flash('danger', 'Image must be smaller than 2MB.');
    } else {
        $new_name = time() . "_" . rand(1000, 9999) . "." . $ext;
        move_uploaded_file($file['tmp_name'], "uploads/" . $new_name);
        run($conn, "INSERT INTO artworks (user_id, category_id, title, description, image) VALUES (?,?,?,?,?)",
            "iisss", [$_SESSION['user_id'], $cat, $title, $desc, $new_name]);
        set_flash('success', 'Artwork submitted! It will appear in the gallery after admin approval.');
        header("Location: my_submissions.php");
        exit;
    }
}
include 'header.php';
?>
<div class="row justify-content-center">
  <div class="col-md-7">
    <div class="card"><div class="card-body">
      <h3 class="mb-3">Upload Artwork / Handcraft</h3>
      <form method="post" enctype="multipart/form-data">
  <?php echo csrf_field(); ?>
        <div class="mb-3"><label class="form-label">Title</label>
          <input type="text" name="title" class="form-control" required></div>
        <div class="mb-3"><label class="form-label">Category</label>
          <select name="category_id" class="form-select" required>
            <option value="">-- Select category --</option>
            <?php while ($c = mysqli_fetch_assoc($categories)) { ?>
              <option value="<?php echo $c['id']; ?>"><?php echo e($c['name']); ?></option>
            <?php } ?>
          </select></div>
        <div class="mb-3"><label class="form-label">Description</label>
          <textarea name="description" class="form-control" rows="4" required></textarea></div>
        <div class="mb-3"><label class="form-label">Image (max 2MB)</label>
          <input type="file" name="image" class="form-control" accept="image/*" required></div>
        <button type="submit" name="upload" class="btn btn-success">Submit Artwork</button>
      </form>
    </div></div>
  </div>
</div>
<?php include 'footer.php'; ?>
