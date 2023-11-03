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
	
	$domain = $_SESSION['domain'];
	
	// Retrieve System settings
	getSettings();
	
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

		$newCredit = $amount + $oldCredit;
				
		// Query to add to Donations table
		 $query = sprintf("INSERT INTO donations (userid, donationTime, type, amount, comment, creditBefore, creditAfter, donatedTo, operator) VALUES ('%d', '%s', '%d', '%f', '%s', '%f', '%f', '%d', '%d');",
		  $userid, $registertime, '1', $amount, $comment, $oldCredit, $newCredit, $donatedTo, $operator);
		  
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
		
			
		// Query to update user profile
		$updateUser = sprintf("UPDATE users SET credit = '%f', lastDispense = '%s' WHERE user_id = '%d';",
			$newCredit,	$registertime, $userid);
				
		try
		{
			$result = $pdo3->prepare("$updateUser")->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
				
			// On success: redirect.
			$_SESSION['successMessage'] = $lang['global-added'] . " " . $amount . $lang['donation-addedsuccessfully'] . $newCredit . " ".$_SESSION['currencyoperator'];
			
			header("Location: new-dispense-2.php?user_id=$userid");
			exit();
		
	}
	
	
if ($_SESSION['realWeight'] == 0) {
	
	// Did this page re-submit with a form? If so, check & store details
	
	// Remember to save with direct or credit flag!!!!!!!!!!!!!! 1 = cash, 2 = tarjeta, 3 = cash. dB field called 'direct'.
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
		$pmtType = $_POST['pmtType'];
		$owndate = $_POST['owndate'];
		$hour = $_POST['hour'];
		$minute = $_POST['minute'];
		$eurdiscount = $_POST['eurdiscount'];
		
		
		
		if ($pmtType == '' || $pmtType == 0) {
			$pmtType = 3;
		}
		
		if ($owndate != '') {
			
			$fulldate = "$owndate $hour:$minute";
			$saletime = date("Y-m-d H:i:s", strtotime($fulldate));
			
		} else {
		
			$saletime = date('Y-m-d H:i:s'); // 	$purchaseDate = date('Y-m-d H:i:s'); ????

		}		
		
		// Look up credit now
		// Add amount of previous dispense to current credit
		// Then continue as below
		
		
		
		// Look up user credit
		$userCredit = "SELECT credit, creditEligible, maxCredit FROM users WHERE user_id = $user_id";
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
			$creditLookup = $row['credit'];
			$newCreditCalc = $creditLookup - $eurcalcTOT;
			$creditEligible = $row['creditEligible'];
			$maxCredit = $row['maxCredit'];
			
			/* 
			
				Credit: 	 8,50
				Purchase: 	38,50
				Newcredit: -30,00
				
			
			*/
			
			// What if it's on Direct, and a member has insufficient saldo? Gotta run the checjk then too. Use direct = 3 I reckon?
			
			if ($newCreditCalc < 0 && $creditEligible == 0 && $pmtType == 3) {
			
				$_SESSION['errorMessage'] = $lang['credit-not-sufficient'] . "!";
				
				pageStart($lang['title-dispense'], NULL, $validationScript, "pdispense", "newsale", $lang['global-dispensecaps'], $_SESSION['successMessage'], $_SESSION['errorMessage']);
				
				exit();
	
				
			} else if ($creditEligible == 1 && $newCreditCalc < (0 - $maxCredit) && $pmtType == 3) {
			
				$_SESSION['errorMessage'] = $lang['credit-exceeded'] . ": " .  $maxCredit;
				
				pageStart($lang['title-dispense'], NULL, $validationScript, "pdispense", "newsale", $lang['global-dispensecaps'], $_SESSION['successMessage'], $_SESSION['errorMessage']);
				
				exit();
				
			}
			
		if ($pmtType == 3) {
			
			// Query to update user credit
			$updateUser = sprintf("UPDATE users SET credit = '%f' WHERE user_id = '%d';",
				$newCreditCalc, $user_id
				);
					
			try
			{
				$result = $pdo3->prepare("$updateUser")->execute();
			}
			catch (PDOException $e)
			{
					$error = 'Error fetching user: ' . $e->getMessage();
					echo $error;
					exit();
			}
			
			// Query to add new sale to Sales table - 6 arguments
		  	$query = sprintf("INSERT INTO sales (saletime, userid, amount, amountPaid, quantity, units, adminComment, settled, settledTo, creditBefore, creditAfter, expiry, realQuantity, discount, direct, discounteur) VALUES ('%s', '%d', '%f', '%f', '%f', '%f', '%s', '%s', '%d', '%f', '%f', '%s', '%f', '%f', '%d', '%f');",
		  	$saletime, $user_id, $eurcalcTOT, $eurcalcTOT, $gramsTOT, $unitsTOT, $adminComment, $saletime, '1', $creditLookup, $newCreditCalc, $paidUntil, $gramsTOT, $totDiscountInput, $pmtType, $eurdiscount);

		
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

			
		} else {
			
			// Query to add new sale to Sales table - 6 arguments
		  	$query = sprintf("INSERT INTO sales (saletime, userid, amount, amountPaid, quantity, units, adminComment, settled, settledTo, expiry, realQuantity, discount, direct, discounteur) VALUES ('%s', '%d', '%f', '%f', '%f', '%f', '%s', '%s', '%d', '%s', '%f', '%f', '%d', '%f');",
		  	$saletime, $user_id, $eurcalcTOT, $eurcalcTOT, $gramsTOT, $unitsTOT, $adminComment, $saletime, '1', $paidUntil, $gramsTOT, $totDiscountInput, $pmtType, $eurdiscount);

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

			
		}
		
		
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
					

			}
		}

	}
	
		// Write to log
		$logTime = date('Y-m-d H:i:s');
	
		$query = sprintf("INSERT INTO log (logtype, logtime, user_id, operator, amount, oldCredit, newCredit) VALUES ('%d', '%s', '%d', '%d', '%f', '%f', '%f');",
		10, $logTime, $user_id, $_SESSION['user_id'], $eurcalcTOT, $creditLookup, $newCreditCalc);
		
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


		// On success: redirect.
		$_SESSION['successMessage'] = $lang['dispense-added'];
		header("Location: dispense.php?saleid=" . $saleid);
		exit();

	}
	/***** FORM SUBMIT END *****/
	
	
	// Get the card ID
	if (isset($_GET['saleid'])) {
		$saleid = $_GET['saleid'];
	} else {
		handleError($lang['error-nomember'],"");
	}

	// Query to look up sale
	$selectSale = "SELECT saleid, saletime, userid, amount, adminComment, creditBefore, creditAfter, direct, units, quantity, realQuantity FROM sales WHERE saleid = $saleid";
		try
		{
			$result = $pdo3->prepare("$selectSale");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$sale = $result->fetch();
		$formattedDate = date("d M H:i", strtotime($sale['saletime'] . "+$offsetSec seconds"));
		$origday =  date("d", strtotime($sale['saletime'] . "+$offsetSec seconds"));
		$origmonth = date("m", strtotime($sale['saletime'] . "+$offsetSec seconds"));
		$origyear = date("Y", strtotime($sale['saletime'] . "+$offsetSec seconds"));
		$saletime = $sale['saletime'];
		$saleid = $sale['saleid'];
		$user_id = $sale['userid'];
		$credit = $sale['creditBefore'];
		$newcredit = $sale['creditAfter'];
		$direct = $sale['direct'];
		$amount = $sale['amount'];
		$units = $sale['units'];
		$quantity = $sale['quantity'];
		$realQuantity = $sale['realQuantity'];
		$adminComment = $sale['adminComment'];
		
		
	// Look up user details for showing profile on the Sales page
	$userDetails2 = "SELECT memberno, paidUntil, userGroup, first_name, last_name, datediff(curdate(),'$memberSince') AS daysMember, paymentWarning, paymentWarningDate, mconsumption, day, month, year, credit, creditEligible, discount, photoext, cardid, nationality, exento FROM users WHERE user_id = '{$user_id}'";
	
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
		$exento = $row2['exento'];		
		
		if ($_SESSION['dispensaryGift'] == 1 && $_SESSION['menuType'] == 1) {
			$giftOption = "<img src='images/free-green.png' id='free%d' style='margin-bottom: 0; margin-left: 5px;' />";
		} else if ($_SESSION['dispensaryGift'] == 1 && $_SESSION['menuType'] == 0) {
			$giftOption = "<img src='images/free.png' id='free%d' style='margin-bottom: 0; margin-left: 5px;' />";
		} else {
			$giftOption = "<input type='hidden' value='%d' />";
		}

		
	// Query to look up total sales and find weekly average
	$selectSales = "SELECT SUM(amount) FROM sales WHERE userid = $user_id";

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
		$totalAmount = $row['SUM(amount)'];
		$totalAmountPerDay = $totalAmount / $daysMember;
		$totalAmountPerWeek = $totalAmountPerDay * 7;
		
		if ($_SESSION['keypads'] == 1) {
			
			$keypadActive = <<<EOD
$('.onScreenKeypad').keypad({
    layout: ['789' + $.keypad.CLOSE, 
        '456' + $.keypad.CLEAR, 
        '123' + $.keypad.BACK, 
        '.0' + $.keypad.SPACE]});			
EOD;
		} else {
			
			$keypadActive = '';
			
		}
		
// Allow negative credit
if ($creditEligible == 1) {
	$validationScript = <<<EOD
	
	  $( function() {
	    $( "#datepicker" ).datepicker({
			dateFormat: "dd-mm-yy"
	    });
	  });
	  
    $(document).ready(function() {
	    
$keypadActive

	    
$.validator.addMethod('totalCheck', function(value, element, params) {
    var field_1 = $('input[name="' + params[0] + '"]').val(),
        field_2 = $('input[name="' + params[1] + '"]').val();
        
    // return parseInt(value) === parseInt(field_1) + parseInt(field_2);
    
    if ((parseFloat(field_1) + parseFloat(field_2)) == 0) {
	    return false;
    } else {
	    return true;
    }
}, "Enter the number of persons (including yourself)");
	    	    

	  $('#registerForm').validate({
		  ignore: [],
		  rules: {
			  cardid: {
				  required: true
				  //, range: ["{$cardid}","{$cardid}"]
			  },
			  eurcalcTOT: {
				  range: [0,"{$_SESSION['dispenseLimit']}"]
			  },
			  gramsTOT: {
				  totalCheck: ['gramsTOT', 'unitsTOT']
			  },
			  unitsTOT: {
				  totalCheck: ['gramsTOT', 'unitsTOT']
			  },
			  pmtType: {
				  required: true,
				  range: [0,3]
			  },
			  hour: {
				  required: {
                    depends: function (element) {
                        return $("#datepicker").is(":filled");
                    }
                  },
				  range:[0,23]
			  },
			  minute: {
				  required: {
                    depends: function (element) {
                        return $("#datepicker").is(":filled");
                    }
                  },
				  range:[0,59]
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
	
	  $( function() {
	    $( "#datepicker" ).datepicker({
			dateFormat: "dd-mm-yy"
	    });
	  });
    $(document).ready(function() {
	    	    
$keypadActive
        
$.validator.addMethod('totalCheck', function(value, element, params) {
    var field_1 = $('input[name="' + params[0] + '"]').val(),
        field_2 = $('input[name="' + params[1] + '"]').val();
        
    // return parseInt(value) === parseInt(field_1) + parseInt(field_2);
    
    if ((parseFloat(field_1) + parseFloat(field_2)) == 0) {
	    return false;
    } else {
	    return true;
    }
}, "Enter the number of persons (including yourself)");

	  $('#registerForm').validate({
		  ignore: [],
		  rules: {
			  newcredit: {
				  range: [0,100000]
			  },
			  cardid: {
				  required: true
				  //, range: ["{$cardid}","{$cardid}"]
			  },
			  eurcalcTOT: {
				  range: [0,"{$_SESSION['dispenseLimit']}"]
			  },
			  gramsTOT: {
				  totalCheck: ['gramsTOT', 'unitsTOT']
			  },
			  unitsTOT: {
				  totalCheck: ['gramsTOT', 'unitsTOT']
			  },
			  pmtType: {
				  required: true,
				  range: [0,3]
			  },
			  hour: {
				  required: {
                    depends: function (element) {
                        return $("#datepicker").is(":filled");
                    }
                  },
				  range:[0,23]
			  },
			  minute: {
				  required: {
                    depends: function (element) {
                        return $("#datepicker").is(":filled");
                    }
                  },
				  range:[0,59]
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
		
		var tempPrice = rsumB * appliedDisc;
		
		var eurdisc = $('#eurdiscount').val();
		
		var newPrice = tempPrice - eurdisc;		

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
          $('#newcredit2').val(roundedtotal);
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
          $('#newcredit2').val(roundedtotal);
	      $('#realNewCredit').val(roundedtotal);
        }
    }
EOD;
}

	$validationScript .= <<<EOD
	
	
	$("#pmt1").click(function () {
		$("#newcredit").val($("#credit").val());
		$("#newcredit2").val($("#credit").val());
		$("#changeTable").css("display", "block");
		$("#paid").focus();
	});	
	$("#pmt2").click(function () {
		$("#newcredit").val($("#credit").val());
		$("#newcredit2").val($("#credit").val());
	});	
	$("#pmt3").click(function () {
	    getItems();
	    computeTot();
	});	
	

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


// Removed CLICK from this one:	
$(document).on('click keypress keyup blur', function(event) {
   if (!$(event.target).is("#cardscan")) {
   if (!$(event.target).is("#pmt1")) {
   if (!$(event.target).is("#pmt2")) {
   if (!$(event.target).is("#pmt3")) {
   if (!$(event.target).is("#submitButton")) {
   if (!$(event.target).is("#paid")) {
   if (!$(event.target).is("#chipClick")) {
	// uncheck all radio boxes
	$("input:radio").attr("checked", false);
	getItems();
    computeTot();
	}
	}
	}
	}
	}
	}
   }
});

$("#paid").on('click keypress keyup blur', function(event) {
	var aX = $('#eurcalcTOT').val();
	var bX = $('#paid').val();
	var totalX = bX - aX;
	var roundedtotalX = totalX.toFixed(2);
	$('#change').val(roundedtotalX);	
});



  }); // end ready
  

EOD;

	pageStart($lang['title-dispense'], NULL, $validationScript, "pdispense", "newsale", $lang['global-dispensecaps'], $_SESSION['successMessage'], $_SESSION['errorMessage']);
	
	// First lookup userdetails, incl. medicinal or not, then decide what to do (which quqeries to execute)
	$userDetails = "SELECT usageType FROM users WHERE user_id = {$user_id}";
	try
	{
		$result = $pdo3->prepare("$userDetails");
		$result->execute();
	}
	catch (PDOException $e)
	{
			$error = 'Error fetching user: ' . $e->getMessage();
			echo $error;
			exit();
	}

	$row = $result->fetch();
		$usageType = $row['usageType'];

if ($_SESSION['dispsig'] == 1) {
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


<div id="memberbox">
<img src="images/minimize.png" class="closeImg pointer" id="minimizeMemberBox" />

<?php

if ($domain == 'cloud') {
	
	echo "<a href='new-picture.php?user_id=$user_id'><img src='images/_$domain/ID/$user_id-front.jpg' class='userPicture' /></a><span class='memberinfo'><a href='#' id='chipClick'><img src='images/rfid.png' onclick='event.preventDefault();' /></a>&nbsp;<a href='profile.php?user_id=$user_id'><span class='membername'>$memberno $first_name $last_name</span></a>";
	
} else {
	
	echo "<a href='new-picture.php?user_id=$user_id'><img src='{$google_root}images/_$domain/members/$user_id.$photoext' class='userPicture' /></a><span class='memberinfo'><a href='#' id='chipClick'><img src='images/rfid.png' onclick='event.preventDefault();' /></a>&nbsp;<a href='profile.php?user_id=$user_id'><span class='membername'>$memberno $first_name $last_name</span></a>";
}

	// Consumption this calendar month
	$selectSales = "SELECT SUM(quantity), SUM(amount) FROM sales WHERE userid = $user_id AND MONTH(saletime) = MONTH(NOW()) AND YEAR(saletime) = YEAR(NOW())";

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
		$quantityToday = $row['SUM(quantity)'];
		
	// Consumption yesterday
	$selectSales = "SELECT SUM(quantity) FROM sales WHERE userid = $user_id AND DATE(saletime) = DATE_ADD(DATE(NOW()), INTERVAL -1 DAY)";

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
		$quantityYday = $row['SUM(quantity)'];

	
	echo "<br /><img src='images/flag.png' style='margin-bottom: -1px; margin-right: 1px;' /><strong>&nbsp;$nationality</strong>";
	
	echo "<br /><img src='images/consumed.png' style='margin-bottom: -1px' /><strong> {$expr(number_format($quantityMonth,1))} / {$expr(number_format($mconsumption,0))} g. <span class='yellow'>{$lang['dispensary-today']}: {$expr(number_format($quantityToday,1))} g, {$lang['dispensary-yesterday']}: {$expr(number_format($quantityYday,1))} g </span></strong>";


	if ($userGroup == 5 && $_SESSION['membershipFees'] == 1 && $exento == 0) {  // show Member w/ expiry
	
		$memberExp = date('y-m-d', strtotime($paidUntil));
		$memberExpReadable = date('d M', strtotime($paidUntil));
		$timeNow = date('y-m-d');
		
		if (strtotime($memberExp) == strtotime($timeNow)) {
			echo "<br /><a href='pay-membership.php?user_id=$user_id'><img src='images/exclamation-20.png' class='warningIcon' /> <span class='yellow'>" . $lang['member-expirestoday'] . "</span></a>";
	  	} else if (strtotime($memberExp) < strtotime($timeNow)) {
		  	echo "<br /><a href='pay-membership.php?user_id=$user_id'><img src='images/exclamation-20.png' class='warningIcon' /> <span class='yellow'>" . $lang['member-expiredon'] . ": $memberExpReadable.</span></a>";
		  	
		  	if ($paymentWarning == '1') {
		  	echo "<br /><a href='pay-membership.php?user_id=$user_id'><img src='images/exclamation-22.png' class='warningIcon' /> <img src='images/exclamation-22.png' class='warningIcon' style='margin-left: -16px; margin-right: 1px;' /> <span class='yellow'>" . $lang['member-receivedwarning'] . "</span></a>";
		  	}
		  	
		}
	}
		if ($usageType == '1') {
		echo "<br /><img src='images/medical-20.png' style='margin-bottom: -4px; margin-right: 2px;' /> <span class='yellow'>" . $lang['medicinal-user'] . "</span>";
	}
	
	if (date('m-d') == date('m-d', strtotime($year . "-" . $month . "-" . $day . " 00:00:00"))) {
		echo "<br /><img src='images/cake-22.png' class='warningIcon' style='margin-bottom: -4px;' /> <span class='yellow'>" . $lang['global-birthday'] . "</span>";
	}

	
	// Is the user a high roller?
	$selectHighRollerLimit = "SELECT highRollerWeekly FROM systemsettings";

	try
	{
		$result = $pdo3->prepare("$selectHighRollerLimit");
		$result->execute();
	}
	catch (PDOException $e)
	{
			$error = 'Error fetching user: ' . $e->getMessage();
			echo $error;
			exit();
	}

	$row = $result->fetch();
		$highRollerWeekly = $row['highRollerWeekly'];

	if ($totalAmountPerWeek >= $highRollerWeekly) {
		echo "<br /><img src='images/hi-roller.png' class='warningIcon' style='margin-bottom: -4px;' /> <span class='yellow'>High roller</span>";
	}
	
	// Query to look up user debt
	$selectSales = "SELECT SUM(amount), SUM(amountpaid) FROM sales WHERE userid = $user_id";

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

	$row3 = $result->fetch();
		$amtTot = $row3['SUM(amount)'];
		$amtPaid = $row3['SUM(amountpaid)'];
		$amtOwed = $amtTot - $amtPaid;

	 if ($amtOwed > 0.1 && $userGroup > 2) {
		 echo "<br /><a href='settle-debt-2.php?user_id=$user_id'><img src='images/exclamation-22.png' class='warningIcon' style='margin-bottom: -4px; margin-left: 7px; margin-right: 2px;' /> <span class='yellow'>Has debt</span></a>";
	 }

	
	
	if ($quantityMonth >= $mconsumption) {
		echo "<br /><img src='images/exclamation-22.png' class='warningIcon' style='margin-bottom: -4px;' /> <span class='yellow'>" . $lang['member-conslimitexc'] . "(+$consumptionDelta g)</span>";
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
  	   
  	   <table id="memberAmount">
  	    <tr>
  	     <td><input type='number' lang='nb' class='specialInput first notZero' id='grcalcTOT' name='gramsTOT' value="1234" readonly /> </td>
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
  	     <td style='text-align: left;'><span style="font-size: 20px;">&nbsp;<?php echo $_SESSION['currencyoperator'] ?></span></td>
  	     <?php if ($credit < 0) { ?>
  	     <td><input type='number' lang='nb' class="specialInput" style='color: red;' id='credit' name='credit' value='0' readonly /> <?php echo $_SESSION['currencyoperator'] ?></td>
  	     <td><input type='number' lang='nb' class='specialInput' style='color: red;' id='newcredit' name='newcredit' value='0' readonly /> <?php echo $_SESSION['currencyoperator'] ?></td>
  	     
  	     <?php } else { ?>
  	     <td><input type='number' lang='nb' class="specialInput" id='credit' name='credit' value='<?php echo $credit; ?>' readonly /> <?php echo $_SESSION['currencyoperator'] ?></td>
  	     <td><input type='number' lang='nb' class='specialInput' id='newcredit' name='newcredit' value='<?php echo $credit; ?>' readonly /> <?php echo $_SESSION['currencyoperator'] ?></td>
  	     <?php } ?>

  	    </tr>
  	    
<?php if ($_SESSION['dispDonate'] == 1) { ?>

  	    <tr class='donateField' style='display: none;'>
  	     <td></td>
  	     <td></td>
  	     <td class='right'><?php echo $lang['donate']; ?>: <input type='number' lang='nb' class='twoDigit donateOrNot' id='newDonation' name='newDonation' /> <?php echo $_SESSION['currencyoperator'] ?></td>
  	     <td></td>
  	    </tr>
<?php } ?>
  	   </table>

<!-- With direct dispensing enabled, show payment options -->
<?php if ($_SESSION['creditOrDirect'] == 0) { ?>

  	   <center><span style='font-size: 17px; display: inline-block; margin-top: 10px; text-transform: uppercase;'><?php echo $lang['paid-by']; ?>:</span><br />
        <input type="radio" id="pmt1" name="pmtType" value='1' />
        <label for="pmt1"><span class='full' id="pmt1trigger"><?php echo $lang['cash']; ?></span></label>
        
<?php if ($_SESSION['bankPayments'] == 1) { ?>
        <input type="radio" id="pmt2" name="pmtType" value='2' />
        <label for="pmt2"><span class='full' id="pmt2trigger"><?php echo $lang['card']; ?></span></label>
<?php } ?>

        <input type="radio" id="pmt3" name="pmtType" value='3' />
        <label for="pmt3"><span class='full' id="pmt3trigger"><?php echo $lang['global-credit']; ?></span></label><br />
  	   <table id='changeTable' style='display: none;'>
  	    <tr>
  	     <td>&nbsp;&nbsp;Pagado:</td>
  	     <td>&nbsp;&nbsp;Cambio:</td>
  	    </tr>
  	    <tr>
  	     <td><input type='number' lang='nb' style='width: 50px; border: 2px solid black; margin-right: 30px;' id='paid' placeholder='<?php echo $_SESSION['currencyoperator'] ?>' /> </td>
  	     <td><input type='number' lang='nb' style='width: 50px; border: 2px solid black; margin: 0;' id='change' placeholder='<?php echo $_SESSION['currencyoperator'] ?>' readonly  /> </td>
  	    </tr>
  	   </table>
  	   </center>

<?php } ?>

<!-- With additional discounts enabled, show discounts -->
<?php if ($_SESSION['checkoutDiscount'] == 1) { ?>
  	   <center><span style='font-size: 17px; display: inline-block; margin-top: 10px; text-transform: uppercase;'><?php echo $lang['member-discount']; ?> <span id='totDiscount'>(a%)</span>:</span><br />
        <input type="checkbox" id="fee0" name="inDiscount" value='5' />
        <label for="fee0"><span class='full2'>5%</span></label>
        
        <input type="checkbox" id="fee1" name="inDiscount" value='10' />
        <label for="fee1"><span class='full2'>10%</span></label>
        
        <input type="checkbox" id="fee2" name="inDiscount" value='20' />
        <label for="fee2"><span class='full2'>20%</span></label>

        <input type="checkbox" id="fee3" name="inDiscount" value='30' />
        <label for="fee3"><span class='full2'>30%</span></label>
        <br />
        <span style='font-size: 17px; display: inline-block; margin-top: 0; text-transform: uppercase;'><?php echo $lang['member-orcaps'] . " " . $lang['member-discount']; ?> <?php echo $_SESSION['currencyoperator'] ?>: <input type='number' name='eurdiscount' id='eurdiscount' class='twoDigit' /></span>
        
  	   </center>
<?php } else { ?>
<input type='hidden' name='eurdiscount' id='eurdiscount' class='twoDigit' value='0' />
 <?php } ?>


       <center>
<?php
	if ($_SESSION['iPadReaders'] == 0) {
		
		// Chip
		if ($_SESSION['dispsig'] == 0) {
			echo <<<EOD
<input type="text" class="donateOrNot" id="cardscan" name="cardid" maxlength="30" placeholder="{$lang['global-scantoconfirm']}" />
EOD;
		// Topaz
		} else if ($_SESSION['dispsig'] == 1) {
			echo <<<EOD
<canvas id="cnv" name="cnv" width="500" height="100" onclick="javascript:onSign()" style="border: 2px solid white;"></canvas><br />
<input id="button2" name="DoneBtn" type="button" value="Finalizar" onclick="javascript:onDone()" style="margin-left: 10px; width: 80px;" /><br />
EOD;
			
		}
	}
?>
   <a href="#" id="openDispenseDate" style="vertical-align: bottom;" class="yellow">+ Fecha manual</a><br />
   <span id='customDispenseDate' style='display: none;'>
 <input type="text" lang="nb" name="owndate" id="datepicker" class="fiveDigit" placeholder="<?php echo $lang['pur-date']; ?>" /> @
 <input type="number" lang="nb" name="hour" id="hour" class="oneDigit" maxlength="2" placeholder="hh" /> :
 <input type="number" lang="nb" name="minute" id="minute" class="oneDigit" maxlength="2" placeholder="mm" />
   </span>

       <a href="#" id="openComment" style="vertical-align: bottom;">+ <?php echo $lang['global-comment']; ?></a>
       <textarea class="hiddenComment" name="adminComment" placeholder="<?php echo $lang['global-comment']; ?>?"></textarea>
  	   
<br /><button type="submit" class='oneClick' name='oneClick' id='submitButton'><?php echo $lang['global-save']; ?></button><br /><br />
 <!--<a href="?saveCart&user_id=<?php echo $user_id; ?>">Save</a>--><a href="#" onclick="event.preventDefault(); document.getElementById('registerForm').reset();"><img src="images/clear-cart.png" /></a>&nbsp;&nbsp;&nbsp;&nbsp;<a href="barcode-dispense.php?user_id=<?php echo $user_id; ?>"><img src="images/barcode2.png"  width="30" /></a></center>
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
  	     <td><span style="font-size: 30px;"> <?php echo $_SESSION['currencyoperator'] ?></span></td>
  	    </tr>
<?php if ($_SESSION['creditOrDirect'] == 1) { ?>
  	    <tr>
  	     <td><!--<img src='images/wallet.png' />--><input type='number' lang='nb' class='fourDigit specialInput' id='newcredit2' name='newcredit2' value="0" readonly /></td>
  	     <td><span style="font-size: 30px;"> <?php echo $_SESSION['currencyoperator'] ?></span></td>
  	    </tr>
<?php } ?>
  	   </table>
</div>

<div class="clearfloat"></div>


<?php

	// Query to look up categories
	$selectCats = "SELECT id, name, description, type from categories ORDER by sortorder ASC, id ASC";
	try
	{
		$resultCats = $pdo3->prepare("$selectCats");
		$resultCats->execute();
	}
	catch (PDOException $e)
	{
			$error = 'Error fetching user: ' . $e->getMessage();
			echo $error;
			exit();
	}

	while ($sort = $resultCats->fetch()) {
		
		$categoryid = $sort['id'];
		$name = $sort['name'];
		$type = $sort['type'];
		
		if ($categoryid == 1) {
			
			if ($_SESSION['fullmenu'] == 0) {
			
	if ($_SESSION['menusortdisp'] == 0) {
		$sorting = "p.salesPrice ASC";
	} else {
		$sorting = "g.name ASC";
	}
		
	$selectFlower = "SELECT g.flowerid, g.name, g.breed2, g.flowertype, g.sativaPercentage, g.description, g.medicaldescription, p.productid, p.purchaseid, p.salesPrice, p.realQuantity, p.growType, p.photoExt, p.medDiscount FROM flower g, purchases p WHERE p.category = 1 AND p.productid = g.flowerid AND p.closedAt IS NULL AND p.inMenu = 1 ORDER BY $sorting;";
	
	try
	{
		$result = $pdo3->prepare("$selectFlower");
		$result->execute();
		$data = $result->fetchAll();
	}
	catch (PDOException $e)
	{
			$error = 'Error fetching user: ' . $e->getMessage();
			echo $error;
			exit();
	}

		// Retrieve Menu type and discount for medical users
		$medicalDiscount = $_SESSION['medicalDiscount'];
		$medicalDiscountPercentage = $_SESSION['medicalDiscountPercentage'];
		
		if ($medicalDiscountPercentage == 1) {
			$medicalDiscountCalc = 1 - ($medicalDiscount / 100);
		}

		
		$menuType = $_SESSION['menuType'];
		
		if ($menuType == 1) {
			echo "<div class='leftfloat'>";
		}
		
	// Start FLOWERS menu
	if ($data) {
		
		$i = 0;

		echo "<h3 class='title' onClick='load1()' style='cursor: pointer;'>{$lang['global-flowerscaps']} <img src='images/plusnew.png' id='plus1' width='15' style='margin-left: 5px;' /><span id='spinner1'></span><input type='hidden' name='click1' id='click1' class='clickControl' value='0' /><input type='hidden' name='clickX1' id='clickX1' value='0' /></h3><span id='menu1'></span>";
	
	}
	
		echo <<<EOD
<script>
function load1(){
	
	if ($("#click1").val() == 0 && $("#clickX1").val() == 0) {
		$(".clickControl").val(1);
		$("#clickX1").val(1);
		$("#plus1").remove();
		
	// Add 'Loading' text
	setInterval(function() {
	$("#spinner1").append(".");

  }, 1000);
  
  
	var dayJSID = parseInt($("#currenti").val());	

    $.ajax({
      type:"post",
      url:"getflowers.php?i="+dayJSID+"&uid=$user_id&discount=$discount&usageType=$usageType",
      datatype:"text",
      success:function(data)
      {
			$("#spinner1").remove();
	       	$('#menu1').append(data);
			$(".clickControl").val(0);
			$("#click1").val(1);
      }
    });
    
    
	}


    
};
</script>
EOD;

			} else {
				
	if ($_SESSION['menusortdisp'] == 0) {
		$sorting = "p.salesPrice ASC";
	} else {
		$sorting = "g.name ASC";
	}
	
	$selectFlower = "SELECT g.flowerid, g.name, g.breed2, g.flowertype, g.sativaPercentage, g.description, g.medicaldescription, p.productid, p.purchaseid, p.salesPrice, p.realQuantity, p.growType, p.photoExt, p.medDiscount FROM flower g, purchases p WHERE p.category = 1 AND p.productid = g.flowerid AND p.closedAt IS NULL AND p.inMenu = 1 ORDER BY $sorting";
	
	try
	{
		$result = $pdo3->prepare("$selectFlower");
		$result->execute();
		$data = $result->fetchAll();
	}
	catch (PDOException $e)
	{
			$error = 'Error fetching user: ' . $e->getMessage();
			echo $error;
			exit();
	}
	
	if ($data) {
	
		echo "<h3 class='title'>{$lang['global-flowerscaps']}</h3>";

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
		
		
		foreach ($data as $flower) {
	

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
		

	// ************************************** Calculate discount(s) ****************************************
	
	$purchaseid = $flower['purchaseid'];
	
	if ($_SESSION['showOrigPrice'] == 1) {
		
		$normalPrice = "({$flower['salesPrice']} {$_SESSION['currencyoperator']})";
		
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
		                <img src='images/info2.png' style='inline-block; margin-top: 5px; margin-left: 5px;' id='comment$i' onClick='showhide$i()' /><div id='helpBox$i' class='helpBox'><strong>{$lang['extracts-description']}:</strong><br />{$flower['description']}</div>
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
	if ($flower['medicaldescription'] != '') {
		
		$commentReadM = "
		                <img src='images/med.png' style='inline-block; margin-top: 5px; margin-left: 5px;' id='commentM$i' onClick='showhideM$i()' /><div id='helpBoxM$i' class='helpBox'><strong>{$lang['extracts-medicaldesc']}:</strong><br />{$flower['medicaldescription']}</div>
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

			$stockDisplay = "<center>$estStock g</center>";
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
	 <div id='imageholder'>
	  <center><img src='images/_$domain/purchases/%s' /></center>
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
	    <td class='right'>%0.02f {$_SESSION['currencyoperator']} <input type='hidden' id='ppgcalc%d' name='sales[%d][ppg]' value='%0.02f' /></td>
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
	 <div id='imageholder'>
	  <center><img src='images/_$domain/purchases/%s' /></center>
	 </div>
	 <h3>%s</h3>
	 <span class='descprice relative'>
	  <span class='firstline'>%s<br /><span class='yellow' style='font-size: 16px; font-weight: 600; margin-top: 3px; display: inline-block;'>%s</span></span><span class='yellow' style='display: none;'>%s</span><br />$commentRead $commentReadM
	 </span>
	 <input class='specialInput' id='ppgcalc%d' name='sales[%d][ppg]' value='%0.02f' readonly /><span class='eurosign'>{$_SESSION['currencyoperator']}</span><br /><span style='color: yellow;' class='biggerfont'>$normalPrice</span><br />$stockDisplay
	 <div class='clearfloat'></div><br />
	 <center>
  	  <input type='text' lang='nb' class='fourDigit centered calc onScreenKeypad' id='grcalc%d' name='sales[%d][grams]' placeholder='Gr.' step='0.01' value='%s' />
  	  <input type='text' lang='nb' class='fourDigit centered calc2 onScreenKeypad' id='eurcalc%d' name='sales[%d][euro]' placeholder='{$_SESSION['currencyoperator']}' step='0.01' value='%s' />
  	  <input type='text' lang='nb' class='fourDigit centered calc calc6 onScreenKeypad' id='grcalcB%d' name='sales[%d][grams2]' placeholder='Gr.' step='0.01' value='%s' style='background-color: yellow; display: none;'  /> $giftOption<br /><br />
  	  <input type='text' lang='nb' class='fourDigit centered calc5' id='realgrcalc%d' name='sales[%d][realGrams]' placeholder='Real gr.' step='0.01' style='width: 70px;' /> &nbsp;&nbsp;<img src='images/delete.png' width='17' style='cursor: pointer;' id='zero%d' />
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
		      return [0.00, parseFloat($('#grcalc%d').val()) + {$_SESSION['flowerLimit']}];
		      
		  }
		});
	

  }); // end ready
</script>
",
	  $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $flower['purchaseid'] . '.' . $flower['photoExt'], $name, $growtype, $flower['flowertype'], $percentageDisplay, $i, $i, $price, $i, $i, $savedGrams[$i], $i, $i, $savedEuro[$i], $i, $i, $savedGrams2[$i], $i, $i, $i, $i, $i, $name, $i, $flower['purchaseid'], $i, $i, $flower['flowerid'], $i, $i, $i
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
	    <td class='right'>%0.02f {$_SESSION['currencyoperator']} <input type='hidden' id='ppgcalc%d' name='sales[%d][ppg]' value='%0.02f' /></td>
	    <td class='right'><span style='color: red;' class='smallerfont'>$normalPrice</span></td>	    
	    <td class='right'>$stockDisplay</td>
	    <td><input type='text' lang='nb' class='twoDigit centered calc onScreenKeypad' style='border: #5aa242 solid 2px; width: 50px; border-radius: 2px; padding: 2px 1px;' id='grcalc%d' name='sales[%d][grams]' placeholder='Gr.' step='0.01' value='%s' /></td>
	    <td><input type='text' lang='nb' class='twoDigit centered calc2 onScreenKeypad' style='border: #5aa242 solid 2px; width: 50px; border-radius: 2px; padding: 2px 1px;' id='eurcalc%d' name='sales[%d][euro]' placeholder='{$_SESSION['currencyoperator']}' step='0.01' value='%s' /></td>
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

				
			}
			
		}

		} else if ($categoryid == 2) {
			
			if ($_SESSION['fullmenu'] == 0) {
			
			
	if ($_SESSION['menusortdisp'] == 0) {
		$sorting = "p.salesPrice ASC";
	} else {
		$sorting = "h.name ASC";
	}

	
  	$selectExtract = "SELECT h.extractid, h.name, h.extract, h.description, h.medicaldescription, p.productid, p.purchaseid, p.salesPrice, p.realQuantity, p.photoExt, p.medDiscount FROM extract h, purchases p WHERE p.category = 2 AND p.productid = h.extractid AND p.closedAt IS NULL AND p.inMenu = 1 ORDER BY $sorting;";
	try
	{
		$result = $pdo3->prepare("$selectExtract");
		$result->execute();
		$data = $result->fetchAll();
	}
	catch (PDOException $e)
	{
			$error = 'Error fetching user: ' . $e->getMessage();
			echo $error;
			exit();
	}
		
	if ($data) {
  
		echo "<h3 class='title' onClick='load2()' style='cursor: pointer;'>{$lang['global-extractscaps']} <img src='images/plusnew.png' id='plus2' width='15' style='margin-left: 5px;' /><span id='spinner2'></span><input type='hidden' name='click2' id='click2' class='clickControl' value='0' /><input type='hidden' name='clickX2' id='clickX2' value='0' /></h3><span id='menu2'></span>";
	
	}
  
		echo <<<EOD
<script>
function load2(){
	
	if ($("#click2").val() == 0 && $("#clickX2").val() == 0) {
		$(".clickControl").val(1);
		$("#clickX2").val(1);
		$("#plus2").remove();
		
	// Add 'Loading' text
	setInterval(function() {
	$("#spinner2").append(".");

  }, 1000);
  
  
	var dayJSID = parseInt($("#currenti").val());	

    $.ajax({
      type:"post",
      url:"getextracts.php?i="+dayJSID+"&uid=$user_id&discount=$discount&usageType=$usageType",
      datatype:"text",
      success:function(data)
      {
			$("#spinner2").remove();
	       	$('#menu2').append(data);
			$(".clickControl").val(0);
			$("#click2").val(1);
      }
    });
    
    
	}


    
};
</script>
EOD;

		} else {
			
	if ($_SESSION['menusortdisp'] == 0) {
		$sorting = "p.salesPrice ASC";
	} else {
		$sorting = "h.name ASC";
	}

	
  	$selectExtract = "SELECT h.extractid, h.name, h.extract, h.description, h.medicaldescription, p.productid, p.purchaseid, p.salesPrice, p.realQuantity, p.photoExt, p.medDiscount FROM extract h, purchases p WHERE p.category = 2 AND p.productid = h.extractid AND p.closedAt IS NULL AND p.inMenu = 1 ORDER BY $sorting;";
	try
	{
		$result = $pdo3->prepare("$selectExtract");
		$result->execute();
		$data = $result->fetchAll();
	}
	catch (PDOException $e)
	{
			$error = 'Error fetching user: ' . $e->getMessage();
			echo $error;
			exit();
	}
	
	if ($data) {
	
		echo "<h3 class='title'>{$lang['global-extractscaps']}</h3>";

		if ($menuType == 1) {
			echo "<table class='default nonhover'><tbody>";
		}


		// Look up category discount
		$selectCategoryDiscount = "SELECT discount FROM catdiscounts WHERE user_id = $user_id AND categoryid = 2";
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
		
		foreach ($data as $extract) {
	

	
	// ************************************** Calculate discount(s) ****************************************
	
	$purchaseid = $extract['purchaseid'];
	
	if ($_SESSION['showOrigPrice'] == 1) {
		
		$normalPrice = "({$extract['salesPrice']} {$_SESSION['currencyoperator']})";
		
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
		$price = number_format($extract['salesPrice'] * $prodDiscount,2);
		
	} else if ($catDiscount != 0 || $catDiscount != '') {
		
		$catCurrentDiscount = (100 - $catDiscount) / 100;
		$price = number_format($extract['salesPrice'] * $catCurrentDiscount,2);

	} else if ($discount != 0) {
		
		$dispDiscount = (100 - $discount) / 100;
		$price = number_format($extract['salesPrice'] * $dispDiscount,2);
		
	} else if ($usageType == '1') {
		
		if ($extract['medDiscount'] > 0) {
			$dispDiscount = (100 - $extract['medDiscount']) / 100;
			$price = number_format($extract['salesPrice'] * $dispDiscount,2);
		} else if ($medicalDiscountPercentage == 1) {
			$price = number_format(($extract['salesPrice']) * $medicalDiscountCalc,2);
		} else {
			$price = number_format(($extract['salesPrice']) - $medicalDiscount,2);
		}
		
	} else {
		
		$price = number_format($extract['salesPrice'],2);
		
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
		                <img src='images/info2.png' style='inline-block; margin-top: 5px; margin-left: 5px;' id='comment$i' onClick='showhide$i()' /><div id='helpBox$i' class='helpBox'><strong>{$lang['extracts-description']}:</strong><br />{$extract['description']}</div>
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
	if ($extract['medicaldescription'] != '') {
		
		$commentReadM = "
		                <img src='images/med.png' style='inline-block; margin-top: 5px; margin-left: 5px;' id='commentM$i' onClick='showhideM$i()' /><div id='helpBoxM$i' class='helpBox'><strong>{$lang['extracts-medicaldesc']}:</strong><br />{$extract['medicaldescription']}</div>
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
		
		$commentReadM = "";
		
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
		$estStock = $extract['realQuantity'] + $permAdditions - $sales - $permRemovals - $inStash;
		
		$estStock = number_format($estStock,1);

			$stockDisplay = "<center>$estStock g</center>";
		}

if ($_SESSION['realWeight'] == 1) {

	if ($menuType == 0) {


	$extract_row =	sprintf("
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
	 <div id='imageholder'>
	  <center><img src='images/_$domain/purchases/%s' /></center>
	 </div>
	 <h3>%s</h3>
	 <span class='descprice relative'>
	  <span class='firstline yellow' style='font-size: 16px;' ><strong>%s</strong></span><br />$commentRead $commentReadM
	 </span>
	 <input class='specialInput' id='ppgcalc%d' name='sales[%d][ppg]' value='%0.02f' readonly /><span class='eurosign'>{$_SESSION['currencyoperator']}</span><br /><span style='color: yellow;' class='biggerfont'>$normalPrice</span><br />$stockDisplay
	 <div class='clearfloat'></div><br />
	 <center>
  	  <input type='text' lang='nb' class='fourDigit centered calc onScreenKeypad' id='grcalc%d' name='sales[%d][grams]' placeholder='Gr.' step='0.01' value='%s' />
  	  <input type='text' lang='nb' class='fourDigit centered calc2 onScreenKeypad' id='eurcalc%d' name='sales[%d][euro]' placeholder='{$_SESSION['currencyoperator']}' step='0.01' value='%s' />
  	  <input type='text' lang='nb' class='fourDigit centered calc calc6 onScreenKeypad' id='grcalcB%d' name='sales[%d][grams2]' placeholder='Gr.' step='0.01' value='%s' style='background-color: yellow; $displayGift'  /> $giftOption<br /><br />
  	  <input type='text' lang='nb' class='fourDigit centered calc5' id='realgrcalc%d' name='sales[%d][realGrams]' placeholder='Real gr.' step='0.01' style='width: 70px;' /> &nbsp;&nbsp;<img src='images/delete.png' width='17' style='cursor: pointer;' id='zero%d' />
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
		      return [0.00, parseFloat($('#grcalc%d').val()) + {$_SESSION['extractLimit']}];
		  }
		});
	

  }); // end ready
</script>
",
	  $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $extract['purchaseid'] . '.' . $extract['photoExt'], $extract['name'], $extract['extract'], $i, $i, $price, $i, $i, $savedGrams[$i], $i, $i, $savedEuro[$i], $i, $i, $savedGrams2[$i], $i, $i, $i, $i, $i, $name, $i, $extract['purchaseid'], $i, $i, $extract['extractid'], $i, $i, $i
	  );
	  
  } else {
	  
		if ($_SESSION['showStock'] == 1) {
			$stockDisplay = "<td class='right'>$estStock g</td>";
		}
		
	$extract_row =	sprintf("
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
	    <td class='centered relative' style='padding: 11px 0;'>$commentRead</td>
	    <td class='centered relative' style='padding: 11px 0;'>$commentReadM</td>
	    <td class='right'>%0.02f {$_SESSION['currencyoperator']} <input type='hidden' id='ppgcalc%d' name='sales[%d][ppg]' value='%0.02f' /></td>
	    <td class='right'><span style='color: red;' class='smallerfont'>$normalPrice</span></td>	    
	    <td class='right'>$stockDisplay</td>
	    <td><input type='text' lang='nb' class='twoDigit centered calc onScreenKeypad' style='border: #5aa242 solid 2px; width: 50px; border-radius: 2px; padding: 2px 1px;' id='grcalc%d' name='sales[%d][grams]' placeholder='Gr.' step='0.01' value='%s' /></td>
	    <td><input type='text' lang='nb' class='twoDigit centered calc2 onScreenKeypad' style='border: #5aa242 solid 2px; width: 50px; border-radius: 2px; padding: 2px 1px;' id='eurcalc%d' name='sales[%d][euro]' placeholder='{$_SESSION['currencyoperator']}' step='0.01' value='%s' /></td>
	    <td><input type='text' lang='nb' class='twoDigit centered calc calc6 onScreenKeypad' id='grcalcB%d' name='sales[%d][grams2]' placeholder='Gr.' step='0.01' value='%s' style='border: #5aa242 solid 2px; width: 50px; border-radius: 2px; padding: 2px 1px; background-color: yellow; $displayGift'  />$giftOption</td>
	    <td><input type='text' lang='nb' class='fourDigit centered calc5' id='realgrcalc%d' name='sales[%d][realGrams]' placeholder='Gr. real' step='0.01' style='border: #5aa242 solid 2px; width: 50px; border-radius: 2px; padding: 2px 1px;' /> &nbsp;&nbsp;<img src='images/delete.png' width='17' style='cursor: pointer;' id='zero%d' /></td>
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
		      return [0.00, parseFloat($('#grcalc%d').val()) + {$_SESSION['extractLimit']}];
		  }
		});
	

  }); // end ready
</script>
",
	  $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $extract['name'], $extract['extract'], $price, $i, $i, $price, $i, $i, $savedGrams[$i], $i, $i, $savedEuro[$i], $i, $i, $savedGrams2[$i], $i, $i, $i, $i, $i, $name, $i, $extract['purchaseid'], $i, $i, $extract['extractid'], $i, $i, $i
	  );
	  
   }
	  echo $extract_row;
	  
} else {
	
	// Real grams
	if ($menuType == 0) {

		if ($_SESSION['showStock'] == 1) {
			$stockDisplay = "<center>$estStock g</center>";
		}

	$extract_row =	sprintf("
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
	<div class='displaybox'>
	 <div id='imageholder'>
	  <center><img src='images/_$domain/purchases/%s' /></center>
	 </div>
	 <h3>%s</h3>
	 <span class='descprice relative'>
	  <span class='firstline yellow' style='font-size: 16px;' ><strong>%s</strong></span><br />$commentRead $commentReadM
	 </span>
	 <input class='specialInput' id='ppgcalc%d' name='sales[%d][ppg]' value='%0.02f' readonly /><span class='eurosign'>{$_SESSION['currencyoperator']}</span><br /><span style='color: yellow;' class='biggerfont'>$normalPrice</span><br />$stockDisplay
	 <div class='clearfloat'></div><br />
	 <center>
  	  <input type='text' lang='nb' class='fourDigit centered calc onScreenKeypad' id='grcalc%d' name='sales[%d][grams]' placeholder='Gr.' step='0.01' value='%s' />
  	  <input type='text' lang='nb' class='fourDigit centered calc2 onScreenKeypad' id='eurcalc%d' name='sales[%d][euro]' placeholder='{$_SESSION['currencyoperator']}' step='0.01' value='%s' />
  	  <input type='text' lang='nb' class='fourDigit centered calc onScreenKeypad' id='grcalcB%d' name='sales[%d][grams2]' placeholder='Gr.' step='0.01' value='%s' style='background-color: yellow; $displayGift'  /> $giftOption
  	  <input type='hidden' name='sales[%d][name]' value='%s' />
  	  <input type='hidden' name='sales[%d][purchaseid]' value='%d' />
  	  <input type='hidden' name='sales[%d][category]' value='2' />
  	  <input type='hidden' name='sales[%d][productid]' value='%d' />
  	 </center>
	</div>",
	  $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $extract['purchaseid'] . '.' . $extract['photoExt'], $extract['name'], $extract['extract'], $i, $i, $price, $i, $i, $savedGrams[$i], $i, $i, $savedEuro[$i], $i, $i, $savedGrams2[$i], $i, $i, $name, $i, $extract['purchaseid'], $i, $i, $extract['extractid']
	  );
	  
  } else {
	  
		if ($_SESSION['showStock'] == 1) {
			$stockDisplay = "<td class='right'>$estStock g</td>";
		}

	  
	$extract_row =	sprintf("
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
	    <td class='centered relative' style='padding: 11px 0;'>$commentRead</td>
	    <td class='centered relative' style='padding: 11px 0;'>$commentReadM</td>
	    <td class='right'>%0.02f {$_SESSION['currencyoperator']} <input type='hidden' id='ppgcalc%d' name='sales[%d][ppg]' value='%0.02f' /></td>
	    <td class='right'><span style='color: red;' class='smallerfont'>$normalPrice</span></td>	    
	    <td class='right'>$stockDisplay</td>
	    <td><input type='text' lang='nb' class='twoDigit centered calc onScreenKeypad' style='border: #5aa242 solid 2px; width: 50px; border-radius: 2px; padding: 2px 1px;' id='grcalc%d' name='sales[%d][grams]' placeholder='Gr.' step='0.01' value='%s' /></td>
	    <td><input type='text' lang='nb' class='twoDigit centered calc2 onScreenKeypad' style='border: #5aa242 solid 2px; width: 50px; border-radius: 2px; padding: 2px 1px;' id='eurcalc%d' name='sales[%d][euro]' placeholder='{$_SESSION['currencyoperator']}' step='0.01' value='%s' /></td>
	    <td><input type='text' lang='nb' class='twoDigit centered calc onScreenKeypad' id='grcalcB%d' name='sales[%d][grams2]' placeholder='Gr.' step='0.01' value='%s' style='border: #5aa242 solid 2px; width: 50px; border-radius: 2px; padding: 2px 1px; background-color: yellow; $displayGift'  />$giftOption</td>
	   </tr>
  	  <input type='hidden' name='sales[%d][name]' value='%s' />
  	  <input type='hidden' name='sales[%d][purchaseid]' value='%d' />
  	  <input type='hidden' name='sales[%d][category]' value='2' />
  	  <input type='hidden' name='sales[%d][productid]' value='%d' />
",
	  $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $extract['name'], $extract['extract'], $price, $i, $i, $price, $i, $i, $savedGrams[$i], $i, $i, $savedEuro[$i], $i, $i, $savedGrams2[$i], $i, $i, $name, $i, $extract['purchaseid'], $i, $i, $extract['extractid']
	  );
	  
   }
	  echo $extract_row;
	  
  }
	  
  }
  

	if ($menuType == 1) {
		echo "</tbody></table></div><div class='leftfloat'><table class='default nonhover'>";
	}

}
		}

		} else {
			
			if ($_SESSION['fullmenu'] == 0) {
			
			
		if ($_SESSION['menusortdisp'] == 0) {
			$sorting = "p.salesPrice ASC";
		} else {
			$sorting = "pr.name ASC";
		}
		
		// For each cat, look up products
	  	$selectProduct = "SELECT pr.productid, pr.name, pr.description, pr.medicaldescription, p.purchaseid, p.salesPrice, p.realQuantity, p.photoExt, p.medDiscount FROM products pr, purchases p WHERE p.category = $categoryid AND p.productid = pr.productid AND p.closedAt IS NULL AND inMenu = 1 ORDER BY $sorting;";
	  	
	  	
		try
		{
			$result = $pdo3->prepare("$selectProduct");
			$result->execute();
			$data = $result->fetchAll();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
			
		if ($data) {
		
		echo "<h3 class='title' onClick='load($categoryid,$type)' style='cursor: pointer;'>$name <img src='images/plusnew.png' id='plus$categoryid' width='15' style='margin-left: 5px;' /><span id='spinner$categoryid'></span><input type='hidden' name='click$categoryid' id='click$categoryid' class='clickControl' value='0' /><input type='hidden' name='clickX$categoryid' id='clickX$categoryid' value='0' /></h3><span id='menu$categoryid'></span>";
		
		}
		
		echo <<<EOD
<script>
function load(cat,type){
	
	if ($("#click"+cat).val() == 0 && $("#clickX"+cat).val() == 0) {
		$(".clickControl").val(1);
		$("#clickX"+cat).val(1);
		$("#plus"+cat).remove();
		
	// Add 'Loading' text
	setInterval(function() {
	$("#spinner"+cat).append(".");

  }, 1000);
  
  
	var dayJSID = parseInt($("#currenti").val());	
	
    $.ajax({
      type:"post",
      url:"getothers.php?i="+dayJSID+"&cat="+cat+"&type="+type+"&uid=$user_id&discount=$discount&usageType=$usageType",
      datatype:"text",
      success:function(data)
      {
			$("#spinner"+cat).remove();
	       	$('#menu'+cat).append(data);
			$(".clickControl").val(0);
			$("#click"+cat).val(1);
      }
    });
    
    
	}


    
};
</script>
EOD;
		
	} else {
		
		
		
		
	if ($_SESSION['menusortdisp'] == 0) {
		$sorting = "p.salesPrice ASC";
	} else {
		$sorting = "pr.name ASC";
	}
	
	// For each cat, look up products
  	$selectProduct = "SELECT pr.productid, pr.name, pr.description, pr.medicaldescription, p.purchaseid, p.salesPrice, p.photoExt, p.realQuantity, p.medDiscount FROM products pr, purchases p WHERE p.category = $categoryid AND p.productid = pr.productid AND p.closedAt IS NULL AND inMenu = 1 ORDER BY $sorting";
	try
	{
		$result = $pdo3->prepare("$selectProduct");
		$result->execute();
		$data = $result->fetchAll();
	}
	catch (PDOException $e)
	{
			$error = 'Error fetching user: ' . $e->getMessage();
			echo $error;
			exit();
	}
	
	if ($data) {
	
		echo "<h3 class='title'>$name</h3>";
		
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

		
		foreach ($data as $product) {
		
	// ************************************** Calculate discount(s) ****************************************
	
	$purchaseid = $product['purchaseid'];
	
	if ($_SESSION['showOrigPrice'] == 1) {
		
		$normalPrice = "({$product['salesPrice']} {$_SESSION['currencyoperator']})";
		
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
		$price = number_format($product['salesPrice'] * $prodDiscount,2);
		
	} else if ($catDiscount != 0 || $catDiscount != '') {
		
		$catCurrentDiscount = (100 - $catDiscount) / 100;
		$price = number_format($product['salesPrice'] * $catCurrentDiscount,2);

	} else if ($discount != 0) {
		
		$dispDiscount = (100 - $discount) / 100;
		$price = number_format($product['salesPrice'] * $dispDiscount,2);
		
	} else if ($usageType == '1') {
		
		if ($product['medDiscount'] > 0) {
			$dispDiscount = (100 - $product['medDiscount']) / 100;
			$price = number_format($product['salesPrice'] * $dispDiscount,2);
		} else if ($medicalDiscountPercentage == 1) {
			$price = number_format(($product['salesPrice']) * $medicalDiscountCalc,2);
		} else {
			$price = number_format(($product['salesPrice']) - $medicalDiscount,2);
		}
		
	} else {
		
		$price = number_format($product['salesPrice'],2);
		
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
	<div class='displaybox'>
	 <div id='imageholder'>
	  <center><img src='images/_$domain/purchases/%s' /></center>
	 </div>
	 <h3>%s</h3>
	 <span class='descprice relative'>
	  <br />$commentRead $commentReadM
	 </span>
	 <input class='specialInput' id='ppgcalc%d' name='sales[%d][ppg]' value='%0.02f' readonly /><span class='eurosign'>{$_SESSION['currencyoperator']}</span><br /><span style='color: yellow;' class='biggerfont'>$normalPrice</span><br />$stockDisplay
	 <div class='clearfloat'></div><br />
	 <center>
  	  <input type='text' lang='nb' class='fourDigit centered calc3 onScreenKeypad' id='grcalc%d' name='sales[%d][grams]' placeholder='#' step='0.01' value='%s' />
  	  <input type='text' lang='nb' class='fourDigit centered calc2 onScreenKeypad' id='eurcalc%d' name='sales[%d][euro]' placeholder='{$_SESSION['currencyoperator']}' step='0.01' value='%s' />
  	  <input type='text' lang='nb' class='fourDigit centered calc4 onScreenKeypad' id='grcalcB%d' name='sales[%d][grams2]' placeholder='#' step='0.01' value='%s' style='background-color: yellow; $displayGift'  /> $giftOption
  	  <input type='hidden' name='sales[%d][name]' value='%s' />
  	  <input type='hidden' name='sales[%d][purchaseid]' value='%d' />
  	  <input type='hidden' name='sales[%d][category]' value='%d' />
  	  <input type='hidden' name='sales[%d][productid]' value='%d' />
  	 </center>
	</div>",
	  $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $product['purchaseid'] . '.' . $product['photoExt'], $product['name'], $i, $i, $price, $i, $i, $savedGrams[$i], $i, $i, $savedEuro[$i], $i, $i, $savedGrams2[$i], $i, $i, $name, $i, $product['purchaseid'], $i, $categoryid, $i, $product['productid']
	  );
	  
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
	    <td class='centered relative' style='padding: 11px 0;'>$commentRead</td>
	    <td class='centered relative' style='padding: 11px 0;'>$commentReadM</td>
	    <td class='right'>%0.02f {$_SESSION['currencyoperator']} <input type='hidden' id='ppgcalc%d' name='sales[%d][ppg]' value='%0.02f' /></td>
	    <td class='right'><span style='color: red;' class='smallerfont'>$normalPrice</span></td>
	    <td class='right'>$stockDisplay</td>
	    <td><input type='text' lang='nb' class='twoDigit centered calc3 onScreenKeypad' style='border: #5aa242 solid 2px; width: 50px; border-radius: 2px; padding: 2px 1px;' id='grcalc%d' name='sales[%d][grams]' placeholder='#' step='0.01' value='%s' /></td>
	    <td><input type='text' lang='nb' class='twoDigit centered calc2 onScreenKeypad' style='border: #5aa242 solid 2px; width: 50px; border-radius: 2px; padding: 2px 1px;' id='eurcalc%d' name='sales[%d][euro]' placeholder='{$_SESSION['currencyoperator']}' step='0.01' value='%s' /></td>
	    <td><input type='text' lang='nb' class='twoDigit centered calc4 onScreenKeypad' id='grcalcB%d' name='sales[%d][grams2]' placeholder='#' step='0.01' value='%s' style='border: #5aa242 solid 2px; width: 50px; border-radius: 2px; padding: 2px 1px; background-color: yellow; $displayGift'  />$giftOption</td>
	   </tr>
  	  <input type='hidden' name='sales[%d][name]' value='%s' />
  	  <input type='hidden' name='sales[%d][purchaseid]' value='%d' />
  	  <input type='hidden' name='sales[%d][category]' value='%d' />
  	  <input type='hidden' name='sales[%d][productid]' value='%d' />
",
	  $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $product['name'], $price, $i, $i, $price, $i, $i, $savedGrams[$i], $i, $i, $savedEuro[$i], $i, $i, $savedGrams2[$i], $i, $i, $name, $i, $product['purchaseid'], $i, $categoryid, $i, $product['productid']
	  );
	  
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
	<div class='displaybox'>
	 <div id='imageholder'>
	  <center><img src='images/_$domain/purchases/%s' /></center>
	 </div>
	 <h3>%s</h3>
	 <span class='descprice relative'>
	  <br />$commentRead $commentReadM
	 </span>
	 <input class='specialInput' id='ppgcalc%d' name='sales[%d][ppg]' value='%0.02f' readonly /><span class='eurosign'>{$_SESSION['currencyoperator']}</span><br /><span style='color: yellow;' class='biggerfont'>$normalPrice</span><br />$stockDisplay
	 <div class='clearfloat'></div><br />
	 <center>
  	  <input type='text' lang='nb' class='fourDigit centered calc onScreenKeypad' id='grcalc%d' name='sales[%d][grams]' placeholder='Gr.' step='0.01' value='%s' />
  	  <input type='text' lang='nb' class='fourDigit centered calc2 onScreenKeypad' id='eurcalc%d' name='sales[%d][euro]' placeholder='{$_SESSION['currencyoperator']}' step='0.01' value='%s' />
  	  <input type='text' lang='nb' class='fourDigit centered calc onScreenKeypad' id='grcalcB%d' name='sales[%d][grams2]' placeholder='Gr.' step='0.01' value='%s' style='background-color: yellow; $displayGift'  /> $giftOption
  	  <input type='hidden' name='sales[%d][name]' value='%s' />
  	  <input type='hidden' name='sales[%d][purchaseid]' value='%d' />
  	  <input type='hidden' name='sales[%d][category]' value='%d' />
  	  <input type='hidden' name='sales[%d][productid]' value='%d' />
  	 </center>
	</div>",
	  $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $product['purchaseid'] . '.' . $product['photoExt'], $product['name'], $i, $i, $price, $i, $i, $savedGrams[$i], $i, $i, $savedEuro[$i], $i, $i, $savedGrams2[$i], $i, $i, $name, $i, $product['purchaseid'], $i, $categoryid, $i, $product['productid']
	  );
	  
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
	    <td class='centered relative' style='padding: 11px 0;'>$commentRead</td>
	    <td class='centered relative' style='padding: 11px 0;'>$commentReadM</td>
	    <td class='right'>%0.02f {$_SESSION['currencyoperator']} <input type='hidden' id='ppgcalc%d' name='sales[%d][ppg]' value='%0.02f' /></td>
	    <td class='right'><span style='color: red;' class='smallerfont'>$normalPrice</span></td>	    
	    <td class='right'>$stockDisplay</td>
	    <td><input type='text' lang='nb' class='twoDigit centered calc onScreenKeypad' style='border: #5aa242 solid 2px; width: 50px; border-radius: 2px; padding: 2px 1px;' id='grcalc%d' name='sales[%d][grams]' placeholder='Gr.' step='0.01' value='%s' /></td>
	    <td><input type='text' lang='nb' class='twoDigit centered calc2 onScreenKeypad' style='border: #5aa242 solid 2px; width: 50px; border-radius: 2px; padding: 2px 1px;' id='eurcalc%d' name='sales[%d][euro]' placeholder='{$_SESSION['currencyoperator']}' step='0.01' value='%s' /></td>
	    <td><input type='text' lang='nb' class='twoDigit centered calc onScreenKeypad' id='grcalcB%d' name='sales[%d][grams2]' placeholder='Gr.' step='0.01' value='%s' style='border: #5aa242 solid 2px; width: 50px; border-radius: 2px; padding: 2px 1px; background-color: yellow; $displayGift'  />$giftOption</td>
	   </tr>
  	  <input type='hidden' name='sales[%d][name]' value='%s' />
  	  <input type='hidden' name='sales[%d][purchaseid]' value='%d' />
  	  <input type='hidden' name='sales[%d][category]' value='%d' />
  	  <input type='hidden' name='sales[%d][productid]' value='%d' />
",
	  $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $product['name'], $price, $i, $i, $price, $i, $i, $savedGrams[$i], $i, $i, $savedEuro[$i], $i, $i, $savedGrams2[$i], $i, $i, $name, $i, $product['purchaseid'], $i, $categoryid, $i, $product['productid']
	  );
	  
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
	 <div id='imageholder'>
	  <center><img src='images/_$domain/purchases/%s' /></center>
	 </div>
	 <h3>%s</h3>
	 <span class='descprice relative'>
	  <br />$commentRead $commentReadM
	 </span>
	 <input class='specialInput' id='ppgcalc%d' name='sales[%d][ppg]' value='%0.02f' readonly /><span class='eurosign'>{$_SESSION['currencyoperator']}</span><br /><span style='color: yellow;' class='biggerfont'>$normalPrice</span><br />$stockDisplay
	 <div class='clearfloat'></div><br />
	 <center>
  	  <input type='text' lang='nb' class='fourDigit centered calc onScreenKeypad' id='grcalc%d' name='sales[%d][grams]' placeholder='Gr.' step='0.01' value='%s' />
  	  <input type='text' lang='nb' class='fourDigit centered calc2 onScreenKeypad' id='eurcalc%d' name='sales[%d][euro]' placeholder='{$_SESSION['currencyoperator']}' step='0.01' value='%s' />
  	  <input type='text' lang='nb' class='fourDigit centered calc calc6 onScreenKeypad' id='grcalcB%d' name='sales[%d][grams2]' placeholder='Gr.' step='0.01' value='%s' style='background-color: yellow; $displayGift'  /> $giftOption<br /><br />
  	  <input type='text' lang='nb' class='fourDigit centered calc5' id='realgrcalc%d' name='sales[%d][realGrams]' placeholder='Real gr.' step='0.01' style='width: 70px;' /> &nbsp;&nbsp;<img src='images/delete.png' width='17' style='cursor: pointer;' id='zero%d' />
  	  <input type='hidden' name='sales[%d][name]' value='%s' />
  	  <input type='hidden' name='sales[%d][purchaseid]' value='%d' />
  	  <input type='hidden' name='sales[%d][category]' value='%d' />
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
		      return [0.00, parseFloat($('#grcalc%d').val()) + {$_SESSION['extractLimit']}];
		  }
		});
	

  }); // end ready
</script>
",
	  $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $product['purchaseid'] . '.' . $product['photoExt'], $product['name'], $i, $i, $price, $i, $i, $savedGrams[$i], $i, $i, $savedEuro[$i], $i, $i, $savedGrams2[$i], $i, $i, $i, $i, $i, $name, $i, $product['purchaseid'], $i, $categoryid, $i, $product['productid'], $i, $i, $i
	  );
	  
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
	    <td class='centered relative' style='padding: 11px 0;'>$commentRead</td>
	    <td class='centered relative' style='padding: 11px 0;'>$commentReadM</td>
	    <td class='right'>%0.02f {$_SESSION['currencyoperator']} <input type='hidden' id='ppgcalc%d' name='sales[%d][ppg]' value='%0.02f' /></td>
	    <td class='right'><span style='color: red;' class='smallerfont'>$normalPrice</span></td>	    
	    <td class='right'>$stockDisplay</td>
	    <td><input type='text' lang='nb' class='twoDigit centered calc onScreenKeypad' style='border: #5aa242 solid 2px; width: 50px; border-radius: 2px; padding: 2px 1px;' id='grcalc%d' name='sales[%d][grams]' placeholder='Gr.' step='0.01' value='%s' /></td>
	    <td><input type='text' lang='nb' class='twoDigit centered calc2 onScreenKeypad' style='border: #5aa242 solid 2px; width: 50px; border-radius: 2px; padding: 2px 1px;' id='eurcalc%d' name='sales[%d][euro]' placeholder='{$_SESSION['currencyoperator']}' step='0.01' value='%s' /></td>
	    <td><input type='text' lang='nb' class='twoDigit centered calc calc6 onScreenKeypad' id='grcalcB%d' name='sales[%d][grams2]' placeholder='Gr.' step='0.01' value='%s' style='border: #5aa242 solid 2px; width: 50px; border-radius: 2px; padding: 2px 1px; background-color: yellow; $displayGift'  />$giftOption</td>
	    <td><input type='text' lang='nb' class='fourDigit centered calc5' id='realgrcalc%d' name='sales[%d][realGrams]' placeholder='Gr. real' step='0.01' style='border: #5aa242 solid 2px; width: 50px; border-radius: 2px; padding: 2px 1px;' /> &nbsp;&nbsp;<img src='images/delete.png' width='17' style='cursor: pointer;' id='zero%d' /></td>
	   </tr>
  	  <input type='hidden' name='sales[%d][name]' value='%s' />
  	  <input type='hidden' name='sales[%d][purchaseid]' value='%d' />
  	  <input type='hidden' name='sales[%d][category]' value='%d' />
  	  <input type='hidden' name='sales[%d][productid]' value='%d' />
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
",
	  $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $product['name'], $price, $i, $i, $price, $i, $i, $savedGrams[$i], $i, $i, $savedEuro[$i], $i, $i, $savedGrams2[$i], $i, $i, $i, $i, $i, $name, $i, $product['purchaseid'], $i, $categoryid, $i, $product['productid'], $i, $i, $i
	  );

	  echo $product_row;
	  
  }
	  
  		} // End gram vs unit cat
	  } // End individual product display, loop back to next product
	} // End real vs fake grams

	  
	  
	  
		
	if ($menuType == 1) {
		echo "</tbody></table></div><div class='leftfloat'><table class='default nonhover'>";
	}
		
	}
	
	}
	
			}


	}


	



?>
	  <div class="clearFloat"></div>

<input type='hidden' id='currenti' value='0' />		
	
<?php if ($_SESSION['dispsig'] == 1) { ?>
<INPUT TYPE=HIDDEN NAME="bioSigData">
<INPUT TYPE=HIDDEN NAME="sigImgData">
<INPUT TYPE=HIDDEN NAME="sigStringData" />
<INPUT TYPE=HIDDEN NAME="sigImageData" />
<?php } ?>
	
</form>

<?php

} else {
	
	// Did this page re-submit with a form? If so, check & store details
	
	// Remember to save with direct or credit flag!!!!!!!!!!!!!! 1 = cash, 2 = tarjeta, 3 = cash. dB field called 'direct'.
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
		$totDiscountInput = $_POST['totDiscountInput'];	
		$pmtType = $_POST['pmtType'];
		$owndate = $_POST['owndate'];
		$hour = $_POST['hour'];
		$minute = $_POST['minute'];
		$eurdiscount = $_POST['eurdiscount'];
		
		if ($pmtType == '' || $pmtType == 0) {
			$pmtType = 3;
		}
		
		if ($owndate != '') {
			
			$fulldate = "$owndate $hour:$minute";
			$saletime = date("Y-m-d H:i:s", strtotime($fulldate));
			
		} else {
		
			$saletime = date('Y-m-d H:i:s'); // 	$purchaseDate = date('Y-m-d H:i:s'); ????

		}
		
		
		// Look up user credit
		$userCredit = "SELECT credit, creditEligible, maxCredit FROM users WHERE user_id = $user_id";
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
			$creditLookup = $row['credit'];
			$newCreditCalc = $creditLookup - $eurcalcTOT;
			$creditEligible = $row['creditEligible'];
			$maxCredit = $row['maxCredit'];
			
			/* 
			
				Credit: 	 8,50
				Purchase: 	38,50
				Newcredit: -30,00
				
			
			*/
			
			if ($newCreditCalc < 0 && $creditEligible == 0 && $pmtType == 3) {
			
				$_SESSION['errorMessage'] = $lang['credit-not-sufficient'] . "!";
				
				pageStart($lang['title-dispense'], NULL, $validationScript, "pdispense", "newsale", $lang['global-dispensecaps'], $_SESSION['successMessage'], $_SESSION['errorMessage']);
				
				exit();
	
				
			} else if ($creditEligible == 1 && $newCreditCalc < (0 - $maxCredit) && $pmtType == 3) {
			
				$_SESSION['errorMessage'] = $lang['credit-exceeded'] . ": " .  $maxCredit;
				
				pageStart($lang['title-dispense'], NULL, $validationScript, "pdispense", "newsale", $lang['global-dispensecaps'], $_SESSION['successMessage'], $_SESSION['errorMessage']);
				
				exit();
				
			}
			
		if ($pmtType == 3) {
			
			// Query to update user credit
			$updateUser = sprintf("UPDATE users SET credit = '%f' WHERE user_id = '%d';",
				$newCreditCalc, $user_id
				);
					
			try
			{
				$result = $pdo3->prepare("$updateUser")->execute();
			}
			catch (PDOException $e)
			{
					$error = 'Error fetching user: ' . $e->getMessage();
					echo $error;
					exit();
			}
			
			// Query to add new sale to Sales table - 6 arguments
		  	$query = sprintf("INSERT INTO sales (saletime, userid, amount, amountPaid, quantity, realQuantity, units, adminComment, settled, settledTo, creditBefore, creditAfter, expiry, discount, direct, discounteur) VALUES ('%s', '%d', '%f', '%f', '%f', '%f', '%f', '%s', '%s', '%d', '%f', '%f', '%s', '%f', '%d', '%f');",
		  	$saletime, $user_id, $eurcalcTOT, $eurcalcTOT, $gramsTOT, $realQuantity, $unitsTOT, $adminComment, $saletime, '1', $creditLookup, $newCreditCalc, $paidUntil, $totDiscountInput, $pmtType, $eurdiscount);
		  	
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

				
		
		} else {
			
			// Query to add new sale to Sales table - 6 arguments
		  	$query = sprintf("INSERT INTO sales (saletime, userid, amount, amountPaid, quantity, realQuantity, units, adminComment, settled, settledTo, expiry, discount, direct, discounteur) VALUES ('%s', '%d', '%f', '%f', '%f', '%f', '%f', '%s', '%s', '%d', '%s', '%f', '%d', '%f');",
		  	$saletime, $user_id, $eurcalcTOT, $eurcalcTOT, $gramsTOT, $realQuantity, $unitsTOT, $adminComment, $saletime, '1', $paidUntil, $totDiscountInput, $pmtType, $eurdiscount);
		  	
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

				
		}
		
		
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
		if ($totDiscountInput > 0) {
			$euro = $sale['euro'] * ((100 - $totDiscountInput) / 100);
		} else {
			$euro = $sale['euro'];
		}
		$gramsTot = $grams + $grams2;
		
		if ($gramsTot > 0) {
			
			if ($grams > 0 && $grams2 > 0) {
				
	    		// Query to add new sale to salesdetails table - ? arguments
			  	$query = sprintf("INSERT INTO salesdetails (saleid, category, productid, quantity, realQuantity, amount, purchaseid) VALUES ('%d', '%d', '%d', '%f', '%f', '%f', '%d');",
			  	$saleid, $category, $productid, $grams, $realGrams, $euro, $purchaseid);
			  
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
					$realGrams = $grams2;
					
				}
	    	
	    		// Query to add new sale to salesdetails table - ? arguments
			  	$query = sprintf("INSERT INTO salesdetails (saleid, category, productid, quantity, realQuantity, amount, purchaseid) VALUES ('%d', '%d', '%d', '%f', '%f', '%f', '%d');",
			  	$saleid, $category, $productid, $grams, $realGrams, $euro, $purchaseid);
			  
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
	
		// Write to log
		$logTime = date('Y-m-d H:i:s');
	
		$query = sprintf("INSERT INTO log (logtype, logtime, user_id, operator, amount, oldCredit, newCredit) VALUES ('%d', '%s', '%d', '%d', '%f', '%f', '%f');",
		10, $logTime, $user_id, $_SESSION['user_id'], $eurcalcTOT, $creditLookup, $newCreditCalc);
		
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


		// On success: redirect.
		$_SESSION['successMessage'] = $lang['dispense-added'];
		header("Location: dispense.php?saleid=" . $saleid);
		exit();

	}
	/***** FORM SUBMIT END *****/
	
	
	// Get the card ID
	if ($_POST['cardid'] != '') {
		
		$cardid = $_POST['cardid'];
		
		if ($cardid == '') {
			
				$_SESSION['errorMessage'] = $lang['scan-error'];
			
		} else {
		
			// Query to look up user
			$rowCount = $pdo3->query("SELECT COUNT(user_id) FROM users WHERE cardid = '$cardid'")->fetchColumn();
			
			if ($rowCount == 0) {
				// Query to look up user
				$rowCount = $pdo3->query("SELECT COUNT(user_id) FROM users WHERE cardid2 = '{$cardid}'")->fetchColumn();
				
				if ($rowCount == 0) {
					// Query to look up user
					$rowCount = $pdo3->query("SELECT COUNT(user_id) FROM users WHERE cardid3 = '{$cardid}'")->fetchColumn();
					
					if ($rowCount == 0) {
				   		handleError($lang['error-keyfob'],"");
					} else {
						$result = $pdo3->prepare("SELECT user_id FROM users WHERE cardid3 = '{$cardid}'");
					}
					
				} else {
					$result = $pdo3->prepare("SELECT user_id FROM users WHERE cardid2 = '{$cardid}'");
				}
	
				
			} else {
				$result = $pdo3->prepare("SELECT user_id FROM users WHERE cardid = '{$cardid}'");
			}
			
					
			$result->execute();
			
			$row = $result->fetch();
				$user_id = $row['user_id'];
				
			// Check if chip is registered more than once
			if ($rowCount > 1) {
				
				$_SESSION['errorMessage'] = $lang['chip-registered-more-than-once'];
				header("Location: duplicate-chip.php?cardid=$cardid");
				exit();
			
			}
		}
		
	} else if (isset($_POST['userSelect'])) {
		$user_id = $_POST['userSelect'];
	} else if (isset($_GET['user_id'])) {
		$user_id = $_GET['user_id'];
	} else {
		handleError($lang['error-nomember'],"");
	}
	
	// Check if member is eligible for dispensing
	$userDetails = "SELECT userGroup, paymentWarning, paymentWarningDate, paidUntil, exento FROM users WHERE user_id = '{$user_id}'";
	
	try
	{
		$result = $pdo3->prepare("$userDetails");
		$result->execute();
	}
	catch (PDOException $e)
	{
			$error = 'Error fetching user: ' . $e->getMessage();
			echo $error;
			exit();
	}

	$row = $result->fetch();
		$userGroup = $row['userGroup'];
		$paymentWarning = $row['paymentWarning'];
		$exento = $row['exento'];
		$paymentWarningDate = $row['paymentWarningDate'];
		$paidUntil = strtotime(date('Y-m-d H:i', strtotime($row['paidUntil'])));
		$nowTime = strtotime(date('Y-m-d H:i'));
		$pwd = strtotime(date('Y-m-d H:i',strtotime($paymentWarningDate)));
		
	if ($userGroup > 7) {
		$_SESSION['errorMessage'] = $lang['cannot-dispense'];
		header("Location: no-dispense.php?user_id=$user_id");
		exit();
	}

	if ($_SESSION['dispExpired'] == 0 && $exento == 0) {
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
	
	try
	{
		$result = $pdo3->prepare("$userDetails");
		$result->execute();
	}
	catch (PDOException $e)
	{
			$error = 'Error fetching user: ' . $e->getMessage();
			echo $error;
			exit();
	}

	$row = $result->fetch();
		$memberSince = date('Y-m-d', strtotime($row['registeredSince']));
	
	
		// Look up user details for showing profile on the Sales page
		$userDetails2 = "SELECT memberno, paidUntil, userGroup, first_name, last_name, datediff(curdate(),'$memberSince') AS daysMember, paymentWarning, paymentWarningDate, mconsumption, day, month, year, credit, creditEligible, discount, photoext, cardid, nationality, exento FROM users WHERE user_id = '{$user_id}'";
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
		$exento = $row2['exento'];		
		
		if ($_SESSION['dispensaryGift'] == 1 && $_SESSION['menuType'] == 1) {
			$giftOption = "<img src='images/free-green.png' id='free%d' style='margin-bottom: 0; margin-left: 5px;' />";
		} else if ($_SESSION['dispensaryGift'] == 1 && $_SESSION['menuType'] == 0) {
			$giftOption = "<img src='images/free.png' id='free%d' style='margin-bottom: 0; margin-left: 5px;' />";
		} else {
			$giftOption = "<input type='hidden' value='%d' />";
		}

		
	// Query to look up total sales and find weekly average
	$selectSales = "SELECT SUM(amount) FROM sales WHERE userid = $user_id";

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
		$totalAmount = $row['SUM(amount)'];
		$totalAmountPerDay = $totalAmount / $daysMember;
		$totalAmountPerWeek = $totalAmountPerDay * 7;
		
		
		if ($_SESSION['keypads'] == 1) {
			
			$keypadActive = <<<EOD
$('.onScreenKeypad').keypad({
    layout: ['789' + $.keypad.CLOSE, 
        '456' + $.keypad.CLEAR, 
        '123' + $.keypad.BACK, 
        '.0' + $.keypad.SPACE]});			
EOD;
		} else {
			
			$keypadActive = '';
			
		}

// Allow negative credit
if ($creditEligible == 1) {
	$validationScript = <<<EOD
	
	  $( function() {
	    $( "#datepicker" ).datepicker({
			dateFormat: "dd-mm-yy"
	    });
	  });
    $(document).ready(function() {
	    
$keypadActive
	    
$.validator.addMethod('totalCheck', function(value, element, params) {
    var field_1 = $('input[name="' + params[0] + '"]').val(),
        field_2 = $('input[name="' + params[1] + '"]').val();
        
    // return parseInt(value) === parseInt(field_1) + parseInt(field_2);
    
    if ((parseFloat(field_1) + parseFloat(field_2)) == 0) {
	    return false;
    } else {
	    return true;
    }
}, "Enter the number of persons (including yourself)");
	    	    

	  $('#registerForm').validate({
		  ignore: [],
		  rules: {
			  cardid: {
				  required: true
				  //, range: ["{$cardid}","{$cardid}"]
			  },
			  eurcalcTOT: {
				  range: [0,"{$_SESSION['dispenseLimit']}"]
			  },
			  gramsTOT: {
				  totalCheck: ['gramsTOT', 'unitsTOT']
			  },
			  unitsTOT: {
				  totalCheck: ['gramsTOT', 'unitsTOT']
			  },
			  pmtType: {
				  required: true,
				  range: [0,3]
			  },
			  hour: {
				  required: {
                    depends: function (element) {
                        return $("#datepicker").is(":filled");
                    }
                  },
				  range:[0,23]
			  },
			  minute: {
				  required: {
                    depends: function (element) {
                        return $("#datepicker").is(":filled");
                    }
                  },
				  range:[0,59]
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
	
	  $( function() {
	    $( "#datepicker" ).datepicker({
			dateFormat: "dd-mm-yy"
	    });
	  });
    $(document).ready(function() {
	    
$keypadActive
	    	    
$.validator.addMethod('totalCheck', function(value, element, params) {
    var field_1 = $('input[name="' + params[0] + '"]').val(),
        field_2 = $('input[name="' + params[1] + '"]').val();
        
    // return parseInt(value) === parseInt(field_1) + parseInt(field_2);
    
    if ((parseFloat(field_1) + parseFloat(field_2)) == 0) {
	    return false;
    } else {
	    return true;
    }
}, "Enter the number of persons (including yourself)");


	  $('#registerForm').validate({
		  ignore: [],
		  rules: {
			  newcredit: {
				  range: [0,100000]
			  },
			  cardid: {
				  required: true
				  //, range: ["{$cardid}","{$cardid}"]
			  },
			  eurcalcTOT: {
				  range: [0,"{$_SESSION['dispenseLimit']}"]
			  },
			  gramsTOT: {
				  totalCheck: ['gramsTOT', 'unitsTOT']
			  },
			  unitsTOT: {
				  totalCheck: ['gramsTOT', 'unitsTOT']
			  },
			  pmtType: {
				  required: true,
				  range: [0,3]
			  },
			  hour: {
				  required: {
                    depends: function (element) {
                        return $("#datepicker").is(":filled");
                    }
                  },
				  range:[0,23]
			  },
			  minute: {
				  required: {
                    depends: function (element) {
                        return $("#datepicker").is(":filled");
                    }
                  },
				  range:[0,59]
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
		
		var tempPrice = rsumB * appliedDisc;
		
		var eurdisc = $('#eurdiscount').val();
		
		var newPrice = tempPrice - eurdisc;		

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
          $('#newcredit2').val(roundedtotal);
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
          $('#newcredit2').val(roundedtotal);
	      $('#realNewCredit').val(roundedtotal);
        }
    }
EOD;
}

	$validationScript .= <<<EOD

	$("#pmt1").click(function () {
		$("#newcredit").val($("#credit").val());
		$("#newcredit2").val($("#credit").val());
		$("#changeTable").css("display", "block");
		$("#paid").focus();
	});	
	$("#pmt2").click(function () {
		$("#newcredit").val($("#credit").val());
		$("#newcredit2").val($("#credit").val());
	});	
	$("#pmt3").click(function () {
	    getItems();
	    computeTot();
	});	
	
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


	
// Removed CLICK from this one:	
$(document).on('click keypress keyup blur', function(event) {
   if (!$(event.target).is("#cardscan")) {
   if (!$(event.target).is("#pmt1")) {
   if (!$(event.target).is("#pmt2")) {
   if (!$(event.target).is("#pmt3")) {
   if (!$(event.target).is("#submitButton")) {
   if (!$(event.target).is("#paid")) {
   if (!$(event.target).is("#chipClick")) {
	// uncheck all radio boxes
	$("input:radio").attr("checked", false);
	getItems();
    computeTot();
	}
	}
	}
	}
	}
	}
   }
});

$("#paid").on('click keypress keyup blur', function(event) {
	var aX = $('#eurcalcTOT').val();
	var bX = $('#paid').val();
	var totalX = bX - aX;
	var roundedtotalX = totalX.toFixed(2);
	$('#change').val(roundedtotalX);	
});



	getItems();
    computeTot();
  }); // end ready
  

EOD;

	pageStart($lang['title-dispense'], NULL, $validationScript, "pdispense", "newsale", $lang['global-dispensecaps'], $_SESSION['successMessage'], $_SESSION['errorMessage']);
	
	// First lookup userdetails, incl. medicinal or not, then decide what to do (which quqeries to execute)
	$userDetails = "SELECT usageType FROM users WHERE user_id = {$user_id}";
	try
	{
		$result = $pdo3->prepare("$userDetails");
		$result->execute();
	}
	catch (PDOException $e)
	{
			$error = 'Error fetching user: ' . $e->getMessage();
			echo $error;
			exit();
	}

	$row = $result->fetch();
	$usageType = $row['usageType'];
	
if ($_SESSION['dispsig'] == 1) {
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

	// Query to look up categories
	$selectCats = "SELECT id, name, description, type from categories ORDER by sortorder ASC, id ASC";
	try
	{
		$resultCats = $pdo3->prepare("$selectCats");
		$resultCats->execute();
	}
	catch (PDOException $e)
	{
			$error = 'Error fetching user: ' . $e->getMessage();
			echo $error;
			exit();
	}

	while ($sort = $resultCats->fetch()) {
		
		$categoryid = $sort['id'];
		$name = $sort['name'];
		$type = $sort['type'];
		
		if ($categoryid == 1) {

			if ($_SESSION['fullmenu'] == 0) {
				
	if ($_SESSION['menusortdisp'] == 0) {
		$sorting = "p.salesPrice ASC";
	} else {
		$sorting = "g.name ASC";
	}
		
	$selectFlower = "SELECT g.flowerid, g.name, g.breed2, g.flowertype, g.sativaPercentage, g.description, g.medicaldescription, p.productid, p.purchaseid, p.salesPrice, p.realQuantity, p.growType, p.photoExt, p.medDiscount FROM flower g, purchases p WHERE p.category = 1 AND p.productid = g.flowerid AND p.closedAt IS NULL AND p.inMenu = 1 ORDER BY $sorting;";
	
	try
	{
		$result = $pdo3->prepare("$selectFlower");
		$result->execute();
		$data = $result->fetchAll();
	}
	catch (PDOException $e)
	{
			$error = 'Error fetching user: ' . $e->getMessage();
			echo $error;
			exit();
	}

		// Retrieve Menu type and discount for medical users
		$medicalDiscount = $_SESSION['medicalDiscount'];
		$medicalDiscountPercentage = $_SESSION['medicalDiscountPercentage'];
		
		if ($medicalDiscountPercentage == 1) {
			$medicalDiscountCalc = 1 - ($medicalDiscount / 100);
		}

		
		$menuType = $_SESSION['menuType'];
		
		if ($menuType == 1) {
			echo "<div class='leftfloat'>";
		}
		
	// Start FLOWERS menu
	if ($data) {
		
		$i = 0;

		echo "<h3 class='title' onClick='load1()' style='cursor: pointer;'>{$lang['global-flowerscaps']} <img src='images/plusnew.png' id='plus1' width='15' style='margin-left: 5px;' /><span id='spinner1'></span><input type='hidden' name='click1' id='click1' class='clickControl' value='0' /><input type='hidden' name='clickX1' id='clickX1' value='0' /></h3><span id='menu1'></span>";
	
	}
	
		echo <<<EOD
<script>
function load1(){
	
	if ($("#click1").val() == 0 && $("#clickX1").val() == 0) {
		$(".clickControl").val(1);
		$("#clickX1").val(1);
		$("#plus1").remove();
		
	// Add 'Loading' text
	setInterval(function() {
	$("#spinner1").append(".");

  }, 1000);
  
  
	var dayJSID = parseInt($("#currenti").val());	

    $.ajax({
      type:"post",
      url:"getflowers.php?i="+dayJSID+"&uid=$user_id&discount=$discount&usageType=$usageType",
      datatype:"text",
      success:function(data)
      {
			$("#spinner1").remove();
	       	$('#menu1').append(data);
			$(".clickControl").val(0);
			$("#click1").val(1);
      }
    });
    
    
	}


    
};
</script>
EOD;	

		} else {
			
			$selectFlower = "SELECT g.flowerid, g.name, g.breed2, g.flowertype, g.sativaPercentage, g.description, g.medicaldescription, p.productid, p.purchaseid, p.salesPrice, p.realQuantity, p.growType, p.photoExt, p.medDiscount FROM flower g, purchases p WHERE p.category = 1 AND p.productid = g.flowerid AND p.closedAt IS NULL AND p.inMenu = 1 ORDER BY $sorting;";
			
			try
			{
				$result = $pdo3->prepare("$selectFlower");
				$result->execute();
				$data = $result->fetchAll();
			}
			catch (PDOException $e)
			{
					$error = 'Error fetching user: ' . $e->getMessage();
					echo $error;
					exit();
			}
	
	if ($data) {
	
		echo "<h3 class='title'>{$lang['global-flowerscaps']}</h3>";
		
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
		

	// ************************************** Calculate discount(s) ****************************************
	
	$purchaseid = $flower['purchaseid'];
	
	if ($_SESSION['showOrigPrice'] == 1) {
		
		$normalPrice = "({$flower['salesPrice']} {$_SESSION['currencyoperator']})";
		
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
		                <img src='images/info2.png' style='inline-block; margin-top: 5px; margin-left: 5px;' id='comment$i' onClick='showhide$i()' /><div id='helpBox$i' class='helpBox'><strong>{$lang['extracts-description']}:</strong><br />{$flower['description']}</div>
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
	if ($flower['medicaldescription'] != '') {
		
		$commentReadM = "
		                <img src='images/med.png' style='inline-block; margin-top: 5px; margin-left: 5px;' id='commentM$i' onClick='showhideM$i()' /><div id='helpBoxM$i' class='helpBox'><strong>{$lang['extracts-medicaldesc']}:</strong><br />{$flower['medicaldescription']}</div>
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

			$stockDisplay = "<center>$estStock g</center>";
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
	 <div id='imageholder'>
	  <center><img src='images/_$domain/purchases/%s' /></center>
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
	    <td class='right'>%0.02f {$_SESSION['currencyoperator']} <input type='hidden' id='ppgcalc%d' name='sales[%d][ppg]' value='%0.02f' /></td>
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
	 <div id='imageholder'>
	  <center><img src='images/_$domain/purchases/%s' /></center>
	 </div>
	 <h3>%s</h3>
	 <span class='descprice relative'>
	  <span class='firstline'>%s<br /><span class='yellow' style='font-size: 16px; font-weight: 600; margin-top: 3px; display: inline-block;'>%s</span></span><span class='yellow' style='display: none;'>%s</span><br />$commentRead $commentReadM
	 </span>
	 <input class='specialInput' id='ppgcalc%d' name='sales[%d][ppg]' value='%0.02f' readonly /><span class='eurosign'>{$_SESSION['currencyoperator']}</span><br /><span style='color: yellow;' class='biggerfont'>$normalPrice</span><br />$stockDisplay
	 <div class='clearfloat'></div><br />
	 <center>
  	  <input type='text' lang='nb' class='fourDigit centered calc onScreenKeypad' id='grcalc%d' name='sales[%d][grams]' placeholder='Gr.' step='0.01' value='%s' />
  	  <input type='text' lang='nb' class='fourDigit centered calc2 onScreenKeypad' id='eurcalc%d' name='sales[%d][euro]' placeholder='{$_SESSION['currencyoperator']}' step='0.01' value='%s' />
  	  <input type='text' lang='nb' class='fourDigit centered calc calc6 onScreenKeypad' id='grcalcB%d' name='sales[%d][grams2]' placeholder='Gr.' step='0.01' value='%s' style='background-color: yellow; display: none;'  /> $giftOption<br /><br />
  	  <input type='text' lang='nb' class='fourDigit centered calc5' id='realgrcalc%d' name='sales[%d][realGrams]' placeholder='Real gr.' step='0.01' style='width: 70px;' /> &nbsp;&nbsp;<img src='images/delete.png' width='17' style='cursor: pointer;' id='zero%d' />
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
		      return [0.00, parseFloat($('#grcalc%d').val()) + {$_SESSION['flowerLimit']}];
		      
		  }
		});
	

  }); // end ready
</script>
",
	  $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $flower['purchaseid'] . '.' . $flower['photoExt'], $name, $growtype, $flower['flowertype'], $percentageDisplay, $i, $i, $price, $i, $i, $savedGrams[$i], $i, $i, $savedEuro[$i], $i, $i, $savedGrams2[$i], $i, $i, $i, $i, $i, $name, $i, $flower['purchaseid'], $i, $i, $flower['flowerid'], $i, $i, $i
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
	    <td class='right'>%0.02f {$_SESSION['currencyoperator']} <input type='hidden' id='ppgcalc%d' name='sales[%d][ppg]' value='%0.02f' /></td>
	    <td class='right'><span style='color: red;' class='smallerfont'>$normalPrice</span></td>	    
	    <td class='right'>$stockDisplay</td>
	    <td><input type='text' lang='nb' class='twoDigit centered calc onScreenKeypad' style='border: #5aa242 solid 2px; width: 50px; border-radius: 2px; padding: 2px 1px;' id='grcalc%d' name='sales[%d][grams]' placeholder='Gr.' step='0.01' value='%s' /></td>
	    <td><input type='text' lang='nb' class='twoDigit centered calc2 onScreenKeypad' style='border: #5aa242 solid 2px; width: 50px; border-radius: 2px; padding: 2px 1px;' id='eurcalc%d' name='sales[%d][euro]' placeholder='{$_SESSION['currencyoperator']}' step='0.01' value='%s' /></td>
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


		
	}
	
		}

		} else if ($categoryid == 2) {
	
			if ($_SESSION['fullmenu'] == 0) {
				
	if ($_SESSION['menusortdisp'] == 0) {
		$sorting = "p.salesPrice ASC";
	} else {
		$sorting = "h.name ASC";
	}

	
  	$selectExtract = "SELECT h.extractid, h.name, h.extract, h.description, h.medicaldescription, p.productid, p.purchaseid, p.salesPrice, p.realQuantity, p.photoExt, p.medDiscount FROM extract h, purchases p WHERE p.category = 2 AND p.productid = h.extractid AND p.closedAt IS NULL AND p.inMenu = 1 ORDER BY $sorting;";
	try
	{
		$result = $pdo3->prepare("$selectExtract");
		$result->execute();
		$data = $result->fetchAll();
	}
	catch (PDOException $e)
	{
			$error = 'Error fetching user: ' . $e->getMessage();
			echo $error;
			exit();
	}
		
	if ($data) {
  
		echo "<h3 class='title' onClick='load2()' style='cursor: pointer;'>{$lang['global-extractscaps']} <img src='images/plusnew.png' id='plus2' width='15' style='margin-left: 5px;' /><span id='spinner2'></span><input type='hidden' name='click2' id='click2' class='clickControl' value='0' /><input type='hidden' name='clickX2' id='clickX2' value='0' /></h3><span id='menu2'></span>";
	
	}
  
		echo <<<EOD
<script>
function load2(){
	
	if ($("#click2").val() == 0 && $("#clickX2").val() == 0) {
		$(".clickControl").val(1);
		$("#clickX2").val(1);
		$("#plus2").remove();
		
	// Add 'Loading' text
	setInterval(function() {
	$("#spinner2").append(".");

  }, 1000);
  
  
	var dayJSID = parseInt($("#currenti").val());	

    $.ajax({
      type:"post",
      url:"getextracts.php?i="+dayJSID+"&uid=$user_id&discount=$discount&usageType=$usageType",
      datatype:"text",
      success:function(data)
      {
			$("#spinner2").remove();
	       	$('#menu2').append(data);
			$(".clickControl").val(0);
			$("#click2").val(1);
      }
    });
    
    
	}


    
};
</script>
EOD;

		} else {
			
	if ($_SESSION['menusortdisp'] == 0) {
		$sorting = "p.salesPrice ASC";
	} else {
		$sorting = "h.name ASC";
	}

	
  	$selectExtract = "SELECT h.extractid, h.name, h.extract, h.description, h.medicaldescription, p.productid, p.purchaseid, p.salesPrice, p.realQuantity, p.photoExt, p.medDiscount FROM extract h, purchases p WHERE p.category = 2 AND p.productid = h.extractid AND p.closedAt IS NULL AND p.inMenu = 1 ORDER BY $sorting;";
	try
	{
		$result = $pdo3->prepare("$selectExtract");
		$result->execute();
		$data = $result->fetchAll();
	}
	catch (PDOException $e)
	{
			$error = 'Error fetching user: ' . $e->getMessage();
			echo $error;
			exit();
	}
	
	if ($data) {
	
		echo "<h3 class='title'>{$lang['global-extractscaps']}</h3>";

	

		if ($menuType == 1) {
			echo "<table class='default nonhover'><tbody>";
		}


		// Look up category discount
		$selectCategoryDiscount = "SELECT discount FROM catdiscounts WHERE user_id = $user_id AND categoryid = 2";
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
		
		foreach ($data as $extract) {
	

	
	// ************************************** Calculate discount(s) ****************************************
	
	$purchaseid = $extract['purchaseid'];
	
	if ($_SESSION['showOrigPrice'] == 1) {
		
		$normalPrice = "({$extract['salesPrice']} {$_SESSION['currencyoperator']})";
		
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
		$price = number_format($extract['salesPrice'] * $prodDiscount,2);
		
	} else if ($catDiscount != 0 || $catDiscount != '') {
		
		$catCurrentDiscount = (100 - $catDiscount) / 100;
		$price = number_format($extract['salesPrice'] * $catCurrentDiscount,2);

	} else if ($discount != 0) {
		
		$dispDiscount = (100 - $discount) / 100;
		$price = number_format($extract['salesPrice'] * $dispDiscount,2);
		
	} else if ($usageType == '1') {
		
		if ($extract['medDiscount'] > 0) {
			$dispDiscount = (100 - $extract['medDiscount']) / 100;
			$price = number_format($extract['salesPrice'] * $dispDiscount,2);
		} else if ($medicalDiscountPercentage == 1) {
			$price = number_format(($extract['salesPrice']) * $medicalDiscountCalc,2);
		} else {
			$price = number_format(($extract['salesPrice']) - $medicalDiscount,2);
		}
		
	} else {
		
		$price = number_format($extract['salesPrice'],2);
		
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
		                <img src='images/info2.png' style='inline-block; margin-top: 5px; margin-left: 5px;' id='comment$i' onClick='showhide$i()' /><div id='helpBox$i' class='helpBox'><strong>{$lang['extracts-description']}:</strong><br />{$extract['description']}</div>
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
	if ($extract['medicaldescription'] != '') {
		
		$commentReadM = "
		                <img src='images/med.png' style='inline-block; margin-top: 5px; margin-left: 5px;' id='commentM$i' onClick='showhideM$i()' /><div id='helpBoxM$i' class='helpBox'><strong>{$lang['extracts-medicaldesc']}:</strong><br />{$extract['medicaldescription']}</div>
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
		
		$commentReadM = "";
		
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
		$estStock = $extract['realQuantity'] + $permAdditions - $sales - $permRemovals - $inStash;
		
		$estStock = number_format($estStock,1);

			$stockDisplay = "<center>$estStock g</center>";
		}

if ($_SESSION['realWeight'] == 1) {

	if ($menuType == 0) {


	$extract_row =	sprintf("
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
	 <div id='imageholder'>
	  <center><img src='images/_$domain/purchases/%s' /></center>
	 </div>
	 <h3>%s</h3>
	 <span class='descprice relative'>
	  <span class='firstline yellow' style='font-size: 16px;' ><strong>%s</strong></span><br />$commentRead $commentReadM
	 </span>
	 <input class='specialInput' id='ppgcalc%d' name='sales[%d][ppg]' value='%0.02f' readonly /><span class='eurosign'>{$_SESSION['currencyoperator']}</span><br /><span style='color: yellow;' class='biggerfont'>$normalPrice</span><br />$stockDisplay
	 <div class='clearfloat'></div><br />
	 <center>
  	  <input type='text' lang='nb' class='fourDigit centered calc onScreenKeypad' id='grcalc%d' name='sales[%d][grams]' placeholder='Gr.' step='0.01' value='%s' />
  	  <input type='text' lang='nb' class='fourDigit centered calc2 onScreenKeypad' id='eurcalc%d' name='sales[%d][euro]' placeholder='{$_SESSION['currencyoperator']}' step='0.01' value='%s' />
  	  <input type='text' lang='nb' class='fourDigit centered calc calc6 onScreenKeypad' id='grcalcB%d' name='sales[%d][grams2]' placeholder='Gr.' step='0.01' value='%s' style='background-color: yellow; $displayGift'  /> $giftOption<br /><br />
  	  <input type='text' lang='nb' class='fourDigit centered calc5' id='realgrcalc%d' name='sales[%d][realGrams]' placeholder='Real gr.' step='0.01' style='width: 70px;' /> &nbsp;&nbsp;<img src='images/delete.png' width='17' style='cursor: pointer;' id='zero%d' />
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
		      return [0.00, parseFloat($('#grcalc%d').val()) + {$_SESSION['extractLimit']}];
		  }
		});
	

  }); // end ready
</script>
",
	  $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $extract['purchaseid'] . '.' . $extract['photoExt'], $extract['name'], $extract['extract'], $i, $i, $price, $i, $i, $savedGrams[$i], $i, $i, $savedEuro[$i], $i, $i, $savedGrams2[$i], $i, $i, $i, $i, $i, $name, $i, $extract['purchaseid'], $i, $i, $extract['extractid'], $i, $i, $i
	  );
	  
  } else {
	  
		if ($_SESSION['showStock'] == 1) {
			$stockDisplay = "<td class='right'>$estStock g</td>";
		}
		
	$extract_row =	sprintf("
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
	    <td class='centered relative' style='padding: 11px 0;'>$commentRead</td>
	    <td class='centered relative' style='padding: 11px 0;'>$commentReadM</td>
	    <td class='right'>%0.02f {$_SESSION['currencyoperator']} <input type='hidden' id='ppgcalc%d' name='sales[%d][ppg]' value='%0.02f' /></td>
	    <td class='right'><span style='color: red;' class='smallerfont'>$normalPrice</span></td>	    
	    <td class='right'>$stockDisplay</td>
	    <td><input type='text' lang='nb' class='twoDigit centered calc onScreenKeypad' style='border: #5aa242 solid 2px; width: 50px; border-radius: 2px; padding: 2px 1px;' id='grcalc%d' name='sales[%d][grams]' placeholder='Gr.' step='0.01' value='%s' /></td>
	    <td><input type='text' lang='nb' class='twoDigit centered calc2 onScreenKeypad' style='border: #5aa242 solid 2px; width: 50px; border-radius: 2px; padding: 2px 1px;' id='eurcalc%d' name='sales[%d][euro]' placeholder='{$_SESSION['currencyoperator']}' step='0.01' value='%s' /></td>
	    <td><input type='text' lang='nb' class='twoDigit centered calc calc6 onScreenKeypad' id='grcalcB%d' name='sales[%d][grams2]' placeholder='Gr.' step='0.01' value='%s' style='border: #5aa242 solid 2px; width: 50px; border-radius: 2px; padding: 2px 1px; background-color: yellow; $displayGift'  />$giftOption</td>
	    <td><input type='text' lang='nb' class='fourDigit centered calc5' id='realgrcalc%d' name='sales[%d][realGrams]' placeholder='Gr. real' step='0.01' style='border: #5aa242 solid 2px; width: 50px; border-radius: 2px; padding: 2px 1px;' /> &nbsp;&nbsp;<img src='images/delete.png' width='17' style='cursor: pointer;' id='zero%d' /></td>
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
		      return [0.00, parseFloat($('#grcalc%d').val()) + {$_SESSION['extractLimit']}];
		  }
		});
	

  }); // end ready
</script>
",
	  $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $extract['name'], $extract['extract'], $price, $i, $i, $price, $i, $i, $savedGrams[$i], $i, $i, $savedEuro[$i], $i, $i, $savedGrams2[$i], $i, $i, $i, $i, $i, $name, $i, $extract['purchaseid'], $i, $i, $extract['extractid'], $i, $i, $i
	  );
	  
   }
	  echo $extract_row;
	  
} else {
	
	// Real grams
	if ($menuType == 0) {

		if ($_SESSION['showStock'] == 1) {
			$stockDisplay = "<center>$estStock g</center>";
		}

	$extract_row =	sprintf("
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
	<div class='displaybox'>
	 <div id='imageholder'>
	  <center><img src='images/_$domain/purchases/%s' /></center>
	 </div>
	 <h3>%s</h3>
	 <span class='descprice relative'>
	  <span class='firstline yellow' style='font-size: 16px;' ><strong>%s</strong></span><br />$commentRead $commentReadM
	 </span>
	 <input class='specialInput' id='ppgcalc%d' name='sales[%d][ppg]' value='%0.02f' readonly /><span class='eurosign'>{$_SESSION['currencyoperator']}</span><br /><span style='color: yellow;' class='biggerfont'>$normalPrice</span><br />$stockDisplay
	 <div class='clearfloat'></div><br />
	 <center>
  	  <input type='text' lang='nb' class='fourDigit centered calc onScreenKeypad' id='grcalc%d' name='sales[%d][grams]' placeholder='Gr.' step='0.01' value='%s' />
  	  <input type='text' lang='nb' class='fourDigit centered calc2 onScreenKeypad' id='eurcalc%d' name='sales[%d][euro]' placeholder='{$_SESSION['currencyoperator']}' step='0.01' value='%s' />
  	  <input type='text' lang='nb' class='fourDigit centered calc onScreenKeypad' id='grcalcB%d' name='sales[%d][grams2]' placeholder='Gr.' step='0.01' value='%s' style='background-color: yellow; $displayGift'  /> $giftOption
  	  <input type='hidden' name='sales[%d][name]' value='%s' />
  	  <input type='hidden' name='sales[%d][purchaseid]' value='%d' />
  	  <input type='hidden' name='sales[%d][category]' value='2' />
  	  <input type='hidden' name='sales[%d][productid]' value='%d' />
  	 </center>
	</div>",
	  $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $extract['purchaseid'] . '.' . $extract['photoExt'], $extract['name'], $extract['extract'], $i, $i, $price, $i, $i, $savedGrams[$i], $i, $i, $savedEuro[$i], $i, $i, $savedGrams2[$i], $i, $i, $name, $i, $extract['purchaseid'], $i, $i, $extract['extractid']
	  );
	  
  } else {
	  
		if ($_SESSION['showStock'] == 1) {
			$stockDisplay = "<td class='right'>$estStock g</td>";
		}

	  
	$extract_row =	sprintf("
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
	    <td class='centered relative' style='padding: 11px 0;'>$commentRead</td>
	    <td class='centered relative' style='padding: 11px 0;'>$commentReadM</td>
	    <td class='right'>%0.02f {$_SESSION['currencyoperator']} <input type='hidden' id='ppgcalc%d' name='sales[%d][ppg]' value='%0.02f' /></td>
	    <td class='right'><span style='color: red;' class='smallerfont'>$normalPrice</span></td>	    
	    <td class='right'>$stockDisplay</td>
	    <td><input type='text' lang='nb' class='twoDigit centered calc onScreenKeypad' style='border: #5aa242 solid 2px; width: 50px; border-radius: 2px; padding: 2px 1px;' id='grcalc%d' name='sales[%d][grams]' placeholder='Gr.' step='0.01' value='%s' /></td>
	    <td><input type='text' lang='nb' class='twoDigit centered calc2 onScreenKeypad' style='border: #5aa242 solid 2px; width: 50px; border-radius: 2px; padding: 2px 1px;' id='eurcalc%d' name='sales[%d][euro]' placeholder='{$_SESSION['currencyoperator']}' step='0.01' value='%s' /></td>
	    <td><input type='text' lang='nb' class='twoDigit centered calc onScreenKeypad' id='grcalcB%d' name='sales[%d][grams2]' placeholder='Gr.' step='0.01' value='%s' style='border: #5aa242 solid 2px; width: 50px; border-radius: 2px; padding: 2px 1px; background-color: yellow; $displayGift'  />$giftOption</td>
	   </tr>
  	  <input type='hidden' name='sales[%d][name]' value='%s' />
  	  <input type='hidden' name='sales[%d][purchaseid]' value='%d' />
  	  <input type='hidden' name='sales[%d][category]' value='2' />
  	  <input type='hidden' name='sales[%d][productid]' value='%d' />
",
	  $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $extract['name'], $extract['extract'], $price, $i, $i, $price, $i, $i, $savedGrams[$i], $i, $i, $savedEuro[$i], $i, $i, $savedGrams2[$i], $i, $i, $name, $i, $extract['purchaseid'], $i, $i, $extract['extractid']
	  );
	  
   }
	  echo $extract_row;
	  
  }
	  
  }
  

	if ($menuType == 1) {
		echo "</tbody></table></div><div class='leftfloat'><table class='default nonhover'>";
	}

			
		}
		
	}

		} else {

 			if ($_SESSION['fullmenu'] == 0) {
 
	
		$mi = 3;

  
		if ($_SESSION['menusortdisp'] == 0) {
			$sorting = "p.salesPrice ASC";
		} else {
			$sorting = "pr.name ASC";
		}
		
		// For each cat, look up products
	  	$selectProduct = "SELECT pr.productid, pr.name, pr.description, pr.medicaldescription, p.purchaseid, p.salesPrice, p.realQuantity, p.photoExt, p.medDiscount FROM products pr, purchases p WHERE p.category = $categoryid AND p.productid = pr.productid AND p.closedAt IS NULL AND inMenu = 1 ORDER BY $sorting;";
	  	
	  	
		try
		{
			$result = $pdo3->prepare("$selectProduct");
			$result->execute();
			$data = $result->fetchAll();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
			
		if ($data) {
		
		echo "<h3 class='title' onClick='load($categoryid,$type)' style='cursor: pointer;'>$name <img src='images/plusnew.png' id='plus$categoryid' width='15' style='margin-left: 5px;' /><span id='spinner$categoryid'></span><input type='hidden' name='click$categoryid' id='click$categoryid' class='clickControl' value='0' /><input type='hidden' name='clickX$categoryid' id='clickX$categoryid' value='0' /></h3><span id='menu$categoryid'></span>";
		
		}
		
		echo <<<EOD
<script>
function load(cat,type){
	
	if ($("#click"+cat).val() == 0 && $("#clickX"+cat).val() == 0) {
		$(".clickControl").val(1);
		$("#clickX"+cat).val(1);
		$("#plus"+cat).remove();
		
	// Add 'Loading' text
	setInterval(function() {
	$("#spinner"+cat).append(".");

  }, 1000);
  
  
	var dayJSID = parseInt($("#currenti").val());	
	
    $.ajax({
      type:"post",
      url:"getothers.php?i="+dayJSID+"&cat="+cat+"&type="+type+"&uid=$user_id&discount=$discount&usageType=$usageType",
      datatype:"text",
      success:function(data)
      {
			$("#spinner"+cat).remove();
	       	$('#menu'+cat).append(data);
			$(".clickControl").val(0);
			$("#click"+cat).val(1);
      }
    });
    
    
	}


    
};
</script>
EOD;

} else {
	
	if ($_SESSION['menusortdisp'] == 0) {
		$sorting = "p.salesPrice ASC";
	} else {
		$sorting = "pr.name ASC";
	}
	
	// For each cat, look up products
  	$selectProduct = "SELECT pr.productid, pr.name, pr.description, pr.medicaldescription, p.purchaseid, p.salesPrice, p.photoExt, p.realQuantity, p.medDiscount FROM products pr, purchases p WHERE p.category = $categoryid AND p.productid = pr.productid AND p.closedAt IS NULL AND inMenu = 1 ORDER BY $sorting";
	try
	{
		$result = $pdo3->prepare("$selectProduct");
		$result->execute();
		$data = $result->fetchAll();
	}
	catch (PDOException $e)
	{
			$error = 'Error fetching user: ' . $e->getMessage();
			echo $error;
			exit();
	}
	
	if ($data) {
	
		echo "<h3 class='title'>$name</h3>";
		
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

		
		foreach ($data as $product) {
		
	// ************************************** Calculate discount(s) ****************************************
	
	$purchaseid = $product['purchaseid'];
	
	if ($_SESSION['showOrigPrice'] == 1) {
		
		$normalPrice = "({$product['salesPrice']} {$_SESSION['currencyoperator']})";
		
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
		$price = number_format($product['salesPrice'] * $prodDiscount,2);
		
	} else if ($catDiscount != 0 || $catDiscount != '') {
		
		$catCurrentDiscount = (100 - $catDiscount) / 100;
		$price = number_format($product['salesPrice'] * $catCurrentDiscount,2);

	} else if ($discount != 0) {
		
		$dispDiscount = (100 - $discount) / 100;
		$price = number_format($product['salesPrice'] * $dispDiscount,2);
		
	} else if ($usageType == '1') {
		
		if ($product['medDiscount'] > 0) {
			$dispDiscount = (100 - $product['medDiscount']) / 100;
			$price = number_format($product['salesPrice'] * $dispDiscount,2);
		} else if ($medicalDiscountPercentage == 1) {
			$price = number_format(($product['salesPrice']) * $medicalDiscountCalc,2);
		} else {
			$price = number_format(($product['salesPrice']) - $medicalDiscount,2);
		}
		
	} else {
		
		$price = number_format($product['salesPrice'],2);
		
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
	<div class='displaybox'>
	 <div id='imageholder'>
	  <center><img src='images/_$domain/purchases/%s' /></center>
	 </div>
	 <h3>%s</h3>
	 <span class='descprice relative'>
	  <br />$commentRead $commentReadM
	 </span>
	 <input class='specialInput' id='ppgcalc%d' name='sales[%d][ppg]' value='%0.02f' readonly /><span class='eurosign'>{$_SESSION['currencyoperator']}</span><br /><span style='color: yellow;' class='biggerfont'>$normalPrice</span><br />$stockDisplay
	 <div class='clearfloat'></div><br />
	 <center>
  	  <input type='text' lang='nb' class='fourDigit centered calc3 onScreenKeypad' id='grcalc%d' name='sales[%d][grams]' placeholder='#' step='0.01' value='%s' />
  	  <input type='text' lang='nb' class='fourDigit centered calc2 onScreenKeypad' id='eurcalc%d' name='sales[%d][euro]' placeholder='{$_SESSION['currencyoperator']}' step='0.01' value='%s' />
  	  <input type='text' lang='nb' class='fourDigit centered calc4 onScreenKeypad' id='grcalcB%d' name='sales[%d][grams2]' placeholder='#' step='0.01' value='%s' style='background-color: yellow; $displayGift'  /> $giftOption
  	  <input type='hidden' name='sales[%d][name]' value='%s' />
  	  <input type='hidden' name='sales[%d][purchaseid]' value='%d' />
  	  <input type='hidden' name='sales[%d][category]' value='%d' />
  	  <input type='hidden' name='sales[%d][productid]' value='%d' />
  	 </center>
	</div>",
	  $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $product['purchaseid'] . '.' . $product['photoExt'], $product['name'], $i, $i, $price, $i, $i, $savedGrams[$i], $i, $i, $savedEuro[$i], $i, $i, $savedGrams2[$i], $i, $i, $name, $i, $product['purchaseid'], $i, $categoryid, $i, $product['productid']
	  );
	  
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
	    <td class='centered relative' style='padding: 11px 0;'>$commentRead</td>
	    <td class='centered relative' style='padding: 11px 0;'>$commentReadM</td>
	    <td class='right'>%0.02f {$_SESSION['currencyoperator']} <input type='hidden' id='ppgcalc%d' name='sales[%d][ppg]' value='%0.02f' /></td>
	    <td class='right'><span style='color: red;' class='smallerfont'>$normalPrice</span></td>
	    <td class='right'>$stockDisplay</td>
	    <td><input type='text' lang='nb' class='twoDigit centered calc3 onScreenKeypad' style='border: #5aa242 solid 2px; width: 50px; border-radius: 2px; padding: 2px 1px;' id='grcalc%d' name='sales[%d][grams]' placeholder='#' step='0.01' value='%s' /></td>
	    <td><input type='text' lang='nb' class='twoDigit centered calc2 onScreenKeypad' style='border: #5aa242 solid 2px; width: 50px; border-radius: 2px; padding: 2px 1px;' id='eurcalc%d' name='sales[%d][euro]' placeholder='{$_SESSION['currencyoperator']}' step='0.01' value='%s' /></td>
	    <td><input type='text' lang='nb' class='twoDigit centered calc4 onScreenKeypad' id='grcalcB%d' name='sales[%d][grams2]' placeholder='#' step='0.01' value='%s' style='border: #5aa242 solid 2px; width: 50px; border-radius: 2px; padding: 2px 1px; background-color: yellow; $displayGift'  />$giftOption</td>
	   </tr>
  	  <input type='hidden' name='sales[%d][name]' value='%s' />
  	  <input type='hidden' name='sales[%d][purchaseid]' value='%d' />
  	  <input type='hidden' name='sales[%d][category]' value='%d' />
  	  <input type='hidden' name='sales[%d][productid]' value='%d' />
",
	  $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $product['name'], $price, $i, $i, $price, $i, $i, $savedGrams[$i], $i, $i, $savedEuro[$i], $i, $i, $savedGrams2[$i], $i, $i, $name, $i, $product['purchaseid'], $i, $categoryid, $i, $product['productid']
	  );
	  
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
	<div class='displaybox'>
	 <div id='imageholder'>
	  <center><img src='images/_$domain/purchases/%s' /></center>
	 </div>
	 <h3>%s</h3>
	 <span class='descprice relative'>
	  <br />$commentRead $commentReadM
	 </span>
	 <input class='specialInput' id='ppgcalc%d' name='sales[%d][ppg]' value='%0.02f' readonly /><span class='eurosign'>{$_SESSION['currencyoperator']}</span><br /><span style='color: yellow;' class='biggerfont'>$normalPrice</span><br />$stockDisplay
	 <div class='clearfloat'></div><br />
	 <center>
  	  <input type='text' lang='nb' class='fourDigit centered calc onScreenKeypad' id='grcalc%d' name='sales[%d][grams]' placeholder='Gr.' step='0.01' value='%s' />
  	  <input type='text' lang='nb' class='fourDigit centered calc2 onScreenKeypad' id='eurcalc%d' name='sales[%d][euro]' placeholder='{$_SESSION['currencyoperator']}' step='0.01' value='%s' />
  	  <input type='text' lang='nb' class='fourDigit centered calc onScreenKeypad' id='grcalcB%d' name='sales[%d][grams2]' placeholder='Gr.' step='0.01' value='%s' style='background-color: yellow; $displayGift'  /> $giftOption
  	  <input type='hidden' name='sales[%d][name]' value='%s' />
  	  <input type='hidden' name='sales[%d][purchaseid]' value='%d' />
  	  <input type='hidden' name='sales[%d][category]' value='%d' />
  	  <input type='hidden' name='sales[%d][productid]' value='%d' />
  	 </center>
	</div>",
	  $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $product['purchaseid'] . '.' . $product['photoExt'], $product['name'], $i, $i, $price, $i, $i, $savedGrams[$i], $i, $i, $savedEuro[$i], $i, $i, $savedGrams2[$i], $i, $i, $name, $i, $product['purchaseid'], $i, $categoryid, $i, $product['productid']
	  );
	  
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
	    <td class='centered relative' style='padding: 11px 0;'>$commentRead</td>
	    <td class='centered relative' style='padding: 11px 0;'>$commentReadM</td>
	    <td class='right'>%0.02f {$_SESSION['currencyoperator']} <input type='hidden' id='ppgcalc%d' name='sales[%d][ppg]' value='%0.02f' /></td>
	    <td class='right'><span style='color: red;' class='smallerfont'>$normalPrice</span></td>	    
	    <td class='right'>$stockDisplay</td>
	    <td><input type='text' lang='nb' class='twoDigit centered calc onScreenKeypad' style='border: #5aa242 solid 2px; width: 50px; border-radius: 2px; padding: 2px 1px;' id='grcalc%d' name='sales[%d][grams]' placeholder='Gr.' step='0.01' value='%s' /></td>
	    <td><input type='text' lang='nb' class='twoDigit centered calc2 onScreenKeypad' style='border: #5aa242 solid 2px; width: 50px; border-radius: 2px; padding: 2px 1px;' id='eurcalc%d' name='sales[%d][euro]' placeholder='{$_SESSION['currencyoperator']}' step='0.01' value='%s' /></td>
	    <td><input type='text' lang='nb' class='twoDigit centered calc onScreenKeypad' id='grcalcB%d' name='sales[%d][grams2]' placeholder='Gr.' step='0.01' value='%s' style='border: #5aa242 solid 2px; width: 50px; border-radius: 2px; padding: 2px 1px; background-color: yellow; $displayGift'  />$giftOption</td>
	   </tr>
  	  <input type='hidden' name='sales[%d][name]' value='%s' />
  	  <input type='hidden' name='sales[%d][purchaseid]' value='%d' />
  	  <input type='hidden' name='sales[%d][category]' value='%d' />
  	  <input type='hidden' name='sales[%d][productid]' value='%d' />
",
	  $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $product['name'], $price, $i, $i, $price, $i, $i, $savedGrams[$i], $i, $i, $savedEuro[$i], $i, $i, $savedGrams2[$i], $i, $i, $name, $i, $product['purchaseid'], $i, $categoryid, $i, $product['productid']
	  );
	  
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
	 <div id='imageholder'>
	  <center><img src='images/_$domain/purchases/%s' /></center>
	 </div>
	 <h3>%s</h3>
	 <span class='descprice relative'>
	  <br />$commentRead $commentReadM
	 </span>
	 <input class='specialInput' id='ppgcalc%d' name='sales[%d][ppg]' value='%0.02f' readonly /><span class='eurosign'>{$_SESSION['currencyoperator']}</span><br /><span style='color: yellow;' class='biggerfont'>$normalPrice</span><br />$stockDisplay
	 <div class='clearfloat'></div><br />
	 <center>
  	  <input type='text' lang='nb' class='fourDigit centered calc onScreenKeypad' id='grcalc%d' name='sales[%d][grams]' placeholder='Gr.' step='0.01' value='%s' />
  	  <input type='text' lang='nb' class='fourDigit centered calc2 onScreenKeypad' id='eurcalc%d' name='sales[%d][euro]' placeholder='{$_SESSION['currencyoperator']}' step='0.01' value='%s' />
  	  <input type='text' lang='nb' class='fourDigit centered calc calc6 onScreenKeypad' id='grcalcB%d' name='sales[%d][grams2]' placeholder='Gr.' step='0.01' value='%s' style='background-color: yellow; $displayGift'  /> $giftOption<br /><br />
  	  <input type='text' lang='nb' class='fourDigit centered calc5' id='realgrcalc%d' name='sales[%d][realGrams]' placeholder='Real gr.' step='0.01' style='width: 70px;' /> &nbsp;&nbsp;<img src='images/delete.png' width='17' style='cursor: pointer;' id='zero%d' />
  	  <input type='hidden' name='sales[%d][name]' value='%s' />
  	  <input type='hidden' name='sales[%d][purchaseid]' value='%d' />
  	  <input type='hidden' name='sales[%d][category]' value='%d' />
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
		      return [0.00, parseFloat($('#grcalc%d').val()) + {$_SESSION['extractLimit']}];
		  }
		});
	

  }); // end ready
</script>
",
	  $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $product['purchaseid'] . '.' . $product['photoExt'], $product['name'], $i, $i, $price, $i, $i, $savedGrams[$i], $i, $i, $savedEuro[$i], $i, $i, $savedGrams2[$i], $i, $i, $i, $i, $i, $name, $i, $product['purchaseid'], $i, $categoryid, $i, $product['productid'], $i, $i, $i
	  );
	  
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
	    <td class='centered relative' style='padding: 11px 0;'>$commentRead</td>
	    <td class='centered relative' style='padding: 11px 0;'>$commentReadM</td>
	    <td class='right'>%0.02f {$_SESSION['currencyoperator']} <input type='hidden' id='ppgcalc%d' name='sales[%d][ppg]' value='%0.02f' /></td>
	    <td class='right'><span style='color: red;' class='smallerfont'>$normalPrice</span></td>	    
	    <td class='right'>$stockDisplay</td>
	    <td><input type='text' lang='nb' class='twoDigit centered calc onScreenKeypad' style='border: #5aa242 solid 2px; width: 50px; border-radius: 2px; padding: 2px 1px;' id='grcalc%d' name='sales[%d][grams]' placeholder='Gr.' step='0.01' value='%s' /></td>
	    <td><input type='text' lang='nb' class='twoDigit centered calc2 onScreenKeypad' style='border: #5aa242 solid 2px; width: 50px; border-radius: 2px; padding: 2px 1px;' id='eurcalc%d' name='sales[%d][euro]' placeholder='{$_SESSION['currencyoperator']}' step='0.01' value='%s' /></td>
	    <td><input type='text' lang='nb' class='twoDigit centered calc calc6 onScreenKeypad' id='grcalcB%d' name='sales[%d][grams2]' placeholder='Gr.' step='0.01' value='%s' style='border: #5aa242 solid 2px; width: 50px; border-radius: 2px; padding: 2px 1px; background-color: yellow; $displayGift'  />$giftOption</td>
	    <td><input type='text' lang='nb' class='fourDigit centered calc5' id='realgrcalc%d' name='sales[%d][realGrams]' placeholder='Gr. real' step='0.01' style='border: #5aa242 solid 2px; width: 50px; border-radius: 2px; padding: 2px 1px;' /> &nbsp;&nbsp;<img src='images/delete.png' width='17' style='cursor: pointer;' id='zero%d' /></td>
	   </tr>
  	  <input type='hidden' name='sales[%d][name]' value='%s' />
  	  <input type='hidden' name='sales[%d][purchaseid]' value='%d' />
  	  <input type='hidden' name='sales[%d][category]' value='%d' />
  	  <input type='hidden' name='sales[%d][productid]' value='%d' />
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
",
	  $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $product['name'], $price, $i, $i, $price, $i, $i, $savedGrams[$i], $i, $i, $savedEuro[$i], $i, $i, $savedGrams2[$i], $i, $i, $i, $i, $i, $name, $i, $product['purchaseid'], $i, $categoryid, $i, $product['productid'], $i, $i, $i
	  );

	  echo $product_row;
	  
  }
	  
  		} // End gram vs unit cat
	  } // End individual product display, loop back to next product
	} // End real vs fake grams

	  
	  
	  
		
	if ($menuType == 1) {
		echo "</tbody></table></div><div class='leftfloat'><table class='default nonhover'>";
	}
	
}

}
}

}

echo "</center>";

?>
	  <div class="clearFloat"></div>
<input type='hidden' id='currenti' value='0' />		

	
	<div id="memberbox">
<img src="images/minimize.png" class="closeImg pointer" id="minimizeMemberBox" />

<?php

if ($domain == 'cloud') {
	
	echo "<a href='new-picture.php?user_id=$user_id'><img src='images/_$domain/ID/$user_id-front.jpg' class='userPicture' /></a><span class='memberinfo'><a href='#' id='chipClick'><img src='images/rfid.png' onclick='event.preventDefault();' /></a>&nbsp;<a href='profile.php?user_id=$user_id'><span class='membername'>$memberno $first_name $last_name</span></a>";
	
} else {
	
	echo "<a href='new-picture.php?user_id=$user_id'><img src='{$google_root}images/_$domain/members/$user_id.$photoext' class='userPicture' /></a><span class='memberinfo'><a href='#' id='chipClick'><img src='images/rfid.png' onclick='event.preventDefault();' /></a>&nbsp;<a href='profile.php?user_id=$user_id'><span class='membername'>$memberno $first_name $last_name</span></a>";
}

	// Consumption this calendar month
	$selectSales = "SELECT SUM(quantity), SUM(amount) FROM sales WHERE userid = $user_id AND MONTH(saletime) = MONTH(NOW()) AND YEAR(saletime) = YEAR(NOW())";

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
		$quantityToday = $row['SUM(quantity)'];
	
	// Consumption yesterday
	$selectSales = "SELECT SUM(quantity) FROM sales WHERE userid = $user_id AND DATE(saletime) = DATE_ADD(DATE(NOW()), INTERVAL -1 DAY)";

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
		$quantityYday = $row['SUM(quantity)'];
		
		
	echo "<br /><img src='images/flag.png' style='margin-bottom: -1px; margin-right: 1px;' /><strong>&nbsp;$nationality</strong>";
	
	echo "<br /><img src='images/consumed.png' style='margin-bottom: -1px' /><strong> {$expr(number_format($quantityMonth,1))} / {$expr(number_format($mconsumption,0))} g. <span class='yellow'>{$lang['dispensary-today']}: {$expr(number_format($quantityToday,1))} g, {$lang['dispensary-yesterday']}: {$expr(number_format($quantityYday,1))} g </span></strong>";


	if ($userGroup == 5 && $_SESSION['membershipFees'] == 1 && $exento == 0) {  // show Member w/ expiry
	
		$memberExp = date('y-m-d', strtotime($paidUntil));
		$memberExpReadable = date('d M', strtotime($paidUntil));
		$timeNow = date('y-m-d');
		
		if (strtotime($memberExp) == strtotime($timeNow)) {
			echo "<br /><a href='pay-membership.php?user_id=$user_id'><img src='images/exclamation-20.png' class='warningIcon' /> <span class='yellow'>" . $lang['member-expirestoday'] . "</span></a>";
	  	} else if (strtotime($memberExp) < strtotime($timeNow)) {
		  	echo "<br /><a href='pay-membership.php?user_id=$user_id'><img src='images/exclamation-20.png' class='warningIcon' /> <span class='yellow'>" . $lang['member-expiredon'] . ": $memberExpReadable.</span></a>";
		  	
		  	if ($paymentWarning == '1') {
		  	echo "<br /><a href='pay-membership.php?user_id=$user_id'><img src='images/exclamation-22.png' class='warningIcon' /> <img src='images/exclamation-22.png' class='warningIcon' style='margin-left: -16px; margin-right: 1px;' /> <span class='yellow'>" . $lang['member-receivedwarning'] . "</span></a>";
		  	}
		  	
		}
	}
		if ($usageType == '1') {
		echo "<br /><img src='images/medical-20.png' style='margin-bottom: -4px; margin-right: 2px;' /> <span class='yellow'>" . $lang['medicinal-user'] . "</span>";
	}
	
	if (date('m-d') == date('m-d', strtotime($year . "-" . $month . "-" . $day . " 00:00:00"))) {
		echo "<br /><img src='images/cake-22.png' class='warningIcon' style='margin-bottom: -4px;' /> <span class='yellow'>" . $lang['global-birthday'] . "</span>";
	}

	
	// Is the user a high roller?
	$selectHighRollerLimit = "SELECT highRollerWeekly FROM systemsettings";

	try
	{
		$result = $pdo3->prepare("$selectHighRollerLimit");
		$result->execute();
	}
	catch (PDOException $e)
	{
			$error = 'Error fetching user: ' . $e->getMessage();
			echo $error;
			exit();
	}

	$row = $result->fetch();
		$highRollerWeekly = $row['highRollerWeekly'];

	if ($totalAmountPerWeek >= $highRollerWeekly) {
		echo "<br /><img src='images/hi-roller.png' class='warningIcon' style='margin-bottom: -4px;' /> <span class='yellow'>High roller</span>";
	}
	
	// Query to look up user debt
	$selectSales = "SELECT SUM(amount), SUM(amountpaid) FROM sales WHERE userid = $user_id";

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

	$row3 = $result->fetch();
		$amtTot = $row3['SUM(amount)'];
		$amtPaid = $row3['SUM(amountpaid)'];
		$amtOwed = $amtTot - $amtPaid;

	 if ($amtOwed > 0.1 && $userGroup > 2) {
		 echo "<br /><a href='settle-debt-2.php?user_id=$user_id'><img src='images/exclamation-22.png' class='warningIcon' style='margin-bottom: -4px; margin-right: 2px;' /> <span class='yellow'>Has debt</span></a>";
	 }

	
	
	if ($quantityMonth >= $mconsumption) {
		echo "<br /><img src='images/exclamation-22.png' class='warningIcon' style='margin-bottom: -4px;' /> <span class='yellow'>" . $lang['member-conslimitexc'] . "(+$consumptionDelta g)</span>";
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
  	     <td style='text-align: left;'><span style="font-size: 20px;">&nbsp;<?php echo $_SESSION['currencyoperator'] ?></span></td>
  	     <?php if ($credit < 0) { ?>
  	     <td><input type='number' lang='nb' class="specialInput" style='color: red;' id='credit' name='credit' value='0' readonly /> <?php echo $_SESSION['currencyoperator'] ?></td>
  	     <td><input type='number' lang='nb' class='specialInput' style='color: red;' id='newcredit' name='newcredit' value='0' readonly /> <?php echo $_SESSION['currencyoperator'] ?></td>
  	     
  	     <?php } else { ?>
  	     <td><input type='number' lang='nb' class="specialInput" id='credit' name='credit' value='<?php echo $credit; ?>' readonly /> <?php echo $_SESSION['currencyoperator'] ?></td>
  	     <td><input type='number' lang='nb' class='specialInput' id='newcredit' name='newcredit' value='<?php echo $credit; ?>' readonly /> <?php echo $_SESSION['currencyoperator'] ?></td>
  	     <?php } ?>

  	    </tr>
  	    
<?php if ($_SESSION['dispDonate'] == 1) { ?>

  	    <tr class='donateField' style='display: none;'>
  	     <td></td>
  	     <td></td>
  	     <td class='right'><?php echo $lang['donate']; ?>: <input type='number' lang='nb' class='twoDigit donateOrNot' id='newDonation' name='newDonation' /> <?php echo $_SESSION['currencyoperator'] ?></td>
  	     <td></td>
  	    </tr>
<?php } ?>
  	   </table>
  	   
<!-- With direct dispensing enabled, show payment options -->
<?php if ($_SESSION['creditOrDirect'] == 0) { ?>

  	   <center><span style='font-size: 17px; display: inline-block; margin-top: 10px; text-transform: uppercase;'><?php echo $lang['paid-by']; ?>:</span><br />
        <input type="radio" id="pmt1" name="pmtType" value='1' />
        <label for="pmt1"><span class='full'><?php echo $lang['cash']; ?></span></label>
        
<?php if ($_SESSION['bankPayments'] == 1) { ?>
        <input type="radio" id="pmt2" name="pmtType" value='2' />
        <label for="pmt2"><span class='full' id="pmt2trigger"><?php echo $lang['card']; ?></span></label>
<?php } ?>

        <input type="radio" id="pmt3" name="pmtType" value='3' />
        <label for="pmt3"><span class='full'><?php echo $lang['global-credit']; ?></span></label>
  	   <table id='changeTable' style='display: none;'>
  	    <tr>
  	     <td>&nbsp;&nbsp;Pagado:</td>
  	     <td>&nbsp;&nbsp;Cambio:</td>
  	    </tr>
  	    <tr>
  	     <td><input type='number' lang='nb' style='width: 50px; border: 2px solid black; margin-right: 30px;' id='paid' placeholder='<?php echo $_SESSION['currencyoperator'] ?>' /> </td>
  	     <td><input type='number' lang='nb' style='width: 50px; border: 2px solid black; margin: 0;' id='change' placeholder='<?php echo $_SESSION['currencyoperator'] ?>' readonly  /> </td>
  	    </tr>
  	   </table>
  	   </center>

<?php } ?>

<!-- With additional discounts enabled, show discounts -->
<?php if ($_SESSION['checkoutDiscount'] == 1) { ?>
  	   <center><span style='font-size: 17px; display: inline-block; margin-top: 10px; text-transform: uppercase;'><?php echo $lang['member-discount']; ?> <span id='totDiscount'>(a%)</span>:</span><br />
        <input type="checkbox" id="fee0" name="inDiscount" value='5' />
        <label for="fee0"><span class='full2'>5%</span></label>
        
        <input type="checkbox" id="fee1" name="inDiscount" value='10' />
        <label for="fee1"><span class='full2'>10%</span></label>
        
        <input type="checkbox" id="fee2" name="inDiscount" value='20' />
        <label for="fee2"><span class='full2'>20%</span></label>

        <input type="checkbox" id="fee3" name="inDiscount" value='30' />
        <label for="fee3"><span class='full2'>30%</span></label>
        <br />
        <span style='font-size: 17px; display: inline-block; margin-top: 0; text-transform: uppercase;'><?php echo $lang['member-orcaps'] . " " . $lang['member-discount']; ?> <?php echo $_SESSION['currencyoperator'] ?>: <input type='number' name='eurdiscount' id='eurdiscount' class='twoDigit' /></span>
  	   </center>
<?php } else { ?>
<input type='hidden' name='eurdiscount' id='eurdiscount' class='twoDigit' value='0' />
 <?php } ?>
 	   
       <center>
<?php
	if ($_SESSION['iPadReaders'] == 0) {
		
		// Chip
		if ($_SESSION['dispsig'] == 0) {
			echo <<<EOD
<input type="text" class="donateOrNot" id="cardscan" name="cardid" maxlength="30" placeholder="{$lang['global-scantoconfirm']}" />
EOD;
		// Topaz
		} else if ($_SESSION['dispsig'] == 1) {
			echo <<<EOD
<canvas id="cnv" name="cnv" width="500" height="100" onclick="javascript:onSign()" style="border: 2px solid white;"></canvas><br />
<input id="button2" name="DoneBtn" type="button" value="Finalizar" onclick="javascript:onDone()" style="margin-left: 10px; width: 80px;" /><br />
EOD;
			
		}
	}
?>

   <a href="#" id="openDispenseDate" style="vertical-align: bottom;" class="yellow">+ Fecha manual</a><br />
   <span id='customDispenseDate' style='display: none;'>
 <input type="text" lang="nb" name="owndate" id="datepicker" class="fiveDigit" placeholder="<?php echo $lang['pur-date']; ?>" /> @
 <input type="number" lang="nb" name="hour" id="hour" class="oneDigit" maxlength="2" placeholder="hh" /> :
 <input type="number" lang="nb" name="minute" id="minute" class="oneDigit" maxlength="2" placeholder="mm" />
   </span>

       <a href="#" id="openComment" style="vertical-align: bottom;">+ <?php echo $lang['global-comment']; ?></a>
       <textarea class="hiddenComment" name="adminComment" placeholder="<?php echo $lang['global-comment']; ?>?"></textarea>
  	   
<br /><button type="submit" class='oneClick' name='oneClick' id='submitButton'><?php echo $lang['global-save']; ?></button><br />
 <!--<a href="?saveCart&user_id=<?php echo $user_id; ?>">Save</a>--><a href="#" onclick="event.preventDefault(); document.getElementById('registerForm').reset();"><img src="images/clear-cart.png" /></a>&nbsp;&nbsp;&nbsp;&nbsp;<a href="barcode-dispense.php?user_id=<?php echo $user_id; ?>"><img src="images/barcode2.png"  width="30" /></a></center>
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
  	     <td><span style="font-size: 30px;"> <?php echo $_SESSION['currencyoperator'] ?></span></td>
  	    </tr>
<?php if ($_SESSION['creditOrDirect'] == 1) { ?>
  	    <tr>
  	     <td><!--<img src='images/wallet.png' />--><input type='number' lang='nb' class='fourDigit specialInput' id='newcredit2' name='newcredit2' value="0" readonly /></td>
  	     <td><span style="font-size: 30px;"> <?php echo $_SESSION['currencyoperator'] ?></span></td>
  	    </tr>
<?php } ?>
  	   </table>
</div>

<?php if ($_SESSION['dispsig'] == 1) { ?>
<INPUT TYPE=HIDDEN NAME="bioSigData">
<INPUT TYPE=HIDDEN NAME="sigImgData">
<INPUT TYPE=HIDDEN NAME="sigStringData" />
<INPUT TYPE=HIDDEN NAME="sigImageData" />
<?php } ?>



</form>

<?php

}

	displayFooter();
