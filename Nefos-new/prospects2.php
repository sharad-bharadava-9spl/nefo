<?php
	
	require_once 'cOnfig/connection.php';
	require_once 'cOnfig/view.php';
	require_once 'cOnfig/authenticate.php';
	require_once 'cOnfig/languages/common.php';
	
	session_start();
	$accessLevel = '3';
	
	// Authenticate & authorize
	authorizeUser($accessLevel);
	
	if (isset($_GET['organic'])) {
		$organic = "AND organic = 1";
	} else if (isset($_GET['nonorganic'])) {
		$organic = "AND organic = 0";
	}
	
	$pos = $_GET['pos'];
	if ($pos == '') {
		$pos = 0;
	}
	
	$href .= strpos($href, '?') === false ? '?' : '&';
    $href .= http_build_query($_GET);
    
    if (strpos($href, 'nonorganic') !== false) {
	    $href = 'nonorganic';
    } else if (strpos($href, 'organic') !== false) {
	    $href = 'organic';
    } else {
	    $href = '';
    }    

	// Query to look up users
	$selectUsers = "SELECT id, number, shortName, longName, phone, email, language, instagram, facebook, prospect_mail, prospect_facebook, prospect_instagram, prospect_call, registeredSince, status, launchdate, prospect_demo, prospect_demoDate, organic FROM customers WHERE status < 5 AND DATE(registeredSince) IS NOT NULL $organic ORDER BY id DESC";
	//$selectUsers = "SELECT id, number, shortName, longName, phone, email, language, instagram, facebook, prospect_mail, prospect_facebook, prospect_instagram, prospect_call, registeredSince FROM customers WHERE status < 5";
		try
		{
			$results = $pdo3->prepare("$selectUsers");
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
			    exclude: ".noExl",
			    name: "Clients",
			    filename: "Clients" //do not include extension
		
			  });
		
			});
			
			$('#mainTable').tablesorter({
				usNumberFormat: true,
				headers: {
					1: {
						sorter: "dates"
					},
					2: {
						sorter: "dates"
					},
					9: {
						sorter: "dates"
					},
					10: {
						sorter: "dates"
					},
					11: {
						sorter: "dates"
					},
					12: {
						sorter: "dates"
					},
					13: {
						sorter: "dates"
					}
				}
			}); 

		});
		
		$(window).resize(function() {
			$('#cloneTable').width($('#mainTable').width());
		});
		
EOD;


	pageStart("Prospect tracking", NULL, $memberScript, "pmembership", NULL, "Prospect tracking", $_SESSION['successMessage'], $_SESSION['errorMessage']);
?>

  <center><a href='?organic' class='cta'>Organic only</a>
  <a href='?nonorganic' class='cta'>Non-organic only</a>
  <a href='?all' class='cta'>ALL</a></center>

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
	    <th>#</th>
	    <th>Added</th>
	    <th>Launched?</th>
	    <th>Short name</th>
	    <th>Status</th>
	    <th>Organic</th>
	    <th>E-mail</th>
	    <th>Phone</th>
	    <th>Instagram</th>
	    <th>Facebook</th>
	    <th>Mailed</th>
	    <th>Instagrammed</th>
	    <th>Facebooked</th>
	    <th>Called</th>
	    <th>Demo organised?</th>
	    <th>Comment</th>
	   </tr>
	  </thead>
	  <tbody>
	  
	  <?php

		while ($user = $results->fetch()) {

			$id = $user['id'];
			$number = $user['number'];
			$registeredSince = date('d-m-Y', strtotime($user['registeredSince']));
			$launchdate = date('d-m-Y', strtotime($user['launchdate']));
			$shortName = $user['shortName'];
			$longName = $user['longName'];
			$phone = $user['phone'];
			$email = $user['email'];
			$instagram = $user['instagram'];
			$facebook = $user['facebook'];
			$prospect_mail = $user['prospect_mail'];
			$prospect_facebook = $user['prospect_facebook'];
			$prospect_instagram = $user['prospect_instagram'];
			$prospect_call = $user['prospect_call'];
			$prospect_demo = $user['prospect_demo'];
			$prospect_demoDate = $user['prospect_demoDate'];
			$status = $user['status'];
			$organic = $user['organic'];
			
			// Look up comments. If none, show 'add comment' button. If there are comments, show.
			$query = "SELECT id, user_id, time, comment FROM comments WHERE customer = '$id' ORDER BY time DESC";
			try
			{
				$result = $pdo3->prepare("$query");
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
				
				$commentShow = "<a href='add-comment.php?client=$id'><img src='images/plus-new.png' width='15' /></a>";
				
				
			} else {
	
				$comments = '';
					
				foreach ($data as $row) {
			
					$commentid = $row['id'];
					$commentuser_id = $row['user_id'];
					$commenttime = date("d/m/Y H:i", strtotime($row['time']));
					$comment = $row['comment'];
						
					// Look up user
					$query = "SELECT first_name, last_name FROM users WHERE user_id = '$commentuser_id'";
					try
					{
						$result = $pdo3->prepare("$query");
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
				
				}
				
			
				$commentShow = <<<EOD
				
<a href='#' id='showComment$id'><img src='images/comments.png' width='15' /></a>
<div id="commentBox$id" class='commentBox' style="display: none;">
<a href='#' id='hideComment$id' class="closeComment"><img src="images/delete.png" width='22' /></a>
<h1>Comments for $shortName</h1><br />
<a href='add-comment.php?client=$id' class='addComment'><img src='images/plus-new.png' width='25' style='margin-bottom: -7px;' />&nbsp;&nbsp;&nbsp;Add comment</a><br /><br /><br />
$comments

</div>
<script>
$("#showComment$id").click(function (e) {
	e.preventDefault();
	$("#commentBox$id").css("display", "block");
});
$("#hideComment$id").click(function (e) {
	e.preventDefault();
	$("#commentBox$id").css("display", "none");
});
</script>
EOD;
			}
			
			if ($organic == 0) {
				$organic = 'No';
			} else {
				$organic = 'Yes';
			}
			
			if ($launchdate == '01-01-1970') {
				$launchdate = "";
			}

			$query = "SELECT statusName FROM customerstatus WHERE id = $status";
			try
			{
				$result = $pdo3->prepare("$query");
				$result->execute();
			}
			catch (PDOException $e)
			{
					$error = 'Error fetching user: ' . $e->getMessage();
					echo $error;
					exit();
			}
		
			$row = $result->fetch();
				$statusName = $row['statusName'];
			
			if ($phone != '') {
				$phone = "<center><img src='images/complete.png' width='16' /></center>";
			} else {
				$phone = "";
			}
			
			if ($email != '') {
				$email = "<center><img src='images/complete.png' width='16' /></center>";
			} else {
				$email = "";
			}
			
			if ($instagram != '') {
				$instagram = "<center><img src='images/complete.png' width='16' /></center>";
			} else {
				$instagram = "";
			}
			
			if ($facebook != '') {
				$facebook = "<center><img src='images/complete.png' width='16' /></center>";
			} else {
				$facebook = "";
			}
			
			if ($prospect_mail == NULL) {
				$prospect_mail = "<a href='#' onClick='return prospect_mail($id)' style='color: #333;'>No</a>";
			} else {
				$prospect_mail = "<a href='#' onClick='return prospect_mail2($id)' style='color: #333;'>" . date('d-m-Y', strtotime($prospect_mail)) . "</a>";
			}
			
			if ($prospect_facebook == NULL) {
				$prospect_facebook = "<a href='#' onClick='return prospect_facebook($id)' style='color: #333;'>No</a>";
			} else {
				$prospect_facebook = "<a href='#' onClick='return prospect_facebook2($id)' style='color: #333;'>" . date('d-m-Y', strtotime($prospect_facebook)) . "</a>";
			}
			
			if ($prospect_instagram == NULL) {
				$prospect_instagram = "<a href='#' onClick='return prospect_instagram($id)' style='color: #333;'>No</a>";
			} else {
				$prospect_instagram = "<a href='#' onClick='return prospect_instagram2($id)' style='color: #333;'>" . date('d-m-Y', strtotime($prospect_instagram)) . "</a>";
			}
			
			if ($prospect_call == NULL) {
				$prospect_call = "<a href='#' onClick='return prospect_call($id)' style='color: #333;'>No</a>";
			} else {
				$prospect_call = "<a href='#' onClick='return prospect_call2($id)' style='color: #333;'>" . date('d-m-Y', strtotime($prospect_call)) . "</a>";
			}
			
			if ($prospect_demo == NULL) {
				$prospect_demo = "<a href='#' onClick='return prospect_demo($id)' style='color: #333;'>No</a>";
			} else {
				$prospect_demo = "<a href='#' onClick='return prospect_demo2($id)' style='color: #333;'>" . date('d-m-Y', strtotime($prospect_demo)) . "</a>";
			}
			
			
	echo <<<EOD
  	   <tr>
  	    <td class='clickableRow' href='customer.php?user_id=$id'>$number</td>
  	    <td class='clickableRow' href='customer.php?user_id=$id'>$registeredSince</td>
  	    <td class='clickableRow' href='customer.php?user_id=$id'>$launchdate</td>
  	    <td class='clickableRow' href='customer.php?user_id=$id'>$shortName</td>
  	    <td class='clickableRow' href='customer.php?user_id=$id'>$statusName</td>
  	    <td class='clickableRow' href='customer.php?user_id=$id'>$organic</td>
  	    <td class='clickableRow' href='customer.php?user_id=$id'>$email</td>
  	    <td class='clickableRow' href='customer.php?user_id=$id'>$phone</td>
  	    <td class='clickableRow' href='customer.php?user_id=$id'>$instagram</td>
  	    <td class='clickableRow' href='customer.php?user_id=$id'>$facebook</td>
  	    <td class='' href='customer.php?user_id=$id'><center>$prospect_mail</center></td>
  	    <td class='' href='customer.php?user_id=$id'><center>$prospect_facebook</center></td>
  	    <td class='' href='customer.php?user_id=$id'><center>$prospect_instagram</center></td>
  	    <td class='' href='customer.php?user_id=$id'><center>$prospect_call</center></td>
  	    <td class='' href='customer.php?user_id=$id'><center>$prospect_demo</center></td>
  	    <td class='' href='customer.php?user_id=$id'><center>$commentShow</center></td>
  	   </tr>
EOD;
	  
  }
  
?>


	 </tbody>
	 </table>
	 
<script>

function prospect_mail(id) {
	
	var href = "<?php echo $href; ?>";
			
		// Get position
		var curpos = window.pageYOffset;
			
		// Launch util function
		window.location.href = 'uTil/prospect_mail.php?id='+id+'&pos='+curpos+'&src='+href;
		
		return false;

}
function prospect_mail2(id) {
	
	var href = "<?php echo $href; ?>";
			
		// Get position
		var curpos = window.pageYOffset;
			
		// Launch util function
		window.location.href = 'uTil/prospect_mail.php?id='+id+'&pos='+curpos+'&src='+href+'&set=null';
		
		return false;

}

function prospect_facebook(id) {
	
	var href = "<?php echo $href; ?>";
			
		// Get position
		var curpos = window.pageYOffset;
			
		// Launch util function
		window.location.href = 'uTil/prospect_facebook.php?id='+id+'&pos='+curpos+'&src='+href;
		
		return false;

}
function prospect_facebook2(id) {
	
	var href = "<?php echo $href; ?>";
			
		// Get position
		var curpos = window.pageYOffset;
			
		// Launch util function
		window.location.href = 'uTil/prospect_facebook.php?id='+id+'&pos='+curpos+'&src='+href+'&set=null';
		
		return false;

}

function prospect_instagram(id) {
	
	var href = "<?php echo $href; ?>";
			
		// Get position
		var curpos = window.pageYOffset;
			
		// Launch util function
		window.location.href = 'uTil/prospect_instagram.php?id='+id+'&pos='+curpos+'&src='+href;
		
		return false;

}
function prospect_instagram2(id) {
	
	var href = "<?php echo $href; ?>";
			
		// Get position
		var curpos = window.pageYOffset;
			
		// Launch util function
		window.location.href = 'uTil/prospect_instagram.php?id='+id+'&pos='+curpos+'&src='+href+'&set=null';
		
		return false;

}

function prospect_call(id) {
	
	var href = "<?php echo $href; ?>";
			
		// Get position
		var curpos = window.pageYOffset;
			
		// Launch util function
		window.location.href = 'uTil/prospect_call.php?id='+id+'&pos='+curpos+'&src='+href;
		
		return false;

}
function prospect_call2(id) {
	
	var href = "<?php echo $href; ?>";
			
		// Get position
		var curpos = window.pageYOffset;
			
		// Launch util function
		window.location.href = 'uTil/prospect_call.php?id='+id+'&pos='+curpos+'&src='+href+'&set=null';
		
		return false;

}

function prospect_demo(id) {
	
	var href = "<?php echo $href; ?>";
			
		// Get position
		var curpos = window.pageYOffset;
			
		// Launch util function
		window.location.href = 'uTil/prospect_demo.php?id='+id+'&pos='+curpos+'&src='+href;
		
		return false;

}
function prospect_demo2(id) {
	
	var href = "<?php echo $href; ?>";
			
		// Get position
		var curpos = window.pageYOffset;
			
		// Launch util function
		window.location.href = 'uTil/prospect_demo.php?id='+id+'&pos='+curpos+'&src='+href+'&set=null';
		
		return false;

}

document.documentElement.scrollTop = document.body.scrollTop = <?php echo $pos; ?>;

</script>

<?php  displayFooter();