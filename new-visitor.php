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

		$visittime = $_SESSION['order-dateDB'] . " " . $_SESSION['order-time'] . ":00";

		$userGroup = 6;
		
		$aval = $_SESSION['aval'];
		$aval2 = $_SESSION['aval2'];
		$first_name = trim($_POST['first_name']);
		$last_name = trim($_POST['last_name']);
		$day = $_POST['day'];
		$month = $_POST['month'];
		$year = $_POST['year'];
		$dni = $_POST['dni'];
		$insertTime = date('Y-m-d H:i:s');	
		$tempMemberNo = $_SESSION['tempNo'];

		
		// Look up system settings, alfanumeric or not
		try
		{
			$result = $pdo3->prepare("SELECT normalNumbers FROM systemsettings");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
			$normalNumbers = $row['normalNumbers'];
			
		if ($normalNumbers == 1) {
			
			try
			{
				$result = $pdo3->prepare("SELECT MAX(memberno) FROM users");
				$result->execute();
			}
			catch (PDOException $e)
			{
					$error = 'Error fetching user: ' . $e->getMessage();
					echo $error;
					exit();
			}
		
			$row = $result->fetch();
				$memberno = $row['MAX(memberno)'] + 1;
			
		} else {

		
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
			
		}
		

			$siteDomain = $_SESSION['domain'];
	
			$memberExp = $paymentTime;
	
			// Query to add new user - 28 arguments
			  $query = sprintf("INSERT INTO users (registeredSince, memberno, userGroup, first_name, last_name, day, month, year, dni, domain, paidUntil, friend, friend2) VALUES ('%s', '%s', '%d', '%s', '%s', '%d', '%d', '%d', '%s', '%s', '%s', '%d', '%d');", 
$insertTime,
$memberno,
$userGroup,
$first_name,
$last_name,
$day,
$month,
$year,
$dni,
$domain,
$insertTime,
$aval,
$aval2
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
	
	
		header("Location: profile.php?user_id=" . $user_id);
		exit();
	}
	/***** FORM SUBMIT END *****/

	$validationScript = <<<EOD
    $(document).ready(function() {
	    
	    	    
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
	

			unset($_SESSION['newmember']);
			
?>
<center>
<form id="registerForm" action="" method="POST" onsubmit="return testInput()">
<input type="hidden" name="nextMemberNo" value="<?php echo $nextMemberNo; ?>" />
  <table>
   <tr>
    <td><?php echo $lang['member-firstnames']; ?></td>
    <td><input type="text" name="first_name" class='defaultinput-no-margin' /><br />&nbsp;</td>
   </tr>
   <tr>
    <td><?php echo $lang['member-lastnames']; ?></td>
    <td><input type="text" name="last_name" class='defaultinput-no-margin' /><br />&nbsp;</td>
   </tr>
   <tr>
    <td><?php echo $lang['dni-or-passport']; ?> #&nbsp;&nbsp;&nbsp;&nbsp;</td>
    <td><input type="text" id="dni" class="idGroup defaultinput-no-margin" name="dni" /><br />&nbsp;</td>
   </tr>
   <tr>
    <td><?php echo $lang['global-birthday']; ?></td>
    <td>
     <input type="number" lang="nb" name="day" class="oneDigit defaultinput-no-margin" maxlength="2" placeholder='dd' />
     <input type="number" lang="nb" name="month" class="oneDigit defaultinput-no-margin" maxlength="2" placeholder='mm' />
     <input type="number" lang="nb" name="year" class="fourDigit defaultinput-no-margin" maxlength="4" placeholder="<?php echo $lang['member-yyyy']; ?>" style='width: 55px;' />
    </td>
   </tr>
  </table>
  

 <div class="clearfloat"></div><br />
 <button class='cta1' name='oneClick' type="submit"><?php echo $lang['global-savechanges']; ?></button>
</form>
</center>
