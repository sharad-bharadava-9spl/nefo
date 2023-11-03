<?php

	require_once 'cOnfig/connection.php';
	require_once 'cOnfig/languages/common.php';
	
	session_start();
	
	$_SESSION['lang'] = 'es';
	
	if ($_GET['tr'] != 'yes5') {
		echo "<br />&nbsp;&nbsp;Access denied";
		exit();
	}
	
	$saleid = $_GET['saleid'];
	$customer = $_GET['cid'];
	
	$query = "SELECT shortName FROM customers WHERE number = '$customer'";
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
		$shortName = $row['shortName'];
		

	
?>

<!DOCTYPE html> 
<html moznomarginboxes mozdisallowselectionprint>
 <head>
  <title>Nefos</title>
 </head>
 <body>
<style>
@page { margin: 0; }

body {
	font-family: Tahoma, sans-serif;
	display: inline-block;
	text-align: left;
	font-size: 16px;
	color: black;
	margin-bottom: 20px;
	height: 50mm;
	border: 1px solid black;
	padding: 5px;
}
</style>

<?php

	echo <<<EOD
<div id='container'>
<center>
<br />
#$saleid<br />
<br /><span style='font-size: 18px;'><strong>This is a really long fucking customer name which might be a pain in the ass! $shortName 4</strong></span>
</center>
</div>
	
EOD;

?>
<!--
  <script type="text/javascript">
window.print();
setTimeout(function() {
    window.close();
    }, 50);
</script>
-->
 </body>
</html>
