<?php
session_start();
require '../db.php';
header('Content-Type: application/json'); //php return me json not html
//community feed e we fetch vote_action so server expects JSON response
// check if user logged in, complaint id ki post diye sent ar vote type 
if (!isset($_SESSION['user_id']) || !isset($_POST['complaint_id']) || !isset($_POST['type'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
    exit();
}
$user_id = $_SESSION['user_id'];
$complaint_id = intval($_POST['complaint_id']);
$type = $_POST['type'];
// Allow ONLY 'up' or 'down' like reddit style
if ($type !== 'up' && $type !== 'down') {
    echo json_encode(['success' => false, 'message' => 'Invalid vote type']);
    exit();
}
$vote_value = ($type === 'up') ? 1 : -1;
// does this user already have a vote record on this complaint?
$check_sql = "SELECT vote_type FROM Complaint_Votes WHERE user_id = ? AND complaint_id = ?"; //
$stmt = $conn->prepare($check_sql);
$stmt->bind_param("ii", $user_id, $complaint_id);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows > 0) {
    // User has voted before
    $row = $result->fetch_assoc();
    $existing_vote = $row['vote_type']; //existing vote either 1 nahole -1
    if ($existing_vote == $vote_value) {
        // User clicked same button -> Remove vote (removes the row from the database).
        $del = $conn->prepare("DELETE FROM Complaint_Votes WHERE user_id = ? AND complaint_id = ?");
        $del->bind_param("ii", $user_id, $complaint_id);
        $del->execute();
    } else {
        // Clicked opposite -> Update vote (1+ but akhon clicked down so -1)
        $upd = $conn->prepare("UPDATE Complaint_Votes SET vote_type = ? WHERE user_id = ? AND complaint_id = ?");
        $upd->bind_param("iii", $vote_value, $user_id, $complaint_id);
        $upd->execute();
    }
} else {
    // new vote (user never voted before)
    $ins = $conn->prepare("INSERT INTO Complaint_Votes (user_id, complaint_id, vote_type) VALUES (?, ?, ?)");
    $ins->bind_param("iii", $user_id, $complaint_id, $vote_value);
    $ins->execute();
}
// sum all vote type for this complaint, judi 0 vote thake then null ar coalesce aitake 0 kore dibe
$sum_sql = "SELECT COALESCE(SUM(vote_type), 0) as total FROM Complaint_Votes WHERE complaint_id = ?";
$stmt_sum = $conn->prepare($sum_sql);
$stmt_sum->bind_param("i", $complaint_id);
$stmt_sum->execute();
$total = $stmt_sum->get_result()->fetch_assoc()['total'];
$final_score = $total; 
$upd_main = $conn->prepare("UPDATE Complaint SET vote_count = ? WHERE complaint_id = ?"); // updating complaints table vote_count
$upd_main->bind_param("ii", $final_score, $complaint_id);
$upd_main->execute();
echo json_encode(['success' => true, 'new_score' => $final_score]);
?>
