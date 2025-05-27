<!doctype html>
<html lang="en" data-bs-theme="dark">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>BlackCode-hub | Contact</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <style>
        body {
            background-color: #121212;
            color: #ffffff;

        }

        .form-container {
            min-height: 73vh;
            background: #1e1e1e;
            border-radius: 16px;
            padding: 30px;
            max-width: 600px;
            margin: 50px auto;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.5);
        }

        .form-row {
            margin-bottom: 15px;
        }

        .form-field {
            width: 100%;
            padding: 10px;
            border-radius: 8px;
            border: none;
            background-color: #2c2c2c;
            color: white;
        }

        .form-field:focus {
            outline: 2px solid #0d6efd;
            background-color: #2c2c2c;
        }

        .send-btn {
            background-color: #0d6efd;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 8px;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        .send-btn:hover {
            background-color: #0b5ed7;
        }
    </style>
</head>

<body>
    <?php require 'partials/header.php'; ?>

    <div class="form-container">
        <form method="POST" action="contact.php"
            onsubmit="return showConfirmation()">
            <h2 class="mb-4 text-center">Send Message</h2>

            <div class="form-row">
                <label for="name">Full Name</label>
                <input type="text" class="form-field" id="name" name="name" required>
            </div>

            <div class="form-row">
                <label for="email">Email</label>
                <input type="email" class="form-field" id="email" name="email" required>
            </div>

            <div class="form-row">
                <label for="message">Type your message...</label>
                <textarea class="form-field" id="message" name="message" rows="4" required></textarea>
            </div>

            <input type="submit" class="send-btn w-100" name="send" value="Send">
        </form>
    </div>

    <?php require "partials/footer.php"; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>

<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'phpmailer/Exception.php';
require 'phpmailer/PHPMailer.php';
require 'phpmailer/SMTP.php';

if (isset($_POST['send'])) {
    // Sanitize and validate user input
    $name = htmlspecialchars(strip_tags($_POST['name']));
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    $message = htmlspecialchars(strip_tags($_POST['message']));

    // Create PHPMailer instance
    $mail = new PHPMailer(true);

    try {
        // SMTP configuration
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'mos399058@gmail.com'; // Consider using getenv() for security
        $mail->Password   = 'cycgdwegbvriwozm';     // Consider using getenv() for security
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        $mail->Port       = 465;

        // Email content
        $mail->setFrom('mos399058@gmail.com', 'iDiscuss Contact Form');
        $mail->addAddress('shoaib132002@gmail.com', 'Admin');

        $mail->isHTML(true);
        $mail->Subject = 'Message from iDiscuss';

        $mail->Body = "
            <div style='font-family: Arial, sans-serif; max-width: 600px; margin: auto; border: 1px solid #ddd; padding: 20px; border-radius: 10px; background-color: #f9f9f9;'>
                <h2 style='color: #333; text-align: center;'>📬 New Contact Message</h2>
                <hr style='border: none; border-top: 1px solid #ccc;'>
                <p><strong style='color: #555;'>👤 Sender Name:</strong> <span style='color: #000;'>$name</span></p>
                <p><strong style='color: #555;'>📧 Email Address:</strong> <a href='mailto:$email' style='color: #1a73e8;'>$email</a></p>
                <p><strong style='color: #555;'>📝 Message:</strong></p>
                <div style='background-color: #fff; padding: 15px; border: 1px solid #ccc; border-radius: 5px; color: #333; white-space: pre-wrap;'>
                    $message
                </div>
                <br>
                <p style='text-align: center; font-size: 12px; color: #888;'>This message was sent via your iDiscuss contact form.</p>
            </div>
        ";

        $mail->send();
        echo "
<script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>
<script>
    Swal.fire({
        icon: 'success',
        title: 'Message Sent!',
        text: 'Your message has been sent successfully.',
        confirmButtonColor: '#3085d6',
        confirmButtonText: 'OK'
    }).then(() => {
        window.location.href = 'contact.php';
    });
</script>
";
    } catch (Exception $e) {
        echo "
    <script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>
    <script>
        Swal.fire({
            icon: 'error',
            title: 'Oops!',
            text: 'Message could not be sent. {$mail->ErrorInfo}',
            confirmButtonColor: '#d33',
            confirmButtonText: 'Try Again'
        }).then(() => {
            window.location.href = 'contact.php';
        });
    </script>
    ";
    }
}
?>