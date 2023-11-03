<?php
	
	require_once 'cOnfig/connection.php';
	require_once 'cOnfig/view.php';
	require_once 'cOnfig/authenticate.php';
	require_once 'cOnfig/languages/common.php';
	
	session_start();
	$accessLevel = '3';
	
	// Authenticate & authorize
	authorizeUser($accessLevel);
	
	// Did this page re-submit with a form? If so, check & store details	
	if (isset($_POST['amount'])) {
		
		$providerid = $_POST['providerid'];
		$amount = $_POST['amount'];
		$comment = str_replace("'","\'",str_replace('%', '&#37;', trim($_POST['comment'])));
		$registertime = date('Y-m-d H:i:s');
		
		// Look up provider credit
		$userCredit = "SELECT credit FROM hw_providers WHERE id = '$providerid'";
		try
		{
			$result = $pdo3->prepare("$userCredit");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
			$oldCredit = $row['credit'];

		$newCredit = $amount + $oldCredit;
		
		// Query to add to Donations table
		 $query = sprintf("INSERT INTO hw_providerpayments (providerid, paymentTime, amount, comment) VALUES ('%d', '%s', '%f', '%s');",
		  $providerid, $registertime, $amount, $comment);
		try
		{
			$result = $pdo3->prepare("$query")->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
			
		// Query to update user profile
		$updateUser = sprintf("UPDATE hw_providers SET credit = '%f' WHERE id = '%d';",
			$newCredit,
			$providerid
			);
		try
		{
			$result = $pdo3->prepare("$updateUser")->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
			
		// On success: redirect.
		$_SESSION['successMessage'] = "Pago hecho con éxito!";
		header("Location: hw-provider.php?id=$providerid");
		exit();
	}
	/***** FORM SUBMIT END *****/
	

	if (isset($_GET['providerid'])) {
		$providerid = $_GET['providerid'];
	} else {
		handleError($lang['error-nomemberid'],"");
	}
		
	
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
	try
	{
		$result = $pdo3->prepare("$selectTotal");
		$result->execute();
	}
	catch (PDOException $e)
	{
			$error = 'Error fetching user: ' . $e->getMessage();
			echo $error;
			exit();
	}
	
	while ($onePurchase = $result->fetch()) {
	
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
		
	
	// Query to look up provider
	$providerDetails = "SELECT registered, name, credit FROM hw_providers WHERE id = $providerid";
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
		
	$selectPurchases = "SELECT purchaseDate, purchaseid, category, productid, purchaseQuantity, purchasePrice, paid FROM b_purchases WHERE hw_provider = $providerid ORDER BY purchaseDate DESC";
		try
		{
			$result = $pdo3->prepare("$selectPurchases");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	


	pageStart($lang['providers'], NULL, $deleteUserScript, "pmembership", "pmembership", $lang['providers'], $_SESSION['successMessage'], $_SESSION['errorMessage']); 
	
	

?>


<br /><br /><br />

<center><div id='productoverview'>
 
 <table style="display: inline-block; vertical-align: top;">
  <tr>
   <td><?php echo $lang['global-name']; ?>:</td>
   <td class='yellow fat'><?php echo $name; ?></td>
  </tr>
  <tr>
   <td><?php echo $lang['global-credit']; ?>:</td>
   <td class='yellow fat'><?php echo number_format($totCredit,2); ?> €</td>
  </tr>
 </table>
</div></center>

 <br /><br />


 <div id="overviewWrap">
 <div class="overview" style="padding: 10px 50px;">
 <form id="registerForm" action="" method="POST">
 
 <h5>Pagar proveedor</h5>
  
  <input type="hidden" name="providerid" value="<?php echo $providerid; ?>" />
  <input type="number" lang="nb" name="amount" placeholder="&euro;" class="fourDigit" step="0.01" /><br />
  <textarea name="comment" placeholder="<?php echo $lang['global-comment']; ?>?"></textarea><br /><br />
  
 <button class='oneClick' name='oneClick' type="submit"><?php echo $lang['global-confirm']; ?></button>
 </form>
 </div>
 
	 <br /><br />
   
<?php displayFooter(); ?>
