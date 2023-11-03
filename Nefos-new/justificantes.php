<?php

	require_once 'cOnfig/connection.php';
	require_once 'cOnfig/viewv6.php';
	require_once 'cOnfig/authenticate.php';
	require_once 'cOnfig/languages/common.php';
	
	// promisetype: 0 = old, uncatregorized, 1 = from SW, 2 = direct link (SMS), 3 = 24-hour extension
	// justificantetype: 0 = system, 1 = link from mobile SMS
	
	session_start();
	$accessLevel = '3';
	
	// Authenticate & authorize
	authorizeUser($accessLevel);
	
	$sortScript = <<<EOD
	
	    $(document).ready(function() {
			
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
					7: {
						sorter: "dates"
					}
				},
				sortList: [[0,1]]
			}); 


		
			
		});


EOD;
	
	pageStart("Justificantes", NULL, $sortScript, "pmembership", NULL, "Justificantes", $_SESSION['successMessage'], $_SESSION['errorMessage']);
	
	// Loop through all files in folder
	$files = scandir("/var/www/html/ccsnubev2_com/v6/justificantes");
	$i = 0;
	foreach ($files as $file) {
		
		if ($file != '.' && $file != '..') {
			
			//$dbname = substr($file, 0, 24);
			$dbname = "justificantes/$file";
			
			$query = "SELECT invno, paid, invdate, amount, customer, justificantetime, justificantetype, verified, justificanteamount FROM invoices WHERE justificante = '$dbname'";
			try
			{
				$results = $pdo->prepare("$query");
				$results->execute();
			}
			catch (PDOException $e)
			{
					$error = 'Error fetching user: ' . $e->getMessage();
					echo $error;
					exit();
			}
			
			$invprovided = '';
		
			while ($row = $results->fetch()) {
				
				$invno = $row['invno'];
				$paid = $row['paid'];
				$invdate = date("d-m-Y", strtotime($row['invdate']));
				$amount = $row['amount'];
				$customer = $row['customer'];
				$justificantetime = date("d-m-Y H:i", strtotime($row['justificantetime']));
				$justificantetype = $row['justificantetype'];
				$verified = $row['verified'];
				$justificanteamount = $row['justificanteamount'];
				
				if ($justificanteamount == 0) {
					$justificanteamount = '';
				}
				
				
				
				if ($justificantetime == '01-01-1970 01:00') {
					$justificantetime = "<span style='color: #fff;'>00-00-0000</span>";
				} else {
					$justificantetime = $justificantetime;
				}
				
				if ($justificantetype == '1') {
					$justificantetype = "SMS";
				} else {
					$justificantetype = "SW";
				}
				
				if ($verified == '1') {
					$icon = "<img src='images/image.png' width='15' target='_blank' />";
				} else if ($verified == '2') {
					$icon = "<img src='images/image-red.png' width='15' target='_blank' />";
				} else {
					$icon = "<img src='images/image-orange.png' width='15' target='_blank' />";
				}				
				
				$query = "SELECT shortName FROM customers WHERE number = '$customer'";
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
					$shortName = $row['shortName'];
					
			if ($verified == 0) {
				
				$justificanteFull = "<a href='verify-justificante.php?invno=$invno&src=justificantes'><img src='images/image-orange.png' width='15' /></a><span style='display:none'>1</span>";
				//$justificante = "<a href='https://www.ccsnubev2.com/v6/$justificante'><img src='images/image-orange.png' width='15' /></a><span style='display:none'>1</span>";
				
			} else if ($verified == 1) {
				
				$justificanteFull = "<a href='https://ccsnubev2.com/v6/$dbname'><img src='images/image.png' width='15' target='_blank' /></a><span style='display:none'>2</span>";
				
			} else if ($verified == 2) {
				
				$justificanteFull = "<a href='https://ccsnubev2.com/v6/$dbname'><img src='images/image-red.png' width='15' target='_blank' /></a><span style='display:none'>3</span>";
				
			}
					
			
		$query = "SELECT COUNT(invno), SUM(amount) FROM invoices WHERE paid = '' AND brand = 'SW' AND customer = '$customer'";

		try
		{
			$result = $pdo->prepare("$query");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
			$invs = $row['COUNT(invno)'];
			$totAmount = number_format($row['SUM(amount)'],2);
			
			
				$tableRow .= "<tr><td>$justificantetime</td><td>$justificantetype</td><td>$customer</td><td>$shortName</td><td class='centered'>$invs</td><td class='right'>$totAmount &euro;</td><td>$invno</td><td>$invdate</td><td class='right'>$amount &euro;</td><td class='right'>$justificanteamount &euro;</td><td>$paid</td><td class='centered'>$justificanteFull</td></tr>";
				
				$invprovided = 'yes';

				
			}
						
		}
		
	}
		
	// Look up details for each and provide link
	
		
	
?>

<div id='mainbox-new-club' style='width: initial;'>
 <div id='mainboxheader'>
  <center>
   Justificantes received
  </center>
 </div>
 <div class='boxcontent'>
   <table class='default' id='mainTable'>
    <thead>
     <tr>
      <th>Time</th>
      <th>Type</th>
      <th>#</th>
      <th>Customer</th>
      <th>Invoices</th>
      <th>Amount</th>
      <th>Inv #</th>
      <th>Inv date</th>
      <th>Inv amount</th>
      <th>Amount paid</th>
      <th>Status</th>
	  <th class='noExl'>Justificante</th>
     </tr>
    </thead>
    <tbody>
     <?php echo $tableRow; ?>
    </tbody>
   </table>
  </center>
 </div>
</div>

<?php

displayFooter();