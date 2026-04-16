<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
include("db.php");

$message = "";

/* ---------------- LOGIN ---------------- */
if (isset($_POST['login'])) {

    $stmt = $conn->prepare("SELECT User_ID, Password FROM Users WHERE Email = ?");
    $stmt->bind_param("s", $_POST['email']);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();

        if (password_verify($_POST['password'], $user['Password'])) {
            $_SESSION['user_id'] = $user['User_ID'];
            header("Location: dashboard.php");
            exit();
        } else {
            $message = "Wrong password.";
        }
    } else {
        $message = "User not found.";
    }
}

/* ---------------- REGISTER ---------------- */
if (isset($_POST['register'])) {

    $user_id = uniqid();

    $hashedPassword = password_hash($_POST['password'], PASSWORD_DEFAULT);

    $stmt = $conn->prepare("
        INSERT INTO Users (Username, Name, Email, Major, Password)
        VALUES (?, ?, ?, ?, ?)
    ");

    $stmt->bind_param(
        "sssss",
        $_POST['username'],
        $_POST['name'],
        $_POST['email'],
        $_POST['major'],
        $hashedPassword
    );

    if ($stmt->execute()) {
        $message = "Account created. You can log in now.";
    } else {
        $message = "Error: " . $stmt->error;
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Campus Skill Exchange</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>

<div class="container">
    <div class="center">
        <h1>Campus Skill Exchange</h1>
        <p>Login or create an account</p>
    </div>

    <?php if (!empty($message)): ?>
        <div class="card" style="text-align: center; border: 1px solid #ccc;">
            <p style="color:red;"><b><?php echo $message; ?></b></p>
        </div>
    <?php endif; ?>

    <div style="display:grid; grid-template-columns: 1fr 1fr; gap:20px;">

        <!-- LOGIN -->
        <div class="card">
            <h2>Login</h2>
            <form method="POST">
                Email:<br>
                <input type="email" name="email" placeholder="Email" required style="width: 100%; padding: 8px; margin: 8px 0;"><br>
                Password:<br>
                <input type="password" name="password" placeholder="Password" required style="width: 100%; padding: 8px; margin: 8px 0;"><br><br>
                <button name="login" class="btn primary" style="width: 100%;">Login</button>
            </form>
        </div>

        <!-- REGISTER -->
        <div class="card">
            <h2>Register</h2>
            <form method="POST">
                Full Name:<br>
                <input type="text" name="name" placeholder="Name" required style="width: 100%; padding: 8px; margin: 8px 0;"><br>
                Username:<br>
                <input type="text" name="username" placeholder="Username" required style="width: 100%; padding: 8px; margin: 8px 0;"><br>
                Email:<br>
                <input type="email" name="email" placeholder="Email" required style="width: 100%; padding: 8px; margin: 8px 0;"><br>
                Major:<br>
                <input type="text" name="major" placeholder="Major" required style="width: 100%; padding: 8px; margin: 8px 0;"><br>
                Password:<br>
                <input type="password" name="password" placeholder="Password" required style="width: 100%; padding: 8px; margin: 8px 0;"><br><br>
                <button name="register" class="btn primary" style="width: 100%;">Create Account</button>
            </form>
        </div>

    </div>
</div>

</body>
</html>