<?php
	
	require_once 'cOnfig/connection.php';
	require_once 'cOnfig/view.php';
	require_once 'cOnfig/authenticate.php';
	require_once 'cOnfig/languages/common.php';
	
	session_start();
	$accessLevel = '3';
	
	// Authenticate & authorize
	authorizeUser($accessLevel);
	

	if (isset($_POST['affid'])) {
		$affid = $_POST['affid'];
	} else if (isset($_GET['affid'])) {
		$affid = $_GET['affid'];
	} else {
		handleError($lang['error-nouserid'],"");
	}
		
	// Query to look up customer
	$userDetails = "SELECT name FROM affiliations WHERE id = $affid";
	try
	{
		$result = $pdo3->prepare("$userDetails");
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
		

	pageStart("Affiliation", NULL, $deleteNoteScript, "pprofilenew", NULL, "Affiliation", $_SESSION['successMessage'], $_SESSION['errorMessage']);
	


?>
 <span class="firstbuttons">
<center>
 
<?php
	echo "<a href='affiliations.php' class='cta'>&laquo; Affiliations</a>";
	echo "<a href='edit-affiliation.php?affid=$affid' class='cta'>Edit</a>";
	echo "<a href='add-affiliate.php?affid=$affid' class='cta'>Add affiliate</a>";

?>
</center>

 </span>
<br /><br />
<div class="overview" style="width: 200px;">
<center>
 <span class="profilefirst"><?php echo $name; ?></span>
</center>
 <br /><br />
 <table class='padonly'>
<?php 

	$query = "SELECT id, shortName FROM customers WHERE affiliation = $affid";
	try
	{
		$resultsA = $pdo3->prepare("$query");
		$resultsA->execute();
	}
	catch (PDOException $e)
	{
			$error = 'Error fetching user: ' . $e->getMessage();
			echo $error;
			exit();
	}
	
	$list = '';

	while ($rowA = $resultsA->fetch()) {
		$list .= "<tr><td><a href='customer.php?user_id=" . $rowA['id'] . "' class='yellow'>" . $rowA['shortName'] . "</a></td><td><a href='uTil/delete-affiliate.php?affid=$affid&clubid={$rowA['id']}'><img src='images/delete.png' height='15' /></a></td></tr>";
	}
 
 echo $list; ?>
 
 </table>
 </center>
 
 </div>
