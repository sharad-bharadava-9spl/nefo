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

	?>
<center>	
<div id="mainbox-no-width">	

 <div class='boxcontent'>
<?php	if (isset($_GET['expenseid'])) {
		
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

 <input type="hidden" name="MAX_FILE_SIZE" value="20000000" />
 <!--  <strong><?php //echo $lang['step']; ?> 1:</strong> -->
   <!-- <input type="file" name="fileToUpload" id="fileToUpload"><br> -->
      	   	<div class="upload-btn-wrapper">
			  <button class="btn" >Choose file</button>
			  <input type="file" name="fileToUpload" id="fileToUpload">
			</div><br>
<!--   <strong><?php //echo $lang['step']; ?> 2:</strong> -->
  <input type="submit" class="cta1" value="<?php echo $lang['submit']; ?>" name="submit">
</form>
</div>
</div>
</center>
