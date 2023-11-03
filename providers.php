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
	$selectCats = "SELECT id, registered, name, comment, providernumber, credit from providers ORDER by providernumber ASC";
		try
		{
			$resultP = $pdo3->prepare("$selectCats");
			$resultP->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		
	
	$deleteCategoryScript = <<<EOD
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
function delete_provider(providerid) {
	
		if (confirm("Esta seguro que quieres borrar este proveedor?  No se puede volver a esta pagina despues!")) {
			window.location = "uTil/delete-provider.php?providerid=" + providerid;
		}
		
}
EOD;
	pageStart($lang['providers'], NULL, $deleteCategoryScript, "pproducts", "admin", $lang['providers'], $_SESSION['successMessage'], $_SESSION['errorMessage']);
	
	
?>

<center><a href="new-provider.php" class="cta1" style='width: 200px;'><?php echo $lang['new-provider']; ?></a></center>

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

		while ($category = $resultP->fetch()) {
	
	$providerid = $category['id'];
	
	$selectPurchases2 = "SELECT SUM(paid) FROM purchases WHERE provider = $providerid";
	
		try
		{
			$result = $pdo3->prepare("$selectPurchases2");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$rowX = $result->fetch();
		$purchasePaid = $rowX['SUM(paid)'];
		
		
	$selectTotal = "SELECT purchasePrice, purchaseQuantity FROM purchases WHERE provider = $providerid";
		
	$totalPurchased = 0;
	
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
			$result = $pdo3->prepare("$selectTotal2");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
		
		$totalPaid = $row['SUM(amount)'] + $purchasePaid;
		
	$selectTotal3 = "SELECT SUM(price), SUM(paid) FROM productmovements WHERE provider = $providerid";
		try
		{
			$result = $pdo3->prepare("$selectTotal3");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
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
  	   <td class='clickableRow' href='provider.php?providerid=%d'>%03d</td>
  	   <td class='clickableRow' href='provider.php?providerid=%d'>%s</td>
  	   <td class='clickableRow right' href='provider.php?providerid=%d'>%0.02f {$_SESSION['currencyoperator']}</td>
	   <td><span class='relativeitem'>$commentRead</span></td>
  	   <td style='text-align: center;'><a href='javascript:delete_provider(%d)'><img src='images/delete.png' height='15' title='Delete category' /></a></td>
	  </tr>",
	  $categoryid, $category['providernumber'], $categoryid, $category['name'], $categoryid, $totCredit, $categoryid
	  );
	  echo $flower_row;
  }
?>



	 </tbody>
	 </table>

<?php  displayFooter(); ?>
