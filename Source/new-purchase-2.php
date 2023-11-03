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
	
	if ($category < 3) {
		$realQuantity = $_POST['realQuantity'];
	} else {
		$realQuantity = $_POST['purchaseQuantity'];
	}
	$purchasePrice = $_POST['purchaseppg'];
	$salesPrice = $_POST['salesppg'];
	$adminComment = str_replace("'","\'",str_replace('%', '&#37;', trim($_POST['adminComment'])));
	
	$inMenu = $_POST['inMenu'];
	
	$sample = $_POST['sample'];
	$displayjar = $_POST['displayjar'];
	$intstash = $_POST['intstash'];
	$extstash = $_POST['extstash'];
	$growtype = $_POST['growtype'];
	$tupperWeight = $_POST['tupperWeight'];
	$provider = $_POST['provider'];
	$paidNow = $_POST['paidNow'];
	$barCode = $_POST['barCode'];
	
	// Look up provider saldo, then adjust it.
	$userCredit = "SELECT credit FROM providers WHERE id = '$provider'";
	try
	{
		$result = $pdo3->prepare("$userCredit");
		$result->execute();
	}
	catch (PDOException $e)
	{
			$error = 'Error fetching user: ' . $e->getMessage();
			echo $error;
			exit();
	}

	$row = $result->fetch();
		$oldCredit = $row['credit'];

	$totPurchasePrice = $_POST['purchasePrice'];
	$debt = $totPurchasePrice - $paidNow;
	$newCredit = $oldCredit - $debt;
	
	$updateCredit = "UPDATE providers SET credit = $newCredit WHERE id = '$provider'";
		try
		{
			$result = $pdo3->prepare("$updateCredit")->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
	
	// Query to add new purchase - 11 arguments
	  $query = sprintf("INSERT INTO purchases (category, productid, purchaseDate, purchasePrice, salesPrice, purchaseQuantity, realQuantity, adminComment, growType, inMenu, tupperWeight, provider, paid, barCode) VALUES ('%d', '%d', '%s', '%f', '%f', '%f', '%f', '%s', '%d', '%d', '%f', '%d', '%f', '%s');",
	  $category, $productID, $purchaseDate, $purchasePrice, $salesPrice, $purchaseQuantity, $realQuantity, $adminComment, $growtype, $inMenu, $tupperWeight, $provider, $paidNow, $barCode);
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
		  $query = sprintf("INSERT INTO productmovements (movementtime, type, purchaseid, quantity, movementTypeid, doneAtRegistration) VALUES ('%s', '%d', '%d', '%f', '%d', '%d');",
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
		  $query = sprintf("INSERT INTO productmovements (movementtime, type, purchaseid, quantity, movementTypeid, doneAtRegistration) VALUES ('%s', '%d', '%d', '%f', '%d', '%d');",
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
		  $query = sprintf("INSERT INTO productmovements (movementtime, type, purchaseid, quantity, movementTypeid, doneAtRegistration) VALUES ('%s', '%d', '%d', '%f', '%d', '%d');",
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
		  $query = sprintf("INSERT INTO productmovements (movementtime, type, purchaseid, quantity, movementTypeid, doneAtRegistration) VALUES ('%s', '%d', '%d', '%f', '%d', '%d');",
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
		//header("Location: purchase.php?purchaseid=" . $purchaseid);
		header("Location: new-purchase-3.php?purchaseid=$purchaseid&purchase=saved");
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

/*		
		echo "productID: $productID<br />";
		echo "category: $category<br />";
		echo "categoryName: $categoryName<br />";
*/

		if ($category == 1) {
			// Query to look up flowers
			$selectFlower = "SELECT flowerid, breed2, name, flowernumber FROM flower WHERE flowerid = {$productID}";
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
				$categoryName = $lang['global-flowers'];
				$productnumber = $row['flowernumber'];

			
		if ($breed2 != '') {
			$name = $name . " x " . $breed2;
		}

			
		} else if ($category == 2) {
			
			// Query to look up extract
			$selectExtract = "SELECT extractid, extracttype, extract, name, extractnumber FROM extract WHERE extractid = {$productID}";
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
				$categoryName = $lang['global-extracts'];
				$productnumber = $row['extractnumber'];

		} else {
			
			// Query to look up product
			$selectProduct = "SELECT productid, name, productnumber FROM products WHERE productid = {$productID}";
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
				$productnumber = $row['productnumber'];
				
			// Check if Category is grams or units
			$selectCats = "SELECT type, name from categories WHERE id = $category";
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
				$categoryName = $row['name'];

		}
	}
	




	$validationScript = <<<EOD
    $(document).ready(function() {
	    	    
		$("input[type='text']").keyup(function() {
		   	  $(this).val($(this).val().replace(/,/g, '.'));
		   	  $(this).val($(this).val().replace(' ', ''));
		   	  $(this).val($(this).val().replace(/[a-z]/g, ''));
		});
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
	
if ($category < 3 || $type == 1) {
	
?>

<h5><?php echo $name . " <span class='usergrouptext' style='margin-bottom: 13px; margin-left: 10px;'>$categoryName</span>"; ?></h5>

<form id="registerForm" action="" method="POST">
    <input type="hidden" name="category" value="<?php echo $category; ?>" />
    <input type="hidden" name="productID" value="<?php echo $productID; ?>" />
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
			var a = $('#realQuantity').val();
			var b = $('#salesPrice').val();
			var total = b / a;
			var roundedtotal = total.toFixed(2);
			$('#salesppg').val(roundedtotal);
		}
		    
		function compute4() {
			var a = $('#realQuantity').val();
			var b = $('#salesppg').val();
			var total = a * b;
			var roundedtotal = total.toFixed(2);
			$('#salesPrice').val(roundedtotal);
		}

        $('#purchaseQuantity').on('click keypress keyup blur', compute2);
        $('#realQuantity').on('click keypress keyup blur', compute4);
        $('#purchaseppg').on('click keypress keyup blur', compute2);
        $('#salesppg').on('click keypress keyup blur', compute4);
        $('#purchasePrice').on('click keypress keyup blur', compute);
        $('#salesPrice').on('click keypress keyup blur', compute3);

  }); // end ready
</script>
<center>
<div class="actionbox-np2">
 <div class='mainboxheader'>
 <?php echo $lang['global-details']; ?>
 </div>
 <div class='boxcontent'>
  <table class='np-table'>
   <tr>
    <td class="biggerFont"><?php echo $lang['global-amount']; ?> <input type='text' lang="nb" class="fourDigit defaultinput-no-margin-smallborder floatright" id="purchaseQuantity" name="purchaseQuantity" placeholder="gr" /></td>
    <td class="biggerFont"><?php echo $lang['add-realweight']; ?> <input type="text" lang="nb" class="fourDigit defaultinput-no-margin-smallborder floatright" id="realQuantity" name="realQuantity" placeholder="gr" /></td>
   </tr>
   <tr>
<?php if ($category == '1') { ?>
    <td class="biggerFont left"><?php echo $lang['global-growtype']; ?> <select name='growtype' class='defaultinput-no-margin-smallborder floatright'>
      <option value=''><?php echo $lang['global-choose']; ?></option>
<?php
	$GRquery = "SELECT growtypeid, growtype FROM growtypes";
	try
	{
		$GRresult = $pdo3->prepare("$GRquery");
		$GRresult->execute();
	}
	catch (PDOException $e)
	{
			$error = 'Error fetching user: ' . $e->getMessage();
			echo $error;
			exit();
	}
	
	while ($GRtype = $GRresult->fetch()) {
		$growtypeid = $GRtype['growtypeid'];
		$growtype = $GRtype['growtype'];
		
		echo "<option value='$growtypeid'>$growtype</option>";
		
	}
		
?>
     </select>
    </td>
<?php } else { ?>
	<td style='background-color: #f9f9f9;'></td>
<?php }  ?>

    <td><?php echo $lang['provider']; ?> <select name='provider' class='defaultinput-no-margin-smallborder floatright'>
      <option value=''><?php echo $lang['global-choose']; ?></option>
<?php
	$PRquery = "SELECT id, name FROM providers";
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
   <tr>
    <td class="biggerFont left"><?php echo $lang['jar-weight']; ?> <input type='text' lang="nb" class="fourDigit defaultinput-no-margin-smallborder floatright" id="tupperWeight" name="tupperWeight" placeholder="gr" /></td>
    <td class="biggerFont left"><label for='inMenu'><?php echo $lang['add-showinmenu']; ?>?</label> <input type="checkbox" name="inMenu" id="inMenu" style="width: 12px; margin-left: 30px;" value="1" /></td>
   </tr>

  </table>
</div>
</div>
   
<div class="actionbox-np2">
 <div class='mainboxheader'>
 <?php echo $lang['add-purchaseprice']; ?>
 </div>
 <div class='boxcontent'>
  <table class='np-table'>
   <tr>
    <td class="left"><?php echo $lang['add-pergram']; ?><input type='text' lang='nb' class="fourDigit defaultinput-no-margin-smallborder floatright" id='purchaseppg' name='purchaseppg' placeholder="&euro;" /></td>
   </tr>
   <tr>
    <td class="left"><?php echo $lang['add-total']; ?> <input type='text' lang="nb" id="purchasePrice" name="purchasePrice" class="fourDigit defaultinput-no-margin-smallborder floatright" placeholder="&euro;" /></td>
   </tr>
   <tr>
    <td class="left"><?php echo $lang['paid']; ?>: <input type='text' lang="nb" id="paidNow" name="paidNow" class="fourDigit defaultinput-no-margin-smallborder floatright" placeholder="&euro;" /></td>
   </tr>
  </table>
</div>
</div>
 
<div class="actionbox-np2" style='height: 268px;'>
 <div class='mainboxheader'>
 <?php echo $lang['add-dispenseprice']; ?>
 </div>
 <div class='boxcontent'>
  <table class='np-table'>
   <tr>
    <td class="left"><?php echo $lang['add-pergram']; ?> <input type='text' lang='nb' class="fourDigit defaultinput-no-margin-smallborder floatright" placeholder="&euro;" id='salesppg' name='salesppg' /></td>
   </tr>
   <tr>
    <td class="left"><?php echo $lang['add-total']; ?> <input type='text' lang="nb" class="fourDigit defaultinput-no-margin-smallborder floatright" placeholder="&euro;" id="salesPrice" name="salesPrice" /></td>
   </tr>
  </table>
</div>
</div>
<div class="actionbox-np2" style='height: 268px;'>
 <div class='mainboxheader'>
 <?php echo $lang['add-movements']; ?>
 </div>
 <div class='boxcontent'>
  <table class='np-table'>
   <tr>
    <td class="left"><?php echo $lang['add-sampletaste']; ?> <input type='text' lang='nb' class="fourDigit defaultinput-no-margin-smallborder floatright" placeholder="gr" name='sample' /></td>
    <td class="left"><?php echo $lang['add-displayjar']; ?> <input type='text' lang='nb' class="fourDigit defaultinput-no-margin-smallborder floatright" placeholder="gr" name='displayjar' /></td>
   </tr>
   <tr>
    <td class="left"><?php echo $lang['closeday-intstash']; ?> <input type='text' lang='nb' class="fourDigit defaultinput-no-margin-smallborder floatright" placeholder="gr" name='intstash' /></td>
    <td class="left"><?php echo $lang['closeday-extstash']; ?> <input type='text' lang='nb' class="fourDigit defaultinput-no-margin-smallborder floatright" placeholder="gr" name='extstash' /></td>
   </tr>
  </table>
</div>
</div>

<div class="actionbox-np2" style='height: 206px;'>
 <div class='mainboxheader'>
  <?php echo $lang['global-comment']; ?>
 </div>
 <div class='boxcontent'>
<textarea name="adminComment" placeholder="<?php echo $lang['global-comment']; ?>"><?php echo $adminComment; ?></textarea><br /><br />
</div>
</div>

<div class="actionbox-np2" style='height: 206px;'>
 <div class='mainboxheader'>
  <?php echo $lang['barcode']; ?>
 </div>
 <div class='boxcontent'>
	<input type='text' lang='nb' class='eightDigit defaultinput-no-margin-smallborder floatright' name='barCode' value='<?php echo $barCode; ?>' style='margin-top: 10px; width: 255px;' /><br /><br />
</div>
</div>

<!-- <div class='infobox comment'>
  <h3 class="smallerFont">Comments?</h3>
  <br /><?php echo $adminComment; ?>
 </div>
 <br />-->
 
   
 <br />
 <br />
 <center><button class='oneClick cta1nm' name='oneClick' type="submit" style='border: 0;'><?php echo $lang['global-savechanges']; ?></button></center>
</form>

<?php } else { ?>







<h5><?php echo $name . " <span class='usergrouptext' style='margin-bottom: 13px; margin-left: 10px;'>$categoryName</span>"; ?></h5>

<form id="registerForm" action="" method="POST">
    <input type="hidden" name="category" value="<?php echo $category; ?>" />
    <input type="hidden" name="productID" value="<?php echo $productID; ?>" />
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
        $('#realQuantity').on('click keypress keyup blur', compute4);
        $('#purchaseppg').on('click keypress keyup blur', compute2);
        $('#salesppg').on('click keypress keyup blur', compute4);
        $('#purchasePrice').on('click keypress keyup blur', compute);
        $('#salesPrice').on('click keypress keyup blur', compute3);

  }); // end ready
</script>
   <center>

<div class="actionbox-np2">
 <div class='mainboxheader'>
 <?php echo $lang['global-details']; ?>
 </div>
 <div class='boxcontent'>
  <table class='np-table'>
   <tr>
    <td class="biggerFont"><?php echo $lang['global-amount']; ?> <input type='text' lang="nb" class="fourDigit defaultinput-no-margin-smallborder floatright" id="purchaseQuantity" name="purchaseQuantity" placeholder="u" /></td>
    <td><?php echo $lang['provider']; ?> <select name='provider' class='defaultinput-no-margin-smallborder floatright'>
      <option value=''><?php echo $lang['global-choose']; ?></option>
<?php
	$PRquery = "SELECT id, name FROM providers";
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

   <tr>
    <td class="biggerFont left"><label for='inMenu'><?php echo $lang['add-showinmenu']; ?>?</label> <input type="checkbox" name="inMenu" id="inMenu" style="width: 12px; margin-left: 30px;" value="1" /></td>
   </tr>

  </table>
</div>
</div>
   
<div class="actionbox-np2">
 <div class='mainboxheader'>
 <?php echo $lang['add-purchaseprice']; ?>
 </div>
 <div class='boxcontent'>
  <table class='np-table'>
   <tr>
    <td class="left"><?php echo $lang['add-perunit']; ?><input type='text' lang='nb' class="fourDigit defaultinput-no-margin-smallborder floatright" id='purchaseppg' name='purchaseppg' placeholder="&euro;" /></td>
   </tr>
   <tr>
    <td class="left"><?php echo $lang['add-total']; ?> <input type='text' lang="nb" id="purchasePrice" name="purchasePrice" class="fourDigit defaultinput-no-margin-smallborder floatright" placeholder="&euro;" /></td>
   </tr>
   <tr>
    <td class="left"><?php echo $lang['paid']; ?>: <input type='text' lang="nb" id="paidNow" name="paidNow" class="fourDigit defaultinput-no-margin-smallborder floatright" placeholder="&euro;" /></td>
   </tr>
  </table>
</div>
</div>
 
<div class="actionbox-np2" style='height: 268px;'>
 <div class='mainboxheader'>
 <?php echo $lang['add-dispenseprice']; ?>
 </div>
 <div class='boxcontent'>
  <table class='np-table'>
   <tr>
    <td class="left"><?php echo $lang['add-perunit']; ?> <input type='text' lang='nb' class="fourDigit defaultinput-no-margin-smallborder floatright" placeholder="&euro;" id='salesppg' name='salesppg' /></td>
   </tr>
   <tr>
    <td class="left"><?php echo $lang['add-total']; ?> <input type='text' lang="nb" class="fourDigit defaultinput-no-margin-smallborder floatright" placeholder="&euro;" id="salesPrice" name="salesPrice" /></td>
   </tr>
  </table>
</div>
</div>
  <br />
<div class="actionbox-np2">
 <div class='mainboxheader'>
 <?php echo $lang['add-movements']; ?>
 </div>
 <div class='boxcontent'>
  <table class='np-table'>
   <tr>
    <td class="left"><?php echo $lang['add-sampletaste']; ?> <input type='text' lang='nb' class="fourDigit defaultinput-no-margin-smallborder floatright" placeholder="u" name='sample' /></td>
    <td class="left"><?php echo $lang['add-displayjar']; ?> <input type='text' lang='nb' class="fourDigit defaultinput-no-margin-smallborder floatright" placeholder="u" name='displayjar' /></td>
   </tr>
   <tr>
    <td class="left"><?php echo $lang['closeday-intstash']; ?> <input type='text' lang='nb' class="fourDigit defaultinput-no-margin-smallborder floatright" placeholder="u" name='intstash' /></td>
    <td class="left"><?php echo $lang['closeday-extstash']; ?> <input type='text' lang='nb' class="fourDigit defaultinput-no-margin-smallborder floatright" placeholder="u" name='extstash' /></td>
   </tr>
  </table>
</div>
</div>

<div class="actionbox-np2" style='height: 206px;'>
 <div class='mainboxheader'>
  <?php echo $lang['global-comment']; ?>
 </div>
 <div class='boxcontent'>
<textarea name="adminComment" placeholder="<?php echo $lang['global-comment']; ?>"><?php echo $adminComment; ?></textarea><br /><br />
</div>
</div>

<div class="actionbox-np2" style='height: 206px;'>
 <div class='mainboxheader'>
  <?php echo $lang['barcode']; ?>
 </div>
 <div class='boxcontent'>
	<input type='text' lang='nb' class='eightDigit defaultinput-no-margin-smallborder floatright' name='barCode' value='<?php echo $barCode; ?>' style='margin-top: 10px; width: 255px;' /><br /><br />
</div>
</div>

<!-- <div class='infobox comment'>
  <h3 class="smallerFont">Comments?</h3>
  <br /><?php echo $adminComment; ?>
 </div>
 <br />-->
 
   
 <br />
 <br />
 <center><button class='oneClick cta1nm' name='oneClick' type="submit" style='border: 0;'><?php echo $lang['global-savechanges']; ?></button></center>
</form>

<?php } displayFooter();
