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
	
	// Did this page re-submit with a form? If so, check & store details
	
	// Write to: Scanhistory + donations + users
	if (isset($_POST['amount'])) {
		
		$userid = $_POST['userid'];
  	    $credit = $_POST['credit'];
		$amount = $_POST['amount'];
		$comment = $_POST['comment'];
		$donatedTo = $_POST['donatedTo'];
		$registertime = date('Y-m-d H:i:s');
		
		$operator = $_SESSION['user_id'];
		
		// Look up user credit
		$userCredit = "SELECT credit FROM users WHERE user_id = '{$userid}'";
	
		$result = mysql_query($userCredit)
			or handleError($lang['error-userload'],"Error loading user: " . mysql_error());
	
		$row = mysql_fetch_array($result);
			$oldCredit = $row['credit'];

		$newCredit = $amount + $oldCredit;
		
		// Query to add to Donations table
		 $query = sprintf("INSERT INTO donations (userid, donationTime, type, amount, comment, creditBefore, creditAfter, donatedTo, operator) VALUES ('%d', '%s', '%d', '%f', '%s', '%f', '%f', '%d', '%d');",
		  $userid, $registertime, '1', $amount, $comment, $oldCredit, $newCredit, $donatedTo, $operator);
		  
		mysql_query($query)
			or handleError($lang['error-savedonation'],"Error inserting donation: " . mysql_error());
			
		 $query = sprintf("INSERT INTO f_donations (userid, donationTime, type, amount, comment, creditBefore, creditAfter, donatedTo, operator) VALUES ('%d', '%s', '%d', '%f', '%s', '%f', '%f', '%d', '%d');",
		  $userid, $registertime, '1', $amount, $comment, $oldCredit, $newCredit, $donatedTo, $operator);
		  
		mysql_query($query)
			or handleError($lang['error-savedonation'],"Error inserting donation: " . mysql_error());


		
		// Query to update user profile
		$updateUser = sprintf("UPDATE users SET credit = '%f' WHERE user_id = '%d';",
			mysql_real_escape_string($newCredit),
			mysql_real_escape_string($userid)
			);
				
		mysql_query($updateUser)
			or handleError($lang['error-savedata'],"Error updating user profile: " . mysql_error());
			
		// Write to log
		$logTime = date('Y-m-d H:i:s');
	
		$query = sprintf("INSERT INTO log (logtype, logtime, user_id, operator, amount, oldCredit, newCredit) VALUES ('%d', '%s', '%d', '%d', '%f', '%f', '%f');",
		6, $logTime, $userid, $_SESSION['user_id'], $amount, $oldCredit, $newCredit);
		
		mysql_query($query)
			or handleError($lang['error-savedata'],"Error inserting purchase: " . mysql_error());

		$query = sprintf("INSERT INTO f_log (logtype, logtime, user_id, operator, amount, oldCredit, newCredit) VALUES ('%d', '%s', '%d', '%d', '%f', '%f', '%f');",
		6, $logTime, $userid, $_SESSION['user_id'], $amount, $oldCredit, $newCredit);
		
		mysql_query($query)
			or handleError($lang['error-savedata'],"Error inserting purchase: " . mysql_error());

				
		// On success: redirect.
		$_SESSION['successMessage'] = $lang['global-added'] . " " . $amount . $lang['donation-addedsuccessfully'] . $newCredit . "&euro;";
		header("Location: profile.php?user_id=$userid");
		exit();
	}
	/***** FORM SUBMIT END *****/
	
	
	if (isset($_GET['userid'])) {
		$userid = $_GET['userid'];
	} else {
		handleError($lang['error-nomemberid'],"");
	}
		
		// Look up user details for showing profile on the Sales page
		$userDetails = "SELECT memberno, paidUntil, userGroup, first_name, last_name, credit, photoExt FROM users WHERE user_id = '{$userid}'";
	
	// Does user ID exist?
	$userCheck = mysql_query($userDetails);
	if(mysql_num_rows($userCheck) == 0) {
   		handleError($lang['error-useridnotexist'],"");
	}
		$result = mysql_query($userDetails)
			or handleError($lang['error-userload'],"Error loading user: " . mysql_error());
	
		$row = mysql_fetch_array($result);
		$memberno = $row['memberno'];
		$first_name = $row['first_name'];
		$last_name = $row['last_name'];
		$paidUntil = $row['paidUntil'];
		$userGroup = $row['userGroup'];
		$credit = $row['credit'];
		$photoExt = $row['photoExt'];

	$deleteDonationScript = <<<EOD
function delete_donation(donationid,amount,userid) {
	if (confirm("{$lang['donation-deleteconfirm']}")) {
				window.location = "uTil/delete-donation.php?donationid=" + donationid + "&amount=" + amount + "&userid=" + userid;
				}
}
    $(document).ready(function() {
	    
	    	    
	  $('#registerForm').validate({
		  ignore:'', //because the radio buttons are hidden, validation ignores them. This way it'll work.
		  rules: {
			  amount: {
				  required: true,
				  range:[0,10000]
			  }, 
			  donatedTo: {
				  required: true
			  }
    	},
		  errorPlacement: function(error, element) {
			  
			  if (element.attr("name") == "expenseCat") {
        		error.appendTo($('#categoryLink'));
    		  } else if ( element.is(":radio") || element.is(":checkbox")){
				 error.appendTo(element.parent());
			} else {
				return true;
			}
		},
    	  submitHandler: function() {
   $(".oneClick").attr("disabled", true);
   form.submit();
	    	  }
	  }); // end validate
  }); // end ready

EOD;
			
	pageStart($lang['title-donations'], NULL, $deleteDonationScript, "pmembership", NULL, $lang['global-donationscaps'], $_SESSION['successMessage'], $_SESSION['errorMessage']);

echo "<center><div id='profilearea'><img src='images/members/$userid.$photoExt' class='salesPagePic' /><h4>#$memberno - $first_name $last_name</h4><div class='clearfloat'></div><span class='creditDisplay'>{$lang['global-credit']}: <span class='creditAmount'>" . number_format($credit,2) . "</span></span><br /><a href='change-credit.php?userid=$userid' class='yellow smallerfont hoverwhite'>[Change manually]</a></div></center>";

?>


<br />


 <div id="overviewWrap">
 <div class="overview" style="padding: 10px 50px;">
 <form id="registerForm" action="" method="POST">
 
 <h5><?php echo $lang['donation-makedonation']; ?></h5>
  
  <input type="hidden" name="userid" value="<?php echo $userid; ?>" />
  <input type="hidden" name="credit" value="<?php echo $credit; ?>" />
  <input type="number" lang="nb" name="amount" placeholder="&euro;" class="fourDigit" step="0.01" /><br />
  <textarea name="comment" placeholder="<?php echo $lang['global-comment']; ?>?"></textarea><br /><br />
  
<?php if ($_SESSION['bankPayments'] == 1) { ?>

<span style="color: white;">
  <strong><?php echo $lang['donated-to']; ?>:</strong><br />
 <input type="radio" name="donatedTo" value="1" style="margin-left: 5px; width: 10px;"><?php echo $lang['global-till']; ?></input>
 <input type="radio" name="donatedTo" value="2" style="margin-left: 27px; width: 10px;"><?php echo $lang['global-bank']; ?></input><br />
</span>
<br />

<?php } ?>

 <button class='oneClick' name='oneClick' type="submit"><?php echo $lang['global-confirm']; ?></button>
 </form>
 </div>
 
 <?php
		// Query to look up past donations
	$selectExpenses = "SELECT donationid, donationTime, amount, creditBefore, creditAfter, donatedTo, comment, type, operator FROM donations WHERE userid = $userid ORDER by donationTime DESC";

	$result2 = mysql_query($selectExpenses)
		or handleError($lang['error-donationload'],"Error loading expense from db: " . mysql_error());
		
?>
<br /><br />
<h3><?php echo $lang['donation-donationhistorycaps']; ?></h3>
	 <table class="default">
	  <thead>
	   <tr>
	    <th><?php echo $lang['global-time']; ?></th>
	    <th><?php echo $lang['global-type']; ?></th>
<?php if ($_SESSION['bankPayments'] == 1) { ?>
	    <th><?php echo $lang['donated-to']; ?></th>
<?php } ?>
	    <th><?php echo $lang['global-amount']; ?></th>
	    <th><?php echo $lang['donation-creditbefore']; ?></th>
	    <th><?php echo $lang['donation-creditafter']; ?></th>
	    <th><?php echo $lang['operator']; ?></th>
	    <th colspan="3"></th>
	   </tr>
	  </thead>
	  <tbody>
	  
	  <?php

	  $i = 1;
while ($donation = mysql_fetch_array($result2)) {
	
	$donationid = $donation['donationid'];
	$amount = $donation['amount'];
	$creditBefore = $donation['creditBefore'];
	$creditAfter = $donation['creditAfter'];
	$donatedTo = $donation['donatedTo'];
	$donationTime = date("d M H:i", strtotime($donation['donationTime'] . "+$offsetSec seconds"));
	$type = $donation['type'];
	$operatorID = $donation['operator'];
	
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
	
	if ($i == 1) {
		$hideOrNot = '';
	} else {
		$hideOrNot = "style='display: none;'";
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
		
	if ($_SESSION['bankPayments'] == 1) {
		
	$expense_row =	sprintf("
  	  <tr>
  	   <td>%s</td>
  	   <td>%s</td>
  	   <td>%s</td>
  	   <td class='right'>%0.02f &euro;</td>
  	   <td class='right'>%0.02f &euro;</td>
  	   <td class='right'>%0.02f &euro;</td>
	   <td class='left'>%s</td>
	   <td class='centered relative'>$commentRead</td>
 	   <td style='text-align: center;'><span $hideOrNot><a href='edit-donation.php?donationid=%d&userid=%d'><img src='images/edit.png' height='15' /></a>&nbsp;&nbsp;<a href='javascript:delete_donation(%d,%f,%d)'><img src='images/delete.png' height='15' title='{$lang['donation-deletedonation']}' /></a></span></td>
	  </tr>",
	  $donationTime, $operationType, $donatedTo, $amount, $creditBefore, $creditAfter, $operator, $donationid, $userid, $donationid, $donation['amount'], $userid
	  );
	  
  	} else {
	  	
	$expense_row =	sprintf("
  	  <tr>
  	   <td>%s</td>
  	   <td>%s</td>
  	   <td class='right'>%0.02f &euro;</td>
  	   <td class='right'>%0.02f &euro;</td>
  	   <td class='right'>%0.02f &euro;</td>
	   <td class='left'>%s</td>
	   <td class='centered relative'>$commentRead</td>
 	   <td style='text-align: center;'><span $hideOrNot><a href='edit-donation.php?donationid=%d&userid=%d'><img src='images/edit.png' height='15' /></a>&nbsp;&nbsp;<a href='javascript:delete_donation(%d,%f,%d)'><img src='images/delete.png' height='15' title='{$lang['donation-deletedonation']}' /></a></span></td>
	  </tr>",
	  $donationTime, $operationType, $amount, $creditBefore, $creditAfter, $operator, $donationid, $userid, $donationid, $donation['amount'], $userid
	  );
	  
  	}
	  echo $expense_row;
	  
	  $i++;
  }
?>

	 </tbody>
	 </table>
	 
	 <br /><br />
   
<?php displayFooter(); ?>
