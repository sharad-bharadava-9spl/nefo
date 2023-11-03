<?php
	
	require_once 'cOnfig/connection.php';
	require_once 'cOnfig/view.php';
	require_once 'cOnfig/authenticate.php';
	
	session_start();
	$accessLevel = '3';
	
	// Authenticate & authorize
	authorizeUser($accessLevel);
	
		// Query to look up openings
	$selectOpenings = "SELECT openingtime, tillBalance, moneyOwed, tillDelta FROM opening ORDER by openingtime DESC";

	$result = mysql_query($selectOpenings)
		or handleError("Error loading expenses from database.","Error loading expense from db: " . mysql_error());

	pageStart("Madiguana Cloud | Expenses", NULL, NULL, "pexpenses", NULL, "Expenses", $_SESSION['successMessage'], $_SESSION['errorMessage']);
?>

	 <table>
	  <thead>
	   <tr>
	    <th>Time</th>
	    <th>Till balance</th>
	    <th>Till Delta</th>
	    <th>Money owed</th>
	    <th>Product</th>
	    <th>Product value</th>
	    <th>Flower</th>
	    <th>Flower value</th>
	    <th>Extract</th>
	    <th>Extract value</th>
	   </tr>
	  </thead>
	  <tbody>
	  
	  <?php

while ($expense = mysql_fetch_array($result2)) {
	
	$userid = $expense['userid']; // find member
	$moneysource = $expense['moneysource'];
	$receipt = $expense['receipt'];
	$other = $expense['other'];
	$formattedDate = date("d M H:i", strtotime($expense['registertime']));
	
	if ($moneysource == 1) {
		$source = 'Till';
	} else if ($moneysource == 2) {
		$source = 'Bank';
	} else if ($moneysource == 3) {
		$source = $other;
	} else {
		$source = 'ERROR';
	}
	
	if ($receipt == 1) {
		$recClass = "";
		$receipt = "Yes";
	} else if ($receipt == 2) {
		$recClass = "negative";
		$receipt = "No";
	}
	
		$userDetails = "SELECT memberno, first_name from users WHERE user_id = $userid";
		$result = mysql_query($userDetails)
			or handleError("Error loading users from database.","Error loading users from db: " . mysql_error());
			
		while ($user = mysql_fetch_array($result)) {
			$member = "#" . $user['memberno'] . " - " . $user['first_name'];
		}

	
	
	$expense_row =	sprintf("
  	  <tr class='clickableRow' href='expense.php?expenseid=%d'>
  	   <td>%s</td>
  	   <td>%s</td>
  	   <td>%s</td>
  	   <td>%s</td>
  	   <td style='text-align: right;'>%0.2f <span class='smallerfont'>{$_SESSION['currencyoperator']}</span></td>
  	   <td>%s</td>
  	   <td class='%s'>%s</td>
  	   <td style='text-align: center;'><a href='edit-expense.php?expenseid=%d'><img src='images/edit.png' height='15' title='Edit expense' /></a>&nbsp;&nbsp;<a href='javascript:delete_expense(%d)'><img src='images/delete.png' height='15' title='Delete expense' /></a></td>
	  </tr>",
	  $expense['expenseid'], $formattedDate, $expense['expense'], $expense['shop'], $member, $expense['amount'], $source, $recClass, $receipt, $expense['expenseid'], $expense['expenseid']
	  );
	  echo $expense_row;
  }
?>

	 </tbody>
	 </table>
	 
<?php  displayFooter(); ?>
