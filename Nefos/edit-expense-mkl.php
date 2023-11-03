<?php
	
	require_once 'cOnfig/connection.php';
	require_once 'cOnfig/view.php';
	require_once 'cOnfig/authenticate.php';
	require_once 'cOnfig/languages/common.php';
	
	session_start();
	$accessLevel = '3';
	
	// Authenticate & authorize
	authorizeUser($accessLevel);
	
	// Get the expense ID
	if (isset($_GET['expenseid'])) {
		$expenseid = $_GET['expenseid'];
	} else {
		handleError($lang['error-noexpenseid'],"");
	}
		// Did this page re-submit with a form? If so, check & store details
	if (isset($_POST['expense'])) {
		
  	    $userid = $_POST['userSelect'];
  	    $expense = $_POST['expense'];
  	    $expenseCat = $_POST['expenseCat'];
		$expenseCatSub = $_POST['expenseCatSub'];
		$shop = $_POST['shop'];
		$amount = $_POST['amount'];
		$moneysource = $_POST['moneysource'];
		$other = $_POST['other'];
		$receipt = $_POST['receipt'];
		$invoice = $_POST['invoice'];
		$comment = $_POST['comment'];
		$vat = $_POST['vat'];
		$brand = $_POST['brand'];
		$day2 = $_POST['day2'];
		$month2 = $_POST['month2'];
		$year2 = $_POST['year2'];
		$type = $_POST['type'];
		$status = $_POST['status'];
		$country = $_POST['country'];
		$personal = $_POST['personal'];
		$refundable = $_POST['refundable'];
		$refunded = $_POST['refunded'];
		$hwsw = $_POST['hwsw'];

		if ($type == '') {
			$type = 0;
		}
		if ($status == '') {
			$status = 0;
		}
		
		$day = $_POST['day'];
		$month = $_POST['month'];
		$year = $_POST['year'];
		
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
		
		

		
		$date_timestamp = strtotime($month . "/" . $day . "/" . $year);
		$registertime = date("Y-m-d H:i:s", $date_timestamp);
		
		$vatamt = $amount - ($amount / ((100 + $vat) / 100));
		
		if ($paymentdate == '') {			
			
		// Query to update expense
		$updateExpense = sprintf("UPDATE expenses_mklnew SET userid = '%d', expense = '%s', moneysource = '%d', other = '%s', amount = '%f', shop = '%s', comment = '%s', receipt = '%d', invoice = '%d', expensecategory = '%d', moneysource = '%d', registertime = '%s', vat = '%f', vatamt = '%f', brand = '%d', type = '%d', status = '%d', country = '%s', subcat = '%d', personal = '%d', refundable = '%d', refunded = '%d', hwsw = '%s' WHERE expenseid = '%d';",
$userid,
$expense,
$moneysource,
$other,
$amount,
$shop,
$comment,
$receipt,
$invoice,
$expenseCat,
$moneysource,
$registertime,
$vat,
$vatamt,
$brand,
$type,
$status,
$country,
$expenseCatSub,
$personal,
$refundable,
$refunded,
$hwsw,
$expenseid
);

		} else {
			
		// Query to update expense
		$updateExpense = sprintf("UPDATE expenses_mklnew SET userid = '%d', expense = '%s', moneysource = '%d', other = '%s', amount = '%f', shop = '%s', comment = '%s', receipt = '%d', invoice = '%d', expensecategory = '%d', moneysource = '%d', registertime = '%s', vat = '%f', vatamt = '%f', brand = '%d', paymentdate = '%s', type = '%d', status = '%d', country = '%s', subcat = '%d', personal = '%d', refundable = '%d', refunded = '%d', hwsw = '%s' WHERE expenseid = '%d';",
$userid,
$expense,
$moneysource,
$other,
$amount,
$shop,
$comment,
$receipt,
$invoice,
$expenseCat,
$moneysource,
$registertime,
$vat,
$vatamt,
$brand,
$paymentdate,
$type,
$status,
$country,
$expenseCatSub,
$personal,
$refundable,
$refunded,
$hwsw,
$expenseid
);

		}
		try
		{
			$result = $pdo3->prepare("$updateExpense")->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
				
			
			
			
		// On success: redirect.
		$_SESSION['successMessage'] = $lang['expense-updatesuccess'];
		header("Location: expense-mkl.php?expenseid={$expenseid}");
		exit();
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
	
	// Query to look up expense
	$selectExpenses = "SELECT expenseid, registertime, userid, expensetype, expense, moneysource, other, amount, shop, comment, receipt, expensecategory, invoice, vat, brand, paymentdate, type, status, country, subcat, personal, refundable, refunded, hwsw FROM expenses_mklnew WHERE expenseid = $expenseid";
		try
		{
			$result = $pdo3->prepare("$selectExpenses");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
	$expenseid = $row['expenseid'];
	$registertime = $row['registertime'];
	$userid = $row['userid'];
	$expensetype = $row['expensetype'];
	$expense = $row['expense'];
	$moneysource = $row['moneysource'];
	$other = $row['other'];
	$amount = $row['amount'];
	$shop = $row['shop'];
	$comment = $row['comment'];
	$receipt = $row['receipt'];
	$invoice = $row['invoice'];
	$vat = $row['vat'];
	$expenseCatOrig = $row['expensecategory'];
	$expenseCatSub = $row['subcat'];
	$brand = $row['brand'];
	$paymentdate = $row['paymentdate'];
	$type = $row['type'];
	$status = $row['status'];
	$country = $row['country'];
	$personal = $row['personal'];
	$refundable = $row['refundable'];
	$refunded = $row['refunded'];
	$hwsw = $row['hwsw'];

	
	// Look up user details
	$userDetails = "SELECT memberno, first_name, last_name FROM users WHERE user_id = '{$userid}'";
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
		$memberno = $row['memberno'];
		$first_name = $row['first_name'];
		$last_name = $row['last_name'];


	

	pageStart($lang['title-editexpense'], NULL, $validationScript, "pexpenses", "admin", $lang['expense-edit'], $_SESSION['successMessage'], $_SESSION['errorMessage']);
	
?>
<div class="actionbox">

<form id="registerForm" action="" method="POST">
<span class="fakelabel"><?php echo $lang['expense-purchasedby']; ?>:</span>
  <select class="fakeInput" name="userSelect">
  <option value="<?php echo $userid; ?>"><?php echo "#" . $memberno . " - " . $first_name . " " . $last_name; ?></option>
<?php
      	// Query to look up pre-registered users:
		$userDetails = "SELECT user_id, memberno, first_name, last_name FROM users WHERE userGroup != '6' AND user_id != $userid ORDER BY memberno ASC";
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

<?php

      	// Query to look up category name:
		if ($_SESSION['lang'] == 'es') {
			$categories = "SELECT namees FROM expensecategories WHERE categoryid = $expenseCatOrig";
		try
		{
			$result = $pdo3->prepare("$categories");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
			$name = $row['namees'];
		} else {
			$categories = "SELECT nameen FROM expensecategories WHERE categoryid = $expenseCatOrig";
		try
		{
			$result = $pdo3->prepare("$categories");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
			$name = $row['nameen'];
		}
		
		$categories = "SELECT nameen FROM expensecategories WHERE categoryid = $expenseCatSub";
		try
		{
			$result = $pdo3->prepare("$categories");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
			$subCatName = $row['nameen'];
		
?>
</span>
<br />
<span class="fakelabel"><?php echo $lang['global-category']; ?>:</span>
<select name="expenseCat" id="expenseCat">
 <option value="<?php echo $expenseCatOrig; ?>"><?php echo $name; ?></option>

<?php
      	// Query to look up categories:
		if ($_SESSION['lang'] == 'es') {
			$selectExpenseCat = "SELECT categoryid, namees, descriptiones FROM expensecategories WHERE categoryid > 45 AND sub = 0 ORDER BY nameen ASC";
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
			$selectExpenseCat = "SELECT categoryid, nameen, descriptionen FROM expensecategories WHERE categoryid > 45 AND sub = 0 ORDER BY nameen ASC";
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
  		
  		
	$selectExpenseCat = "SELECT categoryid, nameen, descriptionen FROM expensecategories WHERE sub = $expenseCatOrig AND categoryid <> $expenseCatSub";
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
	
		while ($category = $results->fetch()) {
		
	  	$categoryid = $category['categoryid'];
	  	$catname = $category['nameen'];
		
		$subcategory_row .= "<option value='$categoryid'>$catname</option>";

	}

?>

  
  
</select>
<br />
<span class="fakelabel">Subcategory:</span>
<select name="expenseCatSub" id="expenseCatSub">
 <option value="<?php echo $expenseCatSub; ?>"><?php echo $subCatName; ?></option>
 <?php echo $subcategory_row; ?>
</select>
<br />
<span class="fakelabel" style="margin-top: 5px;">HW or SW:</span>
	<div class='fakeboxholder'>	
	 <label class="control">
	  <span id="errorBox2"></span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;HW
	  <input type="radio" name="hwsw" value="HW" id="accept2" <?php if ($hwsw == 'HW') { echo " checked"; }?>/>
	  <div class="fakebox"></div>
	 </label>
	</div>
&nbsp;&nbsp;&nbsp;		
	<div class='fakeboxholder'>	
	 <label class="control">
	  <span id="errorBox3"></span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;SW
	  <input type="radio" name="hwsw" value="SW" id="accept3" <?php if ($hwsw == 'SW') { echo " checked"; }?>/>
	  <div class="fakebox"></div>
	 </label>
	</div>

<br />

<br />
<span class="fakelabel"><?php echo $lang['global-expense']; ?>:</span><input type="text" name="expense" placeholder="<?php echo $lang['expense-what']; ?>?" value="<?php echo $expense; ?>" class="eightDigit" />
<br />
<span class="fakelabel"><?php echo $lang['global-shop']; ?>:</span><input type="text" name="shop" placeholder="<?php echo $lang['expense-where']; ?>?" value="<?php echo $shop; ?>" class="eightDigit" />
<br />
<span class="fakelabel">Country:</span><input type="text" name="country" value="<?php echo $country; ?>" class="eightDigit" />
<br />
<span class="fakelabel">Full amount:</span><input type="number" lang="nb" name="amount" placeholder="&euro;" value="<?php echo $amount; ?>" class="fourDigit" />
<br />
<span class="fakelabel">VAT %:</span><input type="number" lang="nb" name="vat" placeholder="%" value="<?php echo $vat; ?>" class="fourDigit" />
<br />
<span class="fakelabel" style="margin-top: 5px;">Type:</span>
	<div class='fakeboxholder'>	
	 <label class="control">
	  <span id="errorBox2"></span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Goods
	  <input type="radio" name="type" value="1" id="accept2" <?php if ($type == 1) { echo " checked"; }?>/>
	  <div class="fakebox"></div>
	 </label>
	</div>
&nbsp;&nbsp;&nbsp;		
	<div class='fakeboxholder'>	
	 <label class="control">
	  <span id="errorBox3"></span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Services
	  <input type="radio" name="type" value="2" id="accept3" <?php if ($type == 2) { echo " checked"; }?>/>
	  <div class="fakebox"></div>
	 </label>
	</div>
<br /><br />

<span class="fakelabel" style="margin-top: 5px;">Status:</span>
	<div class='fakeboxholder'>	
	 <label class="control">
	  <span id="errorBox2"></span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Business
	  <input type="radio" name="status" value="1" id="accept2" <?php if ($status == 1) { echo " checked"; }?>/>
	  <div class="fakebox"></div>
	 </label>
	</div>
&nbsp;&nbsp;&nbsp;	
	<div class='fakeboxholder'>	
	 <label class="control">
	  <span id="errorBox3"></span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Private
	  <input type="radio" name="status" value="2" id="accept3" <?php if ($status == 2) { echo " checked"; }?>/>
	  <div class="fakebox"></div>
	 </label>
	</div>
<br /><br />

<span class="fakelabel">Invoice date:</span>
 <input type="number" lang="nb" name="day" id="day" class="twoDigit" style="margin-left: -1px;" maxlength="2" value="<?php echo date('d', strtotime($registertime)); ?>" />
 <input type="number" lang="nb" name="month" id="month" class="twoDigit" maxlength="2" value="<?php echo date('m', strtotime($registertime)); ?>" />
 <input type="number" lang="nb" name="year" id="year" class="fourDigit" maxlength="4" value="<?php echo date('Y', strtotime($registertime)); ?>" />
<br />
<?php
	if ($paymentdate != '0000-00-00 00:00:00' && $paymentdate != NULL && $paymentdate != '1970-01-01 01:00:00' ) {
		
?>
<span class="fakelabel">Accounting date:</span>
 <input type="number" lang="nb" name="day2" id="day2" class="twoDigit" style="margin-left: -1px;" maxlength="2" value="<?php echo date('d', strtotime($paymentdate)); ?>" />
 <input type="number" lang="nb" name="month2" id="month2" class="twoDigit" maxlength="2" value="<?php echo date('m', strtotime($paymentdate)); ?>" />
 <input type="number" lang="nb" name="year2" id="year2" class="fourDigit" maxlength="4" value="<?php echo date('Y', strtotime($paymentdate)); ?>" />
 <a href="#" class="smallerfont yellow" id="clickChange3">[Use inv. date]</a>
 <?php
 
	} else {
		
?>
<span class="fakelabel">Accounting date:</span>
 <input type="number" lang="nb" name="day2" id="day2" class="twoDigit" style="margin-left: -1px;" maxlength="2" value="" />
 <input type="number" lang="nb" name="month2" id="month2" class="twoDigit" maxlength="2" value="" />
 <input type="number" lang="nb" name="year2" id="year2" class="fourDigit" maxlength="4" value="" />
 <a href="#" class="smallerfont yellow" id="clickChange3">[Use inv. date]</a>

<?php

	}

?>

<br />
<span class="fakelabel" style="margin-top: 5px;"><?php echo $lang['expense-source']; ?>:</span>

  <select class="fakeInput" name="moneysource">
  
<?php

	if ($moneysource == 1) {
		$source = "Cash";
	} else if ($moneysource == 2) {
		$source = "Bank transfer";
	} else if ($moneysource == 6) {
		$source = "Direct Debit";
	} else if ($moneysource == 7) {
		$source = "MKL BBVA SW debit card";
	} else if ($moneysource == 8) {
		$source = "MKL BBVA HW debit card";
	} else if ($moneysource == 9) {
		$source = "MKL BBVA SW credit card";
	} else if ($moneysource == 10) {
		$source = "MKL BBVA HW credit card";
	} else if ($moneysource == 14) {
		$source = "Amazon";
	} else {
		$source = 'ERROR';
	}
	
	echo "<option value='$moneysource'>$source</option>";
	
  	if ($moneysource != 1) { echo "<option value='1'>Cash</option>"; }
  	if ($moneysource != 2) { echo "<option value='2'>Bank transfer</option>"; }
  	if ($moneysource != 6) { echo "<option value='6'>Direct Debit</option>"; }
  	if ($moneysource != 7) { echo "<option value='7'>MKL BBVA SW debit card</option>"; }
  	if ($moneysource != 8) { echo "<option value='8'>MKL BBVA HW debit card</option>"; }
  	if ($moneysource != 9) { echo "<option value='9'>MKL BBVA SW credit card</option>"; }
  	if ($moneysource != 10) { echo "<option value='10'>MKL BBVA HW credit card</option>"; }
  	if ($moneysource != 14) { echo "<option value='14'>Amazon</option>"; }
	
	

?>

  </select>
  <br />

<span class="fakelabel" style="margin-top: 5px;">Receipt?</span>
	<div class='fakeboxholder'>	
	 <label class="control">
	  <span id="errorBox2"></span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Yes
	  <input type="radio" name="receipt" value="1" id="accept2" <?php if ($receipt == 1) { echo " checked"; }?>/>
	  <div class="fakebox"></div>
	 </label>
	</div>
&nbsp;&nbsp;&nbsp;	
	<div class='fakeboxholder'>	
	 <label class="control">
	  <span id="errorBox3"></span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;No
	  <input type="radio" name="receipt" value="2" id="accept3"<?php if ($receipt == 2) { echo " checked"; }?> />
	  <div class="fakebox"></div>
	 </label>
	</div>
<br />
<br />
<span class="fakelabel" style="margin-top: 5px;">Invoice?</span>
	<div class='fakeboxholder'>	
	 <label class="control">
	  <span id="errorBox2"></span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Yes
	  <input type="radio" name="invoice" value="1" id="accept2" <?php if ($invoice == 1) { echo " checked"; }?>/>
	  <div class="fakebox"></div>
	 </label>
	</div>
&nbsp;&nbsp;&nbsp;	
	<div class='fakeboxholder'>	
	 <label class="control">
	  <span id="errorBox3"></span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;No
	  <input type="radio" name="invoice" value="0" id="accept3" <?php if ($invoice == 0) { echo " checked"; }?>/>
	  <div class="fakebox"></div>
	 </label>
	</div>
<br /><br />
<?php if ($_SESSION['user_id'] == 1404 || $_SESSION['user_id'] == 1402) { ?>

<span class="fakelabel" style="margin-top: 5px;">Personal expense?</span>
	<div class='fakeboxholder'>	
	 <label class="control">
	  <span id="errorBox3"></span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
	  <input type="checkbox" name="personal" value="1" id="accept3" <?php if ($personal == 1) { echo "checked"; } ?>/>
	  <div class="fakebox"></div>
	 </label>
	</div>

<br /><br />
<?php } ?>
<span class="fakelabel" style="margin-top: 5px;">Refundable?</span>
	<div class='fakeboxholder'>	
	 <label class="control">
	  <span id="errorBox3"></span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
	  <input type="checkbox" name="refundable" value="1" id="accept3" <?php if ($refundable == 1) { echo "checked"; } ?>/>
	  <div class="fakebox"></div>
	 </label>
	</div>
<br /><br />
<span class="fakelabel" style="margin-top: 5px;">Refunded?</span>
	<div class='fakeboxholder'>	
	 <label class="control">
	  <span id="errorBox3"></span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
	  <input type="checkbox" name="refunded" value="1" id="accept3" <?php if ($refunded == 1) { echo "checked"; } ?>/>
	  <div class="fakebox"></div>
	 </label>
	</div>
<br /><br />
	
<span class="fakelabel" style="vertical-align: top;"><?php echo $lang['global-comment']; ?>:</span><textarea name="comment" placeholder="<?php echo $lang['global-comment']; ?>?"><?php echo $comment; ?></textarea>

 <button class='oneClick' name='oneClick' type="submit"><?php echo $lang['global-save']; ?></button>
 
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
	$("#categoryLink").html("&nbsp;[Change]");
	$("#categoryLink").css("font-size", "11px");
	});	

	$('input[name="expenseCat"]').change(function() {
    			$('#selectedRadio').text(eval('desc'+this.value));
});




    $('input[type="radio"]').click(function(){

        if($(this).attr("value")=="12"){

            $("#hideMe").css("display", "block");

        }
    });

	$("#clickChange3").click(function () {
	
	var v1 = $("#day").val();
	var v2 = $("#month").val();
	var v3 = $("#year").val();
	
	$("#day2").val(v1);
	$("#month2").val(v2);
	$("#year2").val(v3);
	});	
   
	
	
</script>
EOD;

displayFooter();