<?php

	require_once 'cOnfig/connection.php';
	require_once 'cOnfig/view.php';
	require_once 'cOnfig/authenticate.php';
	require_once 'cOnfig/languages/common.php';
	
	session_start();
	$accessLevel = '3';
	
	// Authenticate & authorize
	authorizeUser($accessLevel);

	
	session_start();

	$memberScript = <<<EOD
	
	    $(document).ready(function() {
		    
		    
			$("#xllink").click(function(){

			  $("#mainTable").table2excel({
			    // exclude CSS class
			    exclude: ".noExl",
			    name: "Socios",
			    filename: "Socios" //do not include extension
		
			  });
		
			});
		    
		    
		    
			$('#cloneTable').width($('#mainTable').width());
			
			$.tablesorter.addParser({
			  id: 'dates',
			  is: function(s) { return false },
			  format: function(s) {
			    var dateArray = s.split('-');
			    return dateArray[2].substring(0,4) + dateArray[1] + dateArray[0];
			  },
			  type: 'numeric'
			});
			
			
			$('#mainTable').tablesorter({
				usNumberFormat: true,
				headers: {
					2: {
						sorter: "dates"
					},
					3: {
						sorter: "dates"
					},
					4: {
						sorter: "dates"
					},
					5: {
						sorter: "dates"
					},
					6: {
						sorter: "dates"
					}
				}
			}); 
			
		}); 
		
EOD;


	pageStart($lang['index-members'], NULL, $memberScript, "pmembership", NULL, $lang['index-membersC'], $_SESSION['successMessage'], $_SESSION['errorMessage']);
?>

	 <table class='default' id='cloneTable'>
      <tr class='nonhover'>
       <td colspan='13' style='border-bottom: 0;'>
         <a href="#" id="xllink" onClick="$('#mainTable').tableExport({type:'excel',escape:'false'});"><img src="images/excel.png" style='margin: 0 0 -5px 8px;'/></a>
       </td>
      </tr>
     </table>
<br />
	 <table class='default' id='mainTable'>
	  <thead>	
	   <tr style='cursor: pointer;'>
	    <th class='centered'>#</th>
	    <th class='centered'>Club</th>
	    <th class='centered'>City</th>
	    <th class='centered'>Province</th>
	    <th class='centered'>Fsales</th>
	    <th class='centered'>Sales</th>
	    <th class='centered'>TBI</th>
	   </tr>
	  </thead>
	  <tbody>
	  
	  
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
foreach ($getAllDbs as $DB) {
	
	$database = $DB['Database'];
	
	if ((substr($database,0,3) == 'ccs') && $database != 'ccs_irena' && $database != 'ccs_masterdb') {
		
	$domain = substr($database,4);
		
	$query = "SELECT db_pwd, customer FROM db_access WHERE domain = '$domain'";
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
				$number = $row['customer'];
				
		$query = "SELECT city, state FROM customers WHERE number = '$number'";
		try
		{
			$result = $pdo2->prepare("$query");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
			$city = $row['city'];
			$state = $row['state'];


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
		
		$selectUsersU = "SELECT COUNT( DISTINCT userid )
FROM f_sales
WHERE saletime
BETWEEN '2020-03-01'
AND '2020-03-31';";
		
		try
		{
			$result = $pdo4->prepare("$selectUsersU");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
			$fsales = $row['COUNT( DISTINCT userid )'];
			
		$selectUsersU = "SELECT COUNT( DISTINCT userid )
FROM sales
WHERE saletime
BETWEEN '2020-03-01'
AND '2020-03-31';";
		
		try
		{
			$result = $pdo4->prepare("$selectUsersU");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
			$sales = $row['COUNT( DISTINCT userid )'];
			
		if ($fsales > $sales) {
			$opr = 'Check';
		} else if ($sales > 500) {
			$opr = '500';
		} else {
			$opr = $sales;
		}

			
			echo sprintf("
  	  <tr>
  	   <td class='clickableRow'>%s</td>
  	   <td class='clickableRow'>%s</td>
  	   <td class='clickableRow'>%s</td>
  	   <td class='clickableRow'>%s</td>
  	   <td class='clickableRow'>%s</td>
  	   <td class='clickableRow'>%s</td>
  	   <td class='clickableRow'>%s</td>
</td></tr>",
	  $number, $database, $city, $state, $fsales, $sales, $opr);

	}
}

}