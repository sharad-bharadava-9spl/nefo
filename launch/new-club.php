<?php

 ob_start();
	require_once 'cOnfig/connection-master.php';
	require_once 'cOnfig/view-newclub.php';
	require_once 'cOnfig/languages/common.php';
    session_start();
    require "vendor/autoload.php";
	use PHPMailer\PHPMailer\PHPMailer;
	use PHPMailer\PHPMailer\Exception;
$validationScript = <<<EOD
    $(document).ready(function() {
    	  $.validator.addMethod("noSpace", function(value, element) { 
			  return value.indexOf(" ") < 0 && value != ""; 
			}, "No space please and don't leave it empty");
			$.validator.addMethod('filesize', function (value, element, param) {
			    return this.optional(element) || (element.files[0].size <= param)
			}, 'File size must be less than 2 MB');
      $("#clubRegisterForm").validate({
      		 rules: {
      		 	official_club: {
			      required: true,
			      noSpace: true,
			      remote: {
			        url: "check-club.php",
			        type: "post",
			        data: {
			          official_club: function() {
			            return $("#offcial_club").val();
			          }
			        }
			      }
			    },      		 	
			    club_email: {
			      required: true,
			      email: true,
			      remote: {
			        url: "check-club-email.php",
			        type: "post",
			        data: {
			          club_email: function() {
			            return $("#club_email").val();
			          }
			        }
			      }
			    },
			    phone_number: {
			      required: true,
			      number: true,
			      maxlength:11
			    },
			    website: {
			    	url: true
			    },
			    facebook: {
			    	url: true
			    },
			    instagram: {
			    	url: true
			    },
			    club_logo: {
			    	extension: "jpg|jpeg|png|svg",
			    	filesize: 2097152
			    }
			  }, 
			 messages:
             {
                 official_club:
                 {
                    remote: $.validator.format("{0} is already taken.")
                 },                 
                 club_email:
                 {
                    remote: $.validator.format("{0} is already exist.")
                 },
                 club_logo: {
                 	extension: 'Please upload valid image file',
                 	filesize: 'File size must be less than 2 MB'
                 }

             }, 
            errorPlacement: function(error, element) {
                error.insertAfter(element);
            },

      	});
       tinymce.init({
		    selector: '#contacttext',
		    height :'400',
		    plugins: "code",
		});
		$('#file-upload').change(function() {
		  var file = $('#file-upload')[0].files[0].name;
		  $('#filename').text(file);
		});
  }); // end ready
EOD;

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

$lang = 'en';
$_SESSION['lang'] = 'en';
pageStart($lang['title-club'], NULL, $validationScript, "pnewclub", "clubloggedOut dev-align-center", NULL, $_SESSION['successMessage'], $_SESSION['errorMessage']);

//only assign a new timestamp if the session variable is empty
if (!isset($_SESSION['random_key']) || strlen($_SESSION['random_key'])==0){
    $_SESSION['random_key'] = strtotime(date('Y-m-d H:i:s')); //assign the timestamp to the session variable
	$_SESSION['user_file_ext']= "";
}

$max_file = "8"; 

$allowed_image_types = array('image/pjpeg'=>"jpg",'image/jpeg'=>"jpg",'image/jpg'=>"jpg",'image/pjpeg'=>"jpeg",'image/jpeg'=>"jpeg",'image/jpg'=>"jpeg",'image/png'=>"png",'image/x-png'=>"png",'image/gif'=>"gif");


$upload_dir = "images/_club"; 		// The directory for the images to be saved in
if(!is_dir($upload_dir)){
	mkdir($upload_dir, 0777);
}
$upload_dir = "images/_club";
$upload_path = $upload_dir."/";				// The path to where the image will be saved
$large_image_prefix = "resize_"; 			// The prefix name to large image
$thumb_image_prefix = "";					// The prefix name to the thumb image
$large_image_name = $large_image_prefix.strtotime(date('Y-m-d H:i:s'));     // New name of the large image (append the timestamp to the filename)
$max_file = "2"; 							// Maximum file size in MB
$max_width = "500";							// Max width allowed for the large image
$thumb_width = "150";						// Width of thumbnail image
$thumb_height = "100";						// Height of thumbnail image

//Image Locations

function makeThumbnails($source)
{
    $thumbnail_width = 150;
    $thumbnail_height = 100;
    $thumb_beforeword = "thumb";
    $arr_image_details = getimagesize($source); // pass id to thumb name
    $original_width = $arr_image_details[0];
    $original_height = $arr_image_details[1];
    if ($original_width > $original_height) {
        $new_width = $thumbnail_width;
        $new_height = intval($original_height * $new_width / $original_width);
    } else {
        $new_height = $thumbnail_height;
        $new_width = intval($original_width * $new_height / $original_height);
    }
    $dest_x = intval(($thumbnail_width - $new_width) / 2);
    $dest_y = intval(($thumbnail_height - $new_height) / 2);
    if ($arr_image_details[2] == IMAGETYPE_GIF) {
        $imgt = "ImageGIF";
        $imgcreatefrom = "ImageCreateFromGIF";
    }
    if ($arr_image_details[2] == IMAGETYPE_JPEG) {
        $imgt = "ImageJPEG";
        $imgcreatefrom = "ImageCreateFromJPEG";
    }
    if ($arr_image_details[2] == IMAGETYPE_PNG) {
        $imgt = "ImagePNG";
        $imgcreatefrom = "ImageCreateFromPNG";
    }
    if ($source) {
        $old_image = $imgcreatefrom($source);
        $new_image = imagecreatetruecolor($thumbnail_width, $thumbnail_height);
        imagecopyresized($new_image, $old_image, $dest_x, $dest_y, 0, 0, $new_width, $new_height, $original_width, $original_height);
        $imgt($new_image, $source);
    }
    	chmod($source, 0777);
	return $source;
}

function resizeImage($image,$width,$height,$scale) {
	list($imagewidth, $imageheight, $imageType) = getimagesize($image);
	$imageType = image_type_to_mime_type($imageType);
	$newImageWidth = ceil($width * $scale);
	$newImageHeight = ceil($height * $scale);
	$newImage = imagecreatetruecolor($newImageWidth,$newImageHeight);
	switch($imageType) {
		case "image/gif":
			$source=imagecreatefromgif($image); 
			break;
	    case "image/pjpeg":
		case "image/jpeg":
		case "image/jpg":
			$source=imagecreatefromjpeg($image); 
			break;
	    case "image/png":
		case "image/x-png":
			$source=imagecreatefrompng($image); 
			break;
  	}
	imagecopyresampled($newImage,$source,0,0,0,0,$newImageWidth,$newImageHeight,$width,$height);
	
	switch($imageType) {
		case "image/gif":
	  		imagegif($newImage,$image); 
			break;
      	case "image/pjpeg":
		case "image/jpeg":
		case "image/jpg":
	  		imagejpeg($newImage,$image,100); 
			break;
		case "image/png":
		case "image/x-png":
			imagepng($newImage,$image);  
			break;
    }
	
	chmod($image, 0777);
	return $image;
}

//You do not need to alter these functions
function getHeight($image) {
	$size = getimagesize($image);
	$height = $size[1];
	return $height;
}
//You do not need to alter these functions
function getWidth($image) {
	$size = getimagesize($image);
	$width = $size[0];
	return $width;
}

$large_image_location = $upload_path.$large_image_name.$_SESSION['user_file_ext'];
if(isset($_POST['club_proceed'])){
	$official_club = str_replace("'","\'",str_replace('%', '&#37;', trim($_POST['official_club'])));
	$common_club = $_POST['common_club'];
	$cif_club = $_POST['cif_club'];
	$club_languages = $_POST['club_languages'];
	$street = $_POST['street'];
	$streetnumber = $_POST['add_number'];
	$apartment = $_POST['apartment'];
	$postcode = $_POST['postcode'];
	$city = $_POST['city'];
	$province = $_POST['province'];
	$country = $_POST['country'];
	$phone_number = $_POST['phone_number'];
	$club_email = $_POST['club_email'];
	$website = $_POST['website'];
	$facebook = $_POST['facebook'];
	$instagram = $_POST['instagram'];
	$find_us = $_POST['find_us'];
	$member_contract = addslashes($_POST['member_contract']);
	$insertTime = date("Y-m-d H:i:s");

	
	if (!empty($_FILES['club_logo']['name'])) { 
	//Get the file information

		$clubfile_name = $_FILES['club_logo']['name'];
		$clubfile_tmp = $_FILES['club_logo']['tmp_name'];
		$clubfile_size = $_FILES['club_logo']['size'];
		$clubfile_type = $_FILES['club_logo']['type'];
		$filename = basename($_FILES['club_logo']['name']);
		$file_ext = strtolower(substr($filename, strrpos($filename, '.') + 1));
		$_SESSION['extension'] = $file_ext;
		
		//Only process if the file is a JPG, PNG or GIF and below the allowed limit
		if((!empty($_FILES["club_logo"])) && ($_FILES['club_logo']['error'] == 0)) {
			
			foreach ($allowed_image_types as $mime_type => $ext) {
				//loop through the specified image types and if they match the extension then break out
				//everything is ok so go and check file size
				if($file_ext==$ext && $clubfile_type==$mime_type){
					$error = "";
					break;
				}
			}
			//check if the file size is above the allowed limit
			if ($clubfile_size > ($max_file*1048576)) {
				$_SESSION['errorMessage'].= "Images must be under ".$max_file."MB in size";
				header("Location: new-club.php");
			}
			
		}

		//Everything is ok, so we can upload the image.
		if (strlen($error)==0){
			
			if (isset($_FILES['club_logo']['name'])){
				//this file could now has an unknown file extension (we hope it's one of the ones set above!)
				if ($_SESSION['user_file_ext'] == "") {
					$large_image_location = $large_image_location.".".$file_ext;
					//$thumb_image_location = $thumb_image_location.".".$file_ext;
				}			
				
				//put the file ext in the session so we know what file to look for once its uploaded
				$_SESSION['user_file_ext']=".".$file_ext;
				
				move_uploaded_file($clubfile_tmp, $large_image_location);
				chmod($large_image_location, 0777);
				$uploaded = makeThumbnails($large_image_location);
			/*	$width = $thumb_width;
				$height = $thumb_height;
				//Scale the image if it is greater than the width set above
				if ($width > $max_width){
					$scale = $max_width/$width;
					$uploaded = make_thumb($large_image_location, $large_image_location, )
					//$uploaded = resizeImage($large_image_location,$width,$height,$scale);
				}else{
					$scale = 1;
					$uploaded = resizeImage($large_image_location,$width,$height,$scale);
				}*/
			
			}
			
		}
	}else{
		$large_image_location = '';
	}

		// Add member contract file
		if(!empty($member_contract) || $member_contract !=''){
			file_put_contents('contract.php', $member_contract);
		}else{
			unlink('contract.php');
		}

		// Query to update user - 28 arguments
		$updateUser = sprintf("INSERT INTO customers (registeredSince, longName, shortName, cif, street, streetnumber, flat, postcode, city, state, country, website, email, facebook, instagram, phone, source, member_contract, logo_path, language) VALUES ('%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s')", 
		
						$insertTime,
						$official_club,
						$common_club,
						$cif_club,
						$street,
						$streetnumber,
						$apartment,
						$postcode,
						$city,
						$province,
						$country,
						$website,
						$club_email,
						$facebook,
						$instagram,
						$phone_number,
						$find_us,
						$member_contract,
						$large_image_location,
						$club_languages
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
					// Send success emails
					$clubname = $official_club;
				    $clubEmail = $club_email;
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
							

					$_SESSION['successMessage'] = "Your application is being reviewed and you will receive an e-mail when we've reviewed it !";
					header("Location: new-club.php");
}


?>
	 <div id="load">
	 </div>
		<div class="actionbox-np2" style="width:70%;">
			<div class="mainboxheader" style="text-align: center;"><?php echo $lang['club-register']; ?></div>
			<div class="boxcontent">
				<form action="" method="POST" id="clubRegisterForm" enctype="multipart/form-data">

                     <label class="input_label required"><?php echo $lang['official-club']; ?>:</label><input type="text" name="official_club" id="offcial_club" placeholder="<?php echo $lang['official-club']; ?>" class="defaultinput" required><br>
                     <label class="input_label required"><?php echo $lang['common-club-name']; ?>:</label><input type="text" name="common_club" placeholder="<?php echo $lang['common-club-name']; ?>" class="defaultinput" required><br>
                    <label class="input_label"><?php echo $lang['club-cif']; ?>:</label><input type="text" name="cif_club" placeholder="<?php echo $lang['club-cif'] ?>" class="defaultinput"><br>
					 <label class="input_label required"><?php echo $lang['language-spoken']; ?>:</label><input type="text" name="club_languages" placeholder="<?php echo $lang['language-spoken']; ?>" class="defaultinput" required><br>
<!--					<span class="smallgreen"><?php echo $lang['club-address']; ?></span>-->
					 <label class="input_label required"><?php echo $lang['club-street']; ?>:</label><input type="text" name="street" placeholder="<?php echo $lang['club-street'];  ?>" class="defaultinput" required><br>
					 <label class="input_label required"><?php echo $lang['street-number']; ?>:</label><input type="text" name="add_number" placeholder="<?php echo $lang['street-number'] ?>" class="defaultinput" required><br>
					<label class="input_label required"><?php echo $lang['apartment-number']; ?>:</label><input type="text" name="apartment" placeholder="<?php echo $lang['apartment-number']; ?>" class="defaultinput" required><br>
					<label class="input_label required"><?php echo $lang['postcode-number']; ?>:</label><input type="text" name="postcode" placeholder="<?php echo $lang['postcode-number']; ?>" class="defaultinput" required><br>
					<label class="input_label required"><?php echo $lang['club-city']; ?>:</label><input type="text" name="city" placeholder="<?php echo $lang['club-city'];  ?>" class="defaultinput" required><br>
					<label class="input_label required"><?php echo $lang['club-province']; ?>:</label><input type="text" name="province" placeholder="<?php echo $lang['club-province'];  ?>" class="defaultinput" required><br>
					<label class="input_label required"><?php echo $lang['club-country']; ?>:</label><input type="text" name="country" placeholder="<?php echo $lang['club-country'];  ?>" class="defaultinput" required><br>
					<label class="input_label required"><?php echo $lang['club-telephone-number']; ?>:</label><input type="text" name="phone_number" placeholder="<?php echo $lang['club-telephone-number'];  ?>" class="defaultinput" required><br>
					<label class="input_label required"><?php echo $lang['email-club']; ?>:</label><input type="email" name="club_email" id="club_email" placeholder="<?php echo $lang['email-club'];  ?>" class="defaultinput" required><br>
					<label class="input_label"><?php echo $lang['website-club']; ?>:</label><input type="text" name="website" placeholder="<?php echo $lang['website-club'];  ?>" class="defaultinput"><br>
					<label class="input_label"><?php echo $lang['facebook-club']; ?>:</label><input type="text" name="facebook" placeholder="<?php echo $lang['facebook-club'];  ?>" class="defaultinput"><br>
					<label class="input_label"><?php echo $lang['instagram-club']; ?>:</label><input type="text" name="instagram" placeholder="<?php echo $lang['instagram-club'];  ?>" class="defaultinput"><br>
					 <label class="input_label required"><?php echo $lang['find-us']; ?>:</label><select name="find_us" id="findus" class="defaultinput" required="" style="height: 40px;">
					    <option value="">Select</option>
					    <option value="Google">Google</option>
					    <option value="Recommendation">Recommendation</option>
					    <option value="Lawyer">Lawyer</option>
					    <option value="Accountant">Accountant</option>
					    <option value="Instagram">Instagram</option>
					    <option value="Facebook">Facebook</option>
					    <option value="On-site visit">On-site visit</option>
					    <option value="Marketing">Marketing</option>
					    <option value="Weedmaps/MMJ Menu">Weedmaps/MMJ Menu</option>
					    <option value="MJ Freeway">MJ Freeway</option>
					    <option value="Gestion Verde">Gestion Verde</option>
					    <option value="Weedgest">Weedgest</option>
					    <option value="Easy CSC">Easy CSC</option>
					    <option value="Other">Other</option>
					   </select><br>
                     <label class="input_label">Upload club logo:</label><input type="file" name="club_logo" id="file-upload" class="defaultinput" title="Upload club logo" accept="image/*"><br>
					<label class="input_label"><?php echo $lang['club-member-contract']; ?>:</label><textarea id="contacttext" name="member_contract" placeholder="<?php echo $lang['club-member-contract'];  ?>" class="defaultinput" rows="10"></textarea>
					<button type="submit" name="club_proceed" id="clickSub" class="cta1"><?php echo $lang['club-proceed']; ?></button>
				</form>
			</div>
		</div>


<?php
displayFooter();  ?>

<script type="text/javascript">
$("#clickSub").click(function(){
	if($("#clubRegisterForm").valid()){
		$("#clubRegisterForm").submit(function(){
				 $("#load").show();
				    setTimeout(function () {
				        $("#load").hide();
				    }, 16000);     
		});
	}
});
</script>