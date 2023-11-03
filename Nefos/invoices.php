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
					3: {
						sorter: "dates"
					},
					4: {
						sorter: "currency"
					}
				}
			}); 
			
		}); 
		
EOD;


	pageStart("Invoices", NULL, $memberScript, "pmembership", NULL, "Invoices", $_SESSION['successMessage'], $_SESSION['errorMessage']);
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
	    <th class='centered'>Inv #</th>
	    <th class='centered'>Inv date</th>
	    <th class='centered'>Amount</th>
	    <th class='centered'>Brand</th>
	    <th class='centered'>Status</th>
	    <th class='centered noExl'>PDF</th>
	   </tr>
	  </thead>
	  <tbody>
	  
	  
<?php

		$query = "SELECT * from invoices WHERE DATE(invdate) > '2017-12-31' ORDER BY invdate DESC";
		// $query = "SELECT * from invoices WHERE invno LIKE ('%180%') OR invno LIKE ('%M1%') ORDER BY invdate DESC";
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
	
		while ($row = $results->fetch()) {
			
			$invno = $row['invno'];		
			$paid = $row['paid'];		
			$invdate = date("d-m-Y", strtotime($row['invdate']));
			$amount = $row['amount'];		
			$customer = $row['customer'];		
			$brand = $row['brand'];
	
			// Look up customer details: name and domain
			$selectUsersU = "SELECT shortName FROM customers WHERE number = '$customer'";
			try
			{
				$result = $pdo2->prepare("$selectUsersU");
				$result->execute();
			}
			catch (PDOException $e)
			{
					$error = 'Error fetching user: ' . $e->getMessage();
					echo $error;
					exit();
			}
			
			$rowX = $result->fetch();
				$shortName = $rowX['shortName'];
			
			$query = "SELECT domain from db_access WHERE customer = '$customer'";
			try
			{
				$resultsY = $pdo->prepare("$query");
				$resultsY->execute();
			}
			catch (PDOException $e)
			{
					$error = 'Error fetching user: ' . $e->getMessage();
					echo $error;
					exit();
			}
		
			$rowY = $resultsY->fetch();
				$domain = $rowY['domain'];
				
			// if first letter is 2, else
			if (substr($invno, 0, 1) == 'M') {
				
				$invfile = "../../ccsnubev2_com/_club/_$domain/invoices/$invno.pdf";
				$invfileFull = "https://ccsnubev2.com/_club/_$domain/invoices/$invno.pdf";
				$invfile2 = "../../ccsnubev2_com/v6/_club/_$domain/invoices/$invno.pdf";
				$invfile2Full = "https://ccsnubev2.com/v6/_club/_$domain/invoices/$invno.pdf";
//				echo "M: $customer - $invno<br />";
//				echo "invfileFull: $invfileFull<br />";
//				echo "invfileFull2: $invfileFull2<br /><br />";
				
			} else if (substr($invno, 0, 1) == '1') {
				
				$invfile = "../../ccsnubev2_com/_club/_$domain/invoices/$customer-$invno.pdf";
				$invfileFull = "https://ccsnubev2.com/_club/_$domain/invoices/$customer-$invno.pdf";
				$invfile2 = "../../ccsnubev2_com/v6/_club/_$domain/invoices/$customer-$invno.pdf";
				$invfile2Full = "https://ccsnubev2.com/v6/_club/_$domain/invoices/$customer-$invno.pdf";
//				echo "1: $customer - $invno<br />";
//				echo "invfileFull: $invfileFull<br />";
//				echo "invfileFull2: $invfileFull2<br /><br />";
				
			} else {
				
				$brandShort = substr($brand, 0, 2);
				$invfile = "../../ccsnubev2_com/_club/_$domain/invoices/$customer-$invno-$brandShort.pdf";
				$invfileFull = "https://ccsnubev2.com/_club/_$domain/invoices/$customer-$invno-$brandShort.pdf";
				$invfile2 = "../../ccsnubev2_com/v6/_club/_$domain/invoices/$customer-$invno-$brandShort.pdf";
				$invfile2Full = "https://ccsnubev2.com/v6/_club/_$domain/invoices/$customer-$invno-$brandShort.pdf";
//				echo "2: $customer - $invno<br />";
//				echo "invfileFull: $invfileFull<br />";
//				echo "invfile2Full: $invfile2Full<br /><br />";
			
			}
										
			if (file_exists($invfile)) {
				
				$invlink = "<a href='$invfileFull'><img src='images/pdf.png' /><span style='display:none'>1</span></a>";
				
			} else if (file_exists($invfile2)) {
				
				$invlink = "<a href='$invfile2Full'><img src='images/pdf.png' /><span style='display:none'>1</span></a>";
				
			} else {
				
				$invlink = "";
				
			}
				
			echo sprintf("
  	  <tr>
  	   <td class=''>%s</td>
  	   <td class=''>%s</td>
  	   <td class=''>%s</td>
  	   <td class=''>%s</td>
  	   <td class='right'>%s</td>
  	   <td class='centered'>%s</td>
  	   <td class='centered'>%s</td>
  	   <td class='centered noExl'>%s</td>
</tr>",
	  $customer, $shortName, $invno, $invdate, $amount, $brand, $paid, $invlink);
			
		}