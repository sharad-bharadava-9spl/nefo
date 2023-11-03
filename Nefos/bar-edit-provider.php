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
		$providerid = $_POST['providerid'];
		
		if ($memberno == '') {
			$memberno = $memberNumber;
		}
	
		// Query to update provider
		$updateCat = sprintf("UPDATE b_providers SET name = '%s', comment = '%s', providernumber = '%d', street = '%s', streetnumber = '%s', flat = '%s', postcode = '%s', city = '%s', country = '%s', telephone = '%s', email = '%s', skype = '%s' WHERE id = '%d';",
			mysql_real_escape_string($name),
			mysql_real_escape_string($comment),
			mysql_real_escape_string($memberno),
			mysql_real_escape_string($street),
			mysql_real_escape_string($streetnumber),
			mysql_real_escape_string($flat),
			mysql_real_escape_string($postcode),
			mysql_real_escape_string($city),
			mysql_real_escape_string($country),
			mysql_real_escape_string($telephone),
			mysql_real_escape_string($email),
			mysql_real_escape_string($skype),
			mysql_real_escape_string($providerid)
		);
		  
		mysql_query($updateCat)
			or handleError($lang['error-savedata'],"Error inserting provider: " . mysql_error());
			
		// On success: redirect.
		$_SESSION['successMessage'] = $lang['provider-updated'];
		header("Location: bar-providers.php");
		exit();
	}
	/***** FORM SUBMIT END *****/

	$validationScript = <<<EOD
    $(document).ready(function() {
	    
	    
$('#memberNumber').on('click keypress keyup blur', function() {
  if($(this).val() != ''){
    $('#memberno').val('');
  }
});

$('#memberno').on('click keypress keyup blur', function() {
  if($(this).val() != ''){
    $('#memberNumber').val('');
  }
});

	    	    
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

	$providerid = $_GET['providerid'];

	// Query to look for category
	$categoryDetails = "SELECT name, comment, providernumber, street, streetnumber, flat, postcode, city, country, telephone, email, skype FROM b_providers WHERE id = $providerid";
	
			
	$result = mysql_query($categoryDetails)
		or handleError($lang['error-errorloadingflower'],"Error loading flower: " . mysql_error());
	
	$row = mysql_fetch_array($result);
		$name = $row['name'];
		$comment = $row['comment'];
		$providernumber = $row['providernumber'];
		$street = $row['street'];
		$streetnumber = $row['streetnumber'];
		$flat = $row['flat'];
		$postcode = $row['postcode'];
		$city = $row['city'];
		$country = $row['country'];
		$telephone = $row['telephone'];
		$email = $row['email'];
		$skype = $row['skype'];

	$query = "select max(memberno) from users";

		$result = mysql_query($query)
		or handleError($lang['error-membershipnumberload'],"");
		$row = mysql_fetch_array($result);
		$nextMemberNo = $row['0'] + 1;

	pageStart($lang['edit-provider'], NULL, $validationScript, "pnewcategory", "", $lang['edit-provider'], $_SESSION['successMessage'], $_SESSION['errorMessage']);
	
	
?>
<form id="registerForm" action="" method="POST">
 <input type="hidden" name="providerid" value="<?php echo $providerid; ?>" />
 <input type="text" lang="nb" id="memberno" class="twoDigit memberGroup" name="memberno" value="<?php echo sprintf('%03d', $providernumber); ?>" /> <?php echo $lang['or']; ?> <select name="memberNumber" id="memberNumber" class="memberGroup" style="width: 60px;">
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
				echo "<option value='$nextMemberNo'>" . sprintf('%03d', $nextMemberNo) . "</option>";

?>
  </select>
   <h3><?php echo $lang['edit-provider']; ?></h3>
   <input type="text" name="name" value="<?php echo $name; ?>" /><br />
 <input type="text" name="street" placeholder="<?php echo $lang['member-street']; ?>" value="<?php echo $street; ?>" />
 <input type="text" lang="nb" name="streetnumber" class="twoDigit" placeholder="No." value="<?php echo $streetnumber; ?>" />
 <input type="text" name="flat" class="twoDigit" placeholder="<?php echo $lang['member-flat']; ?>" value="<?php echo $flat; ?>" /><br />
 <input type="text" name="postcode" class="fourDigit" placeholder="<?php echo $lang['member-postcode']; ?>" value="<?php echo $postcode; ?>" />
 <input type="text" name="city" placeholder="<?php echo $lang['member-city']; ?>" value="<?php echo $city; ?>" /><br />
 <input type="text" name="country" placeholder="<?php echo $lang['member-country']; ?>" value="<?php echo $country; ?>" /><br /><br />
 <input type="text" name="telephone" placeholder="<?php echo $lang['member-telephone']; ?>" value="<?php echo $telephone; ?>" /><br />
 <input type="text" name="email" placeholder="E-mail" value="<?php echo $email; ?>" /><br />
 <input type="text" name="skype" placeholder="Skype" value="<?php echo $skype; ?>" /><br /><br />
   <textarea name="comment" placeholder="<?php echo $lang['global-comment']; ?>"><?php echo $comment; ?></textarea><br />
<br />
 <button class='oneClick' name='oneClick' type="submit"><?php echo $lang['submit']; ?></button>
</form>

<?php displayFooter(); ?>

