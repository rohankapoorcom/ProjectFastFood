<?php
include_once './db-connect.php';
include_once './functions.php';

session_start();

if (isset($_POST['street'], $_POST['zipcode'], $_POST['restaurant'], $_POST['price'],
	$_POST['time-placed'], $_POST['time-delivered'])) {

	$street = filter_input(INPUT_POST, 'street', FILTER_SANITIZE_STRING);
	$zipcode = filter_input(INPUT_POST, 'zipcode', FILTER_SANITIZE_STRING);
	$restaurant = filter_input(INPUT_POST, 'restaurant', FILTER_SANITIZE_STRING);
	$price = $_POST['price']; //=filter_input(INPUT_POST, 'price', FILTER_SANITIZE_NUMBER_FLOAT);

	$time_p = $_POST['time-placed'];
	$time_p[10] = ' ';
	$time_p = date_create_from_format('Y-m-d H:i', $time_p);
	$time_placed = $time_p->format('Y-m-d H:i:s');

	$time_d = $_POST['time-delivered'];
	$time_d[10] = ' ';
	$time_d = date_create_from_format('Y-m-d H:i', $time_d);
	$time_delivered = $time_d->format('Y-m-d H:i:s');


	$prep_stmt = "SELECT id FROM locations WHERE street = ? AND zip = ?";
	$stmt = $mysql_con->prepare($prep_stmt);

	$location_id;

	if ($stmt) {
		$stmt->bind_param('si', $street, $zipcode);
		$stmt->execute();
		$stmt->store_result();

		print("I'm here");

		if ($stmt->num_rows == 1) {
			// We have the id
			$stmt->bind_result($location_id);
			$stmt->fetch();

		}

		else {
			// we manufacture a location
			$prep_stmt = "INSERT INTO locations (street, zip) VALUES(?, ?)";
			$stmt = $mysql_con->prepare($prep_stmt);

			if ($stmt) {
				$stmt->bind_param('si', $street, $zipcode);
				$stmt->execute();
				$location_id = mysqli_insert_id($mysql_con);
			}

		}
	}

	$prep_stmt = "INSERT INTO orders (location, restaurant, userID, price, timePlaced, timeArrived)
		VALUES(?, ?, ?, ?, ?, ?)";
	$stmt = $mysql_con->prepare($prep_stmt);

	$uid = $_SESSION['user_id'];

	if ($stmt) {
		$stmt->bind_param('iiidss', $location_id, $restaurant, $uid, $price, $time_placed, $time_delivered);
		$stmt->execute();

		header('Location: /');
	}
}