<?php

	require_once '../cOnfig/connection.php';
	require_once '../cOnfig/authenticate.php';

	session_start();
	$accessLevel = '3';
	
	// Authenticate & authorize
	authorizeUser($accessLevel);
		
	// Build the delete statement
	$deleteUser = sprintf("DELETE FROM flower WHERE flowerid = '%d';", $_REQUEST['flowerid']);
	mysql_query($deleteUser)
		or handleError("Error deleting flower.","Error deleting flower: " . mysql_error());
	$_SESSION['successMessage'] = "Flower has been deleted.";
	header("Location: ../products.php");
?>
