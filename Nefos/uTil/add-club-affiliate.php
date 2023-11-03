<?php

	require_once '../cOnfig/connection.php';
	require_once '../cOnfig/authenticate.php';
	require_once '../cOnfig/languages/common.php';

	session_start();
	$accessLevel = '3';
	
	// Authenticate & authorize
	authorizeUser($accessLevel);
	
	$affid = $_GET['affid'];
	$clubid = $_GET['clubid'];
	
	$changeMenu = "UPDATE customers SET affiliation = $affid WHERE id = $clubid";
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
		
	$_SESSION['successMessage'] = "Affiliate added succesfully!";
	
	header("Location: ../affiliation.php?affid=$affid");