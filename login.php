<?php
	require_once './vendor/autoload.php';
	include_once './includes/db-connect.php';
	include_once './includes/functions.php';

	$loader = new Twig_Loader_Filesystem('./templates');
	$twig = new Twig_Environment($loader);

	sec_session_start();

	if (login_check($mysql_con) == true) {
		$logged = 'in';
	} else {
		$logged = 'out';
	}

	$login_error = false;
	
	if (isset($_GET['error'])) {
            $login_error = true;
    }

	

	echo $twig->render('login.html', array('login_error' => $login_error));
?>