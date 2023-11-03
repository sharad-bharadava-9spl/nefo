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
	
	/***************************** ERROR MANAGEMENT BEGIN *****************************/
	
	// Accountability: Anything else than Shifts, throw error:
	if ($_SESSION['openAndClose'] < 4) {
		
		$_SESSION['errorMessage'] = $lang['open-shift-not-using-open'];
		pageStart($lang['start-shift'], NULL, $confirmLeave, "pcloseday", "", $lang['start-shift'] . " - ERROR", $_SESSION['successMessage'], $_SESSION['errorMessage']);
		exit();

	}
	
	/*
		If you want to open a shift, we need to make sure that:
		1. Is the last opening/shiftopen a Day? Throw error - day and shift already opened.
		2. What if soemone mistakenly clicks OPEN SHIFT instead of OPEN DAY?
		3. Is there an open shift (check both DAY and SHIFT), which is not closed?
	
	*/
	
	// x. Check if last entry is a close day, if so throw error.
	$openingLookup = "SELECT openingtime, 'opening' AS type FROM opening UNION ALL SELECT closingtime AS openingtime, 'closing' AS type FROM closing ORDER BY openingtime DESC LIMIT 1";
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
		$type = $row['type'];
		
	if ($type == 'closing') {
		
		$_SESSION['errorMessage'] = $lang['day-not-open-cant-open-shift'];
		pageStart($lang['start-shift'], NULL, $confirmLeave, "pcloseday", "", $lang['start-shift'] . " - ERROR", $_SESSION['successMessage'], $_SESSION['errorMessage']);
		exit();
		
	}
	
	// 1.
	
	$openingLookup = "SELECT openingtime, 'opening' AS type FROM opening UNION ALL SELECT closingtime AS openingtime, 'shiftclose' AS type FROM shiftclose ORDER BY openingtime DESC LIMIT 1";
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
		$type = $row['type'];

	if ($type == 'opening') {
		
		$_SESSION['errorMessage'] = $lang['dayshift-already-open'];
		pageStart($lang['start-shift'], NULL, $confirmLeave, "pcloseday", "", $lang['start-shift'] . " - ERROR", $_SESSION['successMessage'], $_SESSION['errorMessage']);
		exit();
		
	}
	
	// 2.
	// Check if last entry was a closing of day. If that's the case, throw error.
	$openingLookup = "SELECT openingtime, 'opening' AS type FROM opening UNION ALL SELECT closingtime AS openingtime, 'shiftclose' AS type FROM shiftclose UNION ALL SELECT closingtime AS openingtime, 'closing' AS type FROM closing ORDER BY openingtime DESC LIMIT 1";
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
		$type = $row['type'];
		
	$openingLookup = "SELECT closingtime FROM shiftclose ORDER BY closingtime DESC LIMIT 1";
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
		$closingtime = $row['closingtime'];
		
	$openingLookup = "SELECT closingtime FROM closing ORDER BY closingtime DESC LIMIT 1";
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
		$closingtime2 = $row['closingtime'];
		
	if ($type == 'closing' || ($type == 'shiftclose' && $closingtime2 == $closingtime)) {
		
		$_SESSION['errorMessage'] = $lang['day-not-open-cant-open-shift'];
		pageStart($lang['start-shift'], NULL, $confirmLeave, "pcloseday", "", $lang['start-shift'] . " - ERROR", $_SESSION['successMessage'], $_SESSION['errorMessage']);
		exit();
		
	}
	
	
	
	/***************************** ERROR MANAGEMENT END *****************************/
	
	
	
	// !!! What if someone just opens the day, closes the day (no shifts)...? TEST!

	// OLD Check if any day has been opened and not closed. If so throw error.
	// OLD Check if last opened day/shift has been closed. Does not work for shifts which have been started but not finished.
	// OLD Compare also with Closing...? To see shiftopenedno?
	
	$openingLookup = "SELECT openingtime, tillBalance, shiftClosed AS closed, openedby, 'opening' AS type FROM opening UNION ALL SELECT openingtime, tillBalance, closed, openedby, 'shiftopen' AS type FROM shiftopen ORDER BY openingtime DESC LIMIT 1";
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
			

	// This must done if there DOES exist openings, but no day has been opened today (e.g. last day opened was closed? Test)
		if (!$data) {
		
		$_SESSION['errorMessage'] = $lang['open-shift-no-day-opened'];
		pageStart($lang['start-shift'], NULL, $confirmLeave, "pcloseday", "", $lang['start-shift'] . " - ERROR", $_SESSION['successMessage'], $_SESSION['errorMessage']);
		exit();
		
	}

	$row = $data[0];
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
		try
		{
			$result = $pdo3->prepare("$closingLookup");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
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
<center>
<div id="scriptMsg">
 <div class='error'>{$lang['open-day-shift-day-not-closed']}</div>
</div>
</center>
<br /><br />
<div class="textInset">
 <center><h3 class="title">{$lang['global-details']}</h3></center><br />
 <center><div class="historybox">
 <table class="historytable">
  <tr>
   <td style='text-align: left;'>{$lang['day-opened']}:</td>
   <td style='text-align: left;'>{$openingtimeView}</td>
  </tr>
  <tr>
   <td style='text-align: left;'>{$lang['responsible']}:</td>
   <td style='text-align: left;'>$openingBy</td>
  </tr>
 </table>
 </div></center>
</div>

EOD;
				
		} else {
			
			pageStart($lang['start-shift'], NULL, $confirmLeave, "pcloseday", "", $lang['start-shift'] . " - ERROR", $_SESSION['successMessage'], $_SESSION['errorMessage']);
	
			echo  <<<EOD
<center>
<div id="scriptMsg">
 <div class='error'>{$lang['open-day-shift-not-closed']}</div>
</div>
 </center>
<br /><br />
<div class="textInset">
 <center><h3 class="title">{$lang['global-details']}</h3></center><br />
  <center><div class="historybox">
 <table class="historytable">
  <tr>
   <td style='text-align: left;'>{$lang['shift-opened']}:</td>
   <td style='text-align: left;'>{$openingtimeView}</td>
  </tr>
  <tr>
   <td style='text-align: left;'>{$lang['responsible']}:</td>
   <td style='text-align: left;'>$openingBy</td>
  </tr>
 </table>
 </div></center>
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
