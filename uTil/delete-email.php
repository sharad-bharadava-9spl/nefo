<?php

	require_once '../cOnfig/connection.php';
	require_once '../cOnfig/authenticate.php';
	require_once '../cOnfig/languages/common.php';
	session_start();
	//$accessLevel = '3';
	
	// Authenticate & authorize
	authorizeUser($accessLevel);
	
	$emailid = $_GET['emailid'];
		
	// Build the delete statement
	$deleteEmail = "DELETE FROM closing_mails WHERE id = '$emailid'";
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
		
		$_SESSION['successMessage'] = $lang['email-deleted'];
	
		header("Location: ../sys-settings.php");