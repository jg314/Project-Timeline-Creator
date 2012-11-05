<?php
//After updating the db credentials you need to rename this file to connection.php.
@ $db = new mysqli('localhost', 'username', 'password', 'db_name');
if (mysqli_connect_errno()) {
    echo 'Error: Could not connect to database. Please try again later.';
    exit;
}

//Also must have the database setup through PHPMyAdmin

/*
 * Create one database called "timeline"
 * 
 * Create three fields:
 * 1. id - int, auto-increment
 * 2. project_name - varchar(255)
 * 3. due_dates - mediumtext
*/
?>

