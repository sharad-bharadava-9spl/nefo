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
		$role = str_replace("'","\'",str_replace('%', '&#37;', trim($_POST['role'])));
		$street = str_replace("'","\'",str_replace('%', '&#37;', trim($_POST['street'])));
		$streetnumber = str_replace("'","\'",str_replace('%', '&#37;', trim($_POST['streetnumber'])));
		$flat = str_replace("'","\'",str_replace('%', '&#37;', trim($_POST['flat'])));
		$postcode = str_replace("'","\'",str_replace('%', '&#37;', trim($_POST['postcode'])));
		$city = str_replace("'","\'",str_replace('%', '&#37;', trim($_POST['city'])));
		$state = str_replace("'","\'",str_replace('%', '&#37;', trim($_POST['state'])));
		$country = str_replace("'","\'",str_replace('%', '&#37;', trim($_POST['country'])));
		$phone = str_replace("'","\'",str_replace('%', '&#37;', trim($_POST['phone'])));
		$comment = str_replace("'","\'",str_replace('%', '&#37;', trim($_POST['comment'])));
		$email = str_replace("'","\'",str_replace('%', '&#37;', trim($_POST['email'])));
		$language = str_replace("'","\'",str_replace('%', '&#37;', trim($_POST['language'])));
		$customer = $_SESSION['customer'];
		$id = $_POST['id'];

 		
		// Query to add new contact - 11 arguments
		$query = sprintf("UPDATE contacts SET name = '%s', telephone = '%s', email = '%s', street = '%s', streetnumber = '%s', flat = '%s', postcode = '%s', city = '%s', state = '%s', country = '%s', role = '%s', comment = '%s', language = '%s' WHERE id = '%d';)",
		$name, $phone, $email, $street, $streetnumber, $flat, $postcode, $city, $state, $country, $role, $comment, $language, $id);
		echo $query;
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
		$_SESSION['successMessage'] = "Contact updated succesfully!";
		header("Location: contacts.php");
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
			  memberno: {
        		 require_from_group: [1, '.memberGroup'],
				  digits: true
        	  },
			  memberNumber: {
        		 require_from_group: [1, '.memberGroup']
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

	pageStart("New contact", NULL, $validationScript, "pnewcategory", "", "New contact", $_SESSION['successMessage'], $_SESSION['errorMessage']);
	
	$id = $_GET['contactid'];
	
	$selectCats = "SELECT name, telephone, email, street, streetnumber, flat, postcode, city, state, country, role, comment, language FROM contacts WHERE id = $id ORDER by name ASC";
	try
	{
		$result = $pdo3->prepare("$selectCats");
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
		$telephone = $row['telephone'];
		$email = $row['email'];
		$street = $row['street'];
		$streetnumber = $row['streetnumber'];
		$flat = $row['flat'];
		$postcode = $row['postcode'];
		$city = $row['city'];
		$state = $row['state'];
		$country = $row['country'];
		$role = $row['role'];
		$comment = $row['comment'];
		$language = $row['language'];


	
?>

<form id="registerForm" action="" method="POST">
<table class='profileTable' style='text-align: left; margin: 0;'>

 <tr>
  <td><strong>Name</strong></td>
  <td><input type="text" name="name" value="<?php echo $name; ?>" /></td>
 </tr>
 <tr>
  <td><strong>Role / position &nbsp;&nbsp;&nbsp;</strong></td>
  <td><input type="text" name="role" value="<?php echo $role; ?>" /></td>
 </tr>
 <tr>
  <td><strong>Language</strong></td>
  <td><input type="text" name="language" value="<?php echo $language; ?>" /></td>
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
  <td><input type="text" name="phone" value="<?php echo $telephone; ?>" /></td>
 </tr>
 <tr>
  <td><strong>E-mail</strong></td>
  <td><input type="email" name="email" value="<?php echo $email; ?>" /></td>
 </tr>
</table>

 <textarea name="comment"><?php echo $comment; ?></textarea><br /><br />
 <input type="hidden" name="id" value="<?php echo $id; ?>" />
 <button class='oneClick' name='oneClick' type="submit"><?php echo $lang['submit']; ?></button>
</form>

<?php displayFooter(); ?>

