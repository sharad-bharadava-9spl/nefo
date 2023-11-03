<?php
	
	require_once '../cOnfig/connection-master.php';
	require_once '../cOnfig/languages/common.php';
	require_once '../cOnfig/view-newclub.php';
	
	session_start();

	// Andy protection:
	exit();
	
	//----email start here-----------
	require '../PHPMailerAutoload.php';
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
		$mail->Username = "info@cannabisclub.systems";

		//Password to use for SMTP authentication
		$mail->Password = "Insjormafon9191";

		//Set who the message is to be sent from
		$mail->setFrom('info@cannabisclub.systems', 'CCSNube');

		//Set who the message is to be sent to
		$mail->addAddress($to, $to);
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
	//----email end here-----------


$validationScript = <<<EOD
    $(document).ready(function() {
      $("#step1").validate({
      		 rules: {
      		 	name: "required",
      		 	pattern: "^[a-zA-Z']{1,40}$",
      		 	phone: {
			      required: true,
			    },
			     email: {
			      required: true,
			      email:true
			      }
      		 }, 
			 messages:{
			 	 email:
                 {
                    remote: $.validator.format("{0} is already exist.")
                 }
			 },   
            errorPlacement: function(error, element) {
            	var new_error = error.before('<br>');
            	console.log(new_error);
                error.insertAfter(element);
            }
      	});

  }); // end ready

EOD;



	pageStart("CCS", NULL, $validationScript, "pprofile", "club-launch", "CCS", $_SESSION['successMessage'], $_SESSION['errorMessage']);


	// submit step 1
	if(isset($_POST) && !empty($_POST)){
	  
		$name = str_replace("'","\'",str_replace('%', '&#37;', trim($_POST['name'])));			
		$role = str_replace("'","\'",str_replace('%', '&#37;', trim($_POST['role'])));			
		$phone = str_replace("'","\'",str_replace('%', '&#37;', trim($_POST['phone'])));			
		$email = str_replace("'","\'",str_replace('%', '&#37;', trim($_POST['email'])));			
		$language = implode(",", $_POST['language']);		
		$other_lang = str_replace("'","\'",str_replace('%', '&#37;', trim($_POST['other_lang'])));	
		// Query to update user - 28 arguments
		if(isset($_POST['edit_preview']) && $_POST['edit_preview'] == 'preview'){
				 $update_id = $_SESSION['temp_id'];

					try
					{
						// $updateTempCustomer = "UPDATE temp_customers SET name='$name', role = '$role', person_phone = '$phone', person_email= '$email', language = '$language', other_lang = '$other_lang' WHERE id= '$update_id'"; 
						// $pdo2->prepare("$updateTempCustomer")->execute();

						$updateTempCustomer = $pdo2->prepare("UPDATE temp_customers SET name=?, role = ?, person_phone = ?, person_email= ?, language = ?, other_lang = ? WHERE id= ?");
							$updateTempCustomer->bindValue(1, $name);
							$updateTempCustomer->bindValue(2, $role);
							$updateTempCustomer->bindValue(3, $phone);
							$updateTempCustomer->bindValue(4, $email);
							$updateTempCustomer->bindValue(5, $language);
							$updateTempCustomer->bindValue(6, $other_lang);
							$updateTempCustomer->bindValue(7, $update_id);
							$updateTempCustomer->execute();
					}
					catch (PDOException $e)
					{
							$error = 'Error fetching user: ' . $e->getMessage();
							echo $error;
							exit();
					}
					$_SESSION['successMessage']  = "Step 1 updated successfully !";
					header("location:new-club-5.php");
					die;
		}else{

			$nts_key =  substr(md5(microtime()*rand(0,9999)),0,20);
			$nts_language = $_SESSION['lang'];

			// $insertTempCustomer = sprintf("INSERT INTO temp_customers (name, role, person_phone, person_email, language, other_lang,nts_key,nts_language) VALUES ('%s', '%s', '%s', '%s', '%s', '%s','%s','%s')", 
			// 				$name,
			// 				$role,
			// 				$phone,
			// 				$email,
			// 				$language,
			// 				$other_lang,
			// 				$nts_key,
			// 				$nts_language
			// 				);

						try
						{
							//-----------------------------------------------
							$insertTempCustomer = $pdo2->prepare("INSERT INTO temp_customers (name, role, person_phone, person_email, language, other_lang,nts_key,nts_language) VALUES (?,?,?,?,?,?,?,?)");
							$insertTempCustomer->bindValue(1, $name);
							$insertTempCustomer->bindValue(2, $role);
							$insertTempCustomer->bindValue(3, $phone);
							$insertTempCustomer->bindValue(4, $email);
							$insertTempCustomer->bindValue(5, $language);
							$insertTempCustomer->bindValue(6, $other_lang);
							$insertTempCustomer->bindValue(7, $nts_key);
							$insertTempCustomer->bindValue(8, $nts_language);
							$insertTempCustomer->execute();
							//-----------------------------------------------

							// $insert_result = $pdo2->prepare("$insertTempCustomer")->execute();
						}
						catch (PDOException $e)
						{
								$error = 'Error fetching user: ' . $e->getMessage();
								echo $error;
								exit();
						}
						$id = $pdo2->lastInsertId();
						$_SESSION['temp_id'] = $id;
						$_SESSION['club_step'] = "step2";


						//--------email for verify user-----------------
						$clubname = $name;
						$email = $email;
						$maiAdmin = "info@cannabisclub.systems";
						$subject = "CCS Email Verification";
						$adminmail = new PHPMailer();
						$adminmail->isSMTP();
						$usermail = new PHPMailer();
						$usermail->isSMTP();
						$body = "Hello <b>$clubname</b><br>
						<p>Please Verify Email Address</p>";
						sendEmail($adminmail, $maiAdmin, $body, $subject);
						$userMessage = "Hello <b>$clubname</b><br>
							<p>Welcome to CCS, Please verify your email addres to continue....
							<a href=".$siteroot."launch/verify-email.php?nts_key=".base64_encode($nts_key).">Click here to verify</a></p><br>Thanks & Regards,<br><b>CCS</b>";
						sendEmail($usermail, $email, $userMessage, 'CCS Club Request');
						
						header("location:new-club-2.php");
						die;
		}
	}
// edit preview 
	if(isset($_GET['edit']) && $_GET['edit'] == 'preview'){
		$update_id = $_SESSION['temp_id'];
		try
		{
			// $selectDetails = "SELECT * from temp_customers WHERE id=".$update_id; 
			// $offc_result = $pdo2->prepare("$selectDetails");
			// $offc_result->execute();			

			$offc_result = $pdo2->prepare("SELECT * FROM temp_customers WHERE id = :update_id"); 
			$offc_result->bindValue(':update_id', $update_id);
			$offc_result->execute(); 

          }
          catch (PDOException $e)
          {
              $error = 'Error fetching user: ' . $e->getMessage();
              echo $error;
              exit();
          }
           while($clubRow = $offc_result->fetch()){
				    $name = $clubRow['name'];
				    $role = $clubRow['role'];
				    $person_phone = $clubRow['person_phone'];
				    $person_email = $clubRow['person_email'];
				    $language = $clubRow['language'];
				    $other_lang = $clubRow['other_lang'];
				    $lang_arr =explode(",", $language);

           }
	}
	
	if (isset($_SESSION['name'])) {
		$name = $_SESSION['name'];
		$person_phone = $_SESSION['telephone'];
		$person_email = $_SESSION['email'];
	}

?>

<div id='progress'>
 <div id='progressinside1'>
 </div>
</div>
<br />
<div id='progresstext1'>
 1. Personal
</div>
<form id="step1" action="" method="POST">
	<input type="hidden" name="edit_preview" value="<?php echo $_GET['edit']; ?>">
 <div id='mainbox-new-club'>
  <div id='mainboxheader'>
   <center>
    <?php if ($_SESSION['lang'] == 'es') { echo "Detalles personales"; } else { echo "Please fill in your personal information"; } ?>
   </center>
  </div>
  <div class='boxcontent'>
   <center>
    <table>
     <tr>
      <td><?php if ($_SESSION['lang'] == 'es') { echo "Tu nombre"; } else { echo "Your name"; } ?> *</td>
      <td><input type="text" name="name" class='defaultinput' placeholder="" required="" value="<?php echo $name; ?>" /><br></td>
     </tr>
     <tr>
      <td><?php if ($_SESSION['lang'] == 'es') { echo "Tu posición en el club"; } else { echo "Your role in the club"; } ?></td>
      <td><input type="text" name="role" class='defaultinput' placeholder="<?php if ($_SESSION['lang'] == 'es') { echo 'Por ejemplo Presidente'; } else { echo 'E.g. President'; } ?>" value="<?php echo $role; ?>" /><br></td>
     </tr>
     <tr>
      <td><?php if ($_SESSION['lang'] == 'es') { echo "Tu teléfono"; } else { echo "Your telephone number"; } ?> *</td>
      <td><input type="text" name="phone" class='defaultinput' placeholder="" required="" value="<?php echo $person_phone; ?>" /><br></td>
     </tr>
     <tr>
      <td><?php if ($_SESSION['lang'] == 'es') { echo "Tu e-mail"; } else { echo "Your e-mail address"; } ?> *</td>
      <td><input type="email" name="email" id="person_email" class='defaultinput' placeholder="" value="<?php echo $person_email; ?>" required=""/><br></td>
     </tr>
     <tr>
      <td><?php if ($_SESSION['lang'] == 'es') { echo "Idiomas"; } else { echo "Languages spoken"; } ?></td>
      <td>
       <table style='border-spacing: 12px; border-collapse: separate;'>
        <tr>
         <td>
	      <div class='fakeboxholder firstbox'>	
		   <label class="control">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
		    <?php if ($_SESSION['lang'] == 'es') { echo "Inglés"; } else { echo "English"; } ?>
		    <input type="checkbox" name="language[]" value='English' <?php  if(in_array("English", $lang_arr)){  echo "checked"; } ?>/>
		    <div class="fakebox"></div>
		   </label>
	      </div>
	     </td>
         <td>
	      <div class='fakeboxholder firstbox'>	
		   <label class="control">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
		    <?php if ($_SESSION['lang'] == 'es') { echo "Español"; } else { echo "Spanish"; } ?>
		    <input type="checkbox" name="language[]" value='Spanish' <?php  if(in_array("Spanish", $lang_arr)){  echo "checked"; } ?> />
		    <div class="fakebox"></div>
		   </label>
	      </div>
	     </td>
	    </tr>
        <tr>
         <td>
	      <div class='fakeboxholder firstbox'>	
		   <label class="control">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
		    <?php if ($_SESSION['lang'] == 'es') { echo "Francés"; } else { echo "French"; } ?>
		    <input type="checkbox" name="language[]" value='French' <?php  if(in_array("French", $lang_arr)){  echo "checked"; } ?>/>
		    <div class="fakebox"></div>
		   </label>
	      </div>
	     </td>
         <td>
	      <div class='fakeboxholder firstbox'>	
		   <label class="control">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
		    <?php if ($_SESSION['lang'] == 'es') { echo "Italiano"; } else { echo "Italian"; } ?>
		    <input type="checkbox" name="language[]" value='Italian' <?php  if(in_array("Italian", $lang_arr)){  echo "checked"; } ?>/>
		    <div class="fakebox"></div>
		   </label>
	      </div>
	     </td>
	    </tr>
        <tr>
         <td>
	      <div class='fakeboxholder firstbox'>	
		   <label class="control">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
		    <?php if ($_SESSION['lang'] == 'es') { echo "Catalá"; } else { echo "Catalan"; } ?>
		    <input type="checkbox" name="language[]" value='Catalan' <?php  if(in_array("Catalan", $lang_arr)){  echo "checked"; } ?>/>
		    <div class="fakebox"></div>
		   </label>
	      </div>
	     </td>
         <td>
	      <div class='fakeboxholder firstbox'>	
		   <label class="control">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
		    <?php if ($_SESSION['lang'] == 'es') { echo "Holandés"; } else { echo "Dutch"; } ?>
		    <input type="checkbox" name="language[]" value='Dutch' <?php  if(in_array("Dutch", $lang_arr)){  echo "checked"; } ?>/>
		    <div class="fakebox"></div>
		   </label>
	      </div>
	     </td>
	    </tr>
        <tr>
         <td>
	      <div class='fakeboxholder firstbox'>	
		   <label class="control">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
		    <?php if ($_SESSION['lang'] == 'es') { echo "Aleman"; } else { echo "German"; } ?>
		    <input type="checkbox" name="language[]" value='German' <?php  if(in_array("German", $lang_arr)){  echo "checked"; } ?>/>
		    <div class="fakebox"></div>
		   </label>
	      </div>
	     </td>
         <td>
	      <div class='fakeboxholder firstbox'>	
		   <label class="control">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
		    <?php if ($_SESSION['lang'] == 'es') { echo 'Otra'; } else { echo 'Other'; } ?>:
		    <input type="checkbox" name="language[]" value='Other' <?php  if(in_array("Other", $lang_arr)){  echo "checked"; } ?>/>
		    <div class="fakebox"></div>
		   </label>
	      </div>
	     </td>
	     <?php  if(in_array("Other", $lang_arr)){  ?>
	     	<td><input type="text" name="other_lang" id="other_show" class='defaultinput' placeholder="" value="<?php echo $other_lang; ?>" required=""/><br></td>
	 	<?php }else{ ?>
	 		<td><input type="text" name="other_lang" id="other_show" class='defaultinput' placeholder="" style='display: none;' required=""/><br></td>
	 	<?php } ?>
	    </tr>
	   </table>	     
      </td>
     </tr>
    </table>
   </center>
  </div>
 </div>
</div>
<center><button type="submit" name="step1_sub" class='cta1'>Continue</button></center>
</form>
<script type="text/javascript">
	$(document).ready(function(){
		$("input[name='language[]']").change(function(){
			var chkArray = [];
			

			 $('input[name="language[]"]:checked').each(function() {
            			chkArray.push($(this).val());
       			 });
			 console.log(chkArray);

			 if($.inArray('Other', chkArray) != -1){
			 	 $("#other_show").show();
			 	}else{
			 		 $("#other_show").val('').hide();
			 	}
			//var this_val = $("input[name='language[]']:checked").val();
			/*$('input[name="language[]"]:checked').each(function() {
				  // console.log(this.value); 
				   if(this.value == 'Other'){
				   	  $("#other_show").show();
				   }else{
				   	  $("#other_show").hide();
				   }
				});*/

		})
	});
</script>
<?php
 displayFooter();