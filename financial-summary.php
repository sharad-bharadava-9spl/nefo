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
	
	// If form submits, there will ALAWYS be a untilDate set!
	// Check if 'entre fechas' was utilised
	if (isset($_POST['untilDate'])) {
		
		$firstLoad = 'false';
		
		$fromDate = date("Y-m-d", strtotime($_POST['fromDate']));
		$untilDate = date("Y-m-d", strtotime($_POST['untilDate']));
		
		$timeLimit = "AND DATE(donationTime) BETWEEN DATE('$fromDate') AND DATE('$untilDate')";
		$timeLimit2 = "AND DATE(paymentdate) BETWEEN DATE('$fromDate') AND DATE('$untilDate')";
		$timeLimit3 = "DATE(donationTime) BETWEEN DATE('$fromDate') AND DATE('$untilDate')";
		$timeLimit4 = "DATE(paymentdate) BETWEEN DATE('$fromDate') AND DATE('$untilDate')";
		$timeLimit5 = "AND DATE(saletime) BETWEEN DATE('$fromDate') AND DATE('$untilDate')";
		$timeLimit6 = "DATE(saletime) BETWEEN DATE('$fromDate') AND DATE('$untilDate')";
		
		
		if ($_POST['cashBox'] != 'a') {
			
			$cashLimit = "AND donatedTo = 2";
			$cashLimit2 = "AND paidTo = 2";
			$cashLimit3 = "AND direct = 2";
			
		}
		
		if ($_POST['cardBox'] != 'a') {
			
			$cashLimit .= " AND (donatedTo < 2 OR donatedTo = 4)";
			$cashLimit2 .= " AND (paidTo < 2 OR paidTo = 4)";
			$cashLimit3 .= " AND direct < 2";
			
		}

			
	} else {
		
		$firstLoad = 'true';
		
		$nowDate = date("d-m-Y");
		
		$timeLimit = "AND DATE(donationTime) = DATE(NOW())";
		$timeLimit2 = "AND DATE(paymentdate) = DATE(NOW())";
		$timeLimit3 = "DATE(donationTime) = DATE(NOW())";
		$timeLimit4 = "DATE(paymentdate) = DATE(NOW())";
		$timeLimit5 = "AND DATE(saletime) = DATE(NOW())";
		$timeLimit6 = "DATE(saletime) = DATE(NOW())";
		
	}
		
	// Look up todays donations
	$selectDonations = "SELECT SUM(amount) from donations WHERE (donatedTo < 2 OR donatedTo = 4) $timeLimit $cashLimit";
		try
		{
			$result = $pdo3->prepare("$selectDonations");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
		$donations = $row['SUM(amount)'];
		
	// Look up today's membership fees
	$selectMembershipFees = "SELECT SUM(amountPaid) FROM memberpayments WHERE (paidTo < 2 OR paidTo = 4) $timeLimit2 $cashLimit2";
		try
		{
			$result = $pdo3->prepare("$selectMembershipFees");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
		$membershipFees = $row['SUM(amountPaid)'];
		
	// Look up todays bank donations
	$selectDonations = "SELECT SUM(amount) from donations WHERE donatedTo = 2 $timeLimit $cashLimit";
		try
		{
			$result = $pdo3->prepare("$selectDonations");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
		$bankDonations = $row['SUM(amount)'];
		
		
	// Look up today's membership fees Bank
	$selectMembershipFees = "SELECT SUM(amountPaid) FROM memberpayments WHERE paidTo = 2 $timeLimit2 $cashLimit2";
		try
		{
			$result = $pdo3->prepare("$selectMembershipFees");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
		$membershipfeesBank = $row['SUM(amountPaid)'];
		
		// Direct Dispensing
	if ($_SESSION['creditOrDirect'] == 0) {
		$selectExpenses = "
	SELECT '1' AS type, donationTime AS time, userid, amount, donatedTo FROM donations WHERE $timeLimit3 $cashLimit UNION ALL 
	SELECT '2' AS type, paymentdate AS time, userid, amountPaid AS amount, paidTo AS donatedTo FROM memberpayments WHERE $timeLimit4 $cashLimit2 UNION ALL 
	SELECT '3' AS type, saletime AS time, userid, amount, direct AS donatedTo FROM sales WHERE $timeLimit6 UNION ALL 
	SELECT '4' AS type, saletime AS time, userid, amount, direct AS donatedTo FROM b_sales WHERE $timeLimit6 ORDER BY time DESC";

	} else {
	// Query to look up list of donations & membership fees
	$selectExpenses = "SELECT '1' AS type, donationTime AS time, userid, amount, donatedTo FROM donations WHERE $timeLimit3 $cashLimit UNION ALL SELECT '2' AS type, paymentdate AS time, userid, amountPaid AS amount, paidTo AS donatedTo FROM memberpayments WHERE $timeLimit4 $cashLimit2";
	
}

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
		
		// Direct Dispensing
		if ($_SESSION['creditOrDirect'] == 0) {
							
			// Look up dispensed today cash
			$selectSales = "SELECT SUM(amount) from sales WHERE direct < 2 $timeLimit5";
		try
		{
			$result = $pdo3->prepare("$selectSales");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
				$salesTodayCash = $row['SUM(amount)'];
		
			// Look up dispensed today bank
			$selectSales = "SELECT SUM(amount) from sales WHERE direct = 2 $timeLimit5 $cashLimit3";
		try
		{
			$result = $pdo3->prepare("$selectSales");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
				$salesTodayBank = $row['SUM(amount)'];
					
			// Look up bar sales today cash
			$selectSales = "SELECT SUM(amount) from b_sales WHERE direct < 2 $timeLimit5";
		try
		{
			$result = $pdo3->prepare("$selectSales");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
				$salesTodayBarCash = $row['SUM(amount)'];
		
			// Look up bar sales today bank
			$selectSales = "SELECT SUM(amount) from b_sales WHERE direct = 2 $timeLimit5";
		try
		{
			$result = $pdo3->prepare("$selectSales");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
				$salesTodayBarBank = $row['SUM(amount)'];
					
				
		}
	



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

		    
			$('#cloneTable').width($('#t2').width());
			
			
			$('.default').tablesorter({
				usNumberFormat: true,
				headers: {
					0: {
						sorter: "dates"
					},

					5: {
						sorter: "currency"
					}
				}
			}); 

			
EOD;

	$deleteDonationScript .= <<<EOD

		
			
		});
		
var tablesToExcel = (function () {
    var uri = 'data:application/vnd.ms-excel;base64,'
    , template = '<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns="http://www.w3.org/TR/REC-html40"><head><!--[if gte mso 9]><xml><x:ExcelWorkbook><x:ExcelWorksheets>'
    , templateend = '</x:ExcelWorksheets></x:ExcelWorkbook></xml><![endif]--><meta http-equiv="content-type" content="text/plain; charset=UTF-8"/></head>'
    , body = '<body>'
    , tablevar = '<table>{table'
    , tablevarend = '}</table>'
    , bodyend = '</body></html>'
    , worksheet = '<x:ExcelWorksheet><x:Name>'
    , worksheetend = '</x:Name><x:WorksheetOptions><x:DisplayGridlines/></x:WorksheetOptions></x:ExcelWorksheet>'
    , worksheetvar = '{worksheet'
    , worksheetvarend = '}'
    , base64 = function (s) { return window.btoa(unescape(encodeURIComponent(s))) }
    , format = function (s, c) { return s.replace(/{(\w+)}/g, function (m, p) { return c[p]; }) }
    , wstemplate = ''
    , tabletemplate = '';

    return function (table, name, filename) {
        var tables = table;

        for (var i = 0; i < tables.length; ++i) {
            wstemplate += worksheet + worksheetvar + i + worksheetvarend + worksheetend;
            tabletemplate += tablevar + i + tablevarend;
        }

        var allTemplate = template + wstemplate + templateend;
        var allWorksheet = body + tabletemplate + bodyend;
        var allOfIt = allTemplate + allWorksheet;

        var ctx = {};
        for (var j = 0; j < tables.length; ++j) {
            ctx['worksheet' + j] = name[j];
        }

        for (var k = 0; k < tables.length; ++k) {
            var exceltable;
            if (!tables[k].nodeType) exceltable = document.getElementById(tables[k]);
            ctx['table' + k] = exceltable.innerHTML;
        }

        //document.getElementById("dlink").href = uri + base64(format(template, ctx));
        //document.getElementById("dlink").download = filename;
        //document.getElementById("dlink").click();

        window.location.href = uri + base64(format(allOfIt, ctx));

    }
})();
		
		$(window).resize(function() {
			$('#cloneTable').width($('#mainTable').width());
		});
		
function delete_donation(donationid,amount,userid) {
	if (confirm("{$lang['donation-deleteconfirm']}")) {
				window.location = "uTil/delete-donation.php?donationid=" + donationid + "&amount=" + amount + "&userid=" + userid + "&donscreen";
				}
}
EOD;
			
	pageStart($lang['financial-summary'], NULL, $deleteDonationScript, "pprofile", "statutes", $lang['financial-summary'], $_SESSION['successMessage'], $_SESSION['errorMessage']);


?>
		<center><img src="images/excel-new.png" style="cursor: pointer;" onClick="loadExcel();"  value="Export to Excel" /></center><br /><br />
 <center>
	 <div id="filterbox" >
			<div id="mainboxheader"><?php echo $lang['filter']; ?></div> 
				<div class="boxcontent">
		        <form action='' method='POST'>
		<?php
			if (isset($_POST['fromDate'])) {
				
				echo <<<EOD
				 <input type="text" id="datepicker" name="fromDate" autocomplete="nope" class="sixDigit defaultinput" value="{$_POST['fromDate']}" />
				 <input type="text" id="datepicker2" name="untilDate" autocomplete="nope" class="sixDigit defaultinput" value="{$_POST['untilDate']}" onchange='this.form.submit()' />
				 <button type="submit" class="cta2" style="display: inline-block; width: 40px;">OK</button>
		EOD;
				
			} else {
				
				
				echo <<<EOD
				 <input type="text" id="datepicker" name="fromDate" autocomplete="nope" class="sixDigit defaultinput" value="$nowDate" />
				 <input type="text" id="datepicker2" name="untilDate" autocomplete="nope" class="sixDigit defaultinput" value="$nowDate" onchange='this.form.submit()' />
				 <button type="submit" class="cta2" style="display: inline-block; width: 40px;">OK</button>
		EOD;

			}
			
			
		?>
				<div style="text-align: left; padding-left: 55px;">
					<br>
					 <span>
					<?php if ($_POST['cashBox'] == 'a' || $firstLoad == 'true') { ?>

						<div class='fakeboxholder'>	
						 <label class="control">
						  &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; <?php echo $lang['cash']; ?>
						  <input type="checkbox" name="cashBox" id="accept2" value='a' checked onchange='this.form.submit()' />
						  <div class="fakebox"></div>
						 </label>
						</div>
						<br>
					<?php } else { ?>

						<div class='fakeboxholder'>	
						 <label class="control">
						  &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; <?php echo $lang['cash']; ?>
						  <input type="checkbox" name="cashBox" id="accept2" value='a' onchange='this.form.submit()' />
						  <div class="fakebox"></div>
						 </label>
						</div>
						<br>
					<?php } ?>

						
						
					<?php if ($_POST['cardBox'] == 'a' || $firstLoad == 'true') { ?>

						<div class='fakeboxholder'>	
						 <label class="control">
						  &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?php echo $lang['bank-card']; ?>
						  <input type="checkbox" name="cardBox" id="accept2" value='a' checked onchange='this.form.submit()' />
						  <div class="fakebox"></div>
						 </label>
						</div>
						<br>
					<?php } else { ?>
						
						<div class='fakeboxholder'>	
						 <label class="control">
						  &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?php echo $lang['bank-card']; ?>
						  <input type="checkbox" name="cardBox" id="accept2" value='a' onchange='this.form.submit()' />
						  <div class="fakebox"></div>
						 </label>
						</div>
							<br>
					<?php } ?>
						</span>
					</div>
		        </form>
		        </div>
		       </td>
		      </tr>
    </div>
		      
     </div>
 </center>    
<br>
	 <table class='default' id='t1'>
	  <thead>
	   <tr>
	    <th></th>
	    <th class='right'><?php echo $lang['cash']; ?></th>
  		<th class='right'><?php echo $lang['bank-card']; ?></th>
	    <th class='right'><strong>TOTAL</strong></th>
	   </tr>
	  </thead>
	  <tbody>
  	   <tr>
  	    <td class='left'><?php echo $lang['fees']; ?></td>
  	    <td class='right'><?php echo number_format($membershipFees,2); ?> <?php echo $_SESSION['currencyoperator'] ?></td>
  	    <td class='right'><?php echo number_format($membershipfeesBank,2); ?> <?php echo $_SESSION['currencyoperator'] ?></td>
  	    <td class='right'><strong><?php echo number_format($membershipFees + $membershipfeesBank,2); ?> <?php echo $_SESSION['currencyoperator'] ?></strong></td>
  	   </tr>
  	   <tr>
  	    <td class='left'><?php echo $lang['global-donations']; ?></td>
  	    <td class='right'><?php echo number_format($donations,2); ?> <?php echo $_SESSION['currencyoperator'] ?></td>
  	    <td class='right'><?php echo number_format($bankDonations,2); ?> <?php echo $_SESSION['currencyoperator'] ?></td>
  	    <td class='right'><strong><?php echo number_format($donations + $bankDonations,2); ?> <?php echo $_SESSION['currencyoperator'] ?></strong></td>
  	   </tr>
<?php if ($_SESSION['creditOrDirect'] == 0) { ?>
  	   <tr>
  	    <td class='left'><?php echo $lang['direct-dispenses']; ?></td>
  	    <td class='right'><?php echo number_format($salesTodayCash,2); ?> <?php echo $_SESSION['currencyoperator'] ?></td>
  	    <td class='right'><?php echo number_format($salesTodayBank,2); ?> <?php echo $_SESSION['currencyoperator'] ?></td>
  	    <td class='right'><strong><?php echo number_format($salesTodayCash + $salesTodayBank,2); ?> <?php echo $_SESSION['currencyoperator'] ?></strong></td>
  	   </tr>
  	   <tr>
  	    <td class='left'><?php echo $lang['direct-bar-sales']; ?></td>
  	    <td class='right'><?php echo number_format($salesTodayBarCash,2); ?> <?php echo $_SESSION['currencyoperator'] ?></td>
  	    <td class='right'><?php echo number_format($salesTodayBarBank,2); ?> <?php echo $_SESSION['currencyoperator'] ?></td>
  	    <td class='right'><strong><?php echo number_format($salesTodayBarCash + $salesTodayBarBank,2); ?> <?php echo $_SESSION['currencyoperator'] ?></strong></td>
  	   </tr>
<?php } ?>
  	   <tr>
  	    <td class='left'><strong>Total</strong></td>
  	    <td class='right'><strong><?php echo number_format($membershipFees + $donations + $salesTodayCash + $salesTodayBarCash,2); ?> <?php echo $_SESSION['currencyoperator'] ?></strong></td>
  	    <td class='right'><strong><?php echo number_format($membershipfeesBank + $bankDonations + $salesTodayBank + $salesTodayBarBank,2); ?> <?php echo $_SESSION['currencyoperator'] ?></strong></td>
  	    <td class='right'><strong><?php echo number_format($membershipfeesBank + $bankDonations + $membershipFees + $donations + $salesTodayCash + $salesTodayBarCash + $salesTodayBank + $salesTodayBarBank,2); ?> <?php echo $_SESSION['currencyoperator'] ?></strong></td>
  	   </tr>
	  </tbody>
	 </table>

   
<?php 

	echo <<<EOD
	<br /><br />
	 <table class='default' id='t2'>
	  <thead>
	   <tr style='cursor: pointer;'>
	    <th style='padding: 10px;'>{$lang['global-time']}</th>
	    <th style='padding: 10px;'>{$lang['global-type']}</th>
  		<th style='padding: 10px;'>{$lang['paid-by']}</th>
	    <th style='padding: 10px;'>#</th>
	    <th style='padding: 10px;'>{$lang['global-member']}</th>
	    <th style='padding: 10px;'>{$lang['global-amount']}</th>
	   </tr>
	  </thead>
	  <tbody>
EOD;

		while ($donation = $results->fetch()) {
	
	$id = $donation['id'];
	$donationTime = date("d-m-Y H:i", strtotime($donation['time'] . "+$offsetSec seconds"));
	$user_id = $donation['userid'];
	$amount = $donation['amount'];
	$type = $donation['type'];
	$donatedTo = $donation['donatedTo'];
	
	if ($donatedTo == '2') {
		$donatedTo = $lang['bank-card'];
	} else if ($donatedTo == '3') {
		$donatedTo = '';
	} else {
		$donatedTo = $lang['cash'];
	}
	
	if ($type == 1) {
		$movementType = $lang['donation-donation'];
	} else if ($type == 2) {
		$movementType = $lang['memberfees'];
	} else if ($type == 3) {
		$movementType = $lang['global-dispense'];
	} else if ($type == 4) {
		$movementType = "Bar";
	} else {
		$movementType = "N/A";
	}
	
	// Look up user details for showing profile on the Sales page
	$userDetails = "SELECT memberno, first_name, last_name FROM users WHERE user_id = $user_id";
		try
		{
			$result = $pdo3->prepare("$userDetails");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
		$memberno = $row['memberno'];
		$first_name = $row['first_name'];
		$last_name = $row['last_name'];

	$expense_row = sprintf("
  	  <tr>
  	   <td class='left' style='padding: 10px;'>%s</td>
  	   <td class='left' style='padding: 10px;'>%s</td>
  	   <td class='left' style='padding: 10px;'>%s</td>
  	   <td class='left' style='padding: 10px;'>%s</td>
  	   <td class='left' style='padding: 10px;'>%s %s</td>
  	   <td class='right' style='padding: 10px;'>%0.02f {$_SESSION['currencyoperator']}</td>
	  </tr>",
	  $donationTime, $movementType, $donatedTo, $memberno, $first_name, $last_name, $amount
	  );
			

	  echo $expense_row;
}
  	  echo "</tbody></table>";
  	  
displayFooter();  ?>
<script type="text/javascript">
	 function loadExcel(){
 			$("#load").show();
 			var fromDate = "<?php echo $_POST['fromDate'] ?>";
 			var untilDate = "<?php echo $_POST['untilDate'] ?>";
 			var cashBox = "<?php echo $_POST['cashBox'] ?>";
 			var cardBox = "<?php echo $_POST['cardBox'] ?>";
       		window.location.href = 'financial-summary-report.php?fromDate='+fromDate+'&untilDate='+untilDate+'&cashBox='+cashBox+'&cardBox='+cardBox;
       		    setTimeout(function () {
			        $("#load").hide();
			    }, 5000);   
       }
</script>
