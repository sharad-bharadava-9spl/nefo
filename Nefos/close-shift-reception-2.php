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

	
	// If the page re-submitted, let's save Closing values for Reception! Also set Opening to 2.
	if (isset($_GET['saveReception'])) {
		$tillComment = $_POST['tillComment'];
		$expenses = $_POST['expenses'];
		$bankExpenses = $_POST['bankExpenses'];
		$membershipFees = $_POST['membershipFees'];
		$membershipFeesBank = $_POST['membershipFeesBank'];
		$donationsToday = $_POST['donationsToday'];
		$donationsTodayBank = $_POST['donationsTodayBank'];
		
		$totalIncome = $membershipFees + $membershipFeesBank + $donationsToday + $donationsTodayBank;
		
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
	
		$result = mysql_query($selectMembers)
			or handleError($lang['error-loadsales'],"Error loading sale from db: " . mysql_error());
			
		$row = mysql_fetch_array($result);
			$currentmembers = $row['COUNT(memberno)'];
			
	
		// Active members today
		$selectMembers = "SELECT COUNT(memberno) from users WHERE (userGroup BETWEEN '1' AND '4') OR (userGroup = 5 AND paidUntil >= '$closingtime')";
	
		$result = mysql_query($selectMembers)
			or handleError($lang['error-loadsales'],"Error loading sale from db: " . mysql_error());
			
		$row = mysql_fetch_array($result);
			$activemembers = $row['COUNT(memberno)'];
			
		
		// New members today
		$newMembers = "SELECT COUNT(user_id) FROM users where registeredSince BETWEEN '$openingtime' AND '$closingtime'";
	
		$result = mysql_query($newMembers)
			or handleError($lang['error-loadnewmembers'],"Error loading sale from db: " . mysql_error());
		
		$row = mysql_fetch_array($result);
			$newmembers = $row['COUNT(user_id)'];
	
		// Banned members today
		$bannedmembers = "SELECT COUNT(user_id) FROM users where banTime BETWEEN '$openingtime' AND '$closingtime'";
	
		$result = mysql_query($bannedmembers)
			or handleError($lang['error-loadnewmembers'],"Error loading sale from db: " . mysql_error());
		
		$row = mysql_fetch_array($result);
			$bannedmembers = $row['COUNT(user_id)'];
			
		// Deleted members today
		$deletedmembers = "SELECT COUNT(user_id) FROM users where deleteTime BETWEEN '$openingtime' AND '$closingtime'";
	
		$result = mysql_query($deletedmembers)
			or handleError($lang['error-loadnewmembers'],"Error loading sale from db: " . mysql_error());
		
		$row = mysql_fetch_array($result);
			$deletedmembers = $row['COUNT(user_id)'];
			
			
		// Look up expired members today
		$selectMembers = "SELECT COUNT(memberno) from users WHERE DATE(paidUntil) = DATE_ADD(DATE('$openingtime'), INTERVAL -1 DAY)";
	
		$result = mysql_query($selectMembers)
		or handleError($lang['error-loadsales'],"Error loading sale from db: " . mysql_error());
		
		$row = mysql_fetch_array($result);
			$expiredmembers = $row['COUNT(memberno)'];

		
		// Look up renewed members today
		$selectMembers = "SELECT COUNT(m.paymentid) FROM memberpayments m, users u WHERE u.user_id = m.userid AND m.paymentdate BETWEEN '$openingtime' AND '$closingtime' AND DATE(u.registeredSince) < DATE('$openingtime')";

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
		
		// Check if the opening has been closed, to know whether to INSERT or UPDATE
		if ($_SESSION['type'] == 'opening') {
			
			$openingLookup = "SELECT shiftClosedNo FROM opening WHERE openingid = $openingid";
			
		} else {

			$openingLookup = "SELECT shiftClosedNo FROM shiftopen WHERE openingid = $openingid";	
			
		}
		
		$result = mysql_query($openingLookup)
			or handleError($lang['error-noopeningdetails'],"Error loading opening from db: " . mysql_error());
	
		$row = mysql_fetch_array($result);
			$dayClosedNo = $row['shiftClosedNo'];
			
		if ($dayClosedNo > 0) {
			
			
			
			// Means part of the day has been closed already, so use UPDATE
			
			$realClosingtime = date('Y-m-d H:i:s');

		  $query = sprintf("UPDATE shiftclose SET shiftStart = '%s', closingtime = '%s', shiftEnd = '%s', oneCent = '%d', twoCent = '%d', fiveCent = '%d', tenCent = '%d', twentyCent = '%d', fiftyCent = '%d', oneEuro = '%d', twoEuro = '%d', fiveEuro = '%d', tenEuro = '%d', twentyEuro = '%d', fiftyEuro = '%d', hundredEuro = '%d', coinsTot = '%f', notesTot = '%f', cashintill = '%f', tillComment = '%s', closingbalance = '%f', bankBalance = '%f', newmembers = '%d', moneytaken = '%f', takenduringday = '%f', soldtoday = '%f', quantitySold = '%f', expenses = '%f', membershipFees = '%f', estimatedTill = '%f', tillDelta = '%f', bankExpenses = '%f', income = '%f', closedby = '%d', donations = '%f', renewedMembers = '%d', bannedMembers = '%d', deletedMembers = '%d', totalMembers = '%d', activeMembers = '%d', expiredMembers = '%d', bankDonations = '%f', membershipfeesBank = '%f', unitsSold = '%d', openingBalance = '%f', openingBalanceBank = '%f', totCredit = '%f' WHERE closingid = '%d';",		  
		  $openingtime, $realClosingtime, $closingtime, $closeCount['oneCent'], $closeCount['twoCent'], $closeCount['fiveCent'], $closeCount['tenCent'], $closeCount['twentyCent'], $closeCount['fiftyCent'], $closeCount['oneEuro'], $closeCount['twoEuro'], $closeCount['fiveEuro'], $closeCount['tenEuro'], $closeCount['twentyEuro'], $closeCount['fiftyEuro'], $closeCount['hundredEuro'], $closeCount['coinsTot'], $closeCount['notesTot'], $closeCount['tillTot'], $tillComment, $closingBalance, $closingBank, $newmembers, $banked, $bankedduringday, $salesToday, $quantitySold, $expenses, $membershipFees, $estimatedTill, $tillDelta, $bankExpenses, $totalIncome, $_SESSION['user_id'], $donationsToday, $renewedMembers, $bannedmembers, $deletedmembers, $currentmembers, $activemembers, $expiredmembers, $donationsTodayBank, $membershipFeesBank, $unitsSold, $tillBalanceOpening, $bankBalanceOpening, $totCredit, $dayClosedNo);
		  

		  
		mysql_query($query)
			or handleError($lang['error-savedata'],"Error saving opening: " . mysql_error());
			
			// Here set closed flag in Opening table
			if ($_SESSION['type'] == 'opening') {
				
				$updateOpening = sprintf("UPDATE opening SET recShiftClosed = 2, recShiftClosedAt = '%s' WHERE openingid = '%d';",
					$realClosingtime,
					mysql_real_escape_string($openingid)
					);
				
			} else {
	
				$updateOpening = sprintf("UPDATE shiftopen SET recClosed = 2, recClosedAt = '%s' WHERE openingid = '%d';",
					$realClosingtime,
					mysql_real_escape_string($openingid)
					);
				
			}
	
			mysql_query($updateOpening)
				or handleError($lang['error-savedata'],"Error updating expense: " . mysql_error());
		
			
		} else {
			
			$realClosingtime = date('Y-m-d H:i:s');
			
			// Query to add Closing - 80 arguments
		  $query = sprintf("INSERT INTO shiftclose (shiftStart, closingtime, shiftEnd, oneCent, twoCent, fiveCent, tenCent, twentyCent, fiftyCent, oneEuro, twoEuro, fiveEuro, tenEuro, twentyEuro, fiftyEuro, hundredEuro, coinsTot, notesTot, cashintill, tillComment, closingbalance, bankBalance, newmembers, moneytaken, takenduringday, soldtoday, quantitySold, expenses, membershipFees, estimatedTill, tillDelta, bankExpenses, income, closedby, donations, renewedMembers, bannedMembers, deletedMembers, totalMembers, activeMembers, expiredMembers, bankDonations, membershipfeesBank, unitsSold, openingBalance, openingBalanceBank, totCredit) VALUES ('%s', '%s', '%s', '%d', '%d', '%d', '%d', '%d', '%d', '%d', '%d', '%d', '%d', '%d', '%d', '%d', '%f', '%f', '%f', '%s', '%f', '%f', '%d', '%f', '%f', '%f', '%f', '%f', '%f', '%f', '%f', '%f', '%f', '%d', '%f', '%d', '%d', '%d', '%d', '%d', '%d', '%f', '%f', '%d', '%f', '%f', '%f');",
		  $openingtime, $realClosingtime, $closingtime, $closeCount['oneCent'], $closeCount['twoCent'], $closeCount['fiveCent'], $closeCount['tenCent'], $closeCount['twentyCent'], $closeCount['fiftyCent'], $closeCount['oneEuro'], $closeCount['twoEuro'], $closeCount['fiveEuro'], $closeCount['tenEuro'], $closeCount['twentyEuro'], $closeCount['fiftyEuro'], $closeCount['hundredEuro'], $closeCount['coinsTot'], $closeCount['notesTot'], $closeCount['tillTot'], $tillComment, $closingBalance, $closingBank, $newmembers, $banked, $bankedduringday, $salesToday, $quantitySold, $expenses, $membershipFees, $estimatedTill, $tillDelta, $bankExpenses, $totalIncome, $_SESSION['user_id'], $donationsToday, $renewedMembers, $bannedmembers, $deletedmembers, $currentmembers, $activemembers, $expiredmembers, $donationsTodayBank, $membershipFeesBank, $unitsSold, $tillBalanceOpening, $bankBalanceOpening, $totCredit);
		
		mysql_query($query)
			or handleError($lang['error-savedata'],"Error saving opening: " . mysql_error());
			
		$closingid = mysql_insert_id();
		
		// Here insert closingid into Opening
		if ($_SESSION['type'] == 'opening') {
			
			$updateOpening = sprintf("UPDATE opening SET recShiftClosed = 2, recShiftClosedAt = '%s', shiftClosedNo = '%d' WHERE openingid = '%d';",
				$realClosingtime,
				mysql_real_escape_string($closingid),
				mysql_real_escape_string($openingid)
				);

		} else {
			
			$updateOpening = sprintf("UPDATE shiftopen SET recClosed = 2, recClosedAt = '%s', shiftClosedNo = '%d' WHERE openingid = '%d';",
				$realClosingtime,
				mysql_real_escape_string($closingid),
				mysql_real_escape_string($openingid)
				);
					
		}

		mysql_query($updateOpening)
			or handleError($lang['error-savedata'],"Error updating expense: " . mysql_error());
		
		}
		
		// On success: redirect.
		$_SESSION['successMessage'] = $lang['reception-closed-successfully'];
		header("Location: close-shift.php");
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

		$result = mysql_query($closingLookup)
			or handleError($lang['error-noopeningdetails'],"Error loading opening from db: " . mysql_error());
			
		$row = mysql_fetch_array($result);
			$bankBalance = $row['bankBalance'];	

		// Look up todays dispenses
		$selectSales = "SELECT SUM(amount), SUM(quantity), SUM(units) from sales WHERE saletime BETWEEN '$openingtime' AND '$closingtime'";

		$result = mysql_query($selectSales)
			or handleError($lang['error-dispenseload'],"Error loading sale from db: " . mysql_error());
		
		$row = mysql_fetch_array($result);
			$_SESSION['salesToday'] = $row['SUM(amount)'];
			$_SESSION['quantitySold'] = $row['SUM(quantity)'];
			$_SESSION['unitsSold'] = $row['SUM(units)'];
			

		// Look up todays donations to till
		$selectDonations = "SELECT SUM(amount) from donations WHERE (donatedTo = 0 OR donatedTo = 1) AND donationTime BETWEEN '$openingtime' AND '$closingtime'";

		$donationResult = mysql_query($selectDonations)
			or handleError($lang['error-donationload'],"Error loading donations from db: " . mysql_error());
		
		$row = mysql_fetch_array($donationResult);
			$donationsToday = $row['SUM(amount)'];
			
		// Look up todays donations to bank
		$selectBankDonations = "SELECT SUM(amount) from donations WHERE donatedTo = 2 AND donationTime BETWEEN '$openingtime' AND '$closingtime'";

		$bankDonationResult = mysql_query($selectBankDonations)
			or handleError($lang['error-donationload'],"Error loading donations from db: " . mysql_error());
		
		$row = mysql_fetch_array($bankDonationResult);
			$donationsTodayBank = $row['SUM(amount)'];
			
			
		// Look up today's expenses
		$selectExpenses = "SELECT SUM(amount) FROM expenses WHERE registertime BETWEEN '$openingtime' AND '$closingtime' AND moneysource = 1";
				
		$expenseResult = mysql_query($selectExpenses)
			or handleError($lang['error-expensesload'],"Error loading expense from db: " . mysql_error());
			
		$row = mysql_fetch_array($expenseResult);
		$expenses = $row['SUM(amount)'];
		
				
		// Look up today's bank expenses
		$selectExpensesBank = "SELECT SUM(amount) FROM expenses WHERE registertime BETWEEN '$openingtime' AND '$closingtime' AND moneysource = 2";
				
		$expenseResultBank = mysql_query($selectExpensesBank)
			or handleError($lang['error-expensesload'],"Error loading expense from db: " . mysql_error());
			
		$row = mysql_fetch_array($expenseResultBank);
		$bankExpenses = $row['SUM(amount)'];
		
		
		// Look up today's membership fees
		$selectMembershipFees = "SELECT SUM(amountPaid) FROM memberpayments WHERE paymentdate BETWEEN '$openingtime' AND '$closingtime' AND paidTo <> 2";
				
		$result = mysql_query($selectMembershipFees)
			or handleError($lang['error-expensesload'],"Error loading expense from db: " . mysql_error());
			
		$row = mysql_fetch_array($result);
			$membershipFees = $row['SUM(amountPaid)'];
					
		// Look up today's membership fees paid to bank
		$selectMembershipFees = "SELECT SUM(amountPaid) FROM memberpayments WHERE paymentdate BETWEEN '$openingtime' AND '$closingtime' AND paidTo = 2";
				
		$result = mysql_query($selectMembershipFees)
			or handleError($lang['error-expensesload'],"Error loading expense from db: " . mysql_error());
			
		$row = mysql_fetch_array($result);
			$membershipFeesBank = $row['SUM(amountPaid)'];
			
		// Look up money banked during the day
		$selectBanked = "SELECT SUM(amount) FROM banked WHERE time BETWEEN '$openingtime' AND '$closingtime'";
				
		$result = mysql_query($selectBanked)
			or handleError($lang['error-expensesload'],"Error loading expense from db: " . mysql_error());
			
		$row = mysql_fetch_array($result);
			$bankedDuringDay = $row['SUM(amount)'];
			
			
		// Calculate bank balance
		$closingBank = $bankBalance + $banked + $bankedDuringDay + $donationsTodayBank + $membershipFeesBank - $bankExpenses;
		
				
		// Calculate estimated till
		$estimatedTill = $tillBalance + $membershipFees + $donationsToday - $expenses - $banked - $bankedDuringDay;
		
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
<br /><br />
<form onsubmit='oneClick.disabled = true; return true;' id="registerForm" action="?saveReception" method="POST"><br />
 <input type="hidden" name="step3" value="complete" />
 <input type="hidden" name="salesToday" value="<?php echo $salesToday; ?>" />
 <input type="hidden" name="salesTodayPaid" value="<?php echo $salesTodayPaid; ?>" />
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
	 <td><input type="number" lang="nb" name="tillTot" id="tillTot" class="fourDigit" value="<?php echo $tillTot; ?>" readonly /> <a href="close-shift-reception-1.php?recount" class="recount"><?php echo $lang['closeday-recount']; ?>?</a></td>
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
  <div class="halfblock">
   <h3><?php echo $lang['closeday-bankbalance']; ?></h3>
   <table>
    <tr>
	 <td><?php echo $lang['bank-opening']; ?>:</td>
	 <td><input type="number" lang="nb" name="bankBalance" id="bankBalance" class="fourDigit" value="<?php echo $bankBalance; ?>" readonly /></td>
    </tr>		 
    <tr>
	 <td>+ <?php echo $lang['closeday-membershipfees-bank']; ?>:</td>
	 <td><input type="number" lang="nb" name="membershipFeesBank" id="membershipFeesBank" class="green fourDigit" value="<?php echo $membershipFeesBank; ?>" readonly /></td>
    </tr>		 
    <tr>
	 <td>+ <?php echo $lang['global-donations']; ?>:</td>
	 <td><input type="number" lang="nb" name="donationsTodayBank" id="donationsTodayBank" class="green fourDigit" value="<?php echo $donationsTodayBank; ?>" readonly /></td>
    </tr>		 
    <tr>
	 <td>+ <?php echo $lang['banked-now']; ?>:</td>
	 <td><input type="number" lang="nb" name="banked" id="banked" class="green fourDigit" value="<?php echo $banked; ?>" readonly /></td>
    </tr>		 
    <tr>
	 <td>+ <?php echo $lang['banked-during-shift']; ?>:</td>
	 <td><input type="number" lang="nb" name="bankedDuringDay" id="bankedDuringDay" class="green fourDigit" value="<?php echo $bankedDuringDay; ?>" readonly /></td>
    </tr>		 
    <tr>
	 <td>- <?php echo $lang['global-expenses']; ?>:</td>
	 <td><input type="number" lang="nb" name="bankExpenses" id="bankExpenses" class="fourDigit red" value="<?php echo $bankExpenses; ?>" readonly /></td>
    </tr>		 
    <tr>
	 <td><?php echo $lang['closeday-bankbalance']; ?>:</td>
	 <td><input type="number" lang="nb" name="closingBank" id="closingBank" class="fourDigit" value="<?php echo $closingBank; ?>" readonly /></td>
    </tr>		 
    <tr>
	 <td><?php echo $lang['closeday-bankcomment']; ?>:</td>
	 <td><textarea name="bankComment" placeholder="<?php echo $lang['global-comment']; ?>?"></textarea></td>
    </tr>
   </table>
  </div>
  <button name='oneClick' type="submit"><?php echo $lang['global-confirm']; ?></button>
</form>

<?php displayFooter(); ?>
