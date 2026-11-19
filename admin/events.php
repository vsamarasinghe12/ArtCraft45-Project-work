<?php
require '../config.php';
require_admin();
$page_title = "Manage Events";

// Add event
if (isset($_POST['add'])) {
    $title = trim($_POST['title']);
    $desc = trim($_POST['description']);
    $date = $_POST['event_date'];
    $loc = trim($_POST['location']);
    $max = max(0, (int)$_POST['max_participants']);
    if ($title == "" || $desc == "" || $date == "" || $loc == "") {
        set_flash('danger', 'Please fill in all fields.');
    } else {
        run($conn, "INSERT INTO events (title, description, event_date, location, max_participants) VALUES (?,?,?,?,?)", "ssssi", [$title, $desc, $date, $loc, $max]);
        set_flash('success', 'Event created.');
    }
    header("Location: events.php");
    exit;
}

// Update event
if (isset($_POST['update'])) {
    $title = trim($_POST['title']);
    $desc = trim($_POST['description']);
    $date = $_POST['event_date'];
    $loc = trim($_POST['location']);
    $max = max(0, (int)$_POST['max_participants']);
    if ($title == "" || $desc == "" || $date == "" || $loc == "") {
        set_flash('danger', 'Please fill in all fields.');
    } else {
        run($conn, "UPDATE events SET title=?, description=?, event_date=?, location=?, max_participants=? WHERE id=?", "ssssii", [$title, $desc, $date, $loc, $max, (int)$_POST['id']]);
        set_flash('success', 'Event updated.');
    }
    header("Location: events.php");
    exit;
}

// Delete event
if (isset($_POST['delete'])) {
    run($conn, "DELETE FROM events WHERE id=?", "i", [(int)$_POST['id']]);
    set_flash('success', 'Event deleted.');
    header("Location: events.php");
    exit;
}

// Remove one participant
if (isset($_POST['remove_participant'])) {
    run($conn, "DELETE FROM event_registrations WHERE id=?", "i", [(int)$_POST['reg_id']]);
    set_flash('success', 'Participant removed.');
    header("Location: events.php?view=" . (int)$_POST['event_id']);
    exit;
}

$edit = null;
if (isset($_GET['edit'])) {
    $edit = mysqli_fetch_assoc(run($conn, "SELECT * FROM events WHERE id=?", "i", [(int)$_GET['edit']]));
}
$view = (int)($_GET['view'] ?? 0);
$events = run($conn, "SELECT e.*, (SELECT COUNT(*) FROM event_registrations WHERE event_id=e.id) AS total FROM events e ORDER BY e.event_date DESC");
include 'header.php';
?>
<h2 class="mb-3">Manage Events / Exhibitions</h2>
<div class="row">
  <div class="col-md-4 mb-3">
    <div class="card"><div class="card-body">
      <h5><?php echo $edit ? 'Edit Event' : 'Create Event'; ?></h5>
      <form method="post">
  <?php echo csrf_field(); ?>
        <?php if ($edit) { ?><input type="hidden" name="id" value="<?php echo $edit['id']; ?>"><?php } ?>
        <div class="mb-3"><label class="form-label">Title</label>
          <input type="text" name="title" class="form-control" value="<?php echo e($edit['title'] ?? ''); ?>" required></div>
        <div class="mb-3"><label class="form-label">Date</label>
          <input type="date" name="event_date" class="form-control" value="<?php echo e($edit['event_date'] ?? ''); ?>" required></div>
        <div class="mb-3"><label class="form-label">Location</label>
          <input type="text" name="location" class="form-control" value="<?php echo e($edit['location'] ?? ''); ?>" required></div>
        <div class="mb-3"><label class="form-label">Max participants (0 = no limit)</label>
          <input type="number" name="max_participants" min="0" class="form-control" value="<?php echo e($edit['max_participants'] ?? 0); ?>"></div>
        <div class="mb-3"><label class="form-label">Description</label>
          <textarea name="description" class="form-control" rows="3" required><?php echo e($edit['description'] ?? ''); ?></textarea></div>
        <?php if ($edit) { ?>
          <button type="submit" name="update" class="btn btn-primary">Update</button>
          <a href="events.php" class="btn btn-secondary">Cancel</a>
        <?php } else { ?>
          <button type="submit" name="add" class="btn btn-success">Create Event</button>
        <?php } ?>
      </form>
    </div></div>
  </div>
  <div class="col-md-8">
    <table class="table table-bordered bg-white">
      <thead class="table-light"><tr><th>Title</th><th>Date</th><th>Location</th><th>Participants</th><th>Actions</th></tr></thead>
      <tbody>
      <?php while ($ev = mysqli_fetch_assoc($events)) { ?>
        <tr>
          <td><?php echo e($ev['title']); ?></td>
          <td><?php echo e($ev['event_date']); ?></td>
          <td><?php echo e($ev['location']); ?></td>
          <td><?php echo $ev['total']; ?><?php if ($ev['max_participants'] > 0) echo ' / ' . $ev['max_participants']; ?></td>
          <td>
            <a href="events.php?view=<?php echo $ev['id']; ?>" class="btn btn-sm btn-info">Participants</a>
            <a href="events.php?edit=<?php echo $ev['id']; ?>" class="btn btn-sm btn-primary">Edit</a>
            <form method="post" class="d-inline" onsubmit="return confirm('Delete this event?');">
  <?php echo csrf_field(); ?>
              <input type="hidden" name="id" value="<?php echo $ev['id']; ?>">
              <button type="submit" name="delete" class="btn btn-sm btn-danger">Delete</button>
            </form>
          </td>
        </tr>
      <?php } ?>
      </tbody>
    </table>

    <?php if ($view > 0) {
        $parts = run($conn, "SELECT r.id, r.registered_at, u.full_name, u.email, a.title AS art_title
                             FROM event_registrations r JOIN users u ON r.user_id=u.id
                             LEFT JOIN artworks a ON r.artwork_id=a.id WHERE r.event_id=?", "i", [$view]);
    ?>
      <h5>Participants</h5>
      <table class="table table-bordered bg-white">
        <thead class="table-light"><tr><th>Student</th><th>Email</th><th>Artwork</th><th>Registered</th><th></th></tr></thead>
        <tbody>
        <?php if (mysqli_num_rows($parts) == 0) { echo '<tr><td colspan="5" class="text-center text-muted">No participants yet.</td></tr>'; } ?>
        <?php while ($p = mysqli_fetch_assoc($parts)) { ?>
          <tr>
            <td><?php echo e($p['full_name']); ?></td>
            <td><?php echo e($p['email']); ?></td>
            <td><?php echo e($p['art_title'] ?? '-'); ?></td>
            <td><?php echo e(substr($p['registered_at'], 0, 10)); ?></td>
            <td>
              <form method="post" onsubmit="return confirm('Remove this participant?');">
  <?php echo csrf_field(); ?>
                <input type="hidden" name="reg_id" value="<?php echo $p['id']; ?>">
                <input type="hidden" name="event_id" value="<?php echo $view; ?>">
                <button type="submit" name="remove_participant" class="btn btn-sm btn-outline-danger">Remove</button>
              </form>
            </td>
          </tr>
        <?php } ?>
        </tbody>
      </table>
    <?php } ?>
  </div>
</div>
<?php include 'footer.php'; ?>
