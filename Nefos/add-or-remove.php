<?php
	
	require_once 'cOnfig/connection.php';
	require_once 'cOnfig/view.php';
	require_once 'cOnfig/authenticate.php';
	require_once 'cOnfig/languages/common.php';
	
	session_start();
	$accessLevel = '3';
	
	// Authenticate & authorize
	authorizeUser($accessLevel);
	
	// Did this page resubmit on itself with a form?
	
	// Add to jar
	if ($_POST['productAdd'] == 'complete') {
		
		$movementTypeid = $_POST['movementTypeid'];
		$purchaseid = $_POST['purchaseid'];
		$quantity = $_POST['quantity'];
		$comment = $_POST['comment'];
		$movementtime = date('Y-m-d H:i:s');
		
		// Query to add new purchase movement - 6 arguments
		  $query = sprintf("INSERT INTO productmovements (movementtime, type, purchaseid, quantity, movementTypeid, comment) VALUES ('%s', '%d', '%d', '%f', '%d', '%s');",
		  $movementtime, '1', $purchaseid, $quantity, $movementTypeid, $comment);
		  
		mysql_query($query)
			or handleError($lang['error-savedata'],"Error adding to jar: " . mysql_error());

		// On success: redirect.
		$_SESSION['successMessage'] = $quantity . " " . $lang['addremove-gramsadded'];
		header("Location: purchase.php?purchaseid=" . $purchaseid);
		exit();
	}
	
	// Remove from jar
	if ($_POST['productRemove'] == 'complete') {
		
		$movementTypeid = $_POST['movementTypeid'];
		$purchaseid = $_POST['purchaseid'];
		$quantity = $_POST['quantity'];
		$comment = $_POST['comment'];
		$movementtime = date('Y-m-d H:i:s');
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
			
			// Check if movement is valid for adding to other product
			if ($movementTypeid == 4 || $movementTypeid == 5 || $movementTypeid == 6 || $movementTypeid == 7 || $movementTypeid == 8 || $movementTypeid == 9) {
				
				if ($_SESSION['lang'] == 'es') {
					
					$movementType = "SELECT movementNamees FROM productmovementtypes WHERE movementTypeid = $movementTypeid";
					
					$result = mysql_query($movementType)
						or handleError($lang['error-usersload'],"Error loading users from db: " . mysql_error());
						
					$row = mysql_fetch_array($result);
						$movementName = $row['movementNamees'];
						
					$_SESSION['errorMessage'] = $lang['can-not-add-to-product'] . ":<br />" . $movementName;

				} else {
					
					$movementType = "SELECT movementNameen FROM productmovementtypes WHERE movementTypeid = $movementTypeid";
					
					$result = mysql_query($movementType)
						or handleError($lang['error-usersload'],"Error loading users from db: " . mysql_error());
						
					$row = mysql_fetch_array($result);
						$movementName = $row['movementNameen'];
						
					$_SESSION['errorMessage'] = $lang['can-not-add-to-product'] . ":<br />" . $movementName;
				
				}
			
				pageStart($lang['title-addorremove'], NULL, $validationScript, "paddremove", "admin", $lang['addremove-addorremove'], $_SESSION['successMessage'], $_SESSION['errorMessage']);
					
				exit();
				
			}
			
		    // Query to look up product names:
			$selectProduct = "SELECT g.name, p.purchaseid FROM flower g, purchases p WHERE p.category = 1 AND p.purchaseid = $toProduct AND p.productid = g.flowerid";
			
			$resultProduct = mysql_query($selectProduct)
				or handleError($lang['error-prodprices'],"Error loading flower prices from db: " . mysql_error());

			$row = mysql_fetch_array($resultProduct);
				$toName = $row['name'];
				
			$selectProduct = "SELECT g.name, p.purchaseid FROM flower g, purchases p WHERE p.category = 1 AND p.purchaseid = $purchaseid AND p.productid = g.flowerid";
			
			$resultProduct = mysql_query($selectProduct)
				or handleError($lang['error-prodprices'],"Error loading flower prices from db: " . mysql_error());

			$row = mysql_fetch_array($resultProduct);
				$fromName = $row['name'];
			
			// Query to add new purchase movement - 6 arguments, add
			  $query = sprintf("INSERT INTO productmovements (movementtime, type, purchaseid, quantity, movementTypeid, comment) VALUES ('%s', '%d', '%d', '%f', '%d', '%s');",
			  $movementtime, '1', $toProduct, $quantity, '22', $lang['added-from-other-product'] . ': ' . $fromName . '.');
			  
			mysql_query($query)
				or handleError($lang['error-savedata'],"Error adding to jar: " . mysql_error());
			
			  // Query to add new purchase movement - 6 arguments, remove
			  $query = sprintf("INSERT INTO productmovements (movementtime, type, purchaseid, quantity, movementTypeid, comment) VALUES ('%s', '%d', '%d', '%f', '%d', '%s');",
			  $movementtime, '2', $purchaseid, $quantity, $movementTypeid, $lang['added-to-other-product'] . ': ' . $toName . '.');
			  
			mysql_query($query)
				or handleError($lang['error-savedata'],"Error removing from jar: " . mysql_error());
				
			
		} else {
		
		
			  // Query to add new purchase movement - 6 arguments
			  $query = sprintf("INSERT INTO productmovements (movementtime, type, purchaseid, quantity, movementTypeid, comment) VALUES ('%s', '%d', '%d', '%f', '%d', '%s');",
			  $movementtime, '2', $purchaseid, $quantity, $movementTypeid, $comment);
			  
			mysql_query($query)
				or handleError($lang['error-savedata'],"Error removing from jar: " . mysql_error());
			
		}

		// On success: redirect.
		$_SESSION['successMessage'] = $quantity . " " . $lang['addremove-gramsremoved'];
		header("Location: purchase.php?purchaseid=" . $purchaseid);
		exit();
	}

	/***** FORM SUBMIT END *****/
	
	
	
	// Get the purchase ID
	
	
	
	
	if (isset($_GET['purchaseid'])) {
		$purchaseid = $_GET['purchaseid'];
	} else if ($_POST['purchaseid'] != '') {
		$purchaseid = $_POST['purchaseid'];
	} else {
		handleError($lang['error-nopurchaseid'],"");
	}
/*	
	// Check if product has been closed (and if so throw an error)
	$closeCheck = "SELECT purchaseid FROM purchases WHERE closedAt IS NULL AND purchaseid = $purchaseid;";
	$closeResult = mysql_query($closeCheck)
		or handleError("Error loading purchase closing data from database.","Error loading purchase closing data from db: " . mysql_error());

			if (mysql_num_rows($closeResult) == 0) {
				handleError("You can't add to closed products!");
			}
*/
	
	// Query to look for purchase
	$purchaseDetails = "SELECT category, productid FROM purchases WHERE purchaseid = $purchaseid";
	
	// Does purchase ID exist?
	$purchaseCheck = mysql_query($purchaseDetails);
	if(mysql_num_rows($purchaseCheck) == 0) {
   		handleError($lang['error-purchaseidnotexist'],"");
	}
			
	$result = mysql_query($purchaseDetails)
		or handleError($lang['error-loadpurchase'],"Error loading purchase: " . mysql_error());
	
	$row = mysql_fetch_array($result);
		$category = $row['category'];
		$productid = $row['productid'];

		
		if ($category == 1) {
			// Query to look up flowers
			$selectFlower = "SELECT flowerid, breed2, name FROM flower WHERE flowerid = {$productid}";

			$resultFlower = mysql_query($selectFlower)
				or handleError($lang['error-loadflowers'],"Error loading flower from db: " . mysql_error());
				
			$row = mysql_fetch_array($resultFlower);
			$name = $row['name'];
			$breed2 = $row['breed2'];
			$categoryName = 'Flower';
			
		if ($breed2 != '') {
			$name = $name . " x " . $breed2;
		}
		
	// Calculate what's in internal stash
	$selectStashedInt = "SELECT SUM(m.quantity) FROM productmovements m, purchases p WHERE p.category = 1 AND m.type = 2 AND (m.movementTypeid = 5 OR movementTypeid = 18) AND m.purchaseid = p.purchaseid AND (p.closedAt IS NULL OR DATE(p.closedAt) = DATE(NOW())) AND (p.purchaseid = $purchaseid)";
	$stashedInt = mysql_query($selectStashedInt)
		or handleError($lang['error-loadstash'],"Error loading stash details from db: " . mysql_error());
	
		$row = mysql_fetch_array($stashedInt);
			$stashedInt = $row['SUM(m.quantity)'];
					
	$selectUnStashedInt = "SELECT SUM(m.quantity) FROM productmovements m, purchases p WHERE p.category = 1 AND m.type = 1 AND (m.movementTypeid = 12 OR movementTypeid = 17) AND m.purchaseid = p.purchaseid AND (p.closedAt IS NULL OR DATE(p.closedAt) = DATE(NOW())) AND (p.purchaseid = $purchaseid)";
	$unStashedInt = mysql_query($selectUnStashedInt)
		or handleError($lang['error-loadstash'],"Error loading stash details from db: " . mysql_error());
	
		$row = mysql_fetch_array($unStashedInt);
			$unStashedInt = $row['SUM(m.quantity)'];
						

		$inStashInt = $stashedInt - $unStashedInt;
				
	// Calculate what's in external stash
	$selectStashedExt = "SELECT SUM(m.quantity) FROM productmovements m, purchases p WHERE p.category = 1 AND m.type = 2 AND (m.movementTypeid = 6 OR movementTypeid = 20) AND m.purchaseid = p.purchaseid AND (p.closedAt IS NULL OR DATE(p.closedAt) = DATE(NOW())) AND (p.purchaseid = $purchaseid)";
	$stashedExt = mysql_query($selectStashedExt)
		or handleError($lang['error-loadstash'],"Error loading stash details from db: " . mysql_error());
	
		$row = mysql_fetch_array($stashedExt);
			$stashedExt = $row['SUM(m.quantity)'];
					
	$selectUnStashedExt = "SELECT SUM(m.quantity) FROM productmovements m, purchases p WHERE p.category = 1 AND m.type = 1 AND (m.movementTypeid = 2 OR movementTypeid = 19) AND m.purchaseid = p.purchaseid AND (p.closedAt IS NULL OR DATE(p.closedAt) = DATE(NOW())) AND (p.purchaseid = $purchaseid)";
	$unStashedExt = mysql_query($selectUnStashedExt)
		or handleError($lang['error-loadstash'],"Error loading stash details from db: " . mysql_error());
	
		$row = mysql_fetch_array($unStashedExt);
			$unStashedExt = $row['SUM(m.quantity)'];
						

		$inStashExt = $stashedExt - $unStashedExt;
			
		} else if ($category == 2) {
			// Query to look up extract
			$selectExtract = "SELECT extractid, extracttype, extract, name FROM extract WHERE extractid = {$productid}";

			$resultExtract = mysql_query($selectExtract)
				or handleError($lang['error-loadflowers'],"Error loading flower from db: " . mysql_error());
				
			$row = mysql_fetch_array($resultExtract);
			$name = $row['name'];
			$categoryName = 'Extract';
			
	// Calculate what's in internal stash
	$selectStashedInt = "SELECT SUM(m.quantity) FROM productmovements m, purchases p WHERE p.category = 2 AND m.type = 2 AND (m.movementTypeid = 5 OR movementTypeid = 18) AND m.purchaseid = p.purchaseid AND (p.closedAt IS NULL OR DATE(p.closedAt) = DATE(NOW())) AND (p.purchaseid = $purchaseid)";
	$stashedInt = mysql_query($selectStashedInt)
		or handleError($lang['error-loadstash'],"Error loading stash details from db: " . mysql_error());
	
		$row = mysql_fetch_array($stashedInt);
			$stashedInt = $row['SUM(m.quantity)'];
					
	$selectUnStashedInt = "SELECT SUM(m.quantity) FROM productmovements m, purchases p WHERE p.category = 2 AND m.type = 1 AND (m.movementTypeid = 12 OR movementTypeid = 17) AND m.purchaseid = p.purchaseid AND (p.closedAt IS NULL OR DATE(p.closedAt) = DATE(NOW())) AND (p.purchaseid = $purchaseid)";
	$unStashedInt = mysql_query($selectUnStashedInt)
		or handleError($lang['error-loadstash'],"Error loading stash details from db: " . mysql_error());
	
		$row = mysql_fetch_array($unStashedInt);
			$unStashedInt = $row['SUM(m.quantity)'];
						

		$inStashInt = $stashedInt - $unStashedInt;
		
		
	// Calculate what's in external stash
	$selectStashedExt = "SELECT SUM(m.quantity) FROM productmovements m, purchases p WHERE p.category = 2 AND m.type = 2 AND (m.movementTypeid = 6 OR movementTypeid = 20) AND m.purchaseid = p.purchaseid AND (p.closedAt IS NULL OR DATE(p.closedAt) = DATE(NOW())) AND (p.purchaseid = $purchaseid)";
	$stashedExt = mysql_query($selectStashedExt)
		or handleError($lang['error-loadstash'],"Error loading stash details from db: " . mysql_error());
	
		$row = mysql_fetch_array($stashedExt);
			$stashedExt = $row['SUM(m.quantity)'];
					
	$selectUnStashedExt = "SELECT SUM(m.quantity) FROM productmovements m, purchases p WHERE p.category = 2 AND m.type = 1 AND (m.movementTypeid = 2 OR movementTypeid = 19) AND m.purchaseid = p.purchaseid AND (p.closedAt IS NULL OR DATE(p.closedAt) = DATE(NOW())) AND (p.purchaseid = $purchaseid)";
	$unStashedExt = mysql_query($selectUnStashedExt)
		or handleError($lang['error-loadstash'],"Error loading stash details from db: " . mysql_error());
	
		$row = mysql_fetch_array($unStashedExt);
			$unStashedExt = $row['SUM(m.quantity)'];
						

		$inStashExt = $stashedExt - $unStashedExt;
		

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

	// Calculate what's in internal stash
	$selectStashedInt = "SELECT SUM(m.quantity) FROM productmovements m, purchases p WHERE p.category = $category AND m.type = 2 AND (m.movementTypeid = 5 OR movementTypeid = 18) AND m.purchaseid = p.purchaseid AND (p.closedAt IS NULL OR DATE(p.closedAt) = DATE(NOW())) AND (p.purchaseid = $purchaseid)";
	$stashedInt = mysql_query($selectStashedInt)
		or handleError($lang['error-loadstash'],"Error loading stash details from db: " . mysql_error());
	
		$row = mysql_fetch_array($stashedInt);
			$stashedInt = $row['SUM(m.quantity)'];
					
	$selectUnStashedInt = "SELECT SUM(m.quantity) FROM productmovements m, purchases p WHERE p.category = $category AND m.type = 1 AND (m.movementTypeid = 12 OR movementTypeid = 17) AND m.purchaseid = p.purchaseid AND (p.closedAt IS NULL OR DATE(p.closedAt) = DATE(NOW())) AND (p.purchaseid = $purchaseid)";
	$unStashedInt = mysql_query($selectUnStashedInt)
		or handleError($lang['error-loadstash'],"Error loading stash details from db: " . mysql_error());
	
		$row = mysql_fetch_array($unStashedInt);
			$unStashedInt = $row['SUM(m.quantity)'];
			

		$inStashInt = $stashedInt - $unStashedInt;
		
		
	// Calculate what's in external stash
	$selectStashedExt = "SELECT SUM(m.quantity) FROM productmovements m, purchases p WHERE p.category = $category AND m.type = 2 AND (m.movementTypeid = 6 OR movementTypeid = 20) AND m.purchaseid = p.purchaseid AND (p.closedAt IS NULL OR DATE(p.closedAt) = DATE(NOW())) AND (p.purchaseid = $purchaseid)";
	$stashedExt = mysql_query($selectStashedExt)
		or handleError($lang['error-loadstash'],"Error loading stash details from db: " . mysql_error());
	
		$row = mysql_fetch_array($stashedExt);
			$stashedExt = $row['SUM(m.quantity)'];
					
	$selectUnStashedExt = "SELECT SUM(m.quantity) FROM productmovements m, purchases p WHERE p.category = $category AND m.type = 1 AND (m.movementTypeid = 2 OR movementTypeid = 19) AND m.purchaseid = p.purchaseid AND (p.closedAt IS NULL OR DATE(p.closedAt) = DATE(NOW())) AND (p.purchaseid = $purchaseid)";
	$unStashedExt = mysql_query($selectUnStashedExt)
		or handleError($lang['error-loadstash'],"Error loading stash details from db: " . mysql_error());
	
		$row = mysql_fetch_array($unStashedExt);
			$unStashedExt = $row['SUM(m.quantity)'];
						

		$inStashExt = $stashedExt - $unStashedExt;
			
		}

	$validationScript = <<<EOD
    $(document).ready(function() {
	    	    
     $('#otherProduct').change(function(){
	     
        if(this.checked)
            $('#otherProductHolder').fadeIn('slow');
        else
            $('#otherProductHolder').fadeOut('slow');

    });
    		
		
	  $('#registerForm1').validate({
		  rules: {
			  movementTypeid: {
				  required: true
			  },
			  quantity: {
				  required: true
			  }
    	},
		  errorPlacement: function(error, element) {
			  if ( element.is(":radio") || element.is(":checkbox")){
				 error.appendTo(element.parent());
			} else {
				return true;
			}
		},
    	  submitHandler: function() {
   $(".oneClick").attr("disabled", true);
   form.submit();
	    	  }
	  }); // end validate
	  $('#registerForm2').validate({
		  rules: {
			  movementTypeid: {
				  required: true
			  },
			  quantity: {
				  required: true
			  }
    	},
		  errorPlacement: function(error, element) {
			  if ( element.is(":radio") || element.is(":checkbox")){
				 error.appendTo(element.parent());
			} else {
				return true;
			}
		},
    	  submitHandler: function() {
   $(".oneClick").attr("disabled", true);
   form.submit();
	    	  }
	  }); // end validate
  }); // end ready
EOD;

	
	pageStart($lang['title-addorremove'], NULL, $validationScript, "paddremove", "admin", $lang['addremove-addorremove'], $_SESSION['successMessage'], $_SESSION['errorMessage']);

	
?>


<div id='productoverview'>
 <table>
  <tr>
   <td><?php echo $lang['global-category']; ?>:</td>
   <td class='yellow fat'><?php echo $categoryName; ?></td>
  </tr>
  <tr>
   <td><?php echo $lang['global-strain']; ?>:</td>
   <td class='yellow fat'><a href='purchase.php?purchaseid=<?php echo $purchaseid; ?>'><?php echo $name; ?></a></td>
  </tr>
 </table>
</div>
<br />

<?php if ($category < 3) { ?>

<div class="actionbox">

 <h2><img src="images/add-to-jar.png" class="midalign" />&nbsp;&nbsp;&nbsp;<span><?php echo $lang['addremove-addtojar']; ?></span></h2>
 <form id="registerForm1" action="" method="POST">
  <span class="fakelabel"><?php echo $lang['global-source']; ?>:</span>
   <select class="fakeInput" name="movementTypeid">
    <option value=""><?php echo $lang['addremove-pleaseselect']; ?>:</option>
<?php
      	// Query to look up reasons for adding:
		if ($_SESSION['lang'] == 'es') {
			$movementType = "SELECT movementTypeid, movementNamees FROM productmovementtypes WHERE type = 1 AND movementTypeid < 17 ORDER BY movementNamees ASC";
			$result = mysql_query($movementType)
				or handleError($lang['error-usersload'],"Error loading users from db: " . mysql_error());
		while ($user = mysql_fetch_array($result)) {
			if ($user['movementTypeid'] == '2') { // External stash
				$user_row = sprintf("<option value='%d'>&nbsp;&nbsp;&nbsp;%s (%0.02f g)</option>",
	  								 $user['movementTypeid'], $user['movementNamees'], $inStashExt);
	  			echo $user_row;
			} else if ($user['movementTypeid'] == '12') { // Internal stash
				$user_row = sprintf("<option value='%d'>&nbsp;&nbsp;&nbsp;%s (%0.02f g)</option>",
	  								 $user['movementTypeid'], $user['movementNamees'], $inStashInt);
	  			echo $user_row;
			} else {
				$user_row = sprintf("<option value='%d'>&nbsp;&nbsp;&nbsp;%s</option>",
	  								 $user['movementTypeid'], $user['movementNamees']);
	  			echo $user_row;
  			}
		}
		} else {
			$movementType = "SELECT movementTypeid, movementNameen FROM productmovementtypes WHERE type = 1 AND movementTypeid < 17 ORDER BY movementNameen ASC";
			$result = mysql_query($movementType)
				or handleError($lang['error-usersload'],"Error loading users from db: " . mysql_error());
		while ($user = mysql_fetch_array($result)) {
			if ($user['movementTypeid'] == '2') { // External stash
				$user_row = sprintf("<option value='%d'>&nbsp;&nbsp;&nbsp;%s (%0.02f g)</option>",
	  								 $user['movementTypeid'], $user['movementNameen'], $inStashExt);
	  			echo $user_row;
			} else if ($user['movementTypeid'] == '12') { // Internal stash
				$user_row = sprintf("<option value='%d'>&nbsp;&nbsp;&nbsp;%s (%0.02f g)</option>",
	  								 $user['movementTypeid'], $user['movementNameen'], $inStashInt);
	  			echo $user_row;
			} else {
				$user_row = sprintf("<option value='%d'>&nbsp;&nbsp;&nbsp;%s</option>",
	  								 $user['movementTypeid'], $user['movementNameen']);
	  			echo $user_row;
  			}
		}
		}
      	
      	
      	

?>
   </select>
  <br />
  <span class="fakelabel"><?php echo $lang['global-quantity']; ?>:</span><input type="number" lang="nb" name="quantity" placeholder="g." class="fourDigit" step="0.01" />
  <br />
  <span class="fakelabel" style="vertical-align: top;"><?php echo $lang['global-comment']; ?>:</span><textarea name="comment" placeholder="<?php echo $lang['global-comment']; ?>?"></textarea>
  <input type='hidden' name='productAdd' value='complete' />
  <input type='hidden' name='purchaseid' value='<?php echo $purchaseid; ?>' />
  <button class='oneClick' name='oneClick' type="submit"><?php echo $lang['global-add']; ?></button>
 </form>
</div>


<div class="actionbox">
 <h2><img src="images/remove-from-jar.png" class="midalign" />&nbsp;&nbsp;&nbsp;<?php echo $lang['addremove-removefromjar']; ?></h2>
 <form id="registerForm2" action="" method="POST">
  <span class="fakelabel"><?php echo $lang['addremove-destination']; ?>:</span>
   <select class="fakeInput" name="movementTypeid">
    <option value=""><?php echo $lang['addremove-pleaseselect']; ?>:</option>
<?php
      	// Query to look up reasons for removing:
		if ($_SESSION['lang'] == 'es') {
			$movementType = "SELECT movementTypeid, movementNamees FROM productmovementtypes WHERE type = 2 AND movementTypeid < 17 ORDER BY movementNamees ASC";
			$result = mysql_query($movementType)
				or handleError($lang['error-usersload'],"Error loading users from db: " . mysql_error());
		while ($user = mysql_fetch_array($result)) {
			if ($user['movementTypeid'] == '2') { // External stash
				$user_row = sprintf("<option value='%d'>&nbsp;&nbsp;&nbsp;%s (%0.02f g)</option>",
	  								 $user['movementTypeid'], $user['movementNamees'], $inStashExt);
	  			echo $user_row;
			} else if ($user['movementTypeid'] == '12') { // Internal stash
				$user_row = sprintf("<option value='%d'>&nbsp;&nbsp;&nbsp;%s (%0.02f g)</option>",
	  								 $user['movementTypeid'], $user['movementNamees'], $inStashInt);
	  			echo $user_row;
			} else {
				$user_row = sprintf("<option value='%d'>&nbsp;&nbsp;&nbsp;%s</option>",
	  								 $user['movementTypeid'], $user['movementNamees']);
	  			echo $user_row;
  			}
		}
		} else {
			$movementType = "SELECT movementTypeid, movementNameen FROM productmovementtypes WHERE type = 2 AND movementTypeid < 17 ORDER BY movementNameen ASC";
			$result = mysql_query($movementType)
				or handleError($lang['error-usersload'],"Error loading users from db: " . mysql_error());
		while ($user = mysql_fetch_array($result)) {
			if ($user['movementTypeid'] == '2') { // External stash
				$user_row = sprintf("<option value='%d'>&nbsp;&nbsp;&nbsp;%s (%0.02f g)</option>",
	  								 $user['movementTypeid'], $user['movementNameen'], $inStashExt);
	  			echo $user_row;
			} else if ($user['movementTypeid'] == '12') { // Internal stash
				$user_row = sprintf("<option value='%d'>&nbsp;&nbsp;&nbsp;%s (%0.02f g)</option>",
	  								 $user['movementTypeid'], $user['movementNameen'], $inStashInt);
	  			echo $user_row;
			} else {
				$user_row = sprintf("<option value='%d'>&nbsp;&nbsp;&nbsp;%s</option>",
	  								 $user['movementTypeid'], $user['movementNameen']);
	  			echo $user_row;
  			}
		}
		}
?>
   </select>
  <br />
  <span class="fakelabel">Quantity:</span><input type="number" lang="nb" name="quantity" placeholder="g." class="fourDigit" step="0.01" />
  <br />

  
  <span class="fakelabel" style="vertical-align: top;"><?php echo $lang['global-comment']; ?>:</span><textarea name="comment" placeholder="<?php echo $lang['global-comment']; ?>?"></textarea><br />
  
        <?php echo $lang['add-to-other-product']; ?>? <input type="checkbox" name="otherProduct" id="otherProduct" style="width: 12px;" value="1" /><br />

   <span id='otherProductHolder' style='display: none;'><span class="fakelabel">&nbsp;</span><select class="fakeInput" name="toProduct" >
    <option value=""><?php echo $lang['addremove-pleaseselect']; ?>:</option>
<?php

    // Query to look up open products:
	$selectProducts = "SELECT g.name, g.breed2, p.purchaseid, p.growType FROM flower g, purchases p WHERE p.category = 1 AND p.purchaseid <> $purchaseid AND p.productid = g.flowerid AND p.closedAt IS NULL ORDER BY g.name ASC";
	
	$resultProducts = mysql_query($selectProducts)
		or handleError($lang['error-prodprices'],"Error loading flower prices from db: " . mysql_error());
		
	echo "<option value='0'> -- {$lang['global-flowerscaps']} -- </option>";
		
	while ($product = mysql_fetch_array($resultProducts)) {
		
		$product_row = sprintf("<option value='%d'>%s</option>",
							 $product['purchaseid'], $product['name']);
							 
		echo $product_row;
		
	}
	
	echo "<option value='0'></option>";
	echo "<option value='0'> -- {$lang['global-extractscaps']} -- </option>";
	
	$selectExtracts = "SELECT g.name, '' AS breed2, p.purchaseid, '' AS growType FROM extract g, purchases p WHERE p.category = 2 AND p.purchaseid <> $purchaseid AND p.productid = g.extractid AND p.closedAt IS NULL ORDER BY g.name ASC";
		
	$resultExtracts = mysql_query($selectExtracts)
		or handleError($lang['error-prodprices'],"Error loading flower prices from db: " . mysql_error());
		
	while ($product = mysql_fetch_array($resultExtracts)) {
		
		$product_row = sprintf("<option value='%d'>%s</option>",
							 $product['purchaseid'], $product['name']);
							 
		echo $product_row;
		
	}
      	

?>
   </select>
   </span>
<br />

 <input type='hidden' name='productRemove' value='complete' />
  <input type='hidden' name='purchaseid' value='<?php echo $purchaseid; ?>' />
  <button class='oneClick' name='oneClick' type="submit"><?php echo $lang['addremove-remove']; ?></button>
 </form>
</div>


<?php } else { ?>

<div class="actionbox">

 <h2><img src="images/add-to-jar.png" class="midalign" />&nbsp;&nbsp;&nbsp;<span><?php echo $lang['addremove-addtojar']; ?></span></h2>
 <form id="registerForm1" action="" method="POST">
  <span class="fakelabel"><?php echo $lang['global-source']; ?>:</span>
   <select class="fakeInput" name="movementTypeid">
    <option value=""><?php echo $lang['addremove-pleaseselect']; ?>:</option>
<?php
      	// Query to look up reasons for adding:
		if ($_SESSION['lang'] == 'es') {
			$movementType = "SELECT movementTypeid, movementNamees FROM productmovementtypes WHERE type = 1 AND movementTypeid < 17 ORDER BY movementNamees ASC";
			$result = mysql_query($movementType)
				or handleError($lang['error-usersload'],"Error loading users from db: " . mysql_error());
		while ($user = mysql_fetch_array($result)) {
			if ($user['movementTypeid'] == '2') { // External stash
				$user_row = sprintf("<option value='%d'>&nbsp;&nbsp;&nbsp;%s (%0.02f u)</option>",
	  								 $user['movementTypeid'], $user['movementNamees'], $inStashExt);
	  			echo $user_row;
			} else if ($user['movementTypeid'] == '12') { // Internal stash
				$user_row = sprintf("<option value='%d'>&nbsp;&nbsp;&nbsp;%s (%0.02f u)</option>",
	  								 $user['movementTypeid'], $user['movementNamees'], $inStashInt);
	  			echo $user_row;
			} else {
				$user_row = sprintf("<option value='%d'>&nbsp;&nbsp;&nbsp;%s</option>",
	  								 $user['movementTypeid'], $user['movementNamees']);
	  			echo $user_row;
  			}
		}
		} else {
			$movementType = "SELECT movementTypeid, movementNameen FROM productmovementtypes WHERE type = 1 AND movementTypeid < 17 ORDER BY movementNameen ASC";
			$result = mysql_query($movementType)
				or handleError($lang['error-usersload'],"Error loading users from db: " . mysql_error());
		while ($user = mysql_fetch_array($result)) {
			if ($user['movementTypeid'] == '2') { // External stash
				$user_row = sprintf("<option value='%d'>&nbsp;&nbsp;&nbsp;%s (%0.02f u)</option>",
	  								 $user['movementTypeid'], $user['movementNameen'], $inStashExt);
	  			echo $user_row;
			} else if ($user['movementTypeid'] == '12') { // Internal stash
				$user_row = sprintf("<option value='%d'>&nbsp;&nbsp;&nbsp;%s (%0.02f u)</option>",
	  								 $user['movementTypeid'], $user['movementNameen'], $inStashInt);
	  			echo $user_row;
			} else {
				$user_row = sprintf("<option value='%d'>&nbsp;&nbsp;&nbsp;%s</option>",
	  								 $user['movementTypeid'], $user['movementNameen']);
	  			echo $user_row;
  			}
		}
		}
      	
      	
      	

?>
   </select>
  <br />
  <span class="fakelabel"><?php echo $lang['global-quantity']; ?>:</span><input type="number" lang="nb" name="quantity" placeholder="u." class="fourDigit" step="0.01" />
  <br />
  <span class="fakelabel" style="vertical-align: top;"><?php echo $lang['global-comment']; ?>:</span><textarea name="comment" placeholder="<?php echo $lang['global-comment']; ?>?"></textarea>
  <input type='hidden' name='productAdd' value='complete' />
  <input type='hidden' name='purchaseid' value='<?php echo $purchaseid; ?>' />
  <button class='oneClick' name='oneClick' type="submit"><?php echo $lang['global-add']; ?></button>
 </form>
</div>


<div class="actionbox">
 <h2><img src="images/remove-from-jar.png" class="midalign" />&nbsp;&nbsp;&nbsp;<?php echo $lang['addremove-removefromjar']; ?></h2>
 <form id="registerForm2" action="" method="POST">
  <span class="fakelabel"><?php echo $lang['addremove-destination']; ?>:</span>
   <select class="fakeInput" name="movementTypeid">
    <option value=""><?php echo $lang['addremove-pleaseselect']; ?>:</option>
<?php
      	// Query to look up reasons for removing:
		if ($_SESSION['lang'] == 'es') {
			$movementType = "SELECT movementTypeid, movementNamees FROM productmovementtypes WHERE type = 2 AND movementTypeid < 17 ORDER BY movementNamees ASC";
			$result = mysql_query($movementType)
				or handleError($lang['error-usersload'],"Error loading users from db: " . mysql_error());
		while ($user = mysql_fetch_array($result)) {
			if ($user['movementTypeid'] == '2') { // External stash
				$user_row = sprintf("<option value='%d'>&nbsp;&nbsp;&nbsp;%s (%0.02f u)</option>",
	  								 $user['movementTypeid'], $user['movementNamees'], $inStashExt);
	  			echo $user_row;
			} else if ($user['movementTypeid'] == '12') { // Internal stash
				$user_row = sprintf("<option value='%d'>&nbsp;&nbsp;&nbsp;%s (%0.02f u)</option>",
	  								 $user['movementTypeid'], $user['movementNamees'], $inStashInt);
	  			echo $user_row;
			} else {
				$user_row = sprintf("<option value='%d'>&nbsp;&nbsp;&nbsp;%s</option>",
	  								 $user['movementTypeid'], $user['movementNamees']);
	  			echo $user_row;
  			}
		}
		} else {
			$movementType = "SELECT movementTypeid, movementNameen FROM productmovementtypes WHERE type = 2 AND movementTypeid < 17 ORDER BY movementNameen ASC";
			$result = mysql_query($movementType)
				or handleError($lang['error-usersload'],"Error loading users from db: " . mysql_error());
		while ($user = mysql_fetch_array($result)) {
			if ($user['movementTypeid'] == '2') { // External stash
				$user_row = sprintf("<option value='%d'>&nbsp;&nbsp;&nbsp;%s (%0.02f u)</option>",
	  								 $user['movementTypeid'], $user['movementNameen'], $inStashExt);
	  			echo $user_row;
			} else if ($user['movementTypeid'] == '12') { // Internal stash
				$user_row = sprintf("<option value='%d'>&nbsp;&nbsp;&nbsp;%s (%0.02f u)</option>",
	  								 $user['movementTypeid'], $user['movementNameen'], $inStashInt);
	  			echo $user_row;
			} else {
				$user_row = sprintf("<option value='%d'>&nbsp;&nbsp;&nbsp;%s</option>",
	  								 $user['movementTypeid'], $user['movementNameen']);
	  			echo $user_row;
  			}
		}
		}
?>
   </select>
  <br />
  <span class="fakelabel">Quantity:</span><input type="number" lang="nb" name="quantity" placeholder="u." class="fourDigit" step="0.01" />
  <br />
  <span class="fakelabel" style="vertical-align: top;"><?php echo $lang['global-comment']; ?>:</span><textarea name="comment" placeholder="<?php echo $lang['global-comment']; ?>?"></textarea>
 <input type='hidden' name='productRemove' value='complete' />
  <input type='hidden' name='purchaseid' value='<?php echo $purchaseid; ?>' />
  <button class='oneClick' name='oneClick' type="submit"><?php echo $lang['addremove-remove']; ?></button>
 </form>
</div>

<?php } displayFooter(); ?>
