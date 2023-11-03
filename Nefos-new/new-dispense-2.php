<?php
	
	require_once 'cOnfig/connection.php';
	require_once 'cOnfig/view.php';
	require_once 'cOnfig/authenticate.php';
	require_once 'cOnfig/languages/common.php';
	
	session_start();
	$accessLevel = '3';
	
	// Authenticate & authorize
	authorizeUser($accessLevel);
	
	// Retrieve System settings
	getSettings();
	
/*	// Did the operator ask to save the cart?
	if (isset($_GET['saveCart'])) {
		
		$cartID = 'savedCart' . $_GET['user_id'];
		$_SESSION[$cartID] = $_POST['sales'];
		
		$_SESSION['successMessage'] = "Cart saved succesfully!";
		header("Location: index.php");
		exit();

		
	}*/
		

	// Did this page re-submit with a new donation? 
	if ($_POST['newDonation'] > 0) {
		
		$userid = $_POST['user_id'];
  	    $credit = $_POST['credit'];
		$amount = $_POST['newDonation'];
		$donatedTo = 1;
		$registertime = date('Y-m-d H:i:s');
		
		$_SESSION['savedSale'] = $_POST['sales'];
		
		$operator = $_SESSION['user_id'];
		
		// Look up user credit
		$userCredit = "SELECT credit FROM users WHERE user_id = '{$userid}'";
		
		$result = mysql_query($userCredit)
			or handleError($lang['error-userload'],"Error loading user: " . mysql_error());
	
		$row = mysql_fetch_array($result);
			$oldCredit = $row['credit'];

		$newCredit = $amount + $oldCredit;
				
		// Query to add to Donations table
		 $query = sprintf("INSERT INTO donations (userid, donationTime, type, amount, comment, creditBefore, creditAfter, donatedTo, operator) VALUES ('%d', '%s', '%d', '%f', '%s', '%f', '%f', '%d', '%d');",
		  $userid, $registertime, '1', $amount, $comment, $oldCredit, $newCredit, $donatedTo, $operator);
		  
		mysql_query($query)
			or handleError($lang['error-savedonation'],"Error inserting donation: " . mysql_error());
		
		 $query = sprintf("INSERT INTO f_donations (userid, donationTime, type, amount, comment, creditBefore, creditAfter, donatedTo, operator) VALUES ('%d', '%s', '%d', '%f', '%s', '%f', '%f', '%d', '%d');",
		  $userid, $registertime, '1', $amount, $comment, $oldCredit, $newCredit, $donatedTo, $operator);
		  
		mysql_query($query)
			or handleError($lang['error-savedonation'],"Error inserting donation: " . mysql_error());
			
		// Query to update user profile
		$updateUser = sprintf("UPDATE users SET credit = '%f', lastDispense = '%s' WHERE user_id = '%d';",
			mysql_real_escape_string($newCredit),
			mysql_real_escape_string($registertime),
			mysql_real_escape_string($userid)
			);
				
		mysql_query($updateUser)
			or handleError($lang['error-savedata'],"Error updating user profile: " . mysql_error());
				
			// On success: redirect.
			$_SESSION['successMessage'] = $lang['global-added'] . " " . $amount . $lang['donation-addedsuccessfully'] . $newCredit . "&euro;";
			
			header("Location: new-dispense-2.php?user_id=$userid");
			exit();
		
	}
	
if ($_SESSION['realWeight'] == 0) {
	
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
		

		if ($day > 0 && $month > 0 && $year > 0) {
			$date_timestamp = strtotime($month . "/" . $day . "/" . $year);
			$saletime = date("Y-m-d H:i:s", $date_timestamp);
		} else {
			$saletime = date('Y-m-d H:i:s');
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
			
			/* 
			
				Credit: 	 8,50
				Purchase: 	38,50
				Newcredit: -30,00
				
			
			*/
			
			if ($newCreditCalc < 0 && $creditEligible == 0) {
			
				$_SESSION['errorMessage'] = 'Saldo no suficiente para dispensar!';
				
				pageStart($lang['title-dispense'], NULL, $validationScript, "pdispense", "newsale", $lang['global-dispensecaps'], $_SESSION['successMessage'], $_SESSION['errorMessage']);
				
				exit();
	
				
			} else if ($creditEligible == 1 && $newCreditCalc < (0 - $maxCredit)) {
			
				$_SESSION['errorMessage'] = "No puedes dispensar: Limite de credito excedido!<br />Limite de credito: $maxCredit";
				
				pageStart($lang['title-dispense'], NULL, $validationScript, "pdispense", "newsale", $lang['global-dispensecaps'], $_SESSION['successMessage'], $_SESSION['errorMessage']);
				
				exit();
				
			}
			
		
		// Add scan to scan history
		  $cardid = $_POST['cardid'];
		  $query = sprintf("INSERT INTO scanhistory (userid, scanTime, cardid, scanType) VALUES ('%d', '%s', '%s', '%d');",
		  $user_id, $saletime, $cardid, '4');
		  
		mysql_query($query)
			or handleError($lang['error-savescan'],"Error inserting scan data: " . mysql_error());

			// Query to add new sale to Sales table - 6 arguments
		  	$query = sprintf("INSERT INTO sales (saletime, userid, amount, amountPaid, quantity, units, adminComment, settled, settledTo, creditBefore, creditAfter, expiry, realQuantity, discount) VALUES ('%s', '%d', '%f', '%f', '%f', '%f', '%s', '%s', '%d', '%f', '%f', '%s', '%f', '%f');",
		  	$saletime, $user_id, $eurcalcTOT, $eurcalcTOT, $gramsTOT, $unitsTOT, $adminComment, $saletime, '1', $creditLookup, $newCreditCalc, $paidUntil, $gramsTOT, $totDiscountInput);

		if ($_SESSION['creditOrDirect'] == 1) {
		// Query to update user credit
		$updateUser = sprintf("UPDATE users SET credit = '%f' WHERE user_id = '%d';",
			mysql_real_escape_string($newCreditCalc),
			mysql_real_escape_string($user_id)
			);
				
		mysql_query($updateUser)
			or handleError($lang['error-savingcredit'],"Error updating user profile credit: " . mysql_error());
			
		}
		
		mysql_query($query)
			or handleError($lang['error-savingdispense'],"Error inserting sale: " . mysql_error());
			
		$saleid = mysql_insert_id();

			// Query to add new sale to Sales table - 6 arguments
		  	$query = sprintf("INSERT INTO f_sales (saletime, userid, amount, amountPaid, quantity, units, adminComment, settled, settledTo, creditBefore, creditAfter, expiry, realQuantity, discount) VALUES ('%s', '%d', '%f', '%f', '%f', '%f', '%s', '%s', '%d', '%f', '%f', '%s', '%f', '%f');",
		  	$saletime, $user_id, $eurcalcTOT, $eurcalcTOT, $gramsTOT, $unitsTOT, $adminComment, $saletime, '1', $creditLookup, $newCreditCalc, $paidUntil, $gramsTOT, $totDiscountInput);
		  	
		mysql_query($query)
			or handleError($lang['error-savingdispense'],"Error inserting sale: " . mysql_error());
		
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
			  
				mysql_query($query)
					or handleError($lang['error-savedata'],"Error inserting sale details: " . mysql_error());
				
	    		// Query to add new sale to salesdetails table - ? arguments
			  	$query = sprintf("INSERT INTO salesdetails (saleid, category, productid, quantity, amount, purchaseid, realQuantity) VALUES ('%d', '%d', '%d', '%f', '%f', '%d', '%f');",
			  	$saleid, $category, $productid, $grams2, '0', $purchaseid, $grams2);
			  
				mysql_query($query)
					or handleError($lang['error-savedata'],"Error inserting sale details: " . mysql_error());

	    		// Query to add new sale to salesdetails table - ? arguments
			  	$query = sprintf("INSERT INTO f_salesdetails (saleid, category, productid, quantity, amount, purchaseid, realQuantity) VALUES ('%d', '%d', '%d', '%f', '%f', '%d', '%f');",
			  	$saleid, $category, $productid, $grams, $euro, $purchaseid, $grams);
			  
				mysql_query($query)
					or handleError($lang['error-savedata'],"Error inserting sale details: " . mysql_error());
				
	    		// Query to add new sale to salesdetails table - ? arguments
			  	$query = sprintf("INSERT INTO f_salesdetails (saleid, category, productid, quantity, amount, purchaseid, realQuantity) VALUES ('%d', '%d', '%d', '%f', '%f', '%d', '%f');",
			  	$saleid, $category, $productid, $grams2, '0', $purchaseid, $grams2);
			  
				mysql_query($query)
					or handleError($lang['error-savedata'],"Error inserting sale details: " . mysql_error());

				
			} else {
			
				if ($grams  == 0) {
					
					$grams = $grams2;
					
				}
	    	
	    		// Query to add new sale to salesdetails table - ? arguments
			  	$query = sprintf("INSERT INTO salesdetails (saleid, category, productid, quantity, amount, purchaseid, realQuantity) VALUES ('%d', '%d', '%d', '%f', '%f', '%d', '%f');",
			  	$saleid, $category, $productid, $grams, $euro, $purchaseid, $grams);
			  
				mysql_query($query)
					or handleError($lang['error-savedata'],"Error inserting sale details: " . mysql_error());
					
	    		// Query to add new sale to salesdetails table - ? arguments
			  	$query = sprintf("INSERT INTO f_salesdetails (saleid, category, productid, quantity, amount, purchaseid, realQuantity) VALUES ('%d', '%d', '%d', '%f', '%f', '%d', '%f');",
			  	$saleid, $category, $productid, $grams, $euro, $purchaseid, $grams);
			  
				mysql_query($query)
					or handleError($lang['error-savedata'],"Error inserting sale details: " . mysql_error());

			}
		}

	}
	
		// Write to log
		$logTime = date('Y-m-d H:i:s');
	
		$query = sprintf("INSERT INTO log (logtype, logtime, user_id, operator, amount, oldCredit, newCredit) VALUES ('%d', '%s', '%d', '%d', '%f', '%f', '%f');",
		10, $logTime, $user_id, $_SESSION['user_id'], $eurcalcTOT, $creditLookup, $newCreditCalc);
		
		mysql_query($query)
			or handleError($lang['error-savedata'],"Error inserting purchase: " . mysql_error());

		$query = sprintf("INSERT INTO f_log (logtype, logtime, user_id, operator, amount, oldCredit, newCredit) VALUES ('%d', '%s', '%d', '%d', '%f', '%f', '%f');",
		10, $logTime, $user_id, $_SESSION['user_id'], $eurcalcTOT, $creditLookup, $newCreditCalc);
		
		mysql_query($query)
			or handleError($lang['error-savedata'],"Error inserting purchase: " . mysql_error());

		// On success: redirect.
		$_SESSION['successMessage'] = $lang['dispense-added'];
		header("Location: dispense.php?saleid=" . $saleid);
		exit();

	}
	/***** FORM SUBMIT END *****/
	
	
	// Get the card ID
	if ($_POST['cardid'] != '') {
		
		$cardid = $_POST['cardid'];
		$userDetails = "SELECT user_id FROM users WHERE cardid = '{$cardid}'";
		$userCheck = mysql_query($userDetails);
		if(mysql_num_rows($userCheck) == 0) {
	   		handleError($lang['error-keyfob'],"");
		}
	
		$result = mysql_query($userDetails)
			or handleError($lang['error-userload'],"Error loading user: " . mysql_error());
	
		$row = mysql_fetch_array($result);
		$user_id = $row['user_id'];
		
		// Add scan to scan history
		$scanTime = date('Y-m-d H:i:s'); // 	$purchaseDate = date('Y-m-d H:i:s'); ????
		
		$cardid = $_POST['cardid'];
		  $query = sprintf("INSERT INTO scanhistory (userid, scanTime, cardid, scanType) VALUES ('%d', '%s', '%s', '%d');",
		  $user_id, $scanTime, $cardid, '3');
		  
		mysql_query($query)
			or handleError($lang['error-savedata'],"Error inserting purchase: " . mysql_error());
					
	} else if (isset($_POST['userSelect'])) {
		$user_id = $_POST['userSelect'];
	} else if (isset($_GET['user_id'])) {
		$user_id = $_GET['user_id'];
	} else {
		handleError($lang['error-nomember'],"");
	}
	
	// Check if member is eligible for dispensing
	$userDetails = "SELECT userGroup, paymentWarning, paymentWarningDate, paidUntil FROM users WHERE user_id = '{$user_id}'";
	
	$result = mysql_query($userDetails)
		or handleError($lang['error-userload'],"Error loading user: " . mysql_error());
	
	$row = mysql_fetch_array($result);
		$userGroup = $row['userGroup'];
		$paymentWarning = $row['paymentWarning'];
		$paymentWarningDate = $row['paymentWarningDate'];
		$paidUntil = strtotime(date('Y-m-d H:i', strtotime($row['paidUntil'])));
		$nowTime = strtotime(date('Y-m-d H:i'));
		$pwd = strtotime(date('Y-m-d H:i',strtotime($paymentWarningDate)));
		
	if ($userGroup > 5) {
		$_SESSION['errorMessage'] = $lang['cannot-dispense'];
		header("Location: no-dispense.php?user_id=$user_id");
		exit();
	}
		
	if ($_SESSION['dispExpired'] == 0) {
		if ($paymentWarning == 1 && $nowTime > $pwd && $nowTime > $paidUntil) {
			$_SESSION['errorMessage'] = $lang['cannot-dispense'];
			header("Location: no-dispense.php?user_id=$user_id");
			exit();
		}
	}
	
/*	$cartID = 'savedCart' . $user_id;
	echo $cartID;
	echo "h: " . $_SESSION[$cartID];*/
	
	// Was a donation made, or a cart saved?
	if (isset($_SESSION['savedSale'])) {
		
		$savedSale = $_SESSION['savedSale'];
		unset($_SESSION['savedSale']);
		
	} /*else if (isset($_SESSION[$cartID])) {
		
		$savedSale = $_SESSION[$cartID];
		unset($_SESSION[$cartID]);
		
	}*/

	
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
		$userDetails2 = "SELECT memberno, paidUntil, userGroup, first_name, last_name, datediff(curdate(),$memberSince) AS daysMember, paymentWarning, paymentWarningDate, mconsumption, day, month, year, credit, creditEligible, discount, photoext, cardid, nationality FROM users WHERE user_id = '{$user_id}'";
	
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
		$discount = $row2['discount'];
		$photoext = $row2['photoext'];
		$cardid = $row2['cardid'];
		$nationality = $row2['nationality'];
		
		
		if ($discount != 0) {
			$discount = (100 - $discount) / 100;
		} else {
			$discount = 1;
		}
		
		
		if ($_SESSION['dispensaryGift'] == 1 && $_SESSION['menuType'] == 1) {
			$giftOption = "<img src='images/free-green.png' id='free%d' style='margin-bottom: 0; margin-left: 5px;' />";
		} else if ($_SESSION['dispensaryGift'] == 1 && $_SESSION['menuType'] == 0) {
			$giftOption = "<img src='images/free.png' id='free%d' style='margin-bottom: 0; margin-left: 5px;' />";
		} else {
			$giftOption = "<input type='hidden' value='%d' />";
		}

		
	// Query to look up total sales and find weekly average
	$selectSales = "SELECT SUM(amount) FROM sales WHERE userid = $user_id";

	$result = mysql_query($selectSales)
		or handleError($lang['error-loadsales'],"Error loading sale from db: " . mysql_error());
		
	$row = mysql_fetch_array($result);
		$totalAmount = $row['SUM(amount)'];
		$totalAmountPerDay = $totalAmount / $daysMember;
		$totalAmountPerWeek = $totalAmountPerDay * 7;
		
// Allow negative credit
if ($creditEligible == 1) {
	$validationScript = <<<EOD
	
    $(document).ready(function() {
	    
jQuery.validator.addMethod("greater_than_number", function(value, element, options) {
  var val = $(options[1], element.form).filter(function() {

    return $(this).val();

  }).val();
  
  console.log(val);
  var valid = parseFloat(val) > parseFloat(options[0]) ;
  console.log(valid);
  return valid;
}, jQuery.validator.format('No product has been selected! {0}'));
	    	    

	  $('#registerForm').validate({
		  rules: {
			  cardid: {
				  required: true,
				  range: ["{$cardid}","{$cardid}"]
			  },
			  eurcalcTOT: {
				  range: [0,"{$_SESSION['dispenseLimit']}"]
			  }
    	}, // end rules
    	errorPlacement: function(error, element) { },
    	  submitHandler: function() {
   $(".oneClick").attr("disabled", true);
   form.submit();
	    	  }
	  }); // end validate
EOD;

} else {
	$validationScript = <<<EOD
	
    $(document).ready(function() {
	    	    
jQuery.validator.addMethod("greater_than_number", function(value, element, options) {
  var val = $(options[1], element.form).filter(function() {

    return $(this).val();

  }).val();
  
  console.log(val);
  var valid = parseFloat(val) > parseFloat(options[0]) ;
  console.log(valid);
  return valid;
}, jQuery.validator.format('No product has been selected!'));


	  $('#registerForm').validate({
		  rules: {
			  newcredit: {
				  range: [0,100000]
			  },
			  cardid: {
				  required: true,
				  range: ["{$cardid}","{$cardid}"]
			  },
			  eurcalcTOT: {
				  range: [0,"{$_SESSION['dispenseLimit']}"]
			  }


    	}, // end rules
    	errorPlacement: function(error, element) { },
    	  submitHandler: function() {
   $(".oneClick").attr("disabled", true);
   form.submit();
	    	  }
	  }); // end validate
EOD;
}
	  
	$validationScript .= <<<EOD

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

EOD;

if ($_SESSION['dispDonate'] == 1) {
	
	$validationScript .= <<<EOD
	if ($credit < 0) {
	   function computeTot() {
	          var a = $('#realCredit').val();
	          var b = $('#eurcalcTOT').val();
          	  var c = $('#newDonation').val();
	          var total = ((a*1) - (b*1) + (c*1));
	          var roundedtotal = total.toFixed(2);
	          $('#realNewCredit').val(roundedtotal);
	   }
	} else {
   function computeTot() {
          var a = $('#realCredit').val();
          var b = $('#eurcalcTOT').val();
          var c = $('#newDonation').val();
	      var total = ((a*1) - (b*1) + (c*1));
          var roundedtotal = total.toFixed(2);
          $('#newcredit').val(roundedtotal);
	      $('#realNewCredit').val(roundedtotal);
        }
    }
EOD;

} else {
	
	$validationScript .= <<<EOD
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
EOD;
}

	$validationScript .= <<<EOD

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
	
	/*
	
		Transform 20 to 0,8
		x = 100 - 20
		x / 100
	
	
	*/


	
$(document).on('click keypress keyup blur', function () {
    getItems();
    computeTot();
});

	getItems();
    computeTot();
  }); // end ready
  

EOD;

	pageStart($lang['title-dispense'], NULL, $validationScript, "pdispense", "newsale", $lang['global-dispensecaps'], $_SESSION['successMessage'], $_SESSION['errorMessage']);
	
	// First lookup userdetails, incl. medicinal or not, then decide what to do (which quqeries to execute)
	$userDetails = "SELECT usageType FROM users WHERE user_id = {$user_id}";
	$result = mysql_query($userDetails)
		or handleError($lang['error-usersload'],"Error loading users from db: " . mysql_error());
		
    $row = mysql_fetch_array($result);
	$usageType = $row['usageType'];
	
?>
<form id="registerForm" action="" method="POST">


<div id="memberbox">
<img src="images/minimize.png" class="closeImg pointer" id="minimizeMemberBox" />

<?php
echo "<a href='new-picture.php?user_id=$user_id'><img src='images/members/$user_id.$photoext' class='userPicture' /></a><span class='memberinfo'><a href='#' id='chipClick'><img src='images/rfid.png' /></a>&nbsp;<a href='profile.php?user_id=$user_id'><span class='membername'>$memberno $first_name $last_name</span></a>";

	// Consumption this calendar month
	$selectSales = "SELECT SUM(quantity), SUM(amount) FROM sales WHERE userid = $user_id AND MONTH(saletime) = MONTH(NOW()) AND YEAR(saletime) = YEAR(NOW())";

	$result = mysql_query($selectSales)
		or handleError($lang['error-loadsales'],"Error loading sale from db: " . mysql_error());
		
	$row = mysql_fetch_array($result);
		$amountMonth = $row['SUM(amount)'];
		$quantityMonth = $row['SUM(quantity)'];
		
		if ($quantityMonth == '') {
			$quantityMonth = 0;
		}
		
	// Determine consumption status vs limit
	$consumptionDelta = $quantityMonth - $mconsumption;
	$consumptionDeltaPlus = 0 - $consumptionDelta;
	
	// Consumption today
	$selectSales = "SELECT SUM(quantity) FROM sales WHERE userid = $user_id AND DATE(saletime) = DATE(NOW())";

	$result2 = mysql_query($selectSales)
		or handleError($lang['error-loadsales'],"Error loading sale from db: " . mysql_error());
		
	$row = mysql_fetch_array($result2);
		$quantityToday = $row['SUM(quantity)'];
	
	echo "<br /><img src='images/flag.png' style='margin-bottom: -1px; margin-right: 1px;' /><strong>&nbsp;$nationality</strong>";
	
	echo "<br /><img src='images/consumed.png' style='margin-bottom: -1px' /><strong> {$expr(number_format($quantityMonth,1))} / {$expr(number_format($mconsumption,0))} g. <span class='yellow'>{$lang['dispensary-today']}: {$expr(number_format($quantityToday,1))} g. </span></strong>";


	if ($userGroup == 5 && $_SESSION['membershipFees'] == 1) {  // show Member w/ expiry
	
		$memberExp = date('y-m-d', strtotime($paidUntil));
		$memberExpReadable = date('d M', strtotime($paidUntil));
		$timeNow = date('y-m-d');
		
		if ($memberExp == $timeNow) {
			echo "<br /><a href='pay-membership.php?user_id=$user_id'><img src='images/exclamation-20.png' class='warningIcon' /> <span class='yellow'>" . $lang['member-expirestoday'] . "</span></a>";
	  	} else if ($memberExp < $timeNow) {
		  	echo "<br /><a href='pay-membership.php?user_id=$user_id'><img src='images/exclamation-20.png' class='warningIcon' /> <span class='yellow'>" . $lang['member-expiredon'] . ": $memberExpReadable.</span></a>";
		  	
		  	if ($paymentWarning == '1') {
		  	echo "<br /><a href='pay-membership.php?user_id=$user_id'><img src='images/exclamation-22.png' class='warningIcon' /> <img src='images/exclamation-22.png' class='warningIcon' style='margin-left: -16px; margin-right: 1px;' /> <span class='yellow'>" . $lang['member-receivedwarning'] . "</span></a>";
		  	}
		  	
		}
	}
		if ($usageType == 'Medicinal') {
		echo "<br /><img src='images/medical-20.png' style='margin-bottom: -4px; margin-right: 2px;' /> <span class='yellow'>" . $lang['medicinal-user'] . "</span>";
	}
	
	if (date('m-d') == date('m-d', strtotime($year . "-" . $month . "-" . $day . " 00:00:00"))) {
		echo "<br /><img src='images/cake-22.png' class='warningIcon' style='margin-bottom: -4px;' /> <span class='yellow'>" . $lang['global-birthday'] . "</span>";
	}

	
	// Is the user a high roller?
	$selectHighRollerLimit = "SELECT highRollerWeekly FROM systemsettings";

	$result2 = mysql_query($selectHighRollerLimit)
		or handleError($lang['error-loadsales'],"Error loading sale from db: " . mysql_error());
		
	$row = mysql_fetch_array($result2);
		$highRollerWeekly = $row['highRollerWeekly'];

	if ($totalAmountPerWeek >= $highRollerWeekly) {
		echo "<br /><img src='images/hi-roller.png' class='warningIcon' style='margin-bottom: -4px;' /> <span class='yellow'>High roller</span>";
	}
	
	// Query to look up user debt
	$selectSales = "SELECT SUM(amount), SUM(amountpaid) FROM sales WHERE userid = $user_id";

	$result3 = mysql_query($selectSales)
		or handleError($lang['error-loadsales'],"Error loading sale from db: " . mysql_error());

	$row3 = mysql_fetch_array($result3);
		$amtTot = $row3['SUM(amount)'];
		$amtPaid = $row3['SUM(amountpaid)'];
		$amtOwed = $amtTot - $amtPaid;

	 if ($amtOwed > 0.1 && $userGroup > 2) {
		 echo "<br /><a href='settle-debt-2.php?user_id=$user_id'><img src='images/exclamation-22.png' class='warningIcon' style='margin-bottom: -4px; margin-left: 7px; margin-right: 2px;' /> <span class='yellow'>Has debt</span></a>";
	 }

	
	
	if ($quantityMonth >= $mconsumption) {
		echo "<br /><img src='images/exclamation-22.png' class='warningIcon' style='margin-bottom: -4px; margin-left: 7px; margin-right: 2px;' /> <span class='yellow'>" . $lang['member-conslimitexc'] . "(+$consumptionDelta g)</span>";
	} else if ($consumptionDeltaPlus < ($mconsumption * 0.1)) {
		echo "<br /><img src='images/exclamation-22.png' class='warningIcon'  /> <span class='yellow'>" . $lang['member-conslimitnear'] . "($consumptionDeltaPlus g " . $lang['global-remaining'] . ")</span>";
	}
	
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
  	   
<?php if ($_SESSION['creditOrDirect'] == 1) { ?>
  	   <table id="memberAmount">
  	    <tr>
  	     <td><input type='number' lang='nb' class='specialInput first notZero' id='grcalcTOT' name='gramsTOT' value="0" readonly /> </td>
  	     <td style='text-align: left;'><span style="font-size: 20px;">&nbsp;gr.</span></td>
  	     <td></td>
  	     <td></td>
  	    </tr>
  	    <tr>
  	     <td><input type='number' lang='nb' class='specialInput first notZero' id='unitcalcTOT' name='unitsTOT' value="0" readonly /> </td>
  	     <td style='text-align: left;'><span style="font-size: 20px;">&nbsp;u.</span></td>
  	     <td style='text-align: right;'><a href='donation-management.php?userid=<?php echo $user_id; ?>'><?php echo $lang['global-credit']; ?>:</td>
  	     <td style='text-align: right;'><?php echo $lang['dispense-newcredit']; ?>:</td>
  	    </tr>
  	    <tr>
  	     <td><input type='number' lang='nb' class='specialInput' id='eurcalcTOT' name='eurcalcTOT' value="0" readonly /> </td>
  	     <td style='text-align: left;'><span style="font-size: 20px;">&nbsp;&euro;</span></td>
  	     <?php if ($credit < 0) { ?>
  	     <td><input type='number' lang='nb' class="specialInput" style='color: red;' id='credit' name='credit' value='0' readonly /> &euro;</td>
  	     <td><input type='number' lang='nb' class='specialInput' style='color: red;' id='newcredit' name='newcredit' value='0' readonly /> &euro;</td>
  	     
  	     <?php } else { ?>
  	     <td><input type='number' lang='nb' class="specialInput" id='credit' name='credit' value='<?php echo number_format($credit,2); ?>' readonly /> &euro;</td>
  	     <td><input type='number' lang='nb' class='specialInput' id='newcredit' name='newcredit' value='<?php echo number_format($credit,2); ?>' readonly /> &euro;</td>
  	     <?php } ?>
<?php } else { ?>
  	   <table id="memberAmount">
  	    <tr>
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
  	    <tr class='donateField' style='display: none;'>
  	     <td></td>
  	     <td></td>
  	     <td class='right'><?php echo $lang['donate']; ?>: <input type='number' lang='nb' class='twoDigit donateOrNot' id='newDonation' name='newDonation' /> &euro;</td>
  	     <td></td>
<?php } ?>


  	    </tr>
  	    
<?php if ($_SESSION['dispDonate'] == 1) { ?>

  	    <tr class='donateField' style='display: none;'>
  	     <td></td>
  	     <td></td>
  	     <td class='right'><?php echo $lang['donate']; ?>: <input type='number' lang='nb' class='twoDigit donateOrNot' id='newDonation' name='newDonation' /> &euro;</td>
  	     <td></td>
  	    </tr>
<?php } ?>
  	   </table>
  	   
  	   <center><span style='font-size: 17px; display: inline-block; margin-top: 10px;'>DISCOUNT <span id='totDiscount'>(a%)</span>:</span><br />
        <input type="checkbox" id="fee1" name="inDiscount" value='10' />
        <label for="fee1"><span class='full'>10%</span></label>
        
        <input type="checkbox" id="fee2" name="inDiscount" value='20' />
        <label for="fee2"><span class='full'>20%</span></label>

        <input type="checkbox" id="fee3" name="inDiscount" value='30' />
        <label for="fee3"><span class='full'>30%</span></label>
  	   </center>
       <center><input type="text" class="donateOrNot" id="cardscan" name="cardid" maxlength="10" placeholder="<?php echo $lang['global-scantoconfirm']; ?>" /><br />
   <a href="#" id="openDispenseDate" style="vertical-align: bottom;" class="yellow">+ Fecha manual</a><br />
   <span id='customDispenseDate' style='display: none;'>
    <input type="number" lang="nb" name="day" class="twoDigit" maxlength="2" placeholder="dd" />
    <input type="number" lang="nb" name="month" class="twoDigit" maxlength="2" placeholder="mm" />
    <input type="number" lang="nb" name="year" class="fourDigit" maxlength="4" placeholder="<?php echo $lang['member-yyyy']; ?>" /><br />
   </span>

       <a href="#" id="openComment" style="vertical-align: bottom;">+ <?php echo $lang['global-comment']; ?></a>
       <textarea class="hiddenComment" name="adminComment" placeholder="<?php echo $lang['global-comment']; ?>?"></textarea>
  	   
 <button type="submit" class='oneClick' name='oneClick'><?php echo $lang['global-save']; ?></button><br />
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
		
	$selectFlower = "SELECT g.flowerid, g.name, g.breed2, g.flowertype, g.sativaPercentage, g.description, g.medicaldescription, p.productid, p.purchaseid, p.salesPrice, p.realQuantity, p.growType, p.photoExt FROM flower g, purchases p WHERE p.category = 1 AND p.productid = g.flowerid AND p.closedAt IS NULL AND p.inMenu = 1 ORDER BY p.salesPrice ASC;";
	$resultFlower = mysql_query($selectFlower)
		or handleError($lang['error-prodprices'],"Error loading flower prices from db: " . mysql_error());

  	$selectExtract = "SELECT h.extractid, h.name, h.extract, h.description, h.medicaldescription, p.productid, p.purchaseid, p.salesPrice, p.realQuantity, p.photoExt FROM extract h, purchases p WHERE p.category = 2 AND p.productid = h.extractid AND p.closedAt IS NULL AND p.inMenu = 1 ORDER BY p.salesPrice ASC;";
	$resultExtract = mysql_query($selectExtract)
		or handleError($lang['error-prodprices'],"Error loading extract prices from db: " . mysql_error());
		

		// Retrieve Menu type and discount for medical users
		$medicalDiscount = $_SESSION['medicalDiscount'];
		$medicalDiscountPercentage = $_SESSION['medicalDiscountPercentage'];
		
		if ($medicalDiscountPercentage == 1) {
			$medicalDiscountCalc = 1 - ($medicalDiscount / 100);
		}

		
		$menuType = $_SESSION['menuType'];
		
		
		$i = 0;
		
		if ($menuType == 1) {
			echo "<div class='leftfloat'>";
		}

		echo "<h3 class='title'>FLOWERS</h3>";
		
		if ($menuType == 1) {
			echo "<table class='default nonhover'>";
		}

		
while ($flower = mysql_fetch_array($resultFlower)) {

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
	
	$result = mysql_query($growDetails)
		or handleError($lang['error-growtypeload'],"Error loading growtype: " . mysql_error());
		
	$row = mysql_fetch_array($result);
		$growtype = $row['growtype'];

	if ($usageType == 'Medicinal') {
		
		if ($medicalDiscountPercentage == 1) {
			$price = ($flower['salesPrice'] * $discount) * $medicalDiscountCalc;
		} else {
			$price = ($flower['salesPrice'] * $discount) - $medicalDiscount;
		}
		
	} else {
		
		$price = $flower['salesPrice'] * $discount;
		
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
	
	if ($flower['description'] != '') {
		
		$commentRead = "
		                <img src='images/info2.png' style='inline-block; margin-top: 5px; margin-left: 5px;' id='comment$i' /><div id='helpBox$i' class='helpBox'><strong>{$lang['extracts-description']}:</strong><br />{$flower['description']}</div>
		                <script>
		                  	$('#comment$i').on({
						 		'mouseover' : function() {
								 	$('#helpBox$i').css('display', 'block');
						  		},
						  		'mouseout' : function() {
								 	$('#helpBox$i').css('display', 'none');
							  	}
						  	});
						</script>
		                ";
		
	} else {
		
		$commentRead = "<img src='images/info2.png' style='visibility: hidden;' />";
		
	}		
	if ($flower['medicaldescription'] != '') {
		
		$commentReadM = "
		                <img src='images/med.png' style='inline-block; margin-top: 5px; margin-left: 5px;' id='commentM$i' /><div id='helpBoxM$i' class='helpBox'><strong>{$lang['extracts-medicaldesc']}:</strong><br />{$flower['medicaldescription']}</div>
		                <script>
		                  	$('#commentM$i').on({
						 		'mouseover' : function() {
								 	$('#helpBoxM$i').css('display', 'block');
						  		},
						  		'mouseout' : function() {
								 	$('#helpBoxM$i').css('display', 'none');
							  	}
						  	});
						</script>
		                ";
		
	} else {
		
		$commentReadM = "<img src='images/med.png' style='visibility: hidden;' />";
		
	}		
	  
	  
	if ($menuType == 0) {

	$flower_row =	sprintf("
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
	<div class='displaybox'>
	 <div id='imageholder'>
	  <center><img src='images/purchases/%s' /></center>
	 </div>
	 <h3>%s</h3>
	 <span class='descprice relative'>
	  <span class='firstline'>%s<br /><span class='yellow' style='font-size: 16px; font-weight: 600; margin-top: 3px; display: inline-block;'>%s</span></span><span class='yellow' style='display: none;'>%s</span><br />$commentRead $commentReadM
	 </span>
	 <input class='specialInput' id='ppgcalc%d' name='sales[%d][ppg]' value='%0.02f' readonly /><span class='eurosign'>&euro;</span>
	 <br /><div class='clearFloat'><!--<center><span style='padding: 2px; border-radius: 2px; margin-top: 3px; display: inline-block;'><img src='images/leafsmall.png' style='width: 15px; height: 14px' /><img src='images/leafsmall.png' style='width: 15px; height: 14px' /><img src='images/leafsmall.png' style='width: 15px; height: 14px' /><img src='images/leafsmall.png' style='width: 15px; height: 14px' /><img src='images/leafsmall.png' style='width: 15px; height: 14px' /></span></center>--></div>
	 <div class='clearfloat'></div><br />
	 <center>
  	  <input type='number' lang='nb' class='fourDigit centered calc' id='grcalc%d' name='sales[%d][grams]' placeholder='Gr.' step='0.01' value='%s' />
  	  <input type='number' lang='nb' class='fourDigit centered calc2' id='eurcalc%d' name='sales[%d][euro]' placeholder='&euro;' step='0.01' value='%s' />
  	  <input type='number' lang='nb' class='fourDigit centered calc' id='grcalcB%d' name='sales[%d][grams2]' placeholder='Gr.' step='0.01' value='%s' style='background-color: yellow; display: none;'  /> $giftOption
  	  <input type='hidden' name='sales[%d][name]' value='%s' />
  	  <input type='hidden' name='sales[%d][purchaseid]' value='%d' />
  	  <input type='hidden' name='sales[%d][category]' value='1' />
  	  <input type='hidden' name='sales[%d][productid]' value='%d' />
  	 </center>
	</div>",
	  $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $flower['purchaseid'] . '.' . $flower['photoExt'], $name, $growtype, $flower['flowertype'], $percentageDisplay, $i, $i, $price, $i, $i, $savedGrams[$i], $i, $i, $savedEuro[$i], $i, $i, $savedGrams2[$i], $i, $i, $name, $i, $flower['purchaseid'], $i, $i, $flower['flowerid']
	  );
	  
  } else {
	  
	  
	$flower_row =	sprintf("
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
	    <td class='left'>%s</td>
	    <td class='left'>%s</td>
	    <td class='centered relative' style='padding: 11px 0;'>$commentRead</td>
	    <td class='centered relative' style='padding: 11px 0;'>$commentReadM</td>
	    <td class='right'>%0.02f &euro; <input type='hidden' id='ppgcalc%d' name='sales[%d][ppg]' value='%0.01f' /></td>
	    <td><input type='number' lang='nb' class='twoDigit centered calc' style='border: #5aa242 solid 2px; width: 50px; border-radius: 2px; padding: 2px 1px;' id='grcalc%d' name='sales[%d][grams]' placeholder='Gr.' step='0.01' value='%s' /></td>
	    <td><input type='number' lang='nb' class='twoDigit centered calc2' style='border: #5aa242 solid 2px; width: 50px; border-radius: 2px; padding: 2px 1px;' id='eurcalc%d' name='sales[%d][euro]' placeholder='&euro;' step='0.01' value='%s' /></td>
	    <td><input type='number' lang='nb' class='twoDigit centered calc' id='grcalcB%d' name='sales[%d][grams2]' placeholder='Gr.' step='0.01' value='%s' style='border: #5aa242 solid 2px; width: 50px; border-radius: 2px; padding: 2px 1px; background-color: yellow; $displayGift'  />$giftOption</td>
	   </tr>
  	  <input type='hidden' name='sales[%d][name]' value='%s' />
  	  <input type='hidden' name='sales[%d][purchaseid]' value='%d' />
  	  <input type='hidden' name='sales[%d][category]' value='1' />
  	  <input type='hidden' name='sales[%d][productid]' value='%d' />
",
	  $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $name, $growtype, $flower['flowertype'], $price, $i, $i, $price, $i, $i, $savedGrams[$i], $i, $i, $savedEuro[$i], $i, $i, $savedGrams2[$i], $i, $i, $name, $i, $flower['purchaseid'], $i, $i, $flower['flowerid']
	  );
	  
  }
  
  	  echo $flower_row;
  	  
}

	if ($menuType == 1) {
		echo "</table></div><div class='leftfloat'><table class='default nonhover'>";
	}
  
		echo "<h3 class='title'>EXTRACTS</h3>";
while ($extract = mysql_fetch_array($resultExtract)) {
	
	if ($usageType == 'Medicinal') {
		
		if ($medicalDiscountPercentage == 1) {
			$price = ($extract['salesPrice'] * $discount) * $medicalDiscountCalc;
		} else {
			$price = ($extract['salesPrice'] * $discount) - $medicalDiscount;
		}
		
	} else {
		
		$price = $extract['salesPrice'] * $discount;
		
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
	
	if ($extract['description'] != '') {
		
		$commentRead = "
		                <img src='images/info2.png' style='inline-block; margin-top: 5px; margin-left: 5px;' id='comment$i' /><div id='helpBox$i' class='helpBox'><strong>{$lang['extracts-description']}:</strong><br />{$extract['description']}</div>
		                <script>
		                  	$('#comment$i').on({
						 		'mouseover' : function() {
								 	$('#helpBox$i').css('display', 'block');
						  		},
						  		'mouseout' : function() {
								 	$('#helpBox$i').css('display', 'none');
							  	}
						  	});
						</script>
		                ";
		
	} else {
		
		$commentRead = "";
		
	}		
	if ($extract['medicaldescription'] != '') {
		
		$commentReadM = "
		                <img src='images/med.png' style='inline-block; margin-top: 5px; margin-left: 5px;' id='commentM$i' /><div id='helpBoxM$i' class='helpBox'><strong>{$lang['extracts-medicaldesc']}:</strong><br />{$extract['medicaldescription']}</div>
		                <script>
		                  	$('#commentM$i').on({
						 		'mouseover' : function() {
								 	$('#helpBoxM$i').css('display', 'block');
						  		},
						  		'mouseout' : function() {
								 	$('#helpBoxM$i').css('display', 'none');
							  	}
						  	});
						</script>
		                ";
		
	} else {
		
		$commentReadM = "";
		
	}		


	if ($menuType == 0) {


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
	<div class='displaybox'>
	 <div id='imageholder'>
	  <center><img src='images/purchases/%s' /></center>
	 </div>
	 <h3>%s</h3>
	 <span class='descprice relative'>
	  <span class='firstline yellow' style='font-size: 16px;' ><strong>%s</strong></span><br />$commentRead $commentReadM
	 </span>
	 <input class='specialInput' id='ppgcalc%d' name='sales[%d][ppg]' value='%0.02f' readonly /><span class='eurosign'>&euro;</span>
	 <br /><!--<center><span style='padding: 2px; border-radius: 2px; margin-top: 3px; display: inline-block;'><img src='images/leafsmall.png' style='width: 15px; height: 14px' /><img src='images/leafsmall.png' style='width: 15px; height: 14px' /><img src='images/leafsmall.png' style='width: 15px; height: 14px' /><img src='images/leafsmall.png' style='width: 15px; height: 14px' /><img src='images/leafsmall.png' style='width: 15px; height: 14px' /></span></center>-->
	 <div class='clearfloat'></div><br />
	 <center>
  	  <input type='number' lang='nb' class='fourDigit centered calc' id='grcalc%d' name='sales[%d][grams]' placeholder='Gr.' step='0.01' value='%s' />
  	  <input type='number' lang='nb' class='fourDigit centered calc2' id='eurcalc%d' name='sales[%d][euro]' placeholder='&euro;' step='0.01' value='%s' />
  	  <input type='number' lang='nb' class='fourDigit centered calc' id='grcalcB%d' name='sales[%d][grams2]' placeholder='Gr.' step='0.01' value='%s' style='background-color: yellow; $displayGift'  /> $giftOption
  	  <input type='hidden' name='sales[%d][name]' value='%s' />
  	  <input type='hidden' name='sales[%d][purchaseid]' value='%d' />
  	  <input type='hidden' name='sales[%d][category]' value='2' />
  	  <input type='hidden' name='sales[%d][productid]' value='%d' />
  	 </center>
	</div>",
	  $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $extract['purchaseid'] . '.' . $extract['photoExt'], $extract['name'], $extract['extract'], $i, $i, $price, $i, $i, $savedGrams[$i], $i, $i, $savedEuro[$i], $i, $i, $savedGrams2[$i], $i, $i, $name, $i, $extract['purchaseid'], $i, $i, $extract['extractid']
	  );
	  
  } else {
	  
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
	    <td class='left'>%s</td>
	    <td class='centered relative' style='padding: 11px 0;'>$commentRead</td>
	    <td class='centered relative' style='padding: 11px 0;'>$commentReadM</td>
	    <td class='right'>%0.02f &euro; <input type='hidden' id='ppgcalc%d' name='sales[%d][ppg]' value='%0.01f' /></td>
	    <td><input type='number' lang='nb' class='twoDigit centered calc' style='border: #5aa242 solid 2px; width: 50px; border-radius: 2px; padding: 2px 1px;' id='grcalc%d' name='sales[%d][grams]' placeholder='Gr.' step='0.01' value='%s' /></td>
	    <td><input type='number' lang='nb' class='twoDigit centered calc2' style='border: #5aa242 solid 2px; width: 50px; border-radius: 2px; padding: 2px 1px;' id='eurcalc%d' name='sales[%d][euro]' placeholder='&euro;' step='0.01' value='%s' /></td>
	    <td><input type='number' lang='nb' class='twoDigit centered calc' id='grcalcB%d' name='sales[%d][grams2]' placeholder='Gr.' step='0.01' value='%s' style='border: #5aa242 solid 2px; width: 50px; border-radius: 2px; padding: 2px 1px; background-color: yellow; $displayGift'  />$giftOption</td>
	   </tr>
  	  <input type='hidden' name='sales[%d][name]' value='%s' />
  	  <input type='hidden' name='sales[%d][purchaseid]' value='%d' />
  	  <input type='hidden' name='sales[%d][category]' value='2' />
  	  <input type='hidden' name='sales[%d][productid]' value='%d' />
",
	  $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $extract['name'], $extract['extract'], $price, $i, $i, $price, $i, $i, $savedGrams[$i], $i, $i, $savedEuro[$i], $i, $i, $savedGrams2[$i], $i, $i, $name, $i, $extract['purchaseid'], $i, $i, $extract['extractid']
	  );
	  
   }
	  echo $extract_row;
	  
  }
  
  
  	// Query to look up categories
	$selectCats = "SELECT id, name from categories ORDER by id ASC";

	$resultCats = mysql_query($selectCats)
		or handleError($lang['error-loadflowers'],"Error loading flower from db: " . mysql_error());

	while ($category = mysql_fetch_array($resultCats)) {
		
		$categoryid = $category['id'];
		$name = $category['name'];
  
		if ($menuType == 1) {
			echo "</table></div><br /><div class='leftfloat'><table class='default nonhover'>";
		}
	
 		echo "<h3 class='title'>$name</h3>";
 		
	 // For each cat, look up products
  	$selectProduct = "SELECT pr.productid, pr.name, pr.description, pr.medicaldescription, p.purchaseid, p.salesPrice, p.realQuantity, p.photoExt FROM products pr, purchases p WHERE p.category = $categoryid AND p.productid = pr.productid AND p.closedAt IS NULL AND inMenu = 1 ORDER BY p.salesPrice ASC;";
  	
  	
	$resultProduct = mysql_query($selectProduct)
		or handleError($lang['error-prodprices'],"Error loading extract prices from db: " . mysql_error());
		
		

		while ($product = mysql_fetch_array($resultProduct)) {
			
		if ($usageType == 'Medicinal') {
			
			if ($medicalDiscountPercentage == 1) {
				$price = ($product['salesPrice'] * $discount) * $medicalDiscountCalc;
			} else {
				$price = ($product['salesPrice'] * $discount) - $medicalDiscount;
			}
			
		} else {
			
			$price = $product['salesPrice'] * $discount;
			
		}

	
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
		                <img src='images/info2.png' style='inline-block; margin-top: 5px; margin-left: 5px;' id='comment$i' /><div id='helpBox$i' class='helpBox'><strong>{$lang['extracts-description']}:</strong><br />{$product['description']}</div>
		                <script>
		                  	$('#comment$i').on({
						 		'mouseover' : function() {
								 	$('#helpBox$i').css('display', 'block');
						  		},
						  		'mouseout' : function() {
								 	$('#helpBox$i').css('display', 'none');
							  	}
						  	});
						</script>
		                ";
		
	} else {
		
		$commentRead = "";
		
	}		
	if ($product['medicaldescription'] != '') {
		
		$commentReadM = "
		                <img src='images/med.png' style='inline-block; margin-top: 5px; margin-left: 5px;' id='commentM$i' /><div id='helpBoxM$i' class='helpBox'><strong>{$lang['extracts-medicaldesc']}:</strong><br />{$product['medicaldescription']}</div>
		                <script>
		                  	$('#commentM$i').on({
						 		'mouseover' : function() {
								 	$('#helpBoxM$i').css('display', 'block');
						  		},
						  		'mouseout' : function() {
								 	$('#helpBoxM$i').css('display', 'none');
							  	}
						  	});
						</script>
		                ";
		
	} else {
		
		$commentReadM = "";
		
	}	
				
	if ($menuType == 0) {
	 		
	
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
	<div class='displaybox' style='text-align: center;'>
	 <div id='imageholder'>
	  <center><img src='images/purchases/%s' /></center>
	 </div>
	 <h3>%s</h3>
	 <span class='descprice relative'>
	  <br />$commentRead $commentReadM
	 </span>
	 <input class='specialInput' id='ppgcalc%d' name='sales[%d][ppg]' value='%0.02f' readonly /><span class='eurosign'>&euro;</span>
	 <br />
	 <div class='clearfloat'></div><br />
	 <center>
  	  <input type='number' lang='nb' class='fourDigit centered calc3' id='grcalc%d' name='sales[%d][grams]' placeholder='#' step='0.01' value='%s' />
  	  <input type='number' lang='nb' class='fourDigit centered calc2' id='eurcalc%d' name='sales[%d][euro]' placeholder='&euro;' step='0.01' value='%s' style='display: none;' />
  	  <input type='number' lang='nb' class='fourDigit centered calc4' id='grcalcB%d' name='sales[%d][grams2]' placeholder='#' step='0.01' value='%s' style='background-color: yellow; $displayGift'  /> $giftOption
  	  <input type='hidden' name='sales[%d][name]' value='%s' />
  	  <input type='hidden' name='sales[%d][purchaseid]' value='%d' />
  	  <input type='hidden' name='sales[%d][category]' value='%d' />
  	  <input type='hidden' name='sales[%d][productid]' value='%d' />
  	 </center>
	</div>",
	  $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $product['purchaseid'] . '.' . $product['photoExt'], $product['name'], $i, $i, $price, $i, $i, $savedGrams[$i], $i, $i, $savedEuro[$i], $i, $i, $savedGrams2[$i], $i, $i, $name, $i, $product['purchaseid'], $i, $categoryid, $i, $product['productid']
	  );
	  
  } else {
	  
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
        
	   <tr>
	    <td class='left'>%s</td>
	    <td class='centered relative' style='padding: 11px 0;'>$commentRead</td>
	    <td class='centered relative' style='padding: 11px 0;'>$commentReadM</td>
	    <td class='right'>%0.02f &euro; <input type='hidden' id='ppgcalc%d' name='sales[%d][ppg]' value='%0.01f' /></td>
	    <td><input type='number' lang='nb' class='twoDigit centered calc3' style='border: #5aa242 solid 2px; width: 50px; border-radius: 2px; padding: 2px 1px;' id='grcalc%d' name='sales[%d][grams]' placeholder='#' step='0.01' value='%s' /></td>
	    <td><input type='number' lang='nb' class='twoDigit centered calc2' style='border: #5aa242 solid 2px; width: 50px; border-radius: 2px; padding: 2px 1px; display: none;' id='eurcalc%d' name='sales[%d][euro]' placeholder='&euro;' step='0.01' value='%s' /></td>
	    <td><input type='number' lang='nb' class='twoDigit centered calc4' id='grcalcB%d' name='sales[%d][grams2]' placeholder='#' step='0.01' value='%s' style='border: #5aa242 solid 2px; width: 50px; border-radius: 2px; padding: 2px 1px; background-color: yellow; $displayGift'  />$giftOption</td>
	   </tr>
  	  <input type='hidden' name='sales[%d][name]' value='%s' />
  	  <input type='hidden' name='sales[%d][purchaseid]' value='%d' />
  	  <input type='hidden' name='sales[%d][category]' value='%d' />
  	  <input type='hidden' name='sales[%d][productid]' value='%d' />
",
	  $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $product['name'], $price, $i, $i, $price, $i, $i, $savedGrams[$i], $i, $i, $savedEuro[$i], $i, $i, $savedGrams2[$i], $i, $i, $name, $i, $product['purchaseid'], $i, $categoryid, $i, $product['productid']
	  );
	  
  }
	  echo $product_row;
	  }
		
 		
	}

echo "</center>";

?>
	  <div class="clearFloat"></div>

	 </tbody>
	 </table>
	
	
</form>

<?php

} else {
	
	// Did this page re-submit with a form? If so, check & store details
	if (isset($_POST['dispense'])) {
		
  	    $user_id = $_POST['user_id'];
		$euroTOT = $_POST['euroTOT'];
		$eurcalcTOT = $_POST['eurcalcTOT'];
		$gramsTOT = $_POST['gramsTOT'];
		$unitsTOT = $_POST['unitsTOT'];
		$paidTOT = $_POST['paidTOT'];
		$gramsTOT = $_POST['gramsTOT'];
		$realQuantity = $_POST['realgramsTOT'];
		$adminComment = $_POST['adminComment'];
		$newcredit = $_POST['newcredit'];
		$credit = $_POST['credit'];
		$realCredit = $_POST['realCredit'];
		$realNewCredit = $_POST['realNewCredit'];		
  	    $day = $_POST['day'];
		$month = $_POST['month'];
		$year = $_POST['year'];
		$paidUntil = $_POST['paidUntil'];	

		if ($day > 0 && $month > 0 && $year > 0) {
			$date_timestamp = strtotime($month . "/" . $day . "/" . $year);
			$saletime = date("Y-m-d H:i:s", $date_timestamp);
		} else {
			$saletime = date('Y-m-d H:i:s');
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
			
			/* 
			
				Credit: 	 8,50
				Purchase: 	38,50
				Newcredit: -30,00
				
			
			*/
			
			if ($newCreditCalc < 0 && $creditEligible == 0) {
			
				$_SESSION['errorMessage'] = 'Saldo no suficiente para dispensar!';
				
				pageStart($lang['title-dispense'], NULL, $validationScript, "pdispense", "newsale", $lang['global-dispensecaps'], $_SESSION['successMessage'], $_SESSION['errorMessage']);
				
				exit();
	
				
			} else if ($creditEligible == 1 && $newCreditCalc < (0 - $maxCredit)) {
			
				$_SESSION['errorMessage'] = "No puedes dispensar: Limite de credito excedido!<br />Limite de credito: $maxCredit";
				
				pageStart($lang['title-dispense'], NULL, $validationScript, "pdispense", "newsale", $lang['global-dispensecaps'], $_SESSION['successMessage'], $_SESSION['errorMessage']);
				
				exit();
				
			}
		
		// Add scan to scan history
		  $cardid = $_POST['cardid'];
		  $query = sprintf("INSERT INTO scanhistory (userid, scanTime, cardid, scanType) VALUES ('%d', '%s', '%s', '%d');",
		  $user_id, $saletime, $cardid, '4');
		  
		mysql_query($query)
			or handleError($lang['error-savescan'],"Error inserting scan data: " . mysql_error());

			// Query to add new sale to Sales table - 6 arguments
		  	$query = sprintf("INSERT INTO sales (saletime, userid, amount, amountPaid, quantity, realQuantity, units, adminComment, settled, settledTo, creditBefore, creditAfter, expiry) VALUES ('%s', '%d', '%f', '%f', '%f', '%f', '%f', '%s', '%s', '%d', '%f', '%f', '%s');",
		  	$saletime, $user_id, $eurcalcTOT, $eurcalcTOT, $gramsTOT, $realQuantity, $unitsTOT, $adminComment, $saletime, '1', $creditLookup, $newCreditCalc, $paidUntil);
		  	

		if ($_SESSION['creditOrDirect'] == 1) {
		// Query to update user credit
		$updateUser = sprintf("UPDATE users SET credit = '%f' WHERE user_id = '%d';",
			mysql_real_escape_string($newCreditCalc),
			mysql_real_escape_string($user_id)
			);
				
		mysql_query($updateUser)
			or handleError($lang['error-savingcredit'],"Error updating user profile credit: " . mysql_error());
			
		}
		
		mysql_query($query)
			or handleError($lang['error-savingdispense'],"Error inserting sale: " . mysql_error());
			
		$saleid = mysql_insert_id();

			// Query to add new sale to Sales table - 6 arguments
		  	$query = sprintf("INSERT INTO f_sales (saletime, userid, amount, amountPaid, quantity, realQuantity, units, adminComment, settled, settledTo, creditBefore, creditAfter, expiry) VALUES ('%s', '%d', '%f', '%f', '%f', '%f', '%f', '%s', '%s', '%d', '%f', '%f', '%s');",
		  	$saletime, $user_id, $eurcalcTOT, $eurcalcTOT, $gramsTOT, $realQuantity, $unitsTOT, $adminComment, $saletime, '1', $creditLookup, $newCreditCalc, $paidUntil);
		  	
		mysql_query($query)
			or handleError($lang['error-savingdispense'],"Error inserting sale: " . mysql_error());
		
	foreach($_POST['sales'] as $sale) {
		$name = $sale['name'];
		$category = $sale['category'];
		$productid = $sale['productid'];
		$purchaseid = $sale['purchaseid'];
		$grams = $sale['grams'];
		$grams2 = $sale['grams2'];
		$realGrams = $sale['realGrams'];
		if ($realGrams == '' || $realGrams == 0) {
			$realGrams = $grams;
		}
		$euro = $sale['euro'];
		$gramsTot = $grams + $grams2;
		
		if ($gramsTot > 0) {
			
			if ($grams > 0 && $grams2 > 0) {
				
	    		// Query to add new sale to salesdetails table - ? arguments
			  	$query = sprintf("INSERT INTO salesdetails (saleid, category, productid, quantity, realQuantity, amount, purchaseid) VALUES ('%d', '%d', '%d', '%f', '%f', '%f', '%d');",
			  	$saleid, $category, $productid, $grams, $realGrams, $euro, $purchaseid);
			  
				mysql_query($query)
					or handleError($lang['error-savedata'],"Error inserting sale details: " . mysql_error());
				
	    		// Query to add new sale to salesdetails table - ? arguments
			  	$query = sprintf("INSERT INTO salesdetails (saleid, category, productid, quantity, amount, purchaseid, realQuantity) VALUES ('%d', '%d', '%d', '%f', '%f', '%d', '%f');",
			  	$saleid, $category, $productid, $grams2, '0', $purchaseid, $grams2);
			  
				mysql_query($query)
					or handleError($lang['error-savedata'],"Error inserting sale details: " . mysql_error());

	    		// Query to add new sale to salesdetails table - ? arguments
			  	$query = sprintf("INSERT INTO f_salesdetails (saleid, category, productid, quantity, realQuantity, amount, purchaseid) VALUES ('%d', '%d', '%d', '%f', '%f', '%f', '%d');",
			  	$saleid, $category, $productid, $grams, $realGrams, $euro, $purchaseid);
			  
				mysql_query($query)
					or handleError($lang['error-savedata'],"Error inserting sale details: " . mysql_error());
				
	    		// Query to add new sale to salesdetails table - ? arguments
			  	$query = sprintf("INSERT INTO f_salesdetails (saleid, category, productid, quantity, amount, purchaseid, realQuantity) VALUES ('%d', '%d', '%d', '%f', '%f', '%d', '%f');",
			  	$saleid, $category, $productid, $grams2, '0', $purchaseid, $grams2);
			  
				mysql_query($query)
					or handleError($lang['error-savedata'],"Error inserting sale details: " . mysql_error());

				
			} else {
			
				if ($grams  == 0) {
					
					$grams = $grams2;
					
				}
	    	
	    		// Query to add new sale to salesdetails table - ? arguments
			  	$query = sprintf("INSERT INTO salesdetails (saleid, category, productid, quantity, realQuantity, amount, purchaseid) VALUES ('%d', '%d', '%d', '%f', '%f', '%f', '%d');",
			  	$saleid, $category, $productid, $grams, $realGrams, $euro, $purchaseid);
			  
				mysql_query($query)
					or handleError($lang['error-savedata'],"Error inserting sale details: " . mysql_error());
					
	    		// Query to add new sale to salesdetails table - ? arguments
			  	$query = sprintf("INSERT INTO f_salesdetails (saleid, category, productid, quantity, realQuantity, amount, purchaseid) VALUES ('%d', '%d', '%d', '%f', '%f', '%f', '%d');",
			  	$saleid, $category, $productid, $grams, $realGrams, $euro, $purchaseid);
			  
				mysql_query($query)
					or handleError($lang['error-savedata'],"Error inserting sale details: " . mysql_error());

			}
		}

	}
	
		// Write to log
		$logTime = date('Y-m-d H:i:s');
	
		$query = sprintf("INSERT INTO log (logtype, logtime, user_id, operator, amount, oldCredit, newCredit) VALUES ('%d', '%s', '%d', '%d', '%f', '%f', '%f');",
		10, $logTime, $user_id, $_SESSION['user_id'], $eurcalcTOT, $creditLookup, $newCreditCalc);
		
		mysql_query($query)
			or handleError($lang['error-savedata'],"Error inserting purchase: " . mysql_error());

		$query = sprintf("INSERT INTO f_log (logtype, logtime, user_id, operator, amount, oldCredit, newCredit) VALUES ('%d', '%s', '%d', '%d', '%f', '%f', '%f');",
		10, $logTime, $user_id, $_SESSION['user_id'], $eurcalcTOT, $creditLookup, $newCreditCalc);
		
		mysql_query($query)
			or handleError($lang['error-savedata'],"Error inserting purchase: " . mysql_error());

		// On success: redirect.
		$_SESSION['successMessage'] = $lang['dispense-added'];
		header("Location: dispense.php?saleid=" . $saleid);
		exit();

	}
	/***** FORM SUBMIT END *****/
	
	
	// Get the card ID
	if ($_POST['cardid'] != '') {
		
		$cardid = $_POST['cardid'];
		$userDetails = "SELECT user_id FROM users WHERE cardid = '{$cardid}'";
		$userCheck = mysql_query($userDetails);
		if(mysql_num_rows($userCheck) == 0) {
	   		handleError($lang['error-keyfob'],"");
		}
	
		$result = mysql_query($userDetails)
			or handleError($lang['error-userload'],"Error loading user: " . mysql_error());
	
		$row = mysql_fetch_array($result);
		$user_id = $row['user_id'];
		
		// Add scan to scan history
		$scanTime = date('Y-m-d H:i:s'); // 	$purchaseDate = date('Y-m-d H:i:s'); ????
		
		$cardid = $_POST['cardid'];
		  $query = sprintf("INSERT INTO scanhistory (userid, scanTime, cardid, scanType) VALUES ('%d', '%s', '%s', '%d');",
		  $user_id, $scanTime, $cardid, '3');
		  
		mysql_query($query)
			or handleError($lang['error-savedata'],"Error inserting purchase: " . mysql_error());
					
	} else if (isset($_POST['userSelect'])) {
		$user_id = $_POST['userSelect'];
	} else if (isset($_GET['user_id'])) {
		$user_id = $_GET['user_id'];
	} else {
		handleError($lang['error-nomember'],"");
	}
	
	// Check if member is eligible for dispensing
	$userDetails = "SELECT userGroup, paymentWarning, paymentWarningDate, paidUntil FROM users WHERE user_id = '{$user_id}'";
	
	$result = mysql_query($userDetails)
		or handleError($lang['error-userload'],"Error loading user: " . mysql_error());
	
	$row = mysql_fetch_array($result);
		$userGroup = $row['userGroup'];
		$paymentWarning = $row['paymentWarning'];
		$paymentWarningDate = $row['paymentWarningDate'];
		$paidUntil = strtotime(date('Y-m-d H:i', strtotime($row['paidUntil'])));
		$nowTime = strtotime(date('Y-m-d H:i'));
		$pwd = strtotime(date('Y-m-d H:i',strtotime($paymentWarningDate)));
		
	if ($userGroup > 5) {
		$_SESSION['errorMessage'] = $lang['cannot-dispense'];
		header("Location: no-dispense.php?user_id=$user_id");
		exit();
	}
		
	if ($_SESSION['dispExpired'] == 0) {
		if ($paymentWarning == 1 && $nowTime > $pwd && $nowTime > $paidUntil) {
			$_SESSION['errorMessage'] = $lang['cannot-dispense'];
			header("Location: no-dispense.php?user_id=$user_id");
			exit();
		}
	}
	
/*	$cartID = 'savedCart' . $user_id;
	echo $cartID;
	echo "h: " . $_SESSION[$cartID];*/
	
	// Was a donation made, or a cart saved?
	if (isset($_SESSION['savedSale'])) {
		
		$savedSale = $_SESSION['savedSale'];
		unset($_SESSION['savedSale']);
		
	} /*else if (isset($_SESSION[$cartID])) {
		
		$savedSale = $_SESSION[$cartID];
		unset($_SESSION[$cartID]);
		
	}*/

	
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
		$userDetails2 = "SELECT memberno, paidUntil, userGroup, first_name, last_name, datediff(curdate(),$memberSince) AS daysMember, paymentWarning, paymentWarningDate, mconsumption, day, month, year, credit, creditEligible, discount, photoext, cardid FROM users WHERE user_id = '{$user_id}'";
	
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
		$discount = $row2['discount'];
		$photoext = $row2['photoext'];
		$cardid = $row2['cardid'];
		
		if ($discount != 0) {
			$discount = (100 - $discount) / 100;
		} else {
			$discount = 1;
		}
		
		
		if ($_SESSION['dispensaryGift'] == 1 && $_SESSION['menuType'] == 1) {
			$giftOption = "<img src='images/free-green.png' id='free%d' style='margin-bottom: 0; margin-left: 5px;' />";
		} else if ($_SESSION['dispensaryGift'] == 1 && $_SESSION['menuType'] == 0) {
			$giftOption = "<img src='images/free.png' id='free%d' style='margin-bottom: 0; margin-left: 5px;' />";
		} else {
			$giftOption = "<input type='hidden' value='%d' />";
		}

		
	// Query to look up total sales and find weekly average
	$selectSales = "SELECT SUM(amount) FROM sales WHERE userid = $user_id";

	$result = mysql_query($selectSales)
		or handleError($lang['error-loadsales'],"Error loading sale from db: " . mysql_error());
		
	$row = mysql_fetch_array($result);
		$totalAmount = $row['SUM(amount)'];
		$totalAmountPerDay = $totalAmount / $daysMember;
		$totalAmountPerWeek = $totalAmountPerDay * 7;
		
// Allow negative credit
if ($creditEligible == 1) {
	$validationScript = <<<EOD
	
    $(document).ready(function() {

	  $('#registerForm').validate({
		  rules: {
			  cardid: {
				  required: true
			  },
			  eurcalcTOT: {
				  range: [0,"{$_SESSION['dispenseLimit']}"]
			  }
    	}, // end rules
    	errorPlacement: function(error, element) { },
    	  submitHandler: function() {
   $(".oneClick").attr("disabled", true);
   form.submit();
	    	  }
	  }); // end validate
EOD;

} else {
	$validationScript = <<<EOD
	
    $(document).ready(function() {
	    	    
	  $('#registerForm').validate({
		  rules: {
			  newcredit: {
				  range: [0,100000]
			  },
			  cardid: {
				  required: true
			  },
			  eurcalcTOT: {
				  range: [0,"{$_SESSION['dispenseLimit']}"]
			  }


    	}, // end rules
    	errorPlacement: function(error, element) { },
    	  submitHandler: function() {
   $(".oneClick").attr("disabled", true);
   form.submit();
	    	  }
	  }); // end validate
EOD;
}
	  
	$validationScript .= <<<EOD

	$("#credit").click(function () {
		$(".donateField").css("display", "table-row");
	});
	
	$("#discountThirty").click(function () {
		var discountTotal = $('#eurcalcTOT').val();
		var discountPrice = discountTotal * 0.7;
		var finalPrice = discountPrice.toFixed(2);
  		$('#eurcalcTOT').val(finalPrice);
  		$('#eurcalcTOT2').val(finalPrice);
		
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
  $('#eurcalcTOT').val(rsumB);
  $('#eurcalcTOT2').val(rsumB);
  $('#eurcalcTOTexp').val(rsumB);

  
  
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

  var sumE = sumE.toFixed(2);
  $('#realgrcalcTOT').val(sumE);

}

EOD;

if ($_SESSION['dispDonate'] == 1) {
	
	$validationScript .= <<<EOD
	if ($credit < 0) {
	   function computeTot() {
	          var a = $('#realCredit').val();
	          var b = $('#eurcalcTOT').val();
          	  var c = $('#newDonation').val();
	          var total = ((a*1) - (b*1) + (c*1));
	          var roundedtotal = total.toFixed(2);
	          $('#realNewCredit').val(roundedtotal);
	   }
	} else {
   function computeTot() {
          var a = $('#realCredit').val();
          var b = $('#eurcalcTOT').val();
          var c = $('#newDonation').val();
	      var total = ((a*1) - (b*1) + (c*1));
          var roundedtotal = total.toFixed(2);
          $('#newcredit').val(roundedtotal);
	      $('#realNewCredit').val(roundedtotal);
        }
    }
EOD;

} else {
	
	$validationScript .= <<<EOD
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
EOD;
}

	$validationScript .= <<<EOD

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
	
	$("#hiddenSummary").click(function () {
	$("#memberbox").css("visibility", "visible");
	$("#hiddenSummary").css("display", "none");
	});	
	
$(document).on('click keypress keyup blur', function () {
    getItems();
    computeTot();
});

	getItems();
    computeTot();
  }); // end ready
  

EOD;

	pageStart($lang['title-dispense'], NULL, $validationScript, "pdispense", "newsale", $lang['global-dispensecaps'], $_SESSION['successMessage'], $_SESSION['errorMessage']);
	
	// First lookup userdetails, incl. medicinal or not, then decide what to do (which quqeries to execute)
	$userDetails = "SELECT usageType FROM users WHERE user_id = {$user_id}";
	$result = mysql_query($userDetails)
		or handleError($lang['error-usersload'],"Error loading users from db: " . mysql_error());
		
    $row = mysql_fetch_array($result);
	$usageType = $row['usageType'];
	
?>
<form id="registerForm" action="" method="POST">




<div class="clearfloat"></div>







































<?php
		
	$selectFlower = "SELECT g.flowerid, g.name, g.breed2, g.flowertype, g.sativaPercentage, g.description, g.medicaldescription, p.productid, p.purchaseid, p.salesPrice, p.realQuantity, p.growType, p.photoExt FROM flower g, purchases p WHERE p.category = 1 AND p.productid = g.flowerid AND p.closedAt IS NULL AND p.inMenu = 1 ORDER BY p.salesPrice ASC;";
	$resultFlower = mysql_query($selectFlower)
		or handleError($lang['error-prodprices'],"Error loading flower prices from db: " . mysql_error());

  	$selectExtract = "SELECT h.extractid, h.name, h.extract, h.description, h.medicaldescription, p.productid, p.purchaseid, p.salesPrice, p.realQuantity, p.photoExt FROM extract h, purchases p WHERE p.category = 2 AND p.productid = h.extractid AND p.closedAt IS NULL AND p.inMenu = 1 ORDER BY p.salesPrice ASC;";
	$resultExtract = mysql_query($selectExtract)
		or handleError($lang['error-prodprices'],"Error loading extract prices from db: " . mysql_error());
		

		// Retrieve Menu type and discount for medical users
		$medicalDiscount = $_SESSION['medicalDiscount'];
		$medicalDiscountPercentage = $_SESSION['medicalDiscountPercentage'];
		
		if ($medicalDiscountPercentage == 1) {
			$medicalDiscountCalc = 1 - ($medicalDiscount / 100);
		}

		
		$menuType = $_SESSION['menuType'];
		
		
		$i = 0;
		
		if ($menuType == 1) {
			echo "<div class='leftfloat'>";
		}

		echo "<h3 class='title'>FLOWERS</h3>";
		
		if ($menuType == 1) {
			echo "<table class='default nonhover'>";
		}

		
while ($flower = mysql_fetch_array($resultFlower)) {

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
	
	$result = mysql_query($growDetails)
		or handleError($lang['error-growtypeload'],"Error loading growtype: " . mysql_error());
		
	$row = mysql_fetch_array($result);
		$growtype = $row['growtype'];

	if (($usageType == 'Medicinal') && ($discount == 1)) {
		
		if ($medicalDiscountPercentage == 1) {
			$price = ($flower['salesPrice'] * $discount) * $medicalDiscountCalc;
		} else {
			$price = ($flower['salesPrice'] * $discount) - $medicalDiscount;
		}
		
	} else {
		
		$price = $flower['salesPrice'] * $discount;
		
	}
	
	// Look up purchase-specific discount with user id
	$selectPurchaseDiscount = "SELECT discount FROM inddiscounts WHERE user_id = $user_id AND purchaseid = {$flower['purchaseid']}";
	
	$resultPurchaseDiscount = mysql_query($selectPurchaseDiscount)
		or handleError($lang['error-prodprices'],"Error loading flower prices from db: " . mysql_error());
		
	$rowD = mysql_fetch_array($resultPurchaseDiscount);
		$prodDiscount = $rowD['discount'];
		
	if ($prodDiscount > 0) {
		$prodDiscount = (100 - $prodDiscount) / 100;
		$price = $flower['salesPrice'] * $prodDiscount;
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
	
	if ($flower['description'] != '') {
		
		$commentRead = "
		                <img src='images/info2.png' style='inline-block; margin-top: 5px; margin-left: 5px;' id='comment$i' /><div id='helpBox$i' class='helpBox'><strong>{$lang['extracts-description']}:</strong><br />{$flower['description']}</div>
		                <script>
		                  	$('#comment$i').on({
						 		'mouseover' : function() {
								 	$('#helpBox$i').css('display', 'block');
						  		},
						  		'mouseout' : function() {
								 	$('#helpBox$i').css('display', 'none');
							  	}
						  	});
						</script>
		                ";
		
	} else {
		
		$commentRead = "<img src='images/info2.png' style='visibility: hidden;' />";
		
	}		
	if ($flower['medicaldescription'] != '') {
		
		$commentReadM = "
		                <img src='images/med.png' style='inline-block; margin-top: 5px; margin-left: 5px;' id='commentM$i' /><div id='helpBoxM$i' class='helpBox'><strong>{$lang['extracts-medicaldesc']}:</strong><br />{$flower['medicaldescription']}</div>
		                <script>
		                  	$('#commentM$i').on({
						 		'mouseover' : function() {
								 	$('#helpBoxM$i').css('display', 'block');
						  		},
						  		'mouseout' : function() {
								 	$('#helpBoxM$i').css('display', 'none');
							  	}
						  	});
						</script>
		                ";
		
	} else {
		
		$commentReadM = "<img src='images/med.png' style='visibility: hidden;' />";
		
	}		
	  
	  
	if ($menuType == 0) {

	$flower_row =	sprintf("
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
	<div class='displaybox'>
	 <div id='imageholder'>
	  <center><img src='images/purchases/%s' /></center>
	 </div>
	 <h3>%s</h3>
	 <span class='descprice relative'>
	  <span class='firstline'>%s<br /><span class='yellow' style='font-size: 16px; font-weight: 600; margin-top: 3px; display: inline-block;'>%s</span></span><span class='yellow' style='display: none;'>%s</span><br />$commentRead $commentReadM
	 </span>
	 <input class='specialInput' id='ppgcalc%d' name='sales[%d][ppg]' value='%0.02f' readonly /><span class='eurosign'>&euro;</span>
	 <br /><div class='clearFloat'><!--<center><span style='padding: 2px; border-radius: 2px; margin-top: 3px; display: inline-block;'><img src='images/leafsmall.png' style='width: 15px; height: 14px' /><img src='images/leafsmall.png' style='width: 15px; height: 14px' /><img src='images/leafsmall.png' style='width: 15px; height: 14px' /><img src='images/leafsmall.png' style='width: 15px; height: 14px' /><img src='images/leafsmall.png' style='width: 15px; height: 14px' /></span></center>--></div>
	 <div class='clearfloat'></div><br />
	 <center>
  	  <input type='number' lang='nb' class='fourDigit centered calc' id='grcalc%d' name='sales[%d][grams]' placeholder='Gr.' step='0.01' value='%s' />
  	  <input type='number' lang='nb' class='fourDigit centered calc2' id='eurcalc%d' name='sales[%d][euro]' placeholder='&euro;' step='0.01' value='%s' />
  	  <input type='number' lang='nb' class='fourDigit centered calc' id='grcalcB%d' name='sales[%d][grams2]' placeholder='Gr.' step='0.01' value='%s' style='background-color: yellow; display: none;'  /> $giftOption<br /><br />
  	  <input type='number' lang='nb' class='fourDigit centered calc5' id='realgrcalc%d' name='sales[%d][realGrams]' placeholder='Real gr.' step='0.01' style='width: 70px;' />
  	  <input type='hidden' name='sales[%d][name]' value='%s' />
  	  <input type='hidden' name='sales[%d][purchaseid]' value='%d' />
  	  <input type='hidden' name='sales[%d][category]' value='1' />
  	  <input type='hidden' name='sales[%d][productid]' value='%d' />
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
		      return [0.01, parseFloat($('#grcalc%d').val()) + {$_SESSION['flowerLimit']}];
		      
		  }
		});
	

  }); // end ready
</script>
",
	  $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $flower['purchaseid'] . '.' . $flower['photoExt'], $name, $growtype, $flower['flowertype'], $percentageDisplay, $i, $i, $price, $i, $i, $savedGrams[$i], $i, $i, $savedEuro[$i], $i, $i, $savedGrams2[$i], $i, $i, $i, $i, $name, $i, $flower['purchaseid'], $i, $i, $flower['flowerid'], $i, $i, $i
	  );
	  
  } else {
	  
	  
	$flower_row =	sprintf("
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
	    <td class='left'>%s</td>
	    <td class='left'>%s</td>
	    <td class='centered relative' style='padding: 11px 0;'>$commentRead</td>
	    <td class='centered relative' style='padding: 11px 0;'>$commentReadM</td>
	    <td class='right'>%0.02f &euro; <input type='hidden' id='ppgcalc%d' name='sales[%d][ppg]' value='%0.01f' /></td>
	    <td><input type='number' lang='nb' class='twoDigit centered calc' style='border: #5aa242 solid 2px; width: 50px; border-radius: 2px; padding: 2px 1px;' id='grcalc%d' name='sales[%d][grams]' placeholder='Gr.' step='0.01' value='%s' /></td>
	    <td><input type='number' lang='nb' class='twoDigit centered calc2' style='border: #5aa242 solid 2px; width: 50px; border-radius: 2px; padding: 2px 1px;' id='eurcalc%d' name='sales[%d][euro]' placeholder='&euro;' step='0.01' value='%s' /></td>
	    <td><input type='number' lang='nb' class='twoDigit centered calc' id='grcalcB%d' name='sales[%d][grams2]' placeholder='Gr.' step='0.01' value='%s' style='border: #5aa242 solid 2px; width: 50px; border-radius: 2px; padding: 2px 1px; background-color: yellow; $displayGift'  />$giftOption</td>
	    <td><input type='number' lang='nb' class='fourDigit centered calc5' id='realgrcalc%d' name='sales[%d][realGrams]' placeholder='Real gr.' step='0.01' style='border: #5aa242 solid 2px; width: 50px; border-radius: 2px; padding: 2px 1px;' /></td>
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
		      return [0.01, parseFloat($('#grcalc%d').val()) + {$_SESSION['flowerLimit']}];
		      
		  }
		});
	

  }); // end ready
</script>
",
	  $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $name, $growtype, $flower['flowertype'], $price, $i, $i, $price, $i, $i, $savedGrams[$i], $i, $i, $savedEuro[$i], $i, $i, $savedGrams2[$i], $i, $i, $i, $i, $name, $i, $flower['purchaseid'], $i, $i, $flower['flowerid'], $i, $i, $i
	  );
	  
  }
  
  	  echo $flower_row;
  	  
}

	if ($menuType == 1) {
		echo "</table></div><div class='leftfloat'><table class='default nonhover'>";
	}
  
		echo "<h3 class='title'>EXTRACTS</h3>";
while ($extract = mysql_fetch_array($resultExtract)) {
	
	if (($usageType == 'Medicinal') && ($discount == 1)) {
		
		if ($medicalDiscountPercentage == 1) {
			$price = ($extract['salesPrice'] * $discount) * $medicalDiscountCalc;
		} else {
			$price = ($extract['salesPrice'] * $discount) - $medicalDiscount;
		}
		
	} else {
		
		$price = $extract['salesPrice'] * $discount;
		
	}
	
	// Look up purchase-specific discount with user id
	$selectPurchaseDiscount = "SELECT discount FROM inddiscounts WHERE user_id = $user_id AND purchaseid = {$extract['purchaseid']}";
	
	$resultPurchaseDiscount = mysql_query($selectPurchaseDiscount)
		or handleError($lang['error-prodprices'],"Error loading flower prices from db: " . mysql_error());
		
	$rowD = mysql_fetch_array($resultPurchaseDiscount);
		$prodDiscount = $rowD['discount'];
		
	if ($prodDiscount > 0) {
		$prodDiscount = (100 - $prodDiscount) / 100;
		$price = $extract['salesPrice'] * $prodDiscount;
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
	
	if ($extract['description'] != '') {
		
		$commentRead = "
		                <img src='images/info2.png' style='inline-block; margin-top: 5px; margin-left: 5px;' id='comment$i' /><div id='helpBox$i' class='helpBox'><strong>{$lang['extracts-description']}:</strong><br />{$extract['description']}</div>
		                <script>
		                  	$('#comment$i').on({
						 		'mouseover' : function() {
								 	$('#helpBox$i').css('display', 'block');
						  		},
						  		'mouseout' : function() {
								 	$('#helpBox$i').css('display', 'none');
							  	}
						  	});
						</script>
		                ";
		
	} else {
		
		$commentRead = "";
		
	}		
	if ($extract['medicaldescription'] != '') {
		
		$commentReadM = "
		                <img src='images/med.png' style='inline-block; margin-top: 5px; margin-left: 5px;' id='commentM$i' /><div id='helpBoxM$i' class='helpBox'><strong>{$lang['extracts-medicaldesc']}:</strong><br />{$extract['medicaldescription']}</div>
		                <script>
		                  	$('#commentM$i').on({
						 		'mouseover' : function() {
								 	$('#helpBoxM$i').css('display', 'block');
						  		},
						  		'mouseout' : function() {
								 	$('#helpBoxM$i').css('display', 'none');
							  	}
						  	});
						</script>
		                ";
		
	} else {
		
		$commentReadM = "";
		
	}		


	if ($menuType == 0) {


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
	<div class='displaybox'>
	 <div id='imageholder'>
	  <center><img src='images/purchases/%s' /></center>
	 </div>
	 <h3>%s</h3>
	 <span class='descprice relative'>
	  <span class='firstline yellow' style='font-size: 16px;' ><strong>%s</strong></span><br />$commentRead $commentReadM
	 </span>
	 <input class='specialInput' id='ppgcalc%d' name='sales[%d][ppg]' value='%0.02f' readonly /><span class='eurosign'>&euro;</span>
	 <br /><!--<center><span style='padding: 2px; border-radius: 2px; margin-top: 3px; display: inline-block;'><img src='images/leafsmall.png' style='width: 15px; height: 14px' /><img src='images/leafsmall.png' style='width: 15px; height: 14px' /><img src='images/leafsmall.png' style='width: 15px; height: 14px' /><img src='images/leafsmall.png' style='width: 15px; height: 14px' /><img src='images/leafsmall.png' style='width: 15px; height: 14px' /></span></center>-->
	 <div class='clearfloat'></div><br />
	 <center>
  	  <input type='number' lang='nb' class='fourDigit centered calc' id='grcalc%d' name='sales[%d][grams]' placeholder='Gr.' step='0.01' value='%s' />
  	  <input type='number' lang='nb' class='fourDigit centered calc2' id='eurcalc%d' name='sales[%d][euro]' placeholder='&euro;' step='0.01' value='%s' />
  	  <input type='number' lang='nb' class='fourDigit centered calc' id='grcalcB%d' name='sales[%d][grams2]' placeholder='Gr.' step='0.01' value='%s' style='background-color: yellow; $displayGift'  /> $giftOption<br /><br />
  	  <input type='number' lang='nb' class='fourDigit centered calc5' id='realgrcalc%d' name='sales[%d][realGrams]' placeholder='Real gr.' step='0.01' style='width: 70px;' />
  	  <input type='hidden' name='sales[%d][name]' value='%s' />
  	  <input type='hidden' name='sales[%d][purchaseid]' value='%d' />
  	  <input type='hidden' name='sales[%d][category]' value='2' />
  	  <input type='hidden' name='sales[%d][productid]' value='%d' />
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
		      return [0.01, parseFloat($('#grcalc%d').val()) + {$_SESSION['extractLimit']}];
		  }
		});
	

  }); // end ready
</script>
",
	  $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $extract['purchaseid'] . '.' . $extract['photoExt'], $extract['name'], $extract['extract'], $i, $i, $price, $i, $i, $savedGrams[$i], $i, $i, $savedEuro[$i], $i, $i, $savedGrams2[$i], $i, $i, $i, $i, $name, $i, $extract['purchaseid'], $i, $i, $extract['extractid'], $i, $i, $i
	  );
	  
  } else {
	  
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
	    <td class='left'>%s</td>
	    <td class='centered relative' style='padding: 11px 0;'>$commentRead</td>
	    <td class='centered relative' style='padding: 11px 0;'>$commentReadM</td>
	    <td class='right'>%0.02f &euro; <input type='hidden' id='ppgcalc%d' name='sales[%d][ppg]' value='%0.01f' /></td>
	    <td><input type='number' lang='nb' class='twoDigit centered calc' style='border: #5aa242 solid 2px; width: 50px; border-radius: 2px; padding: 2px 1px;' id='grcalc%d' name='sales[%d][grams]' placeholder='Gr.' step='0.01' value='%s' /></td>
	    <td><input type='number' lang='nb' class='twoDigit centered calc2' style='border: #5aa242 solid 2px; width: 50px; border-radius: 2px; padding: 2px 1px;' id='eurcalc%d' name='sales[%d][euro]' placeholder='&euro;' step='0.01' value='%s' /></td>
	    <td><input type='number' lang='nb' class='twoDigit centered calc' id='grcalcB%d' name='sales[%d][grams2]' placeholder='Gr.' step='0.01' value='%s' style='border: #5aa242 solid 2px; width: 50px; border-radius: 2px; padding: 2px 1px; background-color: yellow; $displayGift'  />$giftOption</td>
	    <td><input type='number' lang='nb' class='fourDigit centered calc5' id='realgrcalc%d' name='sales[%d][realGrams]' placeholder='Gr. real' step='0.01' style='border: #5aa242 solid 2px; width: 50px; border-radius: 2px; padding: 2px 1px;' /></td>
	   </tr>
  	  <input type='hidden' name='sales[%d][name]' value='%s' />
  	  <input type='hidden' name='sales[%d][purchaseid]' value='%d' />
  	  <input type='hidden' name='sales[%d][category]' value='2' />
  	  <input type='hidden' name='sales[%d][productid]' value='%d' />
<script>
    $(document).ready(function() {
	    
		$('#realgrcalc%d').rules('add', {
		  required: function(element){
	            return $('#grcalc%d').val()!='';
	        },
		  number: true,
		  range: function() {
		      return [0.01, parseFloat($('#grcalc%d').val()) + {$_SESSION['extractLimit']}];
		  }
		});
	

  }); // end ready
</script>
",
	  $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $extract['name'], $extract['extract'], $price, $i, $i, $price, $i, $i, $savedGrams[$i], $i, $i, $savedEuro[$i], $i, $i, $savedGrams2[$i], $i, $i, $i, $i, $name, $i, $extract['purchaseid'], $i, $i, $extract['extractid'], $i, $i, $i
	  );
	  
   }
	  echo $extract_row;
	  
  }
  
  
  	// Query to look up categories
	$selectCats = "SELECT id, name from categories ORDER by id ASC";

	$resultCats = mysql_query($selectCats)
		or handleError($lang['error-loadflowers'],"Error loading flower from db: " . mysql_error());

	while ($category = mysql_fetch_array($resultCats)) {
		
		$categoryid = $category['id'];
		$name = $category['name'];
  
		if ($menuType == 1) {
			echo "</table></div><br /><div class='leftfloat'><table class='default nonhover'>";
		}
	
 		echo "<h3 class='title'>$name</h3>";
 		
	 // For each cat, look up products
  	$selectProduct = "SELECT pr.productid, pr.name, pr.description, pr.medicaldescription, p.purchaseid, p.salesPrice, p.realQuantity, p.photoExt FROM products pr, purchases p WHERE p.category = $categoryid AND p.productid = pr.productid AND p.closedAt IS NULL AND inMenu = 1 ORDER BY p.salesPrice ASC;";
  	
  	
	$resultProduct = mysql_query($selectProduct)
		or handleError($lang['error-prodprices'],"Error loading extract prices from db: " . mysql_error());
		
		

		while ($product = mysql_fetch_array($resultProduct)) {
			
		if ($usageType == 'Medicinal') {
			
			if ($medicalDiscountPercentage == 1) {
				$price = ($product['salesPrice'] * $discount) * $medicalDiscountCalc;
			} else {
				$price = ($product['salesPrice'] * $discount) - $medicalDiscount;
			}
			
		} else {
			
			$price = $product['salesPrice'] * $discount;
			
		}

	
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
		                <img src='images/info2.png' style='inline-block; margin-top: 5px; margin-left: 5px;' id='comment$i' /><div id='helpBox$i' class='helpBox'><strong>{$lang['extracts-description']}:</strong><br />{$product['description']}</div>
		                <script>
		                  	$('#comment$i').on({
						 		'mouseover' : function() {
								 	$('#helpBox$i').css('display', 'block');
						  		},
						  		'mouseout' : function() {
								 	$('#helpBox$i').css('display', 'none');
							  	}
						  	});
						</script>
		                ";
		
	} else {
		
		$commentRead = "";
		
	}		
	if ($product['medicaldescription'] != '') {
		
		$commentReadM = "
		                <img src='images/med.png' style='inline-block; margin-top: 5px; margin-left: 5px;' id='commentM$i' /><div id='helpBoxM$i' class='helpBox'><strong>{$lang['extracts-medicaldesc']}:</strong><br />{$product['medicaldescription']}</div>
		                <script>
		                  	$('#commentM$i').on({
						 		'mouseover' : function() {
								 	$('#helpBoxM$i').css('display', 'block');
						  		},
						  		'mouseout' : function() {
								 	$('#helpBoxM$i').css('display', 'none');
							  	}
						  	});
						</script>
		                ";
		
	} else {
		
		$commentReadM = "";
		
	}	
				
	if ($menuType == 0) {
	 		
	
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
	<div class='displaybox' style='text-align: center;'>
	 <div id='imageholder'>
	  <center><img src='images/purchases/%s' /></center>
	 </div>
	 <h3>%s</h3>
	 <span class='descprice relative'>
	  <br />$commentRead $commentReadM
	 </span>
	 <input class='specialInput' id='ppgcalc%d' name='sales[%d][ppg]' value='%0.02f' readonly /><span class='eurosign'>&euro;</span>
	 <br />
	 <div class='clearfloat'></div><br />
	 <center>
  	  <input type='number' lang='nb' class='fourDigit centered calc3' id='grcalc%d' name='sales[%d][grams]' placeholder='#' step='0.01' value='%s' />
  	  <input type='number' lang='nb' class='fourDigit centered calc2' id='eurcalc%d' name='sales[%d][euro]' placeholder='&euro;' step='0.01' value='%s' />
  	  <input type='number' lang='nb' class='fourDigit centered calc4' id='grcalcB%d' name='sales[%d][grams2]' placeholder='#' step='0.01' value='%s' style='background-color: yellow; $displayGift'  /> $giftOption
  	  <input type='hidden' name='sales[%d][name]' value='%s' />
  	  <input type='hidden' name='sales[%d][purchaseid]' value='%d' />
  	  <input type='hidden' name='sales[%d][category]' value='%d' />
  	  <input type='hidden' name='sales[%d][productid]' value='%d' />
  	 </center>
	</div>",
	  $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $product['purchaseid'] . '.' . $product['photoExt'], $product['name'], $i, $i, $price, $i, $i, $savedGrams[$i], $i, $i, $savedEuro[$i], $i, $i, $savedGrams2[$i], $i, $i, $name, $i, $product['purchaseid'], $i, $categoryid, $i, $product['productid']
	  );
	  
  } else {
	  
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
        
	   <tr>
	    <td class='left'>%s</td>
	    <td class='centered relative' style='padding: 11px 0;'>$commentRead</td>
	    <td class='centered relative' style='padding: 11px 0;'>$commentReadM</td>
	    <td class='right'>%0.02f &euro; <input type='hidden' id='ppgcalc%d' name='sales[%d][ppg]' value='%0.01f' /></td>
	    <td><input type='number' lang='nb' class='twoDigit centered calc3' style='border: #5aa242 solid 2px; width: 50px; border-radius: 2px; padding: 2px 1px;' id='grcalc%d' name='sales[%d][grams]' placeholder='#' step='0.01' value='%s' /></td>
	    <td><input type='number' lang='nb' class='twoDigit centered calc2' style='border: #5aa242 solid 2px; width: 50px; border-radius: 2px; padding: 2px 1px; display: none;' id='eurcalc%d' name='sales[%d][euro]' placeholder='&euro;' step='0.01' value='%s' /></td>
	    <td><input type='number' lang='nb' class='twoDigit centered calc4' id='grcalcB%d' name='sales[%d][grams2]' placeholder='#' step='0.01' value='%s' style='border: #5aa242 solid 2px; width: 50px; border-radius: 2px; padding: 2px 1px; background-color: yellow; $displayGift'  />$giftOption</td>
	   </tr>
  	  <input type='hidden' name='sales[%d][name]' value='%s' />
  	  <input type='hidden' name='sales[%d][purchaseid]' value='%d' />
  	  <input type='hidden' name='sales[%d][category]' value='%d' />
  	  <input type='hidden' name='sales[%d][productid]' value='%d' />
",
	  $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $product['name'], $price, $i, $i, $price, $i, $i, $savedGrams[$i], $i, $i, $savedEuro[$i], $i, $i, $savedGrams2[$i], $i, $i, $name, $i, $product['purchaseid'], $i, $categoryid, $i, $product['productid']
	  );
	  
  }
	  echo $product_row;
	  }
		
 		
	}

echo "</center>";

?>
	  <div class="clearFloat"></div>

	 </tbody>
	 </table>
	
	<div id="memberbox">
<img src="images/minimize.png" class="closeImg pointer" id="minimizeMemberBox" />

<?php
echo "<a href='new-picture.php?user_id=$user_id'><img src='images/members/$user_id.$photoext' class='userPicture' /></a><span class='memberinfo'><a href='#' id='chipClick'><img src='images/rfid.png' /></a>&nbsp;<a href='profile.php?user_id=$user_id'><span class='membername'>$memberno $first_name $last_name</span></a>";

	// Consumption this calendar month
	$selectSales = "SELECT SUM(quantity), SUM(amount) FROM sales WHERE userid = $user_id AND MONTH(saletime) = MONTH(NOW()) AND YEAR(saletime) = YEAR(NOW())";

	$result = mysql_query($selectSales)
		or handleError($lang['error-loadsales'],"Error loading sale from db: " . mysql_error());
		
	$row = mysql_fetch_array($result);
		$amountMonth = $row['SUM(amount)'];
		$quantityMonth = $row['SUM(quantity)'];
		
		if ($quantityMonth == '') {
			$quantityMonth = 0;
		}
		
	// Determine consumption status vs limit
	$consumptionDelta = $quantityMonth - $mconsumption;
	$consumptionDeltaPlus = 0 - $consumptionDelta;
	
	// Consumption today
	$selectSales = "SELECT SUM(quantity) FROM sales WHERE userid = $user_id AND DATE(saletime) = DATE(NOW())";

	$result2 = mysql_query($selectSales)
		or handleError($lang['error-loadsales'],"Error loading sale from db: " . mysql_error());
		
	$row = mysql_fetch_array($result2);
		$quantityToday = $row['SUM(quantity)'];
	
	echo "<br /><img src='images/consumed.png' style='margin-bottom: -1px' /><strong> {$expr(number_format($quantityMonth,1))} / {$expr(number_format($mconsumption,0))} g. <span class='yellow'>{$lang['dispensary-today']}: {$expr(number_format($quantityToday,1))} g. </span></strong>";


	if ($userGroup == 5 && $_SESSION['membershipFees'] == 1) {  // show Member w/ expiry
	
		$memberExp = date('y-m-d', strtotime($paidUntil));
		$memberExpReadable = date('d M', strtotime($paidUntil));
		$timeNow = date('y-m-d');
		
		if ($memberExp == $timeNow) {
			echo "<br /><a href='pay-membership.php?user_id=$user_id'><img src='images/exclamation-20.png' class='warningIcon' /> <span class='yellow'>" . $lang['member-expirestoday'] . "</span></a>";
	  	} else if ($memberExp < $timeNow) {
		  	echo "<br /><a href='pay-membership.php?user_id=$user_id'><img src='images/exclamation-20.png' class='warningIcon' /> <span class='yellow'>" . $lang['member-expiredon'] . ": $memberExpReadable.</span></a>";
		  	
		  	if ($paymentWarning == '1') {
		  	echo "<br /><a href='pay-membership.php?user_id=$user_id'><img src='images/exclamation-22.png' class='warningIcon' /> <img src='images/exclamation-22.png' class='warningIcon' style='margin-left: -16px; margin-right: 1px;' /> <span class='yellow'>" . $lang['member-receivedwarning'] . "</span></a>";
		  	}
		  	
		}
	}
		if ($usageType == 'Medicinal') {
		echo "<br /><img src='images/medical-20.png' style='margin-bottom: -4px; margin-right: 2px;' /> <span class='yellow'>" . $lang['medicinal-user'] . "</span>";
	}
	
	if (date('m-d') == date('m-d', strtotime($year . "-" . $month . "-" . $day . " 00:00:00"))) {
		echo "<br /><img src='images/cake-22.png' class='warningIcon' style='margin-bottom: -4px; margin-right: 2px;' /> <span class='yellow'>" . $lang['global-birthday'] . "</span>";
	}

	
	// Is the user a high roller?
	$selectHighRollerLimit = "SELECT highRollerWeekly FROM systemsettings";

	$result2 = mysql_query($selectHighRollerLimit)
		or handleError($lang['error-loadsales'],"Error loading sale from db: " . mysql_error());
		
	$row = mysql_fetch_array($result2);
		$highRollerWeekly = $row['highRollerWeekly'];

	if ($totalAmountPerWeek >= $highRollerWeekly) {
		echo "<br /><img src='images/hi-roller.png' class='warningIcon' style='margin-bottom: -4px; margin-right: 2px;' /> <span class='yellow'>High roller</span>";
	}
	
	// Query to look up user debt
	$selectSales = "SELECT SUM(amount), SUM(amountpaid) FROM sales WHERE userid = $user_id";

	$result3 = mysql_query($selectSales)
		or handleError($lang['error-loadsales'],"Error loading sale from db: " . mysql_error());

	$row3 = mysql_fetch_array($result3);
		$amtTot = $row3['SUM(amount)'];
		$amtPaid = $row3['SUM(amountpaid)'];
		$amtOwed = $amtTot - $amtPaid;

	 if ($amtOwed > 0.1 && $userGroup > 2) {
		 echo "<br /><a href='settle-debt-2.php?user_id=$user_id'><img src='images/exclamation-22.png' class='warningIcon' style='margin-bottom: -4px; margin-right: 2px;' /> <span class='yellow'>Has debt</span></a>";
	 }

	
	
	if ($quantityMonth >= $mconsumption) {
		echo "<br /><img src='images/exclamation-22.png' class='warningIcon' style='margin-bottom: -4px; margin-right: 2px;' /> <span class='yellow'>" . $lang['member-conslimitexc'] . "(+$consumptionDelta g)</span>";
	} else if ($consumptionDeltaPlus < ($mconsumption * 0.1)) {
		echo "<br /><img src='images/exclamation-22.png' class='warningIcon'  /> <span class='yellow'>" . $lang['member-conslimitnear'] . "($consumptionDeltaPlus g " . $lang['global-remaining'] . ")</span>";
	}
	
	?>
	</span>
	<div class="clearFloat"></div>
 	   <input type='hidden' id='eurcalcTOTexp' name='euroTOT' />
 	   <input type='hidden' name='dispense' value='done' /> 	   
 	   <input type='hidden' name='paidUntil' value='<?php echo $paidUntil; ?>' /> 	   
  	   <input type='hidden' name='user_id' value='<?php echo $user_id; ?>' />
  	   <input type='hidden' name='realCredit' id='realCredit' value='<?php echo $realCredit; ?>' />
  	   <input type='hidden' name='realNewCredit' id='realNewCredit' value='<?php echo $realNewCredit; ?>' />
  	   
<?php if ($_SESSION['creditOrDirect'] == 1) { ?>
  	   <table id="memberAmount">
  	    <tr>
  	     <td><input type='number' lang='nb' class='specialInput first notZero' id='grcalcTOT' name='gramsTOT' value="0" readonly /><input type='number' lang='nb' class='specialInput first notZero' id='realgrcalcTOT' name='realgramsTOT' value="0" readonly style='display: none;' /> </td>
  	     <td style='text-align: left;'><span style="font-size: 20px;">&nbsp;gr.</span></td>
  	     <td></td>
  	     <td></td>
  	    </tr>
  	    <tr>
  	     <td><input type='number' lang='nb' class='specialInput first notZero' id='unitcalcTOT' name='unitsTOT' value="0" readonly /> </td>
  	     <td style='text-align: left;'><span style="font-size: 20px;">&nbsp;u.</span></td>
  	     <td style='text-align: right;'><a href='donation-management.php?userid=<?php echo $user_id; ?>'><?php echo $lang['global-credit']; ?>:</td>
  	     <td style='text-align: right;'><?php echo $lang['dispense-newcredit']; ?>:</td>
  	    </tr>
  	    <tr>
  	     <td><input type='number' lang='nb' class='specialInput' id='eurcalcTOT' name='eurcalcTOT' value="0" readonly /> </td>
  	     <td style='text-align: left;'><span style="font-size: 20px;">&nbsp;&euro;</span></td>
  	     <?php if ($credit < 0) { ?>
  	     <td><input type='number' lang='nb' class="specialInput" style='color: red;' id='credit' name='credit' value='0' readonly /> &euro;</td>
  	     <td><input type='number' lang='nb' class='specialInput' style='color: red;' id='newcredit' name='newcredit' value='0' readonly /> &euro;</td>
  	     
  	     <?php } else { ?>
  	     <td><input type='number' lang='nb' class="specialInput" id='credit' name='credit' value='<?php echo number_format($credit,2); ?>' readonly /> &euro;</td>
  	     <td><input type='number' lang='nb' class='specialInput' id='newcredit' name='newcredit' value='<?php echo number_format($credit,2); ?>' readonly /> &euro;</td>
  	     <?php } ?>
<?php } else { ?>
  	   <table id="memberAmount">
  	    <tr>
  	     <td style='text-align: right;'><input type='number' lang='nb' class='specialInput first notZero' id='grcalcTOT' name='gramsTOT' value="0" readonly /><input type='number' lang='nb' class='specialInput first notZero' id='realgrcalcTOT' name='realgramsTOT' value="0" readonly style='display: none;' /> </td>
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
  	    <tr class='donateField' style='display: none;'>
  	     <td></td>
  	     <td></td>
  	     <td class='right'><?php echo $lang['donate']; ?>: <input type='number' lang='nb' class='twoDigit donateOrNot' id='newDonation' name='newDonation' /> &euro;</td>
  	     <td></td>
<?php } ?>


  	    </tr>
  	    
<?php if ($_SESSION['dispDonate'] == 1) { ?>

  	    <tr class='donateField' style='display: none;'>
  	     <td></td>
  	     <td></td>
  	     <td class='right'><?php echo $lang['donate']; ?>: <input type='number' lang='nb' class='twoDigit donateOrNot' id='newDonation' name='newDonation' /> &euro;</td>
  	     <td></td>
  	    </tr>
<?php } ?>
  	   </table>
  	   
  	   
       <center><input type="text" class="donateOrNot" id="cardscan" name="cardid" maxlength="10" placeholder="<?php echo $lang['global-scantoconfirm']; ?>" /><br />
   <a href="#" id="openDispenseDate" style="vertical-align: bottom;" class="yellow">+ Fecha manual</a><br />
   <span id='customDispenseDate' style='display: none;'>
    <input type="number" lang="nb" name="day" class="twoDigit" maxlength="2" placeholder="dd" />
    <input type="number" lang="nb" name="month" class="twoDigit" maxlength="2" placeholder="mm" />
    <input type="number" lang="nb" name="year" class="fourDigit" maxlength="4" placeholder="<?php echo $lang['member-yyyy']; ?>" /><br />
   </span>

       <a href="#" id="openComment" style="vertical-align: bottom;">+ <?php echo $lang['global-comment']; ?></a>
       <textarea class="hiddenComment" name="adminComment" placeholder="<?php echo $lang['global-comment']; ?>?"></textarea>
  	   
 <button type="submit" class='oneClick' name='oneClick'><?php echo $lang['global-save']; ?></button><br />
 <!--<a href="?saveCart&user_id=<?php echo $user_id; ?>">Save</a>--><a href="#" onclick="document.getElementById('registerForm').reset();"><img src="images/clear-cart.png" /></a></center>
  	</div>
</div> <!-- end memberbox -->

<div id='hiddenSummary' style="display: none;>
<img src="images/maximize.png" class="closeImg pointer" id="minimizeSummaryBox" style="z-index: 8888;" />
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
</form>

<?php

}

	displayFooter();