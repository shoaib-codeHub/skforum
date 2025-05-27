<?php
$showError = "false";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    include_once __DIR__ . '/dbconnect.php'; // ✅ CORRECT

    $user_email = mysqli_real_escape_string($conn, $_POST['signupEmail']);
    $pass = $_POST['signupPassword'];
    $cpass = $_POST['signupCPassword'];

    // Check if the email already exists
    $existsql = "SELECT * FROM `users` WHERE `user_email` = '$user_email'";
    $result = mysqli_query($conn, $existsql);

    if (mysqli_num_rows($result) > 0) {
        $showError = "Username already in use";
    } else {
        if ($pass === $cpass) {
            // Hash the password correctly
            $hash = password_hash($pass, PASSWORD_DEFAULT);

            // Insert new user into database
            $sql = "INSERT INTO `users` (`sno`, `user_email`, `user_pass`, `timestamp`) 
                    VALUES (NULL, '$user_email', '$hash', current_timestamp())";

            $result = mysqli_query($conn, $sql);

            if ($result) {
                header("Location: ../index.php?signupsuccess=true"); // if inside /partials/
                exit;
            } else {
                $showError = "Error inserting user: " . mysqli_error($conn);
            }
        } else {
            $showError = "Passwords don't match";
        }
    }

    header("Location: /forum/index.php?signupsuccess=false&error=" . urlencode($showError));
    exit;
}
