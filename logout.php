<?php
session_start();
include 'functions.php'; // Ensure the functions.php is included to establish the database connection

if (isset($_SESSION['username'])) {
    $username = $_SESSION['username'];
    $pollID = $_GET['poll_id'] ?? null; // Assuming poll_id is passed as a parameter if applicable

    // Insert logout event into user_activity table
    $stmt = $pdo->prepare('INSERT INTO user_activity (pollID, username, action_type) VALUES (?, ?, ?)');
    $stmt->execute([$pollID, $username, 'logout']);
}

// Destroy the session to log the user out
session_destroy();

// Redirect to the index page
header('Location: index.php?message=loggedout');
exit;
?>
