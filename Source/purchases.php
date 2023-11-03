<?php
	
	require_once 'cOnfig/connection.php';
	require_once 'cOnfig/view.php';
	require_once 'cOnfig/authenticate.php';
	require_once 'cOnfig/languages/common.php';
	
	session_start();
	$accessLevel = '3';
	
	// Authenticate & authorize
	authorizeUser($accessLevel);
	
	// Query to look up open products
	$selectOpenPurchases = "SELECT purchaseid, category, productid, purchaseDate, purchasePrice, salesPrice, purchaseQuantity, realQuantity, adminComment, closedAt, estClosing, inMenu FROM purchases WHERE closedAt IS NULL ORDER by purchaseDate DESC";
		try
		{
			$resultOpen = $pdo3->prepare("$selectOpenPurchases");
			$resultOpen->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
			
	// Query to look up closed products
	$selectClosedPurchases = "SELECT purchaseid, category, productid, purchaseDate, purchasePrice, salesPrice, purchaseQuantity, realQuantity, adminComment, closedAt, estClosing, inMenu FROM purchases WHERE closedAt IS NOT NULL ORDER by purchaseDate DESC";
		try
		{
			$resultClosed = $pdo3->prepare("$selectClosedPurchases");
			$resultClosed->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	

	
	$deletePurchaseScript = <<<EOD
function delete_purchase(purchaseid) {
	if (confirm("{$lang['confirm-deleteproduct']}")) {
				window.location = "uTil/delete-purchase.php?purchaseid=" + purchaseid;
				}
}
EOD;
	pageStart("Coste", NULL, $deletePurchaseScript, "ppurchases", "purchases admin", "Coste", $_SESSION['successMessage'], $_SESSION['errorMessage']);
		
?>

<center><a href="new-purchase.php" class="cta"><?php echo $lang['newpurchase']; ?></a></center>




	 <table class="default">
	  <thead>
	   <tr>
	    <th><?php echo $lang['global-category']; ?></th>
	    <th><?php echo $lang['global-product']; ?></th>
	    <th>Color</th>
	    <th><?php echo $lang['title-stock']; ?></th>
	    <th><?php echo $lang['pur-inmenu']; ?></th>
	   </tr>
	  </thead>
	  <tbody>
	  
	  <?php

	  		// Non-closed products
		while ($purchase = $resultOpen->fetch()) {

			$productid = $purchase['productid'];
			$purchaseid = $purchase['purchaseid'];
		
			$closedAt = $purchase['closedAt'];
			
			if ($closedAt == 0) {
				$closeColour = '';
			}
			else if ($closedAt < 0) {
				$closeColour = 'negative';
			} else if ($closedAt > 0) {
				$closeColour = 'positive';
			}
			
			$inMenu = $purchase['inMenu'];
			
			if ($inMenu == 1) {
				$inMenu = $lang['global-yes'];
			} else {
				$inMenu = $lang['global-no'];
			}


	// Determine product type, and assign query variables accordingly
	if ($purchase['category'] == 1) {
		$purchaseCategory = $lang['global-flower'];
		$prodSelect = 'g.name, g.breed2 FROM flower g';
		$prodJoin = 'g.flowerid AND p.category = 1';
	} else if ($purchase['category'] == 2) {
		$purchaseCategory = $lang['global-extract'];
		$prodSelect = 'h.name FROM extract h';
		$prodJoin = 'h.extractid AND p.category = 2';
	} else {
		
		// Query to look for category
		$categoryDetails = "SELECT name FROM categories WHERE id = {$purchase['category']}";
		try
		{
			$result = $pdo3->prepare("$categoryDetails");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
			$purchaseCategory = $row['name'];
		
		$prodSelect = 'pr.name FROM products pr';
		$prodJoin = "pr.productid AND p.category = {$purchase['category']}";
	}		

		$selectProduct = "SELECT {$prodSelect}, purchases p WHERE ({$productid} = {$prodJoin})";
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
			$name = $row['name'];
			$formattedDate = date("d M y", strtotime($purchase['purchaseDate'] . "+$offsetSec seconds"));
			$breed2 = $row['breed2'];
		
		if ($breed2 != '') {
			$name = $name . " x " . $breed2;
		}
		
		// Calculate Stock
		
		$selectSales = "SELECT SUM(quantity) FROM salesdetails WHERE purchaseid = $purchaseid";
		try
		{
			$result = $pdo3->prepare("$selectSales");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
			$sales = $row['SUM(quantity)'];

		$selectPermAdditions = "SELECT SUM(quantity) FROM productmovements WHERE purchaseid = $purchaseid AND type = 1 AND (movementTypeid = 1 OR movementTypeid = 3 OR movementTypeid = 10)";
		try
		{
			$result = $pdo3->prepare("$selectPermAdditions");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
				$permAdditions = $row['SUM(quantity)'];
		
		$selectPermRemovals = "SELECT SUM(quantity) FROM productmovements WHERE purchaseid = $purchaseid AND type = 2 AND (movementTypeid = 4 OR movementTypeid = 7 OR movementTypeid = 8 OR movementTypeid = 9 OR movementTypeid = 11 OR movementTypeid = 13 OR movementTypeid = 14 OR movementTypeid = 15 OR movementTypeid = 16)";
		try
		{
			$result = $pdo3->prepare("$selectPermRemovals");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
				$permRemovals = $row['SUM(quantity)'];
				
		// Calculate what's in Internal stash
		$selectStashedInt = "SELECT SUM(quantity) FROM productmovements WHERE purchaseid = $purchaseid AND type = 2 AND (movementTypeid = 5 OR movementTypeid = 18)";
		try
		{
			$result = $pdo3->prepare("$selectStashedInt");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
				$stashedInt = $row['SUM(quantity)'];
			
		$selectUnStashedInt = "SELECT SUM(quantity) FROM productmovements WHERE purchaseid = $purchaseid AND type = 1 AND (movementTypeid = 12 OR movementTypeid = 17)";
		try
		{
			$result = $pdo3->prepare("$selectUnStashedInt");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
				$unStashedInt = $row['SUM(quantity)'];
	
				
			$inStashInt = $stashedInt - $unStashedInt;
			$inStashInt = $inStashInt;
	
	
		// Calculate what's in External stash
		$selectStashedExt = "SELECT SUM(quantity) FROM productmovements WHERE purchaseid = $purchaseid AND type = 2 AND (movementTypeid = 6 OR movementTypeid = 20)";
		try
		{
			$result = $pdo3->prepare("$selectStashedExt");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
				$stashedExt = $row['SUM(quantity)'];
			
		$selectUnStashedExt = "SELECT SUM(quantity) FROM productmovements WHERE purchaseid = $purchaseid AND type = 1 AND (movementTypeid = 2 OR movementTypeid = 19)";
		try
		{
			$result = $pdo3->prepare("$selectUnStashedExt");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
				$unStashedExt = $row['SUM(quantity)'];

			
		$inStashExt = $stashedExt - $unStashedExt;
		$inStashExt = $inStashExt;
		
		$inStash = $inStashInt + $inStashExt;
		$estStock = $purchase['realQuantity'] + $permAdditions - $sales - $permRemovals - $inStash;
		
		$pprice = $purchase['salesPrice'];
		
		if ($pprice == 50) {
			
			$priceColour = "<img src='images/p-black.png' width='18' /><span style='display:none'>50</span>";
			
		} else if ($pprice == 40) {
			
			$priceColour = "<img src='images/p-darkblue.png' width='18' /><span style='display:none'>40</span>";
			
		} else if ($pprice == 18) {
			
			$priceColour = "<img src='images/p-purple.png' width='18' /><span style='display:none'>18</span>";
			
		} else if ($pprice == 15) {
			
			$priceColour = "<img src='images/p-red.png' width='18' /><span style='display:none'>15</span>";
			
		} else if ($pprice == 12) {
			
			$priceColour = "<img src='images/p-green.png' width='18' /><span style='display:none'>12</span>";
			
		} else if ($pprice == 10) {
			
			$priceColour = "<img src='images/p-orange.png' width='18' /><span style='display:none'>10</span>";
			
		} else if ($pprice == 9) {
			
			$priceColour = "<img src='images/p-yellow.png' width='18' /><span style='display:none'>9</span>";
			
		} else if ($pprice == 8) {
			
			$priceColour = "<img src='images/p-lightblue.png' width='18' /><span style='display:none'>8</span>";
			
		} else if ($pprice == 6) {
			
			$priceColour = "<img src='images/p-grey.png' width='18' /><span style='display:none'>6</span>";
			
		} else if ($pprice == 1) {
			
			$priceColour = "<img src='images/p-white.png' width='18' /><span style='display:none'>1</span>";
			
		} else {
			
			$priceColour = $pprice;
			
		}

		
	if ($purchase['category'] < 3) {
		
	$purchase_row =	sprintf("
  	  <tr>
  	   <td class='clickableRow' href='purchase.php?purchaseid=%d'>%s</td>
  	   <td class='clickableRow' href='purchase.php?purchaseid=%d'>%s</td>
  	   <td class='clickableRow right' href='purchase.php?purchaseid=%d'>%s<span class='smallerfont3'></span></td>
  	   <td class='clickableRow right' href='purchase.php?purchaseid=%d'>%0.0f g</td>
  	   <td class='clickableRow' style='text-align: center' href='purchase.php?purchaseid=%d'>%s</td>
	  </tr>",
	  $purchase['purchaseid'], $purchaseCategory, $purchase['purchaseid'], $name, $purchase['purchaseid'], $priceColour, $purchase['purchaseid'], $estStock, $purchase['purchaseid'], $inMenu, $purchase['purchaseid']);
	  
  	} else {

	$purchase_row =	sprintf("
  	  <tr>
  	   <td class='clickableRow' href='purchase.php?purchaseid=%d'>%s</td>
  	   <td class='clickableRow' href='purchase.php?purchaseid=%d'>%s</td>
  	   <td class='clickableRow right' href='purchase.php?purchaseid=%d'>%s<span class='smallerfont3'></span></td>
  	   <td class='clickableRow right' href='purchase.php?purchaseid=%d'>%0.0f u</td>
  	   <td class='clickableRow' style='text-align: center' href='purchase.php?purchaseid=%d'>%s</td>
	  </tr>",
	  $purchase['purchaseid'], $purchaseCategory, $purchase['purchaseid'], $name, $purchase['purchaseid'], $priceColour, $purchase['purchaseid'], $estStock, $purchase['purchaseid'], $inMenu, $purchase['purchaseid']);
	  
  	}
  	
	  echo $purchase_row;
	  
	  
	  
	  
	  
	  
}
	  
	  		// Closed products  		
		while ($purchase = $resultClosed->fetch()) {

			$productid = $purchase['productid'];
		
			$closedAt = $purchase['closedAt'];
			
			if ($closedAt == 0) {
				$closeColour = '';
			}
			else if ($closedAt < 0) {
				$closeColour = 'negative';
			} else if ($closedAt > 0) {
				$closeColour = 'positive';
			}
			
			$inMenu = $purchase['inMenu'];
			
			if ($inMenu == 1) {
				$inMenu = 'Yes';
			} else {
				$inMenu = 'No';
			}


	// Determine product type, and assign query variables accordingly
	if ($purchase['category'] == 1) {
		$purchaseCategory = 'Flower';
		$prodSelect = 'g.name, g.breed2 FROM flower g';
		$prodJoin = 'g.flowerid AND p.category = 1';
	} else if ($purchase['category'] == 2) {
		$purchaseCategory = 'Extract';
		$prodSelect = 'h.name FROM extract h';
		$prodJoin = 'h.extractid AND p.category = 2';
	} else {
		// Query to look for category
		$categoryDetails = "SELECT name FROM categories WHERE id = {$purchase['category']}";
		try
		{
			$result = $pdo3->prepare("$categoryDetails");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
			$purchaseCategory = $row['name'];
		
		$prodSelect = 'pr.name FROM products pr';
		$prodJoin = "pr.productid AND p.category = {$purchase['category']}";
	}

		$selectProduct = "SELECT {$prodSelect}, purchases p WHERE ({$productid} = {$prodJoin})";
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
		$name = $row['name'];
		$formattedDate = date("d M y", strtotime($purchase['purchaseDate'] . "+$offsetSec seconds"));
		$breed2 = $row['breed2'];
		
		if ($breed2 != '') {
			$name = $name . " x " . $breed2;
		}
		
		$pprice = $purchase['salesPrice'];
		
		if ($pprice == 50) {
			
			$priceColour = "<img src='images/p-black.png' width='18' /><span style='display:none'>50</span>";
			
		} else if ($pprice == 40) {
			
			$priceColour = "<img src='images/p-darkblue.png' width='18' /><span style='display:none'>40</span>";
			
		} else if ($pprice == 18) {
			
			$priceColour = "<img src='images/p-purple.png' width='18' /><span style='display:none'>18</span>";
			
		} else if ($pprice == 15) {
			
			$priceColour = "<img src='images/p-red.png' width='18' /><span style='display:none'>15</span>";
			
		} else if ($pprice == 12) {
			
			$priceColour = "<img src='images/p-green.png' width='18' /><span style='display:none'>12</span>";
			
		} else if ($pprice == 10) {
			
			$priceColour = "<img src='images/p-orange.png' width='18' /><span style='display:none'>10</span>";
			
		} else if ($pprice == 9) {
			
			$priceColour = "<img src='images/p-yellow.png' width='18' /><span style='display:none'>9</span>";
			
		} else if ($pprice == 8) {
			
			$priceColour = "<img src='images/p-lightblue.png' width='18' /><span style='display:none'>8</span>";
			
		} else if ($pprice == 6) {
			
			$priceColour = "<img src='images/p-grey.png' width='18' /><span style='display:none'>6</span>";
			
		} else if ($pprice == 1) {
			
			$priceColour = "<img src='images/p-white.png' width='18' /><span style='display:none'>1</span>";
			
		} else {
			
			$priceColour = $pprice;
			
		}
		
	  		// Non-closed products
	  		
		if ($purchase['category'] < 3) {
	  		
			$purchase_row =	sprintf("
		  	  <tr class='closedProduct'>
		  	   <td class='clickableRow' href='purchase.php?purchaseid=%d'>%s</td>
		  	   <td class='clickableRow' href='purchase.php?purchaseid=%d'>%s</td>
  	 		   <td class='clickableRow right' href='purchase.php?purchaseid=%d'>%s<span class='smallerfont3'></span></td>
		  	   <td></td>
		  	   <td class='clickableRow' style='text-align: center' href='purchase.php?purchaseid=%d'>%s</td>
			  </tr>",
			  $purchase['purchaseid'], $purchaseCategory, $purchase['purchaseid'], $name, $purchase['purchaseid'], $priceColour, $purchase['purchaseid'], $inMenu, $purchase['purchaseid'], $closeColour, $closedAt);
			  
		  } else {
	  
			$purchase_row =	sprintf("
		  	  <tr class='closedProduct'>
		  	   <td class='clickableRow' href='purchase.php?purchaseid=%d'>%s</td>
		  	   <td class='clickableRow' href='purchase.php?purchaseid=%d'>%s</td>
  	  		   <td class='clickableRow right' href='purchase.php?purchaseid=%d'>%s<span class='smallerfont3'></span></td>
		  	   <td></td>
		  	   <td class='clickableRow' style='text-align: center' href='purchase.php?purchaseid=%d'>%s</td>
			  </tr>",
			  $purchase['purchaseid'], $purchaseCategory, $purchase['purchaseid'], $name, $purchase['purchaseid'], $priceColour, $purchase['purchaseid'], $inMenu, $purchase['purchaseid'], $closeColour, $closedAt);
			  
		  }

	  echo $purchase_row;
	  
	  
	  
	  
	  
	  
  }
		
?>

	 </tbody>
	 </table>
	 
<?php  displayFooter(); ?>
