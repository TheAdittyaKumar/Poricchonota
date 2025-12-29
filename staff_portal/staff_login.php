<?php
session_start();
require '../db.php';

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $sql = "SELECT user_id, name, password, role, dept_id FROM USER WHERE email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        
        if (password_verify($password, $user['password'])) {
            if ($user['role'] === 'admin' || $user['role'] === 'engineer') {
                $_SESSION['user_id'] = $user['user_id'];
                $_SESSION['name'] = $user['name'];
                $_SESSION['role'] = $user['role'];
                $_SESSION['dept_id'] = $user['dept_id']; 

                if ($user['role'] === 'admin') {
                    header("Location: admin_dashboard.php");
                } else {
                    header("Location: engineer_dashboard.php");
                }
                exit();
            } else {
                $error = "Access Denied: You are not authorized staff.";
            }
        } else {
            $error = "Invalid password.";
        }
    } else {
        $error = "No staff account found with this email.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Staff Login - Poricchonota</title>
    <style>
        body { 
            font-family: 'Segoe UI', sans-serif; 
            background-image: url('../images/my_bg_two.png');
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            background-attachment: fixed;

            display: flex; 
            justify-content: center; 
            align-items: center; 
            height: 100vh; 
            margin: 0; 
        }

        .login-card { 
            background: rgba(255, 255, 255, 0.95);
            padding: 40px; 
            border-radius: 10px; 
            box-shadow: 0 8px 32px rgba(0,0,0,0.2);
            width: 100%; 
            max-width: 400px; 
            text-align: center; 
        }

        h2 { color: #2c3e50; margin-bottom: 20px; }
        input { width: 100%; padding: 12px; margin: 10px 0; border: 1px solid #ddd; border-radius: 5px; box-sizing: border-box; }
        button { width: 100%; padding: 12px; background-color: #27ae60; color: white; border: none; border-radius: 5px; cursor: pointer; font-size: 16px; font-weight: bold; }
        button:hover { background-color: #219150; }
        .error { color: red; font-size: 14px; margin-bottom: 10px; }
        .links { margin-top: 15px; font-size: 14px; }
        .links a { color: #3498db; text-decoration: none; margin: 0 5px; }
    </style>
</head>
<body>

<div class="login-card">
    <h2>Staff Portal Login</h2>
    <p style="color:#7f8c8d; font-size:14px;">Authorized Personnel Only</p>

    <?php if ($error): ?>
        <p class="error"><?php echo $error; ?></p>
    <?php endif; ?>

    <form method="POST">
        <input type="email" name="email" placeholder="Email Address" required>
        <input type="password" name="password" placeholder="Password" required>
        <button type="submit">Login</button>
    </form>

    <div class="links">
        <a href="staff_signup.php">Create New Staff Account</a>
    </div>
</div>

</body>
</html>