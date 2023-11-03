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

	if ($catType == 1) {

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

	} else {

		// Query to update category
		$updateCat = sprintf("UPDATE products SET name = '%s', description = '%s', medicaldescription = '%s', productnumber = '%d' WHERE productid = '%d';",
			$name,
			$description,
			$medicaldescription,
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
	$selectProduct = "SELECT name, flowertype, registeredSince, flowertype, name, description, medicaldescription, breed2, sativaPercentage, THC, CBD, CBN, productnumber, category from products WHERE productid = $productid";
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
  	    
		// Query to look up category
		$selectCats = "SELECT type from categories WHERE id = $category";
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
		
	
?>

<form id="registerForm" action="" method="POST" style='text-align: left;'>
<input type='hidden' name='productid' value='<?php echo $productid; ?>' />
<input type='hidden' name='catType' value='<?php echo $catType; ?>' />
<label class='fakelabel' for='productnumber'><?php echo $lang['product-number']; ?></label><input type="text" name="productnumber" value="<?php echo sprintf('%03d', $productnumber); ?>" maxlength="4"/><br />

<?php
	
	if ($catType == 1) {
?>
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


<?php } else { ?>
<label class='fakelabel' for='name'><?php echo $lang['global-name']; ?></label><input type="text" name="name" value="<?php echo $name; ?>" /><br /><br />
<?php } ?>
<?php echo $lang['extracts-description']; ?>:<br />
 <textarea name="description"><?php echo $description; ?></textarea><br /><br />
 <?php echo $lang['extracts-medicaldesc']; ?>:<br />
<textarea name="medicaldescription"><?php echo $medicaldescription; ?></textarea><br />

 <button class='oneClick' name='oneClick' type="submit"><?php echo $lang['global-savechanges']; ?></button>
</form>

<?php displayFooter(); ?>

