<?php

	require_once '../cOnfig/connection.php';
	require_once '../cOnfig/view.php';
	require_once '../cOnfig/authenticate.php';
	require_once '../cOnfig/languages/common.php';
		
	session_start();
	
	$accessLevel = '3';

	// Authenticate & authorize
	authorizeUser($accessLevel);

	
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
		
	$openingtime = date('Y-m-d H:i:s');
		

	// Look up how much money is owed
	$selectDebt = "SELECT SUM(amount), SUM(amountpaid) FROM sales WHERE amount <> amountpaid";
	try
	{
		$result = $pdo3->prepare("$selectDebt");
		$result->execute();
	}
	catch (PDOException $e)
	{
			$error = 'Error fetching user: ' . $e->getMessage();
			echo $error;
			exit();
	}

	$row = $result->fetch();
		$amount = $row['SUM(amount)'];
		$amountpaid = $row['SUM(amountpaid)'];
		
	$amountOwed = $amount - $amountpaid;
	$owedPlusTill = $tillTot + $amountOwed;
	
	
	// Lookup closingdetails for products
	$closingDetailsLookup = "SELECT category, productid, purchaseid, weight, categoryType FROM shiftclosedetails WHERE closingid = $closingid";
	
	try
	{
		$resultDetails = $pdo3->prepare("$closingDetailsLookup");
		$resultDetails->execute();
		$resultDetails2 = $pdo3->prepare("$closingDetailsLookup");
		$resultDetails2->execute();
	}
	catch (PDOException $e)
	{
			$error = 'Error fetching user: ' . $e->getMessage();
			echo $error;
			exit();
	}
	
	while ($row = $resultDetails->fetch()) {

		$category = $row['category'];
		$categoryType = $row['categoryType'];
		$weight = $row['weight'];
		$weightDelta = $row['weightDelta'];
		
		if ($category == '1') {
			$prodStockFlower = $prodStockFlower + $weight;
			$stockDeltaFlower = $stockDeltaFlower + $weightDelta;
		} else if ($category == '2') {
			$prodStockExtract = $prodStockExtract + $weight;
			$stockDeltaExtract = $stockDeltaExtract + $weightDelta;
		} else if ($categoryType == 0) {
			$otherTotal = $otherTotal + $weight;
			$otherDelta = $otherDelta + $weightDelta;
		}

	}

	$prodStock = $prodStockFlower + $prodStockExtract + $otherTotal;
	$stockDelta = $stockDeltaFlower + $stockDeltaExtract + $otherDelta;

			
	// Query to add Opening - 20 arguments
	$query = sprintf("INSERT INTO shiftopen (openingtime, oneCent, twoCent, fiveCent, tenCent, twentyCent, fiftyCent, oneEuro, twoEuro, fiveEuro, tenEuro, twentyEuro, fiftyEuro, hundredEuro, coinsTot, notesTot, tillBalance, bankBalance, moneyOwed, owedPlusTill, tillDelta, tillComment, stockDelta, openedby, prodStock, prodStockFlower, prodStockExtract, stockDeltaFlower, stockDeltaExtract) VALUES ('%s', '%d', '%d', '%d', '%d', '%d', '%d', '%d', '%d', '%d', '%d', '%d', '%d', '%d', '%f', '%f', '%f', '%f', '%f', '%f', '%f', '%s', '%f', '%d', '%f', '%f', '%f', '%f', '%f');",
	$openingtime, $oneCent, $twoCent, $fiveCent, $tenCent, $twentyCent, $fiftyCent, $oneEuro, $twoEuro, $fiveEuro, $tenEuro, $twentyEuro, $fiftyEuro, $hundredEuro, $coinsTot, $notesTot, $tillBalance, $bankBalance, $amountOwed, $owedPlusTill, '0', 'Automatic opening', '0', $_SESSION['user_id'], $prodStock, $prodStockFlower, $prodStockExtract, $stockDeltaFlower, $stockDeltaExtract);
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
	
	// Insert into openingother
	$otherLookup = "SELECT category, categoryType, prodStock FROM shiftcloseother WHERE closingid = '$closingid'";
	try
	{
		$result = $pdo3->prepare("$otherLookup");
		$result->execute();
	}
	catch (PDOException $e)
	{
			$error = 'Error fetching user: ' . $e->getMessage();
			echo $error;
			exit();
	}
	
	while ($user = $result->fetch()) {
		
		$category = $user['category'];
		$categoryType = $user['categoryType'];
		$prodStock = $user['prodStock'];
		
		$query = sprintf("INSERT INTO shiftopenother (openingid, category, categoryType, prodStock, stockDelta) VALUES ('%d', '%d', '%d', '%f', '%f');",
		  $openingid, $category, $categoryType, $prodStock, 0);
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

	// Openingdetails:
	while ($row = $resultDetails2->fetch()) {
		
		$category = $row['category'];
		$productid = $row['productid'];
		$purchaseid = $row['purchaseid'];
		$weight = $row['weight'];		
			
	  	$query = sprintf("INSERT INTO shiftopendetails (openingid, category, productid, purchaseid, weight, prodOpenComment) VALUES ('%d', '%d', '%d', '%d', '%f', '%s');",
	  	$openingid, $category, $productid, $purchaseid, $weight, 'Automatic opening');
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
	
	// Update flags
	$flagUpdate = "UPDATE shiftclose SET shiftOpened = 2, shiftOpenedBy = '{$_SESSION['user_id']}', disOpened = 2, disOpenedBy = '{$_SESSION['user_id']}', recOpened = 2, recOpenedBy = '{$_SESSION['user_id']}', dis2Opened = 2, dis2OpenedBy = '{$_SESSION['user_id']}', barOpened = 2, barOpenedBy = '{$_SESSION['user_id']}'  WHERE closingid = $closingid";
	try
	{
		$result = $pdo3->prepare("$flagUpdate")->execute();
	}
	catch (PDOException $e)
	{
			$error = 'Error fetching user: ' . $e->getMessage();
			echo $error;
			exit();
	}
			
	// On success: redirect.
	$_SESSION['successMessage'] = $lang['shiftopened'];
	header("Location: ../admin.php");