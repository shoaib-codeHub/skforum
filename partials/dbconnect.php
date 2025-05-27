<?php
$servername = "sql302.infinityfree.com";
$username = "if0_39080379";
$password = "CfCNHkEgzOxAIFj";
$database = "if0_39080379_idiscuss";

$conn = mysqli_connect($servername, $username, $password, $database);

if (!$conn) {
    echo 'database connection failed';
}
