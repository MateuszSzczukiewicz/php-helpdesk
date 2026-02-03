<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    die("Access Denied: You do not have permission to perform this action.");
}

if (!isset($_GET['id'])) {
    die("Error: Ticket ID is missing.");
}

$ticket_id = (int)$_GET['id'];
$msg = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $new_status = $_POST['status'];

    $allowed_statuses = ['new', 'in_progress', 'resolved', 'cancelled'];

    if (in_array($new_status, $allowed_statuses)) {
        $stmt = $conn->prepare("UPDATE tickets SET status = ? WHERE id = ?");
        if ($stmt->execute([$new_status, $ticket_id])) {
            $msg = "<div class='success'>Status updated successfully!</div>";
        } else {
            $msg = "<div class='error'>Database error.</div>";
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
    die("Ticket not found.");
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Manage Ticket #<?php echo $ticket['id']; ?></title>
    <link rel="stylesheet" href="style.css">
</head>

<body>

    <nav style="background-color: #212529;">
        <div><strong>ADMIN PANEL</strong></div>
        <div>
            <a href="admin_panel.php" style="color: white; margin-right: 15px;">&larr; Back to List</a>
            Logged in as: <strong><?php echo htmlspecialchars($_SESSION['username']); ?></strong>
        </div>
    </nav>

    <div class="container" style="max-width: 800px;">

        <h2>Manage Ticket #<?php echo $ticket['id']; ?></h2>
        <?php echo $msg; ?>

        <div style="background-color: #e9ecef; padding: 20px; border-radius: 8px; margin-bottom: 30px; border: 1px solid #dee2e6;">
            <form method="POST" style="display: flex; align-items: center; gap: 15px; margin: 0;">
                <label style="margin: 0; font-weight: bold;">Change Status:</label>

                <select name="status" style="margin: 0; width: auto; flex-grow: 1;">
                    <option value="new" <?php if ($ticket['status'] == 'new') echo 'selected'; ?>>New</option>
                    <option value="in_progress" <?php if ($ticket['status'] == 'in_progress') echo 'selected'; ?>>In Progress</option>
                    <option value="resolved" <?php if ($ticket['status'] == 'resolved') echo 'selected'; ?>>Resolved</option>
                    <option value="cancelled" <?php if ($ticket['status'] == 'cancelled') echo 'selected'; ?>>Cancelled</option>
                </select>

                <button type="submit" style="background-color: #28a745; margin: 0;">Update Status</button>
            </form>
        </div>

        <h3>Ticket Details</h3>
        <table>
            <tr>
                <th style="width: 150px;">Author:</th>
                <td>
                    <?php echo htmlspecialchars($ticket['author_name']); ?>
                    <span style="color: gray; font-size: 0.9em;">(<?php echo htmlspecialchars($ticket['author_email']); ?>)</span>
                </td>
            </tr>
            <tr>
                <th>Category:</th>
                <td><?php echo htmlspecialchars($ticket['category_name']); ?></td>
            </tr>
            <tr>
                <th>Created:</th>
                <td><?php echo $ticket['created_at']; ?></td>
            </tr>
            <tr>
                <th>Subject:</th>
                <td><?php echo htmlspecialchars($ticket['title']); ?></td>
            </tr>
        </table>

        <div style="margin-top: 20px;">
            <strong>Full Description:</strong>
            <div style="background: #fff; border: 1px solid #ddd; padding: 15px; margin-top: 5px; border-radius: 4px;">
                <?php echo nl2br(htmlspecialchars($ticket['description'])); ?>
            </div>
        </div>

    </div>

</body>

</html>