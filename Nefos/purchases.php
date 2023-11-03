<?php
	
	require_once 'cOnfig/connection.php';
	require_once 'cOnfig/view.php';
	require_once 'cOnfig/authenticate.php';
	require_once 'cOnfig/languages/common.php';
	
	session_start();
	$accessLevel = '3';
	
	// Authenticate & authorize
	authorizeUser($accessLevel);
	
	pageStart($lang['purchases'], NULL, NULL, "ppurchases", "purchases admin", $lang['purchasescaps'], $_SESSION['successMessage'], $_SESSION['errorMessage']);
	
	// OPEN PRODUCTS FIRST
	
	echo "<center><a href='new-purchase.php' class='cta'>{$lang['newpurchase']}</a></center><br /><h3 class='title2'>{$lang['open-products']}</h3><br />";

	
	// Query to look up open products, flowers first
	$selectOpenPurchases = "SELECT purchaseid, category, productid, purchaseDate, purchasePrice, salesPrice, purchaseQuantity, realQuantity, adminComment, inMenu FROM purchases WHERE closedAt IS NULL AND category = 1 ORDER by purchaseDate DESC";

	$resultOpen = mysql_query($selectOpenPurchases)
		or handleError($lang['error-loadpurchases'],"Error loading purchase from db: " . mysql_error());
		
	if (mysql_num_rows($resultOpen) > 0) {

		

						
?>


<h3 class='title'><?php echo $lang['global-flowerscaps']; ?></h3>

	 <table class="default">
	  <thead>
	   <tr>
	    <th><?php echo $lang['pur-date']; ?></th>
	    <th><?php echo $lang['global-product']; ?></th>
	    <th><?php echo $lang['global-quantity']; ?></th>
	    <th><?php echo $lang['add-realweightshort']; ?></th>
	    <th><?php echo $lang['purchase']; ?></th>
	    <th><?php echo $lang['pur-quota']; ?></th>
	    <th><?php echo $lang['global-dispense']; ?></th>
	    <th><?php echo $lang['title-stock']; ?></th>
	    <th>Almacén</th>
	    <th><?php echo $lang['pur-inmenu']; ?></th>
	    <th></th>
	   </tr>
	  </thead>
	  <tbody>

<?php

while ($purchase = mysql_fetch_array($resultOpen)) {
	
			$productid = $purchase['productid'];
			$purchaseid = $purchase['purchaseid'];
		
			$inMenu = $purchase['inMenu'];
			
			if ($inMenu == 1) {
				$inMenu = $lang['global-yes'];
			} else {
				$inMenu = $lang['global-no'];
			}
			
	if ($purchase['adminComment'] != '') {
		
		$commentRead = "
		                <img src='images/comments.png' id='comment$purchaseid' /><div id='helpBox$purchaseid' class='helpBox'>{$purchase['adminComment']}</div>
		                <script>
		                  	$('#comment$purchaseid').on({
						 		'mouseover' : function() {
								 	$('#helpBox$purchaseid').css('display', 'block');
						  		},
						  		'mouseout' : function() {
								 	$('#helpBox$purchaseid').css('display', 'none');
							  	}
						  	});
						</script>
		                ";
		
	} else {
		
		$commentRead = "";
		
	}		

		$selectProduct = "SELECT name, breed2 FROM flower WHERE $productid = flowerid";
		
		$productResult = mysql_query($selectProduct)
			or handleError($lang['error-loadflowerdata'],"Error loading flower: " . mysql_error());
			
	    $row = mysql_fetch_array($productResult);
			$name = $row['name'];
			$formattedDate = date("d M y", strtotime($purchase['purchaseDate'] . "+$offsetSec seconds"));
			$breed2 = $row['breed2'];
		
		if ($breed2 != '') {
			$name = $name . " x " . $breed2;
		}
		
		// Calculate Stock
		$selectSales = "SELECT SUM(quantity) FROM salesdetails WHERE purchaseid = $purchaseid";
	
		$sale = mysql_query($selectSales)
			or handleError($lang['error-expensesload'],"Error loading expense from db: " . mysql_error());
		
		$row = mysql_fetch_array($sale);
			$sales = $row['SUM(quantity)'];

		$selectPermAdditions = "SELECT SUM(quantity) FROM productmovements WHERE purchaseid = $purchaseid AND type = 1 AND (movementTypeid = 1 OR movementTypeid = 3 OR movementTypeid = 10)";
		$permAdditions = mysql_query($selectPermAdditions)
			or handleError($lang['error-loadprodadditions'],"Error loading expense from db: " . mysql_error());
			
			$row = mysql_fetch_array($permAdditions);
				$permAdditions = $row['SUM(quantity)'];
		
		$selectPermRemovals = "SELECT SUM(quantity) FROM productmovements WHERE purchaseid = $purchaseid AND type = 2 AND (movementTypeid = 4 OR movementTypeid = 7 OR movementTypeid = 8 OR movementTypeid = 9 OR movementTypeid = 11 OR movementTypeid = 13 OR movementTypeid = 14 OR movementTypeid = 15 OR movementTypeid = 16)";
		
		$permRemovals = mysql_query($selectPermRemovals)
			or handleError($lang['error-loadprodadditions'],"Error loading expense from db: " . mysql_error());
			
			$row = mysql_fetch_array($permRemovals);
				$permRemovals = $row['SUM(quantity)'];
				
		// Calculate what's in Internal stash
		$selectStashedInt = "SELECT SUM(quantity) FROM productmovements WHERE purchaseid = $purchaseid AND type = 2 AND (movementTypeid = 5 OR movementTypeid = 18)";
		$stashedInt = mysql_query($selectStashedInt)
			or handleError($lang['error-loadstash'],"Error loading stash details from db: " . mysql_error());
		
			$row = mysql_fetch_array($stashedInt);
				$stashedInt = $row['SUM(quantity)'];
			
		$selectUnStashedInt = "SELECT SUM(quantity) FROM productmovements WHERE purchaseid = $purchaseid AND type = 1 AND (movementTypeid = 12 OR movementTypeid = 17)";
		$unStashedInt = mysql_query($selectUnStashedInt)
			or handleError($lang['error-loadstash'],"Error loading stash details from db: " . mysql_error());
		
			$row = mysql_fetch_array($unStashedInt);
				$unStashedInt = $row['SUM(quantity)'];
	
				
			$inStashInt = $stashedInt - $unStashedInt;
			$inStashInt = $inStashInt;
	
	
		// Calculate what's in External stash
		$selectStashedExt = "SELECT SUM(quantity) FROM productmovements WHERE purchaseid = $purchaseid AND type = 2 AND (movementTypeid = 6 OR movementTypeid = 20)";
		$stashedExt = mysql_query($selectStashedExt)
			or handleError($lang['error-loadstash'],"Error loading stash details from db: " . mysql_error());
		
			$row = mysql_fetch_array($stashedExt);
				$stashedExt = $row['SUM(quantity)'];
			
		$selectUnStashedExt = "SELECT SUM(quantity) FROM productmovements WHERE purchaseid = $purchaseid AND type = 1 AND (movementTypeid = 2 OR movementTypeid = 19)";
		$unStashedExt = mysql_query($selectUnStashedExt)
			or handleError($lang['error-loadstash'],"Error loading stash details from db: " . mysql_error());
		
			$row = mysql_fetch_array($unStashedExt);
				$unStashedExt = $row['SUM(quantity)'];

			
		$inStashExt = $stashedExt - $unStashedExt;
		$inStashExt = $inStashExt;
		
		$inStash = $inStashInt + $inStashExt;
		$estStock = $purchase['realQuantity'] + $permAdditions - $sales - $permRemovals - $inStash;

	$purchase_row =	sprintf("
  	  <tr>
  	   <td class='clickableRow' href='purchase.php?purchaseid=%d'>%s</td>
  	   <td class='clickableRow' href='purchase.php?purchaseid=%d'>%s</td>
  	   <td class='clickableRow right' href='purchase.php?purchaseid=%d'>%0.0f g</td>
  	   <td class='clickableRow right' href='purchase.php?purchaseid=%d'>%0.1f g</td>
  	   <td class='clickableRow right' href='purchase.php?purchaseid=%d'>%0.2f<span class='smallerfont3'> &euro; /g</span></td>
  	   <td class='clickableRow right' href='purchase.php?purchaseid=%d'>%0.2f<span class='smallerfont3'> &euro; /g</span></td>
  	   <td class='clickableRow right' href='purchase.php?purchaseid=%d'>%0.2f<span class='smallerfont3'> &euro; /g</span></td>
  	   <td class='clickableRow right' href='purchase.php?purchaseid=%d'>%0.0f g</td>
  	   <td class='clickableRow right' href='purchase.php?purchaseid=%d'>%0.0f g</td>
  	   <td class='clickableRow' style='text-align: center' href='purchase.php?purchaseid=%d'>%s</td>
	   <td class='relative'>$commentRead</td>
	  </tr>",
	  $purchase['purchaseid'], $formattedDate, $purchase['purchaseid'], $name, $purchase['purchaseid'], $purchase['purchaseQuantity'], $purchase['purchaseid'], $purchase['realQuantity'], $purchase['purchaseid'], $purchase['purchasePrice'], $purchase['purchaseid'], $purchase['salesPrice'] - $purchase['purchasePrice'], $purchase['purchaseid'], $purchase['salesPrice'], $purchase['purchaseid'], $estStock, $purchase['purchaseid'], $inStash, $purchase['purchaseid'], $inMenu);
	    	
	  echo $purchase_row;
	  
	}
}

?>

	 </tbody>
	 </table>
	 
<?php




	// Query to look up open products, extracts next
	$selectOpenPurchases = "SELECT purchaseid, category, productid, purchaseDate, purchasePrice, salesPrice, purchaseQuantity, realQuantity, adminComment, inMenu FROM purchases WHERE closedAt IS NULL AND category = 2 ORDER by purchaseDate DESC";

	$resultOpen = mysql_query($selectOpenPurchases)
		or handleError($lang['error-loadpurchases'],"Error loading purchase from db: " . mysql_error());
		
	if (mysql_num_rows($resultOpen) > 0) {

		

						
?>
<br /><br />
<h3 class='title'><?php echo $lang['global-extractscaps']; ?></h3>

	 <table class="default">
	  <thead>
	   <tr>
	    <th><?php echo $lang['pur-date']; ?></th>
	    <th><?php echo $lang['global-product']; ?></th>
	    <th><?php echo $lang['global-quantity']; ?></th>
	    <th><?php echo $lang['add-realweightshort']; ?></th>
	    <th><?php echo $lang['purchase']; ?></th>
	    <th><?php echo $lang['pur-quota']; ?></th>
	    <th><?php echo $lang['global-dispense']; ?></th>
	    <th><?php echo $lang['title-stock']; ?></th>
	    <th>Almacén</th>
	    <th><?php echo $lang['pur-inmenu']; ?></th>
	    <th></th>
	   </tr>
	  </thead>
	  <tbody>

<?php

while ($purchase = mysql_fetch_array($resultOpen)) {
	
			$productid = $purchase['productid'];
			$purchaseid = $purchase['purchaseid'];
			
			$inMenu = $purchase['inMenu'];
			
			if ($inMenu == 1) {
				$inMenu = $lang['global-yes'];
			} else {
				$inMenu = $lang['global-no'];
			}
			
	if ($purchase['adminComment'] != '') {
		
		$commentRead = "
		                <img src='images/comments.png' id='comment$purchaseid' /><div id='helpBox$purchaseid' class='helpBox'>{$purchase['adminComment']}</div>
		                <script>
		                  	$('#comment$purchaseid').on({
						 		'mouseover' : function() {
								 	$('#helpBox$purchaseid').css('display', 'block');
						  		},
						  		'mouseout' : function() {
								 	$('#helpBox$purchaseid').css('display', 'none');
							  	}
						  	});
						</script>
		                ";
		
	} else {
		
		$commentRead = "";
		
	}		

		$selectProduct = "SELECT name FROM extract WHERE $productid = extractid";
		
		$productResult = mysql_query($selectProduct)
			or handleError($lang['error-loadflowerdata'],"Error loading flower: " . mysql_error());
			
	    $row = mysql_fetch_array($productResult);
			$name = $row['name'];
			$formattedDate = date("d M y", strtotime($purchase['purchaseDate'] . "+$offsetSec seconds"));
		
		// Calculate Stock
		$selectSales = "SELECT SUM(quantity) FROM salesdetails WHERE purchaseid = $purchaseid";
	
		$sale = mysql_query($selectSales)
			or handleError($lang['error-expensesload'],"Error loading expense from db: " . mysql_error());
		
		$row = mysql_fetch_array($sale);
			$sales = $row['SUM(quantity)'];

		$selectPermAdditions = "SELECT SUM(quantity) FROM productmovements WHERE purchaseid = $purchaseid AND type = 1 AND (movementTypeid = 1 OR movementTypeid = 3 OR movementTypeid = 10)";
		$permAdditions = mysql_query($selectPermAdditions)
			or handleError($lang['error-loadprodadditions'],"Error loading expense from db: " . mysql_error());
			
			$row = mysql_fetch_array($permAdditions);
				$permAdditions = $row['SUM(quantity)'];
		
		$selectPermRemovals = "SELECT SUM(quantity) FROM productmovements WHERE purchaseid = $purchaseid AND type = 2 AND (movementTypeid = 4 OR movementTypeid = 7 OR movementTypeid = 8 OR movementTypeid = 9 OR movementTypeid = 11 OR movementTypeid = 13 OR movementTypeid = 14 OR movementTypeid = 15 OR movementTypeid = 16)";
		
		$permRemovals = mysql_query($selectPermRemovals)
			or handleError($lang['error-loadprodadditions'],"Error loading expense from db: " . mysql_error());
			
			$row = mysql_fetch_array($permRemovals);
				$permRemovals = $row['SUM(quantity)'];
				
		// Calculate what's in Internal stash
		$selectStashedInt = "SELECT SUM(quantity) FROM productmovements WHERE purchaseid = $purchaseid AND type = 2 AND (movementTypeid = 5 OR movementTypeid = 18)";
		$stashedInt = mysql_query($selectStashedInt)
			or handleError($lang['error-loadstash'],"Error loading stash details from db: " . mysql_error());
		
			$row = mysql_fetch_array($stashedInt);
				$stashedInt = $row['SUM(quantity)'];
			
		$selectUnStashedInt = "SELECT SUM(quantity) FROM productmovements WHERE purchaseid = $purchaseid AND type = 1 AND (movementTypeid = 12 OR movementTypeid = 17)";
		$unStashedInt = mysql_query($selectUnStashedInt)
			or handleError($lang['error-loadstash'],"Error loading stash details from db: " . mysql_error());
		
			$row = mysql_fetch_array($unStashedInt);
				$unStashedInt = $row['SUM(quantity)'];
	
				
			$inStashInt = $stashedInt - $unStashedInt;
			$inStashInt = $inStashInt;
	
	
		// Calculate what's in External stash
		$selectStashedExt = "SELECT SUM(quantity) FROM productmovements WHERE purchaseid = $purchaseid AND type = 2 AND (movementTypeid = 6 OR movementTypeid = 20)";
		$stashedExt = mysql_query($selectStashedExt)
			or handleError($lang['error-loadstash'],"Error loading stash details from db: " . mysql_error());
		
			$row = mysql_fetch_array($stashedExt);
				$stashedExt = $row['SUM(quantity)'];
			
		$selectUnStashedExt = "SELECT SUM(quantity) FROM productmovements WHERE purchaseid = $purchaseid AND type = 1 AND (movementTypeid = 2 OR movementTypeid = 19)";
		$unStashedExt = mysql_query($selectUnStashedExt)
			or handleError($lang['error-loadstash'],"Error loading stash details from db: " . mysql_error());
		
			$row = mysql_fetch_array($unStashedExt);
				$unStashedExt = $row['SUM(quantity)'];

			
		$inStashExt = $stashedExt - $unStashedExt;
		$inStashExt = $inStashExt;
		
		$inStash = $inStashInt + $inStashExt;
		$estStock = $purchase['realQuantity'] + $permAdditions - $sales - $permRemovals - $inStash;

	$purchase_row =	sprintf("
  	  <tr>
  	   <td class='clickableRow' href='purchase.php?purchaseid=%d'>%s</td>
  	   <td class='clickableRow' href='purchase.php?purchaseid=%d'>%s</td>
  	   <td class='clickableRow right' href='purchase.php?purchaseid=%d'>%0.0f g</td>
  	   <td class='clickableRow right' href='purchase.php?purchaseid=%d'>%0.1f g</td>
  	   <td class='clickableRow right' href='purchase.php?purchaseid=%d'>%0.2f<span class='smallerfont3'> &euro; /g</span></td>
  	   <td class='clickableRow right' href='purchase.php?purchaseid=%d'>%0.2f<span class='smallerfont3'> &euro; /g</span></td>
  	   <td class='clickableRow right' href='purchase.php?purchaseid=%d'>%0.2f<span class='smallerfont3'> &euro; /g</span></td>
  	   <td class='clickableRow right' href='purchase.php?purchaseid=%d'>%0.0f g</td>
  	   <td class='clickableRow right' href='purchase.php?purchaseid=%d'>%0.0f g</td>
  	   <td class='clickableRow' style='text-align: center' href='purchase.php?purchaseid=%d'>%s</td>
	   <td class='relative'>$commentRead</td>
	  </tr>",
	  $purchase['purchaseid'], $formattedDate, $purchase['purchaseid'], $name, $purchase['purchaseid'], $purchase['purchaseQuantity'], $purchase['purchaseid'], $purchase['realQuantity'], $purchase['purchaseid'], $purchase['purchasePrice'], $purchase['purchaseid'], $purchase['salesPrice'] - $purchase['purchasePrice'], $purchase['purchaseid'], $purchase['salesPrice'], $purchase['purchaseid'], $estStock, $purchase['purchaseid'], $inStash, $purchase['purchaseid'], $inMenu);
	    	
	  echo $purchase_row;
	  
	}
}
		
		
?>

	 </tbody>
	 </table>
	 
<?php
		
		// Query to look up categories, then products in each category
		$selectCats = "SELECT id, name, description from categories ORDER by id ASC";
	
		$resultCats = mysql_query($selectCats)
			or handleError($lang['error-loadflowers'],"Error loading flower from db: " . mysql_error());
	
		while ($category = mysql_fetch_array($resultCats)) {
			
			$categoryname = $category['name'];
			$categoryid = $category['id'];
			
			
	// Query to look up open products, extracts next
	$selectOpenPurchases = "SELECT purchaseid, category, productid, purchaseDate, purchasePrice, salesPrice, purchaseQuantity, realQuantity, adminComment, inMenu FROM purchases WHERE closedAt IS NULL AND category = $categoryid ORDER by purchaseDate DESC";

	$resultOpen = mysql_query($selectOpenPurchases)
		or handleError($lang['error-loadpurchases'],"Error loading purchase from db: " . mysql_error());
		
	if (mysql_num_rows($resultOpen) > 0) {

		

						
?>
<br /><br />
<h3 class='title'><?php echo $categoryname; ?></h3>

	 <table class="default">
	  <thead>
	   <tr>
	    <th><?php echo $lang['pur-date']; ?></th>
	    <th><?php echo $lang['global-product']; ?></th>
	    <th><?php echo $lang['global-quantity']; ?></th>
	    <th><?php echo $lang['add-realweightshort']; ?></th>
	    <th><?php echo $lang['purchase']; ?></th>
	    <th><?php echo $lang['pur-quota']; ?></th>
	    <th><?php echo $lang['global-dispense']; ?></th>
	    <th><?php echo $lang['title-stock']; ?></th>
	    <th>Almacén</th>
	    <th><?php echo $lang['pur-inmenu']; ?></th>
	    <th></th>
	   </tr>
	  </thead>
	  <tbody>

<?php

while ($purchase = mysql_fetch_array($resultOpen)) {
	
			$productid = $purchase['productid'];
			$purchaseid = $purchase['purchaseid'];
			
			$inMenu = $purchase['inMenu'];
			
			if ($inMenu == 1) {
				$inMenu = $lang['global-yes'];
			} else {
				$inMenu = $lang['global-no'];
			}
			
	if ($purchase['adminComment'] != '') {
		
		$commentRead = "
		                <img src='images/comments.png' id='comment$purchaseid' /><div id='helpBox$purchaseid' class='helpBox'>{$purchase['adminComment']}</div>
		                <script>
		                  	$('#comment$purchaseid').on({
						 		'mouseover' : function() {
								 	$('#helpBox$purchaseid').css('display', 'block');
						  		},
						  		'mouseout' : function() {
								 	$('#helpBox$purchaseid').css('display', 'none');
							  	}
						  	});
						</script>
		                ";
		
	} else {
		
		$commentRead = "";
		
	}		

		$selectProduct = "SELECT name FROM products WHERE $productid = productid";
		
		$productResult = mysql_query($selectProduct)
			or handleError($lang['error-loadflowerdata'],"Error loading flower: " . mysql_error());
			
	    $row = mysql_fetch_array($productResult);
			$name = $row['name'];
			$formattedDate = date("d M y", strtotime($purchase['purchaseDate'] . "+$offsetSec seconds"));
		
		// Calculate Stock
		$selectSales = "SELECT SUM(quantity) FROM salesdetails WHERE purchaseid = $purchaseid";
	
		$sale = mysql_query($selectSales)
			or handleError($lang['error-expensesload'],"Error loading expense from db: " . mysql_error());
		
		$row = mysql_fetch_array($sale);
			$sales = $row['SUM(quantity)'];

		$selectPermAdditions = "SELECT SUM(quantity) FROM productmovements WHERE purchaseid = $purchaseid AND type = 1 AND (movementTypeid = 1 OR movementTypeid = 3 OR movementTypeid = 10)";
		$permAdditions = mysql_query($selectPermAdditions)
			or handleError($lang['error-loadprodadditions'],"Error loading expense from db: " . mysql_error());
			
			$row = mysql_fetch_array($permAdditions);
				$permAdditions = $row['SUM(quantity)'];
		
		$selectPermRemovals = "SELECT SUM(quantity) FROM productmovements WHERE purchaseid = $purchaseid AND type = 2 AND (movementTypeid = 4 OR movementTypeid = 7 OR movementTypeid = 8 OR movementTypeid = 9 OR movementTypeid = 11 OR movementTypeid = 13 OR movementTypeid = 14 OR movementTypeid = 15 OR movementTypeid = 16)";
		
		$permRemovals = mysql_query($selectPermRemovals)
			or handleError($lang['error-loadprodadditions'],"Error loading expense from db: " . mysql_error());
			
			$row = mysql_fetch_array($permRemovals);
				$permRemovals = $row['SUM(quantity)'];
				
		// Calculate what's in Internal stash
		$selectStashedInt = "SELECT SUM(quantity) FROM productmovements WHERE purchaseid = $purchaseid AND type = 2 AND (movementTypeid = 5 OR movementTypeid = 18)";
		$stashedInt = mysql_query($selectStashedInt)
			or handleError($lang['error-loadstash'],"Error loading stash details from db: " . mysql_error());
		
			$row = mysql_fetch_array($stashedInt);
				$stashedInt = $row['SUM(quantity)'];
			
		$selectUnStashedInt = "SELECT SUM(quantity) FROM productmovements WHERE purchaseid = $purchaseid AND type = 1 AND (movementTypeid = 12 OR movementTypeid = 17)";
		$unStashedInt = mysql_query($selectUnStashedInt)
			or handleError($lang['error-loadstash'],"Error loading stash details from db: " . mysql_error());
		
			$row = mysql_fetch_array($unStashedInt);
				$unStashedInt = $row['SUM(quantity)'];
	
				
			$inStashInt = $stashedInt - $unStashedInt;
			$inStashInt = $inStashInt;
	
	
		// Calculate what's in External stash
		$selectStashedExt = "SELECT SUM(quantity) FROM productmovements WHERE purchaseid = $purchaseid AND type = 2 AND (movementTypeid = 6 OR movementTypeid = 20)";
		$stashedExt = mysql_query($selectStashedExt)
			or handleError($lang['error-loadstash'],"Error loading stash details from db: " . mysql_error());
		
			$row = mysql_fetch_array($stashedExt);
				$stashedExt = $row['SUM(quantity)'];
			
		$selectUnStashedExt = "SELECT SUM(quantity) FROM productmovements WHERE purchaseid = $purchaseid AND type = 1 AND (movementTypeid = 2 OR movementTypeid = 19)";
		$unStashedExt = mysql_query($selectUnStashedExt)
			or handleError($lang['error-loadstash'],"Error loading stash details from db: " . mysql_error());
		
			$row = mysql_fetch_array($unStashedExt);
				$unStashedExt = $row['SUM(quantity)'];

			
		$inStashExt = $stashedExt - $unStashedExt;
		$inStashExt = $inStashExt;
		
		$inStash = $inStashInt + $inStashExt;
		$estStock = $purchase['realQuantity'] + $permAdditions - $sales - $permRemovals - $inStash;

	$purchase_row =	sprintf("
  	  <tr>
  	   <td class='clickableRow' href='purchase.php?purchaseid=%d'>%s</td>
  	   <td class='clickableRow' href='purchase.php?purchaseid=%d'>%s</td>
  	   <td class='clickableRow right' href='purchase.php?purchaseid=%d'>%0.0f g</td>
  	   <td class='clickableRow right' href='purchase.php?purchaseid=%d'>%0.1f g</td>
  	   <td class='clickableRow right' href='purchase.php?purchaseid=%d'>%0.2f<span class='smallerfont3'> &euro; /g</span></td>
  	   <td class='clickableRow right' href='purchase.php?purchaseid=%d'>%0.2f<span class='smallerfont3'> &euro; /g</span></td>
  	   <td class='clickableRow right' href='purchase.php?purchaseid=%d'>%0.2f<span class='smallerfont3'> &euro; /g</span></td>
  	   <td class='clickableRow right' href='purchase.php?purchaseid=%d'>%0.0f g</td>
  	   <td class='clickableRow right' href='purchase.php?purchaseid=%d'>%0.0f g</td>
  	   <td class='clickableRow' style='text-align: center' href='purchase.php?purchaseid=%d'>%s</td>
	   <td class='relative'>$commentRead</td>
	  </tr>",
	  $purchase['purchaseid'], $formattedDate, $purchase['purchaseid'], $name, $purchase['purchaseid'], $purchase['purchaseQuantity'], $purchase['purchaseid'], $purchase['realQuantity'], $purchase['purchaseid'], $purchase['purchasePrice'], $purchase['purchaseid'], $purchase['salesPrice'] - $purchase['purchasePrice'], $purchase['purchaseid'], $purchase['salesPrice'], $purchase['purchaseid'], $estStock, $purchase['purchaseid'], $inStash, $purchase['purchaseid'], $inMenu);
	    	
	  echo $purchase_row;
	  
	}
}			
			
?>

	 </tbody>
	 </table>
	 
<?php
		}
	

		
		
		
		
		
		
		
		
		
		
		
	// CLOSED PRODUCTS NEXT
	
	// Query to look up closed products, flowers first
	$selectOpenPurchases = "SELECT purchaseid, category, productid, purchaseDate, purchasePrice, salesPrice, purchaseQuantity, realQuantity, adminComment, closedAt, estClosing, inMenu FROM purchases WHERE closedAt IS NOT NULL AND category = 1 ORDER by purchaseDate DESC";

	$resultOpen = mysql_query($selectOpenPurchases)
		or handleError($lang['error-loadpurchases'],"Error loading purchase from db: " . mysql_error());
		
	if (mysql_num_rows($resultOpen) > 0) {

		

						
?>

<br /><br /><br />
<h3 class='title2'><?php echo $lang['closed-products']; ?></h3><br />

<h3 class='title'><?php echo $lang['global-flowerscaps']; ?></h3>

	 <table class="default">
	  <thead>
	   <tr>
	    <th><?php echo $lang['pur-date']; ?></th>
	    <th><?php echo $lang['global-product']; ?></th>
	    <th><?php echo $lang['global-quantity']; ?></th>
	    <th><?php echo $lang['add-realweightshort']; ?></th>
	    <th><?php echo $lang['purchase']; ?></th>
	    <th><?php echo $lang['pur-quota']; ?></th>
	    <th><?php echo $lang['global-dispense']; ?></th>
	    <th><?php echo $lang['title-stock']; ?></th>
	    <th>Almacén</th>
	    <th><?php echo $lang['pur-inmenu']; ?></th>
	    <th></th>
	    <th><?php echo $lang['pur-closedat']; ?></th>
	   </tr>
	  </thead>
	  <tbody>

<?php

while ($purchase = mysql_fetch_array($resultOpen)) {
	
			$productid = $purchase['productid'];
			$purchaseid = $purchase['purchaseid'];
		
			$inMenu = $purchase['inMenu'];
			
			$closedAt = $purchase['closedAt'];
			
			if ($closedAt == 0) {
				$closeColour = '';
			}
			else if ($closedAt < 0) {
				$closeColour = 'negative';
			} else if ($closedAt > 0) {
				$closeColour = 'positive';
			}
			
			if ($inMenu == 1) {
				$inMenu = $lang['global-yes'];
			} else {
				$inMenu = $lang['global-no'];
			}
			
	if ($purchase['adminComment'] != '') {
		
		$commentRead = "
		                <img src='images/comments.png' id='comment$purchaseid' /><div id='helpBox$purchaseid' class='helpBox'>{$purchase['adminComment']}</div>
		                <script>
		                  	$('#comment$purchaseid').on({
						 		'mouseover' : function() {
								 	$('#helpBox$purchaseid').css('display', 'block');
						  		},
						  		'mouseout' : function() {
								 	$('#helpBox$purchaseid').css('display', 'none');
							  	}
						  	});
						</script>
		                ";
		
	} else {
		
		$commentRead = "";
		
	}		

		$selectProduct = "SELECT name, breed2 FROM flower WHERE $productid = flowerid";
		
		$productResult = mysql_query($selectProduct)
			or handleError($lang['error-loadflowerdata'],"Error loading flower: " . mysql_error());
			
	    $row = mysql_fetch_array($productResult);
			$name = $row['name'];
			$formattedDate = date("d M y", strtotime($purchase['purchaseDate'] . "+$offsetSec seconds"));
			$breed2 = $row['breed2'];
		
		if ($breed2 != '') {
			$name = $name . " x " . $breed2;
		}
		
		// Calculate Stock
		$selectSales = "SELECT SUM(quantity) FROM salesdetails WHERE purchaseid = $purchaseid";
	
		$sale = mysql_query($selectSales)
			or handleError($lang['error-expensesload'],"Error loading expense from db: " . mysql_error());
		
		$row = mysql_fetch_array($sale);
			$sales = $row['SUM(quantity)'];

		$selectPermAdditions = "SELECT SUM(quantity) FROM productmovements WHERE purchaseid = $purchaseid AND type = 1 AND (movementTypeid = 1 OR movementTypeid = 3 OR movementTypeid = 10)";
		$permAdditions = mysql_query($selectPermAdditions)
			or handleError($lang['error-loadprodadditions'],"Error loading expense from db: " . mysql_error());
			
			$row = mysql_fetch_array($permAdditions);
				$permAdditions = $row['SUM(quantity)'];
		
		$selectPermRemovals = "SELECT SUM(quantity) FROM productmovements WHERE purchaseid = $purchaseid AND type = 2 AND (movementTypeid = 4 OR movementTypeid = 7 OR movementTypeid = 8 OR movementTypeid = 9 OR movementTypeid = 11 OR movementTypeid = 13 OR movementTypeid = 14 OR movementTypeid = 15 OR movementTypeid = 16)";
		
		$permRemovals = mysql_query($selectPermRemovals)
			or handleError($lang['error-loadprodadditions'],"Error loading expense from db: " . mysql_error());
			
			$row = mysql_fetch_array($permRemovals);
				$permRemovals = $row['SUM(quantity)'];
				
		// Calculate what's in Internal stash
		$selectStashedInt = "SELECT SUM(quantity) FROM productmovements WHERE purchaseid = $purchaseid AND type = 2 AND (movementTypeid = 5 OR movementTypeid = 18)";
		$stashedInt = mysql_query($selectStashedInt)
			or handleError($lang['error-loadstash'],"Error loading stash details from db: " . mysql_error());
		
			$row = mysql_fetch_array($stashedInt);
				$stashedInt = $row['SUM(quantity)'];
			
		$selectUnStashedInt = "SELECT SUM(quantity) FROM productmovements WHERE purchaseid = $purchaseid AND type = 1 AND (movementTypeid = 12 OR movementTypeid = 17)";
		$unStashedInt = mysql_query($selectUnStashedInt)
			or handleError($lang['error-loadstash'],"Error loading stash details from db: " . mysql_error());
		
			$row = mysql_fetch_array($unStashedInt);
				$unStashedInt = $row['SUM(quantity)'];
	
				
			$inStashInt = $stashedInt - $unStashedInt;
			$inStashInt = $inStashInt;
	
	
		// Calculate what's in External stash
		$selectStashedExt = "SELECT SUM(quantity) FROM productmovements WHERE purchaseid = $purchaseid AND type = 2 AND (movementTypeid = 6 OR movementTypeid = 20)";
		$stashedExt = mysql_query($selectStashedExt)
			or handleError($lang['error-loadstash'],"Error loading stash details from db: " . mysql_error());
		
			$row = mysql_fetch_array($stashedExt);
				$stashedExt = $row['SUM(quantity)'];
			
		$selectUnStashedExt = "SELECT SUM(quantity) FROM productmovements WHERE purchaseid = $purchaseid AND type = 1 AND (movementTypeid = 2 OR movementTypeid = 19)";
		$unStashedExt = mysql_query($selectUnStashedExt)
			or handleError($lang['error-loadstash'],"Error loading stash details from db: " . mysql_error());
		
			$row = mysql_fetch_array($unStashedExt);
				$unStashedExt = $row['SUM(quantity)'];

			
		$inStashExt = $stashedExt - $unStashedExt;
		$inStashExt = $inStashExt;
		
		$inStash = $inStashInt + $inStashExt;
		$estStock = $purchase['realQuantity'] + $permAdditions - $sales - $permRemovals - $inStash;

	$purchase_row =	sprintf("
  	  <tr>
  	   <td class='clickableRow' href='purchase.php?purchaseid=%d'>%s</td>
  	   <td class='clickableRow' href='purchase.php?purchaseid=%d'>%s</td>
  	   <td class='clickableRow right' href='purchase.php?purchaseid=%d'>%0.0f g</td>
  	   <td class='clickableRow right' href='purchase.php?purchaseid=%d'>%0.1f g</td>
  	   <td class='clickableRow right' href='purchase.php?purchaseid=%d'>%0.2f<span class='smallerfont3'> &euro; /g</span></td>
  	   <td class='clickableRow right' href='purchase.php?purchaseid=%d'>%0.2f<span class='smallerfont3'> &euro; /g</span></td>
  	   <td class='clickableRow right' href='purchase.php?purchaseid=%d'>%0.2f<span class='smallerfont3'> &euro; /g</span></td>
  	   <td class='clickableRow right' href='purchase.php?purchaseid=%d'>%0.0f g</td>
  	   <td class='clickableRow right' href='purchase.php?purchaseid=%d'>%0.0f g</td>
  	   <td class='clickableRow' style='text-align: center' href='purchase.php?purchaseid=%d'>%s</td>
	   <td class='relative'>$commentRead</td>
  	   <td class='clickableRow right' href='purchase.php?purchaseid=%d'><span class=' %s'>%0.02f g</span></td>
	  </tr>",
	  $purchase['purchaseid'], $formattedDate, $purchase['purchaseid'], $name, $purchase['purchaseid'], $purchase['purchaseQuantity'], $purchase['purchaseid'], $purchase['realQuantity'], $purchase['purchaseid'], $purchase['purchasePrice'], $purchase['purchaseid'], $purchase['salesPrice'] - $purchase['purchasePrice'], $purchase['purchaseid'], $purchase['salesPrice'], $purchase['purchaseid'], $estStock, $purchase['purchaseid'], $inStash, $purchase['purchaseid'], $inMenu, $purchase['purchaseid'], $closeColour, $closedAt);
	    	
	  echo $purchase_row;
	  
	}
}

?>

	 </tbody>
	 </table>
	 
<?php




	// Query to look up closed products, extracts next
	$selectOpenPurchases = "SELECT purchaseid, category, productid, purchaseDate, purchasePrice, salesPrice, purchaseQuantity, realQuantity, adminComment, closedAt, estClosing, inMenu FROM purchases WHERE closedAt IS NOT NULL AND category = 2 ORDER by purchaseDate DESC";

	$resultOpen = mysql_query($selectOpenPurchases)
		or handleError($lang['error-loadpurchases'],"Error loading purchase from db: " . mysql_error());
		
	if (mysql_num_rows($resultOpen) > 0) {

		

						
?>
<br /><br />
<h3 class='title'><?php echo $lang['global-extractscaps']; ?></h3>

	 <table class="default">
	  <thead>
	   <tr>
	    <th><?php echo $lang['pur-date']; ?></th>
	    <th><?php echo $lang['global-product']; ?></th>
	    <th><?php echo $lang['global-quantity']; ?></th>
	    <th><?php echo $lang['add-realweightshort']; ?></th>
	    <th><?php echo $lang['purchase']; ?></th>
	    <th><?php echo $lang['pur-quota']; ?></th>
	    <th><?php echo $lang['global-dispense']; ?></th>
	    <th><?php echo $lang['title-stock']; ?></th>
	    <th>Almacén</th>
	    <th><?php echo $lang['pur-inmenu']; ?></th>
	    <th></th>
	    <th><?php echo $lang['pur-closedat']; ?></th>
	   </tr>
	  </thead>
	  <tbody>

<?php

while ($purchase = mysql_fetch_array($resultOpen)) {
	
			$productid = $purchase['productid'];
			$purchaseid = $purchase['purchaseid'];
			
			$inMenu = $purchase['inMenu'];
			
			$closedAt = $purchase['closedAt'];
			
			if ($closedAt == 0) {
				$closeColour = '';
			}
			else if ($closedAt < 0) {
				$closeColour = 'negative';
			} else if ($closedAt > 0) {
				$closeColour = 'positive';
			}
			
			if ($inMenu == 1) {
				$inMenu = $lang['global-yes'];
			} else {
				$inMenu = $lang['global-no'];
			}
			
	if ($purchase['adminComment'] != '') {
		
		$commentRead = "
		                <img src='images/comments.png' id='comment$purchaseid' /><div id='helpBox$purchaseid' class='helpBox'>{$purchase['adminComment']}</div>
		                <script>
		                  	$('#comment$purchaseid').on({
						 		'mouseover' : function() {
								 	$('#helpBox$purchaseid').css('display', 'block');
						  		},
						  		'mouseout' : function() {
								 	$('#helpBox$purchaseid').css('display', 'none');
							  	}
						  	});
						</script>
		                ";
		
	} else {
		
		$commentRead = "";
		
	}		

		$selectProduct = "SELECT name FROM extract WHERE $productid = extractid";
		
		$productResult = mysql_query($selectProduct)
			or handleError($lang['error-loadflowerdata'],"Error loading flower: " . mysql_error());
			
	    $row = mysql_fetch_array($productResult);
			$name = $row['name'];
			$formattedDate = date("d M y", strtotime($purchase['purchaseDate'] . "+$offsetSec seconds"));
		
		// Calculate Stock
		$selectSales = "SELECT SUM(quantity) FROM salesdetails WHERE purchaseid = $purchaseid";
	
		$sale = mysql_query($selectSales)
			or handleError($lang['error-expensesload'],"Error loading expense from db: " . mysql_error());
		
		$row = mysql_fetch_array($sale);
			$sales = $row['SUM(quantity)'];

		$selectPermAdditions = "SELECT SUM(quantity) FROM productmovements WHERE purchaseid = $purchaseid AND type = 1 AND (movementTypeid = 1 OR movementTypeid = 3 OR movementTypeid = 10)";
		$permAdditions = mysql_query($selectPermAdditions)
			or handleError($lang['error-loadprodadditions'],"Error loading expense from db: " . mysql_error());
			
			$row = mysql_fetch_array($permAdditions);
				$permAdditions = $row['SUM(quantity)'];
		
		$selectPermRemovals = "SELECT SUM(quantity) FROM productmovements WHERE purchaseid = $purchaseid AND type = 2 AND (movementTypeid = 4 OR movementTypeid = 7 OR movementTypeid = 8 OR movementTypeid = 9 OR movementTypeid = 11 OR movementTypeid = 13 OR movementTypeid = 14 OR movementTypeid = 15 OR movementTypeid = 16)";
		
		$permRemovals = mysql_query($selectPermRemovals)
			or handleError($lang['error-loadprodadditions'],"Error loading expense from db: " . mysql_error());
			
			$row = mysql_fetch_array($permRemovals);
				$permRemovals = $row['SUM(quantity)'];
				
		// Calculate what's in Internal stash
		$selectStashedInt = "SELECT SUM(quantity) FROM productmovements WHERE purchaseid = $purchaseid AND type = 2 AND (movementTypeid = 5 OR movementTypeid = 18)";
		$stashedInt = mysql_query($selectStashedInt)
			or handleError($lang['error-loadstash'],"Error loading stash details from db: " . mysql_error());
		
			$row = mysql_fetch_array($stashedInt);
				$stashedInt = $row['SUM(quantity)'];
			
		$selectUnStashedInt = "SELECT SUM(quantity) FROM productmovements WHERE purchaseid = $purchaseid AND type = 1 AND (movementTypeid = 12 OR movementTypeid = 17)";
		$unStashedInt = mysql_query($selectUnStashedInt)
			or handleError($lang['error-loadstash'],"Error loading stash details from db: " . mysql_error());
		
			$row = mysql_fetch_array($unStashedInt);
				$unStashedInt = $row['SUM(quantity)'];
	
				
			$inStashInt = $stashedInt - $unStashedInt;
			$inStashInt = $inStashInt;
	
	
		// Calculate what's in External stash
		$selectStashedExt = "SELECT SUM(quantity) FROM productmovements WHERE purchaseid = $purchaseid AND type = 2 AND (movementTypeid = 6 OR movementTypeid = 20)";
		$stashedExt = mysql_query($selectStashedExt)
			or handleError($lang['error-loadstash'],"Error loading stash details from db: " . mysql_error());
		
			$row = mysql_fetch_array($stashedExt);
				$stashedExt = $row['SUM(quantity)'];
			
		$selectUnStashedExt = "SELECT SUM(quantity) FROM productmovements WHERE purchaseid = $purchaseid AND type = 1 AND (movementTypeid = 2 OR movementTypeid = 19)";
		$unStashedExt = mysql_query($selectUnStashedExt)
			or handleError($lang['error-loadstash'],"Error loading stash details from db: " . mysql_error());
		
			$row = mysql_fetch_array($unStashedExt);
				$unStashedExt = $row['SUM(quantity)'];

			
		$inStashExt = $stashedExt - $unStashedExt;
		$inStashExt = $inStashExt;
		
		$inStash = $inStashInt + $inStashExt;
		$estStock = $purchase['realQuantity'] + $permAdditions - $sales - $permRemovals - $inStash;

	$purchase_row =	sprintf("
  	  <tr>
  	   <td class='clickableRow' href='purchase.php?purchaseid=%d'>%s</td>
  	   <td class='clickableRow' href='purchase.php?purchaseid=%d'>%s</td>
  	   <td class='clickableRow right' href='purchase.php?purchaseid=%d'>%0.0f g</td>
  	   <td class='clickableRow right' href='purchase.php?purchaseid=%d'>%0.1f g</td>
  	   <td class='clickableRow right' href='purchase.php?purchaseid=%d'>%0.2f<span class='smallerfont3'> &euro; /g</span></td>
  	   <td class='clickableRow right' href='purchase.php?purchaseid=%d'>%0.2f<span class='smallerfont3'> &euro; /g</span></td>
  	   <td class='clickableRow right' href='purchase.php?purchaseid=%d'>%0.2f<span class='smallerfont3'> &euro; /g</span></td>
  	   <td class='clickableRow right' href='purchase.php?purchaseid=%d'>%0.0f g</td>
  	   <td class='clickableRow right' href='purchase.php?purchaseid=%d'>%0.0f g</td>
  	   <td class='clickableRow' style='text-align: center' href='purchase.php?purchaseid=%d'>%s</td>
	   <td class='relative'>$commentRead</td>
  	   <td class='clickableRow right' href='purchase.php?purchaseid=%d'><span class=' %s'>%0.02f g</span></td>
	  </tr>",
	  $purchase['purchaseid'], $formattedDate, $purchase['purchaseid'], $name, $purchase['purchaseid'], $purchase['purchaseQuantity'], $purchase['purchaseid'], $purchase['realQuantity'], $purchase['purchaseid'], $purchase['purchasePrice'], $purchase['purchaseid'], $purchase['salesPrice'] - $purchase['purchasePrice'], $purchase['purchaseid'], $purchase['salesPrice'], $purchase['purchaseid'], $estStock, $purchase['purchaseid'], $inStash, $purchase['purchaseid'], $inMenu, $purchase['purchaseid'], $closeColour, $closedAt);
	    	
	  echo $purchase_row;
	  
	}
}
		
		
?>

	 </tbody>
	 </table>
	 
<?php
		
		// Query to look up categories, then products in each category
		$selectCats = "SELECT id, name, description from categories ORDER by id ASC";
	
		$resultCats = mysql_query($selectCats)
			or handleError($lang['error-loadflowers'],"Error loading flower from db: " . mysql_error());
	
		while ($category = mysql_fetch_array($resultCats)) {
			
			$categoryname = $category['name'];
			$categoryid = $category['id'];
			
			
	// Query to look up closed products, extracts next
	$selectOpenPurchases = "SELECT purchaseid, category, productid, purchaseDate, purchasePrice, salesPrice, purchaseQuantity, realQuantity, adminComment, closedAt, estClosing, inMenu FROM purchases WHERE closedAt IS NOT NULL AND category = $categoryid ORDER by purchaseDate DESC";

	$resultOpen = mysql_query($selectOpenPurchases)
		or handleError($lang['error-loadpurchases'],"Error loading purchase from db: " . mysql_error());
		
	if (mysql_num_rows($resultOpen) > 0) {

		

						
?>
<br /><br />
<h3 class='title'><?php echo $categoryname; ?></h3>

	 <table class="default">
	  <thead>
	   <tr>
	    <th><?php echo $lang['pur-date']; ?></th>
	    <th><?php echo $lang['global-product']; ?></th>
	    <th><?php echo $lang['global-quantity']; ?></th>
	    <th><?php echo $lang['add-realweightshort']; ?></th>
	    <th><?php echo $lang['purchase']; ?></th>
	    <th><?php echo $lang['pur-quota']; ?></th>
	    <th><?php echo $lang['global-dispense']; ?></th>
	    <th><?php echo $lang['title-stock']; ?></th>
	    <th>Almacén</th>
	    <th><?php echo $lang['pur-inmenu']; ?></th>
	    <th></th>
	    <th><?php echo $lang['pur-closedat']; ?></th>
	   </tr>
	  </thead>
	  <tbody>

<?php

while ($purchase = mysql_fetch_array($resultOpen)) {
	
			$productid = $purchase['productid'];
			$purchaseid = $purchase['purchaseid'];
			
			$inMenu = $purchase['inMenu'];
			
			$closedAt = $purchase['closedAt'];
			
			if ($closedAt == 0) {
				$closeColour = '';
			}
			else if ($closedAt < 0) {
				$closeColour = 'negative';
			} else if ($closedAt > 0) {
				$closeColour = 'positive';
			}
			
			if ($inMenu == 1) {
				$inMenu = $lang['global-yes'];
			} else {
				$inMenu = $lang['global-no'];
			}
			
	if ($purchase['adminComment'] != '') {
		
		$commentRead = "
		                <img src='images/comments.png' id='comment$purchaseid' /><div id='helpBox$purchaseid' class='helpBox'>{$purchase['adminComment']}</div>
		                <script>
		                  	$('#comment$purchaseid').on({
						 		'mouseover' : function() {
								 	$('#helpBox$purchaseid').css('display', 'block');
						  		},
						  		'mouseout' : function() {
								 	$('#helpBox$purchaseid').css('display', 'none');
							  	}
						  	});
						</script>
		                ";
		
	} else {
		
		$commentRead = "";
		
	}		

		$selectProduct = "SELECT name FROM products WHERE $productid = productid";
		
		$productResult = mysql_query($selectProduct)
			or handleError($lang['error-loadflowerdata'],"Error loading flower: " . mysql_error());
			
	    $row = mysql_fetch_array($productResult);
			$name = $row['name'];
			$formattedDate = date("d M y", strtotime($purchase['purchaseDate'] . "+$offsetSec seconds"));
		
		// Calculate Stock
		$selectSales = "SELECT SUM(quantity) FROM salesdetails WHERE purchaseid = $purchaseid";
	
		$sale = mysql_query($selectSales)
			or handleError($lang['error-expensesload'],"Error loading expense from db: " . mysql_error());
		
		$row = mysql_fetch_array($sale);
			$sales = $row['SUM(quantity)'];

		$selectPermAdditions = "SELECT SUM(quantity) FROM productmovements WHERE purchaseid = $purchaseid AND type = 1 AND (movementTypeid = 1 OR movementTypeid = 3 OR movementTypeid = 10)";
		$permAdditions = mysql_query($selectPermAdditions)
			or handleError($lang['error-loadprodadditions'],"Error loading expense from db: " . mysql_error());
			
			$row = mysql_fetch_array($permAdditions);
				$permAdditions = $row['SUM(quantity)'];
		
		$selectPermRemovals = "SELECT SUM(quantity) FROM productmovements WHERE purchaseid = $purchaseid AND type = 2 AND (movementTypeid = 4 OR movementTypeid = 7 OR movementTypeid = 8 OR movementTypeid = 9 OR movementTypeid = 11 OR movementTypeid = 13 OR movementTypeid = 14 OR movementTypeid = 15 OR movementTypeid = 16)";
		
		$permRemovals = mysql_query($selectPermRemovals)
			or handleError($lang['error-loadprodadditions'],"Error loading expense from db: " . mysql_error());
			
			$row = mysql_fetch_array($permRemovals);
				$permRemovals = $row['SUM(quantity)'];
				
		// Calculate what's in Internal stash
		$selectStashedInt = "SELECT SUM(quantity) FROM productmovements WHERE purchaseid = $purchaseid AND type = 2 AND (movementTypeid = 5 OR movementTypeid = 18)";
		$stashedInt = mysql_query($selectStashedInt)
			or handleError($lang['error-loadstash'],"Error loading stash details from db: " . mysql_error());
		
			$row = mysql_fetch_array($stashedInt);
				$stashedInt = $row['SUM(quantity)'];
			
		$selectUnStashedInt = "SELECT SUM(quantity) FROM productmovements WHERE purchaseid = $purchaseid AND type = 1 AND (movementTypeid = 12 OR movementTypeid = 17)";
		$unStashedInt = mysql_query($selectUnStashedInt)
			or handleError($lang['error-loadstash'],"Error loading stash details from db: " . mysql_error());
		
			$row = mysql_fetch_array($unStashedInt);
				$unStashedInt = $row['SUM(quantity)'];
	
				
			$inStashInt = $stashedInt - $unStashedInt;
			$inStashInt = $inStashInt;
	
	
		// Calculate what's in External stash
		$selectStashedExt = "SELECT SUM(quantity) FROM productmovements WHERE purchaseid = $purchaseid AND type = 2 AND (movementTypeid = 6 OR movementTypeid = 20)";
		$stashedExt = mysql_query($selectStashedExt)
			or handleError($lang['error-loadstash'],"Error loading stash details from db: " . mysql_error());
		
			$row = mysql_fetch_array($stashedExt);
				$stashedExt = $row['SUM(quantity)'];
			
		$selectUnStashedExt = "SELECT SUM(quantity) FROM productmovements WHERE purchaseid = $purchaseid AND type = 1 AND (movementTypeid = 2 OR movementTypeid = 19)";
		$unStashedExt = mysql_query($selectUnStashedExt)
			or handleError($lang['error-loadstash'],"Error loading stash details from db: " . mysql_error());
		
			$row = mysql_fetch_array($unStashedExt);
				$unStashedExt = $row['SUM(quantity)'];

			
		$inStashExt = $stashedExt - $unStashedExt;
		$inStashExt = $inStashExt;
		
		$inStash = $inStashInt + $inStashExt;
		$estStock = $purchase['realQuantity'] + $permAdditions - $sales - $permRemovals - $inStash;

	$purchase_row =	sprintf("
  	  <tr>
  	   <td class='clickableRow' href='purchase.php?purchaseid=%d'>%s</td>
  	   <td class='clickableRow' href='purchase.php?purchaseid=%d'>%s</td>
  	   <td class='clickableRow right' href='purchase.php?purchaseid=%d'>%0.0f g</td>
  	   <td class='clickableRow right' href='purchase.php?purchaseid=%d'>%0.1f g</td>
  	   <td class='clickableRow right' href='purchase.php?purchaseid=%d'>%0.2f<span class='smallerfont3'> &euro; /g</span></td>
  	   <td class='clickableRow right' href='purchase.php?purchaseid=%d'>%0.2f<span class='smallerfont3'> &euro; /g</span></td>
  	   <td class='clickableRow right' href='purchase.php?purchaseid=%d'>%0.2f<span class='smallerfont3'> &euro; /g</span></td>
  	   <td class='clickableRow right' href='purchase.php?purchaseid=%d'>%0.0f g</td>
  	   <td class='clickableRow right' href='purchase.php?purchaseid=%d'>%0.0f g</td>
  	   <td class='clickableRow' style='text-align: center' href='purchase.php?purchaseid=%d'>%s</td>
	   <td class='relative'>$commentRead</td>
  	   <td class='clickableRow right' href='purchase.php?purchaseid=%d'><span class=' %s'>%0.00f u</span></td>
	  </tr>",
	  $purchase['purchaseid'], $formattedDate, $purchase['purchaseid'], $name, $purchase['purchaseid'], $purchase['purchaseQuantity'], $purchase['purchaseid'], $purchase['realQuantity'], $purchase['purchaseid'], $purchase['purchasePrice'], $purchase['purchaseid'], $purchase['salesPrice'] - $purchase['purchasePrice'], $purchase['purchaseid'], $purchase['salesPrice'], $purchase['purchaseid'], $estStock, $purchase['purchaseid'], $inStash, $purchase['purchaseid'], $inMenu, $purchase['purchaseid'], $closeColour, $closedAt);
	    	
	  echo $purchase_row;
	  
	}
}			
			
?>

	 </tbody>
	 </table>
	 
<?php
		}
	
