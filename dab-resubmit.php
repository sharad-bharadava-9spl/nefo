<?php
	
	session_start();
	
	require_once 'cOnfig/connection-tablet.php';
	require_once 'cOnfig/view-nohead.php';
	require_once 'cOnfig/authenticate.php';
	require_once 'cOnfig/languages/common.php';
	require_once 'googleConfig.php';
	
	getSettings();
	
	if (isset($_GET['uid'])) {
		
		$user_id = $_GET['uid'];
		
	} else if (isset($_POST['uid'])) {
		
		$user_id = $_POST['uid'];
		
	} else {
		
		pageStart($lang['title-newuser'], NULL, $validationScript, "pprofile", NULL, "", $_SESSION['successMessage'], $_SESSION['errorMessage']);
		echo "<center>An error has occured. Please try again or <a href='mailto:acw@dabulance.com'>contact us</a> and we'll help you!</center>";
		exit();
		
	}
	

	if (isset($_GET['hash'])) {
		
		$hash = $_GET['hash'];
		
		$query = "SELECT hash FROM users WHERE user_id = $user_id";
		try
		{
			$result = $pdo3->prepare("$query");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user2: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
			$hashreal = $row['hash'];	
	
			
		if ($hashreal != $hash) {
			
			pageStart($lang['title-newuser'], NULL, $validationScript, "pprofile", NULL, "", $_SESSION['successMessage'], $_SESSION['errorMessage']);
			echo "<center>An error has occured1. Please try again or <a href='mailto:acw@dabulance.com'>contact us</a> and we'll help you!</center>";
			exit();
			
		}
		
		$_SESSION['hash'] = 'verified';
		
	} else if ($_SESSION['hash'] != 'verified') {
		
			pageStart($lang['title-newuser'], NULL, $validationScript, "pprofile", NULL, "", $_SESSION['successMessage'], $_SESSION['errorMessage']);
			echo "<center>An error has occured2. Please try again or <a href='mailto:acw@dabulance.com'>contact us</a> and we'll help you!</center>";
			exit();
			
	}


	
	
	if (isset($_GET['upload'])) {
		
		$image_fieldname = "fileToUpload";
		$memberno = $user_id;
	
		// Potential PHP upload errors
		$php_errors = array(1 => $lang['imgError1'],
							2 => $lang['imgError2'],
							3 => $lang['imgError3'],
							4 => $lang['imgError4']);
						
		// Check for any upload errors
		if ($_FILES[$image_fieldname]['error'] != 0) {
			$_SESSION['errorMessage'] = $php_errors[$_FILES[$image_fieldname]['error']] . " " . $lang['try-again'];
			header("Location: ?");
			exit();
		}
		
		// Check if a real file was uploaded
		if (is_uploaded_file($_FILES[$image_fieldname]['tmp_name'])) {
			
		} else {
			$_SESSION['errorMessage'] = $lang['imgError4'];
			header("Location: ?");
			exit();
		}
		
		// Is this actually an image?
		if (getimagesize($_FILES[$image_fieldname]['tmp_name'])) {
			
		} else {
			$_SESSION['errorMessage'] = $lang['imgError5'];
			header("Location: ?");
			exit();
		}
		
		
		// Save the file and store the extension for later db entry
		$extension = pathinfo($_FILES[$image_fieldname]['name'], PATHINFO_EXTENSION);
		$upload_filename = "/var/www/html/ccsnubev2_com/images/_dabulance/ID/" . $memberno . "-front." . $extension;
		$_SESSION['dnifrontextension'] = $extension;
		
		if (move_uploaded_file($_FILES[$image_fieldname]['tmp_name'], $upload_filename)) {
			
		} else {
			$_SESSION['errorMessage'] = $lang['imgError6'];
			header("Location: ?");
			exit();
		}
		
		$query = "UPDATE users SET dniext1 = '$extension' WHERE user_id = $memberno";
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
		
		$query = "UPDATE resubmit SET resubmitted = 1 WHERE user_id = $user_id";
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
		
		pageStart("Passport / ID card scan", NULL, NULL, "pprofile", NULL, "Passport / ID card scan", $_SESSION['successMessage'], $_SESSION['errorMessage']);

			echo "<center>Thank you for re-submitting your application!<br /><br />We'll review it and contact you by mail when done.<br /><br />All the best,<br />The Dabulance team.</center>";
			
		exit();
			
	}
	
	if (isset($_GET['idupload'])) {
		
		
			pageStart("Passport / ID card scan", NULL, NULL, "pprofile", NULL, "Passport / ID card scan", $_SESSION['successMessage'], $_SESSION['errorMessage']);


?>
<br />
<center><h1>Upload a photo of your ID card</h1></center>
<br />
<br />

<form action="?upload&uid=<?php echo $user_id; ?>" method="post" enctype="multipart/form-data">
 <input type="hidden" name="MAX_FILE_SIZE" value="20000000" />
 <input type="hidden" name="upload" value="true" />
 <table>
  <tr>
   <td><strong>Step 1:</strong></td>
   <td style="padding-left: 5px;"><input type="file" name="fileToUpload" id="fileToUpload"></td>
  </tr>
  <tr>
   <td style="padding-top: 10px;"><strong>Step 2:</strong></td>
   <td style="padding-top: 10px; padding-left: 5px;"><input type="submit" value="<?php echo $lang['submit']; ?>" name="submit"></td>
  </tr>
  <tr>
   <td style="padding-top: 10px;"><strong></strong></td>
   <td style="padding-top: 10px; padding-left: 8px;"><br /><a style='color: red !important;' href='?skip'>Submit later</a></td>
  </tr>
</form>

<?php

	exit();
	}
	
	if (isset($_POST['saveuser'])) {
		
		$first_name = $_POST['first_name'];
		$last_name = $_POST['last_name'];
		$gender = $_POST['gender'];
		$nationality = $_POST['nationality'];
		$dni = $_POST['dni'];
		$usageType = $_POST['usageType'];
		$telephone = $_POST['telephone'];
		$email = $_POST['email'];
		$instagram = $_POST['instagram'];
		$street = $_POST['street'];
		$streetnumber = $_POST['streetnumber'];
		$flat = $_POST['flat'];
		$postcode = $_POST['postcode'];
		$city = $_POST['city'];
		$province = $_POST['province'];
		$country = $_POST['country'];
		$comment = $_POST['comment'];	
		$hashreal = $_POST['hash'];
		$day = $_SESSION['day'];
		$month = $_SESSION['month'];
		$year = $_SESSION['year'];
		
		$query = "UPDATE users SET first_name = '$first_name', last_name = '$last_name', gender = '$gender', nationality = '$nationality', dni = '$dni', day = '$day', month = '$month', year = '$year', nationality = '$nationality', dni = '$dni', usageType = '$usageType', telephone = '$telephone', email = '$email', instagram = '$instagram', street = '$street', streetnumber = '$streetnumber', flat = '$flat', postcode = '$postcode', city = '$city', province = '$province', country = '$country', adminComment = '$comment' WHERE user_id = $user_id";
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
		
		$query = "SELECT file1 FROM resubmit WHERE user_id = $user_id";
		try
		{
			$result = $pdo3->prepare("$query");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user1: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
			$file1trigger = $row['file1'];
		
		if ($file1trigger == 0) {
			
			pageStart($lang['title-newuser'], NULL, $validationScript, "pprofile", NULL, "", $_SESSION['successMessage'], $_SESSION['errorMessage']);
			
			echo "<center>Thank you for re-submitting your application!<br /><br />We'll review it and contact you by mail when done.<br /><br />All the best,<br />The Dabulance team.</center>";
				
		} else {
			
			$_SESSION['successMessage'] = "Thank you for submitting your updated information!<br />Now please upload an updated ID card / Passport.";
			header("Location: ?idupload&uid=$user_id");
			
		}
		
		exit();
		
	}
	
	if (isset($_GET['sigdone'])) {
		
		// Check if other info is required
	// Check hash, user id and if resubmit is active!
	$query = "SELECT * FROM resubmit WHERE user_id = $user_id ORDER by id DESC LIMIT 1";

	try
	{
		$result = $pdo3->prepare("$query");
		$result->execute();
	}
	catch (PDOException $e)
	{
			$error = 'Error fetching user1: ' . $e->getMessage();
			echo $error;
			exit();
	}

	$row = $result->fetch();
		$file1trigger = $row['file1'];
		$file2trigger = $row['file2'];
		$first_nametrigger = $row['first_name'];
		$last_nametrigger = $row['last_name'];
		$gendertrigger = $row['gender'];
		$nationalitytrigger = $row['nationality'];
		$dnitrigger = $row['dni'];
		$usageTypetrigger = $row['usageType'];
		$telephonetrigger = $row['telephone'];
		$emailtrigger = $row['email'];
		$instagramtrigger = $row['instagram'];
		$streettrigger = $row['street'];
		$streetnumbertrigger = $row['streetnumber'];
		$flattrigger = $row['flat'];
		$postcodetrigger = $row['postcode'];
		$citytrigger = $row['city'];
		$provincetrigger = $row['province'];
		$countrytrigger = $row['country'];
		$commenttrigger = $row['comment'];
		$resubmitted = $row['resubmitted'];
		$hash = $row['hash'];
		
		$submitstatus = $first_nametrigger + $last_nametrigger + $gendertrigger + $nationalitytrigger + $dnitrigger + $usageTypetrigger + $telephonetrigger + $emailtrigger + $instagramtrigger + $streettrigger + $streetnumbertrigger + $flattrigger + $postcodetrigger + $citytrigger + $provincetrigger + $countrytrigger + $commenttrigger;
		
		if ($submitstatus == 0) {
			
			if ($file1 == 0) {
				
				$query = "SELECT first_name, last_name, gender, nationality, dni, usageType, telephone, email, instagram, street, streetnumber, flat, postcode, city, province, country, adminComment, hash FROM users WHERE user_id = $user_id";
				try
				{
					$result = $pdo3->prepare("$query");
					$result->execute();
				}
				catch (PDOException $e)
				{
						$error = 'Error fetching user2: ' . $e->getMessage();
						echo $error;
						exit();
				}
			
				$row = $result->fetch();
					$first_name = $row['first_name'];
					$last_name = $row['last_name'];
					
				$insertTime = date('Y-m-d H:i:s');

			
				$query = "UPDATE resubmit SET resubmitted = 1, submittime = '$insertTime' WHERE user_id = $user_id";
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
				
				// No extra info needed
				try {
					
					// Send e-mail(s)
					require_once 'PHPMailerAutoload.php';
					
					$mail = new PHPMailer(true);
					$mail->SMTPDebug = 0;
					$mail->Debugoutput = 'html';
					$mail->isSMTP();
					$mail->Host = "mail.dabulance.com";
					$mail->SMTPAuth = true;
					$mail->Username = "acw@dabulance.com";
					$mail->Password = "2beorNOT2be2020!";
					$mail->SMTPSecure = 'ssl'; 
					$mail->Port = 465;
					$mail->setFrom('acw@dabulance.com', 'Dabulance');
					$mail->addAddress("acw@dabulance.com", "Dabulance");
					$mail->Subject = "Member application";
					$mail->isHTML(true);
					$mail->Body = "Dear admin,<br /><br />$first_name $last_name has just resubmitted their application!<br /><br />Please login to your system and approve / reject them.";
					$mail->send();
					
			
				}
				catch (Exception $e)
				{
				   echo $e->errorMessage();
				}
			
				pageStart($lang['title-newuser'], NULL, $validationScript, "pprofile", NULL, "", $_SESSION['successMessage'], $_SESSION['errorMessage']);
				echo "<center>Thank you for re-submitting your application!<br /><br />We'll review it and contact you by mail when done.<br /><br />All the best,<br />The Dabulance team.</center>";
				exit();
				
			} else {
				
				header("Location: ?id&uid=$user_id");
				exit();
				
			}
		} else {
			
			pageStart($lang['title-newuser'], NULL, $validationScript, "pprofile", NULL, "", $_SESSION['successMessage'], $_SESSION['errorMessage']);
			
	$query = "SELECT first_name, last_name, gender, nationality, dni, usageType, telephone, email, instagram, street, streetnumber, flat, postcode, city, province, country, adminComment, hash FROM users WHERE user_id = $user_id";
	try
	{
		$result = $pdo3->prepare("$query");
		$result->execute();
	}
	catch (PDOException $e)
	{
			$error = 'Error fetching user2: ' . $e->getMessage();
			echo $error;
			exit();
	}

	$row = $result->fetch();
		$first_name = $row['first_name'];
		$last_name = $row['last_name'];
		$gender = $row['gender'];
		$nationality = $row['nationality'];
		$dni = $row['dni'];
		$usageType = $row['usageType'];
		$telephone = $row['telephone'];
		$email = $row['email'];
		$instagram = $row['instagram'];
		$street = $row['street'];
		$streetnumber = $row['streetnumber'];
		$flat = $row['flat'];
		$postcode = $row['postcode'];
		$city = $row['city'];
		$province = $row['province'];
		$country = $row['country'];
		$comment = $row['adminComment'];	
		$hashreal = $row['hash'];	

?>

<center>
<form id="registerForm" action="" method="POST" onsubmit="return testInput()">
<input type="hidden" name="user_id" value="<?php echo $user_id; ?>" />
<input type="hidden" name="saveuser" value="yes" />
  <table>
   <tr>
    <td colspan="2"><center><strong style='font-size: 18px;'>Personal details</strong></center><br />&nbsp;</td>
   </tr>
   <tr>
    <td>First name(s)</td>
    <td><input type="text" name="first_name" value="<?php echo $first_name; ?>" <?php if ($first_nametrigger == 1) { echo "style='background-color: yellow;'"; } ?> /><br />&nbsp;</td>
   </tr>
   <tr>
    <td>Last name(s)</td>
    <td><input type="text" name="last_name" value="<?php echo $last_name; ?>" <?php if ($last_nametrigger == 1) { echo "style='background-color: yellow;'"; } ?> /><br />&nbsp;</td>
   </tr>
   <tr>
    <td>Gender</td>
    <td><select name="gender" <?php if ($gendertrigger == 1) { echo "style='background-color: yellow;'"; } ?>>
   <option value=""><?php echo $lang['member-gender']; ?>:</option>
   <option value="Male" <?php if ($gender == 'Male') {echo "selected";} ?>><?php echo $lang['member-male']; ?></option>
   <option value="Female" <?php if ($gender == 'Female') {echo "selected";} ?>><?php echo $lang['member-female']; ?></option>
  </select><br />&nbsp;</td>
   </tr>
   <tr>
    <td>Nationality</td>
    <td><input type="text" name="nationality" value="<?php echo $nationality; ?>" <?php if ($nationalitytrigger == 1) { echo "style='background-color: yellow;'"; } ?> /><br />&nbsp;</td>
   </tr>
   <tr>
    <td>ID card / Passport no.&nbsp;&nbsp;</td>
    <td><input type="text" id="dni" class="idGroup" name="dni" value="<?php echo $dni; ?>" <?php if ($dnitrigger == 1) { echo "style='background-color: yellow;'"; } ?> /><br />&nbsp;</td>
   </tr>
   <tr>
    <td>Usage</td>
    <td><select name="usageType" <?php if ($trigger == 1) { echo "style='background-color: yellow;'"; } ?>>
    <?php if ($usageType == NULL) { ?><option value=""><?php echo $lang['global-select']; ?>:</option> <?php } ?>
    <option value="0" <?php if ($memberType == '0') {echo "selected";} ?>><?php echo $lang['member-recreational']; ?></option>
    <option value="1" <?php if ($memberType == '1') {echo "selected";} ?>><?php echo $lang['member-medicinal']; ?></option>
    <option value="2" <?php if ($memberType == '2') {echo "selected";} ?>>Both</option>
   </select><br />&nbsp;</td>
   </tr>
   <tr>
    <td colspan="2"><center><strong style='font-size: 18px;'>Contact details</strong></center><br />&nbsp;</td>
   </tr>
   <tr>
    <td>Telephone</td>
    <td><input type="text" name="telephone" value="<?php echo $telephone; ?>" <?php if ($telephonetrigger == 1) { echo "style='background-color: yellow;'"; } ?> /><br />&nbsp;</td>
   </tr>
   <tr>
    <td>E-mail</td>
    <td><input type="email" name="email" value="<?php echo $email; ?>" <?php if ($emailtrigger == 1) { echo "style='background-color: yellow;'"; } ?> /> <br />&nbsp;</td>
   </tr>
   <tr>
    <td>Instagram?</td>
    <td><input type="text" name="instagram" value="<?php echo $instagram; ?>" <?php if ($instagramtrigger == 1) { echo "style='background-color: yellow;'"; } ?> /> <br />&nbsp;</td>
   </tr>
   <tr>
    <td>Address</td>
    <td><input type="text" name="street" value="<?php echo $street; ?>" <?php if ($streettrigger == 1) { echo "style='background-color: yellow;'"; } ?> placeholder="Street name" /> <input type="text" lang="nb" name="streetnumber" class="fourDigit" placeholder="Number" value="<?php echo $streetnumber; ?>" <?php if ($streetnumbertrigger == 1) { echo "style='background-color: yellow;'"; } ?> /> <input type="text" name="flat" placeholder="Apartment" class="fourDigit" value="<?php echo $flat; ?>" <?php if ($flattrigger == 1) { echo "style='background-color: yellow;'"; } ?> /><br />&nbsp;</td>
   </tr>
   <tr>
    <td>Post code & city</td>
    <td><input type="text" name="postcode" class="fourDigit" value="<?php echo $postcode; ?>" <?php if ($postcodetrigger == 1) { echo "style='background-color: yellow;'"; } ?> /> <input type="text" name="city" class="sixDigit" value="<?php echo $city; ?>" <?php if ($citytrigger == 1) { echo "style='background-color: yellow;'"; } ?> /><br />&nbsp;</td>
   </tr>
   <tr>
    <td>State</td>
    <td><input type="text" name="province" class="sixDigit" value="<?php echo $province; ?>" <?php if ($provincetrigger == 1) { echo "style='background-color: yellow;'"; } ?> /><br />&nbsp;</td>
   </tr>
   <tr>
    <td>Country</td>
    <td><input type="text" name="country" value="<?php echo $country; ?>" <?php if ($countrytrigger == 1) { echo "style='background-color: yellow;'"; } ?> />  <br />&nbsp;</td>
   </tr>
   <tr>
    <td style='vertical-align: top;'><br />Comment?</td>
    <td><textarea name="comment" <?php if ($commenttrigger == 1) { echo "style='background-color: yellow;'"; } ?> /><?php echo $comment; ?></textarea><br />&nbsp;</td>
   </tr>

  </table>
  

 <div class="clearfloat"></div><br />
 <button class='oneClick' name='oneClick' type="submit"><?php echo $lang['global-savechanges']; ?></button>
</form>
</center>

<?php

		}
		
		exit();
	}	
	// Did this page re-submit with a form? If so, check & store details
	if (isset($_POST['day'])) {
		
			$_SESSION['day'] = $_POST['day'];
			$_SESSION['month'] = $_POST['month'];
			$_SESSION['year'] = $_POST['year'];
			header("Location: dab-resubmit.php?sigdone&uid=$user_id");
			
	}
	
	if (isset($_GET['sig'])) {
		
		$_SESSION['user_id'] = $user_id;
		
	$validationScript = <<<EOD
    $(document).ready(function() {
	    
$('#dd_signaturePadWrapper').click(function(e) {  
        $('#savesig').attr('checked', false)
    });
	    	    
	  $('#registerForm').validate({
		  ignore: [],
		  rules: {
			  accept: {
				  required: true
			  },
			  accept2: {
				  required: true
			  },
			  accept3: {
				  required: true
			  },
			  memberType: {
				  required: true
			  },
			  day: {
				  required: true,
				  range:[1,31]
			  },
			  month: {
				  required: true,
				  range:[1,12]
			  },
			  year: {
				  required: true,
				  range:[1900,2000]
			  }

    	}, // end rules
    	messages: {
	    	memberType: 'Por favor, elige uno'
    	},
		  errorPlacement: function(error, element) {
			if (element.is(".memberType")){
				 error.appendTo("#errorBox4");
			} else if (element.is("#savesig")){
				 error.appendTo("#errorBox1");
			} else if (element.is("#accept2")){
				 error.appendTo("#errorBox2");
			} else if (element.is("#accept3")){
				 error.appendTo("#errorBox3");
			} else {
				return true;
			}
		},
		 
    	  submitHandler: function() {
   $(".oneClick").attr("disabled", true);
   form.submit();
	    	  }
	  }); // end validate
	  $.validator.messages.required = "You have to accept!";
	  
function demo_postSaveAction(f) {
	var objParent=document.getElementById('testDiv');
	var objDiv=document.createElement('div');
	with(objDiv) {
		setAttribute('id','demo_downloadWrapper');
		with(style) {
			position="relative";
			padding="10px";
			textAlign="left";
			margin="15px 70px 0px 70px";
			border="solid 1px #c0c0c0";
			backgroundColor="#efefef";
			borderRadius="4pt";
		}
		innerHTML="<h4>Demo signature saved to image. Click to download.</h4>";
		innerHTML+="<ul><li><a href=\"dd_signature_process.php?download="+f+"\" target=\"_blank\">"+f+"</a></li></ul>";
	}
	objParent.appendChild(objDiv);
}

  }); // end ready
EOD;
		
		pageStart("Update your information", NULL, $validationScript, "pprofile", NULL, "Update your information", $_SESSION['successMessage'], $_SESSION['errorMessage']);
		
?>
<br /><br />
<script type="text/javascript" src="js/dd_signature_pad4.js"></script>

	<form id="registerForm" method="post" action="">
	<input type='hidden' name='user_id' value="<?php echo $user_id; ?>" />

<h1>Terms & Conditions</h1>
<p>Booking is always upon Availability though unfortunately at times we hit other dilemmas and may not be able to
accommodate every request. Your officially considered on the books once your deposit is paid. This deposit is
refundable and held as a security towards last minute cancellations or property damage and must be paid in full at
time of booking. This deposit can and will be refunded in full if their is no issues at the end of your service (please
allow up to 3-5 business days for refunds), if for any reason on either end a cancellation has to be made the
following applies; 72 hours for members and 7 days for business a full refund will be issued. 48 hours prior for
members or 5 days for businesses if cancellation is a must a 60% deposit will be returned, 24 hours prior for
members or 3 days for businesses 40% deposit will be refunded. Any cancellation within 24hours for members or 2
days for businesses that are not due to Exigent circumstances will be charged the full deposit. (Exigent Are those
considered as such examples but not limited to hospitalization, weather, and other acts of Jah).</p>
<br />
<h1>Disclaimer</h1>
<p>We reserve the right to refuse service to anyone. We expect every one to feel safe and comfortable
and that includes but is not limited to you, our other patrons, and our teammates alike. Always be respectful and
follow the rules. This is a private members only service so everything that happens in the club is to be kept in the
club. Please make sure if you’re taking photos, you have permission of those in them prior to avoid any issues. Be
courteous and have fun. We will only revoke membership for serious incidents though for minor moments we will
lean on the side of forgiveness. Though being an American based business it’s a 3 strikes and your out kind of deal.
All guests must be over the age of 21 with an ID, please have them sign into our guest book and fill out a liability and
waiver form before to avoid any hold ups. Preregistration links will be available for your use when needed. Thank
you from the team at Dabulance and thanks to Cannabis Club Systems for revolutionary new platform for our
members to continue to stay safe and secure world wide.</p>

<br />
<center>
<h1>Date of birth</h1>
   <input type="number" lang="nb" name="day" class="twoDigit" maxlength="2" placeholder="dd" value="<?php echo $day; ?>" />
   <input type="number" lang="nb" name="month" class="twoDigit" maxlength="2" placeholder="mm" value="<?php echo $month; ?>" />
   <input type="number" lang="nb" name="year" class="fourDigit" maxlength="4" placeholder="<?php echo $lang['member-yyyy']; ?>" /><br /><br />
</center>
<center><strong><a href="#sig">Your signature:</a></strong><br /><br />
<a name="sig"></a><div id="signatureSet">
		<div id="dd_signaturePadWrapper"></div>
	</div><br />
<input type="checkbox" name="accept" id="savesig" style="width: 12px;" /><span id="errorBox1"></span>
	<label for='savesig'>&nbsp;&nbsp;I confirm that I have read and agree with the paper<br />'RELEASE AND WAIVER OF LIABILITY, ASSUMPTION OF RISK AND INDEMNITY AGREEMENT'<br />as well as the Terms&Conditions and Disclaimer shown above.</label>
   
</center>

<center><span id="errorBox"></span><br />
	 <button name='oneClick' class='oneClick' type="submit">Submit</button><br /><br /><br /></center>
	</form>

<?php
	}
		
	// Check hash, user id and if resubmit is active!
	$query = "SELECT * FROM resubmit WHERE user_id = $user_id ORDER BY id DESC LIMIT 1";
	try
	{
		$result = $pdo3->prepare("$query");
		$result->execute();
	}
	catch (PDOException $e)
	{
			$error = 'Error fetching user1: ' . $e->getMessage();
			echo $error;
			exit();
	}

	$row = $result->fetch();
		$file1trigger = $row['file1'];
		$file2trigger = $row['file2'];
		$first_nametrigger = $row['first_name'];
		$last_nametrigger = $row['last_name'];
		$gendertrigger = $row['gender'];
		$nationalitytrigger = $row['nationality'];
		$dnitrigger = $row['dni'];
		$usageTypetrigger = $row['usageType'];
		$telephonetrigger = $row['telephone'];
		$emailtrigger = $row['email'];
		$instagramtrigger = $row['instagram'];
		$streettrigger = $row['street'];
		$streetnumbertrigger = $row['streetnumber'];
		$flattrigger = $row['flat'];
		$postcodetrigger = $row['postcode'];
		$citytrigger = $row['city'];
		$provincetrigger = $row['province'];
		$countrytrigger = $row['country'];
		$commenttrigger = $row['comment'];
		$resubmitted = $row['resubmitted'];
		$hash = $row['hash'];
		
	if ($resubmitted == 1) {	
		
		pageStart($lang['title-newuser'], NULL, $validationScript, "pprofile", NULL, "", $_SESSION['successMessage'], $_SESSION['errorMessage']);
		echo "<center>Your application has already been resubmitted! If you believe this is wrong, <a href='mailto:acw@dabulance.com'>contact us</a> and we'll help you!</center>";
		exit();
		
	}

	$query = "SELECT first_name, last_name, gender, nationality, dni, usageType, telephone, email, instagram, street, streetnumber, flat, postcode, city, province, country, adminComment, hash FROM users WHERE user_id = $user_id";
	try
	{
		$result = $pdo3->prepare("$query");
		$result->execute();
	}
	catch (PDOException $e)
	{
			$error = 'Error fetching user2: ' . $e->getMessage();
			echo $error;
			exit();
	}

	$row = $result->fetch();
		$first_name = $row['first_name'];
		$last_name = $row['last_name'];
		$gender = $row['gender'];
		$nationality = $row['nationality'];
		$dni = $row['dni'];
		$usageType = $row['usageType'];
		$telephone = $row['telephone'];
		$email = $row['email'];
		$instagram = $row['instagram'];
		$street = $row['street'];
		$streetnumber = $row['streetnumber'];
		$flat = $row['flat'];
		$postcode = $row['postcode'];
		$city = $row['city'];
		$province = $row['province'];
		$country = $row['country'];
		$comment = $row['adminComment'];	
		$hashreal = $row['hash'];	
		
	if ($file2trigger == 1) {
		header("Location: ?sig&uid=$user_id");
		exit();
	}
	

	
	// Did this page re-submit with a form? If so, check & store details
	if (isset($_POST['first_name'])) {

		$userGroup = 11;
	$first_name = str_replace("'","\'",str_replace('%', '&#37;', trim($_POST['first_name'])));
	$last_name = str_replace("'","\'",str_replace('%', '&#37;', trim($_POST['last_name'])));
		$email = $_POST['email'];
		$day = $_SESSION['day'];
		$month = $_SESSION['month'];
		$year = $_SESSION['year'];
	$nationality = str_replace("'","\'",str_replace('%', '&#37;', trim($_POST['nationality'])));
		$gender = $_POST['gender'];
		$dni = $_POST['dni'];
	$street = str_replace("'","\'",str_replace('%', '&#37;', trim($_POST['street'])));
		$streetnumber = $_POST['streetnumber'];
		$flat = $_POST['flat'];
		$postcode = $_POST['postcode'];
	$city = str_replace("'","\'",str_replace('%', '&#37;', trim($_POST['city'])));
	$province = str_replace("'","\'",str_replace('%', '&#37;', trim($_POST['province'])));
	$country = str_replace("'","\'",str_replace('%', '&#37;', trim($_POST['country'])));
		$telephone = $_POST['telephone'];
		$mconsumption = $_SESSION['consumoPrevio'];
		$usageType = $_POST['usageType'];
	$instagram = str_replace("'","\'",str_replace('%', '&#37;', trim($_POST['instagram'])));
		$insertTime = date('Y-m-d H:i:s');
		$paymentTime = date('Y-m-d H:i:s');	
		$tempMemberNo = $_SESSION['tempNo'];
		
	$comment = str_replace("'","\'",str_replace('%', '&#37;', trim($_POST['comment'])));

		
		$memberInitials = strtoupper(substr($first_name, 0,1)) . strtoupper(substr($last_name, 0,1));
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
		
		
		$domainCheck = "SELECT domain FROM systemsettings";
		try
		{
			$result = $pdo3->prepare("$domainCheck");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
			$siteDomain = $row['domain'];
	
			$memberExp = $paymentTime;
			
		$hash = generateRandomString();
	
			// Query to add new user - 28 arguments
			  $query = sprintf("INSERT INTO users (registeredSince, memberno, userGroup, first_name, last_name, email, day, month, year, nationality, gender, dni, street, streetnumber, flat, postcode, city, country, telephone, mconsumption, usageType, signupsource, cardid, photoid, docid, doorAccess, friend, friend2, paidUntil, form1, form2, creditEligible, dniscan, dniext1, dniext2, photoext, province, domain, instagram, adminComment, hash) VALUES ('%s', '%s', '%d', '%s', '%s', '%s', '%d', '%d', '%d', '%s', '%s', '%s', '%s', '%d', '%s', '%s', '%s', '%s', '%s', '%d', '%s', '%s', '%s', '%d', '%d', '%d', '%d', '%d', '%s', '%d', '%d', '%d', '%d', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s');", 
$insertTime,
$memberno,
$userGroup,
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
$photoid,
$docid,
$doorAccess,
$aval,
$aval2,
$memberExp,
'1',
'1',
$creditEligible,
$dniscan,
$_SESSION['dnifrontextension'],
$_SESSION['dnibackextension'],
$_SESSION['userpicextension'],
$province,
$siteDomain,
$instagram,
$comment,
$hash
);
		  
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
			
	$user_id = $pdo3->lastInsertId();
	
	$_SESSION['newUserId'] = $user_id;
	
	$tempMemberNo = $_SESSION['tempNo'];
	
	$oldfile4 = $google_root_folder."images/_dabulance/sigs/" . $tempMemberNo . '.png';
	$newfile4 = $google_root_folder."images/_dabulance/sigs/" . $user_id . '.png';
	rename_object($google_bucket, $oldfile4, $google_bucket, $newfile4);
	
					
	
			header("Location: reg-5.php?noAval");
		exit();
	}
	/***** FORM SUBMIT END *****/

	$validationScript = <<<EOD
    $(document).ready(function() {
	    
	    // if 2 or 3 is selected, hide box.
		var initialVal = $('#paidUntil').val();
			if(initialVal < 1) {
	        	$("#paymentBox").hide();				
			}
	    	    
	    $('#paidUntil').change(function(){
			var val = $(this).val();
		    if(val > 0) {
		        $("#paymentBox").fadeIn('slow');
	    	} else {
		        $("#paymentBox").fadeOut('slow');
	    	}
	    });

	    	    
	  $('#registerForm').validate({
		  rules: {
			  memberno: {
        		 require_from_group: [1, '.memberGroup'],
				  digits: true
        	  },
			  memberNumber: {
        		 require_from_group: [1, '.memberGroup']
			  },
			  first_name: {
				  required: true,
				  minlength: 2
			  },
			  last_name: {
				  required: true,
				  minlength: 2
			  },	  
			  gender: {
				  required: true
			  },
			  email: {
				  email: true
			  },
			  userGroup: {
				  required: true,
				  range:[1,5],
			  },
			  paidUntil: {
				  required: true
			  },
			  nationality: {
				  required: true
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
				  range:[1900,2000],
			  },
			  dni: {
				  required: true,
			  },
			  usageType: {
				  required: true
			  },
			  mconsumption: {
				  required: true,
				  range:[1,100]
			  },
			  telephone: {
				  required: true
			  },
			  email: {
				  required: true
			  },
			  street: {
				  required: true
			  },
			  postcode: {
				  required: true
			  },
			  city: {
				  required: true
			  },
			  country: {
				  required: true
			  },
			  province: {
				  required: true
			  },
			  streetnumber: {
				  required: true
			  },
			  pinCode: {
				  required: true,
				  range: [6464,6464]
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


	pageStart($lang['title-newuser'], NULL, $validationScript, "pprofile", NULL, "", $_SESSION['successMessage'], $_SESSION['errorMessage']);
	
	$consumoPrevio = $_SESSION['consumoPrevio'];
	$memberType = $_SESSION['memberType'];
	$day = $_SESSION['day'];
	$month = $_SESSION['month'];
	$year = $_SESSION['year'];
	
	$tempMemberNo = $_SESSION['tempNo'];
	


			unset($_SESSION['newmember']);
			
?>
<center>
<form id="registerForm" action="" method="POST" onsubmit="return testInput()">
<input type="hidden" name="nextMemberNo" value="<?php echo $nextMemberNo; ?>" />
  <table>
   <tr>
    <td colspan="2"><center><strong style='font-size: 18px;'>Personal details</strong></center><br />&nbsp;</td>
   </tr>
   <tr>
    <td>First name(s)</td>
    <td><input type="text" name="first_name" /><br />&nbsp;</td>
   </tr>
   <tr>
    <td>Last name(s)</td>
    <td><input type="text" name="last_name" /><br />&nbsp;</td>
   </tr>
   <tr>
    <td>Gender</td>
    <td><select name="gender">
   <option value=""><?php echo $lang['member-gender']; ?>:</option>
   <option value="Male"><?php echo $lang['member-male']; ?></option>
   <option value="Female"><?php echo $lang['member-female']; ?></option>
  </select><br />&nbsp;</td>
   </tr>
   <tr>
    <td>Nationality</td>
    <td><input type="text" name="nationality" /><br />&nbsp;</td>
   </tr>
   <tr>
    <td>ID card / Passport no.&nbsp;&nbsp;</td>
    <td><input type="text" id="dni" class="idGroup" name="dni" /><br />&nbsp;</td>
   </tr>
   <tr>
    <td>Usage</td>
    <td><select name="usageType">
    <?php if ($usageType == NULL) { ?><option value=""><?php echo $lang['global-select']; ?>:</option> <?php } ?>
    <option value="0" <?php if ($memberType == '0') {echo "selected";} ?>><?php echo $lang['member-recreational']; ?></option>
    <option value="1" <?php if ($memberType == '1') {echo "selected";} ?>><?php echo $lang['member-medicinal']; ?></option>
    <option value="2" <?php if ($memberType == '2') {echo "selected";} ?>>Both</option>
   </select><br />&nbsp;</td>
   </tr>
   <tr>
    <td colspan="2"><center><strong style='font-size: 18px;'>Contact details</strong></center><br />&nbsp;</td>
   </tr>
   <tr>
    <td>Telephone</td>
    <td><input type="text" name="telephone" /><br />&nbsp;</td>
   </tr>
   <tr>
    <td>E-mail</td>
    <td><input type="email" name="email" /> <br />&nbsp;</td>
   </tr>
   <tr>
    <td>Instagram?</td>
    <td><input type="text" name="instagram" /> <br />&nbsp;</td>
   </tr>
   <tr>
    <td>Address</td>
    <td><input type="text" name="street" placeholder="Street name" /> <input type="text" lang="nb" name="streetnumber" class="fourDigit" placeholder="Number" /> <input type="text" name="flat" placeholder="Apartment" class="fourDigit"  /><br />&nbsp;</td>
   </tr>
   <tr>
    <td>Post code & city</td>
    <td><input type="text" name="postcode" class="fourDigit" /> <input type="text" name="city" class="sixDigit" /><br />&nbsp;</td>
   </tr>
   <tr>
    <td>State</td>
    <td><input type="text" name="province" class="sixDigit" /><br />&nbsp;</td>
   </tr>
   <tr>
    <td>Country</td>
    <td><input type="text" name="country" />  <br />&nbsp;</td>
   </tr>
   <tr>
    <td style='vertical-align: top;'><br />Comment?</td>
    <td><textarea name="comment" />  </textarea><br />&nbsp;</td>
   </tr>

  </table>
  

 <div class="clearfloat"></div><br />
 <button class='oneClick' name='oneClick' type="submit"><?php echo $lang['global-savechanges']; ?></button>
</form>
</center>
