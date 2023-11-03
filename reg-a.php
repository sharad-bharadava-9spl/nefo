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
		$renewFrom      = 2;
		$origPaidUntil = $_POST['origPaidUntil'];
		$user_id      = $_POST['user_id'];
		$adminComment = nl2br($_POST['adminComment']);
		$paymentTime = date('Y-m-d H:i:s');
		
		if ($renewFrom == 1) {
			
			if ($paidUntil == 0) {
				
				$memberExp = $paymentTime;
				$amountPaid = 0;
				
			} else {

				$selectCuota = "SELECT cuota, days FROM cuotas WHERE id = $paidUntil";

				$resultCuota = mysql_query($selectCuota)
					or handleError($lang['error-expensesload'],"Error loading expense from db: " . mysql_error());

				$cuotaRes = mysql_fetch_array($resultCuota);
					$cuota = $cuotaRes['cuota'];
					$days = $cuotaRes['days'];

				$memberExp = date('Y-m-d H:i:s', strtotime("+$days day", strtotime($origPaidUntil)));
				$amountPaid = $cuota;

			}
			
		} else {
			
			if ($paidUntil == 0) {
				
				$memberExp = $paymentTime;
				$amountPaid = 0;
				
			} else {

				$selectCuota = "SELECT cuota, days FROM cuotas WHERE id = $paidUntil";

				$resultCuota = mysql_query($selectCuota)
					or handleError($lang['error-expensesload'],"Error loading expense from db: " . mysql_error());

				$cuotaRes = mysql_fetch_array($resultCuota);
					$cuota = $cuotaRes['cuota'];
					$days = $cuotaRes['days'];

				$memberExp = date('Y-m-d H:i:s', strtotime("+$days day", strtotime($paymentTime)));
				$amountPaid = $cuota;

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
				header("Location: profile.php?user_id=$user_id");
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
		$userDetails = "SELECT memberno, paidUntil, userGroup, first_name, last_name, photoExt, credit, exento FROM users WHERE user_id = '{$user_id}'";
	
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
			$exento = $row['exento'];

			
	pageStart($lang['title-paymembership'], NULL, $validationScript, "paymembership", NULL, $lang['paymembershipcaps'], $_SESSION['successMessage'], $_SESSION['errorMessage']);

echo "
<center>
 <div id='smallprofile'>
  <h4>#$memberno - $first_name $last_name</h4>
 </div>
</center>";


?>
<div class="clearfloat"></div>

 <form id="registerForm" action="" method="POST">
 <br />
 		<span>
 		<h1>1. <?php echo $lang['choose-fee']; ?>:</h1>
 		
 <?php

	// Query to look up cuotas
	$selectCuotas = "SELECT id, name, cuota, days FROM cuotas";

	$resultCuotas = mysql_query($selectCuotas)
		or handleError($lang['error-expensesload'],"Error loading expense from db: " . mysql_error());
		
	while ($cuotaRes = mysql_fetch_array($resultCuotas)) {

		$id = $cuotaRes['id'];
		$name = $cuotaRes['name'];
		$cuota = $cuotaRes['cuota'];
		$days = $cuotaRes['days'];
		
		echo "<input type='radio' id='fee$id' name='paidUntil' value='$id' /><label for='fee$id'><span class='full'><br />$name<br />$cuota".$_SESSION['currencyoperator']."</span></label>";
		
	}


?>

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
       
        
  		<br /><br />
 		</span>
 		

<span>
  <input type="hidden" name="user_id" value="<?php echo $user_id; ?>" />
  <input type="hidden" name="origPaidUntil" value="<?php echo $paidUntil;?>" />
  <textarea name="adminComment" placeholder="<?php echo $lang['global-comment']; ?>?"></textarea><br /><br />
  </span>

   <button class='oneClick' name='oneClick' type="submit"><?php echo $lang['global-confirm']; ?></button>
   
 </form><br />
 

 </div>
 
