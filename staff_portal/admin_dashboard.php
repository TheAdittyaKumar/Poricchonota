<?php
session_start();
require '../db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: staff_login.php");
    exit();
}


$users = $conn->query("SELECT * FROM USER ORDER BY role, name");

$complaints = $conn->query("SELECT c.*, u.name as citizen_name FROM Complaint c LEFT JOIN USER u ON c.user_id = u.user_id ORDER BY c.created_at DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard</title>
    <style>
        body { font-family: 'Segoe UI', sans-serif; background: #f4f6f9; padding: 20px; }
        .container { max-width: 1200px; margin: 0 auto; }
        
        .header { background: white; padding: 20px; border-radius: 8px; display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; box-shadow: 0 2px 5px rgba(0,0,0,0.05); }
        h2 { margin: 0; color: #2c3e50; }
        .logout { color: #e74c3c; text-decoration: none; font-weight: bold; border: 1px solid #e74c3c; padding: 5px 15px; border-radius: 5px; }
        
        .section { background: white; padding: 20px; border-radius: 8px; margin-bottom: 30px; box-shadow: 0 2px 5px rgba(0,0,0,0.05); }
        h3 { border-bottom: 2px solid #eee; padding-bottom: 10px; margin-top: 0; color: #34495e; }

        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #eee; }
        th { background: #f8f9fa; color: #7f8c8d; font-size: 13px; text-transform: uppercase; }
        
        .btn-del { background: #e74c3c; color: white; border: none; padding: 5px 10px; border-radius: 4px; cursor: pointer; text-decoration: none; font-size: 12px; }
        .btn-del:hover { background: #c0392b; }
        
        .role-badge { padding: 2px 8px; border-radius: 10px; font-size: 11px; font-weight: bold; color: white; }
        .role-admin { background: #2c3e50; }
        .role-engineer { background: #27ae60; }
        .role-citizen { background: #3498db; }
    </style>
</head>
<body>

<div class="container">
    <div class="header">
        <h2>Admin Panel</h2>
        <a href="logout.php" class="logout">Logout</a>
    </div>

    <?php if(isset($_GET['msg'])) echo "<p style='color:green; background:#d4edda; padding:10px; border-radius:5px;'>".$_GET['msg']."</p>"; ?>

    <div class="section">
        <h3>Manage Users</h3>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php while($row = $users->fetch_assoc()): ?>
                <tr>
                    <td><?php echo $row['user_id']; ?></td>
                    <td><?php echo htmlspecialchars($row['name']); ?></td>
                    <td><?php echo htmlspecialchars($row['email']); ?></td>
                    <td>
                        <span class="role-badge role-<?php echo $row['role']; ?>">
                            <?php echo ucfirst($row['role']); ?>
                        </span>
                    </td>
                    <td>
                        <?php if($row['user_id'] != $_SESSION['user_id']): ?>
                            <a href="delete_item.php?type=user&id=<?php echo $row['user_id']; ?>" 
                               class="btn-del" 
                               onclick="return confirm('WARNING: Deleting this user will also delete ALL their complaints and history. Are you sure?');">
                               Delete User
                            </a>
                        <?php else: ?>
                            <span style="color:#aaa;">(You)</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>

    <div class="section">
        <h3>Manage Complaints</h3>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Citizen</th>
                    <th>Description</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php while($c = $complaints->fetch_assoc()): 
                    $c_id = $c['Complaint_id'] ?? $c['complaint_id']; 
                ?>
                <tr>
                    <td>#<?php echo $c_id; ?></td>
                    <td><?php echo htmlspecialchars($c['citizen_name']); ?></td>
                    <td><?php echo htmlspecialchars(substr($c['description'], 0, 50)) . '...'; ?></td>
                    <td><strong><?php echo $c['status']; ?></strong></td>
                    <td>
                        <a href="delete_item.php?type=complaint&id=<?php echo $c_id; ?>" 
                           class="btn-del" 
                           onclick="return confirm('Are you sure you want to delete this complaint permanently?');">
                           Delete Complaint
                        </a>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>

</div>

</body>
</html>