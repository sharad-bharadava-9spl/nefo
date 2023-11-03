<?php
	
	require_once '../cOnfig/connection.php';
	require_once '../cOnfig/view.php';
	require_once '../cOnfig/authenticate.php';
	require_once '../cOnfig/languages/common.php';
	
	session_start();
	$accessLevel = '3';
	
	// Authenticate & authorize
	authorizeUser($accessLevel);
	
		// If no closing ID is set, we display the list of closing dates
		if (!isset($_POST['closingid'])) {
			
			// Pagination settings & initialisation
			$resultLimit = 50;
			
			$selectNoOfClosings = "SELECT count(closingid) FROM closing";
			$noOfClosings = mysql_query($selectNoOfClosings);
			
			$row = mysql_fetch_array($noOfClosings);
				$noOfClosings = $row[0];
							
			if (isset($_GET['page'])) {
	        	$page = $_GET['page'] + 1;
	            $offset = $resultLimit * $page;
	        } else {
	            $page = 0;
	            $offset = 0;
	        }
			
	        $resultsLeft = $noOfClosings - ($page * $resultLimit);
	        
			$selectClosings = "SELECT closingid, closingtime FROM closing ORDER BY closingtime DESC LIMIT $offset, $resultLimit";
				
			$selectClosings = mysql_query($selectClosings)
				or handleError($lang['error-expensesload'],"Error loading expense from db: " . mysql_error());
			
			$i = 0;
			while ($closing = mysql_fetch_array($selectClosings)) {
				$closingid = $closing['closingid'];
				$closingdateSQL = date("Y-m-d", strtotime($closing['closingtime']));
				$closingdate = date("l d-m-Y", strtotime($closing['closingtime']));
				
				if ($i == 0) {
					$output .= "<br /><strong>" .  date('F', strtotime($closing['closingtime'])) . "</strong><br />";
				}
				$output .= "<form action='' method='POST'><input type='hidden' name='closingid' value='$closingid'><input type='hidden' name='closingdateSQL' value='$closingdateSQL'><input type='hidden' name='closingdate' value='$closingdate'><button type='submit' class='linkStyle'>$closingdate</button></form>";
				
				// Do we show the month name or not?
				if (date('j', strtotime($closing['closingtime'])) === '1') {
					$output .= "<br /><strong>" .  date('F', strtotime($closing['closingtime'] . '-1 month')) . "</strong><br />";
				}
				
				$i++;
				
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
			
			$closingid = $_POST['closingid'];
			$closingdateSQL = $_POST['closingdateSQL'];			
			$closingdate = $_POST['closingdate'];	

		$selectCloseDetails = "SELECT quantitySold, soldtoday, closingbalance, moneytaken, cashintill, bankBalance, newmembers, expenses, membershipFees, tillDelta, bankExpenses, income, paraphernalia, biscuits, drinksandsnacks, prerolls, otherAdditions, renewedMembers, bannedMembers, deletedMembers, expiredMembers, totalMembers, activeMembers, flowerDispensed, extractDispensed, soldTodayFlower, soldTodayExtract, donations from closing WHERE closingid = $closingid";
		
		$closingResult = mysql_query($selectCloseDetails)
		or handleError($lang['error-loadnewmembers'],"Error loading sale from db: " . mysql_error());
	
		$row = mysql_fetch_array($closingResult);
			$quantitySold = $row['quantitySold'];
			$salesToday = $row['soldtoday'];
			$closingbalance = $row['closingbalance'];
			$banked = $row['moneytaken'];
			$tillTot = $row['cashintill'];
			$bankBalance = $row['bankBalance'];
			$newmembers = $row['newmembers'];
			$tillExpenses = $row['expenses'];
			$membershipFees = $row['membershipFees'];
			$tillDelta = $row['tillDelta'];
			$bankExpenses = $row['bankExpenses'];
			$totalIncome = $row['income'];
			$papersAndP = $row['paraphernalia'];
			$biscuitsAndTHC = $row['biscuits'];
			$drinksAndSnacks = $row['drinksandsnacks'];
			$prerolls = $row['prerolls'];
			$tillAdditions = $row['otherAdditions'];
			$donationsToday = $row['donations'];
			
			$renewedMembers = $row['renewedMembers'];
			$bannedmembers = $row['bannedMembers'];
			$deletedmembers = $row['deletedMembers'];
			$expiredmembers = $row['expiredMembers'];
			$currentmembers = $row['totalMembers'];
			$activemembers = $row['activeMembers'];
	
			$quantitySoldFlower = $row['flowerDispensed'];
			$quantitySoldExtract = $row['extractDispensed'];
			$salesTodayFlower = $row['soldTodayFlower'];
			$salesTodayExtract = $row['soldTodayExtract'];

				
	$flowerSalesPercentageToday = ($salesTodayFlower / $salesToday) * 100;
	$flowerGramsPercentageToday = ($quantitySoldFlower / $quantitySold) * 100;
		
	$extractSalesPercentageToday = ($salesTodayExtract / $salesToday) * 100;
	$extractGramsPercentageToday = ($quantitySoldExtract / $quantitySold) * 100;

		// Calculate total income
		$totalIncome = $donationsToday + $membershipFees + $tillAdditions + $papersAndP + $biscuitsAndTHC + $drinksAndSnacks + $prerolls;
					

		// Calculate estimated till	& club
		// $tillTot = $tillBalance + $donationsToday + $membershipFees + $tillAdditions - $tillExpenses;
		$clubBalance = $tillTot + $bankBalance;
		

		

	pageStart("Report: " . $closingdate, NULL, NULL, "pstatus", "summary admin", $closingdate, $_SESSION['successMessage'], $_SESSION['errorMessage']);
	

	echo <<<EOD
<table style='color: #444; text-align: right; font-family: Tahoma, Verdana, sans-serif; font-size: 14px;' cellpadding='5'>
 <tr>
  <td colspan='5' style='color: #5aa242; text-align: left; font-size: 17px; border-bottom: 2px solid #a80082;'><strong>{$lang['closeday-ataglance']}</strong></td>
 </tr>
 <tr>
  <td style='text-align: left;'>{$lang['closeday-newmembers']}</td>
  <td>$newmembers</td>
  <td></td>
  <td></td>
  <td></td>
  <td></td>
 </tr>
 <tr>
  <td style='text-align: left;'>{$lang['closeday-dispensed']}</td>
  <td>{$expr(number_format($salesToday,0))} &euro;</td>
  <td></td>
  <td>{$expr(number_format($quantitySold,0))} g.</td>
  <td></td>
  <td></td>
 </tr>
 <tr>
  <td style='text-align: left;'><em>{$lang['closeday-owflowers']}</em></td>
  <td>{$expr(number_format($salesTodayFlower,0))} &euro;</td>
  <td>{$expr(number_format($flowerSalesPercentageToday,0))}%</td>
  <td>{$expr(number_format($quantitySoldFlower,0))} g.</td>
  <td>{$expr(number_format($flowerGramsPercentageToday,0))}%</td>
  <td></td>
 </tr>
 <tr>
  <td style='text-align: left;'><em>{$lang['closeday-owextracts']}</em></td>
  <td>{$expr(number_format($salesTodayExtract,0))} &euro;</td>
  <td>{$expr(number_format($extractSalesPercentageToday,0))}%</td>
  <td>{$expr(number_format($quantitySoldExtract,0))} g.</td>
  <td>{$expr(number_format($extractGramsPercentageToday,0))}%</td>
  <td></td>
 </tr>
 <tr rowspan='2'>
  <td colspan='6'>&nbsp;</td>
 </tr>
 <tr>
  <td colspan='5' style='color: #5aa242; text-align: left; font-size: 17px; border-bottom: 2px solid #a80082;'><strong>Member details</strong></td>
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
  <td colspan='6'>&nbsp;</td>
 </tr>
 <tr>
  <td colspan='5' style='color: #5aa242; text-align: left; font-size: 17px; border-bottom: 2px solid #a80082;'><strong>{$lang['closeday-finances']}</strong></td>
 </tr>
 <tr>
  <td style='text-align: left;'>{$lang['closeday-donations']}</td>
  <td>{$expr(number_format($donationsToday,0))} &euro;</td>
  <td></td>
  <td></td>
  <td></td>
  <td></td>
 </tr>
<!-- <tr>
  <td style='text-align: left;'>Unused donations</td>
  <td>{$expr(number_format($unusedDonations,0))} &euro;</td>
  <td></td>
  <td></td>
  <td></td>
  <td></td>
 </tr>-->
 <tr>
  <td style='text-align: left;'>{$lang['closeday-membershipfees']}</td>
  <td>{$expr(number_format($membershipFees,0))} &euro;</td>
  <td></td>
  <td></td>
  <td></td>
  <td></td>
 </tr>
<!-- <tr>
  <td style='text-align: left;'>Debt repaid</td>
  <td>{$expr(number_format($debtRepaid,0))} &euro;</td>
  <td></td>
  <td></td>
  <td></td>
  <td></td>
 </tr>-->
 <tr>
  <td style='text-align: left;'>{$lang['closeday-papers']}</td>
  <td>{$expr(number_format($papersAndP,0))} &euro;</td>
  <td></td>
  <td></td>
  <td></td>
  <td></td>
 </tr>
 <tr>
  <td style='text-align: left;'>{$lang['closeday-biscuits']}</td>
  <td>{$expr(number_format($biscuitsAndTHC,0))} &euro;</td>
  <td></td>
  <td></td>
  <td></td>
  <td></td>
 </tr>
 <tr>
  <td style='text-align: left;'>{$lang['closeday-drinks']}</td>
  <td>{$expr(number_format($drinksAndSnacks,0))} &euro;</td>
  <td></td>
  <td></td>
  <td></td>
  <td></td>
 </tr>
 <tr>
  <td style='text-align: left;'>{$lang['closeday-prerolls']}</td>
  <td>{$expr(number_format($prerolls,0))} &euro;</td>
  <td></td>
  <td></td>
  <td></td>
  <td></td>
 </tr>
 <tr>
  <td style='text-align: left; border-bottom: 1px dashed #ababab;'>{$lang['closeday-othertilladditions']}</td>
  <td style='border-bottom: 1px dashed #ababab;'>{$expr(number_format($otherAdditions,0))} &euro;</td>
  <td></td>
  <td></td>
  <td></td>
  <td></td>
 </tr>
 <tr>
  <td style='text-align: left; border-bottom: 1px solid #ababab;'>{$lang['closeday-totalincome']}</td>
  <td style='border-bottom: 1px solid #ababab;'>{$expr(number_format($totalIncome,0))} &euro;</td>
  <td></td>
  <td></td>
  <td></td>
  <td></td>
 </tr>
 <tr>
  <td style='text-align: left;'>{$lang['closeday-tillexpenses']}</td>
  <td>{$expr(number_format($tillExpenses,0))} &euro;</td>
  <td></td>
  <td></td>
  <td></td>
  <td></td>
 </tr>
 <tr>
  <td style='text-align: left;'><strong>{$lang['closeday-banked']}</strong></td>
  <td><strong>{$expr(number_format($banked,0))} &euro;</strong></td>
  <td></td>
  <td></td>
  <td></td>
  <td></td>
 </tr>
 <tr>
  <td style='text-align: left;'>{$lang['closeday-tillbalance']}</td>
  <td>{$expr(number_format($tillTot,2))} &euro;</td>
  <td></td>
  <td></td>
  <td></td>
  <td></td>
 </tr>
 <tr>
  <td style='text-align: left;'>{$lang['closeday-tilldelta']}</td>
  <td>{$expr(number_format($tillDelta,2))} &euro;</td>
  <td></td>
  <td></td>
  <td></td>
  <td></td>
 </tr>
 <tr>
  <td style='text-align: left;'>{$lang['closeday-bankexpenses']}</td>
  <td>{$expr(number_format($bankExpenses,0))} &euro;</td>
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
 </tr>
 <tr>
  <td style='text-align: left;'>{$lang['closeday-totalclubbalance']}</td>
  <td>{$expr(number_format($clubBalance,2))} &euro;</td>
  <td></td>
  <td></td>
  <td></td>
  <td></td>
 </tr>
 <tr rowspan='2'>
  <td colspan='8'>&nbsp;</td>
 </tr>
</table>
EOD;

		
	$productDetails = <<<EOD
 <tr>
  <td colspan='8'></td>
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
  <td><strong>Status&nbsp;&nbsp;</strong></td>
 </tr>
EOD;


	$prodClosingLookup = "SELECT d.category, d.productid, d.purchaseid, d.soldToday, d.weight, d.weightDelta, d.intStash, d.extStash, d.weightNoShake, d.totalWeight, d.totalNoShake, d.inMenu FROM closingdetails d, closing c WHERE c.closingid = $closingid AND c.closingid = d.closingid ORDER by closingdetailsid ASC";
	
	$resultProducts = mysql_query($prodClosingLookup)
		or handleError($lang['error-prodprices'],"Error loading flower prices from db: " . mysql_error());


		
		while ($product = mysql_fetch_array($resultProducts)) {
			
			$category = $product['category'];
			$productid = $product['productid'];
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
			
	  		// Create totals per category
			if ($category == 1) {
				$flowerTotJar = $flowerTotJar + $weight;
				$flowerTotIntSt = $flowerTotIntSt + $intStash;
				$flowerTotExtSt = $flowerTotExtSt + $extStash;
				$flowerTot = $flowerTotJar + $flowerTotIntSt + $flowerTotExtSt;
				$flowerSoldToday = $flowerSoldToday + $soldToday;
				$flowerTotJarNoShake = $flowerTotJarNoShake + $weightNoShake;
				$flowerTotNoShake = $flowerTotNoShake + $totalNoShake;
				$flowerDelta = $flowerDelta + $weightDelta;
				
			} else if ($category == 2) {
				$extractTotJar = $extractTotJar + $weight;
				$extractTotIntSt = $extractTotIntSt + $intStash;
				$extractTotExtSt = $extractTotExtSt + $extStash;
				$extractTot = $extractTotJar + $extractTotIntSt + $extractTotExtSt;
				$extractSoldToday = $extractSoldToday + $soldToday;
				$extractDelta = $extractDelta + $weightDelta;
			}

			
			// If closedate = today
			$closeLookup = "SELECT closingDate FROM purchases WHERE purchaseid = $purchaseid AND DATE(closingDate) = DATE('$closingdateSQL')";
				
			$resultLookup = mysql_query($closeLookup)
					or handleError($lang['error-prodprices'],"Error loading flower prices from db: " . mysql_error());
					
			if (mysql_num_rows($resultLookup) == 0) {
				
				if ($inMenu == 0) {
					$productStatus = 'Not in menu';
				} else {
					$productStatus = 'In menu';
				}
				
			} else {
				
				$productStatus = 'Closed';
				
			}

					
			$row = mysql_fetch_array($resultLookup);
				$name = $row['name'];
			
			
			
			// Lookup product name, breed2, growtype
			if ($category == 1) {
				
				$productLookup = "SELECT name, breed2 FROM flower WHERE flowerid = $productid";
				
				$resultProduct = mysql_query($productLookup)
					or handleError($lang['error-prodprices'],"Error loading flower prices from db: " . mysql_error());
					
				$row = mysql_fetch_array($resultProduct);
					$name = $row['name'];
					$breed2 = $row['breed2'];
					
				// Look up growtype ID
				$growDetails = "SELECT growType FROM purchases WHERE purchaseid = $purchaseid";
				
				$result = mysql_query($growDetails)
					or handleError($lang['error-growtypeload'],"Error loading growtype: " . mysql_error());
					
				$row = mysql_fetch_array($result);
					$growtypeid = $row['growType'];
					
				// Look up growtype					
				$growDetails = "SELECT growtype FROM growtypes WHERE growtypeid = $growtypeid";
				
				$result = mysql_query($growDetails)
					or handleError($lang['error-growtypeload'],"Error loading growtype: " . mysql_error());
					
				$row = mysql_fetch_array($result);
					$growtype = $row['growtype'];
					
			} else if ($category == 2) {
				
				$productLookup = "SELECT name FROM extract WHERE extractid = $productid";
				
				$resultProduct = mysql_query($productLookup)
					or handleError($lang['error-prodprices'],"Error loading flower prices from db: " . mysql_error());
					
				$row = mysql_fetch_array($resultProduct);
					$name = $row['name'];
					$growtype = '';
					
				// Add Extract header
				if ($extractHeader != 'set') {
					$productDetails .= <<<EOD
 <tr>
  <td colspan='10'></td>
 </tr>
 <tr>
  <td colspan='10' style='color: #a80082; text-align: center; font-size: 14px; border-bottom: 1px solid #dedede; border-top: 1px solid #dedede; margin-top: 10px;'><strong>{$lang['global-extractscaps']}</strong></td>
 </tr>
 <tr>
  <td style='text-align: left;'><strong>{$lang['global-name']}&nbsp;&nbsp;</strong></td>
  <td><strong>{$lang['closeday-jars']}&nbsp;&nbsp;</strong></td>
  <td><strong>{$lang['closeday-intstash']}&nbsp;&nbsp;</strong></td>
  <td><strong>{$lang['closeday-extstash']}&nbsp;&nbsp;</strong></td>
  <td><strong>{$lang['global-total']}&nbsp;&nbsp;</strong></td>
  <td><strong>{$lang['closeday-dispensed']}&nbsp;&nbsp;</strong></td>
  <td><strong>Status&nbsp;&nbsp;</strong></td>
 </tr>
EOD;
					$extractHeader = 'set';
				}			
			}


			
	$productDetails .= <<<EOD
 <tr>
  <td style='text-align: left;'>$name $breed2 $growtype</td>
  <td>{$expr(number_format($weight,0))} g.</td>
  <td>{$expr(number_format($weightNoShake,0))} g.</td>
  <td>{$expr(number_format($intStash,0))} g.</td>
  <td>{$expr(number_format($extStash,0))} g.</td>
  <td><strong>{$expr(number_format($totalWeight,0))} g.</strong></td>
  <td><strong>{$expr(number_format($totalNoShake,0))} g.</strong></td>
  <td>{$expr(number_format($weightDelta,1))} g.</td>
  <td>{$expr(number_format($soldToday,1))} g.</td>
  <td>{$productStatus}</td>
 </tr>		
EOD;
			
	}
	
		$productOverview = <<<EOD
<table style='color: #444; text-align: right; font-family: Tahoma, Verdana, sans-serif; font-size: 14px;' cellpadding='5'>
 <tr>
  <td colspan='9' style='color: #5aa242; text-align: left; font-size: 17px; border-bottom: 2px solid #a80082;'><strong>{$lang['closeday-productoverview']}</strong></td>
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
  <td>{$expr(number_format($flowerTotJar,0))} g.</td>
  <td>{$expr(number_format($flowerTotJarNoShake,0))} g.</td>
  <td>{$expr(number_format($flowerTotIntSt,0))} g.</td>
  <td>{$expr(number_format($flowerTotExtSt,0))} g.</td>
  <td><strong>{$expr(number_format($flowerTot,0))} g.</strong></td>
  <td><strong>{$expr(number_format($flowerTotNoShake,0))} g.</strong></td>
  <td>{$expr(number_format($flowerDelta,0))} g.</td>
  <td>{$expr(number_format($flowerSoldToday,0))} g.</td>
 </tr>
 <tr>
  <td style='text-align: left;'>{$lang['global-extracts']}</td>
  <td>{$expr(number_format($extractTotJar,0))} g.</td>
  <td></td>
  <td>{$expr(number_format($extractTotIntSt,0))} g.</td>
  <td>{$expr(number_format($extractTotExtSt,0))} g.</td>
  <td><strong>{$expr(number_format($extractTot,0))} g.</strong></td>
  <td></td>
  <td>{$expr(number_format($extractDelta,0))} g.</td>
  <td>{$expr(number_format($extractSoldToday,0))} g.</td>
 </tr>
EOD;



		// Query to look up expenses
	$selectExpenses = "SELECT expenseid, registertime, userid, expensetype, expense, moneysource, other, amount, shop, comment, receipt, expensecategory FROM expenses WHERE DATE(registertime) = DATE('$closingdateSQL') ORDER by registertime DESC";

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
	$formattedDate = date("d M H:i", strtotime($expense['registertime']));
	
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
	echo $productDetails;
    echo "</table>";
    echo $expenseDetails;
    echo "</table>";

}

		displayFooter();