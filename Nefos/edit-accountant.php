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
		
		$id = $_POST['id'];
		$name = str_replace("'","\'",str_replace('%', '&#37;', trim($_POST['name'])));
		$telephone = str_replace("'","\'",str_replace('%', '&#37;', trim($_POST['telephone'])));
		$email = str_replace("'","\'",str_replace('%', '&#37;', trim($_POST['email'])));
		$street = str_replace("'","\'",str_replace('%', '&#37;', trim($_POST['street'])));
		$streetnumber = str_replace("'","\'",str_replace('%', '&#37;', trim($_POST['streetnumber'])));
		$flat = str_replace("'","\'",str_replace('%', '&#37;', trim($_POST['flat'])));
		$postcode = str_replace("'","\'",str_replace('%', '&#37;', trim($_POST['postcode'])));
		$city = str_replace("'","\'",str_replace('%', '&#37;', trim($_POST['city'])));
		$state = str_replace("'","\'",str_replace('%', '&#37;', trim($_POST['state'])));
		$country = str_replace("'","\'",str_replace('%', '&#37;', trim($_POST['country'])));
		
		$insertTime = date("Y-m-d H:i:s");
		
	
		// Query to update user - 28 arguments
		$updateUser = "UPDATE accountants SET name = '$name', telephone = '$telephone', email = '$email', street = '$street', streetnumber = '$streetnumber', flat = '$flat', postcode = '$postcode', city = '$city', state = '$state', country = '$country' WHERE id = $id";
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
		$_SESSION['successMessage'] = "accountant updated succesfully!";
		header("Location: accountants.php");
		exit();
	}
	
	/***** FORM SUBMIT END *****/
	
	$id = $_GET['accountantid'];
	
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

	// Query to look up accountant
	$selectUsers = "SELECT name, telephone, email, street, streetnumber, flat, postcode, state, city, country FROM accountants WHERE id = $id";
	try
	{
		$results = $pdo3->prepare("$selectUsers");
		$results->execute();
	}
	catch (PDOException $e)
	{
			$error = 'Error fetching user: ' . $e->getMessage();
			echo $error;
			exit();
	}
	$row = $results->fetch();
		$name = $row['name'];
		$telephone = $row['telephone'];
		$email = $row['email'];
		$street = $row['street'];
		$streetnumber = $row['streetnumber'];
		$flat = $row['flat'];
		$postcode = $row['postcode'];
		$city = $row['city'];
		$state = $row['state'];
		$country = $row['country'];

	pageStart("Edit accountant", NULL, $validationScript, "pprofile", NULL, "Edit accountant", $_SESSION['successMessage'], $_SESSION['errorMessage']);
		
?>


   <form id="registerForm" action="" method="POST">

    <input type="hidden" name="id" value="<?php echo $id; ?>" />
    
 <div class="overview">
 
<table class='profileTable' style='text-align: left; margin: 0;'>
 <tr>
  <td><strong>Name</strong></td>
  <td><input type="text" name="name" value="<?php echo $name; ?>" /></td>
 </tr>
 <tr>
  <td><strong>Address</strong></td>
  <td>
   <input type="text" name="street" value="<?php echo $street; ?>" placeholder="Street" />
   <input type="text" name="streetnumber" class="twoDigit" value="<?php echo $streetnumber; ?>" placeholder="Number" />
   <input type="text" name="flat" class="twoDigit" value="<?php echo $flat; ?>" placeholder="Flat" />
  </td>
 </tr>
 <tr>
  <td><strong>Postcode</strong></td>
  <td><input type="text" name="postcode" class="fourDigit" value="<?php echo $postcode; ?>" placeholder="Post code" /></td>
 </tr>
 <tr>
  <td><strong>City</strong></td>
  <td><input type="text" name="city" value="<?php echo $city; ?>" /></td>
 </tr>
 <tr>
  <td><strong>State</strong></td>
  <td><input type="text" name="state" value="<?php echo $state; ?>" /></td>
 </tr>
 <tr>
  <td><strong>Country</strong></td>
  <td><input type="text" name="country" value="<?php echo $country; ?>" /></td>
 </tr>
 <tr>
  <td><strong>Telephone</strong></td>
  <td><input type="text" name="telephone" value="<?php echo $telephone; ?>" /></td>
 </tr>
 <tr>
  <td><strong>E-mail</strong></td>
  <td><input type="email" name="email" value="<?php echo $email; ?>" /></td>
 </tr>
</table>
 <br />
<button class='oneClick' name='oneClick' type="submit"><?php echo $lang['global-savechanges']; ?></button>


<?php displayFooter(); ?>
