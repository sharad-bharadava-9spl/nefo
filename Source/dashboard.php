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

	$deleteExpenseScript = <<<EOD
	
	    $(document).ready(function() {
		    
$("#xllink").click(function(){

	  $("#mainTable").table2excel({
	    // exclude CSS class
	    exclude: ".noExl",
	    name: "Gastos",
	    filename: "Gastos" //do not include extension

	  });

	});
		    
		    
		    
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
					0: {
						sorter: "dates"
					},
					6: {
						sorter: "currency"
					}
				}
			}); 

		});
		

EOD;
	pageStart("Clients", NULL, $deleteExpenseScript, "pexpenses", "admin", "CLIENTS", $_SESSION['successMessage'], $_SESSION['errorMessage']);
	
?>


	 <table class="default" id="mainTable">
	  <thead>
	   <tr style='cursor: pointer;'>
	    <th>Club</th>
	    <th>Status</th>
	    <th>Members</th>
	    <th>Active members</th>
	    <th>Dispensed members</th>
	    <th># of dispenses</th>
	    <th>Last dispense</th>
	    <th>Dispensed</th>
	    <th>Revenue</th>
	    <th>Warning?</th>
	    <th>Cutoff</th>
	   </tr>
	  </thead>
	  <tbody>
	   <tr>
	    <td class='left'>059</td>
	    <td class='left'>Trial until 02/06/2018</td>
	    <td class='right'>6</td>
	    <td class='right'>4</td>
	    <td class='right'>0</td>
	    <td class='right'>0</td>
	    <td class='right'>04/03/2018</td>
	    <td class='right'>0 g</td>
	    <td class='right'>0 &euro;</td>
	    <td class='left'></td>
	    <td class='left'></td>
	   </tr>
	   <tr>
	    <td class='left'>1900</td>
	    <td class='left'>Client</td>
	    <td class='right'>755</td>
	    <td class='right'>70</td>
	    <td class='right'>69</td>
	    <td class='right'>193</td>
	    <td class='right'>27/05/2018</td>
	    <td class='right'>219 g</td>
	    <td class='right'>7.166 &euro;</td>
	    <td class='center negative'>Yes</td>
	    <td class='center negative'>08/06/2018</td>
	   </tr>
	   <tr>
	    <td class='left'>Abuelita Maria</td>
	    <td class='left'>Client</td>
	    <td class='right'>175</td>
	    <td class='right'>139</td>
	    <td class='right'>145</td>
	    <td class='right'>361</td>
	    <td class='right'>28/05/2018</td>
	    <td class='right'>614 g</td>
	    <td class='right'>11.884 &euro;</td>
	    <td class='left'></td>
	    <td class='left'></td>
	   </tr>
	   <tr>
	    <td class='left'>Acharya</td>
	    <td class='left negative'>Cut off 12/05/2018</td>
	    <td class='right'>1255</td>
	    <td class='right'>318</td>
	    <td class='right'>301</td>
	    <td class='right'>788</td>
	    <td class='right'>12/05/2018</td>
	    <td class='right'>1.668 g</td>
	    <td class='right'>14.226 &euro;</td>
	    <td class='center negative'>Yes</td>
	    <td class='center negative'>12/05/2018</td>
	   </tr>
	  
	  

	 </tbody>
	 </table>
	 
	 
<?php  displayFooter(); ?>
