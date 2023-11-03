<?php
	
	require_once 'cOnfig/connection.php';
	require_once 'cOnfig/view.php';
	require_once 'cOnfig/authenticate.php';
	require_once 'cOnfig/languages/common.php';
	
	session_start();
	$accessLevel = '3';
	
	// Authenticate & authorize
	authorizeUser($accessLevel);
	
	// Is Workstations enabled?
	if ($_SESSION['puestosOrNot'] == 1) {
		
		if ($_SESSION['workstation'] != 'dispensary' && $_SESSION['userGroup'] > 1) {
			
   			handleError("No tienes permiso para ver esta pagina! / You are not authorized to see that page.","User has too low access level.");
			exit();
			
		}
		
	}
	
	// Dabulance customization
	if ($_SESSION['domain'] == 'dabulance') {
		
		$shopid = $_SESSION['shopid'];
		
		if ($shopid == '') {
			$shopid = 0;
		}
	
	}
				
				
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
	
	// Dabulance customization
	if ($_SESSION['domain'] == 'dabulance') {
		
		if ($_SESSION['userGroup'] != 1) {
			
			$selectCats = "SELECT id, time, name, description, type, sortorder, private, shopid, icon from categories WHERE id > 2 AND shopid = '$shopid' ORDER by sortorder ASC";
			
		} else {
		
			$selectCats = "SELECT id, time, name, description, type, sortorder, private, shopid, icon from categories WHERE id > 2 ORDER by shopid, sortorder ASC";
		
		}
		
	} else {
		
		// Query to look up categories
		$selectCats = "SELECT id, time, name, description, type, sortorder, icon from categories ORDER by sortorder ASC";
		
	}
	
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

<center><a href="new-category.php" class="cta1"><?php echo $lang['new-category']; ?></a>
<?php if ($_SESSION['domain'] == 'dabulance') { ?>
		<a href="products.php" class="cta4">Products</a>
		<a href="open-purchases.php" class="cta4">Purchases</a>
<?php } ?>

</center>
 <form id="registerForm" action="" method="POST">
	 <table class="default" id="mainTable">
	  <thead>
	   <tr style='cursor: pointer;'>
	    <th><?php echo $lang['icon']; ?></th>
<?php if ($_SESSION['domain'] == 'dabulance') { ?>
	    <th>Shop</th>
<?php } ?>
	    <th><?php echo $lang['global-category']; ?></th>
<?php if ($_SESSION['domain'] == 'dabulance') { ?>
	    <th>Private?</th>
<?php } ?>
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
	$private = $category['private'];
	$shopid = $category['shopid'];
	$icon = $category['icon'];
	
	if ($shopid == 0) {
		$shopName = "Dabulance";
	} else {
		
		// Lookup club info
		$query = "SELECT name, city FROM shops WHERE id = '$shopid'";
		try
		{
			$result = $pdo3->prepare("$query");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
			
		$row = $result->fetch();
			$name = $row['name'];
			$city = $row['city'];
			$shopName = "$name ($city)";
		
	}
	
	if ($sortorder == 9999) {
		$sortorder = 0;
	}
	
	
	if ($type == 0 && $categoryid > 2) {
		$type = $lang['units'];
	} else {
		$type = $lang['grams'];
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

	$icon_image = "";
	$domain = $_SESSION['domain'];
	if(trim($icon) != ''){

		$icon_image = "<img src='images/_$domain/category/$icon' height='20' />";
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
			
		//$icon = "<img src='images/icon-flower-g.png' height='20' />";
		
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
		
		//$icon = "<img src='images/icon-extract-g.png' height='20' />";
		
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
		
		// Look up icon
		//$icon = "";
		
	}
			
	if ($categoryid < 3) {
		$deleteOrNot = "";
		$clickOrNot = "class='clickableRow'";
	} else {
		$deleteOrNot = "<a href='javascript:delete_category($categoryid,$noOfCats)'><img src='images/delete.png' height='15' title='Delete category' /></a>";
		$clickOrNot = "class='clickableRow'";
	}
	
	if ($private == 1) {
		
		$private = 'Yes';
		
	} else {
		
		$private = '';
		
	}
	
	if ($_SESSION['domain'] == 'dabulance') {
		
		$flower_row =	sprintf("
  	  <tr>
  	   <td $clickOrNot href='edit-category.php?categoryid=%d'><center>$icon_image</center></td>
  	   <td $clickOrNot href='edit-category.php?categoryid=%d'>%s</td>
  	   <td $clickOrNot href='edit-category.php?categoryid=%d'>%s</td>
  	   <td $clickOrNot href='edit-category.php?categoryid=%d'>%s</td>
  	   <td $clickOrNot href='edit-category.php?categoryid=%d' style='text-align: center;'>%d</td>
  	   <td $clickOrNot href='edit-category.php?categoryid=%d' style='text-align: center;'>%s</td>
	   <td><span class='relativeitem'>$commentRead</span></td>
  	   <td style='text-align: center;'><input type='number' name='cat[%d][sortorder]' class='twoDigit defaultinput-no-margin' value='%d' /><input type='hidden' name='cat[%d][categoryid]' class='twoDigit' value='%d' /></td>
  	   <td style='text-align: center;'>%s</td>
	  </tr>",
	  $categoryid, $categoryid, $shopName, $categoryid, $category['name'], $categoryid, $private, $categoryid, $noOfCats, $categoryid, $type, $i, $sortorder, $i, $categoryid, $deleteOrNot
	  );
	  
	} else {
	
		$flower_row =	sprintf("
  	  <tr>
  	   <td $clickOrNot href='edit-category.php?categoryid=%d'><center>$icon_image</center></td>
  	   <td $clickOrNot href='edit-category.php?categoryid=%d'>%s</td>
  	   <td $clickOrNot href='edit-category.php?categoryid=%d' style='text-align: center;'>%d</td>
  	   <td $clickOrNot href='edit-category.php?categoryid=%d' style='text-align: center;'>%s</td>
	   <td><span class='relativeitem'>$commentRead</span></td>
  	   <td style='text-align: center;'><input type='number' name='cat[%d][sortorder]' class='twoDigit defaultinput-no-margin' value='%d' /><input type='hidden' name='cat[%d][categoryid]' class='twoDigit' value='%d' /></td>
  	   <td style='text-align: center;'>%s</td>
	  </tr>",
	  $categoryid, $categoryid, $category['name'], $categoryid, $noOfCats, $categoryid, $type, $i, $sortorder, $i, $categoryid, $deleteOrNot
	  );
	  
  }
	  echo $flower_row;
	  
	  $i++;
  }
?>



	 </tbody>
	 </table>
	 <input type='hidden' name='sortsubmit' />
 <center>
 <button class='oneClick cta4' name='oneClick' type="submit"><?php echo $lang['global-confirm']; ?></button>
 </center>
 </form>

<?php  displayFooter(); ?>
