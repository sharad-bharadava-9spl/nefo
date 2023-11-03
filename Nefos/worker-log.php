<?php
	
	require_once 'cOnfig/connection.php';
	require_once 'cOnfig/view.php';
	require_once 'cOnfig/authenticate.php';
	require_once 'cOnfig/languages/common.php';
	
	session_start();
	$accessLevel = '1';
	
	// Authenticate & authorize
	authorizeUser($accessLevel);
	
	if (isset($_POST['operator'])) {
		$operator = $_POST['operator'];
	} else if (isset($_GET['operator'])) {
		$operator = $_GET['operator'];
	} else {
		handleError($lang['error-nouserid'],"");
	}
	
	// Query to look up log items
	$selectLog = "SELECT id, logtype, logtime, user_id, operator, amount, oldCredit, newCredit, oldExpiry, newExpiry FROM log WHERE operator = $operator ORDER by logtime DESC LIMIT 500";

	$result = mysql_query($selectLog)
		or handleError($lang['error-expensesload'],"Error loading expense from db: " . mysql_error());

	
	pageStart($lang['worker-log'], NULL, $deleteExpenseScript, "pexpenses", "admin", $lang['worker-log'], $_SESSION['successMessage'], $_SESSION['errorMessage']);
	
	
?>

	 <table class="default">
	  <thead>
	   <tr>
	    <th><?php echo $lang['global-time']; ?></th>
	    <th><?php echo $lang['action']; ?></th>
	    <th><?php echo $lang['operator']; ?></th>
	    <th><?php echo $lang['global-member']; ?></th>
	    <th><?php echo $lang['global-amount']; ?></th>
	    <th><?php echo $lang['donation-creditbefore']; ?></th>
	    <th><?php echo $lang['donation-creditafter']; ?></th>
	    <th><?php echo $lang['old-expiry']; ?></th>
	    <th><?php echo $lang['new-expiry']; ?></th>
	   </tr>
	  </thead>
	  <tbody>
	  
	  <?php

while ($logItem = mysql_fetch_array($result)) {
	
	$id = $logItem['id'];
	$user_id = $logItem['user_id'];
	$logtype = $logItem['logtype'];
	$formattedDate = date("d M H:i", strtotime($logItem['logtime'] . "+$offsetSec seconds"));
	$operator = $logItem['operator'];
	
	if ($logItem['amount'] == 0) {
		$amount = '';
	} else {
		$amount = number_format($logItem['amount'],2) . "<span class='smallerfont'>&euro;</span>";
	}
	
	if ($logItem['oldCredit'] > 0 || $logItem['newCredit'] > 0) {
		$oldCredit = number_format($logItem['oldCredit'],2) . "<span class='smallerfont'>&euro;</span>";
		$newCredit = number_format($logItem['newCredit'],2) . "<span class='smallerfont'>&euro;</span>";
	} else {
		$oldCredit = '';
		$newCredit = '';
	}
	
	if ($logItem['newExpiry'] != '') {
		$oldExpiry = date('d M Y', strtotime($logItem['oldExpiry']));
		$newExpiry = date('d M Y', strtotime($logItem['newExpiry']));
	} else {
		$oldExpiry = '';
		$newExpiry = '';
	}
	
	$member = getUser($user_id);
	$operator = getUser($operator);
	
	// Look up logtype
	if ($_SESSION['lang'] == 'es') {
		
		$selectLogType = "SELECT namees, descriptiones FROM logtypes WHERE id = $logtype";
		
		$logTypeResult = mysql_query($selectLogType)
			or handleError($lang['error-expensesload'],"Error loading expense from db: " . mysql_error());
			
		$row = mysql_fetch_array($logTypeResult);
			$logType = $row['namees'];
			$description = $row['descriptiones'];
						
	} else {
		
		$selectLogType = "SELECT nameen, descriptionen FROM logtypes WHERE id = $logtype";
		
		$logTypeResult = mysql_query($selectLogType)
			or handleError($lang['error-expensesload'],"Error loading expense from db: " . mysql_error());
			
		$row = mysql_fetch_array($logTypeResult);
			$logType = $row['nameen'];
			$description = $row['descriptionen'];
			
	}
	

	
	
	$expense_row =	sprintf("
  	  <tr>
  	   <td class='left'>%s</td>
  	   <td class='left'>%s</td>
  	   <td class='left'>%s</td>
  	   <td class='left'>%s</td>
  	   <td style='text-align: right;'>%s</td>
  	   <td style='text-align: right;'>%s</td>
  	   <td style='text-align: right;'>%s</td>
  	   <td class='centered'>%s</td>
  	   <td class='centered'>%s</td>
	  </tr>",
	  $formattedDate, $logType, $operator, $member, $amount, $oldCredit, $newCredit, $oldExpiry, $newExpiry
	  );
	  echo $expense_row;
  }
?>

	 </tbody>
	 </table>
	 
	 
<?php  displayFooter(); ?>
