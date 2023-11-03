<?php

	require_once 'cOnfig/connection.php';
	require_once 'cOnfig/view.php';
	require_once 'cOnfig/authenticate.php';
	require_once 'cOnfig/languages/common.php';
	
	$accessLevel = '3';
	// Authenticate & authorize
	authorizeUser($accessLevel);
    require "../PHPMailerAutoload.php";

	if (isset($_POST['club_id'])) {
		$club_id = $_POST['club_id'];
	} else if (isset($_GET['club_id'])) {
		$club_id = $_GET['club_id'];
	} else {
		handleError($lang['error-nouserid'],"");
	}

	function simpleRandomPassword($char) {
	    $alphabet = '1234567890';
	    $pass = array(); //remember to declare $pass as an array
	    $alphaLength = strlen($alphabet) - 1; //put the length -1 in cache
	    for ($i = 0; $i < $char; $i++) {
	        $n = rand(0, $alphaLength);
	        $pass[] = $alphabet[$n];
	    }
	    return implode($pass); //turn the array into a string
	}

  function slugify($text)
  {
	    // replace non letter or digits by -
	    $text = preg_replace('~[^\pL\d]+~u', '', $text);

	    // transliterate
	    $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);

	    // remove unwanted characters
	    $text = preg_replace('~[^-\w]+~', '', $text);

	    // trim
	    $text = trim($text, '-');

	    // remove duplicate -
	    $text = preg_replace('~-+~', '', $text);

	    // lowercase
	    $text = strtolower($text);

	    return $text;
  }
function sendEmail($mail, $to, $body, $subject) {
    //Enable SMTP debugging
    // 0 = off (for production use)
    // 1 = client messages
    // 2 = client and server messages
    $mail->SMTPDebug = 0;

    //Ask for HTML-friendly debug output
    $mail->Debugoutput = 'html';

    //Set the hostname of the mail server
    $mail->Host = 'mail.cannabisclub.systems';

    //Set the SMTP port number - 587 for authenticated TLS, a.k.a. RFC4409 SMTP submission
    $mail->Port = 465;

    //Set the encryption system to use - ssl (deprecated) or tls
    $mail->SMTPSecure = 'ssl';

    //Whether to use SMTP authentication
    $mail->SMTPAuth = true;

    //Username to use for SMTP authentication - use full email address for gmail
    $mail->Username = 'info@cannabisclub.systems';

    //Password to use for SMTP authentication
    $mail->Password = 'Insjormafon9191';

    //Set who the message is to be sent from
    $mail->setFrom('info@cannabisclub.systems', 'CCSNube');

    //Set who the message is to be sent to
    $mail->addAddress($to, $to);

    //Set the subject line
    $mail->Subject = $subject;

    //Read an HTML message body from an external file, convert referenced images to embedded,
    //convert HTML into a basic plain-text alternative body
    $mail->isHTML(true);
    $mail->Body = $body;
    //Replace the plain text body with one created manually
    $mail->AltBody = 'This is a plain-text message body';

    $sucess = $mail->send();
    // //send the message, check for errors
    // if (!$sucess) {
    // echo "Mailer Error: " . $mail -> ErrorInfo;
    // } else {
    // echo "Message sent!";
    // }
}
	// Approvve or rehject the club
	if(isset($_GET['approve']) || $_GET['approve'] != ''){
		$approveStatus = $_GET['approve'];
		$updateStatus = "UPDATE customers SET club_status = '$approveStatus' WHERE id = '$club_id'";
			try
			{
				$result = $pdo3->prepare("$updateStatus");
				$result->execute();
			}
			catch (PDOException $e)
			{
					$error = 'Error fetching user: ' . $e->getMessage();
					echo $error;
					exit();
			}
			if($approveStatus == 1){
				// launch the club
				include "club-launch.php";
			}else{
							// get club
						$getClub = "SELECT longName,email from customers WHERE id = '$club_id'";
							try
							{
								$result = $pdo3->prepare("$getClub");
								$result->execute();
							}
							catch (PDOException $e)
							{
									$error = 'Error fetching user: ' . $e->getMessage();
									echo $error;
									exit();
							}
							$clubRow = $result->fetch();
								$clubname = $clubRow['longName'];
							    $clubEmail = $clubRow['email'];
							    $maiAdmin = "info@cannabisclub.systems";
							    $email = $clubEmail;
							    $subject = "CCS Club Status";
								$adminmail = new PHPMailer();
								$adminmail->isSMTP();
								$usermail = new PHPMailer();
								$usermail->isSMTP();
							$body = "Hello <b>Admin</b><br>
										<p>The club <b>$clubname</b> has been rejected !</p>";
							sendEmail($adminmail, $maiAdmin, $body, $subject);
							$userMessage = "Hello <b>$clubname</b><br>
												<p>Sorry ! your club request has been rejected. Our representative will contact you soon.</p><br>Thanks & Regards,<br><b>CCS</b>";
							sendEmail($usermail, $email, $userMessage, 'CCS Club Status');
							echo "<script type='text/javascript'>window.location.href = 'club.php?club_id=".$club_id."';</script>";
			}
	}

	// Query to look up customer
	$clubDetails = "SELECT * FROM customers  WHERE  id = $club_id";
	
		try
		{
			$result = $pdo3->prepare("$clubDetails");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
		$id = $row['id'];
		$registeredSince = $row['registeredSince'];
		$Brand = $row['Brand'];
		$number = $row['number'];
		$_SESSION['customer'] = $number;
		$longName = $row['longName'];
		$shortName = $row['shortName'];
		$cif = $row['cif'];
		$street = $row['street'];
		$streetnumber = $row['streetnumber'];
		$flat = $row['flat'];
		$postcode = $row['postcode'];
		$city = $row['city'];
		$state = $row['state'];
		$country = $row['country'];
		$website = $row['website'];
		$email = $row['email'];
		$facebook = $row['facebook'];
		$twitter = $row['twitter'];
		$instagram = $row['instagram'];
		$googleplus = $row['googleplus'];
		$status = $row['status'];
		$type = $row['type'];
		$lawyer = $row['lawyer'];
		$URL = $row['URL'];
		$source = $row['source'];
		$billingType = $row['billingType'];
		$dbname = $row['dbname'];
		$dbuser = $row['dbuser'];
		$dbpwd = $row['dbpwd'];
		$signedContract = $row['signedContract'];
		$telephone = $row['phone'];
		$membermodule = $row['membermodule'];
		$contact = $row['contact'];
		$language = $row['language'];
		$member_contract = $row['member_contract'];
		$logo_path = $row['logo_path'];
		$club_status = $row['club_status'];
		$other_lang = $row['other_lang'];


	$deleteNoteScript = <<<EOD
function delete_note(noteid, userid) {
	if (confirm("{$lang['confirm-deletenote']}")) {
				window.location = "uTil/delete-note.php?noteid=" + noteid + "&userid=" + userid;
				}
}
EOD;
	pageStart("Club profile", NULL, $deleteNoteScript, "pprofilenew", NULL, "Club profile", $_SESSION['successMessage'], $_SESSION['errorMessage']);

?>
<style type="text/css">
			#load
		{
		   
		    display: none;
		    position : fixed;
		    z-index: 100;
		    background-image : url('../images/loading-small.gif');
		    background-color:#666;
		    opacity : 0.4;
		    background-repeat : no-repeat;
		    background-position : center;
		    left : 0;
		    bottom : 0;
		    right : 0;
		    top : 0;
		}
</style>
	 <div id="load">
	 </div>

<div class="overview">
 <span class="profilepicholder">
 	<?php  if(!empty($logo_path)){ ?>
 		<img class="profilepic" src="<?php echo $logo_path; ?>" />
	 <?php } ?>
 </span>
 <?php
 	if($member_contract != '' && $club_status == 1){
 		
 		$search_arr = array("images/_","/logo.png");
 		$replace_arr = array("","");
 	    $clubfolder =str_replace($search_arr, $replace_arr, $logo_path);
 		$contract = "../_club/_".$clubfolder."/contract.php";
 	}else if($member_contract != '' && ($club_status == 2 || $club_status == 0)){
 		$contract = "../contract.php";
 	}else{
 		$contract = "javascript:void(0)";
 	}
 ?>
 <span class="profilefirst"><?php echo $shortName ?> <a href="<?php echo $contract; ?>" target="_blank" ><img src="images/contract.png" style='margin-bottom: -3px; margin-left: 5px;'/></a></span>
 <br />
 <span class="profilesecond">
<?php
	if ($language != '') {
		echo "($language)<br />";
	}
   if($other_lang != ''){
   	  echo "<strong>Other Languages:</strong> $other_lang <br />";
   }


	echo "$longName<br /><span class='yellow'>$statusNam</span>";

		

?>
		
 </span>
 <br />
<div id="memberNotifications"> <span class="profilethird"></span>

			<table style='margin-left: 0; color: white;'>
			 <tr>
				  <td><strong>Club Name</strong></td>
				  <td class='right'><?php echo $longName  ?></td>
				 </tr>
				 <tr>
				  <td colspan='2'>&nbsp;</td>
				 </tr>
				 <tr>
				  <td><strong>CIF No.</strong></td>
				  <td class='right'><?php echo  $cif ?></td>
				 </tr>
				 <tr>
				  <td colspan='2'>&nbsp;</td>
				 </tr>
				 <tr>
				  <td><strong>Official Address</strong></td>
				  <td class='right'><?php echo "$street&nbsp;&nbsp;&nbsp;&nbsp;$streetnumber&nbsp;&nbsp;&nbsp;&nbsp;$flat"; ?></td>
				 </tr>
				 <tr>
				  <td colspan='2'>&nbsp;</td>
				 </tr>
				 <tr>
				  <td><strong>Official Postcode</strong></td>
				  <td class='right'><?php echo $postcode ?></td>
				 </tr>
				 <tr>
				  <td colspan='2'>&nbsp;</td>
				 </tr>
				 <tr>
				  <td><strong>City</strong></td>
				  <td class='right'><?php echo $city  ?></td>
				 </tr>
				 <tr>
				  <td colspan='2'>&nbsp;</td>
				 </tr>
				 <tr>
				  <td><strong>State / Province</strong></td>
				  <td  class='right'><?php echo $state  ?></td>
				 </tr>	
				 <tr>
				  <td colspan='2'>&nbsp;</td>
				 </tr>		 
				 <tr>
				  <td><strong>Country</strong></td>
				  <td  class='right'><?php echo $country ?></td>
				 </tr>	
				 <tr>
				  <td colspan='2'>&nbsp;</td>
				 </tr>	
				 <tr>
				 	<td></td>
				 	<td><strong>Personal Details</strong></td>
				 	<td></td>
				 </tr>
				 <tr>
				  <td colspan='2'>&nbsp;</td>
				 </tr>
				 <tr>
				   <td><strong>Name:</strong></td>
				   <td class='right'><?php echo $row['person_name']; ?></td>
				 </tr> 				 
				 <tr>
				   <td><strong>Role:</strong></td>
				   <td class='right'><?php echo $row['role']; ?></td>
				 </tr> 				
				  <tr>
				   <td><strong>Personal email:</strong></td>
				   <td class='right'><?php echo $row['email']; ?></td>
				 </tr> 				 
				 <tr>
				   <td><strong>Personal Contact:</strong></td>
				   <td class='right'><?php echo $row['phone']; ?></td>
				 </tr>  
				 <tr>
				 	<td></td>
				 	<td><strong>Club Details</strong></td>
				 	<td></td>
				 </tr>
				 <tr>
				  <td colspan='2'>&nbsp;</td>
				 </tr>
				 <tr>
				 	<td><strong>Club Email:</strong></td>
				 	<td class="right"><?php echo $row['club_email'] ?></td>
				 </tr>
				 <tr>
				 	<td><strong>Club Contact:</strong></td>
				 	<td class="right"><?php echo $row['club_phone'] ?></td>
				 </tr>
				  <tr>
				  <td colspan='2'>&nbsp;</td>
				 </tr>	
				 <tr>
				 	<td></td>
				 	<td><strong>Club Location Details</strong></td>
				 	<td></td>
				 </tr>
				 <tr>
				  <td colspan='2'>&nbsp;</td>
				 </tr>
				 <tr>
				  <td><strong>Club Address</strong></td>
				  <td class='right'><?php echo $row['location_street_name']; ?>&nbsp;&nbsp;&nbsp;&nbsp;<?php echo $row['location_street_number'] ?>&nbsp;&nbsp;&nbsp;&nbsp;<?php echo $row['location_local'] ?></td>
				 </tr>

				 <tr>
				  <td><strong>Club Location Postcode</strong></td>
				  <td class='right'><?php echo $row['location_postcode'] ?></td>
				 </tr>

				 <tr>
				  <td><strong>Club Location City</strong></td>
				  <td class='right'><?php echo $row['location_city']  ?></td>
				 </tr>

				 <tr>
				  <td><strong>Club Location State / Province</strong></td>
				  <td  class='right'><?php echo $row['location_province'];  ?></td>
				 </tr>	
		 
				 <tr>
				  <td><strong>Club Location Country</strong></td>
				  <td  class='right'><?php echo $row['location_country'] ?></td>
				 </tr>	
				 <tr>
				  <td colspan='2'>&nbsp;</td>
				 </tr>	 
				 <tr>
				  <td><strong>Website</strong></td>
				  <td  class='right'><?php echo $website ?></td>
				 </tr>	
				  <tr>
				  <td colspan='2'>&nbsp;</td>
				 </tr>		 
				 <tr>
				  <td><strong>Email</strong></td>
				  <td  class='right'><?php echo $email ?></td>
				 </tr>	
				 <tr>
				  <td colspan='2'>&nbsp;</td>
				 </tr>		 
				 <tr>
				  <td><strong>Facebook</strong></td>
				  <td  class='right'><?php echo $facebook ?></td>
				 </tr>	
				 <tr>
				  <td colspan='2'>&nbsp;</td>
				 </tr>		 
				 <tr>
				  <td><strong>Instagram</strong></td>
				  <td  class='right'><?php echo $instagram ?></td>
				 </tr>
				  <tr>
				  <td colspan='2'>&nbsp;</td>
				 </tr>			 
				 
			</table>
		
	<?php echo "</span></div><span class='profilefourth'>";
	?>

 </div> <!-- END OVERVIEW -->
 
  <div class="clearfloat"></div><br />
  <center>
<?php  if($club_status == 0){ ?>
 <span class="secondbuttons">
  <a href="club.php?club_id=<?php echo $club_id ?>&approve=1" class="cta"  id="applink"> Approve</a>
  <a href="club.php?club_id=<?php echo $club_id ?>&approve=2" class="cta" onClick="return confirm('Are you sure, you want to reject this club request ?');"> Reject</a>
 </span>
<?php }else if($club_status == 1){ ?>
 <span class="firstbuttons">
  <a href="javascript:void(0);" class="cta"> Approved</a>
 </span>
<?php }else{ ?>
	<span class="firstbuttons">
		<a href="javascript:void(0)" class="cta" style="background-color: red;"> Rejected</a>
	</span>
<?php } ?>
</center>

<?php displayFooter(); ?>
<script type="text/javascript">
	$("#applink").click(function(){
		if(confirm('Are you sure, you want to approve this club request ?')){
			 $("#load").show();
			  window.location.href = "club.php?club_id=<?php echo $club_id ?>"; 
			    setTimeout(function () {
			        $("#load").hide();
			    }, 140000);     
		}else{
			return false;
		}
	});
</script>