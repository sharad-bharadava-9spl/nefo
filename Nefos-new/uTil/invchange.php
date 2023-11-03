<?php

	require_once '../cOnfig/connection.php';
	require_once '../cOnfig/view.php';
	require_once '../cOnfig/authenticate.php';
	require_once '../cOnfig/languages/common.php';

	session_start();
	$accessLevel = '3';
	
	// Authenticate & authorize
	authorizeUser($accessLevel);
	
	$inMenu = $_GET['menu'];
	$saleid = $_GET['saleid'];
	
	if ($inMenu != 'No') {
		
		$changeMenu = "UPDATE sales SET invoiced = 0, delivereddate = NULL WHERE saleid = $saleid";
		
		try
		{
			$result = $pdo3->prepare("$changeMenu")->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
			
		
		header("Location: ../orders.php");
		exit();
		
	} else {
		
		if ($_POST['confirmed'] == 'yes') {
			
			$delivereddate = $_POST['year'] . "-" . $_POST['month'] . "-" . $_POST['day'];
			
			$changeMenu = "UPDATE sales SET invoiced = 1, delivereddate = '$delivereddate' WHERE saleid = $saleid";
			
			try
			{
				$result = $pdo3->prepare("$changeMenu")->execute();
			}
			catch (PDOException $e)
			{
					$error = 'Error fetching user: ' . $e->getMessage();
					echo $error;
					exit();
			}
				
			
			header("Location: ../orders.php");
			exit();
			
		}
		
	$validationScript = <<<EOD
	
    $(document).ready(function() {
	   
	    
	    	    
	  $('#registerForm').validate({
		  ignore:'', //because the radio buttons are hidden, validation ignores them. This way it'll work.
		  rules: {
			  day: {
				  required: true,
				  range:[0,31]
			  },
			  month: {
				  required: true,
				  range:[0,12]
			  },
			  year: {
				  required: true,
				  range:[0,2025]
			  }
    	},
		  errorPlacement: function(error, element) {
			  
			  if (element.attr("name") == "expenseCat") {
        		error.appendTo($('#categoryLink'));
    		  } else if ( element.is(":radio") || element.is(":checkbox")){
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

		pageStart("Delivery", NULL, $validationScript, "psales", "sales admin", "Delivery", $_SESSION['successMessage'], $_SESSION['errorMessage']);
?>

<center>
<div id='donationholder2'>
 <form id="registerForm" action="" method="POST">

 <h4>When was the product delivered?</h4>
 <br />
 <input type="number" lang="nb" name="day" id="day" class="twoDigit" maxlength="2" placeholder="dd" />
 <input type="number" lang="nb" name="month" id="month" class="twoDigit" maxlength="2" placeholder="mm" />
 <input type="number" lang="nb" name="year" id="year" class="fourDigit" maxlength="4" placeholder="yyyy" />
  <input type='hidden' name='confirmed' value='yes' /><br /><br />

  <button class='oneClick okbutton2' name='oneClick' type="submit" style='margin-left: -2px; width: 286px;'><?php echo $lang['global-confirm']; ?></button></td>

 </form>
</div>
</center>

<?php
		
	}