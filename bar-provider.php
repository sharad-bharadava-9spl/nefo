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
	$providerDetails = "SELECT registered, name, comment, providernumber, credit FROM b_providers WHERE id = $providerid";
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
		
	$selectPurchases = "SELECT '' AS id,  'purchase' AS type, purchaseDate, purchaseid, category, productid, purchaseQuantity, purchasePrice, paid, adminComment AS comment FROM b_purchases WHERE provider = $providerid UNION ALL SELECT paymentid AS id, 'payment' AS type, paymentTime AS purchaseDate, '' AS purchaseid, '' AS category, '' AS productid, '' AS purchaseQuantity, '' AS purchasePrice, amount AS paid, comment FROM b_providerpayments WHERE providerid = $providerid UNION ALL SELECT '' AS id, 'reload' AS type, movementtime AS purchaseDate, purchaseid, '' AS category, '' AS productid, quantity AS purchaseQuantity, price AS purchasePrice, paid, comment FROM b_productmovements WHERE provider = $providerid ORDER BY purchaseDate DESC";
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
	$selectPurchases2 = "SELECT SUM(paid) FROM b_purchases WHERE provider = $providerid";
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
		
		
	$selectTotal = "SELECT purchasePrice, purchaseQuantity FROM b_purchases WHERE provider = $providerid";
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
	
	
	$selectTotal2 = "SELECT SUM(amount) FROM b_providerpayments WHERE providerid = $providerid";
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
	
	$selectTotal3 = "SELECT SUM(price), SUM(paid) FROM b_productmovements WHERE provider = $providerid";

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
	
	$delScript = <<<EOD
	
		function delete_payment(id, providerid) {
			if (confirm("{$lang['payment-deleteconfirm']}")) {
						window.location = "uTil/bar-delete-provider-payment.php?paymentid=" + id + "&providerid=" + providerid;
						}
		}
EOD;
	
	pageStart($lang['providers'], NULL, $delScript, "provider", "admin", $lang['providers'], $_SESSION['successMessage'], $_SESSION['errorMessage']); 

	echo "<center><a href='bar-edit-provider.php?providerid=$providerid' class='cta1'>{$lang['global-edit']}</a>";
	echo "<a href='bar-new-purchase.php?providerid=$providerid' class='cta1'>{$lang['newpurchase']}</a>";
	echo "<a href='bar-pay-provider.php?providerid=$providerid' class='cta1'>{$lang['make-payment']}</a>";

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
   <td class='dispensetd'><?php echo $lang['purchases']; ?>:<div style='inline-block; float: right;'><?php echo number_format($totalPurchased,2); ?> <?php echo $_SESSION['currencyoperator'] ?></div></td>
  </tr>
  <tr>
   <td class='dispensetd'><?php echo $lang['payments']; ?>:<div style='inline-block; float: right;'><?php echo number_format($totalPaid,2); ?> <?php echo $_SESSION['currencyoperator'] ?></div></td>
  </tr>
  <tr>
   <td class='dispensetd' <?php echo $colourStyle; ?>><?php echo $lang['global-credit']; ?>:<div style='inline-block; float: right;'><?php echo number_format($totCredit,2); ?> <?php echo $_SESSION['currencyoperator'] ?></div></td>
  </tr>
 </table>
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
		  	    <td class='right'>%0.02f {$_SESSION['currencyoperator']}</td>
		  	    <td class='right'>%0.02f {$_SESSION['currencyoperator']}</td>
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
		  	    <td class='right'>%0.02f {$_SESSION['currencyoperator']}</td>
		  	    <td class='right'>%0.02f {$_SESSION['currencyoperator']}</td>
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
		  	    <td class='right'>%0.02f {$_SESSION['currencyoperator']}</td>
		  	    <td class='right'>%0.02f {$_SESSION['currencyoperator']}</td>
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
	  	    <td class='right'>%0.02f {$_SESSION['currencyoperator']}</td>
	   		<td><span class='relativeitem'>$commentRead</span></td>
	   		<td><a href='bar-edit-provider-payment.php?paymentid=$id&providerid=$providerid'><img src='images/edit.png' height='15' title='Edit' /></a>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<a href='javascript:delete_payment($id, $providerid)'><img src='images/delete.png' height='15' /></a></td>
	  	    </tr>",
	  	    $formattedDate, $paid);
	  	    
			
		}
		
		echo $provRow;	 
		
	}

	echo "</table>";
displayFooter(); ?>
<script type="text/javascript">
	$(document).ready(function() {
			
			$('#mainTable').tablesorter({
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
</script>
