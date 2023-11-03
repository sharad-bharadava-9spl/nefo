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
		$realNewCredit = $_POST['realNewCredit'];
		$saletime = date('Y-m-d H:i:s');
		


			

		// Query to add new sale to Sales table - 6 arguments
		$query = sprintf("INSERT INTO b_sales (saletime, userid, amount, unitsTot, adminComment, creditBefore, creditAfter) VALUES ('%s', '%d', '%f', '%f', '%s', '%f', '%f');",
		 $saletime, $user_id, $eurcalcTOT, $unitsTOT, $adminComment, $creditLookup, $newCreditCalc);
		 

		mysql_query($query)
			or handleError($lang['error-savingdispense'],"Error inserting sale: " . mysql_error());

		$saleid = mysql_insert_id();
		
		// Query to add new sale to Sales table2 - 6 arguments
		$query = sprintf("INSERT INTO f_b_sales (saletime, userid, amount, unitsTot, adminComment, creditBefore, creditAfter) VALUES ('%s', '%d', '%f', '%f', '%s', '%f', '%f');",
		 $saletime, $user_id, $eurcalcTOT, $unitsTOT, $adminComment, $creditLookup, $newCreditCalc);
		
		mysql_query($query)
			or handleError($lang['error-savingdispense'],"Error inserting sale: " . mysql_error());
			
			
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
	if (isset($_POST['userSelect'])) {
		$user_id = $_POST['userSelect'];
	} else if (isset($_GET['user_id'])) {
		$user_id = $_GET['user_id'];
	} else {
		handleError($lang['error-nomember'],"");
	}
	
	
	
	$userDetails = "SELECT id, registeredSince, Brand, number, longName, shortName, cif, street, streetnumber, flat, postcode, city, state, country, website, email, facebook, twitter, instagram, googleplus, status, type, lawyer, URL, source, billingType, dbname, dbuser, dbpwd FROM customers WHERE id = $user_id";
			
	$result = mysql_query($userDetails)
		or handleError($lang['error-userload'],"Error loading user: " . mysql_error());
	
	$row = mysql_fetch_array($result);
		$id = $row['id'];
		$registeredSince = $row['registeredSince'];
		$Brand = $row['Brand'];
		$number = $row['number'];
		$longName = $row['longName'];
		$shortName = $row['shortName'];
		$cif = $row['cif'];
		$street = $row['street'];
		$streetnumber = $row['streetnumber'];
		$flat = $row['flat'];
		$postcode = $row['postcode'];
		$city = $row['city'];
		$state = $row['state'];
		$country = $row['country'];
		$website = $row['website'];
		$email = $row['email'];
		$facebook = $row['facebook'];
		$twitter = $row['twitter'];
		$instagram = $row['instagram'];
		$googleplus = $row['googleplus'];
		$status = $row['status'];
		$type = $row['type'];
		$lawyer = $row['lawyer'];
		$URL = $row['URL'];
		$source = $row['source'];
		$billingType = $row['billingType'];
		
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
		  rules: {
			  cardid: {
				  required: true
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
	    
$(function(){
    $('#chipClick').click(function() {
        $("#cardscan").val('$cardid');
    });
});

	    	    
	  $('#registerForm').validate({
		  rules: {
			  newcredit: {
				  range: [0,100000]
			  },
			  cardid: {
				  required: true
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
  $('#grcalcTOT2').val(sumC);
  
}



   function computeTot() {
          var a = $('#realCredit').val();
          var b = $('#eurcalcTOT').val();
          var total = a - b;
          var roundedtotal = total.toFixed(2);
          $('#newcredit').val(roundedtotal);
	      $('#realNewCredit').val(roundedtotal);
        }
        
        // If newcred < 0 then NO ++ only if usergroup is 4 (or 5?)
        


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
	
$(document).on('click keypress keyup blur', function () {
    getItems();
    computeTot();
});
  }); // end ready
  

EOD;

	pageStart("NEW HW SALE", NULL, $validationScript, "pdispense", "newsale", "NEW HW SALE", $_SESSION['successMessage'], $_SESSION['errorMessage']);
	
	
?>
<form id="registerForm" action="" method="POST">


<div id="memberbox">
<img src="images/minimize.png" class="closeImg pointer" id="minimizeMemberBox" />

<?php
echo "<a href='new-picture.php?user_id=$user_id'><img src='images/members/$user_id.$photoExt' class='userPicture' /></a><span class='memberinfo'><a href='profile.php?user_id=$user_id'><span class='membername'>$number $shortName</span></a>";


	
	
	?>
	</span>
	<div class="clearFloat"></div>
 	   <input type='hidden' id='eurcalcTOTexp' name='euroTOT' />
 	   <input type='hidden' name='dispense' value='done' /> 	   
  	   <input type='hidden' name='user_id' value='<?php echo $user_id; ?>' />
  	   <input type='hidden' name='realCredit' id='realCredit' value='<?php echo $realCredit; ?>' />
  	   <input type='hidden' name='realNewCredit' id='realNewCredit' value='<?php echo $realNewCredit; ?>' />
  	   
<?php if ($_SESSION['creditOrDirect'] == 1) { ?>
  	   <table id="memberAmount">
  	    <tr>
  	     <td><input type='number' lang='nb' class='specialInput first' id='unitcalcTOT' name='unitsTOT' value="0" readonly /> </td>
  	     <td style='text-align: left;'><span style="font-size: 20px;">&nbsp;u.</span></td>
  	     <td style='text-align: right;'><?php echo $lang['global-credit']; ?>:</td>
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
  	     <td style='text-align: right;'><input type='number' lang='nb' class='specialInput first' id='unitcalcTOT' name='unitsTOT' value="0" readonly /> </td>
  	     <td style='text-align: left;'><span style="font-size: 20px;">&nbsp;u.</span></td>
  	     <td></td>
  	     <td></td>
  	    </tr>
   	    <tr>
  	     <td style='text-align: right;'><input type='number' lang='nb' class='specialInput' id='eurcalcTOT' name='eurcalcTOT' value="0" readonly /> </td>
  	     <td style='text-align: left;'><span style="font-size: 20px;">&nbsp;&euro;</span></td>
  	     <td></td>
  	     <td></td>
<?php } ?>
  	   

  	    </tr>
  	   </table>
  	   
       <center>
       <center><a href="#" id="openComment" style="vertical-align: bottom;">+ <?php echo $lang['global-comment']; ?></a></center><br />
       <center><textarea class="hiddenComment" name="adminComment" placeholder="<?php echo $lang['global-comment']; ?>?"></textarea></center>
  	   
 <button type="submit" class='oneClick' name='oneClick'><?php echo $lang['global-save']; ?></button>
  	</div>
</div> <!-- end memberbox -->

<div id='hiddenSummary'>
<img src="images/maximize.png" class="closeImg pointer" id="minimizeSummaryBox" />
 	   <input type='number' lang='nb' class='fourDigit specialInput first' id='grcalcTOT2' name='gramsTOT2' value="0" readonly /> u.<br />
  	   <input type='number' lang='nb' class='fourDigit specialInput' id='eurcalcTOT2' name='eurcalcTOT2' value="0" readonly /> <span style="font-size: 30px;">&euro;</span>
</div>

<div class="clearfloat"></div>


<?php
		
			
	$selectCategory = "SELECT id, name FROM b_categories ORDER BY name ASC;";
	
	$resultCategory = mysql_query($selectCategory)
		or handleError($lang['error-prodprices'],"Error loading flower prices from db: " . mysql_error());
		
					$i = 0;
		while ($category = mysql_fetch_array($resultCategory)) {
			
			$categoryid = $category['id'];
			$name = $category['name'];
			
			$checkProducts = "SELECT productid from b_purchases WHERE category = $categoryid AND closedAt IS NULL AND inMenu = 1";
			
			$productCheck = mysql_query($checkProducts);
			
			if (mysql_num_rows($productCheck) != 0) {

				
			
  			$selectServices = "SELECT pr.productid, pr.name, p.purchaseid, p.salesPrice, p.purchaseQuantity, pr.photoExt FROM b_products pr, b_purchases p WHERE p.category = $categoryid AND p.productid = pr.productid AND p.closedAt IS NULL AND inMenu = 1 ORDER BY p.salesPrice ASC;";
	
			$resultServices = mysql_query($selectServices)
				or handleError($lang['error-prodprices'],"Error loading flower prices from db: " . mysql_error());
			
				
				$menuType = $_SESSION['barMenuType'];
				
				if ($menuType == 1) {
					echo "<div class='leftfloat'>";
				}
		
				echo "<h3 class='title'>$name</h3>";
					
				if ($menuType == 1) {
					echo "<table class='default nonhover'>";
				}
				
			while ($service = mysql_fetch_array($resultServices)) {
				
				$name = $service['name'];
				$categoryID = $service['category'];
				$productid = $service['productid'];
				$photoExt = $service['photoExt'];
	
				
				
							
	$i++;
					
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
	<div class='displaybox centered'>
	 <div id='imageholder'>
	  <center><img src='images/bar-products/%s' /></center>
	 </div>
	 <h3>%s</h3>
	 <input class='specialInput' id='ppgcalc%d' name='sales[%d][ppg]' value='%0.02f' readonly /><span class='eurosign'>&euro;</span>
	 <br />
	 <div class='clearfloat'></div><br />
	 <center>
  	  <input type='number' lang='nb' class='fourDigit centered calc3' id='grcalc%d' name='sales[%d][grams]' placeholder='#' /> 
  	  <input type='number' lang='nb' class='fourDigit centered calc2' id='eurcalc%d' name='sales[%d][euro]' placeholder='&euro;' step='0.01' style='display: none;' />
  	  <input type='number' lang='nb' class='fourDigit centered calc4' id='grcalcB%d' name='sales[%d][grams2]' placeholder='#' step='0.01' style='background-color: yellow; display: none;'  /> $giftOption
  	  <input type='hidden' name='sales[%d][name]' value='%s' />
  	  <input type='hidden' name='sales[%d][productid]' value='%d' />
  	  <input type='hidden' name='sales[%d][purchaseid]' value='%d' />
  	  <input type='hidden' name='sales[%d][category]' value='%d' />
  	 </center>
	</div>",
	  $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $productid . '.' . $photoExt, $name, $i, $i, $service['salesPrice'], $i, $i, $i, $i, $i, $i, $i, $i, $name, $i, $productid, $i, $service['purchaseid'], $i, $categoryid
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
		<td class='right'>%0.02f &euro; <input type='hidden' id='ppgcalc%d' name='sales[%d][ppg]' value='%0.01f' /></td>
	    <td><input type='number' lang='nb' class='fourDigit centered calc3' style='border: #5aa242 solid 2px; width: 50px; border-radius: 2px; padding: 2px 1px;' id='grcalc%d' name='sales[%d][grams]' placeholder='#' /></td>
  	    <td><input type='number' lang='nb' class='fourDigit centered calc2' id='eurcalc%d' name='sales[%d][euro]' placeholder='&euro;' step='0.01' style='display: none;' /></td>
  	    <td><input type='number' lang='nb' class='fourDigit centered calc4' id='grcalcB%d' name='sales[%d][grams2]' placeholder='#' step='0.01' style='border: #5aa242 solid 2px; width: 50px; border-radius: 2px; padding: 2px 1px; background-color: yellow; display: none;' /> $giftOption</td>
  	   </tr>
  	  <input type='hidden' name='sales[%d][name]' value='%s' />
  	  <input type='hidden' name='sales[%d][productid]' value='%d' />
  	  <input type='hidden' name='sales[%d][purchaseid]' value='%d' />
  	  <input type='hidden' name='sales[%d][category]' value='%d' />
  	 </center>
	</div>",
	  $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $i, $name, $service['salesPrice'] * $discount, $i, $i, $service['salesPrice'] * $discount, $i, $i, $i, $i, $i, $i, $i, $i, $name, $i, $productid, $i, $service['purchaseid'], $i, $categoryid
	  );

	  	
  	}
	  echo $extract_row;
	  




				
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
	
	
</form>

<?php displayFooter(); ?>

