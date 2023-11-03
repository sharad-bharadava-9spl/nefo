<?php

	require_once 'cOnfig/connection.php';
	require_once 'cOnfig/viewv6.php';
	require_once 'cOnfig/authenticate.php';
	require_once 'cOnfig/languages/common.php';
	
	session_start();
	$accessLevel = '3';
	
	// Authenticate & authorize
	authorizeUser($accessLevel);
	
	session_start();

	if (isset($_POST['resubmit'])) {
		
		foreach($_POST['comment'] as $sale) {
			$db = $sale['db'];
			$content = str_replace("'","\'",str_replace('%', '&#37;', trim($sale['content'])));
			$orig = str_replace("'","\'",str_replace('%', '&#37;', trim($sale['orig'])));
			
			$number = $sale['number'];
			$status = $sale['status'];
			$oldstatus = $sale['oldstatus'];
			
			
			if ($status != $oldstatus && $status != 0 && $number != 0 && $number != 55) {
				
				// Update DB with new status
				$updateUsersU = "UPDATE customers SET status = '$status' WHERE number = '$number'";
				try
				{
					$result = $pdo2->prepare("$updateUsersU")->execute();
				}
				catch (PDOException $e)
				{
						$error = 'Error fetching user: ' . $e->getMessage();
						echo $error;
						exit();
				}
				
			}
			
		}
		
		$_SESSION['successMessage'] = 'Status updated succesfully!';
		
	}
	
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
					5: {
						sorter: "dates"
					},
					6: {
						sorter: "dates"
					},
					11: {
						sorter: "dates"
					},
					12: {
						sorter: "dates"
					}
				}
			}); 

			$('#registerForm2').validate({
			  rules: {
				  day: {
					  required: true
				  },
				  month: {
					  required: true
				  },
				  year: {
					  required: true
				  },
				  hour: {
					  required: true
				  },
				  minute: {
					  required: true
				  },
				  comment: {
					  required: true,
					  minlength: 2
				  }
	    	}, // end rules
			  errorPlacement: function(error, element) {
				  if ( element.is(":radio") || element.is(":checkbox")){
					 error.appendTo(element.parent());
				} else {
					return true;
				}
			},
	    	  submitHandler: function() {
	   $(".oneClick").attr("disabled", true);
	   form.submit();
		    	  }
		  }); // end validate

	      tinymce.init({
	        selector: '.contacttext',
	        height :'400',
	        width: 600,
	        plugins: "code",
	    });
			
		}); 
		
EOD;

			



	pageStart("Inactivity report", NULL, $memberScript, "pmembership", NULL, "Inactivity report", $_SESSION['successMessage'], $_SESSION['errorMessage']);
?>

<center>
 <a href="#" id="xllink" onClick="$('#mainTable').tableExport({type:'excel',escape:'false'});"><img src="images/excel-new.png" style='margin: 0 0 -5px 8px;'/></a>
</center>
<form id="registerForm" action="" method="POST">

<br />
<style>
th {
  position: -webkit-sticky;
  position: sticky;
  top: 0;
  z-index: 2;
}
</style>

	 <table class='default' id='mainTable'>
	  <thead>	
	   <tr style='cursor: pointer;'>
	    <th class='centered'>#</th>
	    <th>Club</th>
	    <th>City</th>
	    <th class='centered'></th>
	    <th class='left'>Status</th>
	    <th class='centered'>Launched</th>
	    <th class='centered'>First login</th>
	    <th class='centered'>Last login<br />(days)</th>
	    <th class='centered'>Last log<br />(days)</th>
	    <th class='centered'>Logs last month</th>
	    <th class='centered'>Logs this month</th>
	    <th class='centered'>Comment</th>
	    <th class='centered'>Follow up</th>
	    <th></th>
	   </tr>
	  </thead>
	  <tbody>
	  
	  <?php
	  
	
$sql = $pdo->query('SHOW DATABASES');
$getAllDbs = $sql->fetchALL(PDO::FETCH_ASSOC);

$i = 1;
foreach ($getAllDbs as $DB) {
	
	$database = $DB['Database'];
	
	if ((substr($database,0,3) == 'ccs') && $database != 'ccs_irena' && $database != 'ccs_masterdb' && $database != 'ccs_andyclub1' && $database != 'ccs_andyclub2' && $database != 'ccs_andysclub3' && $database != 'ccs_andyshouse' && $database != 'ccs_berryscorner' && $database != 'ccs_berryshole' && $database != 'ccs_ccstest' && $database != 'ccs_demo' && $database != 'ccs_demo1' && $database != 'ccs_demo2' && $database != 'ccs_demo3' && $database != 'ccs_demo4' && $database != 'ccs_demo5' && $database != 'ccs_demo6' && $database != 'ccs_demo7' && $database != 'ccs_g13viejo' && $database != 'ccs_iuhhfisud' && $database != 'ccs_jazzhuset' && $database != 'ccs_jazzhusetnano' && $database != 'ccs_kjell' && $database != 'ccs_weedbunny' && $database != 'ccs_testtest' && $database != 'ccs_levelclub' && $database != 'ccs_levelclub1' && $database != 'ccs_levelclub2' && $database != 'ccs_lokeshtest11' && $database != 'ccs_us' && $database != 'ccs_bettyboopold' && $database != 'ccs_monoloco2') {
		
		$domain = substr($database,4);
		
		$query = "SELECT db_pwd, customer, warning FROM db_access WHERE domain = '$domain'";
		try
		{
			$result = $pdo->prepare("$query");
			$result->execute();
			$data = $result->fetchAll();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
			
		if ($data) {

			$row = $data[0];
				$db_pwd = $row['db_pwd'];
			    // for local
				//$db_pwd = "";
				$customer = $row['customer'];
				$warning = $row['warning'];
	
			$db_name = "ccs_" . $domain;
			$db_user = $db_name . "u";
			// for local
			//$db_user = "root";
	
			try	{
		 		$pdo6 = new PDO('mysql:host='.DATABASE_HOST.';dbname='.$db_name, $db_user, $db_pwd);
		 		$pdo6->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		 		$pdo6->exec('SET NAMES "utf8"');
			}
			catch (PDOException $e)	{
		  		$output = 'Unable to connect to the database server: ' . $e->getMessage();
		
		 		echo $output;
		 		exit();
			}
			
			// Check last log, if more than 3 days then run queries
			$selectUsersU = "SELECT logtime FROM log ORDER BY logtime DESC LIMIT 1";
			try
			{
				$result = $pdo6->prepare("$selectUsersU");
				$result->execute();
			}
			catch (PDOException $e)
			{
					$error = 'Error fetching user: ' . $e->getMessage();
					echo $error;
					exit();
			}
		
			$row = $result->fetch();
				$lastLog = date("Y-m-d", strtotime($row['logtime']));
				$lastLogFull = date("d-m-Y H:i", strtotime($row['logtime']));
				
			$dateNow = date("Y-m-d");
			
			$date1 = new DateTime("$lastLog");
			$date2 = new DateTime("$dateNow");
			$interval = $date1->diff($date2);
			
			$daysSinceLastLog = $interval->days;
			
			if ($daysSinceLastLog > 3 || $warning == 3) {
				
				if ($daysSinceLastLog > 1000) {
					$daysSinceLastLog = "<span class='white'>0</span>";
				}
				
				$selectUsersU = "SELECT c.id, c.number, c.launchdate, c.startdate, c.status, s.statusName, c.membermodule, c.number, c.city, c.shortName, c.inactive_followup FROM customers c, customerstatus s WHERE c.status = s.id AND c.number = '$customer'";
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
					$clientid = $rowX['id'];
					$launchdate = $rowX['launchdate'];
					$status = $rowX['status'];
					$statusName = $rowX['statusName'];
					$number = $rowX['number'];
					$city = $rowX['city'];
					$shortName = $rowX['shortName'];
					$inactive_followup = $rowX['inactive_followup'];
			
				if ($launchdate != NULL) {
					$launch = date("d-m-Y", strtotime($launchdate));
				} else {
					$launch = "<span class='white'>00-00-0000</span>";
				}
			
				$statuschange = <<<EOD
		<input type='hidden' name='comment[$i][oldstatus]' value='$status' />
		<input type='hidden' name='comment[$i][number]' value='$number' />
		<select name='comment[$i][status]' id="status" class='noExl defaultinput' style='width: 90px; padding-left: 2px;'>
        <option value='0'>Change?</option>
EOD;
      
		      	// Query to look up customergroups      	
				$selectGroups = "SELECT id, statusName FROM customerstatus WHERE id = 1 || id = 2 || id = 3 || id = 4 || id = 9 || id = 10 || id = 12 || id = 13 ORDER by id ASC";
				try
				{
					$results = $pdo2->prepare("$selectGroups");
					$results->execute();
				}
				catch (PDOException $e)
				{
						$error = 'Error fetching user: ' . $e->getMessage();
						echo $error;
						exit();
				}
			
				while ($group = $results->fetch()) {
					if ($group['id'] != $status) {
						$group_row = sprintf("<option value='%d'>%s</option>",
			  								 $group['id'], $group['statusName']);
			  			$statuschange .= $group_row;
		  			}
		  		}
		  		
			  	$statuschange .= "</select>";
			  			
				$selectUsersU = "SELECT time FROM logins WHERE domain = '$domain' AND email <> 'super@user.com' ORDER BY time ASC LIMIT 1";
				try
				{
					$result = $pdo->prepare("$selectUsersU");
					$result->execute();
				}
				catch (PDOException $e)
				{
						$error = 'Error fetching user: ' . $e->getMessage();
						echo $error;
						exit();
				}
			
				$row = $result->fetch();
					$firstLogin = date("d-m-Y", strtotime($row['time']));
					
				if ($firstLogin == '01-01-1970') {
					$firstLogin = "<span class='white'>00-00-0000</span>";
					$noLogin = 'true';
				} else {
					$firstLogin = $firstLogin;
					$noLogin = 'false';
				}
		
				$selectUsersU = "SELECT time FROM logins WHERE domain = '$domain' AND email <> 'super@user.com' ORDER BY time DESC LIMIT 1";
				try
				{
					$result = $pdo->prepare("$selectUsersU");
					$result->execute();
				}
				catch (PDOException $e)
				{
						$error = 'Error fetching user: ' . $e->getMessage();
						echo $error;
						exit();
				}
			
				$row = $result->fetch();
					$lastLogin = date("Y-m-d", strtotime($row['time']));
					
				$date1 = new DateTime("$lastLogin");
				$date2 = new DateTime("$dateNow");
				$interval = $date1->diff($date2);
				
				$daysSinceLastLogin = $interval->days;
		
				if ($daysSinceLastLogin > 1000) {
					$daysSinceLastLogin = "<span class='white'>0</span>";
				}
		
				if ($status == 1 || $status == 2 || $status == 3 || $status == 4 || $status == 9 || $status == 10 || $status == 12 || $status == 14 || $status == 15) {
					
					if ($warning == 3) {
						$custStatus = $statusName . "<br /><span style='color: red;'>CUT OFF</span>";
					} else {
						$custStatus = $statusName;
					}
				} else if ($warning == 3) {
					$custStatus = "<span style='color: red;'>CUT OFF</span>";
				} else if ($daysSinceLastLog > 2) {
					$custStatus = 'Stopped using SW';
				} else if ($daysSinceLastLog < 3) {
					if ($membermodule == 1) {
						$custStatus = 'Customer - member module';
					} else {
						$custStatus = 'Customer';
					}
				} else {
					$custStatus = 'Unknown';
				}
				
				$selectUsersU = "SELECT COUNT(id) FROM log WHERE MONTH(logtime) = MONTH(NOW()) AND YEAR(logtime) = YEAR(NOW()) ORDER BY logtime DESC LIMIT 1";
				try
				{
					$result = $pdo6->prepare("$selectUsersU");
					$result->execute();
				}
				catch (PDOException $e)
				{
						$error = 'Error fetching user: ' . $e->getMessage();
						echo $error;
						exit();
				}
			
				$row = $result->fetch();
					$logsThisMonth = $row['COUNT(id)'];

				$selectUsersU = "SELECT COUNT(id) FROM log WHERE MONTH(logtime) = MONTH(DATE_ADD(DATE(NOW()), INTERVAL -1 MONTH)) AND YEAR(logtime) = YEAR(DATE_ADD(DATE(NOW()), INTERVAL -1 MONTH))";
				try
				{
					$result = $pdo6->prepare("$selectUsersU");
					$result->execute();
				}
				catch (PDOException $e)
				{
						$error = 'Error fetching user: ' . $e->getMessage();
						echo $error;
						exit();
				}
			
				$row = $result->fetch();
					$logsLastMonth = $row['COUNT(id)'];
					
				if ($noLogin == 'true') {
					
					$logsThisMonth = "<span class='white'>0</span>";
					$logsLastMonth = "<span class='white'>0</span>";
					
				}
				
				// Query comments
				$query = "SELECT time, comment, operator FROM inactivecomments WHERE customer = '$number' ORDER BY time DESC";
				try
				{
					$result = $pdo2->prepare("$query");
					$result->execute();
					$data = $result->fetchAll();
				}
				catch (PDOException $e)
				{
						$error = 'Error fetching user: ' . $e->getMessage();
						echo $error;
						exit();
				}
					
				if (!$data) {
					
					$commentShow = "<a href='add-inactive-comment.php?client=$customer'><img src='images/plus-new.png' width='15' style='margin-left: -50px;' /></a><span style='display:none'>0</span>";
					$commentSort = "<span style='color: #fff;'>00-00-0000</span>";
					
					
				} else {
		
					$comments = '';
					//$commentShow = "";
					
					$i = 0;
					
					foreach ($data as $rowC) {
					
						if ($i == 0) {
							$commentSort = "<a href='#' id='showComment$customer'>" . date("d-m-Y", strtotime($rowC['time'])) . "</a>";	
						}
						
						$commenttime = date("d/m/Y H:i:s", strtotime($rowC['time']));
						$comment = $rowC['comment'];		
						$operator = $rowC['operator'];	
							
						// Look up user
						$query = "SELECT first_name, last_name FROM users WHERE user_id = '$operator'";
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
							$first_name = $row['first_name'];
							$last_name = $row['last_name'];
						
						$comments .= "<strong><span style='font-size: 16px;'>$first_name $last_name</span><br />$commenttime</strong><br />$comment<br /><br />";
						
						$i++;
						
					}
				
					// add comment form

					$commentForm = '<center><span id="success_msg'.$customer.'"></span><br /><div id="mainbox-no-width" class="comment_form" style="display:none">
					<form id="registerForm2" action="" method="POST">
<strong>Comment date</strong><br /><br />
<input type="number" lang="nb" name="day" id="day'.$customer.'" class="defaultinput twoDigit" maxlength="2" placeholder="dd" value="'.date("d").'" />
 <input type="number" lang="nb" name="month" id="month'.$customer.'" class="defaultinput twoDigit" maxlength="2" placeholder="mm" value="'.date("m").'" />
 <input type="number" lang="nb" name="year" id="year'.$customer.'" class="defaultinput fourDigit" maxlength="4" placeholder="'.$lang["member-yyyy"].'" value="'.date("Y").'" />
 @
 <input type="number" lang="nb" name="hour" id="hour'.$customer.'" class="defaultinput twoDigit" maxlength="2" placeholder="h" value="'.date("H").'" />
 :
 <input type="number" lang="nb" name="minute" id="minute'.$customer.'" class="defaultinput twoDigit" maxlength="2" placeholder="m" value="'.date("i").'" />
 <input type="hidden" lang="nb" name="seconds" id="second'.$customer.'" class="defaultinput twoDigit" maxlength="2" placeholder="m" value="'.date("s").'" />
<br /><br />
<strong>Follow up date:</strong><br /><br />
<input type="number" lang="nb" name="day2" id="day2'.$customer.'" class="defaultinput twoDigit" maxlength="2" placeholder="dd" />
 <input type="number" lang="nb" name="month2" id="month2'.$customer.'" class="defaultinput twoDigit" maxlength="2" placeholder="mm" />
 <input type="number" lang="nb" name="year2" id="year2'.$customer.'" class="defaultinput fourDigit" maxlength="4" placeholder="'.$lang["member-yyyy"].'" />
<br />
<br />
<br />

  <textarea name="comment" class="contacttext" placeholder="'.$lang["global-comment"].'?" style="width: 800px;"></textarea>
  <input type="hidden" name="confirmed" id="confirmed'.$customer.'" value="yes" />
  <input type="hidden" name="client" id="client'.$customer.'" value="'.$customer.'" />
        <br /><br />
        
	<div class="fakeboxholder">	
	 <label class="control">
	  <span id="errorBox2"></span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Add cutoff comment as well?
	  <input type="checkbox" name="addcutoff" value="1" id="accept2'.$customer.'" />
	  <div class="fakebox"></div>
	 </label>
	</div>
        
        <br /><br /><br />

  <button class="oneClick okbutton2"  onClick="process_comment('.$customer.');" name="oneClick" type="button" style="margin-left: -2px; width: 286px;">'.$lang["global-confirm"].'</button></td>

 </form></div></center>';
//add-inactive-comment.php?client=$customer
					$commentShow = <<<EOD
				
<a href='#' id='showComment$customer'>$commenttimeDisplay</a><span style='display:none'>1</span>
<div id="commentBox$customer" class='commentBox' style="display: none;">
<a href='#' id='hideComment$customer' class="closeComment"><img src="images/delete.png" width='22' /></a>
<span style='font-size: 22px; color: #606f5a; font-weight: 600;'>Comments for <a href='customer.php?user_id=$clientid' target='_blank'> #$customer $shortName</a></span><br /><br />
<a href='javascript:void;' class='addComment'><img src='images/plus-new2.png' width='25' style='margin-bottom: -7px;' />&nbsp;&nbsp;&nbsp;Update</a><br /><br /><br />
<div id='show_comments$customer'>$comments</div><br />$commentForm

</div>
<script>
$("#showComment$customer").click(function (e) {
	e.preventDefault();
	$("#success_msg$customer").html('');
	$("#commentBox$customer").css("display", "block");
});
$("#hideComment$customer").click(function (e) {
	e.preventDefault();
	$("#success_msg$customer").html('');
	$("#commentBox$customer").css("display", "none");
	$(".comment_form").css("display", "none");
});
$(".addComment").click(function (e) {
	e.preventDefault();
	$("#success_msg$customer").html('');
	var currentdate = new Date(); 
	currentdate.toLocaleString('en-US', { timeZone: 'Europe/Madrid' })
	var day = currentdate.getDate();
	var month = currentdate.getMonth()+1;
	var year = currentdate.getFullYear();
	var hours = currentdate.getHours();
	var minutes = currentdate.getMinutes();
	var seconds = currentdate.getSeconds();
	console.log(day, month, year, hours, minutes, seconds);
	$("#day$customer").val(day);
	$("#month$customer").val(month);
	$("#year$customer").val(year);
	$("#hour$customer").val(hours);
	$("#minute$customer").val(minutes);
	$("#second$customer").val(seconds);
	$(".comment_form").css("display", "block");
});
</script>
EOD;

				}
				
				
			//$query = "SELECT 
			if ($inactive_followup == NULL) {
				
				$followup = "<span style='color: #fff;'>00-00-0000</span>";
				$followupicon = "<img src='images/plus-new.png' width='15' style='margin-left: -70px;' />";
				$deleteFollowup = "";
				
			} else {
				
				$followupicon = "";
				$followup = date('d-m-Y', strtotime($inactive_followup));
				
				if (strtotime($inactive_followup) < strtotime(date('Y-m-d'))) {
					
					$followup = "<span style='color: red;'>" . date('d-m-Y', strtotime($inactive_followup)) . "</span>";
					$deleteFollowup = "<a href='uTil/delete-inactive-followup.php?id=$clientid'><img src='images/delete.png' width='15' /></a>";
					
				} else {
					
					$followup = "<span style='color: #333;'>" . date("d-m-Y", strtotime($inactive_followup)) . "</span>";
					$deleteFollowup = "<a href='uTil/delete-inactive-followup.php?id=$clientid'><img src='images/delete.png' width='15' /></a>";
					
				}
				
				
			}





				echo sprintf("
  	  <tr>
  	   <td class='clickableRow' href='customer.php?user_id=$clientid'>%s</td>
  	   <td class='clickableRow' href='customer.php?user_id=$clientid'>%s ($database)</td>
  	   <td class='clickableRow' href='customer.php?user_id=$clientid'>%s</td>
  	   <td>$statuschange</td>
  	   <td class='clickableRow' href='customer.php?user_id=$clientid'>%s</td>
  	   <td class='clickableRow' href='customer.php?user_id=$clientid'>%s</td>
  	   <td class='clickableRow' href='customer.php?user_id=$clientid'>%s</td>
  	   <td class='centered clickableRow' href='customer.php?user_id=$clientid'>%s</td>
  	   <td class='centered clickableRow' href='customer.php?user_id=$clientid'>%s</td>
  	   <td class='centered clickableRow' href='customer.php?user_id=$clientid'>%s</td>
  	   <td class='centered clickableRow' href='customer.php?user_id=$clientid'>%s</td>
  	   <td class='centered'>$commentSort $commentShow</td>
	   <td class='centered'><a href='uTil/inactivity-followup.php?clientid=$clientid'>$followup $followupicon</a></td>
  	   <td>$deleteFollowup</td>

 	   </tr>",
	  $number, $shortName, $city, $custStatus, $launch, $firstLogin, $daysSinceLastLogin, $daysSinceLastLog, $logsLastMonth, $logsThisMonth);
	  
	  
	  $i++;
	  
	  		}


		} else {
			
			// echo "Club not found: $domain<br />";
			
		}
	
	}
}

?>
</table><br />
 <input type='hidden' name='resubmit' value='yes' />
 <center><button type="submit" class='cta1'>OK</button></center>
</form>		
<script src="https://cdn.tiny.cloud/1/9pxfemefuncr8kvf2f5nm34xwdg8su9zxhktrj66loa5mexa/tinymce/5/tinymce.min.js" referrerpolicy="origin"></script>
<?php

displayFooter(); ?>
<script type="text/javascript">
function process_comment(customer){
	 var formData = {
      day: $("#day"+customer).val(),
      month: $("#month"+customer).val(),
      year: $("#year"+customer).val(),
      hour: $("#hour"+customer).val(),
      minute: $("#minute"+customer).val(),
      second: $("#second"+customer).val(),
      day2: $("#day2"+customer).val(),
      month2: $("#month2"+customer).val(),
      year2: $("#year2"+customer).val(),
      comment: tinymce.activeEditor.getContent(),
      confirmed: $("#confirmed"+customer).val(),
      client: $("#client"+customer).val(),
      addcutoff: parseInt($("#accept2"+customer+":checked").val()),
    };
	//console.log(formData);
    $.ajax({
      type: "POST",
      url: "inactive-comment-process.php",
      data: formData,
      dataType: "json",
      encode: true,
    }).done(function (data) {
     // console.log(data);
      $("#show_comments"+customer).html(data.comments);
      $("#success_msg"+customer).html(data.success);
      tinymce.activeEditor.setContent("");
      $(".comment_form").css("display", "none");
    });

    event.preventDefault();
}
		tinymce.remove(".contacttext");

		  // bind to sort events
  $("#mainTable")
    .bind("sortStart",function(e, table) {
      tinymce.remove(".contacttext");
    })
    .bind("sortEnd",function(e, table) {
       tinymce.init({
	        selector: '.contacttext',
	        height :'400',
	        width: 600,
	        plugins: "code",
	    })
    });

</script>