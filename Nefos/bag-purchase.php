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
	if (isset($_POST['bagged'])) {
		
		$e5bags     = $_POST['e5bags'];
		$e5bagsize  = $_POST['e5bagsize'];
		$e10bags    = $_POST['e10bags'];
		$e10bagsize = $_POST['e10bagsize'];
		$comment    = $_POST['comment'];
		$purchaseid = $_POST['purchaseid'];
		
		$e5bagstotal = $e5bags * $e5bagsize;
		$e10bagstotal = $e10bags * $e10bagsize;
			
		$movementtime = date('Y-m-d H:i:s');
			
			
		// Product movement for 5 bags, 25
		if ($e5bags > 0) {
			
			$query = sprintf("INSERT INTO productmovements (movementtime, type, purchaseid, quantity, movementTypeid, comment, noOfBags) VALUES ('%s', '%d', '%d', '%f', '%d', '%s', '%d');",
			  $movementtime, '2', $purchaseid, $e5bagstotal, '25', $comment, $e5bags);
			  
			mysql_query($query)
				or handleError($lang['error-savedata'],"Error adding to jar: " . mysql_error());

		}
		
		// Product movement for 10 bags, 26
		if ($e10bags > 0) {
			
			$query = sprintf("INSERT INTO productmovements (movementtime, type, purchaseid, quantity, movementTypeid, comment, noOfBags) VALUES ('%s', '%d', '%d', '%f', '%d', '%s', '%d');",
			  $movementtime, '2', $purchaseid, $e10bagstotal, '26', $comment, $e10bags);
			  
			mysql_query($query)
				or handleError($lang['error-savedata'],"Error adding to jar: " . mysql_error());

		}

				
		// On success: redirect.
		$_SESSION['successMessage'] = $lang['product-bagged'];
		header("Location: purchase.php?purchaseid=$purchaseid");
		exit();
		
	}
	/***** FORM SUBMIT END *****/
	
	
	// Does purchase ID exist?
	if (!$_GET['purchaseid']) {
		echo $lang['error-nopurchselected'];
		exit();
	} else {
		$purchaseid = $_GET['purchaseid'];
	}
	
	
	// Query to look for purchase
	$purchaseDetails = "SELECT category, productid, purchaseDate, purchasePrice, salesPrice, purchaseQuantity, realQuantity, adminComment, estClosing, closingComment, closedAt, inMenu, growType, closingDate, photoExt, tupperWeight, provider FROM purchases WHERE purchaseid = $purchaseid";
	
	// Does purchase ID exist?
	$purchaseCheck = mysql_query($purchaseDetails);
	if(mysql_num_rows($purchaseCheck) == 0) {
   		handleError($lang['error-purchaseidnotexist'],"");
	}
			
	$result = mysql_query($purchaseDetails)
		or handleError($lang['error-loadpurchase'],"Error loading purchase: " . mysql_error());
	
	if ($result) {
	$row = mysql_fetch_array($result);
		$category = $row['category'];
		$provider = $row['provider'];		
		$productid = $row['productid'];
		$salesPrice = $row['salesPrice'];
		$growtype = $row['growType'];
		$photoExt = $row['photoExt'];
		
		$growDetails = "SELECT name FROM providers WHERE id = $provider";
		
		$result = mysql_query($growDetails)
			or handleError($lang['error-growtypeload'],"Error loading growtype: " . mysql_error());
			
		$row = mysql_fetch_array($result);
			$provider = $row['name'];
		
		$growDetails = "SELECT growtype FROM growtypes WHERE growtypeid = $growtype";
		
		$result = mysql_query($growDetails)
			or handleError($lang['error-growtypeload'],"Error loading growtype: " . mysql_error());
			
		$row = mysql_fetch_array($result);
			$growtype = $row['growtype'];
	
	
			if ($category == 1) {
			// Query to look up flowers
			$selectFlower = "SELECT flowerid, breed2, name FROM flower WHERE flowerid = $productid";

			$resultFlower = mysql_query($selectFlower)
				or handleError($lang['error-loadflowers'],"Error loading flower from db: " . mysql_error());
				
			$row = mysql_fetch_array($resultFlower);
			$name = $row['name'];
			$breed2 = $row['breed2'];
			$categoryName = $lang['global-flower'];
			
		if ($breed2 != '') {
			$name = $name . " x " . $breed2;
		}

			
		} else if ($category == 2) {
			// Query to look up extract
			$selectExtract = "SELECT extractid, extracttype, extract, name FROM extract WHERE extractid = $productid";

			$resultExtract = mysql_query($selectExtract)
				or handleError($lang['error-loadflowers'],"Error loading flower from db: " . mysql_error());
				
			$row = mysql_fetch_array($resultExtract);
				$name = $row['name'];
				$categoryName = $lang['global-extract'];
			
		} else {
			
		// Query to look for category
		$categoryDetails = "SELECT name FROM categories WHERE id = $category";
		
		$result = mysql_query($categoryDetails)
			or handleError($lang['error-errorloadingflower'],"Error loading flower: " . mysql_error());
		
		$row = mysql_fetch_array($result);
			$categoryName = $row['name'];
			
		// Query to look for product
		$selectProducts = "SELECT name from products WHERE productid = $productid";
	
		$resultProducts = mysql_query($selectProducts)
			or handleError($lang['error-loadflowers'],"Error loading flower from db: " . mysql_error());
	
		$row = mysql_fetch_array($resultProducts);
				$name = $row['name'];

		}
	

} else {
		handle_error($lang['error-findinginfo'],"Error locating purchase with ID $purchaseid");
}

	
	
	

	$validationScript = <<<EOD
    $(document).ready(function() {
	    	    
	  $('#registerForm').validate({
		  rules: {
			  name: {
				  required: true
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

	pageStart($lang['bag-it'], NULL, $validationScript, "pnewcategory", "", $lang['bag-it'], $_SESSION['successMessage'], $_SESSION['errorMessage']);
	
	
?>

<div id='productoverview'>
  <a href='change-image.php?purchaseid=<?php echo $purchaseid; ?>'><img src='images/purchases/<?php echo $purchaseid . "." . $photoExt; ?>' height='70' style='display: inline; vertical-align: middle;' /></a>
 <table style="display: inline-block; vertical-align: top; <?php if ($category == '2') { echo 'margin-top: 9px;'; } ?>">
  <tr>
   <td><?php echo $lang['global-category']; ?>:</td>
   <td class='yellow fat'><?php echo $categoryName; ?></td>
  </tr>
  <tr>
   <td><?php echo $lang['global-strain']; ?>:</td>
   <td class='yellow fat'><?php echo $name; ?></td>
  </tr>
  <?php if ($category == '1') { ?>
  <tr>
   <td><?php echo $lang['global-growtype']; ?>:</td>
   <td class='yellow fat'><?php echo $growtype; ?></td>
  </tr>
  <?php } ?>
  <tr>
   <td><?php echo $lang['provider']; ?>:</td>
   <td class='yellow fat'><?php echo $provider; ?></td>
  </tr>
 </table>
</div>

<br />

<form id="registerForm" action="" method="POST" style='text-align: left; font-size: 16px;'>
 <input type="hidden" name="bagged" />
 <input type="hidden" name="purchaseid" value="<?php echo $purchaseid; ?>" />
   <center><strong>Precio al gramo: <?php echo number_format($salesPrice ,2); ?> &euro;<br /></strong></center><br />
   <table>
    <tr>
     <td>Bolsas de&nbsp;&nbsp;&nbsp;</td>
     <td><input type="number" class="twoDigit" value="5" readonly /></td>
     <td>&euro;&nbsp;&nbsp;&nbsp;</td>
     <td><input type="number" class="twoDigit" name="e5bagsize" value="<?php $op1 = 5 / $salesPrice; echo number_format($op1,2); ?>" readonly /></td>
     <td>g.&nbsp;&nbsp;&nbsp;</td>
     <td><input type="number" class="twoDigit" name="e5bags" placeholder="#" /></td>
     <td>u. </td>
    </tr>
    <tr>
     <td>Bolsas de&nbsp;&nbsp;&nbsp;</td>
     <td><input type="number" class="twoDigit" value="10" readonly /></td>
     <td>&euro;&nbsp;&nbsp;&nbsp;</td>
     <td><input type="number" class="twoDigit" name="e10bagsize" value="<?php $op2 = 10 / $salesPrice; echo number_format($op2,2); ?>" readonly /></td>
     <td>g.&nbsp;&nbsp;&nbsp;</td>
     <td><input type="number" class="twoDigit" name="e10bags" placeholder="#" /></td>
     <td>u. </td>
    </tr>
   </table>
<br />
   <center><textarea name="comment" placeholder="<?php echo $lang['global-comment']; ?>"></textarea></center>
   <br />
<br />
 <button class='oneClick' name='oneClick' type="submit"><?php echo $lang['submit']; ?></button>
</form>

<?php displayFooter(); ?>

