<?php
session_start();
include_once 'db/function.php';

// Check user session
if (!isset($_SESSION['userid'])) {
    header("Location: index.php");
    exit;
}

$user_id = $_SESSION['userid'];
$dbFunctions = new DBFunctions();

// Fetch current user data
$result = $dbFunctions->select('users', '*', ['id' => $user_id]);
$userinfo = !empty($result) ? $result[0] : null;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $data = [
        'full_name' => $_POST['full_name'],
        'email' => $_POST['email'],
        'username' => $_POST['username'],
        'department' => $_POST['department'],
    ];

    // If password is provided, hash it and include in update
    if (!empty($_POST['password'])) {
    $newPassword = $_POST['password'];
    $currentHashedPassword = $userinfo['password']; // Fetch current hashed password

    // Check if new password matches the old one
    if (password_verify($newPassword, $currentHashedPassword)) {
        echo "<script>alert('LALAAA DI NA PWEDE GAMITIN YUNG PASSWORD NAGAMIT MO NA TO E!');</script>";
    } else {
        $data['password'] = password_hash($newPassword, PASSWORD_BCRYPT);
        $data['password_updated_at'] = date('Y-m-d H:i:s'); // Update timestamp
    }
}

    

    // Handle file upload
    if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] == 0) {
        $uploadDir = 'assets/images/';
        $fileName = $user_id . '.png';
        $uploadPath = $uploadDir . $fileName;

        // Validate image type
        $allowedTypes = ['image/png', 'image/jpeg', 'image/jpg'];
        if (in_array($_FILES['profile_image']['type'], $allowedTypes)) {
            move_uploaded_file($_FILES['profile_image']['tmp_name'], $uploadPath);
        } else {
            echo "Invalid file type. Only PNG and JPEG are allowed.";
        }
    }

    // Update user information in the database
    $dbFunctions->update('users', $data, ['id' => $user_id]);

}
$result = $dbFunctions->select('users', '*', ['id' => $user_id]);
$userinfo = !empty($result) ? $result[0] : null;
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile Settings</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        @media (max-width: 767px) {
            .container {
                margin-left: 0;
                padding-left: 15px;
                padding-right: 15px;
            }
        }

        @media (min-width: 768px) {
            .container {
                margin-left: 250px;
            }
        }
    </style>
</head>

<body>
    <?php include 'sidebar.php'; ?>

    <div class="content p-4">
        <h2 class="mb-4">Profile Settings</h2>

        <form action="settings.php" method="POST" enctype="multipart/form-data">
            <div class="form-group">
                <label for="full_name">Full Name</label>
                <input type="text" class="form-control" id="full_name" name="full_name"
                    value="<?= htmlspecialchars($userinfo['full_name'] ?? '') ?>" required>
            </div>
            <div class="form-group">
                <label for="email">Email Address</label>
                <input type="email" class="form-control" id="email" name="email"
                    value="<?= htmlspecialchars($userinfo['email'] ?? '') ?>" required>
            </div>
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" class="form-control" id="username" name="username"
                    value="<?= htmlspecialchars($userinfo['username'] ?? '') ?>" required>
            </div>
            <div class="form-group">
                <label for="department">Department</label>
                <input type="text" class="form-control" id="department" name="department"
                    value="<?= htmlspecialchars($userinfo['department'] ?? '') ?>">
            </div>
            <div class="form-group">
                <label for="password">New Password (optional)</label>
                <input type="password" class="form-control" id="password" name="password"
                    placeholder="Enter new password">
            </div>
            <div class="form-group">
                <label for="profile_image">Profile Image</label>
                <input type="file" class="form-control" id="profile_image" name="profile_image"
                    accept="image/png, image/jpeg">
            </div>
            <button type="submit" class="btn btn-primary">Save Changes</button>
        </form>

    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>

</html>