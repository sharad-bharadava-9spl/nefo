<?php
	
	require_once 'cOnfig/connection.php';
	require_once 'cOnfig/view.php';
	require_once 'cOnfig/authenticate.php';
	require_once 'cOnfig/languages/common.php';
	
	session_start();
	$accessLevel = '3';
	
	// Authenticate & authorize
	authorizeUser($accessLevel);

	getSettings();
	
	// Did this page re-submit with a form? If so, check & store details	
	if (isset($_POST['amount'])) {
		
		$providerid = $_POST['providerid'];
		$amount = $_POST['amount'];
		$comment = str_replace("'","\'",str_replace('%', '&#37;', trim($_POST['comment'])));
		$registertime = date('Y-m-d H:i:s');
		
		// Look up provider credit
		$userCredit = "SELECT credit FROM providers WHERE id = '$providerid'";
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
		 $query = sprintf("INSERT INTO providerpayments (providerid, paymentTime, amount, comment) VALUES ('%d', '%s', '%f', '%s');",
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
		$updateUser = sprintf("UPDATE providers SET credit = '%f' WHERE id = '%d';",
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
		$_SESSION['successMessage'] = $lang['payment-done'];
		header("Location: provider.php?providerid=$providerid");
		exit();
	}
	/***** FORM SUBMIT END *****/
	

	if (isset($_GET['providerid'])) {
		$providerid = $_GET['providerid'];
	} else {
		handleError($lang['error-nomemberid'],"");
	}
		
	
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

	$row = $result->fetch();
		$purchasePaid = $row['SUM(paid)'];
		
		
	$selectTotal = "SELECT purchasePrice, purchaseQuantity FROM purchases WHERE provider = $providerid";
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
		
	$totCredit = $totalPaid - $totalPurchased;
		
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
		
	pageStart($lang['providers'], NULL, $deleteUserScript, "pmembership", "pmembership", $lang['providers'], $_SESSION['successMessage'], $_SESSION['errorMessage']); 
	
	

?>

<center>
<div id="mainbox-no-width">
 <div id="mainboxheader">
  <?php echo $lang['pay-provider']; ?>
 </div>
 <div class='boxcontent'>
 
 <table class='default'>
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
   <td class='yellow fat'><?php echo number_format($totCredit,2); ?> <?php echo $_SESSION['currencyoperator'] ?></td>
  </tr>
 </table>
 <br />


 <form id="registerForm" action="" method="POST">
   
  <input type="hidden" name="providerid" value="<?php echo $providerid; ?>" />
  <input type="number" lang="nb" name="amount" placeholder="<?php echo $_SESSION['currencyoperator'] ?>" class="fourDigit defaultinput" step="0.01" /><br />
  <textarea name="comment" placeholder="<?php echo $lang['global-comment']; ?>?" class='defaultinput' style='height: 100px;'></textarea><br /><br />
  
 <button class='cta1' name='oneClick' type="submit"><?php echo $lang['global-confirm']; ?></button>
 </form>
 </div>
 </div>
 
   
<?php displayFooter();
