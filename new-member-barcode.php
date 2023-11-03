<?php
	// Created by konstant for Task-14971249 on 10-11-2021
	require_once 'cOnfig/connection.php';
	require_once 'cOnfig/view.php';
	require_once 'cOnfig/authenticate.php';
	require_once 'cOnfig/languages/common.php';
	
	session_start();
	$accessLevel = '3';
	
	// Authenticate & authorize
	authorizeUser($accessLevel);
	

	pageStart( $lang['member-newmembercaps']. " Scan ID", NULL, NULL, "pprofile", "campage", $lang['member-newmembercaps']. " Scan ID", $_SESSION['successMessage'], $_SESSION['errorMessage']);


?>

 
 <div id='mainbox'>
  <div id='mainboxheader'>
  <?php echo $lang['member-newmembercaps']. " Scan ID"; ?>
  </div>
  
  <div class='twoboxes'>
   <div class='boxheader'>
    <?php echo $lang['upload-photo']; ?>
   </div>
   <div class='boxcontent'>
 <form action="member-scan-process.php" method="post" enctype="multipart/form-data">
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
			<!--<a class="cta" href="new-member-2.php?skipDNI" style='background-color: red; margin: 0; margin-left: 3px; width: 162px;'><?php echo $lang['skip']; ?></a>-->
		</div>
		<div id="post_take_buttons" style="display:none">
			<input type=button value="<?php echo $lang['member-takeanother']; ?>" onClick="cancel_preview()" class="otherbutton">
			<input type=button value="<?php echo $lang['member-savephoto']; ?>" onClick="save_photo()" style="font-weight:bold;" class="okbutton1"><br /><br />
			<!--<a class="cta" href="new-member-2.php?skipDNI" style='background-color: red; margin: 0; margin-left: 3px; width: 162px;'><?php echo $lang['skip']; ?></a>-->
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
		echo "<form onsubmit='oneClick.disabled = true; return true;' id='myform' method='post' action='member-scan-process.php?newmember=true'>";
?>

	
	<input type="hidden" name="mydata" id="mydata" value=""/>
	<input type="hidden" name="newpic" value="yes" />
	</form>   
  </div>


<br /><br />
</div><br /><br />
<center>
   </form>
</center>
<?php
 displayFooter();
?>
