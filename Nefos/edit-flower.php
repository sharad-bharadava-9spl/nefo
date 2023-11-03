<?php
	
	require_once 'cOnfig/connection.php';
	require_once 'cOnfig/view.php';
	require_once 'cOnfig/authenticate.php';
	require_once 'cOnfig/languages/common.php';
	
	session_start();
	$accessLevel = '3';
	
	// Authenticate & authorize
	authorizeUser($accessLevel);
	
	$flowerid = $_GET['flowerid'];
	// Did this page re-submit with a form? If so, check & store details
	if (isset($_POST['name'])) {

	$registeredSince = $_POST['registeredSince'];
	$name = $_POST['name'];
	$flowertype = $_POST['flowertype'];
	$description = $_POST['description'];
	$medicaldescription = $_POST['medicaldescription'];
	$breed2 = $_POST['breed2'];
	$sativaPercentage = $_POST['sativaPercentage'];
	$flowernumber = $_POST['flowernumber'];
	$THC = $_POST['THC'];
	$CBD = $_POST['CBD'];
	$CBN = $_POST['CBN'];
	
		// Query to update flower - 11(10) arguments
		$updateFlower = sprintf("UPDATE flower SET name = '%s', flowertype = '%s', description = '%s', medicaldescription = '%s', breed2 = '%s', sativaPercentage = '%s', THC = '%s', CBD = '%s', CBN = '%s', flowernumber = '%d' WHERE flowerid = '%d';",
			mysql_real_escape_string($name),
			mysql_real_escape_string($flowertype),
			mysql_real_escape_string($description),
			mysql_real_escape_string($medicaldescription),
			mysql_real_escape_string($breed2),
			mysql_real_escape_string($sativaPercentage),
			mysql_real_escape_string($THC),
			mysql_real_escape_string($CBD),
			mysql_real_escape_string($CBN),
			mysql_real_escape_string($flowernumber),
			mysql_real_escape_string($flowerid)
);
			
		mysql_query($updateFlower)
			or handleError($lang['error-savedata'],"Error updating flower: " . mysql_error());
			
		// On success: redirect.
		$_SESSION['successMessage'] = $lang['flowers-updatesuccess'];
		header("Location: products.php");
		exit();
	}
	/***** FORM SUBMIT END *****/

	$validationScript = <<<EOD
    $(document).ready(function() {
	    	    
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

	// Query to look for flower
	$flowerDetails = "SELECT flowerid, flowertype, registeredSince, flowertype, name, description, medicaldescription, breed2, sativaPercentage, THC, CBD, CBN, flowernumber FROM flower WHERE flowerid = '{$flowerid}'";
	
	// Does flower ID exist?
	$flowerCheck = mysql_query($flowerDetails);
	if(mysql_num_rows($flowerCheck) == 0) {
   		handleError($lang['error-floweridnotexist'],"");
	}
			
	$result = mysql_query($flowerDetails)
		or handleError($lang['error-errorloadingflower'],"Error loading flower: " . mysql_error());
	
	if ($result) {
	$row = mysql_fetch_array($result);
	$flowerid = $row['flowerid'];
	$registeredSince = $row['registeredSince'];
	$name = $row['name'];
	$flowertype = $row['flowertype'];
	$description = $row['description'];
	$medicaldescription = $row['medicaldescription'];
	$breed2 = $row['breed2'];
	$sativaPercentage = $row['sativaPercentage'];
	$flowernumber = $row['flowernumber'];
	$THC = $row['THC'];
	$CBD = $row['CBD'];
	$CBN = $row['CBN'];

} else {
		handle_error($lang['error-findinginfo'],"Error locating flower with ID {$flowerid}");
}

	pageStart($lang['title-editflower'], NULL, $validationScript, "pnewstrain", "admin", $lang['extracts-editflower'], $_SESSION['successMessage'], $_SESSION['errorMessage']);

	
?>


<form id="registerForm" action="" method="POST" style='text-align: left;'>
<label class='fakelabel' for='productnumber'><?php echo $lang['product-number']; ?></label><input type="text" name="flowernumber" value="<?php echo sprintf('%03d', $flowernumber); ?>" maxlength="4" /><br />
   <span class="fakelabel"><?php echo $lang['global-name']; ?>:</span><input type="text" name="name" placeholder="<?php echo $lang['global-name']; ?>" value="<?php echo $name; ?>" /> x <input type="text" name="breed2" placeholder="<?php echo $lang['extracts-secondbreed']; ?>" value="<?php echo $breed2; ?>" /><br />
   <span class="fakelabel"><?php echo $lang['global-type']; ?>:</span><select name="flowertype">
<?php if ($flowertype == NULL) { ?><option value=""><?php echo $lang['global-type']; ?>:</option> <?php } ?>
   <option value="Indica" <?php if ($flowertype == "Indica") {echo "selected";} ?>>Indica</option>
   <option value="Sativa" <?php if ($flowertype == "Sativa") {echo "selected";} ?>>Sativa</option>
   <option value="Hybrid" <?php if ($flowertype == "Hybrid") {echo "selected";} ?>><?php echo $lang['global-hybrid']; ?></option>
  </select><br />
<span class="fakelabel">Sativa %:</span><input type="number" lang="nb" class="fourDigit" name="sativaPercentage" placeholder="Sativa %" value="<?php echo $sativaPercentage; ?>" /><br />
<span class="fakelabel">THC %:</span><input type="number" lang="nb" name="THC" class="fourDigit" placeholder="THC %" value="<?php echo $THC; ?>" /><br />
<span class="fakelabel">CBD %:</span><input type="number" lang="nb" class="fourDigit" name="CBD" placeholder="CBD %" value="<?php echo $CBD; ?>" /><br />
<span class="fakelabel">CBN %:</span><input type="number" lang="nb" class="fourDigit" name="CBN" placeholder="CBN %" value="<?php echo $CBN; ?>" /><br /><br />
<?php echo $lang['extracts-description']; ?>:<br />
<textarea name="description"><?php echo $description; ?></textarea><br />
<?php echo $lang['extracts-medicaldesc']; ?>:<br />
<textarea name="medicaldescription"><?php echo $medicaldescription; ?></textarea><br />

 <button class='oneClick' name='oneClick' type="submit"><?php echo $lang['global-savechanges']; ?></button>
</form>

<?php displayFooter(); ?>


<!-- When script submits, check to see if password+salt matches pw+salt in db. If yes, leave. If no, change. Hepp! 
Conversely: Leave Password out of the form, and replace with a link 'change password' -->
