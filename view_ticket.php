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

$user = requireAuth();

if (!isset($_GET['id'])) {
    show404("Ticket");
}

$ticket_id = (int)$_GET['id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'cancel') {
    requireCSRFToken();

    $updateStmt = $conn->prepare("UPDATE tickets SET status = 'cancelled' WHERE id = ? AND user_id = ?");
    try {
        $updateStmt->execute([$ticket_id, $user['id']]);
        logInfo("Ticket cancelled by user", ['ticket_id' => $ticket_id, 'user_id' => $user['id']]);
    } catch (PDOException $e) {
        logDatabaseError("Cancel ticket", $e->getMessage());
    }

    redirect("view_ticket.php?id=" . $ticket_id);
}

$sql = "SELECT t.*, c.name as category_name, u.username
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

if ($ticket['user_id'] != $user['id'] && !isAdmin()) {
    show403("You do not have permission to view this ticket");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ticket #<?= $ticket['id'] ?> - Details</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<nav>
    <div><strong>IT Helpdesk</strong></div>
    <div>
        <a href="index.php" class="link--nav">&larr; Back to Dashboard</a>
        Logged in as: <strong><?= htmlspecialchars($user['username']) ?></strong>
    </div>
</nav>

<div class="container container--wide">
    <div class="ticket-header">
        <span class="ticket-id">Ticket #<?= $ticket['id'] ?></span>
        <h1><?= htmlspecialchars($ticket['title']) ?></h1>
    </div>

    <table class="mb-md">
        <tr>
            <th class="col-label">Status:</th>
            <td><?= getStatusLabel($ticket['status']) ?></td>
        </tr>
        <tr>
            <th>Category:</th>
            <td><?= htmlspecialchars($ticket['category_name']) ?></td>
        </tr>
        <tr>
            <th>Created Date:</th>
            <td><?= $ticket['created_at'] ?></td>
        </tr>
        <tr>
            <th>Author:</th>
            <td><?= htmlspecialchars($ticket['username']) ?></td>
        </tr>
    </table>

    <div class="ticket-description">
        <strong>Description:</strong>
        <p><?= htmlspecialchars($ticket['description']) ?></p>
    </div>

    <?php if ($ticket['user_id'] == $user['id'] && $ticket['status'] == 'new'): ?>
        <div class="flex--right mt-lg">
            <form action="view_ticket.php?id=<?= $ticket['id'] ?>" method="POST" onsubmit="return confirm('Are you sure you want to cancel this ticket?');">
                <?php csrfField(); ?>
                <input type="hidden" name="action" value="cancel">
                <button type="submit" class="btn--danger">Cancel Ticket</button>
            </form>
        </div>
    <?php endif; ?>
</div>

</body>
</html>
