<?php
	
	require_once 'cOnfig/connection.php';
	require_once 'cOnfig/view.php';
	require_once 'cOnfig/authenticate.php';
	require_once 'cOnfig/languages/common.php';
	
	session_start();
	$accessLevel = '3';
	
	// Authenticate & authorize
	authorizeUser($accessLevel);
	
	$domain = $_SESSION['domain'];
	
	$deleteFlowerScript = <<<EOD
function delete_service(productid, category) {
	if (confirm("{$lang['confirm-deleteproduct']}")) {
				window.location = "uTil/bar-delete-product.php?productid=" + productid + "&category=" + category;
				}
}
EOD;
	pageStart('Bar products', NULL, $deleteFlowerScript, "pproducts", "admin", 'BAR PRODUCTS', $_SESSION['successMessage'], $_SESSION['errorMessage']);
	
	
?>

<center><a href="bar-new-product.php" class="cta"><?php echo $lang['admin-newstrain']; ?></a></center>

<?php

	// Query to look up categories
	$selectCats = "SELECT id, name from b_categories WHERE id > 48 OR (id = 35 OR id = 34 OR id = 30 OR id = 19) ORDER by id ASC";
		try
		{
			$results = $pdo3->prepare("$selectCats");
			$results->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		while ($category = $results->fetch()) {
		
		$categoryid = $category['id'];
		$name = $category['name'];
		
		echo "
<h3 class='title'>$name</h3>
	 <table class='default nonhover'>
	  <thead>
	   <tr>
	    <th></th>
	    <th>{$lang['global-name']}</th>
	    <th></th>
	    <th>{$lang['global-actions']}</th>
	   </tr>
	  </thead>
	  <tbody>";
	

	// For each cat, look up products
	$selectProducts = "SELECT productid, name, photoExt, description from b_products WHERE category = $categoryid AND productid NOT IN (36,58,78,92,133,129,131)";
		try
		{
			$result = $pdo3->prepare("$selectProducts");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		while ($product = $result->fetch()) {

		$productid = $product['productid'];
		$productName = $product['name'];
		$photoExt = $product['photoExt'];
		
	if ($product['description'] != '') {
		
		$commentRead = "
		                <img src='images/description.png' id='comment$productid' /><div id='helpBox$productid' class='helpBox'>{$product['description']}</div>
		                <script>
		                  	$('#comment$productid').on({
						 		'mouseover' : function() {
								 	$('#helpBox$productid').css('display', 'block');
						  		},
						  		'mouseout' : function() {
								 	$('#helpBox$productid').css('display', 'none');
							  	}
						  	});
						</script>
		                ";
		
	} else {
		
		$commentRead = "";
		
	}		
	
	$product_row =	sprintf("
  	  <tr>
  	   <td><a href='bar-change-product-image.php?productid=$productid'><img src='images/bar-products/%s' height='30' /></a></td>
  	   <td>%s</td>
  	   <td class='relative'>$commentRead</td>
  	   <td style='text-align: center;'><a href='bar-edit-product.php?productid=%d'><img src='images/edit.png' height='15' title='Editar' /></a>&nbsp;&nbsp;<a href='javascript:delete_service(%d, %d)'><img src='images/delete.png' height='15' title='Delete' /></a></td>
	  </tr>",
	  $productid . '.' . $photoExt, $productName, $productid, $productid, $categoryid
	  );
	  echo $product_row;
  }
  echo "</tbody></table>";
  
}

 displayFooter();
