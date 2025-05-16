<?php
session_start();
require_once 'includes/db.php';

if (!isset($_SESSION['user_id']) || !isset($_GET['id'])) {
    exit('Access denied.');
}

$qr_id = (int) $_GET['id'];

// Get QR code info (verify it belongs to this user)
$check = $pdo->prepare("SELECT * FROM qr_links WHERE id = ? AND user_id = ?");
$check->execute([$qr_id, $_SESSION['user_id']]);
$qr = $check->fetch();

if (!$qr) {
    exit('QR code not found or access denied.');
}

$logs = $pdo->prepare("SELECT * FROM click_logs WHERE qr_link_id = ? ORDER BY timestamp DESC");
$logs->execute([$qr_id]);

header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="clicks_' . $qr['slug'] . '.csv"');

$output = fopen('php://output', 'w');
fputcsv($output, ['Date', 'IP Address', 'Device']);

foreach ($logs as $log) {
    $ua = strtolower($log['user_agent']);
    if (strpos($ua, 'mobile') !== false) {
        $device = 'Mobile';
    } elseif (strpos($ua, 'tablet') !== false) {
        $device = 'Tablet';
    } else {
        $device = 'Desktop';
    }

    fputcsv($output, [
        $log['timestamp'],
        $log['ip_address'],
        $device
    ]);
}

fclose($output);
exit;
