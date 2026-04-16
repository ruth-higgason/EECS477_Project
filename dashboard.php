<?php
session_start();
include("db.php");

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$user_id = $_SESSION['user_id'];

/* Get user info */
$stmt = $conn->prepare("SELECT Username, Name FROM Users WHERE User_ID=?");
$stmt->bind_param("s", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Dashboard - Campus Skill Exchange</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>

<div class="container">
    <div class="center">
        <h1>Welcome, <?php echo htmlspecialchars($user['Name']); ?>!</h1>
        <p>Logged in as: <?php echo htmlspecialchars($user['Username']); ?></p>
    </div>

    <div style="display:grid; grid-template-columns: 1fr 1fr; gap:20px;">
        
        <div class="card">
            <h3>Skills & Learning</h3>
            <p>Ready to learn something new or share your expertise?</p>
            <a href="skills.php" class="btn primary">Browse All Skills</a>
        </div>

        <div class="card">
            <h3>Requests</h3>
            <p>Manage your incoming and outgoing session requests.</p>
            <a href="requests.php" class="btn primary">My Requests</a>
        </div>

        <div class="card">
            <h3>Account</h3>
            <p>Update your personal information and password.</p>
            <a href="profile.php" class="btn primary">View Profile</a>
        </div>

        <div class="card">
            <h3>Feedback</h3>
            <p>See what others are saying about you or leave a review.</p>
            <a href="reviews.php" class="btn primary">Reviews</a>
        </div>

    </div>

    <hr>
    <div class="center">
        <a href="logout.php" class="btn secondary">Logout</a>
    </div>
</div>

</body>
</html>