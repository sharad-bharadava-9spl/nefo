<?php

	require_once '../cOnfig/connection.php';
	require_once '../cOnfig/view.php';
	require_once '../cOnfig/authenticate.php';
	require_once '../cOnfig/languages/common.php';
		
	session_start();
	
	$accessLevel = '3';

	// Authenticate & authorize
	authorizeUser($accessLevel);

		
			$closingLookup = "SELECT closingid, oneCent, twoCent, fiveCent, tenCent, twentyCent, fiftyCent, oneEuro, twoEuro, fiveEuro, tenEuro, twentyEuro, fiftyEuro, hundredEuro, coinsTot, notesTot, cashintill, bankBalance FROM closing ORDER BY closingtime DESC LIMIT 1";
		
		$result = mysql_query($closingLookup)
			or handleError($lang['error-closingload'],"Error loading closing from db: " . mysql_error());

		// Retrieve yesterdays closing data
		$row = mysql_fetch_array($result);
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

		$result = mysql_query($selectDebt)
		or handleError($lang['error-loadsales'],"Error loading sale from db: " . mysql_error());
		
		$row = mysql_fetch_array($result);
			$amount = $row['SUM(amount)'];
			$amountpaid = $row['SUM(amountpaid)'];
			
		$amountOwed = $amount - $amountpaid;
		$owedPlusTill = $tillTot + $amountOwed;
		
		
		// Lookup closingdetails for products
		$closingDetailsLookup = "SELECT category, productid, purchaseid, weight FROM closingdetails WHERE closingid = $closingid";
		
		$resultDetails = mysql_query($closingDetailsLookup)
			or handleError($lang['error-closingload'],"Error loading closing details from db: " . mysql_error());
		$resultDetails2 = mysql_query($closingDetailsLookup)
			or handleError($lang['error-closingload'],"Error loading closing details from db: " . mysql_error());
	
		
		while ($row = mysql_fetch_array($resultDetails)) {

			$category = $row['category'];
			$weight = $row['weight'];
			$weightDelta = $row['weightDelta'];
			
			if ($category == '1') {
				$prodStockFlower = $prodStockFlower + $weight;
				$stockDeltaFlower = $stockDeltaFlower + $weightDelta;
			} else if ($category == '2') {
				$prodStockExtract = $prodStockExtract + $weight;
				$stockDeltaExtract = $stockDeltaExtract + $weightDelta;
			}

}

		$prodStock = $prodStockFlower + $prodStockExtract;
		$stockDelta = $stockDeltaFlower + $stockDeltaExtract;

		
		
		
		// Query to add Opening - 20 arguments
		  $query = sprintf("INSERT INTO opening (openingtime, oneCent, twoCent, fiveCent, tenCent, twentyCent, fiftyCent, oneEuro, twoEuro, fiveEuro, tenEuro, twentyEuro, fiftyEuro, hundredEuro, coinsTot, notesTot, tillBalance, bankBalance, moneyOwed, owedPlusTill, tillDelta, tillComment, stockDelta, openedby, prodStock, prodStockFlower, prodStockExtract, stockDeltaFlower, stockDeltaExtract) VALUES ('%s', '%d', '%d', '%d', '%d', '%d', '%d', '%d', '%d', '%d', '%d', '%d', '%d', '%d', '%f', '%f', '%f', '%f', '%f', '%f', '%f', '%s', '%f', '%d', '%f', '%f', '%f', '%f', '%f');",
		  $openingtime, $oneCent, $twoCent, $fiveCent, $tenCent, $twentyCent, $fiftyCent, $oneEuro, $twoEuro, $fiveEuro, $tenEuro, $twentyEuro, $fiftyEuro, $hundredEuro, $coinsTot, $notesTot, $tillBalance, $bankBalance, $amountOwed, $owedPlusTill, '0', 'Automatic opening', '0', $_SESSION['user_id'], $prodStock, $prodStockFlower, $prodStockExtract, $stockDeltaFlower, $stockDeltaExtract);
		  
			mysql_query($query)
				or handleError($lang['error-savedata'],"Error saving opening: " . mysql_error());
			
		$openingid = mysql_insert_id();
		

		while ($row = mysql_fetch_array($resultDetails2)) {
			$category = $row['category'];
			$productid = $row['productid'];
			$purchaseid = $row['purchaseid'];
			$weight = $row['weight'];		
				
		  	$query = sprintf("INSERT INTO openingdetails (openingid, category, productid, purchaseid, weight, prodOpenComment) VALUES ('%d', '%d', '%d', '%d', '%f', '%s');",
		  	$openingid, $category, $productid, $purchaseid, $weight, 'Automatic opening');
		  
			mysql_query($query)
				or handleError($lang['error-savedata'],"Error saving opening: " . mysql_error());

			}		
				
		// On success: redirect.
		$_SESSION['successMessage'] = $lang['dayopened'];
		header("Location: ../admin.php");
		exit();

	
?>