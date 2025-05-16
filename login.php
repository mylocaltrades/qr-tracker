<?php
session_start();
require_once 'includes/db.php';

// If already logged in, redirect
if (isset($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password_hash'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['name'];
        header('Location: dashboard.php');
        exit;
    } else {
        $error = "Invalid login details.";
    }
}
?>
<?php include 'templates/header.php'; ?>

<div class="container py-5" style="max-width: 500px;">
    <h2 class="mb-4 text-center">Login</h2>

    <?php if ($error): ?>
        <div class="alert alert-danger text-center"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="post">
        <div class="mb-3">
            <label>Email</label>
            <input name="email" type="email" class="form-control" required autofocus>
        </div>
        <div class="mb-3">
            <label>Password</label>
            <input name="password" type="password" class="form-control" required>
        </div>
        <button class="btn btn-primary w-100">Log In</button>
    </form>
</div>

<?php include 'templates/footer.php'; ?>