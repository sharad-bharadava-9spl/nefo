<?php
	
	require_once 'cOnfig/connection.php';
	require_once 'cOnfig/view.php';
	require_once 'cOnfig/authenticate.php';
	require_once 'cOnfig/languages/common.php';
	require_once 'googleConfig.php';

	session_start();
	$accessLevel = '3';
	
	// Authenticate & authorize
	authorizeUser($accessLevel);
	
	$domain = $_SESSION['domain'];
	
	// Did this page re-submit with a form? If so, check & store details
	if (isset($_POST['name'])) {

	$categorySelect = $_POST['categorySelect'];
	$price = $_POST['price'];
		$name = str_replace("'","\'",str_replace('%', '&#37;', trim($_POST['name'])));
		$description = str_replace("'","\'",str_replace('%', '&#37;', trim($_POST['description'])));
	$productid = $_POST['productid'];
		

	  $query = sprintf("UPDATE b_products SET category = '%d', name = '%s', description = '%s', price = '%f' WHERE productid = '%d';",
	  $categorySelect, $name, $description, $price, $productid);
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
		$_SESSION['successMessage'] = 'Product updated successfully!';
		header("Location: bar-products.php");
		exit();
	}
	/***** FORM SUBMIT END *****/
	
	// Did this page load with a pre-selected product?
	if ($_GET['productid']) {
		$productid = $_GET['productid'];

	} else {
		handleError("No service selected","");
	}
	
	

	// Query to look up service
	$selectService = "SELECT productid, category, name, description, price from b_products WHERE productid = $productid";
		try
		{
			$result = $pdo3->prepare("$selectService");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
		$productid = $row['productid'];
		$category = $row['category'];
		$name = $row['name'];
		$description = $row['description'];
		$price = $row['price'];
		
		if ($price == 0) {
			$price = '';
		}
		


	$validationScript = <<<EOD
    $(document).ready(function() {
	    	    
	  $('#registerForm').validate({
		  rules: {
			  purchaseQuantity: {
				  required: true
			  },
			  realQuantity: {
				  required: true
			  },
			  purchaseppg: {
				  required:"#inMenu:checked"
			  },
			  salesppg: {
				  required:"#inMenu:checked"
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

	pageStart('Edit product', NULL, $validationScript, "pnewcategory", "admin", 'Edit product', $_SESSION['successMessage'], $_SESSION['errorMessage']);
	

			// Query to look up categoryname
			$selectCat = "SELECT id, name FROM b_categories WHERE id = $category";
		try
		{
			$result = $pdo3->prepare("$selectCat");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
				$currName = $row['name'];
				$currID = $row['id'];
				
/*	$file1 = "images/_$domain/bar-products/" . $productid . ".jpeg";
	$file2 = "images/_$domain/bar-products/" . $productid . ".png";
	$file3 = "images/_$domain/bar-products/" . $productid . ".jpg";
	
	if (file_exists($file1)) {
		$fileFull = $file1;
	} else if (file_exists($file2)) {
		$fileFull = $file2;
	} else if (file_exists($file3)) {
		$fileFull = $file3;
	} else {
		$fileFull = $productid;
	}*/
/*	$bucketName = 'ccsnubev2';
	$storage = new StorageClient([
	   'keyFile' => json_decode($privateKeyFileContent, true)
	]);
	$bucket = $storage->bucket($bucketName);
	$object = $bucket->object(GLOCAL_SERVER."images/_$domain/bar-products/" . $productid . ".jpg");
	//echo $exist = $object->exists();*/

	$fileFull = $google_root."images/_$domain/bar-products/" . $productid . ".jpg";
				
?>

<center>
<div id="mainbox-no-width">
 <div id="mainboxheader">
  <?php echo $lang['closeday-productdetails'] . " <span class='usergrouptext2' style='vertical-align: top; margin-top: 5px;'>$name $catT</span>"; ?>
 </div>
 <div class='boxcontent'>

<form id="registerForm" action="" method="POST">

<a href='bar-change-product-image.php?productid=<?php echo $productid; ?>'><img src='<?php echo $fileFull; ?>' height='70' style='display: inline; vertical-align: middle;' /></a><br /><br />

   <input type="text" name="name" class='defaultinput' value="<?php echo $name; ?>" /><br />
   <input type="hidden" name="productid" value="<?php echo $productid; ?>" />
   
   <select name="categorySelect" class='defaultinput'>
   <option value='<?php echo $currID; ?>'><?php echo $currName; ?></option>
   
<?php

			

			// Query to look up categoryname
			$selectCat = "SELECT id, name FROM b_categories";
		try
		{
			$result = $pdo3->prepare("$selectCat");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		while ($oneCat = $result->fetch()) {
			if ($oneCat['id'] != $category) {
				$group_row = sprintf("<option value='%d'>%s</option>",
	  								 $oneCat['id'], $oneCat['name']);
	  			echo $group_row;
  			}
  		}
?>
   </select><br />
   <textarea name="description" placeholder="Descripcion" class='defaultinput' style='height: 100px;'><?php echo $description; ?></textarea><br />
 <br />
 <button class='oneClick cta4' name='oneClick' type="submit"><?php echo $lang['global-savechanges']; ?></button>
</form>

<?php displayFooter(); ?>

