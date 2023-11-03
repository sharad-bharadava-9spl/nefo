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
	

		$openingLookup = "SELECT cashintill FROM closing ORDER BY closingtime DESC LIMIT 1";
		
		$result = mysql_query($openingLookup)
			or handleError($lang['error-noopeningdetails'],"Error loading opening from db: " . mysql_error());
	
		$row = mysql_fetch_array($result);
			$tillBalance = $row['cashintill'];	
			
	// Look up today's till expenses
	$selectExpenses = "SELECT SUM(amount) FROM expenses WHERE DATE(registertime) = DATE(NOW()) AND moneysource = 1";
			
	$expenseResult = mysql_query($selectExpenses)
		or handleError($lang['error-expensesload'],"Error loading expense from db: " . mysql_error());
		
	$row = mysql_fetch_array($expenseResult);
		$tillExpenses = $row['SUM(amount)'];
		
	// Look up todays donations
	$selectDonations = "SELECT SUM(amount) from donations WHERE (donatedTo = 0 OR donatedTo = 1) AND DATE(donationTime) = DATE(NOW())";

	$donationResult = mysql_query($selectDonations)
		or handleError($lang['error-donationload'],"Error loading donations from db: " . mysql_error());
		
	$row = mysql_fetch_array($donationResult);
		$donations = $row['SUM(amount)'];
		
	// Look up today's membership fees
	$selectMembershipFees = "SELECT SUM(amountPaid) FROM memberpayments WHERE paidTo <> 2 AND DATE(paymentdate) = DATE(NOW())";
				
	$result = mysql_query($selectMembershipFees)
		or handleError($lang['error-expensesload'],"Error loading expense from db: " . mysql_error());
			
	$row = mysql_fetch_array($result);
		$membershipFees = $row['SUM(amountPaid)'];
		
	// Look up todays bank donations
	$selectDonations = "SELECT SUM(amount) from donations WHERE donatedTo = 2 AND DATE(donationTime) = DATE(NOW())";

	$donationResult = mysql_query($selectDonations)
		or handleError($lang['error-donationload'],"Error loading donations from db: " . mysql_error());
		
	$row = mysql_fetch_array($donationResult);
		$bankDonations = $row['SUM(amount)'];
		
		
	// Look up today's membership fees Bank
	$selectMembershipFees = "SELECT SUM(amountPaid) FROM memberpayments WHERE paidTo = 2 AND DATE(paymentdate) = DATE(NOW())";
				
	$result = mysql_query($selectMembershipFees)
		or handleError($lang['error-expensesload'],"Error loading expense from db: " . mysql_error());
			
	$row = mysql_fetch_array($result);
		$membershipfeesBank = $row['SUM(amountPaid)'];
		
	// Query to look up daily donations & membership fees
	$selectExpenses = "SELECT '1' AS type, donationTime AS time, userid, amount, donatedTo FROM donations WHERE DATE(donationTime) = DATE(NOW()) UNION ALL SELECT '2' AS type, paymentdate AS time, userid, amountPaid AS amount, paidTo AS donatedTo FROM memberpayments WHERE DATE(paymentdate) = DATE(NOW())";

	$result2 = mysql_query($selectExpenses)
		or handleError($lang['error-donationload'],"Error loading expense from db: " . mysql_error());


	// Look up money banked during the day
	$selectBanked = "SELECT SUM(amount) FROM banked WHERE DATE(time) = DATE(NOW())";
			
	$result = mysql_query($selectBanked)
		or handleError($lang['error-expensesload'],"Error loading expense from db: " . mysql_error());
		
	$row = mysql_fetch_array($result);
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
	

	pageStart("Status", NULL, $deleteDonationScript, "pstatus", "", "STATUS", $_SESSION['successMessage'], $_SESSION['errorMessage']);
	
	echo <<<EOD
<table style='color: #444; text-align: right; font-family: Tahoma, Verdana, sans-serif; font-size: 14px;'>
 <tr>
  <td colspan='2' style='color: #5aa242; text-align: left; font-size: 17px; border-bottom: 2px solid #a80082;'><strong>{$lang['closeday-finances']}</strong></td>
 </tr>
 <tr>
  <td style='text-align: left;'>Caja apertura</td>
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
  <td style='text-align: left;'>- Gastos</td>
  <td>{$expr(number_format($tillExpenses,2))} &euro;</td>
 </tr>
 <tr>
  <td style='text-align: left; border-bottom: 1px dashed #ababab;'>- Banqueado durante el dia</td>
  <td style='border-bottom: 1px dashed #ababab;'>{$expr(number_format($bankedDuringDay,2))} &euro;</td>
 </tr>
 <tr>
  <td style='text-align: left; border-bottom: 1px solid #ababab;'><strong>Caja ahora</strong></td>
  <td style='border-bottom: 1px solid #ababab;'><strong>{$expr(number_format($tillBalance + $donations + $membershipFees - $tillExpenses - $bankedDuringDay,2))} &euro;</strong></td>
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
  <td style='text-align: left; border-bottom: 1px dashed #ababab;'>{$lang['closeday-membershipfees-bank']}</td>
  <td style='border-bottom: 1px dashed #ababab;'>{$expr(number_format($membershipfeesBank,2))} &euro;</td>
 </tr>
 <tr>
  <td style='text-align: left; border-bottom: 1px solid #ababab;'><strong>Ingresos banco total</strong></td>
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
  <td style='text-align: left; border-bottom: 1px solid #000; border-top: 1px solid #000; border-left: 1px solid #000;'><strong>Ingresos banco + efectivo</strong></td>
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
	    <th style='padding: 10px;'>#</th>
	    <th style='padding: 10px;'>{$lang['global-member']}</th>
	    <th style='padding: 10px;'>{$lang['global-amount']}</th>
	   </tr>
	  </thead>
	  <tbody>
EOD;

while ($donation = mysql_fetch_array($result2)) {
	
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

	$result = mysql_query($userDetails)
		or handleError($lang['error-userload'],"Error loading user: " . mysql_error());

	$row = mysql_fetch_array($result);
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
  	   <td class='right' style='padding: 10px;'>%0.02f &euro;</td>
	  </tr>",
	  $donationTime, $movementType, $donatedTo, $memberno, $first_name, $last_name, $amount
	  );
			

	  echo $expense_row;
}
  	  echo "</tbody></table>";

 displayFooter();