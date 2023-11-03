<?php
	
	require_once 'cOnfig/connection.php';
	require_once 'cOnfig/view.php';
	require_once 'cOnfig/authenticate.php';
	require_once 'cOnfig/languages/common.php';
	
	session_start();
	$accessLevel = '3';
	
	// Authenticate & authorize
	authorizeUser($accessLevel);
	
	getSettings();
	
	$salesToday = "SELECT MAX(d.category), d.purchaseid, MAX(d.productid), SUM(d.quantity), SUM(d.amount), SUM(d.realQuantity) FROM salesdetails d, sales s WHERE d.saleid = s.saleid GROUP BY category, d.purchaseid ORDER BY category, d.purchaseid ASC ";
		try
		{
			$salesTodayResult = $pdo3->prepare("$salesToday");
			$salesTodayResult->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching userX: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		
		
	// Poll totals per category
	$catsToday = "SELECT distinct d.category FROM salesdetails d, sales s WHERE d.saleid = s.saleid ORDER BY category ASC";
		try
		{
			$catsTodayResult = $pdo3->prepare("$catsToday");
			$catsTodayResult->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user2: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		while ($catPoll = $catsTodayResult->fetch()) {
		
		$catNo = $catPoll['category'];
		$catArray[] = $catNo;
		
		$TOTsalesToday = "SELECT SUM(d.quantity), SUM(d.amount), SUM(d.realQuantity) FROM salesdetails d, sales s WHERE d.saleid = s.saleid AND d.category = $catNo";
		try
		{
			$TOTsalesTodayResult = $pdo3->prepare("$TOTsalesToday");
			$TOTsalesTodayResult->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user3: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
				
		if ($catNo == 1) {
			${'catName' . $catNo} = $lang['global-flowers'];
		} else if ($catNo == 2) {
			${'catName' . $catNo} = $lang['global-extracts'];
		} else {
			
			// Query to look for category
			$categoryDetailsCN = "SELECT name FROM categories WHERE id = $catNo";
		try
		{
			$result = $pdo3->prepare("$categoryDetailsCN");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
				${'catName' . $catNo} = $rowCN['name'];
		}
		
				
		$rowTOT = $TOTsalesTodayResult->fetch();
			$TOTquantity = $rowTOT['SUM(d.quantity)'];
			$TOTrealQuantity = $rowTOT['SUM(d.realQuantity)'];
			$TOTamount = $rowTOT['SUM(d.amount)'];
			${'TOTquantity' . $catNo} = $TOTquantity;
			${'TOTrealQuantity' . $catNo} = $TOTrealQuantity;
			${'TOTamount' . $catNo} = $TOTamount;
			
		$i++;
	}
	$deleteSaleScript = <<<EOD
		$(document).ready(function() {
			$('#mainTable').tablesorter({
				usNumberFormat: true,
				headers: {
					3: {
						sorter: "dates"
					},
					7: {
						sorter: "dates"
					}
				}
			}); 
		});
	EOD;	
	pageStart($lang['title-dispenses'], NULL, $deleteSaleScript, "psales", "sales admin", $lang['global-dispensescaps'], $_SESSION['successMessage'], $_SESSION['errorMessage']);
	
echo <<<EOD
	 <table class='default' id='mainTable'>
	  <thead>
	   <tr>
	    <th>{$lang['global-category']}</th>
	    <th>{$lang['global-product']}</th>
	    <th>{$lang['global-quantity']}</th>
	    <th>{$lang['global-quantity']} real</th>
	    <th>Unidades</th>
	    <th class='right'>{$_SESSION['currencyoperator']}</th>
	    <th class='right'>Coste</th>
	   </tr>
	  </thead>
	  <tbody>
EOD;

	
$currCategory = $catArray[0];
$cloop = 0;

			while ($purchase = $salesTodayResult->fetch()) {

	$purchaseid = $purchase['purchaseid'];
	$catREAL = $catArray[$cloop];
	$categoryID = $purchase['MAX(d.category)'];
	$productid = $purchase['MAX(d.productid)'];
	$quantity = $purchase['SUM(d.quantity)'];
	$realQuantity = $purchase['SUM(d.realQuantity)'];
	$amount = $purchase['SUM(d.amount)'];
	
	$findPrice = "SELECT purchaseQuantity, purchasePrice FROM purchases WHERE purchaseid = $purchaseid";
		try
		{
			$result = $pdo3->prepare("$findPrice");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user4: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$rowPR = $result->fetch();
		$purchaseQuantity = $rowPR['purchaseQuantity'];
		$purchasePrice = $rowPR['purchasePrice'];
		
	$purchasePrice = $purchaseQuantity * $purchasePrice;
		
	if ($categoryID == 1) {
		$catName = $lang['global-flowers'];
		$purchaseCategory = 'Flower';
		$queryVar = ', breed2';
		$prodSelect = 'flower';
		$prodJoin = 'flowerid';
	} else if ($categoryID == 2) {
		$catName = $lang['global-extracts'];
		$purchaseCategory = 'Extract';
		$queryVar = '';
		$prodSelect = 'extract';
		$prodJoin = 'extractid';
	} else {
		$purchaseCategory = $category;
		$queryVar = '';
		$prodSelect = 'products';
		$prodJoin = "productid";
		
		// Query to look for category
		$categoryDetails = "SELECT name FROM categories WHERE id = $categoryID";
		try
		{
			$result = $pdo3->prepare("$categoryDetails");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
			$catName = $row['name'];
			
	}
	
		$selectProduct = "SELECT name{$queryVar} FROM {$prodSelect} WHERE ({$prodJoin} = {$productid})";
		try
		{
			$result = $pdo3->prepare("$selectProduct");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row2 = $result->fetch();
	    
	if ($row2['breed2'] != '') {
		$name = $row2['name'] . " x " . $row['breed2'];
	} else {
		$name = $row2['name'];
	}
	
	
  	// Show total row
	if ($currCategory != $categoryID) {
		
		if ($catREAL < 3) {
			
			$TOTquantity = ${'TOTquantity' . $currCategory};
			$TOTrealQuantity = ${'TOTrealQuantity' . $currCategory};
			$TOTamount = ${'TOTamount' . $currCategory};
		  	echo "<tr><td class='clickableRow' href='purchase.php?purchaseid={$purchaseid}' style='border-bottom: 2px solid #333;'><strong>${'catName' . $currCategory}</strong></td>";
		  	echo "<td style='border-bottom: 2px solid #333;'><strong>TOTAL</strong></td>";
		  	echo "<td class='clickableRow right' href='purchase.php?purchaseid={$purchaseid}' style='border-bottom: 2px solid #333;'><strong>$TOTquantity g</strong></td>";
		  	echo "<td class='clickableRow right' href='purchase.php?purchaseid={$purchaseid}' style='border-bottom: 2px solid #333;'><strong>$TOTrealQuantity g</strong></td>";
		  	echo "<td style='border-bottom: 2px solid #333;'></td>";
		  	echo "<td class='clickableRow right' href='purchase.php?purchaseid={$purchaseid}' style='border-bottom: 2px solid #333;'><strong>$TOTamount {$_SESSION['currencyoperator']}</strong></td>";
		  	echo "<td class='clickableRow right' href='purchase.php?purchaseid={$purchaseid}' style='border-bottom: 2px solid #333;'><strong>$purchasePrice {$_SESSION['currencyoperator']}</strong></td><td style='border-bottom: 2px solid #333;'></td></tr>";
		  	$cloop++;
		  	$currCategory = $catArray[$cloop];
		} else {
			
			$TOTquantity = ${'TOTquantity' . $currCategory};
			$TOTamount = ${'TOTamount' . $currCategory};
		  	echo "<tr><td class='clickableRow' href='purchase.php?purchaseid={$purchaseid}' style='border-bottom: 2px solid #333;'><strong>${'catName' . $currCategory}</strong></td>";
		  	echo "<td style='border-bottom: 2px solid #333;'><strong>TOTAL</strong></td>";
		  	echo "<td style='border-bottom: 2px solid #333;'></td>";
		  	echo "<td style='border-bottom: 2px solid #333;'></td>";
		  	echo "<td class='clickableRow right' href='purchase.php?purchaseid={$purchaseid}' style='border-bottom: 2px solid #333;'><strong>$TOTquantity u</strong></td>";
		  	echo "<td class='clickableRow right' href='purchase.php?purchaseid={$purchaseid}' style='border-bottom: 2px solid #333;'><strong>$TOTamount {$_SESSION['currencyoperator']}</strong></td><td style='border-bottom: 2px solid #333;'></td></tr>";
		  	$cloop++;
		  	$currCategory = $catArray[$cloop];
		}
	}
	
	// Show product lines	
	if ($categoryID < 3) {
		
		if ($purchasePrice != 0) {
		
	  	echo "<tr><td class='clickableRow' href='purchase.php?purchaseid={$purchaseid}'>$catName</td>";
	  	echo "<td class='clickableRow' href='purchase.php?purchaseid={$purchaseid}'>$name</td>";
	  	echo "<td class='clickableRow right' href='purchase.php?purchaseid={$purchaseid}'>$quantity g</td>";
	  	echo "<td class='clickableRow right' href='purchase.php?purchaseid={$purchaseid}'>$realQuantity g</td>";
	  	echo "<td></td>";
	  	echo "<td class='clickableRow right' href='purchase.php?purchaseid={$purchaseid}'>$amount {$_SESSION['currencyoperator']}</td>";
	  	echo "<td class='clickableRow right' href='purchase.php?purchaseid={$purchaseid}'>$purchasePrice {$_SESSION['currencyoperator']}</td></tr>";
	  	
  	}
		
	} else {
	
		if ($purchasePrice != 0) {	
	  	echo "<tr><td class='clickableRow' href='purchase.php?purchaseid={$purchaseid}'>$catName</td>";
	  	echo "<td class='clickableRow' href='purchase.php?purchaseid={$purchaseid}'>$name</td>";
	  	echo "<td></td>";
	  	echo "<td></td>";
	  	echo "<td class='clickableRow right' href='purchase.php?purchaseid={$purchaseid}'>$quantity u</td>";
	  	echo "<td class='clickableRow right' href='purchase.php?purchaseid={$purchaseid}'>$amount {$_SESSION['currencyoperator']}</td>";
	  	echo "<td class='clickableRow right' href='purchase.php?purchaseid={$purchaseid}'>$purchasePrice {$_SESSION['currencyoperator']}</td></tr>";
  	}
	}
			
}

// Show total row for last category
		if ($categoryID < 3) {
			
			$TOTquantity = ${'TOTquantity' . $currCategory};
			$TOTrealQuantity = ${'TOTrealQuantity' . $currCategory};
			$TOTamount = ${'TOTamount' . $currCategory};
		  	echo "<tr><td class='clickableRow' href='purchase.php?purchaseid={$purchaseid}' style='border-bottom: 2px solid #333;'><strong>${'catName' . $currCategory}</strong></td>";
		  	echo "<td style='border-bottom: 2px solid #333;'><strong>TOTAL</strong></td>";
		  	echo "<td class='clickableRow right' href='purchase.php?purchaseid={$purchaseid}' style='border-bottom: 2px solid #333;'><strong>$TOTquantity g</strong></td>";
		  	echo "<td class='clickableRow right' href='purchase.php?purchaseid={$purchaseid}' style='border-bottom: 2px solid #333;'><strong>$TOTrealQuantity g</strong></td>";
		  	echo "<td style='border-bottom: 2px solid #333;'></td>";
		  	echo "<td class='clickableRow right' href='purchase.php?purchaseid={$purchaseid}' style='border-bottom: 2px solid #333;'><strong>$TOTamount {$_SESSION['currencyoperator']}</strong></td><td style='border-bottom: 2px solid #333;'></td></tr>";
		  	$cloop++;
		  	$currCategory = $catArray[$cloop];
		} else {
			
			$TOTquantity = ${'TOTquantity' . $currCategory};
			$TOTamount = ${'TOTamount' . $currCategory};
		  	echo "<tr><td class='clickableRow' href='purchase.php?purchaseid={$purchaseid}' style='border-bottom: 2px solid #333;'><strong>${'catName' . $currCategory}</strong></td>";
		  	echo "<td style='border-bottom: 2px solid #333;'><strong>TOTAL</strong></td>";
		  	echo "<td style='border-bottom: 2px solid #333;'></td>";
		  	echo "<td style='border-bottom: 2px solid #333;'></td>";
		  	echo "<td class='clickableRow right' href='purchase.php?purchaseid={$purchaseid}' style='border-bottom: 2px solid #333;'><strong>$TOTquantity u</strong></td>";
		  	echo "<td class='clickableRow right' href='purchase.php?purchaseid={$purchaseid}' style='border-bottom: 2px solid #333;'><strong>$TOTamount {$_SESSION['currencyoperator']}</strong></td><td style='border-bottom: 2px solid #333;'></td></tr>";
		  	$cloop++;
		  	$currCategory = $catArray[$cloop];
		}

		echo "</tbody></table>";

