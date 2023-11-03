<?php

	require_once '../cOnfig/connection.php';
	require_once '../cOnfig/authenticate.php';

	session_start();
	$accessLevel = '3';
	
	// Authenticate & authorize
	authorizeUser($accessLevel);
		
	// Build the delete statement
	$deleteUser = sprintf("DELETE FROM productmovements WHERE movementid = '%d';", $_GET['movement_id']);
	mysql_query($deleteUser)
		or handleError("Error deleting movement.","Error deleting movement: " . mysql_error());
	$_SESSION['successMessage'] = "Product movement has been deleted.";
	header("Location: ../purchase.php?purchaseid=" . $_GET['purchaseid']);
?>