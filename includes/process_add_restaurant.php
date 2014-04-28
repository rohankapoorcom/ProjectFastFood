<?php
include_once './db-connect.php';
include_once './functions.php';

session_start();

if (isset($_POST['street'], $_POST['zipcode'], $_POST['name'], $_POST['category'],
	$_POST['delivers'])) {

	$street = filter_input(INPUT_POST, 'street', FILTER_SANITIZE_STRING);
	$zipcode = filter_input(INPUT_POST, 'zipcode', FILTER_SANITIZE_STRING);
	$name = filter_input(INPUT_POST, 'name', FILTER_SANITIZE_STRING);
	$category = filter_input(INPUT_POST, 'category', FILTER_SANITIZE_STRING);
	$delivers = filter_input(INPUT_POST, 'delivers', FILTER_SANITIZE_STRING);


	$prep_stmt = "SELECT id FROM locations WHERE street = ? AND zip = ?";
	$stmt = $mysql_con->prepare($prep_stmt);

	$location_id;

	if ($stmt) {
		$stmt->bind_param('si', $street, $zipcode);
		$stmt->execute();
		$stmt->store_result();

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

	$prep_stmt = "INSERT INTO restaurants (name, location, category, delivers, approved, requester)
		VALUES(?, ?, ?, ?, ?, ?)";
	$stmt = $mysql_con->prepare($prep_stmt);

	$uid = $_SESSION['user_id'];
	$approved = 1;

	if ($stmt) {
		$stmt->bind_param('siiiii', $name, $location_id, $category, $delivers, $approved, $uid);
		$stmt->execute();

		header('Location: /');
	}
}