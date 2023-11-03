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
	$closer = $_GET['closer'];
	
	$member = getUser($closer);
	
	$closingtimeReal = date('Y-m-d H:i:s');
	
	tzo();
	$closingtime = date("H:i");
	
	
	if ($_SESSION['type'] == 'opening') {
		
		// Make changes to OPENING table
	  	$query = "UPDATE opening SET shiftClosed = 2, shiftClosedBy = $closer WHERE openingid = $openingid";
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
			
	} else {
		
		// Make changes to OPENING table
	  	$query = "UPDATE shiftopen SET shiftClosed = 2, shiftClosedBy = $closer WHERE openingid = $openingid";
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
		
	}
		
 	$query = "UPDATE shiftclose SET closingtime = '$closingtimeReal' WHERE closingid = $closingid";
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

	// On success: redirect.
	$_SESSION['successMessage'] = $lang['shift-closed-succesfully'];
	header("Location: ../admin.php");