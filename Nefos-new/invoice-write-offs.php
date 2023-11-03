<?php
	
	require_once 'cOnfig/connection.php';
	require_once 'cOnfig/viewv6.php';
	require_once 'cOnfig/authenticate.php';
	require_once 'cOnfig/languages/common.php';
	session_start();
	$accessLevel = '3';
	

	if(isset($_GET['did'])){
			
			$id= $_GET['did'];
			$_SESSION['delete_payment_id'] = $id;

			// get old data of payments

		   	$selectOldInvoices =  "SELECT invoices FROM invoice_writeoffs WHERE id = ".$id;

		   	try
			{
				$old_results =  $pdo->prepare("$selectOldInvoices");
				$old_results->execute();
			}
			catch (PDOException $e)
			{
					$error = 'Error fetching user: ' . $e->getMessage();
					echo $error;
					exit();
			}
			$old_invoice_row = $old_results->fetch();
				$old_invoices = $old_invoice_row['invoices'];
				$old_invoice_arr = explode(",", $old_invoices);
			

			// update old data

			foreach ($old_invoice_arr as $old_invoice) {

					$updateOldInvoice = "UPDATE invoices2 SET writeOff = '0' , paid =''  WHERE invno = '".$old_invoice."'";

				try
				{
					$pdo->prepare("$updateOldInvoice")->execute();
				}
				catch (PDOException $e)
				{
						$error = 'Error fetching user: ' . $e->getMessage();
						echo $error;
						exit();
				}

			}

				// delete bank id
				
				$deleteElement = "DELETE FROM invoice_writeoffs where id = $id";
					try
					{
						$results = $pdo->prepare("$deleteElement");
						$results->execute();
					}
					catch (PDOException $e)
					{
							$error = 'Error fetching user: ' . $e->getMessage();
							echo $error;
							exit();
					}

				$_SESSION['successMessage'] = "Write Off Payment deleted successfully!";
				header("location: invoice-write-offs.php");
				exit();
			

	}

	// Query to look up users
	 $selectWriteOff = "SELECT * FROM invoice_writeoffs order by id DESC";
		try
		{
			$results = $pdo->prepare("$selectWriteOff");
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
			    exclude: ".noExl,.dropdown-filter-dropdown",
			    name: "Write Offs",
			    filename: "Write Offs" //do not include extension
		
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
			
			
			/*$('#mainTable').tablesorter({
				usNumberFormat: true,
				headers: {
					4: {
						sorter: "dates"
					}
				}
			}); */

		});
		
		$(window).resize(function() {
			$('#cloneTable').width($('#mainTable').width());
		});

	 function delete_element(delete_id){
      	 if(confirm('Are you sure to delete this write off payment ?')){
      	 	 window.location = "invoice-write-offs.php?did="+delete_id;
      	 }
      }

		
EOD;
// delete videos
	pageStart("Invoice Write Offs", NULL, $memberScript, "pmembership", NULL, "Invoice Write Offs", $_SESSION['successMessage'], $_SESSION['errorMessage']);
	
?>

<link rel="stylesheet" href="css/excel-bootstrap-table-filter-style.css">
<center>
	<a href='invoice-section.php' class='cta1'>Invoice Section</a>
	<a href='new-write-off.php' class='cta1'>Add Write Off</a>
</center>

         <center><a href="#" id="xllink" onClick="$('#mainTable').tableExport({type:'excel',escape:'false'});"><img src="images/excel-new.png" style='margin: 0 0 -5px 8px;'/></a></center>
<br />
<br />
		 <table class='default' id='mainTable'>
			  <thead>	
			   <tr style='cursor: pointer;'>
			    <th>Write Off #</th>
			    <th>Settled Date</th>
			    <th>Invoices</th>
			    <th>Comment</th>
			    <th dateformat="DD-MM-YYYY HH:mm:ss" isType="date" class="filter">Payment Created</th>
			    <th>Action</th>
			   </tr>
			  </thead>
			  <tbody>
			  
			  <?php
				while ($writeoff = $results->fetch()) {

					$id = $writeoff['id'];
					$settled_date = $writeoff['settled_date'];
					$settled_date = date("d-m-Y", strtotime($settled_date));
					$invoices = $writeoff['invoices'];
					$invoices = str_replace(",", "<br>", $invoices);
					$comment = $writeoff['comment'];
					$created_at = '';
					$updated_at = '';
					if($writeoff['created_at'] != ''){
						$created_at = date("d-m-Y H:i:s", strtotime($writeoff['created_at']));
					}
					if ($comment != '') {
	
						$commentRead = "
						                <img src='images/comments.png' id='comment$id' /><div id='helpBox$id' class='helpBox'>{$comment}</div>
						                <script>
						                  	$('#comment$id').on({
										 		'mouseover' : function() {
												 	$('#helpBox$id').css('display', 'block');
										  		},
										  		'mouseout' : function() {
												 	$('#helpBox$id').css('display', 'none');
											  	}
										  	});
										</script>
						                ";
						
					} else {
						
						$commentRead = "";
						
					}


					echo sprintf("
				  	    <tr><td class='right'>%d</td>
				  	    <td class='right'>%s</td>
				  	    <td>%s</td>
				  	    <td style='position:relative;'>%s</td>
				  	    <td>%s</td>
						<td><a href='javascript:void(0);' onClick='delete_element(%d)'><img src='images/delete.png' height='15' title='Delete Payment'></a></td>
						</tr>",
				  	 $id, $settled_date, $invoices, $commentRead, $created_at, $id
				  	);
				  }
				?>

			 </tbody>
			 </table>
<?php  displayFooter(); ?>
<script src="js/excel-bootstrap-table-filter-bundle.js"></script>
<script src="js/moment.js"></script>
<script type="text/javascript">
	$('#mainTable').excelTableFilter();
</script>