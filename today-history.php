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
			
	
		// If no closing ID is set, we display the list of closing dates
		if (!isset($_POST['reportDate'])) {
			
			// Find first sales date
			$findStartDate = "SELECT saletime FROM sales ORDER BY saletime ASC LIMIT 1";
		try
		{
			$result = $pdo3->prepare("$findStartDate");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
				$startDate = date('Y-m-d', strtotime($row['saletime']));
		
			$endOperator = strtotime("-1 day", strtotime($startDate));
		
			// Find number of rows to display
		    $noOfLines = floor((time() - $endOperator)/(60*60*24)) . "<br />";
    
			// Pagination settings & initialisation
			$resultLimit = 50;
										
			if (isset($_GET['page'])) {
	        	$page = $_GET['page'] + 1;
	            $offset = $resultLimit * $page;
	        } else {
	            $page = 0;
	            $offset = 0;
	        }
			
	        $resultsLeft = $noOfLines - ($page * $resultLimit);
	        
			$reportDate = time();

			for ($i = 1; $i <= $noOfLines; $i++) {
   				$reportDateReadable = date('dS M Y', $reportDate);
				$reportDateSQL = date("Y-m-d", $reportDate);
    			$reportDate -= 86400;

				$output .= "<form action='' method='POST'><input type='hidden' name='reportDate' value='$reportDateSQL'><input type='hidden' name='reportDateReadable<br />' value='$reportDateReadable'><button type='submit' class='linkStyle cta1'>$reportDateReadable<br /></button></form>";
			}
			
			// Pagination display
			if ($resultsLeft < $resultLimit && $offset != 0) {
            	$last = $page - 2;
            	$output .=  "<br /><a href='$_PHP_SELF?page=$last'>&laquo; Previous</a><br />&nbsp;";
         	} else if ($page > 0) {
	            $last = $page - 2;
	            $output .=  "<a href='$_PHP_SELF?page=$last' style='font-size: 25px;'>&laquo;</a>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<a href='$_PHP_SELF?page=$page' style='font-size: 25px;'>&raquo;</a><br />&nbsp;";
	        } else if ($page == 0 && $offset != 0) {
            	$output .=  "<br /><a href='$_PHP_SELF?page=$page'>Next &raquo;</a><br />&nbsp;";
         	} 
			
			pageStart("Daily reports", NULL, NULL, "preporting", "daily dev-align-center", "DAILY REPORT", $_SESSION['successMessage'], $_SESSION['errorMessage']);
			include 'closinglist.html.php';
			
			
		// If a closing ID is set, we display the report
		} else {
			
			$reportDate = $_POST['reportDate'];
			$reportDateReadable = $_POST['reportDateReadable'];
			
			$openingLookup = "SELECT tillBalance FROM recopening WHERE DATE(openingtime) = DATE('$reportDate') ORDER BY openingtime DESC LIMIT 1";
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
			
			
	// Look up today's till expenses
	$selectExpenses = "SELECT SUM(amount) FROM expenses WHERE DATE(registertime) = DATE('$reportDate') AND moneysource = 1";
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
	$selectDonations = "SELECT SUM(amount) FROM donations WHERE donatedTo <> 3 AND (donatedTo < 2 OR donatedTo = 4) AND DATE(donationTime) = DATE('$reportDate')";
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
	$selectMembershipFees = "SELECT SUM(amountPaid) FROM memberpayments WHERE (paidTo < 2 OR paidTo = 4) AND DATE(paymentdate) = DATE('$reportDate')";
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
	$selectDonations = "SELECT SUM(amount) FROM donations WHERE donatedTo <> 3 AND donatedTo = 2 AND DATE(donationTime) = DATE('$reportDate')";
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
	$selectMembershipFees = "SELECT SUM(amountPaid) FROM memberpayments WHERE paidTo = 2 AND DATE(paymentdate) = DATE('$reportDate')";
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
	$selectExpenses = "SELECT '1' AS type, donationTime AS time, userid, amount, donatedTo FROM donations WHERE donatedTo <> 3 AND DATE(donationTime) = DATE('$reportDate') UNION ALL SELECT '2' AS type, paymentdate AS time, userid, amountPaid AS amount, paidTo AS donatedTo FROM memberpayments WHERE DATE(paymentdate) = DATE('$reportDate') ORDER BY time ASC";
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
	$selectBanked = "SELECT SUM(amount) FROM banked WHERE DATE(time) = DATE('$reportDate')";
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
			
		
	// Calculate estimated till	& club balances
	$tillTotalToday = $donations + $membershipFees;
	$bankTotalToday = $bankDonations + $membershipfeesBank;
	
	$totalToday = $tillTotalToday + $bankTotalToday;
	
	
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
	

	pageStart("Status", NULL, $deleteDonationScript, "pstatus", "dev-align-center", "STATUS", $_SESSION['successMessage'], $_SESSION['errorMessage']);
	
	echo <<<EOD
<table class='default' style='color: #444; text-align: right; font-family: Tahoma, Verdana, sans-serif; font-size: 14px;'>
 <tr>
  <td colspan='2'><h5><strong>{$lang['closeday-finances']}</strong></h5></td>
 </tr>
 <tr>
  <td style='text-align: left;'>{$lang['closeday-tillatopening']}</td>
  <td>{$expr(number_format($tillBalance,2))} {$_SESSION['currencyoperator']}</td>
 </tr>
 <tr>
  <td style='text-align: left;' class="green">+ {$lang['closeday-donations-till']}</td>
  <td  class="green">{$expr(number_format($donations,2))} {$_SESSION['currencyoperator']}</td>
 </tr>
 <tr>
  <td style='text-align: left;' class="green">+ {$lang['closeday-membershipfees-till']}</td>
  <td  class="green">{$expr(number_format($membershipFees,2))} {$_SESSION['currencyoperator']}</td>
 </tr>
 <tr>
  <td style='text-align: left;' class="red">- {$lang['title-expenses']}</td>
  <td  class="red">{$expr(number_format($tillExpenses,2))} {$_SESSION['currencyoperator']}</td>
 </tr>
 <tr>
  <td style='text-align: left; border-bottom: 1px dashed #ababab;' class="red">- {$lang['banked-during-day']}</td>
  <td style='border-bottom: 1px dashed #ababab;'  class="red">{$expr(number_format($bankedDuringDay,2))} {$_SESSION['currencyoperator']}</td>
 </tr>
 <tr>
  <td style='text-align: left; border-bottom: 1px solid #ababab;'><strong>{$lang['tillbalnow']}</strong></td>
  <td style='border-bottom: 1px solid #ababab;'><strong>{$expr(number_format($tillBalance + $donations + $membershipFees - $tillExpenses - $bankedDuringDay,2))} {$_SESSION['currencyoperator']}</strong></td>
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
  <td>{$expr(number_format($bankDonations,2))} {$_SESSION['currencyoperator']}</td>
 </tr>
 <tr>
  <td style='text-align: left; border-bottom: 1px dashed #ababab;'>{$lang['closeday-membershipfees-bank']}</td>
  <td style='border-bottom: 1px dashed #ababab;'>{$expr(number_format($membershipfeesBank,2))} {$_SESSION['currencyoperator']}</td>
 </tr>
 <tr>
  <td style='text-align: left; border-bottom: 1px solid #ababab;'><strong>{$lang['closeday-totalincome-bank']}</strong></td>
  <td style='border-bottom: 1px solid #ababab;'><strong>{$expr(number_format($bankTotalToday,2))} {$_SESSION['currencyoperator']}</strong></td>
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
  <td style='border-bottom: 1px solid #000; border-top: 1px solid #000; border-right: 1px solid #000;'><strong>{$expr(number_format($totalToday,2))} {$_SESSION['currencyoperator']}</strong></td>
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

	$expense_row = sprintf("
  	  <tr>
  	   <td class='left' style='padding: 10px;'>%s</td>
  	   <td class='left' style='padding: 10px;'>%s</td>
  	   <td class='left' style='padding: 10px;'>%s</td>
  	   <td class='left' style='padding: 10px;'>%s</td>
  	   <td class='left' style='padding: 10px;'>%s %s</td>
  	   <td class='right' style='padding: 10px;'>%0.02f {$_SESSION['currencyoperator']}</td>
	  </tr>",
	  $donationTime, $movementType, $donatedTo, $memberno, $first_name, $last_name, $amount
	  );
			

	  echo $expense_row;
}
  	  echo "</tbody></table>";
  	  
  	  
	  }
	  
