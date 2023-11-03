<?php
	
	require_once 'cOnfig/connection.php';
	require_once 'cOnfig/view.php';
	require_once 'cOnfig/authenticate.php';
	require_once 'cOnfig/languages/common.php';
	
	session_start();
	$accessLevel = '3';
	
	// Authenticate & authorize
	authorizeUser($accessLevel);
	
	getSettings();
	
	echo "<META HTTP-EQUIV='refresh' CONTENT='30'>";
	
	if ($_SESSION['domain'] == 'cloud') {
		
		// Look up today's till expenses
		$selectExpenses = "SELECT SUM(amount) FROM expenses WHERE DATE(registertime) = DATE(NOW()) AND moneysource = 1 AND TIME(registertime) > TIME('01:00:00')";
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
			
		// Look up todays donations
		$selectDonations = "SELECT SUM(amount) from donations WHERE (donatedTo < 2 OR donatedTo = 4) AND DATE(donationTime) = DATE(NOW()) AND TIME(donationTime) > TIME('01:00:00')";
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
			
		// Look up today's membership fees
		$selectMembershipFees = "SELECT SUM(amountPaid) FROM memberpayments WHERE (paidTo < 2 OR paidTo = 4) AND DATE(paymentdate) = DATE(NOW()) AND TIME(paymentdate) > TIME('01:00:00')";
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
			
		// Look up todays bank donations
		$selectDonations = "SELECT SUM(amount) from donations WHERE donatedTo = 2 AND DATE(donationTime) = DATE(NOW()) AND TIME(donationTime) > TIME('01:00:00')";
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
			
			
		// Look up today's membership fees Bank
		$selectMembershipFees = "SELECT SUM(amountPaid) FROM memberpayments WHERE paidTo = 2 AND DATE(paymentdate) = DATE(NOW()) AND TIME(paymentdate) > TIME('01:00:00')";
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
			
		// Query to look up daily donations & membership fees
		$selectExpenses = "SELECT '1' AS type, donationTime AS time, userid, amount, donatedTo, operator FROM donations WHERE DATE(donationTime) = DATE(NOW()) AND TIME(donationTime) > TIME('01:00:00') UNION ALL SELECT '2' AS type, paymentdate AS time, userid, amountPaid AS amount, paidTo AS donatedTo, operator FROM memberpayments WHERE DATE(paymentdate) = DATE(NOW()) AND TIME(paymentdate) > TIME('01:00:00')";
		try
		{
			$results = $pdo3->prepare("$selectExpenses");
			$results->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
	
	
		// Look up money banked during the day
		$selectBanked = "SELECT SUM(amount) FROM banked WHERE DATE(time) = DATE(NOW()) AND TIME(time) > TIME('01:00:00')";
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
			
		// Look up todays card purchases
		$selectCard = "SELECT SUM(amount) from card_purchase WHERE paidTo = 2 AND DATE(time) = DATE(NOW()) AND TIME(time) > TIME('01:00:00')";
		try
		{
			$result = $pdo3->prepare("$selectCard");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
		
		$row = $result->fetch();
			$cardBank = $row['SUM(amount)'];
			
		$selectCard = "SELECT SUM(amount) from card_purchase WHERE paidTo = 1 AND DATE(time) = DATE(NOW()) AND TIME(time) > TIME('01:00:00')";
		try
		{
			$result = $pdo3->prepare("$selectCard");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
		
		$row = $result->fetch();
			$cardCash = $row['SUM(amount)'];
			
			
			
		// Direct Dispensing)
		if ($_SESSION['creditOrDirect'] == 0) {
							
			// Look up dispensed today cash
			$selectSales = "SELECT SUM(amount) from sales WHERE DATE(saletime) = DATE(NOW()) AND direct < 2 AND TIME(saletime) > TIME('01:00:00')";
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
			$selectSales = "SELECT SUM(amount) from sales WHERE DATE(saletime) = DATE(NOW()) AND direct = 2 AND TIME(saletime) > TIME('01:00:00')";
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
			$selectSales = "SELECT SUM(amount) from b_sales WHERE DATE(saletime) = DATE(NOW()) AND direct < 2 AND TIME(saletime) > TIME('01:00:00')";
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
			$selectSales = "SELECT SUM(amount) from b_sales WHERE DATE(saletime) = DATE(NOW()) AND direct = 2 AND TIME(saletime) > TIME('01:00:00')";
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
		
	} else if ($_SESSION['openAndClose'] == 2) {
		
		$openingLookup = "SELECT cashintill, closingtime FROM closing ORDER BY closingtime DESC LIMIT 1";
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
			$tillBalance = $row['cashintill'];
			$closingtime = $row['closingtime'];
			
		// Look up today's till expenses
		$selectExpenses = "SELECT SUM(amount) FROM expenses WHERE moneysource = 1 AND registertime >= '$closingtime'";
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
			
		// Look up todays donations
		$selectDonations = "SELECT SUM(amount) from donations WHERE (donatedTo < 2 OR donatedTo = 4) AND donationTime >= '$closingtime'";
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
			
		// Look up today's membership fees
		$selectMembershipFees = "SELECT SUM(amountPaid) FROM memberpayments WHERE (paidTo < 2 OR paidTo = 4) AND paymentdate >= '$closingtime'";
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
			
		// Look up todays bank donations
		$selectDonations = "SELECT SUM(amount) from donations WHERE donatedTo = 2 AND donationTime >= '$closingtime'";
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
			
			
		// Look up today's membership fees Bank
		$selectMembershipFees = "SELECT SUM(amountPaid) FROM memberpayments WHERE paidTo = 2 AND paymentdate >= '$closingtime'";
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
			
		// Query to look up daily donations & membership fees
		$selectExpenses = "SELECT '1' AS type, donationTime AS time, userid, amount, donatedTo, operator FROM donations WHERE donationTime >= '$closingtime' UNION ALL SELECT '2' AS type, paymentdate AS time, userid, amountPaid AS amount, paidTo AS donatedTo, operator FROM memberpayments WHERE paymentdate >= '$closingtime'";
		try
		{
			$results = $pdo3->prepare("$selectExpenses");
			$results->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
	
	
		// Look up money banked during the day
		$selectBanked = "SELECT SUM(amount) FROM banked WHERE time >= '$closingtime'";
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
			
		// Look up todays card purchases
		$selectCard = "SELECT SUM(amount) from card_purchase WHERE paidTo = 2 AND time >= '$closingtime'";
		try
		{
			$result = $pdo3->prepare("$selectCard");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
		
		$row = $result->fetch();
			$cardBank = $row['SUM(amount)'];
			
		$selectCard = "SELECT SUM(amount) from card_purchase WHERE paidTo = 1 AND time >= '$closingtime'";
		try
		{
			$result = $pdo3->prepare("$selectCard");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
		
		$row = $result->fetch();
			$cardCash = $row['SUM(amount)'];

			
			
		// Direct Dispensing)
		if ($_SESSION['creditOrDirect'] == 0) {
							
			// Look up dispensed today cash
			$selectSales = "SELECT SUM(amount) from sales WHERE direct < 2 AND saletime >= '$closingtime'";
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
			$selectSales = "SELECT SUM(amount) from sales WHERE direct = 2 AND saletime >= '$closingtime'";
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
			$selectSales = "SELECT SUM(amount) from b_sales WHERE direct < 2 AND saletime >= '$closingtime'";
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
			$selectSales = "SELECT SUM(amount) from b_sales WHERE direct = 2 AND saletime >= '$closingtime'";
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
			
	} else if ($_SESSION['openAndClose'] == 3 || $_SESSION['openAndClose'] == 4) {
		
		if ($domain == 'exclusive') {
			$openingLookup = "SELECT tillBalance, openingtime FROM recshiftopen ORDER BY openingtime DESC LIMIT 1";
		} else {
			$openingLookup = "SELECT tillBalance, openingtime FROM opening ORDER BY openingtime DESC LIMIT 1";
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
			$tillBalance = $row['tillBalance'];
			$openingtime = $row['openingtime'];

		// Look up today's till expenses
		$selectExpenses = "SELECT SUM(amount) FROM expenses WHERE moneysource = 1 AND registertime >= '$openingtime'";
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
			
		// Look up todays donations
		$selectDonations = "SELECT SUM(amount) from donations WHERE (donatedTo < 2 OR donatedTo = 4) AND donationTime >= '$openingtime'";
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
			
		// Look up today's membership fees
		$selectMembershipFees = "SELECT SUM(amountPaid) FROM memberpayments WHERE (paidTo < 2 OR paidTo = 4) AND paymentdate >= '$openingtime'";
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
			
		// Look up todays bank donations
		$selectDonations = "SELECT SUM(amount) from donations WHERE donatedTo = 2 AND donationTime >= '$openingtime'";
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
			
			
		// Look up today's membership fees Bank
		$selectMembershipFees = "SELECT SUM(amountPaid) FROM memberpayments WHERE paidTo = 2 AND paymentdate >= '$openingtime'";
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
			
		// Query to look up daily donations & membership fees
		$selectExpenses = "SELECT '1' AS type, donationTime AS time, userid, amount, donatedTo, operator FROM donations WHERE donationTime >= '$openingtime' UNION ALL SELECT '2' AS type, paymentdate AS time, userid, amountPaid AS amount, paidTo AS donatedTo, operator FROM memberpayments WHERE paymentdate >= '$openingtime'";
		try
		{
			$results = $pdo3->prepare("$selectExpenses");
			$results->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
	
	
		// Look up money banked during the day
		$selectBanked = "SELECT SUM(amount) FROM banked WHERE time >= '$openingtime'";
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
			
		// Look up todays card purchases
		$selectCard = "SELECT SUM(amount) from card_purchase WHERE paidTo = 2 AND time >= '$openingtime'";
		try
		{
			$result = $pdo3->prepare("$selectCard");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
		
		$row = $result->fetch();
			$cardBank = $row['SUM(amount)'];
			
		$selectCard = "SELECT SUM(amount) from card_purchase WHERE paidTo = 1 AND time >= '$openingtime'";
		try
		{
			$result = $pdo3->prepare("$selectCard");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
		
		$row = $result->fetch();
			$cardCash = $row['SUM(amount)'];
			
			
		// Direct Dispensing)
		if ($_SESSION['creditOrDirect'] == 0) {
							
			// Look up dispensed today cash
			$selectSales = "SELECT SUM(amount) from sales WHERE direct < 2 AND saletime >= '$openingtime'";
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
			$selectSales = "SELECT SUM(amount) from sales WHERE direct = 2 AND saletime >= '$openingtime'";
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
			$selectSales = "SELECT SUM(amount) from b_sales WHERE direct < 2 AND saletime >= '$openingtime'";
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
			$selectSales = "SELECT SUM(amount) from b_sales WHERE direct = 2 AND saletime >= '$openingtime'";
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
						
	} else {
			
		// Look up today's till expenses
		$selectExpenses = "SELECT SUM(amount) FROM expenses WHERE DATE(registertime) = DATE(NOW()) AND moneysource = 1 AND TIME(registertime) > TIME('05:00:00')";
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
			
		// Look up todays donations
		$selectDonations = "SELECT SUM(amount) from donations WHERE (donatedTo < 2 OR donatedTo = 4) AND DATE(donationTime) = DATE(NOW()) AND TIME(donationTime) > TIME('05:00:00')";
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
			
		// Look up today's membership fees
		$selectMembershipFees = "SELECT SUM(amountPaid) FROM memberpayments WHERE (paidTo < 2 OR paidTo = 4) AND DATE(paymentdate) = DATE(NOW()) AND TIME(paymentdate) > TIME('05:00:00')";
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
			
		// Look up todays bank donations
		$selectDonations = "SELECT SUM(amount) from donations WHERE donatedTo = 2 AND DATE(donationTime) = DATE(NOW()) AND TIME(donationTime) > TIME('05:00:00')";
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
			
			
		// Look up today's membership fees Bank
		$selectMembershipFees = "SELECT SUM(amountPaid) FROM memberpayments WHERE paidTo = 2 AND DATE(paymentdate) = DATE(NOW()) AND TIME(paymentdate) > TIME('05:00:00')";
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
			
		// Query to look up daily donations & membership fees
		$selectExpenses = "SELECT '1' AS type, donationTime AS time, userid, amount, donatedTo, operator FROM donations WHERE DATE(donationTime) = DATE(NOW()) AND TIME(donationTime) > TIME('05:00:00') UNION ALL SELECT '2' AS type, paymentdate AS time, userid, amountPaid AS amount, paidTo AS donatedTo, operator FROM memberpayments WHERE DATE(paymentdate) = DATE(NOW()) AND TIME(paymentdate) > TIME('05:00:00')";
		try
		{
			$results = $pdo3->prepare("$selectExpenses");
			$results->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
	
	
		// Look up money banked during the day
		$selectBanked = "SELECT SUM(amount) FROM banked WHERE DATE(time) = DATE(NOW()) AND TIME(time) > TIME('05:00:00')";
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
			
		// Look up todays card purchases
		$selectCard = "SELECT SUM(amount) from card_purchase WHERE paidTo = 2 AND DATE(time) = DATE(NOW()) AND TIME(time) > TIME('05:00:00')";
		try
		{
			$result = $pdo3->prepare("$selectCard");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
		
		$row = $result->fetch();
			$cardBank = $row['SUM(amount)'];
			
		$selectCard = "SELECT SUM(amount) from card_purchase WHERE paidTo = 1 AND DATE(time) = DATE(NOW()) AND TIME(time) > TIME('05:00:00')";
		try
		{
			$result = $pdo3->prepare("$selectCard");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
		
		$row = $result->fetch();
			$cardCash = $row['SUM(amount)'];
			
			
			
		// Direct Dispensing)
		if ($_SESSION['creditOrDirect'] == 0) {
							
			// Look up dispensed today cash
			$selectSales = "SELECT SUM(amount) from sales WHERE DATE(saletime) = DATE(NOW()) AND direct < 2 AND TIME(saletime) > TIME('05:00:00')";
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
			$selectSales = "SELECT SUM(amount) from sales WHERE DATE(saletime) = DATE(NOW()) AND direct = 2 AND TIME(saletime) > TIME('05:00:00')";
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
			$selectSales = "SELECT SUM(amount) from b_sales WHERE DATE(saletime) = DATE(NOW()) AND direct < 2 AND TIME(saletime) > TIME('05:00:00')";
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
			$selectSales = "SELECT SUM(amount) from b_sales WHERE DATE(saletime) = DATE(NOW()) AND direct = 2 AND TIME(saletime) > TIME('05:00:00')";
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
				
	}
		
		

	
	// Calculate estimated till	& club balances
	$tillTotalToday = $donations + $membershipFees + $salesTodayCash + $salesTodayBarCash + $cardCash;
	$bankTotalToday = $bankDonations + $membershipfeesBank + $salesTodayBank + $salesTodayBarBank + $cardBank;
	
	$totalToday = $tillTotalToday + $bankTotalToday;
	
	if ($_SESSION['creditOrDirect'] == 1) {
	
	$deleteDonationScript = <<<EOD
	
	    $(document).ready(function() {
			
			
			$('#mainTable').tablesorter({
				usNumberFormat: true,
				headers: {
					5: {
						sorter: "currency"
					}
				}
			}); 
			
	});

EOD;
	

	pageStart("Status", NULL, $deleteDonationScript, "pstatus", "", "STATUS", $_SESSION['successMessage'], $_SESSION['errorMessage']);
	
	echo <<<EOD
<table style='color: #444; text-align: right; font-family: Tahoma, Verdana, sans-serif; font-size: 14px;'>
 <tr>
  <td colspan='2' style='color: #5aa242; text-align: left; font-size: 17px; border-bottom: 2px solid #a80082;'><strong>{$lang['closeday-finances']}</strong></td>
 </tr>
 <tr>
  <td style='text-align: left;'>{$lang['closeday-tillatopening']}</td>
  <td>{$expr(number_format($tillBalance,2))} &euro;</td>
 </tr>
 <tr>
  <td style='text-align: left;'>+ {$lang['closeday-donations-till']}</td>
  <td>{$expr(number_format($donations,2))} &euro;</td>
 </tr>
 <tr>
  <td style='text-align: left;'>+ {$lang['closeday-membershipfees-till']}</td>
  <td>{$expr(number_format($membershipFees,2))} &euro;</td>
 </tr>
 <tr>
  <td style='text-align: left;'>+ {$lang['chip-sales']}</td>
  <td>{$expr(number_format($cardCash,2))} &euro;</td>
 </tr>
 <tr>
  <td style='text-align: left;'>- {$lang['title-expenses']}</td>
  <td>{$expr(number_format($tillExpenses,2))} &euro;</td>
 </tr>
 <tr>
  <td style='text-align: left; border-bottom: 1px dashed #ababab;'>- {$lang['banked-during-day']}</td>
  <td style='border-bottom: 1px dashed #ababab;'>{$expr(number_format($bankedDuringDay,2))} &euro;</td>
 </tr>
 <tr>
  <td style='text-align: left; border-bottom: 1px solid #ababab;'><strong>{$lang['tillbalnow']}</strong></td>
  <td style='border-bottom: 1px solid #ababab;'><strong>{$expr(number_format($tillBalance + $donations + $membershipFees - $tillExpenses - $bankedDuringDay + $cardCash,2))} &euro;</strong></td>
 </tr>
 <tr>
  <td colspan='2'></td>
 </tr>
 <tr>
  <td colspan='2'></td>
 </tr>
 <tr>
  <td colspan='2'></td>
 </tr>
 <tr>
  <td style='text-align: left;'>{$lang['closeday-donations-bank']}</td>
  <td>{$expr(number_format($bankDonations,2))} &euro;</td>
 </tr>
 <tr>
  <td style='text-align: left;'>+ {$lang['closeday-membershipfees-bank']}</td>
  <td>{$expr(number_format($membershipfeesBank,2))} &euro;</td>
 </tr>
 <tr>
  <td style='text-align: left; border-bottom: 1px dashed #ababab;'>+ {$lang['chip-sales']}</td>
  <td style='border-bottom: 1px dashed #ababab;'>{$expr(number_format($cardBank,2))} &euro;</td>
 </tr>
 <tr>
  <td style='text-align: left; border-bottom: 1px solid #ababab;'><strong>{$lang['closeday-totalincome-bank']}</strong></td>
  <td style='border-bottom: 1px solid #ababab;'><strong>{$expr(number_format($bankTotalToday,2))} &euro;</strong></td>
 </tr>
 <tr>
  <td colspan='2'></td>
 </tr>
 <tr>
  <td colspan='2'></td>
 </tr>
 <tr>
  <td colspan='2'></td>
 </tr>
 <tr>
  <td style='text-align: left; border-bottom: 1px solid #000; border-top: 1px solid #000; border-left: 1px solid #000;'><strong>{$lang['closeday-totalincome-bank-and-cash']}</strong></td>
  <td style='border-bottom: 1px solid #000; border-top: 1px solid #000; border-right: 1px solid #000;'><strong>{$expr(number_format($totalToday,2))} &euro;</strong></td>
 </tr>
EOD;




  
	  echo $productOverview;
	  echo $otherProducts;
	  echo $productDetails;
	  echo "</table>";
	  
	echo <<<EOD
	<br /><br />
	 <table class='default' id='mainTable'>
	  <thead>
	   <tr style='cursor: pointer;'>
	    <th style='padding: 10px;'>{$lang['global-time']}</th>
	    <th style='padding: 10px;'>{$lang['global-type']}</th>
  		<th style='padding: 10px;'>{$lang['paid-to']}</th>
  		<th style='padding: 10px;'>{$lang['responsible']}</th>
	    <th style='padding: 10px;'>#</th>
	    <th style='padding: 10px;'>{$lang['global-member']}</th>
	    <th style='padding: 10px;'>{$lang['global-amount']}</th>
	   </tr>
	  </thead>
	  <tbody>
EOD;

		while ($donation = $results->fetch()) {
	
	$id = $donation['id'];
	$donationTime = date("H:i", strtotime($donation['time'] . "+$offsetSec seconds"));
	$user_id = $donation['userid'];
	$amount = $donation['amount'];
	$type = $donation['type'];
	$donatedTo = $donation['donatedTo'];
	$operator = $donation['operator'];
	
	
	if ($donatedTo == '2') {
		$donatedTo = $lang['global-bank'];
	} else if ($donatedTo == '3') {
		$donatedTo = '';
	} else {
		$donatedTo = $lang['global-till'];
	}
	
	if ($type == 1) {
		$movementType = $lang['donation-donation'];
	} else {
		$movementType = $lang['memberfees'];
	}
	
	// Look up user details for showing profile on the Sales page
	$userDetails = "SELECT memberno, first_name, last_name FROM users WHERE user_id = $user_id";
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
	
		$row = $result->fetch();
		$memberno = $row['memberno'];
		$first_name = $row['first_name'];
		$last_name = $row['last_name'];

	// Look up user details for showing profile on the Sales page
	$userDetails = "SELECT memberno, first_name, last_name FROM users WHERE user_id = $operator";
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
	
		$row = $result->fetch();
		$memberno2 = $row['memberno'];
		$first_name2 = $row['first_name'];
		
	$expense_row = sprintf("
  	  <tr>
  	   <td class='clickableRow left' style='padding: 10px;' href='profile.php?user_id={$user_id}'>%s</td>
  	   <td class='clickableRow left' style='padding: 10px;' href='profile.php?user_id={$user_id}'>%s</td>
  	   <td class='clickableRow left' style='padding: 10px;' href='profile.php?user_id={$user_id}'>%s</td>
  	   <td class='clickableRow left' style='padding: 10px;' href='profile.php?user_id={$user_id}'>%s %s</td>
  	   <td class='clickableRow left' style='padding: 10px;' href='profile.php?user_id={$user_id}'>%s</td>
  	   <td class='clickableRow left' style='padding: 10px;' href='profile.php?user_id={$user_id}'>%s %s</td>
  	   <td class='clickableRow right' style='padding: 10px;' href='profile.php?user_id={$user_id}'>%0.02f &euro;</td>
	  </tr>",
	  $donationTime, $movementType, $donatedTo, $memberno2, $first_name2, $memberno, $first_name, $last_name, $amount
	  );
			

	  echo $expense_row;
}
  	  echo "</tbody></table>";
  	  
  	  
  	  
  	  
  	  
  	  
  	  
  	  
  	  
  	  
  	  
  	  
  	  
  	  
  	  
  	  
  	  
  	  
  	  
  	  
  	  
  	  
// Direct dispensing
} else {

	
	
	$deleteDonationScript = <<<EOD
	
	    $(document).ready(function() {
			
			
			$('#mainTable').tablesorter({
				usNumberFormat: true,
				headers: {
					5: {
						sorter: "currency"
					}
				}
			}); 
			
			$('#mainTable2').tablesorter({
				usNumberFormat: true,
				headers: {
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
				}
			}); 
	});

EOD;
	

	pageStart("Status", NULL, $deleteDonationScript, "pstatus", "", "STATUS", $_SESSION['successMessage'], $_SESSION['errorMessage']);
	
	echo <<<EOD
<table style='color: #444; text-align: right; font-family: Tahoma, Verdana, sans-serif; font-size: 14px;'>
 <tr>
  <td colspan='2' style='color: #5aa242; text-align: left; font-size: 17px; border-bottom: 2px solid #a80082;'><strong>{$lang['closeday-finances']}</strong></td>
 </tr>
 <tr>
  <td style='text-align: left;'>{$lang['closeday-tillatopening']}</td>
  <td>{$expr(number_format($tillBalance,2))} &euro;</td>
 </tr>
 <tr>
  <td style='text-align: left;'>+ {$lang['closeday-donations-till']}</td>
  <td>{$expr(number_format($donations,2))} &euro;</td>
 </tr>
 <tr>
  <td style='text-align: left;'>+ {$lang['closeday-membershipfees-till']}</td>
  <td>{$expr(number_format($membershipFees,2))} &euro;</td>
 </tr>
 <tr>
  <td style='text-align: left;'>+ {$lang['direct-dispenses']}</td>
  <td>{$expr(number_format($salesTodayCash,2))} &euro;</td>
 </tr>
 <tr>
  <td style='text-align: left;'>+ {$lang['direct-bar-sales']}</td>
  <td>{$expr(number_format($salesTodayBarCash,2))} &euro;</td>
 </tr>
 <tr>
  <td style='text-align: left;'>- {$lang['title-expenses']}</td>
  <td>{$expr(number_format($tillExpenses,2))} &euro;</td>
 </tr>
 <tr>
  <td style='text-align: left; border-bottom: 1px dashed #ababab;'>- {$lang['banked-during-day']}</td>
  <td style='border-bottom: 1px dashed #ababab;'>{$expr(number_format($bankedDuringDay,2))} &euro;</td>
 </tr>
 <tr>
  <td style='text-align: left; border-bottom: 1px solid #ababab;'><strong>{$lang['tillbalnow']}</strong></td>
  <td style='border-bottom: 1px solid #ababab;'><strong>{$expr(number_format($tillBalance + $donations + $membershipFees + $salesTodayCash + $salesTodayBarCash - $tillExpenses - $bankedDuringDay + $cardCash,2))} &euro;</strong></td>
 </tr>
 <tr>
  <td colspan='2'></td>
 </tr>
 <tr>
  <td colspan='2'></td>
 </tr>
 <tr>
  <td colspan='2'></td>
 </tr>
 <tr>
  <td style='text-align: left;'>{$lang['closeday-donations-bank']}</td>
  <td>{$expr(number_format($bankDonations,2))} &euro;</td>
 </tr>
 <tr>
  <td style='text-align: left;'>+ {$lang['direct-dispenses']}</td>
  <td>{$expr(number_format($salesTodayBank,2))} &euro;</td>
 </tr>
 <tr>
  <td style='text-align: left;'>+ {$lang['direct-bar-sales']}</td>
  <td>{$expr(number_format($salesTodayBarBank,2))} &euro;</td>
 </tr>
 <tr>
  <td style='text-align: left; border-bottom: 1px dashed #ababab;'>+ {$lang['closeday-membershipfees-bank']}</td>
  <td style='border-bottom: 1px dashed #ababab;'>{$expr(number_format($membershipfeesBank,2))} &euro;</td>
 </tr>
 <tr>
  <td style='text-align: left; border-bottom: 1px solid #ababab;'><strong>{$lang['closeday-totalincome-bank']}</strong></td>
  <td style='border-bottom: 1px solid #ababab;'><strong>{$expr(number_format($bankTotalToday,2))} &euro;</strong></td>
 </tr>
 <tr>
  <td colspan='2'></td>
 </tr>
 <tr>
  <td colspan='2'></td>
 </tr>
 <tr>
  <td colspan='2'></td>
 </tr>
 <tr>
  <td style='text-align: left; border-bottom: 1px solid #000; border-top: 1px solid #000; border-left: 1px solid #000;'><strong>{$lang['closeday-totalincome-bank-and-cash']}</strong></td>
  <td style='border-bottom: 1px solid #000; border-top: 1px solid #000; border-right: 1px solid #000;'><strong>{$expr(number_format($totalToday,2))} &euro;</strong></td>
 </tr>
EOD;




  
	  echo $productOverview;
	  echo $otherProducts;
	  echo $productDetails;
	  echo "</table>";
	  
	echo <<<EOD
	<br /><br />
	 <table class='default' id='mainTable'>
	  <thead>
	   <tr style='cursor: pointer;'>
	    <th style='padding: 10px;'>{$lang['global-time']}</th>
	    <th style='padding: 10px;'>{$lang['global-type']}</th>
  		<th style='padding: 10px;'>{$lang['paid-to']}</th>
  		<th style='padding: 10px;'>{$lang['responsible']}</th>
	    <th style='padding: 10px;'>#</th>
	    <th style='padding: 10px;'>{$lang['global-member']}</th>
	    <th style='padding: 10px;'>{$lang['global-amount']}</th>
	   </tr>
	  </thead>
	  <tbody>
EOD;

		while ($donation = $results->fetch()) {
	
	$id = $donation['id'];
	$donationTime = date("H:i", strtotime($donation['time'] . "+$offsetSec seconds"));
	$user_id = $donation['userid'];
	$amount = $donation['amount'];
	$type = $donation['type'];
	$donatedTo = $donation['donatedTo'];
	$operator = $donation['operator'];
	
	if ($donatedTo == '2') {
		$donatedTo = $lang['global-bank'];
	} else if ($donatedTo == '3') {
		$donatedTo = '';
	} else {
		$donatedTo = $lang['global-till'];
	}
	
	if ($type == 1) {
		$movementType = $lang['donation-donation'];
	} else {
		$movementType = $lang['memberfees'];
	}
	
	// Look up user details for showing profile on the Sales page
	$userDetails = "SELECT memberno, first_name, last_name FROM users WHERE user_id = $user_id";
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
	
		$row = $result->fetch();
		$memberno = $row['memberno'];
		$first_name = $row['first_name'];
		$last_name = $row['last_name'];

	// Look up user details for showing profile on the Sales page
	$userDetails = "SELECT memberno, first_name, last_name FROM users WHERE user_id = $operator";
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
	
		$row = $result->fetch();
		$memberno2 = $row['memberno'];
		$first_name2 = $row['first_name'];
		
	$expense_row = sprintf("
  	  <tr>
  	   <td class='left' style='padding: 10px;'>%s</td>
  	   <td class='left' style='padding: 10px;'>%s</td>
  	   <td class='left' style='padding: 10px;'>%s</td>
  	   <td class='left' style='padding: 10px;'>%s %s</td>
  	   <td class='left' style='padding: 10px;'>%s</td>
  	   <td class='left' style='padding: 10px;'>%s %s</td>
  	   <td class='right' style='padding: 10px;'>%0.02f &euro;</td>
	  </tr>",
	  $donationTime, $movementType, $donatedTo, $memberno2, $first_name2, $memberno, $first_name, $last_name, $amount
	  );
			

	  echo $expense_row;
}
  	  echo "</tbody></table>";
  	  	
	
// Look up list of dispenses

	$selectSales = "SELECT saleid, saletime, userid, amount, amountpaid, quantity, units, adminComment, creditBefore, creditAfter FROM sales WHERE DATE(saletime) = DATE(NOW()) AND TIME(saletime) > TIME('05:00:00') ORDER by saletime DESC";
		try
		{
			$resultsX = $pdo3->prepare("$selectSales");
			$resultsX->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		
?>
<br /><br />
	 <table class='default' id='mainTable2'>
	  <thead>
	   <tr style='cursor: pointer;'>
	    <th><?php echo $lang['global-time']; ?></th>
	    <th><?php echo $lang['global-member']; ?></th>
	    <th><?php echo $lang['global-category']; ?></th>
	    <th><?php echo $lang['global-product']; ?></th>
	    <th><?php echo $lang['global-quantity']; ?></th>
	    <th class='right'>&euro;</th>
	    <th>Total g</th>
	    <th>Total u</th>
	    <th>Total &euro;</th>
	    <th></th>
	   </tr>
	  </thead>
	  <tbody>

<?php

		while ($sale = $resultsX->fetch()) {
	
		$formattedDate = date("d-m-Y H:i:s", strtotime($sale['saletime']."+$offsetSec seconds"));
		$saleid = $sale['saleid'];
		$userid = $sale['userid'];
		$quantity = $sale['quantity'];
		$units = $sale['units'];
		
		$amount = $sale['amount'];
		
		$userLookup = "SELECT first_name, memberno FROM users WHERE user_id = {$userid}";
		try
		{
			$result = $pdo3->prepare("$userLookup");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
			$first_name = $row['first_name'];
			$memberno = $row['memberno'];
		
	if ($sale['adminComment'] != '') {
		
		$commentRead = "
		                <img src='images/comments.png' id='comment$saleid' /><div id='helpBox$saleid' class='helpBox'>{$sale['adminComment']}</div>
		                <script>
		                  	$('#comment$saleid').on({
						 		'mouseover' : function() {
								 	$('#helpBox$saleid').css('display', 'block');
						  		},
						  		'mouseout' : function() {
								 	$('#helpBox$saleid').css('display', 'none');
							  	}
						  	});
						</script>
		                ";
		
	} else {
		
		$commentRead = "";
		
	}

			
		$selectoneSale = "SELECT d.category, d.productid, d.quantity, d.amount FROM salesdetails d, sales s WHERE d.saleid = {$saleid} and s.saleid = d.saleid";
		try
		{
			$onesaleResult = $pdo3->prepare("$selectoneSale");
			$onesaleResult->execute();
			$onesaleResult2 = $pdo3->prepare("$selectoneSale");
			$onesaleResult2->execute();
			$onesaleResult3 = $pdo3->prepare("$selectoneSale");
			$onesaleResult3->execute();
			$onesaleResult4 = $pdo3->prepare("$selectoneSale");
			$onesaleResult4->execute();
			$onesaleResult5 = $pdo3->prepare("$selectoneSale");
			$onesaleResult5->execute();
			$onesaleResult6 = $pdo3->prepare("$selectoneSale");
			$onesaleResult6->execute();
			$onesaleResult7 = $pdo3->prepare("$selectoneSale");
			$onesaleResult7->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	

		echo "
  	   <tr><td class='clickableRow' href='profile.php?user_id={$user_id}'>";
  	   
  	   		$o = 0;
  	   		while ($onesale = $onesaleResult6->fetch()) {
	  	   		
	  	   		if ($o == 0) {
					echo "$formattedDate<br/>";
				} else {
					echo "<span class='white'>$formattedDate</span><br/>";
				}
				
				$o++;
			}
			echo "</td>
  	   <td class='clickableRow' href='profile.php?user_id={$user_id}'>";
  	   
  	   		$p = 0;
  	   		while ($onesale = $onesaleResult7->fetch()) {
	  	   		if ($p == 0) {
					echo "#$memberno - $first_name<br/>";
				} else {
					echo "<span class='white'>#$memberno - $first_name</span><br/>";
				}
				
				$p++;
			}
echo "
  	   </td>
  	   <td class='clickableRow' href='profile.php?user_id={$user_id}'>";
		while ($onesale = $onesaleResult->fetch()) {
			if ($onesale['category'] == 1) {
				$category = $lang['global-flower'];
			} else if ($onesale['category'] == 2) {
				$category = $lang['global-extract'];
			} else {
				
				// Query to look for category
				$categoryDetails = "SELECT name FROM categories WHERE id = {$onesale['category']}";
		try
		{
			$result = $pdo3->prepare("$categoryDetails");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
					$category = $row['name'];
			}
				
			echo $category . "<br />";
		}
		echo "</td><td class='clickableRow' href='profile.php?user_id={$user_id}'>";
		while ($onesale = $onesaleResult2->fetch()) {
			
			$productid = $onesale['productid'];
			
	// Determine product type, and assign query variables accordingly
	if ($onesale['category'] == 1) {
		$purchaseCategory = 'Flower';
		$queryVar = ', breed2';
		$prodSelect = 'flower';
		$prodJoin = 'flowerid';
	} else if ($onesale['category'] == 2) {
		$purchaseCategory = 'Extract';
		$queryVar = '';
		$prodSelect = 'extract';
		$prodJoin = 'extractid';
	} else {
		$purchaseCategory = $category;
		$queryVar = '';
		$prodSelect = 'products';
		$prodJoin = "productid";
	}
	
		$selectProduct = "SELECT name{$queryVar} FROM {$prodSelect} WHERE ({$prodJoin} = {$productid})";
		try
		{
			$result = $pdo3->prepare("$selectProduct");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
		
		if ($row['breed2'] != '') {
			$name = $row['name'] . " x " . $row['breed2'];
		} else {
			$name = $row['name'];
		}


			echo $name . "<br />";
		}
		echo "</td><td class='clickableRow right' href='profile.php?user_id={$user_id}'>";
		while ($onesale = $onesaleResult3->fetch()) {
			if ($onesale['category'] > 2) {
				
				// Query to look for category
				$categoryDetailsC = "SELECT name, type FROM categories WHERE id = {$onesale['category']}";
		try
		{
			$result = $pdo3->prepare("$categoryDetailsC");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$rowC = $result->fetch();
					$category = $rowC['name'];
					$type = $rowC['type'];
			}

			if ($onesale['category'] < 3 || $type == 1) {
				echo number_format($onesale['quantity'],2) . " g<br />";
			} else {
				echo number_format($onesale['quantity'],2) . " u<br />";
			}
		}
		echo "</td><td class='clickableRow right' href='profile.php?user_id={$user_id}'>";
		while ($onesale = $onesaleResult4->fetch()) {
			echo number_format($onesale['amount'],2) . " <span class='smallerfont'>&euro;</span><br />";
		}
		echo "</td>";
		
		$quantity = number_format($quantity,2);
		$amount = number_format($amount,2);
		
		if ($_SESSION['creditOrDirect'] == 1) {
		
			if ($credit == NULL && $oldcredit == NULL) {
				
				echo "
				<td class='clickableRow right' href='profile.php?user_id={$user_id}'><strong>{$quantity} g</strong></td>
				<td class='clickableRow right' href='profile.php?user_id={$user_id}'><strong>{$units} u</strong></td>
				<td class='clickableRow right' href='profile.php?user_id={$user_id}'><strong>{$amount} &euro;</strong></td>
				<td class='clickableRow right' href='profile.php?user_id={$user_id}'></td>
				<td class='clickableRow right' href='profile.php?user_id={$user_id}'></td>
				<td class='centered relative'>$commentRead</td>
				<td class='noExl' style='text-align: center;'><a href='javascript:delete_sale({$saleid})'><img src='images/delete.png' height='15' title='{$lang['dispenses-deletesale']}' /></a></td></tr>
				";
				
			} else {
				
				echo "
				<td class='clickableRow right' href='profile.php?user_id={$user_id}'><strong>{$quantity} g</strong></td>
				<td class='clickableRow right' href='profile.php?user_id={$user_id}'><strong>{$units} u</strong></td>
				<td class='clickableRow right' href='profile.php?user_id={$user_id}'><strong>{$amount} &euro;</strong></td>
				<td class='clickableRow right' href='profile.php?user_id={$user_id}'>{$credit} &euro;</td>
				<td class='clickableRow right' href='profile.php?user_id={$user_id}'>{$newcredit} &euro;</td>
				<td class='centered relative'>$commentRead</td>
				<td class='noExl' style='text-align: center;'><a href='javascript:delete_sale({$saleid})'><img src='images/delete.png' height='15' title='{$lang['dispenses-deletesale']}' /></a></td></tr>
				";
			
			}
		
		} else {
			
			if ($credit == NULL && $oldcredit == NULL) {
				
				echo "
				<td class='clickableRow right' href='profile.php?user_id={$user_id}'><strong>{$quantity} g</strong></td>
				<td class='clickableRow right' href='profile.php?user_id={$user_id}'><strong>{$units} u</strong></td>
				<td class='clickableRow right' href='profile.php?user_id={$user_id}'><strong>{$amount} &euro;</strong></td>
				<td class='centered relative'>$commentRead</td>
				<td class='noExl' style='text-align: center;'><a href='javascript:delete_sale({$saleid})'><img src='images/delete.png' height='15' title='{$lang['dispenses-deletesale']}' /></a></td></tr>
				";
				
			} else {
				
				echo "
				<td class='clickableRow right' href='profile.php?user_id={$user_id}'><strong>{$quantity} g</strong></td>
				<td class='clickableRow right' href='profile.php?user_id={$user_id}'><strong>{$units} u</strong></td>
				<td class='clickableRow right' href='profile.php?user_id={$user_id}'><strong>{$amount} &euro;</strong></td>
				<td class='centered relative'>$commentRead</td>
				<td class='noExl' style='text-align: center;'><a href='javascript:delete_sale({$saleid})'><img src='images/delete.png' height='15' title='{$lang['dispenses-deletesale']}' /></a></td></tr>
				";
			
			}
			
		}
	}
  	  echo "</tbody></table>";
	
}

 displayFooter();