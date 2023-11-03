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
	$purchaseQuantity = $_POST['purchaseQuantity'];
	if ($category < 3) {
		$realQuantity = $_POST['realQuantity'];
	} else {
		$realQuantity = $_POST['purchaseQuantity'];
	}
	$purchasePrice = $_POST['purchaseppg'];
	$salesPrice = $_POST['salesppg'];
	$adminComment = $_POST['adminComment'];
	$closingComment = $_POST['closingComment'];
	$purchaseid = $_POST['purchaseid'];
	$inMenu = $_POST['inMenu'];
	
	$sample = $_POST['sample'];
	$displayjar = $_POST['displayjar'];
	$intstash = $_POST['intstash'];
	$extstash = $_POST['extstash'];
	$sampleID = $_POST['sampleID'];
	$displayjarID = $_POST['displayjarID'];
	$intstashID = $_POST['intstashID'];
	$extstashID = $_POST['extstashID'];
	$growtype = $_POST['growtype'];
	$purchaseDate = $_POST['purchaseDate'];
	
	$tupperWeight = $_POST['tupperWeight'];
	$provider = $_POST['provider'];
	$medDiscount = $_POST['medDiscount'];
	$barCode = $_POST['barCode'];
	$paidNow = $_POST['paidNow'];

                // KONSTANT CODE UPDATE BEGIN
        	$volumeDiscounts = "DELETE FROM volume_discounts WHERE purchaseid = $purchaseid";
		try
		{
			$result = $pdo3->prepare("$volumeDiscounts")->execute();
		}
		catch (PDOException $e)
		{
                    $error = 'Error fetching user: ' . $e->getMessage();
                    echo $error;
                    exit();
		}
        

                for($i = 0; $i < count($_POST['volume_unit']); $i++) {
                    if(!empty($_POST['volume_unit'][$i])){
                        $volume_unit = $_POST['volume_unit'][$i];
                        $volume_unit_price = $_POST['volume_unit_price'][$i];
                        $addDiscountB = sprintf("INSERT INTO volume_discounts (purchaseid, units, amount) VALUES ('%d', '%d', '%d');",
                          $purchaseid, $volume_unit, $volume_unit_price);
                        try
                        {
                            $result = $pdo3->prepare("$addDiscountB")->execute();
                        }
                        catch (PDOException $e)
                        {
                            $error = 'Error fetching user: ' . $e->getMessage();
                            echo $error;
                            exit();
                        }
                    }
                }    
                // KONSTANT CODE UPDATE END

		// Update/add/remove initial product movements
		
		// sample taste
		if (($sample == 0 || $sample == '') && $sampleID != '') {
			
			$deleteMovement = "DELETE FROM productmovements WHERE movementid = $sampleID";
		try
		{
			$result = $pdo3->prepare("$deleteMovement")->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
			
		} else if ($sample > 0) {
			
			if ($sampleID == '') {
				
			  $updateMovement = sprintf("INSERT INTO productmovements (movementtime, type, purchaseid, quantity, movementTypeid, doneAtRegistration) VALUES ('%s', '%d', '%d', '%f', '%d', '%d');",
			  $purchaseDate, '2', $purchaseid, $sample, '8', '1');
		  
			} else {
				
		$updateMovement = sprintf("UPDATE productmovements SET quantity = '%f' WHERE movementid = $sampleID;",
			$sample
);
			}
		try
		{
			$result = $pdo3->prepare("$updateMovement")->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
		}

		// display jar
		if (($displayjar == 0 || $displayjar == '') && $displayjarID != '') {
			
			$deleteMovement = "DELETE FROM productmovements WHERE movementid = $displayjarID";
	
		try
		{
			$result = $pdo3->prepare("$deleteMovement")->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
			
		} else if ($displayjar > 0) {
			
			if ($displayjarID == '') {
				
			  $updateMovement = sprintf("INSERT INTO productmovements (movementtime, type, purchaseid, quantity, movementTypeid, doneAtRegistration) VALUES ('%s', '%d', '%d', '%f', '%d', '%d');",
			  $purchaseDate, '2', $purchaseid, $displayjar, '9', '1');
		  
			} else {
				
		$updateMovement = sprintf("UPDATE productmovements SET quantity = '%f' WHERE movementid = $displayjarID;",
			$displayjar
);
			}
		try
		{
			$result = $pdo3->prepare("$updateMovement")->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
		}

		// internal stash
		if (($intstash == 0 || $intstash == '') && $intstashID != '') {
			
			$deleteMovement = "DELETE FROM productmovements WHERE movementid = $intstashID";
		try
		{
			$result = $pdo3->prepare("$deleteMovement")->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
			
		} else if ($intstash > 0) {
			
			if ($intstashID == '') {
				
			  $updateMovement = sprintf("INSERT INTO productmovements (movementtime, type, purchaseid, quantity, movementTypeid, doneAtRegistration) VALUES ('%s', '%d', '%d', '%f', '%d', '%d');",
			  $purchaseDate, '2', $purchaseid, $intstash, '5', '1');
		  
			} else {
				
		$updateMovement = sprintf("UPDATE productmovements SET quantity = '%f' WHERE movementid = $intstashID;",
			$intstash
);
			}
		try
		{
			$result = $pdo3->prepare("$updateMovement")->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
		}

		// external stash
		if (($extstash == 0 || $extstash == '') && $extstashID != '') {
			
			$deleteMovement = "DELETE FROM productmovements WHERE movementid = $extstashID";
		try
		{
			$result = $pdo3->prepare("$deleteMovement")->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
			
		} else if ($extstash > 0) {
			
			if ($extstashID == '') {
				
			  $updateMovement = sprintf("INSERT INTO productmovements (movementtime, type, purchaseid, quantity, movementTypeid, doneAtRegistration) VALUES ('%s', '%d', '%d', '%f', '%d', '%d');",
			  $purchaseDate, '2', $purchaseid, $extstash, '6', '1');
		  
			} else {
				
		$updateMovement = sprintf("UPDATE productmovements SET quantity = '%f' WHERE movementid = $extstashID;",
			$extstash
);
			}
		try
		{
			$result = $pdo3->prepare("$updateMovement")->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
		}
		
		
		// Query to update purchase
		$updatePurchase = sprintf("UPDATE purchases SET purchasePrice = '%f', salesPrice = '%f', purchaseQuantity = '%f', realQuantity = '%f', adminComment = '%s', closingComment = '%s', inMenu = '%d', growType = '%d', tupperWeight = '%f', provider = '%d', medDiscount = '%d', barCode = '%s', paid = '%f' WHERE purchaseid = $purchaseid;",
			$purchasePrice,
			$salesPrice,
			$purchaseQuantity,
			$realQuantity,
			$adminComment,
			$closingComment,
			$inMenu,
			$growtype,
			$tupperWeight,
			$provider,
			$medDiscount,
			$barCode,
			$paidNow
);
			
		try
		{
			$result = $pdo3->prepare("$updatePurchase")->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
			
		// On success: redirect.
		$_SESSION['successMessage'] = $lang['purchases-updatesuccess'];
		header("Location: purchase.php?purchaseid={$purchaseid}");
		exit();
	}
	/***** FORM SUBMIT END *****/
	
	
	
	// Does purchase ID exist?
	if (!$_GET['purchaseid']) {
		echo $lang['error-nopurchselected'];
		exit();
	} else  {
		$purchaseid = $_GET['purchaseid'];
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

	// Query to look for purchase
	$purchaseDetails = "SELECT category, productid, purchaseDate, purchasePrice, salesPrice, purchaseQuantity, realQuantity, adminComment, estClosing, closingComment, closedAt, inMenu, growType, tupperWeight, provider, medDiscount, barCode, paid FROM purchases WHERE purchaseid = $purchaseid";
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
	$purchaseDate = $row['purchaseDate'];
	$purchasePrice = $row['purchasePrice'];
	$salesPrice = $row['salesPrice'];
	$purchaseQuantity = $row['purchaseQuantity'];
	$realQuantity = $row['realQuantity'];
	$adminComment = $row['adminComment']; // Purchase comment, really
	$estClosing = $row['estClosing'];
	$closingComment = $row['closingComment']; // Only active when product closed (if even then)
	$closedAt = $row['closedAt'];
	$inMenu = $row['inMenu'];
	$growtype = $row['growType'];
	$tupperWeight = $row['tupperWeight'];
	$provider = $row['provider'];
	$medDiscount = $row['medDiscount'];
	$barCode = $row['barCode'];
	$paidNow = $row['paid'];

	$purchasePriceTotal = round($purchasePrice * $purchaseQuantity,2);
	$salesPriceTotal = round($salesPrice * $realQuantity,2);

	$closeDiff = $closedAt - $estClosing;
	
	$growtypeID = $growtype;
	
	$growDetails = "SELECT name FROM providers WHERE id = $provider";
		try
		{
			$result = $pdo3->prepare("$growDetails");
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
		
	$growDetails = "SELECT growtype FROM growtypes WHERE growtypeid = $growtype";
		try
		{
			$result = $pdo3->prepare("$growDetails");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
		$growtype = $row['growtype'];

	
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
			$categoryName = 'Flower';
			
		if ($breed2 != '') {
			$name = $name . " x " . $breed2;
		}

			
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
			$categoryName = 'Extract';
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
			$categoryName = $row['name'];
			$type = $row['type'];
			
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
		}	
		

		// Look up product movements. Also remember to save the new ones!! Using UPDATE!
		// sample taste
		$sample = "SELECT movementid, quantity FROM productmovements WHERE purchaseid = $purchaseid AND movementTypeid = 8 AND doneAtRegistration = 1";
		try
		{
			$result = $pdo3->prepare("$sample");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
				$sample = $row['quantity'];
				$sampleID = $row['movementid'];
				
		// display jar
		$displayjar = "SELECT movementid, quantity FROM productmovements WHERE purchaseid = $purchaseid AND movementTypeid = 9 AND doneAtRegistration = 1";
		try
		{
			$result = $pdo3->prepare("$displayjar");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
				$displayjar = $row['quantity'];
				$displayjarID = $row['movementid'];
				
		// internal stash
		$intstash = "SELECT movementid, quantity FROM productmovements WHERE purchaseid = $purchaseid AND movementTypeid = 5 AND doneAtRegistration = 1";
		try
		{
			$result = $pdo3->prepare("$intstash");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
				$intstash = $row['quantity'];
				$intstashID = $row['movementid'];
				
		// external stash
		$extstash = "SELECT movementid, quantity FROM productmovements WHERE purchaseid = $purchaseid AND movementTypeid = 6 AND doneAtRegistration = 1";
		try
		{
			$result = $pdo3->prepare("$extstash");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
				$extstash = $row['quantity'];
				$extstashID = $row['movementid'];

								




	pageStart($lang['title-editpurchase'], NULL, $validationScript, "ppurchase", "admin", $lang['admin-editpurchase'], $_SESSION['successMessage'], $_SESSION['errorMessage']);
	
	
?>

    
<?php if ($category < 3 || $type == 1) { ?>

<h5><?php echo "<a href='purchase.php?purchaseid=$purchaseid'>$name</a> <span class='usergrouptext' style='margin-bottom: 13px; margin-left: 10px;'>$categoryName</span>"; ?></h5>

<form id="registerForm" action="" method="POST">
    <input type="hidden" name="category" value="<?php echo $category; ?>" />
    <input type="hidden" name="productID" value="<?php echo $productID; ?>" />
    <input type="hidden" name="purchaseid" value="<?php echo $purchaseid; ?>" />
    <input type="hidden" name="sampleID" value="<?php echo $sampleID; ?>" />
    <input type="hidden" name="displayjarID" value="<?php echo $displayjarID; ?>" />
    <input type="hidden" name="intstashID" value="<?php echo $intstashID; ?>" />
    <input type="hidden" name="extstashID" value="<?php echo $extstashID; ?>" />
    <input type="hidden" name="purchaseDate" value="<?php echo $purchaseDate; ?>" />
    
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

        $('#purchaseQuantity').on('keypress keyup', compute2);
        $('#realQuantity').on('keypress keyup', compute4);
        $('#purchaseppg').on('keypress keyup', compute2);
        $('#salesppg').on('keypress keyup', compute4);
        $('#purchasePrice').on('keypress keyup', compute);
        $('#salesPrice').on('keypress keyup', compute3);

  }); // end ready
        // KONSTANT CODE UPDATE BEGIN
        var btncount= 1;
        function addMoreDiscount(){
            if(btncount < 11){
                var unitName = "<?php echo $lang['units-grams']; ?>";
                var unitPrice = "<?php echo $lang['add-total']; ?>";
                $("#volumeDiv").after('<tr><td>'+ unitName +'</td><td><input type="number" lang="nb" class="fourDigit" name="volume_unit[]" /> </td><td>'+ unitPrice + '</td><td><input type="number" lang="nb" class="fourDigit" name="volume_unit_price[]" /> &euro;</td></tr><br>');
            }else{
                $("#addMoreDiscountButton").hide();
            }
            btncount++;
        }
        // KONSTANT CODE UPDATE END

</script>
<div class="actionbox-np2">
 <div class='mainboxheader'>
 <?php echo $lang['global-details']; ?>
 </div>
 <div class='boxcontent'>
  <table class='np-table'>
   <tr>
    <td class="biggerFont"><?php echo $lang['global-amount']; ?> <input type='text' lang="nb" class="fourDigit defaultinput-no-margin-smallborder floatright" id="purchaseQuantity" name="purchaseQuantity" placeholder="gr" value="<?php echo $purchaseQuantity; ?>" /></td>
    <td class="biggerFont"><?php echo $lang['add-realweight']; ?> <input type="text" lang="nb" class="fourDigit defaultinput-no-margin-smallborder floatright" id="realQuantity" name="realQuantity" placeholder="gr" value="<?php echo $realQuantity; ?>" /></td>
   </tr>
   <tr>
<?php if ($category == '1') { ?>
    <td class="biggerFont left"><?php echo $lang['global-growtype']; ?> <select name='growtype' class='defaultinput-no-margin-smallborder floatright'>
      <option value='<?php echo $growtypeID; ?>'><?php echo $growtype; ?></option>
<?php
	$GRquery = "SELECT growtypeid, growtype FROM growtypes WHERE growtypeid <> $growtypeID";
		try
		{
			$results = $pdo3->prepare("$GRquery");
			$results->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		while ($GRtype = $results->fetch()) {
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
      <option value='<?php echo $provider; ?>'><?php echo $providerName; ?></option>
<?php
	$PRquery = "SELECT id, name FROM providers WHERE id <> $provider";
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
     </select>
    </td>
   </tr>
   <tr>
    <td class="biggerFont left"><?php echo $lang['jar-weight']; ?> <input type='text' lang="nb" class="fourDigit defaultinput-no-margin-smallborder floatright" id="tupperWeight" name="tupperWeight" placeholder="gr" value="<?php echo $tupperWeight; ?>" /></td>
    <td class="biggerFont left"><label for='inMenu'><?php echo $lang['add-showinmenu']; ?>?</label> <input type="checkbox" name="inMenu" id="inMenu" style="width: 12px; margin-left: 30px;" value="1" <?php if ($inMenu == 1) { echo "checked"; } ?> /></td>
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
    <td class="left"><?php echo $lang['add-pergram']; ?><input type='text' lang='nb' class="fourDigit defaultinput-no-margin-smallborder floatright" id='purchaseppg' name='purchaseppg' placeholder="&euro;" value="<?php echo $purchasePrice; ?>" /></td>
   </tr>
   <tr>
    <td class="left"><?php echo $lang['add-total']; ?> <input type='text' lang="nb" id="purchasePrice" name="purchasePrice" class="fourDigit defaultinput-no-margin-smallborder floatright" placeholder="&euro;" value="<?php echo $purchasePriceTotal; ?>" /></td>
   </tr>
   <tr>
    <td class="left"><?php echo $lang['paid']; ?>: <input type='text' lang="nb" id="paidNow" name="paidNow" class="fourDigit defaultinput-no-margin-smallborder floatright" placeholder="&euro;" value="<?php echo $paidNow; ?>" /></td>
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
    <td class="left"><?php echo $lang['add-pergram']; ?> <input type='text' lang='nb' class="fourDigit defaultinput-no-margin-smallborder floatright" placeholder="&euro;" id='salesppg' name='salesppg' value="<?php echo $salesPrice; ?>" /></td>
   </tr>
   <tr>
    <td class="left"><?php echo $lang['add-total']; ?> <input type='text' lang="nb" class="fourDigit defaultinput-no-margin-smallborder floatright" placeholder="&euro;" id="salesPrice" name="salesPrice" value="<?php echo $salesPriceTotal; ?>"/></td>
   </tr>
  </table>
</div>
</div>


  <!-- // KONSTANT CODE UPDATE BEGIN -->
<div class="actionbox-np2" style='height: 268px;'>
 <div class='mainboxheader'>
 <?php echo $lang['volume-discounts']; ?>
 </div>
  <table>
<?php 
    $volumeDiscounts = "SELECT * FROM volume_discounts WHERE purchaseid = $purchaseid";
    $result = $pdo3->prepare("$volumeDiscounts");
    $result->execute();
    while ($rs = $result->fetch()) { ?>
    <tr>
        <td><?php echo $lang['units-grams']; ?></td>
        <td><input type='number' lang='nb' class='fourDigit' name='volume_unit[]' value="<?php echo $rs['units'];?>" /></td>
        <td><?php echo $lang['add-total']; ?></td>
        <td><input type="number" lang="nb" class="fourDigit" name="volume_unit_price[]" value="<?php echo $rs['amount'];?>" /> &euro;</td>
    </tr>
        
<?php } ?>      
   <tr id="volumeDiv">
    <td><?php echo $lang['units-grams']; ?></td>
    <td><input type='number' lang='nb' class='fourDigit' name='volume_unit[]' /></td>
    <td><?php echo $lang['add-total']; ?></td>
    <td><input type="number" lang="nb" class="fourDigit" name="volume_unit_price[]" /> &euro;</td>
   <br>
   </tr>
    <tr>
       <td colspan="4">
           <button type="button" onclick="addMoreDiscount()" id="addMoreDiscountButton"><?php echo $lang['add-more']; ?></button> 
       </td>
    </tr>
  </table>
   </div>
  <br />
  <!-- // KONSTANT CODE UPDATE END -->

<div class="actionbox-np2" style='height: 268px;'>
 <div class='mainboxheader'>
 <?php echo $lang['add-movements']; ?>
 </div>
 <div class='boxcontent'>
  <table class='np-table'>
   <tr>
    <td class="left"><?php echo $lang['add-sampletaste']; ?> <input type='text' lang='nb' class="fourDigit defaultinput-no-margin-smallborder floatright" placeholder="gr" name='sample' value='<?php echo $sample; ?>' /></td>
    <td class="left"><?php echo $lang['add-displayjar']; ?> <input type='text' lang='nb' class="fourDigit defaultinput-no-margin-smallborder floatright" placeholder="gr" name='displayjar' value='<?php echo $displayjar; ?>' /></td>
   </tr>
   <tr>
    <td class="left"><?php echo $lang['closeday-intstash']; ?> <input type='text' lang='nb' class="fourDigit defaultinput-no-margin-smallborder floatright" placeholder="gr" name='intstash' value='<?php echo $intstash; ?>' /></td>
    <td class="left"><?php echo $lang['closeday-extstash']; ?> <input type='text' lang='nb' class="fourDigit defaultinput-no-margin-smallborder floatright" placeholder="gr" name='extstash' value='<?php echo $extstash; ?>' /></td>
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

 <br />
 <br />
 <center><button class='oneClick cta1nm' name='oneClick' type="submit" style='border: 0;'><?php echo $lang['global-savechanges']; ?></button></center>
</form>

<?php } else { ?>

<h5><?php echo "<a href='purchase.php?purchaseid=$purchaseid'>$name</a> <span class='usergrouptext' style='margin-bottom: 13px; margin-left: 10px;'>$categoryName</span>"; ?></h5>

<form id="registerForm" action="" method="POST">
    <input type="hidden" name="category" value="<?php echo $category; ?>" />
    <input type="hidden" name="productID" value="<?php echo $productID; ?>" />
    <input type="hidden" name="purchaseid" value="<?php echo $purchaseid; ?>" />
    <input type="hidden" name="sampleID" value="<?php echo $sampleID; ?>" />
    <input type="hidden" name="displayjarID" value="<?php echo $displayjarID; ?>" />
    <input type="hidden" name="intstashID" value="<?php echo $intstashID; ?>" />
    <input type="hidden" name="extstashID" value="<?php echo $extstashID; ?>" />
    <input type="hidden" name="purchaseDate" value="<?php echo $purchaseDate; ?>" />
    
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

        $('#purchaseQuantity').on('keypress keyup', compute2);
        $('#realQuantity').on('keypress keyup', compute4);
        $('#purchaseppg').on('keypress keyup', compute2);
        $('#salesppg').on('keypress keyup', compute4);
        $('#purchasePrice').on('keypress keyup', compute);
        $('#salesPrice').on('keypress keyup', compute3);

  }); // end ready
    // KONSTANT CODE UPDATE BEGIN
    var btncount= 1;
    function addMoreDiscount(){
        if(btncount < 11){
            var unitName = "<?php echo $lang['units-grams']; ?>";
            var unitPrice = "<?php echo $lang['add-total']; ?>";
            $("#volumeDiv").after('<tr><td>'+ unitName +'</td><td><input type="number" lang="nb" class="fourDigit" name="volume_unit[]" /> </td><td>'+ unitPrice + '</td><td><input type="number" lang="nb" class="fourDigit" name="volume_unit_price[]" /> &euro;</td></tr><br>');
        }else{
            $("#addMoreDiscountButton").hide();
        }
        btncount++;
    }
    // KONSTANT CODE UPDATE END

</script>
    
<div class="actionbox-np2">
 <div class='mainboxheader'>
 <?php echo $lang['global-details']; ?>
 </div>
 <div class='boxcontent'>
  <table class='np-table'>
   <tr>
    <td class="biggerFont"><?php echo $lang['global-amount']; ?> <input type='text' lang="nb" class="fourDigit defaultinput-no-margin-smallborder floatright" id="purchaseQuantity" name="purchaseQuantity" placeholder="u" /></td>
    <td><?php echo $lang['provider']; ?> <select name='provider' class='defaultinput-no-margin-smallborder floatright'>
      <option value='<?php echo $provider; ?>'><?php echo $providerName; ?></option>
<?php
	$PRquery = "SELECT id, name FROM providers WHERE id <> $provider";
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
     </select>
    </td>
   </tr>

   <tr>
    <td class="biggerFont left"><label for='inMenu'><?php echo $lang['add-showinmenu']; ?>?</label> <input type="checkbox" name="inMenu" id="inMenu" style="width: 12px; margin-left: 30px;" value="1" <?php if ($inMenu == 1) { echo "checked"; } ?> /></td>
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
    <td class="left"><?php echo $lang['add-perunit']; ?><input type='text' lang='nb' class="fourDigit defaultinput-no-margin-smallborder floatright" id='purchaseppg' name='purchaseppg' placeholder="&euro;" value="<?php echo $purchasePrice; ?>" /></td>
   </tr>
   <tr>
    <td class="left"><?php echo $lang['add-total']; ?> <input type='text' lang="nb" id="purchasePrice" name="purchasePrice" class="fourDigit defaultinput-no-margin-smallborder floatright" placeholder="&euro;" value="<?php echo $purchasePriceTotal; ?>" /></td>
   </tr>
   <tr>
    <td class="left"><?php echo $lang['paid']; ?>: <input type='text' lang="nb" id="paidNow" name="paidNow" class="fourDigit defaultinput-no-margin-smallborder floatright" placeholder="&euro;" value="<?php echo $paidNow; ?>"  /></td>
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
    <td class="left"><?php echo $lang['add-perunit']; ?> <input type='text' lang='nb' class="fourDigit defaultinput-no-margin-smallborder floatright" placeholder="&euro;" id='salesppg' name='salesppg' value="<?php echo $salesPrice; ?>" /></td>
   </tr>
   <tr>
    <td class="left"><?php echo $lang['add-total']; ?> <input type='text' lang="nb" class="fourDigit defaultinput-no-margin-smallborder floatright" placeholder="&euro;" id="salesPrice" name="salesPrice" value="<?php echo $salesPriceTotal; ?>" /></td>
   </tr>
  </table>
</div>
</div>

  <!-- // KONSTANT CODE UPDATE BEGIN -->
<div class="actionbox-np2" style='height: 268px;'>
 <div class='mainboxheader'>
 <?php echo $lang['volume-discounts']; ?>
 </div>
  <table>
<?php 
    $volumeDiscounts = "SELECT * FROM volume_discounts WHERE purchaseid = $purchaseid";
    $result = $pdo3->prepare("$volumeDiscounts");
    $result->execute();
    while ($rs = $result->fetch()) { ?>
    <tr>
        <td><?php echo $lang['units-grams']; ?></td>
        <td><input type='number' lang='nb' class='fourDigit' name='volume_unit[]' value="<?php echo $rs['units'];?>" /></td>
        <td><?php echo $lang['add-total']; ?></td>
        <td><input type="number" lang="nb" class="fourDigit" name="volume_unit_price[]" value="<?php echo $rs['amount'];?>" /> &euro;</td>
    </tr>
        
<?php } ?>      
   <tr id="volumeDiv">
    <td><?php echo $lang['units-grams']; ?></td>
    <td><input type='number' lang='nb' class='fourDigit' name='volume_unit[]' /></td>
    <td><?php echo $lang['add-total']; ?></td>
    <td><input type="number" lang="nb" class="fourDigit" name="volume_unit_price[]" /> &euro;</td>
   <br>
   </tr>
    <tr>
       <td colspan="4">
           <button type="button" onclick="addMoreDiscount()" id="addMoreDiscountButton"><?php echo $lang['add-more']; ?></button> 
       </td>
    </tr>
  </table>
   </div>
  <br />
  <!-- // KONSTANT CODE UPDATE END -->

<div class="actionbox-np2">
 <div class='mainboxheader'>
 <?php echo $lang['add-movements']; ?>
 </div>
 <div class='boxcontent'>
  <table class='np-table'>
   <tr>
    <td class="left"><?php echo $lang['add-sampletaste']; ?> <input type='text' lang='nb' class="fourDigit defaultinput-no-margin-smallborder floatright" placeholder="u" name='sample' value='<?php echo $sample; ?>' /></td>
    <td class="left"><?php echo $lang['add-displayjar']; ?> <input type='text' lang='nb' class="fourDigit defaultinput-no-margin-smallborder floatright" placeholder="u" name='displayjar' value='<?php echo $displayjar; ?>' /></td>
   </tr>
   <tr>
    <td class="left"><?php echo $lang['closeday-intstash']; ?> <input type='text' lang='nb' class="fourDigit defaultinput-no-margin-smallborder floatright" placeholder="u" name='intstash' value='<?php echo $intstash; ?>' /></td>
    <td class="left"><?php echo $lang['closeday-extstash']; ?> <input type='text' lang='nb' class="fourDigit defaultinput-no-margin-smallborder floatright" placeholder="u" name='extstash' value='<?php echo $extstash; ?>' /></td>
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
