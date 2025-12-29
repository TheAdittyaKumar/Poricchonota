<?php
$host = "localhost";
$user = "root";   
$pass = "";       
$dbname = "Poricchonota";
// connection var helps me connect to the database like dailing a phone number
$conn = new mysqli($host, $user, $pass, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>