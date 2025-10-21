<?php
// Test file to check if loginControllerAjax.php works
session_start();

// Simulate POST request
$_SERVER['REQUEST_METHOD'] = 'POST';
$_POST['email'] = 'test@example.com';
$_POST['password'] = 'password123';

// Include the controller
include 'src/controllers/loginControllerAjax.php';
?>
