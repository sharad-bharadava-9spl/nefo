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
	
	$memberno = $_SESSION['tempNo'];
	
	
	// Check if user skipped DNI scan
	if (!isset($_GET['skipDNI'])) {
	
		// Check if a webcam DNI photo was submitted
		if (isset($_POST['mydata'])) {
			$encoded_data = $_POST['mydata'];
			$binary_data = base64_decode( $encoded_data );
			
			$imgname = "images/_$domain/ID/" . $memberno . "-back.jpg";
			// save to server (beware of permissions)
			$result = file_put_contents( $imgname, $binary_data );
			
			if (!$result) die($lang['error-imagesave']);
			
			$_SESSION['dnibackextension'] = 'jpg';
			
		// If not, it means a photo was uploaded. Let's verify it.
		} else {
			
			$image_fieldname = "fileToUpload";
			
			// Potential PHP upload errors
			$php_errors = array(1 => $lang['imgError1'],
								2 => $lang['imgError1'],
								3 => $lang['imgError2'],
								4 => $lang['imgError3']);
							
			// Check for any upload errors
			if ($_FILES[$image_fieldname]['error'] != 0) {
				$_SESSION['errorMessage'] = $php_errors[$_FILES[$image_fieldname]['error']] . " " . $lang['try-again'];
				header("Location: new-member-1b.php");
				exit();
			}
			
			// Check if a real file was uploaded
			if (is_uploaded_file($_FILES[$image_fieldname]['tmp_name'])) {
				
			} else {
				$_SESSION['errorMessage'] = $lang['imgError4'];
				header("Location: new-member-1b.php");
				exit();
			}
			
			// Is this actually an image?
			if (getimagesize($_FILES[$image_fieldname]['tmp_name'])) {
				
			} else {
				$_SESSION['errorMessage'] = $lang['imgError5'];
				header("Location: new-member-1b.php");
				exit();
			}
			
			// Save the file
			$extension = pathinfo($_FILES[$image_fieldname]['name'], PATHINFO_EXTENSION);
			$upload_filename = "images/_$domain/ID/" . $memberno . "-back." . $extension;
			$_SESSION['dnibackextension'] = $extension;
			
			if (move_uploaded_file($_FILES[$image_fieldname]['tmp_name'], $upload_filename)) {
				
			} else {
				$_SESSION['errorMessage'] = $lang['imgError6'];
				header("Location: new-member-1b.php");
				exit();
			}
				
		}
		
		$_SESSION['successMessage'] = $lang['dni-2-success'];
		
	}
	

	
	pageStart($lang['title-memberpicture'], NULL, NULL, "pprofile", "campage", $lang['member-newmembercaps'] . " - " . $lang['title-memberpicture'], $_SESSION['successMessage'], $_SESSION['errorMessage']);
	


?>

<div id='progress'>
 <div id='progressinside4'>
 </div>
</div>
<br />
 <div id='progresstext1'>
 1. <?php echo $lang['avalista']; ?>
 </div>

 <div id='progresstext2'>
 2. <?php echo $lang['member-contract']; ?>
 </div>
 
 <div id='progresstext3'>
 3. <?php echo "DNI / " . $lang['member-passport']; ?>
 </div>
 
 <div id='progresstext4'>
 4. <?php echo $lang['title-memberpicture']; ?>
 </div>
 
 <div id='mainbox'>
  <div id='mainboxheader'>
  <?php echo $lang['member-newmembercaps'] . " - " . $lang['title-memberpicture']; ?>
  </div>
  
  <div class='twoboxes'>
   <div class='boxheader'>
    <?php echo $lang['upload-photo']; ?>
   </div>
   <div class='boxcontent'>
 <form action="new-member-3.php" method="post" enctype="multipart/form-data">
 <input type="hidden" name="MAX_FILE_SIZE" value="20000000" />
 <input type="hidden" name="photoupload" />
<div class="upload-btn-wrapper">
  <button class="btn"><?php echo $lang['choose-file']; ?></button>
  <input type="file" name="fileToUpload" id="fileToUpload">
</div>


	</div>
 <input type="submit" value="Confirmar" name="submit" class="okbutton1" style='position: absolute; bottom: 5px; left: 20px;'>
  </form>
  </div>

  <div class='twoboxes'>
   <div class='boxheader'>
    <?php echo $lang['use-webcam']; ?>
   </div>
	<div id="my_camera"></div>
	<script language="JavaScript">
		Webcam.set({
			height: 240,
			image_format: 'jpeg',
			jpeg_quality: 100
		});
		Webcam.attach( '#my_camera' );
	</script>
	<!-- A button for taking snaps -->
		<div id="pre_take_buttons">
			<input type=button value="<?php echo $lang['member-takesnapshot']; ?>" onClick="preview_snapshot()" class="okbutton1"><br /><br />
			<!--<a class="cta" href="new-member-3.php?skipDNI" style='background-color: red; margin: 0; margin-left: 3px; width: 162px;'><?php echo $lang['skip']; ?></a>-->
		</div>
		<div id="post_take_buttons" style="display:none">
			<input type=button value="<?php echo $lang['member-takeanother']; ?>" onClick="cancel_preview()" class="otherbutton">
			<input type=button value="<?php echo $lang['member-savephoto']; ?>" onClick="save_photo()" style="font-weight:bold;" class="okbutton1"><br /><br />
			<!--<a class="cta" href="new-member-3.php?skipDNI" style='background-color: red; margin: 0; margin-left: 3px; width: 162px;'><?php echo $lang['skip']; ?></a>-->
		</div>
	<!-- Code to handle taking the snapshot and displaying it locally -->
	<script language="JavaScript">
		function preview_snapshot() {
			// freeze camera so user can preview pic
			Webcam.freeze();
			
			// swap button sets
			document.getElementById('pre_take_buttons').style.display = 'none';
			document.getElementById('post_take_buttons').style.display = '';
		}
		
		function cancel_preview() {
			// cancel preview freeze and return to live camera feed
			Webcam.unfreeze();
			
			// swap buttons back
			document.getElementById('pre_take_buttons').style.display = '';
			document.getElementById('post_take_buttons').style.display = 'none';
		}
		
		function save_photo() {
			// actually snap photo (from preview freeze) and display it
			Webcam.snap( function(data_uri) {
		var raw_image_data = data_uri.replace(/^data\:image\/\w+\;base64\,/, '');
		
		document.getElementById('mydata').value = raw_image_data;
		document.getElementById('myform').submit();
				
				// swap buttons back
				document.getElementById('pre_take_buttons').style.display = '';
				document.getElementById('post_take_buttons').style.display = 'none';
			} );
		}
	</script>
	
<?php 
		echo "<form onsubmit='oneClick.disabled = true; return true;' id='myform' method='post' action='new-member-3.php?newmember=true'>";
?>

	
	<input type="hidden" name="mydata" id="mydata" value=""/>
	<input type="hidden" name="user_id" value="<?php echo $user_id; ?>"/>
	<input type="hidden" name="newpic" value="yes" />
	</form>   
  </div>


<br /><br />
</div><br /><br />
<center>

<a class="skipbutton" href="new-member-3.php?skipPhoto"><?php echo $lang['skip']; ?></a>
   </form>
</center>
<?php
 displayFooter();