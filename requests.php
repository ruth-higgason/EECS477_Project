<?php include("db.php"); ?>

<!DOCTYPE html>
<html>
<head>
    <title>Requests</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>

<header><h1>Requests</h1></header>

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
    <th>Request ID</th>
    <th>Requester</th>
    <th>Teacher</th>
    <th>Skill</th>
    <th>Status</th>
    <th>Date</th>
</tr>

<?php
$sql = "
SELECT 
    r.Request_ID,
    u1.Name AS Requester,
    u2.Name AS Teacher,
    s.Title AS Skill,
    r.Status,
    r.Date
FROM Requests r
JOIN Users u1 ON r.Requester_ID = u1.User_ID
JOIN Users u2 ON r.Teacher_ID = u2.User_ID
JOIN Skills s ON r.Skill_ID = s.Skill_ID
";

$result = $conn->query($sql);

while ($row = $result->fetch_assoc()) {
    echo "<tr>
        <td>{$row['Request_ID']}</td>
        <td>{$row['Requester']}</td>
        <td>{$row['Teacher']}</td>
        <td>{$row['Skill']}</td>
        <td>{$row['Status']}</td>
        <td>{$row['Date']}</td>
    </tr>";
}
?>

</table>

</div>
</body>
</html>
