<?php
include 'functions.php';
$pdo = pdo_connect_mysql();

if (isset($_GET['id'])) {
    $stmt = $pdo->prepare('SELECT * FROM questions WHERE questionID = ?');
    $stmt->execute([$_GET['id']]);
    $question = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($question) {
        // Fetch option votes
        $stmt = $pdo->prepare('
            SELECT 
                o.optiontext, 
                COUNT(v.voteID) AS votes 
            FROM options o
            LEFT JOIN votes v ON o.optionID = v.optionID
            WHERE o.questionID = ?
            GROUP BY o.optionID
        ');
        $stmt->execute([$question['questionID']]);
        $options = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Fetch votes by gender
        $stmt = $pdo->prepare('
            SELECT 
                o.optiontext, 
                u.gender, 
                COUNT(v.voteID) AS votes 
            FROM options o
            LEFT JOIN votes v ON o.optionID = v.optionID
            LEFT JOIN users u ON v.voter = u.username
            WHERE o.questionID = ?
            GROUP BY o.optionID, u.gender
        ');
        $stmt->execute([$question['questionID']]);
        $gender_votes = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Fetch votes by age
        $stmt = $pdo->prepare('
            SELECT 
                o.optiontext, 
                u.age, 
                COUNT(v.voteID) AS votes 
            FROM options o
            LEFT JOIN votes v ON o.optionID = v.optionID
            LEFT JOIN users u ON v.voter = u.username
            WHERE o.questionID = ?
            GROUP BY o.optionID, u.age
        ');
        $stmt->execute([$question['questionID']]);
        $age_votes = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Fetch votes by city
        $stmt = $pdo->prepare('
            SELECT 
                o.optiontext, 
                u.city, 
                COUNT(v.voteID) AS votes 
            FROM options o
            LEFT JOIN votes v ON o.optionID = v.optionID
            LEFT JOIN users u ON v.voter = u.username
            WHERE o.questionID = ?
            GROUP BY o.optionID, u.city
        ');
        $stmt->execute([$question['questionID']]);
        $city_votes = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Fetch votes by state
        $stmt = $pdo->prepare('
            SELECT 
                o.optiontext, 
                u.state, 
                COUNT(v.voteID) AS votes 
            FROM options o
            LEFT JOIN votes v ON o.optionID = v.optionID
            LEFT JOIN users u ON v.voter = u.username
            WHERE o.questionID = ?
            GROUP BY o.optionID, u.state
        ');
        $stmt->execute([$question['questionID']]);
        $state_votes = $stmt->fetchAll(PDO::FETCH_ASSOC);

    } else {
        exit('Question with that ID does not exist.');
    }
} else {
    exit('No question ID specified.');
}
?>

<?=template_header('Question Analysis')?>

<div class="content poll-analysis">
    <h2>Analysis for: <?=htmlspecialchars($question['questiontext'], ENT_QUOTES)?></h2>

    <div class="chart-container">
        <canvas id="pie-chart"></canvas>
    </div>

    <div class="chart-container">
        <canvas id="gender-chart"></canvas>
    </div>

    <div class="chart-container">
        <canvas id="age-chart"></canvas>
    </div>

    <div class="chart-container">
        <canvas id="city-chart"></canvas>
    </div>

    <div class="chart-container">
        <canvas id="state-chart"></canvas>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
const options = <?=json_encode($options)?>;
const labels = options.map(option => option.optiontext);
const data = options.map(option => option.votes);

// Pie chart for overall votes
const ctx = document.getElementById('pie-chart').getContext('2d');
new Chart(ctx, {
    type: 'pie',
    data: {
        labels: labels,
        datasets: [{
            data: data,
            backgroundColor: ['#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0', '#9966FF']
        }]
    },
    options: {
        plugins: {
            legend: {
                position: 'bottom'
            }
        }
    }
});

// Bar charts for gender, age, city, and state
const genderVotes = <?=json_encode($gender_votes)?>;
const ageVotes = <?=json_encode($age_votes)?>;
const cityVotes = <?=json_encode($city_votes)?>;
const stateVotes = <?=json_encode($state_votes)?>;

function createBarChart(ctx, title, labels, datasets) {
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: datasets
        },
        options: {
            plugins: {
                legend: {
                    position: 'bottom'
                },
                title: {
                    display: true,
                    text: title
                }
            }
        }
    });
}

// Gender Bar Chart
const genderCtx = document.getElementById('gender-chart').getContext('2d');
const genderLabels = [...new Set(genderVotes.map(vote => vote.gender))];
const genderDatasets = labels.map(option => {
    return {
        label: option,
        data: genderLabels.map(label => {
            const vote = genderVotes.find(v => v.optiontext === option && v.gender === label);
            return vote ? vote.votes : 0;
        }),
        backgroundColor: '#36A2EB'
    };
});
createBarChart(genderCtx, 'Votes by Gender', genderLabels, genderDatasets);

// Age Bar Chart
const ageCtx = document.getElementById('age-chart').getContext('2d');
const ageLabels = [...new Set(ageVotes.map(vote => vote.age))];
const ageDatasets = labels.map(option => {
    return {
        label: option,
        data: ageLabels.map(label => {
            const vote = ageVotes.find(v => v.optiontext === option && v.age === label);
            return vote ? vote.votes : 0;
        }),
        backgroundColor: '#FFCE56'
    };
});
createBarChart(ageCtx, 'Votes by Age', ageLabels, ageDatasets);

// City Bar Chart
const cityCtx = document.getElementById('city-chart').getContext('2d');
const cityLabels = [...new Set(cityVotes.map(vote => vote.city))];
const cityDatasets = labels.map(option => {
    return {
        label: option,
        data: cityLabels.map(label => {
            const vote = cityVotes.find(v => v.optiontext === option && v.city === label);
            return vote ? vote.votes : 0;
        }),
        backgroundColor: '#FF6384'
    };
});
createBarChart(cityCtx, 'Votes by City', cityLabels, cityDatasets);

// State Bar Chart
const stateCtx = document.getElementById('state-chart').getContext('2d');
const stateLabels = [...new Set(stateVotes.map(vote => vote.state))];
const stateDatasets = labels.map(option => {
    return {
        label: option,
        data: stateLabels.map(label => {
            const vote = stateVotes.find(v => v.optiontext === option && v.state === label);
            return vote ? vote.votes : 0;
        }),
        backgroundColor: '#4BC0C0'
    };
});
createBarChart(stateCtx, 'Votes by State', stateLabels, stateDatasets);
</script>


