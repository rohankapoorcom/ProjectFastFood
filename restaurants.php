<?php
	require_once './vendor/autoload.php';
	include_once './includes/db-connect.php';
	include_once './includes/functions.php';

	$loader = new Twig_Loader_Filesystem('./templates');
	$twig = new Twig_Environment($loader);

	$username = "";

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

	$query = "SELECT restaurants.name, restaurants.id, categories.name AS category, restaurants.delivers
				FROM restaurants
				INNER JOIN categories
				ON restaurants.category=categories.id
				WHERE restaurants.approved = 1";

	$rests = "";
	$i = 0;

	if ($result = $mysql_con->query($query)) {
		while ($row = $result->fetch_object()) {
			$rests[$i++] = $row;
		}

		$result->close();
	}

	echo $twig->render('list_restaurants.html', array('username' => $username, 'restaurants' => $rests));
?>