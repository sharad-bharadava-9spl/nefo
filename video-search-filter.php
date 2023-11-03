<?php

require_once 'cOnfig/connection.php';
session_start();
 if(isset($_SESSION['lang'])){
  $current_lang = $_SESSION['lang'];
 }else{
  $current_lang = 'en';
 }
      $video_upload_path = "https://ccsnubev2.com/v6/Nefos";
      //$video_upload_path = "Nefos-new/videos";
      $preview_upload_path = "https://ccsnubev2.com/v6/Nefos";
      //$preview_upload_path = "Nefos-new/videos/preview_images";
      $start_date = strtotime(date('2020-06-01'));
      $last_date = strtotime(date('2020-06-15'));
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
?>

<?php
//$video_text = addslashes($_POST['video_text']);
$video_text = str_replace("'","\'",str_replace('%', '&#37;', trim($_POST['video_text'])));
$filter_tags = $_POST['filter_tags'];
$records = $_POST['records'];

if (isset($_POST['page']) && $_POST['page']!="") {
        $page_no = $_POST['page'];
    } 
    else 
    {
        $page_no = 1;
     }

$total_records_per_page = $records;
$offset = ($page_no-1) * $total_records_per_page;
$previous_page = $page_no - 1;
$next_page = $page_no + 1;
$adjacents = "2";

if(!empty($filter_tags) && $filter_tags != ''){
  $tag_limit = "AND tags REGEXP ('($filter_tags)')";
}else{
  $tag_limit = '';
}

if(!empty($video_text) && $video_text != ''){
  if($current_lang == 'en'){
      $video_limit = "AND LOCATE('".$video_text."', video_title_en)>0";
  }else if($current_lang == 'es'){
     $video_limit = "AND LOCATE('".$video_text."', video_title_es)>0";
  }else if($current_lang == 'ca'){
     $video_limit = "AND LOCATE('".$video_text."', video_title_ca)>0";
  }else if($current_lang == 'fr'){
     $video_limit = "AND LOCATE('".$video_text."', video_title_fr)>0";
  }else if($current_lang == 'nl'){
     $video_limit = "AND LOCATE('".$video_text."', video_title_nl)>0";
  }else if($current_lang == 'it'){
     $video_limit = "AND LOCATE('".$video_text."', video_title_it)>0";
  }
}else{
  $video_limit = '';
}
$result_query = "SELECT COUNT(*) As total_records FROM `help_videos` WHERE 1 $video_limit $tag_limit";
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



if(isset($video_text) || isset($filter_tags)){

      $selectVideos = "SELECT * from help_videos WHERE 1 $video_limit $tag_limit order by id desc limit $offset, $total_records_per_page"; 
        try
        {
          $vid_result = $pdo2->prepare("$selectVideos");
          $vid_result->execute();
          $total_results = $vid_result->rowCount(); 
        }
        catch (PDOException $e)
        {
            $error = 'Error fetching user: ' . $e->getMessage();
            echo $error;
            exit();
        }
        if($total_results > 0){
        while($video = $vid_result->fetch()){
              $videoid =$video['id'];
              //$vid_desc = $video['description'];
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
             // $vid_desc = word_limiter($video['description'], 10);
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
            <a href='javascript:void(0);' class='js-open-modal' id='click_modal<?php echo $videoid; ?>' data-modal-id="popup<?php echo $videoid ?>">
              <div class="preview_image">
                <?php  
                // Calulating the difference in timestamps 
                $video_upload_date = strtotime($video['created_at']);
                  $diff = strtotime(date('Y-m-d')) - $video_upload_date; 
                  // 1 day = 24 hours 
                  // 24 * 60 * 60 = 86400 seconds 
                  if((($video_upload_date < $last_date) && ($video_upload_date < $start_date))  || abs(round($diff / 86400)) <= 14){  ?>
                      <span class="usergrouptext">New</span>
                <?php } ?>
                <span class="play_icon"></span><img src="<?php echo $preview_upload_path; ?>/<?php echo $video['preview_path'];  ?>">
              </div>
            </a>
            <div class="video_desc"><?php echo $vid_title; ?></div>
            <div class="video_duration"><img src="images/clock-icon.png"> <span><?php echo $video_duration; ?></span></div>
          </div>  
          <!-- vide  popup-->
          <div id="popup<?php echo $videoid ?>" class="modal-box">  
            <a href="javascript:void(0);" class="js-modal-close close">×</a>
              <video id='vidid<?php echo $videoid; ?>' width="700" controls>
             <source src="<?php echo $video_upload_path; ?>/<?php echo $video_path; ?>" type="video/mp4">
              Your browser does not support HTML5 video.
             </video>
          </div>
        <?php } 
        ?>
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

        <?php
   }else{
       echo "<div class='no_result'><strong>Sorry, no video found, please try again !</strong></div>";
   }
}
?>
