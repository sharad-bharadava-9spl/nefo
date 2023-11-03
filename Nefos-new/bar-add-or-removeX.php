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
		$provider = $_POST['provider'];
		$price = $_POST['price'];
		$paid = $_POST['paid'];
		$quantity = $_POST['quantity'];
		$comment = str_replace("'","\'",str_replace('%', '&#37;', trim($_POST['comment'])));
		$movementtime = date('Y-m-d H:i:s');
		
		// Query to add new purchase movement - 6 arguments
		  $query = sprintf("INSERT INTO b_productmovements (movementtime, type, purchaseid, quantity, movementTypeid, comment, provider, price, paid) VALUES ('%s', '%d', '%d', '%f', '%d', '%s', '%d', '%f', '%f');",
		  $movementtime, '1', $purchaseid, $quantity, $movementTypeid, $comment, $provider, $price, $paid);
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
		header("Location: bar-purchase.php?purchaseid=" . $purchaseid);
		exit();
	}
	
	// Remove from jar
	if ($_POST['productRemove'] == 'complete') {
		
		$movementTypeid = $_POST['movementTypeid'];
		$purchaseid = $_POST['purchaseid'];
		$quantity = $_POST['quantity'];
		$comment = str_replace("'","\'",str_replace('%', '&#37;', trim($_POST['comment'])));
		$movementtime = date('Y-m-d H:i:s');
		
		// Query to add new purchase movement - 6 arguments
		  $query = sprintf("INSERT INTO b_productmovements (movementtime, type, purchaseid, quantity, movementTypeid, comment) VALUES ('%s', '%d', '%d', '%f', '%d', '%s');",
		  $movementtime, '2', $purchaseid, $quantity, $movementTypeid, $comment);
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
		$_SESSION['successMessage'] = $quantity . " " . $lang['addremove-gramsremoved'];
		header("Location: bar-purchase.php?purchaseid=" . $purchaseid);
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
	$purchaseDetails = "SELECT category, productid, salesPrice, purchasePrice FROM b_purchases WHERE purchaseid = $purchaseid";
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

	// Calculate what's in internal stash
	$selectStashedInt = "SELECT SUM(m.quantity) FROM b_productmovements m, b_purchases p WHERE p.category = $category AND m.type = 2 AND (m.movementTypeid = 5 OR movementTypeid = 18) AND m.purchaseid = p.purchaseid AND (p.closedAt IS NULL OR DATE(p.closedAt) = DATE(NOW())) AND (p.purchaseid = $purchaseid)";
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
					
	$selectUnStashedInt = "SELECT SUM(m.quantity) FROM b_productmovements m, b_purchases p WHERE p.category = $category AND m.type = 1 AND (m.movementTypeid = 12 OR movementTypeid = 17) AND m.purchaseid = p.purchaseid AND (p.closedAt IS NULL OR DATE(p.closedAt) = DATE(NOW())) AND (p.purchaseid = $purchaseid)";
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
	$selectStashedExt = "SELECT SUM(m.quantity) FROM b_productmovements m, b_purchases p WHERE p.category = $category AND m.type = 2 AND (m.movementTypeid = 6 OR movementTypeid = 20) AND m.purchaseid = p.purchaseid AND (p.closedAt IS NULL OR DATE(p.closedAt) = DATE(NOW())) AND (p.purchaseid = $purchaseid)";
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
					
	$selectUnStashedExt = "SELECT SUM(m.quantity) FROM b_productmovements m, b_purchases p WHERE p.category = $category AND m.type = 1 AND (m.movementTypeid = 2 OR movementTypeid = 19) AND m.purchaseid = p.purchaseid AND (p.closedAt IS NULL OR DATE(p.closedAt) = DATE(NOW())) AND (p.purchaseid = $purchaseid)";
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
			

	$validationScript = <<<EOD
    $(document).ready(function() {
	    
	    function computeTot() {
	          var a = $purchasePrice;
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
	$PRquery = "SELECT id, name FROM b_providers WHERE id <> '$provider'";
		try
		{
			$result = $pdo3->prepare("$PRquery");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		while ($PRtype = $result->fetch()) {
		$id = $PRtype['id'];
		$name = $PRtype['name'];
		
		echo "<option value='$id'>$name</option>";
		
	}
		
?>
     </select><br />
     <span id="providerBox">
     <span style='display: inline-block; width: 80px;'><?php echo $lang['price']; ?>:</span> <input type="number" lang="nb" name="price" id="price" class="fourDigit" step="0.01" readonly />€<br />
     <span style='display: inline-block; width: 80px;'><?php echo $lang['paid']; ?>:<br /></span> <input type="number" lang="nb" name="paid" id="paid" class="fourDigit" step="0.01" />€<br />
     </div>
  <br />
     </span>
  <div class="clearfloat"></div>
  

<center><textarea name="comment" placeholder="<?php echo $lang['global-comment']; ?>?" ></textarea></center>
  <input type='hidden' name='productAdd' value='complete' />
  <input type='hidden' name='purchaseid' value='<?php echo $purchaseid; ?>' />
  <button class='oneClick' name='oneClick' id="addButton" type="submit"><?php echo $lang['global-add']; ?></button>
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
			$movementType = "SELECT movementTypeid, movementNamees FROM productmovementtypes WHERE type = 2 AND (movementTypeid < 16 OR movementTypeid > 26) AND movementTypeid <> 13 AND movementTypeid <> 14 AND movementTypeid <> 15 AND movementTypeid <> 7 ORDER BY movementNamees ASC";
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
			$movementType = "SELECT movementTypeid, movementNameen FROM productmovementtypes WHERE type = 2 AND (movementTypeid < 16 OR movementTypeid > 26) AND movementTypeid <> 13 AND movementTypeid <> 14 AND movementTypeid <> 15 AND movementTypeid <> 7 ORDER BY movementNameen ASC";
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
  <span class="fakelabel">Quantity:</span><input type="number" lang="nb" name="quantity" placeholder="u." class="fourDigit" step="0.01" />
  <br />
  <span class="fakelabel" style="vertical-align: top;"><?php echo $lang['global-comment']; ?>:</span><textarea name="comment" placeholder="<?php echo $lang['global-comment']; ?>?"></textarea>
 <input type='hidden' name='productRemove' value='complete' />
  <input type='hidden' name='purchaseid' value='<?php echo $purchaseid; ?>' />
  <button class='oneClick' name='oneClick' type="submit"><?php echo $lang['addremove-remove']; ?></button>
 </form>
</div>

<?php displayFooter(); ?>
