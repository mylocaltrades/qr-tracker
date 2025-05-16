<?php
session_start();
require_once 'includes/db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

function generateSlug($length = 6) {
    return substr(str_shuffle('abcdefghijklmnopqrstuvwxyz0123456789'), 0, $length);
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $destination = trim($_POST['destination_url']);

    if (!filter_var($destination, FILTER_VALIDATE_URL)) {
        $error = "Invalid URL format.";
    } else {
        // Auto-generate unique slug
        do {
            $slug = generateSlug();
            $stmt = $pdo->prepare("SELECT id FROM qr_links WHERE slug = ?");
            $stmt->execute([$slug]);
        } while ($stmt->rowCount() > 0);

        // Save to DB
        $label = trim($_POST['label'] ?? '');
        $stmt = $pdo->prepare("INSERT INTO qr_links (user_id, slug, destination_url, label) VALUES (?, ?, ?, ?)");
        $stmt->execute([$_SESSION['user_id'], $slug, $destination, $label]);

        // Generate QR code
        require_once 'includes/phpqrcode/qrlib.php';
        $qrPath = 'assets/qr/' . $slug . '.png';
        $basePath = dirname($_SERVER['SCRIPT_NAME']); // e.g. /qr-tracker
        QRcode::png("https://" . $_SERVER['HTTP_HOST'] . $basePath . '/' . $slug, $qrPath, QR_ECLEVEL_H, 6);

        header('Location: dashboard.php');
        exit;
    }
}
?>

<?php include 'templates/header.php'; ?>

<div class="container py-5" style="max-width: 700px;">
    <h2 class="mb-4">Create a New QR Code</h2>

    <?php if ($error): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="post">
        <div class="mb-3">
            <label>Campaign Label (optional)</label>
            <input name="label" class="form-control" placeholder="e.g. Magazine Ad, Van Sign, Flyer">
        </div>
        <div class="mb-3">
            <label>Destination URL</label>
            <input name="destination_url" type="url" class="form-control" required>
        </div>
        <button class="btn btn-success">Create QR Code</button>
    </form>
</div>

<?php include 'templates/footer.php'; ?>
