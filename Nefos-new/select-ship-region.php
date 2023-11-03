<?php
	
	require_once 'cOnfig/connection.php';
	require_once 'cOnfig/view.php';
	require_once 'cOnfig/authenticate.php';
	require_once 'cOnfig/languages/common.php';
	
	session_start();
	$accessLevel = '3';
	
	// Authenticate & authorize
	authorizeUser($accessLevel);
	
	if ($_POST['submit'] == 'yes') {
		
		$club_id = $_POST['club_id'];
		$shipping = $_POST['shipping'];
		
		if ($shipping == 'Canarias') {
			$query = "UPDATE customers SET shipping = '$shipping', vat = 0 WHERE id = '$club_id'";
		} else {
			$query = "UPDATE customers SET shipping = '$shipping' WHERE id = '$club_id'";
		}
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
		
		$_SESSION['successMessage'] = "Shipping region added succesfully!";
		header("Location: club.php?club_id=$club_id");
		exit();
		
	}
	
	$club_id = $_GET['club_id'];
	
	// Query to look up customer
	$clubDetails = "SELECT longName, shortName, street, streetnumber, flat, postcode, city, state, country FROM customers WHERE  id = $club_id";
	try
	{
		$result = $pdo3->prepare("$clubDetails");
		$result->execute();
	}
	catch (PDOException $e)
	{
			$error = 'Error fetching user: ' . $e->getMessage();
			echo $error;
			exit();
	}

	$row = $result->fetch();
		$longName = $row['longName'];
		$shortName = $row['shortName'];
		$street = $row['street'];
		$streetnumber = $row['streetnumber'];
		$flat = $row['flat'];
		$postcode = $row['postcode'];
		$city = $row['city'];
		$state = $row['state'];
		$country = $row['country'];

	
	pageStart("Shipping region", NULL, $validationScript, "pprofile", NULL, "Select shipping region", $_SESSION['successMessage'], $_SESSION['errorMessage']);
		
?>


   <form id="registerForm" action="" method="POST">

    <input type="hidden" name="club_id" value="<?php echo $club_id; ?>" />
    <input type="hidden" name="submit" value="yes" />
    
 <div class="overview" style="width: 450px;">
 <center>
 <span style="font-size: 17px;">
<?php

	echo <<<EOD
	<strong>
		$longName<br />
		$shortName</strong><br /><br />
		$street $streetnumber $flat<br />
		$postcode $city<br />
		$state<br />
		$country<br /><br />
	
	
EOD;

?>
</span>
<table class='profileTable' style='text-align: left; margin: 0;'>
 <tr>
  <td><strong>Shipping region</strong></td>
  <td>
       <select name="shipping" id="shipping">
        <option value='<?php echo $shipping; ?>'><?php echo $shipping; ?></option>
        <option value='Peninsula'>Peninsula</option>
        <option value='Madrid'>Madrid</option>
        <option value='Baleares'>Baleares</option>
        <option value='Canarias'>Canarias</option>
	   </select>
  </td>
 </tr>

</table>
</center>
 <br /><button class='oneClick' name='oneClick' type="submit"><?php echo $lang['global-savechanges']; ?></button>
</form>
<?php displayFooter(); ?>
