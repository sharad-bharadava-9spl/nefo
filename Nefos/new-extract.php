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

	$name = htmlspecialchars($_POST['name']);
	$extracttype = $_POST['extracttype'];
	$extract = $_POST['extract'];
	$description = $_POST['description'];
	$medicaldescription = $_POST['medicaldescription'];
	$THC = $_POST['THC'];
	$CBD = $_POST['CBD'];
	$CBN = $_POST['CBN'];
	$insertTime = date('Y-m-d H:i:s');
	
		// Query to add new extract - 11 arguments
		  $query = sprintf("INSERT INTO extract (registeredSince, name, extracttype, extract, description, medicaldescription, THC, CBD, CBN) VALUES ('%s', '%s', '%s', '%s', '%s', '%s', '%f', '%f', '%f');",
		  $insertTime, $name, $extracttype, $extract, $description, $medicaldescription, $THC, $CBD, $CBN);
		  			
		mysql_query($query)
			or handleError($lang['error-savedata'],"Error inserting extract: " . mysql_error());
			
		// On success: redirect.
		$_SESSION['successMessage'] = $lang['extracts-addedsuccess'];
		
		if (isset($_POST['frompurchase'])) {
			header("Location: new-purchase.php");
		} else {
			header("Location: products.php");
		}

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

	pageStart($lang['title-newextract'], NULL, $validationScript, "pnewstrain", "admin", $lang['extracts-newextract'], $_SESSION['successMessage'], $_SESSION['errorMessage']);
	
?>

<form id="registerForm" action="" method="POST">

<?php
	if (isset($_GET['frompurchase'])) {
		echo "<input type='hidden' name='frompurchase' value='true' />";
	}
?>

   <input type="text" name="name" placeholder="<?php echo $lang['global-name']; ?>" value="<?php echo $name; ?>" /><br />
   <input type="text" name="extracttype" placeholder="<?php echo $lang['global-type']; ?>" value="<?php echo $extracttype; ?>" /><br />
  <select name="extract">
   <option value=""><?php echo $lang['global-extract']; ?>:</option>
   <option value="Dry"><?php echo $lang['extracts-dry']; ?></option>
   <option value="Ice"><?php echo $lang['extracts-ice']; ?></option>
   <option value="Wax"><?php echo $lang['extracts-wax']; ?></option>
   <option value="Oil"><?php echo $lang['extracts-oil']; ?></option>
   <option value="Ethanol"><?php echo $lang['extracts-ethanol']; ?></option>
   <option value="Glycerine"><?php echo $lang['extracts-glycerine']; ?></option>
  </select><br />
      <input type="number" lang="nb" name="THC" class="fourDigit" placeholder="THC %" value="<?php echo $THC; ?>" />
   <input type="number" lang="nb" class="fourDigit" name="CBD" placeholder="CBD %" value="<?php echo $CBD; ?>" />
   <input type="number" lang="nb" class="fourDigit" name="CBN" placeholder="CBN %" value="<?php echo $CBN; ?>" /><br /><br />

<textarea name="description" placeholder="<?php echo $lang['extracts-description']; ?>"><?php echo $description; ?></textarea><br />
<textarea name="medicaldescription" placeholder="<?php echo $lang['extracts-medicaldesc']; ?>"><?php echo $medicaldescription; ?></textarea><br />
 <button class='oneClick' name='oneClick' type="submit"><?php echo $lang['global-savechanges']; ?></button>
</form>

<?php displayFooter(); ?>

