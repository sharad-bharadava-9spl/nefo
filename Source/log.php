<?php
	
	require_once 'cOnfig/connection.php';
	require_once 'cOnfig/view.php';
	require_once 'cOnfig/authenticate.php';
	require_once 'cOnfig/languages/common.php';
	
	session_start();
	$accessLevel = '1';
	
	// Authenticate & authorize
	authorizeUser($accessLevel);
	
	// Check if new Filter value was submitted, and assign query variable accordingly
	if (isset($_POST['filter'])) {
				
		$filterVar = $_POST['filter'];
		
		if ($filterVar == 100) {
			
			$limitVar = "LIMIT 100";
			$optionList = "<option value='$filterVar'>{$lang['last']} 100</option>
			<option value='250'>{$lang['last']} 250</option>
			<option value='500'>{$lang['last']} 500</option>";
			
		} else if ($filterVar == 250) {
			
			$limitVar = "LIMIT 250";
			$optionList = "<option value='$filterVar'>{$lang['last']} 250</option>
			<option value='100'>{$lang['last']} 100</option>
			<option value='500'>{$lang['last']} 500</option>";
			
		} else if ($filterVar == 500) {
			
			$limitVar = "LIMIT 500";
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
		
		$timeLimit = "WHERE DATE(logtime) BETWEEN DATE('$fromDate') AND DATE('$untilDate')";
		$limitVar = "";
			
	}
	
	
	// Query to look up log items
	$selectLog = "SELECT id, logtype, logtime, user_id, operator, amount, oldCredit, newCredit, oldExpiry, newExpiry, comment FROM log $timeLimit ORDER by logtime DESC $limitVar";
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
	

	// Create month-by-month split
	$findStartDate = "SELECT logtime FROM log ORDER BY logtime ASC LIMIT 1";
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
		$startDate = date('01-m-Y', strtotime($row['logtime']));
		$endDate = date('01-m-Y');
		$endDateShort = date('m-Y', strtotime($endDate));
		
		
	if ($endDateShort != $filterVar) {
		$optionList .= "<option value='$endDateShort'>$endDateShort</option>";
	}
	
	$genDateFull = date('01-m-Y', strtotime($endDate));
	$genDate = date('m-Y', strtotime($genDateFull));
	
	while (strtotime($genDateFull) > strtotime($startDate)) {
		
		$genDateFull = date('01-m-Y', strtotime("$genDateFull - 1 month"));
		$genDate = date('m-Y', strtotime($genDateFull));
		
		// Exclude option if already selected
		if ($genDate != $filterVar) {
			$optionList .= "<option value='$genDate'>$genDate</option>";
		}

	}
	
	$deleteDonationScript = <<<EOD
	
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
	  
	    $(document).ready(function() {
		    
		    
$("#xllink").click(function(){

	  $("#mainTable").table2excel({
	    // exclude CSS class
	    exclude: ".noExl",
	    name: "Log",
	    filename: "Log" //do not include extension

	  });

	});
		    
		    
		    
			$('#cloneTable').width($('#mainTable').width());
			
			
			$('#mainTable').tablesorter({
				usNumberFormat: true,
				headers: {
					0: {
						sorter: "dates"
					},
					4: {
						sorter: "currency"
					},
					5: {
						sorter: "currency"
					},
					6: {
						sorter: "currency"
					}
				}
			}); 
		
		$(window).resize(function() {
			$('#cloneTable').width($('#mainTable').width());
		});
		});
		
EOD;


	pageStart($lang['log'], NULL, $deleteDonationScript, "pexpenses", "admin", $lang['log'], $_SESSION['successMessage'], $_SESSION['errorMessage']);
	
	
?>
	 <table class='default' id='cloneTable' style='text-align: left;'>
      <tr class='nonhover'>
       <td colspan='13' style='border-bottom: 0;'>
         <a href="#" id="xllink" onClick="$('#mainTable').tableExport({type:'excel',escape:'false'});"><img src="images/excel.png" style='margin: 0 0 -5px 8px;'/></a><br /><br />
		<div style='display: inline-block; border: 2px solid #5aa242; padding: 10px;'>
		&nbsp;<strong><?php echo $lang['filter']; ?>:</strong><br /> 
        <form action='' method='POST' style='margin-top: 3px;'>
	     <select id='filter' name='filter' onchange='this.form.submit()'>
	      <?php echo $optionList; ?>
		 </select>
        </form>
        <form action='' method='POST'>
<?php
	if (isset($_POST['fromDate'])) {
		
		echo <<<EOD
		 <input type="text" id="datepicker" name="fromDate" autocomplete="nope" class="sixDigit" value="{$_POST['fromDate']}" />
		 <input type="text" id="datepicker2" name="untilDate" autocomplete="nope" class="sixDigit" value="{$_POST['untilDate']}" onchange='this.form.submit()' />
		 <button type="submit" style='display: inline-block; width: 40px; height: 27px;'>OK</button>
EOD;
		
	} else {
		
		echo <<<EOD
		 <input type="text" id="datepicker" name="fromDate" autocomplete="nope" class="sixDigit" placeholder="{$lang['from-date']}" />
		 <input type="text" id="datepicker2" name="untilDate" autocomplete="nope" class="sixDigit" placeholder="{$lang['to-date']}" onchange='this.form.submit()' />
		 <button type="submit" style='display: inline-block; width: 40px; height: 27px;'>OK</button>
EOD;

	}
?>
        </form>
        </div>
       </td>
      </tr>
     </table>
	 <table class="default" id='mainTable'>
	  <thead>
	   <tr style='cursor: pointer;'>
	    <th><?php echo $lang['global-time']; ?></th>
	    <th><?php echo $lang['action']; ?></th>
	    <th><?php echo $lang['operator']; ?></th>
	    <th><?php echo $lang['global-member']; ?></th>
	    <th><?php echo $lang['global-amount']; ?></th>
	    <th><?php echo $lang['donation-creditbefore']; ?></th>
	    <th><?php echo $lang['donation-creditafter']; ?></th>
	    <th><?php echo $lang['old-expiry']; ?></th>
	    <th><?php echo $lang['new-expiry']; ?></th>
	    <th></th>
	   </tr>
	  </thead>
	  <tbody>
	  
	  <?php

		while ($logItem = $results->fetch()) {
	
	
	$id = $logItem['id'];
	$logtype = $logItem['logtype'];
	$formattedDate = date("d M H:i", strtotime($logItem['logtime'] . "+$offsetSec seconds"));
	$user_id = $logItem['user_id'];
	$operator = $logItem['operator'];
	$operatorID = $logItem['operator'];
	
	if ($logItem['comment'] != '') {
		
		$commentRead = "
		                <img src='images/comments.png' id='comment$id' /><div id='helpBox$id' class='helpBox'>{$logItem['comment']}</div>
		                <script>
		                  	$('#comment$id').on({
						 		'mouseover' : function() {
								 	$('#helpBox$id').css('display', 'block');
						  		},
						  		'mouseout' : function() {
								 	$('#helpBox$id').css('display', 'none');
							  	}
						  	});
						</script>
		                ";
		
	} else {
		
		$commentRead = "";
		
	}

	

	
	if ($logItem['amount'] == 0) {
		$amount = '';
	} else {
		$amount = number_format($logItem['amount'],2) . "<span class='smallerfont'>&euro;</span>";
	}
	
	if ($logItem['oldCredit'] > 0 || $logItem['newCredit'] > 0) {
		$oldCredit = number_format($logItem['oldCredit'],2) . "<span class='smallerfont'>&euro;</span>";
		if ($logtype == 14) {
			$newCredit = '';			
		} else {
			$newCredit = number_format($logItem['newCredit'],2) . "<span class='smallerfont'>&euro;</span>";
		}
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
  	   <td class='left clickableRow' href='worker-log.php?operator=$operatorID'>%s</td>
  	   <td class='left clickableRow' href='member-log.php?user_id=$user_id'>%s</td>
  	   <td style='text-align: right;'>%s</td>
  	   <td style='text-align: right;'>%s</td>
  	   <td style='text-align: right;'>%s</td>
  	   <td class='centered'>%s</td>
  	   <td class='centered'>%s</td>
  	   <td class='centered'>$commentRead</td>
	  </tr>",
	  $formattedDate, $logType, $operator, $member, $amount, $oldCredit, $newCredit, $oldExpiry, $newExpiry
	  );
	  echo $expense_row;
  }
?>

	 </tbody>
	 </table>
	 
	 
<?php  displayFooter(); ?>
