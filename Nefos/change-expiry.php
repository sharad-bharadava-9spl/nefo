<?php
	
	require_once 'cOnfig/connection.php';
	require_once 'cOnfig/view.php';
	require_once 'cOnfig/authenticate.php';
	require_once 'cOnfig/languages/common.php';
	
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
		$paidUntil = $_POST['paidUntil'];
		
		if ($day > 0 && $month > 0 && $year > 0) {
			$date_timestamp = strtotime($month . "/" . $day . "/" . $year);
			$registertime = date("Y-m-d H:i:s", $date_timestamp);
		} else {
			$registertime = date('Y-m-d H:i:s');
		}
		

		// Query to add new sale to Sales table - 6 arguments
		  $query = "UPDATE users SET paymentWarning = '0', paymentWarningDate = NULL, paidUntil = '$registertime' WHERE user_id = '$userid'";
		  
		mysql_query($query)
			or handleError($lang['error-savedata'],"Error inserting expense: " . mysql_error());

		// Write to log
		$logTime = date('Y-m-d H:i:s');
	
		$query = sprintf("INSERT INTO log (logtype, logtime, user_id, operator, oldExpiry, newExpiry) VALUES ('%d', '%s', '%d', '%d', '%s', '%s');",
		8, $logTime, $userid, $_SESSION['user_id'], $paidUntil, $registertime);
		
		mysql_query($query)
			or handleError($lang['error-savedata'],"Error inserting purchase: " . mysql_error());
			
		$query = sprintf("INSERT INTO f_log (logtype, logtime, user_id, operator, oldExpiry, newExpiry) VALUES ('%d', '%s', '%d', '%d', '%s', '%s');",
		8, $logTime, $userid, $_SESSION['user_id'], $paidUntil, $registertime);
		
		mysql_query($query)
			or handleError($lang['error-savedata'],"Error inserting purchase: " . mysql_error());
			
			// On success: redirect.
			$_SESSION['successMessage'] = "Changed expiry successfully";
			header("Location: profile.php?user_id=$userid");
			exit();
		}
	/***** FORM SUBMIT END *****/
	
	
	
	// Get the user ID
	if (isset($_REQUEST['userid'])) {
		$user_id = $_REQUEST['userid'];
	} else {
		handleError($lang['error-nouserid'],"");
	}

	// Look up user details for showing profile on the Sales page
	$userDetails = "SELECT memberno, paidUntil, userGroup, first_name, last_name, photoExt, paidUntil FROM users WHERE user_id = '{$user_id}'";
	
	// Does user ID exist?
	$userCheck = mysql_query($userDetails);
	if(mysql_num_rows($userCheck) == 0) {
   		handleError($lang['error-useridnotexist'],"");
	}
	
		$result = mysql_query($userDetails)
			or handleError($lang['error-userload'],"Error loading user: " . mysql_error());
	
		$row = mysql_fetch_array($result);
		$memberno = $row['memberno'];
		$first_name = $row['first_name'];
		$last_name = $row['last_name'];
		$paidUntil = $row['paidUntil'];
		$userGroup = $row['userGroup'];
		$photoExt = $row['photoExt'];
		$paidUntil = $row['paidUntil'];

	
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



	

	pageStart("Change membership expiry", NULL, $validationScript, "pmembership", "admin", "EXPIRY", $_SESSION['successMessage'], $_SESSION['errorMessage']);
	

echo "<center><div id='profilearea'><img src='images/members/$user_id.$photoExt' class='salesPagePic' /><h4>#$memberno - $first_name $last_name</h4></div></center>";

	
?>
<br />
 <div id="overviewWrap">
 <div class="overview">


<form id="registerForm" action="" method="POST">
   <input type="hidden" name="userid" value="<?php echo $user_id; ?>" />
   <input type="hidden" name="paidUntil" value="<?php echo $paidUntil; ?>" />
   <input type="number" lang="nb" name="day" class="twoDigit" maxlength="2" placeholder="dd" />
   <input type="number" lang="nb" name="month" class="twoDigit" maxlength="2" placeholder="mm" />
   <input type="number" lang="nb" name="year" class="fourDigit" maxlength="4" placeholder="<?php echo $lang['member-yyyy']; ?>" />

<br /><br />
<textarea name="comment" placeholder="<?php echo $lang['global-comment']; ?>?"></textarea>

 <button class='oneClick' name='oneClick' type="submit">Cambiar</button>
 
</form>
</div></div>

<?php displayFooter(); ?>
