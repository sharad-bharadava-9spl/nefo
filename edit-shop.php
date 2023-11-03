<?php
	
	require_once 'cOnfig/connection.php';
	require_once 'cOnfig/view.php';
	require_once 'cOnfig/authenticate.php';
	require_once 'cOnfig/languages/common.php';
	
	session_start();
	$accessLevel = '3';
	
	// Retrieve System settings
	getSettings();

	
	if (isset($_GET['shopid']) && $_SESSION['userGroup'] == 1) {
		
		$shopid = $_GET['shopid'];
		
	} else {
		
		// Find shop ID
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
		
	}
		
	if ($shopid == '') {
		
		$_SESSION['errorMessage'] = "You have no shop registered. <a href='new-shop.php' class='yellow'>Click here</a> to setup your shop!";
		pageStart("Dabulance", NULL, $validationScript, "psales", "Sale", "My shop", $_SESSION['successMessage'], $_SESSION['errorMessage']);
		exit();
	}
	
	if ($_POST['resubmit'] == 'yes') {
		
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
		
		$query = "UPDATE shops SET name = '$name', street = '$street', streetnumber = '$streetnumber', postcode = '$postcode', city = '$city', state = '$state', country = '$country', email = '$email', phone = '$phone', description = '$description' WHERE id = '$shopid'";
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
		
		$_SESSION['successMessage'] = "Shop updated succesfully!";
		header("Location: my-shop.php");
		exit();
	
	}

	// Lookup club info
	$query = "SELECT name, street, streetnumber, postcode, city, state, country, email, phone, logoExt, description FROM shops WHERE id = '$shopid'";
	try
	{
		$result = $pdo3->prepare("$query");
		$result->execute();
		$data = $result->fetchAll();
	}
	catch (PDOException $e)
	{
			$error = 'Error fetching user: ' . $e->getMessage();
			echo $error;
			exit();
	}

		$row = $data[0];
			$name = $row['name'];
			$street = $row['street'];
			$streetnumber = $row['streetnumber'];
			$postcode = $row['postcode'];
			$city = $row['city'];
			$state = $row['state'];
			$country = $row['country'];
			$email = $row['email'];
			$phone = $row['phone'];
			$logoExt = $row['logoExt'];
			$description = $row['description'];
			
		pageStart('Edit shop', NULL, $validationScript, "pnewcategory", "admin", 'Edit shop', $_SESSION['successMessage'], $_SESSION['errorMessage']);
		
		if ($logoExt != '') {
			$logo = "<img src='images/_dabulance/shops/$shopid.$logoExt' style='max-width: 150px; max-height: 150px;' />";
		} else {
			$upload = "<a href='shop-logo.php' class='cta'>Upload logo</a>";
		}
		
?>
<center>
<div class="actionbox-np2">
 <div class='mainboxheader'>
 EDIT SHOP
 </div>
 <div class='boxcontent'>
		
<form id="registerForm" action="?shopid=<?php echo $shopid; ?>" method="POST">
<input type="hidden" name="resubmit" value="yes" />
<h4>Name</h4>
   <input type="text" name="name" placeholder="Name of shop" class='defaultinput' value="<?php echo $name; ?>" /><br /><br />
<h4>Location</h4>
   <input type="text" name="street" placeholder="Street" class='defaultinput sixDigit' value="<?php echo $street; ?>" /> 
   <input type="text" name="streetnumber" placeholder="Number" class='defaultinput twoDigit' value="<?php echo $streetnumber; ?>" /><br />
   <input type="text" name="postcode" placeholder="Postcode" class='defaultinput twoDigit' value="<?php echo $postcode; ?>" /> 
   <input type="text" name="city" placeholder="City" class='defaultinput sixDigit' value="<?php echo $city; ?>" /><br />
   <input type="text" name="state" placeholder="State" style='width: 165px;' class='defaultinput' value="<?php echo $state; ?>" /><br />
   <input type="text" name="country" placeholder="Country" class='defaultinput' style='width: 165px;' value="<?php echo $country; ?>" /><br /><br />
<h4>Contact details</h4>
   <input type="text" name="email" placeholder="Email" class='defaultinput' value="<?php echo $email; ?>" /><br />
   <input type="text" name="phone" placeholder="Telephone" class='defaultinput' value="<?php echo $phone; ?>" /><br />
   <textarea name="description" placeholder="Description (optional)"class='defaultinput' ><?php echo $description; ?></textarea><br />
 <br />
 </div>
 </div>
 <br />
 <button name='oneClick' class='cta1' type="submit"><?php echo $lang['global-savechanges']; ?></button>
</form>
</center>

<?php displayFooter(); ?>

