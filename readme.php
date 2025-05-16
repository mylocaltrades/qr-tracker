<?php
session_start();
require_once 'includes/db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}
?>

<?php include 'templates/header.php'; ?>

<div class="container py-5">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="mb-0">Hello <?= htmlspecialchars($_SESSION['user_name']) ?> 👋</h2>
  </div>

  <h3 class="fw-bold mb-3">QR Tracker – Self-Hosted QR Code Generator & Tracker</h3>
  <p>This is a fully self-hosted QR code tracking tool built by <a href="https://www.my-local-trades.co.uk/" target="_blank">Gary Pratten</a> for tradespeople, business owners, and ToolBox members. No subscriptions, no third-party platforms, no limits — <strong>you own it, you host it</strong>.</p>

  <h4 class="mt-5">💡 What It Does</h4>
  <ul class="list-group mb-4">
    <li class="list-group-item">✅ Create <strong>unlimited QR codes</strong></li>
    <li class="list-group-item">✅ Each with its own <strong>short link</strong></li>
    <li class="list-group-item">✅ Track <strong>unlimited scans</strong> (IP, timestamp, device type)</li>
    <li class="list-group-item">✅ Filter scans by <strong>last 7 days</strong>, <strong>30 days</strong>, or <strong>all time</strong></li>
    <li class="list-group-item">✅ View analytics in a clean, easy-to-use dashboard</li>
    <li class="list-group-item">✅ Export scan data to <strong>CSV</strong></li>
    <li class="list-group-item">✅ Edit or delete QR codes anytime</li>
    <li class="list-group-item">✅ Everything runs from your own server/subdirectory (e.g. <code>yourwebsite.com/qr-tracker</code>)</li>
    <li class="list-group-item">✅ Fully private – <strong>no data shared externally</strong></li>
  </ul>

  <h4>📸 Screenshot</h4>
  <img src="/qr-tracker/screenshot.png" alt="Dashboard Screenshot" class="img-fluid rounded shadow mb-4">

  <h4>⚙️ Installation</h4>
  <ol class="list-group list-group-numbered mb-4">
    <li class="list-group-item">Upload the files to your server in a subdirectory (<code>/qr-tracker</code> or similar).</li>
    <li class="list-group-item">Visit <code>yourdomain.com/qr-tracker/install.php</code>.</li>
    <li class="list-group-item">Fill out your database connection details and admin email/password.</li>
    <li class="list-group-item">Hit "Install" and you're ready to go! 🎉</li>
    <li class="list-group-item">Visit <code>login.php</code> to log in and start creating QR codes.</li>
  </ol>
  <p>📄 Full setup walkthrough: <code>instructions.php</code> included in the repo.</p>

  <h4>🧠 Why I Built This</h4>
  <p>Most QR tracking platforms (like Hovercode, Beaconstac etc.) charge £15+ per month and limit how many QR codes, scans, or features you can use.</p>
  <p>That’s no good for tradespeople and local business owners who just want something <strong>simple, useful, and private</strong> — so I built this.</p>
  <p><strong>No branding. No limits. No fees.</strong> Just a great tool you can actually own.</p>

  <h4>📁 Folder Structure</h4>
  <pre class="bg-light p-3 rounded">
qr-tracker/
├── assets/
│   ├── qr/                  # Generated QR code images
│   ├── css/                 # Custom styles (optional)
│   └── js/                  # Custom scripts (optional)
├── includes/
│   ├── db.php               # Database connection file
│   └── phpqrcode/           # PHP QR code library
├── templates/
│   ├── header.php
│   └── footer.php
├── create.php
├── dashboard.php
├── delete.php
├── edit.php
├── export_clicks.php
├── index.php
├── install.php
├── instructions.php
├── login.php
├── logout.php
└── config.php               # (Auto-created during install)
  </pre>

  <h4>📜 License</h4>
  <p>This project is licensed under the <strong>MIT License</strong> – see the <code>LICENSE</code> file for details.<br>
  Use it freely, modify it, share it – just don’t sell it without asking 😉</p>

  <h4>🤝 Support</h4>
  <p>Questions or need help getting it set up?<br>
  Send me a message or visit <a href="https://www.my-local-trades.co.uk/" target="_blank">my-local-trades.co.uk</a><br>
  I’m always happy to help fellow ToolBox members and small business owners 💪</p>
</div>


<?php include 'templates/footer.php'; ?>