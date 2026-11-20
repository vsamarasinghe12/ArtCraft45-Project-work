<?php
require '../config.php';
require_admin();
$page_title = "Reports";


$by_status = run($conn, "SELECT status, COUNT(*) AS total FROM artworks GROUP BY status");

$by_category = run($conn, "SELECT c.name, COUNT(a.id) AS total FROM categories c LEFT JOIN artworks a ON a.category_id=c.id GROUP BY c.id ORDER BY total DESC");
// 3. Submissions by student
$by_student = run($conn, "SELECT u.full_name, COUNT(a.id) AS total, SUM(a.status='approved') AS approved
                          FROM users u JOIN artworks a ON a.user_id=u.id GROUP BY u.id ORDER BY total DESC");

$by_event = run($conn, "SELECT e.title, e.event_date, COUNT(r.id) AS total FROM events e LEFT JOIN event_registrations r ON r.event_id=e.id GROUP BY e.id ORDER BY e.event_date DESC");

$top_liked = run($conn, "SELECT a.title, u.full_name, COUNT(l.id) AS total FROM artworks a JOIN users u ON a.user_id=u.id
                         LEFT JOIN likes l ON l.artwork_id=a.id WHERE a.status='approved' GROUP BY a.id ORDER BY total DESC LIMIT 5");
include 'header.php';
?>
<h2 class="mb-3">Submission &amp; Participation Reports</h2>
<div class="no-print mb-3">
  <button onclick="window.print()" class="btn btn-dark">Print Report</button>
  <a href="export.php?type=students" class="btn btn-outline-dark">Students (CSV)</a>
  <a href="export.php?type=artworks" class="btn btn-outline-dark">Artworks (CSV)</a>
  <a href="export.php?type=registrations" class="btn btn-outline-dark">Event Registrations (CSV)</a>
</div>

<div class="row">
  <div class="col-md-6 mb-4">
    <h5>Submissions by Status</h5>
    <table class="table table-bordered bg-white">
      <thead class="table-light"><tr><th>Status</th><th>Total</th></tr></thead>
      <tbody><?php while ($r = mysqli_fetch_assoc($by_status)) { ?>
        <tr><td><?php echo e($r['status']); ?></td><td><?php echo $r['total']; ?></td></tr>
      <?php } ?></tbody>
    </table>
  </div>
  <div class="col-md-6 mb-4">
    <h5>Submissions by Category</h5>
    <table class="table table-bordered bg-white">
      <thead class="table-light"><tr><th>Category</th><th>Total</th></tr></thead>
      <tbody><?php while ($r = mysqli_fetch_assoc($by_category)) { ?>
        <tr><td><?php echo e($r['name']); ?></td><td><?php echo $r['total']; ?></td></tr>
      <?php } ?></tbody>
    </table>
  </div>
  <div class="col-md-6 mb-4">
    <h5>Submissions by Student</h5>
    <table class="table table-bordered bg-white">
      <thead class="table-light"><tr><th>Student</th><th>Submitted</th><th>Approved</th></tr></thead>
      <tbody><?php while ($r = mysqli_fetch_assoc($by_student)) { ?>
        <tr><td><?php echo e($r['full_name']); ?></td><td><?php echo $r['total']; ?></td><td><?php echo $r['approved']; ?></td></tr>
      <?php } ?></tbody>
    </table>
  </div>
  <div class="col-md-6 mb-4">
    <h5>Event Participation</h5>
    <table class="table table-bordered bg-white">
      <thead class="table-light"><tr><th>Event</th><th>Date</th><th>Participants</th></tr></thead>
      <tbody><?php while ($r = mysqli_fetch_assoc($by_event)) { ?>
        <tr><td><?php echo e($r['title']); ?></td><td><?php echo e($r['event_date']); ?></td><td><?php echo $r['total']; ?></td></tr>
      <?php } ?></tbody>
    </table>
  </div>
  <div class="col-md-6 mb-4">
    <h5>Top 5 Most Liked Artworks</h5>
    <table class="table table-bordered bg-white">
      <thead class="table-light"><tr><th>Title</th><th>Artist</th><th>Likes</th></tr></thead>
      <tbody><?php while ($r = mysqli_fetch_assoc($top_liked)) { ?>
        <tr><td><?php echo e($r['title']); ?></td><td><?php echo e($r['full_name']); ?></td><td><?php echo $r['total']; ?></td></tr>
      <?php } ?></tbody>
    </table>
  </div>
</div>
<?php include 'footer.php'; ?>
