// Script for managing poll creation
let questionCount = 0;
let currentQuestionIndex = 0;

function showQuestionCountInput() {
    const pollType = document.getElementById('pollType').value;
    const questionCountInput = document.getElementById('questionCountInput');
    const questionsSection = document.getElementById('questionsSection');
    const questionFields = document.getElementById('questionFields');

    questionFields.innerHTML = ''; // Reset question fields

    if (pollType === 'multiple' || pollType === 'multiple_select') {
        questionCountInput.style.display = 'block'; // Show question count input
        questionsSection.style.display = 'none'; // Hide questions section until count is entered
    } else if (pollType === 'single') {
        questionCountInput.style.display = 'none'; // Hide count input
        questionsSection.style.display = 'block'; // Show single question section
        questionFields.innerHTML = `
            <div class="question-block">
                <label for="singleQuestion">Question:</label>
                <input type="text" id="singleQuestion" name="questions[0][text]" placeholder="Enter question text" required>
                <label for="singleOptions">Options:</label>
                <textarea id="singleOptions" name="questions[0][options]" placeholder="Option 1\nOption 2\nOption 3"></textarea>
            </div>
        `;
        document.getElementById('nextButton').style.display = 'none';
        document.getElementById('prevButton').style.display = 'none';
        document.getElementById('submitButton').style.display = 'inline';
    } else {
        questionCountInput.style.display = 'none';
        questionsSection.style.display = 'none';
    }
}

function generateQuestionFields() {
    questionCount = parseInt(document.getElementById('questionCount').value, 10);
    const questionFields = document.getElementById('questionFields');
    questionFields.innerHTML = ''; // Clear existing questions

    for (let i = 0; i < questionCount; i++) {
        const questionDiv = document.createElement('div');
        questionDiv.classList.add('question-block');
        questionDiv.setAttribute('data-index', i); // Index for navigation
        questionDiv.style.display = i === 0 ? 'block' : 'none'; // Show only the first question
        questionDiv.innerHTML = `
            <label for="question_${i}">Question ${i + 1}:</label>
            <input type="text" id="question_${i}" name="questions[${i}][text]" placeholder="Enter question text" required>
            <label for="options_${i}">Options:</label>
            <textarea id="options_${i}" name="questions[${i}][options]" placeholder="Option 1\nOption 2\nOption 3"></textarea>
        `;
        questionFields.appendChild(questionDiv);
    }

    currentQuestionIndex = 0; // Reset navigation index
    document.getElementById('questionsSection').style.display = 'block';
    updateNavigationButtons();
}

function nextQuestion() {
    const questionFields = document.getElementById('questionFields');
    const allQuestions = questionFields.children;

    if (currentQuestionIndex < questionCount - 1) {
        allQuestions[currentQuestionIndex].style.display = 'none'; // Hide current question
        currentQuestionIndex++;
        allQuestions[currentQuestionIndex].style.display = 'block'; // Show next question
    }

    updateNavigationButtons();
}

function prevQuestion() {
    const questionFields = document.getElementById('questionFields');
    const allQuestions = questionFields.children;

    if (currentQuestionIndex > 0) {
        allQuestions[currentQuestionIndex].style.display = 'none'; // Hide current question
        currentQuestionIndex--;
        allQuestions[currentQuestionIndex].style.display = 'block'; // Show previous question
    }

    updateNavigationButtons();
}

function updateNavigationButtons() {
    document.getElementById('prevButton').style.display = currentQuestionIndex === 0 ? 'none' : 'inline';
    document.getElementById('nextButton').style.display = currentQuestionIndex === questionCount - 1 ? 'none' : 'inline';
    document.getElementById('submitButton').style.display = currentQuestionIndex === questionCount - 1 ? 'inline' : 'none';
}
