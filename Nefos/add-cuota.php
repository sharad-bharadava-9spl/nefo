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
		
		$user_id = $_POST['userid'];
  	    $day = $_POST['day'];
		$month = $_POST['month'];
		$year = $_POST['year'];
		$amountPaid = $_POST['customAmount'];
		$paymentTime = date('Y-m-d H:i:s');	
		
		if ($day > 0 && $month > 0 && $year > 0) {
			
			$date_timestamp = strtotime($month . "/" . $day . "/" . $year);
			$registertime = date("Y-m-d H:i:s", $date_timestamp);
			$memberExp = date('Y-m-d H:i:s', strtotime("+365 day", strtotime($registertime)));
			
		} else {
			
			$memberExp = date('Y-m-d H:i:s', strtotime("+365 day", strtotime(date('Y-m-d H:i:s'))));
			
		}
		
		if ($amountPaid >= 60) {
			
			$settledCuota = 1;
			
		} else {
			
			$settledCuota = 0;
			
		}
		
		// Query to add payment
		$query = sprintf("INSERT INTO memberpayments (paymentdate, userid, amountPaid, newExpiry, completed) VALUES ('%s', '%d', '%f', '%s', '%d');",
			$registertime, $user_id, $amountPaid, $memberExp, $settledCuota);
			
		mysql_query($query)
			or handleError($lang['error-savedata'],"Error inserting user: " . mysql_error());
			
		$paymentid = mysql_insert_id();
			
		$query = sprintf("INSERT INTO f_memberpayments (paymentdate, userid, amountPaid, newExpiry, completed) VALUES ('%s', '%d', '%f', '%s', '%d');",
		$registertime, $user_id, $amountPaid, $memberExp, $settledCuota);
		  			
		mysql_query($query)
			or handleError($lang['error-savedata'],"Error inserting user: " . mysql_error());
	
	
		// Query to add payment part
		$query = sprintf("INSERT INTO memberpaymentparts (time, paymentid, amount, userid) VALUES ('%s', '%d', '%f', '%d');",
		$registertime, $paymentid, $amountPaid, $user_id);
		  			
		mysql_query($query)
			or handleError($lang['error-savedata'],"Error inserting user: " . mysql_error());


		// Query to update user
		$query = "UPDATE users SET paymentWarning = '0', paymentWarningDate = NULL, paidUntil = '$memberExp' WHERE user_id = '$user_id'";
		  
		mysql_query($query)
			or handleError($lang['error-savedata'],"Error inserting expense: " . mysql_error());

		// Add initial payment
		
			
		// On success: redirect.
		$_SESSION['successMessage'] = "Pago inicial a&ntilde;adido con &eacute;xito";
		header("Location: profile.php?user_id=$user_id");
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
			  customAmount: {
				  required: true
			  },
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



	

	pageStart("Añadir pago inicial", NULL, $validationScript, "pmembership", "admin", "Añadir pago inicial", $_SESSION['successMessage'], $_SESSION['errorMessage']);
	

echo "<center><div id='profilearea'><img src='images/members/$user_id.$photoExt' class='salesPagePic' /><h4>#$memberno - $first_name $last_name</h4></div></center>";

	
?>
<br />
 <div id="overviewWrap">
 <div class="overview">


<form id="registerForm" action="" method="POST" class='white'>
   <input type="hidden" name="userid" value="<?php echo $user_id; ?>" />
   <input type="hidden" name="paidUntil" value="<?php echo $paidUntil; ?>" />
   <strong>Importe:</strong><br />
   <input type="number" name="customAmount" class="fourDigit" placeholder="&euro;" /><br /><br />
   <strong>Fecha de pago:</strong><br />
   <input type="number" lang="nb" name="day" class="twoDigit" maxlength="2" placeholder="dd" />
   <input type="number" lang="nb" name="month" class="twoDigit" maxlength="2" placeholder="mm" />
   <input type="number" lang="nb" name="year" class="fourDigit" maxlength="4" placeholder="<?php echo $lang['member-yyyy']; ?>" />

<br /><br />
<textarea name="comment" placeholder="<?php echo $lang['global-comment']; ?>?"></textarea>

 <button class='oneClick' name='oneClick' type="submit">A&ntilde;adir</button>
 
</form>
</div></div>

<?php displayFooter(); ?>
