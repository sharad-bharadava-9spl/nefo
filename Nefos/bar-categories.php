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
	$selectCats = "SELECT id, time, name, description from b_categories WHERE id > 48 OR (id = 35 OR id = 34 OR id = 30 OR id = 19) ORDER by name ASC";
		try
		{
			$results = $pdo3->prepare("$selectCats");
			$results->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		
	
	$deleteFlowerScript = <<<EOD
function delete_category(categoryid,noOfCats) {
	
	if (noOfCats != 0) {
		
		if (confirm("This category has " + noOfCats + " products!! Are you sure you want to delete it?")) {
			window.location = "uTil/bar-delete-category.php?categoryid=" + categoryid;
		}
		
	} else {
		
		if (confirm("Esta seguro que quieres borrar este categoria?  No se puede volver a esta pagina despues!")) {
			window.location = "uTil/bar-delete-category.php?categoryid=" + categoryid;
		}
	}
}
EOD;
	pageStart('Bar categories', NULL, $deleteFlowerScript, "pproducts", "admin", 'BAR CATEGORIES', $_SESSION['successMessage'], $_SESSION['errorMessage']);
	
	
?>

<center><a href="bar-new-category.php" class="cta"><?php echo $lang['new-category']; ?></a></center>

	 <table class="default">
	  <thead>
	   <tr>
	    <th><?php echo $lang['global-name']; ?></th>
	    <th># of products</th>
	    <th></th>
	    <th><?php echo $lang['global-actions']; ?></th>
	   </tr>
	  </thead>
	  <tbody>
	  
	  <?php

			while ($category = $results->fetch()) {

	$categoryid = $category['id'];
	
	if ($category['description'] != '') {
		
		$commentRead = "
		                <img src='images/description.png' id='comment$categoryid' /><div id='helpBox$categoryid' class='helpBox'>{$category['description']}</div>
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

	
	// Query to look up products
	$selectServices = "SELECT COUNT(productid) from b_products WHERE category = $categoryid";
		try
		{
			$result = $pdo3->prepare("$selectServices");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
		$noOfCats = $row['COUNT(productid)'];
			
	$flower_row =	sprintf("
  	  <tr>
  	   <td class='clickableRow' href='bar-edit-category.php?categoryid=%d'>%s</td>
  	   <td class='clickableRow' href='bar-edit-category.php?categoryid=%d' style='text-align: center;'>%d</td>
	   <td class='relative'>$commentRead</td>
  	   <td style='text-align: center;'><a href='javascript:delete_category(%d,%d)'><img src='images/delete.png' height='15' title='Delete category' /></a></td>
	  </tr>",
	  $category['id'], $category['name'], $category['id'], $noOfCats, $category['id'], $noOfCats
	  );
	  echo $flower_row;
  }
?>



	 </tbody>
	 </table>

<?php  displayFooter(); ?>
