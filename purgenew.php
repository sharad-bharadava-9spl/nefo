<?php
	
	require_once 'cOnfig/connection.php';
	require_once 'cOnfig/view.php';
	require_once 'cOnfig/authenticate.php';
	require_once 'cOnfig/languages/common.php';
	
	session_start();
	$accessLevel = '1';
	
	// Authenticate & authorize
	authorizeUser($accessLevel);
	
	// Use finance summary as template
	
	// On top, list summary: Total income for period o/w cash/card Income per day (average).
	// Reduce to: X per day, meaning total of Y and daily avg of Z. ALso show % (reduced by T)
	// Re-run the report with those conditions
	// Total = 0
	
	// Per day:
	// Look up random donation, add to total
	// If total < X, display row.
	// If not, proceed to next day.
	// Check if 'entre fechas' was utilised
	
	
	if (isset($_POST['untilDate'])) {
		
		$firstLoad = 'false';
		
		$fromDate = date("Y-m-d", strtotime($_POST['fromDate']));
		$untilDate = date("Y-m-d", strtotime($_POST['untilDate']));
		
		$timeLimit = "AND DATE(donationTime) BETWEEN DATE('$fromDate') AND DATE('$untilDate')";
		$timeLimit2 = "AND DATE(paymentdate) BETWEEN DATE('$fromDate') AND DATE('$untilDate')";
		$timeLimit3 = "DATE(donationTime) BETWEEN DATE('$fromDate') AND DATE('$untilDate')";
		$timeLimit4 = "DATE(paymentdate) BETWEEN DATE('$fromDate') AND DATE('$untilDate')";

			
	} else {
		
		$firstLoad = 'true';
		
		$nowDate = date("d-m-Y");
		
		$timeLimit = "AND DATE(donationTime) = DATE(NOW())";
		$timeLimit2 = "AND DATE(paymentdate) = DATE(NOW())";
		$timeLimit3 = "DATE(donationTime) = DATE(NOW())";
		$timeLimit4 = "DATE(paymentdate) = DATE(NOW())";
		
	}
	
	if (isset($_POST['newValue'])) {
		
		// echo "HAU"; exit();
		
	}
		
	// Look up todays donations
	$selectDonations = "SELECT SUM(amount) from donations WHERE (donatedTo < 2 OR donatedTo = 4) $timeLimit $cashLimit";

	$donationResult = mysql_query($selectDonations)
		or handleError($lang['error-donationload'],"Error loading donations from db: " . mysql_error());
		
	$row = mysql_fetch_array($donationResult);
		$donations = $row['SUM(amount)'];
		
	// Look up today's membership fees
	$selectMembershipFees = "SELECT SUM(amountPaid) FROM memberpayments WHERE (paidTo < 2 OR paidTo = 4) $timeLimit2 $cashLimit2";
				
	$result = mysql_query($selectMembershipFees)
		or handleError($lang['error-expensesload'],"Error loading expense from db: " . mysql_error());
			
	$row = mysql_fetch_array($result);
		$membershipFees = $row['SUM(amountPaid)'];
		
	// Look up todays bank donations
	$selectDonations = "SELECT SUM(amount) from donations WHERE donatedTo = 2 $timeLimit $cashLimit";

	$donationResult = mysql_query($selectDonations)
		or handleError($lang['error-donationload'],"Error loading donations from db: " . mysql_error());
		
	$row = mysql_fetch_array($donationResult);
		$bankDonations = $row['SUM(amount)'];
		
		
	// Look up today's membership fees Bank
	$selectMembershipFees = "SELECT SUM(amountPaid) FROM memberpayments WHERE paidTo = 2 $timeLimit2 $cashLimit2";
				
	$result = mysql_query($selectMembershipFees)
		or handleError($lang['error-expensesload'],"Error loading expense from db: " . mysql_error());
			
	$row = mysql_fetch_array($result);
		$membershipfeesBank = $row['SUM(amountPaid)'];
		
	// Query to look up list of donations & membership fees
	$selectExpenses = "SELECT '1' AS type, donationTime AS time, userid, amount, donatedTo FROM donations WHERE $timeLimit3 $cashLimit UNION ALL SELECT '2' AS type, paymentdate AS time, userid, amountPaid AS amount, paidTo AS donatedTo FROM memberpayments WHERE $timeLimit4 $cashLimit2";

	$result2 = mysql_query($selectExpenses)
		or handleError($lang['error-donationload'] . mysql_error(),"Error loading expense from db: " . mysql_error());



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
	  
	  	function computeTot() {
		  	
          var a = parseInt($('#newValue').val());
          var b = $('#newTotal').val();
          var c = $('#newTotalFull').val();
          var d = parseInt($('#cashTot').val());
          var e = parseInt($('#totDays').val());
          var f = parseInt($('#bankTot').val());
          
          
          var total = (a * e);
          var totalb = (a * e + f);
          // var total = (d / a);
          var roundedtotal = total.toFixed(0);
          var roundedtotalb = totalb.toFixed(0);
          $('#newTotal').val(roundedtotal);
          $('#newTotalFull').val(roundedtotalb);
          console.log('a: ' + a + 'd: ' + d + 'total: ' + total);
          
        }

		    
		    
		    $(document).on('keypress keyup blur', function(event) {

    computeTot();

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
			
	pageStart("Sumario de ingresos", NULL, $deleteDonationScript, "pprofile", "statutes", "Sumario de ingresos", $_SESSION['successMessage'], $_SESSION['errorMessage']);


?>
	 <table class='default' id='cloneTable' style='text-align: left;'>
      <tr class='nonhover'>
       <td colspan='13' style='border-bottom: 0;'>
         <img src="images/excel.png" style="cursor: pointer;" onclick="tablesToExcel(['t1', 't2'], ['t1', 't2'], 'myfile.xls')" value="Export to Excel" /><br /><br />
		<div style='display: inline-block; border: 2px solid #5aa242; padding: 10px;'>
		&nbsp;<strong>Filtrar:</strong><br /> 
        <form action='' method='POST'>
<?php
	if (isset($_POST['fromDate'])) {
		
		echo <<<EOD
		 <input type="text" id="datepicker" name="fromDate" autocomplete="nope" class="sixDigit" value="{$_POST['fromDate']}" />
		 <input type="text" id="datepicker2" name="untilDate" autocomplete="nope" class="sixDigit" value="{$_POST['untilDate']}" onchange='this.form.submit()' />
		 <button type="submit" style='display: inline-block; width: 40px; height: 27px;'>OK</button>
EOD;
		
	} else {
		
		
		echo <<<EOD
		 <input type="text" id="datepicker" name="fromDate" autocomplete="nope" class="sixDigit" value="$nowDate" />
		 <input type="text" id="datepicker2" name="untilDate" autocomplete="nope" class="sixDigit" value="$nowDate" onchange='this.form.submit()' />
		 <button type="submit" style='display: inline-block; width: 40px; height: 27px;'>OK</button>
EOD;

	}
	
	$datediff = strtotime($_POST['untilDate']) - strtotime($_POST['fromDate']);

	$noOfDays = round($datediff / (60 * 60 * 24)) + 1;
	
	echo "<br /><br /><center><strong>Dias: $noOfDays</strong></center>";
	
?>

	&nbsp;&nbsp;&nbsp;
	
        </form>
        </div>
       </td>
      </tr>
     </table>
	<form action='' method='POST'>
	
	 <table class='default' id='t1'>
	  <thead>
	   <tr>
	    <th></th>
  		<th class='right' style='background-color: #ddd;'>Tarjeta</th>
	    <th class='right'><?php echo $lang['cash']; ?></th>
	    <th class='right'>Diario</th>
	    <th class='centered'>Nuevo</th>
	    <th class='centered'><strong>TOTAL efectivo</strong></th>
	    <th class='centered'><strong>TOTAL inc. tarjeta</strong></th>
	   </tr>
	  </thead>
	  <tbody>
  	   <tr>
  	    <td class='left'>Cuotas</td>
  	    <td class='right' style='background-color: #ddd;'><?php echo number_format($membershipfeesBank,2); ?> <?php echo $_SESSION['currencyoperator']; ?></td>
  	    <td class='right'><?php echo number_format($membershipFees,2); ?> <?php echo $_SESSION['currencyoperator']; ?></td>
  	    <td class='right'><?php echo number_format($membershipFees / $noOfDays,2); ?> <?php echo $_SESSION['currencyoperator']; ?></td>
  	   </tr>
  	   <tr>
  	    <td class='left'>Aportaciones</td>
  	    <td class='right' style='background-color: #ddd;'><?php echo number_format($bankDonations,2); ?> <?php echo $_SESSION['currencyoperator']; ?></td>
  	    <td class='right'><?php echo number_format($donations,2); ?> <?php echo $_SESSION['currencyoperator']; ?></td>
  	    <td class='right'><?php echo number_format($donations / $noOfDays,2); ?> <?php echo $_SESSION['currencyoperator']; ?></td>
  	   </tr>
  	   <tr>
  	    <td class='left'><strong>Total</strong></td>
  	    <td class='right' style='background-color: #ddd;'><strong><?php echo number_format($membershipfeesBank + $bankDonations,2); ?> <?php echo $_SESSION['currencyoperator']; ?><input type='hidden' name='bankTot' id='bankTot' value="<?php echo $membershipfeesBank + $bankDonations; ?>" /></strong></td>
  	    <td class='right'><strong><?php echo number_format($membershipFees + $donations,2); ?> <?php echo $_SESSION['currencyoperator']; ?><input type='hidden' name='cashTot' id='cashTot' value="<?php echo $membershipFees + $donations; ?>" /><input type='hidden' name='totDays' id='totDays' value="<?php echo $noOfDays; ?>" /></strong></td>
  	    <td class='right'><strong><?php echo number_format(($membershipFees + $donations)/ $noOfDays,2); ?> <?php echo $_SESSION['currencyoperator']; ?></strong></td>
  	    <td class='centered'><input type='number' class='fourDigit right' name='newValue' id='newValue' value="<?php echo number_format(($membershipFees + $donations)/ $noOfDays,2); ?>" /></td>
  	    <td class='centered'><strong><input type='text' class='sixDigit right' name='newTotal' id='newTotal' value="<?php echo number_format($membershipFees + $donations,2); ?>" readonly /></strong></td>
  	    <td class='centered'><strong><input type='text' class='sixDigit right' name='newTotalFull' id='newTotalFull' value="<?php echo number_format($membershipfeesBank + $bankDonations + $membershipFees + $donations,2); ?>" readonly />
  	    
  	    </strong></td>
  	   </tr>
	  </tbody>
	 </table>

   <center><button type="submit" style='display: inline-block; width: 80px; height: 27px;'>Calcular</button></center>
   </form>
<?php 

	echo <<<EOD
	<br /><br />
	 <table class='default' id='t2'>
	  <thead>
	   <tr style='cursor: pointer;'>
	    <th style='padding: 10px;'>{$lang['global-time']}</th>
	    <th style='padding: 10px;'>{$lang['global-type']}</th>
  		<th style='padding: 10px;'>Pagado con</th>
	    <th style='padding: 10px;'>#</th>
	    <th style='padding: 10px;'>{$lang['global-member']}</th>
	    <th style='padding: 10px;'>{$lang['global-amount']}</th>
	   </tr>
	  </thead>
	  <tbody>
EOD;

while ($donation = mysql_fetch_array($result2)) {
	
	$id = $donation['id'];
	$donationTime = date("d-m-Y H:i", strtotime($donation['time'] . "+$offsetSec seconds"));
	$user_id = $donation['userid'];
	$amount = $donation['amount'];
	$type = $donation['type'];
	$donatedTo = $donation['donatedTo'];
	
	if ($donatedTo == '2') {
		$donatedTo = 'Tarjeta';
	} else if ($donatedTo == '3') {
		$donatedTo = '';
	} else {
		$donatedTo = 'Efectivo';
	}
	
	if ($type == 1) {
		$movementType = $lang['donation-donation'];
	} else {
		$movementType = $lang['memberfees'];
	}
	
	// Look up user details for showing profile on the Sales page
	$userDetails = "SELECT memberno, first_name, last_name FROM users WHERE user_id = $user_id";

	$result = mysql_query($userDetails)
		or handleError($lang['error-userload'],"Error loading user: " . mysql_error());

	$row = mysql_fetch_array($result);
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
  	  
displayFooter();
