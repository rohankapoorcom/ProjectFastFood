<?php
	require_once './vendor/autoload.php';

	$loader = new Twig_Loader_Filesystem('./templates');
	$twig = new Twig_Environment($loader);

	$mysqlDetails[0] = getenv('MYSQL_SERVER');
	$mysqlDetails[1] = getenv('MYSQL_USER');
	$mysqlDetails[2] = getenv('MYSQL_PASS');
	$mysqlDetails[3] = getenv('MYSQL_DB_NAME');

	if (strcmp($mysqlDetails[1], "") == 0) {
		$mysqlDetails[0] = 'localhost';
		$mysqlDetails[1] = '411test';
		$mysqlDetails[2] = '411test';
		$mysqlDetails[3] = '411_test';
	}

	$mysql_con = mysqli_connect($mysqlDetails[0], $mysqlDetails[1], $mysqlDetails[2], $mysqlDetails[3]);

	if (mysqli_connect_errno()) {
  		echo "Failed to connect to MySQL: " . mysqli_connect_error();
  	}

	echo $twig->render('navbar.html');
?>