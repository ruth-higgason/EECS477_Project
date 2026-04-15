<?php 
include("header.php"); 
include("db.php");

$result = $conn->query("SELECT * FROM Requests");
?>

<div class="container">
    <h2>My Requests</h2>

    <?php while($row = $result->fetch_assoc()): ?>
        <div class="card">
            <p><strong>Status:</strong> <?php echo $row['Status']; ?></p>
            <p><strong>Date:</strong> <?php echo $row['Date']; ?></p>

            <?php if($row['Status'] == 'Completed'): ?>
                <a href="review.php?skill=<?php echo $row['Skill_ID']; ?>" class="btn">Leave Review</a>
            <?php endif; ?>
        </div>
    <?php endwhile; ?>
</div>

</body>
</html>