<?php

	require_once '../cOnfig/connection.php';
	require_once '../cOnfig/authenticate.php';
	require_once '../cOnfig/languages/common.php';

	session_start();
	$accessLevel = '3';
	
	// Authenticate & authorize
	authorizeUser($accessLevel);
	
	$inMenu = $_GET['menu'];
	$purchaseid = $_GET['purchaseid'];
	
	if ($inMenu != 'No') {
		
		$changeMenu = "UPDATE b_purchases SET inMenu = 0 WHERE purchaseid = $purchaseid";
		
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
			
		$_SESSION['successMessage'] = $lang['product-removed-from-menu'];
		
		header("Location: ../bar-purchases.php");
		
	} else {

		$changeMenu = "UPDATE b_purchases SET inMenu = 1 WHERE purchaseid = $purchaseid";
		
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
			
		$_SESSION['successMessage'] = $lang['product-added-to-menu'];
		
		header("Location: ../bar-purchases.php");
		
	}