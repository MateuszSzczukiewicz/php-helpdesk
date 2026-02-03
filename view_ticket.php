<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if (!isset($_GET['id'])) {
    die("Error: Ticket ID is missing.");
}

$ticket_id = (int)$_GET['id'];
$current_user_id = $_SESSION['user_id'];
$current_role = $_SESSION['role'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'cancel') {
    $updateStmt = $conn->prepare("UPDATE tickets SET status = 'cancelled' WHERE id = ? AND user_id = ?");
    $updateStmt->execute([$ticket_id, $current_user_id]);

    header("Location: view_ticket.php?id=" . $ticket_id);
    exit();
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
    die("Error: Ticket not found.");
}

if ($ticket['user_id'] != $current_user_id && $current_role !== 'admin') {
    die("Access Denied: You do not have permission to view this ticket.");
}

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
    <title>Ticket #<?php echo $ticket['id']; ?> - Details</title>
    <link rel="stylesheet" href="style.css">
</head>

<body>

    <nav>
        <div><strong>IT Helpdesk</strong></div>
        <div>
            <a href="index.php" style="color: white; margin-right: 15px;">&larr; Back to Dashboard</a>
            Logged in as: <strong><?php echo htmlspecialchars($_SESSION['username']); ?></strong>
        </div>
    </nav>

    <div class="container" style="max-width: 700px;">

        <div style="border-bottom: 1px solid #ddd; padding-bottom: 10px; margin-bottom: 20px;">
            <span style="font-size: 0.9em; color: #666;">Ticket #<?php echo $ticket['id']; ?></span>
            <h1 style="margin: 5px 0;"><?php echo htmlspecialchars($ticket['title']); ?></h1>
        </div>

        <table style="margin-bottom: 20px;">
            <tr>
                <th style="width: 150px;">Status:</th>
                <td><?php echo getStatusLabel($ticket['status']); ?></td>
            </tr>
            <tr>
                <th>Category:</th>
                <td><?php echo htmlspecialchars($ticket['category_name']); ?></td>
            </tr>
            <tr>
                <th>Created Date:</th>
                <td><?php echo $ticket['created_at']; ?></td>
            </tr>
            <tr>
                <th>Author:</th>
                <td><?php echo htmlspecialchars($ticket['username']); ?></td>
            </tr>
        </table>

        <div style="background: #f8f9fa; padding: 15px; border-radius: 5px; border: 1px solid #ddd;">
            <strong>Description:</strong>
            <p style="white-space: pre-wrap; margin-top: 10px;"><?php echo htmlspecialchars($ticket['description']); ?></p>
        </div>

        <?php if ($ticket['user_id'] == $current_user_id && $ticket['status'] == 'new'): ?>
            <div style="margin-top: 30px; text-align: right;">
                <form action="view_ticket.php?id=<?php echo $ticket['id']; ?>" method="POST" onsubmit="return confirm('Are you sure you want to cancel this ticket?');">
                    <input type="hidden" name="action" value="cancel">
                    <button type="submit" style="background-color: #dc3545;">Cancel Ticket</button>
                </form>
            </div>
        <?php endif; ?>

    </div>

</body>

</html>