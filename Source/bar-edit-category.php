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
		$category = $_POST['category'];
		$description = str_replace("'","\'",str_replace('%', '&#37;', trim($_POST['description'])));
	
		// Query to update category
		$updateCat = sprintf("UPDATE b_categories SET name = '%s', description = '%s' WHERE id = '%d';",
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
		$_SESSION['successMessage'] = $lang['bar-category-updated'];
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

	$categoryid = $_GET['categoryid'];

	// Query to look for category
	$categoryDetails = "SELECT name, description FROM b_categories WHERE id = $categoryid";
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
<center>
<div id="mainbox-no-width">
 <div id="mainboxheader">
  <?php echo $lang['edit-category']; ?>
 </div>
 <div class='boxcontent'>
 <input type="hidden" name="category" value="<?php echo $categoryid; ?>" />
   <input type="text" name="name" value="<?php echo $name; ?>" class='defaultinput' /><br />
   <textarea name="description" placeholder="Description" class='defaultinput' style='height: 100px;'><?php echo $description; ?></textarea>
<br />
 <button class='cta4' name='oneClick' type="submit"><?php echo $lang['submit']; ?></button>
</form>
</div>
</div>

<?php displayFooter(); ?>

