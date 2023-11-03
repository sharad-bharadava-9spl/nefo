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
	if (isset($_POST['shortName'])) {
		
		$id = $_POST['id'];
		$registeredSince = $_POST['registeredSince'];
		$Brand = $_POST['Brand'];
		$number = $_POST['number'];
		$oldnumber = $_POST['oldnumber'];
		$status = $_POST['status'];
		$type = $_POST['type'];
		$lawyer = $_POST['lawyer'];
		$source = $_POST['source'];
		$billingType = $_POST['billingType'];
		$facebook = $_POST['facebook'];
		$twitter = $_POST['twitter'];
		$instagram = $_POST['instagram'];
		$googleplus = $_POST['googleplus'];
		$private = $_POST['private'];
		$membermodule = $_POST['membermodule'];
		$number = $_POST['number'];
		$findus = $_POST['findus'];
		$findusother = $_POST['findusother'];
		$contact = $_POST['contact'];
		$contactother = $_POST['contactother'];
		$recommendation = $_POST['recommendation'];
		$accountantother = $_POST['accountantother'];
		$lawyerother = $_POST['lawyerother'];
		$language = $_POST['language'];
		$clubtype = $_POST['clubtype'];
		$size = $_POST['size'];
		$language = $_POST['language'];
		$alias = $_POST['alias'];
		$organic = $_POST['organic'];
		$opened = date("Y-m-d", strtotime($_POST['opened']));
		
		


		
		if ($number != $oldnumber) {
			
			// Update contacts
			$query = "UPDATE contacts SET customer = '$number' WHERE customer = '$oldnumber'";
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
			
			// Update master db
			$query = "UPDATE db_access SET customer = '$number' WHERE customer = '$oldnumber'";
			try
			{
				$result = $pdo->prepare("$query")->execute();
			}
			catch (PDOException $e)
			{
					$error = 'Error fetching user3: ' . $e->getMessage();
					echo $error;
					exit();
			}
			
		}
		
		if ($recommendation != '') {
			$foundus = "1 $findus - $recommendation";
		} else if ($findusother != '') {
			$foundus = "2 $findus - $findusother";
		} else if ($accountantother != '') {
			$foundus = "3 $findus - $accountantother";
		} else if ($lawyerother != '') {
			$foundus = "4 $findus - $lawyerother";
		} else {
			$foundus = $findus;
		}
		
		if ($contact == 'Other') {
			$contact = "$contact - $contactother";
		}
		
		
		if ($membermodule != 1) {
			$membermodule = 0;
		}

		
		$longName = str_replace("'","\'",str_replace('%', '&#37;', trim($_POST['longName'])));
		$shortName = str_replace("'","\'",str_replace('%', '&#37;', trim($_POST['shortName'])));
		$cif = str_replace("'","\'",str_replace('%', '&#37;', trim($_POST['cif'])));
		$street = str_replace("'","\'",str_replace('%', '&#37;', trim($_POST['street'])));
		$streetnumber = str_replace("'","\'",str_replace('%', '&#37;', trim($_POST['streetnumber'])));
		$flat = str_replace("'","\'",str_replace('%', '&#37;', trim($_POST['flat'])));
		$postcode = str_replace("'","\'",str_replace('%', '&#37;', trim($_POST['postcode'])));
		$city = str_replace("'","\'",str_replace('%', '&#37;', trim($_POST['city'])));
		$state = str_replace("'","\'",str_replace('%', '&#37;', trim($_POST['state'])));
		$country = str_replace("'","\'",str_replace('%', '&#37;', trim($_POST['country'])));
		$website = str_replace("'","\'",str_replace('%', '&#37;', trim($_POST['website'])));
		$email = str_replace("'","\'",str_replace('%', '&#37;', trim($_POST['email'])));
		$statusName = str_replace("'","\'",str_replace('%', '&#37;', trim($_POST['statusName'])));
		$phone = str_replace("'","\'",str_replace('%', '&#37;', trim($_POST['phone'])));

		$cutoff = date("Y-m-d", strtotime($_POST['cutoff']));
	
		// Query to update client - 28 arguments
		$updateUser = sprintf("UPDATE customers SET Brand = '%d', longName = '%s', shortName = '%s', cif = '%s', street = '%s', streetnumber = '%d', flat = '%s', postcode = '%s', city = '%s', state = '%s', country = '%s', email = '%s', website = '%s', facebook = '%s', twitter = '%s', instagram = '%s', googleplus = '%s', status = '%d', type = '%d', lawyer = '%d', URL = '%s', source = '%s', billingType = '%d', dbname = '%s', dbuser = '%s', dbpwd = '%s', phone = '%s', private = '%d', contactPerson = '%s', contactPersonDNI = '%s', contract = '%d', membermodule = '%d', number = '%d', contact = '%s', language = '%s', clubtype = '%d', size = '%d', opened = '%s', alias = '%s', organic = '%d' WHERE id = '%d';",
		
$Brand,
$longName,
$shortName,
$cif,
$street,
$streetnumber,
$flat,
$postcode,
$city,
$state,
$country,
$email,
$website,
$facebook,
$twitter,
$instagram,
$googleplus,
$status,
$type,
$lawyer,
$URL,
$foundus,
$billingType,
$dbname,
$dbuser,
$dbpwd,
$phone,
$private,
$contactPerson,
$contactPersonDNI,
$contract,
$membermodule,
$number,
$contact,
$language,
$clubtype,
$size,
$opened,
$alias,
$organic,
$id
);

		try
		{
			$result = $pdo2->prepare("$updateUser")->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user1: ' . $e->getMessage();
				echo $error;
				exit();
		}
		
		// Update warning status and cutoff date
		$domain = $_POST['domain'];
		$cutoff = date("Y-m-d", strtotime($_POST['cutoff']));
		$warning = $_POST['warningstatus'];
		
		if ($domain != '' && $domain != 'NONE') {
		// Look up domain
		$findDomain = "SELECT db_pwd FROM db_access WHERE domain = '$domain'";
		try
		{
			$result = $pdo->prepare("$findDomain");
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
			$db_name = "ccs_" . $domain;
			$db_user = $db_name . "u";
			
		echo "db_pwd: $db_pwd<br />";
		echo "db_name: $db_name<br />";
		echo "db_user: $db_user<br />";

		try	{
	 		$pdo2 = new PDO('mysql:host='.DATABASE_HOST.';dbname='.$db_name, $db_user, $db_pwd);
	 		$pdo2->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	 		$pdo2->exec('SET NAMES "utf8"');
		}
		catch (PDOException $e)	{
	  		$output = 'Unable to connect to the database server 22: ' . $e->getMessage();
	
	 		echo $output;
	 		exit();
		}

		
		$query = "UPDATE db_access SET warning = $warning, cutoff = '$cutoff' WHERE domain = '$domain'";
		try
		{
			$result = $pdo->prepare("$query")->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user3: ' . $e->getMessage();
				echo $error;
				exit();
		}		
		
	}
						
		// On success: redirect.
		$_SESSION['successMessage'] = "Client updated succesfully!";
		header("Location: customer.php?user_id={$id}");
		exit();
	}
	
	/***** FORM SUBMIT END *****/

	$warning = $_GET['warning'];
	$domain = urldecode($_GET['domain']);
	$cutoff = $_GET['cutoff'];
	
	if (isset($_GET['user_id'])) {
		
			$user_id = $_GET['user_id'];
			
	} else {
		
		handleError($lang['error-nouserid'],"");
		
	}
	
	// Query to look for user
	$userDetails = "SELECT c.id, c.registeredSince, c.Brand, c.number, c.longName, c.shortName, c.cif, c.street, c.streetnumber, c.flat, c.postcode, c.city, c.state, c.country, c.website, c.email, c.facebook, c.twitter, c.instagram, c.googleplus, c.status, c.type, c.lawyer, c.URL, c.source, c.billingType, c.dbname, c.dbuser, c.dbpwd, c.phone, c.private, c.contactPerson, c.contactPersonDNI, s.statusName, c.contract, c.membermodule, c.contact, c.language, c.clubtype, c.size, c.opened, c.alias, c.organic FROM customers c, customerstatus s WHERE c.status = s.id AND c.id = $user_id";
		try
		{
			$result = $pdo3->prepare("$userDetails");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
		$id = $row['id'];
		$registeredSince = $row['registeredSince'];
		$Brand = $row['Brand'];
		$number = $row['number'];
		$longName = $row['longName'];
		$shortName = $row['shortName'];
		$cif = $row['cif'];
		$street = $row['street'];
		$streetnumber = $row['streetnumber'];
		$flat = $row['flat'];
		$postcode = $row['postcode'];
		$city = $row['city'];
		$state = $row['state'];
		$country = $row['country'];
		$website = $row['website'];
		$email = $row['email'];
		$facebook = $row['facebook'];
		$twitter = $row['twitter'];
		$instagram = $row['instagram'];
		$googleplus = $row['googleplus'];
		$status = $row['status'];
		$type = $row['type'];
		$lawyer = $row['lawyer'];
		$URL = $row['URL'];
		$source = $row['source'];
		$billingType = $row['billingType'];
		$dbname = $row['dbname'];
		$dbuser = $row['dbuser'];
		$dbpwd = $row['dbpwd'];
		$statusName = $row['statusName'];
		$phone = $row['phone'];
		$private = $row['private'];
		$contactPerson = $row['contactPerson'];
		$contactPersonDNI = $row['contactPersonDNI'];
		$contract = $row['contract'];
		$membermodule = $row['membermodule'];
		$contact = $row['contact'];
		$language = $row['language'];
		$clubtype = $row['clubtype'];
		$size = $row['size'];
		$language = $row['language'];
		$alias = $row['alias'];
		$organic = $row['organic'];
		$opened = date("d-m-Y", strtotime($row['opened']));
				


	$validationScript = <<<EOD
	
	  $( function() {
	    $( "#datepicker" ).datepicker({
			dateFormat: "dd-mm-yy"
	    });
	  });
	  $( function() {
	    $( "#datepicker2" ).datepicker({
			dateFormat: "dd-mm-yy"
	    });
	  });
	  
	    $(document).ready(function() {
		    
	    $('#contact').change(function(){
			var val = $(this).val();
		    if(val == 'Other') {
		        $("#contactother").fadeIn('slow');
	    	} else {
		        $("#contactother").fadeOut('slow');
	    	}
	    });
	    
	    $('#findus').change(function(){
			var val = $(this).val();
		    if(val == 'Other') {
		        $("#findusother").fadeIn('slow');
	    	} else {
		        $("#findusother").fadeOut('slow');
	    	}
	    });

	    $('#findus').change(function(){
			var val = $(this).val();
		    if(val == 'Recommendation') {
		        $("#recommendation").fadeIn('slow');
	    	} else {
		        $("#recommendation").fadeOut('slow');
	    	}
	    });

	    $('#findus').change(function(){
			var val = $(this).val();
		    if(val == 'Lawyer') {
		        $("#lawyerother").fadeIn('slow');
	    	} else {
		        $("#lawyerother").fadeOut('slow');
	    	}
	    });

	    $('#findus').change(function(){
			var val = $(this).val();
		    if(val == 'Accountant') {
		        $("#accountantother").fadeIn('slow');
	    	} else {
		        $("#accountantother").fadeOut('slow');
	    	}
	    });


		  });
EOD;
		
	pageStart($lang['title-profile'], NULL, $validationScript, "pprofile", NULL, $lang['member-editprofile'] . ": " . $first_name . " " . $last_name, $_SESSION['successMessage'], $_SESSION['errorMessage']);
		
?>


   <form id="registerForm" action="" method="POST">

    <input type="hidden" name="id" value="<?php echo $user_id; ?>" />
    <input type="hidden" name="domain" value="<?php echo $domain; ?>" />
    
 <div class="overview">


<table class='profileTable' style='text-align: left; margin: 0;'>

 <tr>
  <td><strong>Brand</strong></td>
  <td>
 <input type="radio" name="Brand" value="1" style="margin-left: 5px;"<?php if ($Brand == 1) { echo " checked"; }?>>CCS</input>
 <input type="radio" name="Brand" value="2"<?php if ($Brand == 2) { echo " checked"; }?>>Nefos</input>
  </td>
 </tr>
 <tr>
  <td><strong>Type</strong></td>
  <td>
 <input type="radio" name="private" value="1" style="margin-left: 5px;"<?php if ($private == 1) { echo " checked"; }?>>Private</input>
 <input type="radio" name="private" value="2"<?php if ($private == 2) { echo " checked"; }?>>Business</input>
  </td>
 </tr>
 <tr>
  <td><strong>Organic</strong></td>
  <td>
 <input type="radio" name="organic" value="1" style="margin-left: 5px;" <?php if ($organic == 1) { echo " checked"; }?>>Yes</input>
 <input type="radio" name="organic" value="0" <?php if ($organic == 0) { echo " checked"; }?>>No</input>
  </td>
 </tr>
 <tr>
  <td><strong>Customer number</strong></td>
  <td>
   <input type="hidden" name="oldnumber" value="<?php echo $number; ?>" />
   <input type="number" lang="nb" id="memberno" class="twoDigit memberGroup" name="number" value="<?php echo $number; ?>" readonly /><a href='#' onClick='load1()' class='yellow'>Assign permanent customer number</a> / <a href='#' onClick='load2()' class='yellow'>Assign temporary customer number</a>
<script>
function load1(){
	

    $.ajax({
      type:"post",
      url:"getnumber.php",
      datatype:"text",
      success:function(data)
      {
	       	$('#memberno').val(data);
      }
    });
    
}
function load2(){
	

    $.ajax({
      type:"post",
      url:"getnumber2.php",
      datatype:"text",
      success:function(data)
      {
	       	$('#memberno').val(data);
      }
    });
    
}
</script>
  </td>
 </tr>
 <tr>
  <td><strong>Long name</strong></td>
  <td><input type="text" name="longName" value="<?php echo $longName; ?>" /></td>
 </tr>
 <tr>
  <td><strong>Short name</strong></td>
  <td><input type="text" name="shortName" value="<?php echo $shortName; ?>" /></td>
 </tr>
 <tr>
  <td><strong>Aliases</strong></td>
  <td><input type="text" name="alias" value="<?php echo $alias; ?>" /></td>
 </tr>
 <tr>
  <td><strong>CIF</strong></td>
  <td><input type="text" name="cif" value="<?php echo $cif; ?>" /></td>
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
  <td><input type="text" name="phone" value="<?php echo $phone; ?>" /></td>
 </tr>
 <tr>
  <td><strong>Website</strong></td>
  <td><input type="text" name="website" value="<?php echo $website; ?>" /></td>
 </tr>
 <tr>
  <td><strong>E-mail</strong></td>
  <td><input type="text" name="email" value="<?php echo $email; ?>" /></td>
 </tr>
 <tr>
  <td><strong>Facebook</strong></td>
  <td><input type="text" name="facebook" value="<?php echo $facebook; ?>" /></td>
 </tr>
 <tr>
  <td><strong>Twitter</strong></td>
  <td><input type="text" name="twitter" value="<?php echo $twitter; ?>" /></td>
 </tr>
 <tr>
  <td><strong>Google+</strong></td>
  <td><input type="text" name="googleplus" value="<?php echo $googleplus; ?>" /></td>
 </tr>
 <tr>
  <td><strong>Instagram</strong></td>
  <td><input type="text" name="instagram" value="<?php echo $instagram; ?>" /></td>
 </tr>
 <tr>
  <td><strong>Status</strong></td>
  <td>
          <select name="status" id="status">
        <option value='<?php echo $status; ?>'><?php echo $statusName; ?></option>
<?php
      
      	// Query to look up customergroups      	
		$selectGroups = "SELECT id, statusName FROM customerstatus WHERE id = 1 || id = 2 || id = 3 || id = 4 || id = 9 || id = 10 || id > 11 ORDER by id ASC";
		try
		{
			$results = $pdo3->prepare("$selectGroups");
			$results->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		while ($group = $results->fetch()) {
						if ($group['id'] != $status) {
				$group_row = sprintf("<option value='%d'>%s</option>",
	  								 $group['id'], $group['statusName']);
	  			echo $group_row;
  			}
  		}
?>
	   </select><input type="checkbox" name="membermodule" style="margin-left: 5px; width: 15px;" value="1"<?php if ($membermodule == 1) { echo " checked"; }?>>Member module only?</input>
  </td>
 </tr>
 <tr>
  <td><strong>Warning & Cutoff date</strong></td>
  <td>
   <select name="warningstatus" id="warningstatus">
<?php

	if ($warning == 3) {
        echo "<option value='3'>Cut off</option>";		
        echo "<option value='0'>No warning</option>";		
        echo "<option value='2'>Last warning</option>";		
        echo "<option value='1'>Soft warning</option>";		
	} else if ($warning == 2) {
        echo "<option value='2'>Last warning</option>";		
        echo "<option value='0'>No warning</option>";		
        echo "<option value='1'>Soft warning</option>";		
        echo "<option value='3'>Cut off</option>";		
	} else if ($warning == 1) {
        echo "<option value='1'>Soft warning</option>";		
        echo "<option value='0'>No warning</option>";		
        echo "<option value='2'>Last warning</option>";		
        echo "<option value='3'>Cut off</option>";		
	} else if ($warning == 0) {
        echo "<option value='0'>No warning</option>";		
        echo "<option value='1'>Soft warning</option>";		
        echo "<option value='2'>Last warning</option>";		
        echo "<option value='3'>Cut off</option>";		
	}
      
    echo "<input type='text' name='cutoff' id='datepicker' value='$cutoff' />";
        
?>
	   </select>
  </td>
 </tr>
 <tr>
  <td><strong>Type</strong></td>
  <td>
 <input type="radio" name="type" value="1" style="margin-left: 5px;"<?php if ($type == 1) { echo " checked"; }?>>Normal</input>
 <input type="radio" name="type" value="2"<?php if ($type == 2) { echo " checked"; }?>>VIP</input>
  </td>
 </tr>
 <tr>
  <td><strong>Lawyer</strong></td>
  <td>
       <select name="lawyer" id="lawyer">
       
<?php 	

      	// Query to look up lawyers      	
		$selectGroups = "SELECT id, name FROM lawyers WHERE id = '$lawyer'";
		try
		{
			$result = $pdo3->prepare("$selectGroups");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
			$lawyerName = $row['name'];
		
		if ($lawyer > 0) {
			echo "<option value='$lawyer'>$lawyer - $lawyerName</option>";
		} else {
			echo "<option value='0'></option>";
		}
			
      	// Query to look up lawyers      	
		$selectGroups = "SELECT id, name FROM lawyers ORDER by id ASC";
		try
		{
			$results = $pdo3->prepare("$selectGroups");
			$results->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		while ($group = $results->fetch()) {
						if ($group['id'] != $lawyer) {
				$group_row = sprintf("<option value='%d'>%d - %s</option>",
	  								 $group['id'], $group['id'], $group['name']);
	  			echo $group_row;
  			}
  		}

 
?>

		
	   </select><br />
  </td>
 </tr>
 <tr>
  <td><strong>How did they find out about us?</strong></td>
  <td>
   <select name="findus" id="findus">
<?php
	if ($source == '0' || $source == '') {
		
		echo "<option value=''>Please select</option>";
		
	} else {
		
		// Check if 'source' contains a hyphen, and if so, explode the string and show text input depending on whether it's a club, lawyer or accountant recommendation. What about 'OTHER'?
		
		if (strpos($source, 'Recommendation') !== false) {
			
			$recommended = 'true'; // To use further down to show text input
			
			$array = explode(" - ",$source);
			
			$recommendedID = $array[1];
			
			// Look up club name
			$query = "SELECT number, shortName FROM customers WHERE id = $recommendedID";
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
				$number = $row['number'];
				$shortName = $row['shortName'];
			
			echo "<option value='Recommendation'>Recommendation</option>";
			
		} else if (strpos($source, 'Accountant') !== false) {
			
			$accountant = 'true'; // To use further down to show text input
			$array = explode(" - ",$source);
			
			$accountantNo = $array[1];
			
	      	// Query to look up accountant
			$selectGroups = "SELECT id, name FROM accountants WHERE id = '$accountantNo'";
			try
			{
				$result = $pdo3->prepare("$selectGroups");
				$result->execute();
			}
			catch (PDOException $e)
			{
					$error = 'Error fetching user: ' . $e->getMessage();
					echo $error;
					exit();
			}
		
			$row = $result->fetch();
				$accountantName = $row['name'];
				
			echo "<option value='Accountant'>Accountant</option>";
			
		} else if (strpos($source, 'Lawyer') !== false) {
	
			$lawyer = 'true'; // To use further down to show text input
			$array = explode(" - ",$source);
			
			$lawyerNo = $array[1];
			
	      	// Query to look up lawyer
			$selectGroups = "SELECT id, name FROM lawyers WHERE id = '$lawyerNo'";
			try
			{
				$result = $pdo3->prepare("$selectGroups");
				$result->execute();
			}
			catch (PDOException $e)
			{
					$error = 'Error fetching user: ' . $e->getMessage();
					echo $error;
					exit();
			}
		
			$row = $result->fetch();
				$lawyerName = $row['name'];

			echo "<option value='Lawyer'>Lawyer</option>";
			echo "<option>$query</option>";

		} else if (strpos($source, 'Other') !== false) {
			
			
			$other = 'true';
			
			$array = explode(" - ",$source);
			
			$otherValue = $array[1];
			
			echo "<option value='Other'>Other</option>";
			
		} else {
			
			echo "<option value='$source'>$source</option>";
		
		}
		
	}
?>
    <option value="Google">Google</option>
    
<?php
	if ($recommended != 'true') {
		
		echo "<option value='Recommendation'>Recommendation</option>";
		
	}
    
	if ($lawyer != 'true') {
		
		echo "<option value='Lawyer'>Lawyer</option>";
		
	}
	
	if ($accountant != 'true') {
		
		echo "<option value='Accountant'>Accountant</option>";
		
	}
?>
    <option value="Instagram">Instagram</option>
    <option value="Facebook">Facebook</option>
    <option value="On-site visit">On-site visit</option>
    <option value="Marketing">Marketing</option>
    <option value="Weedmaps/MMJ Menu">Weedmaps/MMJ Menu</option>
    <option value="MJ Freeway">MJ Freeway</option>
    <option value="Gestion Verde">Gestion Verde</option>
    <option value="Weedgest">Weedgest</option>
    <option value="Easy CSC">Easy CSC</option>
<?php
	if ($other != 'true') {
		
		echo "<option value='Other'>Other</option>";
		
	}
?>
   </select>
<?php
	
	if ($recommended == 'true') {

		echo "<select name='recommendation' id='recommendation'><option value='$recommendedID'>$number - $shortName</option>";
		
      	// Query to look up clubs      	
		$selectGroups = "SELECT id, number, shortName FROM customers WHERE id <> $recommendedID ORDER by shortName ASC";
		try
		{
			$results = $pdo3->prepare("$selectGroups");
			$results->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		while ($group = $results->fetch()) {
			
			$id = $group['id'];
			$number = $group['number'];
			$shortName = $group['shortName'];
			
	  		echo "<option value='$id'>$number - $shortName</option>";
  		}
  		
	} else if ($accountant == 'true') {

		echo "<select name='accountantother' id='accountantother'><option value='$accountantNo'>$accountantName</option>";
		
      	// Query to look up accountants      	
		$selectGroups = "SELECT id, name FROM accountants WHERE id <> '$accountantNo' ORDER by id ASC";
		try
		{
			$results = $pdo3->prepare("$selectGroups");
			$results->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		while ($group = $results->fetch()) {
			
			$id = $group['id'];
			$name = $group['name'];
			
	  		echo "<option value='$id'>$name</option>";
  		}
  		
	} else if ($lawyer == 'true') {

		echo "<select name='lawyerother' id='lawyerother'><option value='$lawyerNo'>$lawyerName</option>";
		
      	// Query to look up lawyers      	
		$selectGroups = "SELECT id, name FROM lawyers WHERE id <> '$lawyerNo' ORDER by id ASC";
		try
		{
			$results = $pdo3->prepare("$selectGroups");
			$results->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		while ($group = $results->fetch()) {
			
			$id = $group['id'];
			$name = $group['name'];
			
	  		echo "<option value='$id'>$name</option>";
  		}
  		
	} else if ($other == 'true') {
		
   		echo "<input type='text' name='findusother' id='findusother' value='$otherValue' />";
		
	} else {
	
		echo "<select name='recommendation' id='recommendation' style='display: none;'><option value=''>Please select</option>";
      	// Query to look up clubs      	
		$selectGroups = "SELECT id, number, shortName FROM customers ORDER by shortName ASC";
		try
		{
			$results = $pdo3->prepare("$selectGroups");
			$results->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		while ($group = $results->fetch()) {
			
			$id = $group['id'];
			$number = $group['number'];
			$shortName = $group['shortName'];
			
	  		echo "<option value='$id'>$number - $shortName</option>";
  		}
	
	}
	
?>
   </select>
   
<?php 
	if ($recommended != 'true' && $accountant != 'true' && $lawyer != 'true' && $other != 'true') {

?>
   <input type="text" name="findusother" id="findusother" placeholder='Please specify' style='display: none;' />
   <select name="lawyerother" id="lawyerother" style='display: none;'>
    <option value=''>Please select</option>
<?php
      	// Query to look up lawyers      	
		$selectGroups = "SELECT id, name FROM lawyers ORDER by id ASC";
		try
		{
			$results = $pdo3->prepare("$selectGroups");
			$results->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		while ($group = $results->fetch()) {
				echo sprintf("<option value='%d'>%s</option>",
	  								 $group['id'], $group['name']);
  		}

 
?>
 </select>

   <select name="accountantother" id="accountantother" style='display: none;'>
    <option value=''>Please select</option>
<?php
      	// Query to look up lawyers      	
		$selectGroups = "SELECT id, name FROM accountants ORDER by id ASC";
		try
		{
			$results = $pdo3->prepare("$selectGroups");
			$results->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		while ($group = $results->fetch()) {
				echo sprintf("<option value='%d'>%s</option>",
	  								 $group['id'], $group['name']);
  		}

 
?>
   </select>
   
<?php } ?>

  </td>
 </tr>
 <tr>
  <td><strong>How did they contact us?</strong></td>
  <td>
   <select name="contact" id="contact">
<?php

	if ($contact == '0' || $contact == '') {
		
		echo "<option value=''>Please select</option>";
		
	} else {
		
		if (strpos($contact, 'Other') !== false) {
			
			$othercontact = 'true';
			
			$array = explode(" - ",$contact);
			
			$other = $array[1];
			
			echo "<option value='Other'>Other</option>";
			
		} else {
			
			echo "<option value='$contact'>$contact</option>";
			
		}

		
	}

?>

    <option value="Website">Website</option>
    <option value="Telephone">Telephone</option>
    <option value="E-mail">E-mail</option>
    <option value="Instagram">Instagram</option>
    <option value="Facebook">Facebook</option>
    <option value="Other">Other</option>
   </select>
<?php

	if ($othercontact == 'true') {
		
		echo "<input type='text' name='contactother' id='contactother' value='$other' />";
		
	} else {
		
   		echo "<input type='text' name='contactother' id='contactother' placeholder='Please specify' style='display: none;' />";
	
	}
	
?>

  </td>
 </tr>
 <tr>
  <td><strong>Billing</strong></td>
  <td>
 <input type="radio" name="billingType" value="1" style="margin-left: 5px;"<?php if ($billingType == 1) { echo " checked"; }?>>Monthly</input>
 <input type="radio" name="billingType" value="2"<?php if ($billingType == 2) { echo " checked"; }?>>Yearly</input>
  </td>
 </tr>
 <tr>
  <td><strong>Club size</strong></td>
  <td>
   <input type="radio" name="size" value="1" <?php if ($size == 1) { echo " checked"; }?>>Small (<100)</input><br />
   <input type="radio" name="size" value="2" <?php if ($size == 2) { echo " checked"; }?>>Medium (100-250)</input><br />
   <input type="radio" name="size" value="3" <?php if ($size == 3) { echo " checked"; }?>>Large (250-500)</input><br />
   <input type="radio" name="size" value="4" <?php if ($size == 4) { echo " checked"; }?>>Full size (>500)</input>
  </td>
 </tr>
 <tr>
  <td><strong>Type</strong></td>
  <td>
   <input type="radio" name="clubtype" value="1" <?php if ($clubtype == 1) { echo " checked"; }?>>Only medicinal</input><br />
   <input type="radio" name="clubtype" value="2" <?php if ($clubtype == 2) { echo " checked"; }?>>Mainly medicinal</input><br />
   <input type="radio" name="clubtype" value="3" <?php if ($clubtype == 3) { echo " checked"; }?>>Mixed</input><br />
   <input type="radio" name="clubtype" value="4" <?php if ($clubtype == 4) { echo " checked"; }?>>Mainly recreational</input><br />
   <input type="radio" name="clubtype" value="5" <?php if ($clubtype == 5) { echo " checked"; }?>>Only recreational</input>
  </td>
 </tr>
 <tr>
  <td><strong>Open since</strong></td>
  <td><input type="text" id="datepicker2" name="opened" class="sixDigit" value="<?php echo $opened; ?>" /></td>
 </tr>

</table>
 <br /><button class='oneClick' name='oneClick' type="submit"><?php echo $lang['global-savechanges']; ?></button>

<?php displayFooter(); ?>
