<?php 
include("header.php"); 
include("db.php");

$result = $conn->query("SELECT * FROM Skills");
?>

<div class="container">
    <h2>Browse Skills</h2>

    <?php while($row = $result->fetch_assoc()): ?>
        <div class="card">
            <h3><?php echo $row['Title']; ?></h3>
            <p><?php echo $row['Description']; ?></p>
            <p><strong>Category:</strong> <?php echo $row['Category']; ?></p>

            <form method="POST" action="requests.php">
                <input type="hidden" name="skill_id" value="<?php echo $row['Skill_ID']; ?>">
                <button class="btn primary">Request Session</button>
            </form>
        </div>
    <?php endwhile; ?>
</div>

</body>
</html>