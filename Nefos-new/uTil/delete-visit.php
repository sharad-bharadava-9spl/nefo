<?php

	require_once '../cOnfig/connection.php';
	require_once '../cOnfig/authenticate.php';

	session_start();
	$accessLevel = '3';
	
	// Authenticate & authorize
	authorizeUser($accessLevel);
	
	$visitNo = $_GET['visitNo'];	
	$source = $_GET['source'];
		
	// Build the delete statement
	$deleteVisit = "DELETE FROM newvisits WHERE visitNo = $visitNo";
	
	mysql_query($deleteVisit)
		or handleError("Error deleting visit.","Error deleting visit: " . mysql_error());
		
	$_SESSION['successMessage'] = "Visit has been deleted.";
	
	if ($source == 'visits') {
		header("Location: ../visits.php");
	} else {
		header("Location: ../member-visits.php?userid=$userid");
	}
?>