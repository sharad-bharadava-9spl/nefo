<?php 

	require_once 'cOnfig/connection.php';
	require_once 'cOnfig/languages/common.php';

	session_start();
	$accessLevel = '3';
	
	getSettings();
	
	$phrase = trim($_GET['phrase']);
	
	if ($phrase != '') {
	
	// Look up category IDs, then loop through each one and add the relevant query
	$query = "SELECT id FROM categories WHERE id > 2";
	try
	{
		$results = $pdo3->prepare("$query");
		$results->execute();
	}
	catch (PDOException $e)
	{
			$error = 'Error fetching user: ' . $e->getMessage();
			echo $error;
			exit();
	}

	while ($row = $results->fetch()) {
		
		$id = $row['id'];
		$catQuery .= " UNION ALL SELECT p.productid, p.name, p.breed2, pu.purchaseid, pu.growType, pu.category, pu.salesPrice FROM products p, purchases pu WHERE p.productid = pu.productid AND pu.category = $id AND pu.closedAt IS NULL AND inMenu = 1 AND p.name LIKE ('%$phrase%')";

	}
	
	// Search for phrase in flower, extract, other categories, only open purchases
	$query = "SELECT p.flowerid, p.name, p.breed2, pu.purchaseid, pu.growType, pu.category, pu.salesPrice FROM flower p, purchases pu WHERE p.flowerid = pu.productid AND pu.category = 1 AND pu.closedAt IS NULL AND inMenu = 1 AND p.name LIKE ('%$phrase%') UNION ALL SELECT p.extractid, p.name, '' AS breed2, pu.purchaseid, pu.growType, pu.category, pu.salesPrice FROM extract p, purchases pu WHERE p.extractid = pu.productid AND pu.category = 2 AND pu.closedAt IS NULL AND inMenu = 1 AND p.name LIKE ('%$phrase%')" . $catQuery;
	try
	{
		$result = $pdo3->prepare("$query");
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
		
		foreach ($data as $row) {
	
			$category = $row['category'];
			$name = $row['name'];
			$salesPrice = $row['salesPrice'];
			$purchaseid = $row['purchaseid'];
			
			$queryC = "SELECT name, type FROM categories WHERE id = $category";
			try
			{
				$resultC = $pdo3->prepare("$queryC");
				$resultC->execute();
			}
			catch (PDOException $e)
			{
					$error = 'Error fetching user: ' . $e->getMessage();
					echo $error;
					exit();
			}
		
			$rowC = $resultC->fetch();
				$categoryName = $rowC['name'];
				$type = $rowC['type'];

			$output .= <<<EOD
 <tr onClick='zoomTo($category,$purchaseid,$type)'>
  <td><a href="#" >$categoryName - $name ($salesPrice &euro;)</a></td>
 </tr>
EOD;
		}

	}

	/*
	// Search for phrase in flower, extract, other categories, only open purchases
	$query = "SELECT p.extractid, p.name, '' AS breed2, pu.purchaseid, pu.growType, pu.category, pu.salesPrice FROM extract p, purchases pu WHERE p.extractid = pu.productid AND pu.category = 2 AND pu.closedAt IS NULL AND inMenu = 1 AND p.name LIKE ('%$phrase%')";
	try
	{
		$result = $pdo3->prepare("$query");
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
		
		foreach ($data as $row) {
	
			$category = $row['category'];
			$name = $row['name'];
			$salesPrice = $row['salesPrice'];
			
			$queryC = "SELECT name FROM categories WHERE id = $category";
			try
			{
				$resultC = $pdo3->prepare("$queryC");
				$resultC->execute();
			}
			catch (PDOException $e)
			{
					$error = 'Error fetching user: ' . $e->getMessage();
					echo $error;
					exit();
			}
		
			$rowC = $resultC->fetch();
				$categoryName = $rowC['name'];

			$output .= <<<EOD
 <tr>
  <td><a href="#">$categoryName - $name ($salesPrice &euro;)</a></td>
 </tr>
EOD;
		}
				
	}
	*/
	
	echo $output . "<br />";

}