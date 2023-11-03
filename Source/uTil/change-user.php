<?php

	require_once '../cOnfig/connection.php';
	require_once '../cOnfig/authenticate.php';

	session_start();
	$accessLevel = '3';
	
	// Authenticate & authorize
	authorizeUser($accessLevel);
		
	// Build the delete statement
	$deleteUser = sprintf("DELETE FROM extract WHERE extractid = '%d';", $_REQUEST['extractid']);
	mysql_query($deleteUser)
		or handleError("Error deleting extract.","Error deleting extract: " . mysql_error());
	$_SESSION['successMessage'] = "Extract has been deleted.";
	header("Location: ../products.php");
?>
