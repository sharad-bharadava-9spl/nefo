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

	// Get the user ID
	if ($_POST['cardid'] != '') {
		$cardid = $_POST['cardid'];
		$userDetails = "SELECT user_id FROM users WHERE cardid = '{$cardid}'";
		$userCheck = mysql_query($userDetails);
		if(mysql_num_rows($userCheck) == 0) {
	   		handleError($lang['error-keyfob'],"");
		}
	
		$result = mysql_query($userDetails)
			or handleError($lang['error-userload'],"Error loading user: " . mysql_error());
	
		$row = mysql_fetch_array($result);
		$user_id = $row['user_id'];
		
		// Add scan to scan history
		$scanTime = date('Y-m-d H:i:s'); // 	$purchaseDate = date('Y-m-d H:i:s'); ????
		
		$cardid = $_POST['cardid'];
		  $query = sprintf("INSERT INTO scanhistory (userid, scanTime, cardid, scanType) VALUES ('%d', '%s', '%s', '%d');",
		  $user_id, $scanTime, $cardid, '11');
		  
		mysql_query($query)
			or handleError($lang['error-savedata'],"Error inserting purchase: " . mysql_error());
		
	} else if (isset($_POST['userSelect'])) {
		$user_id = $_POST['userSelect'];
	} else if (isset($_POST['user_id'])) {
		$user_id = $_POST['user_id'];
	} else if (isset($_GET['user_id'])) {
		$user_id = $_GET['user_id'];
	} else {
		handleError($lang['error-nouserid'],"");
	}

	// Query to look up user
	$userDetails = "SELECT u.user_id, u.memberno, u.registeredSince, u.first_name, u.last_name, u.email, u.day, u.month, u.year, u.nationality, u.gender, u.dni, u.street, u.streetnumber, u.flat, u.postcode, u.city, u.country, u.telephone, u.mconsumption, u.usageType, u.signupsource, u.cardid, u.photoid, u.docid, u.doorAccess, u.friend, u.paidUntil, u.adminComment, ug.userGroup, ug.groupName, ug.groupDesc, u.form1, u.form2, datediff(curdate(),u.registeredSince) AS daysMember, u.paymentWarning, u.paymentWarningDate, u.credit, u.banComment, u.creditEligible, u.dniscan, u.workStation, u.photoext, u.dniext1, u.starCat FROM users u, usergroups ug WHERE u.userGroup = ug.userGroup AND u.user_id = '{$user_id}'";
	
	// Does user ID exist?
	$userCheck = mysql_query($userDetails);
	
	$result = mysql_query($userDetails)
		or handleError($lang['error-userload'],"Error loading user: " . mysql_error());
	
	if(mysql_num_rows($userCheck) == 0) {
		
   		handleError($lang['error-useridnotexist'],"");
   		
	}
	
	$row = mysql_fetch_array($result);
		$user_id = $row['user_id'];
		$memberno = $row['memberno'];
		$registeredSince = $row['registeredSince'];
		$membertime = date("M y", strtotime($registeredSince));
		$userGroup = $row['userGroup'];
		$groupName = $row['groupName'];
		$groupDesc = $row['groupDesc'];
		$first_name = $row['first_name'];
		$last_name = $row['last_name'];
		$email = $row['email'];
		$day = $row['day'];
		$month = $row['month'];
		$year = $row['year'];
		$nationality = $row['nationality'];
		$gender = $row['gender'];
		$dni = $row['dni'];
		$street = $row['street'];
		$streetnumber = $row['streetnumber'];
		$flat = $row['flat'];
		$postcode = $row['postcode'];
		$city = $row['city'];
		$country = $row['country'];
		$telephone = $row['telephone'];
		$mconsumption = $row['mconsumption'];
		$usageType = $row['usageType'];
		$signupsource = $row['signupsource'];
		$cardid = $row['cardid'];
		$photoid = $row['photoid'];
		$docid = $row['docid'];
		$doorAccess = $row['doorAccess'];
		$friend = $row['friend'];
		$paidUntil = $row['paidUntil'];
		$adminComment = $row['adminComment'];
		$daysMember = $row['daysMember'];
		$form1 = $row['form1'];
		$form2 = $row['form2'];
		$dniscan = $row['dniscan'];
		$paymentWarning = $row['paymentWarning'];
		$paymentWarningDate = $row['paymentWarningDate'];
		$paymentWarningDateReadable = date('d M', strtotime($paymentWarningDate));
		$userCredit = $row['credit'];
		$banComment = $row['banComment'];
		$creditEligible = $row['creditEligible'];
		$workStation = $row['workStation'];
		$photoext = $row['photoext'];
		$dniext1 = $row['dniext1'];
		$starCat = $row['starCat'];	
		
		if ($starCat == 1) {
	   		$userStar = "<img src='images/star-yellow.png'/>";
		} else if ($starCat == 2) {
	   		$userStar = "<img src='images/star-black.png' />";
		} else if ($starCat == 3) {
	   		$userStar = "<img src='images/star-green.png' />";
		} else if ($starCat == 4) {
	   		$userStar = "<img src='images/star-red.png' />";
		} else {
	   		$userStar = "";
		}
		
	
	// Look up notes
	$getNotes = "SELECT noteid FROM usernotes WHERE userid = $user_id";
	
	$userNotes = mysql_query($getNotes)
		or handleError($lang['error-userload'],"Error loading user: " . mysql_error());
	
	if (mysql_num_rows($userNotes) == 0) {
		
   		$userNotes = '';
   		
	}

	// Query to look up total sales and find weekly average
	$selectSales = "SELECT SUM(amount) FROM sales WHERE userid = $user_id";

	$result = mysql_query($selectSales)
		or handleError($lang['error-loadsales'],"Error loading sale from db: " . mysql_error());
		
	$row = mysql_fetch_array($result);
		$totalAmount = $row['SUM(amount)'];
		$totalAmountPerDay = $totalAmount / $daysMember;
		$totalAmountPerWeek = $totalAmountPerDay * 7;
		

		
		
if (($_SESSION['visitRegistration'] == 1) && ($_GET['visitRegistered'] != 'true')) {
	
	// Lookup user's last visit:
	$lastVisit = "SELECT visitNo, completed FROM newvisits WHERE userid = $user_id ORDER BY scanin DESC LIMIT 1";
	
	$result = mysql_query($lastVisit)
		or handleError($lang['error-userload'],"Error loading user: " . mysql_error());

	$row = mysql_fetch_array($result);
		$completed = $row['completed'];
		
		
	if (mysql_num_rows($result) == 0) {
		
	// First ever visit
	$visitTime = date('Y-m-d H:i:s');
	tzo();
	$visitTimeReadable = date('H:i');
		
		// No previous visit. Sign in user.
		$query = sprintf("INSERT INTO newvisits (userid, scanin) VALUES ('%d', '%s');",
		  $user_id, $visitTime);
		  
		mysql_query($query)
			or handleError($lang['error-savedata'],"Error saving opening: " . mysql_error());
			
	$_SESSION['successMessage'] = $lang['member-entered'] . " " . $visitTimeReadable . ".";
 		header("Location: mini-profile.php?user_id={$user_id}&visitRegistered=true");
		exit();
		
		
		
		
	} else if ($completed == 0) {
		
		
		
		
	// Lookup user's last visit:
	$lastVisit = "SELECT visitNo, scanin FROM newvisits WHERE userid = $user_id ORDER BY scanin DESC LIMIT 1";
	
	$result = mysql_query($lastVisit)
		or handleError($lang['error-userload'],"Error loading user: " . mysql_error());
		
	$row = mysql_fetch_array($result);
		$visitNo = $row['visitNo'];
		$scanin = $row['scanin'];
		
	$visitTime = date('Y-m-d H:i:s');
	tzo();
	$visitTimeReadable = date('H:i');
	
	// Determine duration
	$minutesOfVisit = round(abs(strtotime($scanin) - strtotime($visitTime)) / 60,2);

	$query = "UPDATE newvisits SET scanout = '$visitTime', duration = $minutesOfVisit, completed = 1 WHERE visitNo = $visitNo";
	
	mysql_query($query)
		or handleError($lang['error-savedata'],"Error saving opening: " . mysql_error());

	$_SESSION['successMessage'] = $lang['global-member'] . " #$memberno $first_name $last_name " . $lang['left-at'] . " " . $visitTimeReadable . ".";
	header("Location: index.php");
	
	exit();
			
	
	
	
	} else {
		
		
		
		
	$visitTime = date('Y-m-d H:i:s');
	tzo();
	$visitTimeReadable = date('H:i');
		
		// No previous visit. Sign in user.
		$query = sprintf("INSERT INTO newvisits (userid, scanin) VALUES ('%d', '%s');",
		  $user_id, $visitTime);
		  
		mysql_query($query)
			or handleError($lang['error-savedata'],"Error saving opening: " . mysql_error());
			
	$_SESSION['successMessage'] = $lang['member-entered'] . " " . $visitTimeReadable . ".";
 		header("Location: mini-profile.php?user_id={$user_id}&visitRegistered=true");
		exit();
	}
}
	pageStart($lang['mini-profile'], NULL, $deleteNoteScript, "pminiprofile", NULL, $lang['mini-profileC'], $_SESSION['successMessage'], $_SESSION['errorMessage']);



echo "
<center>
 <div id='profilearea'>
  <img src='images/members/$user_id.$photoext' />
  <h4>$userStar $memberno - $first_name $last_name</h4>";

	if ($userCredit < 0) {
		$userCreditDisplay = 0;
		$userClass = 'negative';
	} else {
		$userCreditDisplay = $userCredit;
	}
  
	if ($creditEligible == 1) {
		$creditEligibility = "*";
	} else {
		$creditEligibility = "";
	}

if ($_SESSION['creditOrDirect'] == 1) {

echo "<a href='donation-management.php?userid=" . $user_id . "'>
   <span class='creditDisplay'>
    Credit: <span class='creditAmount $userClass'>" . number_format($userCreditDisplay,2) . " &euro;$creditEligibility</span>
   </span>
  </a><br />";
  
}
  
 	if ($userGroup < 5) {
		echo "<strong>" . $groupName . "&nbsp;</strong>";
		
		if ($_SESSION['puestosOrNot'] == 1) {

			if ($workStation == 1 || $workStation == 6 || $workStation == 11 || $workStation == 16) {
				echo "<img src='images/profile-reception.png' />&nbsp;";
			}
			if ($workStation == 5 || $workStation == 6 || $workStation == 15 || $workStation == 16) {
				echo "<img src='images/profile-bar.png' />&nbsp;";
			}
			if ($workStation == 10 || $workStation == 11 || $workStation == 15 || $workStation == 16) {
				echo "<img src='images/profile-dispensary.png' />&nbsp;";
			}
			
		}

		echo "<br /><div class='warningBox'>";  
		
	// If member is banned
	} else if ($userGroup == 7) {
		
		// Banned 
		echo "<br /><span class='banDisplay'><span class='banHeader'>*** {$lang['bannedC']} !! ***</span><br /><strong>{$lang['reason']}:</strong><br />" . $banComment . "</span>";
		
	} else {
	
	if ($userGroup == 5 && $_SESSION['membershipFees'] == 1) {  // show Member w/ expiry
		$memberExp = date('y-m-d', strtotime($paidUntil));
		$memberExpReadable = date('d M Y', strtotime($paidUntil));
		$timeNow = date('y-m-d');
		
		if ($memberExp == $timeNow) {
			echo "<div class='warningBox'><strong><a href='pay-membership.php?user_id=$user_id'><img src='images/exclamation-22.png' class='warningIcon' style='margin-bottom: -4px; margin-left: 7px; margin-right: 5px;' /> <span class='yellow'>" . $lang['member-expirestoday'] . "</span></a></strong>";
	  	} else if ($memberExp > $timeNow) {
		  	echo "<div class='warningBox'><strong><a href='pay-membership.php?user_id=$user_id' class='white'>" . $lang['member-memberuntil'] . ": $memberExpReadable</a></strong>";
		} else {
		  	echo "<div class='warningBox'><strong><a href='pay-membership.php?user_id=$user_id'><img src='images/exclamation-22.png' class='warningIcon' style='margin-bottom: -4px; margin-left: 7px; margin-right: 1px;' /> <span class='yellow'>" . $lang['member-expiredon'] . ": $memberExpReadable</span></a></strong>";
		  	
		  	if ($paymentWarning == '1') {
		  	echo "<br /><strong><a href='pay-membership.php?user_id=$user_id'><img src='images/exclamation-22.png' class='warningIcon' style='margin-bottom: -4px; margin-left: 7px;' /> <img src='images/exclamation-22.png' class='warningIcon' style='margin-bottom: -4px; margin-left: -15px; margin-right: 1px;' /> <span class='yellow'>" . $lang['member-receivedwarning'] . ": $paymentWarningDateReadable</span></a></strong>";
		  	}
		  	
		}
	} else {
		  	echo "<div class='warningBox'><center><strong class='biggerfont2'>" . $groupName . "</strong></center>";
		
	}
	

	

	if ($usageType == 'Medicinal') {
		echo "<br /><img src='images/medical-22.png' lass='warningIcon' style='margin-bottom: -3px; margin-left: 7px; margin-right: 2px;' /> <span class='yellow'>{$lang['medicinal-user']}</span>";
	}
	
	if (date('m-d') == date('m-d', strtotime($year . "-" . $month . "-" . $day . " 00:00:00"))) {
		echo "<br /><img src='images/cake-22.png' class='warningIcon' style='margin-bottom: -4px; margin-left: 7px; margin-right: 2px;' /> <span class='yellow'>{$lang['global-birthday']}</span>";
	}
	
	$file = 'images/ID/' . $user_id . '-front.' . $dniext1;

	if (!file_exists($file)) {
    	echo "<br /><a href='new-id-scan.php?user_id=$user_id'><img src='images/exclamation-22.png' class='warningIcon' style='margin-bottom: -4px; margin-left: 7px; margin-right: 6px;' /><span class='yellow'>" . $lang['member-dninotscanned'] . "</span></a>";
	}
	
	// Retrieve system settings, to determine high roller and consumption %
	$selectSettings = "SELECT highRollerWeekly, consumptionPercentage FROM systemsettings";

	$settingsResult = mysql_query($selectSettings)
		or handleError($lang['error-loadsales'],"Error loading sale from db: " . mysql_error());
		
	$row = mysql_fetch_array($settingsResult);
		$highRollerWeekly = $row['highRollerWeekly'];
		$consumptionPercentage = $row['consumptionPercentage'] / 100;

	// Is the user a high roller?
	if ($totalAmountPerWeek >= $highRollerWeekly) {
		echo "<br /><img src='images/hi-roller.png' class='warningIcon' style='margin-bottom: -4px; margin-left: 7px; margin-right: 2px;' /> <span class='yellow'>High roller</span>";
	}
	
	if ($userNotes != '') {
		echo "<br /><a href='profile.php?user_id=$user_id&deleted=yes'><img src='images/exclamation-22.png' class='warningIcon' style='margin-bottom: -4px; margin-left: 7px; margin-right: 2px;' /><span class='yellow' id='showComment' style='cursor: pointer;'> {$lang['global-admincomment']}</span></a>";
	}
	
		// Consumption this calendar month
	$selectSales = "SELECT SUM(quantity), SUM(units), SUM(amount) FROM sales WHERE userid = $user_id AND MONTH(saletime) = MONTH(NOW()) AND YEAR(saletime) = YEAR(NOW())";

	$result = mysql_query($selectSales)
		or handleError($lang['error-loadsales'],"Error loading sale from db: " . mysql_error());
		
	$row = mysql_fetch_array($result);
		$amountMonth = $row['SUM(amount)'];
		$quantityMonth = $row['SUM(quantity)'];
		$unitsMonth = $row['SUM(units)'];
		
		if ($quantityMonth > $mconsumption) {
			$monthClass = 'negative2';
		}


	// Determine consumption status vs limit
	$consumptionDelta = $quantityMonth - $mconsumption;
	$consumptionDeltaPlus = 0 - $consumptionDelta;
	
	
	if ($quantityMonth >= $mconsumption) {
		echo "<br /><img src='images/exclamation-22.png' class='warningIcon' style='margin-bottom: -4px; margin-left: 7px; margin-right: 2px;' /> <span class='yellow'>" . $lang['member-conslimitexc'] . " (+$consumptionDelta g)</span>";
	} else if ($consumptionDeltaPlus < ($mconsumption * $consumptionPercentage)) {
		echo "<br /><img src='images/exclamation-22.png' class='warningIcon' style='margin-bottom: -4px; margin-left: 7px; margin-right: 2px;' /> <span class='yellow'>" . $lang['member-conslimitnear'] . " ($consumptionDeltaPlus g " . $lang['global-remaining'] . ")</span>";
	}
	
	$file3 = 'images/sigs/' . $user_id . '.png';

	if (!file_exists($file3)) {
    	echo "<br /><a href='new-signature.php?user_id=$user_id&mconsumption=$mconsumption'><img src='images/exclamation-22.png' class='warningIcon' style='margin-bottom: -4px; margin-left: 7px; margin-right: 6px;' /><span class='yellow'>" . $lang['signature-missing'] . "</span></a>";
	}
	
	
}

echo "</div></div>
</center><div id='ctawrapper'>";



if ($_SESSION['puestosOrNot'] == 1 && $_SESSION['userGroup'] > 1) {
	
	// Reception
	if ($_SESSION['workstation'] == 'reception') {
		
		if ($_SESSION['visitRegistration'] == 0) {
		
			// Lookup user's last visit:
			$lastVisit = "SELECT visitNo, completed FROM newvisits WHERE userid = $user_id ORDER BY scanin DESC LIMIT 1";
			
			$result = mysql_query($lastVisit)
				or handleError($lang['error-userload'],"Error loading user: " . mysql_error());
		
			$row = mysql_fetch_array($result);
				$completed = $row['completed'];		
		// Begin CTAs
		
			if (mysql_num_rows($result) == 0) {
				
				// First ever visit
				echo "
				<a href='uTil/user-signin.php?user_id=$user_id' class='minicta minienter' disabled>{$lang['index-signin']}</a>
				<div class='minicta fakeexit' style='background-color: #ddd;'>{$lang['sign-out']}</div><br />";
			} else if ($completed == 0) {
				
				// Last entry was a signin. Disable signin button.
				echo "
				<div class='minicta fakeenter' style='background-color: #ddd;'>{$lang['index-signin']}</div>
				<a href='uTil/user-signout.php?user_id=$user_id' class='miniexit'>{$lang['sign-out']}</a><br />";
				
			} else {
				echo "
				<a href='uTil/user-signin.php?user_id=$user_id' class='minicta minienter' disabled>{$lang['index-signin']}</a>
				<div class='minicta fakeexit' style='background-color: #ddd;'>{$lang['sign-out']}</div><br />";
			}
			
		}
			
		echo "
		 <a href='notes.php?userid=$user_id' class='mininote'>{$lang['add-note']}</a>
		 <a href='profile.php?user_id=$user_id' class='miniprofile'>{$lang['complete-profile']}</a>
		</div>";
		
		
	} else if ($_SESSION['workstation'] == 'bar') {
		
		echo "
		 <a href='bar-new-sale-2.php?user_id=$user_id' class='minibar'>{$lang['bar']}</a><br />
		 <a href='notes.php?userid=$user_id' class='mininote'>{$lang['add-note']}</a>
		 <a href='profile.php?user_id=$user_id' class='miniprofile'>{$lang['complete-profile']}</a>
		</div>";
		
	} else if ($_SESSION['workstation'] == 'dispensary') {
		
		echo "
		 <a href='new-dispense-2.php?user_id=$user_id' class='minidispense'>{$lang['global-dispense']}</a>
		 <a href='notes.php?userid=$user_id' class='mininote'>{$lang['add-note']}</a>
		 <a href='profile.php?user_id=$user_id' class='miniprofile'>{$lang['complete-profile']}</a>
		</div>";
		
	}
	
	
} else {

		
	if ($_SESSION['visitRegistration'] == 0) {
	
		// Lookup user's last visit:
		$lastVisit = "SELECT visitNo, completed FROM newvisits WHERE userid = $user_id ORDER BY scanin DESC LIMIT 1";
		
		$result = mysql_query($lastVisit)
			or handleError($lang['error-userload'],"Error loading user: " . mysql_error());
	
		$row = mysql_fetch_array($result);
			$completed = $row['completed'];		
	// Begin CTAs
	
		if (mysql_num_rows($result) == 0) {
			
			// First ever visit
			echo "
			<a href='uTil/user-signin.php?user_id=$user_id' class='minicta minienter' disabled>{$lang['index-signin']}</a>
			<div class='minicta fakeexit' style='background-color: #ddd;'>{$lang['sign-out']}</div><br />";
		} else if ($completed == 0) {
			
			// Last entry was a signin. Disable signin button.
			echo "
			<div class='minicta fakeenter' style='background-color: #ddd;'>{$lang['index-signin']}</div>
			<a href='uTil/user-signout.php?user_id=$user_id' class='miniexit'>{$lang['sign-out']}</a><br />";
			
		} else {
			echo "
			<a href='uTil/user-signin.php?user_id=$user_id' class='minicta minienter' disabled>{$lang['index-signin']}</a>
			<div class='minicta fakeexit' style='background-color: #ddd;'>{$lang['sign-out']}</div><br />";
		}
		
	}
		
	echo "
	 <a href='new-dispense-2.php?user_id=$user_id' class='minidispense'>{$lang['global-dispense']}</a>
	 <a href='bar-new-sale-2.php?user_id=$user_id' class='minibar'>{$lang['bar']}</a><br />
	 <a href='notes.php?userid=$user_id' class='mininote'>{$lang['add-note']}</a>
	 <a href='profile.php?user_id=$user_id' class='miniprofile'>{$lang['complete-profile']}</a>
	</div>";

}