<?php

	require_once '../cOnfig/connection.php';
	require_once '../cOnfig/view.php';
	require_once '../cOnfig/authenticate.php';
	require_once '../cOnfig/languages/common.php';
		
	session_start();
	
	//$accessLevel = '3';

	// Authenticate & authorize
	authorizeUser($accessLevel);
	
	$closingid = $_GET['cid'];
	
	$timeNow = date('Y-m-d H:i:s');
	
	// Check if opening exists yet
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
		$openingid = $dayOpenedNo;
		
	if ($dayOpenedNo > 0) {
		
		// Delete from openingother
		$deleteOpenOther = "DELETE from openingother WHERE categoryType = 1 AND openingid = '$openingid'";
		try
		{
			$result = $pdo3->prepare("$deleteOpenOther")->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
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
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
			
		// Update flags
		$flagUpdate = "UPDATE closing SET dis2Opened = 2, dis2OpenedBy = '{$_SESSION['user_id']}', dis2OpenedAt = '$timeNow' WHERE closingid = $closingid";
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
		
	} else {
		
		// No opening exists. Let's create it.
		$query = sprintf("INSERT INTO opening (openingtime) VALUES ('%s');",
		  $timeNow);
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
		
		// Update flags
		$flagUpdate = "UPDATE closing SET dis2Opened = 2, dis2OpenedBy = '{$_SESSION['user_id']}', dis2OpenedAt = '$timeNow', dayOpenedNo = '$openingid' WHERE closingid = $closingid";
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
	}

	// Insert into openingother
	$otherLookup = "SELECT category, categoryType, prodStock FROM closingother WHERE categoryType = 1 AND closingid = $closingid";
	try
	{
		$results = $pdo3->prepare("$otherLookup");
		$results->execute();
	}
	catch (PDOException $e)
	{
			$error = 'Error fetching user: ' . $e->getMessage();
			echo $error;
			exit();
	}
	
	while ($user = $results->fetch()) {
		
		$category = $user['category'];
		$categoryType = $user['categoryType'];
		$prodStock = $user['prodStock'];
		
		$query = sprintf("INSERT INTO openingother (openingid, category, categoryType, prodStock, stockDelta) VALUES ('%d', '%d', '%d', '%f', '%f');",
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

	
	// Insert into openingdetails
	$closingDetailsLookup = "SELECT category, productid, purchaseid, weight, categoryType FROM closingdetails WHERE categoryType = 1 AND closingid = $closingid";
	try
	{
		$resultDetails = $pdo3->prepare("$closingDetailsLookup");
		$resultDetails->execute();
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
		$productid = $row['productid'];
		$purchaseid = $row['purchaseid'];
		$weight = $row['weight'];		
			
	  	$query = sprintf("INSERT INTO openingdetails (openingid, category, categoryType, productid, purchaseid, weight, prodOpenComment) VALUES ('%d', '%d', '%d', '%d', '%d', '%f', '%s');",
	  	$openingid, $category, $categoryType, $productid, $purchaseid, $weight, 'Automatic opening');
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
	header("Location: ../open-day.php");