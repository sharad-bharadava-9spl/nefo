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

	$result = mysql_query($selectSale)
		or handleError($lang['error-loadsales'],"Error loading sale from db: " . mysql_error());
	
	pageStart($lang['print-receipt'], NULL, NULL, "pprint", "Sale", $lang['print-receiptD'], $_SESSION['successMessage'], $_SESSION['errorMessage']);
?>

	  
	  <?php
while ($sale = mysql_fetch_array($result)) {
	
		$formattedDate = date("d M H:i", strtotime($sale['saletime']."+$offsetSec seconds"));
		$saleid = $sale['saleid'];
		$userid = $sale['userid'];
		$credit = $sale['creditBefore'];
		$newcredit = $sale['creditAfter'];
		$quantity = $sale['quantity'];
		$units = $sale['units'];
		
		
		$amount = $sale['amount'];
		$amountpaid = $sale['amountpaid'];
		$userLookup = "SELECT first_name, memberno FROM users WHERE user_id = {$userid}";
		$userResult = mysql_query($userLookup)
			or handleError($lang['error-loadproductdata'],"Error loading product: " . mysql_error());
			
	    $row = mysql_fetch_array($userResult);
		$first_name = $row['first_name'];
		$memberno = $row['memberno'];
				
		$selectoneSale = "SELECT d.category, d.productid, d.quantity, d.amount FROM salesdetails d, sales s WHERE d.saleid = {$saleid} and s.saleid = d.saleid";
		$onesaleResult = mysql_query($selectoneSale)
			or handleError($lang['error-loadproductdata'],"Error loading product: " . mysql_error());
		$onesaleResult2 = mysql_query($selectoneSale)
			or handleError($lang['error-loadproductdata'],"Error loading product: " . mysql_error());
		$onesaleResult3 = mysql_query($selectoneSale)
			or handleError($lang['error-loadproductdata'],"Error loading product: " . mysql_error());
		$onesaleResult4 = mysql_query($selectoneSale)
			or handleError($lang['error-loadproductdata'],"Error loading product: " . mysql_error());
		$onesaleResult5 = mysql_query($selectoneSale)
			or handleError($lang['error-loadproductdata'],"Error loading product: " . mysql_error());
			   
		echo "
		<br />
<div id='profilearea'>
<span class='profilefirst left'>#$memberno - $first_name<br />
$formattedDate</span><br />
</div>
		<br /><br />
	<br /><br />

	 <table id='' class='default'>
	  <thead>
	   <tr>
	    <th>{$lang['global-product']}</th>
	    <th>{$lang['global-quantity']}</th>
	    <th>{$_SESSION['currencyoperator']}</th>
	   </tr>
	  </thead>
	  <tbody>
<tr><td>";

//while loop goes here
		while ($onesale = mysql_fetch_array($onesaleResult2)) {
			
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
		$productResult = mysql_query($selectProduct)
			or handleError($lang['error-loadflowerdata'],"Error loading flower: " . mysql_error());
			
	    $row = mysql_fetch_array($productResult);
	    
		if ($row['breed2'] != '') {
			$name = $row['name'] . " x " . $row['breed2'];
		} else { 
			$name = $row['name'];
		}
		

			echo $name . "<br />";

}
		echo "</td><td class='right'>";
		while ($onesale = mysql_fetch_array($onesaleResult3)) {
			if ($onesale['category'] < 3) {
				echo number_format($onesale['quantity'],2) . " g<br />";
			} else {
				echo number_format($onesale['quantity'],2) . " u<br />";
			}
		}
		echo "</td><td class='right'>";
		while ($onesale = mysql_fetch_array($onesaleResult4)) {
			echo number_format($onesale['amount'],2) . " <span class='smallerfont'>{$_SESSION['currencyoperator']}</span><br />";
		}
		
		
		echo "
		 </td>
		</tr>
		<tr>
		 <td style='border-bottom: 2px solid #333;'><strong>TOTAL:</strong></td>
		 <td class='right' colspan='2' style='border-bottom: 2px solid #333;'><strong>$amount</strong><span class='smallerfont'><strong>{$_SESSION['currencyoperator']}</strong></span>
		 </td>
		</tr>
	   </table>


  </div> <!-- END RIGHTPANE -->
 </div> <!-- END DETAILEDINFO -->
 

 ";
}
   displayFooter(); ?>
   
   
