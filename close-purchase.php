<?php
	
	require_once 'cOnfig/connection.php';
	require_once 'cOnfig/view.php';
	require_once 'cOnfig/authenticate.php';
	require_once 'cOnfig/languages/common.php';
	
	session_start();
	$accessLevel = '3';
	
	// Authenticate & authorize
	authorizeUser($accessLevel);

	pageStart($lang['title-closeproduct'], NULL, NULL, "pclosePurchase", "dev-align-center", $lang['closeproduct-closeproduct'], $_SESSION['successMessage'], $_SESSION['errorMessage']);

	$selectRows = "SELECT COUNT(purchaseid) FROM purchases WHERE category = 1 AND closedAt IS NULL";
	$rowCountFlowers = $pdo3->query("$selectRows")->fetchColumn();

	$selectRows = "SELECT COUNT(purchaseid) FROM purchases WHERE category = 2 AND closedAt IS NULL";
	$rowCountExtracts = $pdo3->query("$selectRows")->fetchColumn();
	
?>

<div class="actionbox-noheight">
 <h2><img src="images/icon-flower-g.png" class="midalign" height="48" /><br /><?php echo $lang['global-flowercaps'] . " <span class='usergrouptext'>$rowCountFlowers</span>"; ?></h2>
 <div style='background-color: #c3c8c1; height: 2px;'></div><br />
  <form id="registerForm" action="close-purchase-2.php" method="GET">
   <input type="hidden" name="category" value="1" />
   <select class="fakeInput defaultinput" name="purchaseid">
    <option value=""><?php echo $lang['addremove-pleaseselect']; ?></option>

<h3><?php echo $lang['global-flowers']; ?></h3>
<?php
		
	$selectFlower = "SELECT g.breed2, g.name, p.purchaseid FROM flower g, purchases p WHERE p.category = 1 AND p.productid = g.flowerid AND p.closedAt IS NULL ORDER BY g.name ASC;";
		try
		{
			$result = $pdo3->prepare("$selectFlower");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		while ($flower = $result->fetch()) {
			if ($flower['breed2'] != '') {
			$name = $flower['name'] . " x " . $flower['breed2'];
		} else {
			$name = $flower['name'];
		}


	$flower_row = sprintf("
	  <option value='%d'>%s</option>",
	  $flower['purchaseid'], $name
	  );
	  echo $flower_row;
  }
?>

  </select><br />
  <button type="submit" class='cta2' style='margin-bottom: 0; width: 214px;'><?php echo $lang['global-select']; ?></button>
   </form>
   </div>
   
<div class="actionbox-noheight">
 <h2><img src="images/icon-extract-g.png" class="midalign" height="48" /><br /><?php echo $lang['global-extracts'] . " <span class='usergrouptext'>$rowCountExtracts</span>"; ?></h2>
 <div style='background-color: #c3c8c1; height: 2px;'></div><br />
  <form id="registerForm" action="close-purchase-2.php" method="GET">
   <input type="hidden" name="category" value="2" />
   <select class="fakeInput defaultinput" name="purchaseid">
    <option value=""><?php echo $lang['addremove-pleaseselect']; ?></option>

  <?php
  // AND NOW THE H TABLE:

  	$selectExtract = "SELECT h.name, p.purchaseid FROM extract h, purchases p WHERE p.category = 2 AND p.productid = h.extractid AND p.closedAt IS NULL ORDER BY h.name ASC;";
		try
		{
			$result = $pdo3->prepare("$selectExtract");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		while ($extract = $result->fetch()) {

	$extract_row =	sprintf("
	      <option value='%d'>%s %s</option>",
	  $extract['purchaseid'], $extract['name'], $extract['extracttype']
	  );
	  echo $extract_row;
  }
  ?>
</select><br />
  <button type="submit" class='cta2' style='margin-bottom: 0; width: 214px;'><?php echo $lang['global-select']; ?></button>
   </form>
   </div>
  
  <?php
  
  // AND NOW THE OTHER CATEGORIES:
  
	// Query to look up categories
	$selectCats = "SELECT id, name from categories WHERE id > 2 ORDER by id ASC";
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
	
		while ($category = $results->fetch()) {
		
		$categoryid = $category['id'];
		$name = $category['name'];
		$type = $category['type'];
	
	if ($type == 0) {
		$type = "u";
	} else {
		$type = "g";
	}
  $selectRows = "SELECT COUNT(purchaseid) FROM purchases WHERE category = $categoryid AND closedAt IS NULL";
	$rowCount = $pdo3->query("$selectRows")->fetchColumn();
		
echo "
<div class='actionbox-noheight'>
 <h2><br /><br />$name ($type)<span class='usergrouptext'>$rowCount</span></h2>
 <div style='background-color: #c3c8c1; height: 2px;'></div><br />
  <form id='registerForm' action='close-purchase-2.php' method='GET'>
   <input type='hidden' name='category' value='$categoryid' />
   <select class='fakeInput defaultinput' name='purchaseid'>
    <option value=''>{$lang['addremove-pleaseselect']}</option>";
		
		// For each cat, look up products
		$selectProducts = "SELECT pr.name, p.purchaseid from products pr, purchases p WHERE p.category = $categoryid AND pr.productid = p.productid AND p.closedAt IS NULL ORDER BY pr.name ASC;";
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
	
		while ($product = $result->fetch()) {
	

			$product_row = sprintf("
			<option value='%d'>%s</option>",
			  $product['purchaseid'], $product['name']
			  );
			  echo $product_row;
			  
		}
		
		echo "
  </select><br />
  <button type='submit' class='cta2' style='margin-bottom: 0; width: 214px;'>{$lang['global-select']}</button>
   </form>
</div>";

	}
  
displayFooter(); ?>

