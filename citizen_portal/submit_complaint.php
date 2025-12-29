<?php
session_start(); //login memory start
require '../db.php'; //db connection aseh toh?
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'citizen') {
    header("Location: citizen_login.php"); //user_id or citizen na thakle login page e jao
    exit();
}
$success_msg = ""; // success! show korbe 
$error_msg = ""; // error show korbe

if ($_SERVER["REQUEST_METHOD"] == "POST") { //form submit korlei
    $user_id = $_SESSION['user_id'];
    $cat_id = $_POST['cat_id'];
    $location_text = trim($_POST['location']); //
    $description = trim($_POST['description']);
    $latitude = !empty($_POST['latitude']) ? $_POST['latitude'] : NULL; //if user map e click kore then laltitude longitude set hobe nahole null
    $longitude = !empty($_POST['longitude']) ? $_POST['longitude'] : NULL;
    $image_path = NULL; //$_files stores my photo file info
    if (isset($_FILES['complaint_image']) && $_FILES['complaint_image']['error'] == 0) { //uploads e store kortese pictures
        $target_dir = "../uploads/";
        $file_name = time() . "_" . basename($_FILES["complaint_image"]["name"]); //timestamp helps form unique name if same name image hoy like
        //11754_sds.jpg and 11755_sds.jpg ar pathinfo extension dei like jpg ar strtolower diye small letter e convert kore
        $target_file = $target_dir . $file_name;
        $image_type = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
        $allowed_types = array("jpg", "jpeg", "png", "gif"); //aigula nah hoile bye bye
        if (in_array($image_type, $allowed_types)) {
            if (move_uploaded_file($_FILES["complaint_image"]["tmp_name"], $target_file)) { //moving files from temp to uploads folder
                $image_path = $file_name; 
            } else {
                $error_msg = "Failed to upload image.";
            }
        } else {
            $error_msg = "Only JPG, JPEG, PNG & GIF files are allowed.";
        }
    }
    if (empty($location_text) || empty($description) || empty($cat_id)) { //fields empty rakhle REMIND THEM
        $error_msg = "Please fill in all required fields.";
    } elseif (empty($error_msg)) {
        $sql = "INSERT INTO Complaint (user_id, cat_id, description, location, latitude, longitude, complaint_image, status, created_at) 
                VALUES (?, ?, ?, ?, ?, ?, ?, 'Pending', NOW())"; //pending diye start hobe
        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("iisssds", $user_id, $cat_id, $description, $location_text, $latitude, $longitude, $image_path);
            if ($stmt->execute()) { // bind them sql all together (integer, integer, string, string, string, double, string)
                $success_msg = "Complaint submitted successfully!";
                header("refresh:2;url=citizen_dashboard.php");
            } else {
                $error_msg = "Error submitting: " . $stmt->error;
            }
            $stmt->close();
        } else {
            $error_msg = "Database error: " . $conn->error;
        }
    }
}
$categories = [];
$cat_sql = "SELECT category_id, name FROM Complaint_category"; //dropdown menu which is stored in $result
$result = $conn->query($cat_sql);
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $categories[] = $row; //categories e store kore then html diye build <options> tag
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Post Complaint - Poricchonota</title>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <style>
        body { font-family: 'Segoe UI', sans-serif; background-color: #f0f2f5; margin: 0; padding: 0; }
        .header { background-color: #009688; color: white; padding: 15px 40px; display: flex; align-items: center; }
        .back-btn { color: white; text-decoration: none; font-weight: bold; margin-right: 20px; font-size: 20px; }
        .container { max-width: 600px; margin: 40px auto; background: white; padding: 40px; border-radius: 10px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); }
        h2 { color: #009688; margin-top: 0; }
        label { display: block; margin-top: 15px; color: #555; font-weight: bold; }
        select, input[type="text"], textarea, input[type="file"] {
            width: 100%; padding: 12px; margin-top: 5px; 
            border: 1px solid #ddd; border-radius: 5px; 
            box-sizing: border-box; font-family: inherit;
        }
        #map { height: 300px; width: 100%; margin-top: 10px; border-radius: 5px; border: 2px solid #ddd; }
        button {
            width: 100%; padding: 14px; margin-top: 25px;
            background: linear-gradient(135deg, #2ecc71, #27ae60);
            color: white; border: none; border-radius: 5px;
            font-size: 16px; font-weight: bold; cursor: pointer;
        }
        button:hover { opacity: 0.9; }
        .msg { padding: 10px; margin-bottom: 20px; border-radius: 5px; text-align: center; }
        .success { background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .error { background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
    </style>
</head>
<body>
<div class="header">
    <a href="citizen_dashboard.php" class="back-btn">&#8592;</a>
    <h3>Post a New Complaint</h3>
</div>
<div class="container">
    <h2>Report an Issue</h2>
    <p style="color:#777; margin-bottom:20px;">Provide details, a photo, and the location.</p>
    <?php if ($success_msg): ?>
        <div class="msg success"><?php echo $success_msg; ?></div>
    <?php elseif ($error_msg): ?>
        <div class="msg error"><?php echo $error_msg; ?></div>
    <?php endif; ?>
    <form method="POST" action="" enctype="multipart/form-data">
        <label>Category</label>
        <select name="cat_id" required>
            <option value="">-- Select Issue Type --</option>
            <?php foreach ($categories as $cat): ?>
                <option value="<?php echo $cat['category_id']; ?>">
                    <?php echo htmlspecialchars($cat['name']); ?>
                </option>
            <?php endforeach; ?>
        </select>
        <label>Description</label>
        <textarea name="description" rows="3" placeholder="Describe the issue in detail..." required></textarea>
        <label>Attach Photo (Optional)</label>
        <input type="file" name="complaint_image" accept="image/*">
        <label>Address / Landmark</label>
        <input type="text" name="location" placeholder="e.g. Near House 12, Road 5" required>
        <label>Pinpoint Location on Map (Click on the map)</label>
        <div id="map"></div>
        <input type="hidden" name="latitude" id="lat">
        <input type="hidden" name="longitude" id="lng">
        <button type="submit">Submit Complaint</button>
    </form>
</div>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script> <!-- leaflet map library which runs on the browser -->
<script>
    var map = L.map('map').setView([23.8103, 90.4125], 13); //dhaka center kora map lat,long ar zoom level
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { //loads map from OpenStreetMap server
        attribution: '&copy; OpenStreetMap contributors'
    }).addTo(map);
    var marker; // user click korle then get the coordinates and put them info form fields
    map.on('click', function(e) {
        var lat = e.latlng.lat;
        var lng = e.latlng.lng;
        if (marker) {
            marker.setLatLng(e.latlng); //market exist korle update its position
        } else {
            marker = L.marker(e.latlng).addTo(map);
        }
        document.getElementById('lat').value = lat; //return the element with specified value JS DOM by finding html input with id "lat"
        document.getElementById('lng').value = lng;
    });
</script>
</body>
</html>