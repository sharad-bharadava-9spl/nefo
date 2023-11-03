<?php
	
	require_once 'cOnfig/connection.php';
	require_once 'cOnfig/view.php';
	require_once 'cOnfig/authenticate.php';
	require_once 'cOnfig/languages/common.php';
	
	session_start();
	$accessLevel = '3';
	
	// Authenticate & authorize
	authorizeUser($accessLevel);
	
	// Query to look up flowers
	$selectFlowers = "SELECT flowerid, flowertype, registeredSince, name, description, medicaldescription, breed2, sativaPercentage, THC, CBD, CBN, flowernumber FROM flower ORDER by name ASC";
		try
		{
			$resultFlower = $pdo3->prepare("$selectFlowers");
			$resultFlower->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		
	// Query to look up extract
	$selectExtract = "SELECT extractid, extracttype, registeredSince, name, description, medicaldescription, extract, THC, CBD, CBN, extractnumber FROM extract ORDER by name ASC";
		try
		{
			$resultExtract = $pdo3->prepare("$selectExtract");
			$resultExtract->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
	
	$deleteFlowerScript = <<<EOD
	
	    $(document).ready(function() {
			$('.mainTable').tablesorter();
		});
			
function delete_flower(flowerid) {
	if (confirm("{$lang['extracts-deleteflower']}")) {
				window.location = "uTil/delete-flower.php?flowerid=" + flowerid;
				}
}
function delete_extract(extractid) {
	if (confirm("{$lang['extracts-deleteextract']}")) {
				window.location = "uTil/delete-extract.php?extractid=" + extractid;
				}
}
function delete_product(productid, category) {
	if (confirm("{$lang['confirm-deleteproduct']}")) {
				window.location = "uTil/delete-product.php?productid=" + productid + "&category=" + category;
				}
}
EOD;
	pageStart($lang['global-products'], NULL, $deleteFlowerScript, "pproducts", "admin", $lang['global-productscaps'], $_SESSION['successMessage'], $_SESSION['errorMessage']);
		
?>

<center><a href="new-product.php" class="cta"><?php echo $lang['admin-newstrain']; ?></a></center>

	 
<?php

	// Query to look up categories
	$selectCats = "SELECT id, name, description, type from categories ORDER by id ASC";
		try
		{
			$resultCats = $pdo3->prepare("$selectCats");
			$resultCats->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		while ($category = $resultCats->fetch()) {	
		
		$categoryid = $category['id'];
		$name = $category['name'];
		$type = $category['type'];
		
	if ($type == 0) {
		$type = "u";
	} else {
		$type = "g";
	}
	

	// For each cat, look up products
	$selectProducts = "SELECT productid, name, description, medicaldescription, productnumber from products WHERE category = $categoryid";
		try
		{
			$result = $pdo3->prepare("$selectProducts");
			$result->execute();
			$data = $result->fetchAll();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
		
		
	if ($data) {

		echo "<br /><br />
<h3 class='title'>$name ($type)</h3>
	 <table class='default mainTable'>
	  <thead>
	   <tr style='cursor: pointer;'>
	    <th>{$lang['global-name']}</th>
	    <th>#</th>
	    <th colspan='2'>{$lang['extracts-description']}</th>
	    <th>{$lang['global-actions']}</th>
	   </tr>
	  </thead>
	  <tbody>";
	  
	foreach ($data as $product) {
		$productid = $product['productid'];
		$productName = $product['name'];
		
	
	if ($product['description'] != '') {
		
		$commentRead = "
		                <img src='images/description.png' id='comment$productid' /><div id='helpBox$productid' class='helpBox'><strong>{$lang['extracts-description']}:</strong><br />{$product['description']}</div>
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
		
	if ($product['medicaldescription'] != '') {
		
		$commentReadM = "
		                <img src='images/medical.png' width='15' id='commentM$productid' /><div id='helpBoxM$productid' class='helpBox'><strong>{$lang['extracts-medicaldesc']}:</strong><br />{$product['medicaldescription']}</div>
		                <script>
		                  	$('#commentM$productid').on({
						 		'mouseover' : function() {
								 	$('#helpBoxM$productid').css('display', 'block');
						  		},
						  		'mouseout' : function() {
								 	$('#helpBoxM$productid').css('display', 'none');
							  	}
						  	});
						</script>
		                ";
		
	} else {
		
		$commentReadM = "";
		
	}
	
	if ($product['productnumber'] != 0) {
		$productnumber = str_pad($product['productnumber'], 3, '0', STR_PAD_LEFT);
	} else {
		$productnumber = '';
	}
	
	$product_row =	sprintf("
  	  <tr>
  	   <td class='clickableRow' href='product.php?productid=%d'>%s</td>
  	   <td class='clickableRow' href='product.php?productid=%d'>%s</td>
  	   <td class='relative right clickableRow' style='padding: 11px 6px 11px 0;' href='product.php?productid=%d'>$commentRead</td>
  	   <td class='relative left clickableRow' style='padding: 11px 0 11px 6px;' href='product.php?productid=%d'>$commentReadM</td>
  	   <td style='text-align: center;'><a href='edit-product.php?productid=%d'><img src='images/edit.png' height='15' title='Editar' /></a>&nbsp;&nbsp;<a href='javascript:delete_product(%d, %d)'><img src='images/delete.png' height='15' title='Delete' /></a></td>
	  </tr>",
	  $productid, $productName, $productid, $productnumber, $productid, $productid, $productid, $productid, $categoryid
	  );
	  echo $product_row;
  }
  echo "</tbody></table>";
?>

<?php
	}

 }

displayFooter(); ?>