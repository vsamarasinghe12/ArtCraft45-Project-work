<?php
require 'config.php';
$page_title = "Login";

// Already logged in? No need to see the login page
if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

if (isset($_POST['login'])) {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    $result = run($conn, "SELECT * FROM users WHERE email=?", "s", [$email]);
    $user = mysqli_fetch_assoc($result);

    if ($user && password_verify($password, $user['password'])) {
        session_regenerate_id(true);
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['full_name'];
        $_SESSION['role'] = $user['role'];
        if ($user['role'] == 'admin') {
            header("Location: admin/index.php");
        } else {
            header("Location: index.php");
        }
        exit;
    } else {
        set_flash('danger', 'Invalid email or password.');
    }
}
include 'header.php';
?>
<div class="row justify-content-center">
  <div class="col-md-5">
    <div class="card"><div class="card-body">
      <h3 class="mb-3">Login</h3>
      <form method="post">
  <?php echo csrf_field(); ?>
        <div class="mb-3"><label class="form-label">Email</label>
          <input type="email" name="email" class="form-control" required></div>
        <div class="mb-3"><label class="form-label">Password</label>
          <input type="password" name="password" class="form-control" required></div>
        <button type="submit" name="login" class="btn btn-success w-100">Login</button>
      </form>
      <p class="mt-3 mb-0">New student? <a href="register.php">Register here</a></p>
    </div></div>
  </div>
</div>
<?php include 'footer.php'; ?>
