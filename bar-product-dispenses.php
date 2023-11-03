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
	
	$salesToday = "SELECT d.category, d.purchaseid, d.productid, SUM(d.quantity), SUM(d.amount) FROM b_salesdetails d, b_sales s WHERE d.saleid = s.saleid AND DATE(saletime) = DATE(NOW()) GROUP BY d.purchaseid ORDER BY category, purchaseid ASC";
	
	$salesTodayResult = mysql_query($salesToday)
		or handleError($lang['error-loadproductdata'],"Error loading product: " . mysql_error());
		
		
	// Poll totals per category
	$catsToday = "SELECT distinct d.category FROM b_salesdetails d, b_sales s WHERE d.saleid = s.saleid AND DATE(saletime) = DATE(NOW()) ORDER BY category ASC";
	
	$catsTodayResult = mysql_query($catsToday)
		or handleError($lang['error-loadproductdata'],"Error loading product: " . mysql_error());
		
		
	while ($catPoll = mysql_fetch_array($catsTodayResult)) {
		
		$catNo = $catPoll['category'];
		$catArray[] = $catNo;
		
		$TOTsalesToday = "SELECT SUM(d.quantity), SUM(d.amount) FROM b_salesdetails d, b_sales s WHERE d.saleid = s.saleid AND DATE(saletime) = DATE(NOW()) AND d.category = $catNo";
			
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
	
	echo "<center><a href='bar-product-dispenses-total.php' class='cta'>Totales</a></center>";

echo <<<EOD
	<h3 class="title">HOY</h3>
	 <table class='default' id='mainTable'>
	  <thead>
	   <tr>
	    <th>{$lang['global-category']}</th>
	    <th>{$lang['global-product']}</th>
	    <th>{$lang['units']}</th>
	    <th class='right'>{$_SESSION['currencyoperator']}</th>
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
		  	echo "<td class='clickableRow right' href='purchase.php?purchaseid={$purchaseid}' style='border-bottom: 2px solid #333;'><strong>$TOTamount {$_SESSION['currencyoperator']}</strong></td></tr>";
		  	$cloop++;
		  	$currCategory = $catArray[$cloop];
	}
	
	// Show product lines	

		
	  	echo "<tr><td class='clickableRow' href='purchase.php?purchaseid={$purchaseid}'>$catName</td>";
	  	echo "<td class='clickableRow' href='purchase.php?purchaseid={$purchaseid}'>$name</td>";
	  	echo "<td class='clickableRow right' href='purchase.php?purchaseid={$purchaseid}'>$quantity u</td>";
	  	echo "<td class='clickableRow right' href='purchase.php?purchaseid={$purchaseid}'>$amount {$_SESSION['currencyoperator']}</td></tr>";
		
			
}

// Show total row for last category

			
			$TOTquantity = ${'TOTquantity' . $currCategory};
			$TOTamount = ${'TOTamount' . $currCategory};
		  	echo "<tr><td class='clickableRow' href='purchase.php?purchaseid={$purchaseid}' style='border-bottom: 2px solid #333;'><strong>${'catName' . $currCategory}</strong></td>";
		  	echo "<td style='border-bottom: 2px solid #333;'><strong>TOTAL</strong></td>";
		  	echo "<td class='clickableRow right' href='purchase.php?purchaseid={$purchaseid}' style='border-bottom: 2px solid #333;'><strong>$TOTquantity u</strong></td>";
		  	echo "<td class='clickableRow right' href='purchase.php?purchaseid={$purchaseid}' style='border-bottom: 2px solid #333;'><strong>$TOTamount {$_SESSION['currencyoperator']}</strong></td></tr>";
		  	$cloop++;
		  	$currCategory = $catArray[$cloop];

		echo "</tbody></table>";


unset($salesToday);
unset($salesTodayResult);
unset($catsToday);
unset($catsTodayResult);
unset($catPoll);
unset($catsTodayResult);
unset($catNo);
unset($catArray);
unset($TOTsalesToday);
unset($TOTsalesTodayResult);
unset($catName);
unset($catName0);
unset($catName1);
unset($catName2);
unset($catName3);
unset($catName4);
unset($catName5);
unset($catName6);
unset($catName7);
unset($catName8);
unset($catName9);
unset($catName10);
unset($catName11);
unset($catName12);
unset($catName13);
unset($catName14);
unset($catName15);
unset($catName16);
unset($catName17);
unset($catName18);
unset($catName19);
unset($categoryDetailsCN);
unset($resultCatCN);
unset($rowCN);
unset($rowTOT);
unset($TOTquantity);
unset($TOTquantity0);
unset($TOTquantity1);
unset($TOTquantity2);
unset($TOTquantity3);
unset($TOTquantity4);
unset($TOTquantity5);
unset($TOTquantity6);
unset($TOTquantity7);
unset($TOTquantity8);
unset($TOTquantity9);
unset($TOTquantity10);
unset($TOTquantity11);
unset($TOTquantity12);
unset($TOTquantity13);
unset($TOTquantity14);
unset($TOTquantity15);
unset($TOTquantity16);
unset($TOTquantity17);
unset($TOTquantity18);
unset($TOTquantity19);
unset($TOTrealQuantity);
unset($TOTrealQuantity0);
unset($TOTrealQuantity1);
unset($TOTrealQuantity2);
unset($TOTrealQuantity3);
unset($TOTrealQuantity4);
unset($TOTrealQuantity5);
unset($TOTrealQuantity6);
unset($TOTrealQuantity7);
unset($TOTrealQuantity8);
unset($TOTrealQuantity9);
unset($TOTrealQuantity10);
unset($TOTrealQuantity11);
unset($TOTrealQuantity12);
unset($TOTrealQuantity13);
unset($TOTrealQuantity14);
unset($TOTrealQuantity15);
unset($TOTrealQuantity16);
unset($TOTrealQuantity17);
unset($TOTrealQuantity18);
unset($TOTrealQuantity19);
unset($TOTamount);
unset($TOTamount0);
unset($TOTamount1);
unset($TOTamount2);
unset($TOTamount3);
unset($TOTamount4);
unset($TOTamount5);
unset($TOTamount6);
unset($TOTamount7);
unset($TOTamount8);
unset($TOTamount9);
unset($TOTamount10);
unset($TOTamount11);
unset($TOTamount12);
unset($TOTamount13);
unset($TOTamount14);
unset($TOTamount15);
unset($TOTamount16);
unset($TOTamount17);
unset($TOTamount18);
unset($TOTamount19);
unset($currCategory);
unset($cloop);
unset($purchase);
unset($purchaseid);
unset($catREAL);
unset($categoryID);
unset($productid);
unset($quantity);
unset($amount);
unset($catName);
unset($purchaseCategory);
unset($queryVar);
unset($prodSelect);
unset($prodJoin);
unset($categoryDetails);
unset($resultCat);
unset($row);
unset($catName);
unset($selectProduct);
unset($productResult);
unset($row2);
unset($name);
unset($currCategory);
unset($catREAL);





	$salesToday = "SELECT d.category, d.purchaseid, d.productid, SUM(d.quantity), SUM(d.amount) FROM b_salesdetails d, b_sales s WHERE d.saleid = s.saleid AND DATE(saletime) = DATE_ADD(DATE(NOW()), INTERVAL -1 DAY) GROUP BY d.purchaseid ORDER BY category, purchaseid ASC";
	
	$salesTodayResult = mysql_query($salesToday)
		or handleError($lang['error-loadproductdata'],"Error loading product: " . mysql_error());
		
		
	// Poll totals per category
	$catsToday = "SELECT distinct d.category FROM b_salesdetails d, b_sales s WHERE d.saleid = s.saleid AND DATE(saletime) = DATE_ADD(DATE(NOW()), INTERVAL -1 DAY) ORDER BY category ASC";
	
	$catsTodayResult = mysql_query($catsToday)
		or handleError($lang['error-loadproductdata'],"Error loading product: " . mysql_error());
		
		
	while ($catPoll = mysql_fetch_array($catsTodayResult)) {
		
		$catNo = $catPoll['category'];
		$catArray[] = $catNo;
		
		$TOTsalesToday = "SELECT SUM(d.quantity), SUM(d.amount) FROM b_salesdetails d, b_sales s WHERE d.saleid = s.saleid AND DATE(saletime) = DATE_ADD(DATE(NOW()), INTERVAL -1 DAY) AND d.category = $catNo";
			
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
		

	$repDate = date('d-m-Y',(strtotime ( '-1 day' , strtotime (date('Y-m-d')) ) ));

echo <<<EOD
    <br /><br />
	<h3 class="title">$repDate</h3>
	 <table class='default' id='mainTable'>
	  <thead>
	   <tr>
	    <th>{$lang['global-category']}</th>
	    <th>{$lang['global-product']}</th>
	    <th>{$lang['units']}</th>
	    <th class='right'>{$_SESSION['currencyoperator']}</th>
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
		  	echo "<td class='clickableRow right' href='purchase.php?purchaseid={$purchaseid}' style='border-bottom: 2px solid #333;'><strong>$TOTamount {$_SESSION['currencyoperator']}</strong></td></tr>";
		  	$cloop++;
		  	$currCategory = $catArray[$cloop];
	}
	
	// Show product lines	

		
	  	echo "<tr><td class='clickableRow' href='purchase.php?purchaseid={$purchaseid}'>$catName</td>";
	  	echo "<td class='clickableRow' href='purchase.php?purchaseid={$purchaseid}'>$name</td>";
	  	echo "<td class='clickableRow right' href='purchase.php?purchaseid={$purchaseid}'>$quantity u</td>";
	  	echo "<td class='clickableRow right' href='purchase.php?purchaseid={$purchaseid}'>$amount {$_SESSION['currencyoperator']}</td></tr>";
		
			
}

// Show total row for last category

			
			$TOTquantity = ${'TOTquantity' . $currCategory};
			$TOTamount = ${'TOTamount' . $currCategory};
		  	echo "<tr><td class='clickableRow' href='purchase.php?purchaseid={$purchaseid}' style='border-bottom: 2px solid #333;'><strong>${'catName' . $currCategory}</strong></td>";
		  	echo "<td style='border-bottom: 2px solid #333;'><strong>TOTAL</strong></td>";
		  	echo "<td class='clickableRow right' href='purchase.php?purchaseid={$purchaseid}' style='border-bottom: 2px solid #333;'><strong>$TOTquantity u</strong></td>";
		  	echo "<td class='clickableRow right' href='purchase.php?purchaseid={$purchaseid}' style='border-bottom: 2px solid #333;'><strong>$TOTamount {$_SESSION['currencyoperator']}</strong></td></tr>";
		  	$cloop++;
		  	$currCategory = $catArray[$cloop];

		echo "</tbody></table>";



unset($salesToday);
unset($salesTodayResult);
unset($catsToday);
unset($catsTodayResult);
unset($catPoll);
unset($catsTodayResult);
unset($catNo);
unset($catArray);
unset($TOTsalesToday);
unset($TOTsalesTodayResult);
unset($catName);
unset($catName0);
unset($catName1);
unset($catName2);
unset($catName3);
unset($catName4);
unset($catName5);
unset($catName6);
unset($catName7);
unset($catName8);
unset($catName9);
unset($catName10);
unset($catName11);
unset($catName12);
unset($catName13);
unset($catName14);
unset($catName15);
unset($catName16);
unset($catName17);
unset($catName18);
unset($catName19);
unset($categoryDetailsCN);
unset($resultCatCN);
unset($rowCN);
unset($rowTOT);
unset($TOTquantity);
unset($TOTquantity0);
unset($TOTquantity1);
unset($TOTquantity2);
unset($TOTquantity3);
unset($TOTquantity4);
unset($TOTquantity5);
unset($TOTquantity6);
unset($TOTquantity7);
unset($TOTquantity8);
unset($TOTquantity9);
unset($TOTquantity10);
unset($TOTquantity11);
unset($TOTquantity12);
unset($TOTquantity13);
unset($TOTquantity14);
unset($TOTquantity15);
unset($TOTquantity16);
unset($TOTquantity17);
unset($TOTquantity18);
unset($TOTquantity19);
unset($TOTrealQuantity);
unset($TOTrealQuantity0);
unset($TOTrealQuantity1);
unset($TOTrealQuantity2);
unset($TOTrealQuantity3);
unset($TOTrealQuantity4);
unset($TOTrealQuantity5);
unset($TOTrealQuantity6);
unset($TOTrealQuantity7);
unset($TOTrealQuantity8);
unset($TOTrealQuantity9);
unset($TOTrealQuantity10);
unset($TOTrealQuantity11);
unset($TOTrealQuantity12);
unset($TOTrealQuantity13);
unset($TOTrealQuantity14);
unset($TOTrealQuantity15);
unset($TOTrealQuantity16);
unset($TOTrealQuantity17);
unset($TOTrealQuantity18);
unset($TOTrealQuantity19);
unset($TOTamount);
unset($TOTamount0);
unset($TOTamount1);
unset($TOTamount2);
unset($TOTamount3);
unset($TOTamount4);
unset($TOTamount5);
unset($TOTamount6);
unset($TOTamount7);
unset($TOTamount8);
unset($TOTamount9);
unset($TOTamount10);
unset($TOTamount11);
unset($TOTamount12);
unset($TOTamount13);
unset($TOTamount14);
unset($TOTamount15);
unset($TOTamount16);
unset($TOTamount17);
unset($TOTamount18);
unset($TOTamount19);
unset($currCategory);
unset($cloop);
unset($purchase);
unset($purchaseid);
unset($catREAL);
unset($categoryID);
unset($productid);
unset($quantity);
unset($amount);
unset($catName);
unset($purchaseCategory);
unset($queryVar);
unset($prodSelect);
unset($prodJoin);
unset($categoryDetails);
unset($resultCat);
unset($row);
unset($catName);
unset($selectProduct);
unset($productResult);
unset($row2);
unset($name);
unset($currCategory);
unset($catREAL);




	$salesToday = "SELECT d.category, d.purchaseid, d.productid, SUM(d.quantity), SUM(d.amount) FROM b_salesdetails d, b_sales s WHERE d.saleid = s.saleid AND DATE(saletime) = DATE_ADD(DATE(NOW()), INTERVAL -2 DAY) GROUP BY d.purchaseid ORDER BY category, purchaseid ASC";
	
	$salesTodayResult = mysql_query($salesToday)
		or handleError($lang['error-loadproductdata'],"Error loading product: " . mysql_error());
		
		
	// Poll totals per category
	$catsToday = "SELECT distinct d.category FROM b_salesdetails d, b_sales s WHERE d.saleid = s.saleid AND DATE(saletime) = DATE_ADD(DATE(NOW()), INTERVAL -2 DAY) ORDER BY category ASC";
	
	$catsTodayResult = mysql_query($catsToday)
		or handleError($lang['error-loadproductdata'],"Error loading product: " . mysql_error());
		
		
	while ($catPoll = mysql_fetch_array($catsTodayResult)) {
		
		$catNo = $catPoll['category'];
		$catArray[] = $catNo;
		
		$TOTsalesToday = "SELECT SUM(d.quantity), SUM(d.amount) FROM b_salesdetails d, b_sales s WHERE d.saleid = s.saleid AND DATE(saletime) = DATE_ADD(DATE(NOW()), INTERVAL -2 DAY) AND d.category = $catNo";
			
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
		

	$repDate = date('d-m-Y',(strtotime ( '-2 day' , strtotime (date('Y-m-d')) ) ));

echo <<<EOD
    <br /><br />
	<h3 class="title">$repDate</h3>
	 <table class='default' id='mainTable'>
	  <thead>
	   <tr>
	    <th>{$lang['global-category']}</th>
	    <th>{$lang['global-product']}</th>
	    <th>{$lang['units']}</th>
	    <th class='right'>{$_SESSION['currencyoperator']}</th>
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
	    
	if ($row2['breed2'] != '') {
		$name = $row2['name'] . " x " . $row['breed2'];
	} else {
		$name = $row2['name'];
	}
	
	
  	// Show total row
	if ($currCategory != $categoryID) {
		
			
			$TOTquantity = ${'TOTquantity' . $currCategory};
			$TOTamount = ${'TOTamount' . $currCategory};
		  	echo "<tr><td class='clickableRow' href='purchase.php?purchaseid={$purchaseid}' style='border-bottom: 2px solid #333;'><strong>${'catName' . $currCategory}</strong></td>";
		  	echo "<td style='border-bottom: 2px solid #333;'><strong>TOTAL</strong></td>";
		  	echo "<td class='clickableRow right' href='purchase.php?purchaseid={$purchaseid}' style='border-bottom: 2px solid #333;'><strong>$TOTquantity u</strong></td>";
		  	echo "<td class='clickableRow right' href='purchase.php?purchaseid={$purchaseid}' style='border-bottom: 2px solid #333;'><strong>$TOTamount {$_SESSION['currencyoperator']}</strong></td></tr>";
		  	$cloop++;
		  	$currCategory = $catArray[$cloop];
	}
	
	// Show product lines	

		
	  	echo "<tr><td class='clickableRow' href='purchase.php?purchaseid={$purchaseid}'>$catName</td>";
	  	echo "<td class='clickableRow' href='purchase.php?purchaseid={$purchaseid}'>$name</td>";
	  	echo "<td class='clickableRow right' href='purchase.php?purchaseid={$purchaseid}'>$quantity u</td>";
	  	echo "<td class='clickableRow right' href='purchase.php?purchaseid={$purchaseid}'>$amount {$_SESSION['currencyoperator']}</td></tr>";
		
			
}

// Show total row for last category

			
			$TOTquantity = ${'TOTquantity' . $currCategory};
			$TOTamount = ${'TOTamount' . $currCategory};
		  	echo "<tr><td class='clickableRow' href='purchase.php?purchaseid={$purchaseid}' style='border-bottom: 2px solid #333;'><strong>${'catName' . $currCategory}</strong></td>";
		  	echo "<td style='border-bottom: 2px solid #333;'><strong>TOTAL</strong></td>";
		  	echo "<td class='clickableRow right' href='purchase.php?purchaseid={$purchaseid}' style='border-bottom: 2px solid #333;'><strong>$TOTquantity u</strong></td>";
		  	echo "<td class='clickableRow right' href='purchase.php?purchaseid={$purchaseid}' style='border-bottom: 2px solid #333;'><strong>$TOTamount {$_SESSION['currencyoperator']}</strong></td></tr>";
		  	$cloop++;
		  	$currCategory = $catArray[$cloop];

		echo "</tbody></table>";


unset($salesToday);
unset($salesTodayResult);
unset($catsToday);
unset($catsTodayResult);
unset($catPoll);
unset($catsTodayResult);
unset($catNo);
unset($catArray);
unset($TOTsalesToday);
unset($TOTsalesTodayResult);
unset($catName);
unset($catName0);
unset($catName1);
unset($catName2);
unset($catName3);
unset($catName4);
unset($catName5);
unset($catName6);
unset($catName7);
unset($catName8);
unset($catName9);
unset($catName10);
unset($catName11);
unset($catName12);
unset($catName13);
unset($catName14);
unset($catName15);
unset($catName16);
unset($catName17);
unset($catName18);
unset($catName19);
unset($categoryDetailsCN);
unset($resultCatCN);
unset($rowCN);
unset($rowTOT);
unset($TOTquantity);
unset($TOTquantity0);
unset($TOTquantity1);
unset($TOTquantity2);
unset($TOTquantity3);
unset($TOTquantity4);
unset($TOTquantity5);
unset($TOTquantity6);
unset($TOTquantity7);
unset($TOTquantity8);
unset($TOTquantity9);
unset($TOTquantity10);
unset($TOTquantity11);
unset($TOTquantity12);
unset($TOTquantity13);
unset($TOTquantity14);
unset($TOTquantity15);
unset($TOTquantity16);
unset($TOTquantity17);
unset($TOTquantity18);
unset($TOTquantity19);
unset($TOTrealQuantity);
unset($TOTrealQuantity0);
unset($TOTrealQuantity1);
unset($TOTrealQuantity2);
unset($TOTrealQuantity3);
unset($TOTrealQuantity4);
unset($TOTrealQuantity5);
unset($TOTrealQuantity6);
unset($TOTrealQuantity7);
unset($TOTrealQuantity8);
unset($TOTrealQuantity9);
unset($TOTrealQuantity10);
unset($TOTrealQuantity11);
unset($TOTrealQuantity12);
unset($TOTrealQuantity13);
unset($TOTrealQuantity14);
unset($TOTrealQuantity15);
unset($TOTrealQuantity16);
unset($TOTrealQuantity17);
unset($TOTrealQuantity18);
unset($TOTrealQuantity19);
unset($TOTamount);
unset($TOTamount0);
unset($TOTamount1);
unset($TOTamount2);
unset($TOTamount3);
unset($TOTamount4);
unset($TOTamount5);
unset($TOTamount6);
unset($TOTamount7);
unset($TOTamount8);
unset($TOTamount9);
unset($TOTamount10);
unset($TOTamount11);
unset($TOTamount12);
unset($TOTamount13);
unset($TOTamount14);
unset($TOTamount15);
unset($TOTamount16);
unset($TOTamount17);
unset($TOTamount18);
unset($TOTamount19);
unset($currCategory);
unset($cloop);
unset($purchase);
unset($purchaseid);
unset($catREAL);
unset($categoryID);
unset($productid);
unset($quantity);
unset($amount);
unset($catName);
unset($purchaseCategory);
unset($queryVar);
unset($prodSelect);
unset($prodJoin);
unset($categoryDetails);
unset($resultCat);
unset($row);
unset($catName);
unset($selectProduct);
unset($productResult);
unset($row2);
unset($name);
unset($currCategory);
unset($catREAL);



	$salesToday = "SELECT d.category, d.purchaseid, d.productid, SUM(d.quantity), SUM(d.amount) FROM b_salesdetails d, b_sales s WHERE d.saleid = s.saleid AND DATE(saletime) = DATE_ADD(DATE(NOW()), INTERVAL -3 DAY) GROUP BY d.purchaseid ORDER BY category, purchaseid ASC";
	
	$salesTodayResult = mysql_query($salesToday)
		or handleError($lang['error-loadproductdata'],"Error loading product: " . mysql_error());
		
		
	// Poll totals per category
	$catsToday = "SELECT distinct d.category FROM b_salesdetails d, b_sales s WHERE d.saleid = s.saleid AND DATE(saletime) = DATE_ADD(DATE(NOW()), INTERVAL -3 DAY) ORDER BY category ASC";
	
	$catsTodayResult = mysql_query($catsToday)
		or handleError($lang['error-loadproductdata'],"Error loading product: " . mysql_error());
		
		
	while ($catPoll = mysql_fetch_array($catsTodayResult)) {
		
		$catNo = $catPoll['category'];
		$catArray[] = $catNo;
		
		$TOTsalesToday = "SELECT SUM(d.quantity), SUM(d.amount) FROM b_salesdetails d, b_sales s WHERE d.saleid = s.saleid AND DATE(saletime) = DATE_ADD(DATE(NOW()), INTERVAL -3 DAY) AND d.category = $catNo";
			
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
		

	$repDate = date('d-m-Y',(strtotime ( '-3 day' , strtotime (date('Y-m-d')) ) ));

echo <<<EOD
    <br /><br />
	<h3 class="title">$repDate</h3>
	 <table class='default' id='mainTable'>
	  <thead>
	   <tr>
	    <th>{$lang['global-category']}</th>
	    <th>{$lang['global-product']}</th>
	    <th>{$lang['units']}</th>
	    <th class='right'>{$_SESSION['currencyoperator']}</th>
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
		  	echo "<td class='clickableRow right' href='purchase.php?purchaseid={$purchaseid}' style='border-bottom: 2px solid #333;'><strong>$TOTamount {$_SESSION['currencyoperator']}</strong></td></tr>";
		  	$cloop++;
		  	$currCategory = $catArray[$cloop];
	}
	
	// Show product lines	

		
	  	echo "<tr><td class='clickableRow' href='purchase.php?purchaseid={$purchaseid}'>$catName</td>";
	  	echo "<td class='clickableRow' href='purchase.php?purchaseid={$purchaseid}'>$name</td>";
	  	echo "<td class='clickableRow right' href='purchase.php?purchaseid={$purchaseid}'>$quantity u</td>";
	  	echo "<td class='clickableRow right' href='purchase.php?purchaseid={$purchaseid}'>$amount {$_SESSION['currencyoperator']}</td></tr>";
		
			
}

// Show total row for last category

			
			$TOTquantity = ${'TOTquantity' . $currCategory};
			$TOTamount = ${'TOTamount' . $currCategory};
		  	echo "<tr><td class='clickableRow' href='purchase.php?purchaseid={$purchaseid}' style='border-bottom: 2px solid #333;'><strong>${'catName' . $currCategory}</strong></td>";
		  	echo "<td style='border-bottom: 2px solid #333;'><strong>TOTAL</strong></td>";
		  	echo "<td class='clickableRow right' href='purchase.php?purchaseid={$purchaseid}' style='border-bottom: 2px solid #333;'><strong>$TOTquantity u</strong></td>";
		  	echo "<td class='clickableRow right' href='purchase.php?purchaseid={$purchaseid}' style='border-bottom: 2px solid #333;'><strong>$TOTamount {$_SESSION['currencyoperator']}</strong></td></tr>";
		  	$cloop++;
		  	$currCategory = $catArray[$cloop];

		echo "</tbody></table>";


