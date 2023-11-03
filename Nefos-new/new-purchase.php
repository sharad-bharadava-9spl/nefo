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
	
?>


<?php
	// Query to look up categories
	$selectCats = "SELECT id, name, type from categories ORDER by id ASC";
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
	
		
		echo "
<div class='actionbox'>
 <h2><img src='images/goods-icon.png' class='midalign' />&nbsp;&nbsp;&nbsp;<span>$name ($type)</span></h2>
  <form onsubmit='oneClick.disabled = true; return true;' id='registerForm' action='new-purchase-2.php' method='POST'>
   <input type='hidden' name='category' value='$categoryid' />
   <input type='hidden' name='categoryName' value='$name' />
   <select class='fakeInput' name='prePurchase'>
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
  <button name='oneClick' type='submit'>{$lang['global-select']}</button>
   </form>
   <br />
   {$lang['member-orcaps']}
   <br />
   <br />
   <a href='new-goods.php?frompurchase&id=$categoryid'>{$lang['pur-newproduct']}</a>
</div>";
  
	}

displayFooter(); ?>





