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
	if ($_POST['addToTill'] == 'true') {
		
		$userid = $_POST['userSelect'];
		$amount = $_POST['amount'];
		$comment = $_POST['comment'];
		$registertime = date('Y-m-d H:i:s'); // 	$purchaseDate = date('Y-m-d H:i:s'); ????

		// Query to add to banked table
		  $query = sprintf("INSERT INTO banked (time, amount, userid, comment) VALUES ('%s', '%f', '%d', '%s');",
		  $registertime, $amount, $userid, $comment);
		  
		  
		mysql_query($query)
			or handleError("Error saving data to database. Please try again.","Error inserting expense: " . mysql_error());
			
			// On success: redirect.
			$_SESSION['successMessage'] = $lang['banked-money'];
			header("Location: admin.php");
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
  
  	function delete_banked(bankedid) {
		if (confirm("{$lang['expense-deleteconfirm']}")) {
			
					window.location = "uTil/delete-banked.php?id=" + bankedid;
					
		}
	}

EOD;



	

	pageStart($lang['bank-money'], NULL, $validationScript, "pexpenses", "till", $lang['bank-money'], $_SESSION['successMessage'], $_SESSION['errorMessage']);


?>

<div class="actionbox">
<form id="registerForm" action="" method="POST">
<span class="fakelabel">Socio:</span>
<select name="userSelect">
  <option value="">Elegir</option>
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

<span class="fakelabel">Importe:</span><input type="number" lang="nb" name="amount" placeholder="&euro;" class="fourDigit" />
<br />
<span class="fakelabel" style="vertical-align: top;">Comentario:</span><textarea name="comment" placeholder="Comentario..."></textarea>
 <input type="hidden" name="addToTill" value="true" />
 <button class='oneClick' name='oneClick' type="submit">Banquear</button>
 
</form>
</div>

<?php
		// Query to look up past donations
	$selectBanked = "SELECT id, time, amount, userid, comment FROM banked ORDER by time DESC";

	$result = mysql_query($selectBanked)
		or handleError($lang['error-donationload'],"Error loading expense from db: " . mysql_error());
		
?>
<br /><br />
<h3><?php echo $lang['history']; ?></h3>
	 <table class="default">
	  <thead>
	   <tr>
	    <th><?php echo $lang['global-time']; ?></th>
	    <th><?php echo $lang['global-member']; ?></th>
	    <th><?php echo $lang['global-amount']; ?></th>
	    <th></th>
	    <th></th>
	   </tr>
	  </thead>
	  <tbody>
	  
	  <?php

while ($banked = mysql_fetch_array($result)) {
	
	$id = $banked['id'];
	$amount = $banked['amount'];
	$operator = getOperator($banked['userid']);
	$bankedTime = date("d M H:i", strtotime($banked['time'] . "+$offsetSec seconds"));
	
	if ($banked['comment'] != '') {
		
		$commentRead = "
		                <a href='#'><img src='images/comments.png' id='comment$id' /></a><div id='helpBox$id' class='helpBox'>{$banked['comment']}</div>
		                <script>
		                  	$('#comment$id').on({
						 		'mouseover' : function() {
								 	$('#helpBox$id').css('display', 'block');
						  		},
						  		'mouseout' : function() {
								 	$('#helpBox$id').css('display', 'none');
							  	}
						  	});
						</script>
		                ";
		
	} else {
		
		$commentRead = "";
		
	}
	
		
	$banked_row =	sprintf("
  	  <tr>
  	   <td>%s</td>
  	   <td>%s</td>
  	   <td class='right'>%0.02f &euro;</td>
  	   <td style='text-align: center; position: relative;'>$commentRead</td>
  	   <td style='text-align: center;'><a href='javascript:delete_banked(%d)'><img src='images/delete.png' height='15' /></a></td>
	  </tr>",
	  $bankedTime, $operator, $amount, $id
	  );
	  echo $banked_row;
  }
?>

	 </tbody>
	 </table>
	 
	 
   
<?php displayFooter();

