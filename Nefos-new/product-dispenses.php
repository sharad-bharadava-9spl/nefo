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
	
	$salesToday = "SELECT d.category, d.purchaseid, d.productid, SUM(d.quantity), SUM(d.realQuantity), SUM(d.amount) FROM salesdetails d, sales s WHERE d.saleid = s.saleid AND DATE(saletime) = DATE(NOW()) GROUP BY d.category, d.purchaseid, d.productid ORDER BY category, purchaseid ASC";
		try
		{
			$salesTodayResult = $pdo3->prepare("$salesToday");
			$salesTodayResult->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user1: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
	// Poll totals per category
	$catsToday = "SELECT distinct d.category FROM salesdetails d, sales s WHERE d.saleid = s.saleid AND DATE(saletime) = DATE(NOW()) ORDER BY category ASC";
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
		
		$TOTsalesToday = "SELECT SUM(d.quantity), SUM(d.amount), SUM(d.realQuantity) FROM salesdetails d, sales s WHERE d.saleid = s.saleid AND DATE(saletime) = DATE(NOW()) AND d.category = $catNo";
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
			$results = $pdo3->prepare("$categoryDetailsCN");
			$results->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user4: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		while ($rowCN = $results->fetch()) {
				${'catName' . $catNo} = $rowCN['name'];
		}
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
		
	pageStart($lang['title-dispenses'], NULL, $deleteSaleScript, "psales", "sales admin", $lang['global-dispensescaps'], $_SESSION['successMessage'], $_SESSION['errorMessage']);
	
	echo "<center><a href='product-dispenses-total.php' class='cta'>Totales</a></center>";

echo <<<EOD
	<h3 class="title">HOY</h3>
	 <table class='default' id='mainTable'>
	  <thead>
	   <tr>
	    <th>{$lang['global-category']}</th>
	    <th>{$lang['global-product']}</th>
	    <th>{$lang['global-quantity']}</th>
	    <th>{$lang['global-quantity']} real</th>
	    <th>Unidades</th>
	    <th class='right'>&euro;</th>
	   </tr>
	  </thead>
	  <tbody>
EOD;

$currCategory = $catArray[0];
$cloop = 0;

while ($purchase = $salesTodayResult->fetch()) {
	
	$purchaseid = $purchase['purchaseid'];
	$catREAL = $catArray[$cloop];
	$categoryID = $purchase['category'];
	$productid = $purchase['productid'];
	$quantity = $purchase['SUM(d.quantity)'];
	$realQuantity = $purchase['SUM(d.realQuantity)'];
	$amount = $purchase['SUM(d.amount)'];
	
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
				$error = 'Error fetching user5: ' . $e->getMessage();
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
		  	echo "<td class='clickableRow right' href='purchase.php?purchaseid={$purchaseid}' style='border-bottom: 2px solid #333;'><strong>$TOTamount &euro;</strong></td></tr>";
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
		  	echo "<td class='clickableRow right' href='purchase.php?purchaseid={$purchaseid}' style='border-bottom: 2px solid #333;'><strong>$TOTamount &euro;</strong></td></tr>";
		  	$cloop++;
		  	$currCategory = $catArray[$cloop];
		}
	}
	
	// Show product lines	
	if ($categoryID < 3) {
		
		
		
	  	echo "<tr><td class='clickableRow' href='purchase.php?purchaseid={$purchaseid}'>$catName</td>";
	  	echo "<td class='clickableRow' href='purchase.php?purchaseid={$purchaseid}'>$name</td>";
	  	echo "<td class='clickableRow right' href='purchase.php?purchaseid={$purchaseid}'>$quantity g</td>";
	  	echo "<td class='clickableRow right' href='purchase.php?purchaseid={$purchaseid}'>$realQuantity g</td>";
	  	echo "<td></td>";
	  	echo "<td class='clickableRow right' href='purchase.php?purchaseid={$purchaseid}'>$amount &euro;</td></tr>";
	  	
		
	} else {
		
	  	echo "<tr><td class='clickableRow' href='purchase.php?purchaseid={$purchaseid}'>$catName</td>";
	  	echo "<td class='clickableRow' href='purchase.php?purchaseid={$purchaseid}'>$name</td>";
	  	echo "<td></td>";
	  	echo "<td></td>";
	  	echo "<td class='clickableRow right' href='purchase.php?purchaseid={$purchaseid}'>$quantity u</td>";
	  	echo "<td class='clickableRow right' href='purchase.php?purchaseid={$purchaseid}'>$amount &euro;</td></tr>";
		
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
		  	echo "<td class='clickableRow right' href='purchase.php?purchaseid={$purchaseid}' style='border-bottom: 2px solid #333;'><strong>$TOTamount &euro;</strong></td></tr>";
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
		  	echo "<td class='clickableRow right' href='purchase.php?purchaseid={$purchaseid}' style='border-bottom: 2px solid #333;'><strong>$TOTamount &euro;</strong></td></tr>";
		  	$cloop++;
		  	$currCategory = $catArray[$cloop];
		}

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





	$salesToday = "SELECT d.category, d.purchaseid, d.productid, SUM(d.quantity), SUM(d.amount), SUM(d.realQuantity) FROM salesdetails d, sales s WHERE d.saleid = s.saleid AND DATE(saletime) = DATE_ADD(DATE(NOW()), INTERVAL -1 DAY) GROUP BY d.category, d.purchaseid, d.productid ORDER BY category, purchaseid ASC";
		try
		{
			$salesTodayResult = $pdo3->prepare("$salesToday");
			$salesTodayResult->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
		
		
	// Poll totals per category
	$catsToday = "SELECT distinct d.category FROM salesdetails d, sales s WHERE d.saleid = s.saleid AND DATE(saletime) = DATE_ADD(DATE(NOW()), INTERVAL -1 DAY) ORDER BY category ASC";
		try
		{
			$catsTodayResult = $pdo3->prepare("$catsToday");
			$catsTodayResult->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		
		while ($catPoll = $catsTodayResult->fetch()) {
		
		$catNo = $catPoll['category'];
		$catArray[] = $catNo;
		
		$TOTsalesToday = "SELECT SUM(d.quantity), SUM(d.amount), SUM(d.realQuantity) FROM salesdetails d, sales s WHERE d.saleid = s.saleid AND DATE(saletime) = DATE_ADD(DATE(NOW()), INTERVAL -1 DAY) AND d.category = $catNo";
		try
		{
			$TOTsalesTodayResult = $pdo3->prepare("$TOTsalesToday");
			$TOTsalesTodayResult->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
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
			$results = $pdo3->prepare("$categoryDetailsCN");
			$results->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		while ($rowCN = $results->fetch()) {
				${'catName' . $catNo} = $rowCN['name'];
		}
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
		

	$repDate = date('d-m-Y',(strtotime ( '-1 day' , strtotime (date('Y-m-d')) ) ));

echo <<<EOD
    <br /><br />
	<h3 class="title">$repDate</h3>
	 <table class='default' id='mainTable'>
	  <thead>
	   <tr>
	    <th>{$lang['global-category']}</th>
	    <th>{$lang['global-product']}</th>
	    <th>{$lang['global-quantity']}</th>
	    <th>{$lang['global-quantity']} real</th>
	    <th>Unidades</th>
	    <th class='right'>&euro;</th>
	   </tr>
	  </thead>
	  <tbody>
EOD;

$currCategory = $catArray[0];
$cloop = 0;

while ($purchase = $salesTodayResult->fetch()) {
	
	$purchaseid = $purchase['purchaseid'];
	$catREAL = $catArray[$cloop];
	$categoryID = $purchase['category'];
	$productid = $purchase['productid'];
	$quantity = $purchase['SUM(d.quantity)'];
	$realQuantity = $purchase['SUM(d.realQuantity)'];
	$amount = $purchase['SUM(d.amount)'];
	
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
		  	echo "<td class='clickableRow right' href='purchase.php?purchaseid={$purchaseid}' style='border-bottom: 2px solid #333;'><strong>$TOTamount &euro;</strong></td></tr>";
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
		  	echo "<td class='clickableRow right' href='purchase.php?purchaseid={$purchaseid}' style='border-bottom: 2px solid #333;'><strong>$TOTamount &euro;</strong></td></tr>";
		  	$cloop++;
		  	$currCategory = $catArray[$cloop];
		}
	}
	
	// Show product lines	
	if ($categoryID < 3) {
		
		
		
	  	echo "<tr><td class='clickableRow' href='purchase.php?purchaseid={$purchaseid}'>$catName</td>";
	  	echo "<td class='clickableRow' href='purchase.php?purchaseid={$purchaseid}'>$name</td>";
	  	echo "<td class='clickableRow right' href='purchase.php?purchaseid={$purchaseid}'>$quantity g</td>";
	  	echo "<td class='clickableRow right' href='purchase.php?purchaseid={$purchaseid}'>$realQuantity g</td>";
	  	echo "<td></td>";
	  	echo "<td class='clickableRow right' href='purchase.php?purchaseid={$purchaseid}'>$amount &euro;</td></tr>";
	  	
		
	} else {
		
	  	echo "<tr><td class='clickableRow' href='purchase.php?purchaseid={$purchaseid}'>$catName</td>";
	  	echo "<td class='clickableRow' href='purchase.php?purchaseid={$purchaseid}'>$name</td>";
	  	echo "<td></td>";
	  	echo "<td></td>";
	  	echo "<td class='clickableRow right' href='purchase.php?purchaseid={$purchaseid}'>$quantity u</td>";
	  	echo "<td class='clickableRow right' href='purchase.php?purchaseid={$purchaseid}'>$amount &euro;</td></tr>";
		
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
		  	echo "<td class='clickableRow right' href='purchase.php?purchaseid={$purchaseid}' style='border-bottom: 2px solid #333;'><strong>$TOTamount &euro;</strong></td></tr>";
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
		  	echo "<td class='clickableRow right' href='purchase.php?purchaseid={$purchaseid}' style='border-bottom: 2px solid #333;'><strong>$TOTamount &euro;</strong></td></tr>";
		  	$cloop++;
		  	$currCategory = $catArray[$cloop];
		}

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




	$salesToday = "SELECT d.category, d.purchaseid, d.productid, SUM(d.quantity), SUM(d.amount), SUM(d.realQuantity) FROM salesdetails d, sales s WHERE d.saleid = s.saleid AND DATE(saletime) = DATE_ADD(DATE(NOW()), INTERVAL -2 DAY) GROUP BY d.category, d.purchaseid, d.productid ORDER BY category, purchaseid ASC";
		try
		{
			$salesTodayResult = $pdo3->prepare("$salesToday");
			$salesTodayResult->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
		
		
	// Poll totals per category
	$catsToday = "SELECT distinct d.category FROM salesdetails d, sales s WHERE d.saleid = s.saleid AND DATE(saletime) = DATE_ADD(DATE(NOW()), INTERVAL -2 DAY) ORDER BY category ASC";
		try
		{
			$catsTodayResult = $pdo3->prepare("$catsToday");
			$catsTodayResult->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		
		while ($catPoll = $catsTodayResult->fetch()) {
		
		$catNo = $catPoll['category'];
		$catArray[] = $catNo;
		
		$TOTsalesToday = "SELECT SUM(d.quantity), SUM(d.amount), SUM(d.realQuantity) FROM salesdetails d, sales s WHERE d.saleid = s.saleid AND DATE(saletime) = DATE_ADD(DATE(NOW()), INTERVAL -2 DAY) AND d.category = $catNo";
		try
		{
			$TOTsalesTodayResult = $pdo3->prepare("$TOTsalesToday");
			$TOTsalesTodayResult->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
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
			$results = $pdo3->prepare("$categoryDetailsCN");
			$results->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		while ($rowCN = $results->fetch()) {
				${'catName' . $catNo} = $rowCN['name'];
		}
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
		

	$repDate = date('d-m-Y',(strtotime ( '-2 day' , strtotime (date('Y-m-d')) ) ));

echo <<<EOD
    <br /><br />
	<h3 class="title">$repDate</h3>
	 <table class='default' id='mainTable'>
	  <thead>
	   <tr>
	    <th>{$lang['global-category']}</th>
	    <th>{$lang['global-product']}</th>
	    <th>{$lang['global-quantity']}</th>
	    <th>{$lang['global-quantity']} real</th>
	    <th>Unidades</th>
	    <th class='right'>&euro;</th>
	   </tr>
	  </thead>
	  <tbody>
EOD;

$currCategory = $catArray[0];
$cloop = 0;

while ($purchase = $salesTodayResult->fetch()) {
	
	$purchaseid = $purchase['purchaseid'];
	$catREAL = $catArray[$cloop];
	$categoryID = $purchase['category'];
	$productid = $purchase['productid'];
	$quantity = $purchase['SUM(d.quantity)'];
	$realQuantity = $purchase['SUM(d.realQuantity)'];
	$amount = $purchase['SUM(d.amount)'];
	
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
		  	echo "<td class='clickableRow right' href='purchase.php?purchaseid={$purchaseid}' style='border-bottom: 2px solid #333;'><strong>$TOTamount &euro;</strong></td></tr>";
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
		  	echo "<td class='clickableRow right' href='purchase.php?purchaseid={$purchaseid}' style='border-bottom: 2px solid #333;'><strong>$TOTamount &euro;</strong></td></tr>";
		  	$cloop++;
		  	$currCategory = $catArray[$cloop];
		}
	}
	
	// Show product lines	
	if ($categoryID < 3) {
		
		
		
	  	echo "<tr><td class='clickableRow' href='purchase.php?purchaseid={$purchaseid}'>$catName</td>";
	  	echo "<td class='clickableRow' href='purchase.php?purchaseid={$purchaseid}'>$name</td>";
	  	echo "<td class='clickableRow right' href='purchase.php?purchaseid={$purchaseid}'>$quantity g</td>";
	  	echo "<td class='clickableRow right' href='purchase.php?purchaseid={$purchaseid}'>$realQuantity g</td>";
	  	echo "<td></td>";
	  	echo "<td class='clickableRow right' href='purchase.php?purchaseid={$purchaseid}'>$amount &euro;</td></tr>";
	  	
		
	} else {
		
	  	echo "<tr><td class='clickableRow' href='purchase.php?purchaseid={$purchaseid}'>$catName</td>";
	  	echo "<td class='clickableRow' href='purchase.php?purchaseid={$purchaseid}'>$name</td>";
	  	echo "<td></td>";
	  	echo "<td></td>";
	  	echo "<td class='clickableRow right' href='purchase.php?purchaseid={$purchaseid}'>$quantity u</td>";
	  	echo "<td class='clickableRow right' href='purchase.php?purchaseid={$purchaseid}'>$amount &euro;</td></tr>";
		
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
		  	echo "<td class='clickableRow right' href='purchase.php?purchaseid={$purchaseid}' style='border-bottom: 2px solid #333;'><strong>$TOTamount &euro;</strong></td></tr>";
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
		  	echo "<td class='clickableRow right' href='purchase.php?purchaseid={$purchaseid}' style='border-bottom: 2px solid #333;'><strong>$TOTamount &euro;</strong></td></tr>";
		  	$cloop++;
		  	$currCategory = $catArray[$cloop];
		}

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



	$salesToday = "SELECT d.category, d.purchaseid, d.productid, SUM(d.quantity), SUM(d.amount), SUM(d.realQuantity) FROM salesdetails d, sales s WHERE d.saleid = s.saleid AND DATE(saletime) = DATE_ADD(DATE(NOW()), INTERVAL -3 DAY) GROUP BY d.category, d.purchaseid, d.productid ORDER BY category, purchaseid ASC";
		try
		{
			$salesTodayResult = $pdo3->prepare("$salesToday");
			$salesTodayResult->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
		
		
	// Poll totals per category
	$catsToday = "SELECT distinct d.category FROM salesdetails d, sales s WHERE d.saleid = s.saleid AND DATE(saletime) = DATE_ADD(DATE(NOW()), INTERVAL -3 DAY) ORDER BY category ASC";
		try
		{
			$catsTodayResult = $pdo3->prepare("$catsToday");
			$catsTodayResult->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		
		while ($catPoll = $catsTodayResult->fetch()) {
		
		$catNo = $catPoll['category'];
		$catArray[] = $catNo;
		
		$TOTsalesToday = "SELECT SUM(d.quantity), SUM(d.amount), SUM(d.realQuantity) FROM salesdetails d, sales s WHERE d.saleid = s.saleid AND DATE(saletime) = DATE_ADD(DATE(NOW()), INTERVAL -3 DAY) AND d.category = $catNo";
		try
		{
			$TOTsalesTodayResult = $pdo3->prepare("$TOTsalesToday");
			$TOTsalesTodayResult->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
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
			$results = $pdo3->prepare("$categoryDetailsCN");
			$results->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		while ($rowCN = $results->fetch()) {
				${'catName' . $catNo} = $rowCN['name'];
		}
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
		

	$repDate = date('d-m-Y',(strtotime ( '-3 day' , strtotime (date('Y-m-d')) ) ));

echo <<<EOD
    <br /><br />
	<h3 class="title">$repDate</h3>
	 <table class='default' id='mainTable'>
	  <thead>
	   <tr>
	    <th>{$lang['global-category']}</th>
	    <th>{$lang['global-product']}</th>
	    <th>{$lang['global-quantity']}</th>
	    <th>{$lang['global-quantity']} real</th>
	    <th>Unidades</th>
	    <th class='right'>&euro;</th>
	   </tr>
	  </thead>
	  <tbody>
EOD;

$currCategory = $catArray[0];
$cloop = 0;

while ($purchase = $salesTodayResult->fetch()) {
	
	$purchaseid = $purchase['purchaseid'];
	$catREAL = $catArray[$cloop];
	$categoryID = $purchase['category'];
	$productid = $purchase['productid'];
	$quantity = $purchase['SUM(d.quantity)'];
	$realQuantity = $purchase['SUM(d.realQuantity)'];
	$amount = $purchase['SUM(d.amount)'];
	
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
		  	echo "<td class='clickableRow right' href='purchase.php?purchaseid={$purchaseid}' style='border-bottom: 2px solid #333;'><strong>$TOTamount &euro;</strong></td></tr>";
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
		  	echo "<td class='clickableRow right' href='purchase.php?purchaseid={$purchaseid}' style='border-bottom: 2px solid #333;'><strong>$TOTamount &euro;</strong></td></tr>";
		  	$cloop++;
		  	$currCategory = $catArray[$cloop];
		}
	}
	
	// Show product lines	
	if ($categoryID < 3) {
		
		
		
	  	echo "<tr><td class='clickableRow' href='purchase.php?purchaseid={$purchaseid}'>$catName</td>";
	  	echo "<td class='clickableRow' href='purchase.php?purchaseid={$purchaseid}'>$name</td>";
	  	echo "<td class='clickableRow right' href='purchase.php?purchaseid={$purchaseid}'>$quantity g</td>";
	  	echo "<td class='clickableRow right' href='purchase.php?purchaseid={$purchaseid}'>$realQuantity g</td>";
	  	echo "<td></td>";
	  	echo "<td class='clickableRow right' href='purchase.php?purchaseid={$purchaseid}'>$amount &euro;</td></tr>";
	  	
		
	} else {
		
	  	echo "<tr><td class='clickableRow' href='purchase.php?purchaseid={$purchaseid}'>$catName</td>";
	  	echo "<td class='clickableRow' href='purchase.php?purchaseid={$purchaseid}'>$name</td>";
	  	echo "<td></td>";
	  	echo "<td></td>";
	  	echo "<td class='clickableRow right' href='purchase.php?purchaseid={$purchaseid}'>$quantity u</td>";
	  	echo "<td class='clickableRow right' href='purchase.php?purchaseid={$purchaseid}'>$amount &euro;</td></tr>";
		
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
		  	echo "<td class='clickableRow right' href='purchase.php?purchaseid={$purchaseid}' style='border-bottom: 2px solid #333;'><strong>$TOTamount &euro;</strong></td></tr>";
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
		  	echo "<td class='clickableRow right' href='purchase.php?purchaseid={$purchaseid}' style='border-bottom: 2px solid #333;'><strong>$TOTamount &euro;</strong></td></tr>";
		  	$cloop++;
		  	$currCategory = $catArray[$cloop];
		}

		echo "</tbody></table>";

