<?php

	require_once '../cOnfig/connection.php';
	require_once '../cOnfig/authenticate.php';
	require_once '../cOnfig/languages/common.php';

	session_start();
	$accessLevel = '3';
	
	// Authenticate & authorize
	authorizeUser($accessLevel);

	$_SESSION['complete'] = 'yes';
	
	$inMenu = $_GET['menu'];
	$callid = $_GET['callid'];
	
	if ($inMenu == 'no') {
		
		$changeMenu = "UPDATE calls SET complete = 1 WHERE id = $callid";
		
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
		
		header("Location: ../calls.php");
		
	} else {

		$changeMenu = "UPDATE calls SET complete = 0 WHERE id = $callid";
		
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
			
		
		header("Location: ../calls.php");
		
	}