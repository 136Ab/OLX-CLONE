<?php
$host = "localhost";
$user = "uhcrnj1vbersg";
$password = "q2hr4nxquppc";
$dbname = "dbmovzmzrilrs1";

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname", $user, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}
?>
