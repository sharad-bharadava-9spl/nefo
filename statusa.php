<?php
	
	require_once 'cOnfig/connection.php';
	require_once 'cOnfig/view.php';
	require_once 'cOnfig/authenticate.php';
	require_once 'cOnfig/languages/common.php';
	
	session_start();
	$accessLevel = '1';
	
	// Authenticate & authorize
	authorizeUser($accessLevel);
	
	getSettings();
	
if ($_SESSION['realWeight'] == 1) {
	
	// Total members today
	$selectMembers = "SELECT COUNT(memberno) from users WHERE memberno <> '0' AND userGroup < 6";
		try
		{
			$result = $pdo3->prepare("$selectMembers");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
		$currentmembers = $row['COUNT(memberno)'];

	// Active members today
	$selectMembers = "SELECT COUNT(memberno) from users WHERE (userGroup BETWEEN '1' AND '4') OR (userGroup = 5 AND (DATE(paidUntil) >= DATE(NOW()) OR exento = 1))";
		try
		{
			$result = $pdo3->prepare("$selectMembers");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
		$activemembers = $row['COUNT(memberno)'];
	
	
	
	
	// New members today
	$newMembers = "SELECT COUNT(user_id) FROM users where DATE(registeredSince) = DATE(NOW())";
		try
		{
			$result = $pdo3->prepare("$newMembers");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
		$newmembers = $row['COUNT(user_id)'];

	// Banned members today
	$bannedmembers = "SELECT COUNT(user_id) FROM users where DATE(banTime) = DATE(NOW())";
		try
		{
			$result = $pdo3->prepare("$bannedmembers");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
		$bannedmembers = $row['COUNT(user_id)'];
		
	// Deleted members today
	$deletedmembers = "SELECT COUNT(user_id) FROM users where DATE(deleteTime) = DATE(NOW())";
		try
		{
			$result = $pdo3->prepare("$deletedmembers");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
		$deletedmembers = $row['COUNT(user_id)'];
		
		
	// Look up expired members today
	$selectMembers = "SELECT COUNT(memberno) FROM users WHERE DATE(paidUntil) = DATE_ADD(DATE(NOW()), INTERVAL -1 DAY) AND exento = 0";
		try
		{
			$result = $pdo3->prepare("$selectMembers");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
		$expiredmembers = $row['COUNT(memberno)'];
	
	// Look up renewed members		
	$selectMembers = "SELECT COUNT(m.paymentid) FROM memberpayments m, users u WHERE u.user_id = m.userid AND DATE(m.paymentdate) = DATE(NOW()) AND DATE(u.registeredSince) < DATE(NOW())";
		try
		{
			$result = $pdo3->prepare("$selectMembers");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
		$renewedMembers = $row['COUNT(m.paymentid)'];
		
		
	// Look up member credit
	$newMembers = "SELECT SUM(credit) FROM users WHERE credit > 0 AND memberno <> '0' AND userGroup < 6 ";
		try
		{
			$result = $pdo3->prepare("$newMembers");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
		$totCredit = $row['SUM(credit)'];

		
	// Look up todays dispenses
	$selectSales = "SELECT SUM(amount), SUM(quantity), SUM(realQuantity), SUM(units) from sales WHERE DATE(saletime) = DATE(NOW())";
		try
		{
			$result = $pdo3->prepare("$selectSales");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
		$salesToday = $row['SUM(amount)'];
		$quantitySold = $row['SUM(quantity)'];
		$realquantitySold = $row['SUM(realQuantity)'];
		$unitsSold = $row['SUM(units)'];
		
	// Look up todays dispenses by category 1
	$selectSalesFlower = "SELECT SUM(d.amount), SUM(d.quantity), SUM(d.realQuantity) from sales s, salesdetails d WHERE s.saleid = d.saleid AND DATE(s.saletime) = DATE(NOW()) AND d.category = 1";
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
		$salesTodayFlower = $row['SUM(d.amount)'];
		$quantitySoldFlower = $row['SUM(d.quantity)'];
		$realquantitySoldFlower = $row['SUM(d.realQuantity)'];
		
	$flowerSalesPercentageToday = ($salesTodayFlower / $salesToday) * 100;
	$flowerGramsPercentageToday = ($quantitySoldFlower / $quantitySold) * 100;
	
	// Look up todays dispenses by category 2
	$selectSalesExtract = "SELECT SUM(d.amount), SUM(d.quantity), SUM(d.realQuantity) from sales s, salesdetails d WHERE s.saleid = d.saleid AND DATE(s.saletime) = DATE(NOW()) AND d.category = 2";
		try
		{
			$result = $pdo3->prepare("$selectSalesExtract");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
		$salesTodayExtract = $row['SUM(d.amount)'];
		$quantitySoldExtract = $row['SUM(d.quantity)'];
		$realquantitySoldExtract = $row['SUM(d.realQuantity)'];
		
	$extractSalesPercentageToday = ($salesTodayExtract / $salesToday) * 100;
	$extractGramsPercentageToday = ($quantitySoldExtract / $quantitySold) * 100;
		
	// Look up todays dispenses by non-default categories
	// Another method; Look up which categories have a TYPE 1. Then 'ping' those categories. Much better and faster!
	// Query to look for category
	$categoryDetailsC = "SELECT id, name, type FROM categories WHERE type = 1";
		try
		{
			$results = $pdo3->prepare("$categoryDetailsC");
			$results->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
	$grCatList = '';
	
		while ($rowC = $results->fetch()) {
				
		$catId = $rowC['id'];
		
		$grCatList = $grCatList . $catId . ",";
		
	}
	
	$grCatListfinal = substr($grCatList, 0, -1);
					
	// Look up today's bar sales
	$selectBarSales = "SELECT SUM(amount), SUM(unitsTot) FROM b_sales WHERE DATE(saletime) = DATE(NOW())";
		try
		{
			$result = $pdo3->prepare("$selectBarSales");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
		$barSales = $row['SUM(amount)'];
		$barUnits = $row['SUM(unitsTot)'];

	// Look up todays donations
	$selectDonations = "SELECT SUM(amount), COUNT(donationid) from donations WHERE (donatedTo < 2 OR donatedTo = 4) AND DATE(donationTime) = DATE(NOW())";
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
		$donationsNo = $row['COUNT(donationid)'];
		
	// Look up todays bank donations
	$selectDonations = "SELECT SUM(amount), COUNT(donationid) from donations WHERE donatedTo = 2 AND DATE(donationTime) = DATE(NOW())";
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
		$bankDonations = $row['SUM(amount)'];
		$bankDonationsNo = $row['COUNT(donationid)'];
		
	// Look up today's membership fees
	$selectMembershipFees = "SELECT SUM(amountPaid) FROM memberpayments WHERE (paidTo < 2 OR paidTo = 4) AND DATE(paymentdate) = DATE(NOW())";
		try
		{
			$result = $pdo3->prepare("$selectMembershipFees");
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
		
	// Look up today's membership fees Bank
	$selectMembershipFees = "SELECT SUM(amountPaid) FROM memberpayments WHERE paidTo = 2 AND DATE(paymentdate) = DATE(NOW())";
		try
		{
			$result = $pdo3->prepare("$selectMembershipFees");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
		$membershipfeesBank = $row['SUM(amountPaid)'];
		
		// Look up todays card purchases
		$selectCard = "SELECT SUM(amount) from card_purchase WHERE paidTo = 2 AND DATE(time) = DATE(NOW())";
		try
		{
			$result = $pdo3->prepare("$selectCard");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
		
		$row = $result->fetch();
			$cardBank = $row['SUM(amount)'];
			
		$selectCard = "SELECT SUM(amount) from card_purchase WHERE paidTo = 1 AND DATE(time) = DATE(NOW())";
		try
		{
			$result = $pdo3->prepare("$selectCard");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
		
		$row = $result->fetch();
			$cardCash = $row['SUM(amount)'];


	if ($_SESSION['creditOrDirect'] == 0) {
		
		// Look up dispensed today cash
		$selectSales = "SELECT SUM(amount) from sales WHERE DATE(saletime) = DATE(NOW()) AND direct < 2";
		try
		{
			$result = $pdo3->prepare("$selectSales");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
			$salesTodayCash = $row['SUM(amount)'];
	
		// Look up dispensed today bank
		$selectSales = "SELECT SUM(amount) from sales WHERE DATE(saletime) = DATE(NOW()) AND direct = 2";
		try
		{
			$result = $pdo3->prepare("$selectSales");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
			$salesTodayBank = $row['SUM(amount)'];
		
		// Look up bar sales today cash
		$selectSales = "SELECT SUM(amount) from b_sales WHERE DATE(saletime) = DATE(NOW()) AND direct < 2";
		try
		{
			$result = $pdo3->prepare("$selectSales");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
			$salesTodayBarCash = $row['SUM(amount)'];
	
		// Look up bar sales today bank
		$selectSales = "SELECT SUM(amount) from b_sales WHERE DATE(saletime) = DATE(NOW()) AND direct = 2";
		try
		{
			$result = $pdo3->prepare("$selectSales");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
			$salesTodayBarBank = $row['SUM(amount)'];
		
	}

				
	// Calculate total income
	$totalIncome = $donations + $bankDonations + $membershipFees + $membershipfeesBank + $salesTodayCash + $salesTodayBank + $salesTodayBarCash + $salesTodayBarBank + $cardCash + $cardBank; 
	
	// Look up today's till expenses
	$selectExpenses = "SELECT SUM(amount) FROM expenses WHERE DATE(registertime) = DATE(NOW()) AND moneysource = 1";
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
		$tillExpenses = $row['SUM(amount)'];
		
	// Query to look up today's opening details + till + bank balance
	if ($_SESSION['openAndClose'] > 2) {
		
		$openingLookup = "SELECT openingid, openingtime, tillBalance, bankBalance FROM opening ORDER BY openingtime DESC LIMIT 1";
		try
		{
			$result = $pdo3->prepare("$openingLookup");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
			$tillBalance = $row['tillBalance'];	
			$bankBalance = $row['bankBalance'];
			$openingid = $row['openingid'];
			$openingtime = $row['openingtime'];
		
	} else if ($_SESSION['openAndClose'] == 2) {
		
		$openingLookup = "SELECT closingid, closingtime, cashintill, bankBalance FROM closing WHERE currentClosing = 0 ORDER BY closingtime DESC LIMIT 1";	
		try
		{
			$result = $pdo3->prepare("$openingLookup");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
			$tillBalance = $row['cashintill'];	
			$bankBalance = $row['bankBalance'];
			$openingid = $row['closingid'];
			$openingtime = $row['closingtime'];
			
	}
	
	// Determine if club has not done opening/closing in several days, and if so use variable later down to decide how to calculate stock, dispenses etc.		
	if ($_SESSION['openAndClose'] > 1) {
		
		$cutoffTime = strtotime('-2 day', strtotime(date('Y-m-d H:i')));
		$lastOpenOrClose = strtotime(date('Y-m-d H:i',strtotime($openingtime)));
		
		if ($lastOpenOrClose < $cutoffTime) {

			$noActiveOpening = 'true';
			
			if ($_SESSION['openAndClose'] > 2) {
				
				$_SESSION['errorMessage'] = $lang['no-recent-opening'];
				
			} else {
				
				$_SESSION['errorMessage'] = $lang['no-recent-closing'];
				
			}
		
		} else {
			
			$noActiveOpening = 'false';
			
		}
		
		
	}
	

	
	// Look up today's bank expenses
	$selectExpenses = "SELECT SUM(amount) FROM expenses WHERE DATE(registertime) = DATE(NOW()) AND moneysource = 2";
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
		$bankExpenses = $row['SUM(amount)'];
		
		

		
	// Calculate estimated till	& club balances
	$tillTot = $tillBalance + $donations + $membershipFees + $tillAdditions + $salesTodayCash + $salesTodayBarCash - $tillExpenses;
	$clubBalance = $tillTot + $bankBalance + $salesTodayBank - $bankExpenses;
	

	pageStart($lang['status'], NULL, NULL, "status", "", $lang['status'], $_SESSION['successMessage'], $_SESSION['errorMessage']);
	
	echo <<<EOD
<center>
<div class="statusbox">
 <img src='images/settings-dispensary.png' style='margin-bottom: -7px;' /> {$lang['global-dispensary']}
 <br />
 <br />

<table class='defaultalternate'>
 <tr>
  <td style='text-align: left;'><strong>{$lang['global-total']}</strong></td>
  <td><strong>{$expr(number_format($salesToday,2))} {$_SESSION['currencyoperator']}</strong></td>
  <td></td>
  <td><strong>{$expr(number_format($realquantitySold,2))} g.</strong></td>
  <td><strong>({$expr(number_format($quantitySold,2))} g.)</strong></td>
  <td></td>
  <td><strong>{$expr(number_format($unitsSold,2))} u.</strong></td>
  <td></td>
 </tr>
 <tr>
  <td style='text-align: left;'><em>{$lang['closeday-owflowers']}</em></td>
  <td>{$expr(number_format($salesTodayFlower,2))} {$_SESSION['currencyoperator']}</td>
  <td>{$expr(number_format($flowerSalesPercentageToday,0))}%</td>
  <td>{$expr(number_format($realquantitySoldFlower,2))} g.</td>
  <td>({$expr(number_format($quantitySoldFlower,2))} g.)</td>
  <td>{$expr(number_format($flowerGramsPercentageToday,0))}%</td>
  <td></td>
 </tr>
 <tr>
  <td style='text-align: left;'><em>{$lang['closeday-owextracts']}</em></td>
  <td>{$expr(number_format($salesTodayExtract,2))} {$_SESSION['currencyoperator']}</td>
  <td>{$expr(number_format($extractSalesPercentageToday,0))}%</td>
  <td>{$expr(number_format($realquantitySoldExtract,2))} g.</td>
  <td>({$expr(number_format($quantitySoldExtract,2))} g.)</td>
  <td>{$expr(number_format($extractGramsPercentageToday,0))}%</td>
  <td></td>
  <td></td>
 </tr>
EOD;
 
	// Query to look up categories
	$selectCats = "SELECT id, name, type from categories WHERE id > 2 ORDER by name ASC";
		try
		{
			$results = $pdo3->prepare("$selectCats");
			$results->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	

		$i = 0;
		
		while ($category = $results->fetch()) {
		
		$categoryid = $category['id'];
		$name = $category['name'];
		$type = $category['type'];
		
		// Create more product queries for each category - to be used further down!
		$customProducts .= " UNION ALL SELECT '$categoryid' AS category, pr.productid AS productid, pr.name AS name, p.purchaseid AS purchaseid, '' AS growtype, p.inMenu AS inMenu, p.closedAt AS closedAt, salesPrice AS gramPrice, p.realQuantity FROM products pr, purchases p WHERE p.category = $categoryid AND p.productid = pr.productid AND (p.closedAt IS NULL OR DATE(p.closingDate) >= DATE(NOW()))";
		
		// Look up sales in this cat
		$selectSalesOthers = "SELECT SUM(d.amount), SUM(d.quantity), SUM(d.realQuantity) from sales s, salesdetails d WHERE s.saleid = d.saleid AND DATE(s.saletime) = DATE(NOW()) AND d.category = $categoryid";
		
		// Look up sales in this cat
		try
		{
			$result = $pdo3->prepare("$selectSalesOthers");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
			$salesTodayOthers = $row['SUM(d.amount)'];
			$quantitySoldOthers = $row['SUM(d.quantity)'];
			$quantitySoldOthersReal = $row['SUM(d.realQuantity)'];
			
		if ($type == 0) {
				
			$othersSalesPercentageToday = ($salesTodayOthers / $salesToday) * 100;
			$othersGramsPercentageToday = ($quantitySoldOthers / $unitsSold) * 100;
			
		} else {
				
			$othersSalesPercentageToday = ($salesTodayOthers / $salesToday) * 100;
			$othersGramsPercentageToday = ($quantitySoldOthers / $quantitySold) * 100;
			
		}

		if ($type == 0) {
		$unitCatSummary .=  <<<EOD
 <tr>
  <td style='text-align: left;'><em>{$lang['closeday-ow']} $name</em></td>
  <td>{$expr(number_format($salesTodayOthers,2))} {$_SESSION['currencyoperator']}</td>
  <td>{$expr(number_format($othersSalesPercentageToday,0))}%</td>
  <td></td>
  <td></td>
  <td></td>
  <td>{$expr(number_format($quantitySoldOthers,2))} u.</td>
  <td>{$expr(number_format($othersGramsPercentageToday,0))}%</td>
 </tr>
EOD;
		} else {
			
		$gramCatSummary .=  <<<EOD
 <tr>
  <td style='text-align: left;'><em>{$lang['closeday-ow']} $name</em></td>
  <td>{$expr(number_format($salesTodayOthers,2))} {$_SESSION['currencyoperator']}</td>
  <td>{$expr(number_format($othersSalesPercentageToday,0))}%</td>
  <td>{$expr(number_format($quantitySoldOthersReal,2))} g.</td>
  <td>({$expr(number_format($quantitySoldOthers,2))} g.)</td>
  <td>{$expr(number_format($othersGramsPercentageToday,0))}%</td>
  <td></td>
  <td></td>
 </tr>
EOD;
		}
		
		$i++;

	}
	
		echo $gramCatSummary;
		echo $unitCatSummary;
		

		
		
	// Query to look up categories
	$selectCats = "SELECT id, name from b_categories ORDER by name ASC";
		try
		{
			$results = $pdo3->prepare("$selectCats");
			$results->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	

		$i = 0;
		
		while ($category = $results->fetch()) {
		
		$categoryid = $category['id'];
		$name = $category['name'];
		
		// Look up sales in this cat
		$selectSalesOthers = "SELECT SUM(d.amount), SUM(d.quantity) from b_sales s, b_salesdetails d WHERE s.saleid = d.saleid AND DATE(s.saletime) = DATE(NOW()) AND d.category = $categoryid";
		try
		{
			$result = $pdo3->prepare("$selectSalesOthers");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
		
		$row = $result->fetch();
			$barsalesTodayOthers = $row['SUM(d.amount)'];
			$barquantitySoldOthers = $row['SUM(d.quantity)'];
							
		$barothersSalesPercentageToday = ($barsalesTodayOthers / $barSales) * 100;
		$barothersGramsPercentageToday = ($barquantitySoldOthers / $barUnits) * 100;

		$barCatSummary .=  <<<EOD
 <tr>
  <td style='text-align: left;'><em>{$lang['closeday-ow']} $name</em></td>
  <td>{$expr(number_format($barsalesTodayOthers,2))} {$_SESSION['currencyoperator']}</td>
  <td>{$expr(number_format($barothersSalesPercentageToday,0))}%</td>
  <td></td>
  <td></td>
  <td></td>
  <td>{$expr(number_format($barquantitySoldOthers,2))} u.</td>
  <td>{$expr(number_format($barothersGramsPercentageToday,0))}%</td>
 </tr>
EOD;

		
		$i++;

	}
	
	echo <<<EOD
</table>
</div>
<br /><br />
<div class="statusbox">
 <img src='images/settings-bar.png' style='margin-bottom: -7px;' /> BAR
 <br />
 <br />

<table class='defaultalternate'>
 <tr>
  <td style='text-align: left;'><strong>{$lang['bar']}</strong></td>
  <td><strong>{$expr(number_format($barSales,2))} {$_SESSION['currencyoperator']}</strong></td>
  <td></td>
  <td></td>
  <td></td>
  <td></td>
  <td><strong>{$expr(number_format($barUnits,2))} u.</strong></td>
  <td></td>
 </tr>
EOD;
		echo $barCatSummary;
	echo <<<EOD
</table>
</div>
<br /><br />
<div class="statusbox">
 <img src='images/settings-members.png' style='margin-bottom: -7px;' /> {$lang['index-members']}
 <br />
 <br />

<table class='defaultalternate'>
 <tr>
  <td style='text-align: left;'>{$lang['closeday-newmembers']}</td>
  <td>$newmembers</td>
 </tr>
 <tr>
  <td style='text-align: left;'>{$lang['total-members']}</td>
  <td>$currentmembers</td>
 </tr>
 <tr>
  <td style='text-align: left;'>{$lang['active-members']}</td>
  <td>$activemembers</td>
 </tr>
 <tr>
  <td style='text-align: left;'>{$lang['closeday-renewedmembers']}</td>
  <td>$renewedMembers</td>
 </tr>
 <tr>
  <td style='text-align: left;'>{$lang['expired-members']}</td>
  <td>$expiredmembers</td>
 </tr>
 <tr>
  <td style='text-align: left;'>{$lang['banned-members']}</td>
  <td>$bannedmembers</td>
 </tr>
 <tr>
  <td style='text-align: left;'>{$lang['deleted-members']}</td>
  <td>$deletedmembers</td>
 </tr>
</table>
</div>
<div class="statusbox">
 <img src='images/settings-finances.png' style='margin-bottom: -7px;' /> {$lang['closeday-finances']}
 <br />
 <br />

<table class='defaultalternate'>
 <tr>
  <td style='text-align: left;'>{$lang['closeday-donations-till']}</td>
  <td>{$expr(number_format($donations,2))} {$_SESSION['currencyoperator']}</td>
  <td style='text-align: left;'>($donationsNo)</td>
  <td></td>
  <td></td>
  <td></td>
 </tr>
 <tr>
  <td style='text-align: left;'>{$lang['closeday-donations-bank']}</td>
  <td>{$expr(number_format($bankDonations,2))} {$_SESSION['currencyoperator']}</td>
  <td style='text-align: left;'>($bankDonationsNo)</td>
  <td></td>
  <td></td>
  <td></td>
 </tr>
EOD;
	if ($_SESSION['creditOrDirect'] == 0) {
		
		echo <<<EOD
 <tr>
  <td style='text-align: left;'>{$lang['dispensed-direct-till']}</td>
  <td>{$expr(number_format($salesTodayCash,2))} {$_SESSION['currencyoperator']}</td>
  <td></td>
  <td></td>
  <td></td>
  <td></td>
 </tr>
 <tr>
  <td style='text-align: left;'>{$lang['dispensed-direct-bank']}</td>
  <td>{$expr(number_format($salesTodayBank,2))} {$_SESSION['currencyoperator']}</td>
  <td></td>
  <td></td>
  <td></td>
  <td></td>
 </tr>
 <tr>
  <td style='text-align: left;'>{$lang['direct-bar-sales-till']}</td>
  <td>{$expr(number_format($salesTodayBarCash,2))} {$_SESSION['currencyoperator']}</td>
  <td></td>
  <td></td>
  <td></td>
  <td></td>
 </tr>
 <tr>
  <td style='text-align: left;'>{$lang['direct-bar-sales-bank']}</td>
  <td>{$expr(number_format($salesTodayBarBank,2))} {$_SESSION['currencyoperator']}</td>
  <td></td>
  <td></td>
  <td></td>
  <td></td>
 </tr>
EOD;
	}
	
	echo <<<EOD
 <tr>
  <td style='text-align: left;'>{$lang['closeday-membershipfees-till']}</td>
  <td>{$expr(number_format($membershipFees,2))} {$_SESSION['currencyoperator']}</td>
  <td></td>
  <td></td>
  <td></td>
  <td></td>
 </tr>
 <tr>
  <td style='text-align: left'>{$lang['closeday-membershipfees-bank']}</td>
  <td>{$expr(number_format($membershipfeesBank,2))} {$_SESSION['currencyoperator']}</td>
  <td></td>
  <td></td>
  <td></td>
  <td></td>
 </tr>
 <tr>
  <td style='text-align: left;'>{$lang['chip-sales-cash']}</td>
  <td>{$expr(number_format($cardCash,2))} {$_SESSION['currencyoperator']}</td>
  <td></td>
  <td></td>
  <td></td>
  <td></td>
 </tr>
 <tr>
  <td style='text-align: left; border-bottom: 1px dashed #ababab;'>{$lang['chip-sales-bank']}</td>
  <td style='border-bottom: 1px dashed #ababab;'>{$expr(number_format($cardBank,2))} {$_SESSION['currencyoperator']}</td>
  <td></td>
  <td></td>
  <td></td>
  <td></td>
 </tr>
 <tr>
  <td style='text-align: left; border-bottom: 1px solid #ababab;'>{$lang['closeday-totalincome']}</td>
  <td style='border-bottom: 1px solid #ababab;'>{$expr(number_format($totalIncome,2))} {$_SESSION['currencyoperator']}</td>
  <td></td>
  <td></td>
  <td></td>
  <td></td>
 </tr>
 <tr>
  <td style='text-align: left;'>{$lang['closeday-tillexpenses']}</td>
  <td>{$expr(number_format($tillExpenses,2))} {$_SESSION['currencyoperator']}</td>
  <td></td>
  <td></td>
  <td></td>
  <td></td>
 </tr>
EOD;

		if ($_SESSION['openAndClose'] == 0 || $noActiveOpening == 'true') {
			
	echo <<<EOD
 <tr>
  <td style='text-align: left;'>{$lang['closeday-bankexpenses']}</td>
  <td>{$expr(number_format($bankExpenses,2))} {$_SESSION['currencyoperator']}</td>
  <td></td>
  <td></td>
  <td></td>
  <td></td>
 </tr>
 <tr>
  <td style='text-align: left;'>Saldo de socios</td>
  <td>{$expr(number_format($totCredit,2))} {$_SESSION['currencyoperator']}</td>
  <td></td>
  <td></td>
  <td></td>
  <td></td>
 </tr>
EOD;

		} else {
			
	echo <<<EOD
 <tr>
  <td style='text-align: left;'>{$lang['closeday-tillbalance']}</td>
  <td>{$expr(number_format($tillTot,2))} {$_SESSION['currencyoperator']}</td>
  <td></td>
  <td></td>
  <td></td>
  <td></td>
 </tr>
 <tr>
  <td style='text-align: left;'>{$lang['closeday-bankexpenses']}</td>
  <td>{$expr(number_format($bankExpenses,2))} {$_SESSION['currencyoperator']}</td>
  <td></td>
  <td></td>
  <td></td>
  <td></td>
 </tr>
 <tr>
  <td style='text-align: left;'>Credito de socios</td>
  <td>{$expr(number_format($totCredit,2))} {$_SESSION['currencyoperator']}</td>
  <td></td>
  <td></td>
  <td></td>
  <td></td>
 </tr>
EOD;

		}
/*
	Jars: Opening + added - takeouts - dispensed (check stock.php)
	Remove: w/o shake, Delta

	Select all products who are not closed
	For each product, get:
	V opening weight
	V additions
	V takeouts
	V intstash
	V extstash
	V dispensed
*/

	$selectProducts = "SELECT '1' AS category, g.flowerid AS productid, g.name AS name, p.purchaseid AS purchaseid, p.growType AS growtype, p.inMenu AS inMenu, p.closedAt AS closedAt, salesPrice AS gramPrice, p.realQuantity FROM flower g, purchases p WHERE p.category = 1 AND p.productid = g.flowerid AND (p.closedAt IS NULL OR DATE(p.closingDate) >= DATE(NOW())) UNION ALL SELECT '2' AS category, h.extractid AS productid, h.name AS name, p.purchaseid AS purchaseid, '' AS growtype, p.inMenu AS inMenu, p.closedAt AS closedAt, salesPrice AS gramPrice, p.realQuantity FROM extract h, purchases p WHERE p.category = 2 AND p.productid = h.extractid AND (p.closedAt IS NULL OR DATE(p.closingDate) >= DATE(NOW()))";
	
	
	$selectProducts .= $customProducts;
		try
		{
			$results = $pdo3->prepare("$selectProducts");
			$results->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		

		
	$productDetails = <<<EOD
 <tr>
  <th><span style='font-size: 20px; color: #444;'>{$lang['global-flowerscaps']}</span></th>
  <th><strong>{$lang['closeday-jars']}&nbsp;&nbsp;</strong></th>
  <th><strong>{$lang['closeday-intstash']}&nbsp;&nbsp;</strong></th>
  <th><strong>{$lang['closeday-extstash']}&nbsp;&nbsp;</strong></th>
  <th><strong>{$lang['global-total']}&nbsp;&nbsp;</strong></th>
  <th><strong>{$lang['value']}&nbsp;&nbsp;</strong></th>
  <th><strong>{$lang['closeday-dispensed']}&nbsp;&nbsp;</strong></th>
  <th><strong>Status&nbsp;&nbsp;</strong></th>
 </tr>
EOD;

		
		while ($product = $results->fetch()) {
			
			
			$category = $product['category'];
			$productid = $product['productid'];
			$name = $product['name'];
			$purchaseid = $product['purchaseid'];
			$growtype = $product['growtype'];
			$inMenu = $product['inMenu'];
			$closedAt = $product['closedAt'];
			$gramPrice = $product['gramPrice'];
			
			$noOfProducts++;
			
			$totGramPrice = $totGramPrice + $gramPrice;
			
			
			if ($closedAt != '') {
				$productStatus = "Closed";
			} else if ($inMenu == 0) {
				$productStatus = "Not in menu";
			} else {
				$productStatus = "In menu";
			}
		
			
			if ($category == 1) {
				// Look up growtype
				$growDetails = "SELECT growtype FROM growtypes WHERE growtypeid = '$growtype'";
		try
		{
			$result = $pdo3->prepare("$growDetails");
			$result->execute();
			$data = $result->fetchAll();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
			
		if (!$data) {
					$growtype = '';
				} else {
				
			$row = $data[0];
				$growtype = $row[0];
					$growtype = "(" . $rowGrow['growtype'] . ")";
					
				}
			}

			
			// Determine how to calculate weight and sales:
			if ($_SESSION['openAndClose'] == 0 || $noActiveOpening == 'true') {
				
				// Calculate Stock
				$selectSales = "SELECT SUM(quantity), SUM(realQuantity) FROM salesdetails WHERE purchaseid = $purchaseid";
		try
		{
			$result = $pdo3->prepare("$selectSales");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
					$sales = $row['SUM(quantity)'];
					$realSales = $row['SUM(realQuantity)'];
		
				$selectPermAdditions = "SELECT SUM(quantity) FROM productmovements WHERE purchaseid = $purchaseid AND type = 1 AND (movementTypeid = 1 OR movementTypeid = 3 OR movementTypeid = 10)";
		try
		{
			$result = $pdo3->prepare("$selectPermAdditions");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
						$permAdditions = $row['SUM(quantity)'];
				
				$selectPermRemovals = "SELECT SUM(quantity) FROM productmovements WHERE purchaseid = $purchaseid AND type = 2 AND (movementTypeid = 4 OR movementTypeid = 7 OR movementTypeid = 8 OR movementTypeid = 9 OR movementTypeid = 11 OR movementTypeid = 13 OR movementTypeid = 14 OR movementTypeid = 15 OR movementTypeid = 16)";
		try
		{
			$result = $pdo3->prepare("$selectPermRemovals");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
						$permRemovals = $row['SUM(quantity)'];
						
				// Calculate what's in Internal stash
				$selectStashedInt = "SELECT SUM(quantity) FROM productmovements WHERE purchaseid = $purchaseid AND type = 2 AND (movementTypeid = 5 OR movementTypeid = 18)";
		try
		{
			$result = $pdo3->prepare("$selectStashedInt");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
						$stashedInt = $row['SUM(quantity)'];
					
				$selectUnStashedInt = "SELECT SUM(quantity) FROM productmovements WHERE purchaseid = $purchaseid AND type = 1 AND (movementTypeid = 12 OR movementTypeid = 17)";
		try
		{
			$result = $pdo3->prepare("$selectUnStashedInt");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
						$unStashedInt = $row['SUM(quantity)'];
			
						
					$inStashInt = $stashedInt - $unStashedInt;
					$inStashInt = $inStashInt;
			
			
				// Calculate what's in External stash
				$selectStashedExt = "SELECT SUM(quantity) FROM productmovements WHERE purchaseid = $purchaseid AND type = 2 AND (movementTypeid = 6 OR movementTypeid = 20)";
		try
		{
			$result = $pdo3->prepare("$selectStashedExt");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
						$stashedExt = $row['SUM(quantity)'];
					
				$selectUnStashedExt = "SELECT SUM(quantity) FROM productmovements WHERE purchaseid = $purchaseid AND type = 1 AND (movementTypeid = 2 OR movementTypeid = 19)";
		try
		{
			$result = $pdo3->prepare("$selectUnStashedExt");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
						$unStashedExt = $row['SUM(quantity)'];
		
					
				$inStashExt = $stashedExt - $unStashedExt;
				$inStashExt = $inStashExt;
				
				$inStash = $inStashInt + $inStashExt;
				$jarWeight = $product['realQuantity'] + $permAdditions - $realSales - $permRemovals - $inStash;
				
				$weightTotal = $jarWeight + $inStashInt + $inStashExt;
				$weightPrice = $weightTotal * $gramPrice;
				
				// Look up todays dispenses
				$selectSales = "SELECT SUM(d.quantity), SUM(d.realQuantity) FROM salesdetails d, sales s WHERE DATE(s.saletime) = DATE(NOW()) AND d.saleid = s.saleid AND d.purchaseid = $purchaseid";
		try
		{
			$result = $pdo3->prepare("$selectSales");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
					$soldToday = $row['SUM(d.quantity)'];
					$realSoldToday = $row['SUM(d.realQuantity)'];


			} else {

				// Query to look up today's opening weight
				if ($_SESSION['openAndClose'] > 2) {
					
					$openingLookup = "SELECT d.weight FROM openingdetails d, opening o WHERE d.openingid = $openingid AND d.openingid = o.openingid AND category = $category AND purchaseid = $purchaseid";
					
				} else if ($_SESSION['openAndClose'] == 2) {
			
					$openingLookup = "SELECT d.weight FROM closingdetails d, closing o WHERE d.closingid = $openingid AND d.closingid = o.closingid AND category = $category AND purchaseid = $purchaseid";
					
				}
		try
		{
			$result = $pdo3->prepare("$openingLookup");
			$result->execute();
			$data = $result->fetchAll();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
			
		if (!$data) {
	   				$purchaseLookup = "SELECT realQuantity from purchases where purchaseid = $purchaseid";
					try
					{
						$result = $pdo3->prepare("$purchaseLookup");
						$result->execute();
					}
					catch (PDOException $e)
					{
							$error = 'Error fetching user: ' . $e->getMessage();
							echo $error;
							exit();
					}
				
					
					$row = $result->fetch();
					$openingWeight = $row['0'];
			
		} else {
				$row = $data[0];
					$openingWeight = $row['0'];
			
		}
					
				// Look up todays dispenses
				$selectSales = "SELECT SUM(d.quantity), SUM(d.realQuantity) FROM salesdetails d, sales s WHERE DATE(s.saletime) = DATE(NOW()) AND d.saleid = s.saleid AND d.purchaseid = $purchaseid";
		try
		{
			$result = $pdo3->prepare("$selectSales");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
					$soldToday = $row['SUM(d.quantity)'];
					$realSoldToday = $row['SUM(d.realQuantity)'];
	
				// Look up additions and removals
				$selectAdditions = "SELECT SUM(quantity) FROM productmovements WHERE purchaseid = $purchaseid AND type = 1 AND movementtime > '$openingtime'";
		try
		{
			$result = $pdo3->prepare("$selectAdditions");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
					$addedToday = $row['SUM(quantity)'];
					
				$selectRemovals = "SELECT SUM(quantity) FROM productmovements WHERE purchaseid = $purchaseid AND type = 2 AND movementtime > '$openingtime'";
		try
		{
			$result = $pdo3->prepare("$selectRemovals");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
			
		$row = $result->fetch();
					$takeoutsToday = $row['SUM(quantity)'];
					
					
				// Calculate jar weight:
				$jarWeight = $openingWeight + $addedToday - $takeoutsToday - $realSoldToday;	
				
				
				// Calculate what's in Internal stash
				$selectStashedInt = "SELECT SUM(quantity) FROM productmovements WHERE purchaseid = $purchaseid AND type = 2 AND (movementTypeid = 5 OR movementTypeid = 18)";
		try
		{
			$result = $pdo3->prepare("$selectStashedInt");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
						$stashedInt = $row['SUM(quantity)'];
					
				$selectUnStashedInt = "SELECT SUM(quantity) FROM productmovements WHERE purchaseid = $purchaseid AND type = 1 AND (movementTypeid = 12 OR movementTypeid = 17)";
		try
		{
			$result = $pdo3->prepare("$selectUnStashedInt");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
						$unStashedInt = $row['SUM(quantity)'];
			
						
				$inStashInt = $stashedInt - $unStashedInt;
			
			
				// Calculate what's in External stash
				$selectStashedExt = "SELECT SUM(quantity) FROM productmovements WHERE purchaseid = $purchaseid AND type = 2 AND (movementTypeid = 6 OR movementTypeid = 20)";
		try
		{
			$result = $pdo3->prepare("$selectStashedExt");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
						$stashedExt = $row['SUM(quantity)'];
					
				$selectUnStashedExt = "SELECT SUM(quantity) FROM productmovements WHERE purchaseid = $purchaseid AND type = 1 AND (movementTypeid = 2 OR movementTypeid = 19)";
		try
		{
			$result = $pdo3->prepare("$selectUnStashedExt");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
						$unStashedExt = $row['SUM(quantity)'];
			
						
				$inStashExt = $stashedExt - $unStashedExt;
				
				$weightTotal = $jarWeight + $inStashInt + $inStashExt;
				$weightPrice = $weightTotal * $gramPrice;
				
			}
			
			// Reset Other Cat totals
			$otherTotJar = 0;
			$otherTotIntSt = 0;
			$otherTotExtSt = 0;
			$otherTot = 0;
			$otherSoldToday = 0;

			
	  		// Create totals per category
			if ($category == 1) {
				$flowerTotJar = $flowerTotJar + $jarWeight;
				$flowerTotIntSt = $flowerTotIntSt + $inStashInt;
				$flowerTotExtSt = $flowerTotExtSt + $inStashExt;
				$flowerTot = $flowerTotJar + $flowerTotIntSt + $flowerTotExtSt;
				$flowerSoldToday = $flowerSoldToday + $realSoldToday;
				$flowerWeightPrice = $flowerWeightPrice + $weightPrice;
			} else if ($category == 2) {
				$extractTotJar = $extractTotJar + $jarWeight;
				$extractTotIntSt = $extractTotIntSt + $inStashInt;
				$extractTotExtSt = $extractTotExtSt + $inStashExt;
				$extractTot = $extractTotJar + $extractTotIntSt + $extractTotExtSt;
				$extractSoldToday = $extractSoldToday + $realSoldToday;
				$extractWeightPrice = $extractWeightPrice + $weightPrice;
				
				// Add Extract header
				if ($extractHeader != 'set') {
					$productDetails .= <<<EOD
 <tr>
  <td colspan='8'></td>
 </tr>
 <tr>
  <th><span style='font-size: 20px; color: #444;'>{$lang['global-extractscaps']}</span></th>
  <th><strong>{$lang['closeday-jars']}&nbsp;&nbsp;</strong></th>
  <th><strong>{$lang['closeday-intstash']}&nbsp;&nbsp;</strong></th>
  <th><strong>{$lang['closeday-extstash']}&nbsp;&nbsp;</strong></th>
  <th><strong>{$lang['global-total']}&nbsp;&nbsp;</strong></th>
  <th><strong>{$lang['value']}&nbsp;&nbsp;</strong></th>
  <th><strong>{$lang['closeday-dispensed']}&nbsp;&nbsp;</strong></th>
  <th><strong>Status&nbsp;&nbsp;</strong></th>
 </tr>
EOD;
				$extractHeader = 'set';
				}
				
			} else {
				
				// Query to look up categories
				$selectCats = "SELECT id, name, type from categories WHERE id = $category";
		try
		{
			$result = $pdo3->prepare("$selectCats");
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
		  	    	$catID = $row['id'];
		  	    	$type = $row['type'];
		  	    	
				if (${'otherHeader' . $catID} != 'set') {
					$productDetails .= <<<EOD
 <tr>
  <td colspan='8'></td>
 </tr>
 <tr>
  <th><span style='font-size: 20px; color: #444;'>$catName</span></th>
  <th><strong>Stock&nbsp;&nbsp;</strong></th>
  <th><strong>{$lang['closeday-intstash']}&nbsp;&nbsp;</strong></th>
  <th><strong>{$lang['closeday-extstash']}&nbsp;&nbsp;</strong></th>
  <th><strong>{$lang['global-total']}&nbsp;&nbsp;</strong></th>
  <th><strong>{$lang['value']}&nbsp;&nbsp;</strong></th>
  <th><strong>{$lang['closeday-dispensed']}&nbsp;&nbsp;</strong></th>
  <th><strong>Status&nbsp;&nbsp;</strong></th>
 </tr>
EOD;
				${'otherHeader' . $catID} = 'set';
				}
					
					
					$otherTotals[$catID]['catName'] = $catName;
					$otherTotals[$catID]['categoryType'] = $type;
					$otherTotals[$catID]['otherTotJar'] = $otherTotals[$catID]['otherTotJar'] + $jarWeight;
					$otherTotals[$catID]['otherTotIntSt'] = $otherTotals[$catID]['otherTotIntSt'] + $inStashInt;
					$otherTotals[$catID]['otherTotExtSt'] = $otherTotals[$catID]['otherTotExtSt'] + $inStashExt;
					$otherTotals[$catID]['otherTot'] = $otherTotals[$catID]['otherTotJar'] + $otherTotals[$catID]['otherTotIntSt'] + $otherTotals[$catID]['otherTotExtSt'];
					$otherTotals[$catID]['otherSoldToday'] = $otherTotals[$catID]['otherSoldToday'] + $realSoldToday;
					$otherTotals[$catID]['otherWeightPrice'] = $otherTotals[$catID]['otherWeightPrice'] + $weightPrice;



				}
			
		  	if ($category < 3 || $type == 1) {
				$productDetails .= <<<EOD
 <tr>
  <td style='text-align: left;'>{$name} <span class='smallerfont3'>{$growtype}</span></td>
  <td>{$expr(number_format($jarWeight,2))} g.</td>
  <td>{$expr(number_format($inStashInt,2))} g.</td>
  <td>{$expr(number_format($inStashExt,2))} g.</td>
  <td><strong>{$expr(number_format($weightTotal,2))} g.</strong></td>
  <td>{$expr(number_format($weightPrice,2))} {$_SESSION['currencyoperator']}</td>
  <td>{$expr(number_format($realSoldToday,2))} g.</td>
  <td>{$productStatus}</td>
 </tr>
EOD;

	  		} else {
		  		
				$productDetails .= <<<EOD
 <tr>
  <td style='text-align: left;'>{$name} <span class='smallerfont3'>{$growtype}</span></td>
  <td>{$expr(number_format($jarWeight,2))} u.</td>
  <td>{$expr(number_format($inStashInt,2))} u.</td>
  <td>{$expr(number_format($inStashExt,2))} u.</td>
  <td><strong>{$expr(number_format($weightTotal,2))} u.</strong></td>
  <td>{$expr(number_format($weightPrice,2))} {$_SESSION['currencyoperator']}</td>
  <td>{$expr(number_format($soldToday,2))} u.</td>
  <td>{$productStatus}</td>
 </tr>
EOD;
	  		}
		} // End product loop
		
	foreach($otherTotals as $oTot) {
		
		if ($oTot['categoryType'] == 0) {
		
			$productOvvU .= <<<EOD
		
 <tr>
  <td style='text-align: left;'>{$oTot['catName']}</td>
  <td>{$expr(number_format($oTot['otherTotJar'],2))} u.</td>
  <td>{$expr(number_format($oTot['otherTotIntSt'],2))} u.</td>
  <td>{$expr(number_format($oTot['otherTotExtSt'],2))} u.</td>
  <td><strong>{$expr(number_format($oTot['otherTot'],2))} u.</strong></td>
  <td>{$expr(number_format($oTot['otherWeightPrice'],2))} {$_SESSION['currencyoperator']}</td>
  <td>{$expr(number_format($oTot['otherSoldToday'],2))} u.</td>
 </tr>
EOD;

			$unitsotherTotJar = $unitsotherTotJar + $oTot['otherTotJar'];
			$unitsotherTotIntSt = $unitsotherTotIntSt + $oTot['otherTotIntSt'];
			$unitsotherTotExtSt = $unitsotherTotExtSt + $oTot['otherTotExtSt'];
			$unitsotherTot = $unitsotherTot + $oTot['otherTot'];
			$unitsotherWeightPrice = $unitsotherWeightPrice + $oTot['otherWeightPrice'];
			$unitsotherSoldToday = $unitsotherSoldToday + $oTot['otherSoldToday'];

		} else {
			
			$productOvvG .= <<<EOD
		
 <tr>
  <td style='text-align: left;'>{$oTot['catName']}</td>
  <td>{$expr(number_format($oTot['otherTotJar'],2))} g.</td>
  <td>{$expr(number_format($oTot['otherTotIntSt'],2))} g.</td>
  <td>{$expr(number_format($oTot['otherTotExtSt'],2))} g.</td>
  <td><strong>{$expr(number_format($oTot['otherTot'],2))} g.</strong></td>
  <td>{$expr(number_format($oTot['otherWeightPrice'],2))} {$_SESSION['currencyoperator']}</td>
  <td>{$expr(number_format($oTot['otherSoldToday'],2))} g.</td>
 </tr>
EOD;
			
			$gramsotherTotJar = $gramsotherTotJar + $oTot['otherTotJar'];
			$gramsotherTotIntSt = $gramsotherTotIntSt + $oTot['otherTotIntSt'];
			$gramsotherTotExtSt = $gramsotherTotExtSt + $oTot['otherTotExtSt'];
			$gramsotherTot = $gramsotherTot + $oTot['otherTot'];
			$gramsotherWeightPrice = $gramsotherWeightPrice + $oTot['otherWeightPrice'];
			$gramsotherSoldToday = $gramsotherSoldToday + $oTot['otherSoldToday'];

			
		}
	}


		$fullTotJar = $flowerTotJar + $extractTotJar + $gramsotherTotJar;
		$fullTotIntSt = $flowerTotIntSt + $extractTotIntSt + $gramsotherTotIntSt;
		$fullTotExtSt = $flowerTotExtSt + $extractTotExtSt + $gramsotherTotExtSt;
		$fullTot = $flowerTot + $extractTot + $gramsotherTot;
		$fullSoldToday = $flowerSoldToday + $extractSoldToday + $gramsotherSoldToday;
		$fullWeightPrice = $flowerWeightPrice + $extractWeightPrice + $gramsotherWeightPrice;
		
		$productOverview = <<<EOD
 <tr>
  <th><span style='font-size: 20px; color: #444;'>{$lang['grams']}</span></th>
  <th><strong>{$lang['closeday-jars']}&nbsp;&nbsp;</strong></th>
  <th><strong>{$lang['closeday-intstash']}&nbsp;&nbsp;</strong></th>
  <th><strong>{$lang['closeday-extstash']}&nbsp;&nbsp;</strong></th>
  <th><strong>{$lang['global-total']}&nbsp;&nbsp;</strong></th>
  <th><strong>{$lang['value']}&nbsp;&nbsp;</strong></th>
  <th><strong>{$lang['closeday-dispensed']}&nbsp;&nbsp;</strong></th>
 </tr>
 <tr>
  <td style='text-align: left;'>{$lang['global-flowers']}</td>
  <td>{$expr(number_format($flowerTotJar,2))} g.</td>
  <td>{$expr(number_format($flowerTotIntSt,2))} g.</td>
  <td>{$expr(number_format($flowerTotExtSt,2))} g.</td>
  <td><strong>{$expr(number_format($flowerTot,2))} g.</strong></td>
  <td>{$expr(number_format($flowerWeightPrice,2))} {$_SESSION['currencyoperator']}</td>
  <td>{$expr(number_format($flowerSoldToday,2))} g.</td>
 </tr>
 <tr>
  <td style='text-align: left;'>{$lang['global-extracts']}</td>
  <td>{$expr(number_format($extractTotJar,2))} g.</td>
  <td>{$expr(number_format($extractTotIntSt,2))} g.</td>
  <td>{$expr(number_format($extractTotExtSt,2))} g.</td>
  <td><strong>{$expr(number_format($extractTot,2))} g.</strong></td>
  <td>{$expr(number_format($extractWeightPrice,2))} {$_SESSION['currencyoperator']}</td>
  <td>{$expr(number_format($extractSoldToday,2))} g.</td>
 </tr>
EOD;

		$productOverview .= $productOvvG;
		
		$productOverview .= <<<EOD
 <tr style='border-top: 1px solid #888; border-bottom: 2px solid #888;'>
  <td style='text-align: left;'><strong>TOTAL G</strong></td>
  <td><strong>{$expr(number_format($fullTotJar,2))} g.</strong></td>
  <td><strong>{$expr(number_format($fullTotIntSt,2))} g.</strong></td>
  <td><strong>{$expr(number_format($fullTotExtSt,2))} g.</strong></td>
  <td><strong>{$expr(number_format($fullTot,2))} g.</strong></td>
  <td><strong>{$expr(number_format($fullWeightPrice,2))} {$_SESSION['currencyoperator']}</strong></td>
  <td><strong>{$expr(number_format($fullSoldToday,2))} g.</strong></td>
 </tr>
 <tr>
  <td colspan='7' style='background-color: #fff;'>&nbsp;</td>
 </tr>
 <tr>
  <th><span style='font-size: 20px; color: #444;'>{$lang['units']}</span></th>
  <th><strong>{$lang['closeday-jars']}&nbsp;&nbsp;</strong></th>
  <th><strong>{$lang['closeday-intstash']}&nbsp;&nbsp;</strong></th>
  <th><strong>{$lang['closeday-extstash']}&nbsp;&nbsp;</strong></th>
  <th><strong>{$lang['global-total']}&nbsp;&nbsp;</strong></th>
  <th><strong>{$lang['value']}&nbsp;&nbsp;</strong></th>
  <th><strong>{$lang['closeday-dispensed']}&nbsp;&nbsp;</strong></th>
 </tr>
EOD;

		$productOverview .= $productOvvU;

		$productOverview .= <<<EOD
 <tr style='border-top: 1px solid #888; border-bottom: 2px solid #888;'>
  <td style='text-align: left;'><strong>TOTAL U</strong></td>
  <td><strong>{$expr(number_format($unitsotherTotJar,2))} u.</strong></td>
  <td><strong>{$expr(number_format($unitsotherTotIntSt,2))} u.</strong></td>
  <td><strong>{$expr(number_format($unitsotherTotExtSt,2))} u.</strong></td>
  <td><strong>{$expr(number_format($unitsotherTot,2))} u.</strong></td>
  <td><strong>{$expr(number_format($unitsotherWeightPrice,2))} {$_SESSION['currencyoperator']}</strong></td>
  <td><strong>{$expr(number_format($unitsotherSoldToday,2))} u.</strong></td>
 </tr>
EOD;
		
		
/**********************
		$fullTotJar = $flowerTotJar + $extractTotJar;
		$fullTotIntSt = $flowerTotIntSt + $extractTotIntSt;
		$fullTotExtSt = $flowerTotExtSt + $extractTotExtSt;
		$fullTot = $flowerTot + $extractTot;
		$fullSoldToday = $flowerSoldToday + $extractSoldToday;
		$fullWeightPrice = $flowerWeightPrice + $extractWeightPrice;
				
  
		$productOverview = <<<EOD
 <tr>
  <td colspan='8' style='color: #5aa242; text-align: left; font-size: 17px; border-bottom: 2px solid #a80082;'><strong>{$lang['dispensary-overview']}</strong></td>
 </tr>
 <tr>
  <td></td>
  <td><strong>{$lang['closeday-jars']}&nbsp;&nbsp;</strong></td>
  <td><strong>{$lang['closeday-intstash']}&nbsp;&nbsp;</strong></td>
  <td><strong>{$lang['closeday-extstash']}&nbsp;&nbsp;</strong></td>
  <td><strong>{$lang['global-total']}&nbsp;&nbsp;</strong></td>
  <td><strong>{$lang['value']}&nbsp;&nbsp;</strong></td>
  <td><strong>{$lang['closeday-dispensed']}&nbsp;&nbsp;</strong></td>
 </tr>
 <tr>
  <td style='text-align: left;'>{$lang['global-flowers']}</td>
  <td>{$expr(number_format($flowerTotJar,2))} g.</td>
  <td>{$expr(number_format($flowerTotIntSt,2))} g.</td>
  <td>{$expr(number_format($flowerTotExtSt,2))} g.</td>
  <td><strong>{$expr(number_format($flowerTot,2))} g.</strong></td>
  <td>{$expr(number_format($flowerWeightPrice,2))} {$_SESSION['currencyoperator']}</td>
  <td>{$expr(number_format($flowerSoldToday,2))} g.</td>
 </tr>
 <tr>
  <td style='text-align: left;'>{$lang['global-extracts']}</td>
  <td>{$expr(number_format($extractTotJar,2))} g.</td>
  <td>{$expr(number_format($extractTotIntSt,2))} g.</td>
  <td>{$expr(number_format($extractTotExtSt,2))} g.</td>
  <td><strong>{$expr(number_format($extractTot,2))} g.</strong></td>
  <td>{$expr(number_format($extractWeightPrice,2))} {$_SESSION['currencyoperator']}</td>
  <td>{$expr(number_format($extractSoldToday,2))} g.</td>
 </tr>
 <tr style='border-top: 1px solid #888;'>
  <td style='text-align: left;'><strong>TOTAL</strong></td>
  <td><strong>{$expr(number_format($fullTotJar,2))} g.</strong></td>
  <td><strong>{$expr(number_format($fullTotIntSt,2))} g.</strong></td>
  <td><strong>{$expr(number_format($fullTotExtSt,2))} g.</strong></td>
  <td><strong>{$expr(number_format($fullTot,2))} g.</strong></td>
  <td><strong>{$expr(number_format($fullWeightPrice,2))} {$_SESSION['currencyoperator']}</strong></td>
  <td><strong>{$expr(number_format($fullSoldToday,2))} g.</strong></td>
 </tr>
EOD;

  ********************************/
  
		// Query to look up expenses
	$selectExpenses = "SELECT expenseid, registertime, userid, expensetype, expense, moneysource, other, amount, shop, comment, receipt, expensecategory FROM expenses WHERE DATE(registertime) = DATE(NOW()) ORDER by registertime DESC";
		try
		{
			$resultsX = $pdo3->prepare("$selectExpenses");
			$resultsX->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	

		
		
		$expenseDetails .= <<<EOD
</table>
</div>
<div class='statusbox'>
 <img src='images/settings-finances.png' style='margin-bottom: -7px;' /> {$lang['global-expensescaps']}
 <br />
 <br />

<table class='defaultalternate'>
	   <tr>
	    <th style='text-align: center;'><strong>{$lang['global-time']}</strong></th>
	    <th style='text-align: center;'><strong>{$lang['global-category']}</strong></th>
	    <th style='text-align: center;'><strong>{$lang['global-expense']}</strong></th>
	    <th style='text-align: center;'><strong>{$lang['global-shop']}</strong></th>
	    <th style='text-align: center;'><strong>{$lang['global-member']}</strong></th>
	    <th style='text-align: center;'><strong>{$lang['global-amount']}</strong></th>
	    <th style='text-align: center;'><strong>{$lang['global-source']}</strong></th>
	    <th style='text-align: center;'><strong>{$lang['global-receipt']}?</strong></th>
	   </tr>
EOD;


		while ($expense = $resultsX->fetch()) {
	
	
	$userid = $expense['userid']; // find member
	$moneysource = $expense['moneysource'];
	$receipt = $expense['receipt'];
	$other = $expense['other'];
	$expenseCat = $expense['expensecategory'];
	$formattedDate = date("d M H:i", strtotime($expense['registertime'] . "+$offsetSec seconds"));
	
	if ($expenseCat == NULL) {
		$expenseCat = '';
	} else {
		if ($_SESSION['lang'] == 'es') {
			$selectExpenseCat = "SELECT namees FROM expensecategories WHERE categoryid = $expenseCat";
		try
		{
			$result = $pdo3->prepare("$selectExpenseCat");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
		  	    $expenseCat = $row['namees'];
		} else {
			$selectExpenseCat = "SELECT nameen FROM expensecategories WHERE categoryid = $expenseCat";
		try
		{
			$result = $pdo3->prepare("$selectExpenseCat");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
		  	    $expenseCat = $row['nameen'];
		}
	}
		

	
	if ($moneysource == 1) {
		$source = $lang['global-till'];
	} else if ($moneysource == 2) {
		$source = $lang['global-bank'];
	} else if ($moneysource == 3) {
		$source = $other;
	} else {
		$source = 'ERROR';
	}
	
	if ($receipt == 1) {
		$recClass = "";
		$receipt = $lang['global-yes'];
	} else if ($receipt == 2) {
		$recClass = "negative";
		$receipt = $lang['global-no'];
	}
	
		$userDetails = "SELECT memberno, first_name from users WHERE user_id = $userid";
		try
		{
			$results = $pdo3->prepare("$userDetails");
			$results->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		while ($user = $results->fetch()) {
			$member = "#" . $user['memberno'] . " - " . $user['first_name'];
		}

	
	
	$expense_row =	sprintf("
  	  <tr>
  	   <td class='clickableRow' href='expense.php?expenseid=%d'>%s</td>
  	   <td class='clickableRow' style='text-align: left;' href='expense.php?expenseid=%d'>%s</td>
  	   <td class='clickableRow' style='text-align: left;' href='expense.php?expenseid=%d'>%s</td>
  	   <td class='clickableRow' style='text-align: left;' href='expense.php?expenseid=%d'>%s</td>
  	   <td class='clickableRow' style='text-align: left;' href='expense.php?expenseid=%d'>%s</td>
  	   <td style='text-align: right;' class='clickableRow' href='expense.php?expenseid=%d'>%0.2f <span class='smallerfont'>{$_SESSION['currencyoperator']}</span></td>
  	   <td class='clickableRow' href='expense.php?expenseid=%d'>%s</td>
  	   <td class='clickableRow %s' href='expense.php?expenseid=%d'>%s</td>
	  </tr>",
	  $expense['expenseid'], $formattedDate, $expense['expenseid'], $expenseCat, $expense['expenseid'], $expense['expense'], $expense['expenseid'], $expense['shop'], $expense['expenseid'], $member, $expense['expenseid'], $expense['amount'], $expense['expenseid'], $source, $recClass, $expense['expenseid'], $receipt
	  );
	  $expenseDetails.= $expense_row;
  }
	echo " 
</table>
</div>
<div class='statusbox'>
 <img src='images/settings-dispensary.png' style='margin-bottom: -7px;' /> {$lang['dispensary-overview']}
 <br />
 <br />

<table class='defaultalternate'>";

	  echo $productOverview;
	  
	echo " 
</table>
</div>
<div class='statusbox'>
 <img src='images/settings-dispensary.png' style='margin-bottom: -7px;' /> {$lang['closeday-productdetails']}
 <br />
 <br />

<table class='defaultalternate'>";

	  echo $otherProducts;
	  echo $productDetails;
	  echo "</div><center>";
	  echo $expenseDetails;
	  echo "</table></div>";

	  
	  
	  
	  
	  
	  
	  
	  
	  
	  
	  
	  
	  
	  
	  
	  
	  
	  
	  
	  
	  
	  
	  
	  
	  
	  
	  
	  
} else {
	
	
	
	
	
	
	
	
	
	
		
	// Total members today
	$selectMembers = "SELECT COUNT(memberno) from users WHERE memberno <> '0' AND userGroup < 6";
		try
		{
			$result = $pdo3->prepare("$selectMembers");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
		$currentmembers = $row['COUNT(memberno)'];

	// Active members today
	$selectMembers = "SELECT COUNT(memberno) from users WHERE (userGroup BETWEEN '1' AND '4') OR (userGroup = 5 AND (DATE(paidUntil) >= DATE(NOW()) OR exento = 1))";
		try
		{
			$result = $pdo3->prepare("$selectMembers");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
		$activemembers = $row['COUNT(memberno)'];
	
	
	
	
	// New members today
	$newMembers = "SELECT COUNT(user_id) FROM users where DATE(registeredSince) = DATE(NOW())";
		try
		{
			$result = $pdo3->prepare("$newMembers");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
		$newmembers = $row['COUNT(user_id)'];

	// Banned members today
	$bannedmembers = "SELECT COUNT(user_id) FROM users where DATE(banTime) = DATE(NOW())";
		try
		{
			$result = $pdo3->prepare("$bannedmembers");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
		$bannedmembers = $row['COUNT(user_id)'];
		
	// Deleted members today
	$deletedmembers = "SELECT COUNT(user_id) FROM users where DATE(deleteTime) = DATE(NOW())";
		try
		{
			$result = $pdo3->prepare("$deletedmembers");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
		$deletedmembers = $row['COUNT(user_id)'];
		
		
	// Look up expired members today
	$selectMembers = "SELECT COUNT(memberno) FROM users WHERE DATE(paidUntil) = DATE_ADD(DATE(NOW()), INTERVAL -1 DAY) AND exento = 0";
		try
		{
			$result = $pdo3->prepare("$selectMembers");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
		$expiredmembers = $row['COUNT(memberno)'];
	
	// Look up renewed members		
	$selectMembers = "SELECT COUNT(m.paymentid) FROM memberpayments m, users u WHERE u.user_id = m.userid AND DATE(m.paymentdate) = DATE(NOW()) AND DATE(u.registeredSince) < DATE(NOW())";
		try
		{
			$result = $pdo3->prepare("$selectMembers");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
		$renewedMembers = $row['COUNT(m.paymentid)'];
		
		
	// Look up member credit
	$newMembers = "SELECT SUM(credit) FROM users WHERE credit > 0 AND memberno <> '0' AND userGroup < 6 ";
		try
		{
			$result = $pdo3->prepare("$newMembers");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
		$totCredit = $row['SUM(credit)'];

		
	// Look up todays dispenses
	$selectSales = "SELECT SUM(amount), SUM(quantity), SUM(realQuantity), SUM(units) from sales WHERE DATE(saletime) = DATE(NOW())";
		try
		{
			$result = $pdo3->prepare("$selectSales");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
		$salesToday = $row['SUM(amount)'];
		$quantitySold = $row['SUM(quantity)'];
		$realquantitySold = $row['SUM(realQuantity)'];
		$unitsSold = $row['SUM(units)'];
		
	// Look up todays dispenses by category 1
	$selectSalesFlower = "SELECT SUM(d.amount), SUM(d.quantity), SUM(d.realQuantity) from sales s, salesdetails d WHERE s.saleid = d.saleid AND DATE(s.saletime) = DATE(NOW()) AND d.category = 1";
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
		$salesTodayFlower = $row['SUM(d.amount)'];
		$quantitySoldFlower = $row['SUM(d.quantity)'];
		$realquantitySoldFlower = $row['SUM(d.realQuantity)'];
		
	$flowerSalesPercentageToday = ($salesTodayFlower / $salesToday) * 100;
	$flowerGramsPercentageToday = ($quantitySoldFlower / $quantitySold) * 100;
	
	// Look up todays dispenses by category 2
	$selectSalesExtract = "SELECT SUM(d.amount), SUM(d.quantity), SUM(d.realQuantity) from sales s, salesdetails d WHERE s.saleid = d.saleid AND DATE(s.saletime) = DATE(NOW()) AND d.category = 2";
		try
		{
			$result = $pdo3->prepare("$selectSalesExtract");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
		$salesTodayExtract = $row['SUM(d.amount)'];
		$quantitySoldExtract = $row['SUM(d.quantity)'];
		$realquantitySoldExtract = $row['SUM(d.realQuantity)'];
		
	$extractSalesPercentageToday = ($salesTodayExtract / $salesToday) * 100;
	$extractGramsPercentageToday = ($quantitySoldExtract / $quantitySold) * 100;
		
	// Look up todays dispenses by non-default categories
	// Another method; Look up which categories have a TYPE 1. Then 'ping' those categories. Much better and faster!
	// Query to look for category
	$categoryDetailsC = "SELECT id, name, type FROM categories WHERE type = 1 AND id > 3";
		try
		{
			$results = $pdo3->prepare("$categoryDetailsC");
			$results->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		
	$grCatList = '';
	
		while ($rowC = $results->fetch()) {
				
		$catId = $rowC['id'];
		
		$grCatList = $grCatList . $catId . ",";
		
	}
	
	$grCatListfinal = substr($grCatList, 0, -1);

	
	// Look up today's bar sales
	$selectBarSales = "SELECT SUM(amount), SUM(unitsTot) FROM b_sales WHERE DATE(saletime) = DATE(NOW())";
		try
		{
			$result = $pdo3->prepare("$selectBarSales");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
		$barSales = $row['SUM(amount)'];
		$barUnits = $row['SUM(unitsTot)'];

	// Look up todays donations
	$selectDonations = "SELECT SUM(amount), COUNT(donationid) from donations WHERE (donatedTo < 2 OR donatedTo = 4) AND DATE(donationTime) = DATE(NOW())";
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
		$donationsNo = $row['COUNT(donationid)'];
		
	// Look up todays bank donations
	$selectDonations = "SELECT SUM(amount), COUNT(donationid) from donations WHERE donatedTo = 2 AND DATE(donationTime) = DATE(NOW())";
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
		$bankDonations = $row['SUM(amount)'];
		$bankDonationsNo = $row['COUNT(donationid)'];
		
	// Look up today's membership fees
	$selectMembershipFees = "SELECT SUM(amountPaid) FROM memberpayments WHERE (paidTo < 2 OR paidTo = 4) AND DATE(paymentdate) = DATE(NOW())";
		try
		{
			$result = $pdo3->prepare("$selectMembershipFees");
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
		
	// Look up today's membership fees Bank
	$selectMembershipFees = "SELECT SUM(amountPaid) FROM memberpayments WHERE paidTo = 2 AND DATE(paymentdate) = DATE(NOW())";
		try
		{
			$result = $pdo3->prepare("$selectMembershipFees");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
		$membershipfeesBank = $row['SUM(amountPaid)'];

		// Look up todays card purchases
		$selectCard = "SELECT SUM(amount) from card_purchase WHERE paidTo = 2 AND DATE(time) = DATE(NOW())";
		try
		{
			$result = $pdo3->prepare("$selectCard");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
		
		$row = $result->fetch();
			$cardBank = $row['SUM(amount)'];
			
		$selectCard = "SELECT SUM(amount) from card_purchase WHERE paidTo = 1 AND DATE(time) = DATE(NOW())";
		try
		{
			$result = $pdo3->prepare("$selectCard");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
		
		$row = $result->fetch();
			$cardCash = $row['SUM(amount)'];
				
	if ($_SESSION['creditOrDirect'] == 0) {
		
		// Look up dispensed today cash
		$selectSales = "SELECT SUM(amount) from sales WHERE DATE(saletime) = DATE(NOW()) AND direct < 2";
		try
		{
			$result = $pdo3->prepare("$selectSales");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
			$salesTodayCash = $row['SUM(amount)'];
	
		// Look up dispensed today bank
		$selectSales = "SELECT SUM(amount) from sales WHERE DATE(saletime) = DATE(NOW()) AND direct = 2";
		try
		{
			$result = $pdo3->prepare("$selectSales");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
			$salesTodayBank = $row['SUM(amount)'];
		
		// Look up bar sales today cash
		$selectSales = "SELECT SUM(amount) from b_sales WHERE DATE(saletime) = DATE(NOW()) AND direct < 2";
		try
		{
			$result = $pdo3->prepare("$selectSales");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
			$salesTodayBarCash = $row['SUM(amount)'];
	
		// Look up bar sales today bank
		$selectSales = "SELECT SUM(amount) from b_sales WHERE DATE(saletime) = DATE(NOW()) AND direct = 2";
		try
		{
			$result = $pdo3->prepare("$selectSales");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
			$salesTodayBarBank = $row['SUM(amount)'];
		
	}

				
	// Calculate total income
	$totalIncome = $donations + $bankDonations + $membershipFees + $membershipfeesBank + $salesTodayCash + $salesTodayBank + $salesTodayBarCash + $salesTodayBarBank + $cardCash + $cardBank; 
	
	// Look up today's till expenses
	$selectExpenses = "SELECT SUM(amount) FROM expenses WHERE DATE(registertime) = DATE(NOW()) AND moneysource = 1";
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
		$tillExpenses = $row['SUM(amount)'];
		
	// Query to look up today's opening details + till + bank balance
	if ($_SESSION['openAndClose'] > 2) {
		
		$openingLookup = "SELECT openingid, openingtime, tillBalance, bankBalance FROM opening ORDER BY openingtime DESC LIMIT 1";
		try
		{
			$result = $pdo3->prepare("$openingLookup");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
			$tillBalance = $row['tillBalance'];	
			$bankBalance = $row['bankBalance'];
			$openingid = $row['openingid'];
			$openingtime = $row['openingtime'];
		
	} else if ($_SESSION['openAndClose'] == 2) {
		
		$openingLookup = "SELECT closingid, closingtime, cashintill, bankBalance FROM closing WHERE currentClosing = 0 ORDER BY closingtime DESC LIMIT 1";	
		try
		{
			$result = $pdo3->prepare("$openingLookup");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
			$tillBalance = $row['cashintill'];	
			$bankBalance = $row['bankBalance'];
			$openingid = $row['closingid'];
			$openingtime = $row['closingtime'];
			
	}
	
	// Determine if club has not done opening/closing in several days, and if so use variable later down to decide how to calculate stock, dispenses etc.		
	if ($_SESSION['openAndClose'] > 1) {
		
		$cutoffTime = strtotime('-2 day', strtotime(date('Y-m-d H:i')));
		$lastOpenOrClose = strtotime(date('Y-m-d H:i',strtotime($openingtime)));
		
		if ($lastOpenOrClose < $cutoffTime) {

			$noActiveOpening = 'true';
			
			if ($_SESSION['openAndClose'] > 2) {
				
				$_SESSION['errorMessage'] = $lang['no-recent-opening'];
				
			} else {
				
				$_SESSION['errorMessage'] = $lang['no-recent-closing'];
				
			}
		
		} else {
			
			$noActiveOpening = 'false';
			
		}
		
		
	}
	

	
	// Look up today's bank expenses
	$selectExpenses = "SELECT SUM(amount) FROM expenses WHERE DATE(registertime) = DATE(NOW()) AND moneysource = 2";
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
		$bankExpenses = $row['SUM(amount)'];

		
	// Calculate estimated till	& club balances
	$tillTot = $tillBalance + $donations + $membershipFees + $tillAdditions + $salesTodayCash + $salesTodayBarCash - $tillExpenses + $cardCash + $cardBank;
	$clubBalance = $tillTot + $bankBalance + $salesTodayBank - $bankExpenses;
	

	pageStart($lang['status'], NULL, NULL, "status", "", $lang['status'], $_SESSION['successMessage'], $_SESSION['errorMessage']);
	
	echo <<<EOD
<center>	
 <div class="actionbox-np2">
 <div class="boxcontent">
<table class='default'>
 <tr>
  <td colspan='8' style='color: #5aa242; text-align: left; font-size: 17px; border-bottom: 2px solid #a80082;'><strong>{$lang['bar-and-dispensary']}</strong></td>
 </tr>
 <tr>
  <th style='text-align: left;'><strong>{$lang['dispensary']}</strong></th>
  <th><strong>{$expr(number_format($salesToday,2))} {$_SESSION['currencyoperator']}</strong></th>
  <th></th>
  <th><strong>{$expr(number_format($quantitySold,2))} g.</strong></th>
  <th></th>
  <th><strong>{$expr(number_format($unitsSold,2))} u.</strong></th>
  <th></th>
 </tr>
 <tr>
  <td style='text-align: left;'><em>{$lang['closeday-owflowers']}</em></td>
  <td>{$expr(number_format($salesTodayFlower,2))} {$_SESSION['currencyoperator']}</td>
  <td>{$expr(number_format($flowerSalesPercentageToday,0))}%</td>
  <td>{$expr(number_format($quantitySoldFlower,2))} g.</td>
  <td>{$expr(number_format($flowerGramsPercentageToday,0))}%</td>
  <td></td>
 </tr>
 <tr>
  <td style='text-align: left;'><em>{$lang['closeday-owextracts']}</em></td>
  <td>{$expr(number_format($salesTodayExtract,2))} {$_SESSION['currencyoperator']}</td>
  <td>{$expr(number_format($extractSalesPercentageToday,0))}%</td>
  <td>{$expr(number_format($quantitySoldExtract,2))} g.</td>
  <td>{$expr(number_format($extractGramsPercentageToday,0))}%</td>
  <td></td>
 </tr>
EOD;
 
	// Query to look up categories
	$selectCats = "SELECT id, name, type from categories WHERE id > 2 ORDER by name ASC";
		try
		{
			$results = $pdo3->prepare("$selectCats");
			$results->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	

		$i = 0;
		
		while ($category = $results->fetch()) {
		
		$categoryid = $category['id'];
		$name = $category['name'];
		$type = $category['type'];
		
		// Create more product queries for each category - to be used further down!
		$customProducts .= " UNION ALL SELECT '$categoryid' AS category, pr.productid AS productid, pr.name AS name, p.purchaseid AS purchaseid, '' AS growtype, p.inMenu AS inMenu, p.closedAt AS closedAt, salesPrice AS gramPrice, p.realQuantity FROM products pr, purchases p WHERE p.category = $categoryid AND p.productid = pr.productid AND (p.closedAt IS NULL OR DATE(p.closingDate) >= DATE(NOW()))";
		
		
		// Look up sales in this cat
		$selectSalesOthers = "SELECT SUM(d.amount), SUM(d.quantity) from sales s, salesdetails d WHERE s.saleid = d.saleid AND DATE(s.saletime) = DATE(NOW()) AND d.category = $categoryid";
		try
		{
			$result = $pdo3->prepare("$selectSalesOthers");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
			$salesTodayOthers = $row['SUM(d.amount)'];
			$quantitySoldOthers = $row['SUM(d.quantity)'];
			
		if ($type == 0) {
				
			$othersSalesPercentageToday = ($salesTodayOthers / $salesToday) * 100;
			$othersGramsPercentageToday = ($quantitySoldOthers / $unitsSold) * 100;
			
		} else {
				
			$othersSalesPercentageToday = ($salesTodayOthers / $salesToday) * 100;
			$othersGramsPercentageToday = ($quantitySoldOthers / $quantitySold) * 100;
			
		}

		if ($type == 0) {
		$unitCatSummary .=  <<<EOD
 <tr>
  <td style='text-align: left;'><em>{$lang['closeday-ow']} $name</em></td>
  <td>{$expr(number_format($salesTodayOthers,2))} {$_SESSION['currencyoperator']}</td>
  <td>{$expr(number_format($othersSalesPercentageToday,0))}%</td>
  <td></td>
  <td></td>
  <td>{$expr(number_format($quantitySoldOthers,2))} u.</td>
  <td>{$expr(number_format($othersGramsPercentageToday,0))}%</td>
 </tr>
EOD;
		} else {
			
		$gramCatSummary .=  <<<EOD
 <tr>
  <td style='text-align: left;'><em>{$lang['closeday-ow']} $name</em></td>
  <td>{$expr(number_format($salesTodayOthers,2))} {$_SESSION['currencyoperator']}</td>
  <td>{$expr(number_format($othersSalesPercentageToday,0))}%</td>
  <td>{$expr(number_format($quantitySoldOthers,2))} g.</td>
  <td>{$expr(number_format($othersGramsPercentageToday,0))}%</td>
  <td></td>
  <td></td>
 </tr>
EOD;
		}
		
		$i++;

	}
	
		echo $gramCatSummary;
		echo $unitCatSummary;
		
		
		
	// Query to look up categories
	$selectCats = "SELECT id, name from b_categories ORDER by name ASC";
		try
		{
			$results = $pdo3->prepare("$selectCats");
			$results->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	

		$i = 0;
		
		while ($category = $results->fetch()) {
		
		$categoryid = $category['id'];
		$name = $category['name'];
		
		
		// Look up sales in this cat
		$selectSalesOthers = "SELECT SUM(d.amount), SUM(d.quantity) from b_sales s, b_salesdetails d WHERE s.saleid = d.saleid AND DATE(s.saletime) = DATE(NOW()) AND d.category = $categoryid";
		try
		{
			$result = $pdo3->prepare("$selectSalesOthers");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
			$barsalesTodayOthers = $row['SUM(d.amount)'];
			$barquantitySoldOthers = $row['SUM(d.quantity)'];
							
		$barothersSalesPercentageToday = ($barsalesTodayOthers / $barSales) * 100;
		$barothersGramsPercentageToday = ($barquantitySoldOthers / $barUnits) * 100;

		$barCatSummary .=  <<<EOD
 <tr>
  <td style='text-align: left;'><em>{$lang['closeday-ow']} $name</em></td>
  <td>{$expr(number_format($barsalesTodayOthers,2))} {$_SESSION['currencyoperator']}</td>
  <td>{$expr(number_format($barothersSalesPercentageToday,0))}%</td>
  <td></td>
  <td></td>
  <td>{$expr(number_format($barquantitySoldOthers,2))} u.</td>
  <td>{$expr(number_format($barothersGramsPercentageToday,0))}%</td>
 </tr>
EOD;

		
		$i++;

	}	


	echo <<<EOD
 <tr>
  <td colspan='7'></td>
 </tr>
 <tr>
  <th style='text-align: left;'><strong>{$lang['bar']}</strong></th>
  <th><strong>{$expr(number_format($barSales,2))} {$_SESSION['currencyoperator']}</strong></th>
  <th></th>
  <th></th>
  <th></th>
  <th><strong>{$expr(number_format($barUnits,2))} u.</strong></th>
  <th></th>
 </tr>
EOD;
		echo $barCatSummary;
	echo <<<EOD
 <tr rowspan='2'>
  <td colspan='8'>&nbsp;</td>
 </tr>
 <tr>
  <td colspan='8' style='color: #5aa242; text-align: left; font-size: 17px; border-bottom: 2px solid #a80082;'><strong>Member details</strong></td>
 </tr>
 <tr>
  <td style='text-align: left;'>{$lang['closeday-newmembers']}</td>
  <td>$newmembers</td>
  <td></td>
  <td></td>
  <td></td>
  <td></td>
  <td></td>
 </tr>
 <tr>
  <td style='text-align: left;'>{$lang['total-members']}</td>
  <td>$currentmembers</td>
  <td></td>
  <td></td>
  <td></td>
  <td></td>
 </tr>
 <tr>
  <td style='text-align: left;'>{$lang['active-members']}</td>
  <td>$activemembers</td>
  <td></td>
  <td></td>
  <td></td>
  <td></td>
 </tr>
 <tr>
  <td style='text-align: left;'>{$lang['closeday-renewedmembers']}</td>
  <td>$renewedMembers</td>
  <td></td>
  <td></td>
  <td></td>
  <td></td>
 </tr>
 <tr>
  <td style='text-align: left;'>{$lang['expired-members']}</td>
  <td>$expiredmembers</td>
  <td></td>
  <td></td>
  <td></td>
  <td></td>
 </tr>
 <tr>
  <td style='text-align: left;'>{$lang['banned-members']}</td>
  <td>$bannedmembers</td>
  <td></td>
  <td></td>
  <td></td>
  <td></td>
 </tr>
 <tr>
  <td style='text-align: left;'>{$lang['deleted-members']}</td>
  <td>$deletedmembers</td>
  <td></td>
  <td></td>
  <td></td>
  <td></td>
 </tr>
 <tr rowspan='2'>
  <td colspan='8'>&nbsp;</td>
 </tr>
 <tr>
  <td colspan='8' style='color: #5aa242; text-align: left; font-size: 17px; border-bottom: 2px solid #a80082;'><strong>{$lang['closeday-finances']}</strong></td>
 </tr>
 <tr>
  <td style='text-align: left;'>{$lang['closeday-donations-till']}</td>
  <td>{$expr(number_format($donations,2))} {$_SESSION['currencyoperator']}</td>
  <td style='text-align: left;'>($donationsNo)</td>
  <td></td>
  <td></td>
  <td></td>
 </tr>
 <tr>
  <td style='text-align: left;'>{$lang['closeday-donations-bank']}</td>
  <td>{$expr(number_format($bankDonations,2))} {$_SESSION['currencyoperator']}</td>
  <td style='text-align: left;'>($bankDonationsNo)</td>
  <td></td>
  <td></td>
  <td></td>
 </tr>
EOD;
	if ($_SESSION['creditOrDirect'] == 0) {
		
		echo <<<EOD
 <tr>
  <td style='text-align: left;'>{$lang['dispensed-direct-till']}</td>
  <td>{$expr(number_format($salesTodayCash,2))} {$_SESSION['currencyoperator']}</td>
  <td></td>
  <td></td>
  <td></td>
  <td></td>
 </tr>
 <tr>
  <td style='text-align: left;'>{$lang['dispensed-direct-bank']}</td>
  <td>{$expr(number_format($salesTodayBank,2))} {$_SESSION['currencyoperator']}</td>
  <td></td>
  <td></td>
  <td></td>
  <td></td>
 </tr>
 <tr>
  <td style='text-align: left;'>{$lang['direct-bar-sales-till']}</td>
  <td>{$expr(number_format($salesTodayBarCash,2))} {$_SESSION['currencyoperator']}</td>
  <td></td>
  <td></td>
  <td></td>
  <td></td>
 </tr>
 <tr>
  <td style='text-align: left;'>{$lang['direct-bar-sales-bank']}</td>
  <td>{$expr(number_format($salesTodayBarBank,2))} {$_SESSION['currencyoperator']}</td>
  <td></td>
  <td></td>
  <td></td>
  <td></td>
 </tr>
EOD;
	}
	
	echo <<<EOD
 <tr>
  <td style='text-align: left;'>{$lang['closeday-membershipfees-till']}</td>
  <td>{$expr(number_format($membershipFees,2))} {$_SESSION['currencyoperator']}</td>
  <td></td>
  <td></td>
  <td></td>
  <td></td>
 </tr>
 <tr>
  <td style='text-align: left'>{$lang['closeday-membershipfees-bank']}</td>
  <td>{$expr(number_format($membershipfeesBank,2))} {$_SESSION['currencyoperator']}</td>
  <td></td>
  <td></td>
  <td></td>
  <td></td>
 </tr>
 <tr>
  <td style='text-align: left;'>{$lang['chip-sales-cash']}</td>
  <td>{$expr(number_format($cardCash,2))} {$_SESSION['currencyoperator']}</td>
  <td></td>
  <td></td>
  <td></td>
  <td></td>
 </tr>
 <tr>
  <td style='text-align: left; border-bottom: 1px dashed #ababab;'>{$lang['chip-sales-bank']}</td>
  <td style='border-bottom: 1px dashed #ababab;'>{$expr(number_format($cardBank,2))} {$_SESSION['currencyoperator']}</td>
  <td></td>
  <td></td>
  <td></td>
  <td></td>
 </tr>
 <tr>
  <td style='text-align: left; border-bottom: 1px solid #ababab;'>{$lang['closeday-totalincome']}</td>
  <td style='border-bottom: 1px solid #ababab;'>{$expr(number_format($totalIncome,2))} {$_SESSION['currencyoperator']}</td>
  <td></td>
  <td></td>
  <td></td>
  <td></td>
 </tr>
 <tr>
  <td style='text-align: left;'>{$lang['closeday-tillexpenses']}</td>
  <td>{$expr(number_format($tillExpenses,2))} {$_SESSION['currencyoperator']}</td>
  <td></td>
  <td></td>
  <td></td>
  <td></td>
 </tr>
EOD;

		if ($_SESSION['openAndClose'] == 0 || $noActiveOpening == 'true') {
			
	echo <<<EOD
 <tr>
  <td style='text-align: left;'>{$lang['closeday-bankexpenses']}</td>
  <td>{$expr(number_format($bankExpenses,2))} {$_SESSION['currencyoperator']}</td>
  <td></td>
  <td></td>
  <td></td>
  <td></td>
 </tr>
 <tr>
  <td style='text-align: left;'>Saldo de socios</td>
  <td>{$expr(number_format($totCredit,2))} {$_SESSION['currencyoperator']}</td>
  <td></td>
  <td></td>
  <td></td>
  <td></td>
 </tr>
 <tr rowspan='2'>
  <td colspan='7'>&nbsp;</td>
 </tr>
 
EOD;

		} else {
			
	echo <<<EOD
 <tr>
  <td style='text-align: left;'>{$lang['closeday-tillbalance']}</td>
  <td>{$expr(number_format($tillTot,2))} {$_SESSION['currencyoperator']}</td>
  <td></td>
  <td></td>
  <td></td>
  <td></td>
 </tr>
 <tr>
  <td style='text-align: left;'>{$lang['closeday-bankexpenses']}</td>
  <td>{$expr(number_format($bankExpenses,2))} {$_SESSION['currencyoperator']}</td>
  <td></td>
  <td></td>
  <td></td>
  <td></td>
 </tr>
 <tr>
  <td style='text-align: left;'>Credito de socios</td>
  <td>{$expr(number_format($totCredit,2))} {$_SESSION['currencyoperator']}</td>
  <td></td>
  <td></td>
  <td></td>
  <td></td>
 </tr>
 <tr rowspan='2'>
  <td colspan='7'>&nbsp;</td>
 </tr>
 
EOD;

		}


/*
	Jars: Opening + added - takeouts - dispensed (check stock.php)
	Remove: w/o shake, Delta

	Select all products who are not closed
	For each product, get:
	V opening weight
	V additions
	V takeouts
	V intstash
	V extstash
	V dispensed
*/

	$selectProducts = "SELECT '1' AS category, g.flowerid AS productid, g.name AS name, p.purchaseid AS purchaseid, p.growType AS growtype, p.inMenu AS inMenu, p.closedAt AS closedAt, salesPrice AS gramPrice, p.realQuantity FROM flower g, purchases p WHERE p.category = 1 AND p.productid = g.flowerid AND (p.closedAt IS NULL OR DATE(p.closingDate) >= DATE(NOW())) UNION ALL SELECT '2' AS category, h.extractid AS productid, h.name AS name, p.purchaseid AS purchaseid, '' AS growtype, p.inMenu AS inMenu, p.closedAt AS closedAt, salesPrice AS gramPrice, p.realQuantity FROM extract h, purchases p WHERE p.category = 2 AND p.productid = h.extractid AND (p.closedAt IS NULL OR DATE(p.closingDate) >= DATE(NOW()))";
	
	$selectProducts .= $customProducts;
			
		try
		{
			$results = $pdo3->prepare("$selectProducts");
			$results->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		

		
	$productDetails = <<<EOD
 <tr>
  <td colspan='8'></td>
 </tr>
 <tr>
  <td colspan='8'></td>
 </tr>
 <tr>
  <td colspan='8' style='color: #5aa242; text-align: left; font-size: 17px; border-bottom: 2px solid #a80082;'><strong>{$lang['closeday-productdetails']}</strong></td>
 </tr>
 <tr>
  <td colspan='8' style='color: #a80082; text-align: center; font-size: 14px; border-bottom: 1px solid #dedede;'><strong>{$lang['global-flowerscaps']}</strong></td>
 </tr>
 <tr>
  <th style='text-align: left;'><strong>{$lang['global-name']}&nbsp;&nbsp;</strong></th>
  <th><strong>{$lang['closeday-jars']}&nbsp;&nbsp;</strong></th>
  <th><strong>{$lang['closeday-intstash']}&nbsp;&nbsp;</strong></th>
  <th><strong>{$lang['closeday-extstash']}&nbsp;&nbsp;</strong></th>
  <th><strong>{$lang['global-total']}&nbsp;&nbsp;</strong></th>
  <th><strong>{$lang['value']}&nbsp;&nbsp;</strong></th>
  <th><strong>{$lang['closeday-dispensed']}&nbsp;&nbsp;</strong></th>
  <th><strong>Status&nbsp;&nbsp;</strong></th>
 </tr>
EOD;

		
		while ($product = $results->fetch()) {
			
			
			$category = $product['category'];
			$productid = $product['productid'];
			$name = $product['name'];
			$purchaseid = $product['purchaseid'];
			$growtype = $product['growtype'];
			$inMenu = $product['inMenu'];
			$closedAt = $product['closedAt'];
			$gramPrice = $product['gramPrice'];
			
			$noOfProducts++;
			
			$totGramPrice = $totGramPrice + $gramPrice;
			
			if ($closedAt != '') {
				$productStatus = "Closed";
			} else if ($inMenu == 0) {
				$productStatus = "Not in menu";
			} else {
				$productStatus = "In menu";
			}
		
			
			if ($category == 1) {
				// Look up growtype
				$growDetails = "SELECT growtype FROM growtypes WHERE growtypeid = '$growtype'";
				try
				{
					$result = $pdo3->prepare("$growDetails");
					$result->execute();
					$data = $result->fetchAll();
				}
				catch (PDOException $e)
				{
						$error = 'Error fetching user: ' . $e->getMessage();
						echo $error;
						exit();
				}
					
				if (!$data) {
					$growtype = '';
				} else {
				
			$row = $data[0];
				$growtype = $row[0];
					$growtype = "(" . $rowGrow['growtype'] . ")";
					
				}
			}

			
			// Determine how to calculate weight and sales:
			if ($_SESSION['openAndClose'] == 0 || $noActiveOpening == 'true') {
				
				// Calculate Stock
				$selectSales = "SELECT SUM(quantity), SUM(realQuantity) FROM salesdetails WHERE purchaseid = $purchaseid";
		try
		{
			$result = $pdo3->prepare("$selectSales");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
					$sales = $row['SUM(quantity)'];
					$realSales = $row['SUM(realQuantity)'];
		
				$selectPermAdditions = "SELECT SUM(quantity) FROM productmovements WHERE purchaseid = $purchaseid AND type = 1 AND (movementTypeid = 1 OR movementTypeid = 3 OR movementTypeid = 10)";
		try
		{
			$result = $pdo3->prepare("$selectPermAdditions");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
						$permAdditions = $row['SUM(quantity)'];
				
				$selectPermRemovals = "SELECT SUM(quantity) FROM productmovements WHERE purchaseid = $purchaseid AND type = 2 AND (movementTypeid = 4 OR movementTypeid = 7 OR movementTypeid = 8 OR movementTypeid = 9 OR movementTypeid = 11 OR movementTypeid = 13 OR movementTypeid = 14 OR movementTypeid = 15 OR movementTypeid = 16)";
		try
		{
			$result = $pdo3->prepare("$selectPermRemovals");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
						$permRemovals = $row['SUM(quantity)'];
						
				// Calculate what's in Internal stash
				$selectStashedInt = "SELECT SUM(quantity) FROM productmovements WHERE purchaseid = $purchaseid AND type = 2 AND (movementTypeid = 5 OR movementTypeid = 18)";
		try
		{
			$result = $pdo3->prepare("$selectStashedInt");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
						$stashedInt = $row['SUM(quantity)'];
					
				$selectUnStashedInt = "SELECT SUM(quantity) FROM productmovements WHERE purchaseid = $purchaseid AND type = 1 AND (movementTypeid = 12 OR movementTypeid = 17)";
		try
		{
			$result = $pdo3->prepare("$selectUnStashedInt");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
						$unStashedInt = $row['SUM(quantity)'];
			
						
					$inStashInt = $stashedInt - $unStashedInt;
					$inStashInt = $inStashInt;
			
			
				// Calculate what's in External stash
				$selectStashedExt = "SELECT SUM(quantity) FROM productmovements WHERE purchaseid = $purchaseid AND type = 2 AND (movementTypeid = 6 OR movementTypeid = 20)";
		try
		{
			$result = $pdo3->prepare("$selectStashedExt");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
						$stashedExt = $row['SUM(quantity)'];
					
				$selectUnStashedExt = "SELECT SUM(quantity) FROM productmovements WHERE purchaseid = $purchaseid AND type = 1 AND (movementTypeid = 2 OR movementTypeid = 19)";
		try
		{
			$result = $pdo3->prepare("$selectUnStashedExt");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
						$unStashedExt = $row['SUM(quantity)'];
		
					
				$inStashExt = $stashedExt - $unStashedExt;
				$inStashExt = $inStashExt;
				
				$inStash = $inStashInt + $inStashExt;
				$jarWeight = $product['realQuantity'] + $permAdditions - $sales - $permRemovals - $inStash;
				
				$weightTotal = $jarWeight + $inStashInt + $inStashExt;
				$weightPrice = $weightTotal * $gramPrice;
				
				// Look up todays dispenses
				$selectSales = "SELECT SUM(d.quantity), SUM(d.realQuantity) FROM salesdetails d, sales s WHERE DATE(s.saletime) = DATE(NOW()) AND d.saleid = s.saleid AND d.purchaseid = $purchaseid";
		try
		{
			$result = $pdo3->prepare("$selectSales");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
					$soldToday = $row['SUM(d.quantity)'];
					$realSoldToday = $row['SUM(d.realQuantity)'];


			} else {

				// Query to look up today's opening weight
				if ($_SESSION['openAndClose'] > 2) {
					
					$openingLookup = "SELECT d.weight FROM openingdetails d, opening o WHERE d.openingid = $openingid AND d.openingid = o.openingid AND category = $category AND purchaseid = $purchaseid";
					
				} else if ($_SESSION['openAndClose'] == 2) {
			
					$openingLookup = "SELECT d.weight FROM closingdetails d, closing o WHERE d.closingid = $openingid AND d.closingid = o.closingid AND category = $category AND purchaseid = $purchaseid";
					
				}
				
		try
		{
			$result = $pdo3->prepare("$openingLookup");
			$result->execute();
			$data = $result->fetchAll();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
		
			
		if (!$data) {
			
	   				$purchaseLookup = "SELECT realQuantity from purchases where purchaseid = $purchaseid";
	   				
					try
					{
						$result = $pdo3->prepare("$purchaseLookup");
						$result->execute();
					}
					catch (PDOException $e)
					{
							$error = 'Error fetching user: ' . $e->getMessage();
							echo $error;
							exit();
					}
			
					$row = $result->fetch();
					$openingWeight = $row['0'];
			
		} else {
				$row = $data[0];
					$openingWeight = $row['0'];
			
		}

				
					
				// Look up todays dispenses
				$selectSales = "SELECT SUM(d.quantity), SUM(d.realQuantity) FROM salesdetails d, sales s WHERE DATE(s.saletime) = DATE(NOW()) AND d.saleid = s.saleid AND d.purchaseid = $purchaseid";
		try
		{
			$result = $pdo3->prepare("$selectSales");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
					$soldToday = $row['SUM(d.quantity)'];
					$realSoldToday = $row['SUM(d.realQuantity)'];
	
				// Look up additions and removals
				$selectAdditions = "SELECT SUM(quantity) FROM productmovements WHERE purchaseid = $purchaseid AND type = 1 AND movementtime > '$openingtime'";
		try
		{
			$result = $pdo3->prepare("$selectAdditions");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
					$addedToday = $row['SUM(quantity)'];
					
				$selectRemovals = "SELECT SUM(quantity) FROM productmovements WHERE purchaseid = $purchaseid AND type = 2 AND movementtime > '$openingtime'";
		try
		{
			$result = $pdo3->prepare("$selectRemovals");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
					$takeoutsToday = $row['SUM(quantity)'];
					
					
				// Calculate jar weight:
				$jarWeight = $openingWeight + $addedToday - $takeoutsToday - $soldToday;	
				
				
				// Calculate what's in Internal stash
				$selectStashedInt = "SELECT SUM(quantity) FROM productmovements WHERE purchaseid = $purchaseid AND type = 2 AND (movementTypeid = 5 OR movementTypeid = 18)";
		try
		{
			$result = $pdo3->prepare("$selectStashedInt");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
						$stashedInt = $row['SUM(quantity)'];
					
				$selectUnStashedInt = "SELECT SUM(quantity) FROM productmovements WHERE purchaseid = $purchaseid AND type = 1 AND (movementTypeid = 12 OR movementTypeid = 17)";
		try
		{
			$result = $pdo3->prepare("$selectUnStashedInt");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
						$unStashedInt = $row['SUM(quantity)'];
			
						
				$inStashInt = $stashedInt - $unStashedInt;
			
			
				// Calculate what's in External stash
				$selectStashedExt = "SELECT SUM(quantity) FROM productmovements WHERE purchaseid = $purchaseid AND type = 2 AND (movementTypeid = 6 OR movementTypeid = 20)";
		try
		{
			$result = $pdo3->prepare("$selectStashedExt");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
						$stashedExt = $row['SUM(quantity)'];
					
				$selectUnStashedExt = "SELECT SUM(quantity) FROM productmovements WHERE purchaseid = $purchaseid AND type = 1 AND (movementTypeid = 2 OR movementTypeid = 19)";
		try
		{
			$result = $pdo3->prepare("$selectUnStashedExt");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
						$unStashedExt = $row['SUM(quantity)'];
			
						
				$inStashExt = $stashedExt - $unStashedExt;
				
				$weightTotal = $jarWeight + $inStashInt + $inStashExt;
				$weightPrice = $weightTotal * $gramPrice;
				
			}
			
			// Reset Other Cat totals
			$otherTotJar = 0;
			$otherTotIntSt = 0;
			$otherTotExtSt = 0;
			$otherTot = 0;
			$otherSoldToday = 0;

			
			
	  		// Create totals per category
			if ($category == 1) {
				$flowerTotJar = $flowerTotJar + $jarWeight;
				$flowerTotIntSt = $flowerTotIntSt + $inStashInt;
				$flowerTotExtSt = $flowerTotExtSt + $inStashExt;
				$flowerTot = $flowerTotJar + $flowerTotIntSt + $flowerTotExtSt;
				$flowerSoldToday = $flowerSoldToday + $soldToday;
				$flowerWeightPrice = $flowerWeightPrice + $weightPrice;
			} else if ($category == 2) {
				$extractTotJar = $extractTotJar + $jarWeight;
				$extractTotIntSt = $extractTotIntSt + $inStashInt;
				$extractTotExtSt = $extractTotExtSt + $inStashExt;
				$extractTot = $extractTotJar + $extractTotIntSt + $extractTotExtSt;
				$extractSoldToday = $extractSoldToday + $soldToday;
				$extractWeightPrice = $extractWeightPrice + $weightPrice;
				
				// Add Extract header
				if ($extractHeader != 'set') {
					$productDetails .= <<<EOD
 <tr>
  <td colspan='8'></td>
 </tr>
 <tr>
  <td colspan='8' style='color: #a80082; text-align: center; font-size: 14px; border-bottom: 1px solid #dedede; border-top: 1px solid #dedede; margin-top: 10px;'><strong>{$lang['global-extractscaps']}</strong></td>
 </tr>
 <tr>
  <th style='text-align: left;'><strong>{$lang['global-name']}&nbsp;&nbsp;</strong></th>
  <th><strong>{$lang['closeday-jars']}&nbsp;&nbsp;</strong></th>
  <th><strong>{$lang['closeday-intstash']}&nbsp;&nbsp;</strong></th>
  <th><strong>{$lang['closeday-extstash']}&nbsp;&nbsp;</strong></th>
  <th><strong>{$lang['global-total']}&nbsp;&nbsp;</strong></th>
  <th><strong>{$lang['value']}&nbsp;&nbsp;</strong></th>
  <th><strong>{$lang['closeday-dispensed']}&nbsp;&nbsp;</strong></th>
  <th><strong>Status&nbsp;&nbsp;</strong></th>
 </tr>
EOD;
				$extractHeader = 'set';
				}
				
			} else {
				
				// Query to look up categories
				$selectCats = "SELECT id, name, type from categories WHERE id = $category";
		try
		{
			$result = $pdo3->prepare("$selectCats");
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
		  	    	$catID = $row['id'];
		  	    	$type = $row['type'];
		  	    			  	    	
		  	    	
				if (${'otherHeader' . $catID} != 'set') {
					$productDetails .= <<<EOD
 <tr>
  <td colspan='8'></td>
 </tr>
 <tr>
  <td colspan='8' style='color: #a80082; text-align: center; font-size: 14px; border-bottom: 1px solid #dedede; border-top: 1px solid #dedede; margin-top: 10px;'><strong>$catName</strong></td>
 </tr>
 <tr>
  <th style='text-align: left;'><strong>{$lang['global-name']}&nbsp;&nbsp;</strong></th>
  <th><strong>Stock&nbsp;&nbsp;</strong></th>
  <th><strong>{$lang['closeday-intstash']}&nbsp;&nbsp;</strong></th>
  <th><strong>{$lang['closeday-extstash']}&nbsp;&nbsp;</strong></th>
  <th><strong>{$lang['global-total']}&nbsp;&nbsp;</strong></th>
  <th><strong>{$lang['value']}&nbsp;&nbsp;</strong></th>
  <th><strong>{$lang['closeday-dispensed']}&nbsp;&nbsp;</strong></th>
  <th><strong>Status&nbsp;&nbsp;</strong></th>
 </tr>
EOD;
				${'otherHeader' . $catID} = 'set';
				}
					
					
				
					$otherTotals[$catID]['catName'] = $catName;
					$otherTotals[$catID]['categoryType'] = $type;
					$otherTotals[$catID]['otherTotJar'] = $otherTotals[$catID]['otherTotJar'] + $jarWeight;
					$otherTotals[$catID]['otherTotIntSt'] = $otherTotals[$catID]['otherTotIntSt'] + $inStashInt;
					$otherTotals[$catID]['otherTotExtSt'] = $otherTotals[$catID]['otherTotExtSt'] + $inStashExt;
					$otherTotals[$catID]['otherTot'] = $otherTotals[$catID]['otherTotJar'] + $otherTotals[$catID]['otherTotIntSt'] + $otherTotals[$catID]['otherTotExtSt'];
					$otherTotals[$catID]['otherSoldToday'] = $otherTotals[$catID]['otherSoldToday'] + $realSoldToday;
					$otherTotals[$catID]['otherWeightPrice'] = $otherTotals[$catID]['otherWeightPrice'] + $weightPrice;
					
				}
				
				
			
		  	if ($category < 3 || $type == 1) {
				$productDetails .= <<<EOD
 <tr>
  <td style='text-align: left;'>{$name} <span class='smallerfont3'>{$growtype}</span></td>
  <td>{$expr(number_format($jarWeight,2))} g.</td>
  <td>{$expr(number_format($inStashInt,2))} g.</td>
  <td>{$expr(number_format($inStashExt,2))} g.</td>
  <td><strong>{$expr(number_format($weightTotal,2))} g.</strong></td>
  <td>{$expr(number_format($weightPrice,2))} {$_SESSION['currencyoperator']}</td>
  <td>{$expr(number_format($soldToday,2))} g.</td>
  <td>{$productStatus}</td>
 </tr>
EOD;

	  		} else {
		  		
				$productDetails .= <<<EOD
 <tr>
  <td style='text-align: left;'>{$name} <span class='smallerfont3'>{$growtype}</span></td>
  <td>{$expr(number_format($jarWeight,2))} u.</td>
  <td>{$expr(number_format($inStashInt,2))} u.</td>
  <td>{$expr(number_format($inStashExt,2))} u.</td>
  <td><strong>{$expr(number_format($weightTotal,2))} u.</strong></td>
  <td>{$expr(number_format($weightPrice,2))} {$_SESSION['currencyoperator']}</td>
  <td>{$expr(number_format($soldToday,2))} u.</td>
  <td>{$productStatus}</td>
 </tr>
EOD;
	  		}
	  		
		} // End product loop
		
		
		
		
		
		
		
	foreach($otherTotals as $oTot) {
		
		if ($oTot['categoryType'] == 0) {
		
			$productOvvU .= <<<EOD
		
 <tr>
  <td style='text-align: left;'>{$oTot['catName']}</td>
  <td>{$expr(number_format($oTot['otherTotJar'],2))} u.</td>
  <td>{$expr(number_format($oTot['otherTotIntSt'],2))} u.</td>
  <td>{$expr(number_format($oTot['otherTotExtSt'],2))} u.</td>
  <td><strong>{$expr(number_format($oTot['otherTot'],2))} u.</strong></td>
  <td>{$expr(number_format($oTot['otherWeightPrice'],2))} {$_SESSION['currencyoperator']}</td>
  <td>{$expr(number_format($oTot['otherSoldToday'],2))} u.</td>
 </tr>
EOD;

			$unitsotherTotJar = $unitsotherTotJar + $oTot['otherTotJar'];
			$unitsotherTotIntSt = $unitsotherTotIntSt + $oTot['otherTotIntSt'];
			$unitsotherTotExtSt = $unitsotherTotExtSt + $oTot['otherTotExtSt'];
			$unitsotherTot = $unitsotherTot + $oTot['otherTot'];
			$unitsotherWeightPrice = $unitsotherWeightPrice + $oTot['otherWeightPrice'];
			$unitsotherSoldToday = $unitsotherSoldToday + $oTot['otherSoldToday'];

		} else {
			
			$productOvvG .= <<<EOD
		
 <tr>
  <td style='text-align: left;'>{$oTot['catName']}</td>
  <td>{$expr(number_format($oTot['otherTotJar'],2))} g.</td>
  <td>{$expr(number_format($oTot['otherTotIntSt'],2))} g.</td>
  <td>{$expr(number_format($oTot['otherTotExtSt'],2))} g.</td>
  <td><strong>{$expr(number_format($oTot['otherTot'],2))} g.</strong></td>
  <td>{$expr(number_format($oTot['otherWeightPrice'],2))} {$_SESSION['currencyoperator']}</td>
  <td>{$expr(number_format($oTot['otherSoldToday'],2))} g.</td>
 </tr>
EOD;
			
			$gramsotherTotJar = $gramsotherTotJar + $oTot['otherTotJar'];
			$gramsotherTotIntSt = $gramsotherTotIntSt + $oTot['otherTotIntSt'];
			$gramsotherTotExtSt = $gramsotherTotExtSt + $oTot['otherTotExtSt'];
			$gramsotherTot = $gramsotherTot + $oTot['otherTot'];
			$gramsotherWeightPrice = $gramsotherWeightPrice + $oTot['otherWeightPrice'];
			$gramsotherSoldToday = $gramsotherSoldToday + $oTot['otherSoldToday'];

			
		}
	}


		$fullTotJar = $flowerTotJar + $extractTotJar + $gramsotherTotJar;
		$fullTotIntSt = $flowerTotIntSt + $extractTotIntSt + $gramsotherTotIntSt;
		$fullTotExtSt = $flowerTotExtSt + $extractTotExtSt + $gramsotherTotExtSt;
		$fullTot = $flowerTot + $extractTot + $gramsotherTot;
		$fullSoldToday = $flowerSoldToday + $extractSoldToday + $gramsotherSoldToday;
		$fullWeightPrice = $flowerWeightPrice + $extractWeightPrice + $gramsotherWeightPrice;
		
		$productOverview = <<<EOD
 <tr>
  <td colspan='8' style='color: #5aa242; text-align: left; font-size: 17px; border-bottom: 2px solid #a80082;'><strong>{$lang['dispensary-overview']}</strong></td>
 </tr>
 <tr>
  <td></td>
  <td><strong>{$lang['closeday-jars']}&nbsp;&nbsp;</strong></td>
  <td><strong>{$lang['closeday-intstash']}&nbsp;&nbsp;</strong></td>
  <td><strong>{$lang['closeday-extstash']}&nbsp;&nbsp;</strong></td>
  <td><strong>{$lang['global-total']}&nbsp;&nbsp;</strong></td>
  <td><strong>{$lang['value']}&nbsp;&nbsp;</strong></td>
  <td><strong>{$lang['closeday-dispensed']}&nbsp;&nbsp;</strong></td>
 </tr>
 <tr>
  <td style='text-align: left;'>{$lang['global-flowers']}</td>
  <td>{$expr(number_format($flowerTotJar,2))} g.</td>
  <td>{$expr(number_format($flowerTotIntSt,2))} g.</td>
  <td>{$expr(number_format($flowerTotExtSt,2))} g.</td>
  <td><strong>{$expr(number_format($flowerTot,2))} g.</strong></td>
  <td>{$expr(number_format($flowerWeightPrice,2))} {$_SESSION['currencyoperator']}</td>
  <td>{$expr(number_format($flowerSoldToday,2))} g.</td>
 </tr>
 <tr>
  <td style='text-align: left;'>{$lang['global-extracts']}</td>
  <td>{$expr(number_format($extractTotJar,2))} g.</td>
  <td>{$expr(number_format($extractTotIntSt,2))} g.</td>
  <td>{$expr(number_format($extractTotExtSt,2))} g.</td>
  <td><strong>{$expr(number_format($extractTot,2))} g.</strong></td>
  <td>{$expr(number_format($extractWeightPrice,2))} {$_SESSION['currencyoperator']}</td>
  <td>{$expr(number_format($extractSoldToday,2))} g.</td>
 </tr>
EOD;

		$productOverview .= $productOvvG;
		
		$productOverview .= <<<EOD
 <tr style='border-top: 1px solid #888; border-bottom: 2px solid #888;'>
  <td style='text-align: left;'><strong>TOTAL G</strong></td>
  <td><strong>{$expr(number_format($fullTotJar,2))} g.</strong></td>
  <td><strong>{$expr(number_format($fullTotIntSt,2))} g.</strong></td>
  <td><strong>{$expr(number_format($fullTotExtSt,2))} g.</strong></td>
  <td><strong>{$expr(number_format($fullTot,2))} g.</strong></td>
  <td><strong>{$expr(number_format($fullWeightPrice,2))} {$_SESSION['currencyoperator']}</strong></td>
  <td><strong>{$expr(number_format($fullSoldToday,2))} g.</strong></td>
 </tr>
 <tr>
  <td colspan='7'>&nbsp;</td>
 </tr>
EOD;

		$productOverview .= $productOvvU;

		$productOverview .= <<<EOD
 <tr style='border-top: 1px solid #888; border-bottom: 2px solid #888;'>
  <td style='text-align: left;'><strong>TOTAL U</strong></td>
  <td><strong>{$expr(number_format($unitsotherTotJar,2))} u.</strong></td>
  <td><strong>{$expr(number_format($unitsotherTotIntSt,2))} u.</strong></td>
  <td><strong>{$expr(number_format($unitsotherTotExtSt,2))} u.</strong></td>
  <td><strong>{$expr(number_format($unitsotherTot,2))} u.</strong></td>
  <td><strong>{$expr(number_format($unitsotherWeightPrice,2))} {$_SESSION['currencyoperator']}</strong></td>
  <td><strong>{$expr(number_format($unitsotherSoldToday,2))} u.</strong></td>
 </tr>
 <tr>
  <td colspan='7'>&nbsp;</td>
 </tr>
EOD;

/*
		<table>
  <?php foreach( $otherTotals AS $product ): ?>
    <tr>
      <?php foreach( $product AS $key => $value ): ?>
       <td><?php echo "$key = $value"; ?></td>
      <?php endforeach; ?>
    </tr>
  <?php endforeach; ?>
</table>

					$otherTotals[$catID]['catName'] = $catName;
					$otherTotals[$catID]['otherTotJar'] = $otherTotals[$catID]['otherTotJar'] + $jarWeight;
					$otherTotals[$catID]['otherTotIntSt'] = $otherTotals[$catID]['otherTotIntSt'] + $inStashInt;
					$otherTotals[$catID]['otherTotExtSt'] = $otherTotals[$catID]['otherTotExtSt'] + $inStashExt;
					$otherTotals[$catID]['otherTot'] = $otherTotals[$catID]['otherTotJar'] + $otherTotals[$catID]['otherTotIntSt'] + $otherTotals[$catID]['otherTotExtSt'];
					$otherTotals[$catID]['otherSoldToday'] = $otherTotals[$catID]['otherSoldToday'] + $realSoldToday;
					
				$extractTotJar = $extractTotJar + $jarWeight;
				$extractTotIntSt = $extractTotIntSt + $inStashInt;
				$extractTotExtSt = $extractTotExtSt + $inStashExt;
				$extractTot = $extractTotJar + $extractTotIntSt + $extractTotExtSt;
				$extractSoldToday = $extractSoldToday + $soldToday;
				$extractWeightPrice = $extractWeightPrice + $weightPrice;
*/























  
  
		// Query to look up expenses
	$selectExpenses = "SELECT expenseid, registertime, userid, expensetype, expense, moneysource, other, amount, shop, comment, receipt, expensecategory FROM expenses WHERE DATE(registertime) = DATE(NOW()) ORDER by registertime DESC";
		try
		{
			$resultsX = $pdo3->prepare("$selectExpenses");
			$resultsX->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}

		
		
		$expenseDetails .= <<<EOD
<br />
<br /><br /><br />
<table class='default'>
 <tr>
  <td colspan='8' style='color: #5aa242; text-align: left; font-size: 17px; border-bottom: 2px solid #a80082;'><strong>{$lang['global-expensescaps']}</strong></td>
 </tr>
	   <tr>
	    <th style='text-align: center;'><strong>{$lang['global-time']}</strong></th>
	    <th style='text-align: center;'><strong>{$lang['global-category']}</strong></th>
	    <th style='text-align: center;'><strong>{$lang['global-expense']}</strong></th>
	    <th style='text-align: center;'><strong>{$lang['global-shop']}</strong></th>
	    <th style='text-align: center;'><strong>{$lang['global-member']}</strong></th>
	    <th style='text-align: center;'><strong>{$lang['global-amount']}</strong></th>
	    <th style='text-align: center;'><strong>{$lang['global-source']}</strong></th>
	    <th style='text-align: center;'><strong>{$lang['global-receipt']}?</strong></th>
	   </tr>
EOD;


		while ($expense = $resultsX->fetch()) {
	
	
	$userid = $expense['userid']; // find member
	$moneysource = $expense['moneysource'];
	$receipt = $expense['receipt'];
	$other = $expense['other'];
	$expenseCat = $expense['expensecategory'];
	$formattedDate = date("d M H:i", strtotime($expense['registertime'] . "+$offsetSec seconds"));
	
	if ($expenseCat == NULL) {
		$expenseCat = '';
	} else {
		if ($_SESSION['lang'] == 'es') {
			$selectExpenseCat = "SELECT namees FROM expensecategories WHERE categoryid = $expenseCat";
		try
		{
			$result = $pdo3->prepare("$selectExpenseCat");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
		  	    $expenseCat = $row['namees'];
		} else {
			$selectExpenseCat = "SELECT nameen FROM expensecategories WHERE categoryid = $expenseCat";
		try
		{
			$result = $pdo3->prepare("$selectExpenseCat");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
		  	    $expenseCat = $row['nameen'];
		}
	}
		

	
	if ($moneysource == 1) {
		$source = $lang['global-till'];
	} else if ($moneysource == 2) {
		$source = $lang['global-bank'];
	} else if ($moneysource == 3) {
		$source = $other;
	} else {
		$source = 'ERROR';
	}
	
	if ($receipt == 1) {
		$recClass = "";
		$receipt = $lang['global-yes'];
	} else if ($receipt == 2) {
		$recClass = "negative";
		$receipt = $lang['global-no'];
	}
	
		$userDetails = "SELECT memberno, first_name from users WHERE user_id = $userid";
		try
		{
			$resultsT = $pdo3->prepare("$userDetails");
			$resultsT->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		while ($user = $resultsT->fetch()) {
			$member = "#" . $user['memberno'] . " - " . $user['first_name'];
		}

	
	
	$expense_row =	sprintf("
  	  <tr>
  	   <td class='clickableRow' href='expense.php?expenseid=%d'>%s</td>
  	   <td class='clickableRow' style='text-align: left;' href='expense.php?expenseid=%d'>%s</td>
  	   <td class='clickableRow' style='text-align: left;' href='expense.php?expenseid=%d'>%s</td>
  	   <td class='clickableRow' style='text-align: left;' href='expense.php?expenseid=%d'>%s</td>
  	   <td class='clickableRow' style='text-align: left;' href='expense.php?expenseid=%d'>%s</td>
  	   <td style='text-align: right;' class='clickableRow' href='expense.php?expenseid=%d'>%0.2f <span class='smallerfont'>{$_SESSION['currencyoperator']}</span></td>
  	   <td class='clickableRow' href='expense.php?expenseid=%d'>%s</td>
  	   <td class='clickableRow %s' href='expense.php?expenseid=%d'>%s</td>
	  </tr>",
	  $expense['expenseid'], $formattedDate, $expense['expenseid'], $expenseCat, $expense['expenseid'], $expense['expense'], $expense['expenseid'], $expense['shop'], $expense['expenseid'], $member, $expense['expenseid'], $expense['amount'], $expense['expenseid'], $source, $recClass, $expense['expenseid'], $receipt
	  );
	  $expenseDetails.= $expense_row;
  }
  
	  echo $productOverview;
	  echo $otherProducts;
	  echo $productDetails;
	  echo "</table>";
	  echo "</div";
	  echo "</div";

	  echo $expenseDetails;
	  echo "</table>";
	  echo "<center>";
  
}

 displayFooter();
