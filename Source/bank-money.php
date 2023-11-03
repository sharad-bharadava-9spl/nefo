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
		$comment = str_replace("'","\'",str_replace('%', '&#37;', trim($_POST['comment'])));
		$registertime = date('Y-m-d H:i:s'); // 	$purchaseDate = date('Y-m-d H:i:s'); ????

		// Query to add to banked table
		  $query = sprintf("INSERT INTO banked (time, amount, userid, comment) VALUES ('%s', '%f', '%d', '%s');",
		  $registertime, $amount, $userid, $comment);
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
			$_SESSION['successMessage'] = $lang['banked-money'];
			header("Location: admin.php");
			exit();
		}
	/***** FORM SUBMIT END *****/
	
	
	// Check if new Filter value was submitted, and assign query variable accordingly
	if (isset($_POST['filter'])) {
				
		$filterVar = $_POST['filter'];
		
		if ($filterVar == 100) {
			
			$limitVar = "LIMIT 100";
			$optionList = "<option value='$filterVar'>{$lang['last']} 100</option>
			<option value='250'>{$lang['last']} 250</option>
			<option value='500'>{$lang['last']} 500</option>";
			
		} else if ($filterVar == 250) {
			
			$limitVar = "LIMIT 250";
			$optionList = "<option value='$filterVar'>{$lang['last']} 250</option>
			<option value='100'>{$lang['last']} 100</option>
			<option value='500'>{$lang['last']} 500</option>";
			
		} else if ($filterVar == 500) {
			
			$limitVar = "LIMIT 500";
			$optionList = "<option value='$filterVar'>{$lang['last']} 500</option>
			<option value='100'>{$lang['last']} 100</option>
			<option value='250'>{$lang['last']} 250</option>";
			
		} else {
			
			// Grab month and year number
			$month = substr($filterVar, 0, strrpos($filterVar, '-'));	
			$year = substr($filterVar, strrpos($filterVar, '-') + 1);
			
			$timeLimit = "WHERE MONTH(time) = $month AND YEAR(time) = $year";
			$timeLimit2 = "AND MONTH(closingtime) = $month AND YEAR(closingtime) = $year";
			
			$optionList = "<option value='filterVar'>$filterVar</option>
				<option value='100'>{$lang['last']} 100</option>
				<option value='250'>{$lang['last']} 250</option>
				<option value='500'>{$lang['last']} 500</option>";	
					
		}
		

			
	} else {
		
		$limitVar = "LIMIT 100";
		
		$optionList = "<option value='100'>{$lang['last']} 100</option>
			<option value='250'>{$lang['last']} 250</option>
			<option value='500'>{$lang['last']} 500</option>";		
	}
	
	// Check if 'entre fechas' was utilised
	if (isset($_POST['untilDate'])) {
		
		$limitVar = "";
		
		$fromDate = date("Y-m-d", strtotime($_POST['fromDate']));
		$untilDate = date("Y-m-d", strtotime($_POST['untilDate']));
		
		$timeLimit = "WHERE DATE(time) BETWEEN DATE('$fromDate') AND DATE('$untilDate')";
		$timeLimit2 = "AND DATE(closingtime) BETWEEN DATE('$fromDate') AND DATE('$untilDate')";
		$limitVar = "";			
			
	}

	
	$validationScript = <<<EOD
	
	  $( function() {
	    $( "#datepicker" ).datepicker({
			dateFormat: "dd-mm-yy"
	    });
	  });
	  $( function() {
	    $( "#datepicker2" ).datepicker({
			dateFormat: "dd-mm-yy"
	    });
	  });	    

    $(document).ready(function() {
	    	    
$("#xllink").click(function(){

	  $("#mainTable").table2excel({
	    // exclude CSS class
	    exclude: ".noExl",
	    name: "Banqueado",
	    filename: "Banqueado" //do not include extension

	  });

	});

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
<select name="userSelect" style='margin-left: -1px;'>
  <option value="">Elegir</option>
<?php
      	// Query to look up pre-registered users:
		$userDetails = "SELECT user_id, memberno, first_name, last_name FROM users WHERE userGroup < 4 ORDER BY memberno ASC";
		try
		{
			$result = $pdo3->prepare("$userDetails");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		while ($user = $result->fetch()) {
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

	 <table class='default' id='cloneTable' style='text-align: left;'>
      <tr class='nonhover'>
       <td colspan='13' style='border-bottom: 0;'>
         <a href="#" id="xllink" onClick="$('#mainTable').tableExport({type:'excel',escape:'false'});"><img src="images/excel.png" style='margin: 0 0 -5px 8px;'/></a><br /><br />
		<div style='display: inline-block; border: 2px solid #5aa242; padding: 10px;'>
		&nbsp;<strong>Filtrar lista:</strong><br /> 
        <form action='' method='POST' style='margin-top: 3px;'>
	     <select id='filter' name='filter' onchange='this.form.submit()'>
	      <?php echo $optionList; ?>
		 </select>
        </form>
        <form action='' method='POST'>
<?php
	if (isset($_POST['fromDate'])) {
		
		echo <<<EOD
		 <input type="text" id="datepicker" name="fromDate" autocomplete="nope" class="sixDigit" value="{$_POST['fromDate']}" />
		 <input type="text" id="datepicker2" name="untilDate" autocomplete="nope" class="sixDigit" value="{$_POST['untilDate']}" onchange='this.form.submit()' />
		 <button type="submit" style='display: inline-block; width: 40px; height: 27px;'>OK</button>
EOD;
		
	} else {
		
		echo <<<EOD
		 <input type="text" id="datepicker" name="fromDate" autocomplete="nope" class="sixDigit" placeholder="{$lang['from-date']}" />
		 <input type="text" id="datepicker2" name="untilDate" autocomplete="nope" class="sixDigit" placeholder="{$lang['to-date']}" onchange='this.form.submit()' />
		 <button type="submit" style='display: inline-block; width: 40px; height: 27px;'>OK</button>
EOD;

	}
?>
        </form>
        </div>
       </td>
      </tr>
     </table>


<?php
		// Query to look up past donations
		$selectBanked = "SELECT 'manual' AS type, id, time, amount, userid, comment FROM banked $timeLimit UNION ALL SELECT 'closeday' AS type, closingid AS id, closingtime AS time, moneytaken AS amount, closedby AS userid, '' AS comment FROM closing WHERE moneytaken > 0 $timeLimit2 UNION ALL SELECT 'closeshift' AS type, closingid AS id, closingtime AS time, moneytaken AS amount, closedby AS userid, '' AS comment FROM shiftclose WHERE moneytaken > 0 $timeLimit2 ORDER by time DESC $limitVar";
	
		try
		{
			$result = $pdo3->prepare("$selectBanked");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		
?>
<br /><br />
<h3><?php echo $lang['history']; ?></h3>
	 <table class="default" id="mainTable">
	  <thead>
	   <tr>
	    <th><?php echo $lang['global-time']; ?></th>
	    <th><?php echo $lang['global-type']; ?></th>
	    <th><?php echo $lang['global-member']; ?></th>
	    <th><?php echo $lang['global-amount']; ?></th>
	    <th class='noExl'></th>
	   </tr>
	  </thead>
	  <tbody>
	  
	  <?php

	while ($banked = $result->fetch()) {
	
	$type = $banked['type'];
	$id = $banked['id'];
	$amount = $banked['amount'];
	$operator = getOperator($banked['userid']);
	$bankedTime = date("d M H:i", strtotime($banked['time'] . "+$offsetSec seconds"));
	
	if ($type == 'manual') {
		$bankType = 'Durante el turno';
		$deleteOrNot = "<a href='javascript:delete_banked($id)'><img src='images/delete.png' height='15' /></a>";
	} else if ($type == 'closeday') {
		$bankType = 'Cierre del dia';
		$deleteOrNot = "";
	} else {
		$bankType = 'Cierre del turno';
		$deleteOrNot = "";
	}
	
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
  	   <td>%s</td>
  	   <td class='right'>%0.02f &euro;</td>
  	   <td style='text-align: center; position: relative;'>$commentRead</td>
  	   <td style='text-align: center;' class='noExl'>$deleteOrNot</td>
	  </tr>",
	  $bankedTime, $bankType, $operator, $amount
	  );
	  echo $banked_row;
  }
?>

	 </tbody>
	 </table>
	 
	 
   
<?php displayFooter();

