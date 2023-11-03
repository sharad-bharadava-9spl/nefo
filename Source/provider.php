<?php
	
	require_once 'cOnfig/connection.php';
	require_once 'cOnfig/view.php';
	require_once 'cOnfig/authenticate.php';
	require_once 'cOnfig/languages/common.php';
	
	session_start();
	$accessLevel = '3';
	
	// Authenticate & authorize
	authorizeUser($accessLevel);
		
	// Does purchase ID exist?
	if (!$_GET['providerid']) {
		echo $lang['error-nopurchselected'];
		exit();
	} else  {
		$providerid = $_GET['providerid'];
	}
	
	// Query to look up provider
	$providerDetails = "SELECT registered, name, comment, providernumber, credit FROM providers WHERE id = $providerid";
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
		$comment = $row['comment'];
		$providernumber = $row['providernumber'];
		$credit = $row['credit'];
		
	$selectPurchases = "SELECT 'purchase' AS type, purchaseDate, purchaseid, category, productid, purchaseQuantity, realQuantity, purchasePrice, paid, adminComment AS comment FROM purchases WHERE provider = $providerid UNION ALL SELECT 'payment' AS type, paymentTime AS purchaseDate, '' AS purchaseid, '' AS category, '' AS productid, '' AS purchaseQuantity, '' AS realQuantity, '' AS purchasePrice, amount AS paid, comment FROM providerpayments WHERE providerid = $providerid UNION ALL SELECT 'reload' AS type, movementtime AS purchaseDate, purchaseid, '' AS category, '' AS productid, quantity AS purchaseQuantity, quantity AS realQuantity, price AS purchasePrice, paid, comment FROM productmovements WHERE provider = $providerid ORDER BY purchaseDate DESC";
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


	$selectPurchases2 = "SELECT SUM(paid) FROM purchases WHERE provider = $providerid";
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
		
		
	$selectTotal = "SELECT purchasePrice, purchaseQuantity FROM purchases WHERE provider = $providerid";
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
	
	
	$selectTotal2 = "SELECT SUM(amount) FROM providerpayments WHERE providerid = $providerid";
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
		
	$selectTotal3 = "SELECT SUM(price), SUM(paid) FROM productmovements WHERE provider = $providerid";
		try
		{
			$resultTotal3 = $pdo3->prepare("$selectTotal3");
			$resultTotal3->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $resultTotal3->fetch();
		$reloadPrice = $row['SUM(price)'];
		$reloadPaid = $row['SUM(paid)'];
		
	$totalPurchased = $totalPurchased + $reloadPrice;
	$totalPaid = $totalPaid + $reloadPaid;
		
	$totCredit = $totalPaid - $totalPurchased;
	
	pageStart($lang['providers'], NULL, $deleteUserScript, "provider", "admin", $lang['providers'], $_SESSION['successMessage'], $_SESSION['errorMessage']); 

	echo "<center><a href='edit-provider.php?providerid=$providerid' class='cta1'>{$lang['global-edit']}</a>";
	echo "<a href='new-purchase.php?providerid=$providerid' class='cta1'>{$lang['newpurchase']}</a>";
	echo "<a href='pay-provider.php?providerid=$providerid' class='cta1'>{$lang['make-payment']}</a>";

	if ($totCredit < 0) {
		$colourStyle = "style='background-color: #fef4f3;'";
	}
	
?>
<br />
<div id="mainbox-no-width">
 <div id="mainboxheader">
  #<?php echo $providernumber; ?> - <?php echo $name; ?>
 </div>
 <div class='boxcontent'>
  
 <table style="border-spacing: 10px; border-collapse: separate;">
  <tr>
   <td class='dispensetd'><?php echo $lang['purchases']; ?>:<div style='inline-block; float: right;'><?php echo round($totalPurchased,2); ?> &euro;</div></td>
  </tr>
  <tr>
   <td class='dispensetd'><?php echo $lang['payments']; ?>:<div style='inline-block; float: right;'><?php echo round($totalPaid,2); ?> &euro;</div></td>
  </tr>
  <tr>
   <td class='dispensetd' <?php echo $colourStyle; ?>><?php echo $lang['global-credit']; ?>:<div style='inline-block; float: right;'><?php echo round($totCredit,2); ?> &euro;</div></td>
  </tr>
 </table>
</div>
</div>

 <br /><br />
	 <table class="default">
	  <thead>
	   <tr>
	    <th><?php echo $lang['pur-date']; ?></th>
	    <th><?php echo $lang['global-type']; ?></th>
	    <th><?php echo $lang['global-product']; ?></th>
	    <th><?php echo $lang['global-quantity']; ?></th>
	    <th><?php echo $lang['add-realweightshort']; ?></th>
	    <th><?php echo $lang['price']; ?></th>
	    <th><?php echo $lang['paid']; ?></th>
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
		$realQuantity = $sale['realQuantity'];
		$paid = $sale['paid'];
		$type = $sale['type'];
		$price = $sale['purchasePrice'];
		$comment = $sale['comment'];
		
			
			
		
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
				
			if ($category == 1) {
				
				$selectProduct = "SELECT name, breed2 FROM flower WHERE flowerid = $productid";
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
					$breed2 = $row['breed2'];
					
				if ($breed2 != '') {
					
					$name = $name . " x " . $breed2;
					
				} else {
					
					$name = $name;
					
				}
				
				$type = 1;
					
			} else if ($category == 2) {
				
				$selectProduct = "SELECT name FROM extract WHERE extractid = $productid";
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
					$type = 1;
	
			} else {
				
				$selectProduct = "SELECT name FROM products WHERE productid = $productid";
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
				
				$selectCatS = "SELECT type FROM categories WHERE id = $category";
				try
				{
					$productCatS = $pdo3->prepare("$selectCatS");
					$productCatS->execute();
				}
				catch (PDOException $e)
				{
						$error = 'Error fetching user: ' . $e->getMessage();
						echo $error;
						exit();
				}
			
				$rowCS = $productCatS->fetch();
					$type = $rowCS['type'];
					
			}
			
			$price = $purchaseQuantity * $purchasePrice;
			
		  	    
			if ($type == 1) {
				
				$provRow = sprintf("
				<tr>
		  	    <td class='left clickableRow' href='purchase.php?purchaseid=$purchaseid'>%s</td>
		  	    <td class='left clickableRow' href='purchase.php?purchaseid=$purchaseid'>{$lang['global-purchase']}</td>
		  	    <td class='left clickableRow' href='purchase.php?purchaseid=$purchaseid'>%s</td>
		  	    <td class='right clickableRow' href='purchase.php?purchaseid=$purchaseid'>%0.02f g.</td>
		  	    <td class='right clickableRow' href='purchase.php?purchaseid=$purchaseid'>%0.02f g.</td>
		  	    <td class='right clickableRow' href='purchase.php?purchaseid=$purchaseid'>%0.02f &euro;</td>
		  	    <td class='right clickableRow' href='purchase.php?purchaseid=$purchaseid'>%0.02f &euro;</td>
	   			<td class='relative clickableRow' href='purchase.php?purchaseid=$purchaseid'><span class='relativeitem'>$commentRead</span></td>
		  	    </tr>",
		  	    $formattedDate, $name, round($purchaseQuantity,2), $realQuantity, $price, $paid);
		  	    
	  	    } else {
		  	    
				$provRow = sprintf("
				<tr>
		  	    <td class='left clickableRow'>%s</td>
		  	    <td class='left clickableRow' href='purchase.php?purchaseid=$purchaseid'>{$lang['global-purchase']}</td>
		  	    <td class='left clickableRow' href='purchase.php?purchaseid=$purchaseid'>%s</td>
		  	    <td class='right clickableRow' href='purchase.php?purchaseid=$purchaseid'>%0.02f u.</td>
		  	    <td class='right clickableRow' href='purchase.php?purchaseid=$purchaseid'>%0.02f u.</td>
		  	    <td class='right clickableRow' href='purchase.php?purchaseid=$purchaseid'>%0.02f &euro;</td>
		  	    <td class='right clickableRow' href='purchase.php?purchaseid=$purchaseid'>%0.02f &euro;</td>
	   			<td class='relative clickableRow' href='purchase.php?purchaseid=$purchaseid'><span class='relativeitem'>$commentRead</span></td>
		  	    </tr>",
		  	    $formattedDate, $name, round($purchaseQuantity,2), $realQuantity, $price, $paid);
		  	    
	  	    }

	  	    
	
			
		} else if ($type == 'reload') {
			
			$selectProduct = "SELECT productid, category FROM purchases WHERE purchaseid = $purchaseid";
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
			
			if ($category == 1) {
				
				$selectProduct = "SELECT name, breed2 FROM flower WHERE flowerid = '$productid'";
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
					$breed2 = $row['breed2'];
					
				if ($breed2 != '') {
					
					$name = $name . " x " . $breed2;
					
				} else {
					
					$name = $name;
					
				}
				
				$type = 1;
					
			} else if ($category == 2) {
				
				$selectProduct = "SELECT name FROM extract WHERE extractid = '$productid'";
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
					$type = 1;
	
			} else {
				
				
				$selectProduct = "SELECT name FROM products WHERE productid = '$productid'";
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
				
				$selectCatS = "SELECT type FROM categories WHERE id = '$category'";
				try
				{
					$productCatS = $pdo3->prepare("$selectCatS");
					$productCatS->execute();
				}
				catch (PDOException $e)
				{
						$error = 'Error fetching user: ' . $e->getMessage();
						echo $error;
						exit();
				}
			
				$rowCS = $productCatS->fetch();
					$type = $rowCS['type'];
					
			}
			
			if ($type == 1) {
				
				$provRow = sprintf("
				<tr>
		  	    <td class='clickableRow' href='purchase.php?purchaseid=$purchaseid'>%s</td>
		  	    <td class='left clickableRow' href='purchase.php?purchaseid=$purchaseid'>{$lang['reload']}</td>
		  	    <td class='left clickableRow' href='purchase.php?purchaseid=$purchaseid'>%s</td>
		  	    <td class='right clickableRow' href='purchase.php?purchaseid=$purchaseid'>%0.02f g.</td>
		  	    <td class='right clickableRow' href='purchase.php?purchaseid=$purchaseid'>%0.02f g.</td>
		  	    <td class='right clickableRow' href='purchase.php?purchaseid=$purchaseid'>%0.02f &euro;</td>
		  	    <td class='right clickableRow' href='purchase.php?purchaseid=$purchaseid'>%0.02f &euro;</td>
	   			<td class='relative clickableRow' href='purchase.php?purchaseid=$purchaseid'><span class='relativeitem'>$commentRead</span></td>
		  	    </tr>",
		  	    $formattedDate, $name, round($purchaseQuantity,2), $realQuantity, $price, $paid);
		  	    
	  	    } else {
		  	    
				$provRow = sprintf("
				<tr>
		  	    <td class='clickableRow' href='purchase.php?purchaseid=$purchaseid'>%s</td>
		  	    <td class='left clickableRow' href='purchase.php?purchaseid=$purchaseid'>{$lang['reload']}</td>
		  	    <td class='left clickableRow' href='purchase.php?purchaseid=$purchaseid'>%s</td>
		  	    <td class='right clickableRow' href='purchase.php?purchaseid=$purchaseid'>%0.02f u.</td>
		  	    <td class='right clickableRow' href='purchase.php?purchaseid=$purchaseid'>%0.02f u.</td>
		  	    <td class='right clickableRow' href='purchase.php?purchaseid=$purchaseid'>%0.02f &euro;</td>
		  	    <td class='right clickableRow' href='purchase.php?purchaseid=$purchaseid'>%0.02f &euro;</td>
	   			<td class='relative clickableRow' href='purchase.php?purchaseid=$purchaseid'><span class='relativeitem'>$commentRead</span></td>
		  	    </tr>",
		  	    $formattedDate, $name, round($purchaseQuantity,2), $realQuantity, $price, $paid);
		  	    
	  	    }

	  	    
	
			
		} else {
			
			$provRow = sprintf("
			<tr class='green'>
	  	    <td>%s</td>
		  	<td class='left'>{$lang['payment']}</td>
	  	    <td class='left'></td>
	  	    <td class='right'></td>
	  	    <td class='right'></td>
	  	    <td class='right'></td>
	  	    <td class='right'>%0.02f &euro;</td>
	   		<td><span class='relativeitem'>$commentRead</span></td>
	  	    </tr>",
	  	    $formattedDate, $paid);
	  	    
			
		}
		
		echo $provRow;	 
		
	}

	echo "</table>";
	
displayFooter();