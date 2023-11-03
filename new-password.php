<?php

	require_once 'cOnfig/connection.php';
	require_once 'cOnfig/view.php';
	require_once 'cOnfig/authenticate.php';
	require_once 'cOnfig/languages/common.php';

	session_start();
	$accessLevel = '3';
	
	// Authenticate & authorize
	authorizeUser($accessLevel);
	
	$domain = $_SESSION['domain'];
	
	// Did this page resubmit with a form?
	if ($_POST['pwdaction'] == 'yes') {

		
		$email = trim($_POST['email']);
		//$newpw = crypt($_POST['password'], $email);
		$newpw = $_POST['password'];
		$user_id = $_POST['user_id'];
		$olduserPass = $_POST['olduserPass'];
		$oldemail = $_POST['oldemail'];
		
		// check password sterngth

		  $number = preg_match('@[0-9]@', $newpw);
		  $uppercase = preg_match('@[A-Z]@', $newpw);
		  $specialChars = preg_match('@[^\w]@', $newpw);

		if(strlen($newpw) < 8 || !$number || !$uppercase || !$specialChars) {
			    $msg = "Password must be at least 8 characters in length and must contain at least one number, one upper case letter and one special character.";
			    $_SESSION['errorMessage'] = $msg;
			    header("location: new-password.php?user_id=".$user_id);
			    die;
		 }

		 $newpw = sha1($_POST['password']);

	 	// check for strong password

		$checkPass = sprintf("SELECT userPass2 FROM users WHERE user_id = '%d'", $user_id);
		try
		{
			$resultPass = $pdo3->prepare("$checkPass");
			$resultPass->execute();
			$dataPass = $resultPass->fetch();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user1: ' . $e->getMessage();
				echo $error;
				exit();
		}

		$userPass2 = $dataPass['userPass2'];
		// If current user is tryign to change his password, check his old password first!
		if ($_POST['curruser'] == 'yes') {

			$change_password = 0;
			if($userPass2 != ''){
				$change_password = 0;
				$password_old = sha1($_POST['password_old']);
			}else{
				$change_password = 1;
				$password_old = crypt($_POST['password_old'], $email);
			}
			//$password_old = crypt($_POST['password_old'], $email);
			//$password_old = sha1($_POST['password_old']);
			$olduserPass = $_POST['olduserPass'];
			
			if ($password_old != $olduserPass) {
				
				pageStart($lang['title-newoperator'], NULL, NULL, "pdebt", "index", $lang['newoperator'], $_SESSION['successMessage'], $lang['wrong-old-password']);
				exit();
				
			}
			
		}
			
		// MASTER DB:
		// Does operator already exist?
		$checkUser = "SELECT id FROM users WHERE email = '$email' AND password = '$newpw'";
		try
		{
			$result = $pdo->prepare("$checkUser");
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
			
			pageStart($lang['title-newoperator'], NULL, NULL, "pdebt", "index", $lang['newoperator'], $_SESSION['successMessage'], $lang['invalid-credentials']);
			exit();
			
		} else {
			
			// If olduserpass and oldemail exists, it's an UPDATE
			// If not, it's an INSERT
			if ($olduserPass == '' || $olduserPass == 'NULL') {
				
				// THEN insert new ones!
				$addUser = "INSERT INTO users (email, password, domain, passwordStrong) VALUES ('$email', '$newpw', '$domain', '$newpw')";
				try
				{
					$result = $pdo->prepare("$addUser")->execute();
				}
				catch (PDOException $e)
				{
						$error = 'Error fetching user: ' . $e->getMessage();
						echo $error;
						exit();
				}
				
			} else {
				
			
				// Delete from users where domain and oldemail and oldpassword matches
				$delUser = "DELETE FROM users WHERE email = '$oldemail' AND password = '$olduserPass' AND domain = '$domain'";
				try
				{
					$result = $pdo->prepare("$delUser")->execute();
				}
				catch (PDOException $e)
				{
						$error = 'Error fetching user: ' . $e->getMessage();
						echo $error;
						exit();
				}
				
				// THEN insert new ones!
				$addUser = "INSERT INTO users (email, password, domain, passwordStrong) VALUES ('$email', '$newpw', '$domain', '$newpw')";
				try
				{
					$result = $pdo->prepare("$addUser")->execute();
				}
				catch (PDOException $e)
				{
						$error = 'Error fetching user: ' . $e->getMessage();
						echo $error;
						exit();
				}
				
				
			}
			
		}
			
			
		// Edit user here. Save their new password. Their usergoup has already been updated
		$updateUser = sprintf("UPDATE users SET email = '%s', userPass2 = '%s' WHERE user_id = '%d';",
$email,
$newpw,
$user_id,
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
		$_SESSION['successMessage'] = $lang['confirm-passemailupdated'];
		header("Location: profile.php?user_id=" . $user_id);
		exit();

	}

	
	// Get user ID
	if (isset($_GET['user_id'])) {
		$user_id = $_GET['user_id'];
	} else {
		handleError($lang['error-nomember'],"");
	}
		
	// Look up user details for showing profile on the Sales page
	$userDetails = "SELECT memberno, paidUntil, userGroup, first_name, last_name, photoExt, paidUntil, email, userPass, userPass2 FROM users WHERE user_id = '{$user_id}'";
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
		$memberno = $row['memberno'];
		$first_name = $row['first_name'];
		$last_name = $row['last_name'];
		$paidUntil = $row['paidUntil'];
		$userGroup = $row['userGroup'];
		$photoExt = $row['photoExt'];
		$paidUntil = $row['paidUntil'];
		$email = $row['email'];
		$userPass = $row['userPass'];
		$userPass2 = $row['userPass2'];

	
	$validationScript = <<<EOD
	
    $(document).ready(function() {
	    
	    	    
	  $('#registerForm').validate({
		  ignore:'', //because the radio buttons are hidden, validation ignores them. This way it'll work.
		  rules: {
			  day: {
				  required: true,
				  range:[0,31]
			  },
			  month: {
				  required: true,
				  range:[0,12]
			  },
			  year: {
				  required: true,
				  range:[0,2030]
			  }
    	},
		  errorPlacement: function(error, element) {
			  
			  if (element.attr("name") == "expenseCat") {
        		error.appendTo($('#categoryLink'));
    		  } else if ( element.is(":radio") || element.is(":checkbox")){
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

		if ($_SESSION['domain'] == 'faded') {

			if (($userGroup < $_SESSION['userGroup']) && ($user_id != $_SESSION['user_id'])) {
				
				pageStart($lang['title-newoperator'], NULL, NULL, "pdebt", "index", $lang['newoperator'], $_SESSION['successMessage'], $lang['cant-change-admin-pwd']);
				exit();
			
			}
			
		} else {

			if (($_SESSION['userGroup'] > 1) &&	($user_id != $_SESSION['user_id']) && $userGroup == 1) {
			
				pageStart($lang['title-newoperator'], NULL, NULL, "pdebt", "index", $lang['newoperator'], $_SESSION['successMessage'], $lang['cant-change-admin-pwd']);
				exit();
			}
			
		}

	pageStart($lang['title-newoperator'], NULL, $validationScript, "pprofilenew", "donations fees", $lang['newoperator'], $_SESSION['successMessage'], $_SESSION['errorMessage']);
	
	$memberPhoto = 'images/_' . $_SESSION['domain'] . '/members/' . $user_id . '.' .  $photoExt;
	
	if (!file_exists($memberPhoto)) {
		$memberPhoto = "<img class='profilepic' src='images/silhouette-new-big.png' />";
		$notexist = 'yes';
	} else {
		$memberPhoto = "<img class='profilepic' src='$memberPhoto' width='237' />";
	}
	
	if ($userGroup == 7) {
		$groupName = "<span class='usergrouptextbanned'>$groupName</span>";		
	} else {
		$groupName = "<span class='usergrouptext'>$groupName</span>";
		
	}

		$highRollerWeekly = $_SESSION['highRollerWeekly'];
		$consumptionPercentage = $_SESSION['consumptionPercentage'] / 100;

	// Is the user a high roller?
	if ($totalAmountPerWeek >= $highRollerWeekly && $notexist == 'yes') {
		$highroller = "<br /><img src='images/highroller-big.png' style='margin-top: -4px;' />";
	} else if ($totalAmountPerWeek >= $highRollerWeekly && $notexist != 'yes') {
		$highroller = "<br /><img src='images/highroller-xl.png' style='margin-top: -4px;' />";
	} else {
		$highroller = "";
	}
?>

<div id="mainbox">
 <div id="mainleft">
  <span id="profilepicholder"><a href="new-picture.php?user_id=<?php echo $user_id; ?>"><?php echo $memberPhoto; ?></a><?php echo $highroller; ?></span>
<?php

	echo <<<EOD
   <span class='firsttext'>#$memberno</span><br /><span class='nametext'>$first_name $last_name</span><br />
EOD;
		if ($_SESSION['puestosOrNot'] == 1) {
		
			if ($workStation == 1 || $workStation == 6 || $workStation == 11 || $workStation == 16) {
				echo "<img src='images/puesto-reception.png' height='22' style='margin-bottom: -6px; margin-left: 8px;' />&nbsp;";
			}
			if ($workStation == 5 || $workStation == 6 || $workStation == 15 || $workStation == 16) {
				echo "<img src='images/puesto-bar.png' height='22' style='margin-bottom: -6px; margin-left: 8px;' />&nbsp;";
			}
			if ($workStation == 10 || $workStation == 11 || $workStation == 15 || $workStation == 16) {
				echo "<img src='images/puesto-dispensary.png' height='22' style='margin-bottom: -6px; margin-left: 8px;' />&nbsp;";
			}
		}
		
	if ($_SESSION['showGender'] == 1) {
		$gender = $gender;
	} else {
		$gender = '';
	}
	if ($_SESSION['showAge'] == 1) {
		$age = $age . " " . $lang['member-yearsold'];
	} else {
		$age = '';
	}

		$memberExp = date('y-m-d', strtotime($paidUntil));
		$memberExpReadable = date('d M Y', strtotime($paidUntil));
		$timeNow = date('y-m-d');
		
	echo "<br /><br /><a href='donation-management.php?userid=" . $user_id . "'><span class='creditDisplay'>Credit: <span class='creditAmount $userClass'>" . number_format($credit,2) . " ".$_SESSION['currencyoperator']."$creditEligibility</span></span></a><br /><br />$cuotaWarning";

   echo "</div>";
?>

     
     

 
<div id='donationholder' style='width: 376px; padding-right: 5px;'>
<h4>
<?php
	if ($user_id == $_SESSION['user_id']) {
		echo $lang['choose-login-details-2'];
	} else {
		echo $lang['choose-login-details'];
	}
	if($userPass2 == ''){
		 $old_pass = $userPass;
	}else{
		$old_pass = $userPass2;
	}
?>
</h4>
<form id="registerForm" action="" method="POST">
     <input type="hidden" name='pwdaction' value='yes'>
     <input type="hidden" name='user_id' value='<?php echo $user_id; ?>'>
     <input type="hidden" name='oldemail' value='<?php echo $email; ?>'>
     <input type="hidden" name='olduserPass' value='<?php echo $old_pass; ?>'>
 <h4><?php echo $lang['choose-email-pwd']; ?></h4>
 <br />
 <table>
  <tr>
   <td style='vertical-align: top;'>
<?php if ($user_id == $_SESSION['user_id']) { ?>
     <input type="hidden" name='curruser' value='yes'>
     <input type="hidden" name='email' value='<?php echo $email; ?>'>
     <?php echo $lang['old-password']; ?>: <input type="password" name="password_old" class="defaultinput" placeholder="<?php echo $lang['index-password']; ?>" value="" style='width: 350px;' /><br /><br />
<?php } else { ?>
     E-mail: <input type="email" name="email" value="<?php echo $email; ?>" class="defaultinput" placeholder="<?php echo $lang['member-email']; ?>" style='width: 350px;'/><br /><br />
<?php } ?>
     <?php echo $lang['new-password']; ?>: <input type="password" name="password" class="defaultinput" placeholder="<?php echo $lang['index-password']; ?>" style='width: 350px;' /><br />
<br />
    <button class='oneClick okbutton2' name='oneClick' type="submit" style='margin-left: 1px; width: 365px;'><?php echo $lang['global-confirm']; ?></button> 

   </td>
  </tr>
 </table>

</form>

</div>
</div>


<?php

 displayFooter();


?>
