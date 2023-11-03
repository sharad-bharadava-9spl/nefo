<?php
	
	require_once 'cOnfig/connection.php';
	require_once 'cOnfig/view-closing.php';
	require_once 'cOnfig/authenticate.php';
	require_once 'cOnfig/languages/common.php';
	
	session_start();
	$accessLevel = '3';
	
	// Authenticate & authorize
	authorizeUser($accessLevel);
	
	getSettings();
	
	
	// Look up open day details
	$openingLookup = "SELECT openingid FROM opening";

	$result = mysql_query($openingLookup)
		or handleError($lang['error-noopeningdetails'],"Error loading opening from db: " . mysql_error());

	if (mysql_num_rows($result) == 0) {
		
		header("Location: open-day-pre.php");
		exit();
		
	} else {
		
		header("Location: open-shift-pre.php");
		exit();
		
	}
