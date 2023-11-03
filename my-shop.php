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
		
	if ($data) {

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
			
		pageStart("Dabulance", NULL, $validationScript, "psales", "Sale", "My shop", $_SESSION['successMessage'], $_SESSION['errorMessage']);
		
		if ($_SESSION['userGroup'] == 1) {
			
			$back = "<a href='shops.php' class='cta1'>&laquo; Shops</a>";
			
		}
		
		if ($logoExt != '') {
			$logo = "<img src='images/_dabulance/shops/$shopid.$logoExt' style='max-width: 150px; max-height: 150px;' />";
			$upload = "<a href='shop-logo.php?shopid=$shopid' class='cta1'>Update logo</a>";
		} else {
			$upload = "<a href='shop-logo.php?shopid=$shopid' class='cta1'>Upload logo</a>";
		}
		
		if ($_SESSION['userGroup'] == 1) {
			
			$editLink = "<a href='edit-shop.php?shopid=$shopid' class='cta1'>Edit shop</a> $upload<br />$logo";
			
		} else {
			
			$editLink = "<a href='edit-shop.php' class='cta1'>Edit shop</a> $upload<br />$logo";
		
		}
		
		
		echo <<<EOD
<center>
$back $editLink
<br />
<div class="actionbox-np2">
 <div class='mainboxheader'>
 $name
 </div>
 <div class='boxcontent'>
  <em>$description</em><br /><br />
  $street $streetnumber<br />
  $postcode $city<br />
  $state $country<br /><br />
  $email<br />
  $phone<br />
    <br />
</div></div><br /><br />
<center><h2>ORDERS</h2></center>
EOD;

		exit();
	}
	



?>

   
