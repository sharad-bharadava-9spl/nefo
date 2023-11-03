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
		$oldamount = $_POST['oldamount'];

		$owndate = $_POST['owndate'];
		$hour = $_POST['hour'];
		$minute = $_POST['minute'];
		
		$fulldate = "$owndate $hour:$minute";
		$registertime = date("Y-m-d H:i:s", strtotime($fulldate));
	
		// Query to update expense
		$updateExpense = sprintf("UPDATE expenses SET userid = '%d', expense = '%s', moneysource = '%d', other = '%s', amount = '%f', shop = '%s', comment = '%s', receipt = '%d', invoice = '%d', expensecategory = '%d', registertime = '%s' WHERE expenseid = '%d';",
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
$registertime,
$expenseid
);

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
			
		// Write to log
		$logTime = date('Y-m-d H:i:s');
	
		$query = sprintf("INSERT INTO log (logtype, logtime, user_id, operator, amount, oldCredit) VALUES ('%d', '%s', '%d', '%d', '%f', '%f');",
		14, $logTime, $userid, $_SESSION['user_id'], $amount, $oldamount);
		
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
		$_SESSION['successMessage'] = $lang['expense-updatesuccess'];
		header("Location: expense.php?expenseid={$expenseid}");
		exit();
	}
	/***** FORM SUBMIT END *****/

	
	$validationScript = <<<EOD
	
	  $( function() {
	    $( "#datepicker" ).datepicker({
			dateFormat: "dd-mm-yy"
	    });
	  });

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
	$selectExpenses = "SELECT expenseid, registertime, userid, expensetype, expense, moneysource, other, amount, shop, comment, receipt, expensecategory, invoice FROM expenses WHERE expenseid = $expenseid";
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
	$expenseCatOrig = $row['expensecategory'];
	
	
	

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
<input type='hidden' name='oldamount' value="<?php echo $amount; ?>" />
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
		
?>
</span>
<br />
<span class="fakelabel"><?php echo $lang['global-category']; ?>:</span>
<select name="expenseCat">
 <option value="<?php echo $expenseCatOrig; ?>"><?php echo $name; ?></option>

<?php
      	// Query to look up categories:
		if ($_SESSION['lang'] == 'es') {
			$selectExpenseCat = "SELECT categoryid, namees, descriptiones FROM expensecategories ORDER BY categoryid ASC";
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
			$selectExpenseCat = "SELECT categoryid, nameen, descriptionen FROM expensecategories ORDER BY categoryid ASC";
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

			
			$category_row = "<option value='$expenseCatId'>$expenseCat (<span class='smallerFont'>$expenseDesc</span>)</option>";

				
	  			echo $category_row;
	  			
  		}
?>

  
  
</select>

<br />
<span class="fakelabel"><?php echo $lang['global-expense']; ?>:</span><input type="text" name="expense" placeholder="<?php echo $lang['expense-what']; ?>?" value="<?php echo $expense; ?>" class="eightDigit" />
<br />
<span class="fakelabel"><?php echo $lang['global-shop']; ?>:</span><input type="text" name="shop" placeholder="<?php echo $lang['expense-where']; ?>?" value="<?php echo $shop; ?>" class="eightDigit" />
<br />
<span class="fakelabel"><?php echo $lang['global-amount']; ?>:</span><input type="number" lang="nb" name="amount" placeholder="&euro;" value="<?php echo $amount; ?>" class="fourDigit" />
<br />
<span class="fakelabel"><?php echo $lang['pur-date']; ?>:</span>
 <input type="text" lang="nb" name="owndate" id="datepicker" class="fiveDigit" value="<?php echo date('d-m-Y', strtotime($registertime)); ?>" /> @
 <input type="number" lang="nb" name="hour" id="hour" class="oneDigit" maxlength="2" value="<?php echo date('H', strtotime($registertime)); ?>" /> :
 <input type="number" lang="nb" name="minute" id="minute" class="oneDigit" maxlength="2" value="<?php echo date('i', strtotime($registertime)); ?>" />
<br />
<span class="fakelabel" style="margin-bottom: 4px;"><?php echo $lang['expense-source']; ?>:</span>
<span>
 <input type="radio" name="moneysource" value="1" style="margin-left: 5px;"<?php if ($moneysource == 1) { echo " checked"; }?>><?php echo $lang['global-till']; ?></input>
 <input type="radio" name="moneysource" value="2"<?php if ($moneysource == 2) { echo " checked"; }?> style="margin-left: 27px;"><?php echo $lang['global-bank']; ?></input>
 </span>
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
