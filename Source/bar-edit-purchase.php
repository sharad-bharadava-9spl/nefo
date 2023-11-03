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
	if (isset($_POST['salesPrice'])) {

	$category = $_POST['category'];
	$productID = $_POST['productID'];
	$purchaseQuantity = $_POST['purchaseQuantity'];
	$purchasePrice = $_POST['purchaseppg'];
	$salesPrice = $_POST['salesppg'];
	$adminComment = $_POST['adminComment'];
	$closingComment = $_POST['closingComment'];
	$purchaseid = $_POST['purchaseid'];
	$inMenu = $_POST['inMenu'];
	
	$sample = $_POST['sample'];
	$displayjar = $_POST['displayjar'];
	$intstash = $_POST['intstash'];
	$extstash = $_POST['extstash'];
	$sampleID = $_POST['sampleID'];
	$displayjarID = $_POST['displayjarID'];
	$intstashID = $_POST['intstashID'];
	$extstashID = $_POST['extstashID'];
	$growtype = $_POST['growtype'];
	$purchaseDate = $_POST['purchaseDate'];
	$provider = $_POST['provider'];
	$barCode = $_POST['barCode'];
	
	$tupperWeight = $_POST['tupperWeight'];

		// Update/add/remove initial product movements
		
		// sample taste
		if (($sample == 0 || $sample == '') && $sampleID != '') {
			
			$deleteMovement = "DELETE FROM b_productmovements WHERE movementid = $sampleID";
	
		try
		{
			$result = $pdo3->prepare("$deleteMovement")->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
			
		} else if ($sample > 0) {
			
			if ($sampleID == '') {
				
			  $updateMovement = sprintf("INSERT INTO b_productmovements (movementtime, type, purchaseid, quantity, movementTypeid, doneAtRegistration) VALUES ('%s', '%d', '%d', '%f', '%d', '%d');",
			  $purchaseDate, '2', $purchaseid, $sample, '8', '1');
		  
			} else {
				
		$updateMovement = sprintf("UPDATE b_productmovements SET quantity = '%f' WHERE movementid = $sampleID;",
			$sample
);
			}
		try
		{
			$result = $pdo3->prepare("$updateMovement")->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
		}

		// display jar
		if (($displayjar == 0 || $displayjar == '') && $displayjarID != '') {
			
			$deleteMovement = "DELETE FROM b_productmovements WHERE movementid = $displayjarID";
		try
		{
			$result = $pdo3->prepare("$deleteMovement")->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
			
		} else if ($displayjar > 0) {
			
			if ($displayjarID == '') {
				
			  $updateMovement = sprintf("INSERT INTO b_productmovements (movementtime, type, purchaseid, quantity, movementTypeid, doneAtRegistration) VALUES ('%s', '%d', '%d', '%f', '%d', '%d');",
			  $purchaseDate, '2', $purchaseid, $displayjar, '9', '1');
		  
			} else {
				
		$updateMovement = sprintf("UPDATE b_productmovements SET quantity = '%f' WHERE movementid = $displayjarID;",
			$displayjar
);
			}
		try
		{
			$result = $pdo3->prepare("$updateMovement")->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
		}

		// internal stash
		if (($intstash == 0 || $intstash == '') && $intstashID != '') {
			
			$deleteMovement = "DELETE FROM b_productmovements WHERE movementid = $intstashID";
		try
		{
			$result = $pdo3->prepare("$deleteMovement")->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
			
		} else if ($intstash > 0) {
			
			if ($intstashID == '') {
				
			  $updateMovement = sprintf("INSERT INTO b_productmovements (movementtime, type, purchaseid, quantity, movementTypeid, doneAtRegistration) VALUES ('%s', '%d', '%d', '%f', '%d', '%d');",
			  $purchaseDate, '2', $purchaseid, $intstash, '5', '1');
		  
			} else {
				
		$updateMovement = sprintf("UPDATE b_productmovements SET quantity = '%f' WHERE movementid = $intstashID;",
			$intstash
);
			}
		try
		{
			$result = $pdo3->prepare("$updateMovement")->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
		}

		// external stash
		if (($extstash == 0 || $extstash == '') && $extstashID != '') {
			
			$deleteMovement = "DELETE FROM b_productmovements WHERE movementid = $extstashID";
		try
		{
			$result = $pdo3->prepare("$deleteMovement")->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
			
		} else if ($extstash > 0) {
			
			if ($extstashID == '') {
				
			  $updateMovement = sprintf("INSERT INTO b_productmovements (movementtime, type, purchaseid, quantity, movementTypeid, doneAtRegistration) VALUES ('%s', '%d', '%d', '%f', '%d', '%d');",
			  $purchaseDate, '2', $purchaseid, $extstash, '6', '1');
		  
			} else {
				
		$updateMovement = sprintf("UPDATE b_productmovements SET quantity = '%f' WHERE movementid = $extstashID;",
			$extstash
);
			}
		try
		{
			$result = $pdo3->prepare("$updateMovement")->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
		}
		
		
		// Query to update purchase
		$updatePurchase = sprintf("UPDATE b_purchases SET purchasePrice = '%f', salesPrice = '%f', purchaseQuantity = '%f', adminComment = '%s', closingComment = '%s', inMenu = '%d', provider = '%d', barCode = '%s' WHERE purchaseid = $purchaseid;",
			$purchasePrice,
			$salesPrice,
			$purchaseQuantity,
			$adminComment,
			$closingComment,
			$inMenu,
			$provider,
			$barCode
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
		$_SESSION['successMessage'] = $lang['purchases-updatesuccess'];
		header("Location: bar-purchase.php?purchaseid={$purchaseid}");
		exit();
	}
	/***** FORM SUBMIT END *****/
	
	
	
	// Does purchase ID exist?
	if (!$_GET['purchaseid']) {
		echo $lang['error-nopurchselected'];
		exit();
	} else  {
		$purchaseid = $_GET['purchaseid'];
	}

	
	$validationScript = <<<EOD
    $(document).ready(function() {
	    	    
	  $('#registerForm').validate({
		  rules: {
			  purchaseQuantity: {
				  required: true
			  },
			  realQuantity: {
				  required: true
			  },
			  purchaseppg: {
				  required:"#inMenu:checked"
			  },
			  salesppg: {
				  required:"#inMenu:checked"
			  }
    	}, // end rules
    	errorPlacement: function(error, element) { },
    	  submitHandler: function() {
   $(".oneClick").attr("disabled", true);
   form.submit();
	    	  }
	  }); // end validate
  }); // end ready
EOD;

	// Query to look for purchase
	$purchaseDetails = "SELECT category, productid, purchaseDate, purchasePrice, salesPrice, purchaseQuantity, adminComment, estClosing, closingComment, closedAt, inMenu, provider, barCode FROM b_purchases WHERE purchaseid = $purchaseid";
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
			$inMenu = $row['inMenu'];
			$perGramPurchase = number_format($purchasePrice,2);
			$perGramSale = number_format($salesPrice,2);
			$purchasePriceTotal = number_format($purchasePrice * $purchaseQuantity,2);
			$salesPriceTotal = number_format($salesPrice * $purchaseQuantity,2);
	$provider = $row['provider'];
	$barCode = $row['barCode'];

	$closeDiff = $closedAt - $estClosing;
	
	$growDetails = "SELECT name FROM b_providers WHERE id = $provider";
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
		$providerName = $row['name'];
	
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
		

		// Look up product movements. Also remember to save the new ones!! Using UPDATE!
		// sample taste
		$sample = "SELECT movementid, quantity FROM b_productmovements WHERE purchaseid = $purchaseid AND movementTypeid = 8 AND doneAtRegistration = 1";
		try
		{
			$result = $pdo3->prepare("$sample");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
				$sample = $row['quantity'];
				$sampleID = $row['movementid'];
				
		// display jar
		$displayjar = "SELECT movementid, quantity FROM b_productmovements WHERE purchaseid = $purchaseid AND movementTypeid = 9 AND doneAtRegistration = 1";
		try
		{
			$result = $pdo3->prepare("$displayjar");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
				$displayjar = $row['quantity'];
				$displayjarID = $row['movementid'];
				
		// internal stash
		$intstash = "SELECT movementid, quantity FROM b_productmovements WHERE purchaseid = $purchaseid AND movementTypeid = 5 AND doneAtRegistration = 1";
		try
		{
			$result = $pdo3->prepare("$intstash");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
				$intstash = $row['quantity'];
				$intstashID = $row['movementid'];
				
		// external stash
		$extstash = "SELECT movementid, quantity FROM b_productmovements WHERE purchaseid = $purchaseid AND movementTypeid = 6 AND doneAtRegistration = 1";
		try
		{
			$result = $pdo3->prepare("$extstash");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
				$extstash = $row['quantity'];
				$extstashID = $row['movementid'];

								


	pageStart($lang['title-editpurchase'], NULL, $validationScript, "ppurchase", "newpurchase2 admin", $lang['admin-editpurchase'], $_SESSION['successMessage'], $_SESSION['errorMessage']);
	
	
?>

<form id="registerForm" action="" method="POST">
    <input type="hidden" name="category" value="<?php echo $category; ?>" />
    <input type="hidden" name="productID" value="<?php echo $productID; ?>" />
    <input type="hidden" name="purchaseid" value="<?php echo $purchaseid; ?>" />
    <input type="hidden" name="sampleID" value="<?php echo $sampleID; ?>" />
    <input type="hidden" name="displayjarID" value="<?php echo $displayjarID; ?>" />
    <input type="hidden" name="intstashID" value="<?php echo $intstashID; ?>" />
    <input type="hidden" name="extstashID" value="<?php echo $extstashID; ?>" />
    <input type="hidden" name="purchaseDate" value="<?php echo $purchaseDate; ?>" />


<div id='productoverview'>
 <table>
  <tr>
   <td><?php echo $lang['global-category']; ?>:</td>
   <td class='yellow'><?php echo $categoryName; ?></td>
  </tr>
  <tr>
   <td><?php echo $lang['global-product']; ?>:</td>
   <td class='yellow'><a href='bar-purchase.php?purchaseid=<?php echo $purchaseid; ?>'><?php echo $name; ?></a></td>
  </tr>
 </table>
</div>
<br />
   <script>
    $(document).ready(function() {

   function compute() {
          var a = $('#purchaseQuantity').val();
          var b = $('#purchasePrice').val();
          var total = b / a;
          var roundedtotal = total.toFixed(2);
          $('#purchaseppg').val(roundedtotal);
        }
   function compute2() {
          var a = $('#purchaseQuantity').val();
          var b = $('#purchaseppg').val();
          var total = a * b;
          var roundedtotal = total.toFixed(2);
          $('#purchasePrice').val(roundedtotal);
        }
   function compute3() {
          var a = $('#purchaseQuantity').val();
          var b = $('#salesPrice').val();
          var total = b / a;
          var roundedtotal = total.toFixed(2);
          $('#salesppg').val(roundedtotal);
        }
   function compute4() {
          var a = $('#purchaseQuantity').val();
          var b = $('#salesppg').val();
          var total = a * b;
          var roundedtotal = total.toFixed(2);
          $('#salesPrice').val(roundedtotal);
        }

        $('#purchaseQuantity').on('keypress keyup', compute2);
        $('#purchaseQuantity').on('keypress keyup', compute4);
        $('#purchaseppg').on('keypress keyup', compute2);
        $('#salesppg').on('keypress keyup', compute4);
        $('#purchasePrice').on('keypress keyup', compute);
        $('#salesPrice').on('keypress keyup', compute3);

  }); // end ready
   </script>
    
<div class='leftblock'>
 <div class='infobox'>
  <h3><?php echo $lang['global-details']; ?></h3>
  <table>
   <tr>
    <td class="biggerFont"><?php echo $lang['add-amountpurchased']; ?>:</td>
    <td><input type="number" lang="nb" class="fourDigit" id="purchaseQuantity" name="purchaseQuantity" value="<?php echo $purchaseQuantity; ?>" /> u</td>
   </tr>
   <tr>
    <td class="biggerFont"><?php echo $lang['add-showinmenu']; ?>?</td>
    <td><input type="checkbox" name="inMenu" id="inMenu" style="width: 12px;" value="1" <?php if ($inMenu == 1) { echo "checked"; } ?>/></td>
   </tr>
   <tr>
    <td class="biggerFont left"><?php echo $lang['provider']; ?>:</td>
    <td class='left'>
     <select name='provider' style="width: 120px;">
      <option value='<?php echo $provider; ?>'><?php echo $providerName; ?></option>
<?php
	$PRquery = "SELECT id, name FROM b_providers WHERE id <> $provider";
		try
		{
			$results = $pdo3->prepare("$PRquery");
			$results->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		while ($PRtype = $results->fetch()) {
		$id = $PRtype['id'];
		$name = $PRtype['name'];
		
		echo "<option value='$id'>$name</option>";
		
	}
		
?>
     </select>
</td>
   </tr>
  </table>
 </div>
 
 <br />
 <div id="leftinner">
 <div class='infobox fullwidth'>
  <h3 class="smallerFont"><?php echo $lang['add-purchaseprice']; ?></h3>
  <table>
   <tr>
    <td><?php echo $lang['add-perunit']; ?></td>
    <td><input type='number' lang='nb' class='fourDigit' id='purchaseppg' name='purchaseppg' value="<?php echo $perGramPurchase; ?>" /> &euro;</td>
   </tr>
   <tr>
    <td><?php echo $lang['add-total']; ?></td>
    <td><input type="number" lang="nb" id="purchasePrice" name="purchasePrice" class="fourDigit" value="<?php echo $purchasePriceTotal; ?>" /> &euro;</td>
   </tr>
  </table>
 </div>
 <br /> 
 </div>
 
 <div id="rightinner">

 <div class='infobox fullwidth'>
  <h3 class="smallerFont"><?php echo $lang['add-dispenseprice']; ?></h3>
  <table>
   <tr>
    <td><?php echo $lang['add-perunit']; ?></td>
    <td><input type='number' lang='nb' class='fourDigit' id='salesppg' name='salesppg' value="<?php echo $perGramSale; ?>" /> &euro;</td>
   </tr>
   <tr>
    <td><?php echo $lang['add-total']; ?></td>
    <td><input type="number" lang="nb" class="fourDigit" id="salesPrice" name="salesPrice" value="<?php echo $salesPriceTotal; ?>" /> &euro;</td>
   </tr>
  </table>
   </div>

  </div>
  <br />
  
  <div class='infobox fullwidth3'>
  <h3 class="smallerFont"><?php echo $lang['add-initialmovements']; ?></h3>
  <table>
   <tr>
    <td><?php echo $lang['add-sampletaste']; ?></td>
    <td><input type='number' lang='nb' class='fourDigit' name='sample' value='<?php echo $sample; ?>' /> u</td>
   </tr>
   <tr>
    <td><?php echo $lang['add-displayjar']; ?></td>
    <td><input type='number' lang='nb' class='fourDigit' name='displayjar' value='<?php echo $displayjar; ?>' /> u</td>
   </tr>
   <tr>
    <td><?php echo $lang['add-stashedint']; ?></td>
    <td><input type='number' lang='nb' class='fourDigit' name='intstash' value='<?php echo $intstash; ?>' /> u</td>
   </tr>
   <tr>
    <td><?php echo $lang['add-stashedext']; ?></td>
    <td><input type='number' lang='nb' class='fourDigit' name='extstash' value='<?php echo $extstash; ?>' /> u</td>
   </tr>
  </table>
   </div>

  <br />
   <div class='infobox'>
  <h3 class="smallerFont"><?php echo $lang['global-comment']; ?></h3>
<textarea name="adminComment" placeholder="<?php echo $lang['global-comment']; ?>"><?php echo $adminComment; ?></textarea><br /><br />
   </div>
   <div class='infobox'>
  <h3 class="smallerFont">Codigo de barra</h3>
	<input type='text' lang='nb' class='eightDigit' name='barCode' value='<?php echo $barCode; ?>' /><br /><br />
   </div>
   

<?php
	if ($closedAt != NULL) {
		echo "<div class='infobox fullwidth2'>";
		echo "<h3 class='smallerFont'>Closing details</h3>";
		echo "Product closed at: $closedAt g ($closeDiff g)<br />";
		echo "Closing comment: <em>$closingComment</em><br /><br />";
		echo "<a href='close-purchase-2.php?purchaseid=$purchaseid'>Edit closing details</a><br />";
		echo "</div>";
}
?>
</div> <!-- end leftblock -->


 <button class='oneClick' name='oneClick' type="submit"><?php echo $lang['global-savechanges']; ?></button>
  </form>

<?php  displayFooter(); ?>

