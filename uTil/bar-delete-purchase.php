<?php

	require_once '../cOnfig/connection.php';
	require_once '../cOnfig/authenticate.php';
	require_once '../cOnfig/languages/common.php';
	
	session_start();
	//$accessLevel = '3';
	
	// Authenticate & authorize
	authorizeUser($accessLevel);
		
	// Build the delete statement
	$deleteUser = sprintf("DELETE FROM b_purchases WHERE purchaseid = '%d';", $_REQUEST['purchaseid']);
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
	
	$_SESSION['successMessage'] = $lang['product-deleted'];
	if ($_SESSION['bar-purchasepage'] == 'open') {
		header("Location: ../bar-open-purchases.php");
	} else {
		header("Location: ../bar-closed-purchases.php");
	}