<?php
session_start();
require_once 'cOnfig/connection.php';
require_once 'cOnfig/view.php';
require_once 'cOnfig/authenticate.php';
require_once 'cOnfig/languages/common.php';
$accessLevel = '1';
	// Authenticate & authorize
authorizeUser($accessLevel);
if (isset($_GET['user_id'])) {
	$user_id = $_GET['user_id'];
} else {
	$user_id = $_SESSION['user_id'];
}
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
	// Members signed up
$selectMembers = "SELECT COUNT(id) from log WHERE logtype = 12 AND operator = $user_id AND (logtime BETWEEN '$openingTime' AND '$closingTime')";
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
$newmembers = $row['COUNT(id)'];
	// Members signed up for free
$selectMembers = "SELECT COUNT(id) from log WHERE logtype = 12 AND amount = 0 AND operator = $user_id AND (logtime BETWEEN '$openingTime' AND '$closingTime')";
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
$newmembersfree = $row['COUNT(id)'];
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
$sortScript = <<<EOD
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
EOD;
pageStart($lang['worker-module'], NULL, $sortScript, "pnewcategory", "", $lang['worker-module'], $_SESSION['successMessage'], $_SESSION['errorMessage']);
try
{
	$result = $pdo3->prepare("SELECT user_id, memberno, first_name, last_name, userGroup FROM users WHERE userGroup < 4");
	$result->execute();
}
catch (PDOException $e)
{
	$error = 'Error fetching user: ' . $e->getMessage();
	echo $error;
	exit();
}
while ($row = $result->fetch()) {
	$name = $row['first_name'] . " " . $row['last_name'];
	$userid = $row['user_id'];
	if ($user_id != $userid) {
		    	// $listcontent .= "<option value='$userid'>$name</option><br />";
		$listcontent .= "<a href='{$siteroot}worker-module.php?user_id=$userid'>$name</a><br />";
	} else {
		$curruser = $name;
	} 
}
// Fetch user dispensed data 
  $selectUserSales = "SELECT SUM(amount), SUM(quantity), SUM(realQuantity), SUM(units), SUM(amountpaid) from sales WHERE operatorid ='$user_id' AND (saletime BETWEEN '$openingTime' AND '$closingTime')";
try
{
	$result = $pdo3->prepare("$selectUserSales");
	$result->execute();
}
catch (PDOException $e)
{
	$error = 'Error fetching user: ' . $e->getMessage();
	echo $error;
	exit();
}
$salesRow = $result->fetch();
$userDispensedQuantity = $salesRow['SUM(quantity)'];
$userDispensedRealquant = $salesRow['SUM(realQuantity)'];
$userDispensedUnit = $salesRow['SUM(units)'];
$userDispensedAmount = $salesRow['SUM(amount)'];
$userDispensedAmountPaid = $salesRow['SUM(amountpaid)'];
// Fetch user bar data 
  $selectUserBarSales = "SELECT SUM(amount), SUM(unitsTot) from b_sales WHERE operatorid ='$user_id' AND (saletime BETWEEN '$openingTime' AND '$closingTime')";
try
{
	$result = $pdo3->prepare("$selectUserBarSales");
	$result->execute();
}
catch (PDOException $e)
{
	$error = 'Error fetching user: ' . $e->getMessage();
	echo $error;
	exit();
}
$barsalesRow = $result->fetch();
$userBardUnit = $barsalesRow['SUM(unitsTot)'];
$userBarAmount = $barsalesRow['SUM(amount)'];
// Fetch club dispensed data
 $selectClubSales = "SELECT SUM(amount), SUM(quantity), SUM(realQuantity), SUM(units) from sales  WHERE saletime BETWEEN '$openingTime' AND '$closingTime'";
try
{
	$result = $pdo3->prepare("$selectClubSales");
	$result->execute();
}
catch (PDOException $e)
{
	$error = 'Error fetching user: ' . $e->getMessage();
	echo $error;
	exit();
}
$clubRow = $result->fetch();
$clubDispensedQuantity = $clubRow['SUM(quantity)'];
$clubDispensedRealquant = $clubRow['SUM(realQuantity)'];
$clubDispensedUnit = $clubRow['SUM(units)'];
$clubDispensedAmount = $clubRow['SUM(amount)'];
// Fetch club bar data
  $selectClubBarSales = "SELECT SUM(amount), SUM(unitsTot) from b_sales WHERE saletime BETWEEN '$openingTime' AND '$closingTime'";
try
{
	$result = $pdo3->prepare("$selectClubBarSales");
	$result->execute();
}
catch (PDOException $e)
{
	$error = 'Error fetching user: ' . $e->getMessage();
	echo $error;
	exit();
}
$barclubRow = $result->fetch();
$clubBardUnit = $barclubRow['SUM(unitsTot)'];
$clubBarAmount = $barclubRow['SUM(amount)'];
// Get differences 
$selectSalesDiff =  "SELECT SUM(d.quantity), SUM(d.realQuantity), SUM(s.amount), SUM(s.amountpaid) from sales s, salesdetails d WHERE s.saletime BETWEEN '$openingTime' AND '$closingTime' AND s.operatorid = '$user_id' AND s.saleid=d.saleid";
try
{
	$result = $pdo3->prepare("$selectSalesDiff");
	$result->execute();
}
catch (PDOException $e)
{
	$error = 'Error fetching user: ' . $e->getMessage();
	echo $error;
	exit();
}
$diffRow = $result->fetch();
 $usertotalQuantity = $diffRow['SUM(d.quantity)'];
 $userrealQuantity = $diffRow['SUM(d.realQuantity)'];
 $userdiffQuantity = $usertotalQuantity - $userrealQuantity;
 $userPercentageDiff = ($userdiffQuantity / $usertotalQuantity) * 100;
 // Get amount diff
  $getAmountSales = "SELECT d.purchaseid, d.quantity, d.realQuantity, m.salesPrice from sales s, salesdetails d, purchases m WHERE s.operatorid ='$user_id' AND (s.saletime BETWEEN '$openingTime' AND '$closingTime') AND s.saleid=d.saleid AND m.purchaseid = d.purchaseid";
try
{
	$result = $pdo3->prepare("$getAmountSales");
	$result->execute();
}
catch (PDOException $e)
{
	$error = 'Error fetching user: ' . $e->getMessage();
	echo $error;
	exit();
}
$totalPrice = 0;
$realtotalPrice = 0;
while($amountRow = $result->fetch()){
	$amQuantity = $amountRow['quantity'];
	$amRealQuantity = $amountRow['realQuantity'];
	$amsalesPrice = $amountRow['salesPrice'];
	 $gramPrice = $amQuantity * $amsalesPrice;
	 $totalPrice = $totalPrice + $gramPrice;
	 $realgramPrice = $amRealQuantity * $amsalesPrice;
	 $realtotalPrice = $realtotalPrice + $realgramPrice;
}
 $userAmountDiff =  $totalPrice - $realtotalPrice;
if(is_nan($userPercentageDiff)){
	$userPercentageDiff = 0;
}
// Get user ids of club 
$getClubUsers = "SELECT DISTINCT s.userid from sales s,users d WHERE (s.saletime BETWEEN '$openingTime' AND '$closingTime')";
 try
{
	$result = $pdo3->prepare("$getClubUsers");
	$result->execute();
}
catch (PDOException $e)
{
	$error = 'Error fetching user: ' . $e->getMessage();
	echo $error;
	exit();
}
;
while($getClubuserIDs = $result->fetch()){
	$getclubUserId[] = $getClubuserIDs['userid'];
}
  $clubUserIds = implode($getclubUserId,',');
if(empty($clubUserIds) || $clubUserIds == ''){
	$clubUserIds = 0;
}
//  Get club averages
    $selectClubDiff =  "SELECT AVG(quantity), AVG(realQuantity), AVG(amount), AVG(amountpaid) from sales  WHERE saletime BETWEEN '$openingTime' AND '$closingTime'";
try
{
	$result = $pdo3->prepare("$selectClubDiff");
	$result->execute();
}
catch (PDOException $e)
{
	$error = 'Error fetching user: ' . $e->getMessage();
	echo $error;
	exit();
}
$clubDiffRow = $result->fetch();
$clubtotalQuantity = $clubDiffRow['AVG(quantity)'];
$clubrealQuantity = $clubDiffRow['AVG(realQuantity)'];
$clubDiffQuantity = $clubtotalQuantity - $clubrealQuantity;
$clubPercentageDiff = ($clubDiffQuantity / $clubtotalQuantity) * 100;
 // Get club amount diff
  $getClubAmountSales = "SELECT AVG(d.quantity), AVG(d.realQuantity), AVG(m.salesPrice) from sales s, salesdetails d, purchases m WHERE (s.saletime BETWEEN '$openingTime' AND '$closingTime') AND s.saleid=d.saleid AND m.purchaseid = d.purchaseid";
try
{
	$result = $pdo3->prepare("$getClubAmountSales");
	$result->execute();
}
catch (PDOException $e)
{
	$error = 'Error fetching user: ' . $e->getMessage();
	echo $error;
	exit();
}
$clubamountRow = $result->fetch();
	$clubQuantity = $clubamountRow['AVG(d.quantity)'];
	$clubRealQuantity = $clubamountRow['AVG(d.realQuantity)'];
	$clubsalesPrice = $clubamountRow['AVG(m.salesPrice)'];
	$totalCLubPrice = $clubQuantity * $clubsalesPrice;
	$totalRealCLubPrice = $clubRealQuantity * $clubsalesPrice;
$clubAmountDiff =  $totalCLubPrice - $totalRealCLubPrice;
if(is_nan($clubPercentageDiff)){
	$clubPercentageDiff = 0;
}
// Get user ids of worker 
  $getWorkers = "SELECT DISTINCT s.userid from sales s,users d WHERE s.userid = d.user_id AND d.userGroup = 2 AND (s.saletime BETWEEN '$openingTime' AND '$closingTime')";
 try
{
	$result = $pdo3->prepare("$getWorkers");
	$result->execute();
}
catch (PDOException $e)
{
	$error = 'Error fetching user: ' . $e->getMessage();
	echo $error;
	exit();
}
;
while($getWorkerIds = $result->fetch()){
	$getWorkerId[] = $getWorkerIds['userid'];
}
   $workerIds = implode($getWorkerId,',');
if(empty($workerIds) || $workerIds == ''){
	$workerIds = 0;
}
// Get worker averages
   $selectWorkerDiff =  "SELECT AVG(quantity), AVG(realQuantity), AVG(amount), AVG(amountpaid) from sales  WHERE saletime BETWEEN '$openingTime' AND '$closingTime' AND userid IN ($workerIds)";
try
{
	$result = $pdo3->prepare("$selectWorkerDiff");
	$result->execute();
}
catch (PDOException $e)
{
	$error = 'Error fetching user: ' . $e->getMessage();
	echo $error;
	exit();
}
$workerDiffRow = $result->fetch();
$workertotalQuantity = $workerDiffRow['AVG(quantity)'];
$workerrealQuantity = $workerDiffRow['AVG(realQuantity)'];
$workerDiffQuantity = $workertotalQuantity - $workerrealQuantity;
$workerPercentageDiff = ($workerDiffQuantity / $workertotalQuantity) * 100;
 // Get worker amount diff
  $getWorkerAmountSales = "SELECT AVG(d.quantity), AVG(d.realQuantity), AVG(m.salesPrice) from sales s, salesdetails d, purchases m WHERE s.userid  IN ($workerIds) AND (s.saletime BETWEEN '$openingTime' AND '$closingTime') AND s.saleid=d.saleid AND m.purchaseid = d.purchaseid";
try
{
	$result = $pdo3->prepare("$getWorkerAmountSales");
	$result->execute();
}
catch (PDOException $e)
{
	$error = 'Error fetching user: ' . $e->getMessage();
	echo $error;
	exit();
}
$workeramountRow = $result->fetch();
	$workerQuantity = $workeramountRow['AVG(d.quantity)'];
	$workerRealQuantity = $workeramountRow['AVG(d.realQuantity)'];
	$workersalesPrice = $workeramountRow['AVG(m.salesPrice)'];
     $totalworkerPrice = $workerQuantity * $workersalesPrice;
	$totalRealworkerPrice = $workerRealQuantity * $workersalesPrice;
 $workerAmountDiff =  $totalworkerPrice - $totalRealworkerPrice;
// Get user ids of volunteer 
  $getVolunteers = "SELECT DISTINCT s.userid from sales s,users d WHERE s.userid = d.user_id AND d.userGroup = 3 AND (s.saletime BETWEEN '$openingTime' AND '$closingTime')";
 try
{
	$result = $pdo3->prepare("$getVolunteers");
	$result->execute();
}
catch (PDOException $e)
{
	$error = 'Error fetching user: ' . $e->getMessage();
	echo $error;
	exit();
}
;
while($getvolunteerIds = $result->fetch()){
	$getvolunteerId[] = $getvolunteerIds['userid'];
}
   $volunteerIds = implode($getvolunteerId,',');
   if(empty($volunteerIds) || $volunteerIds == ''){
   		$volunteerIds = 0;
   }
// Get volunteer averages
  $selectvolunteerDiff =  "SELECT AVG(quantity), AVG(realQuantity), AVG(amount), AVG(amountpaid) from sales WHERE saletime BETWEEN '$openingTime' AND '$closingTime' AND userid IN ($volunteerIds)";
try
{
	$result = $pdo3->prepare("$selectvolunteerDiff");
	$result->execute();
}
catch (PDOException $e)
{
	$error = 'Error fetching user: ' . $e->getMessage();
	echo $error;
	exit();
}
$volunteerDiffRow = $result->fetch();
$volunteertotalQuantity = $volunteerDiffRow['AVG(quantity)'];
$volunteerrealQuantity = $volunteerDiffRow['AVG(realQuantity)'];
$volunteerDiffQuantity = $volunteertotalQuantity - $volunteerrealQuantity;
$volunteerPercentageDiff = ($volunteerDiffQuantity / $volunteertotalQuantity) * 100;
 // Get volunteer amount diff
  $getVolunteerAmountSales = "SELECT AVG(d.quantity), AVG(d.realQuantity), AVG(m.salesPrice) from sales s, salesdetails d, purchases m WHERE s.userid  IN ($volunteerIds) AND (s.saletime BETWEEN '$openingTime' AND '$closingTime') AND s.saleid=d.saleid AND m.purchaseid = d.purchaseid";
try
{
	$result = $pdo3->prepare("$getVolunteerAmountSales");
	$result->execute();
}
catch (PDOException $e)
{
	$error = 'Error fetching user: ' . $e->getMessage();
	echo $error;
	exit();
}
$volunteeramountRow = $result->fetch();
	$volunteerQuantity = $volunteeramountRow['AVG(d.quantity)'];
	$volunteerRealQuantity = $volunteeramountRow['AVG(d.realQuantity)'];
	$volunteersalesPrice = $volunteeramountRow['AVG(m.salesPrice)'];
	$totalvolunteerPrice = $volunteerQuantity * $volunteersalesPrice;
	$totalRealvolunteerPrice = $volunteerRealQuantity * $volunteersalesPrice;
$volunteerAmountDiff =  $totalvolunteerPrice - $totalRealvolunteerPrice;
?>
<center>
	<div id="mainbox-no-width">
		<div id="mainboxheader">
			<?php echo $curruser; ?>
			<span class='relativeitem'>
				<a href='#' id='chooseOther' class='usergrouptext' style='width: initial; padding: 2px 5px; font-size: 12px; margin-left: 10px; position: absolute; bottom: -12px;'><?php echo $lang['change']; ?></a>
				<span id="stafflist2">
					<?php echo $listcontent; ?>
				</span>
			</span>
		</div>
		<div class='boxcontent'>
			<form action='' method='POST'>
				<?php
				if (isset($_POST['fromDate'])) {
					echo <<<EOD
					<input type="text" id="datepicker" name="fromDate" autocomplete="nope" class="sixDigit defaultinput" value="{$_POST['fromDate']}" />
					<input type="text" id="datepicker2" name="untilDate" autocomplete="nope" class="sixDigit defaultinput" value="{$_POST['untilDate']}" onchange='this.form.submit()' />
					<button type="submit" class='cta1' style='display: inline-block; width: 50px; border: 0;'>OK</button>
					EOD;
				} else {
					echo <<<EOD
					<input type="text" id="datepicker" name="fromDate" autocomplete="nope" class="sixDigit defaultinput" value="$openingTime" />
					<input type="text" id="datepicker2" name="untilDate" autocomplete="nope" class="sixDigit defaultinput" value="$closingTime" onchange='this.form.submit()' />
					<button type="submit" class='cta1' style='display: inline-block; width: 50px; border: 0;'>OK</button>
					EOD;
				}
				?>
			</form>
			<br />
			<table class='default' style='display: inline-block; margin-right: 20px;'>
				<tr>
					<th colspan='7'><center><strong>DISPENSARY</strong></center></th>
				</tr>
				<tr>
					<td></td>
					<td><strong><?php echo $curruser; ?></strong></td>
					<td></td>
					<td><strong>Club total</strong></td>
					<td></td>
					<td></td>
					<td></td>
				</tr>
				<?php  
					// calculate percentage
					$dispensedQuantityPer = ($userDispensedQuantity / $clubDispensedQuantity) * 100;
					if(is_nan($dispensedQuantityPer) || is_infinite($dispensedQuantityPer)){
						$dispensedQuantityPer = 0;
					}

				  ?>
				<tr>
					<td class='left'>Dispensed g</td>
					<td class='right'><?php echo number_format($userDispensedQuantity, 2); ?> g</td>
					<td>(<?php echo number_format($dispensedQuantityPer, 2); ?>  %)</td>
					<td class='right'><?php echo number_format($clubDispensedQuantity, 2); ?> g</td>
					<td></td>
					<td></td>
					<td></td>
				</tr>		<!-- $salesToday = $row['SUM(amount)'];
				$quantitySold = $row['SUM(quantity)'];
				$quantitySoldReal = $row['SUM(realQuantity)'];
				$unitsSold = $row['SUM(units)']; -->
				<?php  
					// calculate percentage
					$dispensedRealQuantPer = ($userDispensedRealquant / $clubDispensedRealquant) * 100;
					if(is_nan($dispensedRealQuantPer) || is_infinite($dispensedRealQuantPer)){
						$dispensedRealQuantPer = 0;
					}
				  ?>
				<tr>
					<td class='left'>Dispensed real g</td>
					<td class='right'><?php echo number_format($userDispensedRealquant, 2); ?> g</td>
					<td>(<?php echo number_format($dispensedRealQuantPer, 2); ?> %)</td>
					<td class='right'><?php echo number_format($clubDispensedRealquant, 2); ?> g</td>
					<td></td>
					<td></td>
					<td></td>
				</tr>
				<?php  
					// calculate percentage
					$dispensedUnittPer = ($userDispensedUnit / $clubDispensedUnit) * 100;
					if(is_nan($dispensedUnittPer) || is_infinite($dispensedUnittPer)){
						$dispensedUnittPer = 0;
					}

				  ?>
				<tr>
					<td class='left'>Dispensed u</td>
					<td class='right'><?php echo number_format($userDispensedUnit, 2); ?> u</td>
					<td>(<?php echo number_format($dispensedUnittPer, 2); ?> %)</td>
					<td class='right'><?php echo number_format($clubDispensedUnit, 2); ?> u</td>
					<td></td>
					<td></td>
					<td></td>
				</tr>
				<?php  
					// calculate percentage
					$dispensedAmountPer = ($userDispensedAmount / $clubDispensedAmount) * 100;
					if(is_nan($dispensedAmountPer) || is_infinite($dispensedAmountPer)){
						$dispensedAmountPer = 0;
					}
				  ?>
				<tr>
					<td class='left'>Dispensed <?php echo $_SESSION['currencyoperator'] ?></td>
					<td class='right'><?php echo number_format($userDispensedAmount, 2); ?> <?php echo $_SESSION['currencyoperator'] ?></td>
					<td>(<?php echo number_format($dispensedAmountPer, 2); ?> %)</td>
					<td class='right'><?php echo number_format($clubDispensedAmount, 2); ?> <?php echo $_SESSION['currencyoperator'] ?></td>
					<td></td>
					<td></td>
					<td></td>
				</tr>
				<tr>
					<td colspan='7' style='border: 0;'></td>
				</tr>
				<tr>
					<td></td>
					<td><strong><?php echo $curruser; ?></strong></td>
					<td></td>
					<td><strong>Club avg.</strong></td>
					<td></td>
					<td><strong>Worker avg.</strong></td>
					<td><strong>Volunteer avg.</strong></td>
				</tr>
				<tr>
					<td class='left'>Difference g. / real g.</td>
					<td class='right'><?php echo number_format($userdiffQuantity, 2); ?> g</td>
					<td class='left'>(<?php echo number_format($userPercentageDiff, 2, '.', ''); ?> %)</td>
					<td class='right'><?php echo number_format($clubDiffQuantity, 2); ?> g</td>
					<td class='left'>(<?php echo number_format($clubPercentageDiff, 2, '.', ''); ?> %)</td>
					<td class='right'><?php echo number_format($workerDiffQuantity, 2); ?> g</td>
					<td class='right'><?php echo number_format($volunteerDiffQuantity, 2); ?> g</td>
				</tr>
				<tr>
					<td class='left'>Difference in <?php echo $_SESSION['currencyoperator'] ?></td>
					<td class='right'><?php echo number_format($userAmountDiff, 2); ?> <?php echo $_SESSION['currencyoperator'] ?></td>
					<td class='right'></td>
					<td class='right'><?php echo number_format($clubAmountDiff, 2); ?> <?php echo $_SESSION['currencyoperator'] ?></td>
					<td class='right'></td>
					<td class='right'><?php echo number_format($workerAmountDiff, 2); ?> <?php echo $_SESSION['currencyoperator'] ?></td>
					<td class='right'><?php echo number_format($volunteerAmountDiff, 2); ?> <?php echo $_SESSION['currencyoperator'] ?></td>
				</tr>
			</table>
			<table class='default' style='display: inline-block; vertical-align: top;'>
				<tr>
					<th colspan='3'><center><strong>BAR</strong></center></th>
				</tr>
				<tr>
					<td></td>
					<td><strong><?php echo $curruser; ?></strong></td>
					<td><strong>Club total</strong></td>
				</tr>
				<tr>
					<td class='left'>Sold u.</td>
					<td class='right'><?php echo number_format($userBardUnit, 2); ?> u</td>
					<td class='right'><?php echo number_format($clubBardUnit, 2) ?> u</td>
				</tr>
				<tr>
					<td class='left'>Sold <?php echo $_SESSION['currencyoperator'] ?></td>
					<td class='right'><?php echo number_format($userBarAmount, 2); ?> <?php echo $_SESSION['currencyoperator'] ?></td>
					<td class='right'><?php echo number_format($clubBarAmount, 2); ?> <?php echo $_SESSION['currencyoperator'] ?></td>
				</tr>
			</table>
			<br /><br />
			<?php
					// changed expiry members
			 $selectMembers = "SELECT COUNT(id) from log WHERE logtype = 8 AND operator = $user_id AND (logtime BETWEEN '$openingTime' AND '$closingTime')";
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
				$changedexpirymembers = $row['COUNT(id)'];
				
			// changed credit
			 $selectMembers = "SELECT COUNT(id), SUM(newCredit) from log WHERE logtype = 5 AND operator = $user_id AND (logtime BETWEEN '$openingTime' AND '$closingTime')";
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
				$changedcreditmembers = $row['COUNT(id)'];
				$totalchangedcredit = $row['SUM(newCredit)'];		
			// chips sold
			  $selectChips = "SELECT COUNT(id), SUM(amount) from card_purchase WHERE operatorid = $user_id AND (time BETWEEN '$openingTime' AND '$closingTime')";
				try
				{
					$result = $pdo3->prepare("$selectChips");
					$result->execute();
				}
				catch (PDOException $e)
				{
					$error = 'Error fetching user: ' . $e->getMessage();
					echo $error;
					exit();
				}
				$chipRow = $result->fetch();	
				$noOfchips = $chipRow['COUNT(id)'];
				$totalchipsAmount = $chipRow['SUM(amount)'];
				// Get last chips amount
			    $getChips = "SELECT amount from card_purchase WHERE operatorid = $user_id AND amount !='0.00' AND (time BETWEEN '$openingTime' AND '$closingTime') limit 1";
				try
				{
					$result = $pdo3->prepare("$getChips");
					$result->execute();
				}
				catch (PDOException $e)
				{
					$error = 'Error fetching user: ' . $e->getMessage();
					echo $error;
					exit();
				}
				$getChipRow = $result->fetch();
				$chipAmount = $getChipRow['amount']; 
				// Get free chips
				 $getFreeChips =  "SELECT COUNT(id) from card_purchase WHERE operatorid = $user_id AND amount = '0.00' AND (time BETWEEN '$openingTime' AND '$closingTime')";
				try
				{
					$result = $pdo3->prepare("$getFreeChips");
					$result->execute();
				}
				catch (PDOException $e)
				{
					$error = 'Error fetching user: ' . $e->getMessage();
					echo $error;
					exit();
				}
				$freeChipRow = $result->fetch();
			$giftedChips =$freeChipRow['COUNT(id)']; 
			$lossChips = $giftedChips * $chipAmount;
			?>
			<table class='default' style='display: inline-block; vertical-align: top; margin-right: 20px;'>
				<tr>
					<th colspan='2'><center><strong>RECEPTION</strong></center></th>
				</tr>
				<tr>
					<td class='left'>Members signed up</td>
					<td><?php echo $newmembers; ?></td>
				</tr>
				<tr>
					<td class='left'>Members signed up for free</td>
					<td><?php echo $newmembersfree; ?></td>
				</tr>
				<tr>
					<td class='left'># of changed expiration date</td>
					<td><?php echo $changedexpirymembers; ?></td>
				</tr>
				<tr>
					<td class='left'># of changed credit</td>
					<td><?php echo $changedcreditmembers; ?></td>
				</tr>
				<tr>
					<td class='left'>Changed credit total in <?php echo $_SESSION['currencyoperator'] ?></td>
					<td><?php echo number_format($totalchangedcredit, 2); ?> <?php echo $_SESSION['currencyoperator'] ?></td>
				</tr>
				<tr>
					<td class='left'># of chips sold</td>
					<td><?php echo $noOfchips; ?></td>
				</tr>
				<tr>
					<td class='left'>Chips sold <?php echo $_SESSION['currencyoperator'] ?></td>
					<td><?php echo number_format($totalchipsAmount, 2); ?> <?php echo $_SESSION['currencyoperator'] ?></td>
				</tr>
				<tr>
					<td class='left'>Chips gifted</td>
					<td><?php echo $giftedChips; ?></td>
				</tr>
				<tr>
					<td class='left'><?php echo $_SESSION['currencyoperator'] ?> loss due to free chips</td>
					<td><?php echo number_format($lossChips, 2); ?> <?php echo $_SESSION['currencyoperator'] ?></td>
				</tr>
			</table>
			<?php
			// Shifts table data
			 $selectOpenings = "SELECT 'Cerrar dia' AS type, 'CloseDay' AS typeshort, closingid AS openingid, closingtime AS shiftStart, cashintill AS tillBalance, tillDelta, closedby AS openedby, stockDelta, prodStock, prodStockFlower, prodStockExtract, stockDeltaFlower, stockDeltaExtract, recClosed, disClosed, dis2Closed FROM closing where closedby ='$user_id' AND (openingtime BETWEEN '$openingTime' AND '$closingTime')  UNION ALL SELECT 'Abrir dia' AS type, 'OpenDay' AS typeshort, openingid, openingtime AS shiftStart, tillBalance, tillDelta, openedby, stockDelta, prodStock, prodStockFlower, prodStockExtract, stockDeltaFlower, stockDeltaExtract, recClosed, 	disClosed, dis2Closed FROM opening where openedby = '$user_id' AND (openingtime BETWEEN '$openingTime' AND '$closingTime') UNION ALL SELECT 'Cerrar turno' AS type, 'CloseShift' AS typeshort, closingid AS openingid, shiftEnd AS shiftStart, cashintill AS tillBalance, tillDelta, closedby AS openedby, stockDelta, prodStock, prodStockFlower, prodStockExtract, stockDeltaFlower, stockDeltaExtract, recOpened AS recClosed, disOpened AS disClosed, dis2Opened AS dis2Closed FROM shiftclose where closedby ='$user_id' AND (closingtime BETWEEN '$openingTime' AND '$closingTime') UNION ALL SELECT 'Comenzar turno' AS type, 'StartShift' AS typeshort, openingid, openingtime AS shiftStart, tillBalance, tillDelta, openedby, stockDelta, prodStock, prodStockFlower, prodStockExtract, stockDeltaFlower, stockDeltaExtract, recClosed, disClosed, dis2Closed FROM shiftopen where openedby = '$user_id'  AND (openingtime BETWEEN '$openingTime' AND '$closingTime') ORDER by shiftStart DESC";
				try
				{
					$results = $pdo3->prepare("$selectOpenings");
					$results->execute();
				}
				catch (PDOException $e)
				{
						$error = 'Error fetching user: ' . $e->getMessage();
						echo $error;
						exit();
				}
					$allShifts = $results->fetchAll();
				
			 $noOfShifts = count($allShifts);	
			 $shiftDataArr = array();
			
			foreach ($allShifts as $shift ) {
					 $shiftsort = $shift['typeshort'];
					 if($shiftsort == "OpenDay"){
					 	$startday = $shift['shiftStart'];
					 	$shiftDataArr['startshift'][] = $startday;
					 }else if($shiftsort == "CloseDay"){
					 	$closeday = $shift['shiftStart'];
					 	$shiftDataArr['closeshift'][] = $closeday;
					 }else if($shiftsort == "CloseShift"){
					 	$closeshift = $shift['shiftStart'];
					 	$shiftDataArr['closeshift'][] = $closeshift;
					 	
					 }else if($shiftsort == "StartShift"){
					 	$startshift = $shift['shiftStart']; 
					 	$shiftDataArr['startshift'][]  = $startshift;
					 }
					 //Reception closed	
					if($shift['recClosed'] > 0){
						$shiftDataArr['recClosed'][] = $shift['recClosed'];
					}					
					if($shift['disClosed'] > 0){
						$shiftDataArr['disClosed'][] = $shift['disClosed'];
					}
					if($shift['dis2Closed'] > 0){
						$shiftDataArr['dis2Closed'][] = $shift['dis2Closed'];
					}
					$shiftDataArr['euroDelta'][]  = $shift['tillDelta'];
					$shiftDataArr['gramDelta'][]  = $shift['stockDelta'];
					$shiftDataArr['gramDeltaFlower'][]  = $shift['stockDeltaFlower'];
					$shiftDataArr['gramDeltaExtract'][]  = $shift['stockDeltaExtract'];
					
			}
			
			// Difference between shifts
			$shift1 = end($shiftDataArr['startshift']);
		    $shift2 = current($shiftDataArr['closeshift']);
			 $hourdiff = round((strtotime($shift2) - strtotime($shift1))/3600, 1);
			
			 $averageShiftHours = $hourdiff/$noOfShifts;
			$closedReception = count($shiftDataArr['recClosed']);
			$closedDispensery = count($shiftDataArr['disClosed']) + count($shiftDataArr['dis2Closed']);
			$euroDelta = array_sum($shiftDataArr['euroDelta']);
			$gramDetla = array_sum($shiftDataArr['gramDelta']) + array_sum($shiftDataArr['gramDeltaFlower']) + array_sum($shiftDataArr['gramDeltaExtract']);
			?>
			<table class='default' style='display: inline-block; vertical-align: top; margin-right: 20px;'>
				<tr>
					<th colspan='2'><center><strong>SHIFTS</strong></center></th>
				</tr>
				<tr>
					<td class='left'># of shifts</td>
					<td class='right'><?php echo $noOfShifts; ?></td>
				</tr>
				<tr>
					<td class='left'>Avg. shift duration (hours)</td>
					<td class='right'><?php echo number_format($averageShiftHours, 2); ?></td>
				</tr>
				<tr>
					<td class='left'>Closed receptions</td>
					<td class='right'><?php echo $closedReception; ?></td>
				</tr>
				<tr>
					<td class='left'>Closed dispensaries</td>
					<td class='right'><?php echo $closedDispensery; ?></td>
				</tr>
				<tr>
					<td class='left'><?php echo $_SESSION['currencyoperator'] ?> delta</td>
					<td class='right'><?php echo number_format($euroDelta, 2); ?> <?php echo $_SESSION['currencyoperator'] ?></td>
				</tr>
				<tr>
					<td class='left'>g. delta</td>
					<td class='right'><?php echo number_format($gramDelta, 2); ?></td>
				</tr>
			</table>
			<?php  if($_SESSION['workertracking'] == 1){
					// Fetch worker tracking data
				  $selectWorkerTracking = "SELECT MIN(comment), MAX(comment) from logins WHERE type = 2 AND user_id = '$user_id' AND (time BETWEEN '$openingTime' AND '$closingTime')";
					try
					{
						$result = $pdo3->prepare("$selectWorkerTracking");
						$result->execute();
					}
					catch (PDOException $e)
					{
						$error = 'Error fetching user: ' . $e->getMessage();
						echo $error;
						exit();
					}
					$workerRow = $result->fetch();
					$startSchedule = $workerRow['MIN(comment)'];
					$endSchedule = $workerRow['MAX(comment)'];
						$datetime1 = new DateTime($startSchedule);
						$datetime2 = new DateTime($endSchedule);
						$interval = $datetime1->diff($datetime2);
						
						$noOfMonths = $interval->format('%m');
						$noOfDays = $interval->format('%d');
						$noOfHours = $interval->format('%h');
						$noOfMins = $interval->format('%i');
					// Real worked hours
					  $selectWorkerHours = "SELECT MAX(comment) from logins WHERE type = 1 AND user_id = '$user_id' AND (time BETWEEN '$openingTime' AND '$closingTime')";
						try
						{
							$result = $pdo3->prepare("$selectWorkerHours");
							$result->execute();
						}
						catch (PDOException $e)
						{
							$error = 'Error fetching user: ' . $e->getMessage();
							echo $error;
							exit();
						}
						$realWorkerRow = $result->fetch();
						$workTill = $realWorkerRow['MAX(comment)'];
						// total worked hours
						$starthour = new DateTime($endSchedule);
						$workTillhour = new DateTime($workTill);
						$interval = $starthour->diff($workTillhour);
						
						$realnoOfMonths = $interval->format('%m');
						$realnoOfDays = $interval->format('%d');
						$realnoOfHours = $interval->format('%h');
						$realnoOfMins = $interval->format('%i');
			 ?>
				<table class='default' style='display: inline-block; vertical-align: top;'>
					<tr>
						<th colspan='3'><center><strong>WORKER TRACKING</strong></center></th>
					</tr>
					<tr>
						<td class='left'>Scheduled hours</td>
						<td class='right'><?php echo $noOfMonths ? $noOfMonths." M" : '' ?> <?php echo $noOfDays ? $noOfDays." d" : '' ?> <?php echo $noOfHours ? $noOfHours." h" : '' ?> <?php echo $noOfMins ? $noOfMins." m" : '' ?></td>
						<td></td>
					</tr>
					<tr>
						<td class='left'>Real worked hours</td>
						<td class='right'><?php echo $realnoOfMonths ? $realnoOfMonths." M" : '' ?> <?php echo $realnoOfDays ? $realnoOfDays." d" : '' ?> <?php echo $realnoOfHours ? $realnoOfHours." h" : '' ?> <?php echo $realnoOfMins ? $realnoOfMins." m" : '' ?></td>
						<td></td>
					</tr>
					<tr>
						<td class='left'># of shifts</td>
						<td class='right'><?php echo $noOfShifts; ?></td>
						<td></td>
					</tr>
<!-- 					<tr>
						<td class='left'># of times arrived late</td>
						<td class='right'>2</td>
						<td class='left'>(22 min)</td>
					</tr> -->
				</table>
			<?php } ?>
			<br /><br />
			<table class='default' id='mainTable'>
			  <thead>
			   <tr>
			    <th><?php echo $lang['global-type']; ?></th>
			    <th><?php echo $lang['global-time']; ?></th>
			    <th><?php echo $lang['responsible']; ?></th>
			    <th><?php echo $lang['global-till']; ?></th>
			    <th><?php echo $lang['global-delta']; ?></th>
			    <th><?php echo $lang['global-product']; ?></th>
			    <th><?php echo $lang['global-delta']; ?></th>
			    <th><?php echo $lang['global-flowers']; ?></th>
			    <th><?php echo $lang['global-delta']; ?></th>
			    <th><?php echo $lang['global-extracts']; ?></th>
			    <th><?php echo $lang['global-delta']; ?></th>
		<!--	    <th><?php echo $lang['completed']; ?>?</th>-->
			   </tr>
			  </thead>
			  <tbody>
			  
			  <?php
			  try
				{
					$results = $pdo3->prepare("$selectOpenings");
					$results->execute();
				}
				catch (PDOException $e)
				{
						$error = 'Error fetching user: ' . $e->getMessage();
						echo $error;
						exit();
				}
				while ($opening = $results->fetch()) {
			$type = $opening['type'];
			$shiftStartOrig = $opening['shiftStart'];
			$typeshort = $opening['typeshort'];
			$openingid = $opening['openingid'];
			$shiftStart = date("d-m-Y H:i", strtotime($opening['shiftStart'] . "+$offsetSec seconds"));
			$tillBalance = $opening['tillBalance'];
			$tillDelta = $opening['tillDelta'];
			$openedby = $opening['openedby'];
			$prodStock = $opening['prodStock'];
			$prodStockFlower = $opening['prodStockFlower'];
			$prodStockExtract = $opening['prodStockExtract'];
			$stockDelta = $opening['stockDelta'];
			$stockDeltaFlower = $opening['stockDeltaFlower'];
			$stockDeltaExtract = $opening['stockDeltaExtract'];
			
			$user = getUser($openedby);
			if ($typeshort == 'CloseDay') {
				
				$shifttype = 1;
				$type = $lang['closeday-main'];
				
				if ($_SESSION['openAndClose'] > 2) {
					
					$checkFirst = "SELECT dayClosed FROM opening WHERE openingtime < '$shiftStartOrig' ORDER BY openingtime DESC LIMIT 1";
				try
				{
					$result = $pdo3->prepare("$checkFirst");
					$result->execute();
				}
				catch (PDOException $e)
				{
						$error = 'Error fetching user: ' . $e->getMessage();
						echo $error;
						exit();
				}
			
				$row = $result->fetch();
						$dayClosed = $row['dayClosed'];
						
					if ($dayClosed == 2) {
						
						$completedFlag = "<img src='images/checkmark.png' width='15' />";
						
					} else {
						
						$completedFlag = "<span class='redColour'><strong>NO</strong></span>";
						
					}
				}
				
			} else if ($typeshort == 'OpenDay') {
				
				$shifttype = 2;
				$type = $lang['openday'];
				
				$selectUsers = "SELECT COUNT(firstDayOpen) FROM opening";
				$rowCount = $pdo3->query("$selectUsers")->fetchColumn();
				
				$checkFirst = "SELECT firstDayOpen FROM opening";
				try
				{
					$result = $pdo3->prepare("$checkFirst");
					$result->execute();
				}
				catch (PDOException $e)
				{
						$error = 'Error fetching user: ' . $e->getMessage();
						echo $error;
						exit();
				}
			
				
						
				if ($rowCount == 1) {
					
					// Only 1 opening exists - first opening
					$row = $result->fetch();
						$firstDayOpen = $row['firstDayOpen'];
						
					if ($firstDayOpen == 2) {
						
						$completedFlag = "<img src='images/checkmark.png' width='15' />";
						
					} else {
						
						$completedFlag = "<span class='redColour'><strong>NO</strong></span>";
						
					}
					
				} else {
					
					// Not first opening
					$checkFirst = "SELECT dayOpened FROM closing WHERE closingtime < '$shiftStartOrig' ORDER BY closingtime DESC LIMIT 1";
				try
				{
					$result = $pdo3->prepare("$checkFirst");
					$result->execute();
				}
				catch (PDOException $e)
				{
						$error = 'Error fetching user: ' . $e->getMessage();
						echo $error;
						exit();
				}
			
				$row = $result->fetch();
						$dayOpened = $row['dayOpened'];
					
					if ($dayOpened == 2) {
						
						$completedFlag = "<img src='images/checkmark.png' width='15' />";
						
					} else {
						
						$completedFlag = "<span class='redColour'><strong>NO</strong></span>";
						
					}
					
				}
						
				
			} else if ($typeshort == 'CloseShift') {
				
				$shifttype = 3;
				$type = $lang['close-shift'];
				
			} else if ($typeshort == 'StartShift') {
				
				$shifttype = 4;
				$type = $lang['start-shift'];
				
			}
			
			if ($tillDelta < 0) {
				
				$tillColour = 'negative';
				
			} else if ($tillDelta > 0) {
				
				$tillColour = 'positive';
				
			} else {
				
				$tillColour = '';	
				
			}
			
			if ($shifttype == 2) {
				
				$expense_row =	sprintf("
			  	  <tr>
			  	   <td class='clickableRow' href='shift.php?type=%d&id=%d' style='text-align: left; border-bottom: 4px solid #507c3d;'>%s</td>
			  	   <td class='clickableRow' href='shift.php?type=%d&id=%d' style='text-align: left; border-bottom: 4px solid #507c3d;'>%s</td>
			  	   <td class='clickableRow' href='shift.php?type=%d&id=%d' style='text-align: left; border-bottom: 4px solid #507c3d;'>%s</td>
			  	   <td class='clickableRow' href='shift.php?type=%d&id=%d' style='text-align: right; border-bottom: 4px solid #507c3d;'>%0.2f <span class='smallerfont'>{$_SESSION['currencyoperator']}</span></td>
			  	   <td class='clickableRow %s' href='shift.php?type=%d&id=%d' style='text-align: right; border-bottom: 4px solid #507c3d;'>%0.2f <span class='smallerfont'>{$_SESSION['currencyoperator']}</span></td>
			  	   <td class='clickableRow' href='shift.php?type=%d&id=%d' style='text-align: right; border-bottom: 4px solid #507c3d;'>%0.2f <span class='smallerfont'>g.</span></td>
			  	   <td class='clickableRow' href='shift.php?type=%d&id=%d' style='text-align: right; border-bottom: 4px solid #507c3d;'>%0.2f <span class='smallerfont'>g.</span></td>
			  	   <td class='clickableRow' href='shift.php?type=%d&id=%d' style='text-align: right; border-bottom: 4px solid #507c3d;'>%0.2f <span class='smallerfont'>g.</span></td>
			  	   <td class='clickableRow' href='shift.php?type=%d&id=%d' style='text-align: right; border-bottom: 4px solid #507c3d;'>%0.2f <span class='smallerfont'>g.</span></td>
			  	   <td class='clickableRow' href='shift.php?type=%d&id=%d' style='text-align: right; border-bottom: 4px solid #507c3d;'>%0.2f <span class='smallerfont'>g.</span></td>
			  	   <td class='clickableRow' href='shift.php?type=%d&id=%d' style='text-align: right; border-bottom: 4px solid #507c3d;'>%0.2f <span class='smallerfont'>g.</span></td>
			  	   <!--<td style='border-bottom: 4px solid #507c3d;'>%s</td>-->
				  </tr>
		",
				  $shifttype, $openingid, $type, $shifttype, $openingid, $shiftStart, $shifttype, $openingid, $user, $shifttype, $openingid, $tillBalance, $tillColour, $shifttype, $openingid, $tillDelta, $shifttype, $openingid, $prodStock, $shifttype, $openingid, $stockDelta, $shifttype, $openingid, $prodStockFlower, $shifttype, $openingid, $stockDeltaFlower, $shifttype, $openingid, $prodStockExtract, $shifttype, $openingid, $stockDeltaExtract, $completedFlag
				  );
				  
			} else {
				
			
				$expense_row =	sprintf("
			  	  <tr>
			  	   <td class='clickableRow' href='shift.php?type=%d&id=%d' style='text-align: left;'>%s</td>
			  	   <td class='clickableRow' href='shift.php?type=%d&id=%d' style='text-align: left;'>%s</td>
			  	   <td class='clickableRow' href='shift.php?type=%d&id=%d' style='text-align: left;'>%s</td>
			  	   <td class='clickableRow' href='shift.php?type=%d&id=%d' style='text-align: right;'>%0.2f <span class='smallerfont'>{$_SESSION['currencyoperator']}</span></td>
			  	   <td class='clickableRow %s' href='shift.php?type=%d&id=%d' style='text-align: right;'>%0.2f <span class='smallerfont'>{$_SESSION['currencyoperator']}</span></td>
			  	   <td class='clickableRow' href='shift.php?type=%d&id=%d' style='text-align: right;'>%0.2f <span class='smallerfont'>g.</span></td>
			  	   <td class='clickableRow' href='shift.php?type=%d&id=%d' style='text-align: right;'>%0.2f <span class='smallerfont'>g.</span></td>
			  	   <td class='clickableRow' href='shift.php?type=%d&id=%d' style='text-align: right;'>%0.2f <span class='smallerfont'>g.</span></td>
			  	   <td class='clickableRow' href='shift.php?type=%d&id=%d' style='text-align: right;'>%0.2f <span class='smallerfont'>g.</span></td>
			  	   <td class='clickableRow' href='shift.php?type=%d&id=%d' style='text-align: right;'>%0.2f <span class='smallerfont'>g.</span></td>
			  	   <td class='clickableRow' href='shift.php?type=%d&id=%d' style='text-align: right;'>%0.2f <span class='smallerfont'>g.</span></td>
			  	   <!--<td>%s</td>-->
				  </tr>",
				  $shifttype, $openingid, $type, $shifttype, $openingid, $shiftStart, $shifttype, $openingid, $user, $shifttype, $openingid, $tillBalance, $tillColour, $shifttype, $openingid, $tillDelta, $shifttype, $openingid, $prodStock, $shifttype, $openingid, $stockDelta, $shifttype, $openingid, $prodStockFlower, $shifttype, $openingid, $stockDeltaFlower, $shifttype, $openingid, $prodStockExtract, $shifttype, $openingid, $stockDeltaExtract, $completedFlag
				  );
				  
			}
			
			echo $expense_row;
			
		  }
		?>
			 </tbody>
			 </table>
			<!-- <img src='images/shiftsx.png' /> -->
		</div>
	</div>
	<script>
		$("#chooseOther").click(function (e) {
			e.preventDefault();
			$('#stafflist2').toggle();
		});	
	</script>
	<script type="text/javascript">
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
			    
				$('#mainTable').tablesorter({
					usNumberFormat: true,
					headers: {
						1: {
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
						},
						6: {
							sorter: "currency"
						},
						7: {
							sorter: "currency"
						},
						8: {
							sorter: "currency"
						},
						9: {
							sorter: "currency"
						},
						10: {
							sorter: "currency"
						}
					}
				}); 
			}); 
						    
	function delete_shift(shiftid,shifttype) {
		if (confirm("{$lang['expense-deleteconfirm']}")) {
					window.location = "uTil/delete-shift.php?shiftid=" + shiftid + "&shifttype=" + shifttype;
					}
	}
	</script>
	<?php displayFooter(); ?>
