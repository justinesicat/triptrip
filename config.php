<?php
// name of the host
$host = "localhost";  

// database name     
$dbname = "travel_db";  

// MySQL username 
$user = "root";   

// MySQL password         
$pass = "";                

// Create connection
$conn = new mysqli($host, $user, $pass, $dbname);
$conn->set_charset("utf8mb4");

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>