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

	// Check if new Filter value was submitted, and assign query variable accordingly
	if (isset($_POST['filter'])) {
				
		$filterVar = $_POST['filter'];
		
		if ($filterVar == 100) {
			
			$limitVar = "LIMIT 100";
			$optionList = "<option value='$filterVar'>{$lang['last']} 100</option>
			<option value='250'>{$lang['last']} 250</option>
			<option value='500'>{$lang['last']} 500</option>";
			
		} else if ($filterVar == 250) {
			
			$limitVar = "LIMIT 250";
			$optionList = "<option value='$filterVar'>{$lang['last']} 250</option>
			<option value='100'>{$lang['last']} 100</option>
			<option value='500'>{$lang['last']} 500</option>";
			
		} else if ($filterVar == 500) {
			
			$limitVar = "LIMIT 500";
			$optionList = "<option value='$filterVar'>{$lang['last']} 500</option>
			<option value='100'>{$lang['last']} 100</option>
			<option value='250'>{$lang['last']} 250</option>";
			
		} else {
						
			// Grab month and year number
			$month = substr($filterVar, 0, strrpos($filterVar, '-'));	
			$year = substr($filterVar, strrpos($filterVar, '-') + 1);
			
			$timeLimit = "WHERE MONTH(scanin) = $month AND YEAR(scanin) = $year";
			
			$optionList = "<option value='100'>{$lang['last']} 100</option>
				<option value='250'>{$lang['last']} 250</option>
				<option value='500'>{$lang['last']} 500</option>";		
				
		}
			
	} else {
		
		$limitVar = "LIMIT 100";
		
		$optionList = "<option value=''>{$lang['filter']}</option>
			<option value='100'>{$lang['last']} 100</option>
			<option value='250'>{$lang['last']} 250</option>
			<option value='500'>{$lang['last']} 500</option>";		
	}
		
	// Check if 'entre fechas' was utilised
	if (isset($_POST['untilDate'])) {
		
		
		$limitVar = '';

		$fromDate = date("Y-m-d", strtotime($_POST['fromDate']));
		$untilDate = date("Y-m-d", strtotime($_POST['untilDate']));
		
		$timeLimit = "WHERE DATE(scanin) BETWEEN DATE('$fromDate') AND DATE('$untilDate')";
			
	}
	
	// Look up visits
	$scanInR = "SELECT COUNT(DISTINCT userid) FROM newvisits WHERE DATE(scanin) = DATE(NOW()) AND scanout IS NULL";
		try
		{
			$result = $pdo3->prepare("$scanInR");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$rowR = $result->fetch();
		$peopleInside = $rowR['COUNT(DISTINCT userid)'];
	
	// Look up visits
	$scanIn = "SELECT visitNo, userid, scanin, scanout, completed, duration FROM newvisits $timeLimit ORDER BY scanin DESC $limitVar";
		try
		{
			$result2 = $pdo3->prepare("$scanIn");
			$result2->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
	
		
		
	// Create month-by-month split
	$findStartDate = "SELECT scanin FROM newvisits ORDER BY scanin ASC LIMIT 1";
		try
		{
			$result = $pdo3->prepare("$findStartDate");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
		$startDate = date('01-m-Y', strtotime($row['donationTime']));
		$endDate = date('01-m-Y');
		$endDateShort = date('m-Y', strtotime($endDate));
		
		
	if ($endDateShort != $filterVar) {
		$optionList .= "<option value='$endDateShort'>$endDateShort</option>";
	}
	
	$genDateFull = date('01-m-Y', strtotime($endDate));
	$genDate = date('m-Y', strtotime($genDateFull));
	
	while (strtotime($genDateFull) > strtotime($startDate)) {
		
		$genDateFull = date('01-m-Y', strtotime("$genDateFull - 1 month"));
		$genDate = date('m-Y', strtotime($genDateFull));
		
		// Exclude option if already selected
		if ($genDate != $filterVar) {
			$optionList .= "<option value='$genDate'>$genDate</option>";
		}

	}

	
	$deleteVisitScript = <<<EOD
	
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
	  
	    $(document).ready(function() {
		  
			$('#cloneTable').width($('#mainTable').width());
			

			$('#mainTable').tablesorter({
				usNumberFormat: true
			}); 

			
		});
		
		$(window).resize(function() {
			$('#cloneTable').width($('#mainTable').width());
		});
	
function delete_visit(visitNo) {
	if (confirm("Estas seguro?")) {
				window.location = "uTil/delete-visit.php?visitNo=" + visitNo + "&source=visits";
				}
}
EOD;
	
	pageStart($lang['visits'], NULL, $deleteVisitScript, "pexpenses", "admin", $lang['visits'], $_SESSION['successMessage'], $_SESSION['errorMessage']);
	
	
?>
<center>
<div style='display: inline-block;' class='relativeitem'>
<a href="javascript:void(0);" id="xllink" onClick="loadExcel();"><img src="images/excel-new.png" style='float: left; margin-left: 3px;' /></a>
<a href="uTil/signout-all.php" class="cta3" style='float: right; margin-right: 3px;'><img src='images/exit.png' width='18' style='margin-bottom: -2px; margin-right: 10px;' /><?php echo $lang['exit-all']; ?></a>
<br />

<div id='filterbox'>
 <div id='mainboxheader'>
 <?php echo $lang['filter']; ?>
 </div>
 <div class='boxcontent'>
        <form action='' method='POST' style='margin-top: 3px; display: inline-block;'>
	     <select id='filter' name='filter' class='defaultinput' onchange='this.form.submit()' style='display: inline-block; width: 100px; height: 38px;'>
	      <?php echo $optionList; ?>
		 </select>
        </form>
        <form action='' method='POST' style='display: inline-block;'>
<?php
	if (isset($_POST['fromDate'])) {
		
		echo <<<EOD
		 <input type="text" id="datepicker" name="fromDate" autocomplete="nope" class="sixDigit defaultinput" value="{$_POST['fromDate']}" />
		 <input type="text" id="datepicker2" name="untilDate" autocomplete="nope" class="sixDigit defaultinput" value="{$_POST['untilDate']}" onchange='this.form.submit()' />
		 <button type="submit"  class='cta2' style='display: inline-block; width: 50px;'>OK</button>
EOD;
		
	} else {
		
		echo <<<EOD
		 <input type="text" id="datepicker" name="fromDate" autocomplete="nope" class="sixDigit defaultinput" placeholder="{$lang['from-date']}" />
		 <input type="text" id="datepicker2" name="untilDate" autocomplete="nope" class="sixDigit defaultinput" placeholder="{$lang['to-date']}" onchange='this.form.submit()' />
		 <button type="submit"  class='cta2' style='display: inline-block; width: 50px;'>OK</button>
EOD;

	}
?>
        </form>
        </div>
       </td>
      </tr>
     </table>
</div>
<br />
<br />
<div id='productoverview'>
<?php echo $lang['members-in-club']; ?>: <?php echo $peopleInside; ?>
</div>

<br /><br />
<div class="accord_box">
	 <table class='default2 custom_tbl' id="mainTable">
			<?php
			  $chk =1;
			  $i=0;
			  $activeclass='';
				while ($scaninData = $result2->fetch()) {
				$visitNo = $scaninData['visitNo'];
				$userid = $scaninData['userid'];
				$scanin = $scaninData['scanin'];
				$scanout = $scaninData['scanout'];
				$duration = $scaninData['duration'];
				$completed = $scaninData['completed'];
				
				$scantimeReadable = date('H:i', strtotime($scanin."+$offsetSec seconds"));
				
				setlocale(LC_ALL, 'es_ES');

				$dateOnly = ucfirst(strftime("%A %d %B %Y", strtotime($scanin)));

				$userDetails = "SELECT memberno, first_name, last_name from users WHERE user_id = $userid";
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
				
					$user = $result->fetch();
					$member = "<span class='custom_member'>#" . $user['memberno'] . "</span> - " . $user['first_name'] . " " . $user['last_name'];
				 if($i == 0){
					 	$activeclass='active';
					 }	

				if (date('d', strtotime($scanin)) != date('d', strtotime($prevScantime))) {
					 $chk =1;
						if($i > 0){
                                
                                echo "<tr><td colspan='7' style='border: none;'> </td></tr><thead><tr><td class='main-td'  colspan='4' align='left'>$dateOnly</td><td class='main-td'></td><td class='main-td'></td><td class='main-td togglebtn'><span class='toggle_icon'><span class='ui-icon ui-icon-pluswhite ui-icon-plusthick'></span></span></td></tr></thead> <tr><td colspan='7' style='border: none;'> </td></tr>  <tr><th class='hidden_td'></th><th>Socio</th><th>Entrada</th><th>Salida</th><th>Duraci&oacute;n</th><th>Borrar</th><th class='hidden_td'></th></tr>";
                            }else{
                                echo "<thead><tr><td class='main-td'  colspan='4' align='left'>$dateOnly</td><td class='main-td'></td><td class='main-td'></td><td class='main-td togglebtn'><span class='toggle_icon'><span class='ui-icon ui-icon-pluswhite ui-icon-plusthick'></span></span></td></tr></thead> <tr><td colspan='7' style='border: none;'> </td></tr>  <tr><th class='hidden_td'></th><th>Socio</th><th>Entrada</th><th>Salida</th><th>Duraci&oacute;n</th><th>Borrar</th><th class='hidden_td'></th></tr>";
                            }    
					// Insert row with date.
				}
				 if($chk%2 == 0){
				 	$bgcolor = "";
				 }else{
				 	$bgcolor = "bgcolor";
				 } 
				if ($scanout == '') {

					$expense_row =	sprintf("
				  	  <tr>
				  	   <td class='hidden_td'></td>
				  	   <td class='clickableRow $bgcolor' href='member-visits.php?userid=%d' style='text-align: left;'>%s</td>
				  	   <td class='clickableRow $bgcolor' href='member-visits.php?userid=%d'>%s</td>
				  	   <td class='$bgcolor' href='member-visits.php?userid=%d'><a href='uTil/user-signout.php?user_id=%d&source=visits'><img src='images/exit-sign.png' /></a></td>
				  	   <td class='$bgcolor' href='member-visits.php?userid=%d'></td>
				  	   <td class='$bgcolor' style='text-align: center;'><a href='javascript:delete_visit(%d)'><img src='images/delete.png' height='15' title='Borrar' /></a></td><td class='hidden_td'></td>
					  </tr>",
					  $userid, $member, $userid, $scantimeReadable, $userid, $userid, $userid, $visitNo
					  );

				} else {
					
					// Determine visit duration	
					$hours  = floor($duration/60); //round down to nearest minute. 
					$minutes = $duration % 60;
					
					$signoutReadable = date('H:i', strtotime($scanout."+$offsetSec seconds"));
					
					$expense_row =	sprintf("
				  	  <tr>
				  	   <td class='hidden_td'></td>
				  	   <td class='clickableRow $bgcolor' href='member-visits.php?userid=%d' style='text-align: left;'>%s</td>
				  	   <td class='clickableRow $bgcolor' href='member-visits.php?userid=%d'>%s</td>
				  	   <td class='$bgcolor' href='member-visits.php?userid=%d'>%s</td>
				  	   <td class='$bgcolor' href='member-visits.php?userid=%d'>%dh %02dm</td>
				  	   <td class='$bgcolor' style='text-align: center;'><a href='javascript:delete_visit(%d)'><img src='images/delete.png' height='15' title='Borrar' /></a></td><td class='hidden_td'></td>
					  </tr>",
					  $userid, $member, $userid, $scantimeReadable, $userid, $signoutReadable, $userid, $hours, $minutes, $visitNo
					  );
					  
				}

				echo $expense_row;

				$prevScantime = $scanin;
				$i++;
				$chk++;
			  }
			?>

				 </tbody>
	 </table>
	</div>
</div> 
<?php displayFooter(); ?>
<script type="text/javascript">

$(document).ready(function(){
  $('.default2 thead').click(function() {
  	  $(this).toggleClass('active');
  	  if($(this).hasClass('active')){
  	  	$(this).find("span.toggle_icon").html("<span class='ui-icon ui-icon-minusthick'>-</span>");
  	  }else{
  	  	$(this).find("span.toggle_icon").html("<span class='ui-icon ui-icon-pluswhite ui-icon-plusthick'></span>");
  	  }	
      $(this).next().slideToggle();
      return false;
  }).next().hide();
  $('.default2').children('thead:first').click();
});

	 function loadExcel(){
 			$("#load").show();
 			var filter = "<?php echo $_POST['filter'] ?>";
 			var untilDate = "<?php echo $_POST['untilDate'] ?>";
 			var fromDate = "<?php echo $_POST['fromDate'] ?>";
 			console.log(filter, untilDate, fromDate);
       		window.location.href = 'visits-report.php?filter='+filter+'&untilDate='+untilDate+'&fromDate='+fromDate;
       		    setTimeout(function () {
			        $("#load").hide();
			    }, 5000);   
       }
</script>
