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
	$reopenPurchase = sprintf("UPDATE purchases SET estClosing = NULL, closingComment = NULL, closedAt = NULL, closedSales = NULL, closedReloads = NULL, closedTakeouts = NULL, closedAdditions = NULL, closingDate = NULL, inMenu = '1' WHERE purchaseid = '%d';", $_GET['purchaseid']);
	
	mysql_query($reopenPurchase)
		or handleError("Error re-opening product.","Error reopening purchase: " . mysql_error());
		
	$_SESSION['successMessage'] = "Product re-opened and added to Menu.";
	header("Location: ../purchase.php?purchaseid=$purchaseid");
?>