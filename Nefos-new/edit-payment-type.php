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


	if(isset($_POST['id'])){


		$id = $_POST['id'];

		$code = str_replace("'","\'",str_replace('%', '&#37;', trim($_POST['code'])));
		$name = str_replace("'","\'",str_replace('%', '&#37;', trim($_POST['name'])));

		// Query to update user - 28 arguments
		$updatePaymentType = "UPDATE payment_types SET code = '$code', name = '$name' WHERE id =".$id;  
		try
		{
			$result = $pdo->prepare("$updatePaymentType")->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}

		$_SESSION['successMessage'] = "Payment Type Updated !";
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


	pageStart("Edit payment Type", NULL, $validationScript, "pprofile", NULL, "Edit payment Type", $_SESSION['successMessage'], $_SESSION['errorMessage']);

	$id = $_GET['id'];

	// fetch payment type

	$slectPaymentType = "SELECT * FROM payment_types WHERE id =".$id;

	try
	{
		$payment_results = $pdo->prepare("$slectPaymentType");
		$payment_results->execute();
	}
	catch (PDOException $e)
	{
			$error = 'Error fetching user: ' . $e->getMessage();
			echo $error;
			exit();
	}
	$payment_row =$payment_results->fetch();
		$payment_code = $payment_row['code'];
		$payment_name = $payment_row['name'];

	
	?>
	<center>
			<a href='invoice-payments.php' class='cta1'>Invoice Payments</a>
			<a href='payments-types.php' class='cta1'>Payment Types</a>
	</center>
	<center>
		<form id="registerForm" action="" method="POST">
			<div id="mainbox-no-width">
				<div id="mainboxheader"> Edit Payment Type </div>
				<input type="hidden" name="id" value="<?php echo $id; ?>">
				<div class='boxcontent'>
					<table>
						<tr>
							<td><strong>Code</strong></td>
							<td>
								<input type="text" name="code" class="defaultinput" value="<?php echo $payment_code; ?>" required="">
							</td>
						</tr>						
						<tr>
							<td><strong>Name</strong></td>
							<td>
								<input type="text" name="name" class="defaultinput" value="<?php echo $payment_name; ?>" required="">
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
