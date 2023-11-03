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
		
		$selectUsers = "SELECT COUNT(closingid) FROM closing";
		$rowCount = $pdo3->query("$selectUsers")->fetchColumn();
		// Check if a closing exists, to use comparison.
		$openingLookup = "SELECT closingid, dayClosed FROM closing ORDER BY closingtime DESC";
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
			
			// No previous closing. Do closing without comparison.
			$_SESSION['errorMessage'] = $lang['no-closing-data-found-continue'];
			header("Location: close-day.php?noComp");
			exit();
		
		} else if ($rowCount == 1) {
			
			$row = $result->fetch();
				$dayClosed = $row['dayClosed'];
				
			if ($dayClosed == 2) {
				
				// Closing found. Do closing WITH comparison - but only if that day has been closed fully!
				header("Location: close-day.php");
				exit();
				
			} else {
			
				$_SESSION['errorMessage'] = $lang['no-closing-data-found-continue'];
				header("Location: close-day.php?noComp");
				exit();
		
			}
			
		} else {
			
			// Closing found. Do closing WITH comparison - but only if that day has been closed fully!
			header("Location: close-day.php");
			exit();
			
		}
		
	} else if ($_SESSION['openAndClose'] == 3) {
		
		$selectUsers = "SELECT COUNT(openingtime) FROM opening";
		$rowCount = $pdo3->query("$selectUsers")->fetchColumn();
		
		// Check if an opening exists, if it doesn't throw error: No opening exists
		$openingLookup = "SELECT openingtime, dayClosed, firstDayOpen, dayClosedNo FROM opening ORDER BY openingtime DESC";
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
			
			// No previous opening.
			$_SESSION['errorMessage'] = $lang['day-not-opened'];
			pageStart($lang['title-closeday'], NULL, $confirmLeave, "pcloseday", "", $lang['title-closeday'] . " - ERROR", $_SESSION['successMessage'], $_SESSION['errorMessage']);
			exit();
		
		// If just ONE opening, check if it's closed. If not, throw error "opening in progress, finish opening first".
		} else if ($rowCount == 1) {
			
			$row = $result->fetch();
				$openingtime = $row['openingtime'];
				$firstDayOpen = $row['firstDayOpen'];
				$dayClosed = $row['dayClosed'];
				
			if ($firstDayOpen < 2) {
				
				$_SESSION['errorMessage'] = $lang['day-not-finished-opening'];
				pageStart($lang['title-closeday'], NULL, $confirmLeave, "pcloseday", "", $lang['title-closeday'] . " - ERROR", $_SESSION['successMessage'], $_SESSION['errorMessage']);
				exit();
			
			} else if ($dayClosed == 2) {
				
				$_SESSION['errorMessage'] = $lang['day-already-closed'];
				pageStart($lang['title-closeday'], NULL, $confirmLeave, "pcloseday", "", $lang['title-closeday'] . " - ERROR", $_SESSION['successMessage'], $_SESSION['errorMessage']);
				exit();
				
			} else {
				
				header("Location: close-day.php");
				exit();
				
			}
			
		} else {
			
			// Check if any open day exists that hasn't been closed? If not, throw an error: You need to open a day before you can close one.
			$openingLookup = "SELECT dayOpened, closingtime FROM closing ORDER BY closingtime DESC LIMIT 1";
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
	
		$row = $result->fetch();
				$dayOpened = $row2['dayOpened'];
				$closetime = $row2['closingtime'];
				$closingtime = date("d-m-Y", strtotime($row2['closingtime']));
				$timenow = date("d-m-Y");
				
			// Check if the closingid above matches the
				
			if ($dayOpened != 2) { // this is needed in case you're in the middle of today's closing, then click CLOSE DAY:  && $closingtime != $timenow
				
				// Problem her, hvis man stengte i GÅR, og ikke gjorde seg ferdig.. Da får man ikke stengt ferdig i dag, ettersom datoen ikke er den samme!!!! Finn en annen løsning!!!!
				
				// Nå har du laget en NY closing. Og den er selvsagt ikke Opened yet. Derfor fremkaller denne error'n når du klikker CLOSE DAY underveis i closingen
				// Sjekk om det finnes en closing fra I DAG (den du nettopp har laget). Hvis det er tilfelle, 
				
				//*****************//
				// Check opening, if a closing is on-going or not!
				$openingLookup = "SELECT dayClosed FROM opening WHERE openingtime < '$closetime' ORDER BY openingtime DESC LIMIT 1";
				try
				{
					$result2 = $pdo3->prepare("$openingLookup");
					$result2->execute();
				}
				catch (PDOException $e)
				{
						$error = 'Error fetching user: ' . $e->getMessage();
						echo $error;
						exit();
				}
	
				$row2 = $result2->fetch();
					$dayClosed = $row2['dayClosed'];
					
				if ($dayClosed == 2) {
				
					$_SESSION['errorMessage'] = $lang['day-not-opened'];
					pageStart($lang['title-closeday'], NULL, $confirmLeave, "pcloseday", "", $lang['title-closeday'] . " - ERROR", $_SESSION['successMessage'], $_SESSION['errorMessage']);
					exit();
					
				}
				
			} else {
				
				// Check if last closing was complete (loo @ opening table). If so, throw error "Day has already been closed"
				$row = $result->fetch();
					$dayClosed = $row['dayClosed'];
					
				if ($dayClosed == 2) {
					
					$_SESSION['errorMessage'] = $lang['day-already-closed'];
					pageStart($lang['title-closeday'], NULL, $confirmLeave, "pcloseday", "", $lang['title-closeday'] . " - ERROR", $_SESSION['successMessage'], $_SESSION['errorMessage']);
					exit();
				
				}
				
			}

			
			$openingLookup = "SELECT openingtime, dayClosed, firstDayOpen, dayClosedNo FROM opening ORDER BY openingtime DESC";
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
	
		$row = $result->fetch();
				$openingtime = $row['openingtime'];
				$dayClosed = $row['dayClosed'];
				$dayClosedNo = $row['dayClosedNo'];
				
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
		
		$selectRows = "SELECT COUNT(openingid) FROM opening";
		$rowCount1 = $pdo3->query("$selectRows")->fetchColumn();
		
		$selectRows = "SELECT COUNT(openingid) FROM shiftopen";
		$rowCount2 = $pdo3->query("$selectRows")->fetchColumn();
		
		$rowCount = $rowCount1 + $rowCount2;
		
		// Check if an open shift exists:
		// If exists, check if it's been closed. If not exists, throw error "No shift exists".
		$openingLookup = "SELECT openingid, openingtime, tillBalance, bankBalance, shiftClosed AS closed, openedby, 'opening' AS type FROM opening UNION ALL SELECT openingid, openingtime, tillBalance, bankBalance, shiftClosed AS closed, openedby, 'shiftopen' AS type FROM shiftopen ORDER BY openingtime DESC LIMIT 1";
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
	
			$_SESSION['errorMessage'] = $lang['open-shift-no-shift-exists'];
			pageStart($lang['close-shift-and-day'], NULL, $confirmLeave, "pcloseday", "", $lang['close-shift-and-day'] . " - ERROR", $_SESSION['successMessage'], $_SESSION['errorMessage']);
			exit();
	
		} else {
			
			$row = $result->fetch();
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
				header("Location: close-day.php");
				exit();

			}
	
		}
		
	}
