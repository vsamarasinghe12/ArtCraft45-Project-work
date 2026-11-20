<?php
require 'config.php';
require_login();
$page_title = "My Profile";
$uid = $_SESSION['user_id'];

if (isset($_POST['update_profile'])) {
    $name = trim($_POST['full_name'] ?? '');
    $bio = trim($_POST['bio'] ?? '');
    if ($name == "") {
        set_flash('danger', 'Name cannot be empty.');
    } else {
        run($conn, "UPDATE users SET full_name=?, bio=? WHERE id=?", "ssi", [$name, $bio, $uid]);
        $_SESSION['user_name'] = $name;
        set_flash('success', 'Profile updated.');
    }
    header("Location: profile.php");
    exit;
}

if (isset($_POST['change_password'])) {
    $user = mysqli_fetch_assoc(run($conn, "SELECT password FROM users WHERE id=?", "i", [$uid]));
    if (!$user || !password_verify($_POST['old_password'] ?? '', $user['password'])) {
        set_flash('danger', 'Current password is wrong.');
    } elseif (strlen($_POST['new_password'] ?? '') < 6) {
        set_flash('danger', 'New password must be at least 6 characters.');
    } else {
        $hash = password_hash($_POST['new_password'] ?? '', PASSWORD_DEFAULT);
        run($conn, "UPDATE users SET password=? WHERE id=?", "si", [$hash, $uid]);
        set_flash('success', 'Password changed.');
    }
    header("Location: profile.php");
    exit;
}

$me = mysqli_fetch_assoc(run($conn, "SELECT * FROM users WHERE id=?", "i", [$uid]));
if(!$me){
  die("User details not found.Please login again.");
}
$stats = mysqli_fetch_assoc(run($conn, "SELECT
    (SELECT COUNT(*) FROM artworks WHERE user_id=? AND status='approved') AS art,
    (SELECT COUNT(*) FROM likes l JOIN artworks a ON l.artwork_id=a.id WHERE a.user_id=?) AS likes,
    (SELECT COUNT(*) FROM follows WHERE artist_id=?) AS followers", "iii", [$uid, $uid, $uid]));
// Artists I follow
$following = run($conn, "SELECT u.id, u.full_name FROM follows f JOIN users u ON f.artist_id=u.id WHERE f.follower_id=?", "i", [$uid]);
// Events I registered for
$my_events = run($conn, "SELECT e.title, e.event_date, r.artwork_id FROM event_registrations r JOIN events e ON r.event_id=e.id WHERE r.user_id=? ORDER BY e.event_date", "i", [$uid]);
include 'header.php';
?>
<div class="row text-center mb-3">
  <div class="col-4"><div class="card"><div class="card-body py-2"><h3 class="mb-0"><?php echo $stats['art']; ?></h3>Approved artworks</div></div></div>
  <div class="col-4"><div class="card"><div class="card-body py-2"><h3 class="mb-0"><?php echo $stats['likes']; ?></h3>Likes received</div></div></div>
  <div class="col-4"><div class="card"><div class="card-body py-2"><h3 class="mb-0"><?php echo $stats['followers']; ?></h3>Followers</div></div></div>
</div>
<div class="row">
  <div class="col-md-6 mb-3">
    <div class="card"><div class="card-body">
      <h4>Edit Profile</h4>
      <form method="post">
        <?php csrf_field();?>

        <div class="mb-3">
          <label class="form-label">Email</label>
          <input type="email"
          class="form-control"
          value="<?php echo e($me['email'] ?? ''); ?>"
          disabled>
        </div>
        <div  class="mb-3">
          <label class="form-label">Full Name</label>
          <input type="text"
          name="full_name"
          class="form-control"
          value="<?php echo e($me['full_name'] ?? ''); ?>"
          required
          >
        </div>
        <div class="mb-3">
           <label class="form-label">About me(shown on your artist page)</label>
           <textarea name="bio" class="form-control" rows="3" placeholder="Enter your about"><?php echo e($me['bio'] ?? ''); ?></textarea>
        </div>
        <button type="submit" name="update_profile" class="btn btn-primary">Update Profile</button>
      </form>
      </div>
    </div>
  </div>

  <div class="col-md-6 mb-3">
    <div class="card mb-3"><div class="card-body">
      <h4>Change Password</h4>
      <form method="post">
  <?php csrf_field(); ?>
        <div class="mb-3"><input type="password" name="old_password" class="form-control" placeholder="Current password" required></div>
        <div class="mb-3"><input type="password" name="new_password" class="form-control" placeholder="New password (min 6)" minlength="6" required></div>
        <button type="submit" name="change_password" class="btn btn-primary">Change Password</button>
      </form>
    </div></div>
    <div class="card mb-3"><div class="card-body">
      <h5>Artists I Follow</h5>
      <?php if (mysqli_num_rows($following) == 0) { echo '<p class="text-muted mb-0">You are not following anyone.</p>'; } ?>
      <ul class="mb-0">
      <?php while ($f = mysqli_fetch_assoc($following)) { ?>
        <li><a href="artist.php?id=<?php echo $f['id']; ?>"><?php echo e($f['full_name']); ?></a></li>
      <?php } ?>
      </ul>
    </div></div>
    <div class="card"><div class="card-body">
      <h5>My Event Registrations</h5>
      <?php if (mysqli_num_rows($my_events) == 0) { echo '<p class="text-muted mb-0">No registrations yet.</p>'; } ?>
      <ul class="mb-0">
      <?php while ($ev = mysqli_fetch_assoc($my_events)) { ?>
        <li><?php echo e($ev['title']); ?> (<?php echo e($ev['event_date']); ?>)</li>
      <?php } ?>
      </ul>
    </div></div>
  </div>
</div>
<?php include 'footer.php'; ?>
