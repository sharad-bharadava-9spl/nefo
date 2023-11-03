<?php

	require_once '../cOnfig/connection.php';
	require_once '../cOnfig/authenticate.php';

	session_start();
	//$accessLevel = '3';
	
	// Authenticate & authorize
	authorizeUser($accessLevel);
	
	$user_id = $_GET['user_id'];
		
	// Build the delete statement
	$deleteEmail = "DELETE FROM users WHERE user_id = '$user_id'";
	
	try
	{
		$result = $pdo3->prepare("$deleteEmail")->execute();
	}
	catch (PDOException $e)
	{
			$error = 'Error fetching user: ' . $e->getMessage();
			echo $error;
			exit();
	}
		
		$_SESSION['successMessage'] = "User deleted succesfully";
	
		header("Location: ../pre-reg.php");