<?php
	
	require_once 'cOnfig/connection.php';
	require_once 'cOnfig/view.php';
	require_once 'cOnfig/authenticate.php';
	require_once 'cOnfig/languages/common.php';
	
	session_start();
	$accessLevel = '3';
	
	// Authenticate & authorize
	authorizeUser($accessLevel);
	
	// Query to look up categories
	$selectCats = "SELECT id, first_name, last_name, dni, reason, time FROM rejected";
		try
		{
			$resultP = $pdo3->prepare("$selectCats");
			$resultP->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		
	
	$deleteCategoryScript = <<<EOD
function delete_provider(rejectid) {
	
		if (confirm("Esta seguro que quieres borrar este proveedor?  No se puede volver a esta pagina despues!")) {
			window.location = "uTil/delete-reject.php?rejectid=" + rejectid;
		}
		
}
EOD;
	pageStart($lang['reject-list'], NULL, $deleteCategoryScript, "pproducts", "admin", $lang['reject-list'], $_SESSION['successMessage'], $_SESSION['errorMessage']);
	
	
?>

<center><a href="new-reject.php" class="cta"><?php echo $lang['global-add']; ?></a></center>

	 <table class="default">
	  <thead>
	   <tr>
	    <th><?php echo $lang['pur-date']; ?></th>
	    <th><?php echo $lang['member-firstname']; ?></th>
	    <th><?php echo $lang['member-lastname']; ?></th>
	    <th>DNI</th>
	    <th></th>
	    <th></th>
	   </tr>
	  </thead>
	  <tbody>
	  
	  <?php

		while ($category = $resultP->fetch()) {
			
			
			$id = $category['id'];
			$first_name = $category['first_name'];
			$last_name = $category['last_name'];
			$dni = $category['dni'];
			$reason = $category['reason'];
			$time = date("d-m-Y H:i", strtotime($category['time']));
			
	if ($reason != '') {
		
		$commentRead = "
		                <img src='images/comments.png' id='comment$id' /><div id='helpBox$id' class='helpBox'>{$reason}</div>
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
	
	$deleteOrNot = "<td class='noExl' style='text-align: center;'><a href='javascript:delete_provider($id)'><img src='images/delete.png' height='15' /></a></td>";

	
	$flower_row =	sprintf("
  	  <tr>
  	   <td>%s</td>
  	   <td>%s</td>
  	   <td>%s</td>
  	   <td>%s</td>
  	   <td style='text-align: center;'>%s</td>
  	   %s
	  </tr>",
	  $time, $first_name, $last_name, $dni, $commentRead, $deleteOrNot
	  );
	  echo $flower_row;
  }
?>



	 </tbody>
	 </table>

<?php  displayFooter(); ?>
