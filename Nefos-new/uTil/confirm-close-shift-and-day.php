<?php

	require_once '../cOnfig/connection.php';
	require_once '../cOnfig/authenticate.php';
	require_once '../cOnfig/languages/common.php';
	
	$accessLevel = '3';

	// Authenticate & authorize
	authorizeUser($accessLevel);
	
	// Retrieve System settings
	getSettings();
	
	// Get info
	$closingid = $_GET['csid'];
	$openingid = $_GET['osid'];
	$dayclosingid = $_GET['cid'];
	$dayopeningid = $_GET['oid'];
	$closer = $_GET['closer'];
	
	$member = getUser($closer);
	
	$closingtimeReal = date('Y-m-d H:i:s');
	
	// Make changes to OPENING tables
  	$query = "UPDATE opening SET dayClosed = 2, dayClosedBy = $closer WHERE openingid = $dayopeningid";
  	
	mysql_query($query)
		or handleError($lang['error-loadsales'],"Error loading sale from db: " . mysql_error());
		
  	$query = "UPDATE shiftopen SET shiftClosed = 2, shiftClosedBy = $closer WHERE openingid = $openingid";
  	
	mysql_query($query)
		or handleError($lang['error-loadsales'],"Error loading sale from db: " . mysql_error());
		
		
	// Make changes to CLOSING tables
 	$query = "UPDATE closing SET closingtime = '$closingtimeReal' WHERE closingid = $dayclosingid";

	mysql_query($query)
		or handleError($lang['error-loadsales'],"Error loading sale from db: " . mysql_error());
		
 	$query = "UPDATE shiftclose SET closingtime = '$closingtimeReal' WHERE closingid = $closingid";

	mysql_query($query)
		or handleError($lang['error-loadsales'],"Error loading sale from db: " . mysql_error());

	// On success: redirect.
	$_SESSION['successMessage'] = "Turno & Dia cerrado con exito!";
	header("Location: ../admin.php");
	exit();
		  	
