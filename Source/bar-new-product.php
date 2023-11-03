<?php
	
	require_once 'cOnfig/connection.php';
	require_once 'cOnfig/view.php';
	require_once 'cOnfig/authenticate.php';
	require_once 'cOnfig/languages/common.php';
	
	session_start();
	$accessLevel = '3';
	
	// Authenticate & authorize
	authorizeUser($accessLevel);
	

	pageStart($lang['new-bar-product'], NULL, NULL, "pnewstrain", "admin", $lang['new-bar-product'], $_SESSION['successMessage'], $_SESSION['errorMessage']);
		
?>

<?php
	// Query to look up categories
	$selectFlower = "SELECT id, name FROM b_categories ORDER by name ASC";
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
		
		while ($category = $result->fetch()) {// how to reset array datapointer? Better than using resultflower and resultflower2
		
			$categoryid = $category['id'];
			$categoryname = $category['name'];
		
			echo "
<a href='bar-new-product-2.php?id=$categoryid' class='ctalink'>
 <table>
  <tr>
   <td style='height: 70px;'><img src='images/icon-bar.png' class='midalign' /></td>
  </tr>
  <tr>
   <td>$categoryname</td>
  </tr>
 </table>
</a>
";

  		}
	
 displayFooter();