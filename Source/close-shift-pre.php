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
	
	
	// Accountability: "Close only" or "None"
	if ($_SESSION['openAndClose'] < 4) {
		
		$_SESSION['errorMessage'] = $lang['open-shift-not-using-open'];
		pageStart($lang['close-shift'], NULL, $confirmLeave, "pcloseday", "", $lang['close-shift'] . " - ERROR", $_SESSION['successMessage'], $_SESSION['errorMessage']);
		exit();

	}
	
	//NEW If last OPEN DAY has been closed, throw error.
	$openingLookup = "SELECT dayOpened FROM closing ORDER BY closingtime DESC LIMIT 1";
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
		
	$row = $data[0];
	$dayOpened = $row['dayOpened'];
	
	if ($data && $dayOpened < 2) {
	
		$_SESSION['errorMessage'] = $lang['day-not-open-cant-open-shift'];
		pageStart($lang['close-shift'], NULL, $confirmLeave, "pcloseday", "", $lang['close-shift'] . " - ERROR", $_SESSION['successMessage'], $_SESSION['errorMessage']);
		exit();
			
	}
			
		$selectRows = "SELECT COUNT(openingid) FROM opening";
		$rowCount1 = $pdo3->query("$selectRows")->fetchColumn();
		$selectRows = "SELECT COUNT(openingid) FROM shiftopen";
		$rowCount2 = $pdo3->query("$selectRows")->fetchColumn();
		$rowCount = $rowCount1 + $rowCount2;
		
	// Check if an open shift exists:
	// If exists, check if it's been closed. If not exists, throw error "No shift exists".
	$openingLookup = "SELECT openingid, openingtime, tillBalance, shiftClosed AS closed, openedby, 'opening' AS type, firstDayOpen FROM opening UNION ALL SELECT openingid, openingtime, tillBalance, shiftClosed AS closed, openedby, 'shiftopen' AS type, '' AS firstDayOpen FROM shiftopen ORDER BY openingtime DESC";
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
		pageStart($lang['close-shift'], NULL, $confirmLeave, "pcloseday", "", $lang['close-shift'] . " - ERROR", $_SESSION['successMessage'], $_SESSION['errorMessage']);
		exit();

	} else if ($rowCount == 1) {

		$row = $result->fetch();
			$firstDayOpen = $row['firstDayOpen'];	
			
		if ($firstDayOpen < 2) {
			
			$_SESSION['errorMessage'] = $lang['open-shift-no-shift-exists'];
			pageStart($lang['close-shift'], NULL, $confirmLeave, "pcloseday", "", $lang['close-shift'] . " - ERROR", $_SESSION['successMessage'], $_SESSION['errorMessage']);
			exit();
				
		} else {
			
			// Proceed with closing process.
			header("Location: close-shift.php");
			exit();
			
		}


	} else {
		
		$row = $result->fetch();
			$openingid = $row['openingid'];	
			$openingtime = $row['openingtime'];	
			$tillBalance = $row['tillBalance'];
			$closed = $row['closed'];
			$openedby = $row['openedby'];
			$type = $row['type'];
	
		$_SESSION['openingid'] = $openingid;
		$_SESSION['openingtime'] = $openingtime;
		$_SESSION['tillBalance'] = $tillBalance;
		$_SESSION['type'] = $type;
		
		$openingtimeView = date('d-m-Y H:i', strtotime($openingtime));
		$openingBy = getOperator($openedby);
				
		if ($closed == 2) {
			
			$_SESSION['errorMessage'] = $lang['close-shift-shift-closed'];
			pageStart($lang['close-shift'], NULL, $confirmLeave, "pcloseday", "", $lang['close-shift'] . " - ERROR", $_SESSION['successMessage'], $_SESSION['errorMessage']);
			exit();
				
		}
		/*else {
			
			// Open shift exists. Gotta see if it was opened fully, or if it's in progress
			$closingLookup = "SELECT shiftOpened, shiftOpenedNo FROM shiftclose ORDER BY closingtime DESC LIMIT 1";
			
			$resultC = mysql_query($closingLookup)
				or handleError($lang['error-closingload'],"Error loading closing from db: " . mysql_error());
				
			if (mysql_num_rows($resultC) > 0) {
				
				$rowC = mysql_fetch_array($resultC);
					$shiftOpened = $rowC['shiftOpened'];
					$shiftOpenedNo = $rowC['shiftOpenedNo'];
					
				if ($shiftOpened < 2) {
					
					$_SESSION['errorMessage'] = $lang['shiftopen-in-progress'];
					pageStart($lang['close-shift'], NULL, $confirmLeave, "pcloseday", "", $lang['close-shift'] . " - ERROR", $_SESSION['successMessage'], $_SESSION['errorMessage']);
					exit();
					
				}
			}
			
			
		}*/

			// Proceed with closing process.
			header("Location: close-shift.php");
			exit();
			
	}
		
displayFooter();