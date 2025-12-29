<?php
session_start();
require '../db.php';
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    die("Access Denied.");
}
$id = intval($_GET['id']);
$type = $_GET['type']; 
if ($type === 'complaint') {
    $sql = "SELECT complaint_image, resolution_image FROM Complaint WHERE complaint_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    if ($row) {
        if (!empty($row['complaint_image']) && file_exists("../uploads/" . $row['complaint_image'])) {
            unlink("../uploads/" . $row['complaint_image']);
        }
        if (!empty($row['resolution_image']) && file_exists("../uploads/" . $row['resolution_image'])) {
            unlink("../uploads/" . $row['resolution_image']);
        }
    }
    $del = $conn->prepare("DELETE FROM Complaint WHERE complaint_id = ?");
    $del->bind_param("i", $id);
    if ($del->execute()) {
        header("Location: admin_dashboard.php?msg=Complaint deleted successfully");
    } else {
        echo "Error deleting complaint: " . $conn->error;
    }
} elseif ($type === 'user') {
    if ($id == $_SESSION['user_id']) {
        die("You cannot delete your own account.");
    }

    $del = $conn->prepare("DELETE FROM USER WHERE user_id = ?");
    $del->bind_param("i", $id);
    
    if ($del->execute()) {
        header("Location: admin_dashboard.php?msg=User deleted successfully");
    } else {
        echo "Error deleting user: " . $conn->error;
    }
} else {
    echo "Invalid Request";
}
?>