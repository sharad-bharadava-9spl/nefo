<?php
	
	require_once 'cOnfig/connection.php';
	require_once 'cOnfig/view.php';
	require_once 'cOnfig/authenticate.php';
	require_once 'cOnfig/languages/common.php';
	
	session_start();
	$accessLevel = '3';
	
	// Authenticate & authorize
	authorizeUser($accessLevel);
	
	// Query to look up categories
	$selectCats = "SELECT id, registered, name, comment, providernumber, credit from b_providers ORDER by providernumber ASC";

	$resultCats = mysql_query($selectCats)
		or handleError($lang['error-loadflowers'],"Error loading flower from db: " . mysql_error());
		
	
	$deleteCategoryScript = <<<EOD
function delete_provider(providerid) {
	
		if (confirm("Esta seguro que quieres borrar este proveedor?  No se puede volver a esta pagina despues!")) {
			window.location = "uTil/bar-delete-provider.php?providerid=" + providerid;
		}
		
}
EOD;
	pageStart($lang['providers'], NULL, $deleteCategoryScript, "pproducts", "admin", $lang['providers'], $_SESSION['successMessage'], $_SESSION['errorMessage']);
	
	
?>

<center><a href="bar-new-provider.php" class="cta"><?php echo $lang['new-provider']; ?></a></center>

	 <table class="default">
	  <thead>
	   <tr>
	    <th>#</th>
	    <th><?php echo $lang['global-name']; ?></th>
	    <th>Saldo</th>
	    <th></th>
	    <th></th>
	   </tr>
	  </thead>
	  <tbody>
	  
	  <?php

while ($category = mysql_fetch_array($resultCats)) {
	
	$providerid = $category['id'];
	
	$selectPurchases2 = "SELECT SUM(paid) FROM b_purchases WHERE provider = $providerid";
	
	$resultPurchases2 = mysql_query($selectPurchases2)
		or handleError($lang['error-loadpurchases'],"Error loading purchase from db: " . mysql_error());
		
	$rowX = mysql_fetch_array($resultPurchases2);
		$purchasePaid = $rowX['SUM(paid)'];
		
		
	$selectTotal = "SELECT purchasePrice, purchaseQuantity FROM b_purchases WHERE provider = $providerid";

	$resultTotal = mysql_query($selectTotal)
		or handleError($lang['error-loadpurchases'],"Error loading purchase from db: " . mysql_error());
		
	$totalPurchased = 0;
		
	while ($onePurchase = mysql_fetch_array($resultTotal)) {
	
		$purchasePrice = $onePurchase['purchasePrice'];
		$purchaseQuantity = $onePurchase['purchaseQuantity'];
		
		$thisPurchase = $purchasePrice * $purchaseQuantity;
		$totalPurchased = $totalPurchased + $thisPurchase;
		
	}
	
	
	$selectTotal2 = "SELECT SUM(amount) FROM b_providerpayments WHERE providerid = $providerid";

	$resultTotal2 = mysql_query($selectTotal2)
		or handleError($lang['error-loadpurchases'],"Error loading purchase from db: " . mysql_error());
		
	$row = mysql_fetch_array($resultTotal2);
		$totalPaid = $row['SUM(amount)'] + $purchasePaid;
		
	$selectTotal3 = "SELECT SUM(price), SUM(paid) FROM b_productmovements WHERE provider = $providerid";

	$resultTotal3 = mysql_query($selectTotal3)
		or handleError($lang['error-loadpurchases'],"Error loading purchase from db: " . mysql_error());
		
	$row = mysql_fetch_array($resultTotal3);
		$reloadPrice = $row['SUM(price)'];
		$reloadPaid = $row['SUM(paid)'];
		
	$totalPurchased = $totalPurchased + $reloadPrice;
	$totalPaid = $totalPaid + $reloadPaid;
	
	
	$totCredit = $totalPaid - $totalPurchased;

	
	$categoryid = $category['id'];
	
	if ($category['comment'] != '') {
		
		$commentRead = "
		                <img src='images/comments.png' id='comment$categoryid' /><div id='helpBox$categoryid' class='helpBox'>{$category['comment']}</div>
		                <script>
		                  	$('#comment$categoryid').on({
						 		'mouseover' : function() {
								 	$('#helpBox$categoryid').css('display', 'block');
						  		},
						  		'mouseout' : function() {
								 	$('#helpBox$categoryid').css('display', 'none');
							  	}
						  	});
						</script>
		                ";
		
	} else {
		
		$commentRead = "";
		
	}

	
	$flower_row =	sprintf("
  	  <tr>
  	   <td class='clickableRow' href='bar-provider.php?providerid=%d'>%03d</td>
  	   <td class='clickableRow' href='bar-provider.php?providerid=%d'>%s</td>
  	   <td class='clickableRow right' href='bar-provider.php?providerid=%d'>%0.02f €</td>
	   <td class='relative'>$commentRead</td>
  	   <td style='text-align: center;'><a href='bar-edit-provider.php?providerid=$categoryid'><img src='images/edit.png' height='15' title='Edit provider' /></a>&nbsp; &nbsp;<a href='javascript:delete_provider(%d)'><img src='images/delete.png' height='15' title='Delete provider' /></a></td>
	  </tr>",
	  $categoryid, $category['providernumber'], $categoryid, $category['name'], $categoryid, $totCredit, $categoryid
	  );
	  echo $flower_row;
  }
?>



	 </tbody>
	 </table>

<?php  displayFooter(); ?>
