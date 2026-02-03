<?php

declare(strict_types=1);
require 'db.php';
require 'includes/auth.php';
require 'includes/functions.php';
require 'includes/csrf.php';
require 'includes/logger.php';
require 'includes/error_handler.php';
require 'includes/security_headers.php';
require 'includes/session_manager.php';

setSecurityHeaders();
initSecureSession();

$user = requireAdmin();

$success_msg = "";
$error_msg = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    requireCSRFToken();
    
    $action = $_POST['action'] ?? '';
    
    if ($action === 'add') {
        $name = trim($_POST['name']);
        
        if (empty($name)) {
            $error_msg = "Category name is required";
        } elseif (strlen($name) < 3 || strlen($name) > 50) {
            $error_msg = "Category name must be between 3 and 50 characters";
        } else {
            $stmt = $conn->prepare("SELECT id FROM categories WHERE name = ?");
            $stmt->execute([$name]);
            
            if ($stmt->rowCount() > 0) {
                $error_msg = "Category already exists";
            } else {
                $stmt = $conn->prepare("INSERT INTO categories (name) VALUES (?)");
                try {
                    if ($stmt->execute([$name])) {
                        $success_msg = "Category added successfully";
                        logInfo("Category added", ['name' => $name, 'admin' => $user['username']]);
                    }
                } catch (PDOException $e) {
                    $error_msg = "Database error";
                    logDatabaseError("Insert category", $e->getMessage());
                }
            }
        }
    }
    
    elseif ($action === 'edit') {
        $id = (int)$_POST['id'];
        $name = trim($_POST['name']);
        
        if (empty($name)) {
            $error_msg = "Category name is required";
        } elseif (strlen($name) < 3 || strlen($name) > 50) {
            $error_msg = "Category name must be between 3 and 50 characters";
        } else {
            $stmt = $conn->prepare("SELECT id FROM categories WHERE name = ? AND id != ?");
            $stmt->execute([$name, $id]);
            
            if ($stmt->rowCount() > 0) {
                $error_msg = "Category with this name already exists";
            } else {
                $stmt = $conn->prepare("UPDATE categories SET name = ? WHERE id = ?");
                try {
                    if ($stmt->execute([$name, $id])) {
                        $success_msg = "Category updated successfully";
                        logInfo("Category updated", ['id' => $id, 'name' => $name, 'admin' => $user['username']]);
                    }
                } catch (PDOException $e) {
                    $error_msg = "Database error";
                    logDatabaseError("Update category", $e->getMessage());
                }
            }
        }
    }
    
    elseif ($action === 'delete') {
        $id = (int)$_POST['id'];
        
        $stmt = $conn->prepare("SELECT COUNT(*) as count FROM tickets WHERE category_id = ?");
        $stmt->execute([$id]);
        $result = $stmt->fetch();
        
        if ($result['count'] > 0) {
            $error_msg = "Cannot delete category - it has associated tickets";
        } else {
            $stmt = $conn->prepare("DELETE FROM categories WHERE id = ?");
            try {
                if ($stmt->execute([$id])) {
                    $success_msg = "Category deleted successfully";
                    logInfo("Category deleted", ['id' => $id, 'admin' => $user['username']]);
                }
            } catch (PDOException $e) {
                $error_msg = "Database error";
                logDatabaseError("Delete category", $e->getMessage());
            }
        }
    }
}

$stmt = $conn->query("SELECT c.*, COUNT(t.id) as ticket_count 
                      FROM categories c 
                      LEFT JOIN tickets t ON c.id = t.category_id 
                      GROUP BY c.id 
                      ORDER BY c.name ASC");
$categories = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Categories - Admin</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
        }
        
        .modal-content {
            background-color: white;
            margin: 10% auto;
            padding: 30px;
            border-radius: 8px;
            width: 90%;
            max-width: 500px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        
        .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
        }
        
        .close:hover {
            color: #000;
        }
        
        .action-buttons {
            display: flex;
            gap: 10px;
        }
        
        .btn-edit {
            background-color: #ffc107;
            color: white;
            border: none;
            padding: 5px 15px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
        }
        
        .btn-delete {
            background-color: #dc3545;
            color: white;
            border: none;
            padding: 5px 15px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
        }
        
        .btn-edit:hover {
            background-color: #e0a800;
        }
        
        .btn-delete:hover {
            background-color: #c82333;
        }
    </style>
</head>

<body>

    <nav style="background-color: #212529;">
        <div><strong>ADMIN PANEL</strong></div>
        <div>
            <a href="admin_panel.php" style="color: white; margin-right: 15px;">← Back to Tickets</a>
            Logged in as: <strong><?php echo htmlspecialchars($user['username']); ?></strong>
            |
            <a href="logout.php" style="color: #ff6b6b;">Logout</a>
        </div>
    </nav>

    <div class="container" style="max-width: 900px;">
        
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <h2>Manage Categories</h2>
            <button onclick="openAddModal()" style="background-color: #28a745;">+ Add New Category</button>
        </div>

        <?php if (!empty($success_msg)): ?>
            <div class="success"><?php echo $success_msg; ?></div>
        <?php endif; ?>

        <?php if (!empty($error_msg)): ?>
            <div class="error"><?php echo $error_msg; ?></div>
        <?php endif; ?>

        <?php if (count($categories) > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Category Name</th>
                        <th>Tickets Count</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($categories as $cat): ?>
                        <tr>
                            <td>#<?php echo $cat['id']; ?></td>
                            <td><strong><?php echo htmlspecialchars($cat['name']); ?></strong></td>
                            <td><?php echo $cat['ticket_count']; ?></td>
                            <td>
                                <div class="action-buttons">
                                    <button class="btn-edit" onclick="openEditModal(<?php echo $cat['id']; ?>, '<?php echo htmlspecialchars($cat['name'], ENT_QUOTES); ?>')">Edit</button>
                                    <?php if ($cat['ticket_count'] == 0): ?>
                                        <button class="btn-delete" onclick="confirmDelete(<?php echo $cat['id']; ?>, '<?php echo htmlspecialchars($cat['name'], ENT_QUOTES); ?>')">Delete</button>
                                    <?php else: ?>
                                        <button class="btn-delete" disabled title="Cannot delete - has tickets" style="opacity: 0.5; cursor: not-allowed;">Delete</button>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p style="text-align: center; color: #666; padding: 40px;">No categories found.</p>
        <?php endif; ?>

    </div>

    <div id="addModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal('addModal')">&times;</span>
            <h3>Add New Category</h3>
            <form method="POST" action="">
                <?php csrfField(); ?>
                <input type="hidden" name="action" value="add">
                
                <label for="add_name">Category Name:</label>
                <input type="text" name="name" id="add_name" required minlength="3" maxlength="50" placeholder="e.g., Hardware Issues">
                <small style="color: #666; font-size: 0.85em;">3-50 characters</small>
                
                <div style="display: flex; gap: 10px; margin-top: 20px;">
                    <button type="submit" style="flex-grow: 1;">Add Category</button>
                    <button type="button" onclick="closeModal('addModal')" style="background-color: #6c757d;">Cancel</button>
                </div>
            </form>
        </div>
    </div>

    <div id="editModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal('editModal')">&times;</span>
            <h3>Edit Category</h3>
            <form method="POST" action="">
                <?php csrfField(); ?>
                <input type="hidden" name="action" value="edit">
                <input type="hidden" name="id" id="edit_id">
                
                <label for="edit_name">Category Name:</label>
                <input type="text" name="name" id="edit_name" required minlength="3" maxlength="50">
                <small style="color: #666; font-size: 0.85em;">3-50 characters</small>
                
                <div style="display: flex; gap: 10px; margin-top: 20px;">
                    <button type="submit" style="flex-grow: 1; background-color: #ffc107;">Update Category</button>
                    <button type="button" onclick="closeModal('editModal')" style="background-color: #6c757d;">Cancel</button>
                </div>
            </form>
        </div>
    </div>

    <form id="deleteForm" method="POST" action="" style="display: none;">
        <?php csrfField(); ?>
        <input type="hidden" name="action" value="delete">
        <input type="hidden" name="id" id="delete_id">
    </form>

    <script>
        function openAddModal() {
            document.getElementById('addModal').style.display = 'block';
            document.getElementById('add_name').focus();
        }

        function openEditModal(id, name) {
            document.getElementById('edit_id').value = id;
            document.getElementById('edit_name').value = name;
            document.getElementById('editModal').style.display = 'block';
            document.getElementById('edit_name').focus();
        }

        function closeModal(modalId) {
            document.getElementById(modalId).style.display = 'none';
        }

        function confirmDelete(id, name) {
            if (confirm('Are you sure you want to delete category "' + name + '"?')) {
                document.getElementById('delete_id').value = id;
                document.getElementById('deleteForm').submit();
            }
        }

        window.onclick = function(event) {
            if (event.target.classList.contains('modal')) {
                event.target.style.display = 'none';
            }
        }

        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeModal('addModal');
                closeModal('editModal');
            }
        });
    </script>

</body>

</html>
