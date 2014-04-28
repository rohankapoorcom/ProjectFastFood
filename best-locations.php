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

	$rests = "";
	$i = 0;

	$catid ="";

	if (isset($_GET['id']))
		$catid = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT);

	$query = "CREATE OR REPLACE VIEW averages AS
				SELECT AVG(TIME_TO_SEC(TIMEDIFF(orders.timeArrived, orders.timePlaced))) AS wait_time,
					 AVG(orders.price) AS price,
					 orders.restaurant
				FROM orders 
				GROUP BY orders.restaurant";

	if ($result = $mysql_con->query($query)) {
		if ($catid) {
			$query = "SELECT restaurants.name, 
				SEC_TO_TIME(averages.wait_time) AS average_wait_time, 
				averages.price, categories.name AS category, categories.id AS catid
					FROM averages
					INNER JOIN restaurants 
					ON averages.restaurant = restaurants.id
					INNER JOIN categories
					ON restaurants.category = categories.id
					WHERE restaurants.category = $catid
					ORDER BY wait_time ASC, price ASC
					LIMIT 10";
		}

		else {
			$query = "SELECT restaurants.name, 
				SEC_TO_TIME(averages.wait_time) AS average_wait_time, 
				averages.price, categories.name AS category, categories.id AS catid
					FROM averages
					INNER JOIN restaurants 
					ON averages.restaurant = restaurants.id
					INNER JOIN categories
					ON restaurants.category = categories.id
					ORDER BY wait_time ASC, price ASC
					LIMIT 10";
		}

		if ($result = $mysql_con->query($query)) {
			while ($row = $result->fetch_object()) {
				$rests[$i++] = $row;
			}

			$result->close();
		}
	}

	echo $twig->render('best-locations.html', array('username' => $username, "restaurants" => $rests));
?>