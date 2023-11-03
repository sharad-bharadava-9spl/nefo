<?php
	
	require_once 'cOnfig/connection.php';
	require_once 'cOnfig/view.php';
	require_once 'cOnfig/authenticate.php';
	require_once 'cOnfig/languages/common.php';
	
	session_start();
	$accessLevel = '3';
	
	// Authenticate & authorize
	authorizeUser($accessLevel);
	
		// Query to look up sales
	$selectSales = "SELECT saleid, saletime, userid, amount, amountpaid, quantity, units, adminComment, creditBefore, creditAfter FROM sales ORDER by saletime DESC";

	$result = mysql_query($selectSales)
		or handleError($lang['error-loadsales'],"Error loading sale from db: " . mysql_error());
		
	
	$deleteSaleScript = <<<EOD
function delete_sale(saleid) {
	if (confirm("{$lang['confirm-deletedispense']}")) {
				window.location = "uTil/delete-dispense.php?saleid=" + saleid;
				}
}
EOD;
	pageStart($lang['title-dispenses'], NULL, $deleteSaleScript, "psales", "sales admin", $lang['global-dispensescaps'], $_SESSION['successMessage'], $_SESSION['errorMessage']);
	
	
?>

<center><a href="new-dispense.php" class="cta"><?php echo $lang['global-dispense']; ?></a></center>

	 <table class="default">
	  <thead>
	   <tr>
	    <th><?php echo $lang['global-time']; ?></th>
	    <th><?php echo $lang['global-member']; ?></th>
	    <th><?php echo $lang['global-category']; ?></th>
	    <th><?php echo $lang['global-product']; ?></th>
	    <th><?php echo $lang['global-quantity']; ?></th>
	    <th>&euro;</th>
	    <th>Total g</th>
	    <th>Total u</th>
	    <th>Total &euro;</th>
	    <th><?php echo $lang['dispense-oldcredit']; ?></th>
	    <th><?php echo $lang['dispense-newcredit']; ?></th>
	    <th><?php echo $lang['global-delete']; ?></th>
	   </tr>
	  </thead>
	  <tbody>
	  
	  <?php
while ($sale = mysql_fetch_array($result)) {

		$formattedDate = date("d M H:i:s", strtotime($sale['saletime']."+$offsetSec seconds"));
		$saleid = $sale['saleid'];
		$userid = $sale['userid'];
		$quantity = $sale['quantity'];
		$units = $sale['units'];
		$credit = $sale['creditBefore'];
		$newcredit = $sale['creditAfter'];
		
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
		
			// Make unpaid rows red:
			if ($amountpaid < $amount) {
				echo "<tr class='negative'>";
			} else {
				echo "<tr>";
			}
	   
		echo "
  	   <td class='clickableRow' href='dispense.php?saleid={$saleid}'>{$formattedDate}</td>
  	   <td class='clickableRow' href='dispense.php?saleid={$saleid}'>#{$memberno} - {$first_name}</td>
  	   <td class='clickableRow' href='dispense.php?saleid={$saleid}'>";
		while ($onesale = mysql_fetch_array($onesaleResult)) {
			if ($onesale['category'] == 1) {
				$category = $lang['global-flower'];
			} else if ($onesale['category'] == 2) {
				$category = $lang['global-extract'];
			} else {
				
				// Query to look for category
				$categoryDetails = "SELECT name FROM categories WHERE id = {$onesale['category']}";
				
				$resultCat = mysql_query($categoryDetails)
					or handleError($lang['error-errorloadingflower'],"Error loading flower: " . mysql_error());
				
				$row = mysql_fetch_array($resultCat);
					$category = $row['name'];
			}
				
			echo $category . "<br />";
		}
		echo "</td><td class='clickableRow' href='dispense.php?saleid={$saleid}'>";
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
		echo "</td><td class='clickableRow right' href='dispense.php?saleid={$saleid}'>";
		while ($onesale = mysql_fetch_array($onesaleResult3)) {
			if ($onesale['category'] < 3) {
				echo number_format($onesale['quantity'],2) . " g<br />";
			} else {
				echo number_format($onesale['quantity'],2) . " u<br />";
			}
		}
		echo "</td><td class='clickableRow right' href='dispense.php?saleid={$saleid}'>";
		while ($onesale = mysql_fetch_array($onesaleResult4)) {
			echo number_format($onesale['amount'],2) . " <span class='smallerfont'>&euro;</span><br />";
		}
		echo "</td>";
		
		$quantity = number_format($quantity,2);
		$amount = number_format($amount,2);
		if ($credit == NULL && $oldcredit == NULL) {
			echo "
			<td class='clickableRow right' href='dispense.php?saleid={$saleid}'><strong>{$quantity} g</strong></td>
			<td class='clickableRow right' href='dispense.php?saleid={$saleid}'><strong>{$units} u</strong></td>
			<td class='clickableRow right' href='dispense.php?saleid={$saleid}'><strong>{$amount} &euro;</strong></td>
			<td class='clickableRow right' href='dispense.php?saleid={$saleid}'></td>
			<td class='clickableRow right' href='dispense.php?saleid={$saleid}'></td>
			<td style='text-align: center;'><a href='javascript:delete_sale({$saleid})'><img src='images/delete.png' height='15' title='{$lang['dispenses-deletesale']}' /></a></td></tr>
			";
			
		} else {
			
			echo "
			<td class='clickableRow right' href='dispense.php?saleid={$saleid}'><strong>{$quantity} g</strong></td>
			<td class='clickableRow right' href='dispense.php?saleid={$saleid}'><strong>{$units} u</strong></td>
			<td class='clickableRow right' href='dispense.php?saleid={$saleid}'><strong>{$amount} &euro;</strong></td>
			<td class='clickableRow right' href='dispense.php?saleid={$saleid}'>{$credit} &euro;</td>
			<td class='clickableRow right' href='dispense.php?saleid={$saleid}'>{$newcredit} &euro;</td>
			<td style='text-align: center;'><a href='javascript:delete_sale({$saleid})'><img src='images/delete.png' height='15' title='{$lang['dispenses-deletesale']}' /></a></td></tr>
			";
		
		}

	}
?>

	 </tbody>
	 </table>
	 
<?php  displayFooter(); ?>
