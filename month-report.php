<?php
	
	require_once 'cOnfig/connection.php';
	require_once 'cOnfig/view.php';
	require_once 'cOnfig/authenticate.php';
	require_once 'cOnfig/languages/common.php';
	
	session_start();
	$accessLevel = '3';
	
	// Authenticate & authorize
	authorizeUser($accessLevel);
	
	$domain = $_SESSION['domain'];
	
	if ($domain == 'choko') {
		$ftcolor = 'fff';
	} else {
		$ftcolor = '444';
	}
	
	getSettings();
			
	
	// If no closing ID is set, we display the list of closing dates
	// Check if new Filter value was submitted, and assign query variable accordingly
	
	// Check if 'entre fechas' was utilised
	if (isset($_POST['untilDate'])) {
		
		$limitVar = "";
		
		$openingTime = date("Y-m-d", strtotime($_POST['fromDate']));
		$closingTime = date("Y-m-d", strtotime($_POST['untilDate']));
		
		$openingTimeView = date("d-m-Y", strtotime($_POST['fromDate']));
		$closingTimeView = date("d-m-Y", strtotime($_POST['untilDate']));
		
		$monthDisp = $openingTimeView . " - " . $closingTimeView;
			
	} else {
		
		$openingTime = date("Y-m-1", strtotime(date("Y-m-d")));
		$closingTime = date("Y-m-t", strtotime(date("Y-m-d")));
		
		$monthDisp = date("F Y", strtotime($closingTime));

		
	}
	
		// Look up todays donations
		$selectDonations = "SELECT SUM(amount) from donations WHERE donatedTo <> 3 AND donationTime BETWEEN '$openingTime' AND '$closingTime'";
		try
		{
			$result = $pdo3->prepare("$selectDonations");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
			$donations = $row['SUM(amount)'];
				
		$selectDonations = "SELECT SUM(amount) from donations WHERE (donatedTo = 2) AND donationTime BETWEEN '$openingTime' AND '$closingTime'";
		try
		{
			$result = $pdo3->prepare("$selectDonations");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
			$donationsbank = $row['SUM(amount)'];
			
		$selectDonations = "SELECT SUM(amount) from donations WHERE (donatedTo < 2 OR donatedTo = 4) AND donationTime BETWEEN '$openingTime' AND '$closingTime'";
		try
		{
			$result = $pdo3->prepare("$selectDonations");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
			$donationscash = $row['SUM(amount)'];
			
		// Look up todays membership fees
		$selectDonations = "SELECT SUM(amountPaid) from memberpayments WHERE paymentdate BETWEEN '$openingTime' AND '$closingTime'";
		try
		{
			$result = $pdo3->prepare("$selectDonations");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
			$membershipFees = $row['SUM(amountPaid)'];
			
		// Look up todays membership fees
		$selectDonations = "SELECT SUM(amountPaid) from memberpayments WHERE (paidTo = 2) AND paymentdate BETWEEN '$openingTime' AND '$closingTime'";
		try
		{
			$result = $pdo3->prepare("$selectDonations");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
			$membershipFeesbank = $row['SUM(amountPaid)'];
			
		// Look up todays membership fees
		$selectDonations = "SELECT SUM(amountPaid) from memberpayments WHERE (paidTo < 2 OR paidTo = 4) AND paymentdate BETWEEN '$openingTime' AND '$closingTime'";
		try
		{
			$result = $pdo3->prepare("$selectDonations");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
			$membershipFeescash = $row['SUM(amountPaid)'];
			
		// Look up todays dispenses
		$selectSalesFlower = "SELECT SUM(d.amount), SUM(d.realQuantity), SUM(d.quantity), SUM(s.units) from sales s, salesdetails d WHERE s.saleid = d.saleid AND s.saletime BETWEEN '$openingTime' AND '$closingTime'";
		try
		{
			$result = $pdo3->prepare("$selectSalesFlower");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
			$sales = $row['SUM(d.amount)'];
			$realQuantity = $row['SUM(d.realQuantity)'];
			$quantity = $row['SUM(d.quantity)'];
					
		// Look up todays unit dispenses
		$selectSalesFlower = "SELECT SUM(units) from sales WHERE saletime BETWEEN '$openingTime' AND '$closingTime'";
		try
		{
			$result = $pdo3->prepare("$selectSalesFlower");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
			$units = $row['SUM(units)'];
			$quantity = $quantity - $units;
			$realQuantityTot = $realQuantity - $units;
			
		// Look up todays dispensary sales
		$selectSalesFlower = "SELECT SUM(d.amount) from sales s, salesdetails d WHERE s.saleid = d.saleid AND s.saletime BETWEEN '$openingTime' AND '$closingTime' AND direct = 2";
		try
		{
			$result = $pdo3->prepare("$selectSalesFlower");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
			$salesbank = $row['SUM(d.amount)'];
					
		// Look up todays dispensary sales
		$selectSalesFlower = "SELECT SUM(d.amount) from sales s, salesdetails d WHERE s.saleid = d.saleid AND s.saletime BETWEEN '$openingTime' AND '$closingTime' AND direct < 2";
		try
		{
			$result = $pdo3->prepare("$selectSalesFlower");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
			$salescash = $row['SUM(d.amount)'];
			
		// Look up todays bar sales
		$selectSalesFlower = "SELECT SUM(d.amount) from b_sales s, b_salesdetails d WHERE s.saleid = d.saleid AND s.saletime BETWEEN '$openingTime' AND '$closingTime' AND direct = 2";
		try
		{
			$result = $pdo3->prepare("$selectSalesFlower");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
			$barsalesbank = $row['SUM(d.amount)'];
					
		// Look up todays bar sales
		$selectSalesFlower = "SELECT SUM(d.amount) from b_sales s, b_salesdetails d WHERE s.saleid = d.saleid AND s.saletime BETWEEN '$openingTime' AND '$closingTime' AND direct < 2";
		try
		{
			$result = $pdo3->prepare("$selectSalesFlower");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
			$barsalescash = $row['SUM(d.amount)'];
			
		
		// Look up closing data from last closing of month
		$selectExpenses = "SELECT closingid FROM closing WHERE closingtime BETWEEN '$openingTime' AND '$closingTime' ORDER BY closingtime DESC LIMIT 1";
		try
		{
			$result = $pdo3->prepare("$selectExpenses");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
			$closingid = $row['closingid'];
			
		// Look up expenses till
		$selectExpenses = "SELECT SUM(amount) FROM expenses WHERE moneysource = 1 AND DATE(registertime) BETWEEN DATE('$openingTime') AND DATE('$closingTime')";
		try
		{
			$result = $pdo3->prepare("$selectExpenses");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
			$expensesTill = $row['SUM(amount)'];
			

		
		// Look up expenses bank
		$selectExpenses = "SELECT SUM(amount) FROM expenses WHERE moneysource = 2 AND DATE(registertime) BETWEEN DATE('$openingTime') AND DATE('$closingTime')";
		try
		{
			$result = $pdo3->prepare("$selectExpenses");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
			$expensesBank = $row['SUM(amount)'];
		
			
		// Look up closing data from last closing of month
		$selectExpenses = "SELECT totCredit FROM closing WHERE closingid = '$closingid'";
		try
		{
			$result = $pdo3->prepare("$selectExpenses");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
			$totCredit = $row['totCredit'];
			
		if ($totCredit != 0) {
			$totCredit = number_format($row['totCredit'],2) . " ".$_SESSION['currencyoperator'];
		} else {
			$totCredit = '';
		}
		
		// Look up dispensary purchases
		$selectPurchases = "SELECT purchaseid, category, productid, purchaseDate, purchasePrice, purchaseQuantity, realQuantity, salesPrice FROM purchases WHERE DATE(purchaseDate) BETWEEN DATE('$openingTime') AND DATE('$closingTime')";
		try
		{
			$results = $pdo3->prepare("$selectPurchases");
			$results->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		while ($pur = $results->fetch()) {
			
			$purchaseid = $pur['purchaseid'];
			$category = $pur['category'];
			$productid = $pur['productid'];
			$purchaseDate = date("d-m-Y", strtotime($pur['purchaseDate']));
			$purchasePrice = $pur['purchasePrice'];
			$purchaseQuantity = $pur['purchaseQuantity'];
			$realQuantity = $pur['realQuantity'];
			$salesPrice = $pur['salesPrice'];
			
					
			$dispPurchased = $dispPurchased + ($purchasePrice * $purchaseQuantity);
			
			$totalPrice = number_format($purchasePrice * $purchaseQuantity,2);
			
			if ($category == 1) {
				
				$catName = $lang['global-flowers'];
				
				// Look up flower
				$selectProduct = "SELECT name, breed2 FROM flower WHERE flowerid = '$productid'";
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
	
		$row = $result->fetch();
					$name = $row['name'];
					$breed2 = $row['breed2'];
				
				if ($breed2 != '') {
					$name = $name . " x " . $breed2;
				}
				
			} else if ($category == 2) {
				
				$catName = $lang['global-extracts'];
				
				// Look up extract
				$selectProduct = "SELECT name FROM extract WHERE extractid = '$productid'";
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
	
		$row = $result->fetch();
					$name = $row['name'];
	
			} else {
				
				// Query to look for category
				$categoryDetailsCN = "SELECT name, type FROM categories WHERE id = '$category'";
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
	
		$rowCN = $result->fetch();
					$catName = $rowCN['name'];
					$type = $rowCN['type'];
					
				// Look up product
				$selectProduct = "SELECT name FROM products WHERE productid = '$productid'";
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
	
		$row = $result->fetch();
					$name = $row['name'];
				
			}
			
			if ($category < 3 || $type == 1) {
				
				$dispTable .= <<<EOD
 <tr>
  <td class='left'>$purchaseDate</td>
  <td class='left'>$catName</td>
  <td class='left'>$name</td>
  <td>$totalPrice {$_SESSION['currencyoperator']}</td>
  <td>$purchaseQuantity g.</td>
  <td></td>
 </tr>
			
EOD;

			} else {
				
				$dispTable .= <<<EOD
 <tr>
  <td class='left'>$purchaseDate</td>
  <td class='left'>$catName</td>
  <td class='left'>$name</td>
  <td>$totalPrice {$_SESSION['currencyoperator']}</td>
  <td></td>
  <td>$purchaseQuantity u.</td>
 </tr>
			
EOD;

			}
			
			
		}
		
					
		// Look up dispensary reloads
		$selectReloads = "SELECT SUM(price) FROM productmovements WHERE movementTypeid = 1 AND DATE(movementtime) BETWEEN DATE('$openingTime') AND DATE('$closingTime')";
		try
		{
			$result = $pdo3->prepare("$selectReloads");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
			$reloadsDispensary = $row['SUM(price)'];
			
		$selectReloads = "SELECT movementtime, purchaseid, quantity, price, paid FROM productmovements WHERE movementTypeid = 1 AND DATE(movementtime) BETWEEN DATE('$openingTime') AND DATE('$closingTime')";
		try
		{
			$results = $pdo3->prepare("$selectReloads");
			$results->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		while ($pur = $results->fetch()) {
			
			$purchaseDate = date("d-m-Y", strtotime($pur['movementtime']));
			$purchaseid = $pur['purchaseid'];
			$purchaseQuantity = $pur['quantity'];
			$totalPrice = $pur['price'];
			$paid = $pur['paid'];
			
			$selectProdName = "SELECT category, productid FROM purchases WHERE purchaseid = $purchaseid";
			try
			{
				$result = $pdo3->prepare("$selectProdName");
				$result->execute();
			}
			catch (PDOException $e)
			{
					$error = 'Error fetching user: ' . $e->getMessage();
					echo $error;
					exit();
			}
		
			$rowp = $result->fetch();
				$category = $rowp['category'];
				$productid = $rowp['productid'];
				
			if ($category == 1) {
				
				$catName = $lang['global-flowers'];
				
				// Look up flower
				$selectProduct = "SELECT name, breed2 FROM flower WHERE flowerid = '$productid'";
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
	
		$row = $result->fetch();
					$name = $row['name'];
					$breed2 = $row['breed2'];
				
				if ($breed2 != '') {
					$name = $name . " x " . $breed2;
				}
				
			} else if ($category == 2) {
				
				$catName = $lang['global-extracts'];
				
				// Look up extract
				$selectProduct = "SELECT name FROM extract WHERE extractid = '$productid'";
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
	
		$row = $result->fetch();
					$name = $row['name'];
	
			} else {
				
				// Query to look for category
				$categoryDetailsCN = "SELECT name, type FROM categories WHERE id = '$category'";
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
	
		$rowCN = $result->fetch();
					$catName = $rowCN['name'];
					$type = $rowCN['type'];
					
				// Look up product
				$selectProduct = "SELECT name FROM products WHERE productid = '$productid'";
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
	
		$row = $result->fetch();
					$name = $row['name'];
				
			}
			
			if ($category < 3 || $type == 1) {
				
				$dispReloads .= <<<EOD
 <tr>
  <td class='left'>$purchaseDate</td>
  <td class='left'>$catName</td>
  <td class='left'>$name</td>
  <td>$totalPrice {$_SESSION['currencyoperator']}</td>
  <td>$paid {$_SESSION['currencyoperator']}</td>
  <td>$purchaseQuantity g.</td>
  <td></td>
 </tr>
			
EOD;

			} else {
				
				$dispReloads .= <<<EOD
 <tr>
  <td class='left'>$purchaseDate</td>
  <td class='left'>$catName</td>
  <td class='left'>$name</td>
  <td>$totalPrice {$_SESSION['currencyoperator']}</td>
  <td>$paid {$_SESSION['currencyoperator']}</td>
  <td></td>
  <td>$purchaseQuantity u.</td>
 </tr>
			
EOD;

			}
			
			
		}
		
		// Look up bar reloads
		$selectReloads = "SELECT SUM(price) FROM b_productmovements WHERE movementTypeid = 1 AND DATE(movementtime) BETWEEN DATE('$openingTime') AND DATE('$closingTime')";
		try
		{
			$result = $pdo3->prepare("$selectReloads");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
			$reloadsBar = $row['SUM(price)'];
		
		$selectReloads = "SELECT movementtime, purchaseid, quantity, price, paid FROM b_productmovements WHERE movementTypeid = 1 AND DATE(movementtime) BETWEEN DATE('$openingTime') AND DATE('$closingTime')";
		try
		{
			$results = $pdo3->prepare("$selectReloads");
			$results->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		while ($pur = $results->fetch()) {
			
			$purchaseDate = date("d-m-Y", strtotime($pur['movementtime']));
			$purchaseid = $pur['purchaseid'];
			$purchaseQuantity = $pur['quantity'];
			$totalPrice = $pur['price'];
			$paid = $pur['paid'];
			
			$selectProdName = "SELECT category, productid FROM b_purchases WHERE purchaseid = $purchaseid";
		try
		{
			$result = $pdo3->prepare("$selectProdName");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$rowp = $result->fetch();
				$category = $rowp['category'];
				$productid = $rowp['productid'];
				

				
				// Query to look for category
				$categoryDetailsCN = "SELECT name FROM b_categories WHERE id = '$category'";
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
	
		$rowCN = $result->fetch();
					$catName = $rowCN['name'];
					
				// Look up product
				$selectProduct = "SELECT name FROM b_products WHERE productid = '$productid'";
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
	
		$row = $result->fetch();
					$name = $row['name'];
				
				
				$barReloads .= <<<EOD
 <tr>
  <td class='left'>$purchaseDate</td>
  <td class='left'>$catName</td>
  <td class='left'>$name</td>
  <td>$totalPrice {$_SESSION['currencyoperator']}</td>
  <td>$paid {$_SESSION['currencyoperator']}</td>
  <td>$purchaseQuantity u.</td>
 </tr>
			
EOD;

			
			
		}		
		
		// Look up bar purchases
		$selectPurchases = "SELECT purchaseid, category, productid, purchaseDate, purchasePrice, purchaseQuantity, salesPrice FROM b_purchases WHERE DATE(purchaseDate) BETWEEN DATE('$openingTime') AND DATE('$closingTime')";
		try
		{
			$results = $pdo3->prepare("$selectPurchases");
			$results->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		while ($pur = $results->fetch()) {
			
			$purchaseid = $pur['purchaseid'];
			$category = $pur['category'];
			$productid = $pur['productid'];
			$purchaseDate = $pur['purchaseDate'];
			$purchasePrice = $pur['purchasePrice'];
			$purchaseQuantity = $pur['purchaseQuantity'];
			$salesPrice = $pur['salesPrice'];
			
			$barPurchased = $barPurchased + $purchasePrice * $purchaseQuantity;
			
			$totalPrice = number_format($purchasePrice * $purchaseQuantity,2);
			

				
				// Query to look for category
				$categoryDetailsCN = "SELECT name FROM b_categories WHERE id = '$category'";
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
	
		$rowCN = $result->fetch();
					$catName = $rowCN['name'];
					
				// Look up product
				$selectProduct = "SELECT name FROM b_products WHERE productid = '$productid'";
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
	
		$row = $result->fetch();
					$name = $row['name'];
							
				
				$barTable .= <<<EOD
 <tr>
  <td class='left'>$purchaseDate</td>
  <td class='left'>$catName</td>
  <td class='left'>$name</td>
  <td>$totalPrice {$_SESSION['currencyoperator']}</td>
  <td></td>
  <td>$purchaseQuantity u.</td>
 </tr>
			
EOD;

			
		
			
		}
		
		$expTot = $expensesTill + $expensesBank + $dispPurchased + $reloadsDispensary + $reloadsBar + $barPurchased;
		

			
		// Look up stash data
		$selectExpenses = "SELECT intStash, extStash FROM closing WHERE DATE(closingtime) = DATE('$closingTime')";
		try
		{
			$result = $pdo3->prepare("$selectExpenses");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
			$intStash = $row['intStash'];
			$extStash = $row['extStash'];
			$totStash = $intStash + $extStash;
			
		
		$sortScript = <<<EOD
		
var tablesToExcel = (function () {
    var uri = 'data:application/vnd.ms-excel;base64,'
    , template = '<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns="http://www.w3.org/TR/REC-html40"><head><!--[if gte mso 9]><xml><x:ExcelWorkbook><x:ExcelWorksheets>'
    , templateend = '</x:ExcelWorksheets></x:ExcelWorkbook></xml><![endif]--><meta http-equiv="content-type" content="text/plain; charset=UTF-8"/></head>'
    , body = '<body>'
    , tablevar = '<table>{table'
    , tablevarend = '}</table>'
    , bodyend = '</body></html>'
    , worksheet = '<x:ExcelWorksheet><x:Name>'
    , worksheetend = '</x:Name><x:WorksheetOptions><x:DisplayGridlines/></x:WorksheetOptions></x:ExcelWorksheet>'
    , worksheetvar = '{worksheet'
    , worksheetvarend = '}'
    , base64 = function (s) { return window.btoa(unescape(encodeURIComponent(s))) }
    , format = function (s, c) { return s.replace(/{(\w+)}/g, function (m, p) { return c[p]; }) }
    , wstemplate = ''
    , tabletemplate = '';

    return function (table, name, filename) {
        var tables = table;

        for (var i = 0; i < tables.length; ++i) {
            wstemplate += worksheet + worksheetvar + i + worksheetvarend + worksheetend;
            tabletemplate += tablevar + i + tablevarend;
        }

        var allTemplate = template + wstemplate + templateend;
        var allWorksheet = body + tabletemplate + bodyend;
        var allOfIt = allTemplate + allWorksheet;

        var ctx = {};
        for (var j = 0; j < tables.length; ++j) {
            ctx['worksheet' + j] = name[j];
        }

        for (var k = 0; k < tables.length; ++k) {
            var exceltable;
            if (!tables[k].nodeType) exceltable = document.getElementById(tables[k]);
            ctx['table' + k] = exceltable.innerHTML;
        }

        //document.getElementById("dlink").href = uri + base64(format(template, ctx));
        //document.getElementById("dlink").download = filename;
        //document.getElementById("dlink").click();

        window.location.href = uri + base64(format(allOfIt, ctx));

    }
})();

	  $( function() {
	    $( "#datepicker" ).datepicker({
			dateFormat: "dd-mm-yy"
	    });
	  });
	  $( function() {
	    $( "#datepicker2" ).datepicker({
			dateFormat: "dd-mm-yy"
	    });
	  });	    

	    $(document).ready(function() {
		    
			$.tablesorter.addParser({
			  id: 'dates',
			  is: function(s) { return false },
			  format: function(s) {
			    var dateArray = s.split('-');
			    return dateArray[2].substring(0,4) + dateArray[1] + dateArray[0];
			  },
			  type: 'numeric'
			});
			
			$('#t1').tablesorter({
				usNumberFormat: true
			}); 
			$('#t2').tablesorter({
				usNumberFormat: true,
				headers: {
					0: {
						sorter: "dates"
					},
					3: {
						sorter: "currency"
					},
					4: {
						sorter: "currency"
					},
					5: {
						sorter: "currency"
					}
				}			
			}); 
			$('#t3').tablesorter({
				usNumberFormat: true,
				headers: {
					0: {
						sorter: "dates"
					},
					3: {
						sorter: "currency"
					},
					4: {
						sorter: "currency"
					},
					5: {
						sorter: "currency"
					}
				}			
			}); 
			$('#t4').tablesorter({
				usNumberFormat: true
			}); 
			$('#t5').tablesorter({
				usNumberFormat: true
			}); 
		});
EOD;
		
		pageStart($lang['monthly-report'], NULL, $sortScript, "preporting", "daily monthly-report", $lang['monthly-report'], $_SESSION['successMessage'], $_SESSION['errorMessage']);

?>

	<div id="filterbox" style="">
		 <div class="boxcontent">
				
		        <form action='' method='POST'>
		<?php
			if (isset($_POST['fromDate'])) {
				
				echo <<<EOD
				 <input type="text" id="datepicker" name="fromDate"  autocomplete="nope" class="sixDigit defaultinput" value="{$_POST['fromDate']}" />
				 <input type="text" id="datepicker2" name="untilDate" autocomplete="nope" class="sixDigit defaultinput" value="{$_POST['untilDate']}" onchange='this.form.submit()' />
				 <button type="submit" class='cta2' style='display: inline-block; width: 40px;'>OK</button>
		EOD;
				
			} else {
				
				echo <<<EOD
				 <input type="text" id="datepicker" name="fromDate" autocomplete="nope" class="sixDigit defaultinput" value="$openingTime" />
				 <input type="text" id="datepicker2" name="untilDate" autocomplete="nope" class="sixDigit defaultinput" value="$closingTime" onchange='this.form.submit()' />
				 <button type="submit"  class='cta2' style='display: inline-block; width: 40px;'>OK</button>
		EOD;

			}
		?>
		        </form>
		     </div>
		 </div>
		

		     

<br /><br />
<img src="images/excel-new.png" style="cursor: pointer;" onclick="loadExcel();" value="Export to Excel" />
<h3 class="title" style="width: 100%;"><?php echo $monthDisp; ?></h3>
<center>	
<?php
echo <<<EOD
<div class='actionbox-np2'>
<div class='mainboxheader'><img src="images/settings-finances.png" style="margin-bottom: -7px;">&nbsp;&nbsp;Balance</div>
<div class="historybox">
	 <span class="winnerboxheader">{$lang['revenue']}</span><br><br>
 <table class='historytable'>
 <tr>
  <td></td>
  <td><strong>{$lang['cash']}</strong></td>
  <td><strong>{$lang['card']}</strong></td>
  <td><strong>{$lang['global-total']}</strong></td>
 </tr>
 <tr>
  <td style='text-align: left;'>{$lang['global-donations']}</td>
  <td>{$expr(number_format($donationscash,2))} {$_SESSION['currencyoperator']}</td>
  <td>{$expr(number_format($donationsbank,2))} {$_SESSION['currencyoperator']}</td>
  <td>{$expr(number_format($donations,2))} {$_SESSION['currencyoperator']}</td>
 </tr>
 <tr>
  <td style='text-align: left;'>{$lang['memberfees']}</td>
  <td>{$expr(number_format($membershipFeescash,2))} {$_SESSION['currencyoperator']}</td>
  <td>{$expr(number_format($membershipFeesbank,2))} {$_SESSION['currencyoperator']}</td>
  <td>{$expr(number_format($membershipFees,2))} {$_SESSION['currencyoperator']}</td>
 </tr>
EOD;

echo <<<EOD
 <tr>
  <td style='text-align: left;'>{$lang['direct-dispenses']}</td>
  <td>{$expr(number_format($salescash,2))} {$_SESSION['currencyoperator']}</td>
  <td>{$expr(number_format($salesbank,2))} {$_SESSION['currencyoperator']}</td>
  <td>{$expr(number_format($salescash + $salesbank,2))} {$_SESSION['currencyoperator']}</td>
 </tr>
 <tr>
  <td style='text-align: left;'>{$lang['direct-bar-sales']}</td>
  <td>{$expr(number_format($barsalescash,2))} {$_SESSION['currencyoperator']}</td>
  <td>{$expr(number_format($barsalesbank,2))} {$_SESSION['currencyoperator']}</td>
  <td>{$expr(number_format($barsalescash + $barsalesbank,2))} {$_SESSION['currencyoperator']}</td>
 </tr>
EOD;

echo <<<EOD
 <tr>
  <td>&nbsp;</td>
  <td></td>
  <td></td>
  <td></td>
 </tr>
 <tr style='border-top: 3px solid #507c3f; '>
  <td style='text-align: left; color: #4f7e3a; background: #e5ede2;'><strong>{$lang['revenue']}</strong></td>
  <td style ='background: #e5ede2;'><strong>{$expr(number_format($donationscash + $membershipFeescash + $salescash + $barsalescash,2))} {$_SESSION['currencyoperator']}</strong></td>
  <td style ='background: #e5ede2;'><strong>{$expr(number_format($donationsbank + $membershipFeesbank + $salesbank + $barsalesbank,2))} {$_SESSION['currencyoperator']}</strong></td>
  <td style='color:#4f7e3a; background: #e5ede2;'><strong>{$expr(number_format($donations + $membershipFees + $salescash + $salesbank + $barsalescash + $barsalesbank,2))} {$_SESSION['currencyoperator']}</strong></td>
 </tr>


</table>
</div>
<div class="historybox">
<span class="winnerboxheader">{$lang['global-expenses']}</span><br><br>
<table class='historytable'  id='t1'>

 <tr>
  <td></td>
  <td class='centered'><strong>{$lang['cash']}</strong></td>
  <td class='centered'><strong>{$lang['card']}</strong></td>
  <td class='centered'><strong>{$lang['global-total']}</strong></td>
 </tr>
 <tr>
  <td style='text-align: left;'>{$lang['global-expenses']}</td>
  <td>{$expr(number_format($expensesTill,2))} {$_SESSION['currencyoperator']}</td>
  <td>{$expr(number_format($expensesBank,2))} {$_SESSION['currencyoperator']}</td>
  <td>{$expr(number_format($expensesTill + $expensesBank,2))} {$_SESSION['currencyoperator']}</td>
 </tr>
 <tr>
  <td style='text-align: left;'>{$lang['product-purchases-dispensary']}</td>
  <td></td>
  <td></td>
  <td>{$expr(number_format($dispPurchased,2))} {$_SESSION['currencyoperator']}</td>
 </tr>
 <tr>
  <td style='text-align: left;'>{$lang['reloads-dispensary']}</td>
  <td></td>
  <td></td>
  <td>{$expr(number_format($reloadsDispensary,2))} {$_SESSION['currencyoperator']}</td>
 </tr>
 <tr>
  <td style='text-align: left;'>{$lang['product-purchases-bar']}</td>
  <td></td>
  <td></td>
  <td>{$expr(number_format($barPurchased,2))} {$_SESSION['currencyoperator']}</td>
 </tr>
 <tr>
  <td style='text-align: left;'>{$lang['reloads-bar']}</td>
  <td></td>
  <td></td>
  <td>{$expr(number_format($reloadsBar,2))} {$_SESSION['currencyoperator']}</td>
 </tr>
 <tr style='border-top: 3px solid #ce5856;'>
  <td style='text-align: left; background: #ffcccb;'><strong>{$lang['total-expenses']}</strong></td>
  <td style='background: #ffcccb;'></td>
  <td style='background: #ffcccb;'></td>
  <td style='color:#cd584f; background: #ffcccb;'><strong>{$expr(number_format($expTot,2))} {$_SESSION['currencyoperator']}</strong></td>
 </tr>

 </table>

 </div>
  <br>
  <span class='profit_total'><strong style='font-size: 21px; text-transform: uppercase;'>{$lang['profit']}</strong>&nbsp;&nbsp;  &nbsp; <strong style='color:#4f7e3a;'>+&nbsp; {$expr(number_format($donations + $membershipFees + $salescash + $barsalescash + $salesbank + $barsalesbank - $expTot,2))} {$_SESSION['currencyoperator']}</strong></span>
 </div>

<br /><br />

 <div class='actionbox-np2'>
 <div class='mainboxheader'><img src="images/settings-dispensary.png" style="margin-bottom: -7px;">&nbsp;&nbsp;{$lang['global-dispensary']}</div>
<div class="historybox">
<table id='t2' class='historytable default'>
	<span class='winnerboxheader'>{$lang['purchase-details-dispensary']}</span>
 <thead>

  <tr style='cursor: pointer;'>
   <th style='text-align: left;'>{$lang['pur-date']}</th>
   <th style='text-align: left;'>{$lang['global-category']}</th>
   <th style='text-align: left;'>{$lang['global-product']}</th>
   <th style='text-align: left;'>Euro</th>
   <th style='text-align: left;'>{$lang['grams']}</th>
   <th style='text-align: left;'>{$lang['units']}</th>
  </tr>
 </thead>
 <tbody>
$dispTable
 </tbody>
</table>
</div>
<div class="historybox">
<table  id='t4' class='historytable default'>
		<span class='winnerboxheader'>{$lang['reloads-dispensary']}</span>
 <thead>
  <tr style='cursor: pointer;'>
   <th style='text-align: left;'>{$lang['pur-date']}</th>
   <th style='text-align: left;'>{$lang['global-category']}</th>
   <th style='text-align: left;'>{$lang['global-product']}</th>
   <th style='text-align: left;'>Euro</th>
   <th style='text-align: left;'>{$lang['paid']}</th>
   <th style='text-align: left;'>{$lang['grams']}</th>
   <th style='text-align: left;'>{$lang['units']}</th>
  </tr>
 </thead>
 <tbody>
$dispReloads
 </tbody>
</table>
</div>
</div>

<br><br>

<div class='actionbox-np2'>
<div class='mainboxheader'><img src="images/settings-bar.png" style="margin-bottom: -7px;">&nbsp;&nbsp;{$lang['bar']}</div>
<div class="historybox">

<table  id='t3' class='historytable default'>
	<span class='winnerboxheader'>{$lang['purchase-details-bar']}</span>
 <thead>

  <tr style='cursor: pointer;'>
   <th style='text-align: left;'>{$lang['pur-date']}</th>
   <th style='text-align: left;'>{$lang['global-category']}</th>
   <th style='text-align: left;'>{$lang['global-product']}</th>
   <th style='text-align: left;'>Euro</th>
   <th style='text-align: left;'>{$lang['grams']}</th>
   <th style='text-align: left;'>{$lang['units']}</th>
  </tr>
 </thead>
 <tbody>
$barTable
 </tbody>
</table>
</div>


<div class="historybox">
<table  id='t5' class='historytable default'>
	<span class='winnerboxheader'>{$lang['reloads-bar']}</span>
 <thead>

  <tr style='cursor: pointer;'>
   <th style='text-align: left;'>{$lang['pur-date']}</th>
   <th style='text-align: left;'>{$lang['global-category']}</th>
   <th style='text-align: left;'>{$lang['global-product']}</th>
   <th style='text-align: left;'>Euro</th>
   <th style='text-align: left;'>{$lang['paid']}</th>
   <th style='text-align: left;'>{$lang['units']}</th>
  </tr>
 </thead>
 <tbody>
$barReloads
 </tbody>
</table>
</div>
</div>
<br /><br />
 <div class='actionbox-np2'>
<div class='mainboxheader'><img src="images/settings-dispensary.png" style="margin-bottom: -7px;">&nbsp;&nbsp;{$lang['product-dispensed']}</div>
<div class="historybox" style='width:30%'>
<span class='winnerboxheader' style='border: none;'>Resumen</span>
<table class='historytable product_totals' id='t6'>
 <tr>
  <td style='text-align: left;color: #606f5a;'><strong>{$lang['closeday-dispensed']} {$_SESSION['currencyoperator']}</strong></td>
  <td></td>
  <td></td>
  <td><strong>{$expr(number_format($sales,2))} {$_SESSION['currencyoperator']}</strong></td>
 </tr>
 <tr>
  <td style='text-align: left;color: #606f5a;'><strong>{$lang['closeday-dispensed']} g.</strong></td>
  <td></td>
  <td></td>
  <td><strong>{$expr(number_format($quantity,2))} g</strong></td>
 </tr>
 <tr>
  <td style='text-align: left;color: #606f5a;'><strong>{$lang['closeday-dispensed']} g. real</strong></td>
  <td></td>
  <td></td>
  <td><strong>{$expr(number_format($realQuantityTot,2))} g</strong></td>
 </tr>
 <tr>
  <td style='text-align: left;color: #606f5a;'><strong>{$lang['closeday-dispensed']} u.</strong></td>
  <td></td>
  <td></td>
  <td><strong>{$expr(number_format($units,2))} u</strong></td>
 </tr>
</table>
</div>


EOD;
		

	// Look up dispensed last month
	$selectSalesFlower = "SELECT DISTINCT(d.purchaseid), d.productid, d.category FROM salesdetails d, sales s WHERE s.saleid = d.saleid AND s.saletime BETWEEN '$openingTime' AND '$closingTime' ORDER BY d.category ASC";
		try
		{
			$results = $pdo3->prepare("$selectSalesFlower");
			$results->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	

	echo <<<EOD
<div class="historybox" style='width:62%;'>
<span class='winnerboxheader'>{$lang['global-details']}</span>	
<table class='default historytable' id='t7'>
 <thead>
 <tr style='cursor: pointer;'>
  <th style='text-align: left;'>{$lang['global-category']}</th>
  <th style='text-align: left;'>{$lang['global-product']}</th>
  <th style='text-align: left;'>Euro</th>
  <th style='text-align: left;'>{$lang['grams']}</th>
  <th style='text-align: left;'>{$lang['grams']} real</th>
  <th style='text-align: left;'>{$lang['units']}</th>
 </tr>
 </thead>
 <tbody>
EOD;
	
		while ($pur = $results->fetch()) {
		
		
		$purchaseid = $pur['purchaseid'];
		$productid = $pur['productid'];
		
		$selectSalesPurchase = "SELECT SUM(d.amount), SUM(d.quantity), SUM(d.realQuantity), MAX(d.category) from sales s, salesdetails d WHERE s.saleid = d.saleid AND s.saletime BETWEEN '$openingTime' AND '$closingTime' AND d.purchaseid = $purchaseid";
		try
		{
			$result = $pdo3->prepare("$selectSalesPurchase");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
			$category = $row['MAX(d.category)'];
			$salesTot = $row['SUM(d.amount)'];
			$quantitySoldTot = $row['SUM(d.quantity)'];
			$realquantitySoldTot = $row['SUM(d.realQuantity)'];
						
		if ($category == 1) {
			
			$selectProdName = "SELECT name, breed2 FROM flower WHERE flowerid = $productid";
		try
		{
			$result = $pdo3->prepare("$selectProdName");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$prod = $result->fetch();
				$prodName = $prod['name'] . " " . $prod['breed2'];
				
			$selectProdName = "SELECT growType FROM purchases WHERE purchaseid = $purchaseid";
		try
		{
			$result = $pdo3->prepare("$selectProdName");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$prod = $result->fetch();
				$growtype = $prod['growType'];
				
			if ($growtype > 0) {
				
			$selectProdName = "SELECT growtype FROM growtypes WHERE growtypeid = $growtype";
		try
		{
			$result = $pdo3->prepare("$selectProdName");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$prod = $result->fetch();
				$growtype = $prod['growtype'];
				
				$prodName = "$prodName <span class='smallerfont'>($growtype)</span>";
				
			} else {
				
				$prodName = $prodName;
				
			}
				
			$output1 .= <<<EOD
			
 <tr>
  <td style='text-align: left;'>{$lang['global-flower']}</td>
  <td style='text-align: left;'>$prodName</td>
  <td style='text-align: right;'>$salesTot</td>
  <td style='text-align: right;'>$quantitySoldTot</td>
  <td style='text-align: right;'>$realquantitySoldTot</td>
  <td style='text-align: right;'></td>
 </tr>

			
EOD;
		} else if ($category == 2) {
			
			$selectProdName = "SELECT name FROM extract WHERE extractid = $productid";
		try
		{
			$result = $pdo3->prepare("$selectProdName");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$prod = $result->fetch();
				$prodName = $prod['name'];
				
			$output2 .=  <<<EOD
			
 <tr>
  <td style='text-align: left;'>{$lang['global-extract']}</td>
  <td style='text-align: left;'>$prodName</td>
  <td style='text-align: right;'>$salesTot</td>
  <td style='text-align: right;'>$quantitySoldTot</td>
  <td style='text-align: right;'>$realquantitySoldTot</td>
  <td style='text-align: right;'></td>
 </tr>

			
EOD;
		} else {
			
			$selectCAT = "SELECT name, type FROM categories WHERE id = $category";
		try
		{
			$result = $pdo3->prepare("$selectCAT");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$catR = $result->fetch();
				$catName = $catR['name'];
				$type = $catR['type'];
			
			$selectProdName = "SELECT name FROM products WHERE productid = $productid";
		try
		{
			$result = $pdo3->prepare("$selectProdName");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$prod = $result->fetch();
				$prodName = $prod['name'];
				
				
			if ($type == 1) {
				
				//gramcat
				$output3 .= <<<EOD
			
 <tr>
  <td style='text-align: left;'>{$catName}</td>
  <td style='text-align: left;'>$prodName</td>
  <td style='text-align: right;'>$salesTot</td>
  <td style='text-align: right;'>$quantitySoldTot</td>
  <td style='text-align: right;'>$realquantitySoldTot</td>
  <td style='text-align: right;'></td>
 </tr>

			
EOD;
				
			} else {
				
				$output4 .=  <<<EOD
			
 <tr>
  <td style='text-align: left;'>{$catName}</td>
  <td style='text-align: left;'>$prodName</td>
  <td style='text-align: right;'>$salesTot</td>
  <td style='text-align: right;'></td>
  <td style='text-align: right;'></td>
  <td style='text-align: right;'>$realquantitySoldTot</td>
 </tr>

			
EOD;
				
			}
		}
		
	}
	
	echo $output1;
	echo $output2;
	echo $output3;
	echo $output4;
	
	echo "</tbody></table> </div>
		</div>
	</center>";


displayFooter();  ?>
<script type="text/javascript">
	 function loadExcel(){
 			$("#load").show();
 			var fromDate = "<?php echo $_POST['fromDate'] ?>";
 			var untilDate = "<?php echo $_POST['untilDate'] ?>";
       		window.location.href = 'month-excel-report.php?fromDate='+fromDate+'&untilDate='+untilDate;
       		    setTimeout(function () {
			        $("#load").hide();
			    }, 5000);   
       }
</script>
