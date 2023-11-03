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
	
	$salesToday = "SELECT d.category, d.purchaseid, d.productid, SUM(d.quantity), SUM(d.amount) FROM b_salesdetails d, b_sales s WHERE d.saleid = s.saleid GROUP BY d.purchaseid ORDER BY category, purchaseid ASC";
	
	$salesTodayResult = mysql_query($salesToday)
		or handleError($lang['error-loadproductdata'],"Error loading product: " . mysql_error());
		
		
	// Poll totals per category
	$catsToday = "SELECT distinct d.category FROM b_salesdetails d, b_sales s WHERE d.saleid = s.saleid ORDER BY category ASC";
	
	$catsTodayResult = mysql_query($catsToday)
		or handleError($lang['error-loadproductdata'],"Error loading product: " . mysql_error());
		
		
	while ($catPoll = mysql_fetch_array($catsTodayResult)) {
		
		$catNo = $catPoll['category'];
		$catArray[] = $catNo;
		
		$TOTsalesToday = "SELECT SUM(d.quantity), SUM(d.amount) FROM b_salesdetails d, b_sales s WHERE d.saleid = s.saleid AND d.category = $catNo";
			
		$TOTsalesTodayResult = mysql_query($TOTsalesToday)
				or handleError($lang['error-loadproductdata'],"Error loading product: " . mysql_error());
				
			// Query to look for category
			$categoryDetailsCN = "SELECT name FROM b_categories WHERE id = $catNo";
			
			$resultCatCN = mysql_query($categoryDetailsCN)
				or handleError($lang['error-errorloadingflower'],"Error loading flower: " . mysql_error());
			
			$rowCN = mysql_fetch_array($resultCatCN);
				${'catName' . $catNo} = $rowCN['name'];
				
		$rowTOT = mysql_fetch_array($TOTsalesTodayResult);
			$TOTquantity = $rowTOT['SUM(d.quantity)'];
			$TOTrealQuantity = $rowTOT['SUM(d.realQuantity)'];
			$TOTamount = $rowTOT['SUM(d.amount)'];
			${'TOTquantity' . $catNo} = $TOTquantity;
			${'TOTrealQuantity' . $catNo} = $TOTrealQuantity;
			${'TOTamount' . $catNo} = $TOTamount;
			
		$i++;
	}
		
	pageStart($lang['title-dispenses'], NULL, $deleteSaleScript, "psales", "sales admin", $lang['global-dispensescaps'], $_SESSION['successMessage'], $_SESSION['errorMessage']);
	
echo <<<EOD
	 <table class='default' id='mainTable'>
	  <thead>
	   <tr>
	    <th>{$lang['global-category']}</th>
	    <th>{$lang['global-product']}</th>
	    <th>{$lang['units']}</th>
	    <th class='right'>{$_SESSION['currencyoperator']}</th>
	    <th class='right'>Coste</th>
	   </tr>
	  </thead>
	  <tbody>
EOD;

	
$currCategory = $catArray[0];
$cloop = 0;

while ($purchase = mysql_fetch_array($salesTodayResult)) {
	
	$purchaseid = $purchase['purchaseid'];
	$catREAL = $catArray[$cloop];
	$categoryID = $purchase['category'];
	$productid = $purchase['productid'];
	$quantity = $purchase['SUM(d.quantity)'];
	$realQuantity = $purchase['SUM(d.realQuantity)'];
	$amount = $purchase['SUM(d.amount)'];
	
	$findPrice = "SELECT purchaseQuantity, purchasePrice FROM b_purchases WHERE purchaseid = $purchaseid";
	
	$priceResult = mysql_query($findPrice)
		or handleError($lang['error-loadproductdata'],"Error loading product: " . mysql_error());
	
	$rowPR = mysql_fetch_array($priceResult);
		$purchaseQuantity = $rowPR['purchaseQuantity'];
		$purchasePrice = $rowPR['purchasePrice'];
		
	$purchasePrice = $purchaseQuantity * $purchasePrice;
		
		$purchaseCategory = $category;
		$queryVar = '';
		$prodSelect = 'b_products';
		$prodJoin = "productid";
		
		// Query to look for category
		$categoryDetails = "SELECT name FROM b_categories WHERE id = $categoryID";
		
		$resultCat = mysql_query($categoryDetails)
			or handleError($lang['error-errorloadingflower'],"Error loading flower: " . mysql_error());
		
		$row = mysql_fetch_array($resultCat);
			$catName = $row['name'];
			
	
		$selectProduct = "SELECT name{$queryVar} FROM {$prodSelect} WHERE ({$prodJoin} = {$productid})";
		
		$productResult = mysql_query($selectProduct)
				or handleError($lang['error-loadflowerdata'],"Error loading flower: " . mysql_error());
			
	    $row2 = mysql_fetch_array($productResult);
	    
		$name = $row2['name'];
	
	
  	// Show total row
	if ($currCategory != $categoryID) {
		

			
			$TOTquantity = ${'TOTquantity' . $currCategory};
			$TOTamount = ${'TOTamount' . $currCategory};
		  	echo "<tr><td class='clickableRow' href='purchase.php?purchaseid={$purchaseid}' style='border-bottom: 2px solid #333;'><strong>${'catName' . $currCategory}</strong></td>";
		  	echo "<td style='border-bottom: 2px solid #333;'><strong>TOTAL</strong></td>";
		  	echo "<td class='clickableRow right' href='purchase.php?purchaseid={$purchaseid}' style='border-bottom: 2px solid #333;'><strong>$TOTquantity u</strong></td>";
		  	echo "<td class='clickableRow right' href='purchase.php?purchaseid={$purchaseid}' style='border-bottom: 2px solid #333;'><strong>$TOTamount {$_SESSION['currencyoperator']}</strong></td><td style='border-bottom: 2px solid #333;'></td></tr>";
		  	$cloop++;
		  	$currCategory = $catArray[$cloop];

	}
	
	// Show product lines	
	
	
	  	echo "<tr><td class='clickableRow' href='purchase.php?purchaseid={$purchaseid}'>$catName</td>";
	  	echo "<td class='clickableRow' href='purchase.php?purchaseid={$purchaseid}'>$name</td>";
	  	echo "<td class='clickableRow right' href='purchase.php?purchaseid={$purchaseid}'>$quantity u</td>";
	  	echo "<td class='clickableRow right' href='purchase.php?purchaseid={$purchaseid}'>$amount {$_SESSION['currencyoperator']}</td>";
	  	echo "<td class='clickableRow right' href='purchase.php?purchaseid={$purchaseid}'>$purchasePrice {$_SESSION['currencyoperator']}</td></tr>";
			
}

// Show total row for last category
			
			$TOTquantity = ${'TOTquantity' . $currCategory};
			$TOTamount = ${'TOTamount' . $currCategory};
		  	echo "<tr><td class='clickableRow' href='purchase.php?purchaseid={$purchaseid}' style='border-bottom: 2px solid #333;'><strong>${'catName' . $currCategory}</strong></td>";
		  	echo "<td style='border-bottom: 2px solid #333;'><strong>TOTAL</strong></td>";
		  	echo "<td class='clickableRow right' href='purchase.php?purchaseid={$purchaseid}' style='border-bottom: 2px solid #333;'><strong>$TOTquantity u</strong></td>";
		  	echo "<td class='clickableRow right' href='purchase.php?purchaseid={$purchaseid}' style='border-bottom: 2px solid #333;'><strong>$TOTamount {$_SESSION['currencyoperator']}</strong></td><td style='border-bottom: 2px solid #333;'></td></tr>";
		  	$cloop++;
		  	$currCategory = $catArray[$cloop];

		echo "</tbody></table>";

