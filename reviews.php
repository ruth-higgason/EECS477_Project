<?php 
include("header.php"); 
include("db.php");

if($_SERVER["REQUEST_METHOD"] == "POST") {
    $rating = $_POST['rating'];
    $comment = $_POST['comment'];

    $conn->query("INSERT INTO Reviews (Rating, Comment) VALUES ('$rating', '$comment')");
    echo "<p>Review submitted!</p>";
}
?>

<div class="container">
    <h2>Leave a Review</h2>

    <form method="POST">
        <label>Rating (1-5)</label>
        <input type="number" name="rating" min="1" max="5" required>

        <label>Comment</label>
        <textarea name="comment"></textarea>

        <button class="btn primary">Submit Review</button>
    </form>
</div>

</body>
</html>