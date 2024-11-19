<?php
session_start();
include 'functions.php';
$pdo = pdo_connect_mysql();

// Check if the user is logged in
if (!isset($_SESSION['username'])) {
    header('Location: login.php?redirect=edit.php?pollID=' . $_GET['pollID']);
    exit;
}

// Get poll ID from query parameters
$pollID = $_GET['pollID'] ?? null;
$currentQuestionIndex = $_GET['q'] ?? 0;

// Ensure poll ID is present
if (!$pollID) {
    echo 'Poll ID is missing.';
    exit;
}

// Fetch poll details
$stmt = $pdo->prepare('SELECT * FROM polls WHERE pollID = ?');
$stmt->execute([$pollID]);
$poll = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$poll) {
    echo 'Poll not found.';
    exit;
}

// Check if the user is the creator and poll has not started
if ($poll['createdby'] !== $_SESSION['username']) {
    echo 'You do not have permission to edit this poll.';
    exit;
} elseif (new DateTime($poll['startdate']) <= new DateTime()) {
    echo 'Poll has already started and cannot be edited.';
    exit;
}

// Fetch all questions for the poll
$stmt = $pdo->prepare('SELECT * FROM questions WHERE pollID = ? ORDER BY questionID');
$stmt->execute([$pollID]);
$questions = $stmt->fetchAll(PDO::FETCH_ASSOC);
$totalQuestions = count($questions);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['edit_question'])) {
        $questionID = $_POST['questionID'];
        $questionText = $_POST['question'];
        $optionText = $_POST['options'];

        // Update question text
        $stmt = $pdo->prepare('UPDATE questions SET questiontext = ? WHERE questionID = ?');
        $stmt->execute([$questionText, $questionID]);

        // Remove existing options and reinsert new options
        $stmt = $pdo->prepare('DELETE FROM options WHERE questionID = ?');
        $stmt->execute([$questionID]);

        $options = array_filter(array_map('trim', explode("\n", $optionText))); // Split by newline and trim
        foreach ($options as $option) {
            $stmt = $pdo->prepare('INSERT INTO options (questionID, optiontext) VALUES (?, ?)');
            $stmt->execute([$questionID, $option]);
        }
    } elseif (isset($_POST['add_question'])) {
        $newQuestionText = $_POST['new_question'];
        $optionText = $_POST['options'];

        // Insert new question
        $stmt = $pdo->prepare('INSERT INTO questions (pollID, questiontext) VALUES (?, ?)');
        $stmt->execute([$pollID, $newQuestionText]);
        $newQuestionID = $pdo->lastInsertId();

        // Insert options for the new question
        $options = array_filter(array_map('trim', explode("\n", $optionText))); // Split by newline and trim
        foreach ($options as $option) {
            $stmt = $pdo->prepare('INSERT INTO options (questionID, optiontext) VALUES (?, ?)');
            $stmt->execute([$newQuestionID, $option]);
        }

        // Redirect to the newly added question
        header("Location: edit.php?pollID=$pollID&q=$totalQuestions");
        exit;
    }

    // Redirect back to the same page
    header("Location: edit.php?pollID=$pollID&q=$currentQuestionIndex");
    exit;
}

// Fetch current question
$currentQuestion = $questions[$currentQuestionIndex] ?? null;
$currentQuestionID = $currentQuestion['questionID'] ?? null;

// Fetch options for the current question
$options = [];
if ($currentQuestionID) {
    $stmt = $pdo->prepare('SELECT * FROM options WHERE questionID = ?');
    $stmt->execute([$currentQuestionID]);
    $options = $stmt->fetchAll(PDO::FETCH_COLUMN, 1); // Fetch only option texts
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Poll</title>
    <link rel="stylesheet" href="style2.css">
</head>
<body>
    <div class="container">
        <h2>Edit Poll: <?= htmlspecialchars($poll['title'], ENT_QUOTES) ?></h2>

        <!-- Display Current Question -->
        <?php if ($currentQuestion): ?>
            <form method="post" action="edit.php?pollID=<?= htmlspecialchars($pollID, ENT_QUOTES) ?>&q=<?= $currentQuestionIndex ?>">
                <input type="hidden" name="questionID" value="<?= htmlspecialchars($currentQuestion['questionID'], ENT_QUOTES) ?>">

                <div class="input-group">
                    <label for="question">Question:</label>
                    <input type="text" id="question" name="question" value="<?= htmlspecialchars($currentQuestion['questiontext'], ENT_QUOTES) ?>" required>
                </div>

                <div class="input-group">
                    <label for="options">Options (one per line):</label>
                    <textarea id="options" name="options" rows="4"><?= htmlspecialchars(implode("\n", $options), ENT_QUOTES) ?></textarea>
                </div>

                <div class="input-group">
                    <button type="submit" name="edit_question">Save Changes</button>
                </div>
            </form>
        <?php endif; ?>

        <!-- Navigation Buttons -->
        <div class="navigation">
            <?php if ($currentQuestionIndex > 0): ?>
                <form method="get" action="edit.php" style="display: inline;">
                    <input type="hidden" name="pollID" value="<?= htmlspecialchars($pollID, ENT_QUOTES) ?>">
                    <input type="hidden" name="q" value="<?= max(0, $currentQuestionIndex - 1) ?>">
                    <button type="submit">Prev</button>
                </form>
            <?php endif; ?>

            <?php if ($currentQuestionIndex < $totalQuestions - 1): ?>
                <form method="get" action="edit.php" style="display: inline;">
                    <input type="hidden" name="pollID" value="<?= htmlspecialchars($pollID, ENT_QUOTES) ?>">
                    <input type="hidden" name="q" value="<?= min($currentQuestionIndex + 1, $totalQuestions - 1) ?>">
                    <button type="submit">Next</button>
                </form>
            <?php endif; ?>
        </div>

        <!-- Add New Question -->
        <?php if ($currentQuestionIndex == $totalQuestions - 1): ?>
            <form method="post" action="edit.php?pollID=<?= htmlspecialchars($pollID, ENT_QUOTES) ?>&q=<?= $currentQuestionIndex ?>">
                <h3>Add New Question</h3>
                <div class="input-group">
                    <label for="new_question">Question:</label>
                    <input type="text" id="new_question" name="new_question">
                </div>
                <div class="input-group">
                    <label for="new_options">Options (one per line):</label>
                    <textarea id="new_options" name="options" rows="4"></textarea>
                </div>
                <div class="input-group">
                    <button type="submit" name="add_question">Add Question</button>
                </div>
            </form>

            <form method="post" action="index.php">
                <div class="input-group">
                    <button type="submit" name="submit_poll">Submit Poll</button>
                </div>
            </form>
        <?php endif; ?>
    </div>
</body>
</html>
