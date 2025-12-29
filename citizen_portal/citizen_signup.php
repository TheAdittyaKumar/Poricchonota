<?php
require '../db.php'; 
$message = ""; //success or error message dekhabe
if ($_SERVER["REQUEST_METHOD"] == "POST") { //register press korle this runs
    $full_name = $_POST['full_name'];
    $email     = $_POST['email'];
    $password  = trim($_POST['password']);
    $phone     = $_POST['phone'];
    $nid       = $_POST['nid']; 
    $city      = $_POST['city'];
    $address   = $_POST['address']; // pass default best hashing algo use korte bole
    $hashed_password = password_hash($password, PASSWORD_DEFAULT); //php please hash my password so my friends cant see my pass pls. 
    $conn->begin_transaction(); //we want to insert into citizen and user so both has to success otherwise rollback
    try {
        $sql_user = "INSERT INTO USER (name, email, password, phone, role, nid) VALUES (?, ?, ?, ?, 'citizen', ?)";
        $stmt = $conn->prepare($sql_user);
        $stmt->bind_param("sssss", $full_name, $email, $hashed_password, $phone, $nid); //5 strings are binded here except role
        if (!$stmt->execute()) { //email and nid unique nah hoile fail
            throw new Exception("Email or NID already exists.");
        }
        $new_user_id = $conn->insert_id; //auto generated my primary key for new user_id
        $stmt->close();
        $sql_citizen = "INSERT INTO citizen (user_id, city, address) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($sql_citizen);
        $stmt->bind_param("iss", $new_user_id, $city, $address);
        if (!$stmt->execute()) {
            throw new Exception("Error saving citizen details."); //signup cancel
        }
        $stmt->close();
        $conn->commit(); //successful inserts
        $message = "<div class='success'>Registration Successful! <a href='citizen_login.php'>Login Here</a></div>";
    } catch (Exception $e) {
        $conn->rollback();
        $message = "<div class='error'>Error: " . $e->getMessage() . "</div>";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Citizen Signup - Poricchonota</title>
    <style>
        body { font-family: sans-serif; background-color: #f4f4f4; display: flex; justify-content: center; align-items: center; height: 100vh; }
        .container { background: white; padding: 20px; border-radius: 8px; box-shadow: 0 0 10px rgba(0,0,0,0.1); width: 350px; }
        input, select, textarea { width: 100%; padding: 10px; margin: 8px 0; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box;}
        button { width: 100%; padding: 10px; background-color: #28a745; color: white; border: none; border-radius: 4px; cursor: pointer; }
        button:hover { background-color: #218838; }
        .error { color: red; margin-bottom: 10px; }
        .success { color: green; margin-bottom: 10px; }
        h2 { text-align: center; color: #333; }
    </style>
</head>
<body>

<div class="container">
    <h2>Citizen Registration</h2>
    <?php echo $message; ?>
    
    <form method="POST" action="">
        <input type="text" name="full_name" placeholder="Full Name" required>
        <input type="email" name="email" placeholder="Email Address" required>
        <input type="text" name="phone" placeholder="Phone Number" required>
        <input type="password" name="password" placeholder="Password" required>
        
        <input type="text" name="nid" placeholder="National ID (NID)" required>
        
        <hr>
        
        <input type="text" name="city" placeholder="City (e.g. Dhaka)" required>
        <textarea name="address" placeholder="Full Home Address" rows="2" required></textarea>
        
        <button type="submit">Register</button>
    </form>
    <p style="text-align: center;">Already have an account? <a href="citizen_login.php">Login</a></p>
</div>
</body>
</html>