<?php

$conn = new mysqli('mysql.eecs.ku.edu', '447s26_j118k994', 'fxqj44vhTMSV', '447s26_j118k994');

if ($conn->connect_error) {
    die('Could not connect: ' . $conn->connect_error);
}

echo "Connection successful<br>";

$query = 'SELECT * FROM CRUISE';
$result = $conn->query($query);

if (!$result) {
    die("Query failed: " . $conn->error);
}

echo "<!DOCTYPE html><html><body>";

if ($result->num_rows > 0) {
    echo "<table border='1'>";

    // headers
    echo "<tr>";
    $fields = $result->fetch_fields();
    foreach ($fields as $field) {
        echo "<th>{$field->name}</th>";
    }
    echo "</tr>";

    // rows
    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        foreach ($row as $value) {
            echo "<td>$value</td>";
        }
        echo "</tr>";
    }

    echo "</table>";
} else {
    echo "No results found.";
}

echo "</body></html>";

$conn->close();
?>