<?php

declare(strict_types=1);

function showError(string $message, int $code = 500): never
{
    http_response_code($code);

    if (function_exists('logError')) {
        logError($message, [
            'code' => $code,
            'user' => $_SESSION['username'] ?? 'anonymous',
            'url' => $_SERVER['REQUEST_URI'] ?? 'unknown'
        ]);
    }

    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Error - Helpdesk</title>
        <link rel="stylesheet" href="style.css">
    </head>
    <body>
        <div class="error-container">
            <div class="error-code"><?= $code ?></div>
            <div class="error-message"><?= htmlspecialchars($message) ?></div>
            <div class="error-actions">
                <a href="index.php"><button>Go to Dashboard</button></a>
                <a href="javascript:history.back()"><button class="btn--secondary">Go Back</button></a>
            </div>
        </div>
    </body>
    </html>
    <?php
    exit();
}

function show404(string $resource = "Page"): never
{
    showError("$resource not found", 404);
}

function show403(string $message = "You don't have permission to access this resource"): never
{
    showError($message, 403);
}

function showDatabaseError(\PDOException $e): never
{
    if (function_exists('logDatabaseError')) {
        logDatabaseError("Database operation failed", $e->getMessage());
    }
    showError("A database error occurred. Please try again later.", 500);
}
