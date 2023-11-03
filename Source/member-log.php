<?php
	
	require_once 'cOnfig/connection.php';
	require_once 'cOnfig/view.php';
	require_once 'cOnfig/authenticate.php';
	require_once 'cOnfig/languages/common.php';
	
	session_start();
	$accessLevel = '1';
	
	// Authenticate & authorize
	authorizeUser($accessLevel);
	
	if (isset($_POST['user_id'])) {
		$user_id = $_POST['user_id'];
	} else if (isset($_GET['user_id'])) {
		$user_id = $_GET['user_id'];
	} else {
		handleError($lang['error-nouserid'],"");
	}
	
	// Check if new Filter value was submitted, and assign query variable accordingly
	if (isset($_POST['filter'])) {
				
		$filterVar = $_POST['filter'];
		
		if ($filterVar == 100) {
			
			$limitVar = "LIMIT 100";
			$timeLimit = "WHERE";
			$optionList = "<option value='$filterVar'>{$lang['last']} 100</option>
			<option value='250'>{$lang['last']} 250</option>
			<option value='500'>{$lang['last']} 500</option>";
			
		} else if ($filterVar == 250) {
			
			$limitVar = "LIMIT 250";
			$timeLimit = "WHERE";
			$optionList = "<option value='$filterVar'>{$lang['last']} 250</option>
			<option value='100'>{$lang['last']} 100</option>
			<option value='500'>{$lang['last']} 500</option>";
			
		} else if ($filterVar == 500) {
			
			$limitVar = "LIMIT 500";
			$timeLimit = "WHERE";
			$optionList = "<option value='$filterVar'>{$lang['last']} 500</option>
			<option value='100'>{$lang['last']} 100</option>
			<option value='250'>{$lang['last']} 250</option>";
			
		} else {
			
			// Grab month and year number
			$month = substr($filterVar, 0, strrpos($filterVar, '-'));	
			$year = substr($filterVar, strrpos($filterVar, '-') + 1);
			
			$timeLimit = "WHERE MONTH(logtime) = $month AND YEAR(logtime) = $year";
			
			$optionList = "<option value='filterVar'>$filterVar</option>
				<option value='100'>{$lang['last']} 100</option>
				<option value='250'>{$lang['last']} 250</option>
				<option value='500'>{$lang['last']} 500</option>";	
					
		}
			
	} else {
		
		$limitVar = "LIMIT 100";
		$timeLimit = "WHERE";
		
		$optionList = "<option value=''>{$lang['filter']}</option>
			<option value='100'>{$lang['last']} 100</option>
			<option value='250'>{$lang['last']} 250</option>
			<option value='500'>{$lang['last']} 500</option>";		
	}
	
	// Check if 'entre fechas' was utilised
	if (isset($_POST['untilDate'])) {
		
		$limitVar = "";
		
		$fromDate = date("Y-m-d", strtotime($_POST['fromDate']));
		$untilDate = date("Y-m-d", strtotime($_POST['untilDate']));
		
		$timeLimit = "WHERE DATE(logtime) BETWEEN DATE('$fromDate') AND DATE('$untilDate') AND";
		$limitVar = "";
			
	}

	
	// Query to look up log items
	$selectLog = "SELECT id, logtype, logtime, operator, amount, oldCredit, newCredit, oldExpiry, newExpiry FROM log $timeLimit user_id = $user_id ORDER by logtime DESC $limitVar";
		try
		{
			$results = $pdo3->prepare("$selectLog");
			$results->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
	$deleteExpenseScript = <<<EOD
	
	  $( function() {
	    $( "#datepicker" ).datepicker({
			dateFormat: "dd-mm-yy"
	    });
	  });
	  $( function() {
	    $( "#datepicker2" ).datepicker({
			dateFormat: "dd-mm-yy"
	    });
	  });	    
EOD;
	
	pageStart($lang['member-log'], NULL, $deleteExpenseScript, "pexpenses", "admin", $lang['member-log'], $_SESSION['successMessage'], $_SESSION['errorMessage']);
	
	
?>
<center>
<div id='filterbox'>
 <div id='mainboxheader'>
 <?php echo $lang['filter']; ?>
 </div>
 <div class='boxcontent' style='padding-bottom: 0;'>
  <form action='' method='POST' style='margin-top: 3px;'>
   <select id='filter' name='filter' class='defaultinput-no-margin' style='width: 242px;' onchange='this.form.submit()'>
    <?php echo $optionList; ?>
   </select>
  </form><br />
  <form action='' method='POST'>
<?php
	if (isset($_POST['fromDate'])) {
		
		echo <<<EOD
		 <input type="text" id="datepicker" name="fromDate" autocomplete="nope" class="defaultinput-no-margin sixDigit" value="{$_POST['fromDate']}" />
		 <input type="text" id="datepicker2" name="untilDate" autocomplete="nope" class="defaultinput-no-margin sixDigit" value="{$_POST['untilDate']}" onchange='this.form.submit()' />
		 <br /><button type="submit" class='cta2'>{$lang['filter']}</button>
EOD;
		
	} else {
		
		echo <<<EOD
		 <input type="text" id="datepicker" name="fromDate" autocomplete="nope" class="defaultinput-no-margin sixDigit" placeholder="Desde fecha" />
		 <input type="text" id="datepicker2" name="untilDate" autocomplete="nope" class="defaultinput-no-margin sixDigit" placeholder="Hasta fecha" onchange='this.form.submit()' />
		 <br /><button type="submit" class='cta2'>{$lang['filter']}</button>
EOD;

	}
?>
        </form>
 </div>
</div>
</center>
<br />
<center><a href="profile.php?user_id=<?php echo $user_id; ?>" class='cta1nm'>&laquo; <?php echo $lang['title-profile']; ?> &laquo;</a></center>
<br />
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

		while ($logItem = $results->fetch()) {
	
	$id = $logItem['id'];
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
		try
		{
			$result = $pdo3->prepare("$selectLogType");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
			$logType = $row['namees'];
			$description = $row['descriptiones'];
						
	} else {
		
		$selectLogType = "SELECT nameen, descriptionen FROM logtypes WHERE id = $logtype";
		try
		{
			$result = $pdo3->prepare("$selectLogType");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
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
