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

	$categoryID = $_POST['categoryID'];
	$name = str_replace("'","\'",str_replace('%', '&#37;', trim($_POST['name'])));
	$description = str_replace("'","\'",str_replace('%', '&#37;', trim($_POST['description'])));
	$price = $_POST['price'];
	$purchaseDate = date('Y-m-d H:i:s');	
	
	// Query to add new product - 11 arguments
	  $query = sprintf("INSERT INTO b_products (time, category, name, description, price) VALUES ('%s', '%d', '%s', '%s', '%f');",
	  $purchaseDate, $categoryID, $name, $description, $price);
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

	$_SESSION['productid'] = $productid;
		
		// On success: redirect.
		$_SESSION['successMessage'] = 'Product added successfully!';
		header("Location: bar-new-product-3.php");
		exit();
	}
	/***** FORM SUBMIT END *****/
	
	// Did this page load with a pre-selected product?
	if (isset($_GET['category'])) {
		
		$categoryID = $_GET['category'];
		
	} else if ($_POST['prePurchase']) {
		
		$categoryID = $_POST['prePurchase'];
		
	} else {
		
		echo $lang['error-noprodselected'];
		exit();
	}
		
				
			// Query to look up categoryname
			$selectFlower = "SELECT name FROM b_categories WHERE id = {$categoryID}";
		try
		{
			$result = $pdo3->prepare("$selectFlower");
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

	pageStart('New bar product', NULL, $validationScript, "pnewcategory", "admin", 'NEW BAR PRODUCT', $_SESSION['successMessage'], $_SESSION['errorMessage']);
	
	
?>

<form id="registerForm" action="" method="POST">
   <input type="hidden" name="categoryID" value="<?php echo $categoryID; ?>" />
   <input type="text" name="name" placeholder="Name" /><br />
   <textarea name="description" placeholder="Description"></textarea><br />
   
<!--   <strong>Price:</strong>
   
    <input type='number' lang='nb' class='fourDigit' id='price' name='price' step="0.01" /> &euro;
   
<br />-->
 <br />
 <button class='oneClick' name='oneClick' type="submit"><?php echo $lang['global-savechanges']; ?></button>
</form>

<?php displayFooter(); ?>

