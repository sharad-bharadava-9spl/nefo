<?php
	
	require_once 'cOnfig/connection.php';
	require_once 'cOnfig/view.php';
	require_once 'cOnfig/authenticate.php';
	require_once 'cOnfig/languages/common.php';
	
	session_start();
	$accessLevel = '3';
	
	// Authenticate & authorize
	authorizeUser($accessLevel);
	
	$domain = $_SESSION['domain'];
	// Retrieve System settings
	getSettings();
	
	// Did this page re-submit with a form? If so, check & store details
	if (isset($_POST['dispense'])) {
		
  	    $user_id = $_POST['user_id'];
		$euroTOT = $_POST['euroTOT'];
		$eurcalcTOT = $_POST['eurcalcTOT'];
		$unitsTOT = $_POST['unitsTOT'];
		$paidTOT = $_POST['paidTOT'];
		$adminComment = $_POST['adminComment'];
		$newcredit = $_POST['newcredit'];
		$credit = $_POST['credit'];
		$realCredit = $_POST['realCredit'];
		$totDiscountInput = $_POST['totDiscountInput'];	
		$realNewCredit = $_POST['realNewCredit'];
		$pmtType = $_POST['pmtType'];
		$eurdiscount = $_POST['eurdiscount'];
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
			$query = sprintf("INSERT INTO b_sales (saletime, userid, amount, unitsTot, adminComment, creditBefore, creditAfter, direct, discount, discounteur) VALUES ('%s', '%d', '%f', '%f', '%s', '%f', '%f', '%d', '%f', '%f');",
			 $saletime, $user_id, $eurcalcTOT, $unitsTOT, $adminComment, $creditLookup, $newCreditCalc, $pmtType, $totDiscountInput, $eurdiscount);
	
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
			$query = sprintf("INSERT INTO b_sales (saletime, userid, amount, unitsTot, adminComment, direct, discount, discounteur) VALUES ('%s', '%d', '%f', '%f', '%s', '%d', '%f', '%f');",
			 $saletime, $user_id, $eurcalcTOT, $unitsTOT, $adminComment, $pmtType, $totDiscountInput, $eurdiscount);
	
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
		$gramsTot = $grams + $grams2;

		if ($totDiscountInput > 0) {
			$euro = $sale['euro'] * ((100 - $totDiscountInput) / 100);
		} else {
			$euro = $sale['euro'];
		}
		if ($gramsTot > 0) {
    	
 			if ($grams > 0 && $grams2 > 0) {
	 			
		   		// Query to add new sale to salesdetails table - ? arguments
			  	$query = sprintf("INSERT INTO b_salesdetails (saleid, category, productid, quantity, amount, purchaseid) VALUES ('%d', '%d', '%d', '%f', '%f', '%d');",
			  	$saleid, $category, $productid, $grams, $euro, $purchaseid);
			  
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
			  	$query = sprintf("INSERT INTO b_salesdetails (saleid, category, productid, quantity, amount, purchaseid) VALUES ('%d', '%d', '%d', '%f', '%f', '%d');",
			  	$saleid, $category, $productid, $grams2, '0', $purchaseid);
			  
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
			  	$query = sprintf("INSERT INTO f_b_salesdetails (saleid, category, productid, quantity, amount, purchaseid) VALUES ('%d', '%d', '%d', '%f', '%f', '%d');",
			  	$saleid, $category, $productid, $grams, $euro, $purchaseid);
			  
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
			  	$query = sprintf("INSERT INTO f_b_salesdetails (saleid, category, productid, quantity, amount, purchaseid) VALUES ('%d', '%d', '%d', '%f', '%f', '%d');",
			  	$saleid, $category, $productid, $grams2, '0', $purchaseid);
			  
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
			  	$query = sprintf("INSERT INTO b_salesdetails (saleid, category, productid, quantity, amount, purchaseid) VALUES ('%d', '%d', '%d', '%f', '%f', '%d');",
			  	$saleid, $category, $productid, $grams, $euro, $purchaseid);
			  
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
			  	$query = sprintf("INSERT INTO f_b_salesdetails (saleid, category, productid, quantity, amount, purchaseid) VALUES ('%d', '%d', '%d', '%f', '%f', '%d');",
			  	$saleid, $category, $productid, $grams, $euro, $purchaseid);
			  
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
		11, $logTime, $user_id, $_SESSION['user_id'], $eurcalcTOT, $creditLookup, $newCreditCalc);
		
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

		$query = sprintf("INSERT INTO f_log (logtype, logtime, user_id, operator, amount, oldCredit, newCredit) VALUES ('%d', '%s', '%d', '%d', '%f', '%f', '%f');",
		11, $logTime, $user_id, $_SESSION['user_id'], $eurcalcTOT, $creditLookup, $newCreditCalc);
		
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
		$_SESSION['successMessage'] = "Sale(s) added succesfully!";
		header("Location: bar-sale.php?saleid=" . $saleid);
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
	$userDetails2 = "SELECT memberno, paidUntil, userGroup, first_name, last_name, datediff(curdate(),$memberSince) AS daysMember, paymentWarning, paymentWarningDate, mconsumption, day, month, year, credit, creditEligible, discountBar, photoExt, cardid, exento, starCat, nationality FROM users WHERE user_id = '{$user_id}'";
	
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
		$discount = $row2['discountBar'];
		$photoExt = $row2['photoExt'];
		$cardid = $row2['cardid'];
		$exento = $row2['exento'];
		$starCat = $row2['starCat'];	
		$nationality = $row2['nationality'];	
		
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
			$giftOption = "<img src='images/gift-big.png' id='free%d' style='margin-bottom: 0; margin-left: 5px; margin-top: 10px; margin-bottom: 6px;' />";
		} else {
			$giftOption = "<span style='display: block; height: 10px;'></span><input type='hidden' value='%d' />";
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
	
	
		  $( function() {
	    $( "#datepicker" ).datepicker({
			dateFormat: "dd-mm-yy"
	    });
	  });

	
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
<div id='dispensarymain'>

<form id="registerForm" action="" method="POST">


<div id="memberbox">
<div id="memberboxshade">
</div>
<?php

	if ($domain == 'cloud') {
		
		$topimg = "images/_$domain/ID/$user_id-front.jpg";
		
	} else {

		$topimg = "images/_$domain/members/$user_id.$photoExt";
	
	}
	
	if (!file_exists($topimg)) {
		$topimg = 'images/silhouette-new.png';
	}
	
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
	




	
	echo "
	<a href='profile.php?user_id=$user_id'>
	 <span class='firsttext'>#$memberno</span><br />
     <a href='#' id='chipClick'><img src='images/rfid-new.png' onclick='event.preventDefault();' /></a> <span class='nametext2'>$first_name $last_name</span>
    </a><br />
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
         $bday
         $expiry
         $consumptionwarning
        </table>
       </td>
      </tr>
     </table>";

	?>
	<div class="clearFloat"></div>
 	   <input type='hidden' id='eurcalcTOTexp' name='euroTOT' />
 	   <input type='hidden' name='dispense' value='done' /> 	   
  	   <input type='hidden' name='user_id' value='<?php echo $user_id; ?>' />
  	   <input type='hidden' name='realCredit' id='realCredit' value='<?php echo $realCredit; ?>' />
  	   <input type='hidden' name='realNewCredit' id='realNewCredit' value='<?php echo $realNewCredit; ?>' />
  	   <input type='hidden' name='totDiscountInput' id='totDiscountInput' value='' />
<br />  	   
  	   <table id='memberBoxTable'>
  	    <tr>
  	     <td></td>
  	     <td class='dispensetd'><input type='number' lang='nb' class='blankinput first notZero' id='unitcalcTOT' name='unitsTOT' value="0" readonly /></td>
  	     <td class='dispensetd'><input type='number' lang='nb' class='blankinput' id='eurcalcTOT' name='eurcalcTOT' value="0" readonly /> &euro;</td>
	    </tr>  	   
  	    <tr>
  	     <td class='dispensetd' colspan='3'>
  	      <table style='width: 100%;'>
  	       <tr>
  	        <td class='saldoheader'><?php echo $lang['global-credit']; ?>:</td>
  	        <td>
  	        <?php if ($credit < 0) { ?>
  	        <input type='number' lang='nb' class="specialInput" style='color: red !important;' id='credit' name='credit' value='0' readonly /> &euro;
  	        <?php } else { ?>
  	        <input type='number' lang='nb' class="specialInput" id='credit' name='credit' value='<?php echo $credit; ?>' readonly /> &euro;
  	        <?php } ?>
  	        </td>
  	        <td class='saldoheader' style='text-align: right;'><?php echo $lang['new-normal']; ?>:</td>
  	        <td>
  	        <?php if ($credit < 0) { ?>
  	        <input type='number' lang='nb' class='specialInput' style='color: red !important;' id='newcredit' name='newcredit' value='0' readonly /> &euro;
  	        <?php } else { ?>
  	        <input type='number' lang='nb' class='specialInput' id='newcredit' name='newcredit' value='<?php echo $credit; ?>' readonly /> &euro;
  	        <?php } ?>
  	        </td>
  	       </tr>
<?php if ($_SESSION['dispDonate'] == 1) { ?>
  	       <tr style='display: none;' class='donateField'>
  	        <td class='saldoheader'><?php echo $lang['donate']; ?>:</td>
  	        <td style='text-align: right; padding-right: 13px;'><input type='number' lang='nb' class='twoDigit defaultinput-no-margin' id='newDonation' name='newDonation' value='0' /> &euro;</td>
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
        <span style='color: #656d60;'><?php echo $lang['member-orcaps'] . " " . $lang['member-discount']; ?> &euro;:&nbsp; <input type='number' name='eurdiscount' id='eurdiscount' class='twoDigit defaultinput-no-margin' style='margin: 5px 0;' /></span>
        
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
  	         <input type='number' lang='nb' class='twoDigit defaultinput-no-margin' id='paid' placeholder='&euro;' />
  	        </td>
  	        <td class='saldoheader' style='text-align: right;'><?php echo $lang['change-money']; ?>:</td>
  	        <td>
   	         <input type='number' lang='nb' class='twoDigit defaultinput-no-margin' id='change' placeholder='&euro;' readonly  />
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
<button type="submit" class='oneClick cta6' name='oneClick' id='submitButton'><img src='images/checkmark-new.png' style='margin-bottom: -10px;' />&nbsp;<?php echo $lang['global-save']; ?></button><br /><br />
<a href="#" class='cta7' onclick="event.preventDefault(); document.getElementById('registerForm').reset();"><img src='images/cancel.png' style='margin-bottom: -6px;' />&nbsp;<?php echo $lang['global-delete']; ?></a><br /><br />
<center><a href="barcode-bar.php?user_id=<?php echo $user_id; ?>"><img src="images/barcode-new.png" /></a></center>
</div> <!-- end memberbox -->
  	   

<div class="clearfloat"></div>


<?php
		
			
	$selectCategory = "SELECT id, name FROM b_categories ORDER BY name ASC;";
	
		try
		{
			$resultCats = $pdo3->prepare("$selectCategory");
			$resultCats->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
		
		
					$i = 0;
		while ($category = $resultCats->fetch()) {
			
			$categoryid = $category['id'];
			$name = $category['name'];
			
			$checkProducts = "SELECT productid from b_purchases WHERE category = $categoryid AND closedAt IS NULL AND inMenu = 1";
			
			try
			{
				$result = $pdo3->prepare("$checkProducts");
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
			
  			$selectServices = "SELECT pr.productid, pr.name, pr.description, p.purchaseid, p.salesPrice, p.purchaseQuantity, pr.photoExt FROM b_products pr, b_purchases p WHERE p.category = $categoryid AND p.productid = pr.productid AND p.closedAt IS NULL AND inMenu = 1 ORDER BY p.salesPrice ASC;";
	
			try
			{
				$resultServices = $pdo3->prepare("$selectServices");
				$resultServices->execute();
			}
			catch (PDOException $e)
			{
					$error = 'Error fetching user: ' . $e->getMessage();
					echo $error;
					exit();
			}
			
			
				
				$menuType = $_SESSION['barMenuType'];
				
				if ($menuType == 1) {
					echo "<div class='leftfloat'>";
				}
		
				echo "<h3 class='title'>$name</h3>";
					
				if ($menuType == 1) {
					echo "<table class='default nonhover'>";
				}
				
			// Look up discount for THIS category
			$selectCategoryDiscount = "SELECT discount FROM b_catdiscounts WHERE user_id = $user_id AND categoryid = $categoryid";
			
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

				
			while ($service = $resultServices->fetch()) {
				
				$name = $service['name'];
				$categoryID = $service['category'];
				$productid = $service['productid'];
				$photoExt = $service['photoExt'];
				$purchaseid = $service['purchaseid'];
				$description = $service['description'];
				
			// ************************************** Calculate discount(s) ****************************************
			
			if ($_SESSION['showOrigPriceBar'] == 1) {
				
				$normalPrice = "(" . ($service['salesPrice']) . "&euro;)";
				
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
                        $dispDiscount = $dispDiscount1 = 0;
                        
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
				$dispDiscount1 = number_format((($prodFijo * 100) / $service['salesPrice']),2);
				$price = number_format($prodFijo,2);
				
			} else if ($prodDiscount != 0 || $prodDiscount != '') {
                		$dispDiscount1 = $prodDiscount;
				$prodDiscount = (100 - $prodDiscount) / 100;
				$price = number_format(($service['salesPrice'] * $prodDiscount),2);
				
			} else if ($catDiscount != 0 || $catDiscount != '') {
                    		$dispDiscount1 = $catDiscount;
				$catCurrentDiscount = (100 - $catDiscount) / 100;
				$price = number_format(($service['salesPrice'] * $catCurrentDiscount),2);
		
			} else if ($discount != 0) {
                		$dispDiscount1 = $discount;
				$dispDiscount = (100 - $discount) / 100;
				$price = number_format(($service['salesPrice'] * $dispDiscount),2);
			} else if (isset($rowDU['discount_percentage'])){
                            if(isset($rowDU['discount_percentage']) && $rowDU['discount_percentage'] > 0){
                                $dispDiscount1 = $rowDU['discount_percentage'];
                                $dispDiscount = (100 - $rowDU['discount_percentage']) / 100;
                                $price = number_format($service['salesPrice'] * $dispDiscount,2);
                            }else{
                                if(isset($rowDU['discount_price']) && $rowDU['discount_price'] > 0){
                                    $dispDiscount1 = number_format(($rowDU['discount_price'] * 100) / $service['salesPrice'],2);
                                    $price = number_format(($service['salesPrice']) - $rowDU['discount_price'],2);
                                }
                            }	
			} else {
				
				$price = number_format($service['salesPrice'],2);
				
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
                            if(isset($hHDiscount['discount_bar']) && $rowD['discount_bar'] > 0){
                                $dispDiscount1 = $hHDiscount['discount_bar'];
                                $dispDiscount = (100 - $hHDiscount['discount_bar']) / 100;
                                $price = number_format($service['salesPrice'] * $dispDiscount,2);
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
                            if(isset($hHDiscount['discount_bar']) && $hHDiscount['discount_bar'] > 0){
                                $dispDiscount1 = $hHDiscount['discount_bar'];
                                $dispDiscount = (100 - $hHDiscount['discount_bar']) / 100;
                                $price = number_format($service['salesPrice'] * $dispDiscount,2);
                            }
                        }
                        if($happy_hour_id){
                            $selectCategoryDiscount = "SELECT discount FROM b_catdiscounts WHERE categoryid = $categoryid and happy_hour_id = ".$happy_hour_id;
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
                                    $price = number_format($service['salesPrice'] * $catCurrentDiscount,2);
                                }
                            }    



                            $selectPurchaseDiscount = "SELECT discount, fijo FROM b_inddiscounts WHERE purchaseid = $purchaseid and happy_hour_id = $happy_hour_id";
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
                		$prodFijoHD1 = number_format((($prodFijoHD * 100) / $service['salesPrice']),2);
                                if ($prodFijoHD1 > $dispDiscount1){
                                    //$prodDiscountHD = (100 - $prodFijoHD1) / 100;
                                    //$price = number_format($service['salesPrice'] * $prodDiscountHD,2);
                                    $dispDiscount1 = number_format((($prodFijoHD * 100) / $service['salesPrice']),2);
                                    $price = number_format($prodFijoHD,2);
                                }
                            } else if ($prodDiscountHD != 0 || $prodDiscountHD != '') {
                                if($prodDiscountHD > $prodDiscount && $prodDiscountHD > $dispDiscount1){
                                    $prodDiscountHD = (100 - $prodDiscountHD) / 100;
                                    $price = number_format($service['salesPrice'] * $prodDiscountHD,2);
                                }
                            }
                        }                        
                        // KONSTANT CODE UPDATE END		
			
				
		// Calculate Stock
		if ($_SESSION['showStockBar'] == 1) {
			$selectSales = "SELECT SUM(quantity) FROM b_salesdetails WHERE purchaseid = $purchaseid";
		
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
	
			$selectPermAdditions = "SELECT SUM(quantity) FROM b_productmovements WHERE purchaseid = $purchaseid AND type = 1 AND (movementTypeid = 1 OR movementTypeid = 3 OR movementTypeid = 10)";
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
			
			$selectPermRemovals = "SELECT SUM(quantity) FROM b_productmovements WHERE purchaseid = $purchaseid AND type = 2 AND (movementTypeid = 4 OR movementTypeid = 7 OR movementTypeid = 8 OR movementTypeid = 9 OR movementTypeid = 11 OR movementTypeid = 13 OR movementTypeid = 14 OR movementTypeid = 15 OR movementTypeid = 16)";
			
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
			$selectStashedInt = "SELECT SUM(quantity) FROM b_productmovements WHERE purchaseid = $purchaseid AND type = 2 AND (movementTypeid = 5 OR movementTypeid = 18)";
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
				
			$selectUnStashedInt = "SELECT SUM(quantity) FROM b_productmovements WHERE purchaseid = $purchaseid AND type = 1 AND (movementTypeid = 12 OR movementTypeid = 17)";
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
			$selectStashedExt = "SELECT SUM(quantity) FROM b_productmovements WHERE purchaseid = $purchaseid AND type = 2 AND (movementTypeid = 6 OR movementTypeid = 20)";
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
				
			$selectUnStashedExt = "SELECT SUM(quantity) FROM b_productmovements WHERE purchaseid = $purchaseid AND type = 1 AND (movementTypeid = 2 OR movementTypeid = 19)";
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
			$estStock = $service['purchaseQuantity'] + $permAdditions - $sales - $permRemovals - $inStash;
			
			$estStock = number_format($estStock, 0);
				
		}
							
	if ($service['description'] != '') {
		
		$commentRead = "
		                <img src='images/info-normal.png' style='inline-block; margin-top: 5px; margin-left: 5px; z-index:900;' id='comment$i' onClick='showhide$i()' /><div id='helpBox$i' class='helpBox'><strong>{$lang['extracts-description']}:</strong><br />{$service['description']}</div>
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
	
	$i++;
	

if ($_SESSION['barTouchscreen'] == 1) {
	
	if ($menuType == 0) {
		
		
		
		if ($_SESSION['showStockBar'] == 1) {
			$stockDisplay = "<img src='images/box-small.png' style='margin-bottom: -3px; margin-right: 5px;' />$estStock u";
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
		$('#grcalcB%d').css('display', 'inline');
		$('#minusB%d').css('display', 'inline');
		$('#plusB%d').css('display', 'inline');
		$('#free%d').css('display', 'none');
		});	

		$('#plus%d').click(function(e) {
			
			var grP = parseInt($('#grcalc%d').val());
			grP = parseInt(grP) + 1;
			
			var totPrice = $('#ppgcalc%d').val();
			
  			$('#grcalc%d').val(grP);
  			if (totPrice != 0) {
	  			compute();
	  			compute2();
			}
  			
  			e.preventDefault();
		});
		
		$('#minus%d').click(function(e) {
			
			var grM = parseInt($('#grcalc%d').val());
			grM = parseInt(grM) - 1;
			
			var totPrice = $('#ppgcalc%d').val();
			
  			$('#grcalc%d').val(grM);
  			if (totPrice != 0) {
	  			compute();
	  			compute2();
			}
  			
  			e.preventDefault();
 		});

		$('#plusB%d').click(function(e) {
			
			var grP2 = parseInt($('#grcalcB%d').val());
			grP2 = parseInt(grP2) + 1;
			
  			$('#grcalcB%d').val(grP2);
  			compute();
  			compute2();
  			
  			e.preventDefault();
		});
		
		$('#minusB%d').click(function(e) {
			
			var grM2 = parseInt($('#grcalcB%d').val());
			grM2 = parseInt(grM2) - 1;
			
  			$('#grcalcB%d').val(grM2);
  			compute();
  			compute2();
  			
  			e.preventDefault();
 		});

  }); // end ready

        </script>
	<div class='displaybox'>
	 <div id='imageholder'>
	  <center><img src='images/_$domain/bar-products/%s' /></center>
	 </div>
	 <h3>%s</h3>
	 <table style='width: 210px;'>
	  <tr>
	   <td style='width: 80px; padding-left: 5px; vertical-align: top; text-align: left;'>
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
	    $commentRead
	   </td>
	   <td style='width: 85px; text-align: right; vertical-align: top; color: #606f5c; font-weight: 600; padding-top: 13px;'>
	    $stockDisplay
	   </td>
	  </tr>
	 </table>
	 <table style='width: 210px; background-color: #c7d6c1; border-radius: 5px;'>
	  <tr>
	   <td style='vertical-align: top; text-align: left; margin-left: 10px;'>
  	    <center><a href='#'><img src='images/minus-new.png' id='minus%d' style='display: inline-block; margin-bottom: -15px; margin-right: 0px; padding: 5px;' /></a><input type='text' lang='nb' class='dispinput centered calc3 onScreenKeypad tst c$purchaseid' id='grcalc%d' name='sales[%d][grams]' placeholder='#' step='1' style='margin-left: 16px;' value='0' /><a href='#'><img src='images/plus-new.png' id='plus%d' style='display: inline-block; margin-bottom: -15px; margin-left: 10px; padding: 5px;' /></a></center>
	   </td>
	   <td style='vertical-align: top; text-align: right;'>
  	    <input type='text' lang='nb' class='dispinput centered calc2 onScreenKeypad tst' id='eurcalc%d' name='sales[%d][euro]' placeholder='&euro;' step='0.01' style='margin-right: 10px; display: none;'/>
	   </td>
	  </tr>
	  <tr>
	   <td>
  	    <input type='text' lang='nb' class='dispinput centered calc4 onScreenKeypad tst' id='grcalcB%d' name='sales[%d][grams2]' placeholder='#' value='0' step='0.01' style='background-color: #f4b146; display: none; margin-left: 6px; margin-bottom: 10px;'  />
  	    <a href='#'><img src='images/minus-new.png' id='minusB%d' style='display: inline-block; margin-bottom: -15px; padding: 5px; display: none;' /></a> 
  	    $giftOption
  	    <a href='#'><img src='images/plus-new.png' id='plusB%d' style='display: inline-block; margin-bottom: -15px; padding: 5px; display: none;' /></a>
  	   </td>
  	   <td>

  	  <input type='hidden' name='sales[%d][name]' value='%s' />
  	  <input type='hidden' name='sales[%d][productid]' value='%d' />
  	  <input type='hidden' name='sales[%d][purchaseid]' value='%d' />
  	  <input type='hidden' name='sales[%d][category]' value='%d' />
  	   </td>
  	  </tr>
	 </table>
	</div><script>
    $(document).ready(function() {
	    
		$('#grcalc%d').rules('add', {
		  number: true,
		  range: [0.00, 100000]
		});
	

  }); // end ready
</script>
",
	  $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $productid . '.' . $photoExt, $name, $i, $i, $price, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $name, $i, $productid, $i, $service['purchaseid'], $i, $categoryid, $i
	  );
	  
  	} else {
	  	
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

		$('#plus%d').click(function(e) {
			
			var grP = parseInt($('#grcalc%d').val());
			grP = parseInt(grP) + 1;
			
			var totPrice = $('#ppgcalc%d').val();
			
  			$('#grcalc%d').val(grP);
  			if (totPrice != 0) {
	  			compute();
	  			compute2();
			}
  			
  			e.preventDefault();
		});
		
		$('#minus%d').click(function(e) {
			
			var grM = parseInt($('#grcalc%d').val());
			grM = parseInt(grM) - 1;
			
			var totPrice = $('#ppgcalc%d').val();
			
  			$('#grcalc%d').val(grM);
  			if (totPrice != 0) {
	  			compute();
	  			compute2();
			}
  			
  			e.preventDefault();
 		});

		$('#plusB%d').click(function(e) {
			
			var grP2 = parseInt($('#grcalcB%d').val());
			grP2 = parseInt(grP2) + 1;
			
  			$('#grcalcB%d').val(grP2);
  			compute();
  			compute2();
  			
  			e.preventDefault();
		});
		
		$('#minusB%d').click(function(e) {
			
			var grM2 = parseInt($('#grcalcB%d').val());
			grM2 = parseInt(grM2) - 1;
			
  			$('#grcalcB%d').val(grM2);
  			compute();
  			compute2();
  			
  			e.preventDefault();
 		});


  }); // end ready

        </script>
        
	   <tr>
	    <td class='left'>%s</td>
		<td class='right'>%0.02f &euro; <input type='hidden' id='ppgcalc%d' name='sales[%d][ppg]' value='%0.01f' /></td>
	    <td class='right'><span style='color: red;' class='smallerfont'>$normalPrice</span></td>
	    $stockDisplay
	    <td>
	     <a href='#'><img src='images/minus-new.png' id='minus%d' style='display: inline-block; margin-bottom: -10px; padding: 5px;' /></a>
	     <input type='number' lang='nb' class='fourDigit centered calc3' style='border: #5aa242 solid 2px; width: 50px; border-radius: 2px; padding: 2px 1px; margin-left: 5px; margin-right: 8px;' id='grcalc%d' name='sales[%d][grams]' placeholder='#' value='0' />
	     <a href='#'><img src='images/plus-new.png' id='plus%d' style='display: inline-block; margin-bottom: -10px; padding: 5px;' /></a>
	    </td>
  	    <td><input type='number' lang='nb' class='fourDigit centered calc2' id='eurcalc%d' name='sales[%d][euro]' placeholder='&euro;' step='0.01' style='display: none;' /></td>
  	    <td><div id='giftArea%d' style='display: none;'><a href='#'><img src='images/minus-new.png' id='minusB%d' style='display: inline-block; margin-bottom: -10px; padding: 5px;' /></a><input type='number' lang='nb' class='fourDigit centered calc4' id='grcalcB%d' name='sales[%d][grams2]' placeholder='#' step='0.01' style='border: #5aa242 solid 2px; width: 50px; border-radius: 2px; padding: 2px 1px; background-color: yellow; margin-left: 10px; margin-right: 13px;' value='0' /><a href='#'><img src='images/plus-new.png' id='plusB%d' style='display: inline-block; margin-bottom: -10px; padding: 5px;' /></a></div>$giftOption</td>
  	   </tr>
  	  <input type='hidden' name='sales[%d][name]' value='%s' />
  	  <input type='hidden' name='sales[%d][productid]' value='%d' />
  	  <input type='hidden' name='sales[%d][purchaseid]' value='%d' />
  	  <input type='hidden' name='sales[%d][category]' value='%d' />
  	 </center>
	</div><script>
    $(document).ready(function() {
	    
		$('#grcalc%d').rules('add', {
		  number: true,
		  range: [0.00, 100000]
		});
	

  }); // end ready
</script>
",
	  $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $name, $price, $i, $i, $price, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $name, $i, $productid, $i, $service['purchaseid'], $i, $categoryid, $i
	  );

	  	
  	}
	  echo $extract_row;
	  

} else {
	
	if ($menuType == 0) {
		
		
		
		if ($_SESSION['showStockBar'] == 1) {
			$stockDisplay = "<img src='images/box-small.png' style='margin-bottom: -3px; margin-right: 5px;' />$estStock u";
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
	<div class='displaybox centered'>
	 <div id='imageholder'>
	  <center><img src='images/_$domain/bar-products/%s' /></center>
	 </div>
	 <h3>%s</h3>
	 
	 <table style='width: 210px;'>
	  <tr>
	   <td style='width: 80px; padding-left: 5px; vertical-align: top; text-align: left;'>
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
	    $commentRead
	   </td>
	   <td style='width: 85px; text-align: right; vertical-align: top; color: #606f5c; font-weight: 600; padding-top: 13px;'>
	    $stockDisplay
	   </td>
	  </tr>
	 </table>
	 <table style='width: 210px; background-color: #c7d6c1; border-radius: 5px;'>
	  <tr>
	   <td style='vertical-align: top; text-align: left; margin-left: 10px;'>
  	    <input type='text' lang='nb' class='dispinput centered calc3 onScreenKeypad tst c$purchaseid' id='grcalc%d' name='sales[%d][grams]' placeholder='#' step='1' style='margin-left: 10px;' />
	   </td>
	   <td style='vertical-align: top; text-align: right;'>
  	    <input type='text' lang='nb' class='dispinput centered calc2 onScreenKeypad tst' id='eurcalc%d' name='sales[%d][euro]' placeholder='&euro;' step='0.01' style='margin-right: 10px;'/>
	   </td>
	  </tr>
	  <tr>
	   <td>
  	    <input type='text' lang='nb' class='dispinput centered calc4 onScreenKeypad tst' id='grcalcB%d' name='sales[%d][grams2]' placeholder='#' step='0.01' style='background-color: #f4b146; display: none; margin-left: 6px; margin-bottom: 10px;'  /> $giftOption
  	   </td>
  	   <td>
  	  <input type='hidden' name='sales[%d][name]' value='%s' />
  	  <input type='hidden' name='sales[%d][productid]' value='%d' />
  	  <input type='hidden' name='sales[%d][purchaseid]' value='%d' />
  	  <input type='hidden' name='sales[%d][category]' value='%d' />
  	   </td>
  	  </tr>
	 </table>
  	 </center>
	</div><script>
    $(document).ready(function() {
	    
		$('#grcalc%d').rules('add', {
		  number: true,
		  range: [0.00, 100000]
		});
	

  }); // end ready
</script>
",
	  $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $productid . '.' . $photoExt, $name, $i, $i, $price, $i, $i, $i, $i, $i, $i, $i, $i, $name, $i, $productid, $i, $service['purchaseid'], $i, $categoryid, $i
	  );
	  
  	} else {
	  	
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
		<td class='right'>%0.02f &euro; <input type='hidden' id='ppgcalc%d' name='sales[%d][ppg]' value='%0.01f' /></td>
	    <td class='right'><span style='color: red;' class='smallerfont'>$normalPrice</span></td>
	    $stockDisplay
	    <td><input type='number' lang='nb' class='fourDigit centered calc3' style='border: #5aa242 solid 2px; width: 50px; border-radius: 2px; padding: 2px 1px;' id='grcalc%d' name='sales[%d][grams]' placeholder='#' /></td>
  	    <td><input type='number' lang='nb' class='fourDigit centered calc2' id='eurcalc%d' name='sales[%d][euro]' placeholder='&euro;' step='0.01' style='display: none;' /></td>
  	    <td><input type='number' lang='nb' class='fourDigit centered calc4' id='grcalcB%d' name='sales[%d][grams2]' placeholder='#' step='0.01' style='border: #5aa242 solid 2px; width: 50px; border-radius: 2px; padding: 2px 1px; background-color: yellow; display: none;' /> $giftOption</td>
  	   </tr>
  	  <input type='hidden' name='sales[%d][name]' value='%s' />
  	  <input type='hidden' name='sales[%d][productid]' value='%d' />
  	  <input type='hidden' name='sales[%d][purchaseid]' value='%d' />
  	  <input type='hidden' name='sales[%d][category]' value='%d' />
  	 </center>
	</div><script>
    $(document).ready(function() {
	    
		$('#grcalc%d').rules('add', {
		  number: true,
		  range: [0.00, 100000]
		});
	

  }); // end ready
</script>
",
	  $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $name, $price, $i, $i, $price, $i, $i, $i, $i, $i, $i, $i, $i, $name, $i, $productid, $i, $service['purchaseid'], $i, $categoryid, $i
	  );

	  	
  	}
	  echo $extract_row;
	  
}


				
			} // end services
			
	if ($menuType == 1) {
		echo "</table></div><div class='leftfloat'><table class='default nonhover'>";
	}
		}
				
		} // end categories
  
echo "</center>";

?>
	  <div class="clearFloat"></div>

	 </tbody>
	 </table>
	
<?php if ($_SESSION['barsig'] == 1) { ?>
<INPUT TYPE=HIDDEN NAME="bioSigData">
<INPUT TYPE=HIDDEN NAME="sigImgData">
<INPUT TYPE=HIDDEN NAME="sigStringData" />
<INPUT TYPE=HIDDEN NAME="sigImageData" />
<?php } ?>
	
</form>
</div>

<?php displayFooter(); ?>

