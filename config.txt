<?php
$servername = "fdb1032.awardspace.net";
$username = "4488312_czarny";
$password = "Haslo123";
$dbname = "4488312_czarny";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
