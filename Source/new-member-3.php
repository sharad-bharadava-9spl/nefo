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
	
	$domain = $_SESSION['domain'];
	
	// Did this page re-submit with a form? If so, check & store details
	if (isset($_POST['first_name'])) {
		
		$memberno = $_POST['memberno'];
		$nextMemberNo = $_POST['nextMemberNo'];
		$memberNumber = $_POST['memberNumber'];
		$userGroup = $_POST['userGroup'];
		$adminComment = $_POST['adminComment'];
		$first_name = str_replace("'","\'",str_replace('%', '&#37;', trim($_POST['first_name'])));
		$last_name = str_replace("'","\'",str_replace('%', '&#37;', trim($_POST['last_name'])));
		$email = $_POST['email'];
		$day = $_POST['day'];
		$month = $_POST['month'];
		$year = $_POST['year'];
		$nationality = $_POST['nationality'];
		$gender = $_POST['gender'];
		$dni = $_POST['dni'];
		$street = str_replace("'","\'",str_replace('%', '&#37;', trim($_POST['street'])));
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
		$exento = $_POST['exento'];
		$customAmount = $_POST['customAmount'];
		$usergroup2 = $_POST['usergroup2'];
		$interview = $_POST['interview'];
		
		$aval = $_SESSION['aval'];
		$aval2 = $_SESSION['aval2'];
		
		if ($_SESSION['normalNumbers'] == 1) {
			
			//memberno = automatic assign
			//membernumber = selelcted amnually
			
			// If a number was selected from the drop-down, there is no 'memberno', so we use the dropdown member number.
			if ($memberno == '') {
				$memberno = $memberNumber;
			}
			
				
			// We've gotta check if the member number is still available!
			$query = "SELECT memberno FROM users WHERE memberno = $memberno";
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
				
				// Means the number is taken, so use highest free memberno
				$query = "SELECT max(memberno) FROM users";
				try
				{
					$result = $pdo3->prepare("$query");
					$result->execute();
				}
				catch (PDOException $e)
				{
						$error = 'Error fetching user: ' . $e->getMessage();
						echo $error;
						exit();
				}
			
				$row = $result->fetch();
					$oldmemberno = $memberno;
					$memberno = $row['0'] + 1;
					
				// Create feedback message saying that hte memberno was taken, and that we've assigned $memberno to this member.
				$_SESSION['errorMessage'] = $lang['number-taken-1'] . $oldmemberno . $lang['number-taken-2'] . $memberno . $lang['number-taken-3'];
			}
		
		
		} else {

		
			$pattern = array("'é'", "'è'", "'ë'", "'ê'", "'É'", "'È'", "'Ë'", "'Ê'", "'á'", "'à'", "'ä'", "'â'", "'å'", "'Á'", "'À'", "'Ä'", "'Â'", "'Å'", "'ó'", "'ò'", "'ö'", "'ô'", "'Ó'", "'Ò'", "'Ö'", "'Ô'", "'í'", "'ì'", "'ï'", "'î'", "'Í'", "'Ì'", "'Ï'", "'Î'", "'ú'", "'ù'", "'ü'", "'û'", "'Ú'", "'Ù'", "'Ü'", "'Û'", "'ý'", "'ÿ'", "'Ý'", "'ø'", "'Ø'", "'œ'", "'Œ'", "'Æ'", "'ç'", "'Ç'", "'ñ'", "'Ñ'");
			$replace = array('&eacute;', 'e', 'e', 'e', 'E', 'E', 'E', 'E', 'a', 'a', 'a', 'a', 'a', 'A', 'A', 'A', 'A', 'A', 'o', 'o', 'o', 'o', 'O', 'O', 'O', 'O', 'i', 'i', 'i', 'I', 'I', 'I', 'I', 'I', 'u', 'u', 'u', 'u', 'U', 'U', 'U', 'U', 'y', 'y', 'Y', 'o', 'O', 'a', 'A', 'A', 'c', 'C', 'n', 'N'); 
			
			$memberInitials = strtoupper(substr(preg_replace($pattern, $replace, $first_name), 0,1)) . strtoupper(substr(preg_replace($pattern, $replace, $last_name), 0,1));
			
			
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
			
		// Calculate Workstation access
		foreach($_POST['workStation'] as $workstationCheckbox) {
			
		    $workStation +=  $workstationCheckbox;
	
	  	}

	
		if ($paidUntil == 0) {
			
			$memberExp = $paymentTime;
			$amountPaid = 0;
			
		} else if ($paidUntil == 8888) {
			
			$memberExp = $paymentTime;
			$amountPaid = 0;
			$exento = 1;
			
		} else if ($paidUntil == 9999) {
			
			$memberExp = date('Y-m-d H:i:s', strtotime("+365 day", strtotime(date('Y-m-d H:i:s'))));
			$amountPaid = $customAmount;
			
		} else {

			$selectCuota = "SELECT cuota, days FROM cuotas WHERE id = $paidUntil";
		try
		{
			$result = $pdo3->prepare("$selectCuota");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$cuotaRes = $result->fetch();
				$cuota = $cuotaRes['cuota'];
				$days = $cuotaRes['days'];
				
			$memberExp = date('Y-m-d H:i:s', strtotime("+$days day", strtotime(date('Y-m-d H:i:s'))));
			$amountPaid = $cuota;
			
		}
		
		

	if (isset($_SESSION['sigext']) && $_SESSION['sigext'] != '') {
		$sigextension = $_SESSION['sigext'];
	} else {
		$sigextension = "png";
	}
	
		
	
			// Query to add new user - 28 arguments
			  $query = sprintf("INSERT INTO users (registeredSince, memberno, userGroup, first_name, last_name, email, day, month, year, nationality, gender, dni, street, streetnumber, flat, postcode, city, country, telephone, mconsumption, usageType, signupsource, cardid, photoid, docid, doorAccess, friend, friend2, paidUntil, form1, form2, creditEligible, dniscan, dniext1, dniext2, photoext, domain, exento, userGroup2, workStation, sigext, interview, cuota) VALUES ('%s', '%s', '%d', '%s', '%s', '%s', '%d', '%d', '%d', '%s', '%s', '%s', '%s', '%d', '%s', '%s', '%s', '%s', '%s', '%d', '%s', '%s', '%s', '%d', '%d', '%d', '%d', '%d', '%s', '%d', '%d', '%d', '%d', '%s', '%s', '%s', '%s', '%d', '%d', '%d', '%s', '%d', '%d');",
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
$siteDomain,
$exento,
$usergroup2,
$workStation,
$sigextension,
$interview,
$paidUntil
);
		  
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
		
	// Query to add payment
	$query = sprintf("INSERT INTO memberpayments (paymentdate, userid, amountPaid, newExpiry, paidTo) VALUES ('%s', '%d', '%f', '%s', '%d');",
	$paymentTime, $user_id, $amountPaid, $memberExp, $paidTo);
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
		
			
	// Rename the member photo, signature and DNI scans from temp number to real number
	$oldfile = "images/_$domain/members/" . $tempMemberNo . "." . $_SESSION['userpicextension'];
	$newfile = "images/_$domain/members/" . $user_id . "." . $_SESSION['userpicextension'];
	rename($oldfile, $newfile);
	
	$oldfile2 = "images/_$domain/ID/" . $tempMemberNo . "-front." . $_SESSION['dnifrontextension'];
	$newfile2 = "images/_$domain/ID/" . $user_id . "-front." . $_SESSION['dnifrontextension'];
	rename($oldfile2, $newfile2);
	
	$oldfile3 = "images/_$domain/ID/" . $tempMemberNo . "-back." . $_SESSION['dnibackextension'];
	$newfile3 = "images/_$domain/ID/" . $user_id . "-back." . $_SESSION['dnibackextension'];
	rename($oldfile3, $newfile3);
	
	$oldfile4 = "images/_$domain/sigs/" . $tempMemberNo . ".$sigextension";
	$newfile4 = "images/_$domain/sigs/" . $user_id . ".$sigextension";
	rename($oldfile4, $newfile4);
	
	
	
	
		// Write to log
		$logTime = date('Y-m-d H:i:s');
	
		$query = sprintf("INSERT INTO log (logtype, logtime, user_id, operator, amount, newExpiry) VALUES ('%d', '%s', '%d', '%d', '%f', '%s');",
		12, $logTime, $user_id, $_SESSION['user_id'], $amountPaid, $memberExp);
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

		unset($_SESSION['aval']);
		unset($_SESSION['aval2']);

			
		// On success: redirect.
		// $_SESSION['successMessage'] = "User added succesfully!";
		
		if ($_SESSION['iPadReaders'] > 0) {
			
			if ($userGroup < 4) {
				header("Location: new-card.php?staff=true&user_id=" . $user_id);
			} else {
				header("Location: new-card.php?user_id=" . $user_id);
			}
			
		} else if ($userGroup < 4) {
			header("Location: new-password.php?user_id=" . $user_id);			
		} else {
			header("Location: profile.php?user_id=" . $user_id);
		}
		exit();
	}
	/***** FORM SUBMIT END *****/

	if ($_SESSION['iPadReaders'] > 0) {
		
	$validationScript = <<<EOD
    $(document).ready(function() {
	    
	    $('#exento').click(function() {
	        if ($(this).is(':checked')) {
	            $('#paidUntil').prop('disabled', true);
	        } else {
	            $('#paidUntil').prop('disabled', false);
	        }
	    });
	    
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
	    	
			var valB = $(this).val();
		    if(valB == 9999) {
		        $("#custompay").fadeIn('slow');
	    	} else {
		        $("#custompay").fadeOut('slow');
	    	}

	    });

	    	    
	  $('#registerForm').validate({
		  ignore: [],
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
				  range:[1,6],
			  },
               paidUntil:{
                  required: function (element) {
                     if($("#exento").is(':checked')){
                         return false;
                     }
                     else
                     {
                         return true;
                     }  
                  }  
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
				  range:[1900,2001],
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
               paidTo: {
                  required: function (element) {
                     if($("#paidUntil").val() > 0 ){
                         return true;
                     }
                     else
                     {
                         return false;
                     }  
                  }  
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

	} else {
		
	$validationScript = <<<EOD
    $(document).ready(function() {
	    
	    $('#exento').click(function() {
	        if ($(this).is(':checked')) {
	            $('#paidUntil').prop('disabled', true);
	        } else {
	            $('#paidUntil').prop('disabled', false);
	        }
	    });
	    
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
	    	
			var valB = $(this).val();
		    if(valB == 9999) {
		        $("#custompay").fadeIn('slow');
	    	} else {
		        $("#custompay").fadeOut('slow');
	    	}
	    });

	    	    
	  $('#registerForm').validate({
		  ignore: [],
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
				  range:[1,6],
			  },
			  
               paidUntil:{
                  required: function (element) {
                     if($("#exento").is(':checked')){
                         return false;
                     }
                     else
                     {
                         return true;
                     }  
                  }  
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
				  range:[1900,2001],
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
               paidTo: {
                  required: function (element) {
                     if($("#paidUntil").val() > 0 ){
                         return true;
                     }
                     else
                     {
                         return false;
                     }  
                  }  
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

	}

	pageStart($lang['title-newuser'], NULL, $validationScript, "pprofile", "final", $lang['member-newmember'], $_SESSION['successMessage'], $_SESSION['errorMessage']);
	
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
			
			$imgname = "images/_$domain/members/" . $tempMemberNo . '.jpg';
			

			// save to server (beware of permissions)
			$result = file_put_contents( $imgname, $binary_data );
			
			if (!$result) die($lang['error-imagesave']);
			
			$_SESSION['userpicextension'] = 'jpg';
			
		} else {
			
		
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
				$upload_filename = "images/_$domain/members/" . $tempMemberNo . "." . $extension;
				
				$_SESSION['userpicextension'] = $extension;
			
				
				if (move_uploaded_file($_FILES[$image_fieldname]['tmp_name'], $upload_filename)) {
					
				} else {
					$_SESSION['errorMessage'] = $lang['imgError7'];
					header("Location: new-picture-upload-nocrop.php");
					exit();
				}
		
		}
	}


			unset($_SESSION['newmember']);
			

?>

<div id='progress'>
 <div id='progressinside5'>
 </div>
</div>
<br />
 <div id='progresstext1'>
 1. <?php echo $lang['avalista']; ?>
 </div>

 <div id='progresstext2'>
 2. <?php echo $lang['member-contract']; ?>
 </div>
 
 <div id='progresstext3'>
 3. <?php echo "ID / " . $lang['member-passport']; ?>
 </div>
 
 <div id='progresstext4'>
 4. <?php echo $lang['title-memberpicture']; ?>
 </div>
 
 <div id='progresstext5'>
 5. <?php echo $lang['member-details']; ?>
 </div>
 
 <div id='mainbox'>
  <div id='mainboxheader'>
  <?php echo $lang['member-details']; ?>
  </div>
  <div class='boxcontent'>

<form id="registerForm" action="" method="POST" onsubmit="return testInput()">
<input type="hidden" name="nextMemberNo" value="<?php echo $nextMemberNo; ?>" />
<input type="hidden" name="workStationTot" value="<?php echo $workStation; ?>" />
<div id='profilepicholder'>
<img class="profilepic" src="images/_<?php echo $domain; ?>/members/<?php echo $tempMemberNo . '.' . $_SESSION['userpicextension'];?>" />
</div>
   <!-- 
<?php
	if ($_SESSION['normalNumbers'] == 1) {
		
	$query = "select max(memberno) from users";
		try
		{
			$result = $pdo3->prepare("$query");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
			$nextMemberNo = $row['0'] + 1;
		
?>

<input type="number" lang="nb" id="memberno" class="twoDigit memberGroup" name="memberno" value="<?php echo $nextMemberNo; ?>" readonly /> or 
  <select name="memberNumber" id="memberNumber" class="memberGroup" style="width: 60px;">
   <option value=""></option>
<?php
	$sql = "SELECT memberno FROM users";
		try
		{
			$result = $pdo3->prepare("$sql");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		while ($row = $result->fetch()) {
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

<?php } ?>
-->

<span class='uc'><?php echo $lang['member-firstnames']; ?></span>
<input type="text" name="first_name" placeholder="" style='width: 149px;' />
<span class='uc'><?php echo $lang['member-lastnames']; ?></span>
<input type="text" name="last_name" placeholder="" style='width: 205px;' />
  <select name="gender" style='width: 140px;'>
   <option value=""><?php echo $lang['member-gender']; ?>:</option>
   <option value="Male"><?php echo $lang['member-male']; ?></option>
   <option value="Female"><?php echo $lang['member-female']; ?></option>
  </select>
  <br /><br />
  <div class='testbox'>
  <select name="userGroup" id="userGroup">
   <option value=''><?php echo $lang['user-type']; ?>:</option>
<?php
      	// Query to look up usergroups
      	if ($_SESSION['userGroup'] == 1) {
			$selectGroups = "SELECT userGroup, groupName, groupDesc FROM usergroups WHERE userGroup < 10 ORDER by userGroup ASC";
		} else {
			$selectGroups = "SELECT userGroup, groupName, groupDesc FROM usergroups WHERE userGroup > 3 AND userGroup < 10 ORDER by userGroup ASC";
		}
		try
		{
			$result = $pdo3->prepare("$selectGroups");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		while ($group = $result->fetch()) {
			
			if ($group['userGroup'] != $userGroup) {
				$group_row = sprintf("<option value='%d'>%d - %s</option>",
	  								 $group['userGroup'], $group['userGroup'], $group['groupName']);
	  			echo $group_row;
  			}
  		}
?>
  </select> 
<?php if ($_SESSION['puestosOrNot'] == 1) { ?>

	<div id="expiryBox">
 	<div class='fakeboxholder'>	
	 <label class="control">
	  <span id="errorBox4"></span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?php echo $lang['reception']; ?>
	  <input type="checkbox" name='workStation[]' value='1' />
	  <div class="fakebox"></div>
	 </label>
	</div><br />
	<div class='fakeboxholder'>	
	 <label class="control">
	  <span id="errorBox5"></span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?php echo $lang['bar']; ?>
	  <input type="checkbox" name='workStation[]' value='5' />
	  <div class="fakebox"></div>
	 </label>
	</div><br />
	<div class='fakeboxholder'>	
	 <label class="control">
	  <span id="errorBox6"></span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?php echo $lang['dispensary']; ?>
	  <input type="checkbox" name='workStation[]' value='10' />
	  <div class="fakebox"></div>
	 </label>
	</div>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
    </div>

<?php } ?>
  </div>
  <select name="usergroup2" id="usergroup2" style='vertical-align: top;'>
   <option value=''><?php echo $lang['member-usergroup']; ?>:</option>
<?php
      	// Query to look up usergroups
		$selectGroups = "SELECT id, name FROM usergroups2 ORDER by id ASC";
		try
		{
			$result = $pdo3->prepare("$selectGroups");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		while ($group = $result->fetch()) {

				$group_row = sprintf("<option value='%d'>%d - %s</option>",
	  								 $group['id'], $group['id'], $group['name']);
	  			echo $group_row;

  		}
?>
  </select> 

<?php
	$monthNow = date('m'); 
	$next_month = ++$monthNow;

	if($next_month == 13) {
		$next_month = 1;
	}
	
	$nextMonthName = date('F', mktime(0, 0, 0, $next_month, 10));
	
  ?>
<?php if ($_SESSION['membershipFees'] == 1) { ?>
  <div class='testbox'>
  <select name="paidUntil" id="paidUntil" style='vertical-align: top;'>
   <option value=""><?php echo $lang['member-membership']; ?>:</option>
   <option value="0"><?php echo $lang['member-notpaid']; ?></option>
<?php

	// Query to look up cuotas
	$selectCuotas = "SELECT id, name, cuota FROM cuotas";
		try
		{
			$result = $pdo3->prepare("$selectCuotas");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		while ($cuotaRes = $result->fetch()) {

		$id = $cuotaRes['id'];
		$name = $cuotaRes['name'];
		$cuota = $cuotaRes['cuota'];
		
		echo "<option value='$id'>$name ($cuota&euro;)</option>";
		
	}
	
?>



  <?php 
  
	if (($_SESSION['exentoset'] == 0 && $_SESSION['userGroup'] == 1) || $_SESSION['exentoset'] == 1) {
		echo "<option value='8888'>{$lang['exempt']}</option>";
} 
  echo "</select>";
  
  if ($_SESSION['bankPayments'] == 1) { ?> 
<span id='paymentBox'>
	<div class='fakeboxholder'>	
	 <label class="control">
	  <span id="errorBox2"></span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?php echo $lang['cash']; ?>
	  <input type="radio" name='paidTo' id='paidTo' value='1' />
	  <div class="fakebox"></div>
	 </label>
	</div>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
	<div class='fakeboxholder'>	
	 <label class="control">
	  <span id="errorBox3"></span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?php echo $lang['card']; ?>
	  <input type="radio" name='paidTo' id='paidTo' value='2' />
	  <div class="fakebox"></div>
	 </label>
	</div>
</span>
</div>
<?php } else {
	
echo "</div>";



	} } ?>  



 <br /> 
   <select name="usageType">
    <?php if ($usageType == NULL) { ?><option value=""><?php echo $lang['global-select']; ?>:</option> <?php } ?>
    <option value="0" <?php if ($_SESSION['memberType'] == 0) {echo "selected";} ?>><?php echo $lang['member-recreational']; ?></option>
    <option value="1" <?php if ($_SESSION['memberType'] == 1) {echo "selected";} ?>><?php echo $lang['member-medicinal']; ?></option>
   </select>
<span class='uc'>&nbsp;<?php echo $lang['member-consumptiong']; ?></span>
   <input type="text" class="twoDigit" name="mconsumption" value="<?php echo $consumoPrevio; ?>" style="width: 77px;" />

  <div id="custompay" style="display: none;">
  
  <input type="number" name="customAmount" class="fourDigit" placeholder="&euro;" />
  </div>
  <br />
  
<?php 

if ($_SESSION['negcredit'] == 0) { ?>


  <select name="creditEligible">
   <option value="0"><?php echo $lang['dispense-without-credit']; ?></option>
   <option value="0"><?php echo $lang['global-no']; ?></option>
   <option value="1"><?php echo $lang['global-yes']; ?></option>
  </select>
  
<?php } ?>

     <select name="interview">
	  <option value="0"><?php echo $lang['interviewed-member']; ?></option>
	  <option value="0"><?php echo $lang['global-no']; ?></option>
	  <option value="1"><?php echo $lang['global-yes']; ?></option>
     </select>

<br /><br /><br /><br />
<h4><?php echo $lang['member-personal']; ?></h4>
<img src="images/new-flag.png" style='margin-bottom: -2px;' />
<span class='uc'><?php echo $lang['member-nationality']; ?></span>
   <input type="text" name="nationality" style='width: 140px;' />
<img src="images/birthday.png" style='margin-bottom: -3px;' />
<span class='uc'><?php echo $lang['birthdate']; ?></span>
   <input type="number" lang="nb" name="day" class="oneDigit" maxlength="2" value="<?php echo $day; ?>" />
   <input type="number" lang="nb" name="month" class="oneDigit" maxlength="2" value="<?php echo $month; ?>"  style='margin-left: 5px;' />
   <input type="number" lang="nb" name="year" class="twoDigit" maxlength="4" value="<?php echo $year; ?>"  style='margin-left: 5px;' />
<img src="images/id.png" style='margin-bottom: -3px;' />
<span class='uc'><?php echo $lang['dni-or-passport']; ?></span>
   <input type="text" id="dni" class="idGroup" name="dni" style='width: 140px;' /><br /><br />
   
<h4><?php echo $lang['member-contactdetails']; ?></h4>
   <input type="text" name="street" placeholder="<?php echo $lang['member-street']; ?>" value="<?php echo $street; ?>" />
   <input type="number" lang="nb" name="streetnumber" class="twoDigit" placeholder="No." value="<?php echo $streetnumber; ?>" />
   <input type="text" name="flat" class="twoDigit" placeholder="<?php echo $lang['member-flat']; ?>" value="<?php echo $flat; ?>" />
   <input type="text" name="postcode" class="fourDigit" placeholder="<?php echo $lang['member-postcode']; ?>" value="<?php echo $postcode; ?>" />
   <input type="text" name="city" placeholder="<?php echo $lang['member-city']; ?>" value="<?php echo $city; ?>" />
   <input type="text" name="country" placeholder="<?php echo $lang['member-country']; ?>" value="<?php echo $country; ?>" /><br />
   <input type="text" name="email" placeholder="E-mail" value="<?php echo $email; ?>" />  
   <input type="text" name="telephone" placeholder="<?php echo $lang['member-telephone']; ?>" value="<?php echo $telephone; ?>" />
<?php if ($_SESSION['iPadReaders'] == 0) { ?>
<center><span class='uc'><?php echo $lang['chip']; ?><br />

<input type="text" name="cardid" id="input1" maxlength="30" />
<?php } ?>
</center>
 

</div></div><br />
<center>
 <button class='okbutton1' name='oneClick' type="submit"><?php echo $lang['global-savechanges']; ?></button>
</center>
</form>

<?php displayFooter();