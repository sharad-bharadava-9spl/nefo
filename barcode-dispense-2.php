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
			$_SESSION['successMessage'] = $lang['global-added'] . " " . $amount . $lang['donation-addedsuccessfully'] . $newCredit . " ".$_SESSION['currencyoperator'];
			
			header("Location: new-dispense-2.php?user_id=$userid");
			exit();
		
	}
	
	// Did this page re-submit with a form? If so, check & store details

	if (isset($_POST['dispense']) && $_POST['oneClick_sub'] == '1') {
		
		
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

	} // End $_POST['oneClick'] == 1
	
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
			$giftOption = "<img src='images/gift-big.png' id='free%d' style='margin-bottom: 0; margin-left: 5px; margin-top: 10px; margin-bottom: 6px;' />";
		} else {
			$giftOption = "<span style='display: block; height: 10px;'></span><input type='hidden' value='%d' />";
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
        field_2 = $('input[name="' + params[1] + '"]').val(),
        field_3 = $('input[name="' + params[2] + '"]').val();
        
        if(field_3 == null){
        	field_3 = 0;
        }
    // return parseInt(value) === parseInt(field_1) + parseInt(field_2);
    
    if ((parseFloat(field_1) + parseFloat(field_2) + parseFloat(field_3)) == 0) {
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
    	  submitHandler: function(form) {
				   $(".oneClick").attr("disabled", true);
				   // form1
				      setTimeout(function(){
			       form.submit();
			   	}, 700);
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
        field_2 = $('input[name="' + params[1] + '"]').val(),
        field_3 = $('input[name="' + params[2] + '"]').val();
        
        if(field_3 == null){
        	field_3 = 0;
        }
    // return parseInt(value) === parseInt(field_1) + parseInt(field_2);
    
    if ((parseFloat(field_1) + parseFloat(field_2) + parseFloat(field_3)) == 0) {
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
    	  submitHandler: function(form) {
    	  	
   $(".oneClick").attr("disabled", true);
   // form2
      
   setTimeout(function(){
       form.submit();
   	}, 700);

   
	    	  }
	  }); // end validate

EOD;
}
	  
	$validationScript .= <<<EOD
	$("#credit").click(function () {
		$(".donateField").toggle();
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
    
   function commaChange() {
	   	  $(this).val($(this).val().replace(/,/g, '.'));
        }
   $('#newDonation').bind('keypress keyup blur change', commaChange);
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
		setTimeout(function(){
		$("#newcredit").val($("#credit").val());
		$("#newcredit2").val($("#credit").val());
		$("#changeTable").css("display", "table-row");
		$("#paid").focus();
		}, 700);
	});	
	$("#pmt2").click(function () {
		setTimeout(function(){
		$("#newcredit").val($("#credit").val());
		$("#newcredit2").val($("#credit").val());
		$("#changeTable").css("display", "none");
		}, 700);
	});	
	$("#pmt3").click(function () {
		setTimeout(function(){
	    getItems();
	    computeTot();
		$("#changeTable").css("display", "none");
		}, 700);

	});	
	
	$("#openComment").click(function (a) {
	a.preventDefault();
	$("#hiddenComment").toggle();
	//$("#openComment").css("display", "none");
	});	
	
	$("#openDispenseDate").click(function (a) {
	a.preventDefault();
	$("#customDispenseDate").toggle();
	//$("#openDispenseDate").css("display", "none");
	});	
	
	$("#openDiscount").click(function (a) {
	a.preventDefault();
	$("#discountholder").toggle();
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
   if (!$(event.target).is(".title, #plus1, #minus1")) {
   if (!$(event.target).is("#main")) {
   if (!$(event.target).is("#pmt1")) {
   if (!$(event.target).is("#pmt2")) {
   if (!$(event.target).is("#pmt3")) {
   if (!$(event.target).is("#submitButton")) {
   if (!$(event.target).is("#cart_img, #cart_count")) {
   if (!$(event.target).is("#paid")) {
   if (!$(event.target).is("#chipClick")) {
	// uncheck all radio boxes
	$("input:radio").attr("checked", false);
	setTimeout(function(){
		getItems();
	    computeTot();
	    }, 700);
	}
	}
	}
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
	$userDetails = "SELECT usageType, nationality FROM users WHERE user_id = {$user_id}";
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
		$nationality = $row['nationality'];
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
<div id='dispensarymain'>
<form id="registerForm" action="?" method="POST">
<div id="memberbox">
<div id="memberboxshade">
</div>
<?php
	if ($domain == 'cloud') {
		
		//$topimg = "images/_$domain/ID/$user_id-front.jpg";
			$object_exist = object_exist($google_bucket, $google_root_folder."images/_$domain/ID/$user_id-front.jpg");
		if($object_exist){
			$topimg = $google_root."images/_$domain/ID/$user_id-front.jpg";
		}else{
			$topimg = $google_root.'images/silhouette-new.png';
		}
		
	} else {
		//$topimg = "images/_$domain/members/$user_id.$photoext";
			$object_exist = object_exist($google_bucket, $google_root_folder."images/_$domain/members/$user_id.$photoExt");
		if($object_exist){
			$topimg = $google_root."images/_$domain/members/$user_id.$photoExt";
			
		}else{
			$topimg = $google_root.'images/silhouette-new.png';
		}
	
	}
	
/*	if (!file_exists($topimg)) {
		$topimg = 'images/silhouette-new.png';
	}*/
	
	if ($totalAmountPerWeek >= $highRollerWeekly) {
		$highroller = "<br /><div class='highrollerholder'><img src='images/trophy.png' style='margin-bottom: -2px;'/> High roller</div>";
	}
	if ($usageType == 1) {
		$medicalicon = "<tr>
     <td style='padding-bottom: 7px;'><img src='images/medical-new.png' style='margin-bottom: -1px;' /></td><td>{$lang['medical-user']}</td>
    </tr>";
	} else {
		$medicalicon = "";
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
		
	if (date('m-d') == date('m-d', strtotime($year . "-" . $month . "-" . $day . " 00:00:00"))) {
		$bday = "<tr>
     <td style='padding-bottom: 7px;'><img src='images/birthday.png' style='margin-bottom: -4px;' /></td><td>{$lang['global-birthday']}</td>
    </tr>";
	}
	
	if ($userGroup == 5 && $_SESSION['membershipFees'] == 1 && $exento == 0) {  // show Member w/ expiry
	
		$memberExp = date('y-m-d', strtotime($paidUntil));
		$memberExpReadable = date('d M', strtotime($paidUntil));
		$timeNow = date('y-m-d');
		
		if (strtotime($memberExp) == strtotime($timeNow)) {
			$expiry = "<tr>
     <td style='padding-bottom: 7px;'><a href='pay-membership.php?user_id=$user_id'><img src='images/exclamation-15.png' style='margin-bottom: -2px;' /></a></td><td><a href='pay-membership.php?user_id=$user_id'>{$lang['member-expirestoday']}</a></td>
    </tr>";
		  	if ($paymentWarning == '1') {
		  	$expiry .=  "<tr>
     <td style='padding-bottom: 7px;'><a href='pay-membership.php?user_id=$user_id'><img src='images/exclamation-15.png' style='margin-bottom: -2px;' /><img src='images/exclamation-15.png' style='margin-bottom: -2px; margin-left: -6px;' /></a></td><td><a href='pay-membership.php?user_id=$user_id'>{$lang['member-receivedwarning']}</a></td>
    </tr>";
		  	}
	  	} else if (strtotime($memberExp) < strtotime($timeNow)) {
		  	$expiry =  "<tr>
     <td style='padding-bottom: 7px;'><a href='pay-membership.php?user_id=$user_id'><img src='images/exclamation-15.png' style='margin-bottom: -2px;' /></a></td><td><a href='pay-membership.php?user_id=$user_id'>{$lang['member-expiredon']}: $memberExpReadable.</a></td>
    </tr>";
		  	
		  	if ($paymentWarning == '1') {
		  	$expiry .=  "<tr>
     <td style='padding-bottom: 7px;'><a href='pay-membership.php?user_id=$user_id'><img src='images/exclamation-15.png' style='margin-bottom: -2px;' /><img src='images/exclamation-15.png' style='margin-bottom: -2px; margin-left: -6px;' /></a></td><td><a href='pay-membership.php?user_id=$user_id'>{$lang['member-receivedwarning']}</a></td>
    </tr>";
		  	}
		  	
		}
	}
	
	if ($quantityMonth >= $mconsumption) {
		$consumptionwarning = "<tr><td style='padding-bottom: 7px;'><img src='images/exclamation-15.png' class='warningIcon' style='margin-bottom: -4px;' /></td><td>{$lang['member-conslimitexc']} (+$consumptionDelta g)</td></tr>";
	} else if ($consumptionDeltaPlus < ($mconsumption * 0.1)) {
		$consumptionwarning = "<tr><td style='padding-bottom: 7px;'><img src='images/exclamation-15.png' class='warningIcon' /></td><td>{$lang['member-conslimitnear']} ($consumptionDeltaPlus g {$lang['global-remaining']})</td></tr>";
	}
	
	echo "
	<a href='profile.php?user_id=$user_id'>
	 <span class='firsttext'>#$memberno</span></a><br />
     <a href='#' id='chipClick'><img src='images/rfid-new.png' onclick='event.preventDefault();' /></a> <span class='nametext2'>$first_name $last_name</span>
    <br />
     <table style='margin-top: 10px; vertical-align: top;'>
      <tr>
       <td>
        <div style='display: inline-block; line-height: 11px;'>
         <img src='$topimg' width='116' />
         $highroller
        </div>
       </td>
       <td style='vertical-align: top;'>
        <table style='margin-left: 16px;'>
         <tr>
          <td style='padding-bottom: 7px;'><img src='images/new-flag.png' style='margin-bottom: -4px; margin-right: 10px;' /></td>
          <td>$nationality</td>
         </tr>
         $medicalicon
         <tr>
          <td style='padding-bottom: 7px;'><img src='images/stats.png' style='margin-bottom: -2px; margin-right: 10px;' /></td>
          <td style='padding-bottom: 7px;'>{$expr(number_format($quantityMonth,1))} / {$expr(number_format($mconsumption,0))} g.<br /><span class='yellow'>{$lang['dispensary-today']}: {$expr(number_format($quantityToday,1))} g<br />{$lang['dispensary-yesterday']}: {$expr(number_format($quantityYday,1))} g </span></td>
         </tr>
         $bday
         $expiry
         $consumptionwarning
        </table>
       </td>
      </tr>
     </table>";
	?>
 	   <input type='hidden' id='eurcalcTOTexp' name='euroTOT' />
 	   <input type='hidden' name='dispense' value='done' /> 	   
 	   <input type='hidden' name='paidUntil' value='<?php echo $paidUntil; ?>' /> 	   
  	   <input type='hidden' name='user_id' value='<?php echo $user_id; ?>' />
  	   <input type='hidden' name='realCredit' id='realCredit' value='<?php echo $realCredit; ?>' />
  	   <input type='hidden' name='realNewCredit' id='realNewCredit' value='<?php echo $realNewCredit; ?>' />
  	   <input type='hidden' name='totDiscountInput' id='totDiscountInput' value='' />
  	   <input type='hidden' name='discType' id='discType' />
<br />  	   
  	   <table id='memberBoxTable'>
  	    <tr>
  	     <td class='dispensetd'><input type='number' lang='nb' class='blankinput first notZero' id='grcalcTOT' name='gramsTOT' value="" readonly /> g</td>
  	     <td class='dispensetd'><input type='number' lang='nb' class='blankinput first notZero' id='unitcalcTOT' name='unitsTOT' value="0" readonly /> u</td>
  	     <td class='dispensetd'><input type='number' lang='nb' class='blankinput' id='eurcalcTOT' name='eurcalcTOT' value="0" readonly /> <?php echo $_SESSION['currencyoperator'] ?></td>
	    </tr>  	   
  	    <tr>
  	     <td class='dispensetd' colspan='3'>
  	      <table style='width: 100%;'>
  	       <tr>
  	        <td class='saldoheader'><?php echo $lang['global-credit']; ?>:</td>
  	        <td>
  	        <?php if ($credit < 0) { ?>
  	        <input type='number' lang='nb' class="specialInput" style='color: red !important;' id='credit' name='credit' value='0' readonly /> <?php echo $_SESSION['currencyoperator'] ?>
  	        <?php } else { ?>
  	        <input type='number' lang='nb' class="specialInput" id='credit' name='credit' value='<?php echo $credit; ?>' readonly /> <?php echo $_SESSION['currencyoperator'] ?>
  	        <?php } ?>
  	        </td>
  	        <td class='saldoheader' style='text-align: right;'><?php echo $lang['new-normal']; ?>:</td>
  	        <td>
  	        <?php if ($credit < 0) { ?>
  	        <input type='number' lang='nb' class='specialInput' style='color: red !important;' id='newcredit' name='newcredit' value='0' readonly /> <?php echo $_SESSION['currencyoperator'] ?>
  	        <?php } else { ?>
  	        <input type='number' lang='nb' class='specialInput' id='newcredit' name='newcredit' value='<?php echo $credit; ?>' readonly /> <?php echo $_SESSION['currencyoperator'] ?>
  	        <?php } ?>
  	        </td>
  	       </tr>
<?php if ($_SESSION['dispDonate'] == 1) { ?>
  	       <tr style='display: none;' class='donateField'>
  	        <td class='saldoheader'><?php echo $lang['donate']; ?>:</td>
  	        <td style='text-align: right; padding-right: 13px;'><input type='text' lang='nb' class='twoDigit defaultinput-no-margin' id='newDonation' name='newDonation' value='0' /> <?php echo $_SESSION['currencyoperator'] ?></td>
  	       </tr>
<?php } ?>
  	      </table>
  	     </td>
	    </tr>  	   
  	    <tr>
  	     <td colspan='3'>
  	      <table style='width: 100%;'>
  	       <tr>
  	        <td style='width: 50%;'><a href="#" id="openDispenseDate" class='expandable'><img src='images/date-new.png' style='margin-bottom: -3px; margin-right: 3px;' /> <?php echo $lang['pur-date']; ?></a></td>
  	        <td style='width: 50%; text-align: right;'><a href="#" id="openComment" class='expandable'><img src='images/comment-new.png' style='margin-bottom: -3px; margin-right: 3px;' /> <?php echo $lang['comments']; ?></a></td>
  	       </tr>
  	       <tr>
  	        <td colspan='2'>
  	         <span id='customDispenseDate' class='expanded' style='display: none;'>
		      <input type="text" lang="nb" name="owndate" id="datepicker" class="fiveDigit defaultinput" placeholder="<?php echo $lang['pur-date']; ?>" />@
		      <input type="number" lang="nb" name="hour" id="hour" class="oneDigit defaultinput" maxlength="2" placeholder="hh" />
		      <input type="number" lang="nb" name="minute" id="minute" class="oneDigit defaultinput" maxlength="2" placeholder="mm" />
   		     </span>
   		     <span class='expanded' id='hiddenComment' style='display: none;'>
              <textarea class="defaultinput" name="adminComment" placeholder="<?php echo $lang['global-comment']; ?>?"></textarea>
             </span>
            </td>
  	       </tr>
  	      </table>
  	     </td>
	    </tr>
<?php if ($_SESSION['checkoutDiscount'] == 1) { ?>
  	    <tr>
  	     <td colspan='3'>
  	      <table style='width: 100%;'>
  	       <tr>
  	        <td>
  	         <a href="#" id="openDiscount" class='expandable2'><img src='images/discount.png' style='margin-bottom: -4px; margin-right: 3px;' /> <?php echo $lang['apply-discount']; ?> <span id='totDiscount'>(a%)</span></a> 
  	        </td>
  	       </tr>
  	       <tr>
  	        <td class='expanded2' id='discountholder' style='display: none;'>
  	          	   <center>
        <input type="checkbox" id="fee0" name="inDiscount" value='5' />
        <label for="fee0"><span class='full2'>5%</span></label>
        
        <input type="checkbox" id="fee1" name="inDiscount" value='10' />
        <label for="fee1"><span class='full2'>10%</span></label>
        
        <input type="checkbox" id="fee2" name="inDiscount" value='20' />
        <label for="fee2"><span class='full2'>20%</span></label>
        <input type="checkbox" id="fee3" name="inDiscount" value='30' />
        <label for="fee3"><span class='full2'>30%</span></label>
        <br />
        <span style='color: #656d60;'><?php echo $lang['member-orcaps'] . " " . $lang['member-discount']; ?> <?php echo $_SESSION['currencyoperator'] ?>:&nbsp; <input type='number' name='eurdiscount' id='eurdiscount' class='twoDigit defaultinput-no-margin' style='margin: 5px 0;' /></span>
        
  	   </center>
  	        </td>
  	       </tr>
  	      </table>
  	     </td>
	    </tr>
<?php } else {
		echo "<input type='hidden' name='eurdiscount' id='eurdiscount' class='twoDigit' value='0' />";
	}
	if ($_SESSION['creditOrDirect'] == 0) { ?>
<!--  	    <tr>
  	     <td colspan='3'><span style='font-size: 14px; display: inline-block; text-transform: uppercase; font-weight: 600; color: #626d5d;'><?php echo $lang['paid-by']; ?></td>
  	    </tr>-->
   	    <tr>
  	     <td><input type="radio" id="pmt1" name="pmtType" value='1' />
          <label for="pmt1"><span class='full' id="pmt1trigger"><?php echo $lang['cash']; ?></span></label>
         </td>
  	     <td>
<?php if ($_SESSION['bankPayments'] == 1) { ?>
          <input type="radio" id="pmt2" name="pmtType" value='2' />
          <label for="pmt2"><span class='full' id="pmt2trigger"><?php echo $lang['card']; ?></span></label>
<?php } else { ?>
          <input type="radio" id="pmt2" name="pmtType" value='2' style="visibility: hidden;" />
          <label for="pmt2" style="visibility: hidden;" ><span class='full' id="pmt2trigger"><?php echo $lang['card']; ?></span></label>
<?php } ?>
  	     </td>
  	     <td>
          <input type="radio" id="pmt3" name="pmtType" value='3' />
          <label for="pmt3"><span class='full' id="pmt3trigger"><?php echo $lang['global-credit']; ?></span></label><br />
  	     </td>
	    </tr>
<?php } ?>
  	    <tr id='changeTable' style='display: none;'>
  	     <td class='dispensetd' colspan='3'>
  	      <table style='width: 100%;'>
  	       <tr>
  	        <td class='saldoheader'><?php echo $lang['paid']; ?>:</td>
  	        <td>
  	         <input type='number' lang='nb' class='twoDigit defaultinput-no-margin' id='paid' placeholder='<?php echo $_SESSION['currencyoperator'] ?>' />
  	        </td>
  	        <td class='saldoheader' style='text-align: right;'><?php echo $lang['change-money']; ?>:</td>
  	        <td>
   	         <input type='number' lang='nb' class='twoDigit defaultinput-no-margin' id='change' placeholder='<?php echo $_SESSION['currencyoperator'] ?>' readonly  />
  	        </td>
  	       </tr>
  	      </table>
  	     </td>
	    </tr>
	    
	    <?php
	if ($_SESSION['iPadReaders'] == 0) {
		
		// Chip
		if ($_SESSION['dispsig'] == 0) {
			echo <<<EOD
   	    <tr>
  	     <td colspan='3'><input type="text" class="donateOrNot defaultinput" id="cardscan" name="cardid" maxlength="30" placeholder="{$lang['global-scantoconfirm']}" /></td></tr>
EOD;
		// Topaz
		} else if ($_SESSION['dispsig'] == 1) {
			echo <<<EOD
   	    <tr>
  	     <td colspan='3'><canvas id="cnv" name="cnv" class='defaultinput' onclick="javascript:onSign()"></canvas><br />
<center><input id="button2" name="DoneBtn" class='cta1' style='margin: 0;' type="button" value="Guardar firma" onclick="javascript:onDone()" /></center></td></tr>
EOD;
			
		}
	}
?>
	   </table>
  	   
<br />
<input type="hidden" name="oneClick_sub" id="sub_hidden" value="0">
<button type="submit" class='oneClick cta6' name='oneClick' id='submitButton' value="1"><img src='images/checkmark-new.png' style='margin-bottom: -10px;' />&nbsp;<?php echo $lang['global-save']; ?></button><br /><br />
<a href="#" class='cta7' onclick="event.preventDefault(); document.getElementById('registerForm').reset();"><img src='images/cancel.png' style='margin-bottom: -6px;' />&nbsp;<?php echo $lang['global-delete']; ?></a><br /><br />
<center><a href="barcode-dispense.php?user_id=<?php echo $user_id; ?>"><img src="images/barcode-new.png" /></a></center>
</div> <!-- end memberbox -->
<div class="clearfloat"></div>


<?php

echo "<table class='default productlist nonhover'><tbody>";

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
		$medicalDiscount = $_SESSION['medicalDiscount'];
		$medicalDiscountPercentage = $_SESSION['medicalDiscountPercentage'];
		
		if ($medicalDiscountPercentage == 1) {
			$medicalDiscountCalc = 1 - ($medicalDiscount / 100);
		}
				
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
				
				$normalPrice = "({$flower['salesPrice']} {$_SESSION['currencyoperator']})";
				
			} else {
			
				$normalPrice = "";
				
			}
			
	 // KONSTANT CODE UPDATE BEGIN
	// Start usergroup discont 
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
        //End group discount 
	// Individual purchase discount
        $dispDiscount1 = 0;
        $discountType = 0;
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
		$dispDiscount1 = number_format((($prodFijo * 100) / $flower['salesPrice']),2);
	    $discountType = 1;
		$price = number_format($prodFijo,2);
		
	} else if ($prodDiscount != 0 || $prodDiscount != '') {
		$dispDiscount1 = $prodDiscount;
		$discountType = 1;
		$prodDiscount = (100 - $prodDiscount) / 100;
		$price = number_format($flower['salesPrice'] * $prodDiscount,2);
		
	} else if ($catDiscount != 0 || $catDiscount != '') {
		$dispDiscount1 = $catDiscount;
		$discountType = 1;
		$catCurrentDiscount = (100 - $catDiscount) / 100;
		$price = number_format($flower['salesPrice'] * $catCurrentDiscount,2);
	} else if ($discount != 0) {
		$dispDiscount1 = $discount;
		$discountType = 1;
		$dispDiscount = (100 - $discount) / 100;
		$price = number_format($flower['salesPrice'] * $dispDiscount,2);
		
	} else if ($usageType == '1') {
		
		if ($flower['medDiscount'] > 0) {
			$dispDiscount1 = $flower['medDiscount'];
        	$discountType = 2;
			$dispDiscount = (100 - $flower['medDiscount']) / 100;
			$price = number_format($flower['salesPrice'] * $dispDiscount,2);
		} else if ($medicalDiscountPercentage == 1) {
			$dispDiscount1 = $medicalDiscountCalc;
        	$discountType = 2;
			$price = number_format(($flower['salesPrice']) * $medicalDiscountCalc,2);
		} else {
			$dispDiscount1 = number_format((($medicalDiscount * 100) / $flower['salesPrice']),2);
        	$discountType = 2;
			$price = number_format(($flower['salesPrice']) - $medicalDiscount,2);
		}
		
	}else if (isset($rowDU['discount_percentage'])){
            if(isset($rowDU['discount_percentage']) && $rowDU['discount_percentage'] > 0){
                $dispDiscount1 = $rowDU['discount_percentage'];
                $discountType = 4;
                $dispDiscount = (100 - $rowDU['discount_percentage']) / 100;
                $price = number_format($flower['salesPrice'] * $dispDiscount,2);
            }else{
                if(isset($rowDU['discount_price']) && $rowDU['discount_price'] > 0){
                    $dispDiscount1 = number_format(($rowDU['discount_price'] * 100) / $flower['salesPrice'],2);
                    $discountType = 4;
                    $price = number_format(($flower['salesPrice']) - $rowDU['discount_price'],2);
                }
            }
	}
	 else {
		
		$price = number_format($flower['salesPrice'],2);
		
	}
	//Start happy hour discount
        $hHFlag = 0;
        $getDay = date('l').'s';
        $getTime = date('H:i:s');
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
                $discountType = 3;
                $dispDiscount = (100 - $hHDiscount['discount']) / 100;
                $price = number_format($flower['salesPrice'] * $dispDiscount,2);
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
                $discountType = 3;
                $dispDiscount = (100 - $hHDiscount['discount']) / 100;
                $price = number_format($flower['salesPrice'] * $dispDiscount,2);
            }
        }
        if($happy_hour_id){
            $selectCategoryDiscount = "SELECT discount FROM catdiscounts WHERE categoryid = 1 and happy_hour_id = ".$happy_hour_id;
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
                	 $discountType = 3;
                    $price = number_format($flower['salesPrice'] * $catCurrentDiscount,2);
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
                $prodFijoHD1 = number_format((($prodFijoHD * 100) / $flower['salesPrice']),2);
                if ($prodFijoHD1 > $dispDiscount1){
                    //$prodDiscountHD = (100 - $prodFijoHD1) / 100;
                    //$price = number_format($flower['salesPrice'] * $prodDiscountHD,2);
                    $dispDiscount1 = number_format((($prodFijoHD * 100) / $flower['salesPrice']),2);
                     $discountType = 3;
                    $price = number_format($prodFijoHD,2);
                }                
                
            } else if ($prodDiscountHD != 0 || $prodDiscountHD != '') {
                if($prodDiscountHD > $dispDiscount1){
                    $prodDiscountHD = (100 - $prodDiscountHD) / 100;
                    $discountType = 3;
                    $price = number_format($flower['salesPrice'] * $prodDiscountHD,2);
                }
            }
        }
        // KONSTANT CODE UPDATE END			
			
		$savedGrams2[$i] = '';
		$displayGift = 'display: none;';
			
			if ($_SESSION['dispensaryGift'] == 1 && $_SESSION['menuType'] == 1) {
				$giftOption = "<img src='images/gift-small.png' id='free%d' style='margin-bottom: -15px; margin-left: 5px;' />";
			} else if ($_SESSION['dispensaryGift'] == 1 && $_SESSION['menuType'] == 0) {
				if ($_SESSION['realWeight'] == 0) {
					$giftOption = "<img src='images/gift-big.png' id='free%d' style='margin-bottom: 0; margin-left: 5px; margin-top: 10px; margin-bottom: 6px;' />";
				} else {
					$giftOption = "<img src='images/gift-small.png' id='free%d' style='margin-bottom: 0; margin-left: -5px; margin-top: 10px; margin-bottom: 6px;' />";
				}
			} else {
				$giftOption = "<span style='display: block; height: 10px;'></span><input type='hidden' value='%d' />";
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
          	    // KONSTANT CODE UPDATE BEGIN
                var volDiscount = $('#volume_%d_discount').val().delay(700);
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
	    // KONSTANT CODE UPDATE BEGIN4
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
	                    	var dataFilter = data.split('_');
	                    	var firstprice = dataFilter[0];
	                    	var discountPercent = dataFilter[1];
	                    	var volumePercent = dataFilter[2];
	                        var roundedtotal = parseFloat(firstprice).toFixed(2);
	                        $('#volume_%d_discount').val(roundedtotal);
	                        $('#happy_hour_%d_discount').val(discountPercent);
	                        $('#volume_per_%d_discount').val(discountPercent);
	                  	    $('#eurcalc%d').val(roundedtotal);
	                    }else{
	                        $('#volume_%d_discount').val('');
	                        $('#happy_hour_%d_discount').val('');
	                        $('#volume_per_%d_discount').val('');
	                    }
	                }
	            }); 
	        }else{
	        	$('#volume_%d_discount').val('');
	        }
	    }
	    $('#grcalc%d').bind('keyup blur change', applyVolumeDiscount);
	    // KONSTANT CODE UPDATE END
        $('#realgrcalc%d').bind('keypress keyup blur change', compute3);
        $('#grcalc%d').bind('keypress keyup blur change', compute);
        $('#eurcalc%d').bind('keypress keyup blur change', compute2);
        $('#grcalcB%d').bind('keyup blur change', compute3);
       
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
	    <td class='left'>%s<br>%s</td>
	    <td>
	    	<span class='relativeitem'>$commentRead</span>&nbsp;
	    	<span class='relativeitem'>$commentReadM</span>&nbsp;&nbsp;&nbsp;
	    	<strong style='font-size: 15px;'>%0.02f {$_SESSION['currencyoperator']}</strong> <input type='hidden' id='ppgcalc%d' name='sales[%d][ppg]' value='%0.02f' />&nbsp;&nbsp;
	    	<span class='firsttext' style='font-size: 15px;'>$normalPrice</span>&nbsp;&nbsp;&nbsp;	    
	    	<span class='smallgreen' style='text-transform: none;'>$stockDisplay</span>
	    	<br>
	    	<input type='text' lang='nb' class='twoDigit defaultinput centered calc onScreenKeypad c$purchaseid' id='grcalc%d' name='sales[%d][grams]' placeholder='Gr.' step='0.01' value='%s' />
	    	<input type='text' lang='nb' class='twoDigit defaultinput centered calc2 onScreenKeypad' id='eurcalc%d' name='sales[%d][euro]' placeholder='{$_SESSION['currencyoperator']}' step='0.01' value='%s' />
	    	<input type='text' lang='nb' class='fourDigit defaultinput centered calc5' id='realgrcalc%d' name='sales[%d][realGrams]' placeholder='Real gr.' step='0.01'  value='%s' />
	    	<input type='text' lang='nb' class='twoDigit defaultinput centered calc calc6 onScreenKeypad' id='grcalcB%d' name='sales[%d][grams2]' placeholder='Gr.' step='0.01' value='%s' style='background-color: #f4b146; $displayGift'  />$giftOption
	    	  &nbsp;&nbsp;<img src='images/delete.png' width='17' style='cursor: pointer;' class='remove_pr' id='zero%d' />
	    </td>
	   </tr>
  	  <input type='hidden' name='sales[%d][name]' value='%s' />
  	  <input type='hidden' name='sales[%d][purchaseid]' value='%d' id='sales_%d_purchaseid' />
  	  <input type='hidden' name='sales[%d][category]' value='1' id='sales_%d_category' />
  	  <input type='hidden' name='sales[%d][productid]' value='%d' id='sales_%d_productid' />
  	  <input type='hidden' name='sales[%d][volume_discount]' id='volume_%d_discount' />
  	  <input type='hidden' name='sales[%d][discType]' value='%d'/>
  	  <input type='hidden' name='sales[%d][discPercentage]' value='%d'/>
  	  <input type='hidden' name='sales[%d][happy_hour_discount]' id='happy_hour_%d_discount'/>
  	  <input type='hidden' name='sales[%d][volume_per_discount]' id='volume_per_%d_discount'/>
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
	  $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $name, $growtype, $flowertype, $price, $i, $i, $price, $i, $i, $savedGrams[$i], $i, $i, $savedEuro[$i], $i, $i, $savedrealGrams[$i], $i, $i, $savedGrams2[$i], $i, $i, $i, $name, $i, $flower['purchaseid'], $i, $i, $i, $i, $flower['flowerid'], $i, $i, $i, $i, $discountType, $i, $dispDiscount1, $i, $i, $i, $i, $i, $i, $i
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
		$medicalDiscount = $_SESSION['medicalDiscount'];
		$medicalDiscountPercentage = $_SESSION['medicalDiscountPercentage'];
		
		if ($medicalDiscountPercentage == 1) {
			$medicalDiscountCalc = 1 - ($medicalDiscount / 100);
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
        $discountType = 0;
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
		$dispDiscount1 = number_format((($prodFijo * 100) / $extract['salesPrice']),2);
		$discountType = 1;
		$price = number_format($prodFijo,2);
		
	} else if ($prodDiscount != 0 || $prodDiscount != '') {
		$dispDiscount1 = $prodDiscount;
		$discountType = 1;
		$prodDiscount = (100 - $prodDiscount) / 100;
		$price = number_format($extract['salesPrice'] * $prodDiscount,2);
		
	} else if ($catDiscount != 0 || $catDiscount != '') {
		$dispDiscount1 = $catDiscount;
		$discountType = 1;
		$catCurrentDiscount = (100 - $catDiscount) / 100;
		$price = number_format($extract['salesPrice'] * $catCurrentDiscount,2);
	} else if ($discount != 0) {
		$dispDiscount1 = $discount;
		$discountType = 1;
		$dispDiscount = (100 - $discount) / 100;
		$price = number_format($extract['salesPrice'] * $dispDiscount,2);
		
	} else if ($usageType == '1') {
		
		if ($extract['medDiscount'] > 0) {
        		$dispDiscount1 = $extract['medDiscount'];
        		$discountType = 2;	
			$dispDiscount = (100 - $extract['medDiscount']) / 100;
			$price = number_format($extract['salesPrice'] * $dispDiscount,2);
		} else if ($medicalDiscountPercentage == 1) {
        		$dispDiscount1 = $medicalDiscountCalc;
        		$discountType = 2;	
			$price = number_format(($extract['salesPrice']) * $medicalDiscountCalc,2);
		} else {
        		$dispDiscount1 = number_format((($medicalDiscount * 100) / $extract['salesPrice']),2);
        		$discountType = 2;	
			$price = number_format(($extract['salesPrice']) - $medicalDiscount,2);
		}
	} else if (isset($rowDU['discount_percentage'])){
            if(isset($rowDU['discount_percentage']) && $rowDU['discount_percentage'] > 0){
                $dispDiscount1 = $rowDU['discount_percentage'];
                 $discountType = 4;
                $dispDiscount = (100 - $rowDU['discount_percentage']) / 100;
                $price = number_format($extract['salesPrice'] * $dispDiscount,2);
            }else{
                if(isset($rowDU['discount_price']) && $rowDU['discount_price'] > 0){
                    $dispDiscount1 = number_format(($rowDU['discount_price'] * 100) / $extract['salesPrice'],2);
                    $discountType = 4;
                    $price = number_format(($extract['salesPrice']) - $rowDU['discount_price'],2);
                }
            }
	} else {
		
		$price = number_format($extract['salesPrice'],2);
		
	}
        
        
        // KONSTANT CODE UPDATE BEGIN
        //Start happy hour discount
        $hHFlag = 0;
        $getDay = date('l').'s';
        $getTime = date('H:i:s');
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
                $price = number_format($extract['salesPrice'] * $dispDiscount,2);
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
                 $discountType = 3;
                $dispDiscount = (100 - $hHDiscount['discount']) / 100;
                $price = number_format($extract['salesPrice'] * $dispDiscount,2);
            }
        }
        if($happy_hour_id){
            $selectCategoryDiscount = "SELECT discount FROM catdiscounts WHERE categoryid = 2 and happy_hour_id = ".$happy_hour_id;
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
                	 $discountType = 3;
                    $price = number_format($extract['salesPrice'] * $catCurrentDiscount,2);
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
                $prodFijoHD1 = number_format((($prodFijoHD * 100) / $extract['salesPrice']),2);
                if ($prodFijoHD1 > $dispDiscount1){
                    //$prodDiscountHD = (100 - $prodFijoHD1) / 100;
                    //$price = number_format($extract['salesPrice'] * $prodDiscountHD,2);
                    $dispDiscount1 = number_format((($prodFijoHD * 100) / $extract['salesPrice']),2);
                     $discountType = 3;
                    $price = number_format($prodFijoHD,2);
                }                
            } else if ($prodDiscountHD != 0 || $prodDiscountHD != '') {
                if($prodDiscountHD > $prodDiscount && $prodDiscountHD > $dispDiscount1){
                    $prodDiscountHD = (100 - $prodDiscountHD) / 100;
                     $discountType = 3;
                    $price = number_format($extract['salesPrice'] * $prodDiscountHD,2);
                }
            }
        }
        
        
        // KONSTANT CODE UPDATE END
			
			
		$savedGrams2[$i] = '';
		$displayGift = 'display: none;';
			if ($_SESSION['dispensaryGift'] == 1 && $_SESSION['menuType'] == 1) {
				$giftOption = "<img src='images/gift-small.png' id='free%d' style='margin-bottom: -15px; margin-left: 5px;' />";
			} else if ($_SESSION['dispensaryGift'] == 1 && $_SESSION['menuType'] == 0) {
				if ($_SESSION['realWeight'] == 0) {
					$giftOption = "<img src='images/gift-big.png' id='free%d' style='margin-bottom: 0; margin-left: 5px; margin-top: 10px; margin-bottom: 6px;' />";
				} else {
					$giftOption = "<img src='images/gift-small.png' id='free%d' style='margin-bottom: 0; margin-left: -5px; margin-top: 10px; margin-bottom: 6px;' />";
				}
			} else {
				$giftOption = "<span style='display: block; height: 10px;'></span><input type='hidden' value='%d' />";
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
                // KONSTANT CODE UPDATE BEGIN
                var volDiscount = $('#volume_%d_discount').val().delay(700);
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
		                    	var dataFilter = data.split('_');
		                    	var firstprice = dataFilter[0];
		                    	var discountPercent = dataFilter[1];
		                    	var volumePercent = dataFilter[2];
		                        var roundedtotal = parseFloat(firstprice).toFixed(2);
		                        $('#volume_%d_discount').val(roundedtotal);
		                        $('#happy_hour_%d_discount').val(discountPercent);
		                  	    $('#eurcalc%d').val(roundedtotal);
		                    }else{
		                        $('#volume_%d_discount').val('');
		                        $('#happy_hour_%d_discount').val('');
		                        $('#volume_per_%d_discount').val('');
		                    }
                    }
                }); 
            }else{
            	 $('#volume_%d_discount').val('');
            }
        }
        $('#grcalc%d').bind('keyup blur change', applyVolumeDiscount);
        // KONSTANT CODE UPDATE END
        $('#realgrcalc%d').bind('keypress keyup blur change', compute3);
        $('#grcalcB%d').bind('keypress keyup blur change', compute3);
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
        
	   <tr class='$trclass'>
	    <td class='left'><strong>%s</strong></td>
	    <td class='left'>%s</td>
	    <td>
	    	<span class='relativeitem'>$commentRead</span>&nbsp;
	    	<span class='relativeitem'>$commentReadM</span>&nbsp;&nbsp;&nbsp;
	    	<strong style='font-size: 15px;'>%0.02f {$_SESSION['currencyoperator']}</strong> <input type='hidden' id='ppgcalc%d' name='sales[%d][ppg]' value='%0.02f' />&nbsp;&nbsp;
	    	<span style='font-size: 15px;' class='firsttext'>$normalPrice</span>&nbsp;&nbsp;&nbsp;	    
	    	<span class='smallgreen' style='text-transform: none;'>$stockDisplay</span><br>
	    	<input type='text' lang='nb' class='twoDigit defaultinput centered calc onScreenKeypad c$purchaseid'  id='grcalc%d' name='sales[%d][grams]' placeholder='Gr.' step='0.01' value='%s' />
	    	<input type='text' lang='nb' class='twoDigit defaultinput centered calc2 onScreenKeypad' id='eurcalc%d' name='sales[%d][euro]' placeholder='{$_SESSION['currencyoperator']}' step='0.01' value='%s' />
	    	<input type='text' lang='nb' class='fourDigit defaultinput centered calc5' id='realgrcalc%d' name='sales[%d][realGrams]' placeholder='Gr. real' step='0.01'  value='%s'/> 
			<input type='text' lang='nb' class='twoDigit defaultinput centered calc calc6 onScreenKeypad' id='grcalcB%d' name='sales[%d][grams2]' placeholder='Gr.' step='0.01' value='%s' style='background-color: #f4b146; $displayGift'  />$giftOption
	    	&nbsp;&nbsp;<img src='images/delete.png' width='17' style='cursor: pointer;' class='remove_pr' id='zero%d' />
	    	
	    	</td>
	   </tr>
  	  <input type='hidden' name='sales[%d][name]' value='%s' />
  	  <input type='hidden' name='sales[%d][purchaseid]' value='%d' id='sales_%d_purchaseid' />
  	  <input type='hidden' name='sales[%d][category]' value='2' id='sales_%d_category' />
  	  <input type='hidden' name='sales[%d][productid]' value='%d' id='sales_%d_productid' />
  	  <input type='hidden' name='sales[%d][volume_discount]' id='volume_%d_discount' />
  	  <input type='hidden' name='sales[%d][discType]' value='%d'/>
  	  <input type='hidden' name='sales[%d][discPercentage]' value='%d'/>
  	  <input type='hidden' name='sales[%d][happy_hour_discount]' id='happy_hour_%d_discount'/>
  	  <input type='hidden' name='sales[%d][volume_per_discount]' id='volume_per_%d_discount'/>
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
	  $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $extract['name'], $extractype, $price, $i, $i, $price, $i, $i, $savedGrams[$i], $i, $i, $savedEuro[$i],  $i, $i, $savedrealGrams[$i], $i, $i, $savedGrams2[$i],$i, $i, $i, $extract['name'], $i, $extract['purchaseid'], $i, $i, $i, $i, $extract['extractid'], $i, $i, $i, $i, $discountType ,$i, $dispDiscount1, $i, $i, $i, $i, $i, $i, $i, $i, $i
	  );
// KONSTANT CODE UPDATE END	  
	  
	  echo $extract_row;
			
		} else { // End category 2
			
		
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
		$medicalDiscount = $_SESSION['medicalDiscount'];
		$medicalDiscountPercentage = $_SESSION['medicalDiscountPercentage'];
		
		if ($medicalDiscountPercentage == 1) {
			$medicalDiscountCalc = 1 - ($medicalDiscount / 100);
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
        $discountType = 0;
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
		$discountType = 1;
		$price = number_format($prodFijo,2);
		
	} else if ($prodDiscount != 0 || $prodDiscount != '') {
		$dispDiscount1 = $prodDiscount;
		$discountType = 1;
		$prodDiscount = (100 - $prodDiscount) / 100;
		$price = number_format($product['salesPrice'] * $prodDiscount,2);
		
	} else if ($catDiscount != 0 || $catDiscount != '') {
		$dispDiscount1 = $catDiscount;
		$discountType = 1;
		$catCurrentDiscount = (100 - $catDiscount) / 100;
		$price = number_format($product['salesPrice'] * $catCurrentDiscount,2);
	} else if ($discount != 0) {
		$dispDiscount1 = $discount;
		$discountType = 1;
		$dispDiscount = (100 - $discount) / 100;
		$price = number_format($product['salesPrice'] * $dispDiscount,2);
		
	} else if ($usageType == '1') {
		
		if ($product['medDiscount'] > 0) {
        		$dispDiscount1 = $product['medDiscount'];
        		$discountType = 2;
			$dispDiscount = (100 - $product['medDiscount']) / 100;
			$price = number_format($product['salesPrice'] * $dispDiscount,2);
		} else if ($medicalDiscountPercentage == 1) {
        		$dispDiscount1 = $medicalDiscountCalc;
        		$discountType = 2;
			$price = number_format(($product['salesPrice']) * $medicalDiscountCalc,2);
		} else {
        		$dispDiscount1 = number_format((($medicalDiscount * 100) / $product['salesPrice']),2);
        		$discountType = 2;
			$price = number_format(($product['salesPrice']) - $medicalDiscount,2);
		}
	} else if (isset($rowDU['discount_percentage'])){
            if(isset($rowDU['discount_percentage']) && $rowDU['discount_percentage'] > 0){
                $dispDiscount1 = $rowDU['discount_percentage'];
                 $discountType = 4;
                $dispDiscount = (100 - $rowDU['discount_percentage']) / 100;
                $price = number_format($product['salesPrice'] * $dispDiscount,2);
            }else{
                if(isset($rowDU['discount_price']) && $rowDU['discount_price'] > 0){
                    $dispDiscount1 = number_format(($rowDU['discount_price'] * 100) / $product['salesPrice'],2);
                     $discountType = 4;
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
        $getTime = date('H:i:s');
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
                $discountType = 3;
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
                $discountType = 3;
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
                	$discountType = 3;
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
                    $discountType = 3;
                    $price = number_format($prodFijoHD,2);
                }
            } else if ($prodDiscountHD != 0 || $prodDiscountHD != '') {
                if($prodDiscountHD > $prodDiscount && $prodDiscountHD > $dispDiscount1){
                    $prodDiscountHD = (100 - $prodDiscountHD) / 100;
                    $discountType = 3;
                    $price = number_format($product['salesPrice'] * $prodDiscountHD,2);
                }
            }
        }
        // KONSTANT CODE UPDATE END
	
			$savedGrams2[$i] = '';
		    $displayGift = 'display: none;';
				
				if ($_SESSION['dispensaryGift'] == 1 && $_SESSION['menuType'] == 1) {
					$giftOption = "<img src='images/gift-small.png' id='free%d' style='margin-bottom: -15px; margin-left: 5px;' />";
				} else if ($_SESSION['dispensaryGift'] == 1 && $_SESSION['menuType'] == 0) {
					if ($_SESSION['realWeight'] == 0) {
						$giftOption = "<img src='images/gift-big.png' id='free%d' style='margin-bottom: 0; margin-left: 5px; margin-top: 10px; margin-bottom: 6px;' />";
					} else {
						$giftOption = "<img src='images/gift-small.png' id='free%d' style='margin-bottom: 0; margin-left: -5px; margin-top: 10px; margin-bottom: 6px;' />";
					}
				} else {
					$giftOption = "<span style='display: block; height: 10px;'></span><input type='hidden' value='%d' />";
				}
					  
			
			
			// Unit cat first	
			if ($type == 0) {
				
			
			
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
                var volDiscount = $('#volume_%d_discount').val().delay(700);
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
        
   function compute3() {
	   	  $(this).val($(this).val().replace(/,/g, '.'));
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
	                    	var dataFilter = data.split('_');
	                    	var firstprice = dataFilter[0];
	                    	var discountPercent = dataFilter[1];
	                    	var volumePercent = dataFilter[2];
	                        var roundedtotal = parseFloat(firstprice).toFixed(2);
	                        $('#volume_%d_discount').val(roundedtotal);
	                        $('#happy_hour_%d_discount').val(discountPercent);
	                        $('#volume_per_%d_discount').val(volumePercent);
	                  	    $('#eurcalc%d').val(roundedtotal);
	                    }else{
	                        $('#volume_%d_discount').val('');
	                        $('#happy_hour_%d_discount').val('');
	                        $('#volume_per_%d_discount').val('');
	                    }
                    }
                }); 
            }else{
            	$('#volume_%d_discount').val('');
            }
        }
        $('#grcalc%d').bind('keyup blur change', applyVolumeDiscount);
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
	    <td class='left'></td>
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
  	  <input type='hidden' name='sales[%d][purchaseid]' value='%d' id='sales_%d_purchaseid' />
  	  <input type='hidden' name='sales[%d][category]' value='%d' id='sales_%d_category' />
  	  <input type='hidden' name='sales[%d][productid]' value='%d' id='sales_%d_productid' />
  	  <input type='hidden' name='sales[%d][volume_discount]' id='volume_%d_discount' />
  	  <input type='hidden' name='sales[%d][discType]' value='%d'/>
  	  <input type='hidden' name='sales[%d][discPercentage]' value='%d'/>
  	  <input type='hidden' name='sales[%d][happy_hour_discount]' id='happy_hour_%d_discount'/>
  	  <input type='hidden' name='sales[%d][volume_per_discount]' id='volume_per_%d_discount'/>
",  //KONSTANT CODE UPDATE BEGIN 
	  $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i ,$i ,$i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $product['name'], $price, $i, $i, $price, $i, $i, $savedGrams[$i], $i, $i, $savedEuro[$i], $i, $i, $savedGrams2[$i],$i, $i, $product['name'], $i, $product['purchaseid'], $i, $i, $categoryid, $i, $i, $product['productid'], $i, $i, $i, $i, $discountType ,$i, $dispDiscount1, $i, $i, $i, $i
	  );
// KONSTANT CODE UPDATE END
        
	  echo $product_row;

	  
  			} else { // Now Gram cat
		
			
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
                var volDiscount = $('#volume_%d_discount').val().delay(700);
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
	                    	var dataFilter = data.split('_');
	                    	var firstprice = dataFilter[0];
	                    	var discountPercent = dataFilter[1];
	                    	var volumePercent = dataFilter[2];
	                        var roundedtotal = parseFloat(firstprice).toFixed(2);
	                        $('#volume_%d_discount').val(roundedtotal);
	                        $('#happy_hour_%d_discount').val(discountPercent);
	                        $('#volume_per_%d_discount').val(volumePercent);
	                  	    $('#eurcalc%d').val(roundedtotal);
	                    }else{
	                        $('#volume_%d_discount').val('');
	                        $('#happy_hour_%d_discount').val('');
	                        $('#volume_per_%d_discount').val('');
	                    }
                    }
                }); 
            }else{
            	$('#volume_%d_discount').val('');
            }
        }
        $('#grcalc%d').bind('keyup blur change', applyVolumeDiscount);
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
	    <td class='left'></td>
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
  	  <input type='hidden' name='sales[%d][purchaseid]' value='%d' id='sales_%d_purchaseid' />
  	  <input type='hidden' name='sales[%d][category]' value='%d' id='sales_%d_category' />
  	  <input type='hidden' name='sales[%d][productid]' value='%d' id='sales_%d_productid' />
  	  <input type='hidden' name='sales[%d][volume_discount]' id='volume_%d_discount' />
  	  <input type='hidden' name='sales[%d][discType]' value='%d'/>
  	  <input type='hidden' name='sales[%d][discPercentage]' value='%d'/>
  	  <input type='hidden' name='sales[%d][happy_hour_discount]' id='happy_hour_%d_discount'/>
  	  <input type='hidden' name='sales[%d][volume_per_discount]' id='volume_per_%d_discount'/>
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
	  $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $product['name'], $price, $i, $i, $price, $i, $i, $savedGrams[$i], $i, $i, $savedEuro[$i], $i, $i, $savedrealGrams[$i], $i, $i, $savedGrams2[$i], $i, $i, $i, $product['name'], $i, $product['purchaseid'], $i, $i, $categoryid, $i, $i, $product['productid'], $i, $i, $i, $i, $discountType ,$i, $dispDiscount1, $i, $i, $i, $i, $i, $i, $i
	  );
// KONSTANT CODE UPDATE END
	  echo $product_row;
	  
	  
  			} // End gram vs unit cat
	
		} // End 'other' cat
		
	} else { // End firstScan
	
	
	
	
		$barcode = $_POST['barcode'];
		
		$userDetails = "SELECT purchaseid, barCode, category FROM purchases WHERE barCode = '$barcode'";
		try
		{
			$result = $pdo3->prepare("$userDetails");
			$result->execute();
			$data = $result->fetchAll();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user1: ' . $e->getMessage();
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
				$error = 'Error fetching user2: ' . $e->getMessage();
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
				$error = 'Error fetching user3: ' . $e->getMessage();
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
				$error = 'Error fetching user4: ' . $e->getMessage();
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
			
			$categoryName = $lang['global-flower'];
			
		} else if ($categoryid == 2) {
			
			$categoryName = $lang['global-extract'];
			
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
				$error = 'Error fetching user5: ' . $e->getMessage();
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
					$error = 'Error fetching user6: ' . $e->getMessage();
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
		
			// ************************************** Calculate discount(s) ****************************************
			
			if ($_SESSION['showOrigPrice'] == 1) {
				
				$normalPrice = "(" . ($product['salesPrice']) . "{$_SESSION['currencyoperator']})";
				
			} else {
			
				$normalPrice = "";
				
			}
						
	 // KONSTANT CODE UPDATE BEGIN
	// Start usergroup discont 
	$usergroupDiscount = "SELECT users.usergroup2,usergroup_discounts.discount_price,usergroup_discounts.discount_percentage FROM users LEFT JOIN usergroup_discounts on usergroup_discounts.usergroup_id = users.usergroup2 WHERE user_id = $user_id";
        try
        {
            $result = $pdo3->prepare("$usergroupDiscount");
            $result->execute();
        }
        catch (PDOException $e)
        {
            $error = 'Error fetching user7: ' . $e->getMessage();
            echo $error;
            exit();
        }
        $rowDU = $result->fetch();
        $dispDiscount = 0;
        //End group discount 
	// Individual purchase discount
        $dispDiscount1 = 0;
        $discountType = 0;
	$selectPurchaseDiscount = "SELECT discount, fijo FROM inddiscounts WHERE user_id = $user_id AND purchaseid = $purchaseid";
		try
		{
			$result = $pdo3->prepare("$selectPurchaseDiscount");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user8: ' . $selectPurchaseDiscount  . $e->getMessage();
				echo $error;
				exit();
		}
	
		$rowD = $result->fetch();
		$prodDiscount = $rowD['discount'];
		$prodFijo = $rowD['fijo'];
	
	if ($prodFijo != 0 || $prodFijo != '') {
		$dispDiscount1 = number_format((($prodFijo * 100) / $product['salesPrice']),2);
	    $discountType = 1;
		$price = number_format($prodFijo,2);
		
	} else if ($prodDiscount != 0 || $prodDiscount != '') {
		$dispDiscount1 = $prodDiscount;
		$discountType = 1;
		$prodDiscount = (100 - $prodDiscount) / 100;
		$price = number_format($product['salesPrice'] * $prodDiscount,2);
		
	} else if ($catDiscount != 0 || $catDiscount != '') {
		$dispDiscount1 = $catDiscount;
		$discountType = 1;
		$catCurrentDiscount = (100 - $catDiscount) / 100;
		$price = number_format($product['salesPrice'] * $catCurrentDiscount,2);
	} else if ($discount != 0) {
		$dispDiscount1 = $discount;
		$discountType = 1;
		$dispDiscount = (100 - $discount) / 100;
		$price = number_format($product['salesPrice'] * $dispDiscount,2);
		
	} else if ($usageType == '1') {
		
		if ($product['medDiscount'] > 0) {
			$dispDiscount1 = $product['medDiscount'];
        	$discountType = 2;
			$dispDiscount = (100 - $product['medDiscount']) / 100;
			$price = number_format($product['salesPrice'] * $dispDiscount,2);
		} else if ($medicalDiscountPercentage == 1) {
			$dispDiscount1 = $medicalDiscountCalc;
        	$discountType = 2;
			$price = number_format(($product['salesPrice']) * $medicalDiscountCalc,2);
		} else {
			$dispDiscount1 = number_format((($medicalDiscount * 100) / $product['salesPrice']),2);
        	$discountType = 2;
			$price = number_format(($product['salesPrice']) - $medicalDiscount,2);
		}
		
	}else if (isset($rowDU['discount_percentage'])){
            if(isset($rowDU['discount_percentage']) && $rowDU['discount_percentage'] > 0){
                $dispDiscount1 = $rowDU['discount_percentage'];
                $discountType = 4;
                $dispDiscount = (100 - $rowDU['discount_percentage']) / 100;
                $price = number_format($product['salesPrice'] * $dispDiscount,2);
            }else{
                if(isset($rowDU['discount_price']) && $rowDU['discount_price'] > 0){
                    $dispDiscount1 = number_format(($rowDU['discount_price'] * 100) / $product['salesPrice'],2);
                    $discountType = 4;
                    $price = number_format(($product['salesPrice']) - $rowDU['discount_price'],2);
                }
            }
	}
	 else {
		
		$price = number_format($product['salesPrice'],2);
		
	}
	//Start happy hour discount
        $hHFlag = 0;
        $getDay = date('l').'s';
        $getTime = date('H:i:s');
	 $hHDiscount = "SELECT * FROM global_happy_hour_discounts where discount_date = 'Every day' and time_from <= '".$getTime."' and time_to > '".$getTime."'";
        try
        {
            $result = $pdo3->prepare("$hHDiscount");
            $result->execute();
        }
        catch (PDOException $e)
        {
            $error = 'Error fetching user9: ' . $e->getMessage();
            echo $error;
            exit();
        }
        $happy_hour_id = 0;
        while ($hHDiscount = $result->fetch()) {
            $happy_hour_id = $hHDiscount['id'];
            if(isset($hHDiscount['discount']) && $hHDiscount['discount'] > 0){
                $dispDiscount1 = $hHDiscount['discount'];
                $discountType = 3;
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
            $error = 'Error fetching user10: ' . $e->getMessage();
            echo $error;
            exit();
        }
        while ($hHDiscount = $result->fetch()) {
            $happy_hour_id = $hHDiscount['id'];
            if(isset($hHDiscount['discount']) && $hHDiscount['discount'] > 0){
                $dispDiscount1 = $hHDiscount['discount'];
                $discountType = 3;
                $dispDiscount = (100 - $hHDiscount['discount']) / 100;
                $price = number_format($product['salesPrice'] * $dispDiscount,2);
            }
        }
        if($happy_hour_id){
            $selectCategoryDiscount = "SELECT discount FROM catdiscounts WHERE categoryid = 1 and happy_hour_id = ".$happy_hour_id;
            try
            {
                $result = $pdo3->prepare("$selectCategoryDiscount");
                $result->execute();
            }
            catch (PDOException $e)
            {
                $error = 'Error fetching user11: ' . $e->getMessage();
                echo $error;
                exit();
            }
            $rowCD = $result->fetch();
            $catDiscount = $rowCD['discount'];
            if ($catDiscount != 0 || $catDiscount != '') {
		$catCurrentDiscount = (100 - $catDiscount) / 100;
                if($catDiscount > $dispDiscount1){
                	 $discountType = 3;
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
                     $discountType = 3;
                    $price = number_format($prodFijoHD,2);
                }                
                
            } else if ($prodDiscountHD != 0 || $prodDiscountHD != '') {
                if($prodDiscountHD > $dispDiscount1){
                    $prodDiscountHD = (100 - $prodDiscountHD) / 100;
                    $discountType = 3;
                    $price = number_format($product['salesPrice'] * $prodDiscountHD,2);
                }
            }
        }
        
			
		$savedGrams2[$i] = '';
		$displayGift = 'display: none;';
			
			if ($_SESSION['dispensaryGift'] == 1 && $_SESSION['menuType'] == 1) {
				$giftOption = "<img src='images/gift-small.png' id='free%d' style='margin-bottom: -15px; margin-left: 5px;' />";
			} else if ($_SESSION['dispensaryGift'] == 1 && $_SESSION['menuType'] == 0) {
				if ($_SESSION['realWeight'] == 0) {
					$giftOption = "<img src='images/gift-big.png' id='free%d' style='margin-bottom: 0; margin-left: 5px; margin-top: 10px; margin-bottom: 6px;' />";
				} else {
					$giftOption = "<img src='images/gift-small.png' id='free%d' style='margin-bottom: 0; margin-left: -5px; margin-top: 10px; margin-bottom: 6px;' />";
				}
			} else {
				$giftOption = "<span style='display: block; height: 10px;'></span><input type='hidden' value='%d' />";
			}
			
			if ($categoryid > 2 && $type == 0) {
				
				
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
                var volDiscount = $('#volume_%d_discount').val().delay(700);
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
        
   function compute3() {
	   	  $(this).val($(this).val().replace(/,/g, '.'));
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
	                    	var dataFilter = data.split('_');
	                    	var firstprice = dataFilter[0];
	                    	var discountPercent = dataFilter[1];
	                    	var volumePercent = dataFilter[2];
	                        var roundedtotal = parseFloat(firstprice).toFixed(2);
	                        $('#volume_%d_discount').val(roundedtotal);
	                        $('#happy_hour_%d_discount').val(discountPercent);
	                        $('#volume_per_%d_discount').val(volumePercent);
	                  	    $('#eurcalc%d').val(roundedtotal);
	                    }else{
	                        $('#volume_%d_discount').val('');
	                        $('#happy_hour_%d_discount').val('');
	                        $('#volume_per_%d_discount').val('');
	                    }
                    }
                }); 
            }else{
            	$('#volume_%d_discount').val('');
            }
        }
        $('#grcalc%d').bind('keyup blur change', applyVolumeDiscount);
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
	    <td class='left'></td>
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
  	  <input type='hidden' name='sales[%d][volume_discount]' id='volume_%d_discount' />
  	  <input type='hidden' name='sales[%d][discType]' value='%d'/>
  	  <input type='hidden' name='sales[%d][discPercentage]' value='%d'/>
  	  <input type='hidden' name='sales[%d][happy_hour_discount]' id='happy_hour_%d_discount'/>
  	  <input type='hidden' name='sales[%d][volume_per_discount]' id='volume_per_%d_discount'/>
          <!-- // KONSTANT CODE UPDATE END -->
",  //KONSTANT CODE UPDATE BEGIN 
	  $key, $key, $key, $key, $key, $key, $key, $key, $key, $key, $key, $key, $key, $key, $key ,$key ,$key, $key, $key, $key, $key, $key, $key, $key, $key, $key, $key, $key, $key, $key, $key, $key, $product['name'], $price, $key, $key, $price, $key, $key, $grams, $key, $key, $euro, $key, $key, $realGrams, $key, $key, $product['name'], $key, $product['purchaseid'], $key, $key, $categoryid, $key, $key, $product['productid'], $key, $key, $key, $key, $discountType ,$key, $dispDiscount1, $key, $key, $key, $key
	  );
// KONSTANT CODE UPDATE END
        
	  
  		} else {
	  		
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
                var volDiscount = $('#volume_%d_discount').val().delay(700);
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
	    // KONSTANT CODE UPDATE BEGIN4
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
	                    	var dataFilter = data.split('_');
	                    	var firstprice = dataFilter[0];
	                    	var discountPercent = dataFilter[1];
	                    	var volumePercent = dataFilter[2];
	                        var roundedtotal = parseFloat(firstprice).toFixed(2);
	                        $('#volume_%d_discount').val(roundedtotal);
	                        $('#happy_hour_%d_discount').val(discountPercent);
	                        $('#volume_per_%d_discount').val(discountPercent);
	                  	    $('#eurcalc%d').val(roundedtotal);
	                    }else{
	                        $('#volume_%d_discount').val('');
	                        $('#happy_hour_%d_discount').val('');
	                        $('#volume_per_%d_discount').val('');
	                    }
	                }
	            }); 
	        }else{
	        	$('#volume_%d_discount').val('');
	        }
	    }
	    $('#grcalc%d').bind('keyup blur change', applyVolumeDiscount);
	    // KONSTANT CODE UPDATE END
        $('#realgrcalc%d').bind('keypress keyup blur change', compute3);
        $('#grcalc%d').bind('keypress keyup blur change', compute);
        $('#eurcalc%d').bind('keypress keyup blur change', compute2);
        $('#grcalcB%d').bind('keyup blur change', compute3);
       
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
	    <td class='left'>%s<br>%s</td>
	    <td>
	    	<span class='relativeitem'>$commentRead</span>&nbsp;
	    	<span class='relativeitem'>$commentReadM</span>&nbsp;&nbsp;&nbsp;
	    	<strong style='font-size: 15px;'>%0.02f {$_SESSION['currencyoperator']}</strong> <input type='hidden' id='ppgcalc%d' name='sales[%d][ppg]' value='%0.02f' />&nbsp;&nbsp;
	    	<span class='firsttext' style='font-size: 15px;'>$normalPrice</span>&nbsp;&nbsp;&nbsp;	    
	    	<span class='smallgreen' style='text-transform: none;'>$stockDisplay</span>
	    	<br>
	    	<input type='text' lang='nb' class='twoDigit defaultinput centered calc onScreenKeypad c$purchaseid' id='grcalc%d' name='sales[%d][grams]' placeholder='Gr.' step='0.01' value='%s' />
	    	<input type='text' lang='nb' class='twoDigit defaultinput centered calc2 onScreenKeypad' id='eurcalc%d' name='sales[%d][euro]' placeholder='{$_SESSION['currencyoperator']}' step='0.01' value='%s' />
	    	<input type='text' lang='nb' class='fourDigit defaultinput centered calc5' id='realgrcalc%d' name='sales[%d][realGrams]' placeholder='Real gr.' step='0.01'  value='%s' />
	    	<input type='text' lang='nb' class='twoDigit defaultinput centered calc calc6 onScreenKeypad' id='grcalcB%d' name='sales[%d][grams2]' placeholder='Gr.' step='0.01' value='%s' style='background-color: #f4b146; $displayGift'  />$giftOption
	    	  &nbsp;&nbsp;<img src='images/delete.png' width='17' style='cursor: pointer;' class='remove_pr' id='zero%d' />
	    </td>
	   </tr>
  	  <input type='hidden' name='sales[%d][name]' value='%s' />
          <!-- // KONSTANT CODE UPDATE BEGIN  -->
  	  <input type='hidden' name='sales[%d][purchaseid]' value='%d' id='sales_%d_purchaseid' />
  	  <input type='hidden' name='sales[%d][category]' value='%d' id='sales_%d_category' />
  	  <input type='hidden' name='sales[%d][productid]' value='%d' id='sales_%d_productid' />
  	  <input type='hidden' name='sales[%d][volume_discount]' id='volume_%d_discount' />
  	  <input type='hidden' name='sales[%d][discType]' value='%d'/>
  	  <input type='hidden' name='sales[%d][discPercentage]' value='%d'/>
  	  <input type='hidden' name='sales[%d][happy_hour_discount]' id='happy_hour_%d_discount'/>
  	  <input type='hidden' name='sales[%d][volume_per_discount]' id='volume_per_%d_discount'/>
          <!-- // KONSTANT CODE UPDATE END -->
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
	  $key, $key, $key, $key, $key, $key, $key, $key, $key, $key, $key, $key, $key, $key, $key, $key, $key, $key, $key, $key, $key, $key, $key, $key, $key, $key, $key, $key, $key, $key, $key, $key, $key, $key, $key, $key, $key, $key, $key, $key, $name, $growtype, $flowertype, $price, $key, $key, $price, $key, $key, $grams, $key, $key, $euro, $key, $key, $realGrams, $key, $key, $savedGrams2[$key], $key, $key, $key, $name, $key, $product['purchaseid'], $key, $key, $categoryid, $key, $key, $product['flowerid'], $key, $key, $key, $key, $discountType, $key, $dispDiscount1, $key, $key, $key, $key, $key, $key, $key
	  );

	   
  		}
	   
  	  		echo $product_row;
				
				
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
				$error = 'Error fetching user1: ' . $e->getMessage();
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
				$error = 'Error fetching user2: ' . $e->getMessage();
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
				$error = 'Error fetching user3: ' . $e->getMessage();
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
				$error = 'Error fetching user4: ' . $e->getMessage();
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
			
			$categoryName = $lang['global-flower'];
			
		} else if ($categoryid == 2) {
			
			$categoryName = $lang['global-extract'];
			
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
				$error = 'Error fetching user5: ' . $e->getMessage();
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
					$error = 'Error fetching user6: ' . $e->getMessage();
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

		
			// ************************************** Calculate discount(s) ****************************************
			
			if ($_SESSION['showOrigPrice'] == 1) {
				
				$normalPrice = "(" . ($product['salesPrice']) . "{$_SESSION['currencyoperator']})";
				
			} else {
			
				$normalPrice = "";
				
			}
						
	 // KONSTANT CODE UPDATE BEGIN
	// Start usergroup discont 
	$usergroupDiscount = "SELECT users.usergroup2,usergroup_discounts.discount_price,usergroup_discounts.discount_percentage FROM users LEFT JOIN usergroup_discounts on usergroup_discounts.usergroup_id = users.usergroup2 WHERE user_id = $user_id";
        try
        {
            $result = $pdo3->prepare("$usergroupDiscount");
            $result->execute();
        }
        catch (PDOException $e)
        {
            $error = 'Error fetching user7: ' . $e->getMessage();
            echo $error;
            exit();
        }
        $rowDU = $result->fetch();
        $dispDiscount = 0;
        //End group discount 
	// Individual purchase discount
        $dispDiscount1 = 0;
        $discountType = 0;
	$selectPurchaseDiscount = "SELECT discount, fijo FROM inddiscounts WHERE user_id = $user_id AND purchaseid = $purchaseid";
		try
		{
			$result = $pdo3->prepare("$selectPurchaseDiscount");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user8: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$rowD = $result->fetch();
		$prodDiscount = $rowD['discount'];
		$prodFijo = $rowD['fijo'];
	
	if ($prodFijo != 0 || $prodFijo != '') {
		$dispDiscount1 = number_format((($prodFijo * 100) / $product['salesPrice']),2);
	    $discountType = 1;
		$price = number_format($prodFijo,2);
		
	} else if ($prodDiscount != 0 || $prodDiscount != '') {
		$dispDiscount1 = $prodDiscount;
		$discountType = 1;
		$prodDiscount = (100 - $prodDiscount) / 100;
		$price = number_format($product['salesPrice'] * $prodDiscount,2);
		
	} else if ($catDiscount != 0 || $catDiscount != '') {
		$dispDiscount1 = $catDiscount;
		$discountType = 1;
		$catCurrentDiscount = (100 - $catDiscount) / 100;
		$price = number_format($product['salesPrice'] * $catCurrentDiscount,2);
	} else if ($discount != 0) {
		$dispDiscount1 = $discount;
		$discountType = 1;
		$dispDiscount = (100 - $discount) / 100;
		$price = number_format($product['salesPrice'] * $dispDiscount,2);
		
	} else if ($usageType == '1') {
		
		if ($product['medDiscount'] > 0) {
			$dispDiscount1 = $product['medDiscount'];
        	$discountType = 2;
			$dispDiscount = (100 - $product['medDiscount']) / 100;
			$price = number_format($product['salesPrice'] * $dispDiscount,2);
		} else if ($medicalDiscountPercentage == 1) {
			$dispDiscount1 = $medicalDiscountCalc;
        	$discountType = 2;
			$price = number_format(($product['salesPrice']) * $medicalDiscountCalc,2);
		} else {
			$dispDiscount1 = number_format((($medicalDiscount * 100) / $product['salesPrice']),2);
        	$discountType = 2;
			$price = number_format(($product['salesPrice']) - $medicalDiscount,2);
		}
		
	}else if (isset($rowDU['discount_percentage'])){
            if(isset($rowDU['discount_percentage']) && $rowDU['discount_percentage'] > 0){
                $dispDiscount1 = $rowDU['discount_percentage'];
                $discountType = 4;
                $dispDiscount = (100 - $rowDU['discount_percentage']) / 100;
                $price = number_format($product['salesPrice'] * $dispDiscount,2);
            }else{
                if(isset($rowDU['discount_price']) && $rowDU['discount_price'] > 0){
                    $dispDiscount1 = number_format(($rowDU['discount_price'] * 100) / $product['salesPrice'],2);
                    $discountType = 4;
                    $price = number_format(($product['salesPrice']) - $rowDU['discount_price'],2);
                }
            }
	}
	 else {
		
		$price = number_format($product['salesPrice'],2);
		
	}
	//Start happy hour discount
        $hHFlag = 0;
        $getDay = date('l').'s';
        $getTime = date('H:i:s');
	 $hHDiscount = "SELECT * FROM global_happy_hour_discounts where discount_date = 'Every day' and time_from <= '".$getTime."' and time_to > '".$getTime."'";
        try
        {
            $result = $pdo3->prepare("$hHDiscount");
            $result->execute();
        }
        catch (PDOException $e)
        {
            $error = 'Error fetching user9: ' . $e->getMessage();
            echo $error;
            exit();
        }
        $happy_hour_id = 0;
        while ($hHDiscount = $result->fetch()) {
            $happy_hour_id = $hHDiscount['id'];
            if(isset($hHDiscount['discount']) && $hHDiscount['discount'] > 0){
                $dispDiscount1 = $hHDiscount['discount'];
                $discountType = 3;
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
            $error = 'Error fetching user10: ' . $e->getMessage();
            echo $error;
            exit();
        }
        while ($hHDiscount = $result->fetch()) {
            $happy_hour_id = $hHDiscount['id'];
            if(isset($hHDiscount['discount']) && $hHDiscount['discount'] > 0){
                $dispDiscount1 = $hHDiscount['discount'];
                $discountType = 3;
                $dispDiscount = (100 - $hHDiscount['discount']) / 100;
                $price = number_format($product['salesPrice'] * $dispDiscount,2);
            }
        }
        if($happy_hour_id){
            $selectCategoryDiscount = "SELECT discount FROM catdiscounts WHERE categoryid = 1 and happy_hour_id = ".$happy_hour_id;
            try
            {
                $result = $pdo3->prepare("$selectCategoryDiscount");
                $result->execute();
            }
            catch (PDOException $e)
            {
                $error = 'Error fetching user11: ' . $e->getMessage();
                echo $error;
                exit();
            }
            $rowCD = $result->fetch();
            $catDiscount = $rowCD['discount'];
            if ($catDiscount != 0 || $catDiscount != '') {
		$catCurrentDiscount = (100 - $catDiscount) / 100;
                if($catDiscount > $dispDiscount1){
                	 $discountType = 3;
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
                $error = 'Error fetching user12: ' . $e->getMessage();
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
                     $discountType = 3;
                    $price = number_format($prodFijoHD,2);
                }                
                
            } else if ($prodDiscountHD != 0 || $prodDiscountHD != '') {
                if($prodDiscountHD > $dispDiscount1){
                    $prodDiscountHD = (100 - $prodDiscountHD) / 100;
                    $discountType = 3;
                    $price = number_format($product['salesPrice'] * $prodDiscountHD,2);
                }
            }
        }
        
        
			
		$savedGrams2[$i] = '';
		$displayGift = 'display: none;';
			
			if ($_SESSION['dispensaryGift'] == 1 && $_SESSION['menuType'] == 1) {
				$giftOption = "<img src='images/gift-small.png' id='free%d' style='margin-bottom: -15px; margin-left: 5px;' />";
			} else if ($_SESSION['dispensaryGift'] == 1 && $_SESSION['menuType'] == 0) {
				if ($_SESSION['realWeight'] == 0) {
					$giftOption = "<img src='images/gift-big.png' id='free%d' style='margin-bottom: 0; margin-left: 5px; margin-top: 10px; margin-bottom: 6px;' />";
				} else {
					$giftOption = "<img src='images/gift-small.png' id='free%d' style='margin-bottom: 0; margin-left: -5px; margin-top: 10px; margin-bottom: 6px;' />";
				}
			} else {
				$giftOption = "<span style='display: block; height: 10px;'></span><input type='hidden' value='%d' />";
			}
			
			
			if ($categoryid > 2 && $type == 0) {
				
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
                var volDiscount = $('#volume_%d_discount').val().delay(700);
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
        
   function compute3() {
	   	  $(this).val($(this).val().replace(/,/g, '.'));
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
	                    	var dataFilter = data.split('_');
	                    	var firstprice = dataFilter[0];
	                    	var discountPercent = dataFilter[1];
	                    	var volumePercent = dataFilter[2];
	                        var roundedtotal = parseFloat(firstprice).toFixed(2);
	                        $('#volume_%d_discount').val(roundedtotal);
	                        $('#happy_hour_%d_discount').val(discountPercent);
	                        $('#volume_per_%d_discount').val(volumePercent);
	                  	    $('#eurcalc%d').val(roundedtotal);
	                    }else{
	                        $('#volume_%d_discount').val('');
	                        $('#happy_hour_%d_discount').val('');
	                        $('#volume_per_%d_discount').val('');
	                    }
                    }
                }); 
            }else{
            	$('#volume_%d_discount').val('');
            }
        }
        $('#grcalc%d').bind('keyup blur change', applyVolumeDiscount);
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
	    <td class='left'></td>
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
  	  <input type='hidden' name='sales[%d][volume_discount]' id='volume_%d_discount' />
  	  <input type='hidden' name='sales[%d][discType]' value='%d'/>
  	  <input type='hidden' name='sales[%d][discPercentage]' value='%d'/>
  	  <input type='hidden' name='sales[%d][happy_hour_discount]' id='happy_hour_%d_discount'/>
  	  <input type='hidden' name='sales[%d][volume_per_discount]' id='volume_per_%d_discount'/>
          <!-- // KONSTANT CODE UPDATE END -->
",  //KONSTANT CODE UPDATE BEGIN 
	  $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i ,$i ,$i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $product['name'], $price, $i, $i, $price, $i, $i, $savedGrams[$i], $i, $i, $savedEuro[$i], $i, $i, $savedGrams2[$i],$i, $i, $product['name'], $i, $product['purchaseid'], $i, $i, $categoryid, $i, $i, $product['productid'], $i, $i, $i, $i, $discountType ,$i, $dispDiscount1, $i, $i, $i, $i
	  );
// KONSTANT CODE UPDATE END
        
	  
  		} else {
	  		
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
                var volDiscount = $('#volume_%d_discount').val().delay(700);
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
	    // KONSTANT CODE UPDATE BEGIN4
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
	                    	var dataFilter = data.split('_');
	                    	var firstprice = dataFilter[0];
	                    	var discountPercent = dataFilter[1];
	                    	var volumePercent = dataFilter[2];
	                        var roundedtotal = parseFloat(firstprice).toFixed(2);
	                        $('#volume_%d_discount').val(roundedtotal);
	                        $('#happy_hour_%d_discount').val(discountPercent);
	                        $('#volume_per_%d_discount').val(discountPercent);
	                  	    $('#eurcalc%d').val(roundedtotal);
	                    }else{
	                        $('#volume_%d_discount').val('');
	                        $('#happy_hour_%d_discount').val('');
	                        $('#volume_per_%d_discount').val('');
	                    }
	                }
	            }); 
	        }else{
	        	$('#volume_%d_discount').val('');
	        }
	    }
	    $('#grcalc%d').bind('keyup blur change', applyVolumeDiscount);
	    // KONSTANT CODE UPDATE END
        $('#realgrcalc%d').bind('keypress keyup blur change', compute3);
        $('#grcalc%d').bind('keypress keyup blur change', compute);
        $('#eurcalc%d').bind('keypress keyup blur change', compute2);
        $('#grcalcB%d').bind('keyup blur change', compute3);
       
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
	    <td class='left'>%s<br>%s</td>
	    <td>
	    	<span class='relativeitem'>$commentRead</span>&nbsp;
	    	<span class='relativeitem'>$commentReadM</span>&nbsp;&nbsp;&nbsp;
	    	<strong style='font-size: 15px;'>%0.02f {$_SESSION['currencyoperator']}</strong> <input type='hidden' id='ppgcalc%d' name='sales[%d][ppg]' value='%0.02f' />&nbsp;&nbsp;
	    	<span class='firsttext' style='font-size: 15px;'>$normalPrice</span>&nbsp;&nbsp;&nbsp;	    
	    	<span class='smallgreen' style='text-transform: none;'>$stockDisplay</span>
	    	<br>
	    	<input type='text' lang='nb' class='twoDigit defaultinput centered calc onScreenKeypad c$purchaseid' id='grcalc%d' name='sales[%d][grams]' placeholder='Gr.' step='0.01' value='%s' />
	    	<input type='text' lang='nb' class='twoDigit defaultinput centered calc2 onScreenKeypad' id='eurcalc%d' name='sales[%d][euro]' placeholder='{$_SESSION['currencyoperator']}' step='0.01' value='%s' />
	    	<input type='text' lang='nb' class='fourDigit defaultinput centered calc5' id='realgrcalc%d' name='sales[%d][realGrams]' placeholder='Real gr.' step='0.01'  value='%s' />
	    	<input type='text' lang='nb' class='twoDigit defaultinput centered calc calc6 onScreenKeypad' id='grcalcB%d' name='sales[%d][grams2]' placeholder='Gr.' step='0.01' value='%s' style='background-color: #f4b146; $displayGift'  />$giftOption
	    	  &nbsp;&nbsp;<img src='images/delete.png' width='17' style='cursor: pointer;' class='remove_pr' id='zero%d' />
	    </td>
	   </tr>
  	  <input type='hidden' name='sales[%d][name]' value='%s' />
          <!-- // KONSTANT CODE UPDATE BEGIN  -->
  	  <input type='hidden' name='sales[%d][purchaseid]' value='%d' id='sales_%d_purchaseid' />
  	  <input type='hidden' name='sales[%d][category]' value='%d' id='sales_%d_category' />
  	  <input type='hidden' name='sales[%d][productid]' value='%d' id='sales_%d_productid' />
  	  <input type='hidden' name='sales[%d][volume_discount]' id='volume_%d_discount' />
  	  <input type='hidden' name='sales[%d][discType]' value='%d'/>
  	  <input type='hidden' name='sales[%d][discPercentage]' value='%d'/>
  	  <input type='hidden' name='sales[%d][happy_hour_discount]' id='happy_hour_%d_discount'/>
  	  <input type='hidden' name='sales[%d][volume_per_discount]' id='volume_per_%d_discount'/>
          <!-- // KONSTANT CODE UPDATE END -->
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
	  $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $name, $growtype, $flowertype, $price, $i, $i, $price, $i, $i, $savedGrams[$i], $i, $i, $savedEuro[$i], $i, $i, $savedrealGrams[$i], $i, $i, $savedGrams2[$i], $i, $i, $i, $name, $i, $product['purchaseid'], $i, $i, $categoryid, $i, $i, $product['flowerid'], $i, $i, $i, $i, $discountType, $i, $dispDiscount1, $i, $i, $i, $i, $i, $i, $i
	  );
	  
  		}
	   
  	  		echo $product_row;
				
				
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
   <button type="submit" class='oneClick' name='oneClick' id="submitButton2" value='2'>Actualizar</button><br /><br />
</center>

<?php if ($_SESSION['dispsig'] == 1) { ?>
<INPUT TYPE=HIDDEN NAME="bioSigData">
<INPUT TYPE=HIDDEN NAME="sigImgData">
<INPUT TYPE=HIDDEN NAME="sigStringData" />
<INPUT TYPE=HIDDEN NAME="sigImageData" />
<?php } ?>

</form>

</div>
<script type="text/javascript">
	$(document).ready(function(){
		$("#submitButton").click(function(){
			var barcode = $("#focus").val();
		        //alert(barcode);
		     if(barcode != ''){
				$("#sub_hidden").val(0);
			}else{
				$("#sub_hidden").val(1);
			}
		});
		$("#submitButton2").click(function(){
			$("#sub_hidden").val(0);
		});

		$(document).keypress(function(event){
		    var keycode = (event.keyCode ? event.keyCode : event.which);
		    if(keycode == '13'){
		        var barcode = $("#focus").val();
		        //alert(barcode);
		        if(barcode != ''){
		        	$("#sub_hidden").val(0);
		        	//event.preventDefault();
    			//	return false;
		        }   
		    }
		});

	});
</script>
<?php
	displayFooter();
