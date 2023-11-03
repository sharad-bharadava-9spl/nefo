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
		$streetnumber = 0;
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
		$yearGroup = $_POST['yearGroup'];
		
		
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
	
	
			// Query to add new user - 28 arguments
			  $query = sprintf("INSERT INTO users (registeredSince, userGroup, memberno, first_name, last_name, day, month, year, gender, photoext, domain) VALUES ('%s', '%d', '%s', '%s', '%s', '%d', '%d', '%d', '%s', '%s', '%s');",
$insertTime,
2,
$memberno,
$first_name,
$last_name,
$day,
$month,
$year,
$gender,
$_SESSION['userpicextension'],
$siteDomain
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
			
	// Rename the member photo, signature and DNI scans from temp number to real number
	$oldfile = 'images/members/' . $tempMemberNo . '.' . $_SESSION['userpicextension'];
	$newfile = 'images/members/' . $user_id . '.' . $_SESSION['userpicextension'];
	rename($oldfile, $newfile);
	
	
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
		// See if user is vol or admin, and then set pwd
		$userDetails = "SELECT userGroup FROM users WHERE user_id = '{$user_id}'";
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
			$userGroup = $row['userGroup'];
	
			

		if ($userGroup < 3) {
		$_SESSION['successMessage'] = "Person added succesfully!";
			header("Location: new-password.php?user_id={$user_id}");
		} else if ($userGroup == 5) {
			header("Location: find-family.php?user_id={$user_id}");
		} else {
		$_SESSION['successMessage'] = "Person added succesfully!";
			header("Location: profile.php?user_id={$user_id}");
			exit();
		}
		
		exit();
	}
	/***** FORM SUBMIT END *****/

	$validationScript = <<<EOD
    $(document).ready(function() {
	    
	    $('#userGroup').change(function(){
			var val = $(this).val();
		    if(val == 4) {
		        $("#groupbox").fadeIn('slow');
	    	} else {
		        $("#groupbox").fadeOut('slow');
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

	pageStart($lang['new-user'], NULL, $validationScript, "pprofile", NULL, $lang['new-user'], $_SESSION['successMessage'], $_SESSION['errorMessage']);
	

			

?>
<form id="registerForm" action="" method="POST" onsubmit="return testInput()">
<input type="hidden" name="nextMemberNo" value="<?php echo $nextMemberNo; ?>" />
 <div class="overview">
  
<table class='profileTable' style='text-align: left; margin: 0;'>
 <tr>
  <td><strong><?php echo $lang['member-firstnames']; ?>:</strong></td>
  <td><input type="text" name="first_name" /></td>
 </tr>
 <tr>
  <td><strong><?php echo $lang['member-lastnames']; ?>:</strong></td>
  <td><input type="text" name="last_name" /></td>
 </tr>

</table>
 </div> <!-- END OVERVIEW -->
 <div class="clearfloat"></div><br />
 <button type="submit"><?php echo $lang['global-savechanges']; ?></button>
</form>
