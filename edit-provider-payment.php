<?php
	
	require_once 'cOnfig/connection.php';
	require_once 'cOnfig/view.php';
	require_once 'cOnfig/authenticate.php';
	require_once 'cOnfig/languages/common.php';
	
	session_start();
	$accessLevel = '3';
	
	// Authenticate & authorize
	authorizeUser($accessLevel);

	getSettings();
	
	// Did this page re-submit with a form? If so, check & store details	
	if (isset($_POST['amount'])) {
		
		$providerid = $_POST['providerid'];
		$paymentid = $_POST['paymentid'];
		$amount = $_POST['amount'];
		$oldAmount = $_POST['oldAmount'];
		$comment = str_replace("'","\'",str_replace('%', '&#37;', trim($_POST['comment'])));
		
		// Look up provider credit
		$userCredit = "SELECT credit FROM providers WHERE id = '$providerid'";
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

		$newCredit = $oldCredit - $oldAmount + $amount;
		
		// Query to add to Donations table
		 $query = sprintf("UPDATE providerpayments SET amount = '%f', comment = '%s' WHERE paymentid = '%d';",
		  $amount, $comment, $paymentid);
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
		$updateUser = sprintf("UPDATE providers SET credit = '%f' WHERE id = '%d';",
			$newCredit,
			$providerid
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
		$_SESSION['successMessage'] = $lang['payment-edited'];
		header("Location: provider.php?providerid=$providerid");
		exit();
	}
	/***** FORM SUBMIT END *****/
	

	if (isset($_GET['paymentid'])) {
		$providerid = $_GET['providerid'];
		$paymentid = $_GET['paymentid'];
	} else {
		handleError($lang['error-nomemberid'],"");
	}

	// Query to look up provider
	$providerDetails = "SELECT name, providernumber, credit FROM providers WHERE id = $providerid";
	try
	{
		$result = $pdo3->prepare("$providerDetails");
		$result->execute();
	}
	catch (PDOException $e)
	{
			$error = 'Error fetching user: ' . $e->getMessage();
			echo $error;
			exit();
	}

	$row = $result->fetch();
		$name = $row['name'];
		$providernumber = $row['providernumber'];
		$credit = $row['credit'];
		
	// Query to look up payment details
	$providerDetails = "SELECT amount, comment FROM providerpayments WHERE paymentid = $paymentid";
	try
	{
		$result = $pdo3->prepare("$providerDetails");
		$result->execute();
	}
	catch (PDOException $e)
	{
			$error = 'Error fetching user: ' . $e->getMessage();
			echo $error;
			exit();
	}

	$row = $result->fetch();
		$amount = $row['amount'];
		$comment = $row['comment'];
		
	pageStart($lang['edit-provider-payment'], NULL, $deleteUserScript, "pmembership", "pmembership", $lang['edit-provider-payment'], $_SESSION['successMessage'], $_SESSION['errorMessage']); 
	
	

?>

<center>
<div id="mainbox-no-width">
 <div id="mainboxheader">
  <?php echo $lang['edit-provider-payment']; ?>
 </div>
 <div class='boxcontent'>
 
 <table class='default'>
  <tr>
   <td><?php echo $lang['provider']; ?>:</td>
   <td class='yellow fat'>#<?php echo $providernumber; ?></td>
  </tr>
  <tr>
   <td><?php echo $lang['global-name']; ?>:</td>
   <td class='yellow fat'><?php echo $name; ?></td>
  </tr>
  <tr>
   <td><?php echo $lang['global-credit']; ?>:</td>
   <td class='yellow fat'><?php echo number_format($credit,2); ?> <?php echo $_SESSION['currencyoperator'] ?></td>
  </tr>
 </table>
 <br />


 <form id="registerForm" action="" method="POST">
   
  <input type="hidden" name="providerid" value="<?php echo $providerid; ?>" />
  <input type="hidden" name="paymentid" value="<?php echo $paymentid; ?>" />
  <input type="hidden" name="oldAmount" value="<?php echo $amount; ?>" />
  <input type="number" lang="nb" name="amount" value="<?php echo $amount; ?>" class="fourDigit defaultinput" step="0.01" /><br />
  <textarea name="comment" placeholder="<?php echo $lang['global-comment']; ?>?" class='defaultinput' style='height: 100px;'><?php echo $comment; ?></textarea><br /><br />
  
 <button class='cta1' name='oneClick' type="submit"><?php echo $lang['global-confirm']; ?></button>
 </form>
 </div>
 </div>
 
   
<?php displayFooter();
