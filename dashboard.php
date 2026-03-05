<?php
// CRITICAL 1: Start session
session_start();

// CRITICAL 2: Include database connection (use require_once for stability)
require_once 'db.php'; 

// CRITICAL 3: AUTH CHECK & REDIRECT FIX (Non-logged-in users must be redirected)
if (!isset($_SESSION['user_id']) || empty($_SESSION['user_id'])) {
    // Redirect to login page if not logged in (using JS as requested)
    echo '<script>window.location.href = "login.php";</script>';
    exit(); // CRITICAL: Stops the blank page/unauthorized content issue
}

// Fetch user data (using placeholders since database connection needs to be verified)
$user_id = $_SESSION['user_id'];
$user_name = $_SESSION['user_name'] ?? "Pro Developer"; 
$booking_link = "yoursite.com/pd-pro"; 
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - SchedulerClone</title>
    <style>
        :root {
            --primary-color: #0069ff;
            --text-color: #333;
            --light-grey: #e8e8e8;
            --success-color: #28a745;
            --danger-color: #dc3545;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: sans-serif;
        }

        body {
            background-color: #f6f8fa;
            color: var(--text-color);
        }

        .container {
            max-width: 1200px;
            margin: 40px auto;
            padding: 0 20px;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            border-bottom: 2px solid var(--light-grey);
            padding-bottom: 20px;
        }

        .header h1 {
            font-size: 32px;
            color: var(--primary-color);
        }

        .user-info {
            text-align: right;
        }

        .user-info a {
            color: var(--danger-color);
            text-decoration: none;
            margin-left: 15px;
            cursor: pointer;
        }

        .tabs {
            display: flex;
            margin-bottom: 20px;
        }

        .tab-btn {
            background: #fff;
            border: 1px solid var(--light-grey);
            padding: 10px 20px;
            margin-right: 10px;
            cursor: pointer;
            font-weight: 600;
            border-radius: 5px 5px 0 0;
            transition: all 0.2s;
        }

        .tab-btn.active {
            background: var(--primary-color);
            color: white;
            border-color: var(--primary-color);
        }

        .tab-content {
            background: #fff;
            padding: 20px;
            border: 1px solid var(--light-grey);
            border-radius: 0 5px 5px 5px;
        }

        .appointment-list {
            list-style: none;
        }

        .appointment-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 0;
            border-bottom: 1px solid var(--light-grey);
        }

        .appointment-item:last-child {
            border-bottom: none;
        }

        .details h3 {
            font-size: 18px;
            margin-bottom: 5px;
        }

        .details p {
            color: #666;
            font-size: 14px;
        }

        .actions button {
            padding: 8px 15px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            margin-left: 10px;
            font-weight: 600;
        }

        .actions .cancel-btn {
            background: var(--danger-color);
            color: white;
        }

        .actions .reschedule-btn {
            background: var(--primary-color);
            color: white;
        }

        .actions button:hover {
            opacity: 0.9;
        }

        /* Responsive */
        @media (max-width: 600px) {
            .header {
                flex-direction: column;
                align-items: flex-start;
            }

            .user-info {
                margin-top: 15px;
            }

            .appointment-item {
                flex-direction: column;
                align-items: flex-start;
            }

            .actions {
                margin-top: 10px;
            }

            .actions button {
                margin-left: 0;
                margin-right: 10px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <header class="header">
            <h1>📅 Dashboard</h1>
            <div class="user-info">
                <p>Logged in as: <strong><?php echo htmlspecialchars($user_name); ?></strong></p>
                <p>Your Link: <a href="schedule.php?link=<?php echo urlencode($booking_link); ?>" target="_blank"><?php echo htmlspecialchars($booking_link); ?></a></p>
                <a onclick="logout()">Logout</a>
            </div>
        </header>

        <a href="set_availability.php" class="tab-btn" style="background: var(--success-color); color: white; margin-bottom: 20px; display: inline-block; border-radius: 5px;">
            ⚙️ Set Availability
        </a>

        <div class="tabs">
            <button class="tab-btn active" onclick="showTab('upcoming', event)">Upcoming Meetings</button>
            <button class="tab-btn" onclick="showTab('past', event)">Past Meetings</button>
        </div>

        <div id="upcoming" class="tab-content">
            <h2>Upcoming Appointments</h2>
            <ul class="appointment-list">
                <?php
                // Placeholder Data
                $upcoming_appointments = [
                    ['guest' => 'Alice Johnson', 'time' => 'Dec 20, 2025 at 10:00 AM', 'type' => '30 Min Intro Call'],
                    ['guest' => 'Bob Smith', 'time' => 'Jan 5, 2026 at 2:30 PM', 'type' => 'Project Review'],
                ];
                
                if (empty($upcoming_appointments)) {
                    echo "<p>You have no upcoming appointments.</p>";
                } else {
                    foreach ($upcoming_appointments as $app) {
                        echo "<li class='appointment-item'>";
                        echo "<div class='details'>";
                        echo "<h3>{$app['type']} with {$app['guest']}</h3>";
                        echo "<p>Time: {$app['time']}</p>";
                        echo "</div>";
                        echo "<div class='actions'>";
                        echo "<button class='reschedule-btn' onclick='reschedule({$user_id})'>Reschedule</button>";
                        echo "<button class='cancel-btn' onclick='cancel({$user_id})'>Cancel</button>";
                        echo "</div>";
                        echo "</li>";
                    }
                }
                ?>
            </ul>
        </div>

        <div id="past" class="tab-content" style="display:none;">
            <h2>Past Appointments</h2>
            <ul class="appointment-list">
                 <li class="appointment-item">
                    <div class="details">
                        <h3>Client Kick-off Meeting with Jane Doe</h3>
                        <p>Time: Nov 15, 2025 at 11:00 AM | Status: Completed</p>
                    </div>
                    </li>
            </ul>
        </div>
    </div>

    <script>
        // Tab switching logic
        function showTab(tabName, event) {
            var i, tabcontent, tablinks;
            tabcontent = document.getElementsByClassName("tab-content");
            tablinks = document.getElementsByClassName("tab-btn");

            for (i = 0; i < tabcontent.length; i++) {
                tabcontent[i].style.display = "none";
            }
            for (i = 0; i < tablinks.length; i++) {
                tablinks[i].className = tablinks[i].className.replace(" active", "");
            }

            document.getElementById(tabName).style.display = "block";
            event.currentTarget.className += " active";
        }

        // JS Redirection for Logout
        function logout() {
            window.location.href = "logout.php"; 
        }

        function reschedule(appointmentId) {
            alert("Reschedule feature for Appointment ID: " + appointmentId + " is coming soon!");
        }

        function cancel(appointmentId) {
            if (confirm("Are you sure you want to cancel this appointment?")) {
                alert("Appointment ID: " + appointmentId + " cancellation request sent.");
            }
        }
    </script>
</body>
</html>
