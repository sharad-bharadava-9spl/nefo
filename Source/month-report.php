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
			$totCredit = number_format($row['totCredit'],2) . "&euro;";
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
  <td>$totalPrice €</td>
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
  <td>$totalPrice €</td>
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
  <td>$totalPrice €</td>
  <td>$paid €</td>
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
  <td>$totalPrice €</td>
  <td>$paid €</td>
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
  <td>$totalPrice €</td>
  <td>$paid €</td>
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
  <td>$totalPrice €</td>
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
		
		pageStart($lang['monthly-report'], NULL, $sortScript, "preporting", "daily", $lang['monthly-report'], $_SESSION['successMessage'], $_SESSION['errorMessage']);
		
	echo <<<EOD
	
	<center><img src="images/excel.png" style="cursor: pointer;" onclick="tablesToExcel(['t1', 't2', 't3', 't4', 't5', 't6', 't7'], ['t1', 't2', 't3', 't4', 't5', 't6', 't7'], 'myfile.xls')" value="Export to Excel" /></center>
EOD;
?>
	 <table class='default' id='cloneTable' style='text-align: left;'>
      <tr class='nonhover'>
       <td colspan='13' style='border-bottom: 0;'>
		<div style='display: inline-block; border: 2px solid #5aa242; padding: 10px;'>
        <form action='' method='POST'>
<?php
	if (isset($_POST['fromDate'])) {
		
		echo <<<EOD
		 <input type="text" id="datepicker" name="fromDate" autocomplete="nope" class="sixDigit" value="{$_POST['fromDate']}" />
		 <input type="text" id="datepicker2" name="untilDate" autocomplete="nope" class="sixDigit" value="{$_POST['untilDate']}" onchange='this.form.submit()' />
		 <button type="submit" style='display: inline-block; width: 40px; height: 27px;'>OK</button>
EOD;
		
	} else {
		
		echo <<<EOD
		 <input type="text" id="datepicker" name="fromDate" autocomplete="nope" class="sixDigit" value="$openingTime" />
		 <input type="text" id="datepicker2" name="untilDate" autocomplete="nope" class="sixDigit" value="$closingTime" onchange='this.form.submit()' />
		 <button type="submit" style='display: inline-block; width: 40px; height: 27px;'>OK</button>
EOD;

	}
?>
        </form>
        </div>
       </td>
      </tr>
     </table>

<br />

<?php
echo <<<EOD
<table style='color: #$ftcolor; text-align: right; font-family: Tahoma, Verdana, sans-serif; font-size: 14px;' id='t1'>
 <tr>
  <td colspan='4' style='color: #5aa242; text-align: left; font-size: 17px; border-bottom: 2px solid #a80082;'><strong>$monthDisp</strong></td>
 </tr>
 <tr>
  <td></td>
  <td><strong>{$lang['cash']}</strong></td>
  <td><strong>{$lang['card']}</strong></td>
  <td><strong>{$lang['global-total']}</strong></td>
 </tr>
 <tr>
  <td style='text-align: left;'>{$lang['global-expenses']}</td>
  <td>{$expr(number_format($expensesTill,2))} &euro;</td>
  <td>{$expr(number_format($expensesBank,2))} &euro;</td>
  <td>{$expr(number_format($expensesTill + $expensesBank,2))} &euro;</td>
 </tr>
 <tr>
  <td style='text-align: left;'>{$lang['product-purchases-dispensary']}</td>
  <td></td>
  <td></td>
  <td>{$expr(number_format($dispPurchased,2))} &euro;</td>
 </tr>
 <tr>
  <td style='text-align: left;'>{$lang['reloads-dispensary']}</td>
  <td></td>
  <td></td>
  <td>{$expr(number_format($reloadsDispensary,2))} &euro;</td>
 </tr>
 <tr>
  <td style='text-align: left;'>{$lang['product-purchases-bar']}</td>
  <td></td>
  <td></td>
  <td>{$expr(number_format($barPurchased,2))} &euro;</td>
 </tr>
 <tr>
  <td style='text-align: left;'>{$lang['reloads-bar']}</td>
  <td></td>
  <td></td>
  <td>{$expr(number_format($reloadsBar,2))} &euro;</td>
 </tr>
 <tr>
  <td style='text-align: left;'><strong>{$lang['total-expenses']}</strong></td>
  <td></td>
  <td></td>
  <td><strong>{$expr(number_format($expTot,2))} &euro;</strong></td>
 </tr>
 <tr>
  <td>&nbsp;</td>
  <td></td>
  <td></td>
  <td></td>
 </tr>
 <tr>
  <td></td>
  <td><strong>{$lang['cash']}</strong></td>
  <td><strong>{$lang['card']}</strong></td>
  <td><strong>{$lang['global-total']}</strong></td>
 </tr>
 <tr>
  <td style='text-align: left;'>{$lang['global-donations']}</td>
  <td>{$expr(number_format($donationscash,2))} &euro;</td>
  <td>{$expr(number_format($donationsbank,2))} &euro;</td>
  <td>{$expr(number_format($donations,2))} &euro;</td>
 </tr>
 <tr>
  <td style='text-align: left;'>{$lang['memberfees']}</td>
  <td>{$expr(number_format($membershipFeescash,2))} &euro;</td>
  <td>{$expr(number_format($membershipFeesbank,2))} &euro;</td>
  <td>{$expr(number_format($membershipFees,2))} &euro;</td>
 </tr>
EOD;

echo <<<EOD
 <tr>
  <td style='text-align: left;'>{$lang['direct-dispenses']}</td>
  <td>{$expr(number_format($salescash,2))} &euro;</td>
  <td>{$expr(number_format($salesbank,2))} &euro;</td>
  <td>{$expr(number_format($salescash + $salesbank,2))} &euro;</td>
 </tr>
 <tr>
  <td style='text-align: left;'>{$lang['direct-bar-sales']}</td>
  <td>{$expr(number_format($barsalescash,2))} &euro;</td>
  <td>{$expr(number_format($barsalesbank,2))} &euro;</td>
  <td>{$expr(number_format($barsalescash + $barsalesbank,2))} &euro;</td>
 </tr>
EOD;

echo <<<EOD
 <tr>
  <td style='text-align: left;'><strong>{$lang['revenue']}</strong></td>
  <td><strong>{$expr(number_format($donationscash + $membershipFeescash + $salescash + $barsalescash,2))} &euro;</strong></td>
  <td><strong>{$expr(number_format($donationsbank + $membershipFeesbank + $salesbank + $barsalesbank,2))} &euro;</strong></td>
  <td><strong>{$expr(number_format($donations + $membershipFees + $salescash + $salesbank + $barsalescash + $barsalesbank,2))} &euro;</strong></td>
 </tr>
 <tr>
  <td>&nbsp;</td>
  <td></td>
  <td></td>
  <td></td>
 </tr>
 <tr>
  <td style='text-align: left;'><strong>{$lang['profit']}</strong></td>
  <td></td>
  <td></td>
  <td><strong>{$expr(number_format($donations + $membershipFees + $salescash + $barsalescash + $salesbank + $barsalesbank - $expTot,2))} &euro;</strong></td>
 </tr>
 <tr>
  <td>&nbsp;</td>
  <td></td>
  <td></td>
  <td></td>
 </tr>
</table>
<br /><br />

<table style='color: #$ftcolor; text-align: right; font-family: Tahoma, Verdana, sans-serif; font-size: 14px; display: inline; margin-right: 10px;' id='t2' class='default'>
 <thead>
  <tr>
   <td colspan='6'><center><h1>{$lang['purchase-details-dispensary']}</h1></center></td>
  </tr>
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
<table style='color: #$ftcolor; text-align: right; font-family: Tahoma, Verdana, sans-serif; font-size: 14px; display: inline; margin-left: 10px;' id='t3' class='default'>
 <thead>
  <tr>
   <td colspan='6'><center><h1>{$lang['purchase-details-bar']}</h1></center></td>
  </tr>
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
<br /><br /><br />

<table style='color: #$ftcolor; text-align: right; font-family: Tahoma, Verdana, sans-serif; font-size: 14px; display: inline; margin-right: 10px;' id='t4' class='default'>
 <thead>
  <tr>
   <td colspan='7'><center><h1>{$lang['reloads-dispensary']}</h1></center></td>
  </tr>
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
<table style='color: #$ftcolor; text-align: right; font-family: Tahoma, Verdana, sans-serif; font-size: 14px; display: inline; margin-left: 10px;' id='t5' class='default'>
 <thead>
  <tr>
   <td colspan='7'><center><h1>{$lang['reloads-bar']}</h1></center></td>
  </tr>
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

<br /><br /><h1>{$lang['product-dispensed']}</h1>

<table style='color: #$ftcolor; text-align: right; font-family: Tahoma, Verdana, sans-serif; font-size: 14px;' id='t6'>
 <tr>
  <td style='text-align: left;'>{$lang['closeday-dispensed']} &euro;</td>
  <td></td>
  <td></td>
  <td>{$expr(number_format($sales,2))} &euro;</td>
 </tr>
 <tr>
  <td style='text-align: left;'>{$lang['closeday-dispensed']} g.</td>
  <td></td>
  <td></td>
  <td>{$expr(number_format($quantity,2))} g</td>
 </tr>
 <tr>
  <td style='text-align: left;'>{$lang['closeday-dispensed']} g. real</td>
  <td></td>
  <td></td>
  <td>{$expr(number_format($realQuantityTot,2))} g</td>
 </tr>
 <tr>
  <td style='text-align: left;'>{$lang['closeday-dispensed']} u.</td>
  <td></td>
  <td></td>
  <td>{$expr(number_format($units,2))} u</td>
 </tr>
</table><br />


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
<table style='color: #$ftcolor; text-align: right; font-family: Tahoma, Verdana, sans-serif; font-size: 14px;' class='default' id='t7'>
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
	
	echo "</tbody></table>";


displayFooter();