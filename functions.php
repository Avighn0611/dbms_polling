<?php
function pdo_connect_mysql() {
    // Update the details below with your MySQL details
    $DATABASE_HOST = 'localhost';
    $DATABASE_USER = 'root';
    $DATABASE_PASS = ''; // Set your database password here
    $DATABASE_NAME = 'Avighn';
    try {
        return new PDO('mysql:host=' . $DATABASE_HOST . ';dbname=' . $DATABASE_NAME . ';charset=utf8', $DATABASE_USER, $DATABASE_PASS);
    } catch (PDOException $exception) {
        // If there is an error with the connection, stop the script and display the error.
        exit('Failed to connect to database!');
    }
}

// Initialize the database connection and store it in the $pdo variable
$pdo = pdo_connect_mysql();

function template_header($title) {
    echo '<!DOCTYPE html>
    <html>
        <head>
            <meta charset="utf-8">
            <title>' . $title . '</title>
            <link href="style.css" rel="stylesheet" type="text/css">
            
        </head>
        <body>
        <nav class="navtop">
            <div>
                <h1>Poll &amp; Voting System</h1>
                <a href="index.php"><svg width="20" height="20" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M19 3H5C3.9 3 3 3.9 3 5V19C3 20.1 3.9 21 5 21H19C20.1 21 21 20.1 21 19V5C21 3.9 20.1 3 19 3M9 17H7V10H9V17M13 17H11V7H13V17M17 17H15V13H17V17Z" /></svg>Polls</a>
            </div>
        </nav>';
}

function is_user_logged_in($pdo, $username) { 
    $stmt = $pdo->prepare('SELECT * FROM user_activity WHERE username = ? AND action_type = ? ORDER BY timestamp DESC LIMIT 1'); 
    $stmt->execute([$username, 'login']); $last_login = $stmt->fetch(PDO::FETCH_ASSOC); 
    if ($last_login) { return true; } else { return false; } }

?>




