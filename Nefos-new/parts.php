<?php
	
	require_once 'cOnfig/connection.php';
	require_once 'cOnfig/view.php';
	require_once 'cOnfig/authenticate.php';
	require_once 'cOnfig/languages/common.php';
	
	session_start();
	$accessLevel = '3';
	
	// Authenticate & authorize
	authorizeUser($accessLevel);
		
	$domain = $_SESSION['domain'];
	
	getSettings();
	
	
	// Does purchase ID exist?
	if (!$_GET['purchaseid']) {
		echo $lang['error-nopurchselected'];
		exit();
	} else  {
		$purchaseid = $_GET['purchaseid'];
		$purchaseidTop = $_GET['purchaseid'];
	}
	
	if (isset($_POST['submityes'])) {
		
		// Delete all entries for this product
		$query = "DELETE from parts WHERE purchaseid = $purchaseid";
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
		
		foreach($_POST['parts'] as $sale) {
			
			$product = $sale['product'];
			$quantity = $sale['quantity'];
			
			if ($quantity > 0) {
						
				$query = "INSERT INTO parts (purchaseid, product, quantity) VALUES ('$purchaseid', '$product', '$quantity')";
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
			}
		}
		
		$_SESSION['successMessage'] = "Parts updated succesfully!";
		header("Location: part-map.php");
		exit();
	}


	// Query to look for purchase
	$purchaseDetails = "SELECT category, productid, purchaseDate, purchasePrice, salesPrice, purchaseQuantity, realQuantity, adminComment, estClosing, closingComment, closedAt, inMenu, growType, closingDate, photoExt, tupperWeight, medDiscount FROM purchases WHERE purchaseid = $purchaseid";
	try
	{
		$result = $pdo3->prepare("$purchaseDetails");
		$result->execute();
	}
	catch (PDOException $e)
	{
			$error = 'Error fetching user: ' . $e->getMessage();
			echo $error;
			exit();
	}

	$row = $result->fetch();
		$category = $row['category'];
		$productid = $row['productid'];
		$purchaseDate = $row['purchaseDate'];
		$purchasePrice = $row['purchasePrice'];
		$salesPrice = $row['salesPrice'];
		$purchaseQuantity = $row['purchaseQuantity'];
		$realQuantity = $row['realQuantity'];
		$adminComment = $row['adminComment']; // Purchase comment, really
		$estClosing = $row['estClosing'];
		$closingComment = $row['closingComment']; // Only active when product closed (if even then)
		$closedAt = $row['closedAt'];
		$closingDate = date("d M H:i", strtotime($row['closingDate'] . "+$offsetSec seconds"));
		$inMenu = $row['inMenu'];
		$growtype = $row['growType'];
		$photoExt = $row['photoExt'];
		$purchasePriceTotal = number_format($purchasePrice * $purchaseQuantity,2);
		$salesPriceTotal = number_format($salesPrice * $realQuantity,2);
		$tupperWeight = $row['tupperWeight'];
		$medDiscount = $row['medDiscount'];
		
	// Look up category name
	$query = "SELECT name FROM categories WHERE id = $category";
	try
	{
		$result = $pdo3->prepare("$query");
		$result->execute();
	}
	catch (PDOException $e)
	{
			$error = 'Error fetching user: ' . $e->getMessage();
			echo $error;
			exit();
	}

	$row = $result->fetch();
		$categoryname = $row['name'];
		
	// Look up product name
	$query = "SELECT name FROM products WHERE productid = $productid";
	try
	{
		$result = $pdo3->prepare("$query");
		$result->execute();
	}
	catch (PDOException $e)
	{
			$error = 'Error fetching user: ' . $e->getMessage();
			echo $error;
			exit();
	}

	$row = $result->fetch();
		$productname = $row['name'];




	$deleteUserScript = <<<EOD
	
	 $(document).ready(function() {
		    
	  $( function() {
	    $( "#datepicker" ).datepicker({
			dateFormat: "dd-mm-yy"
	    });
	  });
	  
	  $( function() {
	    $( "#datepicker2" ).datepicker({
			dateFormat: "dd-mm-yy"
	    });
	  });
	  
			$.tablesorter.addParser({
			  id: 'dates',
			  is: function(s) { return false },
			  format: function(s) {
			    var dateArray = s.split('-');
			    return dateArray[2].substring(0,4) + dateArray[1] + dateArray[0];
			  },
			  type: 'numeric'
			});
			$('#mvtTable').tablesorter({
				usNumberFormat: true,
				headers: {
					0: {
						sorter: "dates"
					},
					2: {
						sorter: "currency"
					},
					3: {
						sorter: "currency"
					}
				}
			}); 
	  
     });


function delete_movement(movementid, purchaseid) {
	if (confirm("{$lang['confirm-deletemovement']}")) {
				window.location = "uTil/delete-movement.php?movement_id=" + movementid + "&purchaseid=" + purchaseid;
				}
}
function reset_stash(stashtype,purchaseid, stashOffset) {
	if (confirm("Are you sure you want to reset the stash? " + stashOffset + "g will be adjusted!")) {
				window.location = "uTil/reset-stash.php?stashtype=" + stashtype + "&purchaseid=" + purchaseid + "&stashOffset=" + stashOffset;
				}
}
function delete_purchase(purchaseid) {
	if (confirm("{$lang['confirm-deletepurchase']}")) {
				window.location = "uTil/delete-purchase.php?purchaseid=" + purchaseid;
				}
}
EOD;


		pageStart("Parts", NULL, $deleteUserScript, "ppurchase", "admin ppage", "Parts", $_SESSION['successMessage'], $_SESSION['errorMessage']); 
	
		
?>

<div id='productoverview'>
  <a href='change-image.php?purchaseid=<?php echo $purchaseid; ?>'><img src='https://ccsnubev2.com/CCS/images/purchases/<?php echo $purchaseid . "." . $photoExt; ?>' height='70' style='display: inline; vertical-align: middle;' /></a>
 <table style="display: inline-block; vertical-align: top; <?php if ($category == '2') { echo 'margin-top: 9px;'; } ?>">
  <tr>
   <td><?php echo $lang['global-category']; ?>:</td>
   <td class='yellow fat'><?php echo $categoryname; ?></td>
  </tr>
  <tr>
   <td>Product:</td>
   <td class='yellow fat'><?php echo $productname; ?> (<?php echo $purchaseid; ?>)</td>
  </tr>
  
 </table>
</div>
<form id="registerForm" action="?purchaseid=<?php echo $purchaseid; ?>" method="POST">

	 <table class="default" id="mainTable">
	  <thead>
	   <tr style='cursor: pointer;'>
	    <th><?php echo $lang['global-category']; ?></th>
	    <th><?php echo $lang['global-product']; ?></th>
	    <th><?php echo $lang['global-quantity']; ?></th>
	   </tr>
	  </thead>
	  <tbody>


<?php  

	// Query to look up open products
	$selectOpenPurchases = "SELECT purchaseid, category, productid, purchaseDate, purchasePrice, salesPrice, purchaseQuantity, adminComment, closedAt, estClosing, inMenu, barCode, closingDate FROM b_purchases WHERE closedAt IS NULL ORDER by category ASC, purchaseid ASC";
		try
		{
			$resultOpen = $pdo3->prepare("$selectOpenPurchases");
			$resultOpen->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}

		
		$i = 1;
		while ($purchase = $resultOpen->fetch()) {

			$productid = $purchase['productid'];
			$purchaseid = $purchase['purchaseid'];
		
			$closedAt = $purchase['closedAt'];
			

		
		// Query to look for category
		$categoryDetails = "SELECT name FROM b_categories WHERE id = {$purchase['category']}";
		try
		{
			$result = $pdo3->prepare("$categoryDetails");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
			$purchaseCategory = $row['name'];
		
		$prodSelect = 'pr.name FROM b_products pr';
		$prodJoin = "pr.productid AND p.category = {$purchase['category']}";

		$selectProduct = "SELECT {$prodSelect}, b_purchases p WHERE ({$productid} = {$prodJoin})";
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
			$name = $row['name'];
			$formattedDate = date("d-m-Y", strtotime($purchase['purchaseDate'] . "+$offsetSec seconds"));
			
		$selectPart = "SELECT quantity FROM parts WHERE purchaseid = $purchaseidTop AND product = $productid";
		try
		{
			$result = $pdo3->prepare("$selectPart");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
		$row = $result->fetch();
			$quantity = $row['quantity'];
			
		if ($quantity == 0) {
			$quantity = '';
		}
		
	$purchase_row =	sprintf("
  	  <tr>
  	   <td class='left'>%s</td>
  	   <td class='left'>%s</td>
  	   <td><input type='hidden' name='parts[$i][product]' value='%d' /><input type='number' name='parts[$i][quantity]' class='twoDigit' value='%s' /></td>
	  </tr>",
	  $purchaseCategory, $name, $productid, $quantity);
	  
  	
	  echo $purchase_row;
	  
	  $i++;
	  
}

?>
</tbody></table>
<br />
<input type='hidden' name='submityes' />
 <button type="submit" ><?php echo $lang['global-save']; ?></button><br />
</form>

<?php
/*

Cannador #1

sdcard	3
case	6

purchaseid	1
product		3

*/




displayFooter(); ?>
