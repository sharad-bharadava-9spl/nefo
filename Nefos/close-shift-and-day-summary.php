<?php
	
	require_once 'cOnfig/connection.php';
	require_once 'cOnfig/view-closing.php';
	require_once 'cOnfig/authenticate.php';
	require_once 'cOnfig/languages/common.php';
	
	session_start();
	$accessLevel = '3';
	
	// Authenticate & authorize
	authorizeUser($accessLevel);
	
	getSettings();
	
	if (isset($_GET['cid'])) {
		$closingid = $_GET['csid'];
		$openingid = $_GET['osid'];
		$dayclosingid = $_GET['cid'];
		$dayopeningid = $_GET['oid'];
	} else {
		echo $lang['closing-not-specified'];
		exit();
	}
	
	$openingtime = $_SESSION['openingtime'];
	$dayopeningtime = $_SESSION['dayopeningtime'];
	
	$dayOpeningtimeView = date('d-m-Y H:i', strtotime($dayopeningtime . "+$offsetSec seconds"));
	$shiftOpeningtimeView = date('d-m-Y H:i', strtotime($openingtime . "+$offsetSec seconds"));
	
	// Check first! And ask if it's in progress
	$checkOpening = "SELECT dayClosed, dayClosedBy FROM opening WHERE openingid = $dayopeningid";

	$result = mysql_query($checkOpening)
		or handleError($lang['error-savedata'],"Error inserting flower: " . mysql_error());
		
	$row = mysql_fetch_array($result);
		$dayClosed = $row['dayClosed'];
		$dayClosedBy = $row['dayClosedBy'];
		
	if ($dayClosed == '1' && (!isset($_GET['redo']))) {
		
			$closingOperator = getOperator($dayClosedBy);
			
			pageStart($lang['close-shift-and-day'], NULL, $validationScript, "pcloseday", "step2", $lang['close-shift'], $_SESSION['successMessage'], $_SESSION['errorMessage']);
	
	echo  <<<EOD
	<div id="scriptMsg">
	 <div class='error'>
			{$lang['summary-inprogress-1']} $closingOperator<br /><a href='?redo&oid=$dayopeningid&cid=$dayclosingid&csid=$closingid&osid=$openingid' class='yellow'> {$lang['summary-inprogress-2']}
	 </div>
	</div>
	
EOD;
	exit();
	
	}
	
	// Write to DB Opening tables: dayclosing is in progress
	$updateOpening = "UPDATE opening SET dayClosed = 1, dayClosedBy = {$_SESSION['user_id']} WHERE openingid = $dayopeningid";
	
	mysql_query($updateOpening)
		or handleError($lang['error-savedata'],"Error inserting flower: " . mysql_error());
		
	$updateOpening = "UPDATE shiftopen SET shiftClosed = 1, shiftClosedBy = {$_SESSION['user_id']} WHERE openingid = $openingid";
	
	mysql_query($updateOpening)
		or handleError($lang['error-savedata'],"Error inserting flower: " . mysql_error());

			
	// Select closing details
	$selectClosingData = "SELECT shiftStart, closingtime, quantitySold, soldtoday, unitsSold, closingbalance, moneytaken, takenduringday, cashintill, bankBalance, newmembers, closedby, tillComment, expenses, membershipFees, estimatedTill, tillDelta, bankExpenses, prodOpening, prodStock, stockDelta, prodStockFlower, prodStockExtract, income, stockDeltaFlower, stockDeltaExtract, donations, bankDonations, renewedMembers, bannedMembers, deletedMembers, expiredMembers, totalMembers, activeMembers, intStash, extStash, totalWeight, totalNoShake, flowerintStash, flowerextStash, flowerweightNoShake, flowertotalWeight, flowertotalNoShake, extractintStash, extractextStash, extracttotalWeight, flowerDispensed, extractDispensed, soldTodayFlower, soldTodayExtract, membershipfeesBank, soldtodayBar, unitsSoldBar, openingBalance, openingBalanceBank, totCredit FROM shiftclose WHERE closingid = $closingid";
	
	$closingResult = mysql_query($selectClosingData)
		or handleError($lang['error-loadsales'],"Error loading sale from db: " . mysql_error());
		
	$row = mysql_fetch_array($closingResult);
		$openingtime = $row['shiftStart'];
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
		

	$confirmLeave = <<<EOD
    $(document).ready(function() {	    
document.querySelector('button').addEventListener("click", function(){
    window.btn_clicked = true;      //set btn_clicked to true
});

$(window).bind('beforeunload', function(){
    if(!window.btn_clicked){
        return "{$lang['closeday-leavepage']}";
    }
});
  }); // end ready
EOD;

		pageStart($lang['close-shift-and-day'], NULL, $confirmLeave, "pcloseday", "step7", $lang['close-shift-and-day'], $_SESSION['successMessage'], $_SESSION['errorMessage']);
	
	// Re-generate pageheader with current closing time
	$closingtime = date('Y-m-d H:i:s');
	$_SESSION['closingtime'] = $closingtime;
		
	$openingtimeView = date('d-m-Y H:i', strtotime($openingtime . "+$offsetSec seconds"));
	$closingtimeView = date('d-m-Y H:i', strtotime($closingtime . "+$offsetSec seconds"));
	
	// Determine shift duration
	$datetime1 = new DateTime($openingtime);
	$datetime2 = new DateTime($closingtime);
	$interval = $datetime1->diff($datetime2);
	
	$noOfMonths = $interval->format('%m');
	$noOfDays = $interval->format('%d');
	$noOfHours = $interval->format('%h');
	$noOfMins = $interval->format('%i');
	
	if ($noOfMonths == 0) {
		
		if ($noOfDays == 0) {
			if ($noOfHours > 1) {
				$shiftDuration = "$noOfHours {$lang['hoursLC']} $noOfMins {$lang['minLC']}";
			} else {
				$shiftDuration = "$noOfHours {$lang['hourLC']} $noOfMins {$lang['minLC']}";
			}
		} else if ($noOfDays == 1) {
			if ($noOfHours > 1) {
				$shiftDuration = "$noOfDays {$lang['dayLC']} $noOfHours {$lang['hoursLC']} $noOfMins {$lang['minLC']}";
			} else {
				$shiftDuration = "$noOfDays {$lang['dayLC']} $noOfHours {$lang['hourLC']} $noOfMins {$lang['minLC']}";
			}
		} else {
			if ($noOfHours > 1) {
				$shiftDuration = "$noOfDays {$lang['daysLC']} $noOfHours {$lang['hoursLC']} $noOfMins {$lang['minLC']}";
			} else {
				$shiftDuration = "$noOfDays {$lang['daysLC']} $noOfHours {$lang['hourLC']} $noOfMins {$lang['minLC']}";
			}
		}
		
	} else if ($noOfMonths == 1) {
		
		if ($noOfDays == 0) {
			if ($noOfHours > 1) {
				$shiftDuration = "$noOfMonths {$lang['monthLC']} $noOfHours {$lang['hoursLC']} $noOfMins {$lang['minLC']}";
			} else {
				$shiftDuration = "$noOfMonths {$lang['monthLC']} $noOfHours {$lang['hourLC']} $noOfMins {$lang['minLC']}";
			}
		} else if ($noOfDays == 1) {
			if ($noOfHours > 1) {
				$shiftDuration = "$noOfMonths {$lang['monthLC']} $noOfDays {$lang['dayLC']} $noOfHours {$lang['hoursLC']} $noOfMins {$lang['minLC']}";
			} else {
				$shiftDuration = "$noOfMonths {$lang['monthLC']} $noOfDays {$lang['dayLC']} $noOfHours {$lang['hourLC']} $noOfMins {$lang['minLC']}";
			}
		} else {
			if ($noOfHours > 1) {
				$shiftDuration = "$noOfMonths {$lang['monthLC']} $noOfDays {$lang['daysLC']} $noOfHours {$lang['hoursLC']} $noOfMins {$lang['minLC']}";
			} else {
				$shiftDuration = "$noOfMonths {$lang['monthLC']} $noOfDays {$lang['daysLC']} $noOfHours {$lang['hourLC']} $noOfMins {$lang['minLC']}";
			}
		}
		
	} else {
		
		if ($noOfDays == 0) {
			if ($noOfHours > 1) {
				$shiftDuration = "$noOfMonths {$lang['monthsLC']} $noOfHours {$lang['hoursLC']} $noOfMins {$lang['minLC']}";
			} else {
				$shiftDuration = "$noOfMonths {$lang['monthsLC']} $noOfHours {$lang['hourLC']} $noOfMins {$lang['minLC']}";
			}
		} else if ($noOfDays == 1) {
			if ($noOfHours > 1) {
				$shiftDuration = "$noOfMonths {$lang['monthsLC']} $noOfDays {$lang['dayLC']} $noOfHours {$lang['hoursLC']} $noOfMins {$lang['minLC']}";
			} else {
				$shiftDuration = "$noOfMonths {$lang['monthsLC']} $noOfDays {$lang['dayLC']} $noOfHours {$lang['hourLC']} $noOfMins {$lang['minLC']}";
			}
		} else {
			if ($noOfHours > 1) {
				$shiftDuration = "$noOfMonths {$lang['monthsLC']} $noOfDays {$lang['daysLC']} $noOfHours {$lang['hoursLC']} $noOfMins {$lang['minLC']}";
			} else {
				$shiftDuration = "$noOfMonths {$lang['monthsLC']} $noOfDays {$lang['daysLC']} $noOfHours {$lang['hourLC']} $noOfMins {$lang['minLC']}";
			}
		}
		
	}

	// Determine day duration
	$datetime1 = new DateTime($dayopeningtime);
	$datetime2 = new DateTime($closingtime);
	$interval = $datetime1->diff($datetime2);
	
	$noOfMonths = $interval->format('%m');
	$noOfDays = $interval->format('%d');
	$noOfHours = $interval->format('%h');
	$noOfMins = $interval->format('%i');
	
	if ($noOfMonths == 0) {
		
		if ($noOfDays == 0) {
			if ($noOfHours > 1) {
				$shiftDurationDay = "$noOfHours {$lang['hoursLC']} $noOfMins {$lang['minLC']}";
			} else {
				$shiftDurationDay = "$noOfHours {$lang['hourLC']} $noOfMins {$lang['minLC']}";
			}
		} else if ($noOfDays == 1) {
			if ($noOfHours > 1) {
				$shiftDurationDay = "$noOfDays {$lang['dayLC']} $noOfHours {$lang['hoursLC']} $noOfMins {$lang['minLC']}";
			} else {
				$shiftDurationDay = "$noOfDays {$lang['dayLC']} $noOfHours {$lang['hourLC']} $noOfMins {$lang['minLC']}";
			}
		} else {
			if ($noOfHours > 1) {
				$shiftDurationDay = "$noOfDays {$lang['daysLC']} $noOfHours {$lang['hoursLC']} $noOfMins {$lang['minLC']}";
			} else {
				$shiftDurationDay = "$noOfDays {$lang['daysLC']} $noOfHours {$lang['hourLC']} $noOfMins {$lang['minLC']}";
			}
		}
		
	} else if ($noOfMonths == 1) {
		
		if ($noOfDays == 0) {
			if ($noOfHours > 1) {
				$shiftDurationDay = "$noOfMonths {$lang['monthLC']} $noOfHours {$lang['hoursLC']} $noOfMins {$lang['minLC']}";
			} else {
				$shiftDurationDay = "$noOfMonths {$lang['monthLC']} $noOfHours {$lang['hourLC']} $noOfMins {$lang['minLC']}";
			}
		} else if ($noOfDays == 1) {
			if ($noOfHours > 1) {
				$shiftDurationDay = "$noOfMonths {$lang['monthLC']} $noOfDays {$lang['dayLC']} $noOfHours {$lang['hoursLC']} $noOfMins {$lang['minLC']}";
			} else {
				$shiftDurationDay = "$noOfMonths {$lang['monthLC']} $noOfDays {$lang['dayLC']} $noOfHours {$lang['hourLC']} $noOfMins {$lang['minLC']}";
			}
		} else {
			if ($noOfHours > 1) {
				$shiftDurationDay = "$noOfMonths {$lang['monthLC']} $noOfDays {$lang['daysLC']} $noOfHours {$lang['hoursLC']} $noOfMins {$lang['minLC']}";
			} else {
				$shiftDurationDay = "$noOfMonths {$lang['monthLC']} $noOfDays {$lang['daysLC']} $noOfHours {$lang['hourLC']} $noOfMins {$lang['minLC']}";
			}
		}
		
	} else {
		
		if ($noOfDays == 0) {
			if ($noOfHours > 1) {
				$shiftDurationDay = "$noOfMonths {$lang['monthsLC']} $noOfHours {$lang['hoursLC']} $noOfMins {$lang['minLC']}";
			} else {
				$shiftDurationDay = "$noOfMonths {$lang['monthsLC']} $noOfHours {$lang['hourLC']} $noOfMins {$lang['minLC']}";
			}
		} else if ($noOfDays == 1) {
			if ($noOfHours > 1) {
				$shiftDurationDay = "$noOfMonths {$lang['monthsLC']} $noOfDays {$lang['dayLC']} $noOfHours {$lang['hoursLC']} $noOfMins {$lang['minLC']}";
			} else {
				$shiftDurationDay = "$noOfMonths {$lang['monthsLC']} $noOfDays {$lang['dayLC']} $noOfHours {$lang['hourLC']} $noOfMins {$lang['minLC']}";
			}
		} else {
			if ($noOfHours > 1) {
				$shiftDurationDay = "$noOfMonths {$lang['monthsLC']} $noOfDays {$lang['daysLC']} $noOfHours {$lang['hoursLC']} $noOfMins {$lang['minLC']}";
			} else {
				$shiftDurationDay = "$noOfMonths {$lang['monthsLC']} $noOfDays {$lang['daysLC']} $noOfHours {$lang['hourLC']} $noOfMins {$lang['minLC']}";
			}
		}
		
	}
	
		$pageHeader = <<<EOD
<div class="textInset">
 <center><strong>{$lang['close-day-details']}</strong></center><br />
 <table>
  <tr>
   <td style='text-align: left;'>{$lang['day-opened']}:</td>
   <td style='text-align: left;'>{$dayOpeningtimeView}</td>
  </tr>
  <tr>
   <td style='text-align: left;'>{$lang['shift-opened']}:</td>
   <td style='text-align: left;'>{$shiftOpeningtimeView}</td>
  </tr>
  <tr>
   <td style='text-align: left;'>{$lang['day-closed']}:</td>
   <td style='text-align: left;'>{$closingtimeView}</td>
  </tr>
  <tr>
   <td colspan='2'><br /></td>
  </tr>
  <tr>
   <td style='text-align: left;'>{$lang['shift-duration']}:</td>
   <td style='text-align: left;'>{$shiftDuration}</td>
  </tr>
  <tr>
   <td style='text-align: left;'>{$lang['day-duration']}:</td>
   <td style='text-align: left;'>{$shiftDurationDay}</td>
  </tr>
 </table>
</div>
EOD;

	
		$_SESSION['pageHeader'] = $pageHeader;
		
		echo $pageHeader;
		
	// Look up todays total sales, then by category
	
	// Look up &euro;, g, units
	$selectSales = "SELECT SUM(quantitySold), SUM(soldToday), SUM(unitsSold) FROM shiftclose WHERE closingid = $closingid";

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
  <td>{$expr(number_format($salesToday,0))} &euro;</td>
  <td></td>
  <td>{$expr(number_format($quantitySold,0))} g.</td>
  <td></td>
  <td>{$expr(number_format($unitsSold,0))} u.</td>
  <td></td>
 </tr>
 <tr>
  <td style='text-align: left;'><em>{$lang['closeday-owflowers']}</em></td>
  <td>{$expr(number_format($flowerSalesToday,0))} &euro;</td>
  <td>{$expr(number_format($flowerSalesPercentageToday,0))}%</td>
  <td>{$expr(number_format($flowerGramsToday,0))} g.</td>
  <td>{$expr(number_format($flowerGramsPercentageToday,0))}%</td>
  <td></td>
 </tr>
 <tr>
  <td style='text-align: left;'><em>{$lang['closeday-owextracts']}</em></td>
  <td>{$expr(number_format($extractSalesToday,0))} &euro;</td>
  <td>{$expr(number_format($extractSalesPercentageToday,0))}%</td>
  <td>{$expr(number_format($extractGramsToday,0))} g.</td>
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
		$customProducts .= " UNION ALL SELECT '$categoryid' AS category, pr.name, '' AS breed2, c.purchaseid, c.soldToday, c.weight, c.weightDelta, c.intStash, c.extStash, c.weightNoShake, c.totalWeight, c.totalNoShake, c.inMenu, c.specificComment FROM products pr, shiftclosedetails c WHERE c.category = $categoryid AND c.productid = pr.productid AND c.closingid = $closingid";
				
		
		// Look up sales in this cat
		$selectSalesOthers = "SELECT SUM(d.amount), SUM(d.quantity) from sales s, salesdetails d WHERE s.saleid = d.saleid AND DATE(s.saletime) BETWEEN '$openingtime' AND '$closingtime' AND d.category = $categoryid";
	
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
  <td>{$expr(number_format($salesTodayOthers,0))} &euro;</td>
  <td>{$expr(number_format($othersSalesPercentageToday,0))}%</td>
  <td></td>
  <td></td>
  <td>{$expr(number_format($quantitySoldOthers,0))} u.</td>
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
  <td>{$expr(number_format($barSales,0))} &euro;</td>
  <td></td>
  <td></td>
  <td></td>
  <td>{$expr(number_format($barUnits,0))} u.</td>
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
  <td colspan='10' style='color: #5aa242; text-align: left; font-size: 17px; border-bottom: 2px solid #a80082;'><strong>{$lang['closeday-finances']}</strong></td>
 </tr>
 <tr>
  <td style='text-align: left;'>{$lang['closeday-donations-till']}</td>
  <td>{$expr(number_format($donations,0))} &euro;</td>
  <td></td>
  <td style='vertical-align: bottom; text-align: left; font-size: 14px;'><strong>{$lang['till-calculation']}</strong></td>
  <td></td>
  <td></td>
  <td style='vertical-align: bottom; text-align: left; font-size: 14px;'><strong>{$lang['bank-calculation']}</strong></td>
  <td></td>
 </tr>
 <tr>
  <td style='text-align: left;'>{$lang['closeday-donations-bank']}</td>
  <td>{$expr(number_format($bankDonations,0))} &euro;</td>
  <td></td>
  <td style='text-align: left;'>{$lang['closeday-tillatopening']}:</td>
  <td>{$expr(number_format($openingBalance,2))} &euro;</td>
  <td></td>
  <td style='text-align: left;'>{$lang['closeday-openingtoday']}:</td>
  <td>{$expr(number_format($openingBalanceBank,2))} &euro;</td>
 </tr>
 <tr>
  <td style='text-align: left;'>{$lang['closeday-membershipfees-till']}</td>
  <td>{$expr(number_format($membershipFees,0))} &euro;</td>
  <td></td>
  <td style='text-align: left;'>+ {$lang['closeday-membershipfees-till']}:</td>
  <td>{$expr(number_format($membershipFees,2))} &euro;</td>
  <td></td>
  <td style='text-align: left;'>+ {$lang['closeday-membershipfees-bank']}:</td>
  <td>{$expr(number_format($membershipfeesBank,2))} &euro;</td>
 </tr>
 <tr>
  <td style='text-align: left; border-bottom: 1px dashed #ababab;'>{$lang['closeday-membershipfees-bank']}</td>
  <td style='border-bottom: 1px dashed #ababab;'>{$expr(number_format($membershipfeesBank,0))} &euro;</td>
  <td></td>
  <td style='text-align: left;'>+ {$lang['global-donations']}:</td>
  <td>{$expr(number_format($donations,2))} &euro;</td>
  <td></td>
  <td style='text-align: left;'>+ {$lang['global-donations']}:</td>
  <td>{$expr(number_format($bankDonations,2))} &euro;</td>
 </tr>
 <tr>
  <td style='text-align: left; border-bottom: 1px solid #ababab;'>{$lang['closeday-totalincome']}</td>
  <td style='border-bottom: 1px solid #ababab;'>{$expr(number_format($income,0))} &euro;</td>
  <td></td>
  <td style='text-align: left;'>- {$lang['closeday-tillexpenses']}:</td>
  <td>{$expr(number_format($expenses,2))} &euro;</td>
  <td></td>
  <td style='text-align: left;'>+ {$lang['banked-now']}:</td>
  <td>{$expr(number_format($moneytaken,2))} &euro;</td>
 </tr>
 <tr>
  <td style='text-align: left;'>{$lang['closeday-tillexpenses']}</td>
  <td>{$expr(number_format($expenses,0))} &euro;</td>
  <td></td>
  <td style='text-align: left; border-bottom: 1px dashed #ababab;'>- {$lang['closeday-moneybanked']}:</td>
  <td style='text-align: right; border-bottom: 1px dashed #ababab;'>{$expr(number_format($moneytaken,2))} &euro;</td>
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
  <td>{$expr(number_format($bankExpenses,0))} &euro;</td>
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
EOD;

				
		$mailtoProductResponsible = <<<EOD
<table style='color: #444; text-align: right; font-family: Tahoma, Verdana, sans-serif; font-size: 14px;'>
 <tr>
  <td colspan='11' style='color: #5aa242; text-align: left; font-size: 17px; border-bottom: 2px solid #a80082;'><strong>{$lang['closeday-productoverview']}</strong></td>
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
  <td>{$expr(number_format($prodStockFlower,0))} g.</td>
  <td>{$expr(number_format($flowerweightNoShake,0))} g.</td>
  <td>{$expr(number_format($flowerintStash,0))} g.</td>
  <td>{$expr(number_format($flowerextStash,0))} g.</td>
  <td><strong>{$expr(number_format($flowertotalWeight,0))} g.</strong></td>
  <td><strong>{$expr(number_format($flowertotalNoShake,0))} g.</strong></td>
  <td>{$expr(number_format($stockDeltaFlower,0))} g.</td>
  <td>{$expr(number_format($flowerDispensed,0))} g.</td>
 </tr>
 <tr>
  <td style='text-align: left;'>{$lang['global-extracts']}</td>
  <td>{$expr(number_format($prodStockExtract,0))} g.</td>
  <td></td>
  <td>{$expr(number_format($extractintStash,0))} g.</td>
  <td>{$expr(number_format($extractextStash,0))} g.</td>
  <td><strong>{$expr(number_format($extracttotalWeight,0))} g.</strong></td>
  <td></td>
  <td>{$expr(number_format($stockDeltaExtract,0))} g.</td>
  <td>{$expr(number_format($extractDispensed,0))} g.</td>
 </tr>
 <tr rowspan='2'>
  <td colspan='11'>&nbsp;</td>
 </tr>
 <tr>
  <td colspan='11' style='color: #5aa242; text-align: left; font-size: 17px; border-bottom: 2px solid #a80082;'><strong>{$lang['closeday-productdetails']}</strong></td>
 </tr>
 <tr>
  <td colspan='11' style='color: #a80082; text-align: center; font-size: 14px; border-bottom: 1px solid #dedede;'><strong>{$lang['global-flowerscaps']}</strong></td>
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

		
		$defaultProducts = "SELECT category, f.name, f.breed2, c.purchaseid, c.soldToday, c.weight, c.weightDelta, c.intStash, c.extStash, c.weightNoShake, c.totalWeight, c.totalNoShake, c.inMenu, c.specificComment FROM flower f, shiftclosedetails c WHERE c.category = '1' AND c.productid = f.flowerid AND c.closingid = $closingid UNION ALL SELECT category, e.name, '' AS breed2, c.purchaseid, c.soldToday, c.weight, c.weightDelta, c.intStash, c.extStash, c.weightNoShake, c.totalWeight, c.totalNoShake, c.inMenu, c.specificComment FROM extract e, shiftclosedetails c WHERE c.category = '2' AND c.productid = e.extractid AND c.closingid = $closingid";
		
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
  <td colspan='11' style='text-align: left;'>{$lang['global-comment']}: <em>$specificComment</em></td>
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
  <td colspan='11'></td>
 </tr>
 <tr>
  <td colspan='11' style='color: #a80082; text-align: center; font-size: 14px; border-top: 1px solid #dedede; border-bottom: 1px solid #dedede;'><strong>{$lang['global-extractscaps']}</strong></td>
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
  <td><strong>Status&nbsp;&nbsp;</strong></td>
 </tr>
 <tr>
  <td style='text-align: left;'>$name</td>
  <td>{$expr(number_format($weight,0))} g.</td>
  <td></td>
  <td>{$expr(number_format($intStash,0))} g.</td>
  <td>{$expr(number_format($extStash,0))} g.</td>
  <td><strong>{$expr(number_format($totalWeight,0))} g.</strong></td>
  <td></td>
  <td>{$expr(number_format($weightDelta,1))} g.</td>
  <td>{$expr(number_format($soldToday,1))} g.</td>
  <td>{$productStatus}</td>
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
  <td>{$expr(number_format($weight,0))} g.</td>
  <td></td>
  <td>{$expr(number_format($intStash,0))} g.</td>
  <td>{$expr(number_format($extStash,0))} g.</td>
  <td><strong>{$expr(number_format($totalWeight,0))} g.</strong></td>
  <td></td>
  <td>{$expr(number_format($weightDelta,1))} g.</td>
  <td>{$expr(number_format($soldToday,1))} g.</td>
  <td>{$productStatus}</td>
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
  <td colspan='11'></td>
 </tr>
 <tr>
  <td colspan='11' style='color: #a80082; text-align: center; font-size: 14px; border-top: 1px solid #dedede; border-bottom: 1px solid #dedede;'><strong>$categoryname</strong></td>
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
  <td><strong>Status&nbsp;&nbsp;</strong></td>
 </tr>
 <tr>
  <td style='text-align: left;'>$name</td>
  <td></td>
  <td></td>
  <td>{$expr(number_format($intStash,0))} u.</td>
  <td>{$expr(number_format($extStash,0))} u.</td>
  <td></td>
  <td></td>
  <td></td>
  <td>{$expr(number_format($soldToday,0))} u.</td>
  <td>{$productStatus}</td>
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
  <td>{$expr(number_format($intStash,0))} u.</td>
  <td>{$expr(number_format($extStash,0))} u.</td>
  <td></td>
  <td></td>
  <td></td>
  <td>{$expr(number_format($soldToday,0))} u.</td>
  <td>{$productStatus}</td>
 </tr>	
$commentInset
EOD;

				}

			}

		} // Ends products loop
		
		$mailtoProductResponsible .= "</table>";
			
	
				// Span through categories, then look up products from that cat. from closingdetails
				
				
				
				// Finally, set dayClosed in Opening table to 2. Also add dayClosedBy??? Gopod idea I reckon.

		$mailtoProductResponsibleFull .= $mailtoProductResponsible;
		
		echo $mailtoadmin;
		echo $mailtoProductResponsibleFull;
		
		
		$_SESSION['fullMail'] = $mailtoadmin . $mailtoProductResponsibleFull;
		
echo "<br /><a href='uTil/confirm-close-shift-and-day.php?oid=$dayopeningid&cid=$dayclosingid&csid=$closingid&osid=$openingid&closer={$_SESSION['user_id']}' class='cta'>{$lang['close-shift-and-day']}</a><br />";

displayFooter();