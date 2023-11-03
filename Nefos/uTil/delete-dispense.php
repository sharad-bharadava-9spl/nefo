<?php

	require_once '../cOnfig/connection.php';
	require_once '../cOnfig/authenticate.php';

	session_start();
	$accessLevel = '3';
	
	// Authenticate & authorize
	authorizeUser($accessLevel);
	
	// Get the sale ID
	$saleid = $_GET['saleid'];
	
	// Look up sale details (amount)
	$selectSale = "SELECT userid, amount FROM sales where saleid = $saleid";

	$result = mysql_query($selectSale)
		or handleError($lang['error-loadsale'],"Error loading sale from db: " . mysql_error());
	
	$row = mysql_fetch_array($result);
		$userid = $row['userid'];
		$amount = $row['amount'];
		
	// Look up users credit
	$selectCredit = "SELECT credit FROM users WHERE user_id = $userid";

	$result = mysql_query($selectCredit)
		or handleError($lang['error-loadsale'],"Error loading sale from db: " . mysql_error());
	
	$row = mysql_fetch_array($result);
		$credit = $row['credit'];
	
	// Adjust user credit
	$newcredit = $credit + $amount;
	
		$updateCredit = sprintf("UPDATE users SET credit = '%f' WHERE user_id = '%d';",
			mysql_real_escape_string($newcredit),
			mysql_real_escape_string($userid)
			);
				
		mysql_query($updateCredit)
			or handleError($lang['error-savingcredit'],"Error updating user profile credit: " . mysql_error());
	
		
	// Build the delete statement
	$deleteUser = sprintf("DELETE FROM sales WHERE saleid = '%d';", $_REQUEST['saleid']);
	
	mysql_query($deleteUser)
		or handleError($lang['error-errordeletingdispense'],"Error deleting sale: " . mysql_error());
		
	$deleteUser = sprintf("DELETE FROM f_sales WHERE saleid = '%d';", $_REQUEST['saleid']);
	
	mysql_query($deleteUser)
		or handleError($lang['error-errordeletingdispense'],"Error deleting sale: " . mysql_error());
		
		
	$deleteUser = sprintf("DELETE FROM salesdetails WHERE saleid = '%d';", $_REQUEST['saleid']);
	
	mysql_query($deleteUser)
		or handleError($lang['error-errordeletingdispense'],"Error deleting sale: " . mysql_error());
		
	$deleteUser = sprintf("DELETE FROM f_salesdetails WHERE saleid = '%d';", $_REQUEST['saleid']);
	
	mysql_query($deleteUser)
		or handleError($lang['error-errordeletingdispense'],"Error deleting sale: " . mysql_error());
		
	// Write to log
	$logTime = date('Y-m-d H:i:s');

	$query = sprintf("INSERT INTO log (logtype, logtime, user_id, operator, amount, oldCredit, newCredit) VALUES ('%d', '%s', '%d', '%d', '%f', '%f', '%f');",
	1, $logTime, $userid, $_SESSION['user_id'], $amount, $credit, $newcredit);
	
	mysql_query($query)
	or handleError($lang['error-savedata'],"Error inserting purchase: " . mysql_error());

	$query = sprintf("INSERT INTO f_log (logtype, logtime, user_id, operator, amount, oldCredit, newCredit) VALUES ('%d', '%s', '%d', '%d', '%f', '%f', '%f');",
	1, $logTime, $userid, $_SESSION['user_id'], $amount, $credit, $newcredit);
	
	mysql_query($query)
	or handleError($lang['error-savedata'],"Error inserting purchase: " . mysql_error());

	$_SESSION['successMessage'] = "Dispense has been deleted.";
	header("Location: ../dispenses.php");
?>
