<?php
session_start();
include("db.php");

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$message = "";

/* =========================
   POST A REVIEW
========================= */
if (isset($_POST['submit_review'])) {
    $teacher_id = $_POST['teacher_id'];
    $skill_id = $_POST['skill_id'];
    $rating = $_POST['rating'];
    $comment = $_POST['comment'];
    $reviewer_id = $user_id;

    $stmt = $conn->prepare("
        INSERT INTO Reviews (Reviewer_ID, Teacher_ID, Skill_ID, Rating, Comment)
        VALUES (?, ?, ?, ?, ?)
    ");
    $stmt->bind_param("sssis", $reviewer_id, $teacher_id, $skill_id, $rating, $comment);

    if ($stmt->execute()) {
        $message = "Review submitted successfully!";
    } else {
        $message = "Error submitting review: " . $stmt->error;
    }
}

/* =========================
   REVIEWS ABOUT ME (AS TEACHER)
========================= */
$stmt = $conn->prepare("
    SELECT 
        Reviews.Rating,
        Reviews.Comment,
        IFNULL (Users.Username, '<Deleted User>') AS reviewer_name,
        Skills.Title AS skill_title
    FROM Reviews
    LEFT JOIN Users ON Reviews.Reviewer_ID = Users.User_ID
    JOIN Skills ON Reviews.Skill_ID = Skills.Skill_ID
    WHERE Reviews.Teacher_ID = ?
    ORDER BY Reviews.Review_ID DESC
");
$stmt->bind_param("s", $user_id);
$stmt->execute();
$my_received_reviews = $stmt->get_result();

/* =========================
   REVIEWS I HAVE WRITTEN
========================= */
$stmt = $conn->prepare("
    SELECT 
        Reviews.Rating,
        Reviews.Comment,
        Users.Username AS teacher_name,
        Skills.Title AS skill_title
    FROM Reviews
    JOIN Users ON Reviews.Teacher_ID = Users.User_ID
    JOIN Skills ON Reviews.Skill_ID = Skills.Skill_ID
    WHERE Reviews.Reviewer_ID = ?
    ORDER BY Reviews.Review_ID DESC
");
$stmt->bind_param("s", $user_id);
$stmt->execute();
$my_written_reviews = $stmt->get_result();

/* =========================
   ACCEPTED REQUESTS TO REVIEW
========================= */
$stmt = $conn->prepare("
    SELECT 
        Requests.Teacher_ID,
        Requests.Skill_ID,
        Users.Username AS teacher_name,
        Skills.Title AS skill_title
    FROM Requests
    JOIN Users ON Requests.Teacher_ID = Users.User_ID
    JOIN Skills ON Requests.Skill_ID = Skills.Skill_ID
    WHERE Requests.Requester_ID = ?
      AND Requests.Status = 'Accepted'
");
$stmt->bind_param("s", $user_id);
$stmt->execute();
$accepted_requests = $stmt->get_result();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Reviews - Campus Skill Exchange</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>

<div class="container">
    <h1>Reviews</h1>
    <a href="dashboard.php" class="btn secondary">Back to Dashboard</a>
    
    <?php if (!empty($message)): ?>
        <p style="color:green;"><b><?php echo $message; ?></b></p>
    <?php endif; ?>

    <hr>

    <!-- =========================
         POST A REVIEW
    ========================= -->
    <div class="card">
        <h2>Post a Review</h2>
        <p>Review a teacher from one of your accepted sessions.</p>

        <?php if ($accepted_requests->num_rows == 0): ?>
            <p><i>No accepted requests found to review.</i></p>
        <?php else: ?>
            <form method="POST">
                Session to Review:<br>
                <select name="review_data" id="review_data" onchange="updateHiddenFields()" required>
                    <option value="">-- Select a Session --</option>
                    <?php while ($row = $accepted_requests->fetch_assoc()): ?>
                        <option value="<?php echo $row['Teacher_ID'] . '|' . $row['Skill_ID']; ?>">
                            <?php echo htmlspecialchars($row['teacher_name']); ?> 
                            - <?php echo htmlspecialchars($row['skill_title']); ?>
                        </option>
                    <?php endwhile; ?>
                </select>
                
                <input type="hidden" name="teacher_id" id="teacher_id">
                <input type="hidden" name="skill_id" id="skill_id">

                <script>
                function updateHiddenFields() {
                    var val = document.getElementById('review_data').value;
                    if (val) {
                        var parts = val.split('|');
                        document.getElementById('teacher_id').value = parts[0];
                        document.getElementById('skill_id').value = parts[1];
                    }
                }
                </script>
                <br><br>

                Rating (1-5):<br>
                <input type="number" name="rating" min="1" max="5" value="5" required><br><br>

                Comment:<br>
                <textarea name="comment" rows="4" style="width:100%;" placeholder="How was the session?"></textarea><br><br>

                <button type="submit" name="submit_review" class="btn primary">Submit Review</button>
            </form>
        <?php endif; ?>
    </div>

    <hr>

    <!-- =========================
         MY RECEIVED REVIEWS
    ========================= -->
    <div class="card">
        <h2>Reviews About Me</h2>
        <p>What other students say about your teaching.</p>

        <?php if ($my_received_reviews->num_rows == 0): ?>
            <p><i>You haven't received any reviews yet.</i></p>
        <?php else: ?>
            <ul>
                <?php while ($row = $my_received_reviews->fetch_assoc()): ?>
                    <li style="margin-bottom: 15px; border-bottom: 1px solid #eee; padding-bottom: 10px;">
                        <strong>Rating: <?php echo $row['Rating']; ?> / 5</strong> 
                        (for <?php echo htmlspecialchars($row['skill_title']); ?>)<br>
                        <em>"<?php echo htmlspecialchars($row['Comment']); ?>"</em><br>
                        <small>- by <?php echo htmlspecialchars($row['reviewer_name']); ?></small>
                    </li>
                <?php endwhile; ?>
            </ul>
        <?php endif; ?>
    </div>

    <hr>

    <!-- =========================
         REVIEWS I HAVE WRITTEN
    ========================= -->
    <div class="card">
        <h2>Reviews I've Written</h2>
        <p>Reviews you have submitted for others.</p>

        <?php if ($my_written_reviews->num_rows == 0): ?>
            <p><i>You haven't written any reviews yet.</i></p>
        <?php else: ?>
            <ul>
                <?php while ($row = $my_written_reviews->fetch_assoc()): ?>
                    <li style="margin-bottom: 15px; border-bottom: 1px solid #eee; padding-bottom: 10px;">
                        <strong>Rating: <?php echo $row['Rating']; ?> / 5</strong> 
                        (for <?php echo htmlspecialchars($row['teacher_name']); ?>'s <?php echo htmlspecialchars($row['skill_title']); ?>)<br>
                        <em>"<?php echo htmlspecialchars($row['Comment']); ?>"</em>
                    </li>
                <?php endwhile; ?>
            </ul>
        <?php endif; ?>
    </div>
</div>

</body>
</html>