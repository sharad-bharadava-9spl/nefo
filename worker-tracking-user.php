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
	
	$deleteDonationScript = <<<EOD
	
	    $(document).ready(function() {
		    
	  $( function() {
	    $( "#datepicker" ).datepicker({
			dateFormat: "dd-mm-yy"
	    });
	  });
	  $( function() {
	    $( "#datepicker2" ).datepicker({
			dateFormat: "dd-mm-yy"
	    });
	  });

$("#xllink").click(function(){

	  $("#mainTable").table2excel({
	    // exclude CSS class
	    exclude: ".noExl",
	    name: "Donaciones",
	    filename: "Donaciones" //do not include extension

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

if ($_SESSION['bankPayments'] == 1) {
	
	$deleteDonationScript .= <<<EOD
			$('#mainTable').tablesorter({
				usNumberFormat: true,
				headers: {
					0: {
						sorter: "dates"
					},
					5: {
						sorter: "currency"
					},
					6: {
						sorter: "currency"
					},
					7: {
						sorter: "currency"
					}
				}
			}); 
EOD;

} else {
	
	$deleteDonationScript .= <<<EOD
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
					}
				}
			}); 
EOD;

}

	$deleteDonationScript .= <<<EOD

		
			
		});
		
		$(window).resize(function() {
			$('#cloneTable').width($('#mainTable').width());
		});
		
function delete_donation(donationid,amount,userid) {
	if (confirm("{$lang['donation-deleteconfirm']}")) {
				window.location = "uTil/delete-donation.php?donationid=" + donationid + "&amount=" + amount + "&userid=" + userid + "&donscreen";
				}
}
EOD;

	pageStart($lang['worker-tracking'], NULL, $deleteDonationScript, "pmembership", NULL, $lang['worker-tracking'], $_SESSION['successMessage'], $_SESSION['errorMessage']);
	
	$user_id = $_GET['user_id'];

	$selectExpenses = "SELECT time, user_id, type, success, email, comment FROM logins WHERE user_id = $user_id ORDER BY time DESC";
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

?>

	 <table class='default' id='mainTable'>
	  <thead>
	   <tr style='cursor: pointer;'>
	    <th><?php echo $lang['pur-date']; ?></th>
	    <th><?php echo $lang['worker']; ?></th>
	    <th>Scan in</th>
	    <th>Scan out programada</th>
	    <th>Scan out real</th>
	    <th>Jornada programada</th>
	    <th>Jornada real</th>
	   </tr>
	  </thead>
	  <tbody>
	  
	  <?php

/*		$start_date = new DateTime('2019-06-04 10:00');
		$since_start = $start_date->diff(new DateTime('2019-06-04 11:40'));
		$hours = $since_start->h.'h ';
		$minutes = $since_start->i.'m';
		
		echo "HER: $hours $minutes";
	  
	  exit();*/
while ($donation = $results->fetch()) {
	$date = date("d-m-Y", strtotime($donation['time'] . "+$offsetSec seconds"));
	$scanin = date("H:i", strtotime($donation['time'] . "+$offsetSec seconds"));
	$scanoutprog = date("H:i", strtotime($donation['comment'] . "+$offsetSec seconds"));
	$outStamp = $donation['comment'];
	$outStampReal = $donation['email'];
	$inStamp = date("d-m-Y H:i", strtotime($donation['time']));
	
		$start_date = new DateTime($inStamp);
		$since_start = $start_date->diff(new DateTime($outStamp));
		$hours = $since_start->h.'h ';
		$minutes = $since_start->i.'m';
	
	if ($donation['email'] != '') {
		$scanout = date("H:i", strtotime($donation['email'] . "+$offsetSec seconds"));
		
		$start_date = new DateTime($inStamp);
		$since_start = $start_date->diff(new DateTime($outStampReal));
		$hoursReal = $since_start->h.'h ';
		$minutesReal = $since_start->i.'m';
	} else {
		$scanout = '';
		$hoursReal = '';
		$minutesReal = '';
	}
	
	$user_id = $donation['user_id'];
	$type = $donation['type'];
	$success = $donation['success'];
	

	$worker = getOperator($user_id);
	
	if ($type == 2 && $success == 0) {
		$autologout = "style='color: red;'";
	} else {
		$autologout = "";
	}
	
	
	$expense_row =	sprintf("
  	  <tr>
  	   <td class='left clickableRow' href='profile.php?user_id=$user_id'>%s</td>
  	   <td class='left clickableRow' href='profile.php?user_id=$user_id'>%s</td>
  	   <td class='centered clickableRow' href='profile.php?user_id=$user_id'>%s</td>
  	   <td class='centered clickableRow' href='profile.php?user_id=$user_id'>%s</td> 	   
  	   <td class='centered clickableRow' href='profile.php?user_id=$user_id' $autologout>%s</td> 	   
  	   <td class='centered clickableRow' href='profile.php?user_id=$user_id'>%s</td> 	   
  	   <td class='centered clickableRow' href='profile.php?user_id=$user_id'>%s</td> 	   
	  </tr>",
	  $date, $worker, $scanin, $scanoutprog, $scanout, $hours . $minutes,  $hoursReal . $minutesReal
	  );
	
			
	  echo $expense_row;
	

  }
?>

	 </tbody>
	 </table>

   
<?php displayFooter(); ?>
