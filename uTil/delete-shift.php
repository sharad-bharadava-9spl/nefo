<?php

	require_once '../cOnfig/connection.php';
	require_once '../cOnfig/authenticate.php';

	session_start();
	//$accessLevel = '3';
	
	// Authenticate & authorize
	authorizeUser($accessLevel);
	
	// Get note ID and user ID
	$shiftid = $_GET['shiftid'];
	$shifttype = $_GET['shifttype'];
	
	
	if ($shifttype == 1) {
		$deleteShift = "DELETE FROM closing WHERE closingid = $shiftid";
		$deleteShift2 = "DELETE FROM closingdetails WHERE closingid = $shiftid";
	} else if ($shifttype == 2) {
		$deleteShift = "DELETE FROM opening WHERE openingid = $shiftid";
		$deleteShift2 = "DELETE FROM openingdetails WHERE openingid = $shiftid";
	} else if ($shifttype == 3) {
		$deleteShift = "DELETE FROM shiftclose WHERE closingid = $shiftid";
		$deleteShift2 = "DELETE FROM shiftclosedetails WHERE closingid = $shiftid";
	} else if ($shifttype == 4) {
		$deleteShift = "DELETE FROM shiftopen WHERE openingid = $shiftid";
		$deleteShift2 = "DELETE FROM shiftopendetails WHERE openingid = $shiftid";
	}

	mysql_query($deleteShift)
		or handleError("Error deleting shift.","Error deleting shift: " . mysql_error());
	mysql_query($deleteShift2)
		or handleError("Error deleting shift.","Error deleting shift: " . mysql_error());
	$_SESSION['successMessage'] = "Shift has been deleted.";
	header("Location: ../shifts.php");
	
?>