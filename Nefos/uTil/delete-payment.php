<?php

	require_once '../cOnfig/connection.php';
	require_once '../cOnfig/authenticate.php';

	session_start();
	$accessLevel = '3';
	
	// Authenticate & authorize
	authorizeUser($accessLevel);
	
	// Get the purchase ID
	$paymentid = $_GET['paymentid'];
	
	// Lookup old expiry date
	$oldExpiry = "SELECT userid, oldExpiry, newExpiry, amountPaid, paidTo FROM memberpayments WHERE paymentid = $paymentid";
	
	$result = mysql_query($oldExpiry);
	
	$row = mysql_fetch_array($result);
		$userid = $row['userid'];
		$oldExpiry = $row['oldExpiry'];
		$newExpiry = $row['newExpiry'];
		$amount = $row['amountPaid'];
		$paidTo = $row['paidTo'];
		
		// Adjust member credit if he paid from saldo
		if ($paidTo == 3) {
			
			// Look up user to find credit balance
			$userDetails = "SELECT credit FROM users WHERE user_id = $userid";
					
			$result = mysql_query($userDetails);
			
			$row = mysql_fetch_array($result);
				$credit = $row['credit'];
				
			$newCredit = $credit + $amount;
			
			// Update user table
			$updateUser = sprintf("UPDATE users SET credit = '%f' WHERE user_id = '%d';",
				mysql_real_escape_string($newCredit),
				mysql_real_escape_string($userid)
				);
	
			mysql_query($updateUser)
				or handleError($lang['error-savedata'],"Error inserting user: " . mysql_error());
			
		}
			
	
	
		// Update user table
		$updateUser = sprintf("UPDATE users SET paidUntil = '%s' WHERE user_id = '%d';",
mysql_real_escape_string($oldExpiry),
mysql_real_escape_string($userid)
);

		mysql_query($updateUser)
			or handleError($lang['error-savedata'],"Error inserting user: " . mysql_error());
	
	// Delete the donation
	$deleteDonation = sprintf("DELETE FROM memberpayments WHERE paymentid = '%d';", $paymentid);
	
		mysql_query($deleteDonation)
			or handleError($lang['error-savedata'],"Error inserting user: " . mysql_error());
			
	// Delete the donation
	$deleteDonation = sprintf("DELETE FROM f_memberpayments WHERE paymentid = '%d';", $paymentid);
	
		mysql_query($deleteDonation)
			or handleError($lang['error-savedata'],"Error inserting user: " . mysql_error());
			
	// Write to log
	$logTime = date('Y-m-d H:i:s');

	$query = sprintf("INSERT INTO log (logtype, logtime, user_id, operator, amount, oldExpiry, newExpiry) VALUES ('%d', '%s', '%d', '%d', '%f', '%s', '%s');",
	3, $logTime, $userid, $_SESSION['user_id'], $amount, $newExpiry, $oldExpiry);
	
	mysql_query($query)
	or handleError($lang['error-savedata'],"Error inserting purchase: " . mysql_error());
	
	$query = sprintf("INSERT INTO f_log (logtype, logtime, user_id, operator, amount, oldExpiry, newExpiry) VALUES ('%d', '%s', '%d', '%d', '%f', '%s', '%s');",
	3, $logTime, $userid, $_SESSION['user_id'], $amount, $newExpiry, $oldExpiry);
	
	mysql_query($query)
	or handleError($lang['error-savedata'],"Error inserting purchase: " . mysql_error());
	
		// On success: redirect.
		$_SESSION['successMessage'] = "Payment deleted succesfully!";
		
		if (isset($_GET['paymentscreen'])) {
			header("Location: ../member-payments.php");
		} else {
			header("Location: ../pay-membership.php?user_id=$userid");
		}
		
		exit();
?>