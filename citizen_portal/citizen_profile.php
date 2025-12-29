<?php
session_start();
require '../db.php';
if (!isset($_SESSION['user_id'])) { //user logged in toh?
    header("Location: citizen_login.php");
    exit();
}
$user_id = $_SESSION['user_id']; //store the user thats logged in
$sql = "SELECT name, nid, email, phone, role, created_at FROM USER WHERE user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result(); //return rows into result
$user = $result->fetch_assoc(); //$user['name]', $user['nid'] etc

if (!$user) {
    echo "User details not found.";
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Profile - Poricchonota</title>
    <style>
        body { font-family: 'Segoe UI', sans-serif; background-color: #f4f6f9; margin: 0; padding: 20px; }
        .container { max-width: 600px; margin: 40px auto; background: white; padding: 40px; border-radius: 10px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); }
        h2 { margin-top: 0; color: #2c3e50; border-bottom: 2px solid #eee; padding-bottom: 15px; }
        .profile-group { margin-bottom: 20px; }
        .label { font-weight: bold; color: #7f8c8d; font-size: 13px; text-transform: uppercase; display: block; margin-bottom: 5px; }
        .value { font-size: 18px; color: #333; }
        .back-btn { display: inline-block; margin-top: 20px; color: #3498db; text-decoration: none; font-weight: bold; }
        .back-btn:hover { text-decoration: underline; }
        .badge {
            display: inline-block;
            padding: 5px 10px;
            background: #2ecc71;
            color: white;
            border-radius: 15px;
            font-size: 12px;
            vertical-align: middle;
            margin-left: 10px;
        }
    </style>
</head>
<body>
<div class="container">
    <h2>
        My Profile 
        <span class="badge"><?php echo ucfirst($user['role']); ?></span>
    </h2>
    <div class="profile-group">
        <span class="label">Full Name</span>
        <div class="value"><?php echo htmlspecialchars($user['name']); ?></div>
    </div>
    <div class="profile-group">
        <span class="label">NID Number</span>
        <div class="value"><?php echo htmlspecialchars($user['nid']); ?></div>
    </div>
    <div class="profile-group">
        <span class="label">Email Address</span>
        <div class="value"><?php echo htmlspecialchars($user['email']); ?></div>
    </div>
    <div class="profile-group">
        <span class="label">Phone Number</span>
        <div class="value"><?php echo htmlspecialchars($user['phone']); ?></div>
    </div>
    <div class="profile-group">
        <span class="label">Account Created On</span>
        <div class="value"><?php echo date("d M Y, h:i A", strtotime($user['created_at'])); ?></div>
    </div>
    <a href="citizen_dashboard.php" class="back-btn">&larr; Back to Dashboard</a>
</div>
</body>
</html>