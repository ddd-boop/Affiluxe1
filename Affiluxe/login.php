<?php
session_start();
$error_message = '';

// Handle AJAX login request
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['ajax'])) {
    header('Content-Type: application/json');

    // Database connection details
    $servername = "fdb1029.awardspace.net";
    $username = "4567167_affiluxe"; 
    $password = "7YxdH0s*4P59pmqw"; 
    $dbname = "4567167_affiluxe";

    // Create connection
    $conn = new mysqli($servername, $username, $password, $dbname);

    // Check connection
    if ($conn->connect_error) {
        echo json_encode(['success' => false, 'message' => 'Connection failed.']);
        exit();
    }

    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    // Check for special admin login
    if ($email === 'earnmart' && $password === 'ogoniman') {
        $_SESSION['user_id'] = 'admin';
        $_SESSION['role'] = 'admin';
        echo json_encode(['success' => true, 'redirect' => 'withdraw.php']);
        exit();
    }

    // Fetch user ID, hashed password, and role for regular users
    $stmt = $conn->prepare("SELECT id, password, role FROM users WHERE email = ?");
    if (!$stmt) {
        echo json_encode(['success' => false, 'message' => 'Database error.']);
        exit();
    }
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $stmt->bind_result($id, $hashed_password, $role);
        $stmt->fetch();
        if (password_verify($password, $hashed_password)) {
            // Password is correct, set session variables for regular user
            $_SESSION['user_id'] = $id;
            $_SESSION['role'] = $role;

            // Regenerate session ID to prevent session fixation
            session_regenerate_id(true);

            // Return success response with redirect URL based on role
            if ($role === 'admin') {
                echo json_encode(['success' => true, 'redirect' => 'withdraw.php']);
            } else {
                echo json_encode(['success' => true, 'redirect' => 'dashboard.php']);
            }
            exit();
        } else {
            // Invalid password
            echo json_encode(['success' => false, 'message' => 'Invalid password.']);
            exit();
        }
    } else {
        // No user found with that email
        echo json_encode(['success' => false, 'message' => 'No user found with that email.']);
        exit();
    }

    $stmt->close();
    $conn->close();
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<link rel="icon" href="images/earn.jpeg"/>
<title>EarnMart Login</title>
<link rel="stylesheet" href="css/login.css" />
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<style>
  .error {
    color: #ff0000;
    text-align: center;
    margin-bottom: 10px;
  }
</style>
</head>
<body>
<div class="background-shapes">
    <div class="shape"></div>
    <div class="shape"></div>
</div>

<div class="login-container">
    <form id="loginForm" class="login-form" method="POST" action="login.php">
        <div class="eye-container">
            <div class="eye">
                <div class="pupil"></div>
                <div class="eyelid upper-lid"></div>
                <div class="eyelid lower-lid"></div>
            </div>
        </div>
        <h1>Welcome Back</h1>
        <p class="subtitle">Login to your account</p>

        <p class="error" id="errorMessage"></p>

        <div class="input-group">
            <input type="text" id="email" name="email" required placeholder=" " />
            <label for="email">Email</label>
        </div>

        <div class="input-group">
            <input type="password" id="password" name="password" required placeholder=" " />
            <label for="password">Password</label>
        </div>

        <button type="submit">Login</button>

        <p class="signup-link">
            Don't have an account? <a href="signup.php">Sign Up</a>
        </p>
    </form>
</div>

<script>
$(document).ready(function() {
    $('#loginForm').on('submit', function(e) {
        e.preventDefault();

        const email = $('#email').val();
        const password = $('#password').val();

        $('#errorMessage').text('');

        $.ajax({
            url: 'login.php',
            type: 'POST',
            dataType: 'json',
            data: {
                ajax: true,
                email: email,
                password: password
            },
            success: function(response) {
                if (response.success) {
                    window.location.href = response.redirect;
                } else {
                    $('#errorMessage').text(response.message);
                }
            },
            error: function() {
                $('#errorMessage').text('An error occurred. Please try again.');
            }
        });
    });
});
</script>
</body>
</html>

