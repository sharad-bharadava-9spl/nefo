<?php

	require_once '../cOnfig/connection.php';
	require_once '../cOnfig/view.php';
	require_once '../cOnfig/authenticate.php';
	require_once '../cOnfig/languages/common.php';
		
	session_start();
	
	$accessLevel = '3';

	// Authenticate & authorize
	authorizeUser($accessLevel);

		$closingid = $_SESSION['closingid'];
		
		// Lookup closingdetails for products
		$closingDetailsLookup = "SELECT category, productid, purchaseid, weight FROM shiftclosedetails WHERE closingid = $closingid AND category < 3";
		
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

		$openingtime = date('Y-m-d H:i:s');
		$bankBalance = $_SESSION['bankBalance'];
		
		// Check if the last closing has been opened, to know whether to INSERT or UPDATE
		$openingLookup = "SELECT shiftOpenedNo FROM shiftclose WHERE closingid = '$closingid'";
				
		$result = mysql_query($openingLookup)
			or handleError($lang['error-noopeningdetails'],"Error loading opening from db: " . mysql_error());
	
		$row = mysql_fetch_array($result);
			$dayOpenedNo = $row['shiftOpenedNo'];
			$openingid = $dayOpenedNo;
			
		if ($dayOpenedNo > 0) {
			
			// Means part of the day has been opened already, so use UPDATE
			$query = sprintf("UPDATE shiftopen SET openingtime = '%s', stockDelta = '%f', prodStock = '%f', prodStockFlower = '%f', prodStockExtract = '%f', stockDeltaFlower = '%f', stockDeltaExtract = '%f' WHERE openingid = '%d'",
			  $openingtime, $stockDelta, $prodStock, $prodStockFlower, $prodStockExtract, $stockDeltaFlower, $stockDeltaExtract, $openingid);
	
			mysql_query($query)
				or handleError($lang['error-savedata'],"Error saving opening: " . mysql_error());
				
			$timeNow = date('Y-m-d H:i:s');
			
			$updateClosing = sprintf("UPDATE shiftclose SET disOpened = 2, disOpenedAt = '%s' WHERE closingid = '%d';",
				$timeNow,
				mysql_real_escape_string($closingid)
				);
			
			mysql_query($updateClosing)
				or handleError($lang['error-savedata'],"Error updating expense: " . mysql_error());
				
			$deleteWeights = "DELETE FROM shiftopendetails WHERE openingid = $openingid";
			
			mysql_query($deleteWeights)
				or handleError($lang['error-savedata'],"Error updating expense: " . mysql_error());
			

			
		} else {
			
			
			// Means no opening (rec. or dis.) has been done, so use INSERT
			$query = sprintf("INSERT INTO shiftopen (openingtime, stockDelta, prodStock, prodStockFlower, prodStockExtract, stockDeltaFlower, stockDeltaExtract) VALUES ('%s', '%f', '%f', '%f', '%f', '%f', '%f');",
			  $openingtime, $stockDelta, $prodStock, $prodStockFlower, $prodStockExtract, $stockDeltaFlower, $stockDeltaExtract);
	
			mysql_query($query)
				or handleError($lang['error-savedata'],"Error saving opening: " . mysql_error());
				
			$openingid = mysql_insert_id();
			
			$timeNow = date('Y-m-d H:i:s');
			
			$updateClosing = sprintf("UPDATE shiftclose SET disOpened = 2, shiftOpenedNo = '%d', disOpenedAt = '%s' WHERE closingid = '%d';",
				mysql_real_escape_string($openingid),
				$timeNow,
				mysql_real_escape_string($closingid)
				);

			mysql_query($updateClosing)
				or handleError($lang['error-savedata'],"Error updating expense: " . mysql_error());
					
		}

		while ($row = mysql_fetch_array($resultDetails2)) {
			$category = $row['category'];
			$productid = $row['productid'];
			$purchaseid = $row['purchaseid'];
			$weight = $row['weight'];		
				
		  	$query = sprintf("INSERT INTO shiftopendetails (openingid, category, productid, purchaseid, weight, prodOpenComment) VALUES ('%d', '%d', '%d', '%d', '%f', '%s');",
		  	$openingid, $category, $productid, $purchaseid, $weight, $lang['automatic-opening']);
		  
			mysql_query($query)
				or handleError($lang['error-savedata'],"Error saving opening: " . mysql_error());
				

			}		
				
		// On success: redirect.
		$_SESSION['successMessage'] = $lang['dispensary-opened-successfully'];
		header("Location: ../open-shift.php");
		exit();

	
?>