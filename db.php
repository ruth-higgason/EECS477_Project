<?php

$conn = new mysqli('mysql.eecs.ku.edu', '447s26_j118k994', 'fxqj44vhTMSV', '447s26_j118k994');

if ($conn->connect_error) {
    die('Could not connect: ' . $conn->connect_error);
}

?>
