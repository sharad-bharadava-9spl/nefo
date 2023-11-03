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
	$name = str_replace("'","\'",str_replace('%', '&#37;', trim($_POST['name'])));
	$flowertype = $_POST['flowertype'];
	$description = str_replace("'","\'",str_replace('%', '&#37;', trim($_POST['description'])));
	$medicaldescription = str_replace("'","\'",str_replace('%', '&#37;', trim($_POST['medicaldescription'])));
	$breed2 = str_replace("'","\'",str_replace('%', '&#37;', trim($_POST['breed2'])));
	$sativaPercentage = $_POST['sativaPercentage'];
	$flowernumber = $_POST['flowernumber'];
	$THC = $_POST['THC'];
	$CBD = $_POST['CBD'];
	$CBN = $_POST['CBN'];
	if ($sativaPercentage == '') {
		$sativaPercentage = 0;
	}
	if ($THC == '') {
		$THC = 0;
	}
	if ($CBD == '') {
		$CBD = 0;
	}
	if ($CBN == '') {
		$CBN = 0;
	}
	
		// Query to update flower - 11(10) arguments
		$updateFlower = sprintf("UPDATE flower SET name = '%s', flowertype = '%s', description = '%s', medicaldescription = '%s', breed2 = '%s', sativaPercentage = '%s', THC = '%s', CBD = '%s', CBN = '%s', flowernumber = '%d' WHERE flowerid = '%d';",
			$name,
			$flowertype,
			$description,
			$medicaldescription,
			$breed2,
			$sativaPercentage,
			$THC,
			$CBD,
			$CBN,
			$flowernumber,
			$flowerid
);

		try
		{
			$result = $pdo3->prepare("$updateFlower")->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
			
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
		try
		{
			$result = $pdo3->prepare("$flowerDetails");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
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

	pageStart($lang['title-editflower'], NULL, $validationScript, "pnewstrain", "admin", $lang['extracts-editflower'], $_SESSION['successMessage'], $_SESSION['errorMessage']);

	
?>


<form id="registerForm" action="" method="POST" style='text-align: left;'>
<center>
<div id="mainbox-no-width">
 <div id="mainboxheader">
  <?php echo $lang['closeday-productdetails'] . " <span class='usergrouptext2' style='vertical-align: top; margin-top: 5px;'>{$lang['title-flower']}</span>"; ?>
 </div>
 <div class='boxcontent'>
   <span class="smallgreen"><?php echo $lang['global-name']; ?></span><input type="text" name="name" class='tenDigit defaultinput' placeholder="" value="<?php echo $name; ?>" />
   <span class="smallgreen"><?php echo $lang['extracts-secondbreed']; ?></span><input type="text" name="breed2" class='tenDigit defaultinput' value="<?php echo $breed2; ?>" /><br />
  <select name="flowertype" class='defaultinput' style='width: 163px; height: 40px; margin-left: 0;'>
<?php if ($flowertype == NULL) { ?><option value=""><?php echo $lang['global-type']; ?>:</option> <?php } ?>
   <option value="Indica" <?php if ($flowertype == "Indica") {echo "selected";} ?>>Indica</option>
   <option value="Sativa" <?php if ($flowertype == "Sativa") {echo "selected";} ?>>Sativa</option>
   <option value="Hybrid" <?php if ($flowertype == "Hybrid") {echo "selected";} ?>><?php echo $lang['global-hybrid']; ?></option>
  </select>
  <span class="smallgreen">% Sativa</span><input type="number" lang="nb" name="sativaPercentage" class='fourDigit defaultinput' value="<?php echo $sativaPercentage; ?>"/>
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

<?php displayFooter(); 
