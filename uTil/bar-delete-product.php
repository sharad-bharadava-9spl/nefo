<?php

	require_once '../cOnfig/connection.php';
	require_once '../cOnfig/view.php';
	require_once '../cOnfig/authenticate.php';
	require_once '../cOnfig/languages/common.php';

	session_start();
	//$accessLevel = '3';
	
	// Authenticate & authorize
	authorizeUser($accessLevel);
		
	$productid = $_GET['productid'];
	$category = $_GET['category'];
	
	// Look for purchases with this productid, if it exists, throw an error.
	$deleteUser = "SELECT purchaseid FROM b_purchases WHERE category = $category AND productid = $productid";
	
	try
	{
		$result = $pdo3->prepare("$deleteUser");
		$result->execute();
		$data = $result->fetchAll();
	}
	catch (PDOException $e)
	{
			$error = 'Error fetching user: ' . $e->getMessage();
			echo $error;
			exit();
	}
		
	if ($data) {
		
		pageStart($lang['global-products'], NULL, $deleteFlowerScript, "pproducts", "admin", $lang['global-productscaps'], $_SESSION['successMessage'], $_SESSION['errorMessage']);
		echo "<center><div id='scriptMsg'><div class='error'>{$lang['not-allowed-to-delete']}</div></div></center>";
		exit();
		
		
	} else {
		
		// Build the delete statement
		$deleteUser = sprintf("DELETE FROM b_products WHERE productid = '%d';", $productid);
		try
		{
			$result = $pdo3->prepare("$deleteUser")->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
		$_SESSION['successMessage'] = $lang['product-deleted'];
		header("Location: ../bar-products.php");
		
	}