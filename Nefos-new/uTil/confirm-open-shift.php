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
	$openingid = $_GET['oid'];
	$closingid = $_GET['cid'];
	$opener = $_GET['closer'];
	
	$member = getUser($closer);
	
	$openingtime = date('Y-m-d H:i:s');
	
	// Make changes to CLOSING table
  	$query = "UPDATE shiftclose SET shiftOpened = 2, shiftOpenedBy = $opener, shiftOpenedNo = $openingid WHERE closingid = $closingid";
  	
	mysql_query($query)
		or handleError($lang['error-loadsales'],"Error loading sale from db: " . mysql_error());


	$query = "UPDATE shiftopen SET openedby = $opener, openingtime = '$openingtime' WHERE openingid = $openingid";
	
	mysql_query($query)
		or handleError($lang['error-loadsales'],"Error loading sale from db: " . mysql_error());
	
	unset($_SESSION['firstOpening']);
	unset($_SESSION['noCompare']);

	
	// On success: redirect.
	$_SESSION['successMessage'] = $lang['shiftopened'];
	header("Location: ../admin.php");
	exit();
		  	
