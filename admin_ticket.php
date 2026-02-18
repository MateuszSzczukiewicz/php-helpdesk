<?php

declare(strict_types=1);

require 'db.php';
require 'includes/auth.php';
require 'includes/csrf.php';
require 'includes/logger.php';
require 'includes/error_handler.php';
require 'includes/security_headers.php';
require 'includes/session_manager.php';

setSecurityHeaders();
initSecureSession();

$user = requireAdmin();

if (!isset($_GET['id'])) {
    show404("Ticket");
}

$ticket_id = (int)$_GET['id'];
$success_msg = '';
$error_msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    requireCSRFToken();

    $new_status = $_POST['status'];
    $allowed_statuses = ['new', 'in_progress', 'resolved', 'cancelled'];

    if (in_array($new_status, $allowed_statuses)) {
        $stmt = $conn->prepare("UPDATE tickets SET status = ? WHERE id = ?");
        try {
            if ($stmt->execute([$new_status, $ticket_id])) {
                $success_msg = "Status updated successfully!";
                logInfo("Ticket status updated", [
                    'ticket_id' => $ticket_id,
                    'new_status' => $new_status,
                    'admin' => $user['username']
                ]);
            }
        } catch (PDOException $e) {
            $error_msg = "Database error.";
            logDatabaseError("UPDATE ticket status", $e->getMessage());
        }
    }
}

$sql = "SELECT t.*, c.name as category_name, u.username as author_name, u.email as author_email
        FROM tickets t
        LEFT JOIN categories c ON t.category_id = c.id
        LEFT JOIN users u ON t.user_id = u.id
        WHERE t.id = ?";

$stmt = $conn->prepare($sql);
$stmt->execute([$ticket_id]);
$ticket = $stmt->fetch();

if (!$ticket) {
    show404("Ticket");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Ticket #<?= $ticket['id'] ?></title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<nav class="nav--admin">
    <div><strong>ADMIN PANEL</strong></div>
    <div>
        <a href="admin_panel.php" class="link--nav">&larr; Back to List</a>
        Logged in as: <strong><?= htmlspecialchars($user['username']) ?></strong>
    </div>
</nav>

<div class="container container--wide">
    <h2>Manage Ticket #<?= $ticket['id'] ?></h2>

    <?php if (!empty($success_msg)): ?>
        <div class="success"><?= $success_msg ?></div>
    <?php endif; ?>

    <?php if (!empty($error_msg)): ?>
        <div class="error"><?= $error_msg ?></div>
    <?php endif; ?>

    <div class="status-form">
        <form method="POST">
            <?php csrfField(); ?>
            <label>Change Status:</label>
            <select name="status">
                <?php foreach (['new' => 'New', 'in_progress' => 'In Progress', 'resolved' => 'Resolved', 'cancelled' => 'Cancelled'] as $value => $label): ?>
                    <option value="<?= $value ?>" <?= $ticket['status'] === $value ? 'selected' : '' ?>><?= $label ?></option>
                <?php endforeach; ?>
            </select>
            <button type="submit" class="btn--success">Update Status</button>
        </form>
    </div>

    <h3>Ticket Details</h3>
    <table>
        <tr>
            <th class="col-label">Author:</th>
            <td>
                <?= htmlspecialchars($ticket['author_name']) ?>
                <span class="author-email">(<?= htmlspecialchars($ticket['author_email']) ?>)</span>
            </td>
        </tr>
        <tr>
            <th>Category:</th>
            <td><?= htmlspecialchars($ticket['category_name']) ?></td>
        </tr>
        <tr>
            <th>Created:</th>
            <td><?= $ticket['created_at'] ?></td>
        </tr>
        <tr>
            <th>Subject:</th>
            <td><?= htmlspecialchars($ticket['title']) ?></td>
        </tr>
    </table>

    <div class="mt-md">
        <strong>Full Description:</strong>
        <div class="description-box">
            <?= nl2br(htmlspecialchars($ticket['description'])) ?>
        </div>
    </div>
</div>

</body>
</html>
