<?php
require_once 'includes/db.php';
session_start();

// Get the slug from the URL
$basePath = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/') . '/';
$slug = ltrim(str_replace($basePath, '', $_SERVER['REQUEST_URI']), '/');

// Show homepage info if no slug
if ($slug === '' || $slug === 'index.php') {
    include 'templates/header.php';
    ?>
    <div class="container py-5 text-center">
        <h1 class="mb-4">Welcome to QR Tracker 👋</h1>
        <p class="lead mb-3">This is your private QR code dashboard — create, track, and manage all your QR links in one place.</p>
        <p>Log in to get started creating and tracking your QR codes.</p>
        <a href="login.php" class="btn btn-primary mt-3">Login</a>
        <p class="text-muted mt-5 small">If you haven’t installed the app yet, <a href="install.php">run the installer</a>.</p>
    </div>
    <?php
    include 'templates/footer.php';
    exit;
}

// Lookup redirect
$stmt = $pdo->prepare("SELECT * FROM qr_links WHERE slug = ?");
$stmt->execute([$slug]);
$link = $stmt->fetch();

if ($link) {
    // Log the click
    $log = $pdo->prepare("INSERT INTO click_logs (qr_link_id, ip_address, user_agent) VALUES (?, ?, ?)");
    $log->execute([
        $link['id'],
        $_SERVER['REMOTE_ADDR'],
        $_SERVER['HTTP_USER_AGENT']
    ]);

    // Increment count
    $pdo->prepare("UPDATE qr_links SET click_count = click_count + 1 WHERE id = ?")->execute([$link['id']]);

    // Redirect to destination
    header("Location: " . $link['destination_url']);
    exit;

} else {
    include 'templates/header.php';
    ?>
    <div class="container py-5 text-center">
        <h2 class="text-danger mb-3">404 – Link Not Found</h2>
        <p class="text-muted">The QR code you scanned may have expired or been removed.</p>
        <a href="/" class="btn btn-outline-primary mt-3">Back to Home</a>
    </div>
    <?php
    include 'templates/footer.php';
}
