<?php
include_once './db-connect.php';
include_once './functions.php';

session_start();

if (isset( $_POST['restaurant'], $_POST['price'],
	$_POST['time-placed'], $_POST['time-delivered'])) {

	$restaurant = filter_input(INPUT_POST, 'restaurant', FILTER_SANITIZE_STRING);
	$price = $_POST['price'];

	$time_placed = $_POST['time-placed'];

	$time_delivered = $_POST['time-delivered'];

	$orderID = filter_input(INPUT_POST, 'orderID', FILTER_SANITIZE_NUMBER_INT);

	$prep_stmt = "UPDATE orders SET restaurant=?, price=?, timePlaced=?, timeArrived=?
		WHERE id=? AND userID=?";
	$stmt = $mysql_con->prepare($prep_stmt);

	$uid = $_SESSION['user_id'];

	if ($stmt) {
		$stmt->bind_param('idssii', $restaurant, $price, $time_placed, $time_delivered, $orderID, $uid);
		$stmt->execute();
	}

	header('Location: /orders/');
}