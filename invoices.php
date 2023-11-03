<?php
	
	require_once 'cOnfig/connection.php';
	require_once 'cOnfig/view.php';
	require_once 'cOnfig/authenticate.php';
	require_once 'cOnfig/languages/common.php';
	
	session_start();
	$accessLevel = '1';
	
	// Authenticate & authorize
	authorizeUser($accessLevel);
	
	$domain = $_SESSION['domain'];
	
	$disableArrowKeys = <<<EOD
	
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
					}
				},
				sortList: [[0,1]]
			}); 


		
			
		});


EOD;


	if ($_SESSION['lang'] == 'es') {
		pageStart("CCS | Facturación", NULL, $disableArrowKeys, "pdispense", "menu", "FACTURACIÓN", $_SESSION['successMessage'], "¡Pagando facturas es IMPRESCINDIBLE poner NUMERO DE CLIENTE y NUMERO DE FACTURA!</strong></center><center><a href='dl-invoices.php' class='cta1'>Descargar todas</a></center>");
	} else {
		pageStart("CCS | Invoicing", NULL, $disableArrowKeys, "pdispense", "menu", "INVOICING", $_SESSION['successMessage'], "When paying invoices it is MANDATORY stating CLIENT NUMBER and INVOICE NUMBER!</strong></center><center><a href='dl-invoices.php' class='cta1'>Download all</a></center>");
	}	
?>

<table class="default" id="mainTable">
 <thead>
  <tr style='cursor: pointer;'>
   <th><?php echo $lang['pur-date']; ?></th>
   <th><?php echo $lang['global-invoice']; ?></th>
   <th><?php echo $lang['amount']; ?></th>
   <th><?php echo $lang['status']; ?></th>
  </tr>
 </thead>
 <tbody>

<?php

	$files = scandir("_club/_$domain/invoices/");
	$i = 0;
	foreach ($files as $file) {
		
		if (strpos($file, 'pdf') !== false) {
			
			
			// Simplified invoices
			if (substr($file, 6, 1) == 'S') {
				$invoice = substr($file, 6);
				$invoice = substr($invoice, 0, 8);
								
			// Old SW invoices
			} else			// Invoices from 2020
			if (substr($file, -5, 1) == 'W') {
				
				$invoice = substr($file, 6);
				$invoice = substr($invoice, 0, 7);
				
			// Old HW invoices
			} else if (substr($file, 0, 1) != 'M') {
				
				$invoice = substr($file, 6);
				$invoice = substr($invoice, 0, -4);
								
			} else {
				
				$invoice = $file;
				$invoice = substr($invoice, 0, -4);
				
			}

			try
			{
				$result = $pdo->prepare("SELECT invdate, paid, amount FROM invoices WHERE invno = '$invoice'");
				$result->execute();
			}
			catch (PDOException $e)
			{
					$error = 'Error fetching user: ' . $e->getMessage();
					echo $error;
					exit();
			}
		
			$row = $result->fetch();
				$paid = $row['paid'];
				$date = $row['invdate'];
				$amount = number_format($row['amount'],2);
				
			if ($paid == '') {
				$paidFlag = "<span style='color: red;'><strong>{$lang['member-notpaid']}</strong></span>";
			
			} else {
				
				if ($_SESSION['lang'] == 'en') {
								
					$paidFlag = $paid;
					
				} else {
					
					if ($paid == 'Write off' || $paid == 'Write Off') {
						$paidFlag = 'Anulado';
					} else if ($paid == 'Credited') {
						$paidFlag = 'Abonado';
					} else if ($paid == 'Credit note') {
						$paidFlag = 'Abonado';
					} else if ($paid == 'Paid') {
						$paidFlag = 'Pagado';
					}
					
				}
			}
			
				$date = date("d-m-Y", strtotime($date));
//				$date = "02-04-2019";
			
		echo "
  <tr>
   <td><a href='_club/_$domain/invoices/$file' style='color: #444;'>$date</a></td>
   <td><a href='_club/_$domain/invoices/$file' style='color: #444;'>$file</a></td>
   <td><a href='_club/_$domain/invoices/$file' style='color: #444;'>$amount {$_SESSION['currencyoperator']}</a></td>
   <td><a href='_club/_$domain/invoices/$file' style='color: #444;'>$paidFlag</a></td>
  </tr>";
		
  $i++;
		}
		
		
	}
		


?>


	 </tbody>
	 </table>
	
	

<?php displayFooter(); ?>

