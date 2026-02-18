<?php

declare(strict_types=1);

require 'db.php';
require 'includes/auth.php';
require 'includes/security_headers.php';
require 'includes/session_manager.php';

setSecurityHeaders();
initSecureSession();

$user = requireAdmin();

$sql = "SELECT t.*, c.name as category_name, u.username as author_name
        FROM tickets t
        LEFT JOIN categories c ON t.category_id = c.id
        LEFT JOIN users u ON t.user_id = u.id
        ORDER BY t.created_at DESC";

$stmt = $conn->query($sql);
$tickets = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Helpdesk</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<nav class="nav--admin">
    <div><strong>ADMIN PANEL</strong></div>
    <div>
        Logged in as: <strong><?= htmlspecialchars($user['username']) ?></strong>
        |
        <a href="index.php" class="nav__muted">User Dashboard</a>
        |
        <a href="logout.php" class="nav__logout">Logout</a>
    </div>
</nav>

<div class="container container--full">
    <div class="flex--between mb-md">
        <h2>All System Tickets</h2>
        <a href="admin_categories.php"><button class="btn--info">Manage Categories</button></a>
    </div>

    <?php if (count($tickets) > 0): ?>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Author</th>
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
                        <td><strong><?= htmlspecialchars($ticket['author_name']) ?></strong></td>
                        <td><?= htmlspecialchars($ticket['title']) ?></td>
                        <td><?= htmlspecialchars($ticket['category_name']) ?></td>
                        <td><?= getStatusLabel($ticket['status']) ?></td>
                        <td><?= formatDate($ticket['created_at']) ?></td>
                        <td><a href="admin_ticket.php?id=<?= $ticket['id'] ?>" class="link--action">Manage</a></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p class="text-center text-muted">No tickets in the system.</p>
    <?php endif; ?>
</div>

</body>
</html>
