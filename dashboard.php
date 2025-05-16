<?php
session_start();
require_once 'includes/db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$filter = $_GET['filter'] ?? 'all';
$click_filter_sql = '';

if ($filter === '7days') {
    $click_filter_sql = "AND timestamp >= DATE_SUB(NOW(), INTERVAL 7 DAY)";
} elseif ($filter === '30days') {
    $click_filter_sql = "AND timestamp >= DATE_SUB(NOW(), INTERVAL 30 DAY)";
}

// Get user's QR codes
$stmt = $pdo->prepare("SELECT * FROM qr_links WHERE user_id = ? ORDER BY created_at DESC");
$stmt->execute([$_SESSION['user_id']]);
$qr_codes = $stmt->fetchAll();

// Total clicks this month
$monthStart = date('Y-m-01 00:00:00');
$summaryStmt = $pdo->prepare("SELECT COUNT(*) as total_clicks FROM click_logs WHERE qr_link_id IN (SELECT id FROM qr_links WHERE user_id = ?) AND timestamp >= ?");
$summaryStmt->execute([$_SESSION['user_id'], $monthStart]);
$summary = $summaryStmt->fetch();

// Top QR this month
$topStmt = $pdo->prepare("SELECT qr_links.slug, qr_links.label, COUNT(*) as click_total FROM click_logs JOIN qr_links ON click_logs.qr_link_id = qr_links.id WHERE qr_links.user_id = ? AND click_logs.timestamp >= ? GROUP BY qr_links.id ORDER BY click_total DESC LIMIT 1");
$topStmt->execute([$_SESSION['user_id'], $monthStart]);
$top = $topStmt->fetch();

// Device breakdown
$deviceStats = ['Mobile' => 0, 'Tablet' => 0, 'Desktop' => 0];
$deviceStmt = $pdo->prepare("SELECT user_agent FROM click_logs WHERE qr_link_id IN (SELECT id FROM qr_links WHERE user_id = ?)");
$deviceStmt->execute([$_SESSION['user_id']]);
foreach ($deviceStmt->fetchAll() as $row) {
    $ua = strtolower($row['user_agent']);
    if (strpos($ua, 'mobile') !== false) {
        $deviceStats['Mobile']++;
    } elseif (strpos($ua, 'tablet') !== false) {
        $deviceStats['Tablet']++;
    } else {
        $deviceStats['Desktop']++;
    }
}
?>
<?php include 'templates/header.php'; ?>

<div class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Hello <?= htmlspecialchars($_SESSION['user_name']) ?> 👋</h2>
        <a href="logout.php" class="btn btn-sm btn-outline-danger">Logout</a>
    </div>

    <div class="alert alert-info">
        📂 <strong>Total Clicks This Month:</strong> <?= $summary['total_clicks'] ?> <br>
        🏅 <strong>Top Performer:</strong> <?= $top ? ($top['label'] ? $top['label'] . ' – ' : '') . $top['slug'] . " ({$top['click_total']} clicks)" : 'No data yet' ?>
    </div>

    <form method="get" class="mb-4">
        <label class="form-label me-2">🗓️ Filter Click Logs:</label>
        <select name="filter" onchange="this.form.submit()" class="form-select w-auto d-inline-block">
            <option value="all" <?= $filter === 'all' ? 'selected' : '' ?>>All Time</option>
            <option value="7days" <?= $filter === '7days' ? 'selected' : '' ?>>Last 7 Days</option>
            <option value="30days" <?= $filter === '30days' ? 'selected' : '' ?>>Last 30 Days</option>
        </select>
    </form>

    <div class="mb-4" style="max-width: 400px; margin: 0 auto;">
        <canvas id="deviceChart" height="200"></canvas>
    </div>

    <a href="create.php" class="btn btn-primary mb-4">➕ Create New QR Code</a>

    <?php if (count($qr_codes) === 0): ?>
        <p>You haven’t created any QR codes yet. Click above to get started!</p>
    <?php else: ?>
        <div class="table-responsive">
        <table class="table table-bordered table-striped align-middle">
            <thead>
                <tr>
                    <th>QR Code</th>
                    <th>Short Link</th>
                    <th>Destination URL</th>
                    <th>Clicks</th>
                    <th>Created</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($qr_codes as $qr): ?>
                <tr>
                    <td><img src="assets/qr/<?= htmlspecialchars($qr['slug']) ?>.png" width="80"></td>
                    <?php $shortLink = dirname($_SERVER['SCRIPT_NAME']) . '/' . $qr['slug']; ?>
                    <td><a href="<?= $shortLink ?>" target="_blank"><?= $_SERVER['HTTP_HOST'] . $shortLink ?></a></td>
                    <td><a href="<?= htmlspecialchars($qr['destination_url']) ?>" target="_blank"><?= htmlspecialchars($qr['destination_url']) ?></a></td>
                    <td><?= $qr['click_count'] ?></td>
                    <td><?= date("d M Y", strtotime($qr['created_at'])) ?></td>
                    <td>
                       <a href="assets/qr/<?= $qr['slug'] ?>.png" download>Download</a><br>
                       <a href="edit.php?id=<?= $qr['id'] ?>" class="btn btn-sm btn-outline-secondary mt-2">Edit</a><br>
                       <!-- Proper Delete Form -->
                       <button type="button"
        class="btn btn-sm btn-outline-danger mt-2"
        onclick="openDeleteModal(<?= $qr['id'] ?>)">
    Delete
</button>
                       <button class="btn btn-sm btn-outline-primary mt-2" onclick="toggleClicks('<?= $qr['id'] ?>')">View Clicks</button>
                    </td>
                </tr>

                <tr id="clicks-<?= $qr['id'] ?>" style="display: none; background: #f8f9fa;">
                    <td colspan="6">
                        <?php
                        $logStmt = $pdo->prepare("SELECT * FROM click_logs WHERE qr_link_id = ? $click_filter_sql ORDER BY timestamp DESC LIMIT 50");
                        $logStmt->execute([$qr['id']]);
                        $clicks = $logStmt->fetchAll();

                        $chartStmt = $pdo->prepare("SELECT DATE(timestamp) as day, COUNT(*) as count FROM click_logs WHERE qr_link_id = ? $click_filter_sql GROUP BY day ORDER BY day ASC");
                        $chartStmt->execute([$qr['id']]);
                        $chartData = $chartStmt->fetchAll();
                        $labels = array_column($chartData, 'day');
                        $values = array_column($chartData, 'count');
                        ?>

                        <div class="mb-3">
                            <strong>Last <?= count($clicks) ?> Clicks:</strong>
                            <div class="table-responsive">
                                <table class="table table-sm table-bordered mt-2">
                                    <thead>
                                        <tr>
                                            <th>Date</th>
                                            <th>IP Address</th>
                                            <th>Device</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($clicks as $click): ?>
                                            <tr>
                                                <td><?= date("d M Y H:i", strtotime($click['timestamp'])) ?></td>
                                                <td><?= htmlspecialchars($click['ip_address']) ?></td>
                                                <td>
                                                    <?php
                                                    $ua = strtolower($click['user_agent']);
                                                    if (strpos($ua, 'mobile') !== false) {
                                                        echo '📱 Mobile';
                                                    } elseif (strpos($ua, 'tablet') !== false) {
                                                        echo '📲 Tablet';
                                                    } else {
                                                        echo '💻 Desktop';
                                                    }
                                                    ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <div style="max-width: 600px; margin-top: 1rem;">
                          <canvas id="chart-<?= $qr['id'] ?>" height="150"></canvas>
                        </div>
                        <script>
new Chart(document.getElementById('chart-<?= $qr['id'] ?>'), {
    type: 'line',
    data: {
        labels: <?= json_encode($labels) ?>,
        datasets: [{
            label: 'Clicks Per Day',
            data: <?= json_encode($values) ?>,
            borderColor: '#0d6efd',
            backgroundColor: 'rgba(13, 110, 253, 0.1)',
            pointRadius: 5,
            pointHoverRadius: 7,
            tension: 0.3,
            fill: true
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: { display: false }
        },
        scales: {
            x: {
                title: {
                    display: true,
                    text: 'Date'
                }
            },
            y: {
                beginAtZero: true,
                precision: 0,
                title: {
                    display: true,
                    text: 'Clicks'
                }
            }
        }
    }
});
</script>
                        <a href="export_clicks.php?id=<?= $qr['id'] ?>&filter=<?= $filter ?>" class="btn btn-sm btn-outline-secondary mt-3">Export CSV</a>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        </div>
    <?php endif; ?>
</div>

<script>
function toggleClicks(id) {
    const row = document.getElementById('clicks-' + id);
    row.style.display = row.style.display === 'none' ? 'table-row' : 'none';
}

new Chart(document.getElementById('deviceChart'), {
    type: 'pie',
    data: {
        labels: ['Mobile', 'Tablet', 'Desktop'],
        datasets: [{
            label: 'Device Types',
            data: [<?= $deviceStats['Mobile'] ?>, <?= $deviceStats['Tablet'] ?>, <?= $deviceStats['Desktop'] ?>],
            backgroundColor: ['#0d6efd', '#6f42c1', '#198754']
        }]
    }
});
</script>

<script>
function openDeleteModal(qrId) {
  document.getElementById('deleteQrId').value = qrId;
  const modal = new bootstrap.Modal(document.getElementById('confirmDeleteModal'));
  modal.show();
}
</script>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="confirmDeleteModal" tabindex="-1" aria-labelledby="confirmDeleteLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content shadow">
      <div class="modal-header bg-danger text-white">
        <h5 class="modal-title" id="confirmDeleteLabel">Are you sure?</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        This QR code will be permanently deleted. Are you sure you want to continue?
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <form method="post" action="delete.php">
          <input type="hidden" name="id" id="deleteQrId">
          <button type="submit" class="btn btn-danger">Yes, Delete</button>
        </form>
      </div>
    </div>
  </div>
</div>

<?php include 'templates/footer.php'; ?>