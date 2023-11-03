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
		

		// Lookup friend details (userid) and use this as $friend
		if (isset($_POST['friendcardid']) && $_POST['friendcardid'] != '') {
			$friendcardid = $_POST['friendcardid'];
			$userDetails = "SELECT user_id FROM users WHERE cardid = '{$friendcardid}'";
	
			$result = mysql_query($userDetails)
				or handleError($lang['error-userload'],"Error loading user: " . mysql_error());
	
			$row = mysql_fetch_array($result);
			$friend = $row['user_id'];
		} else {
			$friend = $_POST['friend'];	
		}


	$memberno = $_POST['memberno'];
	$nextMemberNo = $_POST['nextMemberNo'];
	$memberNumber = $_POST['memberNumber'];
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
	$regform = $_POST['regform'];
	$consform = $_POST['consform'];
	$dniscan = $_POST['dniscan'];
	$cardid = $_POST['cardid'];
	$photoid = $_POST['photoid'];
	$docid = $_POST['docid'];
	$doorAccess = $_POST['doorAccess'];
	$paidUntil = $_POST['paidUntil'];
	$creditEligible = $_POST['creditEligible'];
	$insertTime = date('Y-m-d H:i:s');
	$paymentTime = date('Y-m-d H:i:s');	
	$tempMemberNo = $_SESSION['tempNo'];
	$paidTo = $_POST['paidTo'];
	
	
	$memberInitials = strtoupper(substr($first_name, 0,1)) . strtoupper(substr($last_name, 0,1));
	$memberDigit = 1;
	
	$memberno = $memberInitials . $memberDigit;
	
	
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
	
	
	$domainCheck = "SELECT domain FROM systemsettings";
	
	$dC = mysql_query($domainCheck);
	
	$row = mysql_fetch_array($dC);
		$siteDomain = $row['domain'];

	/* Normal member numbers BEGIN
	
	// If a number was selected from the drop-down, there is no 'memberno', so we use the dropdown member number.
	if ($memberno == '') {
		$memberno = $memberNumber;
	}
	
		
	// We've gotta check if the member number is still available!
	$query = "SELECT memberno FROM users WHERE memberno = $memberno";
	
	$result = mysql_query($query)
		or handleError($lang['error-membershipnumberload'],"");
		
	if (mysql_num_rows($result) > 0) {
		
		// Means the number is taken, so use highest free memberno
		$query = "SELECT max(memberno) FROM users";
	
		$result = mysql_query($query)
			or handleError($lang['error-membershipnumberload'],"");
			
		$row = mysql_fetch_array($result);
			$oldmemberno = $memberno;
			$memberno = $row['0'] + 1;
			
		// Create feedback message saying that hte memberno was taken, and that we've assigned $memberno to this member.
		$_SESSION['errorMessage'] = $lang['number-taken-1'] . $oldmemberno . $lang['number-taken-2'] . $memberno . $lang['number-taken-3'];
	}


	Normal member numbers END */
   
	if ($paidUntil == 0) {
		$memberExp = $paymentTime;
		$amountPaid = 0;
	} else if ($paidUntil == 1) {
		$memberExp = date('Y-m-d H:i:s', strtotime("+180 day", strtotime(date('Y-m-d H:i:s'))));
		$amountPaid = 10;
	} else if ($paidUntil == 2) {
		$memberExp = date('Y-m-d H:i:s', strtotime("+365 day", strtotime(date('Y-m-d H:i:s'))));
		$amountPaid = 20;
	}

		// Query to add new user - 28 arguments
		  $query = sprintf("INSERT INTO users (registeredSince, memberno, userGroup, first_name, last_name, email, day, month, year, nationality, gender, dni, street, streetnumber, flat, postcode, city, country, telephone, mconsumption, usageType, signupsource, cardid, photoid, docid, doorAccess, friend, paidUntil, form1, form2, creditEligible, dniscan, dniext1, dniext2, photoext, domain) VALUES ('%s', '%s', '%d', '%s', '%s', '%s', '%d', '%d', '%d', '%s', '%s', '%s', '%s', '%d', '%s', '%s', '%s', '%s', '%s', '%d', '%s', '%s', '%s', '%d', '%d', '%d', '%s', '%s', '%d', '%d', '%d', '%d', '%s', '%s', '%s', '%s');",
mysql_real_escape_string($insertTime),
mysql_real_escape_string($memberno),
mysql_real_escape_string($userGroup),
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
mysql_real_escape_string($memberExp),
'1',
'1',
mysql_real_escape_string($creditEligible),
mysql_real_escape_string($dniscan),
mysql_real_escape_string($_SESSION['dnifrontextension']),
mysql_real_escape_string($_SESSION['dnibackextension']),
mysql_real_escape_string($_SESSION['userpicextension']),
mysql_real_escape_string($siteDomain));
		  
		mysql_query($query)
			or handleError($lang['error-savedata'],"Error inserting user: " . mysql_error());
	$user_id = mysql_insert_id();
		
	// Query to add payment
	if ($paidUntil > 0) {
		
		$query = sprintf("INSERT INTO memberpayments (paymentdate, userid, amountPaid, newExpiry, paidTo) VALUES ('%s', '%d', '%f', '%s', '%d');",
		$paymentTime, $user_id, $amountPaid, $memberExp, $paidTo);
		  			
		mysql_query($query)
			or handleError($lang['error-savedata'],"Error inserting user: " . mysql_error());
			
		$query = sprintf("INSERT INTO f_memberpayments (paymentdate, userid, amountPaid, newExpiry, paidTo) VALUES ('%s', '%d', '%f', '%s', '%d');",
		$paymentTime, $user_id, $amountPaid, $memberExp, $paidTo);
		  			
		mysql_query($query)
			or handleError($lang['error-savedata'],"Error inserting user: " . mysql_error());
			
	}
			
			
	// Rename the member photo, signature and DNI scans from temp number to real number
	$oldfile = 'images/members/' . $tempMemberNo . '.' . $_SESSION['userpicextension'];
	$newfile = 'images/members/' . $user_id . '.' . $_SESSION['userpicextension'];
	rename($oldfile, $newfile);
	
	$oldfile2 = 'images/ID/' . $tempMemberNo . '-front.' . $_SESSION['dnifrontextension'];
	$newfile2 = 'images/ID/' . $user_id . '-front.' . $_SESSION['dnifrontextension'];
	rename($oldfile2, $newfile2);
	
	$oldfile3 = 'images/ID/' . $tempMemberNo . '-back.' . $_SESSION['dnibackextension'];
	$newfile3 = 'images/ID/' . $user_id . '-back.' . $_SESSION['dnibackextension'];
	rename($oldfile3, $newfile3);
	
	$oldfile4 = 'images/sigs/' . $tempMemberNo . '.png';
	$newfile4 = 'images/sigs/' . $user_id . '.png';
	rename($oldfile4, $newfile4);
	
	
		// Write to log
		$logTime = date('Y-m-d H:i:s');
	
		$query = sprintf("INSERT INTO log (logtype, logtime, user_id, operator, amount, newExpiry) VALUES ('%d', '%s', '%d', '%d', '%f', '%s');",
		12, $logTime, $user_id, $_SESSION['user_id'], $amountPaid, $memberExp);
		
		mysql_query($query)
			or handleError($lang['error-savedata'],"Error inserting purchase: " . mysql_error());

		$query = sprintf("INSERT INTO f_log (logtype, logtime, user_id, operator, amount, newExpiry) VALUES ('%d', '%s', '%d', '%d', '%f', '%s');",
		12, $logTime, $user_id, $_SESSION['user_id'], $amountPaid, $memberExp);
		
		mysql_query($query)
			or handleError($lang['error-savedata'],"Error inserting purchase: " . mysql_error());


			
		// On success: redirect.
		// $_SESSION['successMessage'] = "User added succesfully!";
		header("Location: profile-preview.php?user_id=" . $user_id);
		exit();
	}
	/***** FORM SUBMIT END *****/

	$validationScript = <<<EOD
    $(document).ready(function() {
	    
	    // if 2 or 3 is selected, hide box.
		var initialVal = $('#paidUntil').val();
			if(initialVal < 1) {
	        	$("#paymentBox").hide();				
			}
	    	    
	    $('#paidUntil').change(function(){
			var val = $(this).val();
		    if(val > 0) {
		        $("#paymentBox").fadeIn('slow');
	    	} else {
		        $("#paymentBox").fadeOut('slow');
	    	}
	    });

	    	    
	  $('#registerForm').validate({
		  rules: {
			  memberno: {
        		 require_from_group: [1, '.memberGroup'],
				  digits: true
        	  },
			  memberNumber: {
        		 require_from_group: [1, '.memberGroup']
			  },
			  first_name: {
				  required: true,
				  minlength: 2
			  },
			  last_name: {
				  required: true,
				  minlength: 2
			  },	  
			  gender: {
				  required: true
			  },
			  email: {
				  email: true
			  },
			  userGroup: {
				  required: true,
				  range:[1,5],
			  }
    	}, // end rules
		  errorPlacement: function(error, element) {
			  if ( element.is(":radio") || element.is(":checkbox")){
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

	pageStart($lang['title-newuser'], NULL, $validationScript, "pprofile", NULL, $lang['member-newmember'], $_SESSION['successMessage'], $_SESSION['errorMessage']);
	
	$consumoPrevio = $_SESSION['consumoPrevio'];
	$memberType = $_SESSION['memberType'];
	$day = $_SESSION['day'];
	$month = $_SESSION['month'];
	$year = $_SESSION['year'];
	
	$tempMemberNo = $_SESSION['tempNo'];
	
	if (!isset($_GET['skipPhoto'])) {
		
		if (isset($_POST['mydata'])) {
			$encoded_data = $_POST['mydata'];
			$binary_data = base64_decode( $encoded_data );
			
			$imgname = 'images/members/' . $tempMemberNo . '.jpg';
			// save to server (beware of permissions)
			$result = file_put_contents( $imgname, $binary_data );
			
			if (!$result) die($lang['error-imagesave']);
			
			$_SESSION['userpicextension'] = 'jpg';
			
		} else {
			
			if ($_SESSION['cropOrNot'] == 0) {
		
				$memberno = $_SESSION['tempNo'];
				
				$image_fieldname = "fileToUpload";
				
				// Potential PHP upload errors
				$php_errors = array(1 => $lang['imgError1'],
									2 => $lang['imgError2'],
									3 => $lang['imgError3'],
									4 => $lang['imgError4']);
								
				// Check for any upload errors
				if ($_FILES[$image_fieldname]['error'] != 0) {
					$_SESSION['errorMessage'] = $php_errors[$_FILES[$image_fieldname]['error']] . " " . $lang['try-again'];
					header("Location: new-picture-upload-nocrop.php");
					exit();
				}
				
				// Check if a real file was uploaded
				if (is_uploaded_file($_FILES[$image_fieldname]['tmp_name'])) {
					
				} else {
					$_SESSION['errorMessage'] = $lang['imgError5'];
					header("Location: new-picture-upload-nocrop.php");
					exit();
				}
				
				// Is this actually an image?
				if (getimagesize($_FILES[$image_fieldname]['tmp_name'])) {
					
				} else {
					$_SESSION['errorMessage'] = $lang['imgError6'];
					header("Location: new-picture-upload-nocrop.php");
					exit();
				}
				
				// Save the file and store the extension in db
				$extension = pathinfo($_FILES[$image_fieldname]['name'], PATHINFO_EXTENSION);
				$upload_filename = "images/members/" . $tempMemberNo . "." . $extension;
				
				$_SESSION['userpicextension'] = $extension;
			
				
				if (move_uploaded_file($_FILES[$image_fieldname]['tmp_name'], $upload_filename)) {
					
				} else {
					$_SESSION['errorMessage'] = $lang['imgError7'];
					header("Location: new-picture-upload-nocrop.php");
					exit();
				}
			}
		
		}
	}


			unset($_SESSION['newmember']);
			
	/* Normal member numbers BEGIN
	
	$query = "select max(memberno) from users";

	$result = mysql_query($query)
		or handleError($lang['error-membershipnumberload'],"");
		
	$row = mysql_fetch_array($result);
		$nextMemberNo = $row['0'] + 1;
		
	Normal member numbers END */

?>
<form id="registerForm" action="" method="POST" onsubmit="return testInput()">
<input type="hidden" name="nextMemberNo" value="<?php echo $nextMemberNo; ?>" />
 <div class="overview">
  <span class="profilepicholder"><img class="profilepic" src="images/members/<?php echo $tempMemberNo . "." . $_SESSION['userpicextension'];?>" /></span>
  
<!--	/* Normal member numbers BEGIN */

  <input type="number" lang="nb" id="memberno" class="twoDigit memberGroup" name="memberno" value="<?php echo $nextMemberNo; ?>" /> or 
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
?>
  </select>
  
<script>

$('#memberNumber').change(function() {
  if($(this).val() != ''){
    $('#memberno').val('');
  }
});

$('#memberno').change(function() {
  if($(this).val() != ''){
    $('#memberNumber').val('');
  }
});


</script>

	/* Normal member numbers END */ -->
  
  <input type="text" name="first_name" placeholder="<?php echo $lang['member-firstnames']; ?>"/> <input type="text" name="last_name" placeholder="<?php echo $lang['member-lastnames']; ?>"/>
  <br /><br />
  <select name="gender">
   <option value=""><?php echo $lang['member-gender']; ?>:</option>
   <option value="Male"><?php echo $lang['member-male']; ?></option>
   <option value="Female"><?php echo $lang['member-female']; ?></option>
  </select>
  <br /><br />
  <select name="userGroup">
   <option value=''><?php echo $lang['member-usergroup']; ?>:</option>
<?php
      	// Query to look up usergroups
      	if ($_SESSION['userGroup'] == 1) {
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
  <br /><br />
<?php
	$monthNow = date('m'); 
	$next_month = ++$monthNow;

	if($next_month == 13) {
		$next_month = 1;
	}
	
	$nextMonthName = date('F', mktime(0, 0, 0, $next_month, 10));
	
  ?>
<?php if ($_SESSION['membershipFees'] == 1) { ?>
  <select name="paidUntil" id="paidUntil">
   <option value=""><?php echo $lang['member-membership']; ?>:</option>
   <option value="0"><?php echo $lang['member-notpaid']; ?></option>
   <option value="1">Semestral (10&euro;)</option>
   <option value="2">Anual (20&euro;)</option>
  </select><br />
<span style="color: white;" id='paymentBox'>
 <strong>&nbsp;&raquo; <?php echo $lang['paid-to']; ?>:</strong><br />
 <input type="radio" name="paidTo" value="1" style="margin-left: 17px; width: 10px;"><?php echo $lang['global-till']; ?></input><br />
 <input type="radio" name="paidTo" value="2" style="margin-left: 17px; width: 10px;"><?php echo $lang['global-bank']; ?></input><br />
</span>
  <br />
  
<?php } ?>

  <select name="creditEligible">
   <option value="0"><?php echo $lang['dispense-without-credit']; ?></option>
   <option value="0"><?php echo $lang['global-no']; ?></option>
   <option value="1"><?php echo $lang['global-yes']; ?></option>
  </select>

 </div> <!-- END OVERVIEW -->
  <div class="clearfloat"></div><br />
  <div id="profileWrapper">
 <div id="detailedinfo">
  <div id="leftpane">
   <strong>&nbsp;&raquo; <?php echo $lang['member-personal']; ?></strong><br />
   <input type="text" name="nationality" placeholder="<?php echo $lang['member-nationality']; ?>" value="<?php echo $nationality; ?>" /><br />
   <input type="number" lang="nb" name="day" class="twoDigit" maxlength="2" value="<?php echo $day; ?>" />
   <input type="number" lang="nb" name="month" class="twoDigit" maxlength="2" value="<?php echo $month; ?>" />
   <input type="number" lang="nb" name="year" class="fourDigit" maxlength="4" value="<?php echo $year; ?>" /><br />
   <input type="text" id="dni" class="idGroup" name="dni" placeholder="<?php echo $lang['dni-or-passport']; ?>" value="<?php echo $dni; ?>" /><br /><br />


   
   <strong>&nbsp;&raquo; <?php echo $lang['member-usage']; ?></strong><br />
   <select name="usageType">
    <?php if ($usageType == NULL) { ?><option value=""><?php echo $lang['global-select']; ?>:</option> <?php } ?>
    <option value="Recreational" <?php if ($memberType == '1') {echo "selected";} ?>><?php echo $lang['member-recreational']; ?></option>
    <option value="Medicinal" <?php if ($memberType == '2') {echo "selected";} ?>><?php echo $lang['member-medicinal']; ?></option>
   </select>
   <br />
   <input type="text" class="twoDigit" name="mconsumption" value="<?php echo $consumoPrevio; ?>" /> <?php echo $lang['member-consumptiong']; ?><br />
  </div> <!-- END LEFTPANE -->
  <div id="rightpane">
  
   <strong>&nbsp;&raquo; <?php echo $lang['member-contactdetails']; ?></strong><br />
   <input type="text" name="telephone" placeholder="<?php echo $lang['member-telephone']; ?>" value="<?php echo $telephone; ?>" /><br />
   <input type="text" name="email" placeholder="E-mail" value="<?php echo $email; ?>" /><br /><br />
   <input type="text" name="street" placeholder="<?php echo $lang['member-street']; ?>" value="<?php echo $street; ?>" />
   <input type="number" lang="nb" name="streetnumber" class="twoDigit" placeholder="No." value="<?php echo $streetnumber; ?>" />
   <input type="text" name="flat" class="twoDigit" placeholder="<?php echo $lang['member-flat']; ?>" value="<?php echo $flat; ?>" /><br />
   <input type="text" name="postcode" class="fourDigit" placeholder="<?php echo $lang['member-postcode']; ?>" value="<?php echo $postcode; ?>" />
   <input type="text" name="city" placeholder="<?php echo $lang['member-city']; ?>" value="<?php echo $city; ?>" /><br />
   <input type="text" name="country" placeholder="<?php echo $lang['member-country']; ?>" value="<?php echo $country; ?>" /><br /><br />
  </div> <!-- END RIGHTPANE -->
 <div class="clearfloat"></div>
 <br />&nbsp;<br />
<center><strong><?php echo $lang['chip']; ?></strong><br />
<input maxlength="10" type="text" name="cardid" value="<?php echo $cardid; ?>" /><br />
</center>
 
 </div> <!-- END DETAILEDINFO -->
 <div id="statistics">
 <h4><?php echo $lang['member-miscellaneous']; ?></h4>
 </div>
 </div> <!-- END PROFILEWRAPPER -->
 <div class="clearfloat"></div><br />
 <button class='oneClick' name='oneClick' type="submit"><?php echo $lang['global-savechanges']; ?></button>
</form>
