<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?php echo e($page_title ?? 'Home'); ?> - NSBM ArtCraft</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="style.css">
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark bg-success">
  <div class="container">
    <a class="navbar-brand" href="index.php">NSBM ArtCraft</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#menu">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="menu">
      <ul class="navbar-nav me-auto">
        <li class="nav-item"><a class="nav-link" href="index.php">Home</a></li>
        <li class="nav-item"><a class="nav-link" href="gallery.php">Gallery</a></li>
        <li class="nav-item"><a class="nav-link" href="artists.php">Artists</a></li>
        <li class="nav-item"><a class="nav-link" href="events.php">Events</a></li>
        <?php if (isset($_SESSION['user_id']) && $_SESSION['role'] == 'student') { ?>
          <li class="nav-item"><a class="nav-link" href="upload.php">Upload</a></li>
          <li class="nav-item"><a class="nav-link" href="my_submissions.php">My Submissions</a></li>
        <?php } ?>
      </ul>
      <ul class="navbar-nav">
        <?php if (isset($_SESSION['user_id'])) { ?>
          <?php if ($_SESSION['role'] == 'admin') { ?>
            <li class="nav-item"><a class="nav-link" href="admin/index.php">Admin Panel</a></li>
          <?php } else { ?>
            <li class="nav-item"><a class="nav-link" href="profile.php">Hi, <?php echo e($_SESSION['user_name']); ?></a></li>
          <?php } ?>
          <li class="nav-item"><a class="nav-link" href="logout.php">Logout</a></li>
        <?php } else { ?>
          <li class="nav-item"><a class="nav-link" href="login.php">Login</a></li>
          <li class="nav-item"><a class="nav-link" href="register.php">Register</a></li>
        <?php } ?>
      </ul>
    </div>
  </div>
</nav>
<div class="container mt-4">
<?php show_flash(); ?>
