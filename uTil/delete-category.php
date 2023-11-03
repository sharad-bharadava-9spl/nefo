<?php

	require_once '../cOnfig/connection.php';
	require_once '../cOnfig/authenticate.php';
	require_once '../cOnfig/view.php';
	require_once '../cOnfig/languages/common.php';

	session_start();
	$accessLevel = '3';
	
	// Authenticate & authorize
	authorizeUser($accessLevel);
	
	$categoryid = $_GET['categoryid'];
	
	
	
	// Look for purchases with this productid, if it exists, throw an error.
	$deleteUser = "SELECT purchaseid FROM purchases WHERE category = $categoryid";
	
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
		echo "<center><div id='scriptMsg'><div class='error'>{$lang['not-allowed-to-delete-category']}</div></div></center>";
		exit();
		
		
	} else {
		// check for category icon
		$selectCat = "SELECT icon FROM categories WHERE id= $categoryid";

		try
		{
			$icon_result = $pdo3->prepare("$selectCat");
			$icon_result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
		$iconRow = $icon_result->fetch();
			$icon = $iconRow['icon'];
			$domain = $_SESSION['domain'];
			if(trim($icon) != ''){
				$icon_path = "../images/_$domain/category/".$icon;
				unlink($icon_path);
			}


		// Build the delete statement
		$deleteUser = "DELETE FROM categories WHERE id = $categoryid";
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
		$_SESSION['successMessage'] = $lang['category-deleted'];
		header("Location: ../categories.php");
		
	}