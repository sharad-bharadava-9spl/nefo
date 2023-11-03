<?php

	require_once 'cOnfig/connection.php';
	require_once 'cOnfig/viewv6.php';
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

 <center><a href="#" id="fullList"><img src="images/excel-new.png" style='margin: 0 0 -5px 8px;'/></a></center><br>
<br />
	

<script type="text/javascript">
	$("#fullList").click(function(){
	    $("#load").show();
	  
	    var url = 'inv-report.php?count=0&totalCount=0';
	    window.open(url, "Invoices Report","height=300,width=300,modal=yes,alwaysRaised=yes");
	    setTimeout(function () {
	        $("#load").hide();
	    }, 2000);    
	 });
</script>
