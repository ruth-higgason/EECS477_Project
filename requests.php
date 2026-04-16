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
   PENDING REQUESTS
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
   ACCEPTED REQUESTS
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
?>

<h1>My Requests</h1>

<hr>

<!-- =========================
     PENDING REQUESTS
========================= -->
<h2>Pending Requests</h2>

<?php if ($pending_requests->num_rows == 0): ?>
    <p>No pending requests.</p>
<?php else: ?>
    <ul>
        <?php while ($row = $pending_requests->fetch_assoc()): ?>
            <li>
                <b><?php echo htmlspecialchars($row['requester_name']); ?></b>
                requested help with
                <b><?php echo htmlspecialchars($row['skill_title']); ?></b>
                on <?php echo htmlspecialchars($row['Date']); ?>

                <form method="POST" style="display:inline;">
                    <input type="hidden" name="request_id" value="<?php echo $row['Request_ID']; ?>">
                    <button type="submit" name="accept_request">Accept</button>
                </form>
            </li>
        <?php endwhile; ?>
    </ul>
<?php endif; ?>

<hr>

<!-- =========================
     ACCEPTED REQUESTS
========================= -->
<h2>Accepted Requests</h2>

<?php if ($accepted_requests->num_rows == 0): ?>
    <p>No accepted requests.</p>
<?php else: ?>
    <ul>
        <?php while ($row = $accepted_requests->fetch_assoc()): ?>
            <li>
                <b><?php echo htmlspecialchars($row['requester_name']); ?></b>
                - <?php echo htmlspecialchars($row['skill_title']); ?>
                (<?php echo htmlspecialchars($row['Date']); ?>)
            </li>
        <?php endwhile; ?>
    </ul>
<?php endif; ?>

<br>

<a href="dashboard.php">Back to Dashboard</a>