<?php
	//Created by Konstant for Task-14954900 on 14/10/2021
	require_once 'cOnfig/connection.php';
	require_once 'cOnfig/viewv6.php';
	require_once 'cOnfig/authenticate.php';
	require_once 'cOnfig/languages/common.php';
	
	session_start();
	$accessLevel = '3';
		
	// Authenticate & authorize
	authorizeUser($accessLevel);
	
	// Did this page re-submit with a form? If so, check & store details
	if (isset($_POST['provider_name'])) {
		$provider_name = str_replace("'","\'",str_replace('%', '&#37;', trim($_POST['provider_name'])));
		$contact_person = str_replace("'","\'",str_replace('%', '&#37;', trim($_POST['contact_person'])));
		$address = str_replace("'","\'",str_replace('%', '&#37;', trim($_POST['address'])));
		$email = str_replace("'","\'",str_replace('%', '&#37;', trim($_POST['email'])));
		$phone_number = str_replace("'","\'",str_replace('%', '&#37;', trim($_POST['phone_number'])));
		$insertTime = date('Y-m-d H:i:s');
		
		// Query to add new category - 11 arguments
		$query = sprintf("INSERT INTO hw_providers (name, contact, email, address, phone_numbers, registered) VALUES ('%s', '%s', '%s', '%s', '%s', '%s');",
		$provider_name, $contact_person, $email, $address, $phone_number, $insertTime);
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
		$_SESSION['successMessage'] = $lang['provider-added'];
		header("Location: hw-providers.php");
		exit();
	}
	/***** FORM SUBMIT END *****/

	$validationScript = <<<EOD
    $(document).ready(function() {
	    	    
	  $('#registerForm').validate({
		  rules: {
			  provider_name: {
				  required: true
			  },

    	}, // end rules
    	errorPlacement: function(error, element) { },
    	  submitHandler: function() {
   $(".oneClick").attr("disabled", true);
   form.submit();
	    	  }
	  }); // end validate
  }); // end ready
EOD;

	pageStart($lang['new-provider'], NULL, $validationScript, "pnewcategory", "", $lang['new-provider'], $_SESSION['successMessage'], $_SESSION['errorMessage']);
	

	
?>

<form id="registerForm" action="" method="POST">
	<center>
		<div id="mainbox-no-width">
			 <div id="mainboxheader">
			  <?php echo $lang['provider']; ?>
			 </div>
			 <div class='boxcontent'>
				 <input type="text" lang="nb" id="provider_name" class="defaultinput" name="provider_name" value="" placeholder="Provider Name" />  

				  <br />

				 <input type="text" name="contact_person" placeholder="Contact Person" class='defaultinput' /><br />
				 <textarea name="address" placeholder="Address" class='defaultinput' style='height: 100px;'></textarea><br /><br />
				 <input type="text" name="email" placeholder="E-mail" class='defaultinput' /><br />
				 <input type="text" name="phone_number" placeholder="Phone Number(s)" class='defaultinput' /><br />
				 <button class='cta4' name='oneClick' type="submit"><?php echo $lang['submit']; ?></button>
			</div>
		</div>
	</center>
</form>

<?php displayFooter();