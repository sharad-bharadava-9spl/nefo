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
		
		// Query to delete current stations
		$dropEmails = "TRUNCATE workstations";
	
		$result = mysql_query($dropEmails)
			or handleError($lang['error-expensesload'],"Error loading expense from db: " . mysql_error());
			
		foreach($_POST['mailRecipients'] as $mailRecipients) {
			
			$name = mysql_real_escape_string(str_replace('%', '&#37;', $mailRecipients['name']));
			$reception = $mailRecipients['reception'];
			$dispensary = $mailRecipients['dispensary'];
			$bar = $mailRecipients['bar'];
			
			if ($name != '') {
				
				// Query to insert workstation
				$insertEmail = "INSERT INTO workstations (name, reception, dispensary, bar) VALUES ('$name', '$reception', '$dispensary', '$bar')";
				
				$result = mysql_query($insertEmail)
					or handleError($lang['error-expensesload'],"Error loading expense from db: " . mysql_error());
				
			}
			
		}

		
		$_SESSION['successMessage'] = "Puestos de trabajo actualizado con &eacute;xito!";
		header("Location: sys-settings.php");
		exit();
		
	}
			
	// Query to look up workstations
	$selectEmails = "SELECT id, name, dispensary, bar, reception FROM workstations";

	$result = mysql_query($selectEmails)
		or handleError($lang['error-expensesload'],"Error loading expense from db: " . mysql_error());
		
  	    
	$deleteEmailScript = <<<EOD
	
    $(document).ready(function() {
	    	    
	  $('#registerForm').validate({
		  rules: {

			  days1: {
				  required: function () {
                	return $('#name1').val().length > 0;
            	  }
			  }
    	}, // end rules
    	errorPlacement: function(error, element) { }
	  }); // end validate
  }); // end ready
  
function delete_cuota(cuotaid) {
	if (confirm("")) {
				window.location = "uTil/delete-cuota.php?cuotaid=" + cuotaid;
				}
}
EOD;

  	pageStart("PUESTOS DE TRABAJO", NULL, $deleteEmailScript, "pexpenses", "admin", "PUESTOS DE TRABAJO", $_SESSION['successMessage'], $_SESSION['errorMessage']);
	
	
?>
<form id="registerForm" action="" method="POST" >
	 <table class="default nonhover">
	  <thead>
	   <tr>
	    <th><?php echo $lang['global-name']; ?></th>
	    <th>Acceso</th>
	    <th></th>
	   </tr>
	  </thead>
	  <tbody>
	  
<?php

	$i = 1;

	while ($emailRes = mysql_fetch_array($result)) {

		$id = $emailRes['id'];
		$name = $emailRes['name'];
		$bar = $emailRes['bar'];
		$dispensary = $emailRes['dispensary'];
		$reception = $emailRes['reception'];

echo <<<EOD
	   <tr>
	    <td class='left'><input type="text" id="name$i" name="mailRecipients[$i][name]" value="$name" class="eightDigit" /></td>
	    <td class='left'>
EOD;
?>
    <input type="checkbox" name="mailRecipients[<?php echo $i; ?>][reception]" value="1" style="width: 12px;" <?php if ($reception == 1) {echo "checked";} if ($_SESSION['userGroup'] != 1) { echo " disabled"; } ?>>Recepci&oacute;n (Inscripci&oacute;n, aportaciones, pagos de cuota)</input><br />
    <input type="checkbox" name="mailRecipients[<?php echo $i; ?>][dispensary]" value="1" style="width: 12px;" <?php if ($dispensary == 1) {echo "checked";} if ($_SESSION['userGroup'] != 1) { echo " disabled"; } ?>>Dispensario (Dispensar, menu, gestionar productos)</input><br />
    <input type="checkbox" name="mailRecipients[<?php echo $i; ?>][bar]" value="1" style="width: 12px;" <?php if ($bar == 1) {echo "checked";} if ($_SESSION['userGroup'] != 1) { echo " disabled"; } ?>>Bar (Vender en el bar, gestionar productos del bar)<br />
</td>
	   </tr>
<?php 	$i++;
	}
	
	
echo <<<EOD
	   <tr>
	    <td class='left'><input type="text" id="name$i" name="mailRecipients[$i][name]" class="eightDigit" /></td>
	    <td class='left'>
    <input type="checkbox" name="mailRecipients[$i][reception]" value="1" style="width: 12px;">Recepci&oacute;n (Inscripci&oacute;n, aportaciones, pagos de cuota)</input><br />
    <input type="checkbox" name="mailRecipients[$i][dispensary]" value="1" style="width: 12px;">Dispensario (Dispensar, menu, gestionar productos)</input><br />
    <input type="checkbox" name="mailRecipients[$i][bar]" value="1" style="width: 12px;">Bar (Vender en el bar, gestionar productos del bar)<br />
</td>
	   </tr>
EOD;

$i++;
	
	
echo <<<EOD
	   <tr>
	    <td class='left'><input type="text" id="name$i" name="mailRecipients[$i][name]" class="eightDigit" /></td>
	    <td class='left'>
    <input type="checkbox" name="mailRecipients[$i][reception]" value="1" style="width: 12px;">Recepci&oacute;n (Inscripci&oacute;n, aportaciones, pagos de cuota)</input><br />
    <input type="checkbox" name="mailRecipients[$i][dispensary]" value="1" style="width: 12px;">Dispensario (Dispensar, menu, gestionar productos)</input><br />
    <input type="checkbox" name="mailRecipients[$i][bar]" value="1" style="width: 12px;">Bar (Vender en el bar, gestionar productos del bar)<br />
</td>
	   </tr>
EOD;

$i++;
	
	
echo <<<EOD
	   <tr>
	    <td class='left'><input type="text" id="name$i" name="mailRecipients[$i][name]" class="eightDigit" /></td>
	    <td class='left'>
    <input type="checkbox" name="mailRecipients[$i][reception]" value="1" style="width: 12px;">Recepci&oacute;n (Inscripci&oacute;n, aportaciones, pagos de cuota)</input><br />
    <input type="checkbox" name="mailRecipients[$i][dispensary]" value="1" style="width: 12px;">Dispensario (Dispensar, menu, gestionar productos)</input><br />
    <input type="checkbox" name="mailRecipients[$i][bar]" value="1" style="width: 12px;">Bar (Vender en el bar, gestionar productos del bar)<br />
</td>
	   </tr>
EOD;
?>
	  </tbody>
	 </table>
	 <br />
     <button class='oneClick' name='oneClick' type="submit"><?php echo $lang['global-savechanges']; ?></button>

</form>
	 
	 
<?php displayFooter(); ?>
