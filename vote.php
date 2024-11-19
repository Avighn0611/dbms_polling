<?php
include 'functions.php';
session_start();

// Connect to MySQL
$pdo = pdo_connect_mysql();

// Check if user is logged in
if (!isset($_SESSION['username'])) {
    // If not logged in, redirect to login page
    header('Location: login.php');
    exit;
}

$username = $_SESSION['username'];

// Check if the poll ID is provided
if (!isset($_GET['id'])) {
    exit('No poll ID specified.');
}

$poll_id = $_GET['id'];

// Fetch poll details
$stmt = $pdo->prepare('SELECT * FROM polls WHERE pollID = ?');
$stmt->execute([$poll_id]);
$poll = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$poll) {
    exit('Poll with that ID does not exist.');
}

// Check poll start and end dates
date_default_timezone_set('Asia/Kolkata');
$current_date = date('Y-m-d H:i');
if ($current_date < $poll['startdate']) {
    exit('The poll has not yet started. You cannot vote for this poll.');
}
$poll_ended = $current_date > $poll['enddate'];

// Fetch all questions for the poll
$stmt = $pdo->prepare('SELECT * FROM questions WHERE pollID = ?');
$stmt->execute([$poll_id]);
$questions = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (!$questions) {
    exit('No questions found for this poll.');
}

// Fetch options for all questions
$all_options = [];
foreach ($questions as $question) {
    $stmt = $pdo->prepare('SELECT * FROM options WHERE questionID = ?');
    $stmt->execute([$question['questionID']]);
    $all_options[$question['questionID']] = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Check if the user has already voted in this poll
$user_votes = [];
$stmt = $pdo->prepare('
    SELECT v.optionID, o.questionID 
    FROM votes v 
    JOIN options o ON v.optionID = o.optionID 
    WHERE v.voter = ? AND o.questionID IN (SELECT questionID FROM questions WHERE pollID = ?)
');
$stmt->execute([$username, $poll_id]);
while ($vote = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $user_votes[$vote['questionID']] = $vote['optionID'];
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach ($questions as $question) {
        $question_id = $question['questionID'];

        if (isset($_POST["question_$question_id"])) {
            $selected_option = $_POST["question_$question_id"];

            if (isset($user_votes[$question_id])) {
                // Update existing vote
                $stmt = $pdo->prepare('UPDATE votes SET optionID = ? WHERE voter = ? AND optionID = ?');
                $stmt->execute([$selected_option, $username, $user_votes[$question_id]]);
            } else {
                // Insert new vote
                $stmt = $pdo->prepare('INSERT INTO votes (voter, optionID) VALUES (?, ?)');
                $stmt->execute([$username, $selected_option]);
            }
        }
    }
    header('Location: result.php?id=' . $poll_id);
    exit;
}
?>

<?=template_header('Poll Vote')?>

<div class="content poll-vote">
    <h2><?=htmlspecialchars($poll['title'], ENT_QUOTES)?></h2>

    <?php if ($poll_ended): ?>
        <p>The poll has ended. You can see the results below.</p>
        <button style="background-color: #266cc2;" onclick="showResults()">Result</button>
    <?php else: ?>
        <form action="vote.php?id=<?=$poll_id?>" method="post">
            <?php foreach ($questions as $question): ?>
                <h3><?=htmlspecialchars($question['questiontext'], ENT_QUOTES)?></h3>
                <?php
                $options = $all_options[$question['questionID']] ?? [];
                foreach ($options as $option): 
                ?>
                    <label>
                        <input type="radio" name="question_<?=$question['questionID']?>" value="<?=$option['optionID']?>"
                        <?=isset($user_votes[$question['questionID']]) && $user_votes[$question['questionID']] == $option['optionID'] ? 'checked' : ''?>
                        <?=isset($user_votes[$question['questionID']]) ? 'disabled' : ''?>
                        <?=!isset($_SESSION['username']) ? 'disabled' : '' ?>>  <!-- Disable if not logged in -->
                        <?=htmlspecialchars($option['optiontext'], ENT_QUOTES)?>
                    </label><br>
                <?php endforeach; ?>
            <?php endforeach; ?>

            <div>
                <button type="submit" <?=!empty($user_votes) || !isset($_SESSION['username']) ? 'disabled' : ''?>>Vote</button> <!-- Disable if not logged in or already voted -->
                <?php if (!$poll_ended): ?>
                    <button type="button" onclick="enableVote()">Change Vote</button>
                <?php endif; ?>
                <button style="background-color: #266cc2;" onclick="showResults()">Result</button>
            </div>
        </form>
    <?php endif; ?>
</div>

<script>
// Enable voting when the "Change Vote" button is clicked
function enableVote() {
    const radioButtons = document.querySelectorAll('input[type="radio"]');
    const voteButton = document.querySelector('button[type="submit"]');
    radioButtons.forEach(button => button.disabled = false);
    voteButton.disabled = false;
}

function showResults() {
    window.location.href = 'result.php?id=<?=$poll_id?>';
}
</script>
