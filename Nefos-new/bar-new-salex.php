<?php
	
	require_once 'cOnfig/connection.php';
	require_once 'cOnfig/view.php';
	require_once 'cOnfig/authenticate.php';
	require_once 'cOnfig/languages/common.php';
	
	session_start();
	$accessLevel = '3';
	
	// Authenticate & authorize
	authorizeUser($accessLevel);
	

	
	if (isset($_POST['searchfield'])) {
		
		$phrase = $_POST['searchfield'];
	
		$selectUsers = "SELECT id, registeredSince, Brand, number, longName, shortName, cif, street, streetnumber, flat, postcode, city, state, country, website, email, facebook, twitter, instagram, googleplus, status, type, lawyer, URL, source, billingType FROM customers WHERE shortName LIKE ('%$phrase%') OR longName LIKE ('%$phrase%') ORDER by number ASC";
		try
		{
			$results = $pdo3->prepare("$selectUsers");
			$results->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
			
	$memberScript = <<<EOD
	
	    $(document).ready(function() {
		    
			$.tablesorter.addParser({
			  id: 'dates',
			  is: function(s) { return false },
			  format: function(s) {
			    var dateArray = s.split('-');
			    return dateArray[2].substring(0,4) + dateArray[1] + dateArray[0];
			  },
			  type: 'numeric'
			});
			
		});
EOD;

		pageStart("NEW HW SALE", NULL, $memberScript, "pmembership", NULL, "NEW HW SALE", $_SESSION['successMessage'], $_SESSION['errorMessage']);
		
?>		
		
	 <table class='default' id='mainTable'>
	  <thead>	
	   <tr style='cursor: pointer;'>
	    <th>#</th>
	    <th><?php echo $lang['global-name']; ?></th>
	   </tr>
	  </thead>
	  <tbody>
	  
	  <?php

while ($user = $results->fetch()) {

	
	
	echo sprintf("
  	  <tr>
  	   <td class='clickableRow' href='new-dispense.php?user_id=%d'>%s</td>
  	   <td class='clickableRow' href='new-dispense.php?user_id=%d'>%s</td>",
	  $user['id'], $user['number'], $user['id'], $user['shortName']);
	  
  }
?>

	 </tbody>
	 </table>		
	
<?php

	} else {
	
	$memberScript = <<<EOD
	
	    $(document).ready(function() {
		    
	  $('#searchForm').validate({
		  rules: {
			  searchfield: {
				  required: true
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
		    
});			
EOD;

		pageStart("NEW HW SALE", NULL, $memberScript, "psales", "dispensepre", "NEW HW SALE", $_SESSION['successMessage'], $_SESSION['errorMessage']);
		

?>


<center>

<form id="registerForm" action="new-dispense.php" method="POST">
 <div id="overview">
   
  <select class="fakeInput" name="userSelect">
  <option value=""><?php echo $lang['expense-choosemember']; ?></option>
<?php
      	// Query to look up pre-registered users:
		$userDetails = "SELECT id, number, shortName FROM customers ORDER BY number ASC";
		try
		{
			$results = $pdo3->prepare("$userDetails");
			$results->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		while ($user = $results->fetch()) {
			
				$user_row = sprintf("<option value='%d'>#%s - %s</option>",
	  								 $user['id'], $user['number'], $user['shortName']);
	  			echo $user_row;
  		}
?>
</select>
	<br /><br />
 <button type="submit"><?php echo $lang['global-select']; ?></button>
 



</div> <!-- END OVERVIEW -->
</form>
<br />
<form id="searchForm" action="" method="POST">
 <div id="overview">

<input type="text" name="searchfield" maxlength="10" autofocus placeholder="Buscar nombre" /><br /><br />

 <button type="submit"><?php echo $lang['global-select']; ?></button>

</div> <!-- END OVERVIEW -->
 </form>

 
</center>
<br />

<?php } displayFooter(); ?>


<!-- When script submits, check to see if password+salt matches pw+salt in db. If yes, leave. If no, change. Hepp! 
Conversely: Leave Password out of the form, and replace with a link 'change password' -->
