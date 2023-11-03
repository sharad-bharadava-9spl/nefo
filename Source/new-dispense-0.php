<?php
	
	require_once 'cOnfig/connection.php';
	require_once 'cOnfig/view.php';
	require_once 'cOnfig/authenticate.php';
	require_once 'cOnfig/languages/common.php';
	
	session_start();
	$accessLevel = '3';
	
	// Authenticate & authorize
	authorizeUser($accessLevel);
	
	$domain = $_SESSION['domain'];
	
	getSettings();

	// Get the user ID
	if ($_POST['cardid'] != '') {
		
		$cardid = $_POST['cardid'];
		
		if ($cardid == '') {
			
				$_SESSION['errorMessage'] = $lang['scan-error'];
			
		} else {
		
			// Query to look up user
			$rowCount = $pdo3->query("SELECT COUNT(user_id) FROM users WHERE cardid = '$cardid'")->fetchColumn();
			
			if ($rowCount == 0) {
				// Query to look up user
				$rowCount = $pdo3->query("SELECT COUNT(user_id) FROM users WHERE cardid2 = '{$cardid}'")->fetchColumn();
				
				if ($rowCount == 0) {
					// Query to look up user
					$rowCount = $pdo3->query("SELECT COUNT(user_id) FROM users WHERE cardid3 = '{$cardid}'")->fetchColumn();
					
					if ($rowCount == 0) {
				   		handleError($lang['error-keyfob'],"");
					} else {
						$result = $pdo3->prepare("SELECT user_id FROM users WHERE cardid3 = '{$cardid}'");
					}
					
				} else {
					$result = $pdo3->prepare("SELECT user_id FROM users WHERE cardid2 = '{$cardid}'");
				}
	
				
			} else {
				$result = $pdo3->prepare("SELECT user_id FROM users WHERE cardid = '{$cardid}'");
			}
			
					
			$result->execute();
			
			$row = $result->fetch();
				$user_id = $row['user_id'];
				
			// Check if chip is registered more than once
			if ($rowCount > 1) {
				
				$_SESSION['errorMessage'] = $lang['chip-registered-more-than-once'];
				header("Location: duplicate-chip.php?cardid=$cardid");
				exit();
			
			}
		}
				
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
	$userDetails = "SELECT u.user_id, u.memberno, u.registeredSince, u.first_name, u.last_name, u.email, u.day, u.month, u.year, u.nationality, u.gender, u.dni, u.street, u.streetnumber, u.flat, u.postcode, u.city, u.country, u.telephone, u.mconsumption, u.usageType, u.signupsource, u.cardid, u.photoid, u.docid, u.doorAccess, u.friend, u.paidUntil, u.adminComment, ug.userGroup, ug.groupName, ug.groupDesc, u.form1, u.form2, datediff(curdate(),u.registeredSince) AS daysMember, u.paymentWarning, u.paymentWarningDate, u.credit, u.banComment, u.creditEligible, u.dniscan, u.workStation, u.photoext, u.dniext1, u.starCat, u.exento FROM users u, usergroups ug WHERE u.userGroup = ug.userGroup AND u.user_id = '{$user_id}'";
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
		$exento = $row['exento'];
		
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
	try
	{
		$results = $pdo3->prepare("SELECT noteid, notetime, userid, note, worker FROM usernotes WHERE userid = $user_id ORDER by notetime DESC");
		$results->execute();
	}
	catch (PDOException $e)
	{
			$error = 'Error fetching user1: ' . $e->getMessage();
			echo $error;
			exit();
	}
	
	if ($results->rowCount()) {
		$userNotes = $results->fetchAll();
	} else {
		$userNotes = '';
	}

	// Query to look up total sales and find weekly average
	$selectSales = "SELECT SUM(amount) FROM sales WHERE userid = $user_id";
		try
		{
			$result = $pdo3->prepare("$selectSales");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
		$totalAmount = $row['SUM(amount)'];
		$totalAmountPerDay = $totalAmount / $daysMember;
		$totalAmountPerWeek = $totalAmountPerDay * 7;
		

		
	


if ($_SESSION['userGroup'] > 1) {
	if (($userGroup < 4) && ($_SESSION['user_id'] != 10977)) {
		
		pageStart($lang['mini-profile'], NULL, $deleteNoteScript, "pminiprofile", NULL, $lang['mini-profileC'], $_SESSION['successMessage'], "Only Musso can dispense staff and workers!");
		exit();
		
	}
}
	
	pageStart($lang['mini-profile'], NULL, $deleteNoteScript, "pminiprofile", NULL, $lang['mini-profileC'], $_SESSION['successMessage'], $_SESSION['errorMessage']);
	
	$memberPhoto = "images/_$domain/members/" . $user_id . '.' .  $photoext;
	if (!file_exists($memberPhoto)) {
		$memberPhoto = 'images/silhouette.png';
	}
	

echo "
<center>
 <div id='profilearea'>
  <img src='$memberPhoto' />
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
	
	if ($userGroup == 5 && $_SESSION['membershipFees'] == 1 && $exento == 0) {  // show Member w/ expiry
	
		$memberExp = date('y-m-d', strtotime($paidUntil));
		$memberExpReadable = date('d M Y', strtotime($paidUntil));
		$timeNow = date('y-m-d');
		
		if (strtotime($memberExp) == strtotime($timeNow)) {
			echo "<div class='warningBox'><strong><a href='pay-membership.php?user_id=$user_id'><img src='images/exclamation-22.png' class='warningIcon' style='margin-bottom: -4px; margin-left: 7px; margin-right: 5px;' /> <span class='yellow'>" . $lang['member-expirestoday'] . "</span></a></strong>";
	  	} else if (strtotime($memberExp) > strtotime($timeNow)) {
		  	echo "<div class='warningBox'><strong><a href='pay-membership.php?user_id=$user_id' class='white'>" . $lang['member-memberuntil'] . ": $memberExpReadable</a></strong>";
		} else {
		  	echo "<div class='warningBox'><strong><a href='pay-membership.php?user_id=$user_id'><img src='images/exclamation-22.png' class='warningIcon' style='margin-bottom: -4px; margin-left: 7px; margin-right: 1px;' /> <span class='yellow'>" . $lang['member-expiredon'] . ": $memberExpReadable</span></a></strong>";
		  	
		  	if ($paymentWarning == '1') {
		  	echo "<br /><strong><a href='pay-membership.php?user_id=$user_id'><img src='images/exclamation-22.png' class='warningIcon' style='margin-bottom: -4px; margin-left: 7px;' /> <img src='images/exclamation-22.png' class='warningIcon' style='margin-bottom: -4px; margin-left: -15px; margin-right: 1px;' /> <span class='yellow'>" . $lang['member-receivedwarning'] . ": $paymentWarningDateReadable</span></a></strong>";
		  	}
		  	
		}
		
	} else {
		
		  	echo "<div class='warningBox'><center>" . $groupName;
		  	
			if ($exento == 1) {
				echo " (" . $lang['exempt'] . ")";
			}

		  	echo "</center>";
		
	}
	

	

	if ($usageType == '1') {
		echo "<br /><img src='images/medical-22.png' lass='warningIcon' style='margin-bottom: -3px; margin-left: 7px; margin-right: 2px;' /> <span class='yellow'>{$lang['medicinal-user']}</span>";
	}
	
	if (date('m-d') == date('m-d', strtotime($year . "-" . $month . "-" . $day . " 00:00:00"))) {
		echo "<br /><img src='images/cake-22.png' class='warningIcon' style='margin-bottom: -4px; margin-left: 7px; margin-right: 2px;' /> <span class='yellow'>{$lang['global-birthday']}</span>";
	}
	
	$file = "images/_$domain/ID/" . $user_id . '-front.' . $dniext1;

	// Retrieve system settings, to determine high roller and consumption %
	$selectSettings = "SELECT highRollerWeekly, consumptionPercentage FROM systemsettings";
		try
		{
			$result = $pdo3->prepare("$selectSettings");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
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
		try
		{
			$result = $pdo3->prepare("$selectSales");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
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
	
}

echo "<br /><center class='yellow' style='font-size: 16px;'><img src='images/flag.png' /> $nationality</center>";

echo "</div></div>
<div class='ctalinks'>";



	echo "<br />
 <a href='new-dispense-2.php?user_id=$user_id&resident=yes' style='font-size: 20px;'><br /><br />Resident<br />&nbsp;</a>
 <a href='new-dispense-2.php?user_id=$user_id&resident=no' style='font-size: 20px;'><br /><br />Non-resident<br />&nbsp;</a>
	</div></center>";
