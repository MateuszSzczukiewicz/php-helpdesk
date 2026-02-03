<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$username = $_SESSION['username'];
$role = $_SESSION['role'];

$sql = "SELECT t.*, c.name as category_name 
        FROM tickets t 
        LEFT JOIN categories c ON t.category_id = c.id 
        WHERE t.user_id = ? 
        ORDER BY t.created_at DESC";

$stmt = $conn->prepare($sql);
$stmt->execute([$user_id]);
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
    <title>Dashboard - Helpdesk</title>
    <link rel="stylesheet" href="style.css">
</head>

<body>

    <nav>
        <div style="font-size: 1.2em;">
            <strong>IT Helpdesk</strong>
        </div>
        <div>
            Logged in as: <strong><?php echo htmlspecialchars($username); ?></strong>
            <span style="font-size:0.8em; opacity:0.8;">(<?php echo strtoupper($role); ?>)</span>
            |
            <a href="logout.php" style="color: #ff6b6b;">Logout</a>
        </div>
    </nav>

    <div class="container">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <h2>My Tickets</h2>
            <a href="create_ticket.php">
                <button>+ Create New Ticket</button>
            </a>
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
                            <td>#<?php echo $ticket['id']; ?></td>
                            <td><?php echo htmlspecialchars($ticket['title']); ?></td>
                            <td><?php echo htmlspecialchars($ticket['category_name']); ?></td>
                            <td><?php echo getStatusLabel($ticket['status']); ?></td>
                            <td><?php echo date("Y-m-d H:i", strtotime($ticket['created_at'])); ?></td>
                            <td>
                                <a href="view_ticket.php?id=<?php echo $ticket['id']; ?>" style="color: #007bff; text-decoration: none;">View Details</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <div style="text-align: center; padding: 40px; color: #666;">
                <p>No tickets found. You haven't reported any issues yet.</p>
            </div>
        <?php endif; ?>

        <?php if ($role === 'admin'): ?>
            <hr style="margin-top: 40px; border: 0; border-top: 1px solid #ddd;">
            <div style="text-align: center; margin-top: 20px;">
                <h3>Admin Panel</h3>
                <p>Manage all system tickets and users.</p>
                <a href="admin_panel.php">
                    <button style="background-color: #343a40;">Go to Admin Dashboard</button>
                </a>
            </div>
        <?php endif; ?>

    </div>

</body>

</html>