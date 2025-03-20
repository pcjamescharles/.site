<?php
session_start();
$admin_username = $_SESSION['username'];
// Database connection
$host = 'localhost';
$user = 'root';
$password = '';
$dbname = 'inventotrack';

$conn = new mysqli($host, $user, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch users where department is 'staff'
$sql = "SELECT * FROM users WHERE department = 'staff'";
$result = $conn->query($sql);
$users = $result->fetch_all(MYSQLI_ASSOC);

// Function to insert log
function insertLog($conn, $user_id, $username, $action_type, $module, $description) {
    // If user_id is empty or does not exist, set it to NULL
    $user_id = !empty($user_id) ? $user_id : NULL;

    $stmt = $conn->prepare("INSERT INTO logs (user_id, username, action_type, module, description) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("issss", $user_id, $_SESSION['name'], $action_type, $module, $description);

    if (!$stmt->execute()) {
        error_log("Log insertion failed: " . $stmt->error);
    }
    $stmt->close();
}



if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'];

    if ($action === 'adduser') {
        $fullName = $_POST['fullName'];
        $type = $_POST['type'];
        $username = $_POST['username'];
        $email = $_POST['email'];
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
        $created_at = date('Y-m-d H:i:s');
    
        // Check if username already exists
        $check_stmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
        $check_stmt->bind_param("s", $username);
        $check_stmt->execute();
        $check_stmt->store_result();
        
        if ($check_stmt->num_rows > 0) {
            echo "<script>alert('Username already exists. Please choose another username.'); window.location.href = 'staff.php';</script>";
        } else {
            // Proceed with insertion
            $stmt = $conn->prepare("INSERT INTO users (full_name, type, username, email, department, password, created_at) VALUES (?, ?, ?, ?, 'staff', ?, ?)");
            $stmt->bind_param("sissss", $fullName, $type, $username, $email, $password, $created_at);
    
            if ($stmt->execute()) {
                $user_id = $stmt->insert_id; // Get the new user ID

                // Log staff addition
                insertLog($conn, $user_id, $fullName, 'ADD', 'USER', "Staff member $fullName was added.");

                echo "<script>alert('User added successfully'); window.location.href = 'staff.php';</script>";
            } else {
                echo "Failed to add user.";
            }
        }
        $check_stmt->close();
    }
    
    elseif ($action === 'edituser') {
        $userId = $_POST['userId'];
        $fullName = $_POST['fullName'];
        $type = $_POST['type'];
        $username = $_POST['username'];
        $email = $_POST['email'];
        $updated_at = date('Y-m-d H:i:s');
    
        // Check if password is provided
        if (!empty($_POST['password'])) {
            $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
            $stmt = $conn->prepare("UPDATE users SET full_name = ?, type = ?, username = ?, email = ?, password = ?, updated_at = ? WHERE id = ?");
            $stmt->bind_param("ssssssi", $fullName, $type, $username, $email, $password, $updated_at, $userId);
        } else {
            $stmt = $conn->prepare("UPDATE users SET full_name = ?, type = ?, username = ?, email = ?, updated_at = ? WHERE id = ?");
            $stmt->bind_param("sssssi", $fullName, $type, $username, $email, $updated_at, $userId);
        }
    
        if ($stmt->execute()) {
            // Log staff update
            insertLog($conn, $userId, $_SESSION['name'], 'EDIT', 'USER', "Staff member $fullName was updated.");
    
            echo "<script>alert('User updated successfully'); window.location.href = 'staff.php';</script>";
        } else {
            echo "Failed to update user.";
        }
    }
}    
// Delete user
if (isset($_GET['delete'])) {
    $userId = $_GET['delete'];

    // Get username before deleting
    $stmt = $conn->prepare("SELECT full_name FROM users WHERE id = ?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $stmt->bind_result($fullName);
    $stmt->fetch();
    $stmt->close();

    // Log staff deletion BEFORE deleting the user
    insertLog($conn, $userId, $_SESSION['name'], 'DELETE', 'USER', "Staff member $fullName was deleted.");

    // Delete user
    $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
    $stmt->bind_param("i", $userId);

    if ($stmt->execute()) {
        echo "<script>alert('User deleted successfully'); window.location.href = 'staff.php';</script>";
    } else {
        echo "Failed to delete user.";
    }
}

?>



<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Staff Management</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/jquery.dataTables.min.css">
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.0/umd/popper.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

</head>

<body>

    <?php include 'sidebar.php'; ?>

    <div class="content p-4">
        <h2>Staff Management</h2>
        <button class="btn btn-primary mb-3" data-toggle="modal" data-target="#addUserModal">Add User</button>

        <table id="usersTable" class="table table-striped table-bordered">
    <thead>
        <tr>
            <th>Full Name</th>
            <th>Type</th>
            <th>Username</th>
            <th>Email</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($users as $user): ?>
            <tr>
                <td>
                    <a href="#" class="text-primary" 
                        data-toggle="modal" 
                        data-target="#staffLogsModal" 
                        data-username="<?php echo htmlspecialchars($user['username']); ?>"
                        data-fullname="<?php echo htmlspecialchars($user['full_name']); ?>"
                        onclick="fetchStaffLogs(this)">
                        <?php echo htmlspecialchars($user['full_name']); ?>
                    </a>
                </td>
                <td><?php echo htmlspecialchars($user['type']); ?></td>
                <td><?php echo htmlspecialchars($user['username']); ?></td>
                <td><?php echo htmlspecialchars($user['email']); ?></td>
                <td>
                    <button class="btn btn-secondary btn-sm" data-toggle="modal" data-target="#editUserModal"
                        data-id="<?php echo $user['id']; ?>"
                        data-fullname="<?php echo htmlspecialchars($user['full_name']); ?>"
                        data-type="<?php echo htmlspecialchars($user['type']); ?>"
                        data-username="<?php echo htmlspecialchars($user['username']); ?>"
                        data-email="<?php echo htmlspecialchars($user['email']); ?>"
                        onclick="populateEditModal(this)">Edit</button>
                    <a href="staff.php?delete=<?php echo $user['id']; ?>&table=users" class="btn btn-danger btn-sm">Delete</a>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>
<!-- Staff Logs Modal -->
<div class="modal fade" id="staffLogsModal" tabindex="-1" aria-labelledby="staffLogsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="staffLogsModalLabel">Recent Activity for <span id="staffName"></span></h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" style="max-height: 400px; overflow-y: auto;">
                <ul class="list-group" id="staffLogsList">
                    <li class="list-group-item text-center">Loading...</li>
                </ul>
            </div>
        </div>
    </div>
</div>
<script>
    function fetchStaffLogs(element) {
        var username = element.getAttribute("data-username");
        var fullName = element.getAttribute("data-fullname");
        
        // Set staff name in modal title
        document.getElementById("staffName").textContent = fullName;
        
        // Clear previous logs and show loading
        var logsList = document.getElementById("staffLogsList");
        logsList.innerHTML = '<li class="list-group-item text-center">Loading...</li>';

        // Send AJAX request to fetch logs
        fetch("fetch_staff_logs.php?username=" + encodeURIComponent(username))
            .then(response => response.json())
            .then(data => {
                logsList.innerHTML = ''; // Clear loading text
                
                if (data.length > 0) {
                    data.forEach(log => {
                        logsList.innerHTML += `
                            <li class='list-group-item'>
                                 ${log.action_type} - ${log.description} 
                                <span class='badge badge-secondary float-right'>${log.time_display}</span>
                            </li>`;
                    });
                } else {
                    logsList.innerHTML = '<li class="list-group-item text-center">No recent activity</li>';
                }
            })
            .catch(error => {
                console.error("Error fetching logs:", error);
                logsList.innerHTML = '<li class="list-group-item text-danger text-center">Failed to load logs</li>';
            });
    }
</script>


        <!-- Edit User Modal -->
        <div class="modal fade" id="editUserModal" tabindex="-1" aria-labelledby="editUserModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="editUserModalLabel">Edit User</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <form action="staff.php" method="POST">
                            <input type="hidden" name="action" value="edituser">
                            <input type="hidden" name="userId" id="editUserId">

                            <div class="form-group">
                                <label for="editFullName">Full Name</label>
                                <input type="text" class="form-control" id="editFullName" name="fullName" required>
                            </div>
                            <div style="display :none;"class="form-group">
                                <label for="editType">Type</label>
                                <input type="text" class="form-control" id="editType" name="type" required>
                            </div>
                            <div class="form-group">
                                <label for="editUsername">Username</label>
                                <input type="text" class="form-control" id="editUsername" name="username" required>
                            </div>
                            <div class="form-group">
                                <label for="editEmail">Email</label>
                                <input type="email" class="form-control" id="editEmail" name="email" required>
                            </div>
                            <div class="form-group">
                                <label for="password">Password</label>
                                <input type="password" class="form-control" id="password" name="password" required>
                            </div>
                            <button type="submit" class="btn btn-primary">Save Changes</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Add User Modal -->
        <div class="modal fade" id="addUserModal" tabindex="-1" aria-labelledby="addUserModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="addUserModalLabel">Add User</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <form action="staff.php" method="POST">
                            <input type="hidden" name="action" value="adduser">

                            <div class="form-group">
                                <label for="fullName">Full Name</label>
                                <input type="text" class="form-control" id="fullName" name="fullName" required>
                            </div>
                            <div style="display :none;" class="form-group">
                                <label for="type">Type</label>
                                <input type="text" class="form-control" id="type" name="type" value ="2" required>
                            </div>
                            <div class="form-group">
                                <label for="username">Username</label>
                                <input type="text" class="form-control" id="username" name="username" required>
                            </div>
                            <div class="form-group">
                                <label for="email">Email</label>
                                <input type="email" class="form-control" id="email" name="email" required>
                            </div>
                            <div class="form-group">
                                <label for="password">Password</label>
                                <input type="password" class="form-control" id="password" name="password" required>
                            </div>
                            <button type="submit" class="btn btn-primary">Add User</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <script>
        function populateEditModal(element) {
            document.getElementById('editUserId').value = element.getAttribute('data-id');
            document.getElementById('editFullName').value = element.getAttribute('data-fullname');
            document.getElementById('editType').value = element.getAttribute('data-type');
            document.getElementById('editUsername').value = element.getAttribute('data-username');
            document.getElementById('editEmail').value = element.getAttribute('data-email');
        }
    </script>

</body>

</html>
