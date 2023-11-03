<?php
	
	session_start();
	
	require_once 'cOnfig/connection-tablet.php';
	require_once 'cOnfig/view-nohead.php';
	require_once 'cOnfig/authenticate.php';
	require_once 'cOnfig/languages/common.php';
	
	getSettings();
	
	$domain = $_SESSION['domain'];
	
	// Did this page re-submit with a form? If so, check & store details
	if (isset($_POST['first_name'])) {

		if ($_SESSION['domain'] == 'choko' || $_SESSION['domain'] == 'royaldream') {
			$userGroup = 11;
		} else {
			$userGroup = 5;
		}
		$first_name = trim($_POST['first_name']);
		$last_name = trim($_POST['last_name']);
		$email = $_POST['email'];
		$day = $_SESSION['day'];
		$month = $_SESSION['month'];
		$year = $_SESSION['year'];
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
		$mconsumption = $_SESSION['consumoPrevio'];
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
				
			if (!$data) {
				
				$memberMatch = 'true';
				
			} else {
				
				// Means the number is taken, so increase by 1 and try again
				$memberDigit = $memberDigit + 1;
				$memberno = $memberInitials . $memberDigit;
				
			}
		}
		
		
		$domainCheck = "SELECT domain FROM systemsettings";
		try
		{
			$result = $pdo3->prepare("$domainCheck");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
			$siteDomain = $row['domain'];
	
			$memberExp = $paymentTime;
	
			// Query to add new user - 28 arguments
			  $query = sprintf("INSERT INTO users (registeredSince, memberno, userGroup, first_name, last_name, email, day, month, year, nationality, gender, dni, street, streetnumber, flat, postcode, city, country, telephone, mconsumption, usageType, signupsource, cardid, photoid, docid, doorAccess, friend, friend2, paidUntil, form1, form2, creditEligible, dniscan, dniext1, dniext2, photoext, domain) VALUES ('%s', '%s', '%d', '%s', '%s', '%s', '%d', '%d', '%d', '%s', '%s', '%s', '%s', '%d', '%s', '%s', '%s', '%s', '%s', '%d', '%s', '%s', '%s', '%d', '%d', '%d', '%d', '%d', '%s', '%d', '%d', '%d', '%d', '%s', '%s', '%s', '%s');", 
$insertTime,
$memberno,
$userGroup,
$first_name,
$last_name,
$email,
$day,
$month,
$year,
$nationality,
$gender,
$dni,
$street,
$streetnumber,
$flat,
$postcode,
$city,
$country,
$telephone,
$mconsumption,
$usageType,
$signupsource,
$cardid,
$photoid,
$docid,
$doorAccess,
$aval,
$aval2,
$memberExp,
'1',
'1',
$creditEligible,
$dniscan,
$_SESSION['dnifrontextension'],
$_SESSION['dnibackextension'],
$_SESSION['userpicextension'],
$siteDomain);
		  
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
			
	$user_id = $pdo3->lastInsertId();
	
	$_SESSION['newUserId'] = $user_id;
	
	$tempMemberNo = $_SESSION['tempNo'];
	
	$oldfile4 = "images/_$domain/sigs/" . $tempMemberNo . '.png';
	$newfile4 = "images/_$domain/sigs/" . $user_id . '.png';
	rename($oldfile4, $newfile4);
	
					
	
		// On success: redirect.
		$_SESSION['successMessage'] = "Usuario registrado con éxito!";
		if ($_SESSION['domain'] == 'choko') {
			header("Location: reg.php");
		} else if ($_SESSION['domain'] == 'royaldream') {
			header("Location: reg-5.php?noAval");
			
		} else {
			header("Location: reg-3.php?user_id=$user_id");
		}
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
			  telephone: {
				  required: true
			  },
			  email: {
				  required: true
			  },
			  street: {
				  required: true
			  },
			  postcode: {
				  required: true
			  },
			  city: {
				  required: true
			  },
			  country: {
				  required: true
			  },
			  streetnumber: {
				  required: true
			  },
			  pinCode: {
				  required: true,
				  range: [6464,6464]
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


	pageStart($lang['title-newuser'], NULL, $validationScript, "pprofile", NULL, "", $_SESSION['successMessage'], $_SESSION['errorMessage']);
	
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
    <td colspan="2"><center><strong style='font-size: 18px;'>Personal details</strong></center><br />&nbsp;</td>
   </tr>
   <tr>
    <td>First name(s)</td>
    <td><input type="text" name="first_name" /><br />&nbsp;</td>
   </tr>
   <tr>
    <td>Last name(s)</td>
    <td><input type="text" name="last_name" /><br />&nbsp;</td>
   </tr>
   <tr>
    <td>Gender</td>
    <td><select name="gender">
   <option value=""><?php echo $lang['member-gender']; ?>:</option>
   <option value="Male"><?php echo $lang['member-male']; ?></option>
   <option value="Female"><?php echo $lang['member-female']; ?></option>
  </select><br />&nbsp;</td>
   </tr>
   <tr>
    <td>Nationality</td>
    <td><input type="text" name="nationality" /><br />&nbsp;</td>
   </tr>
   <tr>
    <td>DNI/Passport #&nbsp;&nbsp;&nbsp;&nbsp;</td>
    <td><input type="text" id="dni" class="idGroup" name="dni" /><br />&nbsp;</td>
   </tr>
   <tr>
    <td>Usage</td>
    <td><select name="usageType">
    <?php if ($usageType == NULL) { ?><option value=""><?php echo $lang['global-select']; ?>:</option> <?php } ?>
    <option value="0" <?php if ($memberType == '1') {echo "selected";} ?>><?php echo $lang['member-recreational']; ?></option>
    <option value="1" <?php if ($memberType == '2') {echo "selected";} ?>><?php echo $lang['member-medicinal']; ?></option>
   </select><br />&nbsp;</td>
   </tr>
   <tr>
    <td colspan="2"><center><strong style='font-size: 18px;'>Contact details</strong></center><br />&nbsp;</td>
   </tr>
   <tr>
    <td>Telephone</td>
    <td><input type="text" name="telephone" /><br />&nbsp;</td>
   </tr>
   <tr>
    <td>E-mail</td>
    <td><input type="email" name="email" /> <br />&nbsp;</td>
   </tr>
   <tr>
    <td>Address in Spain</td>
    <td><input type="text" name="street" placeholder="Street name" /> <input type="text" lang="nb" name="streetnumber" class="fourDigit" placeholder="Number" /> <input type="text" name="flat" placeholder="Apartment" class="fourDigit"  /><br />&nbsp;</td>
   </tr>
   <tr>
    <td>Post code & city</td>
    <td><input type="text" name="postcode" class="fourDigit" /> <input type="text" name="city" class="sixDigit" /><br />&nbsp;</td>
   </tr>
   <tr>
    <td>Country</td>
    <td><input type="text" name="country" />  <br />&nbsp;</td>
   </tr>

  </table>
  
<?php 
if ($_SESSION['domain'] != 'royaldream') {
?>


 <strong>Please give the tablet to the receptionist to finalize the registration.</strong><br /><br />
 <input type="password" name="pinCode" class="fourDigit" />
<?php 
}
?>

 <div class="clearfloat"></div><br />
 <button class='oneClick' name='oneClick' type="submit"><?php echo $lang['global-savechanges']; ?></button>
</form>
</center>
