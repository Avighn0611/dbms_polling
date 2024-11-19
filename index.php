<?php
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include 'functions.php';

// Connect to MySQL
$pdo = pdo_connect_mysql();

// Handle logout within the index.php
if (isset($_GET['action']) && $_GET['action'] == 'logout') { 
    if (isset($_SESSION['username'])) { 
        $stmt = $pdo->prepare('INSERT INTO user_activity (pollID, username, action_type) VALUES (?, ?, ?)'); 
        $stmt->execute([null, $_SESSION['username'], 'logout']); 
    } 
    session_destroy(); 
    header('Location: login.php?message=loggedout'); 
    exit;
}

// Check if the user is logged in by querying user_activity table
$isLoggedIn = false;
if (isset($_SESSION['username'])) {
    $stmt = $pdo->prepare('SELECT * FROM user_activity WHERE username = ? AND action_type = ? ORDER BY timestamp DESC LIMIT 1');
    $stmt->execute([$_SESSION['username'], 'login']);
    $lastLogin = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($lastLogin) {
        $isLoggedIn = true;
    }
}

// Fetch polls with question counts
$stmt = $pdo->query('
    SELECT polls.pollID, polls.title, polls.createdby, COUNT(questions.questionID) AS question_count
    FROM polls
    LEFT JOIN questions ON polls.pollID = questions.pollID
    GROUP BY polls.pollID
');

$polls = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Polls</title>
    <link rel="stylesheet" href="style3.css">
</head>
<body>

<!-- Navbar -->
<div class="navbar">
    <h1>PollMaker</h1>
    <ul>
        <?php if ($isLoggedIn): ?>
            <li><a href="?action=logout">Logout</a></li>
            <li><a href="create.php">Create Poll</a></li>
        <?php else: ?>
            <li><a href="login.php">Login</a></li>
            <li><a href="signup.php">Sign Up</a></li>
        <?php endif; ?>
        <li><a href="create_quiz.php">Quiz</a></li>
        <li><a href="create_test.php">Test</a></li>
    </ul>
</div>

<!-- Main Content -->
<div class="content home">
    <h2>Polls</h2>
    <p>Welcome to the home page! You can view the list of polls below.</p>
    <a href="<?= $isLoggedIn ? 'create.php' : 'login.php?redirect=create.php' ?>" class="create-poll">
        Create Poll
    </a>

    <table>
        <thead>
            <tr>
                <td>#</td>
                <td>Title</td>
                <td>Created BY</td>
                <td>Number of Questions</td>
                <td>Actions</td>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($polls as $poll): ?>
            <tr>
                <td><?=$poll['pollID']?></td>
                <td><?=htmlspecialchars($poll['title'], ENT_QUOTES)?></td>
                <td><?=htmlspecialchars($poll['createdby'], ENT_QUOTES)?></td>
                <td><?=$poll['question_count']?></td>
                <td class="actions">
                    <a href="vote.php?id=<?=$poll['pollID']?>" class="view" title="View Poll">View</a>
                    <a href="delete.php?id=<?=$poll['pollID']?>" class="trash" title="Delete Poll">Delete</a>
                    <a href="edit.php?pollID=<?= $poll['pollID'] ?>">Edit Poll</a>                
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<!-- Features Section -->
<div class="features">
    <h3>Our Main Features</h3>
    <p>Explore our powerful tools designed to help you create engaging polls, quizzes, and tests effortlessly.</p>
    <div class="feature-cards">
        <div class="feature-card">
            <h4>Easy Poll Creation</h4>
            <p>Set up polls quickly with customizable questions and options.</p>
        </div>
        <div class="feature-card">
            <h4>Data Visualization</h4>
            <p>Visualize poll results with charts and statistics.</p>
        </div>
        <div class="feature-card">
            <h4>Real-time Analytics</h4>
            <p>Track votes and responses as they happen.</p>
        </div>
        <div class="feature-card">
            <h4>Shareable Links</h4>
            <p>Generate unique links for easy poll sharing.</p>
        </div>
    </div>
</div>

<!-- Footer -->
<div class="footer">
    <p>&copy; 2024 PollMaker. All Rights Reserved.</p>
    <ul>
        <li><a href="about.php">About Us</a></li>
        <li><a href="privacy.php">Privacy Policy</a></li>
        <li><a href="contact.php">Contact</a></li>
    </ul>
</div>

</body>
</html>
