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
			
			$query = "UPDATE categories SET sortorder = '$sortorder' WHERE id = $categoryid";
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
	$selectCats = "SELECT id, time, name, description, type, sortorder from categories ORDER by sortorder ASC";
	try
	{
		$resultCat = $pdo3->prepare("$selectCats");
		$resultCat->execute();
	}
	catch (PDOException $e)
	{
			$error = 'Error fetching user: ' . $e->getMessage();
			echo $error;
			exit();
	}

	
	$deleteCategoryScript = <<<EOD
	
		   $(document).ready(function() {
			$('#mainTable').tablesorter({
				usNumberFormat: true,
				headers: {
					4: {
						sorter: "currency"
					},
					6: {
						sorter: "dates"
					},
					11: {
						sorter: "dates"
					}
				}
			}); 

			}); 
function delete_category(categoryid,noOfCats) {
	
	if (noOfCats != 0) {
		
		if (confirm("This category has " + noOfCats + " products!! Are you sure you want to delete it?")) {
			window.location = "uTil/delete-category.php?categoryid=" + categoryid;
		}
		
	} else {
		
		if (confirm("Esta seguro que quieres borrar este categoria?  No se puede volver a esta pagina despues!")) {
			window.location = "uTil/delete-category.php?categoryid=" + categoryid;
		}
	}
}


EOD;
	pageStart($lang['categories'], NULL, $deleteCategoryScript, "pproducts", "admin", $lang['categories'], $_SESSION['successMessage'], $_SESSION['errorMessage']);
	
	
?>

<center><a href="new-category.php" class="cta"><?php echo $lang['new-category']; ?></a></center>
 <form id="registerForm" action="" method="POST">
	 <table class="default" id="mainTable">
	  <thead>
	   <tr style='cursor: pointer;'>
	    <th><?php echo $lang['global-name']; ?></th>
	    <th># <?php echo $lang['of-products']; ?></th>
	    <th><?php echo $lang['global-type']; ?></th>
	    <th></th>
	    <th><?php echo $lang['sort-order']; ?></th>
	    <th><?php echo $lang['global-actions']; ?></th>
	   </tr>
	  </thead>
	  <tbody>
	  
	  <?php

	  $i = 1;
	while ($category = $resultCat->fetch()) {

	$categoryid = $category['id'];
	$type = $category['type'];
	$sortorder = $category['sortorder'];
	
	
	if ($sortorder == 9999) {
		$sortorder = 0;
	}
	
	
	if ($type == 0) {
		$type = $lang['units'];
	} else {
		$type = $lang['grams'];
	}
	
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

	if ($categoryid < 3) {
		$deleteOrNot = "";
	} else {
		$deleteOrNot = "<a href='javascript:delete_category($categoryid,$noOfCats)'><img src='images/delete.png' height='15' title='Delete category' /></a>";
	}
	
	if ($categoryid == 1) {
		
		$selectProducts = "SELECT COUNT(flowerid) from flower";
		try
		{
			$result = $pdo3->prepare("$selectProducts");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
			$noOfCats = $row['COUNT(flowerid)'];
		
	} else if ($categoryid == 2) {
		
		$selectProducts = "SELECT COUNT(extractid) from extract";
		try
		{
			$result = $pdo3->prepare("$selectProducts");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
			$noOfCats = $row['COUNT(extractid)'];
		
	} else {
		
		// Query to look up products
		$selectProducts = "SELECT COUNT(productid) from products WHERE category = $categoryid";
		try
		{
			$result = $pdo3->prepare("$selectProducts");
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
		
	}
			
	$flower_row =	sprintf("
  	  <tr>
  	   <td class='clickableRow' href='edit-category.php?categoryid=%d'>%s</td>
  	   <td class='clickableRow' href='edit-category.php?categoryid=%d' style='text-align: center;'>%d</td>
  	   <td class='clickableRow' href='edit-category.php?categoryid=%d' style='text-align: center;'>%s</td>
	   <td class='relative'>$commentRead</td>
  	   <td style='text-align: center;'><input type='number' name='cat[%d][sortorder]' class='twoDigit' value='%d' /><input type='hidden' name='cat[%d][categoryid]' class='twoDigit' value='%d' /></td>
  	   <td style='text-align: center;'>%s</td>
	  </tr>",
	  $categoryid, $category['name'], $categoryid, $noOfCats, $categoryid, $type, $i, $sortorder, $i, $categoryid, $deleteOrNot
	  );
	  echo $flower_row;
	  
	  $i++;
  }
?>



	 </tbody>
	 </table>
	 <br /><br />
	 <input type='hidden' name='sortsubmit' />
 <button class='oneClick' name='oneClick' type="submit"><?php echo $lang['global-confirm']; ?></button>
 </form>

<?php  displayFooter(); ?>
