<?php
session_start();
require '../db.php';
if (!isset($_SESSION['user_id'])) {
    header("Location: citizen_login.php");
    exit();
}
$my_id = $_SESSION['user_id']; //logged in user id, did I vote on this complaint earlier help kore
//c complaint table details, cat complaint category table, U user table theke select who filed the complaint, finally
//each complaint row theke give me vote_type if 1 then upvoted, -1 then downvoted by me ar null maneh korinai
// vote high up  ar newest up if same num of vote
$sql = "SELECT c.*, cat.name as category_name, u.name as citizen_name,
        (SELECT vote_type FROM Complaint_Votes v WHERE v.complaint_id = c.complaint_id AND v.user_id = ?) as my_vote
        FROM Complaint c
        JOIN Complaint_category cat ON c.cat_id = cat.category_id
        JOIN USER u ON c.user_id = u.user_id
        ORDER BY c.vote_count DESC, c.created_at DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $my_id);
$stmt->execute();
$result = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Community Issues - Poricchonota</title>
    <style>
        body { font-family: 'Segoe UI', sans-serif; background-color: #f4f6f9; margin: 0; padding: 20px; }
        .container { max-width: 800px; margin: 0 auto; }
        .nav-bar { margin-bottom: 20px; }
        .nav-bar a { text-decoration: none; color: #3498db; font-weight: bold; font-size: 16px; }

        .card { background: white; border-radius: 8px; padding: 20px; margin-bottom: 20px; box-shadow: 0 2px 5px rgba(0,0,0,0.05); display: flex; gap: 20px; }
        .vote-box { display: flex; flex-direction: column; align-items: center; justify-content: flex-start; min-width: 50px; }
        .vote-btn { background: none; border: none; font-size: 24px; cursor: pointer; color: #ccc; transition: 0.2s; padding: 0; }
        .vote-btn:hover { color: #555; }
        .score { font-size: 18px; font-weight: bold; margin: 5px 0; color: #333; }
        .vote-btn.active-up { color: #e67e22; }
        .vote-btn.active-down { color: #7f8c8d; }
        .content-box { flex-grow: 1; }
        .meta { color: #7f8c8d; font-size: 13px; margin-bottom: 5px; }
        .status-badge { display: inline-block; padding: 3px 8px; border-radius: 12px; font-size: 11px; font-weight: bold; text-transform: uppercase; color: white; margin-left: 10px; }
        .Pending { background-color: #f39c12; }
        .In-Progress { background-color: #3498db; }
        .Resolved { background-color: #27ae60; }
        .Rejected { background-color: #e74c3c; }

        h3 { margin: 5px 0 10px 0; color: #2c3e50; }
        p { color: #555; line-height: 1.5; margin: 0 0 10px 0; }
        .location { font-size: 13px; color: #888; display: flex; align-items: center; }
        .thumb { width: 100px; height: 100px; object-fit: cover; border-radius: 5px; border: 1px solid #eee; }
    </style>
</head>
<body>
<div class="container">
    <div class="nav-bar">
        <a href="citizen_dashboard.php">&larr; Back to Dashboard</a>
        <h1 style="margin-top:10px;">Community Issues</h1>
        <p style="color:#666;">Top voted issues get priority attention.</p>
    </div>
    <?php while($row = $result->fetch_assoc()): 
        $c_id = $row['Complaint_id'] ?? $row['complaint_id'];
        $votes = $row['vote_count'] ?? 0;
        $my_vote = $row['my_vote'] ?? 0;
        $up_class = ($my_vote == 1) ? 'active-up' : '';
        $down_class = ($my_vote == -1) ? 'active-down' : '';
    ?>
    <div class="card">
        <div class="vote-box">
            <button class="vote-btn up <?php echo $up_class; ?>" onclick="vote(<?php echo $c_id; ?>, 'up', this)">▲</button>
            <span class="score" id="score-<?php echo $c_id; ?>"><?php echo $votes; ?></span>
            <button class="vote-btn down <?php echo $down_class; ?>" onclick="vote(<?php echo $c_id; ?>, 'down', this)">▼</button>
        </div>
        <div class="content-box">
            <div class="meta">
                <span><?php echo htmlspecialchars($row['category_name']); ?></span>
                <span> • Posted by <?php echo htmlspecialchars($row['citizen_name']); ?></span>
                <span> • <?php echo date("d M", strtotime($row['created_at'])); ?></span>
                <span class="status-badge <?php echo str_replace(' ', '-', $row['status']); ?>"><?php echo $row['status']; ?></span>
            </div>
            <h3>
                <a href="view_complaint.php?id=<?php echo $c_id; ?>" style="text-decoration:none; color:inherit;">
                    <?php echo htmlspecialchars(substr($row['description'], 0, 50)) . '...'; ?>
                </a>
            </h3>
            <p><?php echo nl2br(htmlspecialchars($row['description'])); ?></p>
            <div class="location">📍 <?php echo htmlspecialchars($row['location']); ?></div>
        </div>
        <?php if(!empty($row['complaint_image'])): ?>
            <img src="../uploads/<?php echo $row['complaint_image']; ?>" class="thumb">
        <?php endif; ?>
    </div>
    <?php endwhile; ?>

</div>
<script>
function vote(complaintId, type, btnElement) {
    const formData = new FormData();
    formData.append('complaint_id', complaintId);
    formData.append('type', type);

    fetch('vote_action.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            document.getElementById('score-' + complaintId).innerText = data.new_score;
            location.reload(); 
        } else {
            alert('Error voting: ' + (data.message || 'Unknown error'));
        }
    })
    .catch(error => console.error('Error:', error));
}
</script>

</body>
</html>