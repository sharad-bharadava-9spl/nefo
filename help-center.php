<?php
	
	require_once 'cOnfig/connection.php';
	require_once 'cOnfig/view.php';
	require_once 'cOnfig/authenticate.php';
	require_once 'cOnfig/languages/common.php';
	//ini_set("display_errors", "on");
	
	session_start();
	$accessLevel = '3';
	
	// Authenticate & authorize
	authorizeUser($accessLevel);
	
	getSettings();	
	$validationScript = <<<EOD
    $(document).ready(function() {
      $("#feedback_form").validate({
      		rules:{
      			reason:{required: true },
      			issue:{required: true },
      			message:{required: true },
      		}
      	});
  }); // end ready
  
function delete_ticket(ticketid) {
	if (confirm("{$lang['confirm-deletenote']}")) {
				window.location = "uTil/delete-ticket.php?ticketid=" + ticketid;
				}
}

EOD;

	$now = date('Y-m-d H:i:s');

	$query = "INSERT INTO helpcenter (time, domain, user_id) VALUES ('$now', '$domain', {$_SESSION['user_id']})";
	try
	{
		$result = $pdo->prepare("$query")->execute();
	}
	catch (PDOException $e)
	{
			$error = 'Error fetching user: ' . $e->getMessage();
			echo $error;
			exit();
	}

	pageStart($lang['help-center'], NULL, $validationScript, "help-center", NULL, $lang['help-center'], $_SESSION['successMessage'], $_SESSION['errorMessage']);


?>
	<div id="help-tabs" style="display: none;">
	  <ul>
	    <li><a href="#feedback"><?php echo $lang['feedback']; ?></a></li>
	    <li><a href="#faq">FAQ</a></li>
	    <li><a href="video-tutorials.php"><?php echo $lang['video-tutorials']; ?></a></li>
	    <li><a href="#updates"><?php echo $lang['updates']; ?></a></li>
	  </ul>
	  <div id="feedback">
	  	<?php  include "help-feedback-form.php";  ?>
	  </div>
	 <div id="faq" style="display: none;">
	    <?php include "help-faq.php"; ?>
	  </div>
	 <div id="video-tutorials" style="display: none;">
	    <?php //include "video-tutorials.php" ?>
	  </div>
	  <div id="updates" style="display: none;">
	    <?php include "help-updates.php" ?>
	  </div>
	</div>
	<?php  displayFooter(); ?>
	  <script>
	  	document.onreadystatechange = function () {
			  var state = document.readyState
			   if (state == 'complete') {
			         document.getElementById('interactive');
			        // document.getElementById('help-tabs').style.visibility="block";
			         $("#help-tabs").show();
			  }
			}
		  $( function() {
		  		 $( "#help-tabs" ).tabs();
		  });
  	</script>
