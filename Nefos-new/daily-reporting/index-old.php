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

		$selectCloseDetails = "SELECT quantitySold, soldtoday, closingbalance, moneytaken, cashintill, bankBalance, newmembers, expenses, membershipFees, tillDelta, bankExpenses, income, paraphernalia, biscuits, drinksandsnacks, prerolls, otherAdditions,  from closing WHERE closingid = $closingid";
		
		$closingResult = mysql_query($selectCloseDetails)
		or handleError($lang['error-loadnewmembers'],"Error loading sale from db: " . mysql_error());
	
		$row = mysql_fetch_array($closingResult);
			$quantitySold = $row['quantitySold'];
			$soldtoday = $row['soldtoday'];
			// $clubBalance = $row['closingbalance']; --- not saving properly in closing db. BUG! FIX!
			$banked = $row['moneytaken'];
			$tillTot = $row['cashintill'];
			$bankBalance = $row['bankBalance'];
			$newmembers = $row['newmembers'];
			$tillExpenses = $row['expenses'];
			$membershipFees = $row['membershipFees'];
			$tillDelta = $row['tillDelta'];
			$bankExpenses = $row['bankExpenses'];
			// $totalIncome = $row['income']; --- not saving properly in closing db. BUG! FIX!
			$papersAndP = $row['paraphernalia'];
			$biscuitsAndTHC = $row['biscuits'];
			$drinksAndSnacks = $row['drinksandsnacks'];
			$prerolls = $row['prerolls'];
			$tillAdditions = $row['otherAdditions'];
			
		
	
	// Look up renewed members		
	$selectMembers = "SELECT COUNT(m.paymentid) FROM memberpayments m, users u WHERE u.user_id = m.userid AND DATE(m.paymentdate) = DATE('$closingdateSQL') AND DATE(u.registeredSince) < DATE('$closingdateSQL')";

	$result = mysql_query($selectMembers)
		or handleError($lang['error-loadsales'],"Error loading sale from db: " . mysql_error());
		
	$row = mysql_fetch_array($result);
		$renewedMembers = $row['COUNT(m.paymentid)'];
		
		
	// Look up todays dispenses
	$selectSales = "SELECT SUM(amount), SUM(quantity) from sales WHERE DATE(saletime) = DATE('$closingdateSQL')";

	$result = mysql_query($selectSales)
		or handleError($lang['error-dispenseload'],"Error loading sale from db: " . mysql_error());
		
	$row = mysql_fetch_array($result);
		$salesToday = $row['SUM(amount)'];
		$quantitySold = $row['SUM(quantity)'];
		
	// Look up todays dispenses by category 1
	$selectSalesFlower = "SELECT SUM(d.amount), SUM(d.quantity) from sales s, salesdetails d WHERE s.saleid = d.saleid AND DATE(s.saletime) = DATE('$closingdateSQL') AND d.category = 1";

	$resultFlower = mysql_query($selectSalesFlower)
		or handleError($lang['error-dispenseload'],"Error loading sale from db: " . mysql_error());
	
	$row = mysql_fetch_array($resultFlower);
		$salesTodayFlower = $row['SUM(d.amount)'];
		$quantitySoldFlower = $row['SUM(d.quantity)'];
		
	$flowerSalesPercentageToday = ($salesTodayFlower / $salesToday) * 100;
	$flowerGramsPercentageToday = ($quantitySoldFlower / $quantitySold) * 100;
	
	// Look up todays dispenses by category 2
	$selectSalesExtract = "SELECT SUM(d.amount), SUM(d.quantity) from sales s, salesdetails d WHERE s.saleid = d.saleid AND DATE(s.saletime) = DATE('$closingdateSQL') AND d.category = 2";

	$resultExtract = mysql_query($selectSalesExtract)
		or handleError($lang['error-dispenseload'],"Error loading sale from db: " . mysql_error());
	
	$row = mysql_fetch_array($resultExtract);
		$salesTodayExtract = $row['SUM(d.amount)'];
		$quantitySoldExtract = $row['SUM(d.quantity)'];
		
	$extractSalesPercentageToday = ($salesTodayExtract / $salesToday) * 100;
	$extractGramsPercentageToday = ($quantitySoldExtract / $quantitySold) * 100;

	// Look up todays donations
	$selectDonations = "SELECT SUM(amount) from donations WHERE DATE(donationTime) = DATE('$closingdateSQL')";

	$donationResult = mysql_query($selectDonations)
		or handleError($lang['error-donationload'],"Error loading donations from db: " . mysql_error());
		
	$row = mysql_fetch_array($donationResult);
		$donationsToday = $row['SUM(amount)'];
		
/*	// Look up today's membership fees
	$selectMembershipFees = "SELECT SUM(amountPaid) FROM memberpayments WHERE DATE(paymentdate) = DATE('$closingdateSQL')";
				
	$result = mysql_query($selectMembershipFees)
		or handleError($lang['error-expensesload'],"Error loading expense from db: " . mysql_error());
			
	$row = mysql_fetch_array($result);
		$membershipFees = $row['SUM(amountPaid)'];
*/		
		// Calculate total income
		$totalIncome = $donationsToday + $membershipFees + $tillAdditions + $papersAndP + $biscuitsAndTHC + $drinksAndSnacks + $prerolls;
					

		// Calculate estimated till	& club
		// $tillTot = $tillBalance + $donationsToday + $membershipFees + $tillAdditions - $tillExpenses;
		$clubBalance = $tillTot + $bankBalance;
		

		

	pageStart("Reporting", NULL, NULL, "pstatus", "summary admin", $closingdate, $_SESSION['successMessage'], $_SESSION['errorMessage']);
	

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
  <td><strong>$banked &euro;</strong></td>
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

	Select all products from closingdetails
	For each product, get:
	jars (weight)
	additions 
	takeouts
	intstash
	extstash
	dispensed
*/


	$closingLookup = "SELECT d.weight FROM closingdetails d, closing c WHERE DATE(closingtime) = DATE_ADD(DATE(NOW()), INTERVAL -1 DAY) AND category = $category AND purchaseid = $purchaseid and d.closingid = c.closingid";
	

	$selectProducts = "SELECT '1' AS category, g.flowerid AS productid, g.name AS name, p.purchaseid AS purchaseid FROM flower g, purchases p WHERE p.category = 1 AND p.productid = g.flowerid AND p.closedAt IS NULL UNION ALL SELECT '2' AS category, h.extractid AS productid, h.name AS name, p.purchaseid AS purchaseid FROM extract h, purchases p WHERE p.category = 2 AND p.productid = h.extractid AND p.closedAt IS NULL;";
	
	$resultProducts = mysql_query($selectProducts)
		or handleError($lang['error-prodprices'],"Error loading flower prices from db: " . mysql_error());

		
	$productDetails = <<<EOD
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
 </tr>
EOD;

		
		while ($product = mysql_fetch_array($resultProducts)) {
			
			
			$category = $product['category'];
			$productid = $product['productid'];
			$name = $product['name'];
			$purchaseid = $product['purchaseid'];
			
			// Query to look up today's opening weight
			$openingLookup = "SELECT d.weight FROM openingdetails d, opening o WHERE DATE(openingtime) = DATE(NOW()) AND d.openingid = o.openingid AND purchaseid = $purchaseid";
			
			$result = mysql_query($openingLookup)
				or handleError($lang['error-loadprodclosedetails'],"Error loading closing from db: " . mysql_error());
		
			$row = mysql_fetch_array($result);
				$openingWeight = $row['weight'];
				
				
			// Look up todays dispenses
			$selectSales = "SELECT SUM(d.quantity) FROM salesdetails d, sales s WHERE DATE(s.saletime) = DATE(NOW()) AND d.saleid = s.saleid AND d.purchaseid = $purchaseid";

			$result = mysql_query($selectSales)
				or handleError($lang['error-loadsales'],"Error loading sale from db: " . mysql_error());
		
			$row = mysql_fetch_array($result);
				$soldToday = $row['SUM(d.quantity)'];

			// Look up additions and removals
			$selectAdditions = "SELECT SUM(quantity) FROM productmovements WHERE purchaseid = $purchaseid AND type = 1 AND DATE(movementtime) = DATE(NOW()) AND movementTypeid < 17";
			$selectRemovals = "SELECT SUM(quantity) FROM productmovements WHERE purchaseid = $purchaseid AND type = 2 AND DATE(movementtime) = DATE(NOW()) AND movementTypeid < 17";
		
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
			$inStashInt = number_format($inStashInt,0);
		
		
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
			$inStashExt = number_format($inStashExt,0);
			
			$weightTotal = $jarWeight + $inStashInt + $inStashExt;
			
			
	  		// Create totals per category
			if ($category == 1) {
				$flowerTotJar = $flowerTotJar + $jarWeight;
				$flowerTotIntSt = $flowerTotIntSt + $inStashInt;
				$flowerTotExtSt = $flowerTotExtSt + $inStashExt;
				$flowerTot = $flowerTotJar + $flowerTotIntSt + $flowerTotExtSt;
			} else if ($category == 2) {
				$extractTotJar = $extractTotJar + $jarWeight;
				$extractTotIntSt = $extractTotIntSt + $inStashInt;
				$extractTotExtSt = $extractTotExtSt + $inStashExt;
				$extractTot = $extractTotJar + $extractTotIntSt + $extractTotExtSt;
				
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
 </tr>
EOD;
				$extractHeader = 'set';
				}
			}
			
	  
		$productDetails .= <<<EOD
 <tr>
  <td style='text-align: left;'>{$name}</td>
  <td>{$expr(number_format($jarWeight,0))} g.</td>
  <td>{$expr(number_format($inStashInt,0))} g.</td>
  <td>{$expr(number_format($inStashExt,0))} g.</td>
  <td><strong>{$expr(number_format($weightTotal,0))} g.</strong></td>
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
  <td><strong>{$lang['closeday-intstash']}&nbsp;&nbsp;</strong></td>
  <td><strong>{$lang['closeday-extstash']}&nbsp;&nbsp;</strong></td>
  <td><strong>{$lang['global-total']}&nbsp;&nbsp;</strong></td>
 </tr>
 <tr>
  <td style='text-align: left;'>{$lang['global-flowers']}</td>
  <td>{$expr(number_format($flowerTotJar,0))} g.</td>
  <td>{$expr(number_format($flowerTotIntSt,0))} g.</td>
  <td>{$expr(number_format($flowerTotExtSt,0))} g.</td>
  <td><strong>{$expr(number_format($flowerTot,0))} g.</strong></td>
 </tr>
 <tr>
  <td style='text-align: left;'>{$lang['global-extracts']}</td>
  <td>{$expr(number_format($extractTotJar,0))} g.</td>
  <td>{$expr(number_format($extractTotIntSt,0))} g.</td>
  <td>{$expr(number_format($extractTotExtSt,0))} g.</td>
  <td><strong>{$expr(number_format($extractTot,0))} g.</strong></td>
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