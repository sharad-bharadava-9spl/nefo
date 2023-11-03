<?php
	
	require_once 'cOnfig/connection.php';
	require_once 'cOnfig/view.php';
	require_once 'cOnfig/authenticate.php';
	require_once 'cOnfig/languages/common.php';
	
	session_start();
	$accessLevel = '3';
	
	// Authenticate & authorize
	authorizeUser($accessLevel);

		$deleteQueueScript = <<<EOD
				
function delete_queue(id) {
	if (confirm("Are you sure to remove this member from queue ?")) {
				window.location = "uTil/delete-queue.php?queueid=" + id;
				}
}	

function empty_queue() {
	if (confirm("Are you sure to empty the queue ?")) {
				window.location = "queue.php?empty";
				}
}
EOD;

	// empty the queue

	if(isset($_REQUEST['empty'])){

		// count the queue members

		$selectQueue = "SELECT COUNT(*) from member_queue";

		try
		{
			$count_result = $pdo3->prepare("$selectQueue");
			$count_result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}

		$count_row = $count_result->fetch();
			$count_num = $count_row['COUNT(*)'];

		if($count_num == 0){
			$_SESSION['errorMessage'] = "Queue is already empty!";
			header("Location: queue.php");
			exit();
		}	

		$emptyQueue = "TRUNCATE TABLE member_queue";
		try
		{
			$pdo3->prepare("$emptyQueue")->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}

		$_SESSION['successMessage'] = "Empty the Queue Successfully!";
		header("Location:queue.php");
		exit();

	}

	// calulate the waiting time

	function humanTiming ($time){

	    $time = time() - $time; // to get the time since that moment
	    $time = ($time<1)? 1 : $time;
	    $tokens = array (
	        31536000 => 'year',
	        2592000 => 'month',
	        604800 => 'week',
	        86400 => 'day',
	        3600 => 'hour',
	        60 => 'minute',
	        1 => 'second'
	    );

	    foreach ($tokens as $unit => $text) {
	        if ($time < $unit) continue;
	        $numberOfUnits = floor($time / $unit);
	        return $numberOfUnits.' '.$text.(($numberOfUnits>1)?'s':'');
	    }

	}

	pageStart("Member Queue", NULL, $deleteQueueScript, "memberstats", "product admin", "Member Queue", $_SESSION['successMessage'], $_SESSION['errorMessage']);


		// Look up current members queue list
		$selectMembers = "SELECT * from member_queue ORDER BY id DESC";
		try
		{
			$result = $pdo3->prepare("$selectMembers");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
?>
<center>
	<button class="cta1" onclick="empty_queue();">Empty Queue</button><br><br>
	<div class="actionbox-np2">
		 <div class="mainboxheader">
		 Members In Queue 
		</div>
		 <div class="boxcontent">
			<table class="default">
				 <tr>
				  <th><h3>#</h3></th>
				  <th><h3>Member</h3></th>
				  <th><h3>Waiting Since</h3></th>
				  <th><h3>Remove</h3></th>
				 </tr>
				 <?php  
				 	while($queue = $result->fetch()){

				 		$queue_user = $queue['user_id'];
				 		$member_in = strtotime($queue['member_in']);
				 		$queue_id = $queue['id'];
				 		// Look up user details for showing usere
						$userDetails = "SELECT memberno, first_name, last_name FROM users WHERE user_id = ".$queue_user;
						try
						{
							$user_result = $pdo3->prepare("$userDetails");
							$user_result->execute();
						}
						catch (PDOException $e)
						{
								$error = 'Error fetching user: ' . $e->getMessage();
								echo $error;
								exit();
						}
					
						$row = $user_result->fetch();
							$memberno = $row['memberno'];
							$first_name = $row['first_name'];
							$last_name = $row['last_name'];
				  ?>
				 <tr>
				  <td class="left"><?php echo $memberno; ?></td>
				  <td><?php echo $first_name." ".$last_name; ?> </td>
				  <td><?php echo humanTiming($member_in); ?> </td>
				  <td><a href="javascript:delete_queue(<?php echo $queue_id; ?>)"><img src="images/delete.png" height="15" title="Delete Member From Queue"></a></td>
				 </tr>
				<?php } ?>
			</table>
		</div>
	</div>
</center>
<?php displayFooter(); ?>