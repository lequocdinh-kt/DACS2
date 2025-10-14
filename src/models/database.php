<?php
$dsn = 'mysql:host=localhost;port=3306;dbname=dacs2';
$username = 'root';
$password = '';
try {
    $db = new PDO($dsn, $username, $password);
    // echo "Connected to database";
} catch (PDOException $e) {
    $error_message = $e->getMessage();
    include('database_error.php');
    exit();
}
