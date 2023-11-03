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

		$bank_id = str_replace("'","\'",str_replace('%', '&#37;', trim($_POST['bank_id'])));

		// Query to update user - 28 arguments
		$updateBankID = "UPDATE payment_bank_id SET bank_id = '$bank_id' WHERE id =".$id;  
		try
		{
			$result = $pdo->prepare("$updateBankID")->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}

		$_SESSION['successMessage'] = "Bank ID Updated !";
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


	pageStart("Edit Bank ID", NULL, $validationScript, "pprofile", NULL, "Edit Bank ID", $_SESSION['successMessage'], $_SESSION['errorMessage']);

	$id = $_GET['id'];

	// fetch bank id details

	$selectBankId = "SELECT * FROM payment_bank_id WHERE id =".$id;

	try
	{
		$bank_results = $pdo->prepare("$selectBankId");
		$bank_results->execute();
	}
	catch (PDOException $e)
	{
			$error = 'Error fetching user: ' . $e->getMessage();
			echo $error;
			exit();
	}
	$bank_id_row = $bank_results->fetch();
		$bank_id = $bank_id_row['bank_id'];
	
	?>
	<center>
			<a href='invoice-payments.php' class='cta1'>Invoice Payments</a>
			<a href='bank-ids.php' class='cta1'>Bank IDs</a>
	</center>
	<center>
		<form id="registerForm" action="" method="POST">
			<div id="mainbox-no-width">
				<input type="hidden" name="id" value="<?php echo $id; ?>">
				<div id="mainboxheader"> Add Bank ID </div>
				<div class='boxcontent'>
					<table>
						<tr>
							<td><strong>Bank ID</strong></td>
							<td>
								<input type="text" name="bank_id" value="<?php echo $bank_id; ?>" class="defaultinput" required="">
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
