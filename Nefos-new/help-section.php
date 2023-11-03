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

	$video_upload_set_path = "/var/www/html/ccsnubev2_com/v6/Nefos/";

	
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

	function delete_element(delete_id){
      	 if(confirm('Are you sure to delete this video ?')){
      	 	 window.location = "help-section.php?did="+delete_id;
      	 }
      }
		
EOD;
// delete videos

	if(isset($_GET['did'])){
		// delete department
		$id= $_GET['did'];
		$selectPathVideo = "SELECT * from help_videos WHERE id= $id";
			try
			{
				$select_results = $pdo3->prepare("$selectPathVideo");
				$select_results->execute();
			}
			catch (PDOException $e)
			{
					$error = 'Error fetching user: ' . $e->getMessage();
					echo $error;
					exit();
			}
			$getRow = $select_results->fetch();
				$remove_video_path_en = $getRow['video_path_en'];
				$remove_video_path_es = $getRow['video_path_en'];
				$remove_video_path_ca = $getRow['video_path_ca'];
				$remove_video_path_fr = $getRow['video_path_fr'];
				$remove_video_path_nl = $getRow['video_path_nl'];
				$remove_video_path_it = $getRow['video_path_it'];
				$remove_preview_path = $getRow['preview_path'];

				unlink($video_upload_set_path.$remove_video_path_en);
				unlink($video_upload_set_path.$remove_video_path_es);
				unlink($video_upload_set_path.$remove_video_path_ca);
				unlink($video_upload_set_path.$remove_video_path_fr);
				unlink($video_upload_set_path.$remove_video_path_nl);
				unlink($video_upload_set_path.$remove_video_path_it);
				unlink($video_upload_set_path.$remove_preview_path);

		$deleteVideo = "DELETE FROM help_videos where id = $id";
			try
			{
				$results = $pdo3->prepare("$deleteVideo");
				$results->execute();
			}
			catch (PDOException $e)
			{
					$error = 'Error fetching user: ' . $e->getMessage();
					echo $error;
					exit();
			}
			$_SESSION['successMessage'] = "Video deleted successfully!";
			header("location: help-section.php");
			exit();
	}

	pageStart("Help Center", NULL, $memberScript, "pmembership", NULL, "Help Center", $_SESSION['successMessage'], $_SESSION['errorMessage']);
	
?>

<center>
	<a href='feedback.php' class='cta1'>Tickets</a>
    <a href='help-section.php' class='cta1'>Video tutorials</a>
	<a href='new-video.php' class='cta1'>Add New Video</a>
	<a href='video-tags.php' class='cta1'>Tags</a>
	<a href='suggestions.php' class='cta1'>Suggestions</a>
</center>


         <a href="#" id="xllink" onClick="$('#mainTable').tableExport({type:'excel',escape:'false'});"><img src="images/excel-new.png" style='margin: 0 0 -5px 8px;'/></a>
<br />
<br />
<?php 
     // select videos
	$selectVideos = "SELECT * from help_videos order by id desc";
		try
		{
			$result = $pdo3->prepare("$selectVideos");
			$result->execute();
			
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
 ?>

	 <table class='default' id='mainTable'>
		  <thead>	
		   <tr style='cursor: pointer;'>
		    <th>Video Title</th>
		    <th>Tags</th>
		    <th>Duration</th>
		    <th>Status</th>
		    <th>Created</th>
		    <th>Actions</th>
		   </tr>
		  </thead>
		  <tbody>
		<?php
		$i= 0;
		   while($video = $result->fetch()){
		   		$videoid = $video['id'];
		   		$video_duration_en =  $video['video_duration_en'];
		   		$video_duration_es =  $video['video_duration_es'];
		   		$video_duration_ca =  $video['video_duration_ca'];
		   		$video_duration_fr =  $video['video_duration_fr'];
		   		$video_duration_nl =  $video['video_duration_nl'];
		   		$video_duration_it =  $video['video_duration_it'];
		   		$video_title_en = $video['video_title_en'];
		   		$video_title_es = $video['video_title_es'];
		   		$video_title_ca = $video['video_title_ca'];
		   		$video_title_fr = $video['video_title_fr'];
		   		$video_title_nl = $video['video_title_nl'];
		   		$video_title_it = $video['video_title_it'];

		   		if($current_lang == 'en'){
		   			$video_title = $video_title_en;
		   			$description = $description_en;
		   			
		   		}else if($current_lang == 'es'){
		   			$video_title = $video_title_es;
		   			$description = $description_es;
		   			
		   		}else if($current_lang == 'ca'){
		   			$video_title = $video_title_ca;
		   			$description = $description_ca;
		   		}else if($current_lang == 'fr'){
		   			$video_title = $video_title_fr;
		   			$description = $description_fr;
		   		}else if($current_lang == 'nl'){
		   			$video_title = $video_title_nl;
		   			$description = $description_nl;
		   		}else if($current_lang == 'it'){
		   			$video_title = $video_title_it;
		   			$description = $description_it;
		   		}


		   		if($current_lang == 'en'){
		   			$video_duration = gmdate("H:i:s", $video_duration_en);
		   		}else if($current_lang == 'es' && $video_duration_es != '' && $video_duration_es != '0.00'){
		   			$video_duration = gmdate("H:i:s", $video_duration_es);
		   		}else if($current_lang == 'ca' && $video_duration_ca != '' && $video_duration_ca != '0.00'){
		   			$video_duration = gmdate("H:i:s", $video_duration_ca);
		   		}else if($current_lang == 'fr' && $video_duration_fr != '' && $video_duration_fr != '0.00'){
		   			$video_duration = gmdate("H:i:s", $video_duration_fr);
		   		}else if($current_lang == 'nl' && $video_duration_nl != '' && $video_duration_nl != '0.00'){
		   			$video_duration = gmdate("H:i:s", $video_duration_nl);
		   		}else if($current_lang == 'it' && $video_duration_it != '' && $video_duration_it != '0.00'){
		   			$video_duration = gmdate("H:i:s", $video_duration_it);
		   		}else{
		   			$video_duration =gmdate("H:i:s", $video_duration_en);
		   		}

		   		$tag_ids = $video['tags'];
		   		$created = $video['created_at'];
		   		
		   		$status = $video['video_status'];
		   		$video_status = '';
		   		if($status == 1){
		   			$video_status = 'New';
		   		}


		   		// get tag names
		   		if(empty($tag_ids) || $tag_ids == ''){
		   			$tag_ids = -1;
		   		}

		   		$selectTags = "SELECT tag from video_tags WHERE id IN ($tag_ids)";
		   				try
						{
							$tag_results = $pdo3->prepare("$selectTags");
							$tag_results->execute();
						}
						catch (PDOException $e)
						{
								$error = 'Error fetching user: ' . $e->getMessage();
								echo $error;
								exit();
						}
						$t=0;
						while($tagRow = $tag_results->fetch()){
							$tag_arr[$i][$t] = $tagRow['tag'];
							$t++;
						}
						$tag_names = implode(",", $tag_arr[$i]);


		   		$video_row =	sprintf("
			  	  <tr>
			  	   <td class='clickableRow' href='edit-video.php?videoid=%d'>%s</td>
			  	   <td class='clickableRow' href='edit-video.php?videoid=%d'>%s</td>
			  	   <td class='clickableRow' href='edit-video.php?videoid=%d'>%s</td>
			  	   <td class='clickableRow' href='edit-video.php?videoid=%d'>%s</td>
			  	   <td class='clickableRow' href='edit-video.php?videoid=%d'>%s</td>
			  	   <td style='text-align: center;'><a href='edit-video.php?videoid=%d'><img src='images/edit.png' height='15' title='Editar' /></a>&nbsp;&nbsp;<a href='javascript:void(0)' onClick='delete_element(%d)' ><img src='images/delete.png' height='15' title='Delete Video' /></a></td>
				  </tr>",
				  $videoid, $video_title, $videoid, $tag_names, $videoid, $video_duration, $videoid, $video_status, $videoid, $created, $videoid, $videoid
				  );
				  $i++;
				  echo $video_row;
		   }
        ?>
		  </tbody>
	 </table>
<?php  displayFooter();