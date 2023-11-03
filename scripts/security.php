<html>
 <head>
  <title>Camera</title>
  <script src="scripts/jquery-1.10.2.min.js"></script>
  <script src="scripts/jquery.validate.min.js"></script>
  <script src="scripts/webcam.js"></script>


  
  
	<div id="my_camera"></div>
	<script language="JavaScript">
		Webcam.set({
			width: 640,
			height: 480,
			image_format: 'jpeg',
			jpeg_quality: 95
		});
		Webcam.attach( '#my_camera' );
	</script>
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
	
<?php if (isset($_POST['preUser'])) { ?>
	<form id="myform" method="post" action="edit-profile.php">
	<input id="mydata" type="hidden" name="mydata" value=""/>
	<input type="hidden" name="user_id" value="<?php echo $user_id; ?>"/>
	</form>
<?php } else { ?>
		<form id="myform" method="post" action="new-user.php">
		<input id="mydata" type="hidden" name="mydata" value=""/>
	</form>
<?php } ?>

<?php if (isset($_POST['preUser'])) { ?>
<?php } else { ?>
<?php } ?>
	

<?php displayFooter(); ?>
