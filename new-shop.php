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
		$street = str_replace("'","\'",str_replace('%', '&#37;', trim($_POST['street'])));
		$streetnumber = str_replace("'","\'",str_replace('%', '&#37;', trim($_POST['streetnumber'])));
		$postcode = str_replace("'","\'",str_replace('%', '&#37;', trim($_POST['postcode'])));
		$city = str_replace("'","\'",str_replace('%', '&#37;', trim($_POST['city'])));
		$state = str_replace("'","\'",str_replace('%', '&#37;', trim($_POST['state'])));
		$country = str_replace("'","\'",str_replace('%', '&#37;', trim($_POST['country'])));
		$email = str_replace("'","\'",str_replace('%', '&#37;', trim($_POST['email'])));
		$phone = str_replace("'","\'",str_replace('%', '&#37;', trim($_POST['phone'])));
		$description = str_replace("'","\'",str_replace('%', '&#37;', trim($_POST['description'])));

		// Query to add new product - 11 arguments
		$query = sprintf("INSERT INTO shops (name, street, streetnumber, postcode, city, state, country, email, phone, description) VALUES ('%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s');",
		$name, $street, $streetnumber, $postcode, $city, $state, $country, $email, $phone, $description);
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

		$shopid = $pdo3->lastInsertId();

		$_SESSION['shopid'] = $shopid;
		
		// Query to add new product - 11 arguments
		$query = sprintf("INSERT INTO shopaccess (shopid, user_id) VALUES ('%d', '%d');",
		$shopid, $_SESSION['user_id']);
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
		$_SESSION['successMessage'] = 'Shop added successfully!';
		header("Location: new-shop-2.php");
		exit();
	}
	/***** FORM SUBMIT END *****/
	
	if ($_SESSION['userGroup'] > 1) {
	
		// Check if shop exists for this user, if so, throw error
		$query = "SELECT shopid FROM shopaccess WHERE user_id = '{$_SESSION['user_id']}'";
		try
		{
			$result = $pdo3->prepare("$query");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
			$shopid = $row['shopid'];
			
		if ($shopid != '') {
			
			$_SESSION['errorMessage'] = "You already have a registered shop! <a href='my-shop.php' class='yellow'>Click here</a> to see it.";
			pageStart("Dabulance", NULL, $validationScript, "psales", "Sale", "My shop", $_SESSION['successMessage'], $_SESSION['errorMessage']);
			exit();
			
		}
	
	}
	
				

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

	pageStart('Create new shop', NULL, $validationScript, "pnewcategory", "admin", 'Create new shop', $_SESSION['successMessage'], $_SESSION['errorMessage']);
	
	
?>
<center>
<div class="actionbox-np2">
 <div class='mainboxheader'>
 NEW SHOP
 </div>
 <div class='boxcontent'>

<form id="registerForm" action="" method="POST">
<h4>Name</h4>
   <input type="text" name="name" placeholder="Name of shop" class='defaultinput' /><br /><br />
<h4>Location</h4>
   <input type="text" name="street" placeholder="Street" class='defaultinput sixDigit' /> 
   <input type="text" name="streetnumber" placeholder="Number" class='defaultinput twoDigit' /><br />
   <input type="text" name="postcode" placeholder="Postcode" class='defaultinput twoDigit' /> 
   <input type="text" name="city" placeholder="City" class='defaultinput sixDigit' /><br />
   <input type="text" name="state" placeholder="State" style='width: 165px;' class='defaultinput' /><br />
   <input type="text" name="country" placeholder="Country" style='width: 165px;' class='defaultinput' /><br /><br />
<h4>Contact details</h4>
   <input type="text" name="email" placeholder="Email" class='defaultinput' /><br />
   <input type="text" name="phone" placeholder="Telephone" class='defaultinput' /><br />
   <textarea name="description" placeholder="Description (optional)"class='defaultinput' ></textarea><br />
 <br />
</div>
</div><br />
 <button class='cta1' name='oneClick' type="submit"><?php echo $lang['global-savechanges']; ?></button>
</form>

<?php displayFooter(); ?>

