<?php

	require_once '../cOnfig/connection.php';
	require_once '../cOnfig/authenticate.php';
	require_once '../cOnfig/languages/common.php';

	session_start();
	$accessLevel = '3';
	
	// Authenticate & authorize
	authorizeUser($accessLevel);
	
	$affid = $_GET['affid'];
	
	$changeMenu = "DELETE FROM affiliations WHERE id = $affid";
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
		
	$_SESSION['successMessage'] = "Affiliation deleted succesfully!";
	
	header("Location: ../affiliations.php");