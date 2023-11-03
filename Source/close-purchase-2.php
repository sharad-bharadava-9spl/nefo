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
	if (isset($_POST['realClosing'])) {
		
		$estClosing = $_POST['estClosing'];
		$realClosing = $_POST['realClosing'];
		$purchaseid = $_POST['purchaseid'];
		$reloads = $_POST['reloads'];
		$additions = $_POST['additions'];
		$removals = $_POST['removals'];
		$quantity = $_POST['quantity'];
		$closingComment = str_replace("'","\'",str_replace('%', '&#37;', trim($_POST['closingComment'])));
		$closingDate = date('Y-m-d H:i:s');
		$closingDelta = $realClosing - $estClosing;
		$otherProduct = $_POST['otherProduct'];
		$toProduct = $_POST['toProduct'];
		
		// Add to other product?
		if ($otherProduct == 1) {
			
			// Check if other product has been selected
			if ($toProduct == '' || $toProduct == 0) {
				
				$_SESSION['errorMessage'] = $lang['error-noprodselected'];
				
				pageStart($lang['title-addorremove'], NULL, $validationScript, "paddremove", "admin", $lang['addremove-addorremove'], $_SESSION['successMessage'], $_SESSION['errorMessage']);
					
				exit();
			}
			
		    // Query to look up product names:
			$selectProduct = "SELECT g.name, p.purchaseid FROM flower g, purchases p WHERE p.category = 1 AND p.purchaseid = $toProduct AND p.productid = g.flowerid";
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
			$toName = str_replace("'","\'",str_replace('%', '&#37;', trim($row['name'])));
				
			$selectProduct = "SELECT g.name, p.purchaseid FROM flower g, purchases p WHERE p.category = 1 AND p.purchaseid = $purchaseid AND p.productid = g.flowerid";
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
			$fromName = str_replace("'","\'",str_replace('%', '&#37;', trim($row['name'])));
			
			// Query to add new purchase movement - 6 arguments, add
			  $query = sprintf("INSERT INTO productmovements (movementtime, type, purchaseid, quantity, movementTypeid, comment) VALUES ('%s', '%d', '%d', '%f', '%d', '%s');",
			  $closingDate, '1', $toProduct, $realClosing, '22', $lang['added-from-other-product'] . ': ' . $fromName . '.');
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
				
			$closingComment .= '<br /><br />' . $lang['shake-removed-closing'] . '.<br />' . $lang['added-to-other-product'] . ': ' . $toName . '.';
			
		}
		
			
		
		// Save the rest of the grade as shake - but only if value <> 0!	
		if ($_POST['realClosing'] != 0) {
			
			// Query to add new purchase movement (shake taken out) - 6 arguments
		  	$query = sprintf("INSERT INTO productmovements (movementtime, type, purchaseid, quantity, movementTypeid, comment) VALUES ('%s', '%d', '%d', '%f', '%d', '%s');",
		  		$closingDate, '2', $purchaseid, $realClosing, '14', $closingComment);
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
				
			$removals = $removals + $realClosing;

		}
		
		// Query to update (close) purchase
		$updatePurchase = sprintf("UPDATE purchases SET estClosing = '%f', closedAt = '%f', closingComment = '%s', closedSales = '%f', closedReloads = '%f', closedTakeouts = '%f', closedAdditions = '%f', closingDate = '%s', inMenu = '0' WHERE purchaseid = '%d';",
			$estClosing,
			$closingDelta,
			$closingComment,
			$quantity,
			$reloads,
			$removals,
			$additions,
			$closingDate,
			$purchaseid
);
			
		try
		{
			$result = $pdo3->prepare("$updatePurchase")->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
			
		// On success: redirect.
		$_SESSION['successMessage'] = $lang['closeproduct-successfulclose'];
		header("Location: closed-purchases.php");
		exit();
	}
	/***** FORM SUBMIT END *****/

	
	if (isset($_GET['purchaseid'])) {
		$purchaseid = $_GET['purchaseid'];
	}
else {
		handleError("No product (purchase) specified.","");
	}
	
	$selectPurchase = "SELECT realQuantity, category, productid FROM purchases WHERE purchaseid = $purchaseid";
		try
		{
			$result = $pdo3->prepare("$selectPurchase");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$purchase = $result->fetch();
		$realQuantity = $purchase['realQuantity'];
		$category = $purchase['category'];
		$productid = $purchase['productid'];
		
	$selectSales = "SELECT SUM(realQuantity) FROM salesdetails WHERE purchaseid = $purchaseid";
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
	
		$sales = $result->fetch();
		$quantity = $sales['SUM(realQuantity)'];
		
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
		
				
	// NEW Query to look up movementtypes - first reload, then #10
	$selectReloads = "SELECT SUM(quantity) FROM productmovements WHERE purchaseid = $purchaseid AND type = 1 AND (movementTypeid = 1)";
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
			$reloads = $row['SUM(quantity)'];

			
	// Also include movementtype 3, display jar! No. Only take out permanent takeouts.
	$selectNonReloads = "SELECT SUM(quantity) FROM productmovements WHERE purchaseid = $purchaseid AND type = 1 AND movementTypeid = 10";
		try
		{
			$result = $pdo3->prepare("$selectNonReloads");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
			$nonReloads = $row['SUM(quantity)'];			
			
	// Also include movementtype 3, display jar!
	$selectPermRemovals = "SELECT SUM(quantity) FROM productmovements WHERE purchaseid = $purchaseid AND type = 2 AND (movementTypeid = 4 OR movementTypeid = 7 OR movementTypeid = 8 OR movementTypeid = 11 OR movementTypeid = 13 OR movementTypeid = 14 OR movementTypeid = 15 OR movementTypeid = 16)";
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
		
		$inStash = $inStashInt + $inStashExt;
		
		$estClosing = $realQuantity + $reloads + $nonReloads - $quantity - $permRemovals - $inStash;

				
	$validationScript = <<<EOD
    $(document).ready(function() {
	    
     $('#otherProduct').change(function(){
	     
        if(this.checked)
            $('#otherProductHolder').fadeIn('slow');
        else
            $('#otherProductHolder').fadeOut('slow');

    });

    
	  $('#registerForm').validate({
		  rules: {
			  realClosing: {
				  required: true
			  }
    	},
    	  submitHandler: function() {
   $(".oneClick").attr("disabled", true);
   form.submit();
	    	  }
	  }); // end validate
  }); // end ready
EOD;
/*
   $(".oneClick").attr("disabled", true);
*/
	pageStart($lang['title-closeproduct'], NULL, $validationScript, "pclosePurchase", "", $lang['closeproduct-closeproduct'], $_SESSION['successMessage'], $_SESSION['errorMessage']);
	
	
?>

<div class='productoverview'>
 <table>
  <tr>
   <td class='fat'><?php echo $lang['global-category']; ?>:</td>
   <td class='yellow'><?php echo $categoryName; ?></td>
  </tr>
  <tr>
   <td class='fat'><?php echo $lang['global-strain']; ?>:</td>
   <td class='yellow'><a href='purchase.php?purchaseid=<?php echo $purchaseid; ?>'><?php echo $name; ?></a></td>
  </tr>
 </table>
</div>
<br />
<div class="actionbox">
<form id="registerForm" action="" method="POST">
 <input type='hidden' name='purchaseid' value='<?php echo $purchaseid; ?>' />
 <table>
  <tr>
   <td><?php echo $lang['closeproduct-purchasequantity']; ?>:</td>
   <td><input type="number" lang="nb" class="fourDigit" id="realQuantity" name="realQuantity" value="<?php echo number_format($realQuantity,2,'.',''); ?>" readonly /></td>
  </tr>
  <tr>
   <td>+ <?php echo $lang['closeproduct-reloads']; ?>:</td>
   <td><input type="number" lang="nb" class="fourDigit green" id="reloads" name="reloads" value="<?php echo number_format($reloads,2,'.',''); ?>" readonly /></td>
  </tr>
  <tr>
   <td>+ <?php echo $lang['global-added']; ?>:</td>
   <td><input type="number" lang="nb" class="fourDigit green" id="additions" name="additions" value="<?php echo number_format($nonReloads,2,'.',''); ?>" readonly /></td>
  </tr>
  <tr>
   <td>- <?php echo $lang['closeproduct-takeouts']; ?>:</td>
   <td><input type="number" lang="nb" class="fourDigit red" id="removals" name="removals" value="<?php echo number_format($permRemovals,2,'.',''); ?>" readonly /></td>
  </tr>
  <tr>
   <td>- <?php echo $lang['closeproduct-totaldispensed']; ?>:</td>
   <td><input type="number" lang="nb" class="fourDigit red" id="quantity" name="quantity" value="<?php echo number_format($quantity,2,'.',''); ?>" /></td>
  </tr>
  <tr>
   <td>- <?php echo $lang['stash']; ?>:</td>
   <td><input type="number" lang="nb" class="fourDigit red" id="quantity" name="quantity" value="<?php echo number_format($inStash,2,'.',''); ?>" /></td>
  </tr>
  <tr>
   <td>= <?php echo $lang['closeproduct-estclosing']; ?>:</td>
   <td><input type="number" lang="nb" class="fourDigit" id="estClosing" name="estClosing" value="<?php echo number_format($estClosing,2,'.',''); ?>" readonly /></td>
  </tr>
  <tr>
   <td><?php echo $lang['product-remaining']; ?>:</td>
   <td><input type="number" lang="nb" class="fourDigit" id="realClosing" name="realClosing" step="0.01" /></td>
  </tr>
  <tr>
   <td><?php echo $lang['add-to-other-product']; ?>?</td>
   <td><input type="checkbox" name="otherProduct" id="otherProduct" style="width: 12px;" value="1" /></td>
  </tr>
  <tr>
   <td colspan="2"><span id='otherProductHolder' style='display: none;'><span class="fakelabel">&nbsp;</span><select class="fakeInput" name="toProduct" >
    <option value=""><?php echo $lang['addremove-pleaseselect']; ?>:</option>
<?php

    // Query to look up open products:
	$selectFlowers = "SELECT g.name, g.breed2, p.purchaseid, p.growType FROM flower g, purchases p WHERE p.category = 1 AND p.purchaseid <> $purchaseid AND p.productid = g.flowerid AND p.closedAt IS NULL ORDER BY g.name ASC";
		try
		{
			$result = $pdo3->prepare("$selectFlowers");
			$result->execute();
			$data = $result->fetchAll();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
			
		if ($data) {
			
			
		echo "<option value='0'> -- {$lang['global-flowerscaps']} -- </option>";
			
		foreach ($data as $product) {
			
			$product_row = sprintf("<option value='%d'>%s (%s)</option>",
								 $product['purchaseid'], $product['name'], $product['purchaseid']);
								 
			echo $product_row;
			
		}
		
		echo "<option value='0'></option>";
		

		
	}
	
	$selectExtracts = "SELECT g.name, '' AS breed2, p.purchaseid, '' AS growType FROM extract g, purchases p WHERE p.category = 2 AND p.purchaseid <> $purchaseid AND p.productid = g.extractid AND p.closedAt IS NULL ORDER BY g.name ASC";
		try
		{
			$result = $pdo3->prepare("$selectExtracts");
			$result->execute();
			$data = $result->fetchAll();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
			
		if ($data) {

	echo "<option value='0'> -- {$lang['global-extractscaps']} -- </option>";
		
		foreach ($data as $product) {
			
			$product_row = sprintf("<option value='%d'>%s (%s)</option>",
								 $product['purchaseid'], $product['name'], $product['purchaseid']);
								 
			echo $product_row;
			
		}
		
		echo "<option value='0'></option>";
		
	}
	
	// Look up other gram-based categories
	$selectCats = "SELECT id, name from categories WHERE id > 2 AND type = 1";
		try
		{
			$resultCats = $pdo3->prepare("$selectCats");
			$resultCats->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	

		while ($catDetails = $resultCats->fetch()) {
		
		$name = $catDetails['name'];
		$catID = $catDetails['id'];
		
		// Look up purchases in this category
		$selectProducts = "SELECT g.name, p.purchaseid FROM products g, purchases p WHERE p.category = $catID AND p.purchaseid <> $purchaseid AND p.productid = g.productid AND p.closedAt IS NULL ORDER BY g.name ASC";
		try
		{
			$result = $pdo3->prepare("$selectProducts");
			$result->execute();
			$data = $result->fetchAll();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
			
		if ($data) {
	
		echo "<option value='0'> -- $name -- </option>";
			
		foreach ($data as $product) {
				
				$product_row = sprintf("<option value='%d'>%s (%s)</option>",
									 $product['purchaseid'], $product['name'], $product['purchaseid']);
									 
				echo $product_row;
				
			}
			
			echo "<option value='0'></option>";
			
		}
		
	}
      	

?>
   </select>
   </span>
   </td>
  </tr>
 </table>
 <br />
 
 
 
 <center><textarea name="closingComment" placeholder="<?php echo $lang['global-comment']; ?>?"></textarea></center>
 <br />
 <button name='oneClick' class='oneClick' type="submit"><?php echo $lang['closeproduct-closeproduct']; ?></button>
</form>
</div>



<?php displayFooter(); ?>

