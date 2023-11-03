<?php
	
	require_once 'cOnfig/connection.php';
	require_once 'cOnfig/view.php';
	require_once 'cOnfig/authenticate.php';
	require_once 'cOnfig/languages/common.php';
	
	session_start();
	$accessLevel = '3';
	
	// Authenticate & authorize
	authorizeUser($accessLevel);

	pageStart($lang['create-prerolls'], NULL, NULL, "pclosePurchase", "", $lang['create-prerolls'], $_SESSION['successMessage'], $_SESSION['errorMessage']);
	
?>
<form id="registerForm" action="" method="POST">
<div class="actionbox">
<table>
<!-- <thead>
  <tr>
   <th><?php echo $lang['global-product']; ?></th>
   <th><?php echo $lang['global-quantity']; ?></th>
  </tr>
 </thead>-->
 <tbody>
  <tr>
   <td colspan="2"><h3><?php echo $lang['global-flowers']; ?></h3></td>
  </tr>
  
<?php
		
	$selectFlower = "SELECT g.breed2, g.name, g.flowerid, p.category, p.purchaseid FROM flower g, purchases p WHERE p.category = 1 AND p.productid = g.flowerid AND p.closedAt IS NULL ORDER BY g.name ASC;";
	$resultFlower = mysql_query($selectFlower)
		or handleError($lang['error-prodprices'],"Error loading flower prices from db: " . mysql_error());

		
while ($flower = mysql_fetch_array($resultFlower)) {
	$name = $flower['name'];
	$category = $flower['category'];
	$productid = $flower['flowerid'];
	$purchaseid = $flower['purchaseid'];
	
		if ($flower['breed2'] != '') {
			$name = $name . " x " . $flower['breed2'];
		}

	$flower_row = sprintf("
	<tr>
	 <td>%s</td>
	 <td>
	  <input type='number' lang='nb' class='fourDigit centered calc' name='sales[%d][grams]' placeholder='Gr.' step='0.01' />
	 </td>
	</tr>",
	  $name, 1
	  );
	  echo $flower_row;
  }
?>
    <tr>
   <td colspan="2"><h3><?php echo $lang['global-extracts']; ?></h3></td>
  </tr>

<?php  
  // AND NOW THE H TABLE:

  	$selectExtract = "SELECT h.name, p.purchaseid, p.category, h.extractid FROM extract h, purchases p WHERE p.category = 2 AND p.productid = h.extractid AND p.closedAt IS NULL ORDER BY h.name ASC;";
	$resultExtract = mysql_query($selectExtract)
		or handleError($lang['error-prodprices'],"Error loading extract prices from db: " . mysql_error());

while ($extract = mysql_fetch_array($resultExtract)) {
	$name = $extract['name'];
	$category = $extract['category'];
	$productid = $extract['extractid'];
	$purchaseid = $extract['purchaseid'];

	$extract_row =	sprintf("
	<tr>
	 <td>%s</td>
	 <td>
	  <input type='number' lang='nb' class='fourDigit centered calc' name='sales[%d][grams]' placeholder='Gr.' step='0.01' />
	 </td>
	</tr>",
	  $extract['name'], 1
	  );
	  echo $extract_row;
  }
  
echo "</tbody></table></div>";
  
?>
<br >
<div class="actionbox">
 <table>
  <tbody>
   <tr>
    <td colspan="2"><h3>Preliados</h3></td>
   </tr>



<?php

	 // For each cat, look up products
  	$selectProduct = "SELECT pr.productid, pr.name, pr.description, pr.medicaldescription, p.purchaseid, p.salesPrice, p.realQuantity, p.photoExt FROM products pr, purchases p WHERE p.category = 46 AND p.productid = pr.productid AND p.closedAt IS NULL AND inMenu = 1 ORDER BY p.salesPrice ASC;";
  	
  	
	$resultProduct = mysql_query($selectProduct)
		or handleError($lang['error-prodprices'],"Error loading extract prices from db: " . mysql_error());
		
	while ($product = mysql_fetch_array($resultProduct)) {
			
		$name = $product['name'];	
		
		echo "<tr><td>$name</td><td><input class='twoDigit centered' placeholder='#' /></td></tr>";	
			
			
	}


?>
  
  
 </table>

</div>
<button type="submit">Guardar</button>
  

  
<?php displayFooter(); ?>

