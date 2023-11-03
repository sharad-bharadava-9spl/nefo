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
	
	// Look up open day details
	$selectRows = "SELECT COUNT(openingid) FROM opening";
	$rowCount = $pdo3->query("$selectRows")->fetchColumn();
		
	$openingLookup = "SELECT dayClosed, openingid, openingtime, tillBalance, bankBalance, firstDayOpen FROM opening ORDER BY openingtime DESC";
		try
		{
			$result = $pdo3->prepare("$openingLookup");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		
	if ($rowCount == 0) {
		
		$_SESSION['errorMessage'] =  $lang['day-not-opened'];
		pageStart($lang['close-shift-and-day'], NULL, $confirmLeave, "pcloseday", "", $lang['close-shift-and-day'] . " - ERROR", $_SESSION['successMessage'], $_SESSION['errorMessage']);
		exit();

	} else if ($rowCount == 1) {
		
		// First opening! Check if it's done
		$row = $result->fetch();
			$firstDayOpen = $row['firstDayOpen'];
			$dayClosed = $row['dayClosed'];
			$dayopeningid = $row['openingid'];
			$dayopeningtime = $row['openingtime'];
			$daytillBalance = $row['tillBalance'];
			$daybankBalance = $row['bankBalance'];
			
		if ($firstDayOpen < 2) {
			
			// If it's in progress, throw error
			$_SESSION['errorMessage'] = $lang['day-not-finished-opening'];
			pageStart($lang['close-shift-and-day'], NULL, $confirmLeave, "pcloseday", "", $lang['close-shift-and-day'] . " - ERROR", $_SESSION['successMessage'], $_SESSION['errorMessage']);
			exit();
			
		}
		
	} else {

		$row = $result->fetch();
			$dayClosed = $row['dayClosed'];
			$dayopeningid = $row['openingid'];
			$dayopeningtime = $row['openingtime'];
			$daytillBalance = $row['tillBalance'];
			$daybankBalance = $row['bankBalance'];
			
	}

	if ($dayClosed == 2) {

		// No open day available to close
		$_SESSION['errorMessage'] = $lang['day-not-opened'];
		pageStart($lang['close-shift-and-day'], NULL, $confirmLeave, "pcloseday", "", $lang['close-shift-and-day'] . " - ERROR", $_SESSION['successMessage'], $_SESSION['errorMessage']);
		exit();

	}
		
	// Look up open shift details
	$openingLookup = "SELECT shiftClosed, openingid, openingtime, tillBalance, bankBalance FROM shiftopen ORDER BY openingtime DESC LIMIT 1";
		try
		{
			$result = $pdo3->prepare("$openingLookup");
			$result->execute();
			$data = $result->fetchAll();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
			

	// No open shifts exist, only open day. Redirect accordingly. IF no shift exists ever, the next screen crashes!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
	if (!$data) {
		
		$_SESSION['errorMessage'] =  $lang['no-shift-only-day-exist'];
		header("Location: close-day-pre.php");
		exit();

	} else {

		// Opened shift exists
		$row = $data[0];
			$shiftClosed = $row['shiftClosed'];
			$openingid = $row['openingid'];	
			$openingtime = $row['openingtime'];	
			$tillBalance = $row['tillBalance'];
			$bankBalance = $row['bankBalance'];
			
		// Check if there are any shifts today (after last opening). If not, show error and just Close Day instead
		if ($openingtime < $dayopeningtime) {
					
			$_SESSION['errorMessage'] =  $lang['no-shift-only-day-exist'];
			header("Location: close-day-pre.php");
			exit();
				
		}
		


		
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