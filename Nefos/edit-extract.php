<?php
	
	require_once 'cOnfig/connection.php';
	require_once 'cOnfig/view.php';
	require_once 'cOnfig/authenticate.php';
	require_once 'cOnfig/languages/common.php';
	
	session_start();
	$accessLevel = '3';
	
	// Authenticate & authorize
	authorizeUser($accessLevel);
	
	$extractid = $_GET['extractid'];
	// Did this page re-submit with a form? If so, check & store details
	if (isset($_POST['name'])) {

	$registeredSince = $_POST['registeredSince'];
	$name = $_POST['name'];
	$extracttype = $_POST['extracttype'];
	$extract = $_POST['extract'];
	$description = $_POST['description'];
	$medicaldescription = $_POST['medicaldescription'];
	$extractnumber = $_POST['extractnumber'];
	$THC = $_POST['THC'];
	$CBD = $_POST['CBD'];
	$CBN = $_POST['CBN'];
	
		// Query to update extract - 11(10) arguments
		$updateExtract = sprintf("UPDATE extract SET name = '%s', extracttype = '%s', extract = '%s', description = '%s', medicaldescription = '%s', THC = '%s', CBD = '%s', CBN = '%s', extractnumber = '%d' WHERE extractid = '%d';",
mysql_real_escape_string($name),
mysql_real_escape_string($extracttype),
mysql_real_escape_string($extract),
mysql_real_escape_string($description),
mysql_real_escape_string($medicaldescription),
mysql_real_escape_string($THC),
mysql_real_escape_string($CBD),
mysql_real_escape_string($CBN),
mysql_real_escape_string($extractnumber),
mysql_real_escape_string($extractid)
);
			
		mysql_query($updateExtract)
			or handleError($lang['error-savedata'],"Error updating extract: " . mysql_error());
			
		// On success: redirect.
		$_SESSION['successMessage'] = $lang['extracts-updatesuccess'];
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

	// Query to look for extract
	$extractDetails = "SELECT extractid, extracttype, extract, registeredSince, name, description, medicaldescription, THC, CBD, CBN, extractnumber FROM extract WHERE extractid = '{$extractid}'";
	
	// Does extract ID exist?
	$extractCheck = mysql_query($extractDetails);
	if(mysql_num_rows($extractCheck) == 0) {
   		handleError($lang['error-extractidnotexist'],"");
	}
			
	$result = mysql_query($extractDetails)
		or handleError($lang['error-errorloadingextract'],"Error loading extract: " . mysql_error());
	
	if ($result) {
	$row = mysql_fetch_array($result);
	$extractid = $row['extractid'];
	$registeredSince = $row['registeredSince'];
	$name = $row['name'];
	$extracttype = $row['extracttype'];
	$extract = $row['extract'];
	$description = $row['description'];
	$medicaldescription = $row['medicaldescription'];
	$extractnumber = $row['extractnumber'];
	$THC = $row['THC'];
	$CBD = $row['CBD'];
	$CBN = $row['CBN'];

} else {
		handle_error($lang['error-findinginfo'],"Error locating extract with ID {$extractid}");
}

	pageStart($lang['title-editeextract'], NULL, $validationScript, "pnewstrain", "admin", $lang['extracts-editextract'], $_SESSION['successMessage'], $_SESSION['errorMessage']);

	
?>


<form id="registerForm" action="" method="POST" style='text-align: left;'>
<label class='fakelabel' for='productnumber'><?php echo $lang['product-number']; ?></label><input type="text" name="extractnumber" value="<?php echo sprintf('%03d', $extractnumber); ?>" maxlength="4" /><br />
   <span class="fakelabel"><?php echo $lang['global-name']; ?>:</span><input type="text" name="name" placeholder="<?php echo $lang['global-name']; ?>" value="<?php echo $name; ?>" /><br />
   <span class="fakelabel"><?php echo $lang['global-type']; ?>:</span><input type="text" name="extracttype" placeholder="<?php echo $lang['global-type']; ?>" value="<?php echo $extracttype; ?>" /><br />
   <span class="fakelabel"><?php echo $lang['global-extract']; ?>:</span>
   <select name="extract">
<?php if ($extract == NULL) { ?><option value=""><?php echo $lang['global-choose']; ?></option> <?php } ?>
   <option value="Dry" <?php if ($extract == "Dry") {echo "selected";} ?>><?php echo $lang['extracts-dry']; ?></option>
   <option value="Ice" <?php if ($extract == "Ice") {echo "selected";} ?>><?php echo $lang['extracts-ice']; ?></option>
   <option value="Wax" <?php if ($extract == "Wax") {echo "selected";} ?>><?php echo $lang['extracts-wax']; ?></option>
   <option value="Oil" <?php if ($extract == "Oil") {echo "selected";} ?>><?php echo $lang['extracts-oil']; ?></option>
   <option value="Ethanol" <?php if ($extract == "Ethanol") {echo "selected";} ?>><?php echo $lang['extracts-ethanol']; ?></option>
   <option value="Glycerine" <?php if ($extract == "Glycerine") {echo "selected";} ?>><?php echo $lang['extracts-glycerine']; ?></option>
  </select><br />
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
