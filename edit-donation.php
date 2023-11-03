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

	getSettings();
		
	// Did this page re-submit with a form? If so, check & store details
	
	if (isset($_POST['amount'])) {
		
		$userid = $_POST['userid'];
		$donationid = $_POST['donationid'];
  	    $credit = $_POST['credit'];
		$amount = $_POST['amount'];
		$comment = $_POST['comment'];
		$donatedTo = $_POST['donatedTo'];
		$origAmount = $_POST['origAmount'];
		$oldCreditOrig = $_POST['oldCredit'];
		$donationTime = $_POST['donationTime'];
		
		$registertime = date('Y-m-d H:i:s');
		
		$changedAmount = $amount - $origAmount; // -10
		
		
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

		$newCredit = $oldCredit + $changedAmount; // 97,5 - 10 = 87,5
		$newOldCredit = $oldCreditOrig + $amount;
		
		// Query to add to Donations table
		 $query = sprintf("UPDATE donations SET amount = '%f', comment = '%s', creditBefore = '%f', creditAfter = '%f', donatedTo = '%d' WHERE donationid = '%d';",
		  $amount, $comment, $oldCreditOrig, $newOldCredit, $donatedTo, $donationid);
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
		$updateUser = sprintf("UPDATE users SET credit = '%f' WHERE user_id = '%d';",
			$newCredit,
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
			
		// Write to log
		$logTime = date('Y-m-d H:i:s');
	
		$query = sprintf("INSERT INTO log (logtype, logtime, user_id, operator, amount, oldCredit, newCredit) VALUES ('%d', '%s', '%d', '%d', '%f', '%f', '%f');",
		9, $logTime, $userid, $_SESSION['user_id'], $changedAmount, $oldCredit, $newCredit);
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

		// Look up dispenses AFTER donation, sort by time ASC
		$userCredit = "SELECT 'dispense' AS type, saleid, saletime, amount, creditBefore, creditAfter FROM sales WHERE saletime > '$donationTime' UNION ALL SELECT 'bar' AS type, saleid, saletime, amount, creditBefore, creditAfter FROM b_sales WHERE saletime > '$donationTime' ORDER BY saletime ASC";
		try
		{
			$results = $pdo3->prepare("$userCredit");
			$results->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		while ($row = $results->fetch()) {
			$type = $row['type'];
			$saleid = $row['saleid'];
			$amount = $row['amount'];
			$creditBefore = $row['creditBefore'];
			$creditAfter = $row['creditAfter'];
			
			$newCreditBefore = $creditBefore + $changedAmount;
			$newCreditAfter = $creditAfter + $changedAmount;
		
			if ($type == 'dispense') {
				
				$query = "UPDATE sales SET creditBefore = '$newCreditBefore', creditAfter = '$newCreditAfter' WHERE saleid = $saleid";
				
			} else {
				
				$query = "UPDATE b_sales SET creditBefore = '$newCreditBefore', creditAfter = '$newCreditAfter' WHERE saleid = $saleid";
				
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
			
			
			
			
		}
				
			// On success: redirect.
			$_SESSION['successMessage'] = $lang['donation-edited'];
			header("Location: profile.php?user_id=$userid");
			exit();
	}
	/***** FORM SUBMIT END *****/
	
	
	if (isset($_GET['donationid'])) {
		$donationid = $_GET['donationid'];
	} else {
		handleError($lang['error-nomemberid'],"");
	}
	
	if (isset($_GET['userid'])) {
		$userid = $_GET['userid'];
	} else {
		handleError($lang['error-nomemberid'],"");
	}

		
	// Look up user details for showing profile on the Sales page
	$userDetails = "SELECT memberno, paidUntil, userGroup, first_name, last_name, credit, photoExt FROM users WHERE user_id = '{$userid}'";
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
		$paidUntil = $row['paidUntil'];
		$userGroup = $row['userGroup'];
		$credit = $row['credit'];
		$photoExt = $row['photoExt'];


	// Query to look up donations
	$selectExpenses = "SELECT donationTime, amount, donatedTo, comment, creditBefore FROM donations WHERE donationid = $donationid";
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
		$donationTime = date("d M H:i", strtotime($row['donationTime'] . "+$offsetSec seconds"));
		$amount = $row['amount'];
		$donatedTo = $row['donatedTo'];
		$comment = $row['comment'];
		$oldCredit = $row['creditBefore'];
			
	pageStart($lang['edit-donation'], NULL, $deleteDonationScript, "pprofilenew", 'donations', $lang['edit-donation'], $_SESSION['successMessage'], $_SESSION['errorMessage']);

/*echo "<center><div id='profilearea'><img src='images/members/$userid.$photoExt' class='salesPagePic' /><h4>#$memberno - $first_name $last_name</h4><div class='clearfloat'></div><span class='creditDisplay'>{$lang['global-credit']}: <span class='creditAmount'>" . number_format($credit,2) . "</span></span><br /><a href='change-credit.php?userid=$userid' class='yellow smallerfont hoverwhite'>[Change manually]</a></div></center>";*/
	$memberPhoto = 'images/members/' . $userid . '.' .  $photoExt;

	$object_exist = object_exist($google_bucket, $google_root_folder.$memberPhoto);
	
	if (!$object_exist) {
		$memberPhoto = "<img class='profilepic' src='{$google_root}images/silhouette-new-big.png' />";
		$notexist = 'yes';
	} else {
		$memberPhoto = "<img class='profilepic' src='{$google_root}$memberPhoto' width='237' />";
	}
?>
<div id="mainbox">
	<div id="mainleft">
	      <span id="profilepicholder"><?php echo $memberPhoto; ?></span>
	      <span class="firsttext"># <?php echo $memberno ?> </span><br><span class="nametext"><?php echo $first_name." ".$last_name ?></span><br><br><br>
	      <div class='clearfloat'></div>
	      <span class='creditDisplay'><?php echo $lang['global-credit'] ?>: <span class='creditAmount'><?php echo number_format($credit,2) ?></span></span><a href='change-credit.php?userid=$userid' class='yellow smallerfont hoverwhite cta4' style="left: -21px;">[Change manually]</a>
	</div>



	 <div id="donationholder">
	
		 <form id="registerForm" action="" method="POST">
		 
		 <h4><?php echo $lang['edit-donation']; ?></h4>
		  
		  <input type="hidden" name="userid" value="<?php echo $userid; ?>" />
		  <input type="hidden" name="credit" value="<?php echo $credit; ?>" />
		  <input type="hidden" name="donationid" value="<?php echo $donationid; ?>" />
		  <input type="hidden" name="origAmount" value="<?php echo $amount; ?>" />
		  <input type="hidden" name="oldCredit" value="<?php echo $oldCredit; ?>" />
		  <input type="hidden" name="donationTime" value="<?php echo $row['donationTime']; ?>" />
		  <table class='donationtable'>
		  	<tr>
		  		<td>
		  			 <table>
		  			 	<tr>
		  			 		<td>Amount</td>
		  			 		<td colspan="2"><?php echo $lang['paid-by']; ?></td>
		  			 	</tr>
		  			 	<tr>
						  <td><input type="number" lang="nb" name="amount" placeholder="<?php echo $_SESSION['currencyoperator'] ?>" value="<?php echo $amount; ?>" class="fourDigit defaultinput" step="0.01" /></td>
						<?php if ($_SESSION['bankPayments'] == 1) { ?>
						        <td><input type="radio" id="donatedTo1" name="donatedTo" value='1' <?php if ($donatedTo == 1) { echo "checked"; } ?> />
						       <label for="donatedTo1"><span class='full'><?php echo $lang['cash']; ?>&nbsp;</span></label></td>
						        
						        <td><input type="radio" id="donatedTo2" name="donatedTo" value='2' <?php if ($donatedTo == 2) { echo "checked"; } ?> />
						        <label for="donatedTo2"><span class='full'><?php echo $lang['bank-card']; ?></span></label></td>
						        
						<?php } ?>
							</tr>
							<tr>
							 <td colspan="3"><button class='oneClick okbutton2' name='oneClick' type="submit"><?php echo $lang['global-confirm']; ?></button></td>
							</tr>
					</table>
				</td> 
				<td style="padding-top: 10px;"><?php echo $lang['global-comment']; ?><br> <textarea name="comment"><?php echo $comment; ?></textarea></td>
			</tr>
		</table>
		 </form>
	
	</div>
</div>	 
	 
   
<?php displayFooter();
