<?php
session_start();
require '../db.php'; //db.php missing thakle stop the page
if (!isset($_SESSION['user_id'])) {
    header("Location: citizen_login.php"); //user_id session na thakle login page e niye jao
    exit();
}

$user_id = $_SESSION['user_id']; //save the user_id in the var
$user_sql = "SELECT name, nid FROM USER WHERE user_id = ?"; //give me the nid and name of the user
$stmt = $conn->prepare($user_sql);
$stmt->bind_param("i", $user_id); //essentially fills ? with user_id var, ex: select name,nid from user where user_id=1
$stmt->execute();
$user_result = $stmt->get_result(); //EX: Adittya Kumar, nid 111111111111
$user = $user_result->fetch_assoc(); //row fetch

date_default_timezone_set('Asia/Dhaka'); 
$hour = date('H'); //24 hour format
if ($hour < 12) {
    $greeting = "Good Morning";
} elseif ($hour < 18) {
    $greeting = "Good Afternoon";
} else {
    $greeting = "Good Evening";
}
// bring me the complaints of the user by connecting complaint and category table and give complaints made by me only and latests ageh
$sql = "SELECT c.complaint_id, c.description, c.created_at, c.status, cat.name as category_name 
        FROM Complaint c
        JOIN Complaint_category cat ON c.cat_id = cat.category_id
        WHERE c.user_id = ? 
        ORDER BY c.created_at DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id); //attach the php vars to ? placeholders 
$stmt->execute();
$complaints_result = $stmt->get_result(); //bring the rows of my complaints
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Citizen Dashboard - Poricchonota</title>
    <style>
        body { font-family: 'Segoe UI', sans-serif; background-color: #f4f6f9; margin: 0; padding: 0; }
        .header { background-color: #fff; padding: 15px 30px; display: flex; justify-content: space-between; align-items: center; box-shadow: 0 2px 5px rgba(0,0,0,0.05); }
        .logo { font-size: 24px; font-weight: bold; color: #2c3e50; }
        .nav-links { display: flex; align-items: center; gap: 20px; }
        .nav-links a { text-decoration: none; font-weight: 600; font-size: 15px; }
        .link-community { color: #8e44ad; display: flex; align-items: center; gap: 5px; }
        .link-logout { color: #e74c3c; border: 1px solid #e74c3c; padding: 5px 15px; border-radius: 20px; transition: 0.2s; }
        .link-logout:hover { background: #e74c3c; color: white; }
        .container { max-width: 1000px; margin: 30px auto; padding: 0 20px; }
        .welcome-card {
            background: linear-gradient(135deg, #1abc9c, #16a085); 
            color: white;
            padding: 30px;
            border-radius: 15px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            box-shadow: 0 10px 20px rgba(26, 188, 156, 0.2);
            position: relative;
            overflow: hidden;
        }
        .welcome-card::before {
            content: '';
            position: absolute;
            top: -50px;
            right: -50px;
            width: 150px;
            height: 150px;
            background: rgba(255,255,255,0.1);
            border-radius: 50%;
        }
        .user-info h1 { margin: 0; font-size: 26px; font-weight: 700; }
        .user-info p { margin: 5px 0 15px 0; font-size: 16px; opacity: 0.9; }
        .nid-badge {
            background-color: rgba(255, 255, 255, 0.2);
            padding: 5px 12px;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 500;
            letter-spacing: 0.5px;
        }
        .card-actions {
            display: flex;
            gap: 15px;
            align-items: center;
            z-index: 2;
        }
        .btn-profile {
            background: rgba(255, 255, 255, 0.2);
            color: white;
            padding: 10px 20px;
            text-decoration: none;
            border-radius: 25px;
            font-weight: 600;
            font-size: 14px;
            border: 1px solid rgba(255,255,255,0.4);
            transition: 0.3s;
        }
        .btn-profile:hover { background: rgba(255,255,255,0.3); }
        .btn-report-large {
            background-color: white;
            color: #16a085;
            padding: 12px 25px;
            text-decoration: none;
            border-radius: 25px;
            font-weight: bold;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            transition: 0.3s;
        }
        .btn-report-large:hover { transform: translateY(-2px); box-shadow: 0 6px 20px rgba(0,0,0,0.15); }
        .section-title { font-size: 18px; color: #34495e; font-weight: 700; margin-bottom: 15px; border-left: 5px solid #1abc9c; padding-left: 10px; }
        .table-container { background: white; border-radius: 10px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); overflow: hidden; }
        table { width: 100%; border-collapse: collapse; }
        th { background-color: #f8f9fa; color: #7f8c8d; font-weight: 600; text-align: left; padding: 15px 20px; border-bottom: 2px solid #eee; text-transform: uppercase; font-size: 12px; }
        td { padding: 15px 20px; border-bottom: 1px solid #eee; color: #333; vertical-align: middle; }
        tr:last-child td { border-bottom: none; }
        tr:hover { background-color: #fafafa; }

        .status-badge { padding: 5px 12px; border-radius: 15px; font-size: 12px; font-weight: bold; color: white; display: inline-block; min-width: 80px; text-align: center; }
        .Pending { background-color: #f39c12; }
        .In-Progress { background-color: #3498db; }
        .Resolved { background-color: #27ae60; }
        .Rejected { background-color: #e74c3c; }
        .view-link { color: #3498db; text-decoration: none; font-weight: bold; font-size: 14px; }
        .view-link:hover { text-decoration: underline; }
        .empty-state { padding: 50px; text-align: center; color: #95a5a6; }
    </style>
</head>
<body>
    <div class="header">
        <div class="logo">Poricchonota</div>
        <div class="nav-links">
            <a href="community_feed.php" class="link-community">🌏 Community Feed</a>
            <a href="logout.php" class="link-logout">Logout</a>
        </div>
    </div>

    <div class="container">
        
        <div class="welcome-card">
            <div class="user-info">
                <h1><?php echo $greeting . ", " . htmlspecialchars($user['name']); ?></h1>
                <p>Welcome to your citizen dashboard.</p>
                <span class="nid-badge">NID: <?php echo htmlspecialchars($user['nid']); ?></span>
            </div>
            <div class="card-actions">
                <a href="citizen_profile.php" class="btn-profile">View Profile</a> <!--clickable button to view profile-->
                <a href="submit_complaint.php" class="btn-report-large">+ Report New Issue</a>
            </div>
        </div>
        <div class="section-title">Your Recent Complaints</div>
        <div class="table-container">
            <?php if ($complaints_result->num_rows > 0): ?> <!--if there are complaints then show me the table otherwise show no complaints found-->
                <table>
                    <thead>
                        <tr>
                            <th>Complaint ID</th>
                            <th>Category</th>
                            <th width="40%">Description</th>
                            <th>Date</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($row = $complaints_result->fetch_assoc()): ?>
                        <tr>
                            <td style="font-weight:bold;">#<?php echo $row['complaint_id']; ?></td>
                            <td><?php echo htmlspecialchars($row['category_name']); ?></td>
                            <td>
                                <?php 
                                    $desc = htmlspecialchars($row['description']);
                                    echo (strlen($desc) > 50) ? substr($desc, 0, 50) . '...' : $desc;
                                ?>
                            </td>
                            <td><?php echo date("d M Y", strtotime($row['created_at'])); ?></td>
                            <td>
                                <span class="status-badge <?php echo str_replace(' ', '-', $row['status']); ?>">
                                    <?php echo $row['status']; ?>
                                </span>
                            </td>
                            <td>
                                <a href="view_complaint.php?id=<?php echo $row['complaint_id']; ?>" class="view-link">View Details &rarr;</a>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="empty-state">
                    <h3>No complaints found</h3>
                    <p>You haven't reported any issues yet.</p>
                </div>
            <?php endif; ?>
        </div>

    </div>

</body>
</html>