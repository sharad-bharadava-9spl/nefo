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
	if (isset($_POST['salesppg'])) {

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
	$salesPrice2 = $_POST['salesppg2'];
	$adminComment = str_replace("'","\'",str_replace('%', '&#37;', trim($_POST['adminComment'])));
	
	$inMenu = $_POST['inMenu'];
	
	$sample = $_POST['sample'];
	$displayjar = $_POST['displayjar'];
	$intstash = $_POST['intstash'];
	$extstash = $_POST['extstash'];
	$extstash2 = $_POST['extstash2'];
	$growtype = $_POST['growtype'];
	$tupperWeight = $_POST['tupperWeight'];
	$provider = $_POST['provider'];
	$paidNow = $_POST['paidNow'];
	$barCode = $_POST['barCode'];
	$medDiscount = $_POST['medDiscount'];
	
	
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
	
	
	if ($_SESSION['domain'] == 'dabulance') {
		
	  $query = sprintf("INSERT INTO purchases (category, productid, purchaseDate, purchasePrice, salesPrice, salesPrice2, purchaseQuantity, realQuantity, adminComment, growType, inMenu, tupperWeight, provider, paid, barCode, medDiscount) VALUES ('%d', '%d', '%s', '%f', '%f', '%f', '%f', '%f', '%s', '%d', '%d', '%f', '%d', '%f', '%s', '%f');",
	  $category, $productID, $purchaseDate, $purchasePrice, $salesPrice, $salesPrice2, $purchaseQuantity, $realQuantity, $adminComment, $growtype, $inMenu, $tupperWeight, $provider, $paidNow, $barCode, $medDiscount);
	  
	} else {
		
	  $query = sprintf("INSERT INTO purchases (category, productid, purchaseDate, purchasePrice, salesPrice, purchaseQuantity, realQuantity, adminComment, growType, inMenu, tupperWeight, provider, paid, barCode, medDiscount) VALUES ('%d', '%d', '%s', '%f', '%f', '%f', '%f', '%s', '%d', '%d', '%f', '%d', '%f', '%s', '%f');",
	  $category, $productID, $purchaseDate, $purchasePrice, $salesPrice, $purchaseQuantity, $realQuantity, $adminComment, $growtype, $inMenu, $tupperWeight, $provider, $paidNow, $barCode, $medDiscount);
	  
  	}
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
	
	if ($_SESSION['domain'] == 'dabulance') {

	if ($extstash2 > 0) {
		  $query = sprintf("INSERT INTO productmovements (movementtime, type, purchaseid, quantity, movementTypeid, doneAtRegistration) VALUES ('%s', '%d', '%d', '%f', '%d', '%d');",
		  $purchaseDate, '2', $purchaseid, $extstash2, '27', '1');
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
	
			$xxs0 = $_POST['xxs0'];
			if ($xxs0 == '') { $xxs0 = 0; }
			$xxs1 = $_POST['xxs1'];
			if ($xxs1 == '') { $xxs1 = 0; }
			$xxs2 = $_POST['xxs2'];
			if ($xxs2 == '') { $xxs2 = 0; }
			$xxs3 = $_POST['xxs3'];
			if ($xxs3 == '') { $xxs3 = 0; }
			$xxs4 = $_POST['xxs4'];
			if ($xxs4 == '') { $xxs4 = 0; }
			
			$xs0 = $_POST['xs0'];
			if ($xs0 == '') { $xs0 = 0; }
			$xs1 = $_POST['xs1'];
			if ($xs1 == '') { $xs1 = 0; }
			$xs2 = $_POST['xs2'];
			if ($xs2 == '') { $xs2 = 0; }
			$xs3 = $_POST['xs3'];
			if ($xs3 == '') { $xs3 = 0; }
			$xs4 = $_POST['xs4'];
			if ($xs4 == '') { $xs4 = 0; }
			
			$s0 = $_POST['s0'];
			if ($s0 == '') { $s0 = 0; }
			$s1 = $_POST['s1'];
			if ($s1 == '') { $s1 = 0; }
			$s2 = $_POST['s2'];
			if ($s2 == '') { $s2 = 0; }
			$s3 = $_POST['s3'];
			if ($s3 == '') { $s3 = 0; }
			$s4 = $_POST['s4'];
			if ($s4 == '') { $s4 = 0; }
			
			$m0 = $_POST['m0'];
			if ($m0 == '') { $m0 = 0; }
			$m1 = $_POST['m1'];
			if ($m1 == '') { $m1 = 0; }
			$m2 = $_POST['m2'];
			if ($m2 == '') { $m2 = 0; }
			$m3 = $_POST['m3'];
			if ($m3 == '') { $m3 = 0; }
			$m4 = $_POST['m4'];
			if ($m4 == '') { $m4 = 0; }
			
			$l0 = $_POST['l0'];
			if ($l0 == '') { $l0 = 0; }
			$l1 = $_POST['l1'];
			if ($l1 == '') { $l1 = 0; }
			$l2 = $_POST['l2'];
			if ($l2 == '') { $l2 = 0; }
			$l3 = $_POST['l3'];
			if ($l3 == '') { $l3 = 0; }
			$l4 = $_POST['l4'];
			if ($l4 == '') { $l4 = 0; }
			
			$xl0 = $_POST['xl0'];
			if ($xl0 == '') { $xl0 = 0; }
			$xl1 = $_POST['xl1'];
			if ($xl1 == '') { $xl1 = 0; }
			$xl2 = $_POST['xl2'];
			if ($xl2 == '') { $xl2 = 0; }
			$xl3 = $_POST['xl3'];
			if ($xl3 == '') { $xl3 = 0; }
			$xl4 = $_POST['xl4'];
			if ($xl4 == '') { $xl4 = 0; }
			
			$xxl0 = $_POST['xxl0'];
			if ($xxl0 == '') { $xxl0 = 0; }
			$xxl1 = $_POST['xxl1'];
			if ($xxl1 == '') { $xxl1 = 0; }
			$xxl2 = $_POST['xxl2'];
			if ($xxl2 == '') { $xxl2 = 0; }
			$xxl3 = $_POST['xxl3'];
			if ($xxl3 == '') { $xxl3 = 0; }
			$xxl4 = $_POST['xxl4'];
			if ($xxl4 == '') { $xxl4 = 0; }
			
			$xxxl0 = $_POST['xxxl0'];
			if ($xxxl0 == '') { $xxxl0 = 0; }
			$xxxl1 = $_POST['xxxl1'];
			if ($xxxl1 == '') { $xxxl1 = 0; }
			$xxxl2 = $_POST['xxxl2'];
			if ($xxxl2 == '') { $xxxl2 = 0; }
			$xxxl3 = $_POST['xxxl3'];
			if ($xxxl3 == '') { $xxxl3 = 0; }
			$xxxl4 = $_POST['xxxl4'];
			if ($xxxl4 == '') { $xxxl4 = 0; }
			
			$xxxxl0 = $_POST['xxxxl0'];
			if ($xxxxl0 == '') { $xxxxl0 = 0; }
			$xxxxl1 = $_POST['xxxxl1'];
			if ($xxxxl1 == '') { $xxxxl1 = 0; }
			$xxxxl2 = $_POST['xxxxl2'];
			if ($xxxxl2 == '') { $xxxxl2 = 0; }
			$xxxxl3 = $_POST['xxxxl3'];
			if ($xxxxl3 == '') { $xxxxl3 = 0; }
			$xxxxl4 = $_POST['xxxxl4'];
			if ($xxxxl4 == '') { $xxxxl4 = 0; }
			
			$query = "INSERT INTO `stock` (`purchaseid`, `xxs0`, `xxs1`, `xxs2`, `xxs3`, `xxs4`, `xs0`, `xs1`, `xs2`, `xs3`, `xs4`, `s0`, `s1`, `s2`, `s3`, `s4`, `m0`, `m1`, `m2`, `m3`, `m4`, `l0`, `l1`, `l2`, `l3`, `l4`, `xl0`, `xl1`, `xl2`, `xl3`, `xl4`, `xxl0`, `xxl1`, `xxl2`, `xxl3`, `xxl4`, `xxxl0`, `xxxl1`, `xxxl2`, `xxxl3`, `xxxl4`, `xxxxl0`, `xxxxl1`, `xxxxl2`, `xxxxl3`, `xxxxl4`) VALUES ('$purchaseid', '$xxs0', '$xxs1', '$xxs2', '$xxs3', '$xxs4', '$xs0', '$xs1', '$xs2', '$xs3', '$xs4', '$s0', '$s1', '$s2', '$s3', '$s4', '$m0', '$m1', '$m2', '$m3', '$m4', '$l0', '$l1', '$l2', '$l3', '$l4', '$xl0', '$xl1', '$xl2', '$xl3', '$xl4', '$xxl0', '$xxl1', '$xxl2', '$xxl3', '$xxl4', '$xxxl0', '$xxxl1', '$xxxl2', '$xxxl3', '$xxxl4', '$xxxxl0', '$xxxxl1', '$xxxxl2', '$xxxxl3', '$xxxxl4')";
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
		
		if ($_SESSION['domain'] == 'dabulance') {
			$productID = $_GET['productid'];
			$category = $_GET['category'];
		} else {
			echo $lang['error-noprodselected'];
			exit();
		}
	}
	
	if ($_POST['prePurchase'] || $_SESSION['domain'] == 'dabulance') {
		
		if ($productID == '' && $category == '') {
			
			$productID = $_POST['prePurchase'];
			$category = $_POST['category'];
			$categoryName = $_POST['categoryName'];
			
		}

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
			$selectProduct = "SELECT productid, name, productnumber, breed2 FROM products WHERE productid = {$productID}";
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
				$breed2 = $row['breed2'];
				$productnumber = $row['productnumber'];
				
		if ($breed2 != '') {
			$name = $name . " x " . $breed2;
		}

				
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
    <td class="biggerFont"><?php echo $lang['add-amountpurchased']; ?> <input type='text' lang="nb" class="fourDigit defaultinput-no-margin-smallborder floatright" id="purchaseQuantity" name="purchaseQuantity" placeholder="gr" /></td>
    <td class="biggerFont"><?php echo $lang['add-realweight']; ?> <input type="text" lang="nb" class="fourDigit defaultinput-no-margin-smallborder floatright" id="realQuantity" name="realQuantity" placeholder="gr" /></td>
   </tr>
   <tr>
<?php if ($category == '1' || $type == 1) { ?>
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

	    <td class="biggerFont left">
		 <span><?php echo $lang['add-showinmenu']; ?>?</span>
		 	<div class="fakeboxholder">	
			 <label class="control">
			  &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
			  <input type="checkbox" name="inMenu" id="inMenu" value="1">
			  <div class="fakebox" style="top: 5px;margin-left: 30px;"></div>
			 </label>
			</div>
		</td>
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
    <td class="left"><?php echo $lang['add-pergram']; ?><input type='text' lang='nb' class="fourDigit defaultinput-no-margin-smallborder floatright" id='purchaseppg' name='purchaseppg' placeholder="<?php echo $_SESSION['currencyoperator'] ?>" /></td>
   </tr>
   <tr>
    <td class="left"><?php echo $lang['add-total']; ?> <input type='text' lang="nb" id="purchasePrice" name="purchasePrice" class="fourDigit defaultinput-no-margin-smallborder floatright" placeholder="<?php echo $_SESSION['currencyoperator'] ?>" /></td>
   </tr>
   <tr>
    <td class="left"><?php echo $lang['paid']; ?>: <input type='text' lang="nb" id="paidNow" name="paidNow" class="fourDigit defaultinput-no-margin-smallborder floatright" placeholder="<?php echo $_SESSION['currencyoperator'] ?>" /></td>
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
    <td class="left"><?php echo $lang['add-pergram']; ?> <input type='text' lang='nb' class="fourDigit defaultinput-no-margin-smallborder floatright" placeholder="<?php echo $_SESSION['currencyoperator'] ?>" id='salesppg' name='salesppg' /></td>
   </tr>
   <tr>
    <td class="left"><?php echo $lang['add-total']; ?> <input type='text' lang="nb" class="fourDigit defaultinput-no-margin-smallborder floatright" placeholder="<?php echo $_SESSION['currencyoperator'] ?>" id="salesPrice" name="salesPrice" /></td>
   </tr>
   <tr>
    <td class="biggerFont left"><img src="images/medical-15.png" style='margin-bottom: -3px;' /> <?php echo $lang['member-discount']; ?> 
    <input type='text' lang="nb" class="defaultinput-no-margin-smallborder fourDigit floatright" id="medDiscount" name="medDiscount" value="<?php echo $medDiscount; ?>" /> %</td>
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

<div class="actionbox-np2" style='height: 268px;'>
 <div class='mainboxheader'>
 <?php echo $lang['global-details']; ?>
 </div>
 <div class='boxcontent'>
  <table class='np-table'>
   <tr>
    <td class="biggerFont"><?php echo $lang['add-amountpurchased']; ?> <input type='text' lang="nb" class="fourDigit defaultinput-no-margin-smallborder floatright" id="purchaseQuantity" name="purchaseQuantity" placeholder="u" /></td>
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
	    <td class="biggerFont left">
		 <span><?php echo $lang['add-showinmenu']; ?>?</span>
		 	<div class="fakeboxholder">	
			 <label class="control">
			  &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
			  <input type="checkbox" name="inMenu" id="inMenu" value="1">
			  <div class="fakebox" style="top: 5px;margin-left: 30px;"></div>
			 </label>
			</div>
		</td>
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
    <td class="left"><?php echo $lang['add-perunit']; ?><input type='text' lang='nb' class="fourDigit defaultinput-no-margin-smallborder floatright" id='purchaseppg' name='purchaseppg' placeholder="<?php echo $_SESSION['currencyoperator'] ?>" /></td>
   </tr>
   <tr>
    <td class="left"><?php echo $lang['add-total']; ?> <input type='text' lang="nb" id="purchasePrice" name="purchasePrice" class="fourDigit defaultinput-no-margin-smallborder floatright" placeholder="<?php echo $_SESSION['currencyoperator'] ?>" /></td>
   </tr>
   <tr>
    <td class="left"><?php echo $lang['paid']; ?>: <input type='text' lang="nb" id="paidNow" name="paidNow" class="fourDigit defaultinput-no-margin-smallborder floatright" placeholder="<?php echo $_SESSION['currencyoperator'] ?>" /></td>
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
<?php if ($_SESSION['domain'] == 'dabulance') { ?>
   <tr>
    <td class="left">Wholesale <input type='text' lang='nb' class="fourDigit defaultinput-no-margin-smallborder floatright" placeholder="<?php echo $_SESSION['currencyoperator'] ?>" id='salesppg' name='salesppg2' /></td>
   </tr>
   <tr>
    <td class="left">Retail <input type='text' lang='nb' class="fourDigit defaultinput-no-margin-smallborder floatright" placeholder="<?php echo $_SESSION['currencyoperator'] ?>" id='salesppg' name='salesppg' /></td>
   </tr>
<?php } else { ?>
   <tr>
    <td class="left"><?php echo $lang['add-perunit']; ?> <input type='text' lang='nb' class="fourDigit defaultinput-no-margin-smallborder floatright" placeholder="<?php echo $_SESSION['currencyoperator'] ?>" id='salesppg' name='salesppg' /></td>
   </tr>
   <tr>
    <td class="left"><?php echo $lang['add-total']; ?> <input type='text' lang="nb" class="fourDigit defaultinput-no-margin-smallborder floatright" placeholder="<?php echo $_SESSION['currencyoperator'] ?>" id="salesPrice" name="salesPrice" /></td>
   </tr>
   <tr>
    <td class="biggerFont left"><img src="images/medical-15.png" style='margin-bottom: -3px;' /> <?php echo $lang['member-discount']; ?> 
    <input type='text' lang="nb" class="defaultinput-no-margin-smallborder fourDigit floatright" id="medDiscount" name="medDiscount" value="<?php echo $medDiscount; ?>" /> %</td>
   </tr>
<?php } ?>
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
<?php 	if ($_SESSION['domain'] == 'dabulance') { ?>

<div class="actionbox-np2">
 <div class='mainboxheader'>
  SIZES
 </div>
 <div class='boxcontent'>
  <table>
   <tr>
    <td class="center"></td>
    <td class="center" style="width: 150px;"><strong>STOCKROOM</td>
    <td class="center" style="width: 150px;"><strong>Dank Grass</td>
    <td class="center" style="width: 150px;"><strong>Selva</td>
    <td class="center" style="width: 150px;"><strong>Green Boutique</td>
    <td class="center" style="width: 150px;"><strong>Purple Room</td>
   </tr>
   <tr>
    <td class="center"><strong>XXS</strong></td>
    <td class="center"><input type="number" name="xxs0" class="defaultinput twoDigit" /></td>
    <td class="center"><input type="number" name="xxs1" class="defaultinput twoDigit" /></td>
    <td class="center"><input type="number" name="xxs2" class="defaultinput twoDigit" /></td>
    <td class="center"><input type="number" name="xxs3" class="defaultinput twoDigit" /></td>
    <td class="center"><input type="number" name="xxs4" class="defaultinput twoDigit" /></td>
   </tr>
   <tr>
    <td class="center"><strong>XS</strong></td>
    <td class="center"><input type="number" name="xs0" class="defaultinput twoDigit" /></td>
    <td class="center"><input type="number" name="xs1" class="defaultinput twoDigit" /></td>
    <td class="center"><input type="number" name="xs2" class="defaultinput twoDigit" /></td>
    <td class="center"><input type="number" name="xs3" class="defaultinput twoDigit" /></td>
    <td class="center"><input type="number" name="xs4" class="defaultinput twoDigit" /></td>
   </tr>
   <tr>
    <td class="center"><strong>S</strong></td>
    <td class="center"><input type="number" name="s0" class="defaultinput twoDigit" /></td>
    <td class="center"><input type="number" name="s1" class="defaultinput twoDigit" /></td>
    <td class="center"><input type="number" name="s2" class="defaultinput twoDigit" /></td>
    <td class="center"><input type="number" name="s3" class="defaultinput twoDigit" /></td>
    <td class="center"><input type="number" name="s4" class="defaultinput twoDigit" /></td>
   </tr>
   <tr>
    <td class="center"><strong>M</strong></td>
    <td class="center"><input type="number" name="m0" class="defaultinput twoDigit" /></td>
    <td class="center"><input type="number" name="m1" class="defaultinput twoDigit" /></td>
    <td class="center"><input type="number" name="m2" class="defaultinput twoDigit" /></td>
    <td class="center"><input type="number" name="m3" class="defaultinput twoDigit" /></td>
    <td class="center"><input type="number" name="m4" class="defaultinput twoDigit" /></td>
   </tr>
   <tr>
    <td class="center"><strong>L</strong></td>
    <td class="center"><input type="number" name="l0" class="defaultinput twoDigit" /></td>
    <td class="center"><input type="number" name="l1" class="defaultinput twoDigit" /></td>
    <td class="center"><input type="number" name="l2" class="defaultinput twoDigit" /></td>
    <td class="center"><input type="number" name="l3" class="defaultinput twoDigit" /></td>
    <td class="center"><input type="number" name="l4" class="defaultinput twoDigit" /></td>
   </tr>
   <tr>
    <td class="center"><strong>XL</strong></td>
    <td class="center"><input type="number" name="xl0" class="defaultinput twoDigit" /></td>
    <td class="center"><input type="number" name="xl1" class="defaultinput twoDigit" /></td>
    <td class="center"><input type="number" name="xl2" class="defaultinput twoDigit" /></td>
    <td class="center"><input type="number" name="xl3" class="defaultinput twoDigit" /></td>
    <td class="center"><input type="number" name="xl4" class="defaultinput twoDigit" /></td>
   </tr>
   <tr>
    <td class="center"><strong>XXL</strong></td>
    <td class="center"><input type="number" name="xxl0" class="defaultinput twoDigit" /></td>
    <td class="center"><input type="number" name="xxl1" class="defaultinput twoDigit" /></td>
    <td class="center"><input type="number" name="xxl2" class="defaultinput twoDigit" /></td>
    <td class="center"><input type="number" name="xxl3" class="defaultinput twoDigit" /></td>
    <td class="center"><input type="number" name="xxl4" class="defaultinput twoDigit" /></td>
   </tr>
   <tr>
    <td class="center"><strong>XXXL</strong></td>
    <td class="center"><input type="number" name="xxxl0" class="defaultinput twoDigit" /></td>
    <td class="center"><input type="number" name="xxxl1" class="defaultinput twoDigit" /></td>
    <td class="center"><input type="number" name="xxxl2" class="defaultinput twoDigit" /></td>
    <td class="center"><input type="number" name="xxxl3" class="defaultinput twoDigit" /></td>
    <td class="center"><input type="number" name="xxxl4" class="defaultinput twoDigit" /></td>
   </tr>
   <tr>
    <td class="center"><strong>XXXXL</strong></td>
    <td class="center"><input type="number" name="xxxxl0" class="defaultinput twoDigit" /></td>
    <td class="center"><input type="number" name="xxxxl1" class="defaultinput twoDigit" /></td>
    <td class="center"><input type="number" name="xxxxl2" class="defaultinput twoDigit" /></td>
    <td class="center"><input type="number" name="xxxxl3" class="defaultinput twoDigit" /></td>
    <td class="center"><input type="number" name="xxxxl4" class="defaultinput twoDigit" /></td>
   </tr>
  </table>
    
   </div>
   </div>
  <br />
<div class="actionbox-np2">
 <div class='mainboxheader'>
  STOCK
 </div>
 <div class='boxcontent'>
  <table>
   <tr>
    <td class="left">Dank Grass</td>
    <td><input type='text' lang='nb' class='defaultinput fourDigit' name='displayjar' /> u</td>
   </tr>
   <tr>
    <td class="left">Selva</td>
    <td><input type='text' lang='nb' class='defaultinput fourDigit' name='intstash' /> u</td>
   </tr>
   <tr>
    <td class="left">Green Boutique</td>
    <td><input type='text' lang='nb' class='defaultinput fourDigit' name='extstash' /> u</td>
   </tr>
   <tr>
    <td class="left">Purple Room</td>
    <td><input type='text' lang='nb' class='defaultinput fourDigit' name='extstash2' /> u</td>
   </tr>
  </table>
   </div>
   </div>
<?php }  else { 
	echo "<br />";
}
?>
<div class="actionbox-np2" style='height: 206px;'>
 <div class='mainboxheader'>
  <?php echo $lang['global-comment']; ?>
 </div>
 <div class='boxcontent'>
<textarea name="adminComment" placeholder="<?php echo $lang['global-comment']; ?>" class="defaultinput"><?php echo $adminComment; ?></textarea><br /><br />
</div>
</div>

<div class="actionbox-np2" style='height: 206px;'>
 <div class='mainboxheader'>
  <?php echo $lang['barcode']; ?>
 </div>
 <div class='boxcontent'>
	<input type='text' lang='nb' class='eightDigit defaultinput' name='barCode' value='<?php echo $barCode; ?>' style='margin-top: 10px; width: 255px;' /><br /><br />
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
