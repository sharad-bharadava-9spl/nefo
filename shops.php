<?php
	
	require_once 'cOnfig/connection.php';
	require_once 'cOnfig/view.php';
	require_once 'cOnfig/authenticate.php';
	require_once 'cOnfig/languages/common.php';
	
	session_start();
	$accessLevel = '1';
	
	// Authenticate & authorize
	authorizeUser($accessLevel);
	
	$domain = $_SESSION['domain'];
	

	// Query to look up shops
	$query = "SELECT id, name, city, state, country, email, phone, logoExt, description FROM shops";
	try
	{
		$resultshop = $pdo3->prepare("$query");
		$resultshop->execute();
	}
	catch (PDOException $e)
	{
			$error = 'Error fetching user: ' . $e->getMessage();
			echo $error;
			exit();
	}
	
	pageStart('Shops', NULL, $deleteFlowerScript, "pproducts", "admin", 'SHOPS', $_SESSION['successMessage'], $_SESSION['errorMessage']);
	
	
?>

<center><a href='new-shop.php' class='cta1'>New shop</a></center>

	 <table class='default' id='mainTable'>
	  <thead>
	   <tr style='cursor: pointer;'>
	    <th></th>
	    <th>Name</th>
	    <th>Owner</th>
	    <th>City</th>
	    <th>State</th>
	    <th>Country</th>
	    <th>Email</th>
	    <th>Phone</th>
	   </tr>
	  </thead>
	  <tbody>

	  
<?php
	
	while ($shop = $resultshop->fetch()) {

		$id = $shop['id'];
		$shopid = $shop['id'];
		$name = $shop['name'];
		$street = $shop['street'];
		$streetnumber = $shop['streetnumber'];
		$postcode = $shop['postcode'];
		$city = $shop['city'];
		$state = $shop['state'];
		$country = $shop['country'];
		$email = $shop['email'];
		$phone = $shop['phone'];
		$logoExt = $shop['logoExt'];
		$description = $shop['description'];
		
		if ($logoExt != '') {
			$logo = "<img src='images/_dabulance/shops/$shopid.$logoExt' style='height: 40px;' />";
		} else {
			$logo = "<div style='height: 40px; display: inline-block;'>&nbsp;</span>";
		}
		
		$query = "SELECT user_id FROM shopaccess WHERE shopid = $shopid";
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
			$user_id = $row['user_id'];

		$query = "SELECT first_name, last_name FROM users WHERE user_id = '$user_id'";
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
			$first_name = $row['first_name'];
			$last_name = $row['last_name'];


		echo "
		<tr>
		<td class='clickableRow left' href='my-shop.php?shopid={$id}'>$logo</td>
		<td class='clickableRow left' href='my-shop.php?shopid={$id}'>$name</td>
		<td class='clickableRow left' href='my-shop.php?shopid={$id}'>$first_name $last_name</td>
		<td class='clickableRow left' href='my-shop.php?shopid={$id}'>$city</td>
		<td class='clickableRow left' href='my-shop.php?shopid={$id}'>$state</td>
		<td class='clickableRow left' href='my-shop.php?shopid={$id}'>$country</td>
		<td class='clickableRow left' href='my-shop.php?shopid={$id}'>$email</td>
		<td class='clickableRow left' href='my-shop.php?shopid={$id}'>$phone</td>
		</tr>";

	}
	
	echo "</table>";

 displayFooter();
