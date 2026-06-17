<?php
// edit_user.php
session_start();
require_once __DIR__ . '/db.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$id = $_GET['id'] ?? null;
if (!$id) {
    header("Location: manage_users.php");
    exit;
}

$message = '';
$error = '';
$user = null;

// Fetch user data using MySQLi
try {
    $stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
    if ($stmt) {
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
        $stmt->close();
        
        if (!$user) {
            header("Location: manage_users.php");
            exit;
        }
    } else {
        die("Database preparation failed.");
    }
} catch (Exception $e) {
    die("Database error: " . $e->getMessage());
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $gender = trim($_POST['gender'] ?? '');
    $bio = trim($_POST['bio'] ?? '');
    $dob = trim($_POST['dob'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $role = trim($_POST['role'] ?? 'user');

    // Server-side validation
    if (empty($name) || empty($email)) {
        $error = 'Name and Email are required fields.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Invalid email format.';
    } else {
        try {
            // Check if email already exists for another user
            $stmt = $conn->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
            if ($stmt) {
                $stmt->bind_param("si", $email, $id);
                $stmt->execute();
                $stmt->store_result();
                
                if ($stmt->num_rows > 0) {
                    $error = 'Email is already registered by another user.';
                    $stmt->close();
                } else {
                    $stmt->close();
                    
                    // Update user details using MySQLi
                    $stmt = $conn->prepare("UPDATE users SET name = ?, email = ?, phone = ?, gender = ?, bio = ?, dob = ?, address = ?, role = ? WHERE id = ?");
                    if ($stmt) {
                        $dob_val = !empty($dob) ? $dob : null;
                        $stmt->bind_param("ssssssssi", $name, $email, $phone, $gender, $bio, $dob_val, $address, $role, $id);
                        
                        if ($stmt->execute()) {
                            $message = 'User profile updated successfully!';
                            // Fetch fresh details
                            $stmt->close();
                            $stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
                            $stmt->bind_param("i", $id);
                            $stmt->execute();
                            $result = $stmt->get_result();
                            $user = $result->fetch_assoc();
                            $stmt->close();
                        } else {
                            $error = 'Failed to update user profile: ' . $stmt->error;
                            $stmt->close();
                        }
                    } else {
                        $error = 'Database update preparation failed.';
                    }
                }
            } else {
                $error = 'Database check query preparation failed.';
            }
        } catch (Exception $e) {
            $error = 'Database error: ' . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit User - Dashboard</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <div class="page-header">
            <div>
                <h1>Edit User Info</h1>
                <p class="subtitle">Update user profile information for ID: <?php echo htmlspecialchars($user['id']); ?></p>
            </div>
            <a href="manage_users.php" class="btn btn-secondary">Back to Dashboard</a>
        </div>

        <?php if (!empty($message)): ?>
            <div class="alert alert-success">
                <strong>Success!</strong> <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($error)): ?>
            <div class="alert alert-danger">
                <strong>Error!</strong> <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <form action="edit_user.php?id=<?php echo $user['id']; ?>" method="POST" class="user-form">
            <div class="form-row">
                <div class="form-group">
                    <label for="name">Full Name *</label>
                    <input type="text" id="name" name="name" class="form-control" value="<?php echo htmlspecialchars($user['name']); ?>" required>
                </div>

                <div class="form-group">
                    <label for="email">Email Address *</label>
                    <input type="email" id="email" name="email" class="form-control" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="phone">Phone Number</label>
                    <input type="tel" id="phone" name="phone" class="form-control" value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>">
                </div>

                <div class="form-group">
                    <label>Gender</label>
                    <div class="radio-group">
                        <label class="radio-label">
                            <input type="radio" name="gender" value="male" <?php echo ($user['gender'] === 'male') ? 'checked' : ''; ?>> Male
                        </label>
                        <label class="radio-label">
                            <input type="radio" name="gender" value="female" <?php echo ($user['gender'] === 'female') ? 'checked' : ''; ?>> Female
                        </label>
                        <label class="radio-label">
                            <input type="radio" name="gender" value="other" <?php echo ($user['gender'] === 'other') ? 'checked' : ''; ?>> Other
                        </label>
                    </div>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="dob">Date of Birth</label>
                    <input type="date" id="dob" name="dob" class="form-control" value="<?php echo htmlspecialchars($user['dob'] ?? ''); ?>">
                </div>

                <div class="form-group">
                    <label for="role">Role</label>
                    <select id="role" name="role" class="form-control">
                        <option value="user" <?php echo (strtolower($user['role'] ?? '') === 'user') ? 'selected' : ''; ?>>User</option>
                        <option value="editor" <?php echo (strtolower($user['role'] ?? '') === 'editor') ? 'selected' : ''; ?>>Editor</option>
                        <option value="admin" <?php echo (strtolower($user['role'] ?? '') === 'admin') ? 'selected' : ''; ?>>Admin</option>
                    </select>
                </div>
            </div>

            <div class="form-group">
                <label for="address">Address</label>
                <input type="text" id="address" name="address" class="form-control" value="<?php echo htmlspecialchars($user['address'] ?? ''); ?>">
            </div>

            <div class="form-group">
                <label for="bio">Short Bio</label>
                <textarea id="bio" name="bio" class="form-control" style="min-height: 80px;"><?php echo htmlspecialchars($user['bio'] ?? ''); ?></textarea>
            </div>

            <div class="button-row">
                <button type="submit" class="btn btn-primary">Save Changes</button>
                <a href="manage_users.php" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</body>
</html>
