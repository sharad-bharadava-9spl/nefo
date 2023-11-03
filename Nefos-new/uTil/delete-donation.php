<?php

	require_once '../cOnfig/connection.php';
	require_once '../cOnfig/authenticate.php';

	session_start();
	$accessLevel = '3';
	
	// Authenticate & authorize
	authorizeUser($accessLevel);
	
	// Get the purchase ID
	$donationid = $_GET['donationid'];
	$amount = $_GET['amount'];
	$userid = $_GET['userid'];
	
	// Delete the donation
	$deleteDonation = sprintf("DELETE FROM donations WHERE donationid = '%d';", $donationid);
	
	mysql_query($deleteDonation)
		or handleError("Error deleting donation.","Error deleting donation: " . mysql_error());
	
	// Delete the donation
	$deleteDonation = sprintf("DELETE FROM f_donations WHERE donationid = '%d';", $donationid);
	
	mysql_query($deleteDonation)
		or handleError("Error deleting donation.","Error deleting donation: " . mysql_error());
		
	// Look up user to find credit balance
	$userDetails = "SELECT credit FROM users WHERE user_id = {$userid}"; // 30
			
	$result = mysql_query($userDetails);
	
	$row = mysql_fetch_array($result);
		$credit = $row['credit'];
	
	// Adjust the credit balance and save the new balance
	$newCredit = $credit - $amount;
	
	$updateUser = sprintf("UPDATE users SET credit = '%f' WHERE user_id = '%d';",
		mysql_real_escape_string($newCredit),
		mysql_real_escape_string($userid)
		);
			
	mysql_query($updateUser)
		or handleError("Error saving data to database. Please try again.","Error updating user profile: " . mysql_error());

	// Write to log
	$logTime = date('Y-m-d H:i:s');

	$query = sprintf("INSERT INTO log (logtype, logtime, user_id, operator, amount, oldCredit, newCredit) VALUES ('%d', '%s', '%d', '%d', '%f', '%f', '%f');",
	2, $logTime, $userid, $_SESSION['user_id'], $amount, $credit, $newCredit);
	
	mysql_query($query)
	or handleError($lang['error-savedata'],"Error inserting purchase: " . mysql_error());
	
	$query = sprintf("INSERT INTO f_log (logtype, logtime, user_id, operator, amount, oldCredit, newCredit) VALUES ('%d', '%s', '%d', '%d', '%f', '%f', '%f');",
	2, $logTime, $userid, $_SESSION['user_id'], $amount, $credit, $newCredit);
	
	mysql_query($query)
	or handleError($lang['error-savedata'],"Error inserting purchase: " . mysql_error());
	
		$_SESSION['successMessage'] = "Donation deleted succesfully!";
		
		if (isset($_GET['donscreen'])) {
			header("Location: ../donations.php");
		} else {
			header("Location: ../donation-management.php?userid=$userid");
		}
	
		// On success: redirect.
		exit();
?>