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
	
	// Query to look up expense
	$selectExpense = "SELECT expenseid, registertime, userid, expensetype, expense, moneysource, other, amount, shop, comment, receipt, invoice, expensecategory, vatamt FROM expenses_nefos WHERE expenseid = $expenseid";
		try
		{
			$result = $pdo3->prepare("$selectExpense");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
  	    $userid = $row['userid']; // find member
  	    $expense = $row['expense'];
  	    $expenseid = $row['expenseid'];
  	    $expenseCat = $row['expensecategory'];
		$shop = $row['shop'];
		$amount = $row['amount'];
		$moneysource = $row['moneysource'];
		$other = $row['other'];
		$receipt = $row['receipt'];
		$invoice = $row['invoice'];
		$vatamt = $row['vatamt'];
		$formattedDate = date("d M H:i", strtotime($row['registertime'] . "+$offsetSec seconds"));
		
		
	if ($row['comment'] != '') {
		
		$commentRead = "
		                <img src='images/comments.png' id='comment$expenseid' /><div id='helpBox$expenseid' class='helpBox'>{$row['comment']}</div>
		                <script>
		                  	$('#comment$expenseid').on({
						 		'mouseover' : function() {
								 	$('#helpBox$expenseid').css('display', 'block');
						  		},
						  		'mouseout' : function() {
								 	$('#helpBox$expenseid').css('display', 'none');
							  	}
						  	});
						</script>
		                ";
		
	} else {
		
		$commentRead = "";
		
	}
		
		
	if ($moneysource == 1) {
		$source = "Cash";
	} else if ($moneysource == 2) {
		$source = "Nefos BBVA Bank transfer";
	} else if ($moneysource == 3) {
		$source = "Nefos BBVA debit card";
	} else if ($moneysource == 4) {
		$source = "Nefos BBVA Ahab credit card";
	} else if ($moneysource == 5) {
		$source = "Nefos BBVA Andy credit card";
	} else if ($moneysource == 6) {
		$source = "Nefos BBVA Direct Debit";
	} else if ($moneysource == 12) {
		$source = "Nefos BOI Direct Debit";
	} else if ($moneysource == 13) {
		$source = "Nefos BOI debit card";
	} else if ($moneysource == 14) {
		$source = "Amazon";
	} else {
		$source = 'ERROR';
	}
	
	if ($receipt == 1) {
		$recClass = "";
		$receipt = $lang['global-yes'];
	} else if ($receipt == 2) {
		$recClass = "negative";
		$receipt = $lang['global-no'];
	}
	
	if ($invoice == 1) {
		$invClass = "";
		$invoice = $lang['global-yes'];
	} else if ($invoice == 0) {
		$invClass = "negative";
		$invoice = $lang['global-no'];
	}
	
		$userDetails = "SELECT memberno, first_name from users WHERE user_id = $userid";
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
	
		$user = $result->fetch();
			$member = "#" . $user['memberno'] . " - " . $user['first_name'];

	if ($expenseCat == NULL) {
		$expenseCat = '';
	} else {
		if ($_SESSION['lang'] == 'es') {
			$selectExpenseCat = "SELECT namees FROM expensecategories WHERE categoryid = $expenseCat";
		try
		{
			$result = $pdo3->prepare("$selectExpenseCat");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
		  	    $expenseCat = $row['namees'];
		} else {
			$selectExpenseCat = "SELECT nameen FROM expensecategories WHERE categoryid = $expenseCat";
		try
		{
			$result = $pdo3->prepare("$selectExpenseCat");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
		  	    $expenseCat = $row['nameen'];
		}
	}

	
	pageStart($lang['title-expense'], NULL, NULL, "pexpenses", "admin", $lang['global-expense'] . " NEFOS", $_SESSION['successMessage'], $_SESSION['errorMessage']);
	
?>

<center><a href="expenses-nefos.php" class="cta">&laquo; Expenses</a>&nbsp;<a href="edit-expense-nefos.php?expenseid=<?php echo $expenseid; ?>" class="cta"><?php echo $lang['expense-edit']; ?></a></center>

	 <table class="default" id="detailedsale">
	  <thead>
	   <tr>
	    <th><?php echo $lang['global-time']; ?></th>
	    <th><?php echo $lang['global-category']; ?></th>
	    <th><?php echo $lang['global-expense']; ?></th>
	    <th><?php echo $lang['global-shop']; ?></th>
	    <th><?php echo $lang['global-member']; ?></th>
	    <th><?php echo $lang['global-amount']; ?></th>
	    <th>VAT</th>
	    <th><?php echo $lang['global-source']; ?></th>
	    <th><?php echo $lang['global-receipt']; ?></th>
	    <th><?php echo $lang['global-invoice']; ?></th>
	    <th></th>
	   </tr>
	  </thead>
	  <tbody>
	   <tr>
	    <td><?php echo $formattedDate; ?></td>
	    <td><?php echo $expenseCat; ?></td>
	    <td><?php echo $expense; ?></td>
	    <td><?php echo $shop; ?></td>
	    <td><?php echo $member; ?></td>
	    <td><?php echo number_format($amount,2); ?>&euro;</td>
	    <td><?php echo number_format($vatamt,2); ?>&euro;</td>
	    <td><?php echo $source; ?></td>
	    <td class="<?php echo $recClass;?>"><?php echo $receipt; ?></td>
	    <td class="<?php echo $invClass;?>"><?php echo $invoice; ?></td>
	    <td class='relative'><?php echo $commentRead; ?></td>
	   </tr>
	  </tbody>
	 </table>
	 
	 <br />
	 <?php if($comment) {
		 echo "<p><strong>{$lang['global-comment']}:</strong><br />" . $comment . "</p>";
	 } ?></p>




  </div> <!-- END RIGHTPANE -->
 </div> <!-- END DETAILEDINFO -->

<?php displayFooter(); ?>
