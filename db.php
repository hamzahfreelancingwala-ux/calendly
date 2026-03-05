<?php
// Database credentials provided by the user
$servername = "localhost";
$username = "rsoa_rsoa0112_2";
$password = "654321#";
$dbname = "rsoa_rsoa0112_2";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    // If this message appears, the issue is database connection.
    die("Database Connection failed: " . $conn->connect_error);
}

// Function to safely close the connection
function close_db_connection($conn) {
    if ($conn) {
        $conn->close();
    }
}
?>
