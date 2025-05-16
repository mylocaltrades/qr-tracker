<?php
session_start();
require_once 'includes/db.php';

if (!isset($_SESSION['user_id']) || !isset($_POST['id'])) {
    header('Location: login.php');
    exit;
}

$id = $_POST['id'];

// Confirm it belongs to this user
$stmt = $pdo->prepare("SELECT * FROM qr_links WHERE id = ? AND user_id = ?");
$stmt->execute([$id, $_SESSION['user_id']]);
$qr = $stmt->fetch();

if ($qr) {
    // Delete QR image
    $imgPath = 'assets/qr/' . $qr['slug'] . '.png';
    if (file_exists($imgPath)) {
        unlink($imgPath);
    }

    // Delete from DB
    $pdo->prepare("DELETE FROM qr_links WHERE id = ?")->execute([$id]);
}

header('Location: dashboard.php');
exit;
?>
