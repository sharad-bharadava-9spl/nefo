<?php
	
	require_once 'cOnfig/connection.php';
	require_once 'cOnfig/view.php';
	require_once 'cOnfig/authenticate.php';
	require_once 'cOnfig/languages/common.php';
	
	session_start();
	$accessLevel = '1';
	
	// Authenticate & authorize
	authorizeUser($accessLevel);
 
	// Did this page re-submit with a form? If so, check & store details
	if (isset($_POST['mailRecipients'])) {
		
		// Query to look up emails
		$dropEmails = "DELETE FROM cuotas;";
		try
		{
			$result = $pdo3->prepare("$dropEmails")->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
		$dropEmails = "ALTER TABLE cuotas AUTO_INCREMENT=1;";
		try
		{
			$result = $pdo3->prepare("$dropEmails")->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}

		foreach($_POST['mailRecipients'] as $mailRecipients) {
			
			if ($mailRecipients['id'] == '') {
				$id = '';
			} else {
				$id = $mailRecipients['id'];
			}
			
			$cuota = str_replace('%', '&#37;', $mailRecipients['cuota']);
			$days = str_replace('%', '&#37;', $mailRecipients['days']);
			$name = str_replace('%', '&#37;', $mailRecipients['name']);
			
			if ($cuota != '') {
				
			if ($mailRecipients['id'] == '') {
				
				// Query to insert e-mail
				$insertEmail = "INSERT INTO cuotas (name, cuota, days) VALUES ('$name', '$cuota', '$days')";
				
			} else {
				
				// Query to insert e-mail
				$insertEmail = "INSERT INTO cuotas (name, cuota, days, id) VALUES ('$name', '$cuota', '$days', '$id')";
				
			}

				
				try
				{
					$result = $pdo3->prepare("$insertEmail")->execute();
				}
				catch (PDOException $e)
				{
						$error = 'Error fetching user: ' . $e->getMessage();
						echo $error;
						exit();
				}
				
			}
			
		}
		
/*		$_SESSION['successMessage'] = $lang['memberfees-updated'];
		header("Location: sys-settings.php");
		exit();*/
			$response_msg  = array("successMessage" => $lang['memberfees-updated']);
			echo json_encode($response_msg);
			die;
		
	}
			
	
	
	// Query to look up emails
	$selectEmails = "SELECT id, name, cuota, days FROM cuotas";
		try
		{
			$results = $pdo3->prepare("$selectEmails");
			$results->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		
  	    
	$deleteEmailScript = <<<EOD
	
	  $('#registerForm1').validate({
		  rules: {

			  days1: {
				  required: function () {
                	return $('#name1').val().length > 0;
            	  }
			  }
    	}, // end rules
    	errorPlacement: function(error, element) { }
	  }); // end validate

  
function delete_cuota(cuotaid) {
	if (confirm("")) {
				window.location = "uTil/delete-cuota.php?cuotaid=" + cuotaid;
				}
}
EOD;

if(!isset($_POST['name']) || $_POST['name'] != 'member_email'){
  	pageStart($lang['memberfees'], NULL, $deleteEmailScript, "pexpenses", "admin", $lang['memberfees'], $_SESSION['successMessage'], $_SESSION['errorMessage']);
  }
	
	

$member_data =  "<style>
::-webkit-input-placeholder { /* Chrome/Opera/Safari */
  font-style: italic;
}
::-moz-placeholder { /* Firefox 19+ */
  font-style: italic;
}
:-ms-input-placeholder { /* IE 10+ */
  font-style: italic;
}
:-moz-placeholder { /* Firefox 18- */
  font-style: italic;
}

  	</style><form id='registerForm1' action='".$_SERVER['PHP_SELF']."' method='POST' >";
$member_data .= "<table class='defaultalternate nonhover'>";
	$member_data .=    "<thead>";
	$member_data .=   "<tr>";
	$member_data .=    "<th>{$lang['global-name']}</th>";
	$member_data .=    "<th>{$lang['global-amount']}</th>";
	$member_data .=    "<th>{$lang['period-in-days']}</th>";
	 $member_data .=   "<th></th>";
	 $member_data .=  "</tr>";
	$member_data .=  "</thead>";
$member_data .=	  "<tbody>";

	$i = 1;

		while ($emailRes = $results->fetch()) {

		$id = $emailRes['id'];
		$name = $emailRes['name'];
		$cuota = $emailRes['cuota'];
		$days = $emailRes['days'];


	$member_data .="<tr>";
	$member_data .="<td class='left'><input type='text' name='mailRecipients[$i][name]' class='eightDigit defaultinput-no-margin' value='$name' /></td>";
	$member_data .="<td class='left'><input type='number' name='mailRecipients[$i][cuota]' class='twoDigit defaultinput-no-margin' value='$cuota' /></td>";
	$member_data .="<td class='left'><input type='number' name='mailRecipients[$i][days]' class='twoDigit defaultinput-no-margin' value='$days' />";
	$member_data .="<input type='hidden' name='mailRecipients[$i][id]' value='$id' /></td>";
	$member_data .="<td><a href='javascript:delete_cuota($id)'><img src='images/delete.png' height='15' /></a></td></tr>";
	$i++;
	}

	 $member_data .="<tr>";
	  $member_data .= "<td class='left'><input type='text' id='name$i' name='mailRecipients[$i][name]' class='eightDigit defaultinput-no-margin' placeholder='{$lang['example-one-month']}' /></td>";
	$member_data .="<td class='left'><input type='number' id='cuota$i' name='mailRecipients[$i][cuota]' class='twoDigit defaultinput-no-margin abc' placeholder='{$_SESSION['currencyoperator']}' /></td>";
	$member_data .="<td class='left'><input type='number' id='days$i' name='mailRecipients[$i][days]' class='twoDigit defaultinput-no-margin' placeholder='#' />";
/* $member_data .= "<script>";

	    
$member_data .=	 "$('#name$i').rules('add', {
				  required: function () {
		                	return $('#cuota$i').val().length > 0;
		            	  }
				});";
	$member_data .=	 "$('#days$i').rules('add', {
		  required: function () {
                	return $('#cuota$i').val().length > 0;
            	  }
		});";
		$member_data .=	 "$('#cuota$i').rules('add', {
		  required: function () {
                	return $('#days$i').val().length > 0;
            	  }
		});";

 $member_data .= "</script>";*/
 $member_data .= "</td>";
 $member_data .= "<td></td>";
 $member_data .=  "</tr>";
	   

	$i++;


	$member_data .= "<tr>";
	$member_data .=     "<td class='left'><input type='text' id='name$i' name='mailRecipients[$i][name]' class='eightDigit defaultinput-no-margin' placeholder='{$lang['example-30-days']}' /></td>";
	$member_data .=     "<td class='left'><input type='number' id='cuota$i' name='mailRecipients[$i][cuota]' class='twoDigit defaultinput-no-margin' placeholder='{$_SESSION['currencyoperator']}' /></td>";
	  $member_data .=   "<td class='left'><input type='number' id='days$i' name='mailRecipients[$i][days]' class='twoDigit defaultinput-no-margin' placeholder='#'' />";
 $member_data .=  "<td></td>";
	 $member_data .=    "</tr>";

	$i++;


	$member_data .= "<tr>";
	  $member_data .=  "<td class='left'><input type='text' id='name$i' name='mailRecipients[$i][name]' class='eightDigit defaultinput-no-margin' placeholder='{$lang["example-quarter"]}'' /></td>";
	   $member_data .=    "<td class='left'><input type='number' id='uota$i' name='mailRecipients[$i][cuota]' class='twoDigit defaultinput-no-margin' placeholder='{$_SESSION['currencyoperator']}' /></td>";
	    $member_data .=   "<td class='left'><input type='number' id='days$i' name='mailRecipients[$i][days]' class='twoDigit defaultinput-no-margin' placeholder='#' />";
    $member_data .=  "<td></td>";
	  $member_data .=    "</tr>";


	$i++;


	$member_data .="<tr>";
	 $member_data .=   "<td class='left'><input type='text' id='name$i' name='mailRecipients[$i][name]' class='eightDigit defaultinput-no-margin' placeholder='{$lang['example-semester']}' /></td>";
	 $member_data .=  "<td class='left'><input type='number' id='cuota$i' name='mailRecipients[$i][cuota]' class='twoDigit defaultinput-no-margin' placeholder='{$_SESSION['currencyoperator']}' /></td>";
	 $member_data .=    "<td class='left'><input type='number' id='days$i' name='mailRecipients[$i][days]' class='twoDigit defaultinput-no-margin' placeholder='#' />";
 $member_data .=  "<td></td>";	
 $member_data .= 	"</tr>";


	$i++;
	
	$member_data .="<tr>";
	 $member_data .=   "<td class='left'><input type='text' id='name$i' name='mailRecipients[$i][name]' class='eightDigit defaultinput-no-margin' placeholder='{$lang['example-yearly']}' /></td>";
	 $member_data .=  "<td class='left'><input type='number' id='cuota$i' name='mailRecipients[$i][cuota]' class='twoDigit defaultinput-no-margin' placeholder='{$_SESSION['currencyoperator']}' /></td>";
	 $member_data .=    "<td class='left'><input type='number' id='days$i' name='mailRecipients[$i][days]' class='twoDigit defaultinput-no-margin' placeholder='#' />";
 $member_data .=  "<td></td>";	
 $member_data .= 	"</tr>";
	  


	$i++;

	$member_data .="</tbody>
	 </table>
	 <br />
     <center><button class='cta1' name='oneClick' type='submit'>{$lang['global-savechanges']}</button>
</form>";


$response = array();
if(isset($_POST['name']) && $_POST['name'] == 'member_email'){
	$response  = array("member_data" => $member_data, 'delete_scripts' => $deleteEmailScript);
	echo json_encode($response);
	die;
}else{
	
	echo $member_data;
	displayFooter();
}

?>

