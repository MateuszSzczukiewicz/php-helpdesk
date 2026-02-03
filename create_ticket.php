<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$error_msg = "";

try {
    $stmt = $conn->query("SELECT * FROM categories ORDER BY name ASC");
    $categories = $stmt->fetchAll();
} catch (PDOException $e) {
    die("Error fetching categories: " . $e->getMessage());
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title']);
    $category_id = (int)$_POST['category_id'];
    $description = trim($_POST['description']);
    $user_id = $_SESSION['user_id'];

    if (empty($title) || empty($description) || empty($category_id)) {
        $error_msg = "Please fill in all fields.";
    } else {
        $sql = "INSERT INTO tickets (user_id, category_id, title, description, status) 
                VALUES (?, ?, ?, ?, 'new')";

        $stmt = $conn->prepare($sql);

        if ($stmt->execute([$user_id, $category_id, $title, $description])) {
            header("Location: index.php");
            exit();
        } else {
            $error_msg = "Database error. Could not create ticket.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>New Ticket - Helpdesk</title>
    <link rel="stylesheet" href="style.css">
</head>

<body>

    <nav>
        <div><strong>IT Helpdesk</strong></div>
        <div>
            <a href="index.php" style="color: white; margin-right: 15px;">Back to Dashboard</a>
            Logged in as: <strong><?php echo htmlspecialchars($_SESSION['username']); ?></strong>
        </div>
    </nav>

    <div class="container" style="max-width: 600px;">
        <h2>Submit a Ticket</h2>
        <p>Describe your issue clearly so our team can help you.</p>

        <?php if (!empty($error_msg)): ?>
            <div class="error"><?php echo $error_msg; ?></div>
        <?php endif; ?>

        <form action="create_ticket.php" method="POST">

            <label for="title">Subject / Title:</label>
            <input type="text" name="title" id="title" placeholder="e.g., Internet is not working" required>

            <label for="category_id">Category:</label>
            <select name="category_id" id="category_id" required>
                <option value="">-- Select Category --</option>
                <?php foreach ($categories as $cat): ?>
                    <option value="<?php echo $cat['id']; ?>">
                        <?php echo htmlspecialchars($cat['name']); ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <label for="description">Description:</label>
            <textarea name="description" id="description" rows="6" placeholder="Please provide details..." required></textarea>

            <div style="display: flex; gap: 10px; margin-top: 15px;">
                <button type="submit" style="flex-grow: 1;">Submit Ticket</button>
                <a href="index.php" style="text-decoration: none;">
                    <button type="button" style="background-color: #6c757d;">Cancel</button>
                </a>
            </div>
        </form>
    </div>

</body>

</html>