<?php
session_start();
include("db.php");

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$stmt = $conn->prepare("SELECT Name, Email, Major FROM Users WHERE User_ID = ?");
$stmt->bind_param("s", $_SESSION['user_id']);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
?>

<h1>Dashboard</h1>

<p>Welcome, <?php echo htmlspecialchars($user['Name']); ?></p>
<p>Email: <?php echo htmlspecialchars($user['Email']); ?></p>
<p>Major: <?php echo htmlspecialchars($user['Major']); ?></p>

<a href="logout.php">Logout</a>