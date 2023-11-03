<?php
	
	require_once 'cOnfig/connection.php';
	require_once 'cOnfig/view.php';
	require_once 'cOnfig/authenticate.php';
	require_once 'cOnfig/languages/common.php';
	
	session_start();
	$accessLevel = '3';
	
	// Authenticate & authorize
	authorizeUser($accessLevel);
	
	// Is Workstations enabled?
	if ($_SESSION['puestosOrNot'] == 1) {
		
		if ($_SESSION['workstation'] != 'bar' && $_SESSION['userGroup'] > 1) {
			
   			handleError("No tienes permiso para ver esta pagina! / You are not authorized to see that page.","User has too low access level.");
			exit();
			
		}
		
	}
	
	
	$domain = $_SESSION['domain'];
	
	$deleteFlowerScript = <<<EOD
	 $(document).ready(function() {
			$('.default').tablesorter({
				usNumberFormat: true,
				headers: {
					3: {
						sorter: "dates"
					},
					7: {
						sorter: "dates"
					}
				}
			}); 
		}); 	
function delete_service(productid, category) {
	if (confirm("{$lang['confirm-deleteproduct']}")) {
				window.location = "uTil/bar-delete-product.php?productid=" + productid + "&category=" + category;
				}
}
EOD;
	pageStart('Bar products', NULL, $deleteFlowerScript, "pproducts", "admin", 'BAR PRODUCTS', $_SESSION['successMessage'], $_SESSION['errorMessage']);
	
	
?>

<center><a href="bar-new-product.php" class="cta1"><?php echo $lang['admin-newstrain']; ?></a></center>

<?php

	// Query to look up categories
	$selectCats = "SELECT id, name, icon from b_categories ORDER by id ASC";
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
		$icon = $category['icon'];
		$domain = $_SESSION['domain'];
		$icon_image = "";
		if(trim($icon) != ''){
			$icon_image = "<img src='images/_$domain/bar-category/$icon' class='filter-white' height='30' style='margin-right: 10px; margin-bottom: -8px;' />";
		}
		
		echo "<h3 class='title' onClick='load$categoryid()' style='cursor: pointer;'>$icon_image $name <img src='images/plusnew.png' id='plus$categoryid' width='15' style='margin-left: 5px;' /><span id='spinner$categoryid'></span><input type='hidden' name='click$categoryid' id='click$categoryid' class='clickControl' value='0' /></h3><span id='menu$categoryid' style='display: none;'>";
		
		echo <<<EOD
<script>
function load$categoryid(){
	
  var x = document.getElementById("menu$categoryid");
  if (x.style.display === "none") {
	  
    x.style.display = "block";
	$("#plus$categoryid").attr("src","images/minusnew.png");

  } else {
	  
    x.style.display = "none";
	$("#plus$categoryid").attr("src","images/plusnew.png");
	
  }

};
</script>
EOD;

		
		echo "

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
	$selectProducts = "SELECT productid, name, photoExt, description from b_products WHERE category = $categoryid";
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
  	   <td class='clickableRow' href='bar-edit-product.php?productid=$productid'><a href='bar-change-product-image.php?productid=$productid'><img src='images/_$domain/bar-products/%s' height='30' /></a></td>
  	   <td class='clickableRow' href='bar-edit-product.php?productid=$productid'>%s</td>
  	   <td class='clickableRow' href='bar-edit-product.php?productid=$productid'><span class='relativeitem'>$commentRead</span></td>
  	   <td href='bar-edit-product.php?productid=$productid' style='text-align: center;'><a href='bar-edit-product.php?productid=%d'><img src='images/edit.png' height='15' title='Editar' /></a>&nbsp;&nbsp;<a href='javascript:delete_service(%d, %d)'><img src='images/delete.png' height='15' title='Delete' /></a></td>
	  </tr>",
	  $productid . '.' . $photoExt, $productName, $productid, $productid, $categoryid
	  );
	  echo $product_row;
  }
  echo "</tbody></table></span>";
  
}

 displayFooter();
