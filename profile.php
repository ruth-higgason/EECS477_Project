<?php
session_start();
include("db.php");

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$user_id = $_SESSION['user_id'];

$password_message = "";

/* =========================
   UPDATE PROFILE (USERNAME INCLUDED)
========================= */
if (isset($_POST['update_profile'])) {
    $username = $_POST['username'];
    $name = $_POST['name'];
    $email = $_POST['email'];
    $major = $_POST['major'];

    $stmt = $conn->prepare("UPDATE Users SET Username=?, Name=?, Email=?, Major=? WHERE User_ID=?");
    $stmt->bind_param("sssss", $username, $name, $email, $major, $user_id);
    $stmt->execute();
}

/* =========================
   CHANGE PASSWORD
========================= */
if (isset($_POST['change_password'])) {
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];

    $stmt = $conn->prepare("SELECT Password FROM Users WHERE User_ID=?");
    $stmt->bind_param("s", $user_id);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();

    if ($result && $result['Password'] === $current_password) {
        $stmt = $conn->prepare("UPDATE Users SET Password=? WHERE User_ID=?");
        $stmt->bind_param("ss", $new_password, $user_id);
        $stmt->execute();

        $password_message = "Password updated successfully.";
    } else {
        $password_message = "Current password is incorrect.";
    }
}

/* =========================
   DELETE SKILL FROM USER
========================= */
if (isset($_POST['delete_skill'])) {
    $skill_id = $_POST['skill_id'];

    $stmt = $conn->prepare("DELETE FROM UserSkills WHERE User_ID=? AND Skill_ID=?");
    $stmt->bind_param("ss", $user_id, $skill_id);
    $stmt->execute();
}

/* =========================
   ADD EXISTING SKILL
========================= */
if (isset($_POST['add_existing_skill'])) {
    $skill_id = $_POST['skill_id'];

    $check = $conn->prepare("SELECT * FROM UserSkills WHERE User_ID=? AND Skill_ID=?");
    $check->bind_param("ss", $user_id, $skill_id);
    $check->execute();
    $result = $check->get_result();

    if ($result->num_rows == 0) {
        $stmt = $conn->prepare("INSERT INTO UserSkills (User_ID, Skill_ID) VALUES (?, ?)");
        $stmt->bind_param("ss", $user_id, $skill_id);
        $stmt->execute();
    }
}

/* =========================
   ADD NEW SKILL
========================= */
if (isset($_POST['add_new_skill'])) {
    $title = $_POST['title'];
    $desc = $_POST['description'];
    $category = $_POST['category'];

    $stmt = $conn->prepare("INSERT INTO Skills (Title, Description, Category) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $title, $desc, $category);
    $stmt->execute();

    $new_skill_id = $conn->insert_id;

    $stmt = $conn->prepare("INSERT INTO UserSkills (User_ID, Skill_ID) VALUES (?, ?)");
    $stmt->bind_param("ss", $user_id, $new_skill_id);
    $stmt->execute();
}

/* =========================
   GET USER INFO
========================= */
$stmt = $conn->prepare("SELECT Username, Name, Email, Major FROM Users WHERE User_ID=?");
$stmt->bind_param("s", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

/* =========================
   GET USER SKILLS
========================= */
$stmt = $conn->prepare("
    SELECT Skills.Skill_ID, Skills.Title
    FROM Skills
    JOIN UserSkills ON Skills.Skill_ID = UserSkills.Skill_ID
    WHERE UserSkills.User_ID = ?
");
$stmt->bind_param("s", $user_id);
$stmt->execute();
$user_skills = $stmt->get_result();

/* =========================
   GET ALL SKILLS
========================= */
$all_skills = $conn->query("SELECT Skill_ID, Title FROM Skills");
?>

<!DOCTYPE html>
<html>
<head>
    <title>My Profile - Campus Skill Exchange</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>

<div class="container">
    <h1>My Profile</h1>
    <a href="dashboard.php">Back to Dashboard</a>
    <hr>

    <!-- =========================
         ACCOUNT INFO
    ========================= -->
    <div class="card">
        <h2>Account Info</h2>

        <form method="POST">
            Username:<br>
            <input type="text" name="username" value="<?php echo htmlspecialchars($user['Username']); ?>"><br><br>

            Name:<br>
            <input type="text" name="name" value="<?php echo htmlspecialchars($user['Name']); ?>"><br><br>

            Email:<br>
            <input type="email" name="email" value="<?php echo htmlspecialchars($user['Email']); ?>"><br><br>

            Major:<br>
            <input type="text" name="major" value="<?php echo htmlspecialchars($user['Major']); ?>"><br><br>

            <button type="submit" name="update_profile" class="btn primary">Update Profile</button>
        </form>
    </div>

    <hr>

    <!-- =========================
         CHANGE PASSWORD
    ========================= -->
    <div class="card">
        <h2>Change Password</h2>

        <?php if (!empty($password_message)) echo "<p><b>$password_message</b></p>"; ?>

        <form method="POST">
            Current Password:<br>
            <input type="password" name="current_password" required><br><br>

            New Password:<br>
            <input type="password" name="new_password" required><br><br>

            <button type="submit" name="change_password" class="btn primary">Update Password</button>
        </form>
    </div>

    <hr>

    <!-- =========================
         SKILLS
    ========================= -->
    <div class="card">
        <h2>My Skills</h2>

        <?php if ($user_skills->num_rows == 0): ?>
            <p>You haven't added any skills yet.</p>
        <?php else: ?>
            <ul>
            <?php while ($row = $user_skills->fetch_assoc()): ?>
                <li style="margin-bottom: 5px;">
                    <?php echo htmlspecialchars($row['Title']); ?>

                    <form method="POST" style="display:inline; margin-left: 10px;">
                        <input type="hidden" name="skill_id" value="<?php echo $row['Skill_ID']; ?>">
                        <button type="submit" name="delete_skill" style="color: red; background: none; border: 1px solid red; cursor: pointer; border-radius: 3px; padding: 2px 5px;">Remove</button>
                    </form>
                </li>
            <?php endwhile; ?>
            </ul>
        <?php endif; ?>
    </div>

    <hr>

    <!-- ADD EXISTING SKILL -->
    <div class="card">
        <h3>Add Existing Skill</h3>

        <form method="POST">
            <select name="skill_id">
                <?php 
                $all_skills->data_seek(0);
                while ($row = $all_skills->fetch_assoc()): ?>
                    <option value="<?php echo $row['Skill_ID']; ?>">
                        <?php echo htmlspecialchars($row['Title']); ?>
                    </option>
                <?php endwhile; ?>
            </select>
            <button type="submit" name="add_existing_skill" class="btn primary">Add Skill</button>
        </form>
    </div>

    <hr>

    <!-- CREATE NEW SKILL -->
    <div class="card">
        <h3>Create New Skill</h3>

        <form method="POST">
            Title:<br>
            <input type="text" name="title" required><br>

            Description:<br>
            <input type="text" name="description"><br>

            Category:<br>
            <input type="text" name="category"><br><br>

            <button type="submit" name="add_new_skill" class="btn primary">Create Skill</button>
        </form>
    </div>

    <br>
    <a href="logout.php">Logout</a>
</div>

</body>
</html>