<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $entered_otp = $_POST['otp'];

    if ($entered_otp == $_SESSION['otp']) {
        unset($_SESSION['otp']); // Remove OTP from session after verification

        if (isset($_GET['userid'])) {
            echo "
<script>
    alert('OTP Verified Successfully');
    window.location.href = 'dashboard.php'; // Redirect to dashboard after successful OTP verification
</script>";
        } else {
            echo "
<script>
    alert('OTP Verified Successfully');
    window.location.href = 'index.php';  // Redirect to home or login page
</script>";
        }
    } else {
        echo "
<script>
    alert('Invalid OTP. Please try again.');
    window.location.href = 'verify_otp.php'; // Reload OTP page
</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify OTP</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>

<body style="background-color: #f2f2f2;">

    <div class="container mt-5">
        <div class="form-card">
            <h3 class="text-center">Verify OTP</h3>
            <form method="POST" action="verify_otp.php">
                <div class="form-group">
                    <label for="otp">Enter OTP</label>
                    <input type="text" class="form-control" id="otp" name="otp" placeholder="Enter OTP" required>
                </div>
                <button type="submit" class="btn btn-success btn-block">Verify OTP</button>
            </form>
        </div>
    </div>

</body>

</html>