<?php
	
	require_once 'cOnfig/connection.php';
	require_once 'cOnfig/view.php';
	require_once 'cOnfig/languages/common.php';
	
	session_start();
	
	getSettings();
	
	// Did this page re-submit with a form? If so, check & store details
	if (isset($_POST['first_name'])) {

		$userGroup = 20;
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
		$insertTime = date('Y-m-d H:i:s');
		$paymentTime = date('Y-m-d H:i:s');	
		$tempMemberNo = $_SESSION['tempNo'];

		
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
	
			$memberExp = $paymentTime;
	
			// Query to add new user - 28 arguments
			  $query = sprintf("INSERT INTO users (registeredSince, memberno, userGroup, first_name, last_name, email, day, month, year, nationality, gender, dni, street, streetnumber, flat, postcode, city, country, telephone, mconsumption, usageType, signupsource, cardid, photoid, docid, doorAccess, friend, friend2, paidUntil, form1, form2, creditEligible, dniscan, dniext1, dniext2, photoext, domain) VALUES ('%s', '%s', '%d', '%s', '%s', '%s', '%d', '%d', '%d', '%s', '%s', '%s', '%s', '%d', '%s', '%s', '%s', '%s', '%s', '%d', '%s', '%s', '%s', '%d', '%d', '%d', '%d', '%d', '%s', '%d', '%d', '%d', '%d', '%s', '%s', '%s', '%s');",
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
mysql_real_escape_string($aval),
mysql_real_escape_string($aval2),
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
					
	// Rename the member photo, signature and DNI scans from temp number to real number
	$oldfile = 'images/members/' . $tempMemberNo . '.' . $_SESSION['userpicextension'];
	$newfile = 'images/members/' . $user_id . '.' . $_SESSION['userpicextension'];
	rename($oldfile, $newfile);
	
		// On success: redirect.
		// $_SESSION['successMessage'] = "User added succesfully!";
		header("Location: register-3.php?user_id=" . $user_id);
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
			  },
			  paidUntil: {
				  required: true
			  },
			  nationality: {
				  required: true
			  },
			  day: {
				  required: true,
				  range:[1,31],
			  },
			  month: {
				  required: true,
				  range:[1,12],
			  },
			  year: {
				  required: true,
				  range:[1900,2000],
			  },
			  dni: {
				  required: true,
			  },
			  usageType: {
				  required: true
			  },
			  mconsumption: {
				  required: true,
				  range:[1,100]
			  },
			  cardid: {
				  required: true
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
	


			unset($_SESSION['newmember']);
			
?>
<center>
<form id="registerForm" action="" method="POST" onsubmit="return testInput()">
<input type="hidden" name="nextMemberNo" value="<?php echo $nextMemberNo; ?>" />
  <table>
   <tr>
    <td>First name(s):</td>
    <td><input type="text" name="first_name" /></td>
   </tr>
   <tr>
    <td>Last name(s):</td>
    <td><input type="text" name="last_name" placeholder="<?php echo $lang['member-lastnames']; ?>"/></td>
   </tr>
  </table>
  <br /><br />
  <select name="gender">
   <option value=""><?php echo $lang['member-gender']; ?>:</option>
   <option value="Male"><?php echo $lang['member-male']; ?></option>
   <option value="Female"><?php echo $lang['member-female']; ?></option>
  </select>
  <br /><br />

  <div class="clearfloat"></div><br />
   <input type="text" name="nationality" placeholder="<?php echo $lang['member-nationality']; ?>" value="<?php echo $nationality; ?>" /><br />
   <input type="text" id="dni" class="idGroup" name="dni" placeholder="<?php echo $lang['dni-or-passport']; ?>" value="<?php echo $dni; ?>" /><br /><br />


   
   <strong>&nbsp;&raquo; <?php echo $lang['member-usage']; ?></strong><br />
   <select name="usageType">
    <?php if ($usageType == NULL) { ?><option value=""><?php echo $lang['global-select']; ?>:</option> <?php } ?>
    <option value="0" <?php if ($memberType == '1') {echo "selected";} ?>><?php echo $lang['member-recreational']; ?></option>
    <option value="1" <?php if ($memberType == '2') {echo "selected";} ?>><?php echo $lang['member-medicinal']; ?></option>
   </select>
   <br />
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
 
 </div> <!-- END DETAILEDINFO -->
 <div id="statistics">
 <h4><?php echo $lang['member-miscellaneous']; ?></h4>
 </div>
 </div> <!-- END PROFILEWRAPPER -->
 <div class="clearfloat"></div><br />
 <button class='oneClick' name='oneClick' type="submit"><?php echo $lang['global-savechanges']; ?></button>
</form>
</center>
