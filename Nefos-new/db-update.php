<?php

	require_once 'cOnfig/connection.php';
	require_once 'cOnfig/view.php';
	require_once 'cOnfig/authenticate.php';
	require_once 'cOnfig/languages/common.php';
	
	session_start();
	$accessLevel = '3';
	
	// Authenticate & authorize
	authorizeUser($accessLevel);
	
	// ini_set('max_execution_time', 0);
	// ignore_user_abort(true);
	
	$deleteSaleScript = <<<EOD
	
	  
	    $(document).ready(function() {
		    
		    
$("#xllink").click(function(){

	  $("#mainTable").table2excel({
	    // exclude CSS class
	    exclude: ".noExl",
	    name: "Retiradas",
	    filename: "Retiradas" //do not include extension

	  });

	});
	
	});
	
EOD;

	pageStart($lang['index-members'], NULL, $deleteSaleScript, "pmembership", NULL, $lang['index-membersC'], $_SESSION['successMessage'], $_SESSION['errorMessage']);
?>

<form id="registerForm" action="" method="POST">
	 <table class='default' id='cloneTable'>
      <tr class='nonhover'>
       <td colspan='13' style='border-bottom: 0;'>
                <a href="#" id="xllink" onClick="$('#mainTable').tableExport({type:'excel',escape:'false'});"><img src="images/excel.png" style='margin: 0 0 -5px 8px;'/></a>
       </td>
      </tr>
     </table>
<br />
	 <table class='default'>
	  
	  <?php
	  
	try	{
		$dbh = new PDO('mysql:host=127.0.0.1:3306;', 'root', 'uqgj5nif5OqjtO3z');
 		$dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
 		$dbh->exec('SET NAMES "utf8"');
	}
	catch (PDOException $e)	{
		
  		$output = 'Unable to connect to the database server: ' . $e->getMessage();

 		echo $output;
 		exit();
 		
	}
	
$sql = $dbh->query('SHOW DATABASES');
$getAllDbs = $sql->fetchALL(PDO::FETCH_ASSOC);

$i = 1;

echo "<table id='mainTable'><tr>
<td style='padding: 5px;'>DB</td>
<td style='padding: 5px;'>Cat</td>
<td style='padding: 5px;'>Fees</td>
<td style='padding: 5px;'>Discounts</td>
</tr>";


foreach ($getAllDbs as $DB) {
	
	$database = $DB['Database'];
	
	if ((substr($database,0,3) == 'ccs') && $database != 'ccs_masterdb') {
		
		$purchaseidlist = '';
		$idlist = '';
		
	$domain = substr($database,4);
		
	$query = "SELECT db_pwd FROM db_access WHERE domain = '$domain'";
		try
		{
			$result = $pdo->prepare("$query");
			$result->execute();
			$data = $result->fetchAll();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}

		if ($data) {

			$row = $data[0];
			$db_pwd = $row['db_pwd'];

			$db_name = "ccs_" . $domain;
			$db_user = $db_name . "u";

		try	{
	 		$pdo4 = new PDO('mysql:host='.DATABASE_HOST.';dbname='.$db_name, $db_user, $db_pwd);
	 		$pdo4->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	 		$pdo4->exec('SET NAMES "utf8"');
		}
		catch (PDOException $e)	{
	  		$output = 'Unable to connect to the database server: ' . $e->getMessage();
	
	 		echo $output;
	 		exit();
		}	   		
		
		
		
		
		/*************** START QUERIES FROM HERE - pdo4! *****************/
		
		echo "NOW DOING: $database<br />";
			$query = "ALTER TABLE `contract` ADD `box3` INT NOT NULL DEFAULT '0' AFTER `image`;";
			try
			{
				$result = $pdo4->prepare("$query");
				$result->execute();
			}
			catch (PDOException $e)
			{
					$error = 'Error fetching user: ' . $e->getMessage();
					echo $error . "<br>";
					//exit();
			}
		
			
			echo "Done.<br /><br />";
		/*
			$query = "SELECT time FROM contract";
			try
			{
				$result = $pdo4->prepare("$query");
				$result->execute();
			}
			catch (PDOException $e)
			{
					$error = 'Error fetching user: ' . $e->getMessage();
					echo $error;
					exit();
			}
		
			$row = $result->fetch();
				$time = $row['time'];
				
				echo "<tr><td>$database</td><td>$time</td></tr>";	
			/*
			$query = "SELECT COUNT(id) FROM cuotas";
			try
			{
				$result = $pdo4->prepare("$query");
				$result->execute();
			}
			catch (PDOException $e)
			{
					$error = 'Error fetching user: ' . $e->getMessage();
					echo $error;
					exit();
			}
		
			$row = $result->fetch();
				$result = $row['COUNT(id)'];
			
			echo "<td>$result</td>";
			
			$query = "SELECT COUNT(id) FROM inddiscounts UNION ALL SELECT COUNT(id) FROM catdiscounts";
			try
			{
				$result = $pdo4->prepare("$query");
				$result->execute();
			}
			catch (PDOException $e)
			{
					$error = 'Error fetching user: ' . $e->getMessage();
					echo $error;
					exit();
			}
		
			$row = $result->fetch();
				$result = $row['COUNT(id)'];
			
			echo "<td>$result</td>";
			
			
			echo "</tr>";*/
				
			
			



	}
}
}


echo "500: $res500<br />";
echo "250: $res250<br />";
echo "100: $res100<br />";
echo "0: $res<br />";
echo "</table>";

?>

<?php

displayFooter();