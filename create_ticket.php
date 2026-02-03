<?php
require 'db.php';
require 'includes/auth.php';
require 'includes/functions.php';
require 'includes/csrf.php';
require 'includes/validation.php';
require 'includes/logger.php';
require 'includes/error_handler.php';
require 'includes/security_headers.php';
require 'includes/session_manager.php';

setSecurityHeaders();
initSecureSession();

$user = requireAuth();

$error_msg = "";

try {
    $stmt = $conn->query("SELECT * FROM categories ORDER BY name ASC");
    $categories = $stmt->fetchAll();
} catch (PDOException $e) {
    logDatabaseError("SELECT categories", $e->getMessage());
    die("Error fetching categories: " . $e->getMessage());
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    requireCSRFToken();
    
    $title = trim($_POST['title']);
    $category_id = (int)$_POST['category_id'];
    $description = trim($_POST['description']);

    if (empty($title) || empty($description) || empty($category_id)) {
        $error_msg = "Please fill in all fields.";
    } else {
        $titleValidation = validateTicketTitle($title);
        if (!$titleValidation->isValid()) {
            $error_msg = $titleValidation->getErrorMessage();
        }
        else {
            $descValidation = validateTicketDescription($description);
            if (!$descValidation->isValid()) {
                $error_msg = $descValidation->getErrorMessage();
            } else {
                $sql = "INSERT INTO tickets (user_id, category_id, title, description, status) 
                        VALUES (?, ?, ?, ?, 'new')";

                $stmt = $conn->prepare($sql);

                try {
                    if ($stmt->execute([$user['id'], $category_id, $title, $description])) {
                        logInfo("Ticket created", [
                            'user_id' => $user['id'],
                            'title' => $title,
                            'category_id' => $category_id
                        ]);
                        redirect('index.php');
                    }
                } catch (PDOException $e) {
                    $error_msg = "Database error. Could not create ticket.";
                    logDatabaseError($sql, $e->getMessage());
                }
            }
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
            Logged in as: <strong><?php echo htmlspecialchars($user['username']); ?></strong>
        </div>
    </nav>

    <div class="container" style="max-width: 600px;">
        <h2>Submit a Ticket</h2>
        <p>Describe your issue clearly so our team can help you.</p>

        <?php if (!empty($error_msg)): ?>
            <div class="error"><?php echo $error_msg; ?></div>
        <?php endif; ?>

        <form action="create_ticket.php" method="POST">
            <?php csrfField(); ?>

            <label for="title">Subject / Title:</label>
            <input type="text" name="title" id="title" placeholder="e.g., Internet is not working" required minlength="5" maxlength="100">
            <small style="color: #666; font-size: 0.85em;">5-100 characters</small>

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
            <textarea name="description" id="description" rows="6" placeholder="Please provide details..." required minlength="10" maxlength="5000"></textarea>
            <small style="color: #666; font-size: 0.85em;">10-5000 characters</small>

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