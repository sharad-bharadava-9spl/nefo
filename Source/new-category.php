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
		  ignore: [],
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

	pageStart($lang['new-category'], NULL, $validationScript, "pprofilenew", "donations", $lang['new-category'], $_SESSION['successMessage'], $_SESSION['errorMessage']);
	
	
?>

<form id="registerForm" action="" method="POST">
<center>
<div id="mainbox-no-width">
 <div id="mainboxheader">
  <?php echo $lang['new-category']; ?>
 </div>
 <div class='boxcontent'>
   <input type="text" name="name" placeholder="Name" class='defaultinput' />
   <br />
    <div style='text-align: left; padding-left: 55px;'>
    <br />
   <span>
	<div class='fakeboxholder'>	
	 <label class="control">
	  <span id="errorBox2"></span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?php echo $lang['units']; ?>
	  <input type="radio" name='type' id='type' value='0' />
	  <div class="fakebox"></div>
	 </label>
	</div><br /><br />
	<div class='fakeboxholder'>	
	 <label class="control">
	  <span id="errorBox3"></span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?php echo $lang['grams']; ?>
	  <input type="radio" name='type' id='type' value='1' />
	  <div class="fakebox"></div>
	 </label>
	</div>
   </span>
   </div>
   <br />
  <textarea name="description" placeholder="Description" class='defaultinput' style='height: 100px;'></textarea><br />
 <button class='cta4' name='oneClick' type="submit"><?php echo $lang['submit']; ?></button>
</form>
</div>
</div>
<?php displayFooter(); 