<?php
	
	require_once 'cOnfig/connection.php';
	// require_once 'cOnfig/view.php';	
	require_once 'cOnfig/viewv6.php';
	require_once 'cOnfig/authenticate.php';
	require_once 'cOnfig/languages/common.php';
	
	session_start();
	$accessLevel = '3';
	
	// Authenticate & authorize
	authorizeUser($accessLevel);

	if (isset($_POST['credit_reason'])) {
		$credit_reason = str_replace("'","\'",str_replace('%', '&#37;', trim($_POST['credit_reason'])));

		
		// check duplicate department
	  $checkElement = "SELECT reason from credit_reasons where reason = '$credit_reason'"; 
		try
		{
			$chkResult = $pdo3->prepare("$checkElement");
			$chkResult->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	    $chkcount = $chkResult->rowCount(); 
		if($chkcount>0){
			$_SESSION['errorMessage'] = "Credit Reason already exist !";
			header("Location: new-credit-reason.php");
			exit();
		}
		// Query to update user - 28 arguments
		 $updateUser = "INSERT into credit_reasons SET reason = '".$credit_reason."'"; 
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
		
		$_SESSION['successMessage'] = "Credit Reason saved !";
		header("Location: credit-reasons.php");
		exit();
	}

	$validationScript = <<<EOD
    $(document).ready(function() {

	  $( "#datepicker" ).datepicker({
	  	   dateFormat: "yy-mm-dd"
	  	});	  
	  	$( "#deadline" ).datepicker({
	  	   dateFormat: "yy-mm-dd"
	  	});
    	    
	  $('#registerForm').validate({
		  rules: {
			  name: {
				  required: true
			  }
    	}, // end rules
		  errorPlacement: function(error, element) {
			if (element.is("#savesig")){
				 error.appendTo("#errorBox1");
			} else if (element.is("#accept2")){
				 error.appendTo("#errorBox2");
			} else if (element.is("#accept3")){
				 error.appendTo("#errorBox3");
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
	pageStart("Add Credit Reason", NULL, $validationScript, "pprofile", NULL, "Add Credit Reason", $_SESSION['successMessage'], $_SESSION['errorMessage']);
	
	?>
	<center>
		<a href='credits.php' class='cta1'>Credits</a>
		<a href='credit-reasons.php' class='cta1'>Credit Reasons</a>
	</center>
	<center>
		<form id="registerForm" action="" method="POST">
			<div id="mainbox-no-width">
				<div id="mainboxheader"> Add Credit Reason </div>
				<div class='boxcontent'>
					<table>
						<tr>
							<td><strong> Credit Reason </strong></td>
							<td>
								<input type="text" name="credit_reason" class="defaultinput" required="">
							</td>
						</tr>						
					</table>
				</div>
			</div>
			<br />
			<button class='oneClick cta1' name='oneClick' type="submit">
				<?php echo $lang['global-savechanges']; ?>
			</button>
			</div>
		</form>
	</center>	
<?php  displayFooter(); ?>
