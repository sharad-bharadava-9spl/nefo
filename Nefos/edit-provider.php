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
		$comment = $_POST['comment'];
		$providerid = $_POST['providerid'];
		
		if ($memberno == '') {
			$memberno = $memberNumber;
		}
	
		// Query to update provider
		$updateCat = sprintf("UPDATE providers SET name = '%s', comment = '%s', providernumber = '%d' WHERE id = '%d';",
			mysql_real_escape_string($name),
			mysql_real_escape_string($comment),
			mysql_real_escape_string($memberno),
			mysql_real_escape_string($providerid)
		);
		
		  
		mysql_query($updateCat)
			or handleError($lang['error-savedata'],"Error inserting provider: " . mysql_error());
			
		// On success: redirect.
		$_SESSION['successMessage'] = $lang['provider-updated'];
		header("Location: providers.php");
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
	$categoryDetails = "SELECT name, comment, providernumber FROM providers WHERE id = $providerid";
	
			
	$result = mysql_query($categoryDetails)
		or handleError($lang['error-errorloadingflower'],"Error loading flower: " . mysql_error());
	
	$row = mysql_fetch_array($result);
		$name = $row['name'];
		$comment = $row['comment'];
		$providernumber = $row['providernumber'];

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
	$sql = "SELECT providernumber FROM providers";
	
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
   <textarea name="comment" placeholder="<?php echo $lang['global-comment']; ?>"><?php echo $description; ?></textarea><br />
<br />
 <button class='oneClick' name='oneClick' type="submit"><?php echo $lang['submit']; ?></button>
</form>

<?php displayFooter(); ?>

