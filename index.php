<?php
	require_once './vendor/autoload.php';
	include_once './includes/db-connect.php';

	$loader = new Twig_Loader_Filesystem('./templates');
	$twig = new Twig_Environment($loader);

	

	echo $twig->render('home.html');
?>