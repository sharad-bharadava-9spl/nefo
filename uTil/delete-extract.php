<?php

	require_once '../cOnfig/connection.php';
	require_once '../cOnfig/view.php';
	require_once '../cOnfig/authenticate.php';
	require_once '../cOnfig/languages/common.php';

	session_start();
	//$accessLevel = '3';
	
	// Authenticate & authorize
	authorizeUser($accessLevel);
		
	$productid = $_GET['extractid'];
	
	
	// Look for purchases with this productid, if it exists, throw an error.
	$deleteUser = "SELECT purchaseid FROM purchases WHERE category = 2 AND productid = $productid";
	
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
		$deleteUser = sprintf("DELETE FROM extract WHERE extractid = '%d';", $productid);
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
		$_SESSION['successMessage'] = $lang['extract-deleted'];
		header("Location: ../products.php");
		
	}