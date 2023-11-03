<?php

	require_once '../cOnfig/connection.php';
	require_once '../cOnfig/authenticate.php';
	require_once '../cOnfig/languages/common.php';

	session_start();
	$accessLevel = '3';
	
	// Authenticate & authorize
	authorizeUser($accessLevel);
	
	$inMenu = $_GET['menu'];
	$saleid = $_GET['saleid'];
	
	if ($inMenu != 'No') {
		
		$changeMenu = "UPDATE sales SET delivered = 0 WHERE saleid = $saleid";
		
		try
		{
			$result = $pdo3->prepare("$changeMenu")->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
			
		
		header("Location: ../orders.php");
		
	} else {

		$changeMenu = "UPDATE sales SET delivered = 1 WHERE saleid = $saleid";
		
		try
		{
			$result = $pdo3->prepare("$changeMenu")->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
			
		
		header("Location: ../orders.php");
		
	}