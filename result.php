<?php
include 'functions.php';

// Connect to MySQL
$pdo = pdo_connect_mysql();

// Check if poll ID is provided
if (isset($_GET['id'])) {
    // Fetch the poll details
    $stmt = $pdo->prepare('SELECT * FROM polls WHERE pollID = ?');
    $stmt->execute([$_GET['id']]);
    $poll = $stmt->fetch(PDO::FETCH_ASSOC);

    // Check if the poll exists
    if ($poll) {
        // Fetch all questions related to the poll
        $stmt = $pdo->prepare('SELECT * FROM questions WHERE pollID = ?');
        $stmt->execute([$_GET['id']]);
        $questions = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $all_results = [];
        foreach ($questions as $question) {
            $question_id = $question['questionID'];

            // Fetch options and votes for this question
            $stmt = $pdo->prepare('
                SELECT 
                    o.optionID, 
                    o.optiontext, 
                    COUNT(v.voteID) AS votes, 
                    (COUNT(v.voteID) * 100 / 
                     (SELECT COUNT(*) FROM votes v2 
                      JOIN options o2 ON v2.optionID = o2.optionID 
                      WHERE o2.questionID = ?)
                    ) AS percentage
                FROM options o
                LEFT JOIN votes v ON o.optionID = v.optionID
                WHERE o.questionID = ?
                GROUP BY o.optionID
            ');
            $stmt->execute([$question_id, $question_id]);
            $options = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $all_results[] = [
                'question' => $question,
                'options' => $options
            ];
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

    <?php foreach ($questions as $index => $question): ?>
        <div class="poll-question">
            <h3>Question <?=($index + 1)?>: <?=htmlspecialchars($question['questiontext'], ENT_QUOTES)?></h3>

            <div class="result-bar-wrapper">
                <?php foreach ($all_results[$index]['options'] as $option):
                    $percentage = round($option['percentage'], 2) ?? 0;
                ?>
                    <div class="poll-option">
                        <p><?=htmlspecialchars($option['optiontext'], ENT_QUOTES)?> (<?=$option['votes']?> votes)</p>
                        <div class="result-bar" style="width: <?= $percentage ?>%; background-color: #4CAF50;">
                            <?= $percentage ?>%
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <a href="analysis.php?id=<?=htmlspecialchars($question['questionID'], ENT_QUOTES)?>" class="analysis-button">Analysis</a>
        </div>
    <?php endforeach; ?>
</div>

<style>
.result-bar-wrapper {
    margin: 10px 0;
}

.poll-option {
    margin: 5px 0;
}

.result-bar {
    height: 20px;
    background-color: #4CAF50;
    text-align: center;
    color: white;
    line-height: 20px;
    font-size: 12px;
    margin-top: 5px;
}

.analysis-button {
    background-color: #007BFF;
    color: white;
    border: none;
    padding: 10px 20px;
    margin-top: 15px;
    cursor: pointer;
    font-size: 14px;
    border-radius: 5px;
    transition: background-color 0.3s;
    text-decoration: none;
    display: inline-block;
}

.analysis-button:hover {
    background-color: #0056b3;
}
</style>
