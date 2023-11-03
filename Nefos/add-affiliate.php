<?php
	
	require_once 'cOnfig/connection.php';
	require_once 'cOnfig/view.php';
	require_once 'cOnfig/authenticate.php';
	require_once 'cOnfig/languages/common.php';
	
	session_start();
	$accessLevel = '3';
	
	// Authenticate & authorize
	authorizeUser($accessLevel);
	
	$affid = $_GET['affid'];
	
	/***** FORM SUBMIT END *****/


  	// Query to look up clubs      	
	$selectGroups = "SELECT id, number, shortName, longName, city FROM customers ORDER by number ASC";
	try
	{
		$results = $pdo3->prepare("$selectGroups");
		$results->execute();
	}
	catch (PDOException $e)
	{
			$error = 'Error fetching user: ' . $e->getMessage();
			echo $error;
			exit();
	}

	$deleteSaleScript = <<<EOD

	    $(document).ready(function() {
		    
			$('#mainTable').tablesorter({
				usNumberFormat: true,
				headers: {
					0: {
						sorter: "dates"
					},
					4: {
						sorter: "currency"
					},
					5: {
						sorter: "currency"
					},
					6: {
						sorter: "currency"
					},
					7: {
						sorter: "currency"
					},
					8: {
						sorter: "currency"
					},
					9: {
						sorter: "currency"
					},
					10: {
						sorter: "currency"
					}
				}
			}); 
			
		});

EOD;

	pageStart("Add affiliate", NULL, $validationScript, "pprofile", NULL, "Add affiliate", $_SESSION['successMessage'], $_SESSION['errorMessage']);
		
?>


   <form id="registerForm" action="" method="POST">

    <input type="hidden" name="affid" value="<?php echo $affid; ?>" />
 <br /><br />
<table class='default'>
 <tr>
  <td><strong>#</strong></td>
  <td><strong>Short Name</strong></td>
  <td><strong>City</strong></td>
  <td><strong></strong></td>
 </tr>
<?php

	while ($group = $results->fetch()) {
		
		$id = $group['id'];
		$number = $group['number'];
		$shortName = $group['shortName'];
		$longName = $group['longName'];
		$city = $group['city'];
		
  		echo <<<EOD
 <tr>
  <td>$number</td>
  <td>$shortName</td>
  <td>$city</td>
  <td><a href='uTil/add-club-affiliate.php?affid=$affid&clubid=$id'><img src='images/plus-new.png' height='15' /></a></td>
 </tr>
  		
EOD;
  		
	}
?>
</table>

<?php displayFooter(); ?>
