<?php 

	require_once 'cOnfig/connection.php';
	require_once 'cOnfig/authenticate.php';

	session_start();
	$accessLevel = '3';
	
	$category = $_GET['cat'];
	
	$selectExpenseCat = "SELECT categoryid, nameen, descriptionen FROM expensecategories WHERE sub = $category";
		try
		{
			$results = $pdo3->prepare("$selectExpenseCat");
			$results->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		while ($category = $results->fetch()) {
		
	  	$categoryid = $category['categoryid'];
	  	$catname = $category['nameen'];
		
		$category_row .= "<option value='$categoryid'>$catname</option>";

	}
		
	echo $category_row;