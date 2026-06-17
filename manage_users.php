<?php
// manage_users.php
session_start();
require_once __DIR__ . '/db.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

try {
    // Retrieve all users ordered by creation date using MySQLi
    $result = $conn->query("SELECT * FROM users ORDER BY created_at DESC");
    $users = [];
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $users[] = $row;
        }
    }
} catch (Exception $e) {
    die("Error retrieving users: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Users - User Management Dashboard</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container" style="max-width: 1100px;">
        <div class="page-header" style="border-bottom: 1px solid var(--border-color); padding-bottom: 1.5rem; margin-bottom: 1.5rem;">
            <div>
                <h1>Manage Users</h1>
                <p class="subtitle" style="margin-bottom: 0;">Logged in as: <strong style="color: var(--text-primary);"><?php echo htmlspecialchars($_SESSION['user_name']); ?></strong> (<?php echo htmlspecialchars($_SESSION['user_role']); ?>)</p>
            </div>
            <div style="display: flex; gap: 0.75rem;">
                <a href="register.php" class="btn btn-primary">Add New User</a>
                <a href="logout.php" class="btn btn-secondary">Sign Out</a>
            </div>
        </div>

        <?php if (isset($_GET['deleted']) && $_GET['deleted'] == 1): ?>
            <div class="alert alert-success">
                <strong>Success!</strong> User has been deleted successfully.
            </div>
        <?php endif; ?>

        <?php if (empty($users)): ?>
            <div class="empty-state">
                <p>No users found in the database. Click "Add New User" to get started.</p>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="user-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Gender</th>
                            <th>DOB</th>
                            <th>Address</th>
                            <th>Role</th>
                            <th>Bio</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $user): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($user['id']); ?></td>
                                <td style="font-weight: 500; color: #ffffff;"><?php echo htmlspecialchars($user['name']); ?></td>
                                <td><?php echo htmlspecialchars($user['email']); ?></td>
                                <td><?php echo htmlspecialchars($user['phone'] ?: '-'); ?></td>
                                <td>
                                    <?php if (!empty($user['gender'])): ?>
                                        <span class="badge badge-gender"><?php echo htmlspecialchars($user['gender']); ?></span>
                                    <?php else: ?>
                                        -
                                    <?php endif; ?>
                                </td>
                                <td><?php echo htmlspecialchars($user['dob'] ?: '-'); ?></td>
                                <td><?php echo htmlspecialchars(mb_strimwidth($user['address'] ?? '', 0, 30, '...')); ?></td>
                                <td>
                                    <?php 
                                        $role = strtolower($user['role'] ?? 'user');
                                        $badge_class = 'badge-role-' . ($role ?: 'user');
                                    ?>
                                    <span class="badge <?php echo $badge_class; ?>"><?php echo htmlspecialchars($role); ?></span>
                                </td>
                                <td><?php echo htmlspecialchars(mb_strimwidth($user['bio'] ?? '', 0, 30, '...')); ?></td>
                                <td class="action-links">
                                    <a href="edit_user.php?id=<?php echo $user['id']; ?>" class="btn btn-secondary btn-sm">Edit</a>
                                    <a href="delete_user.php?id=<?php echo $user['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this user?');">Delete</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
