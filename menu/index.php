<?php
	
	session_start();
	
	if (isset($_GET['domain'])) {
		$_SESSION['domain'] = $_GET['domain'];
		$domain = $_GET['domain'];
	}
	
	require_once '../cOnfig/connection-tablet.php';
	require_once '../cOnfig/view-nohead.php';
	require_once '../cOnfig/authenticate.php';
	require_once '../cOnfig/languages/common.php';
	
	if (isset($_POST['menupin'])) {
		
		$menupin = $_POST['menupin'];
		
		if ($menupin == 6244) {
			
			// Set cookie
			setcookie( "mornacf1", "mornacf1", time() + (10 * 365 * 24 * 60 * 60) );
			header("Location: index.php");
			exit();
			
		}
		
	}
		
	// If no cookie exist, enter PIN.
	if (!isset($_COOKIE['mornacf1'])) {
		
		pageStart("CCS | Menu", NULL, NULL, "", "", "", $_SESSION['successMessage'], $_SESSION['errorMessage']);
		
		echo <<<EOD

<br /><br />
<center>
 <strong>PIN:<strong>
<form id="registerForm" action="" method="POST">
 <input type="password" name="menupin" value="" placeholder="" maxlength="4" autofocus />
<br /><br />
 <button type="submit">OK</button><br />
</form>		
</center>
EOD;

	} else {
		
	// If cookie is set, 

	pageStart("CCS | Menu", NULL, NULL, "pdispense", "menu", "MENU", $_SESSION['successMessage'], $_SESSION['errorMessage']);
	
?>
<div class="clearfloat"></div>

<?php

	// Query to look up categories
	$selectCats = "SELECT id, name, description, type from categories ORDER by sortorder ASC, id ASC";
	try
	{
		$resultCats = $pdo3->prepare("$selectCats");
		$resultCats->execute();
	}
	catch (PDOException $e)
	{
			$error = 'Error fetching user: ' . $e->getMessage();
			echo $error;
			exit();
	}

	while ($sort = $resultCats->fetch()) {
		
		$categoryid = $sort['id'];
		$name = $sort['name'];
		$type = $sort['type'];
		
		if ($categoryid == 1) {

		
	if ($_SESSION['menusortdisp'] == 0) {
		$sorting = "p.salesPrice ASC";
	} else {
		$sorting = "g.name ASC";
	}

	$selectFlower = "SELECT g.flowerid, g.name, g.breed2, g.flowertype, g.sativaPercentage, p.productid, p.purchaseid, p.salesPrice, p.realQuantity, p.growType, p.photoExt FROM flower g, purchases p WHERE p.category = 1 AND p.productid = g.flowerid AND p.closedAt IS NULL AND inMenu = 1 ORDER BY $sorting";
		try
		{
			$result = $pdo3->prepare("$selectFlower");
			$result->execute();
			$dataFlower = $result->fetchAll();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
		
		if ($dataFlower) {
		
		$i = 0;
		echo "<h3 class='title'>FLOWERS</h3>";
foreach ($dataFlower as $flower) {

		if ($flower['breed2'] != '') {
			$name = $flower['name'] . " x " . $flower['breed2'];
		} else {
			$name = $flower['name'];
		}
		
		if ($flower['flowertype'] == 'Hybrid' && $flower['sativaPercentage'] > 0 && $flower['sativaPercentage'] != NULL) {
			$percentageDisplay = '<br />(' . number_format($flower['sativaPercentage'],0) . '% s.)';
		} else {
			$percentageDisplay = '';
		}
		
		
	// Look up growtype
		$growtype = $flower['growType'];
	$growDetails = "SELECT growtype FROM growtypes WHERE growtypeid = $growtype";
		try
		{
			$result = $pdo3->prepare("$growDetails");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
		$growtype = $row['growtype'];
		

		
	$i++;

	$flower_row =	sprintf("
	<div class='displaybox'>
	 <div id='imageholder'>
	  <center><img src='../images/_$domain/purchases/%s' /></center>
	 </div>
	 <h3>%s</h3>
	 <span class='descprice'>
	  <span class='firstline'>%s<br /><span class='yellow' style='font-size: 16px; font-weight: 600; margin-top: 3px; display: inline-block;'>%s</span></span><span class='yellow'>%s</span>
	 </span>
	 <input class='specialInput' id='ppgcalc%d' name='sales[%d][ppg]' value='%0.02f' readonly /><span class='eurosign'>{$_SESSION['currencyoperator']}</span>
	 <div class='clearfloat'></div><br />

	</div>",
	  $flower['purchaseid'] . "." . $flower['photoExt'], $name, $growtype, $flower['flowertype'], $percentageDisplay, $i, $i, $flower['salesPrice']
	  );
	  echo $flower_row;
  }
  
	}		
	
	} else if ($categoryid == 2) {
		
	if ($_SESSION['menusortdisp'] == 0) {
		$sorting = "p.salesPrice ASC";
	} else {
		$sorting = "h.name ASC";
	}

  	$selectExtract = "SELECT h.extractid, h.name, h.extract, p.productid, p.purchaseid, p.salesPrice, p.realQuantity, p.photoExt FROM extract h, purchases p WHERE p.category = 2 AND p.productid = h.extractid AND p.closedAt IS NULL AND inMenu = 1 ORDER BY $sorting";
		try
		{
			$result = $pdo3->prepare("$selectExtract");
			$result->execute();
			$dataExtract = $result->fetchAll();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		

  
		if ($dataExtract) {
			
		echo "<h3 class='title'>EXTRACTS</h3>";
foreach ($dataExtract as $extract) {
	$i++;

	$extract_row =	sprintf("
	<div class='displaybox'>
	 <div id='imageholder'>
	  <center><img src='../images/_$domain/purchases/%s' /></center>
	 </div>
	 <h3>%s</h3>
	 <span class='descprice'>
	  <span class='firstline'>%s</span>
	 </span>
	 <input class='specialInput' id='ppgcalc%d' name='sales[%d][ppg]' value='%0.02f' readonly /><span class='eurosign'>{$_SESSION['currencyoperator']}</span>
	 <div class='clearfloat'></div><br />
	</div>",
	  $extract['purchaseid'] . "." . $extract['photoExt'], $extract['name'], $extract['extract'], $i, $i, $extract['salesPrice']
	  );
	  echo $extract_row;
  }		
}

} else {
  
  
	// Query to look up categories
	$selectCats = "SELECT id, name, type from categories where id = $categoryid";
		try
		{
			$resultscat = $pdo3->prepare("$selectCats");
			$resultscat->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		while ($category = $resultscat->fetch()) {
		
		$categoryid = $category['id'];
		$name = $category['name'];
  
 		
	if ($_SESSION['menusortdisp'] == 0) {
		$sorting = "p.salesPrice ASC";
	} else {
		$sorting = "pr.name ASC";
	}

	 // For each cat, look up products
  	$selectProduct = "SELECT pr.productid, pr.name, p.purchaseid, p.salesPrice, p.realQuantity, p.photoExt FROM products pr, purchases p WHERE p.category = $categoryid AND p.productid = pr.productid AND p.closedAt IS NULL AND inMenu = 1 ORDER BY $sorting;";
		try
		{
			$result = $pdo3->prepare("$selectProduct");
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
			
 		echo "<h3 class='title'>$name</h3>";

	
		foreach ($data as $product) {
	
	 		$i++;
			$productid = $product['productid'];
			$productName = $product['name'];
			
	 		
	
			$product_row =	sprintf("
			<div class='displaybox'>
			 <center><img src='../images/_$domain/purchases/%s' /></center>
			 <h3>%s</h3>
			 <center><input class='specialInput' id='ppgcalc%d' name='sales[%d][ppg]' value='%0.02f' readonly /><span class='eurosign'>{$_SESSION['currencyoperator']}</span></center>
			 <div class='clearfloat'></div><br />
			</div>",
			  $product['purchaseid'] . "." . $product['photoExt'], $product['name'], $i, $i, $product['salesPrice']
			  );
			  echo $product_row;
		  }
		
 		
	}
	
}
}
}
}
 
  
  
echo "</center>";

?>

	  <div class="clearFloat"></div>

	 </tbody>
	 </table>
	
	

<?php displayFooter(); ?>

