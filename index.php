<?php
// CRITICAL 1: Start session at the very beginning
session_start();

// CRITICAL FIX: Ensure any lingering session data is cleared 
// if the user is *not* meant to be logged in right now, preventing false redirects.
if (!isset($_SESSION['user_id']) || empty($_SESSION['user_id'])) {
    $_SESSION = array(); // Clear array if no valid ID
}

// FIX: Check if user is logged in AND their session variable is valid.
if (isset($_SESSION['user_id']) && !empty($_SESSION['user_id'])) {
    // If a valid user ID is found in the session, they go to the dashboard.
    echo '<script>window.location.href = "dashboard.php";</script>';
    exit(); // CRITICAL: Stop execution after redirection
}

// If no valid session, the script falls through to render the homepage HTML.
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>The Ultimate Scheduler Clone</title>
    <style>
        :root {
            --primary-color: #0069ff;
            --secondary-color: #f6f8fa;
            --text-color: #333;
            --light-grey: #e8e8e8;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: sans-serif;
        }

        body {
            background-color: #fff;
            color: var(--text-color);
            line-height: 1.6;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 20px 5%;
            border-bottom: 1px solid var(--light-grey);
        }

        .logo {
            font-size: 24px;
            font-weight: 700;
            color: var(--primary-color);
            text-decoration: none;
        }

        .auth-buttons a {
            text-decoration: none;
            color: var(--text-color);
            padding: 8px 15px;
            margin-left: 10px;
            border-radius: 5px;
            font-weight: 600;
            transition: all 0.3s ease;
            cursor: pointer;
        }

        .auth-buttons .login-btn {
            border: 1px solid var(--light-grey);
        }

        .auth-buttons .signup-btn {
            background-color: var(--primary-color);
            color: white;
            border: 1px solid var(--primary-color);
        }

        .auth-buttons .signup-btn:hover {
            background-color: #0052cc;
        }

        .hero {
            padding: 80px 5%;
            display: flex;
            align-items: center;
            justify-content: space-between;
            max-width: 1200px;
            margin: 0 auto;
        }

        .hero-content {
            max-width: 500px;
        }

        .hero-content h1 {
            font-size: 48px;
            margin-bottom: 20px;
            line-height: 1.1;
        }

        .hero-content p {
            font-size: 18px;
            margin-bottom: 30px;
        }

        .book-link-section {
            display: flex;
            gap: 10px;
            align-items: center;
        }

        .book-link-section input[type="text"] {
            padding: 15px 20px;
            border: 1px solid var(--light-grey);
            border-radius: 8px;
            flex-grow: 1;
            font-size: 16px;
        }

        .book-link-section .book-btn {
            background-color: var(--primary-color);
            color: white;
            padding: 15px 30px;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            transition: background-color 0.3s ease;
        }

        .book-link-section .book-btn:hover {
            background-color: #0052cc;
        }

        .hero-image {
            /* Placeholder for a cool scheduling screenshot or illustration */
            width: 45%;
            height: 350px;
            background-color: var(--secondary-color);
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--text-color);
            font-size: 20px;
            font-weight: 600;
        }

        /* Mobile Responsiveness */
        @media (max-width: 768px) {
            .hero {
                flex-direction: column;
                text-align: center;
                padding: 40px 5%;
            }

            .hero-content {
                max-width: 100%;
                margin-bottom: 40px;
            }

            .hero-content h1 {
                font-size: 36px;
            }

            .book-link-section {
                flex-direction: column;
            }

            .book-link-section input[type="text"],
            .book-link-section .book-btn {
                width: 100%;
            }

            .hero-image {
                width: 100%;
                height: 250px;
            }
        }
    </style>
</head>
<body>
    <header class="header">
        <a href="index.php" class="logo">SchedulerClone</a>
        <div class="auth-buttons">
            <a class="login-btn" onclick="redirectToLogin()">Log In</a>
            <a class="signup-btn" onclick="redirectToSignup()">Sign Up</a>
        </div>
    </header>

    <main class="hero">
        <div class="hero-content">
            <h1>The fastest way to schedule any meeting.</h1>
            <p>
                Stop playing email tag. SchedulerClone takes the work out of connecting with others so you can move forward with your work.
            </p>
            <div class="book-link-section">
                <input type="text" placeholder="Enter your email to get started">
                <button class="book-btn" onclick="redirectToSignup()">
                    Sign Up Now
                </button>
            </div>
            <p style="margin-top: 15px; font-size: 14px;">
                Already have an account? 
                <a onclick="redirectToLogin()" style="color: var(--primary-color); text-decoration: none; cursor: pointer;">Log in here</a>.
            </p>
            </div>

        <div class="hero-image">
                    </div>
    </main>

    <script>
        function redirectToLogin() {
            window.location.href = "login.php";
        }

        function redirectToSignup() {
            window.location.href = "signup.php";
        }

        // REMOVED: function redirectToSchedule() is no longer needed but kept empty for safety
        function redirectToSchedule() {
            // This function is now unused, as the button was removed.
            // Placeholder: window.location.href = "schedule.php?link=demo-user";
        }
    </script>
</body>
</html>
