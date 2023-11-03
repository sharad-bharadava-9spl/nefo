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
	
	$domain = $_SESSION['domain'];
	
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
			$newCredit,
			$registertime,
			$userid
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
				
			// On success: redirect.
			$_SESSION['successMessage'] = $lang['global-added'] . " " . $amount . $lang['donation-addedsuccessfully'] . $newCredit . "&euro;";
			
			header("Location: new-dispense-2.php?user_id=$userid");
			exit();
		
	}
	
	// Did this page re-submit with a form? If so, check & store details
	if (isset($_POST['dispense']) && $_POST['oneClick'] == 1) {
		
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
			$newCreditCalc,
			$user_id
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
		  	$query = sprintf("INSERT INTO sales (saletime, userid, amount, amountPaid, quantity, units, adminComment, settled, settledTo, creditBefore, creditAfter, expiry, realQuantity, discount, direct) VALUES ('%s', '%d', '%f', '%f', '%f', '%f', '%s', '%s', '%d', '%f', '%f', '%s', '%f', '%f', '%d');",
		  	$saletime, $user_id, $eurcalcTOT, $eurcalcTOT, $gramsTOT, $unitsTOT, $adminComment, $saletime, '1', $creditLookup, $newCreditCalc, $paidUntil, $realQuantity, $totDiscountInput, $pmtType);
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
		  	$query = sprintf("INSERT INTO sales (saletime, userid, amount, amountPaid, quantity, units, adminComment, settled, settledTo, expiry, realQuantity, discount, direct) VALUES ('%s', '%d', '%f', '%f', '%f', '%f', '%s', '%s', '%d', '%s', '%f', '%f', '%d');",
		  	$saletime, $user_id, $eurcalcTOT, $eurcalcTOT, $gramsTOT, $unitsTOT, $adminComment, $saletime, '1', $paidUntil, $realQuantity, $totDiscountInput, $pmtType);
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
	} else if (isset($_POST['user_id'])) {
		$user_id = $_POST['user_id'];
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
		
	if ($userGroup > 6) {
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
	$registeredSince = $row['registeredSince'];
	
	if ($registeredSince < '2014-11-01') {
		$memberSince = "'2014-11-01'";
	} else {
		$memberSince = 'registeredSince';
	}
	
	
		// Look up user details for showing profile on the Sales page
		$userDetails2 = "SELECT memberno, paidUntil, userGroup, first_name, last_name, datediff(curdate(),$memberSince) AS daysMember, paymentWarning, paymentWarningDate, mconsumption, day, month, year, credit, creditEligible, discount, photoext, cardid, nationality, exento FROM users WHERE user_id = '{$user_id}'";
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
		  ignore: [],
		  rules: {
			  cardid: {
				required: {
                    depends: function (element) {
                        return $("#focus").is(":blank");
                    }
                }
              },
			  eurcalcTOT: {
				  range: [0,"{$_SESSION['dispenseLimit']}"]
			  },
			  pmtType: {
				required: {
                    depends: function (element) {
                        return $("#focus").is(":blank");
                    }
                },
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
		  ignore: [],
		  rules: {
			  cardid: {
				required: {
                    depends: function (element) {
                        return $("#focus").is(":blank");
                    }
                }
              },
			  eurcalcTOT: {
				  range: [0,"{$_SESSION['dispenseLimit']}"]
			  },
			  pmtType: {
				required: {
                    depends: function (element) {
                        return $("#focus").is(":blank");
                    }
                },
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
<form id="registerForm" action="?" method="POST">




<div class="clearfloat"></div>


<?php

	if ($_GET['firstscan'] == 'yes') {
		
		
		$unit = 1;
		
		$categoryid = $_GET['category'];
		$purchaseid = $_GET['purchaseid'];
		
		// Retrieve Menu type and discount for medical users
		$medicalDiscount = $_SESSION['medicalDiscount'];
		$medicalDiscountPercentage = $_SESSION['medicalDiscountPercentage'];
		
		if ($medicalDiscountPercentage == 1) {
			$medicalDiscountCalc = 1 - ($medicalDiscount / 100);
		}		
		
		if ($_SESSION['dispensaryGift'] == 1) {
			$giftOption = "<img src='images/free-green.png' id='free%d' style='margin-bottom: 0; margin-left: 5px;' />";
		} else {
			$giftOption = "<input type='hidden' value='%d' />";
		}
		
		if ($categoryid == 1) {
			
			$selectFlower = "SELECT g.flowerid, g.name, g.breed2, g.flowertype, g.sativaPercentage, g.description, g.medicaldescription, p.productid, p.purchaseid, p.salesPrice, p.realQuantity, p.growType, p.photoExt, p.medDiscount FROM flower g, purchases p WHERE p.category = 1 AND p.productid = g.flowerid AND p.purchaseid = $purchaseid ORDER BY p.salesPrice ASC;";
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
				
		$flower = $resultFlower->fetch();
			
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
			
			echo "<table class='default nonhover'><tr><th>Categoría</th><th>Producto</th><th>Precio</th><th class='centered'>Gramos</th><th class='centered'>&euro;</th><th class='centered'>Real</th></tr>";
			
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
	    <td class='left'>Flor</td>
	    <td class='left'>%s</td>
		<td class='right'>%0.02f &euro; <input type='hidden' id='ppgcalc%d' name='sales[%d][ppg]' value='%0.01f' /></td>
	    <td><input type='text' lang='nb' class='twoDigit centered calc onScreenKeypad' style='border: #5aa242 solid 2px; width: 50px; border-radius: 2px; padding: 2px 1px;' id='grcalc%d' name='sales[%d][grams]' placeholder='Gr.' step='0.01' value='%s' /></td>
	    <td><input type='text' lang='nb' class='twoDigit centered calc2 onScreenKeypad' style='border: #5aa242 solid 2px; width: 50px; border-radius: 2px; padding: 2px 1px;' id='eurcalc%d' name='sales[%d][euro]' placeholder='&euro;' step='0.01' value='%s' />
	    <td><input type='text' lang='nb' class='fourDigit centered calc5' id='realgrcalc%d' name='sales[%d][realGrams]' placeholder='Real gr.' step='0.01' style='border: #5aa242 solid 2px; width: 50px; border-radius: 2px; padding: 2px 1px;' /></td>
  	  <input type='hidden' name='sales[%d][name]' value='%s' />
  	  <input type='hidden' name='sales[%d][purchaseid]' value='%d' />
  	  <input type='hidden' name='sales[%d][category]' value='1' />
  	  <input type='hidden' name='sales[%d][productid]' value='%d' /></td>
	   </tr>
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
	  $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $name, $price, $i, $i, $price, $i, $i, '', $i, $i, '', $i, $i, $i, $name, $i, $flower['purchaseid'], $i, $i, $flower['flowerid'], $i, $i, $i
	  );
	   
  	  		echo $flower_row;

		} else if ($categoryid == 2) {
			
		  	$selectExtract = "SELECT h.extractid, h.name, h.extract, h.description, h.medicaldescription, p.productid, p.purchaseid, p.salesPrice, p.realQuantity, p.photoExt, p.medDiscount FROM extract h, purchases p WHERE p.category = 2 AND p.productid = h.extractid  AND p.purchaseid = $purchaseid ORDER BY p.salesPrice ASC;";
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
	
		$extract = $result->fetch();
			
				$name = $extract['name'];
			
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
			
			echo "<table class='default nonhover'><tr><th>Categoría</th><th>Producto</th><th>Precio</th><th class='centered'>Gramos</th><th class='centered'>&euro;</th><th class='centered'>Real</th></tr>";
			
			
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
	    <td class='left'>Extracto</td>
	    <td class='left'>%s</td>
		<td class='right'>%0.02f &euro; <input type='hidden' id='ppgcalc%d' name='sales[%d][ppg]' value='%0.01f' /></td>
	    <td><input type='text' lang='nb' class='twoDigit centered calc onScreenKeypad' style='border: #5aa242 solid 2px; width: 50px; border-radius: 2px; padding: 2px 1px;' id='grcalc%d' name='sales[%d][grams]' placeholder='Gr.' step='0.01' value='%s' /></td>
	    <td><input type='text' lang='nb' class='twoDigit centered calc2 onScreenKeypad' style='border: #5aa242 solid 2px; width: 50px; border-radius: 2px; padding: 2px 1px;' id='eurcalc%d' name='sales[%d][euro]' placeholder='&euro;' step='0.01' value='%s' />
	    <td><input type='text' lang='nb' class='fourDigit centered calc5' id='realgrcalc%d' name='sales[%d][realGrams]' placeholder='Real gr.' step='0.01' style='border: #5aa242 solid 2px; width: 50px; border-radius: 2px; padding: 2px 1px;' /></td>
  	  <input type='hidden' name='sales[%d][name]' value='%s' />
  	  <input type='hidden' name='sales[%d][purchaseid]' value='%d' />
  	  <input type='hidden' name='sales[%d][category]' value='2' />
  	  <input type='hidden' name='sales[%d][productid]' value='%d' /></td>
	   </tr>
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
	  $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $name, $price, $i, $i, $price, $i, $i, '', $i, $i, '', $i, $i, $i, $name, $i, $extract['purchaseid'], $i, $i, $extract['extractid'], $i, $i, $i
	  );
	   
  	  		echo $flower_row;
  	  		
  	  							
			// ************************************** Calculate discount(s) ****************************************
			
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
			
		} else {
			
		
			// For each cat, look up products
		  	$selectProduct = "SELECT pr.productid, pr.name, pr.description, pr.medicaldescription, p.purchaseid, p.salesPrice, p.realQuantity, p.photoExt, p.medDiscount FROM products pr, purchases p WHERE p.category = $categoryid AND p.productid = pr.productid  AND p.purchaseid = $purchaseid ORDER BY p.salesPrice ASC;";
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
	
		$product = $result->fetch();
			
				$name = $product['name'];

		
		  	// Query to look up categories
			$selectCats = "SELECT id, name, type from categories WHERE id = $categoryid";
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
	
		$category = $result->fetch();
				$categoryName = $category['name'];
				$type = $category['type'];
				
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
		
					
					
			// ************************************** Calculate discount(s) ****************************************
			
			if ($_SESSION['showOrigPrice'] == 1) {
				
				$normalPrice = "({$product['salesPrice']} &euro;)";
				
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
			
			
			// Unit cat first	
			if ($type == 0) {
					  
			echo "<table class='default nonhover'><tr><th>Categoría</th><th>Producto</th><th>Precio</th><th class='centered'>Unidades</th><th class='centered'>&euro;</th><th class='centered'>Real</th></tr>";
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
	    <td class='left'>$categoryName</td>
	    <td class='left'>%s</td>
		<td class='right'>%0.02f &euro; <input type='hidden' id='ppgcalc%d' name='sales[%d][ppg]' value='%0.01f' /></td>
	    <td><input type='text' lang='nb' class='twoDigit centered calc3 onScreenKeypad' style='border: #5aa242 solid 2px; width: 50px; border-radius: 2px; padding: 2px 1px;' id='grcalc%d' name='sales[%d][grams]' placeholder='Gr.' step='0.01' value='%s' /></td>
	    <td><input type='text' lang='nb' class='twoDigit centered calc2 onScreenKeypad' style='border: #5aa242 solid 2px; width: 50px; border-radius: 2px; padding: 2px 1px;' id='eurcalc%d' name='sales[%d][euro]' placeholder='&euro;' step='0.01' value='%s' />
  	  <input type='hidden' name='sales[%d][name]' value='%s' />
  	  <input type='hidden' name='sales[%d][purchaseid]' value='%d' />
  	  <input type='hidden' name='sales[%d][category]' value='$categoryid' />
  	  <input type='hidden' name='sales[%d][productid]' value='%d' /></td>
	   </tr>
",
	  $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $name, $price, $i, $i, $price, $i, $i, '', $i, $i, '', $i, $name, $i, $product['purchaseid'], $i, $i, $product['productid']
	  );
	   
  	  		echo $flower_row;
	  
  			} else { // Now Gram cat
		
			echo "<table class='default nonhover'><tr><th>Categoría</th><th>Producto</th><th>Precio</th><th class='centered'>Gramos</th><th class='centered'>&euro;</th><th class='centered'>Real</th></tr>";
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
	    <td class='left'>$categoryName</td>
	    <td class='left'>%s</td>
		<td class='right'>%0.02f &euro; <input type='hidden' id='ppgcalc%d' name='sales[%d][ppg]' value='%0.01f' /></td>
	    <td><input type='text' lang='nb' class='twoDigit centered calc onScreenKeypad' style='border: #5aa242 solid 2px; width: 50px; border-radius: 2px; padding: 2px 1px;' id='grcalc%d' name='sales[%d][grams]' placeholder='Gr.' step='0.01' value='%s' /></td>
	    <td><input type='text' lang='nb' class='twoDigit centered calc2 onScreenKeypad' style='border: #5aa242 solid 2px; width: 50px; border-radius: 2px; padding: 2px 1px;' id='eurcalc%d' name='sales[%d][euro]' placeholder='&euro;' step='0.01' value='%s' />
	    <td><input type='text' lang='nb' class='fourDigit centered calc5' id='realgrcalc%d' name='sales[%d][realGrams]' placeholder='Real gr.' step='0.01' style='border: #5aa242 solid 2px; width: 50px; border-radius: 2px; padding: 2px 1px;' /></td>
  	  <input type='hidden' name='sales[%d][name]' value='%s' />
  	  <input type='hidden' name='sales[%d][purchaseid]' value='%d' />
  	  <input type='hidden' name='sales[%d][category]' value='$categoryid' />
  	  <input type='hidden' name='sales[%d][productid]' value='%d' /></td>
	   </tr>
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
	  $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $name, $price, $i, $i, $price, $i, $i, '', $i, $i, '', $i, $i, $i, $name, $i, $product['purchaseid'], $i, $i, $product['productid'], $i, $i, $i
	  );
	   
  	  		echo $flower_row;
	  
	  
  			} // End gram vs unit cat
	
		} // End 'other' cat
		
	} else { // End firstScan
	
		echo "<table class='default nonhover'><tr><th>Categoría</th><th>Producto</th><th>Precio</th><th class='centered'>Unidades</th><th class='centered'>&euro;</th><th class='centered'>Real</th></tr>";
			
		$barcode = $_POST['barcode'];
		
		$userDetails = "SELECT purchaseid, barCode, category FROM purchases WHERE barCode = '$barcode'";
		$userDetails = "SELECT purchaseid, barCode, category FROM purchases WHERE barCode = '$barcode'";
		try
		{
			$result = $pdo3->prepare("$userDetails");
			$result->execute();
			$data = $result->fetchAll();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
			
			
			
			
			
	
			
		if (!$data || $barcode == '') {
			
			echo "<div id='scriptMsg'><div style='color: red;' class='error'>Este codigo de barra no esta registrado!</div></div>";
			$notExist = 'true';
			
		} else {
	
			$row = $data[0];
				$purchaseidNew = $row['purchaseid'];
			
		}
		
		
			foreach($_POST['sales'] as $key => $sale) {
				
				
				$name = $sale['name'];
				$categoryid = $sale['category'];
				$productid = $sale['productid'];
				$purchaseid = $sale['purchaseid'];
				$grams = $sale['grams'];
				$grams2 = $sale['grams2'];
				$realGrams = $sale['realGrams'];
				$euro = $sale['euro'];
				
				
				if ($grams == 0) {
					// Remove from array
					unset($_POST['sales'][$key]);
				} else {
				
				if ($purchaseid == $purchaseidNew) {
					$alreadySet = 1;
				}
				
				
		if ($categoryid == 1) {
			
			$selectProduct = "SELECT g.flowerid, g.name, g.breed2, g.flowertype, g.sativaPercentage, g.description, g.medicaldescription, p.productid, p.purchaseid, p.salesPrice, p.realQuantity, p.growType, p.photoExt, p.medDiscount FROM flower g, purchases p WHERE p.category = 1 AND p.productid = g.flowerid AND p.purchaseid = $purchaseid ORDER BY p.salesPrice ASC;";
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
	
				
		} else if ($categoryid == 2) {
				
		  	$selectProduct = "SELECT h.extractid, h.name, h.extract, h.description, h.medicaldescription, p.productid, p.purchaseid, p.salesPrice, p.realQuantity, p.photoExt, p.medDiscount FROM extract h, purchases p WHERE p.category = 2 AND p.productid = h.extractid  AND p.purchaseid = $purchaseid ORDER BY p.salesPrice ASC;";
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
				
		} else {
			
			// For each cat, look up products
		  	$selectProduct = "SELECT pr.productid, pr.name, pr.description, pr.medicaldescription, p.purchaseid, p.salesPrice, p.realQuantity, p.photoExt, p.medDiscount FROM products pr, purchases p WHERE p.category = $categoryid AND p.productid = pr.productid  AND p.purchaseid = $purchaseid ORDER BY p.salesPrice ASC;";
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
				
		}
		

				
		$product = $result->fetch();
			$name = $product['name'];
			$productid = $product['productid'];
			$photoExt = $product['photoExt'];
			$purchaseid = $product['purchaseid'];
			
		if ($categoryid == 1) {
			
			$categoryName = 'Flor';
			
		} else if ($categoryid == 2) {
			
			$categoryName = 'Extracto';
			
		} else {
			
		  	// Query to look up categories
			$selectCats = "SELECT id, name, type from categories WHERE id = $categoryid";
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
	
		$category = $result->fetch();
				$categoryName = $category['name'];
				$type = $category['type'];
				
		}

			
			// Look up category discount
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

		
			// ************************************** Calculate discount(s) ****************************************
			
			if ($_SESSION['showOrigPriceBar'] == 1) {
				
				$normalPrice = "(" . ($product['salesPrice']) . "&euro;)";
				
			} else {
			
				$normalPrice = "";
				
			}
						
			// Individual purchase discount
			$selectPurchaseDiscount = "SELECT discount, fijo FROM b_inddiscounts WHERE user_id = $user_id AND purchaseid = $purchaseid";
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
				$price = number_format(($product['salesPrice'] * $prodDiscount),2);
				
			} else if ($catDiscount != 0 || $catDiscount != '') {
				
				$catCurrentDiscount = (100 - $catDiscount) / 100;
				$price = number_format(($product['salesPrice'] * $catCurrentDiscount),2);
		
			} else if ($discount != 0) {
				
				$dispDiscount = (100 - $discount) / 100;
				$price = number_format(($product['salesPrice'] * $dispDiscount),2);
				
			} else {
				
				$price = number_format($product['salesPrice'],2);
				
			}

			
			if ($categoryid > 2 && $type == 0) {
				
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
	    <td class='left'>$categoryName</td>
	    <td class='left'>%s</td>
		<td class='right'>%0.02f &euro; <input type='hidden' id='ppgcalc%d' name='sales[%d][ppg]' value='%0.01f' /></td>
	    <td><input type='text' lang='nb' class='twoDigit centered calc3 onScreenKeypad' style='border: #5aa242 solid 2px; width: 50px; border-radius: 2px; padding: 2px 1px;' id='grcalc%d' name='sales[%d][grams]' placeholder='Gr.' step='0.01' value='%s' /></td>
	    <td><input type='text' lang='nb' class='twoDigit centered calc2 onScreenKeypad' style='border: #5aa242 solid 2px; width: 50px; border-radius: 2px; padding: 2px 1px;' id='eurcalc%d' name='sales[%d][euro]' placeholder='&euro;' step='0.01' value='%s' />
  	  <input type='hidden' name='sales[%d][name]' value='%s' />
  	  <input type='hidden' name='sales[%d][purchaseid]' value='%d' />
  	  <input type='hidden' name='sales[%d][category]' value='$categoryid' />
  	  <input type='hidden' name='sales[%d][productid]' value='%d' /></td>
	   </tr>
",
	  $key, $key, $key, $key, $key, $key, $key, $key, $key, $key, $key, $key, $key, $key, $key, $name, $price, $key, $key, $price, $key, $key, $grams, $key, $key, $euro, $key, $name, $key, $product['purchaseid'], $key, $key, $product['productid']
	  );
	  
  		} else {
	  		
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
	    <td class='left'>$categoryName</td>
	    <td class='left'>%s</td>
		<td class='right'>%0.02f &euro; <input type='hidden' id='ppgcalc%d' name='sales[%d][ppg]' value='%0.01f' /></td>
	    <td><input type='text' lang='nb' class='twoDigit centered calc onScreenKeypad' style='border: #5aa242 solid 2px; width: 50px; border-radius: 2px; padding: 2px 1px;' id='grcalc%d' name='sales[%d][grams]' placeholder='Gr.' step='0.01' value='%s' /></td>
	    <td><input type='text' lang='nb' class='twoDigit centered calc2 onScreenKeypad' style='border: #5aa242 solid 2px; width: 50px; border-radius: 2px; padding: 2px 1px;' id='eurcalc%d' name='sales[%d][euro]' placeholder='&euro;' step='0.01' value='%s' />
	    <td><input type='text' lang='nb' class='fourDigit centered calc5' id='realgrcalc%d' name='sales[%d][realGrams]' placeholder='Real gr.' step='0.01' style='border: #5aa242 solid 2px; width: 50px; border-radius: 2px; padding: 2px 1px;' value='%s'/></td>
  	  <input type='hidden' name='sales[%d][name]' value='%s' />
  	  <input type='hidden' name='sales[%d][purchaseid]' value='%d' />
  	  <input type='hidden' name='sales[%d][category]' value='$categoryid' />
  	  <input type='hidden' name='sales[%d][productid]' value='%d' /></td>
	   </tr>
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
	  $key, $key, $key, $key, $key, $key, $key, $key, $key, $key, $key, $key, $key, $key, $key, $key, $key, $key, $key, $key, $key, $key, $key, $name, $price, $key, $key, $price, $key, $key, $grams, $key, $key, $euro, $key, $key, $realGrams, $key, $name, $key, $product['purchaseid'], $key, $key, $product['productid'], $key, $key, $key
	  );
	   
  		}
	   
  	  		echo $flower_row;
				
				
  		}
	}
			
	
	
	// Add newly scanned product
	if ($alreadySet != 1 && $notExist != 'true') {
		
		$barcode = $_POST['barcode'];
		
		$userDetails = "SELECT purchaseid, barCode, category FROM purchases WHERE barCode = '$barcode'";
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
			$purchaseid = $row['purchaseid'];
			$category = $row['category'];
			$categoryid = $category;
		

				
	
		$i = $key + 1;
		
		if ($categoryid == 1) {
			
			$selectProduct = "SELECT g.flowerid, g.name, g.breed2, g.flowertype, g.sativaPercentage, g.description, g.medicaldescription, p.productid, p.purchaseid, p.salesPrice, p.realQuantity, p.growType, p.photoExt, p.medDiscount FROM flower g, purchases p WHERE p.category = 1 AND p.productid = g.flowerid AND p.purchaseid = $purchaseid ORDER BY p.salesPrice ASC;";
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
	
				
		} else if ($categoryid == 2) {
				
		  	$selectProduct = "SELECT h.extractid, h.name, h.extract, h.description, h.medicaldescription, p.productid, p.purchaseid, p.salesPrice, p.realQuantity, p.photoExt, p.medDiscount FROM extract h, purchases p WHERE p.category = 2 AND p.productid = h.extractid  AND p.purchaseid = $purchaseid ORDER BY p.salesPrice ASC;";
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
	
				
		} else {
			
			// For each cat, look up products
		  	$selectProduct = "SELECT pr.productid, pr.name, pr.description, pr.medicaldescription, p.purchaseid, p.salesPrice, p.realQuantity, p.photoExt, p.medDiscount FROM products pr, purchases p WHERE p.category = $categoryid AND p.productid = pr.productid  AND p.purchaseid = $purchaseid ORDER BY p.salesPrice ASC;";
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
	
				
		}
		
		$product = $result->fetch();
			$name = $product['name'];
			$productid = $product['productid'];
			$photoExt = $product['photoExt'];
			$purchaseid = $product['purchaseid'];
			
		if ($categoryid == 1) {
			
			$categoryName = 'Flor';
			
		} else if ($categoryid == 2) {
			
			$categoryName = 'Extracto';
			
		} else {
			
		  	// Query to look up categories
			$selectCats = "SELECT id, name, type from categories WHERE id = $categoryid";
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
	
		$category = $result->fetch();
				$categoryName = $category['name'];
				$type = $category['type'];
				
		}
		
			// Look up category discount
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

		
			// ************************************** Calculate discount(s) ****************************************
			
			if ($_SESSION['showOrigPriceBar'] == 1) {
				
				$normalPrice = "(" . ($product['salesPrice']) . "&euro;)";
				
			} else {
			
				$normalPrice = "";
				
			}
						
			// Individual purchase discount
			$selectPurchaseDiscount = "SELECT discount, fijo FROM b_inddiscounts WHERE user_id = $user_id AND purchaseid = $purchaseid";
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
				$price = number_format(($product['salesPrice'] * $prodDiscount),2);
				
			} else if ($catDiscount != 0 || $catDiscount != '') {
				
				$catCurrentDiscount = (100 - $catDiscount) / 100;
				$price = number_format(($product['salesPrice'] * $catCurrentDiscount),2);
		
			} else if ($discount != 0) {
				
				$dispDiscount = (100 - $discount) / 100;
				$price = number_format(($product['salesPrice'] * $dispDiscount),2);
				
			} else {
				
				$price = number_format($product['salesPrice'],2);
				
			}

			if ($categoryid > 2 && $type == 0) {
				
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
	    <td class='left'>$categoryName</td>
	    <td class='left'>%s</td>
		<td class='right'>%0.02f &euro; <input type='hidden' id='ppgcalc%d' name='sales[%d][ppg]' value='%0.01f' /></td>
	    <td><input type='text' lang='nb' class='twoDigit centered calc3 onScreenKeypad' style='border: #5aa242 solid 2px; width: 50px; border-radius: 2px; padding: 2px 1px;' id='grcalc%d' name='sales[%d][grams]' placeholder='U.' step='0.01' value='%s' /></td>
	    <td><input type='text' lang='nb' class='twoDigit centered calc2 onScreenKeypad' style='border: #5aa242 solid 2px; width: 50px; border-radius: 2px; padding: 2px 1px;' id='eurcalc%d' name='sales[%d][euro]' placeholder='&euro;' step='0.01' value='%s' />
  	  <input type='hidden' name='sales[%d][name]' value='%s' />
  	  <input type='hidden' name='sales[%d][purchaseid]' value='%d' />
  	  <input type='hidden' name='sales[%d][category]' value='$categoryid' />
  	  <input type='hidden' name='sales[%d][productid]' value='%d' /></td>
	   </tr>
",
	  $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $name, $price, $i, $i, $price, $i, $i, '', $i, $i, '', $i, $name, $i, $product['purchaseid'], $i, $i, $product['productid']
	  );
	  
  		} else {
	  		
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
	    <td class='left'>$categoryName</td>
	    <td class='left'>%s</td>
		<td class='right'>%0.02f &euro; <input type='hidden' id='ppgcalc%d' name='sales[%d][ppg]' value='%0.01f' /></td>
	    <td><input type='text' lang='nb' class='twoDigit centered calc onScreenKeypad' style='border: #5aa242 solid 2px; width: 50px; border-radius: 2px; padding: 2px 1px;' id='grcalc%d' name='sales[%d][grams]' placeholder='Gr.' step='0.01' value='%s' /></td>
	    <td><input type='text' lang='nb' class='twoDigit centered calc2 onScreenKeypad' style='border: #5aa242 solid 2px; width: 50px; border-radius: 2px; padding: 2px 1px;' id='eurcalc%d' name='sales[%d][euro]' placeholder='&euro;' step='0.01' value='%s' />
	    <td><input type='text' lang='nb' class='fourDigit centered calc5' id='realgrcalc%d' name='sales[%d][realGrams]' placeholder='Real gr.' step='0.01' style='border: #5aa242 solid 2px; width: 50px; border-radius: 2px; padding: 2px 1px;' /></td>
  	  <input type='hidden' name='sales[%d][name]' value='%s' />
  	  <input type='hidden' name='sales[%d][purchaseid]' value='%d' />
  	  <input type='hidden' name='sales[%d][category]' value='$categoryid' />
  	  <input type='hidden' name='sales[%d][productid]' value='%d' /></td>
	   </tr>
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
	  $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $name, $price, $i, $i, $price, $i, $i, '', $i, $i, '', $i, $i, $i, $name, $i, $product['purchaseid'], $i, $i, $product['productid'], $i, $i, $i
	  );
	  
  		}
	   
  	  		echo $flower_row;
				
				
  		}
		
	} // End not-first scan


?>

	 </table>
	 <?php
	 		echo "<input type='hidden' name='user_id' value='$user_id'>";
?>
	  <div class="clearFloat"></div>
<br />
	 
<center>
  <input type="text" name="barcode" id="focus" maxlength="80" autofocus placeholder="Codigo de barra" style="padding: 3px; width: 172px; border: solid 1px #bababa; box-shadow: 0 0 3px 1px #10762b; margin: 3px;" /><br /><br />
   <button type="submit" class='oneClick' name='oneClick' value='2' style='visibility: hidden;' />Actualizar<br /><br />
</center>

<div id="memberbox">
<img src="images/minimize.png" class="closeImg pointer" id="minimizeMemberBox" />

<?php
echo "<a href='new-picture.php?user_id=$user_id'><img src='images/_$domain/members/$user_id.$photoext' class='userPicture' /></a><span class='memberinfo'><a href='#' id='chipClick'><img src='images/rfid.png' onclick='event.preventDefault();' /></a>&nbsp;<a href='profile.php?user_id=$user_id'><span class='membername'>$memberno $first_name $last_name</span></a>";

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
  	     <td><input type='number' lang='nb' class="specialInput" id='credit' name='credit' value='<?php echo $credit; ?>' readonly /> &euro;</td>
  	     <td><input type='number' lang='nb' class='specialInput' id='newcredit' name='newcredit' value='<?php echo $credit; ?>' readonly /> &euro;</td>
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
  	     <td><input type='number' lang='nb' style='width: 50px; border: 2px solid black; margin-right: 30px;' id='paid' placeholder='&euro;' /> </td>
  	     <td><input type='number' lang='nb' style='width: 50px; border: 2px solid black; margin: 0;' id='change' placeholder='&euro;' readonly  /> </td>
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
  	   </center>
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
<br />
   <a href="#" id="openDispenseDate" style="vertical-align: bottom;" class="yellow">+ Fecha manual</a><br />
   <span id='customDispenseDate' style='display: none;'>
 <input type="text" lang="nb" name="owndate" id="datepicker" class="fiveDigit" placeholder="<?php echo $lang['pur-date']; ?>" /> @
 <input type="number" lang="nb" name="hour" id="hour" class="oneDigit" maxlength="2" placeholder="hh" /> :
 <input type="number" lang="nb" name="minute" id="minute" class="oneDigit" maxlength="2" placeholder="mm" />
   </span>

       <a href="#" id="openComment" style="vertical-align: bottom;">+ <?php echo $lang['global-comment']; ?></a>
       <textarea class="hiddenComment" name="adminComment" placeholder="<?php echo $lang['global-comment']; ?>?"></textarea>
  	   
 <br /><br /> <button type="submit" class='oneClick' id='submitButton' name='oneClick' value='1'><?php echo $lang['global-save']; ?></button><br /><br />

 <!--<a href="?saveCart&user_id=<?php echo $user_id; ?>">Save</a>--></center>
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
<?php if ($_SESSION['creditOrDirect'] == 1) { ?>
  	    <tr>
  	     <td><!--<img src='images/wallet.png' />--><input type='number' lang='nb' class='fourDigit specialInput' id='newcredit2' name='newcredit2' value="0" readonly /></td>
  	     <td><span style="font-size: 30px;"> &euro;</span></td>
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
	displayFooter();
