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

	$name = $_POST['name'];
	$category = $_POST['category'];
	$description = $_POST['description'];
	
		// Query to update category
		$updateCat = sprintf("UPDATE categories SET name = '%s', description = '%s' WHERE id = '%d';",
			$name,
			$description,
			$category
		);

		  
		try
		{
			$result = $pdo3->prepare("$updateCat")->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
			
		// On success: redirect.
		$_SESSION['successMessage'] = $lang['category-updated'];
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

	$categoryid = $_GET['categoryid'];

	// Query to look for category
	$categoryDetails = "SELECT name, description FROM categories WHERE id = $categoryid";
		try
		{
			$result = $pdo3->prepare("$categoryDetails");
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
		$description = $row['description'];


	pageStart($lang['edit-category'], NULL, $validationScript, "pnewcategory", "", $lang['edit-category'], $_SESSION['successMessage'], $_SESSION['errorMessage']);
	
	
?>

<form id="registerForm" action="" method="POST">
 <input type="hidden" name="category" value="<?php echo $categoryid; ?>" />
   <h3><?php echo $lang['edit-category']; ?></h3>
   <input type="text" name="name" value="<?php echo $name; ?>" /><br />
   <textarea name="description" placeholder="Description"><?php echo $description; ?></textarea><br />
<br />
 <button class='oneClick' name='oneClick' type="submit"><?php echo $lang['submit']; ?></button>
</form>

<?php displayFooter(); ?>

