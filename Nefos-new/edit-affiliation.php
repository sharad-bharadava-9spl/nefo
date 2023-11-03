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
		$affid = $_POST['affid'];
		
		$insertTime = date("Y-m-d H:i:s");
		
	
		// Query to update user - 28 arguments
		$updateUser = "UPDATE affiliations SET name = '$name' WHERE id = $affid";
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
		$_SESSION['successMessage'] = "Affiliation updated succesfully!";
		header("Location: affiliation.php?affid=$affid");
		exit();
		
	}
	
	$affid = $_GET['affid'];
	
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

	// Query to look up users
	$selectUsers = "SELECT name FROM affiliations WHERE id = $affid";
		try
		{
			$result = $pdo3->prepare("$selectUsers");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
		$row = $result->fetch();
			$name = $row['name'];


	pageStart("Edit affiliation", NULL, $validationScript, "pprofile", NULL, "Edit affiliation", $_SESSION['successMessage'], $_SESSION['errorMessage']);
		
?>


   <form id="registerForm" action="" method="POST">

    <input type="hidden" name="affid" value="<?php echo $affid; ?>" />
    
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
