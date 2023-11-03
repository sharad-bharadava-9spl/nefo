<?php
	
	require_once '../cOnfig/connection-master.php';
	require_once '../cOnfig/view-newclub.php';
	require_once '../cOnfig/languages/common.php';
	
	session_start();

  if(!isset($_SESSION['step2']) || !isset($_SESSION['step3'])){
     $_SESSION['errorMessage'] = "Please complete all the required steps first !";
      if(!isset($_SESSION['step2'])){
        header("location:new-club-2.php");
      }else{
        header("location:new-club-3.php");
      }
     die;
  }
require '../PHPMailerAutoload.php';
// google recaptcha keys
define('SITE_KEY',"6LdnhvAUAAAAAIgJM3Y8TT_HpL_FbBwjyLqO5HEx"); 
define('SECRET_KEY',"6LdnhvAUAAAAAG-TpixmPFwMzlC0NuoBxoHt0cGd");
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

if(isset($_SESSION['temp_id'])){
    $updatee_id = $_SESSION['temp_id'];
   $selectDetails = "SELECT * from temp_customers WHERE id=".$updatee_id;
          try
          {
            $offc_result = $pdo2->prepare("$selectDetails");
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
    $official_club = str_replace("'","\'",str_replace('%', '&#37;', trim($clubRow['official_club'])));      
    $common_club = str_replace("'","\'",str_replace('%', '&#37;', trim($clubRow['common_club'])));      
    $cif = $clubRow['cif'];
    $official_street_name = $clubRow['official_street_name'];
    $official_street_number = $clubRow['official_street_number'];
    $official_local = $clubRow['official_local'];
    $official_postcode = $clubRow['official_postcode'];
    $official_city = $clubRow['official_city'];
    $official_province = $clubRow['official_province'];
    $official_country = $clubRow['official_country'];
    $club_telephone = $clubRow['club_telephone'];
    $club_email = $clubRow['club_email'];
    $club_website = $clubRow['club_website'];
    $club_facebook = $clubRow['club_facebook'];
    $club_instagram = $clubRow['club_instagram'];
    $location_street_name = $clubRow['location_street_name'];
    $location_street_number = $clubRow['location_street_number'];
    $location_local = $clubRow['location_local'];
    $location_postcode = $clubRow['location_postcode'];
    $location_city = $clubRow['location_city'];
    $location_province = $clubRow['location_province'];
    $location_country = $clubRow['location_country'];
    $logo_path = $clubRow['logo_path'];
    $original_path = $clubRow['original_path'];
    $member_contract = $clubRow['member_contract'];
    $other_lang = $clubRow['other_lang'];
  }
}

if(isset($_POST['final_sub'])){
 
      //reCAPTCHA validation
      if (isset($_POST['g-recaptcha-response'])) {
        
        require('recaptcha/src/autoload.php');    
        
        $recaptcha = new \ReCaptcha\ReCaptcha(SECRET_KEY);

        $resp = $recaptcha->verify($_POST['g-recaptcha-response'], $_SERVER['REMOTE_ADDR']);
          
          if (!$resp->isSuccess()) {
           
            $_SESSION['errorMessage'] = "<b>Captcha</b> Validation Required!";
            header("location: new-club-5.php");
            die;
          }
          
        } 
        

    $insertTime = date("Y-m-d H:i:s");
    $hash = $_SESSION['hash'];
    // inser data into contacts table
    if(isset($_SESSION['name']) || isset($_SESSION['email'])){
        $update_contact = sprintf("UPDATE contacts SET role = '%s', language = '%s' WHERE hash = '%s'", 
          $role,
          $language,
          $hash
          );
        try
        {
          $update_result = $pdo2->prepare("$update_contact")->execute();
        }
        catch (PDOException $e)
        {
            $error = 'Error fetching user: ' . $e->getMessage();
            echo $error;
            exit();
        }
    }else{   
          $hash = generateRandomString(20); 
          $insert_contact = sprintf("INSERT INTO contacts (name, telephone, email, role, language, organic, hash) VALUES ('%s', '%s', '%s', '%s', '%s', 1, '%s')", 
          $name,
          $person_phone,
          $person_email,
          $role,
          $language,
          $hash
          );
        try
        {
          $insert_result = $pdo2->prepare("$insert_contact")->execute();
        }
        catch (PDOException $e)
        {
            $error = 'Error fetching user: ' . $e->getMessage();
            echo $error;
            exit();
        }

    }

    // Query to update user - 28 arguments
    $updateUser = sprintf("INSERT INTO customers (registeredSince, person_name, role, phone, email, language,  longName, shortName, cif, street, streetnumber, flat, postcode, city, state, country, club_phone, club_email,  website, facebook, instagram, location_street_name, location_street_number, location_local, location_postcode, location_city, location_province, location_country, member_contract, logo_path, original_path, other_lang, status, autolaunch) VALUES ('%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', 3, 1)", 
                      $insertTime,
                      $name,
                      $role,
                      $person_phone,
                      $person_email,
                      $language,
                      $official_club,
                      $common_club,
                      $cif,
                      $official_street_name,
                      $official_street_number,
                      $official_local,
                      $official_postcode,
                      $official_city,
                      $official_province,
                      $official_country,
                      $club_telephone,
                      $club_email,
                      $club_website,
                      $club_facebook,
                      $club_instagram,
                      $location_street_name,
                      $location_street_number,
                      $location_local,
                      $location_postcode,
                      $location_city,
                      $location_province,
                      $location_country,
                      addslashes($member_contract), 
                      $logo_path,
                      $original_path,
                      $other_lang
            );  

          try
          {
            $result = $pdo2->prepare("$updateUser")->execute();
          }
          catch (PDOException $e)
          {
              $error = 'Error fetching user: ' . $e->getMessage();
              echo $error;
              exit();
          }
          
          $id = $pdo2->lastInsertId();

          /*
    // Insert contact -- only if coming directly to clublaunch (not checking demovideo first)
    $updateUser = "INSERT INTO contacts (name, telephone, emailregisteredSince, person_name, role, phone, email, language,  longName, shortName, cif, street, streetnumber, flat, postcode, city, state, country, club_phone, club_email,  website, facebook, instagram, location_street_name, location_street_number, location_local, location_postcode, location_city, location_province, location_country, member_contract, logo_path, original_path, other_lang, status) VALUES ('%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', 3)", 
                      $insertTime,
                      $name,
                      $role,
                      $person_phone,
                      $person_email,
                      $language,
                      $official_club,
                      $common_club,
                      $cif,
                      $official_street_name,
                      $official_street_number,
                      $official_local,
                      $official_postcode,
                      $official_city,
                      $official_province,
                      $official_country,
                      $club_telephone,
                      $club_email,
                      $club_website,
                      $club_facebook,
                      $club_instagram,
                      $location_street_name,
                      $location_street_number,
                      $location_local,
                      $location_postcode,
                      $location_city,
                      $location_province,
                      $location_country,
                      addslashes($member_contract), 
                      $logo_path,
                      $original_path,
                      $other_lang
            );  

          try
          {
            $result = $pdo2->prepare("$updateUser")->execute();
          }
          catch (PDOException $e)
          {
              $error = 'Error fetching user: ' . $e->getMessage();
              echo $error;
              exit();
          }
          */
          
        // Add member contract file
        if(!empty($member_contract) && $member_contract !=''){
          file_put_contents('../../ccsnubev2_com/v6/_club/contract.php', $member_contract);
        }else{
          unlink('../../ccsnubev2_com/v6/_club/contract.php');
        }
          // Send success emails
          $clubname = $common_club;
            $clubEmail = $person_email;
            $maiAdmin = "info@cannabisclub.systems";
            $email = $clubEmail;
            $subject = "CCS Club Request";
          $adminmail = new PHPMailer();
          $adminmail->isSMTP();
          $usermail = new PHPMailer();
          $usermail->isSMTP();
          $body = "Hello <b>Admin</b><br>
              <p>The club <b>$clubname</b> have finished the process, and you can approve it !</p>";
        sendEmail($adminmail, $maiAdmin, $body, $subject);
        $userMessage = "Hello <b>$clubname</b><br>
                  <p>Welcome to CCS, your application is being reviewed and you will receive an e-mail when we've reviewed it</p><br>Thanks & Regards,<br><b>CCS</b>";
        sendEmail($usermail, $email, $userMessage, 'CCS Club Request');
        //sendEmail($adminmail, $maiAdmin, $userMessage, 'CCS Club Request');
		
        // delete temp data 
        $deleteTempUser = "DELETE from temp_customers WHERE id=".$updatee_id;
          try
          {
            $pdo2->prepare("$deleteTempUser")->execute();
          }
          catch (PDOException $e)
          {
              $error = 'Error fetching user: ' . $e->getMessage();
              echo $error;
              exit();
          }
         unset($_SESSION['temp_id']);
         unset($_SESSION['club_step']);
         unset($_SESSION['official_address']);
         unset($_SESSION['step2']);
         unset($_SESSION['step3']);
          header("Location: new-club-6.php");
          die;
}

pageStart("CCS", NULL, NULL, "pprofile", NULL, "CCS", $_SESSION['successMessage'], $_SESSION['errorMessage']);
?>

<script src='https://www.google.com/recaptcha/api.js'></script> 
<div id='progress'>
 <div id='progressinside5'>
 </div>
</div>
<br />
<div id='progresstext1'>
 1. Personal
</div>
<div id='progresstext2'>
 2.     <?php if ($_SESSION['lang'] == 'es') { echo "Detalles del club"; } else { echo "Club details"; } ?>
</div>
<div id='progresstext3'>
 3. <?php if ($_SESSION['lang'] == 'es') { echo 'Detalles de contacto'; } else { echo 'Contact details'; } ?>
</div>
<div id='progresstext4'>
 4. <?php if ($_SESSION['lang'] == 'es') { echo 'Logo & Contrato'; } else { echo 'Logo & Contract'; } ?>
</div>
<div id='progresstext5'>
 5. <?php if ($_SESSION['lang'] == 'es') { echo 'Confirmar'; } else { echo 'Confirm'; } ?>
</div>
<form id="registerForm" action="" method="POST">
 <div id='mainbox-new-club'>
  <div id='mainboxheader'>
   <center>
    <?php if ($_SESSION['lang'] == 'es') { echo 'Confirmar tu solicitud'; } else { echo 'Confirm your submission'; } ?>
   </center>
  </div>
  <div class='boxcontent'>
  <center><?php if ($_SESSION['lang'] == 'es') { echo 'Por favor comprueba todos los secciones abajo'; } else { echo 'Please review all sections below, before submitting your application'; } ?>.</center>
  </div>
 </div>
 <div id='mainbox-new-club'>
  <div id='mainboxheader'>
   <center>
    <?php if ($_SESSION['lang'] == 'es') { echo 'Detalles personales'; } else { echo 'Personal details'; } ?> &nbsp;<a href="new-club-1.php?edit=preview"><img src="../images/edit.png"/></a>
   </center>
  </div>
  <div class='boxcontent'>
   <strong><?php if ($_SESSION['lang'] == 'es') { echo 'Tú nombre'; } else { echo 'Your name'; } ?></strong>:&nbsp;&nbsp; <?php echo $name; ?><br />
   <strong><?php if ($_SESSION['lang'] == 'es') { echo 'Tú posición'; } else { echo 'Your role'; } ?></strong>:&nbsp;&nbsp; <?php echo $role; ?><br />
   <strong><?php if ($_SESSION['lang'] == 'es') { echo 'Tú telefono'; } else { echo 'Your telephone number'; } ?></strong>:&nbsp;&nbsp; <?php echo $person_phone ?><br />
   <strong><?php if ($_SESSION['lang'] == 'es') { echo 'Tú e-mail'; } else { echo 'Your e-mail address'; } ?></strong>:&nbsp;&nbsp; <?php echo $person_email; ?><br />
   <strong><?php if ($_SESSION['lang'] == 'es') { echo 'Idiomas'; } else { echo 'Languages spoken'; } ?></strong>:&nbsp;&nbsp; <?php echo $language; ?>
   <?php if(!empty($other_lang) && $other_lang != ''){ ?>
    <strong><?php if ($_SESSION['lang'] == 'es') { echo 'Otra idioma'; } else { echo 'Language (Other)'; } ?></strong>:&nbsp;&nbsp; <?php echo $other_lang; ?>
  <?php } ?>
  </div>
 </div>
 <div id='mainbox-new-club'>
  <div id='mainboxheader'>
   <center>
    <?php if ($_SESSION['lang'] == 'es') { echo 'Detalles del club'; } else { echo 'Club details'; } ?> &nbsp;<a href="new-club-2.php?edit=preview"><img src="../images/edit.png" /></a>
   </center>
  </div>
  <div class='boxcontent'>
   <strong><?php if ($_SESSION['lang'] == 'es') { echo 'Nombre oficial'; } else { echo 'Official licensed club name'; } ?></strong>:&nbsp;&nbsp;<?php echo $official_club; ?><br />
   <strong><?php if ($_SESSION['lang'] == 'es') { echo 'Nombre comercial'; } else { echo 'Commonly used club name'; } ?></strong>:&nbsp;&nbsp;<?php echo $common_club; ?><br />
   <strong><?php if ($_SESSION['lang'] == 'es') { echo 'CIF'; } else { echo 'Club CIF'; } ?></strong>:&nbsp;&nbsp;<?php echo $cif; ?><br />
   <strong><?php if ($_SESSION['lang'] == 'es') { echo 'Dirección de facturación'; } else { echo 'Official licensed address'; } ?></strong>:&nbsp;&nbsp;<?php echo $official_street_name; ?>, <?php echo $official_street_number; ?>, <?php echo $official_local; ?><br> <?php echo $official_postcode; ?>, <?php echo $official_city; ?>, <?php echo $official_province ?>, <?php echo $official_country ?><br />
  </div>
 </div>
 <div id='mainbox-new-club'>
  <div id='mainboxheader'>
   <center>
    <?php if ($_SESSION['lang'] == 'es') { echo 'Detalles de contacto'; } else { echo 'Contact details'; } ?> &nbsp;<a href="new-club-3.php?edit=preview"><img src="../images/edit.png" /></a>
   </center>
  </div>
  <div class='boxcontent'>
   <strong><?php if ($_SESSION['lang'] == 'es') { echo 'Teléfono'; } else { echo 'Club telephone'; } ?></strong>:&nbsp;&nbsp;<?php echo $club_telephone; ?><br />
   <strong><?php if ($_SESSION['lang'] == 'es') { echo 'E-mail'; } else { echo 'Club e-mail'; } ?></strong>:&nbsp;&nbsp;<?php echo $club_email; ?><br />
   <strong><?php if ($_SESSION['lang'] == 'es') { echo 'Sitio web'; } else { echo 'Club website'; } ?></strong>:&nbsp;&nbsp;<?php echo $club_website; ?><br />
   <strong><?php if ($_SESSION['lang'] == 'es') { echo 'Facebook'; } else { echo 'Club Facebook'; } ?></strong>:&nbsp;&nbsp;<?php echo $club_facebook; ?><br />
   <strong><?php if ($_SESSION['lang'] == 'es') { echo 'Instagram'; } else { echo 'Club Instagram'; } ?></strong>:&nbsp;&nbsp;<?php echo $club_instagram; ?><br />
  <strong> <?php if ($_SESSION['lang'] == 'es') { echo 'Dirección física'; } else { echo 'Club location'; } ?></strong>:&nbsp;&nbsp;<?php echo $location_street_name; ?>, <?php echo $location_street_number; ?>, <?php echo $location_local; ?><br> <?php echo $location_postcode; ?>, <?php echo $location_city; ?>, <?php echo $location_province ?>, <?php echo $location_country ?><br />
  </div>
 </div>
 <div id='mainbox-new-club'>
  <div id='mainboxheader'>
   <center>
    <?php if ($_SESSION['lang'] == 'es') { echo 'Logo & Contrato'; } else { echo 'Logo & Contract'; } ?> &nbsp;<a href="new-club-4.php?edit=preview"><img src="../images/edit.png" /></a>
   </center>
  </div>
  <div class='boxcontent'>
    <strong>Logo</strong>:
   <center>
    <?php  if(!empty($logo_path)){  ?>
      <img src="<?php echo $logo_path; ?>">
    <?php } ?>
   </center>
  <strong><?php if ($_SESSION['lang'] == 'es') { echo 'Contrato'; } else { echo 'Contract'; } ?></strong>:
  <?php  echo $member_contract; ?>

  </div>
 </div>
 <br>
<center><div class="g-recaptcha" data-sitekey="<?php echo SITE_KEY; ?>"></div></center>
<center><button type="submit" name="final_sub" class='cta1'><?php if ($_SESSION['lang'] == 'es') { echo 'Confirmar'; } else { echo 'Submit'; } ?></button></center>
</form>

<?php
 displayFooter();