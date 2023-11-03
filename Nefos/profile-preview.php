<?php

	require_once 'cOnfig/connection.php';
	require_once 'cOnfig/view.php';
	require_once 'cOnfig/authenticate.php';
	require_once 'cOnfig/languages/common.php';
	
	if ($_POST['action'] == 'submit') {
		$user_id = $_POST['user_id'];
		
		// Add scan to scan history
		$scanTime = date('Y-m-d H:i:s'); // 	$purchaseDate = date('Y-m-d H:i:s'); ????
		
	$cardid = $_POST['cardid'];
		  $query = sprintf("INSERT INTO scanhistory (userid, scanTime, cardid, scanType) VALUES ('%d', '%s', '%s', '%d');",
		  $user_id, $scanTime, $cardid, '6');
		  
		mysql_query($query)
			or handleError($lang['error-savedata'],"Error inserting scan history: " . mysql_error());
			
	// See if user is vol or admin, and then set pwd
	$userDetails = "SELECT userGroup FROM users WHERE user_id = '{$user_id}'";
	
	$resultUD = mysql_query($userDetails)
		or handleError($lang['error-userload'],"Error loading user: " . mysql_error());
	
	$row = mysql_fetch_array($resultUD);
		$userGroup = $row['userGroup'];

		$_SESSION['successMessage'] = $lang['member-memberaddedsuccessfully'];
		if ($userGroup < 4) {
			header("Location: new-password.php?user_id={$user_id}");
		} else {
			header("Location: profile.php?user_id={$user_id}");
			exit();
		}
	}
	
	
	session_start();
	$accessLevel = '5';
	
	// Authenticate & authorize
	authorizeUser($accessLevel);
	
	$user_id      = $_GET['user_id'];

	// Query to look up user
	$userDetails = "SELECT u.user_id, u.memberno, u.registeredSince, u.first_name, u.last_name, u.email, u.day, u.month, u.year, u.nationality, u.gender, u.dni, u.street, u.streetnumber, u.flat, u.postcode, u.city, u.country, u.telephone, u.mconsumption, u.usageType, u.signupsource, u.cardid, u.photoid, u.docid, u.doorAccess, u.friend, u.friend2, u.paidUntil, u.adminComment, u.photoext, ug.userGroup, ug.groupName, ug.groupDesc FROM users u, usergroups ug WHERE u.userGroup = ug.userGroup AND u.user_id = '{$user_id}'";
	
	// Does user ID exist?
	$userCheck = mysql_query($userDetails);
	if(mysql_num_rows($userCheck) == 0) {
   		handleError($lang['error-memberlookup'],"");
	}
	
	$result = mysql_query($userDetails)
		or handleError($lang['error-memberload'],"Error loading user: " . mysql_error());
		
	if ($result) {
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
	$friend2 = $row['friend2'];
	$paidUntil = $row['paidUntil'];
	$photoext = $row['photoext'];
	
	if (is_numeric($friend)) {
	// Look up friends name and number
	$friendDetails = "SELECT memberno, first_name, last_name FROM users WHERE user_id = $friend";
		
	$result = mysql_query($friendDetails)
		or handleError($lang['error-memberload'],"Error loading user: " . mysql_error());
		
	$row = mysql_fetch_array($result);
	$friendName = "#" . $row['memberno'] . " - " . $row['first_name'] . " " . $row['last_name'];
} else {
	$friendName = $friend;
}
	if (is_numeric($friend2)) {
	// Look up friends name and number
	$friendDetails = "SELECT memberno, first_name, last_name FROM users WHERE user_id = $friend2";
		
	$result = mysql_query($friendDetails)
		or handleError($lang['error-avalload'],"Error loading user: " . mysql_error());
		
	$row = mysql_fetch_array($result);
		$friendName2 = "#" . $row['memberno'] . " - " . $row['first_name'] . " " . $row['last_name'];
} else {
	$friendName2 = $friend2;
}
	
	
// Calculate Age:
$bdayraw = $day . "." . $month . "." . $year;
$bday = new DateTime($bdayraw);
$today = new DateTime(); // for testing purposes
$diff = $today->diff($bday);
$age = $diff->y;

$birthday = date("d M Y", strtotime($bdayraw));

	
} else {
		handle_error($lang['error-findinginfo'],"Error locating user with ID {$user_id}");
}

	pageStart($lang['title-profilepreview'], NULL, NULL, "pprofile", "preview", $lang['member-profilepreviewcaps'], $_SESSION['successMessage'], $_SESSION['errorMessage']);

?>


<div class="overview">
<span class="profilepicholder"><img class="profilepic" src="images/members/<?php echo $user_id . "." . $photoext ;?>" /></span>
<span class="profilefirst">#<?php echo $memberno . " - " . $first_name . " " . $last_name; ?> (<?php echo $membertime; ?>)</span>
<br />
<span class="profilesecond"><?php echo $gender . ", " . $age . " " . $lang['member-yearsold']; ?></span><br />
<span class="profilethird"><?php echo $groupName . " - " . $usageType . " " . $lang['global-usersmall']; ?></span><br />
<!--<span class="profilefourth"><?php 
	  if ($doorAccess == 0) {
		  echo "<span class='negative'>No door access</span>";
} else if ($doorAccess == 1) {
		  echo "<span class='positive'>Door access</span>";
}
?>
<br />-->
<?php 
		$memberExp = date('y-m-d', strtotime($paidUntil));
		$memberExpReadable = date('d M Y', strtotime($paidUntil));
		$timeNow = date('y-m-d');

	if ($userGroup > 4) {
		
		if ($memberExp == $timeNow) {
			echo "<span class='mid'>" . $lang['member-expirestoday'] . "</span>";
	  	} else if ($memberExp > $timeNow) {
		  	echo "<span class='yellow'>" . $lang['member-memberuntil'] . ": $memberExpReadable</span>";
		} else {
		  	echo "<span class='negative'>" . $lang['member-expiredon'] . ": $memberExpReadable</span>";
		}
	}
	
?><br /></span>
<?php if ($adminComment) {
echo "<span class='negative profilefifth'>" . $lang['global-admincomment'] . ":</span><br />";
echo $adminComment;
}
?>
 </div> <!-- END OVERVIEW -->
  <div class="clearfloat"></div><br />
  <div id="profileWrapper">
 <div id="detailedinfo">
  <div id="leftpane">
<strong><?php echo $lang['member-personal']; ?></strong><br />
<?php echo $nationality; ?><br />
<?php echo $lang['global-birthday'] . ": " . $birthday; ?><br />
<?php
	echo $lang['dni-or-passport'] . ": " . $dni;
	?><br />
<?php if ($friend) {
	echo $lang['member-referredby'] . ":<br />" . $friendName . "<br />" . $friendName2 . "<br />";
} ?>
	<br /><br />

<strong><?php echo $lang['member-usage']; ?></strong><br />
<?php echo $lang['global-type']; ?>: <?php echo $usageType; ?><br />
<?php echo $lang['member-monthcons']; ?>: <?php echo $mconsumption; ?><br /><br />

  </div> <!-- END LEFTPANE -->
  <div id="rightpane">

<strong><?php echo $lang['member-contactdetails']; ?></strong><br />
<?php echo $telephone; ?><br />
<a href="mailto:<?php echo $email; ?>"><?php echo $email; ?></a><br /><br />
<?php echo $street . " " . $streetnumber . " " . $flat; ?><br />
      <?php echo $postcode; ?> <?php echo $city; ?><br />
      <?php echo $country; ?><br /><br />
<!--<strong>System specifics</strong><br />
User ID: <?php echo $user_id; ?><br />
Signup source: <?php echo $signupsource; ?><br />
Card ID: <?php echo $cardid; ?><br />-->
  </div> <!-- END RIGHTPANE -->
 </div> <!-- END DETAILEDINFO -->
 <div id="statistics">
<form onsubmit='oneClick.disabled = true; return true;' id="registerForm" action="" method="POST">
 <input type="hidden" name='action' value='submit'>
 <input type="hidden" name='user_id' value='<?php echo $user_id; ?>'>
 <input type="text" name="cardid" maxlength="10" autofocus placeholder="<?php echo $lang['global-scantoconfirm']; ?>" /><br />
<button name='oneClick' type="submit"><?php echo $lang['form-accept']; ?></button>
</form>
<center><a class="cta" href="edit-profile.php?user_id=<?php echo $user_id; ?>&source=not_approved"><?php echo $lang['form-makechanges']; ?></a></center>
 </div>
</div> <!-- END PROFILEWRAPPER -->

<?php displayFooter(); ?>
