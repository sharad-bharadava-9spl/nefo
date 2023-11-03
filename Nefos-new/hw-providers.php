<?php
	//Created by Konstant for Task-14954900 on 12/10/2021
	require_once 'cOnfig/connection.php';
	require_once 'cOnfig/viewv6.php';
	require_once 'cOnfig/authenticate.php';
	require_once 'cOnfig/languages/common.php';
	
	session_start();
	$accessLevel = '3';
	
	// Authenticate & authorize
	authorizeUser($accessLevel);
	
	// Query to look up categories
	$selectProviders = "SELECT * from hw_providers ORDER by id ASC";
		try
		{
			$resultP = $pdo3->prepare("$selectProviders");
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
	pageStart('HW Providers', NULL, $deleteCategoryScript, "pproducts", "admin", 'HW Providers', $_SESSION['successMessage'], $_SESSION['errorMessage']);
	
	
?>

<center><a href="new-hw-provider.php" class="cta1" style='width: 200px;'><?php echo $lang['new-provider']; ?></a></center>

	 <table class="default">
	  <thead>
	   <tr>
	    <th>Provider name</th>
	    <th>Contact person</th>
	    <th>E-mail</th>
	    <th>Phone number(s)</th>
	    <th>Products offered</th>
	    <th>Saldo</th>
	   </tr>
	  </thead>
	  <tbody>
	  
	  <?php

	while ($provider = $resultP->fetch()) {
	
			$providerid = $provider['id'];
	
			$selectPurchases2 = "SELECT SUM(paid) FROM b_purchases WHERE hw_provider = $providerid";
			
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
				
				
			$selectTotal = "SELECT purchasePrice, purchaseQuantity FROM b_purchases WHERE hw_provider = $providerid";
				
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
			
			
			$selectTotal2 = "SELECT SUM(amount) FROM hw_providerpayments WHERE providerid = $providerid";
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
				
			
			
			$totCredit = $totalPaid - $totalPurchased;

			
			//$categoryid = $category['id'];
	
	
			$provider_row =	sprintf("
		  	  <tr>
		  	   <td>%s</td>
		  	   <td>%s</td>
		  	   <td>%s</td>
			   <td>%s</td>
			   <td><a class='cta4' href='hw-provider.php?id=%d' style='padding: 5px 5px; font-size:10px;'>Products Offered</a></td>
			   <td>%s &euro;</td>
			  </tr>",
			 	$provider['name'], $provider['contact'], $provider['email'], $provider['phone_numbers'], $provider['id'], $totCredit
			  );
			  echo $provider_row;
  	}
?>



	 </tbody>
	 </table>

<?php  displayFooter(); ?>
