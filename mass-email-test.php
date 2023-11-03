<?php
	
	require_once 'cOnfig/connection.php';
	require_once 'cOnfig/view.php';
	require_once 'cOnfig/authenticate.php';
	require_once 'cOnfig/languages/common.php';
	
	session_start();
	$accessLevel = '3';
	
	// Authenticate & authorize
	authorizeUser($accessLevel);
	require 'vendor/autoload.php';
	use Mailgun\Mailgun;
   if(isset($_REQUEST['send_email'])){

   	
   	$email_description = str_replace("'","\'",str_replace('%', '&#37;', trim($_POST['email_description'])));  
   	
   	if(empty($email_description) || $email_description == ''){
   		$_SESSION['errorMessage'] = "Please enter the email message!";
   		header("Location: mass-email-test.php");
   		exit();
   	}
   	//echo $email_description; die;    
		# Include the Autoloader (see "Libraries" for install instructions)

		# Instantiate the client.
		$mgClient = Mailgun::create('2a30584b9b33291864f941932b0e800e-64574a68-ae7837ae', 'https://api.mailgun.net/v3/sandbox2fdf8ed68c09471998ad57df5c1d4cbf.mailgun.org');
		$domain = "sandbox2fdf8ed68c09471998ad57df5c1d4cbf.mailgun.org";

		$params = array(
		    'from' => 'Excited User <andreas@nefosolutions.com>',
		    'to' => 'kiplphp71@konstantinfosolutions.com, ccstest@yopmail.com',
		    'subject' => 'Hello',
		     'text'    => 'Testing some Mailgun awesomness!',
      	'html'    => $email_description
		);


		# Make the call to the client.
		$result = $mgClient->messages()->send($domain, $params);
		$_SESSION['successMessage'] = "Mail sent successfully !";
		header("Location: mass-email-test.php");
		exit();
	}


	  $validationScript = <<<EOD
    $(document).ready(function() {


		tinymce.init({
			selector: '#contacttext',
			height: 400,
			plugins: [
			  'advlist autolink lists link image charmap print preview anchor',
			  'searchreplace visualblocks code fullscreen',
			  'insertdatetime media table paste imagetools wordcount'
			],
			toolbar: 'insertfile undo redo | styleselect | bold italic | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link image',
			  /* enable title field in the Image dialog*/
			  image_title: true,
			  /* enable automatic uploads of images represented by blob or data URIs*/
			  automatic_uploads: true,
			  /*
			    URL of our upload handler (for more details check: https://www.tiny.cloud/docs/configure/file-image-upload/#images_upload_url)
			    images_upload_url: 'postAcceptor.php',
			    here we add custom filepicker only to Image dialog
			  */
			  file_picker_types: 'image',
			  /* and here's our custom image picker*/
			  file_picker_callback: function (cb, value, meta) {
			    var input = document.createElement('input');
			    input.setAttribute('type', 'file');
			    input.setAttribute('accept', 'image/*');

			    /*
			      Note: In modern browsers input[type="file"] is functional without
			      even adding it to the DOM, but that might not be the case in some older
			      or quirky browsers like IE, so you might want to add it to the DOM
			      just in case, and visually hide it. And do not forget do remove it
			      once you do not need it anymore.
			    */

			    input.onchange = function () {
			      var file = this.files[0];

			      var reader = new FileReader();
			      reader.onload = function () {
			        /*
			          Note: Now we need to register the blob in TinyMCEs image blob
			          registry. In the next release this part hopefully won't be
			          necessary, as we are looking to handle it internally.
			        */
			        var id = 'blobid' + (new Date()).getTime();
			        var blobCache =  tinymce.activeEditor.editorUpload.blobCache;
			        var base64 = reader.result.split(',')[1];
			        var blobInfo = blobCache.create(id, file, base64);
			        blobCache.add(blobInfo);

			        /* call the callback and populate the Title field with the file name */
			        cb(blobInfo.blobUri(), { title: file.name });
			      };
			      reader.readAsDataURL(file);
			    };

			    input.click();
			  },
			content_style: 'body { font-family:Helvetica,Arial,sans-serif; font-size:14px }'
		  });

  }); // end ready
EOD;
	
	if (isset($_POST['submitted'])) {
		
		$notFirst = 'true';
		
		$cashBox1 = $_POST['cashBox1'];
		$cashBox2 = $_POST['cashBox2'];
		$cashBox3 = $_POST['cashBox3'];
		$cashBox4 = $_POST['cashBox4'];
		$cashBox5 = $_POST['cashBox5'];
		$cashBox6 = $_POST['cashBox6'];
		$cashBox7 = $_POST['cashBox7'];
		$cashBox9 = $_POST['cashBox9'];
		$cashBox10 = $_POST['cashBox10'];
		
		if ($cashBox1 == 1) {
			$userGroups .= "1,";
		}
		if ($cashBox2 == 1) {
			$userGroups .= "2,";
		}
		if ($cashBox3 == 1) {
			$userGroups .= "3,";
		}
		if ($cashBox4 == 1) {
			$userGroups .= "4,";
		}
		if ($cashBox5 == 1) {
			$userGroups .= "5,";
		}
		if ($cashBox6 == 1) {
			$userGroups .= "6,";
		}
		if ($cashBox7 == 1) {
			$userGroups .= "7,";
		}
		if ($cashBox9 == 1) {
			$userGroups .= "9,";
		}
		if($cashBox10 == 1){
			$timeLimit = "AND date(paidUntil) > CURRENT_DATE";
		}
		
		$userGroups = substr($userGroups, 0, -1);
		
		
	} else { 
		$userGroups = "1,2,3,4,5,6,7,9";
		$timeLimit = "AND date(paidUntil) > CURRENT_DATE";
		
	}
	
	    
	// Query to look up users
	$selectUsers = "SELECT email FROM users WHERE email <> '' AND email LIKE ('%@%') AND email LIKE ('%.%') AND userGroup IN ($userGroups) $timeLimit";
	try
	{
		$result = $pdo3->prepare("$selectUsers");
		$result->execute();
	}
	catch (PDOException $e)
	{
			$error = 'Error fetching user: ' . $e->getMessage();
			echo $error;
			exit();
	}

	
	pageStart("Mass Email", NULL, $validationScript, "pprofile", "statutes1", "Mass EMail", $_SESSION['successMessage'], $_SESSION['errorMessage']);
	

?>
<center>	  

<div id='filterbox'>
 <div id='mainboxheader'>
 <?php echo $lang['filter']; ?>
 </div>
 <div class='boxcontent' style='padding-bottom: 0; text-align: left;'>
        <form action='' method='POST'>
        
<?php    if ($notFirst != 'true') { ?>

	<div class='fakeboxholder'>	
	 <label class="control">
	  &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Administrator
	  <input type="checkbox" name="cashBox1" id="accept1" value='1' checked />
	  <div class="fakebox"></div>
	 </label>
	</div>
	<br />
	<br />
	<div class='fakeboxholder'>	
	 <label class="control">
	  &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Staff
	  <input type="checkbox" name="cashBox2" id="accept2" value='1' checked />
	  <div class="fakebox"></div>
	 </label>
	</div>
	<br />
	<br />
	<div class='fakeboxholder'>	
	 <label class="control">
	  &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Volunteer
	  <input type="checkbox" name="cashBox3" id="accept3" value='1' checked />
	  <div class="fakebox"></div>
	 </label>
	</div>
	<br />
	<br />
	<div class='fakeboxholder'>	
	 <label class="control">
	  &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Professional contact
	  <input type="checkbox" name="cashBox4" id="accept4" value='1' checked />
	  <div class="fakebox"></div>
	 </label>
	</div>
	<br />
	<br />
	<div class='fakeboxholder'>	
	 <label class="control">
	  &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Member
	  <input type="checkbox" name="cashBox5" id="accept5" value='1' checked />
	  <div class="fakebox"></div>
	 </label>
	</div>
	<br />
	<br />
	<div class='fakeboxholder'>	
	 <label class="control">
	  &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Visitor
	  <input type="checkbox" name="cashBox6" id="accept6" value='1' checked />
	  <div class="fakebox"></div>
	 </label>
	</div>
	<br />
	<br />
	<div class='fakeboxholder'>	
	 <label class="control">
	  &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Banned
	  <input type="checkbox" name="cashBox7" id="accept7" value='1' checked />
	  <div class="fakebox"></div>
	 </label>
	</div>
	<br />
	<br />
	<div class='fakeboxholder'>	
	 <label class="control">
	  &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Inactive
	  <input type="checkbox" name="cashBox9" id="accept9" value='1' checked />
	  <div class="fakebox"></div>
	 </label>
	</div>	
		<br />
	<br />
	<div class='fakeboxholder'>	
	 <label class="control">
	  &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Only active members
	  <input type="checkbox" name="cashBox10" id="accept10" value='1' checked />
	  <div class="fakebox"></div>
	 </label>
	</div>
<?php  } else { ?>

	<div class='fakeboxholder'>	
	 <label class="control">
	  &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Administrator
	  <input type="checkbox" name="cashBox1" id="accept1" value='1' <?php if ($cashBox1 == 1) { echo 'checked'; } ?> />
	  <div class="fakebox"></div>
	 </label>
	</div>
	<br />
	<br />
	<div class='fakeboxholder'>	
	 <label class="control">
	  &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Staff
	  <input type="checkbox" name="cashBox2" id="accept2" value='1' <?php if ($cashBox2 == 1) { echo 'checked'; } ?> />
	  <div class="fakebox"></div>
	 </label>
	</div>
	<br />
	<br />
	<div class='fakeboxholder'>	
	 <label class="control">
	  &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Volunteer
	  <input type="checkbox" name="cashBox3" id="accept3" value='1' <?php if ($cashBox3 == 1) { echo 'checked'; } ?> />
	  <div class="fakebox"></div>
	 </label>
	</div>
	<br />
	<br />
	<div class='fakeboxholder'>	
	 <label class="control">
	  &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Professional contact
	  <input type="checkbox" name="cashBox4" id="accept4" value='1' <?php if ($cashBox4 == 1) { echo 'checked'; } ?> />
	  <div class="fakebox"></div>
	 </label>
	</div>
	<br />
	<br />
	<div class='fakeboxholder'>	
	 <label class="control">
	  &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Member
	  <input type="checkbox" name="cashBox5" id="accept5" value='1' <?php if ($cashBox5 == 1) { echo 'checked'; } ?> />
	  <div class="fakebox"></div>
	 </label>
	</div>
	<br />
	<br />
	<div class='fakeboxholder'>	
	 <label class="control">
	  &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Visitor
	  <input type="checkbox" name="cashBox6" id="accept6" value='1' <?php if ($cashBox6 == 1) { echo 'checked'; } ?> />
	  <div class="fakebox"></div>
	 </label>
	</div>
	<br />
	<br />
	<div class='fakeboxholder'>	
	 <label class="control">
	  &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Banned
	  <input type="checkbox" name="cashBox7" id="accept7" value='1' <?php if ($cashBox7 == 1) { echo 'checked'; } ?> />
	  <div class="fakebox"></div>
	 </label>
	</div>
	<br />
	<br />
	<div class='fakeboxholder'>	
	 <label class="control">
	  &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Inactive
	  <input type="checkbox" name="cashBox9" id="accept9" value='1' <?php if ($cashBox9 == 1) { echo 'checked'; } ?> />
	  <div class="fakebox"></div>
	 </label>
	</div>
	<br />
	<br />	
	<div class='fakeboxholder'>	
	 <label class="control">
	  &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Only active members
	  <input type="checkbox" name="cashBox10" id="accept10" value='1' <?php if ($cashBox10 == 1) { echo 'checked'; } ?> />
	  <div class="fakebox"></div>
	 </label>
	</div>
<?php  } ?>
	<br />
	<br />

        </div>
       </td>
      </tr>
     </table>
 	<input type="hidden" name="submitted" value="1">
	<button class="cta1" type="submit">OK</button>
	

	

        </form>
   </div>
   </center>
	<br />
	<br />
<center>	  
<div id='filterbox'>
 <div id='mainboxheader'>
 <?php echo $lang['email-list']; ?>
 </div>
 <div class='boxcontent' style='text-align: left;'>
<form action="" method="POST">
	 	<textarea id="contacttext" name="email_description" placeholder="" class="defaultinput" rows="10"></textarea>
	 		<br />
		<br />
		  <?php

		while ($user = $result->fetch()) {

			if (filter_var($user['email'], FILTER_VALIDATE_EMAIL)) {
					$user_row .=	sprintf("
				%s, ",
				$user['email']
				);
			}
	  	}
	  	
		$user_row = substr($user_row, 0, -2);
		echo $user_row;
		
	?>

	   </div>
	   <br>
	   <br>
	   <button name="send_email" class="cta1" type="submit">Send Email</button>
	</form>
	 </center>
	<script type="text/javascript" src="<?php echo $siteroot; ?>/scripts/tinymce.min.js"></script>
<?php  displayFooter(); ?>
