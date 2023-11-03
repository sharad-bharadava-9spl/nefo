<?php 

	require_once 'cOnfig/connection.php';
	require_once 'cOnfig/languages/common.php';

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
			echo "<table class='default nonhover'><tbody>";
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
		
		$normalPrice = "({$product['salesPrice']} &euro;)";
		
	} else {
	
		$normalPrice = "";
		
	}
	
        // KONSTANT CODE UPDATE BEGIN
	// Usergroup discont apply
	$usergroupDiscount = "SELECT users.usergroup2,usergroup_discounts.discount_price,usergroup_discounts.discount_percentage FROM users LEFT JOIN usergroup_discounts on usergroup_discounts.usergroup_id = users.usergroup2 WHERE user_id = $user_id";
        try
        {
            $result = $pdo3->prepare("$usergroupDiscount");
            $result->execute();
        }
        catch (PDOException $e)
        {
            $error = 'Error fetching user: ' . $e->getMessage();
            echo $error;
            exit();
        }
        $rowDU = $result->fetch();
        $dispDiscount = 0;

        // Individual purchase discount
        $dispDiscount1 = 0;
	$selectPurchaseDiscount = "SELECT discount, fijo FROM inddiscounts WHERE user_id = $user_id AND purchaseid = $purchaseid";
		try
		{
			$result = $pdo3->prepare("$selectPurchaseDiscount");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$rowD = $result->fetch();
		$prodDiscount = $rowD['discount'];
		$prodFijo = $rowD['fijo'];
	
	if ($prodFijo != 0 || $prodFijo != '') {
		$dispDiscount1 = number_format((($prodFijo * 100) / $product['salesPrice']),2);
		$price = number_format($prodFijo,2);
		
	} else if ($prodDiscount != 0 || $prodDiscount != '') {
		$dispDiscount1 = $prodDiscount;
		$prodDiscount = (100 - $prodDiscount) / 100;
		$price = number_format($product['salesPrice'] * $prodDiscount,2);
		
	} else if ($catDiscount != 0 || $catDiscount != '') {
		$dispDiscount1 = $catDiscount;
		$catCurrentDiscount = (100 - $catDiscount) / 100;
		$price = number_format($product['salesPrice'] * $catCurrentDiscount,2);

	} else if ($discount != 0) {
		$dispDiscount1 = $discount;
		$dispDiscount = (100 - $discount) / 100;
		$price = number_format($product['salesPrice'] * $dispDiscount,2);
		
	} else if ($usageType == '1') {
		
		if ($product['medDiscount'] > 0) {
        		$dispDiscount1 = $product['medDiscount'];
			$dispDiscount = (100 - $product['medDiscount']) / 100;
			$price = number_format($product['salesPrice'] * $dispDiscount,2);
		} else if ($medicalDiscountPercentage == 1) {
        		$dispDiscount1 = $medicalDiscountCalc;
			$price = number_format(($product['salesPrice']) * $medicalDiscountCalc,2);
		} else {
        		$dispDiscount1 = number_format((($medicalDiscount * 100) / $product['salesPrice']),2);
			$price = number_format(($product['salesPrice']) - $medicalDiscount,2);
		}
	} else if (isset($rowDU['discount_percentage'])){
            if(isset($rowDU['discount_percentage']) && $rowDU['discount_percentage'] > 0){
                $dispDiscount1 = $rowDU['discount_percentage'];
                $dispDiscount = (100 - $rowDU['discount_percentage']) / 100;
                $price = number_format($product['salesPrice'] * $dispDiscount,2);
            }else{
                if(isset($rowDU['discount_price']) && $rowDU['discount_price'] > 0){
                    $dispDiscount1 = number_format(($rowDU['discount_price'] * 100) / $product['salesPrice'],2);
                    $price = number_format(($product['salesPrice']) - $rowDU['discount_price'],2);
                }
            }	
	} else {
		
		$price = number_format($product['salesPrice'],2);
		
	}        

        
        // KONSTANT CODE UPDATE BEGIN
        //Start happy hour discount
        $hHFlag = 0;
        $getDay = date('l').'s';
        $getTime = date('h:i:s');
	$hHDiscount = "SELECT * FROM global_happy_hour_discounts where discount_date = 'Every day' and time_from <= '".$getTime."' and time_to > '".$getTime."'";
        try
        {
            $result = $pdo3->prepare("$hHDiscount");
            $result->execute();
        }
        catch (PDOException $e)
        {
            $error = 'Error fetching user: ' . $e->getMessage();
            echo $error;
            exit();
        }
        $happy_hour_id = 0;
        while ($hHDiscount = $result->fetch()) {
            $happy_hour_id = $hHDiscount['id'];
            if(isset($hHDiscount['discount']) && $hHDiscount['discount'] > 0){
                $dispDiscount1 = $hHDiscount['discount'];
                $dispDiscount = (100 - $hHDiscount['discount']) / 100;
                $price = number_format($product['salesPrice'] * $dispDiscount,2);
            }
        }
        //End happy hour discount
        
	$hHDiscount = "SELECT * FROM global_happy_hour_discounts where discount_date = '$getDay' and time_from <= '".$getTime."' and time_to > '".$getTime."'";
        try
        {
            $result = $pdo3->prepare("$hHDiscount");
            $result->execute();
        }
        catch (PDOException $e)
        {
            $error = 'Error fetching user: ' . $e->getMessage();
            echo $error;
            exit();
        }
        while ($hHDiscount = $result->fetch()) {
            $happy_hour_id = $hHDiscount['id'];
            if(isset($hHDiscount['discount']) && $hHDiscount['discount'] > 0){
                $dispDiscount1 = $hHDiscount['discount'];
                $dispDiscount = (100 - $hHDiscount['discount']) / 100;
                $price = number_format($product['salesPrice'] * $dispDiscount,2);
            }
        }
        if($happy_hour_id){
            $selectCategoryDiscount = "SELECT discount FROM catdiscounts WHERE categoryid = $categoryid and happy_hour_id = ".$happy_hour_id;
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
            if ($catDiscount != 0 || $catDiscount != '') {
		$catCurrentDiscount = (100 - $catDiscount) / 100;
                if($catDiscount > $dispDiscount1){
                    $price = number_format($product['salesPrice'] * $catCurrentDiscount,2);
                }
            }    



            $selectPurchaseDiscount = "SELECT discount, fijo FROM inddiscounts WHERE purchaseid = $purchaseid and happy_hour_id = $happy_hour_id";
            try
            {
                    $result = $pdo3->prepare("$selectPurchaseDiscount");
                    $result->execute();
            }
            catch (PDOException $e)
            {
                            $error = 'Error fetching user: ' . $e->getMessage();
                            echo $error;
                            exit();
            }

            $rowD = $result->fetch();
            $prodDiscountHD = $rowD['discount'];
            $prodFijoHD = $rowD['fijo'];

            if ($prodFijoHD != 0 || $prodFijoHD != '') {
                $prodFijoHD1 = number_format((($prodFijoHD * 100) / $product['salesPrice']),2);
                if ($prodFijoHD1 > $dispDiscount1){
                    //$prodDiscountHD = (100 - $prodFijoHD1) / 100;
                    //$price = number_format($product['salesPrice'] * $prodDiscountHD,2);
                    $dispDiscount1 = number_format((($prodFijoHD * 100) / $product['salesPrice']),2);
                    $price = number_format($prodFijoHD,2);
                }
            } else if ($prodDiscountHD != 0 || $prodDiscountHD != '') {
                if($prodDiscountHD > $prodDiscount && $prodDiscountHD > $dispDiscount1){
                    $prodDiscountHD = (100 - $prodDiscountHD) / 100;
                    $price = number_format($product['salesPrice'] * $prodDiscountHD,2);
                }
            }
        }
        // KONSTANT CODE UPDATE END
	
	 		$i++;
	 		
	 		
			$productid = $product['productid'];
			$productName = $product['name'];
			
				if (($savedSale[$i][grams]) == 0) {
		$savedGrams[$i] = '';
	} else {
		$savedGrams[$i] = $savedSale[$i][grams];
	}
	
	if (($savedSale[$i][euro]) == 0) {
		$savedEuro[$i] = '';
	} else {
		$savedEuro[$i] = $savedSale[$i][euro];
	}
	
	if (($savedSale[$i][grams2]) == 0) {
		$savedGrams2[$i] = '';
		$displayGift = 'display: none;';
			
			if ($_SESSION['dispensaryGift'] == 1 && $_SESSION['menuType'] == 1) {
				$giftOption = "<img src='images/free-green.png' id='free%d' style='margin-bottom: 0; margin-left: 5px;' />";
			} else if ($_SESSION['dispensaryGift'] == 1 && $_SESSION['menuType'] == 0) {
				$giftOption = "<img src='images/free.png' id='free%d' style='margin-bottom: 0; margin-left: 5px;' />";
			} else {
				$giftOption = "<input type='hidden' value='%d' />";
			}
	} else {
		$savedGrams2[$i] = $savedSale[$i][grams2];
		$displayGift = '';
		$giftOption = '';
	}

	if ($product['description'] != '') {
		
		$commentRead = "
		                <img src='images/info2.png' style='inline-block; margin-top: 5px; margin-left: 5px;' id='comment$i' onClick='showhide$i()' /><div id='helpBox$i' class='helpBox'><strong>{$lang['extracts-description']}:</strong><br />{$product['description']}</div>
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
		
		$commentRead = "<img src='images/info2.png' style='visibility: hidden;' />";
		
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
                var volDiscount = $('#volume_%d_discount').val();
                if(volDiscount != ''){
                  var roundedtotal = parseFloat(volDiscount).toFixed(2);
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
                var volDiscount = $('#volume_%d_discount').val();
                if(volDiscount != ''){
                  var roundedtotal = parseFloat(volDiscount).toFixed(2);
                  $('#grcalc%d').val(roundedtotal);
                }
                // KONSTANT CODE UPDATE END
                
      	  } else {
          	$('#eurcalc%d').val('');
          	$('#grcalc%d').val('');
      	  }
        }
        // KONSTANT CODE UPDATE BEGIN
        function applyVolumeDiscount(){
            var a = $('#ppgcalc%d').val();
            var b = $('#grcalc%d').val();
            if (b != 0 || b != '') {
                var purId = $('#sales_%d_purchaseid').val();
                $.ajax({
                    type:'get',
                    url:'get-volume-discount.php?id='+purId+'&qty='+b,
                    datatype:'text',
                    success:function(data)
                    {
                        if(data){
                            var roundedtotal = parseFloat(data).toFixed(2);
                            $('#volume_%d_discount').val(roundedtotal);
                            $('#eurcalc%d').val(roundedtotal);
                        }else{
                            $('#volume_%d_discount').val('');
                        }
                    }
                }); 
            }
        }
        $('#grcalc%d').bind('keyup', applyVolumeDiscount);
        // KONSTANT CODE UPDATE END

        $('#grcalc%d').bind('keypress keyup blur change', compute);
        $('#eurcalc%d').bind('keypress keyup blur change', compute2);
        
		$('#free%d').click(function () {
		$('#grcalcB%d').css('display', 'inline-block');
		$('#free%d').css('display', 'none');
		});	


  }); // end ready

        </script>
	<div class='displaybox'>
	 <div id='imageholder'>
	  <center><img src='images/_$domain/purchases/%s' /></center>
	 </div>
	 <h3>%s</h3>
	 <span class='descprice relative'>
	  <br />$commentRead $commentReadM
	 </span>
	 <input class='specialInput' id='ppgcalc%d' name='sales[%d][ppg]' value='%0.02f' readonly /><span class='eurosign'>&euro;</span><br /><span style='color: yellow;' class='biggerfont'>$normalPrice</span><br />$stockDisplay
	 <div class='clearfloat'></div><br />
	 <center>
  	  <input type='text' lang='nb' class='fourDigit centered calc3 onScreenKeypad c$purchaseid' id='grcalc%d' name='sales[%d][grams]' placeholder='#' step='0.01' value='%s' />
  	  <input type='text' lang='nb' class='fourDigit centered calc2 onScreenKeypad' id='eurcalc%d' name='sales[%d][euro]' placeholder='&euro;' step='0.01' value='%s' />
  	  <input type='text' lang='nb' class='fourDigit centered calc4 onScreenKeypad' id='grcalcB%d' name='sales[%d][grams2]' placeholder='#' step='0.01' value='%s' style='background-color: yellow; $displayGift'  /> $giftOption
  	  <input type='hidden' name='sales[%d][name]' value='%s' />
          <!-- // KONSTANT CODE UPDATE BEGIN -->
  	  <input type='hidden' name='sales[%d][purchaseid]' value='%d' id='sales_%d_purchaseid' />
  	  <input type='hidden' name='sales[%d][category]' value='%d' id='sales_%d_category' />
  	  <input type='hidden' name='sales[%d][productid]' value='%d' id='sales_%d_productid' />
  	  <input type='hidden' name='sales[%d][volume_discount]' id='volume_%d_discount' />
          <!-- // KONSTANT CODE UPDATE END -->
  	 </center>
	</div>", //KONSTANT CODE UPDATE BEGIN 
	  $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $product['purchaseid'] . '.' . $product['photoExt'], $product['name'], $i, $i, $price, $i, $i, $savedGrams[$i], $i, $i, $savedEuro[$i], $i, $i, $savedGrams2[$i], $i, $i, $name, $i, $product['purchaseid'], $i, $i, $categoryid, $i, $i, $product['productid'], $i, $i, $i
	  );
// KONSTANT CODE UPDATE END
        
  } else {
	  
		if ($_SESSION['showStock'] == 1) {
			$stockDisplay = "<td class='right'>$estStock u</td>";
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
                var volDiscount = $('#volume_%d_discount').val();
                if(volDiscount != ''){
                  var roundedtotal = parseFloat(volDiscount).toFixed(2);
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
                var volDiscount = $('#volume_%d_discount').val();
                if(volDiscount != ''){
                  var roundedtotal = parseFloat(volDiscount).toFixed(2);
                  $('#grcalc%d').val(roundedtotal);
                }
                // KONSTANT CODE UPDATE END
      	  } else {
          	$('#eurcalc%d').val('');
          	$('#grcalc%d').val('');
      	  }
        }
        // KONSTANT CODE UPDATE BEGIN
        function applyVolumeDiscount(){
            var a = $('#ppgcalc%d').val();
            var b = $('#grcalc%d').val();
            if (b != 0 || b != '') {
                var purId = $('#sales_%d_purchaseid').val();
                $.ajax({
                    type:'get',
                    url:'get-volume-discount.php?id='+purId+'&qty='+b,
                    datatype:'text',
                    success:function(data)
                    {
                        if(data){
                            var roundedtotal = parseFloat(data).toFixed(2);
                            $('#volume_%d_discount').val(roundedtotal);
                            $('#eurcalc%d').val(roundedtotal);
                        }else{
                            $('#volume_%d_discount').val('');
                        }
                    }
                }); 
            }
        }
        $('#grcalc%d').bind('keyup', applyVolumeDiscount);
        // KONSTANT CODE UPDATE END

        $('#grcalc%d').bind('keypress keyup blur change', compute);
        $('#eurcalc%d').bind('keypress keyup blur change', compute2);
        
		$('#free%d').click(function () {
		$('#grcalcB%d').css('display', 'inline-block');
		$('#free%d').css('display', 'none');
		});	

  }); // end ready
        </script>
        
	   <tr>
	    <td class='left'>%s</td>
	    <td class='centered relative' style='padding: 11px 0;'>$commentRead</td>
	    <td class='centered relative' style='padding: 11px 0;'>$commentReadM</td>
	    <td class='right'>%0.02f &euro; <input type='hidden' id='ppgcalc%d' name='sales[%d][ppg]' value='%0.02f' /></td>
	    <td class='right'><span style='color: red;' class='smallerfont'>$normalPrice</span></td>
	    <td class='right'>$stockDisplay</td>
	    <td><input type='text' lang='nb' class='twoDigit centered calc3 onScreenKeypad c$purchaseid' style='border: #5aa242 solid 2px; width: 50px; border-radius: 2px; padding: 2px 1px;' id='grcalc%d' name='sales[%d][grams]' placeholder='#' step='0.01' value='%s' /></td>
	    <td><input type='text' lang='nb' class='twoDigit centered calc2 onScreenKeypad' style='border: #5aa242 solid 2px; width: 50px; border-radius: 2px; padding: 2px 1px;' id='eurcalc%d' name='sales[%d][euro]' placeholder='&euro;' step='0.01' value='%s' /></td>
	    <td><input type='text' lang='nb' class='twoDigit centered calc4 onScreenKeypad' id='grcalcB%d' name='sales[%d][grams2]' placeholder='#' step='0.01' value='%s' style='border: #5aa242 solid 2px; width: 50px; border-radius: 2px; padding: 2px 1px; background-color: yellow; $displayGift'  />$giftOption</td>
	   </tr>
  	  <input type='hidden' name='sales[%d][name]' value='%s' />
          <!-- // KONSTANT CODE UPDATE BEGIN -->
  	  <input type='hidden' name='sales[%d][purchaseid]' value='%d' id='sales_%d_purchaseid' />
  	  <input type='hidden' name='sales[%d][category]' value='%d' id='sales_%d_category' />
  	  <input type='hidden' name='sales[%d][productid]' value='%d' id='sales_%d_productid' />
  	  <input type='hidden' name='sales[%d][volume_discount]' id='volume_%d_discount' />
          <!-- // KONSTANT CODE UPDATE END -->
",  //KONSTANT CODE UPDATE BEGIN 
	  $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $product['purchaseid'] . '.' . $product['photoExt'], $product['name'], $i, $i, $price, $i, $i, $savedGrams[$i], $i, $i, $savedEuro[$i], $i, $i, $savedGrams2[$i], $i, $i, $name, $i, $product['purchaseid'], $i, $i, $categoryid, $i, $i, $product['productid'], $i, $i, $i
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
                var volDiscount = $('#volume_%d_discount').val();
                if(volDiscount != ''){
                  var roundedtotal = parseFloat(volDiscount).toFixed(2);
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
                var volDiscount = $('#volume_%d_discount').val();
                if(volDiscount != ''){
                  var roundedtotal = parseFloat(volDiscount).toFixed(2);
                  $('#grcalc%d').val(roundedtotal);
                }
                // KONSTANT CODE UPDATE END
      	  } else {
          	$('#eurcalc%d').val('');
          	$('#grcalc%d').val('');
      	  }
        }
        // KONSTANT CODE UPDATE BEGIN
        function applyVolumeDiscount(){
            var a = $('#ppgcalc%d').val();
            var b = $('#grcalc%d').val();
            if (b != 0 || b != '') {
                var purId = $('#sales_%d_purchaseid').val();
                $.ajax({
                    type:'get',
                    url:'get-volume-discount.php?id='+purId+'&qty='+b,
                    datatype:'text',
                    success:function(data)
                    {
                        if(data){
                            var roundedtotal = parseFloat(data).toFixed(2);
                            $('#volume_%d_discount').val(roundedtotal);
                            $('#eurcalc%d').val(roundedtotal);
                        }else{
                            $('#volume_%d_discount').val('');
                        }
                    }
                }); 
            }
        }
        $('#grcalc%d').bind('keyup', applyVolumeDiscount);
        // KONSTANT CODE UPDATE END

        $('#grcalc%d').bind('keypress keyup blur change', compute);
        $('#eurcalc%d').bind('keypress keyup blur change', compute2);
        
		$('#free%d').click(function () {
		$('#grcalcB%d').css('display', 'inline-block');
		$('#free%d').css('display', 'none');
		});	


  }); // end ready

        </script>
	<div class='displaybox'>
	 <div id='imageholder'>
	  <center><img src='images/_$domain/purchases/%s' /></center>
	 </div>
	 <h3>%s</h3>
	 <span class='descprice relative'>
	  <br />$commentRead $commentReadM
	 </span>
	 <input class='specialInput' id='ppgcalc%d' name='sales[%d][ppg]' value='%0.02f' readonly /><span class='eurosign'>&euro;</span><br /><span style='color: yellow;' class='biggerfont'>$normalPrice</span><br />$stockDisplay
	 <div class='clearfloat'></div><br />
	 <center>
  	  <input type='text' lang='nb' class='fourDigit centered calc onScreenKeypad c$purchaseid' id='grcalc%d' name='sales[%d][grams]' placeholder='Gr.' step='0.01' value='%s' />
  	  <input type='text' lang='nb' class='fourDigit centered calc2 onScreenKeypad' id='eurcalc%d' name='sales[%d][euro]' placeholder='&euro;' step='0.01' value='%s' />
  	  <input type='text' lang='nb' class='fourDigit centered calc onScreenKeypad' id='grcalcB%d' name='sales[%d][grams2]' placeholder='Gr.' step='0.01' value='%s' style='background-color: yellow; $displayGift'  /> $giftOption
  	  <input type='hidden' name='sales[%d][name]' value='%s' />
          <!-- // KONSTANT CODE UPDATE BEGIN -->
  	  <input type='hidden' name='sales[%d][purchaseid]' value='%d' id='sales_%d_purchaseid' />
  	  <input type='hidden' name='sales[%d][category]' value='%d' id='sales_%d_category' />
  	  <input type='hidden' name='sales[%d][productid]' value='%d' id='sales_%d_productid' />
  	  <input type='hidden' name='sales[%d][volume_discount]' id='volume_%d_discount' />
          <!-- // KONSTANT CODE UPDATE END -->
  	 </center>
	</div>", //KONSTANT CODE UPDATE BEGIN 
	  $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $product['purchaseid'] . '.' . $product['photoExt'], $product['name'], $i, $i, $price, $i, $i, $savedGrams[$i], $i, $i, $savedEuro[$i], $i, $i, $savedGrams2[$i], $i, $i, $name, $i, $product['purchaseid'], $i, $i, $categoryid, $i, $i, $product['productid'], $i, $i, $i
	  );
// KONSTANT CODE UPDATE END
        
	  echo $product_row;
	  
  } else {
	  
		if ($_SESSION['showStock'] == 1) {
			$stockDisplay = "<td class='right'>$estStock g</td>";
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
                var volDiscount = $('#volume_%d_discount').val();
                if(volDiscount != ''){
                  var roundedtotal = parseFloat(volDiscount).toFixed(2);
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
                var volDiscount = $('#volume_%d_discount').val();
                if(volDiscount != ''){
                  var roundedtotal = parseFloat(volDiscount).toFixed(2);
                  $('#grcalc%d').val(roundedtotal);
                }
                // KONSTANT CODE UPDATE END
      	  } else {
          	$('#eurcalc%d').val('');
          	$('#grcalc%d').val('');
      	  }
        }
        // KONSTANT CODE UPDATE BEGIN
        function applyVolumeDiscount(){
            var a = $('#ppgcalc%d').val();
            var b = $('#grcalc%d').val();
            if (b != 0 || b != '') {
                var purId = $('#sales_%d_purchaseid').val();
                $.ajax({
                    type:'get',
                    url:'get-volume-discount.php?id='+purId+'&qty='+b,
                    datatype:'text',
                    success:function(data)
                    {
                        if(data){
                            var roundedtotal = parseFloat(data).toFixed(2);
                            $('#volume_%d_discount').val(roundedtotal);
                            $('#eurcalc%d').val(roundedtotal);
                        }else{
                            $('#volume_%d_discount').val('');
                        }
                    }
                }); 
            }
        }
        $('#grcalc%d').bind('keyup', applyVolumeDiscount);
        // KONSTANT CODE UPDATE END

        $('#grcalc%d').bind('keypress keyup blur change', compute);
        $('#eurcalc%d').bind('keypress keyup blur change', compute2);
        
		$('#free%d').click(function () {
		$('#grcalcB%d').css('display', 'inline-block');
		$('#free%d').css('display', 'none');
		});	

  }); // end ready
        </script>
        
	   <tr>
	    <td class='left'>%s</td>
	    <td class='centered relative' style='padding: 11px 0;'>$commentRead</td>
	    <td class='centered relative' style='padding: 11px 0;'>$commentReadM</td>
	    <td class='right'>%0.02f &euro; <input type='hidden' id='ppgcalc%d' name='sales[%d][ppg]' value='%0.02f' /></td>
	    <td class='right'><span style='color: red;' class='smallerfont'>$normalPrice</span></td>	    
	    <td class='right'>$stockDisplay</td>
	    <td><input type='text' lang='nb' class='twoDigit centered calc onScreenKeypad c$purchaseid' style='border: #5aa242 solid 2px; width: 50px; border-radius: 2px; padding: 2px 1px;' id='grcalc%d' name='sales[%d][grams]' placeholder='Gr.' step='0.01' value='%s' /></td>
	    <td><input type='text' lang='nb' class='twoDigit centered calc2 onScreenKeypad' style='border: #5aa242 solid 2px; width: 50px; border-radius: 2px; padding: 2px 1px;' id='eurcalc%d' name='sales[%d][euro]' placeholder='&euro;' step='0.01' value='%s' /></td>
	    <td><input type='text' lang='nb' class='twoDigit centered calc onScreenKeypad' id='grcalcB%d' name='sales[%d][grams2]' placeholder='Gr.' step='0.01' value='%s' style='border: #5aa242 solid 2px; width: 50px; border-radius: 2px; padding: 2px 1px; background-color: yellow; $displayGift'  />$giftOption</td>
	   </tr>
  	  <input type='hidden' name='sales[%d][name]' value='%s' />
          <!-- // KONSTANT CODE UPDATE BEGIN -->
  	  <input type='hidden' name='sales[%d][purchaseid]' value='%d' id='sales_%d_purchaseid' />
  	  <input type='hidden' name='sales[%d][category]' value='%d' id='sales_%d_category' />
  	  <input type='hidden' name='sales[%d][productid]' value='%d' id='sales_%d_productid' />
  	  <input type='hidden' name='sales[%d][volume_discount]' id='volume_%d_discount' />
          <!-- // KONSTANT CODE UPDATE END -->
",  //KONSTANT CODE UPDATE BEGIN
	  $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $product['purchaseid'] . '.' . $product['photoExt'], $product['name'], $i, $i, $price, $i, $i, $savedGrams[$i], $i, $i, $savedEuro[$i], $i, $i, $savedGrams2[$i], $i, $i, $name, $i, $product['purchaseid'], $i, $i, $categoryid, $i, $i, $product['productid'], $i, $i, $i
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
                var volDiscount = $('#volume_%d_discount').val();
                if(volDiscount != ''){
                  var roundedtotal = parseFloat(volDiscount).toFixed(2);
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
                var volDiscount = $('#volume_%d_discount').val();
                if(volDiscount != ''){
                  var roundedtotal = parseFloat(volDiscount).toFixed(2);
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
            if (b != 0 || b != '') {
                var purId = $('#sales_%d_purchaseid').val();
                $.ajax({
                    type:'get',
                    url:'get-volume-discount.php?id='+purId+'&qty='+b,
                    datatype:'text',
                    success:function(data)
                    {
                        if(data){
                            var roundedtotal = parseFloat(data).toFixed(2);
                            $('#volume_%d_discount').val(roundedtotal);
                            $('#eurcalc%d').val(roundedtotal);
                        }else{
                            $('#volume_%d_discount').val('');
                        }
                    }
                }); 
            }
        }
        $('#grcalc%d').bind('keyup', applyVolumeDiscount);
        // KONSTANT CODE UPDATE END
        
        $('#realgrcalc%d').bind('keypress keyup blur change', compute3);
        $('#grcalc%d').bind('keypress keyup blur change', compute);
        $('#eurcalc%d').bind('keypress keyup blur change', compute2);
        
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
	 <div id='imageholder'>
	  <center><img src='images/_$domain/purchases/%s' /></center>
	 </div>
	 <h3>%s</h3>
	 <span class='descprice relative'>
	  <br />$commentRead $commentReadM
	 </span>
	 <input class='specialInput' id='ppgcalc%d' name='sales[%d][ppg]' value='%0.02f' readonly /><span class='eurosign'>&euro;</span><br /><span style='color: yellow;' class='biggerfont'>$normalPrice</span><br />$stockDisplay
	 <div class='clearfloat'></div><br />
	 <center>
  	  <input type='text' lang='nb' class='fourDigit centered calc onScreenKeypad c$purchaseid' id='grcalc%d' name='sales[%d][grams]' placeholder='Gr.' step='0.01' value='%s' />
  	  <input type='text' lang='nb' class='fourDigit centered calc2 onScreenKeypad' id='eurcalc%d' name='sales[%d][euro]' placeholder='&euro;' step='0.01' value='%s' />
  	  <input type='text' lang='nb' class='fourDigit centered calc calc6 onScreenKeypad' id='grcalcB%d' name='sales[%d][grams2]' placeholder='Gr.' step='0.01' value='%s' style='background-color: yellow; $displayGift'  /> $giftOption<br /><br />
  	  <input type='text' lang='nb' class='fourDigit centered calc5' id='realgrcalc%d' name='sales[%d][realGrams]' placeholder='Real gr.' step='0.01' style='width: 70px;' /> &nbsp;&nbsp;<img src='images/delete.png' width='17' style='cursor: pointer;' id='zero%d' />
  	  <input type='hidden' name='sales[%d][name]' value='%s' />
          <!-- // KONSTANT CODE UPDATE BEGIN -->
  	  <input type='hidden' name='sales[%d][purchaseid]' value='%d' id='sales_%d_purchaseid' />
  	  <input type='hidden' name='sales[%d][category]' value='%d' id='sales_%d_category' />
  	  <input type='hidden' name='sales[%d][productid]' value='%d' id='sales_%d_productid' />
  	  <input type='hidden' name='sales[%d][volume_discount]' id='volume_%d_discount' />
          <!-- // KONSTANT CODE UPDATE END -->
  	 </center>
	</div>
<script>
    $(document).ready(function() {
	    
		$('#realgrcalc%d').rules('add', {
		  required: function(element){
	            return $('#grcalc%d').val()!='';
	        },
		  number: true,
		  range: function() {
		      return [0.00, parseFloat($('#grcalc%d').val()) + {$_SESSION['extractLimit']}];
		  }
		});
	

  }); // end ready
</script>
",  //KONSTANT CODE UPDATE BEGIN
	  $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $product['purchaseid'] . '.' . $product['photoExt'], $product['name'], $i, $i, $price, $i, $i, $savedGrams[$i], $i, $i, $savedEuro[$i], $i, $i, $savedGrams2[$i], $i, $i, $i, $i, $i, $name, $i, $product['purchaseid'], $i, $i, $categoryid, $i, $i, $product['productid'], $i, $i, $i, $i, $i, $i
	  );
// KONSTANT CODE UPDATE END
                      
	  echo $product_row;
  } else {
	  
		if ($_SESSION['showStock'] == 1) {
			$stockDisplay = "<td class='right'>$estStock g</td>";
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
                var volDiscount = $('#volume_%d_discount').val();
                if(volDiscount != ''){
                  var roundedtotal = parseFloat(volDiscount).toFixed(2);
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
                var volDiscount = $('#volume_%d_discount').val();
                if(volDiscount != ''){
                  var roundedtotal = parseFloat(volDiscount).toFixed(2);
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
            if (b != 0 || b != '') {
                var purId = $('#sales_%d_purchaseid').val();
                $.ajax({
                    type:'get',
                    url:'get-volume-discount.php?id='+purId+'&qty='+b,
                    datatype:'text',
                    success:function(data)
                    {
                        if(data){
                            var roundedtotal = parseFloat(data).toFixed(2);
                            $('#volume_%d_discount').val(roundedtotal);
                            $('#eurcalc%d').val(roundedtotal);
                        }else{
                            $('#volume_%d_discount').val('');
                        }
                    }
                }); 
            }
        }
        $('#grcalc%d').bind('keyup', applyVolumeDiscount);
        // KONSTANT CODE UPDATE END
        $('#realgrcalc%d').bind('keypress keyup blur change', compute3);
        $('#grcalc%d').bind('keypress keyup blur change', compute);
        $('#eurcalc%d').bind('keypress keyup blur change', compute2);
        
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
        
	   <tr>
	    <td class='left'>%s</td>
	    <td class='centered relative' style='padding: 11px 0;'>$commentRead</td>
	    <td class='centered relative' style='padding: 11px 0;'>$commentReadM</td>
	    <td class='right'>%0.02f &euro; <input type='hidden' id='ppgcalc%d' name='sales[%d][ppg]' value='%0.02f' /></td>
	    <td class='right'><span style='color: red;' class='smallerfont'>$normalPrice</span></td>	    
	    <td class='right'>$stockDisplay</td>
	    <td><input type='text' lang='nb' class='twoDigit centered calc onScreenKeypad c$purchaseid' style='border: #5aa242 solid 2px; width: 50px; border-radius: 2px; padding: 2px 1px;' id='grcalc%d' name='sales[%d][grams]' placeholder='Gr.' step='0.01' value='%s' /></td>
	    <td><input type='text' lang='nb' class='twoDigit centered calc2 onScreenKeypad' style='border: #5aa242 solid 2px; width: 50px; border-radius: 2px; padding: 2px 1px;' id='eurcalc%d' name='sales[%d][euro]' placeholder='&euro;' step='0.01' value='%s' /></td>
	    <td><input type='text' lang='nb' class='twoDigit centered calc calc6 onScreenKeypad' id='grcalcB%d' name='sales[%d][grams2]' placeholder='Gr.' step='0.01' value='%s' style='border: #5aa242 solid 2px; width: 50px; border-radius: 2px; padding: 2px 1px; background-color: yellow; $displayGift'  />$giftOption</td>
	    <td><input type='text' lang='nb' class='fourDigit centered calc5' id='realgrcalc%d' name='sales[%d][realGrams]' placeholder='Gr. real' step='0.01' style='border: #5aa242 solid 2px; width: 50px; border-radius: 2px; padding: 2px 1px;' /> &nbsp;&nbsp;<img src='images/delete.png' width='17' style='cursor: pointer;' id='zero%d' /></td>
	   </tr>
  	  <input type='hidden' name='sales[%d][name]' value='%s' />
          <!-- // KONSTANT CODE UPDATE BEGIN -->
  	  <input type='hidden' name='sales[%d][purchaseid]' value='%d' id='sales_%d_purchaseid' />
  	  <input type='hidden' name='sales[%d][category]' value='%d' id='sales_%d_category' />
  	  <input type='hidden' name='sales[%d][productid]' value='%d' id='sales_%d_productid' />
  	  <input type='hidden' name='sales[%d][volume_discount]' id='volume_%d_discount' />
          <!-- // KONSTANT CODE UPDATE END -->
<script>
    $(document).ready(function() {
	    
		$('#realgrcalc%d').rules('add', {
		  required: function(element){
	            return $('#grcalc%d').val()!='';
	        },
		  number: true,
		  range: function() {
		      return [0.00, parseFloat($('#grcalc%d').val()) + {$_SESSION['extractLimit']}];
		  }
		});
	

  }); // end ready
</script>
",  //KONSTANT CODE UPDATE BEGIN
	  $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $product['purchaseid'] . '.' . $product['photoExt'], $product['name'], $i, $i, $price, $i, $i, $savedGrams[$i], $i, $i, $savedEuro[$i], $i, $i, $savedGrams2[$i], $i, $i, $i, $i, $i, $name, $i, $product['purchaseid'], $i, $i, $categoryid, $i, $i, $product['productid'], $i, $i, $i, $i, $i, $i
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
