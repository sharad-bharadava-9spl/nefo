<?php
	
	require_once 'cOnfig/connection.php';
	require_once 'cOnfig/view.php';
	require_once 'cOnfig/authenticate.php';
	require_once 'cOnfig/languages/common.php';
	
	session_start();
	$accessLevel = '3';
	
	// Authenticate & authorize
	authorizeUser($accessLevel);
		
	pageStart($lang['revenue-history'], NULL, NULL, "pdispensary", "product admin", $lang['revenue-history'] . "<span class='smallerfont2'> [beta]</span>", $_SESSION['successMessage'], $_SESSION['errorMessage']);
	
		// Look up todays donations
		$selectDonations = "SELECT SUM(amount) FROM donations WHERE donatedTo < 3 AND DATE(donationTime) = DATE(NOW())";

		$result = mysql_query($selectDonations)
			or handleError($lang['error-loadsales'],"Error loading sale from db: " . mysql_error());
		
		$row = mysql_fetch_array($result);
			$donationsToday = $row['SUM(amount)'];
			
		// And now membership fees
		$selectFees = "SELECT SUM(amountPaid) FROM memberpayments WHERE DATE(paymentdate) = DATE(NOW())";

		$result = mysql_query($selectFees)
			or handleError($lang['error-loadsales'],"Error loading sale from db: " . mysql_error());
		
		$row = mysql_fetch_array($result);
			$feesToday = $row['SUM(amountPaid)'];
			
		$totalToday = $donationsToday + $feesToday;
			
		
		
		
			
		// Look up daily donations -1
		$selectDonations = "SELECT SUM(amount) FROM donations WHERE donatedTo < 3 AND DATE(donationTime) = DATE_ADD(DATE(NOW()), INTERVAL -1 DAY)";

		$result = mysql_query($selectDonations)
		or handleError($lang['error-loadsales'],"Error loading sale from db: " . mysql_error());
		
		$row = mysql_fetch_array($result);
			$donationsTodayMinus1 = $row['SUM(amount)'];
		
		// And now membership fees
		$selectFees = "SELECT SUM(amountPaid) FROM memberpayments WHERE DATE(paymentdate) = DATE_ADD(DATE(NOW()), INTERVAL -1 DAY)";

		$result = mysql_query($selectFees)
			or handleError($lang['error-loadsales'],"Error loading sale from db: " . mysql_error());
		
		$row = mysql_fetch_array($result);
			$feesTodayMinus1 = $row['SUM(amountPaid)'];
			
		$totalTodayMinus1 = $donationsTodayMinus1 + $feesTodayMinus1;
			
			
		
		
			
		// Look up daily donations -2
		$selectDonations = "SELECT SUM(amount) FROM donations WHERE donatedTo < 3 AND DATE(donationTime) = DATE_ADD(DATE(NOW()), INTERVAL -2 DAY)";

		$result = mysql_query($selectDonations)
		or handleError($lang['error-loadsales'],"Error loading sale from db: " . mysql_error());
		
		$row = mysql_fetch_array($result);
			$donationsTodayMinus2 = $row['SUM(amount)'];
		
		// And now membership fees
		$selectFees = "SELECT SUM(amountPaid) FROM memberpayments WHERE DATE(paymentdate) = DATE_ADD(DATE(NOW()), INTERVAL -2 DAY)";

		$result = mysql_query($selectFees)
			or handleError($lang['error-loadsales'],"Error loading sale from db: " . mysql_error());
		
		$row = mysql_fetch_array($result);
			$feesTodayMinus2 = $row['SUM(amountPaid)'];
			
		$totalTodayMinus2 = $donationsTodayMinus2 + $feesTodayMinus2;
			
			
			
		
		
		// Look up daily donations -3
		$selectDonations = "SELECT SUM(amount) FROM donations WHERE donatedTo < 3 AND DATE(donationTime) = DATE_ADD(DATE(NOW()), INTERVAL -3 DAY)";

		$result = mysql_query($selectDonations)
		or handleError($lang['error-loadsales'],"Error loading sale from db: " . mysql_error());
		
		$row = mysql_fetch_array($result);
			$donationsTodayMinus3 = $row['SUM(amount)'];
		
		// And now membership fees
		$selectFees = "SELECT SUM(amountPaid) FROM memberpayments WHERE DATE(paymentdate) = DATE_ADD(DATE(NOW()), INTERVAL -3 DAY)";

		$result = mysql_query($selectFees)
			or handleError($lang['error-loadsales'],"Error loading sale from db: " . mysql_error());
		
		$row = mysql_fetch_array($result);
			$feesTodayMinus3 = $row['SUM(amountPaid)'];
			
		$totalTodayMinus3 = $donationsTodayMinus3 + $feesTodayMinus3;
			
		
			
			
		
		// Look up daily donations -4
		$selectDonations = "SELECT SUM(amount) FROM donations WHERE donatedTo < 3 AND DATE(donationTime) = DATE_ADD(DATE(NOW()), INTERVAL -4 DAY)";

		$result = mysql_query($selectDonations)
		or handleError($lang['error-loadsales'],"Error loading sale from db: " . mysql_error());
		
		$row = mysql_fetch_array($result);
			$donationsTodayMinus4 = $row['SUM(amount)'];
		
		// And now membership fees
		$selectFees = "SELECT SUM(amountPaid) FROM memberpayments WHERE DATE(paymentdate) = DATE_ADD(DATE(NOW()), INTERVAL -4 DAY)";

		$result = mysql_query($selectFees)
			or handleError($lang['error-loadsales'],"Error loading sale from db: " . mysql_error());
		
		$row = mysql_fetch_array($result);
			$feesTodayMinus4 = $row['SUM(amountPaid)'];
			
		$totalTodayMinus4 = $donationsTodayMinus4 + $feesTodayMinus4;
			
		
			
			
		
		// Look up daily donations -5
		$selectDonations = "SELECT SUM(amount) FROM donations WHERE donatedTo < 3 AND DATE(donationTime) = DATE_ADD(DATE(NOW()), INTERVAL -5 DAY)";

		$result = mysql_query($selectDonations)
		or handleError($lang['error-loadsales'],"Error loading sale from db: " . mysql_error());
		
		$row = mysql_fetch_array($result);
			$donationsTodayMinus5 = $row['SUM(amount)'];
		
		// And now membership fees
		$selectFees = "SELECT SUM(amountPaid) FROM memberpayments WHERE DATE(paymentdate) = DATE_ADD(DATE(NOW()), INTERVAL -5 DAY)";

		$result = mysql_query($selectFees)
			or handleError($lang['error-loadsales'],"Error loading sale from db: " . mysql_error());
		
		$row = mysql_fetch_array($result);
			$feesTodayMinus5 = $row['SUM(amountPaid)'];
			
		$totalTodayMinus5 = $donationsTodayMinus5 + $feesTodayMinus5;
			
		
			
			
		
		// Look up daily donations -6
		$selectDonations = "SELECT SUM(amount) FROM donations WHERE donatedTo < 3 AND DATE(donationTime) = DATE_ADD(DATE(NOW()), INTERVAL -6 DAY)";

		$result = mysql_query($selectDonations)
		or handleError($lang['error-loadsales'],"Error loading sale from db: " . mysql_error());
		
		$row = mysql_fetch_array($result);
			$donationsTodayMinus6 = $row['SUM(amount)'];
		
		// And now membership fees
		$selectFees = "SELECT SUM(amountPaid) FROM memberpayments WHERE DATE(paymentdate) = DATE_ADD(DATE(NOW()), INTERVAL -6 DAY)";

		$result = mysql_query($selectFees)
			or handleError($lang['error-loadsales'],"Error loading sale from db: " . mysql_error());
		
		$row = mysql_fetch_array($result);
			$feesTodayMinus6 = $row['SUM(amountPaid)'];
			
		$totalTodayMinus6 = $donationsTodayMinus6 + $feesTodayMinus6;
			
		
			
			
		
		// Look up daily donations -7
		$selectDonations = "SELECT SUM(amount) FROM donations WHERE donatedTo < 3 AND DATE(donationTime) = DATE_ADD(DATE(NOW()), INTERVAL -7 DAY)";

		$result = mysql_query($selectDonations)
		or handleError($lang['error-loadsales'],"Error loading sale from db: " . mysql_error());
		
		$row = mysql_fetch_array($result);
			$donationsTodayMinus7 = $row['SUM(amount)'];
		
		// And now membership fees
		$selectFees = "SELECT SUM(amountPaid) FROM memberpayments WHERE DATE(paymentdate) = DATE_ADD(DATE(NOW()), INTERVAL -7 DAY)";

		$result = mysql_query($selectFees)
			or handleError($lang['error-loadsales'],"Error loading sale from db: " . mysql_error());
		
		$row = mysql_fetch_array($result);
			$feesTodayMinus7 = $row['SUM(amountPaid)'];
			
		$totalTodayMinus7 = $donationsTodayMinus7 + $feesTodayMinus7;
			
		
			
			
		
			
			
			// AND NOW WEEK BY WEEK //
			
		// Look up this weeks sales
		$selectDonations = "SELECT SUM(amount) FROM donations WHERE donatedTo < 3 AND WEEK(donationTime,1) = WEEK(NOW(),1) AND YEAR(donationTime) = YEAR(NOW()) ";

		$result = mysql_query($selectDonations)
		or handleError($lang['error-loadsales'],"Error loading sale from db: " . mysql_error());
		
		$row = mysql_fetch_array($result);
			$donationsWeek = $row['SUM(amount)'];
		
		// And now membership fees
		$selectFees = "SELECT SUM(amountPaid) FROM memberpayments WHERE WEEK(paymentdate,1) = WEEK(NOW(),1) AND YEAR(paymentdate) = YEAR(NOW()) ";

		$result = mysql_query($selectFees)
			or handleError($lang['error-loadsales'],"Error loading sale from db: " . mysql_error());
		
		$row = mysql_fetch_array($result);
			$feesWeek = $row['SUM(amountPaid)'];
			
		$totalWeek = $donationsWeek + $feesWeek;
			
		
			
			
		
		// Look up weekly donations -1
		$selectDonations = "SELECT SUM(amount) FROM donations WHERE donatedTo < 3 AND WEEK(donationTime,1) = WEEK(DATE_ADD((NOW()), INTERVAL -1 WEEK),1) AND YEAR(donationTime) = YEAR(DATE_ADD((NOW()), INTERVAL -1 WEEK))";

		$result = mysql_query($selectDonations)
		or handleError($lang['error-loadsales'],"Error loading sale from db: " . mysql_error());
		
		$row = mysql_fetch_array($result);
			$donationsWeekMinus1 = $row['SUM(amount)'];
		
		// And now membership fees
		$selectFees = "SELECT SUM(amountPaid) FROM memberpayments WHERE WEEK(paymentdate,1) = WEEK(DATE_ADD((NOW()), INTERVAL -1 WEEK),1) AND YEAR(paymentdate) = YEAR(DATE_ADD((NOW()), INTERVAL -1 WEEK))";

		$result = mysql_query($selectFees)
			or handleError($lang['error-loadsales'],"Error loading sale from db: " . mysql_error());
		
		$row = mysql_fetch_array($result);
			$feesWeekMinus1 = $row['SUM(amountPaid)'];
			
		$totalWeekMinus1 = $donationsWeekMinus1 + $feesWeekMinus1;
			
		
			
			
		
		// Look up weekly donations -2
		$selectDonations = "SELECT SUM(amount) FROM donations WHERE donatedTo < 3 AND WEEK(donationTime,1) = WEEK(DATE_ADD((NOW()), INTERVAL -2 WEEK),1) AND YEAR(donationTime) = YEAR(DATE_ADD((NOW()), INTERVAL -2 WEEK))";

		$result = mysql_query($selectDonations)
		or handleError($lang['error-loadsales'],"Error loading sale from db: " . mysql_error());
		
		$row = mysql_fetch_array($result);
			$donationsWeekMinus2 = $row['SUM(amount)'];
		
		// And now membership fees
		$selectFees = "SELECT SUM(amountPaid) FROM memberpayments WHERE WEEK(paymentdate,1) = WEEK(DATE_ADD((NOW()), INTERVAL -2 WEEK),1) AND YEAR(paymentdate) = YEAR(DATE_ADD((NOW()), INTERVAL -2 WEEK))";

		$result = mysql_query($selectFees)
			or handleError($lang['error-loadsales'],"Error loading sale from db: " . mysql_error());
		
		$row = mysql_fetch_array($result);
			$feesWeekMinus2 = $row['SUM(amountPaid)'];
			
		$totalWeekMinus2 = $donationsWeekMinus2 + $feesWeekMinus2;
			
		
			
		
		// Look up weekly donations -3
		$selectDonations = "SELECT SUM(amount) FROM donations WHERE donatedTo < 3 AND WEEK(donationTime,1) = WEEK(DATE_ADD((NOW()), INTERVAL -3 WEEK),1) AND YEAR(donationTime) = YEAR(DATE_ADD((NOW()), INTERVAL -3 WEEK))";

		$result = mysql_query($selectDonations)
		or handleError($lang['error-loadsales'],"Error loading sale from db: " . mysql_error());
		
		$row = mysql_fetch_array($result);
			$donationsWeekMinus3 = $row['SUM(amount)'];
		
		// And now membership fees
		$selectFees = "SELECT SUM(amountPaid) FROM memberpayments WHERE WEEK(paymentdate,1) = WEEK(DATE_ADD((NOW()), INTERVAL -3 WEEK),1) AND YEAR(paymentdate) = YEAR(DATE_ADD((NOW()), INTERVAL -3 WEEK))";

		$result = mysql_query($selectFees)
			or handleError($lang['error-loadsales'],"Error loading sale from db: " . mysql_error());
		
		$row = mysql_fetch_array($result);
			$feesWeekMinus3 = $row['SUM(amountPaid)'];
			
		$totalWeekMinus3 = $donationsWeekMinus3 + $feesWeekMinus3;
			
		
			
	
		
		// Look up weekly donations -4
		$selectDonations = "SELECT SUM(amount) FROM donations WHERE donatedTo < 3 AND WEEK(donationTime,1) = WEEK(DATE_ADD((NOW()), INTERVAL -4 WEEK),1) AND YEAR(donationTime) = YEAR(DATE_ADD((NOW()), INTERVAL -4 WEEK))";

		$result = mysql_query($selectDonations)
		or handleError($lang['error-loadsales'],"Error loading sale from db: " . mysql_error());
		
		$row = mysql_fetch_array($result);
			$donationsWeekMinus4 = $row['SUM(amount)'];
		
		// And now membership fees
		$selectFees = "SELECT SUM(amountPaid) FROM memberpayments WHERE WEEK(paymentdate,1) = WEEK(DATE_ADD((NOW()), INTERVAL -4 WEEK),1) AND YEAR(paymentdate) = YEAR(DATE_ADD((NOW()), INTERVAL -4 WEEK))";

		$result = mysql_query($selectFees)
			or handleError($lang['error-loadsales'],"Error loading sale from db: " . mysql_error());
		
		$row = mysql_fetch_array($result);
			$feesWeekMinus4 = $row['SUM(amountPaid)'];
			
		$totalWeekMinus4 = $donationsWeekMinus4 + $feesWeekMinus4;
			
		
			
			
		
		// Look up weekly donations -5
		$selectDonations = "SELECT SUM(amount) FROM donations WHERE donatedTo < 3 AND WEEK(donationTime,1) = WEEK(DATE_ADD((NOW()), INTERVAL -5 WEEK),1) AND YEAR(donationTime) = YEAR(DATE_ADD((NOW()), INTERVAL -5 WEEK))";

		$result = mysql_query($selectDonations)
		or handleError($lang['error-loadsales'],"Error loading sale from db: " . mysql_error());
		
		$row = mysql_fetch_array($result);
			$donationsWeekMinus5 = $row['SUM(amount)'];
		
		// And now membership fees
		$selectFees = "SELECT SUM(amountPaid) FROM memberpayments WHERE WEEK(paymentdate,1) = WEEK(DATE_ADD((NOW()), INTERVAL -5 WEEK),1) AND YEAR(paymentdate) = YEAR(DATE_ADD((NOW()), INTERVAL -5 WEEK))";

		$result = mysql_query($selectFees)
			or handleError($lang['error-loadsales'],"Error loading sale from db: " . mysql_error());
		
		$row = mysql_fetch_array($result);
			$feesWeekMinus5 = $row['SUM(amountPaid)'];
			
		$totalWeekMinus5 = $donationsWeekMinus5 + $feesWeekMinus5;
			
		
			
			
		
		// Look up weekly donations -6
		$selectDonations = "SELECT SUM(amount) FROM donations WHERE donatedTo < 3 AND WEEK(donationTime,1) = WEEK(DATE_ADD((NOW()), INTERVAL -6 WEEK),1) AND YEAR(donationTime) = YEAR(DATE_ADD((NOW()), INTERVAL -6 WEEK))";

		$result = mysql_query($selectDonations)
		or handleError($lang['error-loadsales'],"Error loading sale from db: " . mysql_error());
		
		$row = mysql_fetch_array($result);
			$donationsWeekMinus6 = $row['SUM(amount)'];
		
		// And now membership fees
		$selectFees = "SELECT SUM(amountPaid) FROM memberpayments WHERE WEEK(paymentdate,1) = WEEK(DATE_ADD((NOW()), INTERVAL -6 WEEK),1) AND YEAR(paymentdate) = YEAR(DATE_ADD((NOW()), INTERVAL -6 WEEK))";

		$result = mysql_query($selectFees)
			or handleError($lang['error-loadsales'],"Error loading sale from db: " . mysql_error());
		
		$row = mysql_fetch_array($result);
			$feesWeekMinus6 = $row['SUM(amountPaid)'];
			
		$totalWeekMinus6 = $donationsWeekMinus6 + $feesWeekMinus6;
			
		
			
			
		
		// Look up weekly donations -7
		$selectDonations = "SELECT SUM(amount) FROM donations WHERE donatedTo < 3 AND WEEK(donationTime,1) = WEEK(DATE_ADD((NOW()), INTERVAL -7 WEEK),1) AND YEAR(donationTime) = YEAR(DATE_ADD((NOW()), INTERVAL -7 WEEK))";

		$result = mysql_query($selectDonations)
		or handleError($lang['error-loadsales'],"Error loading sale from db: " . mysql_error());
		
		$row = mysql_fetch_array($result);
			$donationsWeekMinus7 = $row['SUM(amount)'];
		
		// And now membership fees
		$selectFees = "SELECT SUM(amountPaid) FROM memberpayments WHERE WEEK(paymentdate,1) = WEEK(DATE_ADD((NOW()), INTERVAL -7 WEEK),1) AND YEAR(paymentdate) = YEAR(DATE_ADD((NOW()), INTERVAL -7 WEEK))";

		$result = mysql_query($selectFees)
			or handleError($lang['error-loadsales'],"Error loading sale from db: " . mysql_error());
		
		$row = mysql_fetch_array($result);
			$feesWeekMinus7 = $row['SUM(amountPaid)'];
			
		$totalWeekMinus7 = $donationsWeekMinus7 + $feesWeekMinus7;
			
		
			
			
		
			
			
			// AND NOW MONTH BY MONTH //
			
		// Look up this months sales
		$selectDonations = "SELECT SUM(amount) FROM donations WHERE donatedTo < 3 AND MONTH(donationTime) = MONTH(NOW()) AND YEAR(donationTime) = YEAR(NOW()) ";

		$result = mysql_query($selectDonations)
		or handleError($lang['error-loadsales'],"Error loading sale from db: " . mysql_error());
		
		$row = mysql_fetch_array($result);
			$donationsMonth = $row['SUM(amount)'];
		
		// And now membership fees
		$selectFees = "SELECT SUM(amountPaid) FROM memberpayments WHERE MONTH(paymentdate) = MONTH(NOW()) AND YEAR(paymentdate) = YEAR(NOW()) ";

		$result = mysql_query($selectFees)
			or handleError($lang['error-loadsales'],"Error loading sale from db: " . mysql_error());
		
		$row = mysql_fetch_array($result);
			$feesMonth = $row['SUM(amountPaid)'];
			
		$totalMonth = $donationsMonth + $feesMonth;
			
		
			
			
		
		// Look up monthly donations -1
		$selectDonations = "SELECT SUM(amount) FROM donations WHERE donatedTo < 3 AND MONTH(donationTime) = MONTH(DATE_ADD((NOW()), INTERVAL -1 MONTH)) AND YEAR(donationTime) = YEAR(DATE_ADD((NOW()), INTERVAL -1 MONTH))";

		$result = mysql_query($selectDonations)
		or handleError($lang['error-loadsales'],"Error loading sale from db: " . mysql_error());
		
		$row = mysql_fetch_array($result);
			$donationsMonthMinus1 = $row['SUM(amount)'];
		
		// And now membership fees
		$selectFees = "SELECT SUM(amountPaid) FROM memberpayments WHERE MONTH(paymentdate) = MONTH(DATE_ADD((NOW()), INTERVAL -1 MONTH)) AND YEAR(paymentdate) = YEAR(DATE_ADD((NOW()), INTERVAL -1 MONTH))";

		$result = mysql_query($selectFees)
			or handleError($lang['error-loadsales'],"Error loading sale from db: " . mysql_error());
		
		$row = mysql_fetch_array($result);
			$feesMonthMinus1 = $row['SUM(amountPaid)'];
			
		$totalMonthMinus1 = $donationsMonthMinus1 + $feesMonthMinus1;
			
		
			
			
		
		// Look up monthly donations -2
		$selectDonations = "SELECT SUM(amount) FROM donations WHERE donatedTo < 3 AND MONTH(donationTime) = MONTH(DATE_ADD((NOW()), INTERVAL -2 MONTH)) AND YEAR(donationTime) = YEAR(DATE_ADD((NOW()), INTERVAL -2 MONTH))";

		$result = mysql_query($selectDonations)
		or handleError($lang['error-loadsales'],"Error loading sale from db: " . mysql_error());
		
		$row = mysql_fetch_array($result);
			$donationsMonthMinus2 = $row['SUM(amount)'];
		
		// And now membership fees
		$selectFees = "SELECT SUM(amountPaid) FROM memberpayments WHERE MONTH(paymentdate) = MONTH(DATE_ADD((NOW()), INTERVAL -2 MONTH)) AND YEAR(paymentdate) = YEAR(DATE_ADD((NOW()), INTERVAL -2 MONTH))";

		$result = mysql_query($selectFees)
			or handleError($lang['error-loadsales'],"Error loading sale from db: " . mysql_error());
		
		$row = mysql_fetch_array($result);
			$feesMonthMinus2 = $row['SUM(amountPaid)'];
			
		$totalMonthMinus2 = $donationsMonthMinus2 + $feesMonthMinus2;
			
		
			
			
		
		// Look up monthly donations -3
		$selectDonations = "SELECT SUM(amount) FROM donations WHERE donatedTo < 3 AND MONTH(donationTime) = MONTH(DATE_ADD((NOW()), INTERVAL -3 MONTH)) AND YEAR(donationTime) = YEAR(DATE_ADD((NOW()), INTERVAL -3 MONTH))";

		$result = mysql_query($selectDonations)
		or handleError($lang['error-loadsales'],"Error loading sale from db: " . mysql_error());
		
		$row = mysql_fetch_array($result);
			$donationsMonthMinus3 = $row['SUM(amount)'];
		
		// And now membership fees
		$selectFees = "SELECT SUM(amountPaid) FROM memberpayments WHERE MONTH(paymentdate) = MONTH(DATE_ADD((NOW()), INTERVAL -3 MONTH)) AND YEAR(paymentdate) = YEAR(DATE_ADD((NOW()), INTERVAL -3 MONTH))";

		$result = mysql_query($selectFees)
			or handleError($lang['error-loadsales'],"Error loading sale from db: " . mysql_error());
		
		$row = mysql_fetch_array($result);
			$feesMonthMinus3 = $row['SUM(amountPaid)'];
			
		$totalMonthMinus3 = $donationsMonthMinus3 + $feesMonthMinus3;
			
		
			
			
		
		// Look up monthly donations -4
		$selectDonations = "SELECT SUM(amount) FROM donations WHERE donatedTo < 3 AND MONTH(donationTime) = MONTH(DATE_ADD((NOW()), INTERVAL -4 MONTH)) AND YEAR(donationTime) = YEAR(DATE_ADD((NOW()), INTERVAL -4 MONTH))";

		$result = mysql_query($selectDonations)
		or handleError($lang['error-loadsales'],"Error loading sale from db: " . mysql_error());
		
		$row = mysql_fetch_array($result);
			$donationsMonthMinus4 = $row['SUM(amount)'];
		
		// And now membership fees
		$selectFees = "SELECT SUM(amountPaid) FROM memberpayments WHERE MONTH(paymentdate) = MONTH(DATE_ADD((NOW()), INTERVAL -4 MONTH)) AND YEAR(paymentdate) = YEAR(DATE_ADD((NOW()), INTERVAL -4 MONTH))";

		$result = mysql_query($selectFees)
			or handleError($lang['error-loadsales'],"Error loading sale from db: " . mysql_error());
		
		$row = mysql_fetch_array($result);
			$feesMonthMinus4 = $row['SUM(amountPaid)'];
			
		$totalMonthMinus4 = $donationsMonthMinus4 + $feesMonthMinus4;
			
		
			
			
		
		// Look up monthly donations -5
		$selectDonations = "SELECT SUM(amount) FROM donations WHERE donatedTo < 3 AND MONTH(donationTime) = MONTH(DATE_ADD((NOW()), INTERVAL -5 MONTH)) AND YEAR(donationTime) = YEAR(DATE_ADD((NOW()), INTERVAL -5 MONTH))";

		$result = mysql_query($selectDonations)
		or handleError($lang['error-loadsales'],"Error loading sale from db: " . mysql_error());
		
		$row = mysql_fetch_array($result);
			$donationsMonthMinus5 = $row['SUM(amount)'];
		
		// And now membership fees
		$selectFees = "SELECT SUM(amountPaid) FROM memberpayments WHERE MONTH(paymentdate) = MONTH(DATE_ADD((NOW()), INTERVAL -5 MONTH)) AND YEAR(paymentdate) = YEAR(DATE_ADD((NOW()), INTERVAL -5 MONTH))";

		$result = mysql_query($selectFees)
			or handleError($lang['error-loadsales'],"Error loading sale from db: " . mysql_error());
		
		$row = mysql_fetch_array($result);
			$feesMonthMinus5 = $row['SUM(amountPaid)'];
			
		$totalMonthMinus5 = $donationsMonthMinus5 + $feesMonthMinus5;
			
		
			
			
		
		// Look up monthly donations -6
		$selectDonations = "SELECT SUM(amount) FROM donations WHERE donatedTo < 3 AND MONTH(donationTime) = MONTH(DATE_ADD((NOW()), INTERVAL -6 MONTH)) AND YEAR(donationTime) = YEAR(DATE_ADD((NOW()), INTERVAL -6 MONTH))";

		$result = mysql_query($selectDonations)
		or handleError($lang['error-loadsales'],"Error loading sale from db: " . mysql_error());
		
		$row = mysql_fetch_array($result);
			$donationsMonthMinus6 = $row['SUM(amount)'];
		
		// And now membership fees
		$selectFees = "SELECT SUM(amountPaid) FROM memberpayments WHERE MONTH(paymentdate) = MONTH(DATE_ADD((NOW()), INTERVAL -6 MONTH)) AND YEAR(paymentdate) = YEAR(DATE_ADD((NOW()), INTERVAL -6 MONTH))";

		$result = mysql_query($selectFees)
			or handleError($lang['error-loadsales'],"Error loading sale from db: " . mysql_error());
		
		$row = mysql_fetch_array($result);
			$feesMonthMinus6 = $row['SUM(amountPaid)'];
			
		$totalMonthMinus6 = $donationsMonthMinus6 + $feesMonthMinus6;
			
		
			
			
		
		// Look up monthly donations -7
		$selectDonations = "SELECT SUM(amount) FROM donations WHERE donatedTo < 3 AND MONTH(donationTime) = MONTH(DATE_ADD((NOW()), INTERVAL -7 MONTH)) AND YEAR(donationTime) = YEAR(DATE_ADD((NOW()), INTERVAL -7 MONTH))";

		$result = mysql_query($selectDonations)
		or handleError($lang['error-loadsales'],"Error loading sale from db: " . mysql_error());
		
		$row = mysql_fetch_array($result);
			$donationsMonthMinus7 = $row['SUM(amount)'];
		
		// And now membership fees
		$selectFees = "SELECT SUM(amountPaid) FROM memberpayments WHERE MONTH(paymentdate) = MONTH(DATE_ADD((NOW()), INTERVAL -7 MONTH)) AND YEAR(paymentdate) = YEAR(DATE_ADD((NOW()), INTERVAL -7 MONTH))";

		$result = mysql_query($selectFees)
			or handleError($lang['error-loadsales'],"Error loading sale from db: " . mysql_error());
		
		$row = mysql_fetch_array($result);
			$feesMonthMinus7 = $row['SUM(amountPaid)'];
			
		$totalMonthMinus7 = $donationsMonthMinus7 + $feesMonthMinus7;
			
		


?>
<br />
<table class="dayByDay displaybox">
 <tr>
  <td colspan="4"><h3><?php echo $lang['dispensary-daytoday']; ?></h3></td>
 </tr>
 <tr>
  <td></td>
  <td class='centered'><u><?php echo $lang['title-donations']; ?></u></td>
  <td class='centered'><u><?php echo $lang['fees']; ?></u></td>
  <td class='centered'><u><?php echo $lang['global-total']; ?></u></td>
 </tr>
 <tr>
  <td class="first"><?php echo $lang['dispensary-today']; ?>:</td>
  <td><?php echo number_format($donationsToday,0); ?> <span class="smallerfont">&euro;</span></td>
  <td><?php echo number_format($feesToday,0); ?> <span class="smallerfont">&euro;</span></td>
  <td><?php echo number_format($totalToday,0); ?> <span class="smallerfont">&euro;</span></td>
 </tr>
 <tr>
  <td class="first"><?php echo $lang['dispensary-yesterday']; ?>:</td>
  <td><?php echo number_format($donationsTodayMinus1,0); ?> <span class="smallerfont">&euro;</span></td>
  <td><?php echo number_format($feesTodayMinus1,0); ?> <span class="smallerfont">&euro;</span></td>
  <td><?php echo number_format($totalTodayMinus1,0); ?> <span class="smallerfont">&euro;</span></td>
 </tr>
 <tr>
  <td class="first"><?php echo date("l", strtotime("-2 days")); ?>:</td>
  <td><?php echo number_format($donationsTodayMinus2,0); ?> <span class="smallerfont">&euro;</span></td>
  <td><?php echo number_format($feesTodayMinus2,0); ?> <span class="smallerfont">&euro;</span></td>
  <td><?php echo number_format($totalTodayMinus2,0); ?> <span class="smallerfont">&euro;</span></td>
 </tr>
 <tr>
  <td class="first"><?php echo date("l", strtotime("-3 days")); ?>:</td>
  <td><?php echo number_format($donationsTodayMinus3,0); ?> <span class="smallerfont">&euro;</span></td>
  <td><?php echo number_format($feesTodayMinus3,0); ?> <span class="smallerfont">&euro;</span></td>
  <td><?php echo number_format($totalTodayMinus3,0); ?> <span class="smallerfont">&euro;</span></td>
 </tr>
 <tr>
  <td class="first"><?php echo date("l", strtotime("-4 days")); ?>:</td>
  <td><?php echo number_format($donationsTodayMinus4,0); ?> <span class="smallerfont">&euro;</span></td>
  <td><?php echo number_format($feesTodayMinus4,0); ?> <span class="smallerfont">&euro;</span></td>
  <td><?php echo number_format($totalTodayMinus4,0); ?> <span class="smallerfont">&euro;</span></td>
 </tr>
 <tr>
  <td class="first"><?php echo date("l", strtotime("-5 days")); ?>:</td>
  <td><?php echo number_format($donationsTodayMinus5,0); ?> <span class="smallerfont">&euro;</span></td>
  <td><?php echo number_format($feesTodayMinus5,0); ?> <span class="smallerfont">&euro;</span></td>
  <td><?php echo number_format($totalTodayMinus5,0); ?> <span class="smallerfont">&euro;</span></td>
 </tr>
 <tr>
  <td class="first"><?php echo date("l", strtotime("-6 days")); ?>:</td>
  <td><?php echo number_format($donationsTodayMinus6,0); ?> <span class="smallerfont">&euro;</span></td>
  <td><?php echo number_format($feesTodayMinus6,0); ?> <span class="smallerfont">&euro;</span></td>
  <td><?php echo number_format($totalTodayMinus6,0); ?> <span class="smallerfont">&euro;</span></td>
 </tr>
 <tr>
  <td class="first"><?php echo date("l", strtotime("-7 days")); ?>:</td>
  <td><?php echo number_format($donationsTodayMinus7,0); ?> <span class="smallerfont">&euro;</span></td>
  <td><?php echo number_format($feesTodayMinus7,0); ?> <span class="smallerfont">&euro;</span></td>
  <td><?php echo number_format($totalTodayMinus7,0); ?> <span class="smallerfont">&euro;</span></td>
 </tr>
</table>
<table class="dayByDay displaybox adminHidden">
 <tr>
  <td colspan="5"><h3><?php echo $lang['dispensary-weektoweek']; ?></h3></td>
 </tr>
 <tr>
  <td></td>
  <td class='centered'><u><?php echo $lang['title-donations']; ?></u></td>
  <td class='centered'><u><?php echo $lang['fees']; ?></u></td>
  <td class='centered'><u><?php echo $lang['global-total']; ?></u></td>
  <td></td>
 </tr>
 <tr>
  <td class="first"><?php echo $lang['dispensary-thisweek']; ?>:</td>
  <td><?php echo number_format($donationsWeek,0); ?> <span class="smallerfont">&euro;</span></td>
  <td><?php echo number_format($feesWeek,0); ?> <span class="smallerfont">&euro;</span></td>
  <td><?php echo number_format($totalWeek,0); ?> <span class="smallerfont">&euro;</span></td>
  <td class="evolution"><?php
  $evolution = (($totalWeek - $totalWeekMinus1) /  $totalWeekMinus1) * 100;
  if ($totalWeek > $totalWeekMinus1) {
	  // Improvement
	  echo number_format($evolution,0) . "% <img src='images/positive2.png' />";
  } else if ($totalWeek < $totalWeekMinus1) {
	  // Decline
	  echo number_format($evolution,0) . "% <img src='images/negative2.png' />";
  }
?>
  </td>
 </tr>
 <tr>
  <td class="first"><?php echo $lang['dispensary-lastweek']; ?>:</td>
  <td><?php echo number_format($donationsWeekMinus1,0); ?> <span class="smallerfont">&euro;</span></td>
  <td><?php echo number_format($feesWeekMinus1,0); ?> <span class="smallerfont">&euro;</span></td>
  <td><?php echo number_format($totalWeekMinus1,0); ?> <span class="smallerfont">&euro;</span></td>
  <td class="evolution"><?php
  $evolution = (($totalWeekMinus1 - $totalWeekMinus2) /  $totalWeekMinus2) * 100;
  if ($totalWeekMinus1 > $totalWeekMinus2) {
	  // Improvement
	  echo number_format($evolution,0) . "% <img src='images/positive2.png' />";
  } else if ($totalWeekMinus1 < $totalWeekMinus2) {
	  // Decline
	  echo number_format($evolution,0) . "% <img src='images/negative2.png' />";
  }
?>
  </td>
 </tr>
 <tr>
  <td class="first"><?php echo $lang['dispensary-twoweeksago']; ?>:</td>
  <td><?php echo number_format($donationsWeekMinus2,0); ?> <span class="smallerfont">&euro;</span></td>
  <td><?php echo number_format($feesWeekMinus2,0); ?> <span class="smallerfont">&euro;</span></td>
  <td><?php echo number_format($totalWeekMinus2,0); ?> <span class="smallerfont">&euro;</span></td>
  <td class="evolution"><?php
  $evolution = (($totalWeekMinus2 - $totalWeekMinus3) /  $totalWeekMinus3) * 100;
  if ($totalWeekMinus2 > $totalWeekMinus3) {
	  // Improvement
	  echo number_format($evolution,0) . "% <img src='images/positive2.png' />";
  } else if ($totalWeekMinus2 < $totalWeekMinus3) {
	  // Decline
	  echo number_format($evolution,0) . "% <img src='images/negative2.png' />";
  }
?>
  </td>
 </tr>
 <tr>
  <td class="first"><?php echo $lang['dispensary-threeweeksago']; ?>:</td>
  <td><?php echo number_format($donationsWeekMinus3,0); ?> <span class="smallerfont">&euro;</span></td>
  <td><?php echo number_format($feesWeekMinus3,0); ?> <span class="smallerfont">&euro;</span></td>
  <td><?php echo number_format($totalWeekMinus3,0); ?> <span class="smallerfont">&euro;</span></td>
  <td class="evolution"><?php
  $evolution = (($totalWeekMinus3 - $totalWeekMinus4) /  $totalWeekMinus4) * 100;
  if ($totalWeekMinus3 > $totalWeekMinus4) {
	  // Improvement
	  echo number_format($evolution,0) . "% <img src='images/positive2.png' />";
  } else if ($totalWeekMinus3 < $totalWeekMinus4) {
	  // Decline
	  echo number_format($evolution,0) . "% <img src='images/negative2.png' />";
  }
?>
  </td>
 </tr>
 <tr>
  <td class="first"><?php echo $lang['dispensary-fourweeksago']; ?>:</td>
  <td><?php echo number_format($donationsWeekMinus4,0); ?> <span class="smallerfont">&euro;</span></td>
  <td><?php echo number_format($feesWeekMinus4,0); ?> <span class="smallerfont">&euro;</span></td>
  <td><?php echo number_format($totalWeekMinus4,0); ?> <span class="smallerfont">&euro;</span></td>
  <td class="evolution"><?php
  $evolution = (($totalWeekMinus4 - $totalWeekMinus5) /  $totalWeekMinus5) * 100;
  if ($totalWeekMinus4 > $totalWeekMinus5) {
	  // Improvement
	  echo number_format($evolution,0) . "% <img src='images/positive2.png' />";
  } else if ($totalWeekMinus4 < $totalWeekMinus5) {
	  // Decline
	  echo number_format($evolution,0) . "% <img src='images/negative2.png' />";
  }
?>
  </td>
 </tr>
 <tr>
  <td class="first"><?php echo $lang['dispensary-fiveweeksago']; ?>:</td>
  <td><?php echo number_format($donationsWeekMinus5,0); ?> <span class="smallerfont">&euro;</span></td>
  <td><?php echo number_format($feesWeekMinus5,0); ?> <span class="smallerfont">&euro;</span></td>
  <td><?php echo number_format($totalWeekMinus5,0); ?> <span class="smallerfont">&euro;</span></td>
  <td class="evolution"><?php
  $evolution = (($totalWeekMinus5 - $totalWeekMinus6) /  $totalWeekMinus6) * 100;
  if ($totalWeekMinus5 > $totalWeekMinus6) {
	  // Improvement
	  echo number_format($evolution,0) . "% <img src='images/positive2.png' />";
  } else if ($totalWeekMinus5 < $totalWeekMinus6) {
	  // Decline
	  echo number_format($evolution,0) . "% <img src='images/negative2.png' />";
  }
?>
  </td>
 </tr>
 <tr>
  <td class="first"><?php echo $lang['dispensary-sixweeksago']; ?>:</td>
  <td><?php echo number_format($donationsWeekMinus6,0); ?> <span class="smallerfont">&euro;</span></td>
  <td><?php echo number_format($feesWeekMinus6,0); ?> <span class="smallerfont">&euro;</span></td>
  <td><?php echo number_format($totalWeekMinus6,0); ?> <span class="smallerfont">&euro;</span></td>
  <td class="evolution"><?php
  $evolution = (($totalWeekMinus6 - $totalWeekMinus7) /  $totalWeekMinus7) * 100;
  if ($totalWeekMinus6 > $totalWeekMinus7) {
	  // Improvement
	  echo number_format($evolution,0) . "% <img src='images/positive2.png' />";
  } else if ($totalWeekMinus6 < $totalWeekMinus7) {
	  // Decline
	  echo number_format($evolution,0) . "% <img src='images/negative2.png' />";
  }
?>
  </td>
 </tr>
 <tr>
  <td class="first"><?php echo $lang['dispensary-sevenweeksago']; ?>:</td>
  <td><?php echo number_format($donationsWeekMinus7,0); ?> <span class="smallerfont">&euro;</span></td>
  <td><?php echo number_format($feesWeekMinus7,0); ?> <span class="smallerfont">&euro;</span></td>
  <td><?php echo number_format($totalWeekMinus7,0); ?> <span class="smallerfont">&euro;</span></td>
 </tr>
</table>

<table class="dayByDay displaybox adminHidden">
 <tr>
  <td colspan="5"><h3><?php echo $lang['dispensary-monthtomonth']; ?></h3></td>
 </tr>
 <tr>
  <td></td>
  <td class='centered'><u><?php echo $lang['title-donations']; ?></u></td>
  <td class='centered'><u><?php echo $lang['fees']; ?></u></td>
  <td class='centered'><u><?php echo $lang['global-total']; ?></u></td>
  <td></td>
 </tr>
 <tr>
  <td class="first"><?php echo $lang['dispensary-thismonth']; ?>:</td>
  <td><?php echo number_format($donationsMonth,0); ?> <span class="smallerfont">&euro;</span></td>
  <td><?php echo number_format($feesMonth,0); ?> <span class="smallerfont">&euro;</span></td>
  <td><?php echo number_format($totalMonth,0); ?> <span class="smallerfont">&euro;</span></td>
  <td class="evolution"><?php
  $evolution = (($totalMonth - $totalMonthMinus1) /  $totalMonthMinus1) * 100;
  if ($totalMonth > $totalMonthMinus1) {
	  // Improvement
	  echo number_format($evolution,0) . "% <img src='images/positive2.png' />";
  } else if ($totalMonth < $totalMonthMinus1) {
	  // Decline
	  echo number_format($evolution,0) . "% <img src='images/negative2.png' />";
  }
?>
  </td>
 </tr>
 <tr>
  <td class="first"><?php echo date("F", strtotime("first day of last month")); ?>:</td>
  <td><?php echo number_format($donationsMonthMinus1,0); ?> <span class="smallerfont">&euro;</span></td>
  <td><?php echo number_format($feesMonthMinus1,0); ?> <span class="smallerfont">&euro;</span></td>
  <td><?php echo number_format($totalMonthMinus1,0); ?> <span class="smallerfont">&euro;</span></td>
  <td class="evolution"><?php
  $evolution = (($totalMonthMinus1 - $totalMonthMinus2) /  $totalMonthMinus2) * 100;
  if ($totalMonthMinus1 > $totalMonthMinus2) {
	  // Improvement
	  echo number_format($evolution,0) . "% <img src='images/positive2.png' />";
  } else if ($totalMonthMinus1 < $totalMonthMinus2) {
	  // Decline
	  echo number_format($evolution,0) . "% <img src='images/negative2.png' />";
  }
?>
  </td>
 </tr>
 <tr>
  <td class="first"><?php echo date("F", strtotime("-1 months", strtotime("first day of last month") )); ?>:</td>
  <td><?php echo number_format($donationsMonthMinus2,0); ?> <span class="smallerfont">&euro;</span></td>
  <td><?php echo number_format($feesMonthMinus2,0); ?> <span class="smallerfont">&euro;</span></td>
  <td><?php echo number_format($totalMonthMinus2,0); ?> <span class="smallerfont">&euro;</span></td>
  <td class="evolution"><?php
  $evolution = (($totalMonthMinus2 - $totalMonthMinus3) /  $totalMonthMinus3) * 100;
  if ($totalMonthMinus2 > $totalMonthMinus3) {
	  // Improvement
	  echo number_format($evolution,0) . "% <img src='images/positive2.png' />";
  } else if ($totalMonthMinus2 < $totalMonthMinus3) {
	  // Decline
	  echo number_format($evolution,0) . "% <img src='images/negative2.png' />";
  }
?>
  </td>
 </tr>
 <tr>
  <td class="first"><?php echo date("F", strtotime("-2 months", strtotime("first day of last month") )); ?>:</td>
  <td><?php echo number_format($donationsMonthMinus3,0); ?> <span class="smallerfont">&euro;</span></td>
  <td><?php echo number_format($feesMonthMinus3,0); ?> <span class="smallerfont">&euro;</span></td>
  <td><?php echo number_format($totalMonthMinus3,0); ?> <span class="smallerfont">&euro;</span></td>
  <td class="evolution"><?php
  $evolution = (($totalMonthMinus3 - $totalMonthMinus4) /  $totalMonthMinus4) * 100;
  if ($totalMonthMinus3 > $totalMonthMinus4) {
	  // Improvement
	  echo number_format($evolution,0) . "% <img src='images/positive2.png' />";
  } else if ($totalMonthMinus3 < $totalMonthMinus4) {
	  // Decline
	  echo number_format($evolution,0) . "% <img src='images/negative2.png' />";
  }
?>
  </td>
 </tr>
 <tr>
  <td class="first"><?php echo date("F", strtotime("-3 months", strtotime("first day of last month") )); ?>:</td>
  <td><?php echo number_format($donationsMonthMinus4,0); ?> <span class="smallerfont">&euro;</span></td>
  <td><?php echo number_format($feesMonthMinus4,0); ?> <span class="smallerfont">&euro;</span></td>
  <td><?php echo number_format($totalMonthMinus4,0); ?> <span class="smallerfont">&euro;</span></td>
  <td class="evolution"><?php
  $evolution = (($totalMonthMinus4 - $totalMonthMinus5) /  $totalMonthMinus5) * 100;
  if ($totalMonthMinus4 > $totalMonthMinus5) {
	  // Improvement
	  echo number_format($evolution,0) . "% <img src='images/positive2.png' />";
  } else if ($totalMonthMinus4 < $totalMonthMinus5) {
	  // Decline
	  echo number_format($evolution,0) . "% <img src='images/negative2.png' />";
  }
?>
  </td>
 </tr>
 <tr>
  <td class="first"><?php echo date("F", strtotime("-4 months", strtotime("first day of last month") )); ?>:</td>
  <td><?php echo number_format($donationsMonthMinus5,0); ?> <span class="smallerfont">&euro;</span></td>
  <td><?php echo number_format($feesMonthMinus5,0); ?> <span class="smallerfont">&euro;</span></td>
  <td><?php echo number_format($totalMonthMinus5,0); ?> <span class="smallerfont">&euro;</span></td>
  <td class="evolution"><?php
  $evolution = (($totalMonthMinus5 - $totalMonthMinus6) /  $totalMonthMinus6) * 100;
  if ($totalMonthMinus5 > $totalMonthMinus6) {
	  // Improvement
	  echo number_format($evolution,0) . "% <img src='images/positive2.png' />";
  } else if ($totalMonthMinus5 < $totalMonthMinus6) {
	  // Decline
	  echo number_format($evolution,0) . "% <img src='images/negative2.png' />";
  }
?>
  </td>
 </tr>
 <tr>
  <td class="first"><?php echo date("F", strtotime("-5 months", strtotime("first day of last month") )); ?>:</td>
  <td><?php echo number_format($donationsMonthMinus6,0); ?> <span class="smallerfont">&euro;</span></td>
  <td><?php echo number_format($feesMonthMinus6,0); ?> <span class="smallerfont">&euro;</span></td>
  <td><?php echo number_format($totalMonthMinus6,0); ?> <span class="smallerfont">&euro;</span></td>
  <td class="evolution"><?php
  $evolution = (($totalMonthMinus6 - $totalMonthMinus7) /  $totalMonthMinus7) * 100;
  if ($totalMonthMinus6 > $totalMonthMinus7) {
	  // Improvement
	  echo number_format($evolution,0) . "% <img src='images/positive2.png' />";
  } else if ($totalMonthMinus6 < $totalMonthMinus7) {
	  // Decline
	  echo number_format($evolution,0) . "% <img src='images/negative2.png' />";
  }
?>
  </td>
 </tr>
 <tr>
  <td class="first"><?php echo date("F", strtotime("-6 months", strtotime("first day of last month") )); ?>:</td>
  <td><?php echo number_format($donationsMonthMinus7,0); ?> <span class="smallerfont">&euro;</span></td>
  <td><?php echo number_format($feesMonthMinus7,0); ?> <span class="smallerfont">&euro;</span></td>
  <td><?php echo number_format($totalMonthMinus7,0); ?> <span class="smallerfont">&euro;</span></td>
 </tr>
</table>














</div>



<?php displayFooter(); ?>
