<?php
	
	require_once 'cOnfig/connection.php';
	require_once 'cOnfig/view.php';
	require_once 'cOnfig/authenticate.php';
	require_once 'cOnfig/languages/common.php';
	
	session_start();
	$accessLevel = '3';
	
	// Authenticate & authorize
	authorizeUser($accessLevel);
	
	if (isset($_POST['sortsubmit'])) {
		
		foreach($_POST['cat'] as $sale) {
			
			$sortorder = $sale['sortorder'];
			$categoryid = $sale['categoryid'];
			
			if ($sortorder == 0) {
				
				$sortorder = 9999;
				
			}
			
			$query = "UPDATE b_categories SET sortorder = '$sortorder' WHERE id = $categoryid";
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
		}
	}
	
	// Query to look up categories
	$selectCats = "SELECT id, time, name, description, sortorder from b_categories ORDER by sortorder ASC, name ASC";
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

<center><a href="bar-new-category.php" class="cta1"><?php echo $lang['new-category']; ?></a></center>
 <form id="registerForm" action="" method="POST">
	 <table class="default">
	  <thead>
	   <tr>
	    <th><?php echo $lang['global-name']; ?></th>
	    <th># of products</th>
	    <th></th>
	    <th><?php echo $lang['sort-order']; ?></th>
	    <th><?php echo $lang['global-actions']; ?></th>
	   </tr>
	  </thead>
	  <tbody>
	  
	  <?php
	  
	  	$i = 1;
		while ($category = $results->fetch()) {
	
	$categoryid = $category['id'];
	$sortorder = $category['sortorder'];
	
	if ($sortorder == 9999) {
		$sortorder = 0;
	}
	
	
	
	if ($category['description'] != '') {
		
		$commentRead = "
		                <img src='images/description.png' id='comment$categoryid' /><div id='helpBox$categoryid' class='helpBox' style='top: 10px;'>{$category['description']}</div>
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
	   <td><span class='relativeitem'>$commentRead</span></td>
  	   <td style='text-align: center;'><input type='number' name='cat[%d][sortorder]' class='twoDigit defaultinput-no-margin' value='%d' /><input type='hidden' name='cat[%d][categoryid]' class='twoDigit' value='%d' /></td>
  	   <td style='text-align: center;'><a href='javascript:delete_category(%d,%d)'><img src='images/delete.png' height='15' title='Delete category' /></a></td>
	  </tr>",
	  $category['id'], $category['name'], $category['id'], $noOfCats, $i, $sortorder, $i, $categoryid, $categoryid, $noOfCats
	  );
	  echo $flower_row;
	  
	  $i++;
  }
?>



	 </tbody>
	 </table>
	 <input type='hidden' name='sortsubmit' />
 <center><button class='oneClick cta4' name='oneClick' type="submit"><?php echo $lang['global-confirm']; ?></button></center>
 </form>

<?php  displayFooter(); ?>
