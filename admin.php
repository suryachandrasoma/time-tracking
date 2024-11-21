<?php
session_start();
include 'db.php';

// Redirect non-admin users to the login page
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: index.php");
    exit;
}

// Handle the Clear All Records button click
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['clear_records'])) {
    $delete_query = "DELETE FROM time_logs";
    if ($conn->query($delete_query)) {
        echo "<script>alert('All records have been cleared successfully!');</script>";
    } else {
        echo "<script>alert('Failed to clear records. Please try again.');</script>";
    }
}

// Fetch the records for display
$sql = "SELECT u.username, t.start_time, t.end_time, 
               SEC_TO_TIME(TIMESTAMPDIFF(SECOND, t.start_time, t.end_time)) as duration, 
               t.description
        FROM time_logs t
        JOIN users u ON t.user_id = u.id";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <!-- Bootstrap CSS -->
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.1/css/all.min.css">
    <style>
        body {
            background-image: url('Untitled design.png'); /* Replace with your background image path */
            background-size: cover;
            background-attachment: fixed;
            background-position: center;
        }
        #wrapper {
            display: flex;
            width: 100%;
        }
        #sidebar {
            min-width: 250px;
            max-width: 250px;
            background-color: #343a40;
            color: #fff;
            height: 100vh;
            position: fixed;
            top: 0;
            left: 0;
            padding-top: 20px;
            z-index: 1000;
        }
        #sidebar .list-group-item {
            background-color: #343a40;
            color: #ffffff;
        }
        #sidebar .dropdown-menu, #sidebar .dropdown-item {
            background-color: #343a40;
            color: #ffffff;
        }
        #page-content-wrapper {
            margin-left: 250px;
            padding: 20px;
            width: calc(100% - 250px);
        }
        .card {
            background-color: rgba(255, 255, 255, 0.85);
            border-radius: 15px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
        }
        .header {
            background-color: rgba(52, 58, 64, 0.9);
            color: #ffffff;
            padding: 20px;
            text-align: center;
            border-radius: 15px 15px 0 0;
        }
        .logout-link {
            display: block;
            text-align: center;
            margin-top: 20px;
            border: 1px solid #ffc107;
            color: #ffc107;
            background-color: rgba(255, 255, 255, 0.2);
            transition: all 0.3s ease;
        }
        .logout-link:hover {
            background-color: rgba(255, 193, 7, 0.3);
            color: #fff;
        }
    </style>
</head>
<body>

<div id="wrapper">
    <!-- Sidebar -->
    <div id="sidebar">
        <div class="text-center py-4">Admin Panel</div>
        <div class="list-group list-group-flush">
            <a href="admin_dashboard.php" class="list-group-item list-group-item-action">Dashboard</a>
            
            <!-- Users Dropdown -->
            <a href="#userSubmenu" data-toggle="collapse" class="list-group-item list-group-item-action">Users</a>
            <div class="collapse" id="userSubmenu">
                <a href="add_employee.php" class="list-group-item list-group-item-action">Add New</a>
                <a href="list_employees.php" class="list-group-item list-group-item-action">List</a>
            </div>

            <!-- Projects Dropdown -->
            <a href="#projectSubmenu" data-toggle="collapse" class="list-group-item list-group-item-action">Projects</a>
            <div class="collapse" id="projectSubmenu">
                <a href="add_project.php" class="list-group-item list-group-item-action">Add New Project</a>
                <a href="list_projects.php" class="list-group-item list-group-item-action">List of Projects</a>
            </div>
        </div>
    </div>

    <!-- Page Content -->
    <div id="page-content-wrapper">
        <nav class="navbar navbar-expand-lg navbar-light bg-light">
            <h2 class="ml-3">Yar Tech Services</h2>
        </nav>

        <div class="container mt-4">
            <div class="card">
                <div class="header">
                    <h2><i class="fas fa-user-shield"></i> Admin Dashboard</h2>
                </div>
                <div class="card-body">
                    <div class="widget-title">Time Logs</div>
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover">
                            <thead class="thead-dark">
                                <tr>
                                    <th>Employee</th>
                                    <th>Start Time</th>
                                    <th>End Time</th>
                                    <th>Duration (HH:MM:SS)</th>
                                    <th>Description</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($row = $result->fetch_assoc()) : ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($row['username']); ?></td>
                                    <td><?php echo htmlspecialchars($row['start_time']); ?></td>
                                    <td><?php echo htmlspecialchars($row['end_time']); ?></td>
                                    <td><?php echo htmlspecialchars($row['duration']); ?></td>
                                    <td><?php echo htmlspecialchars($row['description']); ?></td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- Clear All Records Button -->
                    <form method="POST" onsubmit="return confirm('Are you sure you want to clear all records?');">
                        <button type="submit" name="clear_records" class="btn btn-danger btn-block mt-3">
                            <i class="fas fa-trash-alt"></i> Clear All Records
                        </button>
                    </form>

                    <a href="logout.php" class="btn logout-link btn-block">Logout</a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Bootstrap JS and dependencies -->
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

<script>
    // Toggle Sidebar
    $("#menu-toggle").click(function(e) {
        e.preventDefault();
        $("#sidebar").toggleClass("toggled");
        $("#page-content-wrapper").css("margin-left", $("#sidebar").hasClass("toggled") ? "0" : "250px");
    });
</script>

</body>
</html>
