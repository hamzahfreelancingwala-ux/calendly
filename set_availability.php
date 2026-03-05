<?php
session_start();
require_once 'db.php'; // Use require_once for critical files

// CRITICAL: Auth Check
if (!isset($_SESSION['user_id']) || empty($_SESSION['user_id'])) {
    echo '<script>window.location.href = "login.php";</script>';
    exit();
}

$user_id = $_SESSION['user_id'];
$user_name = $_SESSION['user_name'] ?? "User";
$message = '';
$days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];

// --- 1. HANDLE FORM SUBMISSION (Saving New/Updated Availability) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $day_of_week = $_POST['day_of_week'] ?? '';
    $start_time = $_POST['start_time'] ?? '';
    $end_time = $_POST['end_time'] ?? '';
    $duration = (int)($_POST['duration'] ?? 30);

    if (!empty($day_of_week) && !empty($start_time) && !empty($end_time) && in_array($day_of_week, $days)) {
        
        // Check if availability already exists for this day/user (to UPDATE instead of INSERT)
        $check_sql = "SELECT availability_id FROM availability WHERE user_id = ? AND day_of_week = ?";
        $check_stmt = $conn->prepare($check_sql);
        $check_stmt->bind_param("is", $user_id, $day_of_week);
        $check_stmt->execute();
        $result = $check_stmt->get_result();
        
        if ($result->num_rows > 0) {
            // AVAILABILITY EXISTS: UPDATE the existing record
            $row = $result->fetch_assoc();
            $availability_id = $row['availability_id'];
            $update_sql = "UPDATE availability SET start_time = ?, end_time = ?, duration_minutes = ? WHERE availability_id = ?";
            $stmt = $conn->prepare($update_sql);
            $stmt->bind_param("sisi", $start_time, $end_time, $duration, $availability_id);
            if ($stmt->execute()) {
                $message = '<div class="success-message">Availability for ' . htmlspecialchars($day_of_week) . ' updated successfully!</div>';
            } else {
                $message = '<div class="error-message">Error updating availability: ' . $conn->error . '</div>';
            }
        } else {
            // NEW AVAILABILITY: INSERT a new record
            $insert_sql = "INSERT INTO availability (user_id, day_of_week, start_time, end_time, duration_minutes) VALUES (?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($insert_sql);
            $stmt->bind_param("isssi", $user_id, $day_of_week, $start_time, $end_time, $duration);
            if ($stmt->execute()) {
                $message = '<div class="success-message">New availability for ' . htmlspecialchars($day_of_week) . ' added successfully!</div>';
            } else {
                $message = '<div class="error-message">Error adding availability: ' . $conn->error . '</div>';
            }
        }
        $stmt->close();
        $check_stmt->close();
    } else {
         $message = '<div class="error-message">Please fill all fields correctly.</div>';
    }
}

// --- 2. FETCH EXISTING AVAILABILITY (for Display) ---
$current_availability = [];
$fetch_sql = "SELECT day_of_week, start_time, end_time, duration_minutes FROM availability WHERE user_id = ? ORDER BY FIELD(day_of_week, 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday')";
$fetch_stmt = $conn->prepare($fetch_sql);
$fetch_stmt->bind_param("i", $user_id);
$fetch_stmt->execute();
$result = $fetch_stmt->get_result();

while ($row = $result->fetch_assoc()) {
    $current_availability[$row['day_of_week']] = $row;
}
$fetch_stmt->close();
close_db_connection($conn);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Set Availability</title>
    <style>
        :root {
            --primary-color: #0069ff;
            --text-color: #333;
            --light-grey: #e8e8e8;
            --success-color: #28a745;
            --error-color: #dc3545;
        }
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: sans-serif; }
        body { background-color: #f6f8fa; color: var(--text-color); }
        .container { max-width: 900px; margin: 40px auto; padding: 20px; background: #fff; border-radius: 8px; box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05); }
        .header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; border-bottom: 2px solid var(--light-grey); padding-bottom: 20px; }
        .header h1 { font-size: 28px; color: var(--primary-color); }
        .header a { text-decoration: none; color: white; background-color: var(--primary-color); padding: 8px 15px; border-radius: 5px; font-weight: 600; }
        .message-container { margin-bottom: 20px; }
        .error-message, .success-message { padding: 10px; border-radius: 5px; font-size: 14px; text-align: center; }
        .error-message { background-color: #f8d7da; color: var(--error-color); border: 1px solid #f5c6cb; }
        .success-message { background-color: #d4edda; color: var(--success-color); border: 1px solid #c3e6cb; }

        /* Form Styling */
        .form-section { border: 1px solid var(--light-grey); padding: 20px; border-radius: 8px; margin-bottom: 30px; }
        .form-group { margin-bottom: 15px; display: flex; align-items: center; gap: 15px; }
        .form-group label { font-weight: 600; width: 120px; }
        .form-group input[type="time"], .form-group select { padding: 10px; border: 1px solid var(--light-grey); border-radius: 5px; font-size: 16px; flex-grow: 1; max-width: 200px; }
        .submit-btn { background-color: var(--success-color); color: white; padding: 10px 20px; border: none; border-radius: 5px; font-size: 16px; cursor: pointer; font-weight: 600; }

        /* Availability Table Styling */
        .availability-table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        .availability-table th, .availability-table td { padding: 12px; text-align: left; border-bottom: 1px solid var(--light-grey); }
        .availability-table th { background-color: var(--secondary-color); font-weight: 700; }
        .availability-table tr:hover { background-color: #f0f0f0; }
        .availability-table .day-off { color: #999; font-style: italic; }
    </style>
</head>
<body>
    <div class="container">
        <header class="header">
            <h1>⚙️ Set Availability for <?php echo htmlspecialchars($user_name); ?></h1>
            <a href="dashboard.php">← Back to Dashboard</a>
        </header>

        <div class="message-container">
            <?php echo $message; ?>
        </div>
        
        <h2>Add/Update a Time Slot</h2>
        <div class="form-section">
            <form method="POST">
                <div class="form-group">
                    <label for="day_of_week">Day:</label>
                    <select id="day_of_week" name="day_of_week" required>
                        <option value="">Select Day</option>
                        <?php foreach ($days as $day): ?>
                            <option value="<?php echo $day; ?>"><?php echo $day; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="start_time">Start Time:</label>
                    <input type="time" id="start_time" name="start_time" required>

                    <label for="end_time" style="width: 100px;">End Time:</label>
                    <input type="time" id="end_time" name="end_time" required>
                </div>
                
                <div class="form-group">
                    <label for="duration">Duration (min):</label>
                    <select id="duration" name="duration" required>
                        <option value="15">15 Minutes</option>
                        <option value="30" selected>30 Minutes</option>
                        <option value="60">60 Minutes</option>
                    </select>
                </div>

                <button type="submit" class="submit-btn">Save Availability Slot</button>
            </form>
        </div>

        <h2>Current Availability Settings</h2>
        
        <table class="availability-table">
            <thead>
                <tr>
                    <th>Day</th>
                    <th>Time Slot</th>
                    <th>Duration</th>
                </tr>
            </thead>
            <tbody>
                <?php
                // Iterate through all days of the week to ensure a complete list
                foreach ($days as $day) {
                    echo '<tr>';
                    echo '<td>' . htmlspecialchars($day) . '</td>';
                    
                    if (isset($current_availability[$day])) {
                        $data = $current_availability[$day];
                        echo '<td>' . date('g:i A', strtotime($data['start_time'])) . ' - ' . date('g:i A', strtotime($data['end_time'])) . '</td>';
                        echo '<td>' . htmlspecialchars($data['duration_minutes']) . ' min</td>';
                    } else {
                        // Display "Day Off" if no slot is set for that day
                        echo '<td colspan="2" class="day-off">Day Off (No slot set)</td>';
                    }
                    echo '</tr>';
                }

                if (empty($current_availability)) {
                     // Check if there is absolutely no data
                     // Note: The loop above handles this visually by showing "Day Off" for all days.
                }
                ?>
            </tbody>
        </table>

        <p style="margin-top: 20px; font-size: 14px; color: #666;">
            * To remove a slot, submit the form for that day but set the start and end times to the same value (e.g., 00:00).
            A more robust solution would be a dedicated "Delete" button, which you can implement later.
        </p>

    </div>
    
</body>
</html>
