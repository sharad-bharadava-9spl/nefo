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
	
	// Did this page re-submit with a payment extension? If so, add warning to dB, then re-direct.
	if (isset($_GET['postpone'])) {
		$user_id      = $_GET['user_id'];
		$warningTime = date('Y-m-d H:i:s');
	
		$updateUser = sprintf("UPDATE users SET paymentWarning = '1', paymentWarningDate = '%s' WHERE user_id = '%d';",
			mysql_real_escape_string($warningTime),
			mysql_real_escape_string($user_id)
			);
	
		mysql_query($updateUser)
			or handleError($lang['error-savedata'],"Error inserting user: " . mysql_error());
			
		// On success: redirect.
		$_SESSION['successMessage'] = $lang['paymentpostponed'];
		header("Location: profile.php?user_id={$user_id}");
		exit();

	}

	// Did this page re-submit with a form? If so, check & store details
	if (isset($_POST['paidUntil'])) {

		$paidUntil = $_POST['paidUntil'];
		$paidTo      = $_POST['method'];
		$renewFrom      = $_POST['renewFrom'];
		$origPaidUntil = $_POST['origPaidUntil'];
		$user_id      = $_POST['user_id'];
		$adminComment = nl2br($_POST['adminComment']);
		$paymentTime = date('Y-m-d H:i:s');
		
		if ($renewFrom == 1) {
			
			if ($paidUntil == 0) {
				$memberExp = $paymentTime;
				$amountPaid = 0;
			} else if ($paidUntil == 1) {
				$memberExp = date('Y-m-d H:i:s', strtotime("+30 day", strtotime($origPaidUntil)));
				$amountPaid = 7;
			} else if ($paidUntil == 2) {
				$memberExp = date('Y-m-d H:i:s', strtotime("+365 day", strtotime($origPaidUntil)));
				$amountPaid = 50;
			}
			
		} else {
			
			if ($paidUntil == 0) {
				$memberExp = $paymentTime;
				$amountPaid = 0;
			} else if ($paidUntil == 1) {
				$memberExp = date('Y-m-d H:i:s', strtotime("+30 day", strtotime($paymentTime)));
				$amountPaid = 7;
			} else if ($paidUntil == 2) {
				$memberExp = date('Y-m-d H:i:s', strtotime("+365 day", strtotime($paymentTime)));
				$amountPaid = 50;
			}
			
		}
		
		// Check & adjust user saldo
		
		if ($paidTo == 3) {
			
			$userDetails = "SELECT credit FROM users WHERE user_id = '{$user_id}'";
			
			$result = mysql_query($userDetails)
				or handleError($lang['error-userload'],"Error loading user: " . mysql_error());
		
			$row = mysql_fetch_array($result);
				$credit = $row['credit'];
				
			$newCredit = $credit - $amountPaid;
			
			if ($newCredit < 0) {
				
				$_SESSION['errorMessage'] = $lang['insufficient-credit'] . "!";
				header("Location: pay-membership.php?user_id=$user_id");
				exit();
				
			} else {
				
				// Update user credit
				$updateCredit = "UPDATE users SET credit = '$newCredit' WHERE user_id = $user_id";
				
				mysql_query($updateCredit)
					or handleError($lang['error-savedata'],"Error inserting user: " . mysql_error());
					
			}
			
		}

		
		// Query to update user - 28 arguments
		$updateUser = sprintf("UPDATE users SET paymentWarning = '0', paymentWarningDate = NULL, paidUntil = '%s' WHERE user_id = '%d';",
			mysql_real_escape_string($memberExp),
			mysql_real_escape_string($user_id)
			);
	

		mysql_query($updateUser)
			or handleError($lang['error-savedata'],"Error inserting user: " . mysql_error());
			
			
		// Query to add payment
		  $query = sprintf("INSERT INTO memberpayments (paymentdate, userid, amountPaid, oldExpiry, newExpiry, paidTo, comment) VALUES ('%s', '%d', '%f', '%s', '%s', '%d', '%s');",
		  $paymentTime, $user_id, $amountPaid, $origPaidUntil, $memberExp, $paidTo, $adminComment);
		  			
		mysql_query($query)
			or handleError($lang['error-savedata'],"Error inserting user: " . mysql_error());

		  $query = sprintf("INSERT INTO f_memberpayments (paymentdate, userid, amountPaid, oldExpiry, newExpiry, paidTo, comment) VALUES ('%s', '%d', '%f', '%s', '%s', '%d', '%s');",
		  $paymentTime, $user_id, $amountPaid, $origPaidUntil, $memberExp, $paidTo, $adminComment);
		  			
		mysql_query($query)
			or handleError($lang['error-savedata'],"Error inserting user: " . mysql_error());
			
		// Write to log
		$logTime = date('Y-m-d H:i:s');
	
		$query = sprintf("INSERT INTO log (logtype, logtime, user_id, operator, amount, oldExpiry, newExpiry) VALUES ('%d', '%s', '%d', '%d', '%f', '%s', '%s');",
		7, $logTime, $user_id, $_SESSION['user_id'], $amountPaid, $origPaidUntil, $memberExp);
		
		mysql_query($query)
			or handleError($lang['error-savedata'],"Error inserting purchase: " . mysql_error());
			
		$query = sprintf("INSERT INTO f_log (logtype, logtime, user_id, operator, amount, oldExpiry, newExpiry) VALUES ('%d', '%s', '%d', '%d', '%f', '%s', '%s');",
		7, $logTime, $user_id, $_SESSION['user_id'], $amountPaid, $origPaidUntil, $memberExp);
		
		mysql_query($query)
			or handleError($lang['error-savedata'],"Error inserting purchase: " . mysql_error());
			
		// On success: redirect.
		$_SESSION['successMessage'] = "Membership upgraded successfully!";
		header("Location: profile.php?user_id={$user_id}");
		exit();
	}
	/***** FORM SUBMIT END *****/



	$validationScript = <<<EOD
function delete_payment(paymentid) {
	if (confirm("{$lang['payment-deleteconfirm']}")) {
				window.location = "uTil/delete-payment.php?paymentid=" + paymentid;
				}
}
    $(document).ready(function() {
	    	    
	  $('#registerForm').validate({
		  ignore: [],
		  rules: {
			  paidUntil: {
				  required: true
			  },
			  method: {
				  required: true
			  },
			  renewFrom: {
				  required: true
			  }
    	}, // end rules
		  errorPlacement: function(error, element) {
			  
			  if ( element.is(":radio") || element.is(":checkbox")){
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
	
	if (isset($_GET['user_id'])) {
		if ($_SESSION['userGroup'] <= 3) {
			$user_id = $_GET['user_id'];
		} else {
			handleError($lang['error-notauthorized']);
			exit();
		} // What if a user is trying to edit his own profile with a request ID? Well, they shouldn't??
	// ...this means user is trying to access his own profile
	}
	
		
		// Look up user details for showing profile on the Sales page
		$userDetails = "SELECT memberno, paidUntil, userGroup, first_name, last_name, photoExt, credit FROM users WHERE user_id = '{$user_id}'";
	
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
			$photoext = $row['photoExt'];
			$credit = $row['credit'];

			
	pageStart($lang['title-paymembership'], NULL, $validationScript, "paymembership", NULL, $lang['paymembershipcaps'], $_SESSION['successMessage'], $_SESSION['errorMessage']);

echo "
<center>
 <div id='smallprofile'>
  <h4>#$memberno - $first_name $last_name</h4>
  <img src='images/members/$user_id.$photoext' /><br />";

		$memberExp = date('y-m-d', strtotime($paidUntil));
		$memberExpReadable = date('d M Y', strtotime($paidUntil));
		$timeNow = date('y-m-d');

	if ($userGroup > 4) {
		if ($memberExp == $timeNow) {
			echo "<center><strong>" . $lang['member-expirestoday'] . "</strong> <a href='change-expiry.php?userid=$user_id' class='smallerfont2 yellow'>[{$lang['change']}]</a></center>";
	  	} else if ($memberExp > $timeNow) {
		  	echo "<center><strong>" . $lang['member-memberuntil'] . ":</strong><br />$memberExpReadable <a href='change-expiry.php?userid=$user_id' class='smallerfont2 yellow'>[{$lang['change']}]</a></center>";
		} else {
		  	echo "<center><strong>" . $lang['member-expiredon'] . ":</strong><br />$memberExpReadable <a href='change-expiry.php?userid=$user_id' class='smallerfont2 yellow'>[{$lang['change']}]</a></center>";
		}
	}
echo "<br />{$lang['global-credit']}: <strong>$credit €</strong><br />
 </div>
</center>";


?>
<div class="clearfloat"></div>

 <form id="registerForm" action="" method="POST">
 <br />
 		<span>
 		<h1>1. <?php echo $lang['choose-fee']; ?>:</h1>
        <input type="radio" id="fee1" name="paidUntil" value='1' />
        <label for="fee1"><span class='full'><br />Mensual<br />7&euro;</span></label>
        
        <input type="radio" id="fee2" name="paidUntil" value='2' />
        <label for="fee2"><span class='full'><br />Anual<br />50&euro;</span></label>
        
 		<a href="?postpone&user_id=<?php echo $user_id; ?>" class="fakeButton"><br /><?php echo $lang['paynexttime']; ?></a>
 		
 		<br /><br />
 		</span>
 		<span>
 		<h1>2. <?php echo $lang['choose-payment-method']; ?>:</h1>
 		
        <input type="radio" id="method1" name="method" value='1' />
        <label for="method1"><span class='full'><br /><?php echo $lang['cash']; ?><br />&nbsp;</span></label>
        
<?php if ($_SESSION['bankPayments'] == 1) { ?>

        <input type="radio" id="method2" name="method" value='2' />
        <label for="method2"><span class='full'><br /><?php echo $lang['bank-card']; ?></span></label>
        
<?php } ?>
       
        <input type="radio" id="method3" name="method" value='3' />
        <label for="method3"><span class='full'><br /><?php echo $lang['global-credit']; ?><br />&nbsp;</span></label>
        
  		<br /><br />
 		</span>
 		
 		<span>
 		<h1>3. <?php echo $lang['renew-from']; ?>:</h1>
		
        <input type="radio" id="renew1" name="renewFrom" value='1' />
        <label for="renew1"><span class='full'><br /><?php echo $lang['expiry']; ?></span></label>
        
        <input type="radio" id="renew2" name="renewFrom" value='2' />
        <label for="renew2"><span class='full'><br /><?php echo $lang['dispensary-today']; ?></span></label>
        
  		</span>
       
<br /><br />

<span>
  <input type="hidden" name="user_id" value="<?php echo $user_id; ?>" />
  <input type="hidden" name="origPaidUntil" value="<?php echo $paidUntil;?>" />
  <textarea name="adminComment" placeholder="<?php echo $lang['global-comment']; ?>?"></textarea><br /><br />
  </span>

   <button class='oneClick' name='oneClick' type="submit"><?php echo $lang['global-confirm']; ?></button>
   
 </form><br />
 

 </div>
 
 <?php
		// Query to look up past payments
	$selectExpenses = "SELECT paymentid, paymentdate, userid, amountPaid, oldExpiry, newExpiry, paidTo FROM memberpayments WHERE userid = $user_id ORDER by paymentdate DESC";

	$result2 = mysql_query($selectExpenses)
		or handleError($lang['error-donationload'],"Error loading expense from db: " . mysql_error());
		
	$numberOfRows = mysql_num_rows($result2);

		
?>
<br /><br /><br />
<h3>Historial</h3>
	 <table class="default">
	  <thead>
	   <tr>
	    <th><?php echo $lang['global-time']; ?></th>
	    <th><?php echo $lang['paid-by']; ?></th>
	    <th><?php echo $lang['global-amount']; ?></th>
	    <th><?php echo $lang['old-expiry']; ?></th>
	    <th colspan="2"><?php echo $lang['new-expiry']; ?></th>
	   </tr>
	  </thead>
	  <tbody>
	  
	  <?php

	  $u = 0;
while ($donation = mysql_fetch_array($result2)) {
	
	$paymentid = $donation['paymentid'];
	$paymentdate = date("d M H:i", strtotime($donation['paymentdate'] . "+$offsetSec seconds"));
	$amount = $donation['amountPaid'];
	$paidTo = $donation['paidTo'];
	
	if ($donation['oldExpiry'] == NULL) {
		$oldExpiry = '';
	} else {
		$oldExpiry = date("d M Y", strtotime($donation['oldExpiry']));
	}
	$newExpiry = date("d M Y", strtotime($donation['newExpiry']));
	
	
	if ($paidTo == '2') {
		$paidTo = $lang['card'];
	} else if ($paidTo == '3') {
		$paidTo = $lang['global-credit'];
	} else {
		$paidTo = $lang['cash'];
	}
	
	if ($i == 0) { // or if there's only 1 row in the result
		$deleteOrNot = "<td style='text-align: center;'><a href='javascript:delete_payment($paymentid)'><img src='images/delete.png' height='15' title='{$lang['donation-deletedonation']}' /></a></td>";
	} else {
		$deleteOrNot = '';
 	}
		
	$expense_row =	sprintf("
  	  <tr>
  	   <td>%s</td>
  	   <td class='right'>%s</td>
  	   <td class='right'>%0.00f &euro;</td>
  	   <td class='right'>%s</td>
  	   <td class='right'>%s</td>
  	   %s
 	   
	  </tr>",
	  $paymentdate, $paidTo, $amount, $oldExpiry, $newExpiry, $deleteOrNot
	  );
	  echo $expense_row;
	  
	  $i++;
  }
?>

	 </tbody>
	 </table>

   
<?php displayFooter(); ?>
