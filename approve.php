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

	// If user is coming from "Make changes" on the PREVIEW page, we treat them differently:
	
	$source = $_GET['source'];
	
	// Did this page re-submit with a form? If so, check & store details
	if (isset($_POST['first_name'])) {
		
		$oldUserGroup = $_POST['oldUserGroup'];
		$userGroup = $_POST['userGroup'];
		$adminComment = $_POST['adminComment'];
		$first_name = trim($_POST['first_name']);
		$last_name = trim($_POST['last_name']);
		$email = $_POST['email'];
		$day = $_POST['day'];
		$month = $_POST['month'];
		$year = $_POST['year'];
		$nationality = $_POST['nationality'];
		$gender = $_POST['gender'];
		$dni = $_POST['dni'];
		$street = $_POST['street'];
		$streetnumber = $_POST['streetnumber'];
		$flat = $_POST['flat'];
		$postcode = $_POST['postcode'];
		$city = $_POST['city'];
		$country = $_POST['country'];
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
		$insertTime = date('Y-m-d H:i:s');
		$paymentTime = date('Y-m-d H:i:s');
		
if (($_SESSION['exentoset'] == 0 && $_SESSION['userGroup'] == 1) || $_SESSION['exentoset'] == 1) {
	
		$exento = $_POST['exento'];
		
	} else {
		
		$exento = $_POST['oldExento'];
		
	}

		$memberno = $oldmemberno;
		

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
 
		$updateUser = sprintf("UPDATE users SET memberno = '%s', userGroup = '%d', adminComment = '%s', first_name = '%s', last_name = '%s', email = '%s', day = '%d', month = '%d', year = '%d', nationality = '%s', gender = '%s', dni = '%s', street = '%s', streetnumber = '%d', flat = '%s', postcode = '%s', city = '%s', country = '%s', telephone = '%s', mconsumption = '%d', usageType = '%s', signupsource = '%s', cardid = '%s', cardid2 = '%s', cardid3 = '%s', photoid = '%d', docid = '%d', doorAccess = '%d', form1 = '%d', form2 = '%d', creditEligible = '%d', dniscan = '%d', workStation = '%d', starCat = '%d', interview = '%d', maxCredit = '%f', exento = '%d' WHERE user_id = '%d';",
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
		$_SESSION['successMessage'] = "Usuario aprobado con &eacute;xito!";
		header("Location: reg-a.php?user_id={$user_id}");
		exit();
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
	$userDetails = "SELECT u.user_id, u.memberno, u.registeredSince, u.first_name, u.last_name, u.email, u.day, u.month, u.year, u.nationality, u.gender, u.dni, u.street, u.streetnumber, u.flat, u.postcode, u.city, u.country, u.telephone, u.mconsumption, u.usageType, u.signupsource, u.cardid, u.cardid2, u.cardid3, u.photoid, u.docid, u.doorAccess, u.friend, u.paidUntil, u.adminComment, u.form1, u.form2, u.creditEligible, u.dniscan, u.discount, u.discountBar, u.photoext, u.workStation, u.starCat, u.interview, u.maxCredit, u.exento FROM users u WHERE u.user_id = '{$user_id}'";
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

	if ($starCat == 1) {
   		$starColour = $lang['yellow'];
	} else if ($starCat == 2) {
   		$starColour = $lang['black'];
	} else if ($starCat == 3) {
   		$starColour = $lang['green'];
	} else if ($starCat == 4) {
   		$starColour = $lang['red'];
	} else {
   		$starColour = "";
	}
	

if ($userGroup == 6) {
	pageStart($lang['title-newmember'], NULL, $validationScript, "pprofile", "final", $lang['member-newmember'] . ": " . $first_name . " " . $last_name, $_SESSION['successMessage'], $_SESSION['errorMessage']);
	
	

} else {
	pageStart($lang['member-editmember'], NULL, $validationScript, "pprofile", "final", $lang['member-editprofile'] . ": " . $first_name . " " . $last_name, $_SESSION['successMessage'], $_SESSION['errorMessage']);
}
		
?>

<form id="registerForm" action="" method="POST">
  <center>
    <div id="mainbox">
        <div class="boxcontent">
            <?php if ($userGroup == 6) { ?>
                <input type="hidden" name="intToReg" value="yes" />
            <?php } ?>

                <input type="hidden" name="user_id" value="<?php echo $user_id; ?>" />
                <input type="hidden" name="oldmemberno" value="<?php echo $memberno; ?>" />
                <input type="hidden" name="oldUserGroup" value="<?php echo $userGroup; ?>" />
                <input type="hidden" name="workStationTot" value="<?php echo $workStation; ?>" />
                <input type="hidden" name="oldExento" value="<?php echo $exento; ?>" />
                
            
             


            <!--	/* Normal member numbers END */ -->
             <span class="uc"><?php echo $lang['member-firstnames']; ?></span>
              <input type="text" name="first_name" style='width: 149px;' value="<?php echo $first_name; ?>" />
            
              <span class="uc"><?php echo $lang['member-lastnames']; ?></span>
              <input type="text" name="last_name"  style='width: 149px;' value="<?php echo $last_name; ?>" />
           
            <span class="uc"><?php echo $lang['member-gender']; ?></span>
             
                 <select name="gender" style="width: 140px;">
                  <?php if ($gender == NULL) { ?><option value=""><?php echo $lang['global-choose']; ?>:</option> <?php } ?>
            	  <option value="Male" <?php if ($gender == 'Male') {echo "selected";} ?>><?php echo $lang['member-male']; ?></option>
            	  <option value="Female" <?php if ($gender == 'Female') {echo "selected";} ?>><?php echo $lang['member-female']; ?></option>
                 </select>
              
              <span class="uc"><?php echo $lang['colour']; ?></span>
             
                   <select name="starCat" id="starCat">
                   
            <?php 	if ($starColour == "") {
            			echo "<option value='$starCat'>{$lang['colour']}</option>";
            		} else {
            			echo "<option value='$starCat'>$starCat - $starColour</option>";
            		}
                    
            		echo "<option value='0'></option>";
            		echo "<option value='1'>1 - {$lang['yellow']}</option>";
            		echo "<option value='2'>2 - {$lang['black']}</option>";
            		echo "<option value='3'>3 - {$lang['green']}</option>";
            		echo "<option value='4'>4 - {$lang['red']}</option>";
             
            ?>

            		
            	   </select><br />
             
           <span class="uc"><?php echo $lang['member-usergroup']; ?></span>
             
                      <select name="userGroup" id="userGroup">
                    <option value='5'>Socio</option>
            <?php
                  
                  	// Query to look up usergroups
                  	
                  	if ($_SESSION['userGroup'] < 2) {
            			$selectGroups = "SELECT userGroup, groupName, groupDesc FROM usergroups ORDER by userGroup ASC";
            		} else {
            			$selectGroups = "SELECT userGroup, groupName, groupDesc FROM usergroups WHERE userGroup > 3 ORDER by userGroup ASC";
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

                    <span class="uc"><?php echo $lang['access-level']; ?></span>
                 
                <input type="checkbox" name="workStation[]" value="1" style="width: 12px;" <?php if ($workStation == 1 || $workStation == 6 || $workStation == 11 || $workStation == 16) {echo "checked";} if ($_SESSION['userGroup'] != 1) { echo " disabled"; } ?>><?php echo $lang['reception']; ?></input>
                <input type="checkbox" name="workStation[]" value="5" style="width: 12px;" <?php if ($workStation == 5 || $workStation == 6 || $workStation == 15 || $workStation == 16 ) {echo "checked";} if ($_SESSION['userGroup'] != 1) { echo " disabled"; } ?>><?php echo $lang['bar']; ?></input>
                <input type="checkbox" name="workStation[]" value="10" style="width: 12px;" <?php if ($workStation == 10 || $workStation == 11 || $workStation == 15 || $workStation == 16 ) {echo "checked";} if ($_SESSION['userGroup'] != 1) { echo " disabled"; } ?>><?php echo $lang['dispensary']; ?>
              
               
            <?php } ?>
            <br> <br>
            <span class="uc"><?php echo $lang['dispense-without-credit']; ?></span>
             
                 <select name="creditEligible" id="creditEligible" style='width: 60px;'>
            	  <option value="0" <?php if ($creditEligible == 0) {echo "selected";} ?>><?php echo $lang['global-no']; ?></option>
            	  <option value="1" <?php if ($creditEligible == 1) {echo "selected";} ?>><?php echo $lang['global-yes']; ?></option>
                 </select>
                 <span id='maxCreditHolder'>
                 Hasta: <strong>-</strong><input type="text" id="maxCredit" name="maxCredit" class='twoDigit' value="<?php echo $maxCredit; ?>" />
                 </span>
             
             <span class="uc"><?php echo $lang['interviewed-member']; ?></span>
              
                 <select name="interview" style='width: 60px;'>
            	  <option value="0" <?php if ($interview == 0) {echo "selected";} ?>><?php echo $lang['global-no']; ?></option>
            	  <option value="1" <?php if ($interview == 1) {echo "selected";} ?>><?php echo $lang['global-yes']; ?></option>
                 </select>
             
            <?php if ($_SESSION['membershipFees'] == 1) { ?>
            <?php if (($_SESSION['exentoset'] == 0 && $_SESSION['userGroup'] == 1) || $_SESSION['exentoset'] == 1) { ?>


             <span class="uc"><?php echo $lang['exempt-from-fee']; ?></span>
              
                 <select name="exento" id="exento" style='width: 60px;'>
            	  <option value="0" <?php if ($exento != 1) {echo "selected";} ?>><?php echo $lang['global-no']; ?></option>
            	  <option value="1" <?php if ($exento == 1) {echo "selected";} ?>><?php echo $lang['global-yes']; ?></option>
                 </select>
              
             
             
            <?php } } ?>
             <?php
            /*
            	if ($userGroup > 4 && $_SESSION['membershipFees'] == 1) {
            		
            		$memberExp = date('y-m-d', strtotime($paidUntil));
            		$memberExpReadable = date('d M Y', strtotime($paidUntil));
            		$timeNow = date('y-m-d');
            	
            		if ($memberExp == $timeNow) {
            			echo "<tr><td colspan='2'><span class='mid biggerfont2'><strong>&raquo; " . $lang['member-expirestoday'] . "!</strong></span></td></tr>";
            	  	} else if ($memberExp > $timeNow) {
            		  	echo "<tr><td colspan='2'><span class='positive biggerfont2 white'>&raquo; " . $lang['member-memberuntil'] . ": $memberExpReadable</span></td></tr>";
            		} else {
            		  	echo "<tr><td colspan='2'><h4 class='mid biggerfont2'><strong>&raquo; " . $lang['member-expiredon'] . ": $memberExpReadable</strong></h4></td></tr>";
            		}
            	}
            */
            ?>
            <br /><br /><br /><br />
             
                 
                   
                  <h4><?php echo $lang['member-personal']; ?></h4>
                  <input type="text" placeholder="<?php echo $lang['member-nationality']; ?>" name="nationality" value="<?php echo $nationality; ?>" />
                  <input type="number" lang="nb" class="twoDigit" placeholder="dd" maxlength="2" name="day" value="<?php echo $day; ?>" />
                       <input type="number" lang="nb" class="twoDigit" placeholder="mm" maxlength="2" name="month" value="<?php echo $month; ?>" />
                       <input type="number" lang="nb" class="fourDigit" placeholder="<?php echo $lang['member-yyyy']; ?>" maxlength="4" name="year" value="<?php echo $year; ?>" /><br />
                  <input type="text" name="dni" id="dni" placeholder="<?php echo $lang['dni-or-passport']; ?>" value="<?php echo $dni; ?>" /> <br /><br /><br /><br />
                  <h4><?php echo $lang['member-usage']; ?></h4>
                       <select name="usageType">
                        <?php if ($usageType == NULL) { ?><option value=""><?php echo $lang['global-select']; ?>:</option> <?php } ?>
                  	  <option value="0" <?php if ($usageType == '0') {echo "selected";} ?>><?php echo $lang['member-recreational']; ?></option>
                  	  <option value="1" <?php if ($usageType == '1') {echo "selected";} ?>><?php echo $lang['member-medicinal']; ?></option>
                       </select>
                       
                       <input type="text" class="twoDigit" name="mconsumption" value="<?php echo $mconsumption; ?>" /> <?php echo $lang['member-consumptiong']; ?>

                   
                    
                        <br /><br /><br /><br />
                  <h4><?php echo $lang['member-contactdetails']; ?></h4>
                  <input type="text" placeholder="<?php echo $lang['member-telephone']; ?>" name="telephone" value="<?php echo $telephone; ?>" />
                  <input type="text" placeholder="E-mail" name="email" value="<?php echo $email; ?>" />

                       <input type="text" name="street" placeholder="<?php echo $lang['member-street']; ?>" value="<?php echo $street; ?>" />
                       <input type="number" lang="nb" class="twoDigit" placeholder="No." name="streetnumber" value="<?php echo $streetnumber; ?>" />
                       <input type="text" class="twoDigit" placeholder="<?php echo $lang['member-flat']; ?>" name="flat" value="<?php echo $flat; ?>" />
                       <input type="text" class="fourDigit" placeholder="<?php echo $lang['member-postcode']; ?>" name="postcode" value="<?php echo $postcode; ?>" />
                       <input type="text" placeholder="<?php echo $lang['member-city']; ?>" name="city" value="<?php echo $city; ?>" />
                       <input type="text" placeholder="<?php echo $lang['member-country']; ?>" name="country" value="<?php echo $country; ?>" /><br /><br />

                   
                   
                 
                      </div>
                  </div>
              </center> 
            
             <br />
             <center><button class='oneClick okbutton1' name='oneClick'  type="submit"><?php echo $lang['global-savechanges']; ?></button></center>

             <?php	if ($source == 'not_approved') { ?>
            <input type="hidden" name="source" value="<?php echo $source; ?>" />
            <input type="hidden" name="user_id" value="<?php echo $user_id; ?>" />
            <?php	} ?>
   
</form>
<?php displayFooter(); ?>


<!-- When script submits, check to see if password+salt matches pw+salt in db. If yes, leave. If no, change. Hepp! 
Conversely: Leave Password out of the form, and replace with a link 'change password' -->
