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


	if(isset($_POST['bank_id'])){

		$bank_id = str_replace("'","\'",str_replace('%', '&#37;', trim($_POST['bank_id'])));

		// Query to update user - 28 arguments
		$insertBankID = "INSERT into payment_bank_id SET bank_id = '$bank_id'";  
		try
		{
			$result = $pdo->prepare("$insertBankID")->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}

		$_SESSION['successMessage'] = "New Bank ID Saved!";
		header("Location: bank-ids.php");
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


	pageStart("Add New Bank ID", NULL, $validationScript, "pprofile", NULL, "Add New Bank ID", $_SESSION['successMessage'], $_SESSION['errorMessage']);
	
	?>
	<center>
			<a href='invoice-payments.php' class='cta1'>Invoice Payments</a>
			<a href='bank-ids.php' class='cta1'>Bank IDs</a>
	</center>
	<center>
		<form id="registerForm" action="" method="POST">
			<div id="mainbox-no-width">
				<div id="mainboxheader"> Add Bank ID </div>
				<div class='boxcontent'>
					<table>
						<tr>
							<td><strong>Bank ID</strong></td>
							<td>
								<input type="text" name="bank_id" class="defaultinput" required="">
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
