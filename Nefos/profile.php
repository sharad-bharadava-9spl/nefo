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

if (isset($_POST['userSelect'])) {
		$user_id = $_POST['userSelect'];
	} else if (isset($_POST['user_id'])) {
		$user_id = $_POST['user_id'];
	} else if (isset($_GET['user_id'])) {
		$user_id = $_GET['user_id'];
	} else {
		handleError($lang['error-nouserid'],"");
	}
		
	// Query to look up user
	$userDetails = "SELECT u.user_id, u.memberno, u.registeredSince, u.first_name, u.last_name, u.email, u.day, u.month, u.year, u.nationality, u.gender, u.dni, u.street, u.streetnumber, u.flat, u.postcode, u.city, u.country, u.telephone, u.mconsumption, u.usageType, u.signupsource, u.cardid, u.photoid, u.docid, u.doorAccess, u.friend, u.friend2, u.paidUntil, u.adminComment, ug.userGroup, ug.groupName, ug.groupDesc, u.form1, u.form2, datediff(curdate(), u.registeredSince) AS daysMember, u.paymentWarning, u.paymentWarningDate, u.credit, u.banComment, u.creditEligible, u.dniscan, u.discount, u.discountBar, u.photoext, u.dniext1, u.dniext2, u.workStation, u.bajaDate FROM users u, usergroups ug WHERE u.userGroup = ug.userGroup AND u.user_id = '{$user_id}'";
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
	$friend2 = $row['friend2'];
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
	$discount = $row['discount'];
	$discountBar = $row['discountBar'];
	$photoext = $row['photoext'];
	$dniext1 = $row['dniext1'];
	$dniext2 = $row['dniext2'];
	$workStation = $row['workStation'];
	$bajaDate = date('d-m-y', strtotime($row['bajaDate']));
	$starCat = $row['starCat'];	
	$yearGroup = $row['yearGroup'];	
	$form = $row['form'];	
	$fingerprint = $row['fingerprint'];	
	$parent = $row['parent'];	
	$staffCat = $row['staffCat'];	
	
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



	$deleteNoteScript = <<<EOD
function delete_note(noteid, userid) {
	if (confirm("{$lang['confirm-deletenote']}")) {
				window.location = "uTil/delete-note.php?noteid=" + noteid + "&userid=" + userid;
				}
}

function delete_fingerprint(user_id) {
	
	if (confirm("Are you sure you want to delete this fingerprint? This action can not be undone!")) {
		window.location = "uTil/delete-fingerprint.php?user_id=" + user_id;
	}
}

function delete_member(user_id) {
	
	if (confirm("Are you sure you want to delete this person? This action can not be undone!")) {
		window.location = "uTil/delete-user.php?user_id=" + user_id;
	}
}


EOD;
	pageStart($lang['title-memberprofile'], NULL, $deleteNoteScript, "pprofilenew", NULL, $lang['member-profilecaps'], $_SESSION['successMessage'], $_SESSION['errorMessage']);

?>
<center>
 <span class="firstbuttons">
 
  <a href="edit-profile.php?user_id=<?php echo $user_id;?>" class="cta"><?php echo $lang['global-edit']; ?></a>
  <?php if ($userGroup < 4) { ?> <a href="new-password.php?user_id=<?php echo $user_id;?>" class="cta"><?php echo $lang['password']; ?></a> <?php } ?>
  <a href="javascript:delete_member(<?php echo $user_id;?>)" class="cta" style="background-color: red;">DELETE</a>

 </span>
  <br />
<br />
</center>

<div class="overview">
 <span class="profilefirst"><?php echo $first_name . " " . $last_name; ?> </span>
 <br />
 <br />
<div id="memberNotifications"> <span class="profilethird">
<?php 

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


	// If member is banned
	
	if ($userGroup == 4) {
		
		echo "$groupName: $yearGroup, $form";
		
	} else if ($userGroup == 5) {
		
		echo "$groupName";
		
	} else {
		
		echo "$groupName $staffCat";
		
	}
		
	


	
	



	echo "</span></div><span class='profilefourth'>";
	

?>
<br />
<?php 


?>
<br />
</span>
 </div> <!-- END OVERVIEW -->
 
  <div class="clearfloat"></div><br />
  
  
<div id="profileWrapper">

<div id="leftprofile">
 <div id="detailedinfo">
  <div id="leftpane">
<h4><?php echo $lang['member-personal']; ?></h4>
<?php echo $nationality; ?><br />
<?php echo $lang['global-birthday'] . ": " . $birthday; ?><br />

  </div> <!-- END LEFTPANE -->
  <div id="rightpane">

<h4><?php echo $lang['member-contactdetails']; ?></h4>
<?php echo $telephone; ?><br />
<a href="mailto:<?php echo $email; ?>"><?php echo $email; ?></a><br /><br />
<?php echo $street . " " . $streetnumber . " " . $flat; ?><br />
      <?php echo $postcode; ?> <?php echo $city; ?><br />
      <?php echo $country; ?><br /><br />
<!--<h4>System specifics</h4>
User ID: <?php echo $user_id; ?><br />
Signup source: <?php echo $signupsource; ?><br />
Card ID: <?php echo $cardid; ?><br />-->
  </div> <!-- END RIGHTPANE -->
 </div> <!-- END DETAILEDINFO -->

  <div id="userPreferences">
  <div id="leftpane">
<h4><?php echo $lang['member-preferences']; ?></h4>
<?php if ($favouriteCategory != '') { echo $lang['global-category']; ?>: <?php echo " Flor (" . number_format($percentage,0) . "%)"; } ?><br />
<?php if ($favourite1 != '') { ?> #1: <?php echo $favourite1 . " (" . number_format($quantity1,0) . " g)"; ?><br /> <?php } ?>
<?php if ($favourite2 != '') { ?> #2: <?php echo $favourite2 . " (" . number_format($quantity2,0) . " g)"; ?><br /> <?php } ?>
<?php if ($favourite3 != '') { ?>#3: <?php echo $favourite3 . " (" . number_format($quantity3,0) . " g)"; ?><br /> <?php } ?>
<?php if ($favourite4 != '') { ?>#4: <?php echo $favourite4 . " (" . number_format($quantity4,0) . " g)"; ?><br /> <?php } ?>
<?php if ($favourite5 != '') { ?>#5: <?php echo $favourite5 . " (" . number_format($quantity5,0) . " g)"; ?> <?php } ?>
	<br /><br />


  </div> <!-- END LEFTPANE -->
  <div id="rightpane">

<h4><?php echo $lang['member-weeklyavgs']; ?></h4>
Purchases: <?php echo number_format($totalDispensesPerWeek,0); ?><br />
<?php echo $lang['member-spenditure']; ?>: <?php echo number_format($totalAmountPerWeek,0); ?> &euro;<br /><br />

  </div> <!-- END RIGHTPANE -->
 </div> <!-- END DETAILEDINFO -->

  </div> <!-- END LEFTPROFILE -->
 
 
 <div id="statistics">
  <h4>Purchase history</h4>
  <table class="default memberStats">
   <tr>
    <td class="first"><?php echo $lang['dispensary-thisweek']; ?>:</td>
    <td><?php echo number_format($unitsWeek,0); ?> <span class="smallerfont">u.</span></td>
    <td><?php echo number_format($amountWeek,0); ?> <span class="smallerfont">&euro;</span></td>
   </tr>
   <tr>
    <td class="first"><?php echo $lang['dispensary-lastweek']; ?>:</td>
    <td><?php echo number_format($unitsWeekMinusOne,0); ?> <span class="smallerfont">u.</span></td>
    <td><?php echo number_format($amountWeekMinusOne,0); ?> <span class="smallerfont">&euro;</span></td>
   </tr>
   <tr>
    <td class="first"><?php echo $lang['dispensary-twoweeksago']; ?>:</td>
    <td><?php echo number_format($unitsWeekMinusTwo,0); ?> <span class="smallerfont">u.</span></td>
    <td><?php echo number_format($amountWeekMinusTwo,0); ?> <span class="smallerfont">&euro;</span></td>
   </tr>
   <tr>
    <td class="first"><?php echo date('F'); ?>:</td>
    <td><?php echo number_format($unitsMonth,0); ?> <span class="smallerfont">u.</span></td>
    <td><?php echo number_format($amountMonth,0); ?> <span class="smallerfont">&euro;</span></td>
   </tr>
   <tr>
    <td class="first"><?php echo date("F", strtotime("first day of last month")); ?>:</td>
    <td><?php echo number_format($unitsMonthMinus1,0); ?> <span class="smallerfont">u.</span></td>
    <td><?php echo number_format($amountMonthMinus1,0); ?> <span class="smallerfont">&euro;</span></td>
   </tr>
   <tr>
    <td class="first"><?php echo date("F", strtotime("-1 months", strtotime("first day of last month") )); ?>:</td>
    <td><?php echo number_format($unitsMonthMinus2,0); ?> <span class="smallerfont">u.</span></td>
    <td><?php echo number_format($amountMonthMinus2,0); ?> <span class="smallerfont">&euro;</span></td>
   </tr>
  </table>

 </div>
 </div> <!-- END PROFILEWRAPPER -->
 <br /><br />
 <?php displayFooter(); ?>
