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
	
	// Accountability: Anything else than Shifts
	if ($_SESSION['openAndClose'] < 4) {
		
		$_SESSION['errorMessage'] = $lang['open-shift-not-using-open'];
		pageStart($lang['start-shift'], NULL, $confirmLeave, "pcloseday", "", $lang['start-shift'] . " - ERROR", $_SESSION['successMessage'], $_SESSION['errorMessage']);
		exit();

	}

	// Check if any day has been opened and not closed. If so throw error.

	// Check if last opened day/shift has been closed. Does not work for shifts which have been started but not finished.
	// Compare also with Closing...? To see shiftopenedno?
	$openingLookup = "SELECT openingtime, tillBalance, shiftClosed AS closed, openedby, 'opening' AS type FROM opening UNION ALL SELECT openingtime, tillBalance, closed, openedby, 'shiftopen' AS type FROM shiftopen ORDER BY openingtime DESC LIMIT 1";
	
	$result = mysql_query($openingLookup)
		or handleError($lang['error-noopeningdetails'],"Error loading opening from db: " . mysql_error());
	
	// This must done if there DOES exist openings, but no day has been opened today (e.g. last day opened was closed? Test)
	if (mysql_num_rows($result) == 0) {
		
		$_SESSION['errorMessage'] = $lang['open-shift-no-day-opened'];
		pageStart($lang['start-shift'], NULL, $confirmLeave, "pcloseday", "", $lang['start-shift'] . " - ERROR", $_SESSION['successMessage'], $_SESSION['errorMessage']);
		exit();
		
	}

	$row = mysql_fetch_array($result);
		$openingtime = $row['openingtime'];
		$tillBalance = $row['tillBalance'];
		$closed = $row['closed'];
		$openedby = $row['openedby'];
		$type = $row['type'];

	$_SESSION['openingtime'] = $openingtime;
	$_SESSION['tillBalance'] = $tillBalance;
	
	$openingtimeView = date('d-m-Y H:i', strtotime($openingtime));
	$openingBy = getOperator($openedby);

	// Check last closing, to see if it has been closed, to evaluate whether an opening has been made or is in progress
	$closingLookup = "SELECT shiftOpened, shiftOpenedNo FROM shiftclose ORDER BY closingtime DESC LIMIT 1";
	
	$result = mysql_query($closingLookup)
		or handleError($lang['error-closingload'],"Error loading closing from db: " . mysql_error());
		
	$row = mysql_fetch_array($result);
		$shiftOpenedNo = $row['shiftOpenedNo'];
		$shiftOpened = $row['shiftOpened'];
		
	// If a day is opening in progress, take directly to open-day.php
	if ($shiftOpened < 2) {
		
		header("Location: open-shift.php");
		exit();
				
	}
			
			
	if ($closed < 2) {
		
		// A shift/day is still open! Gotta close it first.
		// Day first
		
		if ($type == 'opening') {
			
			pageStart($lang['start-shift'], NULL, $confirmLeave, "pcloseday", "", $lang['start-shift'] . " - ERROR", $_SESSION['successMessage'], $_SESSION['errorMessage']);
	
			echo  <<<EOD
<div id="scriptMsg">
 <div class='error'>{$lang['open-day-shift-day-not-closed']}</div>
</div>
<br /><br />
<div class="textInset">
 <center><strong>{$lang['global-details']}</strong></center><br />
 <table>
  <tr>
   <td style='text-align: left;'>{$lang['day-opened']}:</td>
   <td style='text-align: left;'>{$openingtimeView}</td>
  </tr>
  <tr>
   <td style='text-align: left;'>{$lang['responsible']}:</td>
   <td style='text-align: left;'>$openingBy</td>
  </tr>
 </table>
</div>
EOD;
				
		} else {
			
			pageStart($lang['start-shift'], NULL, $confirmLeave, "pcloseday", "", $lang['start-shift'] . " - ERROR", $_SESSION['successMessage'], $_SESSION['errorMessage']);
	
			echo  <<<EOD
<div id="scriptMsg">
 <div class='error'>{$lang['open-day-shift-not-closed']}</div>
</div>
<br /><br />
<div class="textInset">
 <center><strong>{$lang['global-details']}</strong></center><br />
 <table>
  <tr>
   <td style='text-align: left;'>{$lang['shift-opened']}:</td>
   <td style='text-align: left;'>{$openingtimeView}</td>
  </tr>
  <tr>
   <td style='text-align: left;'>{$lang['responsible']}:</td>
   <td style='text-align: left;'>$openingBy</td>
  </tr>
 </table>
</div>
EOD;
				
		}
		
		exit();
		
	} else if ($closed == 2 && $type == 'shiftopen') {
		
		$_SESSION['errorMessage'] = $lang['day-not-opened'];
		pageStart($lang['start-shift'], NULL, $confirmLeave, "pcloseday", "", $lang['start-shift'] . " - ERROR", $_SESSION['successMessage'], $_SESSION['errorMessage']);
		exit();
		
		
	}
	// if last one was shift and closed 2, we can assume tha tthe day hasn't been opeend. throw error
	
	header("Location: open-shift.php");