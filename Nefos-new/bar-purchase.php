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
	// Display confirmation to reopen
	if (isset($_GET['reopen'])) {
		
		$_SESSION['errorMessage'] = $lang['reopen-confirm'];
		pageStart($lang['title-product'], NULL, $deleteUserScript, "ppurchase", "admin", "Product", $_SESSION['successMessage'], $_SESSION['errorMessage']);
		
		$purchaseid = $_GET['reopen'];
		
		echo <<<EOD
<a href="uTil/bar-reopen-purchase.php?purchaseid=$purchaseid" class="cta">{$lang['pur-reopen']}</a> <a href="new-purchase.php" class="cta">{$lang['newpurchase']}</a>
EOD;
		
	} else {
	
	
	// Does purchase ID exist?
	if (!$_GET['purchaseid']) {
		echo $lang['error-nopurchselected'];
		exit();
	} else  {
		$purchaseid = $_GET['purchaseid'];
	}

	// Query to look for purchase
	$purchaseDetails = "SELECT category, productid, purchaseDate, purchasePrice, salesPrice, purchaseQuantity, adminComment, estClosing, closingComment, closedAt, inMenu, closingDate FROM b_purchases WHERE purchaseid = $purchaseid";
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
		$adminComment = $row['adminComment']; // Purchase comment, really
		$estClosing = $row['estClosing'];
		$closingComment = $row['closingComment']; // Only active when product closed (if even then)
		$closedAt = $row['closedAt'];
		$closingDate = date("d M H:i", strtotime($row['closingDate'] . "+$offsetSec seconds"));
		$inMenu = $row['inMenu'];
		$perGramPurchase = number_format($purchasePrice,2);
		$perGramSale = number_format($salesPrice,2);
		$purchasePriceTotal = number_format($purchasePrice * $purchaseQuantity,2);
		$salesPriceTotal = number_format($salesPrice * $purchaseQuantity,2);
		
	// Find photo extension
	$extFind = "SELECT photoExt from b_products WHERE productid = $productid";
		try
		{
			$result = $pdo3->prepare("$extFind");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
		$photoExt = $row['photoExt'];
		
		
	if ($inMenu == 1) {
		$inMenu = "{$lang['global-yescaps']}";
		$menuClass = 'yellow';
	} else {
		$inMenu = "{$lang['global-nocaps']}";
		$menuClass = 'negative';
	}
	

			
		// Query to look for category
		$categoryDetails = "SELECT name FROM b_categories WHERE id = $category";
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
			$categoryName = $row['name'];
			
		// Query to look for product
		$selectProducts = "SELECT name from b_products WHERE productid = $productid";
		try
		{
			$result = $pdo3->prepare("$selectProducts");
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
	  
     });
     
function delete_movement(movementid, purchaseid) {
	if (confirm("{$lang['confirm-deletemovement']}")) {
				window.location = "uTil/bar-delete-movement.php?movement_id=" + movementid + "&purchaseid=" + purchaseid;
				}
}
function delete_purchase(purchaseid) {
	if (confirm("{$lang['confirm-deletepurchase']}")) {
				window.location = "uTil/bar-delete-purchase.php?purchaseid=" + purchaseid;
				}
}
EOD;


		pageStart($lang['global-purchase'], NULL, $deleteUserScript, "ppurchase", "admin", $lang['global-purchase'], $_SESSION['successMessage'], $_SESSION['errorMessage']); 
	
	// Check if 'entre fechas' was utilised
	if (isset($_POST['untilDate'])) {
		
		$fromDate = date("Y-m-d", strtotime($_POST['fromDate']));
		$untilDate = date("Y-m-d", strtotime($_POST['untilDate']));
		
		$timeLimit = "AND DATE(s.saletime) BETWEEN DATE('$fromDate') AND DATE('$untilDate')";
		$timeLimit2 = "AND DATE(movementtime) BETWEEN DATE('$fromDate') AND DATE('$untilDate')";
			
	}
	
  	// Look up sales
		$selectSales = "SELECT SUM(quantity) FROM b_salesdetails WHERE purchaseid = $purchaseid $timeLimit";
		try
		{
			$result = $pdo3->prepare("$selectSales");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
		$sales = $row['SUM(quantity)'];
		
  	// Look up gifts
	$selectSales = "SELECT SUM(d.quantity) FROM b_salesdetails d, b_sales s WHERE s.saleid = d.saleid AND d.purchaseid = $purchaseid AND d.amount = 0 $timeLimit";
		try
		{
			$result = $pdo3->prepare("$selectSales");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
		$gifts = $row['SUM(d.quantity)'];

		
	// Calculate reloads
	$selectReloads = "SELECT SUM(quantity) FROM b_productmovements WHERE purchaseid = $purchaseid AND type = 1 AND movementTypeid = 1";
		try
		{
			$result = $pdo3->prepare("$selectReloads");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
			$totalReloads = $row['SUM(quantity)'];
			
	if ($totalReloads == '') {
		
		$totalReloads = 0;
		
	}
	
	
	// Calculate added product (from shake)
	$selectShake = "SELECT SUM(quantity) FROM b_productmovements WHERE purchaseid = $purchaseid AND type = 1 AND movementTypeid = 22";
		try
		{
			$result = $pdo3->prepare("$selectShake");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
			$shakeAdded = $row['SUM(quantity)'];
			

	
	// Calculate total purchased
	$totalPurchased = $totalReloads + $purchaseQuantity + $shakeAdded;
	
	$soldPercentage = ($sales / $totalPurchased) * 100;
	$soldPercentageReal = ($realSales / $totalPurchased) * 100;
	$giftedPercentage = ($gifts / $totalPurchased) * 100;
	$giftedPercentageReal = ($realgifts / $totalPurchased) * 100;
	$totalPercentage = (($sales + $gifts) / $totalPurchased) * 100;
	$totalPercentageReal = (($realSales + $realgifts) / $totalPurchased) * 100;
	
	// Select takeouts
	$selectTakeouts = "SELECT movementtypeid, SUM(quantity) FROM b_productmovements WHERE purchaseid = $purchaseid AND type = 2 $timeLimit2 GROUP BY movementtypeid";
		try
		{
			$result = $pdo3->prepare("$selectTakeouts");
			$result->execute();
			$data = $result->fetchAll();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
			
		

			
		
	// Calculate what's in Internal stash
	$selectStashedInt = "SELECT SUM(quantity) FROM b_productmovements WHERE purchaseid = $purchaseid AND type = 2 AND (movementTypeid = 5 OR movementTypeid = 18)";
		try
		{
			$result = $pdo3->prepare("$selectStashedInt");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
			$stashedInt = $row['SUM(quantity)'];
		
	$selectUnStashedInt = "SELECT SUM(quantity) FROM b_productmovements WHERE purchaseid = $purchaseid AND type = 1 AND (movementTypeid = 12 OR movementTypeid = 17)";
		try
		{
			$result = $pdo3->prepare("$selectUnStashedInt");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
			$unStashedInt = $row['SUM(quantity)'];

			
		$inStashInt = $stashedInt - $unStashedInt;
		$inStashInt = $inStashInt;


	// Calculate what's in External stash
	$selectStashedExt = "SELECT SUM(quantity) FROM b_productmovements WHERE purchaseid = $purchaseid AND type = 2 AND (movementTypeid = 6 OR movementTypeid = 20)";
		try
		{
			$result = $pdo3->prepare("$selectStashedExt");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
			$stashedExt = $row['SUM(quantity)'];
		
	$selectUnStashedExt = "SELECT SUM(quantity) FROM b_productmovements WHERE purchaseid = $purchaseid AND type = 1 AND (movementTypeid = 2 OR movementTypeid = 19)";
		try
		{
			$result = $pdo3->prepare("$selectUnStashedExt");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
			$unStashedExt = $row['SUM(quantity)'];

			
		$inStashExt = $stashedExt - $unStashedExt;
		$inStashExt = $inStashExt;

			
	$selectDisplay = "SELECT SUM(quantity) FROM b_productmovements WHERE purchaseid = $purchaseid AND type = 2 AND (movementTypeid = 9)";
		try
		{
			$result = $pdo3->prepare("$selectDisplay");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
			$inDisplay = $row['SUM(quantity)'];
			
	$selectRemovedFromDisplay = "SELECT SUM(quantity) FROM b_productmovements WHERE purchaseid = $purchaseid AND type = 1 AND (movementTypeid = 3)";
		try
		{
			$result = $pdo3->prepare("$selectRemovedFromDisplay");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
			$removedFromDisplay = $row['SUM(quantity)'];
			
			$inDisplay = $inDisplay - $removedFromDisplay;

			// display internal
			
		$selectPermAdditions = "SELECT SUM(quantity) FROM b_productmovements WHERE purchaseid = $purchaseid AND type = 1 AND (movementTypeid = 1 OR movementTypeid = 3 OR movementTypeid = 10)";
		try
		{
			$result = $pdo3->prepare("$selectPermAdditions");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
			$permAdditions = $row['SUM(quantity)'];
	
		$selectPermRemovals = "SELECT SUM(quantity) FROM b_productmovements WHERE purchaseid = $purchaseid AND type = 2 AND (movementTypeid = 4 OR movementTypeid = 7 OR movementTypeid = 8 OR movementTypeid = 9 OR movementTypeid = 11 OR movementTypeid = 13 OR movementTypeid = 14 OR movementTypeid = 15 OR movementTypeid = 16 OR movementTypeid = 27 OR movementTypeid = 28 OR movementTypeid = 29)";
		try
		{
			$result = $pdo3->prepare("$selectPermRemovals");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
			$permRemovals = $row['SUM(quantity)'];
	
	$inStash = $inStashInt + $inStashExt;
	$inStashReal = $inStash;
	
	if ($inStash < 0) {
		$inStash = 0;
	}
	
	$estStock = $purchaseQuantity + $permAdditions - $sales - $permRemovals - $inStash;
	
	$totalProduct = $estStock + $inStashReal + $inDisplay;



?>

<div id='productoverview'>
  <a href='bar-change-product-image.php?productid=<?php echo $productid; ?>&purchaseid=<?php echo $purchaseid; ?>&purchase'><img src='images/_<?php echo $domain; ?>/bar-products/<?php echo $productid . "." . $photoExt; ?>' height='70' style='display: inline; vertical-align: middle;' /></a>
 <table style="display: inline-block; vertical-align: top;">
  <tr>
   <td><?php echo $lang['global-category']; ?>:</td>
   <td class='yellow fat'><?php echo $categoryName; ?></td>
  </tr>
  <tr>
   <td><?php echo $lang['global-product']; ?>:</td>
   <td class='yellow fat'><?php echo $name; ?></td>
  </tr>
 </table>
</div>

<div id="ctawrap">
<a href="bar-purchases.php" class="cta"><span class="ctatext">&laquo; <?php echo $lang['purchases']; ?></span></a>
<a href="bar-edit-purchase.php?purchaseid=<?php echo $purchaseid;?>" class="cta"><span class="ctatext"><?php echo $lang['global-edit']; ?></span></a>
<?php
	if ($closedAt == NULL) {
		echo "<a href='bar-add-or-remove.php?purchaseid=$purchaseid' class='cta'><span class='ctatext'>{$lang['pur-addremove']}</span></a>";
		echo "<a href='bar-close-purchase-2.php?purchaseid=$purchaseid' class='cta'><span class='ctatext'>{$lang['pur-close']}</span></a>";
	} else {
		echo "<a href='?reopen=$purchaseid' class='cta'><span class='ctatext'>{$lang['pur-reopen']}</span></a>";
	}
?>
<a href="javascript:delete_purchase(<?php echo $purchaseid; ?>)" class="cta" style="background-color: red;"><span class="ctatext" style="color: yellow";><?php echo $lang['global-deletecaps']; ?></span></a>

</div>



<br />

<div class='leftblock'>
 <div class='infobox'>
  <h3><?php echo $lang['global-quantity']; ?></h3>
  <table>
   <tr>
    <td class="biggerFont"><?php echo $lang['add-amountpurchased']; ?></td>
    <td class="biggerNumber right"><?php echo $purchaseQuantity; ?> u</td>
   </tr>
   <tr style="border-bottom: 1px solid #eee;">
    <td class="biggerFont"><?php echo $lang['closeproduct-reloads']; ?></td>
    <td class="biggerNumber right"><?php echo number_format($totalReloads,2); ?> u</td>
   </tr>
   <tr style="border-bottom: 2px solid #eee;">
    <td class="biggerFont"><?php echo $lang['add-totalpurchased']; ?></td>
    <td class="biggerNumber right"><?php echo number_format($totalPurchased,2); ?> u</td>
   </tr>
   
<?php
	if ($closedAt != NULL) {
		if ($closedAt < 0) {
			$closeClass = 'negative';
		}
		
		
		echo "
   <tr>
    <td class='biggerFont'>{$lang['pur-closedat']}:</td>
    <td class='biggerNumber right'><span class='$closeClass'>$closedAt g</span><br />$closingDate</td>
   </tr>";

	} else {
		echo "
   <tr>
    <td colspan='2' style='text-align: center; padding-top: 8px; font-size: 16px;'>{$lang['pur-inmenu']}: <span class='$menuClass'><strong>$inMenu</strong></span>.</td>
   </tr>";
	}

?>


  </table>
<?php	if ($closingComment != NULL) {
		echo "
	{$lang['pur-closecomment']}:<br />
    <em>$closingComment</em>";
	}
?>

 </div>
 <br />
 <div id="leftinner">
 <div class='infobox fullwidth'>
  <h3 class="smallerFont"><?php echo $lang['add-purchaseprice']; ?></h3>
  <table>
   <tr>
    <td><?php echo $lang['add-perunit']; ?></td>
    <td class="bigNumber right"><?php echo number_format($perGramPurchase,2); ?> &euro;</td>
   </tr>
   <tr>
    <td><?php echo $lang['add-total']; ?></td>
    <td class="bigNumber right"><?php echo $purchasePriceTotal; ?> &euro;</td>
   </tr>
  </table>
 </div>
 <div class='infobox fullwidth2'>
  <h3 class="smallerFont"><?php echo $lang['admin-statistics']; ?> total</h3><br />
  <form action='' method='POST'>
<?php
	if (isset($_POST['fromDate'])) {
		
		echo <<<EOD
   <input type="text" id="datepicker" name="fromDate" autocomplete="nope" class="fiveDigit" value="{$_POST['fromDate']}" />&nbsp;&nbsp;
   <input type="text" id="datepicker2" name="untilDate" autocomplete="nope" class="fiveDigit" value="{$_POST['untilDate']}" onchange='this.form.submit()' />
EOD;
	} else {
		
		echo <<<EOD
   <input type="text" id="datepicker" name="fromDate" autocomplete="nope" class="fiveDigit" placeholder="{$lang['from-date']}" />&nbsp;&nbsp;
   <input type="text" id="datepicker2" name="untilDate" autocomplete="nope" class="fiveDigit" placeholder="{$lang['to-date']}" onchange='this.form.submit()' />
EOD;

	}
?>

<br /><br />
  </form>
  <table>
   <tr>
    <td class="left"><?php echo $lang['closeday-dispensed']; ?></td>
    <td class="bigNumber right"><?php echo number_format($sales,2); ?> g</td>
    <td class="bigNumber right"><?php echo number_format($soldPercentage,1); ?> %</td>
   </tr>
   <tr style="border-bottom: 1px solid yellow;">
    <td class="left"><?php echo $lang['gifted']; ?></td>
    <td class="bigNumber right"><?php echo number_format($gifts,2); ?> g</td>
    <td class="bigNumber right"><?php echo number_format($giftedPercentage,1); ?> %</td>
   </tr>
   <tr>
    <td class="left"><strong><?php echo $lang['add-total']; ?></strong></td>
    <td class="bigNumber right"><strong><?php echo number_format($gifts + $sales,2); ?> g</strong></td>
    <td class="bigNumber right"><?php echo number_format($totalPercentage,1); ?> %</td>
   </tr>
   <tr>
    <td colspan="3">&nbsp;</td>
   </tr>
<?php 
		

	if ($data) {
		
		echo <<<EOD
		
   <tr>
    <td colspan="3"><strong>{$lang['closeday-takeouts']}</strong></td>
   </tr>

EOD;

		foreach ($data as $movement) {
				
			$movementtypeid = $movement['movementtypeid'];
			$movementQuantity = number_format($movement['SUM(quantity)'],2);
			$movementPercentage = number_format((($movementQuantity / $totalPurchased) * 100),1);
		
			// Look up movementtype name
			if ($_SESSION['lang'] == 'en') {
				$selMovement = "SELECT movementNameen AS name from productmovementtypes WHERE movementTypeid = $movementtypeid";
			} else {
				$selMovement = "SELECT movementNamees AS name from productmovementtypes WHERE movementTypeid = $movementtypeid";
			}
		try
		{
			$movementResult = $pdo3->prepare("$selMovement");
			$movementResult->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$rowM = $movementResult->fetch();
				$movementName = $rowM['name'];
				
			echo <<<EOD
				
   <tr>
    <td class="left">$movementName</td>
    <td class="bigNumber right">$movementQuantity g</td>
    <td class="bigNumber right">$movementPercentage %</td>
   </tr>
   
EOD;
			
						
		}
		
	}
?>
  </table>
 </div>
 
 </div>
 
 <div id="rightinner">

 
 <div class='infobox fullwidth'>
  <h3 class="smallerFont"><?php echo $lang['add-dispenseprice']; ?></h3>
  <table>
   <tr>
    <td><?php echo $lang['add-perunit']; ?></td>
    <td class="bigNumber right"><?php echo number_format($perGramSale,2); ?> &euro;</td>
   </tr>
   <tr>
    <td><?php echo $lang['add-total']; ?></td>
    <td class="bigNumber right"><?php echo $salesPriceTotal; ?> &euro;</td>
   </tr>
  </table>
 </div>
 <br />
 
 
 <div class='infobox fullwidth'>
  <h3 class="smallerFont"><?php echo $lang['pur-eststock']; ?> <a href="stock.php" class="smallerFont"></a></h3>
  <table>
   <tr>
    <td>Office:</td>
    <td class="bigNumber right"><?php echo number_format($estStock,2); ?> u</td>
   </tr>
   <tr>
    <td>Warehouse:</td>
    <td class="bigNumber right"><?php echo number_format($inStashInt,2); ?> u</td>
   </tr>
   <tr>
    <td>Christian:</td>
    <td class="bigNumber right"><?php echo number_format($inDisplay,2); ?> u</td>
   </tr>
   <tr style="border-bottom: 1px solid yellow;">
    <td>Andy:</td>
    <td class="bigNumber right"><?php echo number_format($inStashExt,2); ?> u</td>
   </tr>
   <tr>
    <td><strong><?php echo $lang['global-total']; ?>:</strong></td>
    <td class="bigNumber right"><?php echo number_format($totalProduct,2); ?> u</td>
   </tr>
  </table>
   
   </div>

  </div>
 
   
</div> <!-- end leftblock -->


<div class='rightblock'>
  <h3><?php echo $lang['add-movements']; ?></h3>
  
  <center>
   <a href="?purchaseid=<?php echo $purchaseid; ?>&summary" class='topLink'><?php echo $lang['summary']; ?></a>
<!--
   <a href="?purchaseid=<?php echo $purchaseid; ?>&week" class='topLink'>Weekly</a>
   <a href="?purchaseid=<?php echo $purchaseid; ?>&month" class='topLink'>Monthly</a>
-->
   <a href="?purchaseid=<?php echo $purchaseid; ?>&all" class='topLink'><?php echo $lang['all']; ?></a>
  </center>
  <br />

   
<?php 

	if (isset($_GET['summary'])) {
		
		// Query to look up movements
		$selectMovements = "SELECT type, SUM(quantity), movementTypeid FROM b_productmovements WHERE purchaseid = $purchaseid GROUP BY movementTypeid ORDER by type DESC, movementTypeid ASC";

		
	} else {
		
		// Query to look up movements
		$selectMovements = "SELECT movementid, movementtime, type, purchaseid, quantity, movementTypeid, comment, price FROM b_productmovements WHERE purchaseid = $purchaseid ORDER by movementtime ASC";

	}
		try
		{
			$results = $pdo3->prepare("$selectMovements");
			$results->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
			
?>

	 <table class="default">
	  <tbody>
	  
  	  <tr class='green'>
  	   <td><?php echo date("d M H:i", strtotime($purchaseDate . "+$offsetSec seconds")); ?></td>
  	   <td class='left'><?php echo $lang['pur-origpurchase']; ?></td>
  	   <td style='text-align: right;'><?php echo number_format($purchaseQuantity,2); ?> u</td>
  	   <td style='text-align: right;'><?php echo number_format($purchasePrice,2); ?> &euro; / u</td>
  	   <td></td>
  	   <td></td>
	  </tr>
	  
	  <?php

		while ($movement = $results->fetch()) {
		
	$movementid = $movement['movementid'];
	$movementtime = $movement['movementtime'];
	$type = $movement['type'];
	$purchaseid = $movement['purchaseid'];
	$price = $movement['price'];
	$quantity = $movement['quantity'];

	$movementTypeid = $movement['movementTypeid'];
	
	if ($movementTypeid == 1) {
		$editOrNot = "<a href='edit-movement.php?movementid=$movementid'><img src='images/edit.png' height='15' title='Edit' /></a>&nbsp;&nbsp;";
		$ppg = $price / $quantity . " &euro; / u";
	} else {
		$editOrNot = "";
		$ppg = '';
	}
	
	if (isset($_GET['summary'])) {
		$quantity = $movement['SUM(quantity)'];
		$formattedDate = "";
	} else {
		$quantity = $movement['quantity'];
		$formattedDate = date("d M H:i", strtotime($movement['movementtime'] . "+$offsetSec seconds"));
	}

	
	if ($movementTypeid == 17 || $movementTypeid == 18 || $movementTypeid == 19 || $movementTypeid == 20 ) {
		$rowclass = " class='grey' ";
	} else if ($type == 1) {
		$rowclass = " class='green' ";
	} else if ($type == 2) {
		$rowclass = " class='red' ";
	} else {
		$rowclass = "";
	}
	
	if ($movement['comment'] != '') {
		
		$commentRead = "
		                <img src='images/comments.png' id='comment$movementid' /><div id='helpBox$movementid' class='helpBox'>{$movement['comment']}</div>
		                <script>
		                  	$('#comment$movementid').on({
						 		'mouseover' : function() {
								 	$('#helpBox$movementid').css('display', 'block');
						  		},
						  		'mouseout' : function() {
								 	$('#helpBox$movementid').css('display', 'none');
							  	}
						  	});
						</script>
		                ";
		
	} else {
		
		$commentRead = "";
		
	}
	
	
	// Look up movement name
      	if ($_SESSION['lang'] == 'es') {
			$selectMovementName = "SELECT movementNamees FROM productmovementtypes WHERE movementTypeid = $movementTypeid";
			try
			{
				$result = $pdo3->prepare("$selectMovementName");
				$result->execute();
			}
			catch (PDOException $e)
			{
					$error = 'Error fetching user: ' . $e->getMessage();
					echo $error;
					exit();
			}
		
			$row = $result->fetch();
				$movementName = $row['movementNamees'];
		} else {
			$selectMovementName = "SELECT movementNameen FROM productmovementtypes WHERE movementTypeid = $movementTypeid";
			try
			{
				$result = $pdo3->prepare("$selectMovementName");
				$result->execute();
			}
			catch (PDOException $e)
			{
					$error = 'Error fetching user: ' . $e->getMessage();
					echo $error;
					exit();
			}
		
			$row = $result->fetch();
				$movementName = $row['movementNameen'];
		}

	
	$movement_row =	sprintf("
  	  <tr%s>
  	   <td>%s</td>
  	   <td class='left'>%s</td>
  	   <td style='text-align: right;'>%0.02f u</td>
  	   <td style='text-align: right;'>$ppg</td>
  	   <td class='centered' style='position: relative;'>%s</td>
  	   <td class='right'>$editOrNot<a href='javascript:delete_movement(%d,%d)'><img src='images/delete.png' height='15' title='Delete movement' /></a></td>
	  </tr>",
	  $rowclass, $formattedDate, $movementName, $quantity, $commentRead, $movementid, $purchaseid
	  );
	  echo $movement_row;
  }
?>

	 </tbody>
	 </table>
	 
	 </div>
	 

<br /><br />
 	 <table class="default">
	  <thead>
	   <tr>
	    <th><?php echo $lang['global-time']; ?></th>
	    <th>Client #</th>
	    <th>Client name</th>
	    <th><?php echo $lang['global-quantity']; ?></th>
	    <th><?php echo $lang['global-amount']; ?></th>
	   </tr>
	  </thead>
	  <tbody>

<?php 

	// Purchase dispense history: Time, member, quantity, euro

	$selectSales = "SELECT s.saleid, s.saletime, s.userid, d.quantity, d.amount FROM b_sales s, b_salesdetails d WHERE s.saleid = d.saleid AND d.purchaseid = $purchaseid ORDER BY s.saletime DESC";
		try
		{
			$result = $pdo3->prepare("$selectSales");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		while ($sale = $result->fetch()) {
	
		$formattedDate = date("d M H:i", strtotime($sale['saletime'] . "+$offsetSec seconds"));
		$saleid = $sale['saleid'];
		$userid = $sale['userid'];
		$quantity = $sale['quantity'];
		$amount = $sale['amount'];
		
		$query = "SELECT number, shortName FROM customers WHERE id = '$userid'";
		try
		{
			$resultr = $pdo2->prepare("$query");
			$resultr->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $resultr->fetch();
			$number = $row['number'];
			$shortName = $row['shortName'];
		
		$sale_row =	sprintf("
	  	  <tr>
	  	   <td class='clickableRow' href='bar-sale.php?saleid=%d'>%s</td>
	  	   <td class='clickableRow' href='bar-sale.php?saleid=%d'>%s</td>
	  	   <td class='clickableRow left' href='bar-sale.php?saleid=%d'>%s</td>
	  	   <td style='text-align: right;' class='clickableRow' href='bar-sale.php?saleid=%d'>%0.2f <span class='smallerfont'>u.</span></td>
	  	   <td style='text-align: right;' class='clickableRow' href='bar-sale.php?saleid=%d'>%0.2f <span class='smallerfont'>&euro;</span></td>
		  </tr>",
		  $sale['saleid'], $formattedDate, $sale['saleid'], $number, $sale['saleid'], $shortName, $sale['saleid'], $quantity, $sale['saleid'], $amount
		  );
		  echo $sale_row;

	}

?>

<br class="clearFloat">

<?php } displayFooter(); ?>
