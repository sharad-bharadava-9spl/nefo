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
	
	getSettings();

	// Get the user ID
	if (isset($_POST['user_id'])) {
		$user_id = $_POST['user_id'];
	} else if (isset($_GET['user_id'])) {
		$user_id = $_GET['user_id'];
	} else {
		handleError($lang['error-nouserid'],"");
	}

	// Query to look up user
	$userDetails = "SELECT u.user_id, u.memberno, u.registeredSince, u.first_name, u.last_name, u.email, u.day, u.month, u.year, u.nationality, u.gender, u.dni, u.passport, u.street, u.streetnumber, u.flat, u.postcode, u.city, u.country, u.telephone, u.mconsumption, u.usageType, u.signupsource, u.cardid, u.photoid, u.docid, u.doorAccess, u.friend, u.paidUntil, u.adminComment, ug.userGroup, ug.groupName, ug.groupDesc, u.form1, u.form2, datediff(curdate(),u.registeredSince) AS daysMember, u.paymentWarning, u.paymentWarningDate, u.credit, u.banComment FROM users u, usergroups ug WHERE u.userGroup = ug.userGroup AND u.user_id = '{$user_id}'";
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
		$passport = $row['passport'];
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
		$paymentWarning = $row['paymentWarning'];
		$paymentWarningDate = $row['paymentWarningDate'];
		$paymentWarningDateReadable = date('d M', strtotime($paymentWarningDate));
		$userCredit = $row['credit'];
		$banComment = $row['banComment'];
		
	
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
		

	pageStart("No puede dispensar", NULL, $deleteNoteScript, "pminiprofile", 'dev-align-center', "NO PUEDE DISPENSAR", $_SESSION['successMessage'], $_SESSION['errorMessage']);



echo "
<center>
 <div class='actionbox-np2'>
  <div class='boxcontent'>
  <img src='{$google_root}images/_{$_SESSION["domain"]}/members/$user_id.jpg' />
  <div class='mainboxheader'>#$memberno - $first_name $last_name</div>";
  
 	if ($userGroup < 5) {
		echo "<strong>" . $groupName . "</strong><br /><div class='warningBox'>";  
		
	// If member is banned
	} else if ($userGroup == 7) {
		
		// Banned 
		echo "<br /><span class='banDisplay'><span class='banHeader'>*** BANNED / EXPULSADO !! ***</span><br /><strong>Reason:</strong><br />" . $banComment . "</span>";
		
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
	} else if ($userGroup > 5) {
		echo "<br /><div class='warningBox'><img src='images/exclamation-22.png' class='warningIcon' style='margin-bottom: -4px; margin-left: 7px; margin-right: 3px;' /><span class='yellow'>" . $groupName . "</span>";
	}
	

	

	if ($usageType == '1') {
		echo "<br /><img src='images/medical-22.png' lass='warningIcon' style='margin-bottom: -3px; margin-left: 7px; margin-right: 2px;' /> <span class='yellow'>{$lang['medicinal-user']}</span>";
	}
	
	
	
}

echo "</div></div></div>
</center>";



		
// Begin CTAs

echo "
<div id='ctawrapper'>

 <a href='edit-profile.php?user_id=$user_id' class='miniedit'>{$lang['title-editprofile']}</a>
</div>";
