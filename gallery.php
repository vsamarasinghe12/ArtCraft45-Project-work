<?php
require 'config.php';
$page_title = "Gallery";

$cat = (int)($_GET['category'] ?? 0);
$artist = (int)($_GET['artist'] ?? 0);
$q = trim($_GET['q'] ?? '');

$where = "a.status='approved'";
$types = "";
$params = [];
if ($cat > 0) { $where .= " AND a.category_id=?"; $types .= "i"; $params[] = $cat; }
if ($artist > 0) { $where .= " AND a.user_id=?"; $types .= "i"; $params[] = $artist; }
if ($q != "") {
    $where .= " AND (a.title LIKE ? OR a.description LIKE ?)";
    $types .= "ss";
    $params[] = "%$q%";
    $params[] = "%$q%";
}

$sort = $_GET['sort'] ?? 'newest';
$orders = [
    'newest' => 'a.created_at DESC',
    'oldest' => 'a.created_at DESC',
    'liked'  => 'like_count DESC, a.created_at DESC',
    'rated'  => 'avg_rating DESC, a.created_at DESC',
    'viewed' => 'a.views DESC, a.created_at DESC'
];
if (!isset($orders[$sort])) { $sort = 'newest'; }

$per_page = 9;
$total = mysqli_fetch_assoc(run($conn, "SELECT COUNT(*) AS total FROM artworks a WHERE $where", $types, $params))['total'];
$pages = max(1, ceil($total / $per_page));
$page = (int)($_GET['page'] ?? 1);
if ($page < 1) { $page = 1; }
if ($page > $pages) { $page = $pages; }
$offset = ($page - 1) * $per_page;

$artworks = run($conn, $art_select . " WHERE $where ORDER BY " . $orders[$sort] . " LIMIT $per_page OFFSET $offset", $types, $params);

$link_params = $_GET;
unset($link_params['page']);
$base_link = http_build_query($link_params);

$categories = run($conn, "SELECT * FROM categories ORDER BY name");
$artists = run($conn, "SELECT DISTINCT u.id, u.full_name FROM users u JOIN artworks a ON a.user_id=u.id WHERE a.status='approved' ORDER BY u.full_name");
include 'header.php';
?>
<h2 class="mb-3">Explore Art Gallery</h2>

<form method="get" class="row g-2 mb-4">
  <div class="col-md-3"><input type="text" name="q" class="form-control" placeholder="Search title or description" value="<?php echo e($q); ?>"></div>
  <div class="col-md-2">
    <select name="category" class="form-select">
      <option value="0">All Categories</option>
      <?php while ($c = mysqli_fetch_assoc($categories)) { ?>
        <option value="<?php echo $c['id']; ?>" <?php if ($cat == $c['id']) echo 'selected'; ?>><?php echo e($c['name']); ?></option>
      <?php } ?>
    </select>
  </div>
  <div class="col-md-2">
    <select name="artist" class="form-select">
      <option value="0">All Artists</option>
      <?php while ($u = mysqli_fetch_assoc($artists)) { ?>
        <option value="<?php echo $u['id']; ?>" <?php if ($artist == $u['id']) echo 'selected'; ?>><?php echo e($u['full_name']); ?></option>
      <?php } ?>
    </select>
  </div>
  <div class="col-md-3">
    <select name="sort" class="form-select">
      <option value="newest" <?php if ($sort == 'newest') echo 'selected'; ?>>Newest first</option>
      <option value="oldest" <?php if ($sort == 'oldest') echo 'selected'; ?>>Oldest first</option>
      <option value="liked" <?php if ($sort == 'liked') echo 'selected'; ?>>Most liked</option>
      <option value="rated" <?php if ($sort == 'rated') echo 'selected'; ?>>Highest rated</option>
      <option value="viewed" <?php if ($sort == 'viewed') echo 'selected'; ?>>Most viewed</option>
    </select>
  </div>
  <div class="col-md-2"><button class="btn btn-success w-100">Filter</button></div>
</form>

<p class="text-muted"><?php echo $total; ?> artwork(s) found</p>
<div class="row">
  <?php if (mysqli_num_rows($artworks) == 0) { echo '<p class="text-muted">No artworks found.</p>'; } ?>
  <?php while ($a = mysqli_fetch_assoc($artworks)) { art_card($a); } ?>
</div>

<?php if ($pages > 1) { ?>
<nav>
  <ul class="pagination justify-content-center">
    <li class="page-item <?php if ($page == 1) echo 'disabled'; ?>">
      <a class="page-link" href="gallery.php?<?php echo e($base_link); ?>&page=<?php echo $page - 1; ?>">Previous</a>
    </li>
    <?php for ($i = 1; $i <= $pages; $i++) { ?>
      <li class="page-item <?php if ($i == $page) echo 'active'; ?>">
        <a class="page-link" href="gallery.php?<?php echo e($base_link); ?>&page=<?php echo $i; ?>"><?php echo $i; ?></a>
      </li>
    <?php } ?>
    <li class="page-item <?php if ($page == $pages) echo 'disabled'; ?>">
      <a class="page-link" href="gallery.php?<?php echo e($base_link); ?>&page=<?php echo $page + 1; ?>">Next</a>
    </li>
  </ul>
</nav>
<?php } ?>
<?php include 'footer.php'; ?>
