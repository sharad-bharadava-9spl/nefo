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
				
	$result = mysql_query($providerDetails)
		or handleError($lang['error-loadpurchase'],"Error loading purchase: " . mysql_error());
	
	$row = mysql_fetch_array($result);
		$registered = $row['registered'];
		$name = $row['name'];
		$comment = $row['comment'];
		$providernumber = $row['providernumber'];
		$credit = $row['credit'];
		
	$selectPurchases = "SELECT 'purchase' AS type, purchaseDate, purchaseid, category, productid, purchaseQuantity, realQuantity, purchasePrice, paid FROM purchases WHERE provider = $providerid UNION ALL SELECT 'payment' AS type, paymentTime AS purchaseDate, '' AS purchaseid, '' AS category, '' AS productid, '' AS purchaseQuantity, '' AS realQuantity, '' AS purchasePrice, amount AS paid FROM providerpayments WHERE providerid = $providerid ORDER BY purchaseDate DESC";
	
	$resultPurchases = mysql_query($selectPurchases)
		or handleError($lang['error-loadpurchases'],"Error loading purchase from db: " . mysql_error());


	pageStart($lang['providers'], NULL, $deleteUserScript, "ppurchase", "admin", $lang['providers'], $_SESSION['successMessage'], $_SESSION['errorMessage']); 

	echo "<a href='new-purchase.php?providerid=$providerid' class='cta'>{$lang['newpurchase']}</a>";
	echo "<a href='pay-provider.php?providerid=$providerid' class='cta'>Pagar</a>";

?>
<br /><br /><br />

<div id='productoverview'>
 
 <table style="display: inline-block; vertical-align: top; <?php if ($category == '2') { echo 'margin-top: 9px;'; } ?>">
  <tr>
   <td><?php echo $lang['provider']; ?>:</td>
   <td class='yellow fat'>#<?php echo $providernumber; ?></td>
  </tr>
  <tr>
   <td><?php echo $lang['global-name']; ?>:</td>
   <td class='yellow fat'><?php echo $name; ?></td>
  </tr>
  <tr>
   <td><?php echo $lang['global-credit']; ?>:</td>
   <td class='yellow fat'><?php echo $credit; ?> €</td>
  </tr>
 </table>
</div>

 <br /><br />
 
	 <table class="default">
	  <thead>
	   <tr>
	    <th><?php echo $lang['pur-date']; ?></th>
	    <th><?php echo $lang['global-product']; ?></th>
	    <th><?php echo $lang['global-quantity']; ?></th>
	    <th><?php echo $lang['add-realweightshort']; ?></th>
	    <th>Precio</th>
	    <th>Pagado</th>
	   </tr>
	  </thead>
	  <tbody>
	  
<?php
while ($sale = mysql_fetch_array($resultPurchases)) {
	
		$formattedDate = date("d M H:i", strtotime($sale['purchaseDate'] . "+$offsetSec seconds"));
		$purchaseid = $sale['purchaseid'];
		$category = $sale['category'];
		$productid = $sale['productid'];
		$purchaseQuantity = $sale['purchaseQuantity'];
		$purchasePrice = $sale['purchasePrice'];
		$realQuantity = $sale['realQuantity'];
		$paid = $sale['paid'];
		$type = $sale['type'];
		
		if ($type == 'purchase') {
				
			if ($category == 1) {
				
				$selectProduct = "SELECT name, breed2 FROM flower WHERE flowerid = $productid";
				
				$productResult = mysql_query($selectProduct)
					or handleError($lang['error-loadflowerdata'],"Error loading flower: " . mysql_error());
					
			    $row = mysql_fetch_array($productResult);
					$name = $row['name'];
					$breed2 = $row['breed2'];
					
				if ($breed2 != '') {
					
					$name = $name . " x " . $breed2;
					
				} else {
					
					$name = $name;
					
				}
					
			} else if ($category == 2) {
				
				$selectProduct = "SELECT name FROM extract WHERE extractid = $productid";
				
				$productResult = mysql_query($selectProduct)
					or handleError($lang['error-loadflowerdata'],"Error loading flower: " . mysql_error());
					
			    $row = mysql_fetch_array($productResult);
					$name = $row['name'];
	
			}
			
			$price = $purchaseQuantity * $purchasePrice;
			
			$provRow = sprintf("
			<tr>
	  	    <td>%s</td>
	  	    <td>%s</td>
	  	    <td class='right'>%0.02f g.</td>
	  	    <td class='right'>%0.02f g.</td>
	  	    <td class='right'>%0.02f €</td>
	  	    <td class='right'>%0.02f €</td>
	  	    </tr>",
	  	    $formattedDate, $name, number_format($purchaseQuantity,2), $realQuantity, $price, $paid);
	  	    
	
			
		} else {
			
			$provRow = sprintf("
			<tr class='green'>
	  	    <td>%s</td>
	  	    <td>Pago</td>
	  	    <td class='right'></td>
	  	    <td class='right'></td>
	  	    <td class='right'></td>
	  	    <td class='right'>%0.02f €</td>
	  	    </tr>",
	  	    $formattedDate, $paid);
	  	    
			
		}
		
		echo $provRow;	 
		
	}

displayFooter(); ?>