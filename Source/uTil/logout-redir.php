<?php
	session_start();

	unset($_SESSION['user_id']);
	unset($_SESSION['username']);
	unset($_SESSION['memberno']);
	unset($_SESSION['first_name']);
	unset($_SESSION['userGroup']);
	unset($_SESSION['workStationAccess']);
	unset($_SESSION['workstation']);
	unset($_SESSION['cloud']);
	unset($_SESSION['domain']);
	unset($_SESSION['db_name']);
	unset($_SESSION['db_user']);
	unset($_SESSION['db_pwd']);
	unset($_SESSION['scanner']);
	unset($_COOKIE['ccsnubev2']);
	setcookie('ccsnubev2', '', time() - 3600);

	header('Location: https://www.google.es');