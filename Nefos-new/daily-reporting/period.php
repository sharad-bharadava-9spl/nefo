<?php
	
	require_once '../cOnfig/connection.php';
	require_once '../cOnfig/view.php';
	require_once '../cOnfig/authenticate.php';
	require_once '../cOnfig/languages/common.php';
	
	session_start();
	$accessLevel = '3';
	
	// Authenticate & authorize
	authorizeUser($accessLevel);
	
	/* Select first closing, and loop the below with a for loop until it reaches that date.
	Use strtotime to find first closing, then find next sunday after that.
	
	
	
	
	$selectFirstClosingDay = "SELECT closingtime from closing ORDER BY closingtime ASC LIMIT 1";
	$firstClosing = mysql_query($selectFirstClosingDay);
	
	$row = mysql_fetch_array($firstClosing);
		$firstClosing = $row[0];
		
	$firstClosing = date('Y-m-d',strtotime($firstClosing));
	
	
	
	
	echo $firstClosing;
	
	echo "<br /><br />";
	
	
	$lastSunday = date('Y-m-d',strtotime('last sunday'));
	echo $lastSunday;
	
		if ($firstClosing < $lastSunday) {
			echo "smaller";
		}

	
	echo "<br /><br />";
	
	$lastMonday = date('Y-m-d',strtotime('last monday'));
	echo $lastMonday;
	
	*/
	
	$startDate = '2015-12-28';
	
	$endDate = '2016-01-03';
	

	// New members today
	$newMembers = "SELECT COUNT(user_id) FROM users where DATE(registeredSince) BETWEEN DATE('$startDate') AND DATE('$endDate')";

	$result = mysql_query($newMembers)
		or handleError($lang['error-loadnewmembers'],"Error loading sale from db: " . mysql_error());
	
	$row = mysql_fetch_array($result);
		$newmembers = $row['COUNT(user_id)'];

	// Look up renewed members		
	$selectMembers = "SELECT COUNT(m.paymentid) FROM memberpayments m, users u WHERE u.user_id = m.userid AND DATE(m.paymentdate) BETWEEN DATE('$startDate') AND DATE('$endDate') AND DATE(u.registeredSince) NOT BETWEEN DATE('$startDate') AND DATE('$endDate')";

	$result = mysql_query($selectMembers)
		or handleError($lang['error-loadsales'],"Error loading sale from db: " . mysql_error());
		
	$row = mysql_fetch_array($result);
		$renewedMembers = $row['COUNT(m.paymentid)'];
		
		
	// Look up todays dispenses
	$selectSales = "SELECT SUM(amount), SUM(quantity) from sales WHERE DATE(saletime) BETWEEN DATE('$startDate') AND DATE('$endDate')";

	$result = mysql_query($selectSales)
		or handleError($lang['error-dispenseload'],"Error loading sale from db: " . mysql_error());
		
	$row = mysql_fetch_array($result);
		$salesToday = $row['SUM(amount)'];
		$quantitySold = $row['SUM(quantity)'];
		
	// Look up todays dispenses by category 1
	$selectSalesFlower = "SELECT SUM(d.amount), SUM(d.quantity) from sales s, salesdetails d WHERE s.saleid = d.saleid AND DATE(s.saletime) BETWEEN DATE('$startDate') AND DATE('$endDate') AND d.category = 1";

	$resultFlower = mysql_query($selectSalesFlower)
		or handleError($lang['error-dispenseload'],"Error loading sale from db: " . mysql_error());
	
	$row = mysql_fetch_array($resultFlower);
		$salesTodayFlower = $row['SUM(d.amount)'];
		$quantitySoldFlower = $row['SUM(d.quantity)'];
		
	$flowerSalesPercentageToday = ($salesTodayFlower / $salesToday) * 100;
	$flowerGramsPercentageToday = ($quantitySoldFlower / $quantitySold) * 100;
	
	// Look up todays dispenses by category 2
	$selectSalesExtract = "SELECT SUM(d.amount), SUM(d.quantity) from sales s, salesdetails d WHERE s.saleid = d.saleid AND DATE(s.saletime) BETWEEN DATE('$startDate') AND DATE('$endDate') AND d.category = 2";

	$resultExtract = mysql_query($selectSalesExtract)
		or handleError($lang['error-dispenseload'],"Error loading sale from db: " . mysql_error());
	
	$row = mysql_fetch_array($resultExtract);
		$salesTodayExtract = $row['SUM(d.amount)'];
		$quantitySoldExtract = $row['SUM(d.quantity)'];
		
	$extractSalesPercentageToday = ($salesTodayExtract / $salesToday) * 100;
	$extractGramsPercentageToday = ($quantitySoldExtract / $quantitySold) * 100;

	// Look up todays donations
	$selectDonations = "SELECT SUM(amount) from donations WHERE DATE(donationTime) BETWEEN DATE('$startDate') AND DATE('$endDate')";

	$donationResult = mysql_query($selectDonations)
		or handleError($lang['error-donationload'],"Error loading donations from db: " . mysql_error());
		
	$row = mysql_fetch_array($donationResult);
		$donationsToday = $row['SUM(amount)'];
		
	// Look up today's membership fees
	$selectMembershipFees = "SELECT SUM(amountPaid) FROM memberpayments WHERE DATE(paymentdate) BETWEEN DATE('$startDate') AND DATE('$endDate')";
				
	$result = mysql_query($selectMembershipFees)
		or handleError($lang['error-expensesload'],"Error loading expense from db: " . mysql_error());
			
	$row = mysql_fetch_array($result);
		$membershipFees = $row['SUM(amountPaid)'];
		
		
		
	// Look up today's papers 4 categories
		
	$closingLookup = "SELECT SUM(paraphernalia), SUM(biscuits), SUM(drinksandsnacks), SUM(prerolls), SUM(expenses), SUM(moneytaken), SUM(bankExpenses), cashintill, SUM(tillDelta) from closing WHERE DATE(closingtime) BETWEEN DATE('$startDate') AND DATE('$endDate')";

		$result = mysql_query($closingLookup)
			or handleError($lang['error-closingload'],"Error loading closing from db: " . mysql_error());

		// Retrieve yesterdays closing data
		$row = mysql_fetch_array($result);
			$papersAndP = $row['SUM(paraphernalia)'];
			$biscuitsAndTHC = $row['SUM(biscuits)'];
			$drinksAndSnacks = $row['SUM(drinksandsnacks)'];
			$prerolls = $row['SUM(prerolls)'];
			$tillExpenses = $row['SUM(expenses)'];
			$banked = $row['SUM(moneytaken)'];
			$bankExpenses = $row['SUM(bankExpenses)'];
			$tillTot = $row['cashintill'];
			$tillDelta = $row['SUM(tillDelta)'];
			
 
			
		
		// Calculate total income
		$totalIncome = $donationsToday + $membershipFees + $tillAdditions + $papersAndP + $biscuitsAndTHC + $drinksAndSnacks + $prerolls;
					

		// Calculate estimated till	& club
		// $tillTot = $tillBalance + $donationsToday + $membershipFees + $tillAdditions - $tillExpenses;
		$clubBalance = $tillTot + $bankBalance;
		

		

	pageStart("Week status", NULL, NULL, "pday", "summary admin", "WEEKLY REPORT", $_SESSION['successMessage'], $_SESSION['errorMessage']);
	

	$financeSummary = <<<EOD
<table style='color: #444; text-align: right; font-family: Tahoma, Verdana, sans-serif; font-size: 14px; min-width: 600px;'  cellpadding='5'>
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
  <td style='text-align: left;'>{$lang['closeday-renewedmembers']}</td>
  <td>$renewedMembers</td>
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
  <td style='border-bottom: 1px dashed #ababab;'>{$expr(number_format($tillAdditions,0))} &euro;</td>
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
  <td><strong>{$expr(number_format($banked,0))} &euro;</strong></td>
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
 <tr rowspan='2'>
  <td colspan='8'>&nbsp;</td>
 </tr>
 
EOD;

























	// Query to look up debt
	$selectDebts = "SELECT memberno, first_name, last_name, credit FROM users WHERE credit < 0 ORDER BY credit ASC";

	$result = mysql_query($selectDebts)
		or handleError($lang['error-expensesload'],"Error loading expense from db: " . mysql_error());

		
		$debtSummary .= <<<EOD
<br />
<table style='color: #444; text-align: right; font-family: Tahoma, Verdana, sans-serif; font-size: 14px;' cellpadding='5'>
 <tr>
  <td colspan='8' style='color: #5aa242; text-align: left; font-size: 17px; border-bottom: 2px solid #a80082;'><strong>DEBT SUMMARY</strong></td>
 </tr>
	   <tr>
	    <td style='text-align: center;'><strong>#</strong></td>
	    <td style='text-align: center;'><strong>Name</strong></td>
	    <td style='text-align: center;'><strong>Debt</strong></td>
	   </tr>
EOD;


while ($expense = mysql_fetch_array($result)) {
	
	$memberno = $expense['memberno'];
	$first_name = $expense['first_name'];
	$last_name = $expense['last_name'];
	$credit = $expense['credit'];
	$sumCredit = $expense['SUM(credit)'];
	
	
	
	$expense_row =	sprintf("
  	  <tr>
  	   <td style='text-align: left'>%s</td>
  	   <td style='text-align: left'>%s %s</td>
  	   <td style='text-align: right'>%0.2f</td>
	  </tr>",
	  $memberno, $first_name, $last_name, $credit
	  );
	  $debtSummary.= $expense_row;
  }
  
  
  
  	// TOTAL DEBT:
  	$selectDebt = "SELECT SUM(credit) FROM users WHERE credit < 0 ORDER BY credit ASC";

	$result = mysql_query($selectDebt)
		or handleError($lang['error-expensesload'],"Error loading expense from db: " . mysql_error());
		
	$row = mysql_fetch_array($result);
  	    $totDebt = $row['SUM(credit)'];

		

  $debtSummary.= "
    <tr>
  	   <td style='text-align: left'><strong>TOTAL</strong></td>
  	   <td colspan='2' style='text-align: right'><strong>$totDebt</strong></td>
	  </tr>";


  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  












	// Query to look up expense summary
	$selectExpenses = "SELECT moneysource, expensecategory, SUM(amount) FROM expenses WHERE DATE(registertime) BETWEEN DATE('$startDate') AND DATE('$endDate') GROUP BY moneysource, expensecategory";

	$result = mysql_query($selectExpenses)
		or handleError($lang['error-expensesload'],"Error loading expense from db: " . mysql_error());
	$result2 = mysql_query($selectExpenses)
		or handleError($lang['error-expensesload'],"Error loading expense from db: " . mysql_error());

		
		
		$expenseSummary .= <<<EOD
<br />
<table style='color: #444; text-align: right; font-family: Tahoma, Verdana, sans-serif; font-size: 14px;' cellpadding='5'>
 <tr>
  <td colspan='8' style='color: #5aa242; text-align: left; font-size: 17px; border-bottom: 2px solid #a80082;'><strong>EXPENSE SUMMARY</strong></td>
 </tr>
	   <tr>
	    <td style='text-align: center;'><strong>{$lang['global-source']}</strong></td>
	    <td style='text-align: center;'><strong>{$lang['global-category']}</strong></td>
	    <td style='text-align: center;'><strong>{$lang['global-amount']}</strong></td>
	   </tr>
EOD;


while ($expense = mysql_fetch_array($result2)) {
	
	$moneysource = $expense['moneysource'];
	$expenseCat = $expense['expensecategory'];
	$amount = $expense['SUM(amount)'];
	
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
	
	
	$expense_row =	sprintf("
  	  <tr>
  	   <td style='text-align: left'>%s</td>
  	   <td style='text-align: left'>%s</td>
  	   <td>%s</td>
	  </tr>",
	  $source, $expenseCat, $amount
	  );
	  $expenseSummary.= $expense_row;
  }

  
  
  
  
  




















  
  
		// Query to look up expenses
	$selectExpenses = "SELECT expenseid, registertime, userid, expensetype, expense, moneysource, other, amount, shop, comment, receipt, expensecategory FROM expenses WHERE DATE(registertime) BETWEEN DATE('$startDate') AND DATE('$endDate') ORDER by registertime DESC";

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
  
  	  $mailBody = $financeSummary;
	  $mailBody.= "</table>";
	  $mailBody.= $productOverview;
	  $mailBody.= $productDetails;
	  $mailBody.= "</table>";
	  $mailBody.= $expenseSummary;
	  $mailBody.= "</table>";
	  $mailBody.= $expenseDetails;
	  $mailBody.= "</table>";
	  $mailBody.= $debtSummary;
	  $mailBody.= "</table>";
	  
	  
	  
	  
  	  echo $financeSummary;
	  echo "</table>";
	  echo $productOverview;
	  echo $productDetails;
	  echo "</table>";
	  echo $expenseSummary;
	  echo "</table>";
	  echo $expenseDetails;
	  echo "</table>";
	  echo $debtSummary;
	  echo "</table>";

		// Compose admin e-mail first
		$headers = "From: CCS <info@madiguana.es>\r\n";
		$headers .= 'MIME-Version: 1.0' . "\r\n";
		$headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
		$mailSubject = $lang['closeday-closingovv'];

		mail ('mr.pink@madiguana.es', $mailSubject, $mailBody, $headers);
			
			
			
			
			
			
			
	
		displayFooter();