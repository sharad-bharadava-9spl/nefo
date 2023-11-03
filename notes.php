<?php
	
	require_once 'cOnfig/connection.php';
	require_once 'cOnfig/view.php';
	require_once 'cOnfig/authenticate.php';
	require_once 'cOnfig/languages/common.php';
	require_once 'googleConfig.php';
	
	session_start();
	$accessLevel = '3';
	
	// Authenticate & authorize
	authorizeUser($accessLevel);

	
	// Did this page re-submit with a form? If so, check & store details
	
	// Write to: Scanhistory + donations + users
	if (isset($_POST['comment'])) {
		
		$userid = $_POST['userid'];
		$comment = str_replace("'","\'",str_replace('%', '&#37;', trim($_POST['comment'])));
		$day = $_POST['day'];
		$month = $_POST['month'];
		$year = $_POST['year'];
		
		if ($day > 0 && $month > 0 && $year > 0) {
			$date_timestamp = strtotime($month . "/" . $day . "/" . $year);
			$registertime = date("Y-m-d H:i:s", $date_timestamp);
		} else {
			$registertime = date('Y-m-d H:i:s'); // 	$purchaseDate = date('Y-m-d H:i:s'); ????
		}

		// Query to add to Comments table
		 $query = sprintf("INSERT INTO usernotes (notetime, userid, note, worker) VALUES ('%s', '%d', '%s', '%d');",
		  $registertime, $userid, $comment, $_SESSION['user_id']);
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
			$_SESSION['successMessage'] = "Comment added successfully.";
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
			  comment: {
				  required: true
			  },
			  day: {
				  range:[0,31]
			  },
			  month: {
				  range:[0,31]
			  },
			  year: {
				  range:[0,2025]
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
			
	pageStart("Member notes", NULL, $deleteDonationScript, "pprofilenew", "donations", "MEMBER NOTES", $_SESSION['successMessage'], $_SESSION['errorMessage']);

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

	$memberPhoto_exist = object_exist($google_bucket, $google_root_folder.$memberPhoto);
	
	if (!$memberPhoto_exist) {
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

<center><a href="profile.php?user_id=<?php echo $userid; ?>" class='cta1nm'>&laquo; <?php echo $lang['title-profile']; ?> &laquo;</a></center>

<br />

<div id="mainbox">
 <div id="mainleft">
  <span id="profilepicholder"><?php echo $memberPhoto; ?><?php echo $highroller; ?></span>
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



	echo "<br /><br /><span class='creditDisplay'>Credit: <span class='creditAmount $userClass'>" . number_format($credit,2) . " ".$_SESSION['currencyoperator']."$creditEligibility</span></span><br /></div>";
?>

<div id='donationholder'>
 <form id="registerForm" action="" method="POST">
  <input type="hidden" name="userid" value="<?php echo $userid; ?>" />
 <h4><?php echo $lang['create-note']; ?></h4><br />
 <strong><?php echo $lang['pur-date']; ?>:</strong>
<span id="dateshow">
 <strong>&nbsp;<?php echo date('d-m-Y'); ?></strong> 
 &nbsp;<a href="#" class="smallerfont orange" id="clickChange">[<?php echo $lang['change']; ?>]</a>
</span>
<div id="customDate" style="display: none;">
 <input type="number" lang="nb" name="day" id="day" class="twoDigit defaultinput" maxlength="2" placeholder="dd" />
 <input type="number" lang="nb" name="month" id="month" class="twoDigit defaultinput" maxlength="2" placeholder="mm" />
 <input type="number" lang="nb" name="year" id="year" class="fourDigit defaultinput" maxlength="4" placeholder="<?php echo $lang['member-yyyy']; ?>" />
 <a href="#" class="smallerfont orange" id="clickChange2">[<?php echo $lang['dispensary-today']; ?>]</a>
</div>

<textarea name="comment" placeholder="" style='width: 95%;'></textarea><br /><br />
<button class='oneClick okbutton2' name='oneClick' type="submit"><?php echo $lang['global-confirm']; ?>
 </form>
</div>
</div>
 
	 
<script>
	$("#clickChange").click(function () {
	$("#dateshow").css("display", "none");
	$("#customDate").css("display", "block");
	});	
	$("#clickChange2").click(function () {
	$("#customDate").css("display", "none");
	$("#dateshow").css("display", "inline");
	$("#day").val("");
	$("#month").val("");
	$("#year").val("");
	});	
</script>
	 
   
<?php displayFooter(); ?>
