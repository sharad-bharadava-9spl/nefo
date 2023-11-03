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
	
	$openingid = $_SESSION['openingid'];
	$openingtime = $_SESSION['openingtime'];
	$closingtime = $_SESSION['closingtime'];
	$tillBalance = $_SESSION['tillBalance'];
	$bankBalance = $_SESSION['bankBalance'];
	
	// If the page re-submitted, let's save Closing values for Reception! Also set Opening to 2.
	if (isset($_GET['saveReception'])) {
		
		$tillComment = $_POST['tillComment'];
		$expenses = $_POST['expenses'];
		$bankExpenses = $_POST['bankExpenses'];
		$membershipFees = $_POST['membershipFees'];
		$membershipFeesBank = $_POST['membershipFeesBank'];
		$donationsToday = $_POST['donationsToday'];
		$donationsTodayBank = $_POST['donationsTodayBank'];
		$salesTodayCash = $_POST['salesTodayCash'];
		$salesTodayBank = $_POST['salesTodayBank'];
		$salesBarTodayCash = $_POST['salesBarTodayCash'];
		$salesBarTodayBank = $_POST['salesBarTodayBank'];
		
		$totalIncome = $membershipFees + $membershipFeesBank + $donationsToday + $donationsTodayBank + $salesTodayCash + $salesTodayBank + $salesBarTodayCash + $salesBarTodayBank;
		
		$estimatedTill = $_POST['estimatedTill'];
		$tillDelta = $_POST['tillDelta'];
		$tillBalanceOpening = $_POST['tillBalance'];
		$tillTot = $_POST['tillTot'];
		

		$closingBank = $_POST['closingBank'];
		$bankBalanceOpening = $_POST['bankBalance'];
		
		$banked = $_POST['banked'];
		$bankedduringday = $_POST['bankedDuringDay'];
		$bankComment = $_POST['bankComment'];
		
		
		$salesToday	= $_SESSION['salesToday'];
		$quantitySold = $_SESSION['quantitySold'];
		$unitsSold = $_SESSION['unitsSold'];
		
		$closeCount = $_SESSION['closeCount'];
		
		$closingBalance = $closingBank + $tillTot;
		
		
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
		$selectMembers = "SELECT COUNT(memberno) from users WHERE (userGroup BETWEEN '1' AND '4') OR (userGroup = 5 AND paidUntil >= '$closingtime')";
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
		$newMembers = "SELECT COUNT(user_id) FROM users where registeredSince BETWEEN '$openingtime' AND '$closingtime'";
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
		$bannedmembers = "SELECT COUNT(user_id) FROM users where banTime BETWEEN '$openingtime' AND '$closingtime'";
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
		$deletedmembers = "SELECT COUNT(user_id) FROM users where deleteTime BETWEEN '$openingtime' AND '$closingtime'";
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
		$selectMembers = "SELECT COUNT(memberno) from users WHERE DATE(paidUntil) = DATE_ADD(DATE('$openingtime'), INTERVAL -1 DAY)";
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

		
		// Look up renewed members today
		$selectMembers = "SELECT COUNT(m.paymentid) FROM memberpayments m, users u WHERE u.user_id = m.userid AND m.paymentdate BETWEEN '$openingtime' AND '$closingtime' AND DATE(u.registeredSince) < DATE('$openingtime')";
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
		
		// Check if the opening has been closed, to know whether to INSERT or UPDATE
		if ($_SESSION['type'] == 'opening') {
			
			$openingLookup = "SELECT shiftClosedNo FROM opening WHERE openingid = $openingid";
			
		} else {

			$openingLookup = "SELECT shiftClosedNo FROM shiftopen WHERE openingid = $openingid";	
			
		}
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
			$dayClosedNo = $row['shiftClosedNo'];
			
		$realClosingtime = date('Y-m-d H:i:s');
			

			
		  	$query = sprintf("INSERT INTO recshiftclose (shiftStart, closingtime, shiftEnd, oneCent, twoCent, fiveCent, tenCent, twentyCent, fiftyCent, oneEuro, twoEuro, fiveEuro, tenEuro, twentyEuro, fiftyEuro, hundredEuro, coinsTot, notesTot, cashintill, tillComment, closingbalance, bankBalance, newmembers, moneytaken, takenduringday, soldtoday, quantitySold, expenses, membershipFees, estimatedTill, tillDelta, bankExpenses, income, closedby, donations, renewedMembers, bannedMembers, deletedMembers, totalMembers, activeMembers, expiredMembers, bankDonations, membershipfeesBank, unitsSold, openingBalance, openingBalanceBank, totCredit) VALUES ('%s', '%s', '%s', '%d', '%d', '%d', '%d', '%d', '%d', '%d', '%d', '%d', '%d', '%d', '%d', '%d', '%f', '%f', '%f', '%s', '%f', '%f', '%d', '%f', '%f', '%f', '%f', '%f', '%f', '%f', '%f', '%f', '%f', '%d', '%f', '%d', '%d', '%d', '%d', '%d', '%d', '%f', '%f', '%d', '%f', '%f', '%f');",
		  	$openingtime, $realClosingtime, $closingtime, $closeCount['oneCent'], $closeCount['twoCent'], $closeCount['fiveCent'], $closeCount['tenCent'], $closeCount['twentyCent'], $closeCount['fiftyCent'], $closeCount['oneEuro'], $closeCount['twoEuro'], $closeCount['fiveEuro'], $closeCount['tenEuro'], $closeCount['twentyEuro'], $closeCount['fiftyEuro'], $closeCount['hundredEuro'], $closeCount['coinsTot'], $closeCount['notesTot'], $closeCount['tillTot'], $tillComment, $closingBalance, $closingBank, $newmembers, $banked, $bankedduringday, $salesToday, $quantitySold, $expenses, $membershipFees, $estimatedTill, $tillDelta, $bankExpenses, $totalIncome, $_SESSION['user_id'], $donationsToday, $renewedMembers, $bannedmembers, $deletedmembers, $currentmembers, $activemembers, $expiredmembers, $donationsTodayBank, $membershipFeesBank, $unitsSold, $tillBalance, $bankBalance, $totCredit);
		try
		{
			$result = $pdo3->prepare("$query")->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
			
		$closingid = $pdo3->lastInsertId();
			
	$openingLookup = "SELECT openingid, openingtime, tillBalance, shiftClosed AS closed, openedby, 'opening' AS type, firstDayOpen FROM opening UNION ALL SELECT openingid, openingtime, tillBalance, shiftClosed AS closed, openedby, 'shiftopen' AS type, '' AS firstDayOpen FROM shiftopen ORDER BY openingtime DESC";
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
			$type = $row['type'];

		
		// Here insert closingid into Opening
		if ($type == 'opening') {
			
			$updateOpening = sprintf("UPDATE recopening SET shiftClosed = 2, shiftClosedAt = '%s', shiftClosedNo = '%d', shiftClosedBy = '%d' WHERE openingid = '%d';",
				$realClosingtime,
				$closingid,
				$_SESSION['user_id'], 
				$openingid
				);

		} else {
			
			$updateOpening = sprintf("UPDATE recshiftopen SET shiftClosed = 2, shiftClosedAt = '%s', shiftClosedNo = '%d', shiftClosedBy = '%d' WHERE openingid = '%d';",
				$realClosingtime,
				$closingid,
				$_SESSION['user_id'], 
				$openingid
				);
					
		}
		try
		{
			$result = $pdo3->prepare("$updateOpening")->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
		
		
		// On success: redirect.
		$_SESSION['successMessage'] = $lang['reception-closed-successfully'];
		header("Location: admin.php");
		exit();

	}
	## ON PAGE SUBMISSION END ##
	
	
	// Is step 2 complete -- or has a recount been done?
	if ($_POST['step2'] == 'complete' || (isset($_GET['recount']))) {
		$oneCent = $_POST['oneCent'];
		$twoCent = $_POST['twoCent'];
		$fiveCent = $_POST['fiveCent'];
		$tenCent = $_POST['tenCent'];
		$twentyCent = $_POST['twentyCent'];
		$fiftyCent = $_POST['fiftyCent'];
		$oneEuro = $_POST['oneEuro'];
		$twoEuro = $_POST['twoEuro'];
		$fiveEuro = $_POST['fiveEuro'];
		$tenEuro = $_POST['tenEuro'];
		$twentyEuro = $_POST['twentyEuro'];
		$fiftyEuro = $_POST['fiftyEuro'];
		$hundredEuro = $_POST['hundredEuro'];
		$coinsTot = $_POST['coinsTot'];
		$notesTot = $_POST['notesTot'];
		$tillTot = $_POST['tillTot'];
		$banked = $_POST['banked'];

		$oneCentFull = $_POST['oneCentFull'];
		$twoCentFull = $_POST['twoCentFull'];
		$fiveCentFull = $_POST['fiveCentFull'];
		$tenCentFull = $_POST['tenCentFull'];
		$twentyCentFull = $_POST['twentyCentFull'];
		$fiftyCentFull = $_POST['fiftyCentFull'];
		$oneEuroFull = $_POST['oneEuroFull'];
		$twoEuroFull = $_POST['twoEuroFull'];
		$fiveEuroFull = $_POST['fiveEuroFull'];
		$tenEuroFull = $_POST['tenEuroFull'];
		$twentyEuroFull = $_POST['twentyEuroFull'];
		$fiftyEuroFull = $_POST['fiftyEuroFull'];
		$hundredEuroFull = $_POST['hundredEuroFull'];
		$coinsTotFull = $_POST['coinsTotFull'];
		$notesTotFull = $_POST['notesTotFull'];
		$tillTotFull = $_POST['tillTotFull'];
		
		// Create a session array with the closing count
		$_SESSION['closeCount'] = array("oneCent"=>"$oneCent", "twoCent"=>"$twoCent", "fiveCent"=>"$fiveCent", "tenCent"=>"$tenCent", "twentyCent"=>"$twentyCent", "fiftyCent"=>"$fiftyCent", "oneEuro"=>"$oneEuro", "twoEuro"=>"$twoEuro", "fiveEuro"=>"$fiveEuro", "tenEuro"=>"$tenEuro", "twentyEuro"=>"$twentyEuro", "fiftyEuro"=>"$fiftyEuro", "hundredEuro"=>"$hundredEuro", "coinsTot"=>"$coinsTot", "notesTot"=>"$notesTot", "tillTot"=>"$tillTot", "banked"=>"$banked", "oneCentFull"=>"$oneCentFull", "twoCentFull"=>"$twoCentFull", "fiveCentFull"=>"$fiveCentFull", "tenCentFull"=>"$tenCentFull", "twentyCentFull"=>"$twentyCentFull", "fiftyCentFull"=>"$fiftyCentFull", "oneEuroFull"=>"$oneEuroFull", "twoEuroFull"=>"$twoEuroFull", "fiveEuroFull"=>"$fiveEuroFull", "tenEuroFull"=>"$tenEuroFull", "twentyEuroFull"=>"$twentyEuroFull", "fiftyEuroFull"=>"$fiftyEuroFull", "hundredEuroFull"=>"$hundredEuroFull", "coinsTotFull"=>"$coinsTotFull", "notesTotFull"=>"$notesTotFull", "tillTotFull"=>"$tillTotFull");
	} else {
		handleError($lang['global-twonotcomplete'],"");
	}
	
	

		
		// Query to look up opening balance Bank
		if ($_SESSION['type'] == 'opening') {
			
			$closingLookup = "SELECT bankBalance FROM opening WHERE openingid = $openingid";

		} else {
			
			$closingLookup = "SELECT bankBalance FROM shiftopen WHERE openingid = $openingid";

		}
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
			$bankBalance = $row['bankBalance'];	

		// Look up todays dispenses
		$selectSales = "SELECT SUM(amount), SUM(quantity), SUM(units) from sales WHERE saletime BETWEEN '$openingtime' AND '$closingtime'";
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
			$_SESSION['salesToday'] = $row['SUM(amount)'];
			$_SESSION['quantitySold'] = $row['SUM(quantity)'];
			$_SESSION['unitsSold'] = $row['SUM(units)'];
			

		// Look up todays donations to till
		$selectDonations = "SELECT SUM(amount) from donations WHERE (donatedTo = 0 OR donatedTo = 1) AND donationTime BETWEEN '$openingtime' AND '$closingtime'";
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
			$donationsToday = $row['SUM(amount)'];
			
		// Look up todays donations to bank
		$selectBankDonations = "SELECT SUM(amount) from donations WHERE donatedTo = 2 AND donationTime BETWEEN '$openingtime' AND '$closingtime'";
		try
		{
			$result = $pdo3->prepare("$selectBankDonations");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
			$donationsTodayBank = $row['SUM(amount)'];
			
			
		// Look up today's expenses
		$selectExpenses = "SELECT SUM(amount) FROM expenses WHERE registertime BETWEEN '$openingtime' AND '$closingtime' AND moneysource = 1";
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
		$expenses = $row['SUM(amount)'];
		
				
		// Look up today's bank expenses
		$selectExpensesBank = "SELECT SUM(amount) FROM expenses WHERE registertime BETWEEN '$openingtime' AND '$closingtime' AND moneysource = 2";
		try
		{
			$result = $pdo3->prepare("$selectExpensesBank");
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
		
		
		// Look up today's membership fees
		$selectMembershipFees = "SELECT SUM(amountPaid) FROM memberpayments WHERE paymentdate BETWEEN '$openingtime' AND '$closingtime' AND paidTo < 2";
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
					
		// Look up today's membership fees paid to bank
		$selectMembershipFees = "SELECT SUM(amountPaid) FROM memberpayments WHERE paymentdate BETWEEN '$openingtime' AND '$closingtime' AND paidTo = 2";
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
			$membershipFeesBank = $row['SUM(amountPaid)'];
			
		// Look up money banked during the day
		$selectBanked = "SELECT SUM(amount) FROM banked WHERE time BETWEEN '$openingtime' AND '$closingtime'";
		try
		{
			$result = $pdo3->prepare("$selectBanked");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
			$bankedDuringDay = $row['SUM(amount)'];
			
			
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

							
		// Calculate bank balance
		$closingBank = $bankBalance + $banked + $bankedDuringDay + $donationsTodayBank + $salesTodayBank + $salesBarTodayBank + $membershipFeesBank - $bankExpenses;
				
		// Calculate estimated till
		$estimatedTill = $tillBalance + $membershipFees + $donationsToday + $salesTodayCash + $salesBarTodayCash- $expenses - $banked - $bankedDuringDay;
		
		$tillDelta = $tillTot - $estimatedTill;
		

	$confirmLeave = <<<EOD
    $(document).ready(function() {   	    
document.querySelector('button').addEventListener("click", function(){
    window.btn_clicked = true;      //set btn_clicked to true
});
document.querySelector('.recount').addEventListener("click", function(){
    window.btn_clicked = true;      //set btn_clicked to true
});

$(window).bind('beforeunload', function(){
    if(!window.btn_clicked){
        return "{$lang['closeday-leavepage']}";
    }
});
  }); // end ready
EOD;
		pageStart($lang['close-shift'], NULL, $confirmLeave, "pcloseday", "step3", $lang['closeday-rec-three'], $_SESSION['successMessage'], $_SESSION['errorMessage']);
		
	echo $_SESSION['pageHeader'];
	
?>

<form onsubmit='oneClick.disabled = true; return true;' id="registerForm" action="?saveReception" method="POST"><br />
 <input type="hidden" name="step3" value="complete" />
 <input type="hidden" name="salesToday" value="<?php echo $salesToday; ?>" />
 <input type="hidden" name="salesTodayPaid" value="<?php echo $salesTodayPaid; ?>" />
 <input type="hidden" name="salesTodayBank" value="<?php echo $salesTodayBank; ?>" />
 <input type="hidden" name="salesBarTodayBank" value="<?php echo $salesBarTodayBank; ?>" />
 <input type="hidden" name="membershipFeesBank" value="<?php echo $membershipFeesBank; ?>" />
 <input type="hidden" name="donationsTodayBank" value="<?php echo $donationsTodayBank; ?>" />
 <input type="hidden" name="bankExpenses" value="<?php echo $bankExpenses; ?>" />
 <input type="hidden" name="closingBank" value="<?php echo $closingBank; ?>" />

  <div class="halfblock">
   <h3><?php echo $lang['closeday-tillbalance']; ?></h3>
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
	 <td><input type="number" lang="nb" name="banked" id="banked" class="fourDigit red" value="<?php echo $banked; ?>" readonly /></td>
    </tr>		 
    <tr>
	 <td>- <?php echo $lang['banked-during-shift']; ?>:</td>
	 <td><input type="number" lang="nb" name="bankedDuringDay" id="bankedDuringDay" class="fourDigit red" value="<?php echo $bankedDuringDay; ?>" readonly /></td>
    </tr>		 
    <tr>
	 <td><?php echo $lang['closeday-estimatedtill']; ?>:</td>
	 <td><input type="number" lang="nb" name="estimatedTill" id="estimatedTill" class="fourDigit" value="<?php echo $estimatedTill; ?>" readonly /></td>
    </tr>		 
    <tr>
	 <td><?php echo $lang['closeday-yourcount']; ?>:</td>
	 <td><input type="number" lang="nb" name="tillTot" id="tillTot" class="fourDigit" value="<?php echo $tillTot; ?>" readonly /> <a href="close-reception-shift-1.php?recount" class="recount"><?php echo $lang['closeday-recount']; ?>?</a></td>
    </tr>		 
    <tr>
	 <td><strong><?php echo $lang['global-delta']; ?>:</strong></td>
	 <td><strong><input type="number" lang="nb" name="tillDelta" id="tillDelta" class="fourDigit" value="<?php echo number_format($tillDelta,2,'.',''); ?>" readonly /></strong></td>
    </tr>		 
    <tr>
	 <td><?php echo $lang['closeday-tillcomment']; ?>:</td>
	 <td><textarea name="tillComment" placeholder="Comment?"></textarea></td>
    </tr>		 
   </table>
  </div>
  <button name='oneClick' type="submit"><?php echo $lang['global-confirm']; ?></button>
</form>

<?php displayFooter(); ?>
