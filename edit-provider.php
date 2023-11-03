<?php
	
	require_once 'cOnfig/connection.php';
	require_once 'cOnfig/view.php';
	require_once 'cOnfig/authenticate.php';
	require_once 'cOnfig/languages/common.php';
	
	session_start();
	$accessLevel = '3';
		
	// Authenticate & authorize
	authorizeUser($accessLevel);
	
	// Did this page re-submit with a form? If so, check & store details
	if (isset($_POST['name'])) {

		$memberno = $_POST['memberno'];
		$memberNumber = $_POST['memberNumber'];
		$name = str_replace("'","\'",str_replace('%', '&#37;', trim($_POST['name'])));
		$comment = str_replace("'","\'",str_replace('%', '&#37;', trim($_POST['comment'])));
		$providerid = $_POST['providerid'];
		
		if ($memberno == '') {
			$memberno = $memberNumber;
		}
	
		// Query to update provider
		$updateCat = sprintf("UPDATE providers SET name = '%s', comment = '%s', providernumber = '%d' WHERE id = '%d';",
			$name,
			$comment,
			$memberno,
			$providerid
		);
		
		try
		{
			$result = $pdo3->prepare("$updateCat")->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
			
		// On success: redirect.
		$_SESSION['successMessage'] = $lang['provider-updated'];
		header("Location: providers.php");
		exit();
	}
	/***** FORM SUBMIT END *****/

	$validationScript = <<<EOD
    $(document).ready(function() {
	    
	    
$('#memberNumber').on('click keypress keyup blur', function() {
  if($(this).val() != ''){
    $('#memberno').val('');
  }
});

$('#memberno').on('click keypress keyup blur', function() {
  if($(this).val() != ''){
    $('#memberNumber').val('');
  }
});

	    	    
	  $('#registerForm').validate({
		  rules: {
			  name: {
				  required: true
			  }
    	}, // end rules
    	errorPlacement: function(error, element) { },
    	  submitHandler: function() {
   $(".oneClick").attr("disabled", true);
   form.submit();
	    	  }
	  }); // end validate
  }); // end ready
EOD;

	$providerid = $_GET['providerid'];

	// Query to look for category
	$categoryDetails = "SELECT name, comment, providernumber FROM providers WHERE id = $providerid";
		try
		{
			$result = $pdo3->prepare("$categoryDetails");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
		$name = $row['name'];
		$comment = $row['comment'];
		$providernumber = $row['providernumber'];

	$query = "select max(memberno) from users";
		try
		{
			$result = $pdo3->prepare("$query");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
		$nextMemberNo = $row['0'] + 1;

	pageStart($lang['edit-provider'], NULL, $validationScript, "pnewcategory", "", $lang['edit-provider'], $_SESSION['successMessage'], $_SESSION['errorMessage']);
	
	
?>
<form id="registerForm" action="" method="POST">
<center>
<div id="mainbox-no-width">
 <div id="mainboxheader">
  <?php echo $lang['provider']; ?>
 </div>
 <div class='boxcontent'>
 <input type="hidden" name="providerid" value="<?php echo $providerid; ?>" />
 <input type="text" lang="nb" id="memberno" class="twoDigit memberGroup defaultinput" name="memberno" value="<?php echo sprintf('%03d', $providernumber); ?>" /> <?php echo $lang['or']; ?> <select name="memberNumber" id="memberNumber" class="memberGroup defaultinput" style="width: 60px;">
   <option value=""></option>
<?php
	$sql = "SELECT providernumber FROM providers";
		try
		{
			$results = $pdo3->prepare("$sql");
			$results->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		while ($row = $results->fetch()) {
   		$memberNumbers[] = $row['providernumber'];
	}
	
	for ($i = 0; $i < $nextMemberNo; ++$i) {
		
		if (!in_array($i, $memberNumbers)) {
			echo "<option value='$i'>" . sprintf('%03d', $i) . "</option>";
    	}
	}
				echo "<option value='$nextMemberNo'>" . sprintf('%03d', $nextMemberNo) . "</option>";

?>
  </select>
   <input type="text" name="name" value="<?php echo $name; ?>" class='defaultinput' /><br />
   <textarea name="comment" placeholder="<?php echo $lang['global-comment']; ?>" class='defaultinput' style='height: 100px;'><?php echo $description; ?></textarea><br />
<br />
 <button class='oneClick' name='oneClick' type="submit"><?php echo $lang['submit']; ?></button>
</form>

<?php displayFooter(); ?>

