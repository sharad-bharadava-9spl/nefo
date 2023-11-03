<?php
	
	require_once 'cOnfig/connection.php';
	require_once 'cOnfig/view.php';
	require_once 'cOnfig/authenticate.php';
	require_once 'cOnfig/languages/common.php';
	
	session_start();
	$accessLevel = '3';
	
	// Authenticate & authorize
	authorizeUser($accessLevel);
	

	pageStart($lang['newpurchase'], NULL, NULL, "newpurchase", "admin", $lang['newpurchasecaps'], $_SESSION['successMessage'], $_SESSION['errorMessage']);
	
	$selectRows = "SELECT COUNT(purchaseid) FROM purchases WHERE category = 1 AND closedAt IS NULL";
	$rowCountFlowers = $pdo3->query("$selectRows")->fetchColumn();

	$selectRows = "SELECT COUNT(purchaseid) FROM purchases WHERE category = 2 AND closedAt IS NULL";
	$rowCountExtracts = $pdo3->query("$selectRows")->fetchColumn();

?>

<div class="actionbox-noheight">
 <h2><img src="images/icon-flower-g.png" class="midalign" height="48" /><br /><?php echo $lang['global-flowercaps'] . " <span class='usergrouptext'>$rowCountFlowers</span>"; ?></h2>
 <div style='background-color: #c3c8c1; height: 2px;'></div><br />
  <form id="registerForm" action="new-purchase-2.php" method="POST">
   <input type="hidden" name="category" value="1" />
   <select class="fakeInput defaultinput" name="prePurchase">
    <option value=""><?php echo $lang['addremove-pleaseselect']; ?></option>
<?php
	// Query to look up flowers
	$selectFlower = "SELECT flowerid, name, breed2 FROM flower ORDER by name ASC";
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
	
		while ($row = $result->fetch()) {
			
			$name = $row['name'];
			$breed2 = $row['breed2'];
			$flowerid = $row['flowerid'];
			
			if ($breed2 != '') {
				$name = $name . " x " . $breed2;
			}
	
			$flower_row = sprintf("<option value='%d'>%s</option>",
		  						 $flowerid, $name);
		  	echo $flower_row;
  		}


?>
  </select><br />
  <button type="submit" class='cta2' style='margin-bottom: 0; width: 214px;'><?php echo $lang['global-select']; ?></button>
   </form>
   <br />
   <strong><?php echo $lang['member-orcaps']; ?></strong>
   <br />
   <br />
   <a href="new-flower.php?frompurchase" class='cta2b' style='width: 200px;'><?php echo $lang['extracts-newflower']; ?></a>
</div>
  
<div class="actionbox-noheight">
 <h2><img src="images/icon-extract-g.png" class="midalign" height="48" /><br /><?php echo $lang['global-extractcaps'] . " <span class='usergrouptext'>$rowCountExtracts</span>"; ?></h2>
 <div style='background-color: #c3c8c1; height: 2px;'></div><br />
  <form id="registerForm" action="new-purchase-2.php" method="POST">
   <input type="hidden" name="category" value="2" />
   <select class="fakeInput defaultinput" name="prePurchase">
    <option value=""><?php echo $lang['addremove-pleaseselect']; ?></option>
<?php
	// Query to look up extract
	$selectExtract = "SELECT extractid, extracttype, extract, name FROM extract ORDER by name ASC";
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
		$extract_row = sprintf("<option value='%d'>%s %s</option>",
								 $extract['extractid'], $extract['name'], $extract['extracttype']);
		echo $extract_row;
	}

?>
  </select><br />
  <button type="submit" class='cta2' style='margin-bottom: 0; width: 214px;'><?php echo $lang['global-select']; ?></button>
   </form>
   <br />
   <strong><?php echo $lang['member-orcaps']; ?></strong>
   <br />
   <br />
   <a href="new-extract.php?frompurchase" class='cta2b' style='width: 200px;'><?php echo $lang['extracts-newextract']; ?></a>
</div>

<?php
	// Query to look up categories
	$selectCats = "SELECT id, name, type from categories WHERE id > 2 ORDER by id ASC";
	try
	{
		$resultCats = $pdo3->prepare("$selectCats");
		$resultCats->execute();
	}
	catch (PDOException $e)
	{
			$error = 'Error fetching user: ' . $e->getMessage();
			echo $error;
			exit();
	}

	while ($category = $resultCats->fetch()) {
	
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
 <h2><img src='images/icon-x.png' class='midalign' height='48' /><br />$name ($type)<span class='usergrouptext'>$rowCount</span></h2>
 <div style='background-color: #c3c8c1; height: 2px;'></div><br />
  <form id='registerForm' action='new-purchase-2.php' method='POST'>
   <input type='hidden' name='category' value='$categoryid' />
   <select class='fakeInput defaultinput' name='prePurchase'>
    <option value=''>{$lang['addremove-pleaseselect']}</option>";

	// Query to look up products
	$selectProduct = "SELECT productid, name FROM products WHERE category = $categoryid ORDER by name ASC";
	try
	{
		$resultProduct = $pdo3->prepare("$selectProduct");
		$resultProduct->execute();
	}
	catch (PDOException $e)
	{
			$error = 'Error fetching user: ' . $e->getMessage();
			echo $error;
			exit();
	}

	while ($product = $resultProduct->fetch()) {
				$product_row = sprintf("<option value='%d'>%s</option>",
	  								 $product['productid'], $product['name']);
	  			echo $product_row;
  	}
	
		echo "
  </select><br />
  <button type='submit' class='cta2' style='margin-bottom: 0; width: 214px;'>{$lang['global-select']}</button>
   </form>
   <br />
   <strong>{$lang['member-orcaps']}</strong>
   <br />
   <br />
   <a href='new-goods.php?frompurchase&id=$categoryid' class='cta2b' style='width: 200px;'>{$lang['pur-newproduct']}</a>
</div>";
  
	}

displayFooter(); ?>





