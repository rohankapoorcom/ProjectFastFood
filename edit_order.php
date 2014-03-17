<?php
	require_once './vendor/autoload.php';
	include_once './includes/db-connect.php';
	include_once './includes/functions.php';

	$loader = new Twig_Loader_Filesystem('./templates');
	$twig = new Twig_Environment($loader);

	session_start();

	if (login_check($mysql_con) == true) {
		$id = $_SESSION['user_id'];

		$stmt = $mysql_con->prepare("SELECT name FROM users WHERE id = ? LIMIT 1");
		$stmt->bind_param('i', $id);
		$stmt->execute();
		$stmt->store_result();

		if ($stmt->num_rows == 1) {
			$stmt->bind_result($username);
			$stmt->fetch();
		}

	}

	else {
		$_SESSION['login_redir'] = substr($_SERVER["REQUEST_URI"], 1);
		header('Location: /login/');
		exit();
	}

	$query = "SELECT name, id from restaurants";

	$rests;
	$i = 0;

	if ($result = $mysql_con->query($query)) {
		while ($row = $result->fetch_object()) {
			$rests[$i++] = $row;
		}

		$result->close();
	}

	$orderID = $_GET['id'];

	$stmt = $mysql_con->prepare("SELECT orderID, id, timePlaced, timeArrived, name, durationSeconds, location, price
			FROM restaurants NATURAL JOIN (
				SELECT id AS orderID, restaurant AS id, timePlaced, timeArrived, price, TIMESTAMPDIFF(second, timePlaced, timeArrived) as durationSeconds
				FROM orders where userID=? AND id=?
			) AS DeliverTimes");

	$stmt->bind_param('ii', $id, $orderID);
	$stmt->execute();

	$result = $stmt->get_result();

	if ($order = $result->fetch_object()) {
		// Whee!
		$stmt = $mysql_con->prepare("SELECT street, zip FROM locations WHERE id=?");
		$stmt->bind_param('i', $order->location);
		$stmt->execute();

		$result = $stmt->get_result();

		$location = $result->fetch_object();

		$tmp = $order->timePlaced;
		$tmp[10] = 'T';
		$order->timePlaced = $tmp;

		$tmp = $order->timeArrived;
		$tmp[10] = 'T';
		$order->timeArrived = $tmp;
	}

	else {
		header('Location: /orders/');
		exit();
	}

	echo $twig->render('edit_order.html', array('order' => $order, "rests" => $rests,
		"loc" => $location));
?>