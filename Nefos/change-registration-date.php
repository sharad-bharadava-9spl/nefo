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
		
		if ($day > 0 && $month > 0 && $year > 0) {
			$registertime = date("Y-m-d H:i:s", strtotime($month . "/" . $day . "/" . $year));
		} else {
			$registertime = date('Y-m-d H:i:s'); // 	$purchaseDate = date('Y-m-d H:i:s'); ????
		}
		

		// Query to add new sale to Sales table - 6 arguments
		  $query = "UPDATE users SET registeredSince = '$registertime' WHERE user_id = '$userid'";
		  
		mysql_query($query)
			or handleError($lang['error-savedata'],"Error inserting expense: " . mysql_error());

			
			// On success: redirect.
			$_SESSION['successMessage'] = "Changed reigstration date successfully";
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
	$userDetails = "SELECT memberno, userGroup, first_name, last_name, registeredSince, photoExt FROM users WHERE user_id = '{$user_id}'";
	
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
			$userGroup = $row['userGroup'];
			$photoExt = $row['photoExt'];
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



	

	pageStart("Change registration date", NULL, $validationScript, "pmembership", "admin", "REGISTRATION DATE", $_SESSION['successMessage'], $_SESSION['errorMessage']);

echo "<center><div id='profilearea'><img src='images/members/$user_id.$photoExt' class='salesPagePic' /><h4>#$memberno - $first_name $last_name</h4></div></center>";

	
?>
<br />
 <div id="overviewWrap">
 <div class="overview">


<form id="registerForm" action="" method="POST">
   <input type="hidden" name="userid" value="<?php echo $user_id; ?>" />
   <input type="number" lang="nb" name="day" class="twoDigit" maxlength="2" value="<?php echo $dayReg; ?>" />
   <input type="number" lang="nb" name="month" class="twoDigit" maxlength="2" value="<?php echo $monthReg; ?>" />
   <input type="number" lang="nb" name="year" class="fourDigit" maxlength="4" value="<?php echo $yearReg; ?>" />

<br /><br />

 <button class='oneClick' name='oneClick' type="submit">Cambiar</button>
 
</form>
</div></div>

<?php displayFooter(); ?>
