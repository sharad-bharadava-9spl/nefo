<?php
	
	require_once 'cOnfig/connection.php';
	require_once 'cOnfig/view-photo.php';
	require_once 'cOnfig/authenticate.php';
	require_once 'cOnfig/languages/common.php';
	
	session_start();
	$accessLevel = '3';
	
	// Authenticate & authorize
	authorizeUser($accessLevel);
	
	
	pageStart($lang['global-receipt'], NULL, NULL, "ppurchase", "admin", $lang['global-receipt'], $_SESSION['successMessage'], $_SESSION['errorMessage']);
	
	if (isset($_GET['expenseid'])) {
		
		$expenseid = $_GET['expenseid'];
		echo "<form action='expenses.php?expenseid=$expenseid&saveimg' method='post' enctype='multipart/form-data'>";
		
	} else if (isset($_GET['closeday'])) {
		
		echo "<form action='new-expense-1.php?closeday=true' method='post' enctype='multipart/form-data'>";
		
	} else if (isset($_GET['closeshift'])) {
		
		echo "<form action='new-expense-1.php?closeshift=true' method='post' enctype='multipart/form-data'>";
		
	} else if (isset($_GET['closeshiftandday'])) {
		
		echo "<form action='new-expense-1.php?closeshiftandday=true' method='post' enctype='multipart/form-data'>";
		
	} else {
		
		echo "<form action='new-expense-1.php' method='post' enctype='multipart/form-data'>";
		
	}

?>

 <input type="hidden" name="MAX_FILE_SIZE" value="2000000" />
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