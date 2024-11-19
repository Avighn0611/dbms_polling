<?php
include 'functions.php'; // Include the functions.php file for header and footer templates

// Start session
session_start();

// Connect to the database
$pdo = pdo_connect_mysql();

$error = ''; // Error message
$success = ''; // Success message

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Collect form data
    $firstname = $_POST['firstname'] ?? '';
    $lastname = $_POST['lastname'] ?? '';
    $age = $_POST['age'] ?? '';
    $gender = $_POST['gender'] ?? '';
    $state = $_POST['state'] ?? '';
    $city = $_POST['city'] ?? '';
    $country = $_POST['country'] ?? '';
    $phoneno = $_POST['phoneno'] ?? '';
    $email = $_POST['email'] ?? '';
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    // Check if the username, email, or phone number already exists in the database
    $stmt = $pdo->prepare('SELECT COUNT(*) FROM users WHERE username = ? OR email = ? OR phoneno = ?');
    $stmt->execute([$username, $email, $phoneno]);
    $count = $stmt->fetchColumn();

    if ($count > 0) {
        // If a record already exists, display the error message and stay on this page
        $error = 'Username, Email, or Phone Number already registered!';
    } else {
        try {
            // Insert new user into the users table
            $stmt = $pdo->prepare('INSERT INTO users (firstname, lastname, age, gender, state, city, country, phoneno, email, username, password) 
                                   VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
            $stmt->execute([$firstname, $lastname, $age, $gender, $state, $city, $country, $phoneno, $email, $username, $password]);

            // If successful, display success message and redirect to login
            $success = 'Registered successfully!';
            header('Location: login.php');
            exit;

        } catch (PDOException $e) {
            // If query fails, display the error message
            $error = 'Error: ' . $e->getMessage();
        }
    }
}
?>

<?=template_header('Register')?>

<div class="content">
    <h2>Register</h2>
    <div class="register-container">
        <form action="signup.php" method="post" class="register-form">
            <div class="input-group">
                <label for="firstname">First Name:</label>
                <input type="text" name="firstname" id="firstname" required>
            </div>
            <div class="input-group">
                <label for="lastname">Last Name:</label>
                <input type="text" name="lastname" id="lastname" required>
            </div>
            <div class="input-group">
                <label for="age">Age:</label>
                <input type="number" name="age" id="age" required>
            </div>
            <div class="input-group">
                <label for="gender">Gender:</label>
                <select name="gender" id="gender" required>
                    <option value="">Select Gender</option>
                    <option value="Male">Male</option>
                    <option value="Female">Female</option>
                    <option value="Other">Other</option>
                </select>
            </div>
            <div class="input-group">
                <label for="state">State:</label>
                <input type="text" name="state" id="state" required>
            </div>
            <div class="input-group">
                <label for="city">City:</label>
                <input type="text" name="city" id="city" required>
            </div>
            <div class="input-group">
                <label for="country">Country:</label>
                <input type="text" name="country" id="country" required>
            </div>
            <div class="input-group">
                <label for="phoneno">Phone No:</label>
                <input type="text" name="phoneno" id="phoneno" required>
            </div>
            <div class="input-group">
                <label for="email">Email:</label>
                <input type="email" name="email" id="email" required>
            </div>
            <div class="input-group">
                <label for="username">Username:</label>
                <input type="text" name="username" id="username" required>
            </div>
            <div class="input-group">
                <label for="password">Password:</label>
                <input type="password" name="password" id="password" required>
            </div>
            <div class="input-group">
                <input type="submit" value="Register" class="btn-register">
            </div>
            
            <!-- Display error message if there is an issue with the query -->
            <?php if ($error): ?>
                <p class="error-msg"><?= $error ?></p>
            <?php endif; ?>
            
            <!-- Display success message if registration is successful -->
            <?php if ($success): ?>
                <p class="success-msg"><?= $success ?></p>
            <?php endif; ?>
        </form>
    </div>
</div>

<style>
/* Basic Reset */
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

/* Body and background setup */
body {
    font-family: 'Arial', sans-serif;
    background-color: #f4f7fc;
    color: #333;
    line-height: 1.6;
    padding: 0 20px;
    animation: fadeIn 1s ease-in-out;
}

/* Center the content */
.content {
    max-width: 600px;
    margin: 50px auto;
    background-color: #fff;
    padding: 20px;
    border-radius: 8px;
    box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
    animation: slideUp 0.6s ease-out;
}

/* Header */
h2 {
    text-align: center;
    margin-bottom: 20px;
    font-size: 24px;
    color: #333;
    animation: fadeInDown 0.5s ease-out;
}

/* Form Container */
.register-container {
    display: flex;
    flex-direction: column;
}

/* Input Group */
.input-group {
    margin-bottom: 20px;
    opacity: 0;
    animation: fadeInUp 0.5s forwards;
}

.input-group:nth-child(1) { animation-delay: 0.1s; }
.input-group:nth-child(2) { animation-delay: 0.2s; }
.input-group:nth-child(3) { animation-delay: 0.3s; }
.input-group:nth-child(4) { animation-delay: 0.4s; }
.input-group:nth-child(5) { animation-delay: 0.5s; }
.input-group:nth-child(6) { animation-delay: 0.6s; }
.input-group:nth-child(7) { animation-delay: 0.7s; }
.input-group:nth-child(8) { animation-delay: 0.8s; }
.input-group:nth-child(9) { animation-delay: 0.9s; }
.input-group:nth-child(10) { animation-delay: 1s; }

/* Input Fields and Select */
input[type="text"],
input[type="number"],
input[type="email"],
input[type="password"],
select {
    width: 100%;
    padding: 10px;
    font-size: 14px;
    border: 1px solid #ddd;
    border-radius: 4px;
    outline: none;
    transition: border-color 0.3s ease, box-shadow 0.3s ease;
}

input[type="text"]:focus,
input[type="number"]:focus,
input[type="email"]:focus,
input[type="password"]:focus,
select:focus {
    border-color: #007bff;
    box-shadow: 0 0 8px rgba(0, 123, 255, 0.3);
}

/* Submit Button */
.btn-register {
    background-color: #007bff;
    color: white;
    padding: 10px 20px;
    border: none;
    border-radius: 4px;
    font-size: 16px;
    cursor: pointer;
    transition: background-color 0.3s ease, transform 0.2s ease;
}

.btn-register:hover {
    background-color: #0056b3;
    transform: scale(1.05);
}

.btn-register:active {
    transform: scale(0.98);
}

/* Error and Success Messages */
.error-msg,
.success-msg {
    text-align: center;
    font-size: 16px;
    margin-top: 20px;
    padding: 10px;
    border-radius: 4px;
    width: 100%;
    opacity: 0;
    animation: fadeIn 0.6s forwards;
}

.error-msg {
    background-color: #f8d7da;
    color: #721c24;
    animation-delay: 0.3s;
}

.success-msg {
    background-color: #d4edda;
    color: #155724;
    animation-delay: 0.3s;
}

/* Focused label styling */
.input-group input:focus + label,
.input-group select:focus + label {
    color: #007bff;
}

/* Fade In Animation */
@keyframes fadeIn {
    0% {
        opacity: 0;
    }
    100% {
        opacity: 1;
    }
}

/* Fade In Down Animation */
@keyframes fadeInDown {
    0% {
        transform: translateY(-20px);
        opacity: 0;
    }
    100% {
        transform: translateY(0);
        opacity: 1;
    }
}

/* Slide Up Animation */
@keyframes slideUp {
    0% {
        transform: translateY(30px);
        opacity: 0;
    }
    100% {
        transform: translateY(0);
        opacity: 1;
    }
}

/* Fade In Up Animation */
@keyframes fadeInUp {
    0% {
        transform: translateY(20px);
        opacity: 0;
    }
    100% {
        transform: translateY(0);
        opacity: 1;
    }
}

/* Media Queries for Mobile Responsiveness */
@media (max-width: 768px) {
    .content {
        padding: 15px;
    }

    h2 {
        font-size: 20px;
    }

    .input-group label,
    .input-group input,
    .input-group select {
        font-size: 14px;
    }
}

</style>
