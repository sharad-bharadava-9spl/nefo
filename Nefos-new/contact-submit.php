<?php

	require_once 'cOnfig/connection.php';
	require_once 'cOnfig/viewv6.php';
	require_once 'cOnfig/authenticate.php';
	require_once 'cOnfig/languages/common.php';
	

	session_start();
	$accessLevel = '3';
	
	// Authenticate & authorize
	authorizeUser($accessLevel);
	
	if (isset($_POST['number'])) {
		
		$number = $_POST['number'];
		$notid = $_POST['notid'];
		$hash = $_POST['hash'];
		
		$longName = str_replace("'","\'",str_replace('%', '&#37;', trim($_POST['longName'])));
		$shortName = str_replace("'","\'",str_replace('%', '&#37;', trim($_POST['shortName'])));
		$cif = str_replace("'","\'",str_replace('%', '&#37;', trim($_POST['cif'])));
		$street = str_replace("'","\'",str_replace('%', '&#37;', trim($_POST['street'])));
		$streetnumber = $_POST['streetnumber'];
		$flat = str_replace("'","\'",str_replace('%', '&#37;', trim($_POST['flat'])));
		$postcode = str_replace("'","\'",str_replace('%', '&#37;', trim($_POST['postcode'])));
		$city = str_replace("'","\'",str_replace('%', '&#37;', trim($_POST['city'])));
		$state = str_replace("'","\'",str_replace('%', '&#37;', trim($_POST['state'])));
		$country = str_replace("'","\'",str_replace('%', '&#37;', trim($_POST['country'])));
		$phone = $_POST['phone'];
		$website = $_POST['website'];
		$email = $_POST['email'];
		$facebook = $_POST['facebook'];
		$instagram = $_POST['instagram'];
		$location_street_name = str_replace("'","\'",str_replace('%', '&#37;', trim($_POST['location_street_name'])));
		$location_street_number = $_POST['location_street_number'];
		$location_local = str_replace("'","\'",str_replace('%', '&#37;', trim($_POST['location_local'])));
		$location_postcode = str_replace("'","\'",str_replace('%', '&#37;', trim($_POST['location_postcode'])));
		$location_city = str_replace("'","\'",str_replace('%', '&#37;', trim($_POST['location_city'])));
		$location_province = str_replace("'","\'",str_replace('%', '&#37;', trim($_POST['location_province'])));
		$location_country = str_replace("'","\'",str_replace('%', '&#37;', trim($_POST['location_country'])));
		
		// Copy old information and save to customers_old
		$query = "INSERT INTO customers_old
SELECT d.*
FROM customers d
WHERE number = '$number';";
echo $query;

		try
		{
			$result = $pdo3->prepare("$query")->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user6: ' . $e->getMessage();
				echo $error;
				exit();
		}
		
		// Update information in customers
		$query = "UPDATE customers SET longName = '$longName', shortName = '$shortName', cif = '$cif', street = '$street', streetnumber = '$streetnumber', flat = '$flat', postcode = '$postcode', city = '$city', state = '$state', country = '$country', phone = '$phone', website = '$website', email = '$email', facebook = '$facebook', instagram = '$instagram', location_street_name = '$location_street_name', location_street_number = '$location_street_number', location_local = '$location_local', location_postcode = '$location_postcode', location_city = '$location_city', location_province = '$location_province', location_country = '$location_country' WHERE number = '$number'";
		try
		{
			$result = $pdo3->prepare("$query")->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user5: ' . $e->getMessage();
				echo $error;
				exit();
		}
		
		// Set customers_tmp to approved = 1. NO, because they might have submitted several!
		$query = "UPDATE customers_tmp SET approved = 1, approvedby = '{$_SESSION['user_id']}' WHERE hash = '$hash'";
		try
		{
			$result = $pdo3->prepare("$query")->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user4: ' . $e->getMessage();
				echo $error;
				exit();
		}

		
		// Update notification flag, incl. operator who approved
		$query = "UPDATE notifications SET approved = 1, approvedby = '{$_SESSION['user_id']}' WHERE id = '$notid'";
		try
		{
			$result = $pdo3->prepare("$query")->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user3: ' . $e->getMessage();
				echo $error;
				exit();
		}
		
		// Set existing contacts to inactive
		$query = "UPDATE contacts SET active = 0 WHERE customer = '$number'";
		try
		{
			$result = $pdo2->prepare("$query")->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user2: ' . $e->getMessage();
				echo $error;
				exit();
		}
		
		// Add submitted contacts - and set as active!
		foreach($_POST['contact'] as $contact) {
			
			$name = str_replace("'","\'",str_replace('%', '&#37;', trim($contact['name'])));
			$role = str_replace("'","\'",str_replace('%', '&#37;', trim($contact['role'])));
			$telephone = str_replace("'","\'",str_replace('%', '&#37;', trim($contact['telephone'])));
			$email = str_replace("'","\'",str_replace('%', '&#37;', trim($contact['email'])));
			
			if ($name != '') {
				$query = "INSERT INTO contacts (name, telephone, email, role, customer, active) VALUES ('$name', '$telephone', '$email', '$role', '$number', 2)";
				try
				{
					$result = $pdo2->prepare("$query")->execute();
				}
				catch (PDOException $e)
				{
						$error = 'Error fetching user1: ' . $e->getMessage();
						echo $error;
						exit();
				}
			}
			
			$i++;
			
		}

		
		// Delete contacts from contacts_tmp. Why?		
		
		
		$_SESSION['successMessage'] = "Contact details approved succesfully!";
		header("Location: contact-updates.php");
		exit();
			
	}
	
	// Get notification ID
	$notid = $_GET['id'];
	$number = $_GET['number'];
	
	$query = "SELECT userid, hash FROM customers_tmp WHERE id = '$notid'";
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
		$userid = $row['userid'];
		$hash = $row['hash'];
		
	// Look up domain via number
	$query = "SELECT db_pwd, customer, domain FROM db_access WHERE customer = '$number'";
	try
	{
		$result = $pdo->prepare("$query");
		$result->execute();
	}
	catch (PDOException $e)
	{
			$error = 'Error fetching user2: ' . $e->getMessage();
			echo $error;
			exit();
	}

	$row = $result->fetch();
		$db_pwd = $row['db_pwd'];
		$customer = $row['customer'];
		$domain = $row['domain'];

	$db_name = "ccs_" . $domain;
	$db_user = $db_name . "u";

	// Create pdo9
	try	{
 		$pdo9 = new PDO('mysql:host='.DATABASE_HOST.';dbname='.$db_name, $db_user, $db_pwd);
 		$pdo9->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
 		$pdo9->exec('SET NAMES "utf8"');
	}
	catch (PDOException $e)	{
  		$output = 'Unable to connect to the database server: ' . $e->getMessage();
 		echo $output . "<br />";
	}

	// Look up user data
	$query = "SELECT memberno, first_name, last_name, photoext, userGroup FROM users WHERE user_id = '$userid'";
	try
	{
		$result = $pdo9->prepare("$query");
		$result->execute();
	}
	catch (PDOException $e)
	{
			$error = 'Error fetching user2: ' . $e->getMessage();
			echo $error;
			exit();
	}

	$row = $result->fetch();
		$memberno = $row['memberno'];
		$first_name = $row['first_name'];
		$last_name = $row['last_name'];
		$photoext = $row['photoext'];
		$userGroup = $row['userGroup'];
		
	if ($userGroup == 1) {
		$userGroup = 'Administrator';
	} else if ($userGroup == 2) {
		$userGroup = 'Staff';
	} else if ($userGroup == 3) {
		$userGroup = 'Volunteer';
	} else {
		$userGroup = '';
	}

	// Look up club data
	$query = "SELECT * FROM customers WHERE number = '$number'";
	try
	{
		$result2 = $pdo3->prepare("$query");
		$result2->execute();
	}
	catch (PDOException $e)
	{
			$error = 'Error fetching user: ' . $e->getMessage();
			echo $error;
			exit();
	}
	$row2 = $result2->fetch();
	
	// Look up submitted data
	$query = "SELECT * FROM customers_tmp WHERE hash = '$hash'";
	try
	{
		$result3 = $pdo3->prepare("$query");
		$result3->execute();
	}
	catch (PDOException $e)
	{
			$error = 'Error fetching user: ' . $e->getMessage();
			echo $error;
			exit();
	}
	$row3 = $result3->fetch();
	
	$query = "SELECT * FROM contacts_tmp WHERE hash = '$hash'";
	try
	{
		$result4 = $pdo3->prepare("$query");
		$result4->execute();
	}
	catch (PDOException $e)
	{
			$error = 'Error fetching user: ' . $e->getMessage();
			echo $error;
			exit();
	}
	
	// Look up club contacts
	$query = "SELECT * FROM contacts WHERE customer = '$number' AND active = 2";
	try
	{
		$result5 = $pdo3->prepare("$query");
		$result5->execute();
	}
	catch (PDOException $e)
	{
			$error = 'Error fetching user: ' . $e->getMessage();
			echo $error;
			exit();
	}
	
		
	pageStart("Contact updates", NULL, $timePicker, "pprofile", NULL, "Contact updates", $_SESSION['successMessage'], $_SESSION['errorMessage']);
	

?>
<form id="step3" action="" method="POST">
<input type="hidden" name="number" value="<?php echo $number; ?>" />
<input type="hidden" name="notid" value="<?php echo $notid; ?>" />
<input type="hidden" name="hash" value="<?php echo $hash; ?>" />
 <div id='mainbox-new-club' style='width: 1600px;'>
  <div id='mainboxheader'>
   <center>
    New contact submission
   </center>
  </div>
  <div class='boxcontent'>
  <center>
<?php

	echo <<<EOD
  <img src="https://ccsnubev2.com/v6/images/_$domain/members/$userid.$photoext" style='height: 120px;' /><br />
  <strong>#$memberno $first_name $last_name</strong><br />
  $userGroup
EOD;

?>
  <br />  <br />
  <table class='padded2'>
   <tr>
    <td colspan="2" style='border-bottom: 2px solid #4c8038;'>
     <center><strong style='font-size: 20px;'>OLD DATA</strong></center>
    </td>
    <td style='border-bottom: 2px solid #4c8038;'>
     <center><strong style='font-size: 20px;'>SUBMITTED DATA</strong></center>
    </td>
   </tr>
<?php

	echo <<<EOD
   <tr>
    <td><strong>Official licensed club name</strong></td>
    <td style='border-right: 2px solid #4c8038;'>{$row2['longName']}</td>
    <td><input type='text' name='longName' class='defaultinput twelveDigit' value="{$row3['longName']}" /></td>
   </tr>
   <tr>
    <td><strong>Commonly used club name</strong></td>
    <td style='border-right: 2px solid #4c8038;'>{$row2['shortName']}</td>
    <td><input type='text' name='shortName' class='defaultinput twelveDigit' value="{$row3['shortName']}" /></td>
   </tr>
   <tr>
    <td><strong>CIF</strong></td>
    <td style='border-right: 2px solid #4c8038;'>{$row2['cif']}</td>
    <td><input type='text' name='cif' class='defaultinput twelveDigit' value="{$row3['cif']}" /></td>
   </tr>
   <tr>
    <td style='border-bottom: 2px solid #4c8038;'><strong>Invoicing address</strong></td>
    <td style='border-bottom: 2px solid #4c8038; border-right: 2px solid #4c8038;'>
     {$row2['street']} {$row2['streetnumber']} {$row2['flat']}<br />
     {$row2['postcode']} {$row2['city']}<br />
     {$row2['state']}<br />
     {$row2['country']}
    </td>
    <td style='border-bottom: 2px solid #4c8038;'>
       <input type="text" name="street" class='defaultinput eightDigit' placeholder="{$lang['member-street']}" value="{$row3['street']}" />
       <input type="text" name="streetnumber" class='defaultinput fiveDigit' placeholder="No." value="{$row3['streetnumber']}" />
       <input type="text" name="flat" class='defaultinput fiveDigit' placeholder="{$lang['local']}" value="{$row3['flat']}" /><br />
       <input type="text" name="postcode" class='defaultinput fiveDigit' placeholder="{$lang['postcode-number']}" value="{$row3['postcode']}" />
       <input type="text" name="city" class='defaultinput elevenDigit' placeholder="{$lang['club-city']}" value="{$row3['city']}" /><br />
       <input type="text" name="state" class='defaultinput twelveDigit' placeholder="{$lang['club-province']}" value="{$row3['state']}" /><br />
       <input type="text" name="country" class='defaultinput twelveDigit' placeholder="{$lang['club-country']}" value="{$row3['country']}" /><br />
    </td>
   </tr>
   
   <tr>
    <td><strong>Telephone number</strong></td>
    <td style='border-right: 2px solid #4c8038;'>{$row2['phone']}</td>
    <td><input type='text' name='phone' class='defaultinput twelveDigit' value="{$row3['phone']}" /></td>
   </tr>
   <tr>
    <td><strong>Club email</strong></td>
    <td style='border-right: 2px solid #4c8038;'>{$row2['email']}</td>
    <td><input type='text' name='email' class='defaultinput twelveDigit' value="{$row3['email']}" /></td>
   </tr>
   <tr>
    <td><strong>Website</strong></td>
    <td style='border-right: 2px solid #4c8038;'>{$row2['website']}</td>
    <td><input type='text' name='website' class='defaultinput twelveDigit' value="{$row3['website']}" /></td>
   </tr>
   <tr>
    <td><strong>Facebook</strong></td>
    <td style='border-right: 2px solid #4c8038;'>{$row2['facebook']}</td>
    <td><input type='text' name='facebook' class='defaultinput twelveDigit' value="{$row3['facebook']}" /></td>
   </tr>
   <tr>
    <td><strong>Instagram</strong></td>
    <td style='border-right: 2px solid #4c8038;'>{$row2['instagram']}</td>
    <td><input type='text' name='instagram' class='defaultinput twelveDigit' value="{$row3['instagram']}" /></td>
   </tr>
   <tr>
    <td style='border-bottom: 2px solid #4c8038;'><strong>Club location</strong></td>
    <td style='border-bottom: 2px solid #4c8038; border-right: 2px solid #4c8038;'>
     {$row2['location_street_name']} {$row2['location_street_number']} {$row2['location_local']}<br />
     {$row2['location_postcode']} {$row2['location_city']}<br />
     {$row2['location_province']}<br />
     {$row2['location_country']}
    </td>
 	 	 	 	 	 	
    <td style='border-bottom: 2px solid #4c8038;'>
       <input type="text" name="location_street_name" class='defaultinput eightDigit' placeholder="{$lang['member-street']}" value="{$row3['location_street_name']}" />
       <input type="text" name="location_street_number" class='defaultinput fiveDigit' placeholder="No." value="{$row3['location_street_number']}" />
       <input type="text" name="location_local" class='defaultinput fiveDigit' placeholder="{$lang['local']}" value="{$row3['location_local']}" /><br />
       <input type="text" name="location_postcode" class='defaultinput fiveDigit' placeholder="{$lang['postcode-number']}" value="{$row3['location_postcode']}" />
       <input type="text" name="location_city" class='defaultinput elevenDigit' placeholder="{$lang['club-city']}" value="{$row3['location_city']}" /><br />
       <input type="text" name="location_province" class='defaultinput twelveDigit' placeholder="{$lang['club-province']}" value="{$row3['location_province']}" /><br />
       <input type="text" name="location_country" class='defaultinput twelveDigit' placeholder="{$lang['club-country']}" value="{$row3['location_country']}" /><br />
    </td>
   </tr>
   <tr>
    <td style='vertical-align: top;'><br /><strong>Contacts</strong></td>
    <td style='vertical-align: top; border-right: 2px solid #4c8038;'><br />
EOD;

   	while ($row5 = $result5->fetch()) {
		
		$name = $row5['name'];
		$role = $row5['role'];
		$telephone = $row5['telephone'];
		$email = $row5['email'];
		
		echo <<<EOD
   	<div style='border: 2px solid #00a48c; display: inline-block; padding: 5px; margin: 5px; border-radius: 5px; vertical-align: top;' id='contact$i'>
    <table>
     <tr>
      <td>{$row5['name']}&nbsp;</td>
     </tr>
     <tr>
      <td>{$row5['role']}&nbsp;</td>
     </tr>
     <tr>
      <td>{$row5['telephone']}&nbsp;</td>
     </tr>
     <tr>
      <td>{$row5['email']}&nbsp;</td>
     </tr>
    </table>
   </div>
     
EOD;

	}
	


echo <<<EOD
    </td>
    <td style='vertical-align: top;'><br />
EOD;
    
	$i = 1;
   	while ($row4 = $result4->fetch()) {
	   	
		$name = $row4['name'];
		$role = $row4['role'];
		$telephone = $row4['telephone'];
		$email = $row4['email'];
		
		echo <<<EOD
   	<div style='border: 2px solid #00a48c; display: inline-block; padding: 5px; margin: 5px; border-radius: 5px; vertical-align: top;' id='contact$i'>
    <table>
     <tr>
      <td><input type='text' name='contact[$i][name]' class='defaultinput-no-margin eightDigit' value='$name' placeholder='Name' /></td>
     </tr>
     <tr>
      <td><input type='text' name='contact[$i][role]' class='defaultinput-no-margin eightDigit' value='$role' placeholder='Role' /></td>
     </tr>
     <tr>
      <td><input type='text' name='contact[$i][telephone]' class='defaultinput-no-margin eightDigit' value='$telephone' placeholder='Telephone' /></td>
     </tr>
     <tr>
      <td><input type='text' name='contact[$i][email]' class='defaultinput-no-margin eightDigit' value='$email' placeholder='E-mail' /></td>
     </tr>
    </table>
   </div>
     
EOD;

	$i++;

	}
	
echo <<<EOD
    </td>
   </tr>
  </table>
    </center>
    </div></div>
    <br />
<center>
 <button type="submit" name='step3_sub' class='cta1'>APPROVE</button>
 <!--<a href="?reject&id=$notid" type="submit" name='step3_sub' class='cta3'>REJECT</a>-->
</center>
</form>

EOD;

displayFooter();