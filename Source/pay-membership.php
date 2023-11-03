<?php
	
	require_once 'cOnfig/connection.php';
	require_once 'cOnfig/view.php';
	require_once 'cOnfig/authenticate.php';
	require_once 'cOnfig/languages/common.php';
	
	session_start();
	$accessLevel = '3';
	
	$domain = $_SESSION['domain'];
	
	// Authenticate & authorize
	authorizeUser($accessLevel);
	
	getSettings();
	
	// donatedto: 0 = caja (old), 1 = caja, 2 = bank, 3 = saldo, 4 = cashdro, 5 = change expiry
	
	// Did this page re-submit with a payment extension? If so, add warning to dB, then re-direct.
	if (isset($_GET['postpone'])) {
		$user_id      = $_GET['user_id'];
		$warningTime = date('Y-m-d H:i:s');
	
		$updateUser = sprintf("UPDATE users SET paymentWarning = '1', paymentWarningDate = '%s' WHERE user_id = '%d';",
			$warningTime,
			$user_id
			);
		try
		{
			$result = $pdo3->prepare("$updateUser")->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
			
		// On success: redirect.
		$_SESSION['successMessage'] = $lang['paymentpostponed'];
		header("Location: profile.php?user_id={$user_id}");
		exit();

	} else if (isset($_GET['exento'])) {
		
		$user_id      = $_GET['user_id'];
		
		$updateUser = sprintf("UPDATE users SET exento = 1 WHERE user_id = '%d';",
			$user_id
			);
		try
		{
			$result = $pdo3->prepare("$updateUser")->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
			
		// On success: redirect.
		$_SESSION['successMessage'] = $lang['member-exempt'];
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
				
			} else {

				$selectCuota = "SELECT cuota, days FROM cuotas WHERE id = $paidUntil";
		try
		{
			$result = $pdo3->prepare("$selectCuota");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$cuotaRes = $result->fetch();
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
		try
		{
			$result = $pdo3->prepare("$selectCuota");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$cuotaRes = $result->fetch();
					$cuota = $cuotaRes['cuota'];
					$days = $cuotaRes['days'];

				$memberExp = date('Y-m-d H:i:s', strtotime("+$days day", strtotime($paymentTime)));
				$amountPaid = $cuota;

			}
			
		}
		
		// Check & adjust user saldo		
			$userDetails = "SELECT credit FROM users WHERE user_id = '{$user_id}'";
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
				$credit = $row['credit'];
				
		if ($paidTo == 3) {
			
			$newCredit = $credit - $amountPaid;
			
			if ($newCredit < 0) {
				
				$_SESSION['errorMessage'] = $lang['insufficient-credit'] . "!";
				header("Location: pay-membership.php?user_id=$user_id");
				exit();
				
			} else {
				
				// Update user credit
				$updateCredit = "UPDATE users SET credit = '$newCredit' WHERE user_id = $user_id";
		try
		{
			$result = $pdo3->prepare("$updateCredit")->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
					
			}
			
		} else {
			
			$newCredit = $credit;
			
		}

		
		// Query to update user - 28 arguments
		$updateUser = sprintf("UPDATE users SET paymentWarning = '0', paymentWarningDate = NULL, paidUntil = '%s' WHERE user_id = '%d';",
			$memberExp,
			$user_id
			);
		try
		{
			$result = $pdo3->prepare("$updateUser")->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
			
			
		// Query to add payment
		  $query = sprintf("INSERT INTO memberpayments (paymentdate, userid, amountPaid, oldExpiry, newExpiry, paidTo, comment, operator, creditBefore, creditAfter) VALUES ('%s', '%d', '%f', '%s', '%s', '%d', '%s', '%d', '%f', '%f');",
		  $paymentTime, $user_id, $amountPaid, $origPaidUntil, $memberExp, $paidTo, $adminComment, $_SESSION['user_id'], $credit, $newCredit);
		try
		{
			$result = $pdo3->prepare("$query")->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
			
		// Write to log
		$logTime = date('Y-m-d H:i:s');
	
		$query = sprintf("INSERT INTO log (logtype, logtime, user_id, operator, amount, oldExpiry, newExpiry) VALUES ('%d', '%s', '%d', '%d', '%f', '%s', '%s');",
		7, $logTime, $user_id, $_SESSION['user_id'], $amountPaid, $origPaidUntil, $memberExp);
		try
		{
			$result = $pdo3->prepare("$query")->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
			
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
		$userDetails = "SELECT memberno, paidUntil, userGroup, first_name, last_name, photoExt, credit, exento, photoExt FROM users WHERE user_id = '{$user_id}'";
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
			$paidUntil = $row['paidUntil'];
			$userGroup = $row['userGroup'];
			$photoext = $row['photoExt'];
			$credit = $row['credit'];
			$exento = $row['exento'];

			
	pageStart($lang['title-paymembership'], NULL, $validationScript, "pprofilenew", "donations fees", $lang['paymembershipcaps'], $_SESSION['successMessage'], $_SESSION['errorMessage']);




	$memberPhoto = 'images/_' . $_SESSION['domain'] . '/members/' . $user_id . '.' .  $photoext;
	
	if (!file_exists($memberPhoto)) {
		$memberPhoto = "<img class='profilepic' src='images/silhouette-new-big.png' />";
		$notexist = 'yes';
	} else {
		$memberPhoto = "<img class='profilepic' src='$memberPhoto' width='237' />";
	}
	
	if ($userGroup == 7) {
		$groupName = "<span class='usergrouptextbanned'>$groupName</span>";		
	} else {
		$groupName = "<span class='usergrouptext'>$groupName</span>";
		
	}

		$highRollerWeekly = $_SESSION['highRollerWeekly'];
		$consumptionPercentage = $_SESSION['consumptionPercentage'] / 100;

	// Is the user a high roller?
	if ($totalAmountPerWeek >= $highRollerWeekly && $notexist == 'yes') {
		$highroller = "<br /><img src='images/highroller-big.png' style='margin-top: -4px;' />";
	} else if ($totalAmountPerWeek >= $highRollerWeekly && $notexist != 'yes') {
		$highroller = "<br /><img src='images/highroller-xl.png' style='margin-top: -4px;' />";
	} else {
		$highroller = "";
	}
?>
<center><a href="profile.php?user_id=<?php echo $user_id; ?>" class='cta1nm'>&laquo; <?php echo $lang['title-profile']; ?> &laquo;</a></center>

<div id="mainbox">
 <div id="mainleft">
  <span id="profilepicholder"><a href="new-picture.php?user_id=<?php echo $user_id; ?>"><?php echo $memberPhoto; ?></a><?php echo $highroller; ?></span>
<?php

	echo <<<EOD
   <span class='firsttext'>#$memberno</span><br /><span class='nametext'>$first_name $last_name</span><br />
EOD;
		if ($_SESSION['puestosOrNot'] == 1) {
		
			if ($workStation == 1 || $workStation == 6 || $workStation == 11 || $workStation == 16) {
				echo "<img src='images/puesto-reception.png' height='22' style='margin-bottom: -6px; margin-left: 8px;' />&nbsp;";
			}
			if ($workStation == 5 || $workStation == 6 || $workStation == 15 || $workStation == 16) {
				echo "<img src='images/puesto-bar.png' height='22' style='margin-bottom: -6px; margin-left: 8px;' />&nbsp;";
			}
			if ($workStation == 10 || $workStation == 11 || $workStation == 15 || $workStation == 16) {
				echo "<img src='images/puesto-dispensary.png' height='22' style='margin-bottom: -6px; margin-left: 8px;' />&nbsp;";
			}
		}
		
	if ($_SESSION['showGender'] == 1) {
		$gender = $gender;
	} else {
		$gender = '';
	}
	if ($_SESSION['showAge'] == 1) {
		$age = $age . " " . $lang['member-yearsold'];
	} else {
		$age = '';
	}

		$memberExp = date('y-m-d', strtotime($paidUntil));
		$memberExpReadable = date('d M Y', strtotime($paidUntil));
		$timeNow = date('y-m-d');
		
		if (strtotime($memberExp) > strtotime($timeNow)) {
			
			$cuotaWarning = $lang['member-memberuntil'] . ": $memberExpReadable";
			
	 	} else if (strtotime($memberExp) == strtotime($timeNow)) {
			$cuotaWarning = <<<EOD
			<img src='images/exclamation-15.png' class='warningIcon' style='margin-bottom: -2px; margin-left: 7px; margin-right: 5px;' /> 
   {$lang['member-expirestoday']}
EOD;
		} else if ($paymentWarning == '1') {
			$cuotaWarning = <<<EOD
			<img src='images/exclamation-15.png' class='warningIcon' style='margin-bottom: -2px; margin-left: 7px; margin-right: 5px;' /> <img src='images/exclamation-15.png' class='warningIcon' style='margin-bottom: -2px; margin-left: -14px; margin-right: 5px;' />
   {$lang['member-receivedwarning']}: $paymentWarningDateReadable
EOD;
		} else {
			$cuotaWarning = <<<EOD
		<img src='images/exclamation-15.png' class='warningIcon' style='margin-bottom: -2px; margin-left: 7px; margin-right: 5px;' /> 
   {$lang['member-expiredon']}: $memberExpReadable	
EOD;
		}


	echo "<br /><br /><a href='donation-management.php?userid=" . $user_id . "'><span class='creditDisplay'>Credit: <span class='creditAmount $userClass'>" . number_format($credit,2) . " &euro;$creditEligibility</span></span></a><br /><br />$cuotaWarning";
   			if ($_SESSION['expirychange'] == 1) {
				echo "&nbsp;<a href='change-expiry.php?userid=$user_id' class='smallerfont2 orange'> [{$lang['change']}]</a>";
			}

   echo "</div>";
?>

<div id='donationholder'>
 <form id="registerForm" action="" method="POST">
  <input type="hidden" name="user_id" value="<?php echo $user_id; ?>" />
  <input type="hidden" name="origPaidUntil" value="<?php echo $paidUntil;?>" /><br />
  <input type="hidden" name="userid" value="<?php echo $userid; ?>" />
  <input type="hidden" name="credit" value="<?php echo $credit; ?>" />
 <table class='donationtable'>
  <tr>
   <td><?php echo $lang['choose-fee']; ?></td>
  </tr>
  <tr>
   <td>
 <?php

	// Query to look up cuotas
	$selectCuotas = "SELECT id, name, cuota, days FROM cuotas";
		try
		{
			$results = $pdo3->prepare("$selectCuotas");
			$results->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		while ($cuotaRes = $results->fetch()) {

		$id = $cuotaRes['id'];
		$name = $cuotaRes['name'];
		$cuota = number_format($cuotaRes['cuota'],0);
		$days = $cuotaRes['days'];
		
		echo "<input type='radio' id='fee$id' name='paidUntil' value='$id' /><label for='fee$id'><span class='full'>$name $cuota&euro;</span></label>";
		
	}

		if ($_SESSION['lang'] == 'en') {
 			echo "<a href='?postpone&user_id=$user_id' class='fakeradio' style='line-height: 33px; background-color: #eba4a2; border-color: #e29393; color: white;'>{$lang['paynexttime']}</a>";
		} else {
 			echo "<a href='?postpone&user_id=$user_id' class='fakeradio' style='background-color: #eba4a2; border-color: #e29393; color: white;'>{$lang['paynexttime']}</a>";
		}
		
		if (($_SESSION['exentoset'] == 0 && $_SESSION['userGroup'] == 1) || $_SESSION['exentoset'] == 1) {
			
 			echo "<a href='?exento&user_id=$user_id' class='fakeradio' style='line-height: 33px; background-color: #eba4a2; border-color: #e29393; color: white; vertical-align: top;'>{$lang['exempt']}</a>";
 			
		}
?><br />
   </td>
  </tr>
  <tr>
   <td>
 	<?php echo $lang['choose-payment-method']; ?>
   </td>
  </tr>
  <tr>
   <td>
        <input type="radio" id="method1" name="method" value='1' />
        <label for="method1"><span class='full'><?php echo $lang['cash']; ?></span></label>
        
       
        <input type="radio" id="method3" name="method" value='3' />
        <label for="method3"><span class='full' style='margin-left: -4px;'><?php echo $lang['global-credit']; ?></span></label>
        
  		<br />

   </td>
  </tr>
  <tr>
   <td>
 	<?php echo $lang['renew-from']; ?>
   </td>
  </tr>
  <tr>
   <td>
    <table>
     <tr>
      <td colspan='2'>
        <input type="radio" id="renew1" name="renewFrom" value='1' />
        <label for="renew1"><span class='full' style='margin-left: -4px;'><?php echo $lang['expiry']; ?></span></label>
        <input type="radio" id="renew2" name="renewFrom" value='2' />
        <label for="renew2"><span class='full' style='margin-left: -4px;'><?php echo $lang['dispensary-today']; ?></span></label><br />
  <button class='oneClick okbutton2' name='oneClick' type="submit" style='margin-left: -2px; width: 286px;'><?php echo $lang['global-confirm']; ?></button></td>

      </td>
      <td>
  <textarea name="adminComment" placeholder="<?php echo $lang['global-comment']; ?>?" style='margin-top: 0; margin-left: -12px; width: 116px;'></textarea><br /><br />
      </td>
     </tr>
    </table>
        
        

   </td>
  </tr>
 </table>
 <!--
    <table>
     <tr>
      <td><?php echo $lang['amount']; ?></td>
      <td colspan='2'><?php echo $lang['paid-by']; ?></td>
     </tr>
     <tr>
      <td><input type="number" lang="nb" name="amount" placeholder="&euro;" class="fourDigit defaultinput" step="0.01" /></td>
      <td><input type="radio" id="donatedTo1" name="donatedTo" value='1' />
            <label for="donatedTo1"><span class='full'><?php echo $lang['cash']; ?>&nbsp;</span></label></td>
      <td><input type="radio" id="donatedTo2" name="donatedTo" value='2' />
           <label for="donatedTo2"><span class='full'><?php echo $lang['bank-card']; ?></span></label></td>
     </tr>
     <tr>
      <td colspan='3'><button class='oneClick okbutton2' name='oneClick' type="submit"><?php echo $lang['global-confirm']; ?></button></td>
     </tr>
    </table>
   </td>
   <td style='padding-top: 10px;'><?php echo $lang['global-comment']; ?><br /><textarea name="comment" placeholder=""></textarea></td>
  </tr>
 </table>-->
 </form>
</div>
</div>
 
   <div id="mainbox">
   <div class='mainboxheader'>
    <img src="images/calendar.png" style='margin-bottom: -8px; margin-right: 5px;' /> <?php echo $lang['history']; ?>
   </div>
   <div class='mainboxcontent'>

 <?php
		// Query to look up past payments
	$selectExpenses = "SELECT paymentid, paymentdate, userid, amountPaid, oldExpiry, newExpiry, paidTo, creditBefore, creditAfter, operator FROM memberpayments WHERE userid = $user_id ORDER by paymentdate DESC";
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
	
		

		
?>
<br />
	 <table class="default">
	  <thead>
	   <tr>
	    <th><?php echo $lang['global-time']; ?></th>
	    <th><?php echo $lang['paid-by']; ?></th>
	    <th><?php echo $lang['global-amount']; ?></th>
	    <th><?php echo $lang['old-expiry']; ?></th>
	    <th><?php echo $lang['new-expiry']; ?></th>
	    <th><?php echo $lang['dispense-oldcredit']; ?></th>
	    <th><?php echo $lang['dispense-newcredit']; ?></th>
	    <th colspan="2"><?php echo $lang['operator']; ?></th>
	   </tr>
	  </thead>
	  <tbody>
	  
	  <?php

	  $u = 0;
		while ($donation = $results->fetch()) {
	
	$paymentid = $donation['paymentid'];
	$paymentdate = date("d M H:i", strtotime($donation['paymentdate'] . "+$offsetSec seconds"));
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
	} else if ($paidTo == '4') {
		$paidTo = "CashDro";
	} else if ($paidTo == '5') {
		$paidTo = $lang['changed-expiry'];
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
  	   <td class='left'>%s</td>
  	   <td class='right'>%0.00f &euro;</td>
  	   <td class='right'>%s</td>
  	   <td class='right'>%s</td>
  	   <td class='right'>%0.00f &euro;</td>
  	   <td class='right'>%0.00f &euro;</td>
  	   <td class='left'>%s</td>
  	   %s
 	   
	  </tr>",
	  $paymentdate, $paidTo, $amount, $oldExpiry, $newExpiry, $creditBefore, $creditAfter, $operator, $deleteOrNot
	  );
	  echo $expense_row;
	  
	  $i++;
  }
?>

	 </tbody>
	 </table>
   
<?php displayFooter(); ?>
