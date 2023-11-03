<?php
	
	require_once 'cOnfig/connection.php';
	require_once 'cOnfig/view.php';
	require_once 'cOnfig/authenticate.php';
	require_once 'cOnfig/languages/common.php';
	
	session_start();
	$accessLevel = '3';
	
	// Authenticate & authorize
	authorizeUser($accessLevel);
	// KONSTANT CODE UPDATE BEGIN
	if(isset($_GET['internal'])){
		$stashhMovementId = 5;
		$removestashhMovementId = 12;
		$stashMovementType = 1;
	}else if(isset($_GET['external'])){
		$stashhMovementId = 6;
		$removestashhMovementId = 2;
		$stashMovementType = 2;
	}

	// KONSTANT CODE UPDATE END
	// Add to stash
	if ($_POST['productAdd'] == 'complete') {
		
		$movementTypeid = $_POST['movementTypeid'];
		$purchaseid = $_POST['purchaseid'];
		$quantity = $_POST['quantity'];
		$realweight = $_POST['realweight'];
		$provider = $_POST['provider'];
		$price = $_POST['price'];
		$priceg = $_POST['priceg'];
		$paid = $_POST['paid'];
		$comment = str_replace("'","\'",str_replace('%', '&#37;', trim($_POST['comment'])));
		$movementtime = date('Y-m-d H:i:s');
		
		if ($realweight == '' || $realweight == 0) {
			$realweight = $quantity;
		}
		
		
			// Query to add new purchase movement - 6 arguments
			  $query = sprintf("INSERT INTO productmovements (movementtime, type, purchaseid, quantity, movementTypeid, comment, provider, price, paid, realquantity, priceg, stashMovementType) VALUES ('%s', '%d', '%d', '%f', '%d', '%s', '%d', '%f', '%f', '%f', '%f', %d);",
			  $movementtime, '1', $purchaseid, $quantity, $movementTypeid, $comment, $provider, $price, $paid, $realweight, $priceg, $stashMovementType);   
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
			// KONSTANT CODE UPDATE BEGIN

				// Query to add reloads in internal/external stash
				 $query = sprintf("INSERT INTO productmovements (movementtime, type, purchaseid, quantity, movementTypeid, comment, stashMovementType) VALUES ('%s', '%d', '%d', '%f', '%d', '%s', %d);",
				  $movementtime, '2', $purchaseid, $quantity, $stashhMovementId, $comment, $stashMovementType);
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
			// KONSTANT CODE UPDATE END
			// On success: redirect.
			$_SESSION['successMessage'] = $quantity . " " . $lang['addremove-gramsadded'];
			header("Location: purchase.php?purchaseid=" . $purchaseid);
			exit();
		
		
	}
	
	// Remove from stash
	if ($_POST['productRemove'] == 'complete') {
		
		$movementTypeid = $_POST['movementTypeid'];
		$purchaseid = $_POST['purchaseid'];
		$quantity = $_POST['quantity'];
		$comment = str_replace("'","\'",str_replace('%', '&#37;', trim($_POST['comment'])));
		$movementtime = date('Y-m-d H:i:s');
		
		$toProduct = $_POST['toProduct'];
		
			  // Query to add new purchase movement - 6 arguments
			 $query = sprintf("INSERT INTO productmovements (movementtime, type, purchaseid, quantity, movementTypeid, comment, stashMovementType) VALUES ('%s', '%d', '%d', '%f', '%d', '%s', '%d');",
			  $movementtime, '2', $purchaseid, $quantity, $movementTypeid, $comment, $stashMovementType);
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
		
			// KONSTANT CODE UPDATE BEGIN

			// Query to add reloads in internal/external stash
			 $query = sprintf("INSERT INTO productmovements (movementtime, type, purchaseid, quantity, movementTypeid, comment, stashMovementType) VALUES ('%s', '%d', '%d', '%f', '%d', '%s', '%d');",
			  $movementtime, '1', $purchaseid, $quantity, $removestashhMovementId, $comment ,$stashMovementType);
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
		// KONSTANT CODE UPDATE END
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
		// Get the warehose 
	if (!isset($_GET['internal']) && !isset($_GET['external'])) {
		handleError($lang['error-warehouse'],"");
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
	          var a = $purchasePrice;
	          var b = $('#quantity').val();
	          var total = a * b;
	          var roundedtotal = total.toFixed(2);
	          $('#price').val(roundedtotal);
	          $('#paid').val(roundedtotal);
	          
	          var c = $('#price').val();
	          var pg = c / b;
	          var pgtot = pg.toFixed(2);
	          
	          $('#priceg').val(pgtot);

	   }

	    function computeTot2() {
	          var a = $('#quantity').val();
	          var b = $('#priceg').val();
	          var total = a * b;
	          var roundedtotal = total.toFixed(2);
	          $('#price').val(roundedtotal);
	          $('#paid').val(roundedtotal);
	          
	   }

	   
$(document).on('click keypress keyup blur', function(event) {
   if (!$(event.target).is("#paid")) {
   	if (!$(event.target).is("#addButton")) {
   	 if (!$(event.target).is("textarea")) {
   	 if (!$(event.target).is("#priceg")) {
   	 if (!$(event.target).is("#provider")) {
	    computeTot();
    }
    }
     }
    }
   }
});

$("#priceg").on('click keypress keyup blur', function(event) {
	    computeTot2();
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

	
	pageStart($lang['title-addorremove'], NULL, $validationScript, "paddremove", "admin dev-align-center", $lang['addremove-addorremove'], $_SESSION['successMessage'], $_SESSION['errorMessage']);

	
?>

<h5><a href='purchase.php?purchaseid=<?php echo $purchaseid; ?>'><?php echo $name . "</a><span class='usergrouptext' style='margin-bottom: 13px; margin-left: 10px;'>$categoryName</span>"; ?></h5><br />
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

?>

<?php 
if(isset($_GET['internal'])){
	$availStash = "Internal Stash (".$inStashInt." g)";
}else if(isset($_GET['external'])){
	$availStash = "External Stash (".$inStashExt." g)";
}
  ?>
  <br>
<div id="productoverview" ><?php echo $availStash; ?></div><br>
<?php	if ($category < 3 || $type == 1) {
 ?>

<div class="actionbox-np2 mainbox-no-width-cls">

 <div class="main_box_title"><span><?php echo $lang['addremove-addtostash']; ?></span></div>
 <div class="boxcontent">
 <form id="registerForm1" action="" method="POST">
  <span class="smallgreen"><?php echo $lang['global-source']; ?>:</span>
   <select class="fakeInput defaultinput" name="movementTypeid" id="addType">
    <option value=""><?php echo $lang['addremove-pleaseselect']; ?>:</option>
<?php
        // KONSTANT CODE UPDATE BEGIN
      	// Query to look up reasons for adding:
		if ($_SESSION['lang'] == 'es') {
			$movementType = "SELECT movementTypeid, movementNamees FROM productmovementtypes WHERE type = 1 AND movementTypeid < 17 AND movementTypeid NOT IN(3,2,12) ORDER BY movementNamees ASC";
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
			
					$user_row = sprintf("<option value='%d'>&nbsp;&nbsp;&nbsp;%s</option>",
		  								 $user['movementTypeid'], $user['movementNamees']);
		  			echo $user_row;
	  			
			}
		} else {
			$movementType = "SELECT movementTypeid, movementNameen FROM productmovementtypes WHERE type = 1 AND movementTypeid < 17 AND movementTypeid NOT IN(3,2,12) ORDER BY movementNameen ASC";
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
					$user_row = sprintf("<option value='%d'>&nbsp;&nbsp;&nbsp;%s</option>",
		  								 $user['movementTypeid'], $user['movementNameen']);
		  			echo $user_row;
			}
		}
      	
      	// KONSTANT CODE UPDATE END
      	

?>
   </select>
  <br />
  <span class="smallgreen"><?php echo $lang['global-quantity']; ?>:</span><input type="number" lang="nb" name="quantity" id="quantity" placeholder="g." class="fourDigit defaultinput" step="0.01" />
  <br />
  <span id="providerarea" style="display: none;">
  <span class="smallgreen"><?php echo $lang['add-realweightshort']; ?>:</span><input type="number" lang="nb" name="realweight" id="realweight" placeholder="g." class="fourDigit defaultinput" step="0.01" />
  <br />
  <span class="smallgreen"><?php echo $lang['provider']; ?>:</span>
  <div>
     <select name='provider' id='provider' class="defaultinput">
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
     <span class="smallgreen" style='display: inline-block; width: 80px;'><?php echo $lang['price']; ?> /g:</span> <input type="number" lang="nb" name="priceg" id="priceg" class="fourDigit defaultinput" step="0.01" /><?php echo $_SESSION['currencyoperator'] ?><br />
     <span class="smallgreen" style='display: inline-block; width: 80px;'><?php echo $lang['price']; ?> total:</span> <input type="number" lang="nb" name="price" id="price" class="fourDigit defaultinput" step="0.01" /><?php echo $_SESSION['currencyoperator'] ?><br />
     <span class="smallgreen" style='display: inline-block; width: 80px;'><?php echo $lang['paid']; ?>:<br /></span> <input type="number" lang="nb" name="paid" id="paid" class="fourDigit defaultinput" step="0.01" /><?php echo $_SESSION['currencyoperator'] ?><br />
     </div>
  <br />
     </span>
  <div class="clearfloat"></div>
  
  <center><textarea class="defaultinput" name="comment" placeholder="<?php echo $lang['global-comment']; ?>?"></textarea></center>
  <input type='hidden' name='productAdd' value='complete' />
  <input type='hidden' name='purchaseid' value='<?php echo $purchaseid; ?>' />
  <button class='oneClick cta4' name='oneClick' type="submit" id="addButton"><?php echo $lang['global-add']; ?></button>
 </form>
</div>
</div>

<div class="actionbox-np2 mainbox-no-width-cls">
<div class="main_box_title"><?php echo $lang['addremove-removefromstash']; ?></div>
<div class="boxcontent">
 <form id="registerForm2" action="" method="POST">
  <span class="smallgreen"><?php echo $lang['addremove-destination']; ?>:</span>
   <select class="fakeInput defaultinput" name="movementTypeid">
    <option value=""><?php echo $lang['addremove-pleaseselect']; ?>:</option>
<?php
		// KONSTANT CODE UPDATE BEGIN
      	// Query to look up reasons for removing:
		if ($_SESSION['lang'] == 'es') {
				$movementType = "SELECT movementTypeid, movementNamees FROM productmovementtypes WHERE type = 2 AND movementTypeid < 17  AND movementTypeid != 9 AND movementTypeid NOT IN(5,6)  ORDER BY movementNamees ASC";
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
			$movementType = "SELECT movementTypeid, movementNameen FROM productmovementtypes WHERE type = 2 AND movementTypeid < 17 AND movementTypeid != 9 AND movementTypeid NOT IN(5,6)  ORDER BY movementNameen ASC";
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
		// KONSTANT CODE UPDATE END
?>
   </select>
  <br />
  <span class="smallgreen"><?php echo $lang['global-quantity']; ?>:</span><input type="number" lang="nb" name="quantity" id="quantity" placeholder="g." class="fourDigit defaultinput" step="0.01" />
  <br />

  
  <center><textarea class="defaultinput" name="comment" placeholder="<?php echo $lang['global-comment']; ?>?"></textarea><br /></center>
  

   <span id='otherProductHolder' style='display: none;'><span class="smallgreen">&nbsp;</span>
   <select class="fakeInput defaultinput" name="toProduct" >
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


 <input type='hidden' name='productRemove' value='complete' />
  <input type='hidden' name='purchaseid' value='<?php echo $purchaseid; ?>' />
  <button class='oneClick cta4' name='oneClick' type="submit"><?php echo $lang['addremove-remove']; ?></button>
 </form>
</div>
</div>
<?php } else { ?>

<div class="actionbox-np2 mainbox-no-width-cls">

 <div class="main_box_title"><span><?php echo $lang['addremove-addtostash']; ?></span></div>
 <div class="boxcontent">
 <form id="registerForm1" action="" method="POST">
  <span class="smallgreen"><?php echo $lang['global-source']; ?>:</span>
   <select class="fakeInput defaultinput" name="movementTypeid" id="addType">
    <option value=""><?php echo $lang['addremove-pleaseselect']; ?>:</option>
<?php
      	// Query to look up reasons for adding:
		if ($_SESSION['lang'] == 'es') {
				$movementType = "SELECT movementTypeid, movementNamees FROM productmovementtypes WHERE type = 1 AND movementTypeid < 17 AND movementTypeid NOT IN(3,2,12) ORDER BY movementNamees ASC";
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
				$movementType = "SELECT movementTypeid, movementNameen FROM productmovementtypes WHERE type = 1 AND movementTypeid < 17 AND movementTypeid NOT IN(3,2,12) ORDER BY movementNameen ASC";
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
  <span class="smallgreen"><?php echo $lang['global-quantity']; ?>:</span><input type="number" lang="nb" name="quantity" id="quantity" placeholder="u." class="fourDigit defaultinput" step="0.01" />
  <br />
  <span id="providerarea" style="display: none;">
  <span class="smallgreen"><?php echo $lang['provider']; ?>:</span>
  <div>
     <select name='provider' id='provider' class="defaultinput">
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
     <span class="smallgreen" style='display: inline-block; width: 80px;'><?php echo $lang['price']; ?> /u:</span> <input type="number" lang="nb" name="priceg" id="priceg" class="fourDigit defaultinput" step="0.01" /><?php echo $_SESSION['currencyoperator'] ?><br />
     <span class="smallgreen" style='display: inline-block; width: 80px;'><?php echo $lang['price']; ?>:</span> <input type="number" lang="nb" name="price" id="price" class="fourDigit defaultinput" step="0.01" /><?php echo $_SESSION['currencyoperator'] ?><br />
     <span class="smallgreen" style='display: inline-block; width: 80px;'><?php echo $lang['paid']; ?>:<br /></span> <input type="number" lang="nb" name="paid" id="paid" class="fourDigit defaultinput" step="0.01" /><?php echo $_SESSION['currencyoperator'] ?><br />
     </div>
  <br />
     </span>
  <div class="clearfloat"></div>
    <center><textarea class="defaultinput" name="comment" placeholder="<?php echo $lang['global-comment']; ?>?"></textarea></center>
  <input type='hidden' name='productAdd' value='complete' />
  <input type='hidden' name='purchaseid' value='<?php echo $purchaseid; ?>' />
  <button class='oneClick cta4' name='oneClick' type="submit" id="addButton"><?php echo $lang['global-add']; ?></button>
 </form>
</div>
</div>

<div class="actionbox-np2 mainbox-no-width-cls">
 <div class="main_box_title"><?php echo $lang['addremove-removefromstash']; ?></div>
 <div class="boxcontent">
 <form id="registerForm2" action="" method="POST">
  <span class="smallgreen"><?php echo $lang['addremove-destination']; ?>:</span>
   <select class="fakeInput defaultinput" name="movementTypeid">
    <option value=""><?php echo $lang['addremove-pleaseselect']; ?>:</option>
<?php
      	// Query to look up reasons for removing:
		if ($_SESSION['lang'] == 'es') {
				$movementType = "SELECT movementTypeid, movementNamees FROM productmovementtypes WHERE type = 2 AND movementTypeid < 17 AND movementTypeid != 9  ORDER BY movementNamees ASC";
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
				$movementType = "SELECT movementTypeid, movementNameen FROM productmovementtypes WHERE type = 2 AND movementTypeid < 17 AND movementTypeid != 9 ORDER BY movementNameen ASC";
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
  <span class="smallgreen">Quantity:</span><input type="number" lang="nb" name="quantity" id="quantity" placeholder="u." class="fourDigit defaultinput" step="0.01" />
  <br />
    <br />

   
        <?php echo $lang['add-to-other-product']; ?>? <input type="checkbox" name="otherProduct" id="otherProduct" style="width: 12px;" value="1" /><br />

   <span id='otherProductHolder' style='display: none;'><span class="smallgreen">&nbsp;</span>
   <select class="fakeInput defaultinput" name="toProduct" >
    <option value=""><?php echo $lang['addremove-pleaseselect']; ?>:</option>
<?php

	// Look up other gram-based categories
	$selectCats = "SELECT id, name from categories WHERE id > 2 AND type = 0";
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

  <center><textarea class="defaultinput" name="comment" placeholder="<?php echo $lang['global-comment']; ?>?"></textarea></center>
 <input type='hidden' name='productRemove' value='complete' />
  <input type='hidden' name='purchaseid' value='<?php echo $purchaseid; ?>' />
  <button class='oneClick cta4' name='oneClick' type="submit"><?php echo $lang['addremove-remove']; ?></button>
 </form>
</div>
</div>
<?php }  ?>

<?php displayFooter(); ?>
