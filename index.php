<?php

declare(strict_types=1);

require 'db.php';
require 'includes/auth.php';
require 'includes/security_headers.php';
require 'includes/session_manager.php';

setSecurityHeaders();
initSecureSession();

$user = requireAuth();

$sql = "SELECT t.*, c.name as category_name
        FROM tickets t
        LEFT JOIN categories c ON t.category_id = c.id
        WHERE t.user_id = ?
        ORDER BY t.created_at DESC";

$stmt = $conn->prepare($sql);
$stmt->execute([$user['id']]);
$tickets = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Helpdesk</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<nav>
    <div class="greeting"><strong>IT Helpdesk</strong></div>
    <div>
        Logged in as: <strong><?= htmlspecialchars($user['username']) ?></strong>
        <span class="role-badge">(<?= strtoupper($user['role']) ?>)</span>
        |
        <a href="logout.php" class="nav__logout">Logout</a>
    </div>
</nav>

<div class="container">
    <div class="flex--between mb-md">
        <h2>My Tickets</h2>
        <a href="create_ticket.php"><button>+ Create New Ticket</button></a>
    </div>

    <?php if (count($tickets) > 0): ?>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Subject</th>
                    <th>Category</th>
                    <th>Status</th>
                    <th>Date</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($tickets as $ticket): ?>
                    <tr>
                        <td>#<?= $ticket['id'] ?></td>
                        <td><?= htmlspecialchars($ticket['title']) ?></td>
                        <td><?= htmlspecialchars($ticket['category_name']) ?></td>
                        <td><?= getStatusLabel($ticket['status']) ?></td>
                        <td><?= formatDate($ticket['created_at']) ?></td>
                        <td><a href="view_ticket.php?id=<?= $ticket['id'] ?>" class="link--action">View Details</a></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <div class="text-center mt-xl text-muted">
            <p>No tickets found. You haven't reported any issues yet.</p>
        </div>
    <?php endif; ?>

    <?php if (isAdmin()): ?>
        <hr>
        <div class="text-center mt-md">
            <h3>Admin Panel</h3>
            <p>Manage all system tickets and users.</p>
            <a href="admin_panel.php"><button class="btn--dark">Go to Admin Dashboard</button></a>
        </div>
    <?php endif; ?>
</div>

</body>
</html>
