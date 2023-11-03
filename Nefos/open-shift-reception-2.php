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
	
	$closingid = $_SESSION['closingid'];
	$openingtime = date('Y-m-d H:i:s');
	$openCount = $_SESSION['openCount'];
	$bankBalance = $_SESSION['bankBalance'];
	$tillDelta =  $_SESSION['tillDelta'];
	$openCount = $_SESSION['openCount'];
	
	// Check if the last closing has been opened, to know whether to INSERT or UPDATE
	$openingLookup = "SELECT shiftOpenedNo FROM shiftclose WHERE closingid = '$closingid'";
	
	$result = mysql_query($openingLookup)
		or handleError($lang['error-noopeningdetails'],"Error loading opening from db: " . mysql_error());

	$row = mysql_fetch_array($result);
		$dayOpenedNo = $row['shiftOpenedNo'];
		
	if ($dayOpenedNo > 0) {
		
		// Means part of the day has been opened already, so use UPDATE
		$query = sprintf("UPDATE shiftopen SET openingtime = '%s', oneCent = '%d', twoCent = '%d', fiveCent = '%d', tenCent = '%d', twentyCent = '%d', fiftyCent = '%d', oneEuro = '%d', twoEuro = '%d', fiveEuro = '%d', tenEuro = '%d', twentyEuro = '%d', fiftyEuro = '%d', hundredEuro = '%d', coinsTot = '%f', notesTot = '%f', tillBalance = '%f', bankBalance = '%f', moneyOwed = '%f', owedPlusTill = '%f', tillDelta = '%f', tillComment = '%s' WHERE openingid = '%d';",
		  $openingtime, $openCount['oneCent'], $openCount['twoCent'], $openCount['fiveCent'], $openCount['tenCent'], $openCount['twentyCent'], $openCount['fiftyCent'], $openCount['oneEuro'], $openCount['twoEuro'], $openCount['fiveEuro'], $openCount['tenEuro'], $openCount['twentyEuro'], $openCount['fiftyEuro'], $openCount['hundredEuro'], $openCount['coinsTot'], $openCount['notesTot'], $openCount['tillTot'], $bankBalance, $amountOwed, $owedPlusTill, $tillDelta, $openCount['tillComment'], $dayOpenedNo);

		mysql_query($query)
			or handleError($lang['error-savedata'],"Error saving opening: " . mysql_error());
			
		$timeNow = date('Y-m-d H:i:s');
		
		$updateClosing = sprintf("UPDATE shiftclose SET recOpened = 2, recOpenedAt = '%s' WHERE closingid = '%d';",
		$timeNow,
		mysql_real_escape_string($closingid)
		);
		
		mysql_query($updateClosing)
			or handleError($lang['error-savedata'],"Error updating expense: " . mysql_error());

		
	} else {
		
		// Means no opening (rec. or dis.) has been done, so use INSERT
		$query = sprintf("INSERT INTO shiftopen (openingtime, oneCent, twoCent, fiveCent, tenCent, twentyCent, fiftyCent, oneEuro, twoEuro, fiveEuro, tenEuro, twentyEuro, fiftyEuro, hundredEuro, coinsTot, notesTot, tillBalance, bankBalance, moneyOwed, owedPlusTill, tillDelta, tillComment) VALUES ('%s', '%d', '%d', '%d', '%d', '%d', '%d', '%d', '%d', '%d', '%d', '%d', '%d', '%d', '%f', '%f', '%f', '%f', '%f', '%f', '%f', '%s');",
		  $openingtime, $openCount['oneCent'], $openCount['twoCent'], $openCount['fiveCent'], $openCount['tenCent'], $openCount['twentyCent'], $openCount['fiftyCent'], $openCount['oneEuro'], $openCount['twoEuro'], $openCount['fiveEuro'], $openCount['tenEuro'], $openCount['twentyEuro'], $openCount['fiftyEuro'], $openCount['hundredEuro'], $openCount['coinsTot'], $openCount['notesTot'], $openCount['tillTot'], $bankBalance, $amountOwed, $owedPlusTill, $tillDelta, $openCount['tillComment']);

		mysql_query($query)
			or handleError($lang['error-savedata'],"Error saving opening: " . mysql_error());
			
		$openingid = mysql_insert_id();
				
			$timeNow = date('Y-m-d H:i:s');
			
			$updateClosing = sprintf("UPDATE shiftclose SET recOpened = 2, recOpenedAt = '%s', shiftOpenedNo = '%d' WHERE closingid = '%d';",
			$timeNow,
			mysql_real_escape_string($openingid),
			mysql_real_escape_string($closingid)
			);

			mysql_query($updateClosing)
				or handleError($lang['error-savedata'],"Error updating expense: " . mysql_error());
		
	}
	
		// On success: redirect.
		$_SESSION['successMessage'] = $lang['reception-opened-successfully'];
		header("Location: open-shift.php");
		exit();

		## ON PAGE SUBMISSION END ##
	
		
displayFooter();