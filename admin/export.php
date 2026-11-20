<?php

require '../config.php';
require_admin();

$type = $_GET['type'] ?? '';

if ($type == 'students') {
    $rows = run($conn, "SELECT u.full_name, u.email, u.created_at, COUNT(a.id) AS total_art
                        FROM users u LEFT JOIN artworks a ON a.user_id=u.id
                        WHERE u.role='student' GROUP BY u.id ORDER BY u.full_name");
    $header = ['Name', 'Email', 'Joined', 'Artworks'];
} elseif ($type == 'artworks') {
    $rows = run($conn, "SELECT a.title, u.full_name, c.name, a.status, a.views, a.created_at
                        FROM artworks a JOIN users u ON a.user_id=u.id JOIN categories c ON a.category_id=c.id
                        ORDER BY a.created_at DESC");
    $header = ['Title', 'Student', 'Category', 'Status', 'Views', 'Submitted'];
} elseif ($type == 'registrations') {
    $rows = run($conn, "SELECT e.title, e.event_date, u.full_name, u.email, a.title AS art_title
                        FROM event_registrations r JOIN events e ON r.event_id=e.id JOIN users u ON r.user_id=u.id
                        LEFT JOIN artworks a ON r.artwork_id=a.id ORDER BY e.event_date DESC, u.full_name");
    $header = ['Event', 'Event Date', 'Student', 'Email', 'Artwork'];
} else {
    set_flash('danger', 'Unknown export type.');
    header("Location: reports.php");
    exit;
}

header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="artcraft_' . $type . '.csv"');

$out = fopen('php://output', 'w');
fputcsv($out, $header);
while ($r = mysqli_fetch_row($rows)) {
    fputcsv($out, $r);
}
fclose($out);
?>
