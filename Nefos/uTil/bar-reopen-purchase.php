<?php

	require_once '../cOnfig/connection.php';
	require_once '../cOnfig/authenticate.php';

	session_start();
	$accessLevel = '3';
	
	// Authenticate & authorize
	authorizeUser($accessLevel);
		
	// Does purchase ID exist?
	if (!$_GET['purchaseid']) {
		echo "Error: No purchase selected. Please go back and correct.";
		exit();
	} else  {
		$purchaseid = $_GET['purchaseid'];
	}
		
	// Build the re-open statement
	$reopenPurchase = sprintf("UPDATE b_purchases SET estClosing = NULL, closingComment = NULL, closedAt = NULL, closedSales = NULL, closedReloads = NULL, closedTakeouts = NULL, closedAdditions = NULL, closingDate = NULL, inMenu = '1' WHERE purchaseid = '%d';", $_GET['purchaseid']);
	try
	{
		$result = $pdo3->prepare("$reopenPurchase")->execute();
	}
	catch (PDOException $e)
	{
			$error = 'Error fetching user: ' . $e->getMessage();
			echo $error;
			exit();
	}
		
	$_SESSION['successMessage'] = $lang['product-reopened-added-to-menu'];
	header("Location: ../bar-purchase.php?purchaseid=$purchaseid");