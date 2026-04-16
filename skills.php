<?php
session_start();
include("db.php");

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$user_id = $_SESSION['user_id'];

/* =========================
   GET ALL SKILLS + OWNERS
========================= */
$stmt = $conn->prepare("
    SELECT 
        Skills.Skill_ID,
        Skills.Title,
        Skills.Description,
        Skills.Category,
        Users.User_ID,
        Users.Username,
        Users.Name
    FROM Skills
    LEFT JOIN UserSkills ON Skills.Skill_ID = UserSkills.Skill_ID
    LEFT JOIN Users ON UserSkills.User_ID = Users.User_ID
    ORDER BY Skills.Category, Skills.Title
");
$stmt->execute();
$result = $stmt->get_result();

/* Organize data so each skill groups its users */
$skills = [];

while ($row = $result->fetch_assoc()) {
    $id = $row['Skill_ID'];

    if (!isset($skills[$id])) {
        $skills[$id] = [
            "Title" => $row["Title"],
            "Description" => $row["Description"],
            "Category" => $row["Category"],
            "Users" => []
        ];
    }

    if ($row["User_ID"]) {
        $skills[$id]["Users"][] = [
            "User_ID" => $row["User_ID"],
            "Username" => $row["Username"],
            "Name" => $row["Name"]
        ];
    }
}
?>

<h1>Browse Skills</h1>

<a href="dashboard.php">Back to Dashboard</a>

<hr>

<?php if (count($skills) == 0): ?>
    <p>No skills available.</p>
<?php else: ?>

    <?php foreach ($skills as $skill): ?>
        <div style="border:1px solid #ccc; padding:10px; margin-bottom:10px;">

            <h3><?php echo htmlspecialchars($skill['Title']); ?></h3>

            <p>
                <b>Category:</b> <?php echo htmlspecialchars($skill['Category']); ?>
            </p>

            <p>
                <?php echo htmlspecialchars($skill['Description']); ?>
            </p>

            <p><b>Offered by:</b></p>

            <?php if (count($skill["Users"]) == 0): ?>
                <p><i>No users currently offer this skill</i></p>
            <?php else: ?>
                <ul>
                    <?php foreach ($skill["Users"] as $user): ?>
                        <li>
                            <?php echo htmlspecialchars($user["Username"]); ?>
                            (<?php echo htmlspecialchars($user["Name"]); ?>)

                            <?php if ($user["User_ID"] != $user_id): ?>
            			<form method="POST" action="create_request.php" style="display:inline;">
    				   <input type="hidden" name="skill_id" value="<?php echo $skill['Skill_ID']; ?>">
    				   <input type="hidden" name="teacher_id" value="<?php echo $user['User_ID']; ?>">
    				   <button type="submit">Request</button>
				</form>        		
			    <?php else: ?>
            			<i>(Your skill)</i>
        		    <?php endif; ?>                        
		      </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>

        </div>
    <?php endforeach; ?>

<?php endif; ?>

<br>
<a href="dashboard.php">Back to Dashboard</a>