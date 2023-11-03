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
		$insertTime = date('Y-m-d H:i:s');
		
		if ($memberno == '') {
			$memberno = $memberNumber;
		}

	
		// Query to add new category - 11 arguments
		$query = sprintf("INSERT INTO b_providers (registered, name, comment, providernumber) VALUES ('%s', '%s', '%s', '%03d');",
		$insertTime, $name, $comment, $memberno);
		try
		{
			$result = $pdo3->prepare("$query")->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
			
		// On success: redirect.
		$_SESSION['successMessage'] = $lang['provider-added'];
		header("Location: bar-providers.php");
		exit();
	}
	/***** FORM SUBMIT END *****/

	$validationScript = <<<EOD
    $(document).ready(function() {
	    	    
	  $('#registerForm').validate({
		  rules: {
			  name: {
				  required: true
			  },
			  memberno: {
        		 require_from_group: [1, '.memberGroup'],
				  digits: true
        	  },
			  memberNumber: {
        		 require_from_group: [1, '.memberGroup']
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

	pageStart($lang['new-provider'], NULL, $validationScript, "pnewcategory", "", $lang['new-provider'], $_SESSION['successMessage'], $_SESSION['errorMessage']);
	
	
	// Find highest available provider number
	$query = "select max(providernumber) from b_providers";
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

	
?>

<form id="registerForm" action="" method="POST">
<center>
<div id="mainbox-no-width">
 <div id="mainboxheader">
  <?php echo $lang['provider']; ?>
 </div>
 <div class='boxcontent'>
 <input type="hidden" name="nextMemberNo" value="<?php echo $nextMemberNo; ?>" /> 
 <input type="text" lang="nb" id="memberno" class="twoDigit memberGroup defaultinput" name="memberno" value="<?php echo sprintf('%03d', $nextMemberNo); ?>" /> <?php echo $lang['or']; ?> 
  <select name="memberNumber" id="memberNumber" class="memberGroup defaultinput" style="width: 60px;">
   <option value=""></option>
<?php
	$sql = "SELECT providernumber FROM b_providers";
		try
		{
			$result = $pdo3->prepare("$sql");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		while ($row = $result->fetch()) {
   		$memberNumbers[] = $row['providernumber'];
	}
	
	for ($i = 0; $i < $nextMemberNo; ++$i) {
		
		if (!in_array($i, $memberNumbers)) {
			
			echo "<option value='$i'>" . sprintf('%03d', $i) . "</option>";
			
    	}
	}
?>
  </select>
  <br />
<script>

$('#memberNumber').change(function() {
  if($(this).val() != ''){
    $('#memberno').val('');
  }
});

$('#memberno').change(function() {
  if($(this).val() != ''){
    $('#memberNumber').val('');
  }
});


</script>
 <input type="text" name="name" placeholder="<?php echo $lang['global-name']; ?>" class='defaultinput' /><br />
 <textarea name="comment" placeholder="<?php echo $lang['global-comment']; ?>" class='defaultinput' style='height: 100px;'></textarea><br /><br />
 <button class='cta4' name='oneClick' type="submit"><?php echo $lang['submit']; ?></button>
</form>
</div>
</div>

<?php displayFooter();