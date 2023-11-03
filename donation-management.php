<?php
	
	require_once 'cOnfig/connection.php';
	require_once 'cOnfig/view.php';
	require_once 'cOnfig/authenticate.php';
	require_once 'cOnfig/languages/common.php';
	require_once 'googleConfig.php';
	
	session_start();
	$accessLevel = '3';
	
	$domain = $_SESSION['domain'];
	
	// Authenticate & authorize
	authorizeUser($accessLevel);

	getSettings();
	
	// donatedto: 0 = caja (old), 1 = caja, 2 = bank, 3 = change saldo, 4 = cashdro
	
	// Did this page re-submit with a form? If so, check & store details
	
	// Write to: Scanhistory + donations + users
	if (isset($_POST['amount'])) {
		
		$userid = $_POST['userid'];
  	    $credit = $_POST['credit'];
		$amount = $_POST['amount'];
		$comment = $_POST['comment'];
		$registertime = date('Y-m-d H:i:s');
		
		if ($_SESSION['bankPayments'] == 1) {
			$donatedTo = $_POST['donatedTo'];
		} else {
			$donatedTo = 1;
		}
		
		$operator = $_SESSION['user_id'];
		
		// Look up user credit
		$userCredit = "SELECT credit FROM users WHERE user_id = '{$userid}'";
		try
		{
			$result = $pdo3->prepare("$userCredit");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
			$oldCredit = $row['credit'];

		$newCredit = $amount + $oldCredit;
		
		// Query to add to Donations table
		 $query = sprintf("INSERT INTO donations (userid, donationTime, type, amount, comment, creditBefore, creditAfter, donatedTo, operator) VALUES ('%d', '%s', '%d', '%f', '%s', '%f', '%f', '%d', '%d');",
		  $userid, $registertime, '1', $amount, $comment, $oldCredit, $newCredit, $donatedTo, $operator);
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


		
		// Query to update user profile
		$updateUser = sprintf("UPDATE users SET credit = '%f' WHERE user_id = '%d';",
			$newCredit,
			$userid
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
			
		// Write to log
		$logTime = date('Y-m-d H:i:s');
	
		$query = sprintf("INSERT INTO log (logtype, logtime, user_id, operator, amount, oldCredit, newCredit) VALUES ('%d', '%s', '%d', '%d', '%f', '%f', '%f');",
		6, $logTime, $userid, $_SESSION['user_id'], $amount, $oldCredit, $newCredit);
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
		$_SESSION['successMessage'] = $lang['global-added'] . " " . $amount . $lang['donation-addedsuccessfully'] . $newCredit . " ".$_SESSION['currencyoperator'];
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
				  required: true
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
	  
   function commaChange() {
	   	  $(this).val($(this).val().replace(/,/g, '.'));
	   	  $(this).val($(this).val().replace(' ', ''));
	   	  $(this).val($(this).val().replace('g', ''));
        }
   $('#amount').bind('keypress keyup blur change', commaChange);


   			$('.default').tablesorter({
				usNumberFormat: true,
				headers: {
					3: {
						sorter: "dates"
					},
					7: {
						sorter: "dates"
					}
				}
			}); 

  }); // end ready

EOD;
			
	pageStart($lang['title-donations'], NULL, $deleteDonationScript, "pprofilenew", "donations", $lang['global-donationscaps'], $_SESSION['successMessage'], $_SESSION['errorMessage']);
	
		if ($day != 0) {
		$bdayraw = $day . "." . $month . "." . $year;
		$bday = new DateTime($bdayraw);
		$today = new DateTime(); // for testing purposes
		$diff = $today->diff($bday);
		$age = $diff->y;
		
		$birthday = date("d M Y", strtotime($bdayraw));
	
	} else {
		
		$birthday = '';
		
	}
	if (date('m-d') == date('m-d', strtotime($year . "-" . $month . "-" . $day . " 00:00:00"))) {
		$bdayicon = "<img src='images/birthday.png' style='margin-bottom: -2px;' /> &nbsp;{$lang['global-birthday']}";
	}



	$memberPhoto = 'images/_' . $_SESSION['domain'] . '/members/' . $userid . '.' .  $photoExt;

	$object_exist = object_exist($google_bucket, $google_root_folder.$memberPhoto);
	
	if (!$object_exist) {
		$memberPhoto = "<img class='profilepic' src='{$google_root}images/silhouette-new-big.png' />";
		$notexist = 'yes';
	} else {
		$memberPhoto = "<img class='profilepic' src='{$google_root}$memberPhoto' width='237' />";
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
  <span id="profilepicholder"><a href="new-picture.php?user_id=<?php echo $userid; ?>"><?php echo $memberPhoto; ?></a><?php echo $highroller; ?></span>
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



	echo "<br /><br /><span class='creditDisplay'>Credit: <span class='creditAmount $userClass'>" . number_format($credit,2) . " {$_SESSION['currencyoperator']}$creditEligibility";
	echo "</span></span>";
	
    if ($_SESSION['saldoGift'] == 1) {
	    echo "&nbsp;<a href='gift-credit.php?user_id=$user_id' ><img src='images/gift-noframe.png' style='display: inline-block; margin-top: -3px; margin-left: 10px;'/></a>";
    }
	echo "<br />";

if ($_SESSION['creditchange'] == 1) {
	if ($_SESSION['userGroup'] == 1) {
		echo "<br /><a href='change-credit.php?userid=$userid' class='orange smallerfont'>[{$lang['change-manually']}]</a>";
	}
}
	echo "</div>";
?>

<div id='donationholder'>
 <form id="registerForm" action="" method="POST">
  <input type="hidden" name="userid" value="<?php echo $userid; ?>" />
  <input type="hidden" name="credit" value="<?php echo $credit; ?>" />
 <h4><?php echo $lang['donation-makedonation']; ?></h4>
 <br />
 <table class='donationtable'>
  <tr>
   <td>
    <table>
     <tr>
      <td><?php echo $lang['amount']; ?></td>
      <td colspan='2'><?php echo $lang['paid-by']; ?></td>
     </tr>
     <tr>
      <td><input type="text" lang="nb" name="amount" id="amount" placeholder="<?php echo $_SESSION['currencyoperator'] ?>" class="fourDigit defaultinput" /></td>
      <td><input type="radio" id="donatedTo1" name="donatedTo" value='1' <?php if ($_SESSION['bankPayments'] == 0) { echo "checked"; } ?> />
            <label for="donatedTo1"><span class='full'><?php echo $lang['cash']; ?>&nbsp;</span></label></td>
<?php 	if ($_SESSION['bankPayments'] == 1) { ?>
      <td><input type="radio" id="donatedTo2" name="donatedTo" value='2' />
           <label for="donatedTo2"><span class='full'><?php echo $lang['bank-card']; ?></span></label></td>
<?php } ?>
     </tr>
     <tr>
      <td colspan='3'><button class='oneClick okbutton2' name='oneClick' type="submit"><?php echo $lang['global-confirm']; ?></button></td>
     </tr>
    </table>
   </td>
   <td style='padding-top: 10px;'><?php echo $lang['global-comment']; ?><br /><textarea name="comment" placeholder=""></textarea></td>
  </tr>
 </table>
 </form>
</div>
</div>
 
   <div id="mainbox">
   <div class='mainboxheader'>
    <img src="images/calendar.png" style='margin-bottom: -8px; margin-right: 5px;' /> <?php echo $lang['donation-donationhistorycaps']; ?>
   </div>
   <div class='mainboxcontent'>
 
 <?php
		// Query to look up past donations
	$selectExpenses = "SELECT donationid, donationTime, amount, creditBefore, creditAfter, donatedTo, comment, type, operator FROM donations WHERE userid = $userid ORDER by donationTime DESC";
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
<br /><br />
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
		while ($donation = $results->fetch()) {
	
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
	
	if ($type == 2) {
		$hideOrNot = "style='display: none;'";
	} else if ($i == 1) {
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
	} else if ($donatedTo == '4') {
		$donatedTo = 'CashDro';
	} else {
		$donatedTo = $lang['global-till'];
	}
		
	if ($_SESSION['bankPayments'] == 1) {
		
	$expense_row =	sprintf("
  	  <tr>
  	   <td>%s</td>
  	   <td>%s</td>
  	   <td>%s</td>
  	   <td class='right'>%0.02f {$_SESSION['currencyoperator']}</td>
  	   <td class='right'>%0.02f {$_SESSION['currencyoperator']}</td>
  	   <td class='right'>%0.02f {$_SESSION['currencyoperator']}</td>
	   <td class='left'>%s</td>
	   <td class='centered'><span class='relativeitem'>$commentRead</span></td>
 	   <td style='text-align: center;'><span $hideOrNot><!--<a href='edit-donation.php?donationid=%d&userid=%d'><img src='images/edit.png' height='15' /></a>-->&nbsp;&nbsp;<a href='javascript:delete_donation(%d,%f,%d)'><img src='images/delete.png' height='15' title='{$lang['donation-deletedonation']}' /></a></span></td>
	  </tr>",
	  $donationTime, $operationType, $donatedTo, $amount, $creditBefore, $creditAfter, $operator, $donationid, $userid, $donationid, $donation['amount'], $userid
	  );
	  
  	} else {
	  	
	$expense_row =	sprintf("
  	  <tr>
  	   <td>%s</td>
  	   <td>%s</td>
  	   <td class='right'>%0.02f {$_SESSION['currencyoperator']}</td>
  	   <td class='right'>%0.02f {$_SESSION['currencyoperator']}</td>
  	   <td class='right'>%0.02f {$_SESSION['currencyoperator']}</td>
	   <td class='left'>%s</td>
	   <td class='centered'><span class='relativeitem'>$commentRead</span></td>
 	   <td style='text-align: center;'><span $hideOrNot><!--<a href='edit-donation.php?donationid=%d&userid=%d'><img src='images/edit.png' height='15' /></a>-->&nbsp;&nbsp;<a href='javascript:delete_donation(%d,%f,%d)'><img src='images/delete.png' height='15' title='{$lang['donation-deletedonation']}' /></a></span></td>
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
	 </div>
	 </div>
	 
   
<?php displayFooter(); ?>
