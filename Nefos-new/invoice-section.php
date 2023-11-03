<?php
	
	require_once 'cOnfig/connection.php';
	require_once 'cOnfig/viewv6.php';
	require_once 'cOnfig/authenticate.php';
	require_once 'cOnfig/languages/common.php';
	session_start();
	$accessLevel = '3';
	
	 if(isset($_SESSION['lang'])){
	 	$current_lang = $_SESSION['lang'];
	 }else{
	 	$current_lang = 'en';
	 }

	
	
	$memberScript = <<<EOD
	
	    $(document).ready(function() {
		    
		    
			$("#xllink").click(function(){

			  $("#mainTable").table2excel({
			    // exclude CSS class
			    exclude: ".noExl",
			    name: "Help-videos",
			    filename: "Help-videos" //do not include extension
		
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
	pageStart("Invoice Section", NULL, $memberScript, "pmembership", NULL, "Invoice Section", $_SESSION['successMessage'], $_SESSION['errorMessage']);
	
?>
<style type="text/css">
	.cta1{
		font-size: 14px;
		/*width: auto;;*/
	}
</style>
<center>
	<a href='invoice-elements.php' class='cta1'>Invoice Elements</a>
	<a href='credits.php' class='cta1'>Client Credit</a>
	<a href='customer-debits.php' class='cta1'>Customer Debit</a>
	<a href='invoices.php' class='cta1'>Invoices</a>
	<a href='new-invoice.php?type=sw' class='cta1'>New Software Invoice</a>
	<a href='new-invoice.php?type=hw' class='cta1'>New Hardware Invoice</a>
	<a href='invoice-payments.php' class='cta1'>Invoice Payments</a>
	<a href='invoice-batch.php' class='cta1'>Invoice Batch Process</a>
	<a href='new-credit-note.php?type=sw' class='cta1'>New Credit Note</a>
	<a href='invoice-write-offs.php' class='cta1'>Invoice Write Offs</a>
	<a href='dl-periodical-invoices.php' class='cta1'>Download Periodical Invoices</a>
	
   
</center>


        <!--  <a href="#" id="xllink" onClick="$('#mainTable').tableExport({type:'excel',escape:'false'});"><img src="images/excel-new.png" style='margin: 0 0 -5px 8px;'/></a> -->
<br />
<br />

<?php  displayFooter();