<?php
	require_once './vendor/autoload.php';
	include_once './includes/db-connect.php';
	include_once './includes/functions.php';

	$loader = new Twig_Loader_Filesystem('./templates');
	$twig = new Twig_Environment($loader);

	session_start();

	if (isset($_POST['grubhub_text'])) {

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

		$query = "SELECT name, id from restaurants WHERE approved = 1";

		$rests = "";
		$i = 0;

		if ($result = $mysql_con->query($query)) {
			while ($row = $result->fetch_object()) {
				$rests[$i++] = $row;
			}

			$result->close();
		}

		$grubhub_data = filter_input(INPUT_POST, 'grubhub_text', FILTER_SANITIZE_STRING);

		$parsed_data = parse_grubhub($grubhub_data);

		if ($parsed_data !== FALSE) {
			$high_time = date_create_from_format("h:i A", $parsed_data->high, new DateTimeZone('America/Chicago'));
			$low_time = date_create_from_format("h:i A", $parsed_data->low, new DateTimeZone('America/Chicago'));
			$now = date_create(NULL, new DateTimeZone('America/Chicago'));
			$high_time = $high_time->format('Y-m-d H:i:s');
			$low_time = ($low_time < $now ? $low_time : $now);
			$low_time = $low_time->format('Y-m-d H:i:s');
			$parsed_data->high_time = $high_time;
			$parsed_data->low_time = $low_time;

			echo $twig->render('order2.html', array('username' => $username, 'parsed_data' => $parsed_data, "rests" => $rests));
		}
		else {
			header('Location: /order/');
			exit();	
		}

	}