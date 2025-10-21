<?php 
session_start(); 
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="src/styles/header.css">
    <link rel="stylesheet" href="/src/styles/home.css">
    <link rel="stylesheet" href="src/styles/footer.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <title>Home</title>
</head>

<body>
    <?php include 'src/views/header.php'; ?>
    
    <?php include 'src/views/home.php'; ?>


    <?php include 'src/views/footer.php'; ?>
</body>

</html>