<?php
session_start();

if (file_exists('config.php')) {
    include 'templates/header.php'; ?>
    <div class="container py-5 text-center">
        <h2 class="text-success mb-3">✅ QR Tracker is Already Installed</h2>
        <p class="lead">To reinstall the app, please delete the <code>config.php</code> file from your server.</p>
        <a href="login.php" class="btn btn-primary mt-3">Log In</a>
    </div>
    <?php include 'templates/footer.php';
    exit;
}

$error = '';
$success = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $db_host = $_POST['db_host'];
    $db_name = $_POST['db_name'];
    $db_user = $_POST['db_user'];
    $db_pass = $_POST['db_pass'];
    $admin_email = $_POST['admin_email'];
    $admin_pass = password_hash($_POST['admin_pass'], PASSWORD_DEFAULT);

    try {
        $pdo = new PDO("mysql:host=$db_host", $db_user, $db_pass);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $pdo->exec("CREATE DATABASE IF NOT EXISTS `$db_name`");
        $pdo->exec("USE `$db_name`");

        $pdo->exec("
            CREATE TABLE IF NOT EXISTS users (
                id INT AUTO_INCREMENT PRIMARY KEY,
                name VARCHAR(100) NOT NULL,
                email VARCHAR(255) NOT NULL UNIQUE,
                password_hash VARCHAR(255) NOT NULL,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP
            );
            CREATE TABLE IF NOT EXISTS qr_links (
                id INT AUTO_INCREMENT PRIMARY KEY,
                user_id INT NOT NULL,
                slug VARCHAR(100) NOT NULL UNIQUE,
                destination_url TEXT NOT NULL,
                label VARCHAR(255) DEFAULT NULL,
                click_count INT DEFAULT 0,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
            );
            CREATE TABLE IF NOT EXISTS click_logs (
                id INT AUTO_INCREMENT PRIMARY KEY,
                qr_link_id INT NOT NULL,
                timestamp DATETIME DEFAULT CURRENT_TIMESTAMP,
                ip_address VARCHAR(45),
                user_agent TEXT,
                FOREIGN KEY (qr_link_id) REFERENCES qr_links(id) ON DELETE CASCADE
            );
        ");

        $stmt = $pdo->prepare("INSERT INTO users (name, email, password_hash) VALUES (?, ?, ?)");
        $stmt->execute(['Admin', $admin_email, $admin_pass]);

        $config_content = "<?php
\$db_host = '$db_host';
\$db_name = '$db_name';
\$db_user = '$db_user';
\$db_pass = '$db_pass';
?>";
        file_put_contents('config.php', $config_content);

        $success = "✅ Installation successful! You can now <a href='login.php'>log in</a>.";
    } catch (PDOException $e) {
        $error = "Database error: " . $e->getMessage();
    }
}
?>

<?php include 'templates/header.php'; ?>

<div class="container py-5" style="max-width: 600px;">
    <h2 class="mb-4">Install QR Tracker</h2>

    <?php if ($error): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php elseif ($success): ?>
        <div class="alert alert-success"><?= $success ?></div>
    <?php else: ?>
        <form method="post">
            <div class="mb-3">
                <input name="db_host" class="form-control" placeholder="Database Host (e.g. localhost)" required>
            </div>
            <div class="mb-3">
                <input name="db_name" class="form-control" placeholder="Database Name" required>
            </div>
            <div class="mb-3">
                <input name="db_user" class="form-control" placeholder="Database User" required>
            </div>
            <div class="mb-3">
                <input type="password" name="db_pass" class="form-control" placeholder="Database Password">
            </div>
            <hr>
            <div class="mb-3">
                <input type="email" name="admin_email" class="form-control" placeholder="Admin Email" required>
            </div>
            <div class="mb-3">
                <input type="password" name="admin_pass" class="form-control" placeholder="Admin Password" required>
            </div>
            <button class="btn btn-primary w-100">Install Now</button>
        </form>
    <?php endif; ?>
</div>

<?php include 'templates/footer.php'; ?>
