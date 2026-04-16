<?php
session_start();
include("db.php");

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$user_id = $_SESSION['user_id'];

/* =========================
   ACCEPT REQUEST
========================= */
if (isset($_POST['accept_request'])) {
    $request_id = $_POST['request_id'];

    $stmt = $conn->prepare("
        UPDATE Requests 
        SET Status='Accepted'
        WHERE Request_ID=? AND Teacher_ID=?
    ");
    $stmt->bind_param("ss", $request_id, $user_id);
    $stmt->execute();
}

/* =========================
   REJECT REQUEST
========================= */
if (isset($_POST['reject_request'])) {
    $request_id = $_POST['request_id'];

    $stmt = $conn->prepare("
        UPDATE Requests 
        SET Status='Rejected'
        WHERE Request_ID=? AND Teacher_ID=?
    ");
    $stmt->bind_param("ss", $request_id, $user_id);
    $stmt->execute();
}

/* =========================
   DELETE SENT REQUEST
========================= */
if (isset($_POST['delete_request'])) {
    $request_id = $_POST['request_id'];

    // Only allow deleting PENDING requests sent by the user
    $stmt = $conn->prepare("
        DELETE FROM Requests 
        WHERE Request_ID=? AND Requester_ID=? AND Status='Pending'
    ");
    $stmt->bind_param("ss", $request_id, $user_id);
    $stmt->execute();
}

/* =========================
   PENDING REQUESTS (INCOMING)
========================= */
$stmt = $conn->prepare("
    SELECT 
        Requests.Request_ID,
        Requests.Date,
        Users.Username AS requester_name,
        Skills.Title AS skill_title
    FROM Requests
    JOIN Users ON Requests.Requester_ID = Users.User_ID
    JOIN Skills ON Requests.Skill_ID = Skills.Skill_ID
    WHERE Requests.Teacher_ID = ?
      AND Requests.Status = 'Pending'
    ORDER BY Requests.Date DESC
");
$stmt->bind_param("s", $user_id);
$stmt->execute();
$pending_requests = $stmt->get_result();

/* =========================
   ACCEPTED REQUESTS (INCOMING)
========================= */
$stmt = $conn->prepare("
    SELECT 
        Requests.Request_ID,
        Requests.Date,
        Users.Username AS requester_name,
        Skills.Title AS skill_title
    FROM Requests
    JOIN Users ON Requests.Requester_ID = Users.User_ID
    JOIN Skills ON Requests.Skill_ID = Skills.Skill_ID
    WHERE Requests.Teacher_ID = ?
      AND Requests.Status = 'Accepted'
    ORDER BY Requests.Date DESC
");
$stmt->bind_param("s", $user_id);
$stmt->execute();
$accepted_requests = $stmt->get_result();

/* =========================
   SENT REQUESTS
========================= */
$stmt = $conn->prepare("
    SELECT 
        Requests.Request_ID,
        Requests.Date,
        Requests.Status,
        Users.Username AS teacher_name,
        Skills.Title AS skill_title
    FROM Requests
    JOIN Users ON Requests.Teacher_ID = Users.User_ID
    JOIN Skills ON Requests.Skill_ID = Skills.Skill_ID
    WHERE Requests.Requester_ID = ?
    ORDER BY Requests.Date DESC
");
$stmt->bind_param("s", $user_id);
$stmt->execute();
$sent_requests = $stmt->get_result();
?>

<!DOCTYPE html>
<html>
<head>
    <title>My Requests - Campus Skill Exchange</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>

<div class="container">
    <h1>My Requests</h1>
    <a href="dashboard.php">Back to Dashboard</a>
    <hr>

    <!-- =========================
         SENT REQUESTS
    ========================= -->
    <div class="card">
        <h2>Requests I've Sent</h2>

        <?php if ($sent_requests->num_rows == 0): ?>
            <p>You haven't sent any requests yet.</p>
        <?php else: ?>
            <ul>
                <?php while ($row = $sent_requests->fetch_assoc()): ?>
                    <li style="margin-bottom: 10px;">
                        Requested <b><?php echo htmlspecialchars($row['skill_title']); ?></b>
                        from <b><?php echo htmlspecialchars($row['teacher_name']); ?></b>
                        on <?php echo htmlspecialchars($row['Date']); ?>
                        - <i>Status: <?php echo htmlspecialchars($row['Status']); ?></i>

                        <?php if ($row['Status'] == 'Pending'): ?>
                            <form method="POST" style="display:inline; margin-left: 10px;">
                                <input type="hidden" name="request_id" value="<?php echo $row['Request_ID']; ?>">
                                <button type="submit" name="delete_request" style="color: red; background: none; border: 1px solid red; cursor: pointer; border-radius: 3px; padding: 2px 5px;">Delete</button>
                            </form>
                        <?php endif; ?>
                    </li>
                <?php endwhile; ?>
            </ul>
        <?php endif; ?>
    </div>

    <hr>

    <!-- =========================
         PENDING REQUESTS (INCOMING)
    ========================= -->
    <div class="card">
        <h2>Incoming Pending Requests</h2>

        <?php if ($pending_requests->num_rows == 0): ?>
            <p>No pending requests.</p>
        <?php else: ?>
            <ul>
                <?php while ($row = $pending_requests->fetch_assoc()): ?>
                    <li style="margin-bottom: 10px;">
                        <b><?php echo htmlspecialchars($row['requester_name']); ?></b>
                        requested help with
                        <b><?php echo htmlspecialchars($row['skill_title']); ?></b>
                        on <?php echo htmlspecialchars($row['Date']); ?>

                        <div style="margin-top: 5px;">
                            <form method="POST" style="display:inline;">
                                <input type="hidden" name="request_id" value="<?php echo $row['Request_ID']; ?>">
                                <button type="submit" name="accept_request" class="btn primary" style="padding: 2px 10px;">Accept</button>
                            </form>
                            <form method="POST" style="display:inline; margin-left: 5px;">
                                <input type="hidden" name="request_id" value="<?php echo $row['Request_ID']; ?>">
                                <button type="submit" name="reject_request" style="color: darkred; border: 1px solid darkred; background: white; padding: 2px 10px; cursor: pointer; border-radius: 4px;">Reject</button>
                            </form>
                        </div>
                    </li>
                <?php endwhile; ?>
            </ul>
        <?php endif; ?>
    </div>

    <hr>

    <!-- =========================
         ACCEPTED REQUESTS (INCOMING)
    ========================= -->
    <div class="card">
        <h2>Incoming Accepted Requests</h2>

        <?php if ($accepted_requests->num_rows == 0): ?>
            <p>No accepted requests.</p>
        <?php else: ?>
            <ul>
                <?php while ($row = $accepted_requests->fetch_assoc()): ?>
                    <li style="margin-bottom: 5px;">
                        <b><?php echo htmlspecialchars($row['requester_name']); ?></b>
                        - <?php echo htmlspecialchars($row['skill_title']); ?>
                        (<?php echo htmlspecialchars($row['Date']); ?>)
                    </li>
                <?php endwhile; ?>
            </ul>
        <?php endif; ?>
    </div>
</div>

</body>
</html>