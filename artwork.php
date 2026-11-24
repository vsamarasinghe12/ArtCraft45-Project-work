<?php
require 'config.php';
$id = (int)($_GET['id'] ?? 0);
$uid = $_SESSION['user_id'] ?? 0;
$is_admin = (($_SESSION['role'] ?? '') == 'admin');

$art = mysqli_fetch_assoc(run($conn, $art_select . " WHERE a.id=?", "i", [$id]));

if (!$art || ($art['status'] != 'approved' && $art['user_id'] != $uid && !$is_admin)) {
    set_flash('warning', 'Artwork not found.');
    header("Location: gallery.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    require_login();
    $action = $_POST['action'] ?? '';

    if ($action == 'like') {
        $has = run($conn, "SELECT id FROM likes WHERE user_id=? AND artwork_id=?", "ii", [$uid, $id]);
        if (mysqli_num_rows($has) > 0) {
            run($conn, "DELETE FROM likes WHERE user_id=? AND artwork_id=?", "ii", [$uid, $id]);
        } else {
            run($conn, "INSERT INTO likes (user_id, artwork_id) VALUES (?,?)", "ii", [$uid, $id]);
        }
    } elseif ($action == 'comment') {
        $text = trim($_POST['comment']);
        if ($text == "") {
            set_flash('danger', 'Comment cannot be empty.');
        } elseif (strlen($text) > 500) {
            set_flash('danger', 'Comment is too long (max 500 characters).');
        } else {
            run($conn, "INSERT INTO comments (user_id, artwork_id, comment) VALUES (?,?,?)", "iis", [$uid, $id, $text]);
            set_flash('success', 'Comment added.');
        }
    } elseif ($action == 'rate') {
        $rating = (int)$_POST['rating'];
        if ($rating >= 1 && $rating <= 5) {
            run($conn, "INSERT INTO ratings (user_id, artwork_id, rating) VALUES (?,?,?)
                        ON DUPLICATE KEY UPDATE rating=VALUES(rating)", "iii", [$uid, $id, $rating]);
            set_flash('success', 'Thanks for rating!');
        }
    } elseif ($action == 'delete_comment') {
        $cid = (int)$_POST['comment_id'];
        if ($is_admin) {
            run($conn, "DELETE FROM comments WHERE id=?", "i", [$cid]);
        } else {
            run($conn, "DELETE FROM comments WHERE id=? AND user_id=?", "ii", [$cid, $uid]);
        }
        set_flash('success', 'Comment deleted.');
    }
    header("Location: artwork.php?id=$id");
    exit;
}

if ($art['status'] == 'approved' && $art['user_id'] != $uid && empty($_SESSION['viewed'][$id])) {
    run($conn, "UPDATE artworks SET views = views + 1 WHERE id=?", "i", [$id]);
    $_SESSION['viewed'][$id] = true;
    $art['views']++;
}

$related = run($conn, $art_select . " WHERE a.status='approved' AND a.category_id=? AND a.id<>? ORDER BY a.created_at DESC LIMIT 3", "ii", [$art['category_id'], $id]);

$likes = mysqli_fetch_assoc(run($conn, "SELECT COUNT(*) AS total FROM likes WHERE artwork_id=?", "i", [$id]))['total'];
$rate = mysqli_fetch_assoc(run($conn, "SELECT AVG(rating) AS avg_rating, COUNT(*) AS total FROM ratings WHERE artwork_id=?", "i", [$id]));
$liked = mysqli_num_rows(run($conn, "SELECT id FROM likes WHERE user_id=? AND artwork_id=?", "ii", [$uid, $id])) > 0;
$my_rating = mysqli_fetch_assoc(run($conn, "SELECT rating FROM ratings WHERE user_id=? AND artwork_id=?", "ii", [$uid, $id]));
$comments = run($conn, "SELECT cm.*, u.full_name FROM comments cm JOIN users u ON cm.user_id=u.id WHERE cm.artwork_id=? ORDER BY cm.created_at DESC", "i", [$id]);

$page_title = $art['title'];
include 'header.php';
?>
<div class="row">
  <div class="col-md-7 mb-3">
    <img src="uploads/<?php echo e($art['image']); ?>" class="art-big rounded" alt="<?php echo e($art['title']); ?>">
  </div>
  <div class="col-md-5">
    <h2><?php echo e($art['title']); ?></h2>
    <?php if ($art['status'] != 'approved') { ?>
      <span class="badge bg-warning text-dark"><?php echo e($art['status']); ?></span>
    <?php } ?>
    <p class="text-muted">
      by <a href="artist.php?id=<?php echo $art['user_id']; ?>"><?php echo e($art['full_name']); ?></a>
      | <?php echo e($art['category_name']); ?> | <?php echo e(substr($art['created_at'], 0, 10)); ?>
    </p>
    <p><?php echo nl2br(e($art['description'])); ?></p>

    <p>
      Views: <strong><?php echo $art['views']; ?></strong> |
      Likes: <strong><?php echo $likes; ?></strong> |
      Rating: <strong><?php echo $rate['total'] > 0 ? round($rate['avg_rating'], 1) . '/5' : 'No ratings'; ?></strong>
      (<?php echo $rate['total']; ?>)
    </p>

    <?php if ($uid) { ?>
      <form method="post" class="d-inline">
  <?php echo csrf_field(); ?>
        <input type="hidden" name="action" value="like">
        <button class="btn <?php echo $liked ? 'btn-danger' : 'btn-outline-danger'; ?>">
          <?php echo $liked ? 'Unlike' : 'Like'; ?>
        </button>
      </form>
      <form method="post" class="mt-3">
  <?php echo csrf_field(); ?>
        <input type="hidden" name="action" value="rate">
        <div class="input-group" style="max-width:250px;">
          <select name="rating" class="form-select">
            <?php for ($i = 5; $i >= 1; $i--) { ?>
              <option value="<?php echo $i; ?>" <?php if (($my_rating['rating'] ?? 0) == $i) echo 'selected'; ?>><?php echo $i; ?> star(s)</option>
            <?php } ?>
          </select>
          <button class="btn btn-warning">Rate</button>
        </div>
      </form>
    <?php } else { ?>
      <p><a href="login.php">Login</a> to like, rate and comment.</p>
    <?php } ?>
  </div>
</div>

<hr>
<h4>Comments</h4>
<?php if ($uid) { ?>
  <form method="post" class="mb-3">
  <?php echo csrf_field(); ?>
    <input type="hidden" name="action" value="comment">
    <textarea name="comment" class="form-control mb-2" rows="2" placeholder="Write a comment..." required></textarea>
    <button class="btn btn-success btn-sm">Post Comment</button>
  </form>
<?php } ?>
<?php if (mysqli_num_rows($comments) == 0) { echo '<p class="text-muted">No comments yet.</p>'; } ?>
<?php while ($cm = mysqli_fetch_assoc($comments)) { ?>
  <div class="card mb-2"><div class="card-body py-2">
    <strong><?php echo e($cm['full_name']); ?></strong>
    <small class="text-muted"><?php echo e($cm['created_at']); ?></small>
    <p class="mb-1"><?php echo nl2br(e($cm['comment'])); ?></p>
    <?php if ($uid && ($cm['user_id'] == $uid || $is_admin)) { ?>
      <form method="post" onsubmit="return confirm('Delete this comment?');">
  <?php echo csrf_field(); ?>
        <input type="hidden" name="action" value="delete_comment">
        <input type="hidden" name="comment_id" value="<?php echo $cm['id']; ?>">
        <button class="btn btn-sm btn-outline-danger">Delete</button>
      </form>
    <?php } ?>
  </div></div>
<?php } ?>

<?php if (mysqli_num_rows($related) > 0) { ?>
  <hr>
  <h4 class="mb-3">More in <?php echo e($art['category_name']); ?></h4>
  <div class="row">
    <?php while ($r = mysqli_fetch_assoc($related)) { art_card($r); } ?>
  </div>
<?php } ?>
<?php include 'footer.php'; ?>
