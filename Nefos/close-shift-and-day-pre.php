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
	
	
	
	// Accountability: Check if set to open&close with shifts
	if ($_SESSION['openAndClose'] < 4) {
		
		$_SESSION['errorMessage'] = $lang['open-shift-not-using-open'];
		pageStart($lang['close-shift-and-day'], NULL, $confirmLeave, "pcloseday", "", $lang['close-shift-and-day'] . " - ERROR", $_SESSION['successMessage'], $_SESSION['errorMessage']);
		exit();

	}
	
	// Find opening time of day
	$openingLookup = "SELECT dayClosed, openingid, openingtime, tillBalance, bankBalance FROM opening ORDER BY openingtime DESC LIMIT 1";
	
	$result = mysql_query($openingLookup)
		or handleError($lang['error-noopeningdetails'],"Error loading opening from db: " . mysql_error());

	$row = mysql_fetch_array($result);
		$dayClosed = $row['dayClosed'];
		$dayopeningid = $row['openingid'];
		$dayopeningtime = $row['openingtime'];
		$daytillBalance = $row['tillBalance'];
		$daybankBalance = $row['bankBalance'];
		
	if ($dayClosed == 2) {

		// No open day available to close
		$_SESSION['errorMessage'] = $lang['day-not-opened'];
		pageStart($lang['close-shift-and-day'], NULL, $confirmLeave, "pcloseday", "", $lang['close-shift-and-day'] . " - ERROR", $_SESSION['successMessage'], $_SESSION['errorMessage']);
		exit();

	}
		
	$openingLookup = "SELECT shiftClosed, openingid, openingtime, tillBalance, bankBalance FROM shiftopen ORDER BY openingtime DESC LIMIT 1";

	$result = mysql_query($openingLookup)
		or handleError($lang['error-noopeningdetails'],"Error loading opening from db: " . mysql_error());

	// No open shifts today, only open day. Redirect accordingly. IF no shift exists ever, the next screen crashes!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
	if (mysql_num_rows($result) == 0) {

		header("Location: close-day-pre.php");
		exit();

	} else {

		// Opened shift exists
		$row = mysql_fetch_array($result);
			$shiftClosed = $row['shiftClosed'];
			$openingid = $row['openingid'];	
			$openingtime = $row['openingtime'];	
			$tillBalance = $row['tillBalance'];
			$bankBalance = $row['bankBalance'];
		
		if ($shiftClosed == 2) {
	
			// Last opened shift has been closed
			$_SESSION['errorMessage'] = $lang['close-shift-shift-closed'];
			pageStart($lang['close-shift-and-day'], NULL, $confirmLeave, "pcloseday", "", $lang['close-shift-and-day'] . " - ERROR", $_SESSION['successMessage'], $_SESSION['errorMessage']);
			exit();
	
		}
		
		$_SESSION['dayopeningid'] = $dayopeningid;
		$_SESSION['dayopeningtime'] = $dayopeningtime;
		$_SESSION['daytillBalance'] = $daytillBalance;
		$_SESSION['daybankBalance'] = $daybankBalance;
		$_SESSION['openingid'] = $openingid;
		$_SESSION['openingtime'] = $openingtime;
		$_SESSION['tillBalance'] = $tillBalance;
		$_SESSION['bankBalance'] = $bankBalance;
		
		// continue to close day&shift
		header("Location: close-shift-and-day.php");
		exit();

	}

displayFooter();