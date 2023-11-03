<?php
	require_once 'cOnfig/connection.php';
	require_once 'cOnfig/languages/common.php';

 if(isset($_SESSION['lang'])){
 	$current_lang = $_SESSION['lang'];
 }else{
 	$current_lang = 'en';
 }

$video_upload_path = "https://ccsnubev2.com/v6/Nefos";
//$video_upload_path = "Nefos-new/videos";
$preview_upload_path = "https://ccsnubev2.com/v6/Nefos";
//$preview_upload_path = "Nefos-new/videos/preview_images";
?>
<div id="mainbox-no-width" class='video_main' style="display: block;">
	<div id="mainboxheader">Video Tutorials</div>
	<form id="video_filter_form" action="" method="POST">
		<div class="boxcontent">
			<div class="form-group brd-btm">
				<div class="col col-3">
					<span class="smallgreen"><img src="images/new-search.png"> Filter By Name</span>
				</div>
				<div class="col col-7">										
					<input type="text" name="video_search" class="defaultinput" placeholder="start typing here...">						
				</div>   
				<div class="col col-2 text-center">
					<button  id="filter_sub" class='cta2 btn-filter'><?php echo $lang['filter']; ?></button>
				</div>
			</div>
			<div class="form-group mb0">
				<div class="col col-3">
					<div class="filter_label">
						<img src="images/icon-filter.png">
						<span class="smallgreen"> Filter By labels</span><br><br>
					</div>
				</div>
				<div class="col col-9" style="min-height: 50px;">	
					<div class="selected_tags"><span>&nbsp;</span></div>
				</div>				
				<div class="col col-3">
					<div class="filter_label">
						<span class="smallgreen"> Most Popular:</span><br>
					</div>
				</div>
				<div class="col col-9">	
					<input type="hidden" id='filter_tag_ids'>
					<?php   
							// fetch all video tags
							$selectpopularTAgs= "SELECT * FROM video_tags where most_popular = '1' order by id DESC";
							try
							{
								$pop_results = $pdo2->prepare("$selectpopularTAgs");
								$pop_results->execute();
							}
							catch (PDOException $e)
							{
									$error = 'Error fetching user: ' . $e->getMessage();
									echo $error;
									exit();
							}

					?>
					<div class="popular_tags">
						<?php while($popRow = $pop_results->fetch()){

										$tag_pop_en = $popRow['tag'];
										$tag_pop_es = $popRow['tag_es'];
										$tag_pop_ca = $popRow['tag_ca'];
										$tag_pop_fr = $popRow['tag_fr'];
										$tag_pop_nl = $popRow['tag_nl'];
										$tag_pop_it = $popRow['tag_it'];


									    if($current_lang == 'en'){
								   			$tag_pop_name = $tag_pop_en;
								   			
								   		}else if($current_lang == 'es' && $tag_pop_es != ''){
								   			$tag_pop_name = $tag_pop_es;
								   			
								   		}else if($current_lang == 'ca' && $tag_pop_ca != ''){
								   			$tag_pop_name = $tag_pop_ca;
								   		}else if($current_lang == 'fr' && $tag_pop_fr != ''){
								   			$tag_pop_name = $tag_pop_fr;
								   		}else if($current_lang == 'nl' && $tag_pop_nl != ''){
								   			$tag_pop_name = $tag_pop_nl;
								   		}else if($current_lang == 'it' && $tag_pop_it != ''){
								   			$tag_pop_name = $tag_pop_it;
								   		}else{
								   			$tag_pop_name = $tag_pop_en;
								   		}

						 ?>
							<a href="javascript:void(0);" class="tag_item"><span id= "tag_<?php echo $popRow['id']; ?>" class="usergrouptext"><?php echo $tag_pop_name; ?></span></a>
						<?php } ?>
					</div>
				</div>				
				<div class="col col-12">
					<div class="filter_label">
						<a href='javascript:void(0)' id='filter_label'>
							<span class="smallgreen">All TAGS <img id="close_arrow" src="images/right-arrow.png">
								<img src="images/down-arrow-selected.png"  id="open_arrow" style="display: none;">
							</span>
						</a>
					</div>
				</div>
				
				<div class="col col-12">
					<div class="video_tags" id='label_section' style="display: none;">
						<?php
							// fetch all video tags
									$selectTAgs= "SELECT * FROM video_tags order by id DESC";
									try
									{
										$results = $pdo2->prepare("$selectTAgs");
										$results->execute();
									}
									catch (PDOException $e)
									{
											$error = 'Error fetching user: ' . $e->getMessage();
											echo $error;
											exit();
									}
									while ($tag = $results->fetch()) {

										$tag_en = $tag['tag'];
										$tag_es = $tag['tag_es'];
										$tag_ca = $tag['tag_ca'];
										$tag_fr = $tag['tag_fr'];
										$tag_nl = $tag['tag_nl'];
										$tag_it = $tag['tag_it'];


									    if($current_lang == 'en'){
								   			$tag_name = $tag_en;
								   			
								   		}else if($current_lang == 'es' && $tag_es != ''){
								   			$tag_name = $tag_es;
								   			
								   		}else if($current_lang == 'ca' && $tag_ca != ''){
								   			$tag_name = $tag_ca;
								   		}else if($current_lang == 'fr' && $tag_fr != ''){
								   			$tag_name = $tag_fr;
								   		}else if($current_lang == 'nl' && $tag_nl != ''){
								   			$tag_name = $tag_nl;
								   		}else if($current_lang == 'it' && $tag_it != ''){
								   			$tag_name = $tag_it;
								   		}else{
								   			$tag_name = $tag_en;
								   		}
						?>
						<a href="javascript:void(0);" class="tag_item"><span class="usergrouptext" id= "tag_<?php echo $tag['id']; ?>"><?php echo $tag_name; ?></span></a>
						<?php }  ?>
					</div>
				</div>
			</div>
			</div>			
		</div>
	</form>	
<!--  video listing -->
<?php
define("STRING_DELIMITER", " ");
 
/*
 * @params: String, Integer
 * @return: String
 */
function word_limiter($str, $limit = 10) {
    $str = strip_tags($str); 
    if (stripos($str, STRING_DELIMITER)) {
        $ex_str = explode(STRING_DELIMITER, $str);
        if (count($ex_str) > $limit) {
            for ($i = 0; $i < $limit; $i++) {
                $str_s.=$ex_str[$i] . ' ';
            }
            return $str_s;
        } else {
            return $str;
        }
    } else {
        return $str;
    }
}
// pagignation

if (isset($_GET['page']) && $_GET['page']!="") {
    		$page_no = $_GET['page'];
    } 
    else 
    {
        $page_no = 1;
     }
	
$total_records_per_page = 16;
$offset = ($page_no-1) * $total_records_per_page;
$previous_page = $page_no - 1;
$next_page = $page_no + 1;
$adjacents = "2";

$result_query = "SELECT COUNT(*) As total_records FROM `help_videos`";
				try
				{
					$result_count = $pdo2->prepare("$result_query");
					$result_count->execute();
					
				}
				catch (PDOException $e)
				{
						$error = 'Error fetching user: ' . $e->getMessage();
						echo $error;
						exit();
				}
$total_records = $result_count->fetch();
$total_records = $total_records['total_records'];
$total_no_of_pages = ceil($total_records / $total_records_per_page);
$second_last = $total_no_of_pages - 1; // total pages minus 1


   
?>
<div class="boxcontent video-tutorials">
	<div class="mainboxheader">Video tutorials (in order of publication)</div>
	<div id="loader_img"></div>
	<div class="video_section">
		<?php 
		   	 if(isset($_SESSION['lang'])){
			 	$current_lang = $_SESSION['lang'];
			 }else{
			 	$current_lang = 'en';
			 }
			$start_date = strtotime(date('2020-06-01'));
			$last_date = strtotime(date('2020-06-15'));
		     // select videos
			$selectVideos = "SELECT * from help_videos order by id desc limit $offset, $total_records_per_page";
				try
				{
					$vid_result = $pdo2->prepare("$selectVideos");
					$vid_result->execute();
					
				}
				catch (PDOException $e)
				{
						$error = 'Error fetching user: ' . $e->getMessage();
						echo $error;
						exit();
				}
				 while($video = $vid_result->fetch()){
				 	$videoid =$video['id'];
					$video_title_en = $video['video_title_en'];
			   		$video_title_es = $video['video_title_es'];
			   		$video_title_ca = $video['video_title_ca'];
			   		$video_title_fr = $video['video_title_fr'];
			   		$video_title_nl = $video['video_title_nl'];
			   		$video_title_it = $video['video_title_it'];
				 	//$vid_desc = $video['description'];
					if($current_lang == 'en'){
			   			$vid_title = $video_title_en;
			   		}else if($current_lang == 'es'){
			   			$vid_title = $video_title_es;
			   		}else if($current_lang == 'ca'){
			   			$vid_title = $video_title_ca;
			   		}else if($current_lang == 'fr'){
			   			$vid_title = $video_title_fr;
			   		}else if($current_lang == 'nl'){
			   			$vid_title = $video_title_nl;
			   		}else if($current_lang == 'it'){
			   			$vid_title = $video_title_it;
			   		}
				 	//$vid_desc = word_limiter($video['description'], 10);
				 	$video_path_en = $video['video_path_en'];
				 	$video_path_es = $video['video_path_es'];
				 	$video_path_ca = $video['video_path_ca'];
				 	$video_path_fr = $video['video_path_fr'];
				 	$video_path_nl = $video['video_path_nl'];
				 	$video_path_it = $video['video_path_it'];
				 	$video_duration_en =   $video['video_duration_en'];
				 	$video_duration_es =   $video['video_duration_es'];
				 	$video_duration_ca =   $video['video_duration_ca'];
				 	$video_duration_fr =   $video['video_duration_fr'];
				 	$video_duration_nl =   $video['video_duration_nl'];
				 	$video_duration_it =   $video['video_duration_it'];

					 if($current_lang == 'en'){
			   			$video_duration = gmdate("H:i:s", $video_duration_en);
			   			$video_path = $video_path_en;
			   		}else if($current_lang == 'es' && $video_duration_es != '' && $video_duration_es != '0.00'){
			   			$video_duration = gmdate("H:i:s", $video_duration_es);
			   			$video_path = $video_path_es;
			   		}else if($current_lang == 'ca' && $video_duration_ca != '' && $video_duration_ca != '0.00'){
			   			$video_duration = gmdate("H:i:s", $video_duration_ca);
			   			$video_path = $video_path_ca;
			   		}else if($current_lang == 'fr' && $video_duration_fr != '' && $video_duration_fr != '0.00'){
			   			$video_duration = gmdate("H:i:s", $video_duration_fr);
			   			$video_path = $video_path_fr;
			   		}else if($current_lang == 'nl' && $video_duration_nl != '' && $video_duration_nl != '0.00'){
			   			$video_duration = gmdate("H:i:s", $video_duration_nl);
			   			$video_path = $video_path_nl;
			   		}else if($current_lang == 'it' && $video_duration_it != '' && $video_duration_it != '0.00'){
			   			$video_duration = gmdate("H:i:s", $video_duration_it);
			   			$video_path = $video_path_it;
			   		}else{
			   			$video_duration =gmdate("H:i:s", $video_duration_en);
			   			$video_path = $video_path_en;
			   		}
				 	$video_status =  $video['video_status'];
		 ?>
			<div class="videobox">
				<a href='javascript:void(0);' class='js-open-modal' data-modal-id="popup<?php echo $videoid ?>">
					<div class="preview_image">
						<?php  
							// Calulating the difference in timestamps 
							$video_upload_date = strtotime($video['created_at']);
    						$diff = strtotime(date('Y-m-d')) - $video_upload_date; 
						    // 1 day = 24 hours 
						    // 24 * 60 * 60 = 86400 seconds 
						    if(abs(round($diff / 86400)) <= 14){  ?>
							<span class="usergrouptext">New</span>
						<?php } ?>	
						<span class="play_icon"></span><img src="<?php echo $preview_upload_path; ?>/<?php echo $video['preview_path'];  ?>">
					</div>
				</a>
				<div class="video_desc" ><?php echo $vid_title; ?></div>
				<div class="video_duration"><img src="images/clock-icon.png"> <span><?php echo $video_duration; ?></span></div>
			</div>	
			<!-- vide  popup-->
			<div id="popup<?php echo $videoid ?>" class="modal-box">  
			  <a href="javascript:void(0);" class="js-modal-close close">×</a>
			    <video id='vidid<?php echo $videoid; ?>' width="700" controls>
				 <source src="<?php echo $video_upload_path ?>/<?php echo $video_path; ?>" type="video/mp4">
				  Your browser does not support HTML5 video.
				 </video>
			</div>
		<?php } ?>	
			<ul class="pagination_nav">
				
				<li class="pagination <?php if($page_no <= 1){ echo 'disabled'; } ?>">
					<a  <?php if($page_no > 1){
					echo "href='javascript:void(0);' data-page = '$previous_page'";
					} ?>> << </a>
				</li>
					<?php
						 for ($counter = 1; $counter <= $total_no_of_pages; $counter++){
							 if ($counter == $page_no) {
							 			echo "<li class='pagination active'><a>$counter</a></li>"; 
							         }else{
							        	echo "<li class='pagination'><a href='javascript:void(0);'  data-page = '$counter'>$counter</a></li>";
							                }
						        }
					?>
				<li class="pagination <?php if($page_no >= $total_no_of_pages){
					echo 'disabled';
					} ?>">
					<a <?php if($page_no < $total_no_of_pages) {
					echo "href='javascript:void(0);' data-page = '$next_page'";
					} ?>> >> </a>
				</li>
			</ul>
	</div>
</div>

<input type="hidden" name="total_records" id="total_records_per_page" value="<?php echo $total_records_per_page; ?>">
<input type="hidden" name="page_no" id="page_no" value="<?php echo $page_no; ?>">
<input type="hidden" name="current_video" id="current_video_id">
<input type="hidden" name="click_video" id="auto_click">
<script type="text/javascript">
	var domain = "<?php echo $_SESSION['domain'] ?>";
	var user_id = "<?php echo $_SESSION['user_id']; ?>";
</script>
<script type="text/javascript" src="scripts/video-tutorials.js?t=<?php echo time(); ?>"></script>