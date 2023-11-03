<?php 

	require_once 'cOnfig/connection.php';
	require_once 'cOnfig/languages/common.php';

	session_start();
	$accessLevel = '3';
	
	getSettings();
	
	$menuType = $_SESSION['menuType'];
	
	$i = $_GET['i'];
	$user_id = $_GET['uid'];	
	$discount = $_GET['discount'];
	$usageType = $_GET['usageType'];
	

	$selectFlower = "SELECT g.flowerid, g.name, p.purchaseid, p.salesPrice2 FROM flower g, purchases p WHERE p.category = 1 AND p.productid = g.flowerid AND p.closedAt IS NULL AND p.inMenu = 1 ORDER BY p.salesPrice2 ASC;";
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
		
		
	// ************************************** Calculate discount(s) ****************************************
	
	$purchaseid = $flower['purchaseid'];
	
	if ($_SESSION['showOrigPrice'] == 1) {
		
		$normalPrice = "({$flower['salesPrice2']} {$_SESSION['currencyoperator']})";
		
	} else {
	
		$normalPrice = "";
		
	}
	
	// Individual purchase discount
/*	$selectPurchaseDiscount = "SELECT discount, fijo FROM inddiscounts WHERE user_id = $user_id AND purchaseid = $purchaseid";
	
	$resultPurchaseDiscount = mysql_query($selectPurchaseDiscount)
		or handleError($lang['error-prodprices'],"Error loading flower prices from db: " . mysql_error());
		
	$rowD = mysql_fetch_array($resultPurchaseDiscount);
		$prodDiscount = $rowD['discount'];
		$prodFijo = $rowD['fijo'];*/
		
	if ($prodFijo != 0 || $prodFijo != '') {
		
		$price = number_format($prodFijo,2);
		
	} else if ($prodDiscount != 0 || $prodDiscount != '') {
		
		$prodDiscount = (100 - $prodDiscount) / 100;
		$price = number_format($flower['salesPrice2'] * $prodDiscount,2);
		
	} else if ($catDiscount != 0 || $catDiscount != '') {
		
		$catCurrentDiscount = (100 - $catDiscount) / 100;
		$price = number_format($flower['salesPrice2'] * $catCurrentDiscount,2);

	} else if ($discount != 0) {
		
		$dispDiscount = (100 - $discount) / 100;
		$price = number_format($flower['salesPrice2'] * $dispDiscount,2);
		
	} else if ($usageType == '1') {
		
		if ($flower['medDiscount'] > 0) {
			$dispDiscount = (100 - $flower['medDiscount']) / 100;
			$price = number_format($flower['salesPrice2'] * $dispDiscount,2);
		} else if ($medicalDiscountPercentage == 1) {
			$price = number_format(($flower['salesPrice2']) * $medicalDiscountCalc,2);
		} else {
			$price = number_format(($flower['salesPrice2']) - $medicalDiscount,2);
		}
		
	} else {
		
		$price = number_format($flower['salesPrice2'],2);
		
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
				$giftOption = "<img src='images/free.png' id='free%d' style='margin-bottom: 0; margin-left: 5px;' />";
			} else {
				$giftOption = "<input type='hidden' value='%d' />";
			}
	} else {
		$savedGrams2[$i] = $savedSale[$i][grams2];
		$displayGift = '';
		$giftOption = '';
	}
	

		

	  
	if ($menuType == 0) {

		if ($_SESSION['showStock'] == 1) {
			$stockDisplay = "<center>$estStock g</center>";
		}

	$flower_row =	sprintf("
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
	 <div id='imageholder'>
	  <center><img src='images/purchases/%s' /></center>
	 </div>
	 <h3>%s</h3>
	 <span class='descprice relative'>
	  <span class='firstline'>%s<br /><span class='yellow' style='font-size: 16px; font-weight: 600; margin-top: 3px; display: inline-block;'>%s</span></span><span class='yellow' style='display: none;'>%s</span><br />$commentRead $commentReadM  
	 </span>
	 <input class='specialInput' id='ppgcalc%d' name='sales[%d][ppg]' value='%0.02f' readonly /><span class='eurosign'>{$_SESSION['currencyoperator']}</span><br /><span style='color: yellow;' class='biggerfont'>$normalPrice</span><br />$stockDisplay
	 <div class='clearfloat'></div><br />
	 <center>
  	  <input type='text' lang='nb' class='fourDigit centered calc onScreenKeypad tst' id='grcalc%d' name='sales[%d][grams]' placeholder='Gr.' step='0.01' value='%s' />
  	  <input type='text' lang='nb' class='fourDigit centered calc2 onScreenKeypad tst' id='eurcalc%d' name='sales[%d][euro]' placeholder='{$_SESSION['currencyoperator']}' step='0.01' value='%s' />
  	  <input type='text' lang='nb' class='fourDigit centered calc onScreenKeypad tst' id='grcalcB%d' name='sales[%d][grams2]' placeholder='Gr.' step='0.01' value='%s' style='background-color: yellow; display: none;'  /> $giftOption
  	  <input type='hidden' name='sales[%d][name]' value='%s' />
  	  <input type='hidden' name='sales[%d][purchaseid]' value='%d' />
  	  <input type='hidden' name='sales[%d][category]' value='1' />
  	  <input type='hidden' name='sales[%d][productid]' value='%d' />
  	 </center>
	</div>",
	  $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $flower['purchaseid'] . '.' . $flower['photoExt'], $name, $growtype, $flower['flowertype'], $percentageDisplay, $i, $i, $price, $i, $i, $savedGrams[$i], $i, $i, $savedEuro[$i], $i, $i, $savedGrams2[$i], $i, $i, $name, $i, $flower['purchaseid'], $i, $i, $flower['flowerid']
	  );
	  
  } else {
	  
	  
		if ($_SESSION['showStock'] == 1) {
			$stockDisplay = "<td class='right'>$estStock g</td>";
		}
		
	$flower_row =	sprintf("
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
	    <td class='right'>%0.02f {$_SESSION['currencyoperator']} <input type='hidden' id='ppgcalc%d' name='sales[%d][ppg]' value='%0.01f' /></td>
	    <td class='right'><span style='color: red;' class='smallerfont'>$normalPrice</span></td>
	    $stockDisplay
	    <td><input type='text' lang='nb' class='twoDigit centered calc onScreenKeypad' style='border: #5aa242 solid 2px; width: 50px; border-radius: 2px; padding: 2px 1px;' id='grcalc%d' name='sales[%d][grams]' placeholder='Gr.' step='0.01' value='%s' /></td>
	    <td><input type='text' lang='nb' class='twoDigit centered calc2 onScreenKeypad' style='border: #5aa242 solid 2px; width: 50px; border-radius: 2px; padding: 2px 1px;' id='eurcalc%d' name='sales[%d][euro]' placeholder='{$_SESSION['currencyoperator']}' step='0.01' value='%s' /></td>
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
