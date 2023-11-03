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

	$_SESSION['max_crop_width'] = 1200;
	$_SESSION['max_crop_height'] = 1200;
    require_once 'crop-functions.php';
	
	// Did this page re-submit with a form? If so, check & store details
	if (isset($_POST['expense'])) {
		
		$userid = $_POST['userSelect'];
  	    $expense = str_replace('%', '&#37;', $_POST['expense']);
		$expenseCat = str_replace('%', '&#37;', $_POST['expenseCat']);
		$shop = str_replace('%', '&#37;', $_POST['shop']);
		$amount = $_POST['amount'];
		$moneysource = $_POST['moneysource'];
		$other = $_POST['other'];
		$receipt = $_POST['receipt'];
		$invoice = $_POST['invoice'];
		$comment = str_replace("'","\'",str_replace('%', '&#37;', trim($_POST['comment'])));
		$owndate = $_POST['owndate'];
		$hour = $_POST['hour'];
		$minute = $_POST['minute'];
		$currency = $_POST['currency'];
		
		$tempNo = $_SESSION ['tempNo'];
		$photoext = $_SESSION['expenseextension'];
		
		if ($owndate != '') {
			
			$fulldate = "$owndate $hour:$minute";
			$registertime = date("Y-m-d H:i:s", strtotime($fulldate));
			
		} else {
		
			$registertime = date('Y-m-d H:i:s'); // 	$purchaseDate = date('Y-m-d H:i:s'); ????

		}		

		if ($_SESSION['domain'] == 'dabulance') {
			
			// Query to add new sale to Sales table - 6 arguments
		  	$query = sprintf("INSERT INTO expenses (registertime, userid, expense, moneysource, other, amount, shop, comment, receipt, expensecategory, invoice, photoext, currency) VALUES ('%s', '%d', '%s', '%d', '%s', '%f', '%s', '%s', '%d', '%d', '%d', '%s', '%d');",
		  	$registertime, $userid, $expense, $moneysource, $other, $amount, $shop, $comment, $receipt, $expenseCat, $invoice, $photoext, $currency);
		  	
		} else {
			
			// Query to add new sale to Sales table - 6 arguments
		  	$query = sprintf("INSERT INTO expenses (registertime, userid, expense, moneysource, other, amount, shop, comment, receipt, expensecategory, invoice, photoext) VALUES ('%s', '%d', '%s', '%d', '%s', '%f', '%s', '%s', '%d', '%d', '%d', '%s');",
		  	$registertime, $userid, $expense, $moneysource, $other, $amount, $shop, $comment, $receipt, $expenseCat, $invoice, $photoext);
		  	
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
		$oldfile = "images/_$domain/expenses/" . $tempNo . "." . $photoext;
		$newfile = "images/_$domain/expenses/" . $expenseid . "." . $photoext;
		rename($oldfile, $newfile);
		
		// Write to log
		$logTime = date('Y-m-d H:i:s');
	
		$query = sprintf("INSERT INTO log (logtype, logtime, user_id, operator, amount) VALUES ('%d', '%s', '%d', '%d', '%f');",
		13, $logTime, $userid, $_SESSION['user_id'], $amount);
		
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
				header("Location: expenses.php");
			} else {
				header("Location: admin.php");
			}
			exit();
		}
	}
	/***** FORM SUBMIT END *****/
	
	$validationScript = <<<EOD
	
	  $( function() {
	    $( "#datepicker" ).datepicker({
			dateFormat: "dd-mm-yy"
	    });
	  });

    $(document).ready(function() {
	    
	   	function commaChange() {
	   	  $(this).val($(this).val().replace(/,/g, '.'));
	   	  $(this).val($(this).val().replace(' ', ''));
	   	  $(this).val($(this).val().replace('g', ''));
        }
        $('#amount').bind('keypress keyup blur change', commaChange);

	    	    
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
				  required: true,
				  range:[1,50]
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
			  invoice: {
				  required: true
			  },
			  amount: {
				  required: true
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

	if (!isset($_GET['skipFoto'])) {

		$tempID = $_SESSION['tempNo'];
	
		// Check if a foto was taken by webcam
		if (isset($_POST['mydata'])) {
			$encoded_data = $_POST['mydata'];
			$binary_data = base64_decode( $encoded_data );
			
			$imgname = "images/_$domain/expenses/" . $tempID . ".jpg";
			// save to server (beware of permissions)
			$result = file_put_contents( $imgname, $binary_data );
			
			if (!$result) die($lang['error-imagesave']);
			
			$_SESSION['expenseextension'] = 'jpg';
			
			$_SESSION['image_path'] = $imgname;
			$_SESSION['success_url'] = "new-expense-1.php";
			$_SESSION['successMessage'] = $lang['receipt-saved'];
			header("Location: crop-image.php");
			exit();
	
			
		} else if(isset($_FILES['fileToUpload'])) {
				
				$image_fieldname = "fileToUpload";
		

			// Potential PHP upload errors
			$php_errors = array(1 => $lang['imgError1'],
								2 => $lang['imgError1'],
								3 => $lang['imgError2'],
								4 => $lang['imgError3']);
						
			// Check for any upload errors
			if ($_FILES[$image_fieldname]['error'] != 0) {
				 $_SESSION['errorMessage'] = $php_errors[$_FILES[$image_fieldname]['error']] . " " . $lang['try-again']; 
				header("Location: new-expense.php");
				exit();
			}
			
			// Check if a real file was uploaded
			if (is_uploaded_file($_FILES[$image_fieldname]['tmp_name'])) {
				
			} else {
				 $_SESSION['errorMessage'] = $lang['imgError4']; 
				header("Location: new-expense.php");
				exit();
			}
			
			// Is this actually an image?
			if (getimagesize($_FILES[$image_fieldname]['tmp_name'])) {
				
			} else {
				 $_SESSION['errorMessage'] = $lang['imgError5']; 
				header("Location: new-expense.php");
				exit();
			}
			
			// Save the file and store the extension for later db entry
			$extension = pathinfo($_FILES[$image_fieldname]['name'], PATHINFO_EXTENSION);
			$upload_filename = "images/_$domain/expenses/" . $tempID . "." . $extension;
			$_SESSION['expenseextension'] = $extension;
			
			if (move_uploaded_file($_FILES[$image_fieldname]['tmp_name'], $upload_filename)) {
				
			} else {
			     $_SESSION['errorMessage'] = $lang['imgError6']; 
				header("Location: new-expense.php");
				exit();
			}


			$_SESSION['image_path'] = $upload_filename;
			$_SESSION['success_url'] = "new-expense-1.php";
			$_SESSION['successMessage'] = $lang['receipt-saved'];
			header("Location: crop-image.php");
			exit();

			// $_SESSION['successMessage'] = $lang['receipt-saved']; 

		}
	}

	pageStart($lang['title-newexpense'], NULL, $validationScript, "pexpenses", "admin dev-align-center newexpensepage", $lang['global-expenses'], $_SESSION['successMessage'], $_SESSION['errorMessage']);
	
	
?>


	<form id="registerForm" action="" method="POST">
	
			<div id="mainbox-no-width" >
				<div class="mainboxheader"><?php echo $lang['expense-register']; ?></div>
				<div class="boxcontent" >
					<table class="purchasetable">
							<tr>
								<td class="left">
									<strong><?php echo $lang['expense-purchasedby']; ?></strong>
									  <select class="defaultinput-no-margin-smallborder floatright" name="userSelect">
										  <option value=""><?php echo $lang['expense-choosemember']; ?></option>
										<?php
										      	// Query to look up pre-registered users:
												$userDetails = "SELECT user_id, memberno, first_name, last_name FROM users WHERE userGroup < 4 ORDER BY memberno ASC";
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
								</td>
								<td class="left">
									<strong><?php echo $lang['global-category']; ?></strong>
									<select name="expenseCat" class="defaultinput-no-margin-smallborder floatright" style="width: 236px;">
										 <option value=''><?php echo $lang['addremove-pleaseselect']; ?></option>


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
													
												/*		$category_row = sprintf("<input type='radio' id='radio%d' name='expenseCat' value='%d'><label for='radio%d' class='labelLaunch' onmouseover=%sdocument.getElementById('label%ddesc').style.display = 'block';%s onmouseout=%sdocument.getElementById('label%ddesc').style.display = 'none';%s>%s</label><span id='label%ddesc' class='labelDesc' style='display: none;'>(%s)</span><br /><script>var desc%d = '%s';</script>",
															$expenseCatId, $expenseCatId, $expenseCatId, '"', $expenseCatId, '"', '"', $expenseCatId, '"', $expenseCat, $expenseCatId, $expenseDesc, $expenseCatId, $expenseCat);*/
															
															
													$category_row = "<option value='$expenseCatId'>$expenseCat (<span class='smallerFont'>$expenseDesc</span>)</option>";
													
										  			echo $category_row;
											  			
										  		}
										?>
									</select>
								</td>
							</tr>  
							<tr>
								<td class="left">
									<strong><?php echo $lang['global-expense']; ?></strong><input type="text" name="expense" placeholder="<?php echo $lang['expense-what']; ?>?" class="eightDigit defaultinput-no-margin-smallborder floatright" />
								</td>
								<td class="left">
									<strong><?php echo $lang['global-shop']; ?></strong><input type="text" name="shop" placeholder="<?php echo $lang['expense-where']; ?>?" class="eightDigit defaultinput-no-margin-smallborder floatright" />
								</td>
							</tr>
							<tr>
								<td class="left">
									<strong><?php echo $lang['global-amount']; ?></strong><input type="text" lang="nb" name="amount" id="amount" placeholder="<?php if ($_SESSION['domain'] == 'dabulance') { echo '€ / $'; } else { echo $_SESSION['currencyoperator']; } ?>" class="fourDigit defaultinput-no-margin-smallborder floatright" />
								</td>
								<td class="left">
									<strong><?php echo $lang['pur-date']; ?></strong>
										<span id="dateshow" class="purchaseNumber" style="width:320px;">&nbsp;<?php echo date('d-m-Y'); ?> &nbsp;&nbsp;<a href="javascript:void(0);" class="smallerfont yellow" id="clickChange"><img src="images/edit.png"></a></span>
										<div id="customDate" style="display: none;">
											 <input type="text" lang="nb" name="owndate" id="datepicker" class="fiveDigit defaultinput-no-margin-smallborder" placeholder="<?php echo $lang['pur-date']; ?>" /> @
											 <input type="number" lang="nb" name="hour" id="hour" class="oneDigit defaultinput-no-margin-smallborder" maxlength="2" placeholder="hh" /> :
											 <input type="number" lang="nb" name="minute" id="minute" class="oneDigit defaultinput-no-margin-smallborder" maxlength="2" placeholder="mm" />
											 <a href="javascript:void(0);" class="smallerfont yellow" id="clickChange2">[<?php echo $lang['dispensary-today']; ?>]</a>
										</div>
									
								</td>
							</tr>
						</table>
						<div class="full-dev-box">
							<div class="left-dev-box">
<?php if ($_SESSION['domain'] == 'dabulance') { ?>
								<div class="mainboxheader">Currency</div>
								<table class="purchasetable radiotable">
									<tr>
										<td>
											 <div class="fakeboxholder customradio">	
												 <label class="control">
													  &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Euro
													  <input type="radio" name="currency" value="0">
													  <div class="fakebox"></div>
												 </label>
											</div>
										</td>
										<td>

										 	<div class="fakeboxholder customradio">	
												 <label class="control">
													  &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Dollar
													  <input type="radio" name="currency" value="1">
													  <div class="fakebox"></div>
												 </label>
											</div>
										</td>
									</tr>
								</table>
<?php } ?>
								<div class="mainboxheader"><?php echo $lang['expense-source']; ?></div>
								<table class="purchasetable radiotable">
									<tr>
										<td>
										 <!-- <input type="radio" name="moneysource" value="1" style="margin-left: 5px;"><?php //echo $lang['global-till']; ?></input> -->
											 <div class="fakeboxholder customradio">	
												 <label class="control">
													  &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?php echo $lang['global-till']; ?>
													  <input type="radio" name="moneysource" value="1">
													  <div class="fakebox"></div>
												 </label>
											</div>
										</td>
										<td>
										 <!-- <input type="radio" name="moneysource" value="2" style="margin-left: 30px;"><?php //echo $lang['global-bank']; ?></input> -->
										 	<div class="fakeboxholder customradio">	
												 <label class="control">
													  &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?php echo $lang['global-bank']; ?>
													  <input type="radio" name="moneysource" value="2">
													  <div class="fakebox"></div>
												 </label>
											</div>
										</td>
									</tr>
								</table>
								<div class="mainboxheader"><?php echo $lang['global-receipt']; ?></div>
								<table class="purchasetable radiotable">
									<tr>
										<td>	
										 	<!-- <input type="radio" name="receipt" value="1" style="margin-left: 5px;"><?php //echo $lang['global-yes']; ?></input> -->
										 	<div class="fakeboxholder customradio">	
												 <label class="control">
													  &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?php echo $lang['global-yes']; ?>
													  <input type="radio" name="receipt" value="1">
													  <div class="fakebox"></div>
												 </label>
											</div>
										</td>
										<td>
										 	<!-- <input type="radio" name="receipt" value="2" style="margin-left: 27px;"><?php //echo $lang['global-no']; ?></input> -->
										 	<div class="fakeboxholder customradio">	
												 <label class="control">
													  &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?php echo $lang['global-no']; ?>
													  <input type="radio" name="receipt" value="2">
													  <div class="fakebox"></div>
												 </label>
											</div>
										</td>
									</tr>
								</table>
								<div class="mainboxheader"><?php echo $lang['global-invoice']; ?></div>
								<table class="purchasetable radiotable">
									<tr>
										<td>	
										 	<!-- <input type="radio" name="invoice" value="1" style="margin-left: 5px;"><?php //echo $lang['global-yes']; ?></input> -->
										 	<div class="fakeboxholder customradio">	
												 <label class="control">
													  &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?php echo $lang['global-yes']; ?>
													  <input type="radio" name="invoice" value="1">
													  <div class="fakebox"></div>
												 </label>
											</div>
										</td>
										<td>
											 <!-- <input type="radio" name="invoice" value="0" style="margin-left: 27px;"><?php //echo $lang['global-no']; ?></input> -->
											 <div class="fakeboxholder customradio">	
												 <label class="control">
													  &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?php echo $lang['global-no']; ?>
													  <input type="radio" name="invoice" value="0">
													  <div class="fakebox"></div>
												 </label>
											</div>
										</td>
									</tr>
								</table>
							</div>
							<div class="right-dev-box">
								<div class="mainboxheader"><?php echo $lang['global-comment']; ?></div>
								<table class="purchasetable">
									<tr>
										<td>
											<textarea name="comment" class="defaultinput-no-margin-smallborder" placeholder="<?php echo $lang['global-comment']; ?>?" style="height: 264px; width:100%;" cols="50"></textarea>
										</td>
									</tr>
								</table>
							</div>
					   </div>		
				</div>
			</div>	
			<br>
			 <button type="submit" class="cta1"><?php echo $lang['expense-register']; ?></button>
		
    </form>

	<script>
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
		$("#datepicker").val("");
		$("#hour").val("");
		$("#minute").val("");
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
