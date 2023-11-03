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
		header("Location: bar-open-purchases.php");
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
<h5><?php echo $name . " <span class='usergrouptext' style='margin-bottom: 13px; margin-left: 10px;'>$categoryName</span>"; ?></h5>
<form id="registerForm" action="" method="POST">
    <input type="hidden" name="category" value="<?php echo $category; ?>" />
    <input type="hidden" name="productID" value="<?php echo $productID; ?>" />
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
   
<center>
	 <div class='actionbox-np2'>
		  <div class="mainboxheader"><?php echo $lang['global-details']; ?></div>
		  <div class="boxcontent">
			  <table class="np-table">
				   <tr>
				    <td class="biggerFont"><?php echo $lang['add-amountpurchased']; ?>
				    <input type="number" lang="nb" class="fourDigit defaultinput-no-margin-smallborder floatright" id="purchaseQuantity" name="purchaseQuantity" placeholder="u"/></td>
				   </tr>
				   <tr>
				    <td class="biggerFont left">
				    	 <span><?php echo $lang['add-showinmenu']; ?>?</span>
						 	<div class="fakeboxholder">	
						 	 
							 <label class="control">
							  &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
							  <input type="checkbox" name="inMenu" id="inMenu" value="1" >
							  <div class="fakebox" style="top: 5px;margin-left: 30px;"></div>
							 </label>
							</div>
				   </tr>
				   <tr>
				    <td class="biggerFont left"><?php echo $lang['provider']; ?>
				   
				     <select name='provider' class="defaultinput-no-margin-smallborder floatright" style="width: 120px;">
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
	 </div>
	   
	
	 <div class="actionbox-np2">
		 <div class="mainboxheader"><?php echo $lang['add-purchaseprice']; ?></div>
		 <div class='boxcontent'>
		  <table class="np-table">
		   <tr>
		    <td class="left"><?php echo $lang['add-perunit']; ?>
		    <input type='number' lang='nb' class='fourDigit defaultinput-no-margin-smallborder floatright' id='purchaseppg' name='purchaseppg' placeholder="<?php echo $_SESSION['currencyoperator'] ?>" /></td>
		   </tr>
		   <tr>
		    <td class="left"><?php echo $lang['add-total']; ?>
		    <input type="number" lang="nb" id="purchasePrice" name="purchasePrice" class="fourDigit defaultinput-no-margin-smallborder floatright" placeholder="<?php echo $_SESSION['currencyoperator'] ?>" /></td>
		   </tr>
		   <tr>
		    <td class="left"><?php echo $lang['paid']; ?>
		  	 <input type="number" lang="nb" id="paidNow" name="paidNow" class="fourDigit defaultinput-no-margin-smallborder floatright" placeholder="<?php echo $_SESSION['currencyoperator'] ?>" /></td>
		   </tr>
		  </table>
		 </div>
		 
	 </div>
	 
	 <div class="actionbox-np2" style='height: 206px;'>

		 <div class="mainboxheader"><?php echo $lang['add-dispenseprice']; ?></div>
		 <div class='boxcontent'>
			  <table class="np-table">
			   <tr>
			    <td class="left"><?php echo $lang['add-perunit']; ?>
			    <input type='number' lang='nb' class='fourDigit defaultinput-no-margin-smallborder floatright' id='salesppg' name='salesppg' placeholder="<?php echo $_SESSION['currencyoperator'] ?>" /> </td>
			   </tr>
			   <tr>
			    <td class="left"><?php echo $lang['add-total']; ?>
			    <input type="number" lang="nb" class="fourDigit defaultinput-no-margin-smallborder floatright" id="salesPrice" name="salesPrice" placeholder="<?php echo $_SESSION['currencyoperator'] ?>" /> </td>
			   </tr>
			  </table>
		   </div>

	 </div>
	 
	  <div class='actionbox-np2' style="height: 268px;">
		  <div class="mainboxheader"><?php echo $lang['add-movements']; ?></div>
			  <div class="boxcontent">
				  <table class="np-table">
				   <tr>
					    <td class="left"><?php echo $lang['add-sampletaste']; ?>
					    	<input type='number' lang='nb' class='fourDigit defaultinput-no-margin-smallborder floatright' name='sample' placeholder="u" />
						</td>
					   
					    <td class="left"><?php echo $lang['add-displayjar']; ?>
					    	<input type='number' lang='nb' class='fourDigit defaultinput-no-margin-smallborder floatright' name='displayjar' placeholder="u" />
					    </td>
				   </tr>
				   <tr>
					    <td class="left"><?php echo $lang['add-stashedint']; ?>
					    	<input type='number' lang='nb' class='fourDigit defaultinput-no-margin-smallborder floatright' name='intstash' placeholder="u" />
						</td>
					  
					    <td class="left"><?php echo $lang['add-stashedext']; ?>
					    	<input type='number' lang='nb' class='fourDigit defaultinput-no-margin-smallborder floatright' name='extstash' placeholder="u" />
					    </td>
				   </tr>
				  </table>
			</div>
	   </div>
	 <br>

	   <div class='actionbox-np2' style="height: 206px;">
		  <div class="mainboxheader"><?php echo $lang['global-comment']; ?></div>
		  <div class="boxcontent">
			<textarea name="adminComment" placeholder="<?php echo $lang['global-comment']; ?>"><?php echo $adminComment; ?></textarea><br /><br />
		   </div>
	   </div>
	   <div class='actionbox-np2' style="height: 206px;">
	 	 <div class="mainboxheader"><?php echo $lang['barcode']; ?></div>
		 	 <div class="boxcontent">
				<input type='text' lang='nb' class='eightDigit defaultinput-no-margin-smallborder floatright' name='barCode' value='<?php echo $barCode; ?>' /><br /><br />
			</div>
	   </div>
	   <center>
	    	<button class='oneClick cta1nm' name='oneClick' type="submit" style="border: 0;"><?php echo $lang['global-savechanges']; ?></button>
   		</center>
</center> <!-- end leftblock -->
 

</form>
	
	
	
<?php displayFooter();
