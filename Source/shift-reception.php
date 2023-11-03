<?php

	require_once 'cOnfig/connection.php';
	require_once 'cOnfig/view.php';
	require_once 'cOnfig/authenticate.php';
	require_once 'cOnfig/languages/common.php';
		
	session_start();
	$accessLevel = '3';

	// Authenticate & authorize
	authorizeUser($accessLevel);
	
	$shifttype = $_GET['type'];
	$shiftid = $_GET['id'];
	
		if ($shifttype == 3) {
		// close shift

		// Retrieve close shift details
		$closingLookup = "SELECT closingtime, cashintill, tillDelta, tillComment, membershipFees, donations, expenses, moneytaken, takenduringday FROM recshiftclose WHERE closingid = $shiftid";
		try
		{
			$result = $pdo3->prepare("$closingLookup");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
			$closingid = $shiftid;
			$closingtime = $row['closingtime'];
			$tillTot = $row['cashintill'];	
			$tillDelta = $row['tillDelta'];	
			$tillComment = $row['tillComment'];	
			$membershipFees = $row['membershipFees'];	
			$donationsToday = $row['donations'];	
			$expenses = $row['expenses'];	
			$banked = $row['moneytaken'];	
			$bankedDuringDay = $row['takenduringday'];	

	// Determine whether last opening was an opening of day or shift
	$openingLookup = "SELECT openingid, openingtime, tillBalance, 'openshift' AS type FROM recshiftopen WHERE openingtime < '$closingtime' ORDER BY openingtime DESC LIMIT 1";
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
			$openingid = $row['openingid'];
			$openingtime = $row['openingtime'];
			$tillBalance = $row['tillBalance'];	
			$type = $row['type'];	
			

			if ($tillDelta == 0) {
				$tillDelta = '';
			} else if ($tillDelta > 0) {
				$cashintillDeltaColour = "color: green;";
			} else {
				$cashintillDeltaColour = "color: red;";
			}
			
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
				$error = 'Error fetching user: ' . $e->getMessage();
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
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
				$salesTodayBank = $row['SUM(amount)'];
			
			// Look up BAR SALES today cash
			$selectSales = "SELECT SUM(amount) from b_sales WHERE saletime BETWEEN '$openingtime' AND '$closingtime' AND direct < 2";
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
				$salesBarTodayCash = $row['SUM(amount)'];
		
			// Look up BAR SALES today bank
			$selectSales = "SELECT SUM(amount) from b_sales WHERE saletime BETWEEN '$openingtime' AND '$closingtime' AND direct = 2";
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
				$salesBarTodayBank = $row['SUM(amount)'];
			
		}
		
		// Calculate estimated till
		$estimatedTill = $tillBalance + $membershipFees + $donationsToday + $salesTodayCash + $salesBarTodayCash - $expenses - $banked - $bankedDuringDay;
			
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
<br /><div class="textInset">
 <center><strong>{$lang['close-shift-details']}</strong></center><br />
 <table>
  <tr>
   <td style='text-align: left;'>{$lang['shift-opened']}:</td>
   <td style='text-align: left;'>{$openingtimeView}</td>
  </tr>
  <tr>
   <td style='text-align: left;'>{$lang['shift-closed']}:</td>
   <td style='text-align: left;'>{$closingtimeView}</td>
  </tr>
  <tr>
   <td colspan='2'><br /></td>
  </tr>
  <tr>
   <td style='text-align: left;'>{$lang['shift-duration']}:</td>
   <td style='text-align: left;'>{$shiftDuration}</td>
  </tr>
 </table>
</div><br /><br />
EOD;
	
		pageStart($lang['close-shift-details'], NULL, $confirmLeave, "pcloseday", "step4", $lang['close-shift-details'], $_SESSION['successMessage'], $_SESSION['errorMessage']);
		
		
		echo $pageHeader;
		echo "<h3 class='title'>{$lang['global-till']}</h3>";

	?>	
		<form id="registerForm" action="" method="POST" onsubmit="return checkMismatch()"><br />		 
   <table>
    <tr>
	 <td><?php echo $lang['closeday-tillatopening']; ?>:</td>
	 <td><input type="number" lang="nb" name="tillBalance" id="tillBalance" class="fourDigit" value="<?php echo $tillBalance; ?>" readonly /></td>
    </tr>		 
    <tr>
	 <td>+ <?php echo $lang['closeday-membershipfees-till']; ?>:</td>
	 <td><input type="number" lang="nb" name="membershipFees" id="membershipFees" class="green fourDigit" value="<?php echo $membershipFees; ?>" readonly /></td>
    </tr>		 
    <tr>
	 <td>+ <?php echo $lang['global-donations']; ?>:</td>
	 <td><input type="number" lang="nb" name="donationsToday" id="donationsToday" class="green fourDigit" value="<?php echo $donationsToday; ?>" readonly /></td>
    </tr>		 
<?php if ($_SESSION['creditOrDirect'] == 0) { ?>
    <tr>
	 <td>+ <?php echo $lang['dispensed-direct-till']; ?>:</td>
	 <td><input type="number" lang="nb" name="salesTodayCash" id="salesTodayCash" class="green fourDigit" value="<?php echo $salesTodayCash; ?>" readonly /></td>
    </tr>
    <tr>
	 <td>+ <?php echo $lang['direct-bar-sales-till']; ?>:</td>
	 <td><input type="number" lang="nb" name="salesBarTodayCash" id="salesBarTodayCash" class="green fourDigit" value="<?php echo $salesBarTodayCash; ?>" readonly /></td>
    </tr>
<?php } ?>
    <tr>
	 <td>- <?php echo $lang['closeday-tillexpenses']; ?>:</td>
	 <td><input type="number" lang="nb" name="expenses" id="expenses" class="fourDigit red" value="<?php echo $expenses; ?>" readonly /></td>
    </tr>		 
    <tr>
	 <td>- <?php echo $lang['banked-now']; ?>:</td>
	 <td><input type="number" lang="nb" name="bankedNow" id="bankedNow" class="fourDigit red" value="<?php echo $banked; ?>" readonly /></td>
    </tr>
    <tr>
	 <td>- <?php echo $lang['banked-during-shift']; ?>:</td>
	 <td><input type="number" lang="nb" name="bankedDuring" id="bankedDuring" class="fourDigit red" value="<?php echo $bankedDuringDay; ?>" readonly /></td>
    </tr>
    <tr>
	 <td><?php echo $lang['closeday-estimatedtill']; ?>:</td>
	 <td><input type="number" lang="nb" name="estimatedTill" id="estimatedTill" class="fourDigit" value="<?php echo $estimatedTill; ?>" readonly /></td>
    </tr>		 
    <tr>
	 <td><?php echo $lang['closeday-yourcount']; ?>:</td>
	 <td><input type="number" lang="nb" name="tillTot" id="tillTot" class="fourDigit" value="<?php echo $tillTot; ?>" readonly /></td>
    </tr>		 
    <tr>
	 <td><strong><?php echo $lang['global-delta']; ?>:</strong></td>
	 <td><strong><input type="number" lang="nb" name="tillDelta" id="tillDelta" class="fourDigit" value="<?php echo number_format($tillDelta,2,'.',''); ?>" readonly /></strong></td>
    </tr>		 
		 </table><br />
		 <em><?php echo $lang['closeday-tillcomment']; ?>:</em><br />
		 <?php echo $tillComment; ?>
		 <br /><br />

</form>
<?php

	} else if ($shifttype == 4) {
		// start shift
		
		// What if there was no previous shift opened - use open day instead!
		
		// Retrieve open day details
		$openingLookup = "SELECT openingid, openingtime, openedby, tillComment, oneCent, twoCent, fiveCent, tenCent, twentyCent, fiftyCent, oneEuro, twoEuro, fiveEuro, tenEuro, twentyEuro, fiftyEuro, hundredEuro, coinsTot, notesTot, tillBalance FROM recshiftopen WHERE openingid = $shiftid";
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
			$openingid = $shiftid;
			$openingtime = $row['openingtime'];
			$oneCent = $row['oneCent'];
			$twoCent = $row['twoCent'];
			$fiveCent = $row['fiveCent'];
			$tenCent = $row['tenCent'];
			$twentyCent = $row['twentyCent'];
			$fiftyCent = $row['fiftyCent'];
			$oneEuro = $row['oneEuro'];
			$twoEuro = $row['twoEuro'];
			$fiveEuro = $row['fiveEuro'];
			$tenEuro = $row['tenEuro'];
			$twentyEuro = $row['twentyEuro'];
			$fiftyEuro = $row['fiftyEuro'];
			$hundredEuro = $row['hundredEuro'];
			$coinsTot = $row['coinsTot'];
			$notesTot = $row['notesTot'];
			$tillTot = $row['tillBalance'];	
			$tillComment = $row['tillComment'];	
			$responsible = getOperator($row['openedby']);	

	
	
		
		// Retrieve previous close day details
		$closingLookup = "SELECT closingid, closingtime, oneCent, twoCent, fiveCent, tenCent, twentyCent, fiftyCent, oneEuro, twoEuro, fiveEuro, tenEuro, twentyEuro, fiftyEuro, hundredEuro, coinsTot, notesTot, cashintill FROM recshiftclose WHERE closingtime < '$openingtime' ORDER BY closingtime DESC LIMIT 1";
		try
		{
			$result = $pdo3->prepare("$closingLookup");
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
			$closingtime = $row['closingtime'];
			$oneCentYday = $row['oneCent'];
			$twoCentYday = $row['twoCent'];
			$fiveCentYday = $row['fiveCent'];
			$tenCentYday = $row['tenCent'];
			$twentyCentYday = $row['twentyCent'];
			$fiftyCentYday = $row['fiftyCent'];
			$oneEuroYday = $row['oneEuro'];
			$twoEuroYday = $row['twoEuro'];
			$fiveEuroYday = $row['fiveEuro'];
			$tenEuroYday = $row['tenEuro'];
			$twentyEuroYday = $row['twentyEuro'];
			$fiftyEuroYday = $row['fiftyEuro'];
			$hundredEuroYday = $row['hundredEuro'];
			$coinsTotYday = $row['coinsTot'];
			$notesTotYday = $row['notesTot'];
			$cashintillYday = $row['cashintill'];	

			$oneCentDelta = $oneCent - $oneCentYday;
			if ($oneCentDelta == 0) {
				$oneCentDelta = '';
			} else if ($oneCentDelta > 0) {
				$oneCentDeltaColour = "color: green;";
			} else {
				$oneCentDeltaColour = "color: red;";
			}
			
			$twoCentDelta = $twoCent - $twoCentYday;
			if ($twoCentDelta == 0) {
				$twoCentDelta = '';
			} else if ($twoCentDelta > 0) {
				$twoCentDeltaColour = "color: green;";
			} else {
				$twoCentDeltaColour = "color: red;";
			}
			
			$fiveCentDelta = $fiveCent - $fiveCentYday;
			if ($fiveCentDelta == 0) {
				$fiveCentDelta = '';
			} else if ($fiveCentDelta > 0) {
				$fiveCentDeltaColour = "color: green;";
			} else {
				$fiveCentDeltaColour = "color: red;";
			}
			
			$tenCentDelta = $tenCent - $tenCentYday;
			if ($tenCentDelta == 0) {
				$tenCentDelta = '';
			} else if ($tenCentDelta > 0) {
				$tenCentDeltaColour = "color: green;";
			} else {
				$tenCentDeltaColour = "color: red;";
			}
			
			$twentyCentDelta = $twentyCent - $twentyCentYday;
			if ($twentyCentDelta == 0) {
				$twentyCentDelta = '';
			} else if ($twentyCentDelta > 0) {
				$twentyCentDeltaColour = "color: green;";
			} else {
				$twentyCentDeltaColour = "color: red;";
			}
			
			$fiftyCentDelta = $fiftyCent - $fiftyCentYday;
			if ($fiftyCentDelta == 0) {
				$fiftyCentDelta = '';
			} else if ($fiftyCentDelta > 0) {
				$fiftyCentDeltaColour = "color: green;";
			} else {
				$fiftyCentDeltaColour = "color: red;";
			}
			
			$oneEuroDelta = $oneEuro - $oneEuroYday;
			if ($oneEuroDelta == 0) {
				$oneEuroDelta = '';
			} else if ($oneEuroDelta > 0) {
				$oneEuroDeltaColour = "color: green;";
			} else {
				$oneEuroDeltaColour = "color: red;";
			}
			
			$twoEuroDelta = $twoEuro - $twoEuroYday;
			if ($twoEuroDelta == 0) {
				$twoEuroDelta = '';
			} else if ($twoEuroDelta > 0) {
				$twoEuroDeltaColour = "color: green;";
			} else {
				$twoEuroDeltaColour = "color: red;";
			}
			
			$fiveEuroDelta = $fiveEuro - $fiveEuroYday;
			if ($fiveEuroDelta == 0) {
				$fiveEuroDelta = '';
			} else if ($fiveEuroDelta > 0) {
				$fiveEuroDeltaColour = "color: green;";
			} else {
				$fiveEuroDeltaColour = "color: red;";
			}
			
			$tenEuroDelta = $tenEuro - $tenEuroYday;
			if ($tenEuroDelta == 0) {
				$tenEuroDelta = '';
			} else if ($tenEuroDelta > 0) {
				$tenEuroDeltaColour = "color: green;";
			} else {
				$tenEuroDeltaColour = "color: red;";
			}
			
			$twentyEuroDelta = $twentyEuro - $twentyEuroYday;
			if ($twentyEuroDelta == 0) {
				$twentyEuroDelta = '';
			} else if ($twentyEuroDelta > 0) {
				$twentyEuroDeltaColour = "color: green;";
			} else {
				$twentyEuroDeltaColour = "color: red;";
			}
			
			$fiftyEuroDelta = $fiftyEuro - $fiftyEuroYday;
			if ($fiftyEuroDelta == 0) {
				$fiftyEuroDelta = '';
			} else if ($fiftyEuroDelta > 0) {
				$fiftyEuroDeltaColour = "color: green;";
			} else {
				$fiftyEuroDeltaColour = "color: red;";
			}
			
			$hundredEuroDelta = $hundredEuro - $hundredEuroYday;
			if ($hundredEuroDelta == 0) {
				$hundredEuroDelta = '';
			} else if ($hundredEuroDelta > 0) {
				$hundredEuroDeltaColour = "color: green;";
			} else {
				$hundredEuroDeltaColour = "color: red;";
			}
			
			$coinsTotDelta = $coinsTot - $coinsTotYday;
			if ($coinsTotDelta == 0) {
				$coinsTotDelta = '';
			} else if ($coinsTotDelta > 0) {
				$coinsTotDeltaColour = "color: green;";
			} else {
				$coinsTotDeltaColour = "color: red;";
			}
			
			$notesTotDelta = $notesTot - $notesTotYday;
			if ($notesTotDelta == 0) {
				$notesTotDelta = '';
			} else if ($notesTotDelta > 0) {
				$notesTotDeltaColour = "color: green;";
			} else {
				$notesTotDeltaColour = "color: red;";
			}
			
			$cashintillDelta = $tillTot - $cashintillYday;
			if ($cashintillDelta == 0) {
				$cashintillDelta = '';
			} else if ($cashintillDelta > 0) {
				$cashintillDeltaColour = "color: green;";
			} else {
				$cashintillDeltaColour = "color: red;";
			}
			
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
	
	
	$pageHeader = <<<EOD
<br /><div class="textInset">
 <center><strong>{$lang['close-shift-details']}</strong></center><br />
 <table>
  <tr>
   <td style='text-align: left;'>{$lang['last-closing']}:</td>
   <td style='text-align: left;'>{$closingtimeView}</td>
  </tr>
  <tr>
   <td style='text-align: left;'>{$lang['shift-opened']}:</td>
   <td style='text-align: left;'>{$openingtimeView}</td>
  </tr>
  <tr>
   <td style='text-align: left;'>{$lang['opened-by']}:</td>
   <td style='text-align: left;'>{$responsible}</td>
  </tr>
 </table>
</div><br /><br />
EOD;

				
		pageStart($lang['opening-details'] , NULL, $confirmLeave, "pcloseday", "step4", $lang['opening-details'] , $_SESSION['successMessage'], $_SESSION['errorMessage']);
		
	echo $pageHeader;
	echo "<h3 class='title'>{$lang['global-till']}</h3>";

	?>	
		<form id="registerForm" action="" method="POST" onsubmit="return checkMismatch()"><br />
		 <strong><?php echo $lang['closeday-coins']; ?></strong><br />
		
		 <span class="fakelabel short">&nbsp;</span>
		 <input type="text" class="fourDigit" value="1c" readonly style="background-color: white; border-color: white; box-shadow: 0px 0px 0px white; text-align: center;"  />
		 <input type="text" class="fourDigit" value="2c" readonly style="background-color: white; border-color: white; box-shadow: 0px 0px 0px white; text-align: center;"  />
		 <input type="text" class="fourDigit" value="5c" readonly style="background-color: white; border-color: white; box-shadow: 0px 0px 0px white; text-align: center;"  />
		 <input type="text" class="fourDigit" value="10c" readonly style="background-color: white; border-color: white; box-shadow: 0px 0px 0px white; text-align: center;"  />
		 <input type="text" class="fourDigit" value="20c" readonly style="background-color: white; border-color: white; box-shadow: 0px 0px 0px white; text-align: center;"  />
		 <input type="text" class="fourDigit" value="50c" readonly style="background-color: white; border-color: white; box-shadow: 0px 0px 0px white; text-align: center;"  />
		 <input type="text" class="fourDigit" value="1&euro;" readonly style="background-color: white; border-color: white; box-shadow: 0px 0px 0px white; text-align: center;"  />
		 <input type="text" class="fourDigit" value="2&euro;" readonly style="background-color: white; border-color: white; box-shadow: 0px 0px 0px white; text-align: center;"  />
		 <input type="text" class="fourDigit" value="Total" readonly style="background-color: white; border-color: white; box-shadow: 0px 0px 0px white; text-align: center;"  />
		 <br />
		 
		 <span class="fakelabel short"><?php echo $lang['closed-at']; ?></span>
		 <input type="number" lang="nb" name="oneCentYday" id="oneCentYday" class="fourDigit" value="<?php echo $oneCentYday; ?>" readonly style="background-color: white;"  />
		 <input type="number" lang="nb" name="twoCentYday" id="twoCentYday" class="fourDigit" value="<?php echo $twoCentYday; ?>" readonly style="background-color: white;"  /> 
		 <input type="number" lang="nb" name="fiveCentYday" id="fiveCentYday" class="fourDigit" value="<?php echo $fiveCentYday; ?>" readonly style="background-color: white;"  /> 
		 <input type="number" lang="nb" name="tenCentYday" id="tenCentYday" class="fourDigit" value="<?php echo $tenCentYday; ?>" readonly style="background-color: white;"  /> 
		 <input type="number" lang="nb" name="twentyCentYday" id="twentyCentYday" class="fourDigit" value="<?php echo $twentyCentYday; ?>" readonly style="background-color: white;"  /> 
		 <input type="number" lang="nb" name="fiftyCentYday" id="fiftyCentYday" class="fourDigit" value="<?php echo $fiftyCentYday; ?>" readonly style="background-color: white;"  /> 
		 <input type="number" lang="nb" name="oneEuroYday" id="oneEuroYday" class="fourDigit" value="<?php echo $oneEuroYday; ?>" readonly style="background-color: white;"  /> 
		 <input type="number" lang="nb" name="twoEuroYday" id="twoEuroYday" class="fourDigit" value="<?php echo $twoEuroYday; ?>" readonly style="background-color: white;"  />
		 <strong><input type="number" lang="nb" name="coinsTotYday" id="coinsTotYday" class="fourDigit" value="<?php echo $coinsTotYday; ?>" readonly style="background-color: white;"  /></strong>
		 <br />
		 <span class="fakelabel short"><?php echo $lang['opened-at']; ?></span>
		 <input type="number" lang="nb" name="oneCent" id="oneCent" class="fourDigit" value="<?php echo $oneCent; ?>" />
		 <input type="number" lang="nb" name="twoCent" id="twoCent" class="fourDigit" value="<?php echo $twoCent; ?>" /> 
		 <input type="number" lang="nb" name="fiveCent" id="fiveCent" class="fourDigit" value="<?php echo $fiveCent; ?>" /> 
		 <input type="number" lang="nb" name="tenCent" id="tenCent" class="fourDigit" value="<?php echo $tenCent; ?>" /> 
		 <input type="number" lang="nb" name="twentyCent" id="twentyCent" class="fourDigit" value="<?php echo $twentyCent; ?>" /> 
		 <input type="number" lang="nb" name="fiftyCent" id="fiftyCent" class="fourDigit" value="<?php echo $fiftyCent; ?>" /> 
		 <input type="number" lang="nb" name="oneEuro" id="oneEuro" class="fourDigit" value="<?php echo $oneEuro; ?>" /> 
		 <input type="number" lang="nb" name="twoEuro" id="twoEuro" class="fourDigit" value="<?php echo $twoEuro; ?>" />
		 <strong><input type="number" lang="nb" name="coinsTot" id="coinsTot" class="fourDigit" value="<?php echo $coinsTot; ?>" readonly style="background-color: white;"  /></strong><br />
		 <span class="fakelabel short"><?php echo $lang['global-delta']; ?></span>
		 <input type="number" lang="nb" name="oneCentDelta" id="oneCentDelta" class="fourDigit" value="<?php echo $oneCentDelta; ?>" readonly style="background-color: white; <?php echo $oneCentDeltaColour; ?>"  />
		 <input type="number" lang="nb" name="twoCentDelta" id="twoCentDelta" class="fourDigit" value="<?php echo $twoCentDelta; ?>" readonly style="background-color: white; <?php echo $twoCentDeltaColour; ?>"  /> 
		 <input type="number" lang="nb" name="fiveCentDelta" id="fiveCentDelta" class="fourDigit" value="<?php echo $fiveCentDelta; ?>" readonly style="background-color: white; <?php echo $fiveCentDeltaColour; ?>"  /> 
		 <input type="number" lang="nb" name="tenCentDelta" id="tenCentDelta" class="fourDigit" value="<?php echo $tenCentDelta; ?>" readonly style="background-color: white; <?php echo $tenCentDeltaColour; ?>"  /> 
		 <input type="number" lang="nb" name="twentyCentDelta" id="twentyCentDelta" class="fourDigit" value="<?php echo $twentyCentDelta; ?>" readonly style="background-color: white; <?php echo $twentyCentDeltaColour; ?>"  /> 
		 <input type="number" lang="nb" name="fiftyCentDelta" id="fiftyCentDelta" class="fourDigit" value="<?php echo $fiftyCentDelta; ?>" readonly style="background-color: white; <?php echo $fiftyCentDeltaColour; ?>"  /> 
		 <input type="number" lang="nb" name="oneEuroDelta" id="oneEuroDelta" class="fourDigit" value="<?php echo $oneEuroDelta; ?>" readonly style="background-color: white; <?php echo $oneEuroDeltaColour; ?>"  /> 
		 <input type="number" lang="nb" name="twoEuroDelta" id="twoEuroDelta" class="fourDigit" value="<?php echo $twoEuroDelta; ?>" readonly style="background-color: white; <?php echo $twoEuroDeltaColour; ?>"  />
		 <strong><input type="number" lang="nb" name="coinsTotDelta" id="coinsTotDelta" class="fourDigit" value="<?php echo number_format($coinsTotDelta,2); ?>" readonly style="background-color: white; <?php echo $coinsTotDeltaColour; ?>"  /></strong>
		 <br /><br /><br />
		 
		 <strong><?php echo $lang['closeday-notes']; ?></strong><br />
		 <span class="fakelabel short">&nbsp;</span>
		 <input type="text" class="fourDigit" value="5&euro;" readonly style="background-color: white; border-color: white; box-shadow: 0px 0px 0px white; text-align: center;"  />
		 <input type="text" class="fourDigit" value="10&euro;" readonly style="background-color: white; border-color: white; box-shadow: 0px 0px 0px white; text-align: center;"  />
		 <input type="text" class="fourDigit" value="20&euro;" readonly style="background-color: white; border-color: white; box-shadow: 0px 0px 0px white; text-align: center;"  />
		 <input type="text" class="fourDigit" value="50&euro;" readonly style="background-color: white; border-color: white; box-shadow: 0px 0px 0px white; text-align: center;"  />
		 <input type="text" class="fourDigit" value="100&euro;" readonly style="background-color: white; border-color: white; box-shadow: 0px 0px 0px white; text-align: center;"  />
		 <input type="text" class="fourDigit" value="Total" readonly style="background-color: white; border-color: white; box-shadow: 0px 0px 0px white; text-align: center;"  />

		 <br />
		 <span class="fakelabel short"><?php echo $lang['closed-at']; ?></span>
		 <input type="number" lang="nb" name="fiveEuroYday" id="fiveEuroYday" class="fourDigit" value="<?php echo $fiveEuroYday; ?>" readonly style="background-color: white;"  />
		 <input type="number" lang="nb" name="tenEuroYday" id="tenEuroYday" class="fourDigit" value="<?php echo $tenEuroYday; ?>" readonly style="background-color: white;"  /> 
		 <input type="number" lang="nb" name="twentyEuroYday" id="twentyEuroYday" class="fourDigit" value="<?php echo $twentyEuroYday; ?>" readonly style="background-color: white;"  /> 
		 <input type="number" lang="nb" name="fiftyEuroYday" id="fiftyEuroYday" class="fourDigit" value="<?php echo $fiftyEuroYday; ?>" readonly style="background-color: white;"  /> 
		 <input type="number" lang="nb" name="hundredEuroYday" id="hundredEuroYday" class="fourDigit" value="<?php echo $hundredEuroYday; ?>" readonly style="background-color: white;"  />
		 <strong><input type="number" lang="nb" name="notesTotYday" id="notesTotYday" class="fourDigit" value="<?php echo $notesTotYday; ?>" readonly style="background-color: white;"  /></strong>
		 <br />
		 <span class="fakelabel short"><?php echo $lang['opened-at']; ?></span>
		 <input type="number" lang="nb" name="fiveEuro" id="fiveEuro" class="fourDigit" value="<?php echo $fiveEuro; ?>" />
		 <input type="number" lang="nb" name="tenEuro" id="tenEuro" class="fourDigit" value="<?php echo $tenEuro; ?>" /> 
		 <input type="number" lang="nb" name="twentyEuro" id="twentyEuro" class="fourDigit" value="<?php echo $twentyEuro; ?>" /> 
		 <input type="number" lang="nb" name="fiftyEuro" id="fiftyEuro" class="fourDigit" value="<?php echo $fiftyEuro; ?>" /> 
		 <input type="number" lang="nb" name="hundredEuro" id="hundredEuro" class="fourDigit" value="<?php echo $hundredEuro; ?>" />
		 <strong><input type="number" lang="nb" name="notesTot" id="notesTot" class="fourDigit" value="<?php echo $notesTot; ?>" readonly style="background-color: white;"  /></strong><br />
		 <span class="fakelabel short"><?php echo $lang['global-delta']; ?></span>
		 <input type="number" lang="nb" name="fiveEuroDelta" id="fiveEuroDelta" class="fourDigit" value="<?php echo $fiveEuroDelta; ?>" readonly style="background-color: white; <?php echo $fiveEuroDeltaColour; ?>"  />
		 <input type="number" lang="nb" name="tenEuroDelta" id="tenEuroDelta" class="fourDigit" value="<?php echo $tenEuroDelta; ?>" readonly style="background-color: white; <?php echo $tenEuroDeltaColour; ?>"  /> 
		 <input type="number" lang="nb" name="twentyEuroDelta" id="twentyEuroDelta" class="fourDigit" value="<?php echo $twentyEuroDelta; ?>" readonly style="background-color: white; <?php echo $twentyEuroDeltaColour; ?>"  /> 
		 <input type="number" lang="nb" name="fiftyEuroDelta" id="fiftyEuroDelta" class="fourDigit" value="<?php echo $fiftyEuroDelta; ?>" readonly style="background-color: white; <?php echo $fiftyEuroDeltaColour; ?>"  /> 
		 <input type="number" lang="nb" name="hundredEuroDelta" id="hundredEuroDelta" class="fourDigit" value="<?php echo $hundredEuroDelta; ?>" readonly style="background-color: white; <?php echo $hundredEuroDeltaColour; ?>"  />
		 <strong><input type="number" lang="nb" name="notesTotDelta" id="notesTotDelta" class="fourDigit" value="<?php echo number_format($notesTotDelta,2); ?>" readonly style="background-color: white; <?php echo $notesTotDeltaColour; ?>"  /></strong>
		 <br /><br /><br />
		 
		 <table>
		  <tr>
		   <td><?php echo $lang['till-closed-at']; ?>:</td>
		   <td><input type="number" lang="nb" name="cashintillYday" id="cashintillYday" class="fourDigit" value="<?php echo $cashintillYday; ?>" readonly style="background-color: white;"  /></td>
		  </tr>
		  <tr>
		   <td><?php echo $lang['till-opened-at']; ?>:</td>
		   <td><input type="number" lang="nb" name="tillTot" id="tillTot" class="fourDigit" value="<?php echo $tillTot; ?>" readonly style="background-color: white;"  /></td>
		  </tr>
		  <tr>
		   <td><?php echo $lang['global-delta']; ?>:</td>
		   <td><input type="number" lang="nb" name="cashintillDelta" id="cashintillDelta" class="fourDigit" value="<?php echo number_format($cashintillDelta,2); ?>" readonly style="background-color: white; <?php echo $cashintillDeltaColour; ?>"  /></td>
		  </tr>
		 </table><br />
		 <em><?php echo $lang['closeday-tillcomment']; ?>:</em><br />
		 <?php echo $tillComment; ?>
		 <br /><br />
</form>
<?php
	} else {
		echo "Shift details missing. Critical error.";
		exit();
	}
	
