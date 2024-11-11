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
        // MySQL Query that will get all the answers from the "poll_answers" table
        $stmt = $pdo->prepare('SELECT * FROM Answers WHERE poll_id = ? ORDER BY votes DESC');
        $stmt->execute([ $_GET['id'] ]);
        // Fetch all poll answers
        $poll_answers = $stmt->fetchAll(PDO::FETCH_ASSOC);
        // Total number of votes, will be used to calculate the percentage
        $total_votes = 0;
        foreach ($poll_answers as $poll_answer) {
            // Every poll answers votes will be added to total votes
            $total_votes += $poll_answer['votes'];
        }
    } else {
        exit('Poll with that ID does not exist.');
    }
} else {
    exit('No poll ID specified.');
}
?>

<?=template_header('Poll Results')?>

<div class="content poll-result">

    <h2><?=htmlspecialchars($poll['title'], ENT_QUOTES)?></h2>
    
    <p><?=htmlspecialchars($poll['description'], ENT_QUOTES)?></p>

    <div class="wrapper">
        <?php foreach ($poll_answers as $poll_answer): ?>
        <div class="poll-question">
            <p><?=htmlspecialchars($poll_answer['title'], ENT_QUOTES)?> <span>(<?=$poll_answer['votes']?> Votes)</span></p>
            <div class="result-bar-wrapper">
                <div class="result-bar" style="width:___PHP6___%">
                    <?=$poll_answer['votes'] ? round(($poll_answer['votes']/$total_votes)*100) : 0?>%
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

</div>

<?=template_footer()?>