<?php
	
	require_once 'cOnfig/connection.php';
	require_once 'cOnfig/view.php';
	require_once 'cOnfig/authenticate.php';
	require_once 'cOnfig/languages/common.php';
	
	session_start();
	$accessLevel = '3';
	
	// Authenticate & authorize
	authorizeUser($accessLevel);
	

	pageStart($lang['newpurchase'], NULL, NULL, "newpurchase", "admin dev-align-center", $lang['newpurchasecaps'], $_SESSION['successMessage'], $_SESSION['errorMessage']);
	
?>


<?php
	// Query to look up bar categories
	$selectCats = "SELECT id, name from b_categories ORDER by id ASC";
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
		
		echo "
<div class='actionbox-noheight'>
 <h2><img src='images/goods-icon.png' class='midalign' /><br>$name</h2>
  <form onsubmit='oneClick.disabled = true; return true;' id='registerForm' action='bar-new-purchase-2.php' method='POST'>
   <input type='hidden' name='category' value='$categoryid' />
   <input type='hidden' name='categoryName' value='$name' />
   <select class='fakeInput defaultinput' name='prePurchase'>
    <option value=''>{$lang['addremove-pleaseselect']}</option>";

	// Query to look up products
	$selectProduct = "SELECT productid, name FROM b_products WHERE category = $categoryid ORDER by name ASC";
		try
		{
			$result = $pdo3->prepare("$selectProduct");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		while ($product = $result->fetch()) {
				$product_row = sprintf("<option value='%d'>%s</option>",
	  								 $product['productid'], $product['name']);
	  			echo $product_row;
  		}
	
		echo "
  </select><br />
  <button class='cta2' name='oneClick' type='submit'>{$lang['global-select']}</button>
   </form>
   <br />
   {$lang['member-orcaps']}
   <br />
   <br />
   <a href='bar-new-product-2.php?id=$categoryid' class='cta2b'>{$lang['pur-newproduct']}</a>
</div>";
  
	}

displayFooter(); ?>





