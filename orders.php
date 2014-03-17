<?php
	require_once './vendor/autoload.php';
	include_once './includes/db-connect.php';
	include_once './includes/functions.php';

	$loader = new Twig_Loader_Filesystem('./templates');
	$twig = new Twig_Environment($loader);

	session_start();

	$results;

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

		$stmt = $mysql_con->prepare("SELECT orderID, id, timePlaced, name, durationSeconds
			FROM restaurants NATURAL JOIN (
				SELECT id AS orderID, restaurant AS id, timePlaced, timeArrived, TIMESTAMPDIFF(second, timePlaced, timeArrived) as durationSeconds
				FROM orders where userID=?
			) AS DeliverTimes
		ORDER BY timePlaced ASC");

		$stmt->bind_param('i', $id);
		$stmt->execute();

		$i = 0;

		$result = $stmt->get_result();
		
		while ($row = $result->fetch_object()) {
			$results[$i++] = $row;			
		}
	}

	else {
		$_SESSION['login_redir'] = substr($_SERVER["REQUEST_URI"], 1);
		header('Location: /login/');
		exit();
	}

	echo $twig->render('orders.html', array('username' => $username, 'orders' => $results));
?>