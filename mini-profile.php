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

	if ($_POST['newchip'] == 'yes') {
		
		$newcard = $_POST['newcard'];
		$user_id = $_GET['user_id'];
		
		$queryO = "UPDATE users SET cardid = '$newcard' WHERE user_id = $user_id";
		try
		{
			$result = $pdo3->prepare("$queryO")->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
			
		$_SESSION['successMessage'] = 'Chip updated / actualizado!';
	
	}
	
	
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
	$userDetails = "SELECT u.user_id, u.memberno, u.registeredSince, u.first_name, u.last_name, u.email, u.day, u.month, u.year, u.nationality, u.gender, u.dni, u.street, u.streetnumber, u.flat, u.postcode, u.city, u.country, u.telephone, u.mconsumption, u.usageType, u.signupsource, u.cardid, u.photoid, u.docid, u.doorAccess, u.friend, u.paidUntil, u.adminComment, ug.userGroup, ug.groupName, ug.groupDesc, u.form1, u.form2, datediff(curdate(),u.registeredSince) AS daysMember, u.paymentWarning, u.paymentWarningDate, u.credit, u.banComment, u.creditEligible, u.dniscan, u.workStation, u.photoext, u.dniext1, u.starCat, u.exento, u.sigext, u.usergroup2, u.fptemplate1, u.waive, u.entranceWarning, u.entranceWarningDate, u.entrancePaidUntil FROM users u, usergroups ug WHERE u.userGroup = ug.userGroup AND u.user_id = '{$user_id}'";
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
		$usergroup2 = $row['usergroup2'];
		$sigext = $row['sigext'];
		$fptemplate1 = $row['fptemplate1'];
		$waive = $row['waive'];
		$entranceWarning = $row['entranceWarning'];
		$entranceWarningDate = $row['entranceWarningDate'];
		$entrancePaidUntil = $row['entrancePaidUntil'];
		
		if ($sigext == '') {
			$sigext = 'png';
		}
	if ($usageType == 1) {
		$medicalicon = "<tr>
     <td><img src='images/medical-new.png' style='margin-bottom: -1px;' /></td>
     <td colspan='3'>{$lang['medical-user']}</td>
    </tr>";
	} else {
		$medicalicon = "";
	}
		
		if ($starCat == 1) {
	   		$userStar = "<img src='images/star-yellow.png'/>";
		} else if ($starCat == 2) {
	   		$userStar = "<img src='images/star-black.png' />";
		} else if ($starCat == 3) {
	   		$userStar = "<img src='images/star-green.png' />";
		} else if ($starCat == 4) {
	   		$userStar = "<img src='images/star-red.png' />";
		} else if ($starCat == 5) {
	   		$userStar = "<img src='images/star-purple.png' />";
		} else if ($starCat == 6) {
	   		$userStar = "<img src='images/star-blue.png' />";
		} else {
	   		$userStar = "";
		}
		
	if ($usergroup2 > 0) {
		
	try
	{
		$result = $pdo3->prepare("SELECT name FROM usergroups2 WHERE id = $usergroup2");
		$result->execute();
	}
	catch (PDOException $e)
	{
			$error = 'Error fetching user2: ' . $e->getMessage();
			echo $error;
			exit();
	}

	$row = $result->fetch();
		$groupName2 = $row['name'];
		
	}	
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
	

	// Query to look up user debt
	try
	{
		$result = $pdo3->prepare("SELECT SUM(amount), SUM(amountpaid) FROM sales WHERE userid = $user_id");
		$result->execute();
	}
	catch (PDOException $e)
	{
			$error = 'Error fetching user2: ' . $e->getMessage();
			echo $error;
			exit();
	}

	$row = $result->fetch();
		$amtTot = $row['SUM(amount)'];
		$amtPaid = $row['SUM(amountpaid)'];
		$amtOwed = $amtTot - $amtPaid;
			
	// Query to look up total sales and find weekly average
	try
	{
		$result = $pdo3->prepare("SELECT SUM(amount) FROM sales WHERE userid = $user_id");
		$result->execute();
	}
	catch (PDOException $e)
	{
			$error = 'Error fetching user3: ' . $e->getMessage();
			echo $error;
			exit();
	}

	$row = $result->fetch();
		$totalAmount = $row['SUM(amount)'];
		$totalAmountPerDay = $totalAmount / $daysMember;
		$totalAmountPerWeek = $totalAmountPerDay * 7;
		
		

		
//	if ($_POST['newchip'] != 'yes') {
	
		if ($_SESSION['domain'] == 'royalgreen') {
			$linkVar = "main.php";
		} else {
			$linkVar = "profile.php";
		}

		
	if ($_SESSION['visitRegistration'] == 1 && $_SESSION['user_id'] != 999999) {
	
		// Lookup user's last visit:
		$lastVisit = "SELECT visitNo, completed FROM newvisits WHERE userid = $user_id ORDER BY scanin DESC LIMIT 1";
			try
			{
				$result = $pdo3->prepare("$lastVisit");
				$result->execute();
				$data = $result->fetchAll();
			}
			catch (PDOException $e)
			{
					$error = 'Error fetching user: ' . $e->getMessage();
					echo $error;
					exit();
			}
			
		if (!$data) {
			
			// First ever visit
			$visitTime = date('Y-m-d H:i:s');
			tzo();
			$visitTimeReadable = date('H:i');
				
			// No previous visit. Sign in user.
			$query = sprintf("INSERT INTO newvisits (userid, scanin) VALUES ('%d', '%s');",
			  $user_id, $visitTime);
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
			$_SESSION['successMessage'] = $lang['member-entered'] . " " . $visitTimeReadable . ".";
	 		header("Location: $linkVar?user_id={$user_id}");
			exit();
			
		} else {

			$row = $data[0];
				$completed = $row['completed'];
				
			if ($completed == 0) {
					
				// Lookup user's last visit:
				$lastVisit = "SELECT visitNo, scanin FROM newvisits WHERE userid = $user_id ORDER BY scanin DESC LIMIT 1";
				try
				{
					$result = $pdo3->prepare("$lastVisit");
					$result->execute();
				}
				catch (PDOException $e)
				{
						$error = 'Error fetching user: ' . $e->getMessage();
						echo $error;
						exit();
				}
			
				$row = $result->fetch();
					$visitNo = $row['visitNo'];
					$scanin = $row['scanin'];
					
				$visitTime = date('Y-m-d H:i:s');
				tzo();
				$visitTimeReadable = date('H:i');
				
				// Determine duration
				$minutesOfVisit = round(abs(strtotime($scanin) - strtotime($visitTime)) / 60,2);
			
				$query = "UPDATE newvisits SET scanout = '$visitTime', duration = $minutesOfVisit, completed = 1 WHERE visitNo = $visitNo";
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
				$_SESSION['successMessage'] = $lang['member-entered'] . " " . $visitTimeReadable . ".";
			 		header("Location: $linkVar?user_id={$user_id}");
					exit();
			}
		
		}
		
	}

// }

	$deleteNoteScript = <<<EOD
	

	
function delete_note(noteid, userid) {
	if (confirm("{$lang['confirm-deletenote']}")) {
				window.location = "uTil/delete-note.php?mini&noteid=" + noteid + "&userid=" + userid;
				}
}

EOD;

	pageStart($lang['mini-profile'], NULL, $deleteNoteScript, "pprofilenew", NULL, $lang['mini-profileC'], $_SESSION['successMessage'], $_SESSION['errorMessage']);
	
	
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
	if ($_SESSION['showAge'] == 1) {
		$age = $age . " " . $lang['member-yearsold'];
	} else {
		$age = '';
	}

	if (date('m-d') == date('m-d', strtotime($year . "-" . $month . "-" . $day . " 00:00:00"))) {
		$bdayicon = "<tr>
     <td><img src='images/birthday-cake.png' style='margin-bottom: -2px;' /></td>
     <td colspan='3'><span style='color: #f6ae4a;'><strong>{$lang['global-birthday']}!</strong> ($age)</span> </td>
    </tr>";
	} else {
		$bdayicon = "<tr>
     <td><img src='images/birthday.png' style='margin-bottom: -2px;' /></td>
     <td colspan='3'>$birthday, $age</td>
    </tr>";
	}


	if ($domain == 'cloud') {
		
		$memberPhoto = 'images/_' . $_SESSION['domain'] . '/ID/' . $user_id . '-front.' .  $dniext1;
		
	} else {
		
		$memberPhoto = 'images/_' . $_SESSION['domain'] . '/members/' . $user_id . '.' .  $photoext;
	
	}

	
	if (!file_exists($memberPhoto)) {
		$memberPhoto = "<img class='profilepic' src='images/silhouette-new-big.png' />";
		$notexist = 'yes';
	} else {
		$memberPhoto = "<img class='profilepic' src='$memberPhoto' width='237' />";
	}
	
	if ($userGroup == 7) {
		$groupName = "<span class='usergrouptextbanned'>$groupName</span>";		
	} else if ($userGroup == 5 && $exento == '1') {
		$groupName = "<span class='usergrouptext'>{$lang['exempt']}</span>";		
	} else if ($userGroup == 5) {
		$memberExpReadable = date('d M Y', strtotime($paidUntil));
		$groupName = "<span class='usergrouptext'><a href='pay-membership.php?user_id=$user_id'>{$lang['member-memberuntil']} $memberExpReadable</a></span>";		
	} else {
		$groupName = "<span class='usergrouptext'>$groupName</span>";
		
	}
	if ($groupName2 != '') {
		$groupName2 = "<br /><span class='usergrouptext2'>$groupName2</span><br />";
	} else {
		$groupName2 = "<br />";
	}

	$groupName3 = '';
	if($waive == '1'){
		$groupName3 = "<span class='usergrouptext'>Member can enter without fee!</span><br />";
	}

		$highRollerWeekly = $_SESSION['highRollerWeekly'];
		$consumptionPercentage = $_SESSION['consumptionPercentage'] / 100;

	// Is the user a high roller?
	if ($totalAmountPerWeek >= $highRollerWeekly) {
		$highroller = "<br /><div class='highrollerholder'><img src='images/trophy.png' style='margin-bottom: -2px;'/> High roller</div>";
	} else {
		$highroller = ""; 
	}
?>	

<div id="mainbox">
 <div id="mainleft">
  <span id="profilepicholder"><a href="new-picture.php?user_id=<?php echo $user_id; ?>"><?php echo $memberPhoto; ?></a><?php echo $highroller; ?></span>
<?php

	echo <<<EOD
   <span class='firsttext'>#$memberno</span>&nbsp;&nbsp;<span class='nametext'>$userStar $first_name $last_name</span><br />
   $groupName
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
		
	echo "$groupName2";
	echo "$groupName3";

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

	echo "<br /><a href='donation-management.php?userid=" . $user_id . "'><span class='creditDisplay'>Credit: <span class='creditAmount $userClass'>" . number_format($userCreditDisplay,2) . " ".$_SESSION['currencyoperator']."$creditEligibility</span></span></a>";
		
    if ($_SESSION['saldoGift'] == 1) {
	    echo "&nbsp;<a href='gift-credit.php?user_id=$user_id' ><img src='images/gift-noframe.png' style='display: inline-block; margin-top: -3px; margin-left: 10px;'/></a>";
    }
    
    echo "<br /><br />";

		
	if ($_SESSION['showGender'] == 1) {
		if ($gender == 'Male') {
			$gender = $lang['member-male'];
			$gendericon = "<img src='images/gender.png' style='margin-bottom: -3px;' />";
		} else if ($gender == 'Female') {
			$gender = $lang['member-female'];
			$gendericon = "<img src='images/gender-female.png' style='margin-bottom: -3px;' />";
		} else {
			$gender = '';
		}
	} else {
		$gender = '';
	}
	

	echo <<<EOD
    <table class='smallinfo'>
$bdayicon $medicalicon
    <tr>
     <td><img src="images/new-flag.png" style="margin-bottom: -2px;" /></td>
     <td>$nationality</td>
     <td>$gendericon</td>
     <td>$gender</td>
    </tr>
   </table>
EOD;


?>
  
 </div>
 
<?php

	// Check for all warnings, if any warning found set warningflag = 1
	// if flag = 1, show the box, if not, do nothing
	if ($_SESSION['fingerprint'] == 1) {
	
		if ($fptemplate1 == '') {
		$warningbox .= <<<EOD
  <a href='jmu_create_user.php?user_id=$user_id' class='smallwarning finger'>
   {$lang['finger-not-registered']}
  </a>
EOD;

	    	$warningflag = 1;
		} else {
		$warningbox .= <<<EOD
  <a href='javascript:delete_fingerprint($user_id)' class='smallwarning finger'>
   {$lang['delete-finger']}
  </a>
EOD;
	    $warningflag = 1;
		}
		
	}
	

	
	$file = 'images/_' . $_SESSION['domain'] . '/ID/' . $user_id . '-front.' . $dniext1;
	$file2 = 'images/_' . $_SESSION['domain'] . '/ID/' . $user_id . '-back.' . $dniext2;
	$file3 = 'images/_' . $_SESSION['domain'] . '/sigs/' . $user_id . ".$sigext";

	if (!file_exists($file)) {
		$warningbox .= <<<EOD
  <a href='new-id-scan-front.php?user_id=$user_id' class='smallwarning dni'>
   <img src='images/exclamation-15.png' class='warningIcon' style='margin-bottom: -2px; margin-left: 7px; margin-right: 5px;' /> {$lang['member-dninotscanned']}
  </a>
EOD;
	    $warningflag = 1;
	}
	
	if (!file_exists($file3)) {
		$warningbox .= <<<EOD
  <a href='new-signature.php?user_id=$user_id&mconsumption=$mconsumption&usageType=$usageType' class='smallwarning signature'>
   <img src='images/exclamation-15.png' class='warningIcon' style='margin-bottom: -2px; margin-left: 7px; margin-right: 5px;' /> {$lang['signature-missing']}
  </a>
EOD;

	    $warningflag = 1;
	}
	
	
	if ($userNotes != '') {
		$i = 1;
	    $warningflag = 1;
		
		$warningbox .= <<<EOD
  <a class='smallwarning comment' href="#" id='adminComment' onClick="javascript:toggleDiv('userNotes'); return false;">
  <img src='images/exclamation-15.png' class='warningIcon' style='margin-bottom: -2px; margin-left: 7px; margin-right: 5px;' /> {$lang['comments']}
EOD;

	if ($_GET['deleted'] == 'yes' || isset($_GET['openComment'])) {
		$warningbox .= "<div id='userNotes'>";
	} else {
		$warningbox .= "<div id='userNotes' style='display: none;'>";
	}
	
		$warningbox .= <<<EOD
	 <table class="default">
  	  <tr>
  	   <th class="smallerfont" style='width: 120px;'><strong>{$lang['pur-date']}</strong></th>
  	   <th class="smallerfont" style='width: 120px;'><strong>{$lang['responsible']}</strong></th>
  	   <th class="smallerfont" colspan='2'><strong>{$lang['global-comment']}</strong></th>
	  </tr>
EOD;
		foreach ($userNotes as $userNote) {
	
			if ($userNote['notetime'] == NULL) {
				$formattedDate = '';
			} else {
				$formattedDate = date("d-m-y H:i", strtotime($userNote['notetime'] . "+$offsetSec seconds"));
			}
			$noteid = $userNote['noteid'];
			$note = $userNote['note'];
			$responsible = $userNote['worker'];
			$worker = getUser($responsible);
			
		if($i == $noteCount) {
	   		$warningbox .= <<<EOD
 <tr>
  <td style='border-bottom: 0;'>$formattedDate</td>
  <td style='border-bottom: 0;'>$worker</td>
  <td style='border-bottom: 0;'>$note</td>
  <td style='border-bottom: 0;'></td>
 </tr>
EOD;
		} else {
			$warningbox .= <<<EOD
 <tr>
  <td>$formattedDate</td>
  <td>$worker</td>
  <td>$note</td>
  <td><a href="javascript:void(0);" onclick= "delete_note($noteid,$user_id);" style='z-index: 22000;'><img src='images/delete.png' width='15' /></a></td>
 </tr>
EOD;
	}
			$warningbox .= <<<EOD
EOD;
	$i++;
		}
$warningbox .= "</table></div></a>";
	}
	
	// Check for short visits
	if ($_SESSION['entrysysstay'] > 0) {
		
		$minStay = $_SESSION['entrysysstay'];

	
		// Check for short visits
		$selectRows = "SELECT duration FROM newvisits WHERE userid = $user_id AND completed = 1 ORDER BY scanin DESC LIMIT 1";
		try
		{
			$result = $pdo3->prepare("$selectRows");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
			$duration = $row['duration'];
		
		// 
		if ($duration < $minStay && $duration > 0) {
		
	    $warningflag = 1;
		
		$warningbox .= <<<EOD
  <a class='smallwarning comment relativeitem' href="$linkVar?user_id=$user_id&openComment2">
  <img src='images/exclamation-15.png' class='warningIcon' style='margin-bottom: -2px; margin-left: 7px; margin-right: 5px;' /> {$lang['short-visits']}</a>
EOD;

	}
	
}
	
	if ($userGroup == 5 && $_SESSION['membershipFees'] == 1 && $exento == 0) {  // show Member w/ expiry
	
				$memberExp = date('y-m-d', strtotime($paidUntil));
				$memberExpReadable = date('d M Y', strtotime($paidUntil));
				$timeNow = date('y-m-d');
				
				if (strtotime($memberExp) == strtotime($timeNow)) {
				$warningbox .= <<<EOD
		  <a href='pay-membership.php?user_id=$user_id' class='bigwarning cuota'>
		  <img src='images/exclamation-15.png' class='warningIcon' style='margin-bottom: -2px; margin-left: 7px; margin-right: 5px;' />
		   {$lang['member-expirestoday']}
		  </a>
		EOD;
			    	$warningflag = 1;
			    	
				  	if ($paymentWarning == '1') {
				$warningbox .= <<<EOD
		   <a href='pay-membership.php?user_id=$user_id' class='bigwarning cuota'>
		   <img src='images/exclamation-15.png' class='warningIcon' style='margin-bottom: -2px; margin-left: 7px; margin-right: 5px;' /> <img src='images/exclamation-15.png' class='warningIcon' style='margin-bottom: -2px; margin-left: -14px; margin-right: 5px;' />
		   {$lang['member-receivedwarning']}: $paymentWarningDateReadable
		  </a>
		EOD;
					}

				} else if (strtotime($memberExp) < strtotime($timeNow)) {
					
				$warningbox .= <<<EOD
		   <a href='pay-membership.php?user_id=$user_id' class='bigwarning cuota'>
		  <img src='images/exclamation-15.png' class='warningIcon' style='margin-bottom: -2px; margin-left: 7px; margin-right: 5px;' /> 
		   {$lang['member-expiredon']}: $memberExpReadable
		  </a>
		EOD;

			    	$warningflag = 1;
			    	
				  	if ($paymentWarning == '1') {
				$warningbox .= <<<EOD
		   <a href='pay-membership.php?user_id=$user_id' class='bigwarning cuota'>
		   <img src='images/exclamation-15.png' class='warningIcon' style='margin-bottom: -2px; margin-left: 7px; margin-right: 5px;' /> <img src='images/exclamation-15.png' class='warningIcon' style='margin-bottom: -2px; margin-left: -14px; margin-right: 5px;' />
		   {$lang['member-receivedwarning']}: $paymentWarningDateReadable
		  </a>
		EOD;
			    	$warningflag = 1;
			    	
				  	}
				  	
				}
		
	}

	// show warning for entrance fee
	if ($_SESSION['entranceFees'] == 1 && $waive == 0) {  
	
				$entranceExp = date('y-m-d', strtotime($entrancePaidUntil));
				$entranceExpReadable = date('d M Y', strtotime($entrancePaidUntil));
				$timeNow = date('y-m-d');
				
				if ($entrancePaidUntil == '' || $entrancePaidUntil == null) {
					$warningbox .= <<<EOD
			  <a href='pay-entrance.php?user_id=$user_id' class='bigwarning cuota'>
			  <img src='images/exclamation-15.png' class='warningIcon' style='margin-bottom: -2px; margin-left: 7px; margin-right: 5px;' />
			   Entrance fee has not paid !
			  </a>
			EOD;
			}				

			else if (strtotime($entranceExp) == strtotime($timeNow)) {
					$warningbox .= <<<EOD
			  <a href='pay-entrance.php?user_id=$user_id' class='bigwarning cuota'>
			  <img src='images/exclamation-15.png' class='warningIcon' style='margin-bottom: -2px; margin-left: 7px; margin-right: 5px;' />
			   Entrance Fee will be expired today !
			  </a>
			EOD;
				    	
				    	
				} else if (strtotime($entranceExp) < strtotime($timeNow)) {
					
				$warningbox .= <<<EOD
		   <a href='pay-entrance.php?user_id=$user_id' class='bigwarning cuota'>
		  <img src='images/exclamation-15.png' class='warningIcon' style='margin-bottom: -2px; margin-left: 7px; margin-right: 5px;' /> 
		   Entrance fee has not paid or expired on: $entranceExpReadable
		  </a>
		EOD;

			    	
				  	
				}
		
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
		$warningbox .= <<<EOD
  <div class='bigwarning'>
  <a href='new-signature.php?user_id=$user_id&mconsumption=$mconsumption&usageType=$usageType'><img src='images/exclamation-15.png' class='warningIcon' style='margin-bottom: -2px; margin-left: 7px; margin-right: 5px;' /> {$lang['member-conslimitexc']} (+$consumptionDelta g)</a>
  </div>
EOD;
	    	$warningflag = 1;
	} else if ($consumptionDeltaPlus < ($mconsumption * $consumptionPercentage)) {
		$warningbox .= <<<EOD
  <div class='bigwarning'>
  <a href='new-signature.php?user_id=$user_id&mconsumption=$mconsumption&usageType=$usageType'><img src='images/exclamation-15.png' class='warningIcon' style='margin-bottom: -2px; margin-left: 7px; margin-right: 5px;' /> {$lang['member-conslimitnear']} ($consumptionDeltaPlus g {$lang['global-remaining']})</a>
  </div>
EOD;
	    	$warningflag = 1;
	}
	
	// Check for appointments
	$query = "SELECT * FROM appointments WHERE user_id = '$user_id' AND DATE(cita) = DATE(NOW()) AND fulfilled = 0";
	try
	{
		$result = $pdo3->prepare("$query");
		$result->execute();
		$data = $result->fetchAll();
	}
	catch (PDOException $e)
	{
			$error = 'Error fetching user: ' . $e->getMessage();
			echo $error;
			exit();
	}
		
	if ($data) {
		
		$warningbox .= <<<EOD
  <a href='appointments.php?user_id=$user_id' class='smallwarning'>
   <img src='images/exclamation-15.png' class='warningIcon' style='margin-bottom: -2px; margin-left: 7px; margin-right: 5px;' /> {$lang['cita-today']}
  </a>
EOD;
		
	    	$warningflag = 1;

	}

	// Check for preorders
	$query = "SELECT * FROM sales s, delivery d WHERE s.userid = '$user_id' AND d.saleid = s.saleid AND (s.puesto = 11 OR s.puesto = 22) AND DATE(d.time) = DATE(NOW()) AND s.dispensedFrom <> 1";
	try
	{
		$result = $pdo3->prepare("$query");
		$result->execute();
		$data = $result->fetchAll();
	}
	catch (PDOException $e)
	{
			$error = 'Error fetching user: ' . $e->getMessage();
			echo $error;
			exit();
	}
		
	if ($data) {
		
		$warningbox .= <<<EOD
  <a href='pre-orders.php?user_id=$user_id' class='smallwarning'>
   <img src='images/exclamation-15.png' class='warningIcon' style='margin-bottom: -2px; margin-left: 7px; margin-right: 5px;' /> {$lang['pre-order-today']}
  </a>
EOD;
		
	    	$warningflag = 1;
	    	
	}
	
	
	
	if ($warningflag == 1) {
		
		echo <<<EOD
 <div id="mainright">
$warningbox
 </div>
EOD;

	}
?>
 </div><center><div id='ctawrapper'>
	<?php

		// Lookup user's last entry in queue
		$lastQueue = "SELECT member_in FROM member_queue WHERE user_id = $user_id ORDER BY id DESC LIMIT 1";
		try
		{
			$queue_result = $pdo3->prepare("$lastQueue");
			$queue_result->execute();
			$queue_data = $queue_result->fetchAll();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}


	if(!$queue_data){	
	?>
		<a href="uTil/user-queue.php?user_id=<?php echo $user_id; ?>">Add to Queue</a>
	<?php }else{ ?>
		<div class='minicta fakeexit' style='background-color: #d0717e;'>Added to queue</div>
	<?php } ?>	
<?php
if ($_SESSION['domain'] == 'thebackyard') {
	
		if (!isset($_GET['finger'])) {
			echo "<br />
			<a href='uTil/user-signin2.php?user_id=$user_id' class='minicta minienter'>{$lang['index-signin']}</a>
			<br />";
		}
		
	echo "<br />
	 <a href='notes.php?userid=$user_id' class='mininote'>{$lang['add-note']}</a>
	 <a href='$linkVar?user_id=$user_id' class='miniprofile'>{$lang['complete-profile']}</a>
	</div>";
	
} else if ($_SESSION['domain'] == 'apatac') {
	
		if (!isset($_GET['finger'])) {
			echo "<br />
			<a href='uTil/user-signin.php?user_id=$user_id' class='minicta minienter'>{$lang['index-signin']}</a>
			<br />";
		}
		
	echo "<br />
	 <a href='notes.php?userid=$user_id' class='mininote'>{$lang['add-note']}</a>
	 <a href='$linkVar?user_id=$user_id' class='miniprofile'>{$lang['complete-profile']}</a>
	</div>";
	
} else if ($_SESSION['puestosOrNot'] == 1 && $_SESSION['userGroup'] > 1) {
	
	// Reception
	if ($_SESSION['workstation'] == 'reception') {
		
		if ($_SESSION['visitRegistration'] == 0) {
			
			// Lookup user's last visit:
			$lastVisit = "SELECT visitNo, completed FROM newvisits WHERE userid = $user_id ORDER BY scanin DESC LIMIT 1";
			try
			{
				$result = $pdo3->prepare("$lastVisit");
				$result->execute();
				$data = $result->fetchAll();
			}
			catch (PDOException $e)
			{
					$error = 'Error fetching user: ' . $e->getMessage();
					echo $error;
					exit();
			}
				
				
			// Begin CTAs
			if (!$data) {
				
				// First ever visit
				echo "
				<a href='uTil/user-signin.php?user_id=$user_id' class='minicta minienter'> <img src='images/enter.png' width='18' style='margin-bottom: -2px; margin-right: 5px;' />{$lang['index-signin']}</a>
				<div class='minicta fakeexit' style='background-color: #d0717e;'> <img src='images/exit.png' width='18' style='margin-bottom: -2px; margin-right: 5px;' />{$lang['sign-out']}</div>";
				
			} else {
				
				$row = $data[0];
					$completed = $row['completed'];
	
				if ($completed == 0) {
					
					// Last entry was a signin. Disable signin button.
					echo "
					<div class='minicta fakeenter' style='background-color: #d0717e;'> <img src='images/enter.png' width='18' style='margin-bottom: -2px; margin-right: 5px;' />{$lang['index-signin']}</div>
					<a href='uTil/user-signout.php?user_id=$user_id' class='miniexit'> <img src='images/exit.png' width='18' style='margin-bottom: -2px; margin-right: 5px;' />{$lang['sign-out']}</a>";
					
				} else {
					
					echo "
					<a href='uTil/user-signin.php?user_id=$user_id' class='minicta minienter'> <img src='images/enter.png' width='18' style='margin-bottom: -2px; margin-right: 5px;' />{$lang['index-signin']}</a>
					<div class='minicta fakeexit' style='background-color: #d0717e;'> <img src='images/exit.png' width='18' style='margin-bottom: -2px; margin-right: 5px;' />{$lang['sign-out']}</div>";
					
				}
				
			}
		}
			
		echo "<br />
		 <a href='notes.php?userid=$user_id' class='mininote'> <img src='images/notes.png' height='18' style='margin-bottom: -2px; margin-right: 5px;' />{$lang['add-note']}</a>
		 <a href='$linkVar?user_id=$user_id' class='miniprofile'>{$lang['complete-profile']}</a>
		</div>";
		
		
	} else if ($_SESSION['workstation'] == 'bar') {
		
		echo "<br />
		 <a href='bar-new-sale-2.php?user_id=$user_id' class='minibar'> <img src='images/main-baricon.png' height='18' style='margin-bottom: -2px; margin-right: 5px;' />{$lang['bar']}</a>
		 <a href='notes.php?userid=$user_id' class='mininote'> <img src='images/notes.png' height='18' style='margin-bottom: -2px; margin-right: 5px;' />{$lang['add-note']}</a>
		 <a href='$linkVar?user_id=$user_id' class='miniprofile'>{$lang['complete-profile']}</a>
		</div>";
		
	} else if ($_SESSION['workstation'] == 'dispensary') {
		
		echo "<br />
		 <a href='new-dispense-2.php?user_id=$user_id' class='minidispense'> <img src='images/main-dispense.png' height='18' style='margin-bottom: -2px; margin-right: 5px;' />{$lang['global-dispense']}</a>
		 <a href='notes.php?userid=$user_id' class='mininote'> <img src='images/notes.png' height='18' style='margin-bottom: -2px; margin-right: 5px;' />{$lang['add-note']}</a>
		 <a href='$linkVar?user_id=$user_id' class='miniprofile'>{$lang['complete-profile']}</a>
		</div>";
		
	}
	
	
} else {

		
	if ($_SESSION['visitRegistration'] == 0) {
	
		// Lookup user's last visit:
		$lastVisit = "SELECT visitNo, completed FROM newvisits WHERE userid = $user_id ORDER BY scanin DESC LIMIT 1";
		try
		{
			$result = $pdo3->prepare("$lastVisit");
			$result->execute();
			$data = $result->fetchAll();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
			
			
		// Begin CTAs
		if (!$data) {
			
			// First ever visit
			echo "
			<a href='uTil/user-signin.php?user_id=$user_id' class='minicta minienter'> <img src='images/enter.png' width='18' style='margin-bottom: -2px; margin-right: 5px;' />{$lang['index-signin']}</a>
			<div class='minicta fakeexit' style='background-color: #d0717e;'> <img src='images/exit.png' width='18' style='margin-bottom: -2px; margin-right: 5px;' />{$lang['sign-out']}</div>";
		} else {
			
			$row = $data[0];
				$completed = $row['completed'];

			if ($completed == 0) {
			
				// Last entry was a signin. Disable signin button.
				echo "
				<div class='minicta fakeenter' style='background-color: #d0717e;'> <img src='images/enter.png' width='18' style='margin-bottom: -2px; margin-right: 5px;' />{$lang['index-signin']}</div>
				<a href='uTil/user-signout.php?user_id=$user_id' class='miniexit'> <img src='images/exit.png' width='18' style='margin-bottom: -2px; margin-right: 5px;' />{$lang['sign-out']}</a>";
			
			} else {
				
				echo "
				<a href='uTil/user-signin.php?user_id=$user_id' class='minicta minienter'> <img src='images/enter.png' width='18' style='margin-bottom: -2px; margin-right: 5px;' />{$lang['index-signin']}</a>
				<div class='minicta fakeexit' style='background-color: #d0717e;'> <img src='images/exit.png' width='18' style='margin-bottom: -2px; margin-right: 5px;' />{$lang['sign-out']}</div>";
			}
		
		}
	}
		
	echo "<br />
	 <a href='new-dispense-2.php?user_id=$user_id' class='minidispense'> <img src='images/main-dispense.png' height='18' style='margin-bottom: -2px; margin-right: 5px;' />{$lang['global-dispense']}</a>
	 <a href='bar-new-sale-2.php?user_id=$user_id' class='minibar'> <img src='images/main-baricon.png' height='18' style='margin-bottom: -2px; margin-right: 5px;' />{$lang['bar']}</a>
	 <a href='notes.php?userid=$user_id' class='mininote'> <img src='images/notes.png' height='18' style='margin-bottom: -2px; margin-right: 5px;' />{$lang['add-note']}</a>
	 <a href='$linkVar?user_id=$user_id' class='miniprofile'>{$lang['complete-profile']}</a>
	</div>";

} ?>
<center><br />
<!--<form id="registerForm" action="" autocomplete="off" method="POST">
 <input type="text" name="newcard" autofocus value="" /><br />
 <input type="hidden" name="newchip" value="yes" />
<button name='oneClick' type="submit" style="visibility: hidden;"><?php echo $lang['form-accept']; ?></button>
</form>-->
</center>

<script>
	
function toggleDiv(divId) {
   $("#"+divId).toggle();
}	
</script>