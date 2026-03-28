<?php
/**
 * ADMIN EDIT DEMO - Safe showcase
 * Features: Edit user, edit branch, edit category
 * No real database, hardcoded demo data
 */

// Simulate session and user role
session_start();
$_SESSION['user_id'] = 1;
$_SESSION['role'] = 'admin';

// Dummy database connection (replace with real DB in your app)
function getDb() {
    $db = new SQLite3(':memory:');
    $db->exec("CREATE TABLE users (id INTEGER, username TEXT, full_name TEXT, email TEXT, role TEXT, branch TEXT)");
    $db->exec("CREATE TABLE branches (id INTEGER, name TEXT, code TEXT, location TEXT)");
    $db->exec("CREATE TABLE categories (id INTEGER, name TEXT, type TEXT, color TEXT, icon TEXT)");
    // Insert demo data
    $db->exec("INSERT INTO users VALUES (1, 'admin', 'Admin User', 'admin@demo.com', 'admin', 'Main')");
    $db->exec("INSERT INTO users VALUES (2, 'manager', 'Branch Manager', 'manager@demo.com', 'manager', 'Langano')");
    $db->exec("INSERT INTO branches VALUES (1, 'Main Branch', 'MBN', 'Headquarters')");
    $db->exec("INSERT INTO branches VALUES (2, 'Langano', 'LGN', 'Resort Area')");
    $db->exec("INSERT INTO categories VALUES (1, 'Room Rental', 'income', '#10B981', 'fa-bed')");
    $db->exec("INSERT INTO categories VALUES (2, 'Salaries', 'expense', '#F59E0B', 'fa-user-tie')");
    return $db;
}
$db = getDb();

// POST handlers for editing
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];
    try {
        switch ($action) {
            case 'edit_user':
                $id = intval($_POST['user_id']);
                $full_name = trim($_POST['full_name']);
                $role = $_POST['role'];
                $branch = $_POST['branch'];
                if ($id <= 0 || empty($full_name)) throw new Exception("Invalid data");
                $stmt = $db->prepare("UPDATE users SET full_name = :name, role = :role, branch = :branch WHERE id = :id");
                $stmt->bindValue(':name', $full_name);
                $stmt->bindValue(':role', $role);
                $stmt->bindValue(':branch', $branch);
                $stmt->bindValue(':id', $id);
                $stmt->execute();
                echo "User updated successfully";
                break;
            case 'edit_branch':
                $id = intval($_POST['branch_id']);
                $name = trim($_POST['name']);
                $code = trim($_POST['code']);
                $location = trim($_POST['location']);
                if ($id <= 0 || empty($name) || empty($code)) throw new Exception("Invalid data");
                $stmt = $db->prepare("UPDATE branches SET name = :name, code = :code, location = :location WHERE id = :id");
                $stmt->bindValue(':name', $name);
                $stmt->bindValue(':code', $code);
                $stmt->bindValue(':location', $location);
                $stmt->bindValue(':id', $id);
                $stmt->execute();
                echo "Branch updated successfully";
                break;
            case 'edit_category':
                $id = intval($_POST['category_id']);
                $name = trim($_POST['name']);
                $type = $_POST['type'];
                $color = $_POST['color'];
                $icon = $_POST['icon'];
                if ($id <= 0 || empty($name)) throw new Exception("Invalid data");
                $stmt = $db->prepare("UPDATE categories SET name = :name, type = :type, color = :color, icon = :icon WHERE id = :id");
                $stmt->bindValue(':name', $name);
                $stmt->bindValue(':type', $type);
                $stmt->bindValue(':color', $color);
                $stmt->bindValue(':icon', $icon);
                $stmt->bindValue(':id', $id);
                $stmt->execute();
                echo "Category updated successfully";
                break;
            default: echo "Unknown action";
        }
    } catch (Exception $e) {
        echo "Error: " . $e->getMessage();
    }
    exit();
}

// AJAX endpoint to get category details (for editing)
if (isset($_GET['action']) && $_GET['action'] === 'get_category' && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $stmt = $db->prepare("SELECT * FROM categories WHERE id = :id");
    $stmt->bindValue(':id', $id);
    $result = $stmt->execute();
    $category = $result->fetchArray(SQLITE3_ASSOC);
    if ($category) {
        header('Content-Type: application/json');
        echo json_encode($category);
        exit();
    }
    http_response_code(404);
    echo json_encode(['error' => 'Not found']);
    exit();
}

// HTML frontend (simplified)
?>
<!DOCTYPE html>
<html>
<head>
    <title>Admin Edit Demo</title>
    <style>
        body { font-family: sans-serif; background: #f4f4f4; margin: 20px; }
        table { border-collapse: collapse; width: 100%; background: white; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        button { cursor: pointer; }
        .modal { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); justify-content: center; align-items: center; }
        .modal-content { background: white; padding: 20px; border-radius: 8px; width: 400px; }
    </style>
</head>
<body>
<h1>Admin Panel Demo</h1>
<h2>Users</h2>
<table id="users-table">
    <thead><tr><th>ID</th><th>Full Name</th><th>Role</th><th>Branch</th><th>Action</th></tr></thead>
    <tbody>
        <?php
        $result = $db->query("SELECT * FROM users");
        while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
            echo "<tr>";
            echo "<td>{$row['id']}</td>";
            echo "<td>{$row['full_name']}</td>";
            echo "<td>{$row['role']}</td>";
            echo "<td>{$row['branch']}</td>";
            echo "<td><button onclick='editUser({$row['id']}, \"".htmlspecialchars($row['full_name'])."\", \"{$row['role']}\", \"{$row['branch']}\")'>Edit</button></td>";
            echo "</tr>";
        }
        ?>
    </tbody>
</table>

<h2>Branches</h2>
<table id="branches-table">
    <thead><tr><th>ID</th><th>Name</th><th>Code</th><th>Location</th><th>Action</th></tr></thead>
    <tbody>
        <?php
        $result = $db->query("SELECT * FROM branches");
        while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
            echo "<tr>";
            echo "<td>{$row['id']}</td>";
            echo "<td>{$row['name']}</td>";
            echo "<td>{$row['code']}</td>";
            echo "<td>{$row['location']}</td>";
            echo "<td><button onclick='editBranch({$row['id']}, \"".htmlspecialchars($row['name'])."\", \"".htmlspecialchars($row['code'])."\", \"".htmlspecialchars($row['location'])."\")'>Edit</button></td>";
            echo "</tr>";
        }
        ?>
    </tbody>
</table>

<h2>Categories</h2>
<table id="categories-table">
    <thead><tr><th>ID</th><th>Name</th><th>Type</th><th>Color</th><th>Icon</th><th>Action</th></tr></thead>
    <tbody>
        <?php
        $result = $db->query("SELECT * FROM categories");
        while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
            echo "<tr>";
            echo "<td>{$row['id']}</td>";
            echo "<td>{$row['name']}</td>";
            echo "<td>{$row['type']}</td>";
            echo "<td>{$row['color']}</td>";
            echo "<td>{$row['icon']}</td>";
            echo "<td><button onclick='editCategory({$row['id']})'>Edit</button></td>";
            echo "</tr>";
        }
        ?>
    </tbody>
</table>

<!-- Modals -->
<div id="editUserModal" class="modal">
    <div class="modal-content">
        <h3>Edit User</h3>
        <form method="POST" id="editUserForm">
            <input type="hidden" name="action" value="edit_user">
            <input type="hidden" name="user_id" id="editUserId">
            <label>Full Name: <input type="text" name="full_name" id="editFullName" required></label><br>
            <label>Role: 
                <select name="role" id="editRole">
                    <option value="admin">Admin</option>
                    <option value="manager">Manager</option>
                    <option value="cashier1">Cashier</option>
                </select>
            </label><br>
            <label>Branch: <input type="text" name="branch" id="editBranch"></label><br>
            <button type="submit">Save</button>
            <button type="button" onclick="closeModal('editUserModal')">Cancel</button>
        </form>
    </div>
</div>
<div id="editBranchModal" class="modal">
    <div class="modal-content">
        <h3>Edit Branch</h3>
        <form method="POST" id="editBranchForm">
            <input type="hidden" name="action" value="edit_branch">
            <input type="hidden" name="branch_id" id="editBranchId">
            <label>Name: <input type="text" name="name" id="editBranchName" required></label><br>
            <label>Code: <input type="text" name="code" id="editBranchCode" required></label><br>
            <label>Location: <input type="text" name="location" id="editBranchLocation"></label><br>
            <button type="submit">Save</button>
            <button type="button" onclick="closeModal('editBranchModal')">Cancel</button>
        </form>
    </div>
</div>
<div id="editCategoryModal" class="modal">
    <div class="modal-content">
        <h3>Edit Category</h3>
        <form method="POST" id="editCategoryForm">
            <input type="hidden" name="action" value="edit_category">
            <input type="hidden" name="category_id" id="editCategoryId">
            <label>Name: <input type="text" name="name" id="editCategoryName" required></label><br>
            <label>Type:
                <select name="type" id="editCategoryType">
                    <option value="income">Income</option>
                    <option value="expense">Expense</option>
                </select>
            </label><br>
            <label>Color: <input type="color" name="color" id="editCategoryColor"></label><br>
            <label>Icon: <input type="text" name="icon" id="editCategoryIcon" placeholder="fa-bed"></label><br>
            <button type="submit">Save</button>
            <button type="button" onclick="closeModal('editCategoryModal')">Cancel</button>
        </form>
    </div>
</div>

<script>
function editUser(id, fullName, role, branch) {
    document.getElementById('editUserId').value = id;
    document.getElementById('editFullName').value = fullName;
    document.getElementById('editRole').value = role;
    document.getElementById('editBranch').value = branch;
    showModal('editUserModal');
}
function editBranch(id, name, code, location) {
    document.getElementById('editBranchId').value = id;
    document.getElementById('editBranchName').value = name;
    document.getElementById('editBranchCode').value = code;
    document.getElementById('editBranchLocation').value = location;
    showModal('editBranchModal');
}
function editCategory(id) {
    fetch(`?action=get_category&id=${id}`)
        .then(response => response.json())
        .then(data => {
            document.getElementById('editCategoryId').value = data.id;
            document.getElementById('editCategoryName').value = data.name;
            document.getElementById('editCategoryType').value = data.type;
            document.getElementById('editCategoryColor').value = data.color;
            document.getElementById('editCategoryIcon').value = data.icon;
            showModal('editCategoryModal');
        })
        .catch(error => alert('Could not load category details'));
}
function showModal(modalId) {
    document.getElementById(modalId).style.display = 'flex';
}
function closeModal(modalId) {
    document.getElementById(modalId).style.display = 'none';
}
window.onclick = function(event) {
    if (event.target.classList.contains('modal')) {
        event.target.style.display = 'none';
    }
}
</script>
</body>
</html>