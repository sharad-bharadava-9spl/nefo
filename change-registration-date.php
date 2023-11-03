<?php
	
	require_once 'cOnfig/connection.php';
	require_once 'cOnfig/view.php';
	require_once 'cOnfig/authenticate.php';
	require_once 'cOnfig/languages/common.php';
	require_once 'googleConfig.php';
	
	session_start();
	$accessLevel = '3';
	
	// Authenticate & authorize
	authorizeUser($accessLevel);
	
	// Did this page re-submit with a form? If so, check & store details
	if (isset($_POST['month'])) {
		
		
	
		$userid = $_POST['userid'];
  	    $day = $_POST['day'];
		$month = $_POST['month'];
		$year = $_POST['year'];
		
		if ($day > 0 && $month > 0 && $year > 0) {
			$registertime = date("Y-m-d H:i:s", strtotime($month . "/" . $day . "/" . $year));
		} else {
			$registertime = date('Y-m-d H:i:s'); // 	$purchaseDate = date('Y-m-d H:i:s'); ????
		}
		

		// Query to add new sale to Sales table - 6 arguments
		  $query = "UPDATE users SET registeredSince = '$registertime' WHERE user_id = '$userid'";
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

			
			// On success: redirect.
			$_SESSION['successMessage'] = $lang['changed-registration-date'];
			header("Location: profile.php?user_id=$userid");
			exit();
		}
	/***** FORM SUBMIT END *****/
	
	
	
	// Get the user ID
	if (isset($_GET['userid'])) {
		$user_id = $_GET['userid'];
	} else {
		handleError($lang['error-nouserid'],"");
	}

	// Look up user details for showing profile on the Sales page
	$userDetails = "SELECT memberno, userGroup, first_name, last_name, registeredSince, photoExt, credit FROM users WHERE user_id = '{$user_id}'";
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
			$userGroup = $row['userGroup'];
			$photoExt = $row['photoExt'];
			$credit = $row['credit'];
			$dayReg = date("d", strtotime($row['registeredSince']));
			$monthReg = date("m", strtotime($row['registeredSince']));
			$yearReg = date("y", strtotime($row['registeredSince']));
	
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



	

	pageStart($lang['change-reg-date'], NULL, $validationScript, "pprofilenew", "donations fees", $lang['change-reg-date'], $_SESSION['successMessage'], $_SESSION['errorMessage']);
	
	$memberPhoto = 'images/_' . $_SESSION['domain'] . '/members/' . $user_id . '.' .  $photoExt;

	$object_exist = object_exist($google_bucket, $google_root_folder.$memberPhoto);
	
	if (!$object_exist) {
		$memberPhoto = "<img class='profilepic' src='{$google_root}images/silhouette-new-big.png' />";
		$notexist = 'yes';
	} else {
		$memberPhoto = "<img class='profilepic' src='{$google_root}$memberPhoto' width='237' />";
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



	echo "<br /><br /><a href='donation-management.php?userid=" . $user_id . "'><span class='creditDisplay'>Credit: <span class='creditAmount $userClass'>" . number_format($credit,2) . " ".$_SESSION['currencyoperator']."$creditEligibility</span></span></a><br /><br />$cuotaWarning";

   echo "</div>";
?>


<div id='donationholder'>
<form id="registerForm" action="" method="POST">
   <input type="hidden" name="userid" value="<?php echo $user_id; ?>" />
   <input type="hidden" name="paidUntil" value="<?php echo $paidUntil; ?>" />
 <h4><?php echo $lang['change-reg-date']; ?></h4>
 <br />
 <table>
  <tr>
   <td style='vertical-align: top;'>
    <input type="number" lang="nb" name="day" class="twoDigit defaultinput" maxlength="2" placeholder="dd" />
    <input type="number" lang="nb" name="month" class="twoDigit defaultinput" maxlength="2" placeholder="mm" />
    <input type="number" lang="nb" name="year" class="fourDigit defaultinput" maxlength="4" placeholder="<?php echo $lang['member-yyyy']; ?>" /><br /><br />
    <button class='oneClick okbutton2' name='oneClick' type="submit" style='margin-left: 1px; width: 208px;'><?php echo $lang['global-confirm']; ?></button> 

   </td>
   <td style='display: inline-block; vertical-align: top; margin-left: 10px; margin-top: -10px;'>
    <textarea name="comment" placeholder="<?php echo $lang['global-comment']; ?>?" style='height: 76px;'></textarea>
   </td>
  </tr>
 </table>

</form>

</div>
</div>
