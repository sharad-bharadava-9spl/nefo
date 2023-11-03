<?php
	
	require_once 'cOnfig/connection.php';
	require_once 'cOnfig/view.php';
	require_once 'cOnfig/authenticate.php';
	require_once 'cOnfig/languages/common.php';
	require_once 'googleConfig.php';
	
	session_start();
	$accessLevel = '3';
	
	// Authenticate & authorize
	authorizeUser($accessLevel);
	
	// Retrieve System settings
	getSettings();
	
	// Did this page re-submit with a form? If so, check & store details
	if (isset($_POST['dispense'])) {
		
		if ($_POST['barcode'] != '') {
			
			// Add all shit to array
			foreach($_POST['sales'] as $sale) {
				
				$name = $sale['name'];
				$category = $sale['category'];
				$productid = $sale['productid'];
				$purchaseid = $sale['purchaseid'];
				$grams = $sale['grams'];
				$grams2 = $sale['grams2'];
				$euro = $sale['euro'];
				
				$barcodeArray .= $sale;
				
				echo "Name: $name";
				exit();
				
			}

			
			// Check if barcode exists, if not throw error
			echo "HAU"; exit();
			
		} else {
			
			echo "No More Barcodes"; exit();
	
		}
		
		
  	    $user_id = $_POST['user_id'];
		$euroTOT = $_POST['euroTOT'];
		$eurcalcTOT = $_POST['eurcalcTOT'];
		$unitsTOT = $_POST['unitsTOT'];
		$paidTOT = $_POST['paidTOT'];
		$adminComment = $_POST['adminComment'];
		$newcredit = $_POST['newcredit'];
		$credit = $_POST['credit'];
		$realCredit = $_POST['realCredit'];
		$realNewCredit = $_POST['realNewCredit'];
		$saletime = date('Y-m-d H:i:s');
		$pmtType = $_POST['pmtType'];
		
		if ($pmtType == '' || $pmtType == 0) {
			$pmtType = 3;
		}
		
		
		// Look up user credit
		$userCredit = "SELECT credit, creditEligible, maxCredit FROM users WHERE user_id = $user_id";
	
		$result = mysql_query($userCredit)
			or handleError($lang['error-userload'],"Error loading user: " . mysql_error());
	
		$row = mysql_fetch_array($result);
			$creditLookup = $row['credit'];
			$newCreditCalc = $creditLookup - $eurcalcTOT;
			$creditEligible = $row['creditEligible'];
			$maxCredit = $row['maxCredit'];
			
			if ($newCreditCalc < 0 && $creditEligible == 0 && $pmtType == 3) {
			
				$_SESSION['errorMessage'] = $lang['credit-not-sufficient'] . "!";
				
				pageStart($lang['bar'], NULL, $validationScript, "pdispense", "newsale", $lang['bar'], $_SESSION['successMessage'], $_SESSION['errorMessage']);
				
				exit();
	
				
			} else if ($creditEligible == 1 && $newCreditCalc < (0 - $maxCredit) && $pmtType == 3) {
			
				$_SESSION['errorMessage'] = $lang['credit-exceeded'] . ": " .  $maxCredit;
				
				pageStart($lang['bar'], NULL, $validationScript, "pdispense", "newsale", $lang['bar'], $_SESSION['successMessage'], $_SESSION['errorMessage']);
				
				exit();
				
			}

			
		 
		if ($pmtType == 3) {
			
			// Query to update user credit
			$updateUser = sprintf("UPDATE users SET credit = '%f' WHERE user_id = '%d';",
				mysql_real_escape_string($newCreditCalc),
				mysql_real_escape_string($user_id)
				);
					
			mysql_query($updateUser)
				or handleError($lang['error-savingcredit'],"Error updating user profile credit: " . mysql_error());
				
			// Query to add new sale to Sales table - 6 arguments
			$query = sprintf("INSERT INTO b_sales (saletime, userid, amount, unitsTot, adminComment, creditBefore, creditAfter, direct) VALUES ('%s', '%d', '%f', '%f', '%s', '%f', '%f', '%d');",
			 $saletime, $user_id, $eurcalcTOT, $unitsTOT, $adminComment, $creditLookup, $newCreditCalc, $pmtType);
	
			mysql_query($query)
				or handleError($lang['error-savingdispense'],"Error inserting sale: " . mysql_error());
	
			$saleid = mysql_insert_id();
			
			// Query to add new sale to Sales table2 - 6 arguments
			$query = sprintf("INSERT INTO f_b_sales (saletime, userid, amount, unitsTot, adminComment, creditBefore, creditAfter, direct) VALUES ('%s', '%d', '%f', '%f', '%s', '%f', '%f', '%d');",
			 $saletime, $user_id, $eurcalcTOT, $unitsTOT, $adminComment, $creditLookup, $newCreditCalc, $pmtType);
			
			mysql_query($query)
				or handleError($lang['error-savingdispense'],"Error inserting sale: " . mysql_error());
			
		} else {
			
			// Query to add new sale to Sales table - 6 arguments
			$query = sprintf("INSERT INTO b_sales (saletime, userid, amount, unitsTot, adminComment, direct) VALUES ('%s', '%d', '%f', '%f', '%s', '%d');",
			 $saletime, $user_id, $eurcalcTOT, $unitsTOT, $adminComment, $pmtType);
	
			mysql_query($query)
				or handleError($lang['error-savingdispense'],"Error inserting sale: " . mysql_error());
	
			$saleid = mysql_insert_id();
			
			// Query to add new sale to Sales table2 - 6 arguments
			$query = sprintf("INSERT INTO f_b_sales (saletime, userid, amount, unitsTot, adminComment, direct) VALUES ('%s', '%d', '%f', '%f', '%s', '%d');",
			 $saletime, $user_id, $eurcalcTOT, $unitsTOT, $adminComment, $pmtType);
			
			mysql_query($query)
				or handleError($lang['error-savingdispense'],"Error inserting sale: " . mysql_error());
				
		}
			
	foreach($_POST['sales'] as $sale) {
		$name = $sale['name'];
		$category = $sale['category'];
		$productid = $sale['productid'];
		$purchaseid = $sale['purchaseid'];
		$grams = $sale['grams'];
		$grams2 = $sale['grams2'];
		$euro = $sale['euro'];
		$gramsTot = $grams + $grams2;

		if ($gramsTot > 0) {
    	
 			if ($grams > 0 && $grams2 > 0) {
	 			
		   		// Query to add new sale to salesdetails table - ? arguments
			  	$query = sprintf("INSERT INTO b_salesdetails (saleid, category, productid, quantity, amount, purchaseid) VALUES ('%d', '%d', '%d', '%f', '%f', '%d');",
			  	$saleid, $category, $productid, $grams, $euro, $purchaseid);
			  
				mysql_query($query)
					or handleError($lang['error-savedata'],"Error inserting sale details: " . mysql_error());
	    	
	    		// Query to add new sale to salesdetails table - ? arguments
			  	$query = sprintf("INSERT INTO b_salesdetails (saleid, category, productid, quantity, amount, purchaseid) VALUES ('%d', '%d', '%d', '%f', '%f', '%d');",
			  	$saleid, $category, $productid, $grams2, '0', $purchaseid);
			  
				mysql_query($query)
					or handleError($lang['error-savedata'],"Error inserting sale details: " . mysql_error());
					
					
					
		   		// Query to add new sale to salesdetails table - ? arguments
			  	$query = sprintf("INSERT INTO f_b_salesdetails (saleid, category, productid, quantity, amount, purchaseid) VALUES ('%d', '%d', '%d', '%f', '%f', '%d');",
			  	$saleid, $category, $productid, $grams, $euro, $purchaseid);
			  
				mysql_query($query)
					or handleError($lang['error-savedata'],"Error inserting sale details: " . mysql_error());
	    	
	    		// Query to add new sale to salesdetails table - ? arguments
			  	$query = sprintf("INSERT INTO f_b_salesdetails (saleid, category, productid, quantity, amount, purchaseid) VALUES ('%d', '%d', '%d', '%f', '%f', '%d');",
			  	$saleid, $category, $productid, $grams2, '0', $purchaseid);
			  
				mysql_query($query)
					or handleError($lang['error-savedata'],"Error inserting sale details: " . mysql_error());
				
			} else {
			
				if ($grams  == 0) {
					
					$grams = $grams2;
					
				}
				
		   		// Query to add new sale to salesdetails table - ? arguments
			  	$query = sprintf("INSERT INTO b_salesdetails (saleid, category, productid, quantity, amount, purchaseid) VALUES ('%d', '%d', '%d', '%f', '%f', '%d');",
			  	$saleid, $category, $productid, $grams, $euro, $purchaseid);
			  
				mysql_query($query)
					or handleError($lang['error-savedata'],"Error inserting sale details: " . mysql_error());
					
		   		// Query to add new sale to salesdetails table - ? arguments
			  	$query = sprintf("INSERT INTO f_b_salesdetails (saleid, category, productid, quantity, amount, purchaseid) VALUES ('%d', '%d', '%d', '%f', '%f', '%d');",
			  	$saleid, $category, $productid, $grams, $euro, $purchaseid);
			  
				mysql_query($query)
					or handleError($lang['error-savedata'],"Error inserting sale details: " . mysql_error());
					
			}
		}

	}
	
		// Write to log
		$logTime = date('Y-m-d H:i:s');
	
		$query = sprintf("INSERT INTO log (logtype, logtime, user_id, operator, amount, oldCredit, newCredit) VALUES ('%d', '%s', '%d', '%d', '%f', '%f', '%f');",
		11, $logTime, $user_id, $_SESSION['user_id'], $eurcalcTOT, $creditLookup, $newCreditCalc);
		
		mysql_query($query)
			or handleError($lang['error-savedata'],"Error inserting purchase: " . mysql_error());

		$query = sprintf("INSERT INTO f_log (logtype, logtime, user_id, operator, amount, oldCredit, newCredit) VALUES ('%d', '%s', '%d', '%d', '%f', '%f', '%f');",
		11, $logTime, $user_id, $_SESSION['user_id'], $eurcalcTOT, $creditLookup, $newCreditCalc);
		
		mysql_query($query)
			or handleError($lang['error-savedata'],"Error inserting purchase: " . mysql_error());

		// On success: redirect.
		$_SESSION['successMessage'] = "Sale(s) added succesfully!";
		header("Location: bar-sale.php?saleid=" . $saleid);
		exit();

	}
	/***** FORM SUBMIT END *****/
	
	// Get the card ID
	if (isset($_GET['user_id'])) {
		$user_id = $_GET['user_id'];
	} else {
		handleError($lang['error-nomember'],"");
	}
	
	$user_id = $_GET['user_id'];
	$category = $_GET['category'];
	$purchaseid = $_GET['purchaseid'];
	
	if ($_GET['firstscan'] == 'yes') {
		
		unset($_SESSION['barCodeSale']);
		
	}

	
	// If membertime is lower than first sale date, use first sale date instead
	$userDetails = "SELECT registeredSince FROM users WHERE user_id = '{$user_id}'";
	
	// Does user ID exist?
	$userCheck = mysql_query($userDetails);
	if(mysql_num_rows($userCheck) == 0) {
   		handleError($lang['error-useridnotexist'],"");
	}
	
	$result = mysql_query($userDetails)
		or handleError($lang['error-userload'],"Error loading user: " . mysql_error());
	
	$row = mysql_fetch_array($result);
	$registeredSince = $row['registeredSince'];
	
	if ($registeredSince < '2014-11-01') {
		$memberSince = "'2014-11-01'";
	} else {
		$memberSince = 'registeredSince';
	}
	
	
		// Look up user details for showing profile on the Sales page
		$userDetails2 = "SELECT memberno, paidUntil, userGroup, first_name, last_name, datediff(curdate(),$memberSince) AS daysMember, paymentWarning, paymentWarningDate, mconsumption, day, month, year, credit, creditEligible, discountBar, photoExt, cardid, exento, starCat FROM users WHERE user_id = '{$user_id}'";
	
		$result2 = mysql_query($userDetails2)
			or handleError($lang['error-userload'],"Error loading user: " . mysql_error());
	
		$row2 = mysql_fetch_array($result2);
		$memberno = $row2['memberno'];
		$first_name = $row2['first_name'];
		$last_name = $row2['last_name'];
		$paidUntil = $row2['paidUntil'];
		$userGroup = $row2['userGroup'];
		$day = $row2['day'];
		$month = $row2['month'];
		$year = $row2['year'];
		$mconsumption = $row2['mconsumption'];
		$daysMember = $row2['daysMember'];
		$paymentWarning = $row2['paymentWarning'];
		$paymentWarningDate = $row2['paymentWarningDate'];
		$paymentWarningDateReadable = date('d M', strtotime($paymentWarningDate));
		$credit = $row2['credit'];
		$realCredit = $credit;
		$creditEligible = $row2['creditEligible'];
		$discount = $row2['discountBar'];
		$photoExt = $row2['photoExt'];
		$cardid = $row2['cardid'];
		$exento = $row2['exento'];
		$starCat = $row2['starCat'];	
		
		if ($starCat == 1) {
	   		$userStar = "<img src='images/star-yellow.png'/>";
		} else if ($starCat == 2) {
	   		$userStar = "<img src='images/star-black.png' />";
		} else if ($starCat == 3) {
	   		$userStar = "<img src='images/star-green.png' />";
		} else if ($starCat == 4) {
	   		$userStar = "<img src='images/star-red.png' />";
		} else {
	   		$userStar = "";
		}
		
		
		if ($_SESSION['barGift'] == 1 && $_SESSION['barMenuType'] == 1) {
			$giftOption = "<img src='images/free-green.png' id='free%d' style='margin-bottom: 0; margin-left: 5px;' />";
		} else if ($_SESSION['barGift'] == 1 && $_SESSION['barMenuType'] == 0) {
			$giftOption = "<img src='images/free.png' id='free%d' style='margin-bottom: 0; margin-left: 5px;' />";
		} else {
			$giftOption = "<input type='hidden' value='%d' />";
		}

		
// Allow negative credit
if ($creditEligible == 1) {
	$validationScript = <<<EOD
	
    $(document).ready(function() {
	    
$(function(){
    $('#chipClick').click(function() {
        $("#cardscan").val('$cardid');
    });
});

	    	    
	  $('#registerForm').validate({
		  ignore: [],
		  rules: {
			  cardid: {
				  required: true
			  },
			  pmtType: {
				  required: true,
				  range: [0,3]
			  }
    	}, // end rules
		  errorPlacement: function(error, element) {
			  
			  if ( element.is(":radio") || element.is(":checkbox")){
				 error.appendTo(element.parent());
			} else {
				return true;
			}
		},
    	  submitHandler: function() {
   $(".oneClick").attr("disabled", true);
   form.submit();
	    	  }
	  }); // end validate
EOD;

} else {
	$validationScript = <<<EOD
	
    $(document).ready(function() {
	    
$(function(){
    $('#chipClick').click(function() {
        $("#cardscan").val('$cardid');
    });
});

	    	    
	  $('#registerForm').validate({
		  ignore: [],
		  rules: {
			  newcredit: {
				  range: [0,100000]
			  },
			  cardid: {
				  required: true
			  },
			  pmtType: {
				  required: true,
				  range: [0,3]
			  }
    	}, // end rules
		  errorPlacement: function(error, element) {
			  
			  if ( element.is(":radio") || element.is(":checkbox")){
				 error.appendTo(element.parent());
			} else {
				return true;
			}
		},
    	  submitHandler: function() {
   $(".oneClick").attr("disabled", true);
   form.submit();
	    	  }
	  }); // end validate
EOD;
}
	  
	$validationScript .= <<<EOD

	  
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
  $('#grcalcTOT2').val(sumC);
  
}


	if ($credit <= 0) {
	   function computeTot() {
	          var a = $('#realCredit').val();
	          var b = $('#eurcalcTOT').val();
	          var total = a - b;
	          var roundedtotal = total.toFixed(2);
	          $('#realNewCredit').val(roundedtotal);
	   }
	} else {
   function computeTot() {
          var a = $('#realCredit').val();
          var b = $('#eurcalcTOT').val();
          var total = a - b;
          var roundedtotal = total.toFixed(2);
          $('#newcredit').val(roundedtotal);
	      $('#realNewCredit').val(roundedtotal);
        }
    }
        
	$("#pmt1").click(function () {
		$("#newcredit").val($("#credit").val());
	});	
	$("#pmt2").click(function () {
		$("#newcredit").val($("#credit").val());
	});	
	$("#pmt3").click(function () {
	    getItems();
	    computeTot();
	});	
        


	$("#openComment").click(function () {
	$(".hiddenComment").css("display", "block");
	$("#openComment").css("display", "none");
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
	
	/*
	
		Transform 20 to 0,8
		x = 100 - 20
		x / 100
	
	
	*/


// Removed CLICK from this one:	
$(document).on('click keypress keyup blur', function(event) {
   if (!$(event.target).is("#cardscan")) {
    if (!$(event.target).is("#pmt1")) {
     if (!$(event.target).is("#pmt2")) {
      if (!$(event.target).is("#pmt3")) {
       if (!$(event.target).is("#submitButton")) {
		// uncheck all radio boxes
		$("input:radio").attr("checked", false);
		getItems();
	    computeTot();
       }
      }
     }
    }
   }
});

	getItems();
    computeTot();
  }); // end ready  

EOD;

	pageStart($lang['bar'], NULL, $validationScript, "pdispense", "newsale", $lang['bar'], $_SESSION['successMessage'], $_SESSION['errorMessage']);
	
	// First lookup userdetails, incl. medicinal or not, then decide what to do (which quqeries to execute)
	$userDetails = "SELECT usageType FROM users WHERE user_id = {$user_id}";
	$result = mysql_query($userDetails)
		or handleError($lang['error-usersload'],"Error loading users from db: " . mysql_error());
		
    $row = mysql_fetch_array($result);
	$usageType = $row['usageType'];
	
if ($_SESSION['barsig'] == 1) {
			echo <<<EOD
<script type="text/javascript" src="scripts/SigWebTablet.js"></script>

<script type="text/javascript">
var tmr;

function onSign()
{
   var ctx = document.getElementById('cnv').getContext('2d');         
   SetDisplayXSize( 500 );
   SetDisplayYSize( 100 );
   SetJustifyMode(0);
   ClearTablet();
   tmr = SetTabletState(1, ctx, 50) || tmr;
}

function onClear()
{
   ClearTablet();
}

function onDone()
{
   if(NumberOfTabletPoints() == 0)
   {
      alert("Tienes que firmar!");
   }
   else
   {
      SetTabletState(0, tmr);
      //RETURN TOPAZ-FORMAT SIGSTRING
      SetSigCompressionMode(1);
      document.FORM1.bioSigData.value=GetSigString();
      document.FORM1.sigStringData.value += GetSigString();
      //this returns the signature in Topaz's own format, with biometric information


      //RETURN BMP BYTE ARRAY CONVERTED TO BASE64 STRING
      SetImageXSize(500);
      SetImageYSize(100);
      SetImagePenWidth(5);
      GetSigImageB64(SigImageCallback);
      document.getElementById("button2").style.background='#b6ec98';
   }
}

function SigImageCallback( str )
{
   document.FORM1.sigImageData.value = str;
}


	
</script> 
<script type="text/javascript">
window.onunload = window.onbeforeunload = (function(){
closingSigWeb()
})

function closingSigWeb()
{
   ClearTablet();
   SetTabletState(0, tmr);
}

</script>
EOD;
}


 
?>
<form id="registerForm" action="" method="POST">




<div class="clearfloat"></div>


<?php

	// See if a product has already been scanned
	// If so do calculations
	
	
	// If not (first scan):
	// Find price of product and insert it to the memberbox (so memberbox must come AFTER this code)
	
	// While, for each product scanned (from array)
	
	if ($_GET['firstscan'] == 'yes') {
		
		unset($_SESSION['barCodeSale']);
		$unit = 1;
		
	}

		

	$selectServices = "SELECT pr.productid, pr.name, p.purchaseid, p.salesPrice, p.purchaseQuantity, pr.photoExt, p.category FROM b_products pr, b_purchases p WHERE p.productid = pr.productid AND p.purchaseid = $purchaseid;";

	$resultServices = mysql_query($selectServices)
		or handleError($lang['error-prodprices'],"Error loading flower prices from db: " . mysql_error());
		
	$catDiscount = $rowCD['discount'];

				
	$service = mysql_fetch_array($resultServices);
		$name = $service['name'];
		$categoryid = $service['category'];
		$productid = $service['productid'];
		$photoExt = $service['photoExt'];
		$purchaseid = $service['purchaseid'];
		
			// ************************************** Calculate discount(s) ****************************************
			
			if ($_SESSION['showOrigPriceBar'] == 1) {
				
				$normalPrice = "(" . ($service['salesPrice']) . " ".$_SESSION['currencyoperator'].")";
				
			} else {
			
				$normalPrice = "";
				
			}
						
			// Individual purchase discount
			$selectPurchaseDiscount = "SELECT discount, fijo FROM b_inddiscounts WHERE user_id = $user_id AND purchaseid = $purchaseid";
			
			$resultPurchaseDiscount = mysql_query($selectPurchaseDiscount)
				or handleError($lang['error-prodprices'],"Error loading flower prices from db: " . mysql_error());
				
			$rowD = mysql_fetch_array($resultPurchaseDiscount);
				$prodDiscount = $rowD['discount'];
				$prodFijo = $rowD['fijo'];
			
			if ($prodFijo != 0 || $prodFijo != '') {
		
				$price = number_format($prodFijo,2);
				
			} else if ($prodDiscount != 0 || $prodDiscount != '') {
				
				$prodDiscount = (100 - $prodDiscount) / 100;
				$price = number_format(($service['salesPrice'] * $prodDiscount),2);
				
			} else if ($catDiscount != 0 || $catDiscount != '') {
				
				$catCurrentDiscount = (100 - $catDiscount) / 100;
				$price = number_format(($service['salesPrice'] * $catCurrentDiscount),2);
		
			} else if ($discount != 0) {
				
				$dispDiscount = (100 - $discount) / 100;
				$price = number_format(($service['salesPrice'] * $dispDiscount),2);
				
			} else {
				
				$price = number_format($service['salesPrice'],2);
				
			}
			
		$units = 1;
		
		
	echo "<table class='default nonhover'>";
		
		if ($_SESSION['showStockBar'] == 1) {
			$stockDisplay = "<td class='right'>$estStock u</td>";
		}
	  	
	$extract_row =	sprintf("
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
        
	   <tr>
	    <td class='left'>%s</td>
		<td class='right'>%0.02f {$_SESSION['currencyoperator']} <input type='hidden' id='ppgcalc%d' name='sales[%d][ppg]' value='%0.01f' /></td>
	    <td class='right'><span style='color: red;' class='smallerfont'>$normalPrice</span></td>
	    $stockDisplay
	    <td><input type='number' lang='nb' class='fourDigit centered calc3' style='border: #5aa242 solid 2px; width: 50px; border-radius: 2px; padding: 2px 1px;' id='grcalc%d' name='sales[%d][grams]' value='$unit' readonly /></td>
  	    <td><input type='number' lang='nb' class='fourDigit centered calc2' id='eurcalc%d' name='sales[%d][euro]' placeholder='{$_SESSION['currencyoperator']}' step='0.01' value='$price' style='//display: none;' /></td>
  	    <td><input type='number' lang='nb' class='fourDigit centered calc4' id='grcalcB%d' name='sales[%d][grams2]' placeholder='#' step='0.01' style='border: #5aa242 solid 2px; width: 50px; border-radius: 2px; padding: 2px 1px; background-color: yellow; display: none;' /> $giftOption</td>
  	   </tr>
  	  <input type='hidden' name='sales[%d][name]' value='%s' />
  	  <input type='hidden' name='sales[%d][productid]' value='%d' />
  	  <input type='hidden' name='sales[%d][purchaseid]' value='%d' />
  	  <input type='hidden' name='sales[%d][category]' value='%d' />
  	  <input type='hidden' name='sales[%d][arrayNo]' value='%d' />
  	 </center>
	</div>",
	  $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $name, $price, $i, $i, $price, $i, $i, $i, $i, $i, $i, $i, $i, $name, $i, $productid, $i, $service['purchaseid'], $i, $categoryid, $i, $i
	  );

	  	
	  echo $extract_row;
	  
	  
	  
	  
		echo "</table></div>";
	  
echo "</center>";

?>
	  <div class="clearFloat"></div>

	 </tbody>
	 </table>
	 
<div id="memberbox">
<img src="images/minimize.png" class="closeImg pointer" id="minimizeMemberBox" />

<?php
echo "<a href='new-picture.php?user_id=$user_id'><img src='{$google_root}images/members/$user_id.$photoExt' class='userPicture' /></a><span class='memberinfo'><a href='#' id='chipClick'><img src='images/rfid.png' /></a>&nbsp; $userStar <a href='profile.php?user_id=$user_id'><span class='membername'>$memberno $first_name $last_name</span></a>";

	if ($userGroup == 5 && $_SESSION['membershipFees'] == 1 && $exento == 0) {  // show Member w/ expiry
	
		$memberExp = date('y-m-d', strtotime($paidUntil));
		$memberExpReadable = date('d M', strtotime($paidUntil));
		$timeNow = date('y-m-d');
		
		if (strtotime($memberExp) == strtotime($timeNow)) {
			echo "<br /><a href='pay-membership.php?user_id=$user_id'><img src='images/exclamation-20.png' class='warningIcon' style='margin-bottom: -3px; margin-left: 3px;' /> <span class='yellow'>" . $lang['member-expirestoday'] . "</span></a>";
	  	} else if (strtotime($memberExp) < strtotime($timeNow)) {
		  	echo "<br /><a href='pay-membership.php?user_id=$user_id'><img src='images/exclamation-20.png' class='warningIcon' style='margin-bottom: -3px; margin-left: 7px;' /> <span class='yellow'>" . $lang['member-expiredon'] . ": $memberExpReadable.</span></a>";
		  	
		  	if ($paymentWarning == '1') {
		  	echo "<br /><a href='pay-membership.php?user_id=$user_id'><img src='images/exclamation-22.png' class='warningIcon' style='margin-bottom: -4px; margin-left: 7px;' /> <img src='images/exclamation-22.png' class='warningIcon' style='margin-bottom: -4px; margin-left: -15px; margin-right: 1px;' /> <span class='yellow'>" . $lang['member-receivedwarning'] . "</span></a>";
		  	}
		  	
		}
	}
		if ($usageType == '1') {
		echo "<br /><img src='images/medical-20.png' style='margin-bottom: -4px; margin-left: 7px; margin-right: 2px;' /> <span class='yellow'>" . $lang['medicinal-user'] . "</span>";
	}
	
	if (date('m-d') == date('m-d', strtotime($year . "-" . $month . "-" . $day . " 00:00:00"))) {
		echo "<br /><img src='images/cake-22.png' class='warningIcon' style='margin-bottom: -4px; margin-left: 7px; margin-right: 2px;' /> <span class='yellow'>" . $lang['global-birthday'] . "</span>";
	}
	
	// Is the user a high roller?
	$selectHighRollerLimit = "SELECT highRollerWeekly FROM systemsettings";

	$result2 = mysql_query($selectHighRollerLimit)
		or handleError($lang['error-loadsales'],"Error loading sale from db: " . mysql_error());
		
	$row = mysql_fetch_array($result2);
		$highRollerWeekly = $row['highRollerWeekly'];

	if ($totalAmountPerWeek >= $highRollerWeekly) {
		echo "<br /><img src='images/hi-roller.png' class='warningIcon' style='margin-bottom: -4px; margin-left: 7px; margin-right: 2px;' /> <span class='yellow'>High roller</span>";
	}
	
	?>
	</span>
	<div class="clearFloat"></div>
 	   <input type='hidden' id='eurcalcTOTexp' name='euroTOT' />
 	   <input type='hidden' name='dispense' value='done' /> 	   
  	   <input type='hidden' name='user_id' value='<?php echo $user_id; ?>' />
  	   <input type='hidden' name='realCredit' id='realCredit' value='<?php echo $realCredit; ?>' />
  	   <input type='hidden' name='realNewCredit' id='realNewCredit' value='<?php echo $realNewCredit; ?>' />
  	   <input type='hidden' name='totDiscountInput' id='totDiscountInput' value='' />
  	   
  	   
  	   <table id="memberAmount">
  	    <tr>
  	     <td><input type='number' lang='nb' class='specialInput first' id='unitcalcTOT' name='unitsTOT' value="0" readonly /> </td>
  	     <td style='text-align: left;'><span style="font-size: 20px;">&nbsp;u.</span></td>
  	     <td style='text-align: right;'><?php echo $lang['global-credit']; ?>:</td>
  	     <td style='text-align: right;'><?php echo $lang['dispense-newcredit']; ?>:</td>
  	    </tr>
   	    <tr>
  	     <td><input type='number' lang='nb' class='specialInput' id='eurcalcTOT' name='eurcalcTOT' value="0" readonly /> </td>
  	     <td style='text-align: left;'><span style="font-size: 20px;">&nbsp;<?php echo $_SESSION['currencyoperator'] ?></span></td>
  	     <?php if ($credit < 0) { ?>
  	     <td><input type='number' lang='nb' class="specialInput" style='color: red;' id='credit' name='credit' value='0' readonly /> <?php echo $_SESSION['currencyoperator'] ?></td>
  	     <td><input type='number' lang='nb' class='specialInput' style='color: red;' id='newcredit' name='newcredit' value='0' readonly /> <?php echo $_SESSION['currencyoperator'] ?></td>
  	     <?php } else { ?>
  	     <td><input type='number' lang='nb' class="specialInput" id='credit' name='credit' value='<?php echo number_format($credit,2); ?>' readonly /> <?php echo $_SESSION['currencyoperator'] ?></td>
  	     <td><input type='number' lang='nb' class='specialInput' id='newcredit' name='newcredit' value='<?php echo number_format($credit,2); ?>' readonly /> <?php echo $_SESSION['currencyoperator'] ?></td>
  	     <?php } ?>
  	     
  	    </tr>
  	   </table>
  	   
<!-- With direct dispensing enabled, show payment options -->
<?php if ($_SESSION['creditOrDirect'] == 0) { ?>

  	   <center><span style='font-size: 17px; display: inline-block; margin-top: 10px; text-transform: uppercase;'><?php echo $lang['paid-by']; ?>:</span><br />
        <input type="radio" id="pmt1" name="pmtType" value='1' />
        <label for="pmt1"><span class='full' id="pmt1trigger"><?php echo $lang['cash']; ?></span></label>
        
        <input type="radio" id="pmt2" name="pmtType" value='2' />
        <label for="pmt2"><span class='full' id="pmt2trigger"><?php echo $lang['card']; ?></span></label>

        <input type="radio" id="pmt3" name="pmtType" value='3' />
        <label for="pmt3"><span class='full' id="pmt3trigger"><?php echo $lang['global-credit']; ?></span></label>
  	   </center>

<?php } ?>

<!-- With additional discounts enabled, show discounts -->
<?php if ($_SESSION['checkoutDiscountBar'] == 1) { ?>
  	   <center><span style='font-size: 17px; display: inline-block; margin-top: 10px; text-transform: uppercase;'><?php echo $lang['member-discount']; ?> <span id='totDiscount'>(a%)</span>:</span><br />
        <input type="checkbox" id="fee0" name="inDiscount" value='5' />
        <label for="fee0"><span class='full2'>5%</span></label>
        
        <input type="checkbox" id="fee1" name="inDiscount" value='10' />
        <label for="fee1"><span class='full2'>10%</span></label>
        
        <input type="checkbox" id="fee2" name="inDiscount" value='20' />
        <label for="fee2"><span class='full2'>20%</span></label>

        <input type="checkbox" id="fee3" name="inDiscount" value='30' />
        <label for="fee3"><span class='full2'>30%</span></label>
  	   </center>
<?php } ?>

<center>

<?php
	if ($_SESSION['iPadReaders'] == 0) {
		
		// Chip
		if ($_SESSION['barsig'] == 0) {
			echo <<<EOD
<input type="text" class="donateOrNot" id="cardscan" name="cardid" maxlength="30" placeholder="{$lang['global-scantoconfirm']}" />
EOD;
		// Topaz
		} else if ($_SESSION['barsig'] == 1) {
			echo <<<EOD
<canvas id="cnv" name="cnv" width="500" height="100" onclick="javascript:onSign()" style="border: 2px solid white;"></canvas><br />
<input id="button2" name="DoneBtn" type="button" value="Finalizar" onclick="javascript:onDone()" style="margin-left: 10px; width: 80px;" /><br />
EOD;
			
		}
	}
?>
<br />
       <center><a href="#" id="openComment" style="vertical-align: bottom;">+ <?php echo $lang['global-comment']; ?></a></center><br />
       <center><textarea class="hiddenComment" name="adminComment" placeholder="<?php echo $lang['global-comment']; ?>?"></textarea></center>
  	   
 <button type="submit" class='oneClick' id='submitButton' name='oneClick'><?php echo $lang['global-save']; ?></button><br /><br />
 <!--<a href="?saveCart&user_id=<?php echo $user_id; ?>">Save</a>--><a href="#" onclick="event.preventDefault(); document.getElementById('registerForm').reset();"><img src="images/clear-cart.png" /></a>&nbsp;&nbsp;&nbsp;&nbsp;<a href="barcode-bar.php?user_id=<?php echo $user_id; ?>"><img src="images/barcode2.png"  width="30" /></a></center>
  	</div>
</div> <!-- end memberbox -->

<div id='hiddenSummary'>
<img src="images/maximize.png" class="closeImg pointer" id="minimizeSummaryBox" />
 	   <input type='number' lang='nb' class='fourDigit specialInput first' id='grcalcTOT2' name='gramsTOT2' value="0" readonly /> u.<br />
  	   <input type='number' lang='nb' class='fourDigit specialInput' id='eurcalcTOT2' name='eurcalcTOT2' value="0" readonly /> <span style="font-size: 30px;"><?php echo $_SESSION['currencyoperator'] ?></span>
</div>	 

<center>
  <input type="text" name="barcode" id="focus" maxlength="80" autofocus /><br />
</center>
	
<?php if ($_SESSION['barsig'] == 1) { ?>
<INPUT TYPE=HIDDEN NAME="bioSigData">
<INPUT TYPE=HIDDEN NAME="sigImgData">
<INPUT TYPE=HIDDEN NAME="sigStringData" />
<INPUT TYPE=HIDDEN NAME="sigImageData" />
<?php } ?>

</form>


<?php displayFooter(); ?>

