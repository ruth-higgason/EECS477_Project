<?php
session_start();
include("db.php");

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Get unique categories for the filter dropdown
$categories_result = $conn->query("SELECT DISTINCT Category FROM Skills ORDER BY Category");
$categories = [];
while ($row = $categories_result->fetch_assoc()) {
    $categories[] = $row['Category'];
}

// Build the search query

// 1. Start with the Base SELECT and JOINs
$query = "
    SELECT 
        Skills.Skill_ID,
        Skills.Title,
        Skills.Description,
        Skills.Category,
        Users.User_ID,
        Users.Username,
        Users.Name AS TeacherName,
        AVG(Reviews.Rating) AS Avg_Rating,
        COUNT(Reviews.Review_ID) AS Review_Count
    FROM Skills
    JOIN UserSkills ON Skills.Skill_ID = UserSkills.Skill_ID
    JOIN Users ON UserSkills.User_ID = Users.User_ID
    LEFT JOIN Reviews ON Users.User_ID = Reviews.Teacher_ID 
    AND Skills.Skill_ID = Reviews.Skill_ID
";

// 2. Handle WHERE filters BEFORE Grouping
$where_clauses = [];
$params = [];
$types = "";

if (!empty($search_term)) {
    $where_clauses[] = "(Skills.Title LIKE ? OR Skills.Description LIKE ?)";
    $params[] = "%$search_term%";
    $params[] = "%$search_term%";
    $types .= "ss";
}

if (!empty($category_filter)) {
    $where_clauses[] = "Skills.Category = ?";
    $params[] = $category_filter;
    $types .= "s";
}

if (!empty($teacher_filter)) {
    $where_clauses[] = "(Users.Name LIKE ? OR Users.Username LIKE ?)";
    $params[] = "%$teacher_filter%";
    $params[] = "%$teacher_filter%";
    $types .= "ss";
}

// Construct the WHERE part if filters exist
if (!empty($where_clauses)) {
    $query .= " WHERE " . implode(" AND ", $where_clauses);
}

// 3. Append GROUP BY and ORDER BY at the very end
$query .= " GROUP BY Skills.Skill_ID, Users.User_ID";
$query .= " ORDER BY Skills.Title ASC";

$stmt = $conn->prepare($query);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Browse Skills - Campus Skill Exchange</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>

<div class="container">
    <h1>Browse Skills</h1>
    <a href="dashboard.php">Back to Dashboard</a>
    <hr>

    <!-- Search and Filter Form -->
    <div class="card">
        <h3>Filter Skills</h3>
        <form method="GET" style="display: flex; gap: 15px; flex-wrap: wrap; align-items: flex-end;">
            <div>
                Search:<br>
                <input type="text" name="search" value="<?php echo htmlspecialchars($search_term); ?>" placeholder="Title or Description">
            </div>
            <div>
                Category:<br>
                <select name="category">
                    <option value="">All Categories</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?php echo htmlspecialchars($cat); ?>" <?php if ($category_filter == $cat) echo 'selected'; ?>>
                            <?php echo htmlspecialchars($cat); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                Teacher:<br>
                <input type="text" name="teacher" value="<?php echo htmlspecialchars($teacher_filter); ?>" placeholder="Teacher Name">
            </div>
            <div>
                <button type="submit" class="btn primary">Apply Filters</button>
                <a href="skills.php" class="btn secondary">Clear</a>
            </div>
        </form>
    </div>

    <hr>

    <?php if ($result->num_rows == 0): ?>
        <p>No skills found matching your criteria.</p>
    <?php else: ?>
        <div class="card" style="padding: 0; overflow: hidden;">
            <table style="width: 100%; border-collapse: collapse;">
                <thead>
                    <tr style="background: #2c5aa0; color: white; text-align: left;">
                        <th style="padding: 12px;">Skill</th>
                        <th style="padding: 12px;">Category</th>
                        <th style="padding: 12px;">Description</th>
                        <th style="padding: 12px;">Teacher</th>
                        <th style="padding: 12px;">Rating</th>
                        <th style="padding: 12px;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr style="border-bottom: 1px solid #eee;">
                            <td style="padding: 12px;"><b><?php echo htmlspecialchars($row['Title']); ?></b></td>
                            <td style="padding: 12px;"><?php echo htmlspecialchars($row['Category']); ?></td>
                            <td style="padding: 12px;"><?php echo htmlspecialchars($row['Description']); ?></td>
                            <td style="padding: 12px;"><?php echo htmlspecialchars($row['TeacherName']); ?></td>

                            <td style="padding: 12px;">
                                <?php echo $row['Avg_Rating'] ? 
                                    number_format($row['Avg_Rating'], 1) . " / 5 (" . $row['Review_Count'] . " reviews)" : 
                                    "No ratings yet"; 
                                ?>
                            </td>

                            <td style="padding: 12px;">
                                <?php if ($row['User_ID'] != $user_id): ?>
                                    <form method="POST" action="create_request.php">
                                        <input type="hidden" name="skill_id" value="<?php echo $row['Skill_ID']; ?>">
                                        <input type="hidden" name="teacher_id" value="<?php echo $row['User_ID']; ?>">
                                        <button type="submit" class="btn primary">Request</button>
                                    </form>
                                <?php else: ?>
                                    <span style="color: #888;"><i>Your Skill</i></span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

</body>
</html>