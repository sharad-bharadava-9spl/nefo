<?php 
	require_once 'cOnfig/connection.php';
	require_once 'cOnfig/languages/common.php';
	require_once 'applyMaxDiscount.php';
	session_start();
	$accessLevel = '3';
	
	getSettings();
	
	$domain = $_SESSION['domain'];
	$menuType = $_SESSION['menuType'];
	$usageType = $_GET['usageType'];
	$i = $_GET['i'];
	$categoryid = $_GET['cat'];
	$type = $_GET['type'];
	$user_id = $_GET['uid'];	
	$discount = $_GET['discount'];
	if ($_SESSION['menusortdisp'] == 0) {
		$sorting = "p.salesPrice ASC";
	} else {
		$sorting = "pr.name ASC";
	}
	
	if ($_SESSION['domain'] == 'faded') {
		
	// For each cat, look up products
  	$selectProduct = "SELECT pr.productid, pr.name, pr.description, pr.medicaldescription, p.purchaseid, p.salesPrice, p.photoExt, p.realQuantity, p.medDiscount FROM products pr, purchases p WHERE p.category = $categoryid AND p.productid = pr.productid AND p.closedAt IS NULL AND inMenu = 1 ORDER BY $sorting";
		try
		{
			$resultProduct = $pdo3->prepare("$selectProduct");
			$resultProduct->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
		
		if ($menuType == 1) {
			echo "<table class='default productlist nonhover'><tbody>";
		}
		
	// Look up discount for THIS category
	$selectCategoryDiscount = "SELECT discount FROM catdiscounts WHERE user_id = $user_id AND categoryid = $categoryid";
		try
		{
			$result = $pdo3->prepare("$selectCategoryDiscount");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$rowCD = $result->fetch();
		$catDiscount = $rowCD['discount'];
		
		$medicalDiscount = $_SESSION['medicalDiscount'];
		$medicalDiscountPercentage = $_SESSION['medicalDiscountPercentage'];
		
		if ($medicalDiscountPercentage == 1) {
			$medicalDiscountCalc = 1 - ($medicalDiscount / 100);
		}
		
	while ($product = $resultProduct->fetch()) {
		
	// ************************************** Calculate discount(s) ****************************************
	
	$purchaseid = $product['purchaseid'];
	
	if ($_SESSION['showOrigPrice'] == 1) {
		
		$normalPrice = "{$product['salesPrice']} {$_SESSION['currencyoperator']}";
		
	} else {
	
		$normalPrice = "";
		
	}
	
        // KONSTANT CODE UPDATE BEGIN
			$dispDiscount1 = 0;
	        $discountType = 0;
			$getMaxDisocunt = checkMaxDisocunt($user_id, $purchaseid, $categoryid, $product['salesPrice'], 0, 0, $offsetSec);
			if($getMaxDisocunt){
				$getMaxDisocunt = explode("_", $getMaxDisocunt);
				$dispDiscount1 = $getMaxDisocunt[0];
				$discountType = $getMaxDisocunt[1];
				$applyDisc = (100 - $dispDiscount1) / 100;
				$price = number_format($product['salesPrice'] * $applyDisc,2);
			}else{
				$price = number_format($product['salesPrice'],2);
			}
        // KONSTANT CODE UPDATE END
	
	 		$i++;
	 		
	 		
			$productid = $product['productid'];
			$productName = $product['name'];
	    // KONSTANT CODE UPDATE BEGIN	
		// Fetch saved data
		  $selectSavedData = "SELECT grams, euro, realGrams, grams2 FROM savesales_details WHERE user_id = $user_id AND purchase_id = $purchaseid";
			try
			{
				$result = $pdo3->prepare("$selectSavedData");
				$result->execute();
			}
			catch (PDOException $e)
			{
					$error = 'Error fetching user: ' . $e->getMessage();
					echo $error;
					exit();
			}
        $rowSCount = $result->rowCount();
     
			$rowS = $result->fetch();
			if($rowS['grams'] != ''){
				$savedGrams[$i] = $rowS['grams'];
			}else{
				$savedGrams[$i] = '';
			}
			if ($rowS['euro'] != '') {
				$savedEuro[$i] = $rowS['euro'];
			} else {
				$savedEuro[$i] = '';
			}
			if ($rowS['realGrams'] != '') {
				$savedrealGrams[$i] = $rowS['realGrams'];
			} else {
				$savedrealGrams[$i] = '';
			}
	    
			if ($rowS['grams2'] != '0.00' && $rowS['grams2'] != '') {
				$savedGrams2[$i] = $rowS['grams2'];
				$discountType = 7;
			    $displayGift = '';
				$giftOption = "<input type='hidden' value='%d' />";
				echo "<script>$('#grcalcB$i').css('display', 'inline-block');</script>";
			}
		
		else{
		
			$savedGrams2[$i] = '';
		    $displayGift = 'display: none;';
				
				if ($_SESSION['dispensaryGift'] == 1 && $_SESSION['menuType'] == 1) {
					$giftOption = "<img src='images/gift-small.png' id='free%d' style='margin-bottom: -15px; margin-left: 5px;' />";
				} else if ($_SESSION['dispensaryGift'] == 1 && $_SESSION['menuType'] == 0) {
					if ($_SESSION['realWeight'] == 0) {
						$giftOption = "<img src='images/gift-big.png' class='giftIcon' id='free%d'  />";
					} else {
						$giftOption = "<img src='images/gift-small.png' class='giftIcon' id='free%d'  />";
					}
				} else {
					$giftOption = "<span style='display: block; height: 10px;'></span><input type='hidden' value='%d' />";
				}
		} 
	
    // KONSTANT CODE UPDATE END
	if ($product['description'] != '') {
		
		$commentRead = "
		                <img src='images/info-normal.png' style='inline-block; margin-top: 5px; margin-left: 5px;' id='comment$i' onClick='showhide$i()' /><div id='helpBox$i' class='helpBox'><strong>{$lang['extracts-description']}:</strong><br />{$product['description']}</div>
		                <script>
		                  	$('#comment$i').on({
						 		'mouseover' : function() {
								 	$('#helpBox$i').css('display', 'block');
						  		},
						  		'mouseout' : function() {
								 	$('#helpBox$i').css('display', 'none');
							  	}
						  	});
						  	function showhide$i() {
							    var x = document.getElementById('helpBox$i');
							    if (x.style.display === 'none') {
							        x.style.display = 'block';
							    } else {
							        x.style.display = 'none';
							    }
							} 
						</script>
		                ";
		
	} else {
		
		$commentRead = "<img src='images/info-normal.png' style='visibility: hidden;' />";
		
	}		
	if ($product['medicaldescription'] != '') {
		
		$commentReadM = "
		                <img src='images/med.png' style='inline-block; margin-top: 5px; margin-left: 5px;' id='commentM$i' onClick='showhideM$i()' /><div id='helpBoxM$i' class='helpBox'><strong>{$lang['extracts-medicaldesc']}:</strong><br />{$product['medicaldescription']}</div>
		                <script>
		                  	$('#commentM$i').on({
						 		'mouseover' : function() {
								 	$('#helpBoxM$i').css('display', 'block');
						  		},
						  		'mouseout' : function() {
								 	$('#helpBoxM$i').css('display', 'none');
							  	}
						  	});
						  	function showhideM$i() {
							    var x = document.getElementById('helpBoxM$i');
							    if (x.style.display === 'none') {
							        x.style.display = 'block';
							    } else {
							        x.style.display = 'none';
							    }
							} 
						</script>
		                ";
		
	} else {
		
		$commentReadM = "<img src='images/med.png' style='visibility: hidden;' />";
		
	}		
		if ($_SESSION['showStock'] == 1) {
			
			$selectSales = "SELECT SUM(quantity) FROM salesdetails WHERE purchaseid = $purchaseid";
		
			try
			{
				$result = $pdo3->prepare("$selectSales");
				$result->execute();
			}
			catch (PDOException $e)
			{
					$error = 'Error fetching user: ' . $e->getMessage();
					echo $error;
					exit();
			}
		
			$row = $result->fetch();
				$sales = $row['SUM(quantity)'];
	
			$selectPermAdditions = "SELECT SUM(quantity) FROM productmovements WHERE purchaseid = $purchaseid AND type = 1 AND (movementTypeid = 1 OR movementTypeid = 3 OR movementTypeid = 10)";
			try
			{
				$result = $pdo3->prepare("$selectPermAdditions");
				$result->execute();
			}
			catch (PDOException $e)
			{
					$error = 'Error fetching user: ' . $e->getMessage();
					echo $error;
					exit();
			}
		
			$row = $result->fetch();
				$permAdditions = $row['SUM(quantity)'];
			
			$selectPermRemovals = "SELECT SUM(quantity) FROM productmovements WHERE purchaseid = $purchaseid AND type = 2 AND (movementTypeid = 4 OR movementTypeid = 7 OR movementTypeid = 8 OR movementTypeid = 9 OR movementTypeid = 11 OR movementTypeid = 13 OR movementTypeid = 14 OR movementTypeid = 15 OR movementTypeid = 16)";
			
			try
			{
				$result = $pdo3->prepare("$selectPermRemovals");
				$result->execute();
			}
			catch (PDOException $e)
			{
					$error = 'Error fetching user: ' . $e->getMessage();
					echo $error;
					exit();
			}
		
			$row = $result->fetch();
				$permRemovals = $row['SUM(quantity)'];
					
			// Calculate what's in Internal stash
			$selectStashedInt = "SELECT SUM(quantity) FROM productmovements WHERE purchaseid = $purchaseid AND type = 2 AND (movementTypeid = 5 OR movementTypeid = 18)";
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
				$stashedInt = $row['SUM(quantity)'];
				
			$selectUnStashedInt = "SELECT SUM(quantity) FROM productmovements WHERE purchaseid = $purchaseid AND type = 1 AND (movementTypeid = 12 OR movementTypeid = 17)";
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
				$unStashedInt = $row['SUM(quantity)'];
		
					
				$inStashInt = $stashedInt - $unStashedInt;
				$inStashInt = $inStashInt;
		
		
			// Calculate what's in External stash
			$selectStashedExt = "SELECT SUM(quantity) FROM productmovements WHERE purchaseid = $purchaseid AND type = 2 AND (movementTypeid = 6 OR movementTypeid = 20)";
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
				$stashedExt = $row['SUM(quantity)'];
				
			$selectUnStashedExt = "SELECT SUM(quantity) FROM productmovements WHERE purchaseid = $purchaseid AND type = 1 AND (movementTypeid = 2 OR movementTypeid = 19)";
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
				$unStashedExt = $row['SUM(quantity)'];
	
				
			$inStashExt = $stashedExt - $unStashedExt;
			$inStashExt = $inStashExt;
			
			$inStash = $inStashInt + $inStashExt;
			$estStock = $product['realQuantity'] + $permAdditions - $sales - $permRemovals - $inStash;
			
			$estStock = number_format($estStock,1);
			
			$stockDisplay = "<center>$estStock g</center>";
		}
	if($product['photoExt'] != ''){
	   	  $productImg = "images/_$domain/purchases/".$product['purchaseid'].".".$product['photoExt'];
	   }else{
	   		$productImg = "images/icon-leaf-g.png";
			
	   }
	 $trclass = '';
	  if($i%2==0){
	  	 $trclass = "evencolor";
	  }
	// Unit cat first	
	if ($type == 0) {
				
	if ($menuType == 0) {
	 		
	
	$product_row =	sprintf("
<script>
    $(document).ready(function() {
   function compute() {
	   	  $(this).val($(this).val().replace(/,/g, '.'));
          var a = $('#ppgcalc%d').val();
          var b = $('#grcalc%d').val();
          if (b != 0 || b != '') {
          var total = a * b;
          var roundedtotal = total.toFixed(2);
          	$('#eurcalc%d').val(roundedtotal);
                // KONSTANT CODE UPDATE BEGIN
		        var discountAmount = $('#discount_%d_amount').delay(700).val();
		        if(discountAmount != '' && discountAmount != 0){
		          var roundedtotal = parseFloat(discountAmount).toFixed(2);
		          $('#eurcalc%d').val(roundedtotal);
		        }
                // KONSTANT CODE UPDATE END
                
      	  } else {
          	$('#eurcalc%d').val('');
          	$('#grcalc%d').val('');
      	  }
        }
   function compute2() {
	   	  $(this).val($(this).val().replace(/,/g, '.'));
          var a = $('#eurcalc%d').val();
          var b = $('#ppgcalc%d').val();
          if (a != 0 || a != '') {
          var total = a / b;
          var roundedtotal = total.toFixed(2);
          	$('#grcalc%d').val(roundedtotal);
                // KONSTANT CODE UPDATE BEGIN
	            var discount_quant = $('#discount_%d_quantity').delay(700).val();
	            if(discount_quant != '' && discount_quant != 0){
	              var roundedtotal = parseFloat(discount_quant).toFixed(2);
	              $('#grcalc%d').val(roundedtotal);
	            }
                // KONSTANT CODE UPDATE END
                
      	  } else {
          	$('#eurcalc%d').val('');
          	$('#grcalc%d').val('');
      	  }
        }
        
   function compute3() {
	   	  $(this).val($(this).val().replace(/,/g, '.'));
        }
        // KONSTANT CODE UPDATE BEGIN
        function applyVolumeDiscount(){
            var a = $('#ppgcalc%d').val();
            var b = $('#grcalc%d').val();
            var user_id = '{$user_id}';
            if (b != 0 || b != '') {
                var purId = $('#sales_%d_purchaseid').val();
                $.ajax({
                    type:'get',
                    url:'get-volume-discount.php?id='+purId+'&qty='+b+'&uid='+user_id,
                    dataType:'json',
                    success:function(data)
                    {
	                    if(data != null){
	                    	var firstprice = data.price;
	                    	var discountPercent = data.discount_per;
	                    	var discountType = data.discount_type;
	                        var roundedtotal = parseFloat(firstprice).toFixed(2);
	                        $('#discount_%d_type').val(discountType);
	                        $('#discount_%d_percent').val(discountPercent);
	                        $('#discount_%d_amount').val(roundedtotal);
	                  	    $('#eurcalc%d').val(roundedtotal);
	                    }else{
	                       	$('#discount_%d_type').val('');
	                        $('#discount_%d_percent').val('');
	                        $('#discount_%d_amount').val('');
	                    }
                    }
                }); 
            }else{
               	$('#discount_%d_type').val('');
                $('#discount_%d_percent').val('');
                $('#discount_%d_amount').val('');
            }
        }
	    function applyVolumeDiscountAmount(){
	        var a = $('#ppgcalc%d').val();
	        var b = $('#eurcalc%d').val();
	        var user_id = '{$user_id}';
	        if (b != 0 || b != '') {
	            var purId = $('#sales_%d_purchaseid').val();
	            $.ajax({
	                type:'get',
	                url:'get-volume-discount-amount.php?id='+purId+'&amount='+b+'&uid='+user_id,
	                dataType:'json',
	                success:function(data)
	                {
	                    if(data != null){
	                    	var firstquant = data.quantity;
	                    	var discountPercent = data.discount_per;
	                    	var discountType = data.discount_type;
	                        var roundedtotal = parseFloat(firstquant).toFixed(2);
	                        $('#discount_%d_type').val(discountType);
	                        $('#discount_%d_percent').val(discountPercent);
	                        $('#discount_%d_quantity').val(roundedtotal);
	                  	    $('#grcalc%d').val(roundedtotal);
	                    }else{
	                    	$('#discount_%d_type').val('');
	                        $('#discount_%d_percent').val('');
	                        $('#discount_%d_quantity').val('');
	                    }
	                }
	            }); 
	        }else{
		        $('#discount_%d_type').val('');
                $('#discount_%d_percent').val('');
                $('#discount_%d_quantity').val('');
	        }
	    }
        $('#grcalc%d').bind('keyup blur change', applyVolumeDiscount);
        $('#eurcalc%d').bind('keyup blur change', applyVolumeDiscountAmount);
        // KONSTANT CODE UPDATE END
        $('#grcalc%d').bind('keypress keyup blur change', compute);
        $('#eurcalc%d').bind('keypress keyup blur change', compute2);
        $('#grcalcB%d').bind('keypress keyup blur change', compute3);
        
		$('#free%d').click(function () {
		$('#grcalcB%d').css('display', 'inline-block');
		$('#free%d').css('display', 'none');
		});	
  }); // end ready
        </script>
     <div class = 'sdisplaybox '>   
	<div class='sameHeight_box '>
	 <div class='imageholder'>
	  <img src='$productImg' />
	 </div>
	 <h3>%s</h3>
	 
	 <table style='width: 210px;'>
	  <tr>
	   <td style='width: 80px; padding-left: 5px; vertical-align: top; text-align: left;'>
	   </td>
	   <td style='width: 125px; text-align: right; vertical-align: top;'>
	    <input class='specialInput' id='ppgcalc%d' name='sales[%d][ppg]' value='%0.02f' readonly /><span class='eurosign'>{$_SESSION['currencyoperator']}</span><br />
	    <span class='discountprice'>$normalPrice</span>
	   </td>
	  </tr>
	 </table>
	 <table style='width: 210px; height: 60px;'>
	  <tr>
	   <td style='vertical-align: top; text-align: left;'>
	   <span class='relativeitem'> $commentRead $commentReadM </span>
	   </td>
	   <td style='width: 85px; text-align: right; vertical-align: top; color: #606f5c; font-weight: 600; padding-top: 13px;'>
	    $stockDisplay
	   </td>
	  </tr>
	 </table>
	 <table class ='priceInput' style='width: 210px; background-color: #c7d6c1; border-radius: 5px;'>
	  <tr>
	   <td style='vertical-align: top; text-align: left; margin-left: 10px;'>
  	    <input type='text' lang='nb' class='dispinput centered calc3 onScreenKeypad tst c$purchaseid' id='grcalc%d' name='sales[%d][grams]' placeholder='#' step='1' value='%s' style='margin-left: 10px;' />
	   </td>
	   <td style='vertical-align: top; text-align: right;'>
  	    <input type='text' lang='nb' class='dispinput centered calc2 onScreenKeypad tst' id='eurcalc%d' name='sales[%d][euro]' placeholder='{$_SESSION['currencyoperator']}' step='0.01' value='%s' style='margin-right: 10px;'/>
	   </td>
	  </tr>
	  <tr>
	   <td>
  	    <input type='text' lang='nb' class='dispinput centered calc4 onScreenKeypad tst' id='grcalcB%d' name='sales[%d][grams2]' placeholder='#' step='0.01' value='%s' style='background-color: #f4b146; display: none; margin-left: 6px; margin-bottom: 10px;'  /> $giftOption
  	   </td>
  	   <td>
  	    <input type='hidden' name='sales[%d][name]' value='%s' />
          <!-- // KONSTANT CODE UPDATE BEGIN -->
  	  <input type='hidden' name='sales[%d][purchaseid]' value='%d' id='sales_%d_purchaseid' />
  	  <input type='hidden' name='sales[%d][category]' value='%d' id='sales_%d_category' />
  	  <input type='hidden' name='sales[%d][productid]' value='%d' id='sales_%d_productid' />
  	  <input type='hidden' name='sales[%d][discType]' id='discount_%d_type' value='%d'/>
  	  <input type='hidden' name='sales[%d][discPercentage]' id='discount_%d_percent' value='%0.02f'/>
  	  <input type='hidden' name='sales[%d][discAmount]' id='discount_%d_amount' />
  	  <input type='hidden' name='sales[%d][discQuant]' id='discount_%d_quantity' />
          <!-- // KONSTANT CODE UPDATE END -->
  	   </td>
  	  </tr>
	 </table>
	</div></div>", //KONSTANT CODE UPDATE BEGIN 
	  $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $product['name'], $i, $i, $price, $i, $i, $savedGrams[$i], $i, $i, $savedEuro[$i], $i, $i, $savedGrams2[$i], $i, $i, $product['name'], $i, $product['purchaseid'], $i, $i, $categoryid, $i, $i, $product['productid'], $i, $i, $i, $discountType ,$i, $i, $dispDiscount1, $i, $i, $i, $i
	  );
// KONSTANT CODE UPDATE END
        
  } else {
	 
		if ($_SESSION['showStock'] == 1) {
			$stockDisplay = "<img src='images/box-small.png' style='margin-bottom: -3px; margin-right: 5px;' />$estStock u";
		}
		
	$product_row =	sprintf("
<script>
    $(document).ready(function() {
   function compute() {
	   	  $(this).val($(this).val().replace(/,/g, '.'));
          var a = $('#ppgcalc%d').val();
          var b = $('#grcalc%d').val();
          if (b != 0 || b != '') {
          var total = a * b;
          var roundedtotal = total.toFixed(2);
          	$('#eurcalc%d').val(roundedtotal);
                // KONSTANT CODE UPDATE BEGIN
		        var discountAmount = $('#discount_%d_amount').delay(700).val();
		        if(discountAmount != '' && discountAmount != 0){
		          var roundedtotal = parseFloat(discountAmount).toFixed(2);
		          $('#eurcalc%d').val(roundedtotal);
		        }
                // KONSTANT CODE UPDATE END
                
      	  } else {
          	$('#eurcalc%d').val('');
          	$('#grcalc%d').val('');
      	  }
        }
   function compute2() {
	   	  $(this).val($(this).val().replace(/,/g, '.'));
          var a = $('#eurcalc%d').val();
          var b = $('#ppgcalc%d').val();
          if (a != 0 || a != '') {
          var total = a / b;
          var roundedtotal = total.toFixed(2);
          	$('#grcalc%d').val(roundedtotal);
                // KONSTANT CODE UPDATE BEGIN
	            var discount_quant = $('#discount_%d_quantity').delay(700).val();
	            if(discount_quant != '' && discount_quant != 0){
	              var roundedtotal = parseFloat(discount_quant).toFixed(2);
	              $('#grcalc%d').val(roundedtotal);
	            }
                // KONSTANT CODE UPDATE END
      	  } else {
          	$('#eurcalc%d').val('');
          	$('#grcalc%d').val('');
      	  }
        }
        
   function compute3() {
	   	  $(this).val($(this).val().replace(/,/g, '.'));
        }
        // KONSTANT CODE UPDATE BEGIN
        function applyVolumeDiscount(){
            var a = $('#ppgcalc%d').val();
            var b = $('#grcalc%d').val();
            var user_id = '{$user_id}';
            if (b != 0 || b != '') {
                var purId = $('#sales_%d_purchaseid').val();
                $.ajax({
                    type:'get',
                    url:'get-volume-discount.php?id='+purId+'&qty='+b+'&uid='+user_id,
                    dataType:'json',
                    success:function(data)
                    {
	                 if(data != null){
	                    	var firstprice = data.price;
	                    	var discountPercent = data.discount_per;
	                    	var discountType = data.discount_type;
	                        var roundedtotal = parseFloat(firstprice).toFixed(2);
	                        $('#discount_%d_type').val(discountType);
	                        $('#discount_%d_percent').val(discountPercent);
	                        $('#discount_%d_amount').val(roundedtotal);
	                  	    $('#eurcalc%d').val(roundedtotal);
	                    }else{
	                       	$('#discount_%d_type').val('');
	                        $('#discount_%d_percent').val('');
	                        $('#discount_%d_amount').val('');
	                    }
                    }
                }); 
            }else{
               	$('#discount_%d_type').val('');
                $('#discount_%d_percent').val('');
                $('#discount_%d_amount').val('');
            }
        }
	    function applyVolumeDiscountAmount(){
	        var a = $('#ppgcalc%d').val();
	        var b = $('#eurcalc%d').val();
	        var user_id = '{$user_id}';
	        if (b != 0 || b != '') {
	            var purId = $('#sales_%d_purchaseid').val();
	            $.ajax({
	                type:'get',
	                url:'get-volume-discount-amount.php?id='+purId+'&amount='+b+'&uid='+user_id,
	                dataType:'json',
	                success:function(data)
	                {
	                    if(data != null){
	                    	var firstquant = data.quantity;
	                    	var discountPercent = data.discount_per;
	                    	var discountType = data.discount_type;
	                        var roundedtotal = parseFloat(firstquant).toFixed(2);
	                        $('#discount_%d_type').val(discountType);
	                        $('#discount_%d_percent').val(discountPercent);
	                        $('#discount_%d_quantity').val(roundedtotal);
	                  	    $('#grcalc%d').val(roundedtotal);
	                    }else{
	                    	$('#discount_%d_type').val('');
	                        $('#discount_%d_percent').val('');
	                        $('#discount_%d_quantity').val('');
	                    }
	                }
	            }); 
	        }else{
		        $('#discount_%d_type').val('');
                $('#discount_%d_percent').val('');
                $('#discount_%d_quantity').val('');
	        }
	    }
        $('#grcalc%d').bind('keyup blur change', applyVolumeDiscount);
        $('#eurcalc%d').bind('keyup blur change', applyVolumeDiscountAmount);
        // KONSTANT CODE UPDATE END
        $('#grcalc%d').bind('keypress keyup blur change', compute);
        $('#eurcalc%d').bind('keypress keyup blur change', compute2);
        $('#grcalcB%d').bind('keypress keyup blur change', compute3);
        
		$('#free%d').click(function () {
		$('#grcalcB%d').css('display', 'inline-block');
		$('#free%d').css('display', 'none');
		});	
  }); // end ready
        </script>
        
	   <tr class='$trclass'>
	    <td class='left'><strong>%s</strong></td>
	    <td>
	        <span class='relativeitem'>$commentRead</span>&nbsp;
	    	<span class='relativeitem'>$commentReadM</span>&nbsp;&nbsp;&nbsp;
	    	<strong style='font-size: 15px;'>%0.02f {$_SESSION['currencyoperator']}</strong> <input type='hidden' id='ppgcalc%d' name='sales[%d][ppg]' value='%0.02f' />&nbsp;&nbsp;
	    	<span style='font-size: 15px;' class='firsttext'>$normalPrice</span>&nbsp;&nbsp;&nbsp;	
	    	<span class='smallgreen' style='text-transform: none;'>$stockDisplay</span><br>
	    	<input type='text' lang='nb' class='twoDigit defaultinput centered calc3 onScreenKeypad c$purchaseid'  id='grcalc%d' name='sales[%d][grams]' placeholder='#' step='0.01' value='%s' />
	    	<input type='text' lang='nb' class='twoDigit defaultinput centered calc2 onScreenKeypad' id='eurcalc%d' name='sales[%d][euro]' placeholder='{$_SESSION['currencyoperator']}' step='0.01' value='%s' />
	    	<input type='text' lang='nb' class='twoDigit defaultinput centered calc4 onScreenKeypad' id='grcalcB%d' name='sales[%d][grams2]' placeholder='#' step='0.01' value='%s' style=' background-color: #f4b146; $displayGift'  />$giftOption
		    </td>
	   </tr>
  	  <input type='hidden' name='sales[%d][name]' value='%s' />
          <!-- // KONSTANT CODE UPDATE BEGIN -->
  	  <input type='hidden' name='sales[%d][purchaseid]' value='%d' id='sales_%d_purchaseid' />
  	  <input type='hidden' name='sales[%d][category]' value='%d' id='sales_%d_category' />
  	  <input type='hidden' name='sales[%d][productid]' value='%d' id='sales_%d_productid' />
  	  <input type='hidden' name='sales[%d][discType]'  id='discount_%d_type' value='%d'/>
  	  <input type='hidden' name='sales[%d][discPercentage]' id='discount_%d_percent' value='%0.02f'/>
  	  <input type='hidden' name='sales[%d][discAmount]' id='discount_%d_amount'/>
  	  <input type='hidden' name='sales[%d][discQuant]' id='discount_%d_quantity'/>
          <!-- // KONSTANT CODE UPDATE END -->
",  //KONSTANT CODE UPDATE BEGIN 
	  $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i ,$i ,$i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $product['name'], $price, $i, $i, $price, $i, $i, $savedGrams[$i], $i, $i, $savedEuro[$i], $i, $i, $savedGrams2[$i],$i, $i, $product['name'], $i, $product['purchaseid'], $i, $i, $categoryid, $i, $i, $product['productid'], $i, $i, $i, $discountType ,$i, $i, $dispDiscount1, $i, $i, $i, $i
	  );
// KONSTANT CODE UPDATE END
        
  }
	  echo $product_row;
	  
  		} else { // Now Gram cat
if ($_SESSION['realWeight'] == 0) {
	
	if ($menuType == 0) {
	 		
		if ($_SESSION['showStock'] == 1) {
			$stockDisplay = "<center>$estStock g</center>";
		}
	
	$product_row =	sprintf("
<script>
    $(document).ready(function() {
   function compute() {
	   	  $(this).val($(this).val().replace(/,/g, '.'));
	   	  $(this).val($(this).val().replace(' ', ''));
	   	  $(this).val($(this).val().replace('g', ''));
          var a = $('#ppgcalc%d').val();
          var b = $('#grcalc%d').val();
          if (b != 0 || b != '') {
          var total = a * b;
          var roundedtotal = total.toFixed(2);
          	$('#eurcalc%d').val(roundedtotal);
                // KONSTANT CODE UPDATE BEGIN
		        var discountAmount = $('#discount_%d_amount').delay(700).val();
		        if(discountAmount != '' && discountAmount != 0){
		          var roundedtotal = parseFloat(discountAmount).toFixed(2);
		          $('#eurcalc%d').val(roundedtotal);
		        }
                // KONSTANT CODE UPDATE END
      	  } else {
          	$('#eurcalc%d').val('');
          	$('#grcalc%d').val('');
      	  }
        }
   function compute2() {
	   	  $(this).val($(this).val().replace(/,/g, '.'));
          var a = $('#eurcalc%d').val();
          var b = $('#ppgcalc%d').val();
          if (a != 0 || a != '') {
          var total = a / b;
          var roundedtotal = total.toFixed(2);
          	$('#grcalc%d').val(roundedtotal);
                // KONSTANT CODE UPDATE BEGIN
	            var discount_quant = $('#discount_%d_quantity').delay(700).val();
	            if(discount_quant != '' && discount_quant != 0){
	              var roundedtotal = parseFloat(discount_quant).toFixed(2);
	              $('#grcalc%d').val(roundedtotal);
	            }
                // KONSTANT CODE UPDATE END
      	  } else {
          	$('#eurcalc%d').val('');
          	$('#grcalc%d').val('');
      	  }
        }
        
   function compute3() {
	   	  $(this).val($(this).val().replace(/,/g, '.'));
        }
        // KONSTANT CODE UPDATE BEGIN
        function applyVolumeDiscount(){
            var a = $('#ppgcalc%d').val();
            var b = $('#grcalc%d').val();
            var user_id = '{$user_id}';
            if (b != 0 || b != '') {
                var purId = $('#sales_%d_purchaseid').val();
                $.ajax({
                    type:'get',
                    url:'get-volume-discount.php?id='+purId+'&qty='+b+'&uid='+user_id,
                    dataType:'json',
                    success:function(data)
                    {
	  					if(data != null){
	                    	var firstprice = data.price;
	                    	var discountPercent = data.discount_per;
	                    	var discountType = data.discount_type;
	                        var roundedtotal = parseFloat(firstprice).toFixed(2);
	                        $('#discount_%d_type').val(discountType);
	                        $('#discount_%d_percent').val(discountPercent);
	                        $('#discount_%d_amount').val(roundedtotal);
	                  	    $('#eurcalc%d').val(roundedtotal);
	                    }else{
	                       	$('#discount_%d_type').val('');
	                        $('#discount_%d_percent').val('');
	                        $('#discount_%d_amount').val('');
	                    }
                    }
                }); 
            }else{
               	$('#discount_%d_type').val('');
                $('#discount_%d_percent').val('');
                $('#discount_%d_amount').val('');
            }
        }
	    function applyVolumeDiscountAmount(){
	        var a = $('#ppgcalc%d').val();
	        var b = $('#eurcalc%d').val();
	        var user_id = '{$user_id}';
	        if (b != 0 || b != '') {
	            var purId = $('#sales_%d_purchaseid').val();
	            $.ajax({
	                type:'get',
	                url:'get-volume-discount-amount.php?id='+purId+'&amount='+b+'&uid='+user_id,
	                dataType:'json',
	                success:function(data)
	                {
	                    if(data != null){
	                    	var firstquant = data.quantity;
	                    	var discountPercent = data.discount_per;
	                    	var discountType = data.discount_type;
	                        var roundedtotal = parseFloat(firstquant).toFixed(2);
	                        $('#discount_%d_type').val(discountType);
	                        $('#discount_%d_percent').val(discountPercent);
	                        $('#discount_%d_quantity').val(roundedtotal);
	                  	    $('#grcalc%d').val(roundedtotal);
	                    }else{
	                    	$('#discount_%d_type').val('');
	                        $('#discount_%d_percent').val('');
	                        $('#discount_%d_quantity').val('');
	                    }
	                }
	            }); 
	        }else{
		        $('#discount_%d_type').val('');
                $('#discount_%d_percent').val('');
                $('#discount_%d_quantity').val('');
	        }
	    }
        $('#grcalc%d').bind('keyup blur change', applyVolumeDiscount);
        $('#eurcalc%d').bind('keyup blur change', applyVolumeDiscountAmount);
        // KONSTANT CODE UPDATE END
        $('#grcalc%d').bind('keypress keyup blur change', compute);
        $('#eurcalc%d').bind('keypress keyup blur change', compute2);
        $('#grcalcB%d').bind('keypress keyup blur change', compute3);
        
		$('#free%d').click(function () {
		$('#grcalcB%d').css('display', 'inline-block');
		$('#free%d').css('display', 'none');
		});	
  }); // end ready
        </script>
	<div class='displaybox'>
	<div class='sameHeight_box'>
	 <div class='imageholder'>
	  <img src='$productImg' />
	 </div>
	 <h3>%s</h3>
	 
	 <table style='width: 210px;'>
	  <tr>
	   <td style='width: 80px; padding-left: 5px; vertical-align: top; text-align: left;'>
	   </td>
	   <td style='width: 125px; text-align: right; vertical-align: top;'>
	    <input class='specialInput' id='ppgcalc%d' name='sales[%d][ppg]' value='%0.02f' readonly /><span class='eurosign'>{$_SESSION['currencyoperator']}</span><br />
	    <span class='discountprice'>$normalPrice</span>
	   </td>
	  </tr>
	 </table>
	 <table style='width: 210px; height: 60px;'>
	  <tr>
	   <td style='vertical-align: top; text-align: left;'>
	    <span class='relativeitem'>$commentRead $commentReadM </span>
	   </td>
	   <td style='width: 85px; text-align: right; vertical-align: top; color: #606f5c; font-weight: 600; padding-top: 13px;'>
	    $stockDisplay
	   </td>
	  </tr>
	 </table>
	 <table class ='priceInput' style='width: 210px; background-color: #c7d6c1; border-radius: 5px;'>
	  <tr>
	   <td style='vertical-align: top; text-align: left; margin-left: 10px;'>
  	    <input type='text' lang='nb' class='dispinput centered calc onScreenKeypad tst c$purchaseid' id='grcalc%d' name='sales[%d][grams]' placeholder='Gr.' step='0.01' value='%s' style='margin-left: 10px;' />
	   </td>
	   <td style='vertical-align: top; text-align: right;'>
  	    <input type='text' lang='nb' class='dispinput centered calc2 onScreenKeypad tst' id='eurcalc%d' name='sales[%d][euro]' placeholder='{$_SESSION['currencyoperator']}' step='0.01' value='%s' style='margin-right: 10px;'/>
	   </td>
	  </tr>
	  <tr>
	   <td>
  	    <input type='text' lang='nb' class='dispinput centered calc onScreenKeypad tst' id='grcalcB%d' name='sales[%d][grams2]' placeholder='Gr.' step='0.01' value='%s' style='background-color: #f4b146; display: none; margin-left: 6px; margin-bottom: 10px;'  /> $giftOption
  	   </td>
  	   <td>
  	    <input type='hidden' name='sales[%d][name]' value='%s' />
          <!-- // KONSTANT CODE UPDATE BEGIN -->
  	  <input type='hidden' name='sales[%d][purchaseid]' value='%d' id='sales_%d_purchaseid' />
  	  <input type='hidden' name='sales[%d][category]' value='%d' id='sales_%d_category' />
  	  <input type='hidden' name='sales[%d][productid]' value='%d' id='sales_%d_productid' />
  	  <input type='hidden' name='sales[%d][discType]' id='discount_%d_type' value='%d'/>
  	  <input type='hidden' name='sales[%d][discPercentage]'  id='discount_%d_percent' value='%0.02f'/>
  	  <input type='hidden' name='sales[%d][discAmount]' id='discount_%d_amount'/>
  	  <input type='hidden' name='sales[%d][discQuant]' id='discount_%d_quantity'/>
          <!-- // KONSTANT CODE UPDATE END -->
  	   </td>
  	  </tr>
	 </table>
	</div></div>", //KONSTANT CODE UPDATE BEGIN 
	   $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $product['name'], $i, $i, $price, $i, $i, $savedGrams[$i], $i, $i, $savedEuro[$i], $i, $i, $savedGrams2[$i],$i, $i, $product['name'], $i, $product['purchaseid'], $i, $i, $categoryid, $i, $i, $product['productid'], $i, $i, $i, $discountType ,$i, $i, $dispDiscount1, $i, $i, $i, $i
	  );
// KONSTANT CODE UPDATE END
        
	  echo $product_row;
	  
  } else {
	 
		if ($_SESSION['showStock'] == 1) {
			$stockDisplay = "<img src='images/box-small.png' style='margin-bottom: -3px; margin-right: 5px;' />$estStock g";
		}
		
	$product_row =	sprintf("
<script>
    $(document).ready(function() {
   function compute() {
	   	  $(this).val($(this).val().replace(/,/g, '.'));
	   	  $(this).val($(this).val().replace(' ', ''));
	   	  $(this).val($(this).val().replace('g', ''));
          var a = $('#ppgcalc%d').val();
          var b = $('#grcalc%d').val();
          if (b != 0 || b != '') {
          var total = a * b;
          var roundedtotal = total.toFixed(2);
          	$('#eurcalc%d').val(roundedtotal);
                // KONSTANT CODE UPDATE BEGIN
		        var discountAmount = $('#discount_%d_amount').delay(700).val();
		        if(discountAmount != '' && discountAmount != 0){
		          var roundedtotal = parseFloat(discountAmount).toFixed(2);
		          $('#eurcalc%d').val(roundedtotal);
		        }
                // KONSTANT CODE UPDATE END
      	  } else {
          	$('#eurcalc%d').val('');
          	$('#grcalc%d').val('');
      	  }
        }
   function compute2() {
	   	  $(this).val($(this).val().replace(/,/g, '.'));
          var a = $('#eurcalc%d').val();
          var b = $('#ppgcalc%d').val();
          if (a != 0 || a != '') {
          var total = a / b;
          var roundedtotal = total.toFixed(2);
          	$('#grcalc%d').val(roundedtotal);
                // KONSTANT CODE UPDATE BEGIN
	            var discount_quant = $('#discount_%d_quantity').delay(700).val();
	            if(discount_quant != '' && discount_quant != 0){
	              var roundedtotal = parseFloat(discount_quant).toFixed(2);
	              $('#grcalc%d').val(roundedtotal);
	            }
                // KONSTANT CODE UPDATE END
      	  } else {
          	$('#eurcalc%d').val('');
          	$('#grcalc%d').val('');
      	  }
        }
   function compute3() {
	   	  $(this).val($(this).val().replace(/,/g, '.'));
        }
        // KONSTANT CODE UPDATE BEGIN
        function applyVolumeDiscount(){
            var a = $('#ppgcalc%d').val();
            var b = $('#grcalc%d').val();
            var user_id = '{$user_id}';
            if (b != 0 || b != '') {
                var purId = $('#sales_%d_purchaseid').val();
                $.ajax({
                    type:'get',
                    url:'get-volume-discount.php?id='+purId+'&qty='+b+'&uid='+user_id,
                    dataType:'json',
                    success:function(data)
                    {
		                 if(data != null){
		                    	var firstprice = data.price;
		                    	var discountPercent = data.discount_per;
		                    	var discountType = data.discount_type;
		                        var roundedtotal = parseFloat(firstprice).toFixed(2);
		                        $('#discount_%d_type').val(discountType);
		                        $('#discount_%d_percent').val(discountPercent);
		                        $('#discount_%d_amount').val(roundedtotal);
		                  	    $('#eurcalc%d').val(roundedtotal);
		                    }else{
		                       	$('#discount_%d_type').val('');
		                        $('#discount_%d_percent').val('');
		                        $('#discount_%d_amount').val('');
		                    }
                    }
                }); 
            }else{
               	$('#discount_%d_type').val('');
                $('#discount_%d_percent').val('');
                $('#discount_%d_amount').val('');
            }
        }
	    function applyVolumeDiscountAmount(){
	        var a = $('#ppgcalc%d').val();
	        var b = $('#eurcalc%d').val();
	        var user_id = '{$user_id}';
	        if (b != 0 || b != '') {
	            var purId = $('#sales_%d_purchaseid').val();
	            $.ajax({
	                type:'get',
	                url:'get-volume-discount-amount.php?id='+purId+'&amount='+b+'&uid='+user_id,
	                dataType:'json',
	                success:function(data)
	                {
	                    if(data != null){
	                    	var firstquant = data.quantity;
	                    	var discountPercent = data.discount_per;
	                    	var discountType = data.discount_type;
	                        var roundedtotal = parseFloat(firstquant).toFixed(2);
	                        $('#discount_%d_type').val(discountType);
	                        $('#discount_%d_percent').val(discountPercent);
	                        $('#discount_%d_quantity').val(roundedtotal);
	                  	    $('#grcalc%d').val(roundedtotal);
	                    }else{
	                    	$('#discount_%d_type').val('');
	                        $('#discount_%d_percent').val('');
	                        $('#discount_%d_quantity').val('');
	                    }
	                }
	            }); 
	        }else{
		        $('#discount_%d_type').val('');
                $('#discount_%d_percent').val('');
                $('#discount_%d_quantity').val('');
	        }
	    }
        $('#grcalc%d').bind('keyup blur change', applyVolumeDiscount);
        $('#eurcalc%d').bind('keyup blur change', applyVolumeDiscountAmount);
        // KONSTANT CODE UPDATE END
        $('#grcalc%d').bind('keypress keyup blur change', compute);
        $('#eurcalc%d').bind('keypress keyup blur change', compute2);
        $('#grcalcB%d').bind('keypress keyup blur change', compute3);
        
		$('#free%d').click(function () {
		$('#grcalcB%d').css('display', 'inline-block');
		$('#free%d').css('display', 'none');
		});	
  }); // end ready
        </script>
        
	   <tr class='$trclass'>
	    <td class='left'><strong>%s</strong></td>
	    <td>
	    	<span class='relativeitem'>$commentRead</span>&nbsp;
	    	<span class='relativeitem'>$commentReadM</span>&nbsp;&nbsp;&nbsp;
	    	<strong style='font-size: 15px;'>%0.02f {$_SESSION['currencyoperator']}</strong> <input type='hidden' id='ppgcalc%d' name='sales[%d][ppg]' value='%0.02f' />&nbsp;&nbsp;
	    	<span style='font-size: 15px;' class='firsttext'>$normalPrice</span>&nbsp;&nbsp;&nbsp;	    
	    	<span class='smallgreen' style='text-transform: none;'>$stockDisplay</span><br>
	    	<input type='text' lang='nb' class='twoDigit defaultinput centered calc onScreenKeypad c$purchaseid' id='grcalc%d' name='sales[%d][grams]' placeholder='Gr.' step='0.01' value='%s' />
	    	<input type='text' lang='nb' class='twoDigit defaultinput centered calc2 onScreenKeypad'  id='eurcalc%d' name='sales[%d][euro]' placeholder='{$_SESSION['currencyoperator']}' step='0.01' value='%s' />
	    	<input type='text' lang='nb' class='twoDigit defaultinput centered calc onScreenKeypad' id='grcalcB%d' name='sales[%d][grams2]' placeholder='Gr.' step='0.01' value='%s' style='background-color: #f4b146; $displayGift'  />$giftOption
	    	</td>
	   </tr>
  	  <input type='hidden' name='sales[%d][name]' value='%s' />
          <!-- // KONSTANT CODE UPDATE BEGIN -->
  	  <input type='hidden' name='sales[%d][purchaseid]' value='%d' id='sales_%d_purchaseid' />
  	  <input type='hidden' name='sales[%d][category]' value='%d' id='sales_%d_category' />
  	  <input type='hidden' name='sales[%d][productid]' value='%d' id='sales_%d_productid' />
  	  <input type='hidden' name='sales[%d][discType]' id='discount_%d_type' value='%d'/>
  	  <input type='hidden' name='sales[%d][discPercentage]' id='discount_%d_percent' value='%0.02f'/>
  	  <input type='hidden' name='sales[%d][discAmount]' id='discount_%d_amount'/>
  	  <input type='hidden' name='sales[%d][discQuant]' id='discount_%d_quantity'/>
          <!-- // KONSTANT CODE UPDATE END -->
",  //KONSTANT CODE UPDATE BEGIN
	  $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $product['name'], $price, $i, $i, $price, $i, $i, $savedGrams[$i], $i, $i, $savedEuro[$i], $i, $i, $savedGrams2[$i],$i, $i, $product['name'], $i, $product['purchaseid'], $i, $i, $categoryid, $i, $i, $product['productid'], $i, $i, $i, $discountType ,$i, $i, $dispDiscount1, $i, $i, $i, $i
	  );
// KONSTANT CODE UPDATE END
        
	  echo $product_row;
	  
  }
  
} else {
  		
	if ($menuType == 0) {
		
		if ($_SESSION['showStock'] == 1) {
			$stockDisplay = "<center>$estStock g</center>";
		}
	 		
	$product_row =	sprintf("
<script>
    $(document).ready(function() {
   function compute() {
	   	  $(this).val($(this).val().replace(/,/g, '.'));
	   	  $(this).val($(this).val().replace(' ', ''));
	   	  $(this).val($(this).val().replace('g', ''));
          var a = $('#ppgcalc%d').val();
          var b = $('#grcalc%d').val();
          if (b != 0 || b != '') {
          var total = a * b;
          var roundedtotal = total.toFixed(2);
          	$('#eurcalc%d').val(roundedtotal);
                // KONSTANT CODE UPDATE BEGIN
		        var discountAmount = $('#discount_%d_amount').delay(700).val();
		        if(discountAmount != '' && discountAmount != 0){
		          var roundedtotal = parseFloat(discountAmount).toFixed(2);
		          $('#eurcalc%d').val(roundedtotal);
		        }
                // KONSTANT CODE UPDATE END
                
      	  } else {
          	$('#eurcalc%d').val('');
          	$('#grcalc%d').val('');
          	$('#realgrcalc%d').val('');
      	  }
        }
   function compute2() {
	   	  $(this).val($(this).val().replace(/,/g, '.'));
          var a = $('#eurcalc%d').val();
          var b = $('#ppgcalc%d').val();
          if (a != 0 || a != '') {
          var total = a / b;
          var roundedtotal = total.toFixed(2);
          	$('#grcalc%d').val(roundedtotal);
                // KONSTANT CODE UPDATE BEGIN
	            var discount_quant = $('#discount_%d_quantity').delay(700).val();
	            if(discount_quant != '' && discount_quant != 0){
	              var roundedtotal = parseFloat(discount_quant).toFixed(2);
	              $('#grcalc%d').val(roundedtotal);
	            }
                // KONSTANT CODE UPDATE END
                
      	  } else {
          	$('#eurcalc%d').val('');
          	$('#grcalc%d').val('');
          	$('#realgrcalc%d').val('');
      	  }
        }
   function compute3() {
	   	  $(this).val($(this).val().replace(/,/g, '.'));
	   	  $(this).val($(this).val().replace(' ', ''));
	   	  $(this).val($(this).val().replace('g', ''));
        }
        // KONSTANT CODE UPDATE BEGIN
        function applyVolumeDiscount(){
            var a = $('#ppgcalc%d').val();
            var b = $('#grcalc%d').val();
            var user_id = '{$user_id}';
            if (b != 0 || b != '') {
                var purId = $('#sales_%d_purchaseid').val();
                $.ajax({
                    type:'get',
                    url:'get-volume-discount.php?id='+purId+'&qty='+b+'&uid='+user_id,
                    dataType:'json',
                    success:function(data)
                    {
	                 if(data != null){
	                    	var firstprice = data.price;
	                    	var discountPercent = data.discount_per;
	                    	var discountType = data.discount_type;
	                        var roundedtotal = parseFloat(firstprice).toFixed(2);
	                        $('#discount_%d_type').val(discountType);
	                        $('#discount_%d_percent').val(discountPercent);
	                        $('#discount_%d_amount').val(roundedtotal);
	                  	    $('#eurcalc%d').val(roundedtotal);
	                    }else{
	                       	$('#discount_%d_type').val('');
	                        $('#discount_%d_percent').val('');
	                        $('#discount_%d_amount').val('');
	                    }
                    }
                }); 
            }else{
               	$('#discount_%d_type').val('');
                $('#discount_%d_percent').val('');
                $('#discount_%d_amount').val('');
            }
        }
	    function applyVolumeDiscountAmount(){
	        var a = $('#ppgcalc%d').val();
	        var b = $('#eurcalc%d').val();
	        var user_id = '{$user_id}';
	        if (b != 0 || b != '') {
	            var purId = $('#sales_%d_purchaseid').val();
	            $.ajax({
	                type:'get',
	                url:'get-volume-discount-amount.php?id='+purId+'&amount='+b+'&uid='+user_id,
	                dataType:'json',
	                success:function(data)
	                {
	                    if(data != null){
	                    	var firstquant = data.quantity;
	                    	var discountPercent = data.discount_per;
	                    	var discountType = data.discount_type;
	                        var roundedtotal = parseFloat(firstquant).toFixed(2);
	                        $('#discount_%d_type').val(discountType);
	                        $('#discount_%d_percent').val(discountPercent);
	                        $('#discount_%d_quantity').val(roundedtotal);
	                  	    $('#grcalc%d').val(roundedtotal);
	                    }else{
	                    	$('#discount_%d_type').val('');
	                        $('#discount_%d_percent').val('');
	                        $('#discount_%d_quantity').val('');
	                    }
	                }
	            }); 
	        }else{
		        $('#discount_%d_type').val('');
                $('#discount_%d_percent').val('');
                $('#discount_%d_quantity').val('');
	        }
	    }
        $('#grcalc%d').bind('keyup blur change', applyVolumeDiscount);
        $('#eurcalc%d').bind('keyup blur change', applyVolumeDiscountAmount);
        // KONSTANT CODE UPDATE END
        
        $('#realgrcalc%d').bind('keypress keyup blur change', compute3);
        $('#grcalc%d').bind('keypress keyup blur change', compute);
        $('#eurcalc%d').bind('keypress keyup blur change', compute2);
        $('#grcalcB%d').bind('keypress keyup blur change', compute3);
        
		$('#free%d').click(function () {
		$('#grcalcB%d').css('display', 'inline-block');
		$('#free%d').css('display', 'none');
		});	
		$('#zero%d').click(function () {
          $('#grcalc%d').val('');
          $('#eurcalc%d').val('');
          $('#grcalcB%d').val('');
          $('#realgrcalc%d').val('');
var sum = 0;
$( '.calc' ).each( function( i , e ) {
    var v = +$( e ).val();
    if ( !isNaN( v ) )
        sum += v;
} );
  var rsum = sum.toFixed(2);
  $('#grcalcTOT').val(rsum);
  $('#grcalcTOT2').val(rsum);
  $('#grcalcTOTexp').val(rsum);
var sumB = 0;
$( '.calc2' ).each( function( i , e ) {
    var vB = +$( e ).val() ;
    if ( !isNaN( vB ) )
        sumB += vB;
} );
  var rsumB = sumB.toFixed(2);
		var newPrice = rsumB;		
  $('#eurcalcTOT').val(newPrice);
  $('#eurcalcTOT2').val(newPrice);
  $('#eurcalcTOTexp').val(newPrice);
  
  
var sumC = 0;
$( '.calc3' ).each( function( i , e ) {
    var vC = +$( e ).val();
    if ( !isNaN( vC ) )
        sumC += vC;
} );
$( '.calc4' ).each( function( i , e ) {
    var vD = +$( e ).val();
    if ( !isNaN( vD ) )
        sumC += vD;
} );
  var sumC = sumC.toFixed(2);
  $('#unitcalcTOT').val(sumC);
  $('#unitcalcTOT2').val(sumC);
  
  
var sumE = 0;
$( '.calc5' ).each( function( i , e ) {
    var vE = +$( e ).val();
    if ( !isNaN( vE ) )
        sumE += vE;
} );
var sumF = 0;
$( '.calc6' ).each( function( i , e ) {
    var vF = +$( e ).val();
    if ( !isNaN( vF ) )
        sumF += vF;
} );
	  sumF += sumE;
  $('#realgrcalcTOT').val(sumF);
          var a = $('#realCredit').val();
          var b = $('#eurcalcTOT').val();
	      var total = ((a*1) - (b*1));
          var roundedtotal = total.toFixed(2);
          $('#newcredit').val(roundedtotal);
          $('#newcredit2').val(roundedtotal);
	      $('#realNewCredit').val(roundedtotal);
		});	
  }); // end ready
        </script>
	<div class='displaybox'>
	<div class='sameHeight_box'>
	 <div class='imageholder'>
	  <img src='$productImg' />
	 </div>
	 <h3>%s</h3>
	 
	 <table style='width: 210px;'>
	  <tr>
	   <td style='width: 80px; padding-left: 5px; vertical-align: top; text-align: left;'>
	   </td>
	   <td style='width: 125px; text-align: right; vertical-align: top;'>
	    <input class='specialInput' id='ppgcalc%d' name='sales[%d][ppg]' value='%0.02f' readonly /><span class='eurosign'>{$_SESSION['currencyoperator']}</span><br />
	    <span class='discountprice'>$normalPrice</span>
	   </td>
	  </tr>
	 </table>
	 <table style='width: 210px; height: 60px;'>
	  <tr>
	   <td style='vertical-align: top; text-align: left;'>
	    <span class='relativeitem'>$commentRead $commentReadM </span>
	   </td>
	   <td style='width: 85px; text-align: right; vertical-align: top; color: #606f5c; font-weight: 600; padding-top: 13px;'>
	    $stockDisplay
	   </td>
	  </tr>
	 </table>
	 <table class ='priceInput' style='width: 210px; background-color: #c7d6c1; border-radius: 5px;'>
	  <tr>
	   <td style='vertical-align: top; text-align: left; margin-left: 10px;'>
  	    <input type='text' lang='nb' class='dispinput centered calc onScreenKeypad tst c$purchaseid' id='grcalc%d' name='sales[%d][grams]' placeholder='Gr.' step='0.01' value='%s' style='margin-left: 10px;' />
	   </td>
	   <td style='vertical-align: top; text-align: right;' colspan='2'>
  	    <input type='text' lang='nb' class='dispinput centered calc2 onScreenKeypad tst' id='eurcalc%d' name='sales[%d][euro]' placeholder='{$_SESSION['currencyoperator']}' step='0.01' value='%s' style='margin-right: 10px;'/>
	   </td>
	  </tr>
	  <tr>
	   <td style='vertical-align: top; text-align: left;'>
  	    <input type='text' lang='nb' class='dispinput centered calc5 onScreenKeypad' id='realgrcalc%d' name='sales[%d][realGrams]' value='%s' placeholder='Real gr.' step='0.01' style='margin-left: 10px; margin-bottom: 10px;' />
  	   </td>
  	   <td>
  	    <input type='text' lang='nb' class='dispinput centered calc calc6 onScreenKeypad tst' id='grcalcB%d' name='sales[%d][grams2]' placeholder='Gr.' step='0.01' value='%s' style='background-color: #f4b146; display: none; margin-left: -6px; margin-bottom: 10px; width: 50px;'  /> $giftOption
  	   </td>
  	   <td style='vertical-align: middle; text-align: left;'><img src='images/delete.png' width='17' style='display: inline-block; cursor: pointer;' class='remove_pr' id='zero%d' />
  	    <input type='hidden' name='sales[%d][name]' value='%s' />
          <!-- // KONSTANT CODE UPDATE BEGIN -->
  	  <input type='hidden' name='sales[%d][purchaseid]' value='%d' id='sales_%d_purchaseid' />
  	  <input type='hidden' name='sales[%d][category]' value='%d' id='sales_%d_category' />
  	  <input type='hidden' name='sales[%d][productid]' value='%d' id='sales_%d_productid' />
  	  <input type='hidden' name='sales[%d][discType]' id='discount_%d_type' value='%d'/>
  	  <input type='hidden' name='sales[%d][discPercentage]' id='discount_%d_percent' value='%0.02f'/>
  	  <input type='hidden' name='sales[%d][discAmount]' id='discount_%d_amount'/>
  	  <input type='hidden' name='sales[%d][discQuant]' id='discount_%d_quantity'/>
          <!-- // KONSTANT CODE UPDATE END -->
  	   </td>
  	  </tr>
	 </table>
  	 </center>
	</div>
	</div>
<script>
    $(document).ready(function() {
	    
		$('#realgrcalc%d').rules('add', {
		  required: function(element){
	            return $('#grcalc%d').val()!='';
	        },
		  number: true,
		  range: function() {
		      return [parseFloat($('#grcalc%d').val()) - {$_SESSION['gramLimit']}, parseFloat($('#grcalc%d').val()) + {$_SESSION['gramLimit']}];
		  }
		});
	
  }); // end ready
</script>
",  //KONSTANT CODE UPDATE BEGIN
	  $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $product['name'], $i, $i, $price, $i, $i, $savedGrams[$i], $i, $i, $savedEuro[$i], $i, $i, $savedrealGrams[$i], $i, $i, $savedGrams2[$i],$i, $i, $i, $product['name'], $i, $product['purchaseid'], $i, $i, $categoryid, $i, $i, $product['productid'], $i, $i, $i, $discountType ,$i, $i, $dispDiscount1, $i, $i, $i, $i, $i, $i, $i, $i
	  );
// KONSTANT CODE UPDATE END
                      
	  echo $product_row;
  } else {
	  
		if ($_SESSION['showStock'] == 1) {
			$stockDisplay = "<img src='images/box-small.png' style='margin-bottom: -3px; margin-right: 5px;' />$estStock g";
		}
		
	$product_row =	sprintf("
<script>
    $(document).ready(function() {
   function compute() {
	   	  $(this).val($(this).val().replace(/,/g, '.'));
 	   	  $(this).val($(this).val().replace(' ', ''));
	   	  $(this).val($(this).val().replace('g', ''));
         var a = $('#ppgcalc%d').val();
          var b = $('#grcalc%d').val();
          if (b != 0 || b != '') {
          var total = a * b;
          var roundedtotal = total.toFixed(2);
          	$('#eurcalc%d').val(roundedtotal);
                // KONSTANT CODE UPDATE BEGIN
		        var discountAmount = $('#discount_%d_amount').delay(700).val();
		        if(discountAmount != '' && discountAmount != 0){
		          var roundedtotal = parseFloat(discountAmount).toFixed(2);
		          $('#eurcalc%d').val(roundedtotal);
		        }
                // KONSTANT CODE UPDATE END
      	  } else {
          	$('#eurcalc%d').val('');
          	$('#grcalc%d').val('');
          	$('#realgrcalc%d').val('');
      	  }
        }
   function compute2() {
	   	  $(this).val($(this).val().replace(/,/g, '.'));
          var a = $('#eurcalc%d').val();
          var b = $('#ppgcalc%d').val();
          if (a != 0 || a != '') {
          var total = a / b;
          var roundedtotal = total.toFixed(2);
          	$('#grcalc%d').val(roundedtotal);
                // KONSTANT CODE UPDATE BEGIN
	            var discount_quant = $('#discount_%d_quantity').delay(700).val();
	            if(discount_quant != '' && discount_quant != 0){
	              var roundedtotal = parseFloat(discount_quant).toFixed(2);
	              $('#grcalc%d').val(roundedtotal);
	            }
                // KONSTANT CODE UPDATE END
      	  } else {
          	$('#eurcalc%d').val('');
          	$('#grcalc%d').val('');
          	$('#realgrcalc%d').val('');
      	  }
        }
   function compute3() {
	   	  $(this).val($(this).val().replace(/,/g, '.'));
	   	  $(this).val($(this).val().replace(' ', ''));
	   	  $(this).val($(this).val().replace('g', ''));
        }
        // KONSTANT CODE UPDATE BEGIN
        function applyVolumeDiscount(){
            var a = $('#ppgcalc%d').val();
            var b = $('#grcalc%d').val();
            var user_id = '{$user_id}';
            if (b != 0 || b != '') {
                var purId = $('#sales_%d_purchaseid').val();
                $.ajax({
                    type:'get',
                    url:'get-volume-discount.php?id='+purId+'&qty='+b+'&uid='+user_id,
                    dataType:'json',
                    success:function(data)
                    {
	                 if(data != null){
	                    	var firstprice = data.price;
	                    	var discountPercent = data.discount_per;
	                    	var discountType = data.discount_type;
	                        var roundedtotal = parseFloat(firstprice).toFixed(2);
	                        $('#discount_%d_type').val(discountType);
	                        $('#discount_%d_percent').val(discountPercent);
	                        $('#discount_%d_amount').val(roundedtotal);
	                  	    $('#eurcalc%d').val(roundedtotal);
	                    }else{
	                       	$('#discount_%d_type').val('');
	                        $('#discount_%d_percent').val('');
	                        $('#discount_%d_amount').val('');
	                    }
                    }
                }); 
            }else{
               	$('#discount_%d_type').val('');
                $('#discount_%d_percent').val('');
                $('#discount_%d_amount').val('');
            }
        }
	    function applyVolumeDiscountAmount(){
	        var a = $('#ppgcalc%d').val();
	        var b = $('#eurcalc%d').val();
	        var user_id = '{$user_id}';
	        if (b != 0 || b != '') {
	            var purId = $('#sales_%d_purchaseid').val();
	            $.ajax({
	                type:'get',
	                url:'get-volume-discount-amount.php?id='+purId+'&amount='+b+'&uid='+user_id,
	                dataType:'json',
	                success:function(data)
	                {
	                    if(data != null){
	                    	var firstquant = data.quantity;
	                    	var discountPercent = data.discount_per;
	                    	var discountType = data.discount_type;
	                        var roundedtotal = parseFloat(firstquant).toFixed(2);
	                        $('#discount_%d_type').val(discountType);
	                        $('#discount_%d_percent').val(discountPercent);
	                        $('#discount_%d_quantity').val(roundedtotal);
	                  	    $('#grcalc%d').val(roundedtotal);
	                    }else{
	                    	$('#discount_%d_type').val('');
	                        $('#discount_%d_percent').val('');
	                        $('#discount_%d_quantity').val('');
	                    }
	                }
	            }); 
	        }else{
		        $('#discount_%d_type').val('');
                $('#discount_%d_percent').val('');
                $('#discount_%d_quantity').val('');
	        }
	    }
        $('#grcalc%d').bind('keyup blur change', applyVolumeDiscount);
        $('#eurcalc%d').bind('keyup blur change', applyVolumeDiscountAmount);
        // KONSTANT CODE UPDATE END
        $('#realgrcalc%d').bind('keypress keyup blur change', compute3);
        $('#grcalc%d').bind('keypress keyup blur change', compute);
        $('#eurcalc%d').bind('keypress keyup blur change', compute2);
        $('#grcalcB%d').bind('keypress keyup blur change', compute3);
        
		$('#free%d').click(function () {
		$('#grcalcB%d').css('display', 'inline-block');
		$('#free%d').css('display', 'none');
		});	
		$('#zero%d').click(function () {
          $('#grcalc%d').val('');
          $('#eurcalc%d').val('');
          $('#grcalcB%d').val('');
          $('#realgrcalc%d').val('');
var sum = 0;
$( '.calc' ).each( function( i , e ) {
    var v = +$( e ).val();
    if ( !isNaN( v ) )
        sum += v;
} );
  var rsum = sum.toFixed(2);
  $('#grcalcTOT').val(rsum);
  $('#grcalcTOT2').val(rsum);
  $('#grcalcTOTexp').val(rsum);
var sumB = 0;
$( '.calc2' ).each( function( i , e ) {
    var vB = +$( e ).val() ;
    if ( !isNaN( vB ) )
        sumB += vB;
} );
  var rsumB = sumB.toFixed(2);
		var newPrice = rsumB;		
  $('#eurcalcTOT').val(newPrice);
  $('#eurcalcTOT2').val(newPrice);
  $('#eurcalcTOTexp').val(newPrice);
  
  
var sumC = 0;
$( '.calc3' ).each( function( i , e ) {
    var vC = +$( e ).val();
    if ( !isNaN( vC ) )
        sumC += vC;
} );
$( '.calc4' ).each( function( i , e ) {
    var vD = +$( e ).val();
    if ( !isNaN( vD ) )
        sumC += vD;
} );
  var sumC = sumC.toFixed(2);
  $('#unitcalcTOT').val(sumC);
  $('#unitcalcTOT2').val(sumC);
  
  
var sumE = 0;
$( '.calc5' ).each( function( i , e ) {
    var vE = +$( e ).val();
    if ( !isNaN( vE ) )
        sumE += vE;
} );
var sumF = 0;
$( '.calc6' ).each( function( i , e ) {
    var vF = +$( e ).val();
    if ( !isNaN( vF ) )
        sumF += vF;
} );
	  sumF += sumE;
  $('#realgrcalcTOT').val(sumF);
          var a = $('#realCredit').val();
          var b = $('#eurcalcTOT').val();
	      var total = ((a*1) - (b*1));
          var roundedtotal = total.toFixed(2);
          $('#newcredit').val(roundedtotal);
          $('#newcredit2').val(roundedtotal);
	      $('#realNewCredit').val(roundedtotal);
		});	
  }); // end ready
        </script>
        
	   <tr class='$trclass'>
	    <td class='left'><strong>%s</strong></td>
	    <td>
		    <span class='relativeitem'>$commentRead</span>&nbsp;
		    	<span class='relativeitem'>$commentReadM</span>&nbsp;&nbsp;&nbsp;
		    	<strong style='font-size: 15px;'>%0.02f {$_SESSION['currencyoperator']}</strong> <input type='hidden' id='ppgcalc%d' name='sales[%d][ppg]' value='%0.02f' />&nbsp;&nbsp;
		    	<span style='font-size: 15px;' class='firsttext'>$normalPrice</span>&nbsp;&nbsp;&nbsp;	    
		    	<span class='smallgreen' style='text-transform: none;'>$stockDisplay</span><br>
		    	<input type='text' lang='nb' class='twoDigit defaultinput centered calc onScreenKeypad c$purchaseid' id='grcalc%d' name='sales[%d][grams]' placeholder='Gr.' step='0.01' value='%s' />
		    	<input type='text' lang='nb' class='twoDigit defaultinput centered calc2 onScreenKeypad'  id='eurcalc%d' name='sales[%d][euro]' placeholder='{$_SESSION['currencyoperator']}' step='0.01' value='%s' />
		    	<input type='text' lang='nb' class='fourDigit defaultinput centered calc5 onScreenKeypad' id='realgrcalc%d' name='sales[%d][realGrams]' placeholder='Gr. real' step='0.01'  value='%s' /> 
		    	<input type='text' lang='nb' class='twoDigit defaultinput centered calc calc6 onScreenKeypad' id='grcalcB%d' name='sales[%d][grams2]' placeholder='Gr.' step='0.01' value='%s' style='background-color: #f4b146; $displayGift'  />$giftOption
		    	&nbsp;&nbsp;<img src='images/delete.png' width='17' style='cursor: pointer;' class='remove_pr' id='zero%d' />
	    	</td>
	   </tr>
  	  <input type='hidden' name='sales[%d][name]' value='%s' />
          <!-- // KONSTANT CODE UPDATE BEGIN -->
  	  <input type='hidden' name='sales[%d][purchaseid]' value='%d' id='sales_%d_purchaseid' />
  	  <input type='hidden' name='sales[%d][category]' value='%d' id='sales_%d_category' />
  	  <input type='hidden' name='sales[%d][productid]' value='%d' id='sales_%d_productid' />
  	  <input type='hidden' name='sales[%d][discType]' id='discount_%d_type' value='%d'/>
  	  <input type='hidden' name='sales[%d][discPercentage]' id='discount_%d_percent' value='%0.02f'/>
  	  <input type='hidden' name='sales[%d][discAmount]' id='discount_%d_amount'/>
  	  <input type='hidden' name='sales[%d][discQuant]' id='discount_%d_quantity'/>
          <!-- // KONSTANT CODE UPDATE END -->
<script>
    $(document).ready(function() {
	    
		$('#realgrcalc%d').rules('add', {
		  required: function(element){
	            return $('#grcalc%d').val()!='';
	        },
		  number: true,
		  range: function() {
		      return [parseFloat($('#grcalc%d').val()) - {$_SESSION['gramLimit']}, parseFloat($('#grcalc%d').val()) + {$_SESSION['gramLimit']}];
		  }
		});
	
  }); // end ready
</script>
",  //KONSTANT CODE UPDATE BEGIN
	  $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $product['name'], $price, $i, $i, $price, $i, $i, $savedGrams[$i], $i, $i, $savedEuro[$i], $i, $i, $savedrealGrams[$i], $i, $i, $savedGrams2[$i], $i, $i, $i, $product['name'], $i, $product['purchaseid'], $i, $i, $categoryid, $i, $i, $product['productid'], $i, $i, $i, $discountType ,$i, $i, $dispDiscount1, $i, $i, $i, $i, $i, $i, $i, $i
	  );
// KONSTANT CODE UPDATE END
	  echo $product_row;
	  
  }
	  
  		} // End gram vs unit cat
	  } // End individual product display, loop back to next product
	} // End real vs fake grams
	  
	  
	  
		
	if ($menuType == 1) {
		echo "</tbody></table></div><div class='leftfloat'><table class='default nonhover'>";
	}
echo "<script>$('#currenti').val($i);";
		if ($_SESSION['keypads'] == 1) {
			
			echo <<<EOD
$('.onScreenKeypad').keypad({
    layout: ['789' + $.keypad.CLOSE, 
        '456' + $.keypad.CLEAR, 
        '123' + $.keypad.BACK, 
        '.0' + $.keypad.SPACE]});			
EOD;
		}
echo <<<EOD
</script>
EOD;

	} else {
		
	// For each cat, look up products
  	$selectProduct = "SELECT pr.productid, pr.name, pr.breed2, pr.flowertype, pr.sativaPercentage, pr.description, pr.medicaldescription, p.purchaseid, p.salesPrice, p.photoExt, p.realQuantity, p.medDiscount, pr.THC, pr.CBD, pr.CBN, p.growType FROM products pr, purchases p WHERE p.category = $categoryid AND p.productid = pr.productid AND p.closedAt IS NULL AND inMenu = 1 ORDER BY $sorting";
		try
		{
			$resultProduct = $pdo3->prepare("$selectProduct");
			$resultProduct->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
		// Display product percentages?
		$propquery = "SELECT SUM(pr.sativaPercentage), SUM(pr.THC), SUM(pr.CBD), SUM(pr.CBN) FROM products pr, purchases p WHERE p.category = 1 AND p.productid = pr.productid AND p.closedAt IS NULL AND p.inMenu = 1";
		try
		{
			$resultProp = $pdo3->prepare("$propquery");
			$resultProp->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user:asd ' . $e->getMessage();
				echo $error;
				exit();
		}
		
		$rowProp = $resultProp->fetch();
		$propTotal = $rowProp['SUM(pr.sativaPercentage)'] + $rowProp['SUM(pr.THC)'] + $rowProp['SUM(pr.CBD)'] + $rowProp['SUM(pr.CBN)'];
		if ($menuType == 1) {
			echo "<table class='default productlist nonhover'><tbody>";
		}
		
	// Look up discount for THIS category
	$selectCategoryDiscount = "SELECT discount FROM catdiscounts WHERE user_id = $user_id AND categoryid = $categoryid";
		try
		{
			$result = $pdo3->prepare("$selectCategoryDiscount");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$rowCD = $result->fetch();
		$catDiscount = $rowCD['discount'];
		
		$medicalDiscount = $_SESSION['medicalDiscount'];
		$medicalDiscountPercentage = $_SESSION['medicalDiscountPercentage'];
		
		if ($medicalDiscountPercentage == 1) {
			$medicalDiscountCalc = 1 - ($medicalDiscount / 100);
		}
		
	while ($product = $resultProduct->fetch()) {

		if (trim($product['breed2']) != '') {
			$name = $product['name'] . " x " . $product['breed2'];
		} else {
			$name = $product['name'];
		}
		
/*		if ($product['flowertype'] == 'Hybrid' && $product['sativaPercentage'] > 0 && $product['sativaPercentage'] != NULL) {
			$percentageDisplay = '<br />(' . number_format($product['sativaPercentage'],0) . '% s.)';
		} else {
			$percentageDisplay = '';
		}*/

		$type_prop = "";
		if($product['flowertype'] != '' && $product['sativaPercentage'] == 0){
			$type_prop = $product['flowertype']; 
		}else if($product['flowertype'] == 'Indica' && $product['sativaPercentage'] > 0){
			$percT=100-$product['sativaPercentage'];
			$type_prop = $product['flowertype'].' '.$percT.'%'; 
		}
		else if($product['flowertype'] != '' && $product['sativaPercentage'] > 0){
			$type_prop = $product['flowertype'].' '.$product['sativaPercentage'].'%'; 
		}

			// Look up growtype
	$growtype = $product['growType'];
	
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
	// ************************************** Calculate discount(s) ****************************************
	
	$purchaseid = $product['purchaseid'];
	
	if ($_SESSION['showOrigPrice'] == 1) {
		
		$normalPrice = "{$product['salesPrice']} {$_SESSION['currencyoperator']}";
		
	} else {
	
		$normalPrice = "";
		
	}

		if ($growtype == '') {
			$growtype = "<span class='prodspecsempty'>&nbsp;</span>";
		} else {
			$growtype = "<span class='prodspecs'>$growtype</span>";
		}
		
		if (trim($type_prop) == '') {
			$flowertype = "<span class='prodspecsempty'>&nbsp;</span>";
		} else {
			$flowertype = "<span class='prodspecs'>{$type_prop}</span>";
		}
		
		$sativa = number_format($flower['sativaPercentage'],2);
		$THC = number_format($flower['THC'],2);
		$CBD = number_format($flower['CBD'],2);
		$CBN = number_format($flower['CBN'],2);
		$perTotal  = $THC + $CBD + $CBN;
		if($menuType == 0){
			$properties = "<tr><td colspan='2' style='text-align: left; height: 60px; padding-left: 5px;'></td></tr>";
		}else{
			$properties = "";
		}
		if ($perTotal > 0) {
			
			if ($menuType == 0) {
				$properties = "<tr><td colspan='2' style='text-align: left; height: 60px; padding-left: 5px;'><div class='perc_prop'>";
			} else {
				$properties = "<div class='perc_prop'>";
			}
			
			if ($THC != 0) {
				$properties .= " <span class='prodspecs_new'>THC $THC&#37;</span>";
			}
			if($THC != 0 && $CBD != 0 || $CBN != 0){
				$properties .= " | ";
			}
			if ($CBD != 0) {
				$properties .= " <span class='prodspecs_new'>CBD $CBD&#37;</span>";
			}
			if($CBD != 0 && $CBN != 0){
				$properties .= " | ";
			}
			if ($CBN != 0) {
				$properties .= " <span class='prodspecs_new'>CBN $CBN&#37;</span>";
			}
		
			if ($menuType == 0) {
				$properties .= "</div></td></tr>";
			}else{
				$properties .= "</div>";
			}
			
		}
        // KONSTANT CODE UPDATE BEGIN
			$dispDiscount1 = 0;
	        $discountType = 0;
			$getMaxDisocunt = checkMaxDisocunt($user_id, $purchaseid, $categoryid, $product['salesPrice'], 0, 0, $offsetSec);
			if($getMaxDisocunt){
				$getMaxDisocunt = explode("_", $getMaxDisocunt);
				$dispDiscount1 = $getMaxDisocunt[0];
				$discountType = $getMaxDisocunt[1];
				$applyDisc = (100 - $dispDiscount1) / 100;
				$price = number_format($product['salesPrice'] * $applyDisc,2);
			}else{
				$price = number_format($product['salesPrice'],2);
			}
        // KONSTANT CODE UPDATE END
	
	 		$i++;
	 		
	 		
			$productid = $product['productid'];
			$productName = $product['name'];
	    // KONSTANT CODE UPDATE BEGIN	
		// Fetch saved data
		  $selectSavedData = "SELECT grams, euro, realGrams, grams2 FROM savesales_details WHERE user_id = $user_id AND purchase_id = $purchaseid";
			try
			{
				$result = $pdo3->prepare("$selectSavedData");
				$result->execute();
			}
			catch (PDOException $e)
			{
					$error = 'Error fetching user: ' . $e->getMessage();
					echo $error;
					exit();
			}
        $rowSCount = $result->rowCount();
     
			$rowS = $result->fetch();
			if($rowS['grams'] != ''){
				$savedGrams[$i] = $rowS['grams'];
			}else{
				$savedGrams[$i] = '';
			}
			if ($rowS['euro'] != '') {
				$savedEuro[$i] = $rowS['euro'];
			} else {
				$savedEuro[$i] = '';
			}
			if ($rowS['realGrams'] != '') {
				$savedrealGrams[$i] = $rowS['realGrams'];
			} else {
				$savedrealGrams[$i] = '';
			}
	    
			if ($rowS['grams2'] != '0.00' && $rowS['grams2'] != '') {
				$savedGrams2[$i] = $rowS['grams2'];
				$discountType = 7;
			    $displayGift = '';
				$giftOption = "<input type='hidden' value='%d' />";
				echo "<script>$('#grcalcB$i').css('display', 'inline-block');</script>";
			}
		
		else{
		
			$savedGrams2[$i] = '';
		    $displayGift = 'display: none;';
				
				if ($_SESSION['dispensaryGift'] == 1 && $_SESSION['menuType'] == 1) {
					$giftOption = "<img src='images/gift-small.png' id='free%d' style='margin-bottom: -15px; margin-left: 5px;' />";
				} else if ($_SESSION['dispensaryGift'] == 1 && $_SESSION['menuType'] == 0) {
					if ($_SESSION['realWeight'] == 0) {
						$giftOption = "<img src='images/gift-big.png' class='giftIcon' id='free%d'  />";
					} else {
						$giftOption = "<img src='images/gift-small.png' class='giftIcon' id='free%d'/>";
					}
				} else {
					$giftOption = "<span style='display: block; height: 10px;'></span><input type='hidden' value='%d' />";
				}
		} 
	
    // KONSTANT CODE UPDATE END
	if ($product['description'] != '') {
		
		$commentRead = "
		                <img src='images/info-normal.png' style='inline-block; margin-top: 5px; margin-left: 5px;' id='comment$i' onClick='showhide$i()' /><div id='helpBox$i' class='helpBox'><strong>{$lang['extracts-description']}:</strong><br />{$product['description']}</div>
		                <script>
		                  	$('#comment$i').on({
						 		'mouseover' : function() {
								 	$('#helpBox$i').css('display', 'block');
						  		},
						  		'mouseout' : function() {
								 	$('#helpBox$i').css('display', 'none');
							  	}
						  	});
						  	function showhide$i() {
							    var x = document.getElementById('helpBox$i');
							    if (x.style.display === 'none') {
							        x.style.display = 'block';
							    } else {
							        x.style.display = 'none';
							    }
							} 
						</script>
		                ";
		
	} else {
		
		$commentRead = "<img src='images/info-normal.png' style='visibility: hidden;' />";
		
	}		
	if ($product['medicaldescription'] != '') {
		
		$commentReadM = "
		                <img src='images/med.png' style='inline-block; margin-top: 5px; margin-left: 5px;' id='commentM$i' onClick='showhideM$i()' /><div id='helpBoxM$i' class='helpBox'><strong>{$lang['extracts-medicaldesc']}:</strong><br />{$product['medicaldescription']}</div>
		                <script>
		                  	$('#commentM$i').on({
						 		'mouseover' : function() {
								 	$('#helpBoxM$i').css('display', 'block');
						  		},
						  		'mouseout' : function() {
								 	$('#helpBoxM$i').css('display', 'none');
							  	}
						  	});
						  	function showhideM$i() {
							    var x = document.getElementById('helpBoxM$i');
							    if (x.style.display === 'none') {
							        x.style.display = 'block';
							    } else {
							        x.style.display = 'none';
							    }
							} 
						</script>
		                ";
		
	} else {
		
		$commentReadM = "<img src='images/med.png' style='visibility: hidden;' />";
		
	}		
		if ($_SESSION['showStock'] == 1) {
			
			$selectSales = "SELECT SUM(quantity) FROM salesdetails WHERE purchaseid = $purchaseid";
		
			try
			{
				$result = $pdo3->prepare("$selectSales");
				$result->execute();
			}
			catch (PDOException $e)
			{
					$error = 'Error fetching user: ' . $e->getMessage();
					echo $error;
					exit();
			}
		
			$row = $result->fetch();
				$sales = $row['SUM(quantity)'];
	
			$selectPermAdditions = "SELECT SUM(quantity) FROM productmovements WHERE purchaseid = $purchaseid AND type = 1 AND (movementTypeid = 1 OR movementTypeid = 3 OR movementTypeid = 10)";
			try
			{
				$result = $pdo3->prepare("$selectPermAdditions");
				$result->execute();
			}
			catch (PDOException $e)
			{
					$error = 'Error fetching user: ' . $e->getMessage();
					echo $error;
					exit();
			}
		
			$row = $result->fetch();
				$permAdditions = $row['SUM(quantity)'];
			
			$selectPermRemovals = "SELECT SUM(quantity) FROM productmovements WHERE purchaseid = $purchaseid AND type = 2 AND (movementTypeid = 4 OR movementTypeid = 7 OR movementTypeid = 8 OR movementTypeid = 9 OR movementTypeid = 11 OR movementTypeid = 13 OR movementTypeid = 14 OR movementTypeid = 15 OR movementTypeid = 16)";
			
			try
			{
				$result = $pdo3->prepare("$selectPermRemovals");
				$result->execute();
			}
			catch (PDOException $e)
			{
					$error = 'Error fetching user: ' . $e->getMessage();
					echo $error;
					exit();
			}
		
			$row = $result->fetch();
				$permRemovals = $row['SUM(quantity)'];
					
			// Calculate what's in Internal stash
			$selectStashedInt = "SELECT SUM(quantity) FROM productmovements WHERE purchaseid = $purchaseid AND type = 2 AND (movementTypeid = 5 OR movementTypeid = 18)";
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
				$stashedInt = $row['SUM(quantity)'];
				
			$selectUnStashedInt = "SELECT SUM(quantity) FROM productmovements WHERE purchaseid = $purchaseid AND type = 1 AND (movementTypeid = 12 OR movementTypeid = 17)";
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
				$unStashedInt = $row['SUM(quantity)'];
		
					
				$inStashInt = $stashedInt - $unStashedInt;
				$inStashInt = $inStashInt;
		
		
			// Calculate what's in External stash
			$selectStashedExt = "SELECT SUM(quantity) FROM productmovements WHERE purchaseid = $purchaseid AND type = 2 AND (movementTypeid = 6 OR movementTypeid = 20)";
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
				$stashedExt = $row['SUM(quantity)'];
				
			$selectUnStashedExt = "SELECT SUM(quantity) FROM productmovements WHERE purchaseid = $purchaseid AND type = 1 AND (movementTypeid = 2 OR movementTypeid = 19)";
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
				$unStashedExt = $row['SUM(quantity)'];
	
				
			$inStashExt = $stashedExt - $unStashedExt;
			$inStashExt = $inStashExt;
			
			$inStash = $inStashInt + $inStashExt;
			$estStock = $product['realQuantity'] + $permAdditions - $sales - $permRemovals - $inStash;
			
			$estStock = number_format($estStock,1);
			
			$stockDisplay = "<center>$estStock g</center>";
		}
	   	 $productImg = "images/_$domain/purchases/".$product['purchaseid'].".".$product['photoExt'];
	   if(!file_exists($productImg)){
	   		$productImg = "images/product-placeholder.png";
	   }
	 $trclass = '';
	  if($i%2==0){
	  	 $trclass = "evencolor";
	  }
	// Unit cat first	
	if ($type == 0) {
				
	if ($menuType == 0) {
	 		
	
	$product_row =	sprintf("
<script>
    $(document).ready(function() {
   function compute() {
	   	  $(this).val($(this).val().replace(/,/g, '.'));
          var a = $('#ppgcalc%d').val();
          var b = $('#grcalc%d').val();
          if (b != 0 || b != '') {
          var total = a * b;
          var roundedtotal = total.toFixed(2);
          	$('#eurcalc%d').val(roundedtotal);
                // KONSTANT CODE UPDATE BEGIN
		        var discountAmount = $('#discount_%d_amount').delay(700).val();
		        if(discountAmount != '' && discountAmount != 0){
		          var roundedtotal = parseFloat(discountAmount).toFixed(2);
		          $('#eurcalc%d').val(roundedtotal);
		        }
                // KONSTANT CODE UPDATE END
                
      	  } else {
          	$('#eurcalc%d').val('');
          	$('#grcalc%d').val('');
      	  }
        }
   function compute2() {
	   	  $(this).val($(this).val().replace(/,/g, '.'));
          var a = $('#eurcalc%d').val();
          var b = $('#ppgcalc%d').val();
          if (a != 0 || a != '') {
          var total = a / b;
          var roundedtotal = total.toFixed(2);
          	$('#grcalc%d').val(roundedtotal);
                // KONSTANT CODE UPDATE BEGIN
	            var discount_quant = $('#discount_%d_quantity').delay(700).val();
	            if(discount_quant != '' && discount_quant != 0){
	              var roundedtotal = parseFloat(discount_quant).toFixed(2);
	              $('#grcalc%d').val(roundedtotal);
	            }
                // KONSTANT CODE UPDATE END
                
      	  } else {
          	$('#eurcalc%d').val('');
          	$('#grcalc%d').val('');
      	  }
        }
        
   function compute3() {
	   	  $(this).val($(this).val().replace(/,/g, '.'));
        }
        // KONSTANT CODE UPDATE BEGIN
        function applyVolumeDiscount(){
            var a = $('#ppgcalc%d').val();
            var b = $('#grcalc%d').val();
            var user_id = '{$user_id}';
            if (b != 0 || b != '') {
                var purId = $('#sales_%d_purchaseid').val();
                $.ajax({
                    type:'get',
                    url:'get-volume-discount.php?id='+purId+'&qty='+b+'&uid='+user_id,
                    dataType:'json',
                    success:function(data)
                    {
	                    if(data != null){
	                    	var firstprice = data.price;
	                    	var discountPercent = data.discount_per;
	                    	var discountType = data.discount_type;
	                        var roundedtotal = parseFloat(firstprice).toFixed(2);
	                        $('#discount_%d_type').val(discountType);
	                        $('#discount_%d_percent').val(discountPercent);
	                        $('#discount_%d_amount').val(roundedtotal);
	                  	    $('#eurcalc%d').val(roundedtotal);
	                    }else{
	                       	$('#discount_%d_type').val('');
	                        $('#discount_%d_percent').val('');
	                        $('#discount_%d_amount').val('');
	                    }
                    }
                }); 
            }else{
               	$('#discount_%d_type').val('');
                $('#discount_%d_percent').val('');
                $('#discount_%d_amount').val('');
            }
        }
	    function applyVolumeDiscountAmount(){
	        var a = $('#ppgcalc%d').val();
	        var b = $('#eurcalc%d').val();
	        var user_id = '{$user_id}';
	        if (b != 0 || b != '') {
	            var purId = $('#sales_%d_purchaseid').val();
	            $.ajax({
	                type:'get',
	                url:'get-volume-discount-amount.php?id='+purId+'&amount='+b+'&uid='+user_id,
	                dataType:'json',
	                success:function(data)
	                {
	                    if(data != null){
	                    	var firstquant = data.quantity;
	                    	var discountPercent = data.discount_per;
	                    	var discountType = data.discount_type;
	                        var roundedtotal = parseFloat(firstquant).toFixed(2);
	                        $('#discount_%d_type').val(discountType);
	                        $('#discount_%d_percent').val(discountPercent);
	                        $('#discount_%d_quantity').val(roundedtotal);
	                  	    $('#grcalc%d').val(roundedtotal);
	                    }else{
	                    	$('#discount_%d_type').val('');
	                        $('#discount_%d_percent').val('');
	                        $('#discount_%d_quantity').val('');
	                    }
	                }
	            }); 
	        }else{
		        $('#discount_%d_type').val('');
                $('#discount_%d_percent').val('');
                $('#discount_%d_quantity').val('');
	        }
	    }
        $('#grcalc%d').bind('keyup blur change', applyVolumeDiscount);
        $('#eurcalc%d').bind('keyup blur change', applyVolumeDiscountAmount);
        // KONSTANT CODE UPDATE END
        $('#grcalc%d').bind('keypress keyup blur change', compute);
        $('#eurcalc%d').bind('keypress keyup blur change', compute2);
        $('#grcalcB%d').bind('keypress keyup blur change', compute3);
        
		$('#free%d').click(function () {
		$('#grcalcB%d').css('display', 'inline-block');
		$('#free%d').css('display', 'none');
		});	
  }); // end ready
        </script>
	<div class='displaybox'>
	<div class='sameHeight_box'>
	 <div class='imageholder'>
	  <img src='$productImg' />
	 </div>
	 <h3>%s</h3>
	 
	 <table style='width: 210px;'>
	  <tr>
	   <td style='width: 80px; padding-left: 5px; vertical-align: top; text-align: left;'>
	    %s<br />
	    %s
	   </td>
	   <td style='width: 125px; text-align: right; vertical-align: top;'>
	    <input class='specialInput' id='ppgcalc%d' name='sales[%d][ppg]' value='%0.02f' readonly /><span class='eurosign'>{$_SESSION['currencyoperator']}</span><br />
	    <span class='discountprice'>$normalPrice</span>
	   </td>
	  </tr>
	 </table>
	 <table style='width: 210px; height: 60px;'>
	  <tr>
	   <td style='vertical-align: top; text-align: left;'>
	   <span class='relativeitem'> $commentRead $commentReadM </span>
	   </td>
	   <td style='width: 85px; text-align: right; vertical-align: top; color: #606f5c; font-weight: 600; padding-top: 13px;'>
	    $stockDisplay
	   </td>
	  </tr>
	 </table>
	 <table class ='priceInput' style='width: 210px; background-color: #c7d6c1; border-radius: 5px;'>
	  <tr>
	   <td style='vertical-align: top; text-align: left; margin-left: 10px;'>
  	    <input type='text' lang='nb' class='dispinput centered calc3 onScreenKeypad tst c$purchaseid' id='grcalc%d' name='sales[%d][grams]' placeholder='#' step='1' value='%s' style='margin-left: 10px;' />
	   </td>
	   <td style='vertical-align: top; text-align: right;'>
  	    <input type='text' lang='nb' class='dispinput centered calc2 onScreenKeypad tst' id='eurcalc%d' name='sales[%d][euro]' placeholder='{$_SESSION['currencyoperator']}' step='0.01' value='%s' style='margin-right: 10px;'/>
	   </td>
	  </tr>
	  <tr>
	   <td>
  	    <input type='text' lang='nb' class='dispinput centered calc4 onScreenKeypad tst' id='grcalcB%d' name='sales[%d][grams2]' placeholder='#' step='0.01' value='%s' style='background-color: #f4b146; display: none; margin-left: 6px; margin-bottom: 10px;'  /> $giftOption
  	   </td>
  	   <td>
  	    <input type='hidden' name='sales[%d][name]' value='%s' />
          <!-- // KONSTANT CODE UPDATE BEGIN -->
  	  <input type='hidden' name='sales[%d][purchaseid]' value='%d' id='sales_%d_purchaseid' />
  	  <input type='hidden' name='sales[%d][category]' value='%d' id='sales_%d_category' />
  	  <input type='hidden' name='sales[%d][productid]' value='%d' id='sales_%d_productid' />
  	  <input type='hidden' name='sales[%d][discType]' id='discount_%d_type' value='%d'/>
  	  <input type='hidden' name='sales[%d][discPercentage]' id='discount_%d_percent' value='%0.02f'/>
  	  <input type='hidden' name='sales[%d][discAmount]' id='discount_%d_amount'/>
  	  <input type='hidden' name='sales[%d][discQuant]' id='discount_%d_quantity' />
          <!-- // KONSTANT CODE UPDATE END -->
  	   </td>
  	  </tr>
	 </table>
	</div></div>", //KONSTANT CODE UPDATE BEGIN 
	  $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $name, $growtype, $flowertype, $i, $i, $price, $i, $i, $savedGrams[$i], $i, $i, $savedEuro[$i], $i, $i, $savedGrams2[$i], $i, $i, $product['name'], $i, $product['purchaseid'], $i, $i, $categoryid, $i, $i, $product['productid'], $i, $i, $i, $discountType ,$i, $i, $dispDiscount1, $i, $i, $i, $i
	  );
// KONSTANT CODE UPDATE END
        
  } else {
	 
		if ($_SESSION['showStock'] == 1) {
			$stockDisplay = "<img src='images/box-small.png' style='margin-bottom: -3px; margin-right: 5px;' />$estStock u";
		}
		
	$product_row =	sprintf("
<script>
    $(document).ready(function() {
   function compute() {
	   	  $(this).val($(this).val().replace(/,/g, '.'));
          var a = $('#ppgcalc%d').val();
          var b = $('#grcalc%d').val();
          if (b != 0 || b != '') {
          var total = a * b;
          var roundedtotal = total.toFixed(2);
          	$('#eurcalc%d').val(roundedtotal);
                // KONSTANT CODE UPDATE BEGIN
		        var discountAmount = $('#discount_%d_amount').delay(700).val();
		        if(discountAmount != '' && discountAmount != 0){
		          var roundedtotal = parseFloat(discountAmount).toFixed(2);
		          $('#eurcalc%d').val(roundedtotal);
		        }
                // KONSTANT CODE UPDATE END
                
      	  } else {
          	$('#eurcalc%d').val('');
          	$('#grcalc%d').val('');
      	  }
        }
   function compute2() {
	   	  $(this).val($(this).val().replace(/,/g, '.'));
          var a = $('#eurcalc%d').val();
          var b = $('#ppgcalc%d').val();
          if (a != 0 || a != '') {
          var total = a / b;
          var roundedtotal = total.toFixed(2);
          	$('#grcalc%d').val(roundedtotal);
                // KONSTANT CODE UPDATE BEGIN
	            var discount_quant = $('#discount_%d_quantity').delay(700).val();
	            if(discount_quant != '' && discount_quant != 0){
	              var roundedtotal = parseFloat(discount_quant).toFixed(2);
	              $('#grcalc%d').val(roundedtotal);
	            }
                // KONSTANT CODE UPDATE END
      	  } else {
          	$('#eurcalc%d').val('');
          	$('#grcalc%d').val('');
      	  }
        }
        
   function compute3() {
	   	  $(this).val($(this).val().replace(/,/g, '.'));
        }
        // KONSTANT CODE UPDATE BEGIN
        function applyVolumeDiscount(){
            var a = $('#ppgcalc%d').val();
            var b = $('#grcalc%d').val();
            var user_id = '{$user_id}';
            if (b != 0 || b != '') {
                var purId = $('#sales_%d_purchaseid').val();
                $.ajax({
                    type:'get',
                    url:'get-volume-discount.php?id='+purId+'&qty='+b+'&uid='+user_id,
                    dataType:'json',
                    success:function(data)
                    {
	                 if(data != null){
	                    	var firstprice = data.price;
	                    	var discountPercent = data.discount_per;
	                    	var discountType = data.discount_type;
	                        var roundedtotal = parseFloat(firstprice).toFixed(2);
	                        $('#discount_%d_type').val(discountType);
	                        $('#discount_%d_percent').val(discountPercent);
	                        $('#discount_%d_amount').val(roundedtotal);
	                  	    $('#eurcalc%d').val(roundedtotal);
	                    }else{
	                       	$('#discount_%d_type').val('');
	                        $('#discount_%d_percent').val('');
	                        $('#discount_%d_amount').val('');
	                    }
                    }
                }); 
            }else{
               	$('#discount_%d_type').val('');
                $('#discount_%d_percent').val('');
                $('#discount_%d_amount').val('');
            }
        }
	    function applyVolumeDiscountAmount(){
	        var a = $('#ppgcalc%d').val();
	        var b = $('#eurcalc%d').val();
	        var user_id = '{$user_id}';
	        if (b != 0 || b != '') {
	            var purId = $('#sales_%d_purchaseid').val();
	            $.ajax({
	                type:'get',
	                url:'get-volume-discount-amount.php?id='+purId+'&amount='+b+'&uid='+user_id,
	                dataType:'json',
	                success:function(data)
	                {
	                    if(data != null){
	                    	var firstquant = data.quantity;
	                    	var discountPercent = data.discount_per;
	                    	var discountType = data.discount_type;
	                        var roundedtotal = parseFloat(firstquant).toFixed(2);
	                        $('#discount_%d_type').val(discountType);
	                        $('#discount_%d_percent').val(discountPercent);
	                        $('#discount_%d_quantity').val(roundedtotal);
	                  	    $('#grcalc%d').val(roundedtotal);
	                    }else{
	                    	$('#discount_%d_type').val('');
	                        $('#discount_%d_percent').val('');
	                        $('#discount_%d_quantity').val('');
	                    }
	                }
	            }); 
	        }else{
		        $('#discount_%d_type').val('');
                $('#discount_%d_percent').val('');
                $('#discount_%d_quantity').val('');
	        }
	    }
        $('#grcalc%d').bind('keyup blur change', applyVolumeDiscount);
        $('#eurcalc%d').bind('keyup blur change', applyVolumeDiscountAmount);
        // KONSTANT CODE UPDATE END
        $('#grcalc%d').bind('keypress keyup blur change', compute);
        $('#eurcalc%d').bind('keypress keyup blur change', compute2);
        $('#grcalcB%d').bind('keypress keyup blur change', compute3);
        
		$('#free%d').click(function () {
		$('#grcalcB%d').css('display', 'inline-block');
		$('#free%d').css('display', 'none');
		});	
  }); // end ready
        </script>
        
	   <tr class='$trclass'>
	    <td class='left'><strong>%s</strong></td>
	    <td class='left'>%s<br>%s<br />$properties</td>
	    <td>
	        <span class='relativeitem'>$commentRead</span>&nbsp;
	    	<span class='relativeitem'>$commentReadM</span>&nbsp;&nbsp;&nbsp;
	    	<strong style='font-size: 15px;'>%0.02f {$_SESSION['currencyoperator']}</strong> <input type='hidden' id='ppgcalc%d' name='sales[%d][ppg]' value='%0.02f' />&nbsp;&nbsp;
	    	<span style='font-size: 15px;' class='firsttext'>$normalPrice</span>&nbsp;&nbsp;&nbsp;	
	    	<span class='smallgreen' style='text-transform: none;'>$stockDisplay</span><br>
	    	<input type='text' lang='nb' class='twoDigit defaultinput centered calc3 onScreenKeypad c$purchaseid'  id='grcalc%d' name='sales[%d][grams]' placeholder='#' step='0.01' value='%s' />
	    	<input type='text' lang='nb' class='twoDigit defaultinput centered calc2 onScreenKeypad' id='eurcalc%d' name='sales[%d][euro]' placeholder='{$_SESSION['currencyoperator']}' step='0.01' value='%s' />
	    	<input type='text' lang='nb' class='twoDigit defaultinput centered calc4 onScreenKeypad' id='grcalcB%d' name='sales[%d][grams2]' placeholder='#' step='0.01' value='%s' style=' background-color: #f4b146; $displayGift'  />$giftOption
		    </td>
	   </tr>
  	  <input type='hidden' name='sales[%d][name]' value='%s' />
          <!-- // KONSTANT CODE UPDATE BEGIN -->
  	  <input type='hidden' name='sales[%d][purchaseid]' value='%d' id='sales_%d_purchaseid' />
  	  <input type='hidden' name='sales[%d][category]' value='%d' id='sales_%d_category' />
  	  <input type='hidden' name='sales[%d][productid]' value='%d' id='sales_%d_productid' />
  	  <input type='hidden' name='sales[%d][discType]' id='discount_%d_type' value='%d'/>
  	  <input type='hidden' name='sales[%d][discPercentage]'  id='discount_%d_percent' value='%0.02f'/>
  	  <input type='hidden' name='sales[%d][discAmount]' id='discount_%d_amount'/>
  	  <input type='hidden' name='sales[%d][discQuant]' id='discount_%d_quantity'/>
          <!-- // KONSTANT CODE UPDATE END -->
",  //KONSTANT CODE UPDATE BEGIN 
	  $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i ,$i ,$i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $name, $growtype, $flowertype, $price, $i, $i, $price, $i, $i, $savedGrams[$i], $i, $i, $savedEuro[$i], $i, $i, $savedGrams2[$i],$i, $i, $product['name'], $i, $product['purchaseid'], $i, $i, $categoryid, $i, $i, $product['productid'], $i, $i, $i, $discountType ,$i, $i, $dispDiscount1, $i, $i, $i, $i
	  );
// KONSTANT CODE UPDATE END
        
  }
	  echo $product_row;
	  
  		} else { // Now Gram cat
if ($_SESSION['realWeight'] == 0) {
	
	if ($menuType == 0) {
	 		
		if ($_SESSION['showStock'] == 1) {
			$stockDisplay = "<center>$estStock g</center>";
		}
	
	$product_row =	sprintf("
<script>
    $(document).ready(function() {
   function compute() {
	   	  $(this).val($(this).val().replace(/,/g, '.'));
	   	  $(this).val($(this).val().replace(' ', ''));
	   	  $(this).val($(this).val().replace('g', ''));
          var a = $('#ppgcalc%d').val();
          var b = $('#grcalc%d').val();
          if (b != 0 || b != '') {
          var total = a * b;
          var roundedtotal = total.toFixed(2);
          	$('#eurcalc%d').val(roundedtotal);
                // KONSTANT CODE UPDATE BEGIN
		        var discountAmount = $('#discount_%d_amount').delay(700).val();
		        if(discountAmount != '' && discountAmount != 0){
		          var roundedtotal = parseFloat(discountAmount).toFixed(2);
		          $('#eurcalc%d').val(roundedtotal);
		        }
                // KONSTANT CODE UPDATE END
      	  } else {
          	$('#eurcalc%d').val('');
          	$('#grcalc%d').val('');
      	  }
        }
   function compute2() {
	   	  $(this).val($(this).val().replace(/,/g, '.'));
          var a = $('#eurcalc%d').val();
          var b = $('#ppgcalc%d').val();
          if (a != 0 || a != '') {
          var total = a / b;
          var roundedtotal = total.toFixed(2);
          	$('#grcalc%d').val(roundedtotal);
                // KONSTANT CODE UPDATE BEGIN
	            var discount_quant = $('#discount_%d_quantity').delay(700).val();
	            if(discount_quant != '' && discount_quant != 0){
	              var roundedtotal = parseFloat(discount_quant).toFixed(2);
	              $('#grcalc%d').val(roundedtotal);
	            }
                // KONSTANT CODE UPDATE END
      	  } else {
          	$('#eurcalc%d').val('');
          	$('#grcalc%d').val('');
      	  }
        }
        
   function compute3() {
	   	  $(this).val($(this).val().replace(/,/g, '.'));
        }
        // KONSTANT CODE UPDATE BEGIN
        function applyVolumeDiscount(){
            var a = $('#ppgcalc%d').val();
            var b = $('#grcalc%d').val();
            var user_id = '{$user_id}';
            if (b != 0 || b != '') {
                var purId = $('#sales_%d_purchaseid').val();
                $.ajax({
                    type:'get',
                    url:'get-volume-discount.php?id='+purId+'&qty='+b+'&uid='+user_id,
                    dataType:'json',
                    success:function(data)
                    {
	  					if(data != null){
	                    	var firstprice = data.price;
	                    	var discountPercent = data.discount_per;
	                    	var discountType = data.discount_type;
	                        var roundedtotal = parseFloat(firstprice).toFixed(2);
	                        $('#discount_%d_type').val(discountType);
	                        $('#discount_%d_percent').val(discountPercent);
	                        $('#discount_%d_amount').val(roundedtotal);
	                  	    $('#eurcalc%d').val(roundedtotal);
	                    }else{
	                       	$('#discount_%d_type').val('');
	                        $('#discount_%d_percent').val('');
	                        $('#discount_%d_amount').val('');
	                    }
                    }
                }); 
            }else{
               	$('#discount_%d_type').val('');
                $('#discount_%d_percent').val('');
                $('#discount_%d_amount').val('');
            }
        }
	    function applyVolumeDiscountAmount(){
	        var a = $('#ppgcalc%d').val();
	        var b = $('#eurcalc%d').val();
	        var user_id = '{$user_id}';
	        if (b != 0 || b != '') {
	            var purId = $('#sales_%d_purchaseid').val();
	            $.ajax({
	                type:'get',
	                url:'get-volume-discount-amount.php?id='+purId+'&amount='+b+'&uid='+user_id,
	                dataType:'json',
	                success:function(data)
	                {
	                    if(data != null){
	                    	var firstquant = data.quantity;
	                    	var discountPercent = data.discount_per;
	                    	var discountType = data.discount_type;
	                        var roundedtotal = parseFloat(firstquant).toFixed(2);
	                        $('#discount_%d_type').val(discountType);
	                        $('#discount_%d_percent').val(discountPercent);
	                        $('#discount_%d_quantity').val(roundedtotal);
	                  	    $('#grcalc%d').val(roundedtotal);
	                    }else{
	                    	$('#discount_%d_type').val('');
	                        $('#discount_%d_percent').val('');
	                        $('#discount_%d_quantity').val('');
	                    }
	                }
	            }); 
	        }else{
		        $('#discount_%d_type').val('');
                $('#discount_%d_percent').val('');
                $('#discount_%d_quantity').val('');
	        }
	    }
        $('#grcalc%d').bind('keyup blur change', applyVolumeDiscount);
        $('#eurcalc%d').bind('keyup blur change', applyVolumeDiscountAmount);
        // KONSTANT CODE UPDATE END
        $('#grcalc%d').bind('keypress keyup blur change', compute);
        $('#eurcalc%d').bind('keypress keyup blur change', compute2);
        $('#grcalcB%d').bind('keypress keyup blur change', compute3);
        
		$('#free%d').click(function () {
		$('#grcalcB%d').css('display', 'inline-block');
		$('#free%d').css('display', 'none');
		});	
  }); // end ready
        </script>
	<div class='displaybox'>
	<div class='sameHeight_box'>
	 <div class='imageholder'>
	  <img src='$productImg' />
	 </div>
	 <h3>%s</h3>
	 
	 <table style='width: 210px;'>
	  <tr>
	   <td style='width: 80px; padding-left: 5px; vertical-align: top; text-align: left;'>
	   	%s<br>%s
	   </td>
	   <td style='width: 125px; text-align: right; vertical-align: top;'>
	    <input class='specialInput' id='ppgcalc%d' name='sales[%d][ppg]' value='%0.02f' readonly /><span class='eurosign'>{$_SESSION['currencyoperator']}</span><br />
	    <span class='discountprice'>$normalPrice</span>
	   </td>
	  </tr>
	 </table>
	 <table style='width: 210px; height: 60px;'>
	 $properties
	  <tr>
	   <td style='vertical-align: top; text-align: left;'>
	    <span class='relativeitem'>$commentRead $commentReadM </span>
	   </td>
	   <td style='width: 85px; text-align: right; vertical-align: top; color: #606f5c; font-weight: 600; padding-top: 13px;'>
	    $stockDisplay
	   </td>
	  </tr>
	 </table>
	 <table class ='priceInput' style='width: 210px; background-color: #c7d6c1; border-radius: 5px;'>
	  <tr>
	   <td style='vertical-align: top; text-align: left; margin-left: 10px;'>
  	    <input type='text' lang='nb' class='dispinput centered calc onScreenKeypad tst c$purchaseid' id='grcalc%d' name='sales[%d][grams]' placeholder='Gr.' step='0.01' value='%s' style='margin-left: 10px;' />
	   </td>
	   <td style='vertical-align: top; text-align: right;'>
  	    <input type='text' lang='nb' class='dispinput centered calc2 onScreenKeypad tst' id='eurcalc%d' name='sales[%d][euro]' placeholder='{$_SESSION['currencyoperator']}' step='0.01' value='%s' style='margin-right: 10px;'/>
	   </td>
	  </tr>
	  <tr>
	   <td>
  	    <input type='text' lang='nb' class='dispinput centered calc onScreenKeypad tst' id='grcalcB%d' name='sales[%d][grams2]' placeholder='Gr.' step='0.01' value='%s' style='background-color: #f4b146; display: none; margin-left: 6px; margin-bottom: 10px;'  /> $giftOption
  	   </td>
  	   <td>
  	    <input type='hidden' name='sales[%d][name]' value='%s' />
          <!-- // KONSTANT CODE UPDATE BEGIN -->
  	  <input type='hidden' name='sales[%d][purchaseid]' value='%d' id='sales_%d_purchaseid' />
  	  <input type='hidden' name='sales[%d][category]' value='%d' id='sales_%d_category' />
  	  <input type='hidden' name='sales[%d][productid]' value='%d' id='sales_%d_productid' />
  	  <input type='hidden' name='sales[%d][discType]' id='discount_%d_type' value='%d'/>
  	  <input type='hidden' name='sales[%d][discPercentage]' id='discount_%d_percent' value='%0.02f'/>
  	  <input type='hidden' name='sales[%d][discAmount]' id='discount_%d_amount'/>
  	  <input type='hidden' name='sales[%d][discQuant]' id='discount_%d_quantity'/>
          <!-- // KONSTANT CODE UPDATE END -->
  	   </td>
  	  </tr>
	 </table>
	</div></div>", //KONSTANT CODE UPDATE BEGIN 
	  $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $name, $growtype, $flowertype, $i, $i, $price, $i, $i, $savedGrams[$i], $i, $i, $savedEuro[$i], $i, $i, $savedGrams2[$i],$i, $i, $product['name'], $i, $product['purchaseid'], $i, $i, $categoryid, $i, $i, $product['productid'], $i, $i, $i, $discountType ,$i, $i, $dispDiscount1, $i, $i, $i, $i
	  );
// KONSTANT CODE UPDATE END
        
	  echo $product_row;
	  
  } else {
	 
		if ($_SESSION['showStock'] == 1) {
			$stockDisplay = "<img src='images/box-small.png' style='margin-bottom: -3px; margin-right: 5px;' />$estStock g";
		}
		
	$product_row =	sprintf("
<script>
    $(document).ready(function() {
   function compute() {
	   	  $(this).val($(this).val().replace(/,/g, '.'));
	   	  $(this).val($(this).val().replace(' ', ''));
	   	  $(this).val($(this).val().replace('g', ''));
          var a = $('#ppgcalc%d').val();
          var b = $('#grcalc%d').val();
          if (b != 0 || b != '') {
          var total = a * b;
          var roundedtotal = total.toFixed(2);
          	$('#eurcalc%d').val(roundedtotal);
                // KONSTANT CODE UPDATE BEGIN
		        var discountAmount = $('#discount_%d_amount').delay(700).val();
		        if(discountAmount != '' && discountAmount != 0){
		          var roundedtotal = parseFloat(discountAmount).toFixed(2);
		          $('#eurcalc%d').val(roundedtotal);
		        }
                // KONSTANT CODE UPDATE END
      	  } else {
          	$('#eurcalc%d').val('');
          	$('#grcalc%d').val('');
      	  }
        }
   function compute2() {
	   	  $(this).val($(this).val().replace(/,/g, '.'));
          var a = $('#eurcalc%d').val();
          var b = $('#ppgcalc%d').val();
          if (a != 0 || a != '') {
          var total = a / b;
          var roundedtotal = total.toFixed(2);
          	$('#grcalc%d').val(roundedtotal);
                // KONSTANT CODE UPDATE BEGIN
	            var discount_quant = $('#discount_%d_quantity').delay(700).val();
	            if(discount_quant != '' && discount_quant != 0){
	              var roundedtotal = parseFloat(discount_quant).toFixed(2);
	              $('#grcalc%d').val(roundedtotal);
	            }
                // KONSTANT CODE UPDATE END
      	  } else {
          	$('#eurcalc%d').val('');
          	$('#grcalc%d').val('');
      	  }
        }
   function compute3() {
	   	  $(this).val($(this).val().replace(/,/g, '.'));
        }
        // KONSTANT CODE UPDATE BEGIN
        function applyVolumeDiscount(){
            var a = $('#ppgcalc%d').val();
            var b = $('#grcalc%d').val();
            var user_id = '{$user_id}';
            if (b != 0 || b != '') {
                var purId = $('#sales_%d_purchaseid').val();
                $.ajax({
                    type:'get',
                    url:'get-volume-discount.php?id='+purId+'&qty='+b+'&uid='+user_id,
                    dataType:'json',
                    success:function(data)
                    {
		                 if(data != null){
		                    	var firstprice = data.price;
		                    	var discountPercent = data.discount_per;
		                    	var discountType = data.discount_type;
		                        var roundedtotal = parseFloat(firstprice).toFixed(2);
		                        $('#discount_%d_type').val(discountType);
		                        $('#discount_%d_percent').val(discountPercent);
		                        $('#discount_%d_amount').val(roundedtotal);
		                  	    $('#eurcalc%d').val(roundedtotal);
		                    }else{
		                       	$('#discount_%d_type').val('');
		                        $('#discount_%d_percent').val('');
		                        $('#discount_%d_amount').val('');
		                    }
                    }
                }); 
            }else{
               	$('#discount_%d_type').val('');
                $('#discount_%d_percent').val('');
                $('#discount_%d_amount').val('');
            }
        }
	    function applyVolumeDiscountAmount(){
	        var a = $('#ppgcalc%d').val();
	        var b = $('#eurcalc%d').val();
	        var user_id = '{$user_id}';
	        if (b != 0 || b != '') {
	            var purId = $('#sales_%d_purchaseid').val();
	            $.ajax({
	                type:'get',
	                url:'get-volume-discount-amount.php?id='+purId+'&amount='+b+'&uid='+user_id,
	                dataType:'json',
	                success:function(data)
	                {
	                    if(data != null){
	                    	var firstquant = data.quantity;
	                    	var discountPercent = data.discount_per;
	                    	var discountType = data.discount_type;
	                        var roundedtotal = parseFloat(firstquant).toFixed(2);
	                        $('#discount_%d_type').val(discountType);
	                        $('#discount_%d_percent').val(discountPercent);
	                        $('#discount_%d_quantity').val(roundedtotal);
	                  	    $('#grcalc%d').val(roundedtotal);
	                    }else{
	                    	$('#discount_%d_type').val('');
	                        $('#discount_%d_percent').val('');
	                        $('#discount_%d_quantity').val('');
	                    }
	                }
	            }); 
	        }else{
		        $('#discount_%d_type').val('');
                $('#discount_%d_percent').val('');
                $('#discount_%d_quantity').val('');
	        }
	    }
        $('#grcalc%d').bind('keyup blur change', applyVolumeDiscount);
        $('#eurcalc%d').bind('keyup blur change', applyVolumeDiscountAmount);
        // KONSTANT CODE UPDATE END
        $('#grcalc%d').bind('keypress keyup blur change', compute);
        $('#eurcalc%d').bind('keypress keyup blur change', compute2);
        $('#grcalcB%d').bind('keypress keyup blur change', compute3);
        
		$('#free%d').click(function () {
		$('#grcalcB%d').css('display', 'inline-block');
		$('#free%d').css('display', 'none');
		});	
  }); // end ready
        </script>
        
	   <tr class='$trclass'>
	    <td class='left'><strong>%s</strong></td>
	    <td class='left'>%s<br>%s <br />$properties</td>
	    <td>
	    	<span class='relativeitem'>$commentRead</span>&nbsp;
	    	<span class='relativeitem'>$commentReadM</span>&nbsp;&nbsp;&nbsp;
	    	<strong style='font-size: 15px;'>%0.02f {$_SESSION['currencyoperator']}</strong> <input type='hidden' id='ppgcalc%d' name='sales[%d][ppg]' value='%0.02f' />&nbsp;&nbsp;
	    	<span style='font-size: 15px;' class='firsttext'>$normalPrice</span>&nbsp;&nbsp;&nbsp;	    
	    	<span class='smallgreen' style='text-transform: none;'>$stockDisplay</span><br>
	    	<input type='text' lang='nb' class='twoDigit defaultinput centered calc onScreenKeypad c$purchaseid' id='grcalc%d' name='sales[%d][grams]' placeholder='Gr.' step='0.01' value='%s' />
	    	<input type='text' lang='nb' class='twoDigit defaultinput centered calc2 onScreenKeypad'  id='eurcalc%d' name='sales[%d][euro]' placeholder='{$_SESSION['currencyoperator']}' step='0.01' value='%s' />
	    	<input type='text' lang='nb' class='twoDigit defaultinput centered calc onScreenKeypad' id='grcalcB%d' name='sales[%d][grams2]' placeholder='Gr.' step='0.01' value='%s' style='background-color: #f4b146; $displayGift'  />$giftOption
	    	</td>
	   </tr>
  	  <input type='hidden' name='sales[%d][name]' value='%s' />
          <!-- // KONSTANT CODE UPDATE BEGIN -->
  	  <input type='hidden' name='sales[%d][purchaseid]' value='%d' id='sales_%d_purchaseid' />
  	  <input type='hidden' name='sales[%d][category]' value='%d' id='sales_%d_category' />
  	  <input type='hidden' name='sales[%d][productid]' value='%d' id='sales_%d_productid' />
  	  <input type='hidden' name='sales[%d][discType]' id='discount_%d_type' value='%d'/>
  	  <input type='hidden' name='sales[%d][discPercentage]' id='discount_%d_percent' value='%0.02f'/>
  	  <input type='hidden' name='sales[%d][discAmount]' id='discount_%d_amount'/>
  	  <input type='hidden' name='sales[%d][discQuant]' id='discount_%d_quantity'/>
          <!-- // KONSTANT CODE UPDATE END -->
",  //KONSTANT CODE UPDATE BEGIN
	  $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $name, $growtype, $flowertype, $price, $i, $i, $price, $i, $i, $savedGrams[$i], $i, $i, $savedEuro[$i], $i, $i, $savedGrams2[$i],$i, $i, $product['name'], $i, $product['purchaseid'], $i, $i, $categoryid, $i, $i, $product['productid'], $i, $i, $i, $discountType ,$i, $i, $dispDiscount1, $i, $i, $i, $i
	  );
// KONSTANT CODE UPDATE END
        
	  echo $product_row;
	  
  }
  
} else {
  		
	if ($menuType == 0) {
		
		if ($_SESSION['showStock'] == 1) {
			$stockDisplay = "<center>$estStock g</center>";
		}
	 		
	$product_row =	sprintf("
<script>
    $(document).ready(function() {
   function compute() {
	   	  $(this).val($(this).val().replace(/,/g, '.'));
	   	  $(this).val($(this).val().replace(' ', ''));
	   	  $(this).val($(this).val().replace('g', ''));
          var a = $('#ppgcalc%d').val();
          var b = $('#grcalc%d').val();
          if (b != 0 || b != '') {
          var total = a * b;
          var roundedtotal = total.toFixed(2);
          	$('#eurcalc%d').val(roundedtotal);
                // KONSTANT CODE UPDATE BEGIN
		        var discountAmount = $('#discount_%d_amount').delay(700).val();
		        if(discountAmount != '' && discountAmount != 0){
		          var roundedtotal = parseFloat(discountAmount).toFixed(2);
		          $('#eurcalc%d').val(roundedtotal);
		        }
                // KONSTANT CODE UPDATE END
                
      	  } else {
          	$('#eurcalc%d').val('');
          	$('#grcalc%d').val('');
          	$('#realgrcalc%d').val('');
      	  }
        }
   function compute2() {
	   	  $(this).val($(this).val().replace(/,/g, '.'));
          var a = $('#eurcalc%d').val();
          var b = $('#ppgcalc%d').val();
          if (a != 0 || a != '') {
          var total = a / b;
          var roundedtotal = total.toFixed(2);
          	$('#grcalc%d').val(roundedtotal);
                // KONSTANT CODE UPDATE BEGIN
	            var discount_quant = $('#discount_%d_quantity').delay(700).val();
	            if(discount_quant != '' && discount_quant != 0){
	              var roundedtotal = parseFloat(discount_quant).toFixed(2);
	              $('#grcalc%d').val(roundedtotal);
	            }
                // KONSTANT CODE UPDATE END
                
      	  } else {
          	$('#eurcalc%d').val('');
          	$('#grcalc%d').val('');
          	$('#realgrcalc%d').val('');
      	  }
        }
   function compute3() {
	   	  $(this).val($(this).val().replace(/,/g, '.'));
	   	  $(this).val($(this).val().replace(' ', ''));
	   	  $(this).val($(this).val().replace('g', ''));
        }
        // KONSTANT CODE UPDATE BEGIN
        function applyVolumeDiscount(){
            var a = $('#ppgcalc%d').val();
            var b = $('#grcalc%d').val();
            var user_id = '{$user_id}';
            if (b != 0 || b != '') {
                var purId = $('#sales_%d_purchaseid').val();
                $.ajax({
                    type:'get',
                    url:'get-volume-discount.php?id='+purId+'&qty='+b+'&uid='+user_id,
                    dataType:'json',
                    success:function(data)
                    {
	                 if(data != null){
	                    	var firstprice = data.price;
	                    	var discountPercent = data.discount_per;
	                    	var discountType = data.discount_type;
	                        var roundedtotal = parseFloat(firstprice).toFixed(2);
	                        $('#discount_%d_type').val(discountType);
	                        $('#discount_%d_percent').val(discountPercent);
	                        $('#discount_%d_amount').val(roundedtotal);
	                  	    $('#eurcalc%d').val(roundedtotal);
	                    }else{
	                       	$('#discount_%d_type').val('');
	                        $('#discount_%d_percent').val('');
	                        $('#discount_%d_amount').val('');
	                    }
                    }
                }); 
            }else{
               	$('#discount_%d_type').val('');
                $('#discount_%d_percent').val('');
                $('#discount_%d_amount').val('');
            }
        }
	    function applyVolumeDiscountAmount(){
	        var a = $('#ppgcalc%d').val();
	        var b = $('#eurcalc%d').val();
	        var user_id = '{$user_id}';
	        if (b != 0 || b != '') {
	            var purId = $('#sales_%d_purchaseid').val();
	            $.ajax({
	                type:'get',
	                url:'get-volume-discount-amount.php?id='+purId+'&amount='+b+'&uid='+user_id,
	                dataType:'json',
	                success:function(data)
	                {
	                    if(data != null){
	                    	var firstquant = data.quantity;
	                    	var discountPercent = data.discount_per;
	                    	var discountType = data.discount_type;
	                        var roundedtotal = parseFloat(firstquant).toFixed(2);
	                        $('#discount_%d_type').val(discountType);
	                        $('#discount_%d_percent').val(discountPercent);
	                        $('#discount_%d_quantity').val(roundedtotal);
	                  	    $('#grcalc%d').val(roundedtotal);
	                    }else{
	                    	$('#discount_%d_type').val('');
	                        $('#discount_%d_percent').val('');
	                        $('#discount_%d_quantity').val('');
	                    }
	                }
	            }); 
	        }else{
		        $('#discount_%d_type').val('');
                $('#discount_%d_percent').val('');
                $('#discount_%d_quantity').val('');
	        }
	    }
        $('#grcalc%d').bind('keyup blur change', applyVolumeDiscount);
        $('#eurcalc%d').bind('keyup blur change', applyVolumeDiscountAmount);
        // KONSTANT CODE UPDATE END
        
        $('#realgrcalc%d').bind('keypress keyup blur change', compute3);
        $('#grcalc%d').bind('keypress keyup blur change', compute);
        $('#eurcalc%d').bind('keypress keyup blur change', compute2);
        $('#grcalcB%d').bind('keypress keyup blur change', compute3);
        
		$('#free%d').click(function () {
		$('#grcalcB%d').css('display', 'inline-block');
		$('#free%d').css('display', 'none');
		});	
		$('#zero%d').click(function () {
          $('#grcalc%d').val('');
          $('#eurcalc%d').val('');
          $('#grcalcB%d').val('');
          $('#realgrcalc%d').val('');
var sum = 0;
$( '.calc' ).each( function( i , e ) {
    var v = +$( e ).val();
    if ( !isNaN( v ) )
        sum += v;
} );
  var rsum = sum.toFixed(2);
  $('#grcalcTOT').val(rsum);
  $('#grcalcTOT2').val(rsum);
  $('#grcalcTOTexp').val(rsum);
var sumB = 0;
$( '.calc2' ).each( function( i , e ) {
    var vB = +$( e ).val() ;
    if ( !isNaN( vB ) )
        sumB += vB;
} );
  var rsumB = sumB.toFixed(2);
		var newPrice = rsumB;		
  $('#eurcalcTOT').val(newPrice);
  $('#eurcalcTOT2').val(newPrice);
  $('#eurcalcTOTexp').val(newPrice);
  
  
var sumC = 0;
$( '.calc3' ).each( function( i , e ) {
    var vC = +$( e ).val();
    if ( !isNaN( vC ) )
        sumC += vC;
} );
$( '.calc4' ).each( function( i , e ) {
    var vD = +$( e ).val();
    if ( !isNaN( vD ) )
        sumC += vD;
} );
  var sumC = sumC.toFixed(2);
  $('#unitcalcTOT').val(sumC);
  $('#unitcalcTOT2').val(sumC);
  
  
var sumE = 0;
$( '.calc5' ).each( function( i , e ) {
    var vE = +$( e ).val();
    if ( !isNaN( vE ) )
        sumE += vE;
} );
var sumF = 0;
$( '.calc6' ).each( function( i , e ) {
    var vF = +$( e ).val();
    if ( !isNaN( vF ) )
        sumF += vF;
} );
	  sumF += sumE;
  $('#realgrcalcTOT').val(sumF);
          var a = $('#realCredit').val();
          var b = $('#eurcalcTOT').val();
	      var total = ((a*1) - (b*1));
          var roundedtotal = total.toFixed(2);
          $('#newcredit').val(roundedtotal);
          $('#newcredit2').val(roundedtotal);
	      $('#realNewCredit').val(roundedtotal);
		});	
  }); // end ready
        </script>
	<div class='displaybox'>
	<div class='sameHeight_box'>
	 <div class='imageholder'>
	  <img src='$productImg' />
	 </div>
	 <h3>%s</h3>
	 
	 <table style='width: 210px;'>
	  <tr>
	   <td style='width: 80px; padding-left: 5px; vertical-align: top; text-align: left;'>
	   		%s<br>%s
	   </td>
	   <td style='width: 125px; text-align: right; vertical-align: top;'>
	    <input class='specialInput' id='ppgcalc%d' name='sales[%d][ppg]' value='%0.02f' readonly /><span class='eurosign'>{$_SESSION['currencyoperator']}</span><br />
	    <span class='discountprice'>$normalPrice</span>
	   </td>
	  </tr>
	 </table>
	 <table style='width: 210px; height: 60px;'>
	 $properties
	  <tr>
	   <td style='vertical-align: top; text-align: left;'>
	    <span class='relativeitem'>$commentRead $commentReadM </span>
	   </td>
	   <td style='width: 85px; text-align: right; vertical-align: top; color: #606f5c; font-weight: 600; padding-top: 13px;'>
	    $stockDisplay
	   </td>
	  </tr>
	 </table>
	 <table class ='priceInput' style='width: 210px; background-color: #c7d6c1; border-radius: 5px;'>
	  <tr>
	   <td style='vertical-align: top; text-align: left; margin-left: 10px;'>
  	    <input type='text' lang='nb' class='dispinput centered calc onScreenKeypad tst c$purchaseid' id='grcalc%d' name='sales[%d][grams]' placeholder='Gr.' step='0.01' value='%s' style='margin-left: 10px;' />
	   </td>
	   <td style='vertical-align: top; text-align: right;' colspan='2'>
  	    <input type='text' lang='nb' class='dispinput centered calc2 onScreenKeypad tst' id='eurcalc%d' name='sales[%d][euro]' placeholder='{$_SESSION['currencyoperator']}' step='0.01' value='%s' style='margin-right: 10px;'/>
	   </td>
	  </tr>
	  <tr>
	   <td style='vertical-align: top; text-align: left;'>
  	    <input type='text' lang='nb' class='dispinput centered calc5' id='realgrcalc%d' name='sales[%d][realGrams]' value='%s' placeholder='Real gr.' step='0.01' style='margin-left: 10px; margin-bottom: 10px;' />
  	   </td>
  	   <td>
  	    <input type='text' lang='nb' class='dispinput centered calc calc6 onScreenKeypad tst' id='grcalcB%d' name='sales[%d][grams2]' placeholder='Gr.' step='0.01' value='%s' style='background-color: #f4b146; display: none; margin-left: -6px; margin-bottom: 10px; width: 50px;'  /> $giftOption
  	   </td>
  	   <td style='vertical-align: middle; text-align: left;'><img src='images/delete.png' width='17' style='display: inline-block; cursor: pointer;' class='remove_pr' id='zero%d' />
  	    <input type='hidden' name='sales[%d][name]' value='%s' />
          <!-- // KONSTANT CODE UPDATE BEGIN -->
  	  <input type='hidden' name='sales[%d][purchaseid]' value='%d' id='sales_%d_purchaseid' />
  	  <input type='hidden' name='sales[%d][category]' value='%d' id='sales_%d_category' />
  	  <input type='hidden' name='sales[%d][productid]' value='%d' id='sales_%d_productid' />
  	  <input type='hidden' name='sales[%d][discType]' id='discount_%d_type' value='%d'/>
  	  <input type='hidden' name='sales[%d][discPercentage]' id='discount_%d_percent' value='%0.02f'/>
  	  <input type='hidden' name='sales[%d][discAmount]' id='discount_%d_amount'/>
  	  <input type='hidden' name='sales[%d][discQuant]' id='discount_%d_quantity'/>
          <!-- // KONSTANT CODE UPDATE END -->
  	   </td>
  	  </tr>
	 </table>
  	 </center>
	</div>
	</div>
<script>
    $(document).ready(function() {
	    
		$('#realgrcalc%d').rules('add', {
		  required: function(element){
	            return $('#grcalc%d').val()!='';
	        },
		  number: true,
		  range: function() {
		      return [parseFloat($('#grcalc%d').val()) - {$_SESSION['gramLimit']}, parseFloat($('#grcalc%d').val()) + {$_SESSION['gramLimit']}];
		  }
		});
	
  }); // end ready
</script>
",  //KONSTANT CODE UPDATE BEGIN
	  $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $name, $growtype, $flowertype, $i, $i, $price, $i, $i, $savedGrams[$i], $i, $i, $savedEuro[$i], $i, $i, $savedrealGrams[$i], $i, $i, $savedGrams2[$i],$i, $i, $i, $name, $i, $product['purchaseid'], $i, $i, $categoryid, $i, $i, $product['productid'], $i, $i, $i, $discountType ,$i, $i, $dispDiscount1, $i, $i, $i, $i, $i, $i, $i, $i
	  );
// KONSTANT CODE UPDATE END
                      
	  echo $product_row;
  } else {
	  
		if ($_SESSION['showStock'] == 1) {
			$stockDisplay = "<img src='images/box-small.png' style='margin-bottom: -3px; margin-right: 5px;' />$estStock g";
		}
		
	$product_row =	sprintf("
<script>
    $(document).ready(function() {
   function compute() {
	   	  $(this).val($(this).val().replace(/,/g, '.'));
 	   	  $(this).val($(this).val().replace(' ', ''));
	   	  $(this).val($(this).val().replace('g', ''));
         var a = $('#ppgcalc%d').val();
          var b = $('#grcalc%d').val();
          if (b != 0 || b != '') {
          var total = a * b;
          var roundedtotal = total.toFixed(2);
          	$('#eurcalc%d').val(roundedtotal);
                // KONSTANT CODE UPDATE BEGIN
		        var discountAmount = $('#discount_%d_amount').delay(700).val();
		        if(discountAmount != '' && discountAmount != 0){
		          var roundedtotal = parseFloat(discountAmount).toFixed(2);
		          $('#eurcalc%d').val(roundedtotal);
		        }
                // KONSTANT CODE UPDATE END
      	  } else {
          	$('#eurcalc%d').val('');
          	$('#grcalc%d').val('');
          	$('#realgrcalc%d').val('');
      	  }
        }
   function compute2() {
	   	  $(this).val($(this).val().replace(/,/g, '.'));
          var a = $('#eurcalc%d').val();
          var b = $('#ppgcalc%d').val();
          if (a != 0 || a != '') {
          var total = a / b;
          var roundedtotal = total.toFixed(2);
          	$('#grcalc%d').val(roundedtotal);
                // KONSTANT CODE UPDATE BEGIN
	            var discount_quant = $('#discount_%d_quantity').delay(700).val();
	            if(discount_quant != '' && discount_quant != 0){
	              var roundedtotal = parseFloat(discount_quant).toFixed(2);
	              $('#grcalc%d').val(roundedtotal);
	            }
                // KONSTANT CODE UPDATE END
      	  } else {
          	$('#eurcalc%d').val('');
          	$('#grcalc%d').val('');
          	$('#realgrcalc%d').val('');
      	  }
        }
   function compute3() {
	   	  $(this).val($(this).val().replace(/,/g, '.'));
	   	  $(this).val($(this).val().replace(' ', ''));
	   	  $(this).val($(this).val().replace('g', ''));
        }
        // KONSTANT CODE UPDATE BEGIN
        function applyVolumeDiscount(){
            var a = $('#ppgcalc%d').val();
            var b = $('#grcalc%d').val();
            var user_id = '{$user_id}';
            if (b != 0 || b != '') {
                var purId = $('#sales_%d_purchaseid').val();
                $.ajax({
                    type:'get',
                    url:'get-volume-discount.php?id='+purId+'&qty='+b+'&uid='+user_id,
                    dataType:'json',
                    success:function(data)
                    {
	                 if(data != null){
	                    	var firstprice = data.price;
	                    	var discountPercent = data.discount_per;
	                    	var discountType = data.discount_type;
	                        var roundedtotal = parseFloat(firstprice).toFixed(2);
	                        $('#discount_%d_type').val(discountType);
	                        $('#discount_%d_percent').val(discountPercent);
	                        $('#discount_%d_amount').val(roundedtotal);
	                  	    $('#eurcalc%d').val(roundedtotal);
	                    }else{
	                       	$('#discount_%d_type').val('');
	                        $('#discount_%d_percent').val('');
	                        $('#discount_%d_amount').val('');
	                    }
                    }
                }); 
            }else{
               	$('#discount_%d_type').val('');
                $('#discount_%d_percent').val('');
                $('#discount_%d_amount').val('');
            }
        }
	    function applyVolumeDiscountAmount(){
	        var a = $('#ppgcalc%d').val();
	        var b = $('#eurcalc%d').val();
	        var user_id = '{$user_id}';
	        if (b != 0 || b != '') {
	            var purId = $('#sales_%d_purchaseid').val();
	            $.ajax({
	                type:'get',
	                url:'get-volume-discount-amount.php?id='+purId+'&amount='+b+'&uid='+user_id,
	                dataType:'json',
	                success:function(data)
	                {
	                    if(data != null){
	                    	var firstquant = data.quantity;
	                    	var discountPercent = data.discount_per;
	                    	var discountType = data.discount_type;
	                        var roundedtotal = parseFloat(firstquant).toFixed(2);
	                        $('#discount_%d_type').val(discountType);
	                        $('#discount_%d_percent').val(discountPercent);
	                        $('#discount_%d_quantity').val(roundedtotal);
	                  	    $('#grcalc%d').val(roundedtotal);
	                    }else{
	                    	$('#discount_%d_type').val('');
	                        $('#discount_%d_percent').val('');
	                        $('#discount_%d_quantity').val('');
	                    }
	                }
	            }); 
	        }else{
		        $('#discount_%d_type').val('');
                $('#discount_%d_percent').val('');
                $('#discount_%d_quantity').val('');
	        }
	    }
        $('#grcalc%d').bind('keyup blur change', applyVolumeDiscount);
        $('#eurcalc%d').bind('keyup blur change', applyVolumeDiscountAmount);
        // KONSTANT CODE UPDATE END
        $('#realgrcalc%d').bind('keypress keyup blur change', compute3);
        $('#grcalc%d').bind('keypress keyup blur change', compute);
        $('#eurcalc%d').bind('keypress keyup blur change', compute2);
        $('#grcalcB%d').bind('keypress keyup blur change', compute3);
        
		$('#free%d').click(function () {
		$('#grcalcB%d').css('display', 'inline-block');
		$('#free%d').css('display', 'none');
		});	
		$('#zero%d').click(function () {
          $('#grcalc%d').val('');
          $('#eurcalc%d').val('');
          $('#grcalcB%d').val('');
          $('#realgrcalc%d').val('');
var sum = 0;
$( '.calc' ).each( function( i , e ) {
    var v = +$( e ).val();
    if ( !isNaN( v ) )
        sum += v;
} );
  var rsum = sum.toFixed(2);
  $('#grcalcTOT').val(rsum);
  $('#grcalcTOT2').val(rsum);
  $('#grcalcTOTexp').val(rsum);
var sumB = 0;
$( '.calc2' ).each( function( i , e ) {
    var vB = +$( e ).val() ;
    if ( !isNaN( vB ) )
        sumB += vB;
} );
  var rsumB = sumB.toFixed(2);
		var newPrice = rsumB;		
  $('#eurcalcTOT').val(newPrice);
  $('#eurcalcTOT2').val(newPrice);
  $('#eurcalcTOTexp').val(newPrice);
  
  
var sumC = 0;
$( '.calc3' ).each( function( i , e ) {
    var vC = +$( e ).val();
    if ( !isNaN( vC ) )
        sumC += vC;
} );
$( '.calc4' ).each( function( i , e ) {
    var vD = +$( e ).val();
    if ( !isNaN( vD ) )
        sumC += vD;
} );
  var sumC = sumC.toFixed(2);
  $('#unitcalcTOT').val(sumC);
  $('#unitcalcTOT2').val(sumC);
  
  
var sumE = 0;
$( '.calc5' ).each( function( i , e ) {
    var vE = +$( e ).val();
    if ( !isNaN( vE ) )
        sumE += vE;
} );
var sumF = 0;
$( '.calc6' ).each( function( i , e ) {
    var vF = +$( e ).val();
    if ( !isNaN( vF ) )
        sumF += vF;
} );
	  sumF += sumE;
  $('#realgrcalcTOT').val(sumF);
          var a = $('#realCredit').val();
          var b = $('#eurcalcTOT').val();
	      var total = ((a*1) - (b*1));
          var roundedtotal = total.toFixed(2);
          $('#newcredit').val(roundedtotal);
          $('#newcredit2').val(roundedtotal);
	      $('#realNewCredit').val(roundedtotal);
		});	
  }); // end ready
        </script>
        
	   <tr class='$trclass'>
	    <td class='left'><strong>%s</strong></td>
	    <td class='left'>%s<br>%s <br /> $properties</td>
	    <td>
		    <span class='relativeitem'>$commentRead</span>&nbsp;
		    	<span class='relativeitem'>$commentReadM</span>&nbsp;&nbsp;&nbsp;
		    	<strong style='font-size: 15px;'>%0.02f {$_SESSION['currencyoperator']}</strong> <input type='hidden' id='ppgcalc%d' name='sales[%d][ppg]' value='%0.02f' />&nbsp;&nbsp;
		    	<span style='font-size: 15px;' class='firsttext'>$normalPrice</span>&nbsp;&nbsp;&nbsp;	    
		    	<span class='smallgreen' style='text-transform: none;'>$stockDisplay</span><br>
		    	<input type='text' lang='nb' class='twoDigit defaultinput centered calc onScreenKeypad c$purchaseid' id='grcalc%d' name='sales[%d][grams]' placeholder='Gr.' step='0.01' value='%s' />
		    	<input type='text' lang='nb' class='twoDigit defaultinput centered calc2 onScreenKeypad'  id='eurcalc%d' name='sales[%d][euro]' placeholder='{$_SESSION['currencyoperator']}' step='0.01' value='%s' />
		    	<input type='text' lang='nb' class='fourDigit defaultinput centered calc5' id='realgrcalc%d' name='sales[%d][realGrams]' placeholder='Gr. real' step='0.01'  value='%s' /> 
		    	<input type='text' lang='nb' class='twoDigit defaultinput centered calc calc6 onScreenKeypad' id='grcalcB%d' name='sales[%d][grams2]' placeholder='Gr.' step='0.01' value='%s' style='background-color: #f4b146; $displayGift'  />$giftOption
		    	&nbsp;&nbsp;<img src='images/delete.png' width='17' style='cursor: pointer;' class='remove_pr' id='zero%d' />
	    	</td>
	   </tr>
  	  <input type='hidden' name='sales[%d][name]' value='%s' />
          <!-- // KONSTANT CODE UPDATE BEGIN -->
  	  <input type='hidden' name='sales[%d][purchaseid]' value='%d' id='sales_%d_purchaseid' />
  	  <input type='hidden' name='sales[%d][category]' value='%d' id='sales_%d_category' />
  	  <input type='hidden' name='sales[%d][productid]' value='%d' id='sales_%d_productid' />
  	  <input type='hidden' name='sales[%d][discType]' id='discount_%d_type' value='%d'/>
  	  <input type='hidden' name='sales[%d][discPercentage]' id='discount_%d_percent' value='%0.02f'/>
  	  <input type='hidden' name='sales[%d][discAmount]' id='discount_%d_amount'/>
  	  <input type='hidden' name='sales[%d][discQuant]' id='discount_%d_quantity'/>
          <!-- // KONSTANT CODE UPDATE END -->
<script>
    $(document).ready(function() {
	    
		$('#realgrcalc%d').rules('add', {
		  required: function(element){
	            return $('#grcalc%d').val()!='';
	        },
		  number: true,
		  range: function() {
		      return [parseFloat($('#grcalc%d').val()) - {$_SESSION['gramLimit']}, parseFloat($('#grcalc%d').val()) + {$_SESSION['gramLimit']}];
		  }
		});
	
  }); // end ready
</script>
",  //KONSTANT CODE UPDATE BEGIN
	  $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $name, $growtype, $flowertype, $price, $i, $i, $price, $i, $i, $savedGrams[$i], $i, $i, $savedEuro[$i], $i, $i, $savedrealGrams[$i], $i, $i, $savedGrams2[$i], $i, $i, $i, $name, $i, $product['purchaseid'], $i, $i, $categoryid, $i, $i, $product['productid'], $i, $i, $i, $discountType ,$i, $i, $dispDiscount1, $i, $i, $i, $i, $i, $i, $i, $i
	  );
// KONSTANT CODE UPDATE END
	  echo $product_row;
	  
  }
	  
  		} // End gram vs unit cat
	  } // End individual product display, loop back to next product
	} // End real vs fake grams
	  
	  
	  
		
	if ($menuType == 1) {
		echo "</tbody></table></div><div class='leftfloat'><table class='default nonhover'>";
	}
echo "<script>$('#currenti').val($i);";
		if ($_SESSION['keypads'] == 1) {
			
			echo <<<EOD
$('.onScreenKeypad').keypad({
    layout: ['789' + $.keypad.CLOSE, 
        '456' + $.keypad.CLEAR, 
        '123' + $.keypad.BACK, 
        '.0' + $.keypad.SPACE]});			
EOD;
		}
echo <<<EOD
</script>
EOD;

}