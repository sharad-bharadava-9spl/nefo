<?php
	
	require_once 'cOnfig/connection.php';
	require_once 'cOnfig/view.php';
	require_once 'cOnfig/authenticate.php';
	require_once 'cOnfig/languages/common.php';
	
	session_start();
	$accessLevel = '3';
	
	// Authenticate & authorize
	authorizeUser($accessLevel);
	
	// Did this page re-submit with a form? If so, check & store details
	if (isset($_POST['month'])) {
		
		$userid = $_POST['userid'];
  	    $day = $_POST['day'];
		$month = $_POST['month'];
		$year = $_POST['year'];
		$paidUntil = $_POST['paidUntil'];
		
		if ($day > 0 && $month > 0 && $year > 0) {
			$date_timestamp = strtotime($month . "/" . $day . "/" . $year);
			$registertime = date("Y-m-d H:i:s", $date_timestamp);
		} else {
			$registertime = date('Y-m-d H:i:s');
		}
		
		$nowTime = date('Y-m-d H:i:s');
		
		$userDetails = "SELECT credit FROM users WHERE user_id = '{$userid}'";
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
			$newCredit = $row['credit'];


		// Query to add new sale to Sales table - 6 arguments
		  $query = "UPDATE users SET paymentWarning = '0', paymentWarningDate = NULL, paidUntil = '$registertime' WHERE user_id = '$userid'";
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
		// Query to add payment
		  $query = sprintf("INSERT INTO memberpayments (paymentdate, userid, amountPaid, oldExpiry, newExpiry, paidTo, comment, operator, creditBefore, creditAfter) VALUES ('%s', '%d', '%f', '%s', '%s', '%d', '%s', '%d', '%f', '%f');",
		  $nowTime, $userid, 0, $paidUntil, $registertime, 5, $adminComment, $_SESSION['user_id'], $credit, $newCredit);
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
	
		$query = sprintf("INSERT INTO log (logtype, logtime, user_id, operator, oldExpiry, newExpiry) VALUES ('%d', '%s', '%d', '%d', '%s', '%s');",
		8, $logTime, $userid, $_SESSION['user_id'], $paidUntil, $registertime);
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
			$_SESSION['successMessage'] = $lang['changed-expiry'];
			header("Location: profile.php?user_id=$userid");
			exit();
		}
	/***** FORM SUBMIT END *****/
	
	
	
	// Get the user ID
	if (isset($_REQUEST['userid'])) {
		$user_id = $_REQUEST['userid'];
	} else {
		handleError($lang['error-nouserid'],"");
	}

	// Look up user details for showing profile on the Sales page
	$userDetails = "SELECT memberno, paidUntil, userGroup, first_name, last_name, photoExt, paidUntil, credit FROM users WHERE user_id = '{$user_id}'";
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
		$photoExt = $row['photoExt'];
		$paidUntil = $row['paidUntil'];
		$credit = $row['credit'];

	
	$validationScript = <<<EOD
	
    $(document).ready(function() {
	    
	    	    
	  $('#registerForm').validate({
		  ignore:'', //because the radio buttons are hidden, validation ignores them. This way it'll work.
		  rules: {
			  day: {
				  required: true,
				  range:[0,31]
			  },
			  month: {
				  required: true,
				  range:[0,12]
			  },
			  year: {
				  required: true,
				  range:[0,2030]
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



	

	pageStart($lang['change-expiry'], NULL, $validationScript, "pprofilenew", "donations fees", $lang['change-expiry'], $_SESSION['successMessage'], $_SESSION['errorMessage']);
	
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

   echo "</div>";
?>

<div id='donationholder'>
<form id="registerForm" action="" method="POST">
   <input type="hidden" name="userid" value="<?php echo $user_id; ?>" />
   <input type="hidden" name="paidUntil" value="<?php echo $paidUntil; ?>" />
 <h4><?php echo $lang['change-expiry']; ?></h4>
 <br />
 <table>
  <tr>
   <td style='vertical-align: top;'>
    <input type="number" lang="nb" name="day" class="twoDigit defaultinput" maxlength="2" placeholder="dd" />
    <input type="number" lang="nb" name="month" class="twoDigit defaultinput" maxlength="2" placeholder="mm" />
    <input type="number" lang="nb" name="year" class="fourDigit defaultinput" maxlength="4" placeholder="<?php echo $lang['member-yyyy']; ?>" /><br /><br />
    <button class='oneClick okbutton2' name='oneClick' type="submit" style='margin-left: 1px; width: 208px;'><?php echo $lang['global-confirm']; ?></button> 

   </td>
   <td style='display: inline-block; vertical-align: top; margin-left: 10px; margin-top: -10px;'>
    <textarea name="comment" placeholder="<?php echo $lang['global-comment']; ?>?" style='height: 76px;'></textarea>
   </td>
  </tr>
 </table>

</form>

</div>
</div>
