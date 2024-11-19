<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include 'functions.php';
session_start();
$pdo = pdo_connect_mysql();
$msg = '';

if (isset($_GET['poll_id']) && !empty($_GET['poll_id'])) {
    $poll_id = $_GET['poll_id'];
} else {
    die('Poll ID is missing!');
}

if (isset($_GET['message'])) {
    $msg = htmlspecialchars($_GET['message']);
}

// Handle form submission for setting dates
if (!empty($_POST)) {
    $start_date = $_POST['start_date'] ?? '';
    $end_date = $_POST['end_date'] ?? '';

    if (!empty($start_date) && !empty($end_date)) {
        // Update poll start and end dates
        $stmt = $pdo->prepare('UPDATE polls SET startdate = ?, enddate = ? WHERE pollID = ?');
        $stmt->execute([$start_date, $end_date, $poll_id]);
        $msg = 'Poll dates set successfully!';
    } else {
        $msg = 'Error: Please fill out both dates!';
    }

    
    header("Location: set_dates.php?poll_id=$poll_id&message=" . urlencode($msg));
    exit;
}

// Generate the shareable link
$base_url = $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']);
$shareable_link = $base_url . '/vote.php?id=' . $poll_id;
?>

<?=template_header('Set Poll Dates')?>

<div class="content update">
    <h2>Set Start and End Date</h2>
    <form action="set_dates.php?poll_id=<?=$poll_id?>" method="post">
        <label for="start_date">Start Date</label>
        <input type="datetime-local" name="start_date" id="start_date" required>

        <label for="end_date">End Date</label>
        <input type="datetime-local" name="end_date" id="end_date" required>

        <button type="submit">Set Dates</button>
    </form>

    <!-- Display the message here -->
    <?php if ($msg): ?>
        <p style="color: green;"><?=$msg?></p>
    <?php endif; ?>

    <!-- Shareable link section -->
    <h3>Shareable Link</h3>
    <p>Copy and share this link for the poll:</p>
    <input type="text" value="<?=$shareable_link?>" readonly style="width: 100%; padding: 5px;">

</div>

