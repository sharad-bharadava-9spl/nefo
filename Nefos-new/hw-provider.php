<?php
	//Created by Konstant for Task-14954900 on 18/10/2021
	require_once 'cOnfig/connection.php';
	require_once 'cOnfig/viewv6.php';
	require_once 'cOnfig/authenticate.php';
	require_once 'cOnfig/languages/common.php';
	
	session_start();
	$accessLevel = '3';
	
	// Authenticate & authorize
	authorizeUser($accessLevel);

	if(!isset($_REQUEST['id'])){
		$_SESSION['errorMessage'] = "Please provide valid provider id!";
		header("Location: hw-providers.php");
		exit();
	}

	$provider_id = $_REQUEST['id'];
	// submit products offered
	if(isset($_POST['submitted'])){
		$provider_id = $_POST['provider_id'];
		$products_arr = $_POST['choose_products'];
		$products_offered = implode(",", $products_arr);

		// update product offeres

		$updateProvider = "UPDATE hw_providers SET products_offers = '$products_offered' WHERE id=".$provider_id;
		try{
			$up_results = $pdo3->prepare("$updateProvider");
			$up_results->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
		$_SESSION['successMessage'] = "Products Offered updated!";
		header("Location: hw-provider.php?id=".$provider_id);
		exit();
	}
	
	// Query to look up provider
	$providerDetails = "SELECT registered, name, credit FROM hw_providers WHERE id = $provider_id";
	try
	{
		$result = $pdo3->prepare("$providerDetails");
		$result->execute();
	}
	catch (PDOException $e)
	{
			$error = 'Error fetching user: ' . $e->getMessage();
			echo $error;
			exit();
	}

	$row = $result->fetch();
		$registered = $row['registered'];
		$name = $row['name'];
		$credit = $row['credit'];
		
	$selectPurchases = "SELECT '' AS id,  'purchase' AS type, purchaseDate, purchaseid, category, productid, purchaseQuantity, purchasePrice, paid, adminComment AS comment FROM b_purchases WHERE hw_provider = $provider_id  UNION ALL SELECT paymentid AS id, 'payment' AS type, paymentTime AS purchaseDate, '' AS purchaseid, '' AS category, '' AS productid, '' AS purchaseQuantity, '' AS purchasePrice, amount AS paid, comment FROM hw_providerpayments WHERE providerid = $provider_id ORDER BY purchaseDate DESC ";
	try
	{
		$resultPurchases = $pdo3->prepare("$selectPurchases");
		$resultPurchases->execute();
	}
	catch (PDOException $e)
	{
			$error = 'Error fetching user: ' . $e->getMessage();
			echo $error;
			exit();
	}
	$selectPurchases2 = "SELECT SUM(paid) FROM b_purchases WHERE hw_provider = $provider_id";
		try
		{
			$resultPurchases2 = $pdo3->prepare("$selectPurchases2");
			$resultPurchases2->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$rowX = $resultPurchases2->fetch();
		$purchasePaid = $rowX['SUM(paid)'];
		
		
	$selectTotal = "SELECT purchasePrice, purchaseQuantity FROM b_purchases WHERE hw_provider = $provider_id";
		try
		{
			$resultTotal = $pdo3->prepare("$selectTotal");
			$resultTotal->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
	while ($onePurchase = $resultTotal->fetch()) {
	
		$purchasePrice = $onePurchase['purchasePrice'];
		$purchaseQuantity = $onePurchase['purchaseQuantity'];
		
		$thisPurchase = $purchasePrice * $purchaseQuantity;
		$totalPurchased = $totalPurchased + $thisPurchase;
		
	}
	

		$totalPaid =  $purchasePaid;
		
	$selectTotal2 = "SELECT SUM(amount) FROM hw_providerpayments WHERE providerid = $provider_id";
		try
		{
			$resultTotal2 = $pdo3->prepare("$selectTotal2");
			$resultTotal2->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
	$row = $resultTotal2->fetch();
		$totalPaid = $row['SUM(amount)'] + $purchasePaid;
		
	$totCredit = $totalPaid - $totalPurchased;


	$deleteFlowerScript = <<<EOD
function delete_service(productid, category) {
	if (confirm("{$lang['confirm-deleteproduct']}")) {
				window.location = "uTil/bar-delete-product.php?productid=" + productid + "&category=" + category;
				}
}
EOD;
	pageStart('HW Provider', NULL, $deleteFlowerScript, "pproducts", "admin", 'HW Provider', $_SESSION['successMessage'], $_SESSION['errorMessage']);
	echo "<center><a href='hw-providers.php' class='cta1'>HW Providers</a><a href='bar-pay-hw-provider.php?providerid=$provider_id' class='cta1'>Make Payment</a></center>";

		if ($totCredit < 0) {
		$colourStyle = "style='background-color: #fef4f3;'";
	}
	?>

<br />
<center>
<div id="mainbox-no-width">
 <div id="mainboxheader">
  #<?php echo $providernumber; ?> - <?php echo $name; ?>
 </div>
 <div class='boxcontent'>
  
 <table style="border-spacing: 10px; border-collapse: separate;">
  <tr>
   <td class='dispensetd'><?php echo $lang['purchases']; ?>:<div style='inline-block; float: right;'><?php echo number_format($totalPurchased,2); ?> &euro;</div></td>
  </tr>
  <tr>
   <td class='dispensetd'><?php echo $lang['payments']; ?>:<div style='inline-block; float: right;'><?php echo number_format($totalPaid,2); ?> &euro;</div></td>
  </tr>
  <tr>
   <td class='dispensetd' <?php echo $colourStyle; ?>><?php echo $lang['global-credit']; ?>:<div style='inline-block; float: right;'><?php echo number_format($totCredit,2); ?> &euro;</div></td>
  </tr>
 </table>
 </center>
</div>
</div>

 <br /><br />
	 <table class="default" id="mainTable">
	  <thead>
	   <tr>
	    <th><?php echo $lang['pur-date']; ?></th>
	    <th><?php echo $lang['global-type']; ?></th>
	    <th><?php echo $lang['global-product']; ?></th>
	    <th><?php echo $lang['global-quantity']; ?></th>
	    <th><?php echo $lang['price']; ?></th>
	    <th><?php echo $lang['paid']; ?></th>
	    <th></th>
	    <th></th>
	   </tr>
	  </thead>
	  <tbody>
	  
<?php
$i = 0;
	while ($sale = $resultPurchases->fetch()) {
	
	
		$i++;
	
		$formattedDate = date("d M H:i", strtotime($sale['purchaseDate'] . "+$offsetSec seconds"));
		$purchaseid = $sale['purchaseid'];
		$category = $sale['category'];
		$productid = $sale['productid'];
		$purchaseQuantity = $sale['purchaseQuantity'];
		$purchasePrice = $sale['purchasePrice'];
		$paid = $sale['paid'];
		$type = $sale['type'];
		$price = $sale['purchasePrice'];
		$comment = $sale['comment'];
		$id = $sale['id'];
			
			
		
		if ($comment != '') {
			
			$commentRead = "
			                <img src='images/comments.png' id='comment$i' /><div id='helpBox$i' class='helpBox'>{$comment}</div>
			                <script>
			                  	$('#comment$i').on({
							 		'mouseover' : function() {
									 	$('#helpBox$i').css('display', 'block');
							  		},
							  		'mouseout' : function() {
									 	$('#helpBox$i').css('display', 'none');
								  	}
							  	});
							</script>
			                ";
			
		} else {
			
			$commentRead = "";
			
		}
	
	
		if ($type == 'purchase') {
				
				$selectProduct = "SELECT name FROM b_products WHERE productid = $productid";
				try
				{
					$productResult = $pdo3->prepare("$selectProduct");
					$productResult->execute();
				}
				catch (PDOException $e)
				{
						$error = 'Error fetching user: ' . $e->getMessage();
						echo $error;
						exit();
				}
			
				$row = $productResult->fetch();
					$name = $row['name'];
				
			
			$price = $purchaseQuantity * $purchasePrice;
			
		  	    
			if ($type == 1) {
				
				$provRow = sprintf("
				<tr>
		  	    <td>%s</td>
		  	    <td class='left'>{$lang['global-purchase']}</td>
		  	    <td class='left'>%s</td>
		  	    <td class='right'>%0.02f u.</td>
		  	    <td class='right'>%0.02f &euro;</td>
		  	    <td class='right'>%0.02f &euro;</td>
	   			<td><span class='relativeitem'>$commentRead</span></td>
	   			<td></td>
		  	    </tr>",
		  	    $formattedDate, $name, number_format($purchaseQuantity,2), $price, $paid);
		  	    
	  	    } else {
		  	    
				$provRow = sprintf("
				<tr>
		  	    <td>%s</td>
		  	    <td class='left'>{$lang['global-purchase']}</td>
		  	    <td class='left'>%s</td>
		  	    <td class='right'>%0.02f u.</td>
		  	    <td class='right'>%0.02f &euro;</td>
		  	    <td class='right'>%0.02f &euro;</td>
	   			<td><span class='relativeitem'>$commentRead</span></td>
	   			<td></td>
		  	    </tr>",
		  	    $formattedDate, $name, number_format($purchaseQuantity,2), $price, $paid);
		  	    
	  	    }

	  	    
	
			
		} else if ($type == 'reload') {
			
			$selectProduct = "SELECT productid, category FROM b_purchases WHERE purchaseid = $purchaseid";
			try
			{
				$productResult = $pdo3->prepare("$selectProduct");
				$productResult->execute();
			}
			catch (PDOException $e)
			{
					$error = 'Error fetching user: ' . $e->getMessage();
					echo $error;
					exit();
			}
		
			$row = $productResult->fetch();
				$productid = $row['productid'];
				$category = $row['category'];
			
				
			$selectProduct = "SELECT name FROM b_products WHERE productid = '$productid'";
				try
				{
					$productResult = $pdo3->prepare("$selectProduct");
					$productResult->execute();
				}
				catch (PDOException $e)
				{
						$error = 'Error fetching user: ' . $e->getMessage();
						echo $error;
						exit();
				}
			
				$row = $productResult->fetch();
					$name = $row['name'];

					

				$provRow = sprintf("
				<tr>
		  	    <td>%s</td>
		  	    <td class='left'>{$lang['reload']}</td>
		  	    <td class='left'>%s</td>
		  	    <td class='right'>%0.02f u.</td>
		  	    <td class='right'>%0.02f &euro;</td>
		  	    <td class='right'>%0.02f &euro;</td>
	   			<td><span class='relativeitem'>$commentRead</span></td>
	   			<td></td>
		  	    </tr>",
		  	    $formattedDate, $name, number_format($purchaseQuantity,2), $price, $paid);
		  	    
			
		} else {
			
			$provRow = sprintf("
			<tr class='green'>
	  	    <td>%s</td>
		  	<td class='left'>{$lang['payment']}</td>
	  	    <td class='left'></td>
	  	    <td class='right'></td>
	  	    <td class='right'></td>
	  	    <td class='right'>%0.02f &euro;</td>
	   		<td><span class='relativeitem'>$commentRead</span></td>
	   		<td></td>
	  	    </tr>",
	  	    $formattedDate, $paid);
	  	    
			
		}
		
		echo $provRow;	 
		
	}

	echo "</table>";
	echo "<br><br>";
	// select providers offered products

	$selectProductsOffer = "SELECT products_offers FROM hw_providers WHERE id=".$provider_id;

	try{
		$products_results = $pdo3->prepare("$selectProductsOffer");
		$products_results->execute();
	}
	catch (PDOException $e)
	{
			$error = 'Error fetching user: ' . $e->getMessage();
			echo $error;
			exit();
	}
	$product_row = $products_results->fetch();
		$products_offer = $product_row['products_offers'];
		$products_offer_arr = explode(",", $products_offer);

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
		echo "<form id='productForm' action='' method='POST'>";
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
	    <th>Products to Offer</th>
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

		$checked = "";

		if(in_array($productid, $products_offer_arr)){
			$checked = "checked";
		}
		
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
  	   <td><span class='relativeitem'>$commentRead</sapn></td>
  	   <td class='centered'><div class='fakeboxholder'><label class='control'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input type='checkbox' name='choose_products[]' $checked value='%d'><div class='fakebox'></div></label></div></td>
	  </tr>",
	  $productid . '.' . $photoExt, $productName, $productid
	  );
	  echo $product_row;
  }
  echo "</tbody></table>";
}
  echo "<input type='hidden' value='1' name='submitted' />";
  echo "<input type='hidden' value='$provider_id' name='provider_id' />";
  echo "<center><button class='cta1' type='submit'>Submit</button></center>";
  echo "</form>";

 displayFooter();
