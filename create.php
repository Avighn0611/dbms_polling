<?php
// Include necessary files (db connection, header, etc.)
include 'functions.php';

session_start();
$msg = '';

// Assuming the username is stored in the session when the user logs in
$createdBy = $_SESSION['username'] ?? 'Anonymous';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pollTitle = $_POST['pollTitle'] ?? null;
    $pollType = $_POST['pollType'] ?? null;
    $questions = $_POST['questions'] ?? [];

    if ($pollTitle && $pollType) {
        // Insert poll details into the database, including the createdby field
        $stmt = $pdo->prepare('INSERT INTO polls (title, type, createdby) VALUES (?, ?, ?)');
        $stmt->execute([$pollTitle, $pollType, $createdBy]);
        $pollID = $pdo->lastInsertId();

        // Insert each question and its options into the database
        foreach ($questions as $question) {
            $questionText = $question['text'] ?? '';
            $options = explode("\n", $question['options'] ?? '');

            if (!empty($questionText)) {
                // Insert the question
                $stmt = $pdo->prepare('INSERT INTO questions (pollID, questiontext) VALUES (?, ?)');
                $stmt->execute([$pollID, $questionText]);
                $questionID = $pdo->lastInsertId();

                // Insert each option for the question
                foreach ($options as $option) {
                    $option = trim($option); // Remove extra whitespace
                    if (!empty($option)) {
                        $stmt = $pdo->prepare('INSERT INTO options (questionID, optiontext) VALUES (?, ?)');
                        $stmt->execute([$questionID, $option]);
                    }
                }
            }
        }

        // Redirect to the set_dates.php page with the poll ID
        header("Location: set_dates.php?poll_id=$pollID");
        exit;
    } else {
        echo "Error: Poll title and type are required.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Poll</title>
    <link rel="stylesheet" href="style2.css"> <!-- Update path to your CSS -->
</head>
<body>
    <nav class="navbar">
        <a href="index.php">Home</a>
        <a href="logout.php">Logout</a>
        <a href="quiz.php">quiz</a>
        <a href="test.php">test</a>
    </nav>
    <div class="container">
        <section id="createPoll">
            <h2>Create a Poll</h2>
            <form id="pollForm" method="POST">
                <div class="form-group">
                    <label for="pollTitle">Poll Title:</label>
                    <input type="text" id="pollTitle" name="pollTitle" required>
                </div>

                <div class="form-group">
                    <label for="pollType">Poll Type:</label>
                    <select id="pollType" name="pollType" required onchange="showQuestionCountInput()">
                        <option value="">Select</option>
                        <option value="single">Single Question</option>
                        <option value="multiple">Multiple Choice (MCQ)</option>
                        <option value="multiple_select">Multiple Select (MSQ)</option>
                    </select>
                </div>

                <div id="questionCountInput" class="form-group" style="display: none;">
                    <label for="questionCount">Number of Questions:</label>
                    <input type="number" id="questionCount" name="questionCount" min="1" max="20" onchange="generateQuestionFields()">
                </div>

                <div id="questionsSection" style="display: none;">
                    <h3>Questions</h3>
                    <div id="questionFields"></div>

                    <!-- Navigation Buttons -->
                    <div class="navigation-buttons">
                        <button type="button" id="prevButton" onclick="prevQuestion()" style="display: none;">Previous</button>
                        <button type="button" id="nextButton" onclick="nextQuestion()" style="display: none;">Next</button>
                        <button type="submit" id="submitButton" style="display: none;">Submit Poll</button>
                    </div>
                </div>
            </form>
        </section>
    </div>
    <script src="script.js"></script>
</body>
</html>
