<?php
	
	require_once 'cOnfig/connection.php';
	require_once 'cOnfig/viewv6.php';
	require_once 'cOnfig/authenticate.php';
	require_once 'cOnfig/languages/common.php';
	session_start();
	$accessLevel = '3';

	authorizeUser($accessLevel);

	$memberScript = <<<EOD
	
	    $(document).ready(function() {
		    
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
					4: {
						sorter: "dates"
					}
				}
			}); 

		});
		
		$(window).resize(function() {
			$('#cloneTable').width($('#mainTable').width());
		});

		
EOD;
// delete videos
	pageStart("Invoice Batch Process", NULL, $memberScript, "pmembership", NULL, "Invoice Batch Process", $_SESSION['successMessage'], $_SESSION['errorMessage']);
	
?>

<center>
	<a href='batch-process.php?count=0&totalCount=0' class='cta4'>Run Batch Process</a>
</center>

<br />
<br />

<?php  displayFooter();