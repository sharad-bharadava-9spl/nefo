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
	
	// Did this page re-submit with a form? If so, check & store details
	if (isset($_POST['first_name'])) {
		
		// If old level = Staff, new level = Staff/Parent:
		// Show family tree,
		// Then confirm
		
		/*
		
			- If parent registered at home, but no fingerprint, gotta register fingerprint
			- If registered & print, all good
			- If neither, register him here. You'll then have to apply changes to the parent portal
			- Choose child, search or pick
		
		*/
		
		$oldUserGroup = $_POST['oldUserGroup'];
		$userGroup = $_POST['userGroup'];
		$adminComment = $_POST['adminComment'];
		$first_name = trim($_POST['first_name']);
		$last_name = trim($_POST['last_name']);
		$email = $_POST['email'];
		$day = $_POST['day'];
		$month = $_POST['month'];
		$year = $_POST['year'];
		$nationality = $_POST['nationality'];
		$gender = $_POST['gender'];
		$dni = $_POST['dni'];
		$street = $_POST['street'];
		$streetnumber = $_POST['streetnumber'];
		$flat = $_POST['flat'];
		$postcode = $_POST['postcode'];
		$city = $_POST['city'];
		$country = $_POST['country'];
		$telephone = $_POST['telephone'];
		$mconsumption = $_POST['mconsumption'];
		$usageType = $_POST['usageType'];
		$signupsource = $_POST['signupsource'];
		$cardid = $_POST['cardid'];
		$photoid = $_POST['photoid'];
		$docid = $_POST['docid'];
		$doorAccess = $_POST['doorAccess'];
		$paidUntil = $_POST['paidUntil'];
		$origPaidUntil = $_POST['origPaidUntil'];
		$user_id      = $_POST['user_id'];
		$memberno      = $_POST['memberno'];
		$memberNumber = $_POST['memberNumber'];
		$oldmemberno = $_POST['oldmemberno'];
		$regform      = $_POST['regform'];
		$consform      = $_POST['consform'];
		$dniscan      = $_POST['dniscan'];
		$creditEligible  = $_POST['creditEligible'];
		$discount      = $_POST['discount'];
		$discountBar      = $_POST['discountBar'];
		$starCat      = $_POST['starCat'];
		$interview      = $_POST['interview'];
		$maxCredit      = $_POST['maxCredit'];
		$yearGroup      = $_POST['yearGroup'];
		$insertTime = date('Y-m-d H:i:s');
		$paymentTime = date('Y-m-d H:i:s');
		
		// First, delete from inddiscounts for this user_id, then set new values
		$deleteDiscounts = "DELETE FROM catdiscounts WHERE user_id = $user_id";
		try
		{
			$result = $pdo3->prepare("$deleteDiscounts")->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	

	
	// Alfanumeric member numbers BEGIN

		$updateUser = sprintf("UPDATE users SET userGroup = '%d', first_name = '%s', last_name = '%s', email = '%s', day = '%d', month = '%d', year = '%d', gender = '%s', discount = '%d', discountBar = '%d' WHERE user_id = '%d';",
2,
$first_name,
$last_name,
$email,
$day,
$month,
$year,
$gender,
$discount,
$discountBar,
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
		
	// Compare old user group to new usergroup. Use a HIDDEN form value or something to pass along the old one? if they don't match then launch password function (but perhaps do that AFTER saving the user).
	
	header("Location: profile.php?user_id={$user_id}");
		


		exit();

	}
	/***** FORM SUBMIT END *****/

	$validationScript = <<<EOD
    $(document).ready(function() {
	    
	    
	    // if 2 or 3 is selected, hide box.
		var initialVal = $('#userGroup').val();
			if(initialVal != 4) {
	        	$("#groupbox").hide();				
			}
	    	    
	    $('#userGroup').change(function(){
			var val = $(this).val();
		    if(val == 4) {
		        $("#groupbox").fadeIn('slow');
	    	} else {
		        $("#groupbox").fadeOut('slow');
	    	}
	    });
	    


  }); // end ready
EOD;
	
	// If REQ parameter set, check for admin rights to allow user to edit other users' profiles
	if (isset($_REQUEST['user_id'])) {
		if ($_SESSION['userGroup'] <= 3) {
			$user_id = $_REQUEST['user_id'];
		} else {
			handleError($lang['error-notauthorized']);
			exit();
		} // What if a user is trying to edit his own profile with a request ID? Well, they shouldn't??
	// ...this means user is trying to access his own profile
	} else if (isset($_SESSION['user_id'])) {
		$user_id = $_SESSION['user_id'];
	} else {
		handleError($lang['error-nouserid'],"");
	}
	
	// Query to look for user
	$userDetails = "SELECT u.user_id, u.memberno, u.registeredSince, u.first_name, u.last_name, u.email, u.day, u.month, u.year, u.nationality, u.gender, u.dni, u.street, u.streetnumber, u.flat, u.postcode, u.city, u.country, u.telephone, u.mconsumption, u.usageType, u.signupsource, u.cardid, u.photoid, u.docid, u.doorAccess, u.friend, u.paidUntil, u.adminComment, ug.userGroup, ug.groupName, ug.groupDesc, u.form1, u.form2, u.creditEligible, u.dniscan, u.discount, u.discountBar, u.photoext, u.workStation, u.starCat FROM users u, usergroups ug WHERE u.userGroup = ug.userGroup AND u.user_id = '{$user_id}'";
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
	$userGroup = $row['userGroup'];
	$groupName = $row['groupName'];
	$adminComment = $row['adminComment'];
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
	$form1 = $row['form1'];
	$form2 = $row['form2'];
	$creditEligible = $row['creditEligible'];
	$dniscan = $row['dniscan'];
	$discount = $row['discount'];
	$discountBar = $row['discountBar'];
	$photoExt = $row['photoext'];
	$workStation = $row['workStation'];	
	$starCat = $row['starCat'];	
	$interview = $row['interview'];	
	$maxCredit = $row['maxCredit'];	
	$yearGroup = $row['yearGroup'];	

	if ($starCat == 1) {
   		$starColour = $lang['yellow'];
	} else if ($starCat == 2) {
   		$starColour = $lang['black'];
	} else if ($starCat == 3) {
   		$starColour = $lang['green'];
	} else if ($starCat == 4) {
   		$starColour = $lang['red'];
	} else {
   		$starColour = "";
	}
	
if ($userGroup == 6) {
	pageStart($lang['title-newmember'], NULL, $validationScript, "pprofile", NULL, $lang['member-newmember'] . ": " . $first_name . " " . $last_name, $_SESSION['successMessage'], $_SESSION['errorMessage']);
	
	

} else {
	pageStart($lang['title-profile'], NULL, $validationScript, "pprofile", NULL, $lang['member-editprofile'] . ": " . $first_name . " " . $last_name, $_SESSION['successMessage'], $_SESSION['errorMessage']);
}
		
?>


   <form id="registerForm" action="" method="POST">
<?php if ($userGroup == 6) { ?>
    <input type="hidden" name="intToReg" value="yes" />
<?php } ?>

    <input type="hidden" name="user_id" value="<?php echo $user_id; ?>" />
    <input type="hidden" name="oldmemberno" value="<?php echo $memberno; ?>" />
    <input type="hidden" name="oldUserGroup" value="<?php echo $userGroup; ?>" />
    <input type="hidden" name="workStationTot" value="<?php echo $workStation; ?>" />
    
 <div class="overview">
 

<table class='profileTable' style='text-align: left; margin: 0;'>

<!--	/* Normal member numbers BEGIN -->

 <tr>
  <td><strong><?php echo $lang['member-firstnames']; ?></strong></td>
  <td><input type="text" name="first_name" value="<?php echo $first_name; ?>" /></td>
 </tr>
 <tr>
  <td><strong><?php echo $lang['member-lastnames']; ?></strong></td>
  <td><input type="text" name="last_name" value="<?php echo $last_name; ?>" /></td>
 </tr>

</table>
</span>
 </div> <!-- END OVERVIEW -->
  <div class="clearfloat"></div><br />
 <br /><button class='oneClick' name='oneClick' type="submit"><?php echo $lang['global-savechanges']; ?></button>

 <?php	if ($source == 'not_approved') { ?>
<input type="hidden" name="source" value="<?php echo $source; ?>" />
<input type="hidden" name="user_id" value="<?php echo $user_id; ?>" />
<?php	} ?>
   </form>

<?php displayFooter(); ?>


<!-- When script submits, check to see if password+salt matches pw+salt in db. If yes, leave. If no, change. Hepp! 
Conversely: Leave Password out of the form, and replace with a link 'change password' -->
