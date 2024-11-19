<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['username'])) {
    // Store the poll_id to redirect back after login
    $_SESSION['redirect_after_login'] = 'vote.php?poll_id=' . $_GET['poll_id'];
    // Redirect to the login page
    header('Location: login.php');
    exit;
}

// If logged in, redirect to the vote page
header('Location: vote.php?poll_id=' . $_GET['poll_id']);
exit;
?>
