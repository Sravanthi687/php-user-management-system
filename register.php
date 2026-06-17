<?php
// register.php
require_once __DIR__ . '/db.php';

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $phone = trim($_POST['phone'] ?? '');
    $gender = trim($_POST['gender'] ?? '');
    $bio = trim($_POST['bio'] ?? '');
    $dob = trim($_POST['dob'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $role = trim($_POST['role'] ?? 'user');

    // Server-side validation
    if (empty($name) || empty($email) || empty($password)) {
        $error = 'Name, Email, and Password are required fields.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Invalid email format.';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters long.';
    } else {
        // Check if email already exists using MySQLi
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
        if ($stmt) {
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $stmt->store_result();
            if ($stmt->num_rows > 0) {
                $error = 'Email is already registered.';
                $stmt->close();
            } else {
                $stmt->close();
                // Hash password
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                
                // Insert new user
                $stmt = $conn->prepare("INSERT INTO users (name, email, password, phone, gender, bio, dob, address, role) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
                if ($stmt) {
                    // Normalize empty values to null for DB insertion if needed, or insert empty strings
                    $dob_val = !empty($dob) ? $dob : null;
                    $stmt->bind_param("sssssssss", $name, $email, $hashed_password, $phone, $gender, $bio, $dob_val, $address, $role);
                    
                    if ($stmt->execute()) {
                        $message = 'User registered successfully! You can now login.';
                    } else {
                        $error = 'Error registering user: ' . $stmt->error;
                    }
                    $stmt->close();
                } else {
                    $error = 'Database statement preparation failed.';
                }
            }
        } else {
            $error = 'Database query failed.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - User Management</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <div class="page-header">
            <div>
                <h1>Create Account</h1>
                <p class="subtitle">Enter details to register a new user in the system</p>
            </div>
            <a href="login.php" class="btn btn-secondary">Sign In Instead</a>
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

        <form action="register.php" method="POST" class="user-form">
            <div class="form-row">
                <div class="form-group">
                    <label for="name">Full Name *</label>
                    <input type="text" id="name" name="name" class="form-control" placeholder="John Doe" required>
                </div>

                <div class="form-group">
                    <label for="email">Email Address *</label>
                    <input type="email" id="email" name="email" class="form-control" placeholder="john@example.com" required>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="password">Password *</label>
                    <input type="password" id="password" name="password" class="form-control" placeholder="Minimum 6 characters" required>
                </div>

                <div class="form-group">
                    <label for="phone">Phone Number</label>
                    <input type="tel" id="phone" name="phone" class="form-control" placeholder="+1 (555) 000-0000">
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>Gender</label>
                    <div class="radio-group">
                        <label class="radio-label">
                            <input type="radio" name="gender" value="male" checked> Male
                        </label>
                        <label class="radio-label">
                            <input type="radio" name="gender" value="female"> Female
                        </label>
                        <label class="radio-label">
                            <input type="radio" name="gender" value="other"> Other
                        </label>
                    </div>
                </div>

                <div class="form-group">
                    <label for="dob">Date of Birth</label>
                    <input type="date" id="dob" name="dob" class="form-control">
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="role">Role</label>
                    <select id="role" name="role" class="form-control">
                        <option value="user">User</option>
                        <option value="editor">Editor</option>
                        <option value="admin">Admin</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="address">Address</label>
                    <input type="text" id="address" name="address" class="form-control" placeholder="123 Main St, City, Country">
                </div>
            </div>

            <div class="form-group">
                <label for="bio">Short Bio</label>
                <textarea id="bio" name="bio" class="form-control" placeholder="Tell us about yourself..." style="min-height: 80px;"></textarea>
            </div>

            <div class="button-row">
                <button type="submit" class="btn btn-primary">Submit Registration</button>
                <button type="reset" class="btn btn-secondary">Reset Form</button>
            </div>
        </form>
    </div>
</body>
</html>
