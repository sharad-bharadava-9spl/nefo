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
		
		$memberno = $_POST['memberno'];
		$memberNumber = $_POST['memberNumber'];
		$name = trim($_POST['name']);
		$street = trim($_POST['street']);
		$streetnumber = trim($_POST['streetnumber']);
		$flat = trim($_POST['flat']);
		$postcode = trim($_POST['postcode']);
		$city = trim($_POST['city']);
		$country = trim($_POST['country']);
		$telephone = trim($_POST['telephone']);
		$email = trim($_POST['email']);
		$skype = trim($_POST['skype']);
		$comment = $_POST['comment'];
		$insertTime = date('Y-m-d H:i:s');
		
		if ($memberno == '') {
			$memberno = $memberNumber;
		}

	
		// Query to add new category - 11 arguments
		$query = sprintf("INSERT INTO b_providers (registered, name, comment, providernumber, street, streetnumber, flat, postcode, city, country, telephone, email, skype) VALUES ('%s', '%s', '%s', '%03d', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s');",
		$insertTime, $name, $comment, $memberno, $street, $streetnumber, $flat, $postcode, $city, $country, $telephone, $email, $skype);
		
		mysql_query($query)
			or handleError($lang['error-savedata'],"Error inserting flower: " . mysql_error());
			
		// On success: redirect.
		$_SESSION['successMessage'] = $lang['provider-added'];
		header("Location: bar-providers.php");
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

	pageStart($lang['new-provider'], NULL, $validationScript, "pnewcategory", "", $lang['new-provider'], $_SESSION['successMessage'], $_SESSION['errorMessage']);
	
	
	// Find highest available provider number
	$query = "select max(providernumber) from b_providers";

	$result = mysql_query($query)
		or handleError($lang['error-membershipnumberload'],"");
		
	$row = mysql_fetch_array($result);
		$nextMemberNo = $row['0'] + 1;

	
?>

<form id="registerForm" action="" method="POST">
 <h3><?php echo $lang['new-provider']; ?></h3>
 <input type="hidden" name="nextMemberNo" value="<?php echo $nextMemberNo; ?>" /> 
 <input type="text" lang="nb" id="memberno" class="twoDigit memberGroup" name="memberno" value="<?php echo sprintf('%03d', $nextMemberNo); ?>" /> <?php echo $lang['or']; ?> 
  <select name="memberNumber" id="memberNumber" class="memberGroup" style="width: 60px;">
   <option value=""></option>
<?php
	$sql = "SELECT providernumber FROM b_providers";
		$result = mysql_query($sql);
		
	while ($row = mysql_fetch_array($result)) {
   		$memberNumbers[] = $row['providernumber'];
	}
	
	for ($i = 0; $i < $nextMemberNo; ++$i) {
		
		if (!in_array($i, $memberNumbers)) {
			
			echo "<option value='$i'>" . sprintf('%03d', $i) . "</option>";
			
    	}
	}
?>
  </select>
  <br />
<script>

$('#memberNumber').change(function() {
  if($(this).val() != ''){
    $('#memberno').val('');
  }
});

$('#memberno').change(function() {
  if($(this).val() != ''){
    $('#memberNumber').val('');
  }
});


</script>
 <input type="text" name="name" placeholder="<?php echo $lang['global-name']; ?>" /><br />
 <input type="text" name="street" placeholder="<?php echo $lang['member-street']; ?>" value="<?php echo $street; ?>" />
 <input type="text" lang="nb" name="streetnumber" class="twoDigit" placeholder="No." value="<?php echo $streetnumber; ?>" />
 <input type="text" name="flat" class="twoDigit" placeholder="<?php echo $lang['member-flat']; ?>" value="<?php echo $flat; ?>" /><br />
 <input type="text" name="postcode" class="fourDigit" placeholder="<?php echo $lang['member-postcode']; ?>" value="<?php echo $postcode; ?>" />
 <input type="text" name="city" placeholder="<?php echo $lang['member-city']; ?>" value="<?php echo $city; ?>" /><br />
 <input type="text" name="country" placeholder="<?php echo $lang['member-country']; ?>" value="<?php echo $country; ?>" /><br /><br />
 <input type="text" name="telephone" placeholder="<?php echo $lang['member-telephone']; ?>" value="<?php echo $telephone; ?>" /><br />
 <input type="text" name="email" placeholder="E-mail" value="<?php echo $email; ?>" /><br />
 <input type="text" name="skype" placeholder="Skype" value="<?php echo $skype; ?>" /><br /><br />

 <textarea name="comment" placeholder="<?php echo $lang['global-comment']; ?>"></textarea><br /><br />
 <button class='oneClick' name='oneClick' type="submit"><?php echo $lang['submit']; ?></button>
</form>

<?php displayFooter(); ?>

