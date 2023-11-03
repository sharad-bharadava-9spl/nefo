<?php
	
	require_once 'cOnfig/connection.php';
	require_once 'cOnfig/view.php';
	require_once 'cOnfig/authenticate.php';
	require_once 'cOnfig/languages/common.php';
	
	session_start();
	$accessLevel = '3';
	
	// Authenticate & authorize
	authorizeUser($accessLevel);

	// Generate random temporary expense number, to use throughout the process.
	$tempNo = "_" . generateRandomString();
	$_SESSION['tempNo'] = $tempNo;
	
	
		
	pageStart($lang['title-newexpense'], NULL, $validationScript, "pexpenses", "admin", $lang['upload-receiptC'], $_SESSION['successMessage'], $_SESSION['errorMessage']);

	
	if (isset($_GET['expenseid'])) {
		
		$expenseid = $_GET['expenseid'];
		$action = "expenseid=$expenseid";
		/*echo <<<EOD
<center>
 <a class='cta1' href='new-receipt-upload.php?expenseid=$expenseid'>{$lang['upload-photo']}</a>
 <a class='cta1' href='new-receipt-photo.php?expenseid=$expenseid'>{$lang['use-webcam']}</a>
</center>
EOD;*/
		
	} else if (isset($_GET['closeday'])) {
		$action = "closeday=true";
/*		echo <<<EOD
<center>
 <a class='cta1' href='new-receipt-upload.php?closeday=true'>{$lang['upload-photo']}</a>
 <a class='cta1' href='new-receipt-photo.php?closeday=true'>{$lang['use-webcam']}</a>
 <a class='skipbutton' href='new-expense-1.php?skipFoto&closeday=true'>{$lang['skip']}</a>
</center>
EOD;*/

	} else if (isset($_GET['closeshift'])) {
		$action = "closeshift=true";
/*		echo <<<EOD
<center>
 <a class='cta1' href='new-receipt-upload.php?closeshift=true'>{$lang['upload-photo']}</a>
 <a class='cta1' href='new-receipt-photo.php?closeshift=true'>{$lang['use-webcam']}</a>
 <a class='skipbutton' href='new-expense-1.php?skipFoto&closeshift=true'>{$lang['skip']}</a>
</center>
EOD;*/

	} else if (isset($_GET['closeshiftandday'])) {
		$action = "closeshiftandday=true";
/*		echo <<<EOD
<center>
 <a class='cta1' href='new-receipt-upload.php?closeshiftandday=true'>{$lang['upload-photo']}</a>
 <a class='cta1' href='new-receipt-photo.php?closeshiftandday=true'>{$lang['use-webcam']}</a>
 <a class='skipbutton' href='new-expense-1.php?skipFoto&closeshiftandday=true'>{$lang['skip']}</a>
</center>
EOD;*/

	} else {
		$action = '';
/*		echo <<<EOD
<center>
 <a class='cta1' href='new-receipt-upload.php'>{$lang['upload-photo']}</a>
 <a class='cta1' href='new-receipt-photo.php'>{$lang['use-webcam']}</a>
 <a class='skipbutton' href='new-expense-1.php?skipFoto'>{$lang['skip']}</a>
</center>
EOD;*/

}

?>

<div id="mainbox">
	  <div class="twoboxes">
			
		   <div class="boxheader">
		    Upload photo   </div>
			 <div class="boxcontent">
			 	<?php   if ($_GET['expenseid'] != '') { ?>
					<form name="photo" enctype="multipart/form-data" action="expenses.php?<?php echo $action; ?>" method="post" >
						 <input type="hidden" name="MAX_FILE_SIZE" value="20000000" />
 							<input type="hidden" name="photoupload" />
								<div class="upload-btn-wrapper">
								  <button class="btn">Choose file</button>
								  <input type="file" name="fileToUpload" size="30">
								</div>
						<input type="submit" value="<?php echo $lang['submit']; ?>" name="upload"  class="uploadbutton okbutton1 cta4" style="position: absolute; bottom: 5px; left: 20px;">
					 	
					 </form>
				<?php }else{ ?>	 
					 <form action='new-expense-1.php?<?php echo $action; ?>' method='post' enctype='multipart/form-data'>
					 <input type="hidden" name="MAX_FILE_SIZE" value="20000000" />
					<div class="upload-btn-wrapper">
						  <button class="btn">Choose file</button>
						  <input type="file" name="fileToUpload" size="30" >
						</div>
					 <input type="submit" value="<?php echo $lang['submit']; ?>" name="submit" class="uploadbutton okbutton1 cta4" style="position: absolute; bottom: 5px; left: 20px;">
					
					</form>
				<?php } ?>	
				 </div>
			</div>

			<div class="twoboxes">
				<div class="boxheader">
					    Use webcam   </div>
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
							<input type=button class="cta1" value="<?php echo $lang['member-takesnapshot']; ?>" onClick="preview_snapshot()">
						</div>
						<div id="post_take_buttons" style="display:none">
							<input type=button class="cta1" value="&lt; <?php echo $lang['member-takeanother']; ?>" onClick="cancel_preview()">
							<input type=button class="cta1" value="<?php echo $lang['member-savephoto']; ?> &gt;" onClick="save_photo()" style="font-weight:bold;">
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
					<?php   if ($_GET['expenseid'] != '') { ?>
						<form onsubmit='oneClick.disabled = true; return true;' id='myform' method='post' action='expenses.php?<?php echo $action; ?>'>
					<?php }else{ ?>
						<form onsubmit='oneClick.disabled = true; return true;' id='myform' method='post' action='new-expense-1.php?<?php echo $action; ?>'>
					<?php } ?>
						<input type="hidden" name="mydata" id="mydata" value=""/>
						<input type="hidden" name="newpic" value="yes" />
						</form>


					<br><br>
			</div>
</div>
<center>
  <form action="new-expense-1.php?skipFoto&<?php echo $action; ?>" method="post">
   <input type="submit" name="skip" class="uploadbutton skipbutton" value="<?php echo $lang['skip']; ?>" />
  </form>
 </center> 
<?php	
 displayFooter();
