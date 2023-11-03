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
		$closingid = $_GET['cid'];
		$openingid = $_GET['oid'];
	} else {
		echo $lang['closing-not-specified'];
		exit();
	}
	
	// Check first! And ask if it's in process
	if ($_SESSION['openAndClose'] == 2) {
		
		if ($_SESSION['noCompare'] != 'true') {
	
			$checkOpening = "SELECT dayOpened, dayOpenedBy, dayOpenedNo FROM closing WHERE closingid = $openingid";
		try
		{
			$result = $pdo3->prepare("$checkOpening");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user 1: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
				$dayClosed = $row['dayOpened'];
				$dayClosedBy = $row['dayOpenedBy'];
				$dayClosedNo = $row['dayOpenedNo'];
			
		} else {
	
			$checkOpening = "SELECT dayClosed, dayClosedBy, dayClosedNo FROM closing WHERE closingid = $closingid";
		try
		{
			$result = $pdo3->prepare("$checkOpening");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user 2: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
				$dayClosed = $row['dayClosed'];
				$dayClosedBy = $row['dayClosedBy'];
				$dayClosedNo = $row['dayClosedNo'];
				
		}
		
	} else {
	
		$checkOpening = "SELECT dayClosed, dayClosedBy, dayClosedNo FROM opening WHERE openingid = $openingid";
		try
		{
			$result = $pdo3->prepare("$checkOpening");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user 3: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
			$dayClosed = $row['dayClosed'];
			$dayClosedBy = $row['dayClosedBy'];
			$dayClosedNo = $row['dayClosedNo'];
			
	}

	if ($dayClosed == '1' && (!isset($_GET['redo']))) {
		
			$closingOperator = getOperator($dayClosedBy);		
			
			pageStart($lang['title-closeday'], NULL, $validationScript, "pcloseday", "step2 dev-align-center", $lang['close-day-error'], $_SESSION['successMessage'], $_SESSION['errorMessage']);
	
	echo  <<<EOD
	<div id="scriptMsg">
	 <div class='error'>
			{$lang['summary-inprogress-1']} $closingOperator<br /><a href='?redo&cid=$closingid&oid=$openingid' class='yellow'> {$lang['summary-inprogress-2']}
	 </div>
	</div>
	
EOD;
	exit();
	
	}
	
	
	
	// Write to DB Opening table: dayClosing is in process
	if ($_SESSION['openAndClose'] == 2) {
		
		if ($_SESSION['noCompare'] != 'true') {
			
			$updateOpening = "UPDATE closing SET dayOpened = 1, dayOpenedBy = {$_SESSION['user_id']} WHERE closingid = $openingid";
			
		} else {
			
			$updateOpening = "UPDATE closing SET dayClosed = 1, dayClosedBy = {$_SESSION['user_id']} WHERE closingid = $closingid";
			
		}
		
	} else {
		
		$updateOpening = "UPDATE opening SET dayClosed = 1, dayClosedBy = {$_SESSION['user_id']} WHERE openingid = $openingid";
			
	}
	
		try
		{
			$result = $pdo3->prepare("$updateOpening")->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user 4: ' . $e->getMessage();
				echo $error;
				exit();
		}

			
	// Select closing details
	$selectClosingData = "SELECT openingtime, closingtime, quantitySold, soldtoday, unitsSold, closingbalance, moneytaken, takenduringday, cashintill, bankBalance, newmembers, closedby, tillComment, expenses, membershipFees, estimatedTill, tillDelta, bankExpenses, prodOpening, prodStock, stockDelta, prodStockFlower, prodStockExtract, income, stockDeltaFlower, stockDeltaExtract, donations, bankDonations, renewedMembers, bannedMembers, deletedMembers, expiredMembers, totalMembers, activeMembers, intStash, extStash, totalWeight, totalNoShake, flowerintStash, flowerextStash, flowerweightNoShake, flowertotalWeight, flowertotalNoShake, extractintStash, extractextStash, extracttotalWeight, flowerDispensed, extractDispensed, soldTodayFlower, soldTodayExtract, membershipfeesBank, soldtodayBar, unitsSoldBar, openingBalance, openingBalanceBank, totCredit, quantitySoldReal, soldTodayFlowerReal, soldTodayExtractReal FROM closing WHERE closingid = $closingid";
		try
		{
			$result = $pdo3->prepare("$selectClosingData");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user 5: ' . $e->getMessage();
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
		$quantitySoldReal = $row['quantitySoldReal'];
		$soldTodayFlowerReal = $row['soldTodayFlowerReal'];
		$soldTodayExtractReal = $row['soldTodayExtractReal'];
		
		if ($_SESSION['realWeight'] == 1) {		
			$flowerDispensed = $soldTodayFlowerReal;
			$extractDispensed = $soldTodayExtractReal;
		}

	$confirmLeave = <<<EOD
    $(document).ready(function() {
    		$('.default').tablesorter({
				usNumberFormat: true,
				headers: {
					3: {
						sorter: "dates"
					},
					7: {
						sorter: "dates"
					}
				}
			}); 	    
/*document.querySelector('button').addEventListener("click", function(){
    window.btn_clicked = true;      //set btn_clicked to true
});*/

$(window).bind('beforeunload', function(){
    if(!window.btn_clicked){
        return "{$lang['closeday-leavepage']}";
    }
});
  }); // end ready
EOD;

		pageStart($lang['title-closeday'], NULL, $confirmLeave, "pcloseday", "step7 dev-align-center", $lang['closeday-conf'], $_SESSION['successMessage'], $_SESSION['errorMessage']);
	
	
		if ($_SESSION['openAndClose'] == 2) {
			
			if ($_SESSION['noCompare'] != 'true') {
				
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
			

			$pageHeader = <<<EOD
		<div class="actionbox-np2">
		  <div class='mainboxheader'>{$lang['close-day-details']}</div>
		  <div class="boxcontent">
		 <table class='purchasetable'>
			 <tr>
				   <td class='biggerFont'><strong>{$lang['last-closing']}</strong>&nbsp;
				  		{$openingtimeView}
				   </td>
				   </tr>
				   <tr>
				     <td class='biggerFont'><strong>{$lang['day-closed']}</strong>&nbsp;
				   			{$closingtimeView}
				   	</td>
				   	</tr>
				   	<tr>
				   	 <td class='biggerFont'><strong>{$lang['day-duration']}</strong>&nbsp;
				   		{$shiftDuration}
				   	</td>
			  </tr>
		 </table>
		 </div>
		</div>
		EOD;
		
			} else {
			
				$closingtime = date('Y-m-d H:i:s');
				$_SESSION['closingtime'] = $closingtime;
				
				$closingtimeView = date('d-m-Y H:i', strtotime($closingtime . "+$offsetSec seconds"));


			$pageHeader = <<<EOD
		<div class="actionbox-np2">
		  <div class='mainboxheader'>{$lang['close-day-details']}</div>
		  <div class="boxcontent">
		 <table class='purchasetable'>
			 <tr>
				   <td class='biggerFont'><strong>{$lang['day-closed']}</strong>&nbsp;
				  		{$closingtimeView}
				   </td>
			  </tr>
		 </table>
		 </div>
		</div>
		EOD;
			
			}
			
		} else if ($_SESSION['openAndClose'] < 2) {
			
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

			$pageHeader = <<<EOD
		<div class="actionbox-np2">
		  <div class='mainboxheader'>{$lang['close-day-details']}</div>
		  <div class="boxcontent">
		 <table class='purchasetable'>
			 <tr>
				   <td class='biggerFont'><strong>{$lang['day-opened']}</strong>&nbsp;
				  		{$openingtimeView}
				   </td>
				   </tr>
				   <tr>
				     <td class='biggerFont'><strong>{$lang['day-closed']}</strong>&nbsp;
				   			{$closingtimeView}
				   	</td>
				   	</tr>
				   	<tr>
				   	 <td class='biggerFont'><strong>{$lang['day-duration']}</strong>&nbsp;
				   		{$shiftDuration}
				   	</td>
			  </tr>
		 </table>
		 </div>
		</div>
		EOD;
		}
	
		$_SESSION['pageHeader'] = $pageHeader;
		
		echo $pageHeader;
		
	// Look up todays total sales, then by category
	
	if ($_SESSION['openAndClose'] == 2 && $openingid == '') {
		
	} else {
	
		// Look up $_SESSION['currencyoperator'], g, units
		$selectSales = "SELECT SUM(quantitySold), SUM(soldToday), SUM(unitsSold) FROM closing WHERE closingid = $closingid";
		try
		{
			$result = $pdo3->prepare("$selectSales");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user 6: ' . $e->getMessage();
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
				$error = 'Error fetching user 7: ' . $e->getMessage();
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
				$error = 'Error fetching user 8: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
			$extractSalesToday = $row['SUM(d.amount)'];
			$extractGramsToday = $row['SUM(d.quantity)'];
			
		$extractSalesPercentageToday = ($extractSalesToday / $salesToday) * 100;
		$extractGramsPercentageToday = ($extractGramsToday / $quantitySold) * 100;
	
	}
	
	
	
	// Look up todays donations
	$selectDonations = "SELECT COUNT(donationid) from donations WHERE (donatedTo < 2 OR donatedTo = 4) AND donationTime BETWEEN '$openingtime' AND '$closingtime'";
		try
		{
			$result = $pdo3->prepare("$selectDonations");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user 9: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
		$donationsNo = $row['COUNT(donationid)'];
		
	// Look up todays bank donations
	$selectDonations = "SELECT COUNT(donationid) from donations WHERE donatedTo = 2 AND donationTime BETWEEN '$openingtime' AND '$closingtime'";
		try
		{
			$result = $pdo3->prepare("$selectDonations");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user 10: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
		$bankDonationsNo = $row['COUNT(donationid)'];
		
	if ($_SESSION['creditOrDirect'] == 0) {
		
		// Look up dispensed today cash
		$selectSales = "SELECT SUM(amount) from sales WHERE saletime BETWEEN '$openingtime' AND '$closingtime' AND direct < 2";
		try
		{
			$result = $pdo3->prepare("$selectSales");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user 11: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
			$salesTodayCash = $row['SUM(amount)'];
	
		// Look up dispensed today bank
		$selectSales = "SELECT SUM(amount) from sales WHERE saletime BETWEEN '$openingtime' AND '$closingtime' AND direct = 2";
		try
		{
			$result = $pdo3->prepare("$selectSales");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user 12: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
			$salesTodayBank = $row['SUM(amount)'];
		
		// Look up bar sales today cash
		$selectSales = "SELECT SUM(amount) from b_sales WHERE saletime BETWEEN '$openingtime' AND '$closingtime' AND direct < 2";
		try
		{
			$result = $pdo3->prepare("$selectSales");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user 12: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
			$salesTodayBarCash = $row['SUM(amount)'];
	
		// Look up bar sales today bank
		$selectSales = "SELECT SUM(amount) from b_sales WHERE saletime BETWEEN '$openingtime' AND '$closingtime' AND direct = 2";
		try
		{
			$result = $pdo3->prepare("$selectSales");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user 13: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
			$salesTodayBarBank = $row['SUM(amount)'];
		
	}

	
			
if ($_SESSION['realWeight'] == 1) {
						
		// Compose mail to admin
		$mailtoadmin = <<<EOD
<div class="statusbox">
 <img src='images/settings-dispensary.png' style='margin-bottom: -7px;' /> {$lang['bar-and-dispensary']}
 <br />
 <br />
<table class='defaultalternate'>
 <tr>
  <th style='text-align: left;'>{$lang['dispensary']}</th>
  <th>{$expr(number_format($salesToday,2))} {$_SESSION['currencyoperator']}</th>
  <th></th>
  <th>{$expr(number_format($quantitySoldReal,2))} g.</th>
  <th>({$expr(number_format($quantitySold,2))} g.)</th>
  <th></th>
  <th>{$expr(number_format($unitsSold,2))} u.</th>
  <th></th>
 </tr>
 <tr>
  <td style='text-align: left;'><em>{$lang['closeday-owflowers']}</em></td>
  <td>{$expr(number_format($flowerSalesToday,2))} {$_SESSION['currencyoperator']}</td>
  <td>{$expr(number_format($flowerSalesPercentageToday,0))}%</td>
  <td>{$expr(number_format($soldTodayFlowerReal,2))} g.</td>
  <td>({$expr(number_format($flowerGramsToday,2))} g.)</td>
  <td>{$expr(number_format($flowerGramsPercentageToday,0))}%</td>
  <td></td>
  <td></td>
 </tr>
 <tr>
  <td style='text-align: left;'><em>{$lang['closeday-owextracts']}</em></td>
  <td>{$expr(number_format($extractSalesToday,2))} {$_SESSION['currencyoperator']}</td>
  <td>{$expr(number_format($extractSalesPercentageToday,0))}%</td>
  <td>{$expr(number_format($soldTodayExtractReal,2))} g.</td>
  <td>({$expr(number_format($extractGramsToday,2))} g.)</td>
  <td>{$expr(number_format($extractGramsPercentageToday,0))}%</td>
  <td></td>
  <td></td>
 </tr>
EOD;
	
} else {
						
		// Compose mail to admin
		$mailtoadmin = <<<EOD
<div class="statusbox">
 <img src='images/settings-dispensary.png' style='margin-bottom: -7px;' /> {$lang['bar-and-dispensary']}
 <br />
 <br />
<table class='defaultalternate'>
 <tr>
  <th style='text-align: left;'>{$lang['dispensary']}</th>
  <th>{$expr(number_format($salesToday,2))} {$_SESSION['currencyoperator']}</th>
  <th></th>
  <th>{$expr(number_format($quantitySold,2))} g.</th>
  <th></th>
  <th></th>
  <th>{$expr(number_format($unitsSold,2))} u.</th>
  <th></th>
 </tr>
 <tr>
  <td style='text-align: left;'><em>{$lang['closeday-owflowers']}</em></td>
  <td>{$expr(number_format($flowerSalesToday,2))} {$_SESSION['currencyoperator']}</td>
  <td>{$expr(number_format($flowerSalesPercentageToday,0))}%</td>
  <td>{$expr(number_format($flowerGramsToday,2))} g.</td>
  <td>{$expr(number_format($flowerGramsPercentageToday,0))}%</td>
  <td></td>
  <td></td>
  <td></td>
 </tr>
 <tr>
  <td style='text-align: left;'><em>{$lang['closeday-owextracts']}</em></td>
  <td>{$expr(number_format($extractSalesToday,2))} {$_SESSION['currencyoperator']}</td>
  <td>{$expr(number_format($extractSalesPercentageToday,0))}%</td>
  <td>{$expr(number_format($extractGramsToday,2))} g.</td>
  <td>{$expr(number_format($extractGramsPercentageToday,0))}%</td>
  <td></td>
  <td></td>
  <td></td>
 </tr>
EOD;

}


	// Query to look up categories
	$selectCats = "SELECT id, name, type from categories WHERE id > 2 ORDER by name ASC";
		try
		{
			$resultCats = $pdo3->prepare("$selectCats");
			$resultCats->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user 14: ' . $e->getMessage();
				echo $error;
				exit();
		}
	

		$i = 0;
		
		while ($category = $resultCats->fetch()) {
		
		$categoryid = $category['id'];
		$name = $category['name'];
		$type = $category['type'];
		
		// Create more product queries for each category - to be used in a bigger query further down!
		$customProducts .= " UNION ALL SELECT '$categoryid' AS category, categoryType, pr.name, '' AS breed2, c.purchaseid, c.soldToday, c.weight, c.weightDelta, c.intStash, c.extStash, c.weightNoShake, c.totalWeight, c.totalNoShake, c.inMenu, c.specificComment FROM products pr, closingdetails c WHERE c.category = $categoryid AND c.productid = pr.productid AND c.closingid = $closingid";
				
		
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
				$error = 'Error fetching user 15: ' . $e->getMessage();
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
  <td></td>
EOD;

		if ($_SESSION['realWeight'] == 1) {
		$gramCatSummary .=  <<<EOD
  <td>({$expr(number_format($quantitySoldOthers,2))} g.)</td>			
EOD;

		}
		
		$gramCatSummary .=  <<<EOD
  <td>{$expr(number_format($othersGramsPercentageToday,0))}%</td>
  <td></td>
EOD;
			if ($_SESSION['realWeight'] == 1) {
			$gramCatSummary .=  <<<EOD
	  </tr>			
	EOD;
			}else{
				$gramCatSummary .=  <<<EOD
			<td></td>	
	  </tr>			
	EOD;
			}

		}

		
		$i++;
		
	}

	}
	
		$mailtoadmin .= $gramCatSummary;
		$mailtoadmin .= $unitCatSummary;

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
		$selectSalesOthers = "SELECT SUM(d.amount), SUM(d.quantity) from b_sales s, b_salesdetails d WHERE s.saleid = d.saleid AND s.saletime BETWEEN '$openingtime' AND '$closingtime' AND d.category = $categoryid";
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

		$mailtoadmin .= <<<EOD
 <tr>
  <td colspan='10'></td>
 </tr>
 <tr>
  <th style='text-align: left;'>{$lang['bar']}</th>
  <th>{$expr(number_format($barSales,2))} {$_SESSION['currencyoperator']}</th>
  <th></th>
  <th></th>
  <th></th>
  <th></th>
  <th>{$expr(number_format($barUnits,2))} u.</th>
  <th></th>
 </tr>
 {$barCatSummary}
</table>
</div>
<br>
<div class='statusbox'>
<img src='images/settings-members.png' style='margin-bottom: -7px;'> Member details
<br><br>
<table class='defaultalternate'>
 <tr>
  <td style='text-align: left;'>{$lang['closeday-newmembers']}</td>
  <td>$newmembers</td>
  <td></td>
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
  <td></td>
  <td></td>
 </tr>
 </table>
</div>
 <br>
<div class="statusbox">
 <img src='images/settings-finances.png' style='margin-bottom: -7px;' /> {$lang['closeday-finances']} $reportDateReadable
 <br />
 <br />
 <table class='defaultalternate'>
 <tr>
  <th style='vertical-align: bottom; text-align: center; font-size: 14px;' colspan="2"><strong>{$lang['summary']}</strong></th>
  <th></th>
  <th style='vertical-align: bottom; text-align: center; font-size: 14px;' colspan="2"><strong>{$lang['till-calculation']}</strong></th>
  <th></th>
  <th style='vertical-align: bottom; text-align: center; font-size: 14px;' colspan="2"><strong>{$lang['bank-calculation']}</strong></th>
  <th></th>
  <th></th>
 </tr>
 <tr>
  <td style='text-align: left;'>{$lang['closeday-donations-till']}</td>
  <td>{$expr(number_format($donations,2))} {$_SESSION['currencyoperator']}</td>
  <td style='text-align: left;'>($donationsNo)</td>
  <td style='text-align: left;'>{$lang['closeday-tillatopening']}</td>
  <td>{$expr(number_format($openingBalance,2))} {$_SESSION['currencyoperator']}</td>
  <td></td>
  <td style='text-align: left;'>{$lang['bank-opening']}:</td>
  <td>{$expr(number_format($openingBalanceBank,2))} {$_SESSION['currencyoperator']}</td>
  <td></td>
  <td></td>
 </tr>
 <tr>
  <td style='text-align: left;'>+ {$lang['closeday-donations-bank']}</td>
  <td>{$expr(number_format($bankDonations,2))} {$_SESSION['currencyoperator']}</td>
  <td style='text-align: left;'>($bankDonationsNo)</td>
  <td style='text-align: left;'>+ {$lang['memberfees']}</td>
  <td>{$expr(number_format($membershipFees,2))} {$_SESSION['currencyoperator']}</td>
  <td></td>
  <td style='text-align: left;'>+ {$lang['memberfees']}</td>
  <td>{$expr(number_format($membershipfeesBank,2))} {$_SESSION['currencyoperator']}</td>
  <td></td>
  <td></td>
 </tr>
 <tr>
  <td style='text-align: left;'>+ {$lang['closeday-membershipfees-till']}</td>
  <td>{$expr(number_format($membershipFees,2))} {$_SESSION['currencyoperator']}</td>
  <td></td>
  <td style='text-align: left;'>+ {$lang['global-donations']}</td>
  <td>{$expr(number_format($donations,2))} {$_SESSION['currencyoperator']}</td>
  <td></td>
  <td style='text-align: left;'>+ {$lang['global-donations']}</td>
  <td>{$expr(number_format($bankDonations,2))} {$_SESSION['currencyoperator']}</td>
  <td></td>
  <td></td>
 </tr>
EOD;
	if ($_SESSION['creditOrDirect'] == 0) {
		
		$mailtoadmin .= <<<EOD
 <tr>
  <td style='text-align: left;'>+ {$lang['closeday-membershipfees-bank']}</td>
  <td>{$expr(number_format($membershipfeesBank,2))} {$_SESSION['currencyoperator']}</td>
  <td></td>
  <td style='text-align: left;'>+ {$lang['dispensed-direct']}</td>
  <td>{$expr(number_format($salesTodayCash,2))} {$_SESSION['currencyoperator']}</td>
  <td></td>
  <td style='text-align: left;'>+ {$lang['dispensed-direct']}</td>
  <td>{$expr(number_format($salesTodayBank,2))} {$_SESSION['currencyoperator']}</td>
  <td></td>
  <td></td>
 </tr>
 <tr>
  <td style='text-align: left;'>+ {$lang['dispensed-direct-till']}</td>
  <td>{$expr(number_format($salesTodayCash,2))} {$_SESSION['currencyoperator']}</td>
  <td></td>
  <td style='text-align: left;'>+ {$lang['direct-bar-sales']}</td>
  <td>{$expr(number_format($salesTodayBarCash,2))} {$_SESSION['currencyoperator']}</td>
  <td></td>
  <td style='text-align: left;'>+ {$lang['direct-bar-sales']}</td>
  <td>{$expr(number_format($salesTodayBarBank,2))} {$_SESSION['currencyoperator']}</td>
  <td></td>
  <td></td>
 </tr>
 <tr>
  <td style='text-align: left;'>+ {$lang['dispensed-direct-bank']}</td>
  <td>{$expr(number_format($salesTodayBank,2))} {$_SESSION['currencyoperator']}</td>
  <td></td>
  <td style='text-align: left;'>-&nbsp; {$lang['closeday-moneybanked']}</td>
  <td style='text-align: right;'>{$expr(number_format($moneytaken + $bankedduringday,2))} {$_SESSION['currencyoperator']}</td>
  <td></td>
  <td style='text-align: left;'>+ {$lang['closeday-moneybanked']}</td>
  <td>{$expr(number_format($moneytaken + $bankedduringday,2))} {$_SESSION['currencyoperator']}</td>
  <td></td>
  <td></td>
 </tr>
 <tr>
  <td style='text-align: left;'>+ {$lang['direct-bar-sales-till']}</td>
  <td>{$expr(number_format($salesTodayBarCash,2))} {$_SESSION['currencyoperator']}</td>
  <td></td>
  <td style='text-align: left; border-bottom: 1px dashed #ababab;''>-&nbsp; {$lang['global-expenses']}</td>
  <td style='text-align: right; border-bottom: 1px dashed #ababab;'>{$expr(number_format($expenses,2))} {$_SESSION['currencyoperator']}</td>
  <td></td>
  <td style='text-align: left; border-bottom: 1px dashed #ababab;'>-&nbsp; {$lang['global-expenses']}</td>
  <td style='text-align: right; border-bottom: 1px dashed #ababab;'>{$expr(number_format($bankExpenses,2))} {$_SESSION['currencyoperator']}</td>
  <td></td>
  <td></td>
 </tr>
 <tr>
  <td style='text-align: left; border-bottom: 1px dashed #ababab;'>+ {$lang['direct-bar-sales-bank']}</td>
  <td style='text-align: right; border-bottom: 1px dashed #ababab;'>{$expr(number_format($salesTodayBarBank,2))} {$_SESSION['currencyoperator']}</td>
  <td></td>
  <td style='text-align: left; border-bottom: 1px solid #ababab;'>= {$lang['closeday-estimatedtill']}</td>
  <td style='text-align: right; border-bottom: 1px solid #ababab;'>{$expr(number_format($estimatedTill,2))} {$_SESSION['currencyoperator']}</td>
  <td></td>
  <td style='text-align: left; border-bottom: 1px solid #ababab;'>= {$lang['closeday-bankbalance']}</td>
  <td style='text-align: right; border-bottom: 1px solid #ababab;'>{$expr(number_format($bankBalance,2))} {$_SESSION['currencyoperator']}</td>
  <td></td>
  <td></td>
 </tr>
 <tr>
  <td style='text-align: left; border-bottom: 1px solid #ababab;'>= {$lang['closeday-totalincome']}</td>
  <td style='border-bottom: 1px solid #ababab;'>{$expr(number_format($income,2))} {$_SESSION['currencyoperator']}</td>
  <td></td>
  <td style='text-align: left;'>{$lang['closeday-yourcount']}</td>
  <td>{$expr(number_format($cashintill,2))} {$_SESSION['currencyoperator']}</td>
  <td></td>
  <td></td>
  <td></td>
  <td></td>
  <td></td>
 </tr>
 <tr>
  <td style='text-align: left;'>{$lang['global-expenses']}</td>
  <td>{$expr(number_format($expenses + $bankExpenses,2))} {$_SESSION['currencyoperator']}</td>
  <td></td>
  <td style='text-align: left;'><strong>{$lang['global-delta']}</strong></td>
  <td><strong>{$expr(number_format($tillDelta,2))} {$_SESSION['currencyoperator']}</strong></td>
  <td></td>
  <td></td>
  <td></td>
  <td></td>
  <td></td>
 </tr>
 <tr>
  <td style='text-align: left;'><strong>{$lang['profit']}</strong></td>
  <td><strong>{$expr(number_format($income - $expenses - $bankExpenses,2))} {$_SESSION['currencyoperator']}</strong></td>
  <td></td>
  <td colspan='6' style='text-align: left;'>{$lang['closeday-tillcomment']}:<br /><em>$tillComment</em> </td>
  <td></td>
 </tr>
 <tr>
  <td style='text-align: left;'>{$lang['closeday-totalclubbalance']}</td>
  <td>{$expr(number_format($closingbalance,2))} {$_SESSION['currencyoperator']}</td>
  <td></td>
  <td></td>
  <td></td>
  <td></td>
  <td></td>
  <td></td>
  <td></td>
  <td></td>
 </tr>
 <tr>
  <td style='text-align: left;'>{$lang['member-credit']}</td>
  <td>{$expr(number_format($totCredit,2))} {$_SESSION['currencyoperator']}</td>
  <td></td>
  <td></td>
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
  <td style='border-bottom: 1px dashed #ababab;'>{$expr(number_format($membershipfeesBank,2))} {$_SESSION['currencyoperator']}</td>
  <td></td>
  <td style='text-align: left;'>-&nbsp; {$lang['closeday-moneybanked']}</td>
  <td style='text-align: right;'>{$expr(number_format($moneytaken + $bankedduringday,2))} {$_SESSION['currencyoperator']}</td>
  <td></td>
  <td style='text-align: left;'>+ {$lang['closeday-moneybanked']}</td>
  <td>{$expr(number_format($moneytaken + $bankedduringday,2))} {$_SESSION['currencyoperator']}</td>
  <td></td>
  <td></td>
 </tr>
 <tr>
  <td style='text-align: left; border-bottom: 1px solid #ababab;'>= {$lang['closeday-totalincome']}</td>
  <td style='border-bottom: 1px solid #ababab;'>{$expr(number_format($income,2))} {$_SESSION['currencyoperator']}</td>
  <td></td>
  <td style='text-align: left; border-bottom: 1px dashed #ababab;''>-&nbsp; {$lang['global-expenses']}</td>
  <td style='text-align: right; border-bottom: 1px dashed #ababab;'>{$expr(number_format($expenses,2))} {$_SESSION['currencyoperator']}</td>
  <td></td>
  <td style='text-align: left; border-bottom: 1px dashed #ababab;'>-&nbsp; {$lang['global-expenses']}</td>
  <td style='text-align: right; border-bottom: 1px dashed #ababab;'>{$expr(number_format($bankExpenses,2))} {$_SESSION['currencyoperator']}</td>
  <td></td>
  <td></td>
 </tr>
 <tr>
  <td style='text-align: left;'>{$lang['global-expenses']}</td>
  <td>{$expr(number_format($expenses + $bankExpenses,2))} {$_SESSION['currencyoperator']}</td>
  <td></td>
  <td style='text-align: left; border-bottom: 1px solid #ababab;'>= {$lang['closeday-estimatedtill']}</td>
  <td style='text-align: right; border-bottom: 1px solid #ababab;'>{$expr(number_format($estimatedTill,2))} {$_SESSION['currencyoperator']}</td>
  <td></td>
  <td style='text-align: left; border-bottom: 1px solid #ababab;'>= {$lang['closeday-bankbalance']}</td>
  <td style='text-align: right; border-bottom: 1px solid #ababab;'>{$expr(number_format($bankBalance,2))} {$_SESSION['currencyoperator']}</td>
  <td></td>
  <td></td>
 </tr>
 <tr>
  <td style='text-align: left;'><strong>{$lang['profit']}</strong></td>
  <td><strong>{$expr(number_format($income - $expenses - $bankExpenses,2))} {$_SESSION['currencyoperator']}</strong></td>
  <td></td>
  <td style='text-align: left;'>{$lang['closeday-yourcount']}</td>
  <td>{$expr(number_format($cashintill,2))} {$_SESSION['currencyoperator']}</td>
  <td></td>
  <td></td>
  <td></td>
  <td></td>
  <td></td>
 </tr>
 <tr>
  <td style='text-align: left;'>{$lang['closeday-totalclubbalance']}</td>
  <td>{$expr(number_format($closingbalance,2))} {$_SESSION['currencyoperator']}</td>
  <td></td>
  <td style='text-align: left;'><strong>{$lang['global-delta']}</strong></td>
  <td><strong>{$expr(number_format($tillDelta,2))} {$_SESSION['currencyoperator']}</strong></td>
  <td></td>
  <td></td>
  <td></td>
  <td></td>
  <td></td>
 </tr>
 <tr>
  <td style='text-align: left;'>{$lang['member-credit']}</td>
  <td>{$expr(number_format($totCredit,2))} {$_SESSION['currencyoperator']}</td>
  <td></td>
  <td colspan='6' style='text-align: left;'>{$lang['closeday-tillcomment']}:<br /><em>$tillComment</em> </td>
  <td></td>
 </tr>
EOD;

	}
	
	
	/// Loop somewhere to look up closingother and display results

				
		$mailtoProductResponsible = <<<EOD
 </table>
 </div>		
<div class="statusbox">
 <img src='images/settings-dispensary.png' style='margin-bottom: -7px;' /> {$lang['closeday-productoverview']}
 <br />
 <br />
<table  class='defaultalternate'>
 <tr>
  <th></th>
  <th><strong>{$lang['closeday-jars']}&nbsp;&nbsp;</strong></th>
  <th><strong>{$lang['closeday-woshake']}&nbsp;&nbsp;</strong></th>
  <th><strong>{$lang['closeday-intstash']}&nbsp;&nbsp;</strong></th>
  <th><strong>{$lang['closeday-extstash']}&nbsp;&nbsp;</strong></th>
  <th><strong>{$lang['global-total']}&nbsp;&nbsp;</strong></th>
  <th><strong>{$lang['closeday-woshake']}&nbsp;&nbsp;</strong></th>
  <th><strong>{$lang['global-delta']}&nbsp;&nbsp;</strong></th>
  <th><strong>{$lang['closeday-dispensed']}&nbsp;&nbsp;</strong></th>
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
  <td></td>
  <td>{$expr(number_format($extractintStash,2))} g.</td>
  <td>{$expr(number_format($extractextStash,2))} g.</td>
  <td><strong>{$expr(number_format($extracttotalWeight,2))} g.</strong></td>
  <td></td>
  <td>{$expr(number_format($stockDeltaExtract,2))} g.</td>
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
				$error = 'Error fetching user 16: ' . $e->getMessage();
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
			$resultCN = $pdo3->prepare("$findCatName");
			$resultCN->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user 17: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$rowCN = $resultCN->fetch();
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
				
				$gramProducts .= <<<EOD
 <tr>
  <td style='text-align: left;'>$categoryName</td>
  <td>{$expr(number_format($prodStock,2))} g.</td>
  <td></td>
  <td>{$expr(number_format($intStash,2))} g.</td>
  <td>{$expr(number_format($extStash,2))} g.</td>
  <td><strong>{$expr(number_format($thisTotal,2))} g.</strong></td>
  <td></td>
  <td>{$expr(number_format($stockDelta,2))} g.</td>
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
			
				$unitProducts .= <<<EOD
 <tr>
  <td style='text-align: left;'>$categoryName</td>
  <td>{$expr(number_format($prodStock,2))} u.</td>
  <td></td>
  <td>{$expr(number_format($intStash,2))} u.</td>
  <td>{$expr(number_format($extStash,2))} u.</td>
  <td><strong>{$expr(number_format($thisTotal,2))} u.</strong></td>
  <td></td>
  <td>{$expr(number_format($stockDelta,2))} u.</td>
  <td>{$expr(number_format($unitsSold,2))} u.</td>
 </tr>
EOD;

			}
			
		}
			
		$mailtoProductResponsible .= $gramProducts;
		
		$mailtoProductResponsible .= <<<EOD
 <tr style='border-top: 1px solid #888; border-bottom: 2px solid #888;'>
  <td style='text-align: left;'><strong>TOTAL G</strong></td>
  <td><strong>{$expr(number_format($prodStockFlower + $prodStockExtract + $cat0prodStock,2))} g.</strong></td>
  <td><strong>{$expr(number_format($flowerweightNoShake,2))} g.</strong></td>
  <td><strong>{$expr(number_format($flowerintStash + $extractintStash + $cat0intStash,2))} g.</strong></td>
  <td><strong>{$expr(number_format($flowerextStash + $extractextStash + $cat0extStash,2))} g.</strong></td>
  <td><strong>{$expr(number_format($flowertotalWeight + $extracttotalWeight + $cat0thisTotal,2))} g.</strong></td>
  <td><strong>{$expr(number_format($flowertotalNoShake,2))} g.</strong></td>
  <td><strong>{$expr(number_format($stockDeltaFlower + $stockDeltaExtract + $cat0stockDelta,2))} g.</strong></td>
EOD;
				if ($_SESSION['realWeight'] == 1) {
					
		$mailtoProductResponsible .= <<<EOD
  <td>{$expr(number_format($flowerDispensed + $extractDispensed + $cat0quantitySoldReal,2))} g.</td>
 </tr>
EOD;

				} else {
					
		$mailtoProductResponsible .= <<<EOD
  <td>{$expr(number_format($flowerDispensed + $extractDispensed + $cat0quantitySold,2))} g.</td>
 </tr>
EOD;

				}
		$mailtoProductResponsible .= <<<EOD
 <tr>
  <td colspan='9'>&nbsp;</td>
 </tr>
EOD;

		$mailtoProductResponsible .= $unitProducts;
		$mailtoProductResponsible .= <<<EOD
 <tr style='border-top: 1px solid #888; border-bottom: 2px solid #888;'>
  <td style='text-align: left;'><strong>TOTAL U</strong></td>
  <td><strong>{$expr(number_format($cat1prodStock,2))} u.</strong></td>
  <td></td>
  <td><strong>{$expr(number_format($cat1intStash,2))} u.</strong></td>
  <td><strong>{$expr(number_format($cat1extStash,2))} u.</strong></td>
  <td><strong>{$expr(number_format($cat1thisTotal,2))} u.</strong></td>
  <td></td>
  <td><strong>{$expr(number_format($cat1stockDelta,2))} u.</strong></td>
  <td><strong>{$expr(number_format($cat1unitsSold,2))} u.</strong></td>
 </table>
 </div> 
EOD;
		
		
		
		$mailtoProductResponsible .= <<<EOD
<div class="statusbox">
 <img src='images/settings-dispensary.png' style='margin-bottom: -7px;' /> {$lang['closeday-productdetails']}
 <br />
 <br />
<table class='defaultalternate'>
 <tr>
  <th><span style="font-size: 20px; color: #444;">{$lang['global-flowerscaps']}</span></th>
  <th><strong>{$lang['closeday-jars']}&nbsp;&nbsp;</strong></th>
  <th><strong>{$lang['closeday-woshake']}&nbsp;&nbsp;</strong></th>
  <th><strong>{$lang['closeday-intstash']}&nbsp;&nbsp;</strong></th>
  <th><strong>{$lang['closeday-extstash']}&nbsp;&nbsp;</strong></th>
  <th><strong>{$lang['global-total']}&nbsp;&nbsp;</strong></th>
  <th><strong>{$lang['closeday-woshake']}&nbsp;&nbsp;</strong></th>
  <th><strong>{$lang['global-delta']}&nbsp;&nbsp;</strong></th>
  <th><strong>{$lang['closeday-dispensed']}&nbsp;&nbsp;</strong></th>
  <th><strong>Status&nbsp;&nbsp;</strong></th>
 </tr>
EOD;
		
		 $defaultProducts = "SELECT category, '' AS categoryType, f.name, f.breed2, c.purchaseid, c.soldToday, c.weight, c.weightDelta, c.intStash, c.extStash, c.weightNoShake, c.totalWeight, c.totalNoShake, c.inMenu, c.specificComment FROM flower f, closingdetails c WHERE c.category = '1' AND c.productid = f.flowerid AND c.closingid = $closingid UNION ALL SELECT category, '' AS categoryType, e.name, '' AS breed2, c.purchaseid, c.soldToday, c.weight, c.weightDelta, c.intStash, c.extStash, c.weightNoShake, c.totalWeight, c.totalNoShake, c.inMenu, c.specificComment FROM extract e, closingdetails c WHERE c.category = '2' AND c.productid = e.extractid AND c.closingid = $closingid"; 
		
		$allProducts = $defaultProducts . $customProducts;
		try
		{
			$productsResult = $pdo3->prepare("$allProducts");
			$productsResult->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user 18: ' . $e->getMessage();
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
		try
		{
			$result = $pdo3->prepare("$selectGrowTypeNo");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user 19: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
					$growTypeNo = $row['growType'];
					$closedAt = $row['closedAt'];
					$growtype = '';
		if($growTypeNo != NULL){			
					$growDetails = "SELECT growtype FROM growtypes WHERE growtypeid = $growTypeNo";
			try
			{
				$result = $pdo3->prepare("$growDetails");
				$result->execute();
			}
			catch (PDOException $e)
			{
					$error = 'Error fetching user 20: ' . $e->getMessage();
					echo $error;
					exit();
			}
		
			$row = $result->fetch();
						$growtype = $row['growtype'];
		}			
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
  <td style='text-align: left;'>$name <span class='smallerfont'>$growtype</span> ($purchaseid)</td>
  <td>{$expr(number_format($weight,2))} g.</td>
  <td>{$expr(number_format($weightNoShake,2))} g.</td>
  <td>{$expr(number_format($intStash,2))} g.</td>
  <td>{$expr(number_format($extStash,2))} g.</td>
  <td><strong>{$expr(number_format($totalWeight,2))} g.</strong></td>
  <td><strong>{$expr(number_format($totalNoShake,2))} g.</strong></td>
  <td>{$expr(number_format($weightDelta,2))} g.</td>
  <td>{$expr(number_format($soldToday,2))} g.</td>
  <td>{$productStatus}</td>
 </tr>
$commentInset
EOD;

			} else  if ($category == '2') {
				
				
				
				// Look up closed status
				$selectGrowTypeNo = "SELECT closedAt FROM purchases WHERE purchaseid = $purchaseid";
		try
		{
			$result = $pdo3->prepare("$selectGrowTypeNo");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user 21: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
					$closedAt = $row['closedAt'];
					
					
if ($dividersetExtract != 'yes') {
				
				// Look up closed status
				$selectGrowTypeNo = "SELECT closedAt FROM purchases WHERE purchaseid = $purchaseid";
		try
		{
			$result = $pdo3->prepare("$selectGrowTypeNo");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user 22: ' . $e->getMessage();
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
  <td colspan='11'></td>
 </tr>
 <tr>
  <th><span style='font-size: 20px; color: #444;'>{$lang['global-extractscaps']}</span></th>
  <th><strong>{$lang['closeday-jars']}&nbsp;&nbsp;</strong></th>
  <th><strong></strong></th>
  <th><strong>{$lang['closeday-intstash']}&nbsp;&nbsp;</strong></th>
  <th><strong>{$lang['closeday-extstash']}&nbsp;&nbsp;</strong></th>
  <th><strong>{$lang['global-total']}&nbsp;&nbsp;</strong></th>
  <th><strong></strong></th>
  <th><strong>{$lang['global-delta']}&nbsp;&nbsp;</strong></th>
  <th><strong>{$lang['closeday-dispensed']}&nbsp;&nbsp;</strong></th>
  <th><strong>Status&nbsp;&nbsp;</strong></th>
 </tr>
 <tr>
  <td style='text-align: left;'>$name ($purchaseid)</td>
  <td>{$expr(number_format($weight,2))} g.</td>
  <td></td>
  <td>{$expr(number_format($intStash,2))} g.</td>
  <td>{$expr(number_format($extStash,2))} g.</td>
  <td><strong>{$expr(number_format($totalWeight,2))} g.</strong></td>
  <td></td>
  <td>{$expr(number_format($weightDelta,2))} g.</td>
  <td>{$expr(number_format($soldToday,2))} g.</td>
  <td>{$productStatus}</td>
 </tr>	
$commentInset
EOD;
}

				
				$mailtoProductResponsible .= <<<EOD
 <tr>
  <td style='text-align: left;'>$name ($purchaseid)</td>
  <td>{$expr(number_format($weight,2))} g.</td>
  <td></td>
  <td>{$expr(number_format($intStash,2))} g.</td>
  <td>{$expr(number_format($extStash,2))} g.</td>
  <td><strong>{$expr(number_format($totalWeight,2))} g.</strong></td>
  <td></td>
  <td>{$expr(number_format($weightDelta,2))} g.</td>
  <td>{$expr(number_format($soldToday,2))} g.</td>
  <td>{$productStatus}</td>
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
				$error = 'Error fetching user 23: ' . $e->getMessage();
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
			$resultCats = $pdo3->prepare("$selectCats");
			$resultCats->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user 24: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$catRow = $resultCats->fetch();
						$categoryname = $catRow['name'];
	
					// insert divider
					$gramMail .= <<<EOD
 <tr>
  <td colspan='11'></td>
 </tr>
 <tr>
  <th><span style='font-size: 20px; color: #444;'>$categoryname (g.)</span></th>
  <th><strong>{$lang['closeday-jars']}&nbsp;&nbsp;</strong></th>
  <th><strong></strong></th>
  <th><strong>{$lang['closeday-intstash']}&nbsp;&nbsp;</strong></th>
  <th><strong>{$lang['closeday-extstash']}&nbsp;&nbsp;</strong></th>
  <th><strong>{$lang['global-total']}&nbsp;&nbsp;</strong></th>
  <th><strong></strong></th>
  <th><strong>{$lang['global-delta']}&nbsp;&nbsp;</strong></th>
  <th><strong>{$lang['closeday-dispensed']}&nbsp;&nbsp;</strong></th>
  <th><strong>Status&nbsp;&nbsp;</strong></th>
 </tr>
 <tr>
  <td style='text-align: left;'>$name ($purchaseid)</td>
  <td>{$expr(number_format($weight,2))} g.</td>
  <td></td>
  <td>{$expr(number_format($intStash,2))} g.</td>
  <td>{$expr(number_format($extStash,2))} g.</td>
  <td><strong>{$expr(number_format($weight + $intStash + $extStash,2))} g.</strong></td>
  <td></td>
  <td>{$expr(number_format($weightDelta,2))} g.</td>
  <td>{$expr(number_format($soldToday,2))} g.</td>
  <td>{$productStatus}</td>
 </tr>	
$commentInset
EOD;

					${'otherHeader' . $category} = 'set';
						
				} else {
				
					$gramMail .= <<<EOD
 <tr>
  <td style='text-align: left;'>$name ($purchaseid)</td>
  <td>{$expr(number_format($weight,2))} g.</td>
  <td></td>
  <td>{$expr(number_format($intStash,2))} g.</td>
  <td>{$expr(number_format($extStash,2))} g.</td>
  <td><strong>{$expr(number_format($weight + $intStash + $extStash,2))} g.</strong></td>
  <td></td>
  <td>{$expr(number_format($weightDelta,2))} g.</td>
  <td>{$expr(number_format($soldToday,2))} g.</td>
  <td>{$productStatus}</td>
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
			$resultCats = $pdo3->prepare("$selectCats");
			$resultCats->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user 25: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$catRow = $resultCats->fetch();
						$categoryname = $catRow['name'];
	
					// insert divider
					$unitMail .= <<<EOD
 <tr>
  <td colspan='11'></td>
 </tr>
 <tr>
  <th><span style="font-size: 20px; color: #444;">$categoryname (u.)</span></th>
  <th><strong>{$lang['closeday-jars']}&nbsp;&nbsp;</strong></th>
  <th><strong></strong></th>
  <th><strong>{$lang['closeday-intstash']}&nbsp;&nbsp;</strong></th>
  <th><strong>{$lang['closeday-extstash']}&nbsp;&nbsp;</strong></th>
  <th><strong>{$lang['global-total']}&nbsp;&nbsp;</strong></th>
  <th><strong></strong></th>
  <th><strong>{$lang['global-delta']}&nbsp;&nbsp;</strong></th>
  <th><strong>{$lang['closeday-dispensed']}&nbsp;&nbsp;</strong></th>
  <th><strong>Status&nbsp;&nbsp;</strong></th>
 </tr>
 <tr>
  <td style='text-align: left;'>$name ($purchaseid)</td>
  <td>{$expr(number_format($weight,2))} u.</td>
  <td></td>
  <td>{$expr(number_format($intStash,2))} u.</td>
  <td>{$expr(number_format($extStash,2))} u.</td>
  <td><strong>{$expr(number_format($weight + $intStash + $extStash,2))} u.</strong></td>
  <td></td>
  <td>{$expr(number_format($weightDelta,2))} u.</td>
  <td>{$expr(number_format($soldToday,2))} u.</td>
  <td>{$productStatus}</td>
 </tr>	
$commentInset
EOD;

					${'otherHeader' . $category} = 'set';
						
				} else {
				
					$unitMail .= <<<EOD
 <tr>
  <td style='text-align: left;'>$name ($purchaseid)</td>
  <td>{$expr(number_format($weight,2))} u.</td>
  <td></td>
  <td>{$expr(number_format($intStash,2))} u.</td>
  <td>{$expr(number_format($extStash,2))} u.</td>
  <td><strong>{$expr(number_format($weight + $intStash + $extStash,2))} u.</strong></td>
  <td></td>
  <td>{$expr(number_format($weightDelta,2))} u.</td>
  <td>{$expr(number_format($soldToday,2))} u.</td>
  <td>{$productStatus}</td>
 </tr>	
$commentInset
EOD;

				}
				}


			}

		} // Ends products loop
		
		$mailtoProductResponsible .= $gramMail;
		$mailtoProductResponsible .= $unitMail;
		
		$mailtoProductResponsible .= "</table></div>";
			
	
				// Span through categories, then look up products from that cat. from closingdetails
				
				
				
				// Finally, set dayClosed in Opening table to 2. Also add dayClosedBy??? Gopod idea I reckon.

		$mailtoProductResponsibleFull .= $mailtoProductResponsible;
		
		echo $mailtoadmin;
		echo $mailtoProductResponsibleFull;
		
			// Query to look up expenses
			$selectExpenses = "SELECT expenseid, registertime, userid, expensetype, expense, moneysource, other, amount, shop, comment, receipt, expensecategory FROM expenses WHERE registertime BETWEEN '$openingtime' AND '$closingtime' ORDER by registertime DESC";
		try
		{
			$resultF = $pdo3->prepare("$selectExpenses");
			$resultF->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user 26: ' . $e->getMessage();
				echo $error;
				exit();
		}
		
						$expenseDetails .= <<<EOD
		<br />
	<div class="statusbox">
	 <img src='images/settings-finances.png' style='margin-bottom: -7px;' /> {$lang['global-expensescaps']}
	 <br />
	 <br />
		<table class='defaultalternate' cellpadding='5'>
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

		
			while ($expense = $resultF->fetch()) {
			
			
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
				$error = 'Error fetching user 27: ' . $e->getMessage();
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
				$error = 'Error fetching user 28: ' . $e->getMessage();
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
				$error = 'Error fetching user 29: ' . $e->getMessage();
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
		  	   <td style='text-align: right;' class='clickableRow' href='expense.php?expenseid=%d'>%0.2f <span class='smallerfont'>{$_SESSION['currencyoperator']}</span></td>
		  	   <td class='clickableRow' href='expense.php?expenseid=%d'>%s</td>
		  	   <td class='clickableRow %s' href='expense.php?expenseid=%d'>%s</td>
			  </tr>",
			  $expense['expenseid'], $formattedDate, $expense['expenseid'], $expenseCat, $expense['expenseid'], $expense['expense'], $expense['expenseid'], $expense['shop'], $expense['expenseid'], $member, $expense['expenseid'], $expense['amount'], $expense['expenseid'], $source, $recClass, $expense['expenseid'], $receipt
			  );
			  $expenseDetails.= $expense_row;
		  }
		  
		  		$expenseDetails .= "</table></div>";


			echo $expenseDetails;
					
		$_SESSION['fullMail'] = $mailtoadmin . $mailtoProductResponsibleFull . $expenseDetails;
		
echo <<<EOD
<br /><a href="uTil/confirm-close-day.php?oid={$_SESSION['openingid']}&cid=$closingid&closer={$_SESSION['user_id']}" class='cta1' id='hidecta'>{$lang['admin-closeday']}</a><br />
<img src='images/spinner.gif' id='showspinner' style='display: none; margin-top: -10px;' width='40' />
<script>
		$('#hidecta').click(function () {
		$("html, body").animate({ scrollTop: $(document).height() });
		$('#showspinner').css('display', 'inline-block');
		$('#hidecta').css('display', 'none');
		});	
</script>
EOD;


displayFooter();
