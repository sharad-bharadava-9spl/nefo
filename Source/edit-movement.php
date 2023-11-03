<?php
	
	require_once 'cOnfig/connection.php';
	require_once 'cOnfig/view.php';
	require_once 'cOnfig/authenticate.php';
	require_once 'cOnfig/languages/common.php';
	
	session_start();
	$accessLevel = '3';
	
	// Authenticate & authorize
	authorizeUser($accessLevel);
	
	// Add to jar
	if ($_POST['productAdd'] == 'complete') {
		
		$provider = $_POST['provider'];
		$quantity = $_POST['quantity'];
		$price = $_POST['price'];
		$paid = $_POST['paid'];
		$comment = str_replace('%', '&#37;', $_POST['comment']);
		$movementid = $_POST['movementid'];
		$purchaseid = $_POST['purchaseid'];
		
		// Query to add new purchase movement - 6 arguments
		  $query = sprintf("UPDATE productmovements SET provider = '%d', quantity = '%f', price = '%f', paid = '%f', comment = '%s' WHERE movementid = '%d'",
		  $provider, $quantity, $price, $paid, $comment, $movementid);
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
		$_SESSION['successMessage'] = $lang['movement-updated'];
		header("Location: purchase.php?purchaseid=" . $purchaseid);
		exit();
	}

	/***** FORM SUBMIT END *****/
	
	
	
	// Get the purchase ID
	
	
	
	
	if (isset($_GET['movementid'])) {
		$movementid = $_GET['movementid'];
	} else {
		handleError($lang['error-nopurchaseid'],"");
	}
	
	// Query to look for purchase
	$purchaseDetails = "SELECT quantity, purchaseid, comment, provider, price, paid FROM productmovements WHERE movementid = $movementid";
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
		$quantity = $row['quantity'];
		$purchaseid = $row['purchaseid'];
		$comment = $row['comment'];
		$provider = $row['provider'];
		$price = $row['price'];
		$paid = $row['paid'];
		$ppg = round($price / $quantity,2);
		
	// Check if Category is grams or units
	$purchaseDetails = "SELECT category, purchasePrice FROM purchases WHERE purchaseid = $purchaseid";
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
		$purchasePrice = $row['purchasePrice'];
		
		$pprice = $purchasePrice * $quantity;


	$validationScript = <<<EOD
    $(document).ready(function() {

	    function computeTot() {
	          var a = $purchasePrice;
	          var b = $('#quantity').val();
	          var c = $('#priceg').val();
	          var total = b * c;
	          var roundedtotal = total.toFixed(2);
	          $('#price').val(roundedtotal);
	   }


$(document).on('keypress keyup blur', function(event) {
	    computeTot();
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

	
	pageStart("Editar recarga", NULL, $validationScript, "paddremove", "admin", "Editar recarga", $_SESSION['successMessage'], $_SESSION['errorMessage']);

	
?>


<br />

<?php 	

		
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

	$selectCats = "SELECT name from providers WHERE id = '$provider'";
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
		$providerName = $row['name'];


	
	if ($category < 3 || $type == 1) {
 ?>

<div class="actionbox">
 <h2><img src="images/add-to-jar.png" class="midalign" />&nbsp;&nbsp;&nbsp;<span>Editar recarga</span></h2>

 <form id="registerForm1" action="" method="POST">
  <br />
  <span class="fakelabel"><?php echo $lang['provider']; ?>:</span>
     <select name='provider' id='provider'>
      <option value='<?php echo $provider; ?>'><?php echo $providerName; ?></option>
<?php
	$PRquery = "SELECT id, name FROM providers WHERE id <> '$provider'";
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
     </select><br />
  <span class="fakelabel"><?php echo $lang['global-quantity']; ?>:</span><input type="number" lang="nb" name="quantity" id="quantity" value="<?php echo $quantity; ?>" class="fourDigit" step="0.01" /> g.<br />
     <span class='fakelabel'><?php echo $lang['price']; ?> / g.:</span><input type="number" lang="nb" name="priceg" id="priceg" class="fourDigit" step="0.01" value="<?php echo $ppg; ?>" />&euro;<br />
     <span class='fakelabel'><?php echo $lang['price']; ?> total:</span><input type="number" lang="nb" name="price" id="price" class="fourDigit" step="0.01" value="<?php echo $price; ?>" readonly />&euro;<br />
     <span class='fakelabel'><?php echo $lang['paid']; ?>:<br /></span><input type="number" lang="nb" name="paid" id="paid" class="fourDigit" step="0.01" value="<?php echo $paid; ?>" />&euro;<br />
  <br />
  <div class="clearfloat"></div>
  
  <span class="fakelabel" style="vertical-align: top;"><?php echo $lang['global-comment']; ?>:</span><textarea name="comment" placeholder="<?php echo $lang['global-comment']; ?>?"><?php echo $comment; ?></textarea>
  <input type='hidden' name='productAdd' value='complete' />
  <input type='hidden' name='purchaseid' value='<?php echo $purchaseid; ?>' />
  <input type='hidden' name='movementid' value='<?php echo $movementid; ?>' />
  <button class='oneClick' name='oneClick' type="submit"><?php echo $lang['global-save']; ?></button>
 </form>
</div>


<?php } else { ?>

<div class="actionbox">
 <h2><img src="images/add-to-jar.png" class="midalign" />&nbsp;&nbsp;&nbsp;<span>Editar recarga</span></h2>

 <form id="registerForm1" action="" method="POST">
  <br />
  <span class="fakelabel"><?php echo $lang['provider']; ?>:</span>
     <select name='provider' id='provider'>
      <option value='<?php echo $provider; ?>'><?php echo $providerName; ?></option>
<?php
	$PRquery = "SELECT id, name FROM providers WHERE id <> '$provider'";
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
     </select><br />
  <span class="fakelabel"><?php echo $lang['global-quantity']; ?>:</span><input type="number" lang="nb" name="quantity" id="quantity" value="<?php echo $quantity; ?>" class="fourDigit" step="0.01" /> g.<br />
     <span class='fakelabel'><?php echo $lang['price']; ?> / u.:</span><input type="number" lang="nb" name="priceg" id="priceg" class="fourDigit" step="0.01" value="<?php echo $ppg; ?>" />&euro;<br />
     <span class='fakelabel'><?php echo $lang['price']; ?> total:</span><input type="number" lang="nb" name="price" id="price" class="fourDigit" step="0.01" value="<?php echo $price; ?>" readonly />&euro;<br />
     <span class='fakelabel'><?php echo $lang['paid']; ?>:<br /></span><input type="number" lang="nb" name="paid" id="paid" class="fourDigit" step="0.01" value="<?php echo $paid; ?>" />&euro;<br />
  <br />
  <div class="clearfloat"></div>
  
  <span class="fakelabel" style="vertical-align: top;"><?php echo $lang['global-comment']; ?>:</span><textarea name="comment" placeholder="<?php echo $lang['global-comment']; ?>?"><?php echo $comment; ?></textarea>
  <input type='hidden' name='productAdd' value='complete' />
  <input type='hidden' name='purchaseid' value='<?php echo $purchaseid; ?>' />
  <input type='hidden' name='movementid' value='<?php echo $movementid; ?>' />
  <button class='oneClick' name='oneClick' type="submit"><?php echo $lang['global-save']; ?></button>
 </form>
</div>


<?php } displayFooter(); ?>
