<?php

	require_once '../cOnfig/connection.php';
	require_once '../cOnfig/authenticate.php';

	session_start();
	$accessLevel = '3';
	
	// Authenticate & authorize
	authorizeUser($accessLevel);
		
	// Build the delete statement
	$deleteUser = sprintf("DELETE FROM productmovements WHERE movementid = '%d';", $_GET['movement_id']);
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
	$_SESSION['successMessage'] = $lang['movement-deleted'];
	header("Location: ../purchase.php?purchaseid=" . $_GET['purchaseid']);