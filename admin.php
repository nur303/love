<?php
/**
 * Admin Dashboard
 * View and manage recorded responses
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db.php';

// Handle Logout
if (isset($_GET['action']) && $_GET['action'] === 'logout') {
    unset($_SESSION['admin_logged_in']);
    session_destroy();
    header('Location: admin.php');
    exit;
}

// Handle Login
$loginError = '';
if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST' && isset($_POST['password'])) {
    if ($_POST['password'] === ADMIN_PASSWORD) {
        $_SESSION['admin_logged_in'] = true;
        header('Location: admin.php');
        exit;
    } else {
        $loginError = 'Invalid password! Please try again.';
    }
}

// Check if logged in
$isLoggedIn = !empty($_SESSION['admin_logged_in']);

// If not logged in, show Login Screen
if (!$isLoggedIn): ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - Love Response Dashboard</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
            background: linear-gradient(135deg, #ffc568, #ff8c8c);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .login-card {
            background: #ffffff;
            border-radius: 16px;
            padding: 40px 30px;
            width: 100%;
            max-width: 400px;
            box-shadow: 0 10px 30px rgba(118, 60, 140, 0.2);
            text-align: center;
        }
        .login-card h1 {
            color: #763c8c;
            font-size: 1.8rem;
            margin-bottom: 10px;
        }
        .login-card p {
            color: #666;
            font-size: 0.95rem;
            margin-bottom: 25px;
        }
        .form-group {
            margin-bottom: 20px;
            text-align: left;
        }
        .form-group label {
            display: block;
            font-size: 0.85rem;
            color: #444;
            font-weight: 600;
            margin-bottom: 6px;
        }
        .form-group input {
            width: 100%;
            padding: 12px 16px;
            border: 2px solid #eedbf5;
            border-radius: 8px;
            font-size: 1rem;
            outline: none;
            transition: border-color 0.2s;
        }
        .form-group input:focus {
            border-color: #763c8c;
        }
        .btn-submit {
            width: 100%;
            padding: 12px;
            background: #763c8c;
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: bold;
            cursor: pointer;
            transition: background 0.2s, transform 0.1s;
        }
        .btn-submit:hover {
            background: #5f2f72;
            transform: translateY(-1px);
        }
        .error-msg {
            color: #e74c3c;
            background: #fdf2f2;
            padding: 10px;
            border-radius: 6px;
            margin-bottom: 20px;
            font-size: 0.9rem;
        }
        .hint {
            margin-top: 15px;
            font-size: 0.8rem;
            color: #888;
        }
    </style>
</head>
<body>
    <div class="login-card">
        <div style="font-size: 3rem; margin-bottom: 10px;">🔐</div>
        <h1>Response Dashboard</h1>
        <p>Enter the admin password to view submissions</p>

        <?php if ($loginError): ?>
            <div class="error-msg"><?= htmlspecialchars($loginError) ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-group">
                <label for="password">Admin Password</label>
                <input type="password" id="password" name="password" required autofocus placeholder="Default: love123">
            </div>
            <button type="submit" class="btn-submit">Sign In</button>
        </form>
        <p class="hint">Configured in <code>config.php</code></p>
    </div>
</body>
</html>
<?php
exit;
endif;

// --- Logged in: Process Admin Actions & Display Dashboard ---
$pdo = getDB();

// Handle Delete Single Action
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $delId = intval($_GET['id']);
    $delStmt = $pdo->prepare("DELETE FROM responses WHERE id = :id");
    $delStmt->execute([':id' => $delId]);
    header('Location: admin.php');
    exit;
}

// Handle Clear All Action
if (isset($_GET['action']) && $_GET['action'] === 'clear_all') {
    $pdo->exec("DELETE FROM responses");
    header('Location: admin.php');
    exit;
}

// Handle CSV Export
if (isset($_GET['export']) && $_GET['export'] === 'csv') {
    header('Content-Type: text/csv; charset=UTF-8');
    header('Content-Disposition: attachment; filename="love_responses_' . date('Y-m-d_H-i-s') . '.csv"');
    $out = fopen('php://output', 'w');
    fputcsv($out, ['ID', 'Choice', 'Message', 'Visitor IP', 'User Agent', 'Date']);
    $exportStmt = $pdo->query("SELECT * FROM responses ORDER BY id DESC");
    while ($row = $exportStmt->fetch(PDO::FETCH_ASSOC)) {
        fputcsv($out, [$row['id'], $row['choice'], $row['message'], $row['visitor_ip'], $row['user_agent'], $row['created_at']]);
    }
    fclose($out);
    exit;
}

// Query Stats
$totalCount = (int) $pdo->query("SELECT COUNT(*) FROM responses")->fetchColumn();
$yesCount = (int) $pdo->query("SELECT COUNT(*) FROM responses WHERE choice = 'yes'")->fetchColumn();
$noCount = (int) $pdo->query("SELECT COUNT(*) FROM responses WHERE choice = 'no'")->fetchColumn();
$yesRate = $totalCount > 0 ? round(($yesCount / $totalCount) * 100, 1) : 0;

// Fetch Responses
$stmt = $pdo->query("SELECT * FROM responses ORDER BY created_at DESC");
$responses = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Responses Dashboard - Nur Muhammad Susmoy</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
            background: #f7f5f9;
            color: #333;
            padding: 24px;
        }
        .container {
            max-width: 1100px;
            margin: 0 auto;
        }
        header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: #763c8c;
            color: white;
            padding: 20px 28px;
            border-radius: 14px;
            margin-bottom: 24px;
            box-shadow: 0 4px 15px rgba(118, 60, 140, 0.15);
        }
        header h1 {
            font-size: 1.6rem;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .header-actions {
            display: flex;
            gap: 10px;
            align-items: center;
        }
        .btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 8px 16px;
            border-radius: 8px;
            text-decoration: none;
            font-size: 0.9rem;
            font-weight: 600;
            transition: all 0.2s;
            cursor: pointer;
            border: none;
        }
        .btn-view {
            background: #ffc568;
            color: #552766;
        }
        .btn-view:hover { background: #ffb53f; }
        .btn-export {
            background: rgba(255,255,255,0.2);
            color: white;
        }
        .btn-export:hover { background: rgba(255,255,255,0.3); }
        .btn-logout {
            background: #e74c3c;
            color: white;
        }
        .btn-logout:hover { background: #c0392b; }
        .btn-danger {
            background: #fdf2f2;
            color: #e74c3c;
            border: 1px solid #fabebb;
        }
        .btn-danger:hover { background: #fde6e6; }

        /* Stats Grid */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 18px;
            margin-bottom: 24px;
        }
        .stat-card {
            background: white;
            padding: 20px;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.04);
            border-left: 5px solid #763c8c;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .stat-card.yes { border-left-color: #2ecc71; }
        .stat-card.no { border-left-color: #e74c3c; }
        .stat-card.rate { border-left-color: #f39c12; }
        .stat-info h3 {
            font-size: 0.85rem;
            color: #777;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 6px;
        }
        .stat-info .value {
            font-size: 2rem;
            font-weight: bold;
            color: #222;
        }
        .stat-icon {
            font-size: 2.2rem;
            opacity: 0.85;
        }

        /* Table Card */
        .card {
            background: white;
            border-radius: 14px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.04);
            overflow: hidden;
        }
        .card-header {
            padding: 18px 24px;
            border-bottom: 1px solid #f0edf3;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .card-header h2 {
            font-size: 1.2rem;
            color: #333;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            text-align: left;
        }
        th {
            background: #faf8fc;
            color: #555;
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            padding: 14px 20px;
            border-bottom: 1px solid #eee;
        }
        td {
            padding: 14px 20px;
            border-bottom: 1px solid #f5f3f7;
            font-size: 0.92rem;
            vertical-align: middle;
        }
        tr:hover {
            background: #fbf9fd;
        }
        .badge {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
        }
        .badge-yes {
            background: #e8f8f0;
            color: #27ae60;
        }
        .badge-no {
            background: #fdedec;
            color: #c0392b;
        }
        .message-cell {
            max-width: 320px;
            word-wrap: break-word;
            font-style: italic;
            color: #444;
        }
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #888;
        }
        .empty-state p {
            margin-top: 10px;
            font-size: 1.1rem;
        }
        .footer-credit {
            margin-top: 24px;
            text-align: center;
            font-size: 0.85rem;
            color: #999;
        }
    </style>
</head>
<body>
    <div class="container">
        <header>
            <h1><span>💖</span> Proposal Responses Dashboard</h1>
            <div class="header-actions">
                <a href="index.php" target="_blank" class="btn btn-view">👁️ View Animation</a>
                <a href="admin.php?export=csv" class="btn btn-export">📥 Export CSV</a>
                <a href="admin.php?action=logout" class="btn btn-logout">🚪 Logout</a>
            </div>
        </header>

        <!-- Stats Grid -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-info">
                    <h3>Total Clicks</h3>
                    <div class="value"><?= $totalCount ?></div>
                </div>
                <div class="stat-icon">📊</div>
            </div>
            <div class="stat-card yes">
                <div class="stat-info">
                    <h3>Said YES!</h3>
                    <div class="value"><?= $yesCount ?></div>
                </div>
                <div class="stat-icon">❤️</div>
            </div>
            <div class="stat-card no">
                <div class="stat-info">
                    <h3>Said NO</h3>
                    <div class="value"><?= $noCount ?></div>
                </div>
                <div class="stat-icon">💔</div>
            </div>
            <div class="stat-card rate">
                <div class="stat-info">
                    <h3>Acceptance Rate</h3>
                    <div class="value"><?= $yesRate ?>%</div>
                </div>
                <div class="stat-icon">💍</div>
            </div>
        </div>

        <!-- Table Card -->
        <div class="card">
            <div class="card-header">
                <h2>Recent Submissions (<?= count($responses) ?>)</h2>
                <?php if ($totalCount > 0): ?>
                    <a href="admin.php?action=clear_all" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete ALL responses?');">🗑️ Clear All</a>
                <?php endif; ?>
            </div>

            <?php if (empty($responses)): ?>
                <div class="empty-state">
                    <div style="font-size: 3rem;">💌</div>
                    <p>No responses recorded yet. When someone clicks "Yes" or "No", their answer will appear here!</p>
                </div>
            <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Choice</th>
                            <th>Partner Note / Message</th>
                            <th>Visitor IP</th>
                            <th>Date & Time</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($responses as $row): ?>
                            <tr>
                                <td><strong>#<?= htmlspecialchars($row['id']) ?></strong></td>
                                <td>
                                    <?php if ($row['choice'] === 'yes'): ?>
                                        <span class="badge badge-yes">❤️ YES</span>
                                    <?php else: ?>
                                        <span class="badge badge-no">💔 NO</span>
                                    <?php endif; ?>
                                </td>
                                <td class="message-cell">
                                    <?= !empty($row['message']) ? htmlspecialchars($row['message']) : '<span style="color:#aaa;">(No note attached)</span>' ?>
                                </td>
                                <td><code><?= htmlspecialchars($row['visitor_ip'] ?? 'Unknown') ?></code></td>
                                <td><?= htmlspecialchars($row['created_at']) ?></td>
                                <td>
                                    <a href="admin.php?action=delete&id=<?= $row['id'] ?>" class="btn btn-danger" style="padding: 4px 10px; font-size: 0.8rem;" onclick="return confirm('Delete this response?');">Delete</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>

        <div class="footer-credit">
            Created with ❤️ for Nur Muhammad Susmoy
        </div>
    </div>
</body>
</html>
