<?php
	ob_start();
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
			
		    $timeLimit = "AND MONTH(donationTime) = $month AND YEAR(donationTime) = $year";
			
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
	if (!empty($_POST['untilDate'])) {
		
		$limitVar = "";
		
		$fromDate = date("Y-m-d", strtotime($_POST['fromDate']));
		$untilDate = date("Y-m-d", strtotime($_POST['untilDate']));
		
		$timeLimit = "AND DATE(donationTime) BETWEEN DATE('$fromDate') AND DATE('$untilDate')";
			
	}
	// usergroup filter
	$selectedUsergroup = "1,2,3";
	if(isset($_POST['submitted'])){
		$firstSelect = 'false';
		//  code to filter the sales from usergroups
		if(isset($_POST['cashBox'])){
			$selectedUserArr = $_POST['cashBox'];
			$selectedUsergroup = implode(",", $selectedUserArr);
			$getUsers = "SELECT user_id FROM users WHERE userGroup IN ($selectedUsergroup)";
			$result = $pdo3->prepare("$getUsers");
			$result->execute();
			while($user_ids = $result->fetch()){
				$userArr[] = $user_ids['user_id'];
			}
			if(!empty($userArr)){
				array_push($userArr, 999999);
			}
			$selectedUsers = implode(',',$userArr);
			if(empty($selectedUsers) || $selectedUsers ==''){
				$selectedUsers = -1;
			}
			$user_limit = "AND operator IN ($selectedUsers)";
		}else{
			$user_limit = 'AND operator IN (0)';
		}
	}else{
		 $firstSelect = 'true';
		  $getUsers = "SELECT user_id FROM users WHERE userGroup IN ($selectedUsergroup)";
			$result = $pdo3->prepare("$getUsers");
			$result->execute();
			while($user_ids = $result->fetch()){
				$userArr[] = $user_ids['user_id'];
			}
			array_push($userArr, 999999);
		    $selectedUsers = implode(',',$userArr);
		    if(empty($selectedUsers) || $selectedUsers == ''){
				$selectedUsers = -1;		    	
		    }else{
		    	$selectedUsers .= ',0';
		    }

		   $user_limit = "AND operator IN ($selectedUsers)";
	}
	// Query to look up past payments
	 $selectExpenses = "SELECT donationid, donationTime, userid, amount, creditBefore, creditAfter, donatedTo, operator, type, comment FROM donations WHERE 1 $timeLimit $user_limit ORDER by donationTime DESC $limitVar";
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
	$findStartDate = "SELECT donationTime FROM donations ORDER BY donationTime ASC LIMIT 1";
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
		    
	  $( function() {
	    $( "#datepicker" ).datepicker({
			dateFormat: "dd-mm-yy"
	    });	    
	    $( "#exceldatepicker" ).datepicker({
			dateFormat: "dd-mm-yy"
	    });
	  });
	  $( function() {
	    $( "#datepicker2" ).datepicker({
			dateFormat: "dd-mm-yy"
	    });	    
	    $( "#exceldatepicker2" ).datepicker({
			dateFormat: "dd-mm-yy"
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
  </form>
  <br>
   <span style="margin-top: 27px; font-weight: bold;">OR</span>
   <br>
  <form action='' method='POST'>
		<?php
			if (isset($_POST['fromDate'])) {
				
				echo <<<EOD
				 <input type="text" id="datepicker" name="fromDate" autocomplete="nope" class="defaultinput-no-margin sixDigit" value="{$_POST['fromDate']}" />
				 <input type="text" id="datepicker2" name="untilDate" autocomplete="nope" class="defaultinput-no-margin sixDigit" value="{$_POST['untilDate']}" onchange='this.form.submit()' />
		EOD;
				
			} else {
				
				echo <<<EOD
				 <input type="text" id="datepicker" name="fromDate" autocomplete="nope" class="defaultinput-no-margin sixDigit" placeholder="Desde fecha" />
				 <input type="text" id="datepicker2" name="untilDate" autocomplete="nope" class="defaultinput-no-margin sixDigit" placeholder="Hasta fecha" onchange='this.form.submit()' />
		EOD;
			}
		?>
		  <br>
		  <br>
     
		<div style='display: inline-block; text-align: left; float: left; padding-right: 32px;'>
			&nbsp;<strong>Workers:</strong><br /> <br /> 
			<?php  
				if($firstSelect == 'true'){
			 ?>
			<div class='fakeboxholder firstbox'>	
			 <label class="control">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
			  Administrador
			  <input type="checkbox" name="cashBox[]" id="accept1" value='1' checked />
			  <div class="fakebox"></div>
			 </label>
			</div>
			<br />
			<br />
			<div class='fakeboxholder'>	
			 <label class="control">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
			  Trabajador
			  <input type="checkbox" name="cashBox[]" id="accept2" value='2' checked />
			  <div class="fakebox"></div>
			 </label>
			</div>
			<br />
			<br />
			<div class='fakeboxholder'>	
			 <label class="control">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
			  Voluntario
			  <input type="checkbox" name="cashBox[]" id="accept3" value='3' checked />
			  <div class="fakebox"></div>
			 </label>
			</div>
			<br />
			<br />
			<?php }else{ ?>
			<div class='fakeboxholder firstbox'>	
			 <label class="control">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
			  Administrador
			  <input type="checkbox" name="cashBox[]" id="accept1" value='1' <?php if(in_array(1, $selectedUserArr)){ echo "checked"; } ?> />
			  <div class="fakebox"></div>
			 </label>
			</div>
			<br />
			<br />
			<div class='fakeboxholder'>	
			 <label class="control">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
			  Trabajador
			  <input type="checkbox" name="cashBox[]" id="accept2" value='2' <?php if(in_array(2, $selectedUserArr)){ echo "checked"; } ?> />
			  <div class="fakebox"></div>
			 </label>
			</div>
			<br />
			<br />
			<div class='fakeboxholder'>	
			 <label class="control">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
			  Voluntario
			  <input type="checkbox" name="cashBox[]" id="accept3" value='3' <?php if(in_array(3, $selectedUserArr)){ echo "checked"; } ?> />
			  <div class="fakebox"></div>
			 </label>
			</div>
			<br />
			<br />
			<?php } ?>	
			 <input type="hidden" name="submitted" value="1">
				<button type="submit" class='cta2'><?php echo $lang['filter'] ?></button>
	      
	        </div>
     </form>
 </div>
</div>
</center>
<br />
	 <table class='default' id='mainTable'>
	  <thead>
	   <tr style='cursor: pointer;'>
	    <th style='position: relative;'><a href="#" id="openCOnfirm"  style='position: absolute; top: 0; left: 10px; margin-top: -66px;'><img src="images/excel-new.png" /></a><?php echo $lang['global-time']; ?></th>
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
	  $startIndex = 2;
		while ($donation = $results->fetch()) {
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
	} else if ($donatedTo == '4') {
		$donatedTo = 'CashDro';
	} else {
		$donatedTo = $lang['global-till'];
	}

		if ($_SESSION['domain'] == 'granvalle') {
			$deleteOrNot = "<td class='noExl' style='text-align: center;'></td>";
		} else if ($type != 2) {
			$deleteOrNot = "<td class='noExl' style='text-align: center;'><!--<a href='edit-donation.php?donationid=$donationid&userid=$user_id'><img src='images/edit.png' height='15' /></a>-->&nbsp;&nbsp;<a href='javascript:delete_donation($donationid,$amount,$user_id)'><img src='images/delete.png' height='15' title='{$lang['donation-deletedonation']}' /></a></td>";
		} else {
			$deleteOrNot = "<td class='noExl' style='text-align: center;'></td>";
		}
		
		// Look up user details for showing profile on the Sales page
		$userDetails = "SELECT memberno, first_name, last_name FROM users WHERE user_id = $user_id";
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
		if ($_SESSION['bankPayments'] == 1) {
			
	$expense_row =	sprintf("
  	  <tr>
  	   <td class='left clickableRow' href='profile.php?user_id=$user_id'>%s</td>
  	   <td class='left clickableRow' href='profile.php?user_id=$user_id'>%s</td>
  	   <td class='left clickableRow' href='profile.php?user_id=$user_id'>%s</td>
  	   <td class='left clickableRow' href='profile.php?user_id=$user_id'>%s</td>
  	   <td class='left clickableRow' href='profile.php?user_id=$user_id'>%s %s</td>
  	   <td class='right clickableRow' href='profile.php?user_id=$user_id'>%0.02f {$_SESSION['currencyoperator']}</td>
  	   <td class='right clickableRow' href='profile.php?user_id=$user_id'>%0.02f {$_SESSION['currencyoperator']}</td>
  	   <td class='right clickableRow' href='profile.php?user_id=$user_id'>%0.02f {$_SESSION['currencyoperator']}</td>
  	   <td class='left clickableRow' href='profile.php?user_id=$user_id'>%s</td>
  	   <td class='noExl clickableRow' href='profile.php?user_id=$user_id'><span class='relativeitem'>$commentRead</span></td>
  	   %s
 	   
	  </tr>",
	  $donationTime, $operationType, $donatedTo, $memberno, $first_name, $last_name, $amount, $creditBefore, $creditAfter, $operator, $deleteOrNot
	  );
			
		} else {
		
	$expense_row =	sprintf("
  	  <tr>
  	   <td>%s</td>
  	   <td>%s</td>
  	   <td class='left clickableRow' href='profile.php?user_id=$user_id'>%s</td>
  	   <td class='left clickableRow' href='profile.php?user_id=$user_id'>%s %s</td>
  	   <td class='right clickableRow' href='profile.php?user_id=$user_id'>%0.02f {$_SESSION['currencyoperator']}</td>
  	   <td class='right clickableRow' href='profile.php?user_id=$user_id'>%0.02f {$_SESSION['currencyoperator']}</td>
  	   <td class='right clickableRow' href='profile.php?user_id=$user_id'>%0.02f {$_SESSION['currencyoperator']}</td>
  	   <td class='left clickableRow' href='profile.php?user_id=$user_id'>%s</td>
  	   <td class='noExl clickableRow' href='profile.php?user_id=$user_id'><span class='relativeitem'>$commentRead</span></td>
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
   	 	 <div  class="actionbox-npr" id = "dialog-3" title = "<?php echo $lang['global-donationscaps'] ?>">
			
			<div class='boxcomtemt'>
				<p>Export excel between time ranges</p><br>
				<input type="text" id="exceldatepicker" name="fromDate" autocomplete="nope" class="sixDigit defaultinput" placeholder="<?php echo $lang['from-date'] ?>" />
				 <input type="text" id="exceldatepicker2" name="untilDate" autocomplete="nope" class="sixDigit defaultinput" placeholder="<?php echo $lang['to-date'] ?>"/>
 				<button class='cta1' id="fullList">Ok</button>
 			
			</div>
		</div> 
<?php displayFooter(); ?>
<script type="text/javascript">

	$( "#dialog-3" ).dialog({
	    autoOpen: false, 
	    hide: "puff",
	    show : "slide",
	     position: {
	       my: "top top",
	       at: "top top"
	    }      
	 });
	 $( "#openCOnfirm" ).click(function() {
	    $( "#dialog-3" ).dialog( "open" );
	 });

	 $("#fullList").click(function(){
	    $("#load").show();
	    $( "#dialog-3" ).dialog( "close" );

	    var fromDate = $("#exceldatepicker").val();
	    var untilDate = $("#exceldatepicker2").val();
	    var url = 'donations-report.php?fromDate='+fromDate+'&untilDate='+untilDate+'&count=0&totalCount=0';
	    window.open(url, "Donations Report","height=300,width=300,modal=yes,alwaysRaised=yes");
	    setTimeout(function () {
	        $("#load").hide();
	    }, 2000);    
	 });

/*	$("#xllink").click(function(){
	    $("#load").show();
	    window.location.href = "donations.php?action=xls"; 
	    setTimeout(function () {
	        $("#load").hide();
	    }, 2000);     
	 });*/
</script>	
