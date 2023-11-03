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


	if(isset($_POST['code'])){

		$code = str_replace("'","\'",str_replace('%', '&#37;', trim($_POST['code'])));
		$name = str_replace("'","\'",str_replace('%', '&#37;', trim($_POST['name'])));

		// Query to update user - 28 arguments
		$insertPaymentType = "INSERT into payment_types SET code = '$code', name = '$name'";  
		try
		{
			$result = $pdo->prepare("$insertPaymentType")->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}

		$_SESSION['successMessage'] = "New Payment Type Saved!";
		header("Location: payment-types.php");
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


	pageStart("Add New payment Type", NULL, $validationScript, "pprofile", NULL, "Add New payment Type", $_SESSION['successMessage'], $_SESSION['errorMessage']);
	
	?>
	<center>
			<a href='invoice-payments.php' class='cta1'>Invoice Payments</a>
			<a href='bank-ids.php' class='cta1'>Bank IDs</a>
	</center>
	<center>
		<form id="registerForm" action="" method="POST">
			<div id="mainbox-no-width">
				<div id="mainboxheader"> Add Payment Type </div>
				<div class='boxcontent'>
					<table>
						<tr>
							<td><strong>Code</strong></td>
							<td>
								<input type="text" name="code" class="defaultinput" required="">
							</td>
						</tr>						
						<tr>
							<td><strong>Name</strong></td>
							<td>
								<input type="text" name="name" class="defaultinput" required="">
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
<?php  displayFooter();
