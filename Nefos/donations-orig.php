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
			
			$timeLimit = "WHERE MONTH(donationTime) = $month AND YEAR(donationTime) = $year";
			
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
	$selectExpenses = "SELECT donationid, donationTime, userid, amount, creditBefore, creditAfter, donatedTo, operator, type, comment FROM donations $timeLimit ORDER by donationTime DESC $limitVar";

	$result2 = mysql_query($selectExpenses)
		or handleError($lang['error-donationload'],"Error loading expense from db: " . mysql_error());
		
		
	// Create month-by-month split
	$findStartDate = "SELECT donationTime FROM donations ORDER BY donationTime ASC LIMIT 1";
	
	$startResult = mysql_query($findStartDate)
		or handleError($lang['error-loadsales'],"Error loading sale from db: " . mysql_error());

	$row = mysql_fetch_array($startResult);
		$startDate = date('01-m-Y', strtotime($row['donationTime']));
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
	    name: "Donaciones",
	    filename: "Donaciones" //do not include extension

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
					5: {
						sorter: "currency"
					},
					6: {
						sorter: "currency"
					},
					7: {
						sorter: "currency"
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
EOD;

}

	$deleteDonationScript .= <<<EOD

		
			
		});
		
		$(window).resize(function() {
			$('#cloneTable').width($('#mainTable').width());
		});
		
function delete_donation(donationid,amount,userid) {
	if (confirm("{$lang['donation-deleteconfirm']}")) {
				window.location = "uTil/delete-donation.php?donationid=" + donationid + "&amount=" + amount + "&userid=" + userid + "&donscreen";
				}
}
EOD;
			
	pageStart($lang['global-donations'], NULL, $deleteDonationScript, "pmembership", NULL, $lang['global-donationscaps'], $_SESSION['successMessage'], $_SESSION['errorMessage']);


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
	 <table class='default' id='mainTable'>
	  <thead>
	   <tr style='cursor: pointer;'>
	    <th><?php echo $lang['global-time']; ?></th>
	    <th><?php echo $lang['global-type']; ?></th>
<?php if ($_SESSION['bankPayments'] == 1) { ?>
  		<th><?php echo $lang['donated-to']; ?></th>
<?php } ?>
	    <th>#</th>
	    <th><?php echo $lang['global-member']; ?></th>
	    <th><?php echo $lang['global-amount']; ?></th>
	    <th><?php echo $lang['donation-creditbefore']; ?></th>
	    <th><?php echo $lang['donation-creditafter']; ?></th>
	    <th><?php echo $lang['operator']; ?></th>
	    <th class='noExl' colspan="2"></th>
	   </tr>
	  </thead>
	  <tbody>
	  
	  <?php

while ($donation = mysql_fetch_array($result2)) {
	$donationid = $donation['donationid'];
	$donationTime = date("d-m-Y H:i", strtotime($donation['donationTime'] . "+$offsetSec seconds"));
	$amount = $donation['amount'];
	$creditBefore = $donation['creditBefore'];
	$creditAfter = $donation['creditAfter'];
	$donatedTo = $donation['donatedTo'];
	$user_id = $donation['userid'];
	$operatorID = $donation['operator'];
	$type = $donation['type'];
	
	if ($type == 1) {
		$operationType = $lang['donation-donation'];
	} else if ($type == 2) {
		$operationType = $lang['changed-credit'];
	} else if ($type == 3) {
		$operationType = $lang['global-edit'];
	}

	
	if ($operatorID == 0) {
		$operator = '';
	} else {
		$operator = getOperator($operatorID);
	}
	
	if ($donation['comment'] != '') {
		
		$commentRead = "
		                <img src='images/comments.png' id='comment$donationid' /><div id='helpBox$donationid' class='helpBox'>{$donation['comment']}</div>
		                <script>
		                  	$('#comment$donationid').on({
						 		'mouseover' : function() {
								 	$('#helpBox$donationid').css('display', 'block');
						  		},
						  		'mouseout' : function() {
								 	$('#helpBox$donationid').css('display', 'none');
							  	}
						  	});
						</script>
		                ";
		
	} else {
		
		$commentRead = "";
		
	}
	
	
	if ($donatedTo == '2') {
		$donatedTo = $lang['global-bank'];
	} else if ($donatedTo == '3') {
		$donatedTo = '';
	} else {
		$donatedTo = $lang['global-till'];
	}
	
		$deleteOrNot = "<td class='noExl' style='text-align: center;'><a href='edit-donation.php?donationid=$donationid&userid=$user_id'><img src='images/edit.png' height='15' /></a>&nbsp;&nbsp;<a href='javascript:delete_donation($donationid,$amount,$user_id)'><img src='images/delete.png' height='15' title='{$lang['donation-deletedonation']}' /></a></td>";
		
		// Look up user details for showing profile on the Sales page
		$userDetails = "SELECT memberno, first_name, last_name FROM users WHERE user_id = $user_id";
	
		$result = mysql_query($userDetails)
			or handleError($lang['error-userload'],"Error loading user: " . mysql_error());
	
		$row = mysql_fetch_array($result);
			$memberno = $row['memberno'];
			$first_name = $row['first_name'];
			$last_name = $row['last_name'];

		if ($_SESSION['bankPayments'] == 1) {
			
	$expense_row =	sprintf("
  	  <tr>
  	   <td class='left'>%s</td>
  	   <td class='left'>%s</td>
  	   <td class='left'>%s</td>
  	   <td class='left'>%s</td>
  	   <td class='left'>%s %s</td>
  	   <td class='right'>%0.02f &euro;</td>
  	   <td class='right'>%0.02f &euro;</td>
  	   <td class='right'>%0.02f &euro;</td>
  	   <td class='left'>%s</td>
  	   <td class='noExl' class='left relative'>$commentRead</td>
  	   %s
 	   
	  </tr>",
	  $donationTime, $operationType, $donatedTo, $memberno, $first_name, $last_name, $amount, $creditBefore, $creditAfter, $operator, $deleteOrNot
	  );
			
		} else {
		
	$expense_row =	sprintf("
  	  <tr>
  	   <td>%s</td>
  	   <td>%s</td>
  	   <td class='left'>%s</td>
  	   <td class='left'>%s %s</td>
  	   <td class='right'>%0.02f &euro;</td>
  	   <td class='right'>%0.02f &euro;</td>
  	   <td class='right'>%0.02f &euro;</td>
  	   <td class='left'>%s</td>
  	   <td class='noExl' class='left relative'>$commentRead</td>
  	   %s
 	   
	  </tr>",
	  $donationTime, $operationType, $memberno, $first_name, $last_name, $amount, $creditBefore, $creditAfter, $operator, $deleteOrNot
	  );
	
			
		}

	  echo $expense_row;
	  
  }
?>

	 </tbody>
	 </table>

   
<?php displayFooter(); ?>
