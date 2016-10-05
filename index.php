<?php
	require_once __DIR__.'/vendor/autoload.php';
	require_once './Controller/Route.php';

	$router = new Route();
	$router->routing();
?>
