<?php

	require_once '../cOnfig/connection.php';
	require_once '../cOnfig/authenticate.php';

	session_start();
	//$accessLevel = '3';
	
	// Authenticate & authorize
	authorizeUser($accessLevel);
		
	// Build the delete statement
	$deleteUser = sprintf("DELETE FROM users WHERE user_id = '%d';", $_REQUEST['user_id']);
	mysql_query($deleteUser)
		or handleError("Error deleting member.","Error deleting member: " . mysql_error());
	$_SESSION['successMessage'] = "Member has been deleted.";
	header("Location: ../members.php");
?>