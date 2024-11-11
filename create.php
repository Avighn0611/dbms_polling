<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
?>
<?php
include 'functions.php';
// Connect to MySQL
$pdo = pdo_connect_mysql();
// Output message
$msg = '';
// Check if POST data is not empty
if (!empty($_POST)) {
    // Post data not empty insert a new record
    // Check if POST variable "title" exists, if not default the value to blank, basically the same for all variables
    $title = isset($_POST['title']) ? $_POST['title'] : '';
    $desc = isset($_POST['desc']) ? $_POST['desc'] : '';
    // Insert new record into the "polls" table
    $stmt = $pdo->prepare('INSERT INTO Questions (title, description) VALUES (?, ?)');
    $stmt->execute([ $title, $desc ]);
    // Below will get the last insert ID, this will be the poll id
    $poll_id = $pdo->lastInsertId();
    // Get the answers and convert the multiline string to an array, so we can add each answer to the "poll_answers" table
    $answers = isset($_POST['answers']) ? explode(PHP_EOL, $_POST['answers']) : '';
    // Iterate the answers and insert into the "poll_answers" table
    foreach ($answers as $answer) {
        // If the answer is empty there is no need to insert
        if (empty($answer)) continue;
        // Add answer to the "poll_answers" table
        $stmt = $pdo->prepare('INSERT INTO Answers (poll_id, title, votes) VALUES (?, ?, 0)');
        $stmt->execute([ $poll_id, $answer ]);
    }
    // Output message
    $msg = 'Created Successfully!';
}
?>


<?=template_header('Create Poll')?>

<div class="content update">

    <h2>Create Poll</h2>

    <form action="create.php" method="post">

        <label for="title">Title</label>
        <input type="text" name="title" id="title" placeholder="Title">

        <label for="desc">Description</label>
        <input type="text" name="desc" id="desc" placeholder="Description">

        <label for="answers">Answer Options (per line)</label>
        <textarea name="answers" id="answers" placeholder="Option 1<?=PHP_EOL?>Option 2<?=PHP_EOL?>Option 3"></textarea>

        <button type="submit">Create</button>

    </form>
    <?php if ($msg): ?>
    <p><?=$msg?></p>
    <?php endif; ?>
</div>

<?=template_footer()?>