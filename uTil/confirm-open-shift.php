<?php

	require_once '../cOnfig/connection.php';
	require_once '../cOnfig/authenticate.php';
	require_once '../cOnfig/languages/common.php';
	
	//$accessLevel = '3';

	// Authenticate & authorize
	authorizeUser($accessLevel);
	
	// Retrieve System settings
	getSettings();
	
	// Get info
	$openingid = $_GET['oid'];
	$closingid = $_GET['cid'];
	$opener = $_GET['closer'];
	
	$member = getUser($closer);
	
	$openingtime = date('Y-m-d H:i:s');
	
	// Make changes to CLOSING table
  	$query = "UPDATE shiftclose SET shiftOpened = 2, shiftOpenedBy = $opener, shiftOpenedNo = $openingid WHERE closingid = $closingid";
	try
	{
		$result = $pdo3->prepare("$query")->execute();
	}
	catch (PDOException $e)
	{
			$error = 'Error fetching user: ' . $e->getMessage();
			echo $error;
			exit();
	}
	
	$query = "UPDATE shiftopen SET openedby = $opener, openingtime = '$openingtime' WHERE openingid = $openingid";
	try
	{
		$result = $pdo3->prepare("$query")->execute();
	}
	catch (PDOException $e)
	{
			$error = 'Error fetching user: ' . $e->getMessage();
			echo $error;
			exit();
	}
	
	unset($_SESSION['firstOpening']);
	unset($_SESSION['noCompare']);

	
	// On success: redirect.
	$_SESSION['successMessage'] = $lang['shiftopened'];
	header("Location: ../admin.php");