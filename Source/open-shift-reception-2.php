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
		
		$closingLookup = "SELECT closingid, oneCent, twoCent, fiveCent, tenCent, twentyCent, fiftyCent, oneEuro, twoEuro, fiveEuro, tenEuro, twentyEuro, fiftyEuro, hundredEuro, coinsTot, notesTot, cashintill, bankBalance FROM shiftclose ORDER BY closingtime DESC LIMIT 1";
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
	
	// Check if the last closing has been opened, to know whether to INSERT or UPDATE
	$openingLookup = "SELECT shiftOpenedNo FROM shiftclose WHERE closingid = '$closingid'";
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
		$dayOpenedNo = $row['shiftOpenedNo'];
		
	if ($dayOpenedNo > 0) {
		
		// Means part of the day has been opened already, so use UPDATE
		$query = sprintf("UPDATE shiftopen SET openingtime = '%s', oneCent = '%d', twoCent = '%d', fiveCent = '%d', tenCent = '%d', twentyCent = '%d', fiftyCent = '%d', oneEuro = '%d', twoEuro = '%d', fiveEuro = '%d', tenEuro = '%d', twentyEuro = '%d', fiftyEuro = '%d', hundredEuro = '%d', coinsTot = '%f', notesTot = '%f', tillBalance = '%f', bankBalance = '%f', moneyOwed = '%f', owedPlusTill = '%f', tillDelta = '%f', tillComment = '%s' WHERE openingid = '%d';",
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
			
		$timeNow = date('Y-m-d H:i:s');
		
		$updateClosing = sprintf("UPDATE shiftclose SET recOpened = 2, recOpenedAt = '%s' WHERE closingid = '%d';",
		$timeNow,
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

		
	} else {
		
		// Means no opening (rec. or dis.) has been done, so use INSERT
		$query = sprintf("INSERT INTO shiftopen (openingtime, oneCent, twoCent, fiveCent, tenCent, twentyCent, fiftyCent, oneEuro, twoEuro, fiveEuro, tenEuro, twentyEuro, fiftyEuro, hundredEuro, coinsTot, notesTot, tillBalance, bankBalance, moneyOwed, owedPlusTill, tillDelta, tillComment) VALUES ('%s', '%d', '%d', '%d', '%d', '%d', '%d', '%d', '%d', '%d', '%d', '%d', '%d', '%d', '%f', '%f', '%f', '%f', '%f', '%f', '%f', '%s');",
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
				
			$timeNow = date('Y-m-d H:i:s');
			
			$updateClosing = sprintf("UPDATE shiftclose SET recOpened = 2, recOpenedAt = '%s', shiftOpenedNo = '%d' WHERE closingid = '%d';",
			$timeNow,
			$openingid,
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
	
		// On success: redirect.
		$_SESSION['successMessage'] = $lang['reception-opened-successfully'];
		header("Location: open-shift.php");
		exit();

		## ON PAGE SUBMISSION END ##
	
		
displayFooter();