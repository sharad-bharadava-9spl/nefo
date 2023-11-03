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
		$insertTime = date('Y-m-d H:i:s');
		$paymentTime = date('Y-m-d H:i:s');

	// From baja to socio
	if (($_POST['oldUserGroup'] == 9) && ($_POST['userGroup'] == 5)) {
		
		
		$memberInitials = strtoupper(substr($_POST['first_name'], 0,1)) . strtoupper(substr($_POST['last_name'], 0,1));
		$memberDigit = 1;
		
		$memberno = $memberInitials . $memberDigit;
		
		// Only assign new number if member has no number!
		if ($memberno == 0 || $memberno == '' || $memberno == NULL) {
			
			$memberMatch = 'false';
			
			while ($memberMatch == 'false') {
				
				// We've gotta check if the member number is available!
				$query = "SELECT memberno FROM users WHERE memberno = '$memberno'";
				
				$result = mysql_query($query)
					or handleError($lang['error-membershipnumberload'],"");
					
				if (mysql_num_rows($result) == 0) {
					
					$memberMatch = 'true';
					
				} else {
					
					// Means the number is taken, so increase by 1 and try again
					$memberDigit = $memberDigit + 1;
					$memberno = $memberInitials . $memberDigit;
					
				}
			}
			
		}
		
	} else {
		
		$memberno = $oldmemberno;
		
	}


	// Calculate Workstation access
	if ($_SESSION['userGroup'] == 1) {
		
		foreach($_POST['workStation'] as $workstationCheckbox) {
			
		    $workStation +=  $workstationCheckbox;
	
	  	}
	  	
  	} else {
	  	
	  	$workStation = $_POST['workStationTot'];
  	}
  		
	if ($memberno == '') {
		$memberno = $memberNumber;
	}
	
	// Alfanumeric member numbers BEGIN

		$updateUser = sprintf("UPDATE users SET memberno = '%s', userGroup = '%d', adminComment = '%s', first_name = '%s', last_name = '%s', email = '%s', day = '%d', month = '%d', year = '%d', nationality = '%s', gender = '%s', dni = '%s', street = '%s', streetnumber = '%d', flat = '%s', postcode = '%s', city = '%s', country = '%s', telephone = '%s', mconsumption = '%d', usageType = '%s', signupsource = '%s', cardid = '%s', photoid = '%d', docid = '%d', doorAccess = '%d', form1 = '%d', form2 = '%d', creditEligible = '%d', dniscan = '%d', workStation = '%d', starCat = '%d', interview = '%d', maxCredit = '%f' WHERE user_id = '%d';",
mysql_real_escape_string($memberno),
mysql_real_escape_string($userGroup),
mysql_real_escape_string($adminComment),
mysql_real_escape_string($first_name),
mysql_real_escape_string($last_name),
mysql_real_escape_string($email),
mysql_real_escape_string($day),
mysql_real_escape_string($month),
mysql_real_escape_string($year),
mysql_real_escape_string($nationality),
mysql_real_escape_string($gender),
mysql_real_escape_string($dni),
mysql_real_escape_string($street),
mysql_real_escape_string($streetnumber),
mysql_real_escape_string($flat),
mysql_real_escape_string($postcode),
mysql_real_escape_string($city),
mysql_real_escape_string($country),
mysql_real_escape_string($telephone),
mysql_real_escape_string($mconsumption),
mysql_real_escape_string($usageType),
mysql_real_escape_string($signupsource),
mysql_real_escape_string($cardid),
mysql_real_escape_string($photoid),
mysql_real_escape_string($docid),
mysql_real_escape_string($doorAccess),
mysql_real_escape_string($regform),
mysql_real_escape_string($consform),
mysql_real_escape_string($creditEligible),
mysql_real_escape_string($dniscan),
mysql_real_escape_string($workStation),
mysql_real_escape_string($starCat),
mysql_real_escape_string($interview),
mysql_real_escape_string($maxCredit),
mysql_real_escape_string($user_id)
);

	// Alfanumeric member numbers END
	
	/* NORMAL member numbers BEGIN
		// Query to update user - 28 arguments
		$updateUser = sprintf("UPDATE users SET memberno = '%s', userGroup = '%d', adminComment = '%s', first_name = '%s', last_name = '%s', email = '%s', day = '%d', month = '%d', year = '%d', nationality = '%s', gender = '%s', dni = '%s', street = '%s', streetnumber = '%d', flat = '%s', postcode = '%s', city = '%s', country = '%s', telephone = '%s', mconsumption = '%d', usageType = '%s', signupsource = '%s', cardid = '%s', photoid = '%d', docid = '%d', doorAccess = '%d', friend = '%s', form1 = '%d', form2 = '%d', creditEligible = '%d', dniscan = '%d', workStation = '%d', starCat = '%d' WHERE user_id = '%d';",
mysql_real_escape_string($memberno),
mysql_real_escape_string($userGroup),
mysql_real_escape_string($adminComment),
mysql_real_escape_string($first_name),
mysql_real_escape_string($last_name),
mysql_real_escape_string($email),
mysql_real_escape_string($day),
mysql_real_escape_string($month),
mysql_real_escape_string($year),
mysql_real_escape_string($nationality),
mysql_real_escape_string($gender),
mysql_real_escape_string($dni),
mysql_real_escape_string($street),
mysql_real_escape_string($streetnumber),
mysql_real_escape_string($flat),
mysql_real_escape_string($postcode),
mysql_real_escape_string($city),
mysql_real_escape_string($country),
mysql_real_escape_string($telephone),
mysql_real_escape_string($mconsumption),
mysql_real_escape_string($usageType),
mysql_real_escape_string($signupsource),
mysql_real_escape_string($cardid),
mysql_real_escape_string($photoid),
mysql_real_escape_string($docid),
mysql_real_escape_string($doorAccess),
mysql_real_escape_string($friend),
mysql_real_escape_string($regform),
mysql_real_escape_string($consform),
mysql_real_escape_string($creditEligible),
mysql_real_escape_string($dniscan),
mysql_real_escape_string($workStation),
mysql_real_escape_string($starCat),
mysql_real_escape_string($user_id)
);


	NORMAL member numbers END */

		mysql_query($updateUser)
			or handleError($lang['error-savedata'],"Error inserting user: " . mysql_error());
						
		// On success: redirect.
		
	// Compare old user group to new usergroup. Use a HIDDEN form value or something to pass along the old one? if they don't match then launch password function (but perhaps do that AFTER saving the user).
	if (($oldUserGroup > 3) && ($userGroup < 4)) {
		$_SESSION['successMessage'] = $lang['member-userupdated'];
		header("Location: new-password.php?user_id={$user_id}");
		exit();
	} else if (($oldUserGroup != 7) && ($userGroup == 7)) {
		// Banned
		pageStart($lang['member-editmember'], NULL, $validationScript, "pprofileban", NULL, $lang['member-editmember'] . ": " . $first_name . " " . $last_name, $_SESSION['successMessage'], $_SESSION['errorMessage']);
		
		
		$banWindow = <<<EOD
		<div class="banWindow">
<form id="registerForm" action="" method="POST" >
    <input type="hidden" name="user_id" value="$user_id" />
    <input type="hidden" name="banned" value="true" />

<span class="yellow">{$lang['member-banreason']}:</span><br />
<textarea name="banComment"></textarea>
 <button class='oneClick' name='oneClick' type="submit">{$lang['global-savechanges']}</button>
   </form>
   </div>
EOD;
		echo $banWindow;
		exit();
				
	} else if ($_POST['source'] == 'not_approved') {
	$user_id = $_POST['user_id'];	
		header("Location: profile-preview.php?user_id={$user_id}");
		exit();
	} else if ($_POST['intToReg'] == 'yes') {
		// $_SESSION['successMessage'] = "User added succesfully!";
		header("Location: profile-preview.php?user_id={$user_id}");
		exit();
	} else {
		$_SESSION['successMessage'] = "User updated succesfully!";
		header("Location: profile.php?user_id={$user_id}");
		exit();
	}
	}
	/***** FORM SUBMIT END *****/

	$validationScript = <<<EOD
    $(document).ready(function() {
	    
	    
	    // if 2 or 3 is selected, hide box.
		var initialVal = $('#userGroup').val();
			if(initialVal < 2 || initialVal > 3) {
	        	$("#expiryBox").hide();				
			}
	    	    
	    $('#userGroup').change(function(){
			var val = $(this).val();
		    if(val < 4 && val > 1) {
		        $("#expiryBox").fadeIn('slow');
	    	} else {
		        $("#expiryBox").fadeOut('slow');
	    	}
	    });
	    
	    // if 2 or 3 is selected, hide box.
		var initialVal2 = $('#creditEligible').val();
			if(initialVal2 == 0) {
	        	$("#maxCreditHolder").hide();				
			}
	    	    
	    $('#creditEligible').change(function(){
			var val = $(this).val();
		    if(val == 1) {
		        $("#maxCreditHolder").fadeIn('slow');
	    	} else {
		        $("#maxCreditHolder").fadeOut('slow');
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
	$userDetails = "SELECT u.user_id, u.memberno, u.registeredSince, u.first_name, u.last_name, u.email, u.day, u.month, u.year, u.nationality, u.gender, u.dni, u.street, u.streetnumber, u.flat, u.postcode, u.city, u.country, u.telephone, u.mconsumption, u.usageType, u.signupsource, u.cardid, u.photoid, u.docid, u.doorAccess, u.friend, u.paidUntil, u.adminComment, ug.userGroup, ug.groupName, ug.groupDesc, u.form1, u.form2, u.creditEligible, u.dniscan, u.discount, u.discountBar, u.photoext, u.workStation, u.starCat, u.interview, u.maxCredit FROM users u, usergroups ug WHERE u.userGroup = ug.userGroup AND u.user_id = '{$user_id}'";
	
	// Does user ID exist?
	$userCheck = mysql_query($userDetails);
	if(mysql_num_rows($userCheck) == 0) {
   		handleError($lang['error-useridnotexist'],"");
	}
			
	$result = mysql_query($userDetails)
		or handleError($lang['error-userload'],"Error loading user: " . mysql_error());
	
	if ($result) {
	$row = mysql_fetch_array($result);
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
	
	
	
	/* Normal member numbers BEGIN 

	$query = "select max(memberno) from users";

		$result = mysql_query($query)
		or handleError($lang['error-membershipnumberload'],"");
		$row = mysql_fetch_array($result);
		$nextMemberNo = $row['0'] + 1;
		
	Normal member numbers END */

	
} else {
		handle_error($lang['error-findinginfo'],"Error locating user with ID {$user_id}");
}

if ($userGroup == 6) {
	pageStart($lang['title-newmember'], NULL, $validationScript, "pprofile", NULL, $lang['member-newmember'] . ": " . $first_name . " " . $last_name, $_SESSION['successMessage'], $_SESSION['errorMessage']);
	
	

} else {
	pageStart($lang['member-editmember'], NULL, $validationScript, "pprofile", NULL, $lang['member-editprofile'] . ": " . $first_name . " " . $last_name, $_SESSION['successMessage'], $_SESSION['errorMessage']);
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
 
<span class="profilepicholder"><a href="new-picture.php?user_id=<?php echo $user_id; ?>" target="_blank"><img class="profilepic" src="images/members/<?php

echo $user_id . "." . $photoExt;
?>" /></a></span>

<table class='profileTable' style='text-align: left; margin: 0;>

<!--	/* Normal member numbers BEGIN -->

 <tr>
  <td><?php echo $lang['member-number']; ?></td>
  <td>
   <input type="number" lang="nb" id="memberno" class="twoDigit memberGroup" name="memberno" value="<?php echo $memberno; ?>" readonly /> <?php echo $lang['or']; ?>  
   <select name="memberNumber" id="memberNumber" class="memberGroup" style="width: 60px;">
    <option value=""></option>
<?php
	$sql = "SELECT memberno FROM users";
		$result = mysql_query($sql);
		
	while ($row = mysql_fetch_array($result)) {
   		$memberNumbers[] = $row['memberno'];
	}
	
	for ($i = 0; $i < $nextMemberNo; ++$i) {
		
		if (!in_array($i, $memberNumbers)) {
				echo "<option value='$i'>$i</option>";
    	}
	}
				echo "<option value='$nextMemberNo'>$nextMemberNo</option>";

?>
  </select>
  
<script>

$('#memberNumber').on('click keypress keyup blur', function() {
  if($(this).val() != ''){
    $('#memberno').val('');
  }
});

$('#memberno').on('click keypress keyup blur', function() {
  if($(this).val() != ''){
    $('#memberNumber').val('');
  }
});


</script>

  </td>
 </tr>

<!--	/* Normal member numbers END */ -->
 <tr>
  <td><strong><?php echo $lang['member-firstnames']; ?></strong></td>
  <td><input type="text" name="first_name" value="<?php echo $first_name; ?>" /></td>
 </tr>
 <tr>
  <td><strong><?php echo $lang['member-lastnames']; ?></strong></td>
  <td><input type="text" name="last_name" value="<?php echo $last_name; ?>" /></td>
 </tr>
 <tr>
  <td><strong><?php echo $lang['member-gender']; ?></strong></td>
  <td>
     <select name="gender">
      <?php if ($gender == NULL) { ?><option value=""><?php echo $lang['global-choose']; ?>:</option> <?php } ?>
	  <option value="Male" <?php if ($gender == 'Male') {echo "selected";} ?>><?php echo $lang['member-male']; ?></option>
	  <option value="Female" <?php if ($gender == 'Female') {echo "selected";} ?>><?php echo $lang['member-female']; ?></option>
     </select>
  </td>
 </tr>
 <tr>
  <td><strong><?php echo $lang['colour']; ?></strong></td>
  <td>
       <select name="starCat" id="starCat">
       
<?php 	if ($starColour == "") {
			echo "<option value='$starCat'>{$lang['colour']}</option>";
		} else {
			echo "<option value='$starCat'>$starCat - $starColour</option>";
		}
        
		echo "<option value='0'></option>";
		echo "<option value='1'>1 - {$lang['yellow']}</option>";
		echo "<option value='2'>2 - {$lang['black']}</option>";
		echo "<option value='3'>3 - {$lang['green']}</option>";
		echo "<option value='4'>4 - {$lang['red']}</option>";
 
?>

		
	   </select><br />
  </td>
 </tr>
 <tr>
  <td><strong><?php echo $lang['member-usergroup']; ?></strong></td>
  <td>
          <select name="userGroup" id="userGroup">
        <option value='<?php echo $userGroup; ?>'><?php echo $userGroup . ' - ' . $groupName; ?></option>
<?php
      
      	// Query to look up usergroups
      	
      	if ($_SESSION['userGroup'] < 2) {
			$selectGroups = "SELECT userGroup, groupName, groupDesc FROM usergroups ORDER by userGroup ASC";
		} else {
			$selectGroups = "SELECT userGroup, groupName, groupDesc FROM usergroups WHERE userGroup > 3 ORDER by userGroup ASC";
		}
		$result = mysql_query($selectGroups)
			or handleError($lang['error-usersload'],"Error loading users from db: " . mysql_error());
			
		while ($group = mysql_fetch_array($result)) {
			if ($group['userGroup'] != $userGroup) {
				$group_row = sprintf("<option value='%d'>%d - %s</option>",
	  								 $group['userGroup'], $group['userGroup'], $group['groupName']);
	  			echo $group_row;
  			}
  		}
?>
	   </select>
  </td>
 </tr>
<?php if ($_SESSION['puestosOrNot'] == 1) { ?>

	<tr id="expiryBox">
	 <td>
    <strong><?php echo $lang['access-level']; ?></strong>
     </td>
     <td>
    <input type="checkbox" name="workStation[]" value="1" style="width: 12px;" <?php if ($workStation == 1 || $workStation == 6 || $workStation == 11 || $workStation == 16) {echo "checked";} if ($_SESSION['userGroup'] != 1) { echo " disabled"; } ?>><?php echo $lang['reception']; ?></input><br />
    <input type="checkbox" name="workStation[]" value="5" style="width: 12px;" <?php if ($workStation == 5 || $workStation == 6 || $workStation == 15 || $workStation == 16 ) {echo "checked";} if ($_SESSION['userGroup'] != 1) { echo " disabled"; } ?>><?php echo $lang['bar']; ?></input><br />
    <input type="checkbox" name="workStation[]" value="10" style="width: 12px;" <?php if ($workStation == 10 || $workStation == 11 || $workStation == 15 || $workStation == 16 ) {echo "checked";} if ($_SESSION['userGroup'] != 1) { echo " disabled"; } ?>><?php echo $lang['dispensary']; ?><br />
    </td>
    </tr>
   
<?php } ?>

 <tr>
  <td><strong><?php echo $lang['dispense-without-credit']; ?></strong></td>
  <td>
     <select name="creditEligible" id="creditEligible" style='width: 60px;'>
	  <option value="0" <?php if ($creditEligible == 0) {echo "selected";} ?>><?php echo $lang['global-no']; ?></option>
	  <option value="1" <?php if ($creditEligible == 1) {echo "selected";} ?>><?php echo $lang['global-yes']; ?></option>
     </select>
     <span id='maxCreditHolder'>
     Hasta: <strong>-</strong><input type="text" id="maxCredit" name="maxCredit" class='twoDigit' value="<?php echo $maxCredit; ?>" />
     </span>
  </td>
 </tr>
 <tr>
  <td><strong><?php echo $lang['interviewed-member']; ?></strong></td>
  <td>
     <select name="interview">
	  <option value="0" <?php if ($interview == 0) {echo "selected";} ?>><?php echo $lang['global-no']; ?></option>
	  <option value="1" <?php if ($interview == 1) {echo "selected";} ?>><?php echo $lang['global-yes']; ?></option>
     </select>
  </td>
 </tr>
 <?php
/*
	if ($userGroup > 4 && $_SESSION['membershipFees'] == 1) {
		
		$memberExp = date('y-m-d', strtotime($paidUntil));
		$memberExpReadable = date('d M Y', strtotime($paidUntil));
		$timeNow = date('y-m-d');
	
		if ($memberExp == $timeNow) {
			echo "<tr><td colspan='2'><span class='mid biggerfont2'><strong>&raquo; " . $lang['member-expirestoday'] . "!</strong></span></td></tr>";
	  	} else if ($memberExp > $timeNow) {
		  	echo "<tr><td colspan='2'><span class='positive biggerfont2 white'>&raquo; " . $lang['member-memberuntil'] . ": $memberExpReadable</span></td></tr>";
		} else {
		  	echo "<tr><td colspan='2'><h4 class='mid biggerfont2'><strong>&raquo; " . $lang['member-expiredon'] . ": $memberExpReadable</strong></h4></td></tr>";
		}
	}
*/
?>

</table>
</span>
 </div> <!-- END OVERVIEW -->
  <div class="clearfloat"></div><br />
  <div id="profileWrapper">
 <div id="detailedinfo">
  <div id="leftpane">
<strong><?php echo $lang['member-personal']; ?></strong><br />
<input type="text" placeholder="<?php echo $lang['member-nationality']; ?>" name="nationality" value="<?php echo $nationality; ?>" /><br />
<input type="number" lang="nb" class="twoDigit" placeholder="dd" maxlength="2" name="day" value="<?php echo $day; ?>" />
     <input type="number" lang="nb" class="twoDigit" placeholder="mm" maxlength="2" name="month" value="<?php echo $month; ?>" />
     <input type="number" lang="nb" class="fourDigit" placeholder="<?php echo $lang['member-yyyy']; ?>" maxlength="4" name="year" value="<?php echo $year; ?>" /><br />
<input type="text" name="dni" id="dni" placeholder="<?php echo $lang['dni-or-passport']; ?>" value="<?php echo $dni; ?>" /><br /><br />
<?php

	if ($friend != '') {
		// look up cardid for friend
		$cardSelect = "SELECT cardid, memberno, first_name, last_name FROM users WHERE user_id = $friend";
			
		$cardResult = mysql_query($cardSelect)
			or handleError($lang['error-userload'],"Error loading user: " . mysql_error());
			
		$row = mysql_fetch_array($cardResult);
		
			$friendCardid = $row['cardid'];
			$memberno = $row['memberno'];
			$first_name = $row['first_name'];
			$last_name = $row['last_name'];
		
	}
	
?>
<strong><?php echo $lang['member-usage']; ?></strong><br />
     <select name="usageType">
      <?php if ($usageType == NULL) { ?><option value=""><?php echo $lang['global-select']; ?>:</option> <?php } ?>
	  <option value="0" <?php if ($usageType == '0') {echo "selected";} ?>><?php echo $lang['member-recreational']; ?></option>
	  <option value="1" <?php if ($usageType == '1') {echo "selected";} ?>><?php echo $lang['member-medicinal']; ?></option>
     </select>
     <br />
     <input type="text" class="twoDigit" name="mconsumption" value="<?php echo $mconsumption; ?>" /> <?php echo $lang['member-consumptiong']; ?><br />

  </div> <!-- END LEFTPANE -->
  <div id="rightpane">

<strong><?php echo $lang['member-contactdetails']; ?></strong><br />
<input type="text" placeholder="<?php echo $lang['member-telephone']; ?>" name="telephone" value="<?php echo $telephone; ?>" /><br />
<input type="text" placeholder="E-mail" name="email" value="<?php echo $email; ?>" /><br /><br />

     <input type="text" name="street" placeholder="<?php echo $lang['member-street']; ?>" value="<?php echo $street; ?>" />
     <input type="number" lang="nb" class="twoDigit" placeholder="No." name="streetnumber" value="<?php echo $streetnumber; ?>" />
     <input type="text" class="twoDigit" placeholder="<?php echo $lang['member-flat']; ?>" name="flat" value="<?php echo $flat; ?>" /><br />
     <input type="text" class="fourDigit" placeholder="<?php echo $lang['member-postcode']; ?>" name="postcode" value="<?php echo $postcode; ?>" />
     <input type="text" placeholder="<?php echo $lang['member-city']; ?>" name="city" value="<?php echo $city; ?>" />
     <input type="text" placeholder="<?php echo $lang['member-country']; ?>" name="country" value="<?php echo $country; ?>" /><br /><br />

  </div> <!-- END RIGHTPANE -->
 <div class="clearfloat"></div>
 
<center><strong><?php echo $lang['chip']; ?></strong><br />
<input maxlength="30" type="text" name="cardid" value="<?php echo $cardid; ?>" /><br />
</center>
 
 </div> <!-- END DETAILEDINFO -->
 <div id="statistics">

<center><a href="edit-discounts.php?user_id=<?php echo $user_id; ?>" class="cta"><?php echo $lang['discounts']; ?></a><br />
 </div>
 <div class="clearfloat"></div><br />
  </div> <!-- END PROFILEWRAPPER -->
 <br /><button class='oneClick' name='oneClick' type="submit"><?php echo $lang['global-savechanges']; ?></button>

 <?php	if ($source == 'not_approved') { ?>
<input type="hidden" name="source" value="<?php echo $source; ?>" />
<input type="hidden" name="user_id" value="<?php echo $user_id; ?>" />
<?php	} ?>
   </form>

<?php displayFooter(); ?>


<!-- When script submits, check to see if password+salt matches pw+salt in db. If yes, leave. If no, change. Hepp! 
Conversely: Leave Password out of the form, and replace with a link 'change password' -->
