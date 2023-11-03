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
	
?>


<?php  
  
  
	// Query to look up categories
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
  <form onsubmit='oneClick.disabled = true; return true;' id='registerForm' action='bar-close-purchase-2.php' method='GET'>
   <input type='hidden' name='category' value='$categoryid' />
   <select class='fakeInput defaultinput' name='purchaseid'>
    <option value=''>{$lang['addremove-pleaseselect']}</option>";
		
		// For each cat, look up products
		$selectProducts = "SELECT pr.name, p.purchaseid from b_products pr, b_purchases p WHERE p.category = $categoryid AND pr.productid = p.productid AND p.closedAt IS NULL ORDER BY pr.name ASC;";
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
  <button class='cta2' name='oneClick' type='submit'>{$lang['global-select']}</button>
   </form></div>";

	}
  
displayFooter();
