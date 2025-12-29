<?php
session_start(); 
require '../db.php';
//  user_id is existing in the session? check if not then redirect
if (!isset($_SESSION['user_id'])) {
    header("Location: citizen_login.php"); 
    exit();
}
$complaint_id = $_GET['id'] ?? 0; //id missing then 0 but id thakle get from URL
$user_id = $_SESSION['user_id']; //logged in user
$msg = ""; //display message like reopened

// complaint reopen korte chaile 
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'reopen') {
    $reopen_reason = trim($_POST['reopen_reason']); //text area of citizen
    if (!empty($reopen_reason)) {
        // citizen reopen korte parbena complaint without reason
        $stmt_upd = $conn->prepare("UPDATE Complaint SET status='In Progress', description=CONCAT(description, ?) WHERE complaint_id=? AND user_id=?");
        // Status change koro, apppend the reason by citizen and only allow this to be access by citizen themself.
        $new_desc_append = "\n\n[RE-OPENED by Citizen]: " . $reopen_reason;
        $stmt_upd->bind_param("sii", $new_desc_append, $complaint_id, $user_id);
        if ($stmt_upd->execute() && $stmt_upd->affected_rows > 0) {
            // Records the timeline entry
            $hist_sql = "INSERT INTO Complaint_History (complaint_id, status_from, status_to, changed_by_user_id) VALUES (?, 'Resolved', 'In Progress', ?)";
            $stmt_h = $conn->prepare($hist_sql);
            $stmt_h->bind_param("ii", $complaint_id, $user_id);
            $stmt_h->execute();
            $msg = "Complaint RE-OPENED successfully. The engineer has been notified."; //notify by showing message at top
        } else {
            $msg = "Error: You do not have permission to re-open this complaint.";
        }
    }
}
// fetches complaint details by selecting everything from complaint, join with complaint category and bring data if complaint_id and user_id match
$sql = "SELECT c.*, cat.name as category_name, u.name as citizen_name
        FROM Complaint c
        JOIN Complaint_category cat ON c.cat_id = cat.category_id
        JOIN USER u ON c.user_id = u.user_id
        WHERE c.complaint_id = ?"; 

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $complaint_id); // Binds the complaint ID
$stmt->execute();
$complaint = $stmt->get_result()->fetch_assoc();

if (!$complaint) die("Complaint not found.");
$is_owner = ($complaint['user_id'] == $user_id);

// gets all history rows of this complaint and joins with USER table so staff name and their role can be seen.
$history_sql = "SELECT h.*, u.name as staff_name, u.role 
                FROM Complaint_History h
                JOIN USER u ON h.changed_by_user_id = u.user_id
                WHERE h.complaint_id = ?
                ORDER BY h.changed_at ASC";
$stmt_h = $conn->prepare($history_sql);
$stmt_h->bind_param("i", $complaint_id);
$stmt_h->execute();
$history_result = $stmt_h->get_result();
$timeline = []; //store the history in rows of array
while ($row = $history_result->fetch_assoc()) {
    $timeline[] = $row;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Complaint Details</title>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <style>
        body { font-family: 'Segoe UI', sans-serif; background-color: #f4f6f9; padding: 20px; }
        .container { max-width: 1100px; margin: 0 auto; background: white; padding: 30px; border-radius: 10px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); }
        .header-row { display: flex; justify-content: space-between; border-bottom: 2px solid #eee; padding-bottom: 20px; margin-bottom: 20px; }
        .grid-3 { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 30px; }
        .label { font-weight: bold; color: #7f8c8d; display: block; font-size: 12px; text-transform: uppercase; margin-top: 15px; }
        .value { font-size: 16px; color: #333; }
        .media-box { margin-top: 20px; }
        .complaint-img { width: 100%; height: 200px; object-fit: cover; border-radius: 8px; border: 1px solid #ddd; }
        #map { height: 200px; width: 100%; border-radius: 8px; border: 1px solid #ddd; }
        .status-badge { padding: 5px 12px; border-radius: 15px; font-weight: bold; color: white; display: inline-block;}
        .Pending { background-color: #f39c12; }
        .In-Progress { background-color: #3498db; }
        .Resolved { background-color: #27ae60; }
        .timeline-box { background: #fdfdfd; padding: 20px; border-radius: 8px; border: 1px solid #eee; }
        .timeline { list-style: none; padding: 0; margin: 0; }
        .timeline li { padding-left: 30px; position: relative; margin-bottom: 20px; }
        .timeline li::before { content: ''; position: absolute; left: 0; top: 5px; width: 12px; height: 12px; border-radius: 50%; background: #ddd; z-index: 2; }
        .timeline li::after { content: ''; position: absolute; left: 7px; top: 15px; width: 2px; height: 100%; background: #ddd; z-index: 1; }
        .timeline li:last-child::after { display: none; } 
        .timeline li.latest::before { background: #3498db; }
        .reopen-container { border-top: 2px solid #ffcccc; margin-top: 20px; padding-top: 15px; }
        .btn-reopen { width: 100%; background: #e74c3c; color: white; padding: 10px; border: none; border-radius: 5px; cursor: pointer; font-weight: bold; margin-top: 10px; }
        .back-link { display: inline-block; margin-bottom: 20px; color: #3498db; text-decoration: none; font-weight: bold; }
    </style>
</head>
<body>

<a href="community_feed.php" class="back-link">&#8592; Back to Feed</a>

<div class="container">
    
    <?php if($msg): ?>
        <div style="background:#dff0d8; color:#3c763d; padding:10px; border-radius:5px; margin-bottom:20px;">
            <?php echo $msg; ?>
        </div>
    <?php endif; ?>

    <div class="header-row">
        <div>
            <h2>Complaint #<?php echo $complaint_id; ?></h2>
            <span style="color:#777;">
                By <?php echo htmlspecialchars($complaint['citizen_name']); ?> 
                on <?php echo date("d M Y", strtotime($complaint['created_at'])); ?>
            </span>
        </div>
        <div>
            <span class="status-badge <?php echo str_replace(' ', '-', $complaint['status']); ?>">
                <?php echo $complaint['status']; ?>
            </span>
        </div>
    </div>

    <div class="grid-3">
        <div>
            <h3 style="border-bottom:1px solid #eee;">Details</h3>
            <span class="label">Category</span>
            <div class="value"><?php echo htmlspecialchars($complaint['category_name']); ?></div>

            <span class="label">Description</span>
            <div class="value"><?php echo nl2br(htmlspecialchars($complaint['description'])); ?></div>

            <span class="label">Location Name</span>
            <div class="value"><?php echo htmlspecialchars($complaint['location']); ?></div>
        </div>

        <div>
            <h3 style="border-bottom:1px solid #eee;">Evidence</h3>
            
            <div class="media-box" style="margin-top:0;">
                <span class="label">Report Photo</span>
                <?php if (!empty($complaint['complaint_image'])): ?>
                    <img src="../uploads/<?php echo $complaint['complaint_image']; ?>" class="complaint-img">
                <?php else: ?> 
                    <div style="padding:20px; background:#eee; text-align:center;">No photo</div> 
                <?php endif; ?>
            </div>

            <?php if(!empty($complaint['resolution_image'])): ?>
                <div class="media-box">
                    <span class="label" style="color:#2ecc71;">Resolution Photo</span>
                    <img src="../uploads/<?php echo $complaint['resolution_image']; ?>" class="complaint-img" style="border:2px solid #2ecc71;">
                </div>
            <?php endif; ?>
             
             <div class="media-box">
                <span class="label">Map Location</span>
                <?php if (!empty($complaint['latitude'])): ?><div id="map"></div><?php else: ?><div>No map data</div><?php endif; ?>
            </div>
        </div>

        <div>
            <h3 style="border-bottom:1px solid #eee;">History</h3>
            <div class="timeline-box">
                <ul class="timeline">
                    <?php foreach($timeline as $event): ?>
                        <li>
                            <span class="tl-date"><?php echo date("d M Y, h:i A", strtotime($event['changed_at'])); ?></span>
                            <div>
                                Status: <strong><?php echo $event['status_to']; ?></strong>
                                <br><small>by <?php echo htmlspecialchars($event['staff_name']); ?></small>
                            </div>
                        </li>
                    <?php endforeach; ?>
                </ul>

                <?php if($complaint['status'] == 'Resolved' && $is_owner): ?>
                    <div class="reopen-container">
                        <strong style="color:#e74c3c;">Not satisfied?</strong>
                        <form method="POST">
                            <input type="hidden" name="action" value="reopen">
                            <textarea name="reopen_reason" class="reopen-textarea" rows="2" placeholder="Why is it not fixed?" required></textarea>
                            <button type="submit" class="btn-reopen">Re-open Complaint</button>
                        </form>
                    </div>
                <?php endif; ?>

            </div>
        </div>
    </div>
</div>

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
    var lat = <?php echo $complaint['latitude'] ?? 0; ?>;
    var lng = <?php echo $complaint['longitude'] ?? 0; ?>;
    if (lat != 0 && lng != 0) {
        var map = L.map('map').setView([lat, lng], 15);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(map);
        L.marker([lat, lng]).addTo(map).bindPopup("Issue Location").openPopup();
    }
</script>

</body>
</html>