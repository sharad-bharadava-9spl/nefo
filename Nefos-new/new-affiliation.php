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
	if (isset($_POST['name'])) {
		
		$name = str_replace("'","\'",str_replace('%', '&#37;', trim($_POST['name'])));
		
		$insertTime = date("Y-m-d H:i:s");
		
	
		// Query to update user - 28 arguments
		$updateUser = sprintf("INSERT INTO affiliations (name, time) VALUES ('%s', '%s')",
$name,
$insertTime
);

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
		$affid = $pdo3->lastInsertId();

		// On success: redirect.
		$_SESSION['successMessage'] = "Affiliation added succesfully!";
		header("Location: add-affiliate.php?affid=$affid");
		exit();
		
	}
	
	/***** FORM SUBMIT END *****/
	
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



	pageStart("New affiliation", NULL, $validationScript, "pprofile", NULL, "New affiliation", $_SESSION['successMessage'], $_SESSION['errorMessage']);
		
?>


   <form id="registerForm" action="" method="POST">

    
<div class="overview" style="width: 250px;">
 
<table class='profileTable' style='text-align: left; margin: 0;'>
 <tr>
  <td><strong>Name</strong></td>
  <td><input type="text" name="name" value="<?php echo $name; ?>" /></td>
 </tr>
</table>
 <br />
<button class='oneClick' name='oneClick' type="submit"><?php echo $lang['global-savechanges']; ?></button>


<?php displayFooter(); ?>
