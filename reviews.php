<?php include("db.php"); ?>

<!DOCTYPE html>
<html>
<head>
    <title>Reviews</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>

<header><h1>Reviews</h1></header>

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
    <th>Review ID</th>
    <th>Reviewer</th>
    <th>Teacher</th>
    <th>Skill</th>
    <th>Rating</th>
    <th>Comment</th>
</tr>

<?php
$sql = "
SELECT 
    v.Review_ID,
    u1.Name AS Reviewer,
    u2.Name AS Teacher,
    s.Title AS Skill,
    v.Rating,
    v.Comment
FROM Reviews v
JOIN Users u1 ON v.Reviewer_ID = u1.User_ID
JOIN Users u2 ON v.Teacher_ID = u2.User_ID
JOIN Skills s ON v.Skill_ID = s.Skill_ID
";

$result = $conn->query($sql);

while ($row = $result->fetch_assoc()) {
    echo "<tr>
        <td>{$row['Review_ID']}</td>
        <td>{$row['Reviewer']}</td>
        <td>{$row['Teacher']}</td>
        <td>{$row['Skill']}</td>
        <td>{$row['Rating']}</td>
        <td>{$row['Comment']}</td>
    </tr>";
}
?>

</table>

</div>
</body>
</html>
