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
    SELECT Rating, Comment
    FROM Reviews
    WHERE Teacher_ID=?
    ORDER BY Review_ID DESC
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
   FINAL SUBMISSION (DISABLED)
========================= */
if (isset($_POST['confirm_request'])) {
    // DO NOTHING FOR NOW
    // placeholder so form still submits safely

    echo "<p><b>Request system is currently disabled.</b></p>";
}
?>

<h1>Confirm Request</h1>

<a href="skills.php">Back</a>

<hr>

<!-- SKILL -->
<h2>Skill</h2>
<p><b><?php echo htmlspecialchars($skill['Title']); ?></b></p>
<p><?php echo htmlspecialchars($skill['Description']); ?></p>
<p><b>Category:</b> <?php echo htmlspecialchars($skill['Category']); ?></p>

<hr>

<!-- TEACHER -->
<h2>Provider</h2>
<p><b>Name:</b> <?php echo htmlspecialchars($teacher['Name']); ?></p>
<p><b>Username:</b> <?php echo htmlspecialchars($teacher['Username']); ?></p>
<p><b>Major:</b> <?php echo htmlspecialchars($teacher['Major']); ?></p>

<p>
<b>Average Rating:</b>
<?php echo $rating['avg_rating'] ? round($rating['avg_rating'], 2) : "No ratings yet"; ?>
</p>

<hr>

<!-- OTHER SKILLS -->
<h2>Other Skills They Have</h2>
<ul>
<?php while ($row = $other_skills->fetch_assoc()): ?>
    <li><?php echo htmlspecialchars($row['Title']); ?></li>
<?php endwhile; ?>
</ul>

<hr>

<!-- REVIEWS -->
<h2>Reviews</h2>
<?php if ($reviews->num_rows == 0): ?>
    <p>No reviews yet.</p>
<?php else: ?>
    <ul>
    <?php while ($row = $reviews->fetch_assoc()): ?>
        <li>
            <?php echo $row['Rating']; ?> -
            <?php echo htmlspecialchars($row['Comment']); ?>
        </li>
    <?php endwhile; ?>
    </ul>
<?php endif; ?>

<hr>

<!-- CONFIRM BUTTON (SAFE PLACEHOLDER) -->
<form method="POST">
    <button type="submit" name="confirm_request">
        Request (Disabled for now)
    </button>
</form>

<br>
<a href="skills.php">Cancel</a>