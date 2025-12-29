<?php
session_start();
require '../db.php';
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] !== 'engineer' && $_SESSION['role'] !== 'admin')) {
    header("Location: staff_login.php");
    exit(); //user ki logged in? admin or engineer? otherwise bhag
}
$staff_user_id = $_SESSION['user_id']; 
$complaint_id = $_GET['id'] ?? 0; 
$msg = "";
$error = "";
$sql = "SELECT c.*, cat.name as category_name, u.name as citizen_name, u.phone 
        FROM Complaint c
        JOIN Complaint_category cat ON c.cat_id = cat.category_id
        JOIN USER u ON c.user_id = u.user_id
        WHERE c.complaint_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $complaint_id);
$stmt->execute();
$complaint = $stmt->get_result()->fetch_assoc();
if (!$complaint) die("Complaint not found.");
$current_status = $complaint['status'];
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $target_status = $_POST['target_status'] ?? '';
    $valid_action = false;
    $requires_evidence = false;
    if ($current_status == 'Pending' && ($target_status == 'In Progress' || $target_status == 'Rejected')) {
        $valid_action = true;
    } 
    elseif ($current_status == 'In Progress' && $target_status == 'Resolved') {
        $valid_action = true;
        $requires_evidence = true; 
    }
    if ($valid_action) {
        $remarks = trim($_POST['resolution_remarks'] ?? null); 
        $resolution_image_name = $complaint['resolution_image']; 
        if ($requires_evidence) {
            if (empty($remarks)) {
                $error = "Error: You must write remarks to resolve an issue.";
            } elseif (empty($_FILES['resolution_image']['name']) && empty($resolution_image_name)) {
                $error = "Error: You must upload a photo proof to resolve an issue.";
            }
        }
        if (!$error && !empty($_FILES['resolution_image']['name'])) {
             $target_dir = "../uploads/";
             $file_ext = strtolower(pathinfo($_FILES["resolution_image"]["name"], PATHINFO_EXTENSION));
             $allowed = ['jpg', 'jpeg', 'png'];
             if(in_array($file_ext, $allowed)) {
                 $new_filename = "resolved_" . $complaint_id . "_" . time() . "." . $file_ext;
                 if(move_uploaded_file($_FILES["resolution_image"]["tmp_name"], $target_dir . $new_filename)) {
                     $resolution_image_name = $new_filename;
                 } else { $error = "Failed to upload image."; }
             } else { $error = "Invalid file type. Only JPG/PNG allowed."; }
        }
        if (!$error) {
            $conn->begin_transaction();
            try {
                $history_sql = "INSERT INTO Complaint_History (complaint_id, status_from, status_to, changed_by_user_id) VALUES (?, ?, ?, ?)";
                $stmt_h = $conn->prepare($history_sql);
                $stmt_h->bind_param("issi", $complaint_id, $current_status, $target_status, $staff_user_id);
                $stmt_h->execute();
                $stmt_h->close();

                if ($target_status == 'Resolved') {
                    $update_sql = "UPDATE Complaint SET status=?, resolution_remarks=?, resolution_image=? WHERE complaint_id=?";
                    $stmt_u = $conn->prepare($update_sql);
                    $stmt_u->bind_param("sssi", $target_status, $remarks, $resolution_image_name, $complaint_id);
                } else {
                    $update_sql = "UPDATE Complaint SET status=? WHERE complaint_id=?";
                    $stmt_u = $conn->prepare($update_sql);
                    $stmt_u->bind_param("si", $target_status, $complaint_id);
                }
                $stmt_u->execute();
                $stmt_u->close();
                $conn->commit();
                header("Location: complaint_details.php?id=$complaint_id&success=1");
                exit();

            } catch (Exception $e) {
                $conn->rollback(); 
                $error = "Database Error: " . $e->getMessage();
            }
        }
    } else {
        $error = "Invalid workflow action. Please refresh the page.";
    }
}
if(isset($_GET['success'])) $msg = "Status updated successfully!";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Complaint - Poricchonota</title>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <style>
        body { font-family: 'Segoe UI', sans-serif; background-color: #f4f6f9; padding: 20px; }
        .container { max-width: 1000px; margin: 0 auto; background: white; padding: 30px; border-radius: 10px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); }
        .header-row { display: flex; justify-content: space-between; border-bottom: 2px solid #eee; padding-bottom: 20px; margin-bottom: 20px; }
        h2 { margin: 0; color: #2c3e50; }
        .grid { display: grid; grid-template-columns: 1fr 1fr; gap: 30px; }
        .label { font-weight: bold; color: #7f8c8d; display: block; font-size: 12px; text-transform: uppercase; }
        .value { font-size: 16px; color: #333; margin-bottom: 15px; display:block;}
        .complaint-img { width: 100%; height: 250px; object-fit: cover; border-radius: 8px; border: 1px solid #ddd; }
        #map { height: 250px; width: 100%; border-radius: 8px; border: 1px solid #ddd; }
        .back-link { display: inline-block; margin-bottom: 20px; color: #3498db; text-decoration: none; font-weight: bold; }
        .workflow-box { background: #eef2f3; padding: 25px; border-radius: 8px; margin-top: 20px; border-left: 5px solid #3498db; }
        .workflow-title { font-weight:bold; color:#2c3e50; margin-bottom:15px; display:block; font-size:18px;}
        .btn-action { padding: 12px 20px; border: none; border-radius: 5px; cursor: pointer; font-weight: bold; font-size: 14px; margin-right: 10px; transition: 0.2s; color:white;}
        .btn-progress { background-color: #3498db; } .btn-progress:hover { background-color: #2980b9; }
        .btn-reject { background-color: #e74c3c; } .btn-reject:hover { background-color: #c0392b; }
        .btn-resolve { background-color: #27ae60; width: 100%; } .btn-resolve:hover { background-color: #219150; }
        
        textarea, input[type="file"] { width: 100%; padding: 10px; margin-bottom: 15px; border: 1px solid #ccc; border-radius: 5px; box-sizing: border-box; }
        textarea { height: 80px; resize: vertical; }
        .required-mark { color: red; }
    </style>
</head>
<body>

<a href="engineer_dashboard.php" class="back-link">&#8592; Back to Dashboard</a>

<div class="container">
    <div class="header-row">
        <div>
            <h2>Complaint #<?php echo $complaint_id; ?></h2>
            <span style="color:#777;">Reported on <?php echo date("d M Y, h:i A", strtotime($complaint['created_at'])); ?></span>
        </div>
        <div style="text-align:right;">
            <span class="label">Current Status</span>
            <strong style="color: #e67e22; font-size: 18px; text-transform:uppercase;"><?php echo $complaint['status']; ?></strong>
        </div>
    </div>

    <?php if($msg) echo "<div style='background:#dff0d8; color:#3c763d; padding:10px; border-radius:5px; margin-bottom:20px;'>$msg</div>"; ?>
    <?php if($error) echo "<div style='background:#f2dede; color:#a94442; padding:10px; border-radius:5px; margin-bottom:20px;'>$error</div>"; ?>

    <div class="grid">
        <div>
            <span class="label">Category</span> <span class="value"><?php echo htmlspecialchars($complaint['category_name']); ?></span>
            <span class="label">Citizen Name</span> <span class="value"><?php echo htmlspecialchars($complaint['citizen_name']); ?> (<?php echo htmlspecialchars($complaint['phone'] ?? 'N/A'); ?>)</span>
            <span class="label">Problem Description</span> <p class="value" style="background:#fff; padding:10px; border:1px solid #eee;"><?php echo nl2br(htmlspecialchars($complaint['description'])); ?></p>
            <span class="label">Location</span> <span class="value"><?php echo htmlspecialchars($complaint['location']); ?></span>

            <div class="workflow-box">
                <span class="workflow-title">Take Action</span>
                
                <?php if ($current_status == 'Pending'): ?>
                    <p>Complaint is currently pending. Start working on it or reject it.</p>
                    <div style="display:flex;">
                        <form method="POST">
                            <input type="hidden" name="target_status" value="In Progress">
                            <button type="submit" class="btn-action btn-progress">Start Progress / Accept</button>
                        </form>
                        <form method="POST" onsubmit="return confirm('Are you sure you want to reject this?');">
                            <input type="hidden" name="target_status" value="Rejected">
                            <button type="submit" class="btn-action btn-reject">Reject Complaint</button>
                        </form>
                    </div>

                <?php elseif ($current_status == 'In Progress'): ?>
                    <p>Work is in progress. Once done, submit proof to resolve.</p>
                    <form method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="target_status" value="Resolved">
                        
                        <label class="label">Engineer's Remarks <span class="required-mark">*</span></label>
                        <textarea name="resolution_remarks" required placeholder="Describe what you did to fix it..."></textarea>
                        
                        <label class="label">Upload Proof of Work (Image) <span class="required-mark">*</span></label>
                        <input type="file" name="resolution_image" accept="image/*" required>
                        
                        <button type="submit" class="btn-action btn-resolve">Complete & Resolve Complaint</button>
                    </form>
                    
                <?php else: ?>
                    <div style="text-align:center; color:#7f8c8d; padding: 20px;">
                        This complaint is finalized as <strong><?php echo $current_status; ?></strong>.<br>No further actions can be taken.
                    </div>
                <?php endif; ?>

            </div>
            </div>

        <div>
             <div class="media-box">
                <span class="label">1. Citizen's Report (Before)</span>
                <?php if (!empty($complaint['complaint_image'])): ?>
                    <img src="../uploads/<?php echo $complaint['complaint_image']; ?>" class="complaint-img">
                <?php else: ?> <div style="padding:20px; background:#eee; text-align:center;">No photo</div> <?php endif; ?>
            </div>

            <div class="media-box" style="margin-top:20px;">
                <span class="label">2. Location Map</span>
                <?php if (!empty($complaint['latitude'])): ?><div id="map"></div><?php else: ?><div style="padding:20px; background:#eee; text-align:center;">No GPS data</div><?php endif; ?>
            </div>

            <?php if (!empty($complaint['resolution_image'])): ?>
            <div class="media-box" style="margin-top:20px;">
                <span class="label" style="color:#2ecc71;">3. Engineer's Solution (After)</span>
                <img src="../uploads/<?php echo $complaint['resolution_image']; ?>" class="complaint-img" style="border: 2px solid #2ecc71;">
                <p style="background:#d4edda; padding:10px; margin-top:5px; border-radius:4px; color:#155724;"><strong>Remarks:</strong> <?php echo htmlspecialchars($complaint['resolution_remarks']); ?></p>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
    var lat = <?php echo $complaint['latitude'] ?? 0; ?>;
    var lng = <?php echo $complaint['longitude'] ?? 0; ?>;
    if (lat != 0) {
        var map = L.map('map').setView([lat, lng], 15);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(map);
        L.marker([lat, lng]).addTo(map).bindPopup("Issue Location").openPopup();
    }
</script>
</body>
</html>