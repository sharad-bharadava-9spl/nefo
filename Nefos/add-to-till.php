<?php
	
	require_once 'cOnfig/connection.php';
	require_once 'cOnfig/view.php';
	require_once 'cOnfig/authenticate.php';
	
	session_start();
	$accessLevel = '3';
	
	// Authenticate & authorize
	authorizeUser($accessLevel);
	
	// Did this page re-submit with a form? If so, check & store details
	if ($_POST['addToTill'] == 'true') {
		
		$userid = $_POST['userSelect'];
		$moneysource = $_POST['moneySource'];
		$amount = $_POST['amount'];
		$comment = $_POST['comment'];
		$registertime = date('Y-m-d H:i:s'); // 	$purchaseDate = date('Y-m-d H:i:s'); ????

		// Query to add new sale to Sales table - 6 arguments
		  $query = sprintf("INSERT INTO tillmovements (movementtime, type, tillMovementTypeid, userid, amount, comment) VALUES ('%s', '%d', '%d', '%d', '%f', '%s');",
		  $registertime, '1', $moneysource, $userid, $amount, $comment);
		  
		  
		mysql_query($query)
			or handleError("Error saving data to database. Please try again.","Error inserting expense: " . mysql_error());
			
			// On success: redirect.
			$_SESSION['successMessage'] = "Succesfully added to till!";
			header("Location: till-movements.php");
			exit();
		}
	/***** FORM SUBMIT END *****/
	
	$validationScript = <<<EOD
    $(document).ready(function() {
	    	    

	  $('#registerForm').validate({
		  rules: {
			  userSelect: {
				  required: true
			  },
			  moneySource: {
				  required: true
			  },
			  amount: {
				  required: true
			  }
    	},
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
  }); // end ready
EOD;



	

	pageStart("CCS Cloud | Add to till", NULL, $validationScript, "pexpenses", "till", "ADD TO TILL", $_SESSION['successMessage'], $_SESSION['errorMessage']);

?>

<div class="actionbox">
<form id="registerForm" action="" method="POST">
<span class="fakelabel">Added by:</span>
  <select class="fakeInput" name="userSelect">
  <option value="">Choose member</option>
<?php
      	// Query to look up pre-registered users:
		$userDetails = "SELECT user_id, memberno, first_name, last_name FROM users WHERE userGroup != '6' ORDER BY memberno ASC";
		$result = mysql_query($userDetails)
			or handleError("Error loading users from database.","Error loading users from db: " . mysql_error());
			
		while ($user = mysql_fetch_array($result)) {
				$user_row = sprintf("<option value='%d'>#%s - %s %s</option>",
	  								 $user['user_id'], $user['memberno'], $user['first_name'], $user['last_name']);
	  			echo $user_row;
  		}
?>
</select><br />

<span class="fakelabel">Money source:</span>
  <select class="fakeInput" name="moneySource" id="moneySource">
  <option value="">Please choose</option>
<?php
      	// Query to look up tillmovementtypes
		$tillDetails = "SELECT tillMovementTypeid, movementName FROM tillmovementtypes WHERE movementType = '1'";
		
		$result = mysql_query($tillDetails)
			or handleError("Error loading till movements from database.","Error loading tillmovementtypes from db: " . mysql_error());
			
		while ($type = mysql_fetch_array($result)) {
				$type_row = sprintf("<option value='%d'>%s</option>",
	  								 $type['tillMovementTypeid'], $type['movementName']);
	  			echo $type_row;
  		}
?>
</select><br />
<!--
<div id="hideMe" style="display: none;"><span class="fakelabel">Other:</span> <input type="text" name="expense" placeholder="Please enter" class="sixDigit" />
</div>-->
<span class="fakelabel">Amount:</span><input type="number" lang="nb" name="amount" placeholder="&euro;" class="fourDigit" />
<br />
<span class="fakelabel" style="vertical-align: top;">Comment:</span><textarea name="comment" placeholder="Comment?"></textarea>
 <input type="hidden" name="addToTill" value="true" />
 <button class='oneClick' name='oneClick' type="submit">Add to till</button>
 
</form>
</div>
<script>

document.getElementById('moneySource').addEventListener('change', function () {
    var style = this.value == 4 ? 'block' : 'none';
    document.getElementById('hideMe').style.display = style;
});

</script>
<?php displayFooter(); ?>
