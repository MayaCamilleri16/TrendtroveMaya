<?php
$servername = "localhost";
$username = "Maya";
$password = "OjTcOmKpR4[fZHlt"; 
$dbname = "Trendtrove";

// connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check the connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Connection successful
echo "Connected successfully";

?>
