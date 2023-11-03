<?php
	
	require_once 'cOnfig/connection.php';
	require_once 'cOnfig/viewv6.php';
	require_once 'cOnfig/authenticate.php';
	require_once 'cOnfig/languages/common.php';
	
	session_start();
	$accessLevel = '3';
	
	// Authenticate & authorize
	authorizeUser($accessLevel);
	
	if ($_POST['confirmed'] == 'yes') {
		
		$day = $_POST['day'];
		$month = $_POST['month'];
		$year = $_POST['year'];
		$hour = $_POST['hour'];
		$minute = $_POST['minute'];
		$client = $_POST['client'];
		$comment = str_replace("'","\'",str_replace('%', '&#37;', trim($_POST['comment'])));
		$nowDate = date('Y-m-d H:i');
		
		$registertime = "$year-$month-$day $hour:$minute:00";

				
		$query = sprintf("INSERT INTO inactivecomments (time, customer, comment, operator) VALUES ('%s', '%s', '%s', '%d');",
	  	 $registertime, $client, $comment, $_SESSION['user_id']);
		try
		{
			$result = $pdo2->prepare("$query")->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
			
		// On success: redirect.
		$_SESSION['successMessage'] = "Comment added succesfully!";
		header("Location: inactivity.php");
		exit();
		
	}
	/***** FORM SUBMIT END *****/
	
	$client = $_GET['client'];
	$period = $_GET['period'];
	$invno = $_GET['invno'];
	
	$validationScript = <<<EOD
    $(document).ready(function() {
	    	    
	  $('#registerForm').validate({
		  rules: {
			  day: {
				  required: true
			  },
			  month: {
				  required: true
			  },
			  year: {
				  required: true
			  },
			  hour: {
				  required: true
			  },
			  minute: {
				  required: true
			  },
			  comment: {
				  required: true,
				  minlength: 2
			  }
    	}, // end rules
		  errorPlacement: function(error, element) {
			  if ( element.is(":radio") || element.is(":checkbox")){
				 error.appendTo(element.parent());
			} else {
				return true;
			}
		},
    	  submitHandler: function() {
   $(".oneClick").attr("disabled", true);
   form.submit();
	    	  }
	  }); // end validate

      tinymce.init({
        selector: '#contacttext',
        height :'400',
        plugins: "code",
    });

  }); // end ready
EOD;

	
	pageStart("Nefos tool", NULL, $validationScript, "pprofilenew", "donations fees", $lang['delete-fee-payment'], $_SESSION['successMessage'], $_SESSION['errorMessage']);


?>
<center>
<div id="mainbox-no-width">
 <div class='boxcontent'>
 <form id="registerForm" action="" method="POST">
<strong>Comment date</strong><br /><br />
<input type="number" lang="nb" name="day" id="day" class="defaultinput twoDigit" maxlength="2" placeholder="dd" value="<?php echo date('d'); ?>" />
 <input type="number" lang="nb" name="month" id="month" class="defaultinput twoDigit" maxlength="2" placeholder="mm" value="<?php echo date('m'); ?>" />
 <input type="number" lang="nb" name="year" id="year" class="defaultinput fourDigit" maxlength="4" placeholder="<?php echo $lang['member-yyyy']; ?>" value="<?php echo date('Y'); ?>" />
 @
 <input type="number" lang="nb" name="hour" id="hour" class="defaultinput twoDigit" maxlength="2" placeholder="h" value="<?php echo date('H'); ?>" />
 :
 <input type="number" lang="nb" name="minute" id="minute" class="defaultinput twoDigit" maxlength="2" placeholder="m" value="<?php echo date('i'); ?>" />
<br /><br /><br />

  <textarea name="comment" id="contacttext" placeholder="<?php echo $lang['global-comment']; ?>?" style='width: 800px;'></textarea>
  <input type='hidden' name='confirmed' value='yes' />
  <input type='hidden' name='client' value='<?php echo $client; ?>' />
        <br /><br />

  <button class='oneClick okbutton2' name='oneClick' type="submit" style='margin-left: -2px; width: 286px;'><?php echo $lang['global-confirm']; ?></button></td>

 </form>
</div>
</div>
<script src="https://cdn.tiny.cloud/1/9pxfemefuncr8kvf2f5nm34xwdg8su9zxhktrj66loa5mexa/tinymce/5/tinymce.min.js" referrerpolicy="origin"></script>

<?php displayFooter();