<?php
include 'functions.php';
// Connect to MySQL
$pdo = pdo_connect_mysql();
// If the GET request "id" exists (poll id)...
if (isset($_GET['id'])) {
    // MySQL query that selects the poll records by the GET request "id"
    $stmt = $pdo->prepare('SELECT * FROM Questions WHERE id = ?');
    $stmt->execute([ $_GET['id'] ]);
    // Fetch the record
    $poll = $stmt->fetch(PDO::FETCH_ASSOC);
    // Check if the poll record exists with the id specified
    if ($poll) {
        // MySQL query that selects all the poll answers
        $stmt = $pdo->prepare('SELECT * FROM Answers WHERE poll_id = ?');
        $stmt->execute([ $_GET['id'] ]);
        // Fetch all the poll anwsers
        $poll_answers = $stmt->fetchAll(PDO::FETCH_ASSOC);
        // If the user clicked the "Vote" button...
        if (isset($_POST['poll_answer'])) {
            // Update and increase the vote for the answer the user voted for
            $stmt = $pdo->prepare('UPDATE Answers SET votes = votes + 1 WHERE id = ?');
            $stmt->execute([ $_POST['poll_answer'] ]);
            // Redirect user to the result page
            header ('Location: result.php?id=' . $_GET['id']);
            exit;
        }
    } else {
        exit('Poll with that ID does not exist.');
    }
} else {
    exit('No poll ID specified.');
}
?>

<?=template_header('Poll Vote')?>

<div class="content poll-vote">

    <h2><?=htmlspecialchars($poll['title'], ENT_QUOTES)?></h2>

    <p><?=htmlspecialchars($poll['description'], ENT_QUOTES)?></p>

    <form action="vote.php?id=<?=$_GET['id']?>" method="post">
        <?php for ($i = 0; $i < count($poll_answers); $i++): ?>
        <label>
            <input type="radio" name="poll_answer" value="<?=$poll_answers[$i]['id']?>"<?=$i == 0 ? ' checked' : ''?>>
            <?=htmlspecialchars($poll_answers[$i]['title'], ENT_QUOTES)?>
        </label>
        <?php endfor; ?>
        <div>
            <button type="submit">Vote</button>
            <a href="result.php?id=<?=$poll['id']?>">View Result</a>
        </div>
    </form>

</div>

<?=template_footer()?>
