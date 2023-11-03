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
			
	if ($_SESSION['domain'] == 'exclusive' || $_SESSION['domain'] == 'strainhunters') {
		header("Location: index-split.php");
		exit();
	}
	
		// If no closing ID is set, we display the list of closing dates
		if (!isset($_POST['reportDate']) && !isset($_GET['closingid'])) {
			
			$query = "SELECT openAndClose FROM systemsettings";
			try
			{
				$result = $pdo3->prepare("$query");
				$result->execute();
			}
			catch (PDOException $e)
			{
					$error = 'Error fetching user: ' . $e->getMessage();
					echo $error;
					exit();
			}
		
			$row = $result->fetch();
				$openAndClose = $row['openAndClose'];
				
				
			// No open & close
			if ($openAndClose == 0 ||  $_SESSION['domain'] == 'choko') {
			
				// Find first sales date
				$findStartDate = "SELECT logtime FROM log ORDER BY logtime ASC LIMIT 1";
				try
				{
					$result = $pdo3->prepare("$findStartDate");
					$result->execute();
				}
				catch (PDOException $e)
				{
						$error = 'Error fetching user: ' . $e->getMessage();
						echo $error;
						exit();
				}
			
				$row = $result->fetch();
					$startDate = date('Y-m-d', strtotime($row['logtime']));
			
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
	   				$reportDateReadable = date('dS M Y', $reportDate);
					$reportDateSQL = date("Y-m-d", $reportDate);
	    			$reportDate -= 86400;
	    			
	    			// exclude today's date
	
						$output .= <<<EOD
   <tr>
    <td><form action='' method='POST'><input type='hidden' name='reportDate' value='$reportDateSQL'><input type='hidden' name='reportDateReadable<br />' value='$reportDateReadable'><button type='submit' class='linkStyleNew'>$reportDateReadable<br /></button></form></td>
   </tr>
EOD;
					
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
			
						pageStart($lang['daily-reports'], NULL, NULL, "preporting", "daily", $lang['daily-reports'], $_SESSION['successMessage'], $_SESSION['errorMessage']);
				
					echo <<<EOD
 <table class='default'>
  <thead>
   <tr>
    <th><center>{$lang['pur-date']}</center></th>
   </tr>
  </thead>
  <tbody>
   $output
  </tbody>
 </table>
EOD;
				
			// Only closing
			} else if ($openAndClose == 2) {
				
    			$query = "SELECT closingid, closingtime FROM closing ORDER BY closingtime DESC";
				try
				{
					$result = $pdo3->prepare("$query");
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
			
					foreach ($data as $row) {
		
						$closingid = $row['closingid'];
						$closingtime = date("d-m-Y H:i", strtotime($row['closingtime']."+$offsetSec seconds"));
						$closingtimeSQL = $row['closingtime'];
						
						// Find previous closingtime (use as openingtime)
		    			$query = "SELECT closingid, closingtime FROM closing WHERE closingtime < '$closingtimeSQL' ORDER BY closingtime DESC LIMIT 1";
						try
						{
							$result = $pdo3->prepare("$query");
							$result->execute();
						}
						catch (PDOException $e)
						{
								$error = 'Error fetching user: ' . $e->getMessage();
								echo $error;
								exit();
						}
						
						$row = $result->fetch();
							$openingtime = date("d-m-Y H:i", strtotime($row['closingtime']."+$offsetSec seconds"));
							$openingtimeSQL = $row['closingtime'];
							
						$output .= <<<EOD
   <tr>
    <td class='clickableRow' href='?closingid=$closingid&ot=$openingtime&ct=$closingtime&ots=$openingtimeSQL&cts=$closingtimeSQL' style='padding: 10px; padding-right: 50px;'>$openingtime</td>
    <td class='clickableRow' href='?closingid=$closingid&ot=$openingtime&ct=$closingtime&ots=$openingtimeSQL&cts=$closingtimeSQL' style='padding: 10px;'>$closingtime</td>
   </tr>  
EOD;
						
						
					}
					
					pageStart($lang['daily-reports'], NULL, NULL, "preporting", "daily", $lang['daily-reports'], $_SESSION['successMessage'], $_SESSION['errorMessage']);
					
					echo <<<EOD
 <table class='default'>
  <thead>
   <tr>
    <th><center>{$lang['opening']}&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</center></th>
    <th><center>{$lang['closing']}</center></th>
   </tr>
  </thead>
  <tbody>
   $output
  </tbody>
 </table>
EOD;
				
				// No closing found! Check your system settings
				} else {
				
					$_SESSION['errorMessage'] = $lang['no-closing-found-settings'];
					pageStart($lang['daily-reports'], NULL, NULL, "preporting", "daily", $lang['daily-reports'], $_SESSION['successMessage'], $_SESSION['errorMessage']);
					
				}
			
			// Open & close	
			} else if ($openAndClose > 2) {
				
    			$query = "SELECT openingtime FROM opening ORDER BY openingtime DESC";
				try
				{
					$result = $pdo3->prepare("$query");
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
			
					foreach ($data as $row) {
		
						$openingtime = date("d-m-Y H:i", strtotime($row['openingtime']."+$offsetSec seconds"));
						$openingtimeSQL = $row['openingtime'];
						
						// Find the closing
		    			$query = "SELECT closingid, closingtime FROM closing WHERE closingtime > '$openingtimeSQL'";
						try
						{
							$result2 = $pdo3->prepare("$query");
							$result2->execute();
							$data2 = $result2->fetchAll();
						}
						catch (PDOException $e)
						{
								$error = 'Error fetching user: ' . $e->getMessage();
								echo $error;
								exit();
						}
						
						if ($data2) {
						
							$clickable = 'clickableRow';
							$row2 = $data2[0];
								$closingtime = date("d-m-Y H:i", strtotime($row2['closingtime']."+$offsetSec seconds"));
								$closingtimeSQL = $row2['closingtime'];
								$closingid = $row2['closingid'];
								
								
						} else {
							
							$clickable = '';
							$closingtime = '';
							$closingid = '';
							
						}
													
						$output .= <<<EOD
   <tr>
    <td class='$clickable' href='?closingid=$closingid&ot=$openingtime&ct=$closingtime&ots=$openingtimeSQL&cts=$closingtimeSQL' style='padding: 10px; padding-right: 50px;'>$openingtime</td>
    <td class='$clickable' href='?closingid=$closingid&ot=$openingtime&ct=$closingtime&ots=$openingtimeSQL&cts=$closingtimeSQL' style='padding: 10px;'>$closingtime</td>
   </tr>  
EOD;
						
					}
					
					pageStart($lang['daily-reports'], NULL, NULL, "preporting", "daily", $lang['daily-reports'], $_SESSION['successMessage'], $_SESSION['errorMessage']);
					
					echo <<<EOD
 <table class='default'>
  <thead>
   <tr>
    <th><center>{$lang['opening']}&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</center></th>
    <th><center>{$lang['closing']}</center></th>
   </tr>
  </thead>
  <tbody>
   $output
  </tbody>
 </table>
EOD;
				
				// No closing found! Check your system settings
				} else {
				
					$_SESSION['errorMessage'] = $lang['no-closing-found-settings'];
					pageStart($lang['daily-reports'], NULL, NULL, "preporting", "daily", $lang['daily-reports'], $_SESSION['successMessage'], $_SESSION['errorMessage']);
					
				}
				
			}
				
				
			
			
		// If a closing ID is set, we display the report
		} else {

if ($_SESSION['realWeight'] == 1) {
			
			// Check if closingid exists + get timestamps
			if (isset($_GET['closingid'])) {
				
				$closingid = $_GET['closingid'];
				$closingReadable = $_GET['ct'];
				$closingSQL = $_GET['cts'];
				$openingReadable = $_GET['ot'];
				$openingSQL = $_GET['ots'];
						
				// Look up todays donations
				$selectDonations = "SELECT COUNT(donationid) from donations WHERE (donatedTo < 2 OR donatedTo = 4) AND (donationTime BETWEEN '$openingSQL' AND '$closingSQL')";
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
					$donationsNo = $row['COUNT(donationid)'];
					
				// Look up todays bank donations
				$selectDonations = "SELECT COUNT(donationid) from donations WHERE donatedTo = 2 AND (donationTime BETWEEN '$openingSQL' AND '$closingSQL')";
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
					$bankDonationsNo = $row['COUNT(donationid)'];
					
					
				// Select closing details
				$selectClosingData = "SELECT openingtime, closingtime, quantitySold, soldtoday, unitsSold, closingbalance, moneytaken, takenduringday, cashintill, bankBalance, newmembers, closedby, tillComment, expenses, membershipFees, estimatedTill, tillDelta, bankExpenses, prodOpening, prodStock, stockDelta, prodStockFlower, prodStockExtract, income, stockDeltaFlower, stockDeltaExtract, donations, bankDonations, renewedMembers, bannedMembers, deletedMembers, expiredMembers, totalMembers, activeMembers, intStash, extStash, totalWeight, totalNoShake, flowerintStash, flowerextStash, flowerweightNoShake, flowertotalWeight, flowertotalNoShake, extractintStash, extractextStash, extracttotalWeight, flowerDispensed, extractDispensed, soldTodayFlower, soldTodayExtract, membershipfeesBank, soldtodayBar, unitsSoldBar, openingBalance, openingBalanceBank, totCredit, quantitySoldReal, soldTodayFlowerReal, soldTodayExtractReal FROM closing WHERE closingid = $closingid";
				try
				{
					$result = $pdo3->prepare("$selectClosingData");
					$result->execute();
				}
				catch (PDOException $e)
				{
						$error = 'Error fetching user: ' . $e->getMessage();
						echo $error;
						exit();
				}
			
				$row = $result->fetch();
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
				
				pageStart($lang['daily-reports'], NULL, NULL, "preporting", "daily", $lang['daily-reports'], $_SESSION['successMessage'], $_SESSION['errorMessage']);
			
				echo "<h1>$openingReadable {$lang['to']} $closingReadable</h1>";

				// Look up &euro;, g, units
				$selectSales = "SELECT SUM(quantitySold), SUM(soldToday), SUM(unitsSold) FROM closing WHERE closingid = $closingid";
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
					$salesToday = $row['SUM(soldToday)'];
					$quantitySold = $row['SUM(quantitySold)'];
					$unitsSold = $row['SUM(unitsSold)'];
					
				if ($_SESSION['creditOrDirect'] == 0) {
					
					// Look up dispensed today cash
					$selectSales = "SELECT SUM(amount) from sales WHERE (saletime BETWEEN '$openingSQL' AND '$closingSQL') AND direct < 2";
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
					$selectSales = "SELECT SUM(amount) from sales WHERE (saletime BETWEEN '$openingSQL' AND '$closingSQL') AND direct = 2";
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
					$selectSales = "SELECT SUM(amount) from b_sales WHERE (saletime BETWEEN '$openingSQL' AND '$closingSQL') AND direct < 2";
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
					$selectSales = "SELECT SUM(amount) from b_sales WHERE (saletime BETWEEN '$openingSQL' AND '$closingSQL') AND direct = 2";
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
	$selectCats = "SELECT id, name, type from categories WHERE id > 2 ORDER by name ASC";
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
	

		$i = 0;
		
		while ($category = $resultCats->fetch()) {
		
		$categoryid = $category['id'];
		$name = $category['name'];
		$type = $category['type'];
		
		// Create more product queries for each category - to be used in a bigger query further down!
		$customProducts .= " UNION ALL SELECT '$categoryid' AS category, categoryType, pr.name, '' AS breed2, c.purchaseid, c.soldToday, c.weight, c.weightDelta, c.intStash, c.extStash, c.weightNoShake, c.totalWeight, c.totalNoShake, c.inMenu, c.specificComment, c.value FROM products pr, closingdetails c WHERE c.category = $categoryid AND c.productid = pr.productid AND c.closingid = $closingid";
				
		
		if ($_SESSION['openAndClose'] == 2 && $openingid == '') {
			
		} else {
			
			// Look up sales in this cat
			$selectSalesOthers = "SELECT SUM(d.amount), SUM(d.quantity), SUM(d.realQuantity) from sales s, salesdetails d WHERE s.saleid = d.saleid AND s.saletime BETWEEN '$openingtime' AND '$closingtime' AND d.category = $categoryid";
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
				$othersGramsPercentageToday = ($quantitySoldOthers / $quantitySoldReal) * 100;
				
			}

		
		if ($type == 0) {
		$unitCatSummary .=  <<<EOD
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
		} else {
			
		$gramCatSummary .=  <<<EOD
 <tr>
  <td style='text-align: left;'><em>{$lang['closeday-ow']} $name</em></td>
  <td>{$expr(number_format($salesTodayOthers,2))} &euro;</td>
  <td>{$expr(number_format($othersSalesPercentageToday,0))}%</td>
  <td>{$expr(number_format($quantitySoldOthersReal,2))} g.</td>
EOD;

		if ($_SESSION['realWeight'] == 1) {
		$gramCatSummary .=  <<<EOD
  <td>({$expr(number_format($quantitySoldOthers,2))} g.)</td>			
EOD;

		}
		
		$gramCatSummary .=  <<<EOD
  <td>{$expr(number_format($othersGramsPercentageToday,0))}%</td>
  <td></td>
  <td></td>
 </tr>
EOD;
		}

		
		$i++;
		
	}

	}
	
		$mailtoadmin .= $gramCatSummary;
		$mailtoadmin .= $unitCatSummary;

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
  <td colspan='10' style='color: #5aa242; text-align: left; font-size: 17px; border-bottom: 2px solid #a80082;'><strong>{$lang['member-details']}</strong></td>
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
  <td colspan='10' style='color: #5aa242; text-align: left; font-size: 17px; border-bottom: 2px solid #a80082;'><strong>{$lang['closeday-finances']}</strong></td>
 </tr>
 <tr>
  <td style='vertical-align: bottom; text-align: center; font-size: 14px;' colspan="2"><strong>{$lang['summary']}</strong></td>
  <td></td>
  <td style='vertical-align: bottom; text-align: center; font-size: 14px;' colspan="2"><strong>{$lang['till-calculation']}</strong></td>
  <td></td>
  <td style='vertical-align: bottom; text-align: center; font-size: 14px;' colspan="2"><strong>{$lang['bank-calculation']}</strong></td>
 </tr>
 <tr>
  <td style='text-align: left;'>{$lang['closeday-donations-till']}</td>
  <td>{$expr(number_format($donations,2))} &euro;</td>
  <td style='text-align: left;'>($donationsNo)</td>
  <td style='text-align: left;'>{$lang['closeday-tillatopening']}</td>
  <td>{$expr(number_format($openingBalance,2))} &euro;</td>
  <td></td>
  <td style='text-align: left;'>{$lang['bank-opening']}:</td>
  <td>{$expr(number_format($openingBalanceBank,2))} &euro;</td>
 </tr>
 <tr>
  <td style='text-align: left;'>+ {$lang['closeday-donations-bank']}</td>
  <td>{$expr(number_format($bankDonations,2))} &euro;</td>
  <td style='text-align: left;'>($bankDonationsNo)</td>
  <td style='text-align: left;'>+ {$lang['memberfees']}</td>
  <td>{$expr(number_format($membershipFees,2))} &euro;</td>
  <td></td>
  <td style='text-align: left;'>+ {$lang['memberfees']}</td>
  <td>{$expr(number_format($membershipfeesBank,2))} &euro;</td>
 </tr>
 <tr>
  <td style='text-align: left;'>+ {$lang['closeday-membershipfees-till']}</td>
  <td>{$expr(number_format($membershipFees,2))} &euro;</td>
  <td></td>
  <td style='text-align: left;'>+ {$lang['global-donations']}</td>
  <td>{$expr(number_format($donations,2))} &euro;</td>
  <td></td>
  <td style='text-align: left;'>+ {$lang['global-donations']}</td>
  <td>{$expr(number_format($bankDonations,2))} &euro;</td>
 </tr>
EOD;
	if ($_SESSION['creditOrDirect'] == 0) {
		
		$mailtoadmin .= <<<EOD
 <tr>
  <td style='text-align: left;'>+ {$lang['closeday-membershipfees-bank']}</td>
  <td>{$expr(number_format($membershipfeesBank,2))} &euro;</td>
  <td></td>
  <td style='text-align: left;'>+ {$lang['dispensed-direct']}</td>
  <td>{$expr(number_format($salesTodayCash,2))} &euro;</td>
  <td></td>
  <td style='text-align: left;'>+ {$lang['dispensed-direct']}</td>
  <td>{$expr(number_format($salesTodayBank,2))} &euro;</td>
 </tr>
 <tr>
  <td style='text-align: left;'>+ {$lang['dispensed-direct-till']}</td>
  <td>{$expr(number_format($salesTodayCash,2))} &euro;</td>
  <td></td>
  <td style='text-align: left;'>+ {$lang['direct-bar-sales']}</td>
  <td>{$expr(number_format($salesTodayBarCash,2))} &euro;</td>
  <td></td>
  <td style='text-align: left;'>+ {$lang['direct-bar-sales']}</td>
  <td>{$expr(number_format($salesTodayBarBank,2))} &euro;</td>
 </tr>
 <tr>
  <td style='text-align: left;'>+ {$lang['dispensed-direct-bank']}</td>
  <td>{$expr(number_format($salesTodayBank,2))} &euro;</td>
  <td></td>
  <td style='text-align: left;'>-&nbsp; {$lang['closeday-moneybanked']}</td>
  <td style='text-align: right;'>{$expr(number_format($moneytaken + $bankedduringday,2))} &euro;</td>
  <td></td>
  <td style='text-align: left;'>+ {$lang['closeday-moneybanked']}</td>
  <td>{$expr(number_format($moneytaken + $bankedduringday,2))} &euro;</td>
 </tr>
 <tr>
  <td style='text-align: left;'>+ {$lang['direct-bar-sales-till']}</td>
  <td>{$expr(number_format($salesTodayBarCash,2))} &euro;</td>
  <td></td>
  <td style='text-align: left; border-bottom: 1px dashed #ababab;''>-&nbsp; {$lang['global-expenses']}</td>
  <td style='text-align: right; border-bottom: 1px dashed #ababab;'>{$expr(number_format($expenses,2))} &euro;</td>
  <td></td>
  <td style='text-align: left; border-bottom: 1px dashed #ababab;'>-&nbsp; {$lang['global-expenses']}</td>
  <td style='text-align: right; border-bottom: 1px dashed #ababab;'>{$expr(number_format($bankExpenses,2))} &euro;</td>
 </tr>
 <tr>
  <td style='text-align: left; border-bottom: 1px dashed #ababab;'>+ {$lang['direct-bar-sales-bank']}</td>
  <td style='text-align: right; border-bottom: 1px dashed #ababab;'>{$expr(number_format($salesTodayBarBank,2))} &euro;</td>
  <td></td>
  <td style='text-align: left; border-bottom: 1px solid #ababab;'>= {$lang['closeday-estimatedtill']}</td>
  <td style='text-align: right; border-bottom: 1px solid #ababab;'>{$expr(number_format($estimatedTill,2))} &euro;</td>
  <td></td>
  <td style='text-align: left; border-bottom: 1px solid #ababab;'>= {$lang['closeday-bankbalance']}</td>
  <td style='text-align: right; border-bottom: 1px solid #ababab;'>{$expr(number_format($bankBalance,2))} &euro;</td>
 </tr>
 <tr>
  <td style='text-align: left; border-bottom: 1px solid #ababab;'>= {$lang['closeday-totalincome']}</td>
  <td style='border-bottom: 1px solid #ababab;'>{$expr(number_format($income,2))} &euro;</td>
  <td></td>
  <td style='text-align: left;'>{$lang['closeday-yourcount']}</td>
  <td>{$expr(number_format($cashintill,2))} &euro;</td>
  <td></td>
  <td></td>
  <td></td>
 </tr>
 <tr>
  <td style='text-align: left;'>{$lang['global-expenses']}</td>
  <td>{$expr(number_format($expenses + $bankExpenses,2))} &euro;</td>
  <td></td>
  <td style='text-align: left;'><strong>{$lang['global-delta']}</strong></td>
  <td><strong>{$expr(number_format($tillDelta,2))} &euro;</strong></td>
  <td></td>
  <td></td>
  <td></td>
 </tr>
 <tr>
  <td style='text-align: left;'><strong>{$lang['profit']}</strong></td>
  <td><strong>{$expr(number_format($income - $expenses - $bankExpenses,2))} &euro;</strong></td>
  <td></td>
  <td colspan='6' style='text-align: left;'>{$lang['closeday-tillcomment']}:<br /><em>$tillComment</em> </td>
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
  <td style='text-align: left;'>{$lang['member-credit']}</td>
  <td>{$expr(number_format($totCredit,2))} &euro;</td>
  <td></td>
  <td></td>
  <td></td>
  <td></td>
  <td></td>
  <td></td>
 </tr>
EOD;

	} else {
		
		$mailtoadmin .= <<<EOD
 <tr>
  <td style='text-align: left; border-bottom: 1px dashed #ababab;''>+ {$lang['closeday-membershipfees-bank']}</td>
  <td style='border-bottom: 1px dashed #ababab;'>{$expr(number_format($membershipfeesBank,2))} &euro;</td>
  <td></td>
  <td style='text-align: left;'>-&nbsp; {$lang['closeday-moneybanked']}</td>
  <td style='text-align: right;'>{$expr(number_format($moneytaken + $bankedduringday,2))} &euro;</td>
  <td></td>
  <td style='text-align: left;'>+ {$lang['closeday-moneybanked']}</td>
  <td>{$expr(number_format($moneytaken + $bankedduringday,2))} &euro;</td>
 </tr>
 <tr>
  <td style='text-align: left; border-bottom: 1px solid #ababab;'>= {$lang['closeday-totalincome']}</td>
  <td style='border-bottom: 1px solid #ababab;'>{$expr(number_format($income,2))} &euro;</td>
  <td></td>
  <td style='text-align: left; border-bottom: 1px dashed #ababab;''>-&nbsp; {$lang['global-expenses']}</td>
  <td style='text-align: right; border-bottom: 1px dashed #ababab;'>{$expr(number_format($expenses,2))} &euro;</td>
  <td></td>
  <td style='text-align: left; border-bottom: 1px dashed #ababab;'>-&nbsp; {$lang['global-expenses']}</td>
  <td style='text-align: right; border-bottom: 1px dashed #ababab;'>{$expr(number_format($bankExpenses,2))} &euro;</td>
 </tr>
 <tr>
  <td style='text-align: left;'>{$lang['global-expenses']}</td>
  <td>{$expr(number_format($expenses + $bankExpenses,2))} &euro;</td>
  <td></td>
  <td style='text-align: left; border-bottom: 1px solid #ababab;'>= {$lang['closeday-estimatedtill']}</td>
  <td style='text-align: right; border-bottom: 1px solid #ababab;'>{$expr(number_format($estimatedTill,2))} &euro;</td>
  <td></td>
  <td style='text-align: left; border-bottom: 1px solid #ababab;'>= {$lang['closeday-bankbalance']}</td>
  <td style='text-align: right; border-bottom: 1px solid #ababab;'>{$expr(number_format($bankBalance,2))} &euro;</td>
 </tr>
 <tr>
  <td style='text-align: left;'><strong>{$lang['profit']}</strong></td>
  <td><strong>{$expr(number_format($income - $expenses - $bankExpenses,2))} &euro;</strong></td>
  <td></td>
  <td style='text-align: left;'>{$lang['closeday-yourcount']}</td>
  <td>{$expr(number_format($cashintill,2))} &euro;</td>
  <td></td>
  <td></td>
  <td></td>
 </tr>
 <tr>
  <td style='text-align: left;'>{$lang['closeday-totalclubbalance']}</td>
  <td>{$expr(number_format($closingbalance,2))} &euro;</td>
  <td></td>
  <td style='text-align: left;'><strong>{$lang['global-delta']}</strong></td>
  <td><strong>{$expr(number_format($tillDelta,2))} &euro;</strong></td>
  <td></td>
  <td></td>
  <td></td>
 </tr>
 <tr>
  <td style='text-align: left;'>{$lang['member-credit']}</td>
  <td>{$expr(number_format($totCredit,2))} &euro;</td>
  <td></td>
  <td colspan='6' style='text-align: left;'>{$lang['closeday-tillcomment']}:<br /><em>$tillComment</em> </td>
  <td></td>
  <td></td>
  <td></td>
  <td></td>
 </tr>
EOD;

	}

		$mailtoadmin .= <<<EOD
 <tr rowspan='2'>
  <td colspan='10'>&nbsp;</td>
 </tr>
</table>
<table style='color: #444; text-align: right; font-family: Tahoma, Verdana, sans-serif; font-size: 14px;'>
EOD;

				

		
		$defaultProducts = "SELECT category, '' AS categoryType, f.name, f.breed2, c.purchaseid, c.soldToday, c.weight, c.weightDelta, c.intStash, c.extStash, c.weightNoShake, c.totalWeight, c.totalNoShake, c.inMenu, c.specificComment, c.value FROM flower f, closingdetails c WHERE c.category = '1' AND c.productid = f.flowerid AND c.closingid = $closingid UNION ALL SELECT category, '' AS categoryType, e.name, '' AS breed2, c.purchaseid, c.soldToday, c.weight, c.weightDelta, c.intStash, c.extStash, c.weightNoShake, c.totalWeight, c.totalNoShake, c.inMenu, c.specificComment, c.value FROM extract e, closingdetails c WHERE c.category = '2' AND c.productid = e.extractid AND c.closingid = $closingid";
		
		$allProducts = $defaultProducts . $customProducts;
		 
		try
		{
			$productsResult = $pdo3->prepare("$allProducts");
			$productsResult->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		while ($product = $productsResult->fetch()) {
			
			$category = $product['category'];
			$categoryType = $product['categoryType'];
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
			$value = $product['value'];
			
			$priceQuery = "SELECT salesPrice AS gramPrice from purchases where purchaseid = $purchaseid";
		try
		{
			$result = $pdo3->prepare("$priceQuery");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$rowP = $result->fetch();
				$gramPrice = $rowP['gramPrice'];
				
			$prodPrice = $totalWeight * $gramPrice;
			$deltaPrice = $weightDelta * $gramPrice;

			
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
		try
		{
			$result = $pdo3->prepare("$selectGrowTypeNo");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
					$growTypeNo = $row['growType'];
					$closedAt = $row['closedAt'];
					
				if ($growTypeNo != '') {
					
					$growDetails = "SELECT growtype FROM growtypes WHERE growtypeid = $growTypeNo";
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
  <td>{$expr(number_format($prodPrice,2))} €</td>
  <td>{$expr(number_format($weightDelta,2))} g.</td>
  <td>{$expr(number_format($deltaPrice,2))} €</td>
  <td>{$expr(number_format($soldToday,2))} g.</td>
 </tr>
$commentInset
EOD;

			} else if ($category == 2 && $dividersetExtract != 'yes') {
				
				// Look up closed status
				$selectGrowTypeNo = "SELECT closedAt FROM purchases WHERE purchaseid = $purchaseid";
		try
		{
			$result = $pdo3->prepare("$selectGrowTypeNo");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
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
  <td><strong>{$lang['value']}&nbsp;&nbsp;</strong></td>
  <td><strong>{$lang['global-delta']}&nbsp;&nbsp;</strong></td>
  <td><strong>{$lang['value']}&nbsp;&nbsp;</strong></td>
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
  <td>{$expr(number_format($prodPrice,2))} €</td>
  <td>{$expr(number_format($weightDelta,2))} g.</td>
  <td>{$expr(number_format($deltaPrice,2))} €</td>
  <td>{$expr(number_format($soldToday,2))} g.</td>
 </tr>	
$commentInset
EOD;

			} else if ($category == '2') {
				
				// Look up closed status
				$selectGrowTypeNo = "SELECT closedAt FROM purchases WHERE purchaseid = $purchaseid";
		try
		{
			$result = $pdo3->prepare("$selectGrowTypeNo");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
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
  <td>{$expr(number_format($prodPrice,2))} €</td>
  <td>{$expr(number_format($weightDelta,2))} g.</td>
  <td>{$expr(number_format($deltaPrice,2))} €</td>
  <td>{$expr(number_format($soldToday,2))} g.</td>
 </tr>	
$commentInset
EOD;

			} else {
				
				// Look up closed status
				$selectGrowTypeNo = "SELECT closedAt FROM purchases WHERE purchaseid = $purchaseid";
		try
		{
			$result = $pdo3->prepare("$selectGrowTypeNo");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
					$closedAt = $row['closedAt'];

				if ($closedAt != NULL) {
					$productStatus = "Closed";
				} else if ($inMenu == 0) {
					$productStatus = "Not in menu";
				} else {
					$productStatus = "In menu";
				}

				if ($categoryType == 0) {
					
				// See if header has been set
				if (${'otherHeader' . $category} != 'set') {
				
					// Look up categories
					$selectCats = "SELECT name FROM categories WHERE id = $category";
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
	
		$catRow = $result->fetch();
						$categoryname = $catRow['name'];
	
					// insert divider
					$gramMail .= <<<EOD
 <tr>
  <td colspan='11'></td>
 </tr>
 <tr>
  <td colspan='11' style='color: #a80082; text-align: center; font-size: 14px; border-top: 1px solid #dedede; border-bottom: 1px solid #dedede;'><strong>$categoryname (g.)</strong></td>
 </tr>
 <tr>
  <td></td>
  <td><strong>{$lang['closeday-jars']}&nbsp;&nbsp;</strong></td>
  <td><strong></strong></td>
  <td><strong>{$lang['closeday-intstash']}&nbsp;&nbsp;</strong></td>
  <td><strong>{$lang['closeday-extstash']}&nbsp;&nbsp;</strong></td>
  <td><strong>{$lang['global-total']}&nbsp;&nbsp;</strong></td>
  <td><strong></strong></td>
  <td><strong>{$lang['value']}&nbsp;&nbsp;</strong></td>
  <td><strong>{$lang['global-delta']}&nbsp;&nbsp;</strong></td>
  <td><strong>{$lang['value']}&nbsp;&nbsp;</strong></td>
  <td><strong>{$lang['closeday-dispensed']}&nbsp;&nbsp;</strong></td>
 </tr>
 <tr>
  <td style='text-align: left;'>$name</td>
  <td>{$expr(number_format($weight,2))} g.</td>
  <td></td>
  <td>{$expr(number_format($intStash,2))} g.</td>
  <td>{$expr(number_format($extStash,2))} g.</td>
  <td><strong>{$expr(number_format($weight + $intStash + $extStash,2))} g.</strong></td>
  <td></td>
  <td>{$expr(number_format($prodPrice,2))} €</td>
  <td>{$expr(number_format($weightDelta,2))} g.</td>
  <td>{$expr(number_format($deltaPrice,2))} €</td>
  <td>{$expr(number_format($soldToday,2))} g.</td>
 </tr>	
$commentInset
EOD;

					${'otherHeader' . $category} = 'set';
						
				} else {
				
					$gramMail .= <<<EOD
 <tr>
  <td style='text-align: left;'>$name</td>
  <td>{$expr(number_format($weight,2))} g.</td>
  <td></td>
  <td>{$expr(number_format($intStash,2))} g.</td>
  <td>{$expr(number_format($extStash,2))} g.</td>
  <td><strong>{$expr(number_format($weight + $intStash + $extStash,2))} g.</strong></td>
  <td></td>
  <td>{$expr(number_format($prodPrice,2))} €</td>
  <td>{$expr(number_format($weightDelta,2))} g.</td>
  <td>{$expr(number_format($deltaPrice,2))} €</td>
  <td>{$expr(number_format($soldToday,2))} g.</td>
 </tr>	
$commentInset
EOD;

				}
				
				} else {
					
					
				// See if header has been set
				if (${'otherHeader' . $category} != 'set') {
				
					// Look up categories
					$selectCats = "SELECT name FROM categories WHERE id = $category";
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
	
		$catRow = $result->fetch();
						$categoryname = $catRow['name'];
	
					// insert divider
					$unitMail .= <<<EOD
 <tr>
  <td colspan='11'></td>
 </tr>
 <tr>
  <td colspan='11' style='color: #a80082; text-align: center; font-size: 14px; border-top: 1px solid #dedede; border-bottom: 1px solid #dedede;'><strong>$categoryname (u.)</strong></td>
 </tr>
 <tr>
  <td></td>
  <td><strong>{$lang['closeday-jars']}&nbsp;&nbsp;</strong></td>
  <td><strong></strong></td>
  <td><strong>{$lang['closeday-intstash']}&nbsp;&nbsp;</strong></td>
  <td><strong>{$lang['closeday-extstash']}&nbsp;&nbsp;</strong></td>
  <td><strong>{$lang['global-total']}&nbsp;&nbsp;</strong></td>
  <td><strong></strong></td>
  <td><strong>{$lang['value']}&nbsp;&nbsp;</strong></td>
  <td><strong>{$lang['global-delta']}&nbsp;&nbsp;</strong></td>
  <td><strong>{$lang['value']}&nbsp;&nbsp;</strong></td>
  <td><strong>{$lang['closeday-dispensed']}&nbsp;&nbsp;</strong></td>
 </tr>
 <tr>
  <td style='text-align: left;'>$name</td>
  <td>{$expr(number_format($weight,2))} u.</td>
  <td></td>
  <td>{$expr(number_format($intStash,2))} u.</td>
  <td>{$expr(number_format($extStash,2))} u.</td>
  <td><strong>{$expr(number_format($weight + $intStash + $extStash,2))} u.</strong></td>
  <td></td>
  <td>{$expr(number_format($prodPrice,2))} €</td>
  <td>{$expr(number_format($weightDelta,2))} u.</td>
  <td>{$expr(number_format($deltaPrice,2))} €</td>
  <td>{$expr(number_format($soldToday,2))} u.</td>
 </tr>	
$commentInset
EOD;

					${'otherHeader' . $category} = 'set';
						
				} else {
				
					$unitMail .= <<<EOD
 <tr>
  <td style='text-align: left;'>$name</td>
  <td>{$expr(number_format($weight,2))} u.</td>
  <td></td>
  <td>{$expr(number_format($intStash,2))} u.</td>
  <td>{$expr(number_format($extStash,2))} u.</td>
  <td><strong>{$expr(number_format($weight + $intStash + $extStash,2))} u.</strong></td>
  <td></td>
  <td>{$expr(number_format($prodPrice,2))} €</td>
  <td>{$expr(number_format($weightDelta,2))} u.</td>
  <td>{$expr(number_format($deltaPrice,2))} €</td>
  <td>{$expr(number_format($soldToday,2))} u.</td>
 </tr>	
$commentInset
EOD;

				}
				}


			}

	  		// Create totals per category
			if ($category == 1) {
				$flowerWeightPrice = $flowerWeightPrice + $prodPrice;
				$flowerDeltaPrice = $flowerDeltaPrice + $deltaPrice;
			} else if ($category == 2) {
				$extractWeightPrice = $extractWeightPrice + $prodPrice;
				$extractDeltaPrice = $extractDeltaPrice + $deltaPrice;
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
					
					$otherTotals[$catID]['otherWeightPrice'] = $otherTotals[$catID]['otherWeightPrice'] + $prodPrice;
					$otherTotals[$catID]['otherDeltaPrice'] = $otherTotals[$catID]['otherDeltaPrice'] + $deltaPrice;


			}
			
		} // Ends products loop
		


		
		$mailtoProductResponsible .= $gramMail;
		$mailtoProductResponsible .= $unitMail;
		
		$mailtoProductResponsible .= "</table>";
		  
			// Query to look up expenses
			$selectExpenses = "SELECT expenseid, registertime, userid, expensetype, expense, moneysource, other, amount, shop, comment, receipt, expensecategory FROM expenses WHERE registertime BETWEEN '$openingtime' AND '$closingtime' ORDER by registertime DESC";
		try
		{
			$result2 = $pdo3->prepare("$selectExpenses");
			$result2->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		
				
				
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
		
		
		while ($expense = $result2->fetch()) {
			
			
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
  <td><strong>{$lang['value']}&nbsp;&nbsp;</strong></td>
  <td><strong>{$lang['global-delta']}&nbsp;&nbsp;</strong></td>
  <td><strong>{$lang['value']}&nbsp;&nbsp;</strong></td>
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
  <td>{$expr(number_format($flowerWeightPrice,2))} €</td>
  <td>{$expr(number_format($stockDeltaFlower,2))} g.</td>
  <td>{$expr(number_format($flowerDeltaPrice,2))} €</td>
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
  <td>{$expr(number_format($extractWeightPrice,2))} €</td>
  <td>{$expr(number_format($stockDeltaExtract,2))} g.</td>
  <td>{$expr(number_format($extractDeltaPrice,2))} €</td>
  <td>{$expr(number_format($extractDispensed,2))} g.</td>
 </tr>
EOD;

		// Here insert other categories + summary, read from closingother / shiftcloseother
		$selectOtherCats = "SELECT category, categoryType, prodStock, intStash, extStash, quantitySold, quantitySoldReal, stockDelta, unitsSold FROM closingother WHERE closingid = $closingid";
		try
		{
			$resultOC = $pdo3->prepare("$selectOtherCats");
			$resultOC->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		while ($rowOC = $resultOC->fetch()) {
			
			$category = $rowOC['category'];
			$categoryType = $rowOC['categoryType'];
			$prodStock = $rowOC['prodStock'];
			$intStash = $rowOC['intStash'];
			$extStash = $rowOC['extStash'];
			$quantitySold = $rowOC['quantitySold'];
			$unitsSold = $rowOC['unitsSold'];
			$quantitySoldReal = $rowOC['quantitySoldReal'];
			$stockDelta = $rowOC['stockDelta'];
			
			$thisTotal = $prodStock + $intStash + $extStash;
			
			
			// Look up category name
			$findCatName = "SELECT name FROM categories WHERE id = $category";
		try
		{
			$result = $pdo3->prepare("$findCatName");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$rowCN = $result->fetch();
				$categoryName = $rowCN['name'];
				
			if ($categoryType == 0) {
				
				// Grams
				
				// Aggregates
				$cat0prodStock = $cat0prodStock + $prodStock;
				$cat0intStash = $cat0intStash + $intStash;
				$cat0extStash = $cat0extStash + $extStash;
				$cat0thisTotal = $cat0thisTotal + $thisTotal;
				$cat0stockDelta = $cat0stockDelta + $stockDelta;
				$cat0quantitySoldReal = $cat0quantitySoldReal + $quantitySoldReal;
				$cat0quantitySold = $cat0quantitySold + $quantitySold;
				$euroTotG = $euroTotG + $otherTotals[$category]['otherWeightPrice'];
				$deltaTotG = $deltaTotG + $otherTotals[$category]['otherDeltaPrice'];
				
				$gramProducts .= <<<EOD
 <tr>
  <td style='text-align: left;'>$categoryName</td>
  <td>{$expr(number_format($prodStock,2))} g.</td>
  <td>{$expr(number_format($prodStock,2))} g.</td>
  <td>{$expr(number_format($intStash,2))} g.</td>
  <td>{$expr(number_format($extStash,2))} g.</td>
  <td><strong>{$expr(number_format($thisTotal,2))} g.</strong></td>
  <td><strong>{$expr(number_format($prodStock,2))} g.</strong></td>
  <td>{$expr(number_format($otherTotals[$category]['otherWeightPrice'],2))} €</td>
  <td>{$expr(number_format($stockDelta,2))} g.</td>
  <td>{$expr(number_format($otherTotals[$category]['otherDeltaPrice'],2))} €</td>
EOD;
				if ($_SESSION['realWeight'] == 1) {
					
				$gramProducts .= <<<EOD
  <td>{$expr(number_format($quantitySoldReal,2))} g.</td>
 </tr>
EOD;

				} else {
					
				$gramProducts .= <<<EOD
  <td>{$expr(number_format($quantitySold,2))} g.</td>
 </tr>
EOD;

				}

				
			} else {
				
				// Units
				
				// Aggregates
				$cat1prodStock = $cat1prodStock + $prodStock;
				$cat1intStash = $cat1intStash + $intStash;
				$cat1extStash = $cat1extStash + $extStash;
				$cat1thisTotal = $cat1thisTotal + $thisTotal;
				$cat1stockDelta = $cat1stockDelta + $stockDelta;
				$cat1unitsSold = $cat1unitsSold + $unitsSold;
				$euroTotU = $euroTotU + $otherTotals[$category]['otherWeightPrice'];
				$deltaTotU = $deltaTotU + $otherTotals[$category]['otherDeltaPrice'];
			
				$unitProducts .= <<<EOD
 <tr>
  <td style='text-align: left;'>$categoryName</td>
  <td>{$expr(number_format($prodStock,2))} u.</td>
  <td></td>
  <td>{$expr(number_format($intStash,2))} u.</td>
  <td>{$expr(number_format($extStash,2))} u.</td>
  <td><strong>{$expr(number_format($thisTotal,2))} u.</strong></td>
  <td></td>
  <td>{$expr(number_format($otherTotals[$category]['otherWeightPrice'],2))} €</td>
  <td>{$expr(number_format($stockDelta,2))} u.</td>
  <td>{$expr(number_format($otherTotals[$category]['otherDeltaPrice'],2))} €</td>
  <td>{$expr(number_format($unitsSold,2))} u.</td>
 </tr>
EOD;

			}
			
		}
			
		$MPR .= $gramProducts;
		
		$MPR .= <<<EOD
 <tr style='border-top: 1px solid #888; border-bottom: 2px solid #888;'>
  <td style='text-align: left;'><strong>TOTAL G</strong></td>
  <td><strong>{$expr(number_format($prodStockFlower + $prodStockExtract + $cat0prodStock,2))} g.</strong></td>
  <td><strong>{$expr(number_format($flowerweightNoShake + $prodStockExtract + $cat0prodStock,2))} g.</strong></td>
  <td><strong>{$expr(number_format($flowerintStash + $extractintStash + $cat0intStash,2))} g.</strong></td>
  <td><strong>{$expr(number_format($flowerextStash + $extractextStash + $cat0extStash,2))} g.</strong></td>
  <td><strong>{$expr(number_format($flowertotalWeight + $extracttotalWeight + $cat0thisTotal,2))} g.</strong></td>
  <td><strong>{$expr(number_format($flowertotalNoShake + $extracttotalWeight + $cat0thisTotal,2))} g.</strong></td>
  <td><strong>{$expr(number_format($euroTotG + $extractWeightPrice + $flowerWeightPrice,2))} &euro;</strong></td>
  <td><strong>{$expr(number_format($stockDeltaFlower + $stockDeltaExtract + $cat0stockDelta,2))} g.</strong></td>
  <td><strong>{$expr(number_format($deltaTotG + $extractDeltaPrice + $flowerDeltaPrice,2))} &euro;</strong></td>
EOD;
				if ($_SESSION['realWeight'] == 1) {
					
		$MPR .= <<<EOD
  <td>{$expr(number_format($flowerDispensed + $extractDispensed + $cat0quantitySoldReal,2))} g.</td>
 </tr>
EOD;

				} else {
					
		$MPR .= <<<EOD
  <td>{$expr(number_format($flowerDispensed + $extractDispensed + $cat0quantitySold,2))} g.</td>
 </tr>
EOD;

				}
		$MPR .= <<<EOD
 <tr>
  <td colspan='11'>&nbsp;</td>
 </tr>
EOD;

		$MPR .= $unitProducts;
		$MPR .= <<<EOD
 <tr style='border-top: 1px solid #888; border-bottom: 2px solid #888;'>
  <td style='text-align: left;'><strong>TOTAL U</strong></td>
  <td><strong>{$expr(number_format($cat1prodStock,2))} u.</strong></td>
  <td></td>
  <td><strong>{$expr(number_format($cat1intStash,2))} u.</strong></td>
  <td><strong>{$expr(number_format($cat1extStash,2))} u.</strong></td>
  <td><strong>{$expr(number_format($cat1thisTotal,2))} u.</strong></td>
  <td></td>
  <td><strong>{$expr(number_format($euroTotU,2))} &euro;</strong></td>
  <td><strong>{$expr(number_format($cat1stockDelta,2))} u.</strong></td>
  <td><strong>{$expr(number_format($deltaTotU,2))} &euro;</strong></td>
  <td><strong>{$expr(number_format($cat1unitsSold,2))} u.</strong></td>
 <tr>
  <td colspan='11'>&nbsp;</td>
 </tr>
EOD;
		
		
		echo $MPR;
		echo <<<EOD
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
  <td><strong>{$lang['value']}&nbsp;&nbsp;</strong></td>
  <td><strong>{$lang['global-delta']}&nbsp;&nbsp;</strong></td>
  <td><strong>{$lang['value']}&nbsp;&nbsp;</strong></td>
  <td><strong>{$lang['closeday-dispensed']}&nbsp;&nbsp;</strong></td>
 </tr>
EOD;
		echo $mailtoProductResponsibleFull;
			
	  echo $expenseDetails;
	  echo "</table>";

			
			
			
			
			
			
			
			
			
			
			
			
			
			
			
			

			
			
			
			
			
			
			
			
			
			
			
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
					
		} else {
			
			$_SESSION['errorMessage'] = $lang['no-closing-found'];
			
			// Closing does not exist, generate data
			
			$reportDate = $_POST['reportDate'];
			$reportDateReadable = date("d-m-Y", strtotime($_POST['reportDate']));
			$openingSQL = $_POST['reportDate'] . " 06:00:00";
			$closingSQL = date("Y-m-d", strtotime($_POST['reportDate'] . " +1 days")) . " 06:00:00";
			
			// Total members today
			$selectMembers = "SELECT COUNT(memberno) from users WHERE memberno <> '0' AND userGroup < 6 AND registeredSince <= '$closingSQL'";
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
		
			// New members today
			$newMembers = "SELECT COUNT(user_id) FROM users where (registeredSince BETWEEN '$openingSQL' AND '$closingSQL')";
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
			$bannedmembers = "SELECT COUNT(user_id) FROM users where (banTime BETWEEN '$openingSQL' AND '$closingSQL')";
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
			$deletedmembers = "SELECT COUNT(user_id) FROM users where (deleteTime BETWEEN '$openingSQL' AND '$closingSQL')";
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
				
			// Look up todays dispenses
			$selectSales = "SELECT SUM(amount), SUM(quantity), SUM(realQuantity), SUM(units) from sales WHERE (saletime BETWEEN '$openingSQL' AND '$closingSQL')";
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
				$quantitySoldReal = $row['SUM(realQuantity)'];
				$unitsSold = $row['SUM(units)'];
				
			// Look up todays dispenses by category 1
			$selectSalesFlower = "SELECT SUM(d.amount), SUM(d.quantity), SUM(d.realQuantity) from sales s, salesdetails d WHERE s.saleid = d.saleid AND (s.saletime BETWEEN '$openingSQL' AND '$closingSQL') AND d.category = 1";
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
				$quantitySoldFlowerReal = $row['SUM(d.realQuantity)'];
				
			$flowerSalesPercentageToday = ($salesTodayFlower / $salesToday) * 100;
			$flowerGramsPercentageToday = ($quantitySoldFlower / $quantitySold) * 100;
			
			// Look up todays dispenses by category 2
			$selectSalesExtract = "SELECT SUM(d.amount), SUM(d.quantity), SUM(d.realQuantity) from sales s, salesdetails d WHERE s.saleid = d.saleid AND (s.saletime BETWEEN '$openingSQL' AND '$closingSQL') AND d.category = 2";
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
				$quantitySoldExtractReal = $row['SUM(d.realQuantity)'];
				
			$extractSalesPercentageToday = ($salesTodayExtract / $salesToday) * 100;
			$extractGramsPercentageToday = ($quantitySoldExtract / $quantitySold) * 100;
			
			// Look up todays dispenses by non-default categories
			// Another method; Look up which categories have a TYPE 1. Then 'ping' those categories. Much better and faster!
			// Query to look for category
			$categoryDetailsC = "SELECT id, name, type FROM categories WHERE type = 1";
		try
		{
			$resultC = $pdo3->prepare("$categoryDetailsC");
			$resultC->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
				
			$grCatList = '';
			
		while ($rowC = $resultC->fetch()) {
						
				$catId = $rowC['id'];
				
				$grCatList = $grCatList . $catId . ",";
				
			}
			
			$grCatListfinal = substr($grCatList, 0, -1);
							
		
			// Look up today's bar sales
			$selectBarSales = "SELECT SUM(amount), SUM(unitsTot) from b_sales WHERE (saletime BETWEEN '$openingSQL' AND '$closingSQL')";
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
			$selectDonations = "SELECT SUM(amount) from donations WHERE (donatedTo < 2 OR donatedTo = 4) AND (donationTime BETWEEN '$openingSQL' AND '$closingSQL')";
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
				
			// Look up todays bank donations
			$selectDonations = "SELECT SUM(amount) from donations WHERE donatedTo = 2 AND (donationTime BETWEEN '$openingSQL' AND '$closingSQL')";
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
				
			// Look up todays donations
			$selectDonations = "SELECT COUNT(donationid) from donations WHERE (donatedTo < 2 OR donatedTo = 4) AND (donationTime BETWEEN '$openingSQL' AND '$closingSQL')";
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
				$donationsNo = $row['COUNT(donationid)'];
				
			// Look up todays bank donations
			$selectDonations = "SELECT COUNT(donationid) from donations WHERE donatedTo = 2 AND (donationTime BETWEEN '$openingSQL' AND '$closingSQL')";
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
				$bankDonationsNo = $row['COUNT(donationid)'];
					
				
			// Look up today's membership fees
			$selectMembershipFees = "SELECT SUM(amountPaid) FROM memberpayments WHERE (paidTo < 2 OR paidTo = 4) AND (paymentdate BETWEEN '$openingSQL' AND '$closingSQL')";
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
			$selectMembershipFees = "SELECT SUM(amountPaid) FROM memberpayments WHERE paidTo = 2 AND (paymentdate BETWEEN '$openingSQL' AND '$closingSQL')";
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
				
			// Look up today's till expenses
			$selectExpenses = "SELECT SUM(amount) FROM expenses WHERE (registertime BETWEEN '$openingSQL' AND '$closingSQL') AND moneysource = 1";
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
				
			// Look up today's bank expenses
			$selectExpenses = "SELECT SUM(amount) FROM expenses WHERE (registertime BETWEEN '$openingSQL' AND '$closingSQL') AND moneysource = 2";
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
				
			if ($_SESSION['creditOrDirect'] == 0) {
				
				// Look up dispensed today cash
				$selectSales = "SELECT SUM(amount) from sales WHERE (saletime BETWEEN '$openingSQL' AND '$closingSQL') AND direct < 2";
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
				$selectSales = "SELECT SUM(amount) from sales WHERE (saletime BETWEEN '$openingSQL' AND '$closingSQL') AND direct = 2";
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
				$selectSales = "SELECT SUM(amount) from b_sales WHERE (saletime BETWEEN '$openingSQL' AND '$closingSQL') AND direct < 2";
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
				$selectSales = "SELECT SUM(amount) from b_sales WHERE (saletime BETWEEN '$openingSQL' AND '$closingSQL') AND direct = 2";
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
				
				// Calculate total income
				$totalIncome = $donations + $bankDonations + $membershipFees + $membershipfeesBank + $salesTodayCash + $salesTodayBank + $salesTodayBarCash + $salesTodayBarBank;

			} else {
				
				// Calculate total income
				$totalIncome = $donations + $bankDonations + $membershipFees + $membershipfeesBank;
				
			}
				
							
			pageStart($lang['daily-reports'] . ": $reportDateReadable", NULL, NULL, "preporting", "daily", $lang['daily-reports'] . ": $reportDateReadable", $_SESSION['successMessage'], $_SESSION['errorMessage']);
				
					if ($_SESSION['domain'] == 'choko') {
						echo "<table style='color: #fff; text-align: right; font-family: Tahoma, Verdana, sans-serif; font-size: 14px;'>";
					} else {
						echo "<table style='color: #444; text-align: right; font-family: Tahoma, Verdana, sans-serif; font-size: 14px;'>";
					}

			echo <<<EOD
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
	$selectCats = "SELECT id, name, type from categories WHERE id > 2 ORDER by name ASC";
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
	
			
					$i = 0;
					
		while ($category = $resultCats->fetch()) {
					
					$categoryid = $category['id'];
					$name = $category['name'];
					$type = $category['type'];
						
		// Create more product queries for each category - to be used further down!
		$customProducts .= " UNION ALL SELECT '$categoryid' AS category, pr.productid AS productid, pr.name AS name, p.purchaseid AS purchaseid, '' AS growtype, p.inMenu AS inMenu, p.closedAt AS closedAt, salesPrice AS gramPrice, p.realQuantity FROM products pr, purchases p WHERE p.category = $categoryid AND p.productid = pr.productid AND (p.closedAt IS NULL OR (p.closingDate >= '$closingSQL')) AND (p.purchaseDate <= '$closingSQL')";
		
		
		// Look up sales in this cat
		$selectSalesOthers = "SELECT SUM(d.amount), SUM(d.quantity), SUM(d.realQuantity) from sales s, salesdetails d WHERE s.saleid = d.saleid AND (s.saletime BETWEEN '$openingSQL' AND '$closingSQL') AND d.category = $categoryid";
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
			$othersGramsPercentageToday = ($quantitySoldOthersReal / $unitsSold) * 100;
			
		} else {
				
			$othersSalesPercentageToday = ($salesTodayOthers / $salesToday) * 100;
			$othersGramsPercentageToday = ($quantitySoldOthersReal / $quantitySoldReal) * 100;
			
		}

		if ($type == 0) {
		$unitCatSummary .=  <<<EOD
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
		} else {
			
		$gramCatSummary .=  <<<EOD
 <tr>
  <td style='text-align: left;'><em>{$lang['closeday-ow']} $name</em></td>
  <td>{$expr(number_format($salesTodayOthers,2))} &euro;</td>
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
  <td style='vertical-align: bottom; text-align: center; font-size: 14px;' colspan="2"><strong>{$lang['summary']}</strong></td>
  <td></td>
  <td></td>
  <td></td>
  <td></td>
 </tr>
 <tr>
  <td style='text-align: left;'>{$lang['closeday-donations-till']}</td>
  <td>{$expr(number_format($donations,2))} &euro;</td>
  <td></td>
  <td></td>
  <td></td>
  <td></td>
  <td></td>
  <td></td>
 </tr>
 <tr>
  <td style='text-align: left;'>+ {$lang['closeday-donations-bank']}</td>
  <td>{$expr(number_format($bankDonations,2))} &euro;</td>
  <td></td>
  <td></td>
  <td></td>
  <td></td>
  <td></td>
  <td></td>
 </tr>
 <tr>
  <td style='text-align: left;'>+ {$lang['closeday-membershipfees-till']}</td>
  <td>{$expr(number_format($membershipFees,2))} &euro;</td>
  <td></td>
  <td></td>
  <td></td>
  <td></td>
  <td></td>
  <td></td>
 </tr>
EOD;
	if ($_SESSION['creditOrDirect'] == 0) {
		
		echo <<<EOD
 <tr>
  <td style='text-align: left;'>+ {$lang['closeday-membershipfees-bank']}</td>
  <td>{$expr(number_format($membershipfeesBank,2))} &euro;</td>
  <td></td>
  <td></td>
  <td></td>
  <td></td>
  <td></td>
  <td></td>
 </tr>
 <tr>
  <td style='text-align: left;'>+ {$lang['dispensed-direct-till']}</td>
  <td>{$expr(number_format($salesTodayCash,2))} &euro;</td>
  <td></td>
  <td></td>
  <td></td>
  <td></td>
  <td></td>
  <td></td>
 </tr>
 <tr>
  <td style='text-align: left;'>+ {$lang['dispensed-direct-bank']}</td>
  <td>{$expr(number_format($salesTodayBank,2))} &euro;</td>
  <td></td>
  <td></td>
  <td></td>
  <td></td>
  <td></td>
  <td></td>
 </tr>
 <tr>
  <td style='text-align: left;'>+ {$lang['direct-bar-sales-till']}</td>
  <td>{$expr(number_format($salesTodayBarCash,2))} &euro;</td>
  <td></td>
  <td></td>
  <td></td>
  <td></td>
  <td></td>
  <td></td>
 </tr>
 <tr>
  <td style='text-align: left; border-bottom: 1px dashed #ababab;'>+ {$lang['direct-bar-sales-bank']}</td>
  <td style='text-align: right; border-bottom: 1px dashed #ababab;'>{$expr(number_format($salesTodayBarBank,2))} &euro;</td>
  <td></td>
  <td></td>
  <td></td>
  <td></td>
  <td></td>
  <td></td>
 </tr>
 <tr>
  <td style='text-align: left; border-bottom: 1px solid #ababab;'>= {$lang['closeday-totalincome']}</td>
  <td style='border-bottom: 1px solid #ababab;'>{$expr(number_format($totalIncome,2))} &euro;</td>
  <td></td>
  <td></td>
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
  <td style='text-align: left;'><strong>{$lang['profit']}</strong></td>
  <td><strong>{$expr(number_format($totalIncome - $tillExpenses - $bankExpenses,2))} &euro;</strong></td>
  <td></td>
  <td></td>
  <td></td>
  <td></td>
  <td></td>
  <td></td>
 </tr>
EOD;

	} else {
		
		echo <<<EOD
 <tr>
  <td style='text-align: left; border-bottom: 1px dashed #ababab;'>+ {$lang['closeday-membershipfees-bank']}</td>
  <td style='text-align: right; border-bottom: 1px dashed #ababab;'>{$expr(number_format($membershipfeesBank,2))} &euro;</td>
  <td></td>
  <td></td>
  <td></td>
  <td></td>
  <td></td>
  <td></td>
 </tr>
 <tr>
  <td style='text-align: left; border-bottom: 1px solid #ababab;'>= {$lang['closeday-totalincome']}</td>
  <td style='border-bottom: 1px solid #ababab;'>{$expr(number_format($totalIncome,2))} &euro;</td>
  <td></td>
  <td></td>
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
  <td style='text-align: left;'><strong>{$lang['profit']}</strong></td>
  <td><strong>{$expr(number_format($totalIncome - $tillExpenses - $bankExpenses,2))} &euro;</strong></td>
  <td></td>
  <td></td>
  <td></td>
  <td></td>
  <td></td>
  <td></td>
 </tr>
EOD;

	}

		echo <<<EOD
 <tr rowspan='2'>
  <td colspan='7'>&nbsp;</td>
 </tr>
</table>
EOD;
	
					if ($_SESSION['domain'] == 'choko') {
						echo "<table style='color: #fff; text-align: right; font-family: Tahoma, Verdana, sans-serif; font-size: 14px;'>";
					} else {
						echo "<table style='color: #444; text-align: right; font-family: Tahoma, Verdana, sans-serif; font-size: 14px;'>";
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
		
			$selectProducts = "SELECT '1' AS category, g.flowerid AS productid, g.name AS name, p.purchaseid AS purchaseid, p.growType AS growtype, p.inMenu AS inMenu, p.closedAt AS closedAt, salesPrice AS gramPrice, p.realQuantity FROM flower g, purchases p WHERE p.category = 1 AND p.productid = g.flowerid AND (p.closedAt IS NULL OR (p.closingDate >= '$closingSQL')) AND (p.purchaseDate <= '$closingSQL') UNION ALL SELECT '2' AS category, h.extractid AS productid, h.name AS name, p.purchaseid AS purchaseid, '' AS growtype, p.inMenu AS inMenu, p.closedAt AS closedAt, salesPrice AS gramPrice, p.realQuantity FROM extract h, purchases p WHERE p.category = 2 AND p.productid = h.extractid AND (p.closedAt IS NULL OR (p.closingDate >= '$closingSQL')) AND (p.purchaseDate <= '$closingSQL')";
			
			$selectProducts .= $customProducts;
								
		try
		{
			$resultProducts = $pdo3->prepare("$selectProducts");
			$resultProducts->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
				
		
				
			$productDetails = <<<EOD
		 <tr>
		  <td colspan='7'></td>
		 </tr>
		 <tr>
		  <td colspan='7'></td>
		 </tr>
		 <tr>
		  <td colspan='7' style='color: #5aa242; text-align: left; font-size: 17px; border-bottom: 2px solid #a80082;'><strong>{$lang['closeday-productdetails']}</strong></td>
		 </tr>
		 <tr>
		  <td colspan='7' style='color: #a80082; text-align: center; font-size: 14px; border-bottom: 1px solid #dedede;'><strong>{$lang['global-flowerscaps']}</strong></td>
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
		
				
		while ($product = $resultProducts->fetch()) {
					
					
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
						
					// Look up todays dispenses
					$selectSales = "SELECT SUM(d.realQuantity) FROM salesdetails d, sales s WHERE (s.saletime BETWEEN '$openingSQL' AND '$closingSQL') AND d.saleid = s.saleid AND d.purchaseid = $purchaseid";
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
						$soldToday = $row['SUM(d.realQuantity)'];
		
					// Look up total dispenses
					$selectSalesTot = "SELECT SUM(d.realQuantity) FROM salesdetails d, sales s WHERE (s.saletime <= '$closingSQL') AND d.saleid = s.saleid AND d.purchaseid = $purchaseid";
		try
		{
			$result = $pdo3->prepare("$selectSalesTot");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
						$soldTotal = $rowTot['SUM(d.realQuantity)'];
						
						
					// Look up additions and removals
					$selectAdditions = "SELECT SUM(quantity) FROM productmovements WHERE purchaseid = $purchaseid AND type = 1 AND (movementtime <= '$closingSQL') AND movementTypeid < 23";
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
						
					$selectRemovals = "SELECT SUM(quantity) FROM productmovements WHERE purchaseid = $purchaseid AND type = 2 AND (movementtime <= '$closingSQL') AND movementTypeid < 23";
					
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
					$jarWeight = $openingWeight + $addedToday - $takeoutsToday - $soldTotal;
					
		
					// Calculate what's in Internal stash
					$selectStashedInt = "SELECT SUM(quantity) FROM productmovements WHERE purchaseid = $purchaseid AND type = 2 AND (movementtime <= '$closingSQL') AND (movementTypeid = 5 OR movementTypeid = 18)";
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
						
					$selectUnStashedInt = "SELECT SUM(quantity) FROM productmovements WHERE purchaseid = $purchaseid AND type = 1 AND (movementtime <= '$closingSQL') AND (movementTypeid = 12 OR movementTypeid = 17)";
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
					$selectStashedExt = "SELECT SUM(quantity) FROM productmovements WHERE purchaseid = $purchaseid AND type = 2 AND (movementtime <= '$closingSQL') AND (movementTypeid = 6 OR movementTypeid = 20)";
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
						
					$selectUnStashedExt = "SELECT SUM(quantity) FROM productmovements WHERE purchaseid = $purchaseid AND type = 1 AND (movementtime <= '$closingSQL') AND (movementTypeid = 2 OR movementTypeid = 19)";
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
		  <td colspan='7'></td>
		 </tr>
		 <tr>
		  <td colspan='7' style='color: #a80082; text-align: center; font-size: 14px; border-bottom: 1px solid #dedede; border-top: 1px solid #dedede; margin-top: 10px;'><strong>{$lang['global-extractscaps']}</strong></td>
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
  <td colspan='7'></td>
 </tr>
 <tr>
  <td colspan='7' style='color: #a80082; text-align: center; font-size: 14px; border-bottom: 1px solid #dedede; border-top: 1px solid #dedede; margin-top: 10px;'><strong>$catName</strong></td>
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
					$otherTotals[$catID]['categoryType'] = $type;
					$otherTotals[$catID]['otherTotJar'] = $otherTotals[$catID]['otherTotJar'] + $jarWeight;
					$otherTotals[$catID]['otherTotIntSt'] = $otherTotals[$catID]['otherTotIntSt'] + $inStashInt;
					$otherTotals[$catID]['otherTotExtSt'] = $otherTotals[$catID]['otherTotExtSt'] + $inStashExt;
					$otherTotals[$catID]['otherTot'] = $otherTotals[$catID]['otherTotJar'] + $otherTotals[$catID]['otherTotIntSt'] + $otherTotals[$catID]['otherTotExtSt'];
					$otherTotals[$catID]['otherSoldToday'] = $otherTotals[$catID]['otherSoldToday'] + $soldToday;
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
  <td>{$expr(number_format($weightPrice,2))} &euro;</td>
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
  <td>{$expr(number_format($weightPrice,2))} &euro;</td>
  <td>{$expr(number_format($soldToday,2))} u.</td>
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
  <td>{$expr(number_format($oTot['otherWeightPrice'],2))} &euro;</td>
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
  <td>{$expr(number_format($oTot['otherWeightPrice'],2))} &euro;</td>
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
		  <td colspan='7' style='color: #5aa242; text-align: left; font-size: 17px; border-bottom: 2px solid #a80082;'><strong>{$lang['closeday-productoverview']}</strong></td>
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
EOD;

		$productOverview .= $productOvvG;
		
		$productOverview .= <<<EOD
 <tr style='border-top: 1px solid #888; border-bottom: 2px solid #888;'>
  <td style='text-align: left;'><strong>TOTAL G</strong></td>
  <td><strong>{$expr(number_format($fullTotJar,2))} g.</strong></td>
  <td><strong>{$expr(number_format($fullTotIntSt,2))} g.</strong></td>
  <td><strong>{$expr(number_format($fullTotExtSt,2))} g.</strong></td>
  <td><strong>{$expr(number_format($fullTot,2))} g.</strong></td>
  <td><strong>{$expr(number_format($fullWeightPrice,2))} &euro;</strong></td>
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
  <td><strong>{$expr(number_format($unitsotherWeightPrice,2))} &euro;</strong></td>
  <td><strong>{$expr(number_format($unitsotherSoldToday,2))} u.</strong></td>
 </tr>
 <tr>
  <td colspan='7'>&nbsp;</td>
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
					
			// Check if closingid exists + get timestamps
			if (isset($_GET['closingid'])) {
				
				$closingid = $_GET['closingid'];
				$closingReadable = $_GET['ct'];
				$closingSQL = $_GET['cts'];
				$openingReadable = $_GET['ot'];
				$openingSQL = $_GET['ots'];			
				
					
				// Look up todays donations
				$selectDonations = "SELECT COUNT(donationid) from donations WHERE (donatedTo < 2 OR donatedTo = 4) AND (donationTime BETWEEN '$openingSQL' AND '$closingSQL')";
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
					$donationsNo = $row['COUNT(donationid)'];
					
				// Look up todays bank donations
				$selectDonations = "SELECT COUNT(donationid) from donations WHERE donatedTo = 2 AND (donationTime BETWEEN '$openingSQL' AND '$closingSQL')";
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
					$bankDonationsNo = $row['COUNT(donationid)'];
					
					
				// Select closing details
				$selectClosingData = "SELECT openingtime, closingtime, quantitySold, soldtoday, unitsSold, closingbalance, moneytaken, takenduringday, cashintill, bankBalance, newmembers, closedby, tillComment, expenses, membershipFees, estimatedTill, tillDelta, bankExpenses, prodOpening, prodStock, stockDelta, prodStockFlower, prodStockExtract, income, stockDeltaFlower, stockDeltaExtract, donations, bankDonations, renewedMembers, bannedMembers, deletedMembers, expiredMembers, totalMembers, activeMembers, intStash, extStash, totalWeight, totalNoShake, flowerintStash, flowerextStash, flowerweightNoShake, flowertotalWeight, flowertotalNoShake, extractintStash, extractextStash, extracttotalWeight, flowerDispensed, extractDispensed, soldTodayFlower, soldTodayExtract, membershipfeesBank, soldtodayBar, unitsSoldBar, openingBalance, openingBalanceBank, totCredit FROM closing WHERE closingid = $closingid";
			
		try
		{
			$result = $pdo3->prepare("$selectClosingData");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
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
				
				pageStart($lang['daily-reports'], NULL, NULL, "preporting", "daily", $lang['daily-reports'], $_SESSION['successMessage'], $_SESSION['errorMessage']);
			
				echo "<h1>$openingReadable {$lang['to']} $closingReadable</h1>";
				
	// Look up &euro;, g, units
	$selectSales = "SELECT SUM(quantitySold), SUM(soldToday), SUM(unitsSold) FROM closing WHERE closingid = $closingid";
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
		$salesToday = $row['SUM(soldToday)'];
		$quantitySold = $row['SUM(quantitySold)'];
		$unitsSold = $row['SUM(unitsSold)'];
	
	// Look up todays dispenses by category 1
	$selectSalesFlower = "SELECT SUM(d.amount), SUM(d.quantity) from sales s, salesdetails d WHERE s.saleid = d.saleid AND s.saletime BETWEEN '$openingtime' AND '$closingtime' AND d.category = 1";
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
		$flowerSalesToday = $row['SUM(d.amount)'];
		$flowerGramsToday = $row['SUM(d.quantity)'];
		
	$flowerSalesPercentageToday = ($flowerSalesToday / $salesToday) * 100;
	$flowerGramsPercentageToday = ($flowerGramsToday / $quantitySold) * 100;
	
	// Look up todays dispenses by category 2
	$selectSalesExtract = "SELECT SUM(d.amount), SUM(d.quantity) from sales s, salesdetails d WHERE s.saleid = d.saleid AND s.saletime BETWEEN '$openingtime' AND '$closingtime' AND d.category = 2";
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
		$extractSalesToday = $row['SUM(d.amount)'];
		$extractGramsToday = $row['SUM(d.quantity)'];
		
	$extractSalesPercentageToday = ($extractSalesToday / $salesToday) * 100;
	$extractGramsPercentageToday = ($extractGramsToday / $quantitySold) * 100;
			
				if ($_SESSION['creditOrDirect'] == 0) {
					
					$selectSales = "SELECT SUM(amount) from sales WHERE (saletime BETWEEN '$openingSQL' AND '$closingSQL') AND direct < 2";
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
					$selectSales = "SELECT SUM(amount) from sales WHERE (saletime BETWEEN '$openingSQL' AND '$closingSQL') AND direct = 2";
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
					$selectSales = "SELECT SUM(amount) from b_sales WHERE (saletime BETWEEN '$openingSQL' AND '$closingSQL') AND direct < 2";
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
					$selectSales = "SELECT SUM(amount) from b_sales WHERE (saletime BETWEEN '$openingSQL' AND '$closingSQL') AND direct = 2";
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
				
					// Look up bar sales today bank
					$selectSales = "SELECT SUM(amount) from b_sales WHERE (saletime BETWEEN '$openingSQL' AND '$closingSQL') AND direct = 2";
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
	$selectCats = "SELECT id, name, type from categories WHERE id > 2 ORDER by name ASC";
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
	

		$i = 0;
		
		while ($category = $resultCats->fetch()) {
		
		$categoryid = $category['id'];
		$name = $category['name'];
		$type = $category['type'];
		
		// Create more product queries for each category - to be used in a bigger query further down!
		$customProducts .= " UNION ALL SELECT '$categoryid' AS category, categoryType, pr.name, '' AS breed2, c.purchaseid, c.soldToday, c.weight, c.weightDelta, c.intStash, c.extStash, c.weightNoShake, c.totalWeight, c.totalNoShake, c.inMenu, c.specificComment, c.value FROM products pr, closingdetails c WHERE c.category = $categoryid AND c.productid = pr.productid AND c.closingid = $closingid";
				
		
		// Look up sales in this cat
		$selectSalesOthers = "SELECT SUM(d.amount), SUM(d.quantity) from sales s, salesdetails d WHERE s.saleid = d.saleid AND s.saletime BETWEEN '$openingtime' AND '$closingtime' AND d.category = $categoryid";
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
  <td>{$expr(number_format($salesTodayOthers,2))} &euro;</td>
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
  <td>{$expr(number_format($salesTodayOthers,2))} &euro;</td>
  <td>{$expr(number_format($othersSalesPercentageToday,0))}%</td>
  <td>{$expr(number_format($quantitySoldOthers,2))} g.</td>
EOD;

		if ($_SESSION['realWeight'] == 1) {
		$gramCatSummary .=  <<<EOD
  <td>({$expr(number_format($quantitySoldOthers,2))} g.)</td>			
EOD;

		}
		
		$gramCatSummary .=  <<<EOD
  <td>{$expr(number_format($othersGramsPercentageToday,0))}%</td>
  <td></td>
  <td></td>
 </tr>
EOD;
		}		
		$i++;
		
	}

		$mailtoadmin .= $gramCatSummary;
		$mailtoadmin .= $unitCatSummary;
		
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
  <td style='vertical-align: bottom; text-align: center; font-size: 14px;' colspan="2"><strong>{$lang['summary']}</strong></td>
  <td></td>
  <td style='vertical-align: bottom; text-align: center; font-size: 14px;' colspan="2"><strong>{$lang['till-calculation']}</strong></td>
  <td></td>
  <td style='vertical-align: bottom; text-align: center; font-size: 14px;' colspan="2"><strong>{$lang['bank-calculation']}</strong></td>
 </tr>
 <tr>
  <td style='text-align: left;'>{$lang['closeday-donations-till']}</td>
  <td>{$expr(number_format($donations,2))} &euro;</td>
  <td style='text-align: left;'>($donationsNo)</td>
  <td style='text-align: left;'>{$lang['closeday-tillatopening']}</td>
  <td>{$expr(number_format($openingBalance,2))} &euro;</td>
  <td></td>
  <td style='text-align: left;'>{$lang['bank-opening']}:</td>
  <td>{$expr(number_format($openingBalanceBank,2))} &euro;</td>
 </tr>
 <tr>
  <td style='text-align: left;'>+ {$lang['closeday-donations-bank']}</td>
  <td>{$expr(number_format($bankDonations,2))} &euro;</td>
  <td style='text-align: left;'>($bankDonationsNo)</td>
  <td style='text-align: left;'>+ {$lang['memberfees']}</td>
  <td>{$expr(number_format($membershipFees,2))} &euro;</td>
  <td></td>
  <td style='text-align: left;'>+ {$lang['memberfees']}</td>
  <td>{$expr(number_format($membershipfeesBank,2))} &euro;</td>
 </tr>
 <tr>
  <td style='text-align: left;'>+ {$lang['closeday-membershipfees-till']}</td>
  <td>{$expr(number_format($membershipFees,2))} &euro;</td>
  <td></td>
  <td style='text-align: left;'>+ {$lang['global-donations']}</td>
  <td>{$expr(number_format($donations,2))} &euro;</td>
  <td></td>
  <td style='text-align: left;'>+ {$lang['global-donations']}</td>
  <td>{$expr(number_format($bankDonations,2))} &euro;</td>
 </tr>
EOD;
	if ($_SESSION['creditOrDirect'] == 0) {
		
		$mailtoadmin .= <<<EOD
 <tr>
  <td style='text-align: left;'>+ {$lang['closeday-membershipfees-bank']}</td>
  <td>{$expr(number_format($membershipfeesBank,2))} &euro;</td>
  <td></td>
  <td style='text-align: left;'>+ {$lang['dispensed-direct']}</td>
  <td>{$expr(number_format($salesTodayCash,2))} &euro;</td>
  <td></td>
  <td style='text-align: left;'>+ {$lang['dispensed-direct']}</td>
  <td>{$expr(number_format($salesTodayBank,2))} &euro;</td>
 </tr>
 <tr>
  <td style='text-align: left;'>+ {$lang['dispensed-direct-till']}</td>
  <td>{$expr(number_format($salesTodayCash,2))} &euro;</td>
  <td></td>
  <td style='text-align: left;'>+ {$lang['direct-bar-sales']}</td>
  <td>{$expr(number_format($salesTodayBarCash,2))} &euro;</td>
  <td></td>
  <td style='text-align: left;'>+ {$lang['direct-bar-sales']}</td>
  <td>{$expr(number_format($salesTodayBarBank,2))} &euro;</td>
 </tr>
 <tr>
  <td style='text-align: left;'>+ {$lang['dispensed-direct-bank']}</td>
  <td>{$expr(number_format($salesTodayBank,2))} &euro;</td>
  <td></td>
  <td style='text-align: left;'>-&nbsp; {$lang['closeday-moneybanked']}</td>
  <td style='text-align: right;'>{$expr(number_format($moneytaken + $bankedduringday,2))} &euro;</td>
  <td></td>
  <td style='text-align: left;'>+ {$lang['closeday-moneybanked']}</td>
  <td>{$expr(number_format($moneytaken + $bankedduringday,2))} &euro;</td>
 </tr>
 <tr>
  <td style='text-align: left;'>+ {$lang['direct-bar-sales-till']}</td>
  <td>{$expr(number_format($salesTodayBarCash,2))} &euro;</td>
  <td></td>
  <td style='text-align: left; border-bottom: 1px dashed #ababab;''>-&nbsp; {$lang['global-expenses']}</td>
  <td style='text-align: right; border-bottom: 1px dashed #ababab;'>{$expr(number_format($expenses,2))} &euro;</td>
  <td></td>
  <td style='text-align: left; border-bottom: 1px dashed #ababab;'>-&nbsp; {$lang['global-expenses']}</td>
  <td style='text-align: right; border-bottom: 1px dashed #ababab;'>{$expr(number_format($bankExpenses,2))} &euro;</td>
 </tr>
 <tr>
  <td style='text-align: left; border-bottom: 1px dashed #ababab;'>+ {$lang['direct-bar-sales-bank']}</td>
  <td style='text-align: right; border-bottom: 1px dashed #ababab;'>{$expr(number_format($salesTodayBarBank,2))} &euro;</td>
  <td></td>
  <td style='text-align: left; border-bottom: 1px solid #ababab;'>= {$lang['closeday-estimatedtill']}</td>
  <td style='text-align: right; border-bottom: 1px solid #ababab;'>{$expr(number_format($estimatedTill,2))} &euro;</td>
  <td></td>
  <td style='text-align: left; border-bottom: 1px solid #ababab;'>= {$lang['closeday-bankbalance']}</td>
  <td style='text-align: right; border-bottom: 1px solid #ababab;'>{$expr(number_format($bankBalance,2))} &euro;</td>
 </tr>
 <tr>
  <td style='text-align: left; border-bottom: 1px solid #ababab;'>= {$lang['closeday-totalincome']}</td>
  <td style='border-bottom: 1px solid #ababab;'>{$expr(number_format($income,2))} &euro;</td>
  <td></td>
  <td style='text-align: left;'>{$lang['closeday-yourcount']}</td>
  <td>{$expr(number_format($cashintill,2))} &euro;</td>
  <td></td>
  <td></td>
  <td></td>
 </tr>
 <tr>
  <td style='text-align: left;'>{$lang['global-expenses']}</td>
  <td>{$expr(number_format($expenses + $bankExpenses,2))} &euro;</td>
  <td></td>
  <td style='text-align: left;'><strong>{$lang['global-delta']}</strong></td>
  <td><strong>{$expr(number_format($tillDelta,2))} &euro;</strong></td>
  <td></td>
  <td></td>
  <td></td>
 </tr>
 <tr>
  <td style='text-align: left;'><strong>{$lang['profit']}</strong></td>
  <td><strong>{$expr(number_format($income - $expenses - $bankExpenses,2))} &euro;</strong></td>
  <td></td>
  <td colspan='6' style='text-align: left;'>{$lang['closeday-tillcomment']}:<br /><em>$tillComment</em> </td>
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
  <td style='text-align: left;'>{$lang['member-credit']}</td>
  <td>{$expr(number_format($totCredit,2))} &euro;</td>
  <td></td>
  <td></td>
  <td></td>
  <td></td>
  <td></td>
  <td></td>
 </tr>
EOD;

	} else {
		
		$mailtoadmin .= <<<EOD
 <tr>
  <td style='text-align: left; border-bottom: 1px dashed #ababab;''>+ {$lang['closeday-membershipfees-bank']}</td>
  <td style='border-bottom: 1px dashed #ababab;'>{$expr(number_format($membershipfeesBank,2))} &euro;</td>
  <td></td>
  <td style='text-align: left;'>-&nbsp; {$lang['closeday-moneybanked']}</td>
  <td style='text-align: right;'>{$expr(number_format($moneytaken + $bankedduringday,2))} &euro;</td>
  <td></td>
  <td style='text-align: left;'>+ {$lang['closeday-moneybanked']}</td>
  <td>{$expr(number_format($moneytaken + $bankedduringday,2))} &euro;</td>
 </tr>
 <tr>
  <td style='text-align: left; border-bottom: 1px solid #ababab;'>= {$lang['closeday-totalincome']}</td>
  <td style='border-bottom: 1px solid #ababab;'>{$expr(number_format($income,2))} &euro;</td>
  <td></td>
  <td style='text-align: left; border-bottom: 1px dashed #ababab;''>-&nbsp; {$lang['global-expenses']}</td>
  <td style='text-align: right; border-bottom: 1px dashed #ababab;'>{$expr(number_format($expenses,2))} &euro;</td>
  <td></td>
  <td style='text-align: left; border-bottom: 1px dashed #ababab;'>-&nbsp; {$lang['global-expenses']}</td>
  <td style='text-align: right; border-bottom: 1px dashed #ababab;'>{$expr(number_format($bankExpenses,2))} &euro;</td>
 </tr>
 <tr>
  <td style='text-align: left;'>{$lang['global-expenses']}</td>
  <td>{$expr(number_format($expenses + $bankExpenses,2))} &euro;</td>
  <td></td>
  <td style='text-align: left; border-bottom: 1px solid #ababab;'>= {$lang['closeday-estimatedtill']}</td>
  <td style='text-align: right; border-bottom: 1px solid #ababab;'>{$expr(number_format($estimatedTill,2))} &euro;</td>
  <td></td>
  <td style='text-align: left; border-bottom: 1px solid #ababab;'>= {$lang['closeday-bankbalance']}</td>
  <td style='text-align: right; border-bottom: 1px solid #ababab;'>{$expr(number_format($bankBalance,2))} &euro;</td>
 </tr>
 <tr>
  <td style='text-align: left;'><strong>{$lang['profit']}</strong></td>
  <td><strong>{$expr(number_format($income - $expenses - $bankExpenses,2))} &euro;</strong></td>
  <td></td>
  <td style='text-align: left;'>{$lang['closeday-yourcount']}</td>
  <td>{$expr(number_format($cashintill,2))} &euro;</td>
  <td></td>
  <td></td>
  <td></td>
 </tr>
 <tr>
  <td style='text-align: left;'>{$lang['closeday-totalclubbalance']}</td>
  <td>{$expr(number_format($closingbalance,2))} &euro;</td>
  <td></td>
  <td style='text-align: left;'><strong>{$lang['global-delta']}</strong></td>
  <td><strong>{$expr(number_format($tillDelta,2))} &euro;</strong></td>
  <td></td>
  <td></td>
  <td></td>
 </tr>
 <tr>
  <td style='text-align: left;'>{$lang['member-credit']}</td>
  <td>{$expr(number_format($totCredit,2))} &euro;</td>
  <td></td>
  <td colspan='6' style='text-align: left;'>{$lang['closeday-tillcomment']}:<br /><em>$tillComment</em> </td>
  <td></td>
  <td></td>
  <td></td>
  <td></td>
 </tr>
EOD;

	}

		$mailtoadmin .= <<<EOD
 <tr rowspan='2'>
  <td colspan='10'>&nbsp;</td>
 </tr>
</table>
<table style='color: #444; text-align: right; font-family: Tahoma, Verdana, sans-serif; font-size: 14px;'>
EOD;

				

		
		$defaultProducts = "SELECT category, '' AS categoryType, f.name, f.breed2, c.purchaseid, c.soldToday, c.weight, c.weightDelta, c.intStash, c.extStash, c.weightNoShake, c.totalWeight, c.totalNoShake, c.inMenu, c.specificComment, c.value FROM flower f, closingdetails c WHERE c.category = '1' AND c.productid = f.flowerid AND c.closingid = $closingid UNION ALL SELECT category, '' AS categoryType, e.name, '' AS breed2, c.purchaseid, c.soldToday, c.weight, c.weightDelta, c.intStash, c.extStash, c.weightNoShake, c.totalWeight, c.totalNoShake, c.inMenu, c.specificComment, c.value FROM extract e, closingdetails c WHERE c.category = '2' AND c.productid = e.extractid AND c.closingid = $closingid";
				
		$allProducts = $defaultProducts . $customProducts;
		try
		{
			$productsResult = $pdo3->prepare("$allProducts");
			$productsResult->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		while ($product = $productsResult->fetch()) {
			
			$category = $product['category'];
			$categoryType = $product['categoryType'];
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
			$value = $product['value'];
			
			$priceQuery = "SELECT salesPrice AS gramPrice from purchases where purchaseid = $purchaseid";
		try
		{
			$result = $pdo3->prepare("$priceQuery");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$rowP = $result->fetch();
				$gramPrice = $rowP['gramPrice'];
				
			$prodPrice = $totalWeight * $gramPrice;
			$deltaPrice = $weightDelta * $gramPrice;
			
			
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
		try
		{
			$result = $pdo3->prepare("$selectGrowTypeNo");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
					$growTypeNo = $row['growType'];
					$closedAt = $row['closedAt'];
					
				if ($growTypeNo != '') {
					
					$growDetails = "SELECT growtype FROM growtypes WHERE growtypeid = $growTypeNo";
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
  <td>{$expr(number_format($prodPrice,2))} €</td>
  <td>{$expr(number_format($weightDelta,2))} g.</td>
  <td>{$expr(number_format($deltaPrice,2))} €</td>
  <td>{$expr(number_format($soldToday,2))} g.</td>
 </tr>
$commentInset
EOD;

			} else if ($category == 2 && $dividersetExtract != 'yes') {
				
				// Look up closed status
				$selectGrowTypeNo = "SELECT closedAt FROM purchases WHERE purchaseid = $purchaseid";
		try
		{
			$result = $pdo3->prepare("$selectGrowTypeNo");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
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
  <td><strong>{$lang['value']}&nbsp;&nbsp;</strong></td>
  <td><strong>{$lang['global-delta']}&nbsp;&nbsp;</strong></td>
  <td><strong>{$lang['value']}&nbsp;&nbsp;</strong></td>
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
  <td>{$expr(number_format($prodPrice,2))} €</td>
  <td>{$expr(number_format($weightDelta,2))} g.</td>
  <td>{$expr(number_format($deltaPrice,2))} €</td>
  <td>{$expr(number_format($soldToday,2))} g.</td>
 </tr>	
$commentInset
EOD;

			} else if ($category == '2') {
				
				// Look up closed status
				$selectGrowTypeNo = "SELECT closedAt FROM purchases WHERE purchaseid = $purchaseid";
		try
		{
			$result = $pdo3->prepare("$selectGrowTypeNo");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
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
  <td>{$expr(number_format($prodPrice,2))} €</td>
  <td>{$expr(number_format($weightDelta,2))} g.</td>
  <td>{$expr(number_format($deltaPrice,2))} €</td>
  <td>{$expr(number_format($soldToday,2))} g.</td>
 </tr>	
$commentInset
EOD;

			} else {
				
				// Look up closed status
				$selectGrowTypeNo = "SELECT closedAt FROM purchases WHERE purchaseid = $purchaseid";
		try
		{
			$result = $pdo3->prepare("$selectGrowTypeNo");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
					$closedAt = $row['closedAt'];

				if ($closedAt != NULL) {
					$productStatus = "Closed";
				} else if ($inMenu == 0) {
					$productStatus = "Not in menu";
				} else {
					$productStatus = "In menu";
				}

				if ($categoryType == 0) {
					
				// See if header has been set
				if (${'otherHeader' . $category} != 'set') {
				
					// Look up categories
					$selectCats = "SELECT name FROM categories WHERE id = $category";
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
	
		$catRow = $result->fetch();
						$categoryname = $catRow['name'];
	
					// insert divider
					$gramMail .= <<<EOD
 <tr>
  <td colspan='11'></td>
 </tr>
 <tr>
  <td colspan='11' style='color: #a80082; text-align: center; font-size: 14px; border-top: 1px solid #dedede; border-bottom: 1px solid #dedede;'><strong>$categoryname (g.)</strong></td>
 </tr>
 <tr>
  <td></td>
  <td><strong>{$lang['closeday-jars']}&nbsp;&nbsp;</strong></td>
  <td><strong></strong></td>
  <td><strong>{$lang['closeday-intstash']}&nbsp;&nbsp;</strong></td>
  <td><strong>{$lang['closeday-extstash']}&nbsp;&nbsp;</strong></td>
  <td><strong>{$lang['global-total']}&nbsp;&nbsp;</strong></td>
  <td><strong></strong></td>
  <td><strong>{$lang['value']}&nbsp;&nbsp;</strong></td>
  <td><strong>{$lang['global-delta']}&nbsp;&nbsp;</strong></td>
  <td><strong>{$lang['value']}&nbsp;&nbsp;</strong></td>
 <td><strong>{$lang['closeday-dispensed']}&nbsp;&nbsp;</strong></td>
 </tr>
 <tr>
  <td style='text-align: left;'>$name</td>
  <td>{$expr(number_format($weight,2))} g.</td>
  <td></td>
  <td>{$expr(number_format($intStash,2))} g.</td>
  <td>{$expr(number_format($extStash,2))} g.</td>
  <td><strong>{$expr(number_format($weight + $intStash + $extStash,2))} g.</strong></td>
  <td></td>
  <td>{$expr(number_format($prodPrice,2))} €</td>
  <td>{$expr(number_format($weightDelta,2))} g.</td>
  <td>{$expr(number_format($deltaPrice,2))} €</td>
  <td>{$expr(number_format($soldToday,2))} g.</td>
 </tr>	
$commentInset
EOD;

					${'otherHeader' . $category} = 'set';
						
				} else {
				
					$gramMail .= <<<EOD
 <tr>
  <td style='text-align: left;'>$name</td>
  <td>{$expr(number_format($weight,2))} g.</td>
  <td></td>
  <td>{$expr(number_format($intStash,2))} g.</td>
  <td>{$expr(number_format($extStash,2))} g.</td>
  <td><strong>{$expr(number_format($weight + $intStash + $extStash,2))} g.</strong></td>
  <td></td>
  <td>{$expr(number_format($prodPrice,2))} €</td>
  <td>{$expr(number_format($weightDelta,2))} g.</td>
  <td>{$expr(number_format($deltaPrice,2))} €</td>
  <td>{$expr(number_format($soldToday,2))} g.</td>
 </tr>	
$commentInset
EOD;

				}
				
				} else {
					
					
				// See if header has been set
				if (${'otherHeader' . $category} != 'set') {
				
					// Look up categories
					$selectCats = "SELECT name FROM categories WHERE id = $category";
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
	
		$catRow = $result->fetch();
						$categoryname = $catRow['name'];
	
					// insert divider
					$unitMail .= <<<EOD
 <tr>
  <td colspan='11'></td>
 </tr>
 <tr>
  <td colspan='11' style='color: #a80082; text-align: center; font-size: 14px; border-top: 1px solid #dedede; border-bottom: 1px solid #dedede;'><strong>$categoryname (u.)</strong></td>
 </tr>
 <tr>
  <td></td>
  <td><strong>{$lang['closeday-jars']}&nbsp;&nbsp;</strong></td>
  <td><strong></strong></td>
  <td><strong>{$lang['closeday-intstash']}&nbsp;&nbsp;</strong></td>
  <td><strong>{$lang['closeday-extstash']}&nbsp;&nbsp;</strong></td>
  <td><strong>{$lang['global-total']}&nbsp;&nbsp;</strong></td>
  <td><strong></strong></td>
  <td><strong>{$lang['value']}&nbsp;&nbsp;</strong></td>
  <td><strong>{$lang['global-delta']}&nbsp;&nbsp;</strong></td>
  <td><strong>{$lang['value']}&nbsp;&nbsp;</strong></td>
  <td><strong>{$lang['closeday-dispensed']}&nbsp;&nbsp;</strong></td>
 </tr>
 <tr>
  <td style='text-align: left;'>$name</td>
  <td>{$expr(number_format($weight,2))} u.</td>
  <td></td>
  <td>{$expr(number_format($intStash,2))} u.</td>
  <td>{$expr(number_format($extStash,2))} u.</td>
  <td><strong>{$expr(number_format($weight + $intStash + $extStash,2))} u.</strong></td>
  <td></td>
  <td>{$expr(number_format($prodPrice,2))} €</td>
  <td>{$expr(number_format($weightDelta,2))} u.</td>
  <td>{$expr(number_format($deltaPrice,2))} €</td>
  <td>{$expr(number_format($soldToday,2))} u.</td>
 </tr>	
$commentInset
EOD;

					${'otherHeader' . $category} = 'set';
						
				} else {
				
					$unitMail .= <<<EOD
 <tr>
  <td style='text-align: left;'>$name</td>
  <td>{$expr(number_format($weight,2))} u.</td>
  <td></td>
  <td>{$expr(number_format($intStash,2))} u.</td>
  <td>{$expr(number_format($extStash,2))} u.</td>
  <td><strong>{$expr(number_format($weight + $intStash + $extStash,2))} u.</strong></td>
  <td></td>
  <td>{$expr(number_format($prodPrice,2))} €</td>
  <td>{$expr(number_format($weightDelta,2))} u.</td>
  <td>{$expr(number_format($deltaPrice,2))} €</td>
  <td>{$expr(number_format($soldToday,2))} u.</td>
 </tr>	
$commentInset
EOD;

				}
				}


			}
			
	  		// Create totals per category
			if ($category == 1) {
				$flowerWeightPrice = $flowerWeightPrice + $prodPrice;
				$flowerDeltaPrice = $flowerDeltaPrice + $deltaPrice;
			} else if ($category == 2) {
				$extractWeightPrice = $extractWeightPrice + $prodPrice;
				$extractDeltaPrice = $extractDeltaPrice + $deltaPrice;
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
					
					$otherTotals[$catID]['otherWeightPrice'] = $otherTotals[$catID]['otherWeightPrice'] + $prodPrice;
					$otherTotals[$catID]['otherDeltaPrice'] = $otherTotals[$catID]['otherDeltaPrice'] + $deltaPrice;


			}

		} // Ends products loop
		


		$mailtoProductResponsible .= $gramMail;
		$mailtoProductResponsible .= $unitMail;
		
		$mailtoProductResponsible .= "</table>";
			
	
				
		
		
		
		
		
		
		
		
		  
			// Query to look up expenses
			$selectExpenses = "SELECT expenseid, registertime, userid, expensetype, expense, moneysource, other, amount, shop, comment, receipt, expensecategory FROM expenses WHERE (registertime BETWEEN '$openingSQL' AND '$closingSQL') ORDER by registertime DESC";
		try
		{
			$result2 = $pdo3->prepare("$selectExpenses");
			$result2->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		
				
				
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
		
		
		while ($expense = $result2->fetch()) {
			
			
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
			$result = $pdo3->prepare("$userDetails");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		while ($user = $result->fetch()) {
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
  <td><strong>{$lang['value']}&nbsp;&nbsp;</strong></td>
  <td><strong>{$lang['global-delta']}&nbsp;&nbsp;</strong></td>
  <td><strong>{$lang['value']}&nbsp;&nbsp;</strong></td>
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
  <td>{$expr(number_format($flowerWeightPrice,2))} €</td>
  <td>{$expr(number_format($stockDeltaFlower,2))} g.</td>
  <td>{$expr(number_format($flowerDeltaPrice,2))} €</td>
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
  <td>{$expr(number_format($extractWeightPrice,2))} €</td>
  <td>{$expr(number_format($stockDeltaExtract,2))} g.</td>
  <td>{$expr(number_format($extractDeltaPrice,2))} €</td>
  <td>{$expr(number_format($extractDispensed,2))} g.</td>
 </tr>
EOD;

		// Here insert other categories + summary, read from closingother / shiftcloseother
		$selectOtherCats = "SELECT category, categoryType, prodStock, intStash, extStash, quantitySold, quantitySoldReal, stockDelta, unitsSold FROM closingother WHERE closingid = $closingid";
		try
		{
			$resultOC = $pdo3->prepare("$selectOtherCats");
			$resultOC->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		while ($rowOC = $resultOC->fetch()) {
			
			$category = $rowOC['category'];
			$categoryType = $rowOC['categoryType'];
			$prodStock = $rowOC['prodStock'];
			$intStash = $rowOC['intStash'];
			$extStash = $rowOC['extStash'];
			$quantitySold = $rowOC['quantitySold'];
			$unitsSold = $rowOC['unitsSold'];
			$quantitySoldReal = $rowOC['quantitySoldReal'];
			$stockDelta = $rowOC['stockDelta'];
			
			$thisTotal = $prodStock + $intStash + $extStash;
			
			
			// Look up category name
			$findCatName = "SELECT name FROM categories WHERE id = $category";
		try
		{
			$result = $pdo3->prepare("$findCatName");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$rowCN = $result->fetch();
				$categoryName = $rowCN['name'];
				
			if ($categoryType == 0) {
				
				// Grams
				
				// Aggregates
				$cat0prodStock = $cat0prodStock + $prodStock;
				$cat0intStash = $cat0intStash + $intStash;
				$cat0extStash = $cat0extStash + $extStash;
				$cat0thisTotal = $cat0thisTotal + $thisTotal;
				$cat0stockDelta = $cat0stockDelta + $stockDelta;
				$cat0quantitySoldReal = $cat0quantitySoldReal + $quantitySoldReal;
				$cat0quantitySold = $cat0quantitySold + $quantitySold;
				$euroTotG = $euroTotG + $otherTotals[$category]['otherWeightPrice'];
				$deltaTotG = $deltaTotG + $otherTotals[$category]['otherDeltaPrice'];
				
				$gramProducts .= <<<EOD
 <tr>
  <td style='text-align: left;'>$categoryName</td>
  <td>{$expr(number_format($prodStock,2))} g.</td>
  <td>{$expr(number_format($prodStock,2))} g.</td>
  <td>{$expr(number_format($intStash,2))} g.</td>
  <td>{$expr(number_format($extStash,2))} g.</td>
  <td><strong>{$expr(number_format($thisTotal,2))} g.</strong></td>
  <td><strong>{$expr(number_format($prodStock,2))} g.</strong></td>
  <td>{$expr(number_format($otherTotals[$category]['otherWeightPrice'],2))} €</td>
  <td>{$expr(number_format($stockDelta,2))} g.</td>
  <td>{$expr(number_format($otherTotals[$category]['otherDeltaPrice'],2))} €</td>
EOD;
				if ($_SESSION['realWeight'] == 1) {
					
				$gramProducts .= <<<EOD
  <td>{$expr(number_format($quantitySoldReal,2))} g.</td>
 </tr>
EOD;

				} else {
					
				$gramProducts .= <<<EOD
  <td>{$expr(number_format($quantitySold,2))} g.</td>
 </tr>
EOD;

				}

				
			} else {
				
				// Units
				
				// Aggregates
				$cat1prodStock = $cat1prodStock + $prodStock;
				$cat1intStash = $cat1intStash + $intStash;
				$cat1extStash = $cat1extStash + $extStash;
				$cat1thisTotal = $cat1thisTotal + $thisTotal;
				$cat1stockDelta = $cat1stockDelta + $stockDelta;
				$cat1unitsSold = $cat1unitsSold + $unitsSold;
				$euroTotU = $euroTotU + $otherTotals[$category]['otherWeightPrice'];
				$deltaTotU = $deltaTotU + $otherTotals[$category]['otherDeltaPrice'];
			
				$unitProducts .= <<<EOD
 <tr>
  <td style='text-align: left;'>$categoryName</td>
  <td>{$expr(number_format($prodStock,2))} u.</td>
  <td></td>
  <td>{$expr(number_format($intStash,2))} u.</td>
  <td>{$expr(number_format($extStash,2))} u.</td>
  <td><strong>{$expr(number_format($thisTotal,2))} u.</strong></td>
  <td></td>
  <td>{$expr(number_format($otherTotals[$category]['otherWeightPrice'],2))} €</td>
  <td>{$expr(number_format($stockDelta,2))} u.</td>
  <td>{$expr(number_format($otherTotals[$category]['otherDeltaPrice'],2))} €</td>
  <td>{$expr(number_format($unitsSold,2))} u.</td>
 </tr>
EOD;

			}
			
		}
			
		$MPR .= $gramProducts;
		
		$MPR .= <<<EOD
 <tr style='border-top: 1px solid #888; border-bottom: 2px solid #888;'>
  <td style='text-align: left;'><strong>TOTAL G</strong></td>
  <td><strong>{$expr(number_format($prodStockFlower + $prodStockExtract + $cat0prodStock,2))} g.</strong></td>
  <td><strong>{$expr(number_format($flowerweightNoShake + $prodStockExtract + $cat0prodStock,2))} g.</strong></td>
  <td><strong>{$expr(number_format($flowerintStash + $extractintStash + $cat0intStash,2))} g.</strong></td>
  <td><strong>{$expr(number_format($flowerextStash + $extractextStash + $cat0extStash,2))} g.</strong></td>
  <td><strong>{$expr(number_format($flowertotalWeight + $extracttotalWeight + $cat0thisTotal,2))} g.</strong></td>
  <td><strong>{$expr(number_format($flowertotalNoShake + $extracttotalWeight + $cat0thisTotal,2))} g.</strong></td>
  <td><strong>{$expr(number_format($euroTotG + $extractWeightPrice + $flowerWeightPrice,2))} &euro;</strong></td>
  <td><strong>{$expr(number_format($stockDeltaFlower + $stockDeltaExtract + $cat0stockDelta,2))} g.</strong></td>
  <td><strong>{$expr(number_format($deltaTotG + $extractDeltaPrice + $flowerDeltaPrice,2))} &euro;</strong></td>
EOD;
				if ($_SESSION['realWeight'] == 1) {
					
		$MPR .= <<<EOD
  <td>{$expr(number_format($flowerDispensed + $extractDispensed + $cat0quantitySoldReal,2))} g.</td>
 </tr>
EOD;

				} else {
					
		$MPR .= <<<EOD
  <td>{$expr(number_format($flowerDispensed + $extractDispensed + $cat0quantitySold,2))} g.</td>
 </tr>
EOD;

				}
		$MPR .= <<<EOD
 <tr>
  <td colspan='11'>&nbsp;</td>
 </tr>
EOD;

		$MPR .= $unitProducts;
		$MPR .= <<<EOD
 <tr style='border-top: 1px solid #888; border-bottom: 2px solid #888;'>
  <td style='text-align: left;'><strong>TOTAL U</strong></td>
  <td><strong>{$expr(number_format($cat1prodStock,2))} u.</strong></td>
  <td></td>
  <td><strong>{$expr(number_format($cat1intStash,2))} u.</strong></td>
  <td><strong>{$expr(number_format($cat1extStash,2))} u.</strong></td>
  <td><strong>{$expr(number_format($cat1thisTotal,2))} u.</strong></td>
  <td></td>
  <td><strong>{$expr(number_format($euroTotU,2))} &euro;</strong></td>
  <td><strong>{$expr(number_format($cat1stockDelta,2))} u.</strong></td>
  <td><strong>{$expr(number_format($deltaTotU,2))} &euro;</strong></td>
  <td><strong>{$expr(number_format($cat1unitsSold,2))} u.</strong></td>
 <tr>
  <td colspan='11'>&nbsp;</td>
 </tr>
EOD;
		
		
		echo $MPR;
		echo <<<EOD
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
  <td><strong>{$lang['value']}&nbsp;&nbsp;</strong></td>
  <td><strong>{$lang['global-delta']}&nbsp;&nbsp;</strong></td>
  <td><strong>{$lang['value']}&nbsp;&nbsp;</strong></td>
  <td><strong>{$lang['closeday-dispensed']}&nbsp;&nbsp;</strong></td>
 </tr>
EOD;
		echo $mailtoProductResponsibleFull;
			
	  echo $expenseDetails;
	  echo "</table>";

			
			
			
			
			
			
			
			
			
			
			
			
			
			
			
			

			
			
			
			
			
			
			
			
			
			
			
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
					
		} else {
			
			$_SESSION['errorMessage'] = $lang['no-closing-found'];
			
			// Closing does not exist, generate data

			$reportDate = $_POST['reportDate'];
			$reportDateReadable = date("d-m-Y", strtotime($_POST['reportDate']));
			$openingSQL = $_POST['reportDate'] . " 06:00:00";
			$closingSQL = date("Y-m-d", strtotime($_POST['reportDate'] . " +1 days")) . " 06:00:00";
			
			// Total members today
			$selectMembers = "SELECT COUNT(memberno) from users WHERE memberno <> '0' AND userGroup < 6 AND registeredSince <= '$closingSQL'";
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
		
			// New members today
			$newMembers = "SELECT COUNT(user_id) FROM users where (registeredSince BETWEEN '$openingSQL' AND '$closingSQL')";
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
			$bannedmembers = "SELECT COUNT(user_id) FROM users where (banTime BETWEEN '$openingSQL' AND '$closingSQL')";
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
			$deletedmembers = "SELECT COUNT(user_id) FROM users where (deleteTime BETWEEN '$openingSQL' AND '$closingSQL')";
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
				
			// Look up todays dispenses
			$selectSales = "SELECT SUM(amount), SUM(quantity), SUM(units) from sales WHERE (saletime BETWEEN '$openingSQL' AND '$closingSQL')";
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
				$unitsSold = $row['SUM(units)'];
				
			// Look up todays dispenses by category 1
			$selectSalesFlower = "SELECT SUM(d.amount), SUM(d.quantity) from sales s, salesdetails d WHERE s.saleid = d.saleid AND (s.saletime BETWEEN '$openingSQL' AND '$closingSQL') AND d.category = 1";
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
				
			$flowerSalesPercentageToday = ($salesTodayFlower / $salesToday) * 100;
			$flowerGramsPercentageToday = ($quantitySoldFlower / $quantitySold) * 100;
			
			// Look up todays dispenses by category 2
			$selectSalesExtract = "SELECT SUM(d.amount), SUM(d.quantity) from sales s, salesdetails d WHERE s.saleid = d.saleid AND (s.saletime BETWEEN '$openingSQL' AND '$closingSQL') AND d.category = 2";
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
				
			$extractSalesPercentageToday = ($salesTodayExtract / $salesToday) * 100;
			$extractGramsPercentageToday = ($quantitySoldExtract / $quantitySold) * 100;
			
			// Look up todays dispenses by non-default categories
			// Another method; Look up which categories have a TYPE 1. Then 'ping' those categories. Much better and faster!
			// Query to look for category
			$categoryDetailsC = "SELECT id, name, type FROM categories WHERE type = 1";
		try
		{
			$resultC = $pdo3->prepare("$categoryDetailsC");
			$resultC->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
				
			$grCatList = '';
			
		while ($rowC = $resultC->fetch()) {
						
				$catId = $rowC['id'];
				
				$grCatList = $grCatList . $catId . ",";
				
			}
			
			$grCatListfinal = substr($grCatList, 0, -1);
							
				
			// Look up today's bar sales
			$selectBarSales = "SELECT SUM(amount), SUM(unitsTot) from b_sales WHERE (saletime BETWEEN '$openingSQL' AND '$closingSQL')";
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
			$selectDonations = "SELECT SUM(amount) from donations WHERE (donatedTo < 2 OR donatedTo = 4) AND (donationTime BETWEEN '$openingSQL' AND '$closingSQL')";
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
				
			// Look up todays bank donations
			$selectDonations = "SELECT SUM(amount) from donations WHERE donatedTo = 2 AND (donationTime BETWEEN '$openingSQL' AND '$closingSQL')";
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
				
			// Look up todays donations
			$selectDonations = "SELECT COUNT(donationid) from donations WHERE (donatedTo < 2 OR donatedTo = 4) AND (donationTime BETWEEN '$openingSQL' AND '$closingSQL')";
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
				$donationsNo = $row['COUNT(donationid)'];
				
			// Look up todays bank donations
			$selectDonations = "SELECT COUNT(donationid) from donations WHERE donatedTo = 2 AND (donationTime BETWEEN '$openingSQL' AND '$closingSQL')";
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
				$bankDonationsNo = $row['COUNT(donationid)'];
					
				
			// Look up today's membership fees
			$selectMembershipFees = "SELECT SUM(amountPaid) FROM memberpayments WHERE (paidTo < 2 OR paidTo = 4) AND (paymentdate BETWEEN '$openingSQL' AND '$closingSQL')";
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
			$selectMembershipFees = "SELECT SUM(amountPaid) FROM memberpayments WHERE paidTo = 2 AND (paymentdate BETWEEN '$openingSQL' AND '$closingSQL')";
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
				
			// Look up today's till expenses
			$selectExpenses = "SELECT SUM(amount) FROM expenses WHERE (registertime BETWEEN '$openingSQL' AND '$closingSQL') AND moneysource = 1";
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
				
			// Look up today's bank expenses
			$selectExpenses = "SELECT SUM(amount) FROM expenses WHERE registertime BETWEEN '$openingtime' AND '$closingtime' AND moneysource = 2";
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
				
			if ($_SESSION['creditOrDirect'] == 0) {
				
				// Look up dispensed today cash
				$selectSales = "SELECT SUM(amount) from sales WHERE (saletime BETWEEN '$openingSQL' AND '$closingSQL') AND direct < 2";
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
				$selectSales = "SELECT SUM(amount) from sales WHERE (saletime BETWEEN '$openingSQL' AND '$closingSQL') AND direct = 2";
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
				$selectSales = "SELECT SUM(amount) from b_sales WHERE (saletime BETWEEN '$openingSQL' AND '$closingSQL') AND direct < 2";
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
				$selectSales = "SELECT SUM(amount) from b_sales WHERE (saletime BETWEEN '$openingSQL' AND '$closingSQL') AND direct = 2";
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
				
				// Calculate total income
				$totalIncome = $donations + $bankDonations + $membershipFees + $membershipfeesBank + $salesTodayCash + $salesTodayBank + $salesTodayBarCash + $salesTodayBarBank;

			} else {
				
				// Calculate total income
				$totalIncome = $donations + $bankDonations + $membershipFees + $membershipfeesBank;
				
			}
							
			pageStart($lang['daily-reports'] . ": $reportDateReadable", NULL, NULL, "preporting", "daily", $lang['daily-reports'] . ": $reportDateReadable", $_SESSION['successMessage'], $_SESSION['errorMessage']);
			
		
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
		$selectCats = "SELECT id, name, type from categories WHERE id > 2 ORDER by name ASC";
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
	
			
					$i = 0;
					
		while ($category = $resultCats->fetch()) {
					
					$categoryid = $category['id'];
					$name = $category['name'];
					$type = $category['type'];
						
		// Create more product queries for each category - to be used further down!
		$customProducts .= " UNION ALL SELECT '$categoryid' AS category, pr.productid AS productid, pr.name AS name, p.purchaseid AS purchaseid, '' AS growtype, p.inMenu AS inMenu, p.closedAt AS closedAt, salesPrice AS gramPrice, p.realQuantity FROM products pr, purchases p WHERE p.category = $categoryid AND p.productid = pr.productid AND (p.closedAt IS NULL OR (p.closingDate >= '$closingSQL')) AND (p.purchaseDate <= '$closingSQL')";
		
		
		// Look up sales in this cat
		$selectSalesOthers = "SELECT SUM(d.amount), SUM(d.quantity), SUM(d.realQuantity) from sales s, salesdetails d WHERE s.saleid = d.saleid AND (s.saletime BETWEEN '$openingSQL' AND '$closingSQL') AND d.category = $categoryid";
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
  <td>{$expr(number_format($salesTodayOthers,2))} &euro;</td>
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
  <td>{$expr(number_format($salesTodayOthers,2))} &euro;</td>
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
  <td style='vertical-align: bottom; text-align: center; font-size: 14px;' colspan="2"><strong>{$lang['summary']}</strong></td>
  <td></td>
  <td></td>
  <td></td>
  <td></td>
 </tr>
 <tr>
  <td style='text-align: left;'>{$lang['closeday-donations-till']}</td>
  <td>{$expr(number_format($donations,2))} &euro;</td>
  <td></td>
  <td></td>
  <td></td>
  <td></td>
  <td></td>
  <td></td>
 </tr>
 <tr>
  <td style='text-align: left;'>+ {$lang['closeday-donations-bank']}</td>
  <td>{$expr(number_format($bankDonations,2))} &euro;</td>
  <td></td>
  <td></td>
  <td></td>
  <td></td>
  <td></td>
  <td></td>
 </tr>
 <tr>
  <td style='text-align: left;'>+ {$lang['closeday-membershipfees-till']}</td>
  <td>{$expr(number_format($membershipFees,2))} &euro;</td>
  <td></td>
  <td></td>
  <td></td>
  <td></td>
  <td></td>
  <td></td>
 </tr>
EOD;
	if ($_SESSION['creditOrDirect'] == 0) {
		
		echo <<<EOD
 <tr>
  <td style='text-align: left;'>+ {$lang['closeday-membershipfees-bank']}</td>
  <td>{$expr(number_format($membershipfeesBank,2))} &euro;</td>
  <td></td>
  <td></td>
  <td></td>
  <td></td>
  <td></td>
  <td></td>
 </tr>
 <tr>
  <td style='text-align: left;'>+ {$lang['dispensed-direct-till']}</td>
  <td>{$expr(number_format($salesTodayCash,2))} &euro;</td>
  <td></td>
  <td></td>
  <td></td>
  <td></td>
  <td></td>
  <td></td>
 </tr>
 <tr>
  <td style='text-align: left;'>+ {$lang['dispensed-direct-bank']}</td>
  <td>{$expr(number_format($salesTodayBank,2))} &euro;</td>
  <td></td>
  <td></td>
  <td></td>
  <td></td>
  <td></td>
  <td></td>
 </tr>
 <tr>
  <td style='text-align: left;'>+ {$lang['direct-bar-sales-till']}</td>
  <td>{$expr(number_format($salesTodayBarCash,2))} &euro;</td>
  <td></td>
  <td></td>
  <td></td>
  <td></td>
  <td></td>
  <td></td>
 </tr>
 <tr>
  <td style='text-align: left; border-bottom: 1px dashed #ababab;'>+ {$lang['direct-bar-sales-bank']}</td>
  <td style='text-align: right; border-bottom: 1px dashed #ababab;'>{$expr(number_format($salesTodayBarBank,2))} &euro;</td>
  <td></td>
  <td></td>
  <td></td>
  <td></td>
  <td></td>
  <td></td>
 </tr>
 <tr>
  <td style='text-align: left; border-bottom: 1px solid #ababab;'>= {$lang['closeday-totalincome']}</td>
  <td style='border-bottom: 1px solid #ababab;'>{$expr(number_format($totalIncome,2))} &euro;</td>
  <td></td>
  <td></td>
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
  <td style='text-align: left;'><strong>{$lang['profit']}</strong></td>
  <td><strong>{$expr(number_format($totalIncome - $tillExpenses - $bankExpenses,2))} &euro;</strong></td>
  <td></td>
  <td></td>
  <td></td>
  <td></td>
  <td></td>
  <td></td>
 </tr>
EOD;

	} else {
		
		echo <<<EOD
 <tr>
  <td style='text-align: left; border-bottom: 1px dashed #ababab;'>+ {$lang['closeday-membershipfees-bank']}</td>
  <td style='text-align: right; border-bottom: 1px dashed #ababab;'>{$expr(number_format($membershipfeesBank,2))} &euro;</td>
  <td></td>
  <td></td>
  <td></td>
  <td></td>
  <td></td>
  <td></td>
 </tr>
 <tr>
  <td style='text-align: left; border-bottom: 1px solid #ababab;'>= {$lang['closeday-totalincome']}</td>
  <td style='border-bottom: 1px solid #ababab;'>{$expr(number_format($totalIncome,2))} &euro;</td>
  <td></td>
  <td></td>
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
  <td style='text-align: left;'><strong>{$lang['profit']}</strong></td>
  <td><strong>{$expr(number_format($totalIncome - $tillExpenses - $bankExpenses,2))} &euro;</strong></td>
  <td></td>
  <td></td>
  <td></td>
  <td></td>
  <td></td>
  <td></td>
 </tr>
EOD;

	}

		echo <<<EOD
 <tr rowspan='2'>
  <td colspan='10'>&nbsp;</td>
 </tr>
</table>
<table style='color: #444; text-align: right; font-family: Tahoma, Verdana, sans-serif; font-size: 14px;'>
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
		
			$selectProducts = "SELECT '1' AS category, g.flowerid AS productid, g.name AS name, p.purchaseid AS purchaseid, p.growType AS growtype, p.inMenu AS inMenu, p.closedAt AS closedAt, salesPrice AS gramPrice, p.realQuantity FROM flower g, purchases p WHERE p.category = 1 AND p.productid = g.flowerid AND (p.closedAt IS NULL OR (p.closingDate >= '$closingSQL')) AND (p.purchaseDate <= '$closingSQL') UNION ALL SELECT '2' AS category, h.extractid AS productid, h.name AS name, p.purchaseid AS purchaseid, '' AS growtype, p.inMenu AS inMenu, p.closedAt AS closedAt, salesPrice AS gramPrice, p.realQuantity FROM extract h, purchases p WHERE p.category = 2 AND p.productid = h.extractid AND (p.closedAt IS NULL OR (p.closingDate >= '$closingSQL')) AND (p.purchaseDate <= '$closingSQL')";
			
			$selectProducts .= $customProducts;
		try
		{
			$resultProducts = $pdo3->prepare("$selectProducts");
			$resultProducts->execute();
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
		  <td style='text-align: left;'><strong>{$lang['global-name']}&nbsp;&nbsp;</strong></td>
		  <td><strong>{$lang['closeday-jars']}&nbsp;&nbsp;</strong></td>
		  <td><strong>{$lang['closeday-intstash']}&nbsp;&nbsp;</strong></td>
		  <td><strong>{$lang['closeday-extstash']}&nbsp;&nbsp;</strong></td>
		  <td><strong>{$lang['global-total']}&nbsp;&nbsp;</strong></td>
		  <td><strong>{$lang['value']}&nbsp;&nbsp;</strong></td>
		  <td><strong>{$lang['closeday-dispensed']}&nbsp;&nbsp;</strong></td>
		 </tr>
EOD;
		
				
		while ($product = $resultProducts->fetch()) {
					
					
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
						
					// Look up todays dispenses
					$selectSales = "SELECT SUM(d.quantity) FROM salesdetails d, sales s WHERE (s.saletime BETWEEN '$openingSQL' AND '$closingSQL') AND d.saleid = s.saleid AND d.purchaseid = $purchaseid";
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
		
					// Look up total dispenses
					$selectSalesTot = "SELECT SUM(d.quantity) FROM salesdetails d, sales s WHERE (s.saletime <= '$closingSQL') AND d.saleid = s.saleid AND d.purchaseid = $purchaseid";
		try
		{
			$result = $pdo3->prepare("$selectSalesTot");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
						$soldTotal = $rowTot['SUM(d.quantity)'];
						
						
					// Look up additions and removals
					$selectAdditions = "SELECT SUM(quantity) FROM productmovements WHERE purchaseid = $purchaseid AND type = 1 AND (movementtime <= '$closingSQL') AND movementTypeid < 23";
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
						
					$selectRemovals = "SELECT SUM(quantity) FROM productmovements WHERE purchaseid = $purchaseid AND type = 2 AND (movementtime <= '$closingSQL') AND movementTypeid < 23";
					
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
					$jarWeight = $openingWeight + $addedToday - $takeoutsToday - $soldTotal;
					
		
					// Calculate what's in Internal stash
					$selectStashedInt = "SELECT SUM(quantity) FROM productmovements WHERE purchaseid = $purchaseid AND type = 2 AND (movementtime <= '$closingSQL') AND (movementTypeid = 5 OR movementTypeid = 18)";
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
						
					$selectUnStashedInt = "SELECT SUM(quantity) FROM productmovements WHERE purchaseid = $purchaseid AND type = 1 AND (movementtime <= '$closingSQL') AND (movementTypeid = 12 OR movementTypeid = 17)";
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
					$selectStashedExt = "SELECT SUM(quantity) FROM productmovements WHERE purchaseid = $purchaseid AND type = 2 AND (movementtime <= '$closingSQL') AND (movementTypeid = 6 OR movementTypeid = 20)";
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
						
					$selectUnStashedExt = "SELECT SUM(quantity) FROM productmovements WHERE purchaseid = $purchaseid AND type = 1 AND (movementtime <= '$closingSQL') AND (movementTypeid = 2 OR movementTypeid = 19)";
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
  <td colspan='7'></td>
 </tr>
 <tr>
  <td colspan='7' style='color: #a80082; text-align: center; font-size: 14px; border-bottom: 1px solid #dedede; border-top: 1px solid #dedede; margin-top: 10px;'><strong>$catName</strong></td>
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
					$otherTotals[$catID]['categoryType'] = $type;
					$otherTotals[$catID]['otherTotJar'] = $otherTotals[$catID]['otherTotJar'] + $jarWeight;
					$otherTotals[$catID]['otherTotIntSt'] = $otherTotals[$catID]['otherTotIntSt'] + $inStashInt;
					$otherTotals[$catID]['otherTotExtSt'] = $otherTotals[$catID]['otherTotExtSt'] + $inStashExt;
					$otherTotals[$catID]['otherTot'] = $otherTotals[$catID]['otherTotJar'] + $otherTotals[$catID]['otherTotIntSt'] + $otherTotals[$catID]['otherTotExtSt'];
					$otherTotals[$catID]['otherSoldToday'] = $otherTotals[$catID]['otherSoldToday'] + $soldToday;
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
  <td>{$expr(number_format($weightPrice,2))} &euro;</td>
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
  <td>{$expr(number_format($weightPrice,2))} &euro;</td>
  <td>{$expr(number_format($soldToday,2))} u.</td>
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
  <td>{$expr(number_format($oTot['otherWeightPrice'],2))} &euro;</td>
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
  <td>{$expr(number_format($oTot['otherWeightPrice'],2))} &euro;</td>
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
EOD;

		$productOverview .= $productOvvG;
		
		$productOverview .= <<<EOD
 <tr style='border-top: 1px solid #888; border-bottom: 2px solid #888;'>
  <td style='text-align: left;'><strong>TOTAL G</strong></td>
  <td><strong>{$expr(number_format($fullTotJar,2))} g.</strong></td>
  <td><strong>{$expr(number_format($fullTotIntSt,2))} g.</strong></td>
  <td><strong>{$expr(number_format($fullTotExtSt,2))} g.</strong></td>
  <td><strong>{$expr(number_format($fullTot,2))} g.</strong></td>
  <td><strong>{$expr(number_format($fullWeightPrice,2))} &euro;</strong></td>
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
  <td><strong>{$expr(number_format($unitsotherWeightPrice,2))} &euro;</strong></td>
  <td><strong>{$expr(number_format($unitsotherSoldToday,2))} u.</strong></td>
 </tr>
 <tr>
  <td colspan='7'>&nbsp;</td>
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