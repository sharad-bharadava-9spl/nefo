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
	$name = str_replace("'","\'",str_replace('%', '&#37;', trim($_POST['name'])));
	$extracttype = $_POST['extracttype'];
	$extract = $_POST['extract'];
	$description = str_replace("'","\'",str_replace('%', '&#37;', trim($_POST['description'])));
	$medicaldescription = str_replace("'","\'",str_replace('%', '&#37;', trim($_POST['medicaldescription'])));
	$extractnumber = $_POST['extractnumber'];
	$THC = $_POST['THC'];
	$CBD = $_POST['CBD'];
	$CBN = $_POST['CBN'];
	$sativaPercentage = $_POST['sativaPercentage'];
	
	
	
		// Query to update extract - 11(10) arguments
		$updateExtract = sprintf("UPDATE extract SET name = '%s', extracttype = '%s', extract = '%s', description = '%s', medicaldescription = '%s', THC = '%s', CBD = '%s', CBN = '%s', extractnumber = '%d', sativaPercentage = '%f' WHERE extractid = '%d';",
$name,
$extracttype,
$extract,
$description,
$medicaldescription,
$THC,
$CBD,
$CBN,
$extractnumber,
$sativaPercentage,
$extractid
);
			
		try
		{
			$result = $pdo3->prepare("$updateExtract")->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
			
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
	$extractDetails = "SELECT extractid, extracttype, extract, registeredSince, name, description, medicaldescription, THC, CBD, CBN, extractnumber, sativaPercentage FROM extract WHERE extractid = '{$extractid}'";
		try
		{
			$result = $pdo3->prepare("$extractDetails");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
	$extractid = $row['extractid'];
	$registeredSince = $row['registeredSince'];
	$name = $row['name'];
	$extracttype = $row['extracttype'];
	$extract = $row['extract'];
	$description = $row['description'];
	$medicaldescription = $row['medicaldescription'];
	$extractnumber = $row['extractnumber'];
	$sativaPercentage = $row['sativaPercentage'];
	$THC = $row['THC'];
	$CBD = $row['CBD'];
	$CBN = $row['CBN'];


	pageStart($lang['title-editeextract'], NULL, $validationScript, "pnewstrain", "admin", $lang['extracts-editextract'], $_SESSION['successMessage'], $_SESSION['errorMessage']);

	
?>


<form id="registerForm" action="" method="POST" style='text-align: left;'>
<center>
<div id="mainbox-no-width">
 <div id="mainboxheader">
  <?php echo $lang['closeday-productdetails'] . " <span class='usergrouptext2' style='vertical-align: top; margin-top: 5px;'>{$lang['title-extract']}</span>"; ?>
 </div>
 <div class='boxcontent'>
 
   <span class="smallgreen"><?php echo $lang['global-name']; ?></span><input type="text" name="name" class='tenDigit defaultinput' placeholder="" value="<?php echo $name; ?>" />
   <span class="smallgreen">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?php echo $lang['global-type']; ?></span><input type="text" name="extracttype" class='tenDigit defaultinput' value="<?php echo $extracttype; ?>" /><br />
  <select name="extract" class='defaultinput' style='width: 163px; height: 40px; margin-left: 0;'>
<?php if ($extract == NULL) { ?><option value=""><?php echo $lang['global-choose']; ?></option> <?php } ?>
   <option value="Dry" <?php if ($extract == "Dry") {echo "selected";} ?>><?php echo $lang['extracts-dry']; ?></option>
   <option value="Ice" <?php if ($extract == "Ice") {echo "selected";} ?>><?php echo $lang['extracts-ice']; ?></option>
   <option value="Wax" <?php if ($extract == "Wax") {echo "selected";} ?>><?php echo $lang['extracts-wax']; ?></option>
   <option value="Oil" <?php if ($extract == "Oil") {echo "selected";} ?>><?php echo $lang['extracts-oil']; ?></option>
   <option value="Ethanol" <?php if ($extract == "Ethanol") {echo "selected";} ?>><?php echo $lang['extracts-ethanol']; ?></option>
   <option value="Glycerine" <?php if ($extract == "Glycerine") {echo "selected";} ?>><?php echo $lang['extracts-glycerine']; ?></option>
  </select>  <span class="smallgreen">% Sativa</span><input type="number" lang="nb" name="sativaPercentage" class='fourDigit defaultinput' value="<?php echo $sativaPercentage; ?>" />
  <span class="smallgreen">% THC</span><input type="number" lang="nb" name="THC" class="fourDigit defaultinput" value="<?php echo $THC; ?>" />
  <span class="smallgreen">% CBD</span><input type="number" lang="nb" class="fourDigit defaultinput" name="CBD" value="<?php echo $CBD; ?>" />
  <span class="smallgreen">% CBN</span><input type="number" lang="nb" class="fourDigit defaultinput" name="CBN" value="<?php echo $CBN; ?>" />
  <br />
  <table style='width: 100%;'>
   <tr>
    <td>&nbsp;<img src='images/info-new.png' style='margin-bottom: -1px;' />&nbsp;&nbsp;<span class="smallgreen"><?php echo $lang['extracts-description']; ?></span><br /><textarea name="description"><?php echo $description; ?></textarea></td>
    <td>&nbsp;<img src='images/medical-new.png' style='margin-bottom: -1px;' />&nbsp;&nbsp;<span class="smallgreen"><?php echo $lang['extracts-medicaldesc']; ?></span><br /><textarea name="medicaldescription"><?php echo $medicaldescription; ?></textarea></td>
   </tr>
   </table>


</form>
</div>
</div><br />
<button type="submit" class='cta4'><?php echo $lang['global-savechanges']; ?></button></center>

<?php displayFooter(); ?>
