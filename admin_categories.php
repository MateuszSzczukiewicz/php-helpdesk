<?php

declare(strict_types=1);

require 'db.php';
require 'includes/auth.php';
require 'includes/csrf.php';
require 'includes/logger.php';
require 'includes/error_handler.php';
require 'includes/security_headers.php';
require 'includes/session_manager.php';

setSecurityHeaders();
initSecureSession();

$user = requireAdmin();

$success_msg = '';
$error_msg = '';

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
    } elseif ($action === 'edit') {
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
    } elseif ($action === 'delete') {
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
</head>
<body>

<nav class="nav--admin">
    <div><strong>ADMIN PANEL</strong></div>
    <div>
        <a href="admin_panel.php" class="link--nav">&larr; Back to Tickets</a>
        Logged in as: <strong><?= htmlspecialchars($user['username']) ?></strong>
        |
        <a href="logout.php" class="nav__logout">Logout</a>
    </div>
</nav>

<div class="container container--full">
    <div class="flex--between mb-md">
        <h2>Manage Categories</h2>
        <button onclick="openAddModal()" class="btn--success">+ Add New Category</button>
    </div>

    <?php if (!empty($success_msg)): ?>
        <div class="success"><?= $success_msg ?></div>
    <?php endif; ?>

    <?php if (!empty($error_msg)): ?>
        <div class="error"><?= $error_msg ?></div>
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
                        <td>#<?= $cat['id'] ?></td>
                        <td><strong><?= htmlspecialchars($cat['name']) ?></strong></td>
                        <td><?= $cat['ticket_count'] ?></td>
                        <td>
                            <div class="action-buttons">
                                <button class="btn-edit" onclick="openEditModal(<?= $cat['id'] ?>, '<?= htmlspecialchars($cat['name'], ENT_QUOTES) ?>')">Edit</button>
                                <?php if ($cat['ticket_count'] == 0): ?>
                                    <button class="btn-delete" onclick="confirmDelete(<?= $cat['id'] ?>, '<?= htmlspecialchars($cat['name'], ENT_QUOTES) ?>')">Delete</button>
                                <?php else: ?>
                                    <button class="btn-delete" disabled title="Cannot delete - has tickets">Delete</button>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p class="text-center text-muted">No categories found.</p>
    <?php endif; ?>
</div>

<div id="addModal" class="modal">
    <div class="modal__content">
        <span class="modal__close" onclick="closeModal('addModal')">&times;</span>
        <h3>Add New Category</h3>
        <form method="POST">
            <?php csrfField(); ?>
            <input type="hidden" name="action" value="add">

            <label for="add_name">Category Name:</label>
            <input type="text" name="name" id="add_name" required minlength="3" maxlength="50" placeholder="e.g., Hardware Issues">
            <small class="form-hint">3-50 characters</small>

            <div class="modal__actions">
                <button type="submit" class="flex--grow">Add Category</button>
                <button type="button" onclick="closeModal('addModal')" class="btn--secondary">Cancel</button>
            </div>
        </form>
    </div>
</div>

<div id="editModal" class="modal">
    <div class="modal__content">
        <span class="modal__close" onclick="closeModal('editModal')">&times;</span>
        <h3>Edit Category</h3>
        <form method="POST">
            <?php csrfField(); ?>
            <input type="hidden" name="action" value="edit">
            <input type="hidden" name="id" id="edit_id">

            <label for="edit_name">Category Name:</label>
            <input type="text" name="name" id="edit_name" required minlength="3" maxlength="50">
            <small class="form-hint">3-50 characters</small>

            <div class="modal__actions">
                <button type="submit" class="flex--grow btn--warning">Update Category</button>
                <button type="button" onclick="closeModal('editModal')" class="btn--secondary">Cancel</button>
            </div>
        </form>
    </div>
</div>

<form id="deleteForm" method="POST" class="form--hidden">
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
    };

    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeModal('addModal');
            closeModal('editModal');
        }
    });
</script>

</body>
</html>
