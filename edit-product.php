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

	$registeredSince = $_POST['registeredSince'];
	$name = str_replace("'","\'",str_replace('%', '&#37;', trim($_POST['name'])));
	$flowertype = $_POST['flowertype'];
	$description = str_replace("'","\'",str_replace('%', '&#37;', trim($_POST['description'])));
	$medicaldescription = str_replace("'","\'",str_replace('%', '&#37;', trim($_POST['medicaldescription'])));
	$breed2 = str_replace("'","\'",str_replace('%', '&#37;', trim($_POST['breed2'])));
	$sativaPercentage = $_POST['sativaPercentage'];
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
	$productid = $_POST['productid'];
	$productnumber = $_POST['productnumber'];
	$catType = $_POST['catType'];
	$color = $_POST['color'];
	$material = $_POST['material'];

	
	if ($_SESSION['domain'] == 'dabulance') {
		
		$updateCat = sprintf("UPDATE products SET name = '%s', flowertype = '%s', description = '%s', medicaldescription = '%s', productnumber = '%d', color = '%s', material = '%s' WHERE productid = '%d';",
			$name,
			$flowertype,
			$description,
			$medicaldescription,
			$productnumber,
			$color,
			$material,
			$productid
);

	} else {


		// Query to update flower - 11(10) arguments
		$updateCat = sprintf("UPDATE products SET name = '%s', flowertype = '%s', description = '%s', medicaldescription = '%s', breed2 = '%s', sativaPercentage = '%s', THC = '%s', CBD = '%s', CBN = '%s', productnumber = '%d' WHERE productid = '%d';",
			$name,
			$flowertype,
			$description,
			$medicaldescription,
			$breed2,
			$sativaPercentage,
			$THC,
			$CBD,
			$CBN,
			$productnumber,
			$productid
);

	}

		try
		{
			$result = $pdo3->prepare("$updateCat")->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
			
		// On success: redirect.
		$_SESSION['successMessage'] = "Product modified successfully!";
		
		if (isset($_POST['frompurchase'])) {
			header("Location: new-purchase.php");
		} else {
			header("Location: products.php");
		}
		
		exit();
	}
	/***** FORM SUBMIT END *****/
	
	$productid = $_GET['productid'];
	
	// Query to look up product
	if ($_SESSION['domain'] == 'dabulance') {
		
		$selectProduct = "SELECT name, flowertype, registeredSince, flowertype, name, description, medicaldescription, productnumber, category, color, material from products WHERE productid = $productid";
		
	} else {
		
	$selectProduct = "SELECT name, flowertype, registeredSince, flowertype, name, description, medicaldescription, breed2, sativaPercentage, THC, CBD, CBN, productnumber, category from products WHERE productid = $productid";
		
	}
	
		try
		{
			$result = $pdo3->prepare("$selectProduct");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
		$registeredSince = $row['registeredSince'];
		$name = $row['name'];
		$flowertype = $row['flowertype'];
		$description = $row['description'];
		$medicaldescription = $row['medicaldescription'];
		$breed2 = $row['breed2'];
		$sativaPercentage = $row['sativaPercentage'];
		$THC = $row['THC'];
		$CBD = $row['CBD'];
		$CBN = $row['CBN'];
  	    $productnumber = $row['productnumber'];
  	    $category = $row['category'];
  	    $color = $row['color'];
  	    $material = $row['material'];
  	    
		// Query to look up category
		$selectCats = "SELECT name, type from categories WHERE id = $category";
		try
		{
			$result = $pdo3->prepare("$selectCats");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
	  	    $catType = $row['type'];
	  	    $catName = $row['name'];


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

	pageStart($lang['editproduct'], NULL, $validationScript, "pnewstrain", "admin", $lang['editproductcaps'], $_SESSION['successMessage'], $_SESSION['errorMessage']);
		
	
	if ($_SESSION['domain'] == 'dabulance') { ?>

<form id="registerForm" action="" method="POST" style='text-align: left;'>
<input type='hidden' name='productid' value='<?php echo $productid; ?>' />
<input type='hidden' name='catType' value='<?php echo $catType; ?>' />

<center>
<div id="mainbox-no-width">
 <div id="mainboxheader">
  <?php echo $lang['closeday-productdetails'] . " <span class='usergrouptext2' style='vertical-align: top; margin-top: 5px;'>$catName</span>"; ?>
 </div>
 <div class='boxcontent'>
 <table>
  <tr><td><span class="smallgreen"><?php echo $lang['global-name']; ?></span></td><td><input type="text" name="name" class='tenDigit defaultinput' placeholder="" value="<?php echo $name; ?>" /></td></tr>
  <tr><td><span class="smallgreen">Color</span></td><td><input type="text" name="color" class='tenDigit defaultinput' placeholder="" value="<?php echo $color; ?>" /></td></tr>
  <tr><td><span class="smallgreen">Material</span></td><td><input type="text" name="material" class='tenDigit defaultinput' placeholder="" value="<?php echo $material; ?>" /></td></tr>
 </table>
      <br />
  <table style='width: 100%;'>
   <tr>
    <td>&nbsp;<img src='images/info-new.png' style='margin-bottom: -1px;' />&nbsp;&nbsp;<span class="smallgreen"><?php echo $lang['extracts-description']; ?></span><br /><textarea name="description"><?php echo $description; ?></textarea></td>
   </tr>
   </table>


</form>
</div>
</div><br />
<button type="submit" class='cta4'><?php echo $lang['global-savechanges']; ?></button></center>
	
<?php } else { ?>

<form id="registerForm" action="" method="POST" style='text-align: left;'>
<input type='hidden' name='productid' value='<?php echo $productid; ?>' />
<input type='hidden' name='catType' value='<?php echo $catType; ?>' />

<center>
<div id="mainbox-no-width">
 <div id="mainboxheader">
  <?php echo $lang['closeday-productdetails'] . " <span class='usergrouptext2' style='vertical-align: top; margin-top: 5px;'>$catName</span>"; ?>
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

<?php } displayFooter(); 
