<?php
session_start();
require '../db.php'; 
if (isset($_GET['logout'])) { //logout being handled here like clearing+destroying session, redirecting to login
    $_SESSION = array();
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }
    session_destroy();
    header("Location: citizen_login.php");
    exit();
}
if (isset($_SESSION['user_id'])) { //already logged in then go to dashboard
    header("location: citizen_dashboard.php");
    exit;
}
$error = "";
if ($_SERVER["REQUEST_METHOD"] == "POST") { //user login press korlei post diye send
    $email = trim($_POST['email']); //extra space boomed
    $password = trim($_POST['password']);
    $sql = "SELECT user_id, name, password, role FROM USER WHERE email = ?";
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows == 1) { //if email match kore
            $user_id = $name = $hashed_password = $role = ""; 
            $stmt->bind_result($user_id, $name, $hashed_password, $role);
            if ($stmt->fetch()) {
                if (password_verify($password, $hashed_password)) { //plain user input ar encrypted match korle then Successful login possible
                    session_regenerate_id();
                    $_SESSION['loggedin'] = true;
                    $_SESSION['user_id'] = $user_id;
                    $_SESSION['user_name'] = $name;
                    $_SESSION['role'] = $role;     
                    header("location: citizen_dashboard.php");
                    exit();
                } else {
                    $error = "Invalid password.";
                }
            }
        } else { //if email na mile
            $error = "No account found with that email.";
        }
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Citizen Login - Poricchonota</title>
    <style>
        body { 
            font-family: 'Segoe UI', sans-serif; 
            background-image: url('../images/my_bg.png'); 
            
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
        .login-box { 
            background: rgba(255, 255, 255, 0.95); 
            padding: 40px; 
            border-radius: 8px; 
            box-shadow: 0 8px 32px rgba(0,0,0,0.2); 
            width: 100%; 
            max-width: 400px; 
            text-align: center; 
        }
        h2 { margin-top: 0; color: #2c3e50; }
        input { 
            width: 100%; 
            padding: 12px; 
            margin: 10px 0; 
            border: 1px solid #ddd; 
            border-radius: 5px; 
            box-sizing: border-box; 
        }
        button { 
            width: 100%; 
            padding: 12px; 
            background-color: #009688; 
            color: white; 
            border: none; 
            border-radius: 5px; 
            cursor: pointer; 
            font-size: 16px; 
            font-weight: bold; 
            margin-top: 10px;
            transition: background 0.3s;
        }
        button:hover { background-color: #00796b; }
        .error { color: red; font-size: 14px; margin-bottom: 15px; }
        .links { margin-top: 20px; font-size: 14px; }
        .links a { color: #009688; text-decoration: none; font-weight: bold; }
        .links a:hover { text-decoration: underline; }
    </style>
</head>
<body>
<div class="login-box">
    <h2>Citizen Login</h2>
    <p style="color:#666; margin-bottom:20px;">Welcome back to Poricchonota</p>
    <?php if($error) { echo "<div class='error'>$error</div>"; } ?>
    <form action="" method="post">
        <input type="email" name="email" placeholder="Email Address" required>
        <input type="password" name="password" placeholder="Password" required>
        <button type="submit">Login</button>
    </form>
    <div class="links">
        Don't have an account? <a href="citizen_signup.php">Register here</a>
    </div>
</div>
</body>
</html>