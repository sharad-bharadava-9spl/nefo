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
	
	$domain = $_SESSION['domain'];
	
	// Delete user
if (($_POST['oldUserGroup'] != 8) && ($_POST['userGroup'] == 8)) {
		// deleted
	$user_id      = $_POST['user_id'];
	$memberno      = $_POST['memberno'];
	$deleteTime = date('Y-m-d H:i:s');
	$paymentTime = date('Y-m-d H:i:s');
	
	
		$updateUser = sprintf("UPDATE users SET memberno = '0', userGroup = '8', first_name = 'DELETED', last_name = 'DELETED', email = 'DELETED', dni = 'DELETED', street = 'DELETED', streetnumber = 0, flat = '', telephone = 'DELETED', cardid = '', cardid2 = '', cardid3 = '', friend = '', form1 = '0', form2 = '0', deleteTime = '%s' WHERE user_id = '%d';",
$deleteTime,
$user_id
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
						
	// Look up photoext for image deleting
	$userDetails = "SELECT photoext, dniext1, dniext2 FROM users WHERE user_id = $user_id";
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
		$photoExt = $row['photoext'];
		$dniext1 = $row['dniext1'];
		$dniext2 = $row['dniext2'];
		
		$imgname = "images/_$domain/members/" . $user_id . "." . $photoExt;
		if (file_exists($imgname)) { unlink ($imgname); }
		
		$imgname2 = "images/_$domain/ID/" . $user_id . "-front." . $dniext1;
		if (file_exists($imgname2)) { unlink ($imgname2); }
		
		$imgname3 = "images/_$domain/ID/" . $user_id . "-back." . $dniext2;
		if (file_exists($imgname3)) { unlink ($imgname3); }
		
		$imgname4 = "images/_$domain/sigs/" . $user_id . ".png";
		if (file_exists($imgname4)) { unlink ($imgname4); }
		
		// Check if member has only 1 log entry AND is created today - if so, delete also the entry from memberpayments (as this means it's a newly created profile which they delete right away, possible duplicate of other profile
		$selectRows = "SELECT COUNT(id) FROM log WHERE user_id = $user_id";
		$rowCount = $pdo3->query("$selectRows")->fetchColumn();
		
		if ($rowCount < 2) {
			
			// Created today?
			$query = "SELECT registeredSince FROM users WHERE user_id = $user_id";
			try
			{
				$result = $pdo3->prepare("$query");
				$result->execute();
			}
			catch (PDOException $e)
			{
					$error = 'Error fetching user: ' . $e->getMessage();
					echo $error;
					exit();
			}
		
			$row = $result->fetch();
				$registeredSince = date('Y-m-d', strtotime($row['registeredSince']));
				$dateNow = date('Y-m-d');
			
			$query = "DELETE FROM memberpayments WHERE userid = $user_id";
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
		
		// Insert into log
		$timeNow = date('Y-m-d H:i:s');
		$query = "INSERT INTO log (logtype, logtime, user_id, operator) VALUES (16, '$timeNow', $user_id, {$_SESSION['user_id']})";
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
		
		$_SESSION['successMessage'] = $lang['member-userdeleted'];
		header("Location: profile.php?user_id={$user_id}");
		exit();

}

	// Give baja
	if (($_POST['oldUserGroup'] != 9) && ($_POST['userGroup'] == 9)) {
	
		$user_id = $_POST['user_id'];
		
		$bajaTime = date('Y-m-d H:i:s');
		
		if ($_SESSION['keepNumber'] == 1) {
			
			$updateUser = "UPDATE users SET userGroup = '9', bajaDate = '$bajaTime' WHERE user_id = $user_id";
			
		} else {
			
			$updateUser = "UPDATE users SET userGroup = '9', bajaDate = '$bajaTime', memberno = '0' WHERE user_id = $user_id";
			
		}
	
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
			
		$_SESSION['successMessage'] = $lang['user-baja'];
		header("Location: profile.php?user_id=$user_id");
		exit();
	}
	

	
	
	// Ban user
	if ($_POST['banned'] == 'true') {
		$user_id = $_POST['user_id'];
		$banComment = $_POST['banComment'];
		$banTime = date('Y-m-d H:i:s');

		$banUser = sprintf("UPDATE users SET banComment = '%s', banTime = '%s' WHERE user_id = '%d';",
$banComment,
$banTime,
$user_id
);

		try
		{
			$result = $pdo3->prepare("$banUser")->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}

		$_SESSION['successMessage'] = $lang['member-userbanned'];
		header("Location: profile.php?user_id={$user_id}");
		exit();
	}

	// If user is coming from "Make changes" on the PREVIEW page, we treat them differently:
	
	$source = $_GET['source'];
	
	// Did this page re-submit with a form? If so, check & store details
	if (isset($_POST['first_name'])) {
		
		$oldUserGroup = $_POST['oldUserGroup'];
		$userGroup = $_POST['userGroup'];
		$adminComment = $_POST['adminComment'];
		$first_name = str_replace("'","\'",str_replace('%', '&#37;', trim($_POST['first_name'])));
		$last_name = str_replace("'","\'",str_replace('%', '&#37;', trim($_POST['last_name'])));
		$email = $_POST['email'];
		$day = $_POST['day'];
		$month = $_POST['month'];
		$year = $_POST['year'];
		$nationality = $_POST['nationality'];
		$gender = $_POST['gender'];
		$dni = $_POST['dni'];
		$street = str_replace("'","\'",str_replace('%', '&#37;', trim($_POST['street'])));
		$streetnumber = $_POST['streetnumber'];
		$flat = $_POST['flat'];
		$postcode = $_POST['postcode'];
		$city = str_replace("'","\'",str_replace('%', '&#37;', trim($_POST['city'])));
		$country = str_replace("'","\'",str_replace('%', '&#37;', trim($_POST['country'])));
		$telephone = $_POST['telephone'];
		$mconsumption = $_POST['mconsumption'];
		$usageType = $_POST['usageType'];
		$signupsource = $_POST['signupsource'];
		$cardid = $_POST['cardid'];
		$cardid2 = $_POST['cardid2'];
		$cardid3 = $_POST['cardid3'];
		$photoid = $_POST['photoid'];
		$docid = $_POST['docid'];
		$doorAccess = $_POST['doorAccess'];
		$paidUntil = $_POST['paidUntil'];
		$origPaidUntil = $_POST['origPaidUntil'];
		$user_id      = $_POST['user_id'];
		$memberno      = $_POST['memberno'];
		$memberNumber = $_POST['memberNumber'];
		$oldmemberno = $_POST['oldmemberno'];
		$regform      = $_POST['regform'];
		$consform      = $_POST['consform'];
		$dniscan      = $_POST['dniscan'];
		$creditEligible  = $_POST['creditEligible'];
		$discount      = $_POST['discount'];
		$discountBar      = $_POST['discountBar'];
		$starCat      = $_POST['starCat'];
		$interview      = $_POST['interview'];
		$maxCredit      = $_POST['maxCredit'];
		$usergroup2      = $_POST['usergroup2'];
		$insertTime = date('Y-m-d H:i:s');
		$paymentTime = date('Y-m-d H:i:s');
		
if (($_SESSION['exentoset'] == 0 && $_SESSION['userGroup'] == 1) || $_SESSION['exentoset'] == 1) {
	
		$exento = $_POST['paidUntil'];
		
		if ($exento == 8888) {
			$exento = 1;
		} else {
			$exento = 0;
		}
		
	} else {
		
		$exento = $_POST['oldExento'];
		
	}

		

	// From baja to socio
	if (($_POST['oldUserGroup'] == 9) && ($_POST['userGroup'] == 5)) {
		
		// Only assign new number if member has no number!
		if ($oldmemberno == '0' || $oldmemberno == '' || $oldmemberno == NULL) {
			
			if ($_SESSION['normalNumbers'] == 1) {
				
					if ($memberNumber > 0) {
						
						$memberno = $memberNumber;
						
					} else {
				
						// Means the number is taken, so use highest free memberno
						$query = "SELECT max(memberno) FROM users";
						try
						{
							$result = $pdo3->prepare("$query");
							$result->execute();
						}
						catch (PDOException $e)
						{
								$error = 'Error fetching user: ' . $e->getMessage();
								echo $error;
								exit();
						}
					
						$row = $result->fetch();
							$oldmemberno = $memberno;
							$memberno = $row['0'] + 1;
							
					}
						
					
				} else {
			
					$memberInitials = strtoupper(substr($_POST['first_name'], 0,1)) . strtoupper(substr($_POST['last_name'], 0,1));
					$memberDigit = 1;
					
					$memberno = $memberInitials . $memberDigit;
					
					$memberMatch = 'false';
					
					while ($memberMatch == 'false') {
						
						// We've gotta check if the member number is available!
						$query = "SELECT memberno FROM users WHERE memberno = '$memberno'";
						try
						{
							$result = $pdo3->prepare("$query");
							$result->execute();
							$data = $result->fetchAll();
						}
						catch (PDOException $e)
						{
								$error = 'Error fetching user: ' . $e->getMessage();
								echo $error;
								exit();
						}
							
						if (!$data) {
							
							$memberMatch = 'true';
							
						} else {
							
							// Means the number is taken, so increase by 1 and try again
							$memberDigit = $memberDigit + 1;
							$memberno = $memberInitials . $memberDigit;
							
						}
					}
				}
			
		} else {
			
			$memberno = $oldmemberno;
			
		}
		
	} else {
		
		if ($_SESSION['normalNumbers'] == 1) {
			
			if ($memberno == '') {
				$memberno = $memberNumber;
			}
			
				
			// We've gotta check if the member number is still available!
			$query = "SELECT memberno FROM users WHERE memberno = $memberno AND user_id <> $user_id";
			try
			{
				$result = $pdo3->prepare("$query");
				$result->execute();
				$data = $result->fetchAll();
			}
			catch (PDOException $e)
			{
					$error = 'Error fetching user: ' . $e->getMessage();
					echo $error;
					exit();
			}
				
			if ($data) {
				
				// Means the number is taken, so use highest free memberno
				$query = "SELECT max(memberno) FROM users";
				try
				{
					$result = $pdo3->prepare("$query");
					$result->execute();
				}
				catch (PDOException $e)
				{
						$error = 'Error fetching user: ' . $e->getMessage();
						echo $error;
						exit();
				}
			
				$row = $result->fetch();
					$oldmemberno = $memberno;
					$memberno = $row['0'] + 1;
					
				// Create feedback message saying that hte memberno was taken, and that we've assigned $memberno to this member.
				$_SESSION['errorMessage'] = $lang['number-taken-1'] . $oldmemberno . $lang['number-taken-2'] . $memberno . $lang['number-taken-3'];
			}
			
		} else {
			
			$memberno = $oldmemberno;
			
		}
	}
	

	// Calculate Workstation access
	if ($_SESSION['userGroup'] == 1) {
		
		foreach($_POST['workStation'] as $workstationCheckbox) {
			
		    $workStation +=  $workstationCheckbox;
	
	  	}
	  	
  	} else {
	  	
	  	$workStation = $_POST['workStationTot'];
  	}
  		
	if ($memberno == '') {
		$memberno = $memberNumber;
	}
	
	// Alfanumeric member numbers BEGIN
 
		$updateUser = sprintf("UPDATE users SET memberno = '%s', userGroup = '%d', adminComment = '%s', first_name = '%s', last_name = '%s', email = '%s', day = '%d', month = '%d', year = '%d', nationality = '%s', gender = '%s', dni = '%s', street = '%s', streetnumber = '%d', flat = '%s', postcode = '%s', city = '%s', country = '%s', telephone = '%s', mconsumption = '%d', usageType = '%s', signupsource = '%s', cardid = '%s', cardid2 = '%s', cardid3 = '%s', photoid = '%d', docid = '%d', doorAccess = '%d', form1 = '%d', form2 = '%d', creditEligible = '%d', dniscan = '%d', workStation = '%d', starCat = '%d', interview = '%d', maxCredit = '%f', exento = '%d', usergroup2 = '%d' WHERE user_id = '%d';",
$memberno,
$userGroup,
$adminComment,
$first_name,
$last_name,
$email,
$day,
$month,
$year,
$nationality,
$gender,
$dni,
$street,
$streetnumber,
$flat,
$postcode,
$city,
$country,
$telephone,
$mconsumption,
$usageType,
$signupsource,
$cardid,
$cardid2,
$cardid3,
$photoid,
$docid,
$doorAccess,
$regform,
$consform,
$creditEligible,
$dniscan,
$workStation,
$starCat,
$interview,
$maxCredit,
$exento,
$usergroup2,
$user_id
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
		
	// Compare old user group to new usergroup. Use a HIDDEN form value or something to pass along the old one? if they don't match then launch password function (but perhaps do that AFTER saving the user).
	if (($oldUserGroup > 3) && ($userGroup < 4)) {
		$_SESSION['successMessage'] = $lang['member-userupdated'];
		header("Location: new-password.php?user_id={$user_id}");
		exit();
	} else if (($oldUserGroup != 7) && ($userGroup == 7)) {
		// Banned
		pageStart($lang['member-editmember'], NULL, $validationScript, "pprofilenew", "donations fees", $lang['member-editmember'] . " " . $first_name . " " . $last_name, $_SESSION['successMessage'], $_SESSION['errorMessage']);
		
		
		$banWindow = <<<EOD
<center><div id='donationholder2'>
<form id="registerForm" action="" method="POST" >
    <input type="hidden" name="user_id" value="$user_id" />
    <input type="hidden" name="banned" value="true" />

<h4>{$lang['member-banreason']}:</h4><br />
<textarea name="banComment" style='width: 346px; height: 100px;'></textarea><br /><br />
 <button class='oneClick okbutton2' name='oneClick' type="submit" style='margin-left: -2px; width: 286px;'>{$lang['global-savechanges']}</button>
   </form>
   </div>
</center>
EOD;
		echo $banWindow;
		exit();
				
	} else if ($_POST['source'] == 'not_approved') {
	$user_id = $_POST['user_id'];	
		header("Location: profile.php?user_id={$user_id}");
		exit();
	} else if ($_POST['intToReg'] == 'yes') {
		// $_SESSION['successMessage'] = "User added succesfully!";
		header("Location: profile.php?user_id={$user_id}");
		exit();
	} else {
		$_SESSION['successMessage'] = "User updated succesfully!";
		header("Location: profile.php?user_id={$user_id}");
		exit();
	}
	}
	/***** FORM SUBMIT END *****/

	$validationScript = <<<EOD
    $(document).ready(function() {
	    
	    
	    
	    
	    // if 2 or 3 is selected, hide box.
		var initialVal = $('#userGroup').val();
			if(initialVal < 2 || initialVal > 3) {
	        	$("#expiryBox").hide();				
			}
	    	    
	    $('#userGroup').change(function(){
			var val = $(this).val();
		    if(val < 4 && val > 1) {
		        $("#expiryBox").fadeIn('slow');
	    	} else {
		        $("#expiryBox").fadeOut('slow');
	    	}
	    });
	    
	    // if 2 or 3 is selected, hide box.
		var initialVal2 = $('#creditEligible').val();
			if(initialVal2 == 0) {
	        	$("#maxCreditHolder").hide();				
			}
	    	    
	    $('#creditEligible').change(function(){
			var val = $(this).val();
		    if(val == 1) {
		        $("#maxCreditHolder").fadeIn('slow');
	    	} else {
		        $("#maxCreditHolder").fadeOut('slow');
	    	}
	    });

	  $('#registerForm').validate({
		  rules: {
			  maxCredit: {
				  range:[0,1000000]
        	  },
			  day: {
				  required: true,
				  range:[1,31],
			  },
			  month: {
				  required: true,
				  range:[1,12],
			  },
			  year: {
				  required: true,
				  range:[1900,2001],
			  }
        	  
    	}, // end rules
		  errorPlacement: function(error, element) {
			  if ( element.is(":radio") || element.is(":checkbox")){
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
	
	// If REQ parameter set, check for admin rights to allow user to edit other users' profiles
	if (isset($_REQUEST['user_id'])) {
		if ($_SESSION['userGroup'] <= 3) {
			$user_id = $_REQUEST['user_id'];
		} else {
			handleError($lang['error-notauthorized']);
			exit();
		} // What if a user is trying to edit his own profile with a request ID? Well, they shouldn't??
	// ...this means user is trying to access his own profile
	} else if (isset($_SESSION['user_id'])) {
		$user_id = $_SESSION['user_id'];
	} else {
		handleError($lang['error-nouserid'],"");
	}
	
	// Query to look for user
	$userDetails = "SELECT u.user_id, u.memberno, u.registeredSince, u.first_name, u.last_name, u.email, u.day, u.month, u.year, u.nationality, u.gender, u.dni, u.street, u.streetnumber, u.flat, u.postcode, u.city, u.country, u.telephone, u.mconsumption, u.usageType, u.signupsource, u.cardid, u.cardid2, u.cardid3, u.photoid, u.docid, u.doorAccess, u.friend, u.paidUntil, u.adminComment, ug.userGroup, ug.groupName, ug.groupDesc, u.form1, u.form2, u.creditEligible, u.dniscan, u.discount, u.discountBar, u.photoext, u.workStation, u.starCat, u.interview, u.maxCredit, u.exento, u.usergroup2 FROM users u, usergroups ug WHERE u.userGroup = ug.userGroup AND u.user_id = '{$user_id}'";
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
	$user_id = $row['user_id'];
	$memberno = $row['memberno'];
	$registeredSince = $row['registeredSince'];
	$userGroup = $row['userGroup'];
	$groupName = $row['groupName'];
	$adminComment = $row['adminComment'];
	$first_name = $row['first_name'];
	$last_name = $row['last_name'];
	$email = $row['email'];
	$day = $row['day'];
	$month = $row['month'];
	$year = $row['year'];
	$nationality = $row['nationality'];
	$gender = $row['gender'];
	$dni = $row['dni'];
	$street = $row['street'];
	$streetnumber = $row['streetnumber'];
	$flat = $row['flat'];
	$postcode = $row['postcode'];
	$city = $row['city'];
	$country = $row['country'];
	$telephone = $row['telephone'];
	$mconsumption = $row['mconsumption'];
	$usageType = $row['usageType'];
	$signupsource = $row['signupsource'];
	$cardid = $row['cardid'];
	$cardid2 = $row['cardid2'];
	$cardid3 = $row['cardid3'];
	$photoid = $row['photoid'];
	$docid = $row['docid'];
	$doorAccess = $row['doorAccess'];
	$friend = $row['friend'];
	$paidUntil = $row['paidUntil'];
	$form1 = $row['form1'];
	$form2 = $row['form2'];
	$creditEligible = $row['creditEligible'];
	$dniscan = $row['dniscan'];
	$discount = $row['discount'];
	$discountBar = $row['discountBar'];
	$photoExt = $row['photoext'];
	$workStation = $row['workStation'];	
	$starCat = $row['starCat'];	
	$interview = $row['interview'];	
	$maxCredit = $row['maxCredit'];	
	$exento = $row['exento'];	
	$usergroup2 = $row['usergroup2'];	

	if ($starCat == 1) {
   		$starColour = $lang['yellow'];
	} else if ($starCat == 2) {
   		$starColour = $lang['black'];
	} else if ($starCat == 3) {
   		$starColour = $lang['green'];
	} else if ($starCat == 4) {
   		$starColour = $lang['red'];
	} else if ($starCat == 5) {
   		$starColour = $lang['purle'];
	} else if ($starCat == 6) {
   		$starColour = $lang['blue'];
	} else {
   		$starColour = "";
	}
	
	
	

		


if ($userGroup == 6) {
	pageStart($lang['title-newmember'], NULL, $validationScript, "pprofile", "final", $lang['member-newmember'] . ": " . $first_name . " " . $last_name, $_SESSION['successMessage'], $_SESSION['errorMessage']);
	
	

} else {
	pageStart($lang['member-editmember'], NULL, $validationScript, "pprofile", "final", $lang['member-editprofile'] . ": " . $first_name . " " . $last_name, $_SESSION['successMessage'], $_SESSION['errorMessage']);
}
		
	$memberPhoto = 'images/_' . $_SESSION['domain'] . '/members/' . $user_id . '.' .  $photoExt;
	
	if (!file_exists($memberPhoto)) {
		$memberPhoto = "<img class='profilepic' src='images/silhouette-new-big.png' />";
		$notexist = 'yes';
	} else {
		$memberPhoto = "<img class='profilepic' src='$memberPhoto' width='237' />";
	}

?>
   <form id="registerForm" action="" method="POST">


<?php if ($userGroup == 6) { ?>
    <input type="hidden" name="intToReg" value="yes" />
<?php } ?>

    <input type="hidden" name="user_id" value="<?php echo $user_id; ?>" />
    <input type="hidden" name="oldmemberno" value="<?php echo $memberno; ?>" />
    <input type="hidden" name="oldUserGroup" value="<?php echo $userGroup; ?>" />
    <input type="hidden" name="workStationTot" value="<?php echo $workStation; ?>" />
    <input type="hidden" name="oldExento" value="<?php echo $exento; ?>" />
    
 
 <div id='mainbox'>
  <div id='mainboxheader'>
  <?php echo $lang['member-details']; ?>
  </div>
  <div class='boxcontent'>

<div id='profilepicholder'>
<?php echo $memberPhoto; ?>
</div>
   <!-- 
<?php
	if ($_SESSION['normalNumbers'] == 1) {
		
	$query = "select max(memberno) from users";
		try
		{
			$result = $pdo3->prepare("$query");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
			$nextMemberNo = $row['0'] + 1;
		
?>

<input type="number" lang="nb" id="memberno" class="twoDigit memberGroup" name="memberno" value="<?php echo $nextMemberNo; ?>" readonly /> or 
  <select name="memberNumber" id="memberNumber" class="memberGroup" style="width: 60px;">
   <option value=""></option>
<?php
	$sql = "SELECT memberno FROM users";
		try
		{
			$result = $pdo3->prepare("$sql");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		while ($row = $result->fetch()) {
   		$memberNumbers[] = $row['memberno'];
	}
	
	for ($i = 0; $i < $nextMemberNo; ++$i) {
		
		if (!in_array($i, $memberNumbers)) {
				echo "<option value='$i'>$i</option>";
    	}
	}
?>
  </select>
  
<script>

$('#memberNumber').change(function() {
  if($(this).val() != ''){
    $('#memberno').val('');
  }
});

$('#memberno').change(function() {
  if($(this).val() != ''){
    $('#memberNumber').val('');
  }
});


</script>

<?php } ?>
-->

<span class='uc'><?php echo $lang['member-firstnames']; ?></span>
<input type="text" name="first_name" placeholder="" style='width: 149px;' value="<?php echo $first_name; ?>" />
<span class='uc'><?php echo $lang['member-lastnames']; ?></span>
<input type="text" name="last_name" placeholder="" style='width: 235px;' value="<?php echo $last_name; ?>" />
  <select name="gender" style='width: 140px;'>
      <?php if ($gender == NULL) { ?><option value=""><?php echo $lang['global-choose']; ?>:</option> <?php } ?>
	  <option value="Male" <?php if ($gender == 'Male') {echo "selected";} ?>><?php echo $lang['member-male']; ?></option>
	  <option value="Female" <?php if ($gender == 'Female') {echo "selected";} ?>><?php echo $lang['member-female']; ?></option>
     </select>
  <br /><br />
  <div class='testbox'>
  <select name="userGroup" id="userGroup">
        <option value='<?php echo $userGroup; ?>'><?php echo $userGroup . ' - ' . $groupName; ?></option>
<?php
      	// Query to look up usergroups
      	if ($_SESSION['userGroup'] == 1) {
			$selectGroups = "SELECT userGroup, groupName, groupDesc FROM usergroups WHERE userGroup < 10 ORDER by userGroup ASC";
		} else {
			$selectGroups = "SELECT userGroup, groupName, groupDesc FROM usergroups WHERE userGroup > 3 AND userGroup < 10 ORDER by userGroup ASC";
		}
		try
		{
			$result = $pdo3->prepare("$selectGroups");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		while ($group = $result->fetch()) {
			
			if ($group['userGroup'] != $userGroup) {
				$group_row = sprintf("<option value='%d'>%d - %s</option>",
	  								 $group['userGroup'], $group['userGroup'], $group['groupName']);
	  			echo $group_row;
  			}
  		}
?>
  </select> 
<?php if ($_SESSION['puestosOrNot'] == 1) { ?>

	<div id="expiryBox">
 	<div class='fakeboxholder'>	
	 <label class="control">
	  <span id="errorBox4"></span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?php echo $lang['reception']; ?>
	  <input type="checkbox" name='workStation[]' value='1' />
	  <div class="fakebox"></div>
	 </label>
	</div><br />
	<div class='fakeboxholder'>	
	 <label class="control">
	  <span id="errorBox5"></span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?php echo $lang['bar']; ?>
	  <input type="checkbox" name='workStation[]' value='5' />
	  <div class="fakebox"></div>
	 </label>
	</div><br />
	<div class='fakeboxholder'>	
	 <label class="control">
	  <span id="errorBox6"></span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?php echo $lang['dispensary']; ?>
	  <input type="checkbox" name='workStation[]' value='10' />
	  <div class="fakebox"></div>
	 </label>
	</div>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
    </div>

<?php } ?>
  </div>
          <select name="usergroup2" id="usergroup2">
          
<?php

		$selectGroups = "SELECT name FROM usergroups2 WHERE id = $usergroup2";
		try
		{
			$result = $pdo3->prepare("$selectGroups");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
			$usergroup2name = $row['name'];

		if ($usergroup2 == 0) {
?>
        <option value='0'><?php echo $lang['no-usergroup']; ?></option>
<?php
		} else {
?>
        <option value='<?php echo $usergroup2; ?>'><?php echo $usergroup2 . ' - ' . $usergroup2name; ?></option>
        <option value='0'><?php echo $lang['no-usergroup']; ?></option>
<?php
  		}
  		
      	// Query to look up usergroups
		$selectGroups = "SELECT id, name FROM usergroups2 WHERE id <> $usergroup2 ORDER by id ASC";
		try
		{
			$result = $pdo3->prepare("$selectGroups");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		while ($group = $result->fetch()) {
			if ($group['id'] != $usergroups2) {
				$group_row = sprintf("<option value='%d'>%d - %s</option>",
	  								 $group['id'], $group['id'], $group['name']);
	  			echo $group_row;
  			}
  		}
?>
	   </select>
	   
	   
<?php if ($_SESSION['membershipFees'] == 1) {
	if (($_SESSION['exentoset'] == 0 && $_SESSION['userGroup'] == 1) || $_SESSION['exentoset'] == 1) {

	?>
  <select name="paidUntil" id="paidUntil" style='vertical-align: top;'>

  <?php 
  
  if ($exento == 1) {
		echo "<option value='8888'>{$lang['exempt']}</option><option value='0'>{$lang['global-no']}</option><option value=''>{$lang['exempt-from-fee']}</option>";
  } else {
		echo "<option value='0'>{$lang['exempt-from-fee']}</option><option value='8888'>{$lang['exempt']}</option><option value='0'>{$lang['global-no']}</option>";
  }
  
  echo "</select>";
}
  
}

	$monthNow = date('m'); 
	$next_month = ++$monthNow;

	if($next_month == 13) {
		$next_month = 1;
	}
	
	$nextMonthName = date('F', mktime(0, 0, 0, $next_month, 10));
	
  ?>
<br />



  
   <select name="usageType">
    <?php if ($usageType == NULL) { ?><option value=""><?php echo $lang['global-select']; ?>:</option> <?php } ?>
    <option value="0" <?php if ($_SESSION['memberType'] == 0) {echo "selected";} ?>><?php echo $lang['member-recreational']; ?></option>
    <option value="1" <?php if ($_SESSION['memberType'] == 1) {echo "selected";} ?>><?php echo $lang['member-medicinal']; ?></option>
   </select>
<span class='uc'>&nbsp;<?php echo $lang['member-consumptiong']; ?></span>
   <input type="text" class="twoDigit" name="mconsumption" value="<?php echo $mconsumption; ?>" style="width: 77px;" />

  <div id="custompay" style="display: none;">
  
  <input type="number" name="customAmount" class="fourDigit" placeholder="&euro;" />
  </div>
  <br />
  
<?php 

if ($_SESSION['negcredit'] == 0) { ?>


     <select name="creditEligible" id="creditEligible">
      <option value="0"><?php echo $lang['dispense-without-credit']; ?></option>
	  <option value="0" <?php if ($creditEligible == 0) {echo "selected";} ?>><?php echo $lang['global-no']; ?></option>
	  <option value="1" <?php if ($creditEligible == 1) {echo "selected";} ?>><?php echo $lang['global-yes']; ?></option>
     </select>
     <span id='maxCreditHolder'>
     <?php echo $lang['until']; ?> <strong>-</strong><input type="text" id="maxCredit" name="maxCredit" class='twoDigit' value="<?php echo $maxCredit; ?>" />
     </span>
  
<?php } ?>

     <select name="interview">
	  <option value="0"><?php echo $lang['interviewed-member']; ?></option>
	  <option value="0" <?php if ($interview == 0) {echo "selected";} ?>><?php echo $lang['global-no']; ?></option>
	  <option value="1" <?php if ($interview == 1) {echo "selected";} ?>><?php echo $lang['global-yes']; ?></option>
     </select>

<br /><br /><br /><br />
<h4><?php echo $lang['member-personal']; ?></h4>
<img src="images/new-flag.png" style='margin-bottom: -2px;' />
<span class='uc'><?php echo $lang['member-nationality']; ?></span>
   <input type="text" name="nationality" style='width: 140px;' value="<?php echo $nationality; ?>" />
<img src="images/birthday.png" style='margin-bottom: -3px;' />
<span class='uc'><?php echo $lang['birthdate']; ?></span>
   <input type="number" lang="nb" name="day" class="oneDigit" maxlength="2" value="<?php echo $day; ?>" />
   <input type="number" lang="nb" name="month" class="oneDigit" maxlength="2" value="<?php echo $month; ?>"  style='margin-left: 5px;' />
   <input type="number" lang="nb" name="year" class="twoDigit" maxlength="4" value="<?php echo $year; ?>"  style='margin-left: 5px;' />
<img src="images/id.png" style='margin-bottom: -3px;' />
<span class='uc'><?php echo $lang['dni-or-passport']; ?></span>
   <input type="text" id="dni" class="idGroup" name="dni" style='width: 140px;' value="<?php echo $dni; ?>" /><br /><br />
   
<h4><?php echo $lang['member-contactdetails']; ?></h4>
   <input type="text" name="street" placeholder="<?php echo $lang['member-street']; ?>" value="<?php echo $street; ?>" />
   <input type="number" lang="nb" name="streetnumber" class="twoDigit" placeholder="No." value="<?php echo $streetnumber; ?>" />
   <input type="text" name="flat" class="twoDigit" placeholder="<?php echo $lang['member-flat']; ?>" value="<?php echo $flat; ?>" />
   <input type="text" name="postcode" class="fourDigit" placeholder="<?php echo $lang['member-postcode']; ?>" value="<?php echo $postcode; ?>" />
   <input type="text" name="city" placeholder="<?php echo $lang['member-city']; ?>" value="<?php echo $city; ?>" />
   <input type="text" name="country" placeholder="<?php echo $lang['member-country']; ?>" value="<?php echo $country; ?>" /><br />
   <input type="text" name="email" placeholder="E-mail" value="<?php echo $email; ?>" />  
   <input type="text" name="telephone" placeholder="<?php echo $lang['member-telephone']; ?>" value="<?php echo $telephone; ?>" />
<center><span class='uc'><?php echo $lang['chip']; ?><br />
<input type="text" name="cardid" id="input1" maxlength="30" value="<?php echo $cardid; ?>" />
</center>
 

</div></div><br />
<center>
 <button class='okbutton1' name='oneClick' type="submit"><?php echo $lang['global-savechanges']; ?></button>
</center>
</form>

<?php displayFooter(); ?>





















