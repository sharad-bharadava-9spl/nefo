<?php
	session_start();

	unset($_SESSION['user_id']);
	unset($_SESSION['username']);
	unset($_SESSION['memberno']);
	unset($_SESSION['first_name']);
	unset($_SESSION['userGroup']);
	unset($_SESSION['workStationAccess']);
	unset($_SESSION['workstation']);

	$_SESSION['successMessage'] = "You were logged out successfully.";
	header('Location: ../index.php');
?>