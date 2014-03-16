<?php
	require_once './vendor/autoload.php';
	include_once './includes/db-connect.php';
	include_once './includes/functions.php';
	include_once './includes/register.inc.php';

	$loader = new Twig_Loader_Filesystem('./templates');
	$twig = new Twig_Environment($loader);

	$esc_url = esc_url($_SERVER['PHP_SELF']);

	$registration_error = '';
	if (!empty($error_msg)) {
    	$registration_error = $error_msg;
    }

	echo $twig->render('register.html', array('registration_error' => $registration_error, 'esc_url' => $esc_url));
?>