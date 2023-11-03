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

		
		// Save the rest of the grade as shake - but only if value <> 0!	
		if ($_POST['realClosing'] != 0) {
			
			// Query to add new purchase movement (shake taken out) - 6 arguments
		  	$query = sprintf("INSERT INTO b_productmovements (movementtime, type, purchaseid, quantity, movementTypeid, comment) VALUES ('%s', '%d', '%d', '%f', '%d', '%s');",
		  		$closingDate, '2', $purchaseid, $realClosing, '14', 'Shake added automatically during product closing');
		  
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
		$updatePurchase = sprintf("UPDATE b_purchases SET estClosing = '%f', closedAt = '%f', closingComment = '%s', closedSales = '%f', closedReloads = '%f', closedTakeouts = '%f', closedAdditions = '%f', closingDate = '%s', inMenu = '0' WHERE purchaseid = '%d';",
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
		$_SESSION['successMessage'] = "Product closed succesfully!";
		header("Location: bar-closed-purchases.php");
		exit();
	}
	/***** FORM SUBMIT END *****/

	
	if (isset($_GET['purchaseid'])) {
		$purchaseid = $_GET['purchaseid'];
	}
else {
		handleError("No product (purchase) specified.","");
	}
	
	$selectPurchase = "SELECT purchaseQuantity, category, productid FROM b_purchases WHERE purchaseid = $purchaseid";
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
		$purchaseQuantity = $purchase['purchaseQuantity'];
		$category = $purchase['category'];
		$productid = $purchase['productid'];
		
	$selectSales = "SELECT SUM(quantity) FROM b_salesdetails WHERE purchaseid = $purchaseid";
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
		$quantity = $sales['SUM(quantity)'];
		
			
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
					
				
	// NEW Query to look up movementtypes - first reload, then #10
	$selectReloads = "SELECT SUM(quantity) FROM b_productmovements WHERE purchaseid = $purchaseid AND type = 1 AND (movementTypeid = 1)";
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
	$selectNonReloads = "SELECT SUM(quantity) FROM b_productmovements WHERE purchaseid = $purchaseid AND type = 1 AND movementTypeid = 10";
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
	$selectPermRemovals = "SELECT SUM(quantity) FROM b_productmovements WHERE purchaseid = $purchaseid AND type = 2 AND (movementTypeid = 4 OR movementTypeid = 7 OR movementTypeid = 8 OR movementTypeid = 11 OR movementTypeid = 13 OR movementTypeid = 14 OR movementTypeid = 15 OR movementTypeid = 16)";
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
		
			

	$estClosing = $purchaseQuantity + $reloads + $nonReloads - $quantity - $permRemovals;

				
	$validationScript = <<<EOD
    $(document).ready(function() {
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

<h5><?php echo $name; ?> <span class="usergrouptext" style="margin-bottom: 13px; margin-left: 10px;"><?php echo $categoryName; ?></span></h5>
<br />
<center>
<div class="actionbox-np2">
	<div class='boxcontent'>
<form id="registerForm" action="" method="POST">
 <input type='hidden' name='purchaseid' value='<?php echo $purchaseid; ?>' />
 <table class="np-table">
  <tr>
   <td><?php echo $lang['closeproduct-purchasequantity']; ?>
     <input type="number" lang="nb" class="fourDigit defaultinput-no-margin-smallborder floatright" id="purchaseQuantity" name="purchaseQuantity" value="<?php echo number_format($purchaseQuantity,2,'.',''); ?>" readonly /></td>
  </tr>
  <tr>
   <td>+ <?php echo $lang['closeproduct-reloads']; ?>
     <input type="number" lang="nb" class="fourDigit defaultinput-no-margin-smallborder floatright green" id="reloads" name="reloads" value="<?php echo number_format($reloads,2,'.',''); ?>" readonly /></td>
  </tr>
  <tr>
   <td>+ <?php echo $lang['global-added']; ?>
     <input type="number" lang="nb" class="fourDigit defaultinput-no-margin-smallborder floatright green" id="additions" name="additions" value="<?php echo number_format($nonReloads,2,'.',''); ?>" readonly /></td>
  </tr>
  <tr>
   <td class='red'>- <?php echo $lang['closeproduct-takeouts']; ?>
     <input type="number" lang="nb" class="fourDigit defaultinput-no-margin-smallborder floatright red" id="removals" name="removals" value="<?php echo number_format($permRemovals,2,'.',''); ?>" readonly /></td>
  </tr>
  <tr >
   <td class='red'>- <?php echo $lang['closeproduct-totaldispensed']; ?>
     <input type="number" lang="nb" class="fourDigit defaultinput-no-margin-smallborder floatright red" id="quantity" name="quantity" value="<?php echo number_format($quantity,2,'.',''); ?>" /></td>
  </tr>
  <tr>
   <td><?php echo $lang['closeproduct-estclosing']; ?>
     <input type="number" lang="nb" class="fourDigit defaultinput-no-margin-smallborder floatright" id="estClosing" name="estClosing" value="<?php echo number_format($estClosing,2,'.',''); ?>" readonly /></td>
  </tr>
  <tr>
   <td><?php echo $lang['product-remaining']; ?>
     <input type="number" lang="nb" class="fourDigit defaultinput-no-margin-smallborder floatright" id="realClosing" name="realClosing" step="0.01" /></td>
  </tr>
 </table>
 <br />
 
 
 
 <center><textarea name="closingComment" placeholder="<?php echo $lang['global-comment']; ?>?"></textarea></center>
 <br />
 <button name='oneClick' class='oneClick cta1' type="submit"><?php echo $lang['closeproduct-closeproduct']; ?></button>
</form>
	</div>
</div>
</center>


<?php displayFooter(); ?>

