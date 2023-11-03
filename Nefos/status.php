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

	$result = mysql_query($selectMembers)
		or handleError($lang['error-loadsales'],"Error loading sale from db: " . mysql_error());
		
	$row = mysql_fetch_array($result);
		$currentmembers = $row['COUNT(memberno)'];

	// Active members today
	$selectMembers = "SELECT COUNT(memberno) from users WHERE (userGroup BETWEEN '1' AND '4') OR (userGroup = 5 AND paidUntil >= DATE(NOW()))";

	$result = mysql_query($selectMembers)
		or handleError($lang['error-loadsales'],"Error loading sale from db: " . mysql_error());
		
	$row = mysql_fetch_array($result);
		$activemembers = $row['COUNT(memberno)'];
	
	
	
	
	// New members today
	$newMembers = "SELECT COUNT(user_id) FROM users where DATE(registeredSince) = DATE(NOW())";

	$result = mysql_query($newMembers)
		or handleError($lang['error-loadnewmembers'],"Error loading sale from db: " . mysql_error());
	
	$row = mysql_fetch_array($result);
		$newmembers = $row['COUNT(user_id)'];

	// Banned members today
	$bannedmembers = "SELECT COUNT(user_id) FROM users where DATE(banTime) = DATE(NOW())";

	$result = mysql_query($bannedmembers)
		or handleError($lang['error-loadnewmembers'],"Error loading sale from db: " . mysql_error());
	
	$row = mysql_fetch_array($result);
		$bannedmembers = $row['COUNT(user_id)'];
		
	// Deleted members today
	$deletedmembers = "SELECT COUNT(user_id) FROM users where DATE(deleteTime) = DATE(NOW())";

	$result = mysql_query($deletedmembers)
		or handleError($lang['error-loadnewmembers'],"Error loading sale from db: " . mysql_error());
	
	$row = mysql_fetch_array($result);
		$deletedmembers = $row['COUNT(user_id)'];
		
		
	// Look up expired members today
	$selectMembers = "SELECT COUNT(memberno) FROM users WHERE DATE(paidUntil) = DATE_ADD(DATE(NOW()), INTERVAL -1 DAY)";

	$result = mysql_query($selectMembers)
	or handleError($lang['error-loadsales'],"Error loading sale from db: " . mysql_error());
	
	$row = mysql_fetch_array($result);
		$expiredmembers = $row['COUNT(memberno)'];
	
	// Look up renewed members		
	$selectMembers = "SELECT COUNT(m.paymentid) FROM memberpayments m, users u WHERE u.user_id = m.userid AND DATE(m.paymentdate) = DATE(NOW()) AND DATE(u.registeredSince) < DATE(NOW())";

	$result = mysql_query($selectMembers)
		or handleError($lang['error-loadsales'],"Error loading sale from db: " . mysql_error());
		
	$row = mysql_fetch_array($result);
		$renewedMembers = $row['COUNT(m.paymentid)'];
		
		
	// Look up member credit
	$newMembers = "SELECT SUM(credit) FROM users WHERE credit > 0 AND memberno <> '0' AND userGroup < 6 ";

	$result = mysql_query($newMembers)
		or handleError($lang['error-loadnewmembers'],"Error loading sale from db: " . mysql_error());
	
	$row = mysql_fetch_array($result);
		$totCredit = $row['SUM(credit)'];

		
	// Look up todays dispenses
	$selectSales = "SELECT SUM(amount), SUM(quantity), SUM(realQuantity), SUM(units) from sales WHERE DATE(saletime) = DATE(NOW())";

	$result = mysql_query($selectSales)
		or handleError($lang['error-dispenseload'],"Error loading sale from db: " . mysql_error());
		
	$row = mysql_fetch_array($result);
		$salesToday = $row['SUM(amount)'];
		$quantitySold = $row['SUM(quantity)'];
		$realquantitySold = $row['SUM(realQuantity)'];
		$unitsSold = $row['SUM(units)'];
		
	// Look up todays dispenses by category 1
	$selectSalesFlower = "SELECT SUM(d.amount), SUM(d.quantity), SUM(d.realQuantity) from sales s, salesdetails d WHERE s.saleid = d.saleid AND DATE(s.saletime) = DATE(NOW()) AND d.category = 1";

	$resultFlower = mysql_query($selectSalesFlower)
		or handleError($lang['error-dispenseload'],"Error loading sale from db: " . mysql_error());
	
	$row = mysql_fetch_array($resultFlower);
		$salesTodayFlower = $row['SUM(d.amount)'];
		$quantitySoldFlower = $row['SUM(d.quantity)'];
		$realquantitySoldFlower = $row['SUM(d.realQuantity)'];
		
	$flowerSalesPercentageToday = ($salesTodayFlower / $salesToday) * 100;
	$flowerGramsPercentageToday = ($quantitySoldFlower / $quantitySold) * 100;
	
	// Look up todays dispenses by category 2
	$selectSalesExtract = "SELECT SUM(d.amount), SUM(d.quantity), SUM(d.realQuantity) from sales s, salesdetails d WHERE s.saleid = d.saleid AND DATE(s.saletime) = DATE(NOW()) AND d.category = 2";

	$resultExtract = mysql_query($selectSalesExtract)
		or handleError($lang['error-dispenseload'],"Error loading sale from db: " . mysql_error());
	
	$row = mysql_fetch_array($resultExtract);
		$salesTodayExtract = $row['SUM(d.amount)'];
		$quantitySoldExtract = $row['SUM(d.quantity)'];
		$realquantitySoldExtract = $row['SUM(d.realQuantity)'];
		
	$extractSalesPercentageToday = ($salesTodayExtract / $salesToday) * 100;
	$extractGramsPercentageToday = ($quantitySoldExtract / $quantitySold) * 100;
	
	// Look up today's bar sales
	$selectBarSales = "SELECT SUM(amount), SUM(unitsTot) FROM b_sales WHERE DATE(saletime) = DATE(NOW())";

	$result = mysql_query($selectBarSales)
	or handleError($lang['error-loadsales'],"Error loading sale from db: " . mysql_error());
	
	$row = mysql_fetch_array($result);
		$barSales = $row['SUM(amount)'];
		$barUnits = $row['SUM(unitsTot)'];

	// Look up todays donations
	$selectDonations = "SELECT SUM(amount), COUNT(donationid) from donations WHERE (donatedTo = 0 OR donatedTo = 1) AND DATE(donationTime) = DATE(NOW())";

	$donationResult = mysql_query($selectDonations)
		or handleError($lang['error-donationload'],"Error loading donations from db: " . mysql_error());
		
	$row = mysql_fetch_array($donationResult);
		$donations = $row['SUM(amount)'];
		$donationsNo = $row['COUNT(donationid)'];
		
	// Look up todays bank donations
	$selectDonations = "SELECT SUM(amount), COUNT(donationid) from donations WHERE donatedTo = 2 AND DATE(donationTime) = DATE(NOW())";

	$donationResult = mysql_query($selectDonations)
		or handleError($lang['error-donationload'],"Error loading donations from db: " . mysql_error());
		
	$row = mysql_fetch_array($donationResult);
		$bankDonations = $row['SUM(amount)'];
		$bankDonationsNo = $row['COUNT(donationid)'];
		
	// Look up today's membership fees
	$selectMembershipFees = "SELECT SUM(amountPaid) FROM memberpayments WHERE paidTo <> 2 AND DATE(paymentdate) = DATE(NOW())";
				
	$result = mysql_query($selectMembershipFees)
		or handleError($lang['error-expensesload'],"Error loading expense from db: " . mysql_error());
			
	$row = mysql_fetch_array($result);
		$membershipFees = $row['SUM(amountPaid)'];
		
	// Look up today's membership fees Bank
	$selectMembershipFees = "SELECT SUM(amountPaid) FROM memberpayments WHERE paidTo = 2 AND DATE(paymentdate) = DATE(NOW())";
				
	$result = mysql_query($selectMembershipFees)
		or handleError($lang['error-expensesload'],"Error loading expense from db: " . mysql_error());
			
	$row = mysql_fetch_array($result);
		$membershipfeesBank = $row['SUM(amountPaid)'];

				
	// Calculate total income
	$totalIncome = $donations + $bankDonations + $membershipFees + $membershipfeesBank;
	
	// Look up today's till expenses
	$selectExpenses = "SELECT SUM(amount) FROM expenses WHERE DATE(registertime) = DATE(NOW()) AND moneysource = 1";
			
	$expenseResult = mysql_query($selectExpenses)
		or handleError($lang['error-expensesload'],"Error loading expense from db: " . mysql_error());
		
	$row = mysql_fetch_array($expenseResult);
		$tillExpenses = $row['SUM(amount)'];
		
	// Query to look up today's opening details + till + bank balance
	if ($_SESSION['openAndClose'] > 2) {
		
		$openingLookup = "SELECT openingid, openingtime, tillBalance, bankBalance FROM opening ORDER BY openingtime DESC LIMIT 1";
		
		$result = mysql_query($openingLookup)
			or handleError($lang['error-noopeningdetails'],"Error loading opening from db: " . mysql_error());
	
		$row = mysql_fetch_array($result);
			$tillBalance = $row['tillBalance'];	
			$bankBalance = $row['bankBalance'];
			$openingid = $row['openingid'];
			$openingtime = $row['openingtime'];
		
	} else if ($_SESSION['openAndClose'] == 2) {
		
		$openingLookup = "SELECT closingid, closingtime, cashintill, bankBalance FROM closing WHERE currentClosing = 0 ORDER BY closingtime DESC LIMIT 1";	
		
		$result = mysql_query($openingLookup)
			or handleError($lang['error-noopeningdetails'],"Error loading opening from db: " . mysql_error());
	
		$row = mysql_fetch_array($result);
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
			
	$expenseResult = mysql_query($selectExpenses)
		or handleError($lang['error-expensesload'],"Error loading expense from db: " . mysql_error());
		
	$row = mysql_fetch_array($expenseResult);
		$bankExpenses = $row['SUM(amount)'];

		
	// Calculate estimated till	& club balances
	$tillTot = $tillBalance + $donations + $membershipFees + $tillAdditions - $tillExpenses;
	$clubBalance = $tillTot + $bankBalance - $bankExpenses;
	

	pageStart("Status", NULL, NULL, "pstatus", "", "STATUS", $_SESSION['successMessage'], $_SESSION['errorMessage']);
	
	echo <<<EOD
<table style='color: #444; text-align: right; font-family: Tahoma, Verdana, sans-serif; font-size: 14px;'>
 <tr>
  <td colspan='8' style='color: #5aa242; text-align: left; font-size: 17px; border-bottom: 2px solid #a80082;'><strong>{$lang['bar-and-dispensary']}</strong></td>
 </tr>
 <tr>
  <td style='text-align: left;'>{$lang['dispensary']}</td>
  <td>{$expr(number_format($salesToday,2))} &euro;</td>
  <td></td>
  <td>{$expr(number_format($realquantitySold,2))} g.</td>
  <td>({$expr(number_format($quantitySold,2))} g.)</td>
  <td></td>
  <td>{$expr(number_format($unitsSold,2))} u.</td>
  <td></td>
 </tr>
 <tr>
  <td style='text-align: left;'><em>{$lang['closeday-owflowers']}</em></td>
  <td>{$expr(number_format($salesTodayFlower,2))} &euro;</td>
  <td>{$expr(number_format($flowerSalesPercentageToday,0))}%</td>
  <td>{$expr(number_format($realquantitySoldFlower,2))} g.</td>
  <td>({$expr(number_format($quantitySoldFlower,2))} g.)</td>
  <td>{$expr(number_format($flowerGramsPercentageToday,0))}%</td>
  <td></td>
 </tr>
 <tr>
  <td style='text-align: left;'><em>{$lang['closeday-owextracts']}</em></td>
  <td>{$expr(number_format($salesTodayExtract,2))} &euro;</td>
  <td>{$expr(number_format($extractSalesPercentageToday,0))}%</td>
  <td>{$expr(number_format($realquantitySoldExtract,2))} g.</td>
  <td>({$expr(number_format($quantitySoldExtract,2))} g.)</td>
  <td>{$expr(number_format($extractGramsPercentageToday,0))}%</td>
  <td></td>
 </tr>
EOD;
 
	// Query to look up categories
	$selectCats = "SELECT id, name from categories ORDER by id ASC";

	$resultCats = mysql_query($selectCats)
		or handleError($lang['error-loadflowers'],"Error loading flower from db: " . mysql_error());

		$i = 0;
		
	while ($category = mysql_fetch_array($resultCats)) {
		
		$categoryid = $category['id'];
		$name = $category['name'];
		
		// Create more product queries for each category - to be used further down!
		$customProducts .= " UNION ALL SELECT '$categoryid' AS category, pr.productid AS productid, pr.name AS name, p.purchaseid AS purchaseid, '' AS growtype, p.inMenu AS inMenu, p.closedAt AS closedAt, salesPrice AS gramPrice, p.realQuantity FROM products pr, purchases p WHERE p.category = $categoryid AND p.productid = pr.productid AND (p.closedAt IS NULL OR DATE(p.closingDate) >= DATE(NOW()))";
		
		
		// Look up sales in this cat
		$selectSalesOthers = "SELECT SUM(d.amount), SUM(d.quantity) from sales s, salesdetails d WHERE s.saleid = d.saleid AND DATE(s.saletime) = DATE(NOW()) AND d.category = $categoryid";
	
		$resultOthers = mysql_query($selectSalesOthers)
			or handleError($lang['error-dispenseload'],"Error loading sale from db: " . mysql_error());
		
		$row = mysql_fetch_array($resultOthers);
			$salesTodayOthers = $row['SUM(d.amount)'];
			$quantitySoldOthers = $row['SUM(d.quantity)'];
			
		$othersSalesPercentageToday = ($salesTodayOthers / $salesToday) * 100;
		$othersGramsPercentageToday = ($quantitySoldOthers / $unitsSold) * 100;

		
		echo <<<EOD
 <tr>
  <td style='text-align: left;'><em>{$lang['closeday-ow']} $name</em></td>
  <td>{$expr(number_format($salesTodayOthers,2))} &euro;</td>
  <td>{$expr(number_format($othersSalesPercentageToday,0))}%</td>
  <td></td>
  <td></td>
  <td></td>
  <td>{$expr(number_format($quantitySoldOthers,2))} u.</td>
  <td>{$expr(number_format($othersGramsPercentageToday,0))}%</td>
 </tr>
EOD;
		
		$i++;
	}

	echo <<<EOD
 <tr>
  <td colspan='7'></td>
 </tr>
 <tr>
  <td style='text-align: left;'>{$lang['bar']}</td>
  <td>{$expr(number_format($barSales,2))} &euro;</td>
  <td></td>
  <td></td>
  <td></td>
  <td></td>
  <td>{$expr(number_format($barUnits,2))} u.</td>
  <td></td>
 </tr>
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
  <td style='text-align: left;'>Total members</td>
  <td>$currentmembers</td>
  <td></td>
  <td></td>
  <td></td>
  <td></td>
 </tr>
 <tr>
  <td style='text-align: left;'>Active members</td>
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
  <td style='text-align: left;'>Expired members</td>
  <td>$expiredmembers</td>
  <td></td>
  <td></td>
  <td></td>
  <td></td>
 </tr>
 <tr>
  <td style='text-align: left;'>Banned members</td>
  <td>$bannedmembers</td>
  <td></td>
  <td></td>
  <td></td>
  <td></td>
 </tr>
 <tr>
  <td style='text-align: left;'>Deleted members</td>
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
  <td>{$expr(number_format($donations,2))} &euro;</td>
  <td style='text-align: left;'>($donationsNo)</td>
  <td></td>
  <td></td>
  <td></td>
 </tr>
 <tr>
  <td style='text-align: left;'>{$lang['closeday-donations-bank']}</td>
  <td>{$expr(number_format($bankDonations,2))} &euro;</td>
  <td style='text-align: left;'>($bankDonationsNo)</td>
  <td></td>
  <td></td>
  <td></td>
 </tr>
 <tr>
  <td style='text-align: left;'>{$lang['closeday-membershipfees-till']}</td>
  <td>{$expr(number_format($membershipFees,2))} &euro;</td>
  <td></td>
  <td></td>
  <td></td>
  <td></td>
 </tr>
 <tr>
  <td style='text-align: left; border-bottom: 1px dashed #ababab;'>{$lang['closeday-membershipfees-bank']}</td>
  <td style='border-bottom: 1px dashed #ababab;'>{$expr(number_format($membershipfeesBank,2))} &euro;</td>
  <td></td>
  <td></td>
  <td></td>
  <td></td>
 </tr>
 <tr>
  <td style='text-align: left; border-bottom: 1px solid #ababab;'>{$lang['closeday-totalincome']}</td>
  <td style='border-bottom: 1px solid #ababab;'>{$expr(number_format($totalIncome,2))} &euro;</td>
  <td></td>
  <td></td>
  <td></td>
  <td></td>
 </tr>
 <tr>
  <td style='text-align: left;'>{$lang['closeday-tillexpenses']}</td>
  <td>{$expr(number_format($tillExpenses,2))} &euro;</td>
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
  <td>{$expr(number_format($bankExpenses,2))} &euro;</td>
  <td></td>
  <td></td>
  <td></td>
  <td></td>
 </tr>
 <tr>
  <td style='text-align: left;'>Saldo de socios</td>
  <td>{$expr(number_format($totCredit,2))} &euro;</td>
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
  <td>{$expr(number_format($tillTot,2))} &euro;</td>
  <td></td>
  <td></td>
  <td></td>
  <td></td>
 </tr>
 <tr>
  <td style='text-align: left;'>{$lang['closeday-bankexpenses']}</td>
  <td>{$expr(number_format($bankExpenses,2))} &euro;</td>
  <td></td>
  <td></td>
  <td></td>
  <td></td>
 </tr>
 <tr>
  <td style='text-align: left;'>Credito de socios</td>
  <td>{$expr(number_format($totCredit,2))} &euro;</td>
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
			
	$resultProducts = mysql_query($selectProducts)
		or handleError($lang['error-prodprices'],"Error loading flower prices from db: " . mysql_error());
		

		
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
  <td style='text-align: left;'><strong>{$lang['global-name']}&nbsp;&nbsp;</strong></td>
  <td><strong>{$lang['closeday-jars']}&nbsp;&nbsp;</strong></td>
  <td><strong>{$lang['closeday-intstash']}&nbsp;&nbsp;</strong></td>
  <td><strong>{$lang['closeday-extstash']}&nbsp;&nbsp;</strong></td>
  <td><strong>{$lang['global-total']}&nbsp;&nbsp;</strong></td>
  <td><strong>{$lang['value']}&nbsp;&nbsp;</strong></td>
  <td><strong>{$lang['closeday-dispensed']}&nbsp;&nbsp;</strong></td>
  <td><strong>Status&nbsp;&nbsp;</strong></td>
 </tr>
EOD;

		
		while ($product = mysql_fetch_array($resultProducts)) {
			
			
			$category = $product['category'];
			$productid = $product['productid'];
			$name = $product['name'];
			$purchaseid = $product['purchaseid'];
			$growtype = $product['growtype'];
			$inMenu = $product['inMenu'];
			$closedAt = $product['closedAt'];
			$gramPrice = $product['gramPrice'];
			
			
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
				
				
				$growResult = mysql_query($growDetails)
					or handleError($lang['error-growtypeload'],"Error loading growtype: " . mysql_error());
					
				if(mysql_num_rows($growResult) == 0) {
					$growtype = '';
				} else {
				
				$rowGrow = mysql_fetch_array($growResult);
					$growtype = "(" . $rowGrow['growtype'] . ")";
					
				}
			}

			
			// Determine how to calculate weight and sales:
			if ($_SESSION['openAndClose'] == 0 || $noActiveOpening == 'true') {
				
				// Calculate Stock
				$selectSales = "SELECT SUM(quantity), SUM(realQuantity) FROM salesdetails WHERE purchaseid = $purchaseid";
			
				$sale = mysql_query($selectSales)
					or handleError($lang['error-expensesload'],"Error loading expense from db: " . mysql_error());
				
				$row = mysql_fetch_array($sale);
					$sales = $row['SUM(quantity)'];
					$realSales = $row['SUM(realQuantity)'];
		
				$selectPermAdditions = "SELECT SUM(quantity) FROM productmovements WHERE purchaseid = $purchaseid AND type = 1 AND (movementTypeid = 1 OR movementTypeid = 3 OR movementTypeid = 10)";
				$permAdditions = mysql_query($selectPermAdditions)
					or handleError($lang['error-loadprodadditions'],"Error loading expense from db: " . mysql_error());
					
					$row = mysql_fetch_array($permAdditions);
						$permAdditions = $row['SUM(quantity)'];
				
				$selectPermRemovals = "SELECT SUM(quantity) FROM productmovements WHERE purchaseid = $purchaseid AND type = 2 AND (movementTypeid = 4 OR movementTypeid = 7 OR movementTypeid = 8 OR movementTypeid = 9 OR movementTypeid = 11 OR movementTypeid = 13 OR movementTypeid = 14 OR movementTypeid = 15 OR movementTypeid = 16)";
				
				$permRemovals = mysql_query($selectPermRemovals)
					or handleError($lang['error-loadprodadditions'],"Error loading expense from db: " . mysql_error());
					
					$row = mysql_fetch_array($permRemovals);
						$permRemovals = $row['SUM(quantity)'];
						
				// Calculate what's in Internal stash
				$selectStashedInt = "SELECT SUM(quantity) FROM productmovements WHERE purchaseid = $purchaseid AND type = 2 AND (movementTypeid = 5 OR movementTypeid = 18)";
				$stashedInt = mysql_query($selectStashedInt)
					or handleError($lang['error-loadstash'],"Error loading stash details from db: " . mysql_error());
				
					$row = mysql_fetch_array($stashedInt);
						$stashedInt = $row['SUM(quantity)'];
					
				$selectUnStashedInt = "SELECT SUM(quantity) FROM productmovements WHERE purchaseid = $purchaseid AND type = 1 AND (movementTypeid = 12 OR movementTypeid = 17)";
				$unStashedInt = mysql_query($selectUnStashedInt)
					or handleError($lang['error-loadstash'],"Error loading stash details from db: " . mysql_error());
				
					$row = mysql_fetch_array($unStashedInt);
						$unStashedInt = $row['SUM(quantity)'];
			
						
					$inStashInt = $stashedInt - $unStashedInt;
					$inStashInt = $inStashInt;
			
			
				// Calculate what's in External stash
				$selectStashedExt = "SELECT SUM(quantity) FROM productmovements WHERE purchaseid = $purchaseid AND type = 2 AND (movementTypeid = 6 OR movementTypeid = 20)";
				$stashedExt = mysql_query($selectStashedExt)
					or handleError($lang['error-loadstash'],"Error loading stash details from db: " . mysql_error());
				
					$row = mysql_fetch_array($stashedExt);
						$stashedExt = $row['SUM(quantity)'];
					
				$selectUnStashedExt = "SELECT SUM(quantity) FROM productmovements WHERE purchaseid = $purchaseid AND type = 1 AND (movementTypeid = 2 OR movementTypeid = 19)";
				$unStashedExt = mysql_query($selectUnStashedExt)
					or handleError($lang['error-loadstash'],"Error loading stash details from db: " . mysql_error());
				
					$row = mysql_fetch_array($unStashedExt);
						$unStashedExt = $row['SUM(quantity)'];
		
					
				$inStashExt = $stashedExt - $unStashedExt;
				$inStashExt = $inStashExt;
				
				$inStash = $inStashInt + $inStashExt;
				$jarWeight = $product['realQuantity'] + $permAdditions - $realSales - $permRemovals - $inStash;
				
				$weightTotal = $jarWeight + $inStashInt + $inStashExt;
				$weightPrice = $weightTotal * $gramPrice;
				
				// Look up todays dispenses
				$selectSales = "SELECT SUM(d.quantity), SUM(d.realQuantity) FROM salesdetails d, sales s WHERE DATE(s.saletime) = DATE(NOW()) AND d.saleid = s.saleid AND d.purchaseid = $purchaseid";
	
				$result = mysql_query($selectSales)
					or handleError($lang['error-loadsales'],"Error loading sale from db: " . mysql_error());
			
				$row = mysql_fetch_array($result);
					$soldToday = $row['SUM(d.quantity)'];
					$realSoldToday = $row['SUM(d.realQuantity)'];


			} else {

				// Query to look up today's opening weight
				if ($_SESSION['openAndClose'] > 2) {
					
					$openingLookup = "SELECT d.weight FROM openingdetails d, opening o WHERE d.openingid = $openingid AND d.openingid = o.openingid AND category = $category AND purchaseid = $purchaseid";
					
				} else if ($_SESSION['openAndClose'] == 2) {
			
					$openingLookup = "SELECT d.weight FROM closingdetails d, closing o WHERE d.closingid = $openingid AND d.closingid = o.closingid AND category = $category AND purchaseid = $purchaseid";
					
				}
				
				$result = mysql_query($openingLookup)
					or handleError($lang['error-loadprodclosedetails'],"Error loading closing from db: " . mysql_error());
			
			
				// If opening weight doesn't exist, find purchaseweight!
				if(mysql_num_rows($result) == 0) {
	   				$purchaseLookup = "SELECT realQuantity from purchases where purchaseid = $purchaseid";
	   				
					$result = mysql_query($purchaseLookup)
						or handleError($lang['error-loadprodclosedetails'],"Error loading closing from db: " . mysql_error());
					
				}
				
				$row = mysql_fetch_array($result);
					$openingWeight = $row['0'];
					
				// Look up todays dispenses
				$selectSales = "SELECT SUM(d.quantity), SUM(d.realQuantity) FROM salesdetails d, sales s WHERE DATE(s.saletime) = DATE(NOW()) AND d.saleid = s.saleid AND d.purchaseid = $purchaseid";
	
				$result = mysql_query($selectSales)
					or handleError($lang['error-loadsales'],"Error loading sale from db: " . mysql_error());
			
				$row = mysql_fetch_array($result);
					$soldToday = $row['SUM(d.quantity)'];
					$realSoldToday = $row['SUM(d.realQuantity)'];
	
				// Look up additions and removals
				$selectAdditions = "SELECT SUM(quantity) FROM productmovements WHERE purchaseid = $purchaseid AND type = 1 AND movementtime > '$openingtime'";
				$selectRemovals = "SELECT SUM(quantity) FROM productmovements WHERE purchaseid = $purchaseid AND type = 2 AND movementtime > '$openingtime'";
			
				$additions = mysql_query($selectAdditions)
					or handleError($lang['error-loadprodadditions'],"Error loading expense from db: " . mysql_error());
			
				$row = mysql_fetch_array($additions);
					$addedToday = $row['SUM(quantity)'];
					
				$removals = mysql_query($selectRemovals)
					or handleError($lang['error-loadprodadditions'],"Error loading expense from db: " . mysql_error());
			
				$row = mysql_fetch_array($removals);
					$takeoutsToday = $row['SUM(quantity)'];
					
					
				// Calculate jar weight:
				$jarWeight = $openingWeight + $addedToday - $takeoutsToday - $realSoldToday;	
				
				
				// Calculate what's in Internal stash
				$selectStashedInt = "SELECT SUM(quantity) FROM productmovements WHERE purchaseid = $purchaseid AND type = 2 AND (movementTypeid = 5 OR movementTypeid = 18)";
				$stashedInt = mysql_query($selectStashedInt)
					or handleError($lang['error-loadstash'],"Error loading stash details from db: " . mysql_error());
				
					$row = mysql_fetch_array($stashedInt);
						$stashedInt = $row['SUM(quantity)'];
					
				$selectUnStashedInt = "SELECT SUM(quantity) FROM productmovements WHERE purchaseid = $purchaseid AND type = 1 AND (movementTypeid = 12 OR movementTypeid = 17)";
				$unStashedInt = mysql_query($selectUnStashedInt)
					or handleError($lang['error-loadstash'],"Error loading stash details from db: " . mysql_error());
				
					$row = mysql_fetch_array($unStashedInt);
						$unStashedInt = $row['SUM(quantity)'];
			
						
				$inStashInt = $stashedInt - $unStashedInt;
			
			
				// Calculate what's in External stash
				$selectStashedExt = "SELECT SUM(quantity) FROM productmovements WHERE purchaseid = $purchaseid AND type = 2 AND (movementTypeid = 6 OR movementTypeid = 20)";
				$stashedExt = mysql_query($selectStashedExt)
					or handleError($lang['error-loadstash'],"Error loading stash details from db: " . mysql_error());
				
					$row = mysql_fetch_array($stashedExt);
						$stashedExt = $row['SUM(quantity)'];
					
				$selectUnStashedExt = "SELECT SUM(quantity) FROM productmovements WHERE purchaseid = $purchaseid AND type = 1 AND (movementTypeid = 2 OR movementTypeid = 19)";
				$unStashedExt = mysql_query($selectUnStashedExt)
					or handleError($lang['error-loadstash'],"Error loading stash details from db: " . mysql_error());
				
					$row = mysql_fetch_array($unStashedExt);
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
  <td colspan='8' style='color: #a80082; text-align: center; font-size: 14px; border-bottom: 1px solid #dedede; border-top: 1px solid #dedede; margin-top: 10px;'><strong>{$lang['global-extractscaps']}</strong></td>
 </tr>
 <tr>
  <td style='text-align: left;'><strong>{$lang['global-name']}&nbsp;&nbsp;</strong></td>
  <td><strong>{$lang['closeday-jars']}&nbsp;&nbsp;</strong></td>
  <td><strong>{$lang['closeday-intstash']}&nbsp;&nbsp;</strong></td>
  <td><strong>{$lang['closeday-extstash']}&nbsp;&nbsp;</strong></td>
  <td><strong>{$lang['global-total']}&nbsp;&nbsp;</strong></td>
  <td><strong>{$lang['value']}&nbsp;&nbsp;</strong></td>
  <td><strong>{$lang['closeday-dispensed']}&nbsp;&nbsp;</strong></td>
  <td><strong>Status&nbsp;&nbsp;</strong></td>
 </tr>
EOD;
				$extractHeader = 'set';
				}
				
			} else {
				
				// Query to look up categories
				$selectCats = "SELECT id, name from categories WHERE id = $category";
			
				$resultCats = mysql_query($selectCats)
					or handleError($lang['error-loadflowers'],"Error loading flower from db: " . mysql_error());
					
				$row = mysql_fetch_array($resultCats);
		  	    	$catName = $row['name'];
		  	    	$catID = $row['id'];
		  	    	
				if (${'otherHeader' . $catID} != 'set') {
					$productDetails .= <<<EOD
 <tr>
  <td colspan='8'></td>
 </tr>
 <tr>
  <td colspan='8' style='color: #a80082; text-align: center; font-size: 14px; border-bottom: 1px solid #dedede; border-top: 1px solid #dedede; margin-top: 10px;'><strong>$catName</strong></td>
 </tr>
 <tr>
  <td style='text-align: left;'><strong>{$lang['global-name']}&nbsp;&nbsp;</strong></td>
  <td><strong>Stock&nbsp;&nbsp;</strong></td>
  <td><strong>{$lang['closeday-intstash']}&nbsp;&nbsp;</strong></td>
  <td><strong>{$lang['closeday-extstash']}&nbsp;&nbsp;</strong></td>
  <td><strong>{$lang['global-total']}&nbsp;&nbsp;</strong></td>
  <td><strong>{$lang['value']}&nbsp;&nbsp;</strong></td>
  <td><strong>{$lang['closeday-dispensed']}&nbsp;&nbsp;</strong></td>
  <td><strong>Status&nbsp;&nbsp;</strong></td>
 </tr>
EOD;
				${'otherHeader' . $catID} = 'set';
				}
					
					
					$otherTotals[$catID]['catName'] = $catName;
					$otherTotals[$catID]['otherTotJar'] = $otherTotals[$catID]['otherTotJar'] + $jarWeight;
					$otherTotals[$catID]['otherTotIntSt'] = $otherTotals[$catID]['otherTotIntSt'] + $inStashInt;
					$otherTotals[$catID]['otherTotExtSt'] = $otherTotals[$catID]['otherTotExtSt'] + $inStashExt;
					$otherTotals[$catID]['otherTot'] = $otherTotals[$catID]['otherTotJar'] + $otherTotals[$catID]['otherTotIntSt'] + $otherTotals[$catID]['otherTotExtSt'];
					$otherTotals[$catID]['otherSoldToday'] = $otherTotals[$catID]['otherSoldToday'] + $realSoldToday;
					



				}
			
		  	if ($category < 3) {
				$productDetails .= <<<EOD
 <tr>
  <td style='text-align: left;'>{$name} <span class='smallerfont3'>{$growtype}</span></td>
  <td>{$expr(number_format($jarWeight,2))} g.</td>
  <td>{$expr(number_format($inStashInt,2))} g.</td>
  <td>{$expr(number_format($inStashExt,2))} g.</td>
  <td><strong>{$expr(number_format($weightTotal,2))} g.</strong></td>
  <td>{$expr(number_format($weightPrice,2))} &euro;</td>
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
  <td>{$expr(number_format($weightPrice,2))} &euro;</td>
  <td>{$expr(number_format($soldToday,2))} u.</td>
  <td>{$productStatus}</td>
 </tr>
EOD;
	  		}
		} // End product loop
		
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
  <td>{$expr(number_format($flowerWeightPrice,2))} &euro;</td>
  <td>{$expr(number_format($flowerSoldToday,2))} g.</td>
 </tr>
 <tr>
  <td style='text-align: left;'>{$lang['global-extracts']}</td>
  <td>{$expr(number_format($extractTotJar,2))} g.</td>
  <td>{$expr(number_format($extractTotIntSt,2))} g.</td>
  <td>{$expr(number_format($extractTotExtSt,2))} g.</td>
  <td><strong>{$expr(number_format($extractTot,2))} g.</strong></td>
  <td>{$expr(number_format($extractWeightPrice,2))} &euro;</td>
  <td>{$expr(number_format($extractSoldToday,2))} g.</td>
 </tr>
 <tr style='border-top: 1px solid #888;'>
  <td style='text-align: left;'><strong>TOTAL</strong></td>
  <td><strong>{$expr(number_format($fullTotJar,2))} g.</strong></td>
  <td><strong>{$expr(number_format($fullTotIntSt,2))} g.</strong></td>
  <td><strong>{$expr(number_format($fullTotExtSt,2))} g.</strong></td>
  <td><strong>{$expr(number_format($fullTot,2))} g.</strong></td>
  <td><strong>{$expr(number_format($fullWeightPrice,2))} &euro;</strong></td>
  <td><strong>{$expr(number_format($fullSoldToday,2))} g.</strong></td>
 </tr>
EOD;

  
  
		// Query to look up expenses
	$selectExpenses = "SELECT expenseid, registertime, userid, expensetype, expense, moneysource, other, amount, shop, comment, receipt, expensecategory FROM expenses WHERE DATE(registertime) = DATE(NOW()) ORDER by registertime DESC";

	$result = mysql_query($selectExpenses)
		or handleError($lang['error-expensesload'],"Error loading expense from db: " . mysql_error());
	$result2 = mysql_query($selectExpenses)
		or handleError($lang['error-expensesload'],"Error loading expense from db: " . mysql_error());

		
		
		$expenseDetails .= <<<EOD
<br />
<table style='color: #444; text-align: right; font-family: Tahoma, Verdana, sans-serif; font-size: 14px;' cellpadding='5'>
 <tr>
  <td colspan='8' style='color: #5aa242; text-align: left; font-size: 17px; border-bottom: 2px solid #a80082;'><strong>{$lang['global-expensescaps']}</strong></td>
 </tr>
	   <tr>
	    <td style='text-align: center;'><strong>{$lang['global-time']}</strong></td>
	    <td style='text-align: center;'><strong>{$lang['global-category']}</strong></td>
	    <td style='text-align: center;'><strong>{$lang['global-expense']}</strong></td>
	    <td style='text-align: center;'><strong>{$lang['global-shop']}</strong></td>
	    <td style='text-align: center;'><strong>{$lang['global-member']}</strong></td>
	    <td style='text-align: center;'><strong>{$lang['global-amount']}</strong></td>
	    <td style='text-align: center;'><strong>{$lang['global-source']}</strong></td>
	    <td style='text-align: center;'><strong>{$lang['global-receipt']}?</strong></td>
	   </tr>
EOD;


while ($expense = mysql_fetch_array($result2)) {
	
	
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
			$catResult = mysql_query($selectExpenseCat)
				or handleError($lang['error-expensesload'],"Error loading expense from db: " . mysql_error());
			$row = mysql_fetch_array($catResult);
		  	    $expenseCat = $row['namees'];
		} else {
			$selectExpenseCat = "SELECT nameen FROM expensecategories WHERE categoryid = $expenseCat";
			$catResult = mysql_query($selectExpenseCat)
				or handleError($lang['error-expensesload'],"Error loading expense from db: " . mysql_error());
			$row = mysql_fetch_array($catResult);
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
		$result = mysql_query($userDetails)
			or handleError($lang['error-usersload'],"Error loading users from db: " . mysql_error());
			
		while ($user = mysql_fetch_array($result)) {
			$member = "#" . $user['memberno'] . " - " . $user['first_name'];
		}

	
	
	$expense_row =	sprintf("
  	  <tr>
  	   <td class='clickableRow' href='expense.php?expenseid=%d'>%s</td>
  	   <td class='clickableRow' style='text-align: left;' href='expense.php?expenseid=%d'>%s</td>
  	   <td class='clickableRow' style='text-align: left;' href='expense.php?expenseid=%d'>%s</td>
  	   <td class='clickableRow' style='text-align: left;' href='expense.php?expenseid=%d'>%s</td>
  	   <td class='clickableRow' style='text-align: left;' href='expense.php?expenseid=%d'>%s</td>
  	   <td style='text-align: right;' class='clickableRow' href='expense.php?expenseid=%d'>%0.2f <span class='smallerfont'>&euro;</span></td>
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
	  echo $expenseDetails;
	  echo "</table>";

	  
	  
	  
	  
	  
	  
	  
	  
	  
	  
} else {
	
	
	
	
	
	
	
	
	
		
	// Total members today
	$selectMembers = "SELECT COUNT(memberno) from users WHERE memberno <> '0' AND userGroup < 6";

	$result = mysql_query($selectMembers)
		or handleError($lang['error-loadsales'],"Error loading sale from db: " . mysql_error());
		
	$row = mysql_fetch_array($result);
		$currentmembers = $row['COUNT(memberno)'];

	// Active members today
	$selectMembers = "SELECT COUNT(memberno) from users WHERE (userGroup BETWEEN '1' AND '4') OR (userGroup = 5 AND paidUntil >= DATE(NOW()))";

	$result = mysql_query($selectMembers)
		or handleError($lang['error-loadsales'],"Error loading sale from db: " . mysql_error());
		
	$row = mysql_fetch_array($result);
		$activemembers = $row['COUNT(memberno)'];
	
	
	
	
	// New members today
	$newMembers = "SELECT COUNT(user_id) FROM users where DATE(registeredSince) = DATE(NOW())";

	$result = mysql_query($newMembers)
		or handleError($lang['error-loadnewmembers'],"Error loading sale from db: " . mysql_error());
	
	$row = mysql_fetch_array($result);
		$newmembers = $row['COUNT(user_id)'];

	// Banned members today
	$bannedmembers = "SELECT COUNT(user_id) FROM users where DATE(banTime) = DATE(NOW())";

	$result = mysql_query($bannedmembers)
		or handleError($lang['error-loadnewmembers'],"Error loading sale from db: " . mysql_error());
	
	$row = mysql_fetch_array($result);
		$bannedmembers = $row['COUNT(user_id)'];
		
	// Deleted members today
	$deletedmembers = "SELECT COUNT(user_id) FROM users where DATE(deleteTime) = DATE(NOW())";

	$result = mysql_query($deletedmembers)
		or handleError($lang['error-loadnewmembers'],"Error loading sale from db: " . mysql_error());
	
	$row = mysql_fetch_array($result);
		$deletedmembers = $row['COUNT(user_id)'];
		
		
	// Look up expired members today
	$selectMembers = "SELECT COUNT(memberno) FROM users WHERE DATE(paidUntil) = DATE_ADD(DATE(NOW()), INTERVAL -1 DAY)";

	$result = mysql_query($selectMembers)
	or handleError($lang['error-loadsales'],"Error loading sale from db: " . mysql_error());
	
	$row = mysql_fetch_array($result);
		$expiredmembers = $row['COUNT(memberno)'];
	
	// Look up renewed members		
	$selectMembers = "SELECT COUNT(m.paymentid) FROM memberpayments m, users u WHERE u.user_id = m.userid AND DATE(m.paymentdate) = DATE(NOW()) AND DATE(u.registeredSince) < DATE(NOW())";

	$result = mysql_query($selectMembers)
		or handleError($lang['error-loadsales'],"Error loading sale from db: " . mysql_error());
		
	$row = mysql_fetch_array($result);
		$renewedMembers = $row['COUNT(m.paymentid)'];
		
		
	// Look up member credit
	$newMembers = "SELECT SUM(credit) FROM users WHERE credit > 0 AND memberno <> '0' AND userGroup < 6 ";

	$result = mysql_query($newMembers)
		or handleError($lang['error-loadnewmembers'],"Error loading sale from db: " . mysql_error());
	
	$row = mysql_fetch_array($result);
		$totCredit = $row['SUM(credit)'];

		
	// Look up todays dispenses
	$selectSales = "SELECT SUM(amount), SUM(quantity), SUM(realQuantity), SUM(units) from sales WHERE DATE(saletime) = DATE(NOW())";

	$result = mysql_query($selectSales)
		or handleError($lang['error-dispenseload'],"Error loading sale from db: " . mysql_error());
		
	$row = mysql_fetch_array($result);
		$salesToday = $row['SUM(amount)'];
		$quantitySold = $row['SUM(quantity)'];
		$realquantitySold = $row['SUM(realQuantity)'];
		$unitsSold = $row['SUM(units)'];
		
	// Look up todays dispenses by category 1
	$selectSalesFlower = "SELECT SUM(d.amount), SUM(d.quantity), SUM(d.realQuantity) from sales s, salesdetails d WHERE s.saleid = d.saleid AND DATE(s.saletime) = DATE(NOW()) AND d.category = 1";

	$resultFlower = mysql_query($selectSalesFlower)
		or handleError($lang['error-dispenseload'],"Error loading sale from db: " . mysql_error());
	
	$row = mysql_fetch_array($resultFlower);
		$salesTodayFlower = $row['SUM(d.amount)'];
		$quantitySoldFlower = $row['SUM(d.quantity)'];
		$realquantitySoldFlower = $row['SUM(d.realQuantity)'];
		
	$flowerSalesPercentageToday = ($salesTodayFlower / $salesToday) * 100;
	$flowerGramsPercentageToday = ($quantitySoldFlower / $quantitySold) * 100;
	
	// Look up todays dispenses by category 2
	$selectSalesExtract = "SELECT SUM(d.amount), SUM(d.quantity), SUM(d.realQuantity) from sales s, salesdetails d WHERE s.saleid = d.saleid AND DATE(s.saletime) = DATE(NOW()) AND d.category = 2";

	$resultExtract = mysql_query($selectSalesExtract)
		or handleError($lang['error-dispenseload'],"Error loading sale from db: " . mysql_error());
	
	$row = mysql_fetch_array($resultExtract);
		$salesTodayExtract = $row['SUM(d.amount)'];
		$quantitySoldExtract = $row['SUM(d.quantity)'];
		$realquantitySoldExtract = $row['SUM(d.realQuantity)'];
		
	$extractSalesPercentageToday = ($salesTodayExtract / $salesToday) * 100;
	$extractGramsPercentageToday = ($quantitySoldExtract / $quantitySold) * 100;
	
	// Look up today's bar sales
	$selectBarSales = "SELECT SUM(amount), SUM(unitsTot) FROM b_sales WHERE DATE(saletime) = DATE(NOW())";

	$result = mysql_query($selectBarSales)
	or handleError($lang['error-loadsales'],"Error loading sale from db: " . mysql_error());
	
	$row = mysql_fetch_array($result);
		$barSales = $row['SUM(amount)'];
		$barUnits = $row['SUM(unitsTot)'];

	// Look up todays donations
	$selectDonations = "SELECT SUM(amount), COUNT(donationid) from donations WHERE (donatedTo = 0 OR donatedTo = 1) AND DATE(donationTime) = DATE(NOW())";

	$donationResult = mysql_query($selectDonations)
		or handleError($lang['error-donationload'],"Error loading donations from db: " . mysql_error());
		
	$row = mysql_fetch_array($donationResult);
		$donations = $row['SUM(amount)'];
		$donationsNo = $row['COUNT(donationid)'];
		
	// Look up todays bank donations
	$selectDonations = "SELECT SUM(amount), COUNT(donationid) from donations WHERE donatedTo = 2 AND DATE(donationTime) = DATE(NOW())";

	$donationResult = mysql_query($selectDonations)
		or handleError($lang['error-donationload'],"Error loading donations from db: " . mysql_error());
		
	$row = mysql_fetch_array($donationResult);
		$bankDonations = $row['SUM(amount)'];
		$bankDonationsNo = $row['COUNT(donationid)'];
		
	// Look up today's membership fees
	$selectMembershipFees = "SELECT SUM(amountPaid) FROM memberpayments WHERE paidTo <> 2 AND DATE(paymentdate) = DATE(NOW())";
				
	$result = mysql_query($selectMembershipFees)
		or handleError($lang['error-expensesload'],"Error loading expense from db: " . mysql_error());
			
	$row = mysql_fetch_array($result);
		$membershipFees = $row['SUM(amountPaid)'];
		
	// Look up today's membership fees Bank
	$selectMembershipFees = "SELECT SUM(amountPaid) FROM memberpayments WHERE paidTo = 2 AND DATE(paymentdate) = DATE(NOW())";
				
	$result = mysql_query($selectMembershipFees)
		or handleError($lang['error-expensesload'],"Error loading expense from db: " . mysql_error());
			
	$row = mysql_fetch_array($result);
		$membershipfeesBank = $row['SUM(amountPaid)'];

				
	// Calculate total income
	$totalIncome = $donations + $bankDonations + $membershipFees + $membershipfeesBank;
	
	// Look up today's till expenses
	$selectExpenses = "SELECT SUM(amount) FROM expenses WHERE DATE(registertime) = DATE(NOW()) AND moneysource = 1";
			
	$expenseResult = mysql_query($selectExpenses)
		or handleError($lang['error-expensesload'],"Error loading expense from db: " . mysql_error());
		
	$row = mysql_fetch_array($expenseResult);
		$tillExpenses = $row['SUM(amount)'];
		
	// Query to look up today's opening details + till + bank balance
	if ($_SESSION['openAndClose'] > 2) {
		
		$openingLookup = "SELECT openingid, openingtime, tillBalance, bankBalance FROM opening ORDER BY openingtime DESC LIMIT 1";
		
		$result = mysql_query($openingLookup)
			or handleError($lang['error-noopeningdetails'],"Error loading opening from db: " . mysql_error());
	
		$row = mysql_fetch_array($result);
			$tillBalance = $row['tillBalance'];	
			$bankBalance = $row['bankBalance'];
			$openingid = $row['openingid'];
			$openingtime = $row['openingtime'];
		
	} else if ($_SESSION['openAndClose'] == 2) {
		
		$openingLookup = "SELECT closingid, closingtime, cashintill, bankBalance FROM closing WHERE currentClosing = 0 ORDER BY closingtime DESC LIMIT 1";	
		
		$result = mysql_query($openingLookup)
			or handleError($lang['error-noopeningdetails'],"Error loading opening from db: " . mysql_error());
	
		$row = mysql_fetch_array($result);
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
			
	$expenseResult = mysql_query($selectExpenses)
		or handleError($lang['error-expensesload'],"Error loading expense from db: " . mysql_error());
		
	$row = mysql_fetch_array($expenseResult);
		$bankExpenses = $row['SUM(amount)'];

		
	// Calculate estimated till	& club balances
	$tillTot = $tillBalance + $donations + $membershipFees + $tillAdditions - $tillExpenses;
	$clubBalance = $tillTot + $bankBalance - $bankExpenses;
	

	pageStart("Status", NULL, NULL, "pstatus", "", "STATUS", $_SESSION['successMessage'], $_SESSION['errorMessage']);
	
	echo <<<EOD
<table style='color: #444; text-align: right; font-family: Tahoma, Verdana, sans-serif; font-size: 14px;'>
 <tr>
  <td colspan='8' style='color: #5aa242; text-align: left; font-size: 17px; border-bottom: 2px solid #a80082;'><strong>{$lang['bar-and-dispensary']}</strong></td>
 </tr>
 <tr>
  <td style='text-align: left;'>{$lang['dispensary']}</td>
  <td>{$expr(number_format($salesToday,2))} &euro;</td>
  <td></td>
  <td>{$expr(number_format($quantitySold,2))} g.</td>
  <td></td>
  <td></td>
  <td>{$expr(number_format($unitsSold,2))} u.</td>
  <td></td>
 </tr>
 <tr>
  <td style='text-align: left;'><em>{$lang['closeday-owflowers']}</em></td>
  <td>{$expr(number_format($salesTodayFlower,2))} &euro;</td>
  <td>{$expr(number_format($flowerSalesPercentageToday,0))}%</td>
  <td>{$expr(number_format($quantitySoldFlower,2))} g.</td>
  <td></td>
  <td>{$expr(number_format($flowerGramsPercentageToday,0))}%</td>
  <td></td>
 </tr>
 <tr>
  <td style='text-align: left;'><em>{$lang['closeday-owextracts']}</em></td>
  <td>{$expr(number_format($salesTodayExtract,2))} &euro;</td>
  <td>{$expr(number_format($extractSalesPercentageToday,0))}%</td>
  <td>{$expr(number_format($quantitySoldExtract,2))} g.</td>
  <td></td>
  <td>{$expr(number_format($extractGramsPercentageToday,0))}%</td>
  <td></td>
 </tr>
EOD;
 
	// Query to look up categories
	$selectCats = "SELECT id, name from categories ORDER by id ASC";

	$resultCats = mysql_query($selectCats)
		or handleError($lang['error-loadflowers'],"Error loading flower from db: " . mysql_error());

		$i = 0;
		
	while ($category = mysql_fetch_array($resultCats)) {
		
		$categoryid = $category['id'];
		$name = $category['name'];
		
		// Create more product queries for each category - to be used further down!
		$customProducts .= " UNION ALL SELECT '$categoryid' AS category, pr.productid AS productid, pr.name AS name, p.purchaseid AS purchaseid, '' AS growtype, p.inMenu AS inMenu, p.closedAt AS closedAt, salesPrice AS gramPrice, p.realQuantity FROM products pr, purchases p WHERE p.category = $categoryid AND p.productid = pr.productid AND (p.closedAt IS NULL OR DATE(p.closingDate) >= DATE(NOW()))";
		
		
		// Look up sales in this cat
		$selectSalesOthers = "SELECT SUM(d.amount), SUM(d.quantity) from sales s, salesdetails d WHERE s.saleid = d.saleid AND DATE(s.saletime) = DATE(NOW()) AND d.category = $categoryid";
	
		$resultOthers = mysql_query($selectSalesOthers)
			or handleError($lang['error-dispenseload'],"Error loading sale from db: " . mysql_error());
		
		$row = mysql_fetch_array($resultOthers);
			$salesTodayOthers = $row['SUM(d.amount)'];
			$quantitySoldOthers = $row['SUM(d.quantity)'];
			
		$othersSalesPercentageToday = ($salesTodayOthers / $salesToday) * 100;
		$othersGramsPercentageToday = ($quantitySoldOthers / $unitsSold) * 100;

		
		echo <<<EOD
 <tr>
  <td style='text-align: left;'><em>{$lang['closeday-ow']} $name</em></td>
  <td>{$expr(number_format($salesTodayOthers,2))} &euro;</td>
  <td>{$expr(number_format($othersSalesPercentageToday,0))}%</td>
  <td></td>
  <td></td>
  <td></td>
  <td>{$expr(number_format($quantitySoldOthers,2))} u.</td>
  <td>{$expr(number_format($othersGramsPercentageToday,0))}%</td>
 </tr>
EOD;
		
		$i++;
	}

	echo <<<EOD
 <tr>
  <td colspan='7'></td>
 </tr>
 <tr>
  <td style='text-align: left;'>{$lang['bar']}</td>
  <td>{$expr(number_format($barSales,2))} &euro;</td>
  <td></td>
  <td></td>
  <td></td>
  <td></td>
  <td>{$expr(number_format($barUnits,2))} u.</td>
  <td></td>
 </tr>
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
  <td style='text-align: left;'>Total members</td>
  <td>$currentmembers</td>
  <td></td>
  <td></td>
  <td></td>
  <td></td>
 </tr>
 <tr>
  <td style='text-align: left;'>Active members</td>
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
  <td style='text-align: left;'>Expired members</td>
  <td>$expiredmembers</td>
  <td></td>
  <td></td>
  <td></td>
  <td></td>
 </tr>
 <tr>
  <td style='text-align: left;'>Banned members</td>
  <td>$bannedmembers</td>
  <td></td>
  <td></td>
  <td></td>
  <td></td>
 </tr>
 <tr>
  <td style='text-align: left;'>Deleted members</td>
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
  <td>{$expr(number_format($donations,2))} &euro;</td>
  <td style='text-align: left;'>($donationsNo)</td>
  <td></td>
  <td></td>
  <td></td>
 </tr>
 <tr>
  <td style='text-align: left;'>{$lang['closeday-donations-bank']}</td>
  <td>{$expr(number_format($bankDonations,2))} &euro;</td>
  <td style='text-align: left;'>($bankDonationsNo)</td>
  <td></td>
  <td></td>
  <td></td>
 </tr>
 <tr>
  <td style='text-align: left;'>{$lang['closeday-membershipfees-till']}</td>
  <td>{$expr(number_format($membershipFees,2))} &euro;</td>
  <td></td>
  <td></td>
  <td></td>
  <td></td>
 </tr>
 <tr>
  <td style='text-align: left; border-bottom: 1px dashed #ababab;'>{$lang['closeday-membershipfees-bank']}</td>
  <td style='border-bottom: 1px dashed #ababab;'>{$expr(number_format($membershipfeesBank,2))} &euro;</td>
  <td></td>
  <td></td>
  <td></td>
  <td></td>
 </tr>
 <tr>
  <td style='text-align: left; border-bottom: 1px solid #ababab;'>{$lang['closeday-totalincome']}</td>
  <td style='border-bottom: 1px solid #ababab;'>{$expr(number_format($totalIncome,2))} &euro;</td>
  <td></td>
  <td></td>
  <td></td>
  <td></td>
 </tr>
 <tr>
  <td style='text-align: left;'>{$lang['closeday-tillexpenses']}</td>
  <td>{$expr(number_format($tillExpenses,2))} &euro;</td>
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
  <td>{$expr(number_format($bankExpenses,2))} &euro;</td>
  <td></td>
  <td></td>
  <td></td>
  <td></td>
 </tr>
 <tr>
  <td style='text-align: left;'>Saldo de socios</td>
  <td>{$expr(number_format($totCredit,2))} &euro;</td>
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
  <td>{$expr(number_format($tillTot,2))} &euro;</td>
  <td></td>
  <td></td>
  <td></td>
  <td></td>
 </tr>
 <tr>
  <td style='text-align: left;'>{$lang['closeday-bankexpenses']}</td>
  <td>{$expr(number_format($bankExpenses,2))} &euro;</td>
  <td></td>
  <td></td>
  <td></td>
  <td></td>
 </tr>
 <tr>
  <td style='text-align: left;'>Credito de socios</td>
  <td>{$expr(number_format($totCredit,2))} &euro;</td>
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
			
	$resultProducts = mysql_query($selectProducts)
		or handleError($lang['error-prodprices'],"Error loading flower prices from db: " . mysql_error());
		

		
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
  <td style='text-align: left;'><strong>{$lang['global-name']}&nbsp;&nbsp;</strong></td>
  <td><strong>{$lang['closeday-jars']}&nbsp;&nbsp;</strong></td>
  <td><strong>{$lang['closeday-intstash']}&nbsp;&nbsp;</strong></td>
  <td><strong>{$lang['closeday-extstash']}&nbsp;&nbsp;</strong></td>
  <td><strong>{$lang['global-total']}&nbsp;&nbsp;</strong></td>
  <td><strong>{$lang['value']}&nbsp;&nbsp;</strong></td>
  <td><strong>{$lang['closeday-dispensed']}&nbsp;&nbsp;</strong></td>
  <td><strong>Status&nbsp;&nbsp;</strong></td>
 </tr>
EOD;

		
		while ($product = mysql_fetch_array($resultProducts)) {
			
			
			$category = $product['category'];
			$productid = $product['productid'];
			$name = $product['name'];
			$purchaseid = $product['purchaseid'];
			$growtype = $product['growtype'];
			$inMenu = $product['inMenu'];
			$closedAt = $product['closedAt'];
			$gramPrice = $product['gramPrice'];
			
			
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
				
				
				$growResult = mysql_query($growDetails)
					or handleError($lang['error-growtypeload'],"Error loading growtype: " . mysql_error());
					
				if(mysql_num_rows($growResult) == 0) {
					$growtype = '';
				} else {
				
				$rowGrow = mysql_fetch_array($growResult);
					$growtype = "(" . $rowGrow['growtype'] . ")";
					
				}
			}

			
			// Determine how to calculate weight and sales:
			if ($_SESSION['openAndClose'] == 0 || $noActiveOpening == 'true') {
				
				// Calculate Stock
				$selectSales = "SELECT SUM(quantity), SUM(realQuantity) FROM salesdetails WHERE purchaseid = $purchaseid";
			
				$sale = mysql_query($selectSales)
					or handleError($lang['error-expensesload'],"Error loading expense from db: " . mysql_error());
				
				$row = mysql_fetch_array($sale);
					$sales = $row['SUM(quantity)'];
					$realSales = $row['SUM(realQuantity)'];
		
				$selectPermAdditions = "SELECT SUM(quantity) FROM productmovements WHERE purchaseid = $purchaseid AND type = 1 AND (movementTypeid = 1 OR movementTypeid = 3 OR movementTypeid = 10)";
				$permAdditions = mysql_query($selectPermAdditions)
					or handleError($lang['error-loadprodadditions'],"Error loading expense from db: " . mysql_error());
					
					$row = mysql_fetch_array($permAdditions);
						$permAdditions = $row['SUM(quantity)'];
				
				$selectPermRemovals = "SELECT SUM(quantity) FROM productmovements WHERE purchaseid = $purchaseid AND type = 2 AND (movementTypeid = 4 OR movementTypeid = 7 OR movementTypeid = 8 OR movementTypeid = 9 OR movementTypeid = 11 OR movementTypeid = 13 OR movementTypeid = 14 OR movementTypeid = 15 OR movementTypeid = 16)";
				
				$permRemovals = mysql_query($selectPermRemovals)
					or handleError($lang['error-loadprodadditions'],"Error loading expense from db: " . mysql_error());
					
					$row = mysql_fetch_array($permRemovals);
						$permRemovals = $row['SUM(quantity)'];
						
				// Calculate what's in Internal stash
				$selectStashedInt = "SELECT SUM(quantity) FROM productmovements WHERE purchaseid = $purchaseid AND type = 2 AND (movementTypeid = 5 OR movementTypeid = 18)";
				$stashedInt = mysql_query($selectStashedInt)
					or handleError($lang['error-loadstash'],"Error loading stash details from db: " . mysql_error());
				
					$row = mysql_fetch_array($stashedInt);
						$stashedInt = $row['SUM(quantity)'];
					
				$selectUnStashedInt = "SELECT SUM(quantity) FROM productmovements WHERE purchaseid = $purchaseid AND type = 1 AND (movementTypeid = 12 OR movementTypeid = 17)";
				$unStashedInt = mysql_query($selectUnStashedInt)
					or handleError($lang['error-loadstash'],"Error loading stash details from db: " . mysql_error());
				
					$row = mysql_fetch_array($unStashedInt);
						$unStashedInt = $row['SUM(quantity)'];
			
						
					$inStashInt = $stashedInt - $unStashedInt;
					$inStashInt = $inStashInt;
			
			
				// Calculate what's in External stash
				$selectStashedExt = "SELECT SUM(quantity) FROM productmovements WHERE purchaseid = $purchaseid AND type = 2 AND (movementTypeid = 6 OR movementTypeid = 20)";
				$stashedExt = mysql_query($selectStashedExt)
					or handleError($lang['error-loadstash'],"Error loading stash details from db: " . mysql_error());
				
					$row = mysql_fetch_array($stashedExt);
						$stashedExt = $row['SUM(quantity)'];
					
				$selectUnStashedExt = "SELECT SUM(quantity) FROM productmovements WHERE purchaseid = $purchaseid AND type = 1 AND (movementTypeid = 2 OR movementTypeid = 19)";
				$unStashedExt = mysql_query($selectUnStashedExt)
					or handleError($lang['error-loadstash'],"Error loading stash details from db: " . mysql_error());
				
					$row = mysql_fetch_array($unStashedExt);
						$unStashedExt = $row['SUM(quantity)'];
		
					
				$inStashExt = $stashedExt - $unStashedExt;
				$inStashExt = $inStashExt;
				
				$inStash = $inStashInt + $inStashExt;
				$jarWeight = $product['realQuantity'] + $permAdditions - $sales - $permRemovals - $inStash;
				
				$weightTotal = $jarWeight + $inStashInt + $inStashExt;
				$weightPrice = $weightTotal * $gramPrice;
				
				// Look up todays dispenses
				$selectSales = "SELECT SUM(d.quantity), SUM(d.realQuantity) FROM salesdetails d, sales s WHERE DATE(s.saletime) = DATE(NOW()) AND d.saleid = s.saleid AND d.purchaseid = $purchaseid";
	
				$result = mysql_query($selectSales)
					or handleError($lang['error-loadsales'],"Error loading sale from db: " . mysql_error());
			
				$row = mysql_fetch_array($result);
					$soldToday = $row['SUM(d.quantity)'];
					$realSoldToday = $row['SUM(d.realQuantity)'];


			} else {

				// Query to look up today's opening weight
				if ($_SESSION['openAndClose'] > 2) {
					
					$openingLookup = "SELECT d.weight FROM openingdetails d, opening o WHERE d.openingid = $openingid AND d.openingid = o.openingid AND category = $category AND purchaseid = $purchaseid";
					
				} else if ($_SESSION['openAndClose'] == 2) {
			
					$openingLookup = "SELECT d.weight FROM closingdetails d, closing o WHERE d.closingid = $openingid AND d.closingid = o.closingid AND category = $category AND purchaseid = $purchaseid";
					
				}
				
				$result = mysql_query($openingLookup)
					or handleError($lang['error-loadprodclosedetails'],"Error loading closing from db: " . mysql_error());
			
			
				// If opening weight doesn't exist, find purchaseweight!
				if(mysql_num_rows($result) == 0) {
	   				$purchaseLookup = "SELECT realQuantity from purchases where purchaseid = $purchaseid";
	   				
					$result = mysql_query($purchaseLookup)
						or handleError($lang['error-loadprodclosedetails'],"Error loading closing from db: " . mysql_error());
					
				}
				
				$row = mysql_fetch_array($result);
					$openingWeight = $row['0'];
					
				// Look up todays dispenses
				$selectSales = "SELECT SUM(d.quantity), SUM(d.realQuantity) FROM salesdetails d, sales s WHERE DATE(s.saletime) = DATE(NOW()) AND d.saleid = s.saleid AND d.purchaseid = $purchaseid";
	
				$result = mysql_query($selectSales)
					or handleError($lang['error-loadsales'],"Error loading sale from db: " . mysql_error());
			
				$row = mysql_fetch_array($result);
					$soldToday = $row['SUM(d.quantity)'];
					$realSoldToday = $row['SUM(d.realQuantity)'];
	
				// Look up additions and removals
				$selectAdditions = "SELECT SUM(quantity) FROM productmovements WHERE purchaseid = $purchaseid AND type = 1 AND movementtime > '$openingtime'";
				$selectRemovals = "SELECT SUM(quantity) FROM productmovements WHERE purchaseid = $purchaseid AND type = 2 AND movementtime > '$openingtime'";
			
				$additions = mysql_query($selectAdditions)
					or handleError($lang['error-loadprodadditions'],"Error loading expense from db: " . mysql_error());
			
				$row = mysql_fetch_array($additions);
					$addedToday = $row['SUM(quantity)'];
					
				$removals = mysql_query($selectRemovals)
					or handleError($lang['error-loadprodadditions'],"Error loading expense from db: " . mysql_error());
			
				$row = mysql_fetch_array($removals);
					$takeoutsToday = $row['SUM(quantity)'];
					
					
				// Calculate jar weight:
				$jarWeight = $openingWeight + $addedToday - $takeoutsToday - $soldToday;	
				
				
				// Calculate what's in Internal stash
				$selectStashedInt = "SELECT SUM(quantity) FROM productmovements WHERE purchaseid = $purchaseid AND type = 2 AND (movementTypeid = 5 OR movementTypeid = 18)";
				$stashedInt = mysql_query($selectStashedInt)
					or handleError($lang['error-loadstash'],"Error loading stash details from db: " . mysql_error());
				
					$row = mysql_fetch_array($stashedInt);
						$stashedInt = $row['SUM(quantity)'];
					
				$selectUnStashedInt = "SELECT SUM(quantity) FROM productmovements WHERE purchaseid = $purchaseid AND type = 1 AND (movementTypeid = 12 OR movementTypeid = 17)";
				$unStashedInt = mysql_query($selectUnStashedInt)
					or handleError($lang['error-loadstash'],"Error loading stash details from db: " . mysql_error());
				
					$row = mysql_fetch_array($unStashedInt);
						$unStashedInt = $row['SUM(quantity)'];
			
						
				$inStashInt = $stashedInt - $unStashedInt;
			
			
				// Calculate what's in External stash
				$selectStashedExt = "SELECT SUM(quantity) FROM productmovements WHERE purchaseid = $purchaseid AND type = 2 AND (movementTypeid = 6 OR movementTypeid = 20)";
				$stashedExt = mysql_query($selectStashedExt)
					or handleError($lang['error-loadstash'],"Error loading stash details from db: " . mysql_error());
				
					$row = mysql_fetch_array($stashedExt);
						$stashedExt = $row['SUM(quantity)'];
					
				$selectUnStashedExt = "SELECT SUM(quantity) FROM productmovements WHERE purchaseid = $purchaseid AND type = 1 AND (movementTypeid = 2 OR movementTypeid = 19)";
				$unStashedExt = mysql_query($selectUnStashedExt)
					or handleError($lang['error-loadstash'],"Error loading stash details from db: " . mysql_error());
				
					$row = mysql_fetch_array($unStashedExt);
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
  <td style='text-align: left;'><strong>{$lang['global-name']}&nbsp;&nbsp;</strong></td>
  <td><strong>{$lang['closeday-jars']}&nbsp;&nbsp;</strong></td>
  <td><strong>{$lang['closeday-intstash']}&nbsp;&nbsp;</strong></td>
  <td><strong>{$lang['closeday-extstash']}&nbsp;&nbsp;</strong></td>
  <td><strong>{$lang['global-total']}&nbsp;&nbsp;</strong></td>
  <td><strong>{$lang['value']}&nbsp;&nbsp;</strong></td>
  <td><strong>{$lang['closeday-dispensed']}&nbsp;&nbsp;</strong></td>
  <td><strong>Status&nbsp;&nbsp;</strong></td>
 </tr>
EOD;
				$extractHeader = 'set';
				}
				
			} else {
				
				// Query to look up categories
				$selectCats = "SELECT id, name from categories WHERE id = $category";
			
				$resultCats = mysql_query($selectCats)
					or handleError($lang['error-loadflowers'],"Error loading flower from db: " . mysql_error());
					
				$row = mysql_fetch_array($resultCats);
		  	    	$catName = $row['name'];
		  	    	$catID = $row['id'];
		  	    	
				if (${'otherHeader' . $catID} != 'set') {
					$productDetails .= <<<EOD
 <tr>
  <td colspan='8'></td>
 </tr>
 <tr>
  <td colspan='8' style='color: #a80082; text-align: center; font-size: 14px; border-bottom: 1px solid #dedede; border-top: 1px solid #dedede; margin-top: 10px;'><strong>$catName</strong></td>
 </tr>
 <tr>
  <td style='text-align: left;'><strong>{$lang['global-name']}&nbsp;&nbsp;</strong></td>
  <td><strong>Stock&nbsp;&nbsp;</strong></td>
  <td><strong>{$lang['closeday-intstash']}&nbsp;&nbsp;</strong></td>
  <td><strong>{$lang['closeday-extstash']}&nbsp;&nbsp;</strong></td>
  <td><strong>{$lang['global-total']}&nbsp;&nbsp;</strong></td>
  <td><strong>{$lang['value']}&nbsp;&nbsp;</strong></td>
  <td><strong>{$lang['closeday-dispensed']}&nbsp;&nbsp;</strong></td>
  <td><strong>Status&nbsp;&nbsp;</strong></td>
 </tr>
EOD;
				${'otherHeader' . $catID} = 'set';
				}
					
					
					$otherTotals[$catID]['catName'] = $catName;
					$otherTotals[$catID]['otherTotJar'] = $otherTotals[$catID]['otherTotJar'] + $jarWeight;
					$otherTotals[$catID]['otherTotIntSt'] = $otherTotals[$catID]['otherTotIntSt'] + $inStashInt;
					$otherTotals[$catID]['otherTotExtSt'] = $otherTotals[$catID]['otherTotExtSt'] + $inStashExt;
					$otherTotals[$catID]['otherTot'] = $otherTotals[$catID]['otherTotJar'] + $otherTotals[$catID]['otherTotIntSt'] + $otherTotals[$catID]['otherTotExtSt'];
					$otherTotals[$catID]['otherSoldToday'] = $otherTotals[$catID]['otherSoldToday'] + $realSoldToday;
					



				}
			
		  	if ($category < 3) {
				$productDetails .= <<<EOD
 <tr>
  <td style='text-align: left;'>{$name} <span class='smallerfont3'>{$growtype}</span></td>
  <td>{$expr(number_format($jarWeight,2))} g.</td>
  <td>{$expr(number_format($inStashInt,2))} g.</td>
  <td>{$expr(number_format($inStashExt,2))} g.</td>
  <td><strong>{$expr(number_format($weightTotal,2))} g.</strong></td>
  <td>{$expr(number_format($weightPrice,2))} &euro;</td>
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
  <td>{$expr(number_format($weightPrice,2))} &euro;</td>
  <td>{$expr(number_format($soldToday,2))} u.</td>
  <td>{$productStatus}</td>
 </tr>
EOD;
	  		}
		} // End product loop

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
  <td>{$expr(number_format($flowerWeightPrice,2))} &euro;</td>
  <td>{$expr(number_format($flowerSoldToday,2))} g.</td>
 </tr>
 <tr>
  <td style='text-align: left;'>{$lang['global-extracts']}</td>
  <td>{$expr(number_format($extractTotJar,2))} g.</td>
  <td>{$expr(number_format($extractTotIntSt,2))} g.</td>
  <td>{$expr(number_format($extractTotExtSt,2))} g.</td>
  <td><strong>{$expr(number_format($extractTot,2))} g.</strong></td>
  <td>{$expr(number_format($extractWeightPrice,2))} &euro;</td>
  <td>{$expr(number_format($extractSoldToday,2))} g.</td>
 </tr>
 <tr style='border-top: 1px solid #888;'>
  <td style='text-align: left;'><strong>TOTAL</strong></td>
  <td><strong>{$expr(number_format($fullTotJar,2))} g.</strong></td>
  <td><strong>{$expr(number_format($fullTotIntSt,2))} g.</strong></td>
  <td><strong>{$expr(number_format($fullTotExtSt,2))} g.</strong></td>
  <td><strong>{$expr(number_format($fullTot,2))} g.</strong></td>
  <td><strong>{$expr(number_format($fullWeightPrice,2))} &euro;</strong></td>
  <td><strong>{$expr(number_format($fullSoldToday,2))} g.</strong></td>
 </tr>
EOD;

  
  
		// Query to look up expenses
	$selectExpenses = "SELECT expenseid, registertime, userid, expensetype, expense, moneysource, other, amount, shop, comment, receipt, expensecategory FROM expenses WHERE DATE(registertime) = DATE(NOW()) ORDER by registertime DESC";

	$result = mysql_query($selectExpenses)
		or handleError($lang['error-expensesload'],"Error loading expense from db: " . mysql_error());
	$result2 = mysql_query($selectExpenses)
		or handleError($lang['error-expensesload'],"Error loading expense from db: " . mysql_error());

		
		
		$expenseDetails .= <<<EOD
<br />
<table style='color: #444; text-align: right; font-family: Tahoma, Verdana, sans-serif; font-size: 14px;' cellpadding='5'>
 <tr>
  <td colspan='8' style='color: #5aa242; text-align: left; font-size: 17px; border-bottom: 2px solid #a80082;'><strong>{$lang['global-expensescaps']}</strong></td>
 </tr>
	   <tr>
	    <td style='text-align: center;'><strong>{$lang['global-time']}</strong></td>
	    <td style='text-align: center;'><strong>{$lang['global-category']}</strong></td>
	    <td style='text-align: center;'><strong>{$lang['global-expense']}</strong></td>
	    <td style='text-align: center;'><strong>{$lang['global-shop']}</strong></td>
	    <td style='text-align: center;'><strong>{$lang['global-member']}</strong></td>
	    <td style='text-align: center;'><strong>{$lang['global-amount']}</strong></td>
	    <td style='text-align: center;'><strong>{$lang['global-source']}</strong></td>
	    <td style='text-align: center;'><strong>{$lang['global-receipt']}?</strong></td>
	   </tr>
EOD;


while ($expense = mysql_fetch_array($result2)) {
	
	
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
			$catResult = mysql_query($selectExpenseCat)
				or handleError($lang['error-expensesload'],"Error loading expense from db: " . mysql_error());
			$row = mysql_fetch_array($catResult);
		  	    $expenseCat = $row['namees'];
		} else {
			$selectExpenseCat = "SELECT nameen FROM expensecategories WHERE categoryid = $expenseCat";
			$catResult = mysql_query($selectExpenseCat)
				or handleError($lang['error-expensesload'],"Error loading expense from db: " . mysql_error());
			$row = mysql_fetch_array($catResult);
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
		$result = mysql_query($userDetails)
			or handleError($lang['error-usersload'],"Error loading users from db: " . mysql_error());
			
		while ($user = mysql_fetch_array($result)) {
			$member = "#" . $user['memberno'] . " - " . $user['first_name'];
		}

	
	
	$expense_row =	sprintf("
  	  <tr>
  	   <td class='clickableRow' href='expense.php?expenseid=%d'>%s</td>
  	   <td class='clickableRow' style='text-align: left;' href='expense.php?expenseid=%d'>%s</td>
  	   <td class='clickableRow' style='text-align: left;' href='expense.php?expenseid=%d'>%s</td>
  	   <td class='clickableRow' style='text-align: left;' href='expense.php?expenseid=%d'>%s</td>
  	   <td class='clickableRow' style='text-align: left;' href='expense.php?expenseid=%d'>%s</td>
  	   <td style='text-align: right;' class='clickableRow' href='expense.php?expenseid=%d'>%0.2f <span class='smallerfont'>&euro;</span></td>
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
	  echo $expenseDetails;
	  echo "</table>";

  
}
 
 displayFooter();