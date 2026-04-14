<?php include("db.php"); ?>
<!DOCTYPE html>
<html>
<head>
    <title>Users</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>

<header><h1>Users</h1></header>

<nav>
    <a href="index.php">Home</a>
    <a href="users.php">Users</a>
    <a href="skills.php">Skills</a>
    <a href="requests.php">Requests</a>
    <a href="reviews.php">Reviews</a>
</nav>

<div class="container">

<table>
<tr>
    <th>User ID</th>
    <th>Username</th>
    <th>Name</th>
    <th>Email</th>
    <th>Major</th>
</tr>

<?php
$sql = "SELECT * FROM Users";
$result = $conn->query($sql);

while ($row = $result->fetch_assoc()) {
    echo "<tr>
        <td>{$row['User_ID']}</td>
        <td>{$row['Username']}</td>
        <td>{$row['Name']}</td>
        <td>{$row['Email']}</td>
        <td>{$row['Major']}</td>
    </tr>";
}
?>

</table>

</div>
</body>
</html>
