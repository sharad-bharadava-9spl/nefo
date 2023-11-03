<?php
	
	require_once 'cOnfig/connection.php';
	require_once 'cOnfig/view.php';
	require_once 'cOnfig/authenticate.php';
	require_once 'cOnfig/languages/common.php';
	
	session_start();
	$accessLevel = '3';
	
	// Authenticate & authorize
	authorizeUser($accessLevel);
	
	// Query to look up expenses
	$selectExpenses = "SELECT expenseid, registertime, userid, expensetype, expense, moneysource, other, amount, shop, comment, receipt, invoice, expensecategory, photoext FROM expenses WHERE expensecategory = 20 ORDER by registertime DESC";

	$result = mysql_query($selectExpenses)
		or handleError($lang['error-expensesload'],"Error loading expense from db: " . mysql_error());
	$result2 = mysql_query($selectExpenses)
		or handleError($lang['error-expensesload'],"Error loading expense from db: " . mysql_error());

	
	$deleteExpenseScript = <<<EOD
function delete_expense(expenseid) {
	if (confirm("{$lang['expense-deleteconfirm']}")) {
				window.location = "uTil/delete-expense.php?expenseid=" + expenseid + "&source=expenses";
				}
}
EOD;
	pageStart($lang['volunteer-payments'], NULL, $deleteExpenseScript, "pexpenses", "admin", $lang['volunteer-payments'], $_SESSION['successMessage'], $_SESSION['errorMessage']);
	
?>

<center><a href="new-volunteer-expense.php" class="cta"><?php echo $lang['expense-newexpense']; ?></a></center>

	 <table class="default">
	  <thead>
	   <tr>
	    <th><?php echo $lang['global-time']; ?></th>
	    <th><?php echo $lang['global-category']; ?></th>
	    <th><?php echo $lang['global-expense']; ?></th>
	    <th><?php echo $lang['global-shop']; ?></th>
	    <th><?php echo $lang['global-member']; ?></th>
	    <th><?php echo $lang['global-amount']; ?></th>
	    <th><?php echo $lang['global-source']; ?></th>
	    <th><?php echo $lang['global-receipt']; ?></th>
	    <th><?php echo $lang['global-invoice']; ?></th>
	    <th>Scan</th>
	    <th></th>
	    <th><?php echo $lang['global-actions']; ?></th>
	   </tr>
	  </thead>
	  <tbody>
	  
	  <?php

while ($expense = mysql_fetch_array($result2)) {
	
	$userid = $expense['userid']; // find member
	$moneysource = $expense['moneysource'];
	$receipt = $expense['receipt'];
	$invoice = $expense['invoice'];
	$other = $expense['other'];
	$expenseCat = $expense['expensecategory'];
	$photoext = $expense['photoext'];
	$formattedDate = date("d M H:i", strtotime($expense['registertime'] . "+$offsetSec seconds"));
	$expenseid = $expense['expenseid'];
	
	if ($expense['comment'] != '') {
		
		$commentRead = "
		                <img src='images/comments.png' id='comment$expenseid' /><div id='helpBox$expenseid' class='helpBox'>{$expense['comment']}</div>
		                <script>
		                  	$('#comment$expenseid').on({
						 		'mouseover' : function() {
								 	$('#helpBox$expenseid').css('display', 'block');
						  		},
						  		'mouseout' : function() {
								 	$('#helpBox$expenseid').css('display', 'none');
							  	}
						  	});
						</script>
		                ";
		
	} else {
		
		$commentRead = "";
		
	}

	
	$file = 'images/expenses/' . $expenseid . '.' . $photoext;

	if (file_exists($file)) {
		$invScan = "<a href='$file'><img src='images/receipt.png' /></a>";
	} else {
		$invScan = "<a href='new-expense.php?expenseid=$expenseid'><img src='images/receipt-na.png' /></a>";
	}
	
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
	
	if ($invoice == 1) {
		$invClass = "";
		$invoice = $lang['global-yes'];
	} else if ($invoice == 0) {
		$invClass = "negative";
		$invoice = $lang['global-no'];
	}
	
		$userDetails = "SELECT memberno, first_name from users WHERE user_id = $userid";
		$result = mysql_query($userDetails)
			or handleError($lang['error-usersload'],"Error loading users from db: " . mysql_error());
			
		while ($user = mysql_fetch_array($result)) {
			$member = "#" . $user['memberno'] . " - " . $user['first_name'];
		}

	
	
	$expense_row =	sprintf("
  	  <tr>
  	   <td class='clickableRow left' href='expense.php?expenseid=%d'>%s</td>
  	   <td class='clickableRow left' href='expense.php?expenseid=%d'>%s</td>
  	   <td class='clickableRow left' href='expense.php?expenseid=%d'>%s</td>
  	   <td class='clickableRow left' href='expense.php?expenseid=%d'>%s</td>
  	   <td class='clickableRow left' href='expense.php?expenseid=%d'>%s</td>
  	   <td style='text-align: right;' class='clickableRow' href='expense.php?expenseid=%d'>%0.2f <span class='smallerfont'>&euro;</span></td>
  	   <td class='clickableRow' href='expense.php?expenseid=%d'>%s</td>
  	   <td class='clickableRow %s' href='expense.php?expenseid=%d'>%s</td>
  	   <td class='clickableRow %s' href='expense.php?expenseid=%d'>%s</td>
	   <td>%s</td>
	   <td class='relative'>$commentRead</td>
  	   <td style='text-align: center;'><a href='edit-expense.php?expenseid=%d'><img src='images/edit.png' height='15' title='{$lang['expense-edit']}' /></a>&nbsp;&nbsp;<a href='javascript:delete_expense(%d)'><img src='images/delete.png' height='15' title='{$lang['expense-delete']}' /></a></td>
	  </tr>",
	  $expense['expenseid'], $formattedDate, $expense['expenseid'], $expenseCat, $expense['expenseid'], $expense['expense'], $expense['expenseid'], $expense['shop'], $expense['expenseid'], $member, $expense['expenseid'], $expense['amount'], $expense['expenseid'], $source, $recClass, $expense['expenseid'], $receipt, $invClass, $expense['expenseid'], $invoice, $invScan, $expense['expenseid'], $expense['expenseid']
	  );
	  echo $expense_row;
  }
?>

	 </tbody>
	 </table>
	 
	 
<?php  displayFooter(); ?>
