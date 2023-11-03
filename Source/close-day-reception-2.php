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
	
	if ($_SESSION['openAndClose'] == 2) {
		
		$closingtime = $_SESSION['closingtime'];
		
		if ($_SESSION['noCompare'] != 'true') {
			
			$openingid = $_SESSION['openingid'];
			$openingtime = $_SESSION['openingtime'];
			$tillBalance = $_SESSION['tillBalance']; // Only do this line if Comparing is active!!
			$bankBalance = $_SESSION['bankBalance']; // Only do this line if Comparing is active!!
			
		}

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
			$tillTot = $_POST['tillTot'];
			
	
			$closingBank = $_POST['closingBank'];
			
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
			$selectMembers = "SELECT COUNT(memberno) from users WHERE (userGroup BETWEEN '1' AND '4') OR (userGroup = 5 AND (DATE(paidUntil) >= DATE('$closingtime') OR exento = 1))";
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
				
			
			if ($_SESSION['noCompare'] != 'true') {
				
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
				$selectMembers = "SELECT COUNT(memberno) from users WHERE DATE(paidUntil) = DATE_ADD(DATE('$openingtime'), INTERVAL -1 DAY) AND exento = 0";
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
					
			}
			
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
						
			if ($_SESSION['noCompare'] != 'true') {
	
				$openingLookup = "SELECT dayOpenedNo FROM closing WHERE closingid = $openingid";
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
					$dayClosedNo = $row['dayOpenedNo'];
					
			} else {
			
				$openingLookup = "SELECT closingid FROM closing WHERE currentClosing = 1 ORDER BY closingtime DESC";
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
					$dayClosedNo = $row['closingid'];
				
			}
				
			if ($dayClosedNo > 0) {
				
				// Means part of the day has been closed already, so use UPDATE
				
				$realClosingtime = date('Y-m-d H:i:s');
	
				
				// Only save all values if it's NOT the first closing!
				if ($_SESSION['noCompare'] != 'true') {

					$query = sprintf("UPDATE closing SET openingtime = '%s', closingtime = '%s', shiftEnd = '%s', oneCent = '%d', twoCent = '%d', fiveCent = '%d', tenCent = '%d', twentyCent = '%d', fiftyCent = '%d', oneEuro = '%d', twoEuro = '%d', fiveEuro = '%d', tenEuro = '%d', twentyEuro = '%d', fiftyEuro = '%d', hundredEuro = '%d', coinsTot = '%f', notesTot = '%f', cashintill = '%f', tillComment = '%s', closingbalance = '%f', bankBalance = '%f', newmembers = '%d', moneytaken = '%f', takenduringday = '%f', soldtoday = '%f', quantitySold = '%f', expenses = '%f', membershipFees = '%f', estimatedTill = '%f', tillDelta = '%f', bankExpenses = '%f', income = '%f', closedby = '%d', donations = '%f', renewedMembers = '%d', bannedMembers = '%d', deletedMembers = '%d', totalMembers = '%d', activeMembers = '%d', expiredMembers = '%d', bankDonations = '%f', membershipfeesBank = '%f', unitsSold = '%d', openingBalance = '%f', openingBalanceBank = '%f', totCredit = '%f' WHERE closingid = '%d';",		  
					$openingtime, $realClosingtime, $closingtime, $closeCount['oneCent'], $closeCount['twoCent'], $closeCount['fiveCent'], $closeCount['tenCent'], $closeCount['twentyCent'], $closeCount['fiftyCent'], $closeCount['oneEuro'], $closeCount['twoEuro'], $closeCount['fiveEuro'], $closeCount['tenEuro'], $closeCount['twentyEuro'], $closeCount['fiftyEuro'], $closeCount['hundredEuro'], $closeCount['coinsTot'], $closeCount['notesTot'], $closeCount['tillTot'], $tillComment, $closingBalance, $closingBank, $newmembers, $banked, $bankedduringday, $salesToday, $quantitySold, $expenses, $membershipFees, $estimatedTill, $tillDelta, $bankExpenses, $totalIncome, $_SESSION['user_id'], $donationsToday, $renewedMembers, $bannedmembers, $deletedmembers, $currentmembers, $activemembers, $expiredmembers, $donationsTodayBank, $membershipFeesBank, $unitsSold, $tillBalance, $bankBalance, $totCredit, $dayClosedNo);
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
						
					
					$updateOpening = sprintf("UPDATE closing SET recOpened = 2, recOpenedAt = '%s' WHERE closingid = '%d';",
						$realClosingtime,
						$openingid
						);
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
						
				} else {
					
					$query = sprintf("UPDATE closing SET closingtime = '%s', shiftEnd = '%s', oneCent = '%d', twoCent = '%d', fiveCent = '%d', tenCent = '%d', twentyCent = '%d', fiftyCent = '%d', oneEuro = '%d', twoEuro = '%d', fiveEuro = '%d', tenEuro = '%d', twentyEuro = '%d', fiftyEuro = '%d', hundredEuro = '%d', coinsTot = '%f', notesTot = '%f', cashintill = '%f', tillComment = '%s', closingbalance = '%f', bankBalance = '%f', moneytaken = '%f', takenduringday = '%f', closedby = '%d', totalMembers = '%d', activeMembers = '%d', totCredit = '%f' WHERE closingid = '%d';",		  
					$realClosingtime, $closingtime, $closeCount['oneCent'], $closeCount['twoCent'], $closeCount['fiveCent'], $closeCount['tenCent'], $closeCount['twentyCent'], $closeCount['fiftyCent'], $closeCount['oneEuro'], $closeCount['twoEuro'], $closeCount['fiveEuro'], $closeCount['tenEuro'], $closeCount['twentyEuro'], $closeCount['fiftyEuro'], $closeCount['hundredEuro'], $closeCount['coinsTot'], $closeCount['notesTot'], $closeCount['tillTot'], $tillComment, $closingBalance, $closingBank, $banked, $bankedduringday, $_SESSION['user_id'], $currentmembers, $activemembers, $totCredit, $dayClosedNo);
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
						
					
					$updateOpening = sprintf("UPDATE closing SET recClosed = 2 WHERE closingid = '%d';",
						$dayClosedNo
						);
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
						
				}
				
				
			} else {
				
				$realClosingtime = date('Y-m-d H:i:s');
				
				// Only save all values if it's NOT the first closing!
				if ($_SESSION['noCompare'] != 'true') {
			  
					$query = sprintf("INSERT INTO closing (openingtime, closingtime, shiftEnd, oneCent, twoCent, fiveCent, tenCent, twentyCent, fiftyCent, oneEuro, twoEuro, fiveEuro, tenEuro, twentyEuro, fiftyEuro, hundredEuro, coinsTot, notesTot, cashintill, tillComment, closingbalance, bankBalance, newmembers, moneytaken, takenduringday, soldtoday, quantitySold, expenses, membershipFees, estimatedTill, tillDelta, bankExpenses, income, closedby, donations, renewedMembers, bannedMembers, deletedMembers, totalMembers, activeMembers, expiredMembers, bankDonations, membershipfeesBank, unitsSold, openingBalance, openingBalanceBank, currentClosing, totCredit) VALUES ('%s', '%s', '%s', '%d', '%d', '%d', '%d', '%d', '%d', '%d', '%d', '%d', '%d', '%d', '%d', '%d', '%f', '%f', '%f', '%s', '%f', '%f', '%d', '%f', '%f', '%f', '%f', '%f', '%f', '%f', '%f', '%f', '%f', '%d', '%f', '%d', '%d', '%d', '%d', '%d', '%d', '%f', '%f', '%d', '%f', '%f', '%d', '%f');",
					$openingtime, $realClosingtime, $closingtime, $closeCount['oneCent'], $closeCount['twoCent'], $closeCount['fiveCent'], $closeCount['tenCent'], $closeCount['twentyCent'], $closeCount['fiftyCent'], $closeCount['oneEuro'], $closeCount['twoEuro'], $closeCount['fiveEuro'], $closeCount['tenEuro'], $closeCount['twentyEuro'], $closeCount['fiftyEuro'], $closeCount['hundredEuro'], $closeCount['coinsTot'], $closeCount['notesTot'], $closeCount['tillTot'], $tillComment, $closingBalance, $closingBank, $newmembers, $banked, $bankedduringday, $salesToday, $quantitySold, $expenses, $membershipFees, $estimatedTill, $tillDelta, $bankExpenses, $totalIncome, $_SESSION['user_id'], $donationsToday, $renewedMembers, $bannedmembers, $deletedmembers, $currentmembers, $activemembers, $expiredmembers, $donationsTodayBank, $membershipFeesBank, $unitsSold, $tillBalance, $bankBalance, '1', $totCredit);
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
	
					
					$updateOpening = sprintf("UPDATE closing SET recOpened = 2, recOpenedAt = '%s', dayOpenedNo = '%d' WHERE closingid = '%d';",
						$realClosingtime,
						$closingid,
						$openingid
						);
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
			
				} else {
					
					$query = sprintf("INSERT INTO closing (closingtime, shiftEnd, oneCent, twoCent, fiveCent, tenCent, twentyCent, fiftyCent, oneEuro, twoEuro, fiveEuro, tenEuro, twentyEuro, fiftyEuro, hundredEuro, coinsTot, notesTot, cashintill, tillComment, closingbalance, bankBalance, moneytaken, takenduringday, closedby,totalMembers, activeMembers, currentClosing, totCredit) VALUES ('%s', '%s', '%d', '%d', '%d', '%d', '%d', '%d', '%d', '%d', '%d', '%d', '%d', '%d', '%d', '%f', '%f', '%f', '%s', '%f', '%f', '%f', '%f', '%d', '%d', '%d', '%d', '%f');",
					$realClosingtime, $closingtime, $closeCount['oneCent'], $closeCount['twoCent'], $closeCount['fiveCent'], $closeCount['tenCent'], $closeCount['twentyCent'], $closeCount['fiftyCent'], $closeCount['oneEuro'], $closeCount['twoEuro'], $closeCount['fiveEuro'], $closeCount['tenEuro'], $closeCount['twentyEuro'], $closeCount['fiftyEuro'], $closeCount['hundredEuro'], $closeCount['coinsTot'], $closeCount['notesTot'], $closeCount['tillTot'], $tillComment, $closingBalance, $closingBank, $banked, $bankedduringday, $_SESSION['user_id'], $currentmembers, $activemembers, '1', $totCredit);
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
					
					
					$updateOpening = sprintf("UPDATE closing SET recClosed = 2 WHERE closingid = '%d';",
						$closingid
						);
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
						
				}
				
			}
			
			// On success: redirect.
			$_SESSION['successMessage'] = $lang['reception-closed-successfully'];
			header("Location: close-day.php");
			exit();

		}
	## ON PAGE SUBMISSION END ##
	
	// Is step 2 complete?
	if ($_POST['step2'] != 'complete') {
		echo $lang['global-twonotcomplete'];
		exit();
	}
	
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
	
	if ($_SESSION['noCompare'] != 'true') {

		$tillBalance = $_SESSION['tillBalance'];
		$bankBalance = $_SESSION['bankBalance'];

	}
	
	// Create a session array with the closing count
	$_SESSION['closeCount'] = array("oneCent"=>"$oneCent", "twoCent"=>"$twoCent", "fiveCent"=>"$fiveCent", "tenCent"=>"$tenCent", "twentyCent"=>"$twentyCent", "fiftyCent"=>"$fiftyCent", "oneEuro"=>"$oneEuro", "twoEuro"=>"$twoEuro", "fiveEuro"=>"$fiveEuro", "tenEuro"=>"$tenEuro", "twentyEuro"=>"$twentyEuro", "fiftyEuro"=>"$fiftyEuro", "hundredEuro"=>"$hundredEuro", "coinsTot"=>"$coinsTot", "notesTot"=>"$notesTot", "tillTot"=>"$tillTot", "banked"=>"$banked", "oneCentFull"=>"$oneCentFull", "twoCentFull"=>"$twoCentFull", "fiveCentFull"=>"$fiveCentFull", "tenCentFull"=>"$tenCentFull", "twentyCentFull"=>"$twentyCentFull", "fiftyCentFull"=>"$fiftyCentFull", "oneEuroFull"=>"$oneEuroFull", "twoEuroFull"=>"$twoEuroFull", "fiveEuroFull"=>"$fiveEuroFull", "tenEuroFull"=>"$tenEuroFull", "twentyEuroFull"=>"$twentyEuroFull", "fiftyEuroFull"=>"$fiftyEuroFull", "hundredEuroFull"=>"$hundredEuroFull", "coinsTotFull"=>"$coinsTotFull", "notesTotFull"=>"$notesTotFull", "tillTotFull"=>"$tillTotFull");
	
	if ($_SESSION['noCompare'] != 'true') {

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
		$selectDonations = "SELECT SUM(amount) from donations WHERE donatedTo <> 2 AND donatedTo <> 3 AND donationTime BETWEEN '$openingtime' AND '$closingtime'";
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
		$selectMembershipFees = "SELECT SUM(amountPaid) FROM memberpayments WHERE paymentdate BETWEEN '$openingtime' AND '$closingtime' AND (paidTo <> 2 OR paidTo = 4)";
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
	
	}
		
	// Calculate bank balance
	$closingBank = $bankBalance + $banked + $bankedDuringDay + $donationsTodayBank + $salesTodayBank + $salesBarTodayBank + $membershipFeesBank - $bankExpenses;
			
	// Calculate estimated till
	$estimatedTill = $tillBalance + $membershipFees + $donationsToday + $salesTodayCash + $salesBarTodayCash - $expenses - $banked - $bankedDuringDay;
	
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

		pageStart($lang['title-closeday'], NULL, $confirmLeave, "pcloseday", "step3", $lang['closeday-rec-three'], $_SESSION['successMessage'], $_SESSION['errorMessage']);
			
		echo $_SESSION['pageHeader'];
		
		if ($_SESSION['noCompare'] != 'true') {
	
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
	 <td>- <?php echo $lang['closeday-moneybanked']; ?>:</td>
	 <td><input type="number" lang="nb" name="banked" id="banked" class="fourDigit red" value="<?php echo $banked; ?>" readonly /></td>
    </tr>		 
    <tr>
	 <td>- <?php echo $lang['banked-during-day']; ?>:</td>
	 <td><input type="number" lang="nb" name="bankedDuringDay" id="bankedDuringDay" class="fourDigit red" value="<?php echo $bankedDuringDay; ?>" readonly /></td>
    </tr>		 
    <tr>
	 <td><?php echo $lang['closeday-estimatedtill']; ?>:</td>
	 <td><input type="number" lang="nb" name="estimatedTill" id="estimatedTill" class="fourDigit" value="<?php echo $estimatedTill; ?>" readonly /></td>
    </tr>		 
    <tr>
	 <td><?php echo $lang['closeday-yourcount']; ?>:</td>
	 <td><input type="number" lang="nb" name="tillTot" id="tillTot" class="fourDigit" value="<?php echo $tillTot; ?>" readonly /> <a href="close-day-reception-1.php?recount" class="recount"><?php echo $lang['closeday-recount']; ?>?</a></td>
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

<?php

		} else {

?>


<form onsubmit='oneClick.disabled = true; return true;' id="registerForm" action="?saveReception" method="POST"><br />
 <input type="hidden" name="step3" value="complete" />
 <input type="hidden" name="salesToday" value="<?php echo $salesToday; ?>" />
 <input type="hidden" name="salesTodayPaid" value="<?php echo $salesTodayPaid; ?>" />
 <input type="hidden" name="salesTodayBank" value="<?php echo $salesTodayBank; ?>" />
 <input type="hidden" name="salesBarTodayBank" value="<?php echo $salesBarTodayBank; ?>" />
 <input type="hidden" name="membershipFeesBank" value="<?php echo $membershipFeesBank; ?>" />
 <input type="hidden" name="donationsTodayBank" value="<?php echo $donationsTodayBank; ?>" />

  <div class="halfblock">
   <h3><?php echo $lang['closeday-tillbalance']; ?></h3>
   <table>
    <tr>
	 <td><?php echo $lang['closeday-moneybanked']; ?>:</td>
	 <td><input type="number" lang="nb" name="banked" id="banked" class="fourDigit" value="<?php echo $banked; ?>" readonly /></td>
    </tr>
    <tr>
	 <td><?php echo $lang['closeday-tillbalance']; ?>:</td>
	 <td><input type="number" lang="nb" name="tillTot" id="tillTot" class="fourDigit" value="<?php echo $tillTot; ?>" readonly /> <a href="close-day-reception-1.php?recount" class="recount"><?php echo $lang['closeday-recount']; ?>?</a></td>
    </tr>		 
    <tr>
	 <td><?php echo $lang['closeday-tillcomment']; ?>:</td>
	 <td><textarea name="tillComment" placeholder="Comment?"></textarea></td>
    </tr>		 
   </table>
  </div>
  <button name='oneClick' type="submit"><?php echo $lang['global-confirm']; ?></button>
</form>


<?php

		}
	
	
	
	
	
	
	
	
	
	
	
	
	
	} else {
		
		$closingtime = $_SESSION['closingtime'];
		
		$openingid = $_SESSION['openingid'];
		$openingtime = $_SESSION['openingtime'];
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
			$tillTot = $_POST['tillTot'];
			
	
			$closingBank = $_POST['closingBank'];
			
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
			$selectMembers = "SELECT COUNT(memberno) from users WHERE (userGroup BETWEEN '1' AND '4') OR (userGroup = 5 AND (DATE(paidUntil) >= DATE('$closingtime') OR exento = 1))";
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
			$selectMembers = "SELECT COUNT(memberno) from users WHERE DATE(paidUntil) = DATE_ADD(DATE('$openingtime'), INTERVAL -1 DAY) AND exento = 0";
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
			

			$openingLookup = "SELECT dayClosedNo FROM opening WHERE openingid = $openingid";
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
				$dayClosedNo = $row['dayClosedNo'];
				
			$realClosingtime = date('Y-m-d H:i:s');
				
			if ($dayClosedNo > 0) {
				
				// Means part of the day has been closed already, so use UPDATE
				$query = sprintf("UPDATE closing SET openingtime = '%s', closingtime = '%s', shiftEnd = '%s', oneCent = '%d', twoCent = '%d', fiveCent = '%d', tenCent = '%d', twentyCent = '%d', fiftyCent = '%d', oneEuro = '%d', twoEuro = '%d', fiveEuro = '%d', tenEuro = '%d', twentyEuro = '%d', fiftyEuro = '%d', hundredEuro = '%d', coinsTot = '%f', notesTot = '%f', cashintill = '%f', tillComment = '%s', closingbalance = '%f', bankBalance = '%f', newmembers = '%d', moneytaken = '%f', takenduringday = '%f', soldtoday = '%f', quantitySold = '%f', expenses = '%f', membershipFees = '%f', estimatedTill = '%f', tillDelta = '%f', bankExpenses = '%f', income = '%f', closedby = '%d', donations = '%f', renewedMembers = '%d', bannedMembers = '%d', deletedMembers = '%d', totalMembers = '%d', activeMembers = '%d', expiredMembers = '%d', bankDonations = '%f', membershipfeesBank = '%f', unitsSold = '%d', openingBalance = '%f', openingBalanceBank = '%f', totCredit = '%f' WHERE closingid = '%d';",		  
				$openingtime, $realClosingtime, $closingtime, $closeCount['oneCent'], $closeCount['twoCent'], $closeCount['fiveCent'], $closeCount['tenCent'], $closeCount['twentyCent'], $closeCount['fiftyCent'], $closeCount['oneEuro'], $closeCount['twoEuro'], $closeCount['fiveEuro'], $closeCount['tenEuro'], $closeCount['twentyEuro'], $closeCount['fiftyEuro'], $closeCount['hundredEuro'], $closeCount['coinsTot'], $closeCount['notesTot'], $closeCount['tillTot'], $tillComment, $closingBalance, $closingBank, $newmembers, $banked, $bankedduringday, $salesToday, $quantitySold, $expenses, $membershipFees, $estimatedTill, $tillDelta, $bankExpenses, $totalIncome, $_SESSION['user_id'], $donationsToday, $renewedMembers, $bannedmembers, $deletedmembers, $currentmembers, $activemembers, $expiredmembers, $donationsTodayBank, $membershipFeesBank, $unitsSold, $tillBalance, $bankBalance, $totCredit, $dayClosedNo);
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
					
					
				$updateOpening = sprintf("UPDATE opening SET recClosed = 2, recClosedAt = '%s' WHERE openingid = '%d';",
					$realClosingtime,
					$openingid
					);
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
				
			} else {
				
			  	$query = sprintf("INSERT INTO closing (openingtime, closingtime, shiftEnd, oneCent, twoCent, fiveCent, tenCent, twentyCent, fiftyCent, oneEuro, twoEuro, fiveEuro, tenEuro, twentyEuro, fiftyEuro, hundredEuro, coinsTot, notesTot, cashintill, tillComment, closingbalance, bankBalance, newmembers, moneytaken, takenduringday, soldtoday, quantitySold, expenses, membershipFees, estimatedTill, tillDelta, bankExpenses, income, closedby, donations, renewedMembers, bannedMembers, deletedMembers, totalMembers, activeMembers, expiredMembers, bankDonations, membershipfeesBank, unitsSold, openingBalance, openingBalanceBank, totCredit) VALUES ('%s', '%s', '%s', '%d', '%d', '%d', '%d', '%d', '%d', '%d', '%d', '%d', '%d', '%d', '%d', '%d', '%f', '%f', '%f', '%s', '%f', '%f', '%d', '%f', '%f', '%f', '%f', '%f', '%f', '%f', '%f', '%f', '%f', '%d', '%f', '%d', '%d', '%d', '%d', '%d', '%d', '%f', '%f', '%d', '%f', '%f', '%f');",
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
			
				
				$updateOpening = sprintf("UPDATE opening SET recClosed = 2, dayClosedNo = '%d', recClosedAt = '%s' WHERE openingid = '%d';",
					$closingid,
					$realClosingtime,
					$openingid
					);
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

			}
			
			// On success: redirect.
			$_SESSION['successMessage'] = $lang['reception-closed-successfully'];
			header("Location: close-day.php");
			exit();
	
		}
	## ON PAGE SUBMISSION END ##
	
		// Is step 2 complete?
		if ($_POST['step2'] != 'complete') {
			echo $lang['global-twonotcomplete'];
			exit();
		}
		
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
		
		if ($_SESSION['noCompare'] != 'true') {
	
			$tillBalance = $_SESSION['tillBalance'];
			$bankBalance = $_SESSION['bankBalance'];
	
		}
		
		// Create a session array with the closing count
		$_SESSION['closeCount'] = array("oneCent"=>"$oneCent", "twoCent"=>"$twoCent", "fiveCent"=>"$fiveCent", "tenCent"=>"$tenCent", "twentyCent"=>"$twentyCent", "fiftyCent"=>"$fiftyCent", "oneEuro"=>"$oneEuro", "twoEuro"=>"$twoEuro", "fiveEuro"=>"$fiveEuro", "tenEuro"=>"$tenEuro", "twentyEuro"=>"$twentyEuro", "fiftyEuro"=>"$fiftyEuro", "hundredEuro"=>"$hundredEuro", "coinsTot"=>"$coinsTot", "notesTot"=>"$notesTot", "tillTot"=>"$tillTot", "banked"=>"$banked", "oneCentFull"=>"$oneCentFull", "twoCentFull"=>"$twoCentFull", "fiveCentFull"=>"$fiveCentFull", "tenCentFull"=>"$tenCentFull", "twentyCentFull"=>"$twentyCentFull", "fiftyCentFull"=>"$fiftyCentFull", "oneEuroFull"=>"$oneEuroFull", "twoEuroFull"=>"$twoEuroFull", "fiveEuroFull"=>"$fiveEuroFull", "tenEuroFull"=>"$tenEuroFull", "twentyEuroFull"=>"$twentyEuroFull", "fiftyEuroFull"=>"$fiftyEuroFull", "hundredEuroFull"=>"$hundredEuroFull", "coinsTotFull"=>"$coinsTotFull", "notesTotFull"=>"$notesTotFull", "tillTotFull"=>"$tillTotFull");
		
	
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
		$selectDonations = "SELECT SUM(amount) from donations WHERE donatedTo <> 2 AND donatedTo <> 3 AND donationTime BETWEEN '$openingtime' AND '$closingtime'";
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
		$selectMembershipFees = "SELECT SUM(amountPaid) FROM memberpayments WHERE paymentdate BETWEEN '$openingtime' AND '$closingtime' AND (paidTo < 2 OR paidTo = 4)";
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

		pageStart($lang['title-closeday'], NULL, $confirmLeave, "pcloseday", "step3", $lang['closeday-rec-three'], $_SESSION['successMessage'], $_SESSION['errorMessage']);
			
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
	 <td>- <?php echo $lang['banked-during-day']; ?>:</td>
	 <td><input type="number" lang="nb" name="bankedDuringDay" id="bankedDuringDay" class="fourDigit red" value="<?php echo $bankedDuringDay; ?>" readonly /></td>
    </tr>		 
    <tr>
	 <td><?php echo $lang['closeday-estimatedtill']; ?>:</td>
	 <td><input type="number" lang="nb" name="estimatedTill" id="estimatedTill" class="fourDigit" value="<?php echo $estimatedTill; ?>" readonly /></td>
    </tr>		 
    <tr>
	 <td><?php echo $lang['closeday-yourcount']; ?>:</td>
	 <td><input type="number" lang="nb" name="tillTot" id="tillTot" class="fourDigit" value="<?php echo $tillTot; ?>" readonly /> <a href="close-day-reception-1.php?recount" class="recount"><?php echo $lang['closeday-recount']; ?>?</a></td>
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

<?php	

	}
	
displayFooter();