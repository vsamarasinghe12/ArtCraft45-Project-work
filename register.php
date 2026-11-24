<?php
require 'config.php';
$page_title = "Register";

if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

if (isset($_POST['register'])) {
    $name = trim($_POST['full_name']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm = $_POST['confirm_password'];

    if ($name == "" || $email == "" || $password == "") {
        set_flash('danger', 'All fields are required.');
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        set_flash('danger', 'Please enter a valid email address.');
    } elseif (strlen($password) < 6) {
        set_flash('danger', 'Password must be at least 6 characters.');
    } elseif ($password != $confirm) {
        set_flash('danger', 'Passwords do not match.');
    } else {
        $check = run($conn, "SELECT id FROM users WHERE email=?", "s", [$email]);
        if (mysqli_num_rows($check) > 0) {
            set_flash('danger', 'This email is already registered.');
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            run($conn, "INSERT INTO users (full_name, email, password) VALUES (?,?,?)", "sss", [$name, $email, $hash]);
            set_flash('success', 'Registration successful! Please login.');
            header("Location: login.php");
            exit;
        }
    }
}
include 'header.php';
?>
<div class="row justify-content-center">
  <div class="col-md-5">
    <div class="card"><div class="card-body">
      <h3 class="mb-3">Student Registration</h3>
      <form method="post" onsubmit="return checkPasswords()">
  <?php echo csrf_field(); ?>
        <div class="mb-3"><label class="form-label">Full Name</label>
          <input type="text" name="full_name" class="form-control" required></div>
        <div class="mb-3"><label class="form-label">Email</label>
          <input type="email" name="email" class="form-control" required></div>
        <div class="mb-3"><label class="form-label">Password</label>
          <input type="password" name="password" id="password" class="form-control" minlength="6" required></div>
        <div class="mb-3"><label class="form-label">Confirm Password</label>
          <input type="password" name="confirm_password" id="confirm" class="form-control" required></div>
        <button type="submit" name="register" class="btn btn-success w-100">Register</button>
      </form>
      <p class="mt-3 mb-0">Already have an account? <a href="login.php">Login</a></p>
    </div></div>
  </div>
</div>
<script>
function checkPasswords() {
  if (document.getElementById('password').value != document.getElementById('confirm').value) {
    alert('Passwords do not match!');
    return false;
  }
  return true;
}
</script>
<?php include 'footer.php'; ?>
