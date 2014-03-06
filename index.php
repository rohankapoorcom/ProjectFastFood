<?php
	require_once './vendor/autoload.php';

	$loader = new Twig_Loader_String();
	$twig = new Twig_Environment($loader);
	echo 'I am alive';
?>