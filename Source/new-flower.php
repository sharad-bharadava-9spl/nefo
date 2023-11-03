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

	$name = str_replace("'","\'",str_replace('%', '&#37;', trim($_POST['name'])));
	$flowertype = $_POST['flowertype'];
	$description = str_replace("'","\'",str_replace('%', '&#37;', trim($_POST['description'])));
	$medicaldescription = str_replace("'","\'",str_replace('%', '&#37;', trim($_POST['medicaldescription'])));
	$breed2 = str_replace("'","\'",str_replace('%', '&#37;', trim($_POST['breed2'])));
	$sativaPercentage = $_POST['sativaPercentage'];
	$THC = $_POST['THC'];
	$CBD = $_POST['CBD'];
	$CBN = $_POST['CBN'];
	$insertTime = date('Y-m-d H:i:s');
	
		// Query to add new flower - 11 arguments
		  $query = sprintf("INSERT INTO flower (registeredSince, name, flowertype, description, medicaldescription, breed2, sativaPercentage, THC, CBD, CBN) VALUES ('%s', '%s', '%s', '%s', '%s', '%s', '%f', '%f', '%f', '%f');",
		  $insertTime, $name, $flowertype, $description, $medicaldescription, $breed2, $sativaPercentage, $THC, $CBD, $CBN);
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
		$_SESSION['successMessage'] = $lang['flowers-addedsuccess'] . "<br /><br />" . $lang['remember-add-purchase'];
		
		
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

	pageStart($lang['title-newflower'], NULL, $validationScript, "pnewstrain", "admin", $lang['extracts-newflower'], $_SESSION['successMessage'], $_SESSION['errorMessage']);
	
	
?>

<form id="registerForm" action="" method="POST">

<?php
	if (isset($_GET['frompurchase'])) {
		echo "<input type='hidden' name='frompurchase' value='true' />";
	}
?>
<center>
<div id="mainbox-no-width">
 <div id="mainboxheader">
  <?php echo $lang['closeday-productdetails'] . " <span class='usergrouptext2' style='vertical-align: top; margin-top: 5px;'>{$lang['title-flower']}</span>"; ?>
 </div>
 <div class='boxcontent'>
   <span class="smallgreen"><?php echo $lang['global-name']; ?></span><input type="text" name="name" class='tenDigit defaultinput' placeholder="" />
   <span class="smallgreen"><?php echo $lang['extracts-secondbreed']; ?></span><input type="text" name="breed2" class='tenDigit defaultinput' value="<?php echo $breed2; ?>" /><br />
  <select name="flowertype" class='defaultinput' style='width: 163px; height: 40px; margin-left: 0;'>
   <option value=""><?php echo $lang['global-type']; ?>:</option>
   <option value="Indica">Indica</option>
   <option value="Sativa">Sativa</option>
   <option value="Hybrid"><?php echo $lang['global-hybrid']; ?></option>
  </select>
  <span class="smallgreen">% Sativa</span><input type="number" lang="nb" name="sativaPercentage" class='fourDigit defaultinput' />
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

