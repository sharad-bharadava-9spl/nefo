<?php

	require_once '../cOnfig/connection.php';
	require_once '../cOnfig/authenticate.php';
	require_once '../cOnfig/languages/common.php';

	session_start();
	$accessLevel = '3';
	
	// Authenticate & authorize
	authorizeUser($accessLevel);
		
	// Build the delete statement
	$deleteUser = sprintf("DELETE FROM providers WHERE id = '%d';", $_GET['providerid']);
	try
	{
		$result = $pdo3->prepare("$deleteUser")->execute();
	}
	catch (PDOException $e)
	{
			$error = 'Error fetching user: ' . $e->getMessage();
			echo $error;
			exit();
	}
		
	$_SESSION['successMessage'] = $lang['provider-deleted'];
	header("Location: ../providers.php");