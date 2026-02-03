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
        <style>
            .error-container {
                max-width: 600px;
                margin: 100px auto;
                padding: 40px;
                text-align: center;
                background: white;
                border-radius: 8px;
                box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            }
            .error-code {
                font-size: 72px;
                font-weight: bold;
                color: #dc3545;
                margin-bottom: 20px;
            }
            .error-message {
                font-size: 20px;
                color: #333;
                margin-bottom: 30px;
            }
            .error-actions {
                display: flex;
                gap: 10px;
                justify-content: center;
            }
        </style>
    </head>
    <body>
        <div class="error-container">
            <div class="error-code"><?php echo $code; ?></div>
            <div class="error-message"><?php echo htmlspecialchars($message); ?></div>
            <div class="error-actions">
                <a href="index.php"><button>Go to Dashboard</button></a>
                <a href="javascript:history.back()"><button style="background-color: #6c757d;">Go Back</button></a>
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
