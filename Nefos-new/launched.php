<?php
	
	require_once 'cOnfig/connection.php';
	require_once 'cOnfig/viewv6.php';
	require_once 'cOnfig/authenticate.php';
	require_once 'cOnfig/languages/common.php';
	
	session_start();
	$accessLevel = '3';
	
	// Authenticate & authorize
	authorizeUser($accessLevel);
	
	getSettings();	
	
	// Query to look up users
	$selectUsers = "SELECT YEAR(launchdate), MONTH(launchdate), COUNT(id) FROM `customers` WHERE launchdate IS NOT NULL GROUP BY YEAR(launchdate), MONTH(launchdate) ORDER BY YEAR(launchdate) DESC, MONTH(launchdate) DESC";
		try
		{
			$results = $pdo3->prepare("$selectUsers");
			$results->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		
	
		
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
			
EOD;

	if ($_SESSION['creditOrDirect'] == 1 && $_SESSION['membershipFees'] == 1) {
		
		$memberScript .= <<<EOD
			
			$('#mainTable').tablesorter({
				usNumberFormat: true,
				headers: {
					4: {
						sorter: "currency"
					},
					6: {
						sorter: "dates"
					},
					11: {
						sorter: "dates"
					}
				}
			}); 

		
EOD;
		
	} else if ($_SESSION['creditOrDirect'] == 1 && $_SESSION['membershipFees'] == 0) {
		
		$memberScript .= <<<EOD
			
			$('#mainTable').tablesorter({
				usNumberFormat: true,
				headers: {
					4: {
						sorter: "currency"
					},
					6: {
						sorter: "dates"
					}
				}
			}); 

		
EOD;
		
	} else if ($_SESSION['creditOrDirect'] == 0 && $_SESSION['membershipFees'] == 1) {
		
		$memberScript .= <<<EOD
			
			$('#mainTable').tablesorter({
				usNumberFormat: true,
				headers: {
					4: {
						sorter: "dates"
					},
					9: {
						sorter: "dates"
					}
				}
			}); 

		
EOD;
		
	} else {
		
		$memberScript .= <<<EOD
			
			$('#mainTable').tablesorter({
				usNumberFormat: true,
				headers: {
					3: {
						sorter: "dates"
					}
				}
			}); 

		
EOD;
		
	}
	
	$memberScript .= <<<EOD
		});
		
		$(window).resize(function() {
			$('#cloneTable').width($('#mainTable').width());
		});
		
EOD;


	pageStart("Launched clubs", NULL, $memberScript, "pmembership", NULL, "Launched clubs", $_SESSION['successMessage'], $_SESSION['errorMessage']);
?>

	<center><a href="launched-summary.php" class="cta1">Summary</a></center><br />
	 <table class='default' id='mainTable'>
	  <thead>	
	   <tr style='cursor: pointer;'>
	    <th>Year</th>
	    <th>Month</th>
	    <th># of clubs</th>
	   </tr>
	  </thead>
	  <tbody>
	  
	  <?php
	while ($user = $results->fetch()) {

		$year = $user['YEAR(launchdate)'];
		$month = $user['MONTH(launchdate)'];
		$number = $user['COUNT(id)'];

	
		echo sprintf("
  	  <tr class='clickableRow' href='clients.php?y=$year&m=$month' style='cursor: pointer;'>
  	   <td>%s</td>
  	   <td>%s</td>
  	   <td>%s</td>
  	  </tr>",
	  $year, $month, $number);
	  
	  
  }
?>

	 </tbody>
	 </table>

<?php  displayFooter(); ?>
