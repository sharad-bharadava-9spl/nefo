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
	$category = $_POST['category'];
	$insertTime = date('Y-m-d H:i:s');
	
		// Query to add new goods
		  $query = sprintf("INSERT INTO products (category, registeredSince, name, flowertype, description, medicaldescription, breed2, sativaPercentage, THC, CBD, CBN) VALUES ('%d', '%s', '%s', '%s', '%s', '%s', '%s', '%f', '%f', '%f', '%f');",
		  $category, $insertTime, $name, $flowertype, $description, $medicaldescription, $breed2, $sativaPercentage, $THC, $CBD, $CBN);
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
		$_SESSION['successMessage'] = $lang['product-added'] . "<br /><br />" . $lang['remember-add-purchase'];
		
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
		
	echo "<h3 class='title'>$name $catT</h3>";
	
?>


<form id="registerForm" action="" method="POST">
<input type='hidden' name='category' value='<?php echo $category; ?>' />
<input type='hidden' name='catType' value='<?php echo $catType; ?>' />

<?php
	if (isset($_GET['frompurchase'])) {
		echo "<input type='hidden' name='frompurchase' value='true' />";
	}
	
	if ($catType == 1) {
?>
   <input type="text" name="name" placeholder="<?php echo $lang['global-name']; ?>" /> x <input type="text" name="breed2" placeholder="<?php echo $lang['extracts-secondbreed']; ?>" value="<?php echo $breed2; ?>" /><br />
  <select name="flowertype">
   <option value=""><?php echo $lang['global-type']; ?>:</option>
   <option value="Indica">Indica</option>
   <option value="Sativa">Sativa</option>
   <option value="Hybrid"><?php echo $lang['global-hybrid']; ?></option>
  </select><br />
   <input type="number" lang="nb" name="sativaPercentage" placeholder="Sativa %" value="<?php echo $sativaPercentage; ?>" /><br />
      <input type="number" lang="nb" name="THC" class="fourDigit" placeholder="THC %" value="<?php echo $THC; ?>" />
   <input type="number" lang="nb" class="fourDigit" name="CBD" placeholder="CBD %" value="<?php echo $CBD; ?>" />
   <input type="number" lang="nb" class="fourDigit" name="CBN" placeholder="CBN %" value="<?php echo $CBN; ?>" /><br /><br />

<?php } else { ?>


   <input type="text" name="name" placeholder="<?php echo $lang['global-name']; ?>" /><br />
<?php } ?>

<textarea name="description" placeholder="<?php echo $lang['extracts-description']; ?>"><?php echo $description; ?></textarea><br />
<textarea name="medicaldescription" placeholder="<?php echo $lang['extracts-medicaldesc']; ?>"><?php echo $medicaldescription; ?></textarea><br />
 <button class='oneClick' name='oneClick' type="submit"><?php echo $lang['global-savechanges']; ?></button>
</form>

<?php displayFooter(); ?>

