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
		
		$userid = $_POST['userid'];
		$donationid = $_POST['donationid'];
  	    $credit = $_POST['credit'];
		$amount = $_POST['amount'];
		$comment = $_POST['comment'];
		$donatedTo = $_POST['donatedTo'];
		$origAmount = $_POST['origAmount'];
		$oldCreditOrig = $_POST['oldCredit'];
		$registertime = date('Y-m-d H:i:s');
		
		$changedAmount = $amount - $origAmount; // -10
		
		
		$operator = $_SESSION['user_id'];
		
		// Look up user credit
		$userCredit = "SELECT credit FROM users WHERE user_id = '{$userid}'";
	
		$result = mysql_query($userCredit)
			or handleError($lang['error-userload'],"Error loading user: " . mysql_error());
	
		$row = mysql_fetch_array($result);
			$oldCredit = $row['credit'];

		$newCredit = $oldCredit + $changedAmount; // 97,5 - 10 = 87,5
		$newOldCredit = $oldCreditOrig + $amount;
		
		// Query to add to Donations table
		 $query = sprintf("UPDATE donations SET amount = '%f', comment = '%s', creditBefore = '%f', creditAfter = '%f', donatedTo = '%d' WHERE donationid = '%d';",
		  $amount, $comment, $oldCreditOrig, $newOldCredit, $donatedTo, $donationid);
		  
		mysql_query($query)
			or handleError($lang['error-savedonation'],"Error inserting donation: " . mysql_error());
		
		// Query to add to Donations table 2
		 $query = sprintf("UPDATE f_donations SET amount = '%f', comment = '%s', creditBefore = '%f', creditAfter = '%f', donatedTo = '%d' WHERE donationid = '%d';",
		  $amount, $comment, $oldCreditOrig, $newOldCredit, $donatedTo, $donationid);
		  
		mysql_query($query)
			or handleError($lang['error-savedonation'],"Error inserting donation: " . mysql_error());
			
		// Query to update user profile
		$updateUser = sprintf("UPDATE users SET credit = '%f' WHERE user_id = '%d';",
			mysql_real_escape_string($newCredit),
			mysql_real_escape_string($userid)
			);
				
		mysql_query($updateUser)
			or handleError($lang['error-savedata'],"Error updating user profile: " . mysql_error());
			
		// Write to log
		$logTime = date('Y-m-d H:i:s');
	
		$query = sprintf("INSERT INTO log (logtype, logtime, user_id, operator, amount, oldCredit, newCredit) VALUES ('%d', '%s', '%d', '%d', '%f', '%f', '%f');",
		9, $logTime, $userid, $_SESSION['user_id'], $changedAmount, $oldCredit, $newCredit);
		
		mysql_query($query)
			or handleError($lang['error-savedata'],"Error inserting purchase: " . mysql_error());
				
		$query = sprintf("INSERT INTO f_log (logtype, logtime, user_id, operator, amount, oldCredit, newCredit) VALUES ('%d', '%s', '%d', '%d', '%f', '%f', '%f');",
		9, $logTime, $userid, $_SESSION['user_id'], $changedAmount, $oldCredit, $newCredit);
		
		mysql_query($query)
			or handleError($lang['error-savedata'],"Error inserting purchase: " . mysql_error());
				
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
	
	$result = mysql_query($userDetails)
		or handleError($lang['error-userload'],"Error loading user: " . mysql_error());

	$row = mysql_fetch_array($result);
		$memberno = $row['memberno'];
		$first_name = $row['first_name'];
		$last_name = $row['last_name'];
		$paidUntil = $row['paidUntil'];
		$userGroup = $row['userGroup'];
		$credit = $row['credit'];
		$photoExt = $row['photoExt'];


	// Query to look up donations
	$selectExpenses = "SELECT donationTime, amount, donatedTo, comment, creditBefore FROM donations WHERE donationid = $donationid";

	$result2 = mysql_query($selectExpenses)
		or handleError($lang['error-donationload'],"Error loading expense from db: " . mysql_error());

	$row = mysql_fetch_array($result2);
		$donationTime = date("d M H:i", strtotime($row['donationTime'] . "+$offsetSec seconds"));
		$amount = $row['amount'];
		$donatedTo = $row['donatedTo'];
		$comment = $row['comment'];
		$oldCredit = $row['creditBefore'];
			
	pageStart($lang['edit-donation'], NULL, $deleteDonationScript, "pmembership", NULL, $lang['edit-donation'], $_SESSION['successMessage'], $_SESSION['errorMessage']);

echo "<center><div id='profilearea'><img src='images/members/$userid.$photoExt' class='salesPagePic' /><h4>#$memberno - $first_name $last_name</h4><div class='clearfloat'></div><span class='creditDisplay'>{$lang['global-credit']}: <span class='creditAmount'>" . number_format($credit,2) . "</span></span><br /><a href='change-credit.php?userid=$userid' class='yellow smallerfont hoverwhite'>[Change manually]</a></div></center>";

?>


<br />


 <div id="overviewWrap">
 <div class="overview" style="padding: 10px 50px;">
 <form id="registerForm" action="" method="POST">
 
 <h5><?php echo $lang['edit-donation']; ?></h5>
  
  <input type="hidden" name="userid" value="<?php echo $userid; ?>" />
  <input type="hidden" name="credit" value="<?php echo $credit; ?>" />
  <input type="hidden" name="donationid" value="<?php echo $donationid; ?>" />
  <input type="hidden" name="origAmount" value="<?php echo $amount; ?>" />
  <input type="hidden" name="oldCredit" value="<?php echo $oldCredit; ?>" />
  <input type="number" lang="nb" name="amount" placeholder="&euro;" value="<?php echo $amount; ?>" class="fourDigit" step="0.01" /><br />
  <textarea name="comment" placeholder="<?php echo $lang['global-comment']; ?>?"><?php echo $comment; ?></textarea><br /><br />
<?php if ($_SESSION['bankPayments'] == 1) { ?>
<span style="color: white;">
  <strong><?php echo $lang['donated-to']; ?>:</strong><br />
 <input type="radio" name="donatedTo" value="1" style="margin-left: 5px; width: 10px;" <?php if ($donatedTo == 1 || $moneysource == 0) { echo " checked"; }?>><?php echo $lang['global-till']; ?></input>
 <input type="radio" name="donatedTo" value="2" style="margin-left: 27px; width: 10px;" <?php if ($donatedTo == 2) { echo " checked"; }?>><?php echo $lang['global-bank']; ?></input><br />
</span>
<br />
<?php } ?>


 <button class='oneClick' name='oneClick' type="submit"><?php echo $lang['global-confirm']; ?></button>
 </form>
 </div>

	 
	 
   
<?php displayFooter();
