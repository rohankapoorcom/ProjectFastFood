<?php
include_once './db-connect.php';
include_once './functions.php';

session_start();

$del = $_POST['del'];
$del = array_keys($del);

$id = $_SESSION['user_id'];

foreach ($del as $i) {
	$stmt = $mysql_con->prepare("DELETE FROM orders WHERE id=? AND userID=?");
	$stmt->bind_param('ii', $i, $id);
	$stmt->execute();
}

header('Location: /orders/');