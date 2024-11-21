<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'employee') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$user_id = $_SESSION['user_id'];
$action = $_POST['action'] ?? null;
$description = $_POST['description'] ?? null;

// Prevent empty or invalid actions
if (!$action) {
    echo json_encode(['success' => false, 'message' => 'No action specified']);
    exit;
}

// **1. Action: Start Timer**
if ($action === 'start') {
    // Check if a session already exists for today (by user_id and date)
    $check_query = "SELECT id FROM time_logs WHERE user_id = ? AND DATE(start_time) = CURDATE()";
    $stmt = $conn->prepare($check_query);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        echo json_encode(['success' => false, 'message' => 'You already have a session recorded today.']);
        exit;
    }

    // Insert a new session with the start time
    $insert_query = "INSERT INTO time_logs (user_id, start_time, is_session_active) VALUES (?, NOW(), TRUE)";
    $stmt = $conn->prepare($insert_query);
    $stmt->bind_param("i", $user_id);

    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Session started successfully!']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to start session.']);
    }
    exit;
}

// **2. Action: End Timer**
if ($action === 'end') {
    // Ensure there is an active session to end
    $check_query = "SELECT id FROM time_logs WHERE user_id = ? AND is_session_active = TRUE";
    $stmt = $conn->prepare($check_query);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        echo json_encode(['success' => false, 'message' => 'No active session to end.']);
        exit;
    }

    // Update the session to include the end time
    $update_query = "UPDATE time_logs SET end_time = NOW(), is_session_active = FALSE, description = ? WHERE user_id = ? AND is_session_active = TRUE";
    $stmt = $conn->prepare($update_query);
    $stmt->bind_param("si", $description, $user_id);

    if ($stmt->execute()) {
        // Fetch the duration of the session
        $duration_query = "SELECT SEC_TO_TIME(TIMESTAMPDIFF(SECOND, start_time, end_time)) AS duration FROM time_logs WHERE user_id = ? ORDER BY start_time DESC LIMIT 1";
        $stmt = $conn->prepare($duration_query);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $duration = $result->fetch_assoc()['duration'];

        echo json_encode(['success' => true, 'message' => 'Session ended successfully!', 'duration' => $duration]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to end the session.']);
    }
    exit;
}

// **3. Action: Update Progress**
if ($action === 'update') {
    // Ensure progress updates only once per day
    $check_progress_query = "SELECT id FROM time_logs WHERE user_id = ? AND DATE(start_time) = CURDATE() AND description IS NOT NULL";
    $stmt = $conn->prepare($check_progress_query);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        echo json_encode(['success' => false, 'message' => 'You can only update your progress once per day.']);
        exit;
    }

    // Update progress for the last ended session
    $update_progress_query = "UPDATE time_logs SET description = ? WHERE user_id = ? AND is_session_active = FALSE ORDER BY start_time DESC LIMIT 1";
    $stmt = $conn->prepare($update_progress_query);
    $stmt->bind_param("si", $description, $user_id);

    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Progress updated successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to update progress.']);
    }
    exit;
}

// If an invalid action is provided
echo json_encode(['success' => false, 'message' => 'Invalid action']);
exit;
?>
