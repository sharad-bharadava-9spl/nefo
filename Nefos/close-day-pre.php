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
	
	unset($_SESSION['noCompare']);
	
	/* LOGIC
	
		- Openandclose = 2, Close only: Check if last closing exists, then do compare or no-compare.
		
		// If dayclosed = 0, throw error: Last closing was not completed! Finalise it now.
		
		- Openandclose = 3, Open and Close, check last Opening. If not exist, throw error. Can't close day without opening it first!
		- Openandclose = 4, Shifts. Check for last shift close/open, find a logic
	
	*/

	// Accountability: "Close only" or "None"
	if ($_SESSION['openAndClose'] < 2) {
		
		$_SESSION['errorMessage'] = $lang['close-day-not-using-closed'];
		pageStart($lang['title-closeday'], NULL, $confirmLeave, "pcloseday", "", $lang['title-closeday'] . " - ERROR", $_SESSION['successMessage'], $_SESSION['errorMessage']);
		exit();

	} else if ($_SESSION['openAndClose'] == 2) {
		
		// Check if a closing exists, to use comparison.
		$openingLookup = "SELECT closingid FROM closing WHERE currentClosing = 0";
		
		$result = mysql_query($openingLookup)
			or handleError($lang['error-noopeningdetails'],"Error loading opening from db: " . mysql_error());

		if (mysql_num_rows($result) == 0) {
			
			// No previous closing. Do closing without comparison.
			$_SESSION['errorMessage'] = $lang['no-closing-data-found-continue'];
			header("Location: close-day.php?noComp");
			exit();
		
		} else {
	
			// Closing found. Do closing WITH comparison.
			header("Location: close-day.php");
			exit();
			
		}
		
	} else if ($_SESSION['openAndClose'] == 3) {
		
		// Check if an opening exists, if it doesn't throw error: No opening exists
		$openingLookup = "SELECT openingtime, dayClosed FROM opening ORDER BY openingtime DESC LIMIT 1";
		
		$result = mysql_query($openingLookup)
			or handleError($lang['error-noopeningdetails'],"Error loading opening from db: " . mysql_error());

		if (mysql_num_rows($result) == 0) {
			
			// No previous opening.
			$_SESSION['errorMessage'] = $lang['day-not-opened'];
			pageStart($lang['title-closeday'], NULL, $confirmLeave, "pcloseday", "", $lang['title-closeday'] . " - ERROR", $_SESSION['successMessage'], $_SESSION['errorMessage']);
			exit();
		
		} else {
			
			// If just ONE opening, check if it's closed. If not, throw error "opening in progress, finish opening first".
			
			// Check if any open day exists that hasn't been closed? If not, throw an error: You need to open a day before you can close one.
			$row = mysql_fetch_array($result);
				$openingtime = $row['openingtime'];
				$dayClosed = $row['dayClosed'];
				
			if ($dayClosed == 2) {
				
				$_SESSION['errorMessage'] = $lang['day-not-opened'];
				pageStart($lang['title-closeday'], NULL, $confirmLeave, "pcloseday", "", $lang['title-closeday'] . " - ERROR", $_SESSION['successMessage'], $_SESSION['errorMessage']);
				exit();
			
			}
	
			// Opening found. Do closing WITH comparison.
			header("Location: close-day.php");
			exit();
			
		}
		
	} else if ($_SESSION['openAndClose'] == 4) {
		
		// Check if an open shift exists:
		// If exists, check if it's been closed. If not exists, throw error "No shift exists".
		$openingLookup = "SELECT openingid, openingtime, tillBalance, bankBalance, shiftClosed AS closed, openedby, 'opening' AS type FROM opening UNION ALL SELECT openingid, openingtime, tillBalance, bankBalance, shiftClosed AS closed, openedby, 'shiftopen' AS type FROM shiftopen ORDER BY openingtime DESC LIMIT 1";
		
		$result = mysql_query($openingLookup)
			or handleError($lang['error-noopeningdetails'],"Error loading opening from db: " . mysql_error());
			
		if (mysql_num_rows($result) == 0) {
	
			$_SESSION['errorMessage'] = $lang['open-shift-no-shift-exists'];
			pageStart($lang['close-shift-and-day'], NULL, $confirmLeave, "pcloseday", "", $lang['close-shift-and-day'] . " - ERROR", $_SESSION['successMessage'], $_SESSION['errorMessage']);
			exit();
	
		} else {
			
			$row = mysql_fetch_array($result);
				$openingid = $row['openingid'];	
				$openingtime = $row['openingtime'];	
				$tillBalance = $row['tillBalance'];
				$bankBalance = $row['bankBalance'];
				$closed = $row['closed'];
				$openedby = $row['openedby'];
				$type = $row['type'];
		
			$_SESSION['openingid'] = $openingid;
			$_SESSION['openingtime'] = $openingtime;
			$_SESSION['tillBalance'] = $tillBalance;
			$_SESSION['bankBalance'] = $bankBalance;
			$_SESSION['type'] = $type;
			
			$openingtimeView = date('d-m-Y H:i', strtotime($openingtime));
			$openingBy = getOperator($openedby);
					
			if ($closed == 2) {
				
				$_SESSION['errorMessage'] = $lang['close-shift-shift-closed'];
				pageStart($lang['close-shift-and-day'], NULL, $confirmLeave, "pcloseday", "", $lang['close-shift-and-day'] . " - ERROR", $_SESSION['successMessage'], $_SESSION['errorMessage']);
				exit();
					
			} else {
				
				// Proceed with closing process.
				header("Location: close-shift-and-day.php");
				exit();

			}
	
		}
		
	}