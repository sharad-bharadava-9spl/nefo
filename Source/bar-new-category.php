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
		$description = str_replace("'","\'",str_replace('%', '&#37;', trim($_POST['description'])));
		$insertTime = date('Y-m-d H:i:s');
	
		// Query to add new category - 11 arguments
		  $query = sprintf("INSERT INTO b_categories (time, name, description) VALUES ('%s', '%s', '%s');",
		  $insertTime, $name, $description);
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
		$_SESSION['successMessage'] = $lang['bar-category-added'];
		header("Location: bar-categories.php");
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
    	errorPlacement: function(error, element) { },
    	  submitHandler: function() {
   $(".oneClick").attr("disabled", true);
   form.submit();
	    	  }
	  }); // end validate
  }); // end ready
EOD;

	pageStart($lang['new-bar-category'], NULL, $validationScript, "pnewcategory", "", $lang['new-bar-category'], $_SESSION['successMessage'], $_SESSION['errorMessage']);
	
	
?>

<form id="registerForm" action="" method="POST">
<center>
<div id="mainbox-no-width">
 <div id="mainboxheader">
  <?php echo $lang['new-bar-category']; ?>
 </div>
 <div class='boxcontent'>
   <input type="text" name="name" placeholder="Name" class='defaultinput' /><br />
   <textarea name="description" placeholder="Description" class='defaultinput' style='height: 100px;'></textarea><br />
 <button class='cta4' name='oneClick' type="submit"><?php echo $lang['submit']; ?></button>
</form>
</div>
</div>
<?php displayFooter(); ?>

