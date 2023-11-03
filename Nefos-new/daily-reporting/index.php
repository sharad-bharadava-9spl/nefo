<?php
	
	require_once '../cOnfig/connection.php';
	require_once '../cOnfig/view.php';
	require_once '../cOnfig/authenticate.php';
	require_once '../cOnfig/languages/common.php';
	
	session_start();
	$accessLevel = '3';
	
	// Authenticate & authorize
	authorizeUser($accessLevel);
	
	getSettings();
			
	
		// If no closing ID is set, we display the list of closing dates
		if (!isset($_POST['reportDate'])) {
			
			// Find first sales date
			$findStartDate = "SELECT saletime FROM sales ORDER BY saletime ASC LIMIT 1";
			
			$result = mysql_query($findStartDate)
				or handleError($lang['error-loadsales'],"Error loading sale from db: " . mysql_error());
		
			$row = mysql_fetch_array($result);
				$startDate = date('Y-m-d', strtotime($row['saletime']));
		
			$endOperator = strtotime("-1 day", strtotime($startDate));
		
			// Find number of rows to display
		    $noOfLines = floor((time() - $endOperator)/(60*60*24)) . "<br />";
    
			// Pagination settings & initialisation
			$resultLimit = 50;
										
			if (isset($_GET['page'])) {
	        	$page = $_GET['page'] + 1;
	            $offset = $resultLimit * $page;
	        } else {
	            $page = 0;
	            $offset = 0;
	        }
			
	        $resultsLeft = $noOfLines - ($page * $resultLimit);
	        
			$reportDate = time();

			for ($i = 1; $i <= $noOfLines; $i++) {
   				$reportDateReadable = date('dS M Y', $reportDate) . "<br />";
				$reportDateSQL = date("Y-m-d", $reportDate);
    			$reportDate -= 86400;

				$output .= "<form action='' method='POST'><input type='hidden' name='reportDate' value='$reportDateSQL'><input type='hidden' name='reportDateReadable' value='$reportDateReadable'><button type='submit' class='linkStyle'>$reportDateReadable</button></form>";
			}
			
			// Pagination display
			if ($resultsLeft < $resultLimit && $offset != 0) {
            	$last = $page - 2;
            	$output .=  "<br /><a href='$_PHP_SELF?page=$last'>&laquo; Previous</a><br />&nbsp;";
         	} else if ($page > 0) {
	            $last = $page - 2;
	            $output .=  "<a href='$_PHP_SELF?page=$last' style='font-size: 25px;'>&laquo;</a>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<a href='$_PHP_SELF?page=$page' style='font-size: 25px;'>&raquo;</a><br />&nbsp;";
	        } else if ($page == 0 && $offset != 0) {
            	$output .=  "<br /><a href='$_PHP_SELF?page=$page'>Next &raquo;</a><br />&nbsp;";
         	} 
			
			pageStart("Daily reports", NULL, NULL, "preporting", "daily", "DAILY REPORT", $_SESSION['successMessage'], $_SESSION['errorMessage']);
			include 'closinglist.html.php';
			
			
		// If a closing ID is set, we display the report
		} else {
			
if ($_SESSION['realWeight'] == 1) {

			$reportDate = $_POST['reportDate'];
			$reportDateReadable = $_POST['reportDateReadable'];
			
			// See if a closing exists for this day
			$query = "SELECT closingid FROM closing WHERE DATE(closingtime) = DATE('$reportDate') ORDER by closingtime DESC LIMIT 1";
			
			$result = mysql_query($query)
				or handleError($lang['error-loadsales'],"Error loading sale from db: " . mysql_error());
				
			if (mysql_num_rows($result) == 1) {
				
				// Closing exists, pull data from closing tables
				
				
				$query = "SELECT closingid FROM closing WHERE DATE(closingtime) = DATE('$reportDate') ORDER by closingtime DESC LIMIT 1";
			
				$result = mysql_query($query)
					or handleError($lang['error-loadsales'],"Error loading sale from db: " . mysql_error());
				
				$row = mysql_fetch_array($result);
					$closingid = $row['closingid'];
					
				// Look up todays donations
				$selectDonations = "SELECT COUNT(donationid) from donations WHERE (donatedTo = 0 OR donatedTo = 1) AND DATE(donationTime) = DATE('$reportDate')";
			
				$donationResult = mysql_query($selectDonations)
					or handleError($lang['error-donationload'],"Error loading donations from db: " . mysql_error());
					
				$row = mysql_fetch_array($donationResult);
					$donationsNo = $row['COUNT(donationid)'];
					
				// Look up todays bank donations
				$selectDonations = "SELECT COUNT(donationid) from donations WHERE donatedTo = 2 AND DATE(donationTime) = DATE('$reportDate')";
			
				$donationResult = mysql_query($selectDonations)
					or handleError($lang['error-donationload'],"Error loading donations from db: " . mysql_error());
					
				$row = mysql_fetch_array($donationResult);
					$bankDonationsNo = $row['COUNT(donationid)'];
					
					
				// Select closing details
				$selectClosingData = "SELECT openingtime, closingtime, quantitySold, soldtoday, unitsSold, closingbalance, moneytaken, takenduringday, cashintill, bankBalance, newmembers, closedby, tillComment, expenses, membershipFees, estimatedTill, tillDelta, bankExpenses, prodOpening, prodStock, stockDelta, prodStockFlower, prodStockExtract, income, stockDeltaFlower, stockDeltaExtract, donations, bankDonations, renewedMembers, bannedMembers, deletedMembers, expiredMembers, totalMembers, activeMembers, intStash, extStash, totalWeight, totalNoShake, flowerintStash, flowerextStash, flowerweightNoShake, flowertotalWeight, flowertotalNoShake, extractintStash, extractextStash, extracttotalWeight, flowerDispensed, extractDispensed, soldTodayFlower, soldTodayExtract, membershipfeesBank, soldtodayBar, unitsSoldBar, openingBalance, openingBalanceBank, totCredit, quantitySoldReal, soldTodayFlowerReal, soldTodayExtractReal FROM closing WHERE closingid = $closingid";
			
				$closingResult = mysql_query($selectClosingData)
					or handleError($lang['error-loadsales'],"Error loading sale from db: " . mysql_error());
					
				$row = mysql_fetch_array($closingResult);
					$openingtime = $row['openingtime'];
					$closingtime = $row['closingtime'];
					$quantitySold = $row['quantitySold'];
					$soldtoday = $row['soldtoday'];
					$unitsSold = $row['unitsSold'];
					$closingbalance = $row['closingbalance'];
					$moneytaken = $row['moneytaken'];
					$bankedduringday = $row['takenduringday'];
					$cashintill = $row['cashintill'];
					$bankBalance = $row['bankBalance'];
					$newmembers = $row['newmembers'];
					$tillComment = $row['tillComment'];
					$expenses = $row['expenses'];
					$membershipFees = $row['membershipFees'];
					$estimatedTill = $row['estimatedTill'];
					$tillDelta = $row['tillDelta'];
					$bankExpenses = $row['bankExpenses'];
					$prodOpening = $row['prodOpening'];
					$prodStock = $row['prodStock'];
					$stockDelta = $row['stockDelta'];
					$prodStockFlower = $row['prodStockFlower'];
					$prodStockExtract = $row['prodStockExtract'];
					$income = $row['income'];
					$stockDeltaFlower = $row['stockDeltaFlower'];
					$stockDeltaExtract = $row['stockDeltaExtract'];
					$donations = $row['donations'];
					$bankDonations = $row['bankDonations'];
					$renewedMembers = $row['renewedMembers'];
					$bannedMembers = $row['bannedMembers'];
					$deletedMembers = $row['deletedMembers'];
					$expiredMembers = $row['expiredMembers'];
					$totalMembers = $row['totalMembers'];
					$activeMembers = $row['activeMembers'];
					$intStash = $row['intStash'];
					$extStash = $row['extStash'];
					$totalWeight = $row['totalWeight'];
					$totalNoShake = $row['totalNoShake'];
					$flowerintStash = $row['flowerintStash'];
					$flowerextStash = $row['flowerextStash'];
					$flowerweightNoShake = $row['flowerweightNoShake'];
					$flowertotalWeight = $row['flowertotalWeight'];
					$flowertotalNoShake = $row['flowertotalNoShake'];
					$extractintStash = $row['extractintStash'];
					$extractextStash = $row['extractextStash'];
					$extracttotalWeight = $row['extracttotalWeight'];
					$flowerGramsToday = $row['flowerDispensed'];
					$extractGramsToday = $row['extractDispensed'];
					$flowerSalesToday = $row['soldTodayFlower'];
					$extractSalesToday = $row['soldTodayExtract'];
					$membershipfeesBank = $row['membershipfeesBank'];
					$barSales = $row['soldtodayBar'];
					$barUnits = $row['unitsSoldBar'];
					$openingBalance = $row['openingBalance'];
					$openingBalanceBank = $row['openingBalanceBank'];
					$totCredit = $row['totCredit'];
					$quantitySoldReal = $row['quantitySoldReal'];
					$flowerGramsTodayReal = $row['soldTodayFlowerReal'];
					$extractGramsTodayReal = $row['soldTodayExtractReal'];
					
					$flowerSalesPercentageToday = ($flowerSalesToday / $salesToday) * 100;
					$flowerGramsPercentageToday = ($flowerGramsToday / $quantitySold) * 100;
					
					$extractSalesPercentageToday = ($extractSalesToday / $salesToday) * 100;
					$extractGramsPercentageToday = ($extractGramsToday / $quantitySold) * 100;
				
				pageStart($lang['status'] . ": $reportDateReadable", NULL, NULL, "preporting", "daily", $lang['status'] . ": $reportDateReadable", $_SESSION['successMessage'], $_SESSION['errorMessage']);
			
				
				// Look up &euro;, g, units
				$selectSales = "SELECT SUM(quantitySold), SUM(soldToday), SUM(unitsSold) FROM closing WHERE closingid = $closingid";
			
				$resultSales = mysql_query($selectSales)
					or handleError($lang['error-dispenseload'],"Error loading sale from db: " . mysql_error());
					
				$row = mysql_fetch_array($resultSales);
					$salesToday = $row['SUM(soldToday)'];
					$quantitySold = $row['SUM(quantitySold)'];
					$unitsSold = $row['SUM(unitsSold)'];
			
			
				// Compose mail to admin
				$mailtoadmin = <<<EOD
<table style='color: #444; text-align: right; font-family: Tahoma, Verdana, sans-serif; font-size: 14px;'>
 <tr>
  <td colspan='10' style='color: #5aa242; text-align: left; font-size: 17px; border-bottom: 2px solid #a80082;'><strong>{$lang['bar-and-dispensary']}</strong></td>
 </tr>
 <tr>
  <td style='text-align: left;'>{$lang['dispensary']}</td>
  <td>{$expr(number_format($salesToday,2))} &euro;</td>
  <td></td>
  <td>{$expr(number_format($quantitySoldReal,2))} g.</td>
  <td>({$expr(number_format($quantitySold,2))} g.)</td>
  <td></td>
  <td>{$expr(number_format($unitsSold,2))} u.</td>
  <td></td>
 </tr>
 <tr>
  <td style='text-align: left;'><em>{$lang['closeday-owflowers']}</em></td>
  <td>{$expr(number_format($flowerSalesToday,2))} &euro;</td>
  <td>{$expr(number_format($flowerSalesPercentageToday,0))}%</td>
  <td>{$expr(number_format($flowerGramsTodayReal,2))} g.</td>
  <td>({$expr(number_format($flowerGramsToday,2))} g.)</td>
  <td>{$expr(number_format($flowerGramsPercentageToday,0))}%</td>
  <td></td>
  <td></td>
 </tr>
 <tr>
  <td style='text-align: left;'><em>{$lang['closeday-owextracts']}</em></td>
  <td>{$expr(number_format($extractSalesToday,2))} &euro;</td>
  <td>{$expr(number_format($extractSalesPercentageToday,0))}%</td>
  <td>{$expr(number_format($extractGramsTodayReal,2))} g.</td>
  <td>({$expr(number_format($extractGramsToday,2))} g.)</td>
  <td>{$expr(number_format($extractGramsPercentageToday,0))}%</td>
  <td></td>
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
		
		// Create more product queries for each category - to be used in a bigger query further down!
		$customProducts .= " UNION ALL SELECT '$categoryid' AS category, pr.name, '' AS breed2, c.purchaseid, c.soldToday, c.weight, c.weightDelta, c.intStash, c.extStash, c.weightNoShake, c.totalWeight, c.totalNoShake, c.inMenu, c.specificComment FROM products pr, closingdetails c WHERE c.category = $categoryid AND c.productid = pr.productid AND c.closingid = $closingid";
				
		
		// Look up sales in this cat
		$selectSalesOthers = "SELECT SUM(d.amount), SUM(d.quantity) from sales s, salesdetails d WHERE s.saleid = d.saleid AND s.saletime BETWEEN '$openingtime' AND '$closingtime' AND d.category = $categoryid";
	
		$resultOthers = mysql_query($selectSalesOthers)
			or handleError($lang['error-dispenseload'],"Error loading sale from db: " . mysql_error());
		
		$row = mysql_fetch_array($resultOthers);
			$salesTodayOthers = $row['SUM(d.amount)'];
			$quantitySoldOthers = $row['SUM(d.quantity)'];
			
		$othersSalesPercentageToday = ($salesTodayOthers / $salesToday) * 100;
		$othersGramsPercentageToday = ($quantitySoldOthers / $unitsSold) * 100;

		
		$mailtoadmin .= <<<EOD
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

		$mailtoadmin .= <<<EOD
 <tr>
  <td colspan='10'></td>
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
  <td colspan='10'>&nbsp;</td>
 </tr>
 <tr>
  <td colspan='10' style='color: #5aa242; text-align: left; font-size: 17px; border-bottom: 2px solid #a80082;'><strong>Member details</strong></td>
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
  <td>$totalMembers</td>
  <td></td>
  <td></td>
  <td></td>
  <td></td>
 </tr>
 <tr>
  <td style='text-align: left;'>{$lang['active-members']}</td>
  <td>$activeMembers</td>
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
  <td>$expiredMembers</td>
  <td></td>
  <td></td>
  <td></td>
  <td></td>
 </tr>
 <tr>
  <td style='text-align: left;'>{$lang['banned-members']}</td>
  <td>$bannedMembers</td>
  <td></td>
  <td></td>
  <td></td>
  <td></td>
 </tr>
 <tr>
  <td style='text-align: left;'>{$lang['deleted-members']}</td>
  <td>$deletedMembers</td>
  <td></td>
  <td></td>
  <td></td>
  <td></td>
 </tr>
</table>
<table style='color: #444; text-align: right; font-family: Tahoma, Verdana, sans-serif; font-size: 14px;'>
 <tr rowspan='2'>
  <td colspan='10'>&nbsp;</td>
 </tr>
 <tr>
  <td colspan='10' style='color: #5aa242; text-align: left; font-size: 17px; border-bottom: 2px solid #a80082;'><strong>{$lang['closeday-finances']}</strong> $reportDateReadable</td>
 </tr>
 <tr>
  <td style='text-align: left;'>{$lang['closeday-donations-till']}</td>
  <td>{$expr(number_format($donations,2))} &euro;</td>
  <td style='text-align: left;'>($donationsNo)</td>
  <td style='vertical-align: bottom; text-align: left; font-size: 14px;'><strong>{$lang['till-calculation']}</strong></td>
  <td></td>
  <td></td>
  <td style='vertical-align: bottom; text-align: left; font-size: 14px;'><strong>{$lang['bank-calculation']}</strong></td>
  <td></td>
 </tr>
 <tr>
  <td style='text-align: left;'>{$lang['closeday-donations-bank']}</td>
  <td>{$expr(number_format($bankDonations,2))} &euro;</td>
  <td style='text-align: left;'>($bankDonationsNo)</td>
  <td style='text-align: left;'>{$lang['closeday-tillatopening']}:</td>
  <td>{$expr(number_format($openingBalance,2))} &euro;</td>
  <td></td>
  <td style='text-align: left;'>{$lang['closeday-openingtoday']}:</td>
  <td>{$expr(number_format($openingBalanceBank,2))} &euro;</td>
 </tr>
 <tr>
  <td style='text-align: left;'>{$lang['closeday-membershipfees-till']}</td>
  <td>{$expr(number_format($membershipFees,2))} &euro;</td>
  <td></td>
  <td style='text-align: left;'>+ {$lang['closeday-membershipfees-till']}:</td>
  <td>{$expr(number_format($membershipFees,2))} &euro;</td>
  <td></td>
  <td style='text-align: left;'>+ {$lang['closeday-membershipfees-bank']}:</td>
  <td>{$expr(number_format($membershipfeesBank,2))} &euro;</td>
 </tr>
 <tr>
  <td style='text-align: left; border-bottom: 1px dashed #ababab;'>{$lang['closeday-membershipfees-bank']}</td>
  <td style='border-bottom: 1px dashed #ababab;'>{$expr(number_format($membershipfeesBank,2))} &euro;</td>
  <td></td>
  <td style='text-align: left;'>+ {$lang['global-donations']}:</td>
  <td>{$expr(number_format($donations,2))} &euro;</td>
  <td></td>
  <td style='text-align: left;'>+ {$lang['global-donations']}:</td>
  <td>{$expr(number_format($bankDonations,2))} &euro;</td>
 </tr>
 <tr>
  <td style='text-align: left; border-bottom: 1px solid #ababab;'>{$lang['closeday-totalincome']}</td>
  <td style='border-bottom: 1px solid #ababab;'>{$expr(number_format($income,2))} &euro;</td>
  <td></td>
  <td style='text-align: left;'>- {$lang['closeday-tillexpenses']}:</td>
  <td>{$expr(number_format($expenses,2))} &euro;</td>
  <td></td>
  <td style='text-align: left;'>+ {$lang['banked-now']}:</td>
  <td>{$expr(number_format($moneytaken + $bankedduringday,2))} &euro;</td>
 </tr>
 <tr>
  <td style='text-align: left;'>{$lang['closeday-tillexpenses']}</td>
  <td>{$expr(number_format($expenses,2))} &euro;</td>
  <td></td>
  <td style='text-align: left; border-bottom: 1px dashed #ababab;'>- {$lang['closeday-moneybanked']}:</td>
  <td style='text-align: right; border-bottom: 1px dashed #ababab;'>{$expr(number_format($moneytaken + $bankedduringday,2))} &euro;</td>
  <td></td>
  <td style='text-align: left;'>+ {$lang['banked-during-day']}:</td>
  <td>{$expr(number_format($bankedduringday,2))} &euro;</td>
 </tr>
 <tr>
  <td style='text-align: left;'><strong>{$lang['closeday-banked']}</strong></td>
  <td><strong>{$expr(number_format($moneytaken,2))} &euro;</strong></td>
  <td></td>
  <td style='text-align: left; border-bottom: 1px solid #ababab;'>{$lang['closeday-estimatedtill']}:</td>
  <td style='text-align: right; border-bottom: 1px solid #ababab;'>{$expr(number_format($estimatedTill,2))} &euro;</td>
  <td></td>
  <td style='text-align: left; border-bottom: 1px dashed #ababab;'>- {$lang['global-expenses']}:</td>
  <td style='text-align: right; border-bottom: 1px dashed #ababab;'>{$expr(number_format($bankExpenses,2))} &euro;</td>
 </tr>
 <tr>
  <td style='text-align: left;'>{$lang['closeday-tillbalance']}</td>
  <td>{$expr(number_format($cashintill,2))} &euro;</td>
  <td></td>
  <td style='text-align: left;'>{$lang['closeday-yourcount']}:</td>
  <td>{$expr(number_format($cashintill,2))} &euro;</td>
  <td></td>
  <td style='text-align: left; border-bottom: 1px solid #ababab;'>{$lang['closeday-bankbalance']}:</td>
  <td style='text-align: right; border-bottom: 1px solid #ababab;'>{$expr(number_format($bankBalance,2))} &euro;</td>
 </tr>
 <tr>
  <td style='text-align: left;'>{$lang['closeday-tilldelta']}</td>
  <td>{$expr(number_format($tillDelta,2))} &euro;</td>
  <td></td>
  <td style='text-align: left;'><strong>{$lang['global-delta']}:</strong></td>
  <td><strong>{$expr(number_format($tillDelta,2))} &euro;</strong></td>
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
  <td></td>
  <td></td>
 </tr>
 <tr>
  <td style='text-align: left;'>{$lang['closeday-bankbalance']}</td>
  <td>{$expr(number_format($bankBalance,2))} &euro;</td>
  <td></td>
  <td></td>
  <td></td>
  <td></td>
  <td></td>
  <td></td>
 </tr>
 <tr>
  <td style='text-align: left;'>{$lang['closeday-totalclubbalance']}</td>
  <td>{$expr(number_format($closingbalance,2))} &euro;</td>
  <td></td>
  <td></td>
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
  <td></td>
  <td></td>
 </tr>
 <tr>
  <td colspan='10' style='text-align: left;'>{$lang['closeday-tillcomment']}:<br /><em>$tillComment</em> </td>
 </tr>
 <tr rowspan='2'>
  <td colspan='10'>&nbsp;</td>
 </tr>
</table>
<table style='color: #444; text-align: right; font-family: Tahoma, Verdana, sans-serif; font-size: 14px;'>
EOD;

				

		
		$defaultProducts = "SELECT category, f.name, f.breed2, c.purchaseid, c.soldToday, c.weight, c.weightDelta, c.intStash, c.extStash, c.weightNoShake, c.totalWeight, c.totalNoShake, c.inMenu, c.specificComment FROM flower f, closingdetails c WHERE c.category = '1' AND c.productid = f.flowerid AND c.closingid = $closingid UNION ALL SELECT category, e.name, '' AS breed2, c.purchaseid, c.soldToday, c.weight, c.weightDelta, c.intStash, c.extStash, c.weightNoShake, c.totalWeight, c.totalNoShake, c.inMenu, c.specificComment FROM extract e, closingdetails c WHERE c.category = '2' AND c.productid = e.extractid AND c.closingid = $closingid";
		
		$allProducts = $defaultProducts . $customProducts;
		
		$productsResult = mysql_query($allProducts)
			or handleError($lang['error-loadsales'],"Error loading sale from db: " . mysql_error());
			
		$productsResult2 = mysql_query($allProducts)
			or handleError($lang['error-loadsales'],"Error loading sale from db: " . mysql_error());
		
		while ($product = mysql_fetch_array($productsResult)) {
			
			$category = $product['category'];
			$name = $product['name'];
			$breed2 = $product['breed2'];
			$purchaseid = $product['purchaseid'];
			$soldToday = $product['soldToday'];
			$weight = $product['weight'];
			$weightDelta = $product['weightDelta'];
			$intStash = $product['intStash'];
			$extStash = $product['extStash'];
			$weightNoShake = $product['weightNoShake'];
			$totalWeight = $product['totalWeight'];
			$totalNoShake = $product['totalNoShake'];
			$inMenu = $product['inMenu'];
			$specificComment = $product['specificComment'];
			
			if ($specificComment != '') {
				
				$commentInset = <<<EOD
 <tr>
  <td colspan='10' style='text-align: left;'>{$lang['global-comment']}: <em>$specificComment</em></td>
 </tr>
EOD;

			} else {
				$commentInset = '';
			}
			
			if ($breed2 != '') {
				$name = $name . " x " . $breed2;
			}
			
			
			if ($category == '1') {
				
				// Look up growtype and closed status
				$selectGrowTypeNo = "SELECT growType, closedAt FROM purchases WHERE purchaseid = $purchaseid";
				
				$growTypeNoResult = mysql_query($selectGrowTypeNo)
					or handleError($lang['error-loadsales'],"Error loading sale from db: " . mysql_error());

				$row = mysql_fetch_array($growTypeNoResult);
					$growTypeNo = $row['growType'];
					$closedAt = $row['closedAt'];
					
				if ($growTypeNo != '') {
					
					$growDetails = "SELECT growtype FROM growtypes WHERE growtypeid = $growTypeNo";
									
					$resultGrowType = mysql_query($growDetails)
						or handleError($lang['error-growtypeload'],"Error loading growtype: " . mysql_error());
						
					$row = mysql_fetch_array($resultGrowType);
						$growtype = $row['growtype'];
						
					if ($growtype != '') {
						$growtype = "(" . $growtype . ")";
					} else {
						$growtype = '';
					}
					
				} else {
					
					$growtype = '';
					
				}
				
				if ($closedAt != NULL) {
					$productStatus = "Closed";
				} else if ($inMenu == 0) {
					$productStatus = "Not in menu";
				} else {
					$productStatus = "In menu";
				}


					
				$mailtoProductResponsible .= <<<EOD
 <tr>
  <td style='text-align: left;'>$name <span class='smallerfont'>$growtype</span></td>
  <td>{$expr(number_format($weight,2))} g.</td>
  <td>{$expr(number_format($weightNoShake,2))} g.</td>
  <td>{$expr(number_format($intStash,2))} g.</td>
  <td>{$expr(number_format($extStash,2))} g.</td>
  <td><strong>{$expr(number_format($totalWeight,2))} g.</strong></td>
  <td><strong>{$expr(number_format($totalNoShake,2))} g.</strong></td>
  <td>{$expr(number_format($weightDelta,2))} g.</td>
  <td>{$expr(number_format($soldToday,2))} g.</td>
 </tr>
$commentInset
EOD;

			} else if ($dividersetExtract != 'yes') {
				
				// Look up closed status
				$selectGrowTypeNo = "SELECT closedAt FROM purchases WHERE purchaseid = $purchaseid";
				
				$growTypeNoResult = mysql_query($selectGrowTypeNo)
					or handleError($lang['error-loadsales'],"Error loading sale from db: " . mysql_error());

				$row = mysql_fetch_array($growTypeNoResult);
					$closedAt = $row['closedAt'];

				if ($closedAt != NULL) {
					$productStatus = "Closed";
				} else if ($inMenu == 0) {
					$productStatus = "Not in menu";
				} else {
					$productStatus = "In menu";
				}
				
				// insert divider
				$dividersetExtract = 'yes';
				$mailtoProductResponsible .= <<<EOD
 <tr>
  <td colspan='10'></td>
 </tr>
 <tr>
  <td colspan='10' style='color: #a80082; text-align: center; font-size: 14px; border-top: 1px solid #dedede; border-bottom: 1px solid #dedede;'><strong>{$lang['global-extractscaps']}</strong></td>
 </tr>
 <tr>
  <td></td>
  <td><strong>{$lang['closeday-jars']}&nbsp;&nbsp;</strong></td>
  <td><strong></strong></td>
  <td><strong>{$lang['closeday-intstash']}&nbsp;&nbsp;</strong></td>
  <td><strong>{$lang['closeday-extstash']}&nbsp;&nbsp;</strong></td>
  <td><strong>{$lang['global-total']}&nbsp;&nbsp;</strong></td>
  <td><strong></strong></td>
  <td><strong>{$lang['global-delta']}&nbsp;&nbsp;</strong></td>
  <td><strong>{$lang['closeday-dispensed']}&nbsp;&nbsp;</strong></td>
 </tr>
 <tr>
  <td style='text-align: left;'>$name</td>
  <td>{$expr(number_format($weight,2))} g.</td>
  <td></td>
  <td>{$expr(number_format($intStash,2))} g.</td>
  <td>{$expr(number_format($extStash,2))} g.</td>
  <td><strong>{$expr(number_format($totalWeight,2))} g.</strong></td>
  <td></td>
  <td>{$expr(number_format($weightDelta,2))} g.</td>
  <td>{$expr(number_format($soldToday,2))} g.</td>
 </tr>	
$commentInset
EOD;

			} else if ($category == '2') {
				
				// Look up closed status
				$selectGrowTypeNo = "SELECT closedAt FROM purchases WHERE purchaseid = $purchaseid";
				
				$growTypeNoResult = mysql_query($selectGrowTypeNo)
					or handleError($lang['error-loadsales'],"Error loading sale from db: " . mysql_error());

				$row = mysql_fetch_array($growTypeNoResult);
					$closedAt = $row['closedAt'];

				
				$mailtoProductResponsible .= <<<EOD
 <tr>
  <td style='text-align: left;'>$name</td>
  <td>{$expr(number_format($weight,2))} g.</td>
  <td></td>
  <td>{$expr(number_format($intStash,2))} g.</td>
  <td>{$expr(number_format($extStash,2))} g.</td>
  <td><strong>{$expr(number_format($totalWeight,2))} g.</strong></td>
  <td></td>
  <td>{$expr(number_format($weightDelta,2))} g.</td>
  <td>{$expr(number_format($soldToday,2))} g.</td>
 </tr>	
$commentInset
EOD;

			} else {
				
				// Look up closed status
				$selectGrowTypeNo = "SELECT closedAt FROM purchases WHERE purchaseid = $purchaseid";
				
				$growTypeNoResult = mysql_query($selectGrowTypeNo)
					or handleError($lang['error-loadsales'],"Error loading sale from db: " . mysql_error());

				$row = mysql_fetch_array($growTypeNoResult);
					$closedAt = $row['closedAt'];

				if ($closedAt != NULL) {
					$productStatus = "Closed";
				} else if ($inMenu == 0) {
					$productStatus = "Not in menu";
				} else {
					$productStatus = "In menu";
				}

				// See if header has been set
				if (${'otherHeader' . $category} != 'set') {
				
					// Look up categories
					$selectCats = "SELECT name FROM categories WHERE id = $category";
				
					$resultCats = mysql_query($selectCats)
						or handleError($lang['error-loadflowers'],"Error loading flower from db: " . mysql_error());
						
					$catRow = mysql_fetch_array($resultCats);
						$categoryname = $catRow['name'];
	
				
					// insert divider
					$mailtoProductResponsible .= <<<EOD
 <tr>
  <td colspan='10'></td>
 </tr>
 <tr>
  <td colspan='10' style='color: #a80082; text-align: center; font-size: 14px; border-top: 1px solid #dedede; border-bottom: 1px solid #dedede;'><strong>$categoryname</strong></td>
 </tr>
 <tr>
  <td></td>
  <td></td>
  <td></td>
  <td><strong>{$lang['closeday-intstash']}&nbsp;&nbsp;</strong></td>
  <td><strong>{$lang['closeday-extstash']}&nbsp;&nbsp;</strong></td>
  <td></td>
  <td></td>
  <td></td>
  <td><strong>{$lang['closeday-dispensed']}&nbsp;&nbsp;</strong></td>
 </tr>
 <tr>
  <td style='text-align: left;'>$name</td>
  <td></td>
  <td></td>
  <td>{$expr(number_format($intStash,2))} u.</td>
  <td>{$expr(number_format($extStash,2))} u.</td>
  <td></td>
  <td></td>
  <td></td>
  <td>{$expr(number_format($soldToday,2))} u.</td>
 </tr>	
$commentInset
EOD;

					${'otherHeader' . $category} = 'set';
						
				} else {
				
					$mailtoProductResponsible .= <<<EOD
 <tr>
  <td style='text-align: left;'>$name</td>
  <td></td>
  <td></td>
  <td>{$expr(number_format($intStash,2))} u.</td>
  <td>{$expr(number_format($extStash,2))} u.</td>
  <td></td>
  <td></td>
  <td></td>
  <td>{$expr(number_format($soldToday,2))} u.</td>
 </tr>	
$commentInset
EOD;

				}

			}

		} // Ends products loop
		


		
		$mailtoProductResponsible .= "</table>";
			
	
				
		
		
		
		
		
		
		
		
		  
			// Query to look up expenses
			$selectExpenses = "SELECT expenseid, registertime, userid, expensetype, expense, moneysource, other, amount, shop, comment, receipt, expensecategory FROM expenses WHERE DATE(registertime) = DATE('$reportDate') ORDER by registertime DESC";
		
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


		$mailtoProductResponsibleFull .= $mailtoProductResponsible;
		
		echo $mailtoadmin;
		echo <<<EOD
 <tr>
  <td colspan='10' style='color: #5aa242; text-align: left; font-size: 17px; border-bottom: 2px solid #a80082;'><strong>{$lang['closeday-productoverview']}</strong></td>
 </tr>
 <tr>
  <td></td>
  <td><strong>{$lang['closeday-jars']}&nbsp;&nbsp;</strong></td>
  <td><strong>{$lang['closeday-woshake']}&nbsp;&nbsp;</strong></td>
  <td><strong>{$lang['closeday-intstash']}&nbsp;&nbsp;</strong></td>
  <td><strong>{$lang['closeday-extstash']}&nbsp;&nbsp;</strong></td>
  <td><strong>{$lang['global-total']}&nbsp;&nbsp;</strong></td>
  <td><strong>{$lang['closeday-woshake']}&nbsp;&nbsp;</strong></td>
  <td><strong>{$lang['global-delta']}&nbsp;&nbsp;</strong></td>
  <td><strong>{$lang['closeday-dispensed']}&nbsp;&nbsp;</strong></td>
 </tr>
 <tr>
  <td style='text-align: left;'>{$lang['global-flowers']}</td>
  <td>{$expr(number_format($prodStockFlower,2))} g.</td>
  <td>{$expr(number_format($flowerweightNoShake,2))} g.</td>
  <td>{$expr(number_format($flowerintStash,2))} g.</td>
  <td>{$expr(number_format($flowerextStash,2))} g.</td>
  <td><strong>{$expr(number_format($flowertotalWeight,2))} g.</strong></td>
  <td><strong>{$expr(number_format($flowertotalNoShake,2))} g.</strong></td>
  <td>{$expr(number_format($stockDeltaFlower,2))} g.</td>
  <td>{$expr(number_format($flowerGramsTodayReal,2))} g.</td>
 </tr>
 <tr>
  <td style='text-align: left;'>{$lang['global-extracts']}</td>
  <td>{$expr(number_format($prodStockExtract,2))} g.</td>
  <td>{$expr(number_format($prodStockExtract,2))} g.</td>
  <td>{$expr(number_format($extractintStash,2))} g.</td>
  <td>{$expr(number_format($extractextStash,2))} g.</td>
  <td><strong>{$expr(number_format($extracttotalWeight,2))} g.</strong></td>
  <td><strong>{$expr(number_format($extracttotalWeight,2))} g.</strong></td>
  <td>{$expr(number_format($stockDeltaExtract,2))} g.</td>
  <td>{$expr(number_format($extractGramsTodayReal,2))} g.</td>
 </tr>
 <tr style='border-top: 1px solid #888;'>
  <td style='text-align: left;'><strong>TOTAL</strong></td>
  <td><strong>{$expr(number_format($prodStockFlower + $prodStockExtract,2))} g.</strong></td>
  <td><strong>{$expr(number_format($flowerweightNoShake + $prodStockExtract,2))} g.</strong></td>
  <td><strong>{$expr(number_format($flowerintStash + $extractintStash,2))} g.</strong></td>
  <td><strong>{$expr(number_format($flowerextStash + $extractextStash,2))} g.</strong></td>
  <td><strong>{$expr(number_format($flowertotalWeight + $extracttotalWeight,2))} g.</strong></td>
  <td><strong>{$expr(number_format($flowertotalNoShake + $extracttotalWeight,2))} g.</strong></td>
  <td><strong>{$expr(number_format($stockDeltaFlower + $stockDeltaExtract,2))} g.</strong></td>
  <td><strong>{$expr(number_format($flowerGramsTodayReal + $extractGramsTodayReal,2))} g.</strong></td>
 </tr>
 <tr rowspan='2'>
  <td colspan='10'>&nbsp;</td>
 </tr>
 <tr>
  <td colspan='10' style='color: #5aa242; text-align: left; font-size: 17px; border-bottom: 2px solid #a80082;'><strong>{$lang['closeday-productdetails']}</strong></td>
 </tr>
 <tr>
  <td colspan='10' style='color: #a80082; text-align: center; font-size: 14px; border-bottom: 1px solid #dedede;'><strong>{$lang['global-flowerscaps']}</strong></td>
 </tr>
 <tr>
  <td></td>
  <td><strong>{$lang['closeday-jars']}&nbsp;&nbsp;</strong></td>
  <td><strong>{$lang['closeday-woshake']}&nbsp;&nbsp;</strong></td>
  <td><strong>{$lang['closeday-intstash']}&nbsp;&nbsp;</strong></td>
  <td><strong>{$lang['closeday-extstash']}&nbsp;&nbsp;</strong></td>
  <td><strong>{$lang['global-total']}&nbsp;&nbsp;</strong></td>
  <td><strong>{$lang['closeday-woshake']}&nbsp;&nbsp;</strong></td>
  <td><strong>{$lang['global-delta']}&nbsp;&nbsp;</strong></td>
  <td><strong>{$lang['closeday-dispensed']}&nbsp;&nbsp;</strong></td>
 </tr>
EOD;
		echo $mailtoProductResponsibleFull;
			
	  echo $expenseDetails;
	  echo "</table>";

			
			
			
			
			
			
			
			
			
			
			
			
			
			
			
			

			
			
			
			
			
			
			
			
			
			
			
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
					
		} else {
			
			$_SESSION['errorMessage'] = $lang['no-closing-found'];
			
			// Closing does not exist, generate data

			
			// Total members today
			$selectMembers = "SELECT COUNT(memberno) from users WHERE memberno <> '0' AND userGroup < 6 AND DATE(registeredSince) <= DATE('$reportDate')";
		
			$result = mysql_query($selectMembers)
				or handleError($lang['error-loadsales'],"Error loading sale from db: " . mysql_error());
				
			$row = mysql_fetch_array($result);
				$currentmembers = $row['COUNT(memberno)'];
		
			// New members today
			$newMembers = "SELECT COUNT(user_id) FROM users where DATE(registeredSince) = DATE('$reportDate')";
		
			$result = mysql_query($newMembers)
				or handleError($lang['error-loadnewmembers'],"Error loading sale from db: " . mysql_error());
			
			$row = mysql_fetch_array($result);
				$newmembers = $row['COUNT(user_id)'];
		
			// Banned members today
			$bannedmembers = "SELECT COUNT(user_id) FROM users where DATE(banTime) = DATE('$reportDate')";
		
			$result = mysql_query($bannedmembers)
				or handleError($lang['error-loadnewmembers'],"Error loading sale from db: " . mysql_error());
			
			$row = mysql_fetch_array($result);
				$bannedmembers = $row['COUNT(user_id)'];
				
			// Deleted members today
			$deletedmembers = "SELECT COUNT(user_id) FROM users where DATE(deleteTime) = DATE('$reportDate')";
		
			$result = mysql_query($deletedmembers)
				or handleError($lang['error-loadnewmembers'],"Error loading sale from db: " . mysql_error());
			
			$row = mysql_fetch_array($result);
				$deletedmembers = $row['COUNT(user_id)'];
				
			// Look up todays dispenses
			$selectSales = "SELECT SUM(amount), SUM(quantity), SUM(realQuantity), SUM(units) from sales WHERE DATE(saletime) = DATE('$reportDate')";
		
			$result = mysql_query($selectSales)
				or handleError($lang['error-dispenseload'],"Error loading sale from db: " . mysql_error());
				
			$row = mysql_fetch_array($result);
				$salesToday = $row['SUM(amount)'];
				$quantitySold = $row['SUM(quantity)'];
				$quantitySoldReal = $row['SUM(realQuantity)'];
				$unitsSold = $row['SUM(units)'];
				
			// Look up todays dispenses by category 1
			$selectSalesFlower = "SELECT SUM(d.amount), SUM(d.quantity), SUM(d.realQuantity) from sales s, salesdetails d WHERE s.saleid = d.saleid AND DATE(s.saletime) = DATE('$reportDate') AND d.category = 1";
		
			$resultFlower = mysql_query($selectSalesFlower)
				or handleError($lang['error-dispenseload'],"Error loading sale from db: " . mysql_error());
			
			$row = mysql_fetch_array($resultFlower);
				$salesTodayFlower = $row['SUM(d.amount)'];
				$quantitySoldFlower = $row['SUM(d.quantity)'];
				$quantitySoldFlowerReal = $row['SUM(d.realQuantity)'];
				
			$flowerSalesPercentageToday = ($salesTodayFlower / $salesToday) * 100;
			$flowerGramsPercentageToday = ($quantitySoldFlower / $quantitySold) * 100;
			
			// Look up todays dispenses by category 2
			$selectSalesExtract = "SELECT SUM(d.amount), SUM(d.quantity), SUM(d.realQuantity) from sales s, salesdetails d WHERE s.saleid = d.saleid AND DATE(s.saletime) = DATE('$reportDate') AND d.category = 2";
		
			$resultExtract = mysql_query($selectSalesExtract)
				or handleError($lang['error-dispenseload'],"Error loading sale from db: " . mysql_error());
			
			$row = mysql_fetch_array($resultExtract);
				$salesTodayExtract = $row['SUM(d.amount)'];
				$quantitySoldExtract = $row['SUM(d.quantity)'];
				$quantitySoldExtractReal = $row['SUM(d.realQuantity)'];
				
			$extractSalesPercentageToday = ($salesTodayExtract / $salesToday) * 100;
			$extractGramsPercentageToday = ($quantitySoldExtract / $quantitySold) * 100;
			
			// Look up today's bar sales
			$selectBarSales = "SELECT SUM(amount), SUM(unitsTot) from b_sales WHERE DATE(saletime) = DATE('$reportDate')";
		
			$result = mysql_query($selectBarSales)
			or handleError($lang['error-loadsales'],"Error loading sale from db: " . mysql_error());
			
			$row = mysql_fetch_array($result);
				$barSales = $row['SUM(amount)'];
				$barUnits = $row['SUM(unitsTot)'];
		
			
		
			// Look up todays donations
			$selectDonations = "SELECT SUM(amount) from donations WHERE (donatedTo = 0 OR donatedTo = 1) AND DATE(donationTime) = DATE('$reportDate')";
		
			$donationResult = mysql_query($selectDonations)
				or handleError($lang['error-donationload'],"Error loading donations from db: " . mysql_error());
				
			$row = mysql_fetch_array($donationResult);
				$donations = $row['SUM(amount)'];
				
			// Look up todays bank donations
			$selectDonations = "SELECT SUM(amount) from donations WHERE donatedTo = 2 AND DATE(donationTime) = DATE('$reportDate')";
		
			$donationResult = mysql_query($selectDonations)
				or handleError($lang['error-donationload'],"Error loading donations from db: " . mysql_error());
				
			$row = mysql_fetch_array($donationResult);
				$bankDonations = $row['SUM(amount)'];
				
			// Look up todays donations
			$selectDonations = "SELECT COUNT(donationid) from donations WHERE (donatedTo = 0 OR donatedTo = 1) AND DATE(donationTime) = DATE('$reportDate')";
		
			$donationResult = mysql_query($selectDonations)
				or handleError($lang['error-donationload'],"Error loading donations from db: " . mysql_error());
				
			$row = mysql_fetch_array($donationResult);
				$donationsNo = $row['COUNT(donationid)'];
				
			// Look up todays bank donations
			$selectDonations = "SELECT COUNT(donationid) from donations WHERE donatedTo = 2 AND DATE(donationTime) = DATE('$reportDate')";
		
			$donationResult = mysql_query($selectDonations)
				or handleError($lang['error-donationload'],"Error loading donations from db: " . mysql_error());
				
			$row = mysql_fetch_array($donationResult);
				$bankDonationsNo = $row['COUNT(donationid)'];
					
				
			// Look up today's membership fees
			$selectMembershipFees = "SELECT SUM(amountPaid) FROM memberpayments WHERE paidTo <> 2 AND DATE(paymentdate) = DATE('$reportDate')";
						
			$result = mysql_query($selectMembershipFees)
				or handleError($lang['error-expensesload'],"Error loading expense from db: " . mysql_error());
					
			$row = mysql_fetch_array($result);
				$membershipFees = $row['SUM(amountPaid)'];
				
			// Look up today's membership fees Bank
			$selectMembershipFees = "SELECT SUM(amountPaid) FROM memberpayments WHERE paidTo = 2 AND DATE(paymentdate) = DATE('$reportDate')";
						
			$result = mysql_query($selectMembershipFees)
				or handleError($lang['error-expensesload'],"Error loading expense from db: " . mysql_error());
					
			$row = mysql_fetch_array($result);
				$membershipfeesBank = $row['SUM(amountPaid)'];
				
			// Calculate total income
			$totalIncome = $donations + $bankDonations + $membershipFees + $membershipfeesBank;
			
			// Look up today's till expenses
			$selectExpenses = "SELECT SUM(amount) FROM expenses WHERE DATE(registertime) = DATE('$reportDate') AND moneysource = 1";
					
			$expenseResult = mysql_query($selectExpenses)
				or handleError($lang['error-expensesload'],"Error loading expense from db: " . mysql_error());
				
			$row = mysql_fetch_array($expenseResult);
				$tillExpenses = $row['SUM(amount)'];
				
			// Look up today's bank expenses
			$selectExpenses = "SELECT SUM(amount) FROM expenses WHERE DATE(registertime) = DATE('$reportDate') AND moneysource = 2";
					
			$expenseResult = mysql_query($selectExpenses)
				or handleError($lang['error-expensesload'],"Error loading expense from db: " . mysql_error());
				
			$row = mysql_fetch_array($expenseResult);
				$bankExpenses = $row['SUM(amount)'];
				
				
			pageStart($lang['status'] . ": $reportDateReadable", NULL, NULL, "preporting", "daily", $lang['status'] . ": $reportDateReadable", $_SESSION['successMessage'], $_SESSION['errorMessage']);
				
		
			echo <<<EOD
<table style='color: #444; text-align: right; font-family: Tahoma, Verdana, sans-serif; font-size: 14px;'>
 <tr>
  <td colspan='8' style='color: #5aa242; text-align: left; font-size: 17px; border-bottom: 2px solid #a80082;'><strong>{$lang['bar-and-dispensary']}</strong></td>
 </tr>
 <tr>
  <td style='text-align: left;'>{$lang['dispensary']}</td>
  <td>{$expr(number_format($salesToday,2))} &euro;</td>
  <td></td>
  <td>{$expr(number_format($quantitySoldReal,2))} g.</td>
  <td>({$expr(number_format($quantitySold,2))} g.)</td>
  <td></td>
  <td>{$expr(number_format($unitsSold,2))} u.</td>
  <td></td>
 </tr>
 <tr>
  <td style='text-align: left;'><em>{$lang['closeday-owflowers']}</em></td>
  <td>{$expr(number_format($salesTodayFlower,2))} &euro;</td>
  <td>{$expr(number_format($flowerSalesPercentageToday,0))}%</td>
  <td>{$expr(number_format($quantitySoldFlowerReal,2))} g.</td>
  <td>({$expr(number_format($quantitySoldFlower,2))} g.)</td>
  <td>{$expr(number_format($flowerGramsPercentageToday,0))}%</td>
  <td></td>
  <td></td>
 </tr>
 <tr>
  <td style='text-align: left;'><em>{$lang['closeday-owextracts']}</em></td>
  <td>{$expr(number_format($salesTodayExtract,2))} &euro;</td>
  <td>{$expr(number_format($extractSalesPercentageToday,0))}%</td>
  <td>{$expr(number_format($quantitySoldExtractReal,2))} g.</td>
  <td>({$expr(number_format($quantitySoldExtract,2))} g.)</td>
  <td>{$expr(number_format($extractGramsPercentageToday,0))}%</td>
  <td></td>
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
				$customProducts .= " UNION ALL SELECT '$categoryid' AS category, pr.productid AS productid, pr.name AS name, p.purchaseid AS purchaseid, '' AS growtype, p.inMenu AS inMenu, p.closedAt AS closedAt, salesPrice AS gramPrice FROM products pr, purchases p WHERE p.category = $categoryid AND p.productid = pr.productid AND (p.closedAt IS NULL OR DATE(p.closingDate) >= DATE('$reportDate'))";
				
				
				// Look up sales in this cat
				$selectSalesOthers = "SELECT SUM(d.amount), SUM(d.realQuantity) from sales s, salesdetails d WHERE s.saleid = d.saleid AND DATE(s.saletime) = DATE('$reportDate') AND d.category = $categoryid";
			
				$resultOthers = mysql_query($selectSalesOthers)
					or handleError($lang['error-dispenseload'],"Error loading sale from db: " . mysql_error());
				
				$row = mysql_fetch_array($resultOthers);
					$salesTodayOthers = $row['SUM(d.amount)'];
					$quantitySoldOthers = $row['SUM(d.realQuantity)'];
					
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
		  <td></td>
		 </tr>
EOD;
				
				$i++;
			}
		
			echo <<<EOD
		 <tr>
		  <td colspan='8'></td>
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
		  <td colspan='8' style='color: #5aa242; text-align: left; font-size: 17px; border-bottom: 2px solid #a80082;'><strong>{$lang['closeday-finances']}</strong> $reportDateReadable</td>
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
		 <tr>
		  <td style='text-align: left;'>{$lang['closeday-bankexpenses']}</td>
		  <td>{$expr(number_format($bankExpenses,2))} &euro;</td>
		  <td></td>
		  <td></td>
		  <td></td>
		  <td></td>
		 </tr>
		 <tr rowspan='2'>
		  <td colspan='7'>&nbsp;</td>
		 </tr>
		 
EOD;
		
		
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
		
			$selectProducts = "SELECT '1' AS category, g.flowerid AS productid, g.name AS name, p.purchaseid AS purchaseid, p.growType AS growtype, p.inMenu AS inMenu, p.closedAt AS closedAt, salesPrice AS gramPrice FROM flower g, purchases p WHERE p.category = 1 AND p.productid = g.flowerid AND (p.closedAt IS NULL OR DATE(p.closingDate) >= DATE('$reportDate')) AND DATE(p.purchaseDate) <= DATE('$reportDate') UNION ALL SELECT '2' AS category, h.extractid AS productid, h.name AS name, p.purchaseid AS purchaseid, '' AS growtype, p.inMenu AS inMenu, p.closedAt AS closedAt, salesPrice AS gramPrice FROM extract h, purchases p WHERE p.category = 2 AND p.productid = h.extractid AND (p.closedAt IS NULL OR DATE(p.closingDate) >= DATE('$reportDate')) AND DATE(p.purchaseDate) <= DATE('$reportDate')";
			
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
		
	   				$purchaseLookup = "SELECT realQuantity from purchases where purchaseid = $purchaseid";
	   				
					$result = mysql_query($purchaseLookup)
						or handleError($lang['error-loadprodclosedetails'],"Error loading closing from db: " . mysql_error());
					
					$row = mysql_fetch_array($result);
						$openingWeight = $row['0'];
						
					// Look up todays dispenses
					$selectSales = "SELECT SUM(d.realQuantity) FROM salesdetails d, sales s WHERE DATE(s.saletime) = DATE('$reportDate') AND d.saleid = s.saleid AND d.purchaseid = $purchaseid";
		
					$result = mysql_query($selectSales)
						or handleError($lang['error-loadsales'],"Error loading sale from db: " . mysql_error());
				
					$row = mysql_fetch_array($result);
						$soldToday = $row['SUM(d.realQuantity)'];
		
					// Look up total dispenses
					$selectSalesTot = "SELECT SUM(d.realQuantity) FROM salesdetails d, sales s WHERE DATE(s.saletime) <= DATE('$reportDate') AND d.saleid = s.saleid AND d.purchaseid = $purchaseid";
		
					$resultTot = mysql_query($selectSalesTot)
						or handleError($lang['error-loadsales'],"Error loading sale from db: " . mysql_error());
				
					$rowTot = mysql_fetch_array($resultTot);
						$soldTotal = $rowTot['SUM(d.realQuantity)'];
						
						
					// Look up additions and removals
					$selectAdditions = "SELECT SUM(quantity) FROM productmovements WHERE purchaseid = $purchaseid AND type = 1 AND DATE(movementtime) <= DATE('$reportDate') AND movementTypeid < 23";
					$selectRemovals = "SELECT SUM(quantity) FROM productmovements WHERE purchaseid = $purchaseid AND type = 2 AND DATE(movementtime) <= DATE('$reportDate') AND movementTypeid < 23";
				
					$additions = mysql_query($selectAdditions)
						or handleError($lang['error-loadprodadditions'],"Error loading expense from db: " . mysql_error());
				
					$row = mysql_fetch_array($additions);
						$addedToday = $row['SUM(quantity)'];
						
					$removals = mysql_query($selectRemovals)
						or handleError($lang['error-loadprodadditions'],"Error loading expense from db: " . mysql_error());
				
					$row = mysql_fetch_array($removals);
						$takeoutsToday = $row['SUM(quantity)'];
						
						
					// Calculate jar weight:
					$jarWeight = $openingWeight + $addedToday - $takeoutsToday - $soldTotal;
					
		
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
		 </tr>
EOD;
						${'otherHeader' . $catID} = 'set';
						}
							
							
							$otherTotals[$catID]['catName'] = $catName;
							$otherTotals[$catID]['otherTotJar'] = $otherTotals[$catID]['otherTotJar'] + $jarWeight;
							$otherTotals[$catID]['otherTotIntSt'] = $otherTotals[$catID]['otherTotIntSt'] + $inStashInt;
							$otherTotals[$catID]['otherTotExtSt'] = $otherTotals[$catID]['otherTotExtSt'] + $inStashExt;
							$otherTotals[$catID]['otherTot'] = $otherTotals[$catID]['otherTotJar'] + $otherTotals[$catID]['otherTotIntSt'] + $otherTotals[$catID]['otherTotExtSt'];
							$otherTotals[$catID]['otherSoldToday'] = $otherTotals[$catID]['otherSoldToday'] + $soldToday;
							
		
		
		
						}
					
				  	if ($category < 3) {
						$productDetails .= <<<EOD
		 <tr>
		  <td style='text-align: left;'>{$name} <span class='smallerfont3'>{$growtype}</span></td>
		  <td>{$expr(number_format($jarWeight,2))} g.</td>
		  <td>{$expr(number_format($inStashInt,2))} g.</td>
		  <td>{$expr(number_format($inStashExt,2))} g.</td>
		  <td><strong>{$expr(number_format($weightTotal,2))} g.</strong></td>
		  <td>{$expr(number_format($weightPrice,2))} €</td>
		  <td>{$expr(number_format($soldToday,2))} g.</td>
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
		  <td>{$expr(number_format($weightPrice,2))} €</td>
		  <td>{$expr(number_format($soldToday,2))} u.</td>
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
		  <td colspan='8' style='color: #5aa242; text-align: left; font-size: 17px; border-bottom: 2px solid #a80082;'><strong>{$lang['closeday-productoverview']}</strong></td>
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
		  <td>{$expr(number_format($flowerWeightPrice,2))} €</td>
		  <td>{$expr(number_format($flowerSoldToday,2))} g.</td>
		 </tr>
		 <tr>
		  <td style='text-align: left;'>{$lang['global-extracts']}</td>
		  <td>{$expr(number_format($extractTotJar,2))} g.</td>
		  <td>{$expr(number_format($extractTotIntSt,2))} g.</td>
		  <td>{$expr(number_format($extractTotExtSt,2))} g.</td>
		  <td><strong>{$expr(number_format($extractTot,2))} g.</strong></td>
		  <td>{$expr(number_format($extractWeightPrice,2))} €</td>
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
		
		  

		  
			  echo $productOverview;
			  echo $otherProducts;
			  echo $productDetails;
			  echo "</table>";
			  echo $expenseDetails;
			  echo "</table>";
		
		}

		
} else {
					
			$reportDate = $_POST['reportDate'];
			$reportDateReadable = $_POST['reportDateReadable'];
			
			// See if a closing exists for this day
			$query = "SELECT closingid FROM closing WHERE DATE(closingtime) = DATE('$reportDate') ORDER by closingtime DESC LIMIT 1";
			
			$result = mysql_query($query)
				or handleError($lang['error-loadsales'],"Error loading sale from db: " . mysql_error());
				
			if (mysql_num_rows($result) == 1) {
				
				// Closing exists, pull data from closing tables
				
				
				$query = "SELECT closingid FROM closing WHERE DATE(closingtime) = DATE('$reportDate') ORDER by closingtime DESC LIMIT 1";
			
				$result = mysql_query($query)
					or handleError($lang['error-loadsales'],"Error loading sale from db: " . mysql_error());
				
				$row = mysql_fetch_array($result);
					$closingid = $row['closingid'];
					
				// Look up todays donations
				$selectDonations = "SELECT COUNT(donationid) from donations WHERE (donatedTo = 0 OR donatedTo = 1) AND DATE(donationTime) = DATE('$reportDate')";
			
				$donationResult = mysql_query($selectDonations)
					or handleError($lang['error-donationload'],"Error loading donations from db: " . mysql_error());
					
				$row = mysql_fetch_array($donationResult);
					$donationsNo = $row['COUNT(donationid)'];
					
				// Look up todays bank donations
				$selectDonations = "SELECT COUNT(donationid) from donations WHERE donatedTo = 2 AND DATE(donationTime) = DATE('$reportDate')";
			
				$donationResult = mysql_query($selectDonations)
					or handleError($lang['error-donationload'],"Error loading donations from db: " . mysql_error());
					
				$row = mysql_fetch_array($donationResult);
					$bankDonationsNo = $row['COUNT(donationid)'];
					
					
				// Select closing details
				$selectClosingData = "SELECT openingtime, closingtime, quantitySold, soldtoday, unitsSold, closingbalance, moneytaken, takenduringday, cashintill, bankBalance, newmembers, closedby, tillComment, expenses, membershipFees, estimatedTill, tillDelta, bankExpenses, prodOpening, prodStock, stockDelta, prodStockFlower, prodStockExtract, income, stockDeltaFlower, stockDeltaExtract, donations, bankDonations, renewedMembers, bannedMembers, deletedMembers, expiredMembers, totalMembers, activeMembers, intStash, extStash, totalWeight, totalNoShake, flowerintStash, flowerextStash, flowerweightNoShake, flowertotalWeight, flowertotalNoShake, extractintStash, extractextStash, extracttotalWeight, flowerDispensed, extractDispensed, soldTodayFlower, soldTodayExtract, membershipfeesBank, soldtodayBar, unitsSoldBar, openingBalance, openingBalanceBank, totCredit FROM closing WHERE closingid = $closingid";
			
				$closingResult = mysql_query($selectClosingData)
					or handleError($lang['error-loadsales'],"Error loading sale from db: " . mysql_error());
					
				$row = mysql_fetch_array($closingResult);
					$openingtime = $row['openingtime'];
					$closingtime = $row['closingtime'];
					$quantitySold = $row['quantitySold'];
					$soldtoday = $row['soldtoday'];
					$unitsSold = $row['unitsSold'];
					$closingbalance = $row['closingbalance'];
					$moneytaken = $row['moneytaken'];
					$bankedduringday = $row['takenduringday'];
					$cashintill = $row['cashintill'];
					$bankBalance = $row['bankBalance'];
					$newmembers = $row['newmembers'];
					$tillComment = $row['tillComment'];
					$expenses = $row['expenses'];
					$membershipFees = $row['membershipFees'];
					$estimatedTill = $row['estimatedTill'];
					$tillDelta = $row['tillDelta'];
					$bankExpenses = $row['bankExpenses'];
					$prodOpening = $row['prodOpening'];
					$prodStock = $row['prodStock'];
					$stockDelta = $row['stockDelta'];
					$prodStockFlower = $row['prodStockFlower'];
					$prodStockExtract = $row['prodStockExtract'];
					$income = $row['income'];
					$stockDeltaFlower = $row['stockDeltaFlower'];
					$stockDeltaExtract = $row['stockDeltaExtract'];
					$donations = $row['donations'];
					$bankDonations = $row['bankDonations'];
					$renewedMembers = $row['renewedMembers'];
					$bannedMembers = $row['bannedMembers'];
					$deletedMembers = $row['deletedMembers'];
					$expiredMembers = $row['expiredMembers'];
					$totalMembers = $row['totalMembers'];
					$activeMembers = $row['activeMembers'];
					$intStash = $row['intStash'];
					$extStash = $row['extStash'];
					$totalWeight = $row['totalWeight'];
					$totalNoShake = $row['totalNoShake'];
					$flowerintStash = $row['flowerintStash'];
					$flowerextStash = $row['flowerextStash'];
					$flowerweightNoShake = $row['flowerweightNoShake'];
					$flowertotalWeight = $row['flowertotalWeight'];
					$flowertotalNoShake = $row['flowertotalNoShake'];
					$extractintStash = $row['extractintStash'];
					$extractextStash = $row['extractextStash'];
					$extracttotalWeight = $row['extracttotalWeight'];
					$flowerDispensed = $row['flowerDispensed'];
					$extractDispensed = $row['extractDispensed'];
					$soldTodayFlower = $row['soldTodayFlower'];
					$soldTodayExtract = $row['soldTodayExtract'];
					$membershipfeesBank = $row['membershipfeesBank'];
					$barSales = $row['soldtodayBar'];
					$barUnits = $row['unitsSoldBar'];
					$openingBalance = $row['openingBalance'];
					$openingBalanceBank = $row['openingBalanceBank'];
					$totCredit = $row['totCredit'];
				
			pageStart($lang['status'] . ": $reportDateReadable", NULL, NULL, "preporting", "daily", $lang['status'] . ": $reportDateReadable", $_SESSION['successMessage'], $_SESSION['errorMessage']);
			
				
	// Look up &euro;, g, units
	$selectSales = "SELECT SUM(quantitySold), SUM(soldToday), SUM(unitsSold) FROM closing WHERE closingid = $closingid";

	$resultSales = mysql_query($selectSales)
		or handleError($lang['error-dispenseload'],"Error loading sale from db: " . mysql_error());
		
	$row = mysql_fetch_array($resultSales);
		$salesToday = $row['SUM(soldToday)'];
		$quantitySold = $row['SUM(quantitySold)'];
		$unitsSold = $row['SUM(unitsSold)'];
	
	// Look up todays dispenses by category 1
	$selectSalesFlower = "SELECT SUM(d.amount), SUM(d.quantity) from sales s, salesdetails d WHERE s.saleid = d.saleid AND s.saletime BETWEEN '$openingtime' AND '$closingtime' AND d.category = 1";

	$resultFlower = mysql_query($selectSalesFlower)
		or handleError($lang['error-dispenseload'],"Error loading sale from db: " . mysql_error());
		
	
	$row = mysql_fetch_array($resultFlower);
		$flowerSalesToday = $row['SUM(d.amount)'];
		$flowerGramsToday = $row['SUM(d.quantity)'];
		
	$flowerSalesPercentageToday = ($flowerSalesToday / $salesToday) * 100;
	$flowerGramsPercentageToday = ($flowerGramsToday / $quantitySold) * 100;
	
	// Look up todays dispenses by category 2
	$selectSalesExtract = "SELECT SUM(d.amount), SUM(d.quantity) from sales s, salesdetails d WHERE s.saleid = d.saleid AND s.saletime BETWEEN '$openingtime' AND '$closingtime' AND d.category = 2";

	$resultExtract = mysql_query($selectSalesExtract)
		or handleError($lang['error-dispenseload'],"Error loading sale from db: " . mysql_error());
	
	$row = mysql_fetch_array($resultExtract);
		$extractSalesToday = $row['SUM(d.amount)'];
		$extractGramsToday = $row['SUM(d.quantity)'];
		
	$extractSalesPercentageToday = ($extractSalesToday / $salesToday) * 100;
	$extractGramsPercentageToday = ($extractGramsToday / $quantitySold) * 100;
			
		// Compose mail to admin
		$mailtoadmin = <<<EOD
<table style='color: #444; text-align: right; font-family: Tahoma, Verdana, sans-serif; font-size: 14px;'>
 <tr>
  <td colspan='10' style='color: #5aa242; text-align: left; font-size: 17px; border-bottom: 2px solid #a80082;'><strong>{$lang['bar-and-dispensary']}</strong></td>
 </tr>
 <tr>
  <td style='text-align: left;'>{$lang['dispensary']}</td>
  <td>{$expr(number_format($salesToday,2))} &euro;</td>
  <td></td>
  <td>{$expr(number_format($quantitySold,2))} g.</td>
  <td></td>
  <td>{$expr(number_format($unitsSold,2))} u.</td>
  <td></td>
 </tr>
 <tr>
  <td style='text-align: left;'><em>{$lang['closeday-owflowers']}</em></td>
  <td>{$expr(number_format($flowerSalesToday,2))} &euro;</td>
  <td>{$expr(number_format($flowerSalesPercentageToday,0))}%</td>
  <td>{$expr(number_format($flowerDispensed,2))} g.</td>
  <td>{$expr(number_format($flowerGramsPercentageToday,0))}%</td>
  <td></td>
 </tr>
 <tr>
  <td style='text-align: left;'><em>{$lang['closeday-owextracts']}</em></td>
  <td>{$expr(number_format($extractSalesToday,2))} &euro;</td>
  <td>{$expr(number_format($extractSalesPercentageToday,0))}%</td>
  <td>{$expr(number_format($extractDispensed,2))} g.</td>
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
		
		
		// Create more product queries for each category - to be used in a bigger query further down!
		$customProducts .= " UNION ALL SELECT '$categoryid' AS category, pr.name, '' AS breed2, c.purchaseid, c.soldToday, c.weight, c.weightDelta, c.intStash, c.extStash, c.weightNoShake, c.totalWeight, c.totalNoShake, c.inMenu, c.specificComment FROM products pr, closingdetails c WHERE c.category = $categoryid AND c.productid = pr.productid AND c.closingid = $closingid";
				
		
		// Look up sales in this cat
		$selectSalesOthers = "SELECT SUM(d.amount), SUM(d.quantity) from sales s, salesdetails d WHERE s.saleid = d.saleid AND s.saletime BETWEEN '$openingtime' AND '$closingtime' AND d.category = $categoryid";
		
	
		$resultOthers = mysql_query($selectSalesOthers)
			or handleError($lang['error-dispenseload'],"Error loading sale from db: " . mysql_error());
		
		$row = mysql_fetch_array($resultOthers);
			$salesTodayOthers = $row['SUM(d.amount)'];
			$quantitySoldOthers = $row['SUM(d.quantity)'];
			
		$othersSalesPercentageToday = ($salesTodayOthers / $salesToday) * 100;
		$othersGramsPercentageToday = ($quantitySoldOthers / $unitsSold) * 100;

		
		$mailtoadmin .= <<<EOD
 <tr>
  <td style='text-align: left;'><em>{$lang['closeday-ow']} $name</em></td>
  <td>{$expr(number_format($salesTodayOthers,2))} &euro;</td>
  <td>{$expr(number_format($othersSalesPercentageToday,0))}%</td>
  <td></td>
  <td></td>
  <td>{$expr(number_format($quantitySoldOthers,2))} u.</td>
  <td>{$expr(number_format($othersGramsPercentageToday,0))}%</td>
 </tr>
EOD;
		
		$i++;
	}

		$mailtoadmin .= <<<EOD
 <tr>
  <td colspan='10'></td>
 </tr>
 <tr>
  <td style='text-align: left;'>{$lang['bar']}</td>
  <td>{$expr(number_format($barSales,2))} &euro;</td>
  <td></td>
  <td></td>
  <td></td>
  <td>{$expr(number_format($barUnits,2))} u.</td>
  <td></td>
 </tr>
 <tr rowspan='2'>
  <td colspan='10'>&nbsp;</td>
 </tr>
 <tr>
  <td colspan='10' style='color: #5aa242; text-align: left; font-size: 17px; border-bottom: 2px solid #a80082;'><strong>Member details</strong></td>
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
  <td>$totalMembers</td>
  <td></td>
  <td></td>
  <td></td>
  <td></td>
 </tr>
 <tr>
  <td style='text-align: left;'>{$lang['active-members']}</td>
  <td>$activeMembers</td>
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
  <td>$expiredMembers</td>
  <td></td>
  <td></td>
  <td></td>
  <td></td>
 </tr>
 <tr>
  <td style='text-align: left;'>{$lang['banned-members']}</td>
  <td>$bannedMembers</td>
  <td></td>
  <td></td>
  <td></td>
  <td></td>
 </tr>
 <tr>
  <td style='text-align: left;'>{$lang['deleted-members']}</td>
  <td>$deletedMembers</td>
  <td></td>
  <td></td>
  <td></td>
  <td></td>
 </tr>
 <tr rowspan='2'>
  <td colspan='10'>&nbsp;</td>
 </tr>
 <tr>
  <td colspan='10' style='color: #5aa242; text-align: left; font-size: 17px; border-bottom: 2px solid #a80082;'><strong>{$lang['closeday-finances']}</strong> $reportDateReadable</td>
 </tr>
 <tr>
  <td style='text-align: left;'>{$lang['closeday-donations-till']}</td>
  <td>{$expr(number_format($donations,2))} &euro;</td>
  <td style='text-align: left;'>($donationsNo)</td>
  <td style='vertical-align: bottom; text-align: left; font-size: 14px;'><strong>{$lang['till-calculation']}</strong></td>
  <td></td>
  <td></td>
  <td style='vertical-align: bottom; text-align: left; font-size: 14px;'><strong>{$lang['bank-calculation']}</strong></td>
  <td></td>
 </tr>
 <tr>
  <td style='text-align: left;'>{$lang['closeday-donations-bank']}</td>
  <td>{$expr(number_format($bankDonations,2))} &euro;</td>
  <td style='text-align: left;'>($bankDonationsNo)</td>
  <td style='text-align: left;'>{$lang['closeday-tillatopening']}:</td>
  <td>{$expr(number_format($openingBalance,2))} &euro;</td>
  <td></td>
  <td style='text-align: left;'>{$lang['closeday-openingtoday']}:</td>
  <td>{$expr(number_format($openingBalanceBank,2))} &euro;</td>
 </tr>
 <tr>
  <td style='text-align: left;'>{$lang['closeday-membershipfees-till']}</td>
  <td>{$expr(number_format($membershipFees,2))} &euro;</td>
  <td></td>
  <td style='text-align: left;'>+ {$lang['closeday-membershipfees-till']}:</td>
  <td>{$expr(number_format($membershipFees,2))} &euro;</td>
  <td></td>
  <td style='text-align: left;'>+ {$lang['closeday-membershipfees-bank']}:</td>
  <td>{$expr(number_format($membershipfeesBank,2))} &euro;</td>
 </tr>
 <tr>
  <td style='text-align: left; border-bottom: 1px dashed #ababab;'>{$lang['closeday-membershipfees-bank']}</td>
  <td style='border-bottom: 1px dashed #ababab;'>{$expr(number_format($membershipfeesBank,2))} &euro;</td>
  <td></td>
  <td style='text-align: left;'>+ {$lang['global-donations']}:</td>
  <td>{$expr(number_format($donations,2))} &euro;</td>
  <td></td>
  <td style='text-align: left;'>+ {$lang['global-donations']}:</td>
  <td>{$expr(number_format($bankDonations,2))} &euro;</td>
 </tr>
 <tr>
  <td style='text-align: left; border-bottom: 1px solid #ababab;'>{$lang['closeday-totalincome']}</td>
  <td style='border-bottom: 1px solid #ababab;'>{$expr(number_format($income,2))} &euro;</td>
  <td></td>
  <td style='text-align: left;'>- {$lang['closeday-tillexpenses']}:</td>
  <td>{$expr(number_format($expenses,2))} &euro;</td>
  <td></td>
  <td style='text-align: left;'>+ {$lang['banked-now']}:</td>
  <td>{$expr(number_format($moneytaken + $bankedduringday,2))} &euro;</td>
 </tr>
 <tr>
  <td style='text-align: left;'>{$lang['closeday-tillexpenses']}</td>
  <td>{$expr(number_format($expenses,2))} &euro;</td>
  <td></td>
  <td style='text-align: left; border-bottom: 1px dashed #ababab;'>- {$lang['closeday-moneybanked']}:</td>
  <td style='text-align: right; border-bottom: 1px dashed #ababab;'>{$expr(number_format($moneytaken + $bankedduringday,2))} &euro;</td>
  <td></td>
  <td style='text-align: left;'>+ {$lang['banked-during-day']}:</td>
  <td>{$expr(number_format($bankedduringday,2))} &euro;</td>
 </tr>
 <tr>
  <td style='text-align: left;'><strong>{$lang['closeday-banked']}</strong></td>
  <td><strong>{$expr(number_format($moneytaken,2))} &euro;</strong></td>
  <td></td>
  <td style='text-align: left; border-bottom: 1px solid #ababab;'>{$lang['closeday-estimatedtill']}:</td>
  <td style='text-align: right; border-bottom: 1px solid #ababab;'>{$expr(number_format($estimatedTill,2))} &euro;</td>
  <td></td>
  <td style='text-align: left; border-bottom: 1px dashed #ababab;'>- {$lang['global-expenses']}:</td>
  <td style='text-align: right; border-bottom: 1px dashed #ababab;'>{$expr(number_format($bankExpenses,2))} &euro;</td>
 </tr>
 <tr>
  <td style='text-align: left;'>{$lang['closeday-tillbalance']}</td>
  <td>{$expr(number_format($cashintill,2))} &euro;</td>
  <td></td>
  <td style='text-align: left;'>{$lang['closeday-yourcount']}:</td>
  <td>{$expr(number_format($cashintill,2))} &euro;</td>
  <td></td>
  <td style='text-align: left; border-bottom: 1px solid #ababab;'>{$lang['closeday-bankbalance']}:</td>
  <td style='text-align: right; border-bottom: 1px solid #ababab;'>{$expr(number_format($bankBalance,2))} &euro;</td>
 </tr>
 <tr>
  <td style='text-align: left;'>{$lang['closeday-tilldelta']}</td>
  <td>{$expr(number_format($tillDelta,2))} &euro;</td>
  <td></td>
  <td style='text-align: left;'><strong>{$lang['global-delta']}:</strong></td>
  <td><strong>{$expr(number_format($tillDelta,2))} &euro;</strong></td>
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
  <td></td>
  <td></td>
 </tr>
 <tr>
  <td style='text-align: left;'>{$lang['closeday-bankbalance']}</td>
  <td>{$expr(number_format($bankBalance,2))} &euro;</td>
  <td></td>
  <td></td>
  <td></td>
  <td></td>
  <td></td>
  <td></td>
 </tr>
 <tr>
  <td style='text-align: left;'>{$lang['closeday-totalclubbalance']}</td>
  <td>{$expr(number_format($closingbalance,2))} &euro;</td>
  <td></td>
  <td></td>
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
  <td></td>
  <td></td>
 </tr>
 <tr>
  <td colspan='10' style='text-align: left;'>{$lang['closeday-tillcomment']}:<br /><em>$tillComment</em> </td>
 </tr>
 <tr rowspan='2'>
  <td colspan='10'>&nbsp;</td>
 </tr>
</table>
<table style='color: #444; text-align: right; font-family: Tahoma, Verdana, sans-serif; font-size: 14px;'>
EOD;

				

		
		$defaultProducts = "SELECT category, f.name, f.breed2, c.purchaseid, c.soldToday, c.weight, c.weightDelta, c.intStash, c.extStash, c.weightNoShake, c.totalWeight, c.totalNoShake, c.inMenu, c.specificComment FROM flower f, closingdetails c WHERE c.category = '1' AND c.productid = f.flowerid AND c.closingid = $closingid UNION ALL SELECT category, e.name, '' AS breed2, c.purchaseid, c.soldToday, c.weight, c.weightDelta, c.intStash, c.extStash, c.weightNoShake, c.totalWeight, c.totalNoShake, c.inMenu, c.specificComment FROM extract e, closingdetails c WHERE c.category = '2' AND c.productid = e.extractid AND c.closingid = $closingid";
		
		$allProducts = $defaultProducts . $customProducts;
		
		$productsResult = mysql_query($allProducts)
			or handleError($lang['error-loadsales'],"Error loading sale from db: " . mysql_error());
			
		$productsResult2 = mysql_query($allProducts)
			or handleError($lang['error-loadsales'],"Error loading sale from db: " . mysql_error());
		
		while ($product = mysql_fetch_array($productsResult)) {
			
			$category = $product['category'];
			$name = $product['name'];
			$breed2 = $product['breed2'];
			$purchaseid = $product['purchaseid'];
			$soldToday = $product['soldToday'];
			$weight = $product['weight'];
			$weightDelta = $product['weightDelta'];
			$intStash = $product['intStash'];
			$extStash = $product['extStash'];
			$weightNoShake = $product['weightNoShake'];
			$totalWeight = $product['totalWeight'];
			$totalNoShake = $product['totalNoShake'];
			$inMenu = $product['inMenu'];
			$specificComment = $product['specificComment'];
			
			if ($specificComment != '') {
				
				$commentInset = <<<EOD
 <tr>
  <td colspan='10' style='text-align: left;'>{$lang['global-comment']}: <em>$specificComment</em></td>
 </tr>
EOD;

			} else {
				$commentInset = '';
			}
			
			if ($breed2 != '') {
				$name = $name . " x " . $breed2;
			}
			
			
			if ($category == '1') {
				
				// Look up growtype and closed status
				$selectGrowTypeNo = "SELECT growType, closedAt FROM purchases WHERE purchaseid = $purchaseid";
				
				$growTypeNoResult = mysql_query($selectGrowTypeNo)
					or handleError($lang['error-loadsales'],"Error loading sale from db: " . mysql_error());

				$row = mysql_fetch_array($growTypeNoResult);
					$growTypeNo = $row['growType'];
					$closedAt = $row['closedAt'];
					
				if ($growTypeNo != '') {
					
					$growDetails = "SELECT growtype FROM growtypes WHERE growtypeid = $growTypeNo";
									
					$resultGrowType = mysql_query($growDetails)
						or handleError($lang['error-growtypeload'],"Error loading growtype: " . mysql_error());
						
					$row = mysql_fetch_array($resultGrowType);
						$growtype = $row['growtype'];
						
					if ($growtype != '') {
						$growtype = "(" . $growtype . ")";
					} else {
						$growtype = '';
					}
					
				} else {
					
					$growtype = '';
					
				}
				
				if ($closedAt != NULL) {
					$productStatus = "Closed";
				} else if ($inMenu == 0) {
					$productStatus = "Not in menu";
				} else {
					$productStatus = "In menu";
				}


					
				$mailtoProductResponsible .= <<<EOD
 <tr>
  <td style='text-align: left;'>$name <span class='smallerfont'>$growtype</span></td>
  <td>{$expr(number_format($weight,2))} g.</td>
  <td>{$expr(number_format($weightNoShake,2))} g.</td>
  <td>{$expr(number_format($intStash,2))} g.</td>
  <td>{$expr(number_format($extStash,2))} g.</td>
  <td><strong>{$expr(number_format($totalWeight,2))} g.</strong></td>
  <td><strong>{$expr(number_format($totalNoShake,2))} g.</strong></td>
  <td>{$expr(number_format($weightDelta,2))} g.</td>
  <td>{$expr(number_format($soldToday,2))} g.</td>
 </tr>
$commentInset
EOD;

			} else if ($dividersetExtract != 'yes') {
				
				// Look up closed status
				$selectGrowTypeNo = "SELECT closedAt FROM purchases WHERE purchaseid = $purchaseid";
				
				$growTypeNoResult = mysql_query($selectGrowTypeNo)
					or handleError($lang['error-loadsales'],"Error loading sale from db: " . mysql_error());

				$row = mysql_fetch_array($growTypeNoResult);
					$closedAt = $row['closedAt'];

				if ($closedAt != NULL) {
					$productStatus = "Closed";
				} else if ($inMenu == 0) {
					$productStatus = "Not in menu";
				} else {
					$productStatus = "In menu";
				}
				
				// insert divider
				$dividersetExtract = 'yes';
				$mailtoProductResponsible .= <<<EOD
 <tr>
  <td colspan='10'></td>
 </tr>
 <tr>
  <td colspan='10' style='color: #a80082; text-align: center; font-size: 14px; border-top: 1px solid #dedede; border-bottom: 1px solid #dedede;'><strong>{$lang['global-extractscaps']}</strong></td>
 </tr>
 <tr>
  <td></td>
  <td><strong>{$lang['closeday-jars']}&nbsp;&nbsp;</strong></td>
  <td><strong></strong></td>
  <td><strong>{$lang['closeday-intstash']}&nbsp;&nbsp;</strong></td>
  <td><strong>{$lang['closeday-extstash']}&nbsp;&nbsp;</strong></td>
  <td><strong>{$lang['global-total']}&nbsp;&nbsp;</strong></td>
  <td><strong></strong></td>
  <td><strong>{$lang['global-delta']}&nbsp;&nbsp;</strong></td>
  <td><strong>{$lang['closeday-dispensed']}&nbsp;&nbsp;</strong></td>
 </tr>
 <tr>
  <td style='text-align: left;'>$name</td>
  <td>{$expr(number_format($weight,2))} g.</td>
  <td></td>
  <td>{$expr(number_format($intStash,2))} g.</td>
  <td>{$expr(number_format($extStash,2))} g.</td>
  <td><strong>{$expr(number_format($totalWeight,2))} g.</strong></td>
  <td></td>
  <td>{$expr(number_format($weightDelta,2))} g.</td>
  <td>{$expr(number_format($soldToday,2))} g.</td>
 </tr>	
$commentInset
EOD;

			} else if ($category == '2') {
				
				// Look up closed status
				$selectGrowTypeNo = "SELECT closedAt FROM purchases WHERE purchaseid = $purchaseid";
				
				$growTypeNoResult = mysql_query($selectGrowTypeNo)
					or handleError($lang['error-loadsales'],"Error loading sale from db: " . mysql_error());

				$row = mysql_fetch_array($growTypeNoResult);
					$closedAt = $row['closedAt'];

				
				$mailtoProductResponsible .= <<<EOD
 <tr>
  <td style='text-align: left;'>$name</td>
  <td>{$expr(number_format($weight,2))} g.</td>
  <td></td>
  <td>{$expr(number_format($intStash,2))} g.</td>
  <td>{$expr(number_format($extStash,2))} g.</td>
  <td><strong>{$expr(number_format($totalWeight,2))} g.</strong></td>
  <td></td>
  <td>{$expr(number_format($weightDelta,2))} g.</td>
  <td>{$expr(number_format($soldToday,2))} g.</td>
 </tr>	
$commentInset
EOD;

			} else {
				
				// Look up closed status
				$selectGrowTypeNo = "SELECT closedAt FROM purchases WHERE purchaseid = $purchaseid";
				
				$growTypeNoResult = mysql_query($selectGrowTypeNo)
					or handleError($lang['error-loadsales'],"Error loading sale from db: " . mysql_error());

				$row = mysql_fetch_array($growTypeNoResult);
					$closedAt = $row['closedAt'];

				if ($closedAt != NULL) {
					$productStatus = "Closed";
				} else if ($inMenu == 0) {
					$productStatus = "Not in menu";
				} else {
					$productStatus = "In menu";
				}

				// See if header has been set
				if (${'otherHeader' . $category} != 'set') {
				
					// Look up categories
					$selectCats = "SELECT name FROM categories WHERE id = $category";
				
					$resultCats = mysql_query($selectCats)
						or handleError($lang['error-loadflowers'],"Error loading flower from db: " . mysql_error());
						
					$catRow = mysql_fetch_array($resultCats);
						$categoryname = $catRow['name'];
	
				
					// insert divider
					$mailtoProductResponsible .= <<<EOD
 <tr>
  <td colspan='10'></td>
 </tr>
 <tr>
  <td colspan='10' style='color: #a80082; text-align: center; font-size: 14px; border-top: 1px solid #dedede; border-bottom: 1px solid #dedede;'><strong>$categoryname</strong></td>
 </tr>
 <tr>
  <td></td>
  <td></td>
  <td></td>
  <td><strong>{$lang['closeday-intstash']}&nbsp;&nbsp;</strong></td>
  <td><strong>{$lang['closeday-extstash']}&nbsp;&nbsp;</strong></td>
  <td></td>
  <td></td>
  <td></td>
  <td><strong>{$lang['closeday-dispensed']}&nbsp;&nbsp;</strong></td>
 </tr>
 <tr>
  <td style='text-align: left;'>$name</td>
  <td></td>
  <td></td>
  <td>{$expr(number_format($intStash,2))} u.</td>
  <td>{$expr(number_format($extStash,2))} u.</td>
  <td></td>
  <td></td>
  <td></td>
  <td>{$expr(number_format($soldToday,2))} u.</td>
 </tr>	
$commentInset
EOD;

					${'otherHeader' . $category} = 'set';
						
				} else {
				
					$mailtoProductResponsible .= <<<EOD
 <tr>
  <td style='text-align: left;'>$name</td>
  <td></td>
  <td></td>
  <td>{$expr(number_format($intStash,2))} u.</td>
  <td>{$expr(number_format($extStash,2))} u.</td>
  <td></td>
  <td></td>
  <td></td>
  <td>{$expr(number_format($soldToday,2))} u.</td>
 </tr>	
$commentInset
EOD;

				}

			}

		} // Ends products loop
		


		
		$mailtoProductResponsible .= "</table>";
			
	
				
		
		
		
		
		
		
		
		
		  
			// Query to look up expenses
			$selectExpenses = "SELECT expenseid, registertime, userid, expensetype, expense, moneysource, other, amount, shop, comment, receipt, expensecategory FROM expenses WHERE DATE(registertime) = DATE('$reportDate') ORDER by registertime DESC";
		
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


		$mailtoProductResponsibleFull .= $mailtoProductResponsible;
		
		echo $mailtoadmin;
		echo <<<EOD
 <tr>
  <td colspan='10' style='color: #5aa242; text-align: left; font-size: 17px; border-bottom: 2px solid #a80082;'><strong>{$lang['closeday-productoverview']}</strong></td>
 </tr>
 <tr>
  <td></td>
  <td><strong>{$lang['closeday-jars']}&nbsp;&nbsp;</strong></td>
  <td><strong>{$lang['closeday-woshake']}&nbsp;&nbsp;</strong></td>
  <td><strong>{$lang['closeday-intstash']}&nbsp;&nbsp;</strong></td>
  <td><strong>{$lang['closeday-extstash']}&nbsp;&nbsp;</strong></td>
  <td><strong>{$lang['global-total']}&nbsp;&nbsp;</strong></td>
  <td><strong>{$lang['closeday-woshake']}&nbsp;&nbsp;</strong></td>
  <td><strong>{$lang['global-delta']}&nbsp;&nbsp;</strong></td>
  <td><strong>{$lang['closeday-dispensed']}&nbsp;&nbsp;</strong></td>
 </tr>
 <tr>

  <td style='text-align: left;'>{$lang['global-flowers']}</td>
  <td>{$expr(number_format($prodStockFlower,2))} g.</td>
  <td>{$expr(number_format($flowerweightNoShake,2))} g.</td>
  <td>{$expr(number_format($flowerintStash,2))} g.</td>
  <td>{$expr(number_format($flowerextStash,2))} g.</td>
  <td><strong>{$expr(number_format($flowertotalWeight,2))} g.</strong></td>
  <td><strong>{$expr(number_format($flowertotalNoShake,2))} g.</strong></td>
  <td>{$expr(number_format($stockDeltaFlower,2))} g.</td>
  <td>{$expr(number_format($flowerDispensed,2))} g.</td>
 </tr>
 <tr>
  <td style='text-align: left;'>{$lang['global-extracts']}</td>
  <td>{$expr(number_format($prodStockExtract,2))} g.</td>
  <td>{$expr(number_format($prodStockExtract,2))} g.</td>
  <td>{$expr(number_format($extractintStash,2))} g.</td>
  <td>{$expr(number_format($extractextStash,2))} g.</td>
  <td><strong>{$expr(number_format($extracttotalWeight,2))} g.</strong></td>
  <td><strong>{$expr(number_format($extracttotalWeight,2))} g.</strong></td>
  <td>{$expr(number_format($stockDeltaExtract,2))} g.</td>
  <td>{$expr(number_format($extractDispensed,2))} g.</td>
 </tr>
 <tr style='border-top: 1px solid #888;'>
  <td style='text-align: left;'><strong>TOTAL</strong></td>
  <td><strong>{$expr(number_format($prodStockFlower + $prodStockExtract,2))} g.</strong></td>
  <td><strong>{$expr(number_format($flowerweightNoShake + $prodStockExtract,2))} g.</strong></td>
  <td><strong>{$expr(number_format($flowerintStash + $extractintStash,2))} g.</strong></td>
  <td><strong>{$expr(number_format($flowerextStash + $extractextStash,2))} g.</strong></td>
  <td><strong>{$expr(number_format($flowertotalWeight + $extracttotalWeight,2))} g.</strong></td>
  <td><strong>{$expr(number_format($flowertotalNoShake + $extracttotalWeight,2))} g.</strong></td>
  <td><strong>{$expr(number_format($stockDeltaFlower + $stockDeltaExtract,2))} g.</strong></td>
  <td><strong>{$expr(number_format($flowerGramsTodayReal + $extractGramsTodayReal,2))} g.</strong></td>
 </tr>
 <tr rowspan='2'>
  <td colspan='10'>&nbsp;</td>
 </tr>
 <tr>
  <td colspan='10' style='color: #5aa242; text-align: left; font-size: 17px; border-bottom: 2px solid #a80082;'><strong>{$lang['closeday-productdetails']}</strong></td>
 </tr>
 <tr>
  <td colspan='10' style='color: #a80082; text-align: center; font-size: 14px; border-bottom: 1px solid #dedede;'><strong>{$lang['global-flowerscaps']}</strong></td>
 </tr>
 <tr>
  <td></td>
  <td><strong>{$lang['closeday-jars']}&nbsp;&nbsp;</strong></td>
  <td><strong>{$lang['closeday-woshake']}&nbsp;&nbsp;</strong></td>
  <td><strong>{$lang['closeday-intstash']}&nbsp;&nbsp;</strong></td>
  <td><strong>{$lang['closeday-extstash']}&nbsp;&nbsp;</strong></td>
  <td><strong>{$lang['global-total']}&nbsp;&nbsp;</strong></td>
  <td><strong>{$lang['closeday-woshake']}&nbsp;&nbsp;</strong></td>
  <td><strong>{$lang['global-delta']}&nbsp;&nbsp;</strong></td>
  <td><strong>{$lang['closeday-dispensed']}&nbsp;&nbsp;</strong></td>
 </tr>
EOD;
		echo $mailtoProductResponsibleFull;
			
	  echo $expenseDetails;
	  echo "</table>";

			
			
			
			
			
			
			
			
			
			
			
			
			
			
			
			

			
			
			
			
			
			
			
			
			
			
			
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
					
		} else {
			
			$_SESSION['errorMessage'] = $lang['no-closing-found'];
			
			// Closing does not exist, generate data

			
			// Total members today
			$selectMembers = "SELECT COUNT(memberno) from users WHERE memberno <> '0' AND userGroup < 6 AND DATE(registeredSince) <= DATE('$reportDate')";
		
			$result = mysql_query($selectMembers)
				or handleError($lang['error-loadsales'],"Error loading sale from db: " . mysql_error());
				
			$row = mysql_fetch_array($result);
				$currentmembers = $row['COUNT(memberno)'];
		
			// New members today
			$newMembers = "SELECT COUNT(user_id) FROM users where DATE(registeredSince) = DATE('$reportDate')";
		
			$result = mysql_query($newMembers)
				or handleError($lang['error-loadnewmembers'],"Error loading sale from db: " . mysql_error());
			
			$row = mysql_fetch_array($result);
				$newmembers = $row['COUNT(user_id)'];
		
			// Banned members today
			$bannedmembers = "SELECT COUNT(user_id) FROM users where DATE(banTime) = DATE('$reportDate')";
		
			$result = mysql_query($bannedmembers)
				or handleError($lang['error-loadnewmembers'],"Error loading sale from db: " . mysql_error());
			
			$row = mysql_fetch_array($result);
				$bannedmembers = $row['COUNT(user_id)'];
				
			// Deleted members today
			$deletedmembers = "SELECT COUNT(user_id) FROM users where DATE(deleteTime) = DATE('$reportDate')";
		
			$result = mysql_query($deletedmembers)
				or handleError($lang['error-loadnewmembers'],"Error loading sale from db: " . mysql_error());
			
			$row = mysql_fetch_array($result);
				$deletedmembers = $row['COUNT(user_id)'];
				
			// Look up todays dispenses
			$selectSales = "SELECT SUM(amount), SUM(quantity), SUM(units) from sales WHERE DATE(saletime) = DATE('$reportDate')";
		
			$result = mysql_query($selectSales)
				or handleError($lang['error-dispenseload'],"Error loading sale from db: " . mysql_error());
				
			$row = mysql_fetch_array($result);
				$salesToday = $row['SUM(amount)'];
				$quantitySold = $row['SUM(quantity)'];
				$unitsSold = $row['SUM(units)'];
				
			// Look up todays dispenses by category 1
			$selectSalesFlower = "SELECT SUM(d.amount), SUM(d.quantity) from sales s, salesdetails d WHERE s.saleid = d.saleid AND DATE(s.saletime) = DATE('$reportDate') AND d.category = 1";
		
			$resultFlower = mysql_query($selectSalesFlower)
				or handleError($lang['error-dispenseload'],"Error loading sale from db: " . mysql_error());
			
			$row = mysql_fetch_array($resultFlower);
				$salesTodayFlower = $row['SUM(d.amount)'];
				$quantitySoldFlower = $row['SUM(d.quantity)'];
				
			$flowerSalesPercentageToday = ($salesTodayFlower / $salesToday) * 100;
			$flowerGramsPercentageToday = ($quantitySoldFlower / $quantitySold) * 100;
			
			// Look up todays dispenses by category 2
			$selectSalesExtract = "SELECT SUM(d.amount), SUM(d.quantity) from sales s, salesdetails d WHERE s.saleid = d.saleid AND DATE(s.saletime) = DATE('$reportDate') AND d.category = 2";
		
			$resultExtract = mysql_query($selectSalesExtract)
				or handleError($lang['error-dispenseload'],"Error loading sale from db: " . mysql_error());
			
			$row = mysql_fetch_array($resultExtract);
				$salesTodayExtract = $row['SUM(d.amount)'];
				$quantitySoldExtract = $row['SUM(d.quantity)'];
				
			$extractSalesPercentageToday = ($salesTodayExtract / $salesToday) * 100;
			$extractGramsPercentageToday = ($quantitySoldExtract / $quantitySold) * 100;
			
			// Look up today's bar sales
			$selectBarSales = "SELECT SUM(amount), SUM(unitsTot) from b_sales WHERE DATE(saletime) = DATE('$reportDate')";
		
			$result = mysql_query($selectBarSales)
			or handleError($lang['error-loadsales'],"Error loading sale from db: " . mysql_error());
			
			$row = mysql_fetch_array($result);
				$barSales = $row['SUM(amount)'];
				$barUnits = $row['SUM(unitsTot)'];
		
			
		
			// Look up todays donations
			$selectDonations = "SELECT SUM(amount) from donations WHERE (donatedTo = 0 OR donatedTo = 1) AND DATE(donationTime) = DATE('$reportDate')";
		
			$donationResult = mysql_query($selectDonations)
				or handleError($lang['error-donationload'],"Error loading donations from db: " . mysql_error());
				
			$row = mysql_fetch_array($donationResult);
				$donations = $row['SUM(amount)'];
				
			// Look up todays bank donations
			$selectDonations = "SELECT SUM(amount) from donations WHERE donatedTo = 2 AND DATE(donationTime) = DATE('$reportDate')";
		
			$donationResult = mysql_query($selectDonations)
				or handleError($lang['error-donationload'],"Error loading donations from db: " . mysql_error());
				
			$row = mysql_fetch_array($donationResult);
				$bankDonations = $row['SUM(amount)'];
				
			// Look up todays donations
			$selectDonations = "SELECT COUNT(donationid) from donations WHERE (donatedTo = 0 OR donatedTo = 1) AND DATE(donationTime) = DATE('$reportDate')";
		
			$donationResult = mysql_query($selectDonations)
				or handleError($lang['error-donationload'],"Error loading donations from db: " . mysql_error());
				
			$row = mysql_fetch_array($donationResult);
				$donationsNo = $row['COUNT(donationid)'];
				
			// Look up todays bank donations
			$selectDonations = "SELECT COUNT(donationid) from donations WHERE donatedTo = 2 AND DATE(donationTime) = DATE('$reportDate')";
		
			$donationResult = mysql_query($selectDonations)
				or handleError($lang['error-donationload'],"Error loading donations from db: " . mysql_error());
				
			$row = mysql_fetch_array($donationResult);
				$bankDonationsNo = $row['COUNT(donationid)'];
					
				
			// Look up today's membership fees
			$selectMembershipFees = "SELECT SUM(amountPaid) FROM memberpayments WHERE paidTo <> 2 AND DATE(paymentdate) = DATE('$reportDate')";
						
			$result = mysql_query($selectMembershipFees)
				or handleError($lang['error-expensesload'],"Error loading expense from db: " . mysql_error());
					
			$row = mysql_fetch_array($result);
				$membershipFees = $row['SUM(amountPaid)'];
				
			// Look up today's membership fees Bank
			$selectMembershipFees = "SELECT SUM(amountPaid) FROM memberpayments WHERE paidTo = 2 AND DATE(paymentdate) = DATE('$reportDate')";
						
			$result = mysql_query($selectMembershipFees)
				or handleError($lang['error-expensesload'],"Error loading expense from db: " . mysql_error());
					
			$row = mysql_fetch_array($result);
				$membershipfeesBank = $row['SUM(amountPaid)'];
				
			// Calculate total income
			$totalIncome = $donations + $bankDonations + $membershipFees + $membershipfeesBank;
			
			// Look up today's till expenses
			$selectExpenses = "SELECT SUM(amount) FROM expenses WHERE DATE(registertime) = DATE('$reportDate') AND moneysource = 1";
					
			$expenseResult = mysql_query($selectExpenses)
				or handleError($lang['error-expensesload'],"Error loading expense from db: " . mysql_error());
				
			$row = mysql_fetch_array($expenseResult);
				$tillExpenses = $row['SUM(amount)'];
				
			// Look up today's bank expenses
			$selectExpenses = "SELECT SUM(amount) FROM expenses WHERE DATE(registertime) = DATE('$reportDate') AND moneysource = 2";
					
			$expenseResult = mysql_query($selectExpenses)
				or handleError($lang['error-expensesload'],"Error loading expense from db: " . mysql_error());
				
			$row = mysql_fetch_array($expenseResult);
				$bankExpenses = $row['SUM(amount)'];
				
				
			pageStart($lang['status'] . ": $reportDateReadable", NULL, NULL, "preporting", "daily", $lang['status'] . ": $reportDateReadable", $_SESSION['successMessage'], $_SESSION['errorMessage']);
				
		
			echo <<<EOD
<table style='color: #444; text-align: right; font-family: Tahoma, Verdana, sans-serif; font-size: 14px;'>
 <tr>
  <td colspan='7' style='color: #5aa242; text-align: left; font-size: 17px; border-bottom: 2px solid #a80082;'><strong>{$lang['bar-and-dispensary']}</strong></td>
 </tr>
 <tr>
  <td style='text-align: left;'>{$lang['dispensary']}</td>
  <td>{$expr(number_format($salesToday,2))} &euro;</td>
  <td></td>
  <td>{$expr(number_format($quantitySold,2))} g.</td>
  <td></td>
  <td>{$expr(number_format($unitsSold,2))} u.</td>
  <td></td>
 </tr>
 <tr>
  <td style='text-align: left;'><em>{$lang['closeday-owflowers']}</em></td>
  <td>{$expr(number_format($salesTodayFlower,2))} &euro;</td>
  <td>{$expr(number_format($flowerSalesPercentageToday,0))}%</td>
  <td>{$expr(number_format($quantitySoldFlower,2))} g.</td>
  <td>{$expr(number_format($flowerGramsPercentageToday,0))}%</td>
  <td></td>
 </tr>
 <tr>
  <td style='text-align: left;'><em>{$lang['closeday-owextracts']}</em></td>
  <td>{$expr(number_format($salesTodayExtract,2))} &euro;</td>
  <td>{$expr(number_format($extractSalesPercentageToday,0))}%</td>
  <td>{$expr(number_format($quantitySoldExtract,2))} g.</td>
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
				$customProducts .= " UNION ALL SELECT '$categoryid' AS category, pr.productid AS productid, pr.name AS name, p.purchaseid AS purchaseid, '' AS growtype, p.inMenu AS inMenu, p.closedAt AS closedAt, salesPrice AS gramPrice FROM products pr, purchases p WHERE p.category = $categoryid AND p.productid = pr.productid AND (p.closedAt IS NULL OR DATE(p.closingDate) >= DATE('$reportDate'))";
				
				
				// Look up sales in this cat
				$selectSalesOthers = "SELECT SUM(d.amount), SUM(d.quantity) from sales s, salesdetails d WHERE s.saleid = d.saleid AND DATE(s.saletime) = DATE('$reportDate') AND d.category = $categoryid";
			
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
		  <td>{$expr(number_format($barUnits,2))} u.</td>
		  <td></td>
		 </tr>
		 <tr rowspan='2'>
		  <td colspan='7'>&nbsp;</td>
		 </tr>
		 <tr>
		  <td colspan='7' style='color: #5aa242; text-align: left; font-size: 17px; border-bottom: 2px solid #a80082;'><strong>Member details</strong></td>
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
		  <td colspan='7'>&nbsp;</td>
		 </tr>
		 <tr>
		  <td colspan='7' style='color: #5aa242; text-align: left; font-size: 17px; border-bottom: 2px solid #a80082;'><strong>{$lang['closeday-finances']}</strong> $reportDateReadable</td>
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
		 <tr>
		  <td style='text-align: left;'>{$lang['closeday-bankexpenses']}</td>
		  <td>{$expr(number_format($bankExpenses,2))} &euro;</td>
		  <td></td>
		  <td></td>
		  <td></td>
		  <td></td>
		 </tr>
		 <tr rowspan='2'>
		  <td colspan='7'>&nbsp;</td>
		 </tr>
		 
EOD;
		
		
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
		
			$selectProducts = "SELECT '1' AS category, g.flowerid AS productid, g.name AS name, p.purchaseid AS purchaseid, p.growType AS growtype, p.inMenu AS inMenu, p.closedAt AS closedAt, salesPrice AS gramPrice FROM flower g, purchases p WHERE p.category = 1 AND p.productid = g.flowerid AND (p.closedAt IS NULL OR DATE(p.closingDate) >= DATE('$reportDate')) AND DATE(p.purchaseDate) <= DATE('$reportDate') UNION ALL SELECT '2' AS category, h.extractid AS productid, h.name AS name, p.purchaseid AS purchaseid, '' AS growtype, p.inMenu AS inMenu, p.closedAt AS closedAt, salesPrice AS gramPrice FROM extract h, purchases p WHERE p.category = 2 AND p.productid = h.extractid AND (p.closedAt IS NULL OR DATE(p.closingDate) >= DATE('$reportDate')) AND DATE(p.purchaseDate) <= DATE('$reportDate')";
			
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
		
	   				$purchaseLookup = "SELECT realQuantity from purchases where purchaseid = $purchaseid";
	   				
					$result = mysql_query($purchaseLookup)
						or handleError($lang['error-loadprodclosedetails'],"Error loading closing from db: " . mysql_error());
					
					$row = mysql_fetch_array($result);
						$openingWeight = $row['0'];
						
					// Look up todays dispenses
					$selectSales = "SELECT SUM(d.quantity) FROM salesdetails d, sales s WHERE DATE(s.saletime) = DATE('$reportDate') AND d.saleid = s.saleid AND d.purchaseid = $purchaseid";
		
					$result = mysql_query($selectSales)
						or handleError($lang['error-loadsales'],"Error loading sale from db: " . mysql_error());
				
					$row = mysql_fetch_array($result);
						$soldToday = $row['SUM(d.quantity)'];
		
					// Look up total dispenses
					$selectSalesTot = "SELECT SUM(d.quantity) FROM salesdetails d, sales s WHERE DATE(s.saletime) <= DATE('$reportDate') AND d.saleid = s.saleid AND d.purchaseid = $purchaseid";
		
					$resultTot = mysql_query($selectSalesTot)
						or handleError($lang['error-loadsales'],"Error loading sale from db: " . mysql_error());
				
					$rowTot = mysql_fetch_array($resultTot);
						$soldTotal = $rowTot['SUM(d.quantity)'];
						
						
					// Look up additions and removals
					$selectAdditions = "SELECT SUM(quantity) FROM productmovements WHERE purchaseid = $purchaseid AND type = 1 AND DATE(movementtime) <= DATE('$reportDate') AND movementTypeid < 23";
					$selectRemovals = "SELECT SUM(quantity) FROM productmovements WHERE purchaseid = $purchaseid AND type = 2 AND DATE(movementtime) <= DATE('$reportDate') AND movementTypeid < 23";
				
					$additions = mysql_query($selectAdditions)
						or handleError($lang['error-loadprodadditions'],"Error loading expense from db: " . mysql_error());
				
					$row = mysql_fetch_array($additions);
						$addedToday = $row['SUM(quantity)'];
						
					$removals = mysql_query($selectRemovals)
						or handleError($lang['error-loadprodadditions'],"Error loading expense from db: " . mysql_error());
				
					$row = mysql_fetch_array($removals);
						$takeoutsToday = $row['SUM(quantity)'];
						
						
					// Calculate jar weight:
					$jarWeight = $openingWeight + $addedToday - $takeoutsToday - $soldTotal;
					
		
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
		 </tr>
EOD;
						${'otherHeader' . $catID} = 'set';
						}
							
							
							$otherTotals[$catID]['catName'] = $catName;
							$otherTotals[$catID]['otherTotJar'] = $otherTotals[$catID]['otherTotJar'] + $jarWeight;
							$otherTotals[$catID]['otherTotIntSt'] = $otherTotals[$catID]['otherTotIntSt'] + $inStashInt;
							$otherTotals[$catID]['otherTotExtSt'] = $otherTotals[$catID]['otherTotExtSt'] + $inStashExt;
							$otherTotals[$catID]['otherTot'] = $otherTotals[$catID]['otherTotJar'] + $otherTotals[$catID]['otherTotIntSt'] + $otherTotals[$catID]['otherTotExtSt'];
							$otherTotals[$catID]['otherSoldToday'] = $otherTotals[$catID]['otherSoldToday'] + $soldToday;
							
		
		
		
						}
					
				  	if ($category < 3) {
						$productDetails .= <<<EOD
		 <tr>
		  <td style='text-align: left;'>{$name} <span class='smallerfont3'>{$growtype}</span></td>
		  <td>{$expr(number_format($jarWeight,2))} g.</td>
		  <td>{$expr(number_format($inStashInt,2))} g.</td>
		  <td>{$expr(number_format($inStashExt,2))} g.</td>
		  <td><strong>{$expr(number_format($weightTotal,2))} g.</strong></td>
		  <td>{$expr(number_format($weightPrice,2))} €</td>
		  <td>{$expr(number_format($soldToday,2))} g.</td>
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
		  <td>{$expr(number_format($weightPrice,2))} €</td>
		  <td>{$expr(number_format($soldToday,2))} u.</td>
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
		  <td colspan='8' style='color: #5aa242; text-align: left; font-size: 17px; border-bottom: 2px solid #a80082;'><strong>{$lang['closeday-productoverview']}</strong></td>
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
		  <td>{$expr(number_format($flowerWeightPrice,2))} €</td>
		  <td>{$expr(number_format($flowerSoldToday,2))} g.</td>
		 </tr>
		 <tr>
		  <td style='text-align: left;'>{$lang['global-extracts']}</td>
		  <td>{$expr(number_format($extractTotJar,2))} g.</td>
		  <td>{$expr(number_format($extractTotIntSt,2))} g.</td>
		  <td>{$expr(number_format($extractTotExtSt,2))} g.</td>
		  <td><strong>{$expr(number_format($extractTot,2))} g.</strong></td>
		  <td>{$expr(number_format($extractWeightPrice,2))} €</td>
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
		
		  

		  
			  echo $productOverview;
			  echo $otherProducts;
			  echo $productDetails;
			  echo "</table>";
			  echo $expenseDetails;
			  echo "</table>";
		
		}

}

}
displayFooter();