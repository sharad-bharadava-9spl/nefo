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
	if (isset($_POST['first_name'])) {
		
		$first_name = str_replace("'","\'",str_replace('%', '&#37;', trim($_POST['first_name'])));
		$last_name = str_replace("'","\'",str_replace('%', '&#37;', trim($_POST['last_name'])));
		$dni = str_replace("'","\'",str_replace('%', '&#37;', trim($_POST['dni'])));
		$comment = str_replace("'","\'",str_replace('%', '&#37;', trim($_POST['comment'])));
		$insertTime = date('Y-m-d H:i:s');

	
		// Query to add new category - 11 arguments
		$query = sprintf("INSERT INTO rejected (first_name, last_name, dni, reason, time) VALUES ('%s', '%s', '%s', '%s', '%s');",
		$first_name, $last_name, $dni, $comment, $insertTime);
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
		$_SESSION['successMessage'] = $lang['member-rejected'];
		header("Location: rejected.php");
		exit();
	}
	/***** FORM SUBMIT END *****/

	$validationScript = <<<EOD
    $(document).ready(function() {
	    	    
	  $('#registerForm').validate({
		  rules: {
			  first_name: {
				  required: true
			  },
			  dni: {
				  required: true
			  },
			  last_name: {
				  required: true
        	  }
    	}, // end rules
    	errorPlacement: function(error, element) { },
    	  submitHandler: function() {
   $(".oneClick").attr("disabled", true);
   form.submit();
	    	  }
	  }); // end validate
  }); // end ready
EOD;

	pageStart("Rechazar socio", NULL, $validationScript, "pnewcategory", "", "Rechazar socio", $_SESSION['successMessage'], $_SESSION['errorMessage']);
	
	
?>

<form id="registerForm" action="" method="POST">
 <input type="text" name="first_name" placeholder="<?php echo $lang['member-firstnames']; ?>" /><br />
 <input type="text" name="last_name" placeholder="<?php echo $lang['member-lastnames']; ?>" /><br />
 <input type="text" name="dni" placeholder="DNI" /><br />
 <textarea name="comment" placeholder="<?php echo $lang['reason']; ?>"></textarea><br /><br />
 <button class='oneClick' name='oneClick' type="submit"><?php echo $lang['submit']; ?></button>
</form>

<?php displayFooter(); ?>

