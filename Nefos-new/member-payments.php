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

	
	// Query to look up past payments
	$selectExpenses = "SELECT paymentid, paymentdate, userid, amountPaid, oldExpiry, newExpiry, paidTo, comment FROM memberpayments $timeLimit ORDER by paymentdate DESC $limitVar";

	$result2 = mysql_query($selectExpenses)
		or handleError($lang['error-donationload'],"Error loading expense from db: " . mysql_error());
		
		
	// Create month-by-month split
	$findStartDate = "SELECT paymentdate FROM memberpayments ORDER BY paymentdate ASC LIMIT 1";
	
	$startResult = mysql_query($findStartDate)
		or handleError($lang['error-loadsales'],"Error loading sale from db: " . mysql_error());

	$row = mysql_fetch_array($startResult);
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
	 <table class='default' id='cloneTable'>
      <tr class='nonhover'>
       <td colspan='13' style='border-bottom: 0;'>
        <form action='' method='POST'>
	     <select id='filter' name='filter' onchange='this.form.submit()'>
	      <?php echo $optionList; ?>
		 </select>
         <a href="#" id="xllink" onClick="$('#mainTable').tableExport({type:'excel',escape:'false'});"><img src="images/excel.png" style='margin: 0 0 -5px 8px;'/></a>
        </form>
       </td>
      </tr>
     </table>
<br />
	  
	  <?php
	  
	$y = 0;

while ($donation = mysql_fetch_array($result2)) {
	
	
	$dTime = date("d-m-Y", strtotime($donation['paymentdate']));
	$dTimeSQL = date("Y-m-d", strtotime('-1 day', strtotime($donation['paymentdate'])));
	
	if ($dTime != $currDate) {
		
		$nDate = date("Y-m-d", strtotime($currDate));
		
		
		if ($y > 0) {
			// Query total for THIS date
			$donationTotal = "SELECT SUM(amountPaid) FROM memberpayments WHERE DATE(paymentdate) = DATE('$nDate')";
						
			
			$totalResult = mysql_query($donationTotal)
				or handleError($lang['error-donationload'],"Error loading expense from db: " . mysql_error());	
			
			$amountRow = mysql_fetch_array($totalResult);
				$amountToday = $amountRow['SUM(amountPaid)'];
			
			echo "<tr><td colspan='4'><strong>TOTAL:</strong></td><td style='text-align: right;'><strong>$amountToday &euro;</strong></td><td colspan='3'></td></tr>";
		}
		
	  	echo "</tbody></table>";
		echo "<br /><br /><h3 class='title'>{$dTime}</h3>";
		
		if ($_SESSION['bankPayments'] == 1) {
			echo <<<EOD
	 <table class='default' id='mainTable'>
	  <thead>
	   <tr style='cursor: pointer;'>
	    <th>{$lang['global-time']}</th>
  		<th>{$lang['paid-by']}</th>
	    <th>#</th>
	    <th>{$lang['global-member']}</th>
	    <th>{$lang['global-amount']}</th>
	    <th>{$lang['old-expiry']}</th>
	    <th>{$lang['new-expiry']}</th>
	    <th class='noExl' colspan="2"></th>
	   </tr>
	  </thead>
	  <tbody>
EOD;
		} else {
			echo <<<EOD
	 <table class='default' id='mainTable'>
	  <thead>
	   <tr style='cursor: pointer;'>
	    <th>{$lang['global-time']}</th>
  		<th>{$lang['paid-by']}</th>
	    <th>#</th>
	    <th>{$lang['global-member']}</th>
	    <th>{$lang['global-amount']}</th>
	    <th>{$lang['old-expiry']}</th>
	    <th>{$lang['new-expiry']}</th>
	    <th class='noExl' colspan="2"></th>
	   </tr>
	  </thead>
	  <tbody>
EOD;
			
		}
	}

	
	$paymentid = $donation['paymentid'];
	$paymentdate = date("d-m-Y H:i", strtotime($donation['paymentdate'] . "+$offsetSec seconds"));
	$amount = $donation['amountPaid'];
	$paidTo = $donation['paidTo'];
	
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
	} else {
		$paidTo = $lang['cash'];
	}
	
		$deleteOrNot = "<td class='noExl' style='text-align: center;'><a href='javascript:delete_payment($paymentid)'><img src='images/delete.png' height='15' title='{$lang['donation-deletedonation']}' /></a></td>";
		
	// Look up user details for showing profile on the Sales page
		$userDetails = "SELECT memberno, first_name, last_name FROM users WHERE user_id = {$donation['userid']}";
	
		$result = mysql_query($userDetails)
			or handleError($lang['error-userload'],"Error loading user: " . mysql_error());
	
		$row = mysql_fetch_array($result);
			$memberno = $row['memberno'];
			$first_name = $row['first_name'];
			$last_name = $row['last_name'];

		
		if ($_SESSION['bankPayments'] == 1) {
			
	$expense_row =	sprintf("
  	  <tr>
  	   <td>%s</td>
  	   <td>%s</td>
  	   <td class='left'>%s</td>
  	   <td class='left'>%s %s</td>
  	   <td class='right'>%0.00f &euro;</td>
  	   <td class='right'>%s</td>
  	   <td class='right'>%s</td>
  	   <td class='noExl' class='right relative'>$commentRead</td>
  	   %s
 	   
	  </tr>",
	  $paymentdate, $paidTo, $memberno, $first_name, $last_name, $amount, $oldExpiry, $newExpiry, $deleteOrNot
	  );
	  
  		} else {
	  		
	$expense_row =	sprintf("
  	  <tr>
  	   <td>%s</td>
  	   <td class='left'>%s</td>
  	   <td class='left'>%s %s</td>
  	   <td class='right'>%0.00f &euro;</td>
  	   <td class='right'>%s</td>
  	   <td class='right'>%s</td>
  	   <td class='noExl' class='right relative'>$commentRead</td>
  	   %s
 	   
	  </tr>",
	  $paymentdate, $memberno, $first_name, $last_name, $amount, $oldExpiry, $newExpiry, $deleteOrNot
	  );
	  
  		}
  		
	  echo $expense_row;
	  
	if ($dTime != $currDate) {
	  		$currDate =  date("d-m-Y", strtotime($donation['paymentdate']));
	}

	  $y++;

	  
  }
?>

	 </tbody>
	 </table>

   
<?php displayFooter(); ?>
