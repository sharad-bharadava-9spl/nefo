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
	$selectCats = "SELECT id, registered, name, comment, providernumber, credit from providers ORDER by providernumber ASC";

	$resultCats = mysql_query($selectCats)
		or handleError($lang['error-loadflowers'],"Error loading flower from db: " . mysql_error());
		
	
	$deleteCategoryScript = <<<EOD
function delete_provider(providerid) {
	
		if (confirm("Esta seguro que quieres borrar este proveedor?  No se puede volver a esta pagina despues!")) {
			window.location = "uTil/delete-provider.php?providerid=" + providerid;
		}
		
}
EOD;
	pageStart($lang['providers'], NULL, $deleteCategoryScript, "pproducts", "admin", $lang['providers'], $_SESSION['successMessage'], $_SESSION['errorMessage']);
	
	
?>

<center><a href="new-provider.php" class="cta"><?php echo $lang['new-provider']; ?></a></center>

	 <table class="default">
	  <thead>
	   <tr>
	    <th>#</th>
	    <th><?php echo $lang['global-name']; ?></th>
	    <th>Saldo</th>
	    <th></th>
	    <th></th>
	   </tr>
	  </thead>
	  <tbody>
	  
	  <?php

while ($category = mysql_fetch_array($resultCats)) {	
	
	$categoryid = $category['id'];
	
	if ($category['comment'] != '') {
		
		$commentRead = "
		                <img src='images/comments.png' id='comment$categoryid' /><div id='helpBox$categoryid' class='helpBox'>{$category['comment']}</div>
		                <script>
		                  	$('#comment$categoryid').on({
						 		'mouseover' : function() {
								 	$('#helpBox$categoryid').css('display', 'block');
						  		},
						  		'mouseout' : function() {
								 	$('#helpBox$categoryid').css('display', 'none');
							  	}
						  	});
						</script>
		                ";
		
	} else {
		
		$commentRead = "";
		
	}

	
	$flower_row =	sprintf("
  	  <tr>
  	   <td class='clickableRow' href='provider.php?providerid=%d'>%03d</td>
  	   <td class='clickableRow' href='provider.php?providerid=%d'>%s</td>
  	   <td class='clickableRow right' href='provider.php?providerid=%d'>%0.02f €</td>
	   <td class='relative'>$commentRead</td>
  	   <td style='text-align: center;'><a href='javascript:delete_provider(%d)'><img src='images/delete.png' height='15' title='Delete category' /></a></td>
	  </tr>",
	  $categoryid, $category['providernumber'], $categoryid, $category['name'], $categoryid, $category['credit'], $categoryid
	  );
	  echo $flower_row;
  }
?>



	 </tbody>
	 </table>

<?php  displayFooter(); ?>
