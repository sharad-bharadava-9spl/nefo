<?php

	require_once '../cOnfig/connection.php';
	require_once '../cOnfig/authenticate.php';

	session_start();
	$accessLevel = '3';
	
	// Authenticate & authorize
	authorizeUser($accessLevel);
	
	$emailid = $_GET['emailid'];
		
	// Build the delete statement
	$deleteEmail = "DELETE FROM closing_mails WHERE id = '$emailid'";
	
	mysql_query($deleteEmail)
		or handleError("Error deleting email recipient.","Error: " . mysql_error());
		
		$_SESSION['successMessage'] = $lang['email-deleted'];
	
		header("Location: ../closing-mails.php");
?>