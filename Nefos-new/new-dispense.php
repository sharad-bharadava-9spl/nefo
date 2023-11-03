<?php
	
	require_once 'cOnfig/connection.php';
	require_once 'cOnfig/view2.php';
	require_once 'cOnfig/authenticate.php';
	require_once 'cOnfig/languages/common.php';
	
	session_start();
	$accessLevel = '3';
	
	// Authenticate & authorize
	authorizeUser($accessLevel);
	
	// Retrieve System settings
	getSettings();
	
	// Save dispense, but fulfilled = 0, confirmed = 0
	// Redirect to Confirm page with payment options
	// 

	// Did this page re-submit with a form? If so, check & store details
	if (isset($_POST['dispense'])) {
		
  	    $user_id = $_POST['user_id'];
		$euroTOT = $_POST['euroTOT'];
		$eurcalcTOT = $_POST['eurcalcTOT'];
		$gramsTOT = $_POST['gramsTOT'];
		$unitsTOT = $_POST['unitsTOT'];
		$paidTOT = $_POST['paidTOT'];
		$gramsTOT = $_POST['gramsTOT'];
		$adminComment = $_POST['adminComment'];
		$newcredit = $_POST['newcredit'];
		$credit = $_POST['credit'];
		$realCredit = $_POST['realCredit'];
		$realNewCredit = $_POST['realNewCredit'];		
  	    $day = $_POST['day'];
		$month = $_POST['month'];
		$year = $_POST['year'];
		$paidUntil = $_POST['paidUntil'];	
		$totDiscountInput = $_POST['totDiscountInput'];	
		$saletime = date('Y-m-d H:i:s');
		$orderHash = generateRandomString();
			
		// Query to add new sale to Sales table - 6 arguments
	  	$query = sprintf("INSERT INTO sales (saletime, userid, amount, amountPaid, quantity, units, adminComment, settled, settledTo, creditBefore, creditAfter, expiry, realQuantity, discount, userConfirmed, fulfilled, orderHash) VALUES ('%s', '%d', '%f', '%f', '%f', '%f', '%s', '%s', '%d', '%f', '%f', '%s', '%f', '%f', 0, 0, '%s');",
	  	$saletime, $user_id, $eurcalcTOT, $eurcalcTOT, $gramsTOT, $unitsTOT, $adminComment, $saletime, '1', $creditLookup, $newCreditCalc, $saletime, $gramsTOT, $totDiscountInput, $orderHash);
		
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
		
		$saleid = $pdo3->lastInsertId();

	foreach($_POST['sales'] as $sale) {
		$name = $sale['name'];
		$category = $sale['category'];
		$productid = $sale['productid'];
		$purchaseid = $sale['purchaseid'];
		$grams = $sale['grams'];
		$grams2 = $sale['grams2'];
		
		if ($totDiscountInput > 0) {
			$euro = $sale['euro'] * ((100 - $totDiscountInput) / 100);
		} else {
			$euro = $sale['euro'];
		}
		$gramsTot = $grams + $grams2;
		
		if ($gramsTot > 0) {
			
			if ($grams > 0 && $grams2 > 0) {
				
	    		// Query to add new sale to salesdetails table - ? arguments
			  	$query = sprintf("INSERT INTO salesdetails (saleid, category, productid, quantity, amount, purchaseid, realQuantity) VALUES ('%d', '%d', '%d', '%f', '%f', '%d', '%f');",
			  	$saleid, $category, $productid, $grams, $euro, $purchaseid, $grams);
			  
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
				
	    		// Query to add new sale to salesdetails table - ? arguments
			  	$query = sprintf("INSERT INTO salesdetails (saleid, category, productid, quantity, amount, purchaseid, realQuantity) VALUES ('%d', '%d', '%d', '%f', '%f', '%d', '%f');",
			  	$saleid, $category, $productid, $grams2, '0', $purchaseid, $grams2);
			  
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


				
			} else {
			
				if ($grams  == 0) {
					
					$grams = $grams2;
					
				}
	    	
	    		// Query to add new sale to salesdetails table - ? arguments
			  	$query = sprintf("INSERT INTO salesdetails (saleid, category, productid, quantity, amount, purchaseid, realQuantity) VALUES ('%d', '%d', '%d', '%f', '%f', '%d', '%f');",
			  	$saleid, $category, $productid, $grams, $euro, $purchaseid, $grams);
			  
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
					
	    		// Query to add new sale to salesdetails table - ? arguments
			  	$query = sprintf("INSERT INTO f_salesdetails (saleid, category, productid, quantity, amount, purchaseid, realQuantity) VALUES ('%d', '%d', '%d', '%f', '%f', '%d', '%f');",
			  	$saleid, $category, $productid, $grams, $euro, $purchaseid, $grams);
			  
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
		}

	}
	
		// On success: redirect.
		header("Location: confirm-order.php?sd=" . $saleid . "&ud=$user_id&sh=" . $orderHash);
		exit();

	}
	/***** FORM SUBMIT END *****/
	
	
		$user_id = $_SESSION['user_id'];

	
	
		// Look up user details for showing profile on the Sales page
		$userDetails2 = "SELECT memberno, paidUntil, userGroup, first_name, last_name, paymentWarning, paymentWarningDate, mconsumption, day, month, year, credit, creditEligible, discount, photoext, cardid, nationality FROM users WHERE user_id = '{$user_id}'";
		try
		{
			$result = $pdo3->prepare("$userDetails2");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row2 = $result->fetch();
		$memberno = $row2['memberno'];
		$first_name = $row2['first_name'];
		$last_name = $row2['last_name'];
		$paidUntil = $row2['paidUntil'];
		$userGroup = $row2['userGroup'];
		$day = $row2['day'];
		$month = $row2['month'];
		$year = $row2['year'];
		$mconsumption = $row2['mconsumption'];
		$paymentWarning = $row2['paymentWarning'];
		$paymentWarningDate = $row2['paymentWarningDate'];
		$paymentWarningDateReadable = date('d M', strtotime($paymentWarningDate));
		$credit = $row2['credit'];
		$realCredit = $credit;
		$creditEligible = $row2['creditEligible'];
		$discount = $row2['discount'];
		$photoext = $row2['photoext'];
		$cardid = $row2['cardid'];
		$nationality = $row2['nationality'];		
		
		if ($_SESSION['dispensaryGift'] == 1 && $_SESSION['menuType'] == 1) {
			$giftOption = "<img src='images/free-green.png' id='free%d' style='margin-bottom: 0; margin-left: 5px;' />";
		} else if ($_SESSION['dispensaryGift'] == 1 && $_SESSION['menuType'] == 0) {
			$giftOption = "<img src='images/free.png' id='free%d' style='margin-bottom: 0; margin-left: 5px;' />";
		} else {
			$giftOption = "<input type='hidden' value='%d' />";
		}

		
		
	$validationScript = <<<EOD
	
    $(document).ready(function() {
	    
	$("#credit").click(function () {
		$(".donateField").css("display", "table-row");
	});
	
	
	$(function(){
	    $('#chipClick').click(function() {
	        $("#cardscan").val('$cardid');
	    });
	});
	  
	  function getItems()
{


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
  
	    var sumDisc = 0;
	
	    $("input[type=checkbox]:checked").each(function(){
	      sumDisc += parseInt($(this).val());
	    });
		$('#totDiscount').html("(" + sumDisc + "%)");
		$('#totDiscountInput').val(sumDisc);
		
		var appliedDisc = (100 - sumDisc) / 100;
		
		var newPrice = rsumB * appliedDisc;		

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
  
}

	if ($credit < 0) {
	   function computeTot() {
	          var a = $('#realCredit').val();
	          var b = $('#eurcalcTOT').val();
	          var total = ((a*1) - (b*1));
	          var roundedtotal = total.toFixed(2);
	          $('#realNewCredit').val(roundedtotal);
	   }
	} else {
   function computeTot() {
          var a = $('#realCredit').val();
          var b = $('#eurcalcTOT').val();
	      var total = ((a*1) - (b*1));
          var roundedtotal = total.toFixed(2);
          $('#newcredit').val(roundedtotal);
	      $('#realNewCredit').val(roundedtotal);
        }
    }

	$("#openComment").click(function () {
	$(".hiddenComment").css("display", "block");
	$("#openComment").css("display", "none");
	});	
	
	$("#openDispenseDate").click(function () {
	$("#customDispenseDate").css("display", "block");
	$("#openDispenseDate").css("display", "none");
	});	
	
	
	$("#minimizeMemberBox").click(function () {
	$("#hiddenSummary").css("display", "block");
	$("#memberbox").css("visibility", "hidden");
	});	
	
	$("#minimizeSummaryBox").click(function () {
	$("#memberbox").css("visibility", "visible");
	$("#hiddenSummary").css("display", "none");
	});
	
	
    // Calculate discounts
	$("input[type=checkbox]").change(function(){
	  recalculate();
	});
	
	
	function recalculate() {
		
	    var sumDisc = 0;
	
	    $("input[type=checkbox]:checked").each(function(){
	      sumDisc += parseInt($(this).val());
	    });
		$('#totDiscount').html("(" + sumDisc + "%)");
		$('#totDiscountInput').val(sumDisc);
		
	}
	

	
$(document).on('click keypress keyup blur', function () {
    getItems();
    computeTot();
});

	getItems();
    computeTot();
  }); // end ready
  

EOD;

	pageStart("CCS shop", NULL, $validationScript, "pdispense", "newsale", "", $_SESSION['successMessage'], $_SESSION['errorMessage']);
	
?>
<form id="registerForm" action="" method="POST">


<div id="memberbox">
<img src="images/minimize.png" class="closeImg pointer" id="minimizeMemberBox" />

<?php
echo "<span class='memberinfo'><span class='membername'>DEMO CLUB</span>";

	
	?>
	</span>
	<div class="clearFloat"></div>
 	   <input type='hidden' id='eurcalcTOTexp' name='euroTOT' />
 	   <input type='hidden' name='dispense' value='done' /> 	   
 	   <input type='hidden' name='paidUntil' value='<?php echo $paidUntil; ?>' /> 	   
  	   <input type='hidden' name='user_id' value='<?php echo $user_id; ?>' />
  	   <input type='hidden' name='realCredit' id='realCredit' value='<?php echo $realCredit; ?>' />
  	   <input type='hidden' name='realNewCredit' id='realNewCredit' value='<?php echo $realNewCredit; ?>' />
  	   <input type='hidden' name='totDiscountInput' id='totDiscountInput' value='' />

  	   <table id="memberAmount">
  	    <tr style='display: none;'>
  	     <td style='text-align: right;'><input type='number' lang='nb' class='specialInput first notZero' id='grcalcTOT' name='gramsTOT' value="0" readonly /> </td>
  	     <td style='text-align: left;'><span style="font-size: 20px;">&nbsp;gr.</span></td>
  	     <td></td>
  	     <td></td>
  	    </tr>
  	    <tr>
  	     <td style='text-align: right;'><input type='number' lang='nb' class='specialInput first notZero' id='unitcalcTOT' name='unitsTOT' value="0" readonly /> </td>
  	     <td style='text-align: left;'><span style="font-size: 20px;">&nbsp;u.</span></td>
  	     <td style='text-align: right;'><!--<a href='donation-management.php?userid=<?php echo $user_id; ?>'>Credit:--></td>
  	     <td style='text-align: right;'><!--New credit:--></td>
  	    </tr>
  	    <tr>
  	     <td style='text-align: right;'><input type='number' lang='nb' class='specialInput notZero' id='eurcalcTOT' name='eurcalcTOT' value="0" readonly /> </td>
  	     <td style='text-align: left;'><span style="font-size: 20px;">&nbsp;&euro;</span></td>
  	     <td><!--<input type='number' lang='nb' class="specialInput" style='color: red;' id='credit' name='credit' value='0' readonly /> &euro;--></td>
  	     <td><!--<input type='number' lang='nb' class='specialInput' style='color: red;' id='newcredit' name='newcredit' value='0' readonly /> &euro;--></td>
  	    </tr>
  	   </table>
  	   



       <center>
<br />
 <button type="submit" ><?php echo $lang['global-save']; ?></button><br />
 <!--<a href="?saveCart&user_id=<?php echo $user_id; ?>">Save</a>--><a href="#" onclick="document.getElementById('registerForm').reset();"><img src="images/clear-cart.png" /></a></center>
  	</div>
</div> <!-- end memberbox -->

<div id='hiddenSummary'>
<img src="images/maximize.png" class="closeImg pointer" id="minimizeSummaryBox" />
  	   <table>
  	    <tr>
  	     <td><input type='number' lang='nb' class='fourDigit specialInput first' id='grcalcTOT2' name='gramsTOT2' value="0" readonly /></td>
  	     <td><span style="font-size: 30px;"> &nbsp;gr.</span></td>
  	    </tr>
  	    <tr>
  	     <td><input type='number' lang='nb' class='fourDigit specialInput first' id='unitcalcTOT2' name='unitsTOT2' value="0" readonly /></td>
  	     <td><span style="font-size: 30px;"> u.</span></td>
  	    </tr>
  	    <tr>
  	     <td><input type='number' lang='nb' class='fourDigit specialInput' id='eurcalcTOT2' name='eurcalcTOT2' value="0" readonly /></td>
  	     <td><span style="font-size: 30px;"> &euro;</span></td>
  	    </tr>
  	   </table>
</div>

<div class="clearfloat"></div>

<?php
  
  	// Query to look up categories
	$selectCats = "SELECT id, name from categories WHERE id > 2 ORDER by sortorder ASC, id ASC";
		try
		{
			$results = $pdo3->prepare("$selectCats");
			$results->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		while ($category = $results->fetch()) {
		
		$categoryid = $category['id'];
		$name = $category['name'];
  
		if ($menuType == 1) {
			echo "</table></div><br /><div class='leftfloat'><table class='default nonhover'>";
		}
	
 		// echo "<br /><br /><h3 class='title'>$name</h3>";
		echo "<h3 class='title' onClick='showhideX$categoryid()' style='cursor: pointer;'>$name <img src='images/plusnew.png' id='plus$categoryid' width='15' style='margin-left: 5px;' /> </h3><span id='menu$categoryid' style='display: none;'>
<script>
						  	function showhideX$categoryid() {
							    var x = document.getElementById('menu$categoryid');
							    if (x.style.display === 'none') {
							        x.style.display = 'block';
							        document.getElementById('plus$categoryid').src='images/minusnew.png';
							    } else {
							        x.style.display = 'none';
							        document.getElementById('plus$categoryid').src='images/plusnew.png';
							    }
							} 
</script>";
 		
 		
	// For each cat, look up products
  	$selectProduct = "SELECT pr.productid, pr.name, pr.description, pr.medicaldescription, p.purchaseid, p.salesPrice, p.realQuantity, p.photoExt FROM products pr, purchases p WHERE p.category = $categoryid AND p.productid = pr.productid AND p.closedAt IS NULL AND inMenu = 1 ORDER BY p.sortorder ASC, p.salesPrice ASC;";
		try
		{
			$results2 = $pdo3->prepare("$selectProduct");
			$results2->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
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

			
		while ($product = $results2->fetch()) {
			
	// ************************************** Calculate discount(s) ****************************************
	
	$purchaseid = $product['purchaseid'];
	$origPrice = $product['salesPrice'];
	$price = $product['salesPrice'];
	$photoExt = $product['photoExt'];
		
	$memberPhoto = 'https://ccsnubev2.com/CCS/images/purchases/' . $purchaseid . ".$photoExt";
	
/*	if (!file_exists($memberPhoto)) {
		$memberPhoto = 'images/no-image.png';
	}
	
*/	 		$i++;
	 		
	 		
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
		                <img src='images/info.png' style='inline-block; margin-top: 5px; margin-left: 5px;' id='comment$i' onClick='showhide$i()' /><div id='helpBox$i' class='helpBox' onClick='showhide$i()'><strong>{$lang['extracts-description']}:</strong><br />{$product['description']}</div>
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
		
		$commentRead = "";
		
	}		

	
	$product_row =	sprintf("
<script>
    $(document).ready(function() {

   function compute() {
          var a = $('#ppgcalc%d').val();
          var b = $('#grcalc%d').val();
          var total = a * b;
          var roundedtotal = total.toFixed(2);
          $('#eurcalc%d').val(roundedtotal);
        }
   function compute2() {
          var a = $('#eurcalc%d').val();
          var b = $('#ppgcalc%d').val();
          var total = a / b;
          var roundedtotal = total.toFixed(2);
          $('#grcalc%d').val(roundedtotal);
        }

        $('#grcalc%d').bind('keypress keyup blur', compute);
        $('#eurcalc%d').bind('keypress keyup blur', compute2);
        
		$('#free%d').click(function () {
		$('#grcalcB%d').css('display', 'inline-block');
		$('#free%d').css('display', 'none');
		});	


  }); // end ready

        </script>
	<div class='displayboxNew' style='text-align: center;'>
	 <div id='imageholder'>
	  <center><img src='%s' /></center>
	 </div>
	 <h3>%s $commentRead </h3>
	 <input class='specialInput' id='ppgcalc%d' name='sales[%d][ppg]' value='%0.02f' readonly /><span class='eurosign'>&euro;</span>
	 <div class='clearfloat'></div>
	 <center>
  	  <input type='number' lang='nb' class='fourDigit centered calc3' id='grcalc%d' name='sales[%d][grams]' placeholder='#' step='0.01' value='%s' />
  	  <input type='number' lang='nb' class='fourDigit centered calc2' id='eurcalc%d' name='sales[%d][euro]' placeholder='&euro;' step='0.01' value='%s' style='display: none;' />
  	  <input type='number' lang='nb' class='fourDigit centered calc4' id='grcalcB%d' name='sales[%d][grams2]' placeholder='#' step='0.01' value='%s' style='background-color: yellow; $displayGift'  />
  	  <input type='hidden' name='sales[%d][name]' value='%s' />
  	  <input type='hidden' name='sales[%d][purchaseid]' value='%d' />
  	  <input type='hidden' name='sales[%d][category]' value='%d' />
  	  <input type='hidden' name='sales[%d][productid]' value='%d' />
  	 </center>
	</div>",
	  $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $memberPhoto, $product['name'], $i, $i, $price, $i, $i, $savedGrams[$i], $i, $i, $savedEuro[$i], $i, $i, $savedGrams2[$i], $i, $name, $i, $product['purchaseid'], $i, $categoryid, $i, $product['productid']
	  );
	  
	  echo $product_row;
	  }
		
 		echo "</span>";
	}

echo "</center>";

?>
	  <div class="clearFloat"></div>

	 </tbody>
	 </table>
	
	
</form>

<?php





	displayFooter();