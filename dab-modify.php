<?php
	
	session_start();
	
	require_once 'cOnfig/connection-tablet.php';
	require_once 'cOnfig/view.php';
	require_once 'cOnfig/authenticate.php';
	require_once 'cOnfig/languages/common.php';
	require_once 'googleConfig.php';
	
	getSettings();
	
	if (isset($_GET['user_id'])) {	
		$user_id = $_GET['user_id'];
	} else if (isset($_POST['user_id'])) {	
		$user_id = $_POST['user_id'];
	} else {
		echo "<br />No user specified!";
		exit();
	}
	
	if ($_POST['submit'] == 'true') {
		
		$hash = $_POST['hash'];
		
		$file1 = $_POST['file1'];
		if ($file1 == '') { $file1 = 0; }
		$file2 = $_POST['file2'];
		if ($file2 == '') { $file2 = 0; }
		$first_name = str_replace("'","\'",str_replace('%', '&#37;', trim($_POST['first_name'])));
		if ($first_name == '') { $first_name = 0; }
		$last_name = str_replace("'","\'",str_replace('%', '&#37;', trim($_POST['last_name'])));
		if ($last_name == '') { $last_name = 0; }
		$gender = $_POST['gender'];
		if ($gender == '') { $gender = 0; }
		$nationality = str_replace("'","\'",str_replace('%', '&#37;', trim($_POST['nationality'])));
		if ($nationality == '') { $nationality = 0; }
		$dni = str_replace("'","\'",str_replace('%', '&#37;', trim($_POST['dni'])));
		if ($dni == '') { $dni = 0; }
		$usageType = $_POST['usageType'];
		if ($usageType == '') { $usageType = 0; }
		$telephone = str_replace("'","\'",str_replace('%', '&#37;', trim($_POST['telephone'])));
		if ($telephone == '') { $telephone = 0; }
		$email = str_replace("'","\'",str_replace('%', '&#37;', trim($_POST['email'])));
		if ($email == '') { $email = 0; }
		$instagram = $_POST['instagram'];
		if ($instagram == '') { $instagram = 0; }
		$street = str_replace("'","\'",str_replace('%', '&#37;', trim($_POST['street'])));
		if ($street == '') { $street = 0; }
		$streetnumber = $_POST['streetnumber'];
		if ($streetnumber == '') { $streetnumber = 0; }
		$flat = $_POST['flat'];
		if ($flat == '') { $flat = 0; }
		$postcode = $_POST['postcode'];
		if ($postcode == '') { $postcode = 0; }
		$city = str_replace("'","\'",str_replace('%', '&#37;', trim($_POST['city'])));
		if ($city == '') { $city = 0; }
		$province = str_replace("'","\'",str_replace('%', '&#37;', trim($_POST['province'])));
		if ($province == '') { $province = 0; }
		$country = str_replace("'","\'",str_replace('%', '&#37;', trim($_POST['country'])));
		if ($country == '') { $country = 0; }
		$comment = str_replace("'","\'",str_replace('%', '&#37;', trim($_POST['comment'])));
		if ($comment == '') { $comment = 0; }
		$myComment = str_replace("'","\'",str_replace('%', '&#37;', trim($_POST['myComment'])));
		
		if ($myComment != '') {
			$myComment = ": <strong>$myComment</strong>";
		} else {
			$myComment = ".";
		}
		
		// Change status to SOCIO
		$query = "UPDATE users SET userGroup = 14 WHERE user_id = $user_id";
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
		
		// Add to resubmit table
		$query = "INSERT INTO `resubmit`(`user_id`, `file1`, `file2`, `first_name`, `last_name`, `gender`, `nationality`, `dni`, `usageType`, `telephone`, `email`, `instagram`, `street`, `streetnumber`, `flat`, `postcode`, `city`, `province`, `country`, `comment`, `resubmitted`) VALUES ('$user_id', '$file1', '$file2', '$first_name', '$last_name', '$gender', '$nationality', '$dni', '$usageType', '$telephone', '$email', '$instagram', '$street', '$streetnumber', '$flat', '$postcode', '$city', '$province', '$country', '$comment', 0)";
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
		
		// Look up user e-mail
		$query = "SELECT first_name, email FROM users WHERE user_id = '{$user_id}'";
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
			$first_name = $row['first_name'];
			$email = $row['email'];
			
		// Send mail to member
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
			$mail->addAddress("$email", "$first_name");
			$mail->Subject = "More info needed for your Dabulance application";
			$mail->isHTML(true);
			$mail->Body = "Not quite yet $first_name,<br /><br />You're almost there! We have reviewed your submission and noticed an issue$myComment<br /><br />You can <a href='https://ccsnube.com/join/dab-resubmit.php?uid=$user_id&hash=$hash'>click this link</a> to correct your previous application. You will notice there are highlighted fields which <strong>must</strong> be corrected.<br /><br />Once we have reviewed the updated information you will receive another email with the following steps. Correct Full name and Birthday that match your photo ID are a requirement!<br /><br />
			All the best,<br />The Dabulance crew.";
			$mail->send();
			
			$_SESSION['successMessage'] = "E-mail sent correctly!";
	
		}
		catch (Exception $e)
		{
		   echo $e->errorMessage();
			$_SESSION['errorMessage'] = "ERROR SENDING EMAIL!!!!!!!!";
		}
		
		
		header("Location: ../profile.php?user_id=$user_id");
		
		exit();
		
	}

	pageStart("Request more info", NULL, $validationScript, "pprofile", NULL, "Request more info", $_SESSION['successMessage'], $_SESSION['errorMessage']);
	
	try
	{
		$result = $pdo3->prepare("SELECT u.user_id, u.memberno, u.registeredSince, u.first_name, u.last_name, u.email, u.day, u.month, u.year, u.nationality, u.gender, u.dni, u.street, u.streetnumber, u.flat, u.postcode, u.city, u.country, u.telephone, u.mconsumption, u.usageType, u.signupsource, u.cardid, u.photoid, u.docid, u.doorAccess, u.friend, u.friend2, u.paidUntil, u.adminComment, ug.userGroup, ug.groupName, ug.groupDesc, u.form1, u.form2, datediff(curdate(), u.registeredSince) AS daysMember, u.paymentWarning, u.paymentWarningDate, u.credit, u.banComment, u.creditEligible, u.dniscan, u.discount, u.discountBar, u.photoext, u.dniext1, u.dniext2, u.workStation, u.bajaDate, u.starCat, u.interview, u.exento, u.fptemplate1, u.usergroup2, u.sigext, u.instagram, u.province, u.hash FROM users u, usergroups ug WHERE u.userGroup = ug.userGroup AND u.user_id = '{$user_id}'");
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
		$registeredSince = $row['registeredSince'];
		$membertime = date("M y", strtotime($registeredSince));
		$userGroup = $row['userGroup'];
		$groupName = $row['groupName'];
		$groupDesc = $row['groupDesc'];
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
		$photoid = $row['photoid'];
		$docid = $row['docid'];
		$doorAccess = $row['doorAccess'];
		$friend = $row['friend'];
		$friend2 = $row['friend2'];
		$paidUntil = $row['paidUntil'];
		$adminComment = $row['adminComment'];
		$daysMember = $row['daysMember'];
		$form1 = $row['form1'];
		$form2 = $row['form2'];
		$dniscan = $row['dniscan'];
		$paymentWarning = $row['paymentWarning'];
		$paymentWarningDate = $row['paymentWarningDate'];
		$paymentWarningDateReadable = date('d M', strtotime($paymentWarningDate));
		$userCredit = $row['credit'];
		$banComment = $row['banComment'];
		$creditEligible = $row['creditEligible'];
		$discount = $row['discount'];
		$discountBar = $row['discountBar'];
		$photoext = $row['photoext'];
		$dniext1 = $row['dniext1'];
		$dniext2 = $row['dniext2'];
		$workStation = $row['workStation'];
		$bajaDate = date('d-m-y', strtotime($row['bajaDate']));
		$starCat = $row['starCat'];	
		$interview = $row['interview'];
		$exento = $row['exento'];
		$fptemplate1 = $row['fptemplate1'];
		$usergroup2 = $row['usergroup2'];
		$sigext = $row['sigext'];
		$instagram = $row['instagram'];
		$province = $row['province'];
		$hash = $row['hash'];
		
		
		
		
		
	// check DNI
	$file1 = $google_root."images/_dabulance/ID/" . $user_id . "-front.$dniext1";
	
	// check signature
	$file2 = $google_root."images/_dabulance/sigs/" . $user_id . ".$sigext";
	
	// check all other data
	
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
<input type="hidden" name="user_id" value="<?php echo $user_id; ?>" />
<input type="hidden" name="hash" value="<?php echo $hash; ?>" />
<input type="hidden" name="submit" value="true" />
  <table>
   <tr>
    <td colspan="3"><center><strong style='font-size: 18px;'>Documents</strong></center><br />&nbsp;</td>
   </tr>
   <tr>
    <td style='vertical-align: top;'><br /><input type='checkbox' name='file1' style='width: 15px;' value='1' /></td>
    <td style='vertical-align: top;'><br />DNI</td>
    <td><img src="<?php echo $file1; ?>" height="50" /><br />&nbsp;</td>
   </tr>
   <tr>
    <td style='vertical-align: top;'><br /><input type='checkbox' name='file2' style='width: 15px;' value='1' /></td>
    <td style='vertical-align: top;'><br />Signature</td>
    <td><img src="<?php echo $file2; ?>" height="50" /><br />&nbsp;</td>
   </tr>
   <tr>
    <td colspan="3"><center><strong style='font-size: 18px;'>Personal details</strong></center><br />&nbsp;</td>
   </tr>
   <tr>
    <td><input type='checkbox' name='first_name' style='width: 15px;' value='1' /></td>
    <td>First name(s)</td>
    <td><input type="text" id="first_name" value="<?php echo $first_name; ?>" /><br />&nbsp;</td>
   </tr>
   <tr>
    <td><input type='checkbox' name='last_name' style='width: 15px;' value='1' /></td>
    <td>Last name(s)</td>
    <td><input type="text" id="last_name" value="<?php echo $last_name; ?>" /><br />&nbsp;</td>
   </tr>
   <tr>
    <td><input type='checkbox' name='gender' style='width: 15px;' value='1' /></td>
    <td>Gender</td>
    <td><select id="gender">
   <option value=""><?php echo $lang['member-gender']; ?>:</option>
   <option value="Male" <?php if ($gender == 'Male') {echo "selected";} ?>><?php echo $lang['member-male']; ?></option>
   <option value="Female" <?php if ($gender == 'Female') {echo "selected";} ?>><?php echo $lang['member-female']; ?></option>
  </select><br />&nbsp;</td>
   </tr>
   <tr>
    <td><input type='checkbox' name='nationality' style='width: 15px;' value='1' /></td>
    <td>Nationality</td>
    <td><input type="text" id="nationality" value="<?php echo $nationality; ?>" /><br />&nbsp;</td>
   </tr>
   <tr>
    <td><input type='checkbox' name='dni' style='width: 15px;' value='1' /></td>
    <td>ID card / Passport no.&nbsp;&nbsp;</td>
    <td><input type="text" id="dni" class="idGroup" id="dni" value="<?php echo $dni; ?>" /><br />&nbsp;</td>
   </tr>
   <tr>
    <td><input type='checkbox' name='usageType' style='width: 15px;' value='1' /></td>
    <td>Usage</td>
    <td><select id="usageType">
    <?php if ($usageType == NULL) { ?><option value=""><?php echo $lang['global-select']; ?>:</option> <?php } ?>
    <option value="0" <?php if ($usageType == '0') {echo "selected";} ?>><?php echo $lang['member-recreational']; ?></option>
    <option value="1" <?php if ($usageType == '1') {echo "selected";} ?>><?php echo $lang['member-medicinal']; ?></option>
    <option value="2" <?php if ($usageType == '2') {echo "selected";} ?>>Both</option>
   </select><br />&nbsp;</td>
   </tr>
   <tr>
    <td colspan="3"><center><strong style='font-size: 18px;'>Contact details</strong></center><br />&nbsp;</td>
   </tr>
   <tr>
    <td><input type='checkbox' name='telephone' style='width: 15px;' value='1' /></td>
    <td>Telephone</td>
    <td><input type="text" id="telephone" value="<?php echo $telephone; ?>" /><br />&nbsp;</td>
   </tr>
   <tr>
    <td><input type='checkbox' name='email' style='width: 15px;' value='1' /></td>
    <td>E-mail</td>
    <td><input type="email" id="email" value="<?php echo $email; ?>" value='1' /> <br />&nbsp;</td>
   </tr>
   <tr>
    <td><input type='checkbox' name='instagram' style='width: 15px;' value='1' /></td>
    <td>Instagram?</td>
    <td><input type="text" id="instagram" value="<?php echo $instagram; ?>" /> <br />&nbsp;</td>
   </tr>
   <tr>
    <td><input type='checkbox' name='street' style='width: 15px;' value='1' /></td>
    <td>Street</td>
    <td><input type="text" id="street" placeholder="Street name" value="<?php echo $street; ?>" /><br />&nbsp;</td>
   </tr>
   <tr>
    <td><input type='checkbox' name='streetnumber' style='width: 15px;' value='1' /></td>
    <td>Number</td>
    <td><input type="text" lang="nb" id="streetnumber" class="fourDigit" placeholder="Number" value="<?php echo $streetnumber; ?>" />
   </tr>
   <tr>
    <td><input type='checkbox' name='flat' style='width: 15px;' value='1' /></td>
    <td>Flat</td>
    <td><input type="text" id="flat" placeholder="Apartment" class="fourDigit" value="<?php echo $flat; ?>"  /></td>
   </tr>
   <tr>
    <td><input type='checkbox' name='postcode' style='width: 15px;' value='1' /></td>
    <td>Post code</td>
    <td><input type="text" id="postcode" class="fourDigit" value="<?php echo $postcode; ?>" /><br />&nbsp;</td>
   </tr>
   <tr>
    <td><input type='checkbox' name='city' style='width: 15px;' value='1' /></td>
    <td>City</td>
    <td><input type="text" id="city" class="sixDigit" value="<?php echo $city; ?>" /><br />&nbsp;</td>
   </tr>
   <tr>
    <td><input type='checkbox' name='province' style='width: 15px;' value='1' /></td>
    <td>State</td>
    <td><input type="text" id="province" class="sixDigit" value="<?php echo $province; ?>" /><br />&nbsp;</td>
   </tr>
   <tr>
    <td><input type='checkbox' name='country' style='width: 15px;' value='1' /></td>
    <td>Country</td>
    <td><input type="text" id="country" value="<?php echo $country; ?>" />  <br />&nbsp;</td>
   </tr>
   <tr>
    <td style='vertical-align: top;'><br /><input type='checkbox' name='comment' style='width: 15px;' value='1' /></td>
    <td style='vertical-align: top;'><br />Comment?</td>
    <td><textarea id="comment" name="memberComment" /><?php echo $adminComment; ?></textarea><br />&nbsp;</td>
   </tr>
   <tr>
    <td style='vertical-align: top; text-align: left;' colspan='2'><br />Send comment to member?&nbsp;&nbsp;&nbsp;</td>
    <td><textarea id="myComment" name="myComment" /></textarea><br />&nbsp;</td>
   </tr>

  </table>
  

 <div class="clearfloat"></div><br />
 <button class='oneClick' name='oneClick' type="submit">Submit</button>
</form>
</center>
