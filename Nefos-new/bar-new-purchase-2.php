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
		$purchaseDate = date('Y-m-d H:i:s');
		$purchaseQuantity = $_POST['purchaseQuantity'];
		
		$purchasePrice = $_POST['purchaseppg'];
		$salesPrice = $_POST['salesppg'];
		$adminComment = str_replace("'","\'",str_replace('%', '&#37;', trim($_POST['adminComment'])));
		
		$inMenu = $_POST['inMenu'];
		
		$sample = $_POST['sample'];
		$displayjar = $_POST['displayjar'];
		$intstash = $_POST['intstash'];
		$extstash = $_POST['extstash'];
		$growtype = $_POST['growtype'];
		$provider = $_POST['provider'];
		$paidNow = $_POST['paidNow'];
		$barCode = $_POST['barCode'];
		
		
		// Query to add new purchase - 11 arguments
		  $query = sprintf("INSERT INTO b_purchases (category, productid, purchaseDate, purchasePrice, salesPrice, purchaseQuantity, adminComment, inMenu, provider, paid, barCode) VALUES ('%d', '%d', '%s', '%f', '%f', '%f', '%s', '%d', '%d', '%f', '%s');",
		  $category, $productID, $purchaseDate, $purchasePrice, $salesPrice, $purchaseQuantity, $adminComment, $inMenu, $provider, $paidNow, $barCode);
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
	
			
		$purchaseid = $pdo3->lastInsertId();
	
		$_SESSION['purchaseid'] = $purchaseid;
		$_SESSION['productID'] = $productID;
		
				
		if ($sample > 0) {
			  $query = sprintf("INSERT INTO b_productmovements (movementtime, type, purchaseid, quantity, movementTypeid, doneAtRegistration) VALUES ('%s', '%d', '%d', '%f', '%d', '%d');",
			  $purchaseDate, '2', $purchaseid, $sample, '8', '1');
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
		if ($displayjar > 0) {
			  $query = sprintf("INSERT INTO b_productmovements (movementtime, type, purchaseid, quantity, movementTypeid, doneAtRegistration) VALUES ('%s', '%d', '%d', '%f', '%d', '%d');",
			  $purchaseDate, '2', $purchaseid, $displayjar, '9', '1');
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
		if ($intstash > 0) {
			  $query = sprintf("INSERT INTO b_productmovements (movementtime, type, purchaseid, quantity, movementTypeid, doneAtRegistration) VALUES ('%s', '%d', '%d', '%f', '%d', '%d');",
			  $purchaseDate, '2', $purchaseid, $intstash, '5', '1');
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
		if ($extstash > 0) {
			  $query = sprintf("INSERT INTO b_productmovements (movementtime, type, purchaseid, quantity, movementTypeid, doneAtRegistration) VALUES ('%s', '%d', '%d', '%f', '%d', '%d');",
			  $purchaseDate, '2', $purchaseid, $extstash, '6', '1');
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
		$_SESSION['successMessage'] = $lang['add-purchaseadded'];
		header("Location: bar-purchases.php");
		exit();
	}
	/***** FORM SUBMIT END *****/
	
	// Did this page load with a pre-selected product?
	if (!$_POST['prePurchase']) {
		echo $lang['error-noprodselected'];
		exit();
	}
	
	if ($_POST['prePurchase']) {
		
		$productID = $_POST['prePurchase'];
		$category = $_POST['category'];
		$categoryName = $_POST['categoryName'];
		
			
			// Query to look up product
			$selectProduct = "SELECT productid, name FROM b_products WHERE productid = {$productID}";
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
				$name = $row['name'];
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

	pageStart($lang['newpurchase'], NULL, $validationScript, "ppurchase", "admin", $lang['newpurchasecaps'], $_SESSION['successMessage'], $_SESSION['errorMessage']);
	
	
?>

<form id="registerForm" action="" method="POST">
    <input type="hidden" name="category" value="<?php echo $category; ?>" />
    <input type="hidden" name="productID" value="<?php echo $productID; ?>" />
<div id='productoverview'>
 <table>
  <tr>
   <td><?php echo $lang['global-category']; ?>:</td>
   <td class='yellow fat'><?php echo $categoryName; ?></td>
  </tr>
  <tr>
   <td><?php echo $lang['global-product']; ?>:</td>
   <td class='yellow fat'><?php echo $name; ?></td>
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

        $('#purchaseQuantity').on('click keypress keyup blur', compute2);
        $('#purchaseQuantity').on('click keypress keyup blur', compute4);
        $('#purchaseppg').on('click keypress keyup blur', compute2);
        $('#salesppg').on('click keypress keyup blur', compute4);
        $('#purchasePrice').on('click keypress keyup blur', compute);
        $('#salesPrice').on('click keypress keyup blur', compute3);

  }); // end ready
   </script>
   
<div class='leftblock'>
 <div class='infobox'>
  <h3><?php echo $lang['global-details']; ?></h3>
  <table>
   <tr>
    <td class="biggerFont"><?php echo $lang['add-amountpurchased']; ?>:</td>
    <td><input type="number" lang="nb" class="fourDigit" id="purchaseQuantity" name="purchaseQuantity" /></td>
   </tr>
   <tr>
    <td class="biggerFont"><?php echo $lang['add-showinmenu']; ?>?</td>
    <td><input type="checkbox" name="inMenu" id="inMenu" style="width: 12px;" value="1" /></td>
   </tr>
   <tr>
    <td class="biggerFont left"><?php echo $lang['provider']; ?>:</td>
    <td class='left'>
     <select name='provider' style="width: 120px;">
      <option value=''><?php echo $lang['global-choose']; ?></option>
<?php
	$PRquery = "SELECT id, name FROM b_providers";
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
    <td><input type='number' lang='nb' class='fourDigit' id='purchaseppg' name='purchaseppg' /> &euro;</td>
   </tr>
   <tr>
    <td><?php echo $lang['add-total']; ?></td>
    <td><input type="number" lang="nb" id="purchasePrice" name="purchasePrice" class="fourDigit" /> &euro;</td>
   </tr>
   <tr>
    <td class="left"><?php echo $lang['paid']; ?>:</td>
    <td><input type="number" lang="nb" id="paidNow" name="paidNow" class="fourDigit" /> &euro;</td>
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
    <td><input type='number' lang='nb' class='fourDigit' id='salesppg' name='salesppg' /> &euro;</td>
   </tr>
   <tr>
    <td><?php echo $lang['add-total']; ?></td>
    <td><input type="number" lang="nb" class="fourDigit" id="salesPrice" name="salesPrice" /> &euro;</td>
   </tr>
  </table>
   </div>

  </div>
  <br />
  <div class='infobox fullwidth3'>
  <h3 class="smallerFont"><?php echo $lang['add-movements']; ?></h3>
  <table>
   <tr style='display: none;'>
    <td><?php echo $lang['add-sampletaste']; ?></td>
    <td><input type='number' lang='nb' class='fourDigit' name='sample' /> u</td>
   </tr>
   <tr>
    <td>To Warehouse</td>
    <td><input type='number' lang='nb' class='fourDigit' name='intstash' /> u</td>
   </tr>
   <tr>
    <td>To Christian</td>
    <td><input type='number' lang='nb' class='fourDigit' name='displayjar' /> u</td>
   </tr>
   <tr>
    <td>To Andy</td>
    <td><input type='number' lang='nb' class='fourDigit' name='extstash' /> u</td>
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

   
</div> <!-- end leftblock -->
 
 <button class='oneClick' name='oneClick' type="submit"><?php echo $lang['global-savechanges']; ?></button>
</form>
	
	
	
<?php displayFooter();
