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
	
	// User skips the count
	if (isset($_GET['skipCount'])) {
		
		$closingLookup = "SELECT closingid, oneCent, twoCent, fiveCent, tenCent, twentyCent, fiftyCent, oneEuro, twoEuro, fiveEuro, tenEuro, twentyEuro, fiftyEuro, hundredEuro, coinsTot, notesTot, cashintill, bankBalance FROM closing ORDER BY closingtime DESC LIMIT 1";
		
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
			$closingid = $row['closingid'];
			$oneCent = $row['oneCent'];
			$twoCent = $row['twoCent'];
			$fiveCent = $row['fiveCent'];
			$tenCent = $row['tenCent'];
			$twentyCent = $row['twentyCent'];
			$fiftyCent = $row['fiftyCent'];
			$oneEuro = $row['oneEuro'];
			$twoEuro = $row['twoEuro'];
			$fiveEuro = $row['fiveEuro'];
			$tenEuro = $row['tenEuro'];
			$twentyEuro = $row['twentyEuro'];
			$fiftyEuro = $row['fiftyEuro'];
			$hundredEuro = $row['hundredEuro'];
			$coinsTot = $row['coinsTot'];
			$notesTot = $row['notesTot'];
			$tillBalance = $row['cashintill'];
			$bankBalance = $row['bankBalance'];
			
			$openCount = array("oneCent"=>"$oneCent", "twoCent"=>"$twoCent", "fiveCent"=>"$fiveCent", "tenCent"=>"$tenCent", "twentyCent"=>"$twentyCent", "fiftyCent"=>"$fiftyCent", "oneEuro"=>"$oneEuro", "twoEuro"=>"$twoEuro", "fiveEuro"=>"$fiveEuro", "tenEuro"=>"$tenEuro", "twentyEuro"=>"$twentyEuro", "fiftyEuro"=>"$fiftyEuro", "hundredEuro"=>"$hundredEuro", "coinsTot"=>"$coinsTot", "notesTot"=>"$notesTot", "tillTot"=>"$tillBalance", "tillDelta"=>"$tillDelta", "tillComment"=>"$tillComment");
						
			$tillDelta = 0;
			
	} else {

		$openCount = $_SESSION['openCount'];
		$bankBalance = $_SESSION['bankBalance'];
		$tillDelta =  $_SESSION['tillDelta'];
		$openCount = $_SESSION['openCount'];
		
	}
		
	// Is this the first ever opening? Check if an opening already exists (perhaps the dispensary was opened), and UPDATE that one. If it doens't exist, create a new one!
	if ($_SESSION['firstOpening'] == 'true') {
		
		$selectUsers = "SELECT COUNT(openingid) FROM opening ORDER BY openingtime DESC LIMIT 1";
		$rowCount = $pdo3->query("$selectUsers")->fetchColumn();
		
		// Grab opening ID
		$openingLookup = "SELECT openingid FROM opening ORDER BY openingtime DESC LIMIT 1";
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

			// No opening exists. Let's create it.
			$query = sprintf("INSERT INTO opening (openingtime, oneCent, twoCent, fiveCent, tenCent, twentyCent, fiftyCent, oneEuro, twoEuro, fiveEuro, tenEuro, twentyEuro, fiftyEuro, hundredEuro, coinsTot, notesTot, tillBalance, bankBalance, moneyOwed, owedPlusTill, tillDelta, tillComment) VALUES ('%s', '%d', '%d', '%d', '%d', '%d', '%d', '%d', '%d', '%d', '%d', '%d', '%d', '%d', '%f', '%f', '%f', '%f', '%f', '%f', '%f', '%s');",
			  $openingtime, $openCount['oneCent'], $openCount['twoCent'], $openCount['fiveCent'], $openCount['tenCent'], $openCount['twentyCent'], $openCount['fiftyCent'], $openCount['oneEuro'], $openCount['twoEuro'], $openCount['fiveEuro'], $openCount['tenEuro'], $openCount['twentyEuro'], $openCount['fiftyEuro'], $openCount['hundredEuro'], $openCount['coinsTot'], $openCount['notesTot'], $openCount['tillTot'], $bankBalance, $amountOwed, $owedPlusTill, $tillDelta, $openCount['tillComment']);
				try
				{
					$result = $pdo3->prepare("$query")->execute();
				}
				catch (PDOException $e)
				{
						$error = 'Error fetching user: ' . $e->getMessage();
						echo $error;
						exit();
				}
						
				$openingid = $pdo3->lastInsertId();

		} else {
			
			// Opening exists. Let's update it.
			$row = $result->fetch();
				$openingid = $row['openingid'];
			
			$query = sprintf("UPDATE opening SET openingtime = '%s', oneCent = '%d', twoCent = '%d', fiveCent = '%d', tenCent = '%d', twentyCent = '%d', fiftyCent = '%d', oneEuro = '%d', twoEuro = '%d', fiveEuro = '%d', tenEuro = '%d', twentyEuro = '%d', fiftyEuro = '%d', hundredEuro = '%d', coinsTot = '%f', notesTot = '%f', tillBalance = '%f', bankBalance = '%f', moneyOwed = '%f', owedPlusTill = '%f', tillDelta = '%f', tillComment = '%s' WHERE openingid = '%d';",
			  $openingtime, $openCount['oneCent'], $openCount['twoCent'], $openCount['fiveCent'], $openCount['tenCent'], $openCount['twentyCent'], $openCount['fiftyCent'], $openCount['oneEuro'], $openCount['twoEuro'], $openCount['fiveEuro'], $openCount['tenEuro'], $openCount['twentyEuro'], $openCount['fiftyEuro'], $openCount['hundredEuro'], $openCount['coinsTot'], $openCount['notesTot'], $openCount['tillTot'], $bankBalance, $amountOwed, $owedPlusTill, $tillDelta, $openCount['tillComment'], $openingid);
		try
		{
			$result = $pdo3->prepare("$query")->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
			
		}
			
		$timeNow = date('Y-m-d H:i:s');
		
		if ($_SESSION['noCompare'] != 'true') {
			
			$updateClosing = sprintf("UPDATE closing SET recOpened = 2, recOpenedAt = '%s' WHERE closingid = '%d';",
				$timeNow, $closingid
				);
				
		} else {
			
			$updateClosing = sprintf("UPDATE opening SET firstRecOpen = 2, firstRecOpenAt = '%s' WHERE openingid = '%d';",
				$timeNow, $openingid
				);
				
		}
		try
		{
			$result = $pdo3->prepare("$updateClosing")->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
			
		// On success: redirect.
		$_SESSION['successMessage'] = $lang['reception-opened-successfully'];
		header("Location: open-day.php");
		exit();
		
	}

	// Check if the last closing has been opened, to know whether to INSERT or UPDATE
	$openingLookup = "SELECT dayOpenedNo FROM closing WHERE closingid = '$closingid'";
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
		$dayOpenedNo = $row['dayOpenedNo'];
		
	if ($dayOpenedNo > 0) {
		
		// Means part of the day has been opened already, so use UPDATE
		$query = sprintf("UPDATE opening SET openingtime = '%s', oneCent = '%d', twoCent = '%d', fiveCent = '%d', tenCent = '%d', twentyCent = '%d', fiftyCent = '%d', oneEuro = '%d', twoEuro = '%d', fiveEuro = '%d', tenEuro = '%d', twentyEuro = '%d', fiftyEuro = '%d', hundredEuro = '%d', coinsTot = '%f', notesTot = '%f', tillBalance = '%f', bankBalance = '%f', moneyOwed = '%f', owedPlusTill = '%f', tillDelta = '%f', tillComment = '%s' WHERE openingid = '%d';",
		  $openingtime, $openCount['oneCent'], $openCount['twoCent'], $openCount['fiveCent'], $openCount['tenCent'], $openCount['twentyCent'], $openCount['fiftyCent'], $openCount['oneEuro'], $openCount['twoEuro'], $openCount['fiveEuro'], $openCount['tenEuro'], $openCount['twentyEuro'], $openCount['fiftyEuro'], $openCount['hundredEuro'], $openCount['coinsTot'], $openCount['notesTot'], $openCount['tillTot'], $bankBalance, $amountOwed, $owedPlusTill, $tillDelta, $openCount['tillComment'], $dayOpenedNo);
		try
		{
			$result = $pdo3->prepare("$query")->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
			
		// What if there is no last closing? Can not update Closing.
		if ($_SESSION['noCompare'] != 'true') {
			
			$timeNow = date('Y-m-d H:i:s');
			$updateClosing = sprintf("UPDATE closing SET recOpened = 2, recOpenedAt = '%s' WHERE closingid = '%d';",
			$timeNow, $closingid
			);
		try
		{
			$result = $pdo3->prepare("$updateClosing")->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
			
		}

		
	} else {
		
		// Means no opening (rec. or dis.) has been done, so use INSERT
		$query = sprintf("INSERT INTO opening (openingtime, oneCent, twoCent, fiveCent, tenCent, twentyCent, fiftyCent, oneEuro, twoEuro, fiveEuro, tenEuro, twentyEuro, fiftyEuro, hundredEuro, coinsTot, notesTot, tillBalance, bankBalance, moneyOwed, owedPlusTill, tillDelta, tillComment) VALUES ('%s', '%d', '%d', '%d', '%d', '%d', '%d', '%d', '%d', '%d', '%d', '%d', '%d', '%d', '%f', '%f', '%f', '%f', '%f', '%f', '%f', '%s');",
		  $openingtime, $openCount['oneCent'], $openCount['twoCent'], $openCount['fiveCent'], $openCount['tenCent'], $openCount['twentyCent'], $openCount['fiftyCent'], $openCount['oneEuro'], $openCount['twoEuro'], $openCount['fiveEuro'], $openCount['tenEuro'], $openCount['twentyEuro'], $openCount['fiftyEuro'], $openCount['hundredEuro'], $openCount['coinsTot'], $openCount['notesTot'], $openCount['tillTot'], $bankBalance, $amountOwed, $owedPlusTill, $tillDelta, $openCount['tillComment']);
		try
		{
			$result = $pdo3->prepare("$query")->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
			
		$openingid = $pdo3->lastInsertId();
		
		if ($_SESSION['noCompare'] != 'true') {
			
			$timeNow = date('Y-m-d H:i:s');
			$updateClosing = sprintf("UPDATE closing SET recOpened = 2, recOpenedAt = '%s', dayOpenedNo = '%d' WHERE closingid = '%d';",
			$timeNow, $openingid,
			$closingid
			);
		try
		{
			$result = $pdo3->prepare("$updateClosing")->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
				
		}
		
	}
	
		// On success: redirect.
		$_SESSION['successMessage'] = $lang['reception-opened-successfully'];
		header("Location: open-day.php");
		exit();

		## ON PAGE SUBMISSION END ##
	
		
displayFooter();