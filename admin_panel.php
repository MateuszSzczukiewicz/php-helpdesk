<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if ($_SESSION['role'] !== 'admin') {
    die("Access Denied: You do not have permission to view this page.");
}

$sql = "SELECT t.*, c.name as category_name, u.username as author_name
        FROM tickets t 
        LEFT JOIN categories c ON t.category_id = c.id 
        LEFT JOIN users u ON t.user_id = u.id
        ORDER BY t.created_at DESC";

$stmt = $conn->query($sql);
$tickets = $stmt->fetchAll();

function getStatusLabel($status)
{
    switch ($status) {
        case 'new':
            return '<span class="status-new">New</span>';
        case 'in_progress':
            return '<span class="status-in_progress">In Progress</span>';
        case 'resolved':
            return '<span class="status-resolved">Resolved</span>';
        case 'cancelled':
            return '<span style="color:gray">Cancelled</span>';
        default:
            return $status;
    }
}
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

    <nav style="background-color: #212529;">
        <div><strong>ADMIN PANEL</strong></div>
        <div>
            Logged in as: <strong><?php echo htmlspecialchars($_SESSION['username']); ?></strong>
            |
            <a href="index.php" style="color: #adb5bd;">User Dashboard</a>
            |
            <a href="logout.php" style="color: #ff6b6b;">Logout</a>
        </div>
    </nav>

    <div class="container" style="max-width: 1000px;">

        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <h2>All System Tickets</h2>
            <a href="admin_categories.php">
                <button style="background-color: #17a2b8;">Manage Categories</button>
            </a>
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
                            <td>#<?php echo $ticket['id']; ?></td>
                            <td><strong><?php echo htmlspecialchars($ticket['author_name']); ?></strong></td>
                            <td><?php echo htmlspecialchars($ticket['title']); ?></td>
                            <td><?php echo htmlspecialchars($ticket['category_name']); ?></td>
                            <td><?php echo getStatusLabel($ticket['status']); ?></td>
                            <td><?php echo date("Y-m-d H:i", strtotime($ticket['created_at'])); ?></td>
                            <td>
                                <a href="admin_ticket.php?id=<?php echo $ticket['id']; ?>" style="color: #007bff; text-decoration: none;">Manage</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p style="text-align: center; color: #666;">No tickets in the system.</p>
        <?php endif; ?>

    </div>

</body>

</html>