<?php
session_start();
include 'db.php'; // Includes the database connection

$message = '';

// Check if user is already logged in
if (isset($_SESSION['user_id'])) {
    // Use JS for client-side redirection as requested
    echo '<script>window.location.href = "dashboard.php";</script>';
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (empty($email) || empty($password)) {
        $message = '<div class="error-message">Please enter both email and password.</div>';
    } else {
        // 1. Prepare and execute SQL to fetch user by email
        $stmt = $conn->prepare("SELECT user_id, name, password_hash FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            
            // 2. Verify the hashed password
            if (password_verify($password, $user['password_hash'])) {
                // Login successful! Set session variables
                $_SESSION['user_id'] = $user['user_id'];
                $_SESSION['user_name'] = $user['name'];
                
                $message = '<div class="success-message">Login successful! Redirecting to dashboard...</div>';
                
                // Use JS for client-side redirection
                echo '<script>setTimeout(function(){ window.location.href = "dashboard.php"; }, 1500);</script>';
                $stmt->close();
                close_db_connection($conn);
                exit();
            } else {
                // Password does not match
                $message = '<div class="error-message">Invalid email or password.</div>';
            }
        } else {
            // No user found with that email
            $message = '<div class="error-message">Invalid email or password.</div>';
        }
        $stmt->close();
    }
}

// Check for redirect message from index.php or other pages (e.g., a "you must log in" message)
$status_message = '';
if (isset($_GET['status']) && $_GET['status'] === 'loggedout') {
    $status_message = '<div class="success-message">You have been logged out successfully.</div>';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Log In - SchedulerClone</title>
    <style>
        :root {
            --primary-color: #0069ff;
            --text-color: #333;
            --light-grey: #e8e8e8;
            --success-color: #28a745;
            --error-color: #dc3545;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: sans-serif;
        }

        body {
            background-color: #f6f8fa;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }

        .login-container {
            width: 100%;
            max-width: 400px;
            padding: 40px;
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        .logo {
            font-size: 28px;
            font-weight: 700;
            color: var(--primary-color);
            text-align: center;
            margin-bottom: 25px;
        }

        h2 {
            font-size: 24px;
            text-align: center;
            margin-bottom: 25px;
            color: var(--text-color);
        }

        .form-group {
            margin-bottom: 15px;
        }

        label {
            display: block;
            margin-bottom: 5px;
            font-weight: 600;
            font-size: 14px;
        }

        input[type="email"],
        input[type="password"] {
            width: 100%;
            padding: 12px;
            border: 1px solid var(--light-grey);
            border-radius: 6px;
            font-size: 16px;
            transition: border-color 0.2s;
        }

        input:focus {
            outline: none;
            border-color: var(--primary-color);
        }

        .submit-btn {
            width: 100%;
            background-color: var(--primary-color);
            color: white;
            padding: 12px;
            border: none;
            border-radius: 6px;
            font-size: 18px;
            font-weight: 600;
            cursor: pointer;
            margin-top: 20px;
            transition: background-color 0.3s;
        }

        .submit-btn:hover {
            background-color: #0052cc;
        }
        
        .signup-link {
            text-align: center;
            margin-top: 20px;
            font-size: 14px;
        }

        .signup-link a {
            color: var(--primary-color);
            text-decoration: none;
            font-weight: 600;
        }
        
        .error-message, .success-message {
            padding: 10px;
            margin-bottom: 15px;
            border-radius: 5px;
            font-size: 14px;
            text-align: center;
        }

        .error-message {
            background-color: #f8d7da;
            color: var(--error-color);
            border: 1px solid #f5c6cb;
        }
        
        .success-message {
            background-color: #d4edda;
            color: var(--success-color);
            border: 1px solid #c3e6cb;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="logo">SchedulerClone</div>
        <h2>Log in to your account</h2>

        <?php echo $status_message; // For logged out status ?>
        <?php echo $message; ?>

        <form method="POST">
            <div class="form-group">
                <label for="email">Email Address</label>
                <input type="email" id="email" name="email" required value="<?php echo htmlspecialchars($email ?? ''); ?>">
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required>
            </div>
            <button type="submit" class="submit-btn">Log In</button>
        </form>
        
        <div class="signup-link">
            Don't have an account? 
            <a href="signup.php" onclick="redirectToSignup(event)">Sign Up</a>
        </div>
    </div>
    
    <script>
        // JS Redirection for the signup link
        function redirectToSignup(event) {
            event.preventDefault(); // Stop default anchor behavior
            window.location.href = "signup.php";
        }
    </script>
</body>
</html>
