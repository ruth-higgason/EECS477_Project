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
    <title>Dashboard</title>
</head>
<body>

<h1>Dashboard</h1>

<p>
    Welcome, 
    <?php echo htmlspecialchars($user['Name']); ?> 
    (<?php echo htmlspecialchars($user['Username']); ?>)
</p>

<hr>

<h3>Account</h3>
<a href="profile.php">View / Edit Profile</a>

<hr>

<h3>Requests</h3>
<a href="requests.php">View My Requests</a><br>
<a href="skills.php">Browse Skills</a>

<hr>

<h3>Reviews</h3>
<a href="reviews.php">My Reviews</a>

<hr>

<a href="logout.php">Logout</a>

</body>
</html>