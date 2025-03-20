<?php
include_once 'db/function.php';
$db = new DBFunctions();
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Capture user registration data
    if ($_POST['password'] !== $_POST['confirm_password']) {
        echo "<script>
                Swal.fire({
                    title: 'Error!',
                    text: 'Passwords do not match. Please try again.',
                    icon: 'error',
                    confirmButtonText: 'OK'
                });
              </script>";
        exit;
    }

    $data = [
        'full_name' => $_POST['full_name'],
        'type' => 2, // Set type as 2
        'username' => $_POST['username'],
        'email' => $_POST['email'],
        'department' => $_POST['department'],
        'password' => password_hash($_POST['password'], PASSWORD_BCRYPT),
    ];




    $username_exists = !empty($db->select('users', '*', ['username' => $data['username']]));
    $email_exists = !empty($db->select('users', '*', ['email' => $data['email']]));

    if ($username_exists || $email_exists) {
        echo "<script>
                alert('Username or Email already exists. Please use a different one.');
                window.location.href = 'register.php';
              </script>";
    } else {
        // Insert the new user if no duplicates found
        if ($db->insert('users', $data)) {
            // Generate OTP
            $otp = rand(100000, 999999);

            session_start();
            $_SESSION['otp'] = $otp;

            // Send OTP email
            $otpResponse = $db->sendOtpEmail($data['email'], $otp);

            echo "<script>
                        alert('Registration successful. Please check your email for OTP.');
                        window.location.href = 'verify_otp.php'; // Redirect to OTP verification page
                      </script>";

        } else {
            echo "Error occurred during registration.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registration Form</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <!-- SweetAlert2 CDN -->
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11.0.19/dist/sweetalert2.min.css" rel="stylesheet">
    <style>
        body {
            background: url('https://png.pngtree.com/thumb_back/fh260/background/20231010/pngtree-3d-rendering-of-warehouse-inventory-stockpile-image_13575510.png') no-repeat center center fixed;
            background-size: cover;
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100vh;
        }
        .form-card {
            width: 100%;
            max-width: 500px;
            padding: 20px;
            background: rgba(255, 255, 255, 0.9);
            border-radius: 10px;
            box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.3);
        }
        .form-header {
            text-align: center;
            margin-bottom: 20px;
        }
        .form-header img {
            width: 80px;
            height: 60px;
            margin-bottom: 10px;
        }
        .form-header h2 {
            font-size: 1.8rem;
            margin: 0;
            color: #213830;
        }
        .btn-custom {
            background-color: #213830;
            color: white;
        }
        .btn-custom:hover {
            background-color: #1a2a24;
        }
        #passwordCriteria li {
            font-size: 0.9rem;
            color: red;
        }
        @media (max-width: 768px) {
            .form-card {
                width: 90%;
            }
        }
    </style>
</head>

<body style="background-color: #f2f2f2;">


    <!-- Registration Form -->
    <div class="form-card mt-5">
        <div class="form-header">
            <img src="assets/images/logo.png" alt="Logo">
            <h2>Register</h2>
        </div>

        <form id="registrationForm" method="POST" action="register.php">
            <div class="form-group">
                <label for="registerFullName">Full Name</label>
                <input type="text" class="form-control" id="registerFullName" name="full_name"
                    placeholder="Enter your full name" required>
            </div>
            <div class="form-group">
                <label for="registerUsername">Username</label>
                <input type="text" class="form-control" id="registerUsername" name="username"
                    placeholder="Choose a username" required>
            </div>
            <div class="form-group">
                <label for="registerEmail">Email Address</label>
                <input type="email" class="form-control" id="registerEmail" name="email" placeholder="Enter your email"
                    required>
            </div>
            <div class="form-group">
                <label for="registerDepartment">Department</label>
                <input type="text" class="form-control" id="registerDepartment" name="department"
                    placeholder="Enter your department" required>
            </div>
            <div class="form-group">
    <label for="registerPassword">Password</label>
    <input type="password" class="form-control" id="registerPassword" name="password" placeholder="Enter your password" required>
    <ul id="passwordCriteria">
        <li id="length">* Password must be at least 12 characters</li>
        <li id="number">* Password must contain at least 1 number</li>
        <li id="lowercase">* Password must contain at least 1 lowercase letter</li>
        <li id="uppercase">* Password must contain at least 1 uppercase letter</li>
        <li id="special">* Password must contain at least 1 special character</li>
    </ul>
</div>



            <div class="form-group">
    <label for="registerConfirmPassword">Confirm Password</label>
    <input type="password" class="form-control" id="registerConfirmPassword" name="confirm_password"
        placeholder="Confirm your password" required>
    <small id="passwordMatchMessage" style="color: red; display: none;"></small> <!-- Message Display -->
</div>


            <!-- Terms and Conditions Checkbox -->
            <div class="form-group form-check">
                <input type="checkbox" class="form-check-input" id="acceptTerms" required>
                <label class="form-check-label" for="acceptTerms">I accept the <a href="#" id="termsLink">Terms and
                        Conditions</a></label>
            </div>

            <button type="submit" class="btn btn-custom btn-block">Register</button>
            <a href="index.php" class="btn btn-custom btn-block">Login</a>

        </form>
    </div>

    <!-- jQuery and Bootstrap JS -->
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js"></script>

    <!-- SweetAlert2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.0.19/dist/sweetalert2.min.js"></script>

    <script>

$(document).ready(function () {
    $('#registerPassword, #registerConfirmPassword').on('input', function () {
        var password = $('#registerPassword').val();
        var confirmPassword = $('#registerConfirmPassword').val();
        var message = $('#passwordMatchMessage');

        if (password === confirmPassword && password.length > 0) {
            message.text('✔ Passwords match').css('color', 'green').show();
        } else {
            message.text('✖ Passwords do not match').css('color', 'red').show();
        }
    });
});


    $('#registerPassword').on('input', function () {
        var password = this.value;
        var checks = [
            { id: 'length', regex: /.{12,}/ },
            { id: 'number', regex: /[0-9]/ },
            { id: 'lowercase', regex: /[a-z]/ },
            { id: 'uppercase', regex: /[A-Z]/ },
            { id: 'special', regex: /[!@#$%^&*(),.?":{}|<>]/ }
        ];

        checks.forEach(check => {
            document.getElementById(check.id).style.color = check.regex.test(password) ? 'green' : 'red';
        });
    });



        $(function () {
            $('#registrationForm').on('submit', function (event) {
                if (!$('#acceptTerms').prop('checked')) {
                    event.preventDefault();  // Prevent form submission

                    // Show SweetAlert with a warning
                    Swal.fire({
                        title: 'Oops!',
                        text: 'You must accept the terms and conditions to register.',
                        icon: 'warning',
                        confirmButtonText: 'Ok'
                    });
                }
            });

            $('#termsLink').on('click', function (event) {
                event.preventDefault(); // Prevent page jump
                Swal.fire({
                    title: 'Terms and Conditions',
                    text: 'Here are the terms and conditions...',
                    icon: 'info',
                    confirmButtonText: 'Close'
                });
            });
        });
    </script>
    


</body>

</html>