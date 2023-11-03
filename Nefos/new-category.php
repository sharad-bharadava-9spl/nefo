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
	$type = $_POST['type'];
	$description = str_replace("'","\'",str_replace('%', '&#37;', trim($_POST['description'])));
	$insertTime = date('Y-m-d H:i:s');
	
		// Query to add new category - 11 arguments
		  $query = sprintf("INSERT INTO categories (time, name, description, type) VALUES ('%s', '%s', '%s', '%d');",
		  $insertTime, $name, $description, $type);
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
		$_SESSION['successMessage'] = $lang['category-added'];
		header("Location: categories.php");
		exit();
	}
	/***** FORM SUBMIT END *****/

	$validationScript = <<<EOD
    $(document).ready(function() {
	    	    
	  $('#registerForm').validate({
		  rules: {
			  name: {
				  required: true
			  },
			  type: {
				  required: true
			  }
    	}, // end rules
		  errorPlacement: function(error, element) {
			  if ( element.is(":radio") || element.is(":checkbox")){
				 error.appendTo(element.parent());
			}
		},
    	  submitHandler: function() {
   $(".oneClick").attr("disabled", true);
   form.submit();
	    	  }
	  }); // end validate
  }); // end ready
EOD;

	pageStart($lang['new-category'], NULL, $validationScript, "pnewcategory", "", $lang['new-category'], $_SESSION['successMessage'], $_SESSION['errorMessage']);
	
	
?>

<form id="registerForm" action="" method="POST">
   <h3><?php echo $lang['new-category']; ?></h3>
   <input type="text" name="name" placeholder="<?php echo $lang['global-name']; ?>" /><br /><br />
   <div style='text-align: left;'>
   <span>
    <input type="radio" name="type" value="0" style="margin-left: 30px; width: 15px;" checked><?php echo $lang['units']; ?></input><br />
    <input type="radio" name="type" value="1" style="margin-left: 30px; width: 15px;"><?php echo $lang['grams']; ?></input><br /><br />
   </span>
   </div>
   <textarea name="description" placeholder="<?php echo $lang['extracts-description']; ?>"></textarea><br />
<br />
 <button class='oneClick' name='oneClick' type="submit"><?php echo $lang['submit']; ?></button>
</form>

<?php displayFooter(); ?>

