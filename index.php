<?php include("db.php"); ?>
<!DOCTYPE html>
<html>
<head>
    <title>Campus Skill Exchange</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>

<header>
    <h1>Campus Skill Exchange</h1>
</header>

<nav>
    <a href="index.php">Home</a>
    <a href="users.php">Users</a>
    <a href="skills.php">Skills</a>
    <a href="requests.php">Requests</a>
    <a href="reviews.php">Reviews</a>
</nav>

<div class="container">
    <h2>Dashboard</h2>

    <?php
    $u = $conn->query("SELECT COUNT(*) AS c FROM Users")->fetch_assoc();
    $s = $conn->query("SELECT COUNT(*) AS c FROM Skills")->fetch_assoc();
    $r = $conn->query("SELECT COUNT(*) AS c FROM Requests")->fetch_assoc();
    $v = $conn->query("SELECT COUNT(*) AS c FROM Reviews")->fetch_assoc();
    ?>

    <p><b>Users:</b> <?= $u['c'] ?></p>
    <p><b>Skills:</b> <?= $s['c'] ?></p>
    <p><b>Requests:</b> <?= $r['c'] ?></p>
    <p><b>Reviews:</b> <?= $v['c'] ?></p>
</div>

</body>
</html>
