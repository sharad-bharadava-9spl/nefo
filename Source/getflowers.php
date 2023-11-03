<?php 

	require_once 'cOnfig/connection.php';
	require_once 'cOnfig/languages/common.php';

	session_start();
	$accessLevel = '3';
	
	getSettings();
	
	$domain = $_SESSION['domain'];
	$menuType = $_SESSION['menuType'];
	
	$i = $_GET['i'];
	$user_id = $_GET['uid'];	
	$discount = $_GET['discount'];
	$usageType = $_GET['usageType'];
	
	if ($_SESSION['menusortdisp'] == 0) {
		$sorting = "p.salesPrice ASC";
	} else {
		$sorting = "g.name ASC";
	}
	
	$selectFlower = "SELECT g.flowerid, g.name, g.breed2, g.flowertype, g.sativaPercentage, g.description, g.medicaldescription, p.productid, p.purchaseid, p.salesPrice, p.realQuantity, p.growType, p.photoExt, p.medDiscount FROM flower g, purchases p WHERE p.category = 1 AND p.productid = g.flowerid AND p.closedAt IS NULL AND p.inMenu = 1 ORDER BY $sorting";
	
		try
		{
			$resultFlower = $pdo3->prepare("$selectFlower");
			$resultFlower->execute();
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

		// Look up category discount
		$selectCategoryDiscount = "SELECT discount FROM catdiscounts WHERE user_id = $user_id AND categoryid = 1";
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
		
		
		while ($flower = $resultFlower->fetch()) {
	

		if ($flower['breed2'] != '') {
			$name = $flower['name'] . " x " . $flower['breed2'];
		} else {
			$name = $flower['name'];
		}
		
		if ($flower['flowertype'] == 'Hybrid' && $flower['sativaPercentage'] > 0 && $flower['sativaPercentage'] != NULL) {
			$percentageDisplay = '<br />(' . number_format($flower['sativaPercentage'],0) . '% s.)';
		} else {
			$percentageDisplay = '';
		}
		
	// Look up growtype
	$growtype = $flower['growType'];
	
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
		
		if ($growtype == '') {
			$growtype = "<span class='prodspecsempty'>&nbsp;</span>";
		} else {
			$growtype = "<span class='prodspecs'>$growtype</span>";
		}
		
		if ($flower['flowertype'] == '') {
			$flowertype = "<span class='prodspecsempty'>&nbsp;</span>";
		} else {
			$flowertype = "<span class='prodspecs'>{$flower['flowertype']}</span>";
		}
		
	// ************************************** Calculate discount(s) ****************************************
	
	$purchaseid = $flower['purchaseid'];
	
	if ($_SESSION['showOrigPrice'] == 1) {
		
		$normalPrice = "({$flower['salesPrice']} &euro;)";
		
	} else {
	
		$normalPrice = "";
		
	}
	
	// Individual purchase discount
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
		
		$price = number_format($prodFijo,2);
		
	} else if ($prodDiscount != 0 || $prodDiscount != '') {
		
		$prodDiscount = (100 - $prodDiscount) / 100;
		$price = number_format($flower['salesPrice'] * $prodDiscount,2);
		
	} else if ($catDiscount != 0 || $catDiscount != '') {
		
		$catCurrentDiscount = (100 - $catDiscount) / 100;
		$price = number_format($flower['salesPrice'] * $catCurrentDiscount,2);

	} else if ($discount != 0) {
		
		$dispDiscount = (100 - $discount) / 100;
		$price = number_format($flower['salesPrice'] * $dispDiscount,2);
		
	} else if ($usageType == '1') {
		
		if ($flower['medDiscount'] > 0) {
			$dispDiscount = (100 - $flower['medDiscount']) / 100;
			$price = number_format($flower['salesPrice'] * $dispDiscount,2);
		} else if ($medicalDiscountPercentage == 1) {
			$price = number_format(($flower['salesPrice']) * $medicalDiscountCalc,2);
		} else {
			$price = number_format(($flower['salesPrice']) - $medicalDiscount,2);
		}
		
	} else {
		
		$price = number_format($flower['salesPrice'],2);
		
	}
	
		
	$i++;
	
	
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
				if ($_SESSION['realWeight'] == 0) {
					$giftOption = "<img src='images/gift-big.png' id='free%d' style='margin-bottom: 0; margin-left: 5px; margin-top: 10px; margin-bottom: 6px;' />";
				} else {
					$giftOption = "<img src='images/gift-small.png' id='free%d' style='margin-bottom: 0; margin-left: -5px; margin-top: 10px; margin-bottom: 6px;' />";
				}
			} else {
				$giftOption = "<span style='display: block; height: 10px;'></span><input type='hidden' value='%d' />";
			}
	} else {
		$savedGrams2[$i] = $savedSale[$i][grams2];
		$displayGift = '';
		$giftOption = '';
	}
	
	if ($flower['description'] != '') {
		
		$commentRead = "
		                <img src='images/info-normal.png' style='inline-block; margin-top: 5px; margin-left: 5px;' id='comment$i' onClick='showhide$i()' /><div id='helpBox$i' class='helpBox'><strong>{$lang['extracts-description']}:</strong><br />{$flower['description']}</div>
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
	if ($flower['medicaldescription'] != '') {
		
		$commentReadM = "
		                <img src='images/info-med.png' style='inline-block; margin-top: 5px; margin-left: 5px;' id='commentM$i' onClick='showhideM$i()' /><div id='helpBoxM$i' class='helpBox'><strong>{$lang['extracts-medicaldesc']}:</strong><br />{$flower['medicaldescription']}</div>
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
		
		$commentReadM = "<img src='images/info-med.png' style='visibility: hidden;' />";
		
	}		

	if ($_SESSION['showStock'] == 1) {
		// Calculate Stock
		$selectSales = "SELECT SUM(realQuantity) FROM salesdetails WHERE purchaseid = $purchaseid";
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
			$sales = $row['SUM(realQuantity)'];

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
		$estStock = $flower['realQuantity'] + $permAdditions - $sales - $permRemovals - $inStash;
		
		$estStock = number_format($estStock,1);

			$stockDisplay = "<img src='images/box-small.png' style='margin-bottom: -3px; margin-right: 5px;' />$estStock g";
		}
	  
		
		if ($_SESSION['realWeight'] == 0) {
			
	if ($menuType == 0) {


	$flower_row =	sprintf("
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
      	  } else {
          	$('#eurcalc%d').val('');
          	$('#grcalc%d').val('');
      	  }
        }

        $('#grcalc%d').bind('keyup blur change', compute);
        $('#eurcalc%d').bind('keyup blur change', compute2);
        
		$('#free%d').click(function () {
		$('#grcalcB%d').css('display', 'inline-block');
		$('#free%d').css('display', 'none');
		});	

  }); // end ready
        </script>
	<div class='displaybox'>
	 <div class='imageholder'>
	  <center><img src='images/_$domain/purchases/%s' /></center>
	 </div>
	 <h3>%s</h3>
	 
	 <table style='width: 210px;'>
	  <tr>
	   <td style='width: 80px; padding-left: 5px; vertical-align: top; text-align: left;'>
	    %s<br />
	    %s
	   </td>
	   <td style='width: 125px; text-align: right; vertical-align: top;'>
	    <input class='specialInput' id='ppgcalc%d' name='sales[%d][ppg]' value='%0.02f' readonly /><span class='eurosign'>&euro;</span><br />
	    <span class='discountprice'>$normalPrice</span>
	   </td>
	  </tr>
	 </table>
	 <table style='width: 210px; height: 60px;'>
	  <tr>
	   <td style='vertical-align: top; text-align: left; position: relative;'>
	    $commentRead $commentReadM 
	   </td>
	   <td style='width: 85px; text-align: right; vertical-align: top; color: #606f5c; font-weight: 600; padding-top: 13px;'>
	    $stockDisplay
	   </td>
	  </tr>
	 </table>
	 <table style='width: 210px; background-color: #c7d6c1; border-radius: 5px;'>
	  <tr>
	   <td style='vertical-align: top; text-align: left; margin-left: 10px;'>
  	    <input type='text' lang='nb' class='dispinput centered calc onScreenKeypad tst c$purchaseid' id='grcalc%d' name='sales[%d][grams]' placeholder='Gr.' step='0.01' value='%s' style='margin-left: 10px;' />
	   </td>
	   <td style='vertical-align: top; text-align: right;'>
  	    <input type='text' lang='nb' class='dispinput centered calc2 onScreenKeypad tst' id='eurcalc%d' name='sales[%d][euro]' placeholder='&euro;' step='0.01' value='%s' style='margin-right: 10px;'/>
	   </td>
	  </tr>
	  <tr>
	   <td>
  	    <input type='text' lang='nb' class='dispinput centered calc onScreenKeypad tst' id='grcalcB%d' name='sales[%d][grams2]' placeholder='Gr.' step='0.01' value='%s' style='background-color: #f4b146; display: none; margin-left: 6px; margin-bottom: 10px;'  /> $giftOption
  	   </td>
  	   <td>
  	    <input type='hidden' name='sales[%d][name]' value='%s' />
  	    <input type='hidden' name='sales[%d][purchaseid]' value='%d' />
  	    <input type='hidden' name='sales[%d][category]' value='1' />
   	    <input type='hidden' name='sales[%d][productid]' value='%d' />
  	   </td>
  	  </tr>
	 </table>
  	 </center>
	</div>",
	  $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $flower['purchaseid'] . '.' . $flower['photoExt'], $name, $growtype, $flowertype, $i, $i, $price, $i, $i, $savedGrams[$i], $i, $i, $savedEuro[$i], $i, $i, $savedGrams2[$i], $i, $i, $name, $i, $flower['purchaseid'], $i, $i, $flower['flowerid']
	  );
	  
  } else {
	  
	  
		if ($_SESSION['showStock'] == 1) {
			$stockDisplay = "<img src='images/box-small.png' style='margin-bottom: -3px; margin-right: 5px;' />$estStock g";
		}
		
	$flower_row =	sprintf("
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
      	  } else {
          	$('#eurcalc%d').val('');
          	$('#grcalc%d').val('');
      	  }
        }

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
	    <td class='left'>%s</td>
	    <td class='left'>%s</td>
	    <td class='centered relative' style='padding: 11px 0;'>$commentRead</td>
	    <td class='centered relative' style='padding: 11px 0;'>$commentReadM</td>
	    <td class='right'>%0.02f &euro; <input type='hidden' id='ppgcalc%d' name='sales[%d][ppg]' value='%0.02f' /></td>
	    <td class='right'><span style='color: red;' class='smallerfont'>$normalPrice</span></td>
	    $stockDisplay
	    <td><input type='text' lang='nb' class='twoDigit centered calc onScreenKeypad c$purchaseid' style='border: #5aa242 solid 2px; width: 50px; border-radius: 2px; padding: 2px 1px;' id='grcalc%d' name='sales[%d][grams]' placeholder='Gr.' step='0.01' value='%s' /></td>
	    <td><input type='text' lang='nb' class='twoDigit centered calc2 onScreenKeypad' style='border: #5aa242 solid 2px; width: 50px; border-radius: 2px; padding: 2px 1px;' id='eurcalc%d' name='sales[%d][euro]' placeholder='&euro;' step='0.01' value='%s' /></td>
	    <td><input type='text' lang='nb' class='twoDigit centered calc onScreenKeypad' id='grcalcB%d' name='sales[%d][grams2]' placeholder='Gr.' step='0.01' value='%s' style='border: #5aa242 solid 2px; width: 50px; border-radius: 2px; padding: 2px 1px; background-color: yellow; $displayGift'  />$giftOption</td>
	   </tr>
  	  <input type='hidden' name='sales[%d][name]' value='%s' />
  	  <input type='hidden' name='sales[%d][purchaseid]' value='%d' />
  	  <input type='hidden' name='sales[%d][category]' value='1' />
  	  <input type='hidden' name='sales[%d][productid]' value='%d' />
",
	  $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $name, $growtype, $flower['flowertype'], $price, $i, $i, $price, $i, $i, $savedGrams[$i], $i, $i, $savedEuro[$i], $i, $i, $savedGrams2[$i], $i, $i, $name, $i, $flower['purchaseid'], $i, $i, $flower['flowerid']
	  );
	  
  }
  
  	  echo $flower_row;
  	  
} else {
	
	// Real grams
	if ($menuType == 0) {
		
	$flower_row =	sprintf("
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
	 <div class='imageholder'>
	  <center><img src='images/_$domain/purchases/%s' /></center>
	 </div>
	 <h3>%s</h3>
	 
	 <table style='width: 210px;'>
	  <tr>
	   <td style='width: 80px; padding-left: 5px; vertical-align: top; text-align: left;'>
	    %s<br />
	    %s
	   </td>
	   <td style='width: 125px; text-align: right; vertical-align: top;'>
	    <input class='specialInput' id='ppgcalc%d' name='sales[%d][ppg]' value='%0.02f' readonly /><span class='eurosign'>&euro;</span><br />
	    <span class='discountprice'>$normalPrice</span>
	   </td>
	  </tr>
	 </table>
	 <table style='width: 210px; height: 60px;'>
	  <tr>
	   <td style='vertical-align: top; text-align: left; position: relative;'>
	    $commentRead $commentReadM 
	   </td>
	   <td style='width: 85px; text-align: right; vertical-align: top; color: #606f5c; font-weight: 600; padding-top: 13px;'>
	    $stockDisplay
	   </td>
	  </tr>
	 </table>
	 <table style='width: 210px; background-color: #c7d6c1; border-radius: 5px;'>
	  <tr>
	   <td style='vertical-align: top; text-align: left; margin-left: 10px;'>
  	    <input type='text' lang='nb' class='dispinput centered calc onScreenKeypad tst c$purchaseid' id='grcalc%d' name='sales[%d][grams]' placeholder='Gr.' step='0.01' value='%s' style='margin-left: 10px;' />
	   </td>
	   <td style='vertical-align: top; text-align: right;' colspan='2'>
  	    <input type='text' lang='nb' class='dispinput centered calc2 onScreenKeypad tst' id='eurcalc%d' name='sales[%d][euro]' placeholder='&euro;' step='0.01' value='%s' style='margin-right: 10px;'/>
	   </td>
	  </tr>
	  <tr>
	   <td style='vertical-align: top; text-align: left;'>
  	    <input type='text' lang='nb' class='dispinput centered calc5' id='realgrcalc%d' name='sales[%d][realGrams]' placeholder='Real gr.' step='0.01' style='margin-left: 10px; margin-bottom: 10px; margin-bottom: 10px;' />
  	   </td>
  	   <td>
  	    <input type='text' lang='nb' class='dispinput centered calc calc6 onScreenKeypad tst' id='grcalcB%d' name='sales[%d][grams2]' placeholder='Gr.' step='0.01' value='%s' style='background-color: #f4b146; display: none; margin-left: -6px; margin-bottom: 10px; width: 50px;'  /> $giftOption
  	   </td>
  	   <td style='vertical-align: middle; text-align: left;'><img src='images/delete.png' width='17' style='display: inline-block; cursor: pointer;' id='zero%d' />
  	    <input type='hidden' name='sales[%d][name]' value='%s' />
  	    <input type='hidden' name='sales[%d][purchaseid]' value='%d' />
  	    <input type='hidden' name='sales[%d][category]' value='1' />
   	    <input type='hidden' name='sales[%d][productid]' value='%d' />
  	   </td>
  	  </tr>
	 </table>
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
		      return [0.00, parseFloat($('#grcalc%d').val()) + {$_SESSION['flowerLimit']}];
		      
		  }
		});
	

  }); // end ready
</script>
",
	  $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $flower['purchaseid'] . '.' . $flower['photoExt'], $name, $growtype, $flowertype, $i, $i, $price, $i, $i, $savedGrams[$i], $i, $i, $savedEuro[$i], $i, $i, $i, $i, $savedGrams2[$i], $i, $i, $i, $name, $i, $flower['purchaseid'], $i, $i, $flower['flowerid'], $i, $i, $i
	  );
	  
  } else {
	  
	  
	$flower_row =	sprintf("
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
	    <td class='left'>%s</td>
	    <td class='left'>%s</td>
	    <td class='centered relative' style='padding: 11px 0;'>$commentRead</td>
	    <td class='centered relative' style='padding: 11px 0;'>$commentReadM</td>
	    <td class='right'>%0.02f &euro; <input type='hidden' id='ppgcalc%d' name='sales[%d][ppg]' value='%0.02f' /></td>
	    <td class='right'><span style='color: red;' class='smallerfont'>$normalPrice</span></td>	    
	    <td class='right'>$stockDisplay</td>
	    <td><input type='text' lang='nb' class='twoDigit centered calc onScreenKeypad c$purchaseid' style='border: #5aa242 solid 2px; width: 50px; border-radius: 2px; padding: 2px 1px;' id='grcalc%d' name='sales[%d][grams]' placeholder='Gr.' step='0.01' value='%s' /></td>
	    <td><input type='text' lang='nb' class='twoDigit centered calc2 onScreenKeypad' style='border: #5aa242 solid 2px; width: 50px; border-radius: 2px; padding: 2px 1px;' id='eurcalc%d' name='sales[%d][euro]' placeholder='&euro;' step='0.01' value='%s' /></td>
	    <td><input type='text' lang='nb' class='twoDigit centered calc calc6 onScreenKeypad' id='grcalcB%d' name='sales[%d][grams2]' placeholder='Gr.' step='0.01' value='%s' style='border: #5aa242 solid 2px; width: 50px; border-radius: 2px; padding: 2px 1px; background-color: yellow; $displayGift'  />$giftOption</td>
	    <td><input type='text' lang='nb' class='fourDigit centered calc5' id='realgrcalc%d' name='sales[%d][realGrams]' placeholder='Real gr.' step='0.01' style='border: #5aa242 solid 2px; width: 50px; border-radius: 2px; padding: 2px 1px;' />  &nbsp;&nbsp;<img src='images/delete.png' width='17' style='cursor: pointer;' id='zero%d' /></td>
	   </tr>
  	  <input type='hidden' name='sales[%d][name]' value='%s' />
  	  <input type='hidden' name='sales[%d][purchaseid]' value='%d' />
  	  <input type='hidden' name='sales[%d][category]' value='1' />
  	  <input type='hidden' name='sales[%d][productid]' value='%d' />
<script>
    $(document).ready(function() {
	    
		$('#realgrcalc%d').rules('add', {
		  required: function(element){
	            return $('#grcalc%d').val()!='';
	        },
		  number: true,
		  range: function() {
		      return [0.00, parseFloat($('#grcalc%d').val()) + {$_SESSION['flowerLimit']}];
		      
		  }
		});
	

  }); // end ready
</script>
",
	  $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $name, $growtype, $flower['flowertype'], $price, $i, $i, $price, $i, $i, $savedGrams[$i], $i, $i, $savedEuro[$i], $i, $i, $savedGrams2[$i], $i, $i, $i, $i, $i, $name, $i, $flower['purchaseid'], $i, $i, $flower['flowerid'], $i, $i, $i
	  );
	  
  }
  
  	  echo $flower_row;
  	  
} 
  }
  
  	  
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
