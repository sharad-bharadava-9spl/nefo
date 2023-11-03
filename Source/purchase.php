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
		
	// Display confirmation to reopen
	if (isset($_GET['reopen'])) {
		
		$_SESSION['errorMessage'] = $lang['reopen-confirm'];
		pageStart($lang['title-product'], NULL, $deleteUserScript, "ppurchase", "admin", "Product", $_SESSION['successMessage'], $_SESSION['errorMessage']);
		
		$purchaseid = $_GET['reopen'];
		
		echo <<<EOD
<center><a href="uTil/reopen-purchase.php?purchaseid=$purchaseid" class="cta1">{$lang['pur-reopen']}</a> <a href="new-purchase.php" class="cta1">{$lang['newpurchase']}</a></center>
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
		
	
	$growDetails = "SELECT growtype FROM growtypes WHERE growtypeid = $growtype";
		try
		{
			$result = $pdo3->prepare("$growDetails");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
		$growtype = $row['growtype'];

	
	if ($inMenu == 1) {
		$inMenu = "{$lang['global-yescaps']}";
		$menuClass = 'yellow';
	} else {
		$inMenu = "{$lang['global-nocaps']}";
		$menuClass = 'negative';
	}
	

	$closeDiff = $closedAt - $estClosing;
	
			if ($category == 1) {
			// Query to look up flowers
			$selectFlower = "SELECT flowerid, breed2, name FROM flower WHERE flowerid = {$productid}";
		try
		{
			$result = $pdo3->prepare("$selectFlower");
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
			$breed2 = $row['breed2'];
			$categoryName = $lang['global-flower'];
			
		if ($breed2 != '') {
			$name = $name . " x " . $breed2;
		}

			
		} else if ($category == 2) {
			// Query to look up extract
			$selectExtract = "SELECT extractid, extracttype, extract, name FROM extract WHERE extractid = {$productid}";
		try
		{
			$result = $pdo3->prepare("$selectExtract");
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
				$categoryName = $lang['global-extract'];
			
		} else {
			
		// Query to look for category
		$categoryDetails = "SELECT name FROM categories WHERE id = $category";
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
		$selectProducts = "SELECT name from products WHERE productid = $productid";
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

		}
	


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


		pageStart($lang['purchase'], NULL, $deleteUserScript, "ppurchase", "admin ppage", $lang['purchase'], $_SESSION['successMessage'], $_SESSION['errorMessage']); 
	
	
	// Check if 'entre fechas' was utilised
	if (isset($_POST['untilDate'])) {
		
		$fromDate = date("Y-m-d", strtotime($_POST['fromDate']));
		$untilDate = date("Y-m-d", strtotime($_POST['untilDate']));
		
		$timeLimit = "AND DATE(s.saletime) BETWEEN DATE('$fromDate') AND DATE('$untilDate')";
		$timeLimit2 = "AND DATE(movementtime) BETWEEN DATE('$fromDate') AND DATE('$untilDate')";
			
	}
	
  	// Look up sales
	$selectSales = "SELECT SUM(d.quantity), SUM(d.realQuantity) FROM salesdetails d, sales s WHERE s.saleid = d.saleid AND d.purchaseid = $purchaseid AND d.amount <> 0 $timeLimit";
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
		$sales = $row['SUM(d.quantity)'];
		$realSales = $row['SUM(d.realQuantity)'];
		
  	// Look up gifts
	$selectSales = "SELECT SUM(d.quantity), SUM(d.realQuantity) FROM salesdetails d, sales s WHERE s.saleid = d.saleid AND d.purchaseid = $purchaseid AND d.amount = 0 $timeLimit";

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
		$realgifts = $row['SUM(d.realQuantity)'];

		
	// Calculate reloads
	$selectReloads = "SELECT SUM(quantity) FROM productmovements WHERE purchaseid = $purchaseid AND type = 1 AND movementTypeid = 1";
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
	$selectShake = "SELECT SUM(quantity) FROM productmovements WHERE purchaseid = $purchaseid AND type = 1 AND movementTypeid = 22";
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
	$totalPurchased = $totalReloads + $realQuantity + $shakeAdded;
	
	$soldPercentage = ($sales / $totalPurchased) * 100;
	$soldPercentageReal = ($realSales / $totalPurchased) * 100;
	$giftedPercentage = ($gifts / $totalPurchased) * 100;
	$giftedPercentageReal = ($realgifts / $totalPurchased) * 100;
	$totalPercentage = (($sales + $gifts) / $totalPurchased) * 100;
	$totalPercentageReal = (($realSales + $realgifts) / $totalPurchased) * 100;
	
	// Select takeouts
	$selectTakeouts = "SELECT movementtypeid, SUM(quantity) FROM productmovements WHERE purchaseid = $purchaseid AND type = 2 $timeLimit2 GROUP BY movementtypeid";
		try
		{
			$takeoutResult = $pdo3->prepare("$selectTakeouts");
			$takeoutResult->execute();
			$takeoutData = $takeoutResult->fetchAll();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
			
		
	// Calculate what's in Internal stash
	$selectStashedInt = "SELECT SUM(quantity) FROM productmovements WHERE purchaseid = $purchaseid AND type = 2 AND (movementTypeid = 5 OR movementTypeid = 18)";
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
		
	$selectUnStashedInt = "SELECT SUM(quantity) FROM productmovements WHERE purchaseid = $purchaseid AND type = 1 AND (movementTypeid = 12 OR movementTypeid = 17)";
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
	$selectStashedExt = "SELECT SUM(quantity) FROM productmovements WHERE purchaseid = $purchaseid AND type = 2 AND (movementTypeid = 6 OR movementTypeid = 20)";
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
		
	$selectUnStashedExt = "SELECT SUM(quantity) FROM productmovements WHERE purchaseid = $purchaseid AND type = 1 AND (movementTypeid = 2 OR movementTypeid = 19)";
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

			
	$selectDisplay = "SELECT SUM(quantity) FROM productmovements WHERE purchaseid = $purchaseid AND type = 2 AND (movementTypeid = 9)";
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
			
	$selectRemovedFromDisplay = "SELECT SUM(quantity) FROM productmovements WHERE purchaseid = $purchaseid AND type = 2 AND (movementTypeid = 3)";
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
			
	$selectPermAdditions = "SELECT SUM(quantity) FROM productmovements WHERE purchaseid = $purchaseid AND type = 1 AND (movementTypeid = 1 OR movementTypeid = 3 OR movementTypeid = 10)";
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
	
	$selectPermRemovals = "SELECT SUM(quantity) FROM productmovements WHERE purchaseid = $purchaseid AND type = 2 AND (movementTypeid = 4 OR movementTypeid = 7 OR movementTypeid = 8 OR movementTypeid = 9 OR movementTypeid = 11 OR movementTypeid = 13 OR movementTypeid = 14 OR movementTypeid = 15 OR movementTypeid = 16)";
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
	
	if ($_SESSION['realWeight'] == 1) {
		$estStock = $realQuantity + $permAdditions - $realSales - $realgifts - $permRemovals - $inStash + $shakeAdded;
	} else {
		$estStock = $realQuantity + $permAdditions - $sales - $gifts - $permRemovals - $inStash + $shakeAdded;
	}
	
	$totalProduct = $estStock + $inStashReal + $inDisplay;

	// Calculate real stock
	// If lastopening is less than 2 days, and only show real stock if that's the case
	// Check if an open shift exists:
	// If exists, check if it's been closed. If not exists, throw error "No shift exists".
	$openingLookup = "SELECT openingid, openingtime, 'opening' AS type FROM opening UNION ALL SELECT openingid, openingtime, 'shiftopen' AS type FROM shiftopen ORDER BY openingtime DESC LIMIT 1";
		try
		{
			$result = $pdo3->prepare("$openingLookup");
			$result->execute();
			$data = $result->fetchAll();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
			
		if (!$data) {
			
			$noOpening = 'true';
			
		} else {

			$row = $data[0];
				$openingid = $row['openingid'];	
				$openingtime = $row['openingtime'];
				$type = $row['type'];
			
		if ($type == 'opening') {
			
			// Get opening details
			$weightQuery = "SELECT d.weight FROM opening o, openingdetails d WHERE o.openingid = d.openingid AND d.openingid = $openingid AND purchaseid = $purchaseid";
		try
		{
			$result = $pdo3->prepare("$weightQuery");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
			
				$openingWeight = $row['weight'];
				
			if ($openingWeight == 0) {
				$noOpening = 'true';
			}

				
			
		} else {
			
			// Get opening details
			$weightQuery = "SELECT d.weight FROM shiftopen o, shiftopendetails d WHERE o.openingid = d.openingid AND d.openingid = $openingid AND purchaseid = $purchaseid";
		try
		{
			$result = $pdo3->prepare("$weightQuery");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
				$openingWeight = $row['weight'];
				
			if ($openingWeight == 0) {
				$noOpening = 'true';
			}
			
		}

			// Additions and Removals
			$selecta1dditions = "SELECT SUM(quantity) FROM productmovements WHERE purchaseid = $purchaseid AND type = 1 AND movementtime > '$openingtime'";
		try
		{
			$result = $pdo3->prepare("$selecta1dditions");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
				$prodAdditions = $row['SUM(quantity)'];
			
			$selectRemovals = "SELECT SUM(quantity) FROM productmovements WHERE purchaseid = $purchaseid AND type = 2 AND movementtime > '$openingtime'";
		try
		{
			$result = $pdo3->prepare("$selectRemovals");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
				$prodRemovals = $row['SUM(quantity)'];
				
		  	// Look up sales
			$selectCurrSales = "SELECT SUM(d.quantity), SUM(d.realQuantity) FROM sales s, salesdetails d WHERE s.saleid = d.saleid AND d.purchaseid = $purchaseid AND s.saletime > '$openingtime'";
		try
		{
			$result = $pdo3->prepare("$selectCurrSales");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
				$currSales = $row['SUM(d.quantity)'];
				$currRealSales = $row['SUM(d.realQuantity)'];

				
			$realStock = $openingWeight + $prodAdditions - $currSales - $prodRemovals;
			$totalRealProduct = $realStock + $inStashReal + $inDisplay;
				
	}
		
	
	// Check if Category is grams or units
	$selectCats = "SELECT type from categories WHERE id = $category";
		try
		{
			$result = $pdo3->prepare("$selectCats");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
		$type = $row['type'];
		
	if ($category > 2) {
		if ($type == 1) {
			$categoryName = $categoryName . " (g.)";
		} else {
			$categoryName = $categoryName . " (u.)";
		}
	} else {
		$categoryName = $categoryName . " (g.)";
	}


	$imgname = "images/_$domain/purchases/$purchaseid.$photoExt";
	
	if (!file_exists($imgname)) {
		
		if ($category == 1) {
			$imgname = "images/purchase-flower.png";
		} else if ($category == 2) {
			$imgname = "images/purchase-extract.png";
		} else {
			$imgname = "images/purchase-cannabis.png";
		}
	}
	
	if ($category < 3 || $type == 1) {
		
?>
<center>
<div id='productoverview'>
  <a href='change-image.php?purchaseid=<?php echo $purchaseid; ?>'><img src='<?php echo $imgname; ?>' style='display: inline; vertical-align: middle;' /></a>
 <table style="display: inline-block; vertical-align: top; <?php if ($category == '2') { echo 'margin-top: 9px;'; } ?>">
  <tr>
   <td><?php echo $lang['global-category']; ?>:</td>
   <td class='yellow fat'><?php echo $categoryName; ?></td>
  </tr>
  <tr>
   <td><?php echo $lang['global-strain']; ?>:</td>
   <td class='yellow fat'><?php echo $name; ?> (<?php echo $purchaseid; ?>)</td>
  </tr>
  <?php if ($category == '1') { ?>
  <tr>
   <td><?php echo $lang['global-growtype']; ?>:</td>
   <td class='yellow fat'><?php echo $growtype; ?></td>
  </tr>
  <?php } ?>
  <tr>
   <td>Fecha de compra:</td>
   <td class='yellow fat'><a href="change-purchase-date.php?purchaseid=<?php echo $purchaseid; ?>"><?php echo date('d-m-Y', strtotime($purchaseDate)); ?></a></td>
  </tr>
  
 </table>
</div>

<br />
<?php if (isset($_GET['closed'])) { ?>
<a href="closed-purchases.php" class="cta1"><span class="cta1text">&laquo; <?php echo $lang['purchases']; ?></span></a>
<?php } else { ?>
<a href="open-purchases.php" class="cta1"><span class="cta1text">&laquo; <?php echo $lang['purchases']; ?></span></a>
<?php } ?>
<a href="edit-purchase.php?purchaseid=<?php echo $purchaseid;?>" class="cta1"><span class="cta1text"><?php echo $lang['global-edit']; ?></span></a>
<!--<a href="add-product.php?purchaseid=<?php echo $purchaseid;?>" class="cta1"><span class="cta1text">Add to jar</span></a>
<a href="remove-product.php?purchaseid=<?php echo $purchaseid;?>" class="cta1"><span class="cta1text">Remove from jar</span></a>-->
<?php
	if ($closedAt == NULL) {
		echo "<a href='add-or-remove.php?purchaseid=$purchaseid' class='cta1'><span class='cta1text'>{$lang['pur-addremove']}</span></a>";
		echo "<a href='close-purchase-2.php?purchaseid=$purchaseid' class='cta1'><span class='cta1text'>{$lang['pur-close']}</span></a>";
	} else {
		//echo "<a href='uTil/reopen-purchase.php?purchaseid=$purchaseid' class='cta1'><span class='cta1text'>{$lang['pur-reopen']}</span></a>";
		echo "<a href='?reopen=$purchaseid' class='cta1'><span class='cta1text'>{$lang['pur-reopen']}</span></a>";
	}
	
	if ($_SESSION['userGroup'] == 1) {
?>
<a href="javascript:delete_purchase(<?php echo $purchaseid; ?>)" class="cta1" style="background-color: red;"><span class="cta1text" style="color: yellow";><?php echo $lang['global-deletecaps']; ?></span></a>

<?php } ?>



<br />

<div class="actionbox-np2">
 <div class='mainboxheader'>
  <center><?php echo $lang['global-quantity']; ?></center>
 </div>
 <div class='boxcontent'>
  <table class='purchasetable'>
   <tr>
    <td class="biggerFont left"><?php echo $lang['add-amountpurchased']; ?><span class="purchaseNumber"><?php echo $purchaseQuantity; ?> g</span></td>
   </tr>
   <tr>
    <td class="biggerFont left"><?php echo $lang['add-realweight']; ?><span class="purchaseNumber"><?php echo $realQuantity; ?> g</span></td>
   </tr>
   <tr>
    <td class="biggerFont left"><?php echo $lang['closeproduct-reloads']; ?><span class="purchaseNumber"><?php echo number_format($totalReloads,2); ?> g</span></td>
   </tr>
   <tr>
    <td class="biggerFont left"><?php echo $lang['closeday-added']; ?><span class="purchaseNumber"><?php echo number_format($shakeAdded,2); ?> g</span></td>
   </tr>
   <tr>
    <td class="biggerFont left"><strong><?php echo $lang['add-totalpurchased']; ?><span class="purchaseNumber"><?php echo number_format($totalPurchased,2); ?> g</span></strong></td>
   </tr>
   
<?php
	if ($closedAt != NULL) {
		if ($closedAt < 0) {
			$closeClass = 'negative';
		}
		
		
		echo "
   <tr>
    <td class='biggerFont left'>{$lang['pur-closedat']}:<span class='purchaseNumber'>$closedAt g</span><br />$closingDate</td>
   </tr>";
   
		if ($closingComment != NULL) {
			echo "<tr><td>
		{$lang['pur-closecomment']}:<br />
	    <em>$closingComment</em></td></tr>";
		}   
   

	} else {
		echo "
   <tr>
    <td colspan='2' style='text-align: center; padding-top: 8px; font-size: 16px;'>{$lang['pur-inmenu']}: <span class='$menuClass'><strong>$inMenu</strong></span>.</td>
   </tr>";
	}

?>
   <tr>
    <td class="biggerFont left"><?php echo $lang['jar-weight']; ?><span class="purchaseNumber"><?php echo $tupperWeight; ?> g</span></td>
   </tr>
   <tr>
    <td class="biggerFont left">Descuento <img src="images/medical-15.png" /><span class="purchaseNumber"><?php echo $medDiscount; ?> %</span></td>
   </tr>
  </table>

 </div>
 </div>

	 
<div style='display: inline-block;'>
<div class="actionbox-np2">
 <div class='mainboxheader'>
  <center><?php echo $lang['add-purchaseprice']; ?></center>
 </div>
 <div class='boxcontent'>
  <table class='purchasetable'>
   <tr>
    <td class="left"><?php echo $lang['add-pergram']; ?><span class='purchaseNumber'><?php echo number_format($purchasePrice,2); ?> &euro;</span></td>
   </tr>
   <tr>
    <td class="left"><?php echo $lang['add-total']; ?><span class='purchaseNumber'><?php echo $purchasePriceTotal; ?> &euro;</span></td>
   </tr>
  </table>
 </div>
 </div>
 <br />
<div class="actionbox-np2">
 <div class='mainboxheader'>
  <center><?php echo $lang['add-dispenseprice']; ?></center>
 </div>
 <div class='boxcontent'>
  <table class='purchasetable'>
   <tr>
    <td class="left"><?php echo $lang['add-pergram']; ?><span class='purchaseNumber'><?php echo number_format($salesPrice,2); ?> &euro;</span></td>
   </tr>
   <tr>
    <td class="left"><?php echo $lang['add-total']; ?><span class='purchaseNumber'><?php echo $salesPriceTotal; ?> &euro;</span></td>
   </tr>
  </table>
 </div>
 </div>
</div>
 	 <div class="actionbox-np2">
 <div class='mainboxheader'>
  <center><?php echo $lang['admin-statistics']; ?></center>
 </div>
 <div class='boxcontent'>
  <form action='' method='POST'>
<?php
	if (isset($_POST['fromDate'])) {
		
		echo <<<EOD
   <input type="text" id="datepicker" name="fromDate" autocomplete="nope" class="fiveDigit defaultinput" value="{$_POST['fromDate']}" />&nbsp;&nbsp;
   <input type="text" id="datepicker2" name="untilDate" autocomplete="nope" class="fiveDigit defaultinput" value="{$_POST['untilDate']}" onchange='this.form.submit()' />
EOD;
	} else {
		
		echo <<<EOD
   <input type="text" id="datepicker" name="fromDate" autocomplete="nope" class="fiveDigit defaultinput" placeholder="{$lang['from-date']}" />&nbsp;&nbsp;
   <input type="text" id="datepicker2" name="untilDate" autocomplete="nope" class="fiveDigit defaultinput" placeholder="{$lang['to-date']}" onchange='this.form.submit()' />
EOD;

	}
?>

<br /><br />
  </form>
  <table class='defaultpad'>
   <tr>
    <td class="left"><?php echo $lang['closeday-dispensed']; ?></td>
    <td class="bigNumber right"><?php echo number_format($sales,2); ?> g</td>
    <td class="bigNumber right"><?php echo number_format($soldPercentage,1); ?> %</td>
   </tr>
   <tr style="border-bottom: 1px solid #bbb;">
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
<?php if ($_SESSION['realWeight'] == 1) { ?>
   <tr>
    <td colspan="3"><strong><?php echo $lang['add-realweight']; ?></strong></td>
   </tr>
   <tr>
    <td class="left"><?php echo $lang['closeday-dispensed']; ?></td>
    <td class="bigNumber right"><?php echo number_format($realSales,2); ?> g</td>
    <td class="bigNumber right"><?php echo number_format($soldPercentageReal,1); ?> %</td>
   </tr>
   <tr style="border-bottom: 1px solid #bbb;">
    <td class="left"><?php echo $lang['gifted']; ?></td>
    <td class="bigNumber right"><?php echo number_format($realgifts,2); ?> g</td>
    <td class="bigNumber right"><?php echo number_format($giftedPercentageReal,1); ?> %</td>
   </tr>
   <tr>
    <td class="left"><strong><?php echo $lang['add-total']; ?></strong></td>
    <td class="bigNumber right"><strong><?php echo number_format($realSales + $realgifts,2); ?> g</strong></td>
    <td class="bigNumber right"><?php echo number_format($totalPercentageReal,1); ?> %</td>
   </tr>
   <tr>
    <td colspan="3">&nbsp;</td>
   </tr>   
<?php } 

	if ($takeoutData) {
		
		echo <<<EOD
		
   <tr>
    <td colspan="3"><strong>{$lang['closeday-takeouts']}</strong></td>
   </tr>

EOD;
		foreach ($takeoutData as $movement) {
				
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
			$result = $pdo3->prepare("$selMovement");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$rowM = $result->fetch();
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
<br />
<div class="actionbox-np2">
 <div class='mainboxheader'>
  <center><?php echo $lang['pur-eststock']; ?></center>
 </div>
 <div class='boxcontent'>
  <table class='purchasetable'>
   <tr>
    <td class="left"><?php echo $lang['global-stock']; ?>:<span class='purchaseNumber'><?php echo number_format($estStock,2); ?> g</span></td>
   </tr>
   <tr>
    <td class="left"><?php echo $lang['pur-displayjar']; ?>:<span class='purchaseNumber'><?php echo number_format($inDisplay,2); ?> g</span></td>
   </tr>
   <tr>
    <td class="left"><?php echo $lang['intstash']; ?>:<span class='purchaseNumber'><?php echo number_format($inStashInt,2); ?> g</span></td>
   </tr>
   <tr>
    <td class="left"><?php echo $lang['extstash']; ?>:<span class='purchaseNumber'><?php echo number_format($inStashExt,2); ?> g</span></td>
   </tr>
   <tr>
    <td class="left"><strong><?php echo $lang['global-total']; ?>:</strong><span class='purchaseNumber'><strong><?php echo number_format($totalProduct,2); ?> g</strong></span></td>
   </tr>
  </table>
 </div>
</div>
<?php if ($noOpening != 'true') { ?>
  
<div class="actionbox-np2">
 <div class='mainboxheader'>
  <center><?php echo $lang['real-stock']; ?>&nbsp;&nbsp;<img src="images/questionmark.png" id="helpmark2" /><div id='helpBox2' class='helpBox2'><?php echo $lang['est-stock-info']; ?></div>
		                <script>
		                  	$('#helpmark2').on({
						 		'mouseover' : function() {
								 	$('#helpBox2').css('display', 'block');
						  		},
						  		'mouseout' : function() {
								 	$('#helpBox2').css('display', 'none');
							  	}
						  	});
						</script></center>
 </div>
 <div class='boxcontent'>
  <table class='purchasetable'>
   <tr>
    <td class="left"><?php echo $lang['global-stock']; ?>:<span class='purchaseNumber'><?php echo number_format($realStock,2); ?> g</td>
   </tr>
   <tr>
    <td class="left"><?php echo $lang['pur-displayjar']; ?>:<span class='purchaseNumber'><?php echo number_format($inDisplay,2); ?> g</td>
   </tr>
   <tr>
    <td class="left"><?php echo $lang['intstash']; ?>:<span class='purchaseNumber'><?php echo number_format($inStashInt,2); ?> g</td>
   </tr>
   <tr>
    <td class="left"><?php echo $lang['extstash']; ?>:<span class='purchaseNumber'><?php echo number_format($inStashExt,2); ?> g</td>
   </tr>
   <tr>
    <td class="left"><strong><?php echo $lang['global-total']; ?>:</strong><span class='purchaseNumber'><strong><?php echo number_format($totalRealProduct,2); ?> g</strong></td>
   </tr>
  </table>
   
<?php } ?>

   </div>

  </div>
  
<br />
 <br />

   


<div class="actionbox-np2">
 <div class='mainboxheader'>
  <center><?php echo $lang['add-movements']; ?></center>
 </div>
 <div class='boxcontent'>
  
  <center>
   <a href="?purchaseid=<?php echo $purchaseid; ?>&summary" class='usergrouptext2'><?php echo $lang['summary']; ?></a>&nbsp;&nbsp;&nbsp;
<!--
   <a href="?purchaseid=<?php echo $purchaseid; ?>&week" class='topLink'>Weekly</a>
   <a href="?purchaseid=<?php echo $purchaseid; ?>&month" class='topLink'>Monthly</a>
-->
   <a href="?purchaseid=<?php echo $purchaseid; ?>&all" class='usergrouptext2'><?php echo $lang['all']; ?></a>
  </center>
  <br />

   
<?php 

	if (isset($_GET['summary'])) {
		
		// Query to look up movements
		$selectMovements = "SELECT type, SUM(quantity), movementTypeid FROM productmovements WHERE purchaseid = $purchaseid GROUP BY movementTypeid ORDER by type DESC, movementTypeid ASC";

		
	} else {
		
		// Query to look up movements
		$selectMovements = "SELECT movementid, movementtime, type, purchaseid, quantity, movementTypeid, comment, price FROM productmovements WHERE purchaseid = $purchaseid ORDER by movementtime ASC";

	}
	
		try
		{
			$result = $pdo3->prepare("$selectMovements");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
?>

  <table class='defaultnobg'>
	  <tbody>
	  
  	  <tr class='green'>
  	   <td><?php echo date("d M H:i", strtotime($purchaseDate . "+$offsetSec seconds")); ?></td>
  	   <td class='left'><?php echo $lang['pur-origpurchase']; ?></td>
  	   <td style='text-align: right;'><?php echo number_format($realQuantity,2); ?> g</td>
  	   <td style='text-align: right;'><?php echo number_format($purchasePrice,2); ?> &euro; / g</td>
  	   <td></td>
  	   <td></td>
	  </tr>
	  
	  <?php

		while ($movement = $result->fetch()) {
		
	$movementid = $movement['movementid'];
	$movementtime = $movement['movementtime'];
	$type = $movement['type'];
	$purchaseid = $movement['purchaseid'];
	$price = $movement['price'];
	$quantity = $movement['quantity'];
	$closedAt = $movement['closedAt'];

	$movementTypeid = $movement['movementTypeid'];
	
	if ($movementTypeid == 1) {
		$editOrNot = "<a href='edit-movement.php?movementid=$movementid'><img src='images/edit.png' height='15' title='Edit' /></a>&nbsp;&nbsp;";
		$ppg = round($price / $quantity,2) . " &euro; / g";
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
			$result2 = $pdo3->prepare("$selectMovementName");
			$result2->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result2->fetch();
				$movementName = $row['movementNamees'];
		} else {
			$selectMovementName = "SELECT movementNameen FROM productmovementtypes WHERE movementTypeid = $movementTypeid";
		try
		{
			$result2 = $pdo3->prepare("$selectMovementName");
			$result2->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result2->fetch();
				$movementName = $row['movementNameen'];
		}
		
		if ($closedAt != NULL) {
			$deleteOrNot = "";
		} else {
			$deleteOrNot = "<a href='javascript:delete_movement(%d,%d)'><img src='images/delete.png' height='15' title='Delete movement' /></a>";
		}
		
	$movement_row =	sprintf("
  	  <tr%s>
  	   <td>%s</td>
  	   <td class='left'>%s</td>
  	   <td style='text-align: right;'>%0.02f g</td>
  	   <td style='text-align: right;'>%s</td>
  	   <td class='centered'><span class='relativeitem'>%s</span></td>
  	   <td class='right'>$editOrNot $deleteOrNot</td>
	  </tr>",
	  $rowclass, $formattedDate, $movementName, $quantity, $ppg, $commentRead, $movementid, $purchaseid
	  );
	  echo $movement_row;
  }
?>

	 </tbody>
	 </table>
	 
	 </div>
	 </div>

	 
<?php if ($adminComment != '') { ?>

<div style='display: inline-block;'>
<div class="actionbox-np2">
 <div class='mainboxheader'>
  <center><?php echo $lang['global-comment']; ?></center>
 </div>
 <div class='boxcontent'>
   <tr>
    <td class="left"><?php echo $adminComment; ?></td>
   </tr>
 </div>
 </div>

 
<?php } ?>
 

 </div> <!-- end leftblock -->
	 <table class="default" id="mvtTable">
	  <thead>
	   <tr>
	    <th><?php echo $lang['global-time']; ?></th>
	    <th><?php echo $lang['global-member']; ?></th>
	    <th><?php echo $lang['global-quantity']; ?></th>
	    <th><?php echo $lang['global-amount']; ?></th>
	   </tr>
	  </thead>
	  <tbody>

<?php 

	// Purchase dispense history: Time, member, quantity, euro

	$selectSales = "SELECT s.saleid, s.saletime, s.userid, d.quantity, d.amount FROM sales s, salesdetails d WHERE s.saleid = d.saleid AND d.purchaseid = $purchaseid ORDER BY s.saletime DESC";
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
		
		$member = getUser($userid);
		
		$sale_row =	sprintf("
	  	  <tr>
	  	   <td class='clickableRow' href='dispense.php?saleid=%d'>%s</td>
	  	   <td class='clickableRow' href='dispense.php?saleid=%d'>%s</td>
	  	   <td style='text-align: right;' class='clickableRow' href='dispense.php?saleid=%d'>%0.2f <span class='smallerfont'>g.</span></td>
	  	   <td style='text-align: right;' class='clickableRow' href='dispense.php?saleid=%d'>%0.2f <span class='smallerfont'>&euro;</span></td>
		  </tr>",
		  $sale['saleid'], $formattedDate, $sale['saleid'], $member, $sale['saleid'], $quantity, $sale['saleid'], $amount
		  );
		  echo $sale_row;

	}
	
	echo "</tbody></table>";

?>   
 
		

<?php // Other products
 } else { ?>


<div id='productoverview'>
  <a href='change-image.php?purchaseid=<?php echo $purchaseid; ?>'><img src='<?php echo $imgname; ?>' height='70' style='display: inline; vertical-align: middle;' /></a>
 <table style="display: inline-block; vertical-align: top;">
  <tr>
   <td><?php echo $lang['global-category']; ?>:</td>
   <td class='yellow fat'><?php echo $categoryName; ?></td>
  </tr>
  <tr>
   <td><?php echo $lang['global-product']; ?>:</td>
   <td class='yellow fat'><?php echo $name; ?> (<?php echo $purchaseid; ?>)</td>
  </tr>
  <tr>
   <td>Fecha de compra:</td>
   <td class='yellow fat'><a href="change-purchase-date.php?purchaseid=<?php echo $purchaseid; ?>"><?php echo date('d-m-Y', strtotime($purchaseDate)); ?></a></td>
  </tr>
 </table>
</div>

<div id="cta1wrap">
<a href="open-purchases.php" class="cta1"><span class="cta1text">&laquo; <?php echo $lang['purchases']; ?></span></a>
<a href="edit-purchase.php?purchaseid=<?php echo $purchaseid;?>" class="cta1"><span class="cta1text"><?php echo $lang['global-edit']; ?></span></a>
<?php
	if ($closedAt == NULL) {
		echo "<a href='add-or-remove.php?purchaseid=$purchaseid' class='cta1'><span class='cta1text'>{$lang['pur-addremove']}</span></a>";
		echo "<a href='close-purchase-2.php?purchaseid=$purchaseid' class='cta1'><span class='cta1text'>{$lang['pur-close']}</span></a>";
	} else {
		//echo "<a href='uTil/reopen-purchase.php?purchaseid=$purchaseid' class='cta1'><span class='cta1text'>{$lang['pur-reopen']}</span></a>";
		echo "<a href='?reopen=$purchaseid' class='cta1'><span class='cta1text'>{$lang['pur-reopen']}</span></a>";
	}
?>
<a href="javascript:delete_purchase(<?php echo $purchaseid; ?>)" class="cta1" style="background-color: red;"><span class="cta1text" style="color: yellow";><?php echo $lang['global-deletecaps']; ?></span></a>

</div>



<br />

<div class='leftblock'>
 <div class='infobox'>
  <h3><?php echo $lang['global-quantity']; ?></h3>
  <table>
   <tr>
    <td class="biggerFont left"><?php echo $lang['add-amountpurchased']; ?></td>
    <td class="biggerNumber right"><?php echo $purchaseQuantity; ?> u</td>
   </tr>
   <tr>
    <td class="biggerFont left"><?php echo $lang['closeproduct-reloads']; ?></td>
    <td class="biggerNumber right"><?php echo number_format($totalReloads,0); ?> u</td>
   </tr>
   <tr style="border-bottom: 1px solid #eee;">
    <td class="biggerFont left"><?php echo $lang['closeday-added']; ?></td>
    <td class="biggerNumber right"><?php echo number_format($shakeAdded,0); ?> u</td>
   </tr>
   <tr style="border-bottom: 2px solid #eee;">
    <td class="biggerFont left"><?php echo $lang['add-totalpurchased']; ?></td>
    <td class="biggerNumber right"><?php echo number_format($totalPurchased,0); ?> u</td>
   </tr>
    <tr>
    <td class="biggerFont left">Descuento <img src="images/medical-15.png" /></td>
    <td class="biggerNumber right"><?php echo $medDiscount; ?> %</td>
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
 <div class='infobox fullwidth2'>
  <h3 class="smallerFont"><?php echo $lang['add-purchaseprice']; ?></h3>
  <table>
   <tr>
    <td class="left"><?php echo $lang['add-perunit']; ?></td>
    <td class="bigNumber right"><?php echo number_format($purchasePrice,2); ?> &euro;</td>
   </tr>
   <tr>
    <td class="left"><?php echo $lang['add-total']; ?></td>
    <td class="bigNumber right"><?php echo $purchasePriceTotal; ?> &euro;</td>
   </tr>
  </table>
 </div>
 <br />
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
    <td class="bigNumber right"><?php echo number_format($sales,0); ?> u</td>
    <td class="bigNumber right"><?php echo number_format($soldPercentage,1); ?> %</td>
   </tr>
   <tr style="border-bottom: 1px solid yellow;">
    <td class="left"><?php echo $lang['gifted']; ?></td>
    <td class="bigNumber right"><?php echo number_format($gifts,0); ?> u</td>
    <td class="bigNumber right"><?php echo number_format($giftedPercentage,1); ?> %</td>
   </tr>
   <tr>
    <td class="left"><strong><?php echo $lang['add-total']; ?></strong></td>
    <td class="bigNumber right"><strong><?php echo number_format($gifts + $sales,0); ?> u</strong></td>
    <td class="bigNumber right"><?php echo number_format($totalPercentage,1); ?> %</td>
   </tr>
   <tr>
    <td colspan="3">&nbsp;</td>
   </tr>
<?php if ($_SESSION['realWeight'] == 1) { ?>
   <tr>
    <td colspan="3"><strong><?php echo $lang['add-realweight']; ?></strong></td>
   </tr>
   <tr>
    <td class="left"><?php echo $lang['closeday-dispensed']; ?></td>
    <td class="bigNumber right"><?php echo number_format($realSales,0); ?> u</td>
    <td class="bigNumber right"><?php echo number_format($soldPercentageReal,1); ?> %</td>
   </tr>
   <tr style="border-bottom: 1px solid yellow;">
    <td class="left"><?php echo $lang['gifted']; ?></td>
    <td class="bigNumber right"><?php echo number_format($realgifts,0); ?> u</td>
    <td class="bigNumber right"><?php echo number_format($giftedPercentageReal,1); ?> %</td>
   </tr>
   <tr>
    <td class="left"><strong><?php echo $lang['add-total']; ?></strong></td>
    <td class="bigNumber right"><strong><?php echo number_format($realSales + $realgifts,0); ?> u</strong></td>
    <td class="bigNumber right"><?php echo number_format($totalPercentageReal,1); ?> %</td>
   </tr>
   <tr>
    <td colspan="3">&nbsp;</td>
   </tr>   
<?php } 

	if ($takeoutData) {
		
		echo <<<EOD
		
   <tr>
    <td colspan="3"><strong>{$lang['closeday-takeouts']}</strong></td>
   </tr>

EOD;
		foreach ($takeoutData as $movement) {
				
			$movementtypeid = $movement['movementtypeid'];
			$movementQuantity = number_format($movement['SUM(quantity)'],0);
			$movementPercentage = number_format((($movementQuantity / $totalPurchased) * 100),1);
		
			// Look up movementtype name
			if ($_SESSION['lang'] == 'en') {
				$selMovement = "SELECT movementNameen AS name from productmovementtypes WHERE movementTypeid = $movementtypeid";
			} else {
				$selMovement = "SELECT movementNamees AS name from productmovementtypes WHERE movementTypeid = $movementtypeid";
			}
		try
		{
			$result = $pdo3->prepare("$selMovement");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$rowM = $result->fetch();
				$movementName = $rowM['name'];
				
			echo <<<EOD
				
   <tr>
    <td class="left">$movementName</td>
    <td class="bigNumber right">$movementQuantity u</td>
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

 
 <div class='infobox fullwidth2'>
  <h3 class="smallerFont"><?php echo $lang['add-dispenseprice']; ?></h3>
  <table>
   <tr>
    <td class="left"><?php echo $lang['add-perunit']; ?></td>
    <td class="bigNumber right"><?php echo number_format($salesPrice,2); ?> &euro;</td>
   </tr>
   <tr>
    <td class="left"><?php echo $lang['add-total']; ?></td>
    <td class="bigNumber right"><?php echo $salesPriceTotal; ?> &euro;</td>
   </tr>
  </table>
 </div>
 <br />
 
 
 <div class='infobox fullwidth2'>
  <h3 class="smallerFont"><?php echo $lang['pur-eststock']; ?> <a href="stock.php" class="smallerFont"></a></h3>
  <table>
   <tr>
    <td class="left"><?php echo $lang['global-stock']; ?>:</td>
    <td class="bigNumber right"><?php echo number_format($estStock,0); ?> u</td>
   </tr>
   <tr>
    <td class="left"><?php echo $lang['pur-displayjar']; ?>:</td>
    <td class="bigNumber right"><?php echo number_format($inDisplay,0); ?> u</td>
   </tr>
   <tr>
    <td class="left"><?php echo $lang['intstash']; ?>:</td>
    <td class="bigNumber right"><?php echo number_format($inStashInt,0); ?> u</td>
   </tr>
   <tr style="border-bottom: 1px solid yellow;">
    <td class="left"><?php echo $lang['extstash']; ?>:</td>
    <td class="bigNumber right"><?php echo number_format($inStashExt,0); ?> u</td>
   </tr>
   <tr>
    <td class="left"><strong><?php echo $lang['global-total']; ?>:</strong></td>
    <td class="bigNumber right"><strong><?php echo number_format($totalProduct,0); ?> u</strong></td>
   </tr>
  </table>
<?php if ($noOpening != 'true') { ?>
  
  <br /><br />
  <h3 class="smallerFont">Stock real&nbsp;&nbsp;<img src="images/questionmark.png" id="helpmark2" /><div id='helpBox2' class='helpBox2'><strong><?php echo $lang['est-stock-info']; ?></strong></div>
		                <script>
		                  	$('#helpmark2').on({
						 		'mouseover' : function() {
								 	$('#helpBox2').css('display', 'block');
						  		},
						  		'mouseout' : function() {
								 	$('#helpBox2').css('display', 'none');
							  	}
						  	});
						</script></h3>
  <table>
   <tr>
    <td class="left"><?php echo $lang['global-stock']; ?>:</td>
    <td class="bigNumber right"><?php echo number_format($realStock,0); ?> u</td>
   </tr>
   <tr>
    <td class="left"><?php echo $lang['pur-displayjar']; ?>:</td>
    <td class="bigNumber right"><?php echo number_format($inDisplay,0); ?> u</td>
   </tr>
   <tr>
    <td class="left"><?php echo $lang['intstash']; ?>:</td>
    <td class="bigNumber right"><?php echo number_format($inStashInt,0); ?> u</td>
   </tr>
   <tr style="border-bottom: 1px solid yellow;">
    <td class="left"><?php echo $lang['extstash']; ?>:</td>
    <td class="bigNumber right"><?php echo number_format($inStashExt,0); ?> u</td>
   </tr>
   <tr>
    <td class="left"><strong><?php echo $lang['global-total']; ?>:</strong></td>
    <td class="bigNumber right"><strong><?php echo number_format($totalRealProduct,0); ?> u</strong></td>
   </tr>
  </table>
   
<?php } ?>
   
   </div>

  </div>
 
 <?php if ($adminComment != '') { ?>

<br />
  <div class='infobox comment'>
  <h3 class="smallerFont"><?php echo $lang['global-comment']; ?>:</h3>
  <br /><?php echo $adminComment; ?>
 </div>
 <br />
 
<?php } ?>
  
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

   
<?php 
	if (isset($_GET['summary'])) {
		
		// Query to look up movements
		$selectMovements = "SELECT type, SUM(quantity), movementTypeid FROM productmovements WHERE purchaseid = $purchaseid GROUP BY movementTypeid ORDER by type DESC, movementTypeid ASC";

		
	} else {
		
		// Query to look up movements
		$selectMovements = "SELECT movementid, movementtime, type, purchaseid, quantity, movementTypeid, comment, price FROM productmovements WHERE purchaseid = $purchaseid ORDER by movementtime ASC";

	}
	
		try
		{
			$result = $pdo3->prepare("$selectMovements");
			$result->execute();
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
  	   <td style='text-align: right;'><?php echo number_format($realQuantity,0); ?> u</td>
  	   <td style='text-align: right;'><?php echo number_format($purchasePrice,2); ?> &euro; / u</td>
  	   <td></td>
  	   <td></td>
	  </tr>
	  
	  <?php

		while ($movement = $result->fetch()) {
		
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
			$result2 = $pdo3->prepare("$selectMovementName");
			$result2->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result2->fetch();
				$movementName = $row['movementNamees'];
		} else {
			$selectMovementName = "SELECT movementNameen FROM productmovementtypes WHERE movementTypeid = $movementTypeid";
		try
		{
			$result2 = $pdo3->prepare("$selectMovementName");
			$result2->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result2->fetch();
				$movementName = $row['movementNameen'];
		}
		if ($closedAt != NULL) {
			$deleteOrNot = "";
		} else {
			$deleteOrNot = "<a href='javascript:delete_movement(%d,%d)'><img src='images/delete.png' height='15' title='Delete movement' /></a>";
		}

	
	$movement_row =	sprintf("
  	  <tr%s>
  	   <td>%s</td>
  	   <td class='left'>%s</td>
  	   <td style='text-align: right;'>%0.02f u</td>
  	   <td style='text-align: right;'>$ppg</td>
  	   <td class='centered' style='position: relative;'>%s</td>
  	   <td class='right'>$editOrNot $deleteOrNot</td>
	  </tr>",
	  $rowclass, $formattedDate, $movementName, $quantity, $commentRead, $movementid, $purchaseid
	  );
	  echo $movement_row;
  }
?>

	 </tbody>
	 </table>
	 
	 </div>
	 
 	 <table class="default" id="mvtTable">
	  <thead>
	   <tr>
	    <th><?php echo $lang['global-time']; ?></th>
	    <th><?php echo $lang['global-member']; ?></th>
	    <th><?php echo $lang['global-quantity']; ?></th>
	    <th><?php echo $lang['global-amount']; ?></th>
	   </tr>
	  </thead>
	  <tbody>

<?php 

	// Purchase dispense history: Time, member, quantity, euro

	$selectSales = "SELECT s.saleid, s.saletime, s.userid, d.quantity, d.amount FROM sales s, salesdetails d WHERE s.saleid = d.saleid AND d.purchaseid = $purchaseid ORDER BY s.saletime DESC";
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
		
		$member = getUser($userid);
		
		$sale_row =	sprintf("
	  	  <tr>
	  	   <td class='clickableRow' href='dispense.php?saleid=%d'>%s</td>
	  	   <td class='clickableRow' href='dispense.php?saleid=%d'>%s</td>
	  	   <td style='text-align: right;' class='clickableRow' href='dispense.php?saleid=%d'>%0.2f <span class='smallerfont'>u.</span></td>
	  	   <td style='text-align: right;' class='clickableRow' href='dispense.php?saleid=%d'>%0.2f <span class='smallerfont'>&euro;</span></td>
		  </tr>",
		  $sale['saleid'], $formattedDate, $sale['saleid'], $member, $sale['saleid'], $quantity, $sale['saleid'], $amount
		  );
		  echo $sale_row;

	}
	
	echo "</tbody></table>";

?>
<?php } ?>



<br class="clearFloat">

<?php } displayFooter(); ?>
