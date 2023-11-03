<?php

	require_once '../cOnfig/connection.php';
	require_once '../cOnfig/view.php';
	require_once '../cOnfig/authenticate.php';
	require_once '../cOnfig/languages/common.php';
		
	session_start();
	
	//$accessLevel = '3';

	// Authenticate & authorize
	authorizeUser($accessLevel);
	
	$timeNow = date('Y-m-d H:i:s');
	
	$openingLookup = "SELECT openingid FROM opening";
	try
	{
		$result = $pdo3->prepare("$openingLookup");
		$result->execute();
	}
	catch (PDOException $e)
	{
			$error = 'Error fetching user1: ' . $e->getMessage();
			echo $error;
			exit();
	}

	$row = $result->fetch();
		$openingid = $row['openingid'];

	
	$query = "SELECT purchaseid, category, productid, realQuantity FROM purchases WHERE closedAt IS NULL";
	try
	{
		$results = $pdo3->prepare("$query");
		$results->execute();
	}
	catch (PDOException $e)
	{
			$error = 'Error fetching user2: ' . $e->getMessage();
			echo $error;
			exit();
	}
	
		// Delete from openingdetails
		$deleteOpenDetails = "DELETE from openingdetails WHERE categoryType = 1 AND openingid = '$openingid'";
		try
		{
			$result = $pdo3->prepare("$deleteOpenDetails")->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user3: ' . $e->getMessage();
				echo $error;
				exit();
		}


	while ($row = $results->fetch()) {
		$purchaseid = $row['purchaseid'];
		$category = $row['category'];
		$productid = $row['productid'];
		$realQuantity = $row['realQuantity'];
		
		// Look up category type to ensure you only do gram categoreis!
		$query = "SELECT type FROM categories WHERE id = $category";
		try
		{
			$result = $pdo3->prepare("$query");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user4: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$rowB = $result->fetch();
			$type = $rowB['type'];
			
		if ($category > 2 && $type == 0) {
		
			$query = "INSERT INTO openingdetails (openingid, category, categoryType, productid, purchaseid, weight) VALUES ($openingid, $category, 0, $productid, $purchaseid, $realQuantity)";
			try
			{
				$result = $pdo3->prepare("$query")->execute();
			}
			catch (PDOException $e)
			{
					$error = 'Error fetching user5: ' . $e->getMessage();
					echo $error;
					exit();
			}
			
		}
	}
	
	$prodStock = $prodStockFlower + $prodStockExtract + $otherTotal;

	// For each product, write to openingdetails
	// Then set the flag as "firstdayrecopen" = done
	
		// Update Opening with product weights
		$query = sprintf("UPDATE opening SET openingtime = '%s';",
		$timeNow);
		try
		{
			$result = $pdo3->prepare("$query")->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user6: ' . $e->getMessage();
				echo $error;
				exit();
		}
		
			
		// Update flags
		$flagUpdate = "UPDATE opening SET firstDis2Open = 2, firstDis2OpenBy = '{$_SESSION['user_id']}', firstDis2OpenAt = '$timeNow' WHERE openingid = '$openingid'";
		try
		{
			$result = $pdo3->prepare("$flagUpdate")->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user7: ' . $e->getMessage();
				echo $error;
				exit();
		}			
	// On success: redirect.
	$_SESSION['successMessage'] = $lang['dispensary-opened-successfully'];
	header("Location: ../open-day.php");