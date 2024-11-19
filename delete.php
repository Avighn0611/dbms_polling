<?php
include 'functions.php';
// Connect to MySQL
$pdo = pdo_connect_mysql();

// Get the logged-in user's username (assuming a session is started and the username is stored in the session)
session_start();
$username = $_SESSION['username'] ?? null;  // Get username from session, or null if not logged in

// Output message
$msg = '';

// Check that the poll ID exists
if (isset($_GET['id'])) {
    // Select the record that is going to be deleted
    $stmt = $pdo->prepare('SELECT * FROM polls WHERE pollID = ?');
    $stmt->execute([$_GET['id']]);
    $poll = $stmt->fetch(PDO::FETCH_ASSOC);

    // Check if the poll record exists with the id specified
    if (!$poll) {
        exit('Poll doesn\'t exist with that ID!');
    }

    // Check if the logged-in user is the creator of the poll
    if ($poll['createdby'] !== $username) {
        // If the user is not the creator, display an unauthorized message and redirect
        exit('You are not authorized to delete this poll. <a href="home.php">Go back to the home page</a>');
    }

    // Check poll start and end dates
    $current_date = date('now');
    if ($current_date > $poll['enddate']) {
        exit('The poll has ended. You cannot delete this poll.');
    }

    // Make sure the user confirms before deletion
    if (isset($_GET['confirm'])) {
        // If the user clicked the "Yes" button, delete the poll
        if ($_GET['confirm'] == 'yes') {
            // Delete the poll from the polls table (The foreign keys will handle deleting related questions, options, and votes)
            $stmt = $pdo->prepare('DELETE FROM polls WHERE pollID = ?');
            $stmt->execute([$_GET['id']]);

            // Output msg and redirect to home.php
            $msg = 'You have deleted the poll!';
            header('Location: index.php');
            exit;
        } else {
            // User clicked the "No" button, redirect them back to home.php
            header('Location: index.php');
            exit;
        }
    }
} else {
    exit('No ID specified!');
}
?>

<?=template_header('Delete')?>

<div class="content delete">
    <h2>Delete Poll #<?=$poll['pollID']?></h2>
    <?php if ($msg): ?>
        <p><?=$msg?></p>
    <?php else: ?>
        <p>Are you sure you want to delete poll #<?=$poll['pollID']?>?</p>
        <div class="yesno">
            <a href="delete.php?id=<?=$poll['pollID']?>&confirm=yes">Yes</a>
            <a href="delete.php?id=<?=$poll['pollID']?>&confirm=no">No</a>
        </div>
    <?php endif; ?>
</div>

