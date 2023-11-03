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
// Did this page re-submit with a form? If so, check & store details
	if (isset($_POST['id'])) {
		$id = $_POST['id'];
		$credit_reason = str_replace("'","\'",str_replace('%', '&#37;', trim($_POST['credit_reason'])));

		// Query to update user - 28 arguments
		 $updateUser = "UPDATE credit_reasons SET reason = '$credit_reason'  WHERE id = $id"; 
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
		$_SESSION['successMessage'] = "Reason updated succesfully!";
		header("Location: credit-reasons.php");
		exit();
	}
	
	$validationScript = <<<EOD
    $(document).ready(function() {


    	    
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
	pageStart("Edit Credit Reason", NULL, $validationScript, "pprofile", NULL, "Edit Credit Reason", $_SESSION['successMessage'], $_SESSION['errorMessage']);
	
	$id = $_GET['id'];
	// Query to look up calls
	$selectUsers = "SELECT * FROM  credit_reasons WHERE id = $id";
	try
	{
		$results = $pdo3->prepare("$selectUsers");
		$results->execute();
	}
	catch (PDOException $e)
	{
			$error = 'Error fetching user: ' . $e->getMessage();
			echo $error;
			exit();
	}
	$row = $results->fetch();
		
		$reason = $row['reason'];


	?>
<center>
		<a href='credits.php' class='cta1'>Credits</a>
		<a href='credit-reasons.php' class='cta1'>Credit Reasons</a>
</center>
	<center>
		<form id="registerForm" action="" method="POST">
			<input type="hidden" name="id" value="<?php echo $id; ?>" />
			<div id="mainbox-no-width">
				<div id="mainboxheader"> Edit Credit Reason </div>
				<div class='boxcontent'>
					<table>
						<tr>
							<td><strong> Credit Reason </strong></td>
							<td>
								<input type="text" name="credit_reason" class="defaultinput" value="<?php echo $reason; ?>" required="">
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
