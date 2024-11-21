<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'employee') {
    header("Location: index.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Fetch the latest time log to display the duration
$log_result = $conn->query("SELECT start_time, end_time, TIMESTAMPDIFF(SECOND, start_time, end_time) AS duration_seconds 
                            FROM time_logs WHERE user_id = $user_id ORDER BY start_time DESC LIMIT 1");

$log = $log_result->fetch_assoc();
$duration_seconds = $log['duration_seconds'] ?? 0;

// Convert duration to HH:MM:SS
$hours = floor($duration_seconds / 3600);
$minutes = floor(($duration_seconds % 3600) / 60);
$seconds = $duration_seconds % 60;

// Format duration
$formatted_duration = sprintf("%02d:%02d:%02d", $hours, $minutes, $seconds);

// Check if progress has already been submitted today
$progress_check_result = $conn->query("SELECT id FROM time_logs WHERE user_id = '$user_id' AND log_date = CURDATE()");
$is_progress_submitted = mysqli_num_rows($progress_check_result) > 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Employee Dashboard</title>
    <!-- Bootstrap CSS -->
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.1/css/all.min.css">
    <style>
        body {
            background-image: url('Untitled design.png'); /* Replace with the path to your background image */
            background-size: cover;
            background-attachment: fixed;
            background-position: center;
        }
        .card {
            background-color: rgba(255, 255, 255, 0.85); /* Semi-transparent white background */
            border-radius: 15px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.2);
        }
        .header {
            background-color: #343a40;
            color: #ffffff;
            text-align: center;
            padding: 20px;
            border-radius: 15px 15px 0 0;
        }
        .logout-link {
            text-align: center;
            margin-top: 20px;
        }
        .btn-transparent {
            background-color: rgba(255, 255, 255, 0.3); /* Transparent white */
            color: #343a40; /* Dark color for text */
            border: 1px solid #343a40; /* Dark border */
        }
        .btn-custom {
            font-size: 1.1rem;
            font-weight: 500;
            padding: 10px 20px;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
            transition: all 0.2s ease;
        }
        .btn-custom:hover {
            transform: scale(1.05);
            box-shadow: 0 6px 15px rgba(0, 0, 0, 0.3);
        }
        .background-link {
            text-align: right;
            margin-top: 10px;
        }
        .text-center {
            text-align: center;
        }
    </style>
</head>
<body>

<div class="container mt-5">
    <div class="card">
        <div class="header">
            <h2><i class="fas fa-user"></i> Employee Activity</h2>
        </div>
        <div class="card-body">
            <form id="timeForm">
                <div class="form-group text-center">
                    <button type="button" id="startButton" class="btn btn-success btn-custom mr-3" onclick="handleTimeAction('start')">
                        <i class="fas fa-play"></i> Start
                    </button>
                    <button type="button" id="endButton" class="btn btn-danger btn-custom" onclick="handleTimeAction('end')">
                        <i class="fas fa-stop"></i> End
                    </button>
                </div>
                <div class="form-group">
                    <label for="description">Describe Today's Progress</label>
                    <textarea name="description" id="description" class="form-control" placeholder="Describe today's progress" <?php echo $is_progress_submitted ? 'disabled' : ''; ?>></textarea>
                </div>
                <div class="form-group text-center">
                    <button type="button" class="btn btn-primary" onclick="handleTimeAction('update')" <?php echo $is_progress_submitted ? 'disabled' : ''; ?>>Submit Update</button>
                </div>
            </form>
            
            <div class="text-center mt-3">
                <h5>Duration: <span id="timer"><?php echo $formatted_duration; ?></span></h5>
            </div>
            
            <div class="logout-link">
                <a href="logout.php" class="btn btn-transparent btn-block">Logout</a>
            </div>
        </div>
    </div>
</div>

<script>
    function handleTimeAction(action) {
        const description = document.getElementById('description').value;
        const formData = new FormData();
        formData.append('action', action);
        formData.append('description', description);

        fetch('employee_action.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                if (action === 'start') {
                    document.getElementById('startButton').disabled = true;
                    document.getElementById('endButton').disabled = false;
                    startRealTimeTimer();
                } else if (action === 'end') {
                    document.getElementById('startButton').disabled = false;
                    document.getElementById('endButton').disabled = true;
                    stopRealTimeTimer();
                    document.getElementById('timer').innerText = data.duration;
                }
            } else {
                alert('An error occurred: ' + data.message);
            }
        })
        .catch(error => console.error('Error:', error));
    }

    let timerInterval;

    function startRealTimeTimer() {
        const startTime = new Date().getTime();
        timerInterval = setInterval(() => {
            const currentTime = new Date().getTime();
            const elapsedTime = currentTime - startTime;

            const hours = Math.floor(elapsedTime / (1000 * 60 * 60));
            const minutes = Math.floor((elapsedTime % (1000 * 60 * 60)) / (1000 * 60));
            const seconds = Math.floor((elapsedTime % (1000 * 60)) / 1000);

            document.getElementById('timer').innerText = `${String(hours).padStart(2, '0')}:${String(minutes).padStart(2, '0')}:${String(seconds).padStart(2, '0')}`;
        }, 1000);
    }

    function stopRealTimeTimer() {
        clearInterval(timerInterval);
    }
</script>

</body>
</html>
