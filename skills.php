<?php include("db.php"); ?>

<!DOCTYPE html>
<html>
<head>
    <title>Skills</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>

<header><h1>Skills</h1></header>

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
    <th>Skill ID</th>
    <th>Title</th>
    <th>Description</th>
    <th>Category</th>
</tr>

<?php
$sql = "SELECT * FROM Skills";
$result = $conn->query($sql);

while ($row = $result->fetch_assoc()) {
    echo "<tr>
        <td>{$row['Skill_ID']}</td>
        <td>{$row['Title']}</td>
        <td>{$row['Description']}</td>
        <td>{$row['Category']}</td>
    </tr>";
}
?>

</table>

</div>
</body>
</html>
