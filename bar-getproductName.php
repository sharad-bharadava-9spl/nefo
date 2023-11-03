<?php
	require_once 'cOnfig/connection.php';
	require_once 'cOnfig/languages/common.php';

	session_start();
	$accessLevel = '3';

	$product_id = $_GET['product_id']; 

		$getProduct = "SELECT a.name from b_products a,b_purchases b  WHERE a.productid = b.productid AND b.purchaseid = $product_id";

		try
		{
			$result = $pdo3->prepare("$getProduct");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching product: ' . $e->getMessage();
				echo $error;
				exit();
		}
		$row = $result->fetch();
			echo $productName = $row['name'];


