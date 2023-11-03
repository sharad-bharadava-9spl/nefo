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

	
	// Did this page re-submit with a form? If so, check & store details
	
	// Write to: Scanhistory + donations + users
	if (isset($_POST['amount'])) {
		
		$userid = $_POST['userid'];
		$amount = $_POST['amount'];
		$credit = $_POST['credit'];
		$comment = $_POST['comment'];
		
		$registertime = date('Y-m-d H:i:s');
		
		$newcredit = $credit + $amount;
		$adjusted = $newcredit - $credit;
		
		// Query to update user profile
		$updateUser = sprintf("UPDATE users SET credit = '%f' WHERE user_id = '%d';",
			$newcredit,
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
			
		// Query to add to Donations table
		 $query = sprintf("INSERT INTO donations (userid, donationTime, type, amount, comment, creditBefore, creditAfter, donatedTo, operator) VALUES ('%d', '%s', '%d', '%f', '%s', '%f', '%f', '%d', '%d');",
		  $userid, $registertime, '2', $adjusted, $comment, $credit, $newcredit, '5', $_SESSION['user_id']);
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
			
		// Write to log
		$logTime = date('Y-m-d H:i:s');
	
		$query = sprintf("INSERT INTO log (logtype, logtime, user_id, operator, amount, oldCredit, newCredit) VALUES ('%d', '%s', '%d', '%d', '%f', '%f', '%f');",
		17, $logTime, $userid, $_SESSION['user_id'], $adjusted, $credit, $amount);
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
			$_SESSION['successMessage'] = $lang['credit-gifted'];
			header("Location: profile.php?user_id=$userid");
			exit();
	}
	/***** FORM SUBMIT END *****/
	
	
	if (isset($_GET['user_id'])) {
		$userid = $_GET['user_id'];
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

	$deleteDonationScript = <<<EOD
function delete_donation(donationid,amount,userid) {
	if (confirm("{$lang['donation-deleteconfirm']}")) {
				window.location = "uTil/delete-donation.php?donationid=" + donationid + "&amount=" + amount + "&userid=" + userid;
				}
}
    $(document).ready(function() {
	    
	    	    
	  $('#registerForm').validate({
		  ignore:'', //because the radio buttons are hidden, validation ignores them. This way it'll work.
		  rules: {
			  amount: {
				  required: true,
				  range: [0, 10000000]
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
			
	pageStart($lang['gift-credit'], NULL, $deleteDonationScript, "pprofilenew", "donations", $lang['gift-credit'], $_SESSION['successMessage'], $_SESSION['errorMessage']);

	$memberPhoto = 'images/_' . $_SESSION['domain'] . '/members/' . $userid . '.' .  $photoExt;

	$object_exist = object_exist($google_bucket, $google_root_folder.$memberPhoto);
	
	if (!$object_exist) {
		$memberPhoto = "<img class='profilepic' src='{$google_root}images/silhouette-new-big.png' />";
		$notexist = 'yes';
	} else {
		$memberPhoto = "<img class='profilepic' src='{$google_root}$memberPhoto' width='237' />";
	}
	
	if ($userGroup == 7) {
		$groupName = "<span class='usergrouptextbanned'>$groupName</span>";		
	} else {
		$groupName = "<span class='usergrouptext'>$groupName</span>";
		
	}

		$highRollerWeekly = $_SESSION['highRollerWeekly'];
		$consumptionPercentage = $_SESSION['consumptionPercentage'] / 100;

	// Is the user a high roller?
	if ($totalAmountPerWeek >= $highRollerWeekly && $notexist == 'yes') {
		$highroller = "<br /><img src='images/highroller-big.png' style='margin-top: -4px;' />";
	} else if ($totalAmountPerWeek >= $highRollerWeekly && $notexist != 'yes') {
		$highroller = "<br /><img src='images/highroller-xl.png' style='margin-top: -4px;' />";
	} else {
		$highroller = "";
	}
?>
	
<div id="mainbox">
 <div id="mainleft">
  <span id="profilepicholder"><a href="new-picture.php?user_id=<?php echo $userid; ?>"><?php echo $memberPhoto; ?></a><?php echo $highroller; ?></span>
<?php
	echo "<br /><br /><span class='creditDisplay'>Credit: <span class='creditAmount $userClass'>" . number_format($credit,2) . " ".$_SESSION['currencyoperator']."$creditEligibility";
	
	echo "</span></span><br />";
if ($_SESSION['creditchange'] == 1) {
	if ($_SESSION['userGroup'] == 1) {
		echo "<br /><a href='change-credit.php?userid=$userid' class='orange smallerfont'>[{$lang['change-manually']}]</a>";
	}
}
	echo "</div>";
?>
 
<div id='donationholder'>
 <form id="registerForm" action="" method="POST">
  <input type="hidden" name="userid" value="<?php echo $userid; ?>" />
  <input type="hidden" name="credit" value="<?php echo $credit; ?>" />
 <h4><?php echo $lang['gift-credit']; ?></h4>
 <br />
 <table class='donationtable'>
  <tr>
   <td>
    <table>
     <tr>
      <td><?php echo $lang['amount']; ?></td>
     </tr>
     <tr>
      <td><input type="number" lang="nb" name="amount" placeholder="<?php echo $_SESSION['currencyoperator'] ?>" class="defaultinput" style='width: 100px;' step="0.01" /></td>
     </tr>
     <tr>
      <td colspan='3'><button class='oneClick okbutton2' name='oneClick' type="submit"><?php echo $lang['global-confirm']; ?></button></td>
     </tr>
    </table>
   </td>
  </tr>
 </table>
 </form>
</div>
</div>
 

   
<?php displayFooter(); ?>
