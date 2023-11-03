<?php
	
	require_once 'cOnfig/connection.php';
	require_once 'cOnfig/view.php';
	require_once 'cOnfig/authenticate.php';
	require_once 'cOnfig/languages/common.php';
	
	session_start();
	$accessLevel = '3';
	
	// Authenticate & authorize
	authorizeUser($accessLevel);
	
	// Almacen only
	if ($_POST['almacen'] == 'complete') {
		
		$movementTypeid = $_POST['movementTypeid'];
		$movementTypeid2 = $_POST['movementTypeid2'];
		$purchaseid = $_POST['purchaseid'];
		$category = $_POST['category'];
		$quantity = $_POST['quantity'];
		$comment = str_replace("'","\'",str_replace('%', '&#37;', trim($_POST['comment'])));
		$movementtime = date('Y-m-d H:i:s');
		
		// Query to add new purchase movement - 6 arguments
		  $query = sprintf("INSERT INTO productmovements (movementtime, type, purchaseid, quantity, movementTypeid, comment, user_id, category) VALUES ('%s', '%d', '%d', '%f', '%d', '%s', '%d', '%d');",
		  $movementtime, '1', $purchaseid, $quantity, $movementTypeid, $comment, $_SESSION['user_id'], $category);
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
			
		  // Query to add new purchase movement - 6 arguments
		  $query = sprintf("INSERT INTO productmovements (movementtime, type, purchaseid, quantity, movementTypeid, comment, user_id, category) VALUES ('%s', '%d', '%d', '%f', '%d', '%s', '%d', '%d');",
		  $movementtime, '2', $purchaseid, $quantity, $movementTypeid2, $comment, $_SESSION['user_id'], $category);
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
				

		// On success: redirect.
		$_SESSION['successMessage'] = $quantity . " " . $lang['addremove-gramsadded'];
		header("Location: purchase.php?purchaseid=" . $purchaseid);
		exit();
	}

	// Add to jar
	if ($_POST['productAdd'] == 'complete') {
		
		$movementTypeid = $_POST['movementTypeid'];
		$purchaseid = $_POST['purchaseid'];
		$category = $_POST['category'];
		$quantity = $_POST['quantity'];
		$realweight = $_POST['realweight'];
		$provider = $_POST['provider'];
		$price = $_POST['price'];
		$paid = $_POST['paid'];
		$comment = str_replace("'","\'",str_replace('%', '&#37;', trim($_POST['comment'])));
		$movementtime = date('Y-m-d H:i:s');
		
		if ($realweight == '' || $realweight == 0) {
			$realweight = $quantity;
		}
		
		// Query to add new purchase movement - 6 arguments
		  $query = sprintf("INSERT INTO productmovements (movementtime, type, purchaseid, quantity, movementTypeid, comment, provider, price, paid, realquantity, user_id, category) VALUES ('%s', '%d', '%d', '%f', '%d', '%s', '%d', '%f', '%f', '%f', '%d', '%d');",
		  $movementtime, '1', $purchaseid, $quantity, $movementTypeid, $comment, $provider, $price, $paid, $realweight, $_SESSION['user_id'], $category);
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

		// On success: redirect.
		$_SESSION['successMessage'] = $quantity . " " . $lang['addremove-gramsadded'];
		header("Location: purchase.php?purchaseid=" . $purchaseid);
		exit();
	}
	
	// Remove from jar
	if ($_POST['productRemove'] == 'complete') {
		
		$movementTypeid = $_POST['movementTypeid'];
		$purchaseid = $_POST['purchaseid'];
		$category = $_POST['category'];
		$quantity = $_POST['quantity'];
		$comment = str_replace("'","\'",str_replace('%', '&#37;', trim($_POST['comment'])));
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
					try
					{
						$result = $pdo3->prepare("$movementType");
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
						
					$_SESSION['errorMessage'] = $lang['can-not-add-to-product'] . ":<br />" . $movementName;

				} else {
					
					$movementType = "SELECT movementNameen FROM productmovementtypes WHERE movementTypeid = $movementTypeid";
					try
					{
						$result = $pdo3->prepare("$movementType");
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
						
					$_SESSION['errorMessage'] = $lang['can-not-add-to-product'] . ":<br />" . $movementName;
				
				}
			
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
				$toName = str_replace('%', '&#37;', $row['name']);
				
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
				$fromName = str_replace('%', '&#37;', $row['name']);
				
			$categoryDetails = "SELECT category FROM purchases WHERE purchaseid = '$toProduct'";
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
				$toCategory = $row['category'];
			
			// Query to add new purchase movement - 6 arguments, add
			  $query = sprintf("INSERT INTO productmovements (movementtime, type, purchaseid, quantity, movementTypeid, comment, user_id, category) VALUES ('%s', '%d', '%d', '%f', '%d', '%s', '%d', '%d');",
			  $movementtime, '1', $toProduct, $quantity, '22', $lang['added-from-other-product'] . ': ' . $fromName . '.', $_SESSION['user_id'], $toCategory);
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
			
			  // Query to add new purchase movement - 6 arguments, remove
			  $query = sprintf("INSERT INTO productmovements (movementtime, type, purchaseid, quantity, movementTypeid, comment, user_id, category) VALUES ('%s', '%d', '%d', '%f', '%d', '%s', '%d', '%d');",
			  $movementtime, '2', $purchaseid, $quantity, $movementTypeid, $lang['added-to-other-product'] . ': ' . $toName . '.', $_SESSION['user_id'], $category);
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
				
			
		} else {
		
		
			  // Query to add new purchase movement - 6 arguments
			  $query = sprintf("INSERT INTO productmovements (movementtime, type, purchaseid, quantity, movementTypeid, comment, user_id, category) VALUES ('%s', '%d', '%d', '%f', '%d', '%s', '%d', '%d');",
			  $movementtime, '2', $purchaseid, $quantity, $movementTypeid, $comment, $_SESSION['user_id'], $category);
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
	
	// Query to look for purchase
	$purchaseDetails = "SELECT category, productid, salesPrice, purchasePrice FROM purchases WHERE purchaseid = $purchaseid";
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
		$salesPrice = $row['salesPrice'];
		$purchasePrice = $row['purchasePrice'];

		
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
			$categoryName = 'Flower (g.)';
			
		if ($breed2 != '') {
			$name = $name . " x " . $breed2;
		}
		
	// Calculate what's in internal stash
	$selectStashedInt = "SELECT SUM(m.quantity) FROM productmovements m, purchases p WHERE p.category = 1 AND m.type = 2 AND (m.movementTypeid = 5 OR movementTypeid = 18) AND m.purchaseid = p.purchaseid AND (p.closedAt IS NULL OR DATE(p.closedAt) = DATE(NOW())) AND (p.purchaseid = $purchaseid)";
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
			$stashedInt = $row['SUM(m.quantity)'];
					
	$selectUnStashedInt = "SELECT SUM(m.quantity) FROM productmovements m, purchases p WHERE p.category = 1 AND m.type = 1 AND (m.movementTypeid = 12 OR movementTypeid = 17) AND m.purchaseid = p.purchaseid AND (p.closedAt IS NULL OR DATE(p.closedAt) = DATE(NOW())) AND (p.purchaseid = $purchaseid)";
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
			$unStashedInt = $row['SUM(m.quantity)'];
						

		$inStashInt = $stashedInt - $unStashedInt;
				
	// Calculate what's in external stash
	$selectStashedExt = "SELECT SUM(m.quantity) FROM productmovements m, purchases p WHERE p.category = 1 AND m.type = 2 AND (m.movementTypeid = 6 OR movementTypeid = 20) AND m.purchaseid = p.purchaseid AND (p.closedAt IS NULL OR DATE(p.closedAt) = DATE(NOW())) AND (p.purchaseid = $purchaseid)";
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
			$stashedExt = $row['SUM(m.quantity)'];
					
	$selectUnStashedExt = "SELECT SUM(m.quantity) FROM productmovements m, purchases p WHERE p.category = 1 AND m.type = 1 AND (m.movementTypeid = 2 OR movementTypeid = 19) AND m.purchaseid = p.purchaseid AND (p.closedAt IS NULL OR DATE(p.closedAt) = DATE(NOW())) AND (p.purchaseid = $purchaseid)";
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
			$unStashedExt = $row['SUM(m.quantity)'];
						

		$inStashExt = $stashedExt - $unStashedExt;
			
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
			$categoryName = 'Extract (g.)';
			
	// Calculate what's in internal stash
	$selectStashedInt = "SELECT SUM(m.quantity) FROM productmovements m, purchases p WHERE p.category = 2 AND m.type = 2 AND (m.movementTypeid = 5 OR movementTypeid = 18) AND m.purchaseid = p.purchaseid AND (p.closedAt IS NULL OR DATE(p.closedAt) = DATE(NOW())) AND (p.purchaseid = $purchaseid)";
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
			$stashedInt = $row['SUM(m.quantity)'];
					
	$selectUnStashedInt = "SELECT SUM(m.quantity) FROM productmovements m, purchases p WHERE p.category = 2 AND m.type = 1 AND (m.movementTypeid = 12 OR movementTypeid = 17) AND m.purchaseid = p.purchaseid AND (p.closedAt IS NULL OR DATE(p.closedAt) = DATE(NOW())) AND (p.purchaseid = $purchaseid)";
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
			$unStashedInt = $row['SUM(m.quantity)'];
						

		$inStashInt = $stashedInt - $unStashedInt;
		
		
	// Calculate what's in external stash
	$selectStashedExt = "SELECT SUM(m.quantity) FROM productmovements m, purchases p WHERE p.category = 2 AND m.type = 2 AND (m.movementTypeid = 6 OR movementTypeid = 20) AND m.purchaseid = p.purchaseid AND (p.closedAt IS NULL OR DATE(p.closedAt) = DATE(NOW())) AND (p.purchaseid = $purchaseid)";
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
			$stashedExt = $row['SUM(m.quantity)'];
					
	$selectUnStashedExt = "SELECT SUM(m.quantity) FROM productmovements m, purchases p WHERE p.category = 2 AND m.type = 1 AND (m.movementTypeid = 2 OR movementTypeid = 19) AND m.purchaseid = p.purchaseid AND (p.closedAt IS NULL OR DATE(p.closedAt) = DATE(NOW())) AND (p.purchaseid = $purchaseid)";
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
			$unStashedExt = $row['SUM(m.quantity)'];
						

		$inStashExt = $stashedExt - $unStashedExt;
		

		} else {
			
		// Query to look for category
		$categoryDetails = "SELECT name, type FROM categories WHERE id = $category";
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
			$type = $row['type'];
			
		if ($type == 0) {
			$type = "u";
		} else {
			$type = "g";
		}

			$categoryName = $row['name'] . " ($type.)";
			
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

	// Calculate what's in internal stash
	$selectStashedInt = "SELECT SUM(m.quantity) FROM productmovements m, purchases p WHERE p.category = $category AND m.type = 2 AND (m.movementTypeid = 5 OR movementTypeid = 18) AND m.purchaseid = p.purchaseid AND (p.closedAt IS NULL OR DATE(p.closedAt) = DATE(NOW())) AND (p.purchaseid = $purchaseid)";
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
			$stashedInt = $row['SUM(m.quantity)'];
					
	$selectUnStashedInt = "SELECT SUM(m.quantity) FROM productmovements m, purchases p WHERE p.category = $category AND m.type = 1 AND (m.movementTypeid = 12 OR movementTypeid = 17) AND m.purchaseid = p.purchaseid AND (p.closedAt IS NULL OR DATE(p.closedAt) = DATE(NOW())) AND (p.purchaseid = $purchaseid)";
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
			$unStashedInt = $row['SUM(m.quantity)'];
			

		$inStashInt = $stashedInt - $unStashedInt;
		
		
	// Calculate what's in external stash
	$selectStashedExt = "SELECT SUM(m.quantity) FROM productmovements m, purchases p WHERE p.category = $category AND m.type = 2 AND (m.movementTypeid = 6 OR movementTypeid = 20) AND m.purchaseid = p.purchaseid AND (p.closedAt IS NULL OR DATE(p.closedAt) = DATE(NOW())) AND (p.purchaseid = $purchaseid)";
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
			$stashedExt = $row['SUM(m.quantity)'];
					
	$selectUnStashedExt = "SELECT SUM(m.quantity) FROM productmovements m, purchases p WHERE p.category = $category AND m.type = 1 AND (m.movementTypeid = 2 OR movementTypeid = 19) AND m.purchaseid = p.purchaseid AND (p.closedAt IS NULL OR DATE(p.closedAt) = DATE(NOW())) AND (p.purchaseid = $purchaseid)";
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
			$unStashedExt = $row['SUM(m.quantity)'];
						

		$inStashExt = $stashedExt - $unStashedExt;
			
		}

	$validationScript = <<<EOD
    $(document).ready(function() {
	  
	    function computeTot() {
	          var a = $('#priceg').val();
	          var b = $('#quantity').val();
	          var total = a * b;
	          var roundedtotal = total.toFixed(2);
	          $('#price').val(roundedtotal);
	          $('#paid').val(roundedtotal);
	   }

	   
$(document).on('click keypress keyup blur', function(event) {
   if (!$(event.target).is("#paid")) {
   	if (!$(event.target).is("#addButton")) {
   	 if (!$(event.target).is("textarea")) {
	    computeTot();
     }
    }
   }
});


		var initialVal = $('#addType').val();
			if(initialVal == 1) {
	        	$("#providerarea").show();				
			}

	    
	    $('#addType').change(function(){
			var val = $(this).val();
		    if(val == 1) {
		        $("#providerarea").fadeIn('slow');
	    	} else {
		        $("#providerarea").fadeOut('slow');
	    	}
	    });
	    

	    
	 $('.customDropDown').select2({
		 dropdownAutoWidth: true		 
	 });
	    	    
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

<?php 	// Check if Category is grams or units
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


	
	if ($category < 3 || $type == 1) {
 ?>

<div class="actionbox">

 <h2><img src="images/add-to-jar.png" class="midalign" />&nbsp;&nbsp;&nbsp;<span><?php echo $lang['addremove-addtojar']; ?></span></h2>
 <form id="registerForm1" action="" method="POST">
  <span class="fakelabel"><?php echo $lang['global-source']; ?>:</span>
   <select class="fakeInput" name="movementTypeid" id="addType">
    <option value=""><?php echo $lang['addremove-pleaseselect']; ?>:</option>
<?php
      	// Query to look up reasons for adding:
		if ($_SESSION['lang'] == 'es') {
			$movementType = "SELECT movementTypeid, movementNamees FROM productmovementtypes WHERE type = 1 AND movementTypeid < 17 ORDER BY movementNamees ASC";
		try
		{
			$result = $pdo3->prepare("$movementType");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		while ($user = $result->fetch()) {
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
		try
		{
			$result = $pdo3->prepare("$movementType");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		while ($user = $result->fetch()) {
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
  <span class="fakelabel"><?php echo $lang['global-quantity']; ?>:</span><input type="number" lang="nb" name="quantity" id="quantity" placeholder="g." class="fourDigit" step="0.01" />
  <br />
  <span id="providerarea" style="display: none;">
  <span class="fakelabel"><?php echo $lang['add-realweightshort']; ?>:</span><input type="number" lang="nb" name="realweight" id="realweight" placeholder="g." class="fourDigit" step="0.01" />
  <br />
  <span class="fakelabel"><?php echo $lang['provider']; ?>:</span>
  <div style='float: right;'>
     <select name='provider' id='provider'>
      <option value='<?php echo $provider; ?>'><?php echo $providerName; ?></option>
<?php
	$PRquery = "SELECT id, name FROM providers WHERE id <> '$provider'";
		try
		{
			$PRresult = $pdo3->prepare("$PRquery");
			$PRresult->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
	while ($PRtype = $PRresult->fetch()) {
		$id = $PRtype['id'];
		$name = $PRtype['name'];
		
		echo "<option value='$id'>$name</option>";
		
	}
		
?>
     </select><br />
     <span id="providerBox">
     <span style='display: inline-block; width: 80px;'><?php echo $lang['price']; ?> /g:</span> <input type="number" lang="nb" name="priceg" id="priceg" class="fourDigit" step="0.01" value="<?php echo $purchasePrice; ?>" />€<br />
     <span style='display: inline-block; width: 80px;'><?php echo $lang['price']; ?>:</span> <input type="number" lang="nb" name="price" id="price" class="fourDigit" step="0.01" />€<br />
     <span style='display: inline-block; width: 80px;'><?php echo $lang['paid']; ?>:<br /></span> <input type="number" lang="nb" name="paid" id="paid" class="fourDigit" step="0.01" />€<br />
     </div>
  <br />
     </span>
  <div class="clearfloat"></div>
  
  <center><textarea name="comment" placeholder="<?php echo $lang['global-comment']; ?>?"></textarea></center>
  <input type='hidden' name='productAdd' value='complete' />
  <input type='hidden' name='purchaseid' value='<?php echo $purchaseid; ?>' />
  <input type='hidden' name='category' value='<?php echo $category; ?>' />
  <button class='oneClick' name='oneClick' type="submit" id="addButton"><?php echo $lang['global-add']; ?></button>
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
		try
		{
			$result = $pdo3->prepare("$movementType");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		while ($user = $result->fetch()) {
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
		try
		{
			$result = $pdo3->prepare("$movementType");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		while ($user = $result->fetch()) {
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
  <span class="fakelabel"><?php echo $lang['global-quantity']; ?>:</span><input type="number" lang="nb" name="quantity" id="quantity" placeholder="g." class="fourDigit" step="0.01" />
  <br />

  
  <center><textarea name="comment" placeholder="<?php echo $lang['global-comment']; ?>?"></textarea><br /></center>
  
        <?php echo $lang['add-to-other-product']; ?>? <input type="checkbox" name="otherProduct" id="otherProduct" style="width: 12px;" value="1" /><br />

   <span id='otherProductHolder' style='display: none;'><span class="fakelabel">&nbsp;</span>
   <select class="fakeInput" name="toProduct" >
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
<br />

 <input type='hidden' name='productRemove' value='complete' />
  <input type='hidden' name='purchaseid' value='<?php echo $purchaseid; ?>' />
  <input type='hidden' name='category' value='<?php echo $category; ?>' />
  <button class='oneClick' name='oneClick' type="submit"><?php echo $lang['addremove-remove']; ?></button>
 </form>
</div>

<div class="actionbox">

 <h2><span>Almacen a almacen</span></h2>
 <form id="registerForm1" action="" method="POST">
  <span class="fakelabel"><?php echo $lang['global-source']; ?>:</span>
   <select class="fakeInput" name="movementTypeid">
    <option value=""><?php echo $lang['addremove-pleaseselect']; ?>:</option>
<?php
      	// Query to look up reasons for adding:
		if ($_SESSION['lang'] == 'es') {
			$movementType = "SELECT movementTypeid, movementNamees FROM productmovementtypes WHERE movementTypeid = 2 OR movementTypeid = 12 ORDER BY movementNamees ASC";
		try
		{
			$result = $pdo3->prepare("$movementType");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		while ($user = $result->fetch()) {
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
			$movementType = "SELECT movementTypeid, movementNameen FROM productmovementtypes WHERE movementTypeid = 2 OR movementTypeid = 12 ORDER BY movementNameen ASC";
		try
		{
			$result = $pdo3->prepare("$movementType");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		while ($user = $result->fetch()) {
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
  <span class="fakelabel"><?php echo $lang['addremove-destination']; ?>:</span>
   <select class="fakeInput" name="movementTypeid2">
    <option value=""><?php echo $lang['addremove-pleaseselect']; ?>:</option>
<?php
      	// Query to look up reasons for removing:
		if ($_SESSION['lang'] == 'es') {
			$movementType = "SELECT movementTypeid, movementNamees FROM productmovementtypes WHERE movementTypeid = 5 OR movementTypeid = 6 ORDER BY movementNamees ASC";
		try
		{
			$result = $pdo3->prepare("$movementType");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		while ($user = $result->fetch()) {
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
			$movementType = "SELECT movementTypeid, movementNameen FROM productmovementtypes WHERE movementTypeid = 5 OR movementTypeid = 6 ORDER BY movementNameen ASC";
		try
		{
			$result = $pdo3->prepare("$movementType");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		while ($user = $result->fetch()) {
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
   </select><br />
  <span class="fakelabel"><?php echo $lang['global-quantity']; ?>:</span><input type="number" lang="nb" name="quantity" id="quantity" placeholder="g." class="fourDigit" step="0.01" />
  <br />
  <center><textarea name="comment" placeholder="<?php echo $lang['global-comment']; ?>?"></textarea></center>
  <input type='hidden' name='almacen' value='complete' />
  <input type='hidden' name='purchaseid' value='<?php echo $purchaseid; ?>' />
  <input type='hidden' name='category' value='<?php echo $category; ?>' />
  <button class='oneClick' name='oneClick' type="submit" id="addButton"><?php echo $lang['global-add']; ?></button>
 </form>
</div>


<?php } else { ?>

<div class="actionbox">

 <h2><img src="images/add-to-jar.png" class="midalign" />&nbsp;&nbsp;&nbsp;<span><?php echo $lang['addremove-addtojar']; ?></span></h2>
 <form id="registerForm1" action="" method="POST">
  <span class="fakelabel"><?php echo $lang['global-source']; ?>:</span>
   <select class="fakeInput" name="movementTypeid" id="addType">
    <option value=""><?php echo $lang['addremove-pleaseselect']; ?>:</option>
<?php
      	// Query to look up reasons for adding:
		if ($_SESSION['lang'] == 'es') {
			$movementType = "SELECT movementTypeid, movementNamees FROM productmovementtypes WHERE type = 1 AND movementTypeid < 17 ORDER BY movementNamees ASC";
		try
		{
			$result = $pdo3->prepare("$movementType");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		while ($user = $result->fetch()) {
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
		try
		{
			$result = $pdo3->prepare("$movementType");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		while ($user = $result->fetch()) {
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
  <span class="fakelabel"><?php echo $lang['global-quantity']; ?>:</span><input type="number" lang="nb" name="quantity" id="quantity" placeholder="u." class="fourDigit" step="0.01" />
  <br />
  <span id="providerarea" style="display: none;">
  <span class="fakelabel"><?php echo $lang['provider']; ?>:</span>
  <div style='float: right;'>
     <select name='provider' id='provider'>
      <option value='<?php echo $provider; ?>'><?php echo $providerName; ?></option>
<?php
	$PRquery = "SELECT id, name FROM providers WHERE id <> '$provider'";
		try
		{
			$PRresult = $pdo3->prepare("$PRquery");
			$PRresult->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		while ($PRtype = $PRresult->fetch()) {
		$id = $PRtype['id'];
		$name = $PRtype['name'];
		
		echo "<option value='$id'>$name</option>";
		
	}
		
?>
     </select><br />
     <span id="providerBox">
     <span style='display: inline-block; width: 80px;'><?php echo $lang['price']; ?> /g:</span> <input type="number" lang="nb" name="priceg" id="priceg" class="fourDigit" step="0.01" value="<?php echo $purchasePrice; ?>" />€<br />
     <span style='display: inline-block; width: 80px;'><?php echo $lang['price']; ?>:</span> <input type="number" lang="nb" name="price" id="price" class="fourDigit" step="0.01" />€<br />
     <span style='display: inline-block; width: 80px;'><?php echo $lang['paid']; ?>:<br /></span> <input type="number" lang="nb" name="paid" id="paid" class="fourDigit" step="0.01" />€<br />
     </div>
  <br />
     </span>
  <div class="clearfloat"></div>
    <center><textarea name="comment" placeholder="<?php echo $lang['global-comment']; ?>?"></textarea></center>
  <input type='hidden' name='productAdd' value='complete' />
  <input type='hidden' name='purchaseid' value='<?php echo $purchaseid; ?>' />
  <input type='hidden' name='category' value='<?php echo $category; ?>' />
  <button class='oneClick' name='oneClick' type="submit" id="addButton"><?php echo $lang['global-add']; ?></button>
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
		try
		{
			$result = $pdo3->prepare("$movementType");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		while ($user = $result->fetch()) {
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
		try
		{
			$result = $pdo3->prepare("$movementType");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		while ($user = $result->fetch()) {
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
  <span class="fakelabel">Quantity:</span><input type="number" lang="nb" name="quantity" id="quantity" placeholder="u." class="fourDigit" step="0.01" />
  <br />
  <center><textarea name="comment" placeholder="<?php echo $lang['global-comment']; ?>?"></textarea></center>
 <input type='hidden' name='productRemove' value='complete' />
  <input type='hidden' name='purchaseid' value='<?php echo $purchaseid; ?>' />
  <input type='hidden' name='category' value='<?php echo $category; ?>' />
  <button class='oneClick' name='oneClick' type="submit"><?php echo $lang['addremove-remove']; ?></button>
 </form>
</div>

<?php } displayFooter(); ?>
