<?php
session_start();
require '../db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'engineer') {
    header("Location: staff_login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$engineer_dept_id = $_SESSION['dept_id'];
$eng_sql = "SELECT u.name, u.nid, d.name as dept_name, d.short_code 
            FROM user u 
            LEFT JOIN department d ON u.dept_id = d.dept_id 
            WHERE u.user_id = ?";
$stmt_eng = $conn->prepare($eng_sql);
$stmt_eng->bind_param("i", $user_id);
$stmt_eng->execute();
$engineer_info = $stmt_eng->get_result()->fetch_assoc();

$sql = "SELECT c.complaint_id, cat.name as category_name, c.description, c.status, c.created_at, c.location, u.name as citizen_name
        FROM Complaint c
        JOIN Complaint_category cat ON c.cat_id = cat.category_id
        JOIN USER u ON c.user_id = u.user_id
        WHERE cat.Ddept_id = ? 
        ORDER BY c.created_at DESC";

$complaints = [];
if ($stmt = $conn->prepare($sql)) {
    $stmt->bind_param("i", $engineer_dept_id);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $complaints[] = $row;
    }
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Engineer Dashboard - Poricchonota</title>
    <style>
        body { font-family: 'Segoe UI', sans-serif; background: #f4f6f9; padding: 20px; }
        .container { max-width: 1100px; margin: 0 auto; }
        .header { background: white; padding: 25px; border-radius: 8px; display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; box-shadow: 0 2px 5px rgba(0,0,0,0.05); }
        .welcome-text h2 { margin: 0; color: #2c3e50; font-size: 24px; }
        .welcome-info { margin-top: 8px; font-size: 14px; color: #555; }
        .info-tag { background: #eef2f7; padding: 4px 10px; border-radius: 4px; color: #2c3e50; font-weight: 600; margin-right: 10px; border: 1px solid #dce4ec; }
        
        table { width: 100%; background: white; border-collapse: collapse; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 5px rgba(0,0,0,0.05); }
        th, td { padding: 15px; text-align: left; border-bottom: 1px solid #eee; }
        th { background: #f8f9fa; color: #7f8c8d; text-transform: uppercase; font-size: 13px; }
        
        .status-badge { padding: 4px 8px; border-radius: 12px; font-size: 11px; font-weight: bold; color: white; display: inline-block; }
        .Pending { background: #f39c12; }
        .In-Progress { background: #3498db; }
        .Resolved { background: #27ae60; }
        .Rejected { background: #e74c3c; }
        
        .btn-view { background: #3498db; color: white; padding: 6px 12px; border-radius: 4px; text-decoration: none; font-size: 13px; }
        .btn-view:hover { background: #2980b9; }
        .logout-btn { color: #e74c3c; text-decoration: none; font-weight: bold; border: 1px solid #e74c3c; padding: 8px 15px; border-radius: 4px; transition: 0.2s; }
        .logout-btn:hover { background: #e74c3c; color: white; }
    </style>
</head>
<body>

<div class="container">
    <div class="header">
        <div class="welcome-text">
            <h2>Welcome engineer! <?php echo htmlspecialchars($engineer_info['name']); ?> 👋</h2>
            <div class="welcome-info">
                <span class="info-tag">🆔 NID: <?php echo htmlspecialchars($engineer_info['nid']); ?></span>
                <span class="info-tag">🏢 Dept: <?php echo htmlspecialchars($engineer_info['dept_name']); ?> (ID: <?php echo $engineer_dept_id; ?>)</span>
            </div>
        </div>
        <a href="logout.php" class="logout-btn">Logout</a>
    </div>

    <?php if (count($complaints) > 0): ?>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Issue Type</th>
                    <th>Description</th>
                    <th>Citizen</th>
                    <th>Date</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($complaints as $row): 
                    $c_id = $row['complaint_id'];
                ?>
                    <tr>
                        <td>#<?php echo $c_id; ?></td>
                        <td><?php echo htmlspecialchars($row['category_name']); ?></td>
                        <td><?php echo htmlspecialchars(substr($row['description'], 0, 50)) . '...'; ?></td>
                        <td><?php echo htmlspecialchars($row['citizen_name']); ?></td>
                        <td><?php echo date("d M Y", strtotime($row['created_at'])); ?></td>
                        <td>
                            <span class="status-badge <?php echo str_replace(' ', '-', $row['status']); ?>">
                                <?php echo $row['status']; ?>
                            </span>
                        </td>
                        <td>
                            <a href="complaint_details.php?id=<?php echo $c_id; ?>" class="btn-view">Take Action</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <div style="text-align:center; margin-top:50px; color:#7f8c8d;">
            <h3>No complaints found</h3>
            <p>There are no complaints currently assigned to <strong><?php echo htmlspecialchars($engineer_info['dept_name']); ?></strong>.</p>
        </div>
    <?php endif; ?>
</div>

</body>
</html>