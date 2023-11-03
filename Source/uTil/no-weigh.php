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
			$openingid = $dayOpenedNo;
			
		if ($dayOpenedNo > 0) {
			
			// Means part of the day has been opened already, so use UPDATE
			$query = sprintf("UPDATE shiftopen SET openingtime = '%s', stockDelta = '%f', prodStock = '%f', prodStockFlower = '%f', prodStockExtract = '%f', stockDeltaFlower = '%f', stockDeltaExtract = '%f' WHERE openingid = '%d'",
			  $openingtime, $stockDelta, $prodStock, $prodStockFlower, $prodStockExtract, $stockDeltaFlower, $stockDeltaExtract, $openingid);
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
			
			$updateClosing = sprintf("UPDATE shiftclose SET disOpened = 2, disOpenedAt = '%s' WHERE closingid = '%d';",
				$timeNow, $closingid);
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
				
			$deleteWeights = "DELETE FROM shiftopendetails WHERE openingid = $openingid";
			try
			{
				$result = $pdo3->prepare("$deleteWeights")->execute();
			}
			catch (PDOException $e)
			{
					$error = 'Error fetching user: ' . $e->getMessage();
					echo $error;
					exit();
			}
			

			
		} else {
			
			
			// Means no opening (rec. or dis.) has been done, so use INSERT
			$query = sprintf("INSERT INTO shiftopen (openingtime, stockDelta, prodStock, prodStockFlower, prodStockExtract, stockDeltaFlower, stockDeltaExtract) VALUES ('%s', '%f', '%f', '%f', '%f', '%f', '%f');",
			  $openingtime, $stockDelta, $prodStock, $prodStockFlower, $prodStockExtract, $stockDeltaFlower, $stockDeltaExtract);
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
			
			$updateClosing = sprintf("UPDATE shiftclose SET disOpened = 2, shiftOpenedNo = '%d', disOpenedAt = '%s' WHERE closingid = '%d';",
				$openingid, $timeNow, $closingid);
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

		while ($row = $resultDetails2->fetch()) {

			$category = $row['category'];
			$productid = $row['productid'];
			$purchaseid = $row['purchaseid'];
			$weight = $row['weight'];		
				
		  	$query = sprintf("INSERT INTO shiftopendetails (openingid, category, productid, purchaseid, weight, prodOpenComment) VALUES ('%d', '%d', '%d', '%d', '%f', '%s');",
		  	$openingid, $category, $productid, $purchaseid, $weight, $lang['automatic-opening']);
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
				
		// On success: redirect.
		$_SESSION['successMessage'] = $lang['dispensary-opened-successfully'];
		header("Location: ../open-shift.php");