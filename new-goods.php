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

		if ($_SESSION['domain'] == 'dabulance') {
			
			$name = str_replace("'","\'",str_replace('%', '&#37;', trim($_POST['name'])));
			$color = str_replace("'","\'",str_replace('%', '&#37;', trim($_POST['color'])));
			$material = str_replace("'","\'",str_replace('%', '&#37;', trim($_POST['material'])));
			$flowertype = $_POST['flowertype'];
			$description = str_replace("'","\'",str_replace('%', '&#37;', trim($_POST['description'])));
			$medicaldescription = str_replace("'","\'",str_replace('%', '&#37;', trim($_POST['medicaldescription'])));
			$category = $_POST['category'];
			$type = $_POST['type'];
			$insertTime = date('Y-m-d H:i:s');
		
			// Query to add new goods
			  $query = sprintf("INSERT INTO products (category, registeredSince, name, flowertype, description, medicaldescription, color, material, sex) VALUES ('%d', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%d');",
			  $category, $insertTime, $name, $flowertype, $description, $medicaldescription, $color, $material, $type);
	
			
		} else {
			
			$name = str_replace("'","\'",str_replace('%', '&#37;', trim($_POST['name'])));
			$flowertype = $_POST['flowertype'];
			$description = str_replace("'","\'",str_replace('%', '&#37;', trim($_POST['description'])));
			$medicaldescription = str_replace("'","\'",str_replace('%', '&#37;', trim($_POST['medicaldescription'])));
			$breed2 = str_replace("'","\'",str_replace('%', '&#37;', trim($_POST['breed2'])));
			$sativaPercentage = $_POST['sativaPercentage'];
			$THC = $_POST['THC'];
			$CBD = $_POST['CBD'];
			$CBN = $_POST['CBN'];
			$category = $_POST['category'];
			$insertTime = date('Y-m-d H:i:s');
		
			// Query to add new goods
			  $query = sprintf("INSERT INTO products (category, registeredSince, name, flowertype, description, medicaldescription, breed2, sativaPercentage, THC, CBD, CBN) VALUES ('%d', '%s', '%s', '%s', '%s', '%s', '%s', '%f', '%f', '%f', '%f');",
			  $category, $insertTime, $name, $flowertype, $description, $medicaldescription, $breed2, $sativaPercentage, $THC, $CBD, $CBN);
			  
		  }
	  
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
		
		$productid = $pdo3->lastInsertId();
			
		// On success: redirect.
		if ($_SESSION['domain'] == 'dabulance') {
			$_SESSION['successMessage'] = "Product added succesfully!<br /><br />

REMEMBER: This product won't appear in the menu until you input the stock.
To do so, please go to Admin Panel &raquo; Products Box &raquo; Open Purchases &raquo; <a href='new-purchase-2.php?productid=$productid&category=$category'>NEW PURCHASE</a>.";
		} else {
			$_SESSION['successMessage'] = $lang['product-added'] . "<br /><br />" . $lang['remember-add-purchase'];
		}
		
		if (isset($_POST['frompurchase'])) {
			header("Location: new-purchase.php");
		} else {
			header("Location: products.php");
		}
		
		exit();
	}
	/***** FORM SUBMIT END *****/
	
	$category = $_GET['id'];
	
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
  	    $name = $row['name'];
  	    $catType = $row['type'];

		if ($catType == 1) {
			$catT = '(g.)';
		} else {
			$catT = '(u.)';
		}
		
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

	pageStart($lang['pur-newproduct'], NULL, $validationScript, "pnewstrain", "admin", $lang['pur-newproduct'], $_SESSION['successMessage'], $_SESSION['errorMessage']);
		
	if ($_SESSION['domain'] == 'dabulance') { ?>
	

<form id="registerForm" action="" method="POST">
<input type='hidden' name='category' value='<?php echo $category; ?>' />
<input type='hidden' name='catType' value='<?php echo $catType; ?>' />

<?php
	if (isset($_GET['frompurchase'])) {
		echo "<input type='hidden' name='frompurchase' value='true' />";
	}
	
?>

<center>
<div id="mainbox-no-width">
 <div id="mainboxheader">
  <?php echo $lang['closeday-productdetails'] . " <span class='usergrouptext2' style='vertical-align: top; margin-top: 5px;'>$name $catT</span>"; ?>
 </div>
 <div class='boxcontent'>
 
 <table>
  <tr><td><span class="smallgreen"><?php echo $lang['global-name']; ?></span></td><td><input type="text" name="name" class='tenDigit defaultinput' placeholder="" /></td></tr>
  <tr><td><span class="smallgreen">Color</span></td><td><input type="text" name="color" class='tenDigit defaultinput' placeholder="" /></td></tr>
  <tr><td><span class="smallgreen">Material</span></td><td><input type="text" name="material" class='tenDigit defaultinput' placeholder="" /></td></tr>
  <tr><td><span class="smallgreen">Type</span></td><td style='padding-left: 10px;'>
  	<div class='fakeboxholder'>	
	 <label class="control">
	  <span id="errorBox2"></span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Male
	  <input type="radio" name='type' id='type' value='1' />
	  <div class="fakebox"></div>
	 </label>
	</div><br /><br />
	<div class='fakeboxholder'>	
	 <label class="control">
	  <span id="errorBox3"></span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Female
	  <input type="radio" name='type' id='type' value='2' />
	  <div class="fakebox"></div>
	 </label>
	</div><br /><br />
	<div class='fakeboxholder'>	
	 <label class="control">
	  <span id="errorBox3"></span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Unisex
	  <input type="radio" name='type' id='type' value='3' />
	  <div class="fakebox"></div>
	 </label>
	</div>

  </td></tr>
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
<button type="submit" class='cta4'>Save</button></center>

<?php } else { ?>


<form id="registerForm" action="" method="POST">
<input type='hidden' name='category' value='<?php echo $category; ?>' />
<input type='hidden' name='catType' value='<?php echo $catType; ?>' />

<?php
	if (isset($_GET['frompurchase'])) {
		echo "<input type='hidden' name='frompurchase' value='true' />";
	}
	
?>

<center>
<div id="mainbox-no-width">
 <div id="mainboxheader">
  <?php echo $lang['closeday-productdetails'] . " <span class='usergrouptext2' style='vertical-align: top; margin-top: 5px;'>$name $catT</span>"; ?>
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

<?php } displayFooter(); ?>

