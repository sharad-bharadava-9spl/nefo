<?php

	require_once '../cOnfig/connection.php';
	require_once '../cOnfig/authenticate.php';

	session_start();
	$accessLevel = '3';
	
	// Authenticate & authorize
	authorizeUser($accessLevel);
	
	$cuotaid = $_GET['cuotaid'];
		
	// Build the delete statement
	$deleteEmail = "DELETE FROM cuotas WHERE id = '$cuotaid'";
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
		
		$_SESSION['successMessage'] = $lang['cuota-deleted'];
	
		header("Location: ../cuotas.php");