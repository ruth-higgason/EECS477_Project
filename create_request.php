<?php
session_start();
include("db.php");

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$requester_id = $_SESSION['user_id'];

/* =========================
   VALIDATE INPUT
========================= */
if (!isset($_POST['skill_id']) || !isset($_POST['teacher_id'])) {
    die("Invalid request: missing data.");
}

$skill_id = $_POST['skill_id'];
$teacher_id = $_POST['teacher_id'];

/* =========================
   PREVENT SELF REQUEST
========================= */
if ($requester_id == $teacher_id) {
    die("You cannot request your own skill.");
}

/* =========================
   GET SKILL INFO
========================= */
$stmt = $conn->prepare("
    SELECT Title, Description, Category
    FROM Skills
    WHERE Skill_ID=?
");
$stmt->bind_param("s", $skill_id);
$stmt->execute();
$skill = $stmt->get_result()->fetch_assoc();

/* =========================
   GET TEACHER INFO
========================= */
$stmt = $conn->prepare("
    SELECT Username, Name, Major
    FROM Users
    WHERE User_ID=?
");
$stmt->bind_param("s", $teacher_id);
$stmt->execute();
$teacher = $stmt->get_result()->fetch_assoc();

/* =========================
   AVERAGE RATING
========================= */
$stmt = $conn->prepare("
    SELECT AVG(Rating) AS avg_rating
    FROM Reviews
    WHERE Teacher_ID=?
");
$stmt->bind_param("s", $teacher_id);
$stmt->execute();
$rating = $stmt->get_result()->fetch_assoc();

/* =========================
   REVIEWS
========================= */
$stmt = $conn->prepare("
    SELECT Reviews.Rating, Reviews.Comment, Users.Username
    FROM Reviews
    JOIN Users ON Reviews.Reviewer_ID = Users.User_ID
    WHERE Reviews.Teacher_ID=?
    ORDER BY Reviews.Review_ID DESC
");
$stmt->bind_param("s", $teacher_id);
$stmt->execute();
$reviews = $stmt->get_result();

/* =========================
   OTHER SKILLS
========================= */
$stmt = $conn->prepare("
    SELECT Skills.Title
    FROM Skills
    JOIN UserSkills ON Skills.Skill_ID = UserSkills.Skill_ID
    WHERE UserSkills.User_ID=?
");
$stmt->bind_param("s", $teacher_id);
$stmt->execute();
$other_skills = $stmt->get_result();

/* =========================
   PREVENT DUPLICATE REQUEST
========================= */
$check_stmt = $conn->prepare("
    SELECT Request_ID FROM Requests 
    WHERE Requester_ID=? AND Teacher_ID=? AND Skill_ID=?
");
$check_stmt->bind_param("sss", $requester_id, $teacher_id, $skill_id);
$check_stmt->execute();
$existing_request = $check_stmt->get_result()->fetch_assoc();

/* =========================
   FINAL SUBMISSION
========================= */
$success = false;
$error = "";

if ($existing_request) {
    $error = "You have already sent a request for this skill to this teacher.";
} elseif (isset($_POST['confirm_request'])) {
    $stmt = $conn->prepare("
        INSERT INTO Requests (Requester_ID, Teacher_ID, Skill_ID, Status, Date)
        VALUES (?, ?, ?, 'Pending', CURDATE())
    ");
    $stmt->bind_param("sss", $requester_id, $teacher_id, $skill_id);

    if ($stmt->execute()) {
        $success = true;
    } else {
        $error = "Error: " . $stmt->error;
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Confirm Request - Campus Skill Exchange</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>

<div class="container">
    <?php if ($success): ?>
        <h2>Request sent successfully!</h2>
        <p>You have successfully requested help with <b><?php echo htmlspecialchars($skill['Title']); ?></b> from <b><?php echo htmlspecialchars($teacher['Name']); ?></b>.</p>
        <p>
            <a href="requests.php">View My Requests</a> | 
            <a href="skills.php">Back to Skills</a>
        </p>
    <?php else: ?>
        <?php if (!empty($error)): ?>
            <p style="color:red;"><b><?php echo htmlspecialchars($error); ?></b></p>
            <?php if ($existing_request): ?>
                <p><a href="skills.php">Back to Browse</a> | <a href="requests.php">View My Requests</a></p>
            <?php endif; ?>
        <?php endif; ?>

        <?php if (!$existing_request): ?>
            <h1>Confirm Request</h1>
            
            <p>
                <b>Skill:</b> <?php echo htmlspecialchars($skill['Title']); ?><br>
                <b>Category:</b> <?php echo htmlspecialchars($skill['Category']); ?><br>
                <b>Teacher:</b> <?php echo htmlspecialchars($teacher['Name']); ?> (<?php echo htmlspecialchars($teacher['Username']); ?>)
            </p>

            <a href="skills.php" class="btn secondary">Back to Browse</a>

            <hr>

            <div class="card">
                <h3>Skill Description</h3>
                <p><?php echo htmlspecialchars($skill['Description']); ?></p>
            </div>

            <div class="card">
                <h3>Provider Info</h3>
                <p>Major: <?php echo htmlspecialchars($teacher['Major']); ?></p>
                <p>Average Rating: <?php echo $rating['avg_rating'] ? round($rating['avg_rating'], 2) . " / 5" : "No ratings yet"; ?></p>
            </div>

            <div class="card">
                <h3>Other Skills They Have</h3>
                <ul>
                <?php while ($row = $other_skills->fetch_assoc()): ?>
                    <li><?php echo htmlspecialchars($row['Title']); ?></li>
                <?php endwhile; ?>
                </ul>
            </div>

            <div class="card">
                <h3>Recent Reviews</h3>
                <?php if ($reviews->num_rows == 0): ?>
                    <p>No reviews yet.</p>
                <?php else: ?>
                    <ul>
                    <?php while ($row = $reviews->fetch_assoc()): ?>
                        <li>
                            <b><?php echo $row['Rating']; ?>/5</b> - 
                            <?php echo htmlspecialchars($row['Comment']); ?> 
                            (by <?php echo htmlspecialchars($row['Username']); ?>)
                        </li>
                    <?php endwhile; ?>
                    </ul>
                <?php endif; ?>
            </div>

            <hr>

            <form method="POST">
                <input type="hidden" name="skill_id" value="<?php echo htmlspecialchars($skill_id); ?>">
                <input type="hidden" name="teacher_id" value="<?php echo htmlspecialchars($teacher_id); ?>">
                <button type="submit" name="confirm_request" class="btn primary">
                    Confirm and Send Request
                </button>
            </form>
        <?php endif; ?>
    <?php endif; ?>
</div>

</body>
</html>