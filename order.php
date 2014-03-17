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

	echo $twig->render('order.html', array('username' => $username, "rests" => $rests));
?>