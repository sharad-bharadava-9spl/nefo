<?php
	
	require_once 'cOnfig/connection.php';
	require_once 'cOnfig/view.php';
	require_once 'cOnfig/languages/common.php';
	
	session_start();
	
	

pageStart($lang['title-newpicture'], NULL, NULL, "ppurchase", "admin", $lang['title-newpicture'], $_SESSION['successMessage'], $_SESSION['errorMessage']);

		echo "<form action='new-member-3.php' method='post' enctype='multipart/form-data'>";

?>


 <input type="hidden" name="MAX_FILE_SIZE" value="20000000" />
 <input type="hidden" name="purchaseid" value="<?php echo $purchaseid; ?>" />
 <table>
  <tr>
   <td><strong><?php echo $lang['step']; ?> 1:</strong></td>
   <td style="padding-left: 5px;"><input type="file" name="fileToUpload" id="fileToUpload"></td>
  </tr>
  <tr>
   <td style="padding-top: 10px;"><strong><?php echo $lang['step']; ?> 2:</strong></td>
   <td style="padding-top: 10px; padding-left: 5px;"><input type="submit" value="<?php echo $lang['submit']; ?>" name="submit"></td>
  </tr>
</form>


<?php displayFooter(); ?>
