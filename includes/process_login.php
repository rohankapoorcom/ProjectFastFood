<?php
include_once './db-connect.php';
include_once './functions.php';

session_start(); // Our custom secure way of starting a PHP session.
 
if (isset($_POST['email'], $_POST['p'])) {
    $email = $_POST['email'];
    $password = $_POST['p']; // The hashed password.
 
    if (login($email, $password, $mysql_con) == true) {
        // Login success 
        header('Location: /');
    } else {
        // Login failed 
        header('Location: /login.php?error=1');
    }
} else {
    // The correct POST variables were not sent to this page. 
    echo 'Invalid Request';
}