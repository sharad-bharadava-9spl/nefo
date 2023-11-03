<?php
	
	require_once 'cOnfig/connection.php';
	require_once 'cOnfig/view.php';
	require_once 'cOnfig/authenticate.php';
	require_once 'cOnfig/languages/common.php';
	
	session_start();
	$accessLevel = '3';
	
	// Authenticate & authorize
	authorizeUser($accessLevel);
		
	getSettings();
	
	if ($_POST['categories'] == 'true') {
		
	foreach($_POST['cat'] as $cat) {
		
		$categoryid = $cat['id'];
		$nameen = str_replace('%', '&#37;', $cat['nameen']);
		$namees = str_replace('%', '&#37;', $cat['namees']);
		$descriptionen = str_replace('%', '&#37;', $cat['descriptionen']);
		$descriptiones = str_replace('%', '&#37;', $cat['descriptiones']);
		
		$query = "UPDATE expensecategories SET nameen = '$nameen', namees = '$namees', descriptionen = '$descriptionen', descriptiones = '$descriptiones' WHERE categoryid = $categoryid";
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
	
		
		// On success: redirect.
		$_SESSION['successMessage'] = "Categorias modificado con exito!";
		header("Location: admin.php");
		exit();
		
	}
	// Query to look up expenses
	$selectExpenses = "SELECT categoryid, nameen, namees, descriptionen, descriptiones FROM expensecategories ORDER BY categoryid ASC";
		try
		{
			$results = $pdo3->prepare("$selectExpenses");
			$results->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	


	pageStart($lang['categories'], NULL, $deleteExpenseScript, "pexpenses", "admin", $lang['categories'], $_SESSION['successMessage'], $_SESSION['errorMessage']);
	
	echo "
	<form id='registerForm' action='' method='POST'>
 	<input type='hidden' name='categories' value='true' />

	<table class='defaultalternate' style='padding: 10px;'>
	 <tr>
	  <th></th>
	  <th class='centered'><center><strong>Name</center></strong></th>
	  <th><strong><center>Description</center></strong></th>
	  <th><strong><center>Nombre</center></strong></th>
	  <th><strong><center>Descripcion espa&ntilde;ol</center></strong></th>
	 </tr>";
	 
					$i = 0;
		while ($expense = $results->fetch()) {
	
	$categoryid = $expense['categoryid']; // find member
	$nameen = $expense['nameen'];
	$namees = $expense['namees'];
	$descriptionen = $expense['descriptionen'];
	$descriptiones = $expense['descriptiones'];

	$expense_row =	sprintf("
	 <tr><td><input type='hidden' name='cat[%d][id]' value='%d' class='twoDigit defaultinput' readonly /></td>
	 <td><input type='text' name='cat[%d][nameen]' value='%s' class='defaultinput' /></td>
	 <td><input type='text' name='cat[%d][descriptionen]' value='%s' class='defaultinput' /></td>
	 <td><input type='text' name='cat[%d][namees]' value='%s' class='defaultinput' /></td>
	 <td><input type='text' name='cat[%d][descriptiones]' value='%s' class='defaultinput' /></td></tr>
	 
	 ",
	$i, $categoryid, $i, $nameen, $i, $descriptionen, $i, $namees, $i, $descriptiones
	  );
	  echo $expense_row;
	  
	  $i++;
  }
?>

	 </table>
 <center><button type="submit" class='cta1' name='oneClick'><?php echo $lang['global-save']; ?></button></center>
</form>	 
	 
<?php  displayFooter(); ?>
<script type="text/javascript">
			    $(document).ready(function() {
			
			$('.defaultalternate').tablesorter({
				usNumberFormat: true,
				headers: {
					3: {
						sorter: "dates"
					},
					7: {
						sorter: "dates"
					}
				}
			}); 

		});
</script>
