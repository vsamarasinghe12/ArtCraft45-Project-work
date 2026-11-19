<?php
require 'config.php';
$page_title = "Events";
$uid = $_SESSION['user_id'] ?? 0;
$is_student = (($_SESSION['role'] ?? '') == 'student');

// Register for an event (optionally with one of my approved artworks)
if (isset($_POST['register_event'])) {
    require_login();
    $event_id = (int)$_POST['event_id'];
    $art_id = (int)$_POST['artwork_id'];
    $exists = mysqli_num_rows(run($conn, "SELECT id FROM event_registrations WHERE event_id=? AND user_id=?", "ii", [$event_id, $uid]));
    $event = mysqli_fetch_assoc(run($conn, "SELECT * FROM events WHERE id=?", "i", [$event_id]));
    $taken = mysqli_fetch_assoc(run($conn, "SELECT COUNT(*) AS total FROM event_registrations WHERE event_id=?", "i", [$event_id]))['total'];

    if (!$is_student) {
        set_flash('warning', 'Only students can register for events.');
    } elseif (!$event) {
        set_flash('danger', 'Event not found.');
    } elseif ($event['event_date'] < date('Y-m-d')) {
        set_flash('warning', 'This event is already over.');
    } elseif ($exists > 0) {
        set_flash('warning', 'You are already registered for this event.');
    } elseif ($event['max_participants'] > 0 && $taken >= $event['max_participants']) {
        set_flash('warning', 'Sorry, this event is full.');
    } else {
        // artwork must belong to the student and be approved
        $own = mysqli_num_rows(run($conn, "SELECT id FROM artworks WHERE id=? AND user_id=? AND status='approved'", "ii", [$art_id, $uid]));
        if ($own > 0) {
            run($conn, "INSERT INTO event_registrations (event_id, user_id, artwork_id) VALUES (?,?,?)", "iii", [$event_id, $uid, $art_id]);
        } else {
            run($conn, "INSERT INTO event_registrations (event_id, user_id) VALUES (?,?)", "ii", [$event_id, $uid]);
        }
        set_flash('success', 'You are registered for the event!');
    }
    header("Location: events.php");
    exit;
}

// Cancel registration
if (isset($_POST['cancel_event'])) {
    require_login();
    run($conn, "DELETE FROM event_registrations WHERE event_id=? AND user_id=?", "ii", [(int)$_POST['event_id'], $uid]);
    set_flash('success', 'Registration cancelled.');
    header("Location: events.php");
    exit;
}

$events = run($conn, "SELECT * FROM events ORDER BY event_date DESC");
$my_art = run($conn, "SELECT id, title FROM artworks WHERE user_id=? AND status='approved'", "i", [$uid]);
$my_art_list = [];
while ($r = mysqli_fetch_assoc($my_art)) { $my_art_list[] = $r; }
include 'header.php';
?>
<h2 class="mb-3">Showcase Events &amp; Exhibitions</h2>
<?php if (mysqli_num_rows($events) == 0) { echo '<p class="text-muted">No events yet.</p>'; } ?>
<?php while ($ev = mysqli_fetch_assoc($events)) {
    $reg = mysqli_num_rows(run($conn, "SELECT id FROM event_registrations WHERE event_id=? AND user_id=?", "ii", [$ev['id'], $uid])) > 0;
    $count = mysqli_fetch_assoc(run($conn, "SELECT COUNT(*) AS total FROM event_registrations WHERE event_id=?", "i", [$ev['id']]))['total'];
    $past = ($ev['event_date'] < date('Y-m-d'));
    $full = ($ev['max_participants'] > 0 && $count >= $ev['max_participants']);
?>
  <div class="card mb-3"><div class="card-body">
    <h4><?php echo e($ev['title']); ?> <?php if ($past) echo '<span class="badge bg-secondary">Past</span>'; ?></h4>
    <p class="text-muted mb-1">Date: <?php echo e($ev['event_date']); ?> | Location: <?php echo e($ev['location']); ?> | Participants: <?php echo $count; ?><?php if ($ev['max_participants'] > 0) echo ' / ' . $ev['max_participants']; ?>
      <?php if ($full && !$past) echo '<span class="badge bg-danger">Full</span>'; ?></p>
    <p><?php echo nl2br(e($ev['description'])); ?></p>

    <?php if (!$past && $is_student) { ?>
      <?php if ($reg) { ?>
        <form method="post" onsubmit="return confirm('Cancel your registration?');">
  <?php echo csrf_field(); ?>
          <input type="hidden" name="event_id" value="<?php echo $ev['id']; ?>">
          <span class="badge bg-success">Registered</span>
          <button type="submit" name="cancel_event" class="btn btn-sm btn-outline-danger">Cancel</button>
        </form>
      <?php } elseif ($full) { ?>
        <p class="text-danger mb-0">This event is full.</p>
      <?php } else { ?>
        <form method="post" class="row g-2">
  <?php echo csrf_field(); ?>
          <input type="hidden" name="event_id" value="<?php echo $ev['id']; ?>">
          <div class="col-md-5">
            <select name="artwork_id" class="form-select form-select-sm">
              <option value="0">No artwork (just attending)</option>
              <?php foreach ($my_art_list as $m) { ?>
                <option value="<?php echo $m['id']; ?>"><?php echo e($m['title']); ?></option>
              <?php } ?>
            </select>
          </div>
          <div class="col-md-3"><button type="submit" name="register_event" class="btn btn-sm btn-success">Register</button></div>
        </form>
      <?php } ?>
    <?php } elseif (!$past && !$uid) { ?>
      <a href="login.php">Login</a> as a student to register.
    <?php } ?>
  </div></div>
<?php } ?>
<?php include 'footer.php'; ?>
