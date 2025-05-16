<?php
session_start();
require_once 'includes/db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}
?>

<?php include 'templates/header.php'; ?>
<title>QR Tracker - Instructions</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<div class="container py-5">
    <h1 class="mb-4">📘 QR Tracker – Setup & User Guide</h1>

    <h4>🚀 What is QR Tracker?</h4>
    <p>QR Tracker is a simple tool that lets you create short URLs with QR codes and track how many people scan them. Ideal for tradespeople who want to track marketing efforts (flyers, van signs, posters, etc).</p>

    <hr>

    <h4>🧰 Installation Steps</h4>
    <ol>
        <li>Upload the entire <code>qr-tracker</code> folder to your web hosting. Ideally in a subdirectory like <code>/qr-tracker</code>.</li>
        <li>Create a MySQL database and user in your hosting panel (Plesk/cPanel/etc).</li>
        <li>Visit <code>yourdomain.co.uk/qr-tracker/install.php</code> in your browser.</li>
        <li>Enter your database credentials and create an admin user.</li>
        <li>Start creating QR codes and tracking scans!</li>
    </ol>

    <hr>

    <h4>🔁 URL Not Found / 404 Errors?</h4>
    <p>If you're using the tracker in a subdirectory (like <code>/qr-tracker</code>), open <code>index.php</code> and change:</p>
    <pre><code>// Old
$slug = ltrim($_SERVER['REQUEST_URI'], '/');

// New
$basePath = '/qr-tracker/';
$slug = substr($_SERVER['REQUEST_URI'], strlen($basePath));
</code></pre>

    <hr>

    <h4>📊 What’s Included</h4>
    <ul>
        <li>Create & download QR codes with your own short URLs</li>
        <li>Track clicks with timestamps, device type & IP</li>
        <li>Beautiful charts with stats for the last 7/30/all days</li>
        <li>Device breakdown: mobile, tablet, desktop</li>
        <li>Edit or delete QR codes from the dashboard</li>
    </ul>

    <hr>

    <h4>🔐 Security Notes</h4>
    <ul>
        <li>Only logged-in users can access the dashboard</li>
        <li>Each user sees only their own QR codes</li>
        <li>No third-party dependencies – you host and own everything</li>
    </ul>

    <hr>

    <h4>🙋 Need Help?</h4>
    <p>If you’re one of Gary’s customers or a ToolBox member, drop him a message and he’ll help you get it running smoothly.</p>

    <p class="text-muted mt-5">Built with ❤️ by Gary Pratten – Open source. Private. Yours.</p>
</div>
<?php include 'templates/footer.php'; ?>
