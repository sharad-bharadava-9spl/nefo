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
		$shop = $_POST['shop'];
		$amount = $_POST['amount'];
		$moneysource = $_POST['moneysource'];
		$other = $_POST['other'];
		$receipt = $_POST['receipt'];
		$invoice = $_POST['invoice'];
		$comment = $_POST['comment'];
		$vat = $_POST['vat'];

		$day = $_POST['day'];
		$month = $_POST['month'];
		$year = $_POST['year'];
		
		$date_timestamp = strtotime($month . "/" . $day . "/" . $year);
		$registertime = date("Y-m-d H:i:s", $date_timestamp);
		
		$vatamt = $amount - ($amount / ((100 + $vat) / 100));
		
		// Query to update expense
		$updateExpense = sprintf("UPDATE expenses SET userid = '%d', expense = '%s', moneysource = '%d', other = '%s', amount = '%f', shop = '%s', comment = '%s', receipt = '%d', invoice = '%d', expensecategory = '%d', moneysource = '%d', registertime = '%s', vat = '%f', vatamt = '%f' WHERE expenseid = '%d';",
mysql_real_escape_string($userid),
mysql_real_escape_string($expense),
mysql_real_escape_string($moneysource),
mysql_real_escape_string($other),
mysql_real_escape_string($amount),
mysql_real_escape_string($shop),
mysql_real_escape_string($comment),
mysql_real_escape_string($receipt),
mysql_real_escape_string($invoice),
mysql_real_escape_string($expenseCat),
mysql_real_escape_string($moneysource),
mysql_real_escape_string($registertime),
mysql_real_escape_string($vat),
mysql_real_escape_string($vatamt),
mysql_real_escape_string($expenseid)
);

		mysql_query($updateExpense)
			or handleError($lang['error-savedata'],"Error updating expense: " . mysql_error());
			
		// On success: redirect.
		$_SESSION['successMessage'] = $lang['expense-updatesuccess'];
		header("Location: expense.php?expenseid={$expenseid}");
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
			  shop: {
				  required: true
			  },
			  moneysource: {
				  required: true
			  },
			  receipt: {
				  required: true
			  },
			  amount: {
				  required: true
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
	$selectExpenses = "SELECT expenseid, registertime, userid, expensetype, expense, moneysource, other, amount, shop, comment, receipt, expensecategory, invoice, vat FROM expenses WHERE expenseid = $expenseid";

	$result = mysql_query($selectExpenses)
		or handleError($lang['error-expensesload'],"Error loading expense from db: " . mysql_error());
		
	// check if expense ID exists
	if(mysql_num_rows($result) == 0) {
   		handleError($lang['error-expenseidnotexist'],"");
	}

	if ($result) {
	$row = mysql_fetch_array($result);
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
	
} else {
		handle_error($lang['error-problemfindinginfo'],"Error locating expense with ID {$expenseid}");
}

	// Look up user details
	$userDetails = "SELECT memberno, first_name, last_name FROM users WHERE user_id = '{$userid}'";
	
	// Does user ID exist?
	$userCheck = mysql_query($userDetails);
	if(mysql_num_rows($userCheck) == 0) {
   		handleError($lang['error-useridnotexist'],"");
	}
	
	$result = mysql_query($userDetails)
		or handleError($lang['error-userload'],"Error loading user: " . mysql_error());
	
	$row = mysql_fetch_array($result);
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
		$result = mysql_query($userDetails)
			or handleError($lang['error-usersload'],"Error loading users from db: " . mysql_error());
			
		while ($user = mysql_fetch_array($result)) {
				$user_row = sprintf("<option value='%d'>#%s - %s %s</option>",
	  								 $user['user_id'], $user['memberno'], $user['first_name'], $user['last_name']);
	  			echo $user_row;
  		}
?>
</select>
<br />
<span class="fakelabel"><?php echo $lang['global-category']; ?>:</span> <span id="selectedRadio"> <?php

      	// Query to look up category name:
		if ($_SESSION['lang'] == 'es') {
			$categories = "SELECT namees FROM expensecategories WHERE categoryid = $expenseCatOrig";
		$result = mysql_query($categories)
			or handleError($lang['error-categoriesload'],"Error loading categories from db: " . mysql_error());
			
		$row = mysql_fetch_array($result);
			$name = $row['namees'];
		} else {
			$categories = "SELECT nameen FROM expensecategories WHERE categoryid = $expenseCatOrig";
		$result = mysql_query($categories)
			or handleError($lang['error-categoriesload'],"Error loading categories from db: " . mysql_error());
			
		$row = mysql_fetch_array($result);
			$name = $row['nameen'];
		}
		
		echo $name;
		
?>
</span><a href="#" id="categoryLink" style="font-size: 11px;">&nbsp;[Change]</a>

<div id="categoryBox" style="display: none;">

<?php
      	// Query to look up categories:
		if ($_SESSION['lang'] == 'es') {
			$selectExpenseCat = "SELECT categoryid, namees, descriptiones FROM expensecategories ORDER BY nameen ASC";
			$catResult = mysql_query($selectExpenseCat)
				or handleError($lang['error-expensesload'],"Error loading expense from db: " . mysql_error());
		} else {
			$selectExpenseCat = "SELECT categoryid, nameen, descriptionen FROM expensecategories ORDER BY nameen ASC";
			$catResult = mysql_query($selectExpenseCat)
				or handleError($lang['error-expensesload'],"Error loading expense from db: " . mysql_error());
		}
			
		while ($category = mysql_fetch_array($catResult)) {
			
			if ($_SESSION['lang'] == 'es') {
		  	    $expenseCatId = $category['categoryid'];
		  	    $expenseCat = $category['namees'];
		  	    $expenseDesc = $category['descriptiones'];
	  	    } else {
		  	    $expenseCatId = $category['categoryid'];
		  	    $expenseCat = $category['nameen'];
		  	    $expenseDesc = $category['descriptionen'];
	  	    }

			
			if ($category['categoryid'] == $expenseCatOrig) {
				
				$category_row = sprintf("<input type='radio' id='radio%d' name='expenseCat' value='%d' checked><label for='radio%d' class='labelLaunch' onmouseover=%sdocument.getElementById('label%ddesc').style.display = 'block';%s onmouseout=%sdocument.getElementById('label%ddesc').style.display = 'none';%s>%s</label><span id='label%ddesc' class='labelDesc' style='display: none;'>(%s)</span><br /><script>var desc%d = '%s';</script>",
					$expenseCatId, $expenseCatId, $expenseCatId, '"', $expenseCatId, '"', '"', $expenseCatId, '"', $expenseCat, $expenseCatId, $expenseDesc, $expenseCatId, $expenseCat);
				
			} else {
			
				$category_row = sprintf("<input type='radio' id='radio%d' name='expenseCat' value='%d'><label for='radio%d' class='labelLaunch' onmouseover=%sdocument.getElementById('label%ddesc').style.display = 'block';%s onmouseout=%sdocument.getElementById('label%ddesc').style.display = 'none';%s>%s</label><span id='label%ddesc' class='labelDesc' style='display: none;'>(%s)</span><br /><script>var desc%d = '%s';</script>",
					$expenseCatId, $expenseCatId, $expenseCatId, '"', $expenseCatId, '"', '"', $expenseCatId, '"', $expenseCat, $expenseCatId, $expenseDesc, $expenseCatId, $expenseCat);
					
			}

				
	  			echo $category_row;
	  			
  		}
?>

  
  
</div>

<br />
<span class="fakelabel"><?php echo $lang['global-expense']; ?>:</span><input type="text" name="expense" placeholder="<?php echo $lang['expense-what']; ?>?" value="<?php echo $expense; ?>" class="eightDigit" />
<br />
<span class="fakelabel"><?php echo $lang['global-shop']; ?>:</span><input type="text" name="shop" placeholder="<?php echo $lang['expense-where']; ?>?" value="<?php echo $shop; ?>" class="eightDigit" />
<br />
<span class="fakelabel"><?php echo $lang['global-amount']; ?>:</span><input type="number" lang="nb" name="amount" placeholder="&euro;" value="<?php echo $amount; ?>" class="fourDigit" />
<br />
<span class="fakelabel">VAT %:</span><input type="number" lang="nb" name="vat" placeholder="%" value="<?php echo $vat; ?>" class="fourDigit" />
<br />
<span class="fakelabel"><?php echo $lang['pur-date']; ?>:</span>
 <input type="number" lang="nb" name="day" id="day" class="twoDigit" style="margin-left: -1px;" maxlength="2" value="<?php echo date('d', strtotime($registertime)); ?>" />
 <input type="number" lang="nb" name="month" id="month" class="twoDigit" maxlength="2" value="<?php echo date('m', strtotime($registertime)); ?>" />
 <input type="number" lang="nb" name="year" id="year" class="fourDigit" maxlength="4" value="<?php echo date('Y', strtotime($registertime)); ?>" />
<br />
<span class="fakelabel" style="margin-top: 5px;"><?php echo $lang['expense-source']; ?>:</span>

  <select class="fakeInput" name="moneysource">
  
<?php

	if ($moneysource == 1) {
		$source = "Cash";
	} else if ($moneysource == 2) {
		$source = "Bank transfer";
	} else if ($moneysource == 3) {
		$source = "Nefos debit card";
	} else if ($moneysource == 4) {
		$source = "Ahab credit card";
	} else if ($moneysource == 5) {
		$source = "Andy credit card";
	} else if ($moneysource == 6) {
		$source = "Direct Debit";
	} else if ($moneysource == 7) {
		$source = "MKL debit card";
	} else {
		$source = 'ERROR';
	}
	
	echo "<option value='$moneysource'>$source</option>";
	
  	if ($moneysource != 1) { echo "<option value='1'>Cash</option>"; }
  	if ($moneysource != 2) { echo "<option value='2'>Bank transfer</option>"; }
  	if ($moneysource != 3) { echo "<option value='3'>Nefos debit card</option>"; }
  	if ($moneysource != 4) { echo "<option value='4'>Ahab credit card</option>"; }
  	if ($moneysource != 5) { echo "<option value='5'>Andy credit card</option>"; }
  	if ($moneysource != 6) { echo "<option value='6'>Direct Debit</option>"; }
	
	

?>
  

  </select>
  <br />

<span class="fakelabel"><?php echo $lang['global-receipt']; ?>?</span>
<span>
 <input type="radio" name="receipt" value="1" style="margin-left: 5px;"<?php if ($receipt == 1) { echo " checked"; }?>><?php echo $lang['global-yes']; ?></input>
 <input type="radio" name="receipt" value="2"<?php if ($receipt == 2) { echo " checked"; }?>><?php echo $lang['global-no']; ?></input>
 </span>
<br />
<span class="fakelabel" style="margin-top: 5px;">Invoice?</span>
<span>
 <input type="radio" name="invoice" value="1" style="margin-left: 5px;"<?php if ($invoice == 1) { echo " checked"; }?>><?php echo $lang['global-yes']; ?></input>
 <input type="radio" name="invoice" value="0"<?php if ($invoice == 0) { echo " checked"; }?>><?php echo $lang['global-no']; ?></input>
 </span>
<br /><br />
<span class="fakelabel" style="vertical-align: top;"><?php echo $lang['global-comment']; ?>:</span><textarea name="comment" placeholder="<?php echo $lang['global-comment']; ?>?"><?php echo $comment; ?></textarea>

 <button class='oneClick' name='oneClick' type="submit"><?php echo $lang['global-save']; ?></button>
 
</form>
</div>
<script>

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

   
	
	
</script>

<?php displayFooter(); ?>
