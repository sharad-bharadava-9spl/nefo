<?php
	
	require_once 'cOnfig/connection.php';
	require_once 'cOnfig/view-print.php';
	require_once 'cOnfig/authenticate.php';
	require_once 'cOnfig/languages/common.php';
	
	session_start();
	$accessLevel = '3';
	
	// Authenticate & authorize
	authorizeUser($accessLevel);
	
	// Get the sale ID
	if (isset($_GET['saleid'])) {
		$saleid = $_GET['saleid'];
	} else {
		handleError($lang['error-nosaleid'],"");
	}
	// Query to look up sale
	$selectSale = "SELECT saleid, saletime, userid, amount, amountpaid, quantity, units, adminComment, creditBefore, creditAfter FROM sales WHERE saleid = $saleid";
		try
		{
			$resultsX = $pdo3->prepare("$selectSale");
			$resultsX->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
	
	pageStart($lang['print-receipt'], NULL, NULL, "pprint", "Sale", $lang['print-receiptD'], $_SESSION['successMessage'], $_SESSION['errorMessage']);
?>

	  
	  <?php
	  
		while ($sale = $resultsX->fetch()) {
	
		$formattedDate = date("d M H:i", strtotime($sale['saletime']."+$offsetSec seconds"));
		$saletime = $sale['saletime'];
		$saleid = $sale['saleid'];
		$userid = $sale['userid'];
		$credit = $sale['creditBefore'];
		$newcredit = $sale['creditAfter'];
		$quantity = $sale['quantity'];
		$units = $sale['units'];
		
		
		$amount = $sale['amount'];
		$amountpaid = $sale['amountpaid'];
		
		$userLookup = "SELECT first_name, memberno FROM users WHERE user_id = {$userid}";
		try
		{
			$result = $pdo3->prepare("$userLookup");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
		$first_name = $row['first_name'];
		$memberno = $row['memberno'];
		
		$userLookup = "SELECT operator FROM log WHERE logtime = '$saletime'";
		try
		{
			$result = $pdo3->prepare("$userLookup");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
		$operator = $row['operator'];
				
		$userLookup = "SELECT first_name, memberno FROM users WHERE user_id = {$operator}";
		try
		{
			$result = $pdo3->prepare("$userLookup");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
		$opname = $row['first_name'];
		$opno = $row['memberno'];
		
		$selectoneSale = "SELECT d.category, d.productid, d.quantity, d.amount FROM salesdetails d, sales s WHERE d.saleid = {$saleid} and s.saleid = d.saleid";
		try
		{
			$onesaleResult = $pdo3->prepare("$selectoneSale");
			$onesaleResult->execute();
			$onesaleResult2 = $pdo3->prepare("$selectoneSale");
			$onesaleResult2->execute();
			$onesaleResult3 = $pdo3->prepare("$selectoneSale");
			$onesaleResult3->execute();
			$onesaleResult4 = $pdo3->prepare("$selectoneSale");
			$onesaleResult4->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
			   
		echo "
<strong>
</strong><br /><br />
<table>
 <tr>
  <td><strong>Document no.:</strong></td>
  <td>$saleid</td>
 </tr>
 <tr>
  <td><strong>Data:</strong></td>
  <td>$formattedDate</td>
 </tr>
 <tr>
  <td><strong>Ates per:</strong></td>
  <td>$opname</td>
 </tr>
 <tr>
  <td><strong>Soci:</strong></td>
  <td>#$memberno - $first_name</td>
 </tr>
</table>

	<br /><br />

	 <table id='' class='default'>
	  <thead>
	   <tr>
	    <th style='text-align: left;'>{$lang['global-product']}</th>
	    <th>{$lang['global-quantity']}</th>
	    <th>&euro;</th>
	   </tr>
	  </thead>
	  <tbody>
<tr><td>";

//while loop goes here
		while ($onesale = $onesaleResult2->fetch()) {
			
   			$productid = $onesale['productid'];
   			
			
	// Determine product type, and assign query variables accordingly
	if ($onesale['category'] == 1) {
		$purchaseCategory = 'Flower';
		$queryVar = ', breed2';
		$prodSelect = 'flower';
		$prodJoin = 'flowerid';
	} else if ($onesale['category'] == 2) {
		$purchaseCategory = 'Extract';
		$queryVar = '';
		$prodSelect = 'extract';
		$prodJoin = 'extractid';
	} else {
		$purchaseCategory = $category;
		$queryVar = '';
		$prodSelect = 'products';
		$prodJoin = "productid";
	}
	
		$selectProduct = "SELECT name{$queryVar} FROM {$prodSelect} WHERE ({$prodJoin} = {$productid})";
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
	    
		if ($row['breed2'] != '') {
			$name = $row['name'] . " x " . $row['breed2'];
		} else { 
			$name = $row['name'];
		}
		

			echo $name . "<br />";

}
		echo "</td><td class='right'>";
		while ($onesale = $onesaleResult3->fetch()) {
			if ($onesale['category'] < 3) {
				echo number_format($onesale['quantity'],2) . " g<br />";
			} else {
				echo number_format($onesale['quantity'],2) . " u<br />";
			}
		}
		echo "</td><td class='right'>";
		while ($onesale = $onesaleResult4->fetch()) {
			echo number_format($onesale['amount'],2) . " <span class='smallerfont'>&euro;</span><br />";
		}
		
		
		echo "
		 </td>
		</tr>
		<!--<tr>
		 <td style='border-bottom: 2px solid #333;'><strong>TOTAL:</strong></td>
		 <td class='right' colspan='2' style='border-bottom: 2px solid #333;'><strong>$amount</strong><span class='smallerfont'><strong>&euro;</strong></span>
		 </td>
		</tr>-->
	   </table>


  </div> <!-- END RIGHTPANE -->
 </div> <!-- END DETAILEDINFO -->
 

 ";
}
   displayFooter(); ?>
   
   
