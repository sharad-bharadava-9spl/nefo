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
		
		$registeredSince = $_POST['registeredSince'];
		$Brand = $_POST['Brand'];
		$number = $_POST['number'];
		$longName = str_replace("'","\'",str_replace('%', '&#37;', trim($_POST['longName'])));
		$shortName = str_replace("'","\'",str_replace('%', '&#37;', trim($_POST['shortName'])));
		$street = str_replace("'","\'",str_replace('%', '&#37;', trim($_POST['street'])));
		$streetnumber = $_POST['streetnumber'];
		$flat = $_POST['flat'];
		$postcode = $_POST['postcode'];
		$city = str_replace("'","\'",str_replace('%', '&#37;', trim($_POST['city'])));
		$state = str_replace("'","\'",str_replace('%', '&#37;', trim($_POST['state'])));
		$country = str_replace("'","\'",str_replace('%', '&#37;', trim($_POST['country'])));
		$website = $_POST['website'];
		$email = $_POST['email'];
		$facebook = $_POST['facebook'];
		$twitter = $_POST['twitter'];
		$instagram = $_POST['instagram'];
		$googleplus = $_POST['googleplus'];
		$status = $_POST['status'];
		$type = $_POST['type'];
		$lawyer = $_POST['lawyer'];
		$source = $_POST['source'];
		$billingType = $_POST['billingType'];
		$phone = $_POST['phone'];
		$private = $_POST['private'];
		$domain = $_POST['domain'];
		$memberonly = $_POST['memberonly'];
		$findus = $_POST['findus'];
		$findusother = str_replace("'","\'",str_replace('%', '&#37;', trim($_POST['findusother'])));
		$contact = $_POST['contact'];
		$contactother = str_replace("'","\'",str_replace('%', '&#37;', trim($_POST['contactother'])));
		$recommendation = $_POST['recommendation'];
		$accountantother = $_POST['accountantother'];
		$lawyerother = $_POST['lawyerother'];
		$language = $_POST['language'];
		$clubtype = $_POST['clubtype'];
		$size = $_POST['size'];
		$organic = $_POST['organic'];
		$directdebit = $_POST['directdebit'];
		$shipping = $_POST['shipping'];
		$vat = $_POST['vat'];
		$cif = $_POST['cif'];
		$directdebit_name = str_replace("'","\'",str_replace('%', '&#37;', trim($_POST['directdebit_name'])));
		$directdebit_iban = str_replace("'","\'",str_replace('%', '&#37;', trim($_POST['directdebit_iban'])));
		
		$opened = date("Y-m-d", strtotime($_POST['opened']));

		
		if ($recommendation != '') {
			$foundus = "$findus - $recommendation";
		} else if ($findusother != '') {
			$foundus = "$findus - $findusother";
		} else if ($accountantother != '') {
			$foundus = "$findus - $accountantother";
		} else if ($lawyerother != '') {
			$foundus = "$findus - $lawyerother";
		} else {
			$foundus = $findus;
		}
		
		if ($contact == 'Other') {
			$contact = "$contact - $contactother";
		}
		
		if ($memberonly != 1) {
			$memberonly = 0;
		}
		
				$insertTime = date("Y-m-d H:i:s");

		
	
		// Query to update user - 28 arguments
		$updateUser = sprintf("INSERT INTO customers (registeredSince, Brand, number, longName, shortName, cif, street, streetnumber, flat, postcode, city, state, country, website, email, facebook, twitter, instagram, googleplus, status, type, lawyer, billingType, phone, private, membermodule, source, contact, language, clubtype, size, opened, alias, organic, shipping, addedBy, vat, directdebit_name, directdebit_iban) VALUES ('%s', '%d', '%d', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%d', '%d', '%d', '%d', '%s', '%s', '%d', '%s', '%s', '%s', '%d', '%d', '%s', '%s', '%d', '%s', '%d', '%f', '%s', '%s')",
		
$insertTime,
$Brand,
$number,
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
$website,
$email,
$facebook,
$twitter,
$instagram,
$googleplus,
$status,
$type,
$lawyer,
$billingType,
$phone,
$private,
$memberonly,
$foundus,
$contact,
$language,
$clubtype,
$size,
$opened,
$alias,
$organic,
$shipping,
$_SESSION['user_id'],
$vat,
$directdebit_name,
$directdebit_iban
);

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
		
		$id = $pdo3->lastInsertId();
		
		$linkid = 'cl' . $id . substr($shortName, 0, 1);
		
		$query = "UPDATE customers SET linkid = '$linkid' WHERE id = '$id'";
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
		$_SESSION['successMessage'] = "Client added succesfully!";
		header("Location: customer.php?user_id={$id}");
		exit();
	}
	
	/***** FORM SUBMIT END *****/
	
	$validationScript = <<<EOD
	
	  $( function() {
	    $( "#datepicker" ).datepicker({
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

	    
$('#dd_signaturePadWrapper').click(function(e) {  
        $('#savesig').attr('checked', false)
    });
	    	    
	  $('#registerForm').validate({
		  rules: {
			  Brand: {
				  required: true
			  },
			  organic: {
				  required: true
			  },
			  longName: {
				  required: true
			  },
			  shortName: {
				  required: true
			  },
			  domain: {
				  required: true
			  },
			  status: {
				  required: true
			  },
			  type: {
				  required: true
			  },
			  findus: {
				  required: true
			  },
			  billingType: {
				  required: true
			  },
			  shipping: {
				  required: true
			  },
			  vat: {
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



	pageStart("New client", NULL, $validationScript, "pprofile", NULL, "New client", $_SESSION['successMessage'], $_SESSION['errorMessage']);
	
	$query = "select max(number) from customers";
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
		$nextMemberNo = $row['0'] + 1;

		
?>


   <form id="registerForm" action="" method="POST">

    <input type="hidden" name="id" value="<?php echo $user_id; ?>" />
    
 <div class="overview">
 
<span class="profilepicholder"><a href="new-picture.php?user_id=<?php echo $user_id; ?>" target="_blank"><img class="profilepic" src="images/customers/<?php

echo $user_id . "." . $photoExt;
?>" /></a></span>

<table class='profileTable' style='text-align: left; margin: 0;'>

 <tr>
  <td><strong>Brand</strong></td>
  <td>
  <span>
 <input type="radio" name="Brand" value="1" style="margin-left: 5px;"<?php if ($Brand == 1) { echo " checked"; }?>>CCS</input>
 <input type="radio" name="Brand" value="2"<?php if ($Brand == 2) { echo " checked"; }?>>Nefos</input>
 </span>
  </td>
 </tr>
 <tr>
  <td><strong>Type</strong></td>
  <td>
  <span>
 <input type="radio" name="private" value="1" style="margin-left: 5px;" checked>Private</input>
 <input type="radio" name="private" value="2"<?php if ($private == 2) { echo " checked"; }?>>Business</input>
 </span>
  </td>
 </tr>
 <tr>
  <td><strong>Organic?</strong></td>
  <td>
  <span>
 <input type="radio" name="organic" value="1" style="margin-left: 5px;" onClick='organicYes()'>Yes</input>
 <input type="radio" name="organic" value="0" onClick='organicNo()'>No</input>
 </span>
  </td>
 </tr>
 <tr>
  <td><strong>Customer number</strong></td>
  <td>
   <input type="number" lang="nb" id="number" class="twoDigit memberGroup" name="number" value="<?php echo $nextMemberNo; ?>" readonly />
  </td>
 </tr>
 <tr>
  <td><strong>VAT %</strong></td>
  <td>
   <input type="number" lang="nb" id="vat" class="twoDigit memberGroup" name="vat"  />
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
  <td><strong>Alias</strong></td>
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
  <td><input type="email" name="email" value="<?php echo $email; ?>" /></td>
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
		$selectGroups = "SELECT id, statusName FROM customerstatus WHERE id = 1 || id = 2 || id = 3 || id = 4 ||  id = 9 || id = 10 || id > 11 ORDER by id ASC";
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
	   </select><input type="checkbox" name="memberonly" style="margin-left: 5px; width: 15px;" value="1">Member module only?</input>
  </td>
 </tr>
 <tr>
  <td><strong>Type</strong></td>
  <td>
 <input type="radio" name="type" value="1" style="margin-left: 5px;" checked>Normal</input>
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
  <td><strong><span id='organicText'>How did they find out about us?</span></strong></td>
  <td>
   <select name="findus" id="findus">
    <option value="">Please select</option>
    <option value="Google">Google</option>
    <option value="Recommendation">Recommendation</option>
    <option value="Lawyer">Lawyer</option>
    <option value="Accountant">Accountant</option>
    <option value="Instagram">Instagram</option>
    <option value="Facebook">Facebook</option>
    <option value="On-site visit">On-site visit</option>
    <option value="Marketing">Marketing</option>
    <option value="Weedmaps/MMJ Menu">Weedmaps/MMJ Menu</option>
    <option value="MJ Freeway">MJ Freeway</option>
    <option value="Gestion Verde">Gestion Verde</option>
    <option value="Weedgest">Weedgest</option>
    <option value="Easy CSC">Easy CSC</option>
    <option value="Other">Other</option>
   </select>
   <select name="recommendation" id="recommendation" style='display: none;'>
    <option value="">Please select</option>
<?php
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
?>
   </select>
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
  </td>
 </tr>
 <tr>
  <td><strong>How did they contact us?</strong></td>
  <td>
   <select name="contact" id="contact">
    <option value="">Please select</option>
    <option value="Website">Website</option>
    <option value="Telephone">Telephone</option>
    <option value="E-mail">E-mail</option>
    <option value="Instagram">Instagram</option>
    <option value="Facebook">Facebook</option>
    <option value="Other">Other</option>
   </select>
   <input type="text" name="contactother" id="contactother" placeholder='Please specify' style='display: none;' />
  </td>
 </tr>
 <tr>
  <td><strong>Billing</strong></td>
  <td>
 <input type="radio" name="billingType" value="1" style="margin-left: 5px;" checked>Monthly</input>
 <input type="radio" name="billingType" value="2"<?php if ($billingType == 2) { echo " checked"; }?>>Yearly</input>
  </td>
 </tr>
 <tr>
  <td><strong>Club size</strong></td>
  <td>
   <input type="radio" name="size" value="1">Small (<100)</input><br />
   <input type="radio" name="size" value="2">Medium (100-250)</input><br />
   <input type="radio" name="size" value="3">Large (250-500)</input><br />
   <input type="radio" name="size" value="4">Full size (>500)</input>
  </td>
 </tr>
 <tr>
  <td><strong>Type</strong></td>
  <td>
   <input type="radio" name="clubtype" value="1">Only medicinal</input><br />
   <input type="radio" name="clubtype" value="2">Mainly medicinal</input><br />
   <input type="radio" name="clubtype" value="3">Mixed</input><br />
   <input type="radio" name="clubtype" value="4">Mainly recreational</input><br />
   <input type="radio" name="clubtype" value="5">Only recreational</input>
  </td>
 </tr>
 <tr>
  <td><strong>Open since</strong></td>
  <td><input type="text" id="datepicker" name="opened" class="sixDigit" /></td>
 </tr>
 <tr>
  <td><strong>Direct Debit</strong></td>
  <td>
   <input type="text" name="directdebit_name" placeholder="Name of holder" value="<?php echo $directdebit_name; ?>" /><br />
   <input type="text" name="directdebit_iban" placeholder="IBAN" value="<?php echo $directdebit_iban; ?>" />
  </td>
 </tr>
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
 <br />
<button class='oneClick' name='oneClick' type="submit"><?php echo $lang['global-savechanges']; ?></button>

</form>

<script>

function organicYes() {
	$('#organicText').html('How did they find out about us?');
}
function organicNo() {
	$('#organicText').html('Where did you find them?');
}

</script>

<?php displayFooter(); ?>
