<?php
	
	require_once 'cOnfig/connection.php';
	require_once 'cOnfig/viewv6.php';
	require_once 'cOnfig/authenticate.php';
	require_once 'cOnfig/languages/common.php';
	
	session_start();
	$accessLevel = '3';
	
	// Authenticate & authorize
	authorizeUser($accessLevel);
	
	// Did this page re-submit with a form? If so, check & store details
	if (isset($_POST['expense'])) {
		
		$userid = $_POST['userSelect'];
		$expense = str_replace("'","\'",str_replace('%', '&#37;', trim($_POST['expense'])));
		$expenseCat = $_POST['expenseCat'];
		$expenseCatSub = $_POST['expenseCatSub'];
		$shop = str_replace("'","\'",str_replace('%', '&#37;', trim($_POST['shop'])));
		$amount = $_POST['amount'];
		$moneysource = $_POST['moneysource'];
		$other = $_POST['other'];
		$receipt = $_POST['receipt'];
		$invoice = $_POST['invoice'];
		$comment = str_replace("'","\'",str_replace('%', '&#37;', trim($_POST['comment'])));
		$day = $_POST['day'];
		$month = $_POST['month'];
		$year = $_POST['year'];
		$day2 = $_POST['day2'];
		$month2 = $_POST['month2'];
		$year2 = $_POST['year2'];
		$type = $_POST['type'];
		$status = $_POST['status'];
		$personal = $_POST['personal'];
		$refundable = $_POST['refundable'];
		$account = $_POST['account'];
		$country = str_replace("'","\'",str_replace('%', '&#37;', trim($_POST['country'])));
		
		if ($type == '') {
			$type = 0;
		}
		if ($status == '') {
			$status = 0;
		}

		$vat = $_POST['vat'];
		$brand = $_POST['brand'];
		
		$tempNo = $_SESSION ['tempNo'];
		$photoext = $_SESSION['expenseextension'];
		
		
		if ($day2 > 0 && $month2 > 0 && $year2 > 0) {
			$date_timestamp = strtotime($month2 . "/" . $day2 . "/" . $year2);
			$paymentdate = date("Y-m-d H:i:s", $date_timestamp);
		} else {
			$paymentdate = '';
		}

		
		if ($day > 0 && $month > 0 && $year > 0) {
			$date_timestamp = strtotime($month . "/" . $day . "/" . $year);
			$registertime = date("Y-m-d H:i:s", $date_timestamp);
		} else {
			$registertime = date('Y-m-d H:i:s'); // 	$purchaseDate = date('Y-m-d H:i:s'); ????
		}
		
		$vatamt = $amount - ($amount / ((100 + $vat) / 100));
		
		if ($paymentdate == '') {
			
			// Query to add new sale to Sales table - 6 arguments
			  $query = sprintf("INSERT INTO expenses_nefos (registertime, userid, expense, moneysource, other, amount, shop, comment, receipt, expensecategory, invoice, photoext, vat, vatamt, brand, type, status, country, subcat, personal, refundable, account) VALUES ('%s', '%d', '%s', '%d', '%s', '%f', '%s', '%s', '%d', '%d', '%d', '%s', '%f', '%f', '%d', '%d', '%d', '%s', '%d', '%d', '%d', '%d');",
			  $registertime, $userid, $expense, $moneysource, $other, $amount, $shop, $comment, $receipt, $expenseCat, $invoice, $photoext, $vat, $vatamt, $brand, $type, $status, $country, $expenseCatSub, $personal, $refundable, $account);
			  
		} else {

			// Query to add new sale to Sales table - 6 arguments
			  $query = sprintf("INSERT INTO expenses_nefos (registertime, userid, expense, moneysource, other, amount, shop, comment, receipt, expensecategory, invoice, photoext, vat, vatamt, brand, paymentdate, type, status, country, subcat, personal, refundable, account) VALUES ('%s', '%d', '%s', '%d', '%s', '%f', '%s', '%s', '%d', '%d', '%d', '%s', '%f', '%f', '%d', '%s', '%d', '%d', '%s', '%d', '%d', '%d', '%d');",
			  $registertime, $userid, $expense, $moneysource, $other, $amount, $shop, $comment, $receipt, $expenseCat, $invoice, $photoext, $vat, $vatamt, $brand, $paymentdate, $type, $status, $country, $expenseCatSub, $personal, $refundable, $account);
		  
		}
				  
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
			
		$expenseid = $pdo3->lastInsertId();
		
		// Rename the photo from temp number to real number
		$oldfile = 'images/expenses-nefos/' . $tempNo . '.' . $photoext;
		$newfile = 'images/expenses-nefos/' . $expenseid . '.' . $photoext;
		rename($oldfile, $newfile);
		
		
		if (isset($_GET['closeday'])) {
			// On success: redirect.
			$_SESSION['successMessage'] = $lang['expense-expenseadded'];
			header("Location: close-day-reception.php?addexpense");
			exit();
		} else if (isset($_GET['closeshift'])) {
			// On success: redirect.
			$_SESSION['successMessage'] = $lang['expense-expenseadded'];
			header("Location: close-shift-reception.php?addexpense");
			exit();
		} else if (isset($_GET['closeshiftandday'])) {
			// On success: redirect.
			$_SESSION['successMessage'] = $lang['expense-expenseadded'];
			header("Location: close-shift-and-day-reception.php?addexpense");
			exit();
		} else {
			// On success: redirect.
			$_SESSION['successMessage'] = $lang['expense-expenseadded'];
			if ($_SESSION['userGroup'] < 2) {
				header("Location: expenses-nefos.php");
			} else {
				header("Location: admin.php");
			}
			exit();
		}
	}
	/***** FORM SUBMIT END *****/
	
	$validationScript = <<<EOD
	
    $(document).ready(function() {
	   
	    
	    	    
	  $('#registerForm').validate({
		  ignore:'', //because the radio buttons are hidden, validation ignores them. This way it'll work.
		  rules: {
			  userSelect: {
				  required: true
			  },
			  expense: {
				  required: true
			  },
			  expenseCat: {
				  required: true
			  },
			  expenseCatSub: {
				  required: true
			  },
			  shop: {
				  required: true
			  },
			  status: {
				  required: true
			  },
			  type: {
				  required: true
			  },
			  country: {
				  required: true
			  },
			  moneysource: {
				  required: true
			  },
			  receipt: {
				  required: true
			  },
			  invoice: {
				  required: true
			  },
			  amount: {
				  required: true
			  },
			  brand: {
				  required: true
			  },
			  account: {
				  required: true
			  },
			  day: {
				  range:[0,31]
			  },
			  month: {
				  range:[0,31]
			  },
			  year: {
				  range:[0,2025]
			  }
    	},
		  errorPlacement: function(error, element) {
			  
			  if (element.attr("name") == "expenseCat") {
        		error.appendTo($('#categoryLink'));
    		  } else if ( element.is(":radio") || element.is(":checkbox")){
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
  }); // end ready
EOD;


	pageStart("Unallocated payments", NULL, $validationScript, "pexpenses", "admin", "Unallocated payments", $_SESSION['successMessage'], $_SESSION['errorMessage']);
	
	
?>
<div id='mainbox-new-club'>
 <div id='mainboxheader'>
  <center>
   Add Unallocated payment
  </center>
 </div>
 <div class='boxcontent'>
  <center>

<form id="registerForm" action="" method="POST">
<span class="fakelabel"><?php echo $lang['expense-purchasedby']; ?>:</span>
  <select class="fakeInput" name="userSelect">
  <option value=""><?php echo $lang['expense-choosemember']; ?></option>
<?php
      	// Query to look up pre-registered users:
		$userDetails = "SELECT user_id, memberno, first_name, last_name FROM users WHERE userGroup != '6' ORDER BY memberno ASC";
		try
		{
			$results = $pdo3->prepare("$userDetails");
			$results->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		while ($user = $results->fetch()) {
				$user_row = sprintf("<option value='%d'>#%s - %s %s</option>",
	  								 $user['user_id'], $user['memberno'], $user['first_name'], $user['last_name']);
	  			echo $user_row;
  		}
?>
</select>
<br />
<span class="fakelabel"><?php echo $lang['global-category']; ?>:</span>
<select name="expenseCat" id="expenseCat">
 <option value=''><?php echo $lang['addremove-pleaseselect']; ?></option>


<?php
      	// Query to look up categories:
		if ($_SESSION['lang'] == 'es') {
			$selectExpenseCat = "SELECT categoryid, namees, descriptiones FROM expensecategories WHERE categoryid > 45 AND sub = 0 AND categoryid <> 109 ORDER BY nameen ASC";
		try
		{
			$results = $pdo3->prepare("$selectExpenseCat");
			$results->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		} else {
			$selectExpenseCat = "SELECT categoryid, nameen, descriptionen FROM expensecategories WHERE categoryid > 45 AND sub = 0 AND categoryid <> 109 ORDER BY nameen ASC";
		try
		{
			$results = $pdo3->prepare("$selectExpenseCat");
			$results->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		}
		
		while ($category = $results->fetch()) {
			
			if ($_SESSION['lang'] == 'es') {
		  	    $expenseCatId = $category['categoryid'];
		  	    $expenseCat = $category['namees'];
		  	    $expenseDesc = $category['descriptiones'];
	  	    } else {
		  	    $expenseCatId = $category['categoryid'];
		  	    $expenseCat = $category['nameen'];
		  	    $expenseDesc = $category['descriptionen'];
	  	    }
			
			$category_row = "<option value='$expenseCatId'>$expenseCat</option>";

				
	  			echo $category_row;
	  			
  		}
?>

  
  
</select>
<br />
<span class="fakelabel">Subcategory:</span>
<select name="expenseCatSub" id="expenseCatSub">
 <option value='' id='defSubCat'>Choose category first</option>
</select>
<br />

<span class="fakelabel" style="margin-top: 5px;">Brand:</span>
	<div class='fakeboxholder'>	
	 <label class="control">
	  <span id="errorBox2"></span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Nefos
	  <input type="radio" name="brand" value="0" id="accept2" />
	  <div class="fakebox"></div>
	 </label>
	</div>
&nbsp;&nbsp;&nbsp;		
	<div class='fakeboxholder'>	
	 <label class="control">
	  <span id="errorBox3"></span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;CSS
	  <input type="radio" name="brand" value="1" id="accept3" />
	  <div class="fakebox"></div>
	 </label>
	</div>


<br />
<br />
<span class="fakelabel"><?php echo $lang['global-expense']; ?>:</span><input type="text" name="expense" id="expense" placeholder="<?php echo $lang['expense-what']; ?>?" class="eightDigit" />
<br />
<span class="fakelabel"><?php echo $lang['global-shop']; ?>:</span><input type="text" name="shop" placeholder="<?php echo $lang['expense-where']; ?>?" class="eightDigit" />
<br />
<span class="fakelabel">Country:</span><input type="text" name="country" placeholder="From which country?" class="eightDigit" />
<br />
<span class="fakelabel">Full amount:</span><input type="number" lang="nb" name="amount" placeholder="&euro;" class="fourDigit" />
<br />
<span class="fakelabel">VAT %:</span><input type="number" lang="nb" name="vat" placeholder="%" class="fourDigit" />
<br />
<span class="fakelabel" style="margin-top: 5px;">Type:</span>
	<div class='fakeboxholder'>	
	 <label class="control">
	  <span id="errorBox2"></span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Goods
	  <input type="radio" name="type" value="1" id="accept2" />
	  <div class="fakebox"></div>
	 </label>
	</div>
&nbsp;&nbsp;&nbsp;		
	<div class='fakeboxholder'>	
	 <label class="control">
	  <span id="errorBox3"></span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Services
	  <input type="radio" name="type" value="2" id="accept3" />
	  <div class="fakebox"></div>
	 </label>
	</div>
<br /><br />

<span class="fakelabel" style="margin-top: 5px;">Status:</span>
	<div class='fakeboxholder'>	
	 <label class="control">
	  <span id="errorBox2"></span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Business
	  <input type="radio" name="status" value="1" id="accept2" />
	  <div class="fakebox"></div>
	 </label>
	</div>
&nbsp;&nbsp;&nbsp;	
	<div class='fakeboxholder'>	
	 <label class="control">
	  <span id="errorBox3"></span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Private
	  <input type="radio" name="status" value="2" id="accept3" />
	  <div class="fakebox"></div>
	 </label>
	</div>
<br /><br />

<span class="fakelabel" style="margin-top: 2px; margin-bottom: 2px;">Invoice date:</span>
 <input type="number" lang="nb" name="day" id="day" class="twoDigit" maxlength="2" placeholder="dd" value="<?php echo date('d'); ?>" />
 <input type="number" lang="nb" name="month" id="month" class="twoDigit" maxlength="2" placeholder="mm" value="<?php echo date('m'); ?>" />
 <input type="number" lang="nb" name="year" id="year" class="fourDigit" maxlength="4" placeholder="<?php echo $lang['member-yyyy']; ?>" value="<?php echo date('Y'); ?>" />
<br />
<span class="fakelabel" style="margin-top: 2px; margin-bottom: 2px;">Accounting date:</span>
 <input type="number" lang="nb" name="day2" id="day2" class="twoDigit" maxlength="2" placeholder="dd" />
 <input type="number" lang="nb" name="month2" id="month2" class="twoDigit" maxlength="2" placeholder="mm" />
 <input type="number" lang="nb" name="year2" id="year2" class="fourDigit" maxlength="4" placeholder="<?php echo $lang['member-yyyy']; ?>" />
 <a href="#" class="smallerfont yellow" id="clickChange3">[Use inv. date]</a>


<br />
<span class="fakelabel" style="margin-top: 5px;"><?php echo $lang['expense-source']; ?>:</span>

  <select class="fakeInput" name="moneysource">
  <option value="">Please choose</option>
  <option value="1">Cash</option>
  <option value="2">Nefos BBVA Bank transfer</option>
  <option value="11">Nefos BOI Bank transfer</option>
  <option value="3">Nefos BBVA debit card</option>
  <option value="4">Nefos BBVA Ahab credit card</option>
  <option value="5">Nefos BBVA Andy credit card</option>
  <option value="6">Nefos BBVA Direct Debit</option>
  <option value="12">Nefos BOI Direct Debit</option>
  <option value="13">Nefos BOI debit card</option>
  <option value="14">Amazon</option>
  </select>
<br />
<span class="fakelabel" style="margin-top: 5px;">Account:</span>
	<div class='fakeboxholder'>	
	 <label class="control">
	  <span id="errorBox2"></span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;BBVA SW
	  <input type="radio" name="account" value="0" id="accept2" />
	  <div class="fakebox"></div>
	 </label>
	</div>
&nbsp;&nbsp;&nbsp;	
	<div class='fakeboxholder'>	
	 <label class="control">
	  <span id="errorBox2"></span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;BBVA HW
	  <input type="radio" name="account" value="1" id="accept2" />
	  <div class="fakebox"></div>
	 </label>
	</div>
&nbsp;&nbsp;&nbsp;	
	<div class='fakeboxholder'>	
	 <label class="control">
	  <span id="errorBox3"></span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;BOI
	  <input type="radio" name="account" value="2" id="accept3" />
	  <div class="fakebox"></div>
	 </label>
	</div>
<br />
<br />
<span class="fakelabel" style="margin-top: 5px;">Receipt?</span>
	<div class='fakeboxholder'>	
	 <label class="control">
	  <span id="errorBox2"></span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Yes
	  <input type="radio" name="receipt" value="1" id="accept2" />
	  <div class="fakebox"></div>
	 </label>
	</div>
&nbsp;&nbsp;&nbsp;	
	<div class='fakeboxholder'>	
	 <label class="control">
	  <span id="errorBox3"></span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;No
	  <input type="radio" name="receipt" value="2" id="accept3" />
	  <div class="fakebox"></div>
	 </label>
	</div>
<br />
<br />
<span class="fakelabel" style="margin-top: 5px;">Invoice?</span>
	<div class='fakeboxholder'>	
	 <label class="control">
	  <span id="errorBox2"></span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Yes
	  <input type="radio" name="invoice" value="1" id="accept2" />
	  <div class="fakebox"></div>
	 </label>
	</div>
&nbsp;&nbsp;&nbsp;	
	<div class='fakeboxholder'>	
	 <label class="control">
	  <span id="errorBox3"></span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;No
	  <input type="radio" name="invoice" value="0" id="accept3" />
	  <div class="fakebox"></div>
	 </label>
	</div>
<br /><br />
<?php if ($_SESSION['user_id'] == 1404 || $_SESSION['user_id'] == 1402) { ?>

<span class="fakelabel" style="margin-top: 5px;">Personal expense?</span>
	<div class='fakeboxholder'>	
	 <label class="control">
	  <span id="errorBox3"></span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
	  <input type="checkbox" name="personal" value="1" id="accept3" />
	  <div class="fakebox"></div>
	 </label>
	</div>

<br /><br />
<?php } ?>
<span class="fakelabel" style="margin-top: 5px;">Refundable?</span>
	<div class='fakeboxholder'>	
	 <label class="control">
	  <span id="errorBox3"></span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
	  <input type="checkbox" name="refundable" value="1" id="accept3" />
	  <div class="fakebox"></div>
	 </label>
	</div>
<br /><br />
<span class="fakelabel" style="vertical-align: top;"><?php echo $lang['global-comment']; ?>:</span><textarea id="comment" name="comment" onClick="getSubCats()" placeholder="<?php echo $lang['global-comment']; ?>?"></textarea>

 <button class='oneClick' name='oneClick' type="submit"><?php echo $lang['expense-register']; ?></button>
 
</form>
</div>
<?php
echo <<<EOD
<script>
$("#expenseCat").change(function getSubCats(){
	
	$('#expenseCatSub option').remove();
	$('#expenseCatSub').append('<option>Loading. Please wait...</option>');
	
	var catval = $("#expenseCat").val();
	
    $.ajax({
      type:"post",
      url:"getcats.php?cat="+catval,
      datatype:"text",
      success:function(data)
      {
	       $('#expenseCatSub option').remove();
	       $('#expenseCatSub').append(data);

      }
    });
});

	$("#categoryLink").click(function () {
	$("#categoryBox").css("display", "block");
	});	
	$(".labelLaunch").click(function () {
	$("#categoryBox").css("display", "none");
	$("#categoryLink").html("[Change]");
	$("#categoryLink").css("font-size", "11px");
	});	
	$("#clickChange").click(function () {
	$("#dateshow").css("display", "none");
	$("#customDate").css("display", "block");
	});	
	$("#clickChange2").click(function () {
	$("#customDate").css("display", "none");
	$("#dateshow").css("display", "block");
	$("#day").val("");
	$("#month").val("");
	$("#year").val("");
	});	
	$("#clickChange3").click(function () {
	
	var v1 = $("#day").val();
	var v2 = $("#month").val();
	var v3 = $("#year").val();
	
	$("#day2").val(v1);
	$("#month2").val(v2);
	$("#year2").val(v3);
	});	
	$('input[name="expenseCat"]').change(function() {
    			$('#selectedRadio').text(eval('desc'+this.value));
});




    $('input[type="radio"]').click(function(){

        if($(this).attr("value")=="12"){

            $("#hideMe").css("display", "block");

        }
    });

   
	
	
</script>
EOD;

displayFooter();