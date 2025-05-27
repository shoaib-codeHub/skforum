<?php
session_start();
session_unset();
session_destroy();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="refresh" content="2;url=/index.php" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Logging Out</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <style>
        body {
            background-color: #f8f9fa;
            display: flex;
            height: 100vh;
            justify-content: center;
            align-items: center;
            flex-direction: column;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .spinner-border {
            width: 3rem;
            height: 3rem;
        }
        .logout-message {
            margin-top: 20px;
            font-size: 1.3rem;
            color: #343a40;
        }
    </style>
</head>
<body>
    <div class="text-center">
        <div class="spinner-border text-primary" role="status" id="spinner">
            <span class="visually-hidden">Loading...</span>
        </div>
        <div class="logout-message" id="message">
            Logging you out, please wait...
        </div>
    </div>

    <script>
        $(document).ready(function () {
            // Fade in message and spinner
            $("#spinner").hide().fadeIn(400);
            $("#message").hide().delay(200).fadeIn(600);
        });
    </script>
</body>
</html>
