<?php
session_start();
require_once 'includes/db.php';

if (!isset($_SESSION['user_id']) || !isset($_GET['id'])) {
    header('Location: login.php');
    exit;
}

$id = $_GET['id'];

// Fetch the QR code and ensure it belongs to the user
$stmt = $pdo->prepare("SELECT * FROM qr_links WHERE id = ? AND user_id = ?");
$stmt->execute([$id, $_SESSION['user_id']]);
$qr = $stmt->fetch();

if (!$qr) {
    die("QR code not found or access denied.");
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $label = trim($_POST['label'] ?? '');
    $destination = trim($_POST['destination_url'] ?? '');

    if (!filter_var($destination, FILTER_VALIDATE_URL)) {
        $error = "Invalid URL format.";
    } else {
        $update = $pdo->prepare("UPDATE qr_links SET destination_url = ?, label = ? WHERE id = ? AND user_id = ?");
        $update->execute([$destination, $label, $id, $_SESSION['user_id']]);
        header("Location: dashboard.php");
        exit;
    }
}
?>

<?php include 'templates/header.php'; ?>

<div class="container py-5" style="max-width: 700px;">
    <h2 class="mb-4">Edit QR Code</h2>

    <?php if ($error): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="post">
        <div class="mb-3">
            <label>Campaign Label (optional)</label>
            <input name="label" class="form-control" value="<?= htmlspecialchars($qr['label']) ?>">
        </div>
        <div class="mb-3">
            <label>Destination URL</label>
            <input name="destination_url" type="url" class="form-control" required value="<?= htmlspecialchars($qr['destination_url']) ?>">
        </div>
        <button class="btn btn-success">Save Changes</button>
        <a href="dashboard.php" class="btn btn-secondary ms-2">Cancel</a>
    </form>
</div>

<?php include 'templates/footer.php'; ?>