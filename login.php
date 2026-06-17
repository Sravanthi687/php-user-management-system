<?php
// login.php
session_start();
require_once __DIR__ . '/db.php';

// If already logged in, redirect to dashboard
if (isset($_SESSION['user_id'])) {
    header("Location: manage_users.php");
    exit;
}

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        $error = 'Please enter both email and password.';
    } else {
        // Prepare statement using MySQLi
        $stmt = $conn->prepare("SELECT id, name, password, role FROM users WHERE email = ?");
        if ($stmt) {
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows === 1) {
                $user = $result->fetch_assoc();
                // Verify password hash
                if (password_verify($password, $user['password'])) {
                    // Start session
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['user_name'] = $user['name'];
                    $_SESSION['user_role'] = $user['role'];
                    
                    header("Location: manage_users.php");
                    exit;
                } else {
                    $error = 'Invalid password.';
                }
            } else {
                $error = 'No account found with that email address.';
            }
            $stmt->close();
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
    <title>Sign In - InfoPulse</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container" style="max-width: 450px; margin-top: 10vh;">
        <div style="text-align: center; margin-bottom: 2rem;">
            <h1>Sign In</h1>
            <p class="subtitle">Access your user management dashboard</p>
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

        <form action="login.php" method="POST" class="user-form">
            <div class="form-group">
                <label for="email">Email Address</label>
                <input type="email" id="email" name="email" class="form-control" placeholder="you@example.com" required>
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" class="form-control" placeholder="••••••••" required>
            </div>

            <div class="button-row" style="margin-top: 2rem;">
                <button type="submit" class="btn btn-primary" style="width: 100%;">Sign In</button>
            </div>

            <div style="text-align: center; margin-top: 1.5rem; font-size: 0.9rem;">
                <span style="color: var(--text-secondary);">Don't have an account? </span>
                <a href="register.php" style="color: var(--accent-primary); text-decoration: none; font-weight: 500;">Register Here</a>
            </div>
        </form>
    </div>
</body>
</html>
