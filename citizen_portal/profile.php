<?php
session_start();
require '../db.php';
if (!isset($_SESSION['user_id'])) {
    header("Location: citizen_login.php");
    exit();
}
$user_id = $_SESSION['user_id'];
$msg = "";
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $new_phone = $_POST['phone'];
    $new_address = $_POST['address'];
    
    $sql = "UPDATE USER SET phone = ?, address = ? WHERE user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssi", $new_phone, $new_address, $user_id);
    if($stmt->execute()){
        $msg = "Profile updated successfully!";
    }
}
$sql = "SELECT * FROM USER WHERE user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Profile - Poricchonota</title>
    <style>
        body { font-family: 'Segoe UI', sans-serif; background-color: #f4f6f9; padding: 20px; }
        .container { max-width: 600px; margin: 0 auto; background: white; padding: 40px; border-radius: 10px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); }
        h2 { border-bottom: 2px solid #eee; padding-bottom: 10px; margin-bottom: 20px; color: #2c3e50; }
        .form-group { margin-bottom: 15px; }
        label { font-weight: bold; display: block; margin-bottom: 5px; color: #555; }
        input { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px; box-sizing: border-box; }
        input[readonly] { background-color: #e9ecef; color: #6c757d; cursor: not-allowed; }
        button { background-color: #3498db; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer; font-weight: bold; }
        button:hover { background-color: #2980b9; }
        .back-link { display: block; margin-top: 20px; text-align: center; color: #7f8c8d; text-decoration: none; }
        .success { color: green; margin-bottom: 15px; font-weight: bold; }
    </style>
</head>
<body>
<div class="container">
    <h2>My Profile</h2>
    <?php if($msg) echo "<div class='success'>$msg</div>"; ?>
    <form method="POST">
        <div class="form-group">
            <label>Full Name</label>
            <input type="text" value="<?php echo htmlspecialchars($user['name']); ?>" readonly>
        </div>

        <div class="form-group">
            <label>National ID (NID)</label>
            <input type="text" value="<?php echo htmlspecialchars($user['nid']); ?>" readonly>
        </div>

        <div class="form-group">
            <label>Email Address</label>
            <input type="email" value="<?php echo htmlspecialchars($user['email']); ?>" readonly>
        </div>

        <div class="form-group">
            <label>Phone Number (Editable)</label>
            <input type="text" name="phone" value="<?php echo htmlspecialchars($user['phone']); ?>" required>
        </div>

        <div class="form-group">
            <label>Address (Editable)</label>
            <input type="text" name="address" value="<?php echo htmlspecialchars($user['address'] ?? ''); ?>">
        </div>

        <button type="submit">Update Profile</button>
    </form>
    <a href="citizen_dashboard.php" class="back-link">← Back to Dashboard</a>
</div>

</body>
</html>