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
			
			$timeLimit = "WHERE MONTH(paymentdate) = $month AND YEAR(paymentdate) = $year";
			
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
		
		$timeLimit = "WHERE DATE(paymentdate) BETWEEN DATE('$fromDate') AND DATE('$untilDate')";
			
	}
	
	// Query to look up past payments
	$selectExpenses = "SELECT paymentid, paymentdate, userid, amountPaid, oldExpiry, newExpiry, paidTo, comment, operator, creditBefore, creditAfter FROM memberpayments $timeLimit ORDER by paymentdate DESC $limitVar";
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
	
		
		
	// Create month-by-month split
	$findStartDate = "SELECT paymentdate FROM memberpayments ORDER BY paymentdate ASC LIMIT 1";
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
		$startDate = date('01-m-Y', strtotime($row['paymentdate']));
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
	
	    $(document).ready(function() {
		    
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

$("#xllink").click(function(){

	  $("#mainTable").table2excel({
	    // exclude CSS class
	    exclude: ".noExl",
	    name: "Cuotas",
	    filename: "Cuotas" //do not include extension

	  });

	});
		    
		    
		    
			$('#cloneTable').width($('#mainTable').width());
			
			$.tablesorter.addParser({
			  id: 'dates',
			  is: function(s) { return false },
			  format: function(s) {
			    var dateArray = s.split('-');
			    return dateArray[2].substring(0,4) + dateArray[1] + dateArray[0];
			  },
			  type: 'numeric'
			});
			
EOD;

if ($_SESSION['bankPayments'] == 1) {
	
	$deleteDonationScript .= <<<EOD
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
						sorter: "dates"
					},
					6: {
						sorter: "dates"
					}
				}
			}); 
EOD;

} else {
	
	$deleteDonationScript .= <<<EOD
			$('#mainTable').tablesorter({
				usNumberFormat: true,
				headers: {
					0: {
						sorter: "dates"
					},
					3: {
						sorter: "currency"
					},
					4: {
						sorter: "dates"
					},
					5: {
						sorter: "dates"
					}
				}
			}); 
EOD;

}

	$deleteDonationScript .= <<<EOD
			
		});
		
		$(window).resize(function() {
			$('#cloneTable').width($('#mainTable').width());
		});
		
function delete_payment(paymentid) {
	if (confirm("{$lang['payment-deleteconfirm']}")) {
				window.location = "uTil/delete-payment.php?paymentid=" + paymentid + "&paymentscreen";
				}
}
EOD;
			
	pageStart($lang['memberfees'], NULL, $deleteDonationScript, "pmembership", NULL, $lang['memberfees'], $_SESSION['successMessage'], $_SESSION['errorMessage']);


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
	 <table class='default' id='mainTable'>
	  <thead>
	   <tr style='cursor: pointer;'>
	    <th style='position: relative;'><a href="#" id="xllink" onClick="$('#mainTable').tableExport({type:'excel',escape:'false'});" style='position: absolute; top: 0; left: 10px; margin-top: -66px;'><img src="images/excel-new.png" /></a><?php echo $lang['global-time']; ?></th>
  		<th><?php echo $lang['paid-by']; ?></th>
	    <th>#</th>
	    <th><?php echo $lang['global-member']; ?></th>
	    <th><?php echo $lang['global-amount']; ?></th>
	    <th><?php echo $lang['old-expiry']; ?></th>
	    <th><?php echo $lang['new-expiry']; ?></th>
	    <th><?php echo $lang['dispense-oldcredit']; ?></th>
	    <th><?php echo $lang['dispense-newcredit']; ?></th>
	    <th><?php echo $lang['operator']; ?></th>
	    <th class='noExl' colspan="2"></th>
	   </tr>
	  </thead>
	  <tbody>
	  
	  <?php

		while ($donation = $results->fetch()) {
	
	$paymentid = $donation['paymentid'];
	$paymentdate = date("d-m-Y H:i", strtotime($donation['paymentdate'] . "+$offsetSec seconds"));
	$amount = $donation['amountPaid'];
	$paidTo = $donation['paidTo'];
	
	$creditBefore = $donation['creditBefore'];
	$creditAfter = $donation['creditAfter'];
	$operatorID = $donation['operator'];

	if ($operatorID == 0) {
		$operator = '';
	} else {
		$operator = getOperator($operatorID);
	}

	
	if ($donation['comment'] != '') {
		
		$commentRead = "
		                <img src='images/comments.png' id='comment$paymentid' /><div id='helpBox$paymentid' class='helpBox'>{$donation['comment']}</div>
		                <script>
		                  	$('#comment$paymentid').on({
						 		'mouseover' : function() {
								 	$('#helpBox$paymentid').css('display', 'block');
						  		},
						  		'mouseout' : function() {
								 	$('#helpBox$paymentid').css('display', 'none');
							  	}
						  	});
						</script>
		                ";
		
	} else {
		
		$commentRead = "";
		
	}

	
	if ($donation['oldExpiry'] == NULL) {
		$oldExpiry = "<span class='white'>00-00-0000</span>";
	} else {
		$oldExpiry = date("d-m-Y", strtotime($donation['oldExpiry']));
	}
	$newExpiry = date("d-m-Y", strtotime($donation['newExpiry']));
	
	
	if ($paidTo == '2') {
		$paidTo = $lang['card'];
	} else if ($paidTo == '3') {
		$paidTo = $lang['global-credit'];
	} else if ($paidTo == '4') {
		$paidTo = "CashDro";
	} else if ($paidTo == '5') {
		$paidTo = $lang['changed-expiry'];
	} else {
		$paidTo = $lang['cash'];
	}
	
		$deleteOrNot = "<td class='noExl' style='text-align: center;'><a href='javascript:delete_payment($paymentid)'><img src='images/delete.png' height='15' title='{$lang['donation-deletedonation']}' /></a></td>";
		
	// Look up user details for showing profile on the Sales page
		$userDetails = "SELECT memberno, first_name, last_name FROM users WHERE user_id = {$donation['userid']}";
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

					
	$expense_row =	sprintf("
  	  <tr>
  	   <td>%s</td>
  	   <td>%s</td>
  	   <td class='left'>%s</td>
  	   <td class='left'>%s %s</td>
  	   <td class='right'>%0.00f &euro;</td>
  	   <td class='right'>%s</td>
  	   <td class='right'>%s</td>
  	   <td class='right'>%0.00f &euro;</td>
  	   <td class='right'>%0.00f &euro;</td>
  	   <td class='left'>%s</td>
  	   <td class='noExl' class='right relative'>$commentRead</td>
  	   %s
 	   
	  </tr>",
	  $paymentdate, $paidTo, $memberno, $first_name, $last_name, $amount, $oldExpiry, $newExpiry, $creditBefore, $creditAfter, $operator, $deleteOrNot
	  );
	  

  		
	  echo $expense_row;
	  
  }
?>

	 </tbody>
	 </table>

   
<?php displayFooter(); ?>
