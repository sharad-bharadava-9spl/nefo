<?php
	
	require_once 'cOnfig/connection.php';
	require_once 'cOnfig/view.php';
	require_once 'cOnfig/authenticate.php';
	require_once 'cOnfig/languages/common.php';
	
	session_start();
	$accessLevel = '3';
	
	// Authenticate & authorize
	authorizeUser($accessLevel);

	
	// Did this page re-submit with a form? If so, check & store details
	
	// Write to: Scanhistory + donations + users
	if (isset($_POST['comment'])) {
		
		$userid = $_POST['userid'];
		$comment = str_replace("'","\'",str_replace('%', '&#37;', trim($_POST['comment'])));
		$day = $_POST['day'];
		$month = $_POST['month'];
		$year = $_POST['year'];
		
		if ($day > 0 && $month > 0 && $year > 0) {
			$date_timestamp = strtotime($month . "/" . $day . "/" . $year);
			$registertime = date("Y-m-d H:i:s", $date_timestamp);
		} else {
			$registertime = date('Y-m-d H:i:s'); // 	$purchaseDate = date('Y-m-d H:i:s'); ????
		}

		// Query to add to Comments table
		 $query = sprintf("INSERT INTO customernotes (notetime, userid, note, worker, customer) VALUES ('%s', '%d', '%s', '%d', '%s');",
		  $registertime, $userid, $comment, $_SESSION['user_id'], $_SESSION['customer']);
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
			$_SESSION['successMessage'] = "Comment added successfully.";
			header("Location: customer.php?user_id=$userid");
			exit();
	}
	/***** FORM SUBMIT END *****/
	
	
	if (isset($_GET['userid'])) {
		$userid = $_GET['userid'];
	} else {
		handleError($lang['error-nomemberid'],"");
	}
		
		// Look up user details for showing profile on the Sales page
		$userDetails = "SELECT number, shortName FROM customers WHERE number = '{$_SESSION['customer']}'";
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
		$memberno = $row['number'];
		$first_name = $row['shortName'];
		
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
			  comment: {
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
			
	pageStart("Member notes", NULL, $deleteDonationScript, "pmembership", NULL, "MEMBER NOTES", $_SESSION['successMessage'], $_SESSION['errorMessage']);
echo "<center><div id='profilearea'><img src='../images/_{$_SESSION['customerdomain']}/logo.png' class='salesPagePic' /><h4>#$memberno - $first_name $last_name</h4><div class='clearfloat'></div></div></center>";

?>


<br />


 <div id="overviewWrap">

 <div class="overview" style="padding: 10px 50px;">
 <form id="registerForm" action="" method="POST">
   <h5>Create new note</h5>
   
<strong style="color: white;"><?php echo $lang['pur-date']; ?>:</strong>
<span id="dateshow" style="color: white;">
 <strong style="color: white;">&nbsp;<?php echo date('d-m-Y'); ?></strong> 
 <a href="#" class="smallerfont yellow" id="clickChange">[cambiar]</a>
</span>
<div id="customDate" style="display: none;">
 <input type="number" lang="nb" name="day" id="day" class="twoDigit" maxlength="2" placeholder="dd" />
 <input type="number" lang="nb" name="month" id="month" class="twoDigit" maxlength="2" placeholder="mm" />
 <input type="number" lang="nb" name="year" id="year" class="fourDigit" maxlength="4" placeholder="<?php echo $lang['member-yyyy']; ?>" />
 <a href="#" class="smallerfont yellow" id="clickChange2">[hoy]</a>
</div>
 <br /><br />
  
  <input type="hidden" name="userid" value="<?php echo $userid; ?>" />
  <textarea name="comment" style="height: 80px;"></textarea><br /><br />

 <button class='oneClick' name='oneClick' type="submit"><?php echo $lang['global-confirm']; ?></button>
 </form>
 </div>
 
	 
<script>
	$("#clickChange").click(function () {
	$("#dateshow").css("display", "none");
	$("#customDate").css("display", "block");
	});	
	$("#clickChange2").click(function () {
	$("#customDate").css("display", "none");
	$("#dateshow").css("display", "inline");
	$("#day").val("");
	$("#month").val("");
	$("#year").val("");
	});	
</script>
	 
   
<?php displayFooter(); ?>
