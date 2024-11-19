<?php
session_start();
include 'functions.php';

$error = ''; // Initialize the $error variable

if ($_SERVER['REQUEST_METHOD'] === 'POST') { 
    $username = $_POST['username'] ?? ''; 
    $password = $_POST['password'] ?? ''; 

    // Check credentials
    $stmt = $pdo->prepare('SELECT * FROM users WHERE username = ? AND password = ?');
    $stmt->execute([$username, $password]); 
    $user = $stmt->fetch(PDO::FETCH_ASSOC); 

    if ($user) { 
        // Store user details in the session
        $_SESSION['username'] = $user['username']; 
        $_SESSION['firstname'] = $user['firstname']; 
        $_SESSION['lastname'] = $user['lastname']; 

        // Insert login event into user_activity table 
        $stmt = $pdo->prepare('INSERT INTO user_activity (pollID, username, action_type) VALUES (?, ?, ?)');
        $stmt->execute([null, $user['username'], 'login']);

        // Handle redirection after login 
        $redirect = $_GET['redirect'] ?? 'index.php';
        header("Location: $redirect");
        exit; 
    } else {
        $error = 'Invalid credentials!'; 
    }
}
?>
<?=template_header('Login')?>

<?php if (isset($_GET['message']) && $_GET['message'] === 'loggedout'): ?>
    <p class="success-msg" style='color:#ffffff'>You have been logged out successfully!</p>
<?php endif; ?>

<div id='content' class="content">
    <h2>Login</h2>
    <div id="login-container" class="login-container">
        <form action="login.php<?= isset($_GET['redirect']) ? '?redirect=' . htmlspecialchars($_GET['redirect'], ENT_QUOTES) : '' ?>" method="post" class="login-form">
            <div class="input-group">
                <label for="username">Username:</label>
                <input type="text" name="username" id="username" placeholder="Enter your username" required>
            </div>
            <div class="input-group">
                <label for="password">Password:</label>
                <input type="password" name="password" id="password" placeholder="Enter your password" required>
            </div>
            <div class="input-group">
                <input type="submit" value="LOGIN" class="btn-login">
            </div>
            <!-- Link to signup page if not signed in -->
            <div class="input-group">
                <a href="signup.php">Not signed in? Sign up here.</a>
            </div>
            <!-- Display error message if credentials are not matched -->
            <?php if (!empty($error)): ?>
                <p class="error-msg"><?= htmlspecialchars($error, ENT_QUOTES) ?></p>
            <?php endif; ?>
        </form>
    </div>
</div>

<script>
    // Hide the login form if the logout message is present
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.get('message') === 'loggedout') {
        document.getElementById('content').style.display = 'none';
    }
</script>

<style>
    /* Global Styles */
* {
    box-sizing: border-box;
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    margin: 0;
    padding: 0;
}

body {
    background: linear-gradient(to bottom, #2873cf, #4b93e2);
    display: flex;
    justify-content: center;
    align-items: center;
    min-height: 100vh;
    margin: 0;
}

/* Container for Login/Signup Form */
.content {
    width: 100%;
    max-width: 380px;
    padding: 30px;
    background-color: #fff;
    border-radius: 10px;
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.15);
    text-align: center;
    animation: fadeIn 0.5s ease-in-out;
}

/* Header */
h2 {
    font-size: 24px;
    font-weight: 600;
    color: #333;
    margin-bottom: 20px;
}

/* Form Group */
.input-group {
    margin-bottom: 20px;
    text-align: left;
}

.input-group label {
    font-size: 14px;
    color: #666;
    margin-bottom: 8px;
    display: block;
}

.input-group input[type="text"],
.input-group input[type="password"] {
    width: 100%;
    padding: 10px 12px;
    font-size: 14px;
    border: 1px solid #ddd;
    border-radius: 5px;
    outline: none;
    transition: all 0.3s;
}

.input-group input:focus {
    border-color: #2873cf;
    box-shadow: 0 0 4px rgba(40, 115, 207, 0.3);
}

/* Buttons */
.btn-login {
    width: 100%;
    padding: 12px 0;
    background: #2873cf;
    color: #fff;
    font-size: 16px;
    font-weight: bold;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    transition: background 0.3s;
}

.btn-login:hover {
    background: #266cc2;
}

/* Links */
.input-group a {
    display: block;
    margin-top: 10px;
    font-size: 14px;
    color: #2873cf;
    text-decoration: none;
}

.input-group a:hover {
    text-decoration: underline;
}

/* Error and Success Messages */
.success-msg {
    font-size: 14px;
    color: #37b770;
    margin-bottom: 20px;
    text-align: center;
}

.error-msg {
    font-size: 14px;
    color: #b73737;
    margin-top: 10px;
    text-align: center;
}

/* Animation */
@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateY(-20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

</style>